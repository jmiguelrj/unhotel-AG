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
	<div class="vbo-modotareviews-review-inner-bottom">
	<?php
	// reviewer name
	if (!empty($data['customer_name'])) {
		?>
		<div class="vbo-modotareviews-review-reviewer">
			<span><?php echo $data['customer_name']; ?></span>
		</div>
		<?php
	}
	?>
		<div class="vbo-modotareviews-review-date">
			<span><?php echo VikBookingOtaReviewsHelper::formatIsoDate((isset($data['dt']) ? $data['dt'] : $data['last_updated'])); ?></span>
		</div>
	</div>
</div>
