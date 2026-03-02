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
 * OTA Reporting requestor. Used to report changes to OTA bookings.
 * 
 * @since   1.8.24
 */
final class VCMOtaReporting extends JObject
{
    /**
     * The singleton instance of the class.
     *
     * @var  VCMOtaReporting
     */
    private static $instance = null;

    /**
     * Proxy to construct the object.
     * 
     * @param   array|object  $data  optional data to bind.
     * @param   boolean       $anew  true for forcing a new instance.
     * 
     * @return  self
     */
    public static function getInstance($data = [], $anew = false)
    {
        if (is_null(static::$instance) || $anew) {
            static::$instance = new static($data);
        }

        return static::$instance;
    }

    /**
     * Tells whether the given reservation can be reported for a stay change.
     * 
     * @return  bool    true if the injected reservation can report a stay change.
     */
    public function stayChangeAllowed()
    {
        $channel  = $this->get('channel');
        $ota_bid  = $this->get('idorderota');
        $status   = $this->get('status', '');
        $checkin  = $this->get('checkin', 0);
        $checkout = $this->get('checkout', 0);

        if (empty($channel) || empty($ota_bid) || strcasecmp($status, 'confirmed')) {
            // only confirmed OTA reservations are allowed
            return false;
        }

        $today_midnight_ts = strtotime(date('Y-m-d') . ' 00:00:00');

        if (strtotime(date('Y-m-d', $checkin)) > $today_midnight_ts) {
            // check-in date must be in the past and the guest must have checked in already
            return false;
        }

        if (strtotime(date('Y-m-d', $checkout)) < strtotime('-1 day', $today_midnight_ts)) {
            // check-out date must be in the future or not past one day
            return false;
        }

        // only Booking.com supports this reporting API
        if (stripos($channel, 'booking.com') === false) {
            return false;
        }

        return true;
    }

    /**
     * Tells whether certain reporting operations are allowed (Booking.com).
     * 
     * @return  bool
     */
    public function reportingAllowed()
    {
        $channel  = $this->get('channel', '');

        // only Booking.com supports this reporting API
        if (!$channel || stripos($channel, 'booking.com') === false) {
            return false;
        }

        // ensure the channel is installed and configured
        $channel_names = explode('_', $channel);

        // get channel data
        $channel_data = VikChannelManager::getChannelFromName($channel_names[0]);

        if (!$channel_data) {
            // channel not found
            return false;
        }

        return true;
    }

    /**
     * Notifies the OTA with the room reservation stay change data.
     * 
     * @param   array   $stay_change_data   list of room stay change data.
     * 
     * @return  bool    true on success, or false by setting errors.
     */
    public function notifyStayChange(array $stay_change_data)
    {
        // ensure the channel is installed and configured
        $channel_names = explode('_', (string) $this->get('channel', ''));

        if (empty($channel_names[0])) {
            // channel name is empty
            $this->setError('Reservation OTA details not found');
            return false;
        }

        // get the OTA reservation ID
        $ota_bid = $this->get('idorderota');
        if (!$ota_bid) {
            // OTA reservation ID is empty
            $this->setError('OTA reservation ID is empty');
            return false;
        }

        // get channel data
        $channel_data = VikChannelManager::getChannelFromName($channel_names[0]);

        if (!$channel_data) {
            // channel not found
            $this->setError('The channel [' . ucwords($channel_names[0]) . '] is not installed or configured');
            return false;
        }

        if ($channel_data['uniquekey'] != VikChannelManagerConfig::BOOKING) {
            // only Booking.com supports this reporting API
            $this->setError('Only Booking.com supports this reporting API');
            return false;
        }

        // get the E4jConnect API key
        $apikey = VikChannelManager::getApiKey();

        if (!$apikey) {
            $this->setError('Missing E4jConnect API key');
            return false;
        }

        $dbo = JFactory::getDbo();

        // find the OTA room IDs and account ID
        $ota_account_id = null;
        $room_change_list = [];
        foreach ($stay_change_data as $ind => $stay_change) {
            if (empty($stay_change['idroom']) || empty($stay_change['checkin']) || empty($stay_change['checkout'])) {
                // invalid stay change data not allowed
                $this->setError('Invalid stay change data ' . json_encode($stay_change));
                return false;
            }

            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select([
                        $dbo->qn('idroomota'),
                        $dbo->qn('prop_params'),
                    ])
                    ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                    ->where($dbo->qn('idchannel') . ' = ' . $channel_data['uniquekey'])
                    ->where($dbo->qn('idroomvb') . ' = ' . (int)$stay_change['idroom'])
            , 0, 1);
            $ota_room_record = $dbo->loadAssoc();

            if (!$ota_room_record) {
                // room relation not found
                continue;
            }

            // set OTA account ID
            $ota_params = json_decode($ota_room_record['prop_params'], true);
            if (!$ota_params) {
                // invalid account information
                continue;
            }
            foreach ($ota_params as $ota_param_val) {
                // assign the first property value
                $ota_account_id = $ota_param_val;
                break;
            }

            // set stay change value for the request
            $room_change_list[] = [
                'id'       => $ota_room_record['idroomota'],
                'index'    => $stay_change['index'] ?? null,
                'checkin'  => $stay_change['checkin'],
                'checkout' => $stay_change['checkout'],
                'price'    => $stay_change['price'] ?? null,
            ];
        }

