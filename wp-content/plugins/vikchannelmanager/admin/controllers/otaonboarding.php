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

JLoader::import('adapter.mvc.controllers.admin');

/**
 * OTA Onboarding admin controller.
 * 
 * @since  1.9.2
 */
class VikChannelManagerControllerOtaonboarding extends JControllerAdmin
{
    /**
     * Task used via AJAX to populate the form to onboard a new listing.
     *
     * @return  void
     */
    public function new()
    {
        $app = JFactory::getApplication();
        $dbo = JFactory::getDbo();

        if (!JSession::checkToken()) {
            // missing CSRF-proof token
            VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
        }

        $eligible = [
            VikChannelManagerConfig::BOOKING => VCMOtaOnboardingProcessorBookingcom::class,
            VikChannelManagerConfig::AIRBNBAPI => VCMOtaOnboardingProcessorAirbnbapi::class,
        ];

        $channel_id = $app->input->getInt('channel_id', 0);
        $room_id = $app->input->getInt('room_id', 0);

        $onboard_to = VikChannelManager::getChannel($channel_id);

        if (!$onboard_to) {
            // invalid request
            VCMHttpDocument::getInstance($app)->close(400, 'Could not find the channel for onboarding the new listing.');
        }

        // ideally, get the listing details from the channel where the room is mapped, hence the opposite channel where we are onboarding
        $channel_diff = array_values(array_diff(array_map('intval', array_keys($eligible)), [$channel_id]));
        $download_from = $channel_diff[0];

        // fetch the room record details from VikBooking
        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select('*')
                ->from($dbo->qn('#__vikbooking_rooms'))
                ->where($dbo->qn('id') . ' = ' . $room_id)
        , 0, 1);
        $vbo_listing = $dbo->loadAssoc();

        if (!$vbo_listing) {
            VCMHttpDocument::getInstance($app)->close(404, 'Could not find the VikBooking room.');
        } else {
            // attempt to decode the params and related geographical information
            $vbo_listing['params'] = (array) json_decode(($vbo_listing['params'] ?: '[]'), true);
        }

