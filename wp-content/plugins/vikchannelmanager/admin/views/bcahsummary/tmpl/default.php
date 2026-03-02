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
$hotelData = $this->hotelData;
$e4j_debug = $this->e4j_debug;

$sessionSet = !empty($this->sessionValues)? true : false;

$sessionValues = "";

if($sessionSet) {
	$sessionValues = $this->sessionValues;
}

$vik = new VikApplication(VersionListener::getID());

?>

<script>
jQuery(document).ready(function (){

	var hotelID = jQuery(".vcm-bcahs-hotel-select").val();

	jQuery(document.body).on("change", ".vcm-bcahs-hotel-select", function (){
		hotelID = jQuery(this).val();
	});

	jQuery(document.body).on("click", ".vcm-bcahs-send-button", function (){
		jQuery(".vcm-bcahs-send-button").prop({disabled:true});
		jQuery(".vcm-bcahs-status-button").prop({disabled:true});
		jQuery(".vcm-loading-overlay").show();
		jQuery(".vcm-bcahs-ajax-out").html("");
		var summaryType;
		if(jQuery(this).hasClass("vcm-bcahs-summary-button-check")){
			summaryType = "Check";
		}
		else if(jQuery(this).hasClass("vcm-bcahs-summary-button-open")){
			summaryType = "Open";
		}
		else if(jQuery(this).hasClass("vcm-bcahs-summary-button-closed")){
			summaryType = "Closed";
		}
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikchannelmanager", task: "bca.runHotelSummary", tmpl: "component", val: hotelID, sumtype: summaryType, e4j_debug: "<?php echo $e4j_debug;?>" }
		}).done(function(res) { 
			jQuery(".vcm-bcahs-send-button").prop({disabled:false});
			jQuery(".vcm-bcahs-status-button").prop({disabled:false});
			jQuery(".vcm-loading-overlay").hide();
			if(res.substr(0, 9) == 'e4j.error') {
				jQuery(".vcm-bcahs-ajax-out").html("<pre class='vcmpreerror'>"+res.replace("e4j.error.", "")+"</pre>");
			}else {
				console.log(res);
				var obj = JSON.parse(res);
				console.log(obj);
				if(obj.hasOwnProperty('Warnings')){
					jQuery(".vcm-bcahs-ajax-out").html("<pre>"+obj.Warnings+"</pre>");
				}
				else{
					jQuery(".vcm-bcahs-ajax-out").html("<strong style=\"color:#0F0\">Success!</strong>");
				}
			}
		}).fail(function() { 
			alert("<?php echo JText::_('VCMBCAHSAJAXERR');?>"); 
		});
	});

	jQuery(document.body).on("click", ".vcm-bcahs-status-button", function(){
		jQuery(".vcm-bcahs-send-button").prop({disabled:true});
		jQuery(".vcm-bcahs-status-button").prop({disabled:true});
		jQuery(".vcm-loading-overlay").show();
		jQuery(".vcm-bcahs-ajax-out").html("");
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikchannelmanager", task: "bca.runHotelSearch", tmpl: "component", val: hotelID, e4j_debug: "<?php echo $e4j_debug;?>" }
		}).done(function(res) { 
			jQuery(".vcm-bcahs-send-button").prop({disabled:false});
			jQuery(".vcm-bcahs-status-button").prop({disabled:false});
			jQuery(".vcm-loading-overlay").hide();
			if(res.substr(0, 9) == 'e4j.error') {
				jQuery(".vcm-bcahs-ajax-out").html("<pre class='vcmpreerror'>"+res.replace("e4j.error.", "")+"</pre>");
			}else {
				console.log(res);
				var obj = JSON.parse(res);
				console.log(obj);
				if(obj.hasOwnProperty('Warnings')){
					jQuery(".vcm-bcahs-ajax-out").html("<pre>"+obj.Warnings+"</pre>");
				}
				if(obj.hasOwnProperty('Success')){
					jQuery.each(obj['Success'],function(k,v){
						jQuery(".vcm-bcahs-ajax-out").append(k+": "+v+"</br>");
					});
				}
			}
		}).fail(function() { 
			alert("<?php echo JText::_('VCMBCAHSAJAXERR');?>"); 
		});
	});

});

</script>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-processing"><?php echo JText::_('VCMPROCESSING'); ?></div>
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<form method="POST" name="adminForm" id="adminForm" action="index.php">
	<input type="hidden" name="task" value="adminForm"/>
	<input type="hidden" name="option" value="com_vikchannelmanager"/>
	<input type="hidden" name="e4j_debug" value="<?php echo $e4j_debug;?>"/>
</form>
<div class="vcm-bcahs-container">
	<div class="vcm-bcahs-inner">
		<div class="vcm-bcahs-head">
			<h3>
				<?php echo JText::_('VCMBCAHSTITLE');?>
			</h3>
		</div>
		<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
		<div class="vcm-bcahs-item-container">
			<div class="vcm-bcahs-hotel-select-div">
				<select class="vcm-bcahs-hotel-select">
					<?php foreach ($hotelData as $key => $value) {
						echo "<option value=".$key." ";
						if($key == $sessionValues){
							echo "selected ";
						}
						echo ">".$value."</option>";
					} ?>
				</select>
			</div>
			<div class="vcm-bcahs-summary-buttons">
				<button type="button" class="btn btn-primary vcm-bcahs-send-button vcm-bcahs-summary-button-check"><i class="vboicn-checkmark"></i><?php echo JText::_('VCMBCAHSCHECK');?></button>
				<button type="button" class="btn btn-success vcm-bcahs-send-button vcm-bcahs-summary-button-open"><i class="vboicn-unlocked"></i><?php echo JText::_('VCMBCAHSOPEN');?></button>
				<button type="button" class="btn btn-danger vcm-bcahs-send-button vcm-bcahs-summary-button-closed"><i class="vboicn-lock"></i><?php echo JText::_('VCMBCAHSCLOSED');?></button>
				<button type="button" class="btn vcm-bcahs-status-button vcm-bcahs-summary-button-closed"><i class="vboicn-info"></i><?php echo JText::_('VCMBCAHSSTATUS');?></button>
			</div>
		</div>
		<div class="vcm-bcahs-ajax-out">
		</div>
	</div>
</div>
