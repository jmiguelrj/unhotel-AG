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

$cookie = JFactory::getApplication()->input->cookie;
$vcm_app = new VikApplication(VersionListener::getID());
$vcm_config = VCMFactory::getConfig();

$notifications = $this->notifications;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$notif_filter = VikRequest::getString('notif_filter', '', 'request');

if (class_exists('VikBooking') && method_exists('VikBooking', 'getVboApplication')) {
	//load BS Modal
	$vbo_app = VikBooking::getVboApplication();
	echo $vbo_app->getJmodalScript();
	echo $vbo_app->getJmodalHtml('vcm-notification-details', JText::_('VCMDASHNOTIFICATIONS'));
}

$active_channels = $this->activeChannels;

// load tooltip behavior
$def_df = $vcm_config->get('dateformat', '');
if ($def_df == '%Y/%m/%d') {
	$df = 'Y-m-d H:i';
} elseif ($def_df == '%d/%m/%Y') {
	$df = 'd-m-Y H:i';
} else {
	$df = 'm-d-Y H:i';
}

// API key and Admin email
$api_key 	 = $vcm_config->get('apikey', '');
$admin_email = $vcm_config->get('emailadmin', '');

/**
 * For reasons related to the contract between e4jConnect and Airbnb, all
 * iCal calendars that belong to Airbnb should be upgraded to the new API
 * version as soon as possible, to ensure the best service with this channel.
 * 
 * @since 	1.8.0
 */
$airbnb_status = VikChannelManager::hasDeprecatedAirbnbVersion();

/**
 * We also need to make sure no deprecated calendars of the Vrbo family
 * are still being parsed without having an active API connection.
 * 
 * @since 	1.8.16
 */
$vrbo_status = VCMVrboHelper::hasDeprecatedCalendars();
$snooze_vrbo = $cookie->get('vcmSnoozeVrboUpg', '', 'string');

if ($airbnb_status === true) {
	?>
<div class="err vcm-airbnb-upgrade-err">
	<p class="vcm-airbnb-upgrade-notice"><?php echo JText::_('AIRBNB_UPGNOTICE_ERR1'); ?> <span class="vcm-airbnb-upgrade-clickable" onclick="document.getElementsByClassName('vcm-airbnb-upgrade-info')[0].style.display='block';"><?php echo JText::_('AIRBNB_UPGNOTICE_FINDMORE'); ?></span>.</p>
	<div class="vcm-airbnb-upgrade-info" style="display: none;">
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_ERR2'); ?></p>
		<div class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-pointslist">
			<ol>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT1'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT2'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT3'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT4'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT5'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT6'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT7'); ?></li>
			</ol>
		</div>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_ERR3'); ?></p>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_ERR4'); ?></p>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-launch">
			<a href="index.php?option=com_vikchannelmanager&task=vcm_airbnb_upgrade" class="btn btn-primary"><?php echo JText::_('AIRBNB_UPGNOTICE_ACTIVATE'); ?></a>
		</p>
	</div>
</div>
	<?php
} elseif ($airbnb_status === -1) {
	?>
<div class="warn vcm-airbnb-upgrade-warn">
	<p class="vcm-airbnb-upgrade-notice"><?php echo JText::_('AIRBNB_UPGNOTICE_WARN1'); ?> <span class="vcm-airbnb-upgrade-clickable" onclick="document.getElementsByClassName('vcm-airbnb-upgrade-info')[0].style.display='block';"><?php echo JText::_('AIRBNB_UPGNOTICE_FINDMORE'); ?></span>.</p>
	<div class="vcm-airbnb-upgrade-info" style="display: none;">
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_WARN2'); ?></p>
		<div class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-pointslist">
			<ol>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT1'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT2'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT3'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT4'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT5'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT6'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT7'); ?></li>
			</ol>
		</div>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_WARN3'); ?></p>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_WARN4'); ?></p>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-launch">
			<a href="index.php?option=com_vikchannelmanager&task=vcm_airbnb_upgrade" class="btn btn-warning"><?php echo JText::_('AIRBNB_UPGNOTICE_DEACTIVATE'); ?></a>
		</p>
	</div>
