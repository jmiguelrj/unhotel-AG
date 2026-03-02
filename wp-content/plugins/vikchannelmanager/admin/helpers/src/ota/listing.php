<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * OTA Listing implementation.
 * 
 * @since   1.9.2
 */
final class VCMOtaListing
{
    /**
     * @var  array
     */
    private $options;

    /**
     * @var     array
     * 
     * @since   1.9.4
     */
    private $first_account_details = [];

    /**
     * Proxy to construct the object.
     * 
     * @return  self
     */
    public static function getInstance(array $options = [])
    {
        return new static($options);
    }

    /**
     * Class constructor.
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Fetches the details of a remote OTA listing.
     * 
     * @param   string  $ota_id     The listing ID on the OTA.
     * @param   array   $account    The OTA host account details.
     * @param   bool    $save       Whether to store the OTA room data on the DB (only for some channels).
     * 
     * @return  object              The remote listing details fetched from the OTA.
     * 
     * @throws  Exception
     */
    public function fetchRemoteDetails($ota_id, array $account, $save = false)
    {
        $dbo = JFactory::getDbo();

        $remote_channel = VikChannelManager::getChannel($account['channel_id'] ?? 0);
        $apikey = VikChannelManager::getApiKey(true);

        if (!$remote_channel) {
            throw new Exception('Remote channel not found.', 500);
        }

        if ($remote_channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {

            try {
                // fetch the listing details from Airbnb
                $listing_data = $this->getAirbnbListingDetails($account, $ota_id);
            } catch (Exception $e) {
                // propagate the error
                throw new Exception(sprintf('Listing details - %s', $e->getMessage()), $e->getCode() ?: 500);
            }

            if ($save) {
                // store the listing details onto the database

                // check if the record exists
                $item = $this->getItem([
                    'idchannel'   => (int) $remote_channel['uniquekey'],
                    'account_key' => ($account['user_id'] ?? ''),
                    'idroomota'   => $ota_id,
                    'param'       => 'listing_content',
                ], ['id', 'setting']);

                if ($item) {
                    // merge any eventually missing property
                    $prev_listing_data = (object) json_decode($item->setting);
                    foreach ($prev_listing_data as $prop => $val) {
                        if (substr($prop, 0, 1) == '_' && !property_exists($listing_data, $prop)) {
                            // protected/reserved listing property should be re-added
                            $listing_data->{$prop} = $val;
                        }
                    }
                    $item->setting = json_encode($listing_data);
                } else {
                    // build a new record
                    $item = [
                        'idchannel'   => (int) $remote_channel['uniquekey'],
                        'account_key' => ($account['user_id'] ?? ''),
                        'idroomota'   => $ota_id,
                        'param'       => 'listing_content',
                        'setting'     => json_encode($listing_data),
                    ];
                    $item = (object) $item;
                }

                // create or update listing details
                $this->saveItem($item);
            }

            return $listing_data;
        }

        if ($remote_channel['uniquekey'] == VikChannelManagerConfig::BOOKING) {

            try {
                // fetch the property details in JSON format
                $property_details = $this->getBookingcomHotelDetails($account);
            } catch (Exception $e) {
                // propagate the error
                throw new Exception(sprintf('Hotel info - %s', $e->getMessage()), $e->getCode() ?: 500);
            }

            try {
                // fetch the listing details in JSON format
                $listing_details = $this->getBookingcomListingDetails($account, $ota_id);
            } catch (Exception $e) {
                // propagate the error
                throw new Exception(sprintf('Room details - %s', $e->getMessage()), $e->getCode() ?: 500);
            }

            $listing_data = new stdClass;
            $listing_data->property = $property_details;
            $listing_data->listing  = $listing_details;

            if ($save) {
                /**
                 * For the moment we do not save the downloaded listing details due to the
                 * upcoming changes made to the HDCN endpoint of Booking.com and its migration
                 * to the Modularized Content APIs in JSON format. The property details management
                 * functions in VCM still expect the property details to be in XML format.
                 * 
                 * @todo  implement the saving functionalities as soon as the VCM property details
                 *        management interface will also support the new JSON format rather than XML.
                 */
            }

            return $listing_data;
        }

        throw new Exception('Unsupported channel for fetching the remote listing details.', 500);
    }

    /**
     * Retrieves the hotel information from Booking.com.
     * 
     * @param   array   $account    The OTA host account details.
     * 
     * @return  object              The remote hotel info fetched.
     * 
     * @throws  Exception
     */
    public function getBookingcomHotelDetails(array $account = [])
    {
        if (!$account) {
            // get the account details from the first room mapped
            $account = $this->getFirstAccountDetails(VikChannelManagerConfig::BOOKING);
        }

        if (!$account) {
            throw new Exception('No active accounts found for Booking.com.', 500);
        }

        // fetch the property details from Booking.com
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/hotel-info/' . ($account['hotelid'] ?? 0);
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/hotel-info/' . ($account['hotelid'] ?? 0);
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/hotel-info/' . ($account['hotelid'] ?? 0);
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');
        // request the response to be in JSON format rather than XML, to use their most recent APIs (Modularized Content API)
        $transporter->setHttpHeader(['Accept: application/json'], $replace = false);

        try {
            // return the property details in JSON format
            return $transporter->fetch('GET', 'json');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Hotel info - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Retrieves the listing details from Booking.com.
     * 
     * @param   array   $account     The OTA host account details.
     * @param   string  $listing_id  The OTA listing ID to fetch.
     * 
     * @return  object               The remote listing details fetched.
     * 
     * @throws  Exception
     */
    public function getBookingcomListingDetails(array $account, $listing_id)
    {
        if (!$account || !$listing_id) {
            throw new InvalidArgumentException('Invalid arguments for fetching the listing details.', 400);
        }

        // fetch the listing details from Booking.com
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/room-types/' . ($account['hotelid'] ?? 0) . '/room/' . $listing_id;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/room-types/' . ($account['hotelid'] ?? 0) . '/room/' . $listing_id;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/room-types/' . ($account['hotelid'] ?? 0) . '/room/' . $listing_id;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        try {
            // fetch the listing details in JSON format
            return $transporter->fetch('GET', 'json');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Room details - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Retrieves the listing details from Airbnb (one or more).
     * 
     * @param   array   $account     The OTA host account details.
     * @param   string  $listing_id  The OTA listing ID to fetch.
     * 
     * @return  array|object         The remote listing(s) details fetched.
     * 
     * @throws  Exception
     */
    public function getAirbnbListingDetails(array $account, $listing_id = '')
    {
        if (!$account || !$listing_id) {
            throw new InvalidArgumentException('Invalid arguments for fetching the listing details.', 400);
        }

        // fetch the listing details from Airbnb
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/?r=getlst&c=airbnbapi&_limit=' . ($this->options['limit'] ?? 30) . '&_offset=' . ($this->options['offset'] ?? 0);
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/?r=getlst&c=airbnbapi&_limit=' . ($this->options['limit'] ?? 30) . '&_offset=' . ($this->options['offset'] ?? 0);
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/?r=getlst&c=airbnbapi&_limit=' . ($this->options['limit'] ?? 30) . '&_offset=' . ($this->options['offset'] ?? 0);
                break;
        }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager GETLST Request e4jConnect.com - Airbnbapi -->
<ManageListingsRQ xmlns="http://www.e4jconnect.com/channels/mnglstrq">
    <Notify client="' . JUri::root() . '"/>
    <Api key="' . VikChannelManager::getApiKey(true) . '"/>
    <Fetch hotelid="' . ($account['user_id'] ?? '') . '"/>
    <ReadListings' . ($listing_id ? ' id="' . $listing_id . '"' : '') . '>
        <ReadListing type="listings"></ReadListing>
        <ReadListing type="descriptions"></ReadListing>
        <ReadListing type="photos"></ReadListing>
        <ReadListing type="rooms"></ReadListing>
        <ReadListing type="bookingsettings"></ReadListing>
        <ReadListing type="availabilityrules"></ReadListing>
        <ReadListing type="pricingsettings"></ReadListing>
    </ReadListings>
</ManageListingsRQ>';

        $e4jC = new E4jConnectRequest($endpoint);
        $e4jC->setPostFields($xml);
        $e4jC->setTimeout($this->options['timeout'] ?? 180);
        $e4jC->slaveEnabled = (bool) ($this->options['slave_enabled'] ?? 1);
        $rs = $e4jC->exec();

        if ($e4jC->getErrorNo()) {
            throw new Exception(@curl_error($e4jC->getCurlHeader()), 500);
        }

        if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
            throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
        }

        $listings_data = json_decode($rs);
        if (!is_array($listings_data) || !$listings_data) {
            throw new Exception('The remote JSON response could not be decoded.', 500);
        }

        return $listing_id ? $listings_data[0] : $listings_data;
    }

    /**
     * Creates a new property on Booking.com from the provided parameters.
     * 
     * @param   array   $params     The hotel details for creating the new property.
     * 
     * @return  string              The newly generated Hotel ID.
     * 
     * @throws  Exception
     */
    public function createBookingcomProperty(array $params = [])
    {
        if (!($params['legal_id'] ?? '')) {
            throw new InvalidArgumentException('Missing Booking.com legal entity ID.', 400);
        }

        if (empty($params['name'])) {
            throw new InvalidArgumentException('Missing property name.', 400);
        }

        // create a new property on Booking.com
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/property';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/property';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/property';
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($params);

        try {
            // obtain the new property ID from the request
            return $transporter->fetch('POST', 'json')->property_id;
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('New property - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Creates a new room-type on Booking.com from the provided parameters.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * @param   array   $params     The details for creating the new room-type.
     * 
     * @return  string              The newly generated room-type ID.
     * 
     * @throws  Exception
     */
    public function createBookingcomRoomType($hotel_id, array $params = [])
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID for creating a new room-type.', 400);
        }

        if (empty($params['title'])) {
            throw new InvalidArgumentException('Missing room-type name for creating a new room-type.', 400);
        }

        // create a new room-type on Booking.com
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/room-types/' . $hotel_id;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/room-types/' . $hotel_id;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/room-types/' . $hotel_id;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($params);

        try {
            // obtain the new room-type ID from the request
            $inv = $transporter->fetch('POST', 'xml');
            return (string) $inv->InventoryCrossRefs->InventoryCrossRef->attributes()->ResponseInvCode;
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('New room-type - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Uploads a list of photo URLs onto Booking.com for a specific Hotel ID.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * @param   array   $photos     List of photo URLs to upload.
     * 
     * @return  object              The batch upload result.
     * 
     * @throws  Exception
     */
    public function uploadBookingcomPropertyPhotos($hotel_id, array $photos)
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID for uploading photos.', 400);
        }

        // upload photos for Booking.com property
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/photos/' . $hotel_id;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/photos/' . $hotel_id;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/photos/' . $hotel_id;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($photos);

        try {
            // obtain the photos batch upload object
            return $transporter->fetch('POST', 'json');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Property photos - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Creates a rate plan on Booking.com for the given Hotel ID.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * @param   array   $rate_plan  The rate plan associative information (name).
     * 
     * @return  string              The ID of the created rate plan.
     * 
     * @throws  Exception
     */
    public function createBookingcomRatePlan($hotel_id, array $rate_plan)
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID for creating a new rate plan.', 400);
        }

        // create a new rate plan on Booking.com
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/rate-plans/' . $hotel_id;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/rate-plans/' . $hotel_id;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/rate-plans/' . $hotel_id;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields([
                'create' => [$rate_plan],
            ]);

        try {
            // create the rate plan
            $results = $transporter->fetch('PUT', 'json');

            $xml = simplexml_load_string($results->create[0]);

            // access the newly created rate plan ID
            return (string) $xml->RatePlanCrossRefs->RatePlanCrossRef->attributes()->ResponseRatePlanCode;
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Create rate plan - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Fetches the rate plans from the given hotel ID according to filters.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * @param   array   $filters    Associative list of filters to apply.
     * 
     * @return  object[]            List of rate plan objects.
     * 
     * @throws  Exception
     */
    public function fetchBookingcomRatePlans($hotel_id, array $filters = [])
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID for fetching the rate plans.', 400);
        }

        // fetch the rate plan details from Booking.com
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/rate-plans/' . $hotel_id;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/rate-plans/' . $hotel_id;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/rate-plans/' . $hotel_id;
                break;
        }

        $ota_rate_plans = [];

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'text/xml');

        try {
            // fetch the rate plans in XML format
            $rate_plans = $transporter->fetch('GET', 'xml');
            foreach ($rate_plans->rate as $rate_plan) {
                $rate_plan_attr = $rate_plan->attributes();
                $ota_rate_plan = [
                    'id'       => (string) ($rate_plan_attr->id ?? ''),
                    'name'     => (string) $rate_plan,
                    'active'   => (int) ($rate_plan_attr->active ?? 1),
                    'is_child' => (int) ($rate_plan_attr->is_child_rate ?? 0),
                ];
                $ota_rate_plans[] = (object) $ota_rate_plan;
            }
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Fetch rate plans - %s', $e->getMessage()), $e->getCode() ?: 500);
        }

        // filter rate plans
        $ota_rate_plans = array_filter($ota_rate_plans, function($rate_plan) use ($filters) {
            // filter by active status
            if (!is_null($filters['active'] ?? null)) {
                if ($filters['active']) {
                    // only active rate plans
                    return (bool) $rate_plan->active;
                }

                // only inactive rate plans
                return !((bool) $rate_plan->active);
            }

            // filter by parent or child rates
            if (!is_null($filters['parent'] ?? null)) {
                if ($filters['parent']) {
                    // only parent rate plans
                    return !((bool) $rate_plan->is_child);
                }

                // only child rate plans
                return (bool) $rate_plan->is_child;
            }

            return true;
        });

        if ($filters['name'] ?? '') {
            // sort rate plans by closest name
            $match_name = $filters['name'];

            // map every rate plan object with the similarity to the desired name
            $ota_rate_plans = array_map(function($rate_plan) use ($match_name) {
                similar_text($match_name, $rate_plan->name, $similarity);
                $rate_plan->match = $similarity;
                return $rate_plan;
            }, $ota_rate_plans);

            // sort room names by similarity
            usort($ota_rate_plans, function($a, $b) {
                return $b->match <=> $a->match;
            });

            // get rid of the match-similarity property
            $ota_rate_plans = array_map(function($rate_plan) {
                unset($rate_plan->match);
                return $rate_plan;
            }, $ota_rate_plans);
        }

        return array_values($ota_rate_plans);
    }

