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
 * Booking.com Content API helper.
 * 
 * @since 	1.8.20
 */
final class VCMBookingcomContent
{
	/**
	 * Returns a list of hotel amenity codes (HAC - OTA 2014B).
	 * 
	 * @param 	bool 	$no_group 	if true, a raw associative list will be returned.
	 * 
	 * @return 	array
	 */
	public static function getHotelAmenityCodes($no_group = false)
	{
		$hac_list = [
			[
				'group' => 'Top amenities - Hotel',
				'list' 	=> [
					'5154' => 'Swimming pool',
					'76' => 'Restaurant',
					'77' => 'Room service',
					'165' => 'Bar',
					'1' => '24-hour front desk',
					'79' => 'Sauna',
					'35' => 'Fitness centre',
					'5086' => 'Fitness centre',
					'5005' => 'Garden',
					'5006' => 'Terrace',
					'198' => 'Non-smoking rooms',
					'41' => 'Free airport shuttle',
					'282' => 'Airport shuttle with surcharge',
					'5123' => 'Airport shuttle with surcharge',
					'5041' => 'Family Rooms',
					'5044' => 'Spa and wellness centre',
					'55' => 'Jacuzzi or hot tub',
					'5' => 'Air conditioning',
					'5054' => 'Kids\' club',
					'5056' => 'Water park',
				],
			],
			[
				'group' => 'Top amenities - Home',
				'list' 	=> [
					'5154' => 'Swimming pool',
					'165' => 'Bar',
					'79' => 'Sauna',
					'5005' => 'Garden',
					'5006' => 'Terrace',
					'198' => 'Non-smoking rooms',
					'5041' => 'Family Rooms',
					'55' => 'Jacuzzi or hot tub',
					'5' => 'Air conditioning',
				],
			],
			[
				'group' => 'Top amenities - Hostel',
				'list' 	=> [
					'165' => 'Bar',
					'5005' => 'Garden',
					'44' => 'Game room',
					'5025' => 'Billiard',
					'5026' => 'Table tennis',
					'5045' => 'Karaoke',
					'5046' => 'Soundproof-rooms',
					'5020' => 'Hiking',
					'5002' => 'Bikes available (free)',
					'62' => 'Nightclub/DJ',
					'262' => 'Shared Kitchen',
					'5009' => 'Shared lounge/TV area',
					'60' => 'Evening Entertainment',
					'5061' => 'Board games/puzzles',
					'5094' => 'Pub crawls',
					'5096' => 'Movie nights',
					'5097' => 'Walking tours',
					'5098' => 'Bike tours',
					'5100' => 'Happy hour',
					'5103' => 'Live music/performance',
				],
			],
			[
				'group' => 'Top amenities - Geography dependent',
				'list' 	=> [
					'5015' => 'Beach',
					'272' => 'Skiing',
				],
			],
			[
				'group' => 'Sustainability codes',
				'list' 	=> [
					'316' => 'Electric vehicle charging station',
					'5156' => 'Property removed all, or never offered, single-use plastic toiletries.',
					'5157' => 'Towels Changed Upon Request',
					'5168' => 'Property removed all, or never offered, plastic stirrers.',
					'5169' => 'Property removed all, or never offered, plastic straws.',
					'5170' => 'Property removed all, or never offered, plastic cups.',
					'5171' => 'Property removed all, or never offered, plastic water bottles.',
					'5172' => 'Property removed all, or never offered, plastic bottles for non-water drinks.',
					'5173' => 'Property removed all, or never offered, plastic cutlery and tableware.',
					'5174' => 'Keycard for room electricity',
					'5175' => 'Opt-out from daily room cleaning',
					'5176' => 'Refillable water stations',
					'5177' => 'Bike rental',
					'5178' => 'Bike parking',
					'5204' => 'Wild (non-domesticated) animals are not harmed at the property',
					'5205' => 'Recycling bins are available to guests and waste is recycled',
					'5206' => 'At least 80% of food is sourced from your region',
					'5207' => 'At least 80% of lighting uses energy-efficient LED bulbs',
					'5208' => 'Only using water-efficient toilets',
					'5209' => 'Only using water-efficient showers',
					'5210' => 'All windows are double-glazed',
					'5211' => 'Food waste policy in place that includes education, food waste prevention, reduction, recycling, and disposal',
					'5212' => 'Investing a percentage of revenue back into community projects or sustainability projects',
					'5213' => 'Compensate for at least 10% of total annual carbon emissions by purchasing certified carbon offsets',
					'5214' => 'Tours and activities organized by local guides and businesses offered',
					'5215' => 'Vegetarian options available at the property',
					'5216' => 'Vegan options available at the property',
					'5217' => 'Green spaces such as gardens/rooftop gardens on the property',
					'5218' => 'At least 80% of the food provided is organic',
					'5219' => '100% renewable electricity is used throughout the property',
					'5220' => 'Local artists are offered a platform to display their talents',
					'5221' => 'Provide guests with information regarding local ecosystems, heritage and culture, as well as visitor etiquette',
				],
			],
			[
				'group' => 'General amenities',
				'list' 	=> [
					'1' => '24-hour front desk',
					'5' => 'Air conditioning',
					'7' => 'ATM on site',
					'8' => 'Baby sitting',
					'9' => 'BBQ/Picnic area',
					'14' => 'Library',
					'15' => 'Car rental',
					'16' => 'Casino',
					'22' => 'Concierge desk',
					'26' => 'Currency exchange',
					'33' => 'Elevators',
					'35' => 'Fitness Center',
					'36' => 'Express Check-in/Check-out',
					'41' => 'Airport shuttle (free)',
					'44' => 'Game room',
					'45' => 'Souvenir/Gift Shop',
					'49' => 'Heated pool',
					'50' => 'Housekeeping - daily',
					'53' => 'Indoor parking',
					'54' => 'Indoor pool',
					'55' => 'Jacuzzi (Hot Tub)',
					'60' => 'Evening Entertainment',
					'61' => 'Massage',
					'62' => 'Nightclub/DJ',
					'66' => 'Outdoor pool',
					'76' => 'Restaurant',
					'77' => 'Room service',
					'78' => 'Safe',
					'79' => 'Sauna',
					'81' => 'Shoeshine',
					'83' => 'Solarium',
					'86' => 'Turkish/ Steam Bath',
					'91' => 'Tour Desk',
					'96' => 'Dry cleaning',
					'97' => 'Valet parking',
					'98' => 'Vending Machine(snacks)',
					'116' => 'Accessible parking',
					'122' => 'Shops (on site)',
					'149' => 'Grocery shopping service available',
					'165' => 'Bar',
					'168' => 'Laundry',
					'173' => 'Breakfast',
					'186' => 'Street parking',
					'193' => 'Playground',
					'197' => 'Lockers',
					'198' => 'Non-smoking rooms',
					'202' => 'Bicycle rentals',
					'228' => 'Business center',
					'230' => 'Secured parking',
					'233' => 'Tennis court',
					'234' => 'Water Sport Facilities (on site)',
					'236' => 'Golf Course (within 2 miles)',
					'237' => 'Horseback riding',
					'239' => 'Beachfront',
					'242' => 'Heating',
					'262' => 'Shared Kitchen',
					'269' => 'Meeting/Banquet Facilities',
					'272' => 'Skiing',
					'282' => 'Airport shuttle service (surcharge)',
					'283' => 'Baggage Storage',
					'292' => 'Newspaper',
					'310' => 'Hypoallergenic room available',
					'312' => 'All Spaces Non-Smoking (public and private)',
					'316' => 'Electric vehicle charging station',
					'327' => 'Events ticket service',
					'342' => 'Snack bar',
				],
			],
			[
				'group' => 'Booking.com Extended OTA Codes',
				'list' 	=> [
					'5000' => 'Breakfast in the room',
					'5001' => 'Public transport tickets',
					'5002' => 'Bikes available (free)',
					'5003' => 'Outdoor furniture',
					'5004' => 'Outdoor fireplace',
					'5005' => 'Garden',
					'5006' => 'Terrace',
					'5007' => 'Sun terrace',
					'5008' => 'Chapel/shrine',
					'5009' => 'Shared lounge/TV area',
					'5010' => 'Ironing service',
					'5011' => 'Trouser press',
					'5012' => 'Designated smoking area',
					'5013' => 'Pet basket',
					'5014' => 'Pet bowls',
					'5015' => 'Beach',
					'5016' => 'Bowling',
					'5017' => 'Darts',
					'5018' => 'Fishing',
					'5020' => 'Hiking',
					'5021' => 'Minigolf',
					'5022' => 'Snorkeling',
					'5023' => 'Squash',
					'5024' => 'Windsurfing',
					'5025' => 'Billiard',
					'5026' => 'Table tennis',
					'5027' => 'Canoeing',
					'5028' => 'Ski-to-door access',
					'5029' => 'Diving',
					'5030' => 'Tennis equipment',
					'5031' => 'Badminton equipment',
					'5032' => 'Cycling',
					'5033' => 'Ski storage',
					'5034' => 'Ski school',
					'5035' => 'Ski equipment hire (on site)',
					'5036' => 'Ski pass vendor',
					'5037' => 'Private beach area',
					'5039' => 'Rooms/Facilities for Disabled',
					'5040' => 'Hair dresser-beautician',
					'5041' => 'Family Rooms',
					'5042' => 'Viproom facilities',
					'5043' => 'Bridal Suite',
					'5044' => 'Spa & Wellness Centre',
					'5045' => 'Karaoke',
					'5046' => 'Soundproof-rooms',
					'5047' => 'Packed Lunches',
					'5048' => 'Ticket service',
					'5049' => 'Entertainment Staff',
					'5050' => 'Private Check-in/Check-out',
					'5051' => 'Special Diet Menus (on request)',
					'5052' => 'Vending Machine (drinks)',
					'5053' => 'Hot Spring Bath',
					'5054' => 'Kids\' club',
					'5055' => 'Minimarket on site',
					'5056' => 'Water park',
					'5057' => 'Adult only',
					'5058' => 'Open-air bath',
					'5059' => 'Public bath',
					'5060' => 'Water slide',
					'5061' => 'Board games/puzzles',
					'5062' => 'Book/DVD/Music library for children',
					'5063' => 'Indoor play area',
					'5064' => 'Kids\' outdoor play equipment',
					'5065' => 'Baby safety gates',
					'5066' => 'Children television networks',
					'5067' => 'Kid meals',
					'5068' => 'Kid-friendly buffet',
					'5069' => 'Pool towels',
					'5070' => 'Wine/Champagne',
					'5071' => 'Bottle of water',
					'5072' => 'Fruits',
					'5073' => 'Chocolate/Cookies',
					'5074' => 'Strollers',
					'5075' => 'On-site coffee house',
					'5076' => 'Sun loungers or beach chairs',
					'5077' => 'Sun umbrellas',
					'5078' => 'Picnic area',
					'5079' => 'Beauty Services',
					'5080' => 'Spa Facilities',
					'5081' => 'Steam room',
					'5082' => 'Spa lounge/relaxation area',
					'5083' => 'Foot bath',
					'5084' => 'Spa/wellness packages',
					'5085' => 'Massage chair',
					'5086' => 'Fitness',
					'5087' => 'Yoga classes',
					'5088' => 'Fitness classes',
					'5089' => 'Personal trainer',
					'5090' => 'Fitness/spa locker rooms',
					'5091' => 'Kids pool',
					'5092' => 'Shuttle Service',
					'5093' => 'Temporary art galleries',
					'5094' => 'Pub crawls',
					'5095' => 'Stand-up comedy',
					'5096' => 'Movie nights',
					'5097' => 'Walking tours',
					'5098' => 'Bike tours',
					'5099' => 'Themed dinner nights',
					'5100' => 'Happy hour',
					'5101' => 'Tour or class about local culture',
					'5102' => 'Cooking class',
					'5103' => 'Live music/performance',
					'5104' => 'Live sports events (broadcast)',
					'5105' => 'Archery',
					'5106' => 'Aerobics',
					'5107' => 'Bingo',
					'5108' => 'Ski Shuttle',
					'5109' => 'Outdoor Swimming Pool (all year)',
					'5110' => 'Outdoor Swimming Pool (seasonal)',
					'5111' => 'Indoor Swimming Pool (all year)',
					'5112' => 'Indoor Swimming Pool (seasonal)',
					'5113' => 'Swimming pool toys',
					'5114' => 'Rooftop pool',
					'5115' => 'Infinity pool',
					'5116' => 'Pool with view',
					'5117' => 'Salt-water pool',
					'5118' => 'Plunge pool',
					'5119' => 'Pool bar',
					'5120' => 'Shallow end pool',
					'5121' => 'Pool cover',
					'5122' => 'Fence around pool',
					'5123' => 'Airport Shuttle (surcharge)',
					'5124' => 'Property is wheel chair accessible',
					'5125' => 'Toilet with grab rails',
					'5126' => 'Higher level toilet',
					'5127' => 'Low bathroom sink',
					'5128' => 'Bathroom emergency pull cord',
					'5129' => 'Visual aids: Braille',
					'5130' => 'Visual aids: Tactile Signs',
					'5131' => 'Auditory Guidance',
					'5132' => 'Back massage',
					'5133' => 'Neck massage',
					'5134' => 'Foot massage',
					'5135' => 'Couples massage',
					'5136' => 'Head massage',
					'5137' => 'Hand massage',
					'5138' => 'Full body massage',
					'5139' => 'Facial treatments',
					'5140' => 'Waxing services',
					'5141' => 'Make up services',
					'5142' => 'Hair treatments',
					'5143' => 'Manicure',
					'5144' => 'Pedicure',
					'5145' => 'Hair cut',
					'5146' => 'Hair colouring',
					'5147' => 'Hair styling',
					'5148' => 'Body Treatments',
					'5149' => 'Body scrub',
					'5150' => 'Body wrap',
					'5151' => 'Light therapy',
					'5152' => 'Shuttle Service (free)',
					'5153' => 'Shuttle Service (surcharge)',
					'5154' => 'Swimming pool',
					'5156' => 'Property removed all, or never offered, single-use plastic toiletries.',
					'5157' => 'Towels Changed Upon Request',
					'5158' => '24-hour security',
					'5159' => 'Security alarm',
					'5160' => 'Smoke alarms',
					'5161' => 'CCTV in common areas',
					'5162' => 'CCTV outside property',
					'5163' => 'Fire extinguishers',
					'5164' => 'Key access',
					'5165' => 'Key card access',
					'5166' => 'Carbon monoxide detector',
					'5167' => 'Carbon monoxide source',
					'5168' => 'Property removed all, or never offered, plastic stirrers.',
					'5169' => 'Property removed all, or never offered, plastic straws.',
					'5170' => 'Property removed all, or never offered, plastic cups.',
					'5171' => 'Property removed all, or never offered, plastic water bottles.',
					'5172' => 'Property removed all, or never offered, plastic bottles for non-water drinks.',
					'5173' => 'Property removed all, or never offered, plastic cutlery and tableware.',
					'5174' => 'Keycard for room electricity',
					'5175' => 'Opt-out from daily room cleaning',
					'5176' => 'Refillable water stations',
					'5177' => 'Bike rental',
					'5178' => 'Bike parking',
					'5179' => 'Use of cleaning chemicals that are effective against Coronavirus',
					'5180' => 'Linens, towels and laundry washed in accordance with local authorities guidelines',
					'5181' => 'Guest accommodation is disinfected between stays',
					'5182' => 'Guest accommodation sealed after cleaning',
					'5183' => 'Physical distancing in dining areas',
					'5184' => 'In-room dining options available',
					'5185' => 'Staff follow all safety protocols as directed by local authorities',
					'5186' => 'Shared stationery such as printed menus, magazines, pens, and paper removed',
					'5187' => 'Hand sanitiser in guest accommodation and key areas',
					'5188' => 'Process in place to check health of guests',
					'5189' => 'First aid kit available',
					'5190' => 'Contactless check-in/out',
					'5191' => 'Cashless payment available',
					'5192' => 'Physical distancing following guidelines from local authorities',
					'5193' => 'Mobile app for room service',
					'5194' => 'Screens or physical barriers placed between staff and guests in appropriate areas',
					'5195' => 'Property is cleaned by professional cleaning companies',
					'5196' => 'All plates, cutlery, glasses and other tableware have been sanitised',
					'5197' => 'Guest accommodation cleaning can be avoided on request',
					'5198' => 'Invoices Provided',
					'5199' => 'Breakfast takeaway containers',
					'5200' => 'Delivered food is securely covered',
					'5201' => 'Access to health care professionals',
					'5202' => 'Thermometers for guests provided by property',
					'5203' => 'Face masks for guests available',
					'5204' => 'Wild (non-domesticated) animals are not harmed at the property',
					'5205' => 'Recycling bins are available to guests and waste is recycled',
					'5206' => 'At least 80% of food is sourced from your region',
					'5207' => 'At least 80% of lighting uses energy-efficient LED bulbs',
					'5208' => 'Only using water-efficient toilets',
					'5209' => 'Only using water-efficient showers',
					'5210' => 'All windows are double-glazed',
					'5211' => 'Food waste policy in place that includes education, food waste prevention, reduction, recycling, and disposal',
					'5212' => 'Investing a percentage of revenue back into community projects or sustainability projects',
					'5213' => 'Compensate for at least 10% of total annual carbon emissions by purchasing certified carbon offsets',
					'5214' => 'Tours and activities organized by local guides and businesses offered',
					'5215' => 'Vegetarian options available at the property',
					'5216' => 'Vegan options available at the property',
					'5217' => 'Green spaces such as gardens/rooftop gardens on the property',
					'5218' => 'At least 80% of the food provided is organic',
					'5219' => '100% renewable electricity is used throughout the property',
					'5220' => 'Local artists are offered a platform to display their talents',
					'5221' => 'Provide guests with information regarding local ecosystems, heritage and culture, as well as visitor etiquette',
					'6000' => 'Lunch',
					'6001' => 'Dinner',
				],
			],
		];

		if ($no_group) {
			// extract an associative list of amenity codes from each group
			$hacs = [];

			foreach ($hac_list as $hac_typed) {
				// $hacs = array_merge($hacs, $hac_typed['list']);
				$hacs = $hacs + $hac_typed['list'];
			}

			return $hacs;
		}

		// whole list with groups
		return $hac_list;
	}

