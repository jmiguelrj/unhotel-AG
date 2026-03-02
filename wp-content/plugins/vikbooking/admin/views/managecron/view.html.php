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

class VikBookingViewManagecron extends JViewVikBooking
{	
	function display($tpl = null)
	{
		$id = VikRequest::getVar('cid', []);

		if ($id)
		{
			// fetch cron job data on update
			$this->row = (array) VBOMvcModel::getInstance('cronjob')->getItem((int) $id[0]);
		}
		else
		{
			$this->row = [];
		}

		// fetch all the supported cron job drivers
		$this->supportedDrivers = VBOFactory::getCronFactory()->getInstances();

		// Set the toolbar
		$this->addToolBar();
		
		// display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar()
	{	
		if ($this->row)
		{
			// edit
			JToolBarHelper::title(JText::_('VBMAINCRONSTITLE'), 'vikbooking');
		}
		else
		{
			// new
			JToolBarHelper::title(JText::_('VBMAINCRONSTITLE'), 'vikbooking');
		}

		$user = JFactory::getUser();

		if (($user->authorise('core.edit', 'com_vikbooking') && !empty($this->row['id'])) || $user->authorise('core.create', 'com_vikbooking'))
		{
			JToolBarHelper::apply('cronjob.save', JText::_('VBSAVE'));
			JToolBarHelper::save('cronjob.saveclose', JText::_('VBSAVECLOSE'));
		}

		JToolBarHelper::cancel('cronjob.cancel', JText::_('VBBACK'));
	}
}
