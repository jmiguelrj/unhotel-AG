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

class VikBookingViewManageoptional extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$allrooms = array();
		$tot_rooms = 0;
		$tot_rooms_options = 0;
		$dbo = JFactory::getDbo();

		// read all rooms
		$q = "SELECT `id`, `name`, `idopt` FROM `#__vikbooking_rooms`;";
		$dbo->setQuery($q);
		$records = $dbo->loadAssocList();
		if ($records) {
			foreach ($records as $r) {
				$r['idopt'] = empty($r['idopt']) ? array() : explode(';', rtrim($r['idopt'], ';'));
				$allrooms[$r['id']] = $r;
			}
			$tot_rooms = count($allrooms);
		}

		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$row = $dbo->loadAssoc();
			if (!$row) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
				exit;
			}
			$q = "SELECT `idopt` FROM `#__vikbooking_rooms` WHERE `idopt` LIKE ".$dbo->quote("%".$row['id'].";%").";";
			$dbo->setQuery($q);
			$all_opt = $dbo->loadAssocList();
			if ($all_opt) {
				foreach ($all_opt as $k => $v) {
					$opt_parts = explode(';', $v['idopt']);
					if (in_array((string)$row['id'], $opt_parts)) {
						$tot_rooms_options++;
					}
				}
			}
		}

		// read all payment methods
		$dbo->setQuery(
			$dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikbooking_gpayments'))
				->order($dbo->qn('published') . ' DESC')
				->order($dbo->qn('name') . ' ASC')
		);
		$payment_methods = $dbo->loadAssocList();
		
		$this->row = $row;
		$this->tot_rooms = $tot_rooms;
		$this->tot_rooms_options = $tot_rooms_options;
		$this->allrooms = $allrooms;
		$this->payment_methods = $payment_methods;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar()
	{
		$cid = VikRequest::getVar('cid', array(0));

		if (!empty($cid[0])) {
			//edit
			JToolBarHelper::title(JText::_('VBMAINOPTTITLEEDIT'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply('updateoptionalstay', JText::_('VBSAVE'));
				JToolBarHelper::save('updateoptional', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('canceloptionals', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINOPTTITLENEW'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save('createoptionals', JText::_('VBSAVECLOSE'));
				JToolBarHelper::apply('createoptionalsstay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('canceloptionals', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		}
	}
}
