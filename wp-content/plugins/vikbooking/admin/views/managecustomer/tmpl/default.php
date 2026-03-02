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

$customer = $this->customer;

$df = VikBooking::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}

$vbo_app = VikBooking::getVboApplication();
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
$document->addStyleSheet(VBO_ADMIN_URI.'resources/js_upload/colorpicker.css');
JHtml::_('script', VBO_SITE_URI.'resources/jquery.fancybox.js');
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/colorpicker.js');
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/eye.js');
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/utils.js');

$ptmpl = VikRequest::getString('tmpl', '', 'request');
$pcheckin = VikRequest::getInt('checkin', '', 'request');
$pgoto = VikRequest::getString('goto', '', 'request', VIKREQUEST_ALLOWRAW);
$pbid = VikRequest::getInt('bid', '', 'request');

JText::script('VBOSNAPCAMNOTALLOWED');
JText::script('VBOTAKESNAPCAMLOADING');
JText::script('VBOTAKESNAPCAMTAKEIT');

if (!empty($pcheckin) && !empty($pbid)) {
	?>
<div class="vbo-center">
	<button type="button" class="btn btn-success btn-large" onclick="document.getElementById('adminForm').submit();"><i class="vboicn-checkmark"></i> <?php echo JText::_('VBSAVE'); ?></button>
</div>
	<?php
}
?>
<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="vbo-admin-container">
		<div class="vbo-config-maintab-left">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOCUSTOMERDETAILS'); ?></legend>
					<div class="vbo-params-container">
					<?php
					if (!empty($customer['pic'])) {
						$avatar_caption = ltrim($customer['first_name'] . ' ' . $customer['last_name']);
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label">
								<div class="vbo-customer-info-box">
									<div class="vbo-customer-info-box-avatar vbo-customer-avatar-medium">
										<span>
											<img src="<?php echo strpos($customer['pic'], 'http') === 0 ? $customer['pic'] : VBO_SITE_URI . 'resources/uploads/' . $customer['pic']; ?>" data-caption="<?php echo htmlspecialchars($avatar_caption); ?>" />
										</span>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					if ($customer) {
						// count total bookings
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERTOTBOOKINGS'); ?></div>
							<div class="vbo-param-setting">
							<?php
							if ($customer['tot_bookings'] > 0) {
								?>
								<a href="index.php?option=com_vikbooking&task=orders&cust_id=<?php echo $customer['id']; ?>" class="btn"><span class="label label-success"><?php echo $customer['tot_bookings']; ?></span></a>
								<?php
							} else {
								?>
								<span class="label label-warning">0</span>
								<?php
							}
							?>
							</div>
						</div>
						<?php
					}
					?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERFIRSTNAME'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting"><input type="text" id="vbo_first_name" name="first_name" value="<?php echo $customer ? $customer['first_name'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERLASTNAME'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting"><input type="text" id="vbo_last_name" name="last_name" value="<?php echo $customer ? $customer['last_name'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMEREMAIL'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting"><input type="text" id="vbo_email" name="email" value="<?php echo $customer ? $customer['email'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERPHONE'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printPhoneInputField(array('name' => 'phone', 'id' => 'vbo-phone', 'value' => ($customer ? $customer['phone'] : ''))); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_CUSTOMER_PROF_PIC'); ?></div>
							<div class="vbo-param-setting">
								<div class="input-append">
									<input type="text" name="pic" value="<?php echo $customer ? $customer['pic'] : ''; ?>" size="30"/>
									<button type="button" class="btn btn-primary vbo-trig-upd-pic"><?php VikBookingIcons::e('upload'); ?><span></span></button>
								</div>
							<?php
							if (!empty($customer['pic'])) {
								?>
								<div class="vbo-cur-idscan">
									<i class="vboicn-eye"></i><a href="<?php echo strpos($customer['pic'], 'http') === 0 ? $customer['pic'] : VBO_SITE_URI . 'resources/uploads/' . $customer['pic']; ?>" target="_blank"><?php echo $customer['pic']; ?></a>
								</div>
								<?php
							}
							?>
								<input type="file" name="picimg" id="picimg" style="display: none;" />
								<span class="vbo-param-setting-comment"><?php echo JText::_('VBO_CUSTOMER_PROF_PIC_HELP'); ?></span>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERCOMPANY'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="company" value="<?php echo $customer ? $customer['company'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERCOMPANYVAT'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="vat" value="<?php echo $customer ? $customer['vat'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERFISCCODE'); ?> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBCUSTOMERFISCCODE'), 'content' => JText::_('VBCUSTOMERFISCCODEHELP'))); ?></div>
							<div class="vbo-param-setting"><input type="text" name="fisccode" value="<?php echo $customer ? $customer['fisccode'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERPEC'); ?> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBCUSTOMERPEC'), 'content' => JText::_('VBCUSTOMERPECHELP'))); ?></div>
							<div class="vbo-param-setting"><input type="text" name="pec" value="<?php echo $customer ? $customer['pec'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERRECIPCODE'); ?> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBCUSTOMERRECIPCODE'), 'content' => JText::_('VBCUSTOMERRECIPCODEHELP'))); ?></div>
							<div class="vbo-param-setting"><input type="text" name="recipcode" value="<?php echo $customer ? $customer['recipcode'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERCOUNTRY'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting">
								<select name="country">
									<option value="" data-c2code=""></option>
								<?php
								foreach ($this->countries as $country) {
									?>
									<option data-c2code="<?php echo $country['country_2_code']; ?>" value="<?php echo $country['country_3_code']; ?>"<?php echo $customer && $customer['country'] == $country['country_3_code'] ? ' selected="selected"' : ''; ?>><?php echo $country['country_name']; ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBO_STATE_PROVINCE'); ?></div>
							<div class="vbo-param-setting">
								<select name="state" data-stateset="<?php echo $customer ? $customer['state'] : ''; ?>">
									<option value="">-----</option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERCITY'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="city" value="<?php echo $customer ? $customer['city'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERADDRESS'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="address" value="<?php echo $customer ? $customer['address'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERZIP'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="zip" value="<?php echo $customer ? $customer['zip'] : ''; ?>" size="6"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label<?php echo !empty($pcheckin) && !empty($pbid) && empty($customer['gender']) ? ' vbo-config-param-cell-warn' : ''; ?>"><?php echo JText::_('VBCUSTOMERGENDER'); ?></div>
							<div class="vbo-param-setting">
								<select name="gender">
									<option value=""></option>
									<option value="M"<?php echo $customer && $customer['gender'] == 'M' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCUSTOMERGENDERM'); ?></option>
									<option value="F"<?php echo $customer && $customer['gender'] == 'F' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCUSTOMERGENDERF'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label<?php echo $customer && !empty($pcheckin) && !empty($pbid) && empty($customer['bdate']) ? ' vbo-config-param-cell-warn' : ''; ?>"><?php echo JText::_('VBCUSTOMERBDATE'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->getCalendar('', 'bdate', 'bdate', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label<?php echo $customer && !empty($pcheckin) && !empty($pbid) && empty($customer['pbirth']) ? ' vbo-config-param-cell-warn' : ''; ?>"><?php echo JText::_('VBCUSTOMERPBIRTH'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="pbirth" value="<?php echo $customer ? $customer['pbirth'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERDOCTYPE'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="doctype" value="<?php echo $customer ? $customer['doctype'] : ''; ?>" size="30"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERDOCNUM'); ?></div>
							<div class="vbo-param-setting"><input type="text" name="docnum" value="<?php echo $customer ? $customer['docnum'] : ''; ?>" size="15"/></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label">
								<?php echo JText::_('VBCUSTOMERDOCIMG'); ?>
								<div>
									<button type="button" class="btn vbo-config-btn" id="vbo-camstart">
										<i class="<?php echo VikBookingIcons::i('camera'); ?>" style="float: none;"></i> <?php echo JText::_('VBOTAKESNAPCAM'); ?>
									</button>
								</div>
							</div>
							<div class="vbo-param-setting">
								<input type="file" name="docimg" id="docimg" size="30" />
								<input type="hidden" name="scandocimg" id="scandocimg" value="" />
							<?php
							if (!empty($customer['docimg'])) {
								?>
								<div class="vbo-cur-idscan">
									<i class="vboicn-eye"></i><a href="<?php echo VBO_ADMIN_URI.'resources/idscans/'.$customer['docimg']; ?>" target="_blank"><?php echo $customer['docimg']; ?></a>
								</div>
								<?php
							}
							?>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERPIN'); ?></div>
							<div class="vbo-param-setting">
								<input type="text" name="pin" id="pin" value="<?php echo $customer ? $customer['pin'] : ''; ?>" size="8" placeholder="ABC54321" />
								&nbsp;&nbsp;
								<button type="button" class="btn vbo-config-btn" onclick="vboGeneratePin(8);" style="vertical-align: top;"><?php echo JText::_('VBCUSTOMERGENERATEPIN'); ?></button>
							</div>
						</div>
						<?php
						if (VBOPlatformDetection::isWordPress()) {
							?>
						<!-- @wponly the user label is called statically 'Website User' -->
						<div class="vbo-param-container">
							<div class="vbo-param-label">Website User</div>
							<div class="vbo-param-setting"><?php echo JHtml::_('list.users', 'ujid', (count($customer) ? $customer['ujid'] : ''), 1); ?></div>
						</div>
							<?php
						} else {
							?>
						<!-- @joomlaonly the list of users and label is loaded differently than in WP -->
						<div class="vbo-param-container">
							<div class="vbo-param-label">Joomla User</div>
							<div class="vbo-param-setting">
								<?php
								// JHtmlList::users(string $name, string $active, integer $nouser, string $javascript = null, string $order = 'name')
								if (!class_exists('JHtmlList')) {
									jimport( 'joomla.html.html.list' );
								}
								echo JHtmlList::users('ujid', (count($customer) ? $customer['ujid'] : ''), 1);
								?>
							</div>
						</div>
							<?php
						}
						?>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBCUSTOMERNOTES'); ?></div>
							<div class="vbo-param-setting"><textarea cols="80" rows="5" name="notes" style="width: 90%; height: 130px; resize: vertical;"><?php echo $customer ? htmlspecialchars((string) $customer['notes']) : ''; ?></textarea></div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

	<?php
	$customerch_params = array();
	if (!empty($customer['chdata'])) {
		$customerch_params = json_decode($customer['chdata'], true);
		$customerch_params = is_array($customerch_params) ? $customerch_params : array();
	}
	?>
		<div class="vbo-config-maintab-right">
			<fieldset class="adminform">
				<div class="vbo-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VBOCUSTOMERSALESCHANNEL'); ?></legend>
					<div class="vbo-params-container">
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOCUSTOMERISCHANNEL'); ?></div>
							<div class="vbo-param-setting"><?php echo $vbo_app->printYesNoButtons('ischannel', JText::_('VBYES'), JText::_('VBNO'), ($customer && intval($customer['ischannel']) == 1 ? 1 : 0), 1, 0); ?></div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOCUSTOMERCOMMISSION'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting"><input type="number" name="commission" step="any" value="<?php echo array_key_exists('commission', $customerch_params) ? $customerch_params['commission'] : ''; ?>" placeholder="0.00"/> %</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOCUSTOMERCMMSON'); ?></div>
							<div class="vbo-param-setting">
								<select name="calccmmon">
									<option value="0"<?php echo array_key_exists('calccmmon', $customerch_params) && intval($customerch_params['calccmmon']) < 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCUSTOMERCMMSONTOTAL'); ?></option>
									<option value="1"<?php echo array_key_exists('calccmmon', $customerch_params) && intval($customerch_params['calccmmon']) > 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCUSTOMERCMMSONRRATES'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOCUSTOMERCMMSONTAX'); ?></div>
							<div class="vbo-param-setting">
								<select name="applycmmon">
									<option value="0"<?php echo array_key_exists('applycmmon', $customerch_params) && intval($customerch_params['applycmmon']) < 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCUSTOMERCMMSONTAXINCL'); ?></option>
									<option value="1"<?php echo array_key_exists('applycmmon', $customerch_params) && intval($customerch_params['applycmmon']) > 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCUSTOMERCMMSONTAXEXCL'); ?></option>
								</select>
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOCUSTOMERCMMSNAME'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting">
								<input type="text" name="chname" value="<?php echo array_key_exists('chname', $customerch_params) && !empty($customerch_params['chname']) ? $customerch_params['chname'] : ($customer && intval($customer['ischannel']) > 0 ? str_replace(' ', '-', trim($customer['first_name'].' '.$customer['last_name'])) : ''); ?>" size="30" />
							</div>
						</div>
						<div class="vbo-param-container">
							<div class="vbo-param-label"><?php echo JText::_('VBOCUSTOMERCMMSCOLOR'); ?> <sup>*</sup></div>
							<div class="vbo-param-setting">
								<div class="vbo-colortag-square" style="background-color: <?php echo array_key_exists('chcolor', $customerch_params) && !empty($customerch_params['chcolor']) ? $customerch_params['chcolor'] : ($customer && intval($customer['ischannel']) > 0 ? '#000000' : '#ffffff'); ?>"></div>
								<input type="hidden" name="chcolor" class="chcolor" value="<?php echo array_key_exists('chcolor', $customerch_params) && !empty($customerch_params['chcolor']) ? $customerch_params['chcolor'] : ($customer && intval($customer['ischannel']) > 0 ? '#000000' : '#ffffff'); ?>" />
							</div>
						</div>
					</div>
				</div>
			</fieldset>

			<?php
			if (!empty($customer['id'])) {
				// display dropfiles area only for existing users
				echo $this->loadTemplate('dropfiles');
			}
			?>

		</div>

	</div>

	<div class="vbo-info-overlay-block">
		<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
		<div class="vbo-info-overlay-snapshot">
			<h3 style="text-align: center;"><i class="vboicn-camera"></i><?php echo JText::_('VBOTAKESNAPCAM'); ?></h3>
			<div class="vbo-info-overlay-snapshot-controls">
				<button type="button" class="btn vbo-config-btn" onclick="vboStartStreaming();"><?php VikBookingIcons::e('cog'); ?> <?php echo JText::_('VBOTAKESNAPCAMCONFIGURE'); ?></button>
				<button type="button" class="btn btn-success" onclick="vboTakeSnapshot();" id="vbo-takesnbtn"><?php VikBookingIcons::e('camera'); ?> <?php echo JText::_('VBOTAKESNAPCAMTAKEIT'); ?></button>
			</div>
			<div class="vbo-info-overlay-snapshot-movie">
				<video id="vbo-video-stream" width="800" height="600" style="display: none;"></video>
				<canvas id="vbo-canvas-capture" width="800" height="600" style="display: none;"></canvas>
				<div id="vbo-snapshot" style="display: none;"></div>
			</div>
		</div>
	</div>

<?php
if ($ptmpl == 'component') {
	?>
	<input type="hidden" name="tmpl" value="<?php echo $ptmpl; ?>">
	<?php
}
if (!empty($pcheckin) && !empty($pbid)) {
	?>
	<input type="hidden" name="checkin" value="<?php echo $pcheckin; ?>">
	<?php
}
if (!empty($pgoto)) {
	?>
	<input type="hidden" name="goto" value="<?php echo $pgoto; ?>">
	<?php
}
if (!empty($pbid)) {
	?>
	<input type="hidden" name="bid" value="<?php echo $pbid; ?>">
	<?php
}
if (count($customer)) {
	?>
	<input type="hidden" name="where" value="<?php echo $customer['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="task" value="<?php echo $customer ? 'updatecustomer' : 'savecustomer'; ?>">
	<input type="hidden" name="option" value="com_vikbooking">
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/Javascript">
var vbo_scan_base = '<?php echo VBO_ADMIN_URI.'resources/idscans/'; ?>';

Joomla.submitbutton = function(task) {
	if (task == 'savecustomer' || task == 'updatecustomer' || task == 'updatecustomerstay') {
		// make sure first name, last name and email fields are not empty
		if (!document.getElementById('vbo_first_name').value.length || !document.getElementById('vbo_last_name').value.length || !document.getElementById('vbo_email').value.length) {
			alert('<?php echo addslashes(JText::_('VBCUSTOMERMISSIMPDET')); ?>');
			return false;
		}
	}
	Joomla.submitform(task, document.adminForm);
}

function vboGetRandomValue(min, max) {
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

function vboGeneratePin(length) {
	// pin length
	length = parseInt(length);
	if (length < 1) {
		length = 8;
	}

	let code = '';

	const map = [
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'0123456789'
	];

	// iterate until the specified length is reached
	for (let i = code.length; i < length; i++) {
		// toss tokens block
		let _row = vboGetRandomValue(0, map.length - 1);
		// toss block character
		let _col = vboGetRandomValue(0, map[_row].length - 1);

		// append character to serial code
		code += map[_row][_col];
	}

	// populate field value
	document.getElementById('pin').value = code;
}

var vbo_overlay_on = false,
	vboCameraStream = null,
	vboVideoStream = document.getElementById('vbo-video-stream') || null,
	vboCanvasCapture = document.getElementById('vbo-canvas-capture') || null,
	vboSnapshot = document.getElementById('vbo-snapshot') || null;

function vboStopStreaming() {
	if (vboCameraStream !== null) {
		// streaming is active, deactivate it
		var track = vboCameraStream.getTracks()[0];
		track.stop();
		vboVideoStream.load();
		vboCameraStream = null;
	}
	// hide the video
	jQuery('#vbo-video-stream').hide();
}

function vboStartStreaming() {
	var mediaSupport = 'mediaDevices' in navigator;
	
	if (mediaSupport && vboCameraStream !== null) {
		// streaming is active, deactivate it
		vboStopStreaming();

		// do not proceed
		return;
	}

	if (mediaSupport && vboCameraStream === null) {
		navigator.mediaDevices.getUserMedia({
			video: true
		}).then(function(mediaStream) {
			// hide previous snapshot, if any
			jQuery('#vbo-snapshot').hide();
			// show the video
			jQuery('#vbo-video-stream').show();
			// assign streaming vars
			vboCameraStream = mediaStream;
			vboVideoStream.srcObject = mediaStream;
			vboVideoStream.play();
		}).catch(function(err) {
			alert(Joomla.JText._('VBOSNAPCAMNOTALLOWED'));
			console.log("Unable to access camera: " + err);
		});
	} else {
		alert('Your browser does not support this function, or HTTPS not available.');
		// log the navigator object just to see what's in it
		console.log(navigator);
	}
}

function vboDataURItoBlob(dataURI) {
	var byteString = atob(dataURI.split(',')[1]);
	var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
	
	var buffer	= new ArrayBuffer(byteString.length);
	var data	= new DataView(buffer);
	
	for (var i = 0; i < byteString.length; i++) {
		data.setUint8(i, byteString.charCodeAt(i));
	}
	
	return new Blob(
		[buffer],
		{
			type: mimeString
		}
	);
}

function vboTakeSnapshot() {
	if (vboCameraStream === null) {
		// no permissions or no feature available
		alert(Joomla.JText._('VBOSNAPCAMNOTALLOWED'));
		return;
	}

	// add temporary loading message
	jQuery("#vbo-takesnbtn").prop("disabled", true).html('<?php VikBookingIcons::e('refresh', 'fa-spin fa-fw'); ?> ' + Joomla.JText._('VBOTAKESNAPCAMLOADING'));

	// capture the snapshot
	var ctx = vboCanvasCapture.getContext('2d');
	var img = new Image();
	ctx.drawImage(vboVideoStream, 0, 0, vboCanvasCapture.width, vboCanvasCapture.height);
	img.src = vboCanvasCapture.toDataURL("image/png");
	img.width = 800;
	vboSnapshot.innerHTML = '';
	vboSnapshot.appendChild(img);

	// disable and hide the video
	vboStopStreaming();

	// show the snapshot
	vboSnapshot.style.display = 'block';

	// upload the file
	var fdata = new FormData();
	var imageData = vboDataURItoBlob(img.src);
	fdata.append("snapshot", imageData, "snapshot.png");
	jQuery.ajax({
		type: 'POST',
		url: 'index.php?option=com_vikbooking&task=uploadsnapshot',
		data: fdata,
		processData: false,
		contentType: false
	}).done(function(resp) {
		vboSnapshotComplete(resp);
	}).fail(function(err) {
		alert('Could not upload snapshot');
		console.log('Request failed', err);
	});
}

function vboSnapshotComplete(res) {
	jQuery("#vbo-takesnbtn").prop("disabled", false).html('<?php VikBookingIcons::e('camera'); ?> ' + Joomla.JText._('VBOTAKESNAPCAMTAKEIT'));
	if (res.indexOf('e4j.error') >= 0 ) {
		console.log(res);
		alert(res.replace("e4j.error.", ""));
	} else {
		jQuery("#docimg").hide();
		jQuery("#scandocimg").val(res);
		if (jQuery(".vbo-cur-idscan").length) {
			jQuery(".vbo-cur-idscan").find("a").replaceWith("<a href=\""+vbo_scan_base+res+"\" target=\"_blank\">"+res+"</a>");
		} else {
			jQuery("#scandocimg").after("<div class=\"vbo-cur-idscan\"><i class=\"vboicn-eye\"></i><a href=\""+vbo_scan_base+res+"\" target=\"_blank\">"+res+"</a></div>");
		}
		
		jQuery(".vbo-info-overlay-block").fadeOut();
		vbo_overlay_on = false;
	}
}

function vboReloadStates(country_3_code) {
	var states_elem = jQuery('select[name="state"]');

	// get the current state, if any
	var current_state = states_elem.attr('data-stateset');

	// unset the current states/provinces
	states_elem.html('');

	if (!country_3_code || !country_3_code.length) {
		return;
	}

	// make a request to load the states/provinces of the selected country
	VBOCore.doAjax(
		"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=states.load_from_country'); ?>",
		{
			country_3_code: country_3_code,
			tmpl: "component"
		},
		function(response) {
			try {
				var obj_res = typeof response === 'string' ? JSON.parse(response) : response;
				if (!obj_res) {
					console.error('Unexpected JSON response', obj_res);
					return false;
				}

				// append empty value
				states_elem.append('<option value="">-----</option>');

				for (var i = 0; i < obj_res.length; i++) {
					// append state
					states_elem.append('<option value="' + obj_res[i]['state_2_code'] + '">' + obj_res[i]['state_name'] + '</option>');
				}

				if (current_state.length && states_elem.find('option[value="' + current_state + '"]').length) {
					// set the current value
					states_elem.val(current_state);
				}
			} catch(err) {
				console.error('could not parse JSON response', err, response);
			}
		},
		function(error) {
			console.error(error);
		}
	);
}

jQuery(function() {

	if (!vboVideoStream || !vboCanvasCapture || !vboSnapshot) {
		vboVideoStream = document.getElementById('vbo-video-stream');
		vboCanvasCapture = document.getElementById('vbo-canvas-capture');
		vboSnapshot = document.getElementById('vbo-snapshot');
	}

	jQuery(document).mouseup(function(e) {
		if (!vbo_overlay_on) {
			return;
		}
		var vbo_overlay_cont = jQuery(".vbo-info-overlay-snapshot");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			jQuery(".vbo-info-overlay-block").fadeOut();
			vbo_overlay_on = false;
		}
	});

	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vbo_overlay_on) {
			jQuery(".vbo-info-overlay-block").fadeOut();
			vbo_overlay_on = false;
		}
	});

	jQuery("#vbo-camstart").click(function() {
		// display modal
		jQuery(".vbo-info-overlay-block").fadeIn();
		vbo_overlay_on = true;
		// start streaming
		vboStartStreaming();
	});

	jQuery(document.body).on("click", ".vbo-cur-idscan a", function(e) {
		var imgsrc = jQuery(this).attr("href");
		if (!imgsrc.match(/\.(pdf|zip)$/)) {
			e.preventDefault();
			console.log(imgsrc);
			jQuery.fancybox({
				"helpers": {
					"overlay": {
						"locked": false
					}
				},
				"href": imgsrc,
				"autoScale": false,
				"transitionIn": "none",
				"transitionOut": "none",
				"padding": 0,
				"type": "image"
			});
		}
		// open PDF and ZIP in a new tab of the browser
	});

	jQuery('.vbo-colortag-square').ColorPicker({
		color: '#ffffff',
		onShow: function (colpkr, el) {
			var cur_color = jQuery(el).css('backgroundColor');
			jQuery(el).ColorPickerSetColor(vboRgb2Hex(cur_color));
			jQuery(colpkr).show();
			return false;
		},
		onChange: function (hsb, hex, rgb, el) {
			jQuery(el).css('backgroundColor', '#'+hex);
			jQuery(el).parent().find('.chcolor').val('#'+hex);
		},
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).css('backgroundColor', '#'+hex);
			jQuery(el).parent().find('.chcolor').val('#'+hex);
			jQuery(el).ColorPickerHide();
		},
	});

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

	// country selection
	jQuery('select[name="country"]').on('change', function() {
		// trigger event for phone number
		jQuery('#vbo-phone').trigger('vboupdatephonenumber', jQuery(this).find('option:selected').attr('data-c2code'));
		// reload state/province
		vboReloadStates(jQuery(this).val());
	});

<?php
if (!empty($customer['bdate'])) {
	?>
	jQuery("#bdate").val("<?php echo $customer['bdate']; ?>").attr('data-alt-value', "<?php echo $customer['bdate']; ?>");
	<?php
}
if (!empty($customer['country'])) {
	?>
	setTimeout(() => {
		jQuery('select[name="country"]').trigger('change');
	}, 200);
	<?php
}
if (!empty($customer['phone']) && VBOPlatformDetection::isWordPress() && wp_doing_ajax()) {
	?>
	setTimeout(() => {
		jQuery('#vbo-phone').val('<?php echo $customer['phone']; ?>').trigger('change');
	}, 200);
	<?php
}
?>
});

var vboHexDigits = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");

function vboRgb2Hex(rgb) {
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	return "#" + vboHex(rgb[1]) + vboHex(rgb[2]) + vboHex(rgb[3]);
}

function vboHex(x) {
	return isNaN(x) ? "00" : vboHexDigits[(x - x % 16) / 16] + vboHexDigits[x % 16];
}
</script>
