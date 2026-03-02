<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4jconnect.com | https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

extract($displayData);

$body = (string) ($displayData['body'] ?? '');
$sources = (array) ($displayData['sources'] ?? []);

?>

<div class="ai-message-addon-container">

    <?php if ($sources): ?>
        <details class="ai-message-addon-sources">
            <summary><?php echo JText::plural('VCM_AI_ASSISTANT_VIEW_SOURCES', count($sources)); ?></summary>
            <ul>
                <?php foreach ($sources as $source): ?>
                    <li><?php echo $source; ?></li>
                <?php endforeach; ?>
            </ul>
        </details>
    <?php endif; ?>
    
    <?php if ($body): ?>
        <div class="ai-message-addon-body"><?php echo $body; ?></div>
    <?php endif; ?>

</div>