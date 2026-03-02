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

$safe_chname = preg_replace("/[^a-z0-9]/", '', strtolower($data['channel']));

?>
<div class="vbo-modotareviews-review vbo-modotareviews-review-<?php echo $safe_chname; ?>">
	<div class="vbo-modotareviews-review-inner-top">
		<div class="vbo-modotareviews-review-score">
			<span><?php echo $data['score']; ?></span>
		</div>
	</div>
	<div class="vbo-modotareviews-review-inner-middle">
	<?php
	// review contents
	if (isset($data['content']->content) && (!empty($data['content']->content->headline) || !empty($data['content']->content->positive) || !empty($data['content']->content->negative))) {
		?>
		<div class="vbo-modotareviews-review-inner-messages">
		<?php
		if (!empty($data['content']->content->headline)) {
			?>
			<div class="vbo-modotareviews-review-headline">
				<span><?php echo $data['content']->content->headline; ?></span>
			</div>
			<?php
		}
		if (!empty($data['content']->content->positive)) {
			?>
			<div class="vbo-modotareviews-review-positive">
				<i class="<?php echo VikBookingOtaReviewsHelper::icon('far fa-smile'); ?>"></i>
				<span><?php echo $data['content']->content->positive; ?></span>
			</div>
			<?php
		}
		if (!empty($data['content']->content->negative)) {
			?>
			<div class="vbo-modotareviews-review-negative">
				<i class="<?php echo VikBookingOtaReviewsHelper::icon('far fa-frown'); ?>"></i>
				<span><?php echo $data['content']->content->negative; ?></span>
			</div>
			<?php
		}
		?>
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
			<span><?php echo VikBookingOtaReviewsHelper::formatIsoDate((isset($data['dt']) ? $data['dt'] : $data['last_updated'])); ?></span>
		</div>
		<?php 
		// print the logo of the channel if VCM is available
		$channel_logo = VikBookingOtaReviewsHelper::getChannelLogo($data['uniquekey']);
		if (!empty($channel_logo)) {
		?>
		<div class="vbo-modotareviews-review-chlogo">
			<img src="<?php echo $channel_logo; ?>"/>
		</div>
		<?php
		}
		?>
	</div>
</div>
