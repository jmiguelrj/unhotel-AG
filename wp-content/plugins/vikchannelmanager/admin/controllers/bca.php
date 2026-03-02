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

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerBca extends JControllerAdmin {

	public function makeHotelXml () {

		$dbo = JFactory::getDbo();

		$mainframe = JFactory::getApplication();

		$vik = new VikApplication(VersionListener::getID());

		$hotelName = htmlentities(VikRequest::getString('accountName'));

		$e4j_debug = VikRequest::getInt('e4j_debug');

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$session = JFactory::getSession();
		$session->set('vcmbcahcont'.$channel['params']['hotelid'],'');

		$e4jc_url="https://e4jconnect.com/channelmanager/?r=bcahc&c=".$channel['name'];

		$noValueCount=0;
		$errorLimit=0;
		$timeError=0;

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcahcont".$channel['params']['hotelid']."';";
		$dbo->setQuery($q);
		$dbo->execute();
		//echo "<pre>".print_r($_REQUEST, true)."</pre>";die;
		if ($dbo->getNumRows() > 0) {
			$oldData = $dbo->loadAssoc();
			$oldData=json_decode($oldData['setting'],true);
			/*echo "<pre>".print_r($oldData,true)."</pre>";
			echo "<pre>".print_r($oldData->people,true)."</pre>";*/
			if(!empty($oldData)&&$e4j_debug==1){
				echo "</br><strong>Printing Old Data</strong></br>";
				echo "<pre>".print_r($oldData,true)."</pre>";
			}
		}

		if($e4j_debug==1){
			echo "</br><strong>Printing REQUEST</strong></br>";
			echo "<pre>".print_r($_REQUEST,true)."</pre>";
		}

		$langTag = substr(JFactory::getLanguage()->getTag(),0,2);

		$physical_locationHotelName = htmlentities(VikRequest::getString('vcm-bcah-address-physical-location-hotel-name'));
		$physical_locationPropertyLicenseNumber = htmlentities(VikRequest::getString('vcm-bcah-address-physical-location-property-license-number'));

		$content_array=array();

		$insertType = VikRequest::getString('vcm-bcah-insert-type');

		$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n
			<!-- BCAHC Request e4jConnect.com - VikChannelManager - VikBooking -->\n
			<BCAHotelContentRQ xmlns=\"http://www.e4jconnect.com/avail/bcahcrq\">\n
				<Notify client=\"".JUri::root()."\"/>\n
				<Api key=\"".VikChannelManager::getApiKey()."\"/>\n";

		if($insertType!=""){
			$content_array['insertType'] = $insertType;
		}
		if($insertType=='New'){
			$xmlFinal.="<BCAHotelContent hotelid=\"-1\" HotelName=\"".$physical_locationHotelName."\" LanguageCode=\"".$langTag."\"";
			if($physical_locationPropertyLicenseNumber) {
				$xmlFinal .= " PropertyLicenseNumber=\"".$physical_locationPropertyLicenseNumber."\"";
			}
			$xmlFinal.=" HotelDescriptiveContentNotifType=\"New\">\n";
		}
		else{
			$xmlFinal.="<BCAHotelContent hotelid=\"".$channel['params']['hotelid']."\" HotelName=\"".$hotelName."\" LanguageCode=\"".$langTag."\"";
			if($physical_locationPropertyLicenseNumber) {
				$xmlFinal .= " PropertyLicenseNumber=\"".$physical_locationPropertyLicenseNumber."\"";
			}
			$xmlFinal.=" HotelDescriptiveContentNotifType=\"Overlay\">\n";
		}
		$submittedform = VikRequest::getString('submittedform');
		switch($submittedform){
			case 'contact-info':
				//clear oldData
				unset($oldData['physical_location']);
				unset($oldData['invoices']);
				unset($oldData['general']);
				unset($oldData['contract']);
				unset($oldData['reservations']);
				unset($oldData['availability']);
				unset($oldData['site_content']);
				unset($oldData['parity']);
				unset($oldData['requests']);
				unset($oldData['central_reservations']);
				unset($oldData['latitude']);
				unset($oldData['longitude']);
				unset($oldData['invoicesEmailsIndexes']);
				unset($oldData['invoicesPhonesIndexes']);
				unset($oldData['generalEmailsIndexes']);
				unset($oldData['generalPhonesIndexes']);
				unset($oldData['contractEmailsIndexes']);
				unset($oldData['contractPhonesIndexes']);
				unset($oldData['reservationsEmailsIndexes']);
				unset($oldData['reservationsPhonesIndexes']);
				unset($oldData['availabilityEmailsIndexes']);
				unset($oldData['availabilityPhonesIndexes']);
				unset($oldData['site_contentEmailsIndexes']);
				unset($oldData['site_contentPhonesIndexes']);
				unset($oldData['parityEmailsIndexes']);
				unset($oldData['parityPhonesIndexes']);
				unset($oldData['requestsEmailsIndexes']);
				unset($oldData['requestsPhonesIndexes']);
				unset($oldData['central_reservationsEmailsIndexes']);
				unset($oldData['central_reservationsPhonesIndexes']);
				//

				$contactInfosIndex = 1;

				//get data
				$physical_locationLanguage = htmlentities(VikRequest::getString('vcm-bcah-physical-location-language'));
				$physical_locationCountry = htmlentities(VikRequest::getString('vcm-bcah-address-physical-location-country'));
				$physical_locationCityName = htmlentities(VikRequest::getString('vcm-bcah-address-physical-location-city-name'));
				$physical_locationAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-physical-location-address-line'));
				$physical_locationPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-physical-location-postal-code'));
				$positionLatitude = VikRequest::getFloat('vcm-bcah-position-latitude');
				$positionLongitude = VikRequest::getFloat('vcm-bcah-position-longitude');

				$guestRoomQuantity=htmlentities(VikRequest::getInt('vcm-bcah-guest-room-quantity'));
				$hotelExists='1';
				$hotelType=htmlentities(VikRequest::getString('vcm-bcah-hotel-type'));

				$invoicesPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-invoices-first-name'));
				$invoicesPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-invoices-surname'));
				$invoicesPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-invoices-gender'));
				$invoicesPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-invoices-job-title'));
				$invoicesPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-invoices-language'));
				$invoicesAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-invoices-language'));
				$invoicesAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-invoices-country'));
				$invoicesAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-invoices-city-name'));
				$invoicesAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-invoices-address-line'));
				$invoicesAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-invoices-postal-code'));
				$invoicesEmailIndexes = VikRequest::getVar('vcm-bcah-invoices-email-index',array());
				$invoicesPhoneIndexes = VikRequest::getVar('vcm-bcah-invoices-phone-index',array());

				$generalPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-general-first-name'));
				$generalPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-general-surname'));
				$generalPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-general-gender'));
				$generalPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-general-job-title'));
				$generalPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-general-language'));
				$generalAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-general-language'));
				$generalAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-general-country'));
				$generalAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-general-city-name'));
				$generalAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-general-address-line'));
				$generalAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-general-postal-code'));
				$generalEmailIndexes = VikRequest::getVar('vcm-bcah-general-email-index',array());
				$generalPhoneIndexes = VikRequest::getVar('vcm-bcah-general-phone-index',array());

				$contractPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-contract-first-name'));
				$contractPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-contract-surname'));
				$contractPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-contract-gender'));
				$contractPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-contract-job-title'));
				$contractPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-contract-language'));
				$contractAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-contract-language'));
				$contractAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-contract-country'));
				$contractAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-contract-city-name'));
				$contractAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-contract-address-line'));
				$contractAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-contract-postal-code'));
				$contractEmailIndexes = VikRequest::getVar('vcm-bcah-contract-email-index',array());
				$contractPhoneIndexes = VikRequest::getVar('vcm-bcah-contract-phone-index',array());

				$reservationsPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-reservations-first-name'));
				$reservationsPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-reservations-surname'));
				$reservationsPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-reservations-gender'));
				$reservationsPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-reservations-job-title'));
				$reservationsPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-reservations-language'));
				$reservationsAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-reservations-language'));
				$reservationsAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-reservations-country'));
				$reservationsAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-reservations-city-name'));
				$reservationsAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-reservations-address-line'));
				$reservationsAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-reservations-postal-code'));
				$reservationsEmailIndexes = VikRequest::getVar('vcm-bcah-reservations-email-index',array());
				$reservationsPhoneIndexes = VikRequest::getVar('vcm-bcah-reservations-phone-index',array());

				$availabilityPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-availability-first-name'));
				$availabilityPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-availability-surname'));
				$availabilityPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-availability-gender'));
				$availabilityPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-availability-job-title'));
				$availabilityPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-availability-language'));
				$availabilityAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-availability-language'));
				$availabilityAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-availability-country'));
				$availabilityAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-availability-city-name'));
				$availabilityAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-availability-address-line'));
				$availabilityAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-availability-postal-code'));
				$availabilityEmailIndexes = VikRequest::getVar('vcm-bcah-availability-email-index',array());
				$availabilityPhoneIndexes = VikRequest::getVar('vcm-bcah-availability-phone-index',array());

				$site_contentPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-site_content-first-name'));
				$site_contentPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-site_content-surname'));
				$site_contentPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-site_content-gender'));
				$site_contentPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-site_content-job-title'));
				$site_contentPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-site_content-language'));
				$site_contentAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-site_content-language'));
				$site_contentAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-site_content-country'));
				$site_contentAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-site_content-city-name'));
				$site_contentAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-site_content-address-line'));
				$site_contentAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-site_content-postal-code'));
				$site_contentEmailIndexes = VikRequest::getVar('vcm-bcah-site_content-email-index',array());
				$site_contentPhoneIndexes = VikRequest::getVar('vcm-bcah-site_content-phone-index',array());

				$parityPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-parity-first-name'));
				$parityPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-parity-surname'));
				$parityPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-parity-gender'));
				$parityPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-parity-job-title'));
				$parityPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-parity-language'));
				$parityAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-parity-language'));
				$parityAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-parity-country'));
				$parityAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-parity-city-name'));
				$parityAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-parity-address-line'));
				$parityAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-parity-postal-code'));
				$parityEmailIndexes = VikRequest::getVar('vcm-bcah-parity-email-index',array());
				$parityPhoneIndexes = VikRequest::getVar('vcm-bcah-parity-phone-index',array());

				$requestsPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-requests-first-name'));
				$requestsPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-requests-surname'));
				$requestsPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-requests-gender'));
				$requestsPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-requests-job-title'));
				$requestsPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-requests-language'));
				$requestsAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-requests-language'));
				$requestsAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-requests-country'));
				$requestsAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-requests-city-name'));
				$requestsAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-requests-address-line'));
				$requestsAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-requests-postal-code'));
				$requestsEmailIndexes = VikRequest::getVar('vcm-bcah-requests-email-index',array());
				$requestsPhoneIndexes = VikRequest::getVar('vcm-bcah-requests-phone-index',array());

				$central_reservationsPersonFirstName = htmlentities(VikRequest::getString('vcm-bcah-person-central_reservations-first-name'));
				$central_reservationsPersonSurname = htmlentities(VikRequest::getString('vcm-bcah-person-central_reservations-surname'));
				$central_reservationsPersonGender = htmlentities(VikRequest::getString('vcm-bcah-person-central_reservations-gender'));
				$central_reservationsPersonJobTitle = htmlentities(VikRequest::getString('vcm-bcah-person-central_reservations-job-title'));
				$central_reservationsPersonLanguage = htmlentities(VikRequest::getString('vcm-bcah-person-central_reservations-language'));
				$central_reservationsAddressLanguage = htmlentities(VikRequest::getString('vcm-bcah-address-central_reservations-language'));
				$central_reservationsAddressCountry = htmlentities(VikRequest::getString('vcm-bcah-address-central_reservations-country'));
				$central_reservationsAddressCityName = htmlentities(VikRequest::getString('vcm-bcah-address-central_reservations-city-name'));
				$central_reservationsAddressAddressLine = htmlentities(VikRequest::getString('vcm-bcah-address-central_reservations-address-line'));
				$central_reservationsAddressPostalCode = htmlentities(VikRequest::getString('vcm-bcah-address-central_reservations-postal-code'));
				$central_reservationsEmailIndexes = VikRequest::getVar('vcm-bcah-central_reservations-email-index',array());
				$central_reservationsPhoneIndexes = VikRequest::getVar('vcm-bcah-central_reservations-phone-index',array());
				//

				//make xml
				$xmlFinal.="<ContactInfos>\n";
				if(!empty($physical_locationLanguage)||!empty($physical_locationHotelName)||!empty($physical_locationCountry)||!empty($physical_locationCityName)||!empty($physical_locationAddressLine)||!empty($physical_locationPostalCode)||!empty($positionLatitude)||!empty($positionLongitude)){
					if(!empty($positionLatitude)&&!empty($positionLongitude)){
						$xmlFinal.="<Position Latitude=\"".$positionLatitude."\" Longitude=\"".$positionLongitude."\"/>\n";
						$content_array['latitude']=$positionLatitude;
						$content_array['longitude']=$positionLongitude;
					}
					if(!empty($physical_locationLanguage)||!empty($physical_locationHotelName)||!empty($physical_locationCountry)||!empty($physical_locationCityName)||!empty($physical_locationAddressLine)||!empty($physical_locationPostalCode)){
						$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"PhysicalLocation\">\n<Addresses>\n<Address";
						if(!empty($physical_locationLanguage)){
							$xmlFinal.=" Language=\"".$physical_locationLanguage."\"";
							$content_array['physical_location']['address']['language']=$physical_locationLanguage;
						}
						$xmlFinal.=">\n";
						if(!empty($physical_locationHotelName)){
							if($insertType!="New"){
								$xmlFinal.="<HotelName>".$physical_locationHotelName."</HotelName>\n";
							}
							$content_array['physical_location']['address']['hotelName']=$physical_locationHotelName;
						}
						if(!empty($physical_locationAddressLine)){
							$xmlFinal.="<AddressLine>".$physical_locationAddressLine."</AddressLine>\n";
							$content_array['physical_location']['address']['addressLine']=$physical_locationAddressLine;
						}
						if(!empty($physical_locationCityName)){
							$xmlFinal.="<CityName>".$physical_locationCityName."</CityName>\n";
							$content_array['physical_location']['address']['cityName']=$physical_locationCityName;
						}
						if(!empty($physical_locationCountry)){
							$xmlFinal.="<CountryName>".$physical_locationCountry."</CountryName>\n";
							$content_array['physical_location']['address']['country'] = $physical_locationCountry;
						}
						if(!empty($physical_locationPostalCode)){
							$xmlFinal.="<PostalCode>".$physical_locationPostalCode."</PostalCode>\n";
							$content_array['physical_location']['address']['postalCode'] = $physical_locationPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n</ContactInfo>\n";
					}
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($invoicesPersonFirstName)||!empty($invoicesPersonSurname)||!empty($invoicesPersonGender)||!empty($invoicesPersonJobTitle)||!empty($invoicesPersonLanguage)||!empty($invoicesAddressLanguage)||!empty($invoicesHotelName)||!empty($invoicesCountry)||!empty($invoicesCityName)||!empty($invoicesAddressLine)||!empty($invoicesPostalCode)||!empty($invoicesEmailIndexes)||!empty($invoicesPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"invoices\">\n";
					if(!empty($invoicesPersonLanguage)||!empty($invoicesPersonGender)||!empty($invoicesPersonFirstName)||!empty($invoicesPersonSurname)||!empty($invoicesPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($invoicesPersonLanguage)){
							$xmlFinal.=" Language=\"".$invoicesPersonLanguage."\"";
							$content_array['invoices']['person']['language']=$invoicesPersonLanguage;
						}
						if(!empty($invoicesPersonGender)){
							$xmlFinal.=" Gender=\"".$invoicesPersonGender."\"";
							$content_array['invoices']['person']['gender']=$invoicesPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($invoicesPersonFirstName)){
							$xmlFinal.="<GivenName>".$invoicesPersonFirstName."</GivenName>\n";
							$content_array['invoices']['person']['firstName']=$invoicesPersonFirstName;
						}
						if(!empty($invoicesPersonSurname)){
							$xmlFinal.="<Surname>".$invoicesPersonSurname."</Surname>\n";
							$content_array['invoices']['person']['surname']=$invoicesPersonSurname;
						}
						if(!empty($invoicesPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$invoicesPersonJobTitle."</JobTitle>\n";
							$content_array['invoices']['person']['jobTitle']=$invoicesPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($invoicesAddressLanguage)||!empty($invoicesAddressCountry)||!empty($invoicesAddressCityName)||!empty($invoicesAddressAddressLine)||!empty($invoicesAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($invoicesAddressLanguage)){
							$xmlFinal.=" Language=\"".$invoicesAddressLanguage."\"";
							$content_array['invoices']['address']['language']=$invoicesAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($invoicesAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$invoicesAddressAddressLine."</AddressLine>\n";
							$content_array['invoices']['address']['addressLine']=$invoicesAddressAddressLine;
						}
						if(!empty($invoicesAddressCityName)){
							$xmlFinal.="<CityName>".$invoicesAddressCityName."</CityName>\n";
							$content_array['invoices']['address']['cityName']=$invoicesAddressCityName;
						}
						if(!empty($invoicesAddressCountry)){
							$xmlFinal.="<CountryName>".$invoicesAddressCountry."</CountryName>\n";
							$content_array['invoices']['address']['country']=$invoicesAddressCountry;
						}
						if(!empty($invoicesAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$invoicesAddressPostalCode."</PostalCode>\n";
							$content_array['invoices']['address']['postalCode']=$invoicesAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($invoicesEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($invoicesEmailIndexes as $index) {
							$invoicesEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-invoices-email'.$index.'-email-address'));
							if(!empty($invoicesEmailAddress)){
								$xmlFinal.="<Email>".$invoicesEmailAddress."</Email>\n";
								$content_array['invoicesEmailsIndexes'][]=$index;
								$content_array['invoices']['emails'][$index]['email']=$invoicesEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($invoicesPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($invoicesPhoneIndexes as $index) {
							$invoicesPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-invoices-phone'.$index.'-phone-number'));
							$invoicesPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-invoices-phone'.$index.'-phone-tech-type'));
							$invoicesExtension = htmlentities(VikRequest::getInt('vcm-bcah-invoices-phone'.$index.'-extension'));
							if(!empty($invoicesPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['invoicesPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$invoicesPhoneNumber."\"";
								$content_array['invoices']['phones'][$index]['phoneNumber']=$invoicesPhoneNumber;
								if(!empty($invoicesPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$invoicesPhoneTechType."\"";
									$content_array['invoices']['phones'][$index]['phoneTechType']=$invoicesPhoneTechType;
								}
								if($invoicesExtension!=0){
									$xmlFinal.=" Extension=\"".$invoicesExtension."\"";
									$content_array['invoices']['phones'][$index]['extension']=$invoicesExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($generalPersonFirstName)||!empty($generalPersonSurname)||!empty($generalPersonGender)||!empty($generalPersonJobTitle)||!empty($generalPersonLanguage)||!empty($generalAddressLanguage)||!empty($generalHotelName)||!empty($generalCountry)||!empty($generalCityName)||!empty($generalAddressLine)||!empty($generalPostalCode)||!empty($generalEmailIndexes)||!empty($generalPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"general\">\n";
					if(!empty($generalPersonLanguage)||!empty($generalPersonGender)||!empty($generalPersonFirstName)||!empty($generalPersonSurname)||!empty($generalPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($generalPersonLanguage)){
							$xmlFinal.=" Language=\"".$generalPersonLanguage."\"";
							$content_array['general']['person']['language']=$generalPersonLanguage;
						}
						if(!empty($generalPersonGender)){
							$xmlFinal.=" Gender=\"".$generalPersonGender."\"";
							$content_array['general']['person']['gender']=$generalPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($generalPersonFirstName)){
							$xmlFinal.="<GivenName>".$generalPersonFirstName."</GivenName>\n";
							$content_array['general']['person']['firstName']=$generalPersonFirstName;
						}
						if(!empty($generalPersonSurname)){
							$xmlFinal.="<Surname>".$generalPersonSurname."</Surname>\n";
							$content_array['general']['person']['surname']=$generalPersonSurname;
						}
						if(!empty($generalPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$generalPersonJobTitle."</JobTitle>\n";
							$content_array['general']['person']['jobTitle']=$generalPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($generalAddressLanguage)||!empty($generalAddressCountry)||!empty($generalAddressCityName)||!empty($generalAddressAddressLine)||!empty($generalAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($generalAddressLanguage)){
							$xmlFinal.=" Language=\"".$generalAddressLanguage."\"";
							$content_array['general']['address']['language']=$generalAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($generalAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$generalAddressAddressLine."</AddressLine>\n";
							$content_array['general']['address']['addressLine']=$generalAddressAddressLine;
						}
						if(!empty($generalAddressCityName)){
							$xmlFinal.="<CityName>".$generalAddressCityName."</CityName>\n";
							$content_array['general']['address']['cityName']=$generalAddressCityName;
						}
						if(!empty($generalAddressCountry)){
							$xmlFinal.="<CountryName>".$generalAddressCountry."</CountryName>\n";
							$content_array['general']['address']['country']=$generalAddressCountry;
						}
						if(!empty($generalAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$generalAddressPostalCode."</PostalCode>\n";
							$content_array['general']['address']['postalCode']=$generalAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($generalEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($generalEmailIndexes as $index) {
							$generalEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-general-email'.$index.'-email-address'));
							if(!empty($generalEmailAddress)){
								$xmlFinal.="<Email>".$generalEmailAddress."</Email>\n";
								$content_array['generalEmailsIndexes'][]=$index;
								$content_array['general']['emails'][$index]['email']=$generalEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($generalPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($generalPhoneIndexes as $index) {
							$generalPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-general-phone'.$index.'-phone-number'));
							$generalPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-general-phone'.$index.'-phone-tech-type'));
							$generalExtension = htmlentities(VikRequest::getInt('vcm-bcah-general-phone'.$index.'-extension'));
							if(!empty($generalPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['generalPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$generalPhoneNumber."\"";
								$content_array['general']['phones'][$index]['phoneNumber']=$generalPhoneNumber;
								if(!empty($generalPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$generalPhoneTechType."\"";
									$content_array['general']['phones'][$index]['phoneTechType']=$generalPhoneTechType;
								}
								if($generalExtension!=0){
									$xmlFinal.=" Extension=\"".$generalExtension."\"";
									$content_array['general']['phones'][$index]['extension']=$generalExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($contractPersonFirstName)||!empty($contractPersonSurname)||!empty($contractPersonGender)||!empty($contractPersonJobTitle)||!empty($contractPersonLanguage)||!empty($contractAddressLanguage)||!empty($contractHotelName)||!empty($contractCountry)||!empty($contractCityName)||!empty($contractAddressLine)||!empty($contractPostalCode)||!empty($contractEmailIndexes)||!empty($contractPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"contract\">\n";
					if(!empty($contractPersonLanguage)||!empty($contractPersonGender)||!empty($contractPersonFirstName)||!empty($contractPersonSurname)||!empty($contractPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($contractPersonLanguage)){
							$xmlFinal.=" Language=\"".$contractPersonLanguage."\"";
							$content_array['contract']['person']['language']=$contractPersonLanguage;
						}
						if(!empty($contractPersonGender)){
							$xmlFinal.=" Gender=\"".$contractPersonGender."\"";
							$content_array['contract']['person']['gender']=$contractPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($contractPersonFirstName)){
							$xmlFinal.="<GivenName>".$contractPersonFirstName."</GivenName>\n";
							$content_array['contract']['person']['firstName']=$contractPersonFirstName;
						}
						if(!empty($contractPersonSurname)){
							$xmlFinal.="<Surname>".$contractPersonSurname."</Surname>\n";
							$content_array['contract']['person']['surname']=$contractPersonSurname;
						}
						if(!empty($contractPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$contractPersonJobTitle."</JobTitle>\n";
							$content_array['contract']['person']['jobTitle']=$contractPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($contractAddressLanguage)||!empty($contractAddressCountry)||!empty($contractAddressCityName)||!empty($contractAddressAddressLine)||!empty($contractAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($contractAddressLanguage)){
							$xmlFinal.=" Language=\"".$contractAddressLanguage."\"";
							$content_array['contract']['address']['language']=$contractAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($contractAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$contractAddressAddressLine."</AddressLine>\n";
							$content_array['contract']['address']['addressLine']=$contractAddressAddressLine;
						}
						if(!empty($contractAddressCityName)){
							$xmlFinal.="<CityName>".$contractAddressCityName."</CityName>\n";
							$content_array['contract']['address']['cityName']=$contractAddressCityName;
						}
						if(!empty($contractAddressCountry)){
							$xmlFinal.="<CountryName>".$contractAddressCountry."</CountryName>\n";
							$content_array['contract']['address']['country']=$contractAddressCountry;
						}
						if(!empty($contractAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$contractAddressPostalCode."</PostalCode>\n";
							$content_array['contract']['address']['postalCode']=$contractAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($contractEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($contractEmailIndexes as $index) {
							$contractEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-contract-email'.$index.'-email-address'));
							if(!empty($contractEmailAddress)){
								$xmlFinal.="<Email>".$contractEmailAddress."</Email>\n";
								$content_array['contractEmailsIndexes'][]=$index;
								$content_array['contract']['emails'][$index]['email']=$contractEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($contractPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($contractPhoneIndexes as $index) {
							$contractPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-contract-phone'.$index.'-phone-number'));
							$contractPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-contract-phone'.$index.'-phone-tech-type'));
							$contractExtension = htmlentities(VikRequest::getInt('vcm-bcah-contract-phone'.$index.'-extension'));
							if(!empty($contractPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['contractPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$contractPhoneNumber."\"";
								$content_array['contract']['phones'][$index]['phoneNumber']=$contractPhoneNumber;
								if(!empty($contractPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$contractPhoneTechType."\"";
									$content_array['contract']['phones'][$index]['phoneTechType']=$contractPhoneTechType;
								}
								if($contractExtension!=0){
									$xmlFinal.=" Extension=\"".$contractExtension."\"";
									$content_array['contract']['phones'][$index]['extension']=$contractExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($reservationsPersonFirstName)||!empty($reservationsPersonSurname)||!empty($reservationsPersonGender)||!empty($reservationsPersonJobTitle)||!empty($reservationsPersonLanguage)||!empty($reservationsAddressLanguage)||!empty($reservationsHotelName)||!empty($reservationsCountry)||!empty($reservationsCityName)||!empty($reservationsAddressLine)||!empty($reservationsPostalCode)||!empty($reservationsEmailIndexes)||!empty($reservationsPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"reservations\">\n";
					if(!empty($reservationsPersonLanguage)||!empty($reservationsPersonGender)||!empty($reservationsPersonFirstName)||!empty($reservationsPersonSurname)||!empty($reservationsPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($reservationsPersonLanguage)){
							$xmlFinal.=" Language=\"".$reservationsPersonLanguage."\"";
							$content_array['reservations']['person']['language']=$reservationsPersonLanguage;
						}
						if(!empty($reservationsPersonGender)){
							$xmlFinal.=" Gender=\"".$reservationsPersonGender."\"";
							$content_array['reservations']['person']['gender']=$reservationsPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($reservationsPersonFirstName)){
							$xmlFinal.="<GivenName>".$reservationsPersonFirstName."</GivenName>\n";
							$content_array['reservations']['person']['firstName']=$reservationsPersonFirstName;
						}
						if(!empty($reservationsPersonSurname)){
							$xmlFinal.="<Surname>".$reservationsPersonSurname."</Surname>\n";
							$content_array['reservations']['person']['surname']=$reservationsPersonSurname;
						}
						if(!empty($reservationsPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$reservationsPersonJobTitle."</JobTitle>\n";
							$content_array['reservations']['person']['jobTitle']=$reservationsPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($reservationsAddressLanguage)||!empty($reservationsAddressCountry)||!empty($reservationsAddressCityName)||!empty($reservationsAddressAddressLine)||!empty($reservationsAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($reservationsAddressLanguage)){
							$xmlFinal.=" Language=\"".$reservationsAddressLanguage."\"";
							$content_array['reservations']['address']['language']=$reservationsAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($reservationsAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$reservationsAddressAddressLine."</AddressLine>\n";
							$content_array['reservations']['address']['addressLine']=$reservationsAddressAddressLine;
						}
						if(!empty($reservationsAddressCityName)){
							$xmlFinal.="<CityName>".$reservationsAddressCityName."</CityName>\n";
							$content_array['reservations']['address']['cityName']=$reservationsAddressCityName;
						}
						if(!empty($reservationsAddressCountry)){
							$xmlFinal.="<CountryName>".$reservationsAddressCountry."</CountryName>\n";
							$content_array['reservations']['address']['country']=$reservationsAddressCountry;
						}
						if(!empty($reservationsAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$reservationsAddressPostalCode."</PostalCode>\n";
							$content_array['reservations']['address']['postalCode']=$reservationsAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($reservationsEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($reservationsEmailIndexes as $index) {
							$reservationsEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-reservations-email'.$index.'-email-address'));
							if(!empty($reservationsEmailAddress)){
								$xmlFinal.="<Email>".$reservationsEmailAddress."</Email>\n";
								$content_array['reservationsEmailsIndexes'][]=$index;
								$content_array['reservations']['emails'][$index]['email']=$reservationsEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($reservationsPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($reservationsPhoneIndexes as $index) {
							$reservationsPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-reservations-phone'.$index.'-phone-number'));
							$reservationsPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-reservations-phone'.$index.'-phone-tech-type'));
							$reservationsExtension = htmlentities(VikRequest::getInt('vcm-bcah-reservations-phone'.$index.'-extension'));
							if(!empty($reservationsPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['reservationsPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$reservationsPhoneNumber."\"";
								$content_array['reservations']['phones'][$index]['phoneNumber']=$reservationsPhoneNumber;
								if(!empty($reservationsPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$reservationsPhoneTechType."\"";
									$content_array['reservations']['phones'][$index]['phoneTechType']=$reservationsPhoneTechType;
								}
								if($reservationsExtension!=0){
									$xmlFinal.=" Extension=\"".$reservationsExtension."\"";
									$content_array['reservations']['phones'][$index]['extension']=$reservationsExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($availabilityPersonFirstName)||!empty($availabilityPersonSurname)||!empty($availabilityPersonGender)||!empty($availabilityPersonJobTitle)||!empty($availabilityPersonLanguage)||!empty($availabilityAddressLanguage)||!empty($availabilityHotelName)||!empty($availabilityCountry)||!empty($availabilityCityName)||!empty($availabilityAddressLine)||!empty($availabilityPostalCode)||!empty($availabilityEmailIndexes)||!empty($availabilityPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"availability\">\n";
					if(!empty($availabilityPersonLanguage)||!empty($availabilityPersonGender)||!empty($availabilityPersonFirstName)||!empty($availabilityPersonSurname)||!empty($availabilityPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($availabilityPersonLanguage)){
							$xmlFinal.=" Language=\"".$availabilityPersonLanguage."\"";
							$content_array['availability']['person']['language']=$availabilityPersonLanguage;
						}
						if(!empty($availabilityPersonGender)){
							$xmlFinal.=" Gender=\"".$availabilityPersonGender."\"";
							$content_array['availability']['person']['gender']=$availabilityPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($availabilityPersonFirstName)){
							$xmlFinal.="<GivenName>".$availabilityPersonFirstName."</GivenName>\n";
							$content_array['availability']['person']['firstName']=$availabilityPersonFirstName;
						}
						if(!empty($availabilityPersonSurname)){
							$xmlFinal.="<Surname>".$availabilityPersonSurname."</Surname>\n";
							$content_array['availability']['person']['surname']=$availabilityPersonSurname;
						}
						if(!empty($availabilityPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$availabilityPersonJobTitle."</JobTitle>\n";
							$content_array['availability']['person']['jobTitle']=$availabilityPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($availabilityAddressLanguage)||!empty($availabilityAddressCountry)||!empty($availabilityAddressCityName)||!empty($availabilityAddressAddressLine)||!empty($availabilityAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($availabilityAddressLanguage)){
							$xmlFinal.=" Language=\"".$availabilityAddressLanguage."\"";
							$content_array['availability']['address']['language']=$availabilityAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($availabilityAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$availabilityAddressAddressLine."</AddressLine>\n";
							$content_array['availability']['address']['addressLine']=$availabilityAddressAddressLine;
						}
						if(!empty($availabilityAddressCityName)){
							$xmlFinal.="<CityName>".$availabilityAddressCityName."</CityName>\n";
							$content_array['availability']['address']['cityName']=$availabilityAddressCityName;
						}
						if(!empty($availabilityAddressCountry)){
							$xmlFinal.="<CountryName>".$availabilityAddressCountry."</CountryName>\n";
							$content_array['availability']['address']['country']=$availabilityAddressCountry;
						}
						if(!empty($availabilityAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$availabilityAddressPostalCode."</PostalCode>\n";
							$content_array['availability']['address']['postalCode']=$availabilityAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($availabilityEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($availabilityEmailIndexes as $index) {
							$availabilityEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-availability-email'.$index.'-email-address'));
							if(!empty($availabilityEmailAddress)){
								$xmlFinal.="<Email>".$availabilityEmailAddress."</Email>\n";
								$content_array['availabilityEmailsIndexes'][]=$index;
								$content_array['availability']['emails'][$index]['email']=$availabilityEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($availabilityPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($availabilityPhoneIndexes as $index) {
							$availabilityPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-availability-phone'.$index.'-phone-number'));
							$availabilityPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-availability-phone'.$index.'-phone-tech-type'));
							$availabilityExtension = htmlentities(VikRequest::getInt('vcm-bcah-availability-phone'.$index.'-extension'));
							if(!empty($availabilityPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['availabilityPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$availabilityPhoneNumber."\"";
								$content_array['availability']['phones'][$index]['phoneNumber']=$availabilityPhoneNumber;
								if(!empty($availabilityPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$availabilityPhoneTechType."\"";
									$content_array['availability']['phones'][$index]['phoneTechType']=$availabilityPhoneTechType;
								}
								if($availabilityExtension!=0){
									$xmlFinal.=" Extension=\"".$availabilityExtension."\"";
									$content_array['availability']['phones'][$index]['extension']=$availabilityExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($site_contentPersonFirstName)||!empty($site_contentPersonSurname)||!empty($site_contentPersonGender)||!empty($site_contentPersonJobTitle)||!empty($site_contentPersonLanguage)||!empty($site_contentAddressLanguage)||!empty($site_contentHotelName)||!empty($site_contentCountry)||!empty($site_contentCityName)||!empty($site_contentAddressLine)||!empty($site_contentPostalCode)||!empty($site_contentEmailIndexes)||!empty($site_contentPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"site_content\">\n";
					if(!empty($site_contentPersonLanguage)||!empty($site_contentPersonGender)||!empty($site_contentPersonFirstName)||!empty($site_contentPersonSurname)||!empty($site_contentPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($site_contentPersonLanguage)){
							$xmlFinal.=" Language=\"".$site_contentPersonLanguage."\"";
							$content_array['site_content']['person']['language']=$site_contentPersonLanguage;
						}
						if(!empty($site_contentPersonGender)){
							$xmlFinal.=" Gender=\"".$site_contentPersonGender."\"";
							$content_array['site_content']['person']['gender']=$site_contentPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($site_contentPersonFirstName)){
							$xmlFinal.="<GivenName>".$site_contentPersonFirstName."</GivenName>\n";
							$content_array['site_content']['person']['firstName']=$site_contentPersonFirstName;
						}
						if(!empty($site_contentPersonSurname)){
							$xmlFinal.="<Surname>".$site_contentPersonSurname."</Surname>\n";
							$content_array['site_content']['person']['surname']=$site_contentPersonSurname;
						}
						if(!empty($site_contentPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$site_contentPersonJobTitle."</JobTitle>\n";
							$content_array['site_content']['person']['jobTitle']=$site_contentPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($site_contentAddressLanguage)||!empty($site_contentAddressCountry)||!empty($site_contentAddressCityName)||!empty($site_contentAddressAddressLine)||!empty($site_contentAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($site_contentAddressLanguage)){
							$xmlFinal.=" Language=\"".$site_contentAddressLanguage."\"";
							$content_array['site_content']['address']['language']=$site_contentAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($site_contentAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$site_contentAddressAddressLine."</AddressLine>\n";
							$content_array['site_content']['address']['addressLine']=$site_contentAddressAddressLine;
						}
						if(!empty($site_contentAddressCityName)){
							$xmlFinal.="<CityName>".$site_contentAddressCityName."</CityName>\n";
							$content_array['site_content']['address']['cityName']=$site_contentAddressCityName;
						}
						if(!empty($site_contentAddressCountry)){
							$xmlFinal.="<CountryName>".$site_contentAddressCountry."</CountryName>\n";
							$content_array['site_content']['address']['country']=$site_contentAddressCountry;
						}
						if(!empty($site_contentAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$site_contentAddressPostalCode."</PostalCode>\n";
							$content_array['site_content']['address']['postalCode']=$site_contentAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($site_contentEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($site_contentEmailIndexes as $index) {
							$site_contentEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-site_content-email'.$index.'-email-address'));
							if(!empty($site_contentEmailAddress)){
								$xmlFinal.="<Email>".$site_contentEmailAddress."</Email>\n";
								$content_array['site_contentEmailsIndexes'][]=$index;
								$content_array['site_content']['emails'][$index]['email']=$site_contentEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($site_contentPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($site_contentPhoneIndexes as $index) {
							$site_contentPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-site_content-phone'.$index.'-phone-number'));
							$site_contentPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-site_content-phone'.$index.'-phone-tech-type'));
							$site_contentExtension = htmlentities(VikRequest::getInt('vcm-bcah-site_content-phone'.$index.'-extension'));
							if(!empty($site_contentPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['site_contentPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$site_contentPhoneNumber."\"";
								$content_array['site_content']['phones'][$index]['phoneNumber']=$site_contentPhoneNumber;
								if(!empty($site_contentPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$site_contentPhoneTechType."\"";
									$content_array['site_content']['phones'][$index]['phoneTechType']=$site_contentPhoneTechType;
								}
								if($site_contentExtension!=0){
									$xmlFinal.=" Extension=\"".$site_contentExtension."\"";
									$content_array['site_content']['phones'][$index]['extension']=$site_contentExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($parityPersonFirstName)||!empty($parityPersonSurname)||!empty($parityPersonGender)||!empty($parityPersonJobTitle)||!empty($parityPersonLanguage)||!empty($parityAddressLanguage)||!empty($parityHotelName)||!empty($parityCountry)||!empty($parityCityName)||!empty($parityAddressLine)||!empty($parityPostalCode)||!empty($parityEmailIndexes)||!empty($parityPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"parity\">\n";
					if(!empty($parityPersonLanguage)||!empty($parityPersonGender)||!empty($parityPersonFirstName)||!empty($parityPersonSurname)||!empty($parityPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($parityPersonLanguage)){
							$xmlFinal.=" Language=\"".$parityPersonLanguage."\"";
							$content_array['parity']['person']['language']=$parityPersonLanguage;
						}
						if(!empty($parityPersonGender)){
							$xmlFinal.=" Gender=\"".$parityPersonGender."\"";
							$content_array['parity']['person']['gender']=$parityPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($parityPersonFirstName)){
							$xmlFinal.="<GivenName>".$parityPersonFirstName."</GivenName>\n";
							$content_array['parity']['person']['firstName']=$parityPersonFirstName;
						}
						if(!empty($parityPersonSurname)){
							$xmlFinal.="<Surname>".$parityPersonSurname."</Surname>\n";
							$content_array['parity']['person']['surname']=$parityPersonSurname;
						}
						if(!empty($parityPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$parityPersonJobTitle."</JobTitle>\n";
							$content_array['parity']['person']['jobTitle']=$parityPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($parityAddressLanguage)||!empty($parityAddressCountry)||!empty($parityAddressCityName)||!empty($parityAddressAddressLine)||!empty($parityAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($parityAddressLanguage)){
							$xmlFinal.=" Language=\"".$parityAddressLanguage."\"";
							$content_array['parity']['address']['language']=$parityAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($parityAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$parityAddressAddressLine."</AddressLine>\n";
							$content_array['parity']['address']['addressLine']=$parityAddressAddressLine;
						}
						if(!empty($parityAddressCityName)){
							$xmlFinal.="<CityName>".$parityAddressCityName."</CityName>\n";
							$content_array['parity']['address']['cityName']=$parityAddressCityName;
						}
						if(!empty($parityAddressCountry)){
							$xmlFinal.="<CountryName>".$parityAddressCountry."</CountryName>\n";
							$content_array['parity']['address']['country']=$parityAddressCountry;
						}
						if(!empty($parityAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$parityAddressPostalCode."</PostalCode>\n";
							$content_array['parity']['address']['postalCode']=$parityAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($parityEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($parityEmailIndexes as $index) {
							$parityEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-parity-email'.$index.'-email-address'));
							if(!empty($parityEmailAddress)){
								$xmlFinal.="<Email>".$parityEmailAddress."</Email>\n";
								$content_array['parityEmailsIndexes'][]=$index;
								$content_array['parity']['emails'][$index]['email']=$parityEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($parityPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($parityPhoneIndexes as $index) {
							$parityPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-parity-phone'.$index.'-phone-number'));
							$parityPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-parity-phone'.$index.'-phone-tech-type'));
							$parityExtension = htmlentities(VikRequest::getInt('vcm-bcah-parity-phone'.$index.'-extension'));
							if(!empty($parityPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['parityPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$parityPhoneNumber."\"";
								$content_array['parity']['phones'][$index]['phoneNumber']=$parityPhoneNumber;
								if(!empty($parityPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$parityPhoneTechType."\"";
									$content_array['parity']['phones'][$index]['phoneTechType']=$parityPhoneTechType;
								}
								if($parityExtension!=0){
									$xmlFinal.=" Extension=\"".$parityExtension."\"";
									$content_array['parity']['phones'][$index]['extension']=$parityExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($requestsPersonFirstName)||!empty($requestsPersonSurname)||!empty($requestsPersonGender)||!empty($requestsPersonJobTitle)||!empty($requestsPersonLanguage)||!empty($requestsAddressLanguage)||!empty($requestsHotelName)||!empty($requestsCountry)||!empty($requestsCityName)||!empty($requestsAddressLine)||!empty($requestsPostalCode)||!empty($requestsEmailIndexes)||!empty($requestsPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"requests\">\n";
					if(!empty($requestsPersonLanguage)||!empty($requestsPersonGender)||!empty($requestsPersonFirstName)||!empty($requestsPersonSurname)||!empty($requestsPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($requestsPersonLanguage)){
							$xmlFinal.=" Language=\"".$requestsPersonLanguage."\"";
							$content_array['requests']['person']['language']=$requestsPersonLanguage;
						}
						if(!empty($requestsPersonGender)){
							$xmlFinal.=" Gender=\"".$requestsPersonGender."\"";
							$content_array['requests']['person']['gender']=$requestsPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($requestsPersonFirstName)){
							$xmlFinal.="<GivenName>".$requestsPersonFirstName."</GivenName>\n";
							$content_array['requests']['person']['firstName']=$requestsPersonFirstName;
						}
						if(!empty($requestsPersonSurname)){
							$xmlFinal.="<Surname>".$requestsPersonSurname."</Surname>\n";
							$content_array['requests']['person']['surname']=$requestsPersonSurname;
						}
						if(!empty($requestsPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$requestsPersonJobTitle."</JobTitle>\n";
							$content_array['requests']['person']['jobTitle']=$requestsPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($requestsAddressLanguage)||!empty($requestsAddressCountry)||!empty($requestsAddressCityName)||!empty($requestsAddressAddressLine)||!empty($requestsAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($requestsAddressLanguage)){
							$xmlFinal.=" Language=\"".$requestsAddressLanguage."\"";
							$content_array['requests']['address']['language']=$requestsAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($requestsAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$requestsAddressAddressLine."</AddressLine>\n";
							$content_array['requests']['address']['addressLine']=$requestsAddressAddressLine;
						}
						if(!empty($requestsAddressCityName)){
							$xmlFinal.="<CityName>".$requestsAddressCityName."</CityName>\n";
							$content_array['requests']['address']['cityName']=$requestsAddressCityName;
						}
						if(!empty($requestsAddressCountry)){
							$xmlFinal.="<CountryName>".$requestsAddressCountry."</CountryName>\n";
							$content_array['requests']['address']['country']=$requestsAddressCountry;
						}
						if(!empty($requestsAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$requestsAddressPostalCode."</PostalCode>\n";
							$content_array['requests']['address']['postalCode']=$requestsAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($requestsEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($requestsEmailIndexes as $index) {
							$requestsEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-requests-email'.$index.'-email-address'));
							if(!empty($requestsEmailAddress)){
								$xmlFinal.="<Email>".$requestsEmailAddress."</Email>\n";
								$content_array['requestsEmailsIndexes'][]=$index;
								$content_array['requests']['emails'][$index]['email']=$requestsEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($requestsPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($requestsPhoneIndexes as $index) {
							$requestsPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-requests-phone'.$index.'-phone-number'));
							$requestsPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-requests-phone'.$index.'-phone-tech-type'));
							$requestsExtension = htmlentities(VikRequest::getInt('vcm-bcah-requests-phone'.$index.'-extension'));
							if(!empty($requestsPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['requestsPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$requestsPhoneNumber."\"";
								$content_array['requests']['phones'][$index]['phoneNumber']=$requestsPhoneNumber;
								if(!empty($requestsPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$requestsPhoneTechType."\"";
									$content_array['requests']['phones'][$index]['phoneTechType']=$requestsPhoneTechType;
								}
								if($requestsExtension!=0){
									$xmlFinal.=" Extension=\"".$requestsExtension."\"";
									$content_array['requests']['phones'][$index]['extension']=$requestsExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				if(!empty($central_reservationsPersonFirstName)||!empty($central_reservationsPersonSurname)||!empty($central_reservationsPersonGender)||!empty($central_reservationsPersonJobTitle)||!empty($central_reservationsPersonLanguage)||!empty($central_reservationsAddressLanguage)||!empty($central_reservationsHotelName)||!empty($central_reservationsCountry)||!empty($central_reservationsCityName)||!empty($central_reservationsAddressLine)||!empty($central_reservationsPostalCode)||!empty($central_reservationsEmailIndexes)||!empty($central_reservationsPhoneIndexes)){
					$xmlFinal.="<ContactInfo Index=\"".$contactInfosIndex."\" ContactProfileType=\"central_reservations\">\n";
					if(!empty($central_reservationsPersonLanguage)||!empty($central_reservationsPersonGender)||!empty($central_reservationsPersonFirstName)||!empty($central_reservationsPersonSurname)||!empty($central_reservationsPersonJobTitle)){
						$xmlFinal.="<Names>\n<Name";
						if(!empty($central_reservationsPersonLanguage)){
							$xmlFinal.=" Language=\"".$central_reservationsPersonLanguage."\"";
							$content_array['central_reservations']['person']['language']=$central_reservationsPersonLanguage;
						}
						if(!empty($central_reservationsPersonGender)){
							$xmlFinal.=" Gender=\"".$central_reservationsPersonGender."\"";
							$content_array['central_reservations']['person']['gender']=$central_reservationsPersonGender;
						}
						$xmlFinal.=">";
						if(!empty($central_reservationsPersonFirstName)){
							$xmlFinal.="<GivenName>".$central_reservationsPersonFirstName."</GivenName>\n";
							$content_array['central_reservations']['person']['firstName']=$central_reservationsPersonFirstName;
						}
						if(!empty($central_reservationsPersonSurname)){
							$xmlFinal.="<Surname>".$central_reservationsPersonSurname."</Surname>\n";
							$content_array['central_reservations']['person']['surname']=$central_reservationsPersonSurname;
						}
						if(!empty($central_reservationsPersonJobTitle)){
							$xmlFinal.="<JobTitle>".$central_reservationsPersonJobTitle."</JobTitle>\n";
							$content_array['central_reservations']['person']['jobTitle']=$central_reservationsPersonJobTitle;
						}
						$xmlFinal.="</Name>\n</Names>\n";
					}
					if(!empty($central_reservationsAddressLanguage)||!empty($central_reservationsAddressCountry)||!empty($central_reservationsAddressCityName)||!empty($central_reservationsAddressAddressLine)||!empty($central_reservationsAddressPostalCode)){
						$xmlFinal.="<Addresses>\n<Address";
						if(!empty($central_reservationsAddressLanguage)){
							$xmlFinal.=" Language=\"".$central_reservationsAddressLanguage."\"";
							$content_array['central_reservations']['address']['language']=$central_reservationsAddressLanguage;
						}
						$xmlFinal.=">";
						if(!empty($central_reservationsAddressAddressLine)){
							$xmlFinal.="<AddressLine>".$central_reservationsAddressAddressLine."</AddressLine>\n";
							$content_array['central_reservations']['address']['addressLine']=$central_reservationsAddressAddressLine;
						}
						if(!empty($central_reservationsAddressCityName)){
							$xmlFinal.="<CityName>".$central_reservationsAddressCityName."</CityName>\n";
							$content_array['central_reservations']['address']['cityName']=$central_reservationsAddressCityName;
						}
						if(!empty($central_reservationsAddressCountry)){
							$xmlFinal.="<CountryName>".$central_reservationsAddressCountry."</CountryName>\n";
							$content_array['central_reservations']['address']['country']=$central_reservationsAddressCountry;
						}
						if(!empty($central_reservationsAddressPostalCode)){
							$xmlFinal.="<PostalCode>".$central_reservationsAddressPostalCode."</PostalCode>\n";
							$content_array['central_reservations']['address']['postalCode']=$central_reservationsAddressPostalCode;
						}
						$xmlFinal.="</Address>\n</Addresses>\n";
					}
					if(!empty($central_reservationsEmailIndexes)){
						$xmlFinal.="<Emails>\n";
						foreach ($central_reservationsEmailIndexes as $index) {
							$central_reservationsEmailAddress = htmlentities(VikRequest::getString('vcm-bcah-central_reservations-email'.$index.'-email-address'));
							if(!empty($central_reservationsEmailAddress)){
								$xmlFinal.="<Email>".$central_reservationsEmailAddress."</Email>\n";
								$content_array['central_reservationsEmailsIndexes'][]=$index;
								$content_array['central_reservations']['emails'][$index]['email']=$central_reservationsEmailAddress;
							}
						}
						$xmlFinal.="</Emails>\n";
					}
					if(!empty($central_reservationsPhoneIndexes)){
						$xmlFinal.="<Phones>\n";
						foreach ($central_reservationsPhoneIndexes as $index) {
							$central_reservationsPhoneNumber = htmlentities(VikRequest::getString('vcm-bcah-central_reservations-phone'.$index.'-phone-number'));
							$central_reservationsPhoneTechType = htmlentities(VikRequest::getString('vcm-bcah-central_reservations-phone'.$index.'-phone-tech-type'));
							$central_reservationsExtension = htmlentities(VikRequest::getInt('vcm-bcah-central_reservations-phone'.$index.'-extension'));
							if(!empty($central_reservationsPhoneNumber)){
								$xmlFinal.="<Phone";
								$content_array['central_reservationsPhonesIndexes'][]=$index;
								$xmlFinal.=" PhoneNumber=\"".$central_reservationsPhoneNumber."\"";
								$content_array['central_reservations']['phones'][$index]['phoneNumber']=$central_reservationsPhoneNumber;
								if(!empty($central_reservationsPhoneTechType)){
									$xmlFinal.=" PhoneTechType=\"".$central_reservationsPhoneTechType."\"";
									$content_array['central_reservations']['phones'][$index]['phoneTechType']=$central_reservationsPhoneTechType;
								}
								if($central_reservationsExtension!=0){
									$xmlFinal.=" Extension=\"".$central_reservationsExtension."\"";
									$content_array['central_reservations']['phones'][$index]['extension']=$central_reservationsExtension;
								}
								$xmlFinal.="/>";
							}
						}
						$xmlFinal.="</Phones>\n";
					}
					$xmlFinal.="</ContactInfo>\n";
					$contactInfosIndex++;
				}
				else{
					$noValueCount++;
				}
				$xmlFinal.="</ContactInfos>\n";
				if ($insertType=="New") {
					$xmlFinal.="<HotelInfo>\n";
					$xmlFinal.="	<CategoryCodes>\n";
          			$xmlFinal.="		<GuestRoomInfo Quantity=\"".$guestRoomQuantity."\"/>\n";
         			$xmlFinal.="		<HotelCategory ExistsCode=\"".$hotelExists."\" Code=\"".$hotelType."\"/>\n";
        			$xmlFinal.="	</CategoryCodes>\n";
        			$xmlFinal.="	<Position Latitude=\"".$positionLatitude."\" Longitude=\"".$positionLongitude."\"/>\n";
					$xmlFinal.="</HotelInfo>\n";
				}
				$errorLimit=10;

				//
				break;
			case 'hotel-info':

				//clear oldData
				unset($oldData['guestRoomQuantity']);
				unset($oldData['hotelExists']);
				unset($oldData['hotelType']);
				unset($oldData['hotelInfoMessage']);
				unset($oldData['servicesIndexes']);
				unset($oldData['services']);
				unset($oldData['paymentmethodsIndexes']);
				unset($oldData['paymentmethods']);
				//

				//get data
				$guestRoomQuantity=htmlentities(VikRequest::getInt('vcm-bcah-guest-room-quantity'));
				$hotelExists='1';
				$hotelType=htmlentities(VikRequest::getString('vcm-bcah-hotel-type'));
				$hotelInfoMessage=htmlentities(VikRequest::getString('vcm-bcah-hotel-info-details'));
				$languageIndexes=VikRequest::getVar('vcm-bcah-language-index', array());
				$serviceIndexes=VikRequest::getVar('vcm-bcah-service-index', array());
				$paymentmethodIndexes=VikRequest::getVar('vcm-bcah-paymentmethod-index', array());
				//

				//save data
				//

				//create xml
				$xmlFinal.="<HotelInfo>\n";
				if($guestRoomQuantity!=0||!empty($hotelExists)||$hotelType!=""){
					$xmlFinal.="<CategoryCodes>\n";
					if($guestRoomQuantity!=0){
						$content_array['guestRoomQuantity']=$guestRoomQuantity;
						$xmlFinal.="<GuestRoomInfo Quantity=\"".$guestRoomQuantity."\"/>\n";
					}
					else{
						$noValueCount++;
					}
					if($hotelType!=""){
						$xmlFinal.="<HotelCategory ";
						$xmlFinal.="ExistsCode=\"".$hotelExists."\" ";
						$content_array['hotelExists']=$hotelExists;
						$xmlFinal.="Code=\"".$hotelType."\" ";
						$content_array['hotelType']= $hotelType;
						$xmlFinal.="/>\n";
					}
					else{
						$noValueCount++;
					}
					$xmlFinal.="</CategoryCodes>\n";
				}
				if(!empty($languageIndexes)){
					$xmlFinal.="<Languages>\n";
					foreach ($languageIndexes as $index) {
						$language = htmlentities(VikRequest::getString('vcm-bcah-language'.$index.'-selected-language'));
						if($language!=""){
							$content_array['languagesIndexes'][] = $index;
							$content_array['languages'][$index]['language']=$language;
							$xmlFinal.="<Language LanguageCode=\"".$language."\"/>\n"; 
						}
					}
					$xmlFinal.="</Languages>\n";
				}
				else{
					$noValueCount++;
				}
				if(!empty($serviceIndexes)){
					$xmlFinal.="<Services>\n";
					foreach ($serviceIndexes as $index) {
						$code = htmlentities(VikRequest::getString('vcm-bcah-service'.$index.'-selected-service'));
						$included = (htmlentities(VikRequest::getString('vcm-bcah-service'.$index.'-included'))=='on')? 'true':'false';
						$price = htmlentities(VikRequest::getString('vcm-bcah-service'.$index.'-price'));
						$breakfastType = VikRequest::getVar('vcm-bcah-service'.$index.'-breakfast-type', array());
						if($code!=""||$price!=""){
							$content_array['servicesIndexes'][] = $index;
							$xmlFinal.="<Service ";
							if($code!=""){
								$content_array['services'][$index]['code']=$code;
								$xmlFinal.="Code=\"".$code."\" ";	
							}
							if($included!="false"){
								$xmlFinal.="Included=\"".$included."\" ";
								$content_array['services'][$index]['included']=$included;
							}
							if($price!=""){
								$xmlFinal.="Price=\"".$price."\" ";
								$content_array['services'][$index]['price']=$price;
							}
							$xmlFinal.=">\n";
							if(!empty($breakfastType)){
								$xmlFinal.="<Types>\n";
								foreach ($breakfastType as $breakfast) {
									$xmlFinal.="<Type Code=\"".$breakfast."\"/>\n";
									$content_array['services'][$index]['breakfastTypes'][]=$breakfast;
								}
								$xmlFinal.="</Types>\n";
							}
							$xmlFinal.="</Service>";
						}
					}
					$xmlFinal.="</Services>\n";
				}
				else{
					$noValueCount++;
				}
				if(!empty($paymentmethodIndexes)||$hotelInfoMessage!=""){
					$xmlFinal.="<TPA_Extensions>";
					if(!empty($paymentmethodIndexes)){
						$xmlFinal.="<AcceptedPayments>\n";
						foreach ($paymentmethodIndexes as $index) {
							$paymentmethod = htmlentities(VikRequest::getString('vcm-bcah-paymentmethod'.$index.'-selected-payment-method'));
							if($paymentmethod!=""){
								$content_array['paymentmethodsIndexes'][] = $index;
								$content_array['paymentmethods'][$index]['paymentMethod']=$paymentmethod;
								$xmlFinal.="<AcceptedPayment PaymentTypeCode=\"".$paymentmethod."\"/>\n";
							}
						}
						$xmlFinal.="</AcceptedPayments>\n";
					}
					else{
						$noValueCount++;
					}
					if($hotelInfoMessage!=""){
						$xmlFinal.="<HotelierMessage Language=\"".$langTag."\">".$hotelInfoMessage."</HotelierMessage>\n";
						$content_array['hotelInfoMessage']=$hotelInfoMessage;
					}
					else{
						$noValueCount++;
					}
					$xmlFinal.="</TPA_Extensions>";
				}
				else{
					$noValueCount+=2;
				}
				$xmlFinal.="</HotelInfo>\n";
				$errorLimit=5;
				//
				break;
			case 'facility-info':

				//clear oldData
				unset($oldData['facilityInfoMessage']);
				unset($oldData['amenitiesIndexes']);
				unset($oldData['amenities']);
				//

				//get data
				$facilityInfoMessage = htmlentities(VikRequest::getString('vcm-bcah-facility-info-details'));
				$amenityIndexes = VikRequest::getVar('vcm-bcah-amenity-index',array());
				//
				//make XML
				$xmlFinal.="
				<FacilityInfo>\n";
				if(!empty($amenityIndexes)){
					$xmlFinal.="<GuestRooms>\n
						<GuestRoom>\n
						<Amenities>\n";
					foreach ($amenityIndexes as $index) {
						$xmlFinal.="<Amenity ";
						$selectedAmenity = htmlentities(VikRequest::getString("vcm-bcah-amenity".$index."-selected-amenity"));
						$amenityQuantity = htmlentities(VikRequest::getString("vcm-bcah-amenity".$index."-quantity"));
						if($selectedAmenity!=""||$amenityQuantity!=""){
							$content_array['amenitiesIndexes'][] = $index;
							if($selectedAmenity!=""){
								$xmlFinal.="RoomAmenityCode=\"".$selectedAmenity."\" ";
								$content_array['amenities'][$index]['amenity'] = $selectedAmenity;
							}
							if($amenityQuantity!=""){
								$xmlFinal.="Quantity=\"".$amenityQuantity."\" ";
								$content_array['amenities'][$index]['quantity'] = $amenityQuantity;
							}
						}
						$xmlFinal.="/>\n";
					}
					$xmlFinal.="</Amenities>\n
						</GuestRoom>\n
						</GuestRooms>\n";
				}
				else{
					$noValueCount++;
				}
				if($facilityInfoMessage!=""){
					$xmlFinal.="<TPA_Extensions><HotelierMessage Language=\"".$langTag."\">".$facilityInfoMessage."</HotelierMessage></TPA_Extensions>\n";
					$content_array['facilityInfoMessage']=$facilityInfoMessage;
				}
				else{
					$noValueCount++;
				}
				$xmlFinal.="</FacilityInfo>\n";
				$errorLimit=2;
				//
				break;
			case 'area-info':

				//clear oldData
				unset($oldData['areaInfoMessage']);
				unset($oldData['attractionsIndexes']);
				unset($oldData['attractions']);
				//

				//get data
				$areaInfoMessage = htmlentities(VikRequest::getString('vcm-bcah-area-info-details'));
				$attractionsIndexes = VikRequest::getVar('vcm-bcah-attraction-index',array());
				//
				//make XML
				$xmlFinal.="<AreaInfo>\n";
				if(!empty($attractionsIndexes)){
					$xmlFinal.="<Attractions>\n";
					foreach ($attractionsIndexes as $index) {
						$content_array['attractionsIndexes'][] = $index;
						$xmlFinal.="<Attraction ";
						$attractionName = htmlentities(VikRequest::getString("vcm-bcah-attraction".$index."-name"));
						$attractionSelected = htmlentities(VikRequest::getString("vcm-bcah-attraction".$index."-selected-attraction-type"));
						$attractionDistance = htmlentities(VikRequest::getString("vcm-bcah-attraction".$index."-distance"));
						$attractionDistanceMeasure = htmlentities(VikRequest::getString("vcm-bcah-attraction".$index."-distance-measurement"));
						if($attractionName!=""){
							$xmlFinal.="AttractionName=\"".$attractionName."\" ";
							$content_array['attractions'][$index]['attractionName'] = $attractionName;
						}
						if($attractionSelected!=""){
							$xmlFinal.="AttractionCategoryCode=\"".$attractionSelected."\" ";
							$content_array['attractions'][$index]['attractionCode'] = $attractionSelected;
						}
						if($attractionDistance!=""){
							$xmlFinal.="Distance=\"".$attractionDistance."\" ";
							$content_array['attractions'][$index]['distance'] = $attractionDistance;
						}
						if($attractionDistanceMeasure!=""){
							$xmlFinal.="DistanceUnit=\"".$attractionDistanceMeasure."\" ";
							$content_array['attractions'][$index]['distanceMeasurement'] = $attractionDistanceMeasure;
						}
						$xmlFinal.="/>\n";
					}
					$xmlFinal.="</Attractions>\n";
				}
				else{
					$noValueCount++;
				}
				if($areaInfoMessage!=""){
					$xmlFinal.="<TPA_Extensions><HotelierMessage Language=\"".$langTag."\">".$areaInfoMessage."</HotelierMessage></TPA_Extensions>\n";
					$content_array['areaInfoMessage']=$areaInfoMessage;
				}
				else{
					$noValueCount++;
				}
				$xmlFinal.="</AreaInfo>\n";
				$errorLimit=2;
				//
				break;
			case 'policies':

				//clear oldData
				unset($oldData['checkInTimeStart']);
				unset($oldData['checkInTimeEnd']);
				unset($oldData['checkOutTimeStart']);
				unset($oldData['checkOutTimeEnd']);
				unset($oldData['kidsStayFree']);
				unset($oldData['kidsCutoffAge']);
				unset($oldData['petsAllowedCode']);
				unset($oldData['stayFreeChildren']);
				unset($oldData['nonRefundableFee']);
				unset($oldData['guaranteepaymentsIndexes']);
				unset($oldData['guaranteepayments']);
				unset($oldData['cancelpoliciesIndexes']);
				unset($oldData['cancelpolicies']);
				unset($oldData['taxesIndexes']);
				unset($oldData['taxes']);
				unset($oldData['feesIndexes']);
				unset($oldData['fees']);
				//

				//get data
				$checkInTimeStart = htmlentities(VikRequest::getString('vcm-bcah-check-in-time-start'));
				$checkOutTimeStart = htmlentities(VikRequest::getString('vcm-bcah-check-out-time-start'));
				$checkInTimeEnd = htmlentities(VikRequest::getString('vcm-bcah-check-in-time-end'));
				$checkOutTimeEnd = htmlentities(VikRequest::getString('vcm-bcah-check-out-time-end'));
				$kidsStayFree = htmlentities(VikRequest::getString('vcm-bcah-kids-stay-free'))=='on'? 1 : 0;
				$freeCutoffAge = htmlentities(VikRequest::getString('vcm-bcah-free-cutoff-age'));
				$freeChildPerAdult = htmlentities(VikRequest::getString('vcm-bcah-free-child-per-adult'));
				$petsAllowed = htmlentities(VikRequest::getString('vcm-bcah-pets-allowed'));
				$nonRefundableFee = htmlentities(VikRequest::getString('vcm-bcah-non-refundable-fee'));
				$guaranteepaymentIndexes = VikRequest::getVar('vcm-bcah-guaranteepayment-index', array());
				$cancelpolicyIndexes = VikRequest::getVar('vcm-bcah-cancelpolicy-index', array());
				$taxIndexes = VikRequest::getVar('vcm-bcah-tax-index', array());
				$feeIndexes = VikRequest::getVar('vcm-bcah-fee-index', array());
				//
				//make XML
				$xmlFinal.="<Policies>\n<Policy>\n";
				//SPECIAL CONTROL
				if($checkInTimeStart!=""||$checkOutTimeStart!=""||$kidsStayFree!=""||$freeCutoffAge!=""||$freeChildPerAdult!=""){
					if((intval(explode(':', $checkInTimeStart)[0])*24*60)+(intval(explode(':', $checkInTimeStart)[1])*60)>=(intval(explode(':', $checkInTimeEnd)[0])*24*60)+(intval(explode(':', $checkInTimeEnd)[1])*60)&&$checkInTimeEnd!=""&&$checkInTimeStart!=""){
						$timeError = 1;
					}
					else if((intval(explode(':', $checkOutTimeStart)[0])*24*60)+(intval(explode(':', $checkOutTimeStart)[1])*60)>=(intval(explode(':', $checkOutTimeEnd)[0])*24*60)+(intval(explode(':', $checkOutTimeEnd)[1])*60)&&$checkOutTimeEnd!=""&&$checkOutTimeStart!=""){
						$timeError = 2;
					}
					$xmlFinal.="<PolicyInfo ";
					if($checkInTimeStart!=""){
						$xmlFinal.="CheckInTime=\"".$checkInTimeStart;
						$content_array['checkInTimeStart']=$checkInTimeStart;
						if($checkInTimeEnd!=""){
							$xmlFinal.="-".$checkInTimeEnd."\" ";
							$content_array['checkInTimeEnd']=$checkInTimeEnd;
						}
						else{
							$xmlFinal.="\" ";
						}
					}
					if($checkOutTimeStart!=""){
						$xmlFinal.="CheckOutTime=\"".$checkOutTimeStart;
						$content_array['checkOutTimeStart']=$checkOutTimeStart;
						if($checkOutTimeEnd!=""){
							$xmlFinal.="-".$checkOutTimeEnd."\" ";
							$content_array['checkOutTimeEnd']=$checkOutTimeEnd;
						}
						else{
							$xmlFinal.="\" ";
						}
					}
					if($kidsStayFree!=""){
						$xmlFinal.="KidsStayFree=\"".$kidsStayFree."\" ";
						$content_array['kidsStayFree']=$kidsStayFree;
					}
					if($freeCutoffAge!=""){
						$xmlFinal.="UsualStayFreeCutoffAge=\"".$freeCutoffAge."\" ";
						$content_array['kidsCutoffAge']=$freeCutoffAge;
					}
					if($freeChildPerAdult!=""){
						$xmlFinal.="UsualStayFreeChildPerAdult=\"".$freeChildPerAdult."\" ";
						$content_array['stayFreeChildren']=$freeChildPerAdult;
					}
					$xmlFinal.="/>\n";
				}
				if(!empty($guaranteepaymentIndexes)){
					$xmlFinal.="<GuaranteePaymentPolicy>\n";
					foreach ($guaranteepaymentIndexes as $index) {
						$guaranteepayment = htmlentities(VikRequest::getString('vcm-bcah-guaranteepayment'.$index.'-selected-guaranteed-payment'));
						if($guaranteepayment!=""){
							$xmlFinal.="<GuaranteePayment PolicyCode=\"".$guaranteepayment."\"/>\n";
							$content_array['guaranteepaymentsIndexes'][] = $index;
							$content_array['guaranteepayments'][$index]['guaranteepayment'] = $guaranteepayment;
						}
					}
					$xmlFinal.="</GuaranteePaymentPolicy>\n";
				}
				else{
					$noValueCount++;
				}
				if($petsAllowed!=""||$nonRefundableFee!=""){
					$xmlFinal.="<PetsPolicies ";
					if($petsAllowed!=""){
						$xmlFinal.="PetsAllowedCode=\"".$petsAllowed."\" ";
						$content_array['petsAllowedCode']=$petsAllowed;
					}
					$xmlFinal.=">\n";
					if($nonRefundableFee!=""){
						$xmlFinal.="<PetsPolicy NonRefundableFee=\"".$nonRefundableFee."\"/>\n";
						$content_array['nonRefundableFee']=$nonRefundableFee;
					}
					$xmlFinal.="</PetsPolicies>\n";
				}
				else{
					$noValueCount++;
				}
				if(!empty($cancelpolicyIndexes)){
					$xmlFinal.="<CancelPolicy>\n";
					foreach ($cancelpolicyIndexes as $index) {
						$cancelPolicy = htmlentities(VikRequest::getString('vcm-bcah-cancelpolicy'.$index.'-selected-cancel-policy'));
						if($cancelPolicy!=""){
							$xmlFinal.="<CancelPenalty PolicyCode=\"".$cancelPolicy."\"/>\n";
							$content_array['cancelpoliciesIndexes'][] = $index;
							$content_array['cancelpolicies'][$index]['cancelpolicy'] = $cancelPolicy;
						}
					}
					$xmlFinal.="</CancelPolicy>\n";
				}
				else{
					$noValueCount++;
				}
				if(!empty($taxIndexes)){
					$xmlFinal.="<TaxPolicies>\n";
					foreach ($taxIndexes as $index) {
						$selectedTax = htmlentities(VikRequest::getString("vcm-bcah-tax".$index."-selected-tax"));
						$taxAmount = VikRequest::getFloat("vcm-bcah-tax".$index."-amount");
						$taxDecimalPlaces = htmlentities(VikRequest::getString("vcm-bcah-tax".$index."-decimal-places"));
						$taxType = htmlentities(VikRequest::getString("vcm-bcah-tax".$index."-type"));
						$taxChargeFrequency = htmlentities(VikRequest::getString("vcm-bcah-tax".$index."-charge-frequency"));
						if($selectedTax!=""||$taxAmount!=""||$taxDecimalPlaces!=""||$taxType!=""||$taxChargeFrequency!=""){
							$xmlFinal.="<TaxPolicy ";
							$content_array['taxesIndexes'][] = $index;
							if($selectedTax!=""){
								$xmlFinal.="Code=\"".$selectedTax."\" ";
								$content_array['taxes'][$index]['code'] = $selectedTax;
							}
							if($taxAmount!=""){
								$xmlFinal.="Amount=\"".$taxAmount."\" ";
								$content_array['taxes'][$index]['amount'] = $taxAmount;
							}
							if($taxDecimalPlaces!=""){
								$content_array['taxes'][$index]['decimalPlaces'] = $taxDecimalPlaces;
								$xmlFinal.="DecimalPlaces=\"".$taxDecimalPlaces."\" ";
							}
							if($taxType!=""){
								$content_array['taxes'][$index]['type'] = $taxType;
								$xmlFinal.="Type=\"".$taxType."\" ";
							}
							if($taxChargeFrequency!=""){
								$content_array['taxes'][$index]['chargeFrequency'] = $taxChargeFrequency;
								$xmlFinal.="ChargeFrequency=\"".$taxChargeFrequency."\" ";
							}
							$xmlFinal.="/>\n";
						}
					}
					$xmlFinal.="</TaxPolicies>\n";
				}
				else{
					$noValueCount++;
				}
				if(!empty($feeIndexes)){
					$xmlFinal.="<FeePolicies>\n";
					foreach ($feeIndexes as $index) {
						$selectedFee = htmlentities(VikRequest::getString("vcm-bcah-fee".$index."-selected-fee"));
						$feeAmount = VikRequest::getFloat("vcm-bcah-fee".$index."-amount");
						$feeDecimalPlaces = htmlentities(VikRequest::getString("vcm-bcah-fee".$index."-decimal-places"));
						$feeType = htmlentities(VikRequest::getString("vcm-bcah-fee".$index."-type"));
						$feeChargeFrequency = htmlentities(VikRequest::getString("vcm-bcah-fee".$index."-charge-frequency"));
						if($selectedFee!=""||$feeAmount!=""||$feeDecimalPlaces!=""||$feeType!=""||$feeChargeFrequency!=""){
							$xmlFinal.="<FeePolicy ";
							$content_array['feesIndexes'][] = $index;
							if($selectedFee!=""){
								$xmlFinal.="Code=\"".$selectedFee."\" ";
								$content_array['fees'][$index]['code'] = $selectedFee;
							}
							if($feeAmount!=""){
								$xmlFinal.="Amount=\"".$feeAmount."\" ";
								$content_array['fees'][$index]['amount'] = $feeAmount;
							}
							if($feeDecimalPlaces!=""){
								$xmlFinal.="DecimalPlaces=\"".$feeDecimalPlaces."\" ";
								$content_array['fees'][$index]['decimalPlaces'] = $feeDecimalPlaces;
							}
							if($feeType!=""){
								$xmlFinal.="Type=\"".$feeType."\" ";
								$content_array['fees'][$index]['type'] = $feeType;
							}
							if($feeChargeFrequency!=""){
								$xmlFinal.="ChargeFrequency=\"".$feeChargeFrequency."\" ";
								$content_array['fees'][$index]['chargeFrequency'] = $feeChargeFrequency;
							}
						}
						$xmlFinal.="/>\n";
					}
					$xmlFinal.="</FeePolicies>\n";
				}
				else{
					$noValueCount++;
				}
				$xmlFinal.="</Policy>\n</Policies>\n";
				//
				$errorLimit=5;
				//
				break;
			case 'multimedia':
				//clear oldData
				unset($oldData['imagesIndexes']);
				unset($oldData['images']);
				//
				//get data
				$imageIndexes = VikRequest::getVar("vcm-bcah-image-index",array());
				$imagesUploadType = VikRequest::getString('vcm-bcah-upload-type-selector');
				//
				//make XML
				$xmlFinal.="<MultimediaDescriptions";
				if($imagesUploadType!=""){
					$xmlFinal.=" OverlayGroup=\"".$imagesUploadType."\"";
				}
				$xmlFinal.="><MultimediaDescription>\n";
				if(!empty($imageIndexes)){
					$xmlFinal.="<ImageItems>\n";
					foreach ($imageIndexes as $index) {
						$mainImage = htmlentities(VikRequest::getString('vcm-bcah-image'.$index.'-main-image'));
						$imageURL = htmlentities(VikRequest::getString('vcm-bcah-image'.$index.'-image-url'));
						$selectedTag = VikRequest::getVar('vcm-bcah-image'.$index.'-selected-tag', array());
						$xmlFinal.="<ImageItem>\n<ImageFormat ";
						if($imageURL!=""){
							$content_array['imagesIndexes'][] = $index;
							if($mainImage!=""){
								$mainImage = $mainImage=='on'? 1:0;
								$content_array['images'][$index]['main'] = $mainImage;
								$xmlFinal.="Main=\"".$mainImage."\" ";
							}
							$xmlFinal.=">\n";
							$xmlFinal.="<URL>".$imageURL."</URL>\n";
							$content_array['images'][$index]['url'] = $imageURL;
							$xmlFinal.="</ImageFormat>";
							if($selectedTag!=""){
								$xmlFinal.="<TPA_Extensions><ImageTags>\n";
								foreach ($selectedTag as $tag) {
									$xmlFinal .= "<ImageTag ID=\"".$tag."\"/>\n";
									$content_array['images'][$index]['tag'][] = $tag;
								}
								$xmlFinal.="</ImageTags></TPA_Extensions>\n";
							}
						}
						else{
							$noValueCount++;
						}
						$xmlFinal.="</ImageItem>";
					}
					$xmlFinal.="</ImageItems>\n";
				}else{
					$noValueCount++;
				}
				$errorLimit=1;
				$xmlFinal.="</MultimediaDescription></MultimediaDescriptions>\n";
				//
				break;
			case 'standardphrases':

				$errorLimit = 0;
				$noValueCount = -1;

				unset($oldData['standardphrases']);

				$guestId = VikRequest::getString('vcm-bcah-sp-guest-id');
				$guestId = $guestId == "" ? 0 : 1;
				$informArrival = VikRequest::getString('vcm-bcah-sp-inform-arrival');
				$informArrival = $informArrival == "" ? 0 : 1;
				$beforeStay = VikRequest::getString('vcm-bcah-sp-pay-before-stay');
				$beforeStay = $beforeStay == "" ? 0 : 1;
				$tatooRestriction = VikRequest::getString('vcm-bcah-tatoo-restriction');
				$tatooRestriction = $tatooRestriction == "" ? 0 : 1;
				$keyCollection = VikRequest::getString('vcm-bcah-sp-key-collection');
				$keyCollection = $keyCollection == "" ? 0 : 1;
				$kcAddress = VikRequest::getString('vcm-bcah-sp-key-collection-address');
				$kcCity = VikRequest::getString('vcm-bcah-sp-key-collection-city');
				$kcPostal = VikRequest::getString('vcm-bcah-sp-key-collection-postal');
				$renovation = VikRequest::getString('vcm-bcah-sp-renovation');
				$renovation = $renovation == "" ? 0 : 1;
				$rvFromDate = VikRequest::getString('vcm-bcah-sp-rv-fromdate');
				$rvToDate = VikRequest::getString('vcm-bcah-sp-rv-todate');

				$content_array['standardphrases']['guestid'] = $guestId;
				$content_array['standardphrases']['informarrival'] = $informArrival;
				$content_array['standardphrases']['beforestay'] = $beforeStay;
				$content_array['standardphrases']['tatoorestriction'] = $tatooRestriction;
				$content_array['standardphrases']['keycollection'] = $keyCollection;
				$content_array['standardphrases']['kcaddress'] = $kcAddress;
				$content_array['standardphrases']['kccity'] = $kcCity;
				$content_array['standardphrases']['kcpostal'] = $kcPostal;
				$content_array['standardphrases']['renovation'] = $renovation;
				$content_array['standardphrases']['rvfromdate'] = $rvFromDate;
				$content_array['standardphrases']['rvtodate'] = $rvToDate;


				//make XML
				$xmlFinal.="<TPA_Extensions><StandardPhrases>";
				if(!empty($content_array['standardphrases'])) {
					$xmlFinal.="<StandardPhrase Name=\"GuestIdentification\" Enabled=\"".$guestId."\"/>
					<StandardPhrase Name=\"InformArrivalTime\" Enabled=\"".$informArrival."\"/>
					<StandardPhrase Name=\"PayBeforeStay\" Enabled=\"".$beforeStay."\"/>
					<StandardPhrase Name=\"TattooRestriction\" Enabled=\"".$tatooRestriction."\"/>
					<StandardPhrase Name=\"KeyCollection\" Enabled=\"".$keyCollection."\">
						<Options>";
					if(!empty($content_array['standardphrases']['kcaddress'])) {
						$xmlFinal.="\n<Option Name=\"KeyCollectionAddressLine\">".$kcAddress."</Option>";
					}
					if(!empty($content_array['standardphrases']['kccity'])) {
						$xmlFinal.="\n<Option Name=\"KeyCollectionCityName\">".$kcCity."</Option>";
					}
					if(!empty($content_array['standardphrases']['kcpostal'])) {
						$xmlFinal.="\n<Option Name=\"KeyCollectionPostalCode\">".$kcPostal."</Option>";
					}
						$xmlFinal.="\n</Options>
					</StandardPhrase>";
					$xmlFinal.="<StandardPhrase Name=\"Renovation\" Enabled=\"".$renovation."\">
						<Options>";
					if(!empty($content_array['standardphrases']['rvfromdate'])) {
						$xmlFinal.="\n<Option Name=\"RenovationFrom\">".$rvFromDate."</Option>";
					}
					if(!empty($content_array['standardphrases']['rvtodate'])) {
						$xmlFinal.="\n<Option Name=\"RenovationUntil\">".$rvToDate."</Option>";
					}
						$xmlFinal.="\n</Options>
					</StandardPhrase>";
				}
				$xmlFinal.="</StandardPhrases></TPA_Extensions>\n";
				break;
		}
		$xmlFinal .= "</BCAHotelContent>\n";
		$xmlFinal.="</BCAHotelContentRQ>\n";

		if($e4j_debug==1){
			echo "</br><strong>Printing New Data</strong></br>";
			echo "<pre>".print_r($content_array,true)."</pre>";
			echo "</br><strong>Printing XML</strong></br>";
			echo "<pre>".print_r(trim(htmlentities($xmlFinal)),true)."</pre>";
		}

		$xmlResponse = array();

		if($e4j_debug==1){
			$session->set('vcmbcahcont'.$channel['params']['hotelid'],json_encode($content_array));
			echo "</br><strong>Printing New Data</strong></br>";
			echo "<pre>".print_r($content_array,true)."</pre>";
			die;
		}
		else if ($noValueCount>=$errorLimit){
			$session->set('vcmbcahcont'.$channel['params']['hotelid'],json_encode($content_array));
			$mainframe->enqueueMessage(JText::_('VCMBCAHNODATAERROR'),'error');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcahcont&tab=".$submittedform);
		}
		else if($timeError==1){
			$session->set('vcmbcahcont'.$channel['params']['hotelid'],json_encode($content_array));
			$mainframe->enqueueMessage(JText::_('VCMBCAHCHCKINTIMEERROR'),'error');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcahcont&tab=".$submittedform);
		}
		else if($timeError==2){
			$session->set('vcmbcahcont'.$channel['params']['hotelid'],json_encode($content_array));
			$mainframe->enqueueMessage(JText::_('VCMBCAHCHCKOUTTIMEERROR'),'error');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcahcont&tab=".$submittedform);
		}
		else if($insertType=="New"&&(
		$physical_locationHotelName==""||
		$physical_locationCountry==""||
		$physical_locationCityName==""||
		$physical_locationAddressLine==""||
		$physical_locationPostalCode==""||
		$positionLatitude==""||
		$positionLongitude==""||
		$invoicesPersonFirstName==""||
		$invoicesPersonSurname==""||
		$invoicesPersonGender==""||
		$invoicesPersonJobTitle==""||
		$invoicesAddressCountry==""||
		$invoicesAddressCityName==""||
		$invoicesAddressAddressLine==""||
		$invoicesAddressPostalCode==""||
		$invoicesEmailIndexes==array()||
		$invoicesPhoneIndexes==array()||
		$generalPersonFirstName==""||
		$generalPersonSurname==""||
		$generalPersonGender==""||
		$generalPersonJobTitle==""||
		$generalAddressCountry==""||
		$generalAddressCityName==""||
		$generalAddressAddressLine==""||
		$generalAddressPostalCode==""||
		$generalEmailIndexes==array()||
		$generalPhoneIndexes==array())){
			$session->set('vcmbcahcont'.$channel['params']['hotelid'],json_encode($content_array));
			$mainframe->enqueueMessage(JText::_('VCMBCAHNEWVALIDATE'),'error');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcahcont&tab=".$submittedform);
		}
		else{
			//Send xml to server
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xmlFinal);
			$rs = $e4jC->exec();
			if($e4jC->getErrorNo()) {
				if(!empty($oldData)){
					$content_array = array_merge($oldData,$content_array);
				}
				$session->set('vcmbcahcont'.$channel['params']['hotelid'],json_encode($content_array));
				VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bcahcont&tab='.$submittedform);
			}else {
				if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
					if(!empty($oldData)){
						$content_array = array_merge($oldData,$content_array);
					}
					$session->set('vcmbcahcont'.$channel['params']['hotelid'],json_encode($content_array));
					VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
					$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bcahcont&tab='.$submittedform);
				}else {
					$xmlResponse = unserialize($rs);
				}
			}
		}

		$newHotelID = "";
		if(count($xmlResponse)){
			$newHotelID = $xmlResponse['UniqueID']['ID'];
		}

		if(array_key_exists('Warnings', $xmlResponse)){
			$mainframe->enqueueMessage(sprintf(JText::_('VCMBCAHNEWWARNING'),$xmlResponse['Warnings']),'warning');
		}



		if($e4j_debug!=1){
			if($insertType!="New"){
				if(!empty($oldData)){
					$content_array = array_merge($oldData,$content_array);
				}
				$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcahcont".$channel['params']['hotelid']."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					//echo "<pre>".print_r($dbo->loadAssoc())."</pre>";
					$q="UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($content_array))." WHERE `param`='bcahcont".$channel['params']['hotelid']."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				else {
					$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcahcont".$channel['params']['hotelid']."',".$dbo->quote(json_encode($content_array)).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				if(!array_key_exists('Warnings', $xmlResponse)){
					$mainframe->enqueueMessage(JText::_('VCMBCAUPDATESUCC'));
				}
			}
			else if(!empty($newHotelID)){
				$content_array['insertType'] = "Overlay";
				$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcahcont".$newHotelID."',".$dbo->quote(json_encode($content_array)).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$redirectURL='index.php?option=com_vikchannelmanager&task=config&newbcahid='.$newHotelID;
				if(!array_key_exists('Warnings', $xmlResponse)){
					$mainframe->enqueueMessage(JText::sprintf('VCMBCAHNEWSUCC', $newHotelID));
				}
				$mainframe->redirect($redirectURL);
				exit;
			}
		}
		else{
			die;
		}

		//var_dump($rs); echo "<br/><pre>".print_r($rs,true)."</pre>"; die;
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bcahcont&tab='.$submittedform);
	}

	public function makeRatesXml () {
		$dbo = JFactory::getDbo();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$e4jc_url="https://e4jconnect.com/channelmanager/?r=bcarp&c=".$channel['name'];

		$e4j_debug = VikRequest::getInt('e4j_debug');

		$newProgIDs = array();
		$removeProgIDs = array();
		$overlayProgIDs = array();

		$session = JFactory::getSession();
		$session->set('vcmbcarplans','');

		$productList = array();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param` LIKE 'bcapnotif%';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$products = $dbo->loadAssocList();
			foreach ($products as $key => $product) {
				//echo "<strong>Product ".$key."</strong>: <pre>".print_r(json_decode($product['setting'],true),true)."</pre></br>";
				foreach (json_decode($product['setting'],true)['Connect'] as $thisHotelID => $contents) {
					foreach ($contents as $thisRate => $values) {
						$productList[$thisRate][] = $thisHotelID;
					}
				}
			}
			//echo "<strong>HotelIDs and rates:</strong> <pre>".print_r($productList,true)."</pre>";
			//die;
		}
		
		/*$q = "DELETE FROM `#__vikchannelmanager_config` WHERE `param`='bcarplans".$channel['params']['hotelid']."';";
		$dbo->setQuery($q);
		$dbo->execute();

		die;*/

		$mainframe = JFactory::getApplication();

		$xmlResponse = array();

		$hiddenID = VikRequest::getVar('hiddenID',array());
		$progID = VikRequest::getVar('progID',array());
		$actionType = VikRequest::getVar('actionType',array());
		$name = VikRequest::getVar('name',array());

		if(empty($hiddenID)&&empty($progID)&&empty($actionType)&&empty($name)){
			$mainframe->enqueueMessage(JText::_('VCMBCAHEMPTYVALERR'),'error');
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bcarplans');
		}

		$content_array = array();
		for($i=0;$i<count($progID);$i++){
			$content_array['ratePlans'][$progID[$i]]['hiddenID'] = htmlentities(trim($hiddenID[$i]));
			$content_array['ratePlans'][$progID[$i]]['progID'] = htmlentities(trim($progID[$i]));
			$content_array['ratePlans'][$progID[$i]]['name'] = htmlentities(trim($name[$i]));
			$content_array['ratePlans'][$progID[$i]]['actionType'] = htmlentities(trim($actionType[$i]));
			if($actionType[$i]=='New'){
				$newProgIDs[] = $progID[$i];
			}
			else if($actionType[$i]=='Remove'){
				$removeProgIDs[] = $progID[$i];
			}
			else if($actionType[$i]=='Overlay'){
				$overlayProgIDs[] = $progID[$i];
			}
		}

		//echo "<strong>This is the array:</strong><pre>".print_r($productList,true)."</pre>";
		foreach ($removeProgIDs as $value) {
			$removeValHiddenID = $content_array['ratePlans'][$value]['hiddenID'];
			//echo "<strong>Content Array: </strong><pre>".print_r($content_array,true)."</pre>";
			//echo "<strong>Checking this value:</strong><pre>".print_r($removeValHiddenID,true)."</pre>";
			if(array_key_exists($removeValHiddenID, $productList)){
				//echo "<h2>IT EXISTS</h2>";
				//echo "<h1>At least you're here</h1>";
				$mainframe->enqueueMessage(sprintf(JText::_('VCMBCARPPRODUCTEXISTS'),$content_array['ratePlans'][$value]['name'],$productList[$removeValHiddenID][0]),'error');
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarplans");
				exit;
			}
		}
		//exit('Exited here');

		if($e4j_debug==1){
			echo "<strong>Printing Request: </strong><pre>".print_r($_REQUEST,true)."</pre>";
			echo "<strong>New Hidden IDs: </strong><pre>".print_r($newProgIDs,true)."</pre>";
			echo "<strong>Remove Hidden IDs: </strong><pre>".print_r($removeProgIDs,true)."</pre>";
			echo "<strong>Overlay Hidden IDs: </strong><pre>".print_r($overlayProgIDs,true)."</pre>";
		}

		$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
			<!-- BCARP Request e4jConnect.com - VikChannelManager - VikBooking -->
			<BCARatePlansRQ xmlns=\"http://www.e4jconnect.com/avail/bcarprq\">
			<Notify client=\"".JUri::root()."\"/>
			<Api key=\"".VikChannelManager::getApiKey()."\"/>
			<BCARatePlans hotelid=\"".$channel['params']['hotelid']."\">";
		foreach ($newProgIDs as $value) {
			if($content_array['ratePlans'][$value]['name']!=""){
				$xmlFinal.="<RatePlan RatePlanNotifType=\"New\" RatePlanID=\"".$value."\">
				<Description Name=\"".$content_array['ratePlans'][$value]['name']."\"/>
				</RatePlan>";
			}
		}
		foreach ($overlayProgIDs as $value) {
			if($content_array['ratePlans'][$value]['name']!=""){
				$xmlFinal.="<RatePlan RatePlanNotifType=\"Overlay\" RatePlanCode=\"".$content_array['ratePlans'][$value]['hiddenID']."\">
				<Description Name=\"".$content_array['ratePlans'][$value]['name']."\"/>
				</RatePlan>";
			}
		}
		foreach ($removeProgIDs as $value) {
			$xmlFinal.="<RatePlan RatePlanNotifType=\"Remove\" RatePlanCode=\"".$content_array['ratePlans'][$value]['hiddenID']."\">
			<Description Name=\"".$content_array['ratePlans'][$value]['name']."\"/>
			</RatePlan>";
		}
		$xmlFinal.="</BCARatePlans></BCARatePlansRQ>";
		if($e4j_debug==1){
			echo "<strong>Printing XML: </strong><pre>".print_r(trim(htmlentities($xmlFinal)),true)."</pre>"; die;
		}

		//Send xml to server
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlFinal);
		$rs = $e4jC->exec();

		if($e4jC->getErrorNo()) {
			VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
		}
		else{
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			}
			else{
				echo "<strong>Server response: </strong><pre>".print_r($rs,true)."</pre>";
				$xmlResponse = unserialize($rs);
				echo "<strong>Unserialized server response: </strong><pre>".print_r($xmlResponse,true)."</pre>";
			}
		}
		//Debug server response
		echo '<pre>'.VikChannelManager::getErrorFromMap($rs).'</pre><br/><br/>';
		//exit;

		//
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcahcont&tab=$submittedform");
			//exit;
		}
		//

		if(array_key_exists('Warnings', $xmlResponse)){
			$mainframe->enqueueMessage($xmlResponse['Warnings'],'warning');
		}

		if(count($xmlResponse)){
			foreach ($xmlResponse['RatePlanCrossRef'] as $value) {
				$content_array['ratePlans'][$value['RequestRatePlanCode']]['hiddenID'] = $value['ResponseRatePlanCode'];
			}
			foreach ($removeProgIDs as $value) {
				unset($content_array['ratePlans'][$value]);
			}
			$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcarplans".$channel['params']['hotelid']."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				//echo "<strong>Printing Old Data: </strong><pre>".print_r($dbo->loadAssoc(),true)."</pre>";
				$q="UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($content_array))." WHERE `param`='bcarplans".$channel['params']['hotelid']."';";
				$dbo->setQuery($q);
				$dbo->execute();
			}else {
				$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcarplans".$channel['params']['hotelid']."',".$dbo->quote(json_encode($content_array)).");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			if(!array_key_exists('Warnings', $xmlResponse)){
				$mainframe->enqueueMessage(JText::_('VCMBCAUPDATESUCC'));
			}
		}
		else{
			$session->set('vcmbcarplans',json_encode($content_array));
		}
		if($e4j_debug==1){
			echo "<strong>Printing Uncoded Saved Array: </strong><pre>".print_r($content_array,true)."</pre>";
			die;
		}
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bcarplans');
	}

	public function makeRoomsXml () {

		$mainframe = JFactory::getApplication();

		$dbo = JFactory::getDbo();

		/*$actionSelected = VikRequest::getInt('action-option');
		if($actionSelected==0){
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomid);
			die;
		}*/
		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$status = VikRequest::getString('status');
		$roomValues = VikRequest::getString('roomValues');
		if(substr($roomValues, 0,2)=="--"){
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont");
			exit;
		}
		if($roomValues == -1){
			$roomid = -1;
		}
		else{
			$roomid = explode("-", $roomValues)[0];
		}
		$action = VikRequest::getString('bcarcAction');
		$e4j_debug = VikRequest::getInt('e4j_debug');

		$session = JFactory::getSession();
		$session->set('vcmbcarcontents'.$roomid,'');

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcarcont".$roomid."';";
		$dbo->setQuery($q);
		$dbo->execute();

		$oldData = array();

		//echo "<pre>".print_r($_REQUEST, true)."</pre>";die;
		if ($dbo->getNumRows() > 0) {
			$oldData = $dbo->loadAssoc();
			$oldData=json_decode($oldData['setting'],true);
			/*echo "<pre>".print_r($oldData,true)."</pre>";
			echo "<pre>".print_r($oldData->people,true)."</pre>";*/
			if(!empty($oldData)&&$e4j_debug==1){
				echo "</br><strong>Printing Old Data</strong></br>";
				echo "<pre>".print_r($oldData,true)."</pre>";
			}
		}
		$e4jc_url="https://e4jconnect.com/channelmanager/?r=bcarc&c=".$channel['name'];

		$maxOccupancy = VikRequest::getInt('maxOccupancy');
		$cribs = VikRequest::getString('cribs')=='on'? 1 : false;
		$additionalGuests = VikRequest::getString('additionalGuests')=='on'? 1 : false;
		$maxRollaways = VikRequest::getInt('maxRollaways');
		$noSmoking = VikRequest::getString('noSmoking')=='on'? 1 : false;
		$roomType = VikRequest::getString('roomType');
		$sizeMeasure = VikRequest::getInt('sizeMeasure');
		$roomName = VikRequest::getString('roomName');
		$amenityIndexes = VikRequest::getVar('amenity-index',array());
		$imageIndexes = VikRequest::getVar('image-index',array());
		$subroomIndexes = VikRequest::getVar('subroom-index',array());
		$hotelID = VikRequest::getString('hotelID');

		if(empty($hotelID)){
			$hotelID = $channel['params']['hotelid'];
		}

		$content_array['Room'][$roomid]['hotelid'] = $hotelID;

		$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<!-- BCARC Request e4jConnect.com - VikChannelManager - VikBooking -->
		<BCARoomsContentRQ xmlns=\"http://www.e4jconnect.com/avail/bcarcrq\">
			<Notify client=\"".JUri::root()."\"/>
			<Api key=\"".VikChannelManager::getApiKey()."\"/>
			<BCARoomsContent hotelid=\"".$hotelID."\">
			<SellableProducts  HotelCode=\"".$hotelID."\">
				<SellableProduct InvNotifType=\"";

		if($roomid == -1)
		{
			$xmlFinal .= "New\" InvStatusType=\"Initial";
		}
		else{
			$xmlFinal .= "Overlay\" InvStatusType=\"".$status."\" InvCode=\"".$roomid;
		}
		$xmlFinal.="\"><GuestRoom>";
		if($maxOccupancy!=0){
			$xmlFinal.="<Occupancy MaxOccupancy=\"".$maxOccupancy."\"/>";
			$content_array['Room'][$roomid]['maxOccupancy'] = $maxOccupancy;
		}
		if($cribs!=false||$additionalGuests!=false||$maxRollaways!=0){
			$xmlFinal.="<Quantities ";
			if($cribs!=false){
				$content_array['Room'][$roomid]['cribs'] = $cribs;
				$xmlFinal.="MaxCribs=\"".$cribs."\" ";
			}
			if($additionalGuests!=false){
				$content_array['Room'][$roomid]['additionalGuests'] = $additionalGuests;
				$xmlFinal.="MaximumAdditionalGuests=\"".$additionalGuests."\" ";
			}
			if($maxRollaways!=0){
				$content_array['Room'][$roomid]['maxRollaways'] = $maxRollaways;
				$xmlFinal.="MaxRollaways=\"".$maxRollaways."\" ";
			}
			$xmlFinal.="/>";
		}
		if($noSmoking!=false||$roomType!=""||$sizeMeasure!=0||$noSmoking!=0){
			$xmlFinal.="<Room ";
			if($roomid != -1){
				$xmlFinal.="RoomID=\"".$roomid."\" ";
			}
			if($noSmoking!=false){
				$xmlFinal.="NonSmoking=\"".$noSmoking."\" ";
				$content_array['Room'][$roomid]['noSmoking'] = $noSmoking;
			}
			if($roomType!=""){
				$xmlFinal.="RoomType=\"".$roomType."\" ";
				$content_array['Room'][$roomid]['roomType'] = $roomType;
			}
			if($sizeMeasure!=0){
				$xmlFinal.="SizeMeasurement=\"".$sizeMeasure."\" SizeMeasurementUnit=\"sqm\" ";
				$content_array['Room'][$roomid]['sizeMeasure'] = $sizeMeasure;
			}
			$xmlFinal.="/>";
		}

		if(!empty($amenityIndexes)){
			$configurationValues = array(33 => 1, 200 => 1, 58 => 1, 86 => 1, 102 => 1, 113 => 1, 203 => 1, 249 => 1);
			$content_array['Room'][$roomid]['amenity-index'] = $amenityIndexes;
			$xmlFinal .= "<Amenities>";
			foreach ($amenityIndexes as $index) {
				$selectedAmenity = VikRequest::getInt('amenity'.$index.'-selected-amenity');
				$value = VikRequest::getInt('amenity'.$index.'-value');
				if($selectedAmenity!=0){
					$xmlFinal.="<Amenity AmenityCode=\"".$selectedAmenity."\" ";
					$content_array['Room'][$roomid]['amenities'][$index]['selectedAmenity'] = $selectedAmenity;
					if($value!=0){
						$xmlFinal.="Value=\"".$value."\" ";
						$content_array['Room'][$roomid]['amenities'][$index]['value'] = $value;
					}
					else{
						$xmlFinal.="Value=\"1\" ";
					}
					if(array_key_exists($selectedAmenity, $configurationValues)){
						$xmlFinal.="Configuration=\"".$configurationValues[$selectedAmenity]."\" ";
						$configurationValues[$selectedAmenity]++;
					}
					$xmlFinal.="/>";
				}
			}
			$xmlFinal .= "</Amenities>";
		}

		//BCART Update July 6th - Check if 'Dormitory room' and 'Bed in Dormitory' room types have correct occupancy and room amenities.

		$roomArray = array(33,200,58,86,102,113,203,4001); //Bed Amenities ID
		$selectedAmenitiesIntersect = array_intersect($selectedAmenities, $roomArray); //Intersect between the selected IDs and Bed Amenities

		$roomAmenities = 0; //Number of room amenities
		$bedsOccupancy = 0; //Number of bed occupancies

		foreach ($selectedAmenitiesIntersect as $selectedAmenity) {
			$roomAmenities ++;
			if (in_array($selectedAmenity, array(200,203))) { //200 and 203 seem to be the only beds with a single bed-place
				$bedsOccupancy++;
			} else { //The rest seem to have 2 bed-places. If further information is obtained we recommend modifying this section
				$bedsOccupancy += 2;
			}
		}

		//Check room type, amenities and occupancy
		if ($roomType == "Dormitory room" && ($maxOccupancy <= 1 || $roomAmenities <= 1)) {
			$session->set('vcmbcarcontents'.$roomid,json_encode($content_array)); //Saving inserted data to session
			VikError::raiseWarning('', JText::sprintf('Error! Make sure that your occupancy and your room amenities are correct for your room type (%s)', JText::_('VCMBCARCROOMTYPE10'))); //Reporting this error
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomid); //Redirecting to the modification page
		} else if ($roomType == "Bed in Dormitory" && ($maxOccupancy > $bedsOccupancy || $roomAmenities != 1)) {
			$session->set('vcmbcarcontents'.$roomid,json_encode($content_array)); //Saving inserted data to session
			VikError::raiseWarning('', JText::sprintf('Error! Make sure that your occupancy and your room amenities are correct for your room type (%s)', JText::_('VCMBCARCROOMTYPE11'))); //Reporting this error
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomid); //Redirecting to the modification page
		}
		
		if(!empty($imageIndexes)||$roomName!=""){
			$xmlFinal .= "<Description>";
			if($roomName!=""){
				$content_array['Room'][$roomid]['roomName'] = $roomName;
				$xmlFinal.="<Text>".$roomName."</Text>";
			}
			if(!empty($imageIndexes)){
				$content_array['Room'][$roomid]['image-index'] = $imageIndexes;
				foreach ($imageIndexes as $index) {
					$url = VikRequest::getString('image'.$index.'-image-url');
					$tag = VikRequest::getVar('image'.$index.'-tag', array());
					if($url != ""){
						$xmlFinal.="<Image ImageTagID=\"";
						foreach ($tag as $tagValue) {
							$xmlFinal .= $tagValue.",";
							$content_array['Room'][$roomid]['images'][$index]['tag'][] = $tagValue;
						}
						$xmlFinal = rtrim($xmlFinal, ',');
						$xmlFinal .= "\">".$url."</Image>";
						$content_array['Room'][$roomid]['images'][$index]['url'] = $url;
					}
				}
			}
			$xmlFinal .= "</Description>";
		}

		if(!empty($subroomIndexes)){
			$xmlFinal.="<TPA_Extensions><SubRooms>";
			foreach ($subroomIndexes as $index) {
				$type = VikRequest::getString('subroom'.$index.'-type');
				$occupancy = VikRequest::getInt('subroom'.$index.'-occupancy');
				$bedding = VikRequest::getInt('subroom'.$index.'-bedding');
				$privbathroom = VikRequest::getString('subroom'.$index.'-privbathroom')=='on'? true : false;
				if($type != ""||$occupancy != 0||$privbathroom != false){
					$xmlFinal.="<SubRoom ";
					if($type != ""){
						$xmlFinal.="RoomType=\"".$type."\" ";
						$content_array['Room'][$roomid]['subrooms'][$index]['type'] = $type;
					}
					if($occupancy != 0){
						$xmlFinal.="MaxGuests=\"".$occupancy."\" ";
						$content_array['Room'][$roomid]['subrooms'][$index]['occupancy'] = $occupancy;
					}
					if($privbathroom != false){
						$xmlFinal.="PrivateBathroom=\"".$privbathroom."\" ";
						$content_array['Room'][$roomid]['subrooms'][$index]['privbathroom'] = $privbathroom;
					}
					$xmlFinal.=">";
					if($bedding != 0){
						$xmlFinal.="<Amenities>
                             <Amenity AmenityCode=\"".$bedding."\" Value=\"1\"/>
                         </Amenities>";
						$content_array['Room'][$roomid]['subrooms'][$index]['bedding'] = $bedding;
					}
					$xmlFinal.="</SubRoom>";
				}
			}
			$xmlFinal.="</SubRooms></TPA_Extensions>";
			$content_array['Room'][$roomid]['subroom-index'] = $subroomIndexes;
		}

		$xmlFinal.="</GuestRoom></SellableProduct></SellableProducts></BCARoomsContent></BCARoomsContentRQ>";

		if($action != ""){
			$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
			<!-- BCARC Request e4jConnect.com - VikChannelManager - VikBooking -->
			<BCARoomsContentRQ xmlns=\"http://www.e4jconnect.com/avail/bcarcrq\">
				<Notify client=\"".JUri::root()."\"/>
				<Api key=\"".VikChannelManager::getApiKey()."\"/>
				<BCARoomsContent hotelid=\"".$hotelID."\">
					<SellableProducts HotelCode=\"".$hotelID."\">
    					<SellableProduct InvNotifType=\"Overlay\" InvStatusType=\"".$action."\" InvCode=\"".$roomid."\">
      						<GuestRoom />
			   			</SellableProduct>
					</SellableProducts>
				</BCARoomsContent>
			</BCARoomsContentRQ>";
		}

		/*if($content_array == array()){
			$session->set('vcmbcarcontents'.$roomid,json_encode($content_array));
			VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomid);
		}*/

		/*echo "<strong>Printing Data to be saved: </strong><pre>".print_r($content_array,true)."</pre>";
		echo "<strong>Printing Old Data: </strong><pre>".print_r($oldData,true)."</pre>";*/

		if(!empty($oldData)){
			$content_array = array_merge($oldData,$content_array);
		}

		/*echo "<strong>Request: </strong><pre>".print_r($_REQUEST,true)."</pre>";
		echo "<strong>Content Array: </strong><pre>".print_r($content_array,true)."</pre>";
		echo "<strong>Sent XML: </strong><pre>".print_r(htmlentities($xmlFinal),true)."</pre>";
		die;*/

		/*echo "<strong>Printing Merged Array: </strong><pre>".print_r($content_array,true)."</pre>";
		die;*/

		//Send xml to server
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlFinal);
		$rs = $e4jC->exec();

		if($e4jC->getErrorNo()) {
			$session->set('vcmbcarcontents'.$roomid,json_encode($content_array));
			VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
			//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomid);
		}
		else{
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$session->set('vcmbcarcontents'.$roomid,json_encode($content_array));
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomid);
			}
			else{
				if($action!=""){
					$content_array['Room'][$roomid]['status'] = $action;
				}
				else{
					$content_array['Room'][$roomid]['status'] = $oldData['Room'][$roomid]['status'];
				}
				$xmlResponse = unserialize($rs);
			}
		}
		//Debug server response
		echo '<pre>'.VikChannelManager::getErrorFromMap($rs).'</pre><br/><br/>';
		//exit;

		if($e4j_debug){
			echo "<strong>Server response: </strong><pre>".print_r($rs,true)."</pre>";
			echo "<strong>Unserialized server response: </strong><pre>".print_r($xmlResponse,true)."</pre>";
			die;
		}

		if(array_key_exists('Warnings', $xmlResponse)){
			$mainframe->enqueueMessage($xmlResponse['Warnings'],'warning');
		}

		if(!empty($xmlResponse)){
			if (array_key_exists('InventoryCrossRef', $xmlResponse)) {
				if($roomid==-1){
					$roomid = $xmlResponse['InventoryCrossRef']['ResponseInvCode'];
					$content_array['Room'][$roomid] = $content_array['Room'][-1];
					$content_array['Room'][$roomid]['status'] = 'Active';
					unset($content_array['Room'][-1]);
					$oldID = -1;
				}	
			}
		}
		else{
			$session->set('vcmbcarcontents'.$roomid,json_encode($content_array));
			//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomid);
		}

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcarcont".$roomid."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			//echo "<strong>Printing Old Data: </strong><pre>".print_r($dbo->loadAssoc(),true)."</pre>";
			$q="UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($content_array))." WHERE `param`='bcarcont".$roomid."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}else {
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcarcont".$roomid."',".$dbo->quote(json_encode($content_array)).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		if($oldID==-1&&!empty($content_array['Room'][$roomid])){
			$roomValues = $roomid."-".$roomName."-".$channel['params']['hotelid'];
			$session->set('newroominfo',$roomValues);
			$mainframe->enqueueMessage(JText::_('VCMBCARCNEWSUCC'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcapnotif&selected-option=".$roomValues."&newRoom=1");
		}
		else if(!array_key_exists('Warnings', $xmlResponse)&&!empty($xmlResponse)){
			$mainframe->enqueueMessage(JText::_('VCMBCAUPDATESUCC'));
		}

		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomValues);
	}

	public function makeProductXML(){
		$dbo = JFactory::getDbo();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		
		$mainframe = JFactory::getApplication();

		$e4jc_url="https://e4jconnect.com/channelmanager/?r=bcapn&c=".$channel['name'];

		$e4j_debug = VikRequest::getInt("e4j_debug");

		if($e4j_debug){
			echo "<strong>Request: </strong><pre>".print_r($_REQUEST,true)."</pre>";
		}

		$actionSelected = VikRequest::getString("selected-action");
		$ratePlans = VikRequest::getVar("rate-plans", array());
		$cancelPolicy = VikRequest::getInt("cancel-policy");
    	$mealPlans = VikRequest::getVar("meal-plans",array());
    	$minOffset = VikRequest::getInt("min-offset");
    	$minOffsetType = VikRequest::getString("min-offsetType");
    	$maxOffset = VikRequest::getInt("max-offset");
    	$maxOffsetType = VikRequest::getString("max-offsetType");
    	$maxOccupancy = VikRequest::getInt("max-occupancy");
    	$productRatePlan = VikRequest::getString("productRatePlan");
    	$newRoom = VikRequest::getInt("new-room");

    	$errorRecieved = false;

    	$contentNotifType = "New";

    	if(!empty($actionSelected)&&$actionSelected=="--"){
    		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcapnotif");
    		exit;
    	}

    	$redirectURL = "index.php?option=com_vikchannelmanager&task=bcapnotif&selected-option=".$actionSelected;

    	if($newRoom){
    		$redirectURL.="&newRoom=\"".$newRoom."\"";
    	}

    	//echo "<strong>Action Selected: </strong><pre>".print_r($actionSelected,true)."</pre>";
    	$actionSelected = explode("-", $actionSelected);
    	//echo "<strong>Action Selected Array: </strong><pre>".print_r($actionSelected,true)."</pre>";

    	$roomID = $actionSelected[0];
    	$roomName = $actionSelected[1];
    	$hotelID = $actionSelected[2];

    	/*$q = "DELETE FROM `#__vikchannelmanager_config` WHERE `param`='bcapnotif".$roomID."';";
		$dbo->setQuery($q);
		$dbo->execute();
		die;*/

    	$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcapnotif".$roomID."';";
		$dbo->setQuery($q);
		$dbo->execute();
		//echo "<pre>".print_r($_REQUEST, true)."</pre>";die;
		if ($dbo->getNumRows() > 0) {
			$oldData = json_decode($dbo->loadAssoc()['setting'],true);
		}

		$composedDate = date("Y-m-d H:i:s");

    	$session = JFactory::getSession();
		$session->set('vcmbcapnotif'.$roomID,'');

    	$content_array = $oldData;
    	if(empty($content_array)){
    		$content_array = array();
    	}
    	$session_array = array();

    	if(!empty($productRatePlan)&&empty($e4j_debug)){
    		//compose XML
    		$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
			<!-- BCAPN Request e4jConnect.com - VikChannelManager - VikBooking -->
			<BCAProductNotifRQ xmlns=\"http://www.e4jconnect.com/avail/bcapnrq\">
				<Notify client=\"".JUri::root()."\"/>
				<Api key=\"".VikChannelManager::getApiKey()."\"/>
				<BCAProductNotif hotelid=\"".$hotelID."\">
					<HotelProducts  HotelCode=\"".$hotelID."\">
						<HotelProduct ProductNotifType=\"Remove\">
							<RoomTypes>
								<RoomType RoomTypeCode=\"".$roomID."\"/>
							</RoomTypes>
							<RatePlans>
								<RatePlan RatePlanCode=\"".$productRatePlan."\"/>
							</RatePlans>
						</HotelProduct>
					</HotelProducts>
				</BCAProductNotif>
			</BCAProductNotifRQ>";
    		//Send XML
    		$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xmlFinal);
			$rs = $e4jC->exec();

			if($e4jC->getErrorNo()) {
				$session->set('vcmbcapnotif'.$roomID,json_encode($session_array));
				VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
				$errorRecieved = true;
				//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomID);
			}
			else{
				if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
					$session->set('vcmbcapnotif'.$roomID,json_encode($session_array));
					VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
					$errorRecieved = true;
					//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomID);
				}
				else{
					$xmlResponse = unserialize($rs);
				}
			}

			if(array_key_exists('Warnings', $xmlResponse)){
				$mainframe->enqueueMessage($xmlResponse['Warnings'],'warning');
			}

			if(empty($xmlResponse)){
				$session->set('vcmbcapnotif'.$roomID,json_encode($session_array));
				$errorRecieved = true;
				//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomID);
			}

			//UNSET IN DATABASE
			if(!$errorRecieved){

				unset($content_array['Connect'][$roomID][$productRatePlan]);

				$q="UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($content_array))." WHERE `param`='bcapnotif".$roomID."';";
				$dbo->setQuery($q);
				$dbo->execute();

				if(!array_key_exists('Warnings', $xmlResponse)){
					$mainframe->enqueueMessage(JText::_('VCMBCAUPDATESUCC'));
				}
			}

			$mainframe->redirect($redirectURL);
    	}

    	if(count($ratePlans)){
    		if(!empty($oldData['Connect'][$roomID][$ratePlan])){
    			$contentNotifType = "Overlay";
    		}
    		foreach ($ratePlans as $ratePlan) {
    			$content_array['Connect'][$roomID][$ratePlan]["date"] = $composedDate;
    			$content_array['Connect'][$roomID][$ratePlan]["maxOccupancy"] = $maxOccupancy;
    			unset($content_array['Connect'][$roomID][$ratePlan]["mealPlans"]);
    			foreach ($mealPlans as $mealPlan) {
    				$content_array['Connect'][$roomID][$ratePlan]["mealPlans"][] = $mealPlan;
    			}
		    	$content_array['Connect'][$roomID][$ratePlan]["cancelPolicy"] = $cancelPolicy;
		    	$content_array['Connect'][$roomID][$ratePlan]["minOffset"] = $minOffset;
				$content_array['Connect'][$roomID][$ratePlan]["minOffsetType"] = $minOffsetType;
				$content_array['Connect'][$roomID][$ratePlan]["maxOffset"] = $maxOffset;
			    $content_array['Connect'][$roomID][$ratePlan]["maxOffsetType"] = $maxOffsetType;
			    $session_array['Connect'][$roomID]["ratePlans"][] = $ratePlan;
    		}
    		$session_array['Connect'][$roomID]["maxOccupancy"] = $maxOccupancy;
    		foreach ($mealPlans as $mealPlan) {
				$session_array['Connect'][$roomID]["mealPlans"][] = $mealPlan;
			}
	    	$session_array['Connect'][$roomID]["cancelPolicy"] = $cancelPolicy;
	    	$session_array['Connect'][$roomID]["minOffset"] = $minOffset;
			$session_array['Connect'][$roomID]["minOffsetType"] = $minOffsetType;
			$session_array['Connect'][$roomID]["maxOffset"] = $maxOffset;
		    $session_array['Connect'][$roomID]["maxOffsetType"] = $maxOffsetType;
		}

		$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n
			<!-- BCAPN Request e4jConnect.com - VikChannelManager - VikBooking -->\n
			<BCAProductNotifRQ xmlns=\"http://www.e4jconnect.com/avail/bcapnrq\">\n
				<Notify client=\"".JUri::root()."\"/>\n
				<Api key=\"".VikChannelManager::getApiKey()."\"/>\n
				<BCAProductNotif hotelid=\"".$hotelID."\">
					<HotelProducts  HotelCode=\"".$hotelID."\">
						<HotelProduct ProductNotifType=\"".$contentNotifType."\">
							<RoomTypes><RoomType RoomTypeCode=\"".$roomID."\"";
    	if(!empty($maxOccupancy)){
      		$xmlFinal.=" MaxOccupancy=\"".$maxOccupancy."\"";
      	}
      	$xmlFinal.="/></RoomTypes>";
		if(count($ratePlans)){
			$xmlFinal.="<RatePlans>";
			foreach ($ratePlans as $value) {
				$xmlFinal.="<RatePlan RatePlanCode=\"".$value."\"/>";
			}
			$xmlFinal.="</RatePlans>";
		}
		if(count($mealPlans)){
			$xmlFinal.="<ValueAddInclusions>";
			foreach ($mealPlans as $value) {
				$xmlFinal.="<MealPlan MealPlanCode=\"".$value."\"/>";
			}
			$xmlFinal.="</ValueAddInclusions>";
		}
		if(!empty($cancelPolicy)||!empty($minOffset)||!empty($maxOffset)){
			$xmlFinal.="<PolicyInfo>";
    		if(!empty($cancelPolicy)){
    			$xmlFinal.="<CancelPolicy><CancelPenalty PolicyCode=\"".$cancelPolicy."\"/></CancelPolicy>";
    		}
    		if(!empty($maxOffset)||!empty($minOffset)){
    			$xmlFinal.="<BookingRules>";
	    		if(!empty($minOffset)){
	    			$xmlFinal.="<BookingRule MinAdvancedBookingOffset=\"P".$minOffset.$minOffsetType."\"/>";
	    		}
	    		if(!empty($maxOffset)){
	    			$xmlFinal.="<BookingRule MaxAdvancedBookingOffset=\"P".$maxOffset.$maxOffsetType."\"/>";
	    		}
	    		$xmlFinal.="</BookingRules>";
    		}
    		$xmlFinal.="</PolicyInfo>";
    	}
    	$xmlFinal.="</HotelProduct></HotelProducts></BCAProductNotif></BCAProductNotifRQ>";

    	if((!count($ratePlans)||empty($cancelPolicy)||!count($mealPlans)||empty($maxOccupancy))&&empty($oldData['Connect'][$roomID][$rateplan])){
    		$session->set('vcmbcapnotif'.$roomID,json_encode($session_array));
    		if($e4j_debug){
	    		echo "</br>Not Count Rate Plans: ";
	    		echo !count($ratePlans)? "true" : "false";
	    		echo "</br>Empty Cancel Policy: ";
	    		echo empty($cancelPolicy)? "true" : "false";
	    		echo "</br>Not Count Meal Plan: ";
	    		echo !count($mealPlans)? "true" : "false";
	    		echo "</br>Empty Max Occupancy: ";
	    		echo empty($maxOccupancy)? "true" : "false";
	    		echo "</br><strong>empty!</strong></br>";
	    		die;
	    	}
    		$mainframe->enqueueMessage(JText::_('VCMBCAPNEMPTY'),'error');
			$mainframe->redirect($redirectURL);
    	}

    	$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlFinal);
		$rs = $e4jC->exec();

		if($e4jC->getErrorNo()) {
			$session->set('vcmbcapnotif'.$roomID,json_encode($session_array));
			VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
			$errorRecieved = true;
			//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomID);
		}
		else{
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$session->set('vcmbcapnotif'.$roomID,json_encode($session_array));
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$errorRecieved = true;
				//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomID);
			}
			else{
				$xmlResponse = unserialize($rs);
			}
		}

		if(array_key_exists('Warnings', $xmlResponse)){
			$mainframe->enqueueMessage($xmlResponse['Warnings'],'warning');
		}

		if(empty($xmlResponse)){
			$session->set('vcmbcapnotif'.$roomID,json_encode($session_array));
			$errorRecieved = true;
			//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcarcont&action-option=".$roomID);
		}
		if(!$errorRecieved){

			$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcapnotif".$roomID."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$q="UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($content_array))." WHERE `param`='bcapnotif".$roomID."';";
				$dbo->setQuery($q);
				$dbo->execute();
			}else {
				$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcapnotif".$roomID."',".$dbo->quote(json_encode($content_array)).");";
				$dbo->setQuery($q);
				$dbo->execute();
			}

			if($newRoom){
				$session->set('newroominfo','');
				$mainframe->enqueueMessage(JText::_('VCMBCAPNNEWSUCC'));
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=roomsynch");
			}
			else if(!array_key_exists('Warnings', $xmlResponse)){
				$mainframe->enqueueMessage(JText::_('VCMBCAUPDATESUCC'));
			}
		}

    	if($e4j_debug){
	    	echo "<strong>Sent XML: </strong><pre>".print_r(htmlentities($xmlFinal),true)."</pre>";
	    	echo "<strong>Saved Data: </strong><pre>".print_r($content_array,true)."</pre>";
	    	echo "<strong>Unserialized Response: </strong><pre>".print_r(htmlentities($xmlResponse),true)."</pre>";
	    	die;
    	}

    	$mainframe->redirect($redirectURL);
	}

	public function runHotelSummary(){

		$response = 'e4j.error.';

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		//echo "<strong>Testing</strong></br>";

		$e4jc_url="https://e4jconnect.com/channelmanager/?r=bcahs&c=".$channel['name'];

		$session = JFactory::getSession();

		$summaryType = VikRequest::getString('sumtype');
		$hotelID = VikRequest::getString('val');
		$debug_mode = VikRequest::getInt('e4j_debug');
		/*echo $summaryType." ".$hotelID;
		exit;*/
		$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<!-- BCAHS Request e4jConnect.com - VikChannelManager - VikBooking -->
		<BCAHotelSummaryRQ xmlns=\"http://www.e4jconnect.com/avail/bcahsrq\">
			<Notify client=\"".JUri::root()."\" />
			<Api key=\"".VikChannelManager::getApiKey()."\" />
			<BCAHotelSummary hotelid=\"".$hotelID."\">
				<HotelSummaryMessages>
					<HotelSummaryMessage StatusType=\"".$summaryType."\" />
				</HotelSummaryMessages>
			</BCAHotelSummary>
		</BCAHotelSummaryRQ>";

		if($debug_mode) {
			$response .= "<strong>Sent XML: </strong><pre>".htmlentities($xmlFinal)."</pre>";
			$response .= "<strong>Hotel ID: </strong><pre>".$hotelID."</pre>";
			$response .= "<strong>Summary Type: </strong><pre>".$summaryType."</pre>";
		}

		$session->set('vcmbcahsum',$hotelID);

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlFinal);
		$rs = $e4jC->exec();

		if($debug_mode) {
			$response .= "<strong>Response: </strong><pre>".$rs."</pre>";
		}

		if($e4jC->getErrorNo()) {
			$response = 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl.'.$e4jC->getErrorNo().' '.$e4jC->getErrorMsg());
		}
		else{
			if($debug_mode) {
				$response = 'e4j.error.'.$response;
			}
			else if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$response = 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
			}
			else {
				$response = unserialize($rs);
				$response = is_array($response) ? json_encode($response) : 'e4j.error.Failed to unserialize response';
			}
		}
		echo $response;
		exit;
	}

	public function runHotelSearch(){

		$response = 'e4j.error.';

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		//echo "<strong>Testing</strong></br>";

		$e4jc_url="https://e4jconnect.com/channelmanager/?r=bcacs&c=".$channel['name'];

		$session = JFactory::getSession();

		$hotelID = VikRequest::getString('val');
		$debug_mode = VikRequest::getInt('e4j_debug');
		/*echo $summaryType." ".$hotelID;
		exit;*/
		$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<!-- BCACS Request e4jConnect.com - VikChannelManager - VikBooking -->
		<BCACheckStatusRQ xmlns=\"http://www.e4jconnect.com/avail/bcacsrq\">
			<Notify client=\"".JUri::root()."\" />
			<Api key=\"".VikChannelManager::getApiKey()."\" />
			<BCACheckStatus hotelid=\"".$hotelID."\">
				<Criteria>
		    		<Criterion>
		      	  		<HotelRef HotelCode=\"".$hotelID."\"/>
		    		</Criterion>
		  		</Criteria>
			</BCACheckStatus>
		</BCACheckStatusRQ>";

		if($debug_mode) {
			$response .= "<strong>Sent XML: </strong><pre>".htmlentities($xmlFinal)."</pre>";
			$response .= "<strong>Hotel ID: </strong><pre>".$hotelID."</pre>";
		}

		$session->set('vcmbcahsum',$hotelID);

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlFinal);
		$rs = $e4jC->exec();

		if($debug_mode) {
			$response .= "<strong>Response: </strong><pre>".$rs."</pre>";
		}

		if($e4jC->getErrorNo()) {
			$response = 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl.'.$e4jC->getErrorNo().' '.$e4jC->getErrorMsg());
		}
		else{
			if($debug_mode) {
				$response = 'e4j.error.'.$response;
			}
			else if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$response = 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
			}
			else {
				$response = unserialize($rs);
				$response = is_array($response) ? json_encode($response) : 'e4j.error.Failed to unserialize response';
			}
		}
		echo $response;
		exit;
	}

	public function readHotelInfo () {

		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		$dbo = JFactory::getDbo();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$e4jc_url="https://e4jconnect.com/channelmanager/?r=bcahi&c=".$channel['name'];

		$xmlFinal = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n
<!-- BCAHI Request e4jConnect.com - E4J srl -->\n
<BCAHotelInfoRQ xmlns=\"http://www.e4jconnect.com/channels/bcahirq\">\n
	<Notify client=\"".JUri::root()."\"/>\n
	<Api key=\"".VikChannelManager::getApiKey()."\"/>\n
	<BCAHotelInfo>
		<Fetch hotelid=\"".VikRequest::getString("hotelid")."\"/>
	</BCAHotelInfo>
</BCAHotelInfoRQ>";

		//Send xml to server
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlFinal);
		$rs = $e4jC->exec();

		$xmlResponse = unserialize($rs);

		if($e4jC->getErrorNo()) {
			VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
		}
		else{
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			}
			else{
				$xmlResponse = unserialize($rs);
			}
		}
		//
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			//$mainframe->redirect("index.php?option=com_vikchannelmanager&task=bcahcont&tab=$submittedform");
			//exit;
		}
		//

		if (array_key_exists('Warnings', $xmlResponse)){
			$mainframe->enqueueMessage($xmlResponse['Warnings'],'warning');
		}

		if (count($xmlResponse)){
			$hotelDescriptiveContent = $xmlResponse->HotelDescriptiveContent;
			$jsonData["insertType"] = "Overlay";
			if (property_exists($hotelDescriptiveContent, "HotelName")) {
				$jsonData["physical_location"]["address"]["hotelName"] = $hotelDescriptiveContent->HotelName;
			}

			//Hotel Info
			if (property_exists($hotelDescriptiveContent, "HotelInfo")) {
				$hotelInfo = $hotelDescriptiveContent->HotelInfo;
				if (property_exists($hotelInfo, "CategoryCodes")) {
					if (property_exists($hotelInfo->CategoryCodes, "GuestRoomInfo")) {
						$jsonData["guestRoomQuantity"] = (int)$hotelInfo->CategoryCodes->GuestRoomInfo->Quantity;
					}
					if (property_exists($hotelInfo->CategoryCodes, "HotelCategory")) {
						$jsonData["hotelType"] = (int)$hotelInfo->CategoryCodes->HotelCategory->Code;
					}
				}
				if (property_exists($hotelInfo, "Position")) {
					if (property_exists($hotelInfo->Position, "Longitude")) {
						$jsonData["longitude"] = $hotelInfo->Position->Longitude;
					}
					if (property_exists($hotelInfo->Position, "Latitude")) {
						$jsonData["latitude"] = $hotelInfo->Position->Latitude;
					}
				}
				if (property_exists($hotelInfo, "Services")) {
					if (is_array($hotelInfo->Services->Service)) {
						foreach ($hotelInfo->Services->Service as $index => $service) {
							$jsonData["servicesIndexes"][$index] = (string)($index+1);
							$jsonData["services"][(string)($index+1)]["code"] = (int)$service->Code;
						}
					} else {
						$jsonData["servicesIndexes"][0] = "1";
						$jsonData["services"]["1"]["code"] = (int)$hotelInfo->Services->Service->Code;
					}
				}
				if (property_exists($hotelInfo, "TPA_Extensions")) {
					if (property_exists($hotelInfo->TPA_Extensions, "AcceptedPayments")) {
						if (is_array($hotelInfo->TPA_Extensions->AcceptedPayments->AcceptedPayment)) {
							foreach ($hotelInfo->TPA_Extensions->AcceptedPayments->AcceptedPayment as $index => $acceptedPayment) {
								$jsonData["paymentmethodsIndexes"][$index] = (string)($index+1);
								$jsonData["paymentmethods"][(string)($index+1)]["paymentMethod"] = (string)$acceptedPayment->PaymentTypeCode;
							}
						} else {
							$jsonData["paymentmethodsIndexes"][0] = "1";
							$jsonData["paymentmethods"]["1"]["paymentMethod"] = (string)$hotelInfo->TPA_Extensions->AcceptedPayments->AcceptedPayment->PaymentTypeCode;
						}
					}
				}
			}
			
			//Policies
			if (property_exists($hotelDescriptiveContent, "Policies") && property_exists($hotelDescriptiveContent->Policies, "Policy")) {
				$policy = $hotelDescriptiveContent->Policies->Policy;
				if (property_exists($policy, "PolicyInfo")) {
					$jsonData["checkInTimeStart"] = $policy->PolicyInfo->CheckInTime;
					$jsonData["checkOutTimeEnd"] = $policy->PolicyInfo->CheckOutTime;
				}
				if (property_exists($policy, "PetsPolicy")) {
					$jsonData["petsAllowedCode"] = $policy->PetsPolicy->PetsAllowedCode;
				}
				if (property_exists($policy, "CancelPolicy")) {
					if (is_array($policy->CancelPolicy->CancelPenalty)) {
						foreach ($policy->CancelPolicy->CancelPenalty as $index => $cancelPenalty) {
							$jsonData["cancelpoliciesIndexes"][$index] = (string)($index+1);
							$jsonData["cancelpolicies"][(string)($index+1)]["cancelpolicy"] = (string)$cancelPenalty->PolicyCode;
						}
					} else {
						$jsonData["cancelpoliciesIndexes"][0] = "1";
						$jsonData["cancelpolicies"]["1"]["cancelpolicy"] = (string)$policy->CancelPolicy->CancelPenalty->PolicyCode;
					}
				}
				if (property_exists($policy, "GuaranteePaymentPolicy")) {
					if (is_array($policy->GuaranteePaymentPolicy->GuaranteePayment)) {
						foreach ($policy->GuaranteePaymentPolicy->GuaranteePayment as $index => $guaranteePayment) {
							$jsonData["guaranteepaymentsIndexes"][$index] = (string)($index+1);
							$jsonData["guaranteepayments"][(string)($index+1)]["guaranteepayment"] = (string)$guaranteePayment->PolicyCode;
						}
					} else {
						$jsonData["guaranteepaymentsIndexes"][0] = "1";
						$jsonData["guaranteepayments"]["1"]["guaranteepayment"] = (string)$policy->GuaranteePaymentPolicy->GuaranteePayment->PolicyCode;
					}
				}
				if (property_exists($policy, "TaxPolicies")) {
					if (is_array($policy->TaxPolicies->TaxPolicy)) {
						foreach ($policy->TaxPolicies->TaxPolicy as $index => $taxPolicy) {
							$jsonData["taxesIndexes"][$index] = (string)($index+1);
							$jsonData["taxes"][(string)($index+1)]["code"] = (string)$taxPolicy->Code;
							$jsonData["taxes"][(string)($index+1)]["amount"] = (int)$taxPolicy->Amount;
							$jsonData["taxes"][(string)($index+1)]["type"] = (string)$taxPolicy->Type;
							$jsonData["taxes"][(string)($index+1)]["chargeFrequency"] = (string)$taxPolicy->ChargeFrequency;
						}
					} else {
						$jsonData["taxesIndexes"][0] = "1";
						$jsonData["taxes"][(string)($index+1)]["code"] = (string)$policy->TaxPolicies->TaxPolicy->Code;
						$jsonData["taxes"][(string)($index+1)]["amount"] = (int)$policy->TaxPolicies->TaxPolicy->Amount;
						$jsonData["taxes"][(string)($index+1)]["type"] = (string)$policy->TaxPolicies->TaxPolicy->Type;
						$jsonData["taxes"][(string)($index+1)]["chargeFrequency"] = (string)$policy->TaxPolicies->TaxPolicy->ChargeFrequency;
					}
				}
				if (property_exists($policy, "FeePolicies")) {
					if (is_array($policy->FeePolicies->FeePolicy)) {
						foreach ($policy->FeePolicies->FeePolicy as $index => $feePolicy) {
							$jsonData["feesIndexes"][$index] = (string)($index+1);
							$jsonData["fees"][(string)($index+1)]["code"] = (string)$feePolicy->Code;
							$jsonData["fees"][(string)($index+1)]["amount"] = (int)$feePolicy->Amount;
							$jsonData["fees"][(string)($index+1)]["type"] = (string)$feePolicy->Type;
							$jsonData["fees"][(string)($index+1)]["chargeFrequency"] = (string)$feePolicy->ChargeFrequency;
						}
					} else {
						$jsonData["feesIndexes"][0] = "1";
						$jsonData["fees"][(string)($index+1)]["code"] = (string)$policy->FeePolicies->FeePolicy->Code;
						$jsonData["fees"][(string)($index+1)]["amount"] = (int)$policy->FeePolicies->FeePolicy->Amount;
						$jsonData["fees"][(string)($index+1)]["type"] = (string)$policy->FeePolicies->FeePolicy->Type;
						$jsonData["fees"][(string)($index+1)]["chargeFrequency"] = (string)$policy->FeePolicies->FeePolicy->ChargeFrequency;
					}
				}
			}

			//Contact Info
			if (property_exists($hotelDescriptiveContent, "ContactInfos") && property_exists($hotelDescriptiveContent->ContactInfos, "ContactInfo")) {
				foreach ($hotelDescriptiveContent->ContactInfos->ContactInfo as $index => $contactInfo) {
					if ($contactInfo->ContactProfileType == "PhysicalLocation") {
						if (is_array($contactInfo->Addresses->Address)) {
							foreach ($contactInfo->Addresses->Address as $address_index => $address) {
								if (property_exists($address, "CountryName")) {
									$jsonData["physical_location"]["address"]["language"] = $address->CountryName;
								}
								if (property_exists($address, "HotelName")) {
									$jsonData["physical_location"]["address"]["hotelName"] = $address->HotelName;
								}
								if (property_exists($address, "AddressLine")) {
									$jsonData["physical_location"]["address"]["addressLine"] = $address->AddressLine;
								}
								if (property_exists($address, "CityName")) {
									$jsonData["physical_location"]["address"]["cityName"] = $address->CityName;
								}
								if (property_exists($address, "CountryName")) {
									$jsonData["physical_location"]["address"]["country"] = $address->CountryName;
								}
								if (property_exists($address, "PostalCode")) {
									$jsonData["physical_location"]["address"]["postalCode"] = $address->PostalCode;
								}
							}
						} else {
							if (property_exists($contactInfo->Addresses->Address, "CountryName")) {
								$jsonData["physical_location"]["address"]["language"] = $contactInfo->Addresses->Address->CountryName;
							}
							if (property_exists($contactInfo->Addresses->Address, "HotelName")) {
								$jsonData["physical_location"]["address"]["hotelName"] = $contactInfo->Addresses->Address->HotelName;
							}
							if (property_exists($contactInfo->Addresses->Address, "AddressLine")) {
								$jsonData["physical_location"]["address"]["addressLine"] = $contactInfo->Addresses->Address->AddressLine;
							}
							if (property_exists($contactInfo->Addresses->Address, "CityName")) {
								$jsonData["physical_location"]["address"]["cityName"] = $contactInfo->Addresses->Address->CityName;
							}
							if (property_exists($contactInfo->Addresses->Address, "CountryName")) {
								$jsonData["physical_location"]["address"]["country"] = $contactInfo->Addresses->Address->CountryName;
							}
							if (property_exists($contactInfo->Addresses->Address, "PostalCode")) {
								$jsonData["physical_location"]["address"]["postalCode"] = $contactInfo->Addresses->Address->PostalCode;
							}
						}
					} else {
						$contactType = $contactInfo->ContactProfileType;
						if (property_exists($contactInfo, "Names")) {
							$jsonData[$contactType]["person"]["language"] = $contactInfo->Names->Name->Language;
							$jsonData[$contactType]["person"]["gender"] = ucfirst($contactInfo->Names->Name->Gender);
							$jsonData[$contactType]["person"]["firstName"] = explode(' ', $contactInfo->Names->Name->_value)[0];
							$jsonData[$contactType]["person"]["surname"] = implode(' ', array_diff_key(explode(' ', $contactInfo->Names->Name->_value), array(0)));
						}
						if (property_exists($contactInfo, "Addresses")) {
							$jsonData[$contactType]["address"]["addressLine"] = $contactInfo->Addresses->Address->AddressLine;
							$jsonData[$contactType]["address"]["cityName"] = $contactInfo->Addresses->Address->CityName;
							$jsonData[$contactType]["address"]["postalCode"] = $contactInfo->Addresses->Address->PostalCode;
							$jsonData[$contactType]["address"]["country"] = strtoupper($contactInfo->Addresses->Address->CountryName);
						}
						if (property_exists($contactInfo, "Emails")) {
							$jsonData[$contactType."EmailsIndexes"][0] = "1";
							$jsonData[$contactType]["emails"]["1"]["email"] = $contactInfo->Emails->Email;
						}
						if (property_exists($contactInfo, "Phones")) {
							if (is_array($contactInfo->Phones->Phone)) {
								foreach ($contactInfo->Phones->Phone as $phone_index => $phone) {
									$jsonData[$contactType."PhonesIndexes"][$phone_index] = (string)($phone_index+1);
									$jsonData[$contactType]["phones"][(string)($phone_index+1)]["phoneNumber"] = $phone->PhonesNumber;
									$jsonData[$contactType]["phones"][(string)($phone_index+1)]["phoneTechType"] = $phone->PhoneTechType;
								}
							} else {
								$jsonData[$contactType."PhonesIndexes"][0] = "1";
								$jsonData[$contactType]["phones"][0]["phoneNumber"] = $contactInfo->Phones->Phone->PhonesNumber;
								$jsonData[$contactType]["phones"][0]["phoneTechType"] = $contactInfo->Phones->Phone->PhoneTechType;
							}
						}
					}
				}
			}

			//Guest Rooms
			if (property_exists($hotelDescriptiveContent, "FacilityInfo") && property_exists($hotelDescriptiveContent->FacilityInfo, "GuestRooms") && property_exists($hotelDescriptiveContent->FacilityInfo->GuestRooms, "GuestRoom")) {
				$guestRoomElement = $hotelDescriptiveContent->FacilityInfo->GuestRooms->GuestRoom;
				if (is_array($guestRoomElement)) {
					foreach ($guestRoomElement as $room_index => $guestRoom) {
						$roomJsonData = array();
						$roomJsonData["Room"] = array();
						$roomJsonData["Room"][$guestRoom->ID] = array();
						$roomJsonData["Room"][$guestRoom->ID]["hotelid"] = VikRequest::getString("hotelid");
						if (property_exists($guestRoom, "MaxOccupancy")) {
							$roomJsonData["Room"][$guestRoom->ID]["maxOccupancy"] = $guestRoom->MaxOccupancy;
						}
						if (property_exists($guestRoom, "RoomTypeName")) {
							$roomJsonData["Room"][$guestRoom->ID]["roomType"] = $guestRoom->RoomTypeName;
						}
						if (property_exists($guestRoom, "DescriptiveText")) {
							$roomJsonData["Room"][$guestRoom->ID]["roomName"] = $guestRoom->DescriptiveText;
						}
						if (property_exists($guestRoom, "Active")) {
							$roomJsonData["Room"][$guestRoom->ID]["status"] = ($guestRoom->Active) ? "Active" : "Deactivated";
						}
						if (property_exists($guestRoom, "Amenity")) {
							if (is_array($guestRoom->Amenity)) {
								foreach ($guestRoom->Amenity as $amenity_index => $amenity) {
									$roomJsonData["Room"][$guestRoom->ID]["amenity-index"][$amenity_index] = (string)($amenity_index+1);
									$roomJsonData["Room"][$guestRoom->ID]["amenities"][(string)($amenity_index+1)]["selectedAmenity"] = $amenity->RoomAmenityCode;
								}
							} else {
								$roomJsonData["Room"][$guestRoom->ID]["amenity-index"][0] = "1";
								$roomJsonData["Room"][$guestRoom->ID]["amenities"]["1"]["selectedAmenity"] = $guestRoom->Amenity->RoomAmenityCode;
							}
						}
						$q = "SELECT `id` FROM `#__vikchannelmanager_config` WHERE `param` = 'bcarcont".$guestRoom->ID."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$q = "UPDATE `#__vikchannelmanager_config` SET `setting` = '".json_encode($roomJsonData)."' WHERE `param` = 'bcarcont".$guestRoom->ID."';";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getAffectedRows() <= 0) {
								$mainframe->enqueueMessage(JText::sprintf('VCMBCAHIROOMUPDATEFAIL', $guestRoom->ID),'error');
							} else {
								$session->set("bcarcont".$guestRoom->ID,json_encode($roomJsonData));
								$mainframe->enqueueMessage(JText::sprintf('VCMBCAHIROOMUPDATESUCCESS', $guestRoom->ID));
							}
						} else {
							$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcarcont".$guestRoom->ID."', '".json_encode($roomJsonData)."');";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getAffectedRows() <= 0) {
								$mainframe->enqueueMessage(JText::sprintf('VCMBCAHIROOMINSERTFAIL', $guestRoom->ID),'error');
							} else {
								$session->set("bcarcont".$guestRoom->ID,json_encode($roomJsonData));
								$mainframe->enqueueMessage(JText::sprintf('VCMBCAHIROOMINSERTSUCCESS', $guestRoom->ID));
							}
						}
					}
				} else {
					$roomJsonData = array();
					$roomJsonData["Room"] = array();
					$roomJsonData["Room"][$guestRoomElement->ID] = array();
					$roomJsonData["Room"][$guestRoomElement->ID]["hotelid"] = VikRequest::getString("hotelid");
					if (property_exists($guestRoomElement, "MaxOccupancy")) {
						$roomJsonData["Room"][$guestRoomElement->ID]["maxOccupancy"] = $guestRoomElement->MaxOccupancy;
					}
					if (property_exists($guestRoomElement, "RoomTypeName")) {
						$roomJsonData["Room"][$guestRoomElement->ID]["roomType"] = $guestRoomElement->RoomTypeName;
					}
					if (property_exists($guestRoomElement, "DescriptiveText")) {
						$roomJsonData["Room"][$guestRoomElement->ID]["roomName"] = $guestRoomElement->DescriptiveText;
					}
					if (property_exists($guestRoomElement, "Active")) {
						$roomJsonData["Room"][$guestRoomElement->ID]["status"] = ($guestRoomElement->Active) ? "Active" : "Deactivated";
					}
					if (property_exists($guestRoomElement, "Amenity")) {
						if (is_array($guestRoomElement->Amenity)) {
							foreach ($guestRoomElement->Amenity as $amenity_index => $amenity) {
								$roomJsonData["Room"][$guestRoomElement->ID]["amenity-index"][$amenity_index] = (string)($amenity_index+1);
								$roomJsonData["Room"][$guestRoomElement->ID]["amenities"][(string)($amenity_index+1)]["selectedAmenity"] = $amenity->RoomAmenityCode;
							}
						} else {
							$roomJsonData["Room"][$guestRoomElement->ID]["amenity-index"][0] = "1";
							$roomJsonData["Room"][$guestRoomElement->ID]["amenities"]["1"]["selectedAmenity"] = $guestRoomElement->Amenity->RoomAmenityCode;
						}
					}
					$q = "SELECT `id` FROM `#__vikchannelmanager_config` WHERE `param` = 'bcarcont".$guestRoomElement->ID."';";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$q = "UPDATE `#__vikchannelmanager_config` SET `setting` = '".json_encode($roomJsonData)."' WHERE `param` = 'bcarcont".$guestRoomElement->ID."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getAffectedRows() <= 0) {
							$mainframe->enqueueMessage(JText::sprintf('VCMBCAHIROOMUPDATEFAIL', $guestRoomElement->ID),'error');
						} else {
							$session->set("bcarcont".$guestRoomElement->ID,json_encode($roomJsonData));
							$mainframe->enqueueMessage(JText::sprintf('VCMBCAHIROOMUPDATESUCCESS', $guestRoomElement->ID));
						}
					} else {
						$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcarcont".$guestRoomElement->ID."', '".json_encode($roomJsonData)."');";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getAffectedRows() <= 0) {
							$mainframe->enqueueMessage(JText::sprintf('VCMBCAHIROOMINSERTFAIL', $guestRoomElement->ID),'error');
						} else {
							$session->set("bcarcont".$guestRoomElement->ID,json_encode($roomJsonData));
							$mainframe->enqueueMessage(JText::sprintf('VCMBCAHIROOMINSERTSUCCESS', $guestRoomElement->ID));
						}
					}
				}
			}
		}

		$convertedData = json_encode($jsonData);

		if (isset($_GET['e4j_debug'])) {
			//E4J Debug
			echo "<strong>Unserialized server response</strong><pre>".print_r($xmlResponse,true)."</pre>";
			echo '<strong>Array Pre-JSON Data</strong><pre>'.print_r($jsonData, true).'</pre>';
			echo '<strong>JSON Data</strong><pre>'.print_r($convertedData, true).'</pre>';
			die();
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_config` WHERE `param` = 'bcahcont".VikRequest::getString("hotelid")."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting` = '".$convertedData."' WHERE `param` = 'bcahcont".VikRequest::getString("hotelid")."';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcahcont".VikRequest::getString("hotelid")."', '".$convertedData."');";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		if ($dbo->getAffectedRows() <= 0) {
			$mainframe->enqueueMessage(JText::_('VCMBCAHINODATA'),'error');
		} else {
			$session->set('vcmbcahcont'.$channel['params']['hotelid'],$convertedData);
			$mainframe->enqueueMessage(JText::_('VCMBCAHISUCCESS'));
		}
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bcahcont');
	}
}