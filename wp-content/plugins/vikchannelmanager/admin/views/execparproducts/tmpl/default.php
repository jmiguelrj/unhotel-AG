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

$config = $this->config;
$vbrooms = $this->vbrooms;
$channelrooms = $this->channelrooms;

$rooms_rate_plans = array();

if (isset($channelrooms['Hotel']) && count($channelrooms['Hotel'])) {
?>
<div class="vcmdivhotel">
	<h3><?php echo JText::_('VCMHOTELINFORETURNED'); ?></h3>
	<?php
	$prop_display_info = array();
	$prop_name_found = false;
	foreach ($channelrooms['Hotel'] as $hk => $hv) {
		// Property Name
		if ($prop_name_found === false && stripos($hk, 'name') !== false) {
			$prop_name_found = true;
			?>
		<input type="hidden" name="prop_name" value="<?php echo $hv; ?>" />
			<?php
		}

		/**
		 * Subscription Max_Rooms limit for the e4jConnect validation
		 * 
		 * @since 	1.6.11
		 */
		if ($hk == 'Max_Rooms') {
			echo '<input type="hidden" name="max_rooms" value="' . (int)$hv . '" />' . "\n";
			continue;
		}
		
		// push information to be displayed
		$prop_display_info[$hk] = $hv;
	}
	?>
	<div class="vcm-roomsmapping-property-details">
	<?php
	foreach ($prop_display_info as $prop_info => $prop_val) {
		$prop_val_css = '';
		if (stripos($prop_info, 'logo') !== false && strpos($prop_val, 'http') !== false) {
			$prop_val = '<img src="' . $prop_val . '" class="vcm-roomsmapping-owner-logo" />';
			$prop_info = '';
			$prop_val_css = ' vcm-roomsmapping-property-detail-logo';
		}
		?>
		<span class="vcm-roomsmapping-property-detail-wrap">
		<?php
		if (!empty($prop_info)) {
			?>
			<span class="vcm-roomsmapping-property-detail-key"><?php echo str_replace('_', ' ', strtoupper($prop_info)); ?></span>
			<?php
		}
		?>
			<span class="vcm-roomsmapping-property-detail-val<?php echo $prop_val_css; ?>"><?php echo $prop_val; ?></span>
		</span>
		<?php
	}
	?>
	</div>
</div>
<?php
}
?>

