<?php
/*
Plugin Name:  VikUpdater
Plugin URI:   https://vikwp.com
Description:  Plugin used to update commercial plugins that are not part of the official wordpress.org repository.
Version:      2.0.5
Author:       E4J s.r.l.
Author URI:   https://vikwp.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  vikupdater
Domain Path:  /languages
*/

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

try
{
    // autoload dependencies
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'autoload.php';
}
catch (RuntimeException $error)
{
    // something went wrong while setting up the plugin dependencies
    add_action('admin_notices', function() use ($error)
    {
        ?>
        <div class="notice is-dismissible notice-warning">
            <p><?php echo $error->getMessage(); ?></p>
        </div>
        <?php
    });

    // return to avoid breaking the website
    return;
}

/**
 * Registers the callbacks that will perform the activation, the deactivation and the
 * uninstallation of VikUpdater.
 * 
 * @see VikWP\VikUpdater\WordPress\System\Installer
 */
register_activation_hook(__FILE__, ['VikWP\\VikUpdater\\WordPress\\System\\Installer', 'activate']);
register_deactivation_hook(__FILE__, ['VikWP\\VikUpdater\\WordPress\\System\\Installer', 'deactivate']);
register_uninstall_hook(__FILE__, ['VikWP\\VikUpdater\\WordPress\\System\\Installer', 'uninstall']);

/**
 * Loads the plugin textdomain to support the i18n.
 */
