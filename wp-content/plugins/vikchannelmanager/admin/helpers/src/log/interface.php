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
 * Describes a logger instance.
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data. The only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 * 
 * @since 1.9.16
 */
interface VCMLogInterface
{
    /**
     * Logs with an arbitrary level.
     *
     * @param   string  $level    The error level (@see LogLevel).
     * @param   string  $message  The message to log.
     * @param   array   $context  The placeholders lookup.
     *
     * @return  void
     */
    public function log(string $level, string $message, array $context = []);

    /**
     * System is unusable.
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  void
     */
    public function emergency(string $message, array $context = []);

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  void
     */
    public function alert(string $message, array $context = []);

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  void
     */
    public function critical(string $message, array $context = []);

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  void
     */
    public function error(string $message, array $context = []);

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  void
     */
    public function warning(string $message, array $context = []);

    /**
     * Normal but significant events.
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  void
     */
    public function notice(string $message, array $context = []);

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  void
     */
    public function info(string $message, array $context = []);

    /**
     * Detailed debug information.
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  void
     */
    public function debug(string $message, array $context = []);
}
