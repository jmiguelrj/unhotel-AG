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
 * Notifications helper class.
 * 
 * @since 	1.8.24
 */
final class VCMNotificationsHelper
{
    /**
     * Requests to the E4jConnect central servers to download and transmit new notifications.
     * 
     * @return  bool
     */
    public function downloadNotifications()
    {
        $apikey = VikChannelManager::getApiKey();

        if (!$apikey) {
            return false;
        }

        $requested = 0;

        foreach ($this->getEligibleChannels() as $ch_name => $ch_key) {
            // check if this channel is available
            $channel = VikChannelManager::getChannel($ch_key);

            if (!$channel) {
                continue;
            }

            // build channel endpoint
            $endpoint = 'https://e4jconnect.com/channelmanager/v2/' . $ch_name . '/notifications?ping=1';

            // start the transporter WITHOUT slaves support on REST /v2 endpoint
            $transporter = new E4jConnectRequest($endpoint);
            $transporter->setBearerAuth($apikey, 'application/x-www-form-urlencoded');

            try {
                // perform a GET request
                $transporter->fetch('GET');

                if ($transporter->successResponse()) {
                    $requested++;
                }
            } catch (Exception $e) {
                // do not raise errors
                continue;
            }
        }

        return (bool) $requested;
    }

    /**
     * Returns a list of the eligible channels for which notifications can be requested.
     * 
     * @return  array
     */
    private function getEligibleChannels()
    {
        return [
            'airbnb' => VikChannelManagerConfig::AIRBNBAPI,
        ];
    }
}
