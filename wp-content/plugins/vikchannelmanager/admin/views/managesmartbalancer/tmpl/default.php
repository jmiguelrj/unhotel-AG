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

$vik = new VikApplication(VersionListener::getID());

$df = VikChannelManager::getClearDateFormat();
$debug_val = VikRequest::getInt('e4j_debug', '', 'request');
if ($debug_val == 1) {
	echo '<pre>'.print_r($this->row, true).'</pre><br/>';
}

//Wizard data
$wizard_fest = VikRequest::getString('wizard_fest', '', 'request');
$wizard_from_date = VikRequest::getString('wizard_from_date', '', 'request');
$wizard_to_date = VikRequest::getString('wizard_to_date', '', 'request');
$wizard_radjustment = VikRequest::getString('wizard_radjustment', '', 'request');
$wizard_in_days = VikRequest::getInt('wizard_in_days', '', 'request');
$wizard_in_days = $wizard_in_days < 1 ? 1 : $wizard_in_days;
$wizard_min_gtlt = VikRequest::getInt('wizard_min_gtlt', '', 'request');
$wizard_min_gtlt = $wizard_min_gtlt < 1 ? 1 : $wizard_min_gtlt;

// count total number of room units
$tot_room_units = 0;
foreach ($this->all_rooms as $room) {
	$tot_room_units += $room['units'];
}

// JS lang vars
JText::script('VCMREMOVECONFIRM');

