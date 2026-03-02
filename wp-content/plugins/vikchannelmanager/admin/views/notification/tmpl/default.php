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

$notification = $this->notification;
$row = $this->row;
$rooms = $this->rooms;
$busy = $this->busy;

$dbo = JFactory::getDbo();

$currencyname = VikChannelManager::getCurrencySymb();
$df = VikChannelManager::getClearDateFormat(true);

$txt_parts = explode("\n", $notification['cont']);
$render_mess = VikChannelManager::getErrorFromMap(trim($txt_parts[0]), true);
unset($txt_parts[0]);
$notification['cont'] = $render_mess.(count($txt_parts) > 0 ? "\n".implode("\n", $txt_parts) : '');
switch( intval($notification['type']) ) {
	case 1:
		$ntype = JText::_('VCM_LBL_SUCCESS');
		break;
	case 2:
		$ntype = JText::_('VCM_LBL_WARNING');
		break;
	default:
		$ntype = JText::_('VCM_LBL_ERROR');
		break;
}

$otachannel = '';
$otacurrency = '';
$otachannel_name = '';
if (!empty($row['idorderota'])) {
	$channelparts = explode('_', $row['channel']);
	$otachannel = '<span class="vcm-notif-otaname">'.(strlen($channelparts[1]) > 0 ? $channelparts[1] : $channelparts[0]).'</span>';
	$otachannel .= ' <span class="vcm-notif-otabid">'.JText::_('VCMSMARTBALBID').': '.$row['idorderota'].'</span>';
	$otacurrency = strlen($row['chcurrency']) > 0 ? $row['chcurrency'] : '';
}
if (!empty($row['channel'])) {
	$channelparts = explode('_', $row['channel']);
	$otachannel_name = array_key_exists(1, $channelparts) && strlen($channelparts[1]) > 0 ? $channelparts[1] : ucwords($channelparts[0]);
}

echo (!empty($row['idorderota']) ? "<div class=\"vcm-notif-otainfo\">".$otachannel."</div>" : "");

// check if this is a notification of type info
$info_notif = (empty($notification['idordervb']) && stripos($notification['from'], 'e4j') !== false && strpos($notification['cont'], '<') !== false);
$info_notif = ($info_notif && $notification['type'] > 0 && strpos($notification['cont'], '>') !== false && !count($notification['children']));

if (!$info_notif) {
	?>
<p class="vcm-notif-globcont"><strong><?php echo JText::_('VCMNOTIFICATIONTYPE'); ?>:</strong> <?php echo $ntype; ?></p>
<pre class="vcmnotifymessblock" id="vcm-notif-globcont" <?php if (count($notification['children']) > 0): ?>style="display: none;"<?php endif; ?>><?php echo htmlentities(urldecode($notification['cont'])); ?></pre>
	<?php
} else {
	$html_parts = explode("\n", $notification['cont']);
	if (strpos($html_parts[0], '<') === false) {
		// first line usually contains the subject, so we can get rid of it
		unset($html_parts[0]);
	}
	$notification['cont'] = implode("\n", $html_parts);
	$info_notif_img = null;
	// attempt to detach main image
	if (preg_match("/<img.*?src=['\"](.*?)['\"].*?\/?>/", $notification['cont'], $matches)) {
		$info_notif_img = $matches[0];
		$notification['cont'] = str_replace($info_notif_img, '', $notification['cont']);
	}
	?>
<div class="vcm-notif-infohtml">
	<?php
	if (!empty($info_notif_img)) {
		?>
	<div class="vcm-notif-infohtml-introimg">
		<?php echo $info_notif_img; ?>
	</div>
		<?php
	}	
	?>
	<div class="vcm-notif-infohtml-content">
		<?php echo $notification['cont']; ?>
	</div>
</div>
	<?php
}

// PCI Data Retrieval
if (!empty($row['idorderota']) && !empty($row['channel'])) {
	$channel_source = $row['channel'];
	if (strpos($row['channel'], '_') !== false) {
		$channelparts = explode('_', $row['channel']);
		$channel_source = $channelparts[0];
	}
	//Maximum one hour after the checkout date and time
	if ((time() + 3600) < $row['checkout']) {
		$plain_log = urldecode($notification['cont']);
		if (stripos($plain_log, 'card number') !== false && strpos($plain_log, '****') !== false) {
			//log contains credit card details
			?>
<div class="vcm-notif-pcidrq-container">
	<a class="btn vcm-config-btn vcm-pcid-launch" href="index.php?option=com_vikchannelmanager&amp;task=execpcid&amp;channel_source=<?php echo $channel_source; ?>&amp;otaid=<?php echo $row['idorderota']; ?>&amp;tmpl=component"><?php VikBookingIcons::e('credit-card'); ?> <?php echo JText::_('VCMPCIDLAUNCH'); ?></a>
</div>
			<?php
		}
	}
}
//
?>

