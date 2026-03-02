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
 * Vrbo listing content helper.
 * 
 * @since 	1.8.12
 */
final class VCMVrboListing
{
	/**
	 * @var 	array
	 */
	public static $supported_locales = [
		'da' => 'Danish',
		'de' => 'German',
		'en' => 'English',
		'es' => 'Spanish',
		'fi' => 'Finnish',
		'fr' => 'French',
		'el' => 'Greek',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'nl' => 'Dutch',
		'no' => 'Norwegian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'ru' => 'Russian',
		'sv' => 'Swedish',
		'tr' => 'Turkish',
	];

	/**
	 * @var 	array
	 */
	public static $booking_locales = [
		'af_ZA',
		'ar_AE',
		'ar_BH',
		'ar_DZ',
		'ar_EG',
		'ar_IQ',
		'ar_JO',
		'ar_KW',
		'ar_LB',
		'ar_LY',
		'ar_MA',
		'ar_OM',
		'ar_QA',
		'ar_SA',
		'ar_SY',
		'ar_TN',
		'ar_YE',
		'az_AZ',
		'be_BY',
		'bg_BG',
		'ca_ES',
		'cs_CZ',
		'da_DK',
		'de_AT',
		'de_CH',
		'de_DE',
		'de_LI',
		'de_LU',
		'div_MV',
		'el_GR',
		'en_AU',
		'en_BZ',
		'en_CA',
		'en_CB',
		'en_GB',
		'en_HK',
		'en_IE',
		'en_IN',
		'en_JP',
		'en_JM',
		'en_LK',
		'en_NZ',
		'en_PH',
		'en_SG',
		'en_TT',
		'en_US',
		'es_AR',
		'es_BO',
		'es_CL',
		'es_CO',
		'es_CR',
		'es_DO',
		'es_EC',
		'es_ES',
		'es_GT',
		'es_HN',
		'es_MX',
		'es_NI',
		'es_PA',
		'es_PE',
		'es_PR',
		'es_PY',
		'es_SV',
		'es_US',
		'es_UY',
		'es_VE',
		'et_EE',
		'eu_ES',
		'fa_IR',
		'fi_FI',
		'fo_FO',
		'fr_BE',
		'fr_CA',
		'fr_CH',
		'fr_FR',
		'fr_LU',
		'fr_MC',
		'gl_ES',
		'gu_IN',
		'he_IL',
		'hi_IN',
		'hr_HR',
		'hu_HU',
		'hy_AM',
		'id_ID',
		'is_IS',
		'it_CH',
		'it_IT',
		'ja_JP',
		'ka_GE',
		'kn_IN',
		'kk_KZ',
		'kok_IN',
		'ko_KR',
		'ky_KZ',
		'lt_LT',
		'lv_LV',
		'mk_MK',
		'mn_MN',
		'mr_IN',
		'ms_BN',
		'ms_MY',
		'nb_NO',
		'nl_BE',
		'nl_NL',
		'nn_NO',
		'pa_IN',
		'pl_PL',
		'pt_BR',
		'pt_PT',
		'ro_RO',
		'ru_RU',
		'sa_IN',
		'sk_SK',
		'sl_SI',
		'sq_AL',
		'sr_SP',
		'sv_FI',
		'sv_SE',
		'sw_KE',
		'syr_SY',
		'ta_IN',
		'te_IN',
		'th_TH',
		'tr_TR',
		'tt_RU',
		'uk_UA',
		'ur_PK',
		'uz_UZ',
		'vi_VN',
		'zh_CN',
		'zh_CHS',
		'zh_CHT',
		'zh_HK',
		'zh_MO',
		'zh_SG',
		'zh_TW',
	];

	/**
	 * Checks if the given locale is supported, or returns all the supported locales.
	 * 
	 * @param 	string 	$lang_tag 	the locale to validate (i.e. it_IT, it-IT or it).
	 * 
	 * @return 	mixed 	false on failure, or array.
	 */
	public static function getSupportedLocale($lang_tag = null)
	{
		if ($lang_tag) {
			$country_tag = '';
			if (strlen($lang_tag) > 2) {
				$orig_value = $lang_tag;
				$orig_value = str_replace(['_', '-'], '-', $orig_value);
				$lang_parts = explode('-', $orig_value);
				if (count($lang_parts) > 1) {
					$country_tag = $lang_parts[1];
				}
				// adjust length
				$lang_tag = substr($lang_tag, 0, 2);
			}

			$lang_tag = strtolower($lang_tag);
			$country_tag = strtolower($country_tag);

			if (isset(static::$supported_locales[$lang_tag])) {
				return [$lang_tag => static::$supported_locales[$lang_tag]];
			}

			if ($country_tag && isset(static::$supported_locales[$country_tag])) {
				return [$country_tag => static::$supported_locales[$country_tag]];
			}

			return false;
		}

		return static::$supported_locales;
	}

	/**
	 * Attempts to validate the current booking language against
	 * the supported locales for the reservations (4-char ISO Code).
	 * 
	 * @param 	string 	$lang_tag 	the current language tag assigned to the booking.
	 * 
	 * @return 	string 				the supported booking locale code.
	 */
	public static function matchBookingLocale($lang_tag)
	{
		// default value
		$def_booking_locale = 'en_US';

		if (!$lang_tag) {
			return $def_booking_locale;
		}

		$lang_tag = str_replace('-', '_', $lang_tag);

		if ($lang_tag == 'el') {
			return 'el_GR';
		}

		if (strpos($lang_tag, '_') !== false) {
			$lang_parts = explode('_', $lang_tag);
			// sanitize values
			$safe_lang_parts = [
				// the language identifier should be lowercase
				strtolower($lang_parts[0]),
				// the country identifier should be uppercase
				strtoupper($lang_parts[1]),
			];
			// re-build the original string
			$lang_tag = implode('_', $safe_lang_parts);
		}

		if (in_array($lang_tag, static::$booking_locales)) {
			// the language tag is supported
			return $lang_tag;
		}

		return $def_booking_locale;
	}

