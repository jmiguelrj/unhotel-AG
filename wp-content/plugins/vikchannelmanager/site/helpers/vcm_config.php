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

class VikChannelManagerConfig
{
	const EXPEDIA = '1';
	const TRIP_CONNECT = '2';
	const TRIVAGO = '3';
	const BOOKING = '4';
	const AIRBNB = '5';
	const FLIPKEY = '6';
	const HOLIDAYLETTINGS = '7';
	const AGODA = '8';
	const WIMDU = '9';
	const HOMEAWAY = '10';
	const VRBO = '11';
	const YCS50 = '12';
	const BEDANDBREAKFASTIT = '13';
	const MOBILEAPP = '14';
	const DESPEGAR = '15';
	const OTELZ = '16';
	const GARDAPASS = '17';
	const BEDANDBREAKFASTEU = '18';
	const BEDANDBREAKFASTNL = '19';
	const FERATEL = '20';
	const PITCHUP = '21';
	const CAMPSITESCOUK = '22';
	const HOSTELWORLD = '23';
	const ICAL = '24';
	const AIRBNBAPI = '25';
	const GOOGLEHOTEL = '26';
	const VRBOAPI = '27';
	const AI = '28';
	const GOOGLEVR = '29';
	const DAC = '30';
	const CTRIP = '31';
	const VCM_CONNECTION_SERIAL = 52089858;
	
