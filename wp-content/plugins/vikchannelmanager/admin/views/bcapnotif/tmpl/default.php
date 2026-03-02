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
$validPolicies = $this->validPolicies;
$validRatePlans = $this->validRatePlans;
$actionSelected = $this->actionSelected;
$possiblePolicies = $this->possiblePolicies;
$e4j_debug = $this->e4j_debug;
$sessionValues = $this->sessionValues;
$newRoom = $this->newRoom;
$newRoomInfo = $this->newRoomInfo;

$mainframe = JFactory::getApplication();

$roomID = "";
$roomName = "";
$hotelID = "";

if(!empty($actionSelected)){
	if(empty($roomsInfo)){
		$mainframe->enqueueMessage("Error! No rooms found! Save and retry","error");
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont");
		die;
	}

	if(empty($validPolicies)){
		$mainframe->enqueueMessage("Error! No cancel policies found! Save and retry","error");
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcahcont&tab=policies");
		die;
	}

	if(empty($validRatePlans)){
		$mainframe->enqueueMessage("Error! No rate plans found! Save and retry","error");
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarplans");
		die;
	}
	$roomID = $actionSelected[0];
	$roomName = $actionSelected[1];
	$hotelID = $actionSelected[2];
}



if($e4j_debug){
	echo "<h3>All values</h3></br>";
	echo "<strong>Request: </strong><pre>".print_r($_REQUEST,true)."</pre>";
	echo "<strong>Database Data: </strong><pre>".print_r($oldData,true)."</pre>";
	echo "<strong>Rooms Info: </strong><pre>".print_r($roomsInfo,true)."</pre>";
	echo "<strong>Valid Policies: </strong><pre>".print_r($validPolicies,true)."</pre>";
	echo "<strong>Valid Rate Plans: </strong><pre>".print_r($validRatePlans,true)."</pre>";
	echo "<strong>Possible Policies: </strong><pre>".print_r($possiblePolicies,true)."</pre>";
	echo "<strong>Action Selected: </strong><pre>".print_r($actionSelected,true)."</pre>";
	echo "<strong>Old Data: </strong><pre>".print_r($oldData,true)."</pre>";
	echo "<strong>Session: </strong><pre>".print_r($sessionValues,true)."</pre>";
	echo "<strong>New Room Info: </strong><pre>".print_r($newRoomInfo,true)."</pre>";
}

?>
<script type="text/javascript">
	
	jQuery(document).ready(function(){

		jQuery(".vcm-bcapn-action-select").change(function(){
			jQuery(".vcm-bcapn-header-form").submit();
		});

		jQuery(".vcm-bcapn-product-name").click(function(){
			jQuery(this).parent().next(".vcm-bcapn-product-details").toggle();
			if(jQuery(this).find("i").hasClass("vboicn-enlarge")){
				jQuery(this).find("i").removeClass("vboicn-enlarge");
				jQuery(this).find("i").addClass("vboicn-shrink");
			}
			else if(jQuery(this).find("i").hasClass("vboicn-shrink")){
				jQuery(this).find("i").removeClass("vboicn-shrink");
				jQuery(this).find("i").addClass("vboicn-enlarge");
			}
		});

		jQuery(".vcm-bcapn-delete").click(function(){
			var ratePlanValue = jQuery(this).parent().next().find(".ratePlanValue").html();
			jQuery(this).closest("form").append("<input type=\"hidden\" class=\"vcm-bcapn-product-rate-plan\" name=\"productRatePlan\" value="+ratePlanValue+"</input>");
			jQuery(this).closest("form").submit();
		});
	});
