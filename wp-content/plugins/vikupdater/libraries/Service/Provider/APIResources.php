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

use VikWP\VikUpdater\Core\Keywallet;
use VikWP\VikUpdater\DI\Container;
use VikWP\VikUpdater\DI\ServiceProviderInterface;

/**
 * Service provider for the API resources.
 *
 * @since 2.0
 */
class APIResources implements ServiceProviderInterface
{
	/** @var Keywallet|null */
	protected $keywallet;

	/**
	 * Class constructor.
	 * 
	 * @param  Keywallet  $keywallet
	 */
	public function __construct(?Keywallet $keywallet = null)
	{
		$this->keywallet = $keywallet;
	}

	/**
	 * @inheritDoc
	 */
	public function register(Container $container)
	{
		$container->set('vikupdater.pluginapi', function($options) use ($container) {
			if ($this->keywallet === null) {
				// use the global keywallet instance
				$this->keywallet = $container->get('vikupdater.keywallet');
			}

			return new \VikWP\VikUpdater\WordPress\API\Resources\PluginAPI($options, $this->keywallet);
		});

		$container->set('vikupdater.themeapi', function($options) use ($container) {
			if ($this->keywallet === null) {
				// use the global keywallet instance
				$this->keywallet = $container->get('vikupdater.keywallet');
			}

			return new \VikWP\VikUpdater\WordPress\API\Resources\ThemeAPI($options, $this->keywallet);
		});
	}
}
