<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4jconnect.com | https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Obtain vars from arguments received in the layout file.
 * 
 * @var string        $context  The context for which contents should be generated.
 * @var string        $channel  The channel for which contents will be generated.
 * @var string        $prefix   The class prefix for the various elements.
 * @var array|object  $data     The context data record, like the listing information.
 * @var string        $info     Optional information to be used by default.
 * @var string        $ecls     Optional extra class list to append.
 * @var string        $help     Optional help text to enter the information.
 */
extract($displayData);

// define the default argument values
$context = $context ?? 'listing';
$channel = $channel ?? '';
$prefix  = $prefix ?? 'vcm-content-genai';
$data    = (array) ($data ?? []);
$info    = $info ?? '';
$ecls    = $ecls ?? null;
$help    = $help ?? '';

?>
<div class="<?php echo $prefix; ?>-helper<?php echo $ecls ? ' ' . $ecls : ''; ?>" style="display: none;">
    <div class="<?php echo $prefix; ?>-wrap<?php echo $ecls ? ' ' . $ecls : ''; ?>">
        <div class="vcm-admin-container vcm-admin-container-full vcm-admin-container-compact">
            <div class="vcm-params-wrap">
                <div class="vcm-params-container">
                    <div class="vcm-params-block">

                        <div class="vcm-param-container">
                            <div class="vcm-param-label"><?php echo JText::_('VCMBCAHLANGUAGE'); ?></div>
                            <div class="vcm-param-setting">
                                <select class="<?php echo $prefix; ?>-field" data-field="language">
                                    <option value="">- <?php echo JText::_('VCM_APPEARANCE_PREF_AUTO'); ?> -</option>
                                <?php
                                foreach (VikBooking::getVboApplication()->getKnownLanguages() as $tag => $lang) {
                                    ?>
                                    <option value="<?php echo JHtml::_('esc_attr', $lang['nativeName'] . '__' . $tag); ?>"><?php echo JHtml::_('esc_html', $lang['nativeName']); ?></option>
                                    <?php
                                }
                                ?>
                                </select>
                            </div>
                        </div>

                        <?php

                        // information string
                        $def_ai_info = $info;

                        if ($data && !strcasecmp($context, 'listing')) {
                            // build the basic listing information string
                            $info_list = [
                                $data['name'] ?? '',
                                $data['property_type_group'] ?? '',
                                $data['street'] ?? '',
                                $data['city'] ?? '',
                                $data['country_code'] ?? '',
                            ];

                            $def_ai_info .= "\n" . implode(', ', array_filter($info_list));
                        }

                        ?>
                        <div class="vcm-param-container">
                            <div class="vcm-param-label"><?php echo JText::_('VCMMENUTACDETAILS'); ?></div>
                            <div class="vcm-param-setting">
                                <textarea rows="6" class="<?php echo $prefix; ?>-field" data-field="information"><?php echo JHtml::_('esc_textarea', trim($def_ai_info)); ?></textarea>
                                <span class="vcm-param-setting-comment"><?php echo $help ?: JText::_('VCM_AI_GEN_CONTENT_INFO_HELP'); ?></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
