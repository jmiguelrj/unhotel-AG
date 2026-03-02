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

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();

$nowdf = VikBooking::getDateFormat();
if ($nowdf == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator();

?>
<script type="text/Javascript">
function vboGetRandomCode(len) {
	var codechars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	var code = '';
	for (var i = 0; i < len; i++) {
		code += codechars.charAt(Math.floor(Math.random() * codechars.length));
	}
	return code;
}

function vboGenerateCode() {
	var code = vboGetRandomCode(10);
	document.getElementById('inpcode').value = code;
}

function vboMngOperAddDaysException() {
	let from = document.querySelector('#wdexceptfrom');
	let to = document.querySelector('#wdexceptto');
	let hours = document.querySelector('#wdexcepthours');
	let interval = [];

	if (!from || !from.value) {
		return false;
	}

	interval.push(from.value);

	if (to && to.value && new Date(from.value) < new Date(to.value)) {
		interval.push(to.value);
	} else {
		interval.push(from.value);
	}

	let hidden_inp = document.createElement('input');
	hidden_inp.setAttribute('type', 'hidden');
	hidden_inp.setAttribute('name', 'work_days_exceptions[]');
	hidden_inp.value = JSON.stringify({
		from: interval[0],
		to: interval[1],
		hours: parseInt(hours.value),
	});

	let interval_elem = document.createElement('div');
	interval_elem.classList.add('vbo-closed-date-entry');
	if (hours.value == 0) {
		interval_elem.classList.add('vbo-closed-date-entry-dayoff');
	}
	interval_elem.setAttribute('data-date-period', interval.join('::'));

	let interval_from = document.createElement('span');
	interval_from.innerText = interval[0];
	interval_elem.append(interval_from);

	let interval_sep = document.createElement('span');
	interval_sep.innerText = ' - ';

	if (interval[0] != interval[1]) {
		interval_elem.append(interval_sep);

		let interval_to = document.createElement('span');
		interval_to.innerText = interval[1];
		interval_elem.append(interval_to);
	}

	let interval_sep_clone = interval_sep.cloneNode(true);
	interval_elem.append(interval_sep_clone);

	let interval_hours = document.createElement('span');
	interval_hours.innerText = hours.value > 0 ? hours.value + ' ' + (hours.value > 1 ? <?php echo json_encode(JText::_('VBCONFIGONETENEIGHT')); ?> : <?php echo json_encode(JText::_('VBO_HOUR')); ?>) : <?php echo json_encode(JText::_('VBO_DAY_OFF')); ?>;
	interval_elem.append(interval_hours);

	let interval_delete = document.createElement('span');
	interval_delete.classList.add('vbo-closed-date-rm');
	interval_delete.innerHTML = '<?php VikBookingIcons::e('times'); ?>';
	interval_delete.addEventListener('click', () => {
		vboMngOperRemoveDaysException(interval.join('::'));
	});
	interval_elem.append(interval_delete);

	let container = document.querySelector('#vbo-mngoper-work-days-exceptions');

	container.append(hidden_inp);
	container.append(interval_elem);

	from.value = '';
	to.value = '';
	hours.value = 0;
}

function vboMngOperRemoveDaysException(interval) {
	document.querySelectorAll('.vbo-closed-date-entry[data-date-period="' + interval + '"]').forEach((container) => {
		let hidden_inp = container.previousElementSibling;
		if (hidden_inp && hidden_inp.matches('input[name="work_days_exceptions[]"]')) {
			hidden_inp.remove();
		}
		container.remove();
	});
}

jQuery(function() {
	jQuery('.vbo-trig-upd-pic').click(function() {
		jQuery('#picimg').click();
	});

	jQuery('#picimg').on('change', function(e) {
		var fname = jQuery(this).val();
		if (fname && fname.length) {
			var only_name = fname.split(/[\\/]/).pop();
			jQuery('.vbo-trig-upd-pic').find('span').text(only_name);
		}
	});

	// zoom-able avatars
	jQuery('.vbo-customer-info-box-avatar').each(function() {
		var img = jQuery(this).find('img');
		if (!img.length) {
			return;
		}
		// register click listener
		img.on('click', function(e) {
			// stop events propagation
			e.preventDefault();
			e.stopPropagation();

			// check for caption
			var caption = jQuery(this).attr('data-caption');

			// build modal content
			var zoom_modal = jQuery('<div></div>').addClass('vbo-modal-overlay-block vbo-modal-overlay-zoom-image').css('display', 'block');
			var zoom_dismiss = jQuery('<a></a>').addClass('vbo-modal-overlay-close');
			zoom_dismiss.on('click', function() {
				jQuery('.vbo-modal-overlay-zoom-image').fadeOut();
			});
			zoom_modal.append(zoom_dismiss);
			var zoom_content = jQuery('<div></div>').addClass('vbo-modal-overlay-content vbo-modal-overlay-content-zoom-image');
			var zoom_head = jQuery('<div></div>').addClass('vbo-modal-overlay-content-head');
			var zoom_head_title = jQuery('<span></span>');
			if (caption) {
				zoom_head_title.text(caption);
			}
			var zoom_head_close = jQuery('<span></span>').addClass('vbo-modal-overlay-close-times').html('&times;');
			zoom_head_close.on('click', function() {
				jQuery('.vbo-modal-overlay-zoom-image').fadeOut();
			});
			zoom_head.append(zoom_head_title).append(zoom_head_close);
			var zoom_body = jQuery('<div></div>').addClass('vbo-modal-overlay-content-body vbo-modal-overlay-content-body-scroll');
			var zoom_image = jQuery('<div></div>').addClass('vbo-modal-zoom-image-wrap');
			zoom_image.append(jQuery(this).clone());
			zoom_body.append(zoom_image);
			zoom_content.append(zoom_head).append(zoom_body);
			zoom_modal.append(zoom_content);
			// append modal to body
			if (jQuery('.vbo-modal-overlay-zoom-image').length) {
				jQuery('.vbo-modal-overlay-zoom-image').remove();
			}
			jQuery('body').append(zoom_modal);
		});
	});
<?php
if (!$this->operator) {
	// trigger the generation of an authentication code upon creating a new operator
	?>
	vboGenerateCode();
	<?php
}
?>
});

</script>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDDETAILS'); ?></legend>
					<div class="vbo-params-container">
					<?php
					if ($this->operator && !empty($this->operator['pic'])) {
						$avatar_caption = ltrim($this->operator['first_name'] . ' ' . $this->operator['last_name']);
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label">
								<div class="vbo-customer-info-box">
									<div class="vbo-customer-info-box-avatar vbo-customer-avatar-medium">
										<span>
											<img src="<?php echo strpos($this->operator['pic'], 'http') === 0 ? $this->operator['pic'] : VBO_SITE_URI . 'resources/uploads/' . $this->operator['pic']; ?>" data-caption="<?php echo htmlspecialchars($avatar_caption); ?>" />
										</span>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERFIRSTNAME'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting"><input type="text" name="first_name" value="<?php echo $this->operator ? $this->operator['first_name'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERLASTNAME'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting"><input type="text" name="last_name" value="<?php echo $this->operator ? $this->operator['last_name'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMEREMAIL'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting"><input type="text" name="email" value="<?php echo $this->operator ? $this->operator['email'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERPHONE'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="phone" value="<?php echo $this->operator ? $this->operator['phone'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOCODEOPERATOR'); ?> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBOCODEOPERATOR'), 'content' => JText::_('VBOCODEOPERATORSHELP'))); ?></div>
							<div class="vbo-param-setting"><input type="text" name="code" id="inpcode" value="<?php echo $this->operator ? $this->operator['code'] : ''; ?>" size="16" placeholder="ABCDE12345" /> &nbsp;&nbsp; <button type="button" class="btn" onclick="vboGenerateCode();" style="vertical-align: top;"><?php echo JText::_('VBOCODEOPERATORGEN'); ?></button></div>
						</div>
					<?php
					if (VBOPlatformDetection::isWordPress()) {
						?>
						<!-- @wponly the user label is called statically 'Website User' -->
						<div class="vbo-param-container">
							<div class="vbo-param-label">Website User</div>
							<div class="vbo-param-setting"><?php echo JHtml::_('list.users', 'ujid', ($this->operator ? $this->operator['ujid'] : ''), 1); ?></div>
						</div>
						<?php
					} else {
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label">Joomla User</div>
							<div class="vbo-param-setting">
								<?php
								// JHtmlList::users(string $name, string $active, integer $nouser, string $javascript = null, string $order = 'name')
								if (!class_exists('JHtmlList')) {
									jimport( 'joomla.html.html.list' );
								}
								echo JHtmlList::users('ujid', ($this->operator ? $this->operator['ujid'] : ''), 1);
								?>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_CUSTOMER_PROF_PIC'); ?></div>
							<div class="vbo-param-setting">
								<div class="input-append">
									<input type="text" name="pic" value="<?php echo $this->operator ? $this->operator['pic'] : ''; ?>" size="30"/>
									<button type="button" class="btn btn-primary vbo-trig-upd-pic"><?php VikBookingIcons::e('upload'); ?><span></span></button>
								</div>
							<?php
							if ($this->operator && !empty($this->operator['pic'])) {
								?>
								<div class="vbo-cur-idscan">
									<i class="vboicn-eye"></i><a href="<?php echo strpos($this->operator['pic'], 'http') === 0 ? $this->operator['pic'] : VBO_SITE_URI . 'resources/uploads/' . $this->operator['pic']; ?>" target="_blank"><?php echo $this->operator['pic']; ?></a>
								</div>
								<?php
							}
							?>
								<input type="file" name="picimg" id="picimg" style="display: none;" />
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_OPER_PROF_PIC_HELP'); ?></span>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="vbo-config-maintab-right">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOOPERATORPERMS'); ?></legend>
					<div class="vbo-params-container">
					<?php
					if ($this->operator) {
						// handle the operator permissions
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-setting">
								<a href="javascript: void(0);" onclick="VBOCore.emitEvent('vbo-tool-permissions-modal-toggle');" class="vbo-perms-operators"><i class="vboicn-user-plus icn-nomargin"></i> <span><?php echo JText::_('VBO_MANAGE'); ?></span></a>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</fieldset>
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBO_WORK_DAYS'); ?></legend>
					<div class="vbo-params-legend-comment"><?php echo JText::_('VBO_WORK_DAYS_WEEK_HELP'); ?></div>
					<div class="vbo-params-container">

					<?php
					$work_days_list = [1, 2, 3, 4, 5, 6, 0];
					foreach ($work_days_list as $w) {
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo VikBooking::sayWeekDay($w); ?></div>
							<div class="vbo-param-setting">
								<select name="work_days_week[<?php echo $w; ?>]" id="vbo-mngoper-work-days-week-<?php echo $w; ?>">
									<option value="0"><?php echo JText::_('VBO_DAY_OFF'); ?></option>
									<optgroup label="<?php echo $this->escape(JText::_('VBO_WORKING_HOURS')); ?>">
									<?php
									for ($h = 1; $h <= 24; $h++) {
										$is_selected = false;
										foreach (($this->operator['work_days_week'] ?? []) as $wday_data) {
											if ($wday_data['wday'] == $w) {
												$is_selected = $is_selected || (($wday_data['hours'] ?? 0) == $h);
											}
										}
										?>
										<option value="<?php echo $h; ?>"<?php echo $is_selected ? ' selected="selected"' : ''; ?>><?php echo sprintf('%d %s', $h, ($h > 1 ? JText::_('VBCONFIGONETENEIGHT') : JText::_('VBO_HOUR'))); ?></option>
										<?php
									}
									?>
									</optgroup>
								</select>
							</div>
						</div>
						<?php
					}
					?>

						<div class="vbo-param-container">
							<div class="vbo-param-setting">
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_WORK_DAYS_EXCEPTIONS_HELP'); ?></span>
							</div>
						</div>

						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_EXCEPTIONS'); ?></div>
							<div class="vbo-param-setting">
								<div style="width: 100%; display: inline-block;" class="btn-toolbar vbo-mng-oper-work-dates">
									<div class="btn-group pull-left">
										<?php echo $vbo_app->getCalendar('', 'wdexceptfrom', 'wdexceptfrom', '%Y-%m-%d', array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VBCONFIGCLOSINGDATEFROM'))); ?>
									</div>
									<div class="btn-group pull-left">
										<?php echo $vbo_app->getCalendar('', 'wdexceptto', 'wdexceptto', '%Y-%m-%d', array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VBCONFIGCLOSINGDATETO'))); ?>
									</div>
									<div class="btn-group pull-left">
										<select id="wdexcepthours">
											<option value="0"><?php echo JText::_('VBO_DAY_OFF'); ?></option>
											<optgroup label="<?php echo $this->escape(JText::_('VBO_WORKING_HOURS')); ?>">
											<?php
											for ($h = 1; $h <= 24; $h++) {
												?>
												<option value="<?php echo $h; ?>"><?php echo sprintf('%d %s', $h, ($h > 1 ? JText::_('VBCONFIGONETENEIGHT') : JText::_('VBO_HOUR'))); ?></option>
												<?php
											}
											?>
											</optgroup>
										</select>
									</div>
									<div class="btn-group pull-left">
										<button type="button" class="btn vbo-config-btn" onclick="vboMngOperAddDaysException();"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VBCONFIGCLOSINGDATEADD'); ?></button>
									</div>
								</div>
								<div id="vbo-mngoper-work-days-exceptions" style="display: block;">
								<?php
								$active_wday_exceptions = $this->operator['work_days_exceptions'] ?? [];
								foreach ($active_wday_exceptions as $day_exception) {
									$day_exception_signature = implode('::', [($day_exception['from'] ?? ''), ($day_exception['to'] ?? '')]);
									// make sure the hidden input field is the previous element sibling of the DIV containing the object representation
									?>
									<input type="hidden" name="work_days_exceptions[]" value="<?php echo JHtml::_('esc_attr', json_encode($day_exception)); ?>" />
									<div class="vbo-closed-date-entry<?php echo !$day_exception['hours'] ? ' vbo-closed-date-entry-dayoff' : ''; ?>" data-date-period="<?php echo JHtml::_('esc_attr', $day_exception_signature); ?>">
										<span><?php echo date(str_replace("/", $datesep, $usedf), strtotime($day_exception['from'])); ?></span>
									<?php
									if (!empty($day_exception['to']) && $day_exception['from'] != $day_exception['to']) {
										?>
										<span> - </span>
										<span><?php echo date(str_replace("/", $datesep, $usedf), strtotime($day_exception['to'])); ?></span>
										<?php
									}
									?>
										<span> - </span>
										<span><?php echo !($day_exception['hours'] ?? 0) ? JText::_('VBO_DAY_OFF') : sprintf('%d %s', $day_exception['hours'], ($day_exception['hours'] > 1 ? JText::_('VBCONFIGONETENEIGHT') : JText::_('VBO_HOUR'))); ?></span>
										<span class="vbo-closed-date-rm" onclick="vboMngOperRemoveDaysException('<?php echo JHtml::_('esc_attr', $day_exception_signature); ?>');"><?php VikBookingIcons::e('times'); ?></span>
									</div>
									<?php
								}
								?>
								</div>
							</div>
						</div>

					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<?php
if ($this->operator) {
	?>
	<input type="hidden" name="where" value="<?php echo $this->operator['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="task" value="<?php echo $this->operator ? 'updateoperator' : 'saveoperator'; ?>">
	<input type="hidden" name="option" value="com_vikbooking">
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
if ($this->operator) {
	/**
	 * Render the permissions layout to manage the permissions of
	 * a specific operator for the available tools.
	 * 
	 * @since 	1.16.9 (J) - 1.6.9 (WP)
	 */
	$layout_data = [
		'operator_id' => $this->operator['id'],
	];
	// render the permissions layout
	echo JLayoutHelper::render('operators.permissions', $layout_data);
}
