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

class VikChannelManagerViewDeclinebooking extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$ids = VikRequest::getVar('cid', array(0));

		if (empty($ids) || empty($ids[0])) {
			VikError::raiseWarning('', 'Missing booking ID for cancellation');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=" . (int)$ids[0] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'Booking ID not found for cancellation');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		$reservation = $dbo->loadAssoc();
		
		if ($reservation['status'] != 'cancelled' && !VikChannelManager::reservationNeedsDeclineReasons($reservation)) {
			/**
			 * Just let VBO remove the booking as decline reasons are not needed.
			 * Note: if we get here from a redirect after a successful deny action,
			 * the status of the booking will be cancelled! So we cannot just use
			 * reservationNeedsDeclineReasons(), we also need to make sure booking
			 * has not been cancelled already.
			 */
			VikError::raiseWarning('', 'Booking does not need decline reasons and so it can just be cancelled');
			$app->redirect("index.php?option=com_vikbooking&task=removeorders&cid[]=" . $reservation['id']);
			exit;
		}

		/**
		 * For the moment only Airbnb API supports decline reasons for pending reservations.
		 * If any other channel in the future will support this feature, the channel details
		 * will need to be loaded by using the channel name contained in $reservation['channel'].
		 * 
		 * @since 	1.8.0
		 */
		$channel = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($channel) || !count($channel)) {
			// just let VBO remove the booking as decline reasons are not needed
			VikError::raiseWarning('', 'No valid channels available to provide a decline reason for the booking, so it can just be cancelled');
			$app->redirect("index.php?option=com_vikbooking&task=removeorders&cid[]=" . $reservation['id']);
			exit;
		}
		
		$this->reservation = $reservation;
		$this->channel = $channel;

		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTDECLINEBOOKING'), 'vikchannelmanager');
		JToolBarHelper::apply('doDeclineBooking', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancelDeclineBooking', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