	/**
	 * Returns a list of "reserved" hotel amenity codes, in the sense that they
	 * require additional information, such as a price (breakfast, lunch, dinner).
	 * 
	 * @return 	array
	 */
	public static function getReservedHotelAmenityCodes()
	{
		return [
			// breakfast
			'173',
			// lunch
			'6000',
			// dinner
			'6001',
		];
	}

	/**
	 * Returns an associative list of contact profile types.
	 * 
	 * @return 	array
	 */
	public static function getContactProfileTypes()
	{
		return [
			'PhysicalLocation' => 'Property physical location',
			'general' => 'Primary point of contact for the property',
			'invoices' => 'Contact for accounts payable',
			'contract' => 'Contact for contract matters',
			'reservations' => 'Contact for reservations',
			'availability' => 'Contact for questions about availability',
			'site_content' => 'Contact for photos, descriptions, and other website content',
			'parity' => 'Contact for pricing and rate matters',
			'requests' => 'Contact for special requests',
			'central_reservations' => 'Contact for central reservations. Applies to properties that manage reservations from another location',
		];
	}

	/**
	 * Returns an associative list of property class types (PCT), or the corresponding
	 * PCT name if a code is provided, alternatively the group to which the code belongs.
	 * 
	 * @param 	string 	$code 		optional hotel category code to identify.
	 * @param 	bool 	$get_type 	true to query the group from a category code.
	 * 
	 * @return 	array|string 		full list or string with the queried category code.
	 */
	public static function getPropertyClassTypes($code = '', $get_type = false)
	{
		$pct_list = [
			[
				'category' => 'OTA 2014B',
				'list' 	   => [
					'30' => 'Resort',
					'19' => 'Hostel',
					'20' => 'Hotel',
					'21' => 'Inn',
					'22' => 'Lodge',
					'27' => 'Motel',
				],
			],
			[
				'category' => 'Extended',
				'list' 	   => [
					'5003' => 'Love Hotel',
					'5005' => 'Japanese-style Business Hotel',
					'5008' => 'Capsule Hotel',
				],
			],
			[
				'category' => 'Home properties',
				'list' 	   => [
					'3' => 'Apartment',
					'4' => 'Bed and Breakfast',
					'6' => 'Camping',
					'7' => 'Chalet',
					'12' => 'Boat',
					'15' => 'Farm stay',
					'33' => 'Tented',
					'35' => 'Villa',
					'40' => 'Guest House',
					'5000' => 'ApartHotel',
					'5001' => 'Riad',
					'5002' => 'Ryokan',
					'5004' => 'Homestay',
					'5006' => 'Holiday Home',
					'5007' => 'Country house',
					'5009' => 'Holiday Park',
				],
			],
		];

		if ($code) {
			// find the given code
			foreach ($pct_list as $pct_group) {
				foreach ($pct_group['list'] as $cat_code => $pct) {
					if ($cat_code == $code) {
						// code found
						return $get_type ? $pct_group['category'] : $pct;
					}
				}
			}

			// return an empty string in case the given category code is not found
			return '';
		}

		// return the whole list
		return $pct_list;
	}

