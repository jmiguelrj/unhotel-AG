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

JLoader::import('adapter.mvc.controllers.admin');

/**
 * Review admin controller.
 * 
 * @since 1.8.28
 */
class VikChannelManagerControllerReview extends JControllerAdmin
{
	/**
	 * Task reached through AJAX requests to reply to a review.
	 *
	 * @return  void
	 */
	public function reply()
	{
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		if (!JSession::checkToken()) {
			// missing CSRF-proof token
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		$review_id     = $app->input->getUInt('review_id', 0);
		$ota_review_id = $app->input->getString('ota_review_id', '');
		$uniquekey     = $app->input->getUInt('uniquekey', 0);
		$reply_text    = $app->input->getString('reply_text', '');

		if (empty($reply_text)) {
			VCMHttpDocument::getInstance($app)->close(400, 'Reply text cannot be empty');
		}

		try {
			// reply to the guest review
			(new VCMReviewModel)->reply(
				[
					'review_id'     => $review_id,
					'ota_review_id' => $ota_review_id,
					'uniquekey'     => $uniquekey,
					'reply_text'    => $reply_text,
				]
			);
		} catch(Exception $e) {
			// propagate the error
			VCMHttpDocument::getInstance($app)->close($e->getCode() ?: 500, $e->getMessage());
		}

		// send successful response code to output
		VCMHttpDocument::getInstance($app)->json([
			'msg' => JText::_('VCMREVREPLYSUCCESS'),
		]);
	}

	/**
	 * Task reached through AJAX requests to submit a host-to-guest review.
	 *
	 * @return  void
	 */
	public function host_to_guest()
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		if (!JSession::checkToken()) {
			// missing CSRF-proof token
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		$vbo_oid = $app->input->getInt('vbo_oid', 0);
		$public_review = $app->input->getString('public_review', '');
		$private_review = $app->input->getString('private_review', '');
		$review_cat_clean = $app->input->getInt('review_cat_clean', 5);
		$review_cat_clean_comment = $app->input->getString('review_cat_clean_comment', '');
		$review_cat_comm = $app->input->getInt('review_cat_comm', 5);
		$review_cat_comm_comment = $app->input->getString('review_cat_comm_comment', '');
		$review_cat_hrules = $app->input->getInt('review_cat_hrules', 5);
		$review_cat_hrules_comment = $app->input->getString('review_cat_hrules_comment', '');
		$review_host_again = $app->input->getInt('review_host_again', -1);
		$category_tags = $app->input->get('category_tags', [], 'array');

		$reservation = VikBooking::getBookingInfoFromID($vbo_oid);
		if (!$reservation) {
			VCMHttpDocument::getInstance($app)->close(404, 'Booking not found');
		}

		if (empty($public_review)) {
			VCMHttpDocument::getInstance($app)->close(400, 'Public review text cannot be empty');
		}

		if (!VikChannelManager::hostToGuestReviewSupported($reservation)) {
			VCMHttpDocument::getInstance($app)->close(403, 'The reservation does not support host to guest review at this time');
		}

		$channel = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!$channel) {
			VCMHttpDocument::getInstance($app)->close(400, 'No valid channels available to review your guest');
		}

		try {
			// submit a host-to-guest review
			(new VCMReviewModel)->reviewGuest(
				[
					'reservation'               => $reservation,
					'channel'                   => $channel,
					'public_review'             => $public_review,
					'private_review'            => $private_review,
					'review_cat_clean'          => $review_cat_clean,
					'review_cat_clean_comment'  => $review_cat_clean_comment,
					'review_cat_comm'           => $review_cat_comm,
					'review_cat_comm_comment'   => $review_cat_comm_comment,
					'review_cat_hrules'         => $review_cat_hrules,
					'review_cat_hrules_comment' => $review_cat_hrules_comment,
					'review_host_again'         => $review_host_again,
					'category_tags'             => $category_tags,
				]
			);
		} catch(Exception $e) {
			// propagate the error
			VCMHttpDocument::getInstance($app)->close($e->getCode() ?: 500, $e->getMessage());
		}

		// send successful response code to output
		VCMHttpDocument::getInstance($app)->json([
			'msg' => JText::_('MSG_BASE_SUCCESS'),
		]);
	}
}