	/**
	 * Returns an associative list of amenity codes and values.
	 * 
	 * @return 	array
	 */
	public static function getAmenityCodesData()
	{
		return [
			// Sports and adventure
			'SPORTS_BASKETBALL_COURT' => [
				'group' => 'Sports and adventure',
				'name' => 'Basketball court',
			],
			'SPORTS_CYCLING' => [
				'group' => 'Sports and adventure',
				'name' => 'Cycling',
			],
			'SPORTS_CROSS_COUNTRY_SKIING' => [
				'group' => 'Sports and adventure',
				'name' => 'Cross country skiing',
			],
			'SPORTS_DEEPSEA_FISHING' => [
				'group' => 'Sports and adventure',
				'name' => 'Deepsea fishing',
			],
			'SPORTS_FISHING' => [
				'group' => 'Sports and adventure',
				'name' => 'Fishing',
			],
			'SPORTS_FISHING_BAY' => [
				'group' => 'Sports and adventure',
				'name' => 'Fishing bay',
			],
			'SPORTS_FISHING_FLY' => [
				'group' => 'Sports and adventure',
				'name' => 'Fishing fly',
			],
			'SPORTS_FISHING_FRESHWATER' => [
				'group' => 'Sports and adventure',
				'name' => 'Fishing freshwater',
			],
			'SPORTS_FISHING_SURF' => [
				'group' => 'Sports and adventure',
				'name' => 'Fishing surf',
			],
			'SPORTS_GOLF' => [
				'group' => 'Sports and adventure',
				'name' => 'Golf',
			],
			'SPORTS_GOLF_OPTIONAL' => [
				'group' => 'Sports and adventure',
				'name' => 'Golf optional',
			],
			'SPORTS_HIKING' => [
				'group' => 'Sports and adventure',
				'name' => 'Hiking',
			],
			'SPORTS_HUNTING' => [
				'group' => 'Sports and adventure',
				'name' => 'Hunting',
			],
			'SPORTS_ICE_SKATING' => [
				'group' => 'Sports and adventure',
				'name' => 'Ice skating',
			],
			'SPORTS_JET_SKIING' => [
				'group' => 'Sports and adventure',
				'name' => 'Jet skiing',
			],
			'SPORTS_KAYAKING' => [
				'group' => 'Sports and adventure',
				'name' => 'Kayaking',
			],
			'SPORTS_MOUNTAIN_BIKING' => [
				'group' => 'Sports and adventure',
				'name' => 'Mountain biking',
			],
			'SPORTS_MOUNTAIN_CLIMBING' => [
				'group' => 'Sports and adventure',
				'name' => 'Mountain climbing',
			],
			'SPORTS_MOUNTAINEERING' => [
				'group' => 'Sports and adventure',
				'name' => 'Mountaineering',
			],
			'SPORTS_PARASAILING' => [
				'group' => 'Sports and adventure',
				'name' => 'Parasailing',
			],
			'SPORTS_PIER_FISHING' => [
				'group' => 'Sports and adventure',
				'name' => 'Pier fishing',
			],
			'SPORTS_RAFTING' => [
				'group' => 'Sports and adventure',
				'name' => 'Rafting',
			],
			'SPORTS_ROCK_CLIMBING' => [
				'group' => 'Sports and adventure',
				'name' => 'Rock climbing',
			],
			'SPORTS_SAILING' => [
				'group' => 'Sports and adventure',
				'name' => 'Sailing',
			],
			'SPORTS_SCUBA_OR_SNORKELING' => [
				'group' => 'Sports and adventure',
				'name' => 'Scuba or snorkeling',
			],
			'SPORTS_SKI_LIFT_PRIVILEGES' => [
				'group' => 'Sports and adventure',
				'name' => 'Ski lift privileges',
			],
			'SPORTS_SKI_LIFT_PRIVILEGES_OPTIONAL' => [
				'group' => 'Sports and adventure',
				'name' => 'Ski lift privileges optional',
			],
			'SPORTS_SKIING' => [
				'group' => 'Sports and adventure',
				'name' => 'Skiing',
			],
			'SPORTS_SKIING_WATER' => [
				'group' => 'Sports and adventure',
				'name' => 'Skiing water',
			],
			'SPORTS_SNORKELING' => [
				'group' => 'Sports and adventure',
				'name' => 'Snorkeling',
			],
			'SPORTS_SNORKELING_DIVING' => [
				'group' => 'Sports and adventure',
				'name' => 'Snorkeling diving',
			],
			'SPORTS_SNOWBOARDING' => [
				'group' => 'Sports and adventure',
				'name' => 'Snowboarding',
			],
			'SPORTS_SNOWMOBILING' => [
				'group' => 'Sports and adventure',
				'name' => 'Snowmobiling',
			],
			'SPORTS_SPELUNKING' => [
				'group' => 'Sports and adventure',
				'name' => 'Spelunking',
			],
			'SPORTS_SURFING' => [
				'group' => 'Sports and adventure',
				'name' => 'Surfing',
			],
			'SPORTS_SWIMMING' => [
				'group' => 'Sports and adventure',
				'name' => 'Swimming',
			],
			'SPORTS_TUBING_WATER' => [
				'group' => 'Sports and adventure',
				'name' => 'Tubing water',
			],
			'SPORTS_WHITEWATER_RAFTING' => [
				'group' => 'Sports and adventure',
				'name' => 'Whitewater rafting',
			],
			'SPORTS_WIND_SURFING' => [
				'group' => 'Sports and adventure',
				'name' => 'Wind surfing',
			],
			// Car
			'CAR_NECESSARY' => [
				'group' => 'Car',
				'name' => 'Car is necessary',
			],
			'CAR_NOT_NECESSARY' => [
				'group' => 'Car',
				'name' => 'Car is not necessary',
			],
			'CAR_RECOMMENDED' => [
				'group' => 'Car',
				'name' => 'Car is recommended',
			],
			// General
			'EV_CAR_CHARGER' => [
				'group' => 'General',
				'name' => 'EV car charger',
			],
			'FIRE_PIT' => [
				'group' => 'General',
				'name' => 'Fire pit',
			],
			// Locations
			'LOCATION_TYPE_BEACH' => [
				'group' => 'Locations',
				'name' => 'Beach',
			],
			'LOCATION_TYPE_BEACH_FRONT' => [
				'group' => 'Locations',
				'name' => 'Beach front',
			],
			'LOCATION_TYPE_BEACH_VIEW' => [
				'group' => 'Locations',
				'name' => 'Beach view',
			],
			'LOCATION_TYPE_DOWNTOWN' => [
				'group' => 'Locations',
				'name' => 'Downtown',
			],
			'LOCATION_TYPE_GOLF_COURSE_FRONT' => [
				'group' => 'Locations',
				'name' => 'Golf course front',
			],
			'LOCATION_TYPE_GOLF_COURSE_VIEW' => [
				'group' => 'Locations',
				'name' => 'Golf course view',
			],
			'LOCATION_TYPE_LAKE' => [
				'group' => 'Locations',
				'name' => 'Lake',
			],
			'LOCATION_TYPE_LAKE_FRONT' => [
				'group' => 'Locations',
				'name' => 'Lake front',
			],
			'LOCATION_TYPE_LAKE_VIEW' => [
				'group' => 'Locations',
				'name' => 'Lake view',
			],
			'LOCATION_TYPE_MOUNTAIN' => [
				'group' => 'Locations',
				'name' => 'Mountain',
			],
			'LOCATION_TYPE_MOUNTAIN_VIEW' => [
				'group' => 'Locations',
				'name' => 'Mountain view',
			],
			'LOCATION_TYPE_NEAR_OCEAN' => [
				'group' => 'Locations',
				'name' => 'Near ocean',
			],
			'LOCATION_TYPE_OCEAN_FRONT' => [
				'group' => 'Locations',
				'name' => 'Ocean front',
			],
			'LOCATION_TYPE_OCEAN_VIEW' => [
				'group' => 'Locations',
				'name' => 'Ocean view',
			],
			'LOCATION_TYPE_RESORT' => [
				'group' => 'Locations',
				'name' => 'Resort',
			],
			'LOCATION_TYPE_RIVER' => [
				'group' => 'Locations',
				'name' => 'River',
			],
			'LOCATION_TYPE_RURAL' => [
				'group' => 'Locations',
				'name' => 'Rural',
			],
			'LOCATION_TYPE_SKI_IN' => [
				'group' => 'Locations',
				'name' => 'Ski in',
			],
			'LOCATION_TYPE_SKI_IN_OUT' => [
				'group' => 'Locations',
				'name' => 'Ski in out',
			],
			'LOCATION_TYPE_SKI_OUT' => [
				'group' => 'Locations',
				'name' => 'Ski out',
			],
			'LOCATION_TYPE_TOWN' => [
				'group' => 'Locations',
				'name' => 'Town',
			],
			'LOCATION_TYPE_VILLAGE' => [
				'group' => 'Locations',
				'name' => 'Village',
			],
			'LOCATION_TYPE_WATER_VIEW' => [
				'group' => 'Locations',
				'name' => 'Water view',
			],
			'LOCATION_TYPE_WATERFRONT' => [
				'group' => 'Locations',
				'name' => 'Waterfront',
			],
			// Attractions
			'ATTRACTIONS_BAY' => [
				'group' => 'Attractions',
				'name' => 'Bay',
			],
			'ATTRACTIONS_COIN_LAUNDRY' => [
				'group' => 'Attractions',
				'name' => 'Coin laundry',
			],
			'ATTRACTIONS_DUTY_FREE' => [
				'group' => 'Attractions',
				'name' => 'Duty free',
			],
			'ATTRACTIONS_HEALTH_BEAUTY_SPA' => [
				'group' => 'Attractions',
				'name' => 'Health beauty spa',
			],
			'ATTRACTIONS_MARINA' => [
				'group' => 'Attractions',
				'name' => 'Marina',
			],
			'ATTRACTIONS_MUSEUMS' => [
				'group' => 'Attractions',
				'name' => 'Museums',
			],
			'ATTRACTIONS_THEME_PARKS' => [
				'group' => 'Attractions',
				'name' => 'Theme parks',
			],
			'ATTRACTIONS_WATER_PARKS' => [
				'group' => 'Attractions',
				'name' => 'Water parks',
			],
			'ATTRACTIONS_WINERY_TOURS' => [
				'group' => 'Attractions',
				'name' => 'Winery tours',
			],
			'ATTRACTIONS_ZOO' => [
				'group' => 'Attractions',
				'name' => 'Zoo',
			],
			// Leisure
			'LEISURE_ANTIQUING' => [
				'group' => 'Leisure',
				'name' => 'Antiquing',
			],
			'LEISURE_BIRD_WATCHING' => [
				'group' => 'Leisure',
				'name' => 'Bird watching',
			],
			'LEISURE_BOATING' => [
				'group' => 'Leisure',
				'name' => 'Boating',
			],
			'LEISURE_ECO_TOURISM' => [
				'group' => 'Leisure',
				'name' => 'Eco tourism',
			],
			'LEISURE_GAMBLING' => [
				'group' => 'Leisure',
				'name' => 'Gambling',
			],
			'LEISURE_HORSEBACK_RIDING' => [
				'group' => 'Leisure',
				'name' => 'Horseback riding',
			],
			'LEISURE_OUTLET_SHOPPING' => [
				'group' => 'Leisure',
				'name' => 'Outlet shopping',
			],
			'LEISURE_PADDLE_BOATING' => [
				'group' => 'Leisure',
				'name' => 'Paddle boating',
			],
			'LEISURE_SHOPPING' => [
				'group' => 'Leisure',
				'name' => 'Shopping',
			],
			'LEISURE_SLEDDING' => [
				'group' => 'Leisure',
				'name' => 'Sledding',
			],
			'LEISURE_WATER_SPORTS' => [
				'group' => 'Leisure',
				'name' => 'Water sports',
			],
			'LEISURE_WHALE_WATCHING' => [
				'group' => 'Leisure',
				'name' => 'Whale watching',
			],
			'LEISURE_WILDLIFE_VIEWING' => [
				'group' => 'Leisure',
				'name' => 'Wildlife viewing',
			],
			// Local features
			'LOCAL_FITNESS_CENTER' => [
				'group' => 'Local features',
				'name' => 'Fitness center',
			],
			'LOCAL_LAUNDROMAT' => [
				'group' => 'Local features',
				'name' => 'Laundromat',
			],
			'LOCAL_HOSPITAL' => [
				'group' => 'Local features',
				'name' => 'Hospital',
			],
		];
	}