	/**
	 * Returns an associative list of fee tax type codes.
	 * 
	 * @param 	string 	$policy_type 	optional filter for "tax", "fee" or "children" to grab only this type of codes.
	 * 
	 * @return 	array
	 */
	public static function getFeeTaxTypeCodes($policy_type = '')
	{
		$fee_tax_type_codes = [
			'3' => 'City Tax',
			'12' => 'Resort fee',
			'13' => 'TAX',
			'14' => 'Service charge',
			'18' => 'Tourism fee',
			'35' => 'Goods and services tax',
			'36' => 'VAT (Value Added Tax)',
			'37' => 'Crib fee',
			'38' => 'Rollaway fee',
			'44' => 'Extra child charge',
			'46' => 'Government tax',
			'55' => 'Destination fee',
			'5000' => 'Environment fee',
			'5001' => 'Spa tax',
			'5002' => 'Hot spring tax',
			'5003' => 'Municipality fee',
			'5004' => 'Residential tax',
			'5005' => 'Public transit day ticket',
			'5006' => 'Heritage charge',
			'5007' => 'Sauna/fitness facilities tax',
			'5008' => 'Local council tax',
			'5009' => 'Cleaning fee',
			'5010' => 'Towel charge',
			'5011' => 'Electricity fee',
			'5012' => 'Bed linen fee',
			'5013' => 'Gas fee',
			'5014' => 'Oil fee',
			'5015' => 'Wood fee',
			'5016' => 'Water usage fee',
			'5017' => 'Transfer fee',
			'5018' => 'Linen package fee',
			'5019' => 'Heating fee',
			'5020' => 'Air conditioning fee',
			'5021' => 'Kitchen linen fee',
			'5022' => 'Housekeeping fee',
			'5023' => 'Airport shuttle fee',
			'5024' => 'Shuttle boat fee',
			'5025' => 'Sea plane fee',
			'5026' => 'Ski pass',
			'5027' => 'Final cleaning fee',
			'5028' => 'Wristband fee',
			'5029' => 'Visa support fee',
			'5030' => 'Water park fee',
			'5031' => 'Club card fee',
			'5032' => 'Conservation fee',
			'5034' => 'Pet fee',
			'5035' => 'Internet fee',
			'5036' => 'Parking fee',
		];

		// these codes belong to the type "TaxPolicy", the others to "FeePolicy"
		$tax_type_codes = [
			'3',
			'13',
			'35',
			'36',
			'46',
			'5001',
			'5002',
			'5004',
			'5007',
			'5008',
		];

		// these codes refer to children fee policies
		$children_type_codes = [
			'37',
			'38',
			'44',
		];

		if (!strcasecmp($policy_type, 'tax')) {
			// get only the codes that belong to the type "TaxPolicy"
			return array_filter($fee_tax_type_codes, function($code) use ($tax_type_codes) {
				return in_array($code, $tax_type_codes);
			}, ARRAY_FILTER_USE_KEY);
		}

		if (!strcasecmp($policy_type, 'fee')) {
			// get only the codes that belong to the type "FeePolicy"
			return array_filter($fee_tax_type_codes, function($code) use ($tax_type_codes) {
				return !in_array($code, $tax_type_codes);
			}, ARRAY_FILTER_USE_KEY);
		}

		if (!strcasecmp($policy_type, 'children')) {
			// get only the codes that apply to children fee policies
			return array_filter($fee_tax_type_codes, function($code) use ($children_type_codes) {
				return in_array($code, $children_type_codes);
			}, ARRAY_FILTER_USE_KEY);
		}

		// get the whole list
		return $fee_tax_type_codes;
	}

	/**
	 * Returns an associative list of charge type codes to be used for the charge frequency.
	 * 
	 * @return 	array
	 */
	public static function getChargeTypeCodes()
	{
		return [
			'12' => 'Per stay',
			'19' => 'Per night',
			'20' => 'Per person per stay',
			'21' => 'Per person per night',
			'1' => 'Per day',
			'2' => 'Per hour',
			'10' => 'Per minute',
			'17' => 'Per week',
			'5000' => 'Applicable, charges may vary',
			'5001' => 'Charges are applicable',
			'5002' => 'Charges may apply',
		];
	}

	/**
	 * Returns an associative list of charge type code comments.
	 * 
	 * @return 	array
	 */
	public static function getChargeTypeCodeComments()
	{
		return [
			'12' => 'All except internet service',
			'19' => 'All except parking service, internet service',
			'20' => 'All except parking service, internet service',
			'21' => 'All except parking service, internet service',
			'1' => 'Only parking service, internet service',
			'2' => 'Only parking service, internet service',
			'10' => 'Only internet service',
			'17' => 'Only parking service',
			'5000' => 'This can be used only for electricity fee, gas fee, oil fee, water fee and wood fee',
			'5001' => 'This can be used only for parking service, internet service',
			'5002' => 'This can be used only for parking services',
		];
	}

	/**
	 * Returns an associative list of meal plan type codes.
	 * 
	 * @return 	array
	 */
	public static function getMealPlanCodes()
	{
		return [
			'1' => 'All inclusive',
			'19' => 'Breakfast',
			'21' => 'Lunch',
			'22' => 'Dinner',
			'2' => 'American',
			'3' => 'Bed and breakfast',
			'4' => 'Buffet breakfast',
			'5' => 'Caribbean breakfast',
			'6' => 'Continental breakfast',
			'7' => 'English breakfast',
			'8' => 'European plan',
			'9' => 'Family plan',
			'10' => 'Full board',
			'11' => 'Full breakfast',
			'12' => 'Half board/modified American plan',
			'14' => 'Room only',
			'15' => 'Self catering',
			'16' => 'Bermuda',
			'17' => 'Dinner bed and breakfast plan',
			'18' => 'Family American',
			'20' => 'Modified',
			'23' => 'Breakfast and lunch',
			'24' => 'Lunch and Dinner',
		];
	}

