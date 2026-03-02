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

$config = $this->config;
$channel = $this->channel;

$configok = true;
$validate = array('apikey');
foreach ($validate as $v) {
	if (empty($config[$v])) {
		$configok = false;
	?>
	<p class="vcmfatal"><?php echo JText::_('VCMBASICSETTINGSNOTREADY'); ?></p>
	<?php break; }
}

if ($configok) {
	?>
	<script language="JavaScript" type="text/javascript">
	if (typeof window.JSON == 'undefined') {
		window.JSON = {
			parseJSobject : function (object) {
				var temp = '{';
				var s = 0;
				for (i in object) {
					if (s) { temp+=','; }
					temp += '"'+i+'":';
					if (typeof object[i] == 'object') {
						temp += this.parseJSobject(object[i]);
					} else {
						temp += '"'+object[i]+'"';
					}
					s++;
				}
				temp += '}';
				return temp;
			},
			stringify : function(data){
				return this.parseJSobject(data);
			}
		};
	}
	jQuery(function() {
		jQuery("#vcmstartsynch").click(function() {
			jQuery(".vcmsynchspan").removeClass("vcmsynchspansuccess");
			jQuery(".vcmsynchspan").removeClass("vcmsynchspanerror").addClass("vcmsynchspanloading");
			jQuery("#vcmroomsynchresponsebox").html("");
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=exec_par_products', false); ?>",
				data: { tmpl: "component" }
			}).done(function(res) { 
				jQuery(".vcmsynchspan").removeClass("vcmsynchspanloading");
				if (res.substr(0, 9) == 'e4j.error') {
					jQuery(".vcmsynchspan").addClass("vcmsynchspanerror");
					jQuery("#vcmroomsynchresponsebox").html("<pre class='vcmpreerror'>" + res.replace("e4j.error.", "") + "</pre>");
				} else {
					jQuery(".vcmsynchspan").addClass("vcmsynchspansuccess");
					jQuery("#vcmroomsynchresponsebox").html('<div class="vcmroomsynchresponsebox-inner">' + res + '</div>');
				}
			}).fail(function() { 
				jQuery(".vcmsynchspan").removeClass("vcmsynchspanloading").addClass("vcmsynchspanerror");
				alert("Error Performing Ajax Request"); 
			});
		});
	});
	jQuery(document).on("click", ".vcmtableleftspkeyopen", function() {
		jQuery(this).addClass("vcmtableleftspkeyclose").removeClass("vcmtableleftspkeyopen");
		jQuery(this).next("div").show();
		if (jQuery(this).find('i').length) {
			jQuery(this).find('i').remove();
			jQuery(this).append('<?php echo class_exists('VikBookingIcons') ? '<i class="' . VikBookingIcons::i('chevron-up') . '"></i>' : ''; ?>');
		}
	});
	jQuery(document).on("click", ".vcmtableleftspkeyclose", function() {
		jQuery(this).addClass("vcmtableleftspkeyopen").removeClass("vcmtableleftspkeyclose");
		jQuery(this).next("div").hide();
		if (jQuery(this).find('i').length) {
			jQuery(this).find('i').remove();
			jQuery(this).append('<?php echo class_exists('VikBookingIcons') ? '<i class="' . VikBookingIcons::i('chevron-down') . '"></i>' : ''; ?>');
		}
	});
	function vcmRemoveLink(idrota) {
		jQuery("#vcmrowrelota"+idrota).remove();
		var vcmonlyidrota = idrota.split("_");
		jQuery("#vcmotarselector"+vcmonlyidrota[0]).removeClass("vcmselectedotaroom");
		jQuery("#vcmotarselector"+vcmonlyidrota[0]).find('.vcmselectotaroom-txt').text("<?php echo addslashes(JText::_('VCMSELECTOTAROOMTOLINK')); ?>");
		var vcmoldotaidr = jQuery("#vcmotahelper").val();
		if (vcmoldotaidr == vcmonlyidrota[0]) {
			jQuery("#vcmotahelper").val("");
			jQuery(".vcmselectvbroom").fadeOut();
		}
		jQuery("#inputotar"+idrota).remove();
		jQuery("#inputotarname"+idrota).remove();
		jQuery("#inputvbr"+idrota).remove();
		if (jQuery("#pricingotar"+idrota) != 'undefined') {
			jQuery("#pricingotar"+idrota).remove();
		}
	}
	function vcmStartLinking(idrota, namerota, fast_blink) {
		var vcmid = idrota;
		if (jQuery("#vcmrowrelota"+idrota).length > 0) {
			if (jQuery("#inputvbr"+idrota).length == 0) {
				jQuery("#vcmrowrelota"+idrota).remove();
				jQuery("#inputotar"+idrota).remove();
				jQuery("#inputotarname"+idrota).remove();
				jQuery("#inputvbr"+idrota).remove();
			} else {
				var date = new Date;
				vcmid += "_"+date.getMinutes()+"_"+date.getSeconds();
			}
		}
		var vcmoldotaidr = jQuery("#vcmotahelper").val();
		if (vcmoldotaidr != '') {
			var vcmoldattr = jQuery("#vcmotahelper").attr("rel");
			jQuery("#inputotar"+vcmoldattr).remove();
			jQuery("#inputotarname"+vcmoldattr).remove();
			jQuery("#inputvbr"+vcmoldattr).remove();
			jQuery("#vcmrowrelota"+vcmoldattr).remove();
		}
		jQuery("#vcmotahelper").val(idrota);
		jQuery("#vcmotahelper").attr("rel", vcmid);

		// safely append hidden fields
		jQuery("#vcmroomsynchhelperbox")
			.append(jQuery('<input type="hidden" name="otaroomsids[]"/>').attr('id', 'inputotar' + vcmid).val(idrota))
			.append(jQuery('<input type="hidden" name="otaroomsnames[]"/>').attr('id', 'inputotarname' + vcmid).val(namerota));

		if (typeof room_plans === 'undefined') {
			// the AJAX response should have defined this object
			console.error('Critical: room_plans is undefined. The AJAX response may be broken.', idrota, namerota);
			return false;
		}

		for (var rkey in room_plans) {
			if (room_plans.hasOwnProperty(rkey) && rkey == 'r'+idrota) {
				jQuery("#vcmroomsynchhelperbox").append("<input type='hidden' name='otapricing[]' value='"+JSON.stringify(room_plans[rkey])+"' id='pricingotar"+vcmid+"' />");
				break;
			}
		}

		var link_html = '';
		link_html += '<tr class="vcmrowrelota" id="vcmrowrelota' + vcmid + '">';
		link_html += '<td>' + idrota + '</td>';
		link_html += '<td>' + namerota + '</td>';
		<?php
		if (class_exists('VikBookingIcons')) {
			?>
			link_html += '<td><i class="<?php echo VikBookingIcons::i('times-circle', 'vcm-rmapping-rmrel-icn'); ?>" onclick="vcmRemoveLink(\'' + vcmid + '\');" /></td>';
			<?php
		} else {
			?>
			link_html += '<td><img class="vcmimgremovelink" src="<?php echo addslashes(VCM_ADMIN_URI.'assets/css/images/remove.png'); ?>" onclick="vcmRemoveLink(\'' + vcmid + '\');" /></td>';
			<?php
		}
		?>
		link_html += '</tr>';
		jQuery(".vcmtablemiddle").append(link_html);

		if (!fast_blink) {
			// animate action
			jQuery(".vcmselectvbroom").fadeIn();
			jQuery(".vcmselectotaroom").removeClass("vcmselectedotaroom");
			jQuery(".vcmselectotaroom").find('.vcmselectotaroom-txt').text("<?php echo addslashes(JText::_('VCMSELECTOTAROOMTOLINK')); ?>");
			jQuery("#vcmotarselector"+idrota).find('.vcmselectotaroom-txt').text("<?php echo addslashes(JText::_('VCMSELECTEDOTAROOMTOLINK')); ?>");
			jQuery("#vcmotarselector"+idrota).addClass("vcmselectedotaroom");
			jQuery("#vcmrowrelota"+vcmid).animate({
				backgroundColor: "#ffff99"
			}, 1000, function() {
				jQuery("#vcmrowrelota"+vcmid).animate({
					backgroundColor: "transparent"
				}, 1000);
			});
		}
	}
	function vcmEndLinking (idrvb, namervb, fast_blink) {
		var vcmoldotaidr = jQuery("#vcmotahelper").val();
		var vcmoldattr = jQuery("#vcmotahelper").attr("rel");
		var vcmonlyidrota = vcmoldotaidr.split("_");
		if (vcmoldotaidr != '') {
			jQuery("#vcmrowrelota"+vcmoldattr).append("<td>"+idrvb+"</td><td>"+namervb+"</td>");
			jQuery("#vcmroomsynchhelperbox").append("<input type='hidden' name='vbroomsids[]' value='"+idrvb+"' id='inputvbr"+vcmoldattr+"' />");
			jQuery("#vcmotahelper").val("");
			jQuery("#vcmotahelper").attr("rel", "");

			if (!fast_blink) {
				// animate action
				jQuery("#vcmotarselector"+vcmonlyidrota[0]).removeClass("vcmselectedotaroom");
				jQuery("#vcmotarselector"+vcmonlyidrota[0]).find('.vcmselectotaroom-txt').text("<?php echo addslashes(JText::_('VCMSELECTOTAROOMTOLINK')); ?>");
				jQuery("#vcmrowrelota"+vcmoldattr).animate({
					backgroundColor: "#ffff99"
				}, 1000, function() {
					jQuery("#vcmrowrelota"+vcmoldattr).animate({
						backgroundColor: "transparent"
					}, 1000);
				});
			}
		}
		if (!fast_blink) {
			// animate action
			jQuery(".vcmselectvbroom").fadeOut();
		}
	}
	</script>

	<?php
	$valid_configuration = true;
	$use_chname = ucwords($channel['name']);
	if ($channel['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
		// overwrite channel name
		$use_chname = 'Google Hotel';
		$hinv_id = VikChannelManager::getHotelInventoryID();
		// check if hotels inventory has been submitted
		if (!VikChannelManager::checkIntegrityHotelDetails() || empty($hinv_id)) {
			// missing hotel details, do not proceed and display error
			$valid_configuration = false;
			?>
			<p class="err"><?php echo JText::_('VCM_NO_HOTELDETAILS'); ?></p>
			<p>
				<a href="index.php?option=com_vikchannelmanager&task=hoteldetails" class="btn vcm-config-btn"><i class="vboicn-home"></i> <?php echo JText::_('VCMMENUHOTEL') . ' - ' . JText::_('VCMMENUTACDETAILS'); ?></a>
			</p>
			<?php
		} else {
			// hotel inventory has been submitted, check when
			$hinv_create_dt = VikChannelManager::setHotelInventoryDate(null);
			if (!empty($hinv_create_dt) && (time() - strtotime($hinv_create_dt)) < 86400) {
				// hotel details submitted less than 24 hours ago
				$valid_configuration = false;
				?>
				<p class="err"><?php echo JText::sprintf('VCM_GOOGLEHOTEL_24H', $hinv_create_dt); ?></p>
				<?php
			}
		}
	} elseif ($channel['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
		// overwrite channel name
		$use_chname = 'Google Vacation Rentals';
		// get account key
		$channel['params'] = !empty($channel['params']) && !is_array($channel['params']) ? json_decode($channel['params'], true) : $channel['params'];
		$channel['params'] = is_array($channel['params']) ? $channel['params'] : [];
		$account_key = !empty($channel['params']) && !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::GOOGLEVR, '');
		if (!$account_key) {
			$valid_configuration = false;
			?>
			<p class="err"><?php echo JText::_('VCM_VRBO_NO_LISTINGS'); ?></p>
			<p>
				<a href="index.php?option=com_vikchannelmanager&view=googlevrlistings" class="btn vcm-config-btn"><i class="vboicn-home"></i> <?php echo JText::_('VCMMENUAIRBMNGLST'); ?></a>
			</p>
			<?php
		}
	} elseif ($channel['uniquekey'] == VikChannelManagerConfig::VRBOAPI) {
		// overwrite channel name
		$use_chname = 'Vrbo';
		// get account key
		$channel['params'] = !empty($channel['params']) && !is_array($channel['params']) ? json_decode($channel['params'], true) : $channel['params'];
		$channel['params'] = is_array($channel['params']) ? $channel['params'] : [];
		$account_key = !empty($channel['params']) && !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
		if (!$account_key) {
			$valid_configuration = false;
			?>
			<p class="err"><?php echo JText::_('VCM_VRBO_NO_LISTINGS'); ?></p>
			<p>
				<a href="index.php?option=com_vikchannelmanager&view=vrbolistings" class="btn vcm-config-btn"><i class="vboicn-home"></i> <?php echo JText::_('VCMMENUAIRBMNGLST'); ?></a>
			</p>
			<?php
		}
	}

	if ($valid_configuration) {
	?>
	<span class="vcmsynchspan">
		<a class="vcmsyncha" href="javascript: void(0);" id="vcmstartsynch"><?php echo (class_exists('VikBookingIcons') ? '<i class="' . VikBookingIcons::i('sync-alt') . '"></i> ' : '') . ($channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? JText::_('VCM_AIRBNB_DOSYNC') : JText::sprintf('VCMSTARTSYNCHROOMS', $use_chname)); ?></a>
	</span>
	<br clear="all"/><br clear="all"/>
<?php
	}
}
?>

<input type="hidden" id="vcmotahelper" value=""/>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<div id="vcmroomsynchhelperbox"></div>
	
	<div id="vcmroomsynchresponsebox"></div>
	
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikchannelmanager">
</form>
