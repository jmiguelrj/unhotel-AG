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

/**
 * @joomlaonly  we access the application class and normalize the back-end styles
 * (useful for the Yes/No buttons that have different styles depending on the Joomla series version)
 */
if (VCMPlatformDetection::isJoomla() && method_exists('VikBooking', 'getVboApplication')) {
	echo VikBooking::getVboApplication()->normalizeBackendStyles();
}

JHTML::_('behavior.tooltip');
$vcm_app = new VikApplication(VersionListener::getID());

// lang def for JS
JText::script('VCMCONFIGIMPORTWARNTIME');
JText::script('VCM_ASK_CONTINUE');
JText::script('VCMRESLOGSIDORDOTA');
JText::script('VCMEXPEDIAUNAMEWARN');
JText::script('VCMEXPEDIASHORTUNAMEWARN');
JText::script('VCM_ACCOUNT_ROOMS_MAPPED');
JText::script('VCM_ACCOUNT_NO_MAPPED_ROOMS_WARN');
JText::script('VCMMENUEXPSYNCH');
JText::script('VCM_NEW_ACCOUNT_SAVE_WARN');
JText::script('VCMUPDATEENDPURLCONF');
JText::script('VCMREMOVECONFIRM');
JText::script('VCMREMOVEACCOUNTCONF');
JText::script('MSG_BASE_SUCCESS');
JText::script('VCM_THIS_BROWSER');
JText::script('VCMAPIKEY');
JText::script('VCM_COPY');
JText::script('VCM_COPIED');

$config = $this->config;
$module = $this->module;
$app_accounts = $this->app_accounts;
$decoded_accounts = !empty($app_accounts['setting']) ? json_decode($app_accounts['setting'], true) : array();
$decoded_accounts = !is_array($decoded_accounts) ? array() : $decoded_accounts;

// the currently active hotel ID setting (if supported)
$vcm_current_hotel_id = '';

/**
 * After the loading of all the necessary dependencies,
 * we try to access the VBO application class.
 * 
 * @since 	1.8.3
 */
$vbo_app = VikChannelManager::getVboApplication();

// load brands icons
VikBookingIcons::loadRemoteAssets();

$vb_params = array(
	"currencysymb" => '&euro;',
	"currencyname" => 'EUR',
	"emailadmin" => '',
	"dateformat" => '%Y/%m/%d',
);

$iso4217 = array(
	'AED: United Arab Emirates Dirham',
	'AFN: Afghan Afghani',
	'ALL: Albanian Lek',
	'AMD: Armenian Dram',
	'ANG: Netherlands Antillean Gulden',
	'AOA: Angolan Kwanza',
	'ARS: Argentine Peso',
	'AUD: Australian Dollar',
	'AWG: Aruban Florin',
	'AZN: Azerbaijani Manat',
	'BAM: Bosnia & Herzegovina Convertible Mark',
	'BBD: Barbadian Dollar',
	'BDT: Bangladeshi Taka',
	'BGN: Bulgarian Lev',
	'BIF: Burundian Franc',
	'BMD: Bermudian Dollar',
	'BND: Brunei Dollar',
	'BOB: Bolivian Boliviano',
	'BRL: Brazilian Real',
	'BSD: Bahamian Dollar',
	'BWP: Botswana Pula',
	'BYN: Belarusian ruble',
	'BZD: Belize Dollar',
	'CAD: Canadian Dollar',
	'CDF: Congolese Franc',
	'CHF: Swiss Franc',
	'CLP: Chilean Peso',
	'CNY: Chinese Renminbi Yuan',
	'COP: Colombian Peso',
	'CRC: Costa Rican Colón',
	'CVE: Cape Verdean Escudo',
	'CZK: Czech Koruna',
	'DJF: Djiboutian Franc',
	'DKK: Danish Krone',
	'DOP: Dominican Peso',
	'DZD: Algerian Dinar',
	'EEK: Estonian Kroon',
	'EGP: Egyptian Pound',
	'ETB: Ethiopian Birr',
	'EUR: Euro',
	'FJD: Fijian Dollar',
	'FKP: Falkland Islands Pound',
	'GBP: British Pound',
	'GEL: Georgian Lari',
	'GIP: Gibraltar Pound',
	'GMD: Gambian Dalasi',
	'GNF: Guinean Franc',
	'GTQ: Guatemalan Quetzal',
	'GYD: Guyanese Dollar',
	'HKD: Hong Kong Dollar',
	'HNL: Honduran Lempira',
	'HRK: Croatian Kuna',
	'HTG: Haitian Gourde',
	'HUF: Hungarian Forint',
	'IDR: Indonesian Rupiah',
	'ILS: Israeli New Sheqel',
	'INR: Indian Rupee',
	'ISK: Icelandic Króna',
	'JMD: Jamaican Dollar',
	'JPY: Japanese Yen',
	'KES: Kenyan Shilling',
	'KGS: Kyrgyzstani Som',
	'KHR: Cambodian Riel',
	'KMF: Comorian Franc',
	'KRW: South Korean Won',
	'KYD: Cayman Islands Dollar',
	'KZT: Kazakhstani Tenge',
	'LAK: Lao Kip',
	'LBP: Lebanese Pound',
	'LKR: Sri Lankan Rupee',
	'LRD: Liberian Dollar',
	'LSL: Lesotho Loti',
	'LTL: Lithuanian Litas',
	'LVL: Latvian Lats',
	'MAD: Moroccan Dirham',
	'MDL: Moldovan Leu',
	'MGA: Malagasy Ariary',
	'MKD: Macedonian Denar',
	'MNT: Mongolian Tögrög',
	'MOP: Macanese Pataca',
	'MRU: Mauritanian Ouguiya',
	'MUR: Mauritian Rupee',
	'MVR: Maldivian Rufiyaa',
	'MWK: Malawian Kwacha',
	'MXN: Mexican Peso',
	'MYR: Malaysian Ringgit',
	'MZN: Mozambican Metical',
	'NAD: Namibian Dollar',
	'NGN: Nigerian Naira',
	'NIO: Nicaraguan Córdoba',
	'NOK: Norwegian Krone',
	'NPR: Nepalese Rupee',
	'NZD: New Zealand Dollar',
	'PAB: Panamanian Balboa',
	'PEN: Peruvian Nuevo Sol',
	'PGK: Papua New Guinean Kina',
	'PHP: Philippine Peso',
	'PKR: Pakistani Rupee',
	'PLN: Polish Złoty',
	'PYG: Paraguayan Guaraní',
	'QAR: Qatari Riyal',
	'RON: Romanian Leu',
	'RSD: Serbian Dinar',
	'RUB: Russian Ruble',
	'RWF: Rwandan Franc',
	'SAR: Saudi Riyal',
	'SBD: Solomon Islands Dollar',
	'SCR: Seychellois Rupee',
	'SEK: Swedish Krona',
	'SGD: Singapore Dollar',
	'SHP: Saint Helenian Pound',
	'SLL: Sierra Leonean Leone',
	'SOS: Somali Shilling',
	'SRD: Surinamese Dollar',
	'STD: São Tomé and Príncipe Dobra',
	'SVC: Salvadoran Colón',
	'SZL: Swazi Lilangeni',
	'THB: Thai Baht',
	'TJS: Tajikistani Somoni',
	'TOP: Tongan Paʻanga',
	'TRY: Turkish Lira',
	'TTD: Trinidad and Tobago Dollar',
	'TWD: New Taiwan Dollar',
	'TZS: Tanzanian Shilling',
	'UAH: Ukrainian Hryvnia',
	'UGX: Ugandan Shilling',
	'USD: United States Dollar',
	'UYU: Uruguayan Peso',
	'UZS: Uzbekistani Som',
	'VEF: Venezuelan Bolívar',
	'VND: Vietnamese Đồng',
	'VUV: Vanuatu Vatu',
	'WST: Samoan Tala',
	'XAF: Central African Cfa Franc',
	'XCD: East Caribbean Dollar',
	'XOF: West African Cfa Franc',
	'XPF: Cfp Franc',
	'YER: Yemeni Rial',
	'ZAR: South African Rand',
	'ZMW: Zambian Kwacha'
);

if (class_exists('VikBooking')) {
	$vb_params['currencysymb'] = VikBooking::getCurrencySymb(true);
	$vb_params['currencyname'] = VikBooking::getCurrencyName(true);
	$vb_params['emailadmin'] = VikBooking::getAdminMail(true);
	$vb_params['dateformat'] = VikBooking::getDateFormat(true);
}

foreach ($vb_params as $k => $v) {
	if (empty($config[$k])) {
		$config[$k] = $v;
	}
}

$old_pub_val = -1;
$select_payments = '<select name="defaultpayment">';
$select_payments .= '<option value="">'.JText::_('VCMCONFDEFPAYMENTOPTNONE').'</option>';
foreach ($this->vb_payments as $k => $v) {
	if ($old_pub_val != $v['published']) {
		if ($k != 0) {
			$select_payments .= '</optgroup>';
		}
		$select_payments .= '<optgroup label="'.JText::_('VCMPAYMENTSTATUS'.$v['published']).'">';
		$old_pub_val = $v['published'];
	}
	$select_payments .= '<option value="'.$v['id'].'" '.($config['defaultpayment'] == $v['id'] ? 'selected="selected"' : '').'>'.$v['name'].'</option>';
}
$select_payments .= '</optgroup>';
$select_payments .= '</select>';

$known_langs = VikChannelManager::getKnownLanguages();
$deflang = VikChannelManager::getDefaultLanguage();
$select_langs = '<select name="defaultlang">';
$select_langs .= '<option value="">'.JText::_('VCMCONFDEFLANGOPTNONE').'</option>';
foreach ($known_langs as $ltag => $ldet) {
	$select_langs .= '<option value="'.$ltag.'"'.($ltag == $deflang ? ' selected="selected"' : '').'>'.$ldet['name'].'</option>';
}
$select_langs .= '</select>';

/**
 * For reasons related to the contract between e4jConnect and Airbnb, all
 * iCal calendars that belong to Airbnb should be upgraded to the new API
 * version as soon as possible, to ensure the best service with this channel.
 * 
 * @since 	1.8.0
 */
$airbnb_status = VikChannelManager::hasDeprecatedAirbnbVersion();
if ($airbnb_status === true) {
	?>
<div class="err vcm-airbnb-upgrade-err">
	<p class="vcm-airbnb-upgrade-notice"><?php echo JText::_('AIRBNB_UPGNOTICE_ERR1'); ?> <span class="vcm-airbnb-upgrade-clickable" onclick="document.getElementsByClassName('vcm-airbnb-upgrade-info')[0].style.display='block';"><?php echo JText::_('AIRBNB_UPGNOTICE_FINDMORE'); ?></span>.</p>
	<div class="vcm-airbnb-upgrade-info" style="display: none;">
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_ERR2'); ?></p>
		<div class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-pointslist">
			<ol>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT1'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT2'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT3'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT4'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT5'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT6'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT7'); ?></li>
			</ol>
		</div>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_ERR3'); ?></p>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_ERR4'); ?></p>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-launch">
			<a href="index.php?option=com_vikchannelmanager&task=vcm_airbnb_upgrade" class="btn btn-primary"><?php echo JText::_('AIRBNB_UPGNOTICE_ACTIVATE'); ?></a>
		</p>
	</div>
