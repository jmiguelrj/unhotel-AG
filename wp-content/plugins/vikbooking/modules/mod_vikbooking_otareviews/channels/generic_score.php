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
<div class="vbo-modotareviews-globalscore vbo-modotareviews-globalscore-<?php echo $safe_chname; ?>">
	<div class="vbo-modotareviews-score-info vbo-modotareviews-score-info-property">
		<span class="vbo-modotareviews-score-propname"><?php echo $data['prop_name']; ?></span>
		<span class="vbo-modotareviews-score-lastdate"><?php echo $data['last_updated']; ?></span>
	</div>
	<?php
	// print the logo of the channel if VCM is available
	$channel_logo = VikBookingOtaReviewsHelper::getChannelLogo($data['uniquekey']);
	if (!empty($channel_logo)) {
		?>
		<div class="vbo-modotareviews-score-info">
			<div class="vbo-modotareviews-logo">
				<img src="<?php echo $channel_logo; ?>"/>
			</div>
		</div>
	<?php
	}
	?>
	<div class="vbo-modotareviews-score-info">
		<div class="vbo-modotareviews-score-score-wrap">
			<div class="vbo-modotareviews-score-score-point vbo-modotareviews-score-score-point-<?php echo $safe_chname; ?>">
				<span><?php echo $data['score']; ?></span>
			</div>
		</div>
	</div>
</div>
