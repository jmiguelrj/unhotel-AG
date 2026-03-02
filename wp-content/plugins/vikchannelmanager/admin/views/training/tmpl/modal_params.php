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

<div class="vbo-params-container">

    <!-- TITLE -->

    <div class="vbo-param-container">
        <div class="vbo-param-label"><?php echo JText::_('VCM_TITLE'); ?></div>
        <div class="vbo-param-setting">
            <input type="text" name="title" value="<?php echo $this->escape($this->training->title ?? ''); ?>" />
            <span class="vbo-param-setting-comment"><?php echo JText::_('VCM_TRAINING_TITLE_DESC'); ?></span>
        </div>
    </div>

    <!-- LISTINGS -->

    <div class="vbo-param-container">
        <div class="vbo-param-label"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?></div>
        <div class="vbo-param-setting">
            <select name="listing_selection">
                <?php
                $options = [
                    JHtml::_('select.option', '*', JText::_('VCM_ALL_LISTINGS')),
                    JHtml::_('select.option', 0, JText::_('VCM_ALL_LISTINGS_SELECTED')),
                    JHtml::_('select.option', 1, JText::_('VCM_ALL_LISTINGS_EXCEPT')),
                ];

                echo JHtml::_('select.options', $options, 'value', 'text', $this->training->listing_selection ?? '*');
                ?>
            </select>
            <div class="listing-selection-child" style="margin-top: 10px;<?php echo ($this->training->listing_selection ?? '*') !== '*' ? '' : 'display: none;'; ?>">
                <select name="id_listing[]" multiple>
                    <?php
                    $options = [];

                    foreach ($this->rooms as $room) {
                        $options[] = JHtml::_('select.option', $room['id'], $room['name']);
                    }

                    echo JHtml::_('select.options', $options, 'value', 'text', $this->training->id_listing ?? 0);
                    ?>
                </select>
            </div>
            <span class="vbo-param-setting-comment"><?php echo JText::_('VCM_TRAINING_LISTING_DESC'); ?></span>
        </div>
    </div>

    <!-- CONTENT -->

    <div class="vbo-param-container">
        <div class="vbo-param-label"><?php echo JText::_('VCMGREVCONTENT'); ?></div>
        <div class="vbo-param-setting">
            <textarea name="content" style="min-height: 200px; width: 100% !important;" maxlength="1500"><?php echo $this->training->content ?? ''; ?></textarea>
            <p class="warn"><?php echo JText::plural('VCM_AI_TRAINING_NEEDS_REVIEW_WARNING', $this->trainingModel->getExpirationDays($this->training)); ?></p>
        </div>
    </div>

    <!-- ATTACHMENTS -->

    <div class="vbo-param-container">
        <div class="vbo-param-label"><?php echo JText::_('VCM_ATTACHMENTS'); ?></div>
        <div class="vbo-param-setting">
            <input type="file" name="attachments[]" multiple />
            <span class="vbo-param-setting-comment"><?php echo JText::_('VCM_TRAINING_ATTACHMENTS_DESC'); ?></span>
        </div>
    </div>
    
    <input type="hidden" name="language" value="<?php echo $this->escape($this->training->language ?? 'en-GB'); ?>" />
    <input type="hidden" name="published" value="<?php echo (int) ($this->training->published ?? 0); ?>" />
    <input type="hidden" name="id" value="<?php echo (int) ($this->training->id ?? 0); ?>" />

</div>

<script>
    (function($) {
        'use strict';

        $(function() {
            $('select[name="listing_selection"]').on('change', function() {
                if ($(this).val() === '*') {
                    $('.listing-selection-child').hide();
                } else {
                    $('.listing-selection-child').show();
                }
            });

            $('select[name="id_listing[]"]').select2({
                width: '100%',
            });
        })
    })(jQuery);
</script>