</div>
	<?php
} elseif ($airbnb_status === -1) {
	?>
<div class="warn vcm-airbnb-upgrade-warn">
	<p class="vcm-airbnb-upgrade-notice"><?php echo JText::_('AIRBNB_UPGNOTICE_WARN1'); ?> <span class="vcm-airbnb-upgrade-clickable" onclick="document.getElementsByClassName('vcm-airbnb-upgrade-info')[0].style.display='block';"><?php echo JText::_('AIRBNB_UPGNOTICE_FINDMORE'); ?></span>.</p>
	<div class="vcm-airbnb-upgrade-info" style="display: none;">
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_WARN2'); ?></p>
		<div class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-pointslist">
			<ol>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT1'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT2'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT3'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT4'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT5'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT6'); ?></li>
				<li><?php echo JText::_('AIRBNB_UPGNOTICE_POINT7'); ?></li>
			</ol>
		</div>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_WARN3'); ?></p>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-inner"><?php echo JText::_('AIRBNB_UPGNOTICE_WARN4'); ?></p>
		<p class="vcm-airbnb-upgrade-notice vcm-airbnb-upgrade-notice-launch">
			<a href="index.php?option=com_vikchannelmanager&task=vcm_airbnb_upgrade" class="btn btn-warning"><?php echo JText::_('AIRBNB_UPGNOTICE_DEACTIVATE'); ?></a>
		</p>
	</div>
</div>
	<?php
}
//

