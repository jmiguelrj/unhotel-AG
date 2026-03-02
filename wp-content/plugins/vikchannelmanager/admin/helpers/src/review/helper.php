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
 * Review encapsulation helper.
 * 
 * @since 	1.8.28
 */
final class VCMReviewHelper extends JObject
{
	/**
	 * Channels supporting a reply to the guest review.
	 *
	 * @var  array
	 */
	private static $ch_reply_rev_enabled = [];

	/**
	 * Proxy to construct the object.
	 * 
	 * @param 	array|object  $data  optional data to bind.
	 * 
	 * @return 	self
	 */
	public static function getInstance($data)
	{
		// set channels supporting a reply to the guest review
		static::$ch_reply_rev_enabled = [
			VikChannelManagerConfig::BOOKING,
			VikChannelManagerConfig::AIRBNBAPI,
		];

		$data = (array) $data;

		// attempt to decode review raw content
		if (!empty($data['content']) && is_scalar($data['content'])) {
			$data['content'] = json_decode($data['content']);
			// attempt to map the "listing ID" property to "listing name" or other similar values
			static::mapReadableProperties($data['content']);
		}

		return new static($data);
	}

	/**
	 * Maps some known review-content properties into a readable name.
	 * 
	 * @param 	object 	$content 	the JSON-decoded review-content object.
	 * 
	 * @return 	void
	 */
	protected static function mapReadableProperties($content)
	{
		if (!is_object($content)) {
			return;
		}

		$dbo = JFactory::getDbo();

		// find the listing ID
		$listing_id = $content->listing_id ?? '';
		if ($listing_id) {
			$dbo->setQuery(
				$dbo->getQuery(true)
					->select($dbo->qn('otaroomname'))
					->from($dbo->qn('#__vikchannelmanager_roomsxref'))
					->where($dbo->qn('idroomota') . ' = ' . $dbo->q($listing_id))
			);

			$ota_room_name = $dbo->loadResult();

			if ($ota_room_name) {
				// replace value with the fetched listing name
				$content->listing_id = $ota_room_name;
			}
		}
	}

	/**
	 * Parses a review record into an object for its representation.
	 * 
	 * @return 	object
	 */
	public function parseObject()
	{
		$raw_cont = new stdClass;

		$review_id = $this->get('id');
		if (!$review_id) {
			return $raw_cont;
		}

		$review_data = new JObject($this->get('content', []));
		$review_channel = $this->get('uniquekey', 0);

		// for Booking.com we unset the property URL because we don't want customers to visit an endpoint just for our servers
		if ($review_channel == VikChannelManagerConfig::BOOKING && $review_data->get('url')) {
			// unset property so that it will be filtered out
			$review_data->set('url', '');
		}

		// reply to review: only some channels support it, and only if a reply is not already set
		if ($review_channel == VikChannelManagerConfig::BOOKING) {
			$reply = $review_data->get('reply');
			if (!is_object($reply) || empty($reply->text)) {
				// set the flag for the reply being allowed for Booking.com
				$this->set('can_reply', 1);
				$review_data->set('can_reply', 1);
			} else {
				$this->set('has_reply', 1);
			}
		} elseif ($review_channel == VikChannelManagerConfig::AIRBNBAPI) {
			$reviewee_response = $review_data->get('reviewee_response');
			if (empty($reviewee_response)) {
				// set the flag for the reply being allowed for Airbnb
				$public_review = $this->get('score', 0) != 0;
				$this->set('can_reply', (int) $public_review);
				$review_data->set('can_reply', (int) $public_review);
			} else {
				$this->set('has_reply', 1);
			}
		} elseif (!$this->get('channel') && empty($review_channel)) {
			$reply = $review_data->get('reply');
			if (empty($reply)) {
				// website review with no reply
				$this->set('can_reply', 1);
				$review_data->set('can_reply', 1);
			} else {
				$this->set('has_reply', 1);
			}
		}

		/**
		 * If a reply can be left for the review, ensures not too many AI errors occurred.
		 * 
		 * @since 	1.9.12
		 */
		if ($this->get('can_reply') == 1 && $review_data->get('_ai_reply_errors', 0) > 2) {
			// turn off the flag for supporting a review reply
			$this->set('can_reply', 0);
			$review_data->set('can_reply', 0);
		}

		// channel logo
		$channel_logo = '';
		if ($review_channel) {
			$channel_info = VikChannelManager::getChannel($review_channel);
			if ($channel_info) {
				$channel_logo = VikChannelManager::getLogosInstance($channel_info['name'])->getLogoURL();
			}
		}
		$this->set('channel_logo', $channel_logo);

		// get all review content properties
		$rev_properties = (array) $review_data->getProperties();
		if ($rev_properties) {
			// filter out empty/null values
			$rev_properties = array_filter($rev_properties);
		}

		// set all non-empty review content properties
		$raw_cont->{$review_id} = (object) $rev_properties;

		return $raw_cont;
	}

	/**
	 * Returns the information about channel for the guest review.
	 * 
	 * @return 	array
	 */
	public function getChannelDetails()
	{
		return [
			'channel' 	=> $this->get('channel'),
			'uniquekey' => $this->get('uniquekey'),
			'logo' 	    => $this->get('channel_logo'),
		];
	}

	/**
	 * Tells if the host can reply to the guest review.
	 * 
	 * @return 	bool
	 */
	public function canReply()
	{
		return (bool) $this->get('can_reply', 0);
	}

	/**
	 * Tells if the host replied to the guest review already.
	 * 
	 * @return 	bool
	 */
	public function hasReply()
	{
		return (bool) $this->get('has_reply', 0);
	}
}
