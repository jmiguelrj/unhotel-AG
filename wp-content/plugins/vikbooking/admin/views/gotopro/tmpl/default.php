<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$lic_key = $this->lic_key;
$lic_date = $this->lic_date;
$is_pro = $this->is_pro;

$nowdf = VikBooking::getDateFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator();

?>
<div class="viwppro-cnt">
	<div class="vikwp-alreadypro"><?php echo JText::_('VBOPROALREADYHAVEPRO'); ?></div>
	<div class="vikwppro-header">
		<div class="vikwppro-header-inner">
			<div class="vikwppro-header-text">
				<h2><?php echo JText::_('VBOPROREDUCEOTAFEES'); ?></h2>
				<h3><?php echo JText::_('VBOPROCOLLECTDIRECTBOOK'); ?></h3>
				<h4><?php echo JText::_('VBOPROBOOKINGENGINEPMS'); ?></h4>
				<ul>
					<li><?php VikBookingIcons::e('check'); ?> <span><?php echo JText::_('VBOPROADVONE'); ?></span></li>
					<li><?php VikBookingIcons::e('check'); ?> <span><?php echo JText::_('VBOPROADVTWO'); ?></span></li>
					<li><?php VikBookingIcons::e('check'); ?> <span><?php echo JText::_('VBOPROADVTHREE'); ?></span></li>
				</ul>
				<a href="https://vikwp.com/plugin/vikbooking?utm_source=free_version&utm_medium=vbo&utm_campaign=gotopro" id="vikwpgotoget" class="vikwp-btn-link" target="_blank"><?php VikBookingIcons::e('rocket'); ?> <?php echo JText::_('VBOGOTOPROBTN'); ?></a>
			</div>
			<div class="vikwppro-header-img">
				<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/bookings.png" alt="<?php echo JText::_('VBOPROADVONE'); ?>" />
			</div>
		</div>
	</div>
	<div class="vikwppro-advantages">
		<div class="vikwppro-advantages-item">
			<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/receive-payments.png" alt="<?php echo JText::_('VBOPROCOLLECTDIRCTBOOKTITLE'); ?>" />
			<h4><?php echo JText::_('VBOPROCOLLECTDIRCTBOOKTITLE'); ?></h4>
			<?php echo JText::_('VBOPROCOLLECTDIRCTBOOKTITLEDESC'); ?>
		</div>
		<div class="vikwppro-advantages-item">
			<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/save-on-ota.png" alt="<?php echo JText::_('VBOPROSAVEOTASFEESTITLE'); ?>" />
			<h4><?php echo JText::_('VBOPROSAVEOTASFEESTITLE'); ?></h4>
			<?php echo JText::_('VBOPROSAVEOTASFEESTITLEDESC'); ?>
		</div>
		<div class="vikwppro-advantages-item">
			<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/rocket.png" alt="<?php echo JText::_('VBOPROBECAMEINDEPENDENTTITLE'); ?>" />
			<h4><?php echo JText::_('VBOPROBECAMEINDEPENDENTTITLE'); ?></h4>
			<?php echo JText::_('VBOPROBECAMEINDEPENDENTTITLEDESC'); ?>
		</div>
	</div>
	<div class="viwppro-feats-cnt">
		
		<div class="viwppro-feats-row vikwppro-even viwppro-row-heightsmall">
			<div class="viwppro-feats-text">
				<h4><?php echo JText::_('VBOPROWHYRATES'); ?></h4>
				<p><?php echo JText::_('VBOPROWHYRATESDESC'); ?></p>
			</div>
			<div class="viwppro-feats-img">
				<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/rates_overview.jpg" alt="<?php echo JText::_('VBOPROWHYRATES'); ?>" />
			</div>
		</div>

		<div class="viwppro-feats-row">
			<div class="viwppro-feats-text">
				<h4><?php echo JText::_('VBOPROWHYCRONJOB'); ?></h4>
				<p><?php echo JText::_('VBOPROWHYCRONJOBDESC'); ?></p>
			</div>
			<div class="viwppro-feats-img">
				<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/vik-booking-automatize-email-tasks.webp" alt="<?php echo JText::_('VBOPROWHYCRONJOB'); ?>" />
			</div>
		</div>

		<div class="viwppro-feats-row">
			<div class="viwppro-feats-text">
				<h4><?php echo JText::_('VBOPROWHYEXTRASERVICES'); ?></h4>
				<p><?php echo JText::_('VBOPROWHYEXTRASERVICESDESC'); ?></p>
			</div>
			<div class="viwppro-feats-img">
				<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/vbo-extra-services.jpg" alt="<?php echo JText::_('VBOPROWHYEXTRASERVICES'); ?>" />
			</div>
		</div>

		<div class="viwppro-feats-row vikwppro-even">
			<div class="viwppro-feats-text">
				<h4><?php echo JText::_('VBOPROWHYCHMANAGER'); ?></h4>
				<p><?php echo JText::_('VBOPROWHYCHMANAGERDESC'); ?></p>
			</div>
			<div class="viwppro-feats-img">
				<img style="max-width: 400px;" src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/direct-bookings-best-prices.png" alt="<?php echo JText::_('VBOPROWHYCHMANAGER'); ?>" />
			</div>
		</div>
	</div>
	<div class="viwppro-extra">
		<h3><?php echo JText::_('VBOPROWHYUNLOCKF'); ?></h3>
		<div class="viwppro-extra-inner">
			<div class="viwppro-extra-item">
				<div class="viwppro-extra-item-inner">
					<div class="viwppro-extra-item-text">
						<?php VikBookingIcons::e('calendar-check'); ?>
						<h4><?php echo JText::_('VBOPROWHYCHECKIN'); ?></h4>
						<p><?php echo JText::_('VBOPROWHYCHECKINDESC'); ?></p>
					</div>
				</div>
			</div>
			<div class="viwppro-extra-item">
				<div class="viwppro-extra-item-inner">
					<div class="viwppro-extra-item-text">
						<?php VikBookingIcons::e('chart-line'); ?>
						<h4><?php echo JText::_('VBOPROWHYPMSREP'); ?></h4>
						<p><?php echo JText::_('VBOPROWHYPMSREPDESC'); ?></p>
					</div>
				</div>
			</div>
			<div class="viwppro-extra-item">
				<div class="viwppro-extra-item-inner">
					<div class="viwppro-extra-item-text">
						<?php VikBookingIcons::e('certificate'); ?>
						<h4><?php echo JText::_('VBOPROWHYPROMOTIONS'); ?></h4>
						<p><?php echo JText::_('VBOPROWHYPROMOTIONSDESC'); ?></p>
					</div>
				</div>
			</div>
			<div class="viwppro-extra-item">
				<div class="viwppro-extra-item-inner">
					<div class="viwppro-extra-item-text">
						<?php VikBookingIcons::e('file-text'); ?>
						<h4><?php echo JText::_('VBOPROWHYINVOICES'); ?></h4>
						<p><?php echo JText::_('VBOPROWHYINVOICESDESC'); ?></p>
					</div>
				</div>
			</div>
			<div class="viwppro-extra-item">
				<div class="viwppro-extra-item-inner">
					<div class="viwppro-extra-item-text">
						<?php VikBookingIcons::e('users'); ?>
						<h4><?php echo JText::_('VBOPROWHYCUSTOMERS'); ?></h4>
						<p><?php echo JText::_('VBOPROWHYCUSTOMERSDESC'); ?></p>
					</div>
				</div>
			</div>
			<div class="viwppro-extra-item">
				<div class="viwppro-extra-item-inner">
					<div class="viwppro-extra-item-text">
						<?php VikBookingIcons::e('pie-chart'); ?>
						<h4><?php echo JText::_('VBOPROWHYGRAPHS'); ?></h4>
						<p><?php echo JText::_('VBOPROWHYGRAPHSDESC'); ?></p>
					</div>
				</div>
			</div>
			<div class="viwppro-extra-item">
				<div class="viwppro-extra-item-inner">
					<div class="viwppro-extra-item-text">
						<?php VikBookingIcons::e('sms'); ?>
						<h4><?php echo JText::_('VBOPROWHYCRONS'); ?></h4>
						<p><?php echo JText::_('VBOPROWHYCRONSDESC'); ?></p>
					</div>
				</div>
			</div>
			<div class="viwppro-extra-item">
				<div class="viwppro-extra-item-inner">
					<div class="viwppro-extra-item-text">
						<?php VikBookingIcons::e('credit-card'); ?>
						<h4><?php echo JText::_('VBOPROWHYPAYMENTS'); ?></h4>
						<p><?php echo JText::_('VBOPROWHYPAYMENTSDESC'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<div class="vikwp-extra-more"><?php echo JText::_('VBOPROWHYMOREEXTRA'); ?></div>
		<a name="upgrade"></a>
	</div>
	<div class="vikwppro-licencecnt">
		<div class="col col-md-6 col-sm-12 vikwppro-licencetext">
			<div>
				<h3><?php echo JText::_('VBOPROREADYTOINCREASE'); ?></h3>
			<?php
			if ($lic_date > 0) {
				$valid_until = date(str_replace("/", $datesep, $df), $lic_date);
				?>
				<h4 class="vikwppro-lickey-expired"><?php echo JText::sprintf('VBOLICKEYEXPIREDON', $valid_until); ?></h4>
				<?php
			}
			?>
				<h4 class="vikwppro-licencecnt-get"><?php echo JText::_('VBOPROGETNEWLICFROM'); ?></h4>
				<a href="https://vikwp.com/plugin/vikbooking?utm_source=free_version&utm_medium=vbo&utm_campaign=gotopro" class="vikwp-btn-link" target="_blank"><?php VikBookingIcons::e('rocket'); ?> <?php echo JText::_('VBOGOTOPROBTN'); ?></a>
			</div>
			<span class="icon-background"><?php VikBookingIcons::e('rocket'); ?></span>
		</div>
		<div class="col col-md-6 col-sm-12 vikwppro-licenceform">
			<form>
				<div class="vikwppro-licenceform-inner">
					<h4><?php echo JText::_('VBOPROALREADYHAVEKEY'); ?></h4>
					<span class="vikwppro-inputspan"><?php VikBookingIcons::e('key'); ?><input type="text" name="key" id="lickey" value="<?php echo htmlspecialchars($lic_key); ?>" class="licence-input" autocomplete="off" /></span>
					<button type="button" class="btn vikwp-btn-green" id="vikwpvalidate" onclick="vikWpValidateLicenseKey();"><?php echo JText::_('VBOPROVALNINST'); ?></button>
				</div>
			</form>
		</div>
	</div>

	<div class="viwpro-e4jc">
		<div class="viwpro-e4jc-inner">
			<div class="viwpro-e4jc-text">
				<h3 style="margin-top: 0;"><?php echo JText::_('VBOPROVCMADTITLE'); ?></h3>
				<h4><?php echo JText::_('VBOPROSYNCHNEWBOOKINGS'); ?></h4>
				<p><?php echo JText::_('VBOPROVCMSYNCHEVERYTHING'); ?></p>
				<p><?php echo JText::_('VBOPROVCMADDESCR'); ?></p>
				<a href="https://vikwp.com/plugin/vikchannelmanager?utm_source=free_version&utm_medium=vbo&utm_campaign=gotopro" class="vikwp-pro-discover btn btn-primary" target="_blank"><?php echo JText::_('VBOPROVCMADMOREINFO'); ?></a>
				<div class="viwpro-e4jc-trial-content">
					<p class="viwpro-e4jc-trial-title"><?php echo JText::_('VBOPROVCTRIALTITLE'); ?></p>
					<p><?php echo JText::_('VBOPROVCTRIALQUESTION'); ?></p>
					<a href="https://e4jconnect.com/free-channel-manager-trial?utm_source=free_version&utm_medium=vbo&utm_campaign=gotopro" class="vikwp-pro-trial btn btn-green" target="_blank"><?php echo JText::_('VBOPROVCMTRIAL'); ?></a>
				</div>
			</div>
			<div class="viwpro-e4jc-img"><img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/vcm-synch-img.webp" title="e4jConnect Vik Channel Manager" /></div>
		</div>
		<div class="viwpro-e4jc-channels">
			<div class="viwpro-e4jc-channels-intro"><h3><?php echo JText::_('VBOPROVCMADSOMECHAV'); ?></h3></div>
			<div class="viwpro-e4jc-channels-inner">
				<p class="vikpro-e4jc-badgeimg">
					<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/bookingcom-premier-badge.png" title="e4jConnect Premier Partner" /> 
					<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/airbnb-preferred-badge.png" title="Airbnb e4jConnect Preferred Partner" /> 
					<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/expedia-partner.png" title="e4jConnect Expedia Partner" /> 
					<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/google-partner.png" title="e4jConnect Google Partner" /> 
					<img src="<?php echo VBO_ADMIN_URI; ?>resources/images/pro/vrbo-partner-badge.png" title="e4jConnect Vrbo Partner" />
				</p>				
			</div>
			<div class="viwpro-e4jc-channels-more"><?php echo JText::_('VBOPROVCMADCHANDMANY'); ?></div>
		</div>
	</div>

</div>

<script type="text/javascript">
var vikwp_running = false;

function vikWpValidateLicenseKey() {
	if (vikwp_running) {
		// prevent double submission until request is over
		return;
	}

	// start running
	vikWpStartValidation();

	// request
	VBOCore.doAjax(
		"<?php echo VikBooking::ajaxUrl('admin.php?option=com_vikbooking&task=license.validate'); ?>",
		{
			key: document.getElementById('lickey').value
		},
		(res) => {
			try {
				var obj_res = typeof res === 'string' ? JSON.parse(res) : res;
				document.location.href = 'admin.php?option=com_vikbooking&view=getpro';
			} catch(err) {
				console.error(err);
				// stop the request
				vikWpStopValidation();
				// display error
				alert(err.responseText || 'Request Failed');
			}
		},
		(err) => {
			console.error(err);
			// stop the request
			vikWpStopValidation();
			// display error
			alert(err.responseText || 'Request Failed');
		}
	);
}

function vikWpStartValidation() {
	vikwp_running = true;
	jQuery('#vikwpvalidate').prepend('<?php VikBookingIcons::e('refresh', 'fa-spin'); ?>');
}

function vikWpStopValidation() {
	vikwp_running = false;
	jQuery('#vikwpvalidate').find('i').remove();
}

jQuery(function() {
	jQuery('.vikwp-alreadypro a').click(function(e) {
		e.preventDefault();
		jQuery('html,body').animate({ scrollTop: (jQuery('.vikwppro-licencecnt').offset().top - 50) }, { duration: 'fast' });
	});
	jQuery('#lickey').keyup(function() {
		jQuery(this).val(jQuery(this).val().trim());
	});
	jQuery('#lickey').keypress(function(e) {
		if (e.which == 13) {
			// enter key code pressed, run the validation
			vikWpValidateLicenseKey();
			return false;
		}
	});
});
</script>