    /**
     * Creates a new room-rate relation on Booking.com for the given hotel, room and rate plan IDs.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * @param   string  $room_id    The Booking.com room-type ID.
     * @param   string  $rate_id    The Booking.com rate-plan ID.
     * 
     * @return  true
     * 
     * @throws  Exception
     */
    public function createBookingcomRoomRate($hotel_id, $room_id, $rate_id, array $rate_data = [])
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID for creating a room-rate.', 400);
        }

        if (empty($room_id)) {
            throw new InvalidArgumentException('Missing Booking.com room-type ID for creating a room-rate.', 400);
        }

        if (empty($rate_id)) {
            throw new InvalidArgumentException('Missing Booking.com rate-plan ID for creating a room-rate.', 400);
        }

        // create a new room-rate relation on Booking.com
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/room-rates/' . $hotel_id;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/room-rates/' . $hotel_id;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/room-rates/' . $hotel_id;
                break;
        }

        // build room rate payload
        $room_rate = [
            'room_id' => $room_id,
            'rateplan_id' => $rate_id,
            'meal_code' => $rate_data['meal_code'] ?? null,
            'canc_code' => $rate_data['canc_code'] ?? null,
        ];

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields([
                'create' => [$room_rate],
            ]);

        try {
            // create the room rate relation
            $transporter->fetch('PUT', '');

            // request was successful
            return true;
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Create room rate relation - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Fetches the room-rates from a Booking.com account and maps them into a list.
     * 
     * @param   array   $params    The details for fetching the room rates.
     * 
     * @return  array              Associative list of room rates information.
     * 
     * @throws  Exception
     */
    public function fetchBookingcomRoomRates(array $params = [])
    {
        if (!($params['hotel_id'] ?? '')) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID for fetching the room rates.', 400);
        }

        // pool of room rates
        $room_rates_pool = [];

        // create a new room-type on Booking.com
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/room-rates/' . $params['hotel_id'];
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/room-rates/' . $params['hotel_id'];
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/room-rates/' . $params['hotel_id'];
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        try {
            // get the list of room-rates in XML format
            $room_rates = $transporter->fetch('GET', 'xml');

            foreach ($room_rates->rooms->room as $room) {
                // room-type attributes
                $room_attributes = $room->attributes();

                if (!isset($room->rates->rate)) {
                    // this room-type has got no rate plans assigned
                    continue;
                }

                // room-type details
                $room_id    = (string) $room_attributes->id;
                $room_name  = (string) $room_attributes->room_name;
                $hotel_name = (string) $room_attributes->hotel_name;

                // push room rate data
                $room_rates_pool[$room_id] = [
                    'id'         => $room_id,
                    'name'       => $room_name,
                    'hotel_name' => $hotel_name,
                    'rate_plans' => [],
                ];

                foreach ($room->rates->rate as $rate) {
                    // room-rate attributes
                    $rate_attributes = $rate->attributes();

                    // rate-plan details
                    $rate_id     = (string) $rate_attributes->id;
                    $rate_name   = (string) $rate_attributes->rate_name;
                    $max_persons = (int) ($rate_attributes->max_persons ?? 1);
                    $policy      = (string) ($rate_attributes->policy ?? '');
                    $policy_id   = (string) ($rate_attributes->policy_id ?? '');
                    $meal_plan   = (string) ($rate->meal_plan->attributes()->meal_plan_code ?? '');
                    $pmodel      = (string) ($rate->pricing->attributes()->type ?? 'OBP');

                    // set rate plan information
                    $room_rates_pool[$room_id]['rate_plans'][$rate_id] = [
                        'id'          => $rate_id,
                        'name'        => $rate_name,
                        'max_persons' => $max_persons,
                        'policy'      => $policy,
                        'policy_id'   => $policy_id,
                        'meal_plans'  => VCMBookingcomContent::getIncludedMeals($meal_plan),
                        'pmodel'      => $pmodel,
                    ];
                }
            }
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Get room rates - %s', $e->getMessage()), $e->getCode() ?: 500);
        }

        if ($params['ota_room_id'] ?? null) {
            return $room_rates_pool[$params['ota_room_id']] ?? $room_rates_pool;
        }

        return $room_rates_pool;
    }

    /**
     * Returns an associative list of Booking.com cancellation policy codes defined for a Hotel ID.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * 
     * @return  array               List of defined cancellation policy codes.
     * 
     * @throws  Exception
     */
    public function getBookingcomCancPolicyCodes($hotel_id)
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID.', 400);
        }

        // request the hotel information
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/hotel-info/' . $hotel_id;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/hotel-info/' . $hotel_id;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/hotel-info/' . $hotel_id;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');
        // request the response to be in XML format (OTA_HotelDescriptiveInfo) for the cancellation policies
        $transporter->setHttpHeader(['Accept: text/xml'], $replace = false);

        try {
            // return the property details in XML format
            $hdi_rs = $transporter->fetch('GET', 'xml');

            if (!($hdi_rs->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->CancelPolicy->CancelPenalty ?? null)) {
                throw new Exception('No cancellation policy codes found.', 500);
            }

            $canc_policy_codes = [];

            foreach ($hdi_rs->HotelDescriptiveContents->HotelDescriptiveContent->Policies->Policy->CancelPolicy->CancelPenalty as $cancpenalty) {
                $cancpenalty_attr = $cancpenalty->attributes();

                // push defined cancellation policy code and related information
                $canc_policy_codes[(string) $cancpenalty_attr->PolicyCode] = [
                    'code'  => (string) $cancpenalty_attr->PolicyCode,
                    'name'  => (string) $cancpenalty_attr->PolicyName,
                    'descr' => (string) $cancpenalty_attr->Description,
                ];
            }

            return $canc_policy_codes;
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Hotel info - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Returns the contact detail(s) of a given Booking.com Hotel ID and (optional) type.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * 
     * @return  object|array        The contact detail(s) for the type requested.
     * 
     * @throws  Exception
     * 
     * @since   1.9.4
     */
    public function getBookingcomContactInfos($hotel_id = '')
    {
        if (empty($hotel_id)) {
            // get the account details from the first room mapped
            $account = $this->getFirstAccountDetails(VikChannelManagerConfig::BOOKING);
            $hotel_id = $account['hotelid'] ?? '';
        }

        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID.', 400);
        }

        // the type of contact to fetch (general by default)
        $contact_type = $this->options['contact_type'] ?? 'general';

        if ($this->options['any_contact'] ?? false) {
            // all contact types
            $contact_type = '';
        }

        // request the hotel information
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/contacts/' . $contact_type;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/contacts/' . $contact_type;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/contacts/' . $contact_type;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        try {
            // return the contact details in JSON
            $contact_rs = $transporter->fetch('GET', 'json');

            if ($contact_type && !($contact_rs->contact_person ?? null)) {
                throw new Exception(sprintf('No contact information (%s) found for hotel ID %s.', $contact_type, $hotel_id), 500);
            }

            if (!$contact_type && !$contact_rs) {
                throw new Exception(sprintf('No contact information found for hotel ID %s.', $hotel_id), 500);
            }

            return $contact_rs;
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Get property contact details - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Updates the contact details for a given Booking.com Hotel ID.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * @param   array   $contacts   The list of contact details to set.
     * 
     * @return  void
     * 
     * @throws  Exception
     * 
     * @since   1.9.4
     */
    public function setBookingcomContactInfos($hotel_id, array $contacts)
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID.', 400);
        }

        // perform the request
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/contacts';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/contacts';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/contacts';
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($contacts);

        try {
            // update the contact details
            $transporter->fetch('PUT');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Set property contact details - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Triggers the hotel summary notification to check, open or close a given property ID.
     * 
     * @param   string  $hotel_id   The Booking.com hotel ID.
     * @param   string  $status     The status enumeration to set (Check, Open, Closed).
     * 
     * @return  void
     * 
     * @throws  Exception
     * 
     * @since   1.9.4
     */
    public function triggerBookingcomHotelStatus($hotel_id, $status = '')
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID.', 400);
        }

        $status_enums = [
            'Check',
            'Open',
            'Closed',
        ];

        $status = in_array($status, $status_enums) ? $status : 'Check';

        // perform the request
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/status';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/status';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/status';
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            // request the response to be in JSON format rather than XML, to use their most recent APIs (Modularized Content API)
            ->setHttpHeader(['Accept: application/json'], $replace = false)
            ->setPostFields(['status' => $status]);

        try {
            // trigger hotel summary with given status
            $transporter->fetch('PUT', 'json');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Hotel summary - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Returns the property settings of a given Booking.com Hotel ID.
     * 
     * @param   string  $hotel_id  The Booking.com hotel ID.
     * 
     * @return  object  The booking settings.
     * 
     * @throws  Exception
     * 
     * @since   1.9.15
     */
    public function getBookingcomPropertySettings($hotel_id = '')
    {
        if (empty($hotel_id)) {
            // get the account details from the first room mapped
            $account = $this->getFirstAccountDetails(VikChannelManagerConfig::BOOKING);
            $hotel_id = $account['hotelid'] ?? '';
        }

        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID.', 400);
        }

        // request the hotel information
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/settings/';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/settings/';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/settings/';
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        try {
            // return the property settings in JSON
            return $transporter->fetch('GET', 'json');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Get property settings - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Updates the property settings of a given Booking.com Hotel ID.
     * 
     * @param   string        $hotel_id  The Booking.com hotel ID.
     * @param   object|array  $settings  The property settings to apply. 
     * 
     * @return  void
     * 
     * @throws  Exception
     * 
     * @since   1.9.15
     */
    public function setBookingcomPropertySettings($hotel_id, $settings)
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID.', 400);
        }

        // request the hotel information
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/settings/';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/settings/';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/property/' . $hotel_id . '/settings/';
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($settings);

        try {
            // update the property settings in JSON
            $transporter->fetch('POST', 'json');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Set property settings - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Updates the property facilities of a given Booking.com Hotel ID.
     * 
     * @param   string  $hotel_id    The Booking.com hotel ID.
     * @param   string  $room_id     The room ID. If not specified, the facility will be applied at property level.
     * @param   array   $facilities  The facilities to apply. 
     * 
     * @return  void
     * 
     * @throws  Exception
     * 
     * @since   1.9.15
     */
    public function setBookingcomPropertyFacilities($hotel_id, $room_id, $facilities)
    {
        if (empty($hotel_id)) {
            throw new InvalidArgumentException('Missing Booking.com hotel ID.', 400);
        }

        // request the hotel information
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/bookingcom/facilities/' . $hotel_id;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/bookingcom/facilities/' . $hotel_id;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/bookingcom/facilities/' . $hotel_id;
                break;
        }

        if ($room_id) {
            $endpoint .= '/room/' . $room_id;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($facilities);

        try {
            // update the property/room facilities in JSON
            $transporter->fetch('PUT', 'json');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Set property/room facilities - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Returns a matching (or default) Booking.com cancellation policy code from
     * a given cancellation policy name, usually taken from an Airbnb listing.
     * 
     * @param   string  $policy_name    The optional policy name to match.
     * 
     * @return  string                  The matching or default cancellation policy code.
     */
    public function guessBookingcomCancPolicy($policy_name = null)
    {
        // default policy for Booking.com
        $def_policy_code = '152';

        if (!$policy_name) {
            // fully flexible
            return $def_policy_code;
        }

        if ((stripos($policy_name, 'super') !== false && stripos($policy_name, 'strict') !== false) || (stripos($policy_name, 'non') !== false && stripos($policy_name, 'refundable') !== false)) {
            // non-refundable
            return '1';
        }

        if (stripos($policy_name, 'strict') !== false) {
            // can cancel free of charge up to 14 days before arrival
            return '14';
        }

        if (stripos($policy_name, 'moderate') !== false) {
            // can cancel free of charge up to 5 days before arrival
            return '16';
        }

        if (stripos($policy_name, 'flexible') !== false) {
            // fully flexible
            return '152';
        }

        // fully flexible
        return $def_policy_code;
    }

    /**
     * Creates a new listing on Airbnb.
     * 
     * @param   string  $hostId     The Airbnb host account ID.
     * @param   array   $params     The listing details for the creation.
     * 
     * @return  string              The newly generated listing ID.
     * 
     * @throws  Exception
     */
    public function createAirbnbListing($hostId, array $params = [])
    {
        if (empty($params['name'])) {
            throw new InvalidArgumentException('Missing listing name.', 400);
        }

        // create a new listing on Airbnb
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($params);

        try {
            // obtain the new listing ID from the request
            return $transporter->fetch('POST', 'json')->id;
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('New listing - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Updates a listing on Airbnb.
     * 
     * @param   string  $hostId     The Airbnb host account ID.
     * @param   string  $listingId  The Airbnb listing ID to update.
     * @param   array   $params     The listing details for the update.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function updateAirbnbListing($hostId, $listingId, array $params = [])
    {
        // updates a listing on Airbnb
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($params);

        try {
            // attempt to update the information (do not expect a response, hence no decoding)
            $transporter->fetch('PUT', '');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Update listing - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Sets the descriptions for an Airbnb listing.
     * 
     * @param   string  $hostId     The Airbnb host account ID.
     * @param   string  $listingId  The Airbnb listing ID to update.
     * @param   string  $locale     The descriptions locale.
     * @param   array   $params     The listing descriptions data.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function setAirbnbListingDescriptions($hostId, $listingId, $locale, array $params = [])
    {
        // updates the listing descriptions on Airbnb
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/descriptions/' . $locale;
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/descriptions/' . $locale;
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/descriptions/' . $locale;
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($params);

        try {
            // attempt to set the information (do not expect a response, hence no decoding)
            $transporter->fetch('PUT', '');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Set listing descriptions - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Uploads one or multiple photos for an Airbnb listing.
     * 
     * @param   string  $hostId         The Airbnb host account ID.
     * @param   string  $listingId      The Airbnb listing ID to update.
     * @param   array   $photoPaths     List of photo path objects on the CMS.
     * @param   int     $lim            Optional limit of photos to upload.
     * @param   bool    $strict         If true and no photos are eligible, an Exception is thrown.
     * 
     * @return  int                     Number of photos uploaded.
     * 
     * @throws  Exception
     */
    public function uploadAirbnbListingPhotos($hostId, $listingId, array $photoPaths, $lim = 0, $strict = true)
    {
        // uploads one listing photo on Airbnb per request
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/photos';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/photos';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/photos';
                break;
        }

        $photosUploaded = 0;
        $uploadErrors = [];
        $attempted = 0;

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        foreach ($photoPaths as $photo) {
            // always cast to object to support associative arrays
            $photo = (object) $photo;

            if ($lim > 0 && $photosUploaded > $lim) {
                // enough photos were uploaded
                break;
            }

            // ensure the size is acceptable (20MB)
            if (!is_file($photo->path) || filesize($photo->path) > (1024 * 1024 * 20)) {
                // invalid file
                continue;
            }

            // ensure the image format is accepted
            if (!preg_match("/\.jpe?g$/i", $photo->path) && !preg_match("/\.png$/i", $photo->path)) {
                // unsupported image type
                continue;
            }

            // photo image is eligible for upload
            $attempted++;

            // set image data in base64 string format, and an optional caption
            $transporter->setPostFields([
                'image' => base64_encode(file_get_contents($photo->path)),
                'caption' => $photo->caption ?? null,
            ]);

            try {
                // attempt to post data
                $transporter->fetch('POST', 'json');

                // increase counter
                $photosUploaded++;
            } catch (Exception $e) {
                // catch and push the error
                $uploadErrors[] = $e;
            }
        }

        if ($photoPaths && !$attempted && $strict === true) {
            throw new Exception('No photos are eligible for upload. Maximum size is 20MB. Only PNG and JPG image types are allowed.', 500);
        }

        if ($photoPaths && $uploadErrors && !$photosUploaded) {
            $lastError = end($uploadErrors);
            throw new Exception(sprintf('Upload listing photos (%s) - %s', basename($photo->path), $lastError->getMessage()), $lastError->getCode() ?: 500);
        }

        return $photosUploaded;
    }

    /**
     * Updates the listing booking settings on Airbnb.
     * 
     * @param   string  $hostId     The Airbnb host account ID.
     * @param   string  $listingId  The Airbnb listing ID to update.
     * @param   array   $params     The listing booking settings.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function updateAirbnbListingBookingSettings($hostId, $listingId, array $params)
    {
        // updates the listing booking settings on Airbnb
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/booking-settings';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/booking-settings';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/booking-settings';
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($params);

        try {
            // attempt to set the information (no response should be decoded due to empty response in case of 200)
            $transporter->fetch('PUT', '');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Update listing booking settings - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Updates the listing PnA model on Airbnb.
     * 
     * @param   string  $hostId     The Airbnb host account ID.
     * @param   string  $listingId  The Airbnb listing ID to update.
     * @param   array   $params     The listing PnA model data.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function setAirbnbListingPnAModel($hostId, $listingId, array $params)
    {
        // updates the listing PnA model on Airbnb
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/pna';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/pna';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/pna';
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($params);

        try {
            // attempt to set the information (no response should be decoded due to empty response in case of 200)
            $transporter->fetch('PUT', '');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Set PnA Model - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Updates the listing pricing settings on Airbnb.
     * 
     * @param   string  $hostId     The Airbnb host account ID.
     * @param   string  $listingId  The Airbnb listing ID to update.
     * @param   array   $params     The listing pricing settings.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function setAirbnbListingPricingSettings($hostId, $listingId, array $params)
    {
        // updates the listing pricing settings on Airbnb
        switch ($this->options['server'] ?? '') {
            case 'slave':
                $endpoint = 'https://slave.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/pricing-settings';
                break;
            case 'hotels':
                $endpoint = 'https://hotels.e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/pricing-settings';
                break;
            default:
                $endpoint = 'https://e4jconnect.com/channelmanager/v2/airbnb/listings/' . $hostId . '/listing/' . $listingId . '/pricing-settings';
                break;
        }

        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        if ($params['listing_currency'] ?? null) {
            // the currency field cannot be saved with other price fields
            $transporter->setPostFields(['listing_currency' => $params['listing_currency']]);

            // unset the listing currency value
            unset($params['listing_currency']);

            // set the currency with a separate request
            try {
                // attempt to set the information (no response should be decoded due to empty response in case of 200)
                $transporter->fetch('PUT', '');
            } catch (Exception $e) {
                // propagate the error
                throw new Exception(sprintf('Set pricing settings - %s', $e->getMessage()), $e->getCode() ?: 500);
            }

            if (!$params) {
                // all param fields were fulfilled
                return;
            }
        }

        // register fields to set
        $transporter->setPostFields($params);

        try {
            // attempt to set the information (no response should be decoded due to empty response in case of 200)
            $transporter->fetch('PUT', '');
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Set pricing settings - %s', $e->getMessage()), $e->getCode() ?: 500);
        }
    }

    /**
     * Returns the account information from the first room mapped under the given account.
     * 
     * @param   string|int  $id_channel     The channel identifier.
     * 
     * @return  array
     */
    public function getFirstAccountDetails($id_channel)
    {
        if ($this->first_account_details[$id_channel] ?? []) {
            return $this->first_account_details[$id_channel];
        }

        $dbo = JFactory::getDbo();

        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select($dbo->qn('prop_params'))
                ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                ->where($dbo->qn('idchannel') . ' = ' . (int) $id_channel)
                ->order($dbo->qn('id') . ' DESC')
        , 0, 1);

        // cache value
        $this->first_account_details[$id_channel] = (array) json_decode((string) $dbo->loadResult(), true);

        return $this->first_account_details[$id_channel];
    }

    /**
     * Retrieves the highest listing cost from the rates table.
     * 
     * @param   int     $room_id    The VikBooking room ID.
     * @param   int     $def_cost   The default cost to apply.
     * 
     * @return  int|float
     */
    public function getListingMaxCost($room_id, $def_cost = 300)
    {
        $dbo = JFactory::getDbo();

        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select('MAX(' . $dbo->qn('cost') . ')')
                ->from($dbo->qn('#__vikbooking_dispcost'))
                ->where($dbo->qn('idroom') . ' = ' . (int) $room_id)
        );

        $max_cost = $dbo->loadResult();

        if (!$max_cost) {
            $max_cost = $def_cost;
        }

        return $max_cost;
    }

    /**
     * Gets the requested items and columns based on the provided filters.
     * 
     * @param   array   $filters    Associative list of search filters.
     * @param   array   $columns    Optional list of columns to fetch.
     * @param   array   $orderings  Optional associative list of ordering values.
     * 
     * @return  array
     * 
     * @since   1.9.14
     */
    public function getItems(array $filters, array $columns = [], array $orderings = [])
    {
        $dbo = JFactory::getDbo();

        $q = $dbo->getQuery(true);

        if (!$columns) {
            $q->select('*');
        } else {
            foreach ($columns as $col) {
                $q->select($dbo->qn($col));
            }
        }

        $q->from($dbo->qn('#__vikchannelmanager_otarooms_data'));

        foreach ($filters as $col => $val) {
            $q->where($dbo->qn($col) . ' = ' . $dbo->q($val));
        }

        foreach ($orderings as $col => $type) {
            $q->order($dbo->qn($col) . ' ' . $type);
        }

        $dbo->setQuery($q);

        return $dbo->loadObjectList();
    }

    /**
     * Gets the requested item columns based on the provided filters.
     * 
     * @param   array   $filters    Associative list of search filters.
     * @param   array   $columns    Optional list of columns to fetch.
     * 
     * @return  object|null
     */
    public function getItem(array $filters, array $columns = [])
    {
        $dbo = JFactory::getDbo();

        $q = $dbo->getQuery(true);

        if (!$columns) {
            $q->select('*');
        } else {
            foreach ($columns as $col) {
                $q->select($dbo->qn($col));
            }
        }

        $q->from($dbo->qn('#__vikchannelmanager_otarooms_data'));

        foreach ($filters as $col => $val) {
            $q->where($dbo->qn($col) . ' = ' . $dbo->q($val));
        }

        $dbo->setQuery($q, 0, 1);

        return $dbo->loadObject();
    }

    /**
     * Inserts or updates an item record.
     * 
     * @param   array|object  $item   The record object/assoc-array to insert or update.
     * 
     * @return  bool
     */
    public function saveItem($item)
    {
        $dbo = JFactory::getDbo();

        $item = (object) $item;

        if ($item->id ?? null) {
            $dbo->updateObject('#__vikchannelmanager_otarooms_data', $item, 'id');

            return (bool) $dbo->getAffectedRows();
        }

        $dbo->insertObject('#__vikchannelmanager_otarooms_data', $item, 'id');

        return !empty($item->id);
    }

    /**
     * Obtains the details for the given (VBO) listing ID across all OTAs.
     * 
     * @param   int     $listingId  The VBO listing ID.
     * 
     * @return  array               Associative list of listing details.
     * 
     * @since   1.9.12
     */
    public function getDetails(int $listingId)
    {
        $dbo = JFactory::getDbo();

        // fetch the listing details from VBO
        $vbo_record = VikBooking::getRoomInfo($listingId, $columns = [], $no_cache = true);

        if (!$vbo_record) {
            // listing not found
            return [];
        }

        // build the listing details values from VBO first
        $listing_details = [
            'id'       => $vbo_record['id'],
            'name'     => $vbo_record['name'],
            'capacity' => [
                'max_adults'   => (int) $vbo_record['toadult'],
                'max_children' => (int) $vbo_record['tochild'],
                'max_guests'   => (int) $vbo_record['totpeople'],
            ],
            'short_description' => strip_tags((string) $vbo_record['smalldesc']),
            'long_description'  => strip_tags((string) $vbo_record['info']),
        ];

        // try access some location-related information from VBO
        $geo = VikBooking::getGeocodingInstance();
        $room_params = (array) json_decode((string) $vbo_record['params'], true);
        $vbo_location_data = [
            'address'   => $geo->getRoomGeoParams($room_params, 'address', ''),
            'latitude'  => $geo->getRoomGeoParams($room_params, 'latitude', ''),
            'longitude' => $geo->getRoomGeoParams($room_params, 'longitude', ''),
        ];

        // filter out empty values
        $listing_details = array_filter($listing_details);
        $vbo_location_data = array_filter($vbo_location_data);
        if ($vbo_location_data) {
            // merge location-related information
            $listing_details = array_merge($listing_details, $vbo_location_data);
        }

        /**
         * @todo add support to Booking.com
         */

        // list of OTAs with listing contents
        $otas_with_contents = [
            VikChannelManagerConfig::AIRBNBAPI => [],
            VikChannelManagerConfig::EXPEDIA => [],
        ];

        // check whether the listing is mapped on channels with contents
        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select([
                    $dbo->qn('idroomota'),
                    $dbo->qn('idchannel'),
                ])
                ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                ->where($dbo->qn('idroomvb') . ' = ' . $listingId)
                ->where($dbo->qn('idchannel') . ' IN (' . implode(', ', array_map('intval', array_keys($otas_with_contents))) . ')')
        );
        $ota_listing_ids = $dbo->loadAssocList();

        foreach ($ota_listing_ids as $ota_listing_id) {
            $otas_with_contents[$ota_listing_id['idchannel']][] = $ota_listing_id['idroomota'];
        }

        foreach ($otas_with_contents as $channel_id => $ota_room_ids) {
            foreach ($ota_room_ids as $ota_room_id) {
                // fetch listing data from OTA
                $ota_listing_item = $this->getItem([
                    'idchannel'   => (int) $channel_id,
                    'idroomota'   => $ota_room_id,
                    'param'       => 'listing_content',
                ], ['id', 'setting']);

                if (!$ota_listing_item) {
                    continue;
                }

                // normalize OTA listing details to return
                $normalized = $this->normalizeOtaListingDetails($listing_details, (array) json_decode($ota_listing_item->setting, true), (int) $channel_id);

                if ($normalized) {
                    // return the data normalized for the first eligible channel
                    return $listing_details;
                }
            }
        }

        return $listing_details;
    }

    /**
     * Given the OTA listing data payload, normalizes the contents and details
     * depending on the OTA channel ID. Useful to access OTA listing details.
     * 
     * @param   array   &$details       The current listing details.
     * @param   array   $data           Associative list of OTA listing data.
     * @param   int     $channel_id     The OTA channel identifier.
     * 
     * @return  bool                    True if the listing details were normalized.
     * 
     * @since   1.9.12
     */
    protected function normalizeOtaListingDetails(array &$details, array $data, int $channel_id)
    {
        $ota_listing_details = [];

        if ($channel_id == VikChannelManagerConfig::AIRBNBAPI) {
            // normalize the Airbnb listing contents

            $ota_listing_details = [
                'property_type_group' => $data['property_type_group'] ?? null,
                'property_type_category' => $data['property_type_category'] ?? null,
                'room_type_category' => $data['room_type_category'] ?? null,
                'bedrooms' => $data['bedrooms'] ?? null,
                'bathrooms' => $data['bathrooms'] ?? null,
                'beds' => $data['beds'] ?? null,
                'check_in_option' => $data['check_in_option'] ?? null,
                'street' => $data['street'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'zipcode' => $data['zipcode'] ?? null,
                'country_code' => $data['country_code'] ?? null,
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'directions' => $data['directions'] ?? null,
                'person_capacity' => $data['person_capacity'] ?? null,
                'house_manual' => $data['house_manual'] ?? null,
                'wifi_network' => $data['wifi_network'] ?? null,
                // 'wifi_password' => $data['wifi_password'] ?? null,
                'check_out_tasks' => $data['check_out_tasks'] ?? null,
                'property_details' => $data['property_details'] ?? null,
                'accessibility_features' => $data['accessibility_features'] ?? null,
                'amenities' => $data['amenities'] ?? $data['amenity_categories'] ?? null,
                'house_rules' => $data['_descriptions'][0]['house_rules'] ?? null,
                'expectations_for_guests' => $data['_bookingsettings']['listing_expectations_for_guests'] ?? null,
                'check_in_time_start' => $data['_bookingsettings']['check_in_time_start'] ?? null,
                'check_in_time_end' => $data['_bookingsettings']['check_in_time_end'] ?? null,
                'check_out_time' => $data['_bookingsettings']['check_out_time'] ?? null,
            ];

            if (!empty($ota_listing_details['amenities'])) {
                // filter out empty values
                foreach ($ota_listing_details['amenities'] as &$amenity) {
                    if (isset($amenity['instruction']) && $amenity['instruction'] === '') {
                        unset($amenity['instruction']);
                    }
                }

                unset($amenity);
            }

            if (!empty($data['_descriptions'][0]['description'])) {
                // use the full listing description on Airbnb
                $details['long_description'] = $data['_descriptions'][0]['description'];
            } else {
                // look up for the listing description pieces
                $descriptions = [
                    ($data['_descriptions'][0]['summary'] ?? null),
                    ($data['_descriptions'][0]['space'] ?? null),
                    ($data['_descriptions'][0]['notes'] ?? null),
                ];
                $descriptions = array_filter($descriptions);
                if ($descriptions) {
                    // use the listing description pieces on Airbnb
                    $details['long_description'] = implode("\n\n", $descriptions);
                }
            }

            if (!empty($data['_pricingsettings']['standard_fees'])) {
                $details['extra_fees'] = array_map(function($fee) {
                    // fee amounts are in micros (1/million)
                    $fee['amount'] /= pow(10, 6);
                    $fee['fee_type'] = str_ireplace('PASS_THROUGH_', '', $fee['fee_type']);
                    return $fee;
                }, $data['_pricingsettings']['standard_fees']);
            }
        } elseif ($channel_id == VikChannelManagerConfig::EXPEDIA) {
            // normalize the Expedia listing contents

            $ota_listing_details = [
                'ageCategories' => $data['ageCategories'] ?? null,
                'bedrooms' => $data['bedrooms'] ?? null,
                'maxOccupancy' => $data['maxOccupancy'] ?? null,
                'floorSize' => $data['floorSize'] ?? null,
                'wheelchairAccessibility' => $data['wheelchairAccessibility'] ?? null,
                'smokingPreferences' => $data['smokingPreferences'] ?? null,
                'amenities' => $data['_amenities'] ?? null,
            ];
        } elseif ($channel_id == VikChannelManagerConfig::BOOKING) {

            /**
             * @todo Check whether the OTA details are in XML format. If this is the case, make a request to
             *       e4jconnect to download the JSON version of the facilities. Otherwise use the cached one.
             */

        }

        // filter out empty values
        $ota_listing_details = array_filter($ota_listing_details);

        if (!$ota_listing_details) {
            // nothing was normalized
            return false;
        }

        // assign the full OTA listing details
        $details['details'] = $ota_listing_details;

        return true;
    }
}
