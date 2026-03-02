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
 * This logger acts as a decorator able to filter (ignore) the logs that
 * do not match the one we want to observe.
 * 
 * @since 1.9.16
 */
class VCMLogDriverObservable extends VCMLogAbstract
{
    /** @var VCMLogInterface */
    protected $logger;

    /** @var string[] */
    protected $levels;

    /**
     * Class constructor.
     * 
     * @param  VCMLogInterface  $logger  The aggregated logger.
     * @param  string[]         $levels  A list of levels to observe.
     */
    public function __construct(VCMLogInterface $logger, array $levels)
    {
        $this->logger = $logger;
        $this->levels = $levels;
    }

    /**
     * @inheritDoc
     */
    public function log(string $level, string $message, array $context = [])
    {
        if ($this->levels && !in_array($level, $this->levels)) {
            // level not observed, ignore message
            return;
        }

        // invoke the aggregated logger to register the message
        $this->logger->log($level, $message, $context);
    }
}
