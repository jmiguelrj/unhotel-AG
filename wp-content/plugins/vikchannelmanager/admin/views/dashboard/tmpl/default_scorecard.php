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

// VCM Application for popOver
$vcm_app = new VikApplication(VersionListener::getID());

// session handler for previously stored values
$session = JFactory::getSession();

/**
 * Score-card is only available for a few channels.
 * 
 * @since 	1.8.0 	added support for Airbnb API
 * @since 	1.8.4 	added support for Google Hotel
 */
$eligible_channels = [
	VikChannelManagerConfig::BOOKING,
	VikChannelManagerConfig::AIRBNBAPI,
	VikChannelManagerConfig::GOOGLEHOTEL,
];

$module = VikChannelManager::getActiveModule(true);
$module['params'] = !empty($module['params']) ? json_decode($module['params'], true) : array();
$module['params'] = !is_array($module['params']) ? array() : $module['params'];
$hotelid = null;
$req_channel = 0;

if (in_array($module['uniquekey'], $eligible_channels)) {
	$req_channel = $module['uniquekey'];
	foreach ($module['params'] as $param_name => $param_value) {
		// grab the first channel parameter
		$hotelid = $param_value;
		break;
	}
}

// get the scorecard from the session (if previously set)
$scorecard = $session->get("scorecard_{$req_channel}_{$hotelid}", '', 'vcm-scorecard');

if ($scorecard && $module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && !empty($scorecard->data->hosting_quality)) {
	// make sure to normalize the hosting quality data for display if it was saved in the session
	$scorecard = json_decode(json_encode($scorecard), true);
	$scorecard['data']['hosting_quality'] = VCMAirbnbContent::normalizeHostingQualityData($scorecard['data']['hosting_quality'], [
		'host_id' => $hotelid,
		'purpose' => 'scorecard',
	]);
}

// tell whether the scorecard should be retrieved with an AJAX request
$get_scorecard = !empty($hotelid) && empty($scorecard) ? 1 : 0;

if (!empty($hotelid)) {
	// we can display the content because this channel supports scorecards

	// adjust channel name, if necessary
	if ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
		$module['name'] = 'airbnb';
	} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
		$module['name'] = 'google hotel';
	}

	// get the channel logo
	$channel_info = VikChannelManager::getChannel($req_channel);
	$logo = VikChannelManager::getLogosInstance($channel_info['name']);
	$ch_logo_url = $logo->getLogoURL();

	// get property name
	$prop_name = (string)VikChannelManager::getChannelPropertyName($req_channel, $hotelid);

	// add language definitions for JS
	JText::script('VCMSCORECARD_REVIEW_SCORE');
	JText::script('VCMSCORECARD_REPLY_SCORE');
	JText::script('VCMSCORECARD_CONTENT_SCORE');
	JText::script('VCMSCORECARD_AREA_AVERAGE_SCORE');
	JText::script('VCMSHOWMORE');
	JText::script('VCMSCORECARD_OVERALL_RATING');
	JText::script('VCMSCORECARD_TOT_REVIEWS');
	JText::script('VCMSCORECARD_HOTEL_STATUS');
	JText::script('VCM_GOOGLEHOTEL_ONFEED');
	JText::script('VCM_GOOGLEHOTEL_MATCHMAPS');
	JText::script('VCM_GOOGLEHOTEL_LIVEOG');
	JText::script('VCM_GOOGLEHOTEL_LIVEOG_TOGGLE');
	JText::script('VCM_GOOGLEHOTEL_FBLR_CLICKS');
	JText::script('VCM_DEVICE');
	JText::script('VCM_REGION');
	JText::script('VCM_GOOGLEHOTEL_NOSCORECARD_HELP');
	JText::script('VCM_HOSTING_QUALITY');
	JText::script('VCM_SUMMARY');
	JText::script('VCMMENUDASHBOARD');
	JText::script('VCM_N_LISTINGS');
	JText::script('VCM_TIMES');
	JText::script('VCM_RATINGS');
	JText::script('VCM_BEST_RATING');
	JText::script('VCM_WORST_RATING');
	JText::script('VCMMENUREVIEWS');
	JText::script('VCM_MAXIMUM');
	JText::script('VCM_MINIMUM');
	JText::script('VCM_CATEGORY_RATINGS');
	JText::script('VCM_GUEST_FEEDBACK');
	JText::script('VCM_MOST_FREQUENT');
	JText::script('VCM_LEAST_FREQUENT');
	?>

