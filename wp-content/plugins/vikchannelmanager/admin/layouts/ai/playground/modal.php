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

// check whether the playground should be visible or not
$visible = (bool) ($displayData['visible'] ?? false);

JText::script('VCM_AI_PLAYGROUND');
JText::script('VCM_AI_PLAYGROUND_TEST_BTN');
JText::script('VCM_AI_PLAYGROUND_PROCESSING');
JText::script('VCM_AIRBNB_LISTINGS');
JText::script('VCM_ALL_LISTINGS');
JText::script('VCMMENUORDERS');
JText::script('VBCONFIGURETASK');
JText::script('VBOCHECKEDSTATUSZERO');
JText::script('VBO_SELECT_BOOKING');

if (empty($rooms)) {
    $rooms = VikBooking::getAvailabilityInstance()->loadRooms();
}

if (empty($bookings)) {
    $db = JFactory::getDbo();

    $query = $db->getQuery(true)
        ->select('*')
        ->from($db->qn('#__vikbooking_orders'))
        ->where($db->qn('status') . ' = ' . $db->q('confirmed'))
        ->where($db->qn('closure') . ' = 0')
        ->order($db->qn('id') . ' DESC');

    $db->setQuery($query, 0, 3);
    $bookings = $db->loadObjectList();

    foreach ($bookings as $booking)
    {
        $booking->roomsData = VikBooking::loadOrdersRoomsData($booking->id);
    }
}

?>

<div style="<?php echo $visible ? '' : 'display: none;'; ?>" id="ai-playground-modal-wrapper">
    <div id="ai-playground-modal">
        <div class="query-panel">

            <textarea
                id="vcm-ai-question"
                placeholder="<?php echo $this->escape(JText::_('VCM_AI_PLAYGROUND_QUESTION')); ?>"
            ></textarea>

            <textarea
                id="vcm-ai-answer"
                placeholder="<?php echo $this->escape(JText::_('VCM_AI_PLAYGROUND_ANSWER')); ?>"
                readonly
            ></textarea>

            <div id="vcm-ai-attachments" style="display: none;"></div>

        </div>
    </div>
</div>

<style>
    /* force width to keep buttons aligned */
    .vik-context-menu li a .button-icon {
        display: inline-block;
        width: 20px;
        text-align: center;
    }
</style>

