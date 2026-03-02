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

use VikWP\VikUpdater\Psr\Container\NotFoundExceptionInterface;

/**
 * No container set.
 * 
 * @since 2.0
 */
class ContainerNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{

}
