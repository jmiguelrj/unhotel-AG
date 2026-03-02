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
 * Basic Implementation of VCMLogAwareInterface.
 * 
 * @since 1.9.16
 */
trait VCMLogAwareTrait
{
    /**
     * The logger instance.
     *
     * @var VCMLogInterface|null
     */
    protected $logger;

    /**
     * @see VCMLogAwareInterface::setLogger()
     */
    public function setLogger(VCMLogInterface $logger)
    {
        $this->logger = $logger;
    }
}
