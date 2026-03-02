<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2024 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * VikChannelManager AI tools controller.
 *
 * @since 1.9
 */
class VikChannelManagerControllerAitools extends JControllerAdmin
{
    /**
     * End-point used by the AI Assistant to fetch all the available rooms/listings.
     * 
     * @return  void
     * 
     * @since   1.9.13
     */
    public function rooms_list()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $availabilityHelper = VikBooking::getAvailabilityInstance(true);

        // fetch rooms
        $rooms = [];

        foreach ($availabilityHelper->loadRooms() as $room)
        {
            if ($room['img']) {
                $room['img'] = VBO_SITE_URI . 'resources/uploads/' . $room['img'];
            }

            $room['published'] = (bool) $room['avail'];
            
            unset($room['idcat'], $room['avail']);

            $rooms[] = $room;
        }

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($rooms);
    }

	/**
     * End-point used to fetch room rates for the AI Assistant.
     * 
     * @return  void
     */
    public function fetch_rates()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $checkin  = $app->input->json->getString('checkin', 'now');
        $checkout = $app->input->json->getString('checkout', '+1 day');
        $guests   = $app->input->json->getUint('guests', 2);
        $room     = $app->input->json->getString('room', '');

        // normalize check-in and check-out dates
        $checkin  = JFactory::getDate($checkin);
        $checkout = JFactory::getDate($checkout);

        if ($checkin->format('Y-m-d') === $checkout->format('Y-m-d')) {
            $checkout->modify('+1 day');
        }

        // availability helper
        $av_helper = VikBooking::getAvailabilityInstance();

        // find the requested room
        $room_record = $room ? $av_helper->getRoomByName($room) : [];

        // set stay dates and room party
        $av_helper->setStayDates($checkin->format('Y-m-d'), $checkout->format('Y-m-d'))
            ->setRoomParty($guests, 0);

        // set flag to ignore the restrictions
        $av_helper->ignoreRestrictions(true);

        // set flag to ignore the rooms availability
        $av_helper->ignoreAvailability(true);

        // get room rates
        $room_rates = $av_helper->getRates([
            'num_rooms'  => 1,
            'only_rates' => 1,
            'max_rooms_limit' => 10,
            'forced_room_ids' => $room_record ? [$room_record['id']] : [],
        ]);

        if (!$room_rates) {
            /**
             * Do not terminate the process with an error code, as available rooms may have been
             * queried for an unsupported number of guests. We rather explain the error message.
             */
            VCMHttpDocument::getInstance($app)->json([
                'status' => false,
                'error'  => ($av_helper->getError() ?: 'No rates found with the specified parameters.'),
            ]);
        }

        // room names pool
        $room_names = [];

        if ($room_record && ($room_rates[$room_record['id']] ?? [])) {
            // filter the rates by the requested room
            $room_rates = [
                $room_record['id'] => $room_rates[$room_record['id']],
            ];
        }

        // build response properties
        $response = [
            'currency'   => VikBooking::getCurrencyName(),
            'guests'     => $guests,
            'nights'     => $av_helper->countNightsOfStay(),
            'room_rates' => [],
        ];

        foreach ($room_rates as $room_id => $room_rplans) {
            // build room-rate container
            $room_rate = [
                'room_name'  => $room_rplans[0]['r_short_desc'],
                'rate_plans' => [],
            ];

            foreach ($room_rplans as $room_rplan) {
                // set rate plan data
                $room_rate['rate_plans'][] = [
                    'rate_plan_id'   => (int) $room_rplan['idprice'],
                    'rate_plan_name' => $room_rplan['pricename'],
                    'price'          => $room_rplan['cost'],
                ];
            }

            // push room rates data
            $response['room_rates'][] = $room_rate;

            // set room involved
            $room_names[$room_id] = $room_rplans[0]['r_short_desc'];

            // ensure we do not return too many listings
            if (count($response['room_rates']) >= 10) {
                // that's enough data
                break;
            }
        }

        // create add-on for the room rates
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonRoomrates($checkin, $checkout, $room_names)
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($response);
    }

    /**
     * End-point used to fetch room availability and
     * restrictions inventory for the AI Assistant.
     * 
     * @return  void
     */
    public function fetch_ari()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $from_date    = $app->input->json->getString('start', 'now');
        $to_date      = $app->input->json->getString('end', '+1 week');
        $room         = $app->input->json->getString('room', '');
        $restrictions = $app->input->json->getBool('restrictions', null);

        // normalize check-in and check-out dates
        $from_date = JFactory::getDate($from_date);
        $to_date   = JFactory::getDate($to_date);

        $from_ymd = $from_date->format('Y-m-d');
        $to_ymd = $to_date->format('Y-m-d');
        if ($from_ymd === $to_ymd) {
            $to_ymd = $to_date->modify('+1 day')->format('Y-m-d');
        }

        // availability helper
        $av_helper = VikBooking::getAvailabilityInstance();

        // find the requested room
        $room_record = $room ? $av_helper->getRoomByName($room) : [];

        // set stay dates
        $av_helper->setStayDates($from_ymd, $to_ymd);

        if ($av_helper->countNightsOfStay() > 31) {
            // 416 - Range not satisfiable
            VCMHttpDocument::getInstance($app)->close(416, 'Maximum date range is 31 days.');
        }

        if ($room_record) {
            // set just the requested room record
            $av_helper->loadRooms([
                $room_record['id'],
            ]);

            if ($restrictions === null) {
                // turn on restrictions validation if not specified and if one room involved
                $restrictions = true;
            }
        } else {
            // make sure not to load more than 10 room records
            $av_helper->loadRooms([], 10);
        }

        // obtain the availability inventory
        $ari = $av_helper->getInventory((bool) $restrictions);

        if (!$ari) {
            VCMHttpDocument::getInstance($app)->close(404, $av_helper->getError() ?: 'No inventory found with the specified parameters.');
        }

        // room names pool
        $room_names = [];
        foreach ($av_helper->loadRooms() as $room) {
            $room_names[$room['id']] = $room['name'];
        }

        // create add-on for the room inventory
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonRoomari($from_date, $to_date, $room_names)
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json(array_values($ari));
    }

    /**
     * End-point used by the AI Assistant to close a room on the given dates.
     * 
     * @return  void
     */
    public function close_room()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $start  = $app->input->json->getString('start', 'now');
        $nights = $app->input->json->getInt('nights', 1);
        $room   = $app->input->json->getString('room', '');
        $all_rooms = $app->input->json->getBool('all_rooms', false);

        // normalize check-in and check-out dates
        $start = JFactory::getDate($start);
        $checkin  = $start->format('Y-m-d');
        $checkout = $start->modify("+{$nights} days")->format('Y-m-d');

        // availability helper
        $av_helper = VikBooking::getAvailabilityInstance();

        $other_rooms = [];
        if ($all_rooms) {
            // fetch all rooms
            $other_rooms = $av_helper->loadRooms();
            $room_record = array_shift($other_rooms);
            $other_rooms = array_column($other_rooms, 'id');
        } else {
            // find the requested room
            $room_record = $room ? $av_helper->getRoomByName($room) : [];
        }

        if (!$room_record) {
            // 404 - Not Found
            VCMHttpDocument::getInstance($app)->close(404, 'Could not find a valid room to close.');
        }

        // close the room on the requested dates
        $model_res = VBOModelReservation::getInstance([
            '_isAdministrator' => 1,
            'force_booking'    => 1,
            'set_closed'       => 1,
            'close_others'     => $other_rooms,
            'checkin'          => strtotime($checkin),
            'checkout'         => strtotime($checkout),
            'nights'           => $nights,
        ])->setRoom([
            'id' => $room_record['id'],
        ]);

        // set caller identifier
        $caller_id = JText::_('VCM_AI_ASSISTANT');
        $model_res->setCaller($caller_id)
            ->setHistoryData([
                'ai'     => 1,
                'caller' => $caller_id,
            ]);

        // store the reservation
        $model_res->create();

        // get the new booking ID
        $res_id = $model_res->getNewBookingID();
        if (!$res_id) {
            VCMHttpDocument::getInstance($app)->close(500, $model_res->getError());
        }

        // create add-on for the generated booking record
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonReservation($res_id, 'closure')
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json([
            'success'  => true,
            'checkin'  => $checkin,
            'checkout' => $checkout,
            'room'     => !$all_rooms ? $room_record['name'] : 'all',
        ]);
    }

    /**
     * End-point used by the AI Assistant to create a new booking.
     */
    public function book_room()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $checkin    = $app->input->json->getString('checkin', 'now');
        $checkout   = $app->input->json->getString('checkout');
        $guests     = $app->input->json->get('guests', [], 'array');
        $room       = $app->input->json->getString('room');
        $room_index = $app->input->json->getUInt('room_index', 0);
        $tot_cost   = $app->input->json->getFloat('total_cost');
        $customer   = $app->input->json->get('customer', [], 'array');
        $force_av   = $app->input->json->getBool('force_availability', false);
        $notes      = $app->input->json->getString('extra_notes');

        if (empty($customer))
        {
            VCMHttpDocument::getInstance($app)->close(400, 'Missing mandatory customer details: "first_name", "last_name", "email"');
        }

        // make sure to clean up those properties containing just an empty space
        $customer = array_filter($customer, function($detail)
        {
            return !empty($detail) && strlen(trim((string) $detail));
        });

        if (empty($customer['first_name']))
        {
            VCMHttpDocument::getInstance($app)->close(400, 'Missing mandatory customer details: "first_name"');
        }

        if (empty($customer['last_name']))
        {
            VCMHttpDocument::getInstance($app)->close(400, 'Missing mandatory customer details: "last_name"');
        }

        if (empty($customer['email']))
        {
            VCMHttpDocument::getInstance($app)->close(400, 'Missing mandatory customer details: "email"');
        }

        if (empty($guests['adults']))
        {
            VCMHttpDocument::getInstance($app)->close(400, 'Missing mandatory details: "adults"');
        }

        // set optional number of children and related ages
        $guests['children'] = (int) ($guests['children'] ?? 0);
        $guests['children_age'] = (array) ($guests['children_age'] ?? null);

        // normalize check-in and check-out dates
        $checkin  = JFactory::getDate($checkin);
        $checkout = JFactory::getDate($checkout);

        if ($checkin->format('Y-m-d') === $checkout->format('Y-m-d')) {
            $checkout->modify('+1 day');
        }

        // build filter options for getting the available room rates
        $params = [
            'max_rooms_limit' => 10,
        ];

        // availability helper
        $av_helper = VikBooking::getAvailabilityInstance();

        // find the requested room
        $room_record = $room ? $av_helper->getRoomByName($room) : [];

        if ($room_record) {
            // filter by the requested room type
            $params['forced_room_ids'] = [$room_record['id']];
        }

        // set stay dates and room party
        $av_helper->setStayDates($checkin->format('Y-m-d'), $checkout->format('Y-m-d'))
            ->setRoomParty($guests['adults'], $guests['children']);

        // set flag to ignore the restrictions
        $av_helper->ignoreRestrictions(true);

        // get the available room rates for the requested party
        $room_rates = $av_helper->getRates($params);

        if (!$room_record && !$room_rates) {
            // no availability for these dates and no valid rooms specified
            VCMHttpDocument::getInstance($app)->json([
                'result'   => false,
                'error'    => $av_helper->getError() ?: 'No rooms available for the dates and guests requested.',
                'solution' => 'Specify the exact room name to book and eventually force the availability.',
            ]);
        }

        if ($room_record && (!$room_rates || !isset($room_rates[$room_record['id']])) && !$force_av) {
            // the requested room is not available on the requested dates
            $response = [
                'result'   => false,
                'error'    => sprintf('The room "%s" is not available on the requested dates.', $room_record['name']),
                'solution' => 'Book other rooms, choose different dates or force the availability.',
            ];

            // query other rooms
            $room_rates = $av_helper->getRates(array_merge($params, [
                'forced_room_ids' => [],
            ]));

            if ($room_rates) {
                // list the rooms that would be available
                $available_rooms = [];
                foreach ($room_rates as $rr) {
                    $available_rooms[] = $rr[0]['r_short_desc'];
                }
                $response['available_rooms'] = implode(', ', $available_rooms);
            }

            VCMHttpDocument::getInstance($app)->json($response);
        }

        // proceed with creating the booking record
        $room_rates = is_array($room_rates) ? $room_rates : [];

        // determine the room to book
        $room_id = $room_record ? $room_record['id'] : key($room_rates);

        if (!$room_id) {
            VCMHttpDocument::getInstance($app)->close(404, 'Room not found for creating the reservation.');
        }

        // obtain the room details
        $room_info = $room_record ? $room_record : VikBooking::getRoomInfo($room_id);

        // determine the room booking cost and rate plan
        $cust_cost  = 0;
        $room_cost  = 0;
        $room_rplan = 0;
        if ($tot_cost > 0) {
            $cust_cost = $tot_cost;
        } elseif (($room_rates[$room_id] ?? [])) {
            // take the first room rate plan available
            $room_cost  = $room_rates[$room_id][0]['cost'];
            $room_rplan = $room_rates[$room_id][0]['idprice'];
        }

        $model_res = VBOModelReservation::getInstance([
            '_isAdministrator' => 1,
            'force_booking'    => (int) $force_av,
            'checkin'          => $checkin->format('U'),
            'checkout'         => $checkout->format('U'),
            'adults'           => $guests['adults'],
            'children'         => $guests['children'],
            'children_age'     => $guests['children_age'],
            'admin_notes'      => $notes,
        ])->setCustomer([
            'first_name' => $customer['first_name'],
            'last_name'  => $customer['last_name'],
            'email'      => $customer['email'],
            'country'    => $customer['country'] ?? null,
        ])->setRoom([
            'id'           => $room_id,
            'cust_indexes' => $room_index,
            'cust_cost'    => $cust_cost,
            'room_cost'    => $room_cost,
            'id_price'     => $room_rplan,
            'guess_tax'    => true,
        ]);

        // set caller identifier
        $caller_id = JText::_('VCM_AI_ASSISTANT');
        $model_res->setCaller($caller_id)
            ->setHistoryData([
                'ai'     => 1,
                'caller' => $caller_id,
            ]);

        // store the reservation
        $model_res->create();

        // get the new booking ID
        $res_id = $model_res->getNewBookingID();

        if (!$res_id) {
            VCMHttpDocument::getInstance($app)->close(500, $model_res->getError() ?: 'Could not create the reservation.');
        }

        // create add-on for the generated booking record
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonReservation($res_id, 'new')
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json([
            'success'        => true,
            'checkin'        => $checkin->format('Y-m-d'),
            'checkout'       => $checkout->format('Y-m-d'),
            'guests'         => $guests['adults'] + $guests['children'],
            'room'           => $room_info['name'],
            'reservation_id' => $res_id,
        ]);
    }

    /**
     * End-point used by the AI Assistant to search for reservations.
     * 
     * @return  void
     */
    public function search_bookings()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $booking_number      = $app->input->json->getString('booking_number');
        $confirmation_number = $app->input->json->getString('confirmation_number');
        $customer_name       = $app->input->json->getString('customer_name');
        $customer_email      = $app->input->json->getString('customer_email');
        $customer_phone      = $app->input->json->getString('customer_phone');
        $room_name           = $app->input->json->getString('room');
        $booking_status      = $app->input->json->getString('booking_status');
        $checkin_date        = $app->input->json->getString('checkin_date');
        $checkout_date       = $app->input->json->getString('checkout_date');
        $stay_date           = $app->input->json->getString('stay_date');
        $creation_date       = $app->input->json->getString('creation_date');
        $date_range          = $app->input->json->get('date_range', [], 'array');
        $ordering            = $app->input->json->getString('ordering');
        $direction           = $app->input->json->getString('direction');

        // normalize booking status
        if (!strcasecmp((string) $booking_status, 'pending')) {
            $booking_status = 'standby';
        }

        if (!in_array((string) $booking_status, ['confirmed', 'standby', 'cancelled'])) {
            // unset status filter or the search would fail
            $booking_status = null;
        }

        // set max bookings limit and check for date range validity
        $max_bookings = 10;
        if (($date_range['type'] ?? null) && (($date_range['start'] ?? null) || ($date_range['end'] ?? null))) {
            // date range requested, increase the max bookings limit
            $max_bookings = 25;
        } else {
            // make sure to unset the date range filter
            $date_range = [];
        }

        // access the reservation model
        $model_res = VBOModelReservation::getInstance([
            '_isAdministrator' => 1,
        ])->setFilters([
            'max_bookings'        => $max_bookings,
            'exclude_closures'    => true,
            'booking_id'          => $booking_number,
            'confirmation_number' => $confirmation_number,
            'status'              => $booking_status,
            'creation_date'       => $creation_date,
            'checkin_date'        => $checkin_date,
            'checkout_date'       => $checkout_date,
            'stay_date'           => $stay_date,
            'date_range'          => $date_range,
            'room_name'           => $room_name,
            'customer_name'       => $customer_name,
            'email'               => $customer_email,
            'phone'               => $customer_phone,
            'ordering'            => $ordering,
            'direction'           => $direction,
        ]);

        $bookings = $model_res->search();

        if (!$bookings) {
            // create add-on for the failed query
            (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
                new VCMAiAssistantAddonSearchbookings($app->input->json->getArray(), [], 0)
            );

            VCMHttpDocument::getInstance($app)->close(404, $model_res->getError() ?: 'Could not find any reservation according to the specified parameters.');
        }

        $tot_bookings = method_exists($model_res, 'getTotBookingsFound') ? $model_res->getTotBookingsFound() : count($bookings);

        $response = [
            'total_found' => $tot_bookings,
            'bookings'    => [],
        ];

        // the currency ISO code
        $currency = VikBooking::getCurrencyName();

        foreach ($bookings as $booking) {
            // build customer full name
            $customer_full_name = null;
            if (!empty($booking['customer_first_name']) || !empty($booking['customer_last_name'])) {
                $customer_full_name = trim($booking['customer_first_name'] . ' ' . $booking['customer_last_name']);
            }

            // build channel source
            $source = 'Direct booking';
            if (!empty($booking['channel'])) {
                $channel_parts = explode('_', $booking['channel']);
                if (count($channel_parts) > 1) {
                    $source = $channel_parts[count($channel_parts) - 1];
                } else {
                    $source = ucwords($channel_parts[0]);
                }
            } elseif (!empty($booking['closure'])) {
                $source = 'Manual closure';
            }

            // build booking details
            $details = [
                'booking_id'     => $booking['id'],
                'booking_status' => $booking['status'],
                'created_on'     => date('Y-m-d H:i:s', $booking['ts']),
                'checkin'        => date('Y-m-d H:i:s', $booking['checkin']),
                'checkout'       => date('Y-m-d H:i:s', $booking['checkout']),
                'customer_name'  => $customer_full_name,
                'source'         => $source,
            ];

            if (!$customer_full_name) {
                // set raw customer data
                unset($details['customer_name']);
                $details['customer_details'] = $booking['custdata'];
            }

            if (!empty($booking['idorderota']) && !empty($booking['channel'])) {
                // set OTA reservation number
                $details['ota_reservation_number'] = $booking['idorderota'];
            }

            if (!empty($booking['closure'])) {
                // set property to identify the booking as a closure
                $details['is_closure'] = true;
            }

            // set rooms and guests information
            $booking_rooms = VikBooking::loadOrdersRoomsData($booking['id']);
            $adults_number = array_sum(array_column($booking_rooms, 'adults'));
            $children_number = array_sum(array_column($booking_rooms, 'children'));
            $room_names = array_column($booking_rooms, 'room_name');

            $details['adults']   = $adults_number;
            $details['children'] = $children_number;
            $details['rooms']    = implode(', ', $room_names);

            if (!empty($booking['phone'])) {
                $details['phone'] = $booking['phone'];
            }

            // only if not a closure...
            if (!$booking['closure']) {
                // set total amount(s)
                $details['booking_total'] = (float) $booking['total'];
                $details['amount_paid']   = (float) $booking['totpaid'];
                $details['currency']      = $currency;

                // set customer language
                $details['language'] = $booking['lang'] ?: null;
            }

            // push booking details
            $response['bookings'][] = $details;
        }

        // create add-on for the booking records found
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonSearchbookings($app->input->json->getArray(), $bookings, $tot_bookings)
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($response);
    }

    /**
     * End-point used by the AI Assistant to modify a reservation.
     * 
     * @return  void
     */
    public function modify_booking()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $booking_number     = $app->input->json->getString('booking_number');
        $new_checkin        = $app->input->json->getString('new_checkin');
        $new_checkout       = $app->input->json->getString('new_checkout');
        $cost_difference    = $app->input->json->getFloat('cost_difference');
        $switch_with_room   = $app->input->json->getString('switch_with_room');
        $guests             = $app->input->json->get('guests', [], 'array');
        $add_extra_services = $app->input->json->get('add_extra_services', [], 'array');
        $extra_notes        = $app->input->json->getString('extra_notes');
        $ota_reporting_pref = $app->input->json->getString('ota_reporting');

        if (!$booking_number) {
            VCMHttpDocument::getInstance($app)->close(400, 'Please specify the booking number or ID to modify.');
        }

        $model_res = VBOModelReservation::getInstance([
            '_isAdministrator' => 1,
        ])->setFilters([
            'booking_id'      => $booking_number,
            'exclude_expired' => true,
        ]);

        $bookings = $model_res->search();

        if (!$bookings) {
            VCMHttpDocument::getInstance($app)->close(404, $model_res->getError() ?: 'Could not find any active reservation (check-out date must be in the future for a modification) from the provided booking number/ID.');
        }

        // the booking ID to modify
        $booking_id = $bookings[0]['id'];

        // the room ID to assign to the booking record
        $switch_rooms = [];

        if ($switch_with_room) {
            // find the requested room for the switch
            $room_record = VikBooking::getAvailabilityInstance()->getRoomByName($switch_with_room);

            if (!$room_record) {
                VCMHttpDocument::getInstance($app)->close(404, 'Could not find the requested room for the switch.');
            }

            // push room for switch
            $switch_rooms[] = $room_record['id'];
        }

        // check if we've got an OTA booking that may support the reporting APIs
        $use_ota_reporting = null;
        if (($new_checkin || $new_checkout) && VCMOtaReporting::getInstance($bookings[0])->stayChangeAllowed()) {
            // check if the stay dates should change
            if ($new_checkin != date('Y-m-d', $bookings[0]['checkin']) || $new_checkout != date('Y-m-d', $bookings[0]['checkout'])) {
                // different stay dates have been requested
                if (!$ota_reporting_pref) {
                    // get channel source name
                    $channel_parts = explode('_', $bookings[0]['channel']);
                    if (count($channel_parts) > 1) {
                        $source = $channel_parts[count($channel_parts) - 1];
                    } else {
                        $source = ucwords($channel_parts[0]);
                    }
                    // we attempt to inform the AI model that the OTA could be notified for a stay change
                    VCMHttpDocument::getInstance($app)->json([
                        'status'    => 'action_required',
                        'parameter' => 'ota_reporting',
                        'action'    => sprintf(
                            'This is a reservation from %s that is about to be modified on the PMS and the availability inventory will be updated on the OTA. ' .
                            'It is also possible to notify the channel %s for the new stay dates, even though their commissions will be re-calculated. ' .
                            'Choose if the OTA should only get an update of the availability inventory or if the change of stay dates should be notified as well.',
                            $source,
                            $source
                        ),
                    ]);
                }
                if (!strcasecmp((string) $ota_reporting_pref, 'stay_changes_and_availability')) {
                    // turn flag on
                    $use_ota_reporting = true;
                }
            }
        }

        // register called ID
        $caller_id = JText::_('VCM_AI_ASSISTANT');
        $model_res->setCaller($caller_id)
            ->setHistoryData([
                'ai'     => 1,
                'caller' => $caller_id,
            ]);

        // set booking details
        $model_res->setBooking($bookings[0]);

        // modify the reservation
        $modified = $model_res->modify([
            'booking_id'         => $booking_id,
            'checkin'            => $new_checkin,
            'checkout'           => $new_checkout,
            'cost_difference'    => $cost_difference,
            'switch_rooms'       => $switch_rooms,
            'guests'             => $guests,
            'add_extra_services' => $add_extra_services,
            'extra_notes'        => $extra_notes,
            'ota_reporting'      => $use_ota_reporting,
        ]);

        if (!$modified) {
            VCMHttpDocument::getInstance($app)->close(500, $model_res->getError() ?: 'Could not modify the requested reservation number/ID.');
        }

        // build response
        $response = [
            'success'    => true,
            'booking_id' => $booking_id,
            'status'     => 'Updated',
        ];

        if ($model_res->getError()) {
            // explain to the AI model the non-stopping error that occurred
            $response['status'] = 'Warning';
            $response['alert']  = 'The reservation was modified on the PMS, but the following error occurred afterwards: ' . $model_res->getError();
        }

        // create add-on
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonReservation($booking_id, 'modified')
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($response);
    }

    /**
     * End-point used by the AI Assistant to delete a reservation.
     * 
     * @return  void
     */
    public function delete_booking()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $booking_number      = $app->input->json->getString('booking_number');
        $cancellation_reason = $app->input->json->getString('cancellation_reason');
        $double_confirmation = $app->input->json->getBool('double_confirmation', false);

        if (!$booking_number) {
            VCMHttpDocument::getInstance($app)->close(400, 'Please specify the booking number or ID to cancel.');
        }

        $model_res = VBOModelReservation::getInstance([
            '_isAdministrator' => 1,
        ])->setFilters([
            'booking_id' => $booking_number,
        ]);

        $bookings = $model_res->search();

        if (!$bookings) {
            VCMHttpDocument::getInstance($app)->close(404, $model_res->getError() ?: 'Could not find any reservation from the provided booking number/ID.');
        }

        // the booking ID to cancel
        $booking_id = $bookings[0]['id'];

        if (!$double_confirmation) {
            VCMHttpDocument::getInstance($app)->close(412, sprintf('Please confirm again before proceeding with the cancellation of the booking ID %d.', $booking_id));
        }

        // register called ID
        $caller_id = JText::_('VCM_AI_ASSISTANT');
        $model_res->setCaller($caller_id)
            ->setHistoryData([
                'ai'     => 1,
                'caller' => $caller_id,
            ]);

        // delete the reservation
        $deleted = $model_res->delete([
            'booking_id'          => $booking_id,
            'cancellation_reason' => $cancellation_reason,
            'purge_remove'        => false,
        ]);

        if (!$deleted) {
            VCMHttpDocument::getInstance($app)->close(500, $model_res->getError() ?: 'Could not delete the requested reservation number/ID.');
        }

        // create add-on
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonReservation($booking_id, 'cancelled')
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json([
            'success'    => true,
            'booking_id' => $booking_id,
            'status'     => 'Cancelled',
        ]);
    }

    /**
     * End-point used to fetch some statistics for the AI Assistant.
     * 
     * @return  void
     */
    public function fetch_statistics()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $start   = $app->input->json->getString('start');
        $end     = $app->input->json->getString('end');
        $metrics = $app->input->json->getString('metrics');
        $room    = $app->input->json->getString('room', '');

        // normalize start and end dates
        $start = JFactory::getDate($start);
        $end   = JFactory::getDate($end);

        $status = [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ];

        // find the requested room
        $room_record = $room ? VikBooking::getAvailabilityInstance()->getRoomByName($room) : [];
        $filter_rooms = $room_record ? [$room_record['id']] : [];

        // build fetch signature for caching purposes
        $fetch_signature = implode(';', [$status['start'], $status['end'], ($room_record ? $room_record['id'] : 0)]);

        // the cache limit in seconds
        $cache_lim_seconds = 60;

        try {
            // statistics container
            $stats = [];

            // check for cached values in case of equal subsequent calls made by the AI model
            $cached_stats = VCMFactory::getConfig()->getArray('aitools_stats_cache', []);

            if (($cached_stats['signature'] ?? null) == $fetch_signature && (time() - ($cached_stats['ts'] ?? 0)) < $cache_lim_seconds) {
                // use recently cached statistics for the same values
                $stats = $cached_stats['stats'] ?? [];
            }

            if (!$stats) {
                // obtain the financial stats for the given dates
                $stats = VBOTaxonomyFinance::getInstance()->getStats($status['start'], $status['end'], $filter_rooms);

                // cache the newly fetched statistics
                VCMFactory::getConfig()->set('aitools_stats_cache', json_encode([
                    'signature' => $fetch_signature,
                    'ts'        => time(),
                    'stats'     => $stats,
                ]));
            }
        } catch (Exception $e) {
            // propagate the error
            VCMHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
        }

        // the currency ISO code
        $currency = VikBooking::getCurrencyName();

        // evaluate the requested metrics
        if ($metrics === 'total_revenue')
        {
            $status['value'] = $stats['revenue'];
            $status['currency'] = $currency;
            $status['description'] = 'Total overall net revenue for confirmed reservations, including direct bookings and OTA bookings, excluding taxes, fees and commisions.';
        }
        else if ($metrics === 'direct_bookings_revenue')
        {
            $status['value'] = $stats['ibe_revenue'];
            $status['currency'] = $currency;
            $status['description'] = 'Direct bookings overall revenue.';
        }
        else if ($metrics === 'ota_bookings_revenue')
        {
            $status['value'] = $stats['ota_revenue'];
            $status['currency'] = $currency;
            $status['description'] = 'OTA reservations overall revenue.';
        }
        else if ($metrics === 'reservations')
        {
            $status['value'] = $stats['tot_bookings'];
            $status['description'] = 'The total number of confirmed reservations.';
        }
        else if ($metrics === 'occupancy')
        {
            $status['value'] = $stats['nights_booked'];
            $status['total'] = $stats['tot_inventory'];
            $status['percent'] = $stats['occupancy'] . '%';
            $status['description'] = sprintf('%d nights booked over a total of %d bookable room nights.', $stats['nights_booked'], $stats['tot_inventory']);
        }
        else if ($metrics === 'average_length_of_stay')
        {
            $status['value'] = $stats['avg_los'];
            $status['description'] = 'The average length of stay (number of nights of stay).';
        }
        else if ($metrics === 'average_daily_rate')
        {
            $status['value'] = $stats['adr'];
            $status['currency'] = $currency;
            $status['description'] = 'The average nightly room-rate that was applied to the various reservations.';
        }
        else if ($metrics === 'average_booking_window')
        {
            $status['value'] = $stats['abw'];
            $status['unit'] = 'days';
            $status['description'] = 'Average number of days in advance with which room reservations were made.';
        }
        else if ($metrics === 'ota_service_fees')
        {
            $status['value'] = $stats['cmms'];
            $status['currency'] = $currency;
            $status['description'] = 'The overall amount of OTA commissions applied to all bookings.';
        }
        else if ($metrics === 'taxes')
        {
            $status['value'] = $stats['taxes'];
            $status['currency'] = $currency;
            $status['description'] = 'The overall amount of taxes included in the reservations.';
        }
        else if ($metrics === 'countries_ranking')
        {
            $status['ranking'] = $stats['country_ranks'];
            $status['currency'] = $currency;
            $status['description'] = 'The overall countries list where most bookings came from.';
        }
        else if ($metrics === 'point_of_sales_ranking')
        {
            $status['currency'] = $currency;
            $status['ranking'] = array_map(function($pos) {
                // get rid of logo URL to prevent OpenAI from including it in the response (markdown)
                unset($pos['logo']);
                return $pos;
            }, $stats['pos_revenue']);
            $status['description'] = 'The overall channels ranking where bookings came from.';
        }
        else if ($metrics === 'cancellations')
        {
            if (!$stats['tot_cancellations']) {
                $status['description'] = 'No cancelled bookings found.';
            } else {
                $status['total_cancelled_bookings'] = $stats['tot_cancellations'];
                $status['cancelled_bookings_value'] = $stats['cancellations_amt'];
                $status['cancelled_booking_ids'] = $stats['cancellation_ids'];
                $status['currency'] = $currency;
            }
        }
        else
        {
            VCMHttpDocument::getInstance($app)->close(501, 'Cannot fetch the statistics for the requested metrics');
        }

        // create add-on for the statistics
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonStatistics($start, $end, $metrics, ($room_record['name'] ?: ''))
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($status);
    }

    /**
     * End-point used by the AI Assistant to alter rates and/or restrictions for specific rooms and dates.
     * 
     * @return  void
     * 
     * @since   1.9.13 added support for custom rate types and a list of rate-data objects within a single request.
     */
    public function modify_room_rates()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // allowed rate modification types
        $allowed_rate_types = [
            // "fixed" - classic finished rate to set
            'fixed',
            // "percentage" - rate can be positive or negative, respectively for +% and -%
            'percentage',
            // "add" - increase rates by a fixed (and absolute) amount
            'add',
            // "sub" - decrease rates by a fixed (and absolute) amount
            'sub',
        ];

        // get arguments from payload
        $rates_data = $app->input->json->get('rates_data', [], 'array');
        $skip_otas  = $app->input->json->getBool('only_website', false);

        // room names map with related IDs
        $room_names_map = [];

        // sort the rate-data objects by date ascending
        usort($rates_data, function($a, $b) {
            return ($a['date'] ?? 0) <=> ($b['date'] ?? 0);
        });

        // determine the pricing modification type ("fixed", "percentage", "add", "sub")
        $is_fixed_pricing = true;
        $rooms_involved = [];
        foreach ($rates_data as $rate_data) {
            $rate_type = $rate_data['rate_type'] ?? 'fixed';
            if (in_array($rate_type, $allowed_rate_types) && $rate_type != 'fixed' && !empty($rate_data['rate'])) {
                // pricing modification requires the calculation of the current rates
                $is_fixed_pricing = false;
                if (!empty($rate_data['room']) && !in_array($rate_data['room'], $rooms_involved)) {
                    // push room involved
                    $rooms_involved[] = $rate_data['room'];
                }
            }
        }

        if (!$is_fixed_pricing) {
            // calculate the current room rates before proceeding
            $bound_date_start = $rates_data[0]['date'] ?? null;
            $bound_date_end   = $rates_data[count($rates_data) - 1]['date'] ?? null;
            if (!$bound_date_start || !$bound_date_end || !$rooms_involved) {
                // invalid data for applying the pricing modification
                VCMHttpDocument::getInstance($app)->close(400, 'Missing dates or rooms for applying the pricing modification.');
            }

            // obtain room IDs from the involved rooms
            $room_ids = [];
            foreach ($rooms_involved as $room_name) {
                if ($room_record = VikBooking::getAvailabilityInstance(true)->getRoomByName($room_name)) {
                    // cache room record in map
                    $room_names_map[$room_name] = $room_record;
                    // push involved room id
                    $room_ids[] = $room_record['id'];
                }
            }

            try {
                // fetch room rates
                $room_rates = VBOModelPricing::getInstance()->getRoomRates([
                    // use "id_rooms" to obtain an associative list of daily rates by room id
                    'id_rooms'     => $room_ids,
                    'from_date'    => $bound_date_start,
                    'to_date'      => $bound_date_end,
                    'all_rplans'   => false,
                    'restrictions' => false,
                ]);
            } catch (Exception $e) {
                // propagate the error
                VCMHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
            }

            // convert rate-data objects into fixed pricing alterations
            foreach ($rates_data as &$rate_data) {
                // detect the rate type
                $rate_type = $rate_data['rate_type'] ?? 'fixed';
                if (!in_array($rate_type, $allowed_rate_types) || !isset($room_names_map[$rate_data['room'] ?? 0]) || empty($rate_data['date'])) {
                    // unsupported rate modification type, room or date
                    unset($rate_data);
                    continue;
                }

                // detect the current room record ID
                $room_id = $room_names_map[$rate_data['room']]['id'] ?? 0;

                if ($rate_type != 'fixed' && $room_rates[$room_id][$rate_data['date']]['cost'] ?? 0) {
                    // calculate the proper rate to set
                    $set_rate = $room_rates[$room_id][$rate_data['date']]['cost'];
                    if ($rate_type == 'percentage') {
                        // percent increase or decrease (rate may be negative)
                        $set_rate += $set_rate * ((float) $rate_data['rate']) / 100;
                    } elseif ($rate_type == 'add') {
                        // fixed charge
                        $set_rate += abs((float) $rate_data['rate']);
                    } elseif ($rate_type == 'sub') {
                        // fixed discount
                        $set_rate -= abs((float) $rate_data['rate']);
                    }

                    if ($set_rate <= 5) {
                        // invalid cost calculated
                        unset($rate_data);
                        continue;
                    }

                    // always round to an integer
                    $set_rate = round($set_rate);

                    // update rate data object
                    $rate_data['rate'] = $set_rate;
                    unset($rate_data['rate_type']);
                } elseif ($rate_type != 'fixed') {
                    // unable to calculate the rate to set
                    unset($rate_data);
                    continue;
                } else {
                    // update rate data object even in case of fixed pricing to set
                    unset($rate_data['rate_type']);
                }
            }

            // unset last reference
            unset($rate_data);

            // reset keys
            $rates_data = array_values($rates_data);
        }

        // normalize single and contiguous dates into a range
        $rates_data_range = [];
        $rates_data_sdate = null;
        $rates_data_edate = null;
        $rates_data_sign  = null;
        foreach ($rates_data as $rate_data) {
            if (empty($rate_data['date'])) {
                // invalid payload
                continue;
            }

            // calculate rate data signature
            $current_data_sign = serialize([
                'room'       => $rate_data['room'] ?? '',
                'rplan_name' => $rate_data['rate_plan_name'] ?? $rate_data['rplan_name'] ?? '',
                'rate'       => (float) ($rate_data['rate'] ?? 0),
                'min_los'    => abs((int) ($rate_data['min_los'] ?? 0)),
            ]);

            if (!$rates_data_sdate) {
                // set range start date
                $rates_data_sdate = $rate_data['date'];
                // set range end date
                $rates_data_edate = $rate_data['date'];
                // set rate data signature
                $rates_data_sign = $current_data_sign;
                // go to next rate data object
                continue;
            }

            if ($current_data_sign != $rates_data_sign || $rate_data['date'] != date('Y-m-d', strtotime('+1 day', strtotime($rates_data_edate)))) {
                // push previous rate-data object
                $rates_data_range[] = array_merge([
                    'start' => $rates_data_sdate,
                    'end'   => $rates_data_edate,
                ], unserialize($rates_data_sign));
                // set range start date
                $rates_data_sdate = $rate_data['date'];
                // set range end date
                $rates_data_edate = $rate_data['date'];
                // set rate data signature
                $rates_data_sign = $current_data_sign;
            } else {
                // contiguous date with equal rate-data information found
                $rates_data_edate = $rate_data['date'];
            }
        }

        if ($rates_data_sdate && $rates_data_edate) {
            // push last rate-data object
            $rates_data_range[] = array_merge([
                'start' => $rates_data_sdate,
                'end'   => $rates_data_edate,
            ], unserialize($rates_data_sign));
        }

        // set the sorted and grouped rate-data objects
        $rates_data = $rates_data_range;
        unset($rates_data_range);

        if (!$rates_data) {
            // abort the process if no valid rate-data objects were provided
            VCMHttpDocument::getInstance($app)->json([
                'result'     => false,
                'suggestion' => 'Please provide at least one rate-data object with the pricing information to apply.',
            ]);
        }

        // count the number of pricing update operations
        $tot_operations = count($rates_data);

        // check whether OTAs should be updated asynchronously in background
        $async_upd_otas = !$skip_otas && $tot_operations > 3;

        // build operation bounds
        $operation_bounds = [
            'from_date' => null,
            'to_date'   => null,
            'rooms'     => [],
        ];

        // prepare the response object
        $response = [];

        // iterate all the pricing update operations
        foreach ($rates_data as $rate_data) {
            // normalize rate-data arguments
            $from_date    = ($rate_data['start'] ?? '') ?: 'now';
            $to_date      = ($rate_data['end'] ?? '') ?: 'now';
            $room         = $rate_data['room'] ?? '';
            $rplan_name   = $rate_data['rate_plan_name'] ?? $rate_data['rplan_name'] ?? '';
            $rate         = (float) ($rate_data['rate'] ?? 0);
            $min_los      = abs((int) ($rate_data['min_los'] ?? 0));
            $only_website = $async_upd_otas || $skip_otas;

            if (!$rate && !$min_los) {
                // abort the process if no rates or restrictions were set
                VCMHttpDocument::getInstance($app)->json([
                    'result'     => false,
                    'suggestion' => 'Please provide either a new rate or a new minimum stay restriction to set for the room.',
                ]);
            }

            if (!$room) {
                // abort the process if no listings were set
                VCMHttpDocument::getInstance($app)->json([
                    'result'     => false,
                    'suggestion' => 'Please provide the name of the room/listing to update.',
                ]);
            }

            // normalize check-in and check-out dates
            $from_date = JFactory::getDate($from_date);
            $to_date   = JFactory::getDate($to_date);

            $from_ymd = $from_date->format('Y-m-d');
            $to_ymd = $to_date->format('Y-m-d');

            // availability helper
            $av_helper = VikBooking::getAvailabilityInstance(true);

            // find the requested room
            $room_record = $room ? ($room_names_map[$room] ?? $av_helper->getRoomByName($room)) : [];

            if (!$room_record) {
                VCMHttpDocument::getInstance($app)->close(404, 'No valid room found for modifying the rates or restrictions.');
            }

            // update room names map cache, if needed
            if (!isset($room_names_map[$room])) {
                $room_names_map[$room] = $room_record;
            }

            // set dates involved
            $av_helper->setStayDates($from_ymd, $to_ymd);

            if ($av_helper->countNightsOfStay() > 90) {
                // 416 - Range not satisfiable
                VCMHttpDocument::getInstance($app)->close(416, 'Maximum date range is 90 days.');
            }

            try {
                // access the model pricing by binding data
                $model = VBOModelPricing::getInstance([
                    '_created_by' => JText::_('VCM_AI_ASSISTANT'),
                    'from_date'   => $from_ymd,
                    'to_date'     => $to_ymd,
                    'id_room'     => $room_record['id'],
                    'id_price'    => 0,
                    'rplan_name'  => $rplan_name,
                    'rate'        => $rate,
                    'min_los'     => $min_los,
                    'update_otas' => (bool) (!$only_website),
                ]);

                // apply the new rate/restrictions
                $new_rates = $model->modifyRateRestrictions();
            } catch (Exception $e) {
                // analyse the error
                if ($e->getCode() != 409) {
                    // propagate the error when different than "no pricing modifications were actually needed"
                    VCMHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
                }
            }

            // update operation bounds
            $operation_bounds['from_date'] = empty($operation_bounds['from_date']) || $from_date->format('U') < strtotime($operation_bounds['from_date']) ? $from_ymd : $operation_bounds['from_date'];
            $operation_bounds['to_date'] = empty($operation_bounds['to_date']) || $to_date->format('U') < strtotime($operation_bounds['to_date']) ? $to_ymd : $operation_bounds['to_date'];
            if (!in_array($room_record['id'], $operation_bounds['rooms'])) {
                $operation_bounds['rooms'][] = $room_record['id'];
            }

            // set operation response
            $operation_response = [
                'room_name' => $room_record['name'],
                'from_date' => $from_ymd,
                'to_date'   => $to_ymd,
            ];

            if ($rate) {
                $operation_response['rate'] = $rate;
                $operation_response['currency'] = VikBooking::getCurrencyName();
            }

            if ($min_los) {
                $operation_response['min_los'] = $min_los;
            }

            if ($only_website) {
                if ($async_upd_otas) {
                    // inform the AI that channels will be updated asynchronously
                    $operation_response['channels_updated'] = 'OTAs will be automatically and asynchronously updated in background.';
                } else {
                    // inform the AI that channels were not notified
                    $operation_response['only_website'] = true;
                    $operation_response['channels_updated'] = false;
                }
            } else {
                // inform the AI about the channels that were updated
                $operation_response['channels_updated'] = true;

                // build channels involved list
                $channels_list = [];
                foreach (($new_rates['vcm'] ?? []) as $channels_response_data) {
                    foreach (($channels_response_data['channels_updated'] ?? []) as $channel_updated) {
                        if (!$channel_updated || !($channel_updated['id'] ?? 0)) {
                            continue;
                        }
                        // set channel name
                        $channels_list[$channel_updated['id']] = $channel_updated['name'];
                    }
                }

                if ($channels_list) {
                    $operation_response['channels_list'] = array_values($channels_list);
                }
            }

            // push operation response to pool
            $response[] = $operation_response;

            // create add-on for the room rates modification
            (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
                new VCMAiAssistantAddonModifyrates(
                    $from_date,
                    $to_date,
                    $rplan_name,
                    $rate,
                    $min_los,
                    $room_record,
                    $model
                )
            );
        }

        // check whether OTAs should be updated asynchronously
        if ($async_upd_otas) {
            // trigger an automatic bulk action for uploading the rates just applied to the PMS
            VikChannelManager::autoBulkActions([
                'from_date'    => $operation_bounds['from_date'],
                'to_date'      => $operation_bounds['to_date'],
                'forced_rooms' => $operation_bounds['rooms'],
                'update'       => 'rates',
            ]);
        }

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($response);
    }

    /**
     * End-point used to set a reminder within the AI Assistant.
     * 
     * @return  void
     */
    public function set_reminder()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $action         = $app->input->json->getString('action', 'create');
        $datetime       = $app->input->json->getString('datetime');
        $title          = $app->input->json->getString('title', '');
        $summary        = $app->input->json->getString('summary');
        $booking_number = $app->input->json->getString('booking_number', '');

        if (strlen($title) > 128) {
            $title = substr($title, 0, 125) . '..';
        }

        // normalize datetime
        $datetime = JFactory::getDate($datetime);

        // check if the reminder should be tied to a specific reservation ID
        $attach_bid = 0;
        if ($booking_number) {
            $model_res = VBOModelReservation::getInstance([
                '_isAdministrator' => 1,
            ])->setFilters([
                'max_bookings'     => 1,
                'exclude_closures' => true,
                'booking_id'       => $booking_number,
            ]);

            $bookings = $model_res->search();

            if (!$bookings) {
                VCMHttpDocument::getInstance($app)->close(404, $model_res->getError() ?: 'Could not find any reservation according to the provided booking number. Unable to save the reminder.');
            }

            $attach_bid = $bookings[0]['id'];
        }

        // build reminder object
        $reminder = new stdClass;
        $reminder->title = $title;
        $reminder->descr = $summary;
        $reminder->duedate = $datetime->toSql();
        $reminder->usetime = $datetime->format('H:i:s') != '00:00:00' ? 1 : 0;
        $reminder->idorder = $attach_bid;
        $reminder->payload = ['ai' => 1];
        $reminder->important = 1;

        // access the reminders helper object
        $helper = VBORemindersHelper::getInstance();

        // check if an update (usually a date modification) was requested
        $reminder_updated = false;

        if (!strcasecmp($action, 'update')) {
            // attempt to find the reminder to update
            $previous_reminder = $helper->searchReminder([
                'title'   => $title,
                'payload' => ['ai' => 1],
            ]);

            if ($previous_reminder) {
                // set the record ID to update
                $reminder->id = $previous_reminder->id;
                // make sure to unset the booking ID
                unset($reminder->idorder);
                // update record
                if (!$helper->updateReminder($reminder)) {
                    VCMHttpDocument::getInstance($app)->close(500, 'Could not update the reminder.');
                }
                // turn flag on
                $reminder_updated = true;
            }
        }

        if (!$reminder_updated && !$helper->saveReminder($reminder)) {
            VCMHttpDocument::getInstance($app)->close(500, 'Could not create the reminder.');
        }

        // create add-on for the reminder
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonReminder($reminder, $datetime)
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json([
            'success' => true,
        ]);
    }

    /**
     * End-point used by the AI Assistant to notify a customer.
     * 
     * @return  void
     */
    public function notify_customer()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $booking_id = $app->input->json->getInt('booking_id');
        $subject = $app->input->json->getString('subject');
        $message = $app->input->json->getString('message');

        if (!$booking_id) {
            VCMHttpDocument::getInstance($app)->close(400, 'Please specify the customer reservation ID.');
        }

        $model_res = VBOModelReservation::getInstance([
            '_isAdministrator' => 1,
        ])->setFilters([
            'booking_id' => $booking_id,
        ]);

        $bookings = $model_res->search();

        if (!$bookings) {
            VCMHttpDocument::getInstance($app)->close(404, $model_res->getError() ?: 'Could not find any reservation from the provided booking ID.');
        }

        // the guest notification method
        $notification_method = 'email';
        $channel_name = null;

        // determine whether guest messaging support is available
        $ota_messaging_supported = class_exists('VCMChatMessaging');

        if ($ota_messaging_supported && VCMChatMessaging::getInstance($bookings[0])->supportsOtaMessaging($mandatory = true)) {
            // set notification method
            $channel_name = ucfirst(preg_replace("/api$/", '', explode('_', $bookings[0]['channel'])[0]));
            $notification_method = "{$channel_name} Messaging API";
        }

        if (preg_match("/^no-email-[^@]+@[a-z0-9\.]+\.com$/i", (string) $bookings[0]['custmail']) && !strcasecmp($notification_method, 'email')) {
            // false email address, typical of Airbnb, abort the process
            VCMHttpDocument::getInstance($app)->json([
                'status' => false,
                'reason' => 'Unable to notify the guest because of an invalid email address.',
            ]);
        }

        if (!strcasecmp($notification_method, 'email') && (empty($bookings[0]['custmail']) || $bookings[0]['closure'])) {
            VCMHttpDocument::getInstance($app)->json([
                'status' => false,
                'reason' => 'Reservation does not have a valid email address for the guest.',
            ]);
        }

        // create markdown parser
        $markdownParser = new VCMAiHelperMarkdown($message);

        // recipient identifier
        $recipient_id = $bookings[0]['custmail'];

        if (!strcasecmp($notification_method, 'email')) {
            // notify the guest via email
            $vbo_app = VikBooking::getVboApplication();

            // convert markdown into HTML for e-mail messages
            $message = $markdownParser->toHtml();

            // get sender email address
            $admin_sendermail = VikBooking::getSenderMail();

            $subject = $subject ?: 'Email Notification';

            $result = $vbo_app->sendMail($admin_sendermail, $admin_sendermail, $bookings[0]['custmail'], $admin_sendermail, $subject, $message);

            if (!$result) {
                // append execution log with the error description
                VCMHttpDocument::getInstance($app)->json([
                    'status' => false,
                    'reason' => 'Could not notify the guest via email at ' . $bookings[0]['custmail'],
                ]);
            }

            // Booking History
            VikBooking::getBookingHistoryInstance($bookings[0]['id'])->setExtraData(['ai' => 1])->store('CE', nl2br(JText::_('VCM_AI_ASSISTANT') . "\n\n" . $subject . "\n\n" . $message));
        } else {
            // notify the guest through the OTA messaging API
            $recipient_id = trim($channel_name . ' Reservation');

            // convert markdown into plain text for OTA notifications
            $message = $markdownParser->toText();

            // send the message
            $messaging = VCMChatMessaging::getInstance($bookings[0]);
            $result = $messaging->setMessage($message)
                ->setMessageData(['sender_name' => 'AI'])
                ->sendGuestMessage();

            if (!$result && $error = $messaging->getError()) {
                // append execution log with the error description
                VCMHttpDocument::getInstance($app)->json([
                    'status' => false,
                    'reason' => 'An error occurred while sending a notification to the guest: ' . $error,
                ]);
            }
        }

        // create add-on for the message sent
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonNotification(
                $bookings[0]['id'],
                $notification_method,
                $subject ?? '',
                $recipient_id,
                $message
            )
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json([
            'success' => true,
            'notification_method' => $notification_method,
            'recipient' => $recipient_id,
        ]);
    }

    /**
     * End-point used by the channel manager to internally store the content generated by the AI.
     * 
     * @return  void
     * 
     * @since   1.9.5
     */
    public function generated_content()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $content = $app->input->json->get('content', '', 'raw');
        $type = $app->input->json->get('type', 'plain');

        if (!$content) {
            VCMHttpDocument::getInstance($app)->close(400, 'Missing content.');
        }

        // create add-on for the generated content
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonWritercontent($content, $type)
        );

        // send response to caller
        VCMHttpDocument::getInstance($app)->json(['success' => true]);
    }

    /**
     * End-point used to fetch room orphan dates due
     * to min-stay restrictions for the AI Assistant.
     * 
     * @return  void
     * 
     * @since   1.9.9
     */
    public function fetch_orphans()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $from_date    = $app->input->json->getString('start', 'now');
        $to_date      = $app->input->json->getString('end', '+1 week');
        $room         = $app->input->json->getString('room', '');

        // normalize check-in and check-out dates
        $from_date = JFactory::getDate($from_date);
        $to_date   = JFactory::getDate($to_date);

        $from_ymd = $from_date->format('Y-m-d');
        $to_ymd = $to_date->format('Y-m-d');
        if ($from_ymd === $to_ymd) {
            $to_ymd = $to_date->modify('+1 day')->format('Y-m-d');
        }

        // availability helper
        $av_helper = VikBooking::getAvailabilityInstance();

        // find the requested room
        $room_record = $room ? $av_helper->getRoomByName($room) : [];

        // set stay dates
        $av_helper->setStayDates($from_ymd, $to_ymd);

        if ($av_helper->countNightsOfStay() > 31) {
            // 416 - Range not satisfiable
            VCMHttpDocument::getInstance($app)->close(416, 'Maximum date range is 31 days.');
        }

        // prepare orphans fetching options
        $options = [];

        if ($room_record) {
            // set just the requested room record
            $options['room_ids'] = [$room_record['id']];
        } else {
            // make sure not to load more than 10 room records
            $av_helper->loadRooms([], 10);
        }

        // identify the orphan dates
        $orphans = $av_helper->getOrphanDates($options);

        if (!$orphans) {
            // terminate the process with a success code when no orphan dates have been found
            VCMHttpDocument::getInstance($app)->json([
                'status' => (bool) (!$av_helper->getError()),
                'result' => ($av_helper->getError() ?: 'No orphan dates found with the specified parameters, meaning that rooms are either bookable or already booked.'),
            ]);
        }

        // room names pool
        $room_names = [];
        foreach ($av_helper->loadRooms() as $room) {
            $room_names[$room['id']] = $room['name'];
        }

        // create add-on for the orphan dates
        (new VCMAiAssistantHelper($app->input->getString('uuid')))->createAddon(
            new VCMAiAssistantAddonOrphandates($from_date, $to_date, $room_names)
        );

        // build summary message
        $summary  = 'Some dates are not bookable due to minimum stay restrictions and occupied dates nearby. ';
        $summary .= 'The maximum value allowed for the minimum stay restriction is suggested for each date in order to become bookable.';

        // map instructions summary for the AI model
        $orphans = array_map(function($data) use ($summary) {
            return [
                'room_name'    => ($data['room_name'] ?? null),
                'summary'      => $summary,
                'orphan_dates' => ($data['orphans'] ?? []),
            ];
        }, $orphans);

        // send response to caller
        VCMHttpDocument::getInstance($app)->json(array_values($orphans));
    }

    /**
     * End-point used by the AI on a guest conversation to get the details of a booking.
     * 
     * @return  void
     */
    public function guest_booking_details()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $id_order = $app->input->json->getUint('id_order');

        $booking = VikBooking::getBookingInfoFromID($id_order);
        if (!$booking) {
            VCMHttpDocument::getInstance($app)->close(404, 'Booking not found.');
        }

        // build channel source
        $source = 'Direct booking';
        if (!empty($booking['channel'])) {
            $channel_parts = explode('_', $booking['channel']);
            if (count($channel_parts) > 1) {
                $source = $channel_parts[count($channel_parts) - 1];
            } else {
                $source = ucwords($channel_parts[0]);
            }
        }

        // build booking details
        $details = [
            'booking_id'     => $booking['id'],
            'booking_status' => $booking['status'],
            'created_on'     => date('Y-m-d H:i:s', $booking['ts']),
            'checkin'        => date('Y-m-d H:i:s', $booking['checkin']),
            'checkout'       => date('Y-m-d H:i:s', $booking['checkout']),
            'source'         => $source,
        ];

        if (!empty($booking['idorderota']) && !empty($booking['channel'])) {
            // set OTA reservation number
            $details['ota_reservation_number'] = $booking['idorderota'];
        }

        // get rooms and guests information
        $booking_rooms = VikBooking::loadOrdersRoomsData($booking['id']);
        $adults_number = array_sum(array_column($booking_rooms, 'adults'));
        $children_number = array_sum(array_column($booking_rooms, 'children'));
        $room_names = array_column($booking_rooms, 'room_name');

        // set rooms and guests information
        $details['adults']   = $adults_number;
        $details['children'] = $children_number;
        $details['rooms']    = implode(', ', $room_names);

        // access the model for shortening URLs
        $model = VBOModelShortenurl::getInstance($onlyRouted = false)->setBooking($booking);

        // construct booking link
        $use_sid     = empty($booking['sid']) && !empty($booking['idorderota']) ? $booking['idorderota'] : $booking['sid'];
        $bestitemid  = VikBooking::findProperItemIdType(['booking'], (!empty($booking['lang']) ? $booking['lang'] : null));
        $lang_suffix = $bestitemid && !empty($booking['lang']) ? '&lang=' . $booking['lang'] : '';
        $book_link   = VikBooking::externalroute("index.php?option=com_vikbooking&view=booking&sid=" . $use_sid . "&ts=" . $booking['ts'] . $lang_suffix, false, (!empty($bestitemid) ? $bestitemid : null));

        // set booking details link
        $details['details_link'] = $model->getShortUrl($book_link);

        // check if pre-checkin is allowed at this time for the current booking
        $precheckin = VikBooking::precheckinEnabled();
        if ($precheckin) {
            // make sure the limit of days in advance is reflected
            $precheckin_mind = VikBooking::precheckinMinOffset();
            $precheckin_lim_ts = strtotime("+{$precheckin_mind} days 00:00:00");
            $precheckin = ($precheckin_lim_ts <= $booking['checkin'] || ($precheckin_mind === 1 && time() <= $booking['checkin']));
            if (!$precheckin) {
                // set instructions for pre-checkin
                $details['precheckin_instructions'] = 'Online pre-checkin not allowed at this time. Will be done upon arrival.';
            } else {
                // build pre-checkin link after the booking details URL has been routed
                $uri_obj = JUri::getInstance($book_link);
                $uri_obj->setVar('view', 'precheckin');
                $precheckin_link = (string) $uri_obj;

                // set pre-checkin link
                $details['precheckin_link'] = $model->getShortUrl($precheckin_link);

                // set whether the pre-checkin was fulfilled
                $booking_customer = VikBooking::getCPinInstance()->getCustomerFromBooking($booking['id']);
                if (empty($booking_customer['pax_data'])) {
                    // missing pre-checkin details
                    $details['precheckin_fulfilled'] = false;
                } else {
                    // pre-checkin details available
                    $details['precheckin_fulfilled'] = true;
                }
            }
        } else {
            // set instructions for pre-checkin
            $details['precheckin_instructions'] = 'Online pre-checkin is disabled. Will be done upon arrival.';
        }

        // append cancellation and modification instructions
        if (!empty($booking['idorderota']) && !empty($booking['channel'])) {
            // OTA bookings should be cancelled or modified through the OTA
            $details['cancellation_instructions'] = sprintf('This is a reservation made through %s and so it should be cancelled from the OTA account, as long as the cancellation policy allows to do so.', $source);
            $details['modification_instructions'] = sprintf('This is a reservation made through %s and so it should be modified from the OTA account, as long as the booked rate plan allows to do so.', $source);
        } else {
            // check if cancellation or modification is allowed
            $model = VBOModelReservation::getInstance()
                ->setBooking($booking)
                ->setRoomBooking($booking_rooms);

            // get the booking alteration details
            $alterationDetails = $model->getAlterationDetails();

            if ($alterationDetails['refundable'] ?? false) {
                // cancellation allowed
                $details['cancellation_instructions'] = sprintf('Use the details_link to cancel the reservation before %s.', ($alterationDetails['alteration_deadline'] ?? ''));
            } elseif ($alterationDetails['request_alteration'] ?? false) {
                // cancellation request allowed
                $details['cancellation_instructions'] = sprintf('Use the details_link to submit a booking cancellation request before %s.', ($alterationDetails['alteration_deadline'] ?? ''));
            } else {
                // non-refundable, explain the reason
                $details['cancellation_instructions'] = ($alterationDetails['alteration_disabled'] ?? false) ? 'The reservation cannot be cancelled.' : 'Cancellation not allowed due to policy and terms.';
            }

            if ($alterationDetails['cancellation_policy'] ?? null) {
                // append the cancellation policy
                $details['cancellation_policy'] = $alterationDetails['cancellation_policy'];
            }

            if ($alterationDetails['modifiable'] ?? false) {
                // modification allowed
                $details['modification_instructions'] = sprintf('Use the details_link to modify the reservation before %s.', ($alterationDetails['alteration_deadline'] ?? ''));
            } elseif ($alterationDetails['request_alteration'] ?? false) {
                // modification request allowed
                $details['modification_instructions'] = sprintf('Use the details_link to submit a booking modification request before %s.', ($alterationDetails['alteration_deadline'] ?? ''));
            } else {
                // modification not allowed, explain the reason
                $details['modification_instructions'] = ($alterationDetails['alteration_disabled'] ?? false) ? 'The reservation cannot be modified.' : 'Modification not allowed due to policy and terms.';
            }

            if (!($alterationDetails['alteration_disabled'] ?? false) && !($alterationDetails['request_alteration'] ?? false)) {
                // append alteration deadline date
                $details['alteration_deadline'] = $alterationDetails['alteration_deadline'] ?? null;
            }
        }

        // fetch requested action
        $action = $app->input->json->get('action');

        $allowed = [];

        if ($action === 'info') {
            $allowed = ['booking_id', 'booking_status', 'created_on', 'checkin', 'checkout', 'source', 'adults', 'children', 'rooms', 'details_link'];
        } elseif ($action === 'precheckin') {
            $allowed = ['precheckin_instructions', 'precheckin_link', 'precheckin_fulfilled'];
        } elseif ($action === 'alteration') {
            $allowed = ['details_link', 'modification_instructions', 'alteration_deadline'];
        } elseif ($action === 'cancellation') {
            $allowed = ['details_link', 'cancellation_instructions', 'cancellation_policy', 'alteration_deadline'];
        }

        // filter the response details according to the requested action
        $details = array_filter($details, function($key) use ($allowed) {
            return !$allowed || in_array($key, $allowed);
        }, ARRAY_FILTER_USE_KEY);

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($details);
    }

    /**
     * End-point used by the AI on a guest conversation to get the details of the booked listing(s).
     * 
     * @return  void
     * 
     * @since   1.9.12
     */
    public function guest_listing_details()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $id_order = $app->input->json->getUint('id_order');
        $id_listing = $app->input->json->getUint('id_listing');

        if (!$id_order && !$id_listing) {
            VCMHttpDocument::getInstance($app)->close(400, 'Both order ID and listing ID are missing.');
        }

        if ($id_order) {
            // get the reservation and room(s) involved
            $booking = VikBooking::getBookingInfoFromID($id_order);
            $booking_rooms = VikBooking::loadOrdersRoomsData($booking['id'] ?? 0);
            if (!$booking || !$booking_rooms) {
                VCMHttpDocument::getInstance($app)->close(404, 'Booking not found.');
            }

            // build a list of unique room IDs involved in the reservation
            $listing_ids = array_values(array_unique(array_column($booking_rooms, 'idroom')));
        } else {
            $listing_ids = [$id_listing];
        }

        // gather the details for each booked listing
        $listing_details = [];

        foreach ($listing_ids as $listing_id) {
            $listing_details[] = VCMOtaListing::getInstance([
                'details_type' => $app->input->json->get('type'),
            ])->getDetails($listing_id);
        }

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($listing_details);
    }

    /**
     * End-point used by the AI on a guest conversation to get the door access codes for their booking.
     * 
     * @return  void
     * 
     * @since   1.9.14
     */
    public function door_access_passcodes()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // ensure the door access control framework is available
        if (!class_exists('VBODooraccessFactory')) {
            VCMHttpDocument::getInstance($app)->close(501, 'Door access framework not implemented.');
        }

        // get arguments from payload
        $id_order = $app->input->json->getUint('id_order');

        // get the requested reservation
        $booking = VikBooking::getBookingInfoFromID($id_order);
        if (!$booking) {
            VCMHttpDocument::getInstance($app)->close(404, 'Booking not found.');
        }

        // wrap the reservation record within a registry
        $registry = VBOBookingRegistry::getInstance($booking);

        // ensure the guest request takes place at a time within the booking stay dates
        if (!$registry->isStaying()) {
            // the stay dates differ from the current time, check how
            if ($registry->isArrivingToday()) {
                // still too early for the check-in time of today
                VCMHttpDocument::getInstance($app)->json([
                    'reliable' => true,
                    'passcode' => sprintf(
                        'For security reasons, the passcode will only be available after the check-in time at %s.',
                        date('H:i', $registry->getProperty('checkin', 0))
                    )
                ]);
            }
            if ($registry->isFuture()) {
                // still too early for the check-in date
                VCMHttpDocument::getInstance($app)->json([
                    'reliable' => true,
                    'passcode' => sprintf(
                        'For security reasons, the access codes will be available only after the check-in (%s).',
                        date('Y-m-d H:i', $registry->getProperty('checkin', 0))
                    )
                ]);
            }
            if ($registry->isDepartingToday()) {
                // too late for the check-out time
                VCMHttpDocument::getInstance($app)->json([
                    'reliable' => true,
                    'passcode' => sprintf(
                        'For security reasons, the passcode is no longer available after the check-out date and time (today at %s).',
                        date('H:i', $registry->getProperty('checkout', 0))
                    )
                ]);
            }
            // too late for the check-out date
            VCMHttpDocument::getInstance($app)->json([
                'reliable' => true,
                'passcode' => sprintf(
                    'For security reasons, the passcode is no longer available after the check-out date (%s).',
                    date('Y-m-d', $registry->getProperty('checkout', 0))
                )
            ]);
        }

        try {
            // obtain the passcode details for this booking
            $passcodeDetails = VBOFactory::getDoorAccessControl()->getBookingDevicePasscodes($registry);

            if (!$passcodeDetails) {
                throw new Exception('There are no passcodes to share for this reservation.', 500);
            }
        } catch (Exception $e) {
            // send the error to output
            VCMHttpDocument::getInstance($app)->close($e->getCode() ?: 500, $e->getMessage());
        }

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($passcodeDetails);
    }

    /**
     * End-point used by the AI on a guest conversation to get the doors for their booking opened/unlocked.
     * 
     * @return  void
     * 
     * @since   1.9.14
     */
    public function door_access_open()
    {
        $app = JFactory::getApplication();

        if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
            // 401 - Unauthorized
            VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // get arguments from payload
        $id_order = $app->input->json->getUint('id_order');
        $id_device = $app->input->json->getUint('id_device');

        // get the requested reservation
        $booking = VikBooking::getBookingInfoFromID($id_order);
        if (!$booking) {
            VCMHttpDocument::getInstance($app)->close(404, 'Booking not found.');
        }

        // wrap the reservation record within a registry
        $registry = VBOBookingRegistry::getInstance($booking);

        // ensure the guest request takes place at a time within the booking stay dates
        if (!$registry->isStaying()) {
            // the stay dates differ from the current time, check how
            if ($registry->isArrivingToday()) {
                // still too early for the check-in time of today
                VCMHttpDocument::getInstance($app)->json([
                    'reliable' => true,
                    'passcode' => sprintf(
                        'For security reasons, the passcode will only be available after the check-in time at %s.',
                        date('H:i', $registry->getProperty('checkin', 0))
                    )
                ]);
            }
            if ($registry->isFuture()) {
                // still too early for the check-in date
                VCMHttpDocument::getInstance($app)->json([
                    'reliable' => true,
                    'passcode' => sprintf(
                        'For security reasons, the access codes will be available only after the check-in (%s).',
                        date('Y-m-d H:i', $registry->getProperty('checkin', 0))
                    )
                ]);
            }
            if ($registry->isDepartingToday()) {
                // too late for the check-out time
                VCMHttpDocument::getInstance($app)->json([
                    'reliable' => true,
                    'passcode' => sprintf(
                        'For security reasons, the passcode is no longer available after the check-out date and time (today at %s).',
                        date('H:i', $registry->getProperty('checkout', 0))
                    )
                ]);
            }
            // too late for the check-out date
            VCMHttpDocument::getInstance($app)->json([
                'reliable' => true,
                'passcode' => sprintf(
                    'For security reasons, the passcode is no longer available after the check-out date (%s).',
                    date('Y-m-d', $registry->getProperty('checkout', 0))
                )
            ]);
        }

        try {
            // open/unlock the device(s) assigned to the rooms booked
            $unlockResults = VBOFactory::getDoorAccessControl()->handleBookingDeviceUnlock($registry, ['device_id' => $id_device]);

            if (!$unlockResults) {
                throw new Exception('There are no doors or devices to open/unlock for this reservation.', 500);
            }
        } catch (Exception $e) {
            // send the error to output
            VCMHttpDocument::getInstance($app)->close($e->getCode() ?: 500, $e->getMessage());
        }

        // send response to caller
        VCMHttpDocument::getInstance($app)->json($unlockResults);
    }
}
