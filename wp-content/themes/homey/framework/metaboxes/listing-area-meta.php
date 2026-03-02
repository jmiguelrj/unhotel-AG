<?php
/* Add metaboxes to listing Area/Neighborhood */

if ( !function_exists( 'homey_listing_area_add_meta_fields' ) ) :
    function homey_listing_area_add_meta_fields() {
        $homey_meta = homey_get_listing_area_meta();
        $all_cities = homey_get_all_cities();
        ?>

        <div class="form-field">
            <label><?php esc_html_e('Which city has this area?', 'homey' ); ?></label>
            <select name="homey[parent_city]" class="widefat">
                <option value=""><?php esc_html_e('Select City', 'homey'); ?></option>
                <?php echo ''.$all_cities; ?>
            </select>
            <p class="description"><?php esc_html_e('Select city which has this area.', 'homey' ); ?></p>
        </div>



        <?php
    }
endif;

add_action( 'listing_area_add_form_fields', 'homey_listing_area_add_meta_fields', 10, 2 );


/**
 *   ----------------------------------------------------------------------------------------------------------------------------------------------------
 *   2.0 - Edit meta field
 *   ----------------------------------------------------------------------------------------------------------------------------------------------------
 */

if ( !function_exists( 'homey_listing_area_edit_meta_fields' ) ) :
    function homey_listing_area_edit_meta_fields( $term ) {
        $homey_meta = homey_get_listing_area_meta();

        if(is_object ($term)) {
            $term_id      =  $term->term_id;
            $term_meta    =  get_option( "_homey_listing_area_$term_id" );
            $parent_city  =  isset($term_meta['parent_city']) ? $term_meta['parent_city'] : '';
            $all_cities   =  homey_get_all_cities($parent_city);

        } else {
            $all_cities   =  homey_get_all_cities();
        }
        ?>

        <tr class="form-field">
            <th scope="row" valign="top"><label><?php esc_html_e('Which city has this area?', 'homey' ); ?></label></th>
            <td>
                <select name="homey[parent_city]" class="widefat">
                    <?php echo ''.$all_cities; ?>
                </select>
                <p class="description"><?php esc_html_e('Select city which has this area.', 'homey' ); ?></p>
            </td>
        </tr>

        <?php
    }
endif;

add_action( 'listing_area_edit_form_fields', 'homey_listing_area_edit_meta_fields', 10, 2 );


if ( !function_exists( 'homey_save_listing_area_meta_fields' ) ) :
    function homey_save_listing_area_meta_fields( $term_id ) {

        if ( isset( $_POST['homey'] ) ) {

            $homey_meta = array();

            $homey_meta['parent_city'] = isset( $_POST['homey']['parent_city'] ) ? $_POST['homey']['parent_city'] : '';

            update_option( '_homey_listing_area_'.$term_id, $homey_meta );
        }

    }
endif;

add_action( 'edited_listing_area', 'homey_save_listing_area_meta_fields', 10, 2 );
add_action( 'create_listing_area', 'homey_save_listing_area_meta_fields', 10, 2 );

?>