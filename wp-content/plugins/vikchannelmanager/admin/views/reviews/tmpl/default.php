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

// the ID of the review set by VBO
$revid = VikRequest::getInt('revid', '', 'request');

// container of plain reviews content
$revs_raw_cont = new stdClass;
$revs_raw_dets = new stdClass;

// score ranges for the CSS classes
$score_ranges = array(
	// red
	3 => 'vcm-rev-score-one-fourth',
	// orange
	6 => 'vcm-rev-score-two-fourth',
	// yellow
	8 => 'vcm-rev-score-three-fourth',
	// green
	10 => 'vcm-rev-score-four-fourth',
);

// reviews translated keywords
$revs_prop_tx = array(
	'created_timestamp' => 'VCMPVIEWORDERSVBONE',
	'reply' 			=> 'VCMREVREPLYREV',
	'reviewee_response' => 'VCMREVREPLYREV',
	'reviewer' 			=> 'VCMPVIEWORDERSVBTWO',
	'country_code' 		=> 'VCMTACHOTELCOUNTRY',
	'name' 				=> 'VCMBCAHFIRSTNAME',
	'reservation_id' 	=> 'VCMSMARTBALBID',
	'review_id' 		=> 'VCMREVIEWID',
	'scoring' 			=> 'VCMREVIEWSCORE',
	'value' 			=> 'VCMGREVVALUE',
	'value_for_money' 	=> 'VCMGREVVALUE',
	'clean' 			=> 'VCMGREVCLEAN',
	'cleanliness' 		=> 'VCMGREVCLEAN',
	'comfort' 			=> 'VCMGREVCOMFORT',
	'location' 			=> 'VCMGREVLOCATION',
	'facilities' 		=> 'VCMGREVFACILITIES',
	'staff' 			=> 'VCMGREVSTAFF',
	'review_score' 		=> 'VCMTOTALSCORE',
	'content' 			=> 'VCMGREVCONTENT',
	'message' 			=> 'VCMGREVMESSAGE',
	'text' 				=> 'VCMGREVMESSAGE',
	'headline' 			=> 'VCMGREVMESSAGE',
	'language_code' 	=> 'VCMBCAHLANGUAGE',
	'negative' 			=> 'VCMGREVNEGATIVE',
	'positive' 			=> 'VCMGREVPOSITIVE',
	'public_review' 	=> 'VCMGREVPOSITIVE',
);
foreach ($revs_prop_tx as $prop_tx) {
	JText::script($prop_tx);
}

JText::script('VCM_AI_CHAT_TOOLTIP');
JText::script('VCM_GUEST_REVIEW');

// filters
JHtml::_('behavior.calendar');

?>