?>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
	
	<div class="vcm-smbal-rule-intro">
		<h3><?php echo JText::_('VCMSMARTBALRULEINTRO'); ?></h3>
		<p><?php echo JText::_('VCMSMARTBALRULEINTROEXPL'); ?></p>
	<?php
	if (!empty($this->rule_id)) {
		?>
		<p>
			<a href="index.php?option=com_vikchannelmanager&task=rmsmartbalancer&cid[]=<?php echo $this->rule_id; ?>" class="btn btn-danger" onclick="return confirm(Joomla.JText._('VCMREMOVECONFIRM'));"><?php VikBookingIcons::e('trash'); ?> <?php echo JText::_('REMOVE'); ?></a>
		</p>
		<?php
	}
	?>
	</div>

	<div class="vcm-smbal-rule-content" data-step="1">
		<h3>1. <?php echo JText::_('VCMSMARTBALRULETYPEEXPL'); ?></h3>
		<p class="vcm-smbal-rule-content-expl"></p>
		<div class="vcm-smbal-rule-content-inner">
			<select name="type" id="smbal-type">
			<?php
			if (empty($this->rule_id)) {
				?>
				<option value="" data-expl=""></option>
				<?php
			}
			if (empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] == 'rt')) {
			?>
				<option value="rt" data-expl="<?php echo addslashes(JText::_('VCMSMARTBALRTYPERATESEXPL')); ?>"<?php echo isset($this->row['type']) && $this->row['type'] == 'rt' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMSMARTBALRTYPERATES'); ?></option>
			<?php
			}
			if (empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] == 'av')) {
				?>
				<option value="av" data-expl="<?php echo addslashes(JText::_('VCMSMARTBALRTYPEAVEXPL')); ?>"<?php echo isset($this->row['type']) && $this->row['type'] == 'av' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMSMARTBALRTYPEAV'); ?></option>
				<?php
			}
			?>
			</select>
		</div>
	</div>

	<div class="vcm-smbal-rule-content" style="display: <?php echo empty($this->rule_id) ? 'none' : 'block'; ?>;" data-step="2">
		<h3>2. <?php echo JText::_('VCMSMARTBALRULEDATESEXPL'); ?></h3>
		<p class="vcm-smbal-rule-content-expl"></p>
		<div class="vcm-smbal-rule-content-inner">
			<div class="vcmavpush-dpick-cont vcm-smbal-rule-datescont">
				<label for="from_date"><?php echo JText::_('VCMFROMDATE'); ?></label>
				<input class="vcmpickdate" type="text" name="from_date" id="from_date" value="">
			</div>
			<div class="vcmavpush-dpick-cont vcm-smbal-rule-datescont">
				<label for="to_date"><?php echo JText::_('VCMTODATE'); ?></label>
				<input class="vcmpickdate" type="text" name="to_date" id="to_date" value="">
			</div>
			<div class="vcmavpush-dpick-cont vcm-smbal-rule-datescont" id="rule-wdays" style="display: none; vertical-align: top;">
				<label for="wdays" style="vertical-align: top;"><?php echo JText::_('VCMWDAYS'); ?></label>
				<select name="wdays[]" id="wdays" size="7" multiple="multiple">
					<option value="0"<?php echo isset($this->row['rule']->wdays) && !in_array(0, $this->row['rule']->wdays) ? '' : ' selected="selected"'; ?>><?php echo JText::_('VCMJQCALSUN'); ?></option>
					<option value="1"<?php echo isset($this->row['rule']->wdays) && !in_array(1, $this->row['rule']->wdays) ? '' : ' selected="selected"'; ?>><?php echo JText::_('VCMJQCALMON'); ?></option>
					<option value="2"<?php echo isset($this->row['rule']->wdays) && !in_array(2, $this->row['rule']->wdays) ? '' : ' selected="selected"'; ?>><?php echo JText::_('VCMJQCALTUE'); ?></option>
					<option value="3"<?php echo isset($this->row['rule']->wdays) && !in_array(3, $this->row['rule']->wdays) ? '' : ' selected="selected"'; ?>><?php echo JText::_('VCMJQCALWED'); ?></option>
					<option value="4"<?php echo isset($this->row['rule']->wdays) && !in_array(4, $this->row['rule']->wdays) ? '' : ' selected="selected"'; ?>><?php echo JText::_('VCMJQCALTHU'); ?></option>
					<option value="5"<?php echo isset($this->row['rule']->wdays) && !in_array(5, $this->row['rule']->wdays) ? '' : ' selected="selected"'; ?>><?php echo JText::_('VCMJQCALFRI'); ?></option>
					<option value="6"<?php echo isset($this->row['rule']->wdays) && !in_array(6, $this->row['rule']->wdays) ? '' : ' selected="selected"'; ?>><?php echo JText::_('VCMJQCALSAT'); ?></option>
				</select>
			</div>
			<div class="vcmavpush-dpick-cont vcm-smbal-rule-datescont" id="rule-excldays" style="display: none;">
				<label for="vcm-excl-date"><?php echo JText::_('VCM_EXCLUDED_DATES'); ?></label>
				<input type="text" id="vcm-excl-date" value="">
				<input type="hidden" id="vcm-excl-date-list" name="av-excl-dates" value="<?php echo isset($this->row['rule']->excl_dates) && is_array($this->row['rule']->excl_dates) ? implode(',', $this->row['rule']->excl_dates) : ''; ?>">
				<div class="vcm-smbal-excldays-list">
			<?php
			if (isset($this->row['rule']->excl_dates) && is_array($this->row['rule']->excl_dates)) {
				foreach ($this->row['rule']->excl_dates as $excl_date) {
					?>
					<button type="button" class="btn btn-small btn-danger" onclick="vcmRemoveExclDate(this, '<?php echo $excl_date; ?>');"><i class="vboicn-cross"></i> <?php echo $excl_date; ?></button>
					<?php
				}
			}
			?>
				</div>
			</div>
		</div>
	</div>

	<div class="vcm-smbal-rule-content" style="display: <?php echo empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] != 'rt') ? 'none' : 'block'; ?>;" data-step="3" data-stepcond="3rt">
		<h3>3. <?php echo JText::_('VCMSMARTBALRULERATESUPDOWN'); ?></h3>
		<p class="vcm-smbal-rule-content-expl"><?php echo JText::_('VCMSMARTBALRULERATESUPDOWNEXPL'); ?></p>
		<div class="vcm-smbal-rule-content-inner">
			<table class="vcm-smbal-rule-table">
				<tr>
					<td>
						<select name="rt-updown" id="smbal-rt-updown">
						<?php
						if (empty($this->rule_id)) {
							?>
							<option value=""></option>
							<?php
						}
						?>
							<option value="up"<?php echo isset($this->row['rule']->updown) && $this->row['rule']->updown == 'up' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMSMARTBALRULERATESUP'); ?></option>
							<option value="down"<?php echo isset($this->row['rule']->updown) && $this->row['rule']->updown == 'down' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMSMARTBALRULERATESDOWN'); ?></option>
						</select>
					</td>
					<td style="display: none;">
						<span class="vcm-rt-updown-symb"></span>
					</td>
					<td style="display: none;">
						<input type="number" min="0" name="rt-amount" id="rt-amount" value="<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && isset($this->row['rule']->amount) ? $this->row['rule']->amount : '0.00'; ?>" />
						<select name="rt-pcent" id="rt-pcent">
							<option value="0"<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && (int)$this->row['rule']->pcent < 1 ? ' selected="selected"' : ''; ?>><?php echo VikBooking::getCurrencySymb(); ?></option>
							<option value="1"<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && (int)$this->row['rule']->pcent > 0 ? ' selected="selected"' : ''; ?>>%</option>
						</select>
					</td>
				</tr>
			</table>
			<table class="vcm-smbal-rule-table" id="vcm-smbal-rule-table-rtgtlt" style="display: none;">
				<tr id="rt-daysadv-row" style="display: none;">
					<td></td>
					<td>
						<span><?php echo JText::_('VCMSMARTBALRULERTDAYSADV'); ?></span>
					</td>
					<td colspan="2" style="text-align: right;">
						<strong><?php echo JText::_('VCMDAYS'); ?></strong>
						<input type="number" name="rt-daysadv" min="1" value="<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && isset($this->row['rule']->daysadv) && $this->row['rule']->updown == 'down' ? $this->row['rule']->daysadv : '2'; ?>" />
					</td>
				</tr>
				<tr id="rt-type-lt-cont">
					<td>
						<input type="radio" name="rt-gtlt" value="lt" id="rt-type-lt"<?php echo empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] == 'rt' && $this->row['rule']->gtlt == 'lt') ? ' checked="checked"' : ''; ?> />
					</td>
					<td>
						<label for="rt-type-lt"><?php echo JText::_('VCMSMARTBALRULERATESLT'); ?></label>
					</td>
					<td>
						<span class="vcm-smbal-rule-rtoper">( <strong>&lt;=</strong> )</span>
					</td>
					<td>
						<div class="vcm-smbal-rule-rtnumber">
							<span><?php echo ucfirst(JText::_('VCMOSUNITSLABEL')); ?></span>
							<input type="number" min="1" name="rt-number" id="rt-number" value="<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' ? $this->row['rule']->units : '1'; ?>" />
						</div>
					</td>
				</tr>
				<tr id="rt-type-gt-cont">
					<td>
						<input type="radio" name="rt-gtlt" value="gt" id="rt-type-gt"<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && $this->row['rule']->gtlt == 'gt' ? ' checked="checked"' : ''; ?> />
					</td>
					<td>
						<label for="rt-type-gt"><?php echo JText::_('VCMSMARTBALRULERATESGT'); ?></label>
					</td>
					<td>
						<span class="vcm-smbal-rule-rtoper">( <strong>&gt;=</strong> )</span>
					</td>
					<td></td>
				</tr>
			</table>
		</div>
	</div>