	/**
	 * Returns a list of property type values.
	 * 
	 * @return 	array
	 */
	public static function getPropertyTypes()
	{
		return [
			'PROPERTY_TYPE_APARTMENT' => 'Apartment',
			'PROPERTY_TYPE_BARN' => 'Barn',
			'PROPERTY_TYPE_BED_AND_BREAKFAST' => 'Bed and breakfast',
			'PROPERTY_TYPE_BOAT' => 'Boat',
			'PROPERTY_TYPE_BUILDING' => 'Building',
			'PROPERTY_TYPE_BUNGALOW' => 'Bungalow',
			'PROPERTY_TYPE_CABIN' => 'Cabin',
			'PROPERTY_TYPE_CAMPGROUND' => 'Campground',
			'PROPERTY_TYPE_CARAVAN' => 'Caravan',
			'PROPERTY_TYPE_CASTLE' => 'Castle',
			'PROPERTY_TYPE_CHACARA' => 'Chacara',
			'PROPERTY_TYPE_CHALET' => 'Chalet',
			'PROPERTY_TYPE_CHATEAU' => 'Chateau',
			'PROPERTY_TYPE_CONDO' => 'Condo',
			'PROPERTY_TYPE_CORPORATE_APARTMENT' => 'Corporate apartment',
			'PROPERTY_TYPE_COTTAGE' => 'Cottage',
			'PROPERTY_TYPE_ESTATE' => 'Estate',
			'PROPERTY_TYPE_FARMHOUSE' => 'Farmhouse',
			'PROPERTY_TYPE_GUESTHOUSE' => 'Guesthouse',
			'PROPERTY_TYPE_HOSTEL' => 'Hostel',
			'PROPERTY_TYPE_HOTEL' => 'Hotel',
			'PROPERTY_TYPE_HOUSE' => 'House',
			'PROPERTY_TYPE_HOUSE_BOAT' => 'House boat',
			'PROPERTY_TYPE_LODGE' => 'Lodge',
			'PROPERTY_TYPE_MAS' => 'Mas',
			'PROPERTY_TYPE_MILL' => 'Mill',
			'PROPERTY_TYPE_MOBILE_HOME' => 'Mobile home',
			'PROPERTY_TYPE_RECREATIONAL_VEHICLE' => 'Recreational vehicle',
			'PROPERTY_TYPE_RESORT' => 'Resort',
			'PROPERTY_TYPE_RIAD' => 'Riad',
			'PROPERTY_TYPE_STUDIO' => 'Studio',
			'PROPERTY_TYPE_TOWER' => 'Tower',
			'PROPERTY_TYPE_TOWNHOME' => 'Townhome',
			'PROPERTY_TYPE_VILLA' => 'Villa',
			'PROPERTY_TYPE_YACHT' => 'Yacht',
		];
	}

	/**
	 * Returns a list of bathroom feature values (amenities).
	 * 
	 * @return 	array
	 */
	public static function getBathroomFeatureValues()
	{
		return [
			'AMENITY_BIDET' => 'Bidet',
			'AMENITY_COMBO_TUB_SHOWER' => 'Combo tub shower',
			'AMENITY_JETTED_TUB' => 'Jetted tub',
			'AMENITY_OUTDOOR_SHOWER' => 'Outdoor shower',
			'AMENITY_SHOWER' => 'Shower',
			'AMENITY_TOILET' => 'Toilet',
			'AMENITY_TUB' => 'Tub',
		];
	}

	/**
	 * Returns a list of bathroom type values.
	 * 
	 * @return 	array
	 */
	public static function getBathroomTypeValues()
	{
		return [
			'FULL_BATH' => 'Full Bath',
			'SHOWER_INDOOR_OR_OUTDOOR' => 'Shower indoor or outdoor',
			'HALF_BATH' => 'Half Bath (no tub or shower)',
		];
	}

	/**
	 * Returns a list of bedroom feature values (amenities).
	 * 
	 * @return 	array
	 */
	public static function getBedroomFeatureValues()
	{
		return [
			'AMENITY_BUNK_BED' => 'Bunk bed',
			'AMENITY_CHILD_BED' => 'Child bed',
			'AMENITY_BABY_CRIB' => 'Baby crib',
			'AMENITY_DOUBLE' => 'Double',
			'AMENITY_KING' => 'King',
			'AMENITY_MURPHY_BED' => 'Murphy bed',
			'AMENITY_QUEEN' => 'Queen',
			'AMENITY_SLEEP_SOFA' => 'Sleep sofa',
			'AMENITY_TWIN_SINGLE' => 'Twin single',
		];
	}

	/**
	 * Returns a list of bedroom type values.
	 * 
	 * @return 	array
	 */
	public static function getBedroomTypeValues()
	{
		return [
			'BEDROOM' => 'Bedroom',
			'LIVING_SLEEPING_COMBO' => 'Living sleeping combo (studio)',
			'OTHER_SLEEPING_AREA' => 'Other sleeping area',
		];
	}

