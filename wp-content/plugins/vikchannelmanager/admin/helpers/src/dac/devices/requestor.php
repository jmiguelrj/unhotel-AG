<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * DAC devices requestor.
 * 
 * @since 1.9.14
 */
final class VCMDacDevicesRequestor
{
    /**
     * Adds a device to the current API account.
     * 
     * @param   VBODooraccessIntegrationDevice  $device     The device object to add.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function add(VBODooraccessIntegrationDevice $device)
    {
        // assert capabilities
        $this->assertCapabilities();

        $transporter = new E4jConnectRequest('https://hotels.e4jconnect.com/channelmanager/v2/dac/devices', true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        // set the device ID to add
        $transporter->setPostFields([
            'device' => $device->getID(),
            'notifyurl' => JUri::root(),
            'cms' => VCMPlatformDetection::isWordPress() ? 'wp' : 'j',
        ]);

        // add the device
        $transporter->fetch('POST', '');
    }

    /**
     * Deletes one or more devices from the current API account.
     * 
     * @param   array|VBODooraccessIntegrationDevice  $device     The device object(s) to delete.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    public function delete($device)
    {
        // assert capabilities
        $this->assertCapabilities();

        if (!is_array($device)) {
            $device = [$device];
        }

        $deviceIds = [];
        foreach ($device as $d) {
            $deviceIds[] = $d->getID();
        }

        $transporter = new E4jConnectRequest('https://hotels.e4jconnect.com/channelmanager/v2/dac/devices', true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');

        // set the device ID(s) to delete
        $transporter->setPostFields(['device' => $deviceIds]);

        // delete the device(s)
        $transporter->fetch('DELETE', '');
    }

    /**
     * Runs as a preflight for all DAC device requests.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    private function assertCapabilities()
    {
        if (!VikChannelManager::getChannel(VikChannelManagerConfig::DAC)) {
            throw new Exception('This function requires the "Door Access Control" service integration!', 402);
        }
    }
}