</script>
<div class="vcm-bcapn-container">
	<div class="vcm-bcapn-inner">
		<form class="vcm-bcapn-header-form" method="POST" action="index.php">
			<input type="hidden" name="task" value="bcapnotif"/>
			<input type="hidden" name="option" value="com_vikchannelmanager"/>
			<input type="hidden" name="e4j_debug" value="<?php echo $e4j_debug;?>"/>
			<div class="vcm-bcapn-header-div">
				<h3><?php echo JText::_('VCMBCAPNROOMRATES'); ?></h3>
				<div class="vcm-bcapn-header-select" style="<?php echo $newRoom? "display:none;" : "" ?>">
					<select name="selected-option" class="vcm-bcapn-action-select" <?php echo $newRoom? "disabled" : "" ?>>
						<option value="0"><?php echo JText::_('VCMBCARCSELECTACT'); ?></option>
						<?php
							if(!empty($roomsInfo)){
								foreach ($roomsInfo['RoomId'] as $key => $value) {
									echo "<option value=\"".$value."-".$roomsInfo['RoomName'][$key]."-".$roomsInfo['HotelId'][$key]."\">".sprintf(JText::_('VCMBCARCEDITACT'),$value,$roomsInfo['RoomName'][$key],$roomsInfo['HotelId'][$key])."</option>";
								}
							}
							if(!empty($newRoomInfo)){
								$newRoomInfo = explode("-", $newRoomInfo);
								echo "<option value=\"".$newRoomInfo[0]."-".$newRoomInfo[1]."-".$newRoomInfo[2]."\">".sprintf(JText::_('VCMBCARCEDITACT'),$newRoomInfo[0],$newRoomInfo[1],$newRoomInfo[2])."</option>";
							}
						?>
					</select>
				</div>
			</div>
		</form>
		<div class="vcm-bcapn-body-container" style="<?php echo $actionSelected==""? "display:none;" : "" ?>">
			<form class="vcm-bcapn-body-form" method="POST" name="adminForm" id="adminForm" action="index.php">
				<input type="hidden" name="e4j_debug" value="<?php echo $e4j_debug;?>"/>
				<input type="hidden" name="task" value="bcapnotif"/>
				<input type="hidden" name="option" value="com_vikchannelmanager"/>
				<input type="hidden" name="selected-action" value="<?php echo $roomID."-".$roomName."-".$hotelID;?>"/>
				<input type="hidden" name="new-room" value="<?php echo $newRoom? $newRoom : 0 ?>"/>
				<div class="vcm-bcapn-body-inner">
					<div class="vcm-bcapn-description-div">
						<h3><?php echo sprintf(JText::_('VCMBCAPNRNAME'),$roomName); ?></h3>
						<h4><?php echo sprintf(JText::_('VCMBCAPNRID'),$roomID); ?></h4>
					</div>
					<div class="vcm-bcapn-rate-plans">
						<label class="vcm-bcapn-label"><?php echo JText::_('VCMBCAPNRATEPLANS'); ?></label>
						<div class="vcm-bcapn-rate-plans-select">
							<select name="rate-plans[]" multiple>
								<?php
									foreach ($validRatePlans as $value) {
										echo "<option value=".$value['id']." ";
										if(count($sessionValues['Connect'][$roomID]["ratePlans"])){
											foreach ($sessionValues['Connect'][$roomID]["ratePlans"] as $ratePlan) {
												if($ratePlan == $value['id']){
													echo "selected";
												}
											}
										}
										echo ">".$value['name']."</option>";
									}
								?>
							</select>
						</div>
					</div>
					<div class="vcm-bcapn-occupancy">
						<label><?php echo JText::_('VCMBCAPNOCCUPANCY');?></label>
						<div class="vcm-bcapn-max-occupancy">
							<input type="number" min="1" name="max-occupancy" value="<?php echo $sessionValues['Connect'][$roomID]['maxOccupancy'];?>"/>
						</div>
					</div>
					<div class="vcm-bcapn-policies">
						<label class="vcm-bcapn-label"><?php echo JText::_('VCMBCAPNPOLICIES'); ?></label>
						<div class="vcm-bcapn-policies-select">
							<select name="cancel-policy">
								<?php
									foreach ($validPolicies as $value) {
										echo "<option value=\"".$possiblePolicies[$value]['value']."\"";
										if($sessionValues['Connect'][$roomID]["cancelPolicy"] == $value){
											echo "selected";
										}
										echo ">".$possiblePolicies[$value]['text']."</option>";
									}
								?>
							</select>
						</div>
					</div>
					<div class="vcm-bcapn-meal-plans">
						<label class="vcm-bcapn-label"><?php echo JText::_('VCMBCAPNMEALPLANS'); ?></label>
						<div class="vcm-bcapn-meal-plans-select">
							<select name="meal-plans[]" multiple>
								<option value="0" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==0){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN1');?></option>
								<option value="1" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==1){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN2');?></option>
								<option value="19" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==19){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN3');?></option>
								<option value="21" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==21){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN4');?></option>
								<option value="22" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==22){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN5');?></option>
								<option value="2" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==2){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN6');?></option>
								<option value="3" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==3){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN7');?></option>
								<option value="4" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==4){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN8');?></option>
								<option value="5" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==5){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN9');?></option>
								<option value="6" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==6){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN10');?></option>
								<option value="7" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==7){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN11');?></option>
								<option value="8" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==8){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN12');?></option>
								<option value="9" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==9){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN13');?></option>
								<option value="10" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==10){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN14');?></option>
								<option value="11" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==11){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN15');?></option>
								<option value="12" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==12){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN16');?></option>
								<option value="14" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==14){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN17');?></option>
								<option value="15" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==15){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN18');?></option>
								<option value="16" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==16){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN19');?></option>
								<option value="17" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==17){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN20');?></option>
								<option value="18" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==18){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN21');?></option>
								<option value="20" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==20){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN22');?></option>
								<option value="23" <?php if(count($sessionValues['Connect'][$roomID]["mealPlans"])){ foreach ($sessionValues['Connect'][$roomID]['mealPlans'] as $value) {if($value==23){echo "selected";}}} ?>><?php echo JText::_('VCMBCAPNMEALPLAN23');?></option>
							</select>
						</div>
					</div>
					<div class="vcm-bcapn-min-offset">
						<label class="vcm-bcapn-label"><?php echo JText::_('VCMBCAPNMINOFF'); ?></label>
						<div class="vcm-bcapn-min-offset-type">
							<input type="number" name="min-offset" min="0" value="<?php echo $sessionValues['Connect'][$roomID]['minOffset']; ?>"/>
							<select name="min-offsetType">
								<option value="Y" <?php echo $sessionValues['Connect'][$roomID]['minOffsetType']=="Y"? "selected" : ""; ?>><?php echo JText::_('VCMBCAPNTIME1');?></option>
								<option value="M" <?php echo $sessionValues['Connect'][$roomID]['minOffsetType']=="M"? "selected" : ""; ?>><?php echo JText::_('VCMBCAPNTIME2');?></option>
								<option value="D" <?php echo $sessionValues['Connect'][$roomID]['minOffsetType']=="D"? "selected" : ""; ?>><?php echo JText::_('VCMBCAPNTIME3');?></option>
								<option value="H" <?php echo $sessionValues['Connect'][$roomID]['minOffsetType']=="H"? "selected" : ""; ?>><?php echo JText::_('VCMBCAPNTIME4');?></option>
							</select>
						</div>
					</div>
					<div class="vcm-bcapn-max-offset">
						<label class="vcm-bcapn-label"><?php echo JText::_('VCMBCAPNMAXOFF'); ?></label>
						<div class="vcm-bcapn-max-offset-type">
							<input type="number" name="max-offset" min="0" value="<?php echo $sessionValues['Connect'][$roomID]['maxOffset']; ?>"/>
							<select name="max-offsetType">
								<option value="Y" <?php echo $sessionValues['Connect'][$roomID]['maxOffsetType']=="Y"? "selected" : ""; ?>><?php echo JText::_('VCMBCAPNTIME1');?></option>
								<option value="M" <?php echo $sessionValues['Connect'][$roomID]['maxOffsetType']=="M"? "selected" : ""; ?>><?php echo JText::_('VCMBCAPNTIME2');?></option>
								<option value="D" <?php echo $sessionValues['Connect'][$roomID]['maxOffsetType']=="D"? "selected" : ""; ?>><?php echo JText::_('VCMBCAPNTIME3');?></option>
								<option value="H" <?php echo $sessionValues['Connect'][$roomID]['maxOffsetType']=="H"? "selected" : ""; ?>><?php echo JText::_('VCMBCAPNTIME4');?></option>
							</select>
						</div>
					</div>
				</div>
			</form>
			<div class="vcm-bcapn-details-container" style="<?php echo !empty($newRoom)||empty($oldData['Connect'][$roomID])? "display:none;" : "" ?>">
				<div class="vcm-bcapn-details-inner">
					<form  class="vcm-bcapn-details-form" method="POST" action="<?php echo JUri::root();?>administrator/index.php">
						<input type="hidden" name="e4j_debug" value="<?php echo $e4j_debug;?>"/>
						<input type="hidden" name="task" value="bca.makeProductXML"/>
						<input type="hidden" name="option" value="com_vikchannelmanager"/>
						<input type="hidden" name="selected-action" value="<?php echo $roomID."-".$roomName."-".$hotelID;?>"/>
						<?php if(count($oldData['Connect'][$roomID])) { ?>
						<h3><?php echo sprintf(JText::_('VCMBCAPNPRODFOR'), $roomName);?></h3>
						<?php
						if(!empty($oldData['Connect'][$roomID])){ 
							foreach ($oldData['Connect'][$roomID] as $ratePlan => $values) { 
						?>
							<div class="vcm-bcapn-product-container">
								<div class="vcm-bcapn-product-header">
									<label class="vcm-bcapn-product-name"><i class="vboicn-enlarge vcm-bcapn-hide-icon"></i><strong><?php
										$printedNames = "";
										foreach ($validRatePlans as $names) {
											if($ratePlan == $names['id']){
												$printedNames .= " ".$names['name']." -";
											}
										}
										$printedNames = rtrim($printedNames,"-");
										$printedNames = trim($printedNames);
										echo $printedNames;
									?></strong></label>
									<label class="vcm-bcapn-date"><?php echo $values['date']; ?></label>
									<button type="button" class="btn btn-danger vcm-bcapn-delete"><?php echo JText::_('VCMBCAPNDELPROD');?></button>
								</div>
								<div class="vcm-bcapn-product-details" style="display: none;">
									<div>
										<label><strong><?php echo JText::_('VCMBCAPNRATEPLANVAL');?></strong><span class="ratePlanValue"><?php echo $ratePlan;?></span></label>
									</div>
									<div>
										<label><strong><?php echo JText::_('VCMBCAPNOCCUPANCYVAL'); ?></strong><?php echo $values["maxOccupancy"];?></label>
									</div>
									<div>
										<label><strong><?php echo JText::_('VCMBCAPNMEALPLANSVAL');?></strong></label>
										<div class="vcm-bcapn-meal-plan">
											<?php
											$mealPlanText = "";
											foreach ($values['mealPlans'] as $mealPlan) {
												$mealPlanText .= "<label>";
												switch($mealPlan){
													case 0:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN1');
														break;
													case 1:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN2');
														break;
													case 19:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN3');
														break;
													case 21:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN4');
														break;
													case 22:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN5');
														break;
													case 2:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN6');
														break;
													case 3:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN7');
														break;
													case 4:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN8');
														break;
													case 5:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN9');
														break;
													case 6:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN10');
														break;
													case 7:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN11');
														break;
													case 8:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN12');
														break;
													case 9:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN13');
														break;
													case 10:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN14');
														break;
													case 11:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN15');
														break;
													case 12:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN16');
														break;
													case 14:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN17');
														break;
													case 15:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN18');
														break;
													case 16:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN19');
														break;
													case 17:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN20');
														break;
													case 18:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN21');
														break;
													case 20:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN22');
														break;
													case 23:
														$mealPlanText .= JText::_('VCMBCAPNMEALPLAN23');
														break;
												}
												$mealPlanText .= "</label>, ";
											}
											echo rtrim($mealPlanText, ', ');
											?>
										</div>
									</div>
									<div>
										<label><strong><?php echo JText::_('VCMBCAPNPOLICYVAL'); ?></strong>
										<?php
											foreach ($validPolicies as $value) {
												if($values["cancelPolicy"] == $value){
													echo $possiblePolicies[$value]['text'];
												}
											}
										?>
										</label>
									</div>
									<div>
										<label><strong><?php echo JText::_('VCMBCAPNMINOFFVAL');?></strong> 
										<?php
											if($values['minOffset']!=0){ 
												echo $values['minOffset'];
												switch($values['minOffsetType']){
													case 'Y':
														echo " Years";
														break;
													case 'M':
														echo " Months";
														break;
													case 'D':
														echo " Days";
														break;
													case 'H':
														echo " Hours";
														break;
												}
											}
											else{
												echo "None";
											}
										?>
										</label>
									</div>
									<div>
										<label><strong><?php echo JText::_('VCMBCAPNMAXOFFVAL');?></strong>
										<?php 
											if($values['maxOffset']!=0){
												echo $values['maxOffset'];
												switch($values['maxOffsetType']){
													case 'Y':
														echo " Years";
														break;
													case 'M':
														echo " Months";
														break;
													case 'D':
														echo " Days";
														break;
													case 'H':
														echo " Hours";
														break;
												}
											}
											else{
												echo "None";
											}
										?>
										</label>
									</div>
								</div>
							</div>
						<?php } } }?>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
