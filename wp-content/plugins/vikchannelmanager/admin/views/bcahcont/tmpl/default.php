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

$dbo = JFactory::getDbo();

$vik = new VikApplication(VersionListener::getID());

$RMAAmenitiesNames = $this->RMAAmenitiesNames;
$RMAAmenitiesCodes = $this->RMAAmenitiesCodes;
$languageCodes = $this->languageCodes;
$countryCodes = $this->countryCodes;
$policyCodes = $this->policyCodes;
$imageTagCodes = $this->imageTagCodes;
$oldData = $this->oldData;
$inputTypeReset = $this->inputTypeReset;
$channel = VikChannelManager::getActiveModule(true);
$channel['params'] = json_decode($channel['params'], true);
$sessionValues = $this->sessionValues;
$insertType = $sessionValues !== null && property_exists($sessionValues, "insertType") ? $sessionValues->insertType : '';
$e4j_debug = VikRequest::getInt('e4j_debug');

/*echo "<strong>Session: </strong><pre>".print_r($sessionValues,true)."</pre>";
echo "<strong>Old Data: </strong><pre>".print_r($oldData,true)."</pre>";*/

if (!empty($sessionValues)) {
	$oldData = $sessionValues;
}

if ($inputTypeReset == 1) {
	$insertType = 'Overlay';
	$oldData = $this->oldData;
}

//$oldData = new stdClass();

JHTML::_('behavior.calendar');

$hotelName = $this->hotelName;

$hotelName = empty($hotelName)&&property_exists($oldData, 'physical_location')&&property_exists($oldData->physical_location, 'address')&&property_exists($oldData->physical_location->address, 'hotelName')? $oldData->physical_location->address->hotelName : $hotelName;

if (!empty($sessionValues)) {
	if (property_exists($sessionValues, 'physical_location')&&property_exists($sessionValues->physical_location, 'address')&&property_exists($sessionValues->physical_location->address, 'hotelName')) {
		$hotelName = $sessionValues->physical_location->address->hotelName;
	}
}

if ($e4j_debug) {
	echo "<strong>Session Values:</strong><pre>".print_r($sessionValues,true)."</pre>";
	echo "<strong>Old Data:</strong><pre>".print_r($oldData,true)."</pre>";
}

//$progID = empty($oldData['maxProgID'])? 100001 : $oldData['maxProgID']+=1;

