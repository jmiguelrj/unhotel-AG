<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Expedia Product API helper.
 * 
 * @since 	1.8.11
 */
final class VCMExpediaProduct
{
	/**
	 * Returns a list of predefined room-type names.
	 * 
	 * @return 	array
	 */
	public static function getPredefinedRoomNamesEnum()
	{
		return [
			'Apartment, 1 Bedroom',
			'Apartment, 2 Bedrooms',
			'Classic Double Room',
			'Classic Room',
			'Classic Twin Room',
			'Deluxe Double Room',
			'Deluxe Room',
			'Deluxe Twin Room',
			'Double Room',
			'Double Room Single Use',
			'Executive Room',
			'Executive Suite',
			'Executive Twin Room',
			'Junior Suite',
			'Quadruple Room',
			'Single Room',
			'Standard Double Room',
			'Standard Room',
			'Standard Single Room',
			'Standard Twin Room',
			'Studio',
			'Suite',
			'Suite, 1 Bedroom',
			'Superior Double Room',
			'Superior Room',
			'Superior Twin Room',
			'Triple Room',
			'Twin Room',
		];
	}

	/**
	 * Returns an associative array of bed type and related bed sizes supported.
	 * 
	 * @param 	string 	$filter 	if "types", all bed types will be returned,
	 * 								if "sizes", all bed sizes will be returned,
	 * 								if "surcharges", only those eligible will be returned,
	 * 								otherwise get the whole associative list.
	 * 
	 * @return 	array
	 */
	public static function getBedTypeSizes($filter = '')
	{
		$bed_type_sizes = [
			'Bunk Bed' => [
				'Full',
				'King',
				'Queen',
				'Twin',
				'TwinXL',
			],
			'Crib' => [
				'Crib',
			],
			'Day Bed' => [
				'Full',
				'King',
				'Queen',
				'Twin',
				'TwinXL',
			],
			'Full Bed' => [
				'Full',
			],
			'Futon' => [
				'Full',
				'King',
				'Queen',
				'Twin',
				'TwinXL',
			],
			'King Bed' => [
				'King',
			],
			'Murphy Bed' => [
				'Full',
				'King',
				'Queen',
				'Twin',
				'TwinXL',
			],
			'Queen Bed' => [
				'Queen',
			],
			'Rollaway Bed' => [
				'Full',
				'King',
				'Queen',
				'Twin',
				'TwinXL',
			],
			'Sofa Bed' => [
				'Full',
				'King',
				'Queen',
				'Twin',
				'TwinXL',
			],
			'Trundle Bed' => [
				'Full',
				'King',
				'Queen',
				'Twin',
				'TwinXL',
			],
			'Twin Bed' => [
				'Twin',
			],
			'Twin XL Bed' => [
				'TwinXL',
			],
			'Water Bed' => [
				'Full',
				'King',
				'Queen',
				'Twin',
				'TwinXL',
			],
		];

		if (!strcasecmp($filter, 'types')) {
			// return a list of bed types enum values
			return array_keys($bed_type_sizes);
		}

		if (!strcasecmp($filter, 'sizes')) {
			// return a list of bed sizes enum values
			$bed_sizes = [];
			foreach ($bed_type_sizes as $type_sizes) {
				$bed_sizes = array_merge($bed_sizes, $type_sizes);
			}
			return array_unique($bed_sizes);
		}

		if (!strcasecmp($filter, 'surcharges')) {
			// return only the bed types that support surcharges as extra bed
			return [
				'Crib' 		   => $bed_type_sizes['Crib'],
				'Rollaway Bed' => $bed_type_sizes['Rollaway Bed'],
			];
		}

		// return the whole associative list
		return $bed_type_sizes;
	}

	/**
	 * Returns an associative array of room views with related places supported.
	 * 
	 * @param 	string 	$filter 	if "room_name", only the views that can be used to
	 * 								compose the room name attribute will be returned.
	 * 								if "room_level", only the views that can be used to
	 * 								specify the room-level content will be returned.
	 * 
	 * @return 	array
	 */
	public static function getRoomViews($filter = '')
	{
		$room_views = [
			'Bay View' => [
				'room_name',
				'room_level',
			],
			'Beach View' => [
				'room_name',
				'room_level',
			],
			'Canal View' => [
				'room_name',
				'room_level',
			],
			'City View' => [
				'room_name',
				'room_level',
			],
			'Courtyard View' => [
				'room_name',
				'room_level',
			],
			'Garden View' => [
				'room_name',
				'room_level',
			],
			'Golf View' => [
				'room_name',
				'room_level',
			],
			'Harbor View' => [
				'room_name',
				'room_level',
			],
			'Hill View' => [
				'room_name',
				'room_level',
			],
			'Lagoon View' => [
				'room_name',
				'room_level',
			],
			'Lake View' => [
				'room_name',
				'room_level',
			],
			'Marina View' => [
				'room_name',
				'room_level',
			],
			'Mountain View' => [
				'room_name',
				'room_level',
			],
			'Multiple View' => [
				'room_name',
			],
			'No View' => [
				'room_name',
			],
			'Ocean View' => [
				'room_name',
				'room_level',
			],
			'Park View' => [
				'room_name',
				'room_level',
			],
			'Partial Lake View' => [
				'room_name',
				'room_level',
			],
			'Partial Ocean View' => [
				'room_name',
				'room_level',
			],
			'Partial Sea View' => [
				'room_name',
				'room_level',
			],
			'Partial View' => [
				'room_name',
			],
			'Pool View' => [
				'room_name',
				'room_level',
			],
			'Resort View' => [
				'room_name',
				'room_level',
			],
			'River View' => [
				'room_name',
				'room_level',
			],
			'Sea View' => [
				'room_name',
				'room_level',
			],
			'Valley View' => [
				'room_name',
				'room_level',
			],
			'View' => [
				'room_name',
			],
			'Vineyard View' => [
				'room_name',
				'room_level',
			],
			'Water View' => [
				'room_level',
			],
		];

		if (!strcasecmp($filter, 'room_name')) {
			// return a list of room views to compose the room name attribute
			return array_filter($room_views, function($views) {
				return is_array($views) && in_array('room_name', $views);
			});
		}

		if (!strcasecmp($filter, 'room_level')) {
			// return a list of room views for the room-level content
			return array_filter($room_views, function($views) {
				return is_array($views) && in_array('room_level', $views);
			});
		}

		// return the whole associative list
		return $room_views;
	}

	/**
	 * Returns an associative list of predefined regulatory categories.
	 * 
	 * @return 	array
	 */
	public static function getRegulatoryCategories()
	{
		return [
			'HOTEL' => 'Hotel',
			'BED_AND_BREAKFAST' => 'Bed and breakfast',
			'HOTEL_OR_BNB' => 'Hotel_or_bnb',
			'PRIMARY_HOME' => 'Primary home',
			'PRIMARY_HOME_WITH_EXCEPTION' => 'Primary home with exception',
			'SECONDARY_HOME' => 'Secondary home',
			'VACATION_RENTAL' => 'Vacation rental',
			'LONG_TERM_ONLY' => 'Long term only',
			'SHORT_TERM_RENTAL' => 'Short term rental',
			'MINPAKU' => 'Minpaku',
			'SIMPLE_LODGING' => 'Simple lodging',
			'EVENT' => 'Event',
			'SPECIAL' => 'Special',
			'NO_LICENSE' => 'No license',
			'HOTEL_RYOKAN' => 'Hotel ryokan',
			'RYOKAN' => 'Ryokan',
			'PRIMARY_OR_SECONDARY' => 'Primary or secondary',
		];
	}

