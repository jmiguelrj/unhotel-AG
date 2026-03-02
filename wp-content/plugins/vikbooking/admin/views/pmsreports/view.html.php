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

class VikBookingViewPmsreports extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		if (VBOPlatformDetection::isWordPress()) {
			/**
			 * @wponly - trigger back up of extendable files
			 */
			VikBookingLoader::import('update.manager');
			VikBookingUpdateManager::triggerExtendableClassesBackup('report');
		}

		// load all the available PMS Reports
		list($report_objs, $country_objs) = VBOReportLoader::getInstance()->getDrivers();

		// get countries involved
		$countries = VBOReportLoader::getInstance()->getInvolvedCountries();

		$this->report_objs = $report_objs;
		$this->country_objs = $country_objs;
		$this->countries = $countries;

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('VBMAINPMSREPORTSTITLE'), 'vikbooking');
		JToolBarHelper::cancel( 'canceldash', JText::_('VBBACK'));
		JToolBarHelper::spacer();
	}
}