	public static $TA_HOTEL_AMENITIES = array(
		1 => '24-hour front desk',
		2 => '24-hour room service',
		3 => '24-hour security',
		4 => 'Adjoining rooms',
		5 => 'Air conditioning',
		6 => 'Airline desk',
		7 => 'ATM/Cash machine',
		8 => 'Baby sitting',
		9 => 'BBQ/Picnic area',
		10 => 'Bilingual staff',
		11 => 'Bookstore',
		12 => 'Boutiques/stores',
		13 => 'Brailed elevators',
		14 => 'Business library',
		15 => 'Car rental desk',
		16 => 'Casino',
		17 => 'Check cashing policy',
		18 => 'Check-in kiosk',
		19 => 'Cocktail lounge',
		20 => 'Coffee shop',
		21 => 'Coin operated laundry',
		22 => 'Concierge desk',
		23 => 'Concierge floor',
		24 => 'Conference facilities',
		25 => 'Courtyard',
		26 => 'Currency exchange',
		27 => 'Desk with electrical outlet',
		28 => 'Doctor on call',
		29 => 'Door man',
		30 => 'Driving range',
		31 => 'Drugstore/pharmacy',
		32 => 'Duty free shop',
		33 => 'Elevators',
		34 => 'Executive floor',
		35 => 'Exercise gym',
		36 => 'Express check-in',
		37 => 'Express check-out',
		38 => 'Family plan',
		39 => 'Florist',
		40 => 'Folios',
		41 => 'Free airport shuttle',
		42 => 'Free parking',
		43 => 'Free transportation',
		44 => 'Game room',
		45 => 'Gift/News stand',
		46 => 'Hairdresser/barber',
		47 => 'Accessible facilities',
		48 => 'Health club',
		49 => 'Heated pool',
		50 => 'Housekeeping - daily',
		51 => 'Housekeeping - weekly',
		52 => 'Ice machine',
		53 => 'Indoor parking',
		54 => 'Indoor pool',
		55 => 'Jacuzzi',
		56 => 'Jogging track',
		57 => 'Kennels',
		58 => 'Laundry/Valet service',
		59 => 'Liquor store',
		60 => 'Live entertainment',
		61 => 'Massage services',
		62 => 'Nightclub',
		63 => 'Off-Site parking',
		64 => 'On-Site parking',
		65 => 'Outdoor parking',
		66 => 'Outdoor pool',
		67 => 'Package/Parcel services',
		68 => 'Parking',
		69 => 'Photocopy center',
		70 => 'Playground',
		71 => 'Pool',
		72 => 'Poolside snack bar',
		73 => 'Public address system',
		74 => 'Ramp access',
		75 => 'Recreational vehicle parking',
		76 => 'Restaurant',
		77 => 'Room service',
		78 => 'Safe deposit box',
		79 => 'Sauna',
		80 => 'Security',
		81 => 'Shoe shine stand',
		82 => 'Shopping mall',
		83 => 'Solarium',
		84 => 'Spa',
		85 => 'Sports bar',
		86 => 'Steam bath',
		87 => 'Storage space',
		88 => 'Sundry/Convenience store',
		89 => 'Technical concierge',
		90 => 'Theatre desk',
		91 => 'Tour/sightseeing desk',
		92 => 'Translation services',
		93 => 'Travel agency',
		94 => 'Truck parking',
		95 => 'Valet cleaning',
		96 => 'Dry cleaning',
		97 => 'Valet parking',
		98 => 'Vending machines',
		99 => 'Video tapes',
		100 => 'Wakeup service',
		101 => 'Wheelchair access',
		102 => 'Whirlpool',
		103 => 'Multilingual staff',
		104 => 'Wedding services',
		105 => 'Banquet facilities',
		106 => 'Bell staff/porter',
		107 => 'Beauty shop/salon',
		108 => 'Complimentary self service laundry',
		109 => 'Direct dial telephone',
		110 => 'Female traveler room/floor',
		111 => 'Pharmacy',
		112 => 'Stables',
		113 => '120 AC',
		114 => '120 DC',
		115 => '220 AC',
		116 => 'Accessible parking',
		117 => '220 DC',
		118 => 'Barbeque grills',
		119 => 'Women clothing',
		120 => 'Men clothing',
		121 => 'Children clothing',
		122 => 'Shops and commercial services',
		123 => 'Video games',
		124 => 'Sports bar open for lunch',
		125 => 'Sports bar open for dinner',
		126 => 'Room service - full menu',
		127 => 'Room service - limited menu',
		128 => 'Room service - limited hours',
		129 => 'Valet same day dry cleaning',
		130 => 'Body scrub',
		131 => 'Body wrap',
		132 => 'Public area air conditioned',
		133 => 'Efolio available to company',
		134 => 'Individual Efolio available',
		135 => 'Video review billing',
		136 => 'Butler service',
		137 => 'Complimentary in-room coffee or tea',
		138 => 'Complimentary buffet breakfast',
		139 => 'Complimentary cocktails',
		140 => 'Complimentary coffee in lobby',
		141 => 'Complimentary continental breakfast',
		142 => 'Complimentary full american breakfast',
		143 => 'Dinner delivery service from local restaurant',
		144 => 'Complimentary newspaper delivered to room',
		145 => 'Complimentary newspaper in lobby',
		146 => 'Complimentary shoeshine',
		147 => 'Evening reception',
		148 => 'Front desk',
		149 => 'Grocery shopping service available',
		150 => 'Halal food available',
		151 => 'Kosher food available',
		152 => 'Limousine service',
		153 => 'Managers reception',
		154 => 'Medical Facilities Service',
		155 => 'Telephone jack adaptor available',
		156 => 'All-inclusive meal plan',
		157 => 'Buffet breakfast',
		158 => 'Communal bar area',
		159 => 'Continental breakfast',
		160 => 'Full meal plan',
		161 => 'Full american breakfast',
		162 => 'Meal plan available',
		163 => 'Modified american meal plan',
		164 => 'Food and beverage outlets',
		165 => 'Bar/Lounge',
		166 => 'Barber shop',
		167 => 'Video checkout',
		168 => 'Onsite laundry',
		169 => '24-hour food & beverage kiosk',
		170 => 'Concierge lounge',
		171 => 'Parking fee managed by hotel',
		172 => 'Transportation',
		173 => 'Breakfast served in restaurant',
		174 => 'Lunch served in restaurant',
		175 => 'Dinner served in restaurant',
		176 => 'Full service housekeeping',
		177 => 'Limited service housekeeping',
		178 => 'High speed internet access in public areas',
		179 => 'Wireless internet connection in public areas',
		180 => 'Additional services/amenities/facilities on property',
		181 => 'Transportation services - local area',
		182 => 'Transportation services - local office',
		183 => 'DVD/video rental',
		184 => 'Parking lot',
		185 => 'Parking deck',
		186 => 'Street side parking',
		187 => 'Cocktail lounge with entertainment',
		188 => 'Cocktail lounge with light fare',
		189 => 'Motorcycle parking',
		190 => 'Phone services',
		191 => 'Ballroom',
		192 => 'Bus parking',
		193 => 'Children play area',
		194 => 'Children nursery',
		195 => 'Disco',
		196 => 'Early check-in',
		197 => 'Locker room',
		198 => 'Non-smoking rooms (generic)',
		199 => 'Train access',
		200 => 'Aerobics instruction',
		201 => 'Baggage hold',
		202 => 'Bicycle rentals',
		203 => 'Dietician',
		204 => 'Late check-out available',
		205 => 'Pet-sitting services',
		206 => 'Prayer mats',
		207 => 'Sports trainer',
		208 => 'Turndown service',
		209 => 'DVDs/videos - children',
		210 => 'Bank',
		211 => 'Lobby coffee service',
		212 => 'Banking services',
		213 => 'Stairwells',
		214 => 'Pet amenities available',
		215 => 'Exhibition/convention floor',
		216 => 'Long term parking',
		217 => 'Children not allowed',
		218 => 'Children welcome',
		219 => 'Courtesy car',
		220 => 'Hotel does not provide pornographic films/TV',
		221 => 'Hotspots',
		222 => 'Free high speed internet connection',
		223 => 'Internet services',
		224 => 'Pets allowed',
		225 => 'Gourmet highlights',
		226 => 'Catering services',
		227 => 'Complimentary breakfast',
		228 => 'Business center',
		229 => 'Business services',
		230 => 'Secured parking',
		231 => 'Racquetball',
		232 => 'Snow sports',
		233 => 'Tennis court',
		234 => 'Water sports',
		235 => 'Child programs',
		236 => 'Golf',
		237 => 'Horseback riding',
		238 => 'Oceanfront',
		239 => 'Beachfront',
		240 => 'Hair dryer',
		241 => 'Ironing board',
		242 => 'Heated guest rooms',
		243 => 'Toilet',
		244 => 'Parlor',
		245 => 'Video game player',
		246 => 'Thalassotherapy',
		247 => 'Private dining for groups',
		248 => 'Hearing impaired services',
		249 => 'Carryout breakfast',
		250 => 'Deluxe continental breakfast',
		251 => 'Hot continental breakfast',
		252 => 'Hot breakfast',
		253 => 'Private pool',
		254 => 'Connecting rooms',
		255 => 'Data port',
		256 => 'Exterior corridors',
		257 => 'Gulf view',
		258 => 'Accessible rooms',
		259 => 'High speed internet access',
		260 => 'Interior corridors',
		261 => 'High speed wireless',
		262 => 'Kitchenette',
		263 => 'Private bath or shower',
		264 => 'Fire safety compliant',
		265 => 'Welcome drink',
		266 => 'Boarding pass print-out available',
		267 => 'Printing services available',
		268 => 'All public areas non-smoking',
		269 => 'Meeting rooms',
		270 => 'Movies in room',
		271 => 'Secretarial service',
		272 => 'Snow skiing',
		273 => 'Water skiing',
		274 => 'Fax service',
		275 => 'Great room',
		276 => 'Lobby',
		277 => 'Multiple phone lines billed separately',
		278 => 'Umbrellas',
		279 => 'Gas station',
		280 => 'Grocery store',
		281 => '24-hour coffee shop',
		282 => 'Airport shuttle service',
		283 => 'Luggage service',
		284 => 'Piano Bar',
		285 => 'VIP security',
		286 => 'Complimentary wireless internet',
		287 => 'Concierge breakfast',
		288 => 'Same gender floor',
		289 => 'Children programs',
		290 => 'Building meets local, state and country building codes',
		291 => 'Internet browser On TV',
		292 => 'Newspaper',
		293 => 'Parking - controlled access gates to enter parking area',
		294 => 'Hotel safe deposit box (not room safe box)',
		295 => 'Storage space available – fee',
		296 => 'Type of entrances to guest rooms',
		297 => 'Beverage/cocktail',
		298 => 'Cell phone rental',
		299 => 'Coffee/tea',
		300 => 'Early check in guarantee',
		301 => 'Food and beverage discount',
		302 => 'Late check out guarantee',
		303 => 'Room upgrade confirmed',
		304 => 'Room upgrade on availability',
		305 => 'Shuttle to local businesses',
		306 => 'Shuttle to local attractions',
		307 => 'Social hour',
		308 => 'Video billing',
		309 => 'Welcome gift',
		310 => 'Hypoallergenic rooms',
		311 => 'Room air filtration',
		312 => 'Smoke-free property',
		313 => 'Water purification system in use',
		314 => 'Poolside service',
		315 => 'Clothing store',
		316 => 'Electric car charging stations',
		317 => 'Office rental',
		318 => 'Piano',
		319 => 'Incoming fax',
		320 => 'Outgoing fax',
		321 => 'Semi-private space',
		322 => 'Loading dock',
		323 => 'Baby kit',
		324 => 'Children breakfast',
		325 => 'Cloakroom service',
		326 => 'Coffee lounge',
		327 => 'Events ticket service',
		328 => 'Late check-in',
		329 => 'Limited parking',
		330 => 'Outdoor summer bar/café',
		331 => 'No parking available',
		332 => 'Beer garden',
		333 => 'Garden lounge bar',
		334 => 'Summer terrace',
		335 => 'Winter terrace',
		336 => 'Roof terrace',
		337 => 'Beach bar',
		338 => 'Helicopter service',
		339 => 'Ferry',
		340 => 'Tapas bar',
		341 => 'Café bar',
		342 => 'Snack bar',
	);

