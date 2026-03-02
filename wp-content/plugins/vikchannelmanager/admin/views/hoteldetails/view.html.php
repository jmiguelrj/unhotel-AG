<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewHoteldetails extends JViewUI
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		VCM::load_css_js();
		VCM::load_complex_select();

		$dbo 	 = JFactory::getDbo();
		$session = JFactory::getSession();
		$api_key = VikChannelManager::getApiKey(true);

		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = (array) json_decode($module['params'], true);

		$params = [];

		$multi_hotels = [];
		$multi_haccount_id = 0;

		$tac_rooms_mapped = 0;
		$tac_vbo_tot_rooms = 0;

		$q = "SELECT * FROM `#__vikchannelmanager_hotel_details`;";
		$dbo->setQuery($q);
		$rows = $dbo->loadAssocList();

		foreach ($rows as $r) {
			$params[$r['key']] = $r['value'];
		}

		try {
			$q = "SELECT * FROM `#__vikbooking_countries` ORDER BY `country_name`;";
			$dbo->setQuery($q);
			$countries = $dbo->loadAssocList();
		} catch (Exception $e) {
			// do nothing
			$countries = array();
		}

		if ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL && VikChannelManager::channelHasRoomsMapped($module['uniquekey'])) {
			// allow multi-accounts
			$add_multi_hotel = VikRequest::getInt('add_multi_hotel', 0, 'request');
			$multi_haccount = VikRequest::getInt('multi_hotel_account', 0, 'request');

			if ($add_multi_hotel) {
				// unset previous data
				foreach ($params as $key => $val) {
					$params[$key] = null;
				}
			}

			$q = "SELECT * FROM `#__vikchannelmanager_hotel_multi` WHERE `channel`=" . (int) $module['uniquekey'] . ";";
			$dbo->setQuery($q);
			$multi_rows = $dbo->loadAssocList();
			foreach ($multi_rows as $multi_hotel) {
				$multi_hotel['hdata'] = !empty($multi_hotel['hdata']) ? ((array) json_decode($multi_hotel['hdata'], true)) : [];
				// push additional hotel record
				$multi_hotels[] = $multi_hotel;
				if ($multi_haccount == $multi_hotel['id'] && !empty($multi_hotel['hdata'])) {
					// overwrite current hotel details record
					$params = $multi_hotel['hdata'];
					$multi_haccount_id = $multi_hotel['id'];
				}
			}
		} elseif ($module['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT && !empty($module['params']['tripadvisorid'])) {
			/**
			 * Allow multi accounts for TripAdvisor (TripConnect)
			 * 
			 * @since 	1.9.10
			 */
			$add_multi_hotel = VikRequest::getInt('add_multi_hotel', 0, 'request');
			$multi_haccount = VikRequest::getString('multi_hotel_account', '', 'request');

			if ($add_multi_hotel) {
				// unset previous data
				foreach ($params as $key => $val) {
					$params[$key] = null;
				}
			}

			$q = "SELECT * FROM `#__vikchannelmanager_hotel_multi` WHERE `channel`=" . (int) $module['uniquekey'] . ";";
			$dbo->setQuery($q);
			$multi_rows = $dbo->loadAssocList();
			foreach ($multi_rows as $multi_hotel) {
				$multi_hotel['hdata'] = !empty($multi_hotel['hdata']) ? ((array) json_decode($multi_hotel['hdata'], true)) : [];
				// push additional hotel record
				$multi_hotels[] = $multi_hotel;
				if ($multi_haccount == $multi_hotel['account_id'] && !empty($multi_hotel['hdata'])) {
					// overwrite current hotel details record
					$params = $multi_hotel['hdata'];
					$multi_haccount_id = $multi_hotel['id'];
				}
			}

			// count rooms mapped and total rooms
			$dbo->setQuery("SELECT COUNT(*) FROM `#__vikchannelmanager_tac_rooms`;");
			$tac_rooms_mapped = (int) $dbo->loadResult();
			$dbo->setQuery("SELECT COUNT(*) FROM `#__vikbooking_rooms` WHERE `avail`=1;");
			$tac_vbo_tot_rooms = (int) $dbo->loadResult();
		}

		// whether to display the Vacation Rentals APIs for Booking.com
		$force_vress = VikRequest::getInt('force_vress', 0, 'request');
		// force reload can also be passed via session
		$force_vress_sess = $session->get('force_vress', 0, 'vcm-vress');
		$force_vress = $force_vress_sess > 0 ? $force_vress_sess : $force_vress;

		$display_vress = 0;
		$hotelid = '';
		$vr_allowed = ($module['uniquekey'] == VikChannelManagerConfig::BOOKING);
		$channels_mapping = $vr_allowed ? VikChannelManager::getChannelAccountsMapped(VikChannelManagerConfig::BOOKING) : array();
		$vress_data = $session->get('vress_data', '', 'vcm-vress');
		$vress_errors_count = (int)$session->get('vress_err', 0, 'vcm-vress');
		if ($vr_allowed && $channels_mapping) {
			// some rooms have been mapped already, and Booking.com is the active channel
			foreach ($module['params'] as $param_name => $param_value) {
				// grab the first channel parameter
				$hotelid = $param_value;
				break;
			}
			if (!empty($hotelid)) {
				// turn on the flag for displaying the Vacation Rentals APIs (just some of them will be here in this View)
				$display_vress = 1;
			}
		}

		if ($display_vress && ((!is_object($vress_data) && $vress_errors_count < 1) || $force_vress === 1)) {
			// we can make the VR Essentials request to e4jConnect
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=vress&c=booking.com";
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager VRESS Request e4jConnect.com - Vik Channel Manager -->
<VacationRentalsEssentialsRQ xmlns="http://www.e4jconnect.com/schemas/vressrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $hotelid . '"/>
	<Read type="KeyCollection">
		<Data type="StreamVariations" extra="" />
		<Data type="CheckinMethods" extra="primary_checkin_method" />
		<Data type="PropertyCheckinMethods" extra="" />
	</Read>
	<Read type="PropertyProfile"/>
</VacationRentalsEssentialsRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				$session->set('vress_err', ($vress_errors_count + 1), 'vcm-vress');
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$session->set('vress_err', ($vress_errors_count + 1), 'vcm-vress');
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			} else {
				$vress_data = json_decode($rs);
				if (!is_object($vress_data)) {
					$vress_data = '';
					VikError::raiseWarning('', 'Cannot decode channel response');
				} else {
					// update session value
					$session->set('vress_data', $vress_data, 'vcm-vress');
				}
			}
		}
		if ($vress_errors_count > 0) {
			// API errors have occurred when reading, and so we do not display the VR Essentials
			$display_vress = 0;
		}
		//

		$this->params = $params;
		$this->countries = $countries;
		$this->display_vress = $display_vress;
		$this->hotelid = $hotelid;
		$this->module = $module;
		$this->channels_mapping = $channels_mapping;
		$this->vress_data = $vress_data;
		$this->multi_hotels = $multi_hotels;
		$this->multi_haccount_id = $multi_haccount_id;
		$this->tac_rooms_mapped = $tac_rooms_mapped;
		$this->tac_vbo_tot_rooms = $tac_vbo_tot_rooms;

		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTHOTELDETAILS'), 'vikchannelmanager');
		JToolBarHelper::apply('saveHotelDetails', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
