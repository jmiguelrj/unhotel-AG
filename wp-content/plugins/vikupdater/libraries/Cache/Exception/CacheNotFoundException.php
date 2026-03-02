<?php
/** 
 * @package     VikUpdater
 * @subpackage  cache
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\Cache\Exception;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\Cache\CacheException;

/**
 * Throws an error in case the pool does not own a cached resource.
 * 
 * @since 2.0
 */
class CacheNotFoundException extends \RuntimeException implements CacheException
{

}
