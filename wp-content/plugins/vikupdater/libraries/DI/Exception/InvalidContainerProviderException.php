<?php
/** 
 * @package     VikUpdater
 * @subpackage  di (dependency-injection)
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\DI\Exception;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\Psr\Container\ContainerExceptionInterface;

/**
 * The specified provider is not a valid instance.
 * 
 * @since 2.0
 */
class InvalidContainerProviderException extends \RuntimeException implements ContainerExceptionInterface
{

}
