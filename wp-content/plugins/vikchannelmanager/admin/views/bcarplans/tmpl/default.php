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

$ratePlans = $this->ratePlans;
$oldData = $this->oldData;

$progID = 0;

$sessionSet = !empty($this->sessionValues)? true : false;

if($sessionSet) {
	$sessionValues = $this->sessionValues;
	$oldData = array();
}

if($sessionSet){
	foreach ($sessionValues['ratePlans'] as $value) {
		if(!empty($value['progID'])){
			$progID = max($progID,$value['progID']);
		}
	}
}
else if(count($oldData)){
	foreach ($oldData['ratePlans'] as $value) {
		if(!empty($value['progID'])){
			$progID = max($progID,$value['progID']);
		}
	}
}


//echo "<strong>".$progID."</strong></br>";

//echo "<pre>".print_r($oldData,true)."</pre>";

//$progID = empty($oldData['maxProgID'])? 0 : $oldData['maxProgID'];

$vik = new VikApplication(VersionListener::getID());

?>

<div class="vcm-bcarp-container">
	<div class="vcm-bcarp-inner">
		<div class="vcm-bcarp-head">
			<h3>
				<?php echo JText::_('VCMBCARPTITLE');?>
			</h3>
			<div class="vcm-bcarp-button vcm-bcarp-new-div">
				<button type="button" class="vcm-bcarp-new btn"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
			</div>
		</div>
		<form name="adminForm" id="adminForm">
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="option" value="com_vikchannelmanager"/>
			<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
			<div class="vcm-bcarp-item-container">
				<?php
					if(!empty($sessionValues['ratePlans'])){
						foreach ($sessionValues['ratePlans'] as $value) {
							echo "<div class=\"vcm-bcarp-item\">
								<input type=\"hidden\" name=\"progID[]\" value=\"".$value['progID']."\"/>
								";
								if(array_key_exists('hiddenID', $value)){
									echo "<input type=\"hidden\" name=\"hiddenID[]\" value=\"".$value['hiddenID']."\"/>";
								}
								echo "<input type=\"hidden\" name=\"actionType[]\" value=\"".$value['actionType']."\" class=\"vcm-bcarp-action-type\"/>
								<div class=\"vcm-bcarp-element vcm-bcarp-id\">
									<span>".$value['progID']."</span>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-name\">
									<input type=\"text\" name=\"name[]\" value=\"".$value['name']."\" class=\"vcm-bcarp-name-input\"/>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-button vcm-bcarp-cancel\">
									<button type=\"button\" class=\"btn vcm-bcarp-delete\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-delete-message\" style=\"display:none;\">
									<span>".JText::_('VCMBCARPDELETEMESSAGE')."</span>
								</div>
							</div>";
						}
					}
					else if(!empty($oldData['ratePlans'])){
						foreach ($oldData['ratePlans'] as $value) {
							echo "<div class=\"vcm-bcarp-item\">
								<input type=\"hidden\" name=\"hiddenID[]\" value=\"".$value['hiddenID']."\"/>
								<input type=\"hidden\" name=\"progID[]\" value=\"".$value['progID']."\"/>
								<input type=\"hidden\" name=\"actionType[]\" value=\"Overlay\" class=\"vcm-bcarp-action-type\"/>
								<div class=\"vcm-bcarp-element vcm-bcarp-id\">
									<span>".$value['progID']."</span>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-name\">
									<input type=\"text\" name=\"name[]\" value=\"".$value['name']."\" class=\"vcm-bcarp-name-input\"/>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-button vcm-bcarp-cancel\">
									<button type=\"button\" class=\"btn vcm-bcarp-delete\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-delete-message\" style=\"display:none;\">
									<span>".JText::_('VCMBCARPDELETEMESSAGE')."</span>
								</div>
							</div>";
						}
					}
					else{
						foreach ($ratePlans as $value) {
							$progID++;
							echo "<div class=\"vcm-bcarp-item\">
								<input type=\"hidden\" name=\"hiddenID[]\" value=\"".$value['id']."\"/>
								<input type=\"hidden\" name=\"progID[]\" value=\"".$progID."\"/>
								<input type=\"hidden\" name=\"actionType[]\" value=\"Overlay\" class=\"vcm-bcarp-action-type\"/>
								<div class=\"vcm-bcarp-element vcm-bcarp-id\">
									<span>".$progID."</span>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-name\">
									<input type=\"text\" name=\"name[]\" value=\"".$value['name']."\" class=\"vcm-bcarp-name-input\"/>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-button vcm-bcarp-cancel\">
									<button type=\"button\" class=\"btn vcm-bcarp-delete\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
								</div>
								<div class=\"vcm-bcarp-element vcm-bcarp-delete-message\" style=\"display:none;\">
									<span>".JText::_('VCMBCARPDELETEMESSAGE')."</span>
								</div>
							</div>";
						}
					}
				?>
			</div>
		</form>
	</div>
	<script>
		var progID = <?php echo $progID;?>;

		jQuery(document).ready(function(){

			jQuery(".vcm-bcarp-new").click(function(){
				progID++;
				jQuery(".vcm-bcarp-item-container").append("<div class=\"vcm-bcarp-item\">"+
					"<input type=\"hidden\" name=\"progID[]\" value=\""+progID+"\"/>"+
					"<input type=\"hidden\" name=\"actionType[]\" value=\"New\" class=\"vcm-bcarp-action-type\"/>"+
					"<div class=\"vcm-bcarp-element vcm-bcarp-id\">"+
						"<span>"+progID+"</span>"+
					"</div>"+
					"<div class=\"vcm-bcarp-element vcm-bcarp-name\">"+
						"<input type=\"text\" name=\"name[]\" class=\"vcm-bcarp-name-input\"/>"+
					"</div>"+
					"<div class=\"vcm-bcarp-element vcm-bcarp-button vcm-bcarp-cancel\">"+
						"<button type=\"button\" class=\"btn vcm-bcarp-delete\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>");
			});

			jQuery(document.body).on('click', '.vcm-bcarp-delete', function(){
				if(jQuery(this).closest(".vcm-bcarp-item").find(".vcm-bcarp-action-type").val()=="New"){
					jQuery(this).closest(".vcm-bcarp-item").fadeOut("fast", function(){
						jQuery(this).remove();
					});
				}
				else{
					jQuery(this).closest(".vcm-bcarp-item").addClass("vcm-bcarp-scheduled-for-delete");
					jQuery(this).closest(".vcm-bcarp-item").find(".vcm-bcarp-action-type").val("Remove");
					jQuery(this).closest(".vcm-bcarp-item").find(".vcm-bcarp-name-input").prop({readonly:true});
					jQuery(this).closest(".vcm-bcarp-item").find(".vcm-bcarp-delete-message").show();
					jQuery(this).remove();
				}
			});

		});

	</script>
</div>
