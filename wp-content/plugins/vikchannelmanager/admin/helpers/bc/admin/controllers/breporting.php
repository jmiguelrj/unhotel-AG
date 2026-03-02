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

class VikChannelManagerControllerBreporting extends JControllerAdmin {

	public function noShow () {
		$app = JFactory::getApplication();
		$input = $app->input;
		$otaid = $input->getString('otaid', '');
		$rrid = $input->getString('rrid', '');

		$response = $this->callReport($otaid, "is_no_show", $rrid);

		if ($response) {
			$app->enqueueMessage(JText::_('VCMBCOMREPORTSUCC'));
		}

		$app->redirect("index.php?option=com_vikchannelmanager&task=dashboard");
	}

	public function invalidCreditCard () {
		$app = JFactory::getApplication();
		$input = $app->input;
		$otaid = $input->getString('otaid', '');
		$cancel = $input->getBool('cancel', false);

		if ($cancel) {
			$response = $this->callReport($otaid, "cancel_reservation_invalid_cc");
		} else {
			$response = $this->callReport($otaid, "cc_is_invalid");
		}

		if ($response) {
			$app->enqueueMessage(JText::_('VCMBCOMREPORTSUCC'));
		}

		$app->redirect("index.php?option=com_vikchannelmanager&task=dashboard");
	}

	public function stayChanged () {
		$app = JFactory::getApplication();
		$input = $app->input;
		$otaid = $input->getString('otaid', '');
		$rrid = $input->getString('rrid', '');
		$checkin = $input->getString('checkin', '');
		$checkout = $input->getString('checkout', '');
		$price = $input->getString('price', '');

		$response = $this->callReport($otaid, "stay_change", $rrid, $checkin, $checkout, $price);

		if ($response) {
			$app->enqueueMessage(JText::_('VCMBCOMREPORTSUCC'));
		}

		$app->redirect("index.php?option=com_vikbooking&task=orders");
	}

	private function callReport($otaid, $report_type, $rrid = '', $checkin = '', $checkout = '', $price = '') {
		$channel = VikChannelManager::getActiveModule(true);
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=bcart&c=booking.com";

		if ($report_type == "stay_change") {
			$report_data = "<roomreservation_id>".$rrid."</roomreservation_id>
		<checkin>".$checkin."</checkin>
		<checkout>".$checkout."</checkout>
		<price>".$price."</price>";
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
