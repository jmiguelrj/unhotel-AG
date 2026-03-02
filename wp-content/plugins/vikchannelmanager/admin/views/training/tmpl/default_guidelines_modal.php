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

$guidelines = [
	[
		'title' => 'Check-in Instructions',
		'content' => JLayoutHelper::render('ai.training.guidelines.checkin'),
	],
	[
		'title' => 'Essentials',
		'content' => JLayoutHelper::render('ai.training.guidelines.essentials'),
	],
	[
		'title' => 'Meals',
		'content' => JLayoutHelper::render('ai.training.guidelines.meals'),
	],
	[
		'title' => 'Parking Availability',
		'content' => JLayoutHelper::render('ai.training.guidelines.parking'),
	],
	[
		'title' => 'Property Location',
		'content' => JLayoutHelper::render('ai.training.guidelines.location'),
	],
	[
		'title' => 'Wi-Fi Password',
		'content' => JLayoutHelper::render('ai.training.guidelines.wifi'),
	],
];

?>

<div style="display: none;" id="guidelines-modal-wrapper">
	<div id="guidelines-modal">

		<div class="guidelines-accordion">
			<?php foreach ($guidelines as $guideline): ?>		
				<div class="guideline-card">
					<div class="guideline-card-header">
						<div class="guideline-card-accordion"><?php echo $guideline['title']; ?></div>
					</div>
					<div class="guideline-card-body">
						<?php echo $guideline['content']; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</div>

<script>
	(function($) {
		'use strict';

		$(function() {
			$('.guideline-card-accordion').on('click', function() {
				$(this).parent().toggleClass('active').next().slideToggle();
			});
		});
	})(jQuery);
</script>