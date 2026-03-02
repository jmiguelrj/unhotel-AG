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
$oldData = $this->oldData;
$roomsInfo = $this->roomsInfo;
$roomsNames = $this->roomsNames;
$imageTagCodes = $this->imageTagCodes;
$actionSelected = $this->actionSelected;
$amenityIndexes = $this->amenityIndexes;
$roomName = "";
$hotelID = "";
if($actionSelected!=-1&&!empty($actionSelected)){
	$roomName = explode("-", $actionSelected)[1];
	$hotelID = explode("-", $actionSelected)[2];
	$actionSelected = explode("-", $actionSelected)[0];
}
$sessionValues = $this->sessionValues;
$e4j_debug = $this->e4j_debug;

/*$roomsNames['RoomNames'][1][] = "Test 1";
$roomsNames['RoomNames'][1][] = "Test 2";
$roomsNames['RoomNames'][4][] = "Test 3";*/

if(!empty($sessionValues)){
	$oldData = $sessionValues;
}

if(empty($hotelID) && isset($oldData['Room']) && isset($oldData['Room'][$actionSelected]) && isset($oldData['Room'][$actionSelected]['hotelid'])){
	$hotelID = $oldData['Room'][$actionSelected]['hotelid'];
}

if($e4j_debug){
	echo "<strong>Session: </strong><pre>".print_r($sessionValues,true)."</pre>";
}
$vik = new VikApplication(VersionListener::getID());