        if (!$ota_account_id || !$room_change_list) {
            // could not find valid mapping information
            $this->setError('Could not find valid rooms mapping and account information');
            return false;
        }

        // build channel endpoint
        $endpoint = "https://e4jconnect.com/channelmanager/v2/bookingcom/reporting/{$ota_account_id}/stay-change/{$ota_bid}";

        // start the transporter with slaves support on REST /v2 endpoint
        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth($apikey, 'application/json')
            ->setPostFields([
                'rooms' => $room_change_list,
            ]);

        try {
            // perform a POST request
            $transporter->fetch('POST');

            // store the booking history event
            VikBooking::getBookingHistoryInstance($this->get('id', 0))
                ->setExtraData([
                    'type'  => 'bcom_stay_change',
                    'rooms' => $room_change_list,
                ])
                ->store(
                    'CM',
                    'Booking.com Reporting API - Stay Change - Updated'
                );
        } catch (Exception $e) {
            // set the error and abort
            $this->setError(
                sprintf('Error (%s) from %s: %s',
                    $e->getCode(),
                    ucwords($channel_data['name']),
                    $e->getMessage()
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Notifies the OTA for the guest registration no-show.
     * 
     * @param   bool   $waived_fees   whether the host has waived the fees.
     * 
     * @return  bool   true on success, or false by setting errors.
     */
    public function notifyNoShow($waived_fees = false)
    {
        if (!$this->reportingAllowed()) {
            $this->setError('OTA no-show reporting is not allowed');
            return false;
        }

        // ensure the channel is installed and configured
        $channel_names = explode('_', (string) $this->get('channel', ''));

        if (empty($channel_names[0])) {
            // channel name is empty
            $this->setError('Reservation OTA details not found');
            return false;
        }

        // get the OTA reservation ID
        $ota_bid = $this->get('idorderota');
        if (!$ota_bid) {
            // OTA reservation ID is empty
            $this->setError('OTA reservation ID is empty');
            return false;
        }

        // get channel data
        $channel_data = VikChannelManager::getChannelFromName($channel_names[0]);

        if (!$channel_data) {
            // channel not found
            $this->setError('The channel [' . ucwords($channel_names[0]) . '] is not installed or configured');
            return false;
        }

        if ($channel_data['uniquekey'] != VikChannelManagerConfig::BOOKING) {
            // only Booking.com supports this reporting API
            $this->setError('Only Booking.com supports this reporting API');
            return false;
        }

        // get the E4jConnect API key
        $apikey = VikChannelManager::getApiKey();

        if (!$apikey) {
            $this->setError('Missing E4jConnect API key');
            return false;
        }

        // load booking rooms
        $booking_rooms = VikBooking::loadOrdersRoomsData($this->get('id', 0));
        if (!$booking_rooms) {
            $this->setError('Room reservation records not found');
            return false;
        }

        $dbo = JFactory::getDbo();

        // find the OTA room IDs and account ID
        $ota_account_id = null;
        foreach ($booking_rooms as $booking_room) {
            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select([
                        $dbo->qn('idroomota'),
                        $dbo->qn('prop_params'),
                    ])
                    ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                    ->where($dbo->qn('idchannel') . ' = ' . $channel_data['uniquekey'])
                    ->where($dbo->qn('idroomvb') . ' = ' . (int)$booking_room['idroom'])
            , 0, 1);
            $ota_room_record = $dbo->loadAssoc();

            if (!$ota_room_record) {
                // room relation not found
                continue;
            }

            // set OTA account ID
            $ota_params = json_decode($ota_room_record['prop_params'], true);
            if (!$ota_params) {
                // invalid account information
                continue;
            }
            foreach ($ota_params as $ota_param_val) {
                // assign the first property value
                $ota_account_id = $ota_param_val;
                break;
            }

            // no-show is at reservation-level, not at room-level, so this is enough
            break;
        }

        if (!$ota_account_id) {
            // could not find valid mapping information
            $this->setError('Could not find valid rooms mapping and account information');
            return false;
        }

        // build channel endpoint
        $endpoint = "https://e4jconnect.com/channelmanager/v2/bookingcom/reporting/{$ota_account_id}/no-show/{$ota_bid}";

        // start the transporter with slaves support on REST /v2 endpoint
        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth($apikey, 'application/json')
            ->setPostFields([
                'waived_fees' => (bool) $waived_fees,
            ]);

        try {
            // perform a POST request
            $transporter->fetch('POST');

            // store the booking history event
            VikBooking::getBookingHistoryInstance($this->get('id', 0))
                ->setExtraData([
                    'type'        => 'bcom_no_show',
                    'waived_fees' => (int) $waived_fees,
                ])
                ->store(
                    'CM',
                    'Guest no-show notification'
                );
        } catch (Exception $e) {
            // set the error and abort
            $this->setError(
                sprintf('Error (%s) from %s: %s',
                    $e->getCode(),
                    ucwords($channel_data['name']),
                    $e->getMessage()
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Notifies the OTA for a booking invalid credit card.
     * 
     * @param   bool   $cancel_res   whether to request the cancellation of the reservation.
     * 
     * @return  bool   true on success, or false by setting errors.
     */
    public function notifyInvalidCreditCard($cancel_res = false)
    {
        if (!$this->reportingAllowed()) {
            $this->setError('OTA invalid credit card reporting is not allowed');
            return false;
        }

        // ensure the channel is installed and configured
        $channel_names = explode('_', (string) $this->get('channel', ''));

        if (empty($channel_names[0])) {
            // channel name is empty
            $this->setError('Reservation OTA details not found');
            return false;
        }

        // get the OTA reservation ID
        $ota_bid = $this->get('idorderota');
        if (!$ota_bid) {
            // OTA reservation ID is empty
            $this->setError('OTA reservation ID is empty');
            return false;
        }

        // get channel data
        $channel_data = VikChannelManager::getChannelFromName($channel_names[0]);

        if (!$channel_data) {
            // channel not found
            $this->setError('The channel [' . ucwords($channel_names[0]) . '] is not installed or configured');
            return false;
        }

        if ($channel_data['uniquekey'] != VikChannelManagerConfig::BOOKING) {
            // only Booking.com supports this reporting API
            $this->setError('Only Booking.com supports this reporting API');
            return false;
        }

        // get the E4jConnect API key
        $apikey = VikChannelManager::getApiKey();

        if (!$apikey) {
            $this->setError('Missing E4jConnect API key');
            return false;
        }

        // load booking rooms
        $booking_rooms = VikBooking::loadOrdersRoomsData($this->get('id', 0));
        if (!$booking_rooms) {
            $this->setError('Room reservation records not found');
            return false;
        }

        $dbo = JFactory::getDbo();

        // find the OTA room IDs and account ID
        $ota_account_id = null;
        foreach ($booking_rooms as $booking_room) {
            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select([
                        $dbo->qn('idroomota'),
                        $dbo->qn('prop_params'),
                    ])
                    ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                    ->where($dbo->qn('idchannel') . ' = ' . $channel_data['uniquekey'])
                    ->where($dbo->qn('idroomvb') . ' = ' . (int)$booking_room['idroom'])
            , 0, 1);
            $ota_room_record = $dbo->loadAssoc();

            if (!$ota_room_record) {
                // room relation not found
                continue;
            }

            // set OTA account ID
            $ota_params = json_decode($ota_room_record['prop_params'], true);
            if (!$ota_params) {
                // invalid account information
                continue;
            }
            foreach ($ota_params as $ota_param_val) {
                // assign the first property value
                $ota_account_id = $ota_param_val;
                break;
            }

            // no-show is at reservation-level, not at room-level, so this is enough
            break;
        }

        if (!$ota_account_id) {
            // could not find valid mapping information
            $this->setError('Could not find valid rooms mapping and account information');
            return false;
        }

        // build channel endpoint
        $endpoint = "https://e4jconnect.com/channelmanager/v2/bookingcom/reporting/{$ota_account_id}/invalid-cc/{$ota_bid}";

        // start the transporter with slaves support on REST /v2 endpoint
        $transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth($apikey, 'application/json')
            ->setPostFields([
                'cancel' => (bool) $cancel_res,
            ]);

        try {
            // perform a POST request
            $transporter->fetch('POST');

            // store the booking history event
            VikBooking::getBookingHistoryInstance($this->get('id', 0))
                ->setExtraData([
                    'type'               => 'bcom_invalid_cc',
                    'cancel_reservation' => (int) $cancel_res,
                ])
                ->store(
                    'CM',
                    'Invalid credit card ' . ($cancel_res ? 'booking cancellation request' : 'notification')
                );
        } catch (Exception $e) {
            // set the error and abort
            $this->setError(
                sprintf('Error (%s) from %s: %s',
                    $e->getCode(),
                    ucwords($channel_data['name']),
                    $e->getMessage()
                )
            );

            return false;
        }

        return true;
    }
}