<div class="vcmdashdivleft-row vcm-dashboard-scorecard vcm-dashboard-scorecard-<?php echo preg_replace("/[^a-zA-Z0-9]+/", '', $channel_info['name']); ?>" id="vcm-dashboard-scorecard" style="<?php echo $get_scorecard ? 'display: none;' : ''; ?>">
	<h3 class="vcmdashdivlefthead">
		<span>
		<?php
		echo JText::sprintf(
			'VCMDASHCHSCORECARD', 
			(!empty($ch_logo_url) ? '<img class="vcm-scorecard-logo" src="' . $ch_logo_url . '" title="' . htmlspecialchars(ucwords($module['name'])) . '" />' : ucwords($module['name'])), 
			'<span id="vcm-dashboard-scorecard-propname">' . $prop_name . '</span>'
		);
		?>
		</span>
		<?php
		echo $vcm_app->createPopover(array(
			'title'     => JText::sprintf('VCMDASHCHSCORECARD', ucwords($module['name']), ''),
			'content'   => JText::_('VCMDASHCHSCORECARDHELP'),
			'placement' => 'right',
		));
		?>
	</h3>
	<div class="vcm-dashboard-cont-wrap">
	<?php
	// multiple accounts drop down
	$multi_accounts = VikChannelManager::getChannelAccountsMapped($req_channel);
	if (count($multi_accounts) > 1) {
		// VCM has mapped multiple accounts, build the drop down
		?>
		<div class="vcm-dashboard-scorecard-multiaccounts">
			<div class="vcm-dashboard-scorecard-multiaccounts-sel">
				<select id="vcm-scorecard-selaccount">
				<?php
				foreach ($multi_accounts as $hid => $pname) {
					?>
					<option value="<?php echo $hid; ?>"<?php echo $hid == $hotelid ? ' selected="selected"' : ''; ?>><?php echo $pname; ?></option>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		<?php
	}
	?>
		<div class="vcmdashdivleft-inner vcm-dashboard-scorecard-inner"></div>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	vcmDisplayScorecard(<?php echo $get_scorecard; ?>);
	jQuery('#vcm-scorecard-selaccount').change(function() {
		jQuery('.vcm-dashboard-scorecard-inner').html('');
		let req_hid = jQuery(this).val();
		let req_pname = jQuery(this).find('option:selected').text();
		if (!req_hid.length || !req_pname.length) {
			return false;
		}
		jQuery('#vcm-dashboard-scorecard-propname').text(req_pname);
		// load the new scorecard
		vcmGetScorecard(req_hid);
	});
});

const vcm_score_icn_enabled = '<?php (class_exists('VikBookingIcons') ? VikBookingIcons::e('far fa-check-circle') : ''); ?>';
const vcm_score_icn_disabled = '<?php (class_exists('VikBookingIcons') ? VikBookingIcons::e('far fa-times-circle') : ''); ?>';
const vcm_score_icn_star = '<?php (class_exists('VikBookingIcons') ? VikBookingIcons::e('star') : ''); ?>';
const vcm_enabled_icn = '<?php (class_exists('VikBookingIcons') ? VikBookingIcons::e('check-circle') : ''); ?>';
const vcm_disabled_icn = '<?php (class_exists('VikBookingIcons') ? VikBookingIcons::e('times-circle') : ''); ?>';

function vcmGetScoreCardTn(key) {
	let base_tn   = 'VCMSCORECARD_';
	let check_key = (key + '').toUpperCase();
	let final_key = base_tn + check_key;
	let get_tn = Joomla.JText._(final_key);
	if (!get_tn || !get_tn.length || get_tn == final_key) {
		// no valid translation found, return the original key
		return key;
	}

	// return the translated string
	return get_tn;
}

