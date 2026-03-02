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

?>

<div class="vcm-pcid-header">
	<h3><?php echo JText::_('VCMPCIDRESPONSETITLE'); ?></h3>
</div>

<div class="vcm-pcid-body">

	<div class="vcm-pcid-body-block" id="vcm-pcid-body-left">
		<pre><?php foreach ($this->creditCardResponse as $key => $val) {
				echo sprintf(
					"%s: %s\n",
					ucwords(str_replace('_', ' ', $key)),
					(is_scalar($val) ? (string) $val : print_r($val, true))
				);
		} ?></pre>
	</div>

	<div class="vcm-pcid-body-block off" id="vcm-pcid-body-right">
		<pre><?php echo htmlentities(urldecode((!empty($this->order['paymentlog']) ? $this->order['paymentlog'] : ''))); ?></pre>
	</div>

</div>

<script type="text/javascript">

	jQuery(function() {

		// handle card splitted details show in a PCI manner
		jQuery('.vcm-pcid-body-block').hover(function() {
			if (jQuery(this).hasClass('off')) {
				// hide all elements
				jQuery('.vcm-pcid-body-block').addClass('off');
				// show the hovered element
				jQuery(this).removeClass('off');
			}
		}, function() {
			// do nothing on exit
		});

	});

</script>

<?php
/**
 * Attempt to display the reporting invalid credit card layout.
 * 
 * @since  1.8.27
 */
try {
	// prepare the layout data array
	$layout_data = [
		'caller'  => 'vikchannelmanager',
		'booking' => $this->order,
	];

	?>
	<div class="vcm-execpcid-reporting-invalidcc">
	<?php
	// render the reporting-invalidcc layout
	echo JLayoutHelper::render('reporting.invalidcc', $layout_data, null, [
		'component' => 'com_vikchannelmanager',
		'client' 	=> 'admin',
	]);
	?>
	</div>
	<?php
} catch(Throwable $e) {
	// do nothing
}
