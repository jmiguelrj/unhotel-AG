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

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerGooglevrlst extends JControllerAdmin
{
    /**
     * Task googlevrlst.generate will generate new listings depending on
     * the available rooms on the website created through Vik Booking.
     * 
     * @return  void
     */
    public function generate()
    {
        /**
         * Token validation.
         * Both GET and POST are supported.
         */
        if (!JSession::checkToken() && !JSession::checkToken('get')) {
            VBOHttpDocument::getInstance()->close(403, JText::_('JINVALID_TOKEN'));
        }

        $dbo = JFactory::getDbo();
        $app = JFactory::getApplication();

        $model = VCMOtaListing::getInstance();

        $q = "SELECT * FROM `#__vikbooking_rooms` WHERE `avail`=1;";
        $dbo->setQuery($q);
        $rooms = $dbo->loadAssocList();

        if (!$rooms) {
            $app->enqueueMessage('No active listings found on your website with Vik Booking.', 'error');
            $app->redirect('index.php?option=com_vikchannelmanager&view=googlevrlistings');
            $app->close();
        }

        $account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::GOOGLEVR);

        if (empty($account_key)) {
            $account_key = $this->getGoogleVrUid();
        }

        if (!$account_key) {
            $app->enqueueMessage('Could not obtain the Google Vacation Rentals account ID.', 'error');
            $app->redirect('index.php?option=com_vikchannelmanager&view=googlevrlistings');
            $app->close();
        }

        // total listings generated
        $tot_generated = 0;

        foreach ($rooms as $room) {
            // check if this listing is available on Airbnb or Booking.com
            $airbnb_listing = null;
            $booking_listing = null;

            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select($dbo->qn('idroomota'))
                    ->select($dbo->qn('idchannel'))
                    ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                    ->where($dbo->qn('idroomvb') . ' = ' . (int) $room['id'])
                    ->andWhere([
                        $dbo->qn('idchannel') . ' = ' . (int) VikChannelManagerConfig::AIRBNBAPI,
                        $dbo->qn('idchannel') . ' = ' . (int) VikChannelManagerConfig::BOOKING,
                    ])
            );
            foreach ($dbo->loadAssocList() as $ota_xref) {
                if ($ota_xref['idchannel'] == VikChannelManagerConfig::AIRBNBAPI) {
                    $airbnb_listing = $model->getItem([
                        'idchannel' => (int) VikChannelManagerConfig::AIRBNBAPI,
                        'idroomota' => $ota_xref['idroomota'],
                    ]);
                }
                if ($ota_xref['idchannel'] == VikChannelManagerConfig::BOOKING) {
                    // this may be available in the future
                    $booking_listing = $model->getItem([
                        'idchannel' => (int) VikChannelManagerConfig::BOOKING,
                        'idroomota' => $ota_xref['idroomota'],
                    ]);
                }
            }

            // prepare needed details
            $checkin_time = '15:00';
            $checkout_time = '11:00';
            $latitude = null;
            $longitude = null;
            $address = null;
            $city = null;
            $state = null;
            $zip = null;
            $country = null;
            $category = 'apartment';
            $bathrooms = 1;
            $bedrooms = 1;
            $beds = 1;

            if ($booking_listing) {
                // in the future we may be able to improve the default data from Booking.com
            }

            if ($airbnb_listing) {
                // try to access the needed details from the Airbnb listing
                $airbnb_data = (array) json_decode($airbnb_listing->setting, true);
                $latitude = $airbnb_data['lat'] ?? $latitude;
                $longitude = $airbnb_data['lng'] ?? $longitude;
                $city = $airbnb_data['city'] ?? $city;
                $state = $airbnb_data['state'] ?? $state;
                $zipcode = $airbnb_data['zipcode'] ?? $zipcode;
                $country = $airbnb_data['country_code'] ?? $country;
                $category = $airbnb_data['property_type_category'] ?? $category;
                $bathrooms = $airbnb_data['bathrooms'] ?? $bathrooms;
                $bedrooms = $airbnb_data['bedrooms'] ?? $bedrooms;
                if ($airbnb_data['check_in_time_start'] ?? '') {
                    $checkin_time = $airbnb_data['check_in_time_start'];
                    if (!strpos($checkin_time, ':')) {
                        $checkin_time .= ':00';
                    }
                }
                if ($airbnb_data['check_out_time'] ?? '') {
                    $checkout_time = $airbnb_data['check_out_time'];
                    if (!strpos($checkout_time, ':')) {
                        $checkout_time .= ':00';
                    }
                }
            }

            // access VikBooking geo info
            $room_params = (array) json_decode($room['params'], true);
            $geo = VikBooking::getGeocodingInstance();

            if (!$address) {
                // attempt to read it from VikBooking
                $address = $geo->getRoomGeoParams($room_params, 'address', '');
            }

            if (!$latitude) {
                // attempt to read it from VikBooking
                $latitude = $geo->getRoomGeoParams($room_params, 'latitude', '');
            }

            if (!$longitude) {
                // attempt to read it from VikBooking
                $longitude = $geo->getRoomGeoParams($room_params, 'longitude', '');
            }

            // build listing photos
            $photos = [];
            if (!empty($room['img'])) {
                // push main photo
                $photos[] = VBO_SITE_URI . 'resources/uploads/' . $room['img'];
            }

            foreach (array_filter(explode(';;', (string) $room['moreimgs'])) as $extraPhoto) {
                // push extra photo
                $photos[] = VBO_SITE_URI . 'resources/uploads/big_' . $extraPhoto;
            }

            // prepare default listing settings payload
            $listing_data = [
                'id'           => $room['id'],
                'name'         => $room['name'],
                'main_photo'   => ($photos[0] ?? ''),
                'photos'       => $photos,
                'active'       => true,
                'on_server'    => false,
                'latitude'     => $latitude,
                'longitude'    => $longitude,
                'address'      => $address,
                'city'         => $city,
                'state'        => $state,
                'zip'          => $zip,
                'country'      => $country,
                'attributes'   => [
                    'capacity' => $room['totpeople'],
                ],
                'max_adults'   => $room['toadult'],
                'max_children' => $room['tochild'],
                'website'      => VikBooking::externalroute('index.php?option=com_vikbooking&view=roomdetails&roomid=' . $room['id'], false),
                'category'     => $category,
                'description'  => strip_tags($room['smalldesc'] ?: $room['info']),
                'number_of_bathrooms' => $bathrooms,
                'number_of_bedrooms'  => $bedrooms,
                'number_of_beds'      => $beds,
                'checkin_time'        => $checkin_time,
                'checkout_time'       => $checkout_time,
                'instant_bookable'    => true,
            ];

            // make sure the record does not exist
            $q = "SELECT `id`, `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)VikChannelManagerConfig::GOOGLEVR . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($room['id']) . " AND `param`=" . $dbo->quote('listing_content');
            $dbo->setQuery($q, 0, 1);
            $prev_data = $dbo->loadObject();
            if ($prev_data) {
                // do nothing, skip to the next room because this one exists already
                continue;
            }

            // prepare record
            $listing = new stdClass;
            $listing->idchannel   = VikChannelManagerConfig::GOOGLEVR;
            $listing->account_key = $account_key;
            $listing->idroomota   = $room['id'];
            $listing->param       = 'listing_content';
            $listing->setting     = json_encode($listing_data);

            $dbo->insertObject('#__vikchannelmanager_otarooms_data', $listing, 'id');
            if (!isset($listing->id)) {
                $app->enqueueMessage('Could not create the listing ' . $listing_data['name'] . ' from Vik Booking.', 'error');
                continue;
            }
            $tot_generated++;
        }

        if ($tot_generated) {
            $app->enqueueMessage(JText::_('VCM_VRBO_GEN_FROM_WEBSITE') . ": {$tot_generated}", 'success');
        }

        $app->redirect('index.php?option=com_vikchannelmanager&view=googlevrlistings');
        $app->close();
    }

    /**
     * Task googlevrlst.updatelisting updates a listing.
     */
    public function updatelisting()
    {
        $this->_doUpdateListing();
    }

    /**
     * Task googlevrlst.updatelisting_stay updates a listing (no redirect).
     */
    public function updatelisting_stay()
    {
        $this->_doUpdateListing(true);
    }

    /**
     * Task googlevrlst.delete will delete a given listing remotely and internally.
     */
    public function delete()
    {
        $app = JFactory::getApplication();
        $dbo = JFactory::getDbo();

        /**
         * Token validation.
         * Both GET and POST are supported.
         */
        if (!JSession::checkToken() && !JSession::checkToken('get')) {
            VBOHttpDocument::getInstance()->close(403, JText::_('JINVALID_TOKEN'));
        }

        $listing_id = $app->input->getInt('listing_id');

        if (!$listing_id) {
            VBOHttpDocument::getInstance()->close(400, 'Missing listing ID.');
        }

        // delete the listing internally
        $dbo->setQuery(
            $dbo->getQuery(true)
                ->delete($dbo->qn('#__vikchannelmanager_otarooms_data'))
                ->where($dbo->qn('idchannel') . ' = ' . (int) VikChannelManagerConfig::GOOGLEVR)
                ->where($dbo->qn('idroomota') . ' = ' . $listing_id)
        );
        $dbo->execute();

        // delete the room relation, if any
        $dbo->setQuery(
            $dbo->getQuery(true)
                ->delete($dbo->qn('#__vikchannelmanager_roomsxref'))
                ->where($dbo->qn('idchannel') . ' = ' . (int) VikChannelManagerConfig::GOOGLEVR)
                ->where($dbo->qn('idroomvb') . ' = ' . $listing_id)
        );
        $dbo->execute();

        // delete the listing remotely
        $transporter = new E4jConnectRequest('https://e4jconnect.com/channelmanager/v2/google/vacation-rentals/listings/' . $listing_id, false);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');
        try {
            // delete the remote listing
            $transporter->fetch('DELETE');
        } catch (Exception $e) {
            // silently catch the error
            $app->enqueueMessage(sprintf('Could not delete the listing remotely - %s', $e->getMessage()), 'error');
        }

        $app->redirect('index.php?option=com_vikchannelmanager&view=googlevrlistings');
        $app->close();
    }

    /**
     * Task googlevrlst.sendTransaction will perform a transaction (property data) request to Google.
     * 
     * @since   1.9.13 added support for both GET and AJAX requests/responses.
     */
    public function sendTransaction()
    {
        $app = JFactory::getApplication();
        $dbo = JFactory::getDbo();

        /**
         * Token validation.
         * Both GET and POST are supported.
         */
        if (!JSession::checkToken() && !JSession::checkToken('get')) {
            VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
        }

        // determine if we are running an AJAX request
        $is_ajax = $app->input->getBool('is_ajax', false);

        // access the listing ID
        $listing_id = $app->input->getInt('listing_id');

        if (!$listing_id) {
            VCMHttpDocument::getInstance($app)->close(400, 'Missing listing ID.');
        }

        $model = VCMOtaListing::getInstance();

        $listing = $model->getItem([
            'idchannel' => (int) VikChannelManagerConfig::GOOGLEVR,
            'idroomota' => $listing_id,
        ]);

        if (!$listing) {
            if ($is_ajax) {
                VCMHttpDocument::getInstance($app)->close(404, 'Could not find listing from ID.');
            }
            $app->enqueueMessage('Could not find listing from ID.', 'error');
            $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrlistings");
            $app->close();
        }

        $listing_data = is_string($listing->setting) ? json_decode($listing->setting, true) : $listing->setting;

        if (!is_array($listing_data) || !$listing_data) {
            if ($is_ajax) {
                VCMHttpDocument::getInstance($app)->close(500, 'Missing listing data. Delete the listing and generate it again.');
            }
            $app->enqueueMessage('Missing listing data. Delete the listing and generate it again.', 'error');
            $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrlistings");
            $app->close();
        }

        // load current module
        $module = VikChannelManager::getActiveModule(true);
        if ($module['uniquekey'] != VikChannelManagerConfig::GOOGLEVR) {
            if ($is_ajax) {
                VCMHttpDocument::getInstance($app)->close(500, 'Please make sure Google Vacation Rentals is the active channel.');
            }
            $app->enqueueMessage('Please make sure Google Vacation Rentals is the active channel.', 'error');
            $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrlistings");
            $app->close();
        }

        // perform the transaction (property data) operation for the current listing
        $result = VikChannelManager::transmitPropertyData($module, [$listing->idroomota]);

        if (!is_object($result)) {
            // an error occurred
            $google_err = is_string($result) ? $result : '';
            $google_err = $google_err ?: 'A generic error occurred during the transaction (property-data) operation with Google.';

            if ($is_ajax) {
                VCMHttpDocument::getInstance($app)->close(500, $google_err);
            }

            $app->enqueueMessage($google_err, 'error');
            $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrlistings");
            $app->close();
        }

        // register the last successful transaction date-time for this listing
        $listing_data['transactioned_on'] = date('c');

        // update listing details internally
        $model->saveItem([
            'id' => $listing->id,
            'setting' => json_encode($listing_data),
        ]);

        // success
        if ($is_ajax) {
            VCMHttpDocument::getInstance($app)->json([
                'success' => true,
                'message' => 'Operation completed successfully!',
            ]);
        }
        $app->enqueueMessage('Operation completed successfully!', 'success');
        $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrlistings");
        $app->close();
    }

    /**
     * Task googlevrlst.cancel goes back to the products list page.
     */
    public function cancel()
    {
        JFactory::getApplication()->redirect('index.php?option=com_vikchannelmanager&view=googlevrlistings');
    }

    /**
     * Protected method to update a listing as well as others of its details.
     * 
     * @param   bool    $stay   whether to redirect to the same page.
     * 
     * @return  void
     */
    protected function _doUpdateListing($stay = false)
    {
        $app = JFactory::getApplication();
        $dbo = JFactory::getDbo();

        $model = VCMOtaListing::getInstance();

        $is_ajax = $app->input->getInt('aj', 0);
        $idroomota = $app->input->getInt('idroomota', 0);
        $listing_values = $app->input->get('listing', [], 'array');

        $listing = $model->getItem([
            'idchannel' => (int) VikChannelManagerConfig::GOOGLEVR,
            'idroomota' => $idroomota,
        ]);

        if (!$listing) {
            if ($is_ajax) {
                VBOHttpDocument::getInstance()->close(400, 'Could not find listing from ID.');
            }
            $app->enqueueMessage('Could not find listing from ID.', 'error');
            if ($stay) {
                $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrmnglisting&idroomota={$idroomota}");
            } else {
                $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrlistings");
            }
            $app->close();
        }

        $listing_data = is_string($listing->setting) ? json_decode($listing->setting, true) : $listing->setting;

        if (!is_array($listing_data) || !$listing_data) {
            if ($is_ajax) {
                VBOHttpDocument::getInstance()->close(400, 'Missing listing data. Delete the listing and generate it again.');
            }
            $app->enqueueMessage('Missing listing data. Delete the listing and generate it again.', 'error');
            if ($stay) {
                $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrmnglisting&idroomota={$idroomota}");
            } else {
                $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrlistings");
            }
            $app->close();
        }

        // always attempt to update the listing main photo
        if (!empty($listing_data['photos'])) {
            $listing_data['main_photo'] = $listing_data['photos'][0] ?? $listing_data['main_photo'];
        }

        // whether the listing is on the remote servers
        $on_server = $listing_data['on_server'] ?? false;

        // merge listing information with new values
        $listing_data = array_merge($listing_data, $listing_values);

        // build listing attributes
        $listing_attributes = $listing_data;

        // clean up attribute values
        unset($listing_attributes['id'], $listing_attributes['on_server'], $listing_attributes['name'], $listing_attributes['active'], $listing_attributes['capacity']);

        // always refresh the listing website URL
        $listing_attributes['website'] = VikBooking::externalroute('index.php?option=com_vikbooking&view=roomdetails&roomid=' . $listing->idroomota, false);

        // set the created on property, if not available
        if (!($listing_data['created_on'] ?? null)) {
            $listing_data['created_on'] = date('c');
        }

        // build listing payload
        $listing_payload = [
            'listing_id'   => $listing->idroomota,
            'listing_name' => $listing_data['name'] ?? null,
            'notifyurl'    => JUri::root(),
            'lang'         => JFactory::getLanguage()->getTag(),
            'active'       => (int) ($listing_data['active'] ?? 0),
            'attributes'   => $this->normalizeAttributes($listing_attributes),
        ];

        // remote endpoint
        $url = 'https://e4jconnect.com/channelmanager/v2/google/vacation-rentals/listings';
        if ($on_server) {
            // update existing listing ID
            $url = 'https://e4jconnect.com/channelmanager/v2/google/vacation-rentals/listings/' . $listing->idroomota;
            unset($listing_payload['listing_id']);
        }

        // attempt to store the remote listing details
        $transporter = new E4jConnectRequest($url, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields($listing_payload);

        try {
            // create or update the remote listing
            $transporter->fetch($on_server ? 'PUT' : 'POST');

            // always turn on the "on_server" reserved property
            $listing_data['on_server'] = true;
        } catch (Exception $e) {
            // update the internal listing details no matter what
            $model->saveItem([
                'id' => $listing->id,
                'setting' => json_encode($listing_data),
            ]);
            
            // raise an error
            VBOHttpDocument::getInstance()->close($e->getCode() ?: 500, $e->getMessage());
        }

        // update listing details also internally
        $model->saveItem([
            'id' => $listing->id,
            'setting' => json_encode($listing_data),
        ]);

        if ($listing_data['active'] ?? 0) {
            // when the listing is active, ensure it's mapped or map it automatically
            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select($dbo->qn('id'))
                    ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                    ->where($dbo->qn('idroomvb') . ' = ' . $listing->idroomota)
                    ->where($dbo->qn('idchannel') . ' = ' . (int) VikChannelManagerConfig::GOOGLEVR)
            );
            
            if (!$dbo->loadResult()) {
                // map the listing

                // load rate plans
                $room_rplans = [
                    'RatePlan' => [],
                ];

                $dbo->setQuery(
                    $dbo->getQuery(true)
                        ->select([
                            $dbo->qn('id'),
                            $dbo->qn('name'),
                            $dbo->qn('breakfast_included'),
                            $dbo->qn('free_cancellation'),
                            $dbo->qn('canc_deadline'),
                            $dbo->qn('minlos'),
                            $dbo->qn('minhadv'),
                        ])
                        ->from($dbo->qn('#__vikbooking_prices'))
                        ->order($dbo->qn('id') . ' ASC')
                );

                foreach ($dbo->loadAssocList() as $rplan) {
                    $room_rplans['RatePlan'][$rplan['id']] = $rplan;
                }

                // get the account key
                $account_key = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::GOOGLEVR, '');

                $mapping = new stdClass;
                $mapping->idroomvb = $listing->idroomota;
                $mapping->idroomota = $listing->idroomota;
                $mapping->idchannel = (int) VikChannelManagerConfig::GOOGLEVR;
                $mapping->channel = 'googlevr';
                $mapping->otaroomname = $listing_data['name'] ?? '';
                $mapping->otapricing = json_encode($room_rplans);
                $mapping->prop_name = sprintf('Website %s', $account_key);
                $mapping->prop_params = json_encode(['hotelid' => $account_key]);

                $dbo->insertObject('#__vikchannelmanager_roomsxref', $mapping, 'id');
            }
        }

        // process completed

        if ($is_ajax) {
            VBOHttpDocument::getInstance()->json(['ok' => 1, 'id' => $idroomota]);
        }

        $app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
        if ($stay) {
            $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrmnglisting&idroomota={$idroomota}");
        } else {
            $app->redirect("index.php?option=com_vikchannelmanager&view=googlevrlistings&loaded=1");
        }
        $app->close();
    }

    /**
     * Normalizes the associative list of listing attributes to comply with some field types.
     * 
     * @param   array   $attributes     Associative list of attributes to normalize.
     * 
     * @return  array   The normalized associative list of attributes.
     */
    protected function normalizeAttributes(array $attributes)
    {
        // list of non-boolean attributes
        $non_boolean_attr = [
            'capacity',
            'max_adults',
            'max_children',
            'number_of_bathrooms',
            'number_of_bedrooms',
            'number_of_beds',
        ];

        // scan the list of attributes
        foreach ($attributes as $name => &$value) {
            if (is_array($value) && $value && array_keys($value) !== range(0, count($value) - 1)) {
                // recursively scan the associative list
                $value = $this->normalizeAttributes($value);

                // go to next level
                continue;
            }

            if (is_string($value)) {
                // check for boolean values to be converted
                if (!in_array($name, $non_boolean_attr)) {
                    if ($value === '0') {
                        // convert boolean attribute into "Yes/No"
                        $value = 'No';
                    } elseif ($value === '1') {
                        // convert boolean attribute into "Yes/No"
                        $value = 'Yes';
                    }
                }
                
                // check for empty strings
                if ($value === '') {
                    // do not pass empty string attributes
                    unset($attributes[$name]);
                }
            }
        }

        // unset last reference
        unset($value);

        // return the clean list
        return $attributes;
    }

    /**
     * Makes a request to get the UID for Google VR. In case of
     * success, the value obtained will be stored onto the db.
     * 
     * @return  string   The account ID or an empty string.
     */
    protected function getGoogleVrUid()
    {
        $dbo = JFactory::getDbo();

        $transporter = new E4jConnectRequest('https://e4jconnect.com/channelmanager/v2/google/vacation-rentals/account', true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        try {
            // fetch the user account ID
            $account_id = (string) $transporter->fetch('GET');
        } catch (Exception $e) {
            // do nothing
            $account_id = '';
        }

        if ($account_id) {
            // set value on database
            VCMFactory::getConfig()->set('account_key_' . VikChannelManagerConfig::GOOGLEVR, $account_id);

            // update channel value
            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select([
                        $dbo->qn('id'),
                        $dbo->qn('params'),
                    ])
                    ->from($dbo->qn('#__vikchannelmanager_channel'))
                    ->where($dbo->qn('uniquekey') . ' = ' . $dbo->q(VikChannelManagerConfig::GOOGLEVR))
            );

            foreach ($dbo->loadAssocList() as $ch_record) {
                $ch_account = json_decode($ch_record['params'], true);
                if (!$ch_account || empty($ch_account['hotelid'])) {
                    // update channel account value
                    $ch_account['hotelid'] = $account_id;
                    $ch_record['params'] = json_encode($ch_account);
                    $upd_record = (object) $ch_record;
                    $dbo->updateObject('#__vikchannelmanager_channel', $upd_record, 'id');
                }
            }
        }

        return $account_id;
    }
}