function vcmGetValueImpactEnum(value, thresholds) {
	if (isNaN(value)) {
		return '';
	}
	value = parseFloat(value);

	if (!thresholds || typeof thresholds !== 'object' || !thresholds.hasOwnProperty('success') || !thresholds.hasOwnProperty('warning')) {
		// define default thresholds map
		thresholds = {
			success: 4,
			warning: 3.5,
		};
	}

	if (value > (thresholds.success || 4)) {
		return 'success';
	}

	if (value > (thresholds.warning || 3.5)) {
		return 'warning';
	}

	if (thresholds?.neutral && value >= thresholds.neutral) {
		return 'neutral';
	}

	return 'error';
}

function vcmScorecardShowMetrics() {
	jQuery('#vcm-dashboard-scorecard-showmetrics').remove();
	jQuery('.vcm-dashboard-scorecard-score-summary-metrics-metric').show();
}

function vcmToggleLiveOnGoogle() {
	if (!confirm(Joomla.JText._('VCM_GOOGLEHOTEL_LIVEOG_TOGGLE'))) {
		return false;
	} else {
		document.location.href = 'index.php?option=com_vikchannelmanager&task=ghotel.toggle_live_on_google';
	}
}

function vcmDisplayScorecard(action) {
	if (action) {
		vcmGetScorecard('<?php echo (string)$hotelid; ?>');
	} else {
		var scorecard = <?php echo json_encode((!empty($scorecard) && !is_scalar($scorecard) ? $scorecard : (new stdClass))); ?>;
		vcmRenderScorecard(scorecard);
	}
}

