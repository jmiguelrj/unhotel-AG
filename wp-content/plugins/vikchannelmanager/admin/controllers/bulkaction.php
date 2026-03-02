<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2023 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerBulkaction extends JControllerAdmin
{
    /**
     * AJAX endpoint originally introduced for the Bulk Action - Rates Upload to preload
     * the assets for specific channels. Introduced for Vrbo API to generate an updated
     * copy of the XML files for lodging rate and cache them with the latest rates.
     * 
     * @return  void
     */
    public function rates_preload_channel_asset()
    {
        // allow support for both single and multiple values
        $asset_signs = VikRequest::getVar('assets', array(), 'request', 'array');
        $asset_sign  = VikRequest::getString('asset', '', 'request');

        if (empty($asset_signs) && empty($asset_sign)) {
            VBOHttpDocument::getInstance()->close(500, 'Missing assets to preload');
        }

        // make sure to always have an array of asset signs
        if (!empty($asset_signs)) {
            $preload_assets = $asset_signs;
        } else {
            $preload_assets = [$asset_sign];
        }

        // assets preloaded
        $assets_preloaded = 0;

        foreach ($preload_assets as $asset_sign) {
            list($channel_id, $room_id) = explode('-', $asset_sign);

            $channel = VikChannelManager::getChannel($channel_id);
            if (!$channel || $channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
                continue;
            }

            try {
                // attempt to generate an XML document with no output
                $xml_str = VCMVrboXml::getInstance($app = null, $cache_allowed = false)->processDocument('listing_rate', $channel, $room_id, $render = false);
                if ($xml_str) {
                    // increase counter
                    $assets_preloaded++;
                }
            } catch (Exception $e) {
                // exit with the error returned
                VBOHttpDocument::getInstance()->close(500, $e->getMessage());
            }
        }

        // return a JSON response result
        VBOHttpDocument::getInstance()->json([
            'status'  => $assets_preloaded,
        ]);
    }

    /**
     * AJAX endpoint to count the rooms mapped for a specific channel and account.
     * 
     * @return  void
     * 
     * @since   1.8.16
     */
    public function count_rooms_mapped()
    {
        $account = VikRequest::getString('account', '', 'request');
        $channel = VikRequest::getInt('channel', 0, 'request');

        $mapping_data = VikChannelManager::getChannelAccountsMapped($channel, $get_rooms = true);

        $account_rooms = $mapping_data && isset($mapping_data[$account]) && is_array($mapping_data[$account]) ? count($mapping_data[$account]) : 0;

        // return a JSON response result
        VBOHttpDocument::getInstance()->json([
            'count' => $account_rooms,
        ]);
    }

    /**
     * AJAX endpoint to unset a given relation from the Bulk Rates Cache.
     * 
     * @return  void
     * 
     * @since   1.9.16
     */
    public function unset_cache_relation()
    {
        $app = JFactory::getApplication();

        $roomId = $app->input->getUInt('room_id', 0);
        $rateId = $app->input->getUInt('rate_id', 0);
        $channelId = $app->input->getUInt('channel_id', 0);

        if (!$roomId || !$rateId || !$channelId) {
            VBOHttpDocument::getInstance()->close(400, 'Missing data for deleting a Bulk Rates Cache relation.');
        }

        $bulk_rates_cache = (array) VikChannelManager::getBulkRatesCache();

        if (!isset($bulk_rates_cache[$roomId][$rateId])) {
            VBOHttpDocument::getInstance()->close(404, 'Bulk Rates Cache relation not found for the given room and rate.');
        }

        if (!in_array($channelId, (array) ($bulk_rates_cache[$roomId][$rateId]['channels'] ?? []))) {
            VBOHttpDocument::getInstance()->close(404, 'Bulk Rates Cache relation not found for the given room, rate and channel.');
        }

        // we have made sure that the relation exists, now we delete it
        unset($bulk_rates_cache[$roomId][$rateId]['rplans'][$channelId]);
        unset($bulk_rates_cache[$roomId][$rateId]['cur_rplans'][$channelId]);
        unset($bulk_rates_cache[$roomId][$rateId]['rplanarimode'][$channelId]);
        unset($bulk_rates_cache[$roomId][$rateId]['rmod_channels'][$channelId]);

        $delIndex = array_search($channelId, $bulk_rates_cache[$roomId][$rateId]['channels']);
        if ($delIndex !== false) {
            unset($bulk_rates_cache[$roomId][$rateId]['channels'][$delIndex]);
        }

        if (!$bulk_rates_cache[$roomId][$rateId]['channels']) {
            // no more room-rate-channel relations, so delete the whole entry
            unset($bulk_rates_cache[$roomId][$rateId]);
        }

        if (!$bulk_rates_cache[$roomId]) {
            // no more room-rate relations, so delete the whole entry
            unset($bulk_rates_cache[$roomId]);
        }

        // update bulk rates cache internally
        VCMFactory::getConfig()->set('bulkratescache', $bulk_rates_cache);

        // return the updated bulk rates cache
        VBOHttpDocument::getInstance()->json($bulk_rates_cache);
    }

    /**
     * AJAX endpoint to trigger an update of rates by simulating a Bulk Action.
     * 
     * @return  void
     * 
     * @since   1.9.16
     */
    public function triggerRates()
    {
        $app = JFactory::getApplication();

        $from    = $app->input->getString('from', '');
        $to      = $app->input->getString('to', '');
        $room_id = $app->input->getUInt('room_id', 0);
        $debug   = $app->input->getBool('debug', false);
        $notifications = $app->input->getBool('notifications', false);

        try {
            // invoke bulk action processor
            $processor = (new VCMBulkactionProcessor([
                'update'        => 'rates',
                'from'          => $from,
                'to'            => $to,
                'forced_rooms'  => [$room_id],
                'notifications' => $notifications,
            ]))->setDebug((bool) $debug);

            // trigger auto-bulk action for distributing rates to OTAs
            $rates_result = $processor->distributeRates(true);

            if (!$rates_result) {
                // raise error
                throw new Exception('Could not distribute rates to channels.', 500);
            }
        } catch (Exception $e) {
            // terminate the process by sending the error to output
            VBOHttpDocument::getInstance($app)->close($e->getCode() ?: 500, $e->getMessage());
        }

        // send operation result object to output
        VBOHttpDocument::getInstance($app)->json($rates_result);
    }
}