	/**
	 * Returns a list of unit feature values (unit amenities).
	 * 
	 * @return 	array
	 */
	public static function getUnitFeatureValues()
	{
		return [
			'KITCHEN_DINING_AREA' => [
				'group' => 'Kitchen and dining',
				'name' => 'Area',
			],
			'KITCHEN_DINING_COFFEEMAKER' => [
				'group' => 'Kitchen and dining',
				'name' => 'Coffeemaker',
			],
			'KITCHEN_DINING_DISHES_AND_UTENSILS_FOR_KIDS' => [
				'group' => 'Kitchen and dining',
				'name' => 'Dishes and utensils for kids',
			],
			'KITCHEN_DINING_DISHES_UTENSILS' => [
				'group' => 'Kitchen and dining',
				'name' => 'Dishes utensils',
			],
			'KITCHEN_DINING_DISHWASHER' => [
				'group' => 'Kitchen and dining',
				'name' => 'Dishwasher',
			],
			'KITCHEN_DINING_HIGHCHAIR' => [
				'group' => 'Kitchen and dining',
				'name' => 'Highchair',
			],
			'KITCHEN_DINING_ICE_MAKER' => [
				'group' => 'Kitchen and dining',
				'name' => 'Ice maker',
			],
			'KITCHEN_DINING_KITCHEN' => [
				'group' => 'Kitchen and dining',
				'name' => 'Kitchen',
			],
			'KITCHEN_DINING_KITCHEN_ISLAND' => [
				'group' => 'Kitchen and dining',
				'name' => 'Kitchen island',
			],
			'KITCHEN_DINING_MICROWAVE' => [
				'group' => 'Kitchen and dining',
				'name' => 'Microwave',
			],
			'KITCHEN_DINING_OVEN' => [
				'group' => 'Kitchen and dining',
				'name' => 'Oven',
			],
			'KITCHEN_DINING_REFRIGERATOR' => [
				'group' => 'Kitchen and dining',
				'name' => 'Refrigerator',
			],
			'KITCHEN_DINING_ROOM' => [
				'group' => 'Kitchen and dining',
				'name' => 'Room',
			],
			'KITCHEN_DINING_SPICES' => [
				'group' => 'Kitchen and dining',
				'name' => 'Spices',
			],
			'KITCHEN_DINING_STOVE' => [
				'group' => 'Kitchen and dining',
				'name' => 'Stove',
			],
			'KITCHEN_DINING_DINING_TABLE' => [
				'group' => 'Kitchen and dining',
				'name' => 'Dining table',
			],
			'KITCHEN_DINING_TOASTER' => [
				'group' => 'Kitchen and dining',
				'name' => 'Toaster',
			],
			'AMENITIES_AIR_CONDITIONING' => [
				'group' => 'Amenities',
				'name' => 'Air conditioning',
			],
			'AMENITIES_CABINET_LOCKS' => [
				'group' => 'Amenities',
				'name' => 'Cabinet locks',
			],
			'AMENITIES_COMPUTER_MONITOR' => [
				'group' => 'Amenities',
				'name' => 'Computer monitor',
			],
			'AMENITIES_DESK' => [
				'group' => 'Amenities',
				'name' => 'Desk',
			],
			'AMENITIES_DESK_CHAIR' => [
				'group' => 'Amenities',
				'name' => 'Desk chair',
			],
			'AMENITIES_DRYER' => [
				'group' => 'Amenities',
				'name' => 'Dryer',
			],
			'AMENITIES_ELEVATOR' => [
				'group' => 'Amenities',
				'name' => 'Elevator',
			],
			'AMENITIES_FIREPLACE' => [
				'group' => 'Amenities',
				'name' => 'Fireplace',
			],
			'AMENITIES_FITNESS_ROOM' => [
				'group' => 'Amenities',
				'name' => 'Fitness room',
			],
			'AMENITIES_FREE_WIFI' => [
				'group' => 'Amenities',
				'name' => 'Free wifi',
			],
			'AMENITIES_GAME_ROOM' => [
				'group' => 'Amenities',
				'name' => 'Game room',
			],
			'AMENITIES_GARAGE' => [
				'group' => 'Amenities',
				'name' => 'Garage',
			],
			'AMENITIES_HAIR_DRYER' => [
				'group' => 'Amenities',
				'name' => 'Hair dryer',
			],
			'AMENITIES_HEATING' => [
				'group' => 'Amenities',
				'name' => 'Heating',
			],
			'AMENITIES_INTERNET' => [
				'group' => 'Amenities',
				'name' => 'Internet',
			],
			'AMENITIES_IRON_BOARD' => [
				'group' => 'Amenities',
				'name' => 'Iron board',
			],
			'AMENITIES_LINENS' => [
				'group' => 'Amenities',
				'name' => 'Linens',
			],
			'AMENITIES_LIVING_ROOM' => [
				'group' => 'Amenities',
				'name' => 'Living room',
			],
			'AMENITIES_OFFICE' => [
				'group' => 'Amenities',
				'name' => 'Office',
			],
			'AMENITIES_PAID_WIFI' => [
				'group' => 'Amenities',
				'name' => 'Paid wifi',
			],
			'AMENITIES_PARKING' => [
				'group' => 'Amenities',
				'name' => 'Parking',
			],
			'AMENITIES_PRINTER' => [
				'group' => 'Amenities',
				'name' => 'Printer',
			],
			'AMENITIES_TELEPHONE' => [
				'group' => 'Amenities',
				'name' => 'Telephone',
			],
			'AMENITIES_TOWELS' => [
				'group' => 'Amenities',
				'name' => 'Towels',
			],
			'AMENITIES_WASHER' => [
				'group' => 'Amenities',
				'name' => 'Washer',
			],
			'AMENITIES_WIFI_SPEED_25' => [
				'group' => 'Amenities',
				'name' => 'Wifi speed 25',
			],
			'AMENITIES_WIFI_SPEED_50' => [
				'group' => 'Amenities',
				'name' => 'Wifi speed 50',
			],
			'AMENITIES_WIFI_SPEED_100' => [
				'group' => 'Amenities',
				'name' => 'Wifi speed 100',
			],
			'AMENITIES_WIFI_SPEED_250' => [
				'group' => 'Amenities',
				'name' => 'Wifi speed 250',
			],
			'AMENITIES_WIFI_SPEED_500' => [
				'group' => 'Amenities',
				'name' => 'Wifi speed 500',
			],
			'AMENITIES_WIRELESS_BROADBAND' => [
				'group' => 'Amenities',
				'name' => 'Wireless broadband',
			],
			'AMENITIES_WOOD_STOVE' => [
				'group' => 'Amenities',
				'name' => 'Wood stove',
			],
			'GENERAL_BABY_MONITOR' => [
				'group' => 'Amenities',
				'name' => 'General baby monitor',
			],
			'GENERAL_TRAVEL_CRIB' => [
				'group' => 'Amenities',
				'name' => 'General travel crib',
			],
			'ENTERTAINMENT_BOOKS' => [
				'group' => 'Entertainment',
				'name' => 'Books',
			],
			'ENTERTAINMENT_BOOKS_FOR_KIDS' => [
				'group' => 'Entertainment',
				'name' => 'Books for kids',
			],
			'ENTERTAINMENT_DVD' => [
				'group' => 'Entertainment',
				'name' => 'Dvd',
			],
			'ENTERTAINMENT_FOOSBALL' => [
				'group' => 'Entertainment',
				'name' => 'Foosball',
			],
			'ENTERTAINMENT_GAMES' => [
				'group' => 'Entertainment',
				'name' => 'Games',
			],
			'ENTERTAINMENT_MUSIC_LIBRARY' => [
				'group' => 'Entertainment',
				'name' => 'Music library',
			],
			'ENTERTAINMENT_PING_PONG_TABLE' => [
				'group' => 'Entertainment',
				'name' => 'Ping pong table',
			],
			'ENTERTAINMENT_POOL_TABLE' => [
				'group' => 'Entertainment',
				'name' => 'Pool table',
			],
			'ENTERTAINMENT_SATELLITE_OR_CABLE' => [
				'group' => 'Entertainment',
				'name' => 'Satellite or cable',
			],
			'ENTERTAINMENT_SMART_TV' => [
				'group' => 'Entertainment',
				'name' => 'Smart tv',
			],
			'ENTERTAINMENT_STEREO' => [
				'group' => 'Entertainment',
				'name' => 'Stereo',
			],
			'ENTERTAINMENT_TELEVISION' => [
				'group' => 'Entertainment',
				'name' => 'Television',
			],
			'ENTERTAINMENT_TOYS' => [
				'group' => 'Entertainment',
				'name' => 'Toys',
			],
			'ENTERTAINMENT_VIDEO_GAMES' => [
				'group' => 'Entertainment',
				'name' => 'Video games',
			],
			'ENTERTAINMENT_VIDEO_LIBRARY' => [
				'group' => 'Entertainment',
				'name' => 'Video library',
			],
			'OUTDOOR_BALCONY' => [
				'group' => 'Outdoor',
				'name' => 'Balcony',
			],
			'OUTDOOR_BIKE' => [
				'group' => 'Outdoor',
				'name' => 'Bike',
			],
			'OUTDOOR_BOAT' => [
				'group' => 'Outdoor',
				'name' => 'Boat',
			],
			'OUTDOOR_DECK_PATIO_UNCOVERED' => [
				'group' => 'Outdoor',
				'name' => 'Deck patio uncovered',
			],
			'OUTDOOR_FENCED_POOL' => [
				'group' => 'Outdoor',
				'name' => 'Fenced pool',
			],
			'OUTDOOR_FENCED_YARD' => [
				'group' => 'Outdoor',
				'name' => 'Fenced yard',
			],
			'OUTDOOR_GARDEN' => [
				'group' => 'Outdoor',
				'name' => 'Garden',
			],
			'OUTDOOR_GOLF' => [
				'group' => 'Outdoor',
				'name' => 'Golf',
			],
			'OUTDOOR_GRILL' => [
				'group' => 'Outdoor',
				'name' => 'Grill',
			],
			'OUTDOOR_KAYAK_CANOE' => [
				'group' => 'Outdoor',
				'name' => 'Kayak canoe',
			],
			'OUTDOOR_PLAY_AREA' => [
				'group' => 'Outdoor',
				'name' => 'Play area',
			],
			'OUTDOOR_SNOW_SPORTS_GEAR' => [
				'group' => 'Outdoor',
				'name' => 'Snow sports gear',
			],
			'OUTDOOR_TENNIS' => [
				'group' => 'Outdoor',
				'name' => 'Tennis',
			],
			'OUTDOOR_VERANDA' => [
				'group' => 'Outdoor',
				'name' => 'Veranda',
			],
			'OUTDOOR_WATER_SPORTS_GEAR' => [
				'group' => 'Outdoor',
				'name' => 'Water sports gear',
			],
			'POOL_SPA_COMMUNAL_POOL' => [
				'group' => 'Pool and spa',
				'name' => 'Communal pool',
			],
			'POOL_SPA_HEATED_POOL' => [
				'group' => 'Pool and spa',
				'name' => 'Heated pool',
			],
			'POOL_SPA_HOT_TUB' => [
				'group' => 'Pool and spa',
				'name' => 'Hot tub',
			],
			'POOL_SPA_INDOOR_POOL' => [
				'group' => 'Pool and spa',
				'name' => 'Indoor pool',
			],
			'POOL_SPA_PRIVATE_POOL' => [
				'group' => 'Pool and spa',
				'name' => 'Private pool',
			],
			'POOL_SPA_SAUNA' => [
				'group' => 'Pool and spa',
				'name' => 'Sauna',
			],
			'ACCOMMODATIONS_BREAKFAST_BOOKING_POSSIBLE' => [
				'group' => 'Accommodations',
				'name' => 'Breakfast booking possible',
			],
			'ACCOMMODATIONS_BREAKFAST_INCLUDED_IN_PRICE' => [
				'group' => 'Accommodations',
				'name' => 'Breakfast included in price',
			],
			'ACCOMMODATIONS_CHILDCARE' => [
				'group' => 'Accommodations',
				'name' => 'Childcare',
			],
			'ACCOMMODATIONS_HOUSE_CLEANING_INCLUDED' => [
				'group' => 'Accommodations',
				'name' => 'House cleaning included',
			],
			'ACCOMMODATIONS_HOUSE_CLEANING_OPTIONAL' => [
				'group' => 'Accommodations',
				'name' => 'House cleaning optional',
			],
			'ACCOMMODATIONS_MEAL_DELIVERY' => [
				'group' => 'Accommodations',
				'name' => 'Meal delivery',
			],
			'ACCOMMODATIONS_OTHER_SERVICES_CHAUFFEUR' => [
				'group' => 'Accommodations',
				'name' => 'Other services chauffeur',
			],
			'ACCOMMODATIONS_OTHER_SERVICES_CONCIERGE' => [
				'group' => 'Accommodations',
				'name' => 'Other services concierge',
			],
			'ACCOMMODATIONS_OTHER_SERVICES_PRIVATE_CHEF' => [
				'group' => 'Accommodations',
				'name' => 'Other services private chef',
			],
			'ACCOMMODATIONS_OTHER_SERVICES_MASSAGE' => [
				'group' => 'Accommodations',
				'name' => 'Other services massage',
			],
			'ACCOMMODATIONS_OTHER_SERVICES_CAR_AVAILABLE' => [
				'group' => 'Accommodations',
				'name' => 'Other services car available',
			],
			'THEMES_FAMILY' => [
				'group' => 'Themes',
				'name' => 'Family',
			],
			'THEMES_HISTORIC' => [
				'group' => 'Themes',
				'name' => 'Historic',
			],
			'THEMES_ROMANTIC' => [
				'group' => 'Themes',
				'name' => 'Romantic',
			],
			'SUITABILITY_ACCESSIBILITY_WHEELCHAIR_ACCESSIBLE' => [
				'group' => 'Suitability',
				'name' => 'Wheelchair accessible',
			],
			'SUITABILITY_ACCESSIBILITY_WHEELCHAIR_INACCESSIBLE' => [
				'group' => 'Suitability',
				'name' => 'Wheelchair inaccessible',
			],
		];
	}