	/**
	 * Returns a list of included meal plans from the given meal plan code.
	 * 
	 * @param 	string 	$mp_code 	The meal plan code identifier.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.2
	 */
	public static function getIncludedMeals($mp_code)
	{
		$meals_included = [
			'1' => [
				'All inclusive',
				'Breakfast',
				'Lunch',
				'Dinner',
			],
			'19' => [
				'Breakfast',
			],
			'21' => [
				'Lunch',
			],
			'22' => [
				'Dinner',
			],
			'2' => [
				'American',
				'Breakfast',
				'Lunch',
				'Dinner',
			],
			'3' => [
				'Bed & breakfast',
				'Breakfast',
			],
			'4' => [
				'Buffet breakfast',
				'Breakfast',
			],
			'5' => [
				'Caribbean breakfast',
				'Breakfast',
			],
			'6' => [
				'Continental breakfast',
				'Breakfast',
			],
			'7' => [
				'English breakfast',
				'Breakfast',
			],
			'8' => [
				'European plan',
				'No meals',
			],
			'9' => [
				'Family plan',
				'No meals',
			],
			'10' => [
				'Full board',
				'Breakfast',
				'Lunch',
				'Dinner',
			],
			'11' => [
				'Full breakfast',
				'Breakfast',
			],
			'12' => [
				'Half board/modified American plan',
				'Breakfast',
				'Dinner',
			],
			'14' => [
				'Room only',
				'No meals',
			],
			'15' => [
				'Self catering',
				'No meals',
			],
			'16' => [
				'Bermuda',
				'Breakfast',
			],
			'17' => [
				'Dinner bed and breakfast plan',
				'Breakfast',
				'Dinner',
			],
			'18' => [
				'Family American',
				'No meals',
			],
			'20' => [
				'Modified',
				'Breakfast',
				'Dinner',
			],
			'23' => [
				'Breakfast & lunch',
				'Breakfast',
				'Lunch',
			],
			'24' => [
				'Lunch & Dinner',
				'Lunch',
				'Dinner',
			],
		];

		return $meals_included[$mp_code] ?? [];
	}

