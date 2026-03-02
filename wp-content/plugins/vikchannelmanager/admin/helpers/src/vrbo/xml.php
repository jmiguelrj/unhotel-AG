<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Vrbo XML feeds helper.
 * 
 * @since 	1.8.12
 */
final class VCMVrboXml
{
	/**
	 * A reference to the application object.
	 * 
	 * @var JApplication
	 */
	private $app;

	/**
	 * Whether check-ins on check-outs are allowed.
	 * 
	 * @var bool
	 */
	private $inonout_allowed = true;

	/**
	 * The check-in hours.
	 * 
	 * @var int
	 */
	private $checkin_h = 0;

	/**
	 * The check-in minutes.
	 * 
	 * @var int
	 */
	private $checkin_m = 0;

	/**
	 * The check-out hours.
	 * 
	 * @var int
	 */
	private $checkout_h = 0;

	/**
	 * The check-out minutes.
	 * 
	 * @var int
	 */
	private $checkout_m = 0;

	/**
	 * Whether rates are after tax.
	 * 
	 * @var bool
	 */
	private $tax_inclusive = false;

	/**
	 * Pool of mapped tax IDs to aliquotes for caching.
	 * 
	 * @var array
	 */
	private $cache_tax_rates = [];

	/**
	 * Whether cached XML files can be returned.
	 * 
	 * @var bool
	 */
	private $allow_cached_files = false;

	/**
	 * Proxy used to construct the object.
	 * 
	 * @param 	mixed  	$app 			The application instance. If not specified the
	 *                       			current one will be used.
	 * @param 	bool 	$cache_allowed 	True to allow cached files, or false.
	 * 
	 * @return 	self 	A new instance of this class.
	 */
	public static function getInstance($app = null, $cache_allowed = false)
	{
		if (!$app) {
			$app = JFactory::getApplication();
		}

		return new static($app, $cache_allowed);
	}

	/**
	 * Class constructor.
	 * 
	 * @param 	JApplication 	$app 			The application instance.
	 * @param 	bool 			$cache_allowed 	True to allowed cached files, or false.
	 */
	public function __construct($app, $cache_allowed = false)
	{
		$this->app = $app;
		$this->setAllowedCachedFiles($cache_allowed);
	}

	/**
	 * Renders (or returns) an XML document for the requested listing content type.
	 * In case of validation errors, an exception is thrown.
	 * 
	 * @param 	string 	$x_type 	 the requested type of XML document to process.
	 * @param 	array 	$channel 	 the channel information.
	 * @param 	string 	$listing_id	 the Vrbo listing ID to render.
	 * @param 	bool 	$render 	 true to output the XML, false to return it.
	 * 
	 * @return 	void|string 		 XML string if $render = false, or void.
	 * 
	 * @throws 	Exception
	 */
	public function processDocument($x_type, array $channel, $listing_id, $render = true)
	{
		$result = null;

		try {
			switch ($x_type) {
				case 'listing':
					$result = $this->renderListingContent($channel, $listing_id, $render);
					break;

				case 'listing_lodging_configuration':
					$result = $this->renderListingLodgingConfiguration($channel, $listing_id, $render);
					break;

				case 'listing_availability':
					$result = $this->renderListingAvailability($channel, $listing_id, $render);
					break;

				case 'listing_rate':
					$result = $this->renderListingRate($channel, $listing_id, $render);
					break;

				case 'booking_index':
					$result = $this->renderBookingIndex($channel, $render);
					break;

				case 'booking_update':
					$result = $this->renderBookingUpdate($channel, $render);
					break;

				default:
					throw new Exception('Unsupported XML type', 500);
			}
		} catch (Exception $e) {
			// throw the Exception
			throw $e;
		}

		if (!$render) {
			return $result;
		}
	}

	/**
	 * Outputs an XML error document compatible with Vrbo.
	 * 
	 * @param 	Exception 	$e 			the exception to build the error document.
	 * @param 	array 		$channel 	the channel information.
	 * @param 	string 		$listing_id the Vrbo listing ID requested.
	 * 
	 * @return 	void
	 */
	public function errorDocument(Exception $e, array $channel, $listing_id)
	{
		// start XML root node
		$xml_root = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<error></error>
XML;
		// get the SimpleXMLElement object
		$xml = new SimpleXMLElement($xml_root);

		// error code and message
		$xml->addChild('errorCode', $e->getCode());
		$xml->addChild('message', htmlspecialchars($e->getMessage()));

		// get XML string
		$xml_str = $xml->asXML();

		// format XML string feed
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml_str);
		$dom->formatOutput = true;
		$xml_str = $dom->saveXML();