	/**
	 * Returns a list of unit safety feature values (safety amenities provided by the unit).
	 * 
	 * @return 	array
	 */
	public static function getUnitSafetyFeatureValues()
	{
		return [
			'CARBON_MONOXIDE_DETECTOR' => [
				'name' => 'Carbon monoxide detector',
				'content' => 1,
				'ctype' => ['location'],
			],
			'DEADBOLT_LOCK' => [
				'name' => 'Deadbolt lock',
				'content' => 0,
			],
			'EMERGENCY_EXIT_ROUTE' => [
				'name' => 'Emergency exit route',
				'content' => 1,
				'ctype' => ['instructions'],
			],
			'FIRE_EMERGENCY_CONTACT' => [
				'name' => 'Fire emergency contact',
				'content' => 1,
				'ctype' => ['description'],
			],
			'FIRE_EXTINGUISHER' => [
				'name' => 'Fire extinguisher',
				'content' => 1,
				'ctype' => ['location'],
			],
			'FIRST_AID_KIT' => [
				'name' => 'First aid kit',
				'content' => 1,
				'ctype' => ['location'],
			],
			'MEDICAL_EMERGENCY_CONTACT' => [
				'name' => 'Medical emergency contact',
				'content' => 1,
				'ctype' => ['description'],
			],
			'OUTDOOR_LIGHTING' => [
				'name' => 'Outdoor lighting',
				'content' => 0,
			],
			'POLICE_EMERGENCY_CONTACT' => [
				'name' => 'Police emergency contact',
				'content' => 1,
				'ctype' => ['description'],
			],
			'SMOKE_DETECTOR' => [
				'name' => 'Smoke detector',
				'content' => 1,
				'ctype' => ['location'],
			],
		];
	}

	/**
	 * Returns a list of card code type values (credit cards).
	 * 
	 * @return 	array
	 */
	public static function getCardCodeTypeValues()
	{
		return [
			'AFFIRM' => 'Affirm',
			'AMEX' => 'Amex',
			'CARTA_SI' => 'Carta si',
			'CARTE_BLANCHE' => 'Carte blanche',
			'CARTE_BLEU' => 'Carte bleu',
			'DANKORT' => 'Dankort',
			'DELTA' => 'Delta',
			'DINERS' => 'Diners',
			'ENROUTE' => 'Enroute',
			'JAL' => 'Jal',
			'JCB' => 'Jcb',
			'LASER' => 'Laser',
			'MAESTRO_INTERNATIONAL' => 'Maestro international',
			'MAESTRO_UK' => 'Maestro UK',
			'MASTERCARD' => 'Mastercard',
			'SOLO' => 'Solo',
			'VISA' => 'Visa',
			'VISA_ELECTRON' => 'Visa electron',
		];
	}

