<?php
/** 
 * @package     VikUpdater
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// include defines
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'defines.php';

// make sure the installed PHP version is compatible with the minimum required one
if (version_compare(PHP_VERSION, VIKUPDATER_MINIMUM_PHP, '<'))
{
    throw new RuntimeException(
        sprintf(
            'The currently installed version of PHP (%s) is not compatible with the minimum required one by VikUpdater! You need to have at least the %s version installed. You should contact your hosting provider and ask them to upgrade your PHP version.',
            PHP_VERSION,
            VIKUPDATER_MINIMUM_PHP
        ),
        505
    );
}

/**
 * Method to autoload classes that are namespaced to the PSR-4 standard.
 *
 * @param   string  $class  The fully qualified class name to autoload.
 *
 * @return  bool    True on success, false otherwise.
 */
spl_autoload_register(function($class)
{
    static $namespaces = [];
    
    if (!$namespaces)
    {
        /**
         * Define here all the supported namespaces as "namespace" - "path" pairs.
         * 
         * @var array
         */
        $namespaces['VikWP\\VikUpdater'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libraries';
    }

    $class = $class && $class[0] === '\\' ? substr($class, 1) : $class;

    // find the location of the last NS separator
    $pos = strrpos($class, '\\');

    // If one is found, we're dealing with a NS'd class.
    if ($pos !== false)
    {
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
        $className = substr($class, $pos + 1);
    }
    // if not, no need to parse path
    else
    {
        $classPath = null;
        $className = $class;
    }

    $classPath .= $className . '.php';

    // loop through registered namespaces until we find a match
    foreach ($namespaces as $ns => $path)
    {
        if (strpos($class, "{$ns}\\") === 0)
        {
            $nsPath = trim(str_replace('\\', DIRECTORY_SEPARATOR, $ns), DIRECTORY_SEPARATOR);

            $classFilePath = realpath($path . DIRECTORY_SEPARATOR . substr_replace($classPath, '', 0, strlen($nsPath) + 1));

            // we do not allow files outside the namespace root to be loaded
            if (strpos($classFilePath, realpath($path)) !== 0)
            {
                continue;
            }

            // we check for class_exists to handle case-sensitive file systems
            if (is_file($classFilePath) && !class_exists($class, false))
            {
                return (bool) include_once $classFilePath;
            }
        }
    }

    return false;
});