?>
<script type="text/javascript">

	var roomsNames = <?php echo json_encode($roomsNames['RoomNames']); ?>;
	var AmenityIndex = <?php echo (!empty($oldData['Room'][$actionSelected]['amenity-index'])&&max($oldData['Room'][$actionSelected]['amenity-index'])!=0) ? max($oldData['Room'][$actionSelected]['amenity-index'])+1 : 1; ?>;
	var SubroomIndex = <?php echo (!empty($oldData['Room'][$actionSelected]['subroom-index'])&&max($oldData['Room'][$actionSelected]['subroom-index'])!=0) ? max($oldData['Room'][$actionSelected]['subroom-index'])+1 : 1; ?>;
	var ImageIndex = <?php echo (!empty($oldData['Room'][$actionSelected]['image-index'])&&max($oldData['Room'][$actionSelected]['image-index'])!=0) ? max($oldData['Room'][$actionSelected]['image-index'])+1 : 1; ?>;

	jQuery(document).ready(function(){
		
		jQuery('.vcm-bca-multi-select').select2({
			allowClear: false,
			placeholder: "<?php echo addslashes(JText::_('VCMBCAIMAGETAGS')); ?>",
			width: 300
		});

		jQuery(".vcm-bcarc-action-select").change(function(){
			jQuery(".vcm-bcarc-action-form").submit();
		});

		jQuery(".vcm-bcarc-room-type").change(function(){
			jQuery(".vcm-bcarc-room-name").find('option').remove();
			if(jQuery(this).val()==""){
				jQuery(".vcm-bcarc-room-name").append("<option value=\"\"><?php echo JText::_('VCMBCARCNOROOMTYPE');?></option>");
			}
			for(var roomType in roomsNames){
				if(roomType == jQuery(".vcm-bcarc-room-type").val()){
					roomsNames[roomType].forEach(function(name,index){
						jQuery(".vcm-bcarc-room-name").append("<option value=\""+name+"\">"+name+"</option>");
					});
				}
			};
			if(jQuery(this).val()=='Apartment'||jQuery(this).val()=='Suite'||jQuery(this).val()=='Chalet'||jQuery(this).val()=='Bungalow'||jQuery(this).val()=='Chalet'||jQuery(this).val()=='Holiday home'||jQuery(this).val()=='Villa'||jQuery(this).val()=='Mobile home'){
				jQuery(".vcm-bcarc-room-subrooms").show();
				jQuery(".vcm-bcarc-room-subrooms").find("input").prop({disabled:false});
				jQuery(".vcm-bcarc-room-subrooms").find("select").prop({disabled:false});
			}
			else{
				jQuery(".vcm-bcarc-room-subrooms").hide();
				jQuery(".vcm-bcarc-room-subrooms").find("input").prop({disabled:true});
				jQuery(".vcm-bcarc-room-subrooms").find("select").prop({disabled:true});
			}
		});

		jQuery(document.body).on('change', '.vcm-bcarc-subroom-occupancy', function(){
			var oldValue = Number(jQuery(this).parent().prev(".vcm-bcarc-subroom-occupancy-value").val());
			var newValue = Number(jQuery(".vcm-bcarc-room-occupancy").val()) - oldValue + Number(jQuery(this).val());
			<?php if($e4j_debug) {?>
				console.log("Old Value: "+oldValue+" of type: "+typeof(oldValue));
				console.log("New Value: "+newValue+" of type: "+typeof(newValue));
				console.log(jQuery(this));
				console.log(jQuery(this).parent().prev(".vcm-bcarc-subroom-occupancy-value"));
				console.log(jQuery(".vcm-bcarc-room-occupancy"));
			<?php } ?>
			jQuery(this).parent().prev(".vcm-bcarc-subroom-occupancy-value").val(jQuery(this).val());
			jQuery(".vcm-bcarc-room-occupancy").val(newValue);
		});

		jQuery(document.body).on('click', '.vcm-bcarc-delete-button', function(){
			jQuery(this).closest(".vcm-bcarc-entry-instance").remove();
		});

		jQuery(".vcm-bcarc-send-button").click(function(){
			jQuery(".vcm-bcarc-ajax-input").val("bca.makeRoomsXml");
			if(jQuery(this).hasClass("vcm-bcarc-Active-button")){
				jQuery(".vcm-bcarc-data-form").append("<input type=\"hidden\" name=\"bcarcAction\" value=\"Active\"/>");
			}
			else if(jQuery(this).hasClass("vcm-bcarc-Deactivated-button")){
				jQuery(".vcm-bcarc-data-form").append("<input type=\"hidden\" name=\"bcarcAction\" value=\"Deactivated\"/>");
			}
			jQuery(".vcm-bcarc-data-form").submit();
		});

		jQuery(document.body).on('change', '.vcm-bcarc-amenity-selector', function(){
			var amenityValues = ["1","10","21","63","69","92","129","214","228","234","254","262","5102","5104","5105","5106","5107","5124","5127"];
			if(jQuery.inArray(jQuery(this).val(),amenityValues)>-1){
				jQuery(this).parent().next().show().find(".vcm-bcarc-amenity-value-select").prop({disabled:false});
			}
			else{
				jQuery(this).parent().next().hide().find(".vcm-bcarc-amenity-value-select").prop({disabled:true});	
			}
		});

		jQuery(".vcm-bcarc-new-button").click(function(){
			if(jQuery(this).hasClass("vcm-bcarc-amenity")){
				var appendableText = "<div class=\"vcm-bcarc-entry-instance vcm-bcarc-amenity"+AmenityIndex+"\">"+
					"<input type=\"hidden\" name=\"amenity-index[]\" value=\""+AmenityIndex+"\"/>"+
					"<div class=\"vcm-bcarc-entry-contents\">"+
						"<div class=\"vcm-bcarc-subdetail\">"+
							"<label><?php echo JText::_('VCMBCAHAMENITY');?></label>"+
							"<select name=\"amenity"+AmenityIndex+"-selected-amenity\" class=\"vcm-bcarc-amenity-selector\">"+
								"<option value=\"\"></option>";
								<?php 
									foreach ($amenityIndexes as $key => $value) {
								?>
									appendableText += "<option value=\"<?php echo $value;?>\"><?php echo $key;?></option>";
								<?php
									}
								?>
				appendableText += "<optgroup label=\"<?php echo JText::_('VCMBCARCBEDDINGTYPE');?>\">"+
									"<option value=\"33\"><?php echo JText::_('VCMBCAHAMENTYPE22');?></option>"+
									"<option value=\"200\"><?php echo JText::_('VCMBCAHAMENTYPE86');?></option>"+
									"<option value=\"58\"><?php echo JText::_('VCMBCAHAMENTYPE33');?></option>"+
									"<option value=\"86\"><?php echo JText::_('VCMBCAHAMENTYPE47');?></option>"+
									"<option value=\"102\"><?php echo JText::_('VCMBCAHAMENTYPE162');?></option>"+
									"<option value=\"113\"><?php echo JText::_('VCMBCAHAMENTYPE59');?></option>"+
									"<option value=\"203\"><?php echo JText::_('VCMBCAHAMENTYPE87');?></option>"+
									"<option value=\"249\"><?php echo JText::_('VCMBCAHAMENTYPE100');?></option>"+
									"<option value=\"26\"><?php echo JText::_('VCMBCAHAMENTYPE18');?></option>"+
								"</optgroup>"+
							"</select>"+
						"</div>"+
						"<div class=\"vcm-bcarc-subdetail vcm-bcarc-amenity-value-div\" style=\"display:none;\">"+
							"<label><?php echo JText::_('VCMBCARCVALUE');?></label>"+
							"<select disabled class=\"vcm-bcarc-amenity-value-select\" name=\"amenity"+AmenityIndex+"-value\">"+
								"<option value=\"3\"><?php echo JText::_('VCMBCARCAMENITYVAL1'); ?></option>"+
								"<option value=\"4\"><?php echo JText::_('VCMBCARCAMENITYVAL2'); ?></option>"+
								"<option value=\"5\"><?php echo JText::_('VCMBCARCAMENITYVAL3'); ?></option>"+
							"</select>"+
						"</div>"+
						"<button type=\"button\" class=\"btn vcm-bcarc-delete-button\" id=\"vcm-bcarc-amenity"+AmenityIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>";

				jQuery(".vcm-bcarc-room-amenities-inner").append(appendableText);
				
				AmenityIndex++;

			}
			else if(jQuery(this).hasClass("vcm-bcarc-room-subroom")){
				jQuery(".vcm-bcarc-room-subroom-container").append("<div class=\"vcm-bcarc-room-item-inner vcm-bcarc-room-subroom-inner vcm-bcarc-entry-instance\">"+
					"<div class=\"vcm-bcarc-room-subroom-instance\">"+
						"<input type=\"hidden\" name=\"subroom-index[]\" value=\""+SubroomIndex+"\"/>"+
						"<div class=\"vcm-bcarc-room-row-item vcm-bcarc-room-subroom-item\">"+
							"<label><?php echo JText::_('VCMBCARCROOMTYPE');?></label>"+
							"<select class=\"vcm-bcarc-subroom-type-selector\" name=\"subroom"+SubroomIndex+"-type\">"+
								"<option value=\"Living Room\"><?php echo JText::_('VCMBCARCSUBROOMTYPE1'); ?></option>"+
								"<option value=\"Bedroom\"><?php echo JText::_('VCMBCARCSUBROOMTYPE2'); ?></option>"+
							"</select>"+
						"</div>"+
						"<input type=\"hidden\" value=0 disabled class=\"vcm-bcarc-subroom-occupancy-value\"/>"+
						"<div class=\"vcm-bcarc-room-subroom-item\">"+
							"<label><?php echo JText::_('VCMBCARCMAXGUESTS');?></label>"+
							"<input type=\"number\" min=\"1\" max=\"20\" class=\"vcm-bcarc-subroom-occupancy\" name=\"subroom"+SubroomIndex+"-occupancy\"/>"+
						"</div>"+
						"<div class=\"vcm-bcarc-room-subroom-bedroom-info\">"+
							"<div class=\"vcm-bcarc-room-subroom-item\">"+
								"<label><?php echo JText::_('VCMBCARCBEDDINGTYPE');?></label>"+
								"<select name=\"subroom"+SubroomIndex+"-bedding\">"+
									"<option value=\"33\"><?php echo JText::_('VCMBCAHAMENTYPE22');?></option>"+
									"<option value=\"200\"><?php echo JText::_('VCMBCAHAMENTYPE86');?></option>"+
									"<option value=\"58\"><?php echo JText::_('VCMBCAHAMENTYPE33');?></option>"+
									"<option value=\"86\"><?php echo JText::_('VCMBCAHAMENTYPE47');?></option>"+
									"<option value=\"102\"><?php echo JText::_('VCMBCAHAMENTYPE162');?></option>"+
									"<option value=\"113\"><?php echo JText::_('VCMBCAHAMENTYPE59');?></option>"+
									"<option value=\"203\"><?php echo JText::_('VCMBCAHAMENTYPE87');?></option>"+
									"<option value=\"249\"><?php echo JText::_('VCMBCAHAMENTYPE100');?></option>"+
									"<option value=\"26\"><?php echo JText::_('VCMBCAHAMENTYPE18');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcarc-room-subroom-item vcm-bcarc-subdetail-checkbox-detail\">"+
								"<label><?php echo JText::_('VCMBCARCPRIVBATHROOM');?></label>"+
								"<input type=\"checkbox\" name=\"subroom"+SubroomIndex+"-privbathroom\"/>"+
							"</div>"+
						"</div>"+
						"<div class=\"vcm-bcarc-room-item-btns\">"+
							"<button type=\"button\" class=\"vcm-bcarc-delete-button btn\"><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>"+
				"</div>");
				SubroomIndex++;
			}
		});

		jQuery(".vcm-bcarc-room-type").trigger("change");
		jQuery(".vcm-bcarc-amenity-selector").trigger("change");
		jQuery(".vcm-bcarc-room-name").val("<?php echo isset($oldData['Room'][$actionSelected]['roomName']) ? $oldData['Room'][$actionSelected]['roomName'] : ''; ?>");
	});

	function uploadImageAJAX(input) {
		var index = jQuery(input).data('index');
		jQuery(".vcm-loading-overlay").show();
		var formData = new FormData( jQuery('.vcm-bcarc-data-form')[0] );
		<?php if($e4j_debug) {?>
			console.log(formData);
			console.log(jQuery(input));
		<?php } ?>
		jQuery.noConflict();
		var imgurl="";
		
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_vikchannelmanager&task=upload_image_ajax&tmpl=component&minwidth=2048",
			data: formData,
			cache: false,
			processData: false,
			contentType: false
		}).done(function(resp){
			jQuery(".vcm-loading-overlay").hide();
			var obj = JSON.parse(resp);
			<?php if($e4j_debug) {?>
				console.log(resp); 
				console.log(obj);
			<?php } ?>
			if( obj[0] == 1 ) {
				if(jQuery("#image-status"+index).length!=0){
					jQuery(input).next('#image-status'+index).remove();
				}
				/**
				 * @wponly  The Booking.com Contents API use the directory below, which is in a different path for WP
				 */
				imgurl = '<?php echo VCM_ADMIN_URI;?>assets/vcm/'+obj[2];
				
				var appendableText = "<div class=\"vcm-bcarc-entry-instance vcm-bcarc-image"+ImageIndex+"\">"+
					"<input type=\"hidden\" name=\"image-index[]\" value=\""+ImageIndex+"\"/>"+
					"<div class=\"vcm-bcarc-image-instance\">"+
						"<div class=\"vcm-bcarc-entry-header\">"+
							"<div class=\"vcm-bcarc-image-holder\">"+
								"<img src=\""+imgurl+"\"/>"+
							"</div>"+
						"</div>"+
						"<div class=\"vcm-bcarc-entry-contents vcm-bcarc-image"+ImageIndex+"-div\">"+
							"<div class=\"vcm-bcarc-detail\">"+
								"<div class=\"vcm-bcarc-subdetail\">"+
									"<label><?php echo JText::_('VCMBCAHIMGURL');?></label>"+
									"<input type=\"text\" disabled name=\"image"+ImageIndex+"-image-url-shown\" value=\""+imgurl+"\" size=\"100\"/>"+
									"<input type=\"hidden\" name=\"image"+ImageIndex+"-image-url\" value=\""+imgurl+"\"/>"+
								"</div>"+
							"</div>"+
							"<div class=\"vcm-bcarc-detail\">"+
								"<div class=\"vcm-bcarc-subdetail\">"+
									"<label><?php echo JText::_('VCMBCAHIMGTAG');?></label>"+
									"<select name=\"image"+ImageIndex+"-tag[]\" multiple class=\"vcm-bca-multi-select\">";
										<?php 
											foreach ($imageTagCodes as $key => $value) {
										?>
										appendableText += "<option value=\"<?php echo $value;?>\"><?php echo $key;?></option>";
										<?php
											}
										?>
				appendableText += "</select>"+
								"</div>"+
							"</div>"+
						"</div>"+
						"<div class=\"vcm-bcarc-image-controller\">"+
							"<button type=\"button\" class=\"btn vcm-bcarc-delete-button\" id=\"vcm-bcarc-image"+ImageIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>";
					
				jQuery(".vcm-bcarc-images-div").append(appendableText);

				jQuery('.vcm-bcarc-image'+ImageIndex+'-div').find(".vcm-bca-multi-select").select2({
					allowClear: false,
					placeholder: "<?php echo addslashes(JText::_('VCMBCAIMAGETAGS')); ?>",
					width: 300
				});
					
				ImageIndex++;
				jQuery('#vcm-bcarc-image-input').wrap('<form>').closest('form').get(0).reset();
  				jQuery('#vcm-bcarc-image-input').unwrap();

			} else {
				if(jQuery("#image-status"+index).length==0){
					jQuery(input).after('<img src="<?php echo VCM_ADMIN_URI; ?>assets/css/images/no.png" id="image-status'+index+'" />');
				}
				else{
				}
				if(obj[1].length!=0){
					alert(obj[1]);
				}
				else if(obj[0]==-3){
					alert("<?php echo JText::_('VCMBCARCWRONGSIZE')?>");
				}
				else{
					alert("<?php echo JText::_('VCMBCARCUNKNOWNERROR')?>");
				}
			}
			
		});
	}