	/**
	 * Returns a list of payment and invoice method type values (invoice payment types).
	 * 
	 * @return 	array
	 */
	public static function getPaymentInvoiceMethodTypeValues()
	{
		return [
			'AMEX' => 'Amex',
			'BANKTRANSFER' => 'Bank transfer',
			'BCASH' => 'Bcash',
			'BPAY' => 'Bpay',
			'CASH' => 'Cash',
			'CARTA_SI' => 'Carta si',
			'CARTE_BLANCHE' => 'Carte blanche',
			'CARTE_BLEUE' => 'Carte bleue',
			'CHECK' => 'Check',
			'CREDIT_CARD' => 'Credit card',
			'DEBIT_CARD' => 'Debit card',
			'DANKORT' => 'Dankort',
			'DINERS' => 'Diners',
			'DISCOVER' => 'Discover',
			'ELVSEPA' => 'Elvsepa',
			'ENROUTE' => 'Enroute',
			'IDEAL' => 'Ideal',
			'JAL' => 'Jal',
			'JCB' => 'Jcb',
			'MAESTRO_INTERNATIONAL' => 'Maestro international',
			'MAESTRO_UK' => 'Maestro UK',
			'MASTERCARD' => 'Mastercard',
			'MERCADOPAGO' => 'Mercadopago',
			'MOIP' => 'Moip',
			'NATIONALCREDITCARD' => 'National creditcard',
			'PAGSEGURO' => 'Pagseguro',
			'PAYPAL' => 'Paypal',
			'PAYU' => 'Payu',
			'PSE' => 'Pse',
			'SOLO' => 'Solo',
			'TRAVELERS_CHECK' => 'Travelers check',
			'VACANCES' => 'Vacances',
			'VISA' => 'Visa',
			'VISA_ELECTRON' => 'Visa electron',
		];
	}

	/**
	 * Returns a list of cancellation policy type values (cancellation policy types).
	 * 
	 * @return 	array
	 */
	public static function getCancellationPolicyTypeValues()
	{
		return [
			'STRICT' => [
				'name' => 'Strict',
				'descr' => '100% refund if reservation is cancelled at least 60 days before the arrival date',
			],
			'FIRM' => [
				'name' => 'Firm',
				'descr' => '100% refund if reservation is cancelled at least 60 days before the arrival date, 50% refund if reservation is cancelled at least 30 days before the arrival date',
			],
			'MODERATE' => [
				'name' => 'Moderate',
				'descr' => '100% refund if reservation is cancelled at least 30 days before the arrival date, 50% refund if reservation is cancelled at least 14 days before the arrival date',
			],
			'RELAXED' => [
				'name' => 'Relaxed',
				'descr' => '100% refund if reservation is cancelled at least 14 days before the arrival date, 50% refund if reservation is cancelled at least 7 days before the arrival date',
			],
			'NO_REFUND' => [
				'name' => 'No Refund',
				'descr' => 'No refund if reservation is cancelled',
			],
			'CUSTOM' => [
				'name' => 'Custom',
				'descr' => 'Specify the custom cancellation terms',
			],
		];
	}

	/**
	 * Returns a description of the listing's default cancellation policy.
	 * 
	 * @param 	string 	$listing_id 	the Vrbo listing ID.
	 * 
	 * @return 	string
	 */
	public static function describeCancellationPolicy($listing_id)
	{
		$def_canc_policy = 'No refund if reservation is cancelled';

		$account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
		$listing_record = static::getRecords($account_key, $listing_id);

		if (!$listing_record) {
			return $def_canc_policy;
		}

		// use the first row
		$listing = new JObject(json_decode($listing_record[0]['setting']));

		// get lodging object, if available
		$lodging_arr = (array)$listing->get('lodging', []);
		$lodging = new JObject($lodging_arr);

		// cancellation policy data
		$cancellationPolicy = (array)$lodging->get('cancellationPolicy', []);

		// cancellation policy code
		$canc_policy_policy = !empty($cancellationPolicy['policy']) ? $cancellationPolicy['policy'] : '';

		if (empty($canc_policy_policy)) {
			return $def_canc_policy;
		}

		// all cancellation policy codes
		$canc_policy_codes = static::getCancellationPolicyTypeValues();

		if ($canc_policy_policy != 'CUSTOM' && isset($canc_policy_codes[$canc_policy_policy])) {
			// a description for this cancellation policy is available in the lodging configuration settings
			return $canc_policy_codes[$canc_policy_policy]['descr'];
		}

		if ($canc_policy_policy == 'CUSTOM') {
			// custom cancellation fee and cancellation terms
			$custom_canc_terms = [];
			$canc_fee = !empty($cancellationPolicy['cancellationFee']) ? (float)$cancellationPolicy['cancellationFee'] : 0;
			$canc_policy_periods = !empty($cancellationPolicy['periods']) ? (array)$cancellationPolicy['periods'] : [];

			if ($canc_fee > 0 && !$canc_policy_periods) {
				// just the cancellation fee
				return 'Cancellation fee applied: ' . $listing->get('currency', '') . ' ' . $canc_fee;
			}

			if ($canc_policy_periods) {
				// build the values for the custom cancellation policy periods
				for ($cpol_p = 0; $cpol_p < 3; $cpol_p++) {
					if (!isset($canc_policy_periods[$cpol_p])) {
						continue;
					}
					if (!is_array($canc_policy_periods[$cpol_p])) {
						$canc_policy_periods[$cpol_p] = (array)$canc_policy_periods[$cpol_p];
					}
					$cur_nights_before = isset($canc_policy_periods[$cpol_p]) && isset($canc_policy_periods[$cpol_p]['nightsbefore']) ? (int)$canc_policy_periods[$cpol_p]['nightsbefore'] : null;
					$cur_refund_pcent  = isset($canc_policy_periods[$cpol_p]) && isset($canc_policy_periods[$cpol_p]['refundpcent']) ? (float)$canc_policy_periods[$cpol_p]['refundpcent'] : null;
					if (is_null($cur_nights_before) || is_null($cur_refund_pcent)) {
						continue;
					}
					// push custom cancellation term
					$custom_canc_terms[] = [
						'nights' => $cur_nights_before,
						'pcent'  => $cur_refund_pcent,
					];
				}
			}

			if ($canc_fee > 0) {
				// append the cancellation fee string
				$custom_canc_terms[] = 'Cancellation fee applied: ' . $listing->get('currency', '') . ' ' . $canc_fee;
			}

			if ($custom_canc_terms) {
				$canc_terms_strs = [];
				foreach ($custom_canc_terms as $custom_canc_term) {
					if (is_string($custom_canc_term)) {
						// must be the cancellation fee
						$canc_terms_strs[] = $custom_canc_term;
						continue;
					}
					// append the string with the custom cancellation deadline
					$canc_terms_strs[] = "{$custom_canc_term['pcent']}% refund if reservation is cancelled at least {$custom_canc_term['nights']} days before the arrival date";
				}

				if ($canc_terms_strs) {
					return implode(', ', $canc_terms_strs);
				}
			}
		}

		return $def_canc_policy;
	}

