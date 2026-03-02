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

use VikWP\VikUpdater\WordPress\API\CacheableUpdate;

/**
 * Decorator used to support a caching system for the requests
 * performed by a theme update interface.
 * 
 * @since 2.0
 */
class CacheableThemeAPI extends CacheableUpdate
{
    /**
     * Class constructor.
     * 
     * @param  UpdateInterface  $resource  The resource used to perform the requests.
     * @param  array            $theme   c The details of the observed theme.
     */
    public function __construct(ThemeAPI $resource, array $theme)
    {
        parent::__construct($resource, $theme['theme']);
    }
}
