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

<div class="form-wrap">

    <h2><?php _e('Register a new license', 'vikupdater'); ?></h2>

    <form id="addlicense" method="post" action="tools.php?page=vikupdater" class="validate">

        <div class="form-field form-required license-product-wrap">
            <label for="license-product"><?php _e('Product', 'vikupdater'); ?></label>

            <select name="product" id="license-product" class="postform" aria-required="true" aria-describedby="license-product-description">
                <option value=""><?php _e('Select an option', 'vikupdater'); ?></option>

                <?php foreach ($this->productsOptGroups as $label => $options): ?>
                    <optgroup label="<?php echo esc_attr($label); ?>">
                        <?php foreach ($options as $value => $text): ?>
                            <option value="<?php echo esc_attr($value); ?>"><?php echo $text; ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>

            <p id="license-product-description">
                <?php _e('Choose the product for which you want to register a license key.', 'vikupdater'); ?>
            </p>
        </div>
    
        <div class="form-field form-required license-code-wrap">
            <label for="license-code"><?php _e('License code', 'vikupdater'); ?></label>

            <input name="license" id="license-code" type="text" size="40" aria-required="true" aria-describedby="license-code-description">

            <p id="license-code-description"><?php _e('Enter here the license key received after the purchase.', 'vikupdater'); ?></p>
        </div>

        <p class="submit">
            <?php submit_button(__('Register a new license', 'vikupdater'), 'primary', 'submit', false); ?>
            <span class="spinner"></span>
        </p>

        <input type="hidden" name="task" value="licenses.save">
        <?php wp_nonce_field('license'); ?>
        
    </form>

</div>