	public static $TA_ROOM_AMENITIES = array(
		1 => 'Adjoining rooms',
		2 => 'Air conditioning',
		3 => 'Alarm clock',
		4 => 'All news channel',
		5 => 'AM/FM radio',
		6 => 'Baby listening device',
		7 => 'Balcony/Lanai/Terrace',
		8 => 'Barbeque grills',
		9 => 'Bath tub with spray jets',
		10 => 'Bathrobe',
		11 => 'Bathroom amenities',
		12 => 'Bathroom telephone',
		13 => 'Bathtub',
		14 => 'Bathtub only',
		15 => 'Bathtub/shower combination',
		16 => 'Bidet',
		17 => 'Bottled water',
		18 => 'Cable television',
		19 => 'Coffee/Tea maker',
		20 => 'Color television',
		21 => 'Computer',
		22 => 'Connecting rooms',
		23 => 'Converters/ Voltage adaptors',
		24 => 'Copier',
		25 => 'Cordless phone',
		26 => 'Cribs',
		27 => 'Data port',
		28 => 'Desk',
		29 => 'Desk with lamp',
		30 => 'Dining guide',
		31 => 'Direct dial phone number',
		32 => 'Dishwasher',
		33 => 'Double beds',
		34 => 'Dual voltage outlet',
		35 => 'Electrical current voltage',
		36 => 'Ergonomic chair',
		37 => 'Extended phone cord',
		38 => 'Fax machine',
		39 => 'Fire alarm',
		40 => 'Fire alarm with light',
		41 => 'Fireplace',
		42 => 'Free toll free calls',
		43 => 'Free calls',
		44 => 'Free credit card access calls',
		45 => 'Free local calls',
		46 => 'Free movies/video',
		47 => 'Full kitchen',
		48 => 'Grab bars in bathroom',
		49 => 'Grecian tub',
		50 => 'Hairdryer',
		51 => 'High speed internet connection',
		52 => 'Interactive web TV',
		53 => 'International direct dialing',
		54 => 'Internet access',
		55 => 'Iron',
		56 => 'Ironing board',
		57 => 'Whirlpool / Jacuzzi',
		58 => 'King bed',
		59 => 'Kitchen',
		60 => 'Kitchen supplies',
		61 => 'Kitchenette',
		62 => 'Knock light',
		63 => 'Laptop',
		64 => 'Large desk',
		65 => 'Large work area',
		66 => 'Laundry basket/clothes hamper',
		67 => 'Loft',
		68 => 'Microwave',
		69 => 'Minibar',
		70 => 'Modem',
		71 => 'Modem jack',
		72 => 'Multi-line phone',
		73 => 'Newspaper',
		74 => 'Non-smoking',
		75 => 'Notepads',
		76 => 'Office supplies',
		77 => 'Oven',
		78 => 'Pay per view movies on TV',
		79 => 'Pens',
		80 => 'Phone in bathroom',
		81 => 'Plates and bowls',
		82 => 'Pots and pans',
		83 => 'Prayer mats',
		84 => 'Printer',
		85 => 'Private bathroom',
		86 => 'Queen bed',
		87 => 'Recliner',
		88 => 'Refrigerator',
		89 => 'Refrigerator with ice maker',
		90 => 'Remote control television',
		91 => 'Rollaway bed',
		92 => 'Safe',
		93 => 'Scanner',
		94 => 'Separate closet',
		95 => 'Separate modem line available',
		96 => 'Shoe polisher',
		97 => 'Shower only',
		98 => 'Silverware/utensils',
		99 => 'Sitting area',
		100 => 'Smoke detectors',
		101 => 'Smoking',
		102 => 'Sofa bed',
		103 => 'Speaker phone',
		104 => 'Stereo',
		105 => 'Stove',
		106 => 'Tape recorder',
		107 => 'Telephone',
		108 => 'Telephone for hearing impaired',
		109 => 'Telephones with message light',
		110 => 'Toaster oven',
		111 => 'Trouser/Pant press',
		112 => 'Turn down service',
		113 => 'Twin bed',
		114 => 'Vaulted ceilings',
		115 => 'VCR movies',
		116 => 'VCR player',
		117 => 'Video games',
		118 => 'Voice mail',
		119 => 'Wake-up calls',
		120 => 'Water closet',
		121 => 'Water purification system',
		122 => 'Wet bar',
		123 => 'Wireless internet connection',
		124 => 'Wireless keyboard',
		125 => 'Adaptor available for telephone PC use',
		126 => 'Air conditioning individually controlled in room',
		127 => 'Bathtub &whirlpool separate',
		128 => 'Telephone with data ports',
		129 => 'CD  player',
		130 => 'Complimentary local calls time limit',
		131 => 'Extra person charge for rollaway use',
		132 => 'Down/feather pillows',
		133 => 'Desk with electrical outlet',
		134 => 'ESPN available',
		135 => 'Foam pillows',
		136 => 'HBO available',
		137 => 'High ceilings',
		138 => 'Marble bathroom',
		139 => 'List of movie channels available',
		140 => 'Pets allowed',
		141 => 'Oversized bathtub',
		142 => 'Shower',
		143 => 'Sink in-room',
		144 => 'Soundproofed room',
		145 => 'Storage space',
		146 => 'Tables and chairs',
		147 => 'Two-line phone',
		148 => 'Walk-in closet',
		149 => 'Washer/dryer',
		150 => 'Weight scale',
		151 => 'Welcome gift',
		152 => 'Spare electrical outlet available at desk',
		153 => 'Non-refundable charge for pets',
		154 => 'Refundable deposit for pets',
		155 => 'Separate tub and shower',
		156 => 'Entrance type to guest room',
		157 => 'Ceiling fan',
		158 => 'CNN available',
		159 => 'Electrical adaptors available',
		160 => 'Buffet breakfast',
		161 => 'Accessible room',
		162 => 'Closets in room',
		163 => 'DVD player',
		164 => 'Mini-refrigerator',
		165 => 'Separate line billing for multi-line phone',
		166 => 'Self-controlled heating/cooling system',
		167 => 'Toaster',
		168 => 'Analog data port',
		169 => 'Collect calls',
		170 => 'International calls',
		171 => 'Carrier access',
		172 => 'Interstate calls',
		173 => 'Intrastate calls',
		174 => 'Local calls',
		175 => 'Long distance calls',
		176 => 'Operator-assisted calls',
		177 => 'Credit card access calls',
		178 => 'Calling card calls',
		179 => 'Toll free calls',
		180 => 'Universal AC/DC adaptors',
		181 => 'Bathtub seat',
		182 => 'Canopy/poster bed',
		183 => 'Cups/glassware',
		184 => 'Entertainment center',
		185 => 'Family/oversized room',
		186 => 'Hypoallergenic bed',
		187 => 'Hypoallergenic pillows',
		188 => 'Lamp',
		189 => 'Meal included - breakfast',
		190 => 'Meal included - continental breakfast',
		191 => 'Meal included - dinner',
		192 => 'Meal included - lunch',
		193 => 'Shared bathroom',
		194 => 'Telephone TDD/Textphone',
		195 => 'Water bed',
		196 => 'Extra adult charge',
		197 => 'Extra child charge',
		198 => 'Extra child charge for rollaway use',
		199 => 'Meal included:  full American breakfast',
		200 => 'Futon',
		201 => 'Murphy bed',
		202 => 'Tatami mats',
		203 => 'Single bed',
		204 => 'Annex room',
		205 => 'Free newspaper',
		206 => 'Honeymoon suites',
		207 => 'Complimentary high speed internet in room',
		208 => 'Maid service',
		209 => 'PC hook-up in room',
		210 => 'Satellite television',
		211 => 'VIP rooms',
		212 => 'Cell phone recharger',
		213 => 'DVR player',
		214 => 'iPod docking station',
		215 => 'Media center',
		216 => 'Plug & play panel',
		217 => 'Satellite radio',
		218 => 'Video on demand',
		219 => 'Exterior corridors',
		220 => 'Gulf view',
		221 => 'Accessible room',
		222 => 'Interior corridors',
		223 => 'Mountain view',
		224 => 'Ocean view',
		225 => 'High speed internet access fee',
		226 => 'High speed wireless',
		227 => 'Premium movie channels',
		228 => 'Slippers',
		229 => 'First nighters kit',
		230 => 'Chair provided with desk',
		231 => 'Pillow top mattress',
		232 => 'Feather bed',
		233 => 'Duvet',
		234 => 'Luxury linen type',
		235 => 'International channels',
		236 => 'Pantry',
		237 => 'Dish-cleaning supplies',
		238 => 'Double vanity',
		239 => 'Lighted makeup mirror',
		240 => 'Upgraded bathroom amenities',
		241 => 'VCR player available at front desk',
		242 => 'Instant hot water',
		243 => 'Outdoor space',
		244 => 'Hinoki tub',
		245 => 'Private pool',
		246 => 'HD TV 32 inch or greater',
		247 => 'Room windows open',
		248 => 'Bedding type unknown or unspecified',
		249 => 'Full bed',
		250 => 'Round bed',
		251 => 'TV',
		252 => 'Child rollaway',
		253 => 'DVD player available at front desk',
		254 => 'Video game player',
		255 => 'Video game player available at front desk',
		256 => 'Dining room seats',
		257 => 'Full size mirror',
		258 => 'Mobile/cellular phones',
		259 => 'Movies',
		260 => 'Multiple closets',
		261 => 'Plates/glassware',
		262 => 'Safe large enough to accommodate a laptop',
		263 => 'Bed linen thread count',
		264 => 'Blackout curtain',
		265 => 'Bluray player',
		266 => 'Device with mp3',
		267 => 'No adult channels or adult channel lock',
		268 => 'Non-allergenic room',
		269 => 'Pillow type',
		270 => 'Seating area with sofa/chair',
		271 => 'Separate toilet area',
		272 => 'Web enabled',
		273 => 'Widescreen TV',
		274 => 'Other data connection',
		275 => 'Phoneline billed separately',
		276 => 'Separate tub or shower',
		277 => 'Video games',
		278 => 'Roof ventilator',
		279 => 'Children playpen',
		280 => 'Plunge pool',
		900101 => 'Down comforter',
		900102 => 'Egyptian cotton sheets',
		900103 => 'Espresso maker',
		900104 => 'Free toiletries',
		900105 => 'Frette Italian Sheets',
		900106 => 'Housekeeping',
		900107 => 'Hydromassage Showerhead',
		900108 => 'In-room childcare',
		900109 => 'In-room massage available',
		900110 => 'Individually decorated',
		900111 => 'Individually furnished',
		900112 => 'iPad',
		900113 => 'LCD / Plasma TV',
		900114 => 'LED TV',
		900115 => 'Living room',
		900116 => 'Memory foam mattress',
		900117 => 'Patio',
		900118 => 'Premium bedding',
		900119 => 'Private spa tub',
		900120 => 'Rainfall showerhead',
		900121 => 'Second bathroom',
		900122 => 'Shared / Communal kitchen',
		900123 => 'Tablet computer',
		900124 => 'Weekly housekeeping',
		900125 => 'Yard',
		900126 => 'Free WiFi',
		900127 => 'WiFi with fee',
		900128 => 'WiFi',
		900129 => 'Free wired high speed internet',
		900130 => 'Wired high speed internet',
		900131 => 'Wired high speed internet with fee',
	);

