<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\API;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\Core\Keywallet;

/**
 * Implements the common methods used by the classes that aim to support the update
 * functionalities for items that are not hosted on wordpress.org.
 * 
 * @since 2.0
 */
abstract class UpdateAware implements UpdateInterface
{
    /**
     * A configuration registry.
     * 
     * @param  array
     */
    protected $options;

    /**
     * The licenses keywallet.
     * 
     * @param  Keywallet|null
     */
    protected $keywallet;

    /**
     * Class constructor.
     * 
     * @param  array      $options    A configuration array.
     * @param  Keywallet  $keywallet  An optional licenses keywallet.
     */
    public function __construct(array $options, ?Keywallet $keywallet = null)
    {
        $this->options = wp_parse_args(
            $options,
            [
                'version'        => '1.0',
                'author'         => 'E4J s.r.l.',
                'author_profile' => 'https://vikwp.com',
                'contributors'   => [
                    'e4jvikwp' => [
                        'profile'      => 'https://profiles.wordpress.org/e4jvikwp/',
                        'avatar'       => 'https://secure.gravatar.com/avatar/9bed9c553b8bc648b045b7444d57f086?s=96&d=monsterid&r=g',
                        'display_name' => 'e4jvikwp',
                    ],
                ],
            ]
        );

        // make sure the configuration provided the URL to check the product version
        if (!isset($this->options['url']))
        {
            // URL missing, VikWP product assumed.
            // Make sure the configuration provided at least a SKU to identify the product.
            if (empty($this->options['sku']))
            {
                // Missing SKU, cannot go ahead.
                throw new \InvalidArgumentException(
                    __('The API configuration must receive at least one information among <code>url</code> and <code>sku</code>.', 'vikupdater'),
                    400
                );
            }

            // Construct default URL.
            // Always pass "1.0" to version so that we can obtain the whole changelog.
            $this->options['url'] = 'https://vikwp.com/api/?task=products.version&sku=' . $this->options['sku'] . '&version=1.0&extended=1&lang=' . get_user_locale();

            if (empty($this->options['download']))
            {
                // Construct download URL.
                $this->options['download'] = 'https://vikwp.com/api/?task=products.download&post=1&sku=' . $this->options['sku'];
            }
        }

        $this->keywallet = $keywallet;
    }

    /**
     * @inheritDoc
     */
    public function checkUpdate()
    {
        // get only the latest version of the product
        $response = wp_remote_get( 
            $this->getCheckURL(),
            [
                'timeout' => 10,
            ]
        );

        // check if we have a response error
        if (is_wp_error($response))
        {
            throw new \UnexpectedValueException(
                (string) $response->get_error_message(),
                (int) $response->get_error_code()
            );
        }

        // get status code and body
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // make sure we had a successful response code
        if ($code !== 200)
        {
            throw new \RuntimeException($body ?: 'Unknown error', $code);
        }
        
        // decode version from JSON format
        $response = json_decode($body);

        if (!$response)
        {
            // we received an invalid JSON string
            throw new \UnexpectedValueException('The received response is not a valid JSON', 500);
        }
     
        // make sure the fetched version is higher than the currently installed one
        if (version_compare($this->options['version'], $response->version, '>='))
        {    
            // version up to date
            return null;
        }

        // update found, return information details
        return $this->getUpdateData($response);
    }

    /**
     * Prepares the update details that WordPress will use for internal purposes.
     * 
     * @param   object  $response  The manifest response.
     * 
     * @return  object|array  The update data.
     */
    abstract protected function getUpdateData($response);

    /**
     * Helper method used to return the full check URL.
     * 
     * @return  string  The complete check URL.
     */
    abstract protected function getCheckURL();
}