<p><strong><?php echo JText::_('VCMDASHNOTSFROM'); ?>:</strong> <?php echo $notification['from']; ?> - <strong><?php echo JText::_('VCMDASHNOTSDATE'); ?>:</strong> <?php echo date($df.' H:i', $notification['ts']); ?></p>
<?php
if (count($notification['children']) > 0) {
	foreach ($notification['children'] as $child) {
		$txt_parts = explode("\n", $child['cont']);
		$render_mess = VikChannelManager::getErrorFromMap(trim($txt_parts[0]), true);
		unset($txt_parts[0]);
		$child['cont'] = $render_mess.(count($txt_parts) > 0 ? "\n".implode("\n", $txt_parts) : '');
		switch( intval($child['type']) ) {
			case 1:
				$ntype = JText::_('VCM_LBL_SUCCESS');
				break;
			case 2:
				$ntype = JText::_('VCM_LBL_WARNING');
				break;
			default:
				$ntype = JText::_('VCM_LBL_ERROR');
				break;
		}
		?>
		<div class="vcm-childnotification-block">
			<div class="vcm-childnotification-head">
				<span><strong><?php echo JText::_('VCMNOTIFICATIONTYPE'); ?>:</strong> <?php echo $ntype; ?></span>
		<?php
		if (!empty($child['channel'])) {
			$channel_info = VikChannelManager::getChannel($child['channel']);
			if (is_array($channel_info) && count($channel_info)) {
				$say_name = $channel_info['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucwords($channel_info['name']);
				$say_name = $channel_info['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL ? 'Google Hotel' : $say_name;
				$say_name = $channel_info['uniquekey'] == VikChannelManagerConfig::GOOGLEVR ? 'Google VR' : $say_name;
				?>
				<span class="vcm-sp-right"><span class="vbotasp <?php echo $channel_info['name']; ?>"><?php echo $say_name; ?></span></span>
				<?php
			}
		}
		?>
			</div>
		<?php
		if (!empty($child['cont'])) {
			//parse {hotelid n} for Multiple Accounts
			if (strpos($child['cont'], '{hotelid') !== false) {
				$child['cont'] = VikChannelManager::parseNotificationHotelId($child['cont'], $child['channel']);
			}
			?>
			<pre class="vcmnotifymessblock"><?php echo htmlentities(urldecode($child['cont'])); ?></pre>
			<?php
		}
		?>
		</div>
		<?php
	}
	?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery(".vcm-notif-globcont").css("cursor", "pointer");
	jQuery(".vcm-notif-globcont").click(function(){
		jQuery("#vcm-notif-globcont").toggle();
		jQuery(this).toggleClass("vcm-notif-glob-opened");
	});
});
</script>
	<?php
}
?>
<br clear="all"/>

<?php
/**
 * Booking re-transmit
 * @since 	1.6.10
 */
if (count($this->retransmit)) {
	$trquery = array();
	foreach ($this->retransmit as $k => $v) {
		if (!is_string($v)) {
			continue;
		}
		array_push($trquery, $k . '=' . $v);
	}
	?>
<script type="text/javascript">
var vcmretransmitted = false;
function vcmRetransmitConfirm() {
	if (vcmretransmitted === true) {
		alert('<?php echo addslashes(JText::_('VCMOVERSIGHTLEGENDDASHED')); ?>');
		return false;
	}
	if (confirm('<?php echo addslashes(JText::_('VCMBRTWOCONFIRM')); ?>')) {
		vcmretransmitted = true;
		return true;
	}
	return false;
}
</script>
<div style="text-align: center; margin: 10px;">
	<a class="btn btn-large btn-primary" id="vcm-retransmit-confirm" target="_blank" href="index.php?option=com_vikchannelmanager&task=brtwo&<?php echo implode('&', $trquery); ?>" onclick="return vcmRetransmitConfirm();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VCMBRTWOACT'); ?></a>
</div>
	<?php
}

/**
 * Booking details with new layout
 * @since 	1.6.8
 */
