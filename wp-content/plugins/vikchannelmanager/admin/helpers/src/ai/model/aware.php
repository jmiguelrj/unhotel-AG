<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// Restricted access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * AI model aware.
 * 
 * @since 1.9
 */
abstract class VCMAiModelAware
{
    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $host;

    /**
     * The default HTTP request timeout.
     * 
     * @var int
     */
    public $timeout = 60;

    /**
     * Class constructor.
     * 
     * @param  string  $apiKey  The e4jConnect API Key. If not provided, the default one will be used.
     * @param  string  $host    The server where the request should be made.
     */
    public function __construct(?string $apiKey = null, ?string $host = null)
    {
        $config = VCMFactory::getConfig();

        $this->apiKey = base64_encode($apiKey ?: $config->get('apikey'));

        /**
         * @todo  use "master." subdomain once the master will be divided from the shop
         */
        $this->host = $config->get('ai_server') ?: 'e4jconnect.com';
    }

    /**
     * Helper method used to construct the end-point of the request.
     * 
     * @param   string  $path  The relative end-point path, starting after v2/ai.
     * 
     * @return  string  The full URI.
     */
    protected function getEndPoint(string $path)
    {
        /**
         * @todo The value in configuration might be forced to e4jconnect.com.
         *       In this case, manually prepend "master." once this subdomain will be available.
         */

        return 'https://' . rtrim($this->host, '/') . '/channelmanager/v2/ai/' . ltrim($path);
    }

    /**
     * Prepares the HTTP headers for AI requests.
     * By default, the system will include the following headers:
     * - Authorization: Bearer ...
     * - X-VCM-VERSION: ...
     * 
     * @param   array  $headers  The extra headers to use.
     * 
     * @return  array  The HTTP headers.
     * 
     * @since   1.9.12
     */
    protected function getHeaders(array $headers = [])
    {
        // merge default headers to the provided ones
        return array_merge([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'X-VCM-VERSION' => VIKCHANNELMANAGER_SOFTWARE_VERSION,
        ], $headers);
    }
}
