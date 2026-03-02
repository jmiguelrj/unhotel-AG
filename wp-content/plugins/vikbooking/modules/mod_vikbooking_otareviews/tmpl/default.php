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

$document = JFactory::getDocument();
$document->addStyleSheet($baseurl.'modules/mod_vikbooking_otareviews/mod_vikbooking_otareviews.css');

?>
<div class="vbo-modotareviews-wrap <?php echo $revorscore < 2 ? 'vbo-modotareviews-reviews-wrap' : 'vbo-modotareviews-globscore-wrap'; ?>">
<?php
if (!empty($introtxt)) {
	?>
	<div class="vbo-modotareviews-introtxt"><?php echo $introtxt; ?></div>
	<?php
}
foreach ($revsdata as $review) {
	?>
	<div class="vbo-modotareviews-elem-container <?php echo $revorscore < 2 ? 'vbo-modotareviews-review-container' : 'vbo-modotareviews-globscore-container'; ?>">
		<?php echo $revorscore < 2 ? VikBookingOtaReviewsHelper::parseReview($review, $params) : VikBookingOtaReviewsHelper::parseGlobalScore($review, $params); ?>
	</div>
	<?php
}
?>
</div>
