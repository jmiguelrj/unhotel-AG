<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_otareviews
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2018 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// no direct access
defined('ABSPATH') or die('No script kiddies please!');

jimport('joomla.form.formfield.list');

class JFormFieldChannels extends JFormFieldList
{
	protected $type = 'channels';

	/**
	 * @inheritDoc
	 *
	 * @since 1.15.2
	 */
	public function getOptions()
	{
		$dbo = JFactory::getDbo();
		
		$channels = [];

		// load all channel names from mapped room types
		try {
			if (VBOPlatformDetection::isJoomla() || (defined('VCM_ADMIN_PATH') && file_exists(VCM_ADMIN_PATH))) {
				// run the query on Joomla or on WP if VCM is installed (to avoid database error messages)
				$q = "SELECT `idchannel` AS `value`, CONCAT(UPPER(LEFT(`channel`, 1)), LOWER(SUBSTRING(`channel` FROM 2))) AS `title` FROM `#__vikchannelmanager_roomsxref` GROUP BY `idchannel` ORDER BY `channel` ASC;";
				$dbo->setQuery($q);
				
				foreach ($records = $dbo->loadAssocList() as $v) {
					$channels[$v['value']] = $v['title'];
				}
			}
		} catch (Exception $e) {
			// VCM is not installed
			// if (defined('_JEXEC')) {
				// print an arror message if we are on Joomla
				// JFactory::getApplication()->enqueueMessage('Vik Channel Manager not installed or not configured. Only sample channels and data available.', 'notice');
			// }
		}

		if (!$channels) {
			// add sample data and print error message
			// $html .= '<div style="width: 100%; font-weight: bold; border-bottom: 2px solid #12728b; color: #ff0000; padding: 5px 0; text-align: left; margin-bottom:10px;">Channel Manager not installed or not configured. Only sample channels available.</div>';

			// push sample channels
			$channels = [
				4  => 'Booking.com',
				25 => 'Airbnb',
				1  => 'Expedia',
				23 => 'Hostelworld',
				12 => 'Agoda',
				15 => 'Despegar',
				21 => 'Pitchup.com',
			];
		}
		
		// build the list of options
		// $opt_str = '';
		// foreach ($channels as $k => $v) {
		// 	$opt_str .= '<option value="' . $k . '"'.((is_array($this->value) && in_array($k, $this->value)) || $this->value == $k ? " selected=\"selected\"" : "").'>' . htmlspecialchars(ucwords($v)) . '</option>';
		// }

		// $html .= '<select class="inputbox vbo-mod-channelrates-sel" name="' . $this->name . (substr($this->name, -2) != '[]' ? '[]' : '') . '" multiple="multiple" size="6">';
		// $html .= '<option value=""></option>';
		// $html .= $opt_str;
		// $html .='</select>';

		// load existing options from parent first
		$options = parent::getOptions();

		foreach ($channels as $value => $text) {
			$options[] = JHtml::_('select.option', $value, $text);
		}

		return $options;
    }
}
