<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\API\Resources;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\Core\Keywallet;
use VikWP\VikUpdater\WordPress\API\UpdateAware;

/**
 * Helper class used to support the update functionalities for themes that are
 * not hosted on wordpress.org.
 * 
 * @since 2.0
 */
class ThemeAPI extends UpdateAware
{
    /**
     * @inheritDoc
     */
    public function __construct(array $options, ?Keywallet $keywallet = null)
    {
        parent::__construct($options, $keywallet);

        // check whether the system provided an info URL
        if (!isset($this->options['infourl']))
        {
            // Info URL missing, VikWP theme assumed.
            // Make sure the configuration provided at least a SKU to identify the theme.
            if (!empty($this->options['sku']))
            {
                $this->options['infourl'] = 'https://vikwp.com/api/?view=changelog&tmpl=component&sku=' . $this->options['sku'];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getInfo()
    {
        // The theme information api does not seem to be used by WordPress.
        // We leave this method unimplemented for the moment.
        throw new \RuntimeException('Theme information API not implemented', 501);
    }

    /**
     * @inheritDoc
     */
    protected function getUpdateData($response)
    {
        $update = [
            'theme'       => $response->theme ?? ($this->options['theme'] ?? ''),
            'new_version' => $response->version,
            'url'         => $response->url ?? ($this->options['infourl'] ?? ''),
            'package'     => $this->getDownloadURL($response),
        ];

        // get WP tested-up-to information
        $tested = $response->tested ?? ($this->options['tested'] ?? '');

        if ($tested)
        {
            $update['tested'] = $tested;
        }

        // get minimum WP required version
        $requires = $response->requires ?? ($this->options['requires'] ?? '');

        if ($requires)
        {
            $update['requires'] = $requires;
        }

        // get minimum PHP required version
        $requires_php = $response->requires_php ?? ($this->options['requires_php'] ?? '');

        if ($requires_php)
        {
            $update['requires_php'] = $requires_php;
        }

        /**
         * Filter used to set up the theme update data that will be used by WordPress.
         * In example, it is possible to use the code below to support the theme icons.
         * 
         * $update->icons = [
         *     '2x' => 'https://domain.com/images/logo-2x.png',
         *     '1x' => 'https://domain.com/images/logo-1x.png',
         * ];
         * 
         * @param  array   $update    The WordPress update details.
         * @param  object  $manifest  The theme manifest.
         * @param  array   $options   A configuration array.
         * 
         * @since  2.0
         */
        return apply_filters('vikupdater_prepare_update_theme_data', $update, $response, $this->options);
    }

    /**
     * @inheritDoc
     */
    protected function getCheckURL()
    {
        // fetch default check URL
        $url = $this->options['url'];

        /**
         * Filter used to apply an extra query to the URL needed to check the latest
         * package of a theme.
         * 
         * @param  array   $args     The extra query.
         * @param  array   $options  A configuration array.
         * 
         * @since  2.0
         */
        $args = apply_filters('vikupdater_check_theme_extra_query', [], $this->options);

        if ($args)
        {
            // add arguments to URL
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($args);
        }

        return $url;
    }

    /**
     * Helper method used to return the full download URL.
     * 
     * @param   object  $manifest  The theme manifest.
     * 
     * @return  string  The complete download URL.
     */
    protected function getDownloadURL($manifest)
    {
        $args = [];

        // fetch default download URL
        $url = $manifest->download ?? ($this->options['download'] ?? '');

        if ($this->keywallet)
        {
            // fetch theme identifier
            $theme = $manifest->theme ?? ($this->options['theme'] ?? '');

            // get license details for the current theme, if any
            $license = $this->keywallet->find($theme);

            if ($license)
            {
                // register license within the query string
                $args['license'] = $license->license;
            }
        }

        /**
         * Filter used to apply an extra query to the URL needed to download the latest
         * package of a theme.
         * 
         * @param  array   $args      The extra query.
         * @param  object  $manifest  The theme manifest.
         * @param  array   $options   A configuration array.
         * 
         * @since  2.0
         */
        $args = apply_filters('vikupdater_download_theme_extra_query', $args, $manifest, $this->options);

        if ($args)
        {
            // add arguments to URL
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($args);
        }

        return $url;
    }
}
