<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\System;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helper class to setup the plugin.
 *
 * @since 2.0
 */
class Builder
{
    /**
     * Pushes the plugin pages into the WP admin menu.
     *
     * @return  void
     */
    public static function setupAdminMenu()
    {
        add_management_page(
            _x('VikUpdater', 'Page title', 'vikupdater'),                     // page title
            _x('VikUpdater', 'Menu title', 'vikupdater'),                     // menu title
            'manage_options',                                                 // capability
            'vikupdater',                                                     // slug
            ['\\VikWP\\VikUpdater\\WordPress\\System\\Controller', 'getHtml'] // callback
        );
    }
}