	/**
	 * Applies the listing content validation metrics to tell if a listing has got
	 * sufficient information to start being listed.
	 * 
	 * @param 	mixed 	$payload 	string, array or object with the listing payload data.
	 * 
	 * @return 	array 				list of result and eventual error strings.
	 */
	public static function contentValidationPass($payload)
	{
		if (is_string($payload)) {
			$payload = json_decode($payload);
		}

		if (!is_object($payload) && !is_array($payload)) {
			return [false, 'Invalid listing content payload'];
		}

		// wrap listing object into a JObject object
		$listing = new JObject($payload);

		// list of properties that should not be empty
		$non_empty_props = [
			'Id' => 'id',
			'Name' => 'name',
			'Description' => 'description',
			'Property Name' => 'propertyName',
			'Address Line 1' => 'addressLine1',
			'City' => 'city',
			'Country' => 'country',
			'Postal Code' => 'postalCode',
			'Latitude' => 'latitude',
			'Longitude' => 'longitude',
			'Images' => 'images',
		];

		foreach ($non_empty_props as $prop_read_name => $prop) {
			if (!$listing->get($prop)) {
				return [false, "Mandatory property &quot;{$prop_read_name}&quot; is empty."];
			}
		}

		// minimum content check
		if (strlen((string)$listing->get('name', '')) < 20) {
			return [false, "Listing name (headline) requires at least 20 characters to pass the minimum content check."];
		}
		if (strlen((string)$listing->get('description', '')) < 400) {
			return [false, "Listing description requires at least 400 characters to pass the minimum content check."];
		}

		// wrap unit object into a JObject object
		$unit = new JObject($listing->get('unit'));

		// list of unit properties that should not be empty
		$size_lbl = JText::_('VCM_BED_SIZE');
		$size_unit_lbl = JText::_('VCM_AIRBNB_STDFEE_UNIT_TYPE');
		$non_empty_props = [
			'Bathrooms' => 'bathrooms',
			'Bedrooms' => 'bedrooms',
			'Unit Amenities' => 'featureValues',
			'Property Type' => 'propertyType',
			$size_lbl => 'area',
			$size_unit_lbl => 'areaUnit',
		];

		foreach ($non_empty_props as $prop_read_name => $prop) {
			if (!$unit->get($prop)) {
				return [false, "Mandatory property &quot;{$prop_read_name}&quot; is empty in unit content."];
			}
		}

		// wrap lodging object into a JObject object
		$lodging = new JObject($listing->get('lodging'));

		// list of lodging properties that should not be empty
		$canc_policy_lbl = JText::_('VCM_TA_CANCELLATION_POLICY');
		$non_empty_props = [
			'Accepted Payments' => 'acceptedPaymentForms',
			'Booking Policy' => 'bookingPolicy',
			$canc_policy_lbl => 'cancellationPolicy',
			'Check-in time' => 'checkInTime',
			'Check-out time' => 'checkOutTime',
			'Max Adults' => 'maxAdults',
			'Max Guests' => 'maxGuests',
		];

		foreach ($non_empty_props as $prop_read_name => $prop) {
			if (!$lodging->get($prop)) {
				return [false, "Mandatory property &quot;{$prop_read_name}&quot; is empty in policy (lodging) content."];
			}
		}

		// make sure we have a cancellation policy set
		$cancellationPolicy = (array)$lodging->get('cancellationPolicy', []);
		if (!$cancellationPolicy || empty($cancellationPolicy['policy'])) {
			return [false, "Mandatory property &quot;{$canc_policy_lbl}&quot; is empty in policy (lodging) content."];
		}

		// content validation will pass
		return [true, ''];
	}

	/**
	 * Properly merges previous array keys to current values.
	 * 
	 * @param 	array 	$previous 	the previous array that may contain missing properties.
	 * @param 	array 	$current 	the current array that should get any missing key set.
	 * 
	 * @return 	array 				the current array merged with what was missing from the previous array.
	 */
	public static function mergeAssocProperties(array $previous, array $current)
	{
		foreach ($previous as $prop => $val) {
			if (!isset($current[$prop])) {
				$current[$prop] = $val;
				continue;
			}

			if ($current[$prop] === $val) {
				continue;
			}

			if (is_array($val) && $val && array_keys($val) !== range(0, count($val) - 1)) {
				// recursion on associative arrays (non-numeric) to merge nested properties
				$current[$prop] = is_array($current[$prop]) ? $current[$prop] : [];
				$current[$prop] = static::mergeAssocProperties($val, $current[$prop]);
			}
		}

		return $current;
	}

	/**
	 * Tells whether the given listing ID is mapped to a website room.
	 * 
	 * @param 	string 	$listing_id 	the OTA listing ID.
	 * 
	 * @return 	mixed 	associative array on success or null.
	 */
	public static function getListingMapping($listing_id)
	{
		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomota`=" . $dbo->quote($listing_id) . " AND `idchannel`=" . (int)VikChannelManagerConfig::VRBOAPI;
		$dbo->setQuery($q, 0, 1);

		return $dbo->loadAssoc();
	}

	/**
	 * Gets all listing records or the one requested.
	 * 
	 * @param 	string 	$account_key 	the account identifier number.
	 * @param 	string 	$listing_id 	the optional listing ID to fetch one record.
	 * 
	 * @return 	array 					list of listing records fetched.
	 */
	public static function getRecords($account_key, $listing_id = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_otarooms_data'))
			->where($dbo->qn('idchannel') . ' = ' . $dbo->q(VikChannelManagerConfig::VRBOAPI))
			->where($dbo->qn('account_key') . ' = ' . $dbo->q($account_key))
			->where($dbo->qn('param') . ' = ' . $dbo->q('listing_content'));

		if ($listing_id) {
			$q->where($dbo->qn('idroomota') . ' = ' . $dbo->q($listing_id));
		}

		$dbo->setQuery($q);

		return $dbo->loadAssocList();
	}

	/**
	 * Attempts to return the settings object for a given listing ID.
	 * 
	 * @param 	int 	$listing_id 	the listing ID to fetch.
	 * 
	 * @return 	JObject
	 * 
	 * @since 	1.8.20
	 */
	public static function getListingObject($listing_id)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('setting'))
			->from($dbo->qn('#__vikchannelmanager_otarooms_data'))
			->where($dbo->qn('idchannel') . ' = ' . $dbo->q(VikChannelManagerConfig::VRBOAPI))
			->where($dbo->qn('idroomota') . ' = ' . $dbo->q($listing_id))
			->where($dbo->qn('param') . ' = ' . $dbo->q('listing_content'));

		$dbo->setQuery($q, 0, 1);
		$setting = $dbo->loadResult();

		$listing_data = [];
		if ($setting) {
			$listing_data = (array)json_decode($setting, true);
		}

		return new JObject($listing_data);
	}

	/**
	 * Method to complete the rooms mapping procedure for Vrbo.
	 * The eligible listings will be transmitted to E4jConnect.
	 * During the very first submission, the channel credentials
	 * are stored for the current property manager account.
	 * 
	 * @param 	array 	$channel 	the active module settings.
	 * @param 	array 	$room_ids 	the involved room-type ids.
	 * 
	 * @return 	mixed 	true on success, string with error otherwise.
	 */
	public static function transmitEligibleListings(array $channel, array $room_ids)
	{
		if (!$room_ids) {
			return 'No listings involved in the mapping';
		}

		// make sure to decode channel params
		if (!empty($channel['params']) && is_string($channel['params'])) {
			$channel['params'] = json_decode($channel['params'], true);
			$channel['params'] = is_array($channel['params']) ? $channel['params'] : [];
		}

		// grab the account key
		$missing_creds = true;
		$account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');
		if (!empty($channel['params']) && !empty($channel['params']['hotelid'])) {
			$account_key = $channel['params']['hotelid'];
			$missing_creds = false;
		}

		if (!$account_key) {
			return 'No valid account information found';
		}

		// build listings
		$listings = [];
		foreach ($room_ids as $rid) {
			$records = static::getRecords($account_key, $rid);
			if (!$records) {
				// abort if just one listing was not found
				return 'Could not fetch the information for the listing ID ' . $rid;
			}

			$listing_data = new JObject(json_decode($records[0]['setting']));
			$listing_id = $listing_data->get('id');
			$listing_name = $listing_data->get('name');
			if (!$listing_id || !$listing_name) {
				// abort if just one listing is invalid
				return 'Could not validate the information for the listing ID ' . $rid;
			}

			// push listing basic information
			$listings[] = [
				'id' 	 => $listing_id,
				'name' 	 => $listing_name,
				'active' => (int)$listing_data->get('active'),
			];
		}

		// perform the request
		$rq = [
			'action'   => 'listings_mapping',
			'base' 	   => JUri::root(),
			'apikey'   => VikChannelManager::getApiKey(),
			'lang' 	   => JFactory::getLanguage()->getTag(),
			'pm_id'    => $account_key,
			'listings' => $listings,
		];
		$transp = new E4jConnectRequest('https://e4jconnect.com/vrbo_api/xml/getter');
		$transp->setPostFields(json_encode($rq))->setHttpHeader(['Content-Type: application/json; charset=utf-8']);
		$resp = $transp->exec();

		if (!$resp) {
			return 'Could not transmit the Vrbo eligible listings, please try again later.';
		}

		$json_resp = json_decode($resp);
		if (!is_object($json_resp) || !empty($json_resp->error)) {
			$def_error = 'An error occurred while processing the listings information. Please try again';
			return (is_object($json_resp) && !empty($json_resp->error) ? $json_resp->error : $def_error) . (!is_object($json_resp) && is_string($resp) ? "\n{$resp}" : '');
		}

		// response was successful, check if it was the first listings submission
		if ($missing_creds && !empty($channel['id'])) {
			// set account ID
			$channel['params'] = !empty($channel['params']) ? $channel['params'] : [];
			$channel['params']['hotelid'] = $account_key;

			// update channel record with account ID
			$dbo = JFactory::getDbo();
			$dbo->setQuery("UPDATE `#__vikchannelmanager_channel` SET `params`=" . $dbo->q(json_encode($channel['params'])) . " WHERE `id`=" . (int)$channel['id']);
			$dbo->execute();
		}