<?php
/**
 * The Smart Balancer rules of type availability used to require to have at least one room
 * with more than one unit available. This was because only two options existed:
 * av-type = limit and av-type = units. We have now added a third option av-type = block
 * to simply block the dates, and so we need to allow this function also for those who
 * work with just single-unit rooms.
 * 
 * @since 	1.8.3
 */
?>
	<div class="vcm-smbal-rule-content" style="display: <?php echo empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] != 'av') ? 'none' : 'block'; ?>;" data-step="3" data-stepcond="3av">
		<h3>3. <?php echo JText::_('VCMSMARTBALRULEAVHOW'); ?></h3>
		<p class="vcm-smbal-rule-content-expl"><?php echo JText::_('VCMSMARTBALRULEAVTYPEEXPL'); ?></p>
		<div class="vcm-smbal-rule-content-inner">
			<table class="vcm-smbal-rule-table">
				<tr id="av-type-limit-cont"<?php echo !$this->have_multi_units ? ' style="display: none;"' : ''; ?>>
					<td>
						<input type="radio" name="av-type" value="limit" id="av-type-limit"<?php echo !empty($this->rule_id) && $this->row['type'] == 'av' && $this->row['rule']->type == 'limit' ? ' checked="checked"' : ''; ?> />
					</td>
					<td>
						<label for="av-type-limit"><?php echo JText::_('VCMSMARTBALRULEAVLIMIT'); ?></label>
					</td>
					<td>
						<div class="vcm-smbal-rule-avnumber">
							<span><?php echo ucfirst(JText::_('VCMOSUNITSLABEL')); ?></span>
							<input type="number" min="1" name="av-number" id="av-number" value="<?php echo !empty($this->rule_id) && $this->row['type'] == 'av' ? $this->row['rule']->number : '1'; ?>" />
						</div>
					</td>
				</tr>
				<tr id="av-type-units-cont"<?php echo !$this->have_multi_units ? ' style="display: none;"' : ''; ?>>
					<td>
						<input type="radio" name="av-type" value="units" id="av-type-units"<?php echo !empty($this->rule_id) && $this->row['type'] == 'av' && $this->row['rule']->type == 'units' ? ' checked="checked"' : ''; ?> />
					</td>
					<td>
						<label for="av-type-units"><?php echo JText::_('VCMSMARTBALRULEAVUNITS'); ?></label>
					</td>
					<td></td>
				</tr>
				<tr id="av-type-block-cont">
					<td>
						<input type="radio" name="av-type" value="block" data-skipunits="1" id="av-type-block"<?php echo !empty($this->rule_id) && $this->row['type'] == 'av' && $this->row['rule']->type == 'block' ? ' checked="checked"' : ''; ?> />
					</td>
					<td>
						<label for="av-type-block"><?php echo JText::_('VCMSMARTBALRULEAVBLOCK'); ?></label>
					</td>
					<td></td>
				</tr>
			</table>
		</div>
	</div>

	<div class="vcm-smbal-rule-content" style="display: <?php echo empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] != 'rt') ? 'none' : 'block'; ?>;" data-step="4" data-stepcond="4rt">
		<h3>4. <?php echo JText::_('VCMSMARTBALRULERTWHEREEXPL'); ?></h3>
		<div class="vcm-smbal-rule-content-inner">
			<select name="rt-where" id="rt-where">
			<?php
			if (empty($this->rule_id)) {
				?>
				<option value=""></option>
				<?php
			}
			?>
				<option value="ibeota"<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && $this->row['rule']->ibeotas == 'ibeota' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMSMARTBALRULERTIBEOTA'); ?></option>
				<option value="ibe"<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && $this->row['rule']->ibeotas == 'ibe' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMSMARTBALRULERTIBE'); ?></option>
			</select>
		</div>
	</div>

	<div class="vcm-smbal-rule-content" style="display: <?php echo empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] != 'av') ? 'none' : 'block'; ?>;" data-step="4" data-stepcond="4av">
		<h3>4. <?php echo JText::_('VCMSMARTBALRULEAVSELROOMS'); ?></h3>
		<p><?php echo JText::sprintf('VCM_TOTROOM_UNITS_TYPES', $tot_room_units, count($this->all_rooms)); ?></p>
		<div class="vcm-smbal-rule-content-inner">
			<select name="av-rooms[]" id="av-rooms" size="<?php echo count($this->all_rooms) > 10 ? '10' : count($this->all_rooms); ?>" multiple="multiple">
			<?php
			foreach ($this->all_rooms as $room) {
				?>
				<option value="<?php echo $room['id']; ?>"<?php echo !empty($this->rule_id) && $this->row['type'] == 'av' && in_array($room['id'], $this->row['rooms_aff']) ? ' selected="selected"' : ''; ?>><?php echo $room['name']; ?> (<?php echo $room['units']; ?>)</option>
				<?php
			}
			?>
			</select>
			<button type="button" class="btn vcm-sel-all-rooms" style="vertical-align: top;"><?php echo JText::_('VCMSMARTBALSELALL'); ?></button>
		</div>
	</div>

	<div class="vcm-smbal-rule-content" style="display: <?php echo empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] != 'rt') ? 'none' : 'block'; ?>;" data-step="5" data-stepcond="5rt">
		<h3>5. <?php echo JText::_('VCMSMARTBALRULEAVSELROOMS'); ?></h3>
		<p class="vcm-smbal-rule-content-expl"><?php echo JText::_('VCMSMARTBALRULEAVSELROOMSEXPL'); ?></p>
		<div class="vcm-smbal-rule-content-inner">
			<select name="rt-rooms[]" id="rt-rooms" size="<?php echo count($this->all_rooms) > 10 ? '10' : count($this->all_rooms); ?>" multiple="multiple">
			<?php
			foreach ($this->all_rooms as $room) {
				?>
				<option value="<?php echo $room['id']; ?>" data-units="<?php echo $room['units']; ?>"<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && in_array($room['id'], $this->row['rooms_aff']) ? ' selected="selected"' : ''; ?>><?php echo $room['name']; ?> (<?php echo $room['units']; ?>)</option>
				<?php
			}
			?>
			</select>
			<button type="button" class="btn vcm-sel-all-rooms" style="vertical-align: top;"><?php echo JText::_('VCMSMARTBALSELALL'); ?></button>
			<br />
			<table class="vcm-smbal-rule-table">
				<tr>
					<td>
						<input type="radio" name="rt-units" value="single" id="rt-units-single"<?php echo empty($this->rule_id) || (!empty($this->rule_id) && $this->row['type'] == 'rt' && $this->row['rule']->units_count == 'single') ? 'checked="checked"' : ''; ?> />
					</td>
					<td>
						<label for="rt-units-single"><?php echo JText::_('VCMSMARTBALRULERTUNITSSINGLE'); ?></label>
					</td>
				</tr>
				<tr>
					<td>
						<input type="radio" name="rt-units" value="group" id="rt-units-group"<?php echo !empty($this->rule_id) && $this->row['type'] == 'rt' && $this->row['rule']->units_count == 'group' ? 'checked="checked"' : ''; ?> />
					</td>
					<td>
						<label for="rt-units-group"><?php echo JText::_('VCMSMARTBALRULERTUNITSGROUP'); ?></label>
					</td>
				</tr>
			</table>
		</div>
	</div>

