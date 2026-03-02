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

use VikWP\VikUpdater\WordPress\API\UpdateAware;

/**
 * Helper class used to support the update functionalities for plugins that are
 * not hosted on wordpress.org.
 * 
 * @since 2.0
 */
class PluginAPI extends UpdateAware
{
    /**
     * @inheritDoc
     */
    public function getInfo()
    {
        // fetch the product manifest
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

        // decode body from JSON format
        $response = json_decode($body);

        if (!$response)
        {
            // we received an invalid JSON string
            throw new \UnexpectedValueException('The received response is not a valid JSON', 500);
        }
        
        // set up plugin information
        $info = new \stdClass;

        // define the name of the plugin (translatable)
        $info->name = $response->name ?? ($this->options['name'] ?? '');

        // define the slug of the plugin
        $info->slug = $response->slug ?? ($this->options['slug'] ?? '');

        // define the plugin identifier
        $info->plugin = $response->plugin ?? ($this->options['plugin'] ?? '');

        // define the plugin author
        $info->author = $response->author ?? $this->options['author'];

        // define the URL of the plugin author
        $info->author_profile = $response->author_profile ?? $this->options['author_profile'];

        // get the latest version from the received response
        $info->version = $response->version ?? $this->options['version'];

        // define the tested-up-to version of WordPress
        $info->tested = $response->tested ?? ($this->options['tested'] ?? '');

        // define the minimum required version of WordPress
        $info->requires = $response->requires ?? ($this->options['requires'] ?? '');

        // define the minimum required version of PHP
        $info->requires_php = $response->requires_php ?? ($this->options['requires_php'] ?? '');

        // define the download link
        $info->download_link = $this->getDownloadURL($response);
        // same as download link
        $info->trunk = $info->download_link;

        // define the last update
        if (!empty($response->last_updated))
        {
            $info->last_updated = $response->last_updated;
        }
        else if (!empty($response->changelog[0]->date))
        {
            // take the latest date from the changelog list
            $info->last_updated = max(array_column($response->changelog, 'date'));
        }

        // define the contributors
        $info->contributors = (array) ($response->contributors ?? $this->options['contributors']);

        // set up the tabs to be displayed within the product details modal
        $info->sections = [];

        // get plugin description
        $description = $response->description ?? ($this->options['description'] ?? '');

        if ($description)
        {
            // add "Description" tab
            $info->sections['description'] = $description;
        }

        // get plugin installation
        $installation = $response->installation ?? ($this->options['installation'] ?? '');

        if ($installation)
        {
            // add "Installation" tab
            $info->sections['installation'] = $installation;
        }

        // get plugin FAQ
        $faq = $response->faq ?? ($this->options['faq'] ?? '');

        if ($faq)
        {
            // add "FAQ" tab
            $info->sections['faq'] = $faq;
        }

        // get plugin other notes
        $notes = $response->notes ?? ($this->options['notes'] ?? '');

        if ($notes)
        {
            // add "Other Notes" tab
            $info->sections['other_notes'] = $notes;
        }

        // get plugin changelog
        $changelog = $response->changelog ?? ($this->options['changelog'] ?? []);

        if ($changelog)
        {
            // in case the changelog is an array, format it accordingly
            if (is_array($changelog))
            {
                $changelog = $this->formatChangelog($changelog);
            }

            // add "Changelog" tab
            $info->sections['changelog'] = $changelog;
        }

        // get banners
        $banners = (array) ($response->banners ?? ($this->options['banners'] ?? []));

        // make sure both the low and high resolutions have been provided
        if (!empty($banners['high']) && !empty($banners['low']))
        {
            // add banners to plugin view
            $info->banners = $banners;
        }

        // get screenshots
        $screenshots = (array) ($response->screenshots ?? ($this->options['screenshots'] ?? []));

        if ($screenshots)
        {
            $images = [];

            foreach ($screenshots as $screenshot)
            {
                if (is_string($screenshot))
                {
                    // specify URL and caption
                    $screenshot = [
                        'src'     => $screenshot,
                        'caption' => '',
                    ];
                }
                else
                {
                    // always treat as array
                    $screenshot = (array) $screenshot;
                }

                if (empty($screenshot['src']))
                {
                    // skip in case the source is missing
                    continue;
                }

                // create image
                $image = '<a href="' . $screenshot['src'] . '" target="_blank">'
                    . '<img src="' . $screenshot['src'] . '" alt="' . htmlspecialchars($screenshot['alt'] ?? ($screenshot['caption'] ?? '')) . '">'
                    . '</a>';

                if (!empty($screenshot['caption']))
                {
                    // add caption is provided
                    $image .= '<p>' . $screenshot['caption'] . '</p>';
                }

                $images[] = '<li>' . $image . '</li>';
            }

            // add screenshots to plugin view
            $info->sections['screenshots'] = '<ol>' . implode("\n", $images) . '</ol>';
        }

        /**
         * In case we received a plain author and the author URL has been provided,
         * make the author name linkable.
         * 
         * @since 2.0.1
         */
        if ($info->author_profile && strip_tags($info->author) === $info->author)
        {
            $info->author = '<a href="' . $info->author_profile . '">' . $info->author . '</a>';
        }

        /**
         * Action used to manipulate the plugin info before displaying them.
         * 
         * @param  object  $info      The object holding the plugin details.
         * @param  object  $manifest  The plugin manifest.
         * @param  array   $options   A configuration array.
         * 
         * @since  2.0
         */
        do_action('vikupdater_before_display_plugin_info', $info, $response, $this->options);
        
        return $info;
    }