	/**
	 * Returns an associative list of cancellation policy codes.
	 * 
	 * @param 	string 	$code 		optional cancellation policy code to identify.
	 * @param 	bool 	$get_group 	true to query the group from a policy code.
	 * 
	 * @return 	array|string 		full list or string with the queried policy code.
	 */
	public static function getCancellationPolicyCodes($code = '', $get_group = false)
	{
		$bccp_list = [
			[
				'group' => 'Policy with 100% penalty after reservation/deadline (Non-refundable)',
				'list' 	=> [
					'1' => 'The guest will be charged the total price if they cancel.',
				],
			],
			[
				'group' => 'Policies with 100% penalty after deadline',
				'list' 	=> [
					'12' => 'The guest can cancel free of charge until 42 days before arrival. The guest will be charged the total price if they cancel in the 42 days before arrival.',
					'13' => 'The guest can cancel free of charge until 28 days before arrival. The gues will be charged the total price of the reservation if they cancel in the 28 days before arrival.',
					'14' => 'The guest can cancel free of charge until 14 days before arrival. The guest will be charged the total price if they cancel in the 14 days before arrival.',
					'15' => 'The guest can cancel free of charge until 7 days before arrival. The guest will be charged the total price if they cancel in the 7 days before arrival.',
					'16' => 'The guest can cancel free of charge until 5 days before arrival. The guest will be charged the total price if they cancel in the 5 days before arrival.',
					'29' => 'The guest can cancel free of charge until 2 days before arrival. The guest will be charged the total price if they cancel in the 2 days before arrival.',
					'31' => 'The guest can cancel free of charge until 3 days before arrival. The guest will be charged the total price if they cancel in the 3 days before arrival.',
					'32' => 'The guest can cancel free of charge until 00:00 on the day of arrival. The guest will be charged the total price of the reservation if they cancel after 00:00 on the day of arrival.',
					'38' => 'The guest can cancel free of charge until 1 day before arrival. The guest will be charged the total price if they cancel within 1 day before arrival.',
					'74' => 'The guest can cancel free of charge until 30 days before arrival. The guest will be charged the total price if they cancel in the 30 days before arrival.',
					'121' => 'The guest can cancel free of charge until 60 days before arrival. The guest will be charged the total price if they cancel in the 60 days before arrival.',
					'172' => 'The guest can cancel free of charge until 15 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 15 days before arrival.',
					'178' => 'The guest can cancel free of charge until 10 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 10 days before arrival.',
					'202' => 'The guest can cancel free of charge until 20 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 20 days before arrival.',
					'205' => 'The guest can cancel free of charge until 21 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 21 days before arrival.',
					'231' => 'The guest can cancel free of charge until 31 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 31 days before arrival.',
					'237' => 'The guest can cancel free of charge until 45 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 45 days before arrival.',
					'242' => 'The guest can cancel free of charge until 4 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 4 days before arrival.',
					'249' => 'The guest can cancel free of charge until 6 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 6 days before arrival.',
					'261' => 'The guest can cancel free of charge until 8 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 8 days before arrival.',
					'267' => 'The guest can cancel free of charge until 90 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 90 days before arrival.',
					'555' => 'The guest can cancel free of charge until 9 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 9 days before arrival.',
					'556' => 'The guest can cancel free of charge until 11 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 11 days before arrival.',
					'557' => 'The guest can cancel free of charge until 12 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 12 days before arrival.',
					'558' => 'The guest can cancel free of charge until 13 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 13 days before arrival.',
					'570' => 'The guest can cancel free of charge until 16 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 16 days before arrival.',
					'571' => 'The guest can cancel free of charge until 17 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 17 days before arrival.',
					'572' => 'The guest can cancel free of charge until 18 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 18 days before arrival.',
					'573' => 'The guest can cancel free of charge until 19 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 19 days before arrival.',
				],
			],
			[
				'group' => 'Policies with 50% penalty after deadline',
				'list' 	=> [
					'52' => 'The guest can cancel free of charge until 1 day before arrival. The guest will be charged 50% of the total price if they cancel within 1 day before arrival.',
					'55' => 'The guest can cancel free of charge until 2 days before arrival. The guest will be charged 50% of the total price if they cancel in the 2 days before arrival.',
					'58' => 'The guest can cancel free of charge until 3 days before arrival. The guest will be charged 50% of the total price if they cancel in the 3 days before arrival.',
					'62' => 'The guest can cancel free of charge until 5 days before arrival. The guest will be charged 50% of the total price if they cancel in the 5 days before arrival.',
					'65' => 'The guest can cancel free of charge until 7 days before arrival. The guest will be charged 50% of the total price if they cancel in the 7 days before arrival.',
					'68' => 'The guest can cancel free of charge until 14 days before arrival. The guest will be charged 50% of the total price if they cancel in the 14 days before arrival.',
					'72' => 'The guest can cancel free of charge until 30 days before arrival. The guest will be charged 50% of the total price if they cancel in the 30 days before arrival.',
					'115' => 'The guest can cancel free of charge until 42 days before arrival. The guest will be charged 50% of the total price if they cancel in the 42 days before arrival.',
					'119' => 'The guest can cancel free of charge until 60 days before arrival. The guest will be charged 50% of the total price if they cancel in the 60 days before arrival.',
					'181' => 'The guest can cancel free of charge until 10 days before arrival. The guest will be charged 50% of the total price if they cancel in the 10 days before arrival.',
					'196' => 'The guest can cancel free of charge until 15 days before arrival. The guest will be charged 50% of the total price if they cancel in the 15 days before arrival.',
					'203' => 'The guest can cancel free of charge until 20 days before arrival. The guest will be charged 50% of the total price if they cancel in the 20 days before arrival.',
					'208' => 'The guest can cancel free of charge until 21 days before arrival. The guest will be charged 50% of the total price if they cancel in the 21 days before arrival.',
					'263' => 'The guest can cancel free of charge until 8 days before arrival. The guest will be charged 50% of the total price if they cancel in the 8 days before arrival.',
					'559' => 'The guest can cancel free of charge until 4 days before arrival. The guest will be charged 50% of the total price if they cancel in the 4 days before arrival.',
					'560' => 'The guest can cancel free of charge until 6 days before arrival. The guest will be charged 50% of the total price if they cancel in the 6 days before arrival.',
					'561' => 'The guest can cancel free of charge until 90 days before arrival. The guest will be charged 50% of the total price if they cancel in the 90 days before arrival.',
					'565' => 'The guest can cancel free of charge until 28 days before arrival. The guest will be charged 50% of the total price if they cancel in the 28 days before arrival.',
					'567' => 'The guest can cancel free of charge until 31 days before arrival. The guest will be charged 50% of the total price if they cancel in the 31 days before arrival.',
					'569' => 'The guest can cancel free of charge until 45 days before arrival. The guest will be charged 50% of the total price if they cancel in the 45 days before arrival.',
				],
			],
			[
				'group' => 'Policy with 50% penalty after reservation',
				'list' 	=> [
					'168' => 'The guest will be charged 50% of the total price if they cancel their booking after reservation.',
				],
			],
			[
				'group' => 'Policies with penalty after deadline in nights',
				'list' 	=> [
					'30' => 'The guest can cancel free of charge until 14 days before arrival. The guest will be charged the first night if they cancel in the 14 days before arrival.',
					'33' => 'The guest can cancel free of charge until 3 days before arrival. The guest will be charged the first night if they cancel in the 3 days before arrival.',
					'34' => 'The guest can cancel free of charge until 2 days before arrival. The guest will be charged the first night if they cancel in the 2 days before arrival.',
					'36' => 'The guest can cancel free of charge until 1 day before arrival. The guest will be charged the first night if they cancel within 1 day before arrival.',
					'37' => 'The guest can cancel free of charge until 7 days before arrival. The guest will be charged the first night if they cancel in the 7 days before arrival.',
					'60' => 'The guest can cancel free of charge until 5 days before arrival. The guest will be charged the first night if they cancel in the 5 days before arrival.',
					'70' => 'The guest can cancel free of charge until 30 days before arrival. The guest will be charged the first night if they cancel in the 30 days before arrival.',
					'113' => 'The guest can cancel free of charge until 42 days before arrival. The guest will be charged the first night if they cancel in the 42 days before arrival.',
					'117' => 'The guest can cancel free of charge until 60 days before arrival. The guest will be charged the first night if they cancel in the 60 days before arrival.',
					'173' => 'The guest can cancel free of charge until 4 days before arrival. The guest will be charged the first night if they cancel in the 4 days before arrival.',
					'179' => 'The guest can cancel free of charge until 10 days before arrival. The guest will be charged the first night if they cancel in the 10 days before arrival.',
					'192' => 'The guest can cancel free of charge until 15 days before arrival. The guest will be charged the first night if they cancel in the 15 days before arrival.',
					'206' => 'The guest can cancel free of charge until 21 days before arrival. The guest will be charged the first night if they cancel in the 21 days before arrival.',
					'250' => 'The guest can cancel free of charge until 6 days before arrival. The guest will be charged the first night if they cancel in the 6 days before arrival.',
					'262' => 'The guest can cancel free of charge until 8 days before arrival. The guest will be charged the cost of the first night if they cancel in the 8 days before arrival.',
					'268' => 'The guest can cancel free of charge until 90 days before arrival. The guest will be charged the cost of the first night if they cancel in the 90 days before arrival.',
					'548' => 'The guest can cancel free of charge until 4:00 PM one day before arrival. The guest will be charged the cost of the first night if they cancel after 4:00 PM one day before arrival.',
					'549' => 'The guest can cancel free of charge until 6:00 PM one day before arrival. The guest will be charged the cost of the first night if they cancel after 6:00 PM one day before arrival.',
					'563' => 'The guest can cancel free of charge until 20 days before arrival. The guest will be charged the cost of the first night if they cancel in the 20 days before arrival.',
					'564' => 'The guest can cancel free of charge until 28 days before arrival. The guest will be charged the cost of the first night if they cancel in the 28 days before arrival.',
					'566' => 'The guest can cancel free of charge until 31 days before arrival. The guest will be charged the cost of the first night if they cancel in the 31 days before arrival.',
					'568' => 'The guest can cancel free of charge until 45 days before arrival. The guest will be charged the cost of the first night if they cancel in the 45 days before arrival.',
				],
			],
			[
				'group' => 'Fully flexible policy with no penalty',
				'list' 	=> [
					'152' => 'The guest can cancel free of charge at any time. No prepayment is needed.',
				],
			],
			[
				'group' => 'Policy with penalty after reservation in nights',
				'list' 	=> [
					'166' => 'The guest will be charged the cost of the first night if they cancel after reservation.',
				],
			],
			[
				'group' => 'Policies on the day of arrival',
				'list' 	=> [
					'35' => 'The guest can cancel free of charge until 12:00 AM on the day of arrival. The guest will be charged the cost of the first night if they cancel after 12:00 AM on the day of arrival.',
					'41' => 'The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged the total price if they cancel after 6 pm on the day of arrival.',
					'42' => 'The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged the first night if they cancel after 6 pm on the day of arrival.',
					'43' => 'The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged the first night if they cancel after 2 pm on the day of arrival.',
					'45' => 'The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged 50% of the total price if they cancel after 2 pm on the day of arrival.',
					'47' => 'The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged the total price if they cancel after 2 pm on the day of arrival.',
					'49' => 'The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged 50% of the total price if they cancel after 6 pm on the day of arrival.',
					'163' => 'The guest can cancel free of charge until 4:00 PM on the day of arrival. The guest will be charged the cost of the first night if they cancel after 4:00 PM on the day of arrival.',
					'170' => 'The guest can cancel free of charge until 7:00 PM on the day of arrival. The guest will be charged the cost of the first night if they cancel after 7:00 PM on the day of arrival.',
					'182' => 'The guest can cancel free of charge until 12:00 PM on the day of arrival. The guest will be charged the cost of the first night if they cancel after 12:00 PM on the day of arrival.',
					'183' => 'The guest can cancel free of charge until 01:00 PM on the day of arrival. The guest will be charged the cost of the first night if they cancel after 01:00 PM on the day of arrival.',
					'200' => 'The guest can cancel free of charge until 4:00 PM on the day of arrival. The guest will be charged the total price of the reservation if you cancel after 4:00 PM on the day of arrival.',
					'562' => 'The guest can cancel free of charge until 4:00 PM on the day of arrival. The guest will be charged 50% of the total price if they cancel after 4:00 PM on the day of arrival.',
				],
			],
			[
				'group' => 'Policies only for Germany',
				'list' 	=> [
					'140' => 'The guest will be charged a prepayment of 90% of the total price after reservation.',
					'141' => 'The guest will be charged a prepayment of 90% of the total price after 6:00 PM on the day of arrival.',
					'143' => 'The guest will be charged a prepayment of 90% of the total price within 1 day before arrival.',
					'144' => 'The guest will be charged a prepayment of 90% of the total price in the 2 days before arrival.',
					'145' => 'The guest will be charged a prepayment of 90% of the total price in the 3 days before arrival.',
					'146' => 'The guest will be charged a prepayment of 90% of the total price in the 7 days before arrival.',
					'147' => 'The guest will be charged a prepayment of 90% of the total price in the 14 days before arrival.',
					'148' => 'The guest will be charged a prepayment of 90% of the total price in the 28 days before arrival.',
					'149' => 'The guest will be charged a prepayment of 90% of the total price in the 30 days before arrival.',
					'150' => 'The guest will be charged a prepayment of 90% of the total price in the 42 days before arrival.',
					'153' => 'The guest will be charged a prepayment of 90% of the total price after 2:00 PM on the day of arrival.',
					'154' => 'The guest will be charged a prepayment of 90% of the total price in the 5 days before arrival.',
					'155' => 'The guest will be charged a prepayment of 90% of the total price in the 60 days before arrival.',
					'171' => 'The guest will be charged a prepayment of 80% of the total price after 6:00 PM on the day of arrival.',
					'189' => 'The guest will be charged a prepayment of 80% of the total price in the 14 days before arrival.',
					'201' => 'The guest will be charged a prepayment of 80% of the total price within 1 day before arrival.',
					'209' => 'The guest will be charged a prepayment of 80% of the total price in the 21 days before arrival.',
					'213' => 'The guest will be charged a prepayment of 80% of the total price in the 28 days before arrival.',
					'216' => 'The guest will be charged a prepayment of 80% of the total price in the 2 days before arrival.',
					'226' => 'The guest will be charged a prepayment of 80% of the total price in the 30 days before arrival.',
					'236' => 'The guest will be charged a prepayment of 80% of the total price in the 3 days before arrival.',
					'244' => 'The guest will be charged a prepayment of 80% of the total price in the 5 days before arrival.',
					'258' => 'The guest will be charged a prepayment of 80% of the total price in the 7 days before arrival.',
					'274' => 'The guest will be charged a prepayment of 80% of the total price after reservation.',
					'327' => 'The guest will be charged a prepayment of 90% of the total price in the 21 days before arrival.',
					'343' => 'The guest will be charged a prepayment of 80% of the total price after 2:00 PM on the day of arrival.',
					'344' => 'The guest will be charged a prepayment of 80% of the total price in the 42 days before arrival.',
					'345' => 'The guest will be charged a prepayment of 80% of the total price in the 60 days before arrival.',
				],
			],
			[
				'group' => 'Policies only for Taiwan',
				'list' 	=> [
					'44' => 'The guest can cancel free of charge until 2 pm on the day of arrival. The guest will be charged 30% of the total price if they cancel after 2 pm on the day of arrival.',
					'48' => 'The guest can cancel free of charge until 6 pm on the day of arrival. The guest will be charged 30% of the total price if they cancel after 6 pm on the day of arrival.',
					'51' => 'The guest can cancel free of charge until 1 day before arrival. The guest will be charged 30% of the total price if they cancel within 1 day before arrival.',
					'54' => 'The guest can cancel free of charge until 2 days before arrival. The guest will be charged 30% of the total price if they cancel in the 2 days before arrival.',
					'57' => 'The guest can cancel free of charge until 3 days before arrival. The guest will be charged 30% of the total price if they cancel in the 3 days before arrival.',
					'61' => 'The guest can cancel free of charge until 5 days before arrival. The guest will be charged 30% of the total price if they cancel in the 5 days before arrival.',
					'64' => 'The guest can cancel free of charge until 7 days before arrival. The guest will be charged 30% of the total price if they cancel in the 7 days before arrival.',
					'67' => 'The guest can cancel free of charge until 14 days before arrival. The guest will be charged 30% of the total price if they cancel in the 14 days before arrival.',
					'71' => 'The guest can cancel free of charge until 30 days before arrival. The guest will be charged 30% of the total price if they cancel in the 30 days before arrival.',
					'114' => 'The guest can cancel free of charge until 42 days before arrival. The guest will be charged 30% of the total price if they cancel in the 42 days before arrival.',
					'118' => 'The guest can cancel free of charge until 60 days before arrival. The guest will be charged 30% of the total price if they cancel in the 60 days before arrival.',
				],
			],
		];

		if ($code) {
			// find the given code
			foreach ($bccp_list as $bccp_group) {
				foreach ($bccp_group['list'] as $policy_code => $bccp) {
					if ($policy_code == $code) {
						// code found
						return $get_group ? $bccp_group['group'] : $bccp;
					}
				}
			}

			// return an empty string in case the given policy code is not found
			return '';
		}

		// return the whole list
		return $bccp_list;
	}