		return true;
	}

	/**
	 * Transmits basic listing information to the E4jConnect servers after updating one
	 * existing listing already mapped to a website room. This is useful to immediately
	 * update the information for Vrbo about the active status and last updated date.
	 * In case some compliance regulatory registration records are available, these are
	 * transmitted to E4jConnect as well for updating the registration information.
	 * 
	 * @param 	string 	$listing_id 	 the Vrbo listing ID just updated.
	 * @param 	array 	$listing_fields  the associative array of information.
	 * 
	 * @return 	mixed 					 true on success, error string, or compliance status array.
	 */
	public static function notifyDataUpdated($listing_id, array $listing_fields)
	{
		if (!$listing_id || !$listing_fields) {
			return 'Missing required data';
		}

		// wrap fields into a JObject object
		$listing_data = new JObject($listing_fields);

		// perform the request
		$rq = [
			'action' 	 => 'listing_updated',
			'base' 		 => JUri::root(),
			'apikey' 	 => VikChannelManager::getApiKey(),
			'lang' 		 => JFactory::getLanguage()->getTag(),
			'listing_id' => $listing_id,
			'active' 	 => ($listing_data->get('active') ? 1 : 0),
			'name' 		 => $listing_data->get('name'),
		];

		// check if the registration details were provided
		if (isset($listing_fields['registration_details']) && is_array($listing_fields['registration_details']) && !empty($listing_fields['registration_details']['category'])) {
			// let E4jConnect update the registration details as well
			$rq['registration_details'] = $listing_fields['registration_details'];
		}

		$transp = new E4jConnectRequest('https://e4jconnect.com/vrbo_api/xml/getter');
		$transp->setPostFields(json_encode($rq))->setHttpHeader(['Content-Type: application/json; charset=utf-8']);
		$resp = $transp->exec();

		if (!$resp) {
			return 'Could not update the listing information for Vrbo, please try again later.';
		}

		$json_resp = json_decode($resp);
		if (!is_object($json_resp) || !empty($json_resp->error)) {
			$def_error = 'An error occurred while updating the listing information. Please try again';
			return (is_object($json_resp) && !empty($json_resp->error) ? $json_resp->error : $def_error) . (!is_object($json_resp) && is_string($resp) ? "\n{$resp}" : '');
		}

		// check if the compliance status was returned
		if (isset($json_resp->compliance) && $json_resp->compliance) {
			// return the compliance status information for this listing (as an array to differentiate the type of response)
			return [$json_resp->compliance];
		}

		return true;
	}

	/**
	 * Obtains the listing compliance regulatory information through E4jConnect.
	 * Such details are part of the Lodging Supply GraphQL API, and they contain
	 * country related regulatory information for a listing in order to go live.
	 * 
	 * @param 	string 	$listing_id 	the Vrbo listing ID.
	 * 
	 * @return 	mixed 					error string on failure, decoded object otherwise.
	 */
	public static function getListingComplianceRegulatoryInfo($listing_id)
	{
		// perform the request
		$rq = [
			'action' 	 => 'get_compliance_lodging_supply',
			'base' 		 => JUri::root(),
			'apikey' 	 => VikChannelManager::getApiKey(),
			'lang' 		 => JFactory::getLanguage()->getTag(),
			'listing_id' => $listing_id,
		];

		$transp = new E4jConnectRequest('https://e4jconnect.com/vrbo_api/xml/getter');
		$transp->slaveEnabled = true;
		$transp->setPostFields(json_encode($rq))->setHttpHeader(['Content-Type: application/json; charset=utf-8']);
		$resp = $transp->exec();

		if (!$resp) {
			return 'Could not find the listing information for Vrbo, please try again later.';
		}

		$json_resp = json_decode($resp);
		if (!is_object($json_resp) || !empty($json_resp->error)) {
			$def_error = 'An error occurred while fetching the listing regulatory information. Please try again';
			return (is_object($json_resp) && !empty($json_resp->error) ? $json_resp->error : $def_error) . (!is_object($json_resp) && is_string($resp) ? "\n{$resp}" : '');
		}

		return $json_resp;
	}

	/**
	 * Translates the list of room records into all the available languages.
	 * 
	 * @param 	VikBookingTranslator 	$tn_obj 		the VBO translation object.
	 * @param 	array 					$room_records 	the list of room records.
	 * 
	 * @return 	array 					associative list of translated records and locales (if any).
	 */
	public static function getRoomTranslations($tn_obj, array $room_records = [])
	{
		if (!is_object($tn_obj) || !$room_records) {
			return [];
		}

		$all_langs = $tn_obj->getLanguagesList();
		if (count($all_langs) < 2) {
			return [];
		}

		$tn_rooms  = [];
		$lang_code = $tn_obj->getDefaultLang();
		$lang_code = substr(strtolower($lang_code), 0, 2);

		// parse all the available languages to check for translations
		foreach ($all_langs as $lang_key => $lang_data) {
			$use_lang_code = substr(strtolower($lang_key), 0, 2);
			if ($use_lang_code == $lang_code || !isset(static::$supported_locales[$use_lang_code])) {
				// skip if default lang or if locale not supported
				continue;
			}

			// translate original records
			$tmp_rooms = $room_records;
			$tn_obj->translateContents($tmp_rooms, '#__vikbooking_rooms', [], [], $lang_key);
			if ($tmp_rooms == $room_records) {
				// nothing was actually translated
				continue;
			}

			// start pool for current locale
			if (!isset($tn_rooms[$use_lang_code])) {
				$tn_rooms[$use_lang_code] = [];
			}

			// push translations for this locale
			foreach ($tmp_rooms as $tn_room) {
				$tn_rooms[$use_lang_code][] = $tn_room;
			}
		}

		return $tn_rooms;
	}

	/**
	 * Returns the SSO link for Vrbo with the PM's prefilled information.
	 * 
	 * @param 	string 	$advertiser_id 	the Vrbo Advertiser (Account) ID.
	 * 
	 * @return 	string 	the SSO link for Vrbo to connect with our CM.
	 * 
	 * @since 	1.8.16
	 */
	public static function getSelfServiceOnboardingLink($advertiser_id)
	{
		$query_data = [
			'softwareId' 		   => 'E4JCONNECT',
			'advertiserAssignedId' => $advertiser_id,
		];

		return 'https://www.vrbo.com/p/onboard?prefill=' . base64_encode(http_build_query($query_data));
	}

	/**
	 * Compares two payload settings for a listing with accuracy to detect any changes.
	 * 
	 * @param 	mixed 	$previous 	the previous settings payload, either a JSON string or an object.
	 * @param 	mixed 	$current 	the current settings payload, either a JSON string or an object.
	 * 
	 * @return 	int 	0 if the payloads are identical, -1 if previous less than current, or 1.
	 * 
	 * @since 	1.8.16
	 */
	public static function comparePayloadSettings($previous, $current)
	{
		if (is_string($previous)) {
			$previous = json_decode($previous);
		}

		if (is_string($current)) {
			$current = json_decode($current);
		}

		if (!is_object($previous) && !is_object($current)) {
			return 0;
		}

		if (!is_object($previous)) {
			return -1;
		}

		if (!is_object($current)) {
			return 1;
		}

		$prev_props = get_object_vars($previous);
		$curr_props = get_object_vars($current);
		if (!$prev_props) {
			return !$curr_props ? 0 : -1;
		}

		if (array_diff(array_keys($prev_props), array_keys($curr_props))) {
			return 1;
		}

		if (array_diff(array_keys($curr_props), array_keys($prev_props))) {
			return -1;
		}

		foreach ($prev_props as $prop_name => $prop_vals) {
			if (!isset($current->{$prop_name}) || $current->{$prop_name} != $prop_vals) {
				return 1;
			}
		}

		foreach ($curr_props as $prop_name => $prop_vals) {
			if (!isset($previous->{$prop_name}) || $previous->{$prop_name} != $prop_vals) {
				return -1;
			}
		}

		return 0;
	}
}
