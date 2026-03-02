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
use VikWP\VikUpdater\DI\ContainerDecorator;
use VikWP\VikUpdater\DI\ServiceProviderInterface;

/**
 * Service provider for the MVC system.
 *
 * @since 2.0
 */
class MVCFactory implements ServiceProviderInterface
{
	/**
	 * @inheritDoc
	 */
	public function register(Container $container)
	{
		// register generic controller
		$container->set('vikupdater.controller', function($id = null) {
			return new \VikWP\VikUpdater\MVC\Controller($id);
		});

		// create decorator to quickly load all the supported resources
		$containerDecorator = new ContainerDecorator($container);

		// load all the supported controllers
		$containerDecorator->register(VIKUPDATER_BASE . '/libraries/MVC/Controllers', [
			'template'  => 'vikupdater.controller.{id}',
			'suffix'    => 'Controller',
			'namespace' => 'VikWP\\VikUpdater\\MVC\\Controllers',
		]);

		// load all the supported models
		$containerDecorator->register(VIKUPDATER_BASE . '/libraries/MVC/Models', [
			'template'  => 'vikupdater.model.{id}',
			'suffix'    => 'Model',
			'namespace' => 'VikWP\\VikUpdater\\MVC\\Models',
		]);

		// load all the supported views
		$containerDecorator->register(VIKUPDATER_BASE . '/libraries/MVC/Views', [
			'template'  => 'vikupdater.view.{id}',
			'suffix'    => 'View',
			'namespace' => 'VikWP\\VikUpdater\\MVC\\Views',
		]);
	}
}