<div class="vcm-list-form-filters vcm-btn-toolbar">
	<form action="index.php?option=com_vikchannelmanager&task=reviews" method="post" id="vcm-filters-form">
		<div id="filter-bar" class="btn-toolbar" style="width: 100%; display: inline-block;">
			<div class="btn-group pull-left">
				<?php echo JHTML::_('calendar', '', 'fromdate', 'fromdate', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VCMFROMDATE'))); ?>
			</div>
			<div class="btn-group pull-left">
				<?php echo JHTML::_('calendar', '', 'todate', 'todate', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VCMTODATE'))); ?>
			</div>
			<div class="btn-group pull-left">
				<select name="prop_name" id="propname-filt">
					<option></option>
				<?php
				foreach ($this->propnames as $v) {
					?>
					<option value="<?php echo $v; ?>"<?php echo $v == $this->filters['prop_name'] ? ' selected="selected"' : ''; ?>><?php echo $v; ?></option>
					<?php
				}
				?>
				</select>
			</div>
			<div class="btn-group pull-left">
				<select name="channel" id="channel-filt">
					<option></option>
				<?php
				foreach ($this->channels as $v) {
					?>
					<option value="<?php echo $v; ?>"<?php echo $v == $this->filters['channel'] ? ' selected="selected"' : ''; ?>><?php echo $v; ?></option>
					<?php
				}
				?>
				</select>
			</div>
			<div class="btn-group pull-left">
				<select name="lang" id="lang-filt">
					<option></option>
				<?php
				foreach ($this->langs as $v) {
					?>
					<option value="<?php echo $v; ?>"<?php echo $v == $this->filters['lang'] ? ' selected="selected"' : ''; ?>><?php echo strtoupper($v); ?></option>
					<?php
				}
				?>
				</select>
			</div>
			<div class="btn-group pull-left">
				<select name="country" id="country-filt">
					<option></option>
				<?php
				foreach ($this->countries as $v) {
					?>
					<option value="<?php echo $v; ?>"<?php echo $v == $this->filters['country'] ? ' selected="selected"' : ''; ?>><?php echo strtoupper($v); ?></option>
					<?php
				}
				?>
				</select>
			</div>
		<?php
		if (!empty($revid)) {
			?>
			<div class="btn-group pull-left">
				<a href="index.php?option=com_vikchannelmanager&task=reviews" class="btn btn-danger"><i class="vboicn-cross"></i> <?php echo JText::_('VCMREVIEWID') . ' ' . $revid; ?></a>
			</div>
			<?php
		}
		?>
			<div class="btn-group pull-left">
				&nbsp;&nbsp;&nbsp;
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-secondary"><i class="vboicn-search"></i> <?php echo JText::_('VCMBCAHSUBMIT'); ?></button>
			</div>
			<div class="btn-group pull-left">
				<button type="button" class="btn" onclick="vcmClearFilters();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
		<?php
		if (in_array($this->channel['uniquekey'], array(VikChannelManagerConfig::BOOKING, VikChannelManagerConfig::AIRBNBAPI))) {
			// print the button to download the reviews from this channel
			$hotel_id = '';
			if (!empty($this->channel['params'])) {
				// prepend the property name if available
				if (!empty($this->channel['prop_name'])) {
					$hotel_id .= $this->channel['prop_name'] . ' ';
				}
				// grab the first channel param, usually 'hotelid' or 'user_id'
				foreach ($this->channel['params'] as $firstv) {
					$hotel_id .= $firstv;
					break;
				}
			}
			?>
			<div class="btn-group pull-right">
				<button type="button" id="vcm-download-reviews" class="btn btn-large vcm-config-btn" onclick="vcmDownloadReviews();"<?php echo !empty($hotel_id) ? ' title="' . addslashes($hotel_id) . '"' : ''; ?>><i class="vboicn-cloud-download"></i> <span><?php echo JText::_('VCMREVIEWDOWNLOAD'); ?></span></button>
			</div>
			<?php
		}
		?>
		</div>
	</form>
</div>
<?php
//
?>
<div class="vcm-tabs-selector-container">
	<div class="vcm-tab-selector vcm-tab-selector-active" data-tabname="vcm-tab-reviews"><?php echo JText::_('VCMMENUREVIEWS'); ?></div>
	<div class="vcm-tab-selector" data-tabname="vcm-tab-scores"><?php echo JText::_('VCMREVGLOBSCORES'); ?></div>
</div>

<div class="vcm-tab-content vcm-tab-content-reviews" id="vcm-tab-reviews" style="display: block;">
<?php
if (count($this->rows)) {
	?>
	<form action="index.php?option=com_vikchannelmanager&task=reviews" method="post" name="adminForm" id="adminForm" class="vcm-list-form">
		<div class="table-responsive">
			<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
			<?php echo $vik->openTableHead(); ?>
				<tr>
					<th width="20">
						<?php echo $vik->getAdminToggle(count($this->rows)); ?>
					</th>
					<th class="title center" width="50"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $this->orderingDir, $this->ordering); ?></th>
					<th class="title" width="150"><?php echo JHtml::_('grid.sort', 'VCMREVIEWID', 'review_id', $this->orderingDir, $this->ordering); ?></th>
					<th class="title" width="50"><?php echo JHtml::_('grid.sort', 'VCMSMARTBALBID', 'idorder', $this->orderingDir, $this->ordering); ?></th>
					<th class="title" width="150"><?php echo JHtml::_('grid.sort', 'VCMRESLOGSDT', 'dt', $this->orderingDir, $this->ordering); ?></th>
					<th class="title" width="150"><?php echo JHtml::_('grid.sort', 'VCMPROPNAME', 'prop_name', $this->orderingDir, $this->ordering); ?></th>
					<th class="title" width="150"><?php echo JHtml::_('grid.sort', 'VCMCHANNEL', 'channel', $this->orderingDir, $this->ordering); ?></th>
					<th class="title" width="150"><?php echo JHtml::_('grid.sort', 'VCMPVIEWORDERSVBTWO', 'customer_name', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center" width="50"><?php echo JHtml::_('grid.sort', 'VCMBCAHLANGUAGE', 'lang', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center" width="75"><?php echo JHtml::_('grid.sort', 'VCMBCAHCOUNTRY', 'country', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center" width="75">&nbsp;</th>
					<th class="title center" width="50"><?php echo JHtml::_('grid.sort', 'VCMREVIEWSCORE', 'score', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center" width="50"><?php echo JText::_('VCMRESLOGSDESCR'); ?></th>
					<th class="title center" width="50"><?php echo JHtml::_('grid.sort', 'VCMTACROOMPUBLISHED', 'published', $this->orderingDir, $this->ordering); ?></th>
				</tr>
			<?php echo $vik->closeTableHead(); ?>
			<?php
			$k = 0;
			$i = 0;
			$ch_reply_rev_enabled = array(
				VikChannelManagerConfig::BOOKING,
				VikChannelManagerConfig::AIRBNBAPI,
			);
			for ($i = 0, $n = count($this->rows); $i < $n; $i++) {
				// record row
				$row = $this->rows[$i];

				// let VCM parse the review object
				$review_helper = VCMReviewHelper::getInstance($row);
				$review_data = $review_helper->parseObject();

				// get the review channel details
				$channel_logo = '';
				$channel_details = $review_helper->getChannelDetails();
				if (!empty($channel_details['logo'])) {
					$channel_logo = $channel_details['logo'];
				}

				// access additional details
				$has_reply = (int) $review_helper->hasReply();
				
				// CSS class for this score
				$score_css = '';
				foreach ($score_ranges as $lim => $ccss) {
					if ($row['score'] <= $lim) {
						// this is the appropriate CSS class to use for this score
						$score_css = $ccss;
						break;
					}
				}
				?>
				<tr class="row<?php echo $k; ?>">
					<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>"></td>
					<td class="center">
						<a class="vcm-recordid" href="JavaScript: void(0);" onclick="VBOCore.handleDisplayWidgetNotification({widget_id: 'guest_reviews'}, {review_id: '<?php echo $row['id']; ?>'});"><?php echo $row['id']; ?></a>
					</td>
					<td><?php echo intval($row['review_id']) != -1 ? $row['review_id'] : ''; ?></td>
					<td><?php echo !empty($row['idorder']) ? '<a href="index.php?option=com_vikbooking&task=editorder&cid[]='.$row['idorder'].'" target="_blank">'.$row['idorder'].'</a>' : '----'; ?></td>
					<td><?php echo $row['dt']; ?></td>
					<td><?php echo $row['prop_name']; ?></td>
					<td>
					<?php
					if (!empty($channel_logo)) {
						?>
						<img src="<?php echo $channel_logo; ?>" style="max-width: 100px;"/>
						<?php
					} elseif (!empty($row['channel'])) {
						echo $row['channel'];
					} else {
						?>
						<span class="vcm-review-website-badge"><?php echo JText::_('VCMWEBSITE'); ?></span>
						<?php
					}
					?>
					</td>
					<td><?php echo $row['customer_name']; ?></td>
					<td class="center"><?php echo $row['lang']; ?></td>
					<td class="center"><?php echo $row['country']; ?></td>
					<td class="center">
					<?php
					if ($has_reply) {
						?>
						<span class="vcm-review-reply-badge vcm-review-withreply-badge"><?php echo JText::_('VCMREVIEWHASREPLY'); ?></span>
						<?php
					} else {
						?>
						<span class="vcm-review-reply-badge" onclick="VBOCore.handleDisplayWidgetNotification({widget_id: 'guest_reviews'}, {review_id: '<?php echo $row['id']; ?>'});"><?php echo JText::_('VCMREVIEWNOREPLY'); ?></span>
						<?php
					}
					?>
					</td>
					<td class="center">
						<span class="vcm-review-score-badge <?php echo $score_css; ?>"><?php echo $row['score']; ?></span>
					</td>
					<td class="center">
						<span class="vcm-review-viewdet" onclick="VBOCore.handleDisplayWidgetNotification({widget_id: 'guest_reviews'}, {review_id: '<?php echo $row['id']; ?>'});"><i class="vboicn-eye"></i></span>
					</td>
					<td class="center">
				<?php
				if ($row['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
					// reviews from Airbnb API should not be published according to their terms of service
					?>
						<a href="javascript: void(0);" class="vcm-icn-link-toggle" title="Airbnb reviews cannot be published"><i class="<?php echo class_exists('VikBookingIcons') ? VikBookingIcons::i('ban') : 'vboicn-cross'; ?>" style="color: #D9534F;"></i></a>
					<?php
				} else {
					if ($row['published'] > 0) {
						?>
						<a href="index.php?option=com_vikchannelmanager&task=toggle_review_status&cid[]=<?php echo $row['id']; ?>" class="vcm-icn-link-toggle"><i class="<?php echo class_exists('VikBookingIcons') ? VikBookingIcons::i('check-circle') : 'vboicn-checkmark'; ?>" style="color: green;"></i></a>
						<?php
					} else {
						?>
						<a href="index.php?option=com_vikchannelmanager&task=toggle_review_status&cid[]=<?php echo $row['id']; ?>" class="vcm-icn-link-toggle"><i class="<?php echo class_exists('VikBookingIcons') ? VikBookingIcons::i('times-circle') : 'vboicn-cross'; ?>" style="color: #D9534F;"></i></a>
						<?php
					}
				}
				?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
		</div>
		<input type="hidden" name="filter_order" value="<?php echo $this->ordering; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderingDir; ?>" />
		<input type="hidden" name="option" value="com_vikchannelmanager" />
		<input type="hidden" name="task" value="reviews" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php
		foreach ($this->filters as $kf => $vf) {
			if (is_scalar($vf)) {
				?>
		<input type="hidden" name="<?php echo $kf; ?>" value="<?php echo $vf; ?>" />
				<?php
			} else {
				foreach ($vf as $subvf) {
					?>
		<input type="hidden" name="<?php echo $kf; ?>[]" value="<?php echo $subvf; ?>" />
					<?php
				}
			}
		}
		?>
		<?php echo JHTML::_( 'form.token' ); ?>
		<?php echo '<br/>'.$this->navbut; ?>
	</form>
<?php
} else {
	?>
	<p class="warn"><?php echo JText::_('VCMNOREVIEWSFOUND'); ?></p>
	<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="option" value="com_vikchannelmanager" />
		<input type="hidden" name="task" value="" />
	</form>
	<?php
}
?>
</div>

<div class="vcm-tab-content vcm-tab-content-scores" id="vcm-tab-scores" style="display: none;">
<?php
if (count($this->global_scores)) {
	foreach ($this->global_scores as $glob_score) {
		// channel logo
		$channel_logo = '';
		if (!empty($glob_score['uniquekey'])) {
			$channel_info = VikChannelManager::getChannel($glob_score['uniquekey']);
			if (count($channel_info)) {
				$channel_logo = VikChannelManager::getLogosInstance($channel_info['name'])->getLogoURL();
			}
		}
		//
		if (empty($glob_score['channel'])) {
			$tmpl_name = 'website';
		} else {
			$tmpl_name = preg_replace("/[^a-z0-9]/", '', strtolower($glob_score['channel']));
		}
		if (!is_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'default_' . $tmpl_name . '.php')) {
			$tmpl_name = 'generic';
		}
		// prepare score variables for the template
		$this->glob_score = $glob_score;
		$this->channel_logo = $channel_logo;
		echo $this->loadTemplate($tmpl_name);
	}
} else {
	?>
	<p class="warn"><?php echo JText::_('VCMREVNOGLOBSCORES'); ?></p>
	<?php
}
?>
</div>

<script type="text/javascript">
function vcmClearFilters() {
	jQuery('#vcm-filters-form').find('input, select').val('');
	jQuery('#vcm-filters-form').append('<input type=\'hidden\' name=\'limitstart\' value=\'0\' />');
	document.getElementById('vcm-filters-form').submit();
}

function vcmDownloadReviews() {
	var fromd = document.getElementById('fromdate').value;
	var confstr = '<?php echo addslashes(JText::_('VCMREVIEWDOWNLOADFROMD')); ?>';
	if (fromd.length) {
		if (!confirm(confstr.replace('%s', fromd))) {
			return;
		}
	}
	document.location.href = 'index.php?option=com_vikchannelmanager&task=reviews_download&uniquekey=<?php echo $this->channel['uniquekey']; ?>&fromd=' + fromd;
}

jQuery(function($) {
	$('#propname-filt').select2({
		placeholder: '<?php echo addslashes(JText::_('VCMREVFILTBYPROPNAME')); ?>',
		allowClear: false,
		width: 150
	});
	$('#channel-filt').select2({
		placeholder: '<?php echo addslashes(JText::_('VCMREVFILTBYCH')); ?>',
		allowClear: false,
		width: 150
	});
	$('#lang-filt').select2({
		placeholder: '<?php echo addslashes(JText::_('VCMREVFILTBYLANG')); ?>',
		allowClear: false,
		width: 150
	});
	$('#country-filt').select2({
		placeholder: '<?php echo addslashes(JText::_('VCMREVFILTBYCOUNTRY')); ?>',
		allowClear: false,
		width: 150
	});
	$('#fromdate').val('<?php echo $this->filters['fromdate'] ?>').attr('data-alt-value', '<?php echo $this->filters['fromdate'] ?>');
	$('#todate').val('<?php echo $this->filters['todate'] ?>').attr('data-alt-value', '<?php echo $this->filters['todate'] ?>');

	// tabs
	$('.vcm-tab-selector').click(function() {
		var tabname = $(this).attr('data-tabname');
		$('.vcm-tab-content').hide();
		$('#'+tabname).show();
		$('.vcm-tab-selector').removeClass('vcm-tab-selector-active');
		$(this).addClass('vcm-tab-selector-active');
	});
	//
<?php
if (!empty($revid)) {
	echo "\t" . 'VBOCore.handleDisplayWidgetNotification({widget_id: \'guest_reviews\'}, {review_id: \'' . $revid . '\'});';
}
?>
});
</script>