?>
<script>


	//JQuery Starts Here

	var InvoicesEmailIndex = <?php echo (property_exists($oldData, 'invoicesEmailsIndexes')&&max($oldData->invoicesEmailsIndexes)!=0) ? max($oldData->invoicesEmailsIndexes)+1 : 1;?>;
	var GeneralEmailIndex = <?php echo (property_exists($oldData, 'generalEmailsIndexes')&&max($oldData->generalEmailsIndexes)!=0) ? max($oldData->generalEmailsIndexes)+1 : 1;?>;
	var ContractEmailIndex = <?php echo (property_exists($oldData, 'contractEmailsIndexes')&&max($oldData->contractEmailsIndexes)!=0) ? max($oldData->contractEmailsIndexes)+1 : 1;?>;
	var ReservationsEmailIndex = <?php echo (property_exists($oldData, 'reservationsEmailsIndexes')&&max($oldData->reservationsEmailsIndexes)!=0) ? max($oldData->reservationsEmailsIndexes)+1 : 1;?>;
	var AvailabilityEmailIndex = <?php echo (property_exists($oldData, 'availabilityEmailsIndexes')&&max($oldData->availabilityEmailsIndexes)!=0) ? max($oldData->availabilityEmailsIndexes)+1 : 1;?>;
	var Site_ContentEmailIndex = <?php echo (property_exists($oldData, 'site_contentEmailsIndexes')&&max($oldData->site_contentEmailsIndexes)!=0) ? max($oldData->site_contentEmailsIndexes)+1 : 1;?>;
	var ParityEmailIndex = <?php echo (property_exists($oldData, 'parityEmailsIndexes')&&max($oldData->parityEmailsIndexes)!=0) ? max($oldData->parityEmailsIndexes)+1 : 1;?>;
	var RequestEmailIndex = <?php echo (property_exists($oldData, 'requestsEmailsIndexes')&&max($oldData->requestsEmailsIndexes)!=0) ? max($oldData->requestsEmailsIndexes)+1 : 1;?>;
	var Central_ReservationsEmailIndex = <?php echo (property_exists($oldData, 'central_reservationsEmailsIndexes')&&max($oldData->central_reservationsEmailsIndexes)!=0) ? max($oldData->central_reservationsEmailsIndexes)+1 : 1;?>;
	var InvoicesPhoneIndex = <?php echo (property_exists($oldData, 'invoicesPhonesIndexes')&&max($oldData->invoicesPhonesIndexes)!=0) ? max($oldData->invoicesPhonesIndexes)+1 : 1;?>;
	var GeneralPhoneIndex = <?php echo (property_exists($oldData, 'generalPhonesIndexes')&&max($oldData->generalPhonesIndexes)!=0) ? max($oldData->generalPhonesIndexes)+1 : 1;?>;
	var ContractPhoneIndex = <?php echo (property_exists($oldData, 'contractPhonesIndexes')&&max($oldData->contractPhonesIndexes)!=0) ? max($oldData->contractPhonesIndexes)+1 : 1;?>;
	var ReservationsPhoneIndex = <?php echo (property_exists($oldData, 'reservationsPhonesIndexes')&&max($oldData->reservationsPhonesIndexes)!=0) ? max($oldData->reservationsPhonesIndexes)+1 : 1;?>;
	var AvailabilityPhoneIndex = <?php echo (property_exists($oldData, 'availabilityPhonesIndexes')&&max($oldData->availabilityPhonesIndexes)!=0) ? max($oldData->availabilityPhonesIndexes)+1 : 1;?>;
	var Site_ContentPhoneIndex = <?php echo (property_exists($oldData, 'site_contentPhonesIndexes')&&max($oldData->site_contentPhonesIndexes)!=0) ? max($oldData->site_contentPhonesIndexes)+1 : 1;?>;
	var ParityPhoneIndex = <?php echo (property_exists($oldData, 'parityPhonesIndexes')&&max($oldData->parityPhonesIndexes)!=0) ? max($oldData->parityPhonesIndexes)+1 : 1;?>;
	var RequestsPhoneIndex = <?php echo (property_exists($oldData, 'requestsPhonesIndexes')&&max($oldData->requestsPhonesIndexes)!=0) ? max($oldData->requestsPhonesIndexes)+1 : 1;?>;
	var Central_ReservationsPhoneIndex = <?php echo (property_exists($oldData, 'central_reservationsPhonesIndexes')&&max($oldData->central_reservationsPhonesIndexes)!=0) ? max($oldData->central_reservationsPhonesIndexes)+1 : 1;?>;
	var LanguageIndex = <?php echo (property_exists($oldData, 'languagesIndexes')&&max($oldData->languagesIndexes)!=0) ? max($oldData->languagesIndexes)+1 : 1;?>;
	var ServiceIndex = <?php echo (property_exists($oldData, 'servicesIndexes')&&max($oldData->servicesIndexes)!=0) ? max($oldData->servicesIndexes)+1 : 1;?>;
	var AmenityIndex = <?php echo (property_exists($oldData, 'amenitiesIndexes')&&max($oldData->amenitiesIndexes)!=0) ? max($oldData->amenitiesIndexes)+1 : 1;?>;
	var GuaranteepaymentIndex = <?php echo (property_exists($oldData, 'guaranteepaymentsIndexes')&&max($oldData->guaranteepaymentsIndexes)!=0) ? max($oldData->guaranteepaymentsIndexes)+1 : 1;?>;
	var CancelpolicyIndex = <?php echo (property_exists($oldData, 'cancelpoliciesIndexes')&&max($oldData->cancelpoliciesIndexes)!=0) ? max($oldData->cancelpoliciesIndexes)+1 : 1;?>;
	var TaxIndex = <?php echo (property_exists($oldData, 'taxesIndexes')&&max($oldData->taxesIndexes)!=0) ? max($oldData->taxesIndexes)+1 : 1;?>;
	var FeeIndex = <?php echo (property_exists($oldData, 'feesIndexes')&&max($oldData->feesIndexes)!=0) ? max($oldData->feesIndexes)+1 : 1;?>;
	var ImageIndex = <?php echo (property_exists($oldData, 'imagesIndexes')&&max($oldData->imagesIndexes)!=0) ? max($oldData->imagesIndexes)+1 : 1;?>;
	var PaymentmethodIndex = <?php echo (property_exists($oldData, 'paymentmethodsIndexes')&&max($oldData->paymentmethodsIndexes)!=0) ? max($oldData->paymentmethodsIndexes)+1 : 1;?>;
	var AttractionIndex = <?php echo (property_exists($oldData, 'attractionsIndexes')&&max($oldData->attractionsIndexes)!=0) ? max($oldData->attractionsIndexes)+1 : 1;?>;

	var contentInfoNames = {
		invoices : '<?php echo JText::_('VCMBCAHCINFOTYPE4');?>',
		general : '<?php echo JText::_('VCMBCAHCINFOTYPE1');?>',
		contract : '<?php echo JText::_('VCMBCAHCINFOTYPE2');?>',
		reservations : '<?php echo JText::_('VCMBCAHCINFOTYPE3');?>',
		availability : '<?php echo JText::_('VCMBCAHCINFOTYPE5');?>',
		site_content : '<?php echo JText::_('VCMBCAHCINFOTYPE6');?>',
		parity : '<?php echo JText::_('VCMBCAHCINFOTYPE7');?>',
		requests : '<?php echo JText::_('VCMBCAHCINFOTYPE8');?>',
		central_reservations : '<?php echo JText::_('VCMBCAHCINFOTYPE9');?>',
	}

	var dataObject = {};

	function copyData(source, destination){
		//dataObject = jQuery.makeArray(dataObject);
		console.log("Source name: "+source);
		console.log("Destination name: "+destination);
		console.log("Source value: ");
		console.log(dataObject[source]);
		console.log("Destination value: ");
		console.log(dataObject[destination]);
		/*jQuery.each(dataObject[source], function(key,value){
			console.log("Key: "+key);
			console.log("Value: ");
			console.log(value);
		});*/
		dataObject[destination] = {};
		if(jQuery('.'+destination+':hidden').length!=0){
			jQuery('.'+destination).prev().find(".vcm-bcah-hide-button").first().click();
		}
		jQuery.each(dataObject[source], function(key, value){
			console.log("Source - Key: "+key+" - Value: "+value);
			console.log("Destination - Key: "+key+" - Value: "+value);
			if(key == "vcm-bcah-email-index"){
				jQuery("."+destination).find(".vcm-bcah-email-index").next().find(".vcm-bcah-delete-button").click();
				dataObject[destination][key] = {};
				jQuery.each(value, function(arrayIndex, emailIndex){
					if(jQuery("."+source).find(".vcm-bcah-email"+emailIndex).length!=0){
						jQuery("."+destination).find(".vcm-bcah-new-button.vcm-bcah-email").click();
						jQuery("."+destination).find(".vcm-bcah-email-index").parent().last().find(".vcm-bcah-email-input").val(jQuery("."+source).find(".vcm-bcah-email"+emailIndex).find(".vcm-bcah-email-input").val());
					}
				});
			}
			else if(key == "vcm-bcah-phone-index"){
				jQuery("."+destination).find(".vcm-bcah-phone-index").next().next().find(".vcm-bcah-delete-button").click();
				dataObject[destination][key] = {};
				jQuery.each(value, function(arrayIndex, phoneIndex){
					if(jQuery("."+source).find(".vcm-bcah-phone"+phoneIndex).find(".vcm-bcah-phone-number").length!=0||jQuery("."+source).find(".vcm-bcah-phone"+phoneIndex).find(".vcm-bcah-phone-tech-type-selector").length!=0||jQuery("."+source).find(".vcm-bcah-phone"+phoneIndex).find(".vcm-bcah-phone-extension").length!=0) {
						jQuery("."+destination).find(".vcm-bcah-new-button.vcm-bcah-phone").click();
						jQuery("."+destination).find(".vcm-bcah-phone-index").parent().last().find(".vcm-bcah-phone-number").val(jQuery("."+source).find(".vcm-bcah-phone"+phoneIndex).find(".vcm-bcah-phone-number").val());
						jQuery("."+destination).find(".vcm-bcah-phone-index").parent().last().find(".vcm-bcah-phone-tech-type-selector").val(jQuery("."+source).find(".vcm-bcah-phone"+phoneIndex).find(".vcm-bcah-phone-tech-type-selector").val());
						jQuery("."+destination).find(".vcm-bcah-phone-index").parent().last().find(".vcm-bcah-phone-tech-type-selector").change();
						jQuery("."+destination).find(".vcm-bcah-phone-index").parent().last().find(".vcm-bcah-phone-extension").val(jQuery("."+source).find(".vcm-bcah-phone"+phoneIndex).find(".vcm-bcah-phone-extension").val());
					}
				});
			}
			else{
				console.log(jQuery("."+destination));
				console.log(jQuery("."+destination).find("#"+key));
				dataObject[destination][key] = value;
				jQuery("."+destination).find("#"+key).val(value);
			}
		});
	}

	jQuery(document).ready(function(){

		//BCAHI Request Confirm

		Joomla.submitbutton = function(task) {
			if ( task == 'bca.readHotelInfo') {
				if (confirm('<?php echo addslashes(JText::_('VCMBCAHICONFIRM')); ?>')) {
					Joomla.submitform(task, document.adminForm);
				} else {
					return false;
				}
			} else {
				Joomla.submitform(task, document.adminForm);
			}
		}

		jQuery('.vcm-bca-multi-select').select2({
			allowClear: false,
			placeholder: "<?php echo addslashes(JText::_('VCMBCAIMAGETAGS')); ?>",
			width: 300
		});

		jQuery('.vcm-detail-input-fromdate').val("<?php echo isset($oldData->standardphrases) ? $oldData->standardphrases->rvfromdate : ''; ?>");
		jQuery('.vcm-detail-input-todate').val("<?php echo isset($oldData->standardphrases) ? $oldData->standardphrases->rvtodate : ''; ?>");

		/*jQuery('#vcm-bcah-kids-stay-free').on('change',function(){
			if(this.checked){
				jQuery('#vcm-bcah-free-cutoff-age-div').show();
				jQuery('#vcm-bcah-free-cutoff-age').prop({disabled: false});
				jQuery('#vcm-bcah-free-child-per-adult-div').show();
				jQuery('#vcm-bcah-free-child-per-adult').prop({disabled: false});
			}
			else{
				jQuery('#vcm-bcah-free-cutoff-age-div').hide();
				jQuery('#vcm-bcah-free-cutoff-age').prop({disabled: true});
				jQuery('#vcm-bcah-free-child-per-adult-div').hide();
				jQuery('#vcm-bcah-free-child-per-adult').prop({disabled: true});
			}
		});*/

		jQuery(document.body).on('change', '.vcm-bcah-service-select', function() {
			if(this.value=="173"){
				jQuery(this).closest('.vcm-bcah-entry-contents').find('.vcm-bcah-breakfast-price').show();
				jQuery(this).closest('.vcm-bcah-entry-contents').find('.vcm-bcah-breakfast-type').show();
				jQuery(this).closest('.vcm-bcah-entry-contents').find('.vcm-bcah-breakfast-price').find('input').prop({disabled: false});
				jQuery(this).closest('.vcm-bcah-entry-contents').find('.vcm-bcah-breakfast-type').find('select').prop({disabled: false});
			}
			else{
				jQuery(this).closest('.vcm-bcah-entry-contents').find('.vcm-bcah-breakfast-price').hide();
				jQuery(this).closest('.vcm-bcah-entry-contents').find('.vcm-bcah-breakfast-type').hide();
				jQuery(this).closest('.vcm-bcah-entry-contents').find('.vcm-bcah-breakfast-price').find('input').prop({disabled: true});
				jQuery(this).closest('.vcm-bcah-entry-contents').find('.vcm-bcah-breakfast-type').find('select').prop({disabled: true});
			}
		});

		jQuery(document.body).on('change', ".vcm-bcah-saved-value", function() {
			var thisClass = jQuery(this).closest(".vcm-bcah-contact-info-container").attr("class").replace("vcm-bcah-contact-info-container","").trim();
			if(jQuery(this).val().length!=0){
				dataObject[thisClass] = {};
				if(!jQuery.isArray(dataObject[thisClass]["vcm-bcah-email-index"])){
					dataObject[thisClass]["vcm-bcah-email-index"] = [];
				}
				if(!jQuery.isArray(dataObject[thisClass]["vcm-bcah-phone-index"])){
					dataObject[thisClass]["vcm-bcah-phone-index"] = [];
				}
				jQuery.each(jQuery(this).closest(".vcm-bcah-contact-info-container").find('.vcm-bcah-saved-value'), function(key, value){
					if(jQuery(value).hasClass("vcm-bcah-email-index")){
						dataObject[thisClass]["vcm-bcah-email-index"].push(jQuery(value).val());
					}
					else if(jQuery(value).hasClass("vcm-bcah-phone-index")){
						dataObject[thisClass]["vcm-bcah-phone-index"].push(jQuery(value).val());
					}
					else{
						var nameVar = jQuery(value).attr("id");
						dataObject[thisClass][nameVar] = jQuery(value).val();
					}
				});
				jQuery(".vcm-bcah-copy-links").html("");
				jQuery.each(jQuery(".vcm-bcah-copy-links"), function(index, value){
					var divClass = jQuery(value).closest(".vcm-bcah-contact-info-container").attr("class").replace("vcm-bcah-contact-info-container","").trim();
					jQuery.each(dataObject,function(source,content){
						if(divClass!=source){
							var sourceTrueName = source.replace('-div','');
							sourceTrueName = sourceTrueName.replace('vcm-bcah-','');
							jQuery(value).append("<label class=\"vcm-bcah-copy-link\" id=\""+source+"\"><i class=\"vboicn-copy\"></i>"+contentInfoNames[sourceTrueName]+"</label>");
						}
					});
				});
			}
		});
		
		jQuery(document.body).on('click', '.vcm-bcah-copy-link', function(){
			var source = jQuery(this).attr('id');
			var destination = jQuery(this).closest(".vcm-bcah-contact-info-container").attr('class').replace('vcm-bcah-contact-info-container','').trim();
			copyData(source,destination);
		});

		jQuery(document.body).on('change', '.vcm-bcah-phone-tech-type-selector', function() {
			if(jQuery(this).val()==5||jQuery(this).val()==0){
				jQuery(this).parent().next().find("input").prop({disabled:true});
				jQuery(this).parent().next().hide();
			}
			else{
				jQuery(this).parent().next().find("input").prop({disabled:false});
				jQuery(this).parent().next().show();
			}
		});

		jQuery(document.body).on('change', '.vcm-bcah-insert-type', function() {
			if(this.value=="New"){
				var newHTML = '<div class="vcm-bcah-detail">';
				newHTML +='	<label><?php echo JText::_('VCMBCAHGROOMQ');?></label>';
				newHTML +='	<input type="number" name="vcm-bcah-guest-room-quantity" value="0" min="0"/>';
				newHTML +='</div>';
				newHTML +='<div class="vcm-bcah-category-container" id="vcm-bcah-hotelcategory-container">';
				newHTML +='	<div class="vcm-bcah-category-div">';
				newHTML +='		<label><?php echo JText::_('VCMBCAHHOTTYPE');?></label>';
				newHTML +='		<select name="vcm-bcah-hotel-type">';
				newHTML +='			<option value=""></option>';
				newHTML +='			<option value="3"><?php echo JText::_('VCMBCAHHOTTYPE1');?></option>';
				newHTML +='			<option value="4"><?php echo JText::_('VCMBCAHHOTTYPE2');?></option>';
				newHTML +='			<option value="5"><?php echo JText::_('VCMBCAHHOTTYPE3');?></option>';
				newHTML +='			<option value="6"><?php echo JText::_('VCMBCAHHOTTYPE4');?></option>';
				newHTML +='			<option value="7"><?php echo JText::_('VCMBCAHHOTTYPE5');?></option>';
				newHTML +='			<option value="8"><?php echo JText::_('VCMBCAHHOTTYPE6');?></option>';
				newHTML +='			<option value="12"><?php echo JText::_('VCMBCAHHOTTYPE7');?></option>';
				newHTML +='			<option value="14"><?php echo JText::_('VCMBCAHHOTTYPE8');?></option>';
				newHTML +='			<option value="15"><?php echo JText::_('VCMBCAHHOTTYPE9');?></option>';
				newHTML +='			<option value="16"><?php echo JText::_('VCMBCAHHOTTYPE10');?></option>';
				newHTML +='			<option value="18"><?php echo JText::_('VCMBCAHHOTTYPE11');?></option>';
				newHTML +='			<option value="19"><?php echo JText::_('VCMBCAHHOTTYPE12');?></option>';
				newHTML +='			<option value="20"><?php echo JText::_('VCMBCAHHOTTYPE13');?></option>';
				newHTML +='			<option value="21"><?php echo JText::_('VCMBCAHHOTTYPE14');?></option>';
				newHTML +='			<option value="22"><?php echo JText::_('VCMBCAHHOTTYPE15');?></option>';
				newHTML +='			<option value="23"><?php echo JText::_('VCMBCAHHOTTYPE16');?></option>';
				newHTML +='			<option value="25"><?php echo JText::_('VCMBCAHHOTTYPE17');?></option>';
				newHTML +='			<option value="26"><?php echo JText::_('VCMBCAHHOTTYPE18');?></option>';
				newHTML +='			<option value="27"><?php echo JText::_('VCMBCAHHOTTYPE19');?></option>';
				newHTML +='			<option value="28"><?php echo JText::_('VCMBCAHHOTTYPE20');?></option>';
				newHTML +='			<option value="29"><?php echo JText::_('VCMBCAHHOTTYPE21');?></option>';
				newHTML +='			<option value="30"><?php echo JText::_('VCMBCAHHOTTYPE22');?></option>';
				newHTML +='			<option value="31"><?php echo JText::_('VCMBCAHHOTTYPE23');?></option>';
				newHTML +='			<option value="32"><?php echo JText::_('VCMBCAHHOTTYPE24');?></option>';
				newHTML +='			<option value="33"><?php echo JText::_('VCMBCAHHOTTYPE25');?></option>';
				newHTML +='			<option value="34"><?php echo JText::_('VCMBCAHHOTTYPE26');?></option>';
				newHTML +='			<option value="35"><?php echo JText::_('VCMBCAHHOTTYPE27');?></option>';
				newHTML +='			<option value="36"><?php echo JText::_('VCMBCAHHOTTYPE28');?></option>';
				newHTML +='			<option value="37"><?php echo JText::_('VCMBCAHHOTTYPE29');?></option>';
				newHTML +='			<option value="40"><?php echo JText::_('VCMBCAHHOTTYPE30');?></option>';
				newHTML +='			<option value="44"><?php echo JText::_('VCMBCAHHOTTYPE31');?></option>';
				newHTML +='			<option value="45"><?php echo JText::_('VCMBCAHHOTTYPE32');?></option>';
				newHTML +='			<option value="46"><?php echo JText::_('VCMBCAHHOTTYPE33');?></option>';
				newHTML +='			<option value="50"><?php echo JText::_('VCMBCAHHOTTYPE34');?></option>';
				newHTML +='			<option value="51"><?php echo JText::_('VCMBCAHHOTTYPE35');?></option>';
				newHTML +='			<option value="52"><?php echo JText::_('VCMBCAHHOTTYPE36');?></option>';
				newHTML +='		</select>';
				newHTML +='	</div>';
				newHTML +='</div>';
				jQuery('.vcm-bcah-entry-detail').append(newHTML);
				jQuery('.vcm-bcah-user-details').hide();
				jQuery('.vcm-bcah-contact-info-container').find("input[type='text'],input[type='number'],select").val('');
				jQuery('.vcm-bcah-physical-location-div').find("input[type='text'],input[type='number'],select").val('');
				jQuery('.vcm-bcah-contact-info-container').find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").find(".vcm-bcah-delete-button").click();
				jQuery('.vcm-bcah-contact-info-container').find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").find(".vcm-bcah-delete-button").click();
				jQuery('.vcm-bcah-saved-value').change();
				jQuery.each(dataObject, function(key, value){
					value['vcm-bcah-email-index'] = [];
					value['vcm-bcah-phone-index'] = [];
				});
				jQuery('.vcm-bcah-language-div').hide().find("select").prop({disabled:true});
			}
			else{
				document.location.href = 'index.php?option=com_vikchannelmanager&task=bcahcont&insertTypeReSet=1';
			}
		});

		jQuery(".vcm-bcah-tab").click(function(){
			if(!jQuery(this).hasClass("vcm-bcah-active")){
				var tabId = jQuery(this).attr("id");
				jQuery(".vcm-bcah-tab").removeClass("vcm-bcah-active");
				jQuery(this).addClass("vcm-bcah-active");
				jQuery(".vcm-bcah-container-content").hide();
				jQuery(".vcm-bcah-container-content[id='"+tabId+"']").show();
			}
		});


		jQuery(document.body).on('click', '.vcm-bcah-hide-button', function(){
			var buttonId = jQuery(this).attr("id");
	    	if(jQuery(this).closest(".vcm-bcah-container-header").next(".vcm-bcah-entry-container").find(".vcm-bcah-entry-instance").length==0&&jQuery(this).parent().parent(".vcm-bcah-container-header").length!=0&&!jQuery(this).parent().parent(".vcm-bcah-container-header").hasClass("vcm-bcah-detail")){
	    	}
	    	else{
	    		jQuery("."+buttonId+"-icon").toggleClass("vboicn-circle-down");
	    		jQuery("."+buttonId+"-icon").toggleClass("vboicn-circle-up");
	    		jQuery("."+buttonId+"-div").toggle("fast");
	    	}
		});

		jQuery(document.body).on('click', '.vcm-bcah-delete-button', function(){
			var buttonId = jQuery(this).attr("id");
			var relatedHeader = jQuery(this).closest(".vcm-bcah-entry-container").prev(".vcm-bcah-container-header");
			jQuery(this).closest(".vcm-bcah-entry-instance").fadeOut("fast", function(){
				jQuery(this).remove();
				if(relatedHeader.next(".vcm-bcah-entry-container").find(".vcm-bcah-entry-instance").length==0){
					relatedHeader.find(".vcm-bcah-hide-button").first().hide();
					relatedHeader.next(".vcm-bcah-entry-container").hide();
				}
			});
		});

		jQuery(".vcm-bcah-new-button").click(function(){
			jQuery(this).closest(".vcm-bcah-container-header").find(".vcm-bcah-hide-button").first().show();
			jQuery(this).parent().next(".vcm-bcah-entry-container").show();
			if(jQuery(this).hasClass("vcm-bcah-email")){
				if(jQuery(this).parent().hasClass("vcm-bcah-invoices")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+InvoicesEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-invoices-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+InvoicesEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-invoices-email"+InvoicesEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+InvoicesEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					InvoicesEmailIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-general")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+GeneralEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-general-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+GeneralEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-general-email"+GeneralEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+GeneralEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					GeneralEmailIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-contract")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+ContractEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-contract-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+ContractEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-contract-email"+ContractEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+ContractEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					ContractEmailIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-reservations")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+ReservationsEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-reservations-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+ReservationsEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-reservations-email"+ReservationsEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+ReservationsEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					ReservationsEmailIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-availability")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+AvailabilityEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-availability-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+AvailabilityEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-availability-email"+AvailabilityEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+AvailabilityEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					AvailabilityEmailIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-site_content")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+Site_ContentEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-site_content-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+Site_ContentEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-site_content-email"+Site_ContentEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+Site_ContentEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					Site_ContentEmailIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-parity")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+ParityEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-parity-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+ParityEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-parity-email"+ParityEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+ParityEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					ParityEmailIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-requests")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+RequestEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-requests-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+RequestEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-requests-email"+RequestEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+RequestEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					RequestEmailIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-central_reservations")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-emails-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-email"+Central_ReservationsEmailIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-central_reservations-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\""+Central_ReservationsEmailIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-contents\">"+
							"<span><?php echo JText::_('VCMBCAHEMAIL');?></span>"+
							"<input type=\"text\" name=\"vcm-bcah-central_reservations-email"+Central_ReservationsEmailIndex+"-email-address\" class=\"vcm-bcah-email-input\"/>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email"+Central_ReservationsEmailIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					Central_ReservationsEmailIndex++;
				}
				jQuery(this).parent().closest(".vcm-bcah-category-container").find(".vcm-bcah-email-index").trigger("change");				
			}
			else if(jQuery(this).hasClass("vcm-bcah-phone")){
				if(jQuery(this).parent().hasClass("vcm-bcah-invoices")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+InvoicesPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-invoices-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+InvoicesPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+InvoicesPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+InvoicesPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+InvoicesPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+InvoicesPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-invoices-phone"+InvoicesPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-invoices-phone"+InvoicesPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-invoices-phone"+InvoicesPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+InvoicesPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					InvoicesPhoneIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-general")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+GeneralPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-general-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+GeneralPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+GeneralPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+GeneralPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+GeneralPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+GeneralPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-general-phone"+GeneralPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-general-phone"+GeneralPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-general-phone"+GeneralPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+GeneralPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					GeneralPhoneIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-contract")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+ContractPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-contract-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+ContractPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+ContractPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+ContractPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+ContractPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+ContractPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-contract-phone"+ContractPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-contract-phone"+ContractPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-contract-phone"+ContractPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+ContractPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					ContractPhoneIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-reservations")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+ReservationsPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-reservations-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+ReservationsPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+ReservationsPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+ReservationsPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+ReservationsPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+ReservationsPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-reservations-phone"+ReservationsPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-reservations-phone"+ReservationsPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-reservations-phone"+ReservationsPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+ReservationsPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					ReservationsPhoneIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-availability")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+AvailabilityPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-availability-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+AvailabilityPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+AvailabilityPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+AvailabilityPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+AvailabilityPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+AvailabilityPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-availability-phone"+AvailabilityPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-availability-phone"+AvailabilityPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-availability-phone"+AvailabilityPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+AvailabilityPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					AvailabilityPhoneIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-site_content")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+Site_ContentPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-site_content-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+Site_ContentPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+Site_ContentPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+Site_ContentPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+Site_ContentPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+Site_ContentPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-site_content-phone"+Site_ContentPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-site_content-phone"+Site_ContentPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-site_content-phone"+Site_ContentPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+Site_ContentPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					Site_ContentPhoneIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-parity")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+ParityPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-parity-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+ParityPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+ParityPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+ParityPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+ParityPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+ParityPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-parity-phone"+ParityPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-parity-phone"+ParityPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-parity-phone"+ParityPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+ParityPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					ParityPhoneIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-requests")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+RequestsPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-requests-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+RequestsPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+RequestsPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+RequestsPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+RequestsPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+RequestsPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-requests-phone"+RequestsPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-requests-phone"+RequestsPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-requests-phone"+RequestsPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+RequestsPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					RequestsPhoneIndex++;
				}
				if(jQuery(this).parent().hasClass("vcm-bcah-central_reservations")){
					jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-phone"+Central_ReservationsPhoneIndex+"\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-central_reservations-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\""+Central_ReservationsPhoneIndex+"\"/>"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<span>"+
								"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone"+Central_ReservationsPhoneIndex+"-icon\" id=\"vcm-bcah-phone"+Central_ReservationsPhoneIndex+"\"></i>"+
								"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone"+Central_ReservationsPhoneIndex+"\"><?php echo JText::_('VCMBCAHPHONE');?></span>"+
							"</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-phone"+Central_ReservationsPhoneIndex+"-div\">"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONENUMB');?></label>"+
								"<input type=\"text\" name=\"vcm-bcah-central_reservations-phone"+Central_ReservationsPhoneIndex+"-phone-number\" class=\"vcm-bcah-phone-number\"/>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONETYPE');?></label>"+
								"<select name=\"vcm-bcah-central_reservations-phone"+Central_ReservationsPhoneIndex+"-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">"+
									"<option value=\"\"> </option>"+
									"<option value=\"1\"><?php echo JText::_('VCMBCAHPHONETYPE1');?></option>"+
									"<option value=\"3\"><?php echo JText::_('VCMBCAHPHONETYPE2');?></option>"+
									"<option value=\"5\"><?php echo JText::_('VCMBCAHPHONETYPE3');?></option>"+
								"</select>"+
							"</div>"+
							"<div class=\"vcm-bcah-entry-detail\">"+
								"<label><?php echo JText::_('VCMBCAHPHONEEXT');?></label>"+
								"<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-central_reservations-phone"+Central_ReservationsPhoneIndex+"-extension\" class=\"vcm-bcah-phone-extension\"/>"+
							"</div>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone"+Central_ReservationsPhoneIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>");

					Central_ReservationsPhoneIndex++;
				}
				jQuery(this).parent().closest(".vcm-bcah-category-container").find(".vcm-bcah-phone-index").trigger("change");
				jQuery(this).closest(".vcm-bcah-category-container").find(".vcm-bcah-phones-div").find(".vcm-bcah-entry-instance-container").find(".vcm-bcah-phone-tech-type-selector").change();			
			}
			else if(jQuery(this).hasClass("vcm-bcah-language")){
				var appendableText = "<div class=\"vcm-bcah-entry-instance vcm-bcah-language"+LanguageIndex+"\">"+
					"<div class=\"vcm-bcah-entry-contents\">"+
						"<input type=\"hidden\" name=\"vcm-bcah-language-index[]\" value=\""+LanguageIndex+"\"/>"+
						"<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>"+
						"<select name=\"vcm-bcah-language"+LanguageIndex+"-selected-language\">";
						<?php 
							foreach ($languageCodes as $key => $value) {
						?>
							appendableText += "<option value=\"<?php echo $value;?>\"><?php echo $key;?></option>";
						<?php
							}
						?>
						appendableText += "</select>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-language"+LanguageIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>";

				jQuery(".vcm-bcah-languages-div").find(".vcm-bcah-entry-instance-container").append(appendableText);

				LanguageIndex++;
				
			}
			else if(jQuery(this).hasClass("vcm-bcah-service")){
				jQuery(".vcm-bcah-services-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance-invisible-content vcm-bcah-entry-instance vcm-bcah-service"+ServiceIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-service-index[]\" value=\""+ServiceIndex+"\"/>"+
					"<div class=\"vcm-bcah-entry-header\">"+
						"<span>"+
							"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-service"+ServiceIndex+"-icon\" id=\"vcm-bcah-service"+ServiceIndex+"\"></i>"+
							"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-service"+ServiceIndex+"\"><?php echo JText::_('VCMBCAHSERVICE');?></span>"+
						"</span>"+
					"</div>"+
					"<div class=\"vcm-bcah-entry-contents vcm-bcah-service"+ServiceIndex+"-div\">"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHSERVTYPE');?></label>"+
							"<select name=\"vcm-bcah-service"+ServiceIndex+"-selected-service\" class=\"vcm-bcah-service-select\">"+
								"<option value=\"1\"><?php echo JText::_('VCMBCAHSERV1');?></option>"+
								"<option value=\"5\"><?php echo JText::_('VCMBCAHSERV2');?></option>"+
								"<option value=\"7\"><?php echo JText::_('VCMBCAHSERV3');?></option>"+
								"<option value=\"8\"><?php echo JText::_('VCMBCAHSERV4');?></option>"+
								"<option value=\"9\"><?php echo JText::_('VCMBCAHSERV5');?></option>"+
								"<option value=\"14\"><?php echo JText::_('VCMBCAHSERV6');?></option>"+
								"<option value=\"15\"><?php echo JText::_('VCMBCAHSERV7');?></option>"+
								"<option value=\"16\"><?php echo JText::_('VCMBCAHSERV8');?></option>"+
								"<option value=\"22\"><?php echo JText::_('VCMBCAHSERV9');?></option>"+
								"<option value=\"26\"><?php echo JText::_('VCMBCAHSERV10');?></option>"+
								"<option value=\"33\"><?php echo JText::_('VCMBCAHSERV11');?></option>"+
								"<option value=\"35\"><?php echo JText::_('VCMBCAHSERV12');?></option>"+
								"<option value=\"41\"><?php echo JText::_('VCMBCAHSERV13');?></option>"+
								"<option value=\"44\"><?php echo JText::_('VCMBCAHSERV14');?></option>"+
								"<option value=\"45\"><?php echo JText::_('VCMBCAHSERV15');?></option>"+
								"<option value=\"49\"><?php echo JText::_('VCMBCAHSERV16');?></option>"+
								"<option value=\"54\"><?php echo JText::_('VCMBCAHSERV17');?></option>"+
								"<option value=\"60\"><?php echo JText::_('VCMBCAHSERV18');?></option>"+
								"<option value=\"61\"><?php echo JText::_('VCMBCAHSERV19');?></option>"+
								"<option value=\"62\"><?php echo JText::_('VCMBCAHSERV20');?></option>"+
								"<option value=\"76\"><?php echo JText::_('VCMBCAHSERV21');?></option>"+
								"<option value=\"77\"><?php echo JText::_('VCMBCAHSERV22');?></option>"+
								"<option value=\"78\"><?php echo JText::_('VCMBCAHSERV23');?></option>"+
								"<option value=\"79\"><?php echo JText::_('VCMBCAHSERV24');?></option>"+
								"<option value=\"81\"><?php echo JText::_('VCMBCAHSERV25');?></option>"+
								"<option value=\"83\"><?php echo JText::_('VCMBCAHSERV26');?></option>"+
								"<option value=\"86\"><?php echo JText::_('VCMBCAHSERV27');?></option>"+
								"<option value=\"91\"><?php echo JText::_('VCMBCAHSERV28');?></option>"+
								"<option value=\"96\"><?php echo JText::_('VCMBCAHSERV29');?></option>"+
								"<option value=\"97\"><?php echo JText::_('VCMBCAHSERV30');?></option>"+
								"<option value=\"98\"><?php echo JText::_('VCMBCAHSERV31');?></option>"+
								"<option value=\"122\"><?php echo JText::_('VCMBCAHSERV32');?></option>"+
								"<option value=\"159\"><?php echo JText::_('VCMBCAHSERV33');?></option>"+
								"<option value=\"165\"><?php echo JText::_('VCMBCAHSERV34');?></option>"+
								"<option value=\"168\"><?php echo JText::_('VCMBCAHSERV35');?></option>"+
								"<option value=\"173\"><?php echo JText::_('VCMBCAHSERV36');?></option>"+
								"<option value=\"193\"><?php echo JText::_('VCMBCAHSERV37');?></option>"+
								"<option value=\"197\"><?php echo JText::_('VCMBCAHSERV38');?></option>"+
								"<option value=\"198\"><?php echo JText::_('VCMBCAHSERV39');?></option>"+
								"<option value=\"202\"><?php echo JText::_('VCMBCAHSERV40');?></option>"+
								"<option value=\"228\"><?php echo JText::_('VCMBCAHSERV41');?></option>"+
								"<option value=\"233\"><?php echo JText::_('VCMBCAHSERV42');?></option>"+
								"<option value=\"234\"><?php echo JText::_('VCMBCAHSERV43');?></option>"+
								"<option value=\"236\"><?php echo JText::_('VCMBCAHSERV44');?></option>"+
								"<option value=\"237\"><?php echo JText::_('VCMBCAHSERV45');?></option>"+
								"<option value=\"239\"><?php echo JText::_('VCMBCAHSERV46');?></option>"+
								"<option value=\"242\"><?php echo JText::_('VCMBCAHSERV47');?></option>"+
								"<option value=\"262\"><?php echo JText::_('VCMBCAHSERV48');?></option>"+
								"<option value=\"269\"><?php echo JText::_('VCMBCAHSERV49');?></option>"+
								"<option value=\"272\"><?php echo JText::_('VCMBCAHSERV50');?></option>"+
								"<option value=\"282\"><?php echo JText::_('VCMBCAHSERV51');?></option>"+
								"<option value=\"283\"><?php echo JText::_('VCMBCAHSERV52');?></option>"+
								"<option value=\"292\"><?php echo JText::_('VCMBCAHSERV53');?></option>"+
								"<option value=\"310\"><?php echo JText::_('VCMBCAHSERV54');?></option>"+
								"<option value=\"312\"><?php echo JText::_('VCMBCAHSERV55');?></option>"+
							"</select>"+
						"</div>"+
						"<div class=\"vcm-bcah-subdetail vcm-subdetail-checkbox-detail\">"+
							"<label><?php echo JText::_('VCMBCAHSERVINCL');?></label>"+
							"<input type=\"checkbox\" name=\"vcm-bcah-service"+ServiceIndex+"-included\"/>"+
						"</div>"+
						"<div class=\"vcm-bcah-subdetail vcm-bcah-breakfast-price\" style=\"display: none;\">"+
							"<label><?php echo JText::_('VCMBCAHBRKFPRICE');?></label>"+
							"<input type=\"number\" name=\"vcm-bcah-service"+ServiceIndex+"-price\" min=\"0\"/>"+
						"</div>"+
						"<div class=\"vcm-bcah-subdetail vcm-bcah-breakfast-type\" style=\"display: none;\">"+
							"<label><?php echo JText::_('VCMBCAHBRKFTYPE');?></label>"+
							"<select name=\"vcm-bcah-service"+ServiceIndex+"-breakfast-type[]\" multiple>"+
								"<option value=\"5001\"><?php echo JText::_('VCMBCAHBRKFTYPE1');?></option>"+
								"<option value=\"5002\"><?php echo JText::_('VCMBCAHBRKFTYPE2');?></option>"+
								"<option value=\"5003\"><?php echo JText::_('VCMBCAHBRKFTYPE3');?></option>"+
								"<option value=\"5004\"><?php echo JText::_('VCMBCAHBRKFTYPE4');?></option>"+
								"<option value=\"5005\"><?php echo JText::_('VCMBCAHBRKFTYPE5');?></option>"+
								"<option value=\"5006\"><?php echo JText::_('VCMBCAHBRKFTYPE6');?></option>"+
								"<option value=\"5007\"><?php echo JText::_('VCMBCAHBRKFTYPE7');?></option>"+
								"<option value=\"5008\"><?php echo JText::_('VCMBCAHBRKFTYPE8');?></option>"+
								"<option value=\"5009\"><?php echo JText::_('VCMBCAHBRKFTYPE9');?></option>"+
							"</select>"+
						"</div>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-service"+ServiceIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>");

				ServiceIndex++;
			}
			else if(jQuery(this).hasClass("vcm-bcah-amenity")){
				var appendableText = "<div class=\"vcm-bcah-entry-instance vcm-bcah-amenity"+AmenityIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-amenity-index[]\" value=\""+AmenityIndex+"\"/>"+
					"<div class=\"vcm-bcah-entry-contents\">"+
						"<div class=\"vcm-bcah-subdetail\">"+
							"<label><?php echo JText::_('VCMBCAHAMENITY');?></label>"+
							"<select name=\"vcm-bcah-amenity"+AmenityIndex+"-selected-amenity\" class=\"vcm-bcah-amenity-selector\">"+
								"<option value=\"\"></option>"
								<?php 
									foreach ($RMAAmenitiesNames as $value) {
								?>
								appendableText += "<option value=\"<?php echo $RMAAmenitiesCodes[$value];?>\"><?php echo $value;?></option>";
								<?php
									}
								?>
						appendableText+="</select>"+
						"</div>"+
						"<div class=\"vcm-bcah-subdetail\">"+
							"<label><?php echo JText::_('VCMBCAHQUANTITY');?></label>"+
							"<input type=\"number\" name=\"vcm-bcah-amenity"+AmenityIndex+"-quantity\" id=\"vcm-bcah-amenity"+AmenityIndex+"-quantity\" min=\"1\"/>"+
						"</div>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-amenity"+AmenityIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>";
				jQuery(".vcm-bcah-amenities-div").find(".vcm-bcah-entry-instance-container").append(appendableText);

				AmenityIndex++;

			}
			else if(jQuery(this).hasClass("vcm-bcah-guaranteepayment")){
				var appendableText = "<div class=\"vcm-bcah-entry-instance vcm-bcah-guaranteepayment"+GuaranteepaymentIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-guaranteepayment-index[]\" value=\""+GuaranteepaymentIndex+"\"/>"+
					"<div class=\"vcm-bcah-entry-contents\">"+
						"<label><?php echo JText::_('VCMBCAHGUAPAYPOL');?></label>"+
						"<select name=\"vcm-bcah-guaranteepayment"+GuaranteepaymentIndex+"-selected-guaranteed-payment\">";
							<?php 
								foreach ($policyCodes as $key => $value) {
							?>
							appendableText += "<option value=\"<?php echo $value;?>\"><?php echo $key;?></option>";
							<?php
								}
							?>
						appendableText += "</select>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-guaranteepayment"+GuaranteepaymentIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>";

				jQuery(".vcm-bcah-guaranteepayments-div").find(".vcm-bcah-entry-instance-container").append(appendableText);

				GuaranteepaymentIndex++;

			}
			else if(jQuery(this).hasClass("vcm-bcah-cancelpolicy")){
				var appendableText = "<div class=\"vcm-bcah-entry-instance vcm-bcah-cancelpolicy"+CancelpolicyIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-cancelpolicy-index[]\" value=\""+CancelpolicyIndex+"\"/>"+
					"<div class=\"vcm-bcah-entry-contents\">"+
						"<label><?php echo JText::_('VCMBCAHCANCPOL');?></label>"+
						"<select name=\"vcm-bcah-cancelpolicy"+CancelpolicyIndex+"-selected-cancel-policy\">";
							<?php 
								foreach ($policyCodes as $key => $value) {
							?>
							appendableText += "<option value=\"<?php echo $value;?>\"><?php echo $key;?></option>";
							<?php
								}
							?>
						appendableText += "</select>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-cancelpolicy"+CancelpolicyIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>";

				jQuery(".vcm-bcah-cancelpolicies-div").find(".vcm-bcah-entry-instance-container").append(appendableText);

				CancelpolicyIndex++;
				
			}
			else if(jQuery(this).hasClass("vcm-bcah-tax")){

				jQuery(".vcm-bcah-taxes-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-tax"+TaxIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-tax-index[]\" value=\""+TaxIndex+"\"/>"+
					"<div class=\"vcm-bcah-entry-header\">"+
						"<span>"+
							"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-tax"+TaxIndex+"-icon\" id=\"vcm-bcah-tax"+TaxIndex+"\"></i>"+
							"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-tax"+TaxIndex+"\"><?php echo JText::_('VCMBCAHTAX');?></span>"+
						"</span>"+
					"</div>"+
					"<div class=\"vcm-bcah-entry-contents vcm-bcah-tax"+TaxIndex+"-div\">"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHTAXTYPE');?></label>"+
							"<select name=\"vcm-bcah-tax"+TaxIndex+"-selected-tax\">"+
								"<option value=\"3\"><?php echo JText::_('VCMBCAHTAXTYPE1');?></option>"+
								"<option value=\"13\"><?php echo JText::_('VCMBCAHTAXTYPE2');?></option>"+
								"<option value=\"35\"><?php echo JText::_('VCMBCAHTAXTYPE3');?></option>"+
								"<option value=\"36\"><?php echo JText::_('VCMBCAHTAXTYPE4');?></option>"+
								"<option value=\"46\"><?php echo JText::_('VCMBCAHTAXTYPE5');?></option>"+
								"<option value=\"5001\"><?php echo JText::_('VCMBCAHTAXTYPE6');?></option>"+
								"<option value=\"5002\"><?php echo JText::_('VCMBCAHTAXTYPE7');?></option>"+
								"<option value=\"5004\"><?php echo JText::_('VCMBCAHTAXTYPE8');?></option>"+
								"<option value=\"5007\"><?php echo JText::_('VCMBCAHTAXTYPE9');?></option>"+
								"<option value=\"5008\"><?php echo JText::_('VCMBCAHTAXTYPE10');?></option>"+
							"</select>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHAMOUNT');?></label>"+
							"<input type=\"number\" name=\"vcm-bcah-tax"+TaxIndex+"-amount\" step=\"any\"/>"+
							"<span>%</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHDECIMALPLACES');?></label>"+
							"<input type=\"number\" name=\"vcm-bcah-tax"+TaxIndex+"-decimal-places\" min=\"0\"/>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHPRICETYPE');?></label>"+
							"<select name=\"vcm-bcah-tax"+TaxIndex+"-type\">"+
								"<option value=\"Inclusive\"><?php echo JText::_('VCMBCAHINCLUS');?></option>"+
								"<option value=\"Exclusive\"><?php echo JText::_('VCMBCAHEXCLUS');?></option>"+
							"</select>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHCHGFRQ');?></label>"+
							"<select name=\"vcm-bcah-tax"+TaxIndex+"-charge-frequency\">"+
								"<option value=\"12\"><?php echo JText::_('VCMBCAHCHGFRQ1');?></option>"+
								"<option value=\"19\"><?php echo JText::_('VCMBCAHCHGFRQ2');?></option>"+
								"<option value=\"20\"><?php echo JText::_('VCMBCAHCHGFRQ3');?></option>"+
								"<option value=\"21\"><?php echo JText::_('VCMBCAHCHGFRQ4');?></option>"+
								"<option value=\"5000\"><?php echo JText::_('VCMBCAHCHGFRQ5');?></option>"+
							"</select>"+
						"</div>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-tax"+TaxIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>");

				TaxIndex++;
				
			}
			else if(jQuery(this).hasClass("vcm-bcah-fee")){

				jQuery(".vcm-bcah-fees-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-fee"+FeeIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-fee-index[]\" value=\""+FeeIndex+"\"/>"+
					"<div class=\"vcm-bcah-entry-header\">"+
						"<span>"+
							"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-fee"+FeeIndex+"-icon\" id=\"vcm-bcah-fee"+FeeIndex+"\"></i>"+
							"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-fee"+FeeIndex+"\"><?php echo JText::_('VCMBCAHFEE');?></span>"+
						"</span>"+
					"</div>"+
					"<div class=\"vcm-bcah-entry-contents vcm-bcah-fee"+FeeIndex+"-div\">"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHFEETYPE');?></label>"+
							"<select name=\"vcm-bcah-fee"+FeeIndex+"-selected-fee\">"+
								"<option value=\"12\"><?php echo JText::_('VCMBCAHFEETYPE1');?></option>"+
								"<option value=\"14\"><?php echo JText::_('VCMBCAHFEETYPE2');?></option>"+
								"<option value=\"18\"><?php echo JText::_('VCMBCAHFEETYPE3');?></option>"+
								"<option value=\"55\"><?php echo JText::_('VCMBCAHFEETYPE4');?></option>"+
								"<option value=\"5000\"><?php echo JText::_('VCMBCAHFEETYPE5');?></option>"+
								"<option value=\"5003\"><?php echo JText::_('VCMBCAHFEETYPE6');?></option>"+
								"<option value=\"5005\"><?php echo JText::_('VCMBCAHFEETYPE7');?></option>"+
								"<option value=\"5006\"><?php echo JText::_('VCMBCAHFEETYPE8');?></option>"+
								"<option value=\"5009\"><?php echo JText::_('VCMBCAHFEETYPE9');?></option>"+
								"<option value=\"5010\"><?php echo JText::_('VCMBCAHFEETYPE10');?></option>"+
								"<option value=\"5011\"><?php echo JText::_('VCMBCAHFEETYPE11');?></option>"+
								"<option value=\"5012\"><?php echo JText::_('VCMBCAHFEETYPE12');?></option>"+
								"<option value=\"5013\"><?php echo JText::_('VCMBCAHFEETYPE13');?></option>"+
								"<option value=\"5014\"><?php echo JText::_('VCMBCAHFEETYPE14');?></option>"+
								"<option value=\"5015\"><?php echo JText::_('VCMBCAHFEETYPE15');?></option>"+
								"<option value=\"5016\"><?php echo JText::_('VCMBCAHFEETYPE16');?></option>"+
								"<option value=\"5017\"><?php echo JText::_('VCMBCAHFEETYPE17');?></option>"+
								"<option value=\"5018\"><?php echo JText::_('VCMBCAHFEETYPE18');?></option>"+
								"<option value=\"5019\"><?php echo JText::_('VCMBCAHFEETYPE19');?></option>"+
								"<option value=\"5020\"><?php echo JText::_('VCMBCAHFEETYPE20');?></option>"+
								"<option value=\"5021\"><?php echo JText::_('VCMBCAHFEETYPE21');?></option>"+
								"<option value=\"5022\"><?php echo JText::_('VCMBCAHFEETYPE22');?></option>"+
								"<option value=\"5023\"><?php echo JText::_('VCMBCAHFEETYPE23');?></option>"+
								"<option value=\"5024\"><?php echo JText::_('VCMBCAHFEETYPE24');?></option>"+
								"<option value=\"5025\"><?php echo JText::_('VCMBCAHFEETYPE25');?></option>"+
								"<option value=\"5026\"><?php echo JText::_('VCMBCAHFEETYPE26');?></option>"+
								"<option value=\"5027\"><?php echo JText::_('VCMBCAHFEETYPE27');?></option>"+
								"<option value=\"5028\"><?php echo JText::_('VCMBCAHFEETYPE28');?></option>"+
								"<option value=\"5029\"><?php echo JText::_('VCMBCAHFEETYPE29');?></option>"+
								"<option value=\"5030\"><?php echo JText::_('VCMBCAHFEETYPE30');?></option>"+
								"<option value=\"5031\"><?php echo JText::_('VCMBCAHFEETYPE31');?></option>"+
								"<option value=\"5032\"><?php echo JText::_('VCMBCAHFEETYPE32');?></option>"+
								"<option value=\"5033\"><?php echo JText::_('VCMBCAHFEETYPE33');?></option>"+
								"<option value=\"5034\"><?php echo JText::_('VCMBCAHFEETYPE34');?></option>"+
								"<option value=\"5035\"><?php echo JText::_('VCMBCAHFEETYPE35');?></option>"+
								"<option value=\"5036\"><?php echo JText::_('VCMBCAHFEETYPE36');?></option>"+
							"</select>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHAMOUNT');?></label>"+
							"<input type=\"number\" name=\"vcm-bcah-fee"+FeeIndex+"-amount\" step=\"any\"/>"+
							"<span>%</span>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHDECIMALPLACES');?></label>"+
							"<input type=\"number\" name=\"vcm-bcah-fee"+FeeIndex+"-decimal-places\" min=\"0\"/>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHPRICETYPE');?></label>"+
							"<select name=\"vcm-bcah-fee"+FeeIndex+"-type\">"+
								"<option value=\"Inclusive\"><?php echo JText::_('VCMBCAHINCLUS');?></option>"+
								"<option value=\"Exclusive\"><?php echo JText::_('VCMBCAHEXCLUS');?></option>"+
							"</select>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHCHGFRQ');?></label>"+
							"<select name=\"vcm-bcah-fee"+FeeIndex+"-charge-frequency\">"+
								"<option value=\"12\"><?php echo JText::_('VCMBCAHCHGFRQ1');?></option>"+
								"<option value=\"19\"><?php echo JText::_('VCMBCAHCHGFRQ2');?></option>"+
								"<option value=\"20\"><?php echo JText::_('VCMBCAHCHGFRQ3');?></option>"+
								"<option value=\"21\"><?php echo JText::_('VCMBCAHCHGFRQ4');?></option>"+
								"<option value=\"5000\"><?php echo JText::_('VCMBCAHCHGFRQ5');?></option>"+
							"</select>"+
						"</div>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-fee"+FeeIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>");

				FeeIndex++;
				
			}
			else if(jQuery(this).hasClass("vcm-bcah-paymentmethod")){
				jQuery(".vcm-bcah-paymentmethods-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-paymentmethod"+PaymentmethodIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-paymentmethod-index[]\" value=\""+PaymentmethodIndex+"\"/>"+
					"<div class=\"vcm-bcah-entry-contents\">"+
						"<label><?php echo JText::_('VCMBCAHPAYMETH');?></label>"+
						"<select name=\"vcm-bcah-paymentmethod"+PaymentmethodIndex+"-selected-payment-method\">"+
							"<option value=\"1\"><?php echo JText::_('VCMBCAHPAYMETH1');?></option>"+
							"<option value=\"2\"><?php echo JText::_('VCMBCAHPAYMETH2');?></option>"+
							"<option value=\"3\"><?php echo JText::_('VCMBCAHPAYMETH3');?></option>"+
							"<option value=\"4\"><?php echo JText::_('VCMBCAHPAYMETH4');?></option>"+
							"<option value=\"5\"><?php echo JText::_('VCMBCAHPAYMETH5');?></option>"+
							"<option value=\"7\"><?php echo JText::_('VCMBCAHPAYMETH6');?></option>"+
							"<option value=\"8\"><?php echo JText::_('VCMBCAHPAYMETH7');?></option>"+
							"<option value=\"9\"><?php echo JText::_('VCMBCAHPAYMETH8');?></option>"+
							"<option value=\"10\"><?php echo JText::_('VCMBCAHPAYMETH9');?></option>"+
							"<option value=\"11\"><?php echo JText::_('VCMBCAHPAYMETH10');?></option>"+
							"<option value=\"12\"><?php echo JText::_('VCMBCAHPAYMETH11');?></option>"+
							"<option value=\"13\"><?php echo JText::_('VCMBCAHPAYMETH12');?></option>"+
							"<option value=\"14\"><?php echo JText::_('VCMBCAHPAYMETH13');?></option>"+
							"<option value=\"15\"><?php echo JText::_('VCMBCAHPAYMETH14');?></option>"+
							"<option value=\"16\"><?php echo JText::_('VCMBCAHPAYMETH15');?></option>"+
							"<option value=\"17\"><?php echo JText::_('VCMBCAHPAYMETH16');?></option>"+
							"<option value=\"18\"><?php echo JText::_('VCMBCAHPAYMETH17');?></option>"+
							"<option value=\"19\"><?php echo JText::_('VCMBCAHPAYMETH18');?></option>"+
							"<option value=\"21\"><?php echo JText::_('VCMBCAHPAYMETH19');?></option>"+
							"<option value=\"22\"><?php echo JText::_('VCMBCAHPAYMETH20');?></option>"+
							"<option value=\"23\"><?php echo JText::_('VCMBCAHPAYMETH21');?></option>"+
							"<option value=\"25\"><?php echo JText::_('VCMBCAHPAYMETH22');?></option>"+
							"<option value=\"26\"><?php echo JText::_('VCMBCAHPAYMETH23');?></option>"+
							"<option value=\"27\"><?php echo JText::_('VCMBCAHPAYMETH24');?></option>"+
							"<option value=\"28\"><?php echo JText::_('VCMBCAHPAYMETH25');?></option>"+
							"<option value=\"29\"><?php echo JText::_('VCMBCAHPAYMETH26');?></option>"+
							"<option value=\"30\"><?php echo JText::_('VCMBCAHPAYMETH27');?></option>"+
							"<option value=\"31\"><?php echo JText::_('VCMBCAHPAYMETH28');?></option>"+
							"<option value=\"32\"><?php echo JText::_('VCMBCAHPAYMETH29');?></option>"+
							"<option value=\"34\"><?php echo JText::_('VCMBCAHPAYMETH30');?></option>"+
							"<option value=\"35\"><?php echo JText::_('VCMBCAHPAYMETH31');?></option>"+
							"<option value=\"36\"><?php echo JText::_('VCMBCAHPAYMETH32');?></option>"+
							"<option value=\"37\"><?php echo JText::_('VCMBCAHPAYMETH33');?></option>"+
							"<option value=\"38\"><?php echo JText::_('VCMBCAHPAYMETH34');?></option>"+
							"<option value=\"39\"><?php echo JText::_('VCMBCAHPAYMETH35');?></option>"+
							"<option value=\"40\"><?php echo JText::_('VCMBCAHPAYMETH36');?></option>"+
							"<option value=\"41\"><?php echo JText::_('VCMBCAHPAYMETH37');?></option>"+
							"<option value=\"42\"><?php echo JText::_('VCMBCAHPAYMETH38');?></option>"+
							"<option value=\"43\"><?php echo JText::_('VCMBCAHPAYMETH39');?></option>"+
							"<option value=\"44\"><?php echo JText::_('VCMBCAHPAYMETH40');?></option>"+
						"</select>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-paymentmethod"+PaymentmethodIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>");

				PaymentmethodIndex++;
				
			}
			else if(jQuery(this).hasClass("vcm-bcah-attraction")){
				jQuery(".vcm-bcah-attractions-div").find(".vcm-bcah-entry-instance-container").append("<div class=\"vcm-bcah-entry-instance vcm-bcah-attraction"+AttractionIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-attraction-index[]\" value=\""+AttractionIndex+"\"/>"+
					"<div class=\"vcm-bcah-entry-header\">"+
						"<span>"+
							"<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-attraction"+AttractionIndex+"-icon\" id=\"vcm-bcah-attraction"+AttractionIndex+"\"></i>"+
							"<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-attraction"+AttractionIndex+"\"><?php echo JText::_('VCMBCAHATTRACTION');?></span>"+
						"</span>"+
					"</div>"+
					"<div class=\"vcm-bcah-entry-contents vcm-bcah-attraction"+AttractionIndex+"-div\">"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHATTNAME');?></label>"+
							"<input type=\"text\" name=\"vcm-bcah-attraction"+AttractionIndex+"-name\"/>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHATTTYPE');?></label>"+
							"<select name=\"vcm-bcah-attraction"+AttractionIndex+"-selected-attraction-type\">"+
								"<option value=\"5\"><?php echo JText::_('VCMBCAHATTTYPE1');?></option>"+
								"<option value=\"25\"><?php echo JText::_('VCMBCAHATTTYPE2');?></option>"+
								"<option value=\"29\"><?php echo JText::_('VCMBCAHATTTYPE3');?></option>"+
								"<option value=\"31\"><?php echo JText::_('VCMBCAHATTTYPE4');?></option>"+
								"<option value=\"33\"><?php echo JText::_('VCMBCAHATTTYPE5');?></option>"+
								"<option value=\"41\"><?php echo JText::_('VCMBCAHATTTYPE6');?></option>"+
								"<option value=\"42\"><?php echo JText::_('VCMBCAHATTTYPE7');?></option>"+
								"<option value=\"45\"><?php echo JText::_('VCMBCAHATTTYPE8');?></option>"+
								"<option value=\"47\"><?php echo JText::_('VCMBCAHATTTYPE9');?></option>"+
								"<option value=\"73\"><?php echo JText::_('VCMBCAHATTTYPE10');?></option>"+
							"</select>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHDISTANCE');?></label>"+
							"<input type=\"number\" step=\"any\" name=\"vcm-bcah-attraction"+AttractionIndex+"-distance\"/>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-detail\">"+
							"<label><?php echo JText::_('VCMBCAHDISTMSR');?></label>"+
							"<select name=\"vcm-bcah-attraction"+AttractionIndex+"-distance-measurement\">"+
								"<option value=\"miles\"><?php echo JText::_('VCMBCAHDISTMSR1');?></option>"+
								"<option value=\"meters\"><?php echo JText::_('VCMBCAHDISTMSR2');?></option>"+
								"<option value=\"kilometers\"><?php echo JText::_('VCMBCAHDISTMSR3');?></option>"+
								"<option value=\"feet\"><?php echo JText::_('VCMBCAHDISTMSR4');?></option>"+
							"</select>"+
						"</div>"+
						"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-attraction"+AttractionIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
					"</div>"+
				"</div>");

				AttractionIndex++;
				
			}
		});
		
		jQuery(".vcm-bcah-phone-tech-type-selector").change();
		if(jQuery(".vcm-bcah-insert-type").val()=="New"){
			jQuery(".vcm-bcah-insert-type").change();
		}
		jQuery('.vcm-bcah-hotel-name-input').val("<?php echo $hotelName;?>");
		jQuery(".vcm-bcah-saved-value").change();
		
	});

	function uploadImageAJAX(input) {
		//console.log(jQuery(input));
		jQuery(input).parent().next(".vcm-bcah-entry-container").show();
		var index = jQuery(input).data('index');
		jQuery(".vcm-loading-overlay").show();
		var formData = new FormData( jQuery('#vcm-bcah-multimedia-form')[0] );
		
		jQuery.noConflict();
		var imgurl="";
		
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_vikchannelmanager&task=upload_image_ajax&tmpl=component",
			data: formData,
			cache: false,
			processData: false,
			contentType: false
		}).done(function(resp){
			jQuery(".vcm-loading-overlay").hide();
			var obj = JSON.parse(resp); 
			//console.log(obj);
			if( obj[0] == 1 ) {
				if(jQuery("#image-status"+index).length!=0){
					jQuery(input).next('#image-status'+index).remove();
				}
				/**
				 * @wponly  The Booking.com Contents API use the directory below, which is in a different path for WP
				 */
				imgurl = '<?php echo VCM_ADMIN_URI;?>assets/vcm/'+obj[2];

				var appendableText = "<div class=\"vcm-bcah-entry-instance vcm-bcah-image"+ImageIndex+"\">"+
					"<input type=\"hidden\" name=\"vcm-bcah-image-index[]\" value=\""+ImageIndex+"\"/>"+
					"<div class=\"vcm-bcah-image-instance\">"+
						"<div class=\"vcm-bcah-entry-header\">"+
							"<div class=\"vcm-bcah-image-holder\">"+
								"<img src=\""+imgurl+"\"/>"+
							"</div>"+
						"</div>"+
						"<div class=\"vcm-bcah-entry-contents vcm-bcah-image"+ImageIndex+"-div\">"+
							"<div class=\"vcm-bcah-detail\">"+
								"<div class=\"vcm-bcah-subdetail\">"+
									"<label><?php echo JText::_('VCMBCAHIMGURL');?></label>"+
									"<input type=\"text\" disabled name=\"vcm-bcah-image"+ImageIndex+"-image-url-shown\" value=\""+imgurl+"\" size=\"100\"/>"+
									"<input type=\"hidden\" name=\"vcm-bcah-image"+ImageIndex+"-image-url\" value=\""+imgurl+"\"/>"+
								"</div>"+
								"<div class=\"vcm-bcah-subdetail\">"+
									"<label><?php echo JText::_('VCMBCAHIMGTAG');?></label>"+
									"<select name=\"vcm-bcah-image"+ImageIndex+"-selected-tag[]\" multiple class=\"vcm-bca-multi-select\">";
										<?php 
											foreach ($imageTagCodes as $key => $value) {
										?>
										appendableText += "<option value=\"<?php echo $value;?>\"><?php echo $key;?></option>";
										<?php
											}
										?>
									appendableText += "</select>"+
								"</div>"+
								"<div class=\"vcm-bcah-image-subdetails\">"+
									"<div class=\"vcm-bcah-subdetail vcm-bcah-subdetail-checkbox-detail\">"+
										"<label><?php echo JText::_('VCMBCAHMAINIMAGE');?></label>"+
										"<input type=\"checkbox\" name=\"vcm-bcah-image"+ImageIndex+"-main-image\"/>"+
									"</div>"+
								"</div>"+
							"</div>"+
						"</div>"+
						"<div class=\"vcm-bcah-image-controller\">"+
							"<button type=\"button\" class=\"btn vcm-bcah-hide-button\" id=\"vcm-bcah-image"+ImageIndex+"\"><?php echo JText::_('VCMBCAHSHHIDETAILS');?></button>"+
							"<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-image"+ImageIndex+"\"><i class=\"vboicn-cancel-circle\"></i><?php echo JText::_('VCMBCAHDELETE');?></button>"+
						"</div>"+
					"</div>";

				jQuery(".vcm-bcah-images-div").find(".vcm-bcah-entry-instance-container").append(appendableText);

				jQuery('.vcm-bcah-image'+ImageIndex+'-div').find(".vcm-bca-multi-select").select2({
					allowClear: false,
					placeholder: "<?php echo addslashes(JText::_('VCMBCAIMAGETAGS')); ?>",
					width: 300
				});

				ImageIndex++;
				jQuery('#vcm-bcah-image-input').wrap('<form>').closest('form').get(0).reset();
  				jQuery('#vcm-bcah-image-input').unwrap();

			} else {
				if(jQuery("#image-status"+index).length==0){
					jQuery(input).after('<img src="<?php echo VCM_ADMIN_URI; ?>assets/css/images/no.gif" id="image-status'+index+'" />');
				}
				else{
				}
				alert(obj[1]);
			}
			
		});
	}
