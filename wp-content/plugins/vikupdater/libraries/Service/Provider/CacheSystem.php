<?php
/** 
 * @package     VikUpdater
 * @subpackage  service
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\Service\Provider;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\DI\Container;
use VikWP\VikUpdater\DI\ServiceProviderInterface;

/**
 * Service provider for the global caching system.
 *
 * @since 2.0
 */
class CacheSystem implements ServiceProviderInterface
{
	/**
	 * @inheritDoc
	 */
	public function register(Container $container)
	{
		$container->set('vikupdater.cache.engine', function($options = []) {
			return new \VikWP\VikUpdater\WordPress\Cache\TransientCache($options);
		});
	}
}
