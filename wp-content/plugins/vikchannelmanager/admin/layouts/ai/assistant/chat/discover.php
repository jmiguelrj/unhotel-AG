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

$scope = $displayData['scope'] ?? 'concierge';

$sections = [];

/** @var $sections  obtain the examples supported by the specified scope */
echo $this->sublayout($scope, ['sections' => &$sections]);

?>

<div style="display: none;" class="aitools-discover-modal-wrapper" data-scope="<?php echo $scope; ?>">
    <div class="aitools-discover-modal" data-scope="<?php echo $scope; ?>">

        <?php foreach ($sections as $section): ?>
            <div class="aitools-discover-card">
                <h3>
                    <?php if ($section['icon'] ?? ''): ?>
                        <i class="<?php echo $section['icon']; ?>"></i>
                    <?php endif; ?>
                    <span><?php echo $section['title']; ?></span>
                </h3>

                <?php if ($section['description']) : ?>
                    <p><?php echo $section['description']; ?></p>
                    <hr />
                <?php endif; ?>

                <ul>
                    <?php foreach ($section['options'] as $option): ?>
                        <li>
                            <div><?php echo $option['summary']; ?></div>

                            <div class="aitools-discover-example">
                                <div class="aitools-message-row me">
                                    <div class="aitools-message-bubble">
                                        <div class="aitools-message-text"><?php echo $option['example']; ?></div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-primary btn-small aitools-discover-try text-nowrap" data-role="<?php echo $option['role']; ?>">
                                    <?php if (strcasecmp($option['role'], 'get')): ?>
                                        <i class="<?php echo VikBookingIcons::i('pen'); ?>"></i>&nbsp;<?php echo JText::_('JTOOLBAR_EDIT'); ?>
                                    <?php else: ?>
                                        <i class="<?php echo VikBookingIcons::i('play'); ?>"></i>&nbsp;<?php echo JText::_('VBO_TRY'); ?>
                                    <?php endif; ?>
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<script>
    (function($) {
        'use strict';

        $(function() {
            $('button.aitools-discover-try').on('click', function() {
                // extract question from bubble
                const question = $(this).closest('li').find('.aitools-message-text').text();
                // extract request role
                const role = ($(this).data('role') || 'get').toLowerCase();

                // dismiss the modal
                VBOCore.emitEvent('vcm-aiassistant-capabilities.dismiss', {
                    runExample: {
                        question: question,
                        role: role,
                    },
                });
            });
        });
    })(jQuery);
</script>