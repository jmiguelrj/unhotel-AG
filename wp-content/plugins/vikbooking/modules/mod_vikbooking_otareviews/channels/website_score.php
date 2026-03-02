<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_otareviews
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2018 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */
 
// no direct access
defined('ABSPATH') or die('No script kiddies please!');

$contents_level = $params->get('contents', 'standard');

// score ranges for the CSS classes imitating Booking.com
$score_ranges = array(
	// red
	3 => 'vbo-modotareviews-one-fourth',
	// orange
	6 => 'vbo-modotareviews-two-fourth',
	// yellow
	8 => 'vbo-modotareviews-three-fourth',
	// green
	10 => 'vbo-modotareviews-four-fourth',
);

if ($contents_level == 'compact') {
	$contents_name = 'vbo-modotareviews-score-compact';
} else {
	$contents_name = 'vbo-modotareviews-score-standard';
}
?>
<div class="vbo-modotareviews-globalscore vbo-modotareviews-globalscore-website <?php echo $contents_name; ?>">
	
	<div class="vbo-modotareviews-score-info-header">
	<?php
	if ($contents_level != 'compact') {
	?>
		<div class="vbo-modotareviews-score-info vbo-modotareviews-score-info-property">
		<?php
		if (!empty($data['prop_name'])) {
			?>
			<span class="vbo-modotareviews-propname"><?php echo $data['prop_name']; ?></span>
			<?php
		}
		?>
			<span class="vbo-modotareviews-lastdate"><?php echo VikBookingOtaReviewsHelper::formatIsoDate($data['last_updated']); ?></span>
		</div>
	<?php
	}
	?>
	</div>

	<div class="vbo-modotareviews-score-info-top">
		<div class="vbo-modotareviews-score-info">
			<div class="vbo-modotareviews-score-wrap vbo-modotareviews-score-wrap-website">
				<div class="vbo-modotareviews-score-point">
					<span><?php echo $data['score']; ?></span>
				</div>
				<div class="vbo-modotareviews-score-totrev">
					<span><?php echo JText::sprintf('VBOMODREVSREVBASEDONTOT', (isset($data['content']->review_score) ? $data['content']->review_score->review_count : 0)); ?></span>
				</div>
			</div>
		</div>
	</div>
	
	<?php
	$has_multi_services = false;
	foreach ($data['content'] as $key => $v) {
		if ($key != 'review_score' && isset($v->score) && isset($v->review_count)) {
			$has_multi_services = true;
			break;
		}
	}
	if ($contents_level != 'compact' && $has_multi_services === true) {
	?>
	<div class="vbo-modotareviews-score-info-bottom">
		<div class="vbo-modotareviews-subscores">
		<?php
		foreach ($data['content'] as $category => $score) {
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
			<div class="vbo-modotareviews-subscore">
				<div class="vbo-modotareviews-subscore-inner">
					<div class="vbo-modotareviews-subscore-category"><?php echo VikBookingOtaReviewsHelper::tnReviewCategory($category); ?></div>
					<div class="vbo-modotareviews-subscore-progress-inner">
						<div class="vbo-modotareviews-subscore-progress-bar">
							<div class="vbo-modotareviews-subscore-progress <?php echo $score_css; ?>" style="width: <?php echo $cat_pcent; ?>%;"></div>
						</div>
					</div>
					<div class="vbo-modotareviews-subscore-point"><?php echo round($score->score, 1); ?></div>
				</div>
			</div>
			<?php
		}
		?>
		</div>
	</div>
	<?php
	}
	?>
</div>
