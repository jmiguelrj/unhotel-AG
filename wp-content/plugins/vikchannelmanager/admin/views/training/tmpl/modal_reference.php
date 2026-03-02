<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

?>

<div class="aitools-messages-container">
    <div class="aitools-messages-list">
        <?php foreach ($this->training->reference as $message): ?>
            <div class="aitools-message-row <?php echo ($message->role ?? '') === 'assistant' ? 'me' : 'not-me'; ?>">
                <div class="aitools-message-bubble">
                    <div class="aitools-message-text"><?php echo $message->content ?? '--'; ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>