<div class="vcmdivleft">
	<h3><?php echo $this->channel['uniquekey'] == VikChannelManagerConfig::VRBOAPI ? JText::_('VCM_ELIGIBLE_LISTINGS') : JText::_('VCMROOMSRETURNEDBYOTA'); ?></h3>
	<table class="vcmtableleft">
		<?php
		$tototarooms = 0;
		foreach ($channelrooms['Rooms'] as $rk => $room) {
			if (!empty($room['id']) && !empty($room['name'])) {
				$tototarooms++;
			}
			$rate_plan = [];
			if (isset($room['RatePlan']) && is_array($room['RatePlan']) && $room['RatePlan']) {
				foreach ($room['RatePlan'] as $plan) {
					/**
					 * Sanitize rate plan name to avoid JS errors.
					 * 
					 * @since 	1.8.20
					 */
					if (!empty($plan['name'])) {
						$plan['name'] = htmlspecialchars($plan['name'], ENT_QUOTES);
					}

					// push value
					$rate_plan[$plan['id']] = $plan;
				}
				// push room rate plans
				$rooms_rate_plans[$room['id']]['RatePlan'] = $rate_plan;
				// check for room info
				if (isset($room['RoomInfo']) && is_array($room['RoomInfo']) && $room['RoomInfo']) {
					// push room information
					$rooms_rate_plans[$room['id']]['RoomInfo'] = $room['RoomInfo'];
				}
			}
			?>
			<tr>
				<td class="vcmtableleftsecondtd">
				<?php
				if (count($room) > 0) {
					foreach ($room as $keyr => $valr) {
						if (!is_array($valr)) { ?>
							<div class="vcmtableleftdivroomfield"><span class="vcmtableleftspkey"><?php echo ucwords($keyr); ?>:</span> <span class="vcmtableleftspval"><?php echo $valr; ?></span></div>
							<?php
						} else { ?>
							<div class="vcmtableleftdivroomfield">
								<span class="vcmtableleftspkeyopen"><?php echo $keyr . (class_exists('VikBookingIcons') ? ' <i class="' . VikBookingIcons::i('chevron-down') . '"></i>' : ''); ?></span>
								<div class="vcmtableleftsubdiv">
							<?php
							$rp_loop = 0;
							foreach ($valr as $subrk => $subrv) {
								$rp_loop++;
								if (is_array($subrv)) {
									foreach ($subrv as $srk => $srv) {
										?>
										<div class="vcmtableleftdivroomfield"><span class="vcmtableleftspkey"><?php echo $srk; ?>:</span> <span class="vcmtableleftspval"><?php echo $srv; ?></span></div>
										<?php
									}
									if ($rp_loop < count($valr)) {
										?>
										<div class="vcmsubdivseparator"></div>
										<?php
									}
								}else { ?>
									<div class="vcmtableleftdivroomfield"><span class="vcmtableleftspkey"><?php echo $subrk; ?>:</span> <span class="vcmtableleftspval"><?php echo $subrv; ?></span></div>
									<?php
								}
							}
							?>
							</div>
						</div>
						<?php
						}
					}
				} ?>
			</td>
			<td class="vcmtableleftfirsttd">
				<span class="vcmselectotaroom" id="vcmotarselector<?php echo $room['id']; ?>" onclick="vcmStartLinking('<?php echo $room['id']; ?>', '<?php echo addslashes(htmlspecialchars(str_replace(array("\r\n", "\n", "\r"), '', $room['name']), ENT_COMPAT)); ?>');">
					<span class="vcmselectotaroom-txt"><?php echo JText::_('VCMSELECTOTAROOMTOLINK'); ?></span>
					<span class="vcmselectotaroom-icn"><?php echo class_exists('VikBookingIcons') ? '<i class="' . VikBookingIcons::i('link') . '"></i>' : ''; ?></span>
				</span>
			</td>
		</tr>
		<?php } ?>
	</table>
</div>
	
<div class="vcmdivmiddle">
	<h3><?php echo JText::_('VCMROOMSRELATIONS'); ?></h3>
	<table class="vcmtablemiddle">
		<tr>
			<td colspan="2" style="width: 45%; text-align: center; font-weight: bold; border-bottom: 1px solid #dddddd;"><?php echo JText::_('VCMROOMSRELATIONSOTA'); ?></td>
			<td rowspan="2" style="width: 10%; vertical-align: middle; text-align: center;"><?php echo class_exists('VikBookingIcons') ? '<i class="' . VikBookingIcons::i('link') . '"></i>' : '<img src="' . VCM_ADMIN_URI . 'assets/css/images/link.png" />'; ?></td>
			<td colspan="2" style="width: 45%; text-align: center; font-weight: bold; border-bottom: 1px solid #dddddd;"><?php echo JText::_('VCMROOMSRELATIONSVB'); ?></td>
		</tr>
		<tr>
			<td style="text-align: center; font-weight: bold;"><?php echo JText::_('VCMROOMSRELATIONSID'); ?></td>
			<td style="text-align: center; font-weight: bold;"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></td>
			<td style="text-align: center; font-weight: bold;"><?php echo JText::_('VCMROOMSRELATIONSID'); ?></td>
			<td style="text-align: center; font-weight: bold;"><?php echo JText::_('VCMROOMSRELATIONSNAME'); ?></td>
		</tr>
	</table>
</div>
	
