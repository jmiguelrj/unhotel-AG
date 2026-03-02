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

/**
 * Interface used to support the update functionalities for items that are
 * not hosted on wordpress.org.
 * 
 * @since 2.0
 */
interface UpdateInterface
{
    /**
     * Returns the information for the provided item.
     * 
     * @return  object|array
     * 
     * @throws  \Exception
     */
    public function getInfo();

    /**
     * Checks whethere there is an available update for this item.
     * 
     * @return  object|array|null  The object containing the update info. Null if up-to-date.
     */
    public function checkUpdate();
}