	/**
	 * Returns an associative list of predefined registration number types.
	 * 
	 * @return 	array
	 */
	public static function getRegistrationNumberTypes()
	{
		return [
			'LICENSE_NUMBER' => 'License number',
			'LICENSE_ID' => 'License id',
			'PARTIAL_TAX_ID' => 'Partial tax id',
			'REGISTRATION_NUMBER' => 'Registration number',
			'BUSINESS_LICENSE_NUMBER' => 'Business license number',
			'OPERATOR_LICENSE_ID' => 'Operator license id',
			'BUSINESS_TAX_ID' => 'Business tax id',
			'RESORT_TAX_ID' => 'Resort tax id',
			'SHORT_TERM_RENTAL_LICENSE' => 'Short term rental license',
			'PERMIT_NUMBER' => 'Permit number',
			'PLANNING_NUMBER' => 'Planning number',
			'HOTEL_LICENSE' => 'Hotel license',
			'TOURIST_DEVELOPMENT_TAX_ACCOUNT_NUMBER' => 'Tourist development tax account number',
		];
	}

	/**
	 * Returns an associative list of surcharge types.
	 * 
	 * @return 	array
	 */
	public static function getSurchargeTypes()
	{
		return [
			'Free' 		=> JText::_('VCMBCARCAMENITYVAL2'),
			'Per Day' 	=> JText::_('VCMRARRATEPERDAY'),
			'Per Night' => JText::_('VCMRARADDLOSCOSTPNIGHT'),
			'Per Week'  => JText::_('VCMCONFREPORTSWEEK'),
			'Per Stay'  => JText::_('VCMBCAHCHGFRQ1'),
		];
	}

	/**
	 * Returns a list of type of rooms to compose the room name attributes.
	 * 
	 * @return 	array
	 */
	public static function getTypeOfRooms()
	{
		return [
			'Apartment',
			'Bungalow',
			'Cabin',
			'Chalet',
			'Condo',
			'Cottage',
			'Double or Twin Room',
			'Double Room',
			'Double Room Single Use',
			'Duplex',
			'House',
			'Loft',
			'Mobile Home',
			'Penthouse',
			'Quadruple Room',
			'Room',
			'Shared Dormitory',
			'Single Room',
			'Studio',
			'Studio Suite',
			'Suite',
			'Tent',
			'Townhome',
			'Tree House',
			'Triple Room',
			'Twin Room',
			'Villa',
		];
	}

	/**
	 * Returns a list of room classes to compose the room name attributes.
	 * 
	 * @return 	array
	 */
	public static function getRoomClasses()
	{
		return [
			'Basic',
			'Business',
			'City',
			'Classic',
			'Club',
			'Comfort',
			'Deluxe',
			'Design',
			'Economy',
			'Elite',
			'Exclusive',
			'Executive',
			'Family',
			'Gallery',
			'Grand',
			'Honeymoon',
			'Junior',
			'Luxury',
			'Panoramic',
			'Premier',
			'Premium',
			'Presidential',
			'Romantic',
			'Senior',
			'Signature',
			'Standard',
			'Superior',
			'Traditional',
		];
	}

	/**
	 * Returns a list of bedroom details to compose the room name attributes.
	 * 
	 * @return 	array
	 */
	public static function getBedroomDetails()
	{
		return [
			'1 Bedroom',
			'2 Bedrooms',
			'3 Bedrooms',
			'4 Bedrooms',
			'5 Bedrooms',
			'6 Bedrooms',
			'Multiple Bedrooms',
			'Men only',
			'Mixed Dorm',
			'Women only',
		];
	}

	/**
	 * Returns a list of featured amenities to compose the room name attributes.
	 * 
	 * @return 	array
	 */
	public static function getFeaturedAmenities()
	{
		return [
			'2 Bathrooms',
			'Allergy Friendly',
			'Balcony',
			'Bathtub',
			'Business Lounge Access',
			'Concierge Service',
			'Connecting Rooms',
			'Ensuite',
			'Fireplace',
			'Hot Tub',
			'Jetted Tub',
			'Kitchen',
			'Kitchenette',
			'Lanai',
			'Microwave',
			'No Windows',
			'Patio',
			'Pool Access',
			'Private Bathroom',
			'Private Pool',
			'Refrigerator',
			'Refrigerator & Microwave',
			'Sauna',
			'Shared Bathroom',
			'Terrace',
		];
	}

	/**
	 * Returns a list of area descriptions to compose the room name attributes.
	 * 
	 * @return 	array
	 */
	public static function getAreaDescriptions()
	{
		return [
			'Annex Building',
			'Beachfront',
			'Beachside',
			'Corner',
			'Courtyard Area',
			'Executive Level',
			'Garden Area',
			'Ground Floor',
			'Lakeside',
			'Mezzanine',
			'Mountainside',
			'Oceanfront',
			'Overwater',
			'Poolside',
			'Sea Facing',
			'Slope side',
			'Tower',
		];
	}