	/**
	 * Parses a ContactInfo SimpleXMLElement node into an associative array representation.
	 * 
	 * @param 	SimpleXMLElement 	$contact_info 	the node to parse.
	 * 
	 * @return 	array 								the associative array representation of the node.
	 */
	public static function parseContactInfoNodes(SimpleXMLElement $contact_info)
	{
		$contact_info_attr = $contact_info->attributes();
		$contact_type = isset($contact_info_attr->ContactProfileType) ? (string) $contact_info_attr->ContactProfileType : '';

		$representation = [
			'type' 	  => $contact_type,
			'name' 	  => [
				'language' 	=> '',
				'gender' 	=> '',
				'givenname' => '',
				'jobtitle' 	=> '',
			],
			'email'   => '',
			'phones'  => [],
			'address' => [
				'hide' 		=> 0,
				'street' 	=> '',
				'city'   	=> '',
				'postcode' 	=> '',
				'country' 	=> '',
				'stateprov' => '',
				'tn' 		=> [],
			],
		];

		if (isset($contact_info->Names) && isset($contact_info->Names->Name)) {
			$name_attr = $contact_info->Names->Name->attributes();
			if (isset($name_attr->Language)) {
				$representation['name']['language'] = (string) $name_attr->Language;
			}
			if (isset($name_attr->Gender)) {
				$representation['name']['gender'] = (string) $name_attr->Gender;
			}
			if (isset($contact_info->Names->Name->GivenName)) {
				$representation['name']['givenname'] = (string) $contact_info->Names->Name->GivenName;
			}
			if (isset($contact_info->Names->Name->JobTitle)) {
				$representation['name']['jobtitle'] = (string) $contact_info->Names->Name->JobTitle;
			}
		}

		if (isset($contact_info->Emails) && isset($contact_info->Emails->Email)) {
			$representation['email'] = (string) $contact_info->Emails->Email;
		}

		if (isset($contact_info->Phones) && isset($contact_info->Phones->Phone)) {
			foreach ($contact_info->Phones->Phone as $cphone) {
				$cphone_attr = $cphone->attributes();
				$phone_data  = [
					'number' => (isset($cphone_attr->PhonesNumber) ? (string) $cphone_attr->PhonesNumber : ''),
					'type' 	 => (isset($cphone_attr->PhoneTechType) ? (string) $cphone_attr->PhoneTechType : ''),
				];
				// push phone information
				$representation['phones'][] = $phone_data;
			}

			// make sure to represent the phones array as a numeric array with 3 objects, one for each type of phone
			if ($representation['phones'] && count($representation['phones']) < 3) {
				$phone_types = [];
				foreach ($representation['phones'] as $phone_data) {
					$phone_types[$phone_data['type']] = $phone_data;
				}
				$sorted_phones = [];
				foreach ([1, 3, 5] as $ptype) {
					if (!isset($phone_types[$ptype])) {
						$sorted_phones[] = [
							'number' => '',
							'type' 	 => $ptype,
						];
					} else {
						$sorted_phones[] = $phone_types[$ptype];
					}
				}
				$representation['phones'] = $sorted_phones;
			}
		}

		if (isset($contact_info->Addresses) && isset($contact_info->Addresses->Address)) {
			$address_count = 0;
			$hide_address  = 0;
			if (isset($contact_info->HiddenAddress)) {
				$hide_address_attr = $contact_info->HiddenAddress->attributes();
				if (isset($hide_address_attr->ShouldHideAddress)) {
					$hide_address = (int) $hide_address_attr->ShouldHideAddress;
				}
			}
			$representation['address']['hide'] = $hide_address;
			foreach ($contact_info->Addresses->Address as $address) {
				$address_attr = $address->attributes();
				if (!isset($address_attr->Language) || !(string) $address_attr->Language || !strcasecmp((string) $address_attr->Language, 'en')) {
					// main address information non-translated
					if (isset($address->AddressLine)) {
						$representation['address']['street'] = (string) $address->AddressLine;
					}
					if (isset($address->CityName)) {
						$representation['address']['city'] = (string) $address->CityName;
					}
					if (isset($address->PostalCode)) {
						$representation['address']['postcode'] = (string) $address->PostalCode;
					}
					if (isset($address->CountryName)) {
						$representation['address']['country'] = (string) $address->CountryName;
					}
					if (isset($address->StateProv)) {
						$state_attr = $address->StateProv->attributes();
						$representation['address']['stateprov'] = isset($address->StateProv->StateCode) ? (string) $address->StateProv->StateCode : '';
					}
				} else {
					// localized address information (grab only the mandatory hotel name)
					if (isset($address->HotelName)) {
						$representation['address']['tn'][(string) $address_attr->Language] = (string) $address->HotelName;
					}
				}

				// increase counter
				$address_count++;
			}
		}

		// return the parsed contact info nodes
		return $representation;
	}

	/**
	 * Builds an associative array representing a tax policy ready to be JSON encoded.
	 * 
	 * @param 	SimpleXMLElement 	$taxpolicy_attr 	the tax policy node attributes.
	 * 
	 * @return 	array 				the associative array with the tax policy payload.
	 */
	public static function buildTaxPolicyPayload(SimpleXMLElement $taxpolicy_attr)
	{
		$tax_policy = [
			'code' => (string) $taxpolicy_attr->Code,
		];

		if (isset($taxpolicy_attr->Percent)) {
			// percent amount
			$tax_policy['percent'] = (int) $taxpolicy_attr->Percent;
		} else {
			// fixed amount
			$tax_policy['amount'] = (int) $taxpolicy_attr->Amount;
		}

		if (isset($taxpolicy_attr->DecimalPlaces)) {
			// number of decimals
			$tax_policy['decimalplaces'] = (int) $taxpolicy_attr->DecimalPlaces;
		}

		if (isset($taxpolicy_attr->Type)) {
			// inclusive or exclusive of taxes
			$tax_policy['type'] = (string) $taxpolicy_attr->Type;
		}

		if (isset($taxpolicy_attr->ChargeFrequency)) {
			// charge frequency
			$tax_policy['chargefrequency'] = (string) $taxpolicy_attr->ChargeFrequency;
		}

		if (isset($taxpolicy_attr->InvCode)) {
			// room-type ID
			$tax_policy['invcode'] = (string) $taxpolicy_attr->InvCode;
		}

		return $tax_policy;
	}

	/**
	 * Builds an associative array representing a fee policy ready to be JSON encoded.
	 * 
	 * @param 	SimpleXMLElement 	$feepolicy_node 	the fee policy main node.
	 * 
	 * @return 	array 				the associative array with the fee policy payload.
	 */
	public static function buildFeePolicyPayload(SimpleXMLElement $feepolicy_node)
	{
		$feepolicy_attr = $feepolicy_node->attributes();

		$fee_policy = [
			'code' => (string) $feepolicy_attr->Code,
		];

		if (isset($feepolicy_attr->Percent)) {
			// percent amount
			$fee_policy['percent'] = (int) $feepolicy_attr->Percent;
		} else {
			// fixed amount
			$fee_policy['amount'] = (int) $feepolicy_attr->Amount;
		}

		if (isset($feepolicy_attr->DecimalPlaces)) {
			// number of decimals
			$fee_policy['decimalplaces'] = (int) $feepolicy_attr->DecimalPlaces;
		}

		if (isset($feepolicy_attr->Type)) {
			// inclusive or exclusive of taxes
			$fee_policy['type'] = (string) $feepolicy_attr->Type;
		}

		if (isset($feepolicy_attr->ChargeFrequency)) {
			// charge frequency
			$fee_policy['chargefrequency'] = (string) $feepolicy_attr->ChargeFrequency;
		}

		if (isset($feepolicy_attr->MinAge)) {
			// min age (only for specific fee tax types)
			$fee_policy['minage'] = (int) $feepolicy_attr->MinAge;
		}

		if (isset($feepolicy_attr->MaxAge)) {
			// max age (only for specific fee tax types)
			$fee_policy['maxage'] = (int) $feepolicy_attr->MaxAge;
		}

		if (isset($feepolicy_attr->InvCode)) {
			// room-type ID
			$fee_policy['invcode'] = (string) $feepolicy_attr->InvCode;
		}

		// check if this specific type of fee has got a TPA_Extensions node with a child
		if (isset($feepolicy_node->TPA_Extensions)) {
			// supported containers: Conditions, InternetFeePolicy, ParkingFeePolicy
			if (isset($feepolicy_node->TPA_Extensions->Conditions->Condition)) {
				// cleaning fees
				$fee_policy['condition']['type'] = (string) $feepolicy_node->TPA_Extensions->Conditions->Condition->attributes()->Type;
			}

			if (isset($feepolicy_node->TPA_Extensions->ParkingFeePolicy)) {
				// parking fees
				$fee_policy['parkingfeepolicy'] = [];
				$parking_attr = $feepolicy_node->TPA_Extensions->ParkingFeePolicy->attributes();
				foreach ($parking_attr as $attr_name => $attr_val) {
					$fee_policy['parkingfeepolicy'][strtolower((string) $attr_name)] = (string) $attr_val;
				}
			}

			if (isset($feepolicy_node->TPA_Extensions->InternetFeePolicy)) {
				// internet fees
				$fee_policy['internetfeepolicy'] = [];
				$internet_attr = $feepolicy_node->TPA_Extensions->InternetFeePolicy->attributes();
				foreach ($internet_attr as $attr_name => $attr_val) {
					$fee_policy['internetfeepolicy'][strtolower((string) $attr_name)] = (string) $attr_val;
				}
			}
		}

		return $fee_policy;
	}

