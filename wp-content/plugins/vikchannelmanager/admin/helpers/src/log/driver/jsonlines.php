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
 * This logger can be used to register JSON-encoded logs within a .jsonl file.
 * It is helpful in case the log file supports an automatic scan of its contents,
 * as every line of the file is occupied by a JSON string.
 * 
 * @since 1.9.16
 */
class VCMLogDriverJsonlines extends VCMLogDriverFile
{
    /**
     * @inheritDoc
     */
    protected function renderMessage(string $level, string $message, array $context = [])
    {
        $data = new \stdClass;
        $data->level = $level;
        $data->message = $message;
        $data->date = JFactory::getDate('now')->toISO8601();

        return json_encode($data) . "\n";
    }
}