    /**
     * @inheritDoc
     */
    public function checkUpdate()
    {
        // get only the latest version of the plugin
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
     * @inheritDoc
     */
    protected function getUpdateData($response)
    {
        $update = new \stdClass;
        $update->slug        = $response->slug ?? ($this->options['slug'] ?? '');
        $update->plugin      = $response->plugin ?? ($this->options['plugin'] ?? '');
        $update->new_version = $response->version;
        $update->package     = $this->getDownloadURL($response);

        // get WP tested-up-to information
        $tested = $response->tested ?? ($this->options['tested'] ?? '');

        if ($tested)
        {
            $update->tested = $tested;
        }

        // get minimum WP required version
        $requires = $response->requires ?? ($this->options['requires'] ?? '');

        if ($requires)
        {
            $update->requires = $requires;
        }

        // get minimum PHP required version
        $requires_php = $response->requires_php ?? ($this->options['requires_php'] ?? '');

        if ($requires_php)
        {
            $update->requires_php = $requires_php;
        }

        /**
         * Filter used to set up the update data that will be used by WordPress.
         * In example, it is possible to use the code below to support the plugin icons.
         * 
         * $update->icons = [
         *     '2x' => 'https://domain.com/images/logo-2x.png',
         *     '1x' => 'https://domain.com/images/logo-1x.png',
         * ];
         * 
         * @param  object  $update    The WordPress update details.
         * @param  object  $manifest  The plugin manifest.
         * @param  array   $options   A configuration array.
         * 
         * @since  2.0
         */
        return apply_filters('vikupdater_prepare_update_plugin_data', $update, $response, $this->options);
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
         * package of a plugin.
         * 
         * @param  array   $args     The extra query.
         * @param  array   $options  A configuration array.
         * 
         * @since  2.0
         */
        $args = apply_filters('vikupdater_check_plugin_extra_query', [], $this->options);

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
     * @param   object  $manifest  The plugin manifest.
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
            // fetch plugin identifier
            $plugin = $manifest->plugin ?? ($this->options['plugin'] ?? '');

            // get license details for the current plugin, if any
            $license = $this->keywallet->find($plugin);

            if ($license)
            {
                // register license within the query string
                $args['license'] = $license->license;
            }
        }

        /**
         * Filter used to apply an extra query to the URL needed to download the latest
         * package of a product.
         * 
         * @param  array   $args      The extra query.
         * @param  object  $manifest  The plugin manifest.
         * @param  array   $options   A configuration array.
         * 
         * @since  2.0
         */
        $args = apply_filters('vikupdater_download_plugin_extra_query', $args, $manifest, $this->options);

        if ($args)
        {
            // add arguments to URL
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($args);
        }

        return $url;
    }

    /**
     * Converts the changelog in a more readable format.
     * 
     * @param   array   $changelog  An array containing all the released versions.
     * 
     * @return  string  The changelog in HTML format.
     */
    protected function formatChangelog(array $changelog)
    {   
        $html = '';
        
        foreach ($changelog as $version)
        {
            if (!empty($version->version))
            {
                $html .= '<h4>' . $version->version . '</h4>';
            }

            if (!empty($version->date))
            {
                $date = date_i18n('d F Y', strtotime($version->date));
                $html .= '<p><em>' . sprintf(__('Release date - %s', 'vikupdater'), $date) . '</em></p>';
            }

            // parse the sections of this version changelog
            foreach ($version->sections ?? [] as $section)
            {
                if (!empty($section->title) && $section->title !== '--')
                {
                    $html .= '<h4>' . $section->title . '</h4>';
                }

                $features = [];

                // parse the children of this section
                foreach ($section->children ?? [] as $feature)
                {
                    $li = '';

                    if (!empty($feature->title))
                    {
                        $li .= '<strong>' . $feature->title . '</strong>';
                    }

                    if (!empty($feature->description))
                    {
                        $li .= ($li ? ' - ' : '') . '<span>' . $feature->description . '</span>';
                    }

                    if (is_string($feature))
                    {
                        $li .= $feature;
                    }

                    $features[] = '<li>' . $li . '</li>';
                }

                if ($features)
                {
                    $html .= '<ul>' . implode("\n", $features) . '</ul>';
                }
            }
        }

        return $html;
    }
}