<?php
if (empty($this->rule_id)) {
	//Ready for saving message
	?>
	<div class="vcm-smbal-rule-content vcm-smbal-rule-ready" style="display: none;">
		<h3><i class="vboicn-checkmark" style="color: #196a19;"></i> <?php echo JText::_('VCMSMARTBALRULEREADYSAVE'); ?></h3>
		<div class="vcm-smbal-rule-content-inner">
			<label for="rule-name"><?php echo JText::_('VCMSMARTBALRNAME'); ?></label>
			<input type="text" name="rule-name" id="rule-name" value="" size="40" />
		</div>
	</div>
	<?php
} else {
	?>
	<div class="vcm-smbal-rule-content vcm-smbal-rule-ready" style="display: block;">
		<h3><i class="vboicn-checkmark" style="color: #196a19;"></i> <?php echo JText::_('VCMSMARTBALRULEREADYSAVE'); ?></h3>
		<div class="vcm-smbal-rule-content-inner">
			<label for="rule-name"><?php echo JText::_('VCMSMARTBALRNAME'); ?></label>
			<input type="text" name="rule-name" id="rule-name" value="<?php echo htmlentities($this->row['name']); ?>" size="40" />
		</div>
	</div>
	<?php
}
?>

	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="whereup" value="<?php echo (int)$this->rule_id; ?>" />