<script>
    (function($) {
        'use strict';

        let aiReplyOptions = null;

        if (typeof sessionStorage !== 'undefined') {
            try {
                aiReplyOptions = JSON.parse(sessionStorage.getItem('vcm-ai-playground'));
            } catch (err) {

            }
        }

        if (!aiReplyOptions) {
            aiReplyOptions = {
                listings: null,
                bookings: null,
                reliability: 0,
            };
        }

        const typeAnswer = (textarea, words, min, max) => {
            if (isNaN(min) || min < 0) {
                min = 0;
            }

            if (isNaN(max) || max < 0) {
                max = 512;
            }

            return new Promise((resolve) => {
                typeAnswerRecursive(resolve, textarea, words, min, max);
            });
        }

        const typeAnswerRecursive = (resolve, textarea, words, min, max) => {
            if (words.length == 0) {
                // typed all the provided words
                resolve();
            } else {
                // register timeout to append the next word
                setTimeout(() => {
                    let val = textarea.val();
                    // extract word and append it within the textarea value
                    textarea.val((val.length ? val + ' ' : '') + words.shift());
                    // keep going until we reach the end of the queue
                    typeAnswerRecursive(resolve, textarea, words, min, max);
                }, Math.floor(Math.random() * (max - min + 1) + min));
            }
        }

        let loadingInterval;
        let previousHint;

        const startLoadingAnimation = (textarea) => {
            let text  = Joomla.JText._('VCM_AI_PLAYGROUND_PROCESSING');
            let count = 0;

            previousHint = $(textarea).attr('placeholder');

            loadingInterval = setInterval(() => {
                let dots = (count++ % 4);
                textarea.attr('placeholder', text + '.'.repeat(dots));
            }, 128);
        }

        const stopLoadingAnimation = (textarea) => {
            clearInterval(loadingInterval);

            setTimeout(() => {
                // reset the textarea placeholder
                $(textarea).attr('placeholder', previousHint);
            }, 256);
        }

        const askAi = (question, options) => {
            return new Promise((resolve, reject) => {
                // make request to look for an answer
                VBOCore.doAjax(
                    // AI end-point
                    'index.php?option=com_vikchannelmanager&task=ai.answer',
                    {
                        text: question,
                        options: options,
                    },
                    (result) => {
                        resolve(result);
                    },
                    (error) => {
                        reject(error.responseText || error.statusText || 'Unknown error');
                    }
                );
            });
        }

        const rooms = <?php echo json_encode(array_values($rooms)); ?>;
        const bookings = <?php echo json_encode($bookings); ?>;

        const handleRadioSelection = function(handle, event) {
            if (this.selected) {
                // already selected do nothing
                return false;
            }

            // get configuration
            const config = $(handle).vboContextMenu('config');

            // iterate buttons
            config.buttons.forEach((btn) => {
                if (!btn.group) {
                    return;
                }

                // clear selection for all the buttons that
                // belong to the same group of the clicked one
                if (btn.group == this.group) {
                    btn.selected = false;
                }

                btn.disabled = this.group === 'bookings' && btn.group !== 'bookings' && this.value;
            });

            // select the button
            this.selected = true;

            aiReplyOptions[this.group] = this.value;

            if (typeof sessionStorage !== 'undefined') {
                sessionStorage.setItem('vcm-ai-playground', JSON.stringify(aiReplyOptions));
            }
        }

        window.openAiPlayground = () => {
            let buttons = [];

            ////////////////////
            ///// BOOKINGS /////
            ////////////////////

            buttons.push({
                text: Joomla.JText._('VCMMENUORDERS'),
                disabled: true,
                class: 'btngroup',
                searchable: false,
                group: 'bookings',
            });

            const noneLabel = Joomla.JText._('VBOCHECKEDSTATUSZERO');

            buttons.push({
                text: noneLabel.substr(0, 1).toUpperCase() + noneLabel.substr(1),
                value: null,
                selected: !aiReplyOptions.bookings,
                icon: function(root, config) {
                    return this.selected ? 'fas fa-check' : 'keep-aligned';
                },
                action: handleRadioSelection,
                searchable: false,
                group: 'bookings',
            });

            bookings.forEach((booking) => {
                let text = [
                    '#' + booking.id,
                    (booking?.roomsData[0]?.t_first_name + ' ' + booking?.roomsData[0]?.t_last_name).trim(),
                    booking?.roomsData[0]?.room_name,
                ];

                buttons.push({
                    text: text.filter(s => s).join(', '),
                    value: booking.id,
                    selected: aiReplyOptions.bookings == booking.id,
                    icon: function(root, config) {
                        return this.selected ? 'fas fa-check' : 'keep-aligned';
                    },
                    action: handleRadioSelection,
                    group: 'bookings',
                });
            });

            const isCustomBooking = aiReplyOptions.bookings && !bookings.some(b => b.id == aiReplyOptions.bookings);

            buttons.push({
                text: Joomla.JText._('VBO_SELECT_BOOKING') + (isCustomBooking ? ' (#' + aiReplyOptions.bookings + ')' : ''),
                value: isCustomBooking ? aiReplyOptions.bookings : null,
                selected: isCustomBooking,
                icon: function(root, config) {
                    return this.selected ? 'fas fa-check' : 'keep-aligned';
                },
                manualAction: handleRadioSelection,
                action: function(handle, event) {
                    VBOCore.handleDisplayWidgetNotification({widget_id: 'booking_details'}, {selected_event: 'vcm-ai-playground-booking-selected'});

                    $(document).off('vcm-ai-playground-booking-selected').on('vcm-ai-playground-booking-selected', (event) => {
                        const booking = event.originalEvent.detail.booking;

                        if (!booking) {
                            return false;
                        }

                        this.text = Joomla.JText._('VBO_SELECT_BOOKING') + ' (#' + booking.id + ')';
                        this.value = booking.id;

                        this.manualAction(handle, event);
                    });
                },
                searchable: false,
                group: 'bookings',
            });

            ////////////////////
            ///// LISTINGS /////
            ////////////////////

            buttons.push({
                text: Joomla.JText._('VCM_AIRBNB_LISTINGS'),
                disabled: true,
                class: 'btngroup',
                searchable: false,
                group: 'listings',
            });

            buttons.push({
                text: Joomla.JText._('VCM_ALL_LISTINGS'),
                value: null,
                selected: !aiReplyOptions.listings,
                disabled: aiReplyOptions.bookings,
                icon: function(root, config) {
                    return this.selected ? 'fas fa-check' : 'keep-aligned';
                },
                action: handleRadioSelection,
                searchable: false,
                group: 'listings',
            });

            rooms.forEach((room) => {
                buttons.push({
                    text: room.name,
                    value: room.id,
                    selected: aiReplyOptions.listings == room.id,
                    disabled: aiReplyOptions.bookings,
                    icon: function(root, config) {
                        return this.selected ? 'fas fa-check' : 'keep-aligned';
                    },
                    action: handleRadioSelection,
                    group: 'listings',
                });
            });

            buttons[buttons.length - 1].separator = true;

            buttons.push({
                /** 
                 * @todo translate button text
                 */
                text: 'Accept only reliable answers',
                value: parseInt(aiReplyOptions.reliability),
                icon: function(root, config) {
                    return this.value ? 'fas fa-check' : 'keep-aligned';
                },
                action: function(handle, event) {
                    // negate current value
                    this.value ^= 1;
                    
                    aiReplyOptions.reliability = this.value;

                    if (typeof sessionStorage !== 'undefined') {
                        sessionStorage.setItem('vcm-ai-playground', JSON.stringify(aiReplyOptions));
                    }
                }
            });

            ////////////////////

            const settingsButton = $('<button id="ai-playground-config-btn" class="btn"></button>')
                .append('<?php VikBookingIcons::e('cog'); ?>')
                .append($('<span></span>').text(Joomla.JText._('VBCONFIGURETASK')).css('margin-left', '4px'));

            settingsButton.vboContextMenu({
                placement: 'top-left',
                buttons: buttons,
                search: true,
            });

            const testBtn = $('<button type="button" class="btn btn-success" id="ai-playground-test-btn"></button>')
                .text(Joomla.JText._('VCM_AI_PLAYGROUND_TEST_BTN'))
                .append('<span class="keyboard-shortcut minimal"><span class="keyboard-shortcut-key">⌃</span><span class="keyboard-shortcut-key">↵</span></span>');

            VBOCore.displayModal({
                suffix: 'ai-playground',
                title: $('<strong></strong>').text(Joomla.JText._('VCM_AI_PLAYGROUND')).css('font-size', '18px'),
                body: $('#ai-playground-modal'),
                body_prepend: true,
                lock_scroll: true,
                footer_left: settingsButton,
                footer_right: testBtn,
                onDismiss: () => {
                    $('#ai-playground-modal').appendTo('#ai-playground-modal-wrapper');
                },
            });
        }

        $(function() {
            const questionTextarea = $('#vcm-ai-question');
            const answerTextarea   = $('#vcm-ai-answer');
            const attachmentsArea  = $('#vcm-ai-attachments');

            // trigger submit with CTRL+ENTER shortcut
            questionTextarea.on('keydown', (event) => {
                let modifier = event.ctrlKey;

                // if (navigator.platform.toUpperCase().indexOf('MAC') === 0) {
                //     modifier = event.metaKey;
                // } else {
                //     modifier = event.ctrlKey;
                // }

                if (event.which == 13 && modifier == true) {
                    event.preventDefault();
                    $('#ai-playground-test-btn').trigger('click');
                    return false;
                }
            });

            $(document).on('click', '#ai-playground-test-btn', async function() {
                questionTextarea.prop('readonly', true);

                // disable to prevent duplicate requests
                $(this).prop('disabled', true);
                answerTextarea.val('');

                // clear attachments too
                attachmentsArea.hide().html('');

                startLoadingAnimation(answerTextarea);

                try {
                    const options = {
                        id_listing: aiReplyOptions.listings,
                        id_order: aiReplyOptions.bookings,
                        reliability: aiReplyOptions.reliability,
                    };

                    const answer = await askAi($('#vcm-ai-question').val(), options);

                    stopLoadingAnimation(answerTextarea);

                    // update answer textarea
                    await typeAnswer(answerTextarea, (answer.answer || '').split(/ +/), 32, 128);

                    if (answer.attachments.length) {
                        answer.attachments.forEach((attachment) => {
                            const link = $('<a class="label label-info" target="_blank"></a>');
                            link.attr('href', attachment.url);

                            link.append($('<i></i>').addClass('<?php echo VikBookingIcons::i('paperclip'); ?>'));
                            link.append($('<span></span>').text(attachment.filename));

                            attachmentsArea.append(link);
                        });
                        attachmentsArea.show();
                    }
                } catch (error) {
                    stopLoadingAnimation(answerTextarea);
                    alert(error);
                }

                questionTextarea.prop('readonly', false).focus();
                $(this).prop('disabled', false);
            });
        });
    })(jQuery);
</script>