	/**
	 * Returns an associative list of amenity codes, detail codes and values.
	 * This is to transmit the array of amenity codes for a room-type.
	 * 
	 * @return 	array
	 */
	public static function getAmenityCodesData()
	{
		return [
			// Internet access
			'ROOM_WIRED_INTERNET' => [
				'group' => JText::_('VCMBCAHAMENTYPE29'),
				'name' => 'Room wired internet',
				'detailCodes' => [
					'FREE' => JText::_('VCMBCARCAMENITYVAL2'),
					'SURCHARGE' => JText::_('VCMBCARCAMENITYVAL3'),
				],
				'valueType' => null,
			],
			'ROOM_NO_WIRED_INTERNET' => [
				'group' => JText::_('VCMBCAHAMENTYPE29'),
				'name' => 'Room no wired internet',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WIFI_INTERNET' => [
				'group' => JText::_('VCMBCAHAMENTYPE29'),
				'name' => 'Room wifi internet',
				'detailCodes' => [
					'FREE' => JText::_('VCMBCARCAMENITYVAL2'),
					'SURCHARGE' => JText::_('VCMBCARCAMENITYVAL3'),
				],
				'valueType' => null,
			],
			'ROOM_NO_WIFI_INTERNET' => [
				'group' => JText::_('VCMBCAHAMENTYPE29'),
				'name' => 'Room no wifi internet',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DIALUP_INTERNET_FREE' => [
				'group' => JText::_('VCMBCAHAMENTYPE29'),
				'name' => 'Room dialup internet free',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DIALUP_INTERNET_SURCHARGE' => [
				'group' => JText::_('VCMBCAHAMENTYPE29'),
				'name' => 'Room dialup internet surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],

			// Bathroom
			'ROOM_NUMBER_OF_BATHROOMS' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room number of bathrooms',
				'detailCodes' => null,
				'valueType' => [
					'type' => 'number',
					'attributes' => [
						'min' => 0,
						'max' => 10,
						'step' => '0.5',
					],
				],
			],
			'ROOM_BATHROOM_TYPE' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room bathroom type',
				'detailCodes' => [
					'PRIVATE_BATHROOM' => 'Private bathroom',
					'PARTIALLY_OPEN_BATHROOM' => 'Partially open bathroom',
					'PRIVATE_BATHROOM_NOT_IN_ROOM' => 'Private bathroom not in room',
					'SHARED_BATHROOM' => 'Shared bathroom',
					'SHARED_BATHROOM_SINK_IN_ROOM' => 'Shared bathroom sink in room',
				],
				'valueType' => null,
			],
			'ROOM_BATHROOM_TYPE_OUTDOOR' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room bathroom type outdoor',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SHOWER_TYPE' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room shower type',
				'detailCodes' => [
					'SHOWER_ONLY' => 'Shower only',
					'BATHTUB_ONLY' => 'Bathtub only',
					'BATHTUB_OR_SHOWER' => 'Bathtub or shower',
					'SEPARATE_BATHTUB_AND_SHOWER' => 'Separate bathtub and shower',
					'SHOWER_AND_BATHTUB_COMBO' => 'Shower and bathtub combo',
				],
				'valueType' => null,
			],
			'ROOM_SHOWER_TYPE_OUTDOOR' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room shower type outdoor',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BATHTUB_TYPE' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room bathtub type',
				'detailCodes' => [
					'DEEP_SOAKING' => 'Deep soaking',
					'JETTED' => 'Jetted',
					'SPRING_WATER' => 'Spring water',
				],
				'valueType' => null,
			],
			'ROOM_NO_BATHTUB' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room no bathtub',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_JETTED_BATHTUB' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room no jetted bathtub',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HYDROMASSAGE_SHOWERHEAD' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room hydromassage showerhead',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_RAINFALL_SHOWERHEAD' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room rainfall showerhead',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BABY_BATH' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room baby bath',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BATHROBES' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room bathrobes',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BATHROBES_CHILD_SIZE' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room bathrobes child size',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BIDET' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room bidet',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BIDET_ELECTRONIC' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room bidet electronic',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FREE_TOILETRIES' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room free toiletries',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DESIGNER_TOILETRIES' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room designer toiletries',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SHAMPOO' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room shampoo',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SOAP' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room soap',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TOILET_PAPER' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room toilet paper',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_FREE_TOILETRIES' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room no free toiletries',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_GETA_SANDALS' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room geta sandals',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HAIR_DRYER' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room hair dryer',
				'detailCodes' => [
					'IN_ROOM' => 'In room',
					'ON_REQUEST' => 'On request',
				],
				'valueType' => null,
			],
			'ROOM_NO_HAIR_DRYER' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room no hair dryer',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_INDOOR_PRIVATE_BATH_NO_MINERAL_SPRINGS' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room indoor private bath no mineral springs',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_OUTDOOR_PRIVATE_BATH_NO_MINERAL_SPRINGS' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room outdoor private bath no mineral springs',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PRIVATE_MINERAL_HOT_SPRINGS_INDOOR' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room private mineral hot springs indoor',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PRIVATE_MINERAL_HOT_SPRINGS_OUTDOOR' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room private mineral hot springs outdoor',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SLIPPERS' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room slippers',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SLIPPERS_CHILD_SIZE' => [
				'group' => JText::_('VCMBCAHIMGTAG52'),
				'name' => 'Room slippers child size',
				'detailCodes' => null,
				'valueType' => null,
			],

			// Food-related
			'ROOM_KITCHEN' => [
				'group' => 'Food-related',
				'name' => 'Room kitchen',
				'detailCodes' => [
					'KITCHEN' => 'Kitchen',
					'KITCHENETTE' => 'Kitchenette',
					'SHARED_KITCHEN' => 'Shared kitchen',
				],
				'valueType' => null,
			],
			'ROOM_KITCHEN_SURCHARGE' => [
				'group' => 'Food-related',
				'name' => 'Room kitchen surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_KITCHENETTE_SURCHARGE' => [
				'group' => 'Food-related',
				'name' => 'Room kitchenette surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_KITCHEN' => [
				'group' => 'Food-related',
				'name' => 'Room no kitchen',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BLENDER' => [
				'group' => 'Food-related',
				'name' => 'Room blender',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CHAMPAGNE_SERVICE' => [
				'group' => 'Food-related',
				'name' => 'Room champagne service',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_CHAMPAGNE_SERVICE' => [
				'group' => 'Food-related',
				'name' => 'Room no champagne service',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CHILD_DISHWARE' => [
				'group' => 'Food-related',
				'name' => 'Room child dishware',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLEANING_SUPPLIES' => [
				'group' => 'Food-related',
				'name' => 'Room cleaning supplies',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLEANING_SUPPLIES_SURCHARGE' => [
				'group' => 'Food-related',
				'name' => 'Room cleaning supplies surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_COFFEE_AND_TEA_MAKER' => [
				'group' => 'Food-related',
				'name' => 'Room coffee and tea maker',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_COFFEE_GRINDER' => [
				'group' => 'Food-related',
				'name' => 'Room coffee grinder',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ELECTRIC_KETTLE' => [
				'group' => 'Food-related',
				'name' => 'Room electric kettle',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ESPRESSO_MAKER' => [
				'group' => 'Food-related',
				'name' => 'Room espresso maker',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FREE_TEA_INSTANT_COFFEE' => [
				'group' => 'Food-related',
				'name' => 'Room free tea instant coffee',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_COFFEE_TEA_MAKER' => [
				'group' => 'Food-related',
				'name' => 'Room no coffee tea maker',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DISHWARE' => [
				'group' => 'Food-related',
				'name' => 'Room dishware',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DISHWASHER' => [
				'group' => 'Food-related',
				'name' => 'Room dishwasher',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_DISHWASHER' => [
				'group' => 'Food-related',
				'name' => 'Room no dishwasher',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FREE_BOTTLED_WATER' => [
				'group' => 'Food-related',
				'name' => 'Room free bottled water',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_FREE_BOTTLED_WATER' => [
				'group' => 'Food-related',
				'name' => 'Room no free bottled water',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FREEZER' => [
				'group' => 'Food-related',
				'name' => 'Room freezer',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_GRIDDLE' => [
				'group' => 'Food-related',
				'name' => 'Room griddle',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ICE_MAKER' => [
				'group' => 'Food-related',
				'name' => 'Room ice maker',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_IN_ROOM_DINING_ONLY' => [
				'group' => 'Food-related',
				'name' => 'Room in room dining only',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LOBSTER_POT' => [
				'group' => 'Food-related',
				'name' => 'Room lobster pot',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_MICROWAVE' => [
				'group' => 'Food-related',
				'name' => 'Room microwave',
				'detailCodes' => [
					'IN_ROOM' => 'In room',
					'ON_REQUEST' => 'On request',
				],
				'valueType' => null,
			],
			'ROOM_MICROWAVE_SURCHARGE' => [
				'group' => 'Food-related',
				'name' => 'Room microwave surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_MICROWAVE' => [
				'group' => 'Food-related',
				'name' => 'Room no microwave',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_MINIBAR' => [
				'group' => 'Food-related',
				'name' => 'Room minibar',
				'detailCodes' => [
					'STOCKED_WITH_FREE_ITEMS' => 'Stocked with free items',
					'STOCKED_WITH_SOME_FREE_ITEMS' => 'Stocked with some free items',
					'STOCKED_NO_FREE_ITEMS' => 'Stocked no free items',
				],
				'valueType' => null,
			],
			'ROOM_NO_MINIBAR' => [
				'group' => 'Food-related',
				'name' => 'Room no minibar',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_MIXER' => [
				'group' => 'Food-related',
				'name' => 'Room mixer',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_OVEN' => [
				'group' => 'Food-related',
				'name' => 'Room oven',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_OVEN' => [
				'group' => 'Food-related',
				'name' => 'Room no oven',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PAPER_TOWELS' => [
				'group' => 'Food-related',
				'name' => 'Room paper towels',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_ROOM_SIZE' => [
				'group' => 'Food-related',
				'name' => 'Room no room size',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_REFRIGERATOR' => [
				'group' => 'Food-related',
				'name' => 'Room refrigerator',
				'detailCodes' => [
					'IN_ROOM' => 'In room',
					'FULL_SIZE_IN_ROOM' => 'Full size in room',
					'MINIFRIDGE_IN_ROOM' => 'Minifridge in room',
					'ON_REQUEST' => 'On request',
				],
				'valueType' => null,
			],
			'ROOM_REFRIGERATOR_SURCHARGE' => [
				'group' => 'Food-related',
				'name' => 'Room refrigerator surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_REFRIGERATOR' => [
				'group' => 'Food-related',
				'name' => 'Room no refrigerator',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_RICE_COOKER' => [
				'group' => 'Food-related',
				'name' => 'Room rice cooker',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SPICES' => [
				'group' => 'Food-related',
				'name' => 'Room spices',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_STOVETOP' => [
				'group' => 'Food-related',
				'name' => 'Room stovetop',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_STOVETOP' => [
				'group' => 'Food-related',
				'name' => 'Room no stovetop',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TOASTER' => [
				'group' => 'Food-related',
				'name' => 'Room toaster',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TOASTER_OVEN' => [
				'group' => 'Food-related',
				'name' => 'Room toaster oven',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WAFFLE_MAKER' => [
				'group' => 'Food-related',
				'name' => 'Room waffle maker',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WET_BAR' => [
				'group' => 'Food-related',
				'name' => 'Room wet bar',
				'detailCodes' => null,
				'valueType' => null,
			],

			// In-room entertainment
			'ROOM_ART_SUPPLIES' => [
				'group' => 'In-room entertainment',
				'name' => 'Room art supplies',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CD_PLAYER' => [
				'group' => 'In-room entertainment',
				'name' => 'Room cd player',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CHILD_BOOKS' => [
				'group' => 'In-room entertainment',
				'name' => 'Room child books',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TV_SERVICE' => [
				'group' => 'In-room entertainment',
				'name' => 'Room tv service',
				'detailCodes' => [
					'CABLE' => 'Cable',
					'SATELLITE' => 'Satellite',
					'DIGITAL' => 'Digital',
				],
				'valueType' => null,
			],
			'ROOM_NO_TV_SERVICE' => [
				'group' => 'In-room entertainment',
				'name' => 'Room no tv service',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SMART_TV' => [
				'group' => 'In-room entertainment',
				'name' => 'Room smart tv',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TV' => [
				'group' => 'In-room entertainment',
				'name' => 'Room tv',
				'detailCodes' => [
					'LCD' => 'LCD',
					'LED' => 'Led',
					'PLASMA' => 'Plasma',
					'FLAT_PANEL' => 'Flat panel',
					'GENERIC' => 'Generic',
				],
				'valueType' => null,
			],
			'ROOM_TV_SIZE' => [
				'group' => 'In-room entertainment',
				'name' => 'Room tv size',
				'detailCodes' => [
					'SIZE_INCH' => 'Size inches',
					'SIZE_CM' => 'Size centimeters',
				],
				'valueType' => [
					'type' => 'number',
					'attributes' => [
						'min' => 1,
						'max' => 1000,
						'step' => 'any',
					],
				],
			],
			'ROOM_TV_SURCHARGE' => [
				'group' => 'In-room entertainment',
				'name' => 'Room tv surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_TV' => [
				'group' => 'In-room entertainment',
				'name' => 'Room no tv',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DVD_PLAYER' => [
				'group' => 'In-room entertainment',
				'name' => 'Room dvd player',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DVR' => [
				'group' => 'In-room entertainment',
				'name' => 'Room dvr',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FIRST_RUN_MOVIES' => [
				'group' => 'In-room entertainment',
				'name' => 'Room first run movies',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_MUSICAL_INSTRUMENTS' => [
				'group' => 'In-room entertainment',
				'name' => 'Room musical instruments',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HULU' => [
				'group' => 'In-room entertainment',
				'name' => 'Room hulu',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NETFLIX' => [
				'group' => 'In-room entertainment',
				'name' => 'Room netflix',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ONCOMMAND_VIDEO' => [
				'group' => 'In-room entertainment',
				'name' => 'Room oncommand video',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PAY_MOVIES' => [
				'group' => 'In-room entertainment',
				'name' => 'Room pay movies',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_PAY_MOVIES' => [
				'group' => 'In-room entertainment',
				'name' => 'Room no pay movies',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PREMIUM_TV_CHANNELS' => [
				'group' => 'In-room entertainment',
				'name' => 'Room premium tv channels',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_PREMIUM_TV_CHANNELS' => [
				'group' => 'In-room entertainment',
				'name' => 'Room no premium tv channels',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_STREAMING_SERVICES' => [
				'group' => 'In-room entertainment',
				'name' => 'Room streaming services',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_VIDEO_GAME' => [
				'group' => 'In-room entertainment',
				'name' => 'Room video game',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_COMPUTER' => [
				'group' => 'In-room entertainment',
				'name' => 'Room computer',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_IPAD' => [
				'group' => 'In-room entertainment',
				'name' => 'Room ipad',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TABLET' => [
				'group' => 'In-room entertainment',
				'name' => 'Room tablet',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_IPOD_DOCK' => [
				'group' => 'In-room entertainment',
				'name' => 'Room ipod dock',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_MP3_PLAYER_DOCK' => [
				'group' => 'In-room entertainment',
				'name' => 'Room mp3 player dock',
				'detailCodes' => null,
				'valueType' => null,
			],

			// Bedding and linens
			'ROOM_EXTRA_FUTON_FREE' => [
				'group' => 'Bedding and linens',
				'name' => 'Room extra futon free',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PREMIUM_LINENS' => [
				'group' => 'Bedding and linens',
				'name' => 'Room premium linens',
				'detailCodes' => [
					'EGYPTIAN_COTTON_SHEETS' => 'Egyptian cotton sheets',
					'FRETTE_ITALIAN_SHEETS' => 'Frette italian sheets',
				],
				'valueType' => null,
			],
			'ROOM_HYPO_BED_AVAIL' => [
				'group' => 'Bedding and linens',
				'name' => 'Room hypo bed avail',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PREMIUM_MATTRESS' => [
				'group' => 'Bedding and linens',
				'name' => 'Room premium mattress',
				'detailCodes' => [
					'MEMORY_FOAM' => 'Memory foam',
					'PILLOW_TOP' => 'Pillow top',
					'SLEEP_NUMBER' => 'Sleep number',
					'TEMPURPEDIC' => 'Tempurpedic',
				],
				'valueType' => null,
			],
			'ROOM_DOWN_COMFORTER' => [
				'group' => 'Bedding and linens',
				'name' => 'Room down comforter',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PILLOW_MENU' => [
				'group' => 'Bedding and linens',
				'name' => 'Room pillow menu',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PREMIUM_BEDDING' => [
				'group' => 'Bedding and linens',
				'name' => 'Room premium bedding',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_PREMIUM_BEDDING' => [
				'group' => 'Bedding and linens',
				'name' => 'Room no premium bedding',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LINENS_INCLUDED' => [
				'group' => 'Bedding and linens',
				'name' => 'Room linens included',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LINENS_PROVIDED' => [
				'group' => 'Bedding and linens',
				'name' => 'Room linens provided',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_LINENS' => [
				'group' => 'Bedding and linens',
				'name' => 'Room no linens',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TOWELS' => [
				'group' => 'Bedding and linens',
				'name' => 'Room towels',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TOWELS_SURCHARGE' => [
				'group' => 'Bedding and linens',
				'name' => 'Room towels surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_TOWELS' => [
				'group' => 'Bedding and linens',
				'name' => 'Room no towels',
				'detailCodes' => null,
				'valueType' => null,
			],

			// Room layout (Bedroom)
			'ROOM_NUMBER_OF_SEPARATE_BEDROOMS' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room number of separate bedrooms',
				'detailCodes' => null,
				'valueType' => [
					'type' => 'number',
					'attributes' => [
						'min' => 0,
						'max' => 10,
						'step' => '1',
					],
				],
			],
			'ROOM_NO_SEPARATE_BEDROOM' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room no separate bedroom',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DINING_AREA' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room dining area',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_SEPARATE_DINING_AREA' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room no separate dining area',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FUSUMA_PARTITION' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room fusuma partition',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LAPTOP_WORKSPACE' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room laptop workspace',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LIVING_ROOM' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room living room',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_SEPARATE_LIVING_ROOM' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room no separate living room',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SITTING_AREA' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room sitting area',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_SEPARATE_SITTING_AREA' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room no separate sitting area',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BALCONY' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room balcony',
				'detailCodes' => [
					'BALCONY' => 'Balcony',
					'BALCONY_OR_PATIO' => 'Balcony or patio',
					'FURNISHED_BALCONY' => 'Furnished balcony',
					'FURNISHED_BALCONY_OR_PATIO' => 'Furnished balcony or patio',
					'FURNISHED_LANAI' => 'Furnished lanai',
					'FURNISHED_PATIO' => 'Furnished patio',
					'LANAI' => 'Lanai',
					'PATIO' => 'Patio',
				],
				'valueType' => null,
			],
			'ROOM_DECK_OR_PATIO' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room deck or patio',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_BALCONY_OR_PATIO' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room no balcony or patio',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PRIVATE_POOL' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room private pool',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PRIVATE_PLUNGE_POOL' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room private plunge pool',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_PRIVATE_POOL' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room no private pool',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PRIVATE_SPA' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room private spa',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CONNECTED_ROOMS' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room connected rooms',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_EXT_ACCESS' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room ext access',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SOUND_ISOLATION' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room sound isolation',
				'detailCodes' => [
					'SOUNDPROOFED' => 'Soundproofed',
					'NOISE_DISCLAIMER' => 'Noise disclaimer',
				],
				'valueType' => null,
			],
			'ROOM_TATAMI_FLOOR' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room tatami floor',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TOKONOMA_ALCOVE' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room tokonoma alcove',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_YARD' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room yard',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_WINDOWS' => [
				'group' => 'Room layout (Bedroom)',
				'name' => 'Room no windows',
				'detailCodes' => null,
				'valueType' => null,
			],

			// Accessibility
			'ROOM_ACCESSIBLE_BATHTUB' => [
				'group' => 'Accessibility',
				'name' => 'Room accessible bathtub',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BRAILLE_SIGNAGE' => [
				'group' => 'Accessibility',
				'name' => 'Room braille signage',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DOORBELL_PHONE_NOTIFICATION' => [
				'group' => 'Accessibility',
				'name' => 'Room doorbell phone notification',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_EMERGENCY_PULL_CORD_IN_BATHROOM' => [
				'group' => 'Accessibility',
				'name' => 'Room emergency pull cord in bathroom',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_GRAB_BARS_BATHROOM' => [
				'group' => 'Accessibility',
				'name' => 'Room grab bars bathroom',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_GRAB_BARS_IN_BATHTUB' => [
				'group' => 'Accessibility',
				'name' => 'Room grab bars in bathtub',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_GRAB_BARS_IN_SHOWER' => [
				'group' => 'Accessibility',
				'name' => 'Room grab bars in shower',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_GRAB_BARS_NEAR_TOILET' => [
				'group' => 'Accessibility',
				'name' => 'Room grab bars near toilet',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HANDHELD_SHOWER' => [
				'group' => 'Accessibility',
				'name' => 'Room handheld shower',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HEIGHT_ADJUSTED_AMENITIES' => [
				'group' => 'Accessibility',
				'name' => 'Room height adjusted amenities',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HEIGHT_ADJUSTED_SHOWERHEAD' => [
				'group' => 'Accessibility',
				'name' => 'Room height adjusted showerhead',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LEVER_HANDLES' => [
				'group' => 'Accessibility',
				'name' => 'Room lever handles',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LOW_HEIGHT_BED' => [
				'group' => 'Accessibility',
				'name' => 'Room low height bed',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LOW_HEIGHT_COUNTERS_OR_SINK' => [
				'group' => 'Accessibility',
				'name' => 'Room low height counters or sink',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LOW_HEIGHT_DESK' => [
				'group' => 'Accessibility',
				'name' => 'Room low height desk',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LOWERED_BATHROOM_OUTLETS' => [
				'group' => 'Accessibility',
				'name' => 'Room lowered bathroom outlets',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LOWERED_DOOR_LOCKS' => [
				'group' => 'Accessibility',
				'name' => 'Room lowered door locks',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LOWERED_PEEPHOLE' => [
				'group' => 'Accessibility',
				'name' => 'Room lowered peephole',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PHONE_ACCESSIBILITY_KIT' => [
				'group' => 'Accessibility',
				'name' => 'Room phone accessibility kit',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PILLOW_ALARM' => [
				'group' => 'Accessibility',
				'name' => 'Room pillow alarm',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PORTABLE_BATHTUB_SEAT' => [
				'group' => 'Accessibility',
				'name' => 'Room portable bathtub seat',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PORTABLE_SHOWER_SEAT' => [
				'group' => 'Accessibility',
				'name' => 'Room portable shower seat',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_RAISED_TOILET_SEAT' => [
				'group' => 'Accessibility',
				'name' => 'Room raised toilet seat',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ROLL_IN_SHOWER' => [
				'group' => 'Accessibility',
				'name' => 'Room roll in shower',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TRANSFER_SHOWER' => [
				'group' => 'Accessibility',
				'name' => 'Room transfer shower',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TV_CLOSED_CAPTIONED' => [
				'group' => 'Accessibility',
				'name' => 'Room tv closed captioned',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_VISIBLE_DOORBELL' => [
				'group' => 'Accessibility',
				'name' => 'Room visible doorbell',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_VISUAL_FIRE_ALARM' => [
				'group' => 'Accessibility',
				'name' => 'Room visual fire alarm',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WHEELCHAIR_ACCESSIBLE_VANITY' => [
				'group' => 'Accessibility',
				'name' => 'Room wheelchair accessible vanity',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WHEELCHAIR_WIDTH_DOORS' => [
				'group' => 'Accessibility',
				'name' => 'Room wheelchair width doors',
				'detailCodes' => null,
				'valueType' => null,
			],

			// In-room services
			'ROOM_CHEF' => [
				'group' => 'In-room services',
				'name' => 'Room chef',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HOUSEKEEPING' => [
				'group' => 'In-room services',
				'name' => 'Room housekeeping',
				'detailCodes' => [
					'DAILY' => 'Daily',
					'LIMITED' => 'Limited',
					'ONCE_PER_STAY' => 'Once per stay',
					'WEEKENDS' => 'Weekends',
				],
				'valueType' => null,
			],
			'ROOM_HOUSEKEEPING_ON_REQUEST' => [
				'group' => 'In-room services',
				'name' => 'Room housekeeping on request',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_HOUSEKEEPING' => [
				'group' => 'In-room services',
				'name' => 'Room no housekeeping',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NEWSPAPER_FREE' => [
				'group' => 'In-room services',
				'name' => 'Room newspaper free',
				'detailCodes' => [
					'DAILY' => 'Daily',
					'WEEKDAYS' => 'Weekdays',
				],
				'valueType' => null,
			],
			'ROOM_NO_NEWSPAPER_FREE' => [
				'group' => 'In-room services',
				'name' => 'Room no newspaper free',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CHILDCARE' => [
				'group' => 'In-room services',
				'name' => 'Room childcare',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_GUIDEBOOKS' => [
				'group' => 'In-room services',
				'name' => 'Room guidebooks',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LOCAL_MAPS' => [
				'group' => 'In-room services',
				'name' => 'Room local maps',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_MASSAGE' => [
				'group' => 'In-room services',
				'name' => 'Room massage',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_RESTAURANT_GUIDE' => [
				'group' => 'In-room services',
				'name' => 'Room restaurant guide',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_TURNDOWN' => [
				'group' => 'In-room services',
				'name' => 'Room turndown',
				'detailCodes' => null,
				'valueType' => null,
			],

			// Room features
			'ROOM_AIR_CONDITIONING' => [
				'group' => 'Room features',
				'name' => 'Room air conditioning',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_AIR_CONDITIONING_SURCHARGE' => [
				'group' => 'Room features',
				'name' => 'Room air conditioning surcharge',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLIMATE_CONTROL' => [
				'group' => 'Room features',
				'name' => 'Room climate control',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLIMATE_CONTROL_HEAT' => [
				'group' => 'Room features',
				'name' => 'Room climate control heat',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HEATING' => [
				'group' => 'Room features',
				'name' => 'Room heating',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_AIR_CONDITIONING' => [
				'group' => 'Room features',
				'name' => 'Room no air conditioning',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CEILING_FAN' => [
				'group' => 'Room features',
				'name' => 'Room ceiling fan',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_CEILING_FAN' => [
				'group' => 'Room features',
				'name' => 'Room no ceiling fan',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_GENERAL_FAN' => [
				'group' => 'Room features',
				'name' => 'Room general fan',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PORTABLE_FAN' => [
				'group' => 'Room features',
				'name' => 'Room portable fan',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BABY_MONITOR' => [
				'group' => 'Room features',
				'name' => 'Room baby monitor',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_BLACKOUT_DRAPES' => [
				'group' => 'Room features',
				'name' => 'Room blackout drapes',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CHANGING_TABLE' => [
				'group' => 'Room features',
				'name' => 'Room changing table',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DECOR' => [
				'group' => 'Room features',
				'name' => 'Room decor',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FURNISHING' => [
				'group' => 'Room features',
				'name' => 'Room furnishing',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_DESK' => [
				'group' => 'Room features',
				'name' => 'Room desk',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_DESK' => [
				'group' => 'Room features',
				'name' => 'Room no desk',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ELECTRICAL_ADAPTER_OR_CHARGER' => [
				'group' => 'Room features',
				'name' => 'Room electrical adapter or charger',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ENVIRONMENT_COMPOSTING' => [
				'group' => 'Room features',
				'name' => 'Room environment composting',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ENVIRONMENT_ECO_FRIENDLY_CLEANING_PRODUCTS' => [
				'group' => 'Room features',
				'name' => 'Room environment eco friendly cleaning products',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ENVIRONMENT_ECO_FRIENDLY_TOILETRIES' => [
				'group' => 'Room features',
				'name' => 'Room environment eco friendly toiletries',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ENVIRONMENT_LED_LIGHT_BULBS' => [
				'group' => 'Room features',
				'name' => 'Room environment led light bulbs',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ENVIRONMENT_RECYCLING' => [
				'group' => 'Room features',
				'name' => 'Room environment recycling',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ENVIRONMENT_REUSABLE_COFFEE_TEA_FILTERS' => [
				'group' => 'Room features',
				'name' => 'Room environment reusable coffee tea filters',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FIREPLACE' => [
				'group' => 'Room features',
				'name' => 'Room fireplace',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_FIREPLACE' => [
				'group' => 'Room features',
				'name' => 'Room no fireplace',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HEATED_FLOOR' => [
				'group' => 'Room features',
				'name' => 'Room heated floor',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HEATED_FLOOR_BATHROOM_ONLY' => [
				'group' => 'Room features',
				'name' => 'Room heated floor bathroom only',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_HIGHCHAIR' => [
				'group' => 'Room features',
				'name' => 'Room highchair',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_IRON' => [
				'group' => 'Room features',
				'name' => 'Room iron',
				'detailCodes' => [
					'IN_ROOM' => 'In room',
					'ON_REQUEST' => 'On request',
				],
				'valueType' => null,
			],
			'ROOM_NO_IRON' => [
				'group' => 'Room features',
				'name' => 'Room no iron',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LAUNDRY_DETERGENT' => [
				'group' => 'Room features',
				'name' => 'Room laundry detergent',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_LIMITED_FACILITY_ACCESS' => [
				'group' => 'Room features',
				'name' => 'Room limited facility access',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FREE_LOCAL_CALLS' => [
				'group' => 'Room features',
				'name' => 'Room free local calls',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FREE_LONG_DISTANCE_CALLS' => [
				'group' => 'Room features',
				'name' => 'Room free long distance calls',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FREE_INTERNATIONAL_CALLS' => [
				'group' => 'Room features',
				'name' => 'Room free international calls',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PET_FRIENDLY' => [
				'group' => 'Room features',
				'name' => 'Room pet friendly',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NOT_PET_FRIENDLY' => [
				'group' => 'Room features',
				'name' => 'Room not pet friendly',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PHONE' => [
				'group' => 'Room features',
				'name' => 'Room phone',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_PLAYPEN' => [
				'group' => 'Room features',
				'name' => 'Room playpen',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_RECENT_RENOVATION_MONTH' => [
				'group' => 'Room features',
				'name' => 'Room recent renovation month',
				'detailCodes' => null,
				'valueType' => [
					'type' => 'number',
					'attributes' => [
						'min' => 0,
						'max' => 12,
						'step' => '1',
					],
				],
			],
			'ROOM_RECENT_RENOVATION_YEAR' => [
				'group' => 'Room features',
				'name' => 'Room recent renovation year',
				'detailCodes' => null,
				'valueType' => [
					'type' => 'number',
					'attributes' => [
						'min' => 2000,
						'step' => '1',
					],
				],
			],
			'ROOM_RUN_OF_HOUSE' => [
				'group' => 'Room features',
				'name' => 'Room run of house',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SAFE' => [
				'group' => 'Room features',
				'name' => 'Room safe',
				'detailCodes' => [
					'LAPTOP_COMPATIBLE' => 'Laptop compatible',
					'STANDARD_SIZE' => 'Standard size',
					'SURCHARGE' => 'Surcharge',
				],
				'valueType' => null,
			],
			'ROOM_NO_IN_ROOM_SAFE' => [
				'group' => 'Room features',
				'name' => 'Room no in room safe',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SAFETY_CAR_AVAILABLE' => [
				'group' => 'Room features',
				'name' => 'Room safety car available',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SAFETY_EMERGENCY_EXIT_ROUTE' => [
				'group' => 'Room features',
				'name' => 'Room safety emergency exit route',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SAFETY_FIRE_EMERGENCY_CONTACT' => [
				'group' => 'Room features',
				'name' => 'Room safety fire emergency contact',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SAFETY_MEDICAL_EMERGENCY_CONTACT' => [
				'group' => 'Room features',
				'name' => 'Room safety medical emergency contact',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SAFETY_POLICE_EMERGENCY_CONTACT' => [
				'group' => 'Room features',
				'name' => 'Room safety police emergency contact',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SHARED_ACCOMODATIONS' => [
				'group' => 'Room features',
				'name' => 'Room shared accomodations',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SMARTPHONE' => [
				'group' => 'Room features',
				'name' => 'Room smartphone',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_SMARTPHONE_DATA' => [
				'group' => 'Room features',
				'name' => 'Room smartphone data',
				'detailCodes' => [
					'LIMITED' => 'Limited',
					'UNLIMITED' => 'Unlimited',
				],
				'valueType' => null,
			],
			'ROOM_SMARTPHONE_DATA_SPEED' => [
				'group' => 'Room features',
				'name' => 'Room smartphone data speed',
				'detailCodes' => [
					'3G' => '3G',
					'4G' => '4G',
					'LTE' => 'LTE',
				],
				'valueType' => null,
			],
			'ROOM_SMARTPHONE_FREE_CALLS' => [
				'group' => 'Room features',
				'name' => 'Room smartphone free calls',
				'detailCodes' => [
					'LIMITED' => 'Limited',
					'UNLIMITED' => 'Unlimited',
				],
				'valueType' => null,
			],
			'ROOM_DRYER' => [
				'group' => 'Room features',
				'name' => 'Room dryer',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WASHER' => [
				'group' => 'Room features',
				'name' => 'Room washer',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WASHER_DRYER_ALL_IN_ONE' => [
				'group' => 'Room features',
				'name' => 'Room washer dryer all in one',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WASHING_MACHINE' => [
				'group' => 'Room features',
				'name' => 'Room washing machine',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_NO_IN_ROOM_LAUNDRY' => [
				'group' => 'Room features',
				'name' => 'Room no in room laundry',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_WINDOW_SCREENS' => [
				'group' => 'Room features',
				'name' => 'Room window screens',
				'detailCodes' => null,
				'valueType' => null,
			],

			// Fitness
			'ROOM_TREADMILL' => [
				'group' => 'Fitness',
				'name' => 'Room treadmill',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_STATIONARY_BIKE' => [
				'group' => 'Fitness',
				'name' => 'Room stationary bike',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ROWING_MACHINE' => [
				'group' => 'Fitness',
				'name' => 'Room rowing machine',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_ELLIPTICAL_MACHINE' => [
				'group' => 'Fitness',
				'name' => 'Room elliptical machine',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_FREE_WEIGHTS' => [
				'group' => 'Fitness',
				'name' => 'Room free weights',
				'detailCodes' => [
					'IN_ROOM' => 'In room',
					'ON_REQUEST' => 'On request',
				],
				'valueType' => null,
			],
			'ROOM_STREAMING_FITNESS_CLASSES' => [
				'group' => 'Fitness',
				'name' => 'Room streaming fitness classes',
				'detailCodes' => [
					'IN_ROOM' => 'In room',
					'ON_REQUEST' => 'On request',
				],
				'valueType' => null,
			],
			'ROOM_WORKOUT_APPAREL' => [
				'group' => 'Fitness',
				'name' => 'Room workout apparel',
				'detailCodes' => [
					'IN_ROOM' => 'In room',
					'ON_REQUEST' => 'On request',
				],
				'valueType' => null,
			],
			'ROOM_OTHER_WORKOUT_EQUIPMENT' => [
				'group' => 'Fitness',
				'name' => 'Room other workout equipment',
				'detailCodes' => [
					'IN_ROOM' => 'In room',
					'ON_REQUEST' => 'On request',
				],
				'valueType' => null,
			],

			// Club or executive level amenities
			'ROOM_CLUB_EXEC_LEVEL' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec level',
				'detailCodes' => [
					'CLUB_LEVEL' => 'Club level',
					'EXEC_LEVEL' => 'Exec level',
				],
				'valueType' => null,
			],
			'ROOM_NO_CLUB_EXEC_LEVEL' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room no club exec level',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLUB_EXEC_LOUNGE_ACCESS' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec lounge access',
				'detailCodes' => [
					'CLUB_LOUNGE' => 'Club lounge',
					'EXEC_LOUNGE' => 'Exec lounge',
				],
				'valueType' => null,
			],
			'ROOM_CLUB_EXEC_LEVEL_MIN_AGE' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec level min age',
				'detailCodes' => null,
				'valueType' => [
					'type' => 'number',
					'attributes' => [
						'min' => 0,
						'max' => 18,
						'step' => '1',
					],
				],
			],
			'ROOM_CLUB_EXEC_MEET_ROOM' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec meet room',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLUB_EXEC_MEET_ROOM_TIME_LIMIT_HOURS' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec meet room time limit hours',
				'detailCodes' => null,
				'valueType' => [
					'type' => 'number',
					'attributes' => [
						'min' => 1,
						'max' => 24,
						'step' => '1',
					],
				],
			],
			'ROOM_CLUB_EXEC_BREAKFAST' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec breakfast',
				'detailCodes' => [
					'BREAKFAST_BUFFET' => 'Breakfast buffet',
					'BREAKFAST_CONTINENTAL' => 'Breakfast continental',
					'BREAKFAST_COOKED' => 'Breakfast cooked',
					'BREAKFAST_ENGLISH' => 'Breakfast english',
					'BREAKFAST_FULL' => 'Breakfast full',
				],
				'valueType' => null,
			],
			'ROOM_CLUB_EXEC_REFRESHMENTS' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec refreshments',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLUB_EXEC_LOUNGE_INTERNET' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec lounge internet',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLUB_EXEC_LUNCH' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec lunch',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLUB_EXEC_SEPARATE_CHECKIN' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec separate checkin',
				'detailCodes' => null,
				'valueType' => null,
			],
			'ROOM_CLUB_EXEC_DINNER' => [
				'group' => 'Club or executive level amenities',
				'name' => 'Room club exec dinner',
				'detailCodes' => null,
				'valueType' => null,
			],
		];
	}

	/**
	 * Returns an associative list of "per stay fees" to be used for cancellation penalties.
	 * 
	 * @return 	array
	 */
	public static function getPerStayFees()
	{
		return [
			'None' => 'None',
			'1stNightRoomAndTax' => '1st Night Room and Tax',
			'2NightsRoomAndTax' => '2 Nights Room and Tax',
			'10PercentCostOfStay' => '10% Cost of Stay',
			'20PercentCostOfStay' => '20% Cost of Stay',
			'30PercentCostOfStay' => '30% Cost of Stay',
			'40PercentCostOfStay' => '40% Cost of Stay',
			'50PercentCostOfStay' => '50% Cost of Stay',
			'60PercentCostOfStay' => '60% Cost of Stay',
			'70PercentCostOfStay' => '70% Cost of Stay',
			'80PercentCostOfStay' => '80% Cost of Stay',
			'90PercentCostOfStay' => '90% Cost of Stay',
			'FullCostOfStay' => 'Full Cost of Stay',
		];
	}

	/**
	 * Returns a list of age category values.
	 * 
	 * @return 	array
	 */
	public static function getAgeCategories()
	{
		return [
			'Adult',
			'ChildAgeA',
			'ChildAgeB',
			'ChildAgeC',
			'ChildAgeD',
			'Infant',
		];
	}

	/**
	 * Returns an associative list of value add inclusions for a rate plan.
	 * 
	 * @return 	array
	 */
	public static function getValueAddInclusions()
	{
		return [
			'Free Breakfast' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Continental Breakfast' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Continental Breakfast for 2' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Breakfast Buffet' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Full Breakfast' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'English Breakfast' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Breakfast for 1' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Breakfast for 2' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Internet' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free WiFi' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free High-Speed Internet' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Parking' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free welcome drink' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Drinks and hors doeuvres' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'All Meals' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Half Board' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Full Board' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Lunch' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Dinner' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'All-Inclusive' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Food/Beverage Credit' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Airport Parking' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Valet Parking' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Airport Shuttle' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Room Upgrade' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Resort Credit Included' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Welcome Gift Upon Arrival' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Spa Credit' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Golf Credit' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'VIP Line Access to Nightclub(s)' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'2-for-1 Buffet' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free Ski Lift Ticket & Rental' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Full Kitchen' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Complimentary green fees' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free one-way airport transfer' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free return airport transfer' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free water park passes' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'2 Game Drives per night' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'1 Game Drive per night' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Early Check-in' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Late Check-out' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free massage included' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free minibar' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Ski pass included' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Disney Park tickets' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Spa access' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Slot Play' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Casino Credit' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Match Play' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Free 75CAD Gift Card' => [
				'group' => 'For Standalone and Package rate plans',
			],
			'Same-Day Cancellation' => [
				'group' => 'For Corporate rate plans',
			],
			'Eligible for Hotel Loyalty Points' => [
				'group' => 'For Corporate rate plans',
			],
			'Free High-Speed Internet' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Breakfast' => [
				'group' => 'For Corporate rate plans',
			],
			'Continental Breakfast' => [
				'group' => 'For Corporate rate plans',
			],
			'Full Breakfast' => [
				'group' => 'For Corporate rate plans',
			],
			'Breakfast Buffet' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Fitness Center Access' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Business Center Access' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Airport Shuttle' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Hotel Parking' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Valet Parking' => [
				'group' => 'For Corporate rate plans',
			],
			'Welcome Drink Upon Arrival' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Local Calls' => [
				'group' => 'For Corporate rate plans',
			],
			'Complimentary Wine Reception' => [
				'group' => 'For Corporate rate plans',
			],
			'Free WiFi' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Local Shuttle' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Local Newspaper' => [
				'group' => 'For Corporate rate plans',
			],
			'Includes One Free In-Room Movie' => [
				'group' => 'For Corporate rate plans',
			],
			'Room Upgrade Upon Availability' => [
				'group' => 'For Corporate rate plans',
			],
			'Guaranteed Room Upgrade' => [
				'group' => 'For Corporate rate plans',
			],
			'Early Check-In Privilege' => [
				'group' => 'For Corporate rate plans',
			],
			'Late Check-out Privilege' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Bottled Water' => [
				'group' => 'For Corporate rate plans',
			],
			'City Tax Included' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Train Shuttle' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Breakfast for 1 Adult' => [
				'group' => 'For Corporate rate plans',
			],
			'Upgrade to Business Floor' => [
				'group' => 'For Corporate rate plans',
			],
			'Complimentary Minibar Items' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Dinner' => [
				'group' => 'For Corporate rate plans',
			],
			'Free Dinner for 1 Adult' => [
				'group' => 'For Corporate rate plans',
			],
			'Full Kitchen' => [
				'group' => 'For Corporate rate plans',
			],
			'Incl. 1000 CP Reward Pts.' => [
				'group' => 'For Corporate rate plans',
			],
			'Incl. 500 CP Reward Pts.' => [
				'group' => 'For Corporate rate plans',
			],
			'Incl. 5000 CP Reward Pts.' => [
				'group' => 'For Corporate rate plans',
			],
			'Incl. 4000 CP Reward Pts.' => [
				'group' => 'For Corporate rate plans',
			],
			'Evening Manager\'s Reception' => [
				'group' => 'For Corporate rate plans',
			],
			'Egencia Exclusive Rate' => [
				'group' => 'For Corporate rate plans',
			],
			'Food-and-Beverage Discount' => [
				'group' => 'For Corporate rate plans',
			],
			'In-Room Dinner' => [
				'group' => 'For Corporate rate plans',
			],
			'Resort Fee Waived' => [
				'group' => 'For Corporate rate plans',
			],
		];
	}

	/**
	 * Returns an associative list of rate plan value add inclusions including
	 * certain meal plans to understand if breakfast, lunch, dinner are offered.
	 * 
	 * @return 	array 	associative list of Expedia v.a.i enum and meal plans included.
	 * 
	 * @since 	1.8.12
	 */
	public static function getMealValueAddInclusions()
	{
		$meal_value_adds = [];

		foreach (self::getValueAddInclusions() as $vai_enum => $vai_data) {
			$meals_included = [];
			if (!strcasecmp($vai_enum, 'All Meals') || !strcasecmp($vai_enum, 'All-Inclusive') || !strcasecmp($vai_enum, 'Full Board')) {
				// all meals included
				$meals_included = [
					'breakfast',
					'lunch',
					'dinner',
				];
			} elseif (!strcasecmp($vai_enum, 'Half Board')) {
				// breakfast and dinner included
				$meals_included = [
					'breakfast',
					'dinner',
				];
			} else {
				// check what meals are included
				if (preg_match("/breakfast/i", $vai_enum)) {
					$meals_included[] = 'breakfast';
				}
				if (preg_match("/lunch/i", $vai_enum)) {
					$meals_included[] = 'lunch';
				}
				if (preg_match("/dinner/i", $vai_enum)) {
					$meals_included[] = 'dinner';
				}
			}

			if ($meals_included) {
				$vai_enum = strtolower($vai_enum);
				$meal_value_adds[$vai_enum] = $meals_included;
			}
		}

		return $meal_value_adds;
	}
}
