<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikBookingViewEinvoicing extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$driver_objs = array();
		
		$driver_base = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'einvoicing' . DIRECTORY_SEPARATOR;

		// require the parent abstract class
		require_once $driver_base . 'einvoicing.php';

		$driver_base .= 'drivers' . DIRECTORY_SEPARATOR;
		$driver_files = glob($driver_base . '*.php');

		/**
		 * Trigger event to let other plugins register additional drivers.
		 *
		 * @return 	array 	A list of supported drivers.
		 */
		$list = VBOFactory::getPlatform()->getDispatcher()->filter('onLoadEinvoicingDrivers');
		foreach ($list as $chunk) {
			// merge default driver files with the returned ones
			$driver_files = array_merge($driver_files, (array)$chunk);
		}

		foreach ($driver_files as $k => $driver_path) {
			try {
				$driver_file = basename($driver_path, '.php');

				// require driver class file
				if (is_file($driver_path)) {
					require_once($driver_path);
				}

				$classname = 'VikBookingEInvoicing' . str_replace(' ', '', ucwords(str_replace('_', ' ', $driver_file)));
				if (class_exists($classname)) {
					$einv_driver = new $classname();
					// push driver
					array_push($driver_objs, $einv_driver);
				}
			} catch (Exception $e) {
				// do nothing
			}
		}

		$this->driver_objs = $driver_objs;

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINEINVOICINGTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
			JToolBarHelper::addNew('newmaninvoice', JText::_('VBOMANUALINVOICE'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::cancel( 'canceldash', JText::_('VBBACK'));
		JToolBarHelper::spacer();
	}
}
