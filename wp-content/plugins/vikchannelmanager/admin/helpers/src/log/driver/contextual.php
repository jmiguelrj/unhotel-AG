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
 * This logger acts as a decorator to support default contexts without having
 * to propagate them to the children classes.
 * 
 * @since 1.9.16
 */
class VCMLogDriverContextual extends VCMLogAbstract
{
    /** @var VCMLogInterface */
    protected $logger;

    /** @var array */
    protected $context;

    /**
     * Class constructor.
     * 
     * @param  VCMLogInterface  $logger   The aggregated logger.
     * @param  array            $context  The default context to propagate.
     */
    public function __construct(VCMLogInterface $logger, array $context)
    {
        $this->logger = $logger;
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function log(string $level, string $message, array $context = [])
    {
        // Merge the default context with the provided one.
        // The default context comes first to give higher priority to the
        // one provided by this method and allow its overwriting.
        $context = array_merge($this->context, $context);

        // invoke the aggregated logger to register the message
        $this->logger->log($level, $message, $context);
    }
}