if (is_array($rooms)) {
	$currencyname = VikChannelManager::getCurrencySymb();
	$payment = VikChannelManager::getPaymentVb($row['idpayment']);
	
	$tars = array();
	$arrpeople = array();
	foreach($rooms as $ind => $or) {
		$num = $ind + 1;
		if (!empty($or['idtar'])) {
			$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`=".$or['idtar'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$tar = $dbo->loadAssocList();
		} else {
			$tar = array();
		}
		$tars[$num] = $tar;
		$arrpeople[$num]['adults'] = $or['adults'];
		$arrpeople[$num]['children'] = $or['children'];
	}
	$secdiff = $row['checkout'] - $row['checkin'];
	$daysdiff = $secdiff / 86400;
	if (is_int($daysdiff)) {
		if ($daysdiff < 1) {
			$daysdiff = 1;
		}
	} else {
		if ($daysdiff < 1) {
			$daysdiff = 1;
		} else {
			$sum = floor($daysdiff) * 86400;
			$newdiff = $secdiff - $sum;
			$maxhmore = VikChannelManager::getHoursMoreRb() * 3600;
			if ($maxhmore >= $newdiff) {
				$daysdiff = floor($daysdiff);
			} else {
				$daysdiff = ceil($daysdiff);
			}
		}
	}
	?>
<div class="vcm-bookingdet-topcontainer">
	
	<div class="vcm-bookdet-container">
		<div class="vcm-bookdet-wrap">
			<div class="vcm-bookdet-head">
				<span>ID</span>
			</div>
			<div class="vcm-bookdet-foot">
				<span><a href="index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $row['id']; ?>" target="_blank"><?php echo $row['id']; ?></a></span>
			</div>
		</div>
		<div class="vcm-bookdet-wrap">
			<div class="vcm-bookdet-head">
				<span><?php echo JText::_('VCMVBEDITORDERONE'); ?></span>
			</div>
			<div class="vcm-bookdet-foot">
				<span><?php echo date($df.' H:i', $row['ts']); ?></span>
			</div>
		</div>
		<div class="vcm-bookdet-wrap">
			<div class="vcm-bookdet-head">
				<span><?php echo JText::_('VCMVBEDITORDERROOMSNUM'); ?></span>
			</div>
			<div class="vcm-bookdet-foot">
				<span><?php echo $row['roomsnum']; ?></span>
			</div>
		</div>
		<div class="vcm-bookdet-wrap">
			<div class="vcm-bookdet-head">
				<span><?php echo JText::_('VCMVBEDITORDERFOUR'); ?></span>
			</div>
			<div class="vcm-bookdet-foot">
				<span><?php echo $row['days']; ?></span>
			</div>
		</div>
		<div class="vcm-bookdet-wrap">
			<div class="vcm-bookdet-head">
				<span><?php echo JText::_('VCMVBEDITORDERFIVE'); ?></span>
			</div>
			<div class="vcm-bookdet-foot">
				<span><?php echo date($df.' H:i', $row['checkin']); ?></span>
			</div>
		</div>
		<div class="vcm-bookdet-wrap">
			<div class="vcm-bookdet-head">
				<span><?php echo JText::_('VCMVBEDITORDERSIX'); ?></span>
			</div>
			<div class="vcm-bookdet-foot">
				<span><?php echo date($df.' H:i', $row['checkout']); ?></span>
			</div>
		</div>
		<?php
		if ($row['status'] == "confirmed") {
			$saystaus = '<span class="label label-success">'.JText::_('VBCONFIRMED').'</span>';
		} elseif ($row['status']=="standby") {
			$saystaus = '<span class="label label-warning">'.JText::_('VBSTANDBY').'</span>';
		} else {
			$saystaus = '<span class="label label-error" style="background-color: #d9534f;">'.JText::_('VBCANCELLED').'</span>';
		}
		?>
		<div class="vcm-bookdet-wrap">
			<div class="vcm-bookdet-head">
				<span><?php echo JText::_('VCMPVIEWORDERSVBEIGHT'); ?></span>
			</div>
			<div class="vcm-bookdet-foot">
				<?php echo $saystaus; ?>
			</div>
		</div>
	</div>

	<div class="vcm-bookingdet-innercontainer">
		
		<div class="vcm-bookingdet-customer">
			<div class="vcm-bookingdet-detcont<?php echo $row['closure'] > 0 ? ' vcm-bookingdet-closure' : ''; ?>">
			<?php
			$custdata_parts = explode("\n", $row['custdata']);
			if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
				//attempt to format labels and values
				foreach ($custdata_parts as $custdet) {
					if (strlen($custdet) < 1) {
						continue;
					}
					$custdet_parts = explode(':', $custdet);
					$custd_lbl = '';
					$custd_val = '';
					if (count($custdet_parts) < 2) {
						$custd_val = $custdet;
					} else {
						$custd_lbl = $custdet_parts[0];
						unset($custdet_parts[0]);
						$custd_val = trim(implode(':', $custdet_parts));
					}
					?>
				<div class="vcm-bookingdet-userdetail">
					<?php
					if (strlen($custd_lbl)) {
						?>
					<span class="vcm-bookingdet-userdetail-lbl"><?php echo $custd_lbl; ?></span>
						<?php
					}
					if (strlen($custd_val)) {
						?>
					<span class="vcm-bookingdet-userdetail-val"><?php echo $custd_val; ?></span>
						<?php
					}
					?>
				</div>
					<?php
				}
			} else {
				if ($row['closure'] > 0) {
					?>
				<div class="vcm-bookingdet-userdetail">
					<span class="vcm-bookingdet-userdetail-val"><?php echo nl2br($row['custdata']); ?></span>
				</div>
					<?php
				} else {
					echo nl2br($row['custdata']);
					?>
				<div class="vcm-bookingdet-userdetail">
					<span class="vcm-bookingdet-userdetail-val">&nbsp;</span>
				</div>
					<?php
				}
			}
			?>
			</div>
			<?php
			if ((!empty($row['channel']) && !empty($row['idorderota'])) || strlen($row['confirmnumber']) > 0) {
				?>
			<div class="vcm-bookingdet-detcont">
				<?php
				if (!empty($row['channel']) && !empty($row['idorderota'])) {
					?>
					<div>
						<span class="label label-info"><?php echo $otachannel_name.' ID'; ?> <span class="badge"><?php echo $row['idorderota']; ?></span></span>
					</div>
					<?php
				}
				if (strlen($row['confirmnumber']) > 0) {
					?>
					<div>
						<span class="label label-success"><?php echo JText::_('VCMVBCONFIRMNUMB'); ?> <span class="badge"><?php echo $row['confirmnumber']; ?></span></span>
					</div>
					<?php
				}
				?>
			</div>
				<?php
			}
			?>
		</div>

		<div class="vcm-bookingdet-summary">
			<div class="table-responsive">
				<table class="table">
				<?php
				foreach ($rooms as $ind => $or) {
					$num = $ind + 1;
					?>
					<tr class="vcm-bookingdet-summary-room">
						<td class="vcm-bookingdet-summary-room-firstcell">
							<div class="vcm-bookingdet-summary-roomnum"><?php echo JText::sprintf('VCMVBNOTROOMNUM', $num); ?></div>
							<div class="vcm-bookingdet-summary-roomguests">
								<div class="vcm-bookingdet-summary-roomadults">
									<span><?php echo JText::_('VCMVBEDITORDERADULTS'); ?>:</span> <?php echo $arrpeople[$num]['adults']; ?>
								</div>
							<?php
							if ($arrpeople[$num]['children'] > 0) {
								?>
								<div class="vcm-bookingdet-summary-roomchildren">
									<span><?php echo JText::_('VCMVBEDITORDERCHILDREN'); ?>:</span> <?php echo $arrpeople[$num]['children']; ?>
								</div>
								<?php
							}
							?>
							</div>
						</td>
						<td>
							<div class="vcm-bookingdet-summary-roomname"><?php echo $or['name']; ?></div>
							<div class="vcm-bookingdet-summary-roomrate">
							<?php
							if (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00) {
								if (isset($or['pkg_name']) && !empty($or['pkg_name'])) {
									//package
									echo $or['pkg_name'];
								} else {
									//custom cost can have an OTA Rate Plan name
									if (!empty($or['otarplan'])) {
										echo ucwords($or['otarplan']);
									} else {
										echo '';
									}
								}
							} elseif (array_key_exists($num, $tars) && count($tars[$num]) && !empty($tars[$num][0]['idprice'])) {
								echo VikChannelManager::getPriceName($tars[$num][0]['idprice']);
							} elseif (!empty($or['otarplan'])) {
								echo ucwords($or['otarplan']);
							} elseif ($row['closure'] < 1) {
								echo '';
							}
							?>
							</div>
						</td>
						<td></td>
					</tr>
					<?php
				}
				?>
					<tr class="vcm-bookingdet-summary-total">
						<td></td>
						<td>
							<span class="vcm-bookingdet-summary-lbl"><?php echo JText::_('VCMVBEDITORDERNINE'); ?></span>
						</td>
						<td>
							<span class="vcm-bookingdet-summary-cost"><?php echo (strlen($otacurrency) > 0 ? '('.$otacurrency.') '.$currencyname : $currencyname); ?> <?php echo $row['total']; ?></span>
						</td>
					</tr>
				</table>
			</div>
		</div>

	</div>

</div>
	
<?php
}
