<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Throw this exception in case the storage is unable to find or decode a
 * serialized onboarding processor.
 * 
 * @since 1.9.2
 */
class VCMOtaOnboardingExceptionStoragenotfound extends RuntimeException
{

}