</div>
	<?php
} elseif ($vrbo_status === 1) {
	// suggest the activation of the API channel
	?>
<script type="text/javascript">
	function vcmSnoozeVrboUpgrade() {
		// set cookie for two weeks
		var nd = new Date();
		nd.setTime(nd.getTime() + (14*24*60*60*1000));
		document.cookie = "vcmSnoozeVrboUpg=1; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
		// hide message
		jQuery('.vcm-vrbo-upgrade-err').hide();
	}
</script>

<div class="err vcm-vrbo-upgrade-err" style="<?php echo !empty($snooze_vrbo) ? 'display: none;' : ''; ?>">
	<p class="vcm-vrbo-upgrade-notice"><?php echo JText::_('VRBO_UPGNOTICE_ERR1'); ?> <a href="JavaScript: void(0);" onclick="document.getElementsByClassName('vcm-vrbo-upgrade-info')[0].style.display='block';"><?php echo JText::_('AIRBNB_UPGNOTICE_FINDMORE'); ?></a>.</p>
	<div class="vcm-vrbo-upgrade-info" style="display: none;">
		<p class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-inner"><?php echo JText::_('VRBO_UPGNOTICE_ERR2'); ?></p>
		<div class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-pointslist">
			<ol>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT1'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT2'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT3'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT4'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT5'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT6'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT7'); ?></li>
			</ol>
		</div>
		<p class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-inner"><?php echo JText::_('VRBO_UPGNOTICE_ERR3'); ?></p>
		<p class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-inner"><?php echo JText::_('VRBO_UPGNOTICE_ERR4'); ?></p>
		<p class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-launch">
			<a href="index.php?option=com_vikchannelmanager&task=vrbolst.vcm_vrbo_upgrade" class="btn btn-primary"><?php echo JText::_('VRBO_UPGNOTICE_ACTIVATE'); ?></a>
			<button type="button" class="btn btn-warning" onclick="vcmSnoozeVrboUpgrade();"><?php echo JText::_('VCMTIPMODALOKREMIND'); ?></button>
		</p>
	</div>
</div>
	<?php
} elseif ($vrbo_status === -1) {
	// suggest the removal of the iCal calendars
	?>
<div class="warn vcm-vrbo-upgrade-warn">
	<p class="vcm-vrbo-upgrade-notice"><?php echo JText::_('VRBO_UPGNOTICE_WARN1'); ?> <a href="JavaScript: void(0);" onclick="document.getElementsByClassName('vcm-vrbo-upgrade-info')[0].style.display='block';"><?php echo JText::_('AIRBNB_UPGNOTICE_FINDMORE'); ?></a>.</p>
	<div class="vcm-vrbo-upgrade-info" style="display: none;">
		<p class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-inner"><?php echo JText::_('VRBO_UPGNOTICE_WARN2'); ?></p>
		<div class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-pointslist">
			<ol>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT1'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT2'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT3'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT4'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT5'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT6'); ?></li>
				<li><?php echo JText::_('VRBO_UPGNOTICE_POINT7'); ?></li>
			</ol>
		</div>
		<p class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-inner"><?php echo JText::_('VRBO_UPGNOTICE_WARN3'); ?></p>
		<p class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-inner"><?php echo JText::_('VRBO_UPGNOTICE_WARN4'); ?></p>
		<p class="vcm-vrbo-upgrade-notice vcm-vrbo-upgrade-notice-launch">
			<a href="index.php?option=com_vikchannelmanager&task=vrbolst.vcm_vrbo_upgrade" class="btn btn-warning"><?php echo JText::_('VRBO_UPGNOTICE_DEACTIVATE'); ?></a>
		</p>
	</div>
</div>
	<?php
}
//
?>