</script>

<div class="vcm-loading-overlay">
	<div class="vcm-loading-processing"><?php echo JText::_('VCMPROCESSING'); ?></div>
	<div class="vcm-loading-dot vcm-loading-dot1"></div>
	<div class="vcm-loading-dot vcm-loading-dot2"></div>
	<div class="vcm-loading-dot vcm-loading-dot3"></div>
	<div class="vcm-loading-dot vcm-loading-dot4"></div>
	<div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-bcah-content-api-container">
	<div id="vcm-bcah-tab-header">
		<div id="1" class="vcm-bcah-tab <?php if(VikRequest::getString('tab')==""||VikRequest::getString('tab')=="contact-info"){echo "vcm-bcah-active";}?>">
			<span><?php echo JText::_('VCMBCAHCINFO');?></span>
		</div>
		<div id="2" class="vcm-bcah-tab <?php if(VikRequest::getString('tab')=="hotel-info"){echo "vcm-bcah-active";}?>">
			<span><?php echo JText::_('VCMBCAHHINFO');?></span>
		</div>
		<div id="3" class="vcm-bcah-tab <?php if(VikRequest::getString('tab')=="facility-info"){echo "vcm-bcah-active";}?>">
			<span><?php echo JText::_('VCMBCAHFINFO');?></span>
		</div>
		<div id="4" class="vcm-bcah-tab <?php if(VikRequest::getString('tab')=="area-info"){echo "vcm-bcah-active";}?>">
			<span><?php echo JText::_('VCMBCAHAINFO');?></span>
		</div>
		<div id="5" class="vcm-bcah-tab <?php if(VikRequest::getString('tab')=="policies"){echo "vcm-bcah-active";}?>">
			<span><?php echo JText::_('VCMBCAHPOLICIES');?></span>
		</div>
		<div id="6" class="vcm-bcah-tab <?php if(VikRequest::getString('tab')=="multimedia"){echo "vcm-bcah-active";}?>">
			<span><?php echo JText::_('VCMBCAHMULTIMEDIA');?></span>
		</div>
		<div id="7" class="vcm-bcah-tab <?php if(VikRequest::getString('tab')=="standardphrases"){echo "vcm-bcah-active";}?>">
			<span><?php echo JText::_('VCMBCAHSPHRASES');?></span>
		</div>
	</div>
	<div class="vcm-bcah-user-details" <?php echo $insertType=="New"? "style=\"display:none;\"":"";?>>
		<div><h3><?php echo JText::_('VCMBCAHUSERID');?><span><?php echo $channel['params']['hotelid'];?></span></h3></div>
		<div><h4><?php echo JText::_('VCMBCAHACCOUNTNAME');?><span><?php echo $hotelName;?></span></h4></div>
	</div>
	<div id="contents">
		<div id="1" class="vcm-bcah-container-content" style="<?php if(VikRequest::getString('tab')!="contact-info"&&VikRequest::getString('tab')!=""){echo "display: none;";}?>">
			<form name="vcm-bcah-contact-info-form" id="vcm-bcah-contact-info-form" method="POST" action="index.php?option=com_vikchannelmanager&task=bca.makeHotelXml">
				<!--<input type="hidden" name="progID" value="<?php echo $progID;?>"/>-->
				<input type="hidden" name="accountName" value="<?php echo $hotelName;?>"/>
				<div class="vcm-bcah-tab-title">
					<span class="vcm-bcah-content-title"><?php echo JText::_('VCMBCAHCINFO');?></span>
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
				<input type="hidden" name="submittedform" value="contact-info"/>
				<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
				<div class="vcm-bcah-entry-detail">
					<label><?php echo JText::_('VCMBCAHINSERTTYPE');?></label>
					<select name="vcm-bcah-insert-type" class="vcm-bcah-insert-type">
						<option value="Overlay" <?php echo $insertType=="Overlay"? "selected":"";?>><?php echo JText::_('VCMBCAHUPDATEPROP')." ".$channel['params']['hotelid']." - ".$hotelName;?></option>
						<option value="New" <?php echo $insertType=="New"? "selected":"";?>><?php echo JText::_('VCMBCAHNEWPROP');?></option>
					</select>
				</div>
				<div class="vcm-bcah-physical-location-container">
					<div class="vcm-bcah-detail vcm-bcah-container-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'physical_location'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-physical-location-icon" id="vcm-bcah-physical-location"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-physical-location"><?php echo JText::_('VCMBCAHCINFOTYPE10');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-physical-location-div vcm-bcah-category-div" style="<?php echo (!property_exists($oldData, 'physical_location'))? "display:none;" : "";?>">
						<div class="vcm-bcah-entry-instance vcm-bcah-address-physical-location vcm-bcah-entry-contents">
							<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
								<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
								<select name="vcm-bcah-physical-location-language">
									<option value=""></option>
									<?php foreach ($languageCodes as $key => $value) {
										echo "<option value=\"".$value."\" ".($oldData->physical_location->address->language==$value? "selected":"").">".$key."</option>";
									} ?>
								</select>
							</div>
							<div class="vcm-bcah-hotel-name">
								<label><?php echo JText::_('VCMBCAHHOTELNAME');?></label>
								<input type="text" name="vcm-bcah-address-physical-location-hotel-name" class="vcm-bcah-hotel-name-input" value="<?php echo $hotelName;?>"/>
							</div>
							<div class="vcm-bcah-entry-detail">
								<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
								<select name="vcm-bcah-address-physical-location-country">
									<option value=""></option>
									<?php foreach ($countryCodes as $key => $value) {
										echo "<option value=\"".$value."\" ".($oldData->physical_location->address->country==$value? "selected":"").">".$key."</option>";
									} ?>
								</select>
							</div>
							<div class="vcm-bcah-entry-detail">
								<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
								<input type="text" name="vcm-bcah-address-physical-location-city-name" value="<?php echo isset($oldData->physical_location->address->cityName) ? $oldData->physical_location->address->cityName : '';?>"/>
							</div>
							<div class="vcm-bcah-entry-detail">
								<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
								<input type="text" name="vcm-bcah-address-physical-location-address-line" value="<?php echo isset($oldData->physical_location->address->addressLine) ? $oldData->physical_location->address->addressLine : '';?>"/>
							</div>
							<div class="vcm-bcah-entry-detail">
								<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
								<input type="text" name="vcm-bcah-address-physical-location-postal-code" value="<?php echo isset($oldData->physical_location->address->postalCode) ? $oldData->physical_location->address->postalCode : '';?>"/>
							</div>
							<div class="vcm-bcah-entry-detail">
								<label><?php echo JText::_('VCMBCAHPROPLICNUM');?></label>
								<input type="text" name="vcm-bcah-address-physical-location-property-license-number" value="<?php echo isset($oldData->physical_location->address->propertyLicenseNumber) ? $oldData->physical_location->address->propertyLicenseNumber : '';?>"/>
								<?php echo $vik->createPopover(array('title' => JText::_('VCMBCAHPROPLICNUMPPVT'), 'content' => JText::_('VCMBCAHPROPLICNUMPPVC')));?>
							</div>
							<div class="vcm-bcah-detail vcm-bcah-container-header">
								<span>
									<i class="<?php echo (!property_exists($oldData, 'latitude')||!property_exists($oldData, 'longitude'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-position-icon" id="vcm-bcah-position"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-position"><?php echo JText::_('VCMBCAHPOSITION');?></span>
								</span>
							</div>
							<div class="vcm-bcah-position-div vcm-bcah-entry-contents" style="<?php echo (!property_exists($oldData, 'latitude')||!property_exists($oldData, 'longitude'))? "display: none;" : "";?>">
								<div class="vcm-bcah-subdetail">
									<label><?php echo JText::_('VCMBCAHLATITUDE');?></label>
									<input type="number" name="vcm-bcah-position-latitude" step="any" max="+180" min="-180" value="<?php echo isset($oldData->latitude) ? $oldData->latitude : ''; ?>"/>
								</div>
								<div class="vcm-bcah-subdetail">
									<label><?php echo JText::_('VCMBCAHLONGITUDE');?></label>
									<input type="number" name="vcm-bcah-position-longitude" step="any" max="+180" min="-180" value="<?php echo isset($oldData->longitude) ? $oldData->longitude : ''; ?>"/>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-invoices-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'invoices'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-invoices-icon" id="vcm-bcah-invoices"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-invoices"><?php echo JText::_('VCMBCAHCINFOTYPE4');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-invoices-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'invoices'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-invoices-people-container">
							<div class="vcm-bcah-invoices-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-invoices">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-invoices-icon" id="vcm-bcah-person-invoices"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-invoices"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-invoices-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-invoices-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset ($oldData->invoices->person->firstName) ? $oldData->invoices->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-invoices-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset ($oldData->invoices->person->surname) ? $oldData->invoices->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-invoices-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male" <?php echo (isset($oldData->invoices->person->gender) && $oldData->invoices->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->invoices->person->gender) && $oldData->invoices->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->invoices->person->gender) && $oldData->invoices->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-invoices-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->invoices->person->jobTitle) && $oldData->invoices->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-invoices-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->invoices->address->language) && $oldData->invoices->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-invoices-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-invoices">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-invoices-icon" id="vcm-bcah-address-invoices"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-invoices"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-invoices-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-invoices-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->invoices->address->language) && $oldData->invoices->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-invoices-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->invoices->address->country) && $oldData->invoices->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-invoices-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->invoices->address->cityName) ? $oldData->invoices->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-invoices-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->invoices->address->addressLine) ? $oldData->invoices->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-invoices-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->invoices->address->postalCode) ? $oldData->invoices->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-invoices">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'invoicesEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "invoicesEmailsIndexes")&&max($oldData->invoicesEmailsIndexes)!=0){
										foreach ($oldData->invoicesEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-invoices-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-invoices-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->invoices->emails->$index->email) ? $oldData->invoices->emails->$index->email : '')."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-invoices">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'invoicesPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "invoicesPhonesIndexes")&&max($oldData->invoicesPhonesIndexes)!=0){
										foreach ($oldData->invoicesPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-invoices-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-invoices".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-invoices-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->invoices->phones->$index->phoneNumber) ? $oldData->invoices->phones->$index->phoneNumber : '')."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-invoices-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".(isset($oldData->invoices->phones->$index->phoneTechType) && $oldData->invoices->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".(isset($oldData->invoices->phones->$index->phoneTechType) && $oldData->invoices->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".(isset($oldData->invoices->phones->$index->phoneTechType) && $oldData->invoices->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-invoices-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->invoices->phones->$index->extension) ? $oldData->invoices->phones->$index->extension : '')."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-general-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'general'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-general-icon" id="vcm-bcah-general"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-general"><?php echo JText::_('VCMBCAHCINFOTYPE1');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-general-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'general'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-general-people-container">
							<div class="vcm-bcah-general-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-general">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-general-icon" id="vcm-bcah-person-general"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-general"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-general-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-general-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset($oldData->general->person->firstName) ? $oldData->general->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-general-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset($oldData->general->person->surname) ? $oldData->general->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-general-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male"<?php echo (isset($oldData->general->person->gender) && $oldData->general->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->general->person->gender) && $oldData->general->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->general->person->gender) && $oldData->general->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-general-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->general->person->jobTitle) && $oldData->general->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-general-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->general->address->language) && $oldData->general->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-general-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-general">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-general-icon" id="vcm-bcah-address-general"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-general"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-general-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-general-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->general->address->language) && $oldData->general->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-general-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->general->address->country) && $oldData->general->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-general-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->general->address->cityName) ? $oldData->general->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-general-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->general->address->addressLine) ? $oldData->general->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-general-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->general->address->postalCode) ? $oldData->general->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-general">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'generalEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "generalEmailsIndexes")&&max($oldData->generalEmailsIndexes)!=0){
										foreach ($oldData->generalEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-general-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-general-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->general->emails->$index->email) && $oldData->general->emails->$index->email)."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-general">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'generalPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "generalPhonesIndexes")&&max($oldData->generalPhonesIndexes)!=0){
										foreach ($oldData->generalPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-general-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-general".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-general-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->general->phones->$index->phoneNumber) && $oldData->general->phones->$index->phoneNumber)."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-general-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".(isset($oldData->general->phones->$index->phoneTechType) && $oldData->general->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".(isset($oldData->general->phones->$index->phoneTechType) && $oldData->general->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".(isset($oldData->general->phones->$index->phoneTechType) && $oldData->general->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-general-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->general->phones->$index->extension) && $oldData->general->phones->$index->extension)."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-contract-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'contract'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-contract-icon" id="vcm-bcah-contract"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-contract"><?php echo JText::_('VCMBCAHCINFOTYPE2');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-contract-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'contract'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-contract-people-container">
							<div class="vcm-bcah-contract-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-contract">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-contract-icon" id="vcm-bcah-person-contract"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-contract"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-contract-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-contract-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset($oldData->contract->person->firstName) ? $oldData->contract->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-contract-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset($oldData->contract->person->surname) ? $oldData->contract->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-contract-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male"<?php echo (isset($oldData->contract->person->gender) && $oldData->contract->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->contract->person->gender) && $oldData->contract->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->contract->person->gender) && $oldData->contract->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-contract-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->contract->person->jobTitle) && $oldData->contract->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div-contract">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-contract-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->contract->address->language) && $oldData->contract->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-contract-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-contract">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-contract-icon" id="vcm-bcah-address-contract"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-contract"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-contract-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div-contract">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-contract-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->contract->address->language) && $oldData->contract->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-contract-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->contract->address->country) && $oldData->contract->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-contract-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->contract->address->cityName) ? $oldData->contract->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-contract-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->contract->address->addressLine) ? $oldData->contract->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-contract-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->contract->address->postalCode) ? $oldData->contract->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-contract">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'contractEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "contractEmailsIndexes")&&max($oldData->contractEmailsIndexes)!=0){
										foreach ($oldData->contractEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-contract-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-contract-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->contract->emails->$index->email) ? $oldData->contract->emails->$index->email : '')."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-contract">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'contractPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "contractPhonesIndexes")&&max($oldData->contractPhonesIndexes)!=0){
										foreach ($oldData->contractPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-contract-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-contract".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-contract-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->contract->phones->$index->phoneNumber) ? $oldData->contract->phones->$index->phoneNumber : '')."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-contract-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".(isset($oldData->contract->phones->$index->phoneTechType) && $oldData->contract->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".(isset($oldData->contract->phones->$index->phoneTechType) && $oldData->contract->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".(isset($oldData->contract->phones->$index->phoneTechType) && $oldData->contract->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-contract-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->contract->phones->$index->extension) ? $oldData->contract->phones->$index->extension : '')."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-reservations-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'reservations'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-reservations-icon" id="vcm-bcah-reservations"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-reservations"><?php echo JText::_('VCMBCAHCINFOTYPE3');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-reservations-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'reservations'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-reservations-people-container">
							<div class="vcm-bcah-reservations-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-reservations">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-reservations-icon" id="vcm-bcah-person-reservations"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-reservations"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-reservations-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-reservations-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset($oldData->reservations->person->firstName) ? $oldData->reservations->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-reservations-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset($oldData->reservations->person->surname) ? $oldData->reservations->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-reservations-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male"<?php echo (isset($oldData->reservations->person->gender) && $oldData->reservations->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->reservations->person->gender) && $oldData->reservations->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->reservations->person->gender) && $oldData->reservations->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-reservations-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->reservations->person->jobTitle) && $oldData->reservations->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-reservations-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->reservations->address->language) && $oldData->reservations->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-reservations-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-reservations">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-reservations-icon" id="vcm-bcah-address-reservations"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-reservations"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-reservations-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-reservations-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->reservations->address->language) && $oldData->reservations->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-reservations-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".(isset($oldData->reservations->address->country) && $oldData->reservations->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-reservations-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->reservations->address->cityName) ? $oldData->reservations->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-reservations-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->reservations->address->addressLine) ? $oldData->reservations->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-reservations-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->reservations->address->postalCode) ? $oldData->reservations->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-reservations">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'reservationsEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "reservationsEmailsIndexes")&&max($oldData->reservationsEmailsIndexes)!=0){
										foreach ($oldData->reservationsEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-reservations-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-reservations-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->reservations->emails->$index->email) ? $oldData->reservations->emails->$index->email : '')."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-reservations">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'reservationsPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "reservationsPhonesIndexes")&&max($oldData->reservationsPhonesIndexes)!=0){
										foreach ($oldData->reservationsPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-reservations-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-reservations".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-reservations-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->reservations->phones->$index->phoneNumber) ? $oldData->reservations->phones->$index->phoneNumber : '')."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-reservations-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".(isset($oldData->reservations->phones->$index->phoneTechType) && $oldData->reservations->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".(isset($oldData->reservations->phones->$index->phoneTechType) && $oldData->reservations->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".(isset($oldData->reservations->phones->$index->phoneTechType) && $oldData->reservations->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-reservations-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->reservations->phones->$index->extension) ? $oldData->reservations->phones->$index->extension : '')."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-availability-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'availability'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-availability-icon" id="vcm-bcah-availability"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-availability"><?php echo JText::_('VCMBCAHCINFOTYPE5');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-availability-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'availability'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-availability-people-container">
							<div class="vcm-bcah-availability-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-availability">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-availability-icon" id="vcm-bcah-person-availability"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-availability"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-availability-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-availability-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset($oldData->availability->person->firstName) ? $oldData->availability->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-availability-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset($oldData->availability->person->surname) ? $oldData->availability->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-availability-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male"<?php echo (isset($oldData->availability->person->gender) && $oldData->availability->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->availability->person->gender) && $oldData->availability->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->availability->person->gender) && $oldData->availability->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-availability-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->availability->person->jobTitle) && $oldData->availability->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-availability-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->availability->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-availability-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-availability">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-availability-icon" id="vcm-bcah-address-availability"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-availability"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-availability-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-availability-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->availability->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-availability-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->availability->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-availability-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->availability->address->cityName) ? $oldData->availability->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-availability-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->availability->address->addressLine) ? $oldData->availability->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-availability-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->availability->address->postalCode) ? $oldData->availability->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-availability">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'availabilityEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "availabilityEmailsIndexes")&&max($oldData->availabilityEmailsIndexes)!=0){
										foreach ($oldData->availabilityEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-availability-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-availability-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->availability->emails->$index->email) ? $oldData->availability->emails->$index->email : '')."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-availability">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'availabilityPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "availabilityPhonesIndexes")&&max($oldData->availabilityPhonesIndexes)!=0){
										foreach ($oldData->availabilityPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-availability-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-availability".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-availability-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->availability->phones->$index->phoneNumber) ? $oldData->availability->phones->$index->phoneNumber : '')."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-availability-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".($oldData->availability->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".($oldData->availability->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".($oldData->availability->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-availability-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->availability->phones->$index->extension) ? $oldData->availability->phones->$index->extension : '')."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-site_content-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'site_content'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-site_content-icon" id="vcm-bcah-site_content"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-site_content"><?php echo JText::_('VCMBCAHCINFOTYPE6');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-site_content-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'site_content'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-site_content-people-container">
							<div class="vcm-bcah-site_content-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-site_content">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-site_content-icon" id="vcm-bcah-person-site_content"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-site_content"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-site_content-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-site_content-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset($oldData->site_content->person->firstName) ? $oldData->site_content->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-site_content-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset($oldData->site_content->person->surname) ? $oldData->site_content->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-site_content-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male"<?php echo (isset($oldData->site_content->person->gender) && $oldData->site_content->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->site_content->person->gender) && $oldData->site_content->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->site_content->person->gender) && $oldData->site_content->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-site_content-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->site_content->person->jobTitle) && $oldData->site_content->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-site_content-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->site_content->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-site_content-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-site_content">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-site_content-icon" id="vcm-bcah-address-site_content"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-site_content"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-site_content-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-site_content-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->site_content->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-site_content-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->site_content->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-site_content-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->site_content->address->cityName) ? $oldData->site_content->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-site_content-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->site_content->address->addressLine) ? $oldData->site_content->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-site_content-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->site_content->address->postalCode) ? $oldData->site_content->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-site_content">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'site_contentEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "site_contentEmailsIndexes")&&max($oldData->site_contentEmailsIndexes)!=0){
										foreach ($oldData->site_contentEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-site_content-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-site_content-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->site_content->emails->$index->email) ? $oldData->site_content->emails->$index->email : '')."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-site_content">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'site_contentPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "site_contentPhonesIndexes")&&max($oldData->site_contentPhonesIndexes)!=0){
										foreach ($oldData->site_contentPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-site_content-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-site_content".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-site_content-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->site_content->phones->$index->phoneNumber) ? $oldData->site_content->phones->$index->phoneNumber : '')."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-site_content-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".($oldData->site_content->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".($oldData->site_content->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".($oldData->site_content->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-site_content-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->site_content->phones->$index->extension) ? $oldData->site_content->phones->$index->extension : '')."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-parity-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'parity'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-parity-icon" id="vcm-bcah-parity"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-parity"><?php echo JText::_('VCMBCAHCINFOTYPE7');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-parity-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'parity'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-parity-people-container">
							<div class="vcm-bcah-parity-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-parity">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-parity-icon" id="vcm-bcah-person-parity"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-parity"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-parity-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-parity-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset($oldData->parity->person->firstName) ? $oldData->parity->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-parity-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset($oldData->parity->person->surname) ? $oldData->parity->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-parity-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male"<?php echo (isset($oldData->parity->person->gender) && $oldData->parity->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->parity->person->gender) && $oldData->parity->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->parity->person->gender) && $oldData->parity->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-parity-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->parity->person->jobTitle) && $oldData->parity->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-parity-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->parity->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-parity-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-parity">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-parity-icon" id="vcm-bcah-address-parity"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-parity"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-parity-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-parity-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->parity->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-parity-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->parity->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-parity-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->parity->address->cityName) ? $oldData->parity->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-parity-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->parity->address->addressLine) ? $oldData->parity->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-parity-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->parity->address->postalCode) ? $oldData->parity->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-parity">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'parityEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "parityEmailsIndexes")&&max($oldData->parityEmailsIndexes)!=0){
										foreach ($oldData->parityEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-parity-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-parity-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->parity->emails->$index->email) ? $oldData->parity->emails->$index->email : '')."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-parity">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'parityPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "parityPhonesIndexes")&&max($oldData->parityPhonesIndexes)!=0){
										foreach ($oldData->parityPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-parity-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-parity".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-parity-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->parity->phones->$index->phoneNumber) ? $oldData->parity->phones->$index->phoneNumber : '')."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-parity-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".($oldData->parity->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".($oldData->parity->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".($oldData->parity->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-parity-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->parity->phones->$index->extension) ? $oldData->parity->phones->$index->extension : '')."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-requests-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'requests'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-requests-icon" id="vcm-bcah-requests"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-requests"><?php echo JText::_('VCMBCAHCINFOTYPE8');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-requests-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'requests'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-requests-people-container">
							<div class="vcm-bcah-requests-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-requests">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-requests-icon" id="vcm-bcah-person-requests"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-requests"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-requests-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-requests-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset($oldData->requests->person->firstName) ? $oldData->requests->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-requests-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset($oldData->requests->person->surname) ? $oldData->requests->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-requests-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male"<?php echo (isset($oldData->requests->person->gender) && $oldData->requests->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->requests->person->gender) && $oldData->requests->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->requests->person->gender) && $oldData->requests->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-requests-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->requests->person->jobTitle) && $oldData->requests->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-requests-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->requests->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-requests-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-requests">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-requests-icon" id="vcm-bcah-address-requests"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-requests"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-requests-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-requests-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->requests->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-requests-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->requests->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-requests-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->requests->address->cityName) ? $oldData->requests->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-requests-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->requests->address->addressLine) ? $oldData->requests->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-requests-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->requests->address->postalCode) ? $oldData->requests->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-requests">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'requestsEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "requestsEmailsIndexes")&&max($oldData->requestsEmailsIndexes)!=0){
										foreach ($oldData->requestsEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-requests-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-requests-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->requests->emails->$index->email) ? $oldData->requests->emails->$index->email : '')."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-requests">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'requestsPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "requestsPhonesIndexes")&&max($oldData->requestsPhonesIndexes)!=0){
										foreach ($oldData->requestsPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-requests-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-requests".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-requests-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->requests->phones->$index->phoneNumber) ? $oldData->requests->phones->$index->phoneNumber : '')."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-requests-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".($oldData->requests->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".($oldData->requests->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".($oldData->requests->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-requests-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->requests->phones->$index->extension) ? $oldData->requests->phones->$index->extension : '')."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-central_reservations-container">
					<div class="vcm-bcah-entry-header">
						<h4><span>
							<i class="<?php echo (!property_exists($oldData, 'central_reservations'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-central_reservations-icon" id="vcm-bcah-central_reservations"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-central_reservations"><?php echo JText::_('VCMBCAHCINFOTYPE9');?></span>
						</h4></span>
					</div>
					<div class="vcm-bcah-central_reservations-div vcm-bcah-contact-info-container" style="<?php echo (!property_exists($oldData, 'central_reservations'))? "display:none;" : "";?>">
						<div class="vcm-bcah-copy-links-container">
							<div class="vcm-bcah-copy-links"></div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-central_reservations-people-container">
							<div class="vcm-bcah-central_reservations-people-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-person-central_reservations">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-person-central_reservations-icon" id="vcm-bcah-person-central_reservations"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-person-central_reservations"><?php echo JText::_('VCMBCAHPERSON');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-person-central_reservations-div">
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHFIRSTNAME');?></label>
												<input type="text" name="vcm-bcah-person-central_reservations-first-name" class="vcm-bcah-first-name-input vcm-bcah-saved-value" id="vcm-bcah-first-name" value="<?php echo isset($oldData->central_reservations->person->firstName) ? $oldData->central_reservations->person->firstName : '';?>" />
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHSURNAME');?></label>
												<input type="text" name="vcm-bcah-person-central_reservations-surname" class="vcm-bcah-surname-input vcm-bcah-saved-value" id="vcm-bcah-surname" value="<?php echo isset($oldData->central_reservations->person->surname) ? $oldData->central_reservations->person->surname : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHGENDER');?></label>
												<select name="vcm-bcah-person-central_reservations-gender" class="vcm-bcah-saved-value" id="vcm-bcah-gender">
													<option value=""></option>
													<option value="Male"<?php echo (isset($oldData->central_reservations->person->gender) && $oldData->central_reservations->person->gender=="Male"? "selected":"");?>><?php echo JText::_('VCMBCAHMALE');?></option>
													<option value="Female" <?php echo (isset($oldData->central_reservations->person->gender) && $oldData->central_reservations->person->gender=="Female"? "selected":"");?>><?php echo JText::_('VCMBCAHFEMALE');?></option>
													<option value="unknown" <?php echo (isset($oldData->central_reservations->person->gender) && $oldData->central_reservations->person->gender=="unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHOTHER');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHJOBTITLE');?></label>
												<select name="vcm-bcah-person-central_reservations-job-title" class="vcm-bcah-saved-value" id="vcm-bcah-job-title">
													<option value=""></option>
													<option value="Administration Employee" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Administration Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB1');?></option>
													<option value="Director of Business Development" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Director of Business Development"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB2');?></option>
													<option value="E-Commerce Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="E-Commerce Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB3');?></option>
													<option value="Finance Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Finance Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB4');?></option>
													<option value="Front Office Employee" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Front Office Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB5');?></option>
													<option value="Front Office Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Front Office Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB6');?></option>
													<option value="General Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="General Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB7');?></option>
													<option value="Marketing Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB8');?></option>
													<option value="Owner" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Owner"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB9');?></option>
													<option value="Reservations Employee" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Reservations Employee"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB10');?></option>
													<option value="Reservations Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Reservations Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB11');?></option>
													<option value="Revenue Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Revenue Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB12');?></option>
													<option value="Rooms Division Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Rooms Division Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB13');?></option>
													<option value="Sales & Marketing Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Sales & Marketing Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB14');?></option>
													<option value="Sales Executive" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Sales Executive"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB15');?></option>
													<option value="Sales Manager" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Sales Manager"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB16');?></option>
													<option value="Unknown" <?php echo (isset($oldData->central_reservations->person->jobTitle) && $oldData->central_reservations->person->jobTitle=="Unknown"? "selected":"");?>><?php echo JText::_('VCMBCAHJOB17');?></option>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-person-central_reservations-language" class="vcm-bcah-saved-value" id="vcm-bcah-person-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->central_reservations->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-addresses-container">
							<div class="vcm-bcah-addresses-central_reservations-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<div class="vcm-bcah-entry-instance vcm-bcah-address-central_reservations">
										<div class="vcm-bcah-entry-header vcm-bcah-entry-instance-container">
											<span>
												<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-address-central_reservations-icon" id="vcm-bcah-address-central_reservations"></i>
												<span class="vcm-bcah-hide-button" id="vcm-bcah-address-central_reservations"><?php echo JText::_('VCMBCAHADDRESS');?></span>
											</span>
										</div>
										<div class="vcm-bcah-entry-contents vcm-bcah-address-central_reservations-div">
											<div class="vcm-bcah-entry-detail vcm-bcah-language-div">
												<label><?php echo JText::_('VCMBCAHLANGUAGE');?></label>
												<select name="vcm-bcah-address-central_reservations-language" class="vcm-bcah-saved-value" id="vcm-bcah-address-language">
													<option value=""></option>
													<?php foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->central_reservations->address->language==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCOUNTRY');?></label>
												<select name="vcm-bcah-address-central_reservations-country" class="vcm-bcah-saved-value" id="vcm-bcah-country">
													<option value=""></option>
													<?php foreach ($countryCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->central_reservations->address->country==$value? "selected":"").">".$key."</option>";
													} ?>
												</select>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHCITYNAME');?></label>
												<input type="text" name="vcm-bcah-address-central_reservations-city-name" class="vcm-bcah-saved-value" id="vcm-bcah-city-name" value="<?php echo isset($oldData->central_reservations->address->cityName) ? $oldData->central_reservations->address->cityName : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHADDLINE');?></label>
												<input type="text" name="vcm-bcah-address-central_reservations-address-line" class="vcm-bcah-saved-value" id="vcm-bcah-address-line" value="<?php echo isset($oldData->central_reservations->address->addressLine) ? $oldData->central_reservations->address->addressLine : '';?>"/>
											</div>
											<div class="vcm-bcah-entry-detail">
												<label><?php echo JText::_('VCMBCAHPOSCODE');?></label>
												<input type="text" name="vcm-bcah-address-central_reservations-postal-code" class="vcm-bcah-saved-value" id="vcm-bcah-postal-code" value="<?php echo isset($oldData->central_reservations->address->postalCode) ? $oldData->central_reservations->address->postalCode : '';?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-emails-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-central_reservations">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-emails-icon" id="vcm-bcah-emails" style="<?php echo (!property_exists($oldData, 'central_reservationsEmailsIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-emails"><?php echo JText::_('VCMBCAHEMAILS');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-email"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-emails-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "central_reservationsEmailsIndexes")&&max($oldData->central_reservationsEmailsIndexes)!=0){
										foreach ($oldData->central_reservationsEmailsIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-email".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-central_reservations-email-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-email-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-contents\">
													<span>".JText::_('VCMBCAHEMAIL')."</span>
													<input type=\"text\" name=\"vcm-bcah-central_reservations-email".$index."-email-address\" class=\"vcm-bcah-email-input\" value=\"".(isset($oldData->central_reservations->emails->$index->email) ? $oldData->central_reservations->emails->$index->email : '')."\"/>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-email".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
												</div>
											</div>";
										}
									}
									?>
								</div>
							</div>
						</div>
						<div class="vcm-bcah-category-container" id="vcm-bcah-phones-container">
							<div class="vcm-bcah-container-header vcm-bcah-entry-instance-container vcm-bcah-central_reservations">
								<span>
									<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phones-icon" id="vcm-bcah-phones" style="<?php echo (!property_exists($oldData, 'central_reservationsPhonesIndexes'))? "display:none;" : "";?>"></i>
									<span class="vcm-bcah-hide-button" id="vcm-bcah-phones"><?php echo JText::_('VCMBCAHPHONES');?></span>
								</span>
								<button type="button" class="btn vcm-bcah-new-button vcm-bcah-phone"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
							</div>
							<div class="vcm-bcah-phones-div vcm-bcah-entry-container">
								<div class="vcm-bcah-entry-instance-container">
									<?php
									if(property_exists($oldData, "central_reservationsPhonesIndexes")&&max($oldData->central_reservationsPhonesIndexes)!=0){
										foreach ($oldData->central_reservationsPhonesIndexes as $key => $index) {
											echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-phone".$index."\">
												<input type=\"hidden\" name=\"vcm-bcah-central_reservations-phone-index[]\" class=\"vcm-bcah-saved-value vcm-bcah-phone-index\" value=\"".$index."\"/>
												<div class=\"vcm-bcah-entry-header\">
													<span>
														<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-phone".$index."-icon\" id=\"vcm-bcah-phone".$index."\"></i>
														<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-phone-central_reservations".$index."\">".JText::_('VCMBCAHPHONE')."</span>
													</span>
												</div>
												<div class=\"vcm-bcah-entry-contents vcm-bcah-phone".$index."-div\">
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONENUMB')."</label>
														<input type=\"text\" name=\"vcm-bcah-central_reservations-phone".$index."-phone-number\" class=\"vcm-bcah-phone-number\" value=\"".(isset($oldData->central_reservations->phones->$index->phoneNumber) ? $oldData->central_reservations->phones->$index->phoneNumber : '')."\"/>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONETYPE')."</label>
														<select name=\"vcm-bcah-central_reservations-phone".$index."-phone-tech-type\" class=\"vcm-bcah-phone-tech-type-selector\">
															<option value=\"\"> </option>
															<option value=\"1\" ".($oldData->central_reservations->phones->$index->phoneTechType=="1"? "selected":"").">".JText::_('VCMBCAHPHONETYPE1')."</option>
															<option value=\"3\" ".($oldData->central_reservations->phones->$index->phoneTechType=="3"? "selected":"").">".JText::_('VCMBCAHPHONETYPE2')."</option>
															<option value=\"5\" ".($oldData->central_reservations->phones->$index->phoneTechType=="5"? "selected":"").">".JText::_('VCMBCAHPHONETYPE3')."</option>
														</select>
													</div>
													<div class=\"vcm-bcah-entry-detail\">
														<label>".JText::_('VCMBCAHPHONEEXT')."</label>
														<input type=\"number\" min=\"0\" step=\"1\" name=\"vcm-bcah-central_reservations-phone".$index."-extension\" class=\"vcm-bcah-phone-extension\" value=\"".(isset($oldData->central_reservations->phones->$index->extension) ? $oldData->central_reservations->phones->$index->extension : '')."\"/>
													</div>
													<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-phone".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
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
				<div class="vcm-bcah-bottom-button">
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
			</form>
		</div>
		<div id="2" class="vcm-bcah-container-content" style="<?php if(VikRequest::getString('tab')!="hotel-info"){echo "display: none;";}?>">
			<form name="vcm-bcah-hotel-info-form" id="vcm-bcah-hotel-info-form" method="POST" action="index.php?option=com_vikchannelmanager&task=bca.makeHotelXml">
				<!--<input type="hidden" name="progID" value="<?php echo $progID;?>"/>-->
				<input type="hidden" name="accountName" value="<?php echo $hotelName;?>"/>
				<div class="vcm-bcah-tab-title">
					<span class="vcm-bcah-content-title"><?php echo JText::_('VCMBCAHHINFO');?></span>
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
				<input type="hidden" name="submittedform" value="hotel-info"/>
				<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
				<div class="vcm-bcah-detail">
					<label><?php echo JText::_('VCMBCAHGROOMQ');?></label>
					<input type="number" name="vcm-bcah-guest-room-quantity" value="<?php echo isset($oldData->guestRoomQuantity) ? $oldData->guestRoomQuantity : ''; ?>" min="0"/>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-hotelcategory-container">
					<div class="vcm-bcah-category-div">
						<label><?php echo JText::_('VCMBCAHHOTTYPE');?></label>
						<select name="vcm-bcah-hotel-type">
							<option value=""></option>
							<option value="3" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='3'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE1');?></option>
							<option value="4" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='4'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE2');?></option>
							<option value="5" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='5'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE3');?></option>
							<option value="6" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='6'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE4');?></option>
							<option value="7" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='7'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE5');?></option>
							<option value="8" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='8'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE6');?></option>
							<option value="12" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='12'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE7');?></option>
							<option value="14" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='14'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE8');?></option>
							<option value="15" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='15'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE9');?></option>
							<option value="16" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='16'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE10');?></option>
							<option value="18" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='18'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE11');?></option>
							<option value="19" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='19'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE12');?></option>
							<option value="20" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='20'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE13');?></option>
							<option value="21" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='21'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE14');?></option>
							<option value="22" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='22'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE15');?></option>
							<option value="23" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='23'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE16');?></option>
							<option value="25" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='25'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE17');?></option>
							<option value="26" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='26'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE18');?></option>
							<option value="27" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='27'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE19');?></option>
							<option value="28" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='28'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE20');?></option>
							<option value="29" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='29'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE21');?></option>
							<option value="30" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='30'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE22');?></option>
							<option value="31" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='31'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE23');?></option>
							<option value="32" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='32'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE24');?></option>
							<option value="33" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='33'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE25');?></option>
							<option value="34" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='34'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE26');?></option>
							<option value="35" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='35'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE27');?></option>
							<option value="36" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='36'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE28');?></option>
							<option value="37" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='37'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE29');?></option>
							<option value="40" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='40'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE30');?></option>
							<option value="44" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='44'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE31');?></option>
							<option value="45" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='45'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE32');?></option>
							<option value="46" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='46'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE33');?></option>
							<option value="50" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='50'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE34');?></option>
							<option value="51" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='51'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE35');?></option>
							<option value="52" <?php echo (isset($oldData->hotelType) && $oldData->hotelType=='52'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHHOTTYPE36');?></option>
						</select>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-languages-container">
					<div class="vcm-bcah-container-header">
						<span>
							<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-languages-icon" id="vcm-bcah-languages" style="<?php echo (!property_exists($oldData, 'languages'))? "display: none;" : "";?>"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-languages"><?php echo JText::_('VCMBCAHLANGUAGES');?></span>
						</span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-language"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-languages-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'languages'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
								if(property_exists($oldData, 'languages')){
									foreach ($oldData->languagesIndexes as $key => $index) { 
										echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-language".$index."\">
											<div class=\"vcm-bcah-entry-contents\">
												<input type=\"hidden\" name=\"vcm-bcah-language-index[]\" value=\"".$index."\"/>
												<label>".JText::_('VCMBCAHLANGUAGE')."</label>
												<select name=\"vcm-bcah-language".$index."-selected-language\">";
													foreach ($languageCodes as $key => $value) {
														echo "<option value=\"".$value."\" ".($oldData->languages->$index->language==$value? "selected":"").">".$key."</option>";
													}
												echo "</select>
												<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-language".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
											</div>
										</div>";
									}
								}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-services-container">
					<div class="vcm-bcah-container-header">
						<span>
							<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-services-icon" id="vcm-bcah-services" style="<?php echo (!property_exists($oldData, 'services'))? "display: none;" : "";?>"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-services"><?php echo JText::_('VCMBCAHSERVICES');?></span>
						</span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-service"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-services-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'services'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php 
							if(property_exists($oldData, 'services')){
								foreach ($oldData->servicesIndexes as $key => $index) { 
									echo "<div class=\"vcm-bcah-entry-instance-invisible-content vcm-bcah-entry-instance vcm-bcah-service".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-service-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-entry-header\">
											<span>
												<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-service".$index."-icon\" id=\"vcm-bcah-service".$index."\"></i>
												<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-service".$index."\">".JText::_('VCMBCAHSERVICE')."</span>
											</span>
										</div>
										<div class=\"vcm-bcah-entry-contents vcm-bcah-service".$index."-div\">
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHSERVTYPE')."</label>
												<select name=\"vcm-bcah-service".$index."-selected-service\" class=\"vcm-bcah-service-select\">
													<option value=\"1\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="1"? "selected":"").">".JText::_('VCMBCAHSERV1')."</option>
													<option value=\"5\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="5"? "selected":"").">".JText::_('VCMBCAHSERV2')."</option>
													<option value=\"7\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="7"? "selected":"").">".JText::_('VCMBCAHSERV3')."</option>
													<option value=\"8\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="8"? "selected":"").">".JText::_('VCMBCAHSERV4')."</option>
													<option value=\"9\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="9"? "selected":"").">".JText::_('VCMBCAHSERV5')."</option>
													<option value=\"14\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="14"? "selected":"").">".JText::_('VCMBCAHSERV6')."</option>
													<option value=\"15\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="15"? "selected":"").">".JText::_('VCMBCAHSERV7')."</option>
													<option value=\"16\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="16"? "selected":"").">".JText::_('VCMBCAHSERV8')."</option>
													<option value=\"22\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="22"? "selected":"").">".JText::_('VCMBCAHSERV9')."</option>
													<option value=\"26\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="26"? "selected":"").">".JText::_('VCMBCAHSERV10')."</option>
													<option value=\"33\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="33"? "selected":"").">".JText::_('VCMBCAHSERV11')."</option>
													<option value=\"35\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="35"? "selected":"").">".JText::_('VCMBCAHSERV12')."</option>
													<option value=\"41\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="41"? "selected":"").">".JText::_('VCMBCAHSERV13')."</option>
													<option value=\"44\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="44"? "selected":"").">".JText::_('VCMBCAHSERV14')."</option>
													<option value=\"45\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="45"? "selected":"").">".JText::_('VCMBCAHSERV15')."</option>
													<option value=\"49\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="49"? "selected":"").">".JText::_('VCMBCAHSERV16')."</option>
													<option value=\"54\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="54"? "selected":"").">".JText::_('VCMBCAHSERV17')."</option>
													<option value=\"60\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="60"? "selected":"").">".JText::_('VCMBCAHSERV18')."</option>
													<option value=\"61\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="61"? "selected":"").">".JText::_('VCMBCAHSERV19')."</option>
													<option value=\"62\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="62"? "selected":"").">".JText::_('VCMBCAHSERV20')."</option>
													<option value=\"76\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="76"? "selected":"").">".JText::_('VCMBCAHSERV21')."</option>
													<option value=\"77\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="77"? "selected":"").">".JText::_('VCMBCAHSERV22')."</option>
													<option value=\"78\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="78"? "selected":"").">".JText::_('VCMBCAHSERV23')."</option>
													<option value=\"79\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="79"? "selected":"").">".JText::_('VCMBCAHSERV24')."</option>
													<option value=\"81\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="81"? "selected":"").">".JText::_('VCMBCAHSERV25')."</option>
													<option value=\"83\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="83"? "selected":"").">".JText::_('VCMBCAHSERV26')."</option>
													<option value=\"86\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="86"? "selected":"").">".JText::_('VCMBCAHSERV27')."</option>
													<option value=\"91\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="91"? "selected":"").">".JText::_('VCMBCAHSERV28')."</option>
													<option value=\"96\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="96"? "selected":"").">".JText::_('VCMBCAHSERV29')."</option>
													<option value=\"97\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="97"? "selected":"").">".JText::_('VCMBCAHSERV30')."</option>
													<option value=\"98\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="98"? "selected":"").">".JText::_('VCMBCAHSERV31')."</option>
													<option value=\"122\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="122"? "selected":"").">".JText::_('VCMBCAHSERV32')."</option>
													<option value=\"159\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="159"? "selected":"").">".JText::_('VCMBCAHSERV33')."</option>
													<option value=\"165\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="165"? "selected":"").">".JText::_('VCMBCAHSERV34')."</option>
													<option value=\"168\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="168"? "selected":"").">".JText::_('VCMBCAHSERV35')."</option>
													<option value=\"173\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="173"? "selected":"").">".JText::_('VCMBCAHSERV36')."</option>
													<option value=\"193\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="193"? "selected":"").">".JText::_('VCMBCAHSERV37')."</option>
													<option value=\"197\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="197"? "selected":"").">".JText::_('VCMBCAHSERV38')."</option>
													<option value=\"198\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="198"? "selected":"").">".JText::_('VCMBCAHSERV39')."</option>
													<option value=\"202\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="202"? "selected":"").">".JText::_('VCMBCAHSERV40')."</option>
													<option value=\"228\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="228"? "selected":"").">".JText::_('VCMBCAHSERV41')."</option>
													<option value=\"233\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="233"? "selected":"").">".JText::_('VCMBCAHSERV42')."</option>
													<option value=\"234\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="234"? "selected":"").">".JText::_('VCMBCAHSERV43')."</option>
													<option value=\"236\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="236"? "selected":"").">".JText::_('VCMBCAHSERV44')."</option>
													<option value=\"237\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="237"? "selected":"").">".JText::_('VCMBCAHSERV45')."</option>
													<option value=\"239\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="239"? "selected":"").">".JText::_('VCMBCAHSERV46')."</option>
													<option value=\"242\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="242"? "selected":"").">".JText::_('VCMBCAHSERV47')."</option>
													<option value=\"262\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="262"? "selected":"").">".JText::_('VCMBCAHSERV48')."</option>
													<option value=\"269\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="269"? "selected":"").">".JText::_('VCMBCAHSERV49')."</option>
													<option value=\"272\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="272"? "selected":"").">".JText::_('VCMBCAHSERV50')."</option>
													<option value=\"282\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="282"? "selected":"").">".JText::_('VCMBCAHSERV51')."</option>
													<option value=\"283\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="283"? "selected":"").">".JText::_('VCMBCAHSERV52')."</option>
													<option value=\"292\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="292"? "selected":"").">".JText::_('VCMBCAHSERV53')."</option>
													<option value=\"310\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="310"? "selected":"").">".JText::_('VCMBCAHSERV54')."</option>
													<option value=\"312\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code=="312"? "selected":"").">".JText::_('VCMBCAHSERV55')."</option>
												</select>
											</div>
											<div class=\"vcm-bcah-subdetail vcm-subdetail-checkbox-detail\">
												<label>".JText::_('VCMBCAHSERVINCL')."</label>
												<input type=\"checkbox\" name=\"vcm-bcah-service".$index."-included\" ".(isset($oldData->services->$index->included) && $oldData->services->$index->included=='true'? 'checked' : '')."/>
											</div>
											<div class=\"vcm-bcah-subdetail vcm-bcah-breakfast-price\" style=\"".(isset($oldData->services->$index->code) && $oldData->services->$index->code!="173"? "display:none;":"")."\">
												<label>".JText::_('VCMBCAHBRKFPRICE')."</label>
												<input type=\"number\" name=\"vcm-bcah-service".$index."-price\" value=\"".(isset($oldData->services->$index->price) ? $oldData->services->$index->price : '')."\" min=\"0\" ".(isset($oldData->services->$index->code) && $oldData->services->$index->code!="173"? "disabled;":"")."/>
											</div>
											<div class=\"vcm-bcah-subdetail vcm-bcah-breakfast-type\" style=\"".(isset($oldData->services->$index->code) && $oldData->services->$index->code!="173"? "display:none;":"")."\">
												<label>".JText::_('VCMBCAHBRKFTYPE')."</label>
												<select name=\"vcm-bcah-service".$index."-breakfast-type[]\" multiple ".(isset($oldData->services->$index->code) && $oldData->services->$index->code!="173"? "disabled":"")."\">
													<option value=\"\"> </option>
													<option value=\"5001\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5001"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE1')."</option>
													<option value=\"5002\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5002"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE2')."</option>
													<option value=\"5003\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5003"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE3')."</option>
													<option value=\"5004\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5004"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE4')."</option>
													<option value=\"5005\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5005"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE5')."</option>
													<option value=\"5006\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5006"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE6')."</option>
													<option value=\"5007\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5007"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE7')."</option>
													<option value=\"5008\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5008"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE8')."</option>
													<option value=\"5009\" ";
													if(property_exists($oldData->services->$index, 'breakfastTypes')){
														foreach($oldData->services->$index->breakfastTypes as $breakfast){
															if($breakfast=="5009"){
																echo "selected";
															}
														}
													}
													echo ">".JText::_('VCMBCAHBRKFTYPE9')."</option>
												</select>
											</div>
											<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-service".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
										</div>
									</div>";
								}
							}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-paymentmethod-container">
					<div class="vcm-bcah-container-header">
						<span>
							<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-paymentmethod-icon" id="vcm-bcah-paymentmethods" style="<?php echo (!property_exists($oldData, 'paymentmethods'))? "display: none;" : "";?>"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-paymentmethods"><?php echo JText::_('VCMBCAHPAYMETHS');?></span>
						</span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-paymentmethod"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-paymentmethods-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'paymentmethods'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
							if(property_exists($oldData, 'paymentmethods')){
								foreach ($oldData->paymentmethodsIndexes as $key => $index) {
									echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-paymentmethod".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-paymentmethod-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-entry-contents\">
											<label>".JText::_('VCMBCAHPAYMETH')."</label>
											<select name=\"vcm-bcah-paymentmethod".$index."-selected-payment-method\">
												<option value=\"1\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="1"? "selected":"").">".JText::_('VCMBCAHPAYMETH1')."</option>
												<option value=\"2\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="2"? "selected":"").">".JText::_('VCMBCAHPAYMETH2')."</option>
												<option value=\"3\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="3"? "selected":"").">".JText::_('VCMBCAHPAYMETH3')."</option>
												<option value=\"4\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="4"? "selected":"").">".JText::_('VCMBCAHPAYMETH4')."</option>
												<option value=\"5\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="5"? "selected":"").">".JText::_('VCMBCAHPAYMETH5')."</option>
												<option value=\"7\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="7"? "selected":"").">".JText::_('VCMBCAHPAYMETH6')."</option>
												<option value=\"8\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="8"? "selected":"").">".JText::_('VCMBCAHPAYMETH7')."</option>
												<option value=\"9\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="9"? "selected":"").">".JText::_('VCMBCAHPAYMETH8')."</option>
												<option value=\"10\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="10"? "selected":"").">".JText::_('VCMBCAHPAYMETH9')."</option>
												<option value=\"11\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="11"? "selected":"").">".JText::_('VCMBCAHPAYMETH10')."</option>
												<option value=\"12\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="12"? "selected":"").">".JText::_('VCMBCAHPAYMETH11')."</option>
												<option value=\"13\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="13"? "selected":"").">".JText::_('VCMBCAHPAYMETH12')."</option>
												<option value=\"14\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="14"? "selected":"").">".JText::_('VCMBCAHPAYMETH13')."</option>
												<option value=\"15\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="15"? "selected":"").">".JText::_('VCMBCAHPAYMETH14')."</option>
												<option value=\"16\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="16"? "selected":"").">".JText::_('VCMBCAHPAYMETH15')."</option>
												<option value=\"17\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="17"? "selected":"").">".JText::_('VCMBCAHPAYMETH16')."</option>
												<option value=\"18\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="18"? "selected":"").">".JText::_('VCMBCAHPAYMETH17')."</option>
												<option value=\"19\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="19"? "selected":"").">".JText::_('VCMBCAHPAYMETH18')."</option>
												<option value=\"21\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="21"? "selected":"").">".JText::_('VCMBCAHPAYMETH19')."</option>
												<option value=\"22\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="22"? "selected":"").">".JText::_('VCMBCAHPAYMETH20')."</option>
												<option value=\"23\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="23"? "selected":"").">".JText::_('VCMBCAHPAYMETH21')."</option>
												<option value=\"25\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="25"? "selected":"").">".JText::_('VCMBCAHPAYMETH22')."</option>
												<option value=\"26\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="26"? "selected":"").">".JText::_('VCMBCAHPAYMETH23')."</option>
												<option value=\"27\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="27"? "selected":"").">".JText::_('VCMBCAHPAYMETH24')."</option>
												<option value=\"28\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="28"? "selected":"").">".JText::_('VCMBCAHPAYMETH25')."</option>
												<option value=\"29\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="29"? "selected":"").">".JText::_('VCMBCAHPAYMETH26')."</option>
												<option value=\"30\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="30"? "selected":"").">".JText::_('VCMBCAHPAYMETH27')."</option>
												<option value=\"31\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="31"? "selected":"").">".JText::_('VCMBCAHPAYMETH28')."</option>
												<option value=\"32\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="32"? "selected":"").">".JText::_('VCMBCAHPAYMETH29')."</option>
												<option value=\"34\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="34"? "selected":"").">".JText::_('VCMBCAHPAYMETH30')."</option>
												<option value=\"35\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="35"? "selected":"").">".JText::_('VCMBCAHPAYMETH31')."</option>
												<option value=\"36\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="36"? "selected":"").">".JText::_('VCMBCAHPAYMETH32')."</option>
												<option value=\"37\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="37"? "selected":"").">".JText::_('VCMBCAHPAYMETH33')."</option>
												<option value=\"38\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="38"? "selected":"").">".JText::_('VCMBCAHPAYMETH34')."</option>
												<option value=\"39\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="39"? "selected":"").">".JText::_('VCMBCAHPAYMETH35')."</option>
												<option value=\"40\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="40"? "selected":"").">".JText::_('VCMBCAHPAYMETH36')."</option>
												<option value=\"41\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="41"? "selected":"").">".JText::_('VCMBCAHPAYMETH37')."</option>
												<option value=\"42\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="42"? "selected":"").">".JText::_('VCMBCAHPAYMETH38')."</option>
												<option value=\"43\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="43"? "selected":"").">".JText::_('VCMBCAHPAYMETH39')."</option>
												<option value=\"44\" ".(isset($oldData->paymentmethods->$index->paymentMethod) && $oldData->paymentmethods->$index->paymentMethod=="44"? "selected":"").">".JText::_('VCMBCAHPAYMETH40')."</option>
											</select>
											<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-paymentmethod".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
										</div>
									</div>";
								}
							}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-detail vcm-bcah-textarea-details">
					<span><?php echo JText::_('VCMBCAHHINFODET');?></span>
					<textarea name="vcm-bcah-hotel-info-details"><?php echo isset($oldData->hotelInfoMessage) && $oldData->hotelInfoMessage;?></textarea>
				</div>
				<div class="vcm-bcah-bottom-button">
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
			</form>
		</div>
		<div id="3" class="vcm-bcah-container-content" style="<?php if(VikRequest::getString('tab')!="facility-info"){echo "display: none;";}?>">
			<form name="vcm-bcah-facility-info-form" id="vcm-bcah-facility-info-form" method="POST" action="index.php?option=com_vikchannelmanager&task=bca.makeHotelXml">
				<!--<input type="hidden" name="progID" value="<?php echo $progID;?>"/>-->
				<input type="hidden" name="accountName" value="<?php echo $hotelName;?>"/>
				<div class="vcm-bcah-tab-title">
					<span class="vcm-bcah-content-title"><?php echo JText::_('VCMBCAHFINFO');?></span>
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
				<input type="hidden" name="submittedform" value="facility-info"/>
				<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
				<div class="vcm-bcah-category-container" id="vcm-bcah-amenities-container">
					<div class="vcm-bcah-container-header">
						<span><?php echo JText::_('VCMBCAHAMENITIES');?></span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-amenity"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-amenities-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'amenities'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
							if(property_exists($oldData, 'amenities')){
								foreach ($oldData->amenitiesIndexes as $key => $index) { 
									echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-amenity".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-amenity-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-entry-contents\">
											<div class=\"vcm-bcah-subdetail\">
												<label>".JText::_('VCMBCAHAMENITY')."</label>
												<select name=\"vcm-bcah-amenity".$index."-selected-amenity\" class=\"vcm-bcah-amenity-selector\">
													<option value=\"\"></option>";
													foreach ($RMAAmenitiesNames as $value) {
														echo "<option value=\"".$RMAAmenitiesCodes[$value]."\" ".(isset($oldData->amenities->$index->amenity) && $oldData->amenities->$index->amenity==$RMAAmenitiesCodes[$value]? "selected":"").">".$value."</option>";
													}
												echo "</select>
											</div>
											<div class=\"vcm-bcah-subdetail vcm-bcah-amenities-quantity-div\";>
												<label>".JText::_('VCMBCAHQUANTITY')."</label>
												<input type=\"number\" name=\"vcm-bcah-amenity".$index."-quantity\" id=\"vcm-bcah-amenity".$index."-quantity\" value=\"".(isset($oldData->amenities->$index->quantity) ? $oldData->amenities->$index->quantity : '')."\" min=\"1\"/>
											</div>
											<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-amenity".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
										</div>
									</div>";
								}
							}?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-detail vcm-bcah-textarea-details">
					<span><?php echo JText::_('VCMBCAHFINFODET');?></span>
					<textarea name="vcm-bcah-facility-info-details"><?php echo isset($oldData->facilityInfoMessage) && $oldData->facilityInfoMessage;?></textarea>
				</div>
				<div class="vcm-bcah-bottom-button">
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
			</form>
		</div>
		<div id="4" class="vcm-bcah-container-content" style="<?php if(VikRequest::getString('tab')!="area-info"){echo "display: none;";}?>">
			<form name="vcm-bcah-area-info-form" id="vcm-bcah-area-info-form" method="POST" action="index.php?option=com_vikchannelmanager&task=bca.makeHotelXml">
				<!--<input type="hidden" name="progID" value="<?php echo $progID;?>"/>-->
				<input type="hidden" name="accountName" value="<?php echo $hotelName;?>"/>
				<div class="vcm-bcah-tab-title">
					<span class="vcm-bcah-content-title"><?php echo JText::_('VCMBCAHAINFO');?></span>
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
				<input type="hidden" name="submittedform" value="area-info"/>
				<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
				<div class="vcm-bcah-category-container" id="vcm-bcah-attractions-container">
					<div class="vcm-bcah-container-header">
						<span><?php echo JText::_('VCMBCAHATTRACTIONS');?></span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-attraction"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-attractions-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'attractions'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
							if(property_exists($oldData, 'attractions')){
								foreach ($oldData->attractionsIndexes as $key => $index) {
									echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-attraction".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-attraction-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-entry-header\">
											<span>
												<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-attraction".$index."-icon\" id=\"vcm-bcah-attraction".$index."\"></i>
												<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-attraction".$index."\">".JText::_('VCMBCAHATTRACTION')."</span>
											</span>
										</div>
										<div class=\"vcm-bcah-entry-contents vcm-bcah-attraction".$index."-div\">
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHATTNAME')."</label>
												<input type=\"text\" name=\"vcm-bcah-attraction".$index."-name\" value=\"".(isset($oldData->attractions->$index->attractionName) ? $oldData->attractions->$index->attractionName : '')."\"/>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHATTTYPE')."</label>
												<select name=\"vcm-bcah-attraction".$index."-selected-attraction-type\">
													<option value=\"5\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="5"? "selected":"").">".JText::_('VCMBCAHATTTYPE1')."</option>
													<option value=\"25\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="25"? "selected":"").">".JText::_('VCMBCAHATTTYPE2')."</option>
													<option value=\"29\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="29"? "selected":"").">".JText::_('VCMBCAHATTTYPE3')."</option>
													<option value=\"31\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="31"? "selected":"").">".JText::_('VCMBCAHATTTYPE4')."</option>
													<option value=\"33\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="33"? "selected":"").">".JText::_('VCMBCAHATTTYPE5')."</option>
													<option value=\"41\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="41"? "selected":"").">".JText::_('VCMBCAHATTTYPE6')."</option>
													<option value=\"42\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="42"? "selected":"").">".JText::_('VCMBCAHATTTYPE7')."</option>
													<option value=\"45\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="45"? "selected":"").">".JText::_('VCMBCAHATTTYPE8')."</option>
													<option value=\"47\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="47"? "selected":"").">".JText::_('VCMBCAHATTTYPE9')."</option>
													<option value=\"73\" ".(isset($oldData->attractions->$index->attractionCode) && $oldData->attractions->$index->attractionCode=="73"? "selected":"").">".JText::_('VCMBCAHATTTYPE10')."</option>
												</select>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHDISTANCE')."</label>
												<input type=\"number\" name=\"vcm-bcah-attraction".$index."-distance\" step=\"any\" value=\"".(isset($oldData->attractions->$index->distance) ? $oldData->attractions->$index->distance : '')."\"/>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHDISTMSR')."</label>
												<select name=\"vcm-bcah-attraction".$index."-distance-measurement\">
													<option value=\"miles\" ".(isset($oldData->attractions->$index->distanceMeasurement) && $oldData->attractions->$index->distanceMeasurement=="miles"? "selected":"").">".JText::_('VCMBCAHDISTMSR1')."</option>
													<option value=\"meters\" ".(isset($oldData->attractions->$index->distanceMeasurement) && $oldData->attractions->$index->distanceMeasurement=="meters"? "selected":"").">".JText::_('VCMBCAHDISTMSR2')."</option>
													<option value=\"kilometers\" ".(isset($oldData->attractions->$index->distanceMeasurement) && $oldData->attractions->$index->distanceMeasurement=="kilometers"? "selected":"").">".JText::_('VCMBCAHDISTMSR3')."</option>
													<option value=\"feet\" ".(isset($oldData->attractions->$index->distanceMeasurement) && $oldData->attractions->$index->distanceMeasurement=="feet"? "selected":"").">".JText::_('VCMBCAHDISTMSR4')."</option>
												</select>
											</div>
											<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-attraction".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
										</div>
									</div>";
								}
							}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-detail vcm-bcah-textarea-details">
					<span><?php echo JText::_('VCMBCAHAINFODET');?></span>
					<textarea name="vcm-bcah-area-info-details"><?php echo isset($oldData->areaInfoMessage) && $oldData->areaInfoMessage;?></textarea>
				</div>
				<div class="vcm-bcah-bottom-button">
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
			</form>
		</div>
		<div id="5" class="vcm-bcah-container-content" style="<?php if(VikRequest::getString('tab')!="policies"){echo "display: none;";}?>">
			<form name="vcm-bcah-policies-form" id="vcm-bcah-policies-form" method="POST" action="index.php?option=com_vikchannelmanager&task=bca.makeHotelXml">
				<!--<input type="hidden" name="progID" value="<?php echo $progID;?>"/>-->
				<input type="hidden" name="accountName" value="<?php echo $hotelName;?>"/>
				<div class="vcm-bcah-tab-title">
					<span class="vcm-bcah-content-title"><?php echo JText::_('VCMBCAHPOLICIES');?></span>
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
				<input type="hidden" name="submittedform" value="policies"/>
				<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
				<div class="vcm-bcah-category-container" id="vcm-bcah-timepolicies-container">
					<div class="vcm-bcah-detail vcm-bcah-container-header">
						<span>
							<i class="<?php echo (!property_exists($oldData, 'checkInTimeStart')&&!property_exists($oldData, 'checkOutTimeStart')&&!property_exists($oldData, 'checkInTimeEnd')&&!property_exists($oldData, 'checkOutTimeEnd'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-timepolicies-icon" id="vcm-bcah-timepolicies"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-timepolicies"><?php echo JText::_('VCMBCAHTIMEPOL');?></span>
						</span>
					</div>
					<div class="vcm-bcah-timepolicies-div" style="<?php echo (!property_exists($oldData, 'checkInTimeStart')&&!property_exists($oldData, 'checkOutTimeStart')&&!property_exists($oldData, 'checkInTimeEnd')&&!property_exists($oldData, 'checkOutTimeEnd'))? "display:none;" : "";?>">
						<div class="vcm-bcah-subdetail">
							<div class="vcm-bcah-start-time">
								<label><?php echo JText::_('VCMBCAHCHECKINFROM');?></label>
								<select name="vcm-bcah-check-in-time-start">
									<option value=""></option>
									<option value="07:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='07:00'? 'selected' : ''); ?>>7:00</option>
									<option value="07:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='07:30'? 'selected' : ''); ?>>7:30</option>
									<option value="08:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='08:00'? 'selected' : ''); ?>>8:00</option>
									<option value="08:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='08:30'? 'selected' : ''); ?>>8:30</option>
									<option value="09:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='09:00'? 'selected' : ''); ?>>9:00</option>
									<option value="09:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='09:30'? 'selected' : ''); ?>>9:30</option>
									<option value="10:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='10:00'? 'selected' : ''); ?>>10:00</option>
									<option value="10:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='10:30'? 'selected' : ''); ?>>10:30</option>
									<option value="11:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='11:00'? 'selected' : ''); ?>>11:00</option>
									<option value="11:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='11:30'? 'selected' : ''); ?>>11:30</option>
									<option value="12:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='12:00'? 'selected' : ''); ?>>12:00</option>
									<option value="12:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='12:30'? 'selected' : ''); ?>>12:30</option>
									<option value="13:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='13:00'? 'selected' : ''); ?>>13:00</option>
									<option value="13:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='13:30'? 'selected' : ''); ?>>13:30</option>
									<option value="14:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='14:00'? 'selected' : ''); ?>>14:00</option>
									<option value="14:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='14:30'? 'selected' : ''); ?>>14:30</option>
									<option value="15:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='15:00'? 'selected' : ''); ?>>15:00</option>
									<option value="15:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='15:30'? 'selected' : ''); ?>>15:30</option>
									<option value="16:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='16:00'? 'selected' : ''); ?>>16:00</option>
									<option value="16:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='16:30'? 'selected' : ''); ?>>16:30</option>
									<option value="17:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='17:00'? 'selected' : ''); ?>>17:00</option>
									<option value="17:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='17:30'? 'selected' : ''); ?>>17:30</option>
									<option value="18:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='18:00'? 'selected' : ''); ?>>18:00</option>
									<option value="18:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='18:30'? 'selected' : ''); ?>>18:30</option>
									<option value="19:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='19:00'? 'selected' : ''); ?>>19:00</option>
									<option value="19:30" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='19:30'? 'selected' : ''); ?>>19:30</option>
									<option value="20:00" <?php echo (isset($oldData->checkInTimeStart) && $oldData->checkInTimeStart=='20:00'? 'selected' : ''); ?>>20:00</option>
								</select>
							</div>
							<div class="vcm-bcah-end-time">
								<label><?php echo JText::_('VCMBCAHTO');?></label>
								<select name="vcm-bcah-check-in-time-end">
									<option value=""></option>
									<option value="07:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='07:00'? 'selected' : ''); ?>>7:00</option>
									<option value="07:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='07:30'? 'selected' : ''); ?>>7:30</option>
									<option value="08:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='08:00'? 'selected' : ''); ?>>8:00</option>
									<option value="08:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='08:30'? 'selected' : ''); ?>>8:30</option>
									<option value="09:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='09:00'? 'selected' : ''); ?>>9:00</option>
									<option value="09:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='09:30'? 'selected' : ''); ?>>9:30</option>
									<option value="10:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='10:00'? 'selected' : ''); ?>>10:00</option>
									<option value="10:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='10:30'? 'selected' : ''); ?>>10:30</option>
									<option value="11:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='11:00'? 'selected' : ''); ?>>11:00</option>
									<option value="11:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='11:30'? 'selected' : ''); ?>>11:30</option>
									<option value="12:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='12:00'? 'selected' : ''); ?>>12:00</option>
									<option value="12:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='12:30'? 'selected' : ''); ?>>12:30</option>
									<option value="13:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='13:00'? 'selected' : ''); ?>>13:00</option>
									<option value="13:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='13:30'? 'selected' : ''); ?>>13:30</option>
									<option value="14:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='14:00'? 'selected' : ''); ?>>14:00</option>
									<option value="14:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='14:30'? 'selected' : ''); ?>>14:30</option>
									<option value="15:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='15:00'? 'selected' : ''); ?>>15:00</option>
									<option value="15:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='15:30'? 'selected' : ''); ?>>15:30</option>
									<option value="16:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='16:00'? 'selected' : ''); ?>>16:00</option>
									<option value="16:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='16:30'? 'selected' : ''); ?>>16:30</option>
									<option value="17:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='17:00'? 'selected' : ''); ?>>17:00</option>
									<option value="17:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='17:30'? 'selected' : ''); ?>>17:30</option>
									<option value="18:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='18:00'? 'selected' : ''); ?>>18:00</option>
									<option value="18:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='18:30'? 'selected' : ''); ?>>18:30</option>
									<option value="19:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='19:00'? 'selected' : ''); ?>>19:00</option>
									<option value="19:30" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='19:30'? 'selected' : ''); ?>>19:30</option>
									<option value="20:00" <?php echo (isset($oldData->checkInTimeEnd) && $oldData->checkInTimeEnd=='20:00'? 'selected' : ''); ?>>20:00</option>
								</select>
							</div>
						</div>
						<div class="vcm-bcah-subdetail">
							<div class="vcm-bcah-start-time">
								<label><?php echo JText::_('VCMBCAHCHECKOUTFROM');?></label>
								<select name="vcm-bcah-check-out-time-start">
									<option value=""></option>
									<option value="07:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='07:00'? 'selected' : ''); ?>>7:00</option>
									<option value="07:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='07:30'? 'selected' : ''); ?>>7:30</option>
									<option value="08:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='08:00'? 'selected' : ''); ?>>8:00</option>
									<option value="08:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='08:30'? 'selected' : ''); ?>>8:30</option>
									<option value="09:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='09:00'? 'selected' : ''); ?>>9:00</option>
									<option value="09:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='09:30'? 'selected' : ''); ?>>9:30</option>
									<option value="10:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='10:00'? 'selected' : ''); ?>>10:00</option>
									<option value="10:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='10:30'? 'selected' : ''); ?>>10:30</option>
									<option value="11:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='11:00'? 'selected' : ''); ?>>11:00</option>
									<option value="11:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='11:30'? 'selected' : ''); ?>>11:30</option>
									<option value="12:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='12:00'? 'selected' : ''); ?>>12:00</option>
									<option value="12:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='12:30'? 'selected' : ''); ?>>12:30</option>
									<option value="13:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='13:00'? 'selected' : ''); ?>>13:00</option>
									<option value="13:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='13:30'? 'selected' : ''); ?>>13:30</option>
									<option value="14:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='14:00'? 'selected' : ''); ?>>14:00</option>
									<option value="14:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='14:30'? 'selected' : ''); ?>>14:30</option>
									<option value="15:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='15:00'? 'selected' : ''); ?>>15:00</option>
									<option value="15:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='15:30'? 'selected' : ''); ?>>15:30</option>
									<option value="16:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='16:00'? 'selected' : ''); ?>>16:00</option>
									<option value="16:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='16:30'? 'selected' : ''); ?>>16:30</option>
									<option value="17:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='17:00'? 'selected' : ''); ?>>17:00</option>
									<option value="17:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='17:30'? 'selected' : ''); ?>>17:30</option>
									<option value="18:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='18:00'? 'selected' : ''); ?>>18:00</option>
									<option value="18:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='18:30'? 'selected' : ''); ?>>18:30</option>
									<option value="19:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='19:00'? 'selected' : ''); ?>>19:00</option>
									<option value="19:30" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='19:30'? 'selected' : ''); ?>>19:30</option>
									<option value="20:00" <?php echo (isset($oldData->checkOutTimeStart) && $oldData->checkOutTimeStart=='20:00'? 'selected' : ''); ?>>20:00</option>
								</select>
							</div>
							<div class="vcm-bcah-end-time">
								<label><?php echo JText::_('VCMBCAHTO');?></label>
								<select name="vcm-bcah-check-out-time-end">
									<option value=""></option>
									<option value="07:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='07:00'? 'selected' : ''); ?>>7:00</option>
									<option value="07:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='07:30'? 'selected' : ''); ?>>7:30</option>
									<option value="08:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='08:00'? 'selected' : ''); ?>>8:00</option>
									<option value="08:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='08:30'? 'selected' : ''); ?>>8:30</option>
									<option value="09:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='09:00'? 'selected' : ''); ?>>9:00</option>
									<option value="09:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='09:30'? 'selected' : ''); ?>>9:30</option>
									<option value="10:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='10:00'? 'selected' : ''); ?>>10:00</option>
									<option value="10:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='10:30'? 'selected' : ''); ?>>10:30</option>
									<option value="11:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='11:00'? 'selected' : ''); ?>>11:00</option>
									<option value="11:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='11:30'? 'selected' : ''); ?>>11:30</option>
									<option value="12:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='12:00'? 'selected' : ''); ?>>12:00</option>
									<option value="12:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='12:30'? 'selected' : ''); ?>>12:30</option>
									<option value="13:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='13:00'? 'selected' : ''); ?>>13:00</option>
									<option value="13:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='13:30'? 'selected' : ''); ?>>13:30</option>
									<option value="14:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='14:00'? 'selected' : ''); ?>>14:00</option>
									<option value="14:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='14:30'? 'selected' : ''); ?>>14:30</option>
									<option value="15:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='15:00'? 'selected' : ''); ?>>15:00</option>
									<option value="15:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='15:30'? 'selected' : ''); ?>>15:30</option>
									<option value="16:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='16:00'? 'selected' : ''); ?>>16:00</option>
									<option value="16:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='16:30'? 'selected' : ''); ?>>16:30</option>
									<option value="17:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='17:00'? 'selected' : ''); ?>>17:00</option>
									<option value="17:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='17:30'? 'selected' : ''); ?>>17:30</option>
									<option value="18:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='18:00'? 'selected' : ''); ?>>18:00</option>
									<option value="18:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='18:30'? 'selected' : ''); ?>>18:30</option>
									<option value="19:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='19:00'? 'selected' : ''); ?>>19:00</option>
									<option value="19:30" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='19:30'? 'selected' : ''); ?>>19:30</option>
									<option value="20:00" <?php echo (isset($oldData->checkOutTimeEnd) && $oldData->checkOutTimeEnd=='20:00'? 'selected' : ''); ?>>20:00</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-kidspolicies-container">
					<div class="vcm-bcah-detail vcm-bcah-container-header">
						<span>
							<i class="<?php echo (!property_exists($oldData, 'kidsStayFree')&&!property_exists($oldData, 'kidsCutoffAge')&&!property_exists($oldData, 'stayFreeChildren'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-kidspolicies-icon" id="vcm-bcah-kidspolicies"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-kidspolicies"><?php echo JText::_('VCMBCAHKIDSPOLI');?></span>
						</span>
					</div>
					<div class="vcm-bcah-kidspolicies-div" style="<?php echo (!property_exists($oldData, 'kidsStayFree')&&!property_exists($oldData, 'kidsCutoffAge')&&!property_exists($oldData, 'stayFreeChildren'))? "display: none;" : "";?>">
						<div class="vcm-bcah-subdetail vcm-bcah-subdetail-checkbox-detail">
							<label><?php echo JText::_('VCMBCAHKIDSFREE');?></label>
							<input type="checkbox" name="vcm-bcah-kids-stay-free" id="vcm-bcah-kids-stay-free" <?php echo (isset($oldData->kidsStayFree) && $oldData->kidsStayFree=='1'? 'checked' : ''); ?>/>
						</div>
						<div class="vcm-bcah-subdetail" id="vcm-bcah-free-cutoff-age-div"> <!-- style="<?php echo (!property_exists($oldData, 'kidsStayFree')||$oldData->kidsStayFree!='1')? "display:none;" : ""?>" -->
							<label><?php echo JText::_('VCMBCAHKIDSCUTOFF');?></label>
							<input type="number" name="vcm-bcah-free-cutoff-age" id="vcm-bcah-free-cutoff-age" value="<?php echo isset($oldData->kidsCutoffAge) ? $oldData->kidsCutoffAge : ''; ?>" min="0"/> <!-- <?php echo (!property_exists($oldData, 'kidsStayFree')||$oldData->kidsStayFree!='1')? "disabled" : ""?> -->
						</div>
						<div class="vcm-bcah-subdetail" id="vcm-bcah-free-child-per-adult-div"> <!-- style="<?php echo (!property_exists($oldData, 'kidsStayFree')||$oldData->kidsStayFree!='1')? "display:none;" : ""?>" -->
							<label><?php echo JText::_('VCMBCAHKIDPERADULT');?></label>
							<input type="number" name="vcm-bcah-free-child-per-adult" id="vcm-bcah-free-child-per-adult" value="<?php echo isset($oldData->stayFreeChildren) ? $oldData->stayFreeChildren : ''; ?>" min="0"/> <!-- <?php echo (!property_exists($oldData, 'kidsStayFree')||$oldData->kidsStayFree!='1')? "disabled" : ""?> -->
						</div>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-petspolicies-container">
					<div class="vcm-bcah-detail vcm-bcah-container-header">
						<span>
							<i class="<?php echo (!property_exists($oldData, 'petsAllowedCode')&&!property_exists($oldData, 'nonRefundableFee'))? "vboicn-circle-down" : "vboicn-circle-up";?> vcm-bcah-hide-button vcm-bcah-petspolicies-icon" id="vcm-bcah-petspolicies"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-petspolicies"><?php echo JText::_('VCMBCAHPETSPOLI');?></span>
						</span>
					</div>
					<div class="vcm-bcah-petspolicies-div" style="<?php echo (!property_exists($oldData, 'petsAllowedCode')&&!property_exists($oldData, 'nonRefundableFee'))? "display:none;" : "";?>">
						<div class="vcm-bcah-subdetail">
							<label><?php echo JText::_('VCMBCAHPETSENTRANCE');?></label>
							<select name="vcm-bcah-pets-allowed">
								<option value=""></option>
								<option value="Pets Allowed" <?php echo (isset($oldData->petsAllowedCode) && $oldData->petsAllowedCode=='Pets Allowed'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHPETSALLOW');?></option>
								<option value="Pets Not Allowed" <?php echo (isset($oldData->petsAllowedCode) && $oldData->petsAllowedCode=='Pets Not Allowed'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHPETSNALLOW');?></option>
								<option value="Pets By Arrangements" <?php echo (isset($oldData->petsAllowedCode) && $oldData->petsAllowedCode=='Pets By Arrangements'? 'selected' : ''); ?>><?php echo JText::_('VCMBCAHPETSNARRANGE');?></option>
							</select>
						</div>
						<div class="vcm-bcah-subdetail">
							<label><?php echo JText::_('VCMBCAHNONREFFEE');?></label>
							<input type="number" name="vcm-bcah-non-refundable-fee" value="<?php echo isset($oldData->nonRefundableFee) ? $oldData->nonRefundableFee : ''; ?>" min="0"/>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-guaranteepayments-container">
					<div class="vcm-bcah-container-header">
						<span>
							<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-guaranteepayments-icon" id="vcm-bcah-guaranteepayments" style="<?php echo (!property_exists($oldData, 'guaranteepayments'))? "display: none;" : "";?>"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-guaranteepayments"><?php echo JText::_('VCMBCAHGUAPAYPOLS');?></span>
						</span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-guaranteepayment"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-guaranteepayments-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'guaranteepayments'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
							if(property_exists($oldData, 'guaranteepayments')){
								foreach ($oldData->guaranteepaymentsIndexes as $key => $index) {
									echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-guaranteepayment".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-guaranteepayment-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-entry-contents\">
											<label>".JText::_('VCMBCAHGUAPAYPOL')."</label>
											<select name=\"vcm-bcah-guaranteepayment".$index."-selected-guaranteed-payment\">";
												foreach ($policyCodes as $key => $value) {
													echo "<option value=\"".$value."\" ".(isset($oldData->guaranteepayments->$index->guaranteepayment) && $oldData->guaranteepayments->$index->guaranteepayment==$value? "selected":"").">".$key."</option>";
												}
											echo "</select>
											<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-guaranteepayment".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
										</div>
									</div>";
								}
							}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-cancelpolicies-container">
					<div class="vcm-bcah-container-header">
						<span>
							<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-cancelpolicies-icon" id="vcm-bcah-cancelpolicies" style="<?php echo (!property_exists($oldData, 'cancelpolicies'))? "display: none;" : "";?>"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-cancelpolicies"><?php echo JText::_('VCMBCAHCANCPOLS');?></span>
						</span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-cancelpolicy"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-cancelpolicies-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'cancelpolicies'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
							if(property_exists($oldData, 'cancelpolicies')){
								foreach ($oldData->cancelpoliciesIndexes as $key => $index) {
									echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-cancelpolicy".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-cancelpolicy-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-entry-contents\">
											<label>".JText::_('VCMBCAHCANCPOLS')."</label>
											<select name=\"vcm-bcah-cancelpolicy".$index."-selected-cancel-policy\">";
												foreach ($policyCodes as $key => $value) {
													echo "<option value=\"".$value."\" ".(isset($oldData->cancelpolicies->$index->cancelpolicy) && $oldData->cancelpolicies->$index->cancelpolicy==$value? "selected":"").">".$key."</option>";
												}
											echo "</select>
											<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-cancelpolicy".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
										</div>
									</div>";
								}
							}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-taxes-container">
					<div class="vcm-bcah-container-header">
						<span>
							<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-taxes-icon" id="vcm-bcah-taxes" style="<?php echo (!property_exists($oldData, 'taxes'))? "display: none;" : "";?>"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-taxes"><?php echo JText::_('VCMBCAHTAXES');?></span>
						</span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-tax"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-taxes-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'taxes'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
							if(property_exists($oldData, 'taxes')){
								foreach ($oldData->taxesIndexes as $key => $index) {
									echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-tax".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-tax-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-entry-header\">
											<span>
												<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-tax".$index."-icon\" id=\"vcm-bcah-tax".$index."\"></i>
												<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-tax".$index."\">".JText::_('VCMBCAHTAX')."</span>
											</span>
										</div>
										<div class=\"vcm-bcah-entry-contents vcm-bcah-tax".$index."-div\">
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHTAXTYPE')."</label>
												<select name=\"vcm-bcah-tax".$index."-selected-tax\">
													<option value=\"3\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="3"? "selected":"").">".JText::_('VCMBCAHTAXTYPE1')."</option>
													<option value=\"13\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="13"? "selected":"").">".JText::_('VCMBCAHTAXTYPE2')."</option>
													<option value=\"35\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="35"? "selected":"").">".JText::_('VCMBCAHTAXTYPE3')."</option>
													<option value=\"36\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="36"? "selected":"").">".JText::_('VCMBCAHTAXTYPE4')."</option>
													<option value=\"46\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="46"? "selected":"").">".JText::_('VCMBCAHTAXTYPE5')."</option>
													<option value=\"5001\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="5001"? "selected":"").">".JText::_('VCMBCAHTAXTYPE6')."</option>
													<option value=\"5002\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="5002"? "selected":"").">".JText::_('VCMBCAHTAXTYPE7')."</option>
													<option value=\"5004\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="5004"? "selected":"").">".JText::_('VCMBCAHTAXTYPE8')."</option>
													<option value=\"5007\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="5007"? "selected":"").">".JText::_('VCMBCAHTAXTYPE9')."</option>
													<option value=\"5008\" ".(isset($oldData->taxes->$index->code) && $oldData->taxes->$index->code=="5008"? "selected":"").">".JText::_('VCMBCAHTAXTYPE10')."</option>
												</select>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHAMOUNT')."</label>
												<input type=\"number\" name=\"vcm-bcah-tax".$index."-amount\" step=\"any\" value=\"".(isset($oldData->taxes->$index->amount) ? $oldData->taxes->$index->amount : '')."\"/><span>%</span>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHDECIMALPLACES')."</label>
												<input type=\"number\" name=\"vcm-bcah-tax".$index."-decimal-places\" value=\"".(isset($oldData->taxes->$index->decimalPlaces) ? $oldData->taxes->$index->decimalPlaces : '')."\" min=\"0\"/>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHPRICETYPE')."</label>
												<select name=\"vcm-bcah-tax".$index."-type\">
													<option value=\"Inclusive\" ".(isset($oldData->taxes->$index->type) && $oldData->taxes->$index->type=="Inclusive"? "selected":"").">".JText::_('VCMBCAHINCLUS')."</option>
													<option value=\"Exclusive\" ".(isset($oldData->taxes->$index->type) && $oldData->taxes->$index->type=="Exclusive"? "selected":"").">".JText::_('VCMBCAHEXCLUS')."</option>
												</select>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHCHGFRQ')."</label>
												<select name=\"vcm-bcah-tax".$index."-charge-frequency\">
													<option value=\"12\" ".(isset($oldData->taxes->$index->chargeFrequency) && $oldData->taxes->$index->chargeFrequency=="12"? "selected":"").">".JText::_('VCMBCAHCHGFRQ1')."</option>
													<option value=\"19\" ".(isset($oldData->taxes->$index->chargeFrequency) && $oldData->taxes->$index->chargeFrequency=="19"? "selected":"").">".JText::_('VCMBCAHCHGFRQ2')."</option>
													<option value=\"20\" ".(isset($oldData->taxes->$index->chargeFrequency) && $oldData->taxes->$index->chargeFrequency=="20"? "selected":"").">".JText::_('VCMBCAHCHGFRQ3')."</option>
													<option value=\"21\" ".(isset($oldData->taxes->$index->chargeFrequency) && $oldData->taxes->$index->chargeFrequency=="21"? "selected":"").">".JText::_('VCMBCAHCHGFRQ4')."</option>
													<option value=\"5000\" ".(isset($oldData->taxes->$index->chargeFrequency) && $oldData->taxes->$index->chargeFrequency=="5000"? "selected":"").">".JText::_('VCMBCAHCHGFRQ5')."</option>
												</select>
											</div>
											<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-tax".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
										</div>
									</div>";
								}
							}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-fees-container">
					<div class="vcm-bcah-container-header">
						<span>
							<i class="vboicn-circle-up vcm-bcah-hide-button vcm-bcah-fees-icon" id="vcm-bcah-fees" style="<?php echo (!property_exists($oldData, 'fees'))? "display: none;" : "";?>"></i>
							<span class="vcm-bcah-hide-button" id="vcm-bcah-fees"><?php echo JText::_('VCMBCAHFEES');?></span>
						</span>
						<button type="button" class="btn vcm-bcah-new-button vcm-bcah-fee"><i class="icon-plus"></i><?php echo JText::_('VCMBCAHADD');?></button>
					</div>
					<div class="vcm-bcah-fees-div vcm-bcah-entry-container" style="<?php echo (!property_exists($oldData, 'fees'))? "display: none;" : "";?>">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
							if(property_exists($oldData, 'fees')){
								foreach ($oldData->feesIndexes as $key => $index) {
									echo "<div class=\"vcm-bcah-entry-instance vcm-bcah-fee".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-fee-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-entry-header\">
											<span>
												<i class=\"vboicn-circle-up vcm-bcah-hide-button vcm-bcah-fee".$index."-icon\" id=\"vcm-bcah-fee".$index."\"></i>
												<span class=\"vcm-bcah-hide-button\" id=\"vcm-bcah-fee".$index."\">".JText::_('VCMBCAHFEE')."</span>
											</span>
										</div>
										<div class=\"vcm-bcah-entry-contents vcm-bcah-fee".$index."-div\">
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHFEETYPE')."</label>
												<select name=\"vcm-bcah-fee".$index."-selected-fee\">
													<option value=\"12\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="12"? "selected":"").">".JText::_('VCMBCAHFEETYPE1')."</option>
													<option value=\"14\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="14"? "selected":"").">".JText::_('VCMBCAHFEETYPE2')."</option>
													<option value=\"18\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="18"? "selected":"").">".JText::_('VCMBCAHFEETYPE3')."</option>
													<option value=\"55\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="55"? "selected":"").">".JText::_('VCMBCAHFEETYPE4')."</option>
													<option value=\"5000\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5000"? "selected":"").">".JText::_('VCMBCAHFEETYPE5')."</option>
													<option value=\"5003\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5003"? "selected":"").">".JText::_('VCMBCAHFEETYPE6')."</option>
													<option value=\"5005\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5005"? "selected":"").">".JText::_('VCMBCAHFEETYPE7')."</option>
													<option value=\"5006\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5006"? "selected":"").">".JText::_('VCMBCAHFEETYPE8')."</option>
													<option value=\"5009\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5009"? "selected":"").">".JText::_('VCMBCAHFEETYPE9')."</option>
													<option value=\"5010\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5010"? "selected":"").">".JText::_('VCMBCAHFEETYPE10')."</option>
													<option value=\"5011\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5011"? "selected":"").">".JText::_('VCMBCAHFEETYPE11')."</option>
													<option value=\"5012\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5012"? "selected":"").">".JText::_('VCMBCAHFEETYPE12')."</option>
													<option value=\"5013\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5013"? "selected":"").">".JText::_('VCMBCAHFEETYPE13')."</option>
													<option value=\"5014\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5014"? "selected":"").">".JText::_('VCMBCAHFEETYPE14')."</option>
													<option value=\"5015\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5015"? "selected":"").">".JText::_('VCMBCAHFEETYPE15')."</option>
													<option value=\"5016\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5016"? "selected":"").">".JText::_('VCMBCAHFEETYPE16')."</option>
													<option value=\"5017\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5017"? "selected":"").">".JText::_('VCMBCAHFEETYPE17')."</option>
													<option value=\"5018\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5018"? "selected":"").">".JText::_('VCMBCAHFEETYPE18')."</option>
													<option value=\"5019\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5019"? "selected":"").">".JText::_('VCMBCAHFEETYPE19')."</option>
													<option value=\"5020\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5020"? "selected":"").">".JText::_('VCMBCAHFEETYPE20')."</option>
													<option value=\"5021\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5021"? "selected":"").">".JText::_('VCMBCAHFEETYPE21')."</option>
													<option value=\"5022\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5022"? "selected":"").">".JText::_('VCMBCAHFEETYPE22')."</option>
													<option value=\"5023\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5023"? "selected":"").">".JText::_('VCMBCAHFEETYPE23')."</option>
													<option value=\"5024\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5024"? "selected":"").">".JText::_('VCMBCAHFEETYPE24')."</option>
													<option value=\"5025\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5025"? "selected":"").">".JText::_('VCMBCAHFEETYPE25')."</option>
													<option value=\"5026\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5026"? "selected":"").">".JText::_('VCMBCAHFEETYPE26')."</option>
													<option value=\"5027\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5027"? "selected":"").">".JText::_('VCMBCAHFEETYPE27')."</option>
													<option value=\"5028\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5028"? "selected":"").">".JText::_('VCMBCAHFEETYPE28')."</option>
													<option value=\"5029\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5029"? "selected":"").">".JText::_('VCMBCAHFEETYPE29')."</option>
													<option value=\"5030\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5030"? "selected":"").">".JText::_('VCMBCAHFEETYPE30')."</option>
													<option value=\"5031\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5031"? "selected":"").">".JText::_('VCMBCAHFEETYPE31')."</option>
													<option value=\"5032\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5032"? "selected":"").">".JText::_('VCMBCAHFEETYPE32')."</option>
													<option value=\"5033\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5033"? "selected":"").">".JText::_('VCMBCAHFEETYPE33')."</option>
													<option value=\"5034\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5034"? "selected":"").">".JText::_('VCMBCAHFEETYPE34')."</option>
													<option value=\"5035\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5035"? "selected":"").">".JText::_('VCMBCAHFEETYPE35')."</option>
													<option value=\"5036\" ".(isset($oldData->fees->$index->code) && $oldData->fees->$index->code=="5036"? "selected":"").">".JText::_('VCMBCAHFEETYPE36')."</option>
												</select>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHAMOUNT')."</label>
												<input type=\"number\" name=\"vcm-bcah-fee".$index."-amount\" step=\"any\" value=\"".(isset($oldData->fees->$index->amount) ? $oldData->fees->$index->amount : '')."\"/><span>%</span>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHDECIMALPLACES')."</label>
												<input type=\"number\" name=\"vcm-bcah-fee".$index."-decimal-places\" value=\"".(isset($oldData->fees->$index->decimalPlaces) ? $oldData->fees->$index->decimalPlaces : '')."\" min=\"0\"/>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHPRICETYPE')."</label>
												<select name=\"vcm-bcah-fee".$index."-type\">
													<option value=\"Inclusive\" ".(isset($oldData->fees->$index->type) && $oldData->fees->$index->type=="Inclusive"? "selected":"").">".JText::_('VCMBCAHINCLUS')."</option>
													<option value=\"Exclusive\" ".(isset($oldData->fees->$index->type) && $oldData->fees->$index->type=="Exclusive"? "selected":"").">".JText::_('VCMBCAHEXCLUS')."</option>
												</select>
											</div>
											<div class=\"vcm-bcah-entry-detail\">
												<label>".JText::_('VCMBCAHCHGFRQ')."</label>
												<select name=\"vcm-bcah-fee".$index."-charge-frequency\">
													<option value=\"12\" ".(isset($oldData->fees->$index->chargeFrequency) && $oldData->fees->$index->chargeFrequency=="12"? "selected":"").">".JText::_('VCMBCAHCHGFRQ1')."</option>
													<option value=\"19\" ".(isset($oldData->fees->$index->chargeFrequency) && $oldData->fees->$index->chargeFrequency=="19"? "selected":"").">".JText::_('VCMBCAHCHGFRQ2')."</option>
													<option value=\"20\" ".(isset($oldData->fees->$index->chargeFrequency) && $oldData->fees->$index->chargeFrequency=="20"? "selected":"").">".JText::_('VCMBCAHCHGFRQ3')."</option>
													<option value=\"21\" ".(isset($oldData->fees->$index->chargeFrequency) && $oldData->fees->$index->chargeFrequency=="21"? "selected":"").">".JText::_('VCMBCAHCHGFRQ4')."</option>
													<option value=\"5000\" ".(isset($oldData->fees->$index->chargeFrequency) && $oldData->fees->$index->chargeFrequency=="5000"? "selected":"").">".JText::_('VCMBCAHCHGFRQ5')."</option>
												</select>
											</div>
											<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-fee".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
										</div>
									</div>";
								}
							}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-bottom-button">
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
			</form>
		</div>
		<div id="6" class="vcm-bcah-container-content" style="<?php if(VikRequest::getString('tab')!="multimedia"){echo "display: none;";}?>">
			<form name="vcm-bcah-multimedia-form" enctype="multipart/form-data" id="vcm-bcah-multimedia-form" method="POST" action="index.php?option=com_vikchannelmanager&task=bca.makeHotelXml">
				<!--<input type="hidden" name="progID" value="<?php echo $progID;?>"/>-->
				<input type="hidden" name="accountName" value="<?php echo $hotelName;?>"/>
				<div class="vcm-bcah-tab-title">
					<span class="vcm-bcah-content-title"><?php echo JText::_('VCMBCAHMULTIMEDIA');?></span>
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
				<input type="hidden" name="submittedform" value="multimedia"/>
				<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
				<div class="vcm-bcah-upload-type-selector">
					<label><?php echo JText::_('VCMBCAHUPLOADTYPE');?></label>
					<ul>
						<li>
							<?php echo JText::_('VCMBCAHUPLOADTYPEDESC1');?>
						</li>
						<li>
							<?php echo JText::_('VCMBCAHUPLOADTYPEDESC2');?>
						</li>
						<li>
							<?php echo JText::_('VCMBCAHUPLOADTYPEDESC3');?>
						</li>
					</ul>
					<select name="vcm-bcah-upload-type-selector" id="vcm-bcah-upload-type-selector">
						<option value="all"><?php echo JText::_('VCMBCAHUPLOADTYPE1');?></option>
						<option value="manual"><?php echo JText::_('VCMBCAHUPLOADTYPE2');?></option>
						<option value="contentAPI"><?php echo JText::_('VCMBCAHUPLOADTYPE3');?></option>
					</select>
				</div>
				<div class="vcm-bcah-category-container" id="vcm-bcah-images-container">
					<div class="vcm-bcah-container-header">
						<label><?php echo JText::_('VCMBCAHUPLOADDESC');?></label>
						<input type="file" data-index="ImageIndex" name="vcm-image-upload" id="vcm-bcah-image-input" size="35" onChange="uploadImageAJAX(this);">
					</div>
					<div class="vcm-bcah-images-div vcm-bcah-entry-container">
						<div class="vcm-bcah-entry-instance-container">
							<!--PHP GOES HERE-->
							<?php
							if(property_exists($oldData, 'images')){
								foreach ($oldData->imagesIndexes as $key => $index) {
									echo "
									<div class=\"vcm-bcah-entry-instance vcm-bcah-image".$index."\">
										<input type=\"hidden\" name=\"vcm-bcah-image-index[]\" value=\"".$index."\"/>
										<div class=\"vcm-bcah-image-instance\">
											<div class=\"vcm-bcah-entry-header\">
												<div class=\"vcm-bcah-image-holder\">
													<img src=\"".$oldData->images->$index->url."\"/>
												</div>
											</div>
											<div class=\"vcm-bcah-entry-contents vcm-bcah-image".$index."-div\">
												<div class=\"vcm-bcah-detail\">
													<div class=\"vcm-bcah-subdetail\">
														<label>".JText::_('VCMBCAHIMGURL')."</label>
														<input type=\"text\" disabled name=\"vcm-bcah-image".$index."-image-url-shown\" value=\"".(isset($oldData->images->$index->url) ? $oldData->images->$index->url : '')."\" size=\"100\"/>
														<input type=\"hidden\" name=\"vcm-bcah-image".$index."-image-url\" value=\"".(isset($oldData->images->$index->url) ? $oldData->images->$index->url : '')."\"/>
													</div>
													<div class=\"vcm-bcah-subdetail\">
														<label>".JText::_('VCMBCAHIMGTAG')."</label>
														<select name=\"vcm-bcah-image".$index."-selected-tag[]\" multiple class=\"vcm-bca-multi-select\">";
															foreach ($imageTagCodes as $key => $value) {
																echo "<option value=\"".$value."\" ";
																if(is_array($oldData->images->$index->tag)) {
																	foreach ($oldData->images->$index->tag as $tag) {
																		echo $tag == $value ? "selected":"";
																	}
																}
																echo ">".$key."</option>";
															}
														echo "</select>
													</div>
													<div class=\"vcm-bcah-image-subdetails\">
														<div class=\"vcm-bcah-subdetail vcm-bcah-subdetail-checkbox-detail\">
															<label>".JText::_('VCMBCAHMAINIMAGE')."</label>
															<input type=\"checkbox\" name=\"vcm-bcah-image".$index."-main-image\" ".(isset($oldData->images->$index->main) && $oldData->images->$index->main==1? 'checked' : '')."/>
														</div>
													</div>
												</div>
											</div>
											<div class=\"vcm-bcah-image-controller\">
												<button type=\"button\" class=\"btn vcm-bcah-hide-button\" id=\"vcm-bcah-image".$index."\">".JText::_('VCMBCAHSHHIDETAILS')."</button>
												<button type=\"button\" class=\"btn vcm-bcah-delete-button\" id=\"vcm-bcah-image".$index."\"><i class=\"vboicn-cancel-circle\"></i>".JText::_('VCMBCAHDELETE')."</button>
											</div>
										</div>
									</div>";
								}
							}
							?>
						</div>
					</div>
				</div>
				<div class="vcm-bcah-bottom-button">
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
			</form>
			<form method="POST" name="adminForm" id="adminForm" action="index.php">
				<input type="hidden" name="hotelid" value="<?php echo $channel['params']['hotelid'];?>"/>
				<input type="hidden" name="task" value="dashboard"/>
				<input type="hidden" name="option" value="com_vikchannelmanager"/>
				<input type="hidden" name="e4j_debug" value="<?php echo $e4j_debug;?>"/>
			</form>
		</div>
		<div id="7" class="vcm-bcah-container-content" style="<?php if(VikRequest::getString('tab')!="standardphrases"){echo "display: none;";}?>">
			<form name="vcm-bcah-sphrases-form" id="vcm-bcah-sphrases-form" method="POST" action="index.php?option=com_vikchannelmanager&task=bca.makeHotelXml">
				<!--<input type="hidden" name="progID" value="<?php echo $progID;?>"/>-->
				<input type="hidden" name="accountName" value="<?php echo $hotelName;?>"/>
				<div class="vcm-bcah-tab-title">
					<span class="vcm-bcah-content-title"><?php echo JText::_('VCMBCAHSPHRASES');?></span>
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
				<input type="hidden" name="submittedform" value="standardphrases"/>
				<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug');?>"/>
				<div class="vcm-detail-checkbox-detail">
					<label><?php echo JText::_('VCMBCAHSPGUESTID');?></label>
					<input type="checkbox" name="vcm-bcah-sp-guest-id" <?php echo isset($oldData->standardphrases->guestid) && $oldData->standardphrases->guestid == 1? "checked":""; ?>/>
				</div>
				<div class="vcm-detail-checkbox-detail">
					<label><?php echo JText::_('VCMBCAHSPINFORMARRIVAL');?></label>
					<input type="checkbox" name="vcm-bcah-sp-inform-arrival" <?php echo isset($oldData->standardphrases->informarrival) && $oldData->standardphrases->informarrival == 1? "checked":""; ?>/>
				</div>
				<div class="vcm-detail-checkbox-detail">
					<label><?php echo JText::_('VCMBCAHSPPAYBEFORESTAY');?></label>
					<input type="checkbox" name="vcm-bcah-sp-pay-before-stay" <?php echo isset($oldData->standardphrases->beforestay) && $oldData->standardphrases->beforestay == 1? "checked":""; ?>/>
				</div>
				<div class="vcm-detail-checkbox-detail">
					<label><?php echo JText::_('VCMBCAHSPTATOORESTRICTION');?></label>
					<input type="checkbox" name="vcm-bcah-tatoo-restriction" <?php echo isset($oldData->standardphrases->tatoorestriction) && $oldData->standardphrases->tatoorestriction == 1? "checked":""; ?>/>
				</div>
				<div class="vcm-detail-checkbox-detail">
					<label><?php echo JText::_('VCMBCAHSPKEYCOLLECTION');?></label>
					<input type="checkbox" name="vcm-bcah-sp-key-collection" <?php echo isset($oldData->standardphrases->keycollection) && $oldData->standardphrases->keycollection == 1? "checked":""; ?>/>
					<div class="">
						<label><?php echo JText::_('VCMBCAHSPKCADDRESS');?></label>
						<input type="text" name="vcm-bcah-sp-key-collection-address" value="<?php echo isset($oldData->standardphrases->kcaddress) ? $oldData->standardphrases->kcaddress : ''; ?>"/>
					</div>
					<div class="">
						<label><?php echo JText::_('VCMBCAHSPKCCITY');?></label>
						<input type="text" name="vcm-bcah-sp-key-collection-city" value="<?php echo isset($oldData->standardphrases->kccity) ? $oldData->standardphrases->kccity : ''; ?>"/>
					</div>
					<div class="">
						<label><?php echo JText::_('VCMBCAHSPKCPOSTAL');?></label>
						<input type="text" name="vcm-bcah-sp-key-collection-postal" value="<?php echo isset($oldData->standardphrases->kcpostal) ? $oldData->standardphrases->kcpostal : ''; ?>"/>
					</div>
				</div>
				<div>
					<div class="vcm-detail-checkbox-detail">
						<label><?php echo JText::_('VCMBCAHSPRENOVATION');?></label>
						<input type="checkbox" name="vcm-bcah-sp-renovation" <?php echo isset($oldData->standardphrases->renovation) && $oldData->standardphrases->renovation == 1? "checked":""; ?>/>
					</div>
					<div>
						<span class="vcminlinedate"><?php echo JText::_('VCMFROMDATE'); ?> <?php echo JHTML::_('calendar', (isset($oldData->standardphrases->rvfromdate) ? $oldData->standardphrases->rvfromdate : ''), 'vcm-bcah-sp-rv-fromdate', 'vcm-bcah-sp-rv-fromdate', '%Y-%m-%d', array('class'=>'vcm-detail-input-fromdate', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></span>
						<span class="vcminlinedate"><?php echo JText::_('VCMTODATE'); ?> <?php echo JHTML::_('calendar', (isset($oldData->standardphrases->rvtodate) ? $oldData->standardphrases->rvtodate : ''), 'vcm-bcah-sp-rv-todate', 'vcm-bcah-sp-rv-todate', '%Y-%m-%d', array('class'=>'vcm-detail-input-todate', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></span>
					</div>
				</div>
				<div class="vcm-bcah-bottom-button">
					<button type="submit" class="btn vcm-bcah-submit-button"><i class="icon-save"></i><?php echo JText::_('VCMBCAHSUBMIT');?></button>
				</div>
			</form>
		</div>
	</div>
</div>