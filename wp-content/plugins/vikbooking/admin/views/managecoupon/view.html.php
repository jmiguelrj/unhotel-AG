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

class VikBookingViewManagecoupon extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$coupid = $cid[0];
		}

		$dbo = JFactory::getDbo();

		$coupon = [];
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_coupons` WHERE `id`=".(int)$coupid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$coupon = $dbo->loadAssoc();
			}
		}
		$wselrooms = "";
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rooms = $dbo->loadAssocList();
			$filterroomr = [];
			if (count($coupon) && strlen($coupon['idrooms']) > 0) {
				$cparts = explode(";", $coupon['idrooms']);
				foreach ($cparts as $fc) {
					if (!empty($fc)) {
						$filterroomr[] = $fc;
					}
				}
			}
			$wselrooms = "<select name=\"idrooms[]\" multiple=\"multiple\" size=\"5\">\n";
			foreach ($rooms as $c) {
				$wselrooms .= "<option value=\"".$c['id']."\"".(in_array($c['id'], $filterroomr) ? " selected=\"selected\"" : "").">".$c['name']."</option>\n";
			}
			$wselrooms .= "</select>\n";
		}

		$coupon_customers = [];
		$use_count = 0;

		if (!empty($coupon['id'])) {
			/**
			 * Customers currently assigned to this coupon.
			 * 
			 * @since 	1.16.0 (J) - 1.6.0 (WP)
			 */
			$q = "SELECT `c`.`id`, `c`.`first_name`, `c`.`last_name`, `ccp`.`idcoupon`, `ccp`.`automatic`, 
				(SELECT COUNT(*) FROM `#__vikbooking_customers_orders` AS `co` WHERE `co`.`idcustomer`=`c`.`id`) AS `tot_bookings` 
				FROM `#__vikbooking_customers` AS `c` 
				LEFT JOIN `#__vikbooking_customers_coupons` AS `ccp` ON `c`.`id`=`ccp`.`idcustomer` 
				WHERE `ccp`.`idcoupon`={$coupon['id']} 
				ORDER BY `c`.`first_name` ASC, `c`.`last_name` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$customers = $dbo->loadAssocList();
				foreach ($customers as $k => $customer) {
					$customers[$k]['text'] = trim($customer['first_name'] . ' ' . $customer['last_name']) . ' (' . $customer['tot_bookings'] . ')';
				}
				$coupon_customers = $customers;
			}

			// count number of uses
			$q = "SELECT COUNT(*) FROM `#__vikbooking_orders` WHERE `coupon` LIKE " . $dbo->quote("%;{$coupon['code']}");
			$dbo->setQuery($q);
			$dbo->execute();
			$use_count = (int)$dbo->loadResult();
		}

		$this->coupon = $coupon;
		$this->wselrooms = $wselrooms;
		$this->coupon_customers = $coupon_customers;
		$this->use_count = $use_count;

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		$cid = VikRequest::getVar('cid', array(0));
		
		if (!empty($cid[0])) {
			//edit
			JToolBarHelper::title(JText::_('VBMAINCOUPONTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply('updatecoupon_stay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save('updatecoupon', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('cancelcoupon', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINCOUPONTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save('createcoupon', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcoupon', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		}
	}
}
