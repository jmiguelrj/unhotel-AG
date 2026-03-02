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
 * This is a simple Logger trait that classes unable to extend AbstractLogger
 * (because they extend another class, etc) can include.
 *
 * It simply delegates all log-level-specific methods to the `log` method to
 * reduce boilerplate code that a simple Logger that does the same thing with
 * messages regardless of the error level has to implement.
 * 
 * @since 1.9.16
 */
trait VCMLogTrait
{
    /**
     * @see VCMLogInterface::emergency()
     */
    public function emergency(string $message, array $context = [])
    {
        $this->log(VCMLogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @see VCMLogInterface::alert()
     */
    public function alert(string $message, array $context = [])
    {
        $this->log(VCMLogLevel::ALERT, $message, $context);
    }

    /**
     * @see VCMLogInterface::critical()
     */
    public function critical(string $message, array $context = [])
    {
        $this->log(VCMLogLevel::CRITICAL, $message, $context);
    }

    /**
     * @see VCMLogInterface::error()
     */
    public function error(string $message, array $context = [])
    {
        $this->log(VCMLogLevel::ERROR, $message, $context);
    }

    /**
     * @see VCMLogInterface::warning()
     */
    public function warning(string $message, array $context = [])
    {
        $this->log(VCMLogLevel::WARNING, $message, $context);
    }

    /**
     * @see VCMLogInterface::notice()
     */
    public function notice(string $message, array $context = [])
    {
        $this->log(VCMLogLevel::NOTICE, $message, $context);
    }

    /**
     * @see VCMLogInterface::info()
     */
    public function info(string $message, array $context = [])
    {
        $this->log(VCMLogLevel::INFO, $message, $context);
    }

    /**
     * @see VCMLogInterface::debug()
     */
    public function debug(string $message, array $context = [])
    {
        $this->log(VCMLogLevel::DEBUG, $message, $context);
    }

    /**
     * @see VCMLogInterface::log()
     */
    abstract public function log(string $level, string $message, array $context = []);

    /**
     * Interpolates context values into the message placeholders.
     * 
     * @param   string  $message  The message to log.
     * @param   array   $context  The placeholders lookup.
     * 
     * @return  string  The interpolated message.
     */
    final protected function interpolate(string $message, array $context = [])
    {
        // build a replacement array with braces around the context keys
        $replace = [];

        foreach ($context as $key => $val)
        {
            // check that the value can be converted into a string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString')))
            {
                $replace['{' . $key . '}'] = (string) $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