function vcmGetScorecard(hotelid) {
	// make the AJAX request because this channel seems to support scorecards
	jQuery.ajax({
		type: "POST",
		url: "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=get_property_score'); ?>",
		data: {
			hotelid: hotelid,
			uniquekey: "<?php echo $channel_info['uniquekey']; ?>",
		}
	}).done(function(resp) {
		// parse the response
		try {
			resp = typeof resp === 'string' ? JSON.parse(resp) : resp;
		} catch (e) {
			console.error('Could not decode scorecard response', e, resp);
			resp = null;
		}
		if (!resp || !resp.hasOwnProperty('data')) {
			// silently exit the process
			console.error('Invalid scorecard response', resp);
			return;
		}

		// render the scorecard by using the response obtained
		vcmRenderScorecard(resp);
	}).fail(function(err) {
		// this is a silent request
		console.log(err.responseText);
		console.info('Channel Scorecard could not be displayed');
	<?php
	if ($channel_info['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
		// we need to display a message that says the property must be approved and rooms must be mapped
		?>
		let htmlscore = '<p class="err">' + err.responseText + '</p>' + "\n";
		htmlscore += '<p class="warn">' + Joomla.JText._('VCM_GOOGLEHOTEL_NOSCORECARD_HELP') + '</p>';
		jQuery('#vcm-dashboard-scorecard').find('.vcm-dashboard-scorecard-inner').html(htmlscore);
		if (!jQuery('#vcm-dashboard-scorecard').is(':visible')) {
			jQuery('#vcm-dashboard-scorecard').fadeIn();
		}
		<?php
	}
	?>
	});
}

function vcmLoadScorecardLogo(remote_uri, success_callback, error_callback) {
	let remote_img = new Image();
	remote_img.onload = success_callback;
	remote_img.onerror = error_callback;
	remote_img.src = remote_uri;

	return;
}

function vcmRenderScorecard(scorecard) {
	if (!scorecard || !scorecard.hasOwnProperty('data')) {
		// empty data, quit process
		jQuery('#vcm-dashboard-scorecard').hide();
		return;
	}

	// check if a logo/profile pic should be loaded remotely
	let load_logo = null;

	// check if hosting quality is available
	let hosting_quality = scorecard?.data?.hosting_quality || null;
	if (hosting_quality) {
		// get rid of the original object property
		delete scorecard.data.hosting_quality;
	}

	// build the HTML score
	let htmlscore = '';
	for (let scoretype in scorecard.data) {
		if (!scorecard.data.hasOwnProperty(scoretype) || !scorecard.data[scoretype].hasOwnProperty('summary')) {
			// score summary is mandatory
			continue;
		}
		htmlscore += '<div class="vcmdashdivitem vcm-dashboard-scorecard-score">' + "\n";
		htmlscore += '	<span class="vcmdashdivitem-lbl">' + vcmGetScoreCardTn(scoretype) + '</span>' + "\n";
		htmlscore += '	<div class="vcm-dashboard-scorecard-score-summary">' + "\n";
		htmlscore += '		<div class="vcm-dashboard-scorecard-score-summary-score">' + "\n";
		if (scorecard.data[scoretype]['summary'].hasOwnProperty('logo_url')) {
			htmlscore += '		<div class="vcm-dashboard-scorecard-score-summary-score-left">' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-score-summary-score-logo" style="display: none;"></span>' + "\n";
			htmlscore += '		</div>' + "\n";
			// set flag for loading the logo
			load_logo = scorecard.data[scoretype]['summary']['logo_url'];
		}
		if (scorecard.data[scoretype]['summary'].hasOwnProperty('score')) {
			htmlscore += '		<div class="vcm-dashboard-scorecard-score-summary-score-top">' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-score-summary-score-current">' + scorecard.data[scoretype]['summary']['score'] + '</span>' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-score-summary-score-sep">/</span>' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-score-summary-score-max">' + scorecard.data[scoretype]['summary']['max_score'] + '</span>' + "\n";
			if (scorecard.data[scoretype]['summary'].hasOwnProperty('star')) {
				htmlscore += '		<span class="vcm-dashboard-scorecard-score-summary-score-star">' + vcm_score_icn_star + '</span>' + "\n";
			}
			htmlscore += '		</div>' + "\n";
		}
		if (scorecard.data[scoretype]['summary'].hasOwnProperty('on_feed')) {
			let extra_class_status = '';
			let is_feed_active = (scorecard.data[scoretype]['summary']['on_feed'] > 0);
			extra_class_status = is_feed_active ? 'vcm-dashboard-scorecard-score-summary-score-status' : 'vcm-dashboard-scorecard-score-summary-score-status-warn';
			htmlscore += '		<div class="vcm-dashboard-scorecard-score-summary-score-top ' + extra_class_status + '">' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-status-icn">' + (is_feed_active ? vcm_enabled_icn : vcm_disabled_icn) + '</span>' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-status-val">' + Joomla.JText._('VCM_GOOGLEHOTEL_ONFEED') + '</span>' + "\n";
			htmlscore += '		</div>' + "\n";
			let has_matched = (scorecard.data[scoretype]['summary']['matched'] > 0);
			extra_class_status = has_matched ? 'vcm-dashboard-scorecard-score-summary-score-status' : 'vcm-dashboard-scorecard-score-summary-score-status-warn';
			htmlscore += '		<div class="vcm-dashboard-scorecard-score-summary-score-top ' + extra_class_status + '">' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-status-icn">' + (has_matched ? vcm_enabled_icn : vcm_disabled_icn) + '</span>' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-status-val">' + Joomla.JText._('VCM_GOOGLEHOTEL_MATCHMAPS') + '</span>' + "\n";
			htmlscore += '		</div>' + "\n";
			let is_serv_live = (scorecard.data[scoretype]['summary']['is_live'] > 0);
			extra_class_status = is_serv_live ? 'vcm-dashboard-scorecard-score-summary-score-status' : 'vcm-dashboard-scorecard-score-summary-score-status-warn';
			htmlscore += '		<div class="vcm-dashboard-scorecard-score-summary-score-top vcm-dashboard-scorecard-score-btn ' + extra_class_status + '" onclick="vcmToggleLiveOnGoogle();">' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-status-icn">' + (is_serv_live ? vcm_enabled_icn : vcm_disabled_icn) + '</span>' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-status-val">' + Joomla.JText._('VCM_GOOGLEHOTEL_LIVEOG') + '</span>' + "\n";
			htmlscore += '		</div>' + "\n";
		}
		if (scorecard.data[scoretype].hasOwnProperty('report') && scorecard.data[scoretype]['report'].hasOwnProperty('stats')) {
			// parse and display "report" property values, common with GoogleHotel
			if (Array.isArray(scorecard.data[scoretype]['report']['stats']) && scorecard.data[scoretype]['report']['stats'].length) {
				htmlscore += '<div class="vcm-dashboard-scorecard-score-summary-score-bottom vcm-dashboard-scorecard-ghblinks-report">' + "\n";
				for (let ghi = 0; ghi < scorecard.data[scoretype]['report']['stats'].length; ghi++) {
					if (ghi >= 8) {
						// we do not display so many information
						break;
					}
					let clicks_report_info = scorecard.data[scoretype]['report']['stats'][ghi];
					if (!clicks_report_info.hasOwnProperty('date') || !clicks_report_info.hasOwnProperty('clickCount')) {
						continue;
					}
					htmlscore += '<div class="vcm-dashboard-scorecard-ghblink-details">' + "\n";
					htmlscore += '	<div class="vcm-ghblink-main">' + "\n";
					htmlscore += '		<span class="vcm-ghblink-date">' + clicks_report_info['date'] + '</span>' + "\n";
					htmlscore += '		<div class="vcm-ghblink-data">' + "\n";
					htmlscore += '			<span class="vcm-ghblink-lbl">' + Joomla.JText._('VCM_GOOGLEHOTEL_FBLR_CLICKS') + '</span>' + "\n";
					htmlscore += '			<span class="vcm-ghblink-val vcm-ghblink-clicks">' + clicks_report_info['clickCount'] + '</span>' + "\n";
					htmlscore += '		</div>' + "\n";
					htmlscore += '	</div>' + "\n";
					htmlscore += '	<div class="vcm-ghblink-info">' + "\n";
					if (clicks_report_info.hasOwnProperty('deviceType')) {
						htmlscore += '		<div class="vcm-ghblink-data">' + "\n";
						htmlscore += '			<span class="vcm-ghblink-lbl">' + Joomla.JText._('VCM_DEVICE') + '</span>' + "\n";
						htmlscore += '			<span class="vcm-ghblink-val vcm-ghblink-device">' + clicks_report_info['deviceType'] + '</span>' + "\n";
						htmlscore += '		</div>' + "\n";
					}
					if (clicks_report_info.hasOwnProperty('userRegionCode')) {
						htmlscore += '		<div class="vcm-ghblink-data">' + "\n";
						htmlscore += '			<span class="vcm-ghblink-lbl">' + Joomla.JText._('VCM_REGION') + '</span>' + "\n";
						htmlscore += '			<span class="vcm-ghblink-val vcm-ghblink-region">' + clicks_report_info['userRegionCode'] + '</span>' + "\n";
						htmlscore += '		</div>' + "\n";
					}
					htmlscore += '	</div>' + "\n";
					htmlscore += '</div>' + "\n";
				}
				htmlscore += '</div>' + "\n";
			}
		}
		if (scorecard.data[scoretype]['summary'].hasOwnProperty('reviews')) {
			htmlscore += '		<div class="vcm-dashboard-scorecard-score-summary-score-bottom">' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-score-summary-tot-reviews">' + "\n";
			htmlscore += '				<span>' + Joomla.JText._('VCMSCORECARD_TOT_REVIEWS') + '</span>' + "\n";
			htmlscore += '				<strong>' + scorecard.data[scoretype]['summary']['reviews'] + '</strong>' + "\n";
			htmlscore += '			</span>' + "\n";
			htmlscore += '		</div>' + "\n";
			// set flag for loading the logo
			load_logo = scorecard.data[scoretype]['summary']['logo_url'];
		}
		if (scorecard.data[scoretype]['summary'].hasOwnProperty('area_average_score') && scorecard.data[scoretype]['summary']['area_average_score'] !== null) {
			htmlscore += '		<div class="vcm-dashboard-scorecard-score-summary-score-avgarea">' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-score-summary-score-avgarea-lbl">' + Joomla.JText._('VCMSCORECARD_AREA_AVERAGE_SCORE') + '</span>' + "\n";
			htmlscore += '			<span class="vcm-dashboard-scorecard-score-summary-score-avgarea-val">' + scorecard.data[scoretype]['summary']['area_average_score'] + '</span>' + "\n";
			htmlscore += '		</div>' + "\n";
		}
		htmlscore += '		</div>' + "\n";
		if (scorecard.data[scoretype].hasOwnProperty('metrics') && scorecard.data[scoretype]['metrics'].length) {
			// scores can have metrics
			htmlscore += '	<div class="vcm-dashboard-scorecard-score-summary-metrics">' + "\n";
			let metrics_counter = 0;
			let metrics_limit   = 5;
			for (let mind in scorecard.data[scoretype]['metrics']) {
				if (!scorecard.data[scoretype]['metrics'].hasOwnProperty(mind) || !scorecard.data[scoretype]['metrics'][mind].hasOwnProperty('action') || !scorecard.data[scoretype]['metrics'][mind].hasOwnProperty('done')) {
					// invalid metric structure
					continue;
				}
				metrics_counter++;
				let metric_visibility = metrics_counter > metrics_limit ? 'style="display: none;" ' : '';
				let metric_done = (parseInt(scorecard.data[scoretype]['metrics'][mind]['done']) > 0);
				htmlscore += '	<div ' + metric_visibility + 'class="vcm-dashboard-scorecard-score-summary-metrics-metric ' + (metric_done ? 'vcm-dashboard-scorecard-metric-done' : 'vcm-dashboard-scorecard-metric-undone') + '">' + "\n";
				htmlscore += '		<span>' + (metric_done ? vcm_score_icn_enabled : vcm_score_icn_disabled) + ' ' + scorecard.data[scoretype]['metrics'][mind]['action'] + '</span>' + "\n";
				htmlscore += '	</div>' + "\n";
			}
			if (metrics_counter > metrics_limit) {
				// display button to show all metrics
				htmlscore += '	<button type="button" class="btn" id="vcm-dashboard-scorecard-showmetrics" onclick="vcmScorecardShowMetrics();">' + Joomla.JText._('VCMSHOWMORE') + '</button>' + "\n";
			}
			htmlscore += '	</div>' + "\n";
		}
		htmlscore += '	</div>' + "\n";
		htmlscore += '</div>' + "\n";
	}

	// check if hosting quality data should be rendered
	if (hosting_quality) {
		// start container
		htmlscore += '<div class="vcmdashdivitem vcm-dashboard-scorecard-score vcm-scorecard-hosting-quality">' + "\n";
		// label/title
		htmlscore += '	<span class="vcmdashdivitem-lbl">' + Joomla.JText._('VCM_HOSTING_QUALITY') + ' - ' + Joomla.JText._('VCM_SUMMARY') + '</span>' + "\n";
		// link to hosting quality dashboard
		let hq_dash_link = '<a class="btn btn-small btn-primary vcm-hosting-quality-dashboard-link" href="<?php echo VCMFactory::getPlatform()->getUri()->admin('index.php?option=com_vikchannelmanager&view=hostingquality', $xhtml = false); ?>">' + Joomla.JText._('VCM_HOSTING_QUALITY') + ' - ' + Joomla.JText._('VCMMENUDASHBOARD') + '</a>';
		// total number of listings
		htmlscore += '	<div class="vcm-hosting-quality-tot-listings"><?php VikBookingIcons::e('home'); ?> <span>' + Joomla.JText._('VCM_N_LISTINGS').replace('%d', hosting_quality?.info?.tot_listings || 0) + '</span> ' + hq_dash_link + '</div>' + "\n";
		// start blocks
		htmlscore += '	<div class="vcm-hosting-quality-blocks">' + "\n";

		// ratings block
		htmlscore += '		<div class="vcm-hosting-quality-block" data-type="ratings">' + "\n";
		htmlscore += '			<div class="vcm-hosting-quality-block-title"><?php VikBookingIcons::e('star'); ?> <span>' + Joomla.JText._('VCM_RATINGS') + '</span></div>' + "\n";
		htmlscore += '			<div class="vcm-hosting-quality-block-stats">' + "\n";
		htmlscore += '				<div class="vcm-hosting-quality-block-stat" data-impact="' + vcmGetValueImpactEnum(hosting_quality?.stats?.best_rating_listing_score) + '">' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-text">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-main">' + (hosting_quality?.listings_map[hosting_quality?.stats?.best_rating_listing_id] || hosting_quality?.stats?.best_rating_listing_id) + '</div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-sub">' + Joomla.JText._('VCM_BEST_RATING') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-score">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-main">' + (hosting_quality?.stats?.best_rating_listing_score || '?') + '<span class="vcm-hosting-quality-score-submain">/5</span></div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '				</div>' + "\n";
		htmlscore += '				<div class="vcm-hosting-quality-block-stat" data-impact="' + vcmGetValueImpactEnum(hosting_quality?.stats?.worst_rating_listing_score) + '">' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-text">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-main">' + (hosting_quality?.listings_map[hosting_quality?.stats?.worst_rating_listing_id] || hosting_quality?.stats?.worst_rating_listing_id) + '</div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-sub">' + Joomla.JText._('VCM_WORST_RATING') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-score">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-main">' + (hosting_quality?.stats?.worst_rating_listing_score || '?') + '<span class="vcm-hosting-quality-score-submain">/5</span></div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '				</div>' + "\n";
		htmlscore += '			</div>' + "\n";
		htmlscore += '		</div>' + "\n";

		// reviews block
		htmlscore += '		<div class="vcm-hosting-quality-block" data-type="reviews">' + "\n";
		htmlscore += '			<div class="vcm-hosting-quality-block-title"><?php VikBookingIcons::e('comments'); ?> <span>' + Joomla.JText._('VCMMENUREVIEWS') + '</span><span class="vcm-hosting-quality-block-title-sub">' + (hosting_quality?.info?.tot_reviews || 0) + '</span></div>' + "\n";
		htmlscore += '			<div class="vcm-hosting-quality-block-stats">' + "\n";
		htmlscore += '				<div class="vcm-hosting-quality-block-stat" data-impact="' + vcmGetValueImpactEnum(hosting_quality?.stats?.most_reviewed_listing_count, {success: 50, warning: 20, neutral: 9}) + '">' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-text">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-main">' + (hosting_quality?.listings_map[hosting_quality?.stats?.most_reviewed_listing_id] || hosting_quality?.stats?.most_reviewed_listing_id) + '</div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-sub">' + Joomla.JText._('VCM_MAXIMUM') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-score">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-main">' + (hosting_quality?.stats?.most_reviewed_listing_count || '?') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '				</div>' + "\n";
		htmlscore += '				<div class="vcm-hosting-quality-block-stat" data-impact="' + vcmGetValueImpactEnum(hosting_quality?.stats?.least_reviewed_listing_count, {success: 50, warning: 20, neutral: 9}) + '">' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-text">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-main">' + (hosting_quality?.listings_map[hosting_quality?.stats?.least_reviewed_listing_id] || hosting_quality?.stats?.least_reviewed_listing_id) + '</div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-sub">' + Joomla.JText._('VCM_MINIMUM') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-score">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-main">' + (hosting_quality?.stats?.least_reviewed_listing_count || '?') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '				</div>' + "\n";
		htmlscore += '			</div>' + "\n";
		htmlscore += '		</div>' + "\n";

		// review categories
		let best_review_category = {
			name: '',
			score: 0,
		};
		let worst_review_category = {
			name: '',
			score: 0,
		};
		for (const [categoryName, categoryScore] of Object.entries(hosting_quality?.stats?.rank_review_categories || {})) {
			if (!best_review_category.name) {
				// set only once
				best_review_category.name = categoryName;
				best_review_category.score = categoryScore;
			}
			// always overwrite the worst category until the last is reached
			worst_review_category.name = categoryName;
			worst_review_category.score = categoryScore;
		}

		htmlscore += '		<div class="vcm-hosting-quality-block" data-type="review-categories">' + "\n";
		htmlscore += '			<div class="vcm-hosting-quality-block-title"><?php VikBookingIcons::e('chart-line'); ?> <span>' + Joomla.JText._('VCM_CATEGORY_RATINGS') + '</span></div>' + "\n";
		htmlscore += '			<div class="vcm-hosting-quality-block-stats">' + "\n";
		htmlscore += '				<div class="vcm-hosting-quality-block-stat" data-impact="' + vcmGetValueImpactEnum(best_review_category.score) + '">' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-text">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-main">' + (best_review_category.name.charAt(0).toUpperCase() + best_review_category.name.slice(1)) + '</div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-sub">' + Joomla.JText._('VCM_BEST_RATING') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-score">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-main">' + best_review_category.score + '<span class="vcm-hosting-quality-score-submain">/5</span></div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '				</div>' + "\n";
		htmlscore += '				<div class="vcm-hosting-quality-block-stat" data-impact="' + vcmGetValueImpactEnum(worst_review_category.score) + '">' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-text">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-main">' + (worst_review_category.name.charAt(0).toUpperCase() + worst_review_category.name.slice(1)) + '</div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-sub">' + Joomla.JText._('VCM_WORST_RATING') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-score">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-main">' + worst_review_category.score + '<span class="vcm-hosting-quality-score-submain">/5</span></div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '				</div>' + "\n";
		htmlscore += '			</div>' + "\n";
		htmlscore += '		</div>' + "\n";

		// review category tags
		let most_freq_positive_category_tag = {
			name: '',
			count: 0,
		};
		let most_freq_negative_category_tag = {
			name: '',
			count: 0,
		};
		for (const [tagName, tagCount] of Object.entries(hosting_quality?.stats?.top_positive_category_tags || {})) {
			// set only the first
			let lowerTagName = (tagName + '').toLowerCase();
			most_freq_positive_category_tag.name = hosting_quality?.category_tags_map[lowerTagName] || tagName.split('_').join(' ');
			most_freq_positive_category_tag.count = tagCount;
			break;
		}
		for (const [tagName, tagCount] of Object.entries(hosting_quality?.stats?.top_negative_category_tags || {})) {
			// set only the first
			let lowerTagName = (tagName + '').toLowerCase();
			most_freq_negative_category_tag.name = hosting_quality?.category_tags_map[lowerTagName] || tagName.split('_').join(' ');
			most_freq_negative_category_tag.count = tagCount;
			break;
		}

		htmlscore += '		<div class="vcm-hosting-quality-block" data-type="review-category-tags">' + "\n";
		htmlscore += '			<div class="vcm-hosting-quality-block-title"><?php VikBookingIcons::e('thumbs-up'); ?> <span>' + Joomla.JText._('VCM_GUEST_FEEDBACK') + '</span></div>' + "\n";
		htmlscore += '			<div class="vcm-hosting-quality-block-stats">' + "\n";
		htmlscore += '				<div class="vcm-hosting-quality-block-stat" data-impact="success">' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-text">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-main"><span class="label label-info">' + Joomla.JText._('VCM_MOST_FREQUENT') + '</span></div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-sub">' + most_freq_positive_category_tag.name + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-score">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-main">' + most_freq_positive_category_tag.count + '</div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-sub">' + Joomla.JText._('VCM_TIMES') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '				</div>' + "\n";
		htmlscore += '				<div class="vcm-hosting-quality-block-stat" data-impact="error">' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-text">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-main"><span class="label label-error">' + Joomla.JText._('VCM_MOST_FREQUENT') + '</span></div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-text-sub">' + most_freq_negative_category_tag.name + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '					<div class="vcm-hosting-quality-block-stat-score">' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-main">' + most_freq_negative_category_tag.count + '</div>' + "\n";
		htmlscore += '						<div class="vcm-hosting-quality-block-stat-score-sub">' + Joomla.JText._('VCM_TIMES') + '</div>' + "\n";
		htmlscore += '					</div>' + "\n";
		htmlscore += '				</div>' + "\n";
		htmlscore += '			</div>' + "\n";
		htmlscore += '		</div>' + "\n";

		// close blocks
		htmlscore += '	</div>' + "\n";

		// close container
		htmlscore += '</div>' + "\n";
	}

	// load logo, if necessary (to avoid displaying a broken image)
	if (load_logo) {
		vcmLoadScorecardLogo(load_logo, function() {
			let logo_target = jQuery('.vcm-dashboard-scorecard-score-summary-score-logo');
			jQuery(this).addClass('vcm-dashboard-scorecard-profilepic').appendTo(logo_target);
			logo_target.fadeIn();
		}, function() {
			console.error('Could not load profile picture', load_logo);
		});
	}

	// append scorecard HTML score and display container
	jQuery('#vcm-dashboard-scorecard').find('.vcm-dashboard-scorecard-inner').html(htmlscore);
	if (!jQuery('#vcm-dashboard-scorecard').is(':visible')) {
		jQuery('#vcm-dashboard-scorecard').fadeIn();
	}

}
</script>

<?php
} else {
	// do nothing as the currently active channel does not support any scorecard
}