add_action('init', function()
{
    load_plugin_textdomain('vikupdater', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

/**
 * Attempts the re-activation in case something went wrong during the installation of the plugin.
 * Also tries to check whether an update process should be launched after downloading a newer
 * version of VikUpdater.
 * 
 * @see VikWP\VikUpdater\WordPress\System\Installer
 */
add_action('init', ['VikWP\\VikUpdater\\WordPress\\System\\Installer', 'onInit']);
add_action('plugins_loaded', ['VikWP\\VikUpdater\\WordPress\\System\\Installer', 'update']);

/**
 * Introduces the "VikUpdater" menu item under the "Tools" section of WordPress.
 * 
 * @see VikWP\VikUpdater\WordPress\System\Builder
 */
add_action('admin_menu', ['VikWP\\VikUpdater\\WordPress\\System\\Builder', 'setupAdminMenu']);

/**
 * Hook used to display the notices (messages) to the administrators.
 * In case the process stops before executing this action (e.g. because of a redirect),
 * any pending messages are temporarily saved and displayed at the next page loading.
 * 
 * @see VikWP\VikUpdater\WordPress\System\MessagesQueue
 */
add_action('admin_notices', function()
{
    VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.messages')->display();
});

/**
 * Looks in the request if we need to execute a task or display a page of VikUpdater.
 * 
 * @see VikWP\VikUpdater\WordPress\System\Controller
 */
add_action('init', function()
{
    // process VikUpdater only if it has been requested via GET or POST
    if (($_REQUEST['page'] ?? null) === 'vikupdater' || ($_REQUEST['action'] ?? null) === 'vikupdater')
    {
        VikWP\VikUpdater\WordPress\System\Controller::process();
    }
});

/**
 * Filters the response for the current WordPress.org Plugin Installation API request.
 *
 * Returning a non-false value will effectively short-circuit the WordPress.org API request.
 *
 * If `$action` is 'query_plugins' or 'plugin_information', an object MUST be passed.
 * If `$action` is 'hot_tags' or 'hot_categories', an array should be passed.
 * 
 * -----------------------------------------------------------------------------------------
 * 
 * We use this filter to perform an API request to the manifest URL provided by the
 * plugins subscribed to VikUpdater. The returned information will be passwed to WordPress
 * according to their requirements, which will be used accordingly to display a product page
 * as it was part of the official WordPress repository.
 * 
 * The API requests are temporarily cached for 5 minutes.
 *
 * @param  false|object|array  $result  The result object or array. Default false.
 * @param  string              $action  The type of information being requested from the Plugin Installation API.
 * @param  object              $args    Plugin API arguments.
 */
add_filter('plugins_api', function($res, $action, $args)
{
    // do nothing if this is not about getting plugin information
    if ($action !== 'plugin_information')
    {
        return $res;
    }

    // take all the subscribed elements and cache them statically to prevent duplicate executions
    $subscriptions = VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.updates.observer')->getPlugins();

    // filter all the subscriptions to make sure that the current one exists
    $subscriptions = array_filter($subscriptions, function($plugin) use ($args)
    {
        return $args->slug == $plugin['slug'];
    });

    // do nothing if it is not our plugin
    if (!$subscriptions)
    {
        return $res;
    }

    // take only the first available subscription
    $plugin = reset($subscriptions);

    // take advantage of the cache to save multiple requests
    return (new VikWP\VikUpdater\WordPress\API\Resources\CacheablePluginAPI(
        /** @var VikWP\VikUpdater\WordPress\API\UpdateInterface */
        VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.pluginapi', $plugin),
        $plugin
    ))->getInfo();
}, 20, 3);

/**
 * Filters the value of an existing site transient.
 *
 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
 * 
 * Hook used to look for a newer version of the plugin.
 * 
 * -----------------------------------------------------------------------------------------------------------------------
 * 
 * Every time WordPress checks whether there's a newer version of the official plugins, it calls the following code:
 * `get_site_transient( 'update_plugins' );`
 * This transient should have registered all the plugins that have a newer version available, checked by WordPress itself,
 * which will have already performed an API request to the theme repositories.
 * 
 * Here we make our API requests to the private plugins subscribed to VikUpdater. In case the manifest finds a newer
 * version, the transient object will be updated accordingly.
 * 
 * The API requests are temporarily cached for 12-24 hours (randomly).
 * Visiting the details of a plugin should empty this cache.
 *
 * @param  mixed   $value      Value of site transient.
 * @param  string  $transient  Transient name.
 */
add_filter('site_transient_update_plugins', function($transient)
{
    if (!class_exists('VikWP\\VikUpdater\\Core\\Factory') || !$transient)
    {
        // the VikUpdater factory does not exist, the user is probably uninstalling the plugin
        return $transient;
    }

    // take all the subscribed elements and cache them statically to prevent duplicate executions
    $subscriptions = VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.updates.observer')->getPlugins();

    foreach ($subscriptions as $plugin)
    {
        try
        {
            // take advantage of the cache to save multiple requests
            $update = (new VikWP\VikUpdater\WordPress\API\Resources\CacheablePluginAPI(
                /** @var VikWP\VikUpdater\WordPress\API\UpdateInterface */
                VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.pluginapi', $plugin),
                $plugin
            ))->checkUpdate();
            
            // check whether an update has been found and the new version is higher than the current one
            if ($update && version_compare($update->new_version, $plugin['version'] ?? '0.0.1', '>'))
            {
                // an update was found, inform WP
                $transient->response[$update->plugin] = $update;
            }
        }
        catch (Exception $e)
        {
            // display the exception through a WordPress notice, then go ahead
            VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.messages')->error($e->getMessage());
        }
    }
 
    return $transient;
}, PHP_INT_MAX);

/**
 * Filters the value of an existing site transient.
 *
 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
 * 
 * Hook used to look for a newer version of the themes.
 * 
 * ----------------------------------------------------------------------------------------------------------------------
 * 
 * Every time WordPress checks whether there's a newer version of the official themes, it calls the following code:
 * `get_site_transient( 'update_themes' );`
 * This transient should have registered all the themes that have a newer version available, checked by WordPress itself,
 * which will have already performed an API request to the theme repositories.
 * 
 * Here we make our API requests to the private themes subscribed to VikUpdater. In case the manifest finds a newer
 * version, the transient object will be updated accordingly.
 * 
 * The API requests are temporarily cached for 12-24 hours (randomly).
 * Contrarily to the plugins, there is no a "view details" theme page to empty the cache.
 *
 * @param  mixed   $value      Value of site transient.
 * @param  string  $transient  Transient name.
 */
add_filter('site_transient_update_themes', function($transient)
{
    if (!class_exists('VikWP\\VikUpdater\\Core\\Factory') || !$transient)
    {
        // the VikUpdater factory does not exist, the user is probably uninstalling the plugin
        return $transient;
    }

    // take all the subscribed elements and cache them statically to prevent duplicate executions
    $subscriptions = VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.updates.observer')->getThemes();

    foreach ($subscriptions as $theme)
    {
        try
        {
            // take advantage of the cache to save multiple requests
            $update = (new VikWP\VikUpdater\WordPress\API\Resources\CacheableThemeAPI(
                /** @var VikWP\VikUpdater\WordPress\API\UpdateInterface */
                VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.themeapi', $theme),
                $theme
            ))->checkUpdate();
            
            // check whether an update has been found and the new version is higher than the current one
            if ($update && version_compare($update['new_version'], $theme['version'] ?? '0.0.1', '>'))
            {
                // an update was found, inform WP
                $transient->response[$update['theme']] = $update;
            }
        }
        catch (Exception $e)
        {
            // display the exception through a WordPress notice, then go ahead
            VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.messages')->error($e->getMessage());
        }
    }
 
    return $transient;
}, PHP_INT_MAX);

/**
 * Filters the full array of plugins to list in the Plugins list table.
 * 
 * --------------------------------------------------------------------------------
 * 
 * We use this filter to force WordPress to display the "View Details" link even if
 * there isn't an update available.
 * 
 * Subscribed plugins must provide the "plugin" information, which is usually built
 * as `folder/plugin.php`.
 *
 * @since 3.0.0
 *
 * @see get_plugins()
 */
add_filter('all_plugins', function($plugins)
{
    if (!class_exists('VikWP\\VikUpdater\\Core\\Factory'))
    {
        // the VikUpdater factory does not exist, the user is probably uninstalling the plugin
        return $plugins;
    }

    // take all the subscribed elements and cache them statically to prevent duplicate executions
    $subscriptions = VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.updates.observer')->getPlugins();

    // iterate all the subscribed plugins
    foreach ($subscriptions as $plugin)
    {
        $id = $plugin['plugin'] ?? '';

        // make sure the plugin exists
        if (!isset($plugins[$id]))
        {
            continue;
        }

        // inject custom information within the WP default plugin data
        $plugins[$id] = wp_parse_args($plugins[$id], $plugin);
    }

    return $plugins;
});

/**
 * Filters used to set up the update data that will be used by WordPress.
 * 
 * ----------------------------------------------------------------------
 * 
 * We use this internal filter to make sure that the Dashboard > Updates
 * page properly shows the provided images as icon of VikUpdater.
 * 
 * @param  object  $update    The WordPress update details.
 * @param  object  $manifest  The plugin manifest.
 * @param  array   $options   A configuration array.
 * 
 * @since  2.0
 */
add_filter('vikupdater_prepare_update_plugin_data', function($update, $response, $options)
{
    if ($update->slug === 'vikupdater')
    {
        // register the plugin icons
        $update->icons = [
            '2x' => VIKUPDATER_URI . 'assets/images/logo-256x256.png',
            '1x' => VIKUPDATER_URI . 'assets/images/logo-128x128.png',
        ];
    }

    return $update;
}, 10, 3);

/**
 * Prints scripts or data before the default footer scripts.
 * 
 * ------------------------------------------------------------
 * 
 * Force the images under the plugin information popup to have
 * a maximum width equals to the 100% of the page. Without this
 * simple rule all the images larger than ~480 pixer would
 * exceed the viewport width.
 *
 * @since 1.2.0
 */
add_action('admin_footer', function()
{
    // make sure the images inside the plugin information view does not exceed the viewport
    echo '<style>#plugin-information img { max-width: 100%; }</style>';
});

/**
 * //////////////////////////////////
 * ///// BACKWARD COMPATIBILITY /////
 * //////////////////////////////////
 * 
 * The following filters are meant to support the update for those plugins and themes
 * that are not yet aware of the new requirements brought by VikUpdater 2.0.
 */

/**
 * Make sure that, after updating the old version of VikUpdater we are properly landing
 * into the new 2.0 interface.
 * 
 * Before the update the plugin URL was:
 * /options-general.php?page=vikupdater
 * Starting from the 2.0 version the URL has been changed into:
 * /tools.php?page=vikupdater
 * 
 * This means that default landing page will cause WordPress to raise an error because
 * VikUpdater cannot be found under the settings tab. For this reason we should manually
 * redirect the users to the correct location.
 * 
 * @since  2.0
 */
add_action('init', ['VikWP\\VikUpdater\\BC\\Manager', 'redirect']);

/**
 * Filter used to obtain a list of plugins that wishes to use
 * the features provided by VikUpdater.
 * 
 * ----------------------------------------------------------
 * 
 * Auto-subscribe the plugins with a registered license.
 * 
 * @param  array  $subscribers  A list of subscribers.
 * 
 * @since  2.0
 */
add_filter('vikupdater_subscribe_plugins', ['VikWP\\VikUpdater\\BC\\Manager', 'autowirePlugins']);

/**
 * Filter used to obtain a list of themes that wishes to use
 * the features provided by VikUpdater.
 * 
 * ---------------------------------------------------------
 * 
 * Auto-subscribe the themes with a registered license.
 * 
 * @param  array  $subscribers  A list of subscribers.
 * 
 * @since  2.0
 */
add_filter('vikupdater_subscribe_themes', ['VikWP\\VikUpdater\\BC\\Manager', 'autowireThemes']);
