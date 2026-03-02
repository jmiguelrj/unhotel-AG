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

$is_rev_per_service = false;
foreach ($data['content']->scoring as $extraserv => $score) {
	if ($extraserv != 'review_score') {
		$is_rev_per_service = true;
		break;
	}
}

?>
<div class="vbo-modotareviews-review vbo-modotareviews-review-website">
	<div class="vbo-modotareviews-review-inner-top">
	<?php
	if ($is_rev_per_service === true) {
		?>
		<div class="vbo-modotareviews-review-score">
			<span><?php echo $data['score']; ?></span>
		</div>
		<?php
	} else {
		// star rating
		$stars_count = floor(($data['content']->scoring->review_score / 2));
		?>
		<div class="vbo-modotareviews-review-stars">
		<?php
		for ($i = 1; $i <= $stars_count; $i++) { 
			?>
			<i class="<?php echo VikBookingOtaReviewsHelper::icon('star'); ?> vbo-modotareviews-star-full"></i>
			<?php
		}
		for ($i = ($stars_count + 1); $i <= 5; $i++) { 
			?>
			<i class="<?php echo VikBookingOtaReviewsHelper::icon('star'); ?>"></i>
			<?php
		}
		?>
		</div>
		<?php
	}
	?>
	</div>
	<div class="vbo-modotareviews-review-inner-middle">
	<?php
	// review contents
	if (isset($data['content']->content) && !empty($data['content']->content->message)) {
		?>
		<div class="vbo-modotareviews-review-inner-messages">
			<div class="vbo-modotareviews-review-headline">
				<span><?php echo $data['content']->content->message; ?></span>
			</div>
		</div>
		<?php
	}
	?>
	</div>
	<div class="vbo-modotareviews-review-inner-bottom">
		<?php
		// reviewer name
		if (isset($data['content']->reviewer) && !empty($data['content']->reviewer->name)) {
		?>
		<div class="vbo-modotareviews-review-reviewer">
			<span><?php echo $data['content']->reviewer->name; ?></span>
		</div>
		<?php
		}
		?>
		<div class="vbo-modotareviews-review-date">
			<span><?php echo VikBookingOtaReviewsHelper::formatIsoDate((isset($data['dt']) ? $data['dt'] : $data['content']->created_timestamp)); ?></span>
		</div>
	</div>
</div>
