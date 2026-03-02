<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// Restricted access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Reviews cron AI helper.
 * 
 * @since 1.9
 */
class VCMAiCronReviews
{
    /** @var VCMAiModelSettings */
    protected $settingsModel;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->settingsModel = new VCMAiModelSettings;
    }

    /**
     * Periodically process the latest reviews that requires a reply and asks
     * to the AI to write a message.
     * 
     * @param   int  $threshold   The reviews must not be created before the specified number of days.
     * @param   int  $maxReviews  The maximum number of reviews that can be processed per time.
     * 
     * @return  void
     */
    public function autoReply(int $threshold = 7, int $maxReviews = 10)
    {
        // check whether the review auto-reply has been enabled
        if (!$this->isEnabled()) {
            // abort immediately
            return;
        }

        // load all the compatible reviews
        $reviews = $this->loadReviews($threshold, $maxReviews);

        foreach ($reviews as $review) {
            // set up notification for VBO center
            $notification = [
                'sender' => 'ai',
                'idorder' => $review->get('idorder'),
                'widget' => 'guest_reviews',
                'widget_options' => [
                    'review_id' => $review->get('id'),
                ],
            ];
                
            try {
                // ask the AI to generate a reply for the review
                $reply = $this->generateReply($review);
                
                // send the review reply to e4jConnect
                (new VCMReviewModel)->reply([
                    'review_id'  => $review->get('id'),
                    'reply_text' => $reply,
                ]);

                // complete notification details on success
                $notification['type']    = 'review.reply.ok';
                $notification['title']   = JText::_('VCM_AI_AUTO_REVIEW_REPLY_SUCCESS_TITLE');
                $notification['summary'] = $reply;
                $notification['label']   = JText::_('VCM_AI_AUTO_RESPONDER_BTN_SEE_REPLY');
            } catch (Exception $error) {
                // complete notification details on failure
                $notification['type']    = 'review.reply.error';
                $notification['title']   = JText::_('VCM_AI_AUTO_REVIEW_REPLY_ERROR_TITLE');
                $notification['summary'] = $error->getMessage();

                /**
                 * Count and increase errors for the current review content.
                 * Useful to avoid multiple errors for the same review action.
                 * 
                 * @since   1.9.12
                 */
                $review_errors_count = (int) $this->getContentValue($review, '_ai_reply_errors', 0);
                (new VCMReviewModel)->updateContent((int) $review->get('id'), [
                    '_ai_reply_errors' => ++$review_errors_count,
                ]);
            }

            try {
                $notification['date'] = JFactory::getDate()->toISO8601();

                // register notification with the current date
                VBOFactory::getNotificationCenter()->store([$notification]);
            } catch (Throwable $error) {
                // notification center not supported
            }
        }
    }

    /**
     * Checks whether the reviews auto-responder is enabled or not.
     * 
     * @return  bool  True if enabled, false otherwise.
     */
    protected function isEnabled()
    {
        return $this->settingsModel->isReviewReplyAutoResponderEnabled();
    }

    /**
     * Loads all the reviews left in the last N days (7 by default) that can
     * actually receive an auto-reply from the administrator.
     * 
     * The number of resulting records depends on the maxReviews constant.
     * 
     * @param   int  $threshold   The number of days in the past.
     * @param   int  $maxReviews  The maximum number of reviews that can be processed per time.
     * 
     * @return  VCMReviewHelper[]
     */
    protected function loadReviews(int $threshold, int $maxReviews)
    {
        $db = JFactory::getDbo();

        $reviews = [];

        // convert the days threshold into a date object
        $threshold = JFactory::getDate('-' . abs($threshold) . ' days');

        // take all the reviews left in the last N days
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->qn('#__vikchannelmanager_otareviews'))
            ->where($db->qn('dt') . ' >= ' . $db->q($threshold->toSql()))
            ->order($db->qn('dt') . ' ASC');

        $db->setQuery($query);
        
        // iterate all the compatible reviews
        foreach ($db->loadObjectList() as $review) {
            $review = VCMReviewHelper::getInstance($review);
            $review->parseObject();

            // make sure the review can actually receive an auto-reply
            if (!$review->canReply()) {
                continue;
            }

            // make sure the review hasn't received yet a reply
            if ($review->hasReply()) {
                continue;
            }

            // check whether the review is negative and should be excluded
            if ($this->settingsModel->shouldExcludeNegativeReview((int) $review->get('score', 0))) {
                continue;
            }

            $reviews[] = $review;

            // immediately stop if we reached the maximum number of processable reviews
            if (count($reviews) >= $maxReviews) {
                return $reviews;
            }
        }

        return $reviews;
    }

    /**
     * Asks the AI to generate a reply for the provided review.
     * 
     * @param   VCMReviewHelper  $review  The review details.
     * 
     * @return  string  The AI response.
     * 
     * @throws  Exception
     */
    protected function generateReply($review)
    {
        $raw = $review->get('content');

        // extract contents from review
        $reviewText = [];

        if ($review->get('uniquekey') == VikChannelManagerConfig::BOOKING) {
            // Booking.com
            $reviewText[] = $raw->content->headline ?? null;
            $reviewText[] = $raw->content->negative ?? null;
            $reviewText[] = $raw->content->positive ?? null;
        } else if ($review->get('uniquekey') == VikChannelManagerConfig::AIRBNBAPI) {
            // Airbnb API
            $reviewText[] = $raw->content->public_review ?? null;
            $reviewText = array_merge($reviewText, array_values($raw->content->comments ?? []));
        } else if ($review->get('uniquekey') == 0) {
            // Website
            $reviewText[] = $raw->content->message ?? null;
        }

        // add overall score
        if ($score = $review->get('score')) {
            $reviewText[] = 'Overall score: ' . round($score) . '/10';
        }

        // join review text components
        $reviewText = implode("\n", array_filter($reviewText));

        if (!$reviewText) {
            // empty review...
            throw new UnexpectedValueException('Unable to identify the details of the review left by the guest', 400);
        }

        // generate review reply
        return (new VCMAiModelService)->review([
            // @unused because the review reply is always based on the message locale
            'language' => $review->get('lang'),
            'customer' => $review->get('customer_name'),
            'review'   => $reviewText,
            'id_order' => $review->get('idorder'),
        ]);
    }

    /**
     * Retrieves the requested content value from the given review representation.
     * 
     * @param   VCMReviewHelper     $review     The review record wrapper.
     * @param   string              $value      The name of the content value to get.
     * @param   mixed               $default    The default value to return, if value not set.
     * 
     * @return  mixed
     * 
     * @since   1.9.12
     */
    protected function getContentValue(VCMReviewHelper $review, string $value, $default = null)
    {
        $content = $review->get('content');

        if (!is_object($content)) {
            return $default;
        }

        return $content->{$value} ?? $default;
    }
}
