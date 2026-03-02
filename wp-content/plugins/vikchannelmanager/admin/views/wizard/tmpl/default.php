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

<div class="vcmwizardcontainer">
	
	<div class="vcmwizardapicontainer">
		
		<div class="vcmwizardapititlediv">
			<?php echo JText::_('VCMWIZARDAPIKEYTITLE'); ?>
		</div>
		
		<div class="vcmwizardapilabeldiv">
			<?php echo JText::_('VCMWIZARDAPIKEYLABEL'); ?>
		</div>
	
		<div class="vcmwizardapikeydiv">
			
			<div class="vcmapikeytextdiv">
				<div class="vcmorderapikeydiv">
					<div class="vcmorderapikeyinner">
						<span class="vcmorderapikeylabspan"><?php echo JText::_('VCMAPIKEY'); ?>:</span>
						<div class="vcmorderapikeyinner-wizard">
							<span><?php echo class_exists('VikBookingIcons') ? '<i class="' . VikBookingIcons::i('key') . '"></i>' : ''; ?></span>
							<input class="vcmorderapikeyvalinput" name="apikey" size="28" id="vcmapikeytext" placeholder="API Key"/>
						</div>
					</div>
				</div>
			</div>
			
			<div class="vcmapikeyactiondiv">
				<a href="javascript: void(0);" onClick="validateApiKey();"><?php echo JText::_('VCMWIZARDINSERTBUTTON'); ?></a>
			</div>
			
		</div>
		
		<div class="vcmwizarderrordiv">
			
		</div>

		<div class="vcm-wizard-getapikey">
			<span class="vcm-wizard-getapikey-info"><?php echo JText::_('VCMWIZGETAPIKEY'); ?></span>
			<span class="vcm-wizard-getapikey-link"><a href="https://e4jconnect.com" target="_blank">https://e4jConnect.com</a></span>
		</div>
		
		<div class="vcmwizardchannelsdiv" style="display: none;">
			
			<span class="vcmchannelspan"><a class="vcmchannel" href="javascript: void(0);" id="vcmstartchannel"><?php echo JText::_('VCMGETCHANNELS'); ?></a></span>
			<span class="vcmchannelrs" id="vcmchars"></span>
			
		</div>
		
	</div>

</div>

<script type="text/javascript">

	function validateApiKey() {
		
		jQuery('.vcmwizarderrordiv').html('');
		jQuery('#vcmapikeytext').removeClass('vcmapirefused');
		jQuery('#vcmapikeytext').removeClass('vcmapiaccepted');
		jQuery('#vcmapikeytext').addClass('vcmapiloading');
		
		var apikey = jQuery('#vcmapikeytext').val();
		
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikchannelmanager", task: "wizard_store_api_key", apikey: apikey, tmpl: "component" }
		}).done(function(res) { 
			var obj = JSON.parse(res);
			
			jQuery('#vcmapikeytext').removeClass('vcmapiloading');
			
			if( obj[0] ) {
				jQuery('#vcmapikeytext').prop('readonly', true);
				
				jQuery('#vcmapikeytext').addClass('vcmapiaccepted');
				jQuery('.vcmapikeyactiondiv').fadeOut();
				jQuery('.vcm-wizard-getapikey').fadeOut();
				jQuery('.vcmwizardchannelsdiv').fadeIn();
			} else {
				jQuery('#vcmapikeytext').addClass('vcmapirefused');
				jQuery('.vcmwizarderrordiv').html(obj[1]);
			}
		}).fail(function(res) { 
			jQuery('.vcmwizarderrordiv').html(res);
		});
		
	}

	jQuery(function() {
		jQuery("#vcmstartchannel").click(function() {
			jQuery(".vcmchannelspan").removeClass("vcmchannelspansuccess");
			jQuery(".vcmchannelspan").removeClass("vcmchannelspanerror").addClass("vcmchannelspanloading");
			jQuery("#vcmstartchannel").text('<?php echo addslashes(JText::_('VCMSCHECKAPIEXPDATELOAD')); ?>');
			jQuery("#vcmchannelrs").html("");

			VBOCore.doAjax(
				"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=exec_cha'); ?>",
				{
					tmpl: 'component',
				},
				(res) => {
					jQuery("#vcmstartchannel").text('<?php echo addslashes(JText::_('VCMGETCHANNELS')); ?>');
					jQuery(".vcmchannelspan").removeClass("vcmchannelspanloading");
					if (res.substr(0, 9) == 'e4j.error') {
						jQuery(".vcmchannelspan").addClass("vcmchannelspanerror");
						jQuery("#vcmchars").html("<pre class='vcmpreerror'>" + res.replace("e4j.error.", "") + "</pre>");
					} else {
						jQuery(".vcmchannelspan").addClass("vcmchannelspansuccess");
						jQuery("#vcmchars").html(res);
						setTimeout("document.location.href='index.php?option=com_vikchannelmanager&fromwizard=1'", 5000);
					}
				},
				(error) => {
					console.error(error);
					jQuery("#vcmstartchannel").text('<?php echo addslashes(JText::_('VCMGETCHANNELS')); ?>');
					jQuery(".vcmchannelspan").removeClass("vcmchannelspanloading").addClass("vcmchannelspanerror");
					alert("Error Performing Ajax Request");
				}
			);
		});
	});

</script>
