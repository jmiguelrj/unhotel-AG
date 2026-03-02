<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$vik = new VikApplication(VersionListener::getID());

// lang vars for JS
JText::script('VCMDISMISSCONF');

if (count($this->rows)) {
	?>
<div class="vcm-opps-description">
	<p><?php echo JText::_('VCMOPPORTUNITIESDESCR'); ?></p>
</div>

<div class="vcm-opps-container">
<?php
foreach ($this->rows as $opp) {
	$tmpl_name = 'generic';
	if (!empty($opp->channel) && stripos($opp->channel, 'website') === false) {
		// get apposite channel template file name
		$tmpl_name = preg_replace("/[^a-z0-9]/", '', strtolower($opp->channel));
	}
	// set vars for channel template file
	$this->opp = $opp;
	$this->channel_logo = VikChannelManager::getLogosInstance($opp->channel)->getLogoURL();
	// load the template within a try-catch
	try {
		// let the apposite channel template file render the opportunity
		echo $this->loadTemplate($tmpl_name);
	} catch (Exception $e) {
		// do nothing when template file not found, but raise an error
		VikError::raiseWarning('', 'Could not render opportunity ID ' . $opp->id . ' for channel ' . (!empty($opp->channel) ? $opp->channel : '-----'));
	}
}
?>
</div>
<?php
} else {
	?>
<p class="warn"><?php echo JText::_('VCMNOOPPSFOUND'); ?></p>
	<?php
}
?>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="task" value="opportunities" />
</form>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.vcm-opp-confirmaction').click(function() {
		if (confirm(Joomla.JText._('VCMDISMISSCONF'))) {
			jQuery(this).closest('.vcm-opp-element').addClass('vcm-opp-element-dismissed');
			return true;
		} else {
			return false;
		}
	});
	jQuery('.vcm-opp-setdone').click(function() {
		/**
		 * We change class for these elements only if they are implementations
		 * of type REDIRECT (Booking.com) or MANUAL (Airbnb API activation mode),
		 * meaning that they have target=_blank and data-manmode="1" attributes.
		 */
		var man_mode = jQuery(this).attr('data-manmode');
		if (man_mode && man_mode.length && man_mode == '1') {
			// this is a blank page, current page will NOT reload
			// set implemented class and hide buttons
			var block_elem = jQuery(this).closest('.vcm-opp-element');
			block_elem.addClass('vcm-opp-element-implemented').find('.vcm-opp-actions').hide();
			// move the entire opportunity block to the end of the list after half second
			setTimeout(function() {
				block_elem.appendTo('.vcm-opps-container');
			}, 500);
		}
	});
});
</script>