	public static function compareRoomAmenities($a, $b) {
		return strcasecmp($a, $b);
	}

	public static $TA_ROOM_CODES = array(
		1 => 'Double',
		2 => 'Futon',
		3 => 'King',
		4 => 'Murphy Bed',
		5 => 'Queen',
		6 => 'Sofa Bed',
		7 => 'Tatami Mats',
		8 => 'Twin',
		9 => 'Single',
		10 => 'Full',
		11 => 'Run of the House',
		12 => 'Dorm Bed',
	);

	public static $TRI_ROOM_CODES = array(
		'SINGLE',
		'DOUBLE',
		'OTHER',
	);

	public static $AVAILABLE_CHANNELS = array(
		VikChannelManagerConfig::BOOKING => 'booking',
		// the API version of Airbnb should come before the iCal version
		VikChannelManagerConfig::AIRBNBAPI => 'airbnbapi',
		VikChannelManagerConfig::EXPEDIA => 'expedia',
		VikChannelManagerConfig::GOOGLEHOTEL => 'googlehotel',
		VikChannelManagerConfig::GOOGLEVR => 'googlevr',
		VikChannelManagerConfig::VRBOAPI => 'vrbo',
		VikChannelManagerConfig::AGODA => 'agoda',
		VikChannelManagerConfig::YCS50 => 'agoda',
		VikChannelManagerConfig::HOSTELWORLD => 'hostelworld',
		VikChannelManagerConfig::CTRIP => 'ctrip',
		VikChannelManagerConfig::TRIP_CONNECT => 'tripconnect',
		VikChannelManagerConfig::TRIVAGO => 'trivago',
		VikChannelManagerConfig::AIRBNB => 'airbnb',
		VikChannelManagerConfig::HOMEAWAY => 'homeaway',
		VikChannelManagerConfig::HOLIDAYLETTINGS => 'holidaylettings',
		VikChannelManagerConfig::WIMDU => 'wimdu',
		VikChannelManagerConfig::FLIPKEY => 'flipkey',
		VikChannelManagerConfig::BEDANDBREAKFASTIT => 'bedandbreakfastit',
		VikChannelManagerConfig::DESPEGAR => 'despegar',
		VikChannelManagerConfig::OTELZ => 'otelz',
		VikChannelManagerConfig::GARDAPASS => 'gardapass',
		VikChannelManagerConfig::BEDANDBREAKFASTEU => 'bedandbreakfasteu',
		VikChannelManagerConfig::BEDANDBREAKFASTNL => 'bedandbreakfastnl',
		VikChannelManagerConfig::FERATEL => 'feratel',
		VikChannelManagerConfig::PITCHUP => 'pitchup',
		VikChannelManagerConfig::CAMPSITESCOUK => 'campsitescouk',
		VikChannelManagerConfig::ICAL => 'ical',
		VikChannelManagerConfig::DAC => 'dac',
		VikChannelManagerConfig::MOBILEAPP => 'mobileapp',
		VikChannelManagerConfig::AI => 'ai',
	);
	
