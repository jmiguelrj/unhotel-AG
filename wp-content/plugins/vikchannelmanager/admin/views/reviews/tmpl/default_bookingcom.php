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

$score_content = json_decode($this->glob_score['content']);
$score_content = is_object($score_content) ? $score_content : new stdClass;

// score ranges for the CSS classes imitating Booking.com
$score_ranges = array(
	// red
	3 => 'vcm-scorerange-one-fourth',
	// orange
	6 => 'vcm-scorerange-two-fourth',
	// yellow
	8 => 'vcm-scorerange-three-fourth',
	// green
	10 => 'vcm-scorerange-four-fourth',
);

?>
<div class="vcm-revscore-container">
	
	<div class="vcm-revscore-info-top">
		<div class="vcm-revscore-info vcm-revscore-info-property">
			<span class="vcm-revscore-propname"><?php echo $this->glob_score['prop_name']; ?></span>
			<span class="vcm-revscore-lastdate"><?php echo $this->glob_score['last_updated']; ?></span>
		</div>
		<div class="vcm-revscore-info">
			<div class="vcm-revscore-logo">
			<?php
			if (!empty($this->channel_logo)) {
				?>
				<img src="<?php echo $this->channel_logo; ?>" style="max-width: 100px;"/>
				<?php
			} elseif (!empty($this->glob_score['channel'])) {
				echo '<span>' . $this->glob_score['channel'] . '</span>';
			} else {
				echo '<span>' . JText::_('VCMCOMPONIBE') . '</span>';
			}
			?>
			</div>
		</div>
		<div class="vcm-revscore-info">
			<div class="vcm-revscore-score-wrap">
				<div class="vcm-revscore-score-point vcm-revscore-score-point-<?php echo preg_replace("/[^a-z0-9]/", '', strtolower($this->glob_score['channel'])); ?>">
					<span><?php echo $this->glob_score['score']; ?></span>
				</div>
				<div class="vcm-revscore-score-totrev">
					<span><?php echo JText::sprintf('VCMREVBASEDONTOT', (isset($score_content->review_score) ? $score_content->review_score->review_count : 0)); ?></span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="vcm-revscore-info-bottom">
		<div class="vcm-revscore-subscores">
	<?php
	if (isset($score_content->review_score)) {
		// at least the property review_score must be set, but we will exclude it as it's used above
		foreach ($score_content as $category => $score) {
			if ($category == 'review_score') {
				continue;
			}
			// find the appropriate CSS class for this score
			$cat_score = (float)$score->score;
			// maximum value is 10 and we need a percentage value for the DIV width
			$cat_pcent = round(($cat_score * 10), 0);
			$cat_pcent = $cat_pcent > 100 ? 100 : $cat_pcent;
			// CSS class for this score
			$score_css = '';
			foreach ($score_ranges as $lim => $ccss) {
				if ($cat_score <= $lim) {
					// this is the appropriate CSS class to use for this score
					$score_css = $ccss;
					break;
				}
			}
			?>
			<div class="vcm-revscore-subscore">
				<div class="vcm-revscore-subscore-inner">
					<div class="vcm-revscore-subscore-category"><?php echo ucwords(str_replace('_', ' ', $category)); ?></div>
					<div class="vcm-revscore-subscore-point"><?php echo round($score->score, 1); ?></div>
				</div>
				<div class="vcm-revscore-subscore-progress-inner">
					<div class="vcm-revscore-subscore-progress <?php echo $score_css; ?>" style="width: <?php echo $cat_pcent; ?>%;"></div>
				</div>
			</div>
			<?php
		}
	}
	?>
		</div>
	</div>
</div>
