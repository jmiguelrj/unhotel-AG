/**
 * VikChannelManager aitools.js
 * Copyright (C) 2024 E4J s.r.l. All Rights Reserved.
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * https://vikwp.com | https://e4j.com | https://e4jconnect.com
 */
(function($, w) {
    'use strict';

    let threadId = null;
    let chatMessages = [];
    let pendingAttachments = [];
    let isWaitingForAnswer = false;

    const typeAnswer = (area, words, min, max) => {
        if (isNaN(min) || min < 0) {
            min = 0;
        }

        if (isNaN(max) || max < 0) {
            max = 512;
        }

        return new Promise((resolve) => {
            typeAnswerRecursive(resolve, area, words, min, max);
        });
    }

    const typeAnswerRecursive = (resolve, area, words, min, max) => {
        if (words.length == 0) {
            // typed all the provided words
            resolve();
        } else {
            // register timeout to append the next word
            setTimeout(() => {
                let val = area.html();
                // extract word and append it to the specified area
                area.html((val.length ? val + ' ' : '') + words.shift());
                // scroll chat to bottom
                scrollToBottom();
                // keep going until we reach the end of the queue
                typeAnswerRecursive(resolve, area, words, min, max);
            }, Math.floor(Math.random() * (max - min + 1) + min));
        }
    }

    const scrollToBottom = () => {
        const conv = $('.aitools-messages-list');
        conv.scrollTop(conv[0].scrollHeight + 200);
    }

    const isBoxOutOfViewport = (box, margin, height) => {
        const conv = $('.aitools-messages-list');

        let box_y         = box[0].offsetTop;
        let scroll        = conv.scrollTop();
        let screen_height = conv.height();
        let box_height    = height ? height : box.height();

        // check whether the height of the box exceeds 
        // the total height of the window
        if (box_height > screen_height) {
            // use a third of the screen height as reference
            box_height = screen_height / 3;
        }

        if (margin === undefined) {
            margin = 0;
        }

        // check if we should scroll down
        if (box_y - scroll + box_height + margin > screen_height) {
            return box_y - scroll + margin + box.height() - screen_height;
        }

        // check if we should scroll up
        if (scroll - margin > box_y + box_height) {
            return box_y - scroll - margin;
        }
        
        // the box is visible
        return false;
    }

    const postMessage = async (textarea) => {
        if ($(textarea).prop('readonly')) {
            return false;
        }

        const message = $(textarea).val();

        if (!message) {
            return false;
        }

        // prevent actions when the AI is answering
        isWaitingForAnswer = true;

        $(textarea).prop('readonly', true);

        const container = $(textarea).closest('.aitools-messages-container');

        container.find('.no-records-placeholder').hide();

        // draw user message
        let bubble = buildMessageBubble(message, false);
        container.find('.aitools-messages-list').append(bubble);

        // draw registered attachments
        pendingAttachments.forEach((attachment) => {
            let file;

            if (attachment.name.match(/\.(a?png|jpe?g|gif|webp)$/)) {
                file = $('<img />').attr('title', attachment.name).attr('src', attachment.base64);
            } else {
                file = $('<i class="fas"></i>').attr('title', attachment.name);

                if (attachment.name.match(/\.(pdf|docx?|rtf|odt|pages|txt|md|markdown)$/)) {
                    file.addClass('fa-file-pdf');
                } else if (attachment.name.match(/\.(xlsx?|csv|ods|numbers)$/)) {
                    file.addClass('fa-file-excel');
                } else if (attachment.name.match(/\.(ppsx?|pptx?|odp|key)$/)) {
                    file.addClass('fa-file-powerpoint');
                }
            }

            let fileBubble = buildMessageBubble(file, false);
            fileBubble.addClass('message-attachment')
            bubble = bubble.add(fileBubble);
            
            container.find('.aitools-messages-list').append(fileBubble);
        });

        // hide attachments bar
        $('.aitools-uploaded-files').hide();

        // draw assistant message with a dot flashing animation
        const aiBubble = buildMessageBubble('<div class="ai-thinking"><div class="dot-flashing"></div></div>', true);
        container.find('.aitools-messages-list').append(aiBubble);

        scrollToBottom();

        let timeoutMin = 32, timeoutMax = 128;

        try {
            // ask AI to answer
            const response = await postMessageRequest(message, pendingAttachments);
            clearAttachments();

            if (response.text.length >= 1000) {
                // write 4x faster in case the response contains 1000+ characters
                timeoutMin /= 4;
                timeoutMax /= 4;
            } else if (response.text.length >= 500) {
                // write 2x faster in case the response contains 500+ characters
                timeoutMin /= 2;
                timeoutMax /= 2;
            }

            // start typing animation
            await typeAnswer(aiBubble.find('.aitools-message-text').html(''), response.text.replace(/(?:\r\n|\r|\n)/g, '<br>').split(/ +/), timeoutMin, timeoutMax);

            // normalize math formulas in resulting HTML
            response.html = response.html.replace(/(?:<br\s*\/?>\s*){0,2}\\\[(.*?)\\\](?:<br\s*\/?>\s*){0,2}/gs, (match) => {
                return match.replace(/<br\s*\/?>/g, "\n");
            });

            // refresh HTML with fixed syntax
            aiBubble.find('.aitools-message-text').html(response.html);

            // format all the returned formulas with KaTeX
            renderMathInElement(aiBubble.find('.aitools-message-text')[0]);

            if (response.addon) {
                // display add-on after the message
                container.find('.aitools-messages-list').append($('<div class="aitools-message-addon"></div>').html(response.addon));
                scrollToBottom();
            }

            // display the button to clear the conversation
            container.find('.aitools-clear-btn').addClass('slide-in');

            // collect both the user and assistant messages
            chatMessages = chatMessages.concat([
                {
                    role: 'user',
                    content: message,
                },
                {
                    role: 'assistant',
                    content: response.text,
                },
            ]);
        } catch (error) {
            alert(error);

            // remove drawn messages
            bubble.remove();
            aiBubble.remove();

            // restore the textarea value with the message typed by the user
            $(textarea).val(message);

            if (container.find('.aitools-messages-list .aitools-message-row').length == 0) {
                container.find('.no-records-placeholder').show();
            }
        }

        // show attachments bar again
        $('.aitools-uploaded-files').show();

        $(textarea).prop('readonly', false);

        // we are no longer waiting for an answer
        isWaitingForAnswer = false;
    }

    const postMessageRequest = (message, attachments) => {
        return new Promise((resolve, reject) => {
            // make request to look for an answer
            VBOCore.doAjax(
                // AI end-point
                w.AI_TOOLS_TASK_URI,
                {
                    thread_id: threadId,
                    messages: chatMessages.concat([{
                        role: 'user',
                        content: message,
                        attachments: attachments,
                    }]),
                },
                (result) => {
                    // always refresh thread ID on success
                    threadId = result.thread_id;
                    resolve(result);
                },
                (error) => {
                    reject(error.responseText || error.statusText || 'Unknown error');
                }
            );
        });
    }

    const buildMessageBubble = (message, ai) => {
        const messageRow = $('<div class="aitools-message-row"></div>');

        if (ai) {
            messageRow.addClass('not-me');
        } else {
            messageRow.addClass('me');
        }

        // add avatar to message (only if not me)
        if (messageRow.hasClass('not-me')) {
            messageRow.append(
                $('<div class="aitools-message-author-avatar"></div>').html(
                    $('<img>').attr('src', w.AI_TOOLS_AVATAR_URI)
                )
            );
        }

        const bubble = $('<div class="aitools-message-bubble"></div>');
                
        // add message to bubble
        if (typeof message === 'string') {
            bubble.append(
                $('<div class="aitools-message-text"></div>').html(message.replace(/(?:\r\n|\r|\n)/g, "<br>"))
            );
        } else {
            bubble.append(message);
        }

        // add bubble to message row
        messageRow.append(bubble);

        return messageRow;
    }

    const clearConversation = () => {
        // clear the thread ID
        threadId = null;
        // reset chat messages pool
        chatMessages = [];

        // empty the chat HTML container
        $('.aitools-messages-list').html('');

        // display the placeholder again
        $('.no-records-placeholder').show();

        // hide clear button
        $('.aitools-clear-btn').removeClass('slide-in');

        clearAttachments();
    }

    const addAttachments = (files) => {
        for (let i = 0; i < files.length; i++) {
            let file = files[i];

            // create attachment node
            const attachmentNode = $('<div class="file-attachment"></div>');
            attachmentNode.append($('<span class="attachment-name"></span>').text(file.name));

            const removeBtn = $('<i class="fas fa-times attachment-remove-btn"></i>').on('click', () => {
                // remove attachment and node
                attachmentNode.remove();
                pendingAttachments = pendingAttachments.filter(a => a.name != file.name);
            });

            attachmentNode.append(removeBtn);

            // create upload/reading progress
            const progress = $('<progress max="100" value="0" data-index="' + i + '"></progress>');
            attachmentNode.append(progress);

            $('.aitools-uploaded-files').append(attachmentNode);

            let reader = new FileReader();

            reader.onload = (event) => {
                pendingAttachments.push({
                    name: file.name,
                    base64: event.target.result,
                });

                setTimeout(() => {
                    progress.remove();
                }, 1500);
            };

            reader.onerror = (event) => {
                // unable to read file
                alert('Unsupported file.');
                attachmentNode.remove();
            };

            reader.onprogress = (event) => {
                console.log(event);

                if (event.lengthComputable) {
                    const percent = (event.loaded / event.total) * 100;
                    
                    setTimeout(() => {
                        // update progress with a delay to prevent refresh issues with certain browsers
                        progress.val(percent);
                    }, 64);
                }
            };

            reader.readAsDataURL(file);
        }
    }

    const clearAttachments = () => {
        // clear selected files
        $('.aitools-upload-files').val(null);
        pendingAttachments = [];

        $('.aitools-uploaded-files').html('');
    }

    $(function() {
        // submit the message after clicking the "send" button
        $(document).on('click', '.aitools-send-btn', function() {
            const textarea = $(this).prevAll('textarea');
            postMessage(textarea);
            textarea.val('');
        });

        // hit ENTER to send a message
        $(document).on('keydown', '.aitools-message-area', function(event) {
            // check if ENTER was pressed without any other modifiers
            if (event.key == 'Enter' && !event.altKey && !event.ctrlKey && !event.shiftKey) {
                postMessage(this);

                $(this).val('');
                return false;
            }
        });

        // propagate the focus to the textarea whenever the action wrapper is clicked
        $(document).on('click', '.aitools-messages-action', function(event) {
            if ($(this).is(event.target)) {
                $(this).find('textarea').focus();
            }
        });

        // extend the height of the widget (sidepanel only) whenever the textarea gets focused
        $(document).on('focus', '.aitools-message-area', function() {
            $(this).closest('.aitools-messages-container').addClass('focused');
        });

        // clear the current conversation
        $(document).on('click', '.aitools-clear-btn', () => {
            if (!isWaitingForAnswer) {
                // clear the conversation
                clearConversation();
            }
        });

        $(document).on('vbo-widget-ai-tools-dismissed', () => {
            // clear the conversation
            clearConversation();
        });

        $(document).on('click', '.ai-message-addon-sources summary', function() {
            const details = $(this).parent();

            // The click event is performed before assigning the "open" attribute to the parent.
            // This means that the details are expanded when open is false.
            if (!details.prop('open')) {
                // auto-scroll after clicking the "View source" link
                setTimeout(() => {
                    // auto-scroll only in case the details are not visible
                    if (isBoxOutOfViewport(details)) {
                        const conv = $('.aitools-messages-list');
                        conv.scrollTop(conv[0].scrollTop + details.height() + 20);
                    }
                }, 32);
            }
        });

        // open file manager after clicking the "upload" button
        $(document).on('click', '.aitools-upload-btn', function() {
            if (!isWaitingForAnswer) {
                $('.aitools-upload-files').val(null).trigger('click');
            }
        });

        // start file upload after selecting one or more documents
        $(document).on('change', '.aitools-upload-files', function() {
            // get selected files
            const files = $(this)[0].files;

            addAttachments(files);

            setTimeout(() => {
                $('.aitools-message-area').focus();
            }, 512);
        });

        // attachments drag & drop

        let dragCounter = 0;

        $(document).on('drag dragstart dragend dragover dragenter dragleave drop', '.aitools-messages-container', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        $(document).on('dragenter', '.aitools-messages-container', function(e) {
            // increase the drag counter because we may enter into a child element
            dragCounter++;

            $(this).addClass('drag-enter');
        });

        $(document).on('dragleave', '.aitools-messages-container', function(e) {
            // decrease the drag counter to check if we 
            // left the main container
            dragCounter--;

            if (dragCounter <= 0) {
                $(this).removeClass('drag-enter');
            }
        });

        $(document).on('drop', '.aitools-messages-container', function(e) {
            $(this).removeClass('drag-enter');
            
            addAttachments(e.originalEvent.dataTransfer.files);
        });
    });
})(jQuery, window);