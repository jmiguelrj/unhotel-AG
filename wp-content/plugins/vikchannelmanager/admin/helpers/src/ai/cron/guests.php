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
 * Guests cron AI helper.
 * 
 * @since 1.9
 */
class VCMAiCronGuests
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
     * Automatically reviews the guests that checked-out in the last 2 weeks.
     * 
     * @param   int   $maxReviews  The maximum number of reviews to submit per time.
     * 
     * @return  void
     */
    public function autoReview(int $maxReviews = 10)
    {
        // check whether the guests auto-reviewer has been enabled
        if (!$this->isEnabled()) {
            // abort immediately
            return;
        }

        // load all the compatible reservations
        $reservations = $this->loadReservations($maxReviews);

        foreach ($reservations as $reservation) {
            // set up notification for VBO center
            $notification = [
                'sender' => 'ai',
                'idorder' => $reservation->id,
                'idorderota' => $reservation->idorderota,
                'url' => VCMFactory::getPlatform()->getUri()->admin('index.php?option=com_vikchannelmanager&task=hostguestreview&cid[]=' . $reservation->id),
            ];
                
            try {
                // ask the AI to generate a review for the guest
                $review = $this->generateReview($reservation);
                
                // send the guest review to e4jConnect
                (new VCMReviewModel)->reviewGuest($review);

                // complete notification details on success
                $notification['type']    = 'review.guest.ok';
                $notification['title']   = JText::_('VCM_AI_AUTO_REVIEW_GUEST_SUCCESS_TITLE');
                $notification['summary'] = $review['public_review'];
                $notification['label']   = JText::_('VCM_AI_AUTO_RESPONDER_BTN_SEE_REVIEW');
            } catch (Exception $error) {
                // complete notification details on failure
                $notification['type']    = 'review.guest.error';
                $notification['title']   = JText::_('VCM_AI_AUTO_REVIEW_GUEST_ERROR_TITLE');
                $notification['summary'] = $error->getMessage();
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
     * Checks whether the guests auto-reviewer is enabled or not.
     * 
     * @return  bool  True if enabled, false otherwise.
     */
    protected function isEnabled()
    {
        return $this->settingsModel->isGuestReviewAutoResponderEnabled();
    }

    /**
     * Loads the next reservations that should receive a host-to-guest review.
     * 
     * @param   int  $maxReviews  The maximum number of reviews to submit per time.
     * 
     * @return  object[]  The compatible reservations.
     */
    protected function loadReservations(int $maxReviews)
    {
        $start = JFactory::getDate('-14 days', date_default_timezone_get());
        $end   = JFactory::getDate('-1 day', date_default_timezone_get());

        $reservations = [];

        $db = JFactory::getDbo();

        // take all the confirmed reservations with checkout between the last 14 and 1 day
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->qn('#__vikbooking_orders'))
            ->where($db->qn('checkout') . ' BETWEEN ' . $start->format('U', true) . ' AND ' . $end->format('U', true))
            ->where($db->qn('status') . ' = ' . $db->q('confirmed'))
            ->order($db->qn('checkout') . ' ASC');

        $db->setQuery($query);

        foreach ($db->loadObjectList() as $reservation) {
            // make sure the current reservation supports host-to-guest reviews
            if (VikChannelManager::hostToGuestReviewSupported((array) $reservation)) {
                // make sure the ignore option was not set
                $booking_ai_opts = VBOFactory::getConfig()->getArray('ai_auto_guest_review_opt_' . $reservation->id, []);
                if ($booking_ai_opts['ignore'] ?? 0) {
                    // skip reservation from automatic guest review
                    continue;
                }

                // push eligible reservation
                $reservations[] = $reservation;

                // immediately stop if we reached the maximum number of processable reviews
                if (count($reservations) >= $maxReviews) {
                    return $reservations;
                }
            }
        }

        return $reservations;
    }

    /**
     * Generates a positive guest review for the specified reservation.
     * 
     * @param   object  $reservation  An object holding all the reservations details.
     * 
     * @return  array   An associative array containing the review information.
     */
    protected function generateReview($reservation)
    {
        // fetch customer details
        $customer = VikBooking::getCPinInstance()->getCustomerFromBooking($reservation->id);

        // generate review reply
        $guestReview = (new VCMAiModelService)->review([
            'language' => $reservation->lang ?: null,
            'customer' => $customer['first_name'] ?? null,
        ]);

        // set up host-to-guest review options
        return [
            'reservation' => (array) $reservation,
            'channel' => VikChannelManager::getChannelFromName($reservation->channel),
            'public_review' => $guestReview,
            'review_cat_clean' => 5,
            'review_cat_comm' => 5,
            'review_cat_hrules' => 5,
            'review_host_again' => 1,
        ];
    }
}
