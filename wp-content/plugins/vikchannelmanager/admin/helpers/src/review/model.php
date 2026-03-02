<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Review model.
 * 
 * @since 	1.8.28
 */
final class VCMReviewModel
{
	/**
	 * Gets a review record from the given options.
	 * 
	 * @param 	array|int 	$pk 	The review ID to load, or an array of fields to match.
	 * 
	 * @return 	object|null
	 */
	public function getItem($pk)
	{
		if (is_scalar($pk)) {
			$pk = [
				'id' => (int) $pk,
			];
		}

		if (!is_array($pk)) {
			return null;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_otareviews'));

		foreach ($pk as $column => $value) {
			$q->where($dbo->qn($column) . ' = ' . $dbo->q($value));
		}

		$dbo->setQuery($q, 0, 1);

		return $dbo->loadObject();
	}

	/**
	 * Replies to a guest review, coming either from the website or an OTA.
	 * 
	 * @param 	array|object	$options  List of reply options.
	 * 
	 * @return 	bool
	 * 
	 * @throws 	Exception
	 */
	public function reply($options)
	{
		$dbo = JFactory::getDbo();

		$options = (array) $options;

		// expected reply options
		$review_id     = $options['review_id'] ?? 0;
		$ota_review_id = $options['ota_review_id'] ?? '';
		$reply_text    = $options['reply_text'] ?? '';

		if (!$review_id || !$reply_text) {
			throw new Exception('Missing mandatory values', 400);
		}

		// get the review record
		$review = $this->getItem($review_id);

		if (!$review) {
			throw new Exception('Review not found', 404);
		}

		if (!$ota_review_id) {
			$ota_review_id = $review->review_id;
		}

		if (!empty($review->channel)) {
			// OTA review

			// load the channel for this review
			$channel = VikChannelManager::getChannel($review->uniquekey);
			if (!$channel) {
				throw new Exception('Channel not found', 404);
			}

			// access channel params
			$channel['params'] = json_decode($channel['params'], true);
			$channel['params'] = is_array($channel['params']) ? $channel['params'] : [];

			// get the first parameter, which may not be 'hotelid'
			$usehid = '';
			foreach ($channel['params'] as $v) {
				$usehid = $v;
				break;
			}

			// make sure the params saved for this channel match the account ID of the review
			if (!$usehid || $usehid != $review->prop_first_param) {
				// load the proper account in case of multi-account channels
				$dbo->setQuery(
					$dbo->getQuery(true)
						->select($dbo->qn('prop_params'))
						->from($dbo->qn('#__vikchannelmanager_roomsxref'))
						->where($dbo->qn('idchannel') . ' = ' . $dbo->q($review->uniquekey))
						->where($dbo->qn('prop_params') . ' LIKE ' . $dbo->q('%' . $review->prop_first_param . '%'))
				);

				// overwrite channel params with the account requested
				$channel['params'] = json_decode($dbo->loadResult(), true);
				$channel['params'] = is_array($channel['params']) ? $channel['params'] : [];

				// get the first parameter, which may not be 'hotelid'
				foreach ($channel['params'] as $v) {
					$usehid = $v;
					break;
				}
			}

			if (!$usehid) {
				throw new Exception('Channel account settings not found', 404);
			}

			// adjust the channel name for TripConnect on e4jConnect
			$usech = $channel['name'];
			if ($channel['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT) {
				$usech = 'tripadvisor';
			}

			// make the request to e4jConnect to reply to the review
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=rprew&c=" . $usech;
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager RREVW Request e4jConnect.com - ' . ucwords($channel['name']) . ' -->
<ReadReviewsRQ xmlns="http://www.e4jconnect.com/channels/rrevwrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . VikChannelManager::getApiKey(true) . '"/>
	<ReadReviews>
		<Fetch hotelid="' . trim($usehid) . '" revid="' . $ota_review_id . '" />
		<Reply><![CDATA[' . $reply_text . ']]></Reply>
	</ReadReviews>
</ReadReviewsRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$rs = $e4jC->exec();

			if ($e4jC->getErrorNo()) {
				throw new Exception('Service temporary unavailable', 503);
			}

			if (strpos($rs, 'e4j.ok') === false) {
				throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
			}
		}

		// update the content of this review with the reply message so that no other replies will be allowed
		$new_rev_content = !empty($review->content) ? json_decode($review->content) : (new stdClass);
		$new_rev_content = !is_object($new_rev_content) ? new stdClass : $new_rev_content;
		$new_rev_content->reply = isset($new_rev_content->reply) && is_object($new_rev_content->reply) ? $new_rev_content->reply : (new stdClass);

		if (empty($review->channel)) {
			// website review we update the "reply" property
			$new_rev_content->reply = $reply_text;
		} elseif ($review->uniquekey == VikChannelManagerConfig::AIRBNBAPI) {
			// Airbnb API OTA review, we update the "reviewee_response" property
			$new_rev_content->reviewee_response = $reply_text;
			unset($new_rev_content->reply);
		} else {
			// OTA review, we update the "text" property in "reply"
			$new_rev_content->reply->text = $reply_text;
		}

		// set new raw content
		$review->content = json_encode($new_rev_content);

		// update record
		return (bool) $dbo->updateObject('#__vikchannelmanager_otareviews', $review, 'id');
	}

	/**
	 * Submits a review from the host to the guest (only for some channels).
	 * 
	 * @param 	array|object	$options  List of review options.
	 * 
	 * @return 	bool
	 * 
	 * @throws 	Exception
	 */
	public function reviewGuest($options)
	{
		$dbo = JFactory::getDbo();

		$options = (array) $options;

		// expected review options
		$reservation               = $options['reservation'] ?? [];
		$channel                   = $options['channel'] ?? [];
		$public_review             = $options['public_review'] ?? '';
		$private_review            = $options['private_review'] ?? '';
		$review_cat_clean          = $options['review_cat_clean'] ?? '';
		$review_cat_clean_comment  = $options['review_cat_clean_comment'] ?? '';
		$review_cat_comm           = $options['review_cat_comm'] ?? '';
		$review_cat_comm_comment   = $options['review_cat_comm_comment'] ?? '';
		$review_cat_hrules         = $options['review_cat_hrules'] ?? '';
		$review_cat_hrules_comment = $options['review_cat_hrules_comment'] ?? '';
		$review_host_again         = $options['review_host_again'] ?? '';
		$category_tags             = $options['category_tags'] ?? [];

		if (empty($reservation['idorderota'])) {
			throw new Exception('Missing OTA reservation ID', 400);
		}

		if (!$channel) {
			throw new Exception('Missing OTA details', 400);
		}

		// find the mapping information for the room(s) booked
		$account_key = null;

		$dbo->setQuery(
			$dbo->getQuery(true)
				->select([
					$dbo->qn('or.idroom'),
					$dbo->qn('x.idroomota'),
					$dbo->qn('x.prop_params'),
				])
				->from($dbo->qn('#__vikbooking_ordersrooms', 'or'))
				->leftJoin($dbo->qn('#__vikchannelmanager_roomsxref', 'x') . ' ON ' . $dbo->qn('or.idroom') . ' = ' . $dbo->qn('x.idroomvb'))
				->where($dbo->qn('or.idorder') . ' = ' . (int) $reservation['id'])
				->where($dbo->qn('x.idchannel') . ' = ' . (int) $channel['uniquekey'])
		);

		foreach ($dbo->loadAssocList() as $rassoc) {
			if (empty($rassoc['prop_params'])) {
				continue;
			}

			$account_data = json_decode($rassoc['prop_params'], true);
			if (!$account_data) {
				continue;
			}

			foreach ($account_data as $acc_val) {
				// we grab the first param value
				if (!empty($acc_val)) {
					$account_key = $acc_val;
					break 2;
				}
			}
		}

		if (empty($account_key)) {
			// the account credentials must be present to perform the request
			throw new Exception('Could not find the channel account params', 500);
		}

		// sanitize values for XML request
		if (defined('ENT_XML1')) {
			// only available from PHP 5.4 and on
			$public_review = htmlspecialchars($public_review, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$private_review = htmlspecialchars($private_review, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_clean_comment = htmlspecialchars($review_cat_clean_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_comm_comment = htmlspecialchars($review_cat_comm_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_hrules_comment = htmlspecialchars($review_cat_hrules_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			
		} else {
			// fallback to plain all html entities
			$public_review = htmlentities($public_review);
			$private_review = htmlentities($private_review);
			$review_cat_clean_comment = htmlentities($review_cat_clean_comment);
			$review_cat_comm_comment = htmlentities($review_cat_comm_comment);
			$review_cat_hrules_comment = htmlentities($review_cat_hrules_comment);
		}

		/**
		 * Added support to Airbnb review category tags.
		 * 
		 * @since 	1.9
		 */
		$cleanliness_tags = $category_tags['cleanliness'] ?? [];
		$communication_tags = $category_tags['communication'] ?? [];
		$respect_house_rules_tags = $category_tags['respect_house_rules'] ?? [];
		if (is_array($cleanliness_tags)) {
			$cleanliness_tags = implode(', ', $cleanliness_tags);
		}
		if (is_array($communication_tags)) {
			$communication_tags = implode(', ', $communication_tags);
		}
		if (is_array($respect_house_rules_tags)) {
			$respect_house_rules_tags = implode(', ', $respect_house_rules_tags);
		}

		// make the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=htgr&c=" . $channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager HTGR Request e4jConnect.com - Vik Channel Manager -->
<HostToGuestReviewRQ xmlns="http://www.e4jconnect.com/schemas/htgrrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $account_key . '"/>
	<HostReview otaresid="' . $reservation['idorderota'] . '">
		<Public><![CDATA[' . $public_review . ']]></Public>
		<Private><![CDATA[' . $private_review . ']]></Private>
		<Ratings>
			<Rating category="cleanliness" score="' . $review_cat_clean . '"' . ($cleanliness_tags ? ' tags="' . $cleanliness_tags . '"' : '') . '><![CDATA[' . $review_cat_clean_comment . ']]></Rating>
			<Rating category="communication" score="' . $review_cat_comm . '"' . ($communication_tags ? ' tags="' . $communication_tags . '"' : '') . '><![CDATA[' . $review_cat_comm_comment . ']]></Rating>
			<Rating category="respect_house_rules" score="' . $review_cat_hrules . '"' . ($respect_house_rules_tags ? ' tags="' . $respect_house_rules_tags . '"' : '') . '><![CDATA[' . $review_cat_hrules_comment . ']]></Rating>
			<Rating category="host_again" score="' . $review_host_again . '" />
		</Ratings>
	</HostReview>
</HostToGuestReviewRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			throw new Exception(VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()), 500);
		}

		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
		}

		// channel name
		$say_channel_name = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucfirst($channel['name']);

		// update booking history
		VikBooking::getBookingHistoryInstance($reservation['id'])->store('CM', $say_channel_name . ' - ' . JText::_('VCM_HOST_TO_GUEST_REVIEW'));

		// build record name
		$transient_name = 'host_to_guest_review_' . $channel['uniquekey'] . '_' . $reservation['id'];

		// build host review object with some basic details
		$host_review_object = new stdClass;
		$host_review_object->public_review     = $public_review;
		$host_review_object->private_review    = $private_review;
		$host_review_object->review_cat_clean  = $review_cat_clean;
		$host_review_object->review_cat_comm   = $review_cat_comm;
		$host_review_object->review_cat_hrules = $review_cat_hrules;

		// insert record in VCM so that the system will detect that a review was left already
		VCMFactory::getConfig()->set($transient_name, json_encode($host_review_object));

		// flag as completed the reminder to review the guest, if any
		VBORemindersHelper::getInstance()->completeBookingReminders($reservation['id'], ['airbnb_host_guest_review' => 1]);

		return true;
	}

	/**
	 * Updates the content of a given review ID. Useful to set error counter values.
	 * 
	 * @param 	int 	$review_id 	The review record ID.
	 * @param 	array 	$options 	The content options to set.
	 * 
	 * @return 	bool
	 * 
	 * @since 	1.9.12
	 */
	public function updateContent(int $review_id, array $options)
	{
		$dbo = JFactory::getDbo();

		// get the review record
		$review = $this->getItem($review_id);

		if (!$review) {
			// review not found
			return false;
		}

		// access and decode the current review content
		$rev_content = !empty($review->content) ? (array) json_decode($review->content, true) : [];

		// merge review content values with given options
		$rev_content = array_merge($rev_content, $options);

		// set new and encoded review content
		$review->content = json_encode($rev_content);

		// build new review record
		$review_record = new stdClass;
		$review_record->id = $review->id;
		$review_record->content = $review->content;

		// update record
		return (bool) $dbo->updateObject('#__vikchannelmanager_otareviews', $review_record, 'id');
	}
}