<div class="vcmdivright">
	<h3><?php echo JText::_('VCMROOMSRETURNEDBYVB'); ?></h3>
	<table class="vcmtableright">
	<?php foreach ($vbrooms as $vbroom) { ?>
		<tr>
			<td class="vcmvbroomtdlink" rowspan="2">
				<span class="vcmselectvbroom" id="vcmvbrselector<?php echo $vbroom['id']; ?>" onclick="vcmEndLinking('<?php echo $vbroom['id']; ?>', '<?php echo addslashes(htmlspecialchars(str_replace(array("\r\n", "\n", "\r"), '', $vbroom['name']), ENT_COMPAT)); ?>');">
					<span class="vcmselectvbroom-icn"><?php echo class_exists('VikBookingIcons') ? '<i class="' . VikBookingIcons::i('link') . '"></i>' : ''; ?></span>
					<span class="vcmselectvbroom-txt"><?php echo JText::_('VCMSELECTVBROOMTOLINK'); ?></span>
				</span>
			</td>
			<td rowspan="2"><?php echo (!empty($vbroom['img']) ? '<img src="'.VBO_SITE_URI.'resources/uploads/'.$vbroom['img'].'" class="vcmvbroomimg"/>' : ''); ?></td>
			<td class="vcmvbroomtdname"><?php echo $vbroom['name']; ?></td>
		</tr>
		<tr>
			<td colspan="3" class="vcmvbroomtdsmalldesc"><?php echo $vbroom['smalldesc']; ?></td>
		</tr>
		<?php } ?>
	</table>
</div>
	
<input type="hidden" name="tototarooms" value="<?php echo $tototarooms; ?>"/>

<?php
if (count($rooms_rate_plans) > 0) {
	?>
<script type="text/javascript">
	var room_plans = new Object();
	<?php
	foreach ($rooms_rate_plans as $idr => $room_plan) {
		echo "room_plans.r".$idr." = ".json_encode($room_plan).";\n";
	}

	/**
	 * Populate current relations with this account (if any)
	 * 
	 * @since 	1.7.2
	 * @since 	1.8.6 we speed up the animation process in case of a lot of relations.
	 */
	$tot_active_xref = count($this->active_xref);
	if ($tot_active_xref) {
		$active_vbo_rooms_linked = array();
		foreach ($this->active_xref as $xref) {
			if (in_array($xref['idroomvb'], $active_vbo_rooms_linked)) {
				continue;
			}
			// find this OTA room ID in the just-read room types
			foreach ($channelrooms['Rooms'] as $rk => $room) {
				if (!empty($room['id']) && $room['id'] == $xref['idroomota']) {
					// OTA room found, find the name of this room on VBO first
					$vbo_rname = '';
					foreach ($vbrooms as $vbroom) {
						if ((int)$vbroom['id'] == (int)$xref['idroomvb']) {
							// vbo room found
							$vbo_rname = $vbroom['name'];
							break;
						}
					}
					if (empty($vbo_rname)) {
						// this room no longer exists, we cannot proceed
						continue;
					}
					// decide animation type for relation
					$anim_type = isset($xref['is_fake']) || $tot_active_xref > 10 ? 1 : 0;
					// simulate the "start linking"
					echo "vcmStartLinking('{$xref['idroomota']}', '" . addslashes(htmlspecialchars(str_replace(array("\r\n", "\n", "\r"), '', $room['name']), ENT_COMPAT)) . "', {$anim_type});\n";
					// simulate the end linking for the corresponding room ID in VBO
					echo "vcmEndLinking('{$xref['idroomvb']}', '" . addslashes(htmlspecialchars(str_replace(array("\r\n", "\n", "\r"), '', $vbo_rname), ENT_COMPAT)) . "', {$anim_type})\n";
					// push vbo rooms mapped
					array_push($active_vbo_rooms_linked, $xref['idroomvb']);
				}
			}
		}
	}
?>
</script>
<?php
}

//Debug:
//echo '<br clear="all"/><br clear="all"/><br/><pre>'.print_r($channelrooms, true).'</pre><br/>';
