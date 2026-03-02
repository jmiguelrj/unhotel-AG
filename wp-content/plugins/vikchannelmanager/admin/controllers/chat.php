<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// load chat controller implementor
require_once VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'implementors' . DIRECTORY_SEPARATOR . 'chat.php';

/**
 * VikChannelManager chat controller.
 *
 * @since 1.6.13
 */
class VikChannelManagerControllerChat extends VCMControllerImplementorChat
{
	/**
     * Changes the AI auto-responder status (enabled/disabled) for the specified thread.
     * 
     * @return  void
     */
    public function toggle_ai_auto_responder()
    {
        $app = JFactory::getApplication();

        // get chat arguments
        $thread = new stdClass;
        $thread->id         = $app->input->getUint('id_thread', 0);
        $thread->ai_stopped = $app->input->getUint('ai_stopped', 0);

        JFactory::getDbo()->updateObject('#__vikchannelmanager_threads', $thread, 'id');

        $app->close();
    }
}