        // attempt to get the listing contents from the other eligible OTA to facilitate the onboarding
        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select([
                    $dbo->qn('x.idroomvb'),
                    $dbo->qn('x.idroomota'),
                    $dbo->qn('x.idchannel'),
                    $dbo->qn('x.channel'),
                    $dbo->qn('x.otaroomname'),
                    $dbo->qn('x.prop_name'),
                    $dbo->qn('x.prop_params'),
                    $dbo->qn('d.account_key'),
                    $dbo->qn('d.param'),
                    $dbo->qn('d.setting'),
                ])
                ->from($dbo->qn('#__vikchannelmanager_roomsxref', 'x'))
                ->leftJoin($dbo->qn('#__vikchannelmanager_otarooms_data', 'd') . ' ON ' . $dbo->qn('x.idroomota') . ' = ' . $dbo->qn('d.idroomota') . ' AND ' . $dbo->qn('x.idchannel') . ' = ' . $dbo->qn('d.idchannel'))
                ->where($dbo->qn('x.idroomvb') . ' = ' . $room_id)
                ->where($dbo->qn('x.idchannel') . ' IN (' . implode(', ', array_map('intval', $channel_diff)) . ')')
        , 0, 1);

        $listing_contents = $dbo->loadAssoc();

        if (!$listing_contents) {
            /**
             * We may be onboarding a listing that is currently not available on any eligible OTA
             * although at least one of the eligible OTAs is configured with other listings mapped.
             * Try to fetch the OTA account information where we want to onboard the listing.
             * 
             * @since   1.9.10
             */
            $dbo->setQuery(
                $dbo->getQuery(true)
                    ->select([
                        $dbo->qn('x.idroomvb'),
                        $dbo->qn('x.idroomota'),
                        $dbo->qn('x.idchannel'),
                        $dbo->qn('x.channel'),
                        $dbo->qn('x.otaroomname'),
                        $dbo->qn('x.prop_name'),
                        $dbo->qn('x.prop_params'),
                        $dbo->qn('d.account_key'),
                        $dbo->qn('d.param'),
                        $dbo->qn('d.setting'),
                    ])
                    ->from($dbo->qn('#__vikchannelmanager_roomsxref', 'x'))
                    ->leftJoin($dbo->qn('#__vikchannelmanager_otarooms_data', 'd') . ' ON ' . $dbo->qn('x.idroomota') . ' = ' . $dbo->qn('d.idroomota') . ' AND ' . $dbo->qn('x.idchannel') . ' = ' . $dbo->qn('d.idchannel'))
                    ->where($dbo->qn('x.idchannel') . ' IN (' . implode(', ', array_map('intval', array_keys($eligible))) . ')')
                    // give higher preference to another room mapped under this same OTA, go to the other eligible OTA otherwise
                    ->order('IF(' . $dbo->qn('x.idchannel') . ' = ' . (int) $onboard_to['uniquekey'] . ', 1, 0)' . ' DESC')
            , 0, 1);

            $listing_contents = $dbo->loadAssoc();

            if ($listing_contents) {
                // make sure to set the proper "download from" channel identifier, as we may be downloading
                // the information from the same OTA where we are currently onboarding the new listing.
                $download_from = $listing_contents['idchannel'];
            }
        }

        if (!$listing_contents) {
            // unable to proceed
            VCMHttpDocument::getInstance($app)->close(500, 'The listing to onboard is currently not mapped on any eligible channel.');
        }

        /**
         * Always download them for Booking.com to obtain the JSON format from their most recent APIs.
         * 
         * @todo  Remove the below forced download of the listing details for Booking.com as soon as the VCM
         *        property details management interface will also support the new JSON format rather than XML.
         *        This way the information can be read directly from the database rather than fetched via API (not really urgent).
         */

        if (empty($listing_contents['setting']) || $listing_contents['idchannel'] == VikChannelManagerConfig::BOOKING) {
            // download the listing details
            $account_params = (array) json_decode($listing_contents['prop_params'], true);
            $account_params = array_merge($account_params, ['channel_id' => $download_from]);

            try {
                $listing_details = VCMOtaListing::getInstance(['server' => 'master'])->fetchRemoteDetails($listing_contents['idroomota'], $account_params, $save = true);
            } catch (Exception $e) {
                // propagate the error
                VCMHttpDocument::getInstance($app)->close($e->getCode(), sprintf('Error while retrieving the remote listing details: %s', $e->getMessage()));
            }
        } else {
            // decode current listing details fetched from db
            $listing_details = json_decode($listing_contents['setting']);
        }

        // get the mapped accounts where the listing should be onboarded
        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select([
                    $dbo->qn('prop_name'),
                    $dbo->qn('prop_params'),
                ])
                ->from($dbo->qn('#__vikchannelmanager_roomsxref'))
                ->where($dbo->qn('idchannel') . ' = ' . (int) $onboard_to['uniquekey'])
                ->group($dbo->qn('prop_name'))
                ->group($dbo->qn('prop_params'))
                ->order($dbo->qn('prop_name') . ' ASC')
        );
        $active_accounts = $dbo->loadAssocList();

        // access the current onboarding progress data, if any
        $onboarding_progress = new stdClass;
        try {
            $onboarding_progress = (new VCMOtaOnboardingStorageConfig)->load($room_id, $eligible[$channel_id])->getProgressData();
        } catch (Exception $e) {
            // do nothing
        }

        // fetch the form to onboard the new listing on the OTA
        $layout_data = [
            'room_id'             => $room_id,
            'vbo_listing'         => $vbo_listing,
            'listing_details'     => $listing_details,
            'from_channel'        => $download_from,
            'to_channel'          => $onboard_to,
            'active_accounts'     => $active_accounts,
            'create_new_prop'     => ($onboard_to['uniquekey'] == VikChannelManagerConfig::BOOKING ? 1 : 0),
            'onboarding_progress' => $onboarding_progress,
            'caller'              => 'vikbooking',
        ];

        $form_html = JLayoutHelper::render('onboarding.listing', $layout_data);

        // send the response to output
        VCMHttpDocument::getInstance($app)->json([
            'html' => $form_html,
        ]);
    }

    /**
     * Task used via AJAX to create a new listing on the OTA for onboarding.
     *
     * @return  void
     */
    public function create()
    {
        $app = JFactory::getApplication();

        if (!JSession::checkToken()) {
            // missing CSRF-proof token
            VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
        }

        // unlimited execution time
        @set_time_limit(0);
        // ignore user termination of script execution
        @ignore_user_abort(true);

        $eligible = [
            VikChannelManagerConfig::BOOKING => VCMOtaOnboardingProcessorBookingcom::class,
            VikChannelManagerConfig::AIRBNBAPI => VCMOtaOnboardingProcessorAirbnbapi::class,
        ];

        $channel_id = $app->input->getInt('channel_id', 0);
        $room_id = $app->input->getInt('room_id', 0);
        $data = $app->input->get('onboarding', [], 'array');

        $channel = VikChannelManager::getChannel($channel_id);

        if (!$channel) {
            // invalid channel
            VCMHttpDocument::getInstance($app)->close(400, 'Could not find the channel for onboarding the new listing.');
        }

        $onboardingProcessor = $eligible[$channel['uniquekey']] ?? null;

        if (!$onboardingProcessor) {
            // invalid processor
            VCMHttpDocument::getInstance($app)->close(400, 'The selected channel does not support the onboarding procedure.');
        }

        // collect the VBO listing details
        $room = VikBooking::getRoomInfo($room_id);

        if (!$room) {
            VCMHttpDocument::getInstance($app)->close(404, 'Could not find the VikBooking room.');
        }

        // set up onboarding mediator
        $onboardingMediator = new VCMOtaOnboardingMediator(
            $onboardingProcessor,
            new VCMOtaOnboardingStorageConfig
        );

        try {
            // start or resume the onboarding process
            $onboardingMediator->process((object) $room, (object) $data);
        } catch (Throwable $error) {
            // an error has been faced - construct the JSON erroneous response

            // attempt to access the onboarding processor and related progress data
            $onboardingProcessor = $onboardingMediator->getProcessor();
            $onboardingProgress = $onboardingProcessor ? $onboardingProcessor->getProgressData() : (new stdClass);

            // build the JSON erroneous response that contains the processor progress data, if any
            $response = array_merge([
                'error' => $error->getMessage(),
            ], get_object_vars($onboardingProgress));

            // terminate the execution with an error
            VCMHttpDocument::getInstance($app)->close($error->getCode() ?: 500, json_encode($response));
        }

        // send the response to output
        VCMHttpDocument::getInstance($app)->json([
            'status' => 1,
        ]);
    }
}