if (count($this->more_accounts)) {
?>
<div class="vcm-info-overlay-block vcm-info-overlay-chaccounts">
	<div class="vcm-info-overlay-content">
		<h3><?php echo ucwords($module['name']).' - '.JText::_('VCMMANAGEACCOUNTS'); ?></h3>
		<table class="vcm-moreaccounts-table">
			<tr class="vcm-moreaccounts-firstrow">
				<td><?php echo JText::_('VCMMANAGEACCOUNTNAME'); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		<?php
		foreach ($this->more_accounts as $acck => $accv) {
			$acc_name = $accv['prop_name'];
			$acc_info = json_decode($accv['prop_params'], true);
			$acc_id = '';
			if (isset($acc_info['hotelid'])) {
				$acc_id = $acc_info['hotelid'];
			} elseif (isset($acc_info['id'])) {
				// useful for Pitchup.com to identify multiple accounts
				$acc_id = $acc_info['id'];
			} elseif (isset($acc_info['property_id'])) {
				// useful for Hostelworld
				$acc_id = $acc_info['property_id'];
			} elseif (isset($acc_info['user_id'])) {
				// useful for Airbnb API
				$acc_id = $acc_info['user_id'];
			}
			?>
			<tr class="vcm-moreaccounts-rows">
				<td><span<?php echo (!empty($acc_id) ? ' title="ID '.$acc_id.'"' : ''); ?> class="vcm-moreaccounts-listname"><?php echo (empty($acc_name) ? $acc_id : $acc_name); ?></span><span class="vcm-moreaccounts-numrooms"><?php echo JText::sprintf('VCMMANAGEACCOUNTNUMRS', $accv['tot_rooms']); ?></span></td>
				<td>
				<?php
				if ($accv['active'] != 1) {
					?>
					<button type="button" class="btn btn-primary" onclick="vcmCloseModal();setAccountParams('<?php echo $acck; ?>');"><?php echo JText::_('VCMSELECTACCOUNT'); ?></button>
					<?php
				} else {
					?>
					<button type="button" class="btn btn-success" onclick="vcmCloseModal();"><?php echo JText::_('VCMACTIVEACCOUNT'); ?></button>
					<?php
				}
				?>
				</td>
				<td>
				<?php
				if ($accv['active'] != 1) {
					?>
					<button type="button" class="btn btn-danger" onclick="vcmCloseModal();removeAccount('<?php echo $acck; ?>', '<?php echo $acc_id; ?>');"><?php echo JText::_('VCMREMOVEACCOUNT'); ?></button>
					<?php
				} else {
					?>&nbsp;<?php
				}
				?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
	</div>
</div>
<?php
}

if ($decoded_accounts) {
?>
<div class="vcm-info-overlay-block vcm-info-overlay-password">
	<div class="vcm-info-overlay-content">
		<form method="POST" action="index.php">
			<input type="hidden" name="option" value="com_vikchannelmanager"/>
			<input type="hidden" name="task" value="app_account_update"/>
			<input type="hidden" name="action" value="Update"/>
			<input type="hidden" name="email" class="vcm-info-overlay-password-email-value"/>
			<h3>
				<span class="vcm-info-overlay-password-email" class="vcm-info-overlay-pass-title-span"></span>
				<span>-</span>
				<span><?php echo JText::_('VCMAPPCHANGEPASS'); ?></span>
			</h3>
			<div class="vcm-info-overlay-pass-element">
				<label for="vcm-info-overlay-pass-pass" class="vcm-info-overlay-pass-desc"><?php echo JText::_('VCMAPPPASSWORD'); ?></label>
				<input id="vcm-info-overlay-pass-pass" type="password" name="pass" class="vcm-info-overlay-pass-input"/>
			</div>
			<div class="vcm-info-overlay-pass-element">
				<label for="vcm-info-overlay-pass-conf" class="vcm-info-overlay-pass-desc"><?php echo JText::_('VCMAPPCONFPASSWORD'); ?></label>
				<input id="vcm-info-overlay-pass-conf" type="password" name="pass-check" class="vcm-info-overlay-pass-confirm"/>
			</div>
			<div class="vcm-info-overlay-pass-error-div" style="display: none;">
				<span style="color: red;"><?php echo JText::_('VCMAPPPASSERR');?></span>
			</div>
			<div class="vcm-info-overlay-pass-element">
				<button type="submit" disabled class="btn btn-success vcm-info-overlay-pass-sub-button"><?php echo JText::_('VCMBCAHSUBMIT'); ?></button>
			</div>
			<h3><?php echo JText::_('VCM_APP_API_PWD'); ?></h3>
			<div class="vcm-info-overlay-pass-element">
				<div class="btn-group input-append" style="margin: 0;">
					<input type="hidden" class="vcm-info-overlay-password-app-pwd" value="" />
					<input type="text" class="vcm-info-overlay-password-app-pwd-show" value="" placeholder="<?php echo str_repeat('*', 32); ?>" readonly="readonly" style="min-width: 40%;" /><button type="button" class="btn btn-primary" onclick="vcmToggleAPIPwd();"><?php VikBookingIcons::e('eye'); ?></button>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
}

// check whether the last_endpoint is different than the current endpoint that could be submitted
if (!empty($this->last_endpoint)) {
	$endpoint_warning = '';
	$current_endpoint = JUri::root();
	$last_protocolpos = strpos($this->last_endpoint, ':');
	$cur_protocolpos = strpos($current_endpoint, ':');
	if ($last_protocolpos !== false && $cur_protocolpos !== false) {
		$last_basedom = substr($this->last_endpoint, ($last_protocolpos + 3));
		$current_basedom = substr($current_endpoint, ($cur_protocolpos + 3));
		if ($last_protocolpos != $cur_protocolpos) {
			//protocol has changed from HTTP to HTTPS or vice-versa
			$endpoint_warning = JText::sprintf('VCMENDPOINTWARNPROTOCOL', strtoupper(substr($this->last_endpoint, 0, $last_protocolpos)), strtoupper(substr($current_endpoint, 0, $cur_protocolpos)));
		} elseif ($last_basedom != $current_basedom) {
			//the base domain name has changed
			$endpoint_warning = JText::sprintf('VCMENDPOINTWARNDOMAIN', $this->last_endpoint, $current_endpoint);
		}
	}
	if (!empty($endpoint_warning)) {
	?>
	<div class="vcm-config-warning-endpoint">
		<span><?php echo $endpoint_warning; ?></span>
		<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=update_endpoints" onclick="return confirm(Joomla.JText._('VCMUPDATEENDPURLCONF'));"><?php echo JText::sprintf('VCMUPDATEENDPURL', $current_endpoint); ?></a>
	</div>
	<?php
	}
}
//
?>

<script type="text/javascript">
var vcm_shouldcheck_cred = <?php echo $this->module['uniquekey'] == VikChannelManagerConfig::EXPEDIA ? 'true' : 'false'; ?>;
var isbcom = <?php echo $this->module['uniquekey'] == VikChannelManagerConfig::BOOKING ? 'true' : 'false'; ?>;
var vcm_alertone = false;
function vcmTrimApi(inp) {
	if (inp.value.length) {
		inp.value = inp.value.trim();
	}
}
function vcmCopyApi(inp) {
	if (inp.readOnly) {
		return;
	}
	vcmTrimApi(inp);
	document.getElementById('vcm-hidden-apikey').value = inp.value;
}
function vcmToggleApiEdit() {
	var actual_input = document.getElementById('vcm-visible-apikey');
	if (actual_input.readOnly) {
		actual_input.value = document.getElementById('vcm-hidden-apikey').value;
		actual_input.readOnly = false;
		actual_input.setAttribute('data-revealed', 1);
	} else {
		actual_input.readOnly = true;
		if (actual_input.hasAttribute('data-revealed')) {
			// copy to clipboard
			try {
				VBOCore.copyToClipboard(actual_input).then((success) => {
					VBOToast.enqueue(new VBOToastMessage({
						title: Joomla.JText._('VCMAPIKEY'),
						body:  Joomla.JText._('VCM_COPIED') + '!',
						icon:  '<?php echo VikBookingIcons::i('key'); ?>',
						action: () => {
							VBOToast.dispose(true);
						},
					}));
				}).catch((err) => {
					// propagate the error
					throw err;
				});
			} catch(err) {
				console.error(err);
			}
		}
	}
}
function vcmTrimPwd(inp) {
	if (!vcm_shouldcheck_cred) {
		return;
	}
	if (inp.value.length) {
		inp.value = inp.value.trim();
	}
}
function vcmValidateParam(inp) {
	if (inp.name == 'hotelid') {
		// always trim the hotel ID param for all channels
		inp.value = inp.value.trim();
		if ((isbcom || vcm_shouldcheck_cred) && !vcm_alertone && isNaN(inp.value)) {
			vcm_alertone = true;
			alert('Your Hotel ID may be invalid as it must be a numeric value. Please double check it before saving.');
		}
	}
	if (!vcm_shouldcheck_cred) {
		return;
	}
	var hide_mess = false;
	if (inp.name == 'username') {
		var mess_elem = null;
		for (var i = 0; i < inp.parentElement.childNodes.length; i++) {
			if (inp.parentElement.childNodes[i].className == "vcm-param-validation-msg") {
				mess_elem = inp.parentElement.childNodes[i];
				break;
			}
		}
		if (inp.value.length > 2) {
			if (inp.value.substr(0, 3) != 'EQC') {
				if (mess_elem !== null) {
					mess_elem.innerText = Joomla.JText._('VCMEXPEDIAUNAMEWARN');
				}
			} else if (inp.value.substr(0, 3) == 'EQC' && inp.value.length < 8) {
				if (mess_elem !== null) {
					mess_elem.innerText = Joomla.JText._('VCMEXPEDIASHORTUNAMEWARN');
				}
			} else {
				hide_mess = true;
			}
		} else {
			hide_mess = true;
		}
		if (hide_mess && mess_elem !== null) {
			mess_elem.innerText = '';
		}
	}
}
</script>

<?php
if ($this->config_import > 0) {
	?>
<div class="vcm-bfirstsummary vcm-config-import">
	<h3><?php echo JText::_('VCMCONFIGIMPORTTITLE'); ?></h3>
	<p><?php echo JText::_('VCMCONFIGIMPORTDESC'); ?></p>
	<button type="button" class="btn btn-success" onclick="vcmDoConfigImport('1');"><i class="icon-download"></i> <?php echo JText::_('VCMCONFIGIMPORTOK'); ?></button>
	&nbsp;&nbsp;&nbsp;
	<button type="button" class="btn btn-danger" onclick="vcmDoConfigImport('0');"><i class="icon-cancel"></i> <?php echo JText::_('VCMCONFIGIMPORTKO'); ?></button>
</div>
<a id="vcm-hidden-link-ic" style="display: none;" href="index.php?option=com_vikchannelmanager&task=config_import"></a>
<script type="text/javascript">
function vcmDoConfigImport(act) {
	// this request may take a long time to complete, make sure to disabled the buttons if accepted
	if (act == '1') {
		if (confirm(Joomla.JText._('VCMCONFIGIMPORTWARNTIME'))) {
			jQuery('button.btn').prop('disabled', true);
			document.location.href = jQuery('#vcm-hidden-link-ic').attr('href') + '&imp=' + act;
		} else {
			return;
		}
	} else {
		document.location.href = jQuery('#vcm-hidden-link-ic').attr('href') + '&imp=' + act;
	}
	
}
</script>
	<?php
}

if ($this->first_summary > 0) {
	?>
<div class="vcm-bfirstsummary">
	<h3><?php echo JText::_('VCMFIRSTBSUMMTITLE'); ?></h3>
	<p><?php echo JText::_('VCMFIRSTBSUMMDESC'); ?></p>
	<button type="button" class="btn btn-success" onclick="vcmDoImport('1');"><i class="icon-download"></i> <?php echo JText::_('VCMFIRSTBSUMMOK'); ?></button>
	&nbsp;&nbsp;&nbsp;
	<button type="button" class="btn btn-danger" onclick="vcmDoImport('0');"><i class="icon-cancel"></i> <?php echo JText::_('VCMFIRSTBSUMMKO'); ?></button>
</div>
<a id="vcm-hidden-link-fs" style="display: none;" href="index.php?option=com_vikchannelmanager&task=first_summary"></a>
<script type="text/javascript">
function vcmDoImport(act) {
	document.location.href = jQuery('#vcm-hidden-link-fs').attr('href') + '&imp='+act;
}
</script>
	<?php
}
?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	<div class="vcm-admin-container">
		<div class="vcm-config-maintab-left">

			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend">
						<h3><?php VikBookingIcons::e('plug'); ?> <?php echo JText::_('VCM_CONNECTIVITY'); ?></h3>
					</legend>
					<div class="vcm-params-container">
					<?php if (VikChannelManager::isAvailabilityRequest()) { ?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMAUTOSYNC'); ?></div>
							<div class="vcm-param-setting">
							<?php
							if ($vbo_app && method_exists($vbo_app, 'printYesNoButtons')) {
								echo $vbo_app->printYesNoButtons('vikbookingsynch', JText::_('VCMAUTOSYNCON'), JText::_('VCMAUTOSYNCOFF'), (int)$config['vikbookingsynch'], 1, 0);
							} else {
								?>
								<input type="hidden" name="vikbookingsynch" value="<?php echo intval($config['vikbookingsynch']); ?>" />
								<?php
							}
							?>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCMAUTOSYNC_HELP'); ?></span>
							</div>
						</div>
					<?php } else { ?>
						<input type="hidden" name="vikbookingsynch" value="<?php echo intval($config['vikbookingsynch']); ?>" />
					<?php } ?>

						<?php
						$has_api_key = (!empty($config['apikey']));
						?>
						<div class="vcm-param-container vcm-param-apikey">
							<div class="vcm-param-label"><?php echo JText::_('VCMAPIKEY'); ?></div>
							<div class="vcm-param-setting">
								<div class="vcmorderapikeydiv">
									<div class="vcmorderapikeylogo"<?php echo $has_api_key ? ' onclick="vcmToggleApiEdit();"' : ''; ?>>
										<span class="vcm-apikey-key-icon<?php echo $has_api_key ? ' vcm-apikey-key-icon-obfuscated' : ''; ?>"><?php VikBookingIcons::e('key'); ?></span>
									</div>
									<div class="vcmorderapikeyinner">
									<?php
									if ($has_api_key) {
										?>
										<input class="vcmorderapikeyvalinput" id="vcm-visible-apikey" name="" onkeyup="vcmCopyApi(this);" onblur="vcmCopyApi(this);" ondblclick="vcmToggleApiEdit();" value="<?php echo substr_replace($config['apikey'], str_repeat('•', (strlen($config['apikey']) - 3)), 3); ?>" size="24" readonly />
										<input type="hidden" name="vcm_apikey" id="vcm-hidden-apikey" value="<?php echo $config['apikey']; ?>" />
										<?php
									} else {
										?>
										<input class="vcmorderapikeyvalinput" name="vcm_apikey" onkeyup="vcmTrimApi(this);" onblur="vcmTrimApi(this);" value="" size="24"/>
										<?php
									}
									?>
									</div>
								</div>
							</div>
						</div>

					<?php
					if (VikChannelManager::isAvailabilityRequest()) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<?php echo JText::_('VCM_SYNC_PENDING'); ?>
								<?php
								echo $vcm_app->createPopover([
									'title' => JText::_('VCM_SYNC_PENDING'),
									'content' => JText::_('VCM_SYNC_PENDING_TIP'),
								]);
								?>
							</div>
							<div class="vcm-param-setting">
								<?php echo $vbo_app->printYesNoButtons('sync_pending', JText::_('VCMYES'), JText::_('VCMNO'), (int)VCMRequestAvailability::syncWhenPending(), 1, 0); ?>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_SYNC_PENDING_HELP'); ?></span>
							</div>
						</div>
						<?php
					}
					?>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFREPORTSINTERV'); ?> <?php echo $vcm_app->createPopover(array('title' => JText::_('VCMCONFREPORTSINTERV'), 'content' => JText::_('VCMCONFREPORTSINTERVDESC'))); ?></div>
							<div class="vcm-param-setting">
								<select name="reports_interval">
									<option value="0"<?php echo isset($config['reports_interval']) && empty($config['reports_interval']) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMDISABLED'); ?></option>
									<option value="7"<?php echo isset($config['reports_interval']) && (int)$config['reports_interval'] == 7 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMCONFREPORTSWEEK'); ?></option>
									<option value="14"<?php echo isset($config['reports_interval']) && (int)$config['reports_interval'] == 14 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMCONFREPORTS2WEEK'); ?></option>
									<option value="30"<?php echo (isset($config['reports_interval']) && (int)$config['reports_interval'] == 30) || !isset($config['reports_interval']) ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMCONFREPORTSMONTH'); ?></option>
								</select>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label">&nbsp;</div>
							<div class="vcm-param-setting">
								<a href="index.php?option=com_vikchannelmanager&amp;task=diagnostic" class="vcm-diagnostic-setting"><?php VikBookingIcons::e('broadcast-tower'); ?> <?php echo JText::_('VCMCONFDIAGNOSTICBTN'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend">
						<h3><?php VikBookingIcons::e('cogs'); ?> <?php echo JText::_('VCMMENUSETTINGS'); ?></h3>
					</legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_APPEARANCE_PREF'); ?></div>
							<div class="vcm-param-setting">
							<?php
							// prepare args for the multi-state toggle switch
							$appearance_vals = array(
								'light',
								'auto',
								'dark',
							);
							$appearance_lbls = array(
								array(
									'value' => '<i class="' . VikBookingIcons::i('sun') . '"></i>',
									'title' => JText::_('VCM_APPEARANCE_PREF_LIGHT'),
								),
								array(
									'value' => '<i class="' . VikBookingIcons::i('magic') . '"></i>',
									'title' => JText::_('VCM_APPEARANCE_PREF_AUTO'),
								),
								array(
									'value' => '<i class="' . VikBookingIcons::i('moon') . '"></i>',
									'title' => JText::_('VCM_APPEARANCE_PREF_DARK'),
								),
							);
							$appearance_attrs = array(
								array(
									'label_class' => 'vik-multiswitch-radiobtn-light',
									'input' 	  => array(
										'onchange' => 'vcmPreviewAppeareance(this.value)',
									),
								),
								array(
									'label_class' => 'vik-multiswitch-radiobtn-auto',
									'input' 	  => array(
										'onchange' => 'vcmPreviewAppeareance(this.value)',
									),
								),
								array(
									'label_class' => 'vik-multiswitch-radiobtn-dark',
									'input' 	  => array(
										'onchange' => 'vcmPreviewAppeareance(this.value)',
									),
								),
							);
							if ($vbo_app && method_exists($vbo_app, 'multiStateToggleSwitchField')) {
								// only updated versions of VBO will support this method
								echo $vbo_app->multiStateToggleSwitchField('appearance_pref', VikChannelManager::getAppearancePref(), $appearance_vals, $appearance_lbls, $appearance_attrs);
							} else {
								// we rely on the same method declared on VCM as a fallback
								echo VCM::multiStateToggleSwitchField('appearance_pref', VikChannelManager::getAppearancePref(), $appearance_vals, $appearance_lbls, $appearance_attrs);
							}
							?>
							</div>
						</div>
						<script type="text/javascript">
							/**
							 * Apply on the fly the preview of the selected
							 * appearance mode: light, auto, dark.
							 */
							function vcmPreviewAppeareance(mode) {
								var vcm_css_base_uri = '<?php echo VCM_ADMIN_URI . 'assets/css/vcm-appearance-%s.css'; ?>';
								var vcm_css_base_id  = 'vcm-css-appearance-';
								var vcm_css_modes 	 = {
									auto: vcm_css_base_uri.replace('%s', 'auto'),
									dark: vcm_css_base_uri.replace('%s', 'dark'),
									light: null
								};
								if (!vcm_css_modes.hasOwnProperty(mode)) {
									return false;
								}
								// set/unset CSS files from DOM
								for (var app_mode in vcm_css_modes) {
									if (!vcm_css_modes.hasOwnProperty(app_mode) || !vcm_css_modes[app_mode]) {
										continue;
									}
									if (app_mode == mode) {
										// set this CSS file
										jQuery('head').append('<link rel="stylesheet" id="' + vcm_css_base_id + app_mode + '" href="' + vcm_css_modes[app_mode] + '" media="all">');
									} else {
										// unset this CSS file
										if (jQuery('link#' + vcm_css_base_id + app_mode).length) {
											jQuery('link#' + vcm_css_base_id + app_mode).remove();
										} else if (jQuery('link#' + vcm_css_base_id + app_mode + '-css').length) {
											// WP framework may add "-css" as suffix to the given ID
											jQuery('link#' + vcm_css_base_id + app_mode + '-css').remove();
										}
									}
								}
							}

							/**
							 * Listen to appearance preference event that may be triggered by a shortcut.
							 */
							document.addEventListener('vcm-set-color-scheme', (e) => {
								let scheme = e?.detail?.scheme;
								if (!scheme) {
									return;
								}
								try {
									// just check the proper radio button without triggering any change event
									document.querySelector('input[type="radio"][name="appearance_pref"][value="' + scheme + '"]').checked = true;
								} catch(e) {
									// do nothing
								}
							});
						</script>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFEMAIL'); ?></div>
							<div class="vcm-param-setting"><input type="text" name="emailadmin" value="<?php echo $config['emailadmin']; ?>" size="40"/></div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFDATEFORMAT'); ?></div>
							<div class="vcm-param-setting">
								<select name="dateformat">
									<option value="%Y/%m/%d"<?php echo ($config['dateformat']=="%Y/%m/%d" ? " selected=\"selected\"" : ""); ?>>Y/m/d</option>
									<option value="%d/%m/%Y"<?php echo ($config['dateformat']=="%d/%m/%Y" ? " selected=\"selected\"" : ""); ?>>d/m/Y</option>
									<option value="%m/%d/%Y"<?php echo ($config['dateformat']=="%m/%d/%Y" ? " selected=\"selected\"" : ""); ?>>m/d/Y</option>
									<option value="%Y-%m-%d"<?php echo ($config['dateformat']=="%Y-%m-%d" ? " selected=\"selected\"" : ""); ?>>Y-m-d</option>
									<option value="%d-%m-%Y"<?php echo ($config['dateformat']=="%d-%m-%Y" ? " selected=\"selected\"" : ""); ?>>d-m-Y</option>
									<option value="%m-%d-%Y"<?php echo ($config['dateformat']=="%m-%d-%Y" ? " selected=\"selected\"" : ""); ?>>m-d-Y</option>
									<option value="%Y.%m.%d"<?php echo ($config['dateformat']=="%Y.%m.%d" ? " selected=\"selected\"" : ""); ?>>Y.m.d</option>
									<option value="%d.%m.%Y"<?php echo ($config['dateformat']=="%d.%m.%Y" ? " selected=\"selected\"" : ""); ?>>d.m.Y</option>
									<option value="%m.%d.%Y"<?php echo ($config['dateformat']=="%m.%d.%Y" ? " selected=\"selected\"" : ""); ?>>m.d.Y</option>
								</select>
							</div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFCURSYMB'); ?></div>
							<div class="vcm-param-setting"><input type="text" name="currencysymb" value="<?php echo $config['currencysymb']; ?>" size="10"/></div>
						</div>
					
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFCURNAME'); ?></div>
							<div class="vcm-param-setting">
								<select name="currencyname">
								<?php
								foreach ($iso4217 as $currency) {
									echo '<option value="'.substr($currency, 0, 3).'"'.($config['currencyname'] == substr($currency, 0, 3) ? ' selected="selected"' : '').'>'.$currency.'</option>'."\n";
								}
								?>
								</select>
							</div>
						</div>
						
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFDEFPAYMENTOPT'); ?></div>
							<div class="vcm-param-setting"><?php echo $select_payments; ?></div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMCONFDEFLANG'); ?></div>
							<div class="vcm-param-setting"><?php echo $select_langs; ?></div>
						</div>

					<?php
					if (VikChannelManager::isAvailabilityRequest($api_channel = false)) {
						/**
						 * If an iCal (non API) channel is available, ask for iCal cancellations.
						 * 
						 * @since 	1.8.9
						 */
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<?php echo JText::_('VCM_ICAL_CANCELLATIONS'); ?>
								<?php echo $vcm_app->createPopover(array('title' => JText::_('VCM_ICAL_CANCELLATIONS'), 'content' => JText::_('VCM_ICAL_CANCELLATIONS_HELP'))); ?>
							</div>
							<div class="vcm-param-setting">
							<?php
							if ($vbo_app && method_exists($vbo_app, 'printYesNoButtons')) {
								echo $vbo_app->printYesNoButtons('ical_cancellations', JText::_('VCMYES'), JText::_('VCMNO'), (!empty($config['ical_cancellations']) ? (int)$config['ical_cancellations'] : 0), 1, 0);
							} else {
								?>
								<input type="hidden" name="ical_cancellations" value="<?php echo (!empty($config['ical_cancellations']) ? (int)$config['ical_cancellations'] : 0); ?>" />
								<?php
							}
							?>
							</div>
						</div>
						<?php
					}

					if (VikChannelManager::isAvailabilityRequest()) {
						// auto bulk actions
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AV_WINDOW'); ?></div>
							<div class="vcm-param-setting">
								<div class="vcm-param-radio-group">
									<div class="vcm-param-radio">
										<input type="radio" name="av_window" id="av-window-manual" value="manual" <?php echo empty($config['av_window']) || $config['av_window'] == 'manual' ? 'checked' : ''; ?>/>
										<label for="av-window-manual" class="vcm-param-radio-label"><?php echo JText::_('VCM_AV_WINDOW_MANUAL'); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="radio" name="av_window" id="av-window-3m" value="3" <?php echo !empty($config['av_window']) && $config['av_window'] == '3' ? 'checked' : ''; ?>/>
										<label for="av-window-3m" class="vcm-param-radio-label"><?php echo JText::sprintf('VCM_AV_WINDOW_XMONTHS', 3); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="radio" name="av_window" id="av-window-6m" value="6" <?php echo !empty($config['av_window']) && $config['av_window'] == '6' ? 'checked' : ''; ?>/>
										<label for="av-window-6m" class="vcm-param-radio-label"><?php echo JText::sprintf('VCM_AV_WINDOW_XMONTHS', 6); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="radio" name="av_window" id="av-window-9m" value="9" <?php echo !empty($config['av_window']) && $config['av_window'] == '9' ? 'checked' : ''; ?>/>
										<label for="av-window-9m" class="vcm-param-radio-label"><?php echo JText::sprintf('VCM_AV_WINDOW_XMONTHS', 9); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="radio" name="av_window" id="av-window-12m" value="12" <?php echo !empty($config['av_window']) && $config['av_window'] == '12' ? 'checked' : ''; ?>/>
										<label for="av-window-12m" class="vcm-param-radio-label"><?php echo JText::sprintf('VCM_AV_WINDOW_XMONTHS', 12); ?></label>
									</div>
								</div>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AV_WINDOW_DESCR'); ?></span>
							</div>
						</div>
						<?php
					}

					/**
					 * Guest messages auto-responder.
					 * 
					 * @since 	1.8.21
					 */
					if (VikChannelManager::isAvailabilityRequest() && (VikChannelManager::getChannel(VikChannelManagerConfig::BOOKING) || VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI))) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_AUTORESPONDER_MESS'); ?></div>
							<div class="vcm-param-setting">
								<textarea name="autoresponder_mess"><?php echo htmlentities(VCMChatAutoresponder::getInstance()->getMessage()); ?></textarea>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_AUTORESPONDER_MESS_HELP'); ?></span>
							</div>
						</div>
						<?php
					}
					?>

						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<?php echo JText::_('VCM_EXPIRATION_REMINDERS'); ?>
								<?php echo $vcm_app->createPopover(array('title' => JText::_('VCM_EXPIRATION_REMINDERS'), 'content' => JText::_('VCM_EXPIRATION_REMINDERS_HELP'))); ?>
							</div>
							<div class="vcm-param-setting">
							<?php
							if ($vbo_app && method_exists($vbo_app, 'printYesNoButtons')) {
								echo $vbo_app->printYesNoButtons('expiration_reminders', JText::_('VCMYES'), JText::_('VCMNO'), (int)VikChannelManager::expirationReminders(), 1, 0);
							} else {
								?>
								<input type="hidden" name="expiration_reminders" value="<?php echo (int)VikChannelManager::expirationReminders(); ?>" />
								<?php
							}
							?>
							</div>
						</div>

						<?php
						/**
						 * iCal privacy protected fields: select the fields to be excluded.
						 * 
						 * @since 	1.8.24
						 */
						$ical_privacy_fields = VCMFactory::getConfig()->getArray('ical_privacy_fields', []);
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_ICAL_PRIVACY_FIELDS'); ?></div>
							<div class="vcm-param-setting">
								<div class="vcm-param-radio-group">
									<div class="vcm-param-radio">
										<input type="checkbox" name="ical_privacy_fields[]" id="ical-privacy-field-bid" value="bid" <?php echo in_array('bid', $ical_privacy_fields) ? 'checked' : ''; ?>/>
										<label for="ical-privacy-field-bid" class="vcm-param-radio-label"><?php echo JText::_('VCMSMARTBALBID'); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="checkbox" name="ical_privacy_fields[]" id="ical-privacy-field-email" value="email" <?php echo in_array('email', $ical_privacy_fields) ? 'checked' : ''; ?>/>
										<label for="ical-privacy-field-email" class="vcm-param-radio-label"><?php echo JText::_('VCMBCAHEMAIL'); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="checkbox" name="ical_privacy_fields[]" id="ical-privacy-field-customer" value="customer" <?php echo in_array('customer', $ical_privacy_fields) ? 'checked' : ''; ?>/>
										<label for="ical-privacy-field-customer" class="vcm-param-radio-label"><?php echo JText::_('VCMPVIEWORDERSVBTWO'); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="checkbox" name="ical_privacy_fields[]" id="ical-privacy-field-room" value="room" <?php echo in_array('room', $ical_privacy_fields) ? 'checked' : ''; ?>/>
										<label for="ical-privacy-field-room" class="vcm-param-radio-label"><?php echo JText::_('VCMBCARCROOMNAME'); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="checkbox" name="ical_privacy_fields[]" id="ical-privacy-field-guests" value="guests" <?php echo in_array('guests', $ical_privacy_fields) ? 'checked' : ''; ?>/>
										<label for="ical-privacy-field-guests" class="vcm-param-radio-label"><?php echo JText::_('VCMBCAHIMGTAG92'); ?></label>
									</div>
									<div class="vcm-param-radio">
										<input type="checkbox" name="ical_privacy_fields[]" id="ical-privacy-field-total" value="total" <?php echo in_array('total', $ical_privacy_fields) ? 'checked' : ''; ?>/>
										<label for="ical-privacy-field-total" class="vcm-param-radio-label"><?php echo JText::_('VCMPVIEWORDERSVBSEVEN'); ?></label>
									</div>
								</div>
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_ICAL_PRIVACY_FIELDS_HELP'); ?></span>
							</div>
						</div>

					</div>
				</div>
			</fieldset>

		<?php
		/**
		 * Recovery tools are only supported by a few channels.
		 * 
		 * @since 	1.8.0 	Feature introduced.
		 * @since 	1.8.1 	Expedia was added next to Booking.com and Airbnb API.
		 */
		$rec_tools_eligch = array(
			VikChannelManagerConfig::BOOKING,
			VikChannelManagerConfig::AIRBNBAPI,
			VikChannelManagerConfig::EXPEDIA,
		);
		if (in_array($module['uniquekey'], $rec_tools_eligch) && VikChannelManager::channelHasRoomsMapped($module['uniquekey'])) {
			$use_chname = $module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb API' : ucwords($module['name']);
			$use_chname = $module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL ? 'Google Hotel' : $use_chname;
			$use_chname = $module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR ? 'Google Vacation Rentals' : $use_chname;
			$use_chname = $module['uniquekey'] == VikChannelManagerConfig::VRBOAPI ? 'Vrbo API' : $use_chname;
			?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend">
						<h3><?php VikBookingIcons::e('ambulance'); ?> <?php echo JText::_('VCM_RECOVERYTOOLS'); ?></h3>
					</legend>
					<div class="vcm-params-container">

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCMFIRSTBSUMMTITLE'); ?></div>
							<div class="vcm-param-setting">
								<a href="index.php?option=com_vikchannelmanager&task=recovery_tools&mode=first_summary" onclick="return confirm(Joomla.JText._('VCM_ASK_CONTINUE'));" class="btn vcm-config-btn"><?php VikBookingIcons::e('cloud-download-alt'); ?> <?php echo $use_chname; ?></a>
							</div>
						</div>

						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo JText::_('VCM_DOWNLOAD_OTABOOKING'); ?></div>
							<div class="vcm-param-setting">
								<a href="index.php?option=com_vikchannelmanager&task=recovery_tools&mode=retransmit_otabooking&otaresid=%s&accountid=%s" id="vcm-recovery-tools-otares-link" style="display: none;"></a>
							<?php
							if (count($this->more_accounts)) {
								?>
								<div style="margin-bottom: 10px;">
									<select id="vcm-recovery-tools-otares-account">
									<?php
									foreach ($this->more_accounts as $acck => $accv) {
										$acc_name = $accv['prop_name'];
										$acc_info = json_decode($accv['prop_params'], true);
										$acc_info = !is_array($acc_info) ? array() : $acc_info;
										$acc_id = '';
										if (isset($acc_info['hotelid'])) {
											$acc_id = $acc_info['hotelid'];
										} elseif (isset($acc_info['id'])) {
											// useful for Pitchup.com to identify multiple accounts
											$acc_id = $acc_info['id'];
										} elseif (isset($acc_info['property_id'])) {
											// useful for Hostelworld
											$acc_id = $acc_info['property_id'];
										} elseif (isset($acc_info['user_id'])) {
											// useful for Airbnb API
											$acc_id = $acc_info['user_id'];
										}
										if (empty($acc_name)) {
											$acc_name = $acc_id;
										}
										?>
										<option value="<?php echo $acc_id; ?>"<?php echo $accv['active'] == 1 ? ' selected="selected"' : ''; ?>><?php echo $use_chname . ' - ' . $acc_name; ?></option>
										<?php
									}
									?>
									</select>
								</div>
								<?php
							} elseif (!empty($module['params']) && is_array($module['params'])) {
								/**
								 * Use the first account key as a hidden input field.
								 * 
								 * @since 	1.8.6
								 */
								$first_account_key = '';
								foreach ($module['params'] as $first_account_val) {
									if (!empty($first_account_val)) {
										$first_account_key = $first_account_val;
										break;
									}
								}
								?>
								<input type="hidden" id="vcm-recovery-tools-otares-account" value="<?php echo $first_account_key; ?>" />
								<?php
							}
							?>
								<input type="text" id="vcm-recovery-tools-otares-id" placeholder="<?php echo $this->escape(JText::_('VCMRESLOGSIDORDOTA')); ?>" value="" />
								<button type="button" class="btn vcm-config-btn" onclick="vcmDownloadOtaResId();"><?php VikBookingIcons::e('download'); ?> <?php echo JText::_('VCM_DOWNLOAD'); ?></button>
							</div>
						</div>

					</div>
				</div>
			</fieldset>
			
			<script type="text/javascript">
				function vcmDownloadOtaResId() {
					var base_link = jQuery('#vcm-recovery-tools-otares-link').attr('href');
					var ota_resid = jQuery('#vcm-recovery-tools-otares-id').val();
					if (!ota_resid || !ota_resid.length) {
						alert(Joomla.JText._('VCMRESLOGSIDORDOTA') + '!');
						return false;
					}
					base_link = base_link.replace('%s', ota_resid);
					if (jQuery('#vcm-recovery-tools-otares-account').length) {
						base_link = base_link.replace('%s', jQuery('#vcm-recovery-tools-otares-account').val());
					} else {
						base_link = base_link.replace('%s', '');
					}
					if (confirm(Joomla.JText._('VCM_ASK_CONTINUE'))) {
						document.location.href = base_link;
					} else {
						return false;
					}
				}
			</script>
			<?php
		}
		?>

		</div>

		<div class="vcm-config-maintab-right">
		<?php
		if (!empty($module['id']) && !empty($module['params'])) {
			$use_chname = $module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb API' : ucwords($module['name']);
			$use_chname = $module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL ? 'Google Hotel' : $use_chname;
			$use_chname = $module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR ? 'Google Vacation Rentals' : $use_chname;
			$use_chname = $module['uniquekey'] == VikChannelManagerConfig::VRBOAPI ? 'Vrbo API' : $use_chname;
			?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend">
						<div class="vcmparamshead<?php echo preg_replace("/[^a-zA-Z0-9]+/", '', $module['name']); ?>">
							<h3><?php echo $use_chname; ?></h3>
						</div>
					</legend>
					<div class="vcm-params-container">
					<?php
					if (count($this->more_accounts)) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<label for="vcm-changeaccount"><?php echo JText::_('VCMCHANGEACCOUNT'); ?></label>
							</div>
							<div class="vcm-param-setting">
								<select id="vcm-changeaccount" onchange="setAccountParams(this.value);">
								<?php
								foreach ($this->more_accounts as $acck => $accv) {
									$acc_name = $accv['prop_name'];
									if (empty($acc_name)) {
										$acc_info = json_decode($accv['prop_params'], true);
										$acc_name = '';
										if (isset($acc_info['hotelid'])) {
											$acc_name = $acc_info['hotelid'];
										} elseif (isset($acc_info['id'])) {
											// useful for Pitchup.com to identify multiple accounts
											$acc_name = $acc_info['id'];
										} elseif (isset($acc_info['property_id'])) {
											// useful for Hostelworld
											$acc_name = $acc_info['property_id'];
										} elseif (isset($acc_info['user_id'])) {
											// useful for Airbnb API
											$acc_name = $acc_info['user_id'];
										}
									}
									?>
									<option value="<?php echo $acck; ?>"<?php echo $accv['active'] == 1 ? ' selected="selected"' : ''; ?>><?php echo $acc_name; ?></option>
									<?php
								}
								?>
								</select>
								<div class="vcm-managemultiacc-btn">
									<button type="button" class="btn" onclick="vcmOpenModal();"><i class="icon-edit"></i><?php echo JText::_('VCMMANAGEACCOUNTS'); ?></button>
								</div>
							</div>
						</div>
						<?php
					}

					// display active module main parameters
					foreach ($module['params'] as $k => $v ) {
						$forced_lbl = ucwords(str_replace('_', ' ', $k));
						if ($k == 'hotelid') {
							$forced_lbl = 'Hotel ID';
							if ($module['uniquekey'] == VikChannelManagerConfig::VRBOAPI || $module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
								$forced_lbl = 'Account ID';
							}
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo $forced_lbl; ?> <sup>*</sup></div>
							<div class="vcm-param-setting">
							<?php
							if (strpos($k, 'pwd') !== false || strpos($k, 'pass') !== false) {
								?>
								<input type="text" name="<?php echo $k; ?>" value="" size="13" placeholder="<?php echo str_repeat('*', strlen($v)); ?>" data-param="<?php echo $k; ?>" onkeyup="vcmTrimPwd(this);"/>
								<input type="hidden" name="old_<?php echo $k; ?>" value="<?php echo $v; ?>"/>
								<?php
							} else {
								if (!empty($this->force_insert) && $k == 'hotelid') {
									$v = $this->force_insert;
								}
								$readonly = (in_array($module['uniquekey'], [VikChannelManagerConfig::AIRBNBAPI, VikChannelManagerConfig::VRBOAPI, VikChannelManagerConfig::GOOGLEVR]));
								if ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
									$readonly = true;
									$ghotel_mess = empty($v) ? 'VCM_GOOGLEHOTEL_WARNPARAM_EMPTY' : 'VCM_GOOGLEHOTEL_WARNPARAM_FULL';
									if ($k == 'hotelid' && empty($v)) {
										$hinv_id = VikChannelManager::getHotelInventoryID();
										$v = !empty($hinv_id) ? 'G-' . $hinv_id : $v;
										$ghotel_mess = !empty($v) ? 'VCM_GOOGLEHOTEL_WARNPARAM_EMPTYSAVE' : $ghotel_mess;
									}
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
									if ($k == 'hotelid') {
										$v = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::GOOGLEVR, '');
									}
								}
								?>
								<input type="text" name="<?php echo $k; ?>" value="<?php echo $v; ?>" size="13" data-param="<?php echo $k; ?>" data-channelid="<?php echo $module['uniquekey']; ?>" onkeyup="vcmValidateParam(this);" <?php echo $readonly ? 'readonly ' : ''; ?>/><span class="vcm-param-validation-msg"></span>
								<?php
								if ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
									?>
								<span class="vcm-param-setting-comment">
									<?php
									if (class_exists('VikBookingIcons')) {
										VikBookingIcons::e('exclamation-circle');
									}
									echo ' ' . JText::_((empty($v) ? 'VCM_AIRBAPI_WARNPARAM_EMPTY' : 'VCM_AIRBAPI_WARNPARAM_FULL'));
									?>
								</span>
									<?php
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
									?>
								<span class="vcm-param-setting-comment">
									<?php
									if (class_exists('VikBookingIcons')) {
										VikBookingIcons::e('exclamation-circle');
									}
									echo ' ' . JText::_($ghotel_mess);
									?>
								</span>
								<?php
									if ($ghotel_mess == 'VCM_GOOGLEHOTEL_WARNPARAM_EMPTY') {
										?>
								<p>
									<a href="index.php?option=com_vikchannelmanager&task=hoteldetails" class="btn vcm-config-btn"><i class="vboicn-home"></i> <?php echo JText::_('VCMMENUHOTEL') . ' - ' . JText::_('VCMMENUTACDETAILS'); ?></a>
								</p>
										<?php
									}
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::VRBOAPI) {
									?>
								<span class="vcm-param-setting-comment">
									<?php
									if (class_exists('VikBookingIcons')) {
										VikBookingIcons::e('exclamation-circle');
									}
									echo ' ' . JText::_((empty($v) ? 'VCM_VRBO_WARNPARAM_EMPTY' : 'VCM_GOOGLEHOTEL_WARNPARAM_FULL'));
									?>
								</span>
									<?php
									if (empty($v)) {
										?>
								<p>
									<a href="index.php?option=com_vikchannelmanager&view=vrbolistings" class="btn vcm-config-btn"><?php VikBookingIcons::e('building'); ?> <?php echo JText::_('VCMMENUAIRBMNGLST'); ?></a>
								</p>
										<?php
									}
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
									?>
								<span class="vcm-param-setting-comment">
									<?php
									if (class_exists('VikBookingIcons')) {
										VikBookingIcons::e('exclamation-circle');
									}
									echo ' ' . JText::_((empty($v) ? 'VCM_VRBO_WARNPARAM_EMPTY' : 'VCM_GOOGLEHOTEL_WARNPARAM_FULL'));
									?>
								</span>
								<p>
									<a href="index.php?option=com_vikchannelmanager&view=googlevrlistings" class="btn vcm-config-btn"><?php VikBookingIcons::e('building'); ?> <?php echo JText::_('VCMMENUAIRBMNGLST'); ?></a>
								</p>
									<?php
								} elseif ($k == 'hotelid' && !$readonly) {
									// memorize the current hotel ID setting
									$vcm_current_hotel_id = (string)$v;
									// add an empty container for the comment of this setting in case JS will need it
									?>
								<span class="vcm-param-setting-comment"></span>
									<?php
								}
							}
							?>
							</div>
						</div>
						<?php
					}

					/**
					 * Prepare the help section for Booking.com first connection aid.
					 * 
					 * @since 	1.9.16
					 */
					if ($module['uniquekey'] == VikChannelManagerConfig::BOOKING) {
						?>
						<div class="vcm-param-container" data-type="bcom-connection-aid" style="display: none;">
							<div class="vcm-param-label"></div>
							<div class="vcm-param-setting">
								<button type="button" class="btn btn-small vcm-bcom-connection-aid"><?php VikBookingIcons::e('info-circle'); ?> <?php echo JText::sprintf('VCM_PROBLEMS_WITH_OTA', 'Booking.com'); ?></button>
							</div>
						</div>
						<?php
					}

					/**
					 * Some channels, like Airbnb API, may need to render custom HTML content next to the params.
					 * 
					 * @since 	1.8.0
					 */
					if (!isset($this->ch_custom_params['params']) || !is_array($this->ch_custom_params['params'])) {
						$this->ch_custom_params['params'] = array();
					}
					foreach ($this->ch_custom_params['params'] as $k => $v) {
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label"><?php echo !is_numeric($k) ? ucwords(str_replace('_', ' ', $k)) : '&nbsp;'; ?></div>
							<div class="vcm-param-setting"><?php echo $v; ?></div>
						</div>
						<?php
					}
					//

					/**
					 * Instructions for configuring another account for this channel.
					 * 
					 * @since 	1.7.5
					 */
					$ch_support_multi_acc = !in_array($module['uniquekey'], [VikChannelManagerConfig::GOOGLEHOTEL, VikChannelManagerConfig::GOOGLEVR, VikChannelManagerConfig::VRBOAPI]);
					if (!$this->more_accounts && $module['av_enabled'] == 1 && $ch_support_multi_acc && !empty($module['params']) && is_array($module['params']) && strlen((string)$module['params'][key($module['params'])])) {
						?>
						<div class="vcm-param-container" id="vcm-config-multiaccount-tip" style="display: none;">
							<div class="vcm-param-setting">
								<span class="vcm-param-setting-comment"><?php echo $module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? JText::_('VCM_TIP_NEWACCOUNT_AIRBNBAPI') : JText::sprintf('VCM_TIP_CONF_NEWACCOUNT', ucwords($module['name'])); ?></span>
								<span class="vcm-param-setting-comment">
									<button type="button" class="btn btn-small btn-primary" onclick="vcmHideMultiaccTip();">
									<?php
									if (class_exists('VikBookingIcons')) {
										VikBookingIcons::e('times-circle');
									}
									echo ' ' . JText::_('VCMTIPMODALOK');
									?>
									</button>
								</span>
							</div>
						</div>
						<?php
					}

					/**
					 * Display the SSO (Self-Service Onboarding) link to connect the account with our Channel Manager.
					 * 
					 * @since 	1.8.16
					 */
					if ($module['uniquekey'] == VikChannelManagerConfig::VRBOAPI && VikChannelManager::channelHasRoomsMapped($module['uniquekey'])) {
						$vrbo_account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
						if (!empty($vrbo_account_key)) {
							?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">Vrbo Onboarding Link</div>
							<div class="vcm-param-setting">
								<a href="<?php echo VCMVrboListing::getSelfServiceOnboardingLink($vrbo_account_key); ?>" target="_blank" class="btn vcm-config-btn"><?php VikBookingIcons::e('external-link'); ?> Self-Service Onboarding</a>
								<span class="vcm-param-setting-comment">Need to connect your Vrbo account for the first time to the Channel Manager? Click the above link to start the connection or to create your Vrbo account for the first time.</span>
							</div>
						</div>
							<?php
						}
					}
					?>
					</div>
				</div>
			</fieldset>
		<?php
		}

		if (!empty($module['id']) && (!empty($module['settings']) || (isset($this->ch_custom_params['settings']) && is_array($this->ch_custom_settings['params'])))) {
			?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<legend class="adminlegend">
						<div class="vcmsettingshead<?php echo preg_replace("/[^a-zA-Z0-9]+/", '', $module['name']); ?>">
							<h3><?php echo JText::_('VCMCONFIGCHASETTINGSTITLE'); ?></h3>
						</div>
					</legend>
					<div class="vcm-params-container">
					<?php
					if (!is_array($module['settings'])) {
						$module['settings'] = array();
					}
					foreach ($module['settings'] as $k => $v ) {
						if ($v['type'] === 'hidden') {
							?><input type="hidden" name="<?php echo $this->escape($k); ?>" value="<?php echo $this->escape(is_array($v['value']) || is_object($v['value']) ? json_encode($v['value']) : $v['value']); ?>" /><?php
							continue;
						}

						$required = '';
						$req_action = '';
						if ($v['required']) {
							$required = ' <sup>*</sup>';
							$req_action = 'onBlur="checkField(\'vcm'.$k.'\');"';
						}
						?>
						<div class="vcm-param-container">
							<div class="vcm-param-label">
								<?php
								$paramlbl = JText::_($v['label']);
								echo $paramlbl . $required;
								$help_text = JText::_($v['label'].'_HELP');
								if (!empty($help_text) && $help_text != $v['label'].'_HELP') {
									echo ' ' . $vcm_app->createPopover(array('title' => $paramlbl, 'content' => $help_text));
								}
								?>
							</div>
							<div class="vcm-param-setting">
							<?php
							if ($v['type'] == 'text') {
								?><input type="text" name="<?php echo $k; ?>" id="vcm<?php echo $k; ?>" value="<?php echo ((!empty($v['value'])) ? $v['value'] : $v['default']); ?>" <?php echo $req_action; ?> size="30"/><?php
							} elseif ($v['type'] == 'largetext') {
								?><textarea
									cols="50"
									rows="6"
									name="<?php echo $k; ?>"
									id="vcm<?php echo $k; ?>"
									<?php echo !empty($v['hint']) ? 'placeholder="' . $this->escape(JText::_($v['hint'])) . '"' : ''; ?>
									<?php echo $req_action; ?>
								><?php echo ((!empty($v['value'])) ? $v['value'] : $v['default']); ?></textarea><?php
							} elseif ($v['type'] == 'select') {
								?><select name="<?php echo $k; ?>" id="vcm<?php echo $k; ?>" <?php echo $req_action; ?>>
									<?php $value = (!empty($v['value']) ? $v['value'] : $v['default']);
									foreach ($v['options'] as $o) { ?>
										<option value="<?php echo $o; ?>" <?php echo (($value == $o) ? 'selected="selected"' : ''); ?>><?php echo JText::_($o); ?></option>
									<?php } ?>
								</select><?php
							} elseif ($v['type'] == 'multiple') {
								$v['value'] = (array) $v['value'];
								?><select name="<?php echo $k; ?>[]" id="vcm<?php echo $k; ?>" multiple size="<?php echo min(count($v['options'])+1, 8); ?>" <?php echo $req_action; ?>>
									<?php $values = (count($v['value']) > 0 ? $v['value'] : $v['default']);
									foreach ($v['options'] as $o) { ?>
										<option value="<?php echo $o; ?>" <?php echo ((@in_array($o, $values) == $o) ? 'selected="selected"' : ''); ?>><?php echo JText::_($o); ?></option>
									<?php } ?>
								</select><?php
							} elseif ($v['type'] == 'checkbox') {
								$checked = (int) (!is_null($v['value'] ?? null) ? $v['value'] : ($v['default'] ?? false));
								
								echo $vbo_app->printYesNoButtons($k, JText::_('JYES'), JText::_('JNO'), $checked, 1, 0);
							}
							?>
								<?php if (!empty($v['description'])): ?>
									<span class="vcm-param-setting-comment"><?php echo JText::_($v['description']); ?></span>
								<?php endif; ?>
							</div>
						</div>
							<?php
					}

					/**
					 * Some channels, like Airbnb API, may need to render custom settings.
					 * 
					 * @since 	1.8.0
					 */
					if (isset($this->ch_custom_params['settings']) && is_array($this->ch_custom_params['settings'])) {
						foreach ($this->ch_custom_params['settings'] as $settings_helper) {
							if (!is_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'default_' . $settings_helper . '.php')) {
								continue;
							}
							echo $this->loadTemplate($settings_helper);
						}
					}
					//
					?>
					</div>
				</div>
			</fieldset>
			<?php
		}

		if ($module['uniquekey'] == VikChannelManagerConfig::AI) {
			?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<a id="aiperms"></a>
					<legend class="adminlegend">
						<div class="vcmsettingsheadai">
							<h3><?php echo JText::_('VCM_AI_ACL_FIELDSET_TITLE'); ?></h3>
						</div>
					</legend>
					<div class="vcm-params-container">
						<div class="vcm-param-container" style="padding: 0 0 15px;">
							<div class="vcm-param-setting-comment">
								<?php echo JText::_('VCM_AI_ACL_FIELDSET_HELP'); ?>
							</div>
						</div>
						<?php
						$aclRules = $module['settings']['acl']['rules'] ?? [];
						$acl = (array) json_decode($module['settings']['acl']['value'] ?? '[]', true);

						foreach ($aclRules as $rule): ?>
							<div class="vcm-param-container">
								<div class="vcm-param-label"><?php echo JText::_('VCM_AI_ACL_RULE_' . strtoupper($rule) . '_LABEL'); ?></div>
								<div class="vcm-param-setting">
									<?php echo $vbo_app->printYesNoButtons('ai_acl_' . $rule, JText::_('JYES'), JText::_('JNO'), (int) ($acl[$rule] ?? true), 1, 0); ?>
									<div class="vcm-param-setting-comment">
										<?php echo JText::_('VCM_AI_ACL_RULE_' . strtoupper($rule) . '_DESC'); ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</fieldset>
			<script>
				(function($) {
					'use strict';

					$(function() {
						// auto-populate the AI ACL field every time a checkbox changes
						$('input[name*="ai_acl_"]').on('change', function() {
							const aclField = $('input[name="acl"]');
							let acl = aclField.val();

							try {
								acl = JSON.parse(acl);
							} catch (err) {
								acl = {};
							}

							const rule = $(this).attr('name').replace(/^ai_acl_/, '');

							acl[rule] = $(this).is(':checked');

							aclField.val(JSON.stringify(acl));
						});
					});
				})(jQuery);
			</script>
			<?php
		}
		
		if ($decoded_accounts) {
			?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<a id="appaccounts"></a>
					<legend class="adminlegend"><?php VikBookingIcons::e('mobile'); ?> <?php echo JText::_('VCMAPPACCTITLE'); ?></legend>
					<div class="vcm-params-container">
						<div class="vcm-param-setting">
						<?php
						foreach ($decoded_accounts as $key => $value) {
							?>
							<div class="vcm-appaccounts-row">
								<div class="vcm-appaccounts-element">
									<i class="vboicn-key"></i>
									<span class="vcm-appaccounts-email"><?php echo $key; ?></span>
								</div>
								<div class="vcm-appaccounts-buttons">
									<div class="vcm-appaccounts-element">
										<span class="vcm-appaccounts-edit">
											<a class="btn" onclick="vcmOpenUPModal('<?php echo addslashes($key); ?>', '<?php echo addslashes($value); ?>');"><i class="vboicn-cogs" style="margin:0;"></i></a>
										</span>
									</div>
									<div class="vcm-appaccounts-element">
										<span class="vcm-appaccounts-remove">
											<a class="btn btn-danger" href="index.php?option=com_vikchannelmanager&task=app_account_update&action=Remove&email=<?php echo $key; ?>" onclick="return confirm(Joomla.JText._('VCMREMOVECONFIRM'));">&times;</a>
										</span>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						</div>
					</div>
				</div>
			</fieldset>
			<?php
		}

		/**
		 * Active desktop Push subscriptions.
		 * 
		 * @since 	1.8.20
		 */
		$current_user 		= JFactory::getUser();
		$current_user_name 	= $current_user->username;
		$current_user_email = $current_user->email;
		$push_registrations = VCMPushSubscription::getInstance()->getRegistrations();
		if ($push_registrations) {
			?>
			<fieldset class="adminform">
				<div class="vcm-params-wrap">
					<a id="pushregistrations"></a>
					<legend class="adminlegend">
						<span class="vcm-congrats-notifications"><?php VikBookingIcons::e('desktop'); ?></span>
						<span><?php echo JText::_('VCM_WEBAPP_PUSH_REGISTRATIONS'); ?></span>
					</legend>
					<div class="vcm-params-container vcm-pushregistration-list">
					<?php
					// display registrations in a descending order
					for ($ind = count($push_registrations) - 1; $ind >= 0; $ind--) {
						// get current registration
						$desktop_registration = $push_registrations[$ind];

						if (!is_array($desktop_registration) || !isset($desktop_registration['data']) || !isset($desktop_registration['user'])) {
							// corrupted structure
							continue;
						}

						// check if this registration matches the current user
						$registration_account_match = ($desktop_registration['user']['name'] == $current_user_name && $desktop_registration['user']['email'] == $current_user_email);

						// obtain the information about this desktop registration for Webpush
						$registration_info = VCMPushSubscription::getInstance($desktop_registration)->getInformation();

						// build browser and OS icons
						switch ($registration_info['browser']) {
							case 'Safari':
								$browser_icon = VikBookingIcons::i('safari', '', 'fab');
								break;
							case 'Firefox':
								$browser_icon = VikBookingIcons::i('firefox-browser', '', 'fab');
								break;
							case 'Chrome':
								$browser_icon = VikBookingIcons::i('chrome', '', 'fab');
								break;
							case 'Edge':
								$browser_icon = VikBookingIcons::i('edge', '', 'fab');
								break;
							case 'Opera':
								$browser_icon = VikBookingIcons::i('opera', '', 'fab');
								break;
							default:
								$browser_icon = VikBookingIcons::i('desktop');
								break;
						}

						switch ($registration_info['platform']) {
							case 'Apple':
								$os_icon = VikBookingIcons::i('apple', '', 'fab');
								break;
							case 'Windows':
								$os_icon = VikBookingIcons::i('windows', '', 'fab');
								break;
							case 'Linux':
								$os_icon = VikBookingIcons::i('linux', '', 'fab');
								break;
							case 'Android':
								$os_icon = VikBookingIcons::i('android', '', 'fab');
								break;
							default:
								$os_icon = VikBookingIcons::i('question');
								break;
						}
						?>
						<div class="vcm-params-block" data-registration-index="<?php echo $ind; ?>" data-registration-accountmatch="<?php echo (int)$registration_account_match; ?>">
							<div class="vcm-param-container">
								<div class="vcm-param-setting">
									<strong><i class="<?php echo $browser_icon; ?>"></i> <?php echo $registration_info['browser']; ?></strong>
									<div>
										<i class="<?php echo $os_icon; ?>" title="<?php echo $this->escape($registration_info['platform']); ?>"></i> <span title="<?php echo $this->escape($desktop_registration['date']); ?>"><?php echo JHtml::_('date.relative', $desktop_registration['date']); ?></span>
									</div>
									<div><?php echo implode(' - ', [$desktop_registration['user']['name'], $desktop_registration['user']['email']]) ?></div>
									<span class="vcm-pushregistration-agent" style="display: none;"><?php echo $desktop_registration['data']['agent']; ?></span>
									<span class="vcm-pushregistration-endpoint" style="display: none;"><?php echo $desktop_registration['data']['endpoint']; ?></span>
								</div>
								<span class="vcm-param-setting-pull-right">
									<button type="button" class="btn btn-danger" onclick="vcmDeleteDesktopRegistration(this);">&times;</button>
								</span>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</fieldset>

			<script type="text/javascript">
				jQuery(function() {

					var vbo_browser_push_endp,
						vbo_browser_push_agent
						vbo_browser_push_matched = false;

					var this_browser_badge = jQuery('<span></span>')
						.addClass('label label-info')
						.text(Joomla.JText._('VCM_THIS_BROWSER'));

					try {
						vbo_browser_push_endp  = VBOCore.storageGetItem(VBOCore.push_storage_endpoint_id);
						vbo_browser_push_agent = window.navigator.userAgent;
					} catch(e) {
						vbo_browser_push_endp  = '';
						vbo_browser_push_agent = '';
					}

					jQuery('.vcm-pushregistration-list').find('.vcm-params-block[data-registration-accountmatch="1"]').each(function() {
						if (vbo_browser_push_endp && vbo_browser_push_endp == jQuery(this).find('.vcm-pushregistration-endpoint').text()) {
							// match found, add badge to identify the current browser
							vbo_browser_push_matched = true;
							jQuery(this).find('.vcm-param-setting-pull-right').prepend(this_browser_badge);
							// break the loop
							return false;
						}
					});

					if (!vbo_browser_push_matched && vbo_browser_push_agent) {
						jQuery('.vcm-pushregistration-agent').each(function() {
							if (vbo_browser_push_agent == jQuery(this).text()) {
								// match found, add badge to identify the current browser
								vbo_browser_push_matched = true;
								jQuery(this).closest('.vcm-params-block').find('.vcm-param-setting-pull-right').prepend(this_browser_badge);
								// break the loop
								return false;
							}
						});
					}

					if (vbo_browser_push_matched) {
						jQuery('.vcm-congrats-notifications').on('click', function() {
							// register click event to test the notifications
							VBOCore.dispatchCongratulations();
						});
					}

				});
			</script>
			<?php
		}
		?>
		</div>

	</div>
	
	<input type="hidden" name="force_insert" value="<?php echo $this->force_insert; ?>">
	<?php
	if (!empty($this->force_insert)) {
		$warnings = VikRequest::getString('warnings');
		if (!empty($warnings)) {
			?>
	<input type="hidden" name="warnings" value="<?php echo $warnings; ?>">
			<?php
		}
	}
	?>
	<input type="hidden" name="task" value="saveconfig">
	<input type="hidden" name="option" value="com_vikchannelmanager">
</form>

<script>
	var vcm_overlay_on = false;

	function checkField(id) {
		if (jQuery('#'+id).val().length == 0) {
			jQuery('#'+id).addClass('vcmrequired');
		} else {
			jQuery('#'+id).removeClass('vcmrequired');
		}
	}

	function vcmCloseModal() {
		jQuery(".vcm-info-overlay-block").fadeOut(400, function() {
			//jQuery(this).attr("class", "vcm-info-overlay-block");
		});
		vcm_overlay_on = false;
	}

	function vcmOpenModal() {
		jQuery(".vcm-info-overlay-chaccounts").fadeIn();
		vcm_overlay_on = true;
	}

	function vcmOpenUPModal(email, pwd) {
		jQuery(".vcm-info-overlay-password").fadeIn();
		jQuery(".vcm-info-overlay-password-email-value").val(email);
		jQuery(".vcm-info-overlay-password-email").html(email);
		jQuery(".vcm-info-overlay-password-app-pwd").val(pwd);
		vcm_overlay_on = true;
	}

	function vcmToggleAPIPwd() {
		var hidden_value = jQuery('.vcm-info-overlay-password-app-pwd').val();
		var display_value = jQuery('.vcm-info-overlay-password-app-pwd-show').val();
		if (!display_value.length) {
			jQuery('.vcm-info-overlay-password-app-pwd-show').val(hidden_value);
		} else {
			jQuery('.vcm-info-overlay-password-app-pwd-show').val('');
		}
	}

	/**
	 * Multi-account configuration tips.
	 * 
	 * @since 	1.7.5
	 */
	function vcmHideMultiaccTip() {
		jQuery('#vcm-config-multiaccount-tip').hide();
		var nd = new Date();
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
		document.cookie = "vcmHideMultiaccTip=1; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
	}

	/**
	 * Delete a specific desktop registration for Push.
	 */
	function vcmDeleteDesktopRegistration(elem) {
		var index = jQuery(elem).closest('.vcm-params-block').attr('data-registration-index');
		if (!index) {
			throw new Error('Could not find registration index');
		}
		if (confirm(Joomla.JText._('VCMREMOVECONFIRM'))) {
			var hash_rand_reload = Math.random();
			var page_reload_url  = "<?php echo VCMFactory::getPlatform()->getUri()->admin('index.php?option=com_vikchannelmanager&task=config', $xhtml = false); ?>&rand_reload=" + hash_rand_reload + "#pushregistrations";
			VBOCore.doAjax(
				"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=push.delete_registration'); ?>",
				{
					index: index
				},
				(success) => {
					// display success message and reload the page
					alert(Joomla.JText._('MSG_BASE_SUCCESS') + '!');
					document.location.href = page_reload_url;
				},
				(error) => {
					// display error and always reload the page, because the registration may have been deleted internally
					alert(error.responseText);
					document.location.href = page_reload_url;
				}
			);
		}
	}

	jQuery(function() {

		/**
		 * Memorize the current hotel ID setting, if the currently active channel supports it.
		 */
		var vcm_current_hotel_id = '<?php echo $vcm_current_hotel_id; ?>';

		/**
		 * Check whether to show multi-account configuration tips.
		 * 
		 * @since 	1.7.5
		 */
		if (jQuery('#vcm-config-multiaccount-tip').length && document.cookie.indexOf('vcmHideMultiaccTip=1') < 0) {
			jQuery('#vcm-config-multiaccount-tip').fadeIn();
		}

		/**
		 * Wrap the keyup debounce event for validating the main channel setting in a try-catch as it relies on VBOCore.
		 */
		try {
			/**
			 * Register keyup event listener with a debounce technique for validating the value for the "hotel ID" setting.
			 */
			let hotel_id_setting = document.querySelector('input[data-param="hotelid"]');
			if (hotel_id_setting) {
				// register the event
				hotel_id_setting.addEventListener('keyup', VBOCore.debounceEvent((e) => {
					let input = e.target;
					if (!input || !input.value) {
						return;
					}
					// check if there are rooms mapped under this account
					VBOCore.doAjax(
						"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=bulkaction.count_rooms_mapped'); ?>",
						{
							account: input.value,
							channel: input.dataset.channelid,
							tmpl: "component"
						},
						(resp) => {
							let obj_res = typeof resp === 'string' ? JSON.parse(resp) : resp;
							let tot_account_rooms = obj_res['count'] > 0 ? obj_res['count'] : 0;

							let help_elem = hotel_id_setting.parentNode.querySelector('span.vcm-param-setting-comment');
							if (!help_elem) {
								return;
							}

							let base_page = '<?php echo VCMPlatformDetection::isJoomla() ? 'index.php' : 'admin.php'; ?>';

							help_elem.innerHTML = Joomla.JText._('VCM_ACCOUNT_ROOMS_MAPPED').replace('%d', tot_account_rooms);
							if (!tot_account_rooms) {
								help_elem.innerHTML += '<br/>' + Joomla.JText._('VCM_ACCOUNT_NO_MAPPED_ROOMS_WARN').replace('%s', '<a href="' + base_page + '?option=com_vikchannelmanager&task=roomsynch">' + Joomla.JText._('VCMMENUEXPSYNCH') + '</a>');
							}
							if (input.value != vcm_current_hotel_id) {
								help_elem.innerHTML += '<br/><span style="color: red;">' + Joomla.JText._('VCM_NEW_ACCOUNT_SAVE_WARN') + '</span>';
							}

							// check for Booking.com connection AID
							let bcom_connection_aid = document.querySelector('.vcm-param-container[data-type="bcom-connection-aid"]');
							if (bcom_connection_aid) {
								// display element
								bcom_connection_aid.style.display = '';
								if (!bcom_connection_aid?._clickEv) {
									// register button click event
									let bcom_aid_btn = bcom_connection_aid.querySelector('button.vcm-bcom-connection-aid');
									if (!tot_account_rooms) {
										bcom_aid_btn.classList.add('btn-warning');
									}
									bcom_aid_btn.addEventListener('click', () => {
										VBOCore.displayModal({
											suffix: 'bcom_connection_aid',
											extra_class: 'vbo-modal-tall vbo-modal-nofooter',
											title: <?php echo json_encode(JText::_('VCM_BCOM_CONNECTION_AID_TITLE')); ?>,
											body: <?php echo json_encode(JText::_('VCM_BCOM_CONNECTION_AID_TXT')); ?>,
										});
									});
								}
							}
						},
						(err) => {
							// display error
							console.error(err);
							alert(err.responseText);
							// make the current help message empty
							let help_elem = hotel_id_setting.parentNode.querySelector('span.vcm-param-setting-comment');
							if (help_elem) {
								help_elem.innerHTML = '';
							}
						}
					);
				}, 500));

				// schedule the dispatch of the event so that the page can display this useful information about the rooms mapped
				if (vcm_current_hotel_id) {
					setTimeout(() => {
						// trigger event
						const count_rooms_event = new Event('keyup');
						hotel_id_setting.dispatchEvent(count_rooms_event);
					}, 500);
				}
			}
		} catch(e) {
			// do nothing
			console.error(e);
		}

		jQuery(".vcm-info-overlay-pass-input").change(function() {
			var confirm = jQuery(".vcm-info-overlay-pass-confirm").val();
			if (jQuery(this).val() == confirm) {
				jQuery(".vcm-info-overlay-pass-error-div").hide();
				if (jQuery(this).val() != "") {
					jQuery(".vcm-info-overlay-pass-sub-button").prop({disabled:false});
				}
			} else {
				jQuery(".vcm-info-overlay-pass-error-div").show();
				jQuery(".vcm-info-overlay-pass-sub-button").prop({disabled:true});
			}
		});

		jQuery(".vcm-info-overlay-pass-confirm").keyup(function() {
			var confirm = jQuery(".vcm-info-overlay-pass-input").val();
			if (jQuery(this).val() == confirm) {
				jQuery(".vcm-info-overlay-pass-error-div").hide();
				if (jQuery(this).val() != "") {
					jQuery(".vcm-info-overlay-pass-sub-button").prop({disabled:false});
				}
			} else {
				jQuery(".vcm-info-overlay-pass-error-div").show();
				jQuery(".vcm-info-overlay-pass-sub-button").prop({disabled:true});
			}
		});

		jQuery(document).mouseup(function(e) {
			if (!vcm_overlay_on) {
				return false;
			}
			var vcm_overlay_cont = jQuery(".vcm-info-overlay-content");
			if (!vcm_overlay_cont.is(e.target) && vcm_overlay_cont.has(e.target).length === 0) {
				vcmCloseModal();
			}
		});
		jQuery(document).keyup(function(e) {
			if (e.keyCode == 27 && vcm_overlay_on) {
				vcmCloseModal();
			}
		});
	<?php
	if (!empty($this->force_insert)) {
		?>
		document.getElementById("adminForm").submit();
		<?php
	}
	?>
	});

<?php
if ($this->more_accounts) {
	$js_acc_arr = array();
	foreach ($this->more_accounts as $acck => $accv) {
		$js_acc_arr[$acck] = json_decode($accv['prop_params']);
	}
	?>
	var vcm_accounts_params = <?php echo json_encode($js_acc_arr); ?>;

	function setAccountParams(ind) {
		if (!window.jQuery) {
			alert('JavaScript error: jQuery is undefined');
			return false;
		}
		var params_set = 0;
		if (vcm_accounts_params.hasOwnProperty(ind)) {
			for (var param in vcm_accounts_params[ind]) {
				if (vcm_accounts_params[ind].hasOwnProperty(param)) {
					var felem = jQuery("input[data-param='"+param+"']");
					if (felem.length) {
						felem.val(vcm_accounts_params[ind][param]).addClass('vcm-accountparam-changed');
						params_set++;
					}
				}
			}
		}
		if (params_set > 0) {
			jQuery('#adminForm').append('<input type="hidden" name="flush_session" value="1" />');
			document.getElementById("adminForm").submit();
		} else {
			alert('No Params found');
			return false;
		}
	}

	function removeAccount(ind, hotelid) {
		if (!window.jQuery) {
			alert('JavaScript error: jQuery is undefined');
			return false;
		}
		if (confirm(Joomla.JText._('VCMREMOVEACCOUNTCONF'))) {
			window.location.href = 'index.php?option=com_vikchannelmanager&task=rmchaccount&ind='+ind+'&hid='+hotelid;
		} else {
			return false;
		}

	}
	<?php
}
?>

(function($) {
	'use strict';

	$(function() {
		// for the AI settings only, toggle the visibility of the "AI Signature" parameter depending
		// on the "Messaging Auto-Responder" checkbox
		$('input[name="autoresponder_messages"]')
			.on('change', function() {
				if ($(this).is(':checked')) {
					$('textarea[name="autoresponder_messages_footer"]').closest('.vcm-param-container').show();
				} else {
					$('textarea[name="autoresponder_messages_footer"]').closest('.vcm-param-container').hide();
				}
			})
			.trigger('change');

		// for the AI settings only, toggle the visibility of the "Exclude Negative Reviews" parameter depending
		// on the "Automatic Review Replies" checkbox
		$('input[name="autoresponder_review_reply"]')
			.on('change', function() {
				if ($(this).is(':checked')) {
					$('input[name="ignore_negative_reviews"]').closest('.vcm-param-container').show();
				} else {
					$('input[name="ignore_negative_reviews"]').closest('.vcm-param-container').hide();
				}
			})
			.trigger('change');
	});
})(jQuery);
</script>