</form>

<div class="vcm-smbal-rule-spacer"></div>

<div class="vcm-floating-scrolltop" style="display: none;">
	<div class="vcm-floating-scrolltop-inner">
		<button type="button" class="btn vcm-scrolltop-btn" id="vcm-scrolltop-trigger"><?php VikBookingIcons::e('arrow-up'); ?></button>
	</div>
</div>

<script type="text/javascript">
var canSave = false;
var isNew = <?php echo empty($this->rule_id) ? 'true' : 'false'; ?>;
var currentStep = 1;
var totSteps = 0;

//Prevent submission if not ready
Joomla.submitbutton = function(task) {
	if (task == 'savesmartbalancer' && !canSave) {
		alert('<?php echo addslashes(JText::_('MSG_BASE_ERROR_REQUEST')); ?>');
		return false;
	}
	Joomla.submitform(task, document.adminForm);
}
//

function calculateRuleName() {
	var rule_name = '';
	var main_type = jQuery('#smbal-type').val();
	if (main_type == 'rt') {
		var rt_type = jQuery('#smbal-rt-updown').val();
		if (rt_type == 'up') {
			rule_name = '<?php echo addslashes(JText::_('VCMSMARTBALRNAMERTUP')); ?>';
		} else if (rt_type == 'down') {
			rule_name = '<?php echo addslashes(JText::_('VCMSMARTBALRNAMERTDOWN')); ?>';
		}
	} else if (main_type == 'av') {
		var av_type = jQuery('input[name="av-type"]:checked').val();
		if (av_type == 'block') {
			rule_name = '<?php echo addslashes(JText::_('VCM_BLOCK_DATES')); ?>';
		} else {
			rule_name = '<?php echo addslashes(JText::_('VCMSMARTBALRNAMEAV')); ?>';
		}
	}
	if (isNew) {
		jQuery('#rule-name').val(rule_name);
	}
}

function vcmTriggerStep(origCurrentStep) {
	//must be called when the currentStep has already been increased.
	if (currentStep == 2) {
		//datepicker calendars
		vcmCheckDates(null, null);
	} else {
		var main_type = jQuery('#smbal-type').val();
		var checkelem = jQuery('.vcm-smbal-rule-content[data-step="'+currentStep+'"]');
		var stepcond = checkelem.attr('data-stepcond');
		if (typeof stepcond !== typeof undefined && stepcond !== false) {
			checkelem = jQuery('.vcm-smbal-rule-content[data-stepcond="'+currentStep+main_type+'"]');
		}
		checkelem.find("select, input").first().trigger("change");
	}
}

function vcmNextStep(fromStep) {
	var origStep = currentStep;
	var doTrigger = false;
	if (fromStep == currentStep) {
		currentStep++;
	} else {
		//a previous step has been modified
		currentStep = fromStep + 1;
		doTrigger = true;
		//hide all the steps after
		if (currentStep < totSteps) {
			for (var i = currentStep; i <= totSteps; i++) {
				jQuery('.vcm-smbal-rule-content[data-step="'+i+'"]').hide();
			}
			//reset canSave to false and hide 'is ready'
			canSave = false;
			jQuery('.vcm-smbal-rule-ready').hide();
			//
		}
	}
	var main_type = jQuery('#smbal-type').val();
	var gotoelem = jQuery('.vcm-smbal-rule-content[data-step="'+currentStep+'"]');
	if (gotoelem.length) {
		var stepcond = gotoelem.attr('data-stepcond');
		if (typeof stepcond !== typeof undefined && stepcond !== false) {
			gotoelem.hide();
			gotoelem = jQuery('.vcm-smbal-rule-content[data-stepcond="'+currentStep+main_type+'"]');
		}
		gotoelem.fadeIn(400, function() {
			jQuery('html,body').animate({ scrollTop: (gotoelem.offset().top - 5) }, { duration: 'fast' });
			if (doTrigger === true) {
				vcmTriggerStep(origStep);
			}
		});
	} else {
		currentStep--;
	}
}

