<?php
/** 
 * @package     VikUpdater
 * @subpackage  views
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

?>

<form id="licenses-filter" method="post" action="tools.php?page=vikupdater">

    <div class="tablenav top">
        
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">
                <?php _e('Select bulk action'); ?>
            </label>

            <select name="task" id="bulk-action-selector-top">
                <option value=""><?php _e('Bulk actions'); ?></option>
                <option value="licenses.delete"><?php _e('Delete'); ?></option>
            </select>

            <input type="submit" id="doaction" class="button action" value="<?php _e('Apply'); ?>">
        </div>

        <?php
        $total_count = count($this->items);

        if ($total_count): ?>
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php
                    echo sprintf(
                        /* translators: %s: Number of items. */
                        _n('%s item', '%s items', $total_count),
                        number_format_i18n($total_count)
                    );
                    ?>
                </span>
            </div>
        <?php endif; ?>

    </div>

    <h2 class="screen-reader-text"><?php _e('Registered licenses list'); ?></h2>

    <table class="wp-list-table widefat fixed striped table-view-list licenses">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-1" type="checkbox">
                    <label for="cb-select-all-1"><span class="screen-reader-text"><?php _e('Select all'); ?></span></label>
                </td>

                <th scope="col" id="license-id-col" class="manage-column column-product column-primary">
                    <span><?php _e('Product', 'vikupdater'); ?></span>
                </th>

                <th scope="col" id="license-code-col" class="manage-column column-key">
                    <span><?php _e('Key', 'vikupdater'); ?></span>
                </th>

                <th scope="col" id="license-date-col" class="manage-column column-modified">
                    <span><?php _e('Date'); ?></span>
                </th>
            </tr>
        </thead>

        <tbody id="the-list" data-wp-lists="list:licenses">
            <?php if (!$this->items): ?>
                <tr class="no-items">
                    <td class="colspanchange" colspan="4"><?php _e('No registered licenses.', 'vikupdater'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($this->items as $index => $item): ?>
                    <tr id="license-<?php echo (int) $index; ?>">

                        <th scope="row" class="check-column">
                            <input type="checkbox" name="delete_licenses[]" value="<?php echo esc_attr($item->id); ?>" id="cb-select-<?php echo (int) $index; ?>">
                            <label for="cb-select-<?php echo (int) $index; ?>">
                                <span class="screen-reader-text">
                                    <?php
                                    echo sprintf(
                                        _x('Select %s', 'Means the selection of a record from the table. The wildcard will be replaced by a product name', 'vikupdater'),
                                        $this->products[$item->id]['name'] ?? $item->id
                                    ); 
                                    ?>
                                </span>
                            </label>
                        </th>

                        <td class="product column-product has-row-actions column-primary">
                            <strong>
                                <?php echo $this->products[$item->id]['name'] ?? $item->id; ?>
                            </strong>

                            <small>—&nbsp;<?php echo $this->products[$item->id]['group'] ?? __('Plugins'); ?></small>

                            <br>
                            
                            <div class="hidden" id="inline_3">
                                <div class="product"><?php echo $item->id; ?></div>
                                <div class="license"><?php echo $item->license; ?></div>
                            </div>
                            
                            <div class="row-actions">
                                <span class="inline">
                                    <button type="button" class="button-link editinline">
                                        <?php _e('Quick Edit'); ?>
                                    </button> | 
                                </span>

                                <span class="delete">
                                    <a href="<?php echo wp_nonce_url('tools.php?page=vikupdater&task=licenses.delete&delete_licenses[]=' . $item->id, 'license'); ?>" class="delete-license" role="button">
                                        <?php _e('Delete'); ?>
                                    </a>
                                </span>
                            </div>
                        </td>

                        <td class="license column-key">
                            <?php echo $item->license; ?>
                        </td>

                        <td class="license column-date">
                            <?php
                            echo date_i18n(
                                get_option('date_format') . ' ' . get_option('time_format'),
                                strtotime($item->modified ?: $item->created)
                            );
                            ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>

    </table>

    <?php wp_nonce_field('license'); ?>

</form>