<?php
if (!$vcm_config->get('block_program', '')) {
	?>

<div class="vcmdash-container">

<?php
/**
 * Check whether the last_endpoint is different than the current endpoint that could be submitted.
 * 
 * @since 	1.6.18
 */
$last_endpoint = VikChannelManager::getLastEndpoint();
if (!empty($last_endpoint)) {
	$endpoint_warning = '';
	$current_endpoint = JUri::root();
	$last_protocolpos = strpos($last_endpoint, ':');
	$cur_protocolpos = strpos($current_endpoint, ':');
	if ($last_protocolpos !== false && $cur_protocolpos !== false) {
		$last_basedom = substr($last_endpoint, ($last_protocolpos + 3));
		$current_basedom = substr($current_endpoint, ($cur_protocolpos + 3));
		if ($last_protocolpos != $cur_protocolpos) {
			//protocol has changed from HTTP to HTTPS or vice-versa
			$endpoint_warning = JText::sprintf('VCMENDPOINTWARNPROTOCOL', strtoupper(substr($last_endpoint, 0, $last_protocolpos)), strtoupper(substr($current_endpoint, 0, $cur_protocolpos)));
		} elseif ($last_basedom != $current_basedom) {
			//the base domain name has changed
			$endpoint_warning = JText::sprintf('VCMENDPOINTWARNDOMAIN', $last_endpoint, $current_endpoint);
		}
	}
	if (!empty($endpoint_warning)) {
	?>
	<div class="vcm-config-warning-endpoint">
		<span><?php echo $endpoint_warning; ?></span>
		<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=update_endpoints" onclick="return confirm('<?php echo addslashes(JText::_('VCMUPDATEENDPURLCONF')); ?>');"><?php echo JText::sprintf('VCMUPDATEENDPURL', $current_endpoint); ?></a>
	</div>
	<?php
	}
}
//
?>

	<div class="vcmdashdivleft">
		
		<?php if ($vcm_config->getInt('to_update', 0) == 1) { ?>
		<p class="vcmupdater vcmnewupavaildash">
			<i class="vboicn-cloud-download"></i>
			<span><?php echo JText::_('VCMNEWOPTIONALUPDATEAV'); ?></span>
			<span class="vcmupdatedashbutton">
				<a href="index.php?option=com_vikchannelmanager&task=update_program" class="vcmupdatenowlink"><?php echo JText::_('VCMUPDATENOWBTN'); ?></a>
			</span>
		</p>
		<?php } ?>

		<?php
		/**
		 * We load the scorecard template that will display some contents only if available and appropriate
		 * 
		 * @since 	1.7.2
		 */
		echo $this->loadTemplate('scorecard');
		?>

		<div class="vcmdashdivleft-row">
			<h3 class="vcmdashdivlefthead"><?php echo JText::_('VCMDASHSTATUS'); ?></h3>
			<div class="vcmdashdivleft-inner vcm-dashboard-status-wrap">
				<?php
				if ($this->showSync) {
					?>
					<?php if ($vcm_config->getInt('vikbookingsynch', 0) == 1) { ?>
						<div class="vcmdashdivitem vcmstatusongreen"><span class="vcmdashdivitem-lbl"><?php echo JText::_('VCMAUTOSYNC'); ?></span> <?php echo JText::_('VCMDASHVBSYNCHON'); ?></div>
					<?php } else { ?>
						<div class="vcmdashdivitem vcmstatusoff"><span class="vcmdashdivitem-lbl"><?php echo JText::_('VCMAUTOSYNC'); ?></span> <?php echo JText::_('VCMDASHVBSYNCHOFF'); ?></div>
					<?php }
				}
				?>
			
				<?php if (empty($api_key)) { ?>
					<div class="vcmdashdivitem vcmstatusoff"><span class="vcmdashdivitem-lbl"><?php echo JText::_('VCMDASHEMPTYAPI'); ?></span></div>
				<?php } else { ?>
					<div class="vcmdashdivitem vcmstatuson"><span class="vcmdashdivitem-lbl"><?php echo JText::_('VCMDASHYOURAPI'); ?></span> <?php echo substr($api_key, 0, (strlen($api_key) - 4)).' &bull; &bull; &bull; &bull;'; ?></div>
				<?php }

				if (empty($admin_email)) { ?>
					<div class="vcmdashdivitem vcmstatusoff"><span class="vcmdashdivitem-lbl"><?php echo JText::_('VCMDASHEMPTYEMAILADMIN'); ?></span></div>
				<?php } else {
					// check also the email address for the hotel and build links to edit them
					$email_list = [$admin_email];
					if (!empty($this->hotel_email) && $this->hotel_email != $admin_email) {
						// different addresses, inform the customer with edit links
						$email_list = [
							'<a href="index.php?option=com_vikchannelmanager&task=config">' . $admin_email . '</a>',
							'<a href="index.php?option=com_vikchannelmanager&task=hoteldetails">' . $this->hotel_email . '</a>',
						];
					}
					?>
					<div class="vcmdashdivitem vcmstatuson"><span class="vcmdashdivitem-lbl"><?php echo JText::_('VCMDASHYOUREMAILADMIN'); ?></span> <?php echo implode(', ', $email_list); ?></div>
				<?php } ?>
			</div>
		</div>

		<div class="vcmdashdivleft-row">
			<h3 class="vcmdashdivlefthead"><?php echo JText::_('VCMDASHYOURACTIVECHANNELS'); ?></h3>
			<div class="vcmdashdivleft-inner vcm-dashboard-activechannels-wrap <?php echo (count($active_channels) > 0 ? 'vcmok' : 'vcmfatal'); ?>">
				<div class="vcmactivechannelscont">
				<?php
				/**
				 * We need to alert those still using the iCal version of Airbnb.
				 * 
				 * @since 	1.8.0
				 */
				$airbnb_api_available = null;
				foreach (VikChannelManagerConfig::$AVAILABLE_CHANNELS as $uniq => $name) {
					$airbnb_ical_alert = null;
					if ($uniq == VikChannelManagerConfig::AGODA) {
						// this channel has been replaced by YCS50
						continue;
					}
					// check if the current channel is active
					$ch_is_active = in_array($uniq, $active_channels);

					/**
					 * Check if the channel has been deprecated/dismissed.
					 * 
					 * @since 	1.8.3
					 */
					if (!$ch_is_active && in_array($uniq, array(VikChannelManagerConfig::HOMEAWAY, VikChannelManagerConfig::WIMDU))) {
						continue;
					}

					// check if it's Airbnb or Airbnb API
					if ($uniq == VikChannelManagerConfig::AIRBNBAPI) {
						$airbnb_api_available = $ch_is_active;
					} elseif ($uniq == VikChannelManagerConfig::AIRBNB && $ch_is_active && $airbnb_api_available === false) {
						$airbnb_ical_alert = JText::_('VCM_AIRBNBICAL_DEPRECATED_ALERT');
					}
					if ($uniq == VikChannelManagerConfig::AIRBNB && ($airbnb_api_available === true || ($airbnb_api_available === false && !$ch_is_active))) {
						// we no longer list the iCal version of Airbnb when the API version is available, or when neither of them are available
						continue;
					}

					// build classes list for logo container
					$logo_classes = array('vcmchlogo' . $name);
					if (!$ch_is_active) {
						array_push($logo_classes, 'unactive-channel');
					}
					if (!empty($airbnb_ical_alert)) {
						array_push($logo_classes, 'deprecated-channel');
					}
					?>
					<div class="<?php echo implode(' ', $logo_classes); ?>"<?php echo !empty($airbnb_ical_alert) ? ' onmouseover="vcmDisplayAlert(\'' . addslashes($airbnb_ical_alert) . '\');"' : ''; ?>></div>
					<?php
				}
				?>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			var vcm_alerts_displayed = 0;
			function vcmDisplayAlert(message) {
				if (!message || !message.length) {
					return;
				}
				if (vcm_alerts_displayed > 1) {
					return;
				}
				alert(message);
				vcm_alerts_displayed++;
			}
		</script>

		<div class="vcmdashdivleft-row">
			<div class="vcmdashdivleft-inner vcm-dashboard-checkstatus-wrap">
				<div class="vcmdashdivitem vcmok"<?php echo $this->tot_smbal_rules < 1 ? ' style="display: none;"' : ''; ?>>
					<span class="vcmdashdivitem-lbl"><?php echo JText::_('VCMDASHSMBALACTRULES'); ?></span> 
					<span class="badge badge-info vcmdashdivitem-tot_smbal"><?php echo $this->tot_smbal_rules; ?></span>
				</div>

		<?php
		// maximum date in the future pushed for availability and rates through the Bulk Actions
		$maxdates = VikChannelManager::getInventoryMaxFutureDates();
		$maxdates = is_array($maxdates) ? $maxdates : [];
		if (VikChannelManager::isAvailabilityRequest()) {
			// take the minimum date from availability and rates
			$minmaxdate  = count($maxdates) ? min($maxdates) : 0;
			$ninemonths  = strtotime("+9 months");
			$sixmonths   = strtotime("+6 months");
			$threemonths = strtotime("+3 months");
			$minimum_dt  = count($maxdates) ? date(str_replace(' H:i', '', $df), $minmaxdate) : '????';
			if ($minmaxdate < $ninemonths) {
				// display this information only if maxdate is less than 9 months ahead
				if ($minmaxdate < $threemonths) {
					$maxdatewarnclass = 'vcm-dash-invmaxdates-severity-high';
				} elseif ($minmaxdate < $sixmonths) {
					$maxdatewarnclass = 'vcm-dash-invmaxdates-severity-mid';
				} else {
					$maxdatewarnclass = 'vcm-dash-invmaxdates-severity-low';
				}
				// when we do not have any information about the bulk actions, use the mid severity by default
				$maxdatewarnclass = empty($minmaxdate) ? 'vcm-dash-invmaxdates-severity-mid' : $maxdatewarnclass;
				?>
				<div class="vcmdashdivitem vcm-dash-invmaxdates <?php echo $maxdatewarnclass; ?>">
					<p>
						<?php echo $vcm_app->createPopover(array('title' => JText::_('VCMTIPMODALTITLE'), 'content' => JText::_('VCM_INVMAXDATE_MESSDATE_HELP'))); ?>
						<span><?php echo JText::sprintf('VCM_INVMAXDATE_MESSDATE', '<strong>' . $minimum_dt . '</strong>'); ?></span>
					</p>
				<?php
				if (!isset($maxdates['av']) || (isset($maxdates['av']) && $maxdates['av'] < $ninemonths)) {
					// push availability button if max date pushed less than 9 months ahead, or if never pushed before
					?>
					<div class="vcm-dash-invmaxdates-btn">
						<a href="index.php?option=com_vikchannelmanager&task=avpush" class="btn"><?php echo JText::_('VCM_PUSH_AVAILABILITY_INV'); ?></a>
					</div>
					<?php
				}
				if (!isset($maxdates['rates']) || (isset($maxdates['rates']) && $maxdates['rates'] < $ninemonths)) {
					// push rates button if max date pushed less than 9 months ahead, or if never pushed before
					?>
					<div class="vcm-dash-invmaxdates-btn">
						<a href="index.php?option=com_vikchannelmanager&task=ratespush" class="btn"><?php echo JText::_('VCM_PUSH_RATES_INV'); ?></a>
					</div>
					<?php
				}
				?>
				</div>
				<?php
			}
		}
		?>

				<?php //Expiring API Date Request
				if (!empty($api_key)) { ?>
					
					<script type="text/javascript">
					jQuery(function() {
						jQuery("#vcmstartsynch").click(function() {
							jQuery(".vcmsynchspan").removeClass("vcmsynchspansuccess");
							jQuery(".vcmsynchspan").removeClass("vcmsynchspanerror").addClass("vcmsynchspanloading");
							jQuery("#vcmstartsynch").text('<?php echo addslashes(JText::_('VCMSCHECKAPIEXPDATELOAD')); ?>');
							jQuery("#vcmexprs").html("");
							VBOCore.doAjax(
								"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=exec_exp'); ?>",
								{
									tmpl: 'component',
								},
								(res) => {
									jQuery("#vcmstartsynch").text('<?php echo addslashes(JText::_('VCMSCHECKAPIEXPDATE')); ?>');
									jQuery(".vcmsynchspan").removeClass("vcmsynchspanloading");
									if (res.substr(0, 9) == 'e4j.error') {
										jQuery(".vcmsynchspan").addClass("vcmsynchspanerror");
										jQuery("#vcmexprs").html("<pre class='vcmpreerror'>" + res.replace("e4j.error.", "") + "</pre>");
									} else {
										jQuery(".vcmsynchspan").addClass("vcmsynchspansuccess");
										jQuery("#vcmexprs").html(res);
									}
								},
								(error) => {
									console.error(error);
									jQuery("#vcmstartsynch").text('<?php echo addslashes(JText::_('VCMSCHECKAPIEXPDATE')); ?>');
									jQuery(".vcmsynchspan").removeClass("vcmsynchspanloading").addClass("vcmsynchspanerror");
									alert("Error Performing Ajax Request");
								}
							);
						});
						
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
										setTimeout("document.location.href='index.php?option=com_vikchannelmanager'", 5000);
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
					
					<div class="vcmdashdivitem vcmok">
						<span class="vcmdashdivitem-lbl"><?php echo JText::_('VCM_SUBSCR_APIKEY'); ?></span>
						<span class="vcmsynchspan" style="margin: 0 !important;"><a class="vcmsyncha" href="javascript: void(0);" id="vcmstartsynch"><i class="vboicn-key"></i> <?php echo JText::_('VCM_CHECK_EXPDATE'); ?></a></span>
						<span class="vcmexprsdash" id="vcmexprs"></span>
					</div>
					
					<div class="vcmdashdivitem vcmok">
						<span class="vcmdashdivitem-lbl"><?php echo JText::_('VCMGETCHANNELS'); ?></span>
						<span class="vcmchannelspan"><a class="vcmchannel" href="javascript: void(0);" id="vcmstartchannel"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VCMGETCHANNELS'); ?></a></span>
						<span class="vcmchannelrsdash" id="vcmchars"></span>
					</div>
				<?php } ?>
			</div>
		</div>

	</div>

	<script type="text/javascript">

	function vcmCheckAll(bx) {
		var cbs = document.getElementsByTagName('input');
		for(var i=0; i < cbs.length; i++) {
			if (cbs[i].type == 'checkbox') {
				cbs[i].checked = bx.checked;
			}
		}
	}

	function vcmPromptSearch() {
		var search_val = prompt("<?php echo addslashes(JText::_('VCMSEARCHNOTIF')); ?>", "<?php echo addslashes($notif_filter); ?>");
		if (search_val != null) {
			document.getElementById('notif_filter').value = search_val;
			document.getElementById('adminForm').submit();
		}
	}

	</script>

	<div class="vcmdashdivright">
		<h3 class="vcmdashdivrighthead"><i class="vboicn-cloud"></i><?php echo JText::_('VCMDASHNOTIFICATIONS'); ?></h3>
		<?php if ($notifications) { ?>
		
		<form name="adminForm" action="<?php echo VCMFactory::getPlatform()->getUri()->admin('index.php?option=com_vikchannelmanager'); ?>" method="post" id="adminForm">
			<div class="table-responsive">
				<table class="table vcmtablenots">
					<tr>
						<td><a href="index.php?option=com_vikchannelmanager&task=reslogs" class="vcm-link-reslogs"><i class="vboicn-history"></i></a></td>
						<td><strong><?php echo JText::_('VCMDASHNOTSFROM'); ?></strong></td>
						<td><strong><?php echo JText::_('VCMDASHNOTSDATE'); ?></strong></td>
						<td>
							<strong><?php echo JText::_('VCMDASHNOTSTEXT'); ?></strong>
							<span style="cursor: pointer; display: inline-block; margin-left: 15px; vertical-align: middle;" onclick="javascript: vcmPromptSearch();"><i class="vboicn-search" style="margin: 0;"></i></span>
						</td>
						<td class="vcmnotstdright"><button type="submit" class="btn btn-small btn-secondary vcmremovenots" onclick="return vcmRmNotifs();"><i class="vboicn-bin"></i> <?php echo JText::_('VCMDASHNOTSRMSELECTED'); ?></button></td>
						<td class="vcmnotstdright"><input type="checkbox" name="vcmnotsselall" value="1" onclick="javascript: vcmCheckAll(this);"/></td>
					</tr>
				<?php
				
				$kr = 0;
				foreach ($notifications as $notify) {
					$txt_parts = explode("\n", $notify['cont']);
					$render_mess = VikChannelManager::getErrorFromMap(trim($txt_parts[0]), true);
					unset($txt_parts[0]);
					$notify['cont'] = $render_mess.(count($txt_parts) > 0 ? "\n".implode("\n", $txt_parts) : '');
					switch (intval($notify['type'])) {
						case 1:
							$imgtypenot = 'vboicn-checkmark vcm-ic-suc';
							$imgtypenottitle = 'Success';
							break;
						case 2:
							$imgtypenot = 'vboicn-warning vcm-ic-war';
							$imgtypenottitle = 'Success - Warning';
							break;
						default:
							$imgtypenot = 'vboicn-cross vcm-ic-err';
							$imgtypenottitle = 'Error';
							break;
					}
					$cont = explode("\n", $notify['cont']);
					$cont[0] = str_replace(":", " ", $cont[0]);
					$notify['children'] = !array_key_exists('children', $notify) ? array() : $notify['children'];
					$notify['from'] = strtolower($notify['from']) == 'airbnbapi' ? 'Airbnb' : $notify['from'];
					$notify['from'] = strtolower($notify['from']) == 'googlehotel' ? 'Google Hotel' : $notify['from'];
					$notify['from'] = strtolower($notify['from']) == 'googlevr' ? 'Google VR' : $notify['from'];
					$notify['from'] = strtolower($notify['from']) == 'vrboapi' ? 'Vrbo' : $notify['from'];
					?>
					<tr class="vcmnotsrow<?php echo $kr; ?><?php echo $notify['read'] == 0 ? ' vcm-notif-toberead' : ''; ?>">
						<td><i class="<?php echo $imgtypenot; ?>" title="<?php echo $imgtypenottitle; ?>"></i></td>
						<td><?php if (count($notify['children']) > 0) : ?><a href="javascript: void(0);" class="vcm-dash-openchildn" id="parentn<?php echo $notify['id']; ?>"><i class="vcm-arrow vcm-arrow-right"></i> <?php echo $notify['from']; ?></a><?php else: ?><?php echo $notify['from']; ?><?php endif; ?></td>
						<td><a href="javascript: void(0);" onclick="vboOpenJModal('vcm-notification-details', 'index.php?option=com_vikchannelmanager&task=notification&cid[]=<?php echo $notify['id']; ?>&tmpl=component');" class="vcmmodal-notif"><?php echo date($df, $notify['ts']); ?> <i class="vboicn-menu"></i></a></td>
						<td colspan="2"><?php echo $cont[0].(!empty($notify['idordervb']) ? ' (VB.ID '.$notify['idordervb'].')' : ''); ?></td>
						<td class="vcmnotstdright"><input type="checkbox" name="notsids[]" value="<?php echo $notify['id']; ?>"/></td>
					</tr>
					<?php
					if (count($notify['children']) > 0) {
						foreach ($notify['children'] as $child) {
							$txt_parts = explode("\n", $child['cont']);
							$render_mess = VikChannelManager::getErrorFromMap(trim($txt_parts[0]), true);
							unset($txt_parts[0]);
							$child['cont'] = $render_mess.(count($txt_parts) > 0 ? "\n".implode("\n", $txt_parts) : '');
							switch (intval($child['type'])) {
								case 1:
									$imgtypenot = 'vboicn-checkmark vcm-ic-suc';
									$imgtypenottitle = 'Success';
									break;
								case 2:
									$imgtypenot = 'vboicn-warning vcm-ic-war';
									$imgtypenottitle = 'Success - Warning';
									break;
								default:
									$imgtypenot = 'vboicn-cross vcm-ic-err';
									$imgtypenottitle = 'Error';
									break;
							}
							$channel_info = VikChannelManager::getChannel($child['channel']);
							$cont = explode("\n", $child['cont']);
							$cont[0] = str_replace(":", " ", $cont[0]);
							//parse {hotelid n} for Multiple Accounts
							$account_id = '';
							if (strpos($child['cont'], '{hotelid') !== false) {
								$account_id = VikChannelManager::parseNotificationHotelId($child['cont'], $child['channel'], true);
							}
							if (count($channel_info)) {
								if ($channel_info['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
									$channel_info['name'] = 'airbnb';
								} elseif ($channel_info['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
									$channel_info['name'] = 'google hotel';
								} elseif ($channel_info['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
									$channel_info['name'] = 'google vr';
								} elseif ($channel_info['uniquekey'] == VikChannelManagerConfig::VRBOAPI) {
									$channel_info['name'] = 'vrbo';
								}
							}
						?>
					<tr class="vcmnotsrow<?php echo $kr; ?> vcm-dash-hidden vcm-childrow<?php echo $notify['id']; ?>">
						<td>&nbsp;</td>
						<td colspan="2"><i class="<?php echo $imgtypenot; ?>" title="<?php echo $imgtypenottitle; ?>"></i><span class="vcm-childnotif-otaname<?php echo (!empty($account_id) ? ' hasTooltip' : ''); ?>"<?php echo (!empty($account_id) ? ' title="'.$account_id.'"' : ''); ?>><?php echo count($channel_info) > 0 ? ucwords($channel_info['name']) : ''; ?></span></td>
						<td colspan="3"><?php echo str_replace(array('e4j.OK.', 'e4j.error.', 'e4j.warning.'), '', $cont[0]); ?></td>
					</tr>
						<?php
						}
					}
					$kr = 1 - $kr;
				}
				?>
				</table>
			</div>
			<script type="text/javascript">
			jQuery(function() {
				jQuery(".vcm-dash-openchildn").click(function() {
					var parent_name = jQuery(this).attr("id");
					var parent_id = parent_name.split("parentn");
					if (jQuery(".vcm-childrow"+parent_id[1])) {
						jQuery(".vcm-childrow"+parent_id[1]).toggleClass("vcm-dash-hidden");
						jQuery(this).find("i.vcm-arrow").toggleClass("vcm-arrow-right").toggleClass("vcm-arrow-down");
					}
				});
				jQuery(".vcmmodal-notif").click(function(){
					jQuery(this).parent("td").parent("tr").removeClass("vcm-notif-toberead");
				});
			});
			function vcmRmNotifs() {
				if (confirm('<?php echo addslashes(JText::_('VCMREMOVECONFIRM')); ?>')) {
					jQuery('#adminForm').append('<input type="hidden" name="rmnotifications" value="1" />');
					return true;
				}
				return false;
			}
			</script>
			<input type="hidden" id="notif_filter" name="notif_filter" value="<?php echo $notif_filter; ?>"/>
			<input type="hidden" name="filter_hash" value="<?php echo !empty($notif_filter) ? md5($notif_filter) : ''; ?>"/>
			<input type="hidden" name="option" value="com_vikchannelmanager"/>
			<?php echo $navbut; ?>
		</form>
		<?php
			/**
			 * We read all notifications when opening the page Dashboard.
			 * 
			 * @since 	1.8.24
			 */
			VikChannelManager::readNotifications($notifications, $read_all = true);
		}
		?>

	</div>
</div>

<?php } ?>
