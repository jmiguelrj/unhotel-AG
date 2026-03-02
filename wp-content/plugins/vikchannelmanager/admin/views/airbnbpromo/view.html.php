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

class VikChannelManagerViewAirbnbpromo extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		
		if (!function_exists('curl_init')) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		
		$config = VikChannelManager::loadConfiguration();
		$validate = array('apikey');
		foreach ($validate as $v) {
			if (empty($config[$v])) {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
				$mainframe->redirect("index.php?option=com_vikchannelmanager");
				exit;
			}
		}
		
		$q = "SELECT `vbr`.`id`,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikbooking_rooms` AS `vbr` ORDER BY `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$vbrooms = $dbo->loadAssocList();
		} else {
			VikError::raiseWarning('', 'There are no rooms in VikBooking.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id'])) {
			VikError::raiseWarning('', 'Empty User ID for Airbnb.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		// load the information about the rate plans from rooms mapping
		$otarooms = array();
		$otalistings = array();
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".VikChannelManagerConfig::AIRBNBAPI.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$otarooms = $dbo->loadAssocList();
			foreach ($otarooms as $otaroom) {
				$otalistings[$otaroom['idroomota']] = $otaroom['otaroomname'];
			}
		}
		
		// empty string means that the request has to be performed
		$promotions = $session->get('vcmAirbnbPromo', '');

		// promotions loading command
		$loadpromo = VikRequest::getInt('loadpromo', 0, 'request');

		// promotions data set by the airbnbpromo controller
		$airbnbpdata = $session->get('vcmAirbnbPData', '');
		// success value in query string for redirect from airbnbpromomanage view
		$success_str = VikRequest::getString('success', '', 'request');
		if (!empty($airbnbpdata) && $airbnbpdata == $success_str) {
			// print a success message and force the re-load of the promotions
			$mainframe->enqueueMessage(JText::sprintf('VCMBPROMUPDSUCCRQ', $airbnbpdata));
			$loadpromo = 1;
		}

		
		if ($loadpromo > 0) {
			// make the request to e4jConnect to load the Promotions
			
			// required filter by hotel ID (host user id)
			$filters = array('hotelid="'.$channel['params']['user_id'].'"');

			// filters (optional)
			$promoid = VikRequest::getInt('promoid', 0, 'request');
			if (!empty($promoid)) {
				array_push($filters, 'id="'.$promoid.'"');
			}
			
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=rprom&c=".$channel['name'];
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager RPROM Request e4jConnect.com - '.ucwords($channel['name']).' -->
<ReadPromotionsRQ xmlns="http://www.e4jconnect.com/channels/rpromrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$config['apikey'].'"/>
	<ReadPromotions>
		<Fetch '.implode(' ', $filters).'/>
	</ReadPromotions>
</ReadPromotionsRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnbpromo");
				exit;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnbpromo");
				exit;
			}
			
			/**
			 * The response could be a count-0 array in case of no promotions, or
			 * an array of promotion objects if some were found on Airbnb.
			 * A string would mean an invalid response.
			 */
			$promotions = unserialize($rs);
			if ($promotions === false || !is_array($promotions)) {
				VikError::raiseWarning('', 'Invalid response.<br/>'.$rs);
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnbpromo");
				exit;
			}

			$session->set('vcmAirbnbPromo', $promotions);
		}

		if (is_array($promotions)) {
			// filter duplicate promo IDs
			$airbnb_promo_ids = [];
			foreach ($promotions as $ind => $promo) {
				if (!is_object($promo) || !($promo->id ?? '')) {
					continue;
				}
				if (in_array($promo->id, $airbnb_promo_ids)) {
					unset($promotions[$ind]);
					continue;
				}
				$airbnb_promo_ids[] = $promo->id;
			}
			$promotions = array_values($promotions);
		}

		$this->config = $config;
		$this->channel = $channel;
		$this->promotions = $promotions;
		$this->otarooms = $otarooms;
		$this->otalistings = $otalistings;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::sprintf('VCMMAINTCHPROMOTIONS', 'Airbnb'), 'vikchannelmanager');
		JToolBarHelper::addNew('airbnbpromonew', JText::_('NEW'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
		
	}
}