	public static $ERRORS_MAP = array(
		"e4j" => array(
			"_default" => "MSG_BASE",
			"error" => array(
				"_default" => "MSG_BASE_ERROR",
				"Authentication" => array(
					"_default" => "MSG_BASE_ERROR_AUTH",
					"TripConnect" => array(
					   "_default" => "MSG_BASE_ERROR_AUTH_TRIPCONNECT",
                    ),
				),
				"NoChannels" => array(
				    "_default" => "MSG_BASE_ERROR_NOCHANNELS",
                ),
				"Curl" => array(
				    "_default" => "MSG_BASE_ERROR_CURL",
					"Request" => array(
						"_default" => "MSG_BASE_ERROR_CURL_REQUEST",
					),
					"Connection" => array(
						"_default" => "MSG_BASE_ERROR_CURL_CONNECTION"
					),
					"Broken" => array(
						"_default" => "MSG_BASE_ERROR_CURL_BROKEN"
					),
                ),
				"Expedia" => array(
					"_default" => "MSG_BASE_ERROR_EXPEDIA",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_EXPEDIA_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_EXPEDIA_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_EXPEDIA_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_EXPEDIA_AR_RS",
					),
				),
				"Agoda" => array(
					"_default" => "MSG_BASE_ERROR_AGODA",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_AGODA_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_AGODA_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_AGODA_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_AGODA_AR_RS",
					),
				),
				"Booking" => array(
					"_default" => "MSG_BASE_ERROR_BOOKING",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_AR_RS",
					),
				),
				"Airbnb" => array(
					"_default" => "MSG_BASE_ERROR_CHANNELS",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_AR_RS",
					),
					"ACCPRR_RS" => array(
						"_default" => "MSG_BASE_ERROR_AIRBNB_ACCPRR_RS",
					),
					"DENPRR_RS" => array(
						"_default" => "MSG_BASE_ERROR_AIRBNB_DENPRR_RS",
					),
				),
				"Airbnbapi" => array(
					"_default" => "MSG_BASE_ERROR_CHANNELS",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_AR_RS",
					),
					"ACCPRR_RS" => array(
						"_default" => "MSG_BASE_ERROR_AIRBNB_ACCPRR_RS",
					),
					"DENPRR_RS" => array(
						"_default" => "MSG_BASE_ERROR_AIRBNB_DENPRR_RS",
					),
				),
				"Googlehotel" => array(
					"_default" => "MSG_BASE_ERROR_CHANNELS",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_AR_RS",
					),
				),
				"Googlevr" => array(
					"_default" => "MSG_BASE_ERROR_CHANNELS",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_AR_RS",
					),
				),
				"Despegar" => array(
					"_default" => "MSG_BASE_ERROR_DESPEGAR",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_AR_RS",
					),
				),
				"Otelz" => array(
					"_default" => "MSG_BASE_ERROR_OTELZ",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_OTELZ_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_OTELZ_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_OTELZ_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_OTELZ_AR_RS",
					),
				),
				"Gardapass" => array(
					"_default" => "MSG_BASE_ERROR_GARDAPASS",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_GARDAPASS_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_GARDAPASS_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_GARDAPASS_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_GARDAPASS_AR_RS",
					),
				),
				"Bedandbreakfastit" => array(
					"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_AR_RS",
					),
				),
				"Bedandbreakfasteu" => array(
					"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_AR_RS",
					),
				),
				"Bedandbreakfastnl" => array(
					"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BEDANDBREAKFASTIT_AR_RS",
					),
				),
				"Feratel" => array(
					"_default" => "MSG_BASE_ERROR_DESPEGAR",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_AR_RS",
					),
				),
				"Pitchup" => array(
					"_default" => "MSG_BASE_ERROR_DESPEGAR",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_AR_RS",
					),
				),
				"Hostelworld" => array(
					"_default" => "MSG_BASE_ERROR_DESPEGAR",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_DESPEGAR_AR_RS",
					),
				),
				"Vrboapi" => array(
					"_default" => "MSG_BASE_ERROR_CHANNELS",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_AR_RS",
					),
				),
				"Ctrip" => array(
					"_default" => "MSG_BASE_ERROR_CHANNELS",
					"RAR" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_RAR",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_BC_RS",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_ERROR_BOOKING_AR_RS",
					),
				),
				"Channels" => array(
					"_default" => "MSG_BASE_ERROR_CHANNELS",
					"NoSynchRooms" => array(
						"_default" => "MSG_BASE_ERROR_CHANNELS_NOSYNCHROOMS",
					),
					"BookingDownload" => array(
						"_default" => "MSG_BASE_ERROR_CHANNELS_BOOKINGDOWNLOAD",
					),
					"InvalidBooking" => array(
						"_default" => "MSG_BASE_ERROR_CHANNELS_INVALIDBOOKING",
					),
					"BookingModification" => array(
						"_default" => "MSG_BASE_ERROR_CHANNELS_BOOKINGMODIFICATION",
					),
					"ACMP_Busy" => array(
						"_default" => "MSG_BASE_ERROR_CHANNELS_ACMPBUSY",
					),
					"AVPUSH_Busy" => array(
						"_default" => "MSG_BASE_ERROR_CHANNELS_AVPUSHBUSY",
					),
					"RATESPUSH_Busy" => array(
						"_default" => "MSG_BASE_ERROR_CHANNELS_RATESPUSHBUSY",
					),
				),
				"File" => array(
					"_default" => "MSG_BASE_ERROR_FILE",
					"Permissions" => array(
						"_default" => "MSG_BASE_ERROR_FILE_PERMISSIONS",
						"Write" => array(
							"_default" => "MSG_BASE_ERROR_FILE_PERMISSIONS_WRITE",
						),
					),
					"NotFound" => array(
						"_default" => "MSG_BASE_ERROR_FILE_NOTFOUND",
					),
				),
				"Max31days" => array(
					"_default" => "MSG_BASE_ERROR_MAX31DAYSREQ",
				),
                "ParRequestResponseIntegrity" => array(
                    "_default" => "MSG_BASE_ERROR_PAR_RR"
                ),
				"Query" => array(
					"_default" => "MSG_BASE_ERROR_QUERY",
				),
				"RequestIntegrity" => array(
					"_default" => "MSG_BASE_ERROR_REQUEST",
				),
				"Schema" => array(
					"_default" => "MSG_BASE_ERROR_SCHEMA",
				),
				"Settings" => array(
                    "_default" => "MSG_BASE_ERROR_SETTINGS",
                ),
                "Pcidata" => array(
                    "_default" => "MSG_BASE_ERROR_PCIDATA",
                ),
			),
			"warning" => array(
				"_default" => "MSG_BASE_WARNING",
				"Expedia" => array(
					"_default" => "MSG_BASE_WARNING_EXPEDIA",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_EXPEDIA_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_EXPEDIA_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_EXPEDIA_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_EXPEDIA_BC_RS",
					),
				),
				"Agoda" => array(
					"_default" => "MSG_BASE_WARNING_AGODA",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_AGODA_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_AGODA_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_AGODA_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_AGODA_BC_RS",
					),
				),
				"Booking" => array(
					"_default" => "MSG_BASE_WARNING_BOOKING",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_BC_RS",
					),
				),
				"Airbnb" => array(
					"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_BC_RS",
					),
				),
				"Airbnbapi" => array(
					"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_BC_RS",
					),
				),
				"Googlehotel" => array(
					"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_BC_RS",
					),
				),
				"Googlevr" => array(
					"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_BC_RS",
					),
				),
				"Despegar" => array(
					"_default" => "MSG_BASE_WARNING_DESPEGAR",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_BC_RS",
					),
				),
				"Otelz" => array(
					"_default" => "MSG_BASE_WARNING_OTELZ",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_OTELZ_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_OTELZ_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_OTELZ_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_OTELZ_BC_RS",
					),
				),
				"Gardapass" => array(
					"_default" => "MSG_BASE_WARNING_GARDAPASS",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_GARDAPASS_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_GARDAPASS_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_GARDAPASS_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_GARDAPASS_BC_RS",
					),
				),
				"Bedandbreakfastit" => array(
					"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_BC_RS",
					),
				),
				"Bedandbreakfasteu" => array(
					"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_BC_RS",
					),
				),
				"Bedandbreakfastnl" => array(
					"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT_BC_RS",
					),
				),
				"Feratel" => array(
					"_default" => "MSG_BASE_WARNING_DESPEGAR",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_BC_RS",
					),
				),
				"Pitchup" => array(
					"_default" => "MSG_BASE_WARNING_DESPEGAR",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_BC_RS",
					),
				),
				"Hostelworld" => array(
					"_default" => "MSG_BASE_WARNING_DESPEGAR",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_DESPEGAR_BC_RS",
					),
				),
				"Vrboapi" => array(
					"_default" => "MSG_BASE_WARNING_BEDANDBREAKFASTIT",
					"RAR" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_RAR",
					),
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_WARNING_BOOKING_BC_RS",
					),
				),
				"NoUpdates" => array(
					"_default" => "MSG_BASE_WARNING_NOUPD",
				),
			),
			"OK" => array(
				"_default" => "MSG_BASE_SUCCESS",
				"VCMRATESPUSHOKCHRES" => array(
					"_default" => "VCMRATESPUSHOKCHRES",
				),
				"Channels" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RQ" => array(
						"_default" => "MSG_BASE_SUCCESS_CHANNELS_CUSTAR_RQ",
					),
					"AVPUSHCUSTAR_RQ" => array(
						"_default" => "MSG_BASE_SUCCESS_CHANNELS_AVPUSHCUSTAR_RQ",
					),
					"AUTOBULKCUSTAR_RQ" => array(
						"_default" => "MSG_BASE_SUCCESS_CHANNELS_AUTOBULKCUSTAR_RQ",
					),
					"AUTOBULKRAR_RQ" => array(
						"_default" => "MSG_BASE_SUCCESS_CHANNELS_AUTOBULKRAR_RQ",
					),
					"VCMRATESPUSHOKCHRES" => array(
						"_default" => "VCMRATESPUSHOKCHRES",
					),
					"NewBookingDownloaded" => array(
						"_default" => "MSG_BASE_SUCCESS_CHANNELS_NEWBOOKINGDOWNLOADED",
					),
					"BookingModified" => array(
						"_default" => "MSG_BASE_SUCCESS_CHANNELS_BOOKINGMODIFIED",
					),
					"BookingCancelled" => array(
						"_default" => "MSG_BASE_SUCCESS_CHANNELS_BOOKINGCANCELLED",
					),
				),
				"Expedia" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_EXPEDIA_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_EXPEDIA_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_EXPEDIA_BC_RS",
					),
				),
				"Agoda" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_AGODA_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_AGODA_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_AGODA_BC_RS",
					),
				),
				"Booking" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_BC_RS",
					),
				),
				"Airbnb" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_BC_RS",
					),
					"ACCPRR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_AIRBNB_ACCPRR_RS",
					),
					"DENPRR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_AIRBNB_DENPRR_RS",
					),
				),
				"Airbnbapi" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_BC_RS",
					),
					"ACCPRR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_AIRBNB_ACCPRR_RS",
					),
					"DENPRR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_AIRBNB_DENPRR_RS",
					),
				),
				"Googlehotel" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_BC_RS",
					),
				),
				"Googlevr" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_BC_RS",
					),
				),
				"Despegar" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_BC_RS",
					),
				),
				"Otelz" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_BC_RS",
					),
				),
				"Gardapass" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_BC_RS",
					),
				),
				"Bedandbreakfastit" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_BC_RS",
					),
				),
				"Bedandbreakfasteu" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_BC_RS",
					),
				),
				"Bedandbreakfastnl" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BEDANDBREAKFASTIT_BC_RS",
					),
				),
				"Feratel" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_BC_RS",
					),
				),
				"Pitchup" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_BC_RS",
					),
				),
				"Hostelworld" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_DESPEGAR_BC_RS",
					),
				),
				"Vrboapi" => array(
					"_default" => "MSG_BASE_SUCCESS",
					"CUSTAR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_CUSTAR_RS",
					),
					"AR_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_AR_RS",
					),
					"BC_RS" => array(
						"_default" => "MSG_BASE_SUCCESS_BOOKING_BC_RS",
					),
				),
			),
		),
	);
}
