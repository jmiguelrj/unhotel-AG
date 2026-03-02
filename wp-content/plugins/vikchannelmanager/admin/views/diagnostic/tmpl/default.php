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

$date_format = VikChannelManager::getClearDateFormat(true);

?>

<button type="button" class="btn vcm-diagnostic-btn" onClick="launchInputOutputDiagnostic();"><?php echo JText::_("VCMSTARTIODIAGNOSTICBTN"); ?></button>

<div class="vcm-diagnostic-goodresponse" style="display: none;">
	<div class="head-title"><?php echo JText::_('VCMIODIAGNOSTICGOODTITLE'); ?></div>
	<div class="body-content"></div>
</div>

<div class="vcm-diagnostic-badresponse" style="display: none">
	<div class="head-title"><?php echo JText::_('VCMIODIAGNOSTICBADTITLE'); ?></div>
	<div class="body-content"></div>
</div>

<script>

	function launchInputOutputDiagnostic() {

		enableDiagnosticButton(false);

		jQuery.noConflict();

		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_vikchannelmanager&task=input_output_diagnostic&tmpl=component",
			data: { }
		}).done(function(res) { 

			if( res.substr(0, 9) == 'e4j.error' ) {
				jQuery('.vcm-diagnostic-badresponse .body-content').html( res.substr(10) );
				jQuery('.vcm-diagnostic-badresponse').show();
			} else {
				var obj = JSON.parse(res);
				jQuery('.vcm-diagnostic-goodresponse .body-content').html( obj );
				jQuery('.vcm-diagnostic-goodresponse').show();
			}

			enableDiagnosticButton(true);

		}).fail(function(res) { 
			alert(res);

			enableDiagnosticButton(true);
		});

	}

	function enableDiagnosticButton(status) {
		jQuery('.vcm-diagnostic-btn').prop('disabled', (status ? false : true));

		if( !status ) {
			jQuery('.vcm-diagnostic-goodresponse, .vcm-diagnostic-badresponse').hide();
		}
	}

</script>