//datepicker selections
function vcmCheckDates(selectedDate, inst) {
	if (selectedDate !== null && inst !== null) {
		var inpidparts = jQuery(this).attr('id').split('_');
		var cur_from_date = jQuery(this).val();
		if (inpidparts[0] == 'from' && cur_from_date.length > 0) {
			var nowstart = jQuery(this).datepicker('getDate');
			var nowstartdate = new Date(nowstart.getTime());
			var nextyear = new Date(nowstart.getTime());
			nextyear.setFullYear((nextyear.getFullYear() + 2));
			jQuery('#to_'+inpidparts[1]).datepicker( 'option', { minDate: nowstartdate, maxDate: nextyear } );
			jQuery('#vcm-excl-date').datepicker( 'option', { minDate: nowstartdate, maxDate: nextyear } );
		} else if (inpidparts[0] == 'to' && cur_from_date.length > 0) {
			var nowstart = jQuery(this).datepicker('getDate');
			var nowstartdate = new Date(nowstart.getTime());
			jQuery('#vcm-excl-date').datepicker( 'option', { maxDate: nowstartdate } );
		}
	}
	jQuery('.vcmpickdate:input').each(function(k, v) {
		var inpidparts = jQuery(this).attr('id').split('_');
		var cur_from_date = jQuery(this).val();
		var cur_to_date = jQuery('#to_'+inpidparts[1]).val();
		if (inpidparts[0] != 'from' || cur_from_date.length <= 0 || cur_to_date.length <= 0) {
			return true;
		}
		var fromd = new Date(cur_from_date);
		fromd.setMinutes(fromd.getMinutes() - fromd.getTimezoneOffset());
		var tod = new Date(cur_to_date);
		tod.setMinutes(tod.getMinutes() - tod.getTimezoneOffset());
		var daysdiff = Math.round((tod - fromd) / 86400000);
		if (parseInt(daysdiff) < 0) {
			daysdiff = 1;
			fromd.setDate(fromd.getDate() + 1);
			jQuery('#to_'+inpidparts[1]).datepicker( 'setDate', fromd );
		}
		var base_str = "<?php echo addslashes(JText::_('VCMSMARTBALRULEDATESLENGTH')); ?>";
		jQuery(this).closest(".vcm-smbal-rule-content").find(".vcm-smbal-rule-content-expl").html(base_str.replace("%d", daysdiff));
		if (daysdiff > 6<?php echo !empty($this->rule_id) && isset($this->row['rule']->wdays) && count($this->row['rule']->wdays) > 0 && count($this->row['rule']->wdays) < 7 ? ' || true' : ''; ?>) {
			jQuery('#rule-wdays').show();
		} else {
			jQuery('#rule-wdays').hide();
		}
		if (isNew) {
			//show the next field
			vcmNextStep(2);
		}
	});
}

function vcmAddExcludedDate(selectedDate, inst) {
	if (!selectedDate) {
		return;
	}

	// check if this date has already been selected
	var current_excl_dates_val = jQuery('#vcm-excl-date-list').val();
	var current_excl_dates = current_excl_dates_val && current_excl_dates_val.length ? current_excl_dates_val.split(',') : [];
	if (current_excl_dates.length && current_excl_dates.indexOf(selectedDate) >= 0) {
		console.log(selectedDate + ' has already been excluded');
		return;
	}

	// add new excluded date
	current_excl_dates.push(selectedDate);
	jQuery('#vcm-excl-date-list').val(current_excl_dates.join(','));
	var excl_date_btn = '<button type="button" class="btn btn-small btn-danger" onclick="vcmRemoveExclDate(this, \'' + selectedDate + '\');"><i class="vboicn-cross"></i> ' + selectedDate + '</button>';
	jQuery('.vcm-smbal-excldays-list').append(excl_date_btn + "\n");

	// empty date selected
	jQuery(this).datepicker('setDate', '');
	// drop focus to input field
	jQuery('#vcm-excl-date').blur();
}

function vcmRemoveExclDate(elem, dt) {
	if (!elem || !dt) {
		return;
	}
	// remove button
	jQuery(elem).remove();
	// remove date from hidden input field
	var current_excl_dates_val = jQuery('#vcm-excl-date-list').val();
	var current_excl_dates = current_excl_dates_val && current_excl_dates_val.length ? current_excl_dates_val.split(',') : [];
	var dt_index = current_excl_dates.indexOf(dt);
	if (current_excl_dates.length && dt_index >= 0) {
		current_excl_dates.splice(dt_index, 1);
		jQuery('#vcm-excl-date-list').val(current_excl_dates.join(','));
	}
}
//