	/**
	 * Parses the "rate" XML nodes for the rate plans in order to sort them properly and not randomly.
	 * 
	 * @param 	SimpleXMLElement 	$rateplans 	the iterable "rate" XML node for each rate plan.
	 * 
	 * @return 	array 							list of rate plan objects.
	 */
	public static function sortRatePlans(SimpleXMLElement $rateplans)
	{
		$list = [];

		foreach ($rateplans as $bcom_rplan) {
			$rplan_name = (string) $bcom_rplan;
			$rplan_attr = $bcom_rplan->attributes();

			$rplan_data = new stdClass;
			$rplan_data->name = $rplan_name;

			foreach ($rplan_attr as $attr_name => $attr_value) {
				$attr_name = (string) $attr_name;
				$rplan_data->{$attr_name} = (string) $attr_value;
			}

			$list[] = $rplan_data;
		}

		// sort list of rate plans
		usort($list, function($a, $b)
		{
			if (isset($a->active) && isset($b->active)) {
				if ($a->active == '0' && $b->active == '1') {
					// active rate plans go first
					return 1;
				}
				if ($a->active == '1' && $b->active == '0') {
					// active rate plans go first
					return -1;
				}
			}

			if (isset($a->is_child_rate) && $a->is_child_rate == '1') {
				if (!isset($b->is_child_rate) || $b->is_child_rate == '0') {
					// derived rate plans go after
					return 1;
				}
			}

			if (isset($b->is_child_rate) && $b->is_child_rate == '1') {
				if (!isset($a->is_child_rate) || $a->is_child_rate == '0') {
					// derived rate plans go after
					return -1;
				}
			}

			// sort by rate plan ID (not "name")
			return strcasecmp($a->id, $b->id);
		});

		return $list;
	}

	/**
	 * Filters the eligible rate plans to be added to a room-rate relation.
	 * 
	 * @param 	array 	$rateplans 			the list of sorted rate plan objects for the whole property.
	 * @param 	array 	$roomrate_plans 	numeric array containing the rate plan IDs on the current room-rate relation.
	 * 
	 * @return 	array 						list of eligible (active and unused) rate plans for a new room-rate relation.
	 */
	public static function filterEligibleRoomRatePlans(array $rateplans, array $roomrate_plans = [])
	{
		$eligible = [];

		foreach ($rateplans as $rplan) {
			if (!isset($rplan->active) || $rplan->active != '1') {
				// rate plan must be active in order to be able to create a room-rate relation
				continue;
			}

			if (in_array($rplan->id, $roomrate_plans)) {
				// rate plan is linked to a room-rate already
				continue;
			}

			// push eligible rate plan
			$eligible[] = $rplan;
		}

		return $eligible;
	}

	/**
	 * Builds an associative array representing a room-rate relation ready to be JSON encoded.
	 * 
	 * @param 	SimpleXMLElement 	$roomrate_node 	the room-rate main node.
	 * @param 	string 				$room_id 		the room-type ID for this room-rate node.
	 * 
	 * @return 	array 				the associative array with the room-rate payload.
	 */
	public static function buildRoomRatePayload(SimpleXMLElement $roomrate_node, $room_id)
	{
		$roomrate_attr = $roomrate_node->attributes();

		$roomrate_data = [
			'room_id' 	  	=> $room_id,
			'rateplan_id' 	=> (string) $roomrate_attr->id,
			'rateplan_name' => (string) $roomrate_attr->rate_name,
		];

		if (isset($roomrate_node->meal_plan)) {
			$meal_plan_attr = $roomrate_node->meal_plan->attributes();
			$roomrate_data['meal_code'] = (string) $meal_plan_attr->meal_plan_code;
		}

		if (isset($roomrate_node->policies)) {
			if (isset($roomrate_node->policies->booking_rules) && isset($roomrate_node->policies->booking_rules->booking_rule)) {
				$book_rule_attr = $roomrate_node->policies->booking_rules->booking_rule->attributes();
				if (isset($book_rule_attr->min_advanced_booking_offset) || isset($book_rule_attr->max_advanced_booking_offset)) {
					// booking rules (optional)
					$roomrate_data['booking_rules'] = [
						'min_offset' => isset($book_rule_attr->min_advanced_booking_offset) ? (string) $book_rule_attr->min_advanced_booking_offset : '',
						'max_offset' => isset($book_rule_attr->max_advanced_booking_offset) ? (string) $book_rule_attr->max_advanced_booking_offset : '',
					];
				}
			}

			if (isset($roomrate_node->policies->cancel_policy)) {
				if (isset($roomrate_node->policies->cancel_policy->cancel_penalty)) {
					$canc_penalty_attr = $roomrate_node->policies->cancel_policy->cancel_penalty->attributes();
					$roomrate_data['canc_code'] = (string) $canc_penalty_attr->policy_code;
				}
				if (isset($roomrate_node->policies->cancel_policy->policy_overrides) && isset($roomrate_node->policies->cancel_policy->policy_overrides->policy_override)) {
					// policy overrides (optional)
					$roomrate_data['override_policies'] = [];
					foreach ($roomrate_node->policies->cancel_policy->policy_overrides->policy_override as $policy_ovr) {
						$policy_ovr_attr = $policy_ovr->attributes();
						// push override information
						$roomrate_data['override_policies'][] = [
							'name'  => isset($policy_ovr_attr->policy_code) ? (string) $policy_ovr_attr->policy_code : '',
							'dates' => [
								[
									'start' => isset($policy_ovr_attr->start_date) ? (string) $policy_ovr_attr->start_date : '',
									'end' 	=> isset($policy_ovr_attr->end_date) ? (string) $policy_ovr_attr->end_date : '',
								]
							],
							'wdays' => [],
						];
					}
				}
			}
		}

		if (isset($roomrate_node->value_added_services) && isset($roomrate_node->value_added_services->value_added_service)) {
			// start payload containers for value-adds
			$roomrate_data['value_adds_codes'] = [];
			$roomrate_data['value_adds_attributes'] = [];

			foreach ($roomrate_node->value_added_services->value_added_service as $valueadd) {
				$vad_attr = $valueadd->attributes();
				if (!isset($vad_attr->service_id)) {
					continue;
				}

				// get the service code
				$service_code = (string) $vad_attr->service_id;

				// push value-add service code
				$roomrate_data['value_adds_codes'][] = $service_code;

				// scan additional attributes, if any
				foreach ($vad_attr as $attr_name => $attr_val) {
					if (!strcasecmp($attr_name, 'service_id')) {
						// not a desired attribute
						continue;
					}

					if (!isset($roomrate_data['value_adds_attributes'][$service_code])) {
						// start container
						$roomrate_data['value_adds_attributes'][$service_code] = [];
					}

					// set attribute name and value
					$roomrate_data['value_adds_attributes'][$service_code][ucfirst($attr_name)] = is_numeric($attr_val) ? (float) $attr_val : (string) $attr_val;
				}
			}
		}

		return $roomrate_data;
	}

