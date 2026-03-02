<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 08/10/16
 * Time: 9:00 PM
 */
if ( !function_exists( 'homey_listing_state_add_meta_fields' ) ) :
    function homey_listing_state_add_meta_fields() {
        ?>

        <div class="form-field">
            <label><?php esc_html_e('Which country has this state?', 'homey' ); ?></label>
            <select name="homey[parent_country]" class="widefat">
                <option value=""><?php esc_html_e('Select Country', 'homey'); ?></option>
                <?php echo homey_get_all_countries(); ?>
            </select>
            <p class="description"><?php esc_html_e('Select country which has this state.', 'homey' ); ?></p>
        </div>

        <?php
    }
endif;

add_action( 'listing_state_add_form_fields', 'homey_listing_state_add_meta_fields', 10, 2 );


/**
 *   ----------------------------------------------------------------------------------------
 *   2.0 - Edit meta field
 *   ----------------------------------------------------------------------------------------
 */

if ( !function_exists( 'homey_listing_state_edit_meta_fields' ) ) :
    function homey_listing_state_edit_meta_fields( $term ) {
        $homey_meta = homey_get_listing_state_meta();

        $parent_country = '';
        if(is_object ($term)) {
            $term_id      =  $term->term_id;
            $term_meta    =  get_option( "_homey_listing_state_$term_id" );
            $parent_country  =  $term_meta['parent_country'] ? $term_meta['parent_country'] : '';
        } 
        ?>

        <tr class="form-field">
            <th scope="row" valign="top"><label><?php esc_html_e('Which country has this state?', 'homey' ); ?></label></th>
            <td>
                <select name="homey[parent_country]" class="widefat">
                    <option value=""><?php esc_html_e('Select Country', 'homey'); ?></option>
                    <?php echo homey_get_all_countries($parent_country); ?>
                </select>
                <p class="description"><?php esc_html_e('Select country which has this state.', 'homey' ); ?></p>
            </td>
        </tr>

        <?php
    }
endif;

add_action( 'listing_state_edit_form_fields', 'homey_listing_state_edit_meta_fields', 10, 2 );


if ( !function_exists( 'homey_save_listing_state_meta_fields' ) ) :
    function homey_save_listing_state_meta_fields( $term_id ) {

        if ( isset( $_POST['homey'] ) ) {

            $homey_meta = array();

            $homey_meta['parent_country'] = isset( $_POST['homey']['parent_country'] ) ? $_POST['homey']['parent_country'] : '';

            update_option( '_homey_listing_state_'.$term_id, $homey_meta );
        }

    }
endif;

add_action( 'edited_listing_state', 'homey_save_listing_state_meta_fields', 10, 2 );
add_action( 'create_listing_state', 'homey_save_listing_state_meta_fields', 10, 2 );
?>