		// output the XML document
		$this->outputDocument($xml_str);
	}

	/**
	 * Turns on or off the reading of cached XML files.
	 * 
	 * @param 	bool 	$allowed 	true to allowed cached files, or false.
	 * 
	 * @return 	self
	 */
	public function setAllowedCachedFiles($allowed = false)
	{
		$this->allow_cached_files = (bool) $allowed;

		return $this;
	}

	/**
	 * Renders (or returns) an XML document with the requested listing content.
	 * In case of validation errors, the execution is truncated with an error status code.
	 * 
	 * @param 	array 	$channel 	 the channel information.
	 * @param 	string 	$listing_id	 the Vrbo listing ID to render.
	 * @param 	bool 	$render 	 true to output the XML, false to return it.
	 * 
	 * @return 	void|string 		 XML string if $render = false, or void.
	 * 
	 * @throws 	Exception
	 */
	public function renderListingContent(array $channel, $listing_id, $render = true)
	{
		// load resources (in case of errors the execution will be truncated with an error document)
		list($account_key, $listing_record, $listing_data, $vbo_listing_record) = $this->loadListingResources($channel, $listing_id);

		// translator object and default language
		$tn_obj = VikBooking::getTranslator();
		$lang_code = $tn_obj->getDefaultLang();
		$lang_code = substr(strtolower($lang_code), 0, 2);

		// translate room record
		$tn_rooms = VCMVrboListing::getRoomTranslations($tn_obj, [$vbo_listing_record]);

		// wrap listing payload into a JObject instance
		$listing = new JObject($listing_data);

		// wrap Vik Booking listing object into a JObject
		$vbo_listing = new JObject($vbo_listing_record);

		/**
		 * For those who have a different website default language than the one required by Vrbo to be the
		 * default one, we allow to read the listing language locale from the VCM listing management interface.
		 * 
		 * @since 	1.8.27
		 */
		if ($forced_locale = $listing->get('forced_locale', '')) {
			$lang_code = strtolower($forced_locale);
		}

		// validate language code
		if (!isset(VCMVrboListing::$supported_locales[$lang_code])) {
			$lang_code = 'en';
		}

		// start XML root node
		$xml_root = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<listing>
	<externalId>$listing_id</externalId>
</listing>
XML;
		// get the SimpleXMLElement object
		$xml = new SimpleXMLElement($xml_root);

		$xml->addChild('active', ($listing->get('active') ? 'true' : 'false'));

		// start adContent node
		$adContent = $xml->addChild('adContent');

		// accommodations summary
		$accommodationsSummary = $adContent->addChild('accommodationsSummary');
		$this->addXmlTextLocaleValues($accommodationsSummary, $lang_code, $listing->get('accommodationsSummary'), 'smalldesc', $tn_rooms);

		// description
		$description = $adContent->addChild('description');
		$this->addXmlTextLocaleValues($description, $lang_code, $listing->get('description'), 'info', $tn_rooms);

		// headline
		$headline = $adContent->addChild('headline');
		$this->addXmlTextLocaleValues($headline, $lang_code, $listing->get('name'), 'name', $tn_rooms);

		// property name (must be translated if other fields were translated as well, even if this field does not support translations in VBO)
		$propertyName = $adContent->addChild('propertyName');
		$this->addXmlTextLocaleValues($propertyName, $lang_code, $listing->get('propertyName'), 'propertyName', $tn_rooms);
		
		// listing amenities
		$listing_amenities = (array) $listing->get('featureValues', []);
		if ($listing_amenities) {
			$featureValues = $xml->addChild('featureValues');
			foreach ($listing_amenities as $listing_amenity) {
				if (!is_array($listing_amenity) || empty($listing_amenity['listingFeatureName'])) {
					continue;
				}
				$count = !empty($listing_amenity['count']) ? (int) $listing_amenity['count'] : 1;
				$featureValue = $featureValues->addChild('featureValue');
				$featureValue->addChild('count', $count);
				$featureValue->addChild('listingFeatureName', htmlspecialchars($listing_amenity['listingFeatureName']));
			}
		}

		// location, address and geocode
		$location = $xml->addChild('location');
		$address = $location->addChild('address');
		$address->addChild('addressLine1', $listing->get('addressLine1'));
		$address->addChild('addressLine2', $listing->get('addressLine2', ''));
		$address->addChild('city', $listing->get('city'));
		if ($listing->get('stateOrProvince')) {
			$address->addChild('stateOrProvince', $listing->get('stateOrProvince'));
		}
		$address->addChild('country', $listing->get('country'));
		$address->addChild('postalCode', $listing->get('postalCode'));
		$geocode = $location->addChild('geoCode');
		$latlng  = $geocode->addChild('latLng');
		$latlng->addChild('latitude', $listing->get('latitude'));
		$latlng->addChild('longitude', $listing->get('longitude'));
		$showExactLocation = $listing->get('showExactLocation');
		$showExactLocation = is_string($showExactLocation) && $showExactLocation === 'false' ? false : $showExactLocation;
		if ($showExactLocation) {
			$location->addChild('showExactLocation', 'true');
		} else {
			$location->addChild('showExactLocation', 'false');
		}

		// images
		$listing_images = (array) $listing->get('images', []);
		if ($listing_images) {
			$images = $xml->addChild('images');
			foreach ($listing_images as $listing_image) {
				if (!is_array($listing_image) || empty($listing_image['url'])) {
					continue;
				}
				// check for translated captions
				$photo_base_name = basename($listing_image['url']);
				$tn_caption_key  = null;
				$tn_caption_list = [];
				if ($tn_rooms && !empty($listing_image['caption'])) {
					foreach ($tn_rooms as $tn_locale => $records) {
						foreach ($records as $tn_room) {
							if (empty($tn_room['moreimgs']) || empty($tn_room['imgcaptions'])) {
								break;
							}
							$orig_more_imgs = explode(';;', $tn_room['moreimgs']);
							$orig_captions  = json_decode($tn_room['imgcaptions']);
							if (!is_array($orig_captions) || !$orig_captions) {
								break;
							}
							// find matching photo file name
							foreach ($orig_more_imgs as $more_img_k => $orig_more_img) {
								if ("big_{$orig_more_img}" === $photo_base_name && !empty($orig_captions[$more_img_k]) && $listing_image['caption'] != $orig_captions[$more_img_k]) {
									// translation for wanted photo caption found
									$tn_caption_key = 'photo_caption';
									$tn_caption_list[$tn_locale] = [
										[
											$tn_caption_key => $orig_captions[$more_img_k],
										],
									];
									break 2;
								}
							}
							// we've got just one room record even though it's a list of records
							break;
						}
					}
				}

				// populate image nodes
				$image = $images->addChild('image');
				$image->addChild('externalId', htmlspecialchars($listing_image['id']));
				if (!empty($listing_image['caption'])) {
					$img_title = $image->addChild('title');
					$this->addXmlTextLocaleValues($img_title, $lang_code, $listing_image['caption'], $tn_caption_key, $tn_caption_list);
				}
				$image->addChild('uri', htmlspecialchars($listing_image['url']));
			}
		}

		// wrap unit object into a JObject object
		$listing_unit = new JObject($listing->get('unit'));

		// listing units (always just one unit)
		$units = $xml->addChild('units');
		$unit = $units->addChild('unit');
		$unit->addChild('externalId', $listing->get('id') . '-u');
		$unit->addChild('active', ($listing->get('active') ? 'true' : 'false'));
		$unit->addChild('area', $listing_unit->get('area'));
		$unit->addChild('areaUnit', $listing_unit->get('areaUnit'));
		if ($listing_unit->get('bathroomDetails')) {
			$bathroomDetails = $unit->addChild('bathroomDetails');
			$this->addXmlTextLocaleValues($bathroomDetails, $lang_code, $listing_unit->get('bathroomDetails'));
		}
		$unit_bathrooms = (array) $listing_unit->get('bathrooms', []);
		if ($unit_bathrooms) {
			$bathrooms = $unit->addChild('bathrooms');
			foreach ($unit_bathrooms as $unit_bathroom) {
				if (!is_array($unit_bathroom) || empty($unit_bathroom['roomSubType'])) {
					continue;
				}
				$bathroom = $bathrooms->addChild('bathroom');
				if (!empty($unit_bathroom['amenities']) && is_array($unit_bathroom['amenities'])) {
					$bathroom_amenities = $bathroom->addChild('amenities');
					foreach ($unit_bathroom['amenities'] as $bath_amenity) {
						if (empty($bath_amenity)) {
							continue;
						}
						$bathroom_amenity = $bathroom_amenities->addChild('amenity');
						$bathroom_amenity->addChild('count', 1);
						$bathroom_amenity->addChild('bathroomFeatureName', htmlspecialchars($bath_amenity));
					}
				}
				$bathroom->addChild('roomSubType', htmlspecialchars($unit_bathroom['roomSubType']));
			}
		}
		if ($listing_unit->get('bedroomDetails')) {
			$bedroomDetails = $unit->addChild('bedroomDetails');
			$this->addXmlTextLocaleValues($bedroomDetails, $lang_code, $listing_unit->get('bedroomDetails'));
		}
		$unit_bedrooms = (array) $listing_unit->get('bedrooms', []);
		if ($unit_bedrooms) {
			$bedrooms = $unit->addChild('bedrooms');
			foreach ($unit_bedrooms as $unit_bedroom) {
				if (!is_array($unit_bedroom) || empty($unit_bedroom['roomSubType'])) {
					continue;
				}
				$bedroom = $bedrooms->addChild('bedroom');
				if (!empty($unit_bedroom['amenities']) && is_array($unit_bedroom['amenities'])) {
					$bedroom_amenities = $bedroom->addChild('amenities');
					foreach ($unit_bedroom['amenities'] as $bed_amenity) {
						if (empty($bed_amenity)) {
							continue;
						}
						$bedroom_amenity = $bedroom_amenities->addChild('amenity');
						$bedroom_amenity->addChild('count', 1);
						$bedroom_amenity->addChild('bedroomFeatureName', htmlspecialchars($bed_amenity));
					}
				}
				$bedroom->addChild('roomSubType', htmlspecialchars($unit_bedroom['roomSubType']));
			}
		}

		// we make the unit description equal to the listing description
		$description = $unit->addChild('description');
		$this->addXmlTextLocaleValues($description, $lang_code, $listing->get('description'), 'info', $tn_rooms);

		// unit amenities
		$unit_amenities = (array) $listing_unit->get('featureValues', []);
		if ($unit_amenities) {
			$unit_featurevalues = $unit->addChild('featureValues');
			foreach ($unit_amenities as $unit_amenity) {
				if (!is_array($unit_amenity) || empty($unit_amenity['unitFeatureName'])) {
					continue;
				}
				$unit_featurevalue = $unit_featurevalues->addChild('featureValue');
				if (!empty($unit_amenity['count'])) {
					$unit_feature_count = (int) $unit_amenity['count'];
				} else {
					$unit_feature_count = 1;
				}
				$unit_featurevalue->addChild('count', $unit_feature_count);
				$unit_featurevalue->addChild('unitFeatureName', htmlspecialchars($unit_amenity['unitFeatureName']));
			}
		}

		// unit safety amenities
		$unit_safety_amenities = (array) $listing_unit->get('safetyFeatureValues', []);
		if ($unit_safety_amenities) {
			$unit_safety_featurevalues = $unit->addChild('safetyFeatureValues');
			foreach ($unit_safety_amenities as $unit_safety_amenity) {
				if (!is_array($unit_safety_amenity) || empty($unit_safety_amenity['safetyFeatureName'])) {
					continue;
				}
				$unit_safety_featurevalue = $unit_safety_featurevalues->addChild('safetyFeatureValue');
				if (!empty($unit_safety_amenity['content']) && !empty($unit_safety_amenity['ctype'])) {
					$safety_feature_cont = $unit_safety_featurevalue->addChild('safetyFeatureContent');
					$safety_feature_cont->addAttribute('contentType', $unit_safety_amenity['ctype']);
					$this->addXmlTextLocaleValues($safety_feature_cont, $lang_code, $unit_safety_amenity['content']);
				}
				$unit_safety_featurevalue->addChild('safetyFeatureName', htmlspecialchars($unit_safety_amenity['safetyFeatureName']));
			}
		}

		// property type and additional unit data
		$unit->addChild('propertyType', htmlspecialchars($listing_unit->get('propertyType', '')));
		$unit->addChild('representedUnits', (int) $vbo_listing->get('units', 1));
		$monetary_info = $unit->addChild('unitMonetaryInformation');
		$monetary_info->addChild('currency', strtoupper($listing->get('currency', VikBooking::getCurrencyName())));

		// unit name is mandatory
		$unit_name = $unit->addChild('unitName');
		$this->addXmlTextLocaleValues($unit_name, $lang_code, $listing_unit->get('name', $vbo_listing->get('name', '')));

		// get XML string
		$xml_str = $xml->asXML();

		// format XML string feed
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml_str);
		$dom->formatOutput = true;
		$xml_str = $dom->saveXML();

		if (!$render) {
			return $xml_str;
		}

		// output the XML document
		$this->outputDocument($xml_str);
	}

	/**
	 * Renders (or returns) an XML document with the requested listing lodging configuration.
	 * In case of validation errors, the execution is truncated with an error status code.
	 * 
	 * @param 	array 	$channel 	 the channel information.
	 * @param 	string 	$listing_id	 the Vrbo listing ID to render.
	 * @param 	bool 	$render 	 true to output the XML, false to return it.
	 * 
	 * @return 	void|string 		 XML string if $render = false, or void.
	 * 
	 * @throws 	Exception
	 */
	public function renderListingLodgingConfiguration(array $channel, $listing_id, $render = true)
	{
		// load resources (in case of errors the execution will be truncated with an error document)
		list($account_key, $listing_record, $listing_data, $vbo_listing_record) = $this->loadListingResources($channel, $listing_id);

		// wrap listing payload into a JObject instance
		$listing = new JObject($listing_data);

		// wrap lodging object into a JObject instance
		$lodging_arr = (array) $listing->get('lodging', []);
		$lodging = new JObject($lodging_arr);

		// wrap Vik Booking listing object into a JObject
		$vbo_listing = new JObject($vbo_listing_record);

		// start XML root node
		$xml_root = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<lodgingConfigurationContent>
	<listingExternalId>$listing_id</listingExternalId>
</lodgingConfigurationContent>
XML;
		// get the SimpleXMLElement object
		$xml = new SimpleXMLElement($xml_root);

		// unit ID
		$xml->addChild('unitExternalId', $listing->get('id') . '-u');

		// lodging configuration (listing-level override for the E4jConnect defaults)
		$lodging_config = $xml->addChild('lodgingConfiguration');

		// accepted payments
		$acceptedPaymentForms = $lodging_config->addChild('acceptedPaymentForms');
		$accepted_payments = (array) $lodging->get('acceptedPaymentForms', []);
		foreach ($accepted_payments as $apfk => $acc_payment) {
			if (is_object($acc_payment)) {
				$acc_payment = (array) $acc_payment;
			}
			if (!is_array($acc_payment) || empty($acc_payment['payment_type'])) {
				continue;
			}
			if (!strcasecmp($acc_payment['payment_type'], 'INVOICE')) {
				// invoice
				$payment_descriptor = $acceptedPaymentForms->addChild('paymentInvoiceDescriptor');
				$payment_descriptor->addChild('paymentFormType', 'INVOICE');
				$payment_descriptor->addChild('paymentNote', (!empty($acc_payment['payment_note']) ? htmlspecialchars($acc_payment['payment_note']) : ''));
				if (!empty($acc_payment['payment_note'])) {
					$payment_descriptor->addChild('invoicePaymentType', htmlspecialchars($acc_payment['invoice_type']));
				}
			} else {
				// card
				$payment_descriptor = $acceptedPaymentForms->addChild('paymentCardDescriptor');
				$payment_descriptor->addChild('paymentFormType', 'CARD');
				$payment_descriptor->addChild('cardCode', (!empty($acc_payment['card_code']) ? $acc_payment['card_code'] : ''));
				$payment_descriptor->addChild('cardType', (!empty($acc_payment['card_type']) ? $acc_payment['card_type'] : ''));
			}			
		}

		// booking policy
		$bookingPolicy = $lodging_config->addChild('bookingPolicy');
		$bookingPolicy->addChild('policy', $lodging->get('bookingPolicy', ''));

		// cancellation policy
		$canc_policy = (array) $lodging->get('cancellationPolicy', []);
		$cancellationPolicy = $lodging_config->addChild('cancellationPolicy');
		$cancellationPolicy->addChild('policy', (!empty($canc_policy['policy']) ? $canc_policy['policy'] : ''));
		if (!empty($canc_policy['policy']) && !strcasecmp($canc_policy['policy'], 'CUSTOM') && !empty($canc_policy['periods']) && is_array($canc_policy['periods'])) {
			// set policy periods
			$policyPeriods = $cancellationPolicy->addChild('policyPeriods');
			// check for custom cancellation fee
			if (!empty($canc_policy['cancellationFee'])) {
				$cancellationFee = $policyPeriods->addChild('cancellationFee');
				$cancellationFee->addChild('currency', strtoupper($listing->get('currency', VikBooking::getCurrencyName())));
				$cancellationFee->addChild('amount', (float) $canc_policy['cancellationFee']);
			}
			// loop over the periods defined (up to 3)
			foreach ($canc_policy['periods'] as $canc_policy_period) {
				$canc_policy_period = (array) $canc_policy_period;
				if (!is_array($canc_policy_period) || !isset($canc_policy_period['nightsbefore']) || !isset($canc_policy_period['refundpcent'])) {
					continue;
				}
				$canc_nights_before = (int) $canc_policy_period['nightsbefore'];
				$policyPeriod = $policyPeriods->addChild('policyPeriod');
				$policyPeriod->addChild('nightsBeforeCheckin', ($canc_nights_before > 0 ? $canc_nights_before : 1));
				$policyPeriod->addChild('refundPercent', (float) $canc_policy_period['refundpcent']);
			}
		}

		// check-in and check-out times
		$lodging_config->addChild('checkInTime', $lodging->get('checkInTime', ''));
		$lodging_config->addChild('checkOutTime', $lodging->get('checkOutTime', ''));

		// allowed rules
		$is_children_allowed = $lodging->get('childrenAllowedRule');
		$is_children_allowed = is_string($is_children_allowed) && $is_children_allowed === 'false' ? false : $is_children_allowed;
		$childrenAllowedRule = $lodging_config->addChild('childrenAllowedRule');
		$childrenAllowedRule->addChild('allowed', ($is_children_allowed ? 'true' : 'false'));
		$is_events_allowed = $lodging->get('eventsAllowedRule');
		$is_events_allowed = is_string($is_events_allowed) && $is_events_allowed === 'false' ? false : $is_events_allowed;
		$eventsAllowedRule = $lodging_config->addChild('eventsAllowedRule');
		$eventsAllowedRule->addChild('allowed', ($is_events_allowed ? 'true' : 'false'));

		// maximum occupancy
		$maximumOccupancyRule = $lodging_config->addChild('maximumOccupancyRule');
		$maximumOccupancyRule->addChild('adults', (int) $lodging->get('maxAdults', 2));
		$maximumOccupancyRule->addChild('guests', (int) $lodging->get('maxGuests', 2));

		// more allowed rules
		$is_pets_allowed = $lodging->get('petsAllowedRule');
		$is_pets_allowed = is_string($is_pets_allowed) && $is_pets_allowed === 'false' ? false : $is_pets_allowed;
		$petsAllowedRule = $lodging_config->addChild('petsAllowedRule');
		$petsAllowedRule->addChild('allowed', ($is_pets_allowed ? 'true' : 'false'));
		$is_smoking_allowed = $lodging->get('smokingAllowedRule');
		$is_smoking_allowed = is_string($is_smoking_allowed) && $is_smoking_allowed === 'false' ? false : $is_smoking_allowed;
		$smokingAllowedRule = $lodging_config->addChild('smokingAllowedRule');
		$smokingAllowedRule->addChild('allowed', ($is_smoking_allowed ? 'true' : 'false'));

		// get XML string
		$xml_str = $xml->asXML();

		// format XML string feed
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml_str);
		$dom->formatOutput = true;
		$xml_str = $dom->saveXML();

		if (!$render) {
			return $xml_str;
		}

		// output the XML document
		$this->outputDocument($xml_str);
	}

	/**
	 * Renders (or returns) an XML document with the requested listing availability.
	 * In case of validation errors, the execution is truncated with an error status code.
	 * 
	 * @param 	array 	$channel 	 the channel information.
	 * @param 	string 	$listing_id	 the Vrbo listing ID to render.
	 * @param 	bool 	$render 	 true to output the XML, false to return it.
	 * 
	 * @return 	void|string 		 XML string if $render = false, or void.
	 * 
	 * @throws 	Exception
	 */
	public function renderListingAvailability(array $channel, $listing_id, $render = true)
	{
		// load resources (in case of errors the execution will be truncated with an error document)
		list($account_key, $listing_record, $listing_data, $vbo_listing_record) = $this->loadListingResources($channel, $listing_id);

		// check if the cached file should be sent to output
		if ($render && $this->allow_cached_files && $this->cachedFileExists('listing_availability', $listing_id)) {
			// attempt to output cached document
			$this->outputCachedDocument('listing_availability', $listing_id);
		}

		// set constraints
		$this->setInOutConstraints();

		// wrap listing payload into a JObject instance
		$listing = new JObject($listing_data);

		// wrap Vik Booking listing object into a JObject
		$vbo_listing = new JObject($vbo_listing_record);

		// the VBO room ID
		$vbo_room_id = $vbo_listing->get('id', $listing_id);

		// total listing units
		$total_units = (int) $vbo_listing->get('units', 1);

		// start XML root node
		$xml_root = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<unitAvailabilityContent>
	<listingExternalId>$listing_id</listingExternalId>
</unitAvailabilityContent>
XML;
		// get the SimpleXMLElement object
		$xml = new SimpleXMLElement($xml_root);

		// unit ID
		$xml->addChild('unitExternalId', $listing->get('id') . '-u');

		// unit availability node
		$unitAvailability = $xml->addChild('unitAvailability');

		// default availability for non-covered dates (N)
		$unitAvailability->addChild('availabilityDefault', 'N');

		// calculate default room restriction values
		$min_prior_notify = $this->getListingMinPriorNotif();
		$def_min_los 	  = $this->getListingDefaultMinStay($vbo_room_id);
		$def_max_los 	  = $this->getListingDefaultMaxStay($vbo_room_id);
		$glob_min_los 	  = VikBooking::getDefaultNightsCalendar();
		$glob_min_los 	  = $glob_min_los < 1 ? 1 : $glob_min_los;

		// calculate availability dates range from today
		list($from_date, $to_date) = $this->getListingDefaultAvailDatesRange($pricing_model = '', $vbo_room_id, 'availability');
		$from_ts = VikBooking::getDateTimestamp($from_date, $this->checkin_h, $this->checkin_m);
		$to_ts 	 = VikBooking::getDateTimestamp($to_date, $this->checkout_h, $this->checkout_m);

		// count the number of inclusive nights in the range
		$total_range_nights = $this->countTotalInclusiveNights($from_date, $to_date);
		if (!$total_range_nights) {
			throw new Exception('Invalid range of dates', 500);
		}

		// other default values
		if ($total_units > 1) {
			$unitAvailability->addChild('availableUnitCountDefault', $total_units);
		}
		$unitAvailability->addChild('changeOverDefault', 'X');

		// set date range for the availability information
		$dateRange = $unitAvailability->addChild('dateRange');
		$dateRange->addChild('beginDate', $from_date);
		$dateRange->addChild('endDate', $to_date);

		// more default values
		$unitAvailability->addChild('maxStayDefault', $def_max_los);
		$unitAvailability->addChild('minPriorNotifyDefault', $min_prior_notify);
		$unitAvailability->addChild('minStayDefault', $def_min_los);
		$unitAvailability->addChild('stayIncrementDefault', 'D');

		// availability configuration for the range of dates
		$avail_config = $unitAvailability->addChild('unitAvailabilityConfiguration');

		// get all nights involved in the range of dates
		$groupdays = VikBooking::getGroupDays($from_ts, $to_ts, $total_range_nights);
		
		// load busy records
		$busy_records = VikBooking::loadBusyRecords([$vbo_room_id], $from_ts, strtotime('+1 day', $to_ts));

		// calculate availability for the range of dates
		$av_pool = [];
		foreach ($groupdays as $gday) {
			$day_key = date('Y-m-d', $gday);
			$bfound = 0;

			if (!isset($busy_records[$vbo_room_id])) {
				$busy_records[$vbo_room_id] = [];
			}

			foreach ($busy_records[$vbo_room_id] as $bu) {
				$busy_info_in = getdate($bu['checkin']);
				$busy_info_out = getdate($bu['checkout']);
				$busy_in_ts = mktime(0, 0, 0, $busy_info_in['mon'], $busy_info_in['mday'], $busy_info_in['year']);
				$busy_out_ts = mktime(0, 0, 0, $busy_info_out['mon'], $busy_info_out['mday'], $busy_info_out['year']);
				if ($gday >= $busy_in_ts && $gday == $busy_out_ts && !$this->inonout_allowed && $total_units < 2) {
					// check-ins on check-outs not allowed
					$bfound++;
					if ($bfound >= $total_units) {
						break;
					}
				}
				if ($gday >= $busy_in_ts && $gday < $busy_out_ts) {
					$bfound++;
					if ($bfound >= $total_units) {
						break;
					}
				}
			}

			// count units left
			$units_left = $total_units - $bfound;
			$units_left = $units_left < 0 ? 0 : $units_left;

			// set remaining availability for this day
			$av_pool[$day_key] = $units_left;
		}

		// set availability nodes
		$av_list = array_values($av_pool);

		$av_sequence = array_map(function($av_count) {
			return $av_count > 0 ? 'Y' : 'N';
		}, $av_list);

		$avail_config->addChild('availability', implode('', $av_sequence));

		if ($total_units > 1) {
			// set availableUnitCount node for representative units
			$avail_config->addChild('availableUnitCount', implode(',', $av_list));
		}

		// load restrictions for this room
		$restrictions = VikBooking::loadRestrictions($filters = true, [$vbo_room_id]);

		// calculate restrictions for the range of dates, and build change over sequence (CTA/CTD)
		$restr_pool = [];
		$changeover_sequence = [];
		foreach ($groupdays as $gday) {
			$day_key = date('Y-m-d', $gday);

			$today_info  = getdate($gday);
			$today_tsin  = mktime(0, 0, 0, $today_info['mon'], $today_info['mday'], $today_info['year']);
			$today_tsout = mktime(0, 0, 0, $today_info['mon'], ($today_info['mday'] + 1), $today_info['year']);

			$restr_pool[$day_key] = VikBooking::parseSeasonRestrictions($today_tsin, $today_tsout, 1, $restrictions);

			// calculate changeover value (C=check-in/out, O=check-out only, I=check-in only, X=no action)
			$def_changeover = 'C';
			if ($restr_pool[$day_key] && (!empty($restr_pool[$day_key]['cta']) || !empty($restr_pool[$day_key]['ctd']))) {
				// CTA/CTD restriction is set for this day
				$day_checkin_allowed  = true;
				$day_checkout_allowed = true;

				// check if arrival on this day is allowed
				if (!empty($restr_pool[$day_key]['cta'])) {
					// clean up week day index (week-day could wrapped around dashes like "-6-")
					$restr_pool[$day_key]['cta'] = array_map(function($wday) {
						return preg_replace("/[^0-9]/", '', $wday);
					}, $restr_pool[$day_key]['cta']);
					if (in_array($today_info['wday'], $restr_pool[$day_key]['cta'])) {
						$day_checkin_allowed = false;
					}
				}

				// check if departure on this day is allowed
				if (!empty($restr_pool[$day_key]['ctd'])) {
					// clean up week day index (week-day could wrapped around dashes like "-6-")
					$restr_pool[$day_key]['ctd'] = array_map(function($wday) {
						return preg_replace("/[^0-9]/", '', $wday);
					}, $restr_pool[$day_key]['ctd']);
					if (in_array($today_info['wday'], $restr_pool[$day_key]['ctd'])) {
						$day_checkout_allowed = false;
					}
				}

				// calculate proper changeover value for this day
				if (!$day_checkin_allowed && !$day_checkout_allowed) {
					// no arrivals and no departures on this day
					$def_changeover = 'X';
				} elseif ($day_checkin_allowed && !$day_checkout_allowed) {
					// only arrivals are accepted on this day
					$def_changeover = 'I';
				} elseif (!$day_checkin_allowed && $day_checkout_allowed) {
					// only departures are accepted on this day
					$def_changeover = 'O';
				}
			}

			// set changeover value for this day
			$changeover_sequence[] = $def_changeover;
		}

		// minimum stay sequence
		$min_los_sequence = [];
		$use_def_min_los  = $glob_min_los > $def_min_los ? $glob_min_los : $def_min_los;
		foreach ($restr_pool as $day_key => $day_restr) {
			if (!$day_restr || empty($day_restr['minlos'])) {
				// no restrictions for this day
				$min_los_sequence[] = $use_def_min_los;
				continue;
			}
			// set minimum stay for this day (always in days even if 7, 14, 21 etc..)
			$min_los_sequence[] = $day_restr['minlos'];
		}

		// stay increment
		$stay_increment_sequence = [];
		foreach ($restr_pool as $day_key => $day_restr) {
			$def_stay_increment = 'D';
			if ($day_restr && !empty($day_restr['cta']) && !empty($day_restr['ctd'])) {
				if (count($day_restr['cta']) == 6 && count($day_restr['ctd']) == 6 && ($day_restr['minlos'] % 7) == 0) {
					// stay increment must be one week
					$def_stay_increment = 'W';
				}
			}

			// set value for this day
			$stay_increment_sequence[] = $def_stay_increment;
		}

		// maximum stay sequence
		$max_los_sequence = [];
		foreach ($restr_pool as $day_key => $day_restr) {
			if (!$day_restr || empty($day_restr['maxlos'])) {
				// no restrictions for this day
				$max_los_sequence[] = $def_max_los;
				continue;
			}
			// set restriction for this day
			$max_los_sequence[] = $day_restr['maxlos'];
		}

		// set changeover node
		$avail_config->addChild('changeOver', implode('', $changeover_sequence));

		// set maximum stay node
		$avail_config->addChild('maxStay', implode(',', $max_los_sequence));

		// set minimum stay node
		$avail_config->addChild('minStay', implode(',', $min_los_sequence));

		// set stay increment node
		$avail_config->addChild('stayIncrement', implode('', $stay_increment_sequence));

		// get XML string
		$xml_str = $xml->asXML();

		// format XML string feed
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml_str);
		$dom->formatOutput = true;
		$xml_str = $dom->saveXML();

		// always cache the file locally
		$this->storeCachedFile($xml_str, $this->getCachedFilePath('listing_availability', $listing_id));

		if (!$render) {
			return $xml_str;
		}

		// output the XML document
		$this->outputDocument($xml_str);
	}

	/**
	 * Renders (or returns) an XML document with the requested listing rates.
	 * In case of validation errors, the execution is truncated with an error status code.
	 * 
	 * @param 	array 	$channel 	 the channel information.
	 * @param 	string 	$listing_id	 the Vrbo listing ID to render.
	 * @param 	bool 	$render 	 true to output the XML, false to return it.
	 * 
	 * @return 	void|string 		 XML string if $render = false, or void.
	 * 
	 * @throws 	Exception
	 */
	public function renderListingRate(array $channel, $listing_id, $render = true)
	{
		// load resources (in case of errors the execution will be truncated with an error document)
		list($account_key, $listing_record, $listing_data, $vbo_listing_record) = $this->loadListingResources($channel, $listing_id);

		// check if the cached file should be sent to output
		if ($render && $this->allow_cached_files && $this->cachedFileExists('listing_rate', $listing_id)) {
			// attempt to output cached document
			$this->outputCachedDocument('listing_rate', $listing_id);
		}

		// set constraints
		$this->setInOutConstraints();

		// wrap listing payload into a JObject instance
		$listing = new JObject($listing_data);

		// wrap Vik Booking listing object into a JObject
		$vbo_listing = new JObject($vbo_listing_record);

		// get the listing's default rate plan ID
		$def_rplan_id = (int) $listing->get('def_rplan_id', 0);
		if (!$def_rplan_id) {
			throw new Exception('Unable to handle the request due to missing default rate plan', 500);
		}

		// the corresponding VBO room ID
		$vbo_room_id = $vbo_listing->get('id');

		// the room minimum and maximum adults occupancy
		$min_adults_occ = (int) $vbo_listing->get('fromadult');
		$min_adults_occ = $min_adults_occ < 1 ? 1 : $min_adults_occ;
		$max_adults_occ = (int) $vbo_listing->get('toadult');
		if ($max_adults_occ <= 0 || $max_adults_occ < $min_adults_occ) {
			throw new Exception('Invalid listing occupancy settings', 500);
		}

		// start XML root node
		$xml_root = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<lodgingRateContent>
	<listingExternalId>$listing_id</listingExternalId>
</lodgingRateContent>
XML;
		// get the SimpleXMLElement object
		$xml = new SimpleXMLElement($xml_root);

		// unit ID
		$xml->addChild('unitExternalId', $listing->get('id') . '-u');

		// determine whether to use "Nightly Rates" or "LOS Rates"
		$pricing_model = 'nightly';
		$has_los_records = VBORoomHelper::hasLosRecords($vbo_room_id, $def_rplan_id, true);
		if ($has_los_records === true || (is_int($has_los_records) && $has_los_records < 28)) {
			// use LOS Rates
			$pricing_model = 'los';
		}

		// load room OBP offsets
		$obp_offsets = VikBooking::loadRoomAdultsDiff($vbo_room_id);
		$def_obp_fee_type = 'pernight';
		if ($obp_offsets) {
			// make sure they are all "per stay" or "per night", "fixed" or "percent", with no mixed values
			$first_obp_rule_data = '';
			foreach ($obp_offsets as $occ => $occ_offset) {
				if (!($occ_offset['value'] > 0)) {
					unset($obp_offsets[$occ]);
					continue;
				}
				$obp_rule_data = "{$occ_offset['valpcent']}-{$occ_offset['pernight']}";
				if (!$first_obp_rule_data) {
					// make sure all other offsets will match this setup
					$first_obp_rule_data = $obp_rule_data;
					continue;
				}
				if ($first_obp_rule_data != $obp_rule_data) {
					// different setup for this offset, unset them all as not supported
					$obp_offsets = [];
					break;
				}
				if ($occ_offset['valpcent'] == 2) {
					// percent values are not supported, unset them all
					$obp_offsets = [];
					break;
				}
				// update default guest fee type
				$def_obp_fee_type = $occ_offset['pernight'] ? 'pernight' : 'perstay';
			}
		}

		// calculate rates date range from today
		list($from_date, $to_date) = $this->getListingDefaultAvailDatesRange($pricing_model, $vbo_room_id, 'rate');
		$from_ts = VikBooking::getDateTimestamp($from_date, $this->checkin_h, $this->checkin_m);
		$to_ts 	 = VikBooking::getDateTimestamp($to_date, $this->checkout_h, $this->checkout_m);

		// read the room rates for the lowest number of nights
		$room_rates = $this->getRoomRates($vbo_room_id, $def_rplan_id);
		if (!$room_rates) {
			throw new Exception('Missing room rates', 500);
		}

		/**
		 * Check if season records should be preloaded. Beware of the hundreds of MBs of server's memory
		 * that could be used for pre-loading and pre-caching records in favour of CPU.
		 * 
		 * @since 	1.9.4
		 */
		$cached_seasons = VikBooking::getDateSeasonRecords($from_ts, $to_ts, [$vbo_room_id]);
		$cached_wdayseasons = [];
		if (method_exists('VikBooking', 'getWdaySeasonRecords')) {
			$cached_wdayseasons = VikBooking::getWdaySeasonRecords();
		}

		// always calculate the rates for this listing in case of nightly pricing model
		$calendar_rates = [];
		if ($pricing_model == 'nightly') {
			$cur_date_info = getdate($from_ts);
			while ($cur_date_info[0] < $to_ts) {
				$day_key = date('Y-m-d', $cur_date_info[0]);

				$today_tsin  = mktime($this->checkin_h, $this->checkin_m, 0, $cur_date_info['mon'], $cur_date_info['mday'], $cur_date_info['year']);
				$today_tsout = mktime($this->checkout_h, $this->checkout_m, 0, $cur_date_info['mon'], ($cur_date_info['mday'] + 1), $cur_date_info['year']);

				$tars = VikBooking::applySeasonsRoom([$room_rates], $today_tsin, $today_tsout, [], $cached_seasons, $cached_wdayseasons);
				$calendar_rates[$day_key] = $tars;

				// next day
				$cur_date_info = getdate(mktime(0, 0, 0, $cur_date_info['mon'], ($cur_date_info['mday'] + 1), $cur_date_info['year']));
			}
		}

		/**
		 * In case of "Nightly Rates" (not LOS) and OBP, make sure to base the room rates on the minimum occupancy,
		 * by applying upfront any OBP discount, like a solo-rate. At that point, we can properly calculate the
		 * actual fees for any number of additional guests ("guestFees" do not allow discounts, only charges).
		 */
		if ($pricing_model == 'nightly' && $obp_offsets) {
			// calculate the rates for this listing and rate plan

			// pool of additional guest fees (only used for "Nightly Rates")
			$obp_guest_fees   = [];
			$guest_fees_range = [];

			// find an OBP rule of type "discount" for a specific occupancy number
			$occ_offset_discount = 0;
			$occ_offset_charge   = 0;
			foreach ($obp_offsets as $occ => $occ_offset) {
				if ($occ_offset['chdisc'] == 1) {
					// charge
					$occ_offset_charge = $occ;
				}
				if (!$occ_offset_charge && $occ_offset['chdisc'] == 2) {
					// the lowest guest occupancy has got a discount on which rates should be based
					$occ_offset_discount = $occ;
					// stop at the lowest adults occupancy (first key)
					break;
				}
			}

			// count the base offset discount for any other occupancy rule
			$base_offset_discount = isset($obp_offsets[$occ_offset_discount]) ? $obp_offsets[$occ_offset_discount]['value'] : 0;

			if ($occ_offset_discount) {
				// base the room rates on this occupancy, which is the minimum occupancy charge
				foreach ($calendar_rates as &$day_tars) {
					$day_tars = VBORoomHelper::getInstance()->applyOBPRules($day_tars, (array) $vbo_listing_record, $occ_offset_discount);
				}

				// unset last reference
				unset($day_tars);
			}

			// calculate the OBP fees
			for ($g = $min_adults_occ; $g <= $max_adults_occ; $g++) { 
				if ($g == $occ_offset_discount || !isset($obp_offsets[$g]) || !$obp_offsets[$g]['value']) {
					// apply a charge if we have altered the base rates on the minimum guests occupancy because of a discount
					if ($base_offset_discount && $g > $occ_offset_discount && !isset($obp_offsets[$g])) {
						// set a charge for this occupancy to cope up with the discounted base rates
						$obp_guest_fees[] = [
							'occ'  => $g,
							'fee'  => $base_offset_discount,
							'type' => $def_obp_fee_type,
						];
					}
					// no rate modifications for this adults occupancy
					continue;
				}
				if ($obp_offsets[$g]['chdisc'] == 1) {
					// charge defined for this adults occupancy
					$occ_fee_val = $obp_offsets[$g]['value'] + $base_offset_discount;
				} else {
					// discount defined for this adults occupancy
					$occ_fee_val = $base_offset_discount - $obp_offsets[$g]['value'];
				}
				if ($occ_fee_val <= 0) {
					// guest fee amounts must be greater than zero
					continue;
				}
				// push properly calculated guest occupancy fee
				$obp_guest_fees[] = [
					'occ'  => $g,
					'fee'  => $occ_fee_val,
					'type' => ($obp_offsets[$g]['pernight'] ? 'pernight' : 'perstay'),
				];
			}

			// make sure to always sort the OBP guest fees by occupancy ascending
			$this->sortOBPGuestFees($obp_guest_fees);

			// calculate the actual ranges for min/max adults number
			$range_cur_fee 	  = null;
			$range_min_adults = 0;
			$range_max_adults = 0;
			$maxest_occ_range = 0;
			foreach ($obp_guest_fees as $obp_fee_k => $obp_guest_fee) {
				if ($maxest_occ_range >= $obp_guest_fee['occ']) {
					// occupancy already included within a range
					continue;
				}
				$range_cur_fee 	  = $obp_guest_fee['fee'];
				$range_min_adults = $obp_guest_fee['occ'];
				$range_max_adults = $range_min_adults;
				$fee_counter = 1;
				$fee_index 	 = $obp_fee_k + 1;
				while (isset($obp_guest_fees[$fee_index])) {
					if (($range_min_adults + $fee_counter) != $obp_guest_fees[$fee_index]['occ'] || $range_cur_fee != $obp_guest_fees[$fee_index]['fee']) {
						// non consecutive occupancy or charge amount
						break;
					}
					// update range max for consecutive occupancy and charge amount
					$range_max_adults = $obp_guest_fees[$fee_index]['occ'];
					// next loop
					$fee_counter++;
					$fee_index++;
				}
				// push guest fees range
				$guest_fees_range[] = array_merge($obp_guest_fee, [
					'min' => $range_min_adults,
					'max' => $range_max_adults,
				]);
				// set maxest occupancy parsed so far
				$maxest_occ_range = $range_max_adults;
			}
		}

		// get all mandatory fees and pet fees for this listing
		$mandatory_fees = VikChannelManager::getAllMandatoryFees([$vbo_room_id], true);

		// attempt to extract the mandatory city taxes
		$city_taxes = $this->getListingTypedFees($mandatory_fees, 'city');

		// attempt to extract the mandatory cleaning fees
		$cleaning_fees = $this->getListingTypedFees($mandatory_fees, 'cleaning');

		// attempt to extract the mandatory damage deposit fees
		$damage_deposit_fees = $this->getListingTypedFees($mandatory_fees, 'damage_deposit');

		// attempt to extract the mandatory pet fees
		$pet_fees = $this->getListingTypedFees($mandatory_fees, 'pet');

		// attempt to extract the mandatory "percent" fees
		$percent_fees = $this->getListingTypedFees($mandatory_fees, 'percent');

		// attempt to extract the mandatory "other" fees
		$other_fees = $this->getListingTypedFees($mandatory_fees, 'other');

		// add main lodging rate node
		$rate_node = $xml->addChild(($pricing_model == 'nightly' ? 'lodgingRate' : 'lodgingRateLos'));

		// currency
		$currency = $listing->get('currency', VikBooking::getCurrencyName());
		$rate_node->addChild('currency', strtoupper($currency));

		/**
		 * Weekly and monthly discounts are defined at listing-level and included in the "lodgingRate" node (no LOS)
		 * 
		 * @since 	1.8.20
		 */
		if ($pricing_model == 'nightly') {
			// discounts are not supported within the node "lodgingRateLos"

			// wrap lodging object into a JObject instance
			$lodging_arr = (array) $listing->get('lodging', []);
			$lodging = new JObject($lodging_arr);

			// check if anything was defined
			$weekly_discount_amount  = (int) $lodging->get('weekly_discount', 0);
			$weekly_discount_type 	 = $lodging->get('weekly_discount_type', 'percent');
			$monthly_discount_amount = (int) $lodging->get('monthly_discount', 0);
			$monthly_discount_type 	 = $lodging->get('monthly_discount_type', 'percent');

			if ($weekly_discount_amount > 0 || $monthly_discount_amount > 0) {
				// add main discounts node
				$discounts = $rate_node->addChild('discounts');

				if ($weekly_discount_amount > 0) {
					// weekly discount factor
					$discount_type_node = $discounts->addChild(($weekly_discount_type == 'percent' ? 'percentOfRentDiscounts' : 'flatAmountDiscounts'));

					$weekly_discount_node = $discount_type_node->addChild('discount');
					$weekly_discount_node->addChild('name', htmlspecialchars('Weekly Discount'));
					if ($weekly_discount_type != 'percent') {
						$weekly_discount_node->addChild('amount', number_format($weekly_discount_amount, 2, '.', ''));
					}

					$range = $weekly_discount_node->addChild('appliesPerNight')
						->addChild('forStaysOfNights')
						->addChild('range');

					$range->addChild('max', 27);
					$range->addChild('min', 7);

					if ($weekly_discount_type == 'percent') {
						$weekly_discount_node->addChild('percent', number_format($weekly_discount_amount, 2, '.', ''));
					}
				}

				// listing max LOS
				$listing_max_los = $this->getListingDefaultMaxStay($vbo_room_id);

				/**
				 * We allow to override the default Max LOS at listing level to avoid
				 * asking the Property Manager to specify more data in the Rates Table.
				 * 
				 * @since 	1.8.21
				 */
				$max_los_override = (int) $lodging->get('max_los', 0);
				if ($max_los_override > 0) {
					$listing_max_los = $max_los_override;
				}

				if ($monthly_discount_amount > 0 && $listing_max_los > 28) {
					// monthly discount factor
					if (!isset($discount_type_node) || $weekly_discount_type != $monthly_discount_type) {
						// start a new node
						$discount_type_node = $discounts->addChild(($monthly_discount_type == 'percent' ? 'percentOfRentDiscounts' : 'flatAmountDiscounts'));
					}

					$monthly_discount_node = $discount_type_node->addChild('discount');
					$monthly_discount_node->addChild('name', htmlspecialchars('Monthly Discount'));
					if ($monthly_discount_type != 'percent') {
						$monthly_discount_node->addChild('amount', number_format($monthly_discount_amount, 2, '.', ''));
					}

					$range = $monthly_discount_node->addChild('appliesPerNight')
						->addChild('forStaysOfNights')
						->addChild('range');

					$range->addChild('max', $listing_max_los);
					$range->addChild('min', 28);

					if ($monthly_discount_type == 'percent') {
						$monthly_discount_node->addChild('percent', number_format($monthly_discount_amount, 2, '.', ''));
					}
				}
			}
		}

		// rate plan ID for information purposes
		$rate_node->addChild('externalId', $def_rplan_id);

		// fees and rent tax rules
		$fees_tax_pool = [];
		$rent_tax_pool = [];

		// check room base rates to calculate rent tax rules
		$tax_aliquote = $this->getAliquoteFromRatePlanId($room_rates['idprice']);
		if ($tax_aliquote) {
			$rent_tax_pool[$tax_aliquote] = ['rent'];
		}

		/**
		 * Take into account any channel rates alteration rule.
		 */
		$channel_rates_alteration = '';
		$room_rate_alterations = VikBooking::getOtasRatesVal($room_rates, $per_channel = true);
		if (is_array($room_rate_alterations) && !empty($room_rate_alterations[VikChannelManagerConfig::VRBOAPI])) {
			// array value for OTA rate rules
			$channel_rates_alteration = $room_rate_alterations[VikChannelManagerConfig::VRBOAPI];
		} elseif (!empty($room_rate_alterations) && is_string($room_rate_alterations)) {
			// string value for OTA rate rules (single channel alteration)
			$channel_rates_alteration = $room_rate_alterations;
		}

		/**
		 * Determine if fees are subjected to channel alteration rule due to different currencies.
		 * We don't take into account the currency conversion rate in the Bulk Rates Cache, but
		 * we rather check if Vrbo requires a different currency than the PMS and apply the same
		 * pricing alteration to the nightly rates to all fixed fees.
		 * 
		 * @since 	1.9.14
		 */
		$fees_currency_alteration = $channel_rates_alteration && strtoupper($currency) != strtoupper(VikBooking::getCurrencyName());

		// check for fees
		if ($cleaning_fees || $damage_deposit_fees || $pet_fees || $percent_fees || $other_fees || (isset($guest_fees_range) && $guest_fees_range)) {
			// add fees parent node
			$fees_node = $rate_node->addChild('fees');

			// cleaning fees
			if ($cleaning_fees) {
				$cleaningFees = $fees_node->addChild('cleaningFees');
				foreach ($cleaning_fees as $cleaning_fee) {
					$tax_id = 0;
					if (!empty($cleaning_fee['idiva'])) {
						$tax_id = $cleaning_fee['idiva'];
						$tax_aliquote = $this->getAliquoteFromTaxId($tax_id);
						if ($tax_aliquote) {
							if (!isset($fees_tax_pool[$tax_aliquote])) {
								$fees_tax_pool[$tax_aliquote] = [];
							}
							// push tax information
							$fees_tax_pool[$tax_aliquote][] = 'cleaning';
						}
					}
					$fee_cost = VikBooking::sayOptionalsMinusIva($cleaning_fee['cost'], $tax_id);
					$fee_node = $cleaningFees->addChild('fee');
					if (!empty($cleaning_fee['id'])) {
						$fee_node->addChild('externalId', $cleaning_fee['id']);
					}
					if ($fees_currency_alteration) {
						$fee_cost = $this->applyRateAlterationRule($fee_cost, $channel_rates_alteration);
					}
					$fee_node->addChild('amount', number_format($fee_cost, 2, '.', ''));
					// determine how the fee is applied
					if ($cleaning_fee['perday'] && $cleaning_fee['perperson']) {
						// per guest and per night
						$fee_node->addChild('appliesPerGuestPerNight');
					} elseif ($cleaning_fee['perperson'] && !$cleaning_fee['perday']) {
						// per guest per stay
						$fee_node->addChild('appliesPerGuestPerStay');
					} elseif ($cleaning_fee['perday'] && !$cleaning_fee['perperson']) {
						// per night
						$fee_node->addChild('appliesPerNight');
					} else {
						// default to per stay
						$fee_node->addChild('appliesPerStay');
					}
					// we just allow one fee at most
					break;
				}
			}

			// damage deposit fees
			if ($damage_deposit_fees) {
				$flatRefundableDamageDepositFees = $fees_node->addChild('flatRefundableDamageDepositFees');
				foreach ($damage_deposit_fees as $damage_deposit_fee) {
					$tax_id = 0;
					if (!empty($damage_deposit_fee['idiva'])) {
						$tax_id = $damage_deposit_fee['idiva'];
						$tax_aliquote = $this->getAliquoteFromTaxId($tax_id);
						if ($tax_aliquote) {
							if (!isset($fees_tax_pool[$tax_aliquote])) {
								$fees_tax_pool[$tax_aliquote] = [];
							}
							// push tax information
							$fees_tax_pool[$tax_aliquote][] = 'damage_deposit';
						}
					}
					$fee_cost = VikBooking::sayOptionalsMinusIva($damage_deposit_fee['cost'], $tax_id);
					$fee_node = $flatRefundableDamageDepositFees->addChild('fee');
					if (!empty($damage_deposit_fee['id'])) {
						$fee_node->addChild('externalId', $damage_deposit_fee['id']);
					}
					if ($fees_currency_alteration) {
						$fee_cost = $this->applyRateAlterationRule($fee_cost, $channel_rates_alteration);
					}
					$fee_node->addChild('amount', number_format($fee_cost, 2, '.', ''));
					// default to per stay
					$fee_node->addChild('appliesPerStay');
					// we just allow one fee at most
					break;
				}
			}

			// guest fees (the actual OBP rules converted to Vrbo)
			if (isset($guest_fees_range) && $guest_fees_range) {
				$guestFees = $fees_node->addChild('guestFees');

				/**
				 * It looks like the range nodes with <min> and <max> are cumulative.
				 * For example, amount 20 min/max 3 adults, amount 35 min/max 4 adults
				 * is charged as +55/night for 4 adults, by including also the charge
				 * for 3 adults although the "max" node is set to 3 for the amount of 20.
				 * Therefore, we subtract the already added amounts as the adults grow.
				 * 
				 * @since 	1.8.19
				 */
				$subtract_fee = 0;

				foreach ($guest_fees_range as $guest_fee_range) {
					$obp_cost = $this->calcAmountBeforeTax($guest_fee_range['fee'], $def_rplan_id);
					$fee_node = $guestFees->addChild('fee');
					$guest_fee_cost = $obp_cost - $subtract_fee;
					if ($fees_currency_alteration) {
						$guest_fee_cost = $this->applyRateAlterationRule($guest_fee_cost, $channel_rates_alteration);
					}
					$fee_node->addChild('amount', number_format($guest_fee_cost, 2, '.', ''));
					$applies_node = $fee_node->addChild(($guest_fee_range['type'] == 'pernight' ? 'appliesPerGuestPerNight' : 'appliesPerGuestPerStay'));
					$forGuestNumber = $applies_node->addChild('forGuestNumber');
					$range = $forGuestNumber->addChild('range');
					$range->addChild('max', $guest_fee_range['max']);
					$range->addChild('min', $guest_fee_range['min']);

					// increase value for the cumulative fees
					$subtract_fee += $guest_fee_cost;
				}
			}

			// other fees (requires "name" and "productCode")
			if ($other_fees) {
				$otherFees = $fees_node->addChild('otherFees');
				foreach ($other_fees as $other_fee) {
					$tax_id = 0;
					if (!empty($other_fee['idiva'])) {
						$tax_id = $other_fee['idiva'];
						$tax_aliquote = $this->getAliquoteFromTaxId($tax_id);
						if ($tax_aliquote) {
							if (!isset($fees_tax_pool[$tax_aliquote])) {
								$fees_tax_pool[$tax_aliquote] = [];
							}
							// push tax information
							$fees_tax_pool[$tax_aliquote][] = 'RESERVATION';
						}
					}
					$fee_cost = VikBooking::sayOptionalsMinusIva($other_fee['cost'], $tax_id);
					$fee_node = $otherFees->addChild('fee');
					if (!empty($other_fee['id'])) {
						$fee_node->addChild('externalId', $other_fee['id']);
					}
					if ($fees_currency_alteration) {
						$fee_cost = $this->applyRateAlterationRule($fee_cost, $channel_rates_alteration);
					}
					$fee_node->addChild('amount', number_format($fee_cost, 2, '.', ''));
					// determine how the fee is applied
					if ($other_fee['perday'] && $other_fee['perperson']) {
						// per guest and per night
						$fee_node->addChild('appliesPerGuestPerNight');
					} elseif ($other_fee['perperson'] && !$other_fee['perday']) {
						// per guest per stay
						$fee_node->addChild('appliesPerGuestPerStay');
					} elseif ($other_fee['perday'] && !$other_fee['perperson']) {
						// per night
						$fee_node->addChild('appliesPerNight');
					} else {
						// default to per stay
						$fee_node->addChild('appliesPerStay');
					}
					// name (max 64 chars)
					$other_fee_name = $other_fee['name'];
					if (strlen($other_fee_name) > 64) {
						if (function_exists('mb_substr')) {
							$other_fee_name = mb_substr($other_fee_name, 0, 64, 'UTF-8');
						} else {
							$other_fee_name = substr($other_fee_name, 0, 64);
						}
					}
					$fee_node->addChild('name', htmlspecialchars($other_fee_name));
					// product code (default to "RESERVATION")
					$fee_node->addChild('productCode', 'RESERVATION');
				}
			}

			// percent fees
			if ($percent_fees) {
				$percentOfRentFees = $fees_node->addChild('percentOfRentFees');
				foreach ($percent_fees as $percent_fee) {
					$fee_cost = $percent_fee['cost'];
					$fee_node = $percentOfRentFees->addChild('fee');
					if (!empty($percent_fee['id'])) {
						$fee_node->addChild('externalId', $percent_fee['id']);
					}
					$fee_node->addChild('percent', number_format($fee_cost, 2, '.', ''));
					// determine how the fee is applied
					if ($percent_fee['perday']) {
						// per night
						$fee_node->addChild('appliesPerNight');
					} else {
						// default to per stay
						$fee_node->addChild('appliesPerStay');
					}
					// we just allow one fee at most
					break;
				}
			}

			// pet fees
			if ($pet_fees) {
				$petFees = $fees_node->addChild('petFees');
				foreach ($pet_fees as $pet_fee) {
					$tax_id = 0;
					if (!empty($pet_fee['idiva'])) {
						$tax_id = $pet_fee['idiva'];
						$tax_aliquote = $this->getAliquoteFromTaxId($tax_id);
						if ($tax_aliquote) {
							if (!isset($fees_tax_pool[$tax_aliquote])) {
								$fees_tax_pool[$tax_aliquote] = [];
							}
							// push tax information
							$fees_tax_pool[$tax_aliquote][] = 'pet';
						}
					}
					$fee_cost = VikBooking::sayOptionalsMinusIva($pet_fee['cost'], $tax_id);
					$fee_node = $petFees->addChild('fee');
					if (!empty($pet_fee['id'])) {
						$fee_node->addChild('externalId', $pet_fee['id']);
					}
					if ($fees_currency_alteration) {
						$fee_cost = $this->applyRateAlterationRule($fee_cost, $channel_rates_alteration);
					}
					$fee_node->addChild('amount', number_format($fee_cost, 2, '.', ''));
					// determine how the fee is applied
					if ($pet_fee['perday']) {
						// per night
						$fee_node->addChild('appliesPerPetPerNight');
					} else {
						// default to per stay
						$fee_node->addChild('appliesPerPetPerStay');
					}
					// we just allow one fee at most
					break;
				}
			}
		}

		// translator object and default language
		$tn_obj = VikBooking::getTranslator();
		$lang_code = $tn_obj->getDefaultLang();
		$lang_code = substr(strtolower($lang_code), 0, 2);
		if (!isset(VCMVrboListing::$supported_locales[$lang_code])) {
			$lang_code = 'en';
		}

		/**
		 * For those who have a different website default language than the one required by Vrbo to be the
		 * default one, we allow to read the listing language locale from the VCM listing management interface.
		 * 
		 * @since 	1.8.27
		 */
		if ($forced_locale = $listing->get('forced_locale', '')) {
			$lang_code = strtolower($forced_locale);
		}

		// set language attribute
		$rate_node->addChild('language', $lang_code);

		// handle city/tourist tax (lodgingStayCollectedFeeSchedule if paid at/after check-in)
		$tourist_tax_pay_when = $listing->get('tourist_tax_pay_when', 'checkin');
		if ($city_taxes && !strcasecmp($tourist_tax_pay_when, 'checkin')) {
			// make sure the city taxes are flat values, not percent
			$city_taxes_offline_flat  = [];
			foreach ($city_taxes as $city_tax) {
				if (empty($city_tax['pcentroom']) && $city_tax['cost'] > 0) {
					// this is a flat city tax, an absolute value in the PM's currency
					$city_taxes_offline_flat[] = $city_tax;
				}
			}

			// check if we actually have offline flat tourist taxes to be paid at check-in (not online)
			if ($city_taxes_offline_flat) {
				// we can start the nodes
				$lodgingStayCollectedFeeSchedule = $rate_node->addChild('lodgingStayCollectedFeeSchedule');
				$lodgingStayCollectedFeeSchedule->addChild('currency', strtoupper($currency));
				$flat_fees = $lodgingStayCollectedFeeSchedule->addChild('flatFees');
				foreach ($city_taxes_offline_flat as $city_tax) {
					$fee_node = $flat_fees->addChild('fee');
					$fee_node->addChild('days', 1);
					$fee_node->addChild('due', 'AT_CHECKIN');
					$fee_node->addChild('externalId', $city_tax['id']);
					$fee_node->addChild('levied', 'ALWAYS');
					// name (max 64 chars)
					$city_tax_name = $city_tax['name'];
					if (strlen($city_tax_name) > 64) {
						if (function_exists('mb_substr')) {
							$city_tax_name = mb_substr($city_tax_name, 0, 64, 'UTF-8');
						} else {
							$city_tax_name = substr($city_tax_name, 0, 64);
						}
					}
					$fee_node->addChild('name', htmlspecialchars($city_tax_name));
					// product code (default to "CITY_TAX")
					$fee_node->addChild('productCode', 'CITY_TAX');
					$fee_cost = VikBooking::sayOptionalsMinusIva($city_tax['cost'], (!empty($city_tax['idiva']) ? $city_tax['idiva'] : 0));
					$fee_node->addChild('amount', number_format($fee_cost, 2, '.', ''));
					// determine how the fee is applied
					if ($city_tax['perday'] && $city_tax['perperson']) {
						// per guest and per night
						$fee_node->addChild('appliesPerGuestPerNight');
					} elseif ($city_tax['perperson'] && !$city_tax['perday']) {
						// per guest per stay
						$fee_node->addChild('appliesPerGuestPerStay');
					} elseif ($city_tax['perday'] && !$city_tax['perperson']) {
						// per night
						$fee_node->addChild('appliesPerNight');
					} else {
						// default to per stay
						$fee_node->addChild('appliesPerStay');
					}
					$fee_node->addChild('currency', strtoupper($currency));
					// we just allow one city tax at most
					break;
				}
				// set required language node
				$lodgingStayCollectedFeeSchedule->addChild('language', $lang_code);
				// set required support payment methods nide
				$supported_paymets = $lodgingStayCollectedFeeSchedule->addChild('supportedPaymentMethods');
				$supported_paymets->addChild('method', 'CASH');
			}
		}

		/**
		 * "LOS" pricing model requires the OBP rates to be specified
		 * for any length of stay through various nodes.
		 */
		if ($pricing_model == 'los') {
			/**
			 * The first thing to do is to group the occupancy ranges.
			 * If one, two and three adults pay the same, we can have
			 * just one node for every check-in date with occupancy 3.
			 * Example (1st node: up to 3 guests, 2nd node: 4 and 5 guests)
			 * 2023-03-14,3,100,180,240...
			 * 2023-03-14,5,120,200,280...
			 */

			$lengthOfStayBaseRent = $rate_node->addChild('lengthOfStayBaseRent');

			// determine the occupancy ranges for which rates should be expressed (OBP)
			$los_occ_ranges = [];
			if ($obp_offsets) {
				// check if the minimum guest occupancy is covered
				$min_obp_occ = min(array_keys($obp_offsets));
				if ($min_adults_occ < $min_obp_occ) {
					// push first range until the default occupancy
					$los_occ_ranges[] = [
						'min' => $min_adults_occ,
						'max' => ($min_obp_occ - 1),
					];
				}

				// calculate ranges depending on the type of offsets
				$from_obp_occ = $min_obp_occ;
				$from_obp_occ = $from_obp_occ < 1 ? 1 : $from_obp_occ;
				$prev_obp_fee = 0;
				$last_obp_occ = 0;
				$max_obp_occ  = 0;
				foreach ($obp_offsets as $occ => $obp_offset) {
					if (!$prev_obp_fee) {
						$prev_obp_fee = $obp_offset['value'];
						$last_obp_occ = $occ;
						continue;
					}
					if ($prev_obp_fee != $obp_offset['value']) {
						// push previous range
						$los_occ_ranges[] = [
							'min' => $from_obp_occ,
							'max' => $last_obp_occ,
						];
						$max_obp_occ = $last_obp_occ;
						$from_obp_occ = $occ;
						$prev_obp_fee = $obp_offset['value'];
						$last_obp_occ = $occ;
					} else {
						// update last occupancy
						$last_obp_occ = $occ;
					}
				}

				if ($max_adults_occ > $max_obp_occ) {
					// push last range
					$los_occ_ranges[] = [
						'min' => ($max_obp_occ + 1),
						'max' => $max_adults_occ,
					];
				}
			}

			if (!$los_occ_ranges) {
				// push the whole min and max occupancy
				$los_occ_ranges[] = [
					'min' => $min_adults_occ,
					'max' => $max_adults_occ,
				];
			}

			/**
			 * Get the LOS room rates for any number of nights of stay up to 180.
			 * Do not calculate average values for one night of stay, so that when
			 * applying the seasonal prices, the "Value Overrides" ("LOS Overrides")
			 * will work correctly, even if the stay dates will be always for one night.
			 */
			$avg_costs = false;
			$los_room_rates = $this->getLOSRoomRates($vbo_room_id, $def_rplan_id, $avg_costs = false);

			// determine the LOS with no rates defined
			$los_rate_nights = [];
			foreach ($los_room_rates as $los_room_rate) {
				if ($los_room_rate['los_nights'] > 180) {
					// max LOS is 180 nights when in LOS model
					continue;
				}
				// push the rate for the actual number of nights of stay
				$los_rate_nights[] = $los_room_rate['los_nights'];
			}
			if (!$los_rate_nights) {
				throw new Exception('Invalid LOS rates for unsupported nights', 500);
			}
			$los_max_nights  = max($los_rate_nights);
			$los_full_nights = range(1, $los_max_nights);
			$los_no_nights 	 = array_diff($los_full_nights, $los_rate_nights);

			// loop over all the inventory dates to be displayed
			$cur_date_info = getdate($from_ts);
			while ($cur_date_info[0] < $to_ts) {
				$day_key = date('Y-m-d', $cur_date_info[0]);

				$today_tsin  = mktime($this->checkin_h, $this->checkin_m, 0, $cur_date_info['mon'], $cur_date_info['mday'], $cur_date_info['year']);
				$today_tsout = mktime($this->checkout_h, $this->checkout_m, 0, $cur_date_info['mon'], ($cur_date_info['mday'] + 1), $cur_date_info['year']);

				// immediately apply seasonal rates over all los records to save queries
				$tars = VikBooking::applySeasonsRoom($los_room_rates, $today_tsin, $today_tsout, [], $cached_seasons, $cached_wdayseasons);

				// loop over any occupancy range
				foreach ($los_occ_ranges as $los_occ_range) {
					// start LOS string for this check-in day and occupancy
					$los_string_row = [
						$day_key,
						$los_occ_range['max'],
					];

					// parse all LOS room rates
					for ($nos = 1; $nos <= $los_max_nights; $nos++) {
						if (!isset($los_room_rates[$nos]) && in_array($nos, $los_no_nights)) {
							// no stay allowed for this number of nights, so set a cost of 0
							$los_string_row[] = 0;
							continue;
						}

						// get the tariffs for just this length of stay
						$los_tariffs = [$tars[$nos]];

						// in case we will decide to switch to average costs per night, this will be required
						if ($avg_costs) {
							// multiply the average nighly cost by the actual number of nights of stay
							foreach ($los_tariffs as &$tar) {
								$tar['cost'] *= $nos;
								$tar['days'] = $nos;
							}

							// unset last reference
							unset($tar);
						}

						// apply OBP rules (if any)
						$los_tariffs = VBORoomHelper::getInstance()->applyOBPRules($los_tariffs, (array) $vbo_listing_record, $los_occ_range['max']);

						// get the final cost before tax for this number of nights, occupancy and check-in date
						$los_final_cost = VikBooking::sayCostMinusIva($los_tariffs[0]['cost'], $los_tariffs[0]['idprice']);

						// apply any channel rate alteration rule
						$los_final_cost = $this->applyRateAlterationRule($los_final_cost, $channel_rates_alteration);

						// push piece
						$los_string_row[] = number_format($los_final_cost, 2, '.', '');
					}

					// set node for this check-in day and occupancy
					$lengthOfStayBaseRent->addChild('lengthOfStayBaseRentRow', implode(',', $los_string_row));
				}

				// next day
				$cur_date_info = getdate(mktime(0, 0, 0, $cur_date_info['mon'], ($cur_date_info['mday'] + 1), $cur_date_info['year']));
			}
		}

		/**
		 * "Nightly Rates" (not LOS) pricing model requires a cost for each week-day that we do not have,
		 * so we grab the first cost in the calendar rates calculated and we use it for every day of week.
		 * Then, we build the ranges of overrides in case of price changes on some days in the range.
		 */
		if ($pricing_model == 'nightly') {
			$dow_def_cost = 0;
			foreach ($calendar_rates as $day_tars) {
				foreach ($day_tars as $day_tar) {
					$dow_def_cost = VikBooking::sayCostMinusIva($day_tar['cost'], $day_tar['idprice']);
					break 2;
				}
			}
			if (!$dow_def_cost) {
				throw new Exception('Could not identify the default day of week cost', 500);
			}

			// apply any channel rate alteration rule
			$dow_def_cost = $this->applyRateAlterationRule($dow_def_cost, $channel_rates_alteration);

			// start node
			$nightlyRates = $rate_node->addChild('nightlyRates');

			// set default cost for days of week
			$nightlyRates->addChild('fri', number_format($dow_def_cost, 2, '.', ''));
			$nightlyRates->addChild('mon', number_format($dow_def_cost, 2, '.', ''));

			// build the ranges of date for every price modification
			$nightly_rate_ranges = [];
			$last_compare_cost = 0;
			$last_dt_from = '';
			$last_dt_to   = '';
			$prev_dt_from = '';
			foreach ($calendar_rates as $day_key => $day_tars) {
				if (!$last_compare_cost) {
					$last_compare_cost = $day_tars[0]['cost'];
					$last_dt_from = $day_key;
					$prev_dt_from = $day_key;
					continue;
				}
				if ($last_compare_cost != $day_tars[0]['cost']) {
					// close previous range
					$nightly_rate_ranges[] = array_merge($day_tars[0], [
						'cost' 		=> $last_compare_cost,
						'from_date' => $last_dt_from,
						'to_date' 	=> $prev_dt_from,
					]);
					// update comparison data
					$last_compare_cost = $day_tars[0]['cost'];
					$last_dt_from = $day_key;
					$last_dt_to = $prev_dt_from;
				}
				// set previous consecutive date
				$prev_dt_from = $day_key;
			}

			// check for last range
			if ($last_dt_to != $prev_dt_from) {
				$nightly_rate_ranges[] = array_merge($day_tars[0], [
					'cost' 		=> $day_tars[0]['cost'],
					'from_date' => $last_dt_from,
					'to_date' 	=> $prev_dt_from,
				]);
			}

			// now build the nightly overrides
			$nightly_overrides = [];
			foreach ($nightly_rate_ranges as $rate_range) {
				if (!isset($nightly_overrides[$rate_range['cost']])) {
					$nightly_overrides[$rate_range['cost']] = [];
				}
				$nightly_overrides[$rate_range['cost']][] = [
					'cost' 		=> $rate_range['cost'],
					'idprice' 	=> $rate_range['idprice'],
					'from_date' => $rate_range['from_date'],
					'to_date'   => $rate_range['to_date'],
				];
			}

			if (count($nightly_overrides) > 1) {
				// we have different rates across the whole range of dates
				$nightlyOverrides = $nightlyRates->addChild('nightlyOverrides');
				foreach ($nightly_overrides as $amount_overrides) {
					// get the final cost before tax
					$nightly_ovr_before_tax = VikBooking::sayCostMinusIva($amount_overrides[0]['cost'], $amount_overrides[0]['idprice']);

					// apply any channel rate alteration rule
					$nightly_ovr_before_tax = $this->applyRateAlterationRule($nightly_ovr_before_tax, $channel_rates_alteration);

					// start override node
					$override = $nightlyOverrides->addChild('override');

					// set the override amount
					$override->addChild('amount', number_format($nightly_ovr_before_tax, 2, '.', ''));

					// set the range of nights on which the rate is applied
					$nights_node = $override->addChild('nights');
					foreach ($amount_overrides as $nightly_override) {
						$range_node = $nights_node->addChild('range');
						$range_node->addChild('max', $nightly_override['to_date']);
						$range_node->addChild('min', $nightly_override['from_date']);
					}
				}
			}

			// keep setting default cost for days of week
			$nightlyRates->addChild('sat', number_format($dow_def_cost, 2, '.', ''));
			$nightlyRates->addChild('sun', number_format($dow_def_cost, 2, '.', ''));
			$nightlyRates->addChild('thu', number_format($dow_def_cost, 2, '.', ''));
			$nightlyRates->addChild('tue', number_format($dow_def_cost, 2, '.', ''));
			$nightlyRates->addChild('wed', number_format($dow_def_cost, 2, '.', ''));
		}

		// payment schedule
		$paymentSchedule = $rate_node->addChild('paymentSchedule');
		$payments = $paymentSchedule->addChild('payments');
		if (VikBooking::payTotal()) {
			// full amount required at time of booking
			$payment = $payments->addChild('payment');
			$payment->addChild('dueType', 'AT_BOOKING');
			$requiresPercentOfTotalBooking = $payment->addChild('requiresPercentOfTotalBooking');
			$requiresPercentOfTotalBooking->addChild('percent', 100);
		} else {
			// deposit enabled
			$deposit_pcent  = (VikBooking::getTypeDeposit() != 'fixed');
			$deposit_amount = VikBooking::getAccPerCent();
			// add first payment node for the deposit amount
			$payment = $payments->addChild('payment');
			$payment->addChild('dueType', 'AT_BOOKING');
			if ($deposit_pcent) {
				$requiresPercentOfTotalBooking = $payment->addChild('requiresPercentOfTotalBooking');
				$requiresPercentOfTotalBooking->addChild('percent', number_format($deposit_amount, 2, '.', ''));
			} else {
				$requiresFlatAmountOf = $payment->addChild('requiresFlatAmountOf');
				$requiresFlatAmountOf->addChild('amount', number_format($deposit_amount, 2, '.', ''));
			}
			// check no deposit days in advance setting
			$no_dep_days_adv = VikBooking::getDepositIfDays();
			if ($no_dep_days_adv > 0) {
				// add another payment node for the balance N days before check-in
				$payment = $payments->addChild('payment');
				$payment->addChild('days', $no_dep_days_adv);
				$payment->addChild('dueType', 'BEFORE_CHECKIN');
				$payment->addChild('requiresRemainder');
			} else {
				// add another payment node for the balance at check-in
				$payment = $payments->addChild('payment');
				$payment->addChild('dueType', 'AT_CHECKIN');
				$payment->addChild('requiresRemainder');
			}
		}

		// tax rules (or tourist taxes to be paid online at time of booking)
		$city_taxes_online = ($city_taxes && !strcasecmp($tourist_tax_pay_when, 'booking'));
		if ($fees_tax_pool || $rent_tax_pool || $city_taxes_online) {
			// start the taxRules node because for sure we've got something to include
			$taxRules = $rate_node->addChild('taxRules');

			// check if we've got city taxes to be paid online, and split them among flat and percent
			$city_taxes_online_flat  = [];
			$city_taxes_online_pcent = [];
			if ($city_taxes_online) {
				foreach ($city_taxes as $city_tax) {
					if (!empty($city_tax['pcentroom'])) {
						// this is a percent city tax of the room rate
						$city_taxes_online_pcent[] = $city_tax;
					} else {
						// this is a flat city tax, an absolute value in the PM's currency
						$city_taxes_online_flat[] = $city_tax;
					}
				}
			}

			// parse the flat city taxes with absolute values in the PM's currency
			if ($city_taxes_online_flat) {
				// set the tourist tax (should be just one) to be paid online, they will still be excluded from commissions
				$flatTaxRules = $taxRules->addChild('flatTaxRules');
				foreach ($city_taxes_online_flat as $city_tax) {
					$rule = $flatTaxRules->addChild('rule');
					$activeLocalDateRange = $rule->addChild('activeLocalDateRange');
					$activeLocalDateRange->addChild('min', $from_date);
					$rule->addChild('currency', strtoupper($currency));
					$rule->addChild('externalId', $city_tax['id']);
					// name (max 64 chars)
					$city_tax_name = $city_tax['name'];
					if (strlen($city_tax_name) > 64) {
						if (function_exists('mb_substr')) {
							$city_tax_name = mb_substr($city_tax_name, 0, 64, 'UTF-8');
						} else {
							$city_tax_name = substr($city_tax_name, 0, 64);
						}
					}
					$rule->addChild('name', htmlspecialchars($city_tax_name));
					$fee_cost = VikBooking::sayOptionalsMinusIva($city_tax['cost'], (!empty($city_tax['idiva']) ? $city_tax['idiva'] : 0));
					$rule->addChild('amount', number_format($fee_cost, 2, '.', ''));
					// determine how the fee is applied
					if ($city_tax['perday'] && $city_tax['perperson']) {
						// per guest and per night
						$rule->addChild('appliesPerGuestPerNight');
					} elseif ($city_tax['perperson'] && !$city_tax['perday']) {
						// per guest per stay
						$rule->addChild('appliesPerGuestPerStay');
					} elseif ($city_tax['perday'] && !$city_tax['perperson']) {
						// per night
						$rule->addChild('appliesPerNight');
					} else {
						// default to per stay
						$rule->addChild('appliesPerStay');
					}

					// we just allow one city tax at most
					break;
				}
			}

			// check taxes (VAT/GST)
			if ($fees_tax_pool && $rent_tax_pool && array_keys($fees_tax_pool) === array_keys($rent_tax_pool)) {
				// fees and rent have the same tax rules: describe tax rules to be applied on both fees and rent
				$percentOfRentAndFeesTaxRules = $taxRules->addChild('percentOfRentAndFeesTaxRules');
				foreach ($rent_tax_pool as $aliquote => $elements) {
					$float_aliq = (float) $aliquote;
					$rule = $percentOfRentAndFeesTaxRules->addChild('rule');
					$activeLocalDateRange = $rule->addChild('activeLocalDateRange');
					$activeLocalDateRange->addChild('min', $from_date);
					$rule->addChild('name', htmlspecialchars("{$float_aliq}% Tax"));
					// these are the valid node positions
					$rule->addChild('appliesToRentAndFeesPerStay');
					$rule->addChild('percent', number_format($aliquote, 2, '.', ''));

					// just one tax rate is possible because rent is involved, and just one rate plan was read
					break;
				}
			} else {
				// parse tax rules for fees and rent
				if ($fees_tax_pool) {
					// describe the tax rules to be applied to the fees
					$percentOfFeesTaxRules = $taxRules->addChild('percentOfFeesTaxRules');
					// map of product code identified
					$product_code_map = [
						'cleaning' 		 => 'CLEANING',
						'damage_deposit' => 'DEPOSIT_DAMAGE',
						'pet' 			 => 'PET',
						'RESERVATION' 	 => 'RESERVATION',
					];
					foreach ($fees_tax_pool as $aliquote => $elements) {
						$float_aliq = (float) $aliquote;
						$rule = $percentOfFeesTaxRules->addChild('rule');
						$activeLocalDateRange = $rule->addChild('activeLocalDateRange');
						$activeLocalDateRange->addChild('min', $from_date);
						$rule->addChild('name', htmlspecialchars("{$float_aliq}% Tax"));
						// these are the valid node positions
						$fees_per_stay = $rule->addChild('appliesToFeesPerStay');
						$whenProductCodeIn = $fees_per_stay->addChild('whenProductCodeIn');
						$unique_elements = array_unique($elements);
						foreach ($unique_elements as $elem_key) {
							$product_code = isset($product_code_map[$elem_key]) ? $product_code_map[$elem_key] : 'RESERVATION';
							$whenProductCodeIn->addChild('code', $product_code);
						}
						$rule->addChild('percent', number_format($aliquote, 2, '.', ''));
					}
				}
				if ($rent_tax_pool) {
					// describe the tax rule to be applied to the rent
					$percentOfRentTaxRules = $taxRules->addChild('percentOfRentTaxRules');
					foreach ($rent_tax_pool as $aliquote => $elements) {
						$float_aliq = (float) $aliquote;
						$rule = $percentOfRentTaxRules->addChild('rule');
						$activeLocalDateRange = $rule->addChild('activeLocalDateRange');
						$activeLocalDateRange->addChild('min', $from_date);
						$rule->addChild('name', htmlspecialchars("{$float_aliq}% Tax"));
						// these are the valid node positions
						$rule->addChild('percent', number_format($aliquote, 2, '.', ''));
						$rule->addChild('appliesPerStay');

						// just one tax rate is possible because just one rate plan was read
						break;
					}
				}
			}

			// check if tourist taxes must be paid online at the time of booking

			// parse the percent city taxes
			if ($city_taxes_online_pcent) {
				// set the tourist tax (should be just one) to be paid online, they will still be excluded from commissions
				if (!isset($percentOfRentTaxRules)) {
					// make sure to define this node only once
					$percentOfRentTaxRules = $taxRules->addChild('percentOfRentTaxRules');
				}
				foreach ($city_taxes_online_pcent as $city_tax) {
					$rule = $percentOfRentTaxRules->addChild('rule');
					$activeLocalDateRange = $rule->addChild('activeLocalDateRange');
					$activeLocalDateRange->addChild('min', $from_date);
					$rule->addChild('externalId', $city_tax['id']);
					// name (max 64 chars)
					$city_tax_name = $city_tax['name'];
					if (strlen($city_tax_name) > 64) {
						if (function_exists('mb_substr')) {
							$city_tax_name = mb_substr($city_tax_name, 0, 64, 'UTF-8');
						} else {
							$city_tax_name = substr($city_tax_name, 0, 64);
						}
					}
					$rule->addChild('name', htmlspecialchars($city_tax_name));
					$rule->addChild('percent', number_format($city_tax['cost'], 2, '.', ''));
					// determine how the fee is applied (when city taxes are paid online we can only have per night or per stay because it's a percent value)
					if ($city_tax['perday'] && !$city_tax['perperson']) {
						// per night
						$rule->addChild('appliesPerNight');
					} else {
						// default to per stay
						$rule->addChild('appliesPerStay');
					}

					// we just allow one city tax at most
					break;
				}
			}
		}

		// get XML string
		$xml_str = $xml->asXML();

		// format XML string feed
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml_str);
		$dom->formatOutput = true;
		$xml_str = $dom->saveXML();

		// always cache the file locally
		$this->storeCachedFile($xml_str, $this->getCachedFilePath('listing_rate', $listing_id));

		if (!$render) {
			return $xml_str;
		}

		// output the XML document
		$this->outputDocument($xml_str);
	}

	/**
	 * Renders (or returns) an XML document with the booking content index.
	 * 
	 * @param 	array 	$channel 	 the channel information.
	 * @param 	bool 	$render 	 true to output the XML, false to return it.
	 * 
	 * @return 	void|string 		 XML string if $render = false, or void.
	 * 
	 * @throws 	Exception
	 */
	public function renderBookingIndex(array $channel, $render = true)
	{
		if (empty($channel['uniquekey']) || $channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
			throw new Exception('Bad Request', 400);
		}

		$account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
		if (!empty($channel['params']) && !empty($channel['params']['hotelid'])) {
			$account_key = $channel['params']['hotelid'];
		}

		if (!$account_key) {
			throw new Exception('Unauthorized', 401);
		}

		// check if the cached file should be sent to output
		if ($render && $this->allow_cached_files && $this->cachedFileExists('booking_index')) {
			// attempt to output cached document
			$this->outputCachedDocument('booking_index');
		}

		// default minimum last updated date for reading the online bookings
		$min_last_updated_date = date('Y-m-d', strtotime('-2 months'));
		$min_checkout_date 	   = date('Y-m-d', strtotime('-45 days'));

		/**
		 * Vrbo may post an XML request to this endpoint to request an explicit
		 * "startDate" to filter the online bookings with a "last-update-date"
		 * after the one requested. Moreover, bookings with a check-out date
		 * that is more than 45 days in the past should never be updated/modified.
		 */
		$vrbo_req_limits = $this->collectBookingIndexRequestLimits($account_key);
		if ($vrbo_req_limits && !empty($vrbo_req_limits['min_last_updated_date'])) {
			// we have collected some request values
			$min_last_updated_date = $vrbo_req_limits['min_last_updated_date'];
		}

		// start XML root node
		$xml_root = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<bookingContentIndex>
	<documentVersion>1.3</documentVersion>
</bookingContentIndex>
XML;
		// get the SimpleXMLElement object
		$xml = new SimpleXMLElement($xml_root);

		// set advertiser node and ID
		$advertiser = $xml->addChild('advertiser');
		$advertiser->addChild('assignedId', $account_key);

		// get an instance of the URI helper class
		$uri = JUri::getInstance();
		$uri->setVar('x_type', 'booking_update');

		// load all bookings that Vrbo should see listed
		foreach ($this->loadOnlineBookings($min_last_updated_date, $min_checkout_date) as $vrbo_res) {
			// set booking entry node
			$booking_entry = $advertiser->addChild('bookingContentIndexEntry');
			// set booking identifier
			$uri->setVar('bid', $vrbo_res['id']);
			$booking_entry->addChild('bookingUpdateUrl', htmlspecialchars((string) $uri));
			// set last update date time
			$last_upd_dt = JFactory::getDate($vrbo_res['last_updated']);
			$booking_entry->addChild('lastUpdatedDate', $last_upd_dt->format('Y-m-d\TH:i:s\Z'));
		}

		// get XML string
		$xml_str = $xml->asXML();

		// format XML string feed
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml_str);
		$dom->formatOutput = true;
		$xml_str = $dom->saveXML();

		// always cache the file locally
		$this->storeCachedFile($xml_str, $this->getCachedFilePath('booking_index'));

		if (!$render) {
			return $xml_str;
		}

		// output the XML document
		$this->outputDocument($xml_str);
	}

	/**
	 * Renders (or returns) an XML document with the booking update content.
	 * 
	 * @param 	array 	$channel 	 the channel information.
	 * @param 	bool 	$render 	 true to output the XML, false to return it.
	 * @param 	int 	$force_bid 	 optional value to force the reading of a booking ID.
	 * 
	 * @return 	void|string 		 XML string if $render = false, or void.
	 * 
	 * @throws 	Exception
	 */
	public function renderBookingUpdate(array $channel, $render = true, $force_bid = null)
	{
		$bid = VikRequest::getInt('bid', 0, 'request');
		if (!$bid && !$force_bid) {
			throw new Exception('Missing booking ID', 500);
		}

		// set proper booking ID involved
		$bid = !$bid ? $force_bid : $bid;

		if (empty($channel['uniquekey']) || $channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
			throw new Exception('Bad Request', 400);
		}

		$account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
		if (!empty($channel['params']) && !empty($channel['params']['hotelid'])) {
			$account_key = $channel['params']['hotelid'];
		}

		if (!$account_key) {
			throw new Exception('Unauthorized', 401);
		}

		// load booking and room details
		$booking = VikBooking::getBookingInfoFromID($bid);
		$rooms 	 = VikBooking::loadOrdersRoomsData($bid);

		if (!$booking || !$rooms) {
			throw new Exception('Booking not found', 404);
		}

		// attempt to get the customer record
		$customer = VikBooking::getCPinInstance()->getCustomerFromBooking($booking['id']);

		if (!$customer) {
			// we cannot proceed without a proper customer record associated
			throw new Exception('Customer (traveler) record not found for the reservation ID ' . $booking['id'], 404);
		}

		// get the (first) listing settings (Content/Product information in VCM)
		$listing_data = $this->getListingData($channel, $account_key, $rooms[0]['idroom']);
		// wrap listing payload into a JObject instance
		$listing = new JObject($listing_data);
		// listing currency
		$listing_currency = $listing->get('currency', VikBooking::getCurrencyName());

		// determine the language of the booking
		$booking_locale = 'en_US';
		if (!empty($booking['lang']) && strlen($booking['lang']) >= 2) {
			// find the proper and supported booking locale
			$booking_locale = VCMVrboListing::matchBookingLocale($booking['lang']);
		}

		// start XML root node
		$xml_root = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<bookingUpdate>
	<documentVersion>1.3</documentVersion>
</bookingUpdate>
XML;
		// get the SimpleXMLElement object
		$xml = new SimpleXMLElement($xml_root);

		// set update details node
		$update_details = $xml->addChild('bookingUpdateDetails');

		// set info nodes
		$update_details->addChild('advertiserAssignedId', $account_key);
		$update_details->addChild('listingExternalId', $rooms[0]['idroom']);
		$update_details->addChild('unitExternalId', $rooms[0]['idroom'] . '-u');
		$update_details->addChild('externalId', $booking['id']);
		$update_details->addChild('guestProfileExternalId', $customer['id']);

		// inquirer node (details about the traveler)
		$inquirer = $update_details->addChild('inquirer');
		$inquirer->addAttribute('locale', $booking_locale);
		$inquirer->addChild('firstName', htmlspecialchars($customer['first_name']));
		$inquirer->addChild('lastName', htmlspecialchars($customer['last_name']));
		$inquirer->addChild('emailAddress', htmlspecialchars($customer['email']));

		// customer phone number
		$customer_phone = $customer['phone'];
		if (empty($customer_phone)) {
			$customer_phone = $booking['phone'];
		}
		$customer_phone = str_replace(' ', '', (string) $customer_phone);
		if ($customer_phone) {
			$country_phone_prefix = $this->getCountryPhonePrefix($customer['country']);
			if ($country_phone_prefix && strpos($customer_phone, $country_phone_prefix) === 0) {
				// specify both country prefix and phone number
				$inquirer->addChild('phoneCountryCode', htmlspecialchars($country_phone_prefix));
				$inquirer->addChild('phoneNumber', htmlspecialchars(substr($customer_phone, strlen($country_phone_prefix))));
			} else {
				// add just the full phone number
				$inquirer->addChild('phoneNumber', htmlspecialchars($customer_phone));
			}
		}

		// customer address
		$customer_address = $inquirer->addChild('address');
		$customer_address->addAttribute('rel', 'BILLING');
		$customer_address->addChild('addressLine1', htmlspecialchars($customer['address']));
		$customer_address->addChild('addressLine3', htmlspecialchars($customer['city']));
		if (!empty($customer['state'])) {
			$customer_address->addChild('addressLine4', htmlspecialchars($customer['state']));
		}
		$customer_address->addChild('addressLine5', htmlspecialchars($customer['country_name']));
		$customer_address->addChild('country', htmlspecialchars($customer['country_2_code']));
		$customer_address->addChild('postalCode', htmlspecialchars($customer['zip']));

		// locale
		$update_details->addChild('locale', $booking_locale);

		// details about the booking
		$order_list = $update_details->addChild('orderList');
		$order = $order_list->addChild('order');
		$order->addChild('currency', htmlspecialchars(strtoupper($listing_currency)));
		$order->addChild('externalId', $booking['id']);
		$order_item_list = $order->addChild('orderItemList');

		// load all the options of type "refundable damage deposit"
		$damage_options  = VCMFeesHelper::getInstance()->getDamageDepositOptions();
		$damage_deposits = 0;

		// rooms booked - should always be just one listing ("RENTAL")
		$rooms_total_amount = 0;
		$rooms_net_amount 	= 0;
		$rooms_total_tax 	= 0;
		foreach ($rooms as $k => $book_room) {
			$tax_aliq = 0;
			$order_item = $order_item_list->addChild('orderItem');
			$order_item->addChild('description', 'Rent');
			$order_item->addChild('externalId', $book_room['idroom']);
			$order_item->addChild('feeType', ($k > 0 ? 'MISC' : 'RENTAL'));
			$order_item->addChild('name', htmlspecialchars($book_room['room_name']));
			if ($book_room['cust_cost'] > 0) {
				$cost_minus_tax = VikBooking::sayPackageMinusIva((float) $book_room['cust_cost'], (int) $book_room['cust_idiva']);
				$cost_plus_tax = VikBooking::sayPackagePlusIva((float) $book_room['cust_cost'], (int) $book_room['cust_idiva']);
			} else {
				$rate_plan_id = 0;
				$orig_tariff = VBORoomHelper::getInstance()->getTariffData($book_room['idtar']);
				if ($orig_tariff) {
					$rate_plan_id = $orig_tariff['idprice'];
					list($tax_aliq, $tax_name) = VBOTaxonomySummary::getTaxRatePlan($orig_tariff['idprice']);
				}
				$cost_minus_tax = VikBooking::sayCostMinusIva((float) $book_room['room_cost'], $rate_plan_id);
				$cost_plus_tax = VikBooking::sayCostPlusIva((float) $book_room['room_cost'], $rate_plan_id);
			}
			$pretax_amount = $order_item->addChild('preTaxAmount', number_format($cost_minus_tax, 2, '.', ''));
			$pretax_amount->addAttribute('currency', htmlspecialchars(strtoupper($listing_currency)));
			if (!strcasecmp($booking['status'], 'confirmed')) {
				$order_item->addChild('status', 'ACCEPTED');
			} elseif (!strcasecmp($booking['status'], 'standby')) {
				$order_item->addChild('status', 'PENDING');
			} else {
				$order_item->addChild('status', 'CANCELLED');
			}
			if ($tax_aliq) {
				// we omit this node because they say it's ignored
				// $order_item->addChild('taxRate', (float) $tax_aliq);
			}
			// we keep using the cost before taxes so that we will add a dedicated order-item node for taxes
			$total_amount = $order_item->addChild('totalAmount', number_format($cost_minus_tax, 2, '.', ''));
			$total_amount->addAttribute('currency', htmlspecialchars(strtoupper($listing_currency)));
			$rooms_total_amount += $cost_plus_tax;
			$rooms_net_amount 	+= $cost_minus_tax;
			$rooms_total_tax 	+= ($cost_plus_tax - $cost_minus_tax);

			// check if this room has got a refundable damage deposit assigned
			if (!empty($book_room['optionals'])) {
				$room_opts = explode(';', $book_room['optionals']);
				foreach ($room_opts as $room_opt) {
					$room_opt_data = explode(':', $room_opt);
					if (isset($damage_options[$room_opt_data[0]]) && $damage_options[$room_opt_data[0]]['cost'] > 0) {
						$damage_option = $damage_options[$room_opt_data[0]];
						// calculate the amount for the damage deposit
						$damage_dep_cost = $damage_option['cost'];
						if ($damage_option['pcentroom']) {
							$damage_dep_cost = $cost_plus_tax * $damage_dep_cost / 100;
						}
						if ($damage_option['perday']) {
							$damage_dep_cost *= $booking['days'];
						}
						if ($damage_option['perperson']) {
							$damage_dep_cost *= $book_room['adults'];
						}
						if ($damage_option['idiva']) {
							// always before taxes, because taxes will get a separate line item
							$damage_dep_cost = VikBooking::sayOptionalsMinusIva($damage_dep_cost, $damage_option['idiva']);
						}
						// set damage deposit gross amount
						$damage_deposits += $damage_dep_cost;
					}
				}
			}

			// check for damage deposits in extras
			if (!empty($book_room['extracosts'])) {
				$room_extras = is_array($book_room['extracosts']) ? $book_room['extracosts'] : (array)json_decode($book_room['extracosts'], true);
				foreach ($room_extras as $room_extra) {
					if (!is_array($room_extra) || empty($room_extra['type']) || !isset($room_extra['cost']) || $room_extra['cost'] <= 0) {
						continue;
					}
					if (!strcasecmp($room_extra['type'], 'DEPOSIT')) {
						// we've got a damage deposit stored as an extra service
						$damage_dep_cost = $room_extra['cost'];
						if (!empty($room_extra['idtax'])) {
							// always before taxes, because taxes will get a separate line item
							$damage_dep_cost = VikBooking::sayOptionalsMinusIva($damage_dep_cost, $room_extra['idtax']);
						}
						// set damage deposit gross amount
						$damage_deposits += $damage_dep_cost;
					}
				}
			}
		}

		// check for any kind of extra service and include it here
		if ($rooms_net_amount < ($booking['total'] - $booking['tot_taxes'] - $booking['tot_city_taxes'] - $damage_deposits)) {
			// there must be other services associated with the booking (options/extras)
			$extras_net = $booking['total'] - $booking['tot_taxes'] - $booking['tot_city_taxes'] - $damage_deposits - $rooms_net_amount;

			// add a dedicated node for the extra services before taxes
			$order_item = $order_item_list->addChild('orderItem');
			$order_item->addChild('feeType', 'MISC');
			$order_item->addChild('name', 'Extra services');
			$pretax_amount = $order_item->addChild('preTaxAmount', number_format($extras_net, 2, '.', ''));
			$pretax_amount->addAttribute('currency', htmlspecialchars(strtoupper($listing_currency)));
			if (!strcasecmp($booking['status'], 'confirmed')) {
				$order_item->addChild('status', 'ACCEPTED');
			} elseif (!strcasecmp($booking['status'], 'standby')) {
				$order_item->addChild('status', 'PENDING');
			} else {
				$order_item->addChild('status', 'CANCELLED');
			}
			// we keep using the cost before taxes so that we will add a dedicated order-item node for taxes
			$total_amount = $order_item->addChild('totalAmount', number_format($extras_net, 2, '.', ''));
			$total_amount->addAttribute('currency', htmlspecialchars(strtoupper($listing_currency)));
		}

		if ($damage_deposits) {
			// add a dedicated order line item for the security (damage) deposit
			$order_item = $order_item_list->addChild('orderItem');
			$order_item->addChild('feeType', 'DEPOSIT');
			$order_item->addChild('name', 'Damage deposit');
			$pretax_amount = $order_item->addChild('preTaxAmount', number_format($damage_deposits, 2, '.', ''));
			$pretax_amount->addAttribute('currency', htmlspecialchars(strtoupper($listing_currency)));
			$order_item->addChild('productId', 'DEPOSIT_DAMAGE');
			if (!strcasecmp($booking['status'], 'confirmed')) {
				$order_item->addChild('status', 'ACCEPTED');
			} elseif (!strcasecmp($booking['status'], 'standby')) {
				$order_item->addChild('status', 'PENDING');
			} else {
				$order_item->addChild('status', 'CANCELLED');
			}
			// we keep using the cost before taxes so that we will add a dedicated order-item node for taxes
			$total_amount = $order_item->addChild('totalAmount', number_format($damage_deposits, 2, '.', ''));
			$total_amount->addAttribute('currency', htmlspecialchars(strtoupper($listing_currency)));
		}

		// add a new order line item for taxes
		$total_taxes = ($booking['tot_taxes'] + $booking['tot_city_taxes']);
		if ($total_taxes > 0) {
			$order_item = $order_item_list->addChild('orderItem');
			$order_item->addChild('description', 'Tax');
			$order_item->addChild('feeType', 'TAX');
			$order_item->addChild('name', 'VAT/GST/City');
			$pretax_amount = $order_item->addChild('preTaxAmount', number_format($total_taxes, 2, '.', ''));
			$pretax_amount->addAttribute('currency', htmlspecialchars(strtoupper($listing_currency)));
			if (!strcasecmp($booking['status'], 'confirmed')) {
				$order_item->addChild('status', 'ACCEPTED');
			} elseif (!strcasecmp($booking['status'], 'standby')) {
				$order_item->addChild('status', 'PENDING');
			} else {
				$order_item->addChild('status', 'CANCELLED');
			}
			$total_amount = $order_item->addChild('totalAmount', number_format($total_taxes, 2, '.', ''));
			$total_amount->addAttribute('currency', htmlspecialchars(strtoupper($listing_currency)));
		}

		// reservation payment status
		if (!$booking['total'] || $booking['totpaid'] >= $booking['total']) {
			$pay_status = 'PAID';
		} elseif ($booking['totpaid'] > 0 && $booking['totpaid'] < $booking['total']) {
			$pay_status = 'PARTIAL_PAID';
		} else {
			$pay_status = 'UNPAID';
		}
		$update_details->addChild('reservationPaymentStatus', $pay_status);

		// details about the requested stay dates
		$reservation = $update_details->addChild('reservation');

		// count guests and pets (for when VBO will support pets)
		$tot_adults   = 0;
		$tot_children = 0;
		$tot_pets 	  = 0;
		foreach ($rooms as $book_room) {
			$tot_adults += $book_room['adults'];
			$tot_children += $book_room['children'];
			if (!empty($book_room['pets'])) {
				$tot_pets += $book_room['pets'];
			}
		}

		$reservation->addChild('numberOfAdults', $tot_adults);
		$reservation->addChild('numberOfChildren', $tot_children);
		$reservation->addChild('numberOfPets', $tot_pets);
		$stay_dates = $reservation->addChild('reservationDates');
		$stay_dates->addChild('beginDate', date('Y-m-d', $booking['checkin']));
		$stay_dates->addChild('endDate', date('Y-m-d', $booking['checkout']));
		// time is an integer between 0-23
		$reservation->addChild('checkinTime', date('G', $booking['checkin']));
		$reservation->addChild('checkoutTime', date('G', $booking['checkout']));
		// date when the booking originated
		$creation_dt = JFactory::getDate(date('Y-m-d H:i:s', $booking['ts']));
		$reservation->addChild('reservationOriginationDate', $creation_dt->format('Y-m-d\TH:i:s\Z'));

		// reservation overall status
		if (!strcasecmp($booking['status'], 'confirmed')) {
			$res_status = 'CONFIRMED';
		} elseif (!strcasecmp($booking['status'], 'standby')) {
			$res_status = 'UNCONFIRMED';
		} else {
			// check how it was cancelled (owner or traveler)
			if (VikBooking::getBookingHistoryInstance()->setBid($booking['id'])->hasEvent('CB')) {
				$res_status = 'CANCELLED_BY_OWNER';
			} else {
				$res_status = 'CANCELLED_BY_TRAVELER';
			}
		}
		$update_details->addChild('reservationStatus', $res_status);

		// get XML string
		$xml_str = $xml->asXML();

		// format XML string feed
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml_str);
		$dom->formatOutput = true;
		$xml_str = $dom->saveXML();

		if (!$render) {
			return $xml_str;
		}

		// output the XML document
		$this->outputDocument($xml_str);
	}

	/**
	 * Gets the listing's default Min LOS.
	 * 
	 * @param 	int 	$room_id 	the VBO room ID.
	 * 
	 * @return 	int 				the listing Min LOS.
	 */
	public function getListingDefaultMinStay($room_id)
	{
		$dbo = JFactory::getDbo();

		$min_los = 1;

		$q = "SELECT MIN(`days`) AS `min_nights` FROM `#__vikbooking_dispcost` WHERE `idroom`=" . (int) $room_id;
		$dbo->setQuery($q);
		$room_min_los = (int) $dbo->loadResult();

		if ($room_min_los > 1) {
			// we give the highest priority to the Rates Table
			return $room_min_los;
		}

		return $min_los;
	}

	/**
	 * Gets the listing's default Max LOS.
	 * 
	 * @param 	int 	$room_id 	the VBO room ID.
	 * 
	 * @return 	int 				the listing Max LOS.
	 */
	public function getListingDefaultMaxStay($room_id)
	{
		$dbo = JFactory::getDbo();

		$max_los = 999;

		$q = "SELECT MAX(`days`) AS `max_nights` FROM `#__vikbooking_dispcost` WHERE `idroom`=" . (int) $room_id;
		$dbo->setQuery($q);
		$room_max_los = (int) $dbo->loadResult();

		if ($room_max_los > 1) {
			// we give the highest priority to the Rates Table
			return $room_max_los;
		}

		return $max_los;
	}

	/**
	 * Gets the minimum booking advance period in days.
	 * 
	 * @param 	int 	$room_id 	the VBO room ID.
	 * 
	 * @return 	int 				the minimum days of prior notif.
	 */
	public function getListingMinPriorNotif($room_id = null)
	{
		// get the default value defined in the VBO settings
		return (int)VikBooking::getMinDaysAdvance($no_closing_dates = true);
	}

	/**
	 * Calculates the default range of dates by checking the maximum future date
	 * from today allowed for bookings. These dates will be used to define the
	 * range of availability dates that will be included within the XML feed.
	 * In case the pricing model is passed as "los", 6 months maximum will be used.
	 * 
	 * @param 	string 	$pricing_model 	the optional listing pricing model.
	 * @param 	int 	$room_id 		the optional VBO room ID.
	 * @param 	string 	$type 	        either "rate" or "availability".
	 * 
	 * @return 	array 					list of from and to dates in Y-m-d format (dates range).
	 * 
	 * @since 	1.9.4 	added 3rd argument $type.
	 */
	public function getListingDefaultAvailDatesRange($pricing_model = '', $vbo_room_id = null, $type = '')
	{
		// default to one year from today (or to 6 months in case of LOS pricing model)
		$dates_range = [
			date('Y-m-d'),
			date('Y-m-d', strtotime((!strcasecmp($pricing_model, 'los') ? '+6 months' : '+1 year'))),
		];

		/**
		 * Added support to fixed limit just for Vrbo to reduce the server resources needed.
		 * 
		 * @since 	1.9.4
		 */
		$vrbo_fixed_limit_period = $type === 'rate' ? VCMFactory::getConfig()->get('vrbo_xml_listing_fixed_limit_period_rate') : '';
		$vrbo_fixed_limit_period = preg_match("/^\+[1-9][0-9]*(d|w|m|y)$/i", (string) $vrbo_fixed_limit_period) ? $vrbo_fixed_limit_period : '';

		$max_adv_bookings_period = $vrbo_fixed_limit_period ?: VikBooking::getMaxDateFuture($vbo_room_id);
		if (empty($max_adv_bookings_period)) {
			return $dates_range;
		}

		$maxdate_val 	= (int)substr($max_adv_bookings_period, 1, (strlen($max_adv_bookings_period) - 2));
		$maxdate_period = substr($max_adv_bookings_period, -1, 1);

		if ($maxdate_val < 1) {
			// invalid period, revert to default dates
			return $dates_range;
		}

		if (!strcasecmp($pricing_model, 'los')) {
			// when LOS model, either less than 6 months or 6 months at most
			if ($maxdate_period == 'y') {
				// too many dates for the LOS model
				return $dates_range;
			}
			if ($maxdate_period == 'm' && $maxdate_val > 6) {
				// too many dates for the LOS model
				return $dates_range;
			}
			if ($maxdate_period == 'w' && $maxdate_val > 26) {
				// too many dates for the LOS model
				return $dates_range;
			}
			if ($maxdate_period == 'd' && $maxdate_val > 180) {
				// too many dates for the LOS model
				return $dates_range;
			}
		}

		// we need to save server resources, by setting a limit of 1 year unless voluntarily removed
		if ($maxdate_period == 'y' && $maxdate_val > 1) {
			// 3 years is the maximum limit
			$maxdate_val = $maxdate_val > 3 ? 3 : $maxdate_val;
			if (!VCMFactory::getConfig()->get('vrbo_xml_listing_availability_nolimit')) {
				// unless this record is created, we use one year at most
				return $dates_range;
			}
		}

		$now = getdate();
		if ($maxdate_period == 'w') {
			$dates_range[1] = date('Y-m-d', strtotime("+$numlim weeks"));
		} else {
			$next_month = $maxdate_period == 'm' ? ($now['mon'] + $maxdate_val) : $now['mon'];
			$next_day 	= $maxdate_period == 'd' ? ($now['mday'] + $maxdate_val) : $now['mday'];
			$next_year 	= $maxdate_period == 'y' ? ($now['year'] + $maxdate_val) : $now['year'];
			$dates_range[1] = date('Y-m-d', mktime(0, 0, 0, $next_month, $next_day, $next_year));
		}

		return $dates_range;
	}

	/**
	 * Attempts to extract the requested type of fees from a list of mandatory fee records.
	 * 
	 * @param 	array 	$mandatory_fees 	list of VBO options assigned to this listing.
	 * @param 	string 	$fee_type 			the type of fees to extract (cleaning, deposit, pets, city..).
	 * 
	 * @return 	array 						eligible option records found or empty array.
	 */
	public function getListingTypedFees(array $mandatory_fees, $fee_type)
	{
		$eligible_fees = [];

		if (!$mandatory_fees || empty($fee_type)) {
			return [];
		}

		// list of pre-translated wordings for "Cleaning fees" in other languages
		$cleaning_tn_langs = [
			'Cleaning',
			'Reinigungs',
			'Pulizia',
			'Nettoyage',
			'Ménage',
			'Limpieza',
		];

		foreach ($mandatory_fees as $man_fee) {
			if (!is_array($man_fee)) {
				continue;
			}

			$known_type = '';

			// cleaning fees (name must match translation for "Cleaning Fee" or pre-translated strings)
			$cleaning_matched = false;
			if (!empty($man_fee['name'])) {
				foreach ($cleaning_tn_langs as $cleaning_tn) {
					if (stripos($man_fee['name'], $cleaning_tn) !== false) {
						$cleaning_matched = true;
						break;
					}
				}
			}
			if (!empty($man_fee['name']) && (stripos($man_fee['name'], JText::_('VCM_AIRBNB_CLEANFEE')) !== false || $cleaning_matched === true)) {
				$known_type = 'cleaning';
				if (!strcasecmp($fee_type, 'cleaning')) {
					// push eligible fee
					$eligible_fees[] = $man_fee;
					continue;
				}
			}

			// damage deposit fees
			if (!empty($man_fee['oparams'])) {
				$fee_params = is_string($man_fee['oparams']) ? (array)json_decode($man_fee['oparams'], true) : $man_fee['oparams'];
				if (is_array($fee_params) && !empty($fee_params['damagedep'])) {
					$known_type = 'damage_deposit';
					if (!strcasecmp($fee_type, 'damage_deposit')) {
						// push eligible fee
						$eligible_fees[] = $man_fee;
						continue;
					}
				}
			}

			// pet fees (name must match translation for "Pet fee", or the apposite flag must be enabled in VBO)
			if (!empty($man_fee['oparams'])) {
				$fee_params = is_string($man_fee['oparams']) ? (array)json_decode($man_fee['oparams'], true) : $man_fee['oparams'];
				if (is_array($fee_params) && !empty($fee_params['pet_fee'])) {
					$known_type = 'pet';
					if (!strcasecmp($fee_type, 'pet')) {
						// push eligible fee
						$eligible_fees[] = $man_fee;
						continue;
					}
				}
			}
			if (!empty($man_fee['name']) && stripos($man_fee['name'], JText::_('VCM_AIRBNB_STDFEE_TYPE_PET')) !== false) {
				$known_type = 'pet';
				if (!strcasecmp($fee_type, 'pet')) {
					// push eligible fee
					$eligible_fees[] = $man_fee;
					continue;
				}
			}

			// city/tourist taxes
			if (!empty($man_fee['is_citytax'])) {
				$known_type = 'city';
				if (!strcasecmp($fee_type, 'city') || !strcasecmp($fee_type, 'tourist')) {
					// push eligible fee
					$eligible_fees[] = $man_fee;
					continue;
				}
			}

			/**
			 * Percent of rent fees with no tax (not belonging to any particular type).
			 * 
			 * @since 	1.9.6
			 */
			if (!$known_type && !strcasecmp($fee_type, 'percent') && !empty($man_fee['pcentroom']) && empty($man_fee['idiva'])) {
				$known_type = 'percent';
				// push eligible fee
				$eligible_fees[] = $man_fee;
				continue;
			}

			// other fees (not belonging to any particular type)
			if (!$known_type && !strcasecmp($fee_type, 'other')) {
				// push eligible fee
				$eligible_fees[] = $man_fee;
				continue;
			}
		}

		return $eligible_fees;
	}

	/**
	 * Loads the necessary listing resources to compose the XML feed given the input vars.
	 * In case of validation errors, the execution is truncated with an error status code.
	 * 
	 * @param 	array 	$channel 	 	the channel information.
	 * @param 	string 	$acccount_key 	the Vrbo account identifier.
	 * @param 	string 	$listing_id	 	the Vrbo listing ID to render.
	 * 
	 * @return 	array 				 associative array of listing decoded settings.
	 * 
	 * @throws 	Exception
	 */
	private function getListingData(array $channel, $account_key, $listing_id)
	{
		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int) $channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$listing_record = $dbo->loadObject();
		if (!$listing_record) {
			throw new Exception('Not Found', 404);
		}

		$listing_data = json_decode($listing_record->setting, true);
		if (!is_array($listing_data)) {
			throw new Exception('Precondition Failed', 412);
		}

		return $listing_data;
	}

	/**
	 * Loads the necessary listing resources to compose the XML feed given the input vars.
	 * In case of validation errors, the execution is truncated with an error status code.
	 * 
	 * @param 	array 	$channel 	 the channel information.
	 * @param 	string 	$listing_id	 the Vrbo listing ID to render.
	 * @param 	bool 	$mapped 	 if true, the listing must be mapped to a website room.
	 * 
	 * @return 	array 				 list of listing resources.
	 * 
	 * @throws 	Exception
	 */
	private function loadListingResources(array $channel, $listing_id, $mapped = true)
	{
		$dbo = JFactory::getDbo();

		if (empty($listing_id) || empty($channel['uniquekey']) || $channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
			throw new Exception('Bad Request', 400);
		}

		$account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
		if (!empty($channel['params']) && !empty($channel['params']['hotelid'])) {
			$account_key = $channel['params']['hotelid'];
		}

		if (!$account_key) {
			throw new Exception('Unauthorized', 401);
		}

		$q = "SELECT * FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int) $channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($listing_id) . " AND `param`=" . $dbo->quote('listing_content');
		$dbo->setQuery($q, 0, 1);
		$listing_record = $dbo->loadObject();
		if (!$listing_record) {
			throw new Exception('Not Found', 404);
		}

		$listing_data = json_decode($listing_record->setting, true);
		if (!is_array($listing_data)) {
			throw new Exception('Precondition Failed', 412);
		}

		// minimum content validation
		$min_cont_validation = VCMVrboListing::contentValidationPass($listing_data);
		if ($min_cont_validation[0] === false) {
			throw new Exception('The listing content is insufficient: ' . $min_cont_validation[1], 500);
		}

		// load corresponding room in VBO
		$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`=" . $dbo->quote($listing_record->idroomota);
		$dbo->setQuery($q, 0, 1);
		$vbo_listing_record = $dbo->loadAssoc();
		if (!$vbo_listing_record) {
			throw new Exception('Website Listing Not Found', 404);
		}

		if ($mapped && !VCMVrboListing::getListingMapping($listing_id)) {
			throw new Exception('Listing is off sync', 500);
		}

		return [
			$account_key,
			$listing_record,
			$listing_data,
			$vbo_listing_record,
		];
	}

	/**
	 * Adds child XML nodes for the available text locale values.
	 * 
	 * @param 	SimpleXMLElement 	$xml_node 	 the node where children will be added to.
	 * @param 	string 				$locale 	 the default locale to use.
	 * @param 	string 				$value 		 the value to set for the default locale.
	 * @param 	string 				$tn_key 	 the key to fetch from the translated records.
	 * @param 	array 				$tn_records  associative list of translations per locale.
	 * 
	 * @return 	void
	 */
	private function addXmlTextLocaleValues($xml_node, $locale, $value, $tn_key = null, array $tn_records = [])
	{
		// populate text nodes for default locale
		$texts = $xml_node->addChild('texts');
		$text = $texts->addChild('text');
		$text->addAttribute('locale', (string) $locale);
		$text->addChild('textValue', htmlspecialchars(strip_tags((string) $value)));

		if (!$tn_key) {
			return;
		}

		// parse translated records
		foreach ($tn_records as $tn_locale => $records) {
			if ($tn_key == 'propertyName') {
				/**
				 * It is mandatory to translate the "propertyName" in other locales, if translations are available
				 * even though in VBO this info is not a translatable field.
				 * 
				 * @since 	1.8.20
				 */
				$text = $texts->addChild('text');
				$text->addAttribute('locale', (string) $tn_locale);
				$text->addChild('textValue', htmlspecialchars(strip_tags((string) $value)));
				continue;
			}
			foreach ($records as $tn_record) {
				if (!is_array($tn_record) || empty($tn_record[$tn_key])) {
					continue;
				}
				// populate text nodes for current locale
				$text = $texts->addChild('text');
				$text->addAttribute('locale', (string) $tn_locale);
				$text->addChild('textValue', htmlspecialchars(strip_tags((string) $tn_record[$tn_key])));
			}
		}

		return;
	}

	/**
	 * Loads the room rates for the lowest number of nights.
	 * 
	 * @param 	int 	$room_id 	the VBO room ID.
	 * @param 	int 	$rplan_id 	the room rate plan ID.
	 * 
	 * @return 	array
	 */
	private function getRoomRates($room_id, $rplan_id)
	{
		$dbo = JFactory::getDbo();

		$room_id  = (int) $room_id;
		$rplan_id = (int) $rplan_id;

		$q = "SELECT `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name`,`p`.`minlos`
			FROM `#__vikbooking_dispcost` AS `r`
			LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` 
			WHERE `r`.`idroom`={$room_id} AND `r`.`idprice`={$rplan_id}
			ORDER BY `r`.`days` ASC, `r`.`cost` ASC";
		$dbo->setQuery($q, 0, 50);
		$full_room_rates = $dbo->loadAssocList();

		if (!$full_room_rates) {
			return [];
		}

		// count the average cost per night for the lowest number of nights defined
		foreach ($full_room_rates as &$full_room_rate) {
			$full_room_rate['cost'] = round(($full_room_rate['cost'] / $full_room_rate['days']), 2);
			$full_room_rate['days'] = 1;

			// stop the loop after the first room-day-rate found
			break;
		}

		// unset last reference
		unset($full_room_rate);

		// return the room rate for the lowest number of nights
		return $full_room_rates[0];
	}

	/**
	 * Loads the LOS room rates for any allowed number of nights.
	 * We load records up to 180 combinations of nights of stay.
	 * 
	 * @param 	int 	$room_id 	the VBO room ID.
	 * @param 	int 	$rplan_id 	the room rate plan ID.
	 * @param 	bool 	$avg_costs 	whether to get the average cost for one night for any LOS.
	 * 
	 * @return 	array 				associative list of LOS room rates.
	 */
	private function getLOSRoomRates($room_id, $rplan_id, $avg_costs = true)
	{
		$dbo = JFactory::getDbo();

		$room_id  = (int) $room_id;
		$rplan_id = (int) $rplan_id;

		$q = "SELECT `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name`,`p`.`minlos`
			FROM `#__vikbooking_dispcost` AS `r`
			LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` 
			WHERE `r`.`idroom`={$room_id} AND `r`.`idprice`={$rplan_id}
			ORDER BY `r`.`days` ASC, `r`.`cost` ASC";
		$dbo->setQuery($q, 0, 180);
		$los_room_rates = $dbo->loadAssocList();

		if (!$los_room_rates) {
			return [];
		}

		$los_assoc_rates = [];

		// count the average cost per night for the lowest number of nights defined
		foreach ($los_room_rates as $los_room_rate) {
			// set the LOS nights original value
			$los_room_rate['los_nights'] = $los_room_rate['days'];
			if ($avg_costs) {
				// count the average cost per night
				$los_room_rate['cost'] = round(($los_room_rate['cost'] / $los_room_rate['days']), 2);
				// set the number of nights to 1 for the average cost
				$los_room_rate['days'] = 1;
			}
			// push LOS rate
			$los_assoc_rates[$los_room_rate['los_nights']] = $los_room_rate;
		}

		// return the associative LOS room rates for any number of nights of stay allowed
		return $los_assoc_rates;
	}

	/**
	 * Fetches the bookings involving the channel Vrbo API.
	 * 
	 * @param 	string 	$min_last_updated_date 	the minimum booking last updated date (Y-m-d).
	 * @param 	string 	$min_checkout_date 		the minimum checkout date (Y-m-d).
	 * 
	 * @return 	array 	associative list of booking base details.
	 */
	private function loadOnlineBookings($min_last_updated_date = '', $min_checkout_date = '')
	{
		$dbo = JFactory::getDbo();

		if ($min_last_updated_date) {
			// make sure the minimum last updated date is a datetime and not just an Y-m-d string
			$min_last_updated_date = JFactory::getDate($min_last_updated_date, date_default_timezone_get())->toSql($local = true);
		}

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('b') . '.*');
		$q->select(sprintf('MAX(%s) AS %s', $dbo->qn('h.dt'), $dbo->qn('last_updated')));
		$q->from($dbo->qn('#__vikbooking_orders', 'b'));
		$q->leftjoin($dbo->qn('#__vikbooking_orderhistory', 'h') . ' ON ' . $dbo->qn('h.idorder') . ' = ' . $dbo->qn('b.id'));
		$q->where($dbo->qn('b.idorderota') . ' IS NOT NULL');
		$q->where($dbo->qn('b.channel') . ' LIKE ' . $dbo->q('vrboapi%'));
		if ($min_checkout_date) {
			$q->where($dbo->qn('b.checkout') . ' >= ' . (int)strtotime($min_checkout_date));
		}
		if ($min_last_updated_date) {
			$q->having($dbo->qn('last_updated') . ' >= ' . $dbo->q($min_last_updated_date));
		}
		$q->group($dbo->qn('b.id'));
		$q->order($dbo->qn('b.id') . ' DESC');

		$dbo->setQuery($q);

		return $dbo->loadAssocList();
	}

	/**
	 * The document of type Booking Content Index can be requested with
	 * some filters posted through an XML request, such as the minimum
	 * date for the bookings last updated date.
	 * 
	 * @param 	string 	$acccount_key 	the Vrbo account identifier.
	 * 
	 * @return 	array 	empty or associative array of limits to apply.
	 */
	private function collectBookingIndexRequestLimits($account_key)
	{
		$limits  = [];
		$xml_req = null;

		try {
			// get the full request body
			$body_raw_rq = file_get_contents('php://input');

			if (!empty($body_raw_rq)) {
				// parse the XML request document
				$xml = simplexml_load_string($body_raw_rq);

				// validate document
				if (is_object($xml) && property_exists($xml, 'startDate')) {
					if (property_exists($xml, 'advertiser') && property_exists($xml->advertiser, 'assignedId')) {
						// set the whole request object
						$xml_req = $xml;
					}
				}
			}
		} catch (Exception $e) {
			// do nothing
		}

		if (!$xml_req || (string) $xml_req->advertiser->assignedId != $account_key) {
			// no request or invalid Vrbo PM identifier
			return $limits;
		}

		$req_start_date = (string) $xml_req->startDate;
		if (!empty($req_start_date)) {
			// dates will be always passed in UTC format, and DateTime uses UTC by default
			$limits['min_last_updated_date'] = JFactory::getDate($req_start_date)->format('Y-m-d');
		}

		return $limits;
	}

	/**
	 * Counts the number of inclusive nights in the range of dates provided.
	 * 
	 * @param 	string 	$from_ymd 	the from date in Y-m-d format.
	 * @param 	string 	$to_ymd 	the to date in Y-m-d format.
	 * 
	 * @return 	int 				the number of nights included in the range.
	 */
	private function countTotalInclusiveNights($from_ymd, $to_ymd)
	{
		$from_date = new DateTime($from_ymd);
		$to_date   = new DateTime($to_ymd);

		$dates_diff = $from_date->diff($to_date);

		if (!$dates_diff) {
			return 0;
		}

		// dates are inclusive, so we always increment the difference in days by one
		return (abs($dates_diff->days) + 1);
	}

	/**
	 * Applies sorting over the array of OBP Guest Fees calculated.
	 * 
	 * @param 	array 	$obp_guest_fees 	the computed OBP guest fees.
	 * 
	 * @return 	void
	 */
	private function sortOBPGuestFees(array &$obp_guest_fees)
	{
		$sort_map = [];
		foreach ($obp_guest_fees as $k => $obp_guest_fee) {
			$sort_map[$k] = $obp_guest_fee['occ'];
		}

		// sort map in ascending order and keep keys ordering
		asort($sort_map);

		$sorted_obp_guest_fees = [];
		foreach ($sort_map as $k => $occ) {
			$sorted_obp_guest_fees[] = $obp_guest_fees[$k];
		}

		// replace array
		$obp_guest_fees = $sorted_obp_guest_fees;
	}

	/**
	 * Given a tax record ID returns the defined aliquote.
	 * 
	 * @param 	int 	$tax_id 	the tax record ID.
	 * 
	 * @return 	int|float 			the aliquote for the tax ID.
	 */
	private function getAliquoteFromTaxId($tax_id)
	{
		if (isset($this->cache_tax_rates[$tax_id])) {
			return $this->cache_tax_rates[$tax_id];
		}

		$aliquote = VikBooking::getAliq((int) $tax_id);
		if (!$aliquote || !($aliquote > 0)) {
			$aliquote = 0;
		}

		// cache value and return it
		$this->cache_tax_rates[$tax_id] = $aliquote;

		return $aliquote;
	}

	/**
	 * Given a rate plan ID returns the assigned aliquote.
	 * 
	 * @param 	int 	$rplan_id 	the rate plan record ID.
	 * 
	 * @return 	int|float 			the aliquote for the tax ID assigned.
	 */
	private function getAliquoteFromRatePlanId($rplan_id)
	{
		if (!$rplan_id) {
			return 0;
		}

		$rate_plan = VikBooking::getPriceInfo((int) $rplan_id);
		if (!$rate_plan || !is_array($rate_plan) || empty($rate_plan['idiva'])) {
			return 0;
		}

		return $this->getAliquoteFromTaxId($rate_plan['idiva']);
	}

	/**
	 * Calculates the given amount before taxes, if tax included.
	 * 
	 * @param 	float 	$amount 	the amount inclusive of taxes.
	 * @param 	int 	$rplan_id 	the VBO rate plan ID.
	 * 
	 * @return 	float 				the amount exclusive of taxes.
	 */
	private function calcAmountBeforeTax($amount, $rplan_id)
	{
		if (!($amount > 0) || !$rplan_id || !$this->tax_inclusive) {
			// nothing to calculate
			return $amount;
		}

		$aliquote = $this->getAliquoteFromRatePlanId($rplan_id);
		if (!$aliquote || !($aliquote > 0)) {
			// tax rate not found or not applicable
			return $amount;
		}

		return $amount / ((100 + $aliquote) / 100);
	}

	/**
	 * Applies the charge or discount defined for this channel, room and
	 * rate plan defined through the "Bulk Action - Rates Upload" settings.
	 * 
	 * @param 	float 	$cost_before_tax 	the amount to alter.
	 * @param 	string 	$rates_alteration 	the string ideintifying the rule (i.e. "+18%").
	 * 
	 * @return 	foat 						either the original or the altered amount.
	 */
	private function applyRateAlterationRule($cost_before_tax, $rates_alteration)
	{
		if (!$rates_alteration) {
			return $cost_before_tax;
		}

		$alter_type = 'charge';
		$fixed_pcent = 'fixed';
		$rmodop = substr($rates_alteration, 0, 1);
		if ($rmodop == '+') {
			$alter_type = 'charge';
		} elseif ($rmodop == '-') {
			$alter_type = 'discount';
		} else {
			return $cost_before_tax;
		}

		// fixed or percent alteration
		if (substr($rates_alteration, -1, 1) == '%') {
			$fixed_pcent = 'pcent';
		}

		// get only numbers
		$alter_amount = (float)preg_replace("/[^0-9\.]+/", '', $rates_alteration);

		if (!$alter_amount) {
			return $cost_before_tax;
		}

		if ($alter_type == 'charge') {
			// increase rates
			if ($fixed_pcent == 'pcent') {
				// percentage charge
				$cost_before_tax = $cost_before_tax * (100 + $alter_amount) / 100;
			} else {
				// fixed charge
				$cost_before_tax += $alter_amount;
			}
		} else {
			// discount rates
			if ($fixed_pcent == 'pcent') {
				// percentage discount
				$disc_op = $cost_before_tax * $alter_amount / 100;
				$cost_before_tax -= $disc_op;
			} else {
				// fixed discount
				$cost_before_tax -= $alter_amount;
			}
		}

		return $cost_before_tax;
	}

	/**
	 * Sets the proper check-in/out constraints in class properties.
	 * 
	 * @return 	void
	 */
	private function setInOutConstraints()
	{
		$checkinh = 0;
		$checkinm = 0;
		$checkouth = 0;
		$checkoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			if ($timeopst[0] < $timeopst[1]) {
				// set no arrivals/depatures on the same day
				$this->inonout_allowed = false;
			}
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$checkinh = $opent[0];
			$checkinm = $opent[1];
			$checkouth = $closet[0];
			$checkoutm = $closet[1];
		}

		// set properties
		$this->checkin_h  = $checkinh;
		$this->checkin_m  = $checkinm;
		$this->checkout_h = $checkouth;
		$this->checkout_m = $checkoutm;

		// set rates before/after tax
		$this->tax_inclusive = VikBooking::ivaInclusa();
	}

	/**
	 * Returns the phone prefix for the given country 3 code.
	 * 
	 * @param 	string 	$country_3_code 	the country 3 code.
	 * 
	 * @return 	string 	empty string or trimmed country phone prefix.
	 */
	private function getCountryPhonePrefix($country_3_code)
	{
		$dbo = JFactory::getDbo();

		$q = "SELECT `phone_prefix` FROM `#__vikbooking_countries` WHERE `country_3_code`=" . $dbo->quote($country_3_code);
		$dbo->setQuery($q, 0, 1);
		$prefix = $dbo->loadResult();

		if (empty($prefix)) {
			return '';
		}

		return str_replace(' ', '', $prefix);
	}

	/**
	 * Tells whether the cached XML file exists.
	 * 
	 * @param 	string 	$x_type 		the type of XML document.
	 * @param 	string 	$listing_id 	the Vrbo listing ID involved.
	 * 
	 * @return 	bool 	true if the cached file exists, or false.
	 * 
	 * @since 	1.9.5 	method visibility has become public.
	 */
	public function cachedFileExists($x_type, $listing_id = '')
	{
		return is_file($this->getCachedFilePath($x_type, $listing_id));
	}

	/**
	 * Builds the full path and file name to the cached XML file.
	 * 
	 * @param 	string 	$x_type 		the processed type of XML document.
	 * @param 	string 	$listing_id 	the Vrbo listing ID involved.
	 * 
	 * @return 	string 	the full file name path, or an empty string.
	 */
	private function getCachedFilePath($x_type, $listing_id = '')
	{
		$file_name = '';

		if (empty($x_type)) {
			return $file_name;
		}

		// clean up values
		$x_type = preg_replace("/[^a-z0-9\_\-]/", '', strtolower($x_type));
		$listing_id = preg_replace("/[^0-9]/", '', $listing_id);

		// prefix name
		if (!empty($listing_id)) {
			$prefix_name = "vrbo_{$listing_id}_{$x_type}_";
		} else {
			// booking content index and other types of XML docs do not have a listing ID set
			$prefix_name = "vrbo_{$x_type}_";
		}

		// build file name
		$file_name = $prefix_name . md5(VikChannelManager::getApiKey()) . '.xml';

		return implode(DIRECTORY_SEPARATOR, [VCM_ADMIN_PATH, 'assets', 'xml', $file_name]);
	}

	/**
	 * Outputs the cached XML document.
	 * 
	 * @param 	string 	$x_type 		the type of XML document.
	 * @param 	string 	$listing_id 	the Vrbo listing ID involved.
	 * 
	 * @return 	void|bool 				false in case of errors, or void.
	 */
	private function outputCachedDocument($x_type, $listing_id = '')
	{
		$xml_str = file_get_contents($this->getCachedFilePath($x_type, $listing_id));

		if (!$xml_str) {
			return false;
		}

		// output the XML document
		$this->outputDocument($xml_str);
	}

	/**
	 * Stores an XML file locally.
	 * 
	 * @param 	string 	$xml_str 	the content of the XML file.
	 * @param 	string 	$xml_path 	the full path and file name
	 * 								where the file should be stored.
	 * 
	 * @return 	bool 	true if the file was stored, or false.
	 */
	private function storeCachedFile($xml_str, $xml_path)
	{
		if (empty($xml_str) || empty($xml_path)) {
			return false;
		}

		return JFile::write($xml_path, $xml_str);
	}

	/**
	 * Outputs the XML document.
	 * 
	 * @param 	string 	$xml_str 	the content of the XML file.
	 * 
	 * @return 	void
	 */
	private function outputDocument($xml_str)
	{
		/**
		 * Make sure no other plugins have broken our buffer with an empty space.
		 * Scan all active buffers and clean them because we want an empty output.
		 */
		while (ob_get_status()) {
			// repeat until the buffer is empty
			ob_end_clean();
		}

		// set proper headers
		$this->app->setHeader('Content-Type', 'application/xml', $replace = true);

		// output the XML document
		VCMHttpDocument::getInstance($this->app)->close(200, $xml_str);
	}
}
