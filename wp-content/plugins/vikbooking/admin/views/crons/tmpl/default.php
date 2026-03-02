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

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$orderby = $this->orderby;
$ordersort = $this->ordersort;

$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);

JText::script('VBCRONLOGS');
JText::script('VBCRONGETCMD');

if (empty($rows)) {
	?>
<p class="warn"><?php echo JText::_('VBNOCRONS'); ?></p>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="crons" />
	<input type="hidden" name="option" value="com_vikbooking" />
</form>
	<?php
} else {
	?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm" class="vbo-list-form">
	<div class="table-responsive">
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
			<thead>
				<tr>
					<th width="20">
						<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
					</th>
					<th class="title left" width="50">
						<a href="index.php?option=com_vikbooking&amp;view=crons&amp;vborderby=id&amp;vbordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "id" ? "vbo-list-activesort" : "")); ?>">
							ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "id" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
						</a>
					</th>
					<th class="title left" width="200">
						<a href="index.php?option=com_vikbooking&amp;view=crons&amp;vborderby=cron_name&amp;vbordersort=<?php echo ($orderby == "cron_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "cron_name" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "cron_name" ? "vbo-list-activesort" : "")); ?>">
							<?php echo JText::_('VBCRONNAME').($orderby == "cron_name" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "cron_name" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
						</a>
					</th>
					<th class="title center" width="100"><?php echo JText::_( 'VBCRONCLASS' ); ?></th>
					<th class="title center" width="75">
						<a href="index.php?option=com_vikbooking&amp;view=crons&amp;vborderby=last_exec&amp;vbordersort=<?php echo ($orderby == "last_exec" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "last_exec" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "last_exec" ? "vbo-list-activesort" : "")); ?>">
							<?php echo JText::_('VBCRONLASTEXEC').($orderby == "last_exec" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "last_exec" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
						</a>
					</th>
					<th class="title center" width="50"><?php echo JText::_( 'VBCRONPUBLISHED' ); ?></th>
					<th class="title center" width="150"><?php echo JText::_( 'VBCRONACTIONS' ); ?></th>
					<th class="title center" width="100">&nbsp;</th>
				</tr>
			</thead>
		<?php
		$kk = 0;
		$i = 0;
		for ($i = 0, $n = count($rows); $i < $n; $i++) {
			$row = $rows[$i];
			?>
			<tr class="row<?php echo $kk; ?>">
				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);">
				</td>
				<td>
					<a href="index.php?option=com_vikbooking&amp;task=cronjob.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a>
				</td>
				<td class="vbo-highlighted-td">
					<a href="index.php?option=com_vikbooking&amp;task=cronjob.edit&amp;cid[]=<?php echo $row['id']; ?>" class="vbo-cron-name" data-cronid="<?php echo $row['id']; ?>"><?php echo $row['cron_name']; ?></a>
				</td>
				<td class="center">
					<?php echo $row['class_file']; ?>
				</td>
				<td class="center">
					<?php echo !empty($row['last_exec']) ? date(str_replace("/", $datesep, $df).' H:i:s', $row['last_exec']) : '----'; ?>
				</td>
				<td class="center">
				<?php
				if (intval($row['published']) > 0) {
					?>
					<i class="<?php echo VikBookingIcons::i('check', 'vbo-icn-img'); ?>" style="color: #099909;"></i>
					<?php
				} else {
					?>
					<i class="<?php echo VikBookingIcons::i('times', 'vbo-icn-img'); ?>" style="color: #ff0000;"></i>
					<?php
				}
				?>
				</td>
				<td class="center">
				<?php
				if (VBOPlatformDetection::isJoomla()) {
					?>
					<button type="button" class="btn btn-secondary vbo-getcmd" data-cronid="<?php echo $row['id']; ?>" data-cronname="<?php echo addslashes($row['cron_name']); ?>" data-cronclass="<?php echo $row['class_file']; ?>"><?php VikBookingIcons::e('terminal'); ?> <?php echo JText::_('VBCRONGETCMD'); ?></button> &nbsp;&nbsp; 
					<?php
				}
				?>
					<button type="button" class="btn btn-warning vbo-exec" data-cronid="<?php echo $row['id']; ?>"><span><?php VikBookingIcons::e('play'); ?></span> <?php echo JText::_('VBCRONACTION'); ?></button>
				</td>
				<td class="center">
					<button type="button" class="btn btn-secondary vbo-logs" data-cronid="<?php echo $row['id']; ?>"><span><?php VikBookingIcons::e('server'); ?></span> <?php echo JText::_('VBCRONLOGS'); ?></button>
				</td>
			</tr>
			<?php
			$kk = 1 - $kk;
		}
		?>
		</table>
	</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="crons" />
	<input type="hidden" name="view" value="crons" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $navbut; ?>
</form>

<div class="vbo-crons-list-helper" style="display: none;">
	<?php
	if (VBOPlatformDetection::isJoomla()) {
		/**
		 * @joomlaonly  we split the instructions depending on the Joomla version.
		 */
		$j_version = (new JVersion)->getShortVersion();
		$scheduled_tasks_supported = version_compare($j_version, '4.1.0', '>=');
		?>
	<div class="vbo-crons-list-helper-getcmd">
		<?php
		if ($scheduled_tasks_supported) {
			// Joomla 4.1.x
			echo JText::sprintf('VBO_CRONJOB_INSTRUCTIONS_SCHEDTASKS', 'index.php?option=com_scheduler', '<i class="' . VikBookingIcons::i('external-link') . '"></i>');
			// base URI for executing the cron job via GET request
			$base_cron_uri = VBOFactory::getPlatform()->getUri()->route('index.php?option=com_vikbooking&task=cron_exec&cron_id=%d&cronkey=' . md5(VikBooking::getCronKey()));
			?>
		<div class="vbo-admin-container vbo-params-container-wide">
			<div class="vbo-config-maintab-left">
				<fieldset class="adminform">
					<div class="vbo-params-wrap">
						<div class="vbo-params-container">
							<div class="vbo-param-container">
								<div class="vbo-param-label"><?php echo JText::_('VBO_CRONJOB_SCHEDTASKS_EXEC_URL'); ?></div>
								<div class="vbo-param-setting">
									<div class="input-group">
										<input type="text" value="" data-baseuri="<?php echo $base_cron_uri; ?>" id="vbo-cron-url-exec" style="min-width: 60%;" readonly />
										<button type="button" class="btn btn-primary" id="vbo-cron-url-copy"><i id="vbo-copycronurl-result" class="<?php echo VikBookingIcons::i('copy'); ?>"></i></button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(function() {
				jQuery('#vbo-cron-url-copy').click(function() {
					VBOCore.copyToClipboard(jQuery('#vbo-cron-url-exec')).then(() => {
						jQuery('#vbo-cron-url-copy').addClass('btn-success');
						jQuery('#vbo-copycronurl-result').attr('class', '<?php echo VikBookingIcons::i('check-circle'); ?>');
						setTimeout(() => {
							jQuery('#vbo-cron-url-copy').removeClass('btn-success');
							jQuery('#vbo-copycronurl-result').attr('class', '<?php echo VikBookingIcons::i('copy'); ?>');
						}, 1500);
					});
				});
			});
		</script>
		<?php
		} else {
			// Joomla 3.x
			?>
		<blockquote class="vbo-crongetcmd-help"><?php echo JText::_('VBCRONGETCMDHELP') ?></blockquote>
		<h4><?php echo JText::_('VBCRONGETCMDINSTSTEPS') ?></h4>
		<ol>
			<li><?php echo JText::_('VBCRONGETCMDINSTSTEPONE') ?></li>
			<li><?php echo JText::_('VBCRONGETCMDINSTSTEPTWO') ?></li>
			<li><?php echo JText::_('VBCRONGETCMDINSTSTEPTHREE') ?></li>
			<li><?php echo JText::_('VBCRONGETCMDINSTSTEPFOUR') ?></li>
		</ol>
		<p><?php echo JText::_('VBCRONGETCMDINSTPATH'); ?></p>
		<p><span class="label label-info">/usr/bin/php <?php echo JPATH_SITE . DIRECTORY_SEPARATOR; ?><span class="crongetcmd-php"></span>.php</span></p>
		<p><i class="vboicn-warning"></i><?php echo JText::_('VBCRONGETCMDINSTURL'); ?></p>
		<p><span class="label"><?php echo JURI::root(); ?><span class="crongetcmd-php"></span>.php</span></p>
		<br/>
		<form action="index.php?option=com_vikbooking" method="post">
			<button type="submit" class="btn"><i class="vboicn-download"></i><?php echo JText::_('VBCRONGETCMDGETFILE') ?></button>
			<input type="hidden" name="cron_id" id="cronid-inp" value="" />
			<input type="hidden" name="cron_name" id="cronname-inp" value="" />
			<input type="hidden" name="task" value="downloadcron" />
		</form>
		<?php
		}
		?>
	</div>
		<?php
	}
	?>

	<div class="vbo-crons-list-helper-cronoutput">
		<div class="vbo-cron-modal-output"></div>
	</div>

</div>

<script type="text/javascript">

	jQuery(function() {

		jQuery('.vbo-getcmd').click(function() {
			var cronid = jQuery(this).attr('data-cronid');
			var cronname = jQuery(this).attr('data-cronname');
			var cronclass = jQuery(this).attr('data-cronclass');
			jQuery('#cronid-inp').val(cronid);
			var cronnamephp = cronname.replace(/\s/g, '').toLowerCase();
			jQuery('#cronname-inp').val(cronnamephp);
			jQuery('.crongetcmd-php').text(cronnamephp);

			/**
			 * @joomlaonly  code is compatible even if the element is not present.
			 */
			var j_exec_uri_input = jQuery('#vbo-cron-url-exec');
			if (j_exec_uri_input.length) {
				var cron_base_uri = j_exec_uri_input.attr('data-baseuri');
				j_exec_uri_input.val(cron_base_uri.replace('%d', cronid));
			}

			// display modal
			let modal_body = VBOCore.displayModal({
				suffix: 'crons-list-getcmd',
				extra_class: 'vbo-modal-large vbo-modal-nofooter',
				title: '<?php VikBookingIcons::e('terminal'); ?> ' + Joomla.JText._('VBCRONGETCMD') + ': ' + cronname,
				onDismiss: () => {
					jQuery('.vbo-crons-list-helper-getcmd').appendTo('.vbo-crons-list-helper');
				},
			});

			// set modal body
			jQuery('.vbo-crons-list-helper-getcmd').appendTo(modal_body);
		});

		jQuery('.vbo-logs').click(function() {
			var btn_trig  = jQuery(this);
			var cron_id   = btn_trig.attr('data-cronid');
			var cron_name = jQuery('.vbo-cron-name[data-cronid="' + cron_id + '"]').text();

			btn_trig.find('span').html('<?php VikBookingIcons::e('circle-notch', 'fa-spin fa-fw'); ?>');

			VBOCore.doAjax(
				"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=cronlogs'); ?>",
				{
					cron_id: cron_id,
				},
				(resp) => {
					btn_trig.find('span').html('<?php VikBookingIcons::e('server'); ?>');
					try {
						var obj_res = typeof resp === 'string' ? JSON.parse(resp) : resp;

						// set helper content
						jQuery('.vbo-cron-modal-output').html(obj_res[0]);

						// display modal
						let modal_body = VBOCore.displayModal({
							suffix: 'crons-list-logs',
							extra_class: 'vbo-modal-large vbo-modal-nofooter',
							title: cron_name + ' - ' + Joomla.JText._('VBCRONLOGS'),
							onDismiss: () => {
								jQuery('.vbo-crons-list-helper-cronoutput').appendTo('.vbo-crons-list-helper');
							},
						});

						// set modal body
						jQuery('.vbo-crons-list-helper-cronoutput').appendTo(modal_body);
					} catch(err) {
						console.error('Could not parse JSON response', err, resp);
					}
				},
				(err) => {
					console.log(err);
					alert(err.responseText);
					btn_trig.find('span').html('<?php VikBookingIcons::e('server'); ?>');
				}
			);
		});

		jQuery('.vbo-exec').click(function() {
			var btn_trig  = jQuery(this);
			var cron_id   = btn_trig.attr('data-cronid');
			var cron_name = jQuery('.vbo-cron-name[data-cronid="' + cron_id + '"]').text();

			btn_trig.find('span').html('<?php VikBookingIcons::e('circle-notch', 'fa-spin fa-fw'); ?>');

			VBOCore.doAjax(
				"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=cron_exec&cronkey=' . md5(VikBooking::getCronKey())); ?>",
				{
					cron_id: cron_id,
				},
				(resp) => {
					btn_trig.find('span').html('<?php VikBookingIcons::e('play'); ?>');
					try {
						var obj_res = typeof resp === 'string' ? JSON.parse(resp) : resp;

						// set helper content
						jQuery('.vbo-cron-modal-output').html(obj_res[0]);

						// display modal
						let modal_body = VBOCore.displayModal({
							suffix: 'crons-list-exec',
							extra_class: 'vbo-modal-large vbo-modal-nofooter',
							title: '<?php VikBookingIcons::e('terminal'); ?> ' + cron_name,
							onDismiss: () => {
								jQuery('.vbo-crons-list-helper-cronoutput').appendTo('.vbo-crons-list-helper');
							},
						});

						// set modal body
						jQuery('.vbo-crons-list-helper-cronoutput').appendTo(modal_body);
					} catch(err) {
						console.error('Could not parse JSON response', err, resp);
					}
				},
				(err) => {
					console.log(err);
					alert(err.responseText);
					btn_trig.find('span').html('<?php VikBookingIcons::e('play'); ?>');
				}
			);
		});

	});
</script>
<?php
}
