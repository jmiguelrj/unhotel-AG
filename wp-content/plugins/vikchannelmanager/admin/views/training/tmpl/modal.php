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

<style>
    .vcm-training-draft-modal {
        display: flex;
        gap: 10px;
        border: 1px solid var(--vbo-basic-btn);
        border-radius: 4px;
    }
    .vcm-training-draft-modal .vbo-admin-container {
        flex: 1;
    }
    .vcm-training-draft-modal .vcm-training-draft-reference {
        max-width: 35%;
        border-left: 1px solid var(--vbo-basic-btn);
    }
    .vcm-training-draft-modal .vcm-training-draft-reference .aitools-messages-container {
        border: 0;
        margin: 0;
        padding: 20px;
    }
    .vcm-training-draft-modal .vcm-training-draft-reference .aitools-messages-container .aitools-messages-list .aitools-message-row {
        margin-bottom: 20px;
    }
    .vcm-training-draft-modal .vcm-training-draft-reference .aitools-messages-container .aitools-messages-list .aitools-message-row:last-child {
        margin-bottom: 0;
    }
    .vcm-training-draft-modal .vcm-training-draft-reference .aitools-message-row.not-me .aitools-message-bubble {
        margin-left: 0;
    }
    @media screen and (max-width: 1080px) {
        .vcm-training-draft-modal {
            flex-direction: column;
        }
        .vcm-training-draft-modal .vcm-training-draft-reference {
            max-width: 100%;
            border-left: 0;
            border-top: 1px solid var(--vbo-basic-btn);
        }
    }
</style>

<form action="#" method="post" id="vbo-training-draft-form" enctype="multipart/form-data">
    
    <div class="vcm-training-draft-modal">

        <div class="vbo-admin-container vbo-admin-container-full vbo-admin-container-compact">
            <div class="vbo-params-wrap">
                <?php echo $this->loadTemplate('params'); ?>
            </div>
        </div>

        <?php if (!empty($this->training->reference)): ?>
            <div class="vcm-training-draft-reference">
                <?php echo $this->loadTemplate('reference'); ?>
            </div>
        <?php endif; ?>

    </div>

</form>
