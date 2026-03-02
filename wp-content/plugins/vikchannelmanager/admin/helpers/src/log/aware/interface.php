<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Describes a logger-aware instance.
 * 
 * @since 1.9.16
 */
interface VCMLogAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param   VCMLogInterface  $logger
     *
     * @return  void
     */
    public function setLogger(VCMLogInterface $logger);
}
