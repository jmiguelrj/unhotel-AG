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

// load required resources only if not explicitly disabled
if ($load_assets ?? true) {
    // js and css assets
    JHtml::_('script', VCM_ADMIN_URI . 'layouts/ai/assistant/aitools.js', ['version' => VIKCHANNELMANAGER_SOFTWARE_VERSION]);
    JHtml::_('stylesheet', VCM_ADMIN_URI . 'layouts/ai/assistant/aitools.css', ['version' => VIKCHANNELMANAGER_SOFTWARE_VERSION]);

    JHtml::_('script', VCM_ADMIN_URI . 'assets/js/katex/katex.min.js', ['version' => VIKCHANNELMANAGER_SOFTWARE_VERSION]);
    JHtml::_('script', VCM_ADMIN_URI . 'assets/js/katex/auto-render.min.js', ['version' => VIKCHANNELMANAGER_SOFTWARE_VERSION]);
    JHtml::_('stylesheet', VCM_ADMIN_URI . 'assets/js/katex/katex.min.css', ['version' => VIKCHANNELMANAGER_SOFTWARE_VERSION]);

    // language definitions
    JText::script('VBO_AI_ASSISTANT_DISCLAIMER');
    JText::script('VBO_AI_ASSISTANT_DISCOVER_HINT');
    JText::script('VBO_AI_ASSISTANT_DISCOVER_TITLE');
}

if (empty($scope)) {
    $scope = 'concierge';
}

?>

<?php if (($widget_title ?? false) === true): ?>
<div class="vbo-admin-widget-wrapper">
    <div class="vbo-admin-widget-head">
        <h4><?php echo $widget_icon ?? ''; ?> <span><?php echo $widget_name ?? ''; ?></span></h4>
    </div>
</div>
<?php endif; ?>

<div class="aitools-messages-container">

    <div class="no-records-placeholder">
        <i class="fas fa-comments"></i>
        <div class="text">
            <span><?php echo JText::_('VBO_AI_ASSISTANT_CHAT_NO_MESSAGES'); ?></span>
            <?php VikBookingIcons::e('info-circle', 'aitools-disclaimer-trigger'); ?>
        </div>
        <div class="hint">
            <a href="javascript:void(0)" class="aitools-discover-link"><?php echo JText::_('VBO_AI_ASSISTANT_DISCOVER_HINT'); ?></a>
        </div>
    </div>
        
    <div class="aitools-messages-list">
        
    </div>

    <div class="aitools-messages-action-wrapper">
        <a href="javascript:void(0)" class="aitools-clear-btn"><?php VikBookingIcons::e('eraser'); ?> <?php echo JText::_('VBOSIGNATURECLEAR'); ?></a>
        <div class="aitools-messages-action">
            <textarea
                class="aitools-message-area"
                placeholder="<?php echo $this->escape(html_entity_decode(JText::_('VBO_AI_ASSISTANT_CHAT_MESSAGE_PLACEHOLDER'))); ?>"
                autocomplete="off"
            ><?php echo $prompt['message'] ?? ''; ?></textarea>
            <a href="javascript:void(0)" class="aitools-upload-btn">
                <?php VikBookingIcons::e('plus-circle', 'fa-2x'); ?>
            </a>
            <div class="aitools-uploaded-files"></div>
            <a href="javascript:void(0)" class="aitools-send-btn"><?php VikBookingIcons::e('arrow-circle-up', 'fa-2x'); ?></a>
            <input
                type="file" class="aitools-upload-files" multiple style="display: none;"
                accept=".png,.gif,.jpg,.jpeg,.webp,.txt,.md,.markdown,.pps,.ppsx,.ppt,.pptx,.odp,.key,.xls,.xlsx,.csv,.ods,.numbers,.pdf,.doc,.docx,.rtf,.odt,.pages"
            />
        </div>
    </div>

    <div class="aitools-dnd-target">
        
        <?php VikBookingIcons::e('image'); ?>
        
        <p><?php echo JText::_('VBO_DROP_FILES_TO_UPLOAD'); ?></p>

    </div>

