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

$valid_rate_plans = array_key_exists('otapricing', $this->roomsrel) && !empty($this->roomsrel['otapricing']) ? true : false;
if ($valid_rate_plans) {
	$this->roomsrel['otapricing'] = json_decode($this->roomsrel['otapricing'], true);
	$valid_rate_plans = is_array($this->roomsrel['otapricing']) && count($this->roomsrel['otapricing']) > 0 ? true : false;
}

if (count($this->roomsrel) == 0 && $valid_rate_plans === true) {
	?>
	<p class="vcmfatal"><?php echo JText::_('VCMNOROOMSASSOCFOUND'); ?></p>
	<?php
} else {
	?>
	<div class="vcm-roomsrelrplan-cont">
		<p class="vcm-roominfo-paragraph"><?php echo JText::_('VCMROOMSRELRPLANS'); ?></p>
	<?php
	$def_found = false;
	foreach ($this->roomsrel['otapricing']['RatePlan'] as $rplan_k => $rplan_v) {
		$is_def = (array_key_exists('vcm_default', $rplan_v) && !$def_found);
		?>
		<div class="vcm-roominfo-block<?php echo $is_def ? ' vcm-roomsrelrplan-default-block' : ''; ?>">
			<div class="vcm-roomsrelrplan-checkdefault">
		<?php
		if ($is_def) {
			$def_found = true;
			?>
				<span class="vcm-roomsrelrplan-curdef"><i class="vboicn-star-full"></i><?php echo JText::_('VCMROOMSRELDEFRPLAN'); ?></span>
			<?php
		} else {
			?>
				<button type="button" class="btn btn-light vcm-roomsrelrplan-setdef" data-vcmreldata="<?php echo $this->roomsrel['id'].'_'.$rplan_k; ?>"><i class="vboicn-star-empty"></i><?php echo JText::_('VCMROOMSRELDEFRPLANSET'); ?></button>
			<?php
		}
		?>
			</div>
		<?php
		foreach ($rplan_v as $rinfo_k => $rinfo_v) {
			if ($rinfo_k == 'vcm_default') {
				continue;
			}
			?>
			<div class="vcm-roominfo-entry">
				<span><?php echo ucwords(str_replace('_', ' ', $rinfo_k)); ?>:</span>
				<?php echo $rinfo_v; ?>
			</div>
			<?php
		}
		?>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
?>
<script type="text/javascript">
jQuery('.vcm-roomsrelrplan-setdef').click(function() {
	jQuery(this).prop("disabled", true);
	var reldata = jQuery(this).attr('data-vcmreldata');
	var relid = reldata.split("_");
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikchannelmanager", task: "rooms_rel_rplan_setdef", reldata: reldata<?php echo isset($_REQUEST['e4j_debug']) && (int)$_REQUEST['e4j_debug'] == 1 ? ', e4j_debug: 1' : ''; ?>, tmpl: "component" }
	}).done(function(res) {
		if (res.substr(0, 9) == 'e4j.error') {
			alert(res.replace("e4j.error.", ""));
		} else {
			//Trigger parent window to reload the modal box
			jQuery(".vcmshowinfo[data-vcmrelid='"+relid[0]+"']").trigger("click");
		}
	}).fail(function() {
		alert("Error Performing Ajax Request");
	});
});
</script>
