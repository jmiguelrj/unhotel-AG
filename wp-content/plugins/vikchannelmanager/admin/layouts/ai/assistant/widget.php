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

/**
 * The layout display data.
 * 
 * @var widget  VCMAiAssistantAddonwidget  The addon widget to render.
 */
extract($displayData);

$class = $widget->getClass();

?>

<div class="ai-message-addon-widget<?php echo $class ? ' ' . $class : ''; ?>">

    <?php if ($title = $widget->getTitle()): ?>
        <div class="addon-widget-head">
            <?php if ($icon = $widget->getIcon()): ?>
                <?php if (substr($icon, 0, 1) === '<'): ?>
                    <?php echo $icon; ?>
                <?php else: ?>
                    <i class="<?php echo $this->escape($icon); ?>"></i>
                <?php endif; ?>
            <?php endif; ?>

            <span class="addon-widget-title"><?php echo $title; ?></span>
        </div>
    <?php endif; ?>
        
    <?php if ($summary = $widget->getSummary()): ?>
        <div class="addon-widget-summary"><?php echo $summary; ?></div>
    <?php endif; ?>

    <?php if ($body = $widget->getBody()): ?>
        <div class="addon-widget-body"><?php echo $body; ?></div>
    <?php endif; ?>

    <?php if ($footer = $widget->getFooter()): ?>
        <hr />
        <div class="addon-widget-footer"><?php echo $footer; ?></div>
    <?php endif; ?>

</div>