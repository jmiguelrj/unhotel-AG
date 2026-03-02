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

$field = $this->field;

$vbo_app = VikBooking::getVboApplication();

$choose = "";
if (count($field) && $field['type'] == "select") {
	$x = explode(";;__;;", $field['choose']);
	foreach ($x as $y) {
		if (!empty($y)) {
			$choose .= '<div class="vbo-customf-sel-added"><input type="text" name="choose[]" value="' . JHtml::_('esc_attr', $y) . '" size="40"/></div>'."\n";
		}
	}
}

?>
<script type="text/javascript">
function setCustomfChoose(val) {
	if (val == "text") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbflag').style.display = 'flex';
		document.getElementById('vbdefvalue').style.display = 'none';
	}
	if (val == "textarea" || val == "checkbox" || val == "date" || val == "separator" || val == "state") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbflag').style.display = 'none';
		document.getElementById('vbdefvalue').style.display = 'none';
	}
	if (val == "select") {
		document.getElementById('customfchoose').style.display = 'block';
		document.getElementById('vbflag').style.display = 'none';
		document.getElementById('vbdefvalue').style.display = 'none';
	}
	if (val == "country") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbflag').style.display = 'none';
		document.getElementById('vbdefvalue').style.display = 'flex';
	}
	return true;
}

function addElement() {
	var ni = document.getElementById('customfchooseadd');
	var numi = document.getElementById('theValue');
	var num = (document.getElementById('theValue').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<div class=\'vbo-customf-sel-added\'><input type=\'text\' name=\'choose[]\' value=\'\' size=\'40\'/></div>';
	ni.appendChild(newdiv);
}
</script>
<input type="hidden" value="0" id="theValue" />

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOADMINLEGENDDETAILS'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCUSTOMFONE'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="name" value="<?php echo count($field) ? JHtml::_('esc_attr', $field['name']) : ''; ?>" size="40"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCUSTOMFTWO'); ?></div>
							<div class="vbo-param-setting">
								<select id="stype" name="type" onchange="setCustomfChoose(this.value);">
									<option value="text"<?php echo (count($field) && $field['type'] == "text" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFTHREE'); ?></option>
									<option value="textarea"<?php echo (count($field) && $field['type'] == "textarea" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFTEN'); ?></option>
									<option value="select"<?php echo (count($field) && $field['type'] == "select" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFFOUR'); ?></option>
									<option value="checkbox"<?php echo (count($field) && $field['type'] == "checkbox" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFFIVE'); ?></option>
									<option value="date"<?php echo (count($field) && $field['type'] == "date" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFDATETYPE'); ?></option>
									<option value="country"<?php echo (count($field) && $field['type'] == "country" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFCOUNTRY'); ?></option>
									<option value="state"<?php echo (count($field) && $field['type'] == "state" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBO_STATE_PROVINCE'); ?></option>
									<option value="separator"<?php echo (count($field) && $field['type'] == "separator" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFSEPARATOR'); ?></option>
								</select>
								<div id="customfchoose" style="display: <?php echo (count($field) && $field['type'] == "select" ? "block" : "none"); ?>;">
									<?php
									if ((count($field) && $field['type'] != "select") || !count($field)) {
									?>
									<div class="vbo-customf-sel-added"><input type="text" name="choose[]" value="" size="40"/></div>
									<?php
									} else {
										echo $choose;
									}
									?>
									<div id="customfchooseadd" style="display: block;"></div>
									<span><b><a href="javascript: void(0);" onclick="javascript: addElement();"><?php echo JText::_('VBNEWCUSTOMFNINE'); ?></a></b></span>
								</div>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCUSTOMFSIX'); ?></div>
							<div class="vbo-param-setting">
								<?php echo $vbo_app->printYesNoButtons('required', JText::_('VBYES'), JText::_('VBNO'), (count($field) && intval($field['required']) == 1 ? 1 : 0), 1, 0); ?>
							</div>
						</div>
						<div class="vbo-param-container" id="vbdefvalue"<?php echo (!count($field) || (count($field) && $field['type'] != "country") ? " style=\"display: none;\"" : ""); ?>>
							<div class="vbo-param-label"><?php echo JText::_('VBO_DEF_VALUE'); ?></div>
							<div class="vbo-param-setting">
								<input type="text" name="defvalue" value="<?php echo count($field) ? $this->escape($field['defvalue']) : ''; ?>" />
								<div class="vbo-param-setting-comment">
									<?php echo JText::_('VBO_COUNTRY_DEF_VALUE_HELP'); ?>
								</div>
							</div>
						</div>
						<div class="vbo-param-container" id="vbflag"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCUSTOMFFLAG'); ?> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWCUSTOMFFLAG'), 'content' => JText::_('VBNEWCUSTOMFFLAGHELP'))); ?></div>
							<div class="vbo-param-setting">
								<select name="flag">
									<option value=""></option>
									<option value="isemail"<?php echo (count($field) && intval($field['isemail']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFSEVEN'); ?></option>
									<option value="isnominative"<?php echo (count($field) && intval($field['isnominative']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBISNOMINATIVE'); ?></option>
									<option value="isphone"<?php echo (count($field) && intval($field['isphone']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBISPHONENUMBER'); ?></option>
									<option value="isaddress"<?php echo (count($field) && stripos($field['flag'], 'address') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBISADDRESS'); ?></option>
									<option value="iscity"<?php echo (count($field) && stripos($field['flag'], 'city') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBISCITY'); ?></option>
									<option value="iszip"<?php echo (count($field) && stripos($field['flag'], 'zip') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBISZIP'); ?></option>
									<option value="iscompany"<?php echo (count($field) && stripos($field['flag'], 'company') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBISCOMPANY'); ?></option>
									<option value="isvat"<?php echo (count($field) && stripos($field['flag'], 'vat') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBISVAT'); ?></option>
									<option value="isfisccode"<?php echo (count($field) && stripos($field['flag'], 'fisccode') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCUSTOMERFISCCODE'); ?></option>
									<option value="ispec"<?php echo (count($field) && stripos($field['flag'], 'pec') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCUSTOMERPEC'); ?></option>
									<option value="isrecipcode"<?php echo (count($field) && stripos($field['flag'], 'recipcode') !== false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCUSTOMERRECIPCODE'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBNEWCUSTOMFEIGHT'); ?></div>
							<div class="vbo-param-setting">
								<input type="text" name="poplink" value="<?php echo count($field) ? JHtml::_('esc_attr', $field['poplink']) : ''; ?>" size="40"/>
								<br/>
							<?php
							if (VBOPlatformDetection::isWordPress()) {
								?>
								<small>Eg. <i><?php echo get_site_url(); ?>/link-to-your-terms-page</i></small>
								<?php
							} else {
								?>
								<small>Ex. <i>index.php?option=com_content&amp;view=article&amp;id=#JoomlaArticleID#&amp;tmpl=component</i></small>
								<?php
							}
							?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($field)) :
?>
	<input type="hidden" name="where" value="<?php echo $field['id']; ?>">
<?php
endif;
?>
	<?php echo JHtml::_('form.token'); ?>
</form>
