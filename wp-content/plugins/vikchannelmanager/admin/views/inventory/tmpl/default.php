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

$app = JFactory::getApplication();

JHtml::_('behavior.tooltip');

$rooms = $this->rooms;

$curr_symb = VikChannelManager::getCurrencySymb(true);

$ta_room_amenities = VikChannelManagerConfig::$TA_ROOM_AMENITIES;
uasort($ta_room_amenities, array("VikChannelManagerConfig", "compareRoomAmenities"));

asort(VikChannelManagerConfig::$TA_ROOM_CODES);

/**
 * Add support to multiple TripAdvisor (TripConnect) accounts.
 * 
 * @since 	1.9.10
 */
$active_account_id = $app->input->getString('active_account_id');
$ta_account_id = (string) ($active_account_id ?: $this->module['params']['tripadvisorid'] ?? '');

if ($this->multi_hotels) {
	?>
<div class="vcm-admin-container">
	<div class="vcm-config-maintab-left">
		<fieldset class="adminform">
			<div class="vcm-params-wrap">
				<legend class="adminlegend">TripAdvisor Account ID</legend>
				<div class="vcm-params-container">
					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMSELECTACCOUNT'); ?></div>
						<div class="vcm-param-setting">
							<select id="multi-hotel-account-val" onchange="vcmSelectMultiHotelAccount(this.value);">
								<option value=""><?php echo VCMGhotelMultiaccounts::getMainHotelName() . ' (' . VCMFactory::getConfig()->get('tac_partner_ta_id', 'First Account') . ')'; ?></option>
							<?php
							foreach ($this->multi_hotels as $multi_hotel) {
								?>
								<option value="<?php echo $multi_hotel['account_id']; ?>"<?php echo $ta_account_id == $multi_hotel['account_id'] ? ' selected="selected"' : ''; ?>><?php echo $multi_hotel['hname'] . ' (' . $multi_hotel['account_id'] . ')'; ?></option>
								<?php
							}
							?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>
</div>

<a href="index.php?option=com_vikchannelmanager&task=inventory" id="vcm-inventory-base-uri" style="display: none;"></a>

<script type="text/javascript">
	function vcmSelectMultiHotelAccount(id) {
		var base_uri = document.getElementById('vcm-inventory-base-uri').getAttribute('href');
		if (!id || !id.length) {
			// main account selected
			document.location.href = base_uri;
			return;
		}
		base_uri += '&active_account_id=' + id;
		document.location.href = base_uri;
		return;
	}
</script>
	<?php
}

?>