// go to top
function vcmDebounceEvent(method, delay) {
	clearTimeout(method.timer);
	method.timer = setTimeout(function() {
		method();
	}, delay);
}

function vcmHandleScroll() {
	if (jQuery(window).scrollTop() > 150) {
		jQuery('.vcm-floating-scrolltop').fadeIn();
	} else {
		jQuery('.vcm-floating-scrolltop').hide();
	}
}

jQuery(document).ready(function() {

	//calculate tot steps
	jQuery('.vcm-smbal-rule-content').each(function(k, v) {
		var nowstep = parseInt(jQuery(this).attr('data-step'));
		if (!isNaN(nowstep) && nowstep > totSteps) {
			totSteps = nowstep;
		}
	});
	//

	//prepare datepicker calendars
	jQuery('.vcmpickdate:input').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: 0,
		maxDate: "+2y",
		onSelect: vcmCheckDates
	});
	jQuery('#vcm-excl-date').datepicker({
		dateFormat: "yy-mm-dd",
		minDate: 0,
		maxDate: "+2y",
		onSelect: vcmAddExcludedDate
	});
<?php
if (!empty($this->rule_id)) {
	//Populate rule dates
	?>
	jQuery('#from_date').datepicker( 'setDate', new Date('<?php echo date('Y-m-d', $this->row['from_ts']); ?>') );
	jQuery('#to_date').datepicker( 'setDate', new Date('<?php echo date('Y-m-d', $this->row['to_ts']); ?>') );
	<?php
}
?>
	vcmCheckDates(null, null);
	//
	
	//onchange listener for the type
	jQuery("#smbal-type").on('change', function() {
		var cur_val = jQuery(this).val();
		if (cur_val.length) {
			jQuery(this).closest(".vcm-smbal-rule-content").find(".vcm-smbal-rule-content-expl").html(jQuery(this).find("option:selected").attr("data-expl"));
			if (jQuery(this).find('option[value=""]').length) {
				//remove the empty option at the first selection
				jQuery(this).find('option[value=""]').remove();
			}
			if (isNew) {
				//show the next field
				vcmNextStep(1);
			}
		} else {
			jQuery(this).closest(".vcm-smbal-rule-content").find(".vcm-smbal-rule-content-expl").html("");
		}
	});
	//

	//onchange listener for the av-type
	jQuery('input[name="av-type"]').on('change', function() {
		var active_radio = jQuery('input[name="av-type"]:checked');
		var nowval = active_radio.val();
		var skip_units = active_radio.attr('data-skipunits');
		if (!skip_units) {
			jQuery('.vcm-smbal-rule-avnumber').show().appendTo('#av-type-'+nowval+'-cont td:last-child');
		} else {
			jQuery('.vcm-smbal-rule-avnumber').hide();
		}
		if (nowval == 'block') {
			jQuery('#rule-excldays').show();
		} else {
			jQuery('#rule-excldays').hide();
		}
		if (isNew && currentStep < 4) {
			//show the next field
			vcmNextStep(3);
		}
	});
	//

	//onchange listener for the rt-updown
	jQuery('#smbal-rt-updown').on('change', function() {
		var cur_val = jQuery(this).val();
		if (cur_val.length) {
			if (jQuery(this).find('option[value=""]').length) {
				//remove the empty option at the first selection
				jQuery(this).find('option[value=""]').remove();
			}
			jQuery(this).closest("table").find("td").show();
			jQuery('#vcm-smbal-rule-table-rtgtlt').show();
			if (cur_val == 'up') {
				jQuery('.vcm-rt-updown-symb').html('+');
				//hide the minimum days in advance
				jQuery('#rt-daysadv-row').hide();
				//
			} else {
				jQuery('.vcm-rt-updown-symb').html('-');
				//display the minimum days in advance
				jQuery('#rt-daysadv-row').show();
				//
			}
		}
	});
	//

	//onchange listener for the rt-gtlt
	jQuery('input[name="rt-gtlt"]').on('change', function() {
		var nowval = jQuery('input[name="rt-gtlt"]:checked').val();
		jQuery('.vcm-smbal-rule-rtnumber').appendTo('#rt-type-'+nowval+'-cont td:last-child');
	});
	//

	//onchange listener for the rates modification amount
	jQuery('#rt-amount').on('change', function() {
		var cur_val = parseFloat(jQuery(this).val());
		if (!isNaN(cur_val) && cur_val > 0) {
			if (isNew && currentStep <= 3) {
				//show the next field
				vcmNextStep(3);
			}
		}
	});
	//

	//onchange listener for the rooms multi-select AV
	jQuery('#av-rooms').on('change', function() {
		var tot_sel = jQuery('#av-rooms option:selected').length;
		if (tot_sel > 0) {
			canSave = true;
		} else {
			canSave = false;
		}
		if (jQuery('.vcm-smbal-rule-ready').length) {
			if (canSave) {
				calculateRuleName();
				jQuery('.vcm-smbal-rule-ready').fadeIn();
			} else {
				jQuery('.vcm-smbal-rule-ready').hide();
			}
		}
	});
	//

	//onchange listener for the RT Where
	jQuery('#rt-where').on('change', function() {
		var cur_val = jQuery(this).val();
		if (cur_val.length) {
			if (jQuery(this).find('option[value=""]').length) {
				//remove the empty option at the first selection
				jQuery(this).find('option[value=""]').remove();
			}
			if (isNew && currentStep <= 4) {
				//show the next field
				vcmNextStep(4);
			}
		}
	});
	//

	//onchange listener for the rooms multi-select RT
	jQuery('#rt-rooms').on('change', function() {
		var tot_sel = jQuery('#rt-rooms option:selected').length;
		if (tot_sel > 0) {
			canSave = true;
		} else {
			canSave = false;
		}
		if (jQuery('.vcm-smbal-rule-ready').length) {
			if (canSave) {
				calculateRuleName();
				jQuery('.vcm-smbal-rule-ready').fadeIn();
			} else {
				jQuery('.vcm-smbal-rule-ready').hide();
			}
		}
	});
	//

	//onclick listener for the select all rooms buttons
	jQuery('.vcm-sel-all-rooms').on('click', function() {
		jQuery(this).parent().find('select option').prop('selected', true);
		jQuery(this).parent().find('select').trigger('change');
	});
	//

