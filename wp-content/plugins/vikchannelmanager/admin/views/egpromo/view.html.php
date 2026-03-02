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

class VikChannelManagerViewEgpromo extends JViewUI {
	
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
		if (!isset($channel['params']['hotelid']) || empty($channel['params']['hotelid'])) {
			VikError::raiseWarning('', 'Empty Hotel ID for Expedia.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		// load the information about the rate plans from rooms mapping
		$otarooms = array();
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".VikChannelManagerConfig::EXPEDIA.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$otarooms = $dbo->loadAssocList();
		}
		
		// empty string means that the request has to be performed
		$promotions = $session->get('vcmEGPromo', '');

		// promotions loading command
		$loadpromo = VikRequest::getInt('loadpromo', 0, 'request');

		// promotions data set by the egpromo controller
		$egpdata = $session->get('vcmEGPData', '');
		// success value in query string for redirect from egpromomanage view
		$success_str = VikRequest::getString('success', '', 'request');
		if (!empty($egpdata) && $egpdata == $success_str) {
			// print a success message and force the re-load of the promotions
			$mainframe->enqueueMessage(JText::sprintf('VCMBPROMUPDSUCCRQ', $egpdata));
			$loadpromo = 1;
		}

		
		if ($loadpromo > 0) {
			// make the request to e4jConnect to load the Promotions
			
			// required filter by hotel ID
			$filters = array('hotelid="'.$channel['params']['hotelid'].'"');

			// test properties with a username and password can pass it for authentication
			if (!empty($channel['params']['username'])) {
				array_push($filters, 'name="' . $channel['params']['username'] . '"');
			}
			if (!empty($channel['params']['password'])) {
				array_push($filters, 'auth="' . $channel['params']['password'] . '"');
			}

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
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=egpromo");
				exit;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=egpromo");
				exit;
			}
			
			/**
			 * The response could be a count-0 array in case of no promotions, or
			 * an array of promotion objects if some were found on Expedia.
			 * A string would mean an invalid response.
			 */
			$promotions = unserialize($rs);
			if ($promotions === false || !is_array($promotions)) {
				VikError::raiseWarning('', 'Invalid response.<br/>'.$rs);
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=egpromo");
				exit;
			}

			$session->set('vcmEGPromo', $promotions);
		}

		$this->config = $config;
		$this->channel = $channel;
		$this->promotions = $promotions;
		$this->otarooms = $otarooms;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::sprintf('VCMMAINTCHPROMOTIONS', 'Expedia'), 'vikchannelmanager');
		JToolBarHelper::addNew('egpromonew', JText::_('NEW'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
		
	}
}