	/**
	 * Returns an associative list of value-adds category, type and service codes.
	 * 
	 * @param 	string 	$code 		optional value-adds service code to identify.
	 * @param 	bool 	$get_group 	true to query the group from a value-adds service code.
	 * 
	 * @return 	array|string 		full list or string with the queried value-adds service code.
	 * 
	 * @since 	1.9.0
	 */
	public static function getValueAddsServiceCodes($code = '', $get_group = false)
	{
		$bvalueadds = [
			[
				'macro' => 'Transportation',
				'group' => 'Parking',
				'list' 	=> [
					'1001' => [
						'name' => 'Self: 1 car Per room - per stay',
						'attributes' => [],
					],
					'1002' => [
						'name' => 'Valet: 1 car Per room - per stay',
						'attributes' => [],
					],
				],
			],
			[
				'macro' => 'Transportation',
				'group' => 'Airport transfer',
				'list' 	=> [
					'6001' => [
						'name' => 'Roundtrip',
						'attributes' => [],
					],
					'6002' => [
						'name' => 'One way',
						'attributes' => [],
					],
				],
			],
			[
				'macro' => 'Food and Beverage (F&B)',
				'group' => 'Credit or discount',
				'list' 	=> [
					'2001' => [
						'name' => 'Daily Credit Per adult - No Daily Accrual',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2002' => [
						'name' => 'Daily Credit Per adult - Daily Accrual',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2003' => [
						'name' => 'Daily Credit Per room - No Daily Accrual',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2004' => [
						'name' => 'Daily Credit Per room - Daily Accrual',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2009' => [
						'name' => 'Services Discount',
						'attributes' => [
							[
								'name' => 'Percentage',
								'type' => 'number',
								'help' => 'Percentage discount applied. Must be greater than 0 and less than or equal to 100.',
								'min'  => 0,
								'max'  => 100,
							],
						],
					],
					'2013' => [
						'name' => 'Per stay credit - per adult',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2014' => [
						'name' => 'Per stay credit - per room',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
				],
			],
			[
				'macro' => 'Food and Beverage (F&B)',
				'group' => 'Bottle of wine',
				'list' 	=> [
					'9001' => [
						'name' => 'Bottle of wine',
						'attributes' => [],
					],
				],
			],
			[
				'macro' => 'Food and Beverage (F&B)',
				'group' => 'Bottle of champagne',
				'list' 	=> [
					'9002' => [
						'name' => 'Bottle of champagne',
						'attributes' => [],
					],
				],
			],
			[
				'macro' => 'Property Services (PS)',
				'group' => 'Credit or discount',
				'list' 	=> [
					'2005' => [
						'name' => 'Daily Credit Per adult - No Daily Accrual',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2006' => [
						'name' => 'Daily Credit Per adult - Daily Accrual',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2007' => [
						'name' => 'Daily Credit Per room - No Daily Accrual',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2008' => [
						'name' => 'Daily Credit Per room - Daily Accrual',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2010' => [
						'name' => 'Discount',
						'attributes' => [
							[
								'name' => 'Percentage',
								'type' => 'number',
								'help' => 'Percentage discount applied. Must be greater than 0 and less than or equal to 100.',
								'min'  => 0,
								'max'  => 100,
							],
						],
					],
					'2011' => [
						'name' => 'Per stay credit - per adult',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
					'2012' => [
						'name' => 'Per stay credit - per room',
						'attributes' => [
							[
								'name' => 'CurrencyCode',
								'type' => 'currency',
								'help' => 'CurrencyCode: Specifies the currency code for the selected amount. Default: Uses property\'s currency code.',
							],
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Amount: Specifies an estimated worth of the value add included in the rate.',
							],
						],
					],
				],
			],
			[
				'macro' => 'Property Services (PS)',
				'group' => 'Internet Services (PS)',
				'list' 	=> [
					'5001' => [
						'name' => 'HighSpeed Internet Per stay - per room',
						'attributes' => [],
					],
				],
			],
			[
				'macro' => 'Frontdesk services',
				'group' => 'Early Check-in',
				'list' 	=> [
					'3001' => [
						'name' => 'Early Check-in',
						'attributes' => [
							[
								'name' => 'Hour',
								'type' => 'number',
								'help' => 'Specifies the minimum hour of the day the check-in is allowed.',
								'max'  => 24,
							],
						],
					],
				],
			],
			[
				'macro' => 'Frontdesk services',
				'group' => 'Late Check-out',
				'list' 	=> [
					'3002' => [
						'name' => 'Late Check-out',
						'attributes' => [
							[
								'name' => 'Hour',
								'type' => 'number',
								'help' => 'Specifies the maximum hour of the day the check-out is allowed.',
								'max'  => 24,
							],
						],
					],
				],
			],
			[
				'macro' => 'Frontdesk services',
				'group' => 'Late Check-in',
				'list' 	=> [
					'3003' => [
						'name' => 'Late Check-in',
						'attributes' => [
							[
								'name' => 'Hour',
								'type' => 'number',
								'help' => 'Specifies the maximum hour of the day the check-in is allowed.',
								'max'  => 24,
							],
						],
					],
				],
			],
			[
				'macro' => 'Wellness',
				'group' => 'Spa',
				'list' 	=> [
					'4001' => [
						'name' => 'Unlimited access Per day - per adult',
						'attributes' => [],
					],
					'4002' => [
						'name' => 'Hourly access Per day - per adult',
						'attributes' => [
							[
								'name' => 'Hour',
								'type' => 'number',
								'help' => 'Specifies the number of hours allowed at the spa per day, per adult.',
								'max'  => 24,
							],
						],
					],
				],
			],
			[
				'macro' => 'Wellness',
				'group' => 'Massage',
				'list' 	=> [
					'4003' => [
						'name' => 'N minutes Per stay - per adult',
						'attributes' => [
							[
								'name' => 'Minute',
								'type' => 'number',
								'help' => 'Specifies the duration of massage service in minutes included per stay, per adult.',
								'max'  => 360,
							],
						],
					],
				],
			],
			[
				'macro' => 'Activities',
				'group' => 'Safari game drive',
				'list' 	=> [
					'7001' => [
						'name' => 'Per day - per room',
						'attributes' => [],
					],
				],
			],
			[
				'macro' => 'Activities',
				'group' => 'Safari walk',
				'list' 	=> [
					'7002' => [
						'name' => 'Per day - per room',
						'attributes' => [],
					],
				],
			],
			[
				'macro' => 'Pets',
				'group' => 'Pets',
				'list' 	=> [
					'8001' => [
						'name' => 'Per day - per room',
						'attributes' => [
							[
								'name' => 'Amount',
								'type' => 'number',
								'help' => 'Specifies the number of allowed pets.',
								'min'  => 1,
								'max'  => 5,
							],
						],
					],
				],
			],
		];

		if ($code) {
			// find the given code
			foreach ($bvalueadds as $bvalueadd_group) {
				foreach ($bvalueadd_group['list'] as $service_code => $bvas) {
					if ($service_code == $code) {
						// code found
						return $get_group ? $bvalueadd_group['group'] : $bvas['name'];
					}
				}
			}

			// return an empty string in case the given value-adds service code is not found
			return '';
		}

		// return the whole list
		return $bvalueadds;
	}

	/**
	 * Returns an associative list of Booking.com room type codes (BCRT).
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.2
	 */
	public static function getRoomTypeCodes()
	{
		return [
			'1' => 'Apartment',
			'4' => 'Quadruple',
			'5' => 'Suite',
			'7' => 'Triple',
			'8' => 'Twin',
			'9' => 'Double',
			'10' => 'Single',
			'12' => 'Studio',
			'13' => 'Family',
			'24' => 'Twin/Double',
			'25' => 'Dormitory room',
			'26' => 'Bed in Dormitory',
			'27' => 'Bungalow',
			'28' => 'Chalet',
			'29' => 'Holiday home',
			'31' => 'Villa',
			'32' => 'Mobile home',
			'33' => 'Tent',
		];
	}

	/**
	 * Returns an associative list of Booking.com language codes (BCL).
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.2
	 */
	public static function getLanguageCodes()
	{
		return [
			'af' => 'Afrikaans',
			'ar' => 'Arabic',
			'az' => 'Azerbaijani',
			'be' => 'Belarusian',
			'bg' => 'Bulgarian',
			'ca' => 'Catalan',
			'cs' => 'Czech',
			'da' => 'Danish',
			'de' => 'German',
			'el' => 'Greek',
			'en-gb' => 'English (UK)',
			'en-us' => 'English (American)',
			'es' => 'Spanish',
			'es-ar' => 'Spanish (Argentine)',
			'et' => 'Estonian',
			'fr' => 'French',
			'fi' => 'Finnish',
			'he' => 'Hebrew',
			'hi' => 'Hindi',
			'hr' => 'Croatian',
			'hu' => 'Hungarian',
			'id' => 'Indonesian',
			'is' => 'Icelandic',
			'it' => 'Italian',
			'ja' => 'Japanese',
			'km' => 'Khmer',
			'ko' => 'Korean',
			'lo' => 'Lao',
			'lt' => 'Lithuanian',
			'lv' => 'Latvian',
			'ms' => 'Malay',
			'nl' => 'Dutch',
			'no' => 'Norwegian',
			'pl' => 'Polish',
			'pt-br' => 'Portuguese (Brazilian)',
			'pt-pt' => 'Portuguese',
			'ro' => 'Romanian',
			'ru' => 'Russian',
			'sk' => 'Slovak',
			'sl' => 'Slovenian',
			'sr' => 'Serbian',
			'sv' => 'Swedish',
			'tl' => 'Tagalog',
			'th' => 'Thai',
			'tr' => 'Turkish',
			'uk' => 'Ukrainian',
			'vi' => 'Vietnamese',
			'yu' => 'Cantonese',
			'zh-cn' => 'Chinese (Simplified)',
			'zh-tw' => 'Chinese (Traditional)',
		];
	}
}