</div>

<?php echo $this->sublayout('discover', ['scope' => $scope]); ?>

<script>
    (function($, w) {
        'use strict';

        /**
         * The image URI that will be used to draw the avatar of the AI assistant.
         * 
         * @var string
         */
        // w.AI_TOOLS_AVATAR_URI = '<?php echo VBO_ADMIN_URI . 'resources/channels/ai-icn-white.png'; ?>';
        w.AI_TOOLS_AVATAR_URI = '<?php echo VCM_ADMIN_URI . 'assets/css/channels/ai-avatar.png'; ?>';

        /**
         * The end-point URL that will be used to make the requests to the AI assistant.
         * 
         * @var string
         */
        w.AI_TOOLS_TASK_URI = '<?php echo VCMFactory::getPlatform()->getUri()->ajax('index.php?option=com_vikchannelmanager&task=ai.assistant&scope=' . $scope); ?>';

        $(function() {
            // add fixed height to the modal wrapper
            $('.aitools-message-area').closest('.vbo-modal-widget_modal-wrap').css('height', 'calc(100% - 25px)');

            // show disclaimer text
            $('.aitools-disclaimer-trigger').on('click', function() {
                let disclaimer_html = '';
                disclaimer_html += '<div class="aiassistant-disclaimer-wrap">';
                disclaimer_html += '    <div class="aiassistant-disclaimer-icn"><?php VikBookingIcons::e('exclamation-circle'); ?></div>';
                disclaimer_html += '    <div class="aiassistant-disclaimer-txt">' + Joomla.JText._('VBO_AI_ASSISTANT_DISCLAIMER') + '</div>';
                disclaimer_html += '</div>';
                VBOCore.displayModal({
                    suffix:      'vcm-aiassistant-disclaimer',
                    extra_class: 'vbo-modal-rounded vbo-modal-tooltip',
                    body:        disclaimer_html,
                    header:      false,
                    lock_scroll: true,
                });
            });

            // show assistant capabilities
            $('.aitools-discover-link').on('click', function() {
                // identify the textarea related to the clicked link
                const messageArea = $(this).closest('.aitools-messages-container').find('.aitools-message-area');

                const modalBody = $('.aitools-discover-modal[data-scope="<?php echo $scope; ?>"]').first();

                VBOCore.displayModal({
                    suffix: 'vcm-aiassistant-capabilities',
                    title: $('<strong></strong>').text(Joomla.JText._('VBO_AI_ASSISTANT_DISCOVER_TITLE')).css('font-size', '18px'),
                    extra_class: 'vbo-modal-tall vbo-modal-rounded vbo-modal-nofooter',
                    body: modalBody,
                    lock_scroll: true,
                    dismiss_event: 'vcm-aiassistant-capabilities.dismiss',
                    onDismiss: (event) => {
                        modalBody.appendTo($('.aitools-discover-modal-wrapper[data-scope="<?php echo $scope; ?>"]').first());

                        if (event?.detail?.runExample) {
                            // update textarea content
                            messageArea.val(event.detail.runExample.question).focus();

                            // simulate submit key down only in case of GET role
                            if ((event.detail.runExample.role || 'get').toLowerCase() === 'get') {
                                const enterKeydownEvent = $.Event('keydown');
                                enterKeydownEvent.key = 'Enter';
                                messageArea.trigger(enterKeydownEvent);
                            }
                        }
                    },
                });
            });

            <?php if ($auto_focus ?? false): ?>
                // auto-focus textarea
                const el = $('textarea.aitools-message-area').first()[0];
                el.focus();
                el.setSelectionRange(el.value.length, el.value.length);
            <?php endif; ?>

            <?php if ($prompt['submit'] ?? false): ?>
                // simulate submit key down only in case of GET role
                const enterKeydownEvent = $.Event('keydown');
                enterKeydownEvent.key = 'Enter';
                $('textarea.aitools-message-area').first().trigger(enterKeydownEvent);
            <?php endif; ?>
        });
    })(jQuery, window);
</script>