</script>
<div class="vcm-bcarc-container">
	<div class="vcm-bcarc-inner">
		<form class="vcm-bcarc-action-form" action="index.php" method="POST">
			<input type="hidden" name="task" value="bcarcont"/>
			<input type="hidden" name="option" value="com_vikchannelmanager"/>
			<input type="hidden" name="e4j_debug" value="<?php echo $e4j_debug;?>"/>
			<div class="vcm-bcarc-header">
				<h3><?php echo JText::_('VCMBCARCTITLE'); ?></h3>
				<select class="vcm-bcarc-action-select" name="action-option">
					<option value="0-0-0"><?php echo JText::_('VCMBCARCSELECTACT'); ?></option>
					<?php
						if(!empty($roomsInfo)){
							foreach ($roomsInfo['RoomId'] as $key => $value) {
								echo "<option value=\"".$value."-".$roomsInfo['RoomName'][$key]."-".$roomsInfo['HotelId'][$key]."\">".sprintf(JText::_('VCMBCARCEDITACT'),$value,$roomsInfo['RoomName'][$key],$roomsInfo['HotelId'][$key])."</option>";
							}
						}
					?>
					<option value="-1"><?php echo JText::_('VCMBCARCNEWACT'); ?></option>
				</select>
			</div>
		</form>

		<div class="vcm-loading-overlay">
			<div class="vcm-loading-processing"><?php echo JText::_('VCMPROCESSING'); ?></div>
			<div class="vcm-loading-dot vcm-loading-dot1"></div>
			<div class="vcm-loading-dot vcm-loading-dot2"></div>
			<div class="vcm-loading-dot vcm-loading-dot3"></div>
			<div class="vcm-loading-dot vcm-loading-dot4"></div>
			<div class="vcm-loading-dot vcm-loading-dot5"></div>
		</div>

		<form class="vcm-bcarc-data-form" name="adminForm" id="adminForm" enctype="multipart/form-data" action="index.php" method="POST">
			<input type="hidden" name="roomValues" value="<?php if($actionSelected == -1){echo -1;}else{echo $actionSelected."-".$roomName."-".$hotelID;}?>"/>
			<input type="hidden" name="hotelID" value="<?php echo $hotelID;?>"/>
			<input type="hidden" name="e4j_debug" value="<?php echo $e4j_debug;?>"/>
			<input type="hidden" name="status" value="<?php echo (!empty($oldData['Room'][$actionSelected]['status'])? $oldData['Room'][$actionSelected]['status'] : 'Active');?>"/>
			<input type="hidden" name="task" class="vcm-bcarc-ajax-input" value="upload_image_ajax"/>
			<input type="hidden" name="option" value="com_vikchannelmanager"/>
			<div class="vcm-bcarc-rooms-container" style="<?php echo empty($actionSelected)? "display:none;" : "active" ?>">
				<div class="vcm-bcarc-room-header">
					<h3><?php echo !empty($roomName)? $roomName : "New Room"; ?></h3>
					<h4 style="<?php echo $actionSelected=="-1"? "display:none;" : "" ?>"><?php  echo sprintf(JText::_('VCMBCARCROOMDETAILS'),$actionSelected,$hotelID);?></h4>
				</div>
				<div class="vcm-bcarc-room-inner">
					<div class="vcm-bcarc-room-content">
						<?php if($actionSelected!=-1) {
						$notStatus = (isset($oldData['Room'][$actionSelected]['status']) && $oldData['Room'][$actionSelected]['status']=='Active'? 'Deactivated' : 'Active');?>
							<div class="vcm-bcarc-room-info vcm-bcarc-notif-buttons">
								<button type="button" class="vcm-bcarc-<?php echo $notStatus; ?>-button vcm-bcarc-send-button btn <?php echo $notStatus=="Active"? "btn-success" : "btn-danger";?>"><?php echo $notStatus=='Active'? JText::_('VCMBCARCACTIVATE') : JText::_('VCMBCARCDEACTIVATE');?></button>
							</div>
						<?php } ?>
						<!-- Sezione 1 nella mail -->
						<div class="vcm-bcarc-room-info vcm-bcarc-room-item">
							<div class="vcm-bcarc-room-item-header vcm-bcarc-room-info-header">
								<span><?php echo JText::_('VCMBCARCROOMINFO'); ?></span>
							</div>
							<div class="vcm-bcarc-room-info-container">
								<div class="vcm-bcarc-room-item-inner vcm-bcarc-room-info-inner"> 
									<div class="vcm-bcarc-room-info-item">
										<label><?php echo JText::_('VCMBCARCMAXOCCUP'); ?></label>
										<input type="number" name="maxOccupancy" class="vcm-bcarc-room-occupancy" value="<?php echo isset($oldData['Room'][$actionSelected]['maxOccupancy']) ? $oldData['Room'][$actionSelected]['maxOccupancy'] : ''; ?>"/>
									</div>
									<div class="vcm-bcarc-room-info-item vcm-bcarc-subdetail-checkbox-detail">
										<label><?php echo JText::_('VCMBCARCCRIBS'); ?></label>
										<input type="checkbox" name="cribs" <?php echo isset($oldData['Room'][$actionSelected]['cribs'])? "checked" : ""; ?>/>
									</div>
									<div class="vcm-bcarc-room-info-item vcm-bcarc-subdetail-checkbox-detail">
										<label><?php echo JText::_('VCMBCARCADDGUESTS'); ?></label>
										<input type="checkbox" name="additionalGuests" <?php echo isset($oldData['Room'][$actionSelected]['additionalGuests'])? "checked" : ""; ?>/>
									</div>
									<div class="vcm-bcarc-room-info-item">
										<label><?php echo JText::_('VCMBCARCMAXROLL'); ?></label>
										<input type="number" name="maxRollaways" value="<?php echo isset($oldData['Room'][$actionSelected]['maxRollaways']) ? $oldData['Room'][$actionSelected]['maxRollaways'] : ''; ?>"/>
									</div>
									<div class="vcm-bcarc-room-info-item vcm-bcarc-subdetail-checkbox-detail">
										<label><?php echo JText::_('VCMBCARCNONSMOKING'); ?></label>
										<input type="checkbox" name="noSmoking" <?php echo isset($oldData['Room'][$actionSelected]['noSmoking'])? "checked" : ""; ?>/>
									</div>
									<div class="vcm-bcarc-room-info-item">
										<label><?php echo JText::_('VCMBCARCROOMTYPE'); ?></label>
										<select name="roomType" class="vcm-bcarc-room-type">
											<option value=""></option>
											<option value="Apartment" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Apartment"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE1'); ?></option>
											<option value="Quadruple" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Quadruple"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE2'); ?></option>
											<option value="Suite" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Suite"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE3'); ?></option>
											<option value="Triple" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Triple"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE4'); ?></option>
											<option value="Twin" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Twin"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE5'); ?></option>
											<option value="Double" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Double"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE6'); ?></option>
											<option value="Single" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Single"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE7'); ?></option>
											<option value="Studio" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Studio"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE8'); ?></option>
											<option value="Family" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Family"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE9'); ?></option>
											<option value="Dormitory room" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Dormitory room"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE10'); ?></option>
											<option value="Bed in Dormitory" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Bed in Dormitory"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE11'); ?></option>
											<option value="Bungalow" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Bungalow"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE12'); ?></option>
											<option value="Chalet" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Chalet"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE13'); ?></option>
											<option value="Holiday home" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Holiday home"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE14'); ?></option>
											<option value="Villa" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Villa"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE15'); ?></option>
											<option value="Mobile home" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Mobile home"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE16'); ?></option>
											<option value="Tent" <?php echo isset($oldData['Room'][$actionSelected]['roomType']) && $oldData['Room'][$actionSelected]['roomType'] == "Tent"? 'selected' : ''; ?>><?php echo JText::_('VCMBCARCROOMTYPE17'); ?></option>
										</select>
									</div>
									<div class="vcm-bcarc-room-info-item">
										<label><?php echo JText::_('VCMBCARCSIZEMEASUREMENT'); ?></label>
										<input type="number" name="sizeMeasure" value="<?php echo isset($oldData['Room'][$actionSelected]['sizeMeasure']) ? $oldData['Room'][$actionSelected]['sizeMeasure'] : ''; ?>"/>
										<label class="vcm-bcarc-label-unit"><?php echo JText::_('VCMBCARCSIZEMEASUREMENTUNIT');?></label>
									</div>
								</div>
							</div>
						</div>
						<!-- Sezione 2 nella mail -->
						<div class="vcm-bcarc-room-amenities vcm-bcarc-room-item">
							<div class="vcm-bcarc-room-amenities-header vcm-bcarc-room-item-header">
								<span><?php echo JText::_('VCMBCARCROOMAMENITIES'); ?></span>
								<div class="vcm-bcarc-room-item-btns">
									<button type="button" class="vcm-bcarc-button btn vcm-bcarc-amenity vcm-bcarc-new-button"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
							</div>
							<div class="vcm-bcarc-room-amenities-container vcm-bcarc-room-item-container">
								<div class="vcm-bcarc-room-item-inner vcm-bcarc-room-amenities-inner"> 
									<!-- Amenity Qui -->
									<?php
										if(!empty($oldData['Room'][$actionSelected]['amenity-index'])){
											foreach ($oldData['Room'][$actionSelected]['amenity-index'] as $index) {
												$valueAmenities = ["63","69","234","254","262","5102","5104","5105","5106","5107","5124","5127"];
												$valueAmenity = in_array($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'], $valueAmenities);
												echo "<div class=\"vcm-bcarc-entry-instance vcm-bcarc-amenity".$index."\">
													<input type=\"hidden\" name=\"amenity-index[]\" value=\"".$index."\"/>
													<div class=\"vcm-bcarc-entry-contents\">
														<div class=\"vcm-bcarc-subdetail\">
															<label>".JText::_('VCMBCAHAMENITY')."</label>
															<select name=\"amenity".$index."-selected-amenity\" class=\"vcm-bcarc-amenity-selector\">
																<option value=\"\"></option>";
																foreach ($amenityIndexes as $key => $value) {
																	echo "<option value=\"".$value."\" ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == $value? "selected":"").">".$key."</option>";
																}
																echo "<optgroup label=\"".JText::_('VCMBCARCBEDDINGTYPE')."\">
																	<option value=\"33\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 33? "selected" : "").">".JText::_('VCMBCAHAMENTYPE22')."</option>
																	<option value=\"200\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 200? "selected" : "").">".JText::_('VCMBCAHAMENTYPE86')."</option>
																	<option value=\"58\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 58? "selected" : "").">".JText::_('VCMBCAHAMENTYPE33')."</option>
																	<option value=\"86\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 86? "selected" : "").">".JText::_('VCMBCAHAMENTYPE47')."</option>
																	<option value=\"102\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 102? "selected" : "").">".JText::_('VCMBCAHAMENTYPE162')."</option>
																	<option value=\"113\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 113? "selected" : "").">".JText::_('VCMBCAHAMENTYPE59')."</option>
																	<option value=\"203\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 203? "selected" : "").">".JText::_('VCMBCAHAMENTYPE87')."</option>
																	<option value=\"249\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 249? "selected" : "").">".JText::_('VCMBCAHAMENTYPE100')."</option>
																	<option value=\"26\"  ".($oldData['Room'][$actionSelected]['amenities'][$index]['selectedAmenity'] == 26? "selected" : "").">".JText::_('VCMBCAHAMENTYPE18')."</option>
																</optgroup>
															</select>
														</div>".
														/*<div class=\"vcm-bcarc-subdetail\">
															<label>".JText::_('VCMBCAHQUANTITY')."</label>
															<input type=\"number\" name=\"amenity".$index."-quantity\" id=\"vcm-bcarc-amenity".$index."-quantity\" class=\"vcm-bcarc-amenity-quantity\" min=\"1\" value=\"".$oldData['Room'][$actionSelected]['amenities'][$index]['quantity']."\"/>
														</div>*/
														"<div class=\"vcm-bcarc-subdetail vcm-bcarc-amenity-value-div\" ".($valueAmenity? "" : "style=\"display:none;\"").">
															<label>".JText::_('VCMBCARCVALUE')."</label>
															<select ".($valueAmenity? "" : "disabled")." class=\"vcm-bcarc-amenity-value-select\" name=\"amenity".$index."-value\">
																<option value=\"3\" ".($oldData['Room'][$actionSelected]['amenities'][$index]['value'] == 3? "selected" : "").">".JText::_('VCMBCARCAMENITYVAL1')."</option>
																<option value=\"4\" ".($oldData['Room'][$actionSelected]['amenities'][$index]['value'] == 4? "selected" : "").">".JText::_('VCMBCARCAMENITYVAL2')."</option>
																<option value=\"5\" ".($oldData['Room'][$actionSelected]['amenities'][$index]['value'] == 5? "selected" : "").">".JText::_('VCMBCARCAMENITYVAL3')."</option>
															</select>
														</div>
														<button type=\"button\" class=\"btn vcm-bcarc-delete-button\" id=\"vcm-bcarc-amenity".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
													</div>
												</div>";
											}
										}
									?>
								</div>
							</div>
						</div>
						<!-- Sezione 3 nella mail -->
						<div class="vcm-bcarc-room-description vcm-bcarc-room-item">
							<div class="vcm-bcarc-room-description-header vcm-bcarc-room-item-header">
								<span><?php echo JText::_('VCMBCARCROOMDESC'); ?></span>
							</div>
							<div class="vcm-bcarc-room-description-container vcm-bcarc-room-item-container">
								<div class="vcm-bcarc-room-item-inner vcm-bcarc-room-description-inner"> 
									<div class="vcm-bcarc-room-name-div">
										<span><?php echo JText::_('VCMBCARCROOMNAME'); ?></span>
										<select name="roomName" class="vcm-bcarc-room-name">
											<option value=""><?php echo JText::_('VCMBCARCNOROOMTYPE'); ?></option>
										</select>
									</div>
									<div class="vcm-bcarc-room-image-upload-div">
										<span><?php echo JText::_('VCMBCARCROOMIMAGES'); ?></span>
										<input type="file" data-index="ImageIndex" name="vcm-image-upload" id="vcm-bcarc-image-input" size="35" onChange="uploadImageAJAX(this);"/>
										<div class="vcm-bcarc-room-description-images-container vcm-bcarc-images-div">
											<!--Un esempio di immagine-->
											<?php
												if(!empty($oldData['Room'][$actionSelected]['image-index'])){
													foreach ($oldData['Room'][$actionSelected]['image-index'] as $index) {
														echo "<div class=\"vcm-bcarc-entry-instance vcm-bcarc-image".$index."\">
														<input type=\"hidden\" name=\"image-index[]\" value=\"".$index."\"/>
														<div class=\"vcm-bcarc-image-instance\">
															<div class=\"vcm-bcarc-entry-header\">
																<div class=\"vcm-bcarc-image-holder\">
																	<img src=\"".$oldData['Room'][$actionSelected]['images'][$index]['url']."\"/>
																</div>
															</div>
															<div class=\"vcm-bcarc-entry-contents vcm-bcarc-image".$index."-div\">
																<div class=\"vcm-bcarc-detail\">
																	<div class=\"vcm-bcarc-subdetail\">
																		<label>".JText::_('VCMBCAHIMGURL')."</label>
																		<input type=\"text\" disabled name=\"image".$index."-image-url-shown\" value=\"".$oldData['Room'][$actionSelected]['images'][$index]['url']."\" size=\"100\"/>
																		<input type=\"hidden\" name=\"image".$index."-image-url\" value=\"".$oldData['Room'][$actionSelected]['images'][$index]['url']."\"/>
																	</div>
																</div>
																<div class=\"vcm-bcarc-detail\">
																	<div class=\"vcm-bcarc-subdetail\">
																		<label>".JText::_('VCMBCAHIMGTAG')."</label>
																		<select name=\"image".$index."-tag[]\" multiple class=\"vcm-bca-multi-select\">";
																			foreach ($imageTagCodes as $key => $value) {
																				echo "<option value=\"".$value."\" ";
																				if(is_array($oldData['Room'][$actionSelected]['images'][$index]['tag'])) {
																					foreach ($oldData['Room'][$actionSelected]['images'][$index]['tag'] as $tag) {
																						echo $tag == $value? "selected" : "";
																					}
																				}
																				echo ">".$key."</option>";
																			}
																		echo "</select>
																	</div>
																</div>
															</div>
															<div class=\"vcm-bcarc-image-controller\">
																<button type=\"button\" class=\"btn vcm-bcarc-delete-button\" id=\"vcm-bcarc-image".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
															</div>
														</div>
													</div>";
													}
												}	
											?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- Sezione 4 nella mail -->
						<div class="vcm-bcarc-room-subrooms">
							<div class="vcm-bcarc-room-subroom-header vcm-bcarc-room-item-header">
								<span><?php echo JText::_('VCMBCARCSUBROOMS'); ?></span>
								<div class="vcm-bcarc-room-item-btns">
									<button type="button" class="vcm-bcarc-button vcm-bcarc-room-subroom btn vcm-bcarc-new-button"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD'); ?></button>
								</div>
							</div>
							<div class="vcm-bcarc-room-subroom-container">
								<!-- Un esempio di subroom -->
								<?php
									if(!empty($oldData['Room'][$actionSelected]['subroom-index'])){
										foreach ($oldData['Room'][$actionSelected]['subroom-index'] as $index) {
												echo "<div class=\"vcm-bcarc-room-item-inner vcm-bcarc-room-subroom-inner vcm-bcarc-entry-instance\">
												<div class=\"vcm-bcarc-room-subroom-instance\">
													<input type=\"hidden\" name=\"subroom-index[]\" value=\"".$index."\"/>
													<div class=\"vcm-bcarc-room-row-item vcm-bcarc-room-subroom-item\">
														<label>".JText::_('VCMBCARCROOMTYPE')."</label>
														<select class=\"vcm-bcarc-subroom-type-selector\" name=\"subroom".$index."-type\">
															<option value=\"Living Room\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['type'] == 'Living Room'? "selected" : "").">".JText::_('VCMBCARCSUBROOMTYPE1')."</option>
															<option value=\"Bedroom\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['type'] == 'Bedroom'? "selected" : "").">".JText::_('VCMBCARCSUBROOMTYPE2')."</option>
														</select>
													</div>
													<input type=\"hidden\" value=\"".$oldData['Room'][$actionSelected]['subrooms'][$index]['occupancy']."\" disabled class=\"vcm-bcarc-subroom-occupancy-value\"/>
													<div class=\"vcm-bcarc-room-subroom-item\">
														<label>".JText::_('VCMBCARCMAXGUESTS')."</label>
														<input type=\"number\" min=\"1\" max=\"20\" class=\"vcm-bcarc-subroom-occupancy\" value=\"".$oldData['Room'][$actionSelected]['subrooms'][$index]['occupancy']."\" name=\"subroom".$index."-occupancy\"/>
													</div>
													<div class=\"vcm-bcarc-room-subroom-bedroom-info\">
														<div class=\"vcm-bcarc-room-subroom-item\">
															<label>".JText::_('VCMBCARCBEDDINGTYPE')."</label>
															<select name=\"subroom".$index."-bedding\">
																<option value=\"33\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 33? "selected" : "").">".JText::_('VCMBCAHAMENTYPE22')."</option>
																<option value=\"200\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 200? "selected" : "").">".JText::_('VCMBCAHAMENTYPE86')."</option>
																<option value=\"58\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 58? "selected" : "").">".JText::_('VCMBCAHAMENTYPE33')."</option>
																<option value=\"86\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 86? "selected" : "").">".JText::_('VCMBCAHAMENTYPE47')."</option>
																<option value=\"102\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 102? "selected" : "").">".JText::_('VCMBCAHAMENTYPE162')."</option>
																<option value=\"113\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 113? "selected" : "").">".JText::_('VCMBCAHAMENTYPE59')."</option>
																<option value=\"203\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 203? "selected" : "").">".JText::_('VCMBCAHAMENTYPE87')."</option>
																<option value=\"249\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 249? "selected" : "").">".JText::_('VCMBCAHAMENTYPE100')."</option>
																<option value=\"26\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['bedding'] == 26? "selected" : "").">".JText::_('VCMBCAHAMENTYPE18')."</option>
															</select>
														</div>
														<div class=\"vcm-bcarc-room-subroom-item vcm-bcarc-subdetail-checkbox-detail\">
															<label>".JText::_('VCMBCARCPRIVBATHROOM')."</label>
															<input type=\"checkbox\" name=\"subroom".$index."-privbathroom\" ".($oldData['Room'][$actionSelected]['subrooms'][$index]['privbathroom'] == 1? "checked" : "")."/>
														</div>
													</div>
													<div class=\"vcm-bcarc-room-item-btns\">
														<button type=\"button\" class=\"vcm-bcarc-delete-button btn\">".JText::_('VCMBCAHDELETE')."</button>
													</div>
												</div>
											</div>";
										}
									}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>