<form action="index.php" name="adminForm" id="adminForm" method="POST">
	
	<div class="vcmactionstoolbar">
		
		<div class="vcmtacroomheadtitle"><?php echo JText::_('VCMTACROOMSHEADTITLE'); ?></div>
		
		<div class="vcmtacroompuballdiv">
			<a href="javascript: void(0);" onClick="changeAllRoomsStatus(1);" class="vcmtripstatuslinkactive"><?php echo JText::_('VCMTACROOMBTNPUBALL'); ?></a>
		</div>
		
		<div class="vcmtacroomunpuballdiv">
			<a href="javascript: void(0);" onClick="changeAllRoomsStatus(0);" class="vcmtripstatuslinkunactive"><?php echo JText::_('VCMTACROOMBTNUNPUBALL'); ?></a>
		</div>
	</div>
	
	<?php if( count($rooms) == 0 ) { ?>
	    <div class="vcminventorynoroom"><?php echo JText::_("VCMINVENTORYNOROOM"); ?></div>
	<?php } ?>

	<div class="vcmtacallrooms">
		
		<?php $i = 0; ?>
		<?php foreach( $rooms as $r ) { ?>
			
			<div class="vcmtacroomdiv <?php echo (($r['tac_room_id'] != 0) ? 'vcmroomactive' : 'vcmroomunactive'); ?>" id="vcmroom<?php echo $i; ?>">
			
				<div class="vcmtacroomtopdiv">
					<div class="vcmtacroomimagediv">
						<?php if( !empty($r['img']) ) { ?>
							<img style="max-height: 190px;" src="<?php echo VBO_SITE_URI.'resources/uploads/'.$r['img']; ?>" />
						<?php } ?>
						<input type="hidden" name="image[]" value="<?php echo $r['img']; ?>"/>
					</div>
					
					<div class="vcmtacroomdetailsdiv">
						<div class="vcmtacroomnamediv">
							<input type="text" name="name[]" value="<?php echo $r['name']; ?>" placeholder="<?php echo JText::_('VCMTACROOMDETNAME'); ?>" class="vcmroomdetinput"/>
						</div>
						
						<label class="vcmtacroomcostlabel">
					     	<span class="vcmtacroomcostspan"><?php echo $curr_symb; ?></span>
					      	<input type="text" name="cost[]" value="<?php echo $r['cost']; ?>" placeholder="<?php echo JText::_('VCMTACROOMDETCOST'); ?>" class="vcmroomdetinput"/>
					    </label>
						
						<div class="vcmtacroomcodediv">
							<?php echo VikChannelManager::composeSelectRoomCodes('codes[]', VikChannelManagerConfig::$TA_ROOM_CODES, $r['codes'], 'vcmroomdetinput', true); ?>
						</div>
					</div>
				</div>
				
				<div class="vcmtacroomurldiv">
					<input type="text" name="url[]" value="<?php echo $r['url']; ?>" size="32" readonly placeholder="<?php echo JText::_('VCMTACROOMDETURL'); ?>" class="vcmroomdetinput"/>
				</div>
				
				<div class="vcmtacroomdescdiv">
					<textarea name="desc[]" style="max-height: 70px;" placeholder="<?php echo JText::_('VCMTACROOMDETDESC'); ?>" class="vcmroomdetinput"><?php echo $r['smalldesc'] ?></textarea>
				</div>
				
				<div class="vcmtacroomamenitiesdiv">
					<?php echo VikChannelManager::composeSelectAmenities('amenities['.($i).'][]', $ta_room_amenities, $r['amenities'], 'vcmroomdetinput vcm-multi-select', true); ?>
				</div>
				
				<div class="vcmtacroomtriplogodiv">
					<a href="javascript: void(0);" onClick="changeRoomStatus(<?php echo $i; ?>);" id="vcmtripstatuslink<?php echo $i; ?>" class="vcmtripstatuslink"><?php echo JText::_(($r['tac_room_id'] != 0) ? 'VCMTACROOMPUBLISHED' : 'VCMTACROOMUNPUBLISHED'); ?></a>
				</div>
				
				<input type="hidden" name="status[]" value="<?php echo (($r['tac_room_id'] != 0) ? 1 : 0); ?>" id="vcmroomtacstatus<?php echo $i; ?>" class="vcmroomtacstatushidden" />
				<input type="hidden" name="vb_room_id[]" value="<?php echo $r['id']; ?>" />
				<input type="hidden" name="tac_room_id[]" value="<?php echo $r['tac_room_id']; ?>" id="vcmroomtacid<?php echo $i; ?>"/>
			
			</div>
			
			<?php $i++; ?>
			
		<?php } ?>
		
	</div>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="ta_account_id" value="<?php echo $ta_account_id; ?>" />
	
</form>

<script>
	
	function changeRoomStatus(id) {
		var status = (parseInt(jQuery('#vcmroomtacstatus'+id).val()) + 1)%2;
		
		jQuery('#vcmroomtacstatus'+id).val(status);
		if( status ) {
			jQuery('#vcmroom'+id).removeClass('vcmroomunactive');
			jQuery('#vcmroom'+id).addClass('vcmroomactive');
			jQuery('#vcmtripstatuslink'+id).html('<?php echo addslashes(JText::_('VCMTACROOMPUBLISHED')); ?>');
		} else {
			jQuery('#vcmroom'+id).removeClass('vcmroomactive');
			jQuery('#vcmroom'+id).addClass('vcmroomunactive');
			jQuery('#vcmtripstatuslink'+id).html('<?php echo addslashes(JText::_('VCMTACROOMUNPUBLISHED')); ?>');
		}
		
	}
	
	function changeAllRoomsStatus(status) {
		jQuery('.vcmroomtacstatushidden').val(status);
		if( status ) {
			jQuery('.vcmtacroomdiv').removeClass('vcmroomunactive');
			jQuery('.vcmtacroomdiv').addClass('vcmroomactive');
			jQuery('.vcmtripstatuslink').html('<?php echo addslashes(JText::_('VCMTACROOMPUBLISHED')); ?>');
		} else {
			jQuery('.vcmtacroomdiv').removeClass('vcmroomactive');
			jQuery('.vcmtacroomdiv').addClass('vcmroomunactive');
			jQuery('.vcmtripstatuslink').html('<?php echo addslashes(JText::_('VCMTACROOMUNPUBLISHED')); ?>');
		}
	}

	jQuery(function() {
		jQuery('.vcm-multi-select').select2({
			allowClear: false,
			placeholder: "<?php echo addslashes(JText::_('VCMTACROOMAMENITIES')); ?>",
			width: 300
		});
	});
	
</script>
