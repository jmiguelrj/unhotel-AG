<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerBreporting extends JControllerAdmin
{
	/**
	 * New AJAX task used by updated versions of VikBooking and VCM.
	 * Reports to Booking.com a reservation ID as no-show.
	 * 
	 * @since 	1.8.24
	 */
	public function no_show()
	{
		if (!JSession::checkToken()) {
			// missing CSRF-proof token
			VCMHttpDocument::getInstance()->close(403, JText::_('JINVALID_TOKEN'));
		}

		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$otaid       = $app->input->getString('otaid');
		$waived_fees = $app->input->getBool('waived_fees', false);

		$dbo->setQuery(
			$dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikbooking_orders'))
				->where($dbo->qn('idorderota') . ' = ' . $dbo->q($otaid))
		);

		$booking = $dbo->loadAssoc();

		if (!$booking) {
			VCMHttpDocument::getInstance()->close(404, 'Booking not found');
		}

		$reporting = VCMOtaReporting::getInstance($booking);

		if (!$reporting->notifyNoShow($waived_fees)) {
			VCMHttpDocument::getInstance()->close(500, $reporting->getError() ?: 'Invalid channel response');
		}

		// terminate with success
		VCMHttpDocument::getInstance()->json(['success' => 1]);
	}

	/**
	 * New AJAX task used by updated versions of VikBooking and VCM.
	 * Reports to Booking.com a reservation credit card as invalid.
	 * 
	 * @since 	1.8.24
	 */
	public function invalid_credit_card()
	{
		if (!JSession::checkToken()) {
			// missing CSRF-proof token
			VCMHttpDocument::getInstance()->close(403, JText::_('JINVALID_TOKEN'));
		}

		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$otaid      = $app->input->getString('otaid');
		$cancel_res = $app->input->getBool('cancel_res', false);

		$dbo->setQuery(
			$dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikbooking_orders'))
				->where($dbo->qn('idorderota') . ' = ' . $dbo->q($otaid))
		);

		$booking = $dbo->loadAssoc();

		if (!$booking) {
			VCMHttpDocument::getInstance()->close(404, 'Booking not found');
		}

		$reporting = VCMOtaReporting::getInstance($booking);

		if (!$reporting->notifyInvalidCreditCard($cancel_res)) {
			VCMHttpDocument::getInstance()->close(500, $reporting->getError() ?: 'Invalid channel response');
		}

		// terminate with success
		VCMHttpDocument::getInstance()->json(['success' => 1]);
	}

	/**
	 * Deprecated in favour of the E4jConnect /v2 endpoint.
	 * 
	 * @deprecated  1.8.24
	 * 
	 * @see 		VCMOtaReporting
	 */
	public function noShow()
	{
		VCMHttpDocument::getInstance()->close(400, 'Please update both VikBooking and Vik Channel Manager');
	}

	/**
	 * Deprecated in favour of the E4jConnect /v2 endpoint.
	 * 
	 * @deprecated  1.8.24
	 * 
	 * @see 		VCMOtaReporting
	 */
	public function invalidCreditCard()
	{
		VCMHttpDocument::getInstance()->close(400, 'Please update both VikBooking and Vik Channel Manager');
	}

	/**
	 * Deprecated in favour of the E4jConnect /v2 endpoint.
	 * 
	 * @deprecated  1.8.24
	 * 
	 * @see 		VCMOtaReporting
	 */
	public function stayChanged()
	{
		VCMHttpDocument::getInstance()->close(400, 'Please update both VikBooking and Vik Channel Manager');
	}

	/**
	 * Deprecated in favour of the E4jConnect /v2 endpoint.
	 * 
	 * @deprecated  1.8.24
	 * 
	 * @see 		VCMOtaReporting
	 */
	private function callReport($otaid, $report_type, $rrid = '', $checkin = '', $checkout = '', $price = '')
	{
		// terminate the execution
		VCMHttpDocument::getInstance()->close(400, 'Please update both VikBooking and Vik Channel Manager');

		$channel = VikChannelManager::getActiveModule(true);
		if ($channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			$channel = VikChannelManager::getChannel(VikChannelManagerConfig::BOOKING);
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=bcart&c=booking.com";

		if ($report_type == "stay_change") {
			$report_data = "<roomreservation_id>".$rrid."</roomreservation_id>
		<checkin>".$checkin."</checkin>
		<checkout>".$checkout."</checkout>
		<price>".$price."</price>";
		} else if ($report_type == "is_no_show" && $rrid) {
			$report_data = "<is_no_show roomreservation_id=\"".$rrid."\" waived_fees=\"".$checkin."\"/>";
		} else if ($report_type == "is_no_show") {
			$report_data = "<report waived_fees=\"".$checkin."\">is_no_show</report>";
		} else {
			$report_data = $report_type;
		}

		$channel['params'] = json_decode($channel['params'], true);

		$report = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!-- BCART Request e4jConnect.com - VikChannelManager - VikBooking -->
<BCAReportRQ xmlns=\"http://www.e4jconnect.com/avail/bcartrq\">
	<Notify client=\"".JUri::root()."\"/>
	<Api key=\"".VikChannelManager::getApiKey()."\"/>
	<BCAReport hotelid=\"".$channel['params']['hotelid']."\">
		<reservation_id>".$otaid."</reservation_id>
			";
		if ($report_type == "stay_change") {
			$report .= "<stay_change>".$report_data."</stay_change>";
		} else if ($report_type == "is_no_show") {
			$report .= $report_data;
		} else {
			$report .= "<report>".$report_data."</report>";
			if ($rrid) {
				$report .= "<roomreservation_id>".$rrid."</roomreservation_id>";
			}
		}
	$report .= "
	</BCAReport>
</BCAReportRQ>";

		if (class_exists("DOMDocument")) {
			$dom = new DOMDocument;
			$dom->preserveWhiteSpace = FALSE;
			$dom->loadXML($report);
			$dom->formatOutput = TRUE;
			$report = $dom->saveXML();
		}

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($report);
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
			return false;
		} else {
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				return false;
			} else {
				return true;
			}
		}
	}
}