<?php
if (!empty($this->rule_id)) {
	//trigger events for this rule
	if ($this->row['type'] == 'rt') {
	?>
	jQuery('#smbal-rt-updown').trigger('change');
	jQuery('input[name="rt-gtlt"]').trigger('change');
	jQuery('#rt-amount').trigger('change');
	<?php
	} elseif ($this->row['type'] == 'av') {
	?>
	jQuery('input[name="av-type"]').trigger('change');
	<?php
	}
} elseif (!empty($wizard_fest)) {
	//Wizard submitted data
	$wiz_updown = strlen($wizard_radjustment) && substr($wizard_radjustment, 0, 1) == '-' ? 'down' : 'up';
	$wiz_amount = floatval(str_replace('%', '', substr($wizard_radjustment, 1)));
	$wiz_rt_gtlt = $wiz_updown == 'up' ? 'lt' : 'gt';
	//always suggest to count the units left as a group
	//$wiz_rt_units = $wiz_updown == 'up' ? 'single' : 'group';
	$wiz_rt_units = 'group';
	?>
	jQuery('#smbal-type').val('rt').trigger('change');
	jQuery('#from_date').datepicker('setDate', new Date('<?php echo $wizard_from_date; ?>'));
	jQuery('#to_date').datepicker('setDate', new Date('<?php echo $wizard_to_date; ?>'));
	vcmCheckDates(null, null);
	jQuery('#smbal-rt-updown').val('<?php echo $wiz_updown; ?>').trigger('change');
	jQuery('#rt-pcent').val('1').trigger('change');
	<?php
	if ($wiz_updown == 'down') {
		//discount rates should set the min days in advance
		?>
	jQuery('input[name="rt-daysadv"]').val('<?php echo $wizard_in_days; ?>').trigger('change');
		<?php
	}
	?>
	jQuery('#rt-number').val('<?php echo $wizard_min_gtlt; ?>').trigger('change');
	jQuery('#rt-type-<?php echo $wiz_rt_gtlt; ?>').prop('checked', true).trigger('change');
	jQuery('#rt-amount').val('<?php echo $wiz_amount; ?>').trigger('change');
	jQuery('#rt-where').val('ibeota').trigger('change');
	jQuery('.vcm-sel-all-rooms').trigger('click');
	jQuery('#rt-units-<?php echo $wiz_rt_units; ?>').prop('checked', true).trigger('change');
	jQuery('#rule-name').val("<?php echo htmlentities($wizard_fest); ?>");
	<?php
}
?>

	// select2
	jQuery('#wdays').select2();
	jQuery('#av-rooms').select2();
	jQuery('#rt-rooms').select2();

	// scrolltop button
	jQuery('#vcm-scrolltop-trigger').click(function() {
		jQuery('html,body').animate({scrollTop: 0}, {duration: 400});
	});

	// scrolltop button position listener
	jQuery(window).scroll(function() {
		vcmDebounceEvent(vcmHandleScroll, 500);
	});

});
</script>
