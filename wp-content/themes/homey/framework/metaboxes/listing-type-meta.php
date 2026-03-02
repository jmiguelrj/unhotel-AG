<?php
/* Add metaboxes to listing type */

if ( !function_exists( 'homey_listing_type_add_meta_fields' ) ) :
	function homey_listing_type_add_meta_fields() {
		$homey_meta = homey_get_listing_type_meta();
?>

	<div class="form-field">
		 <label for="Color"><?php esc_html_e('Global Color', 'homey'); ?></label><br/>
		 <label><input type="radio" name="homey[color_type]" value="inherit" class="homey-radio color-type" <?php checked( $homey_meta['color_type'], 'inherit' );?>> <?php esc_html_e('Inherit from default accent color', 'homey' ); ?></label>
		 <label><input type="radio" name="homey[color_type]" value="custom" class="homey-radio color-type" <?php checked( $homey_meta['color_type'], 'custom' );?>> <?php esc_html_e('Custom', 'homey' ); ?></label>
		 <div id="homey_color_wrap">
		 <p>
		   	<input name="homey[color]" type="text" class="homey_colorpicker" value="<?php echo esc_attr($homey_meta['color']); ?>" data-default-color="<?php echo esc_attr($homey_meta['color']); ?>"/>
		 </p>
		 <?php if ( !empty( $colors ) ) { echo ''.$colors; } ?>
		 </div>
		 <div class="clear"></div>
		 <p class="howto"><?php esc_html_e('Choose color', 'homey' ); ?></p>
	</div>



	<?php
	}
endif;

add_action( 'listing_type_add_form_fields', 'homey_listing_type_add_meta_fields', 10, 2 );


/**
*   ----------------------------------------------------------------------------------------------------------------------------------------------------
*   2.0 - Edit Category meta field
*   ----------------------------------------------------------------------------------------------------------------------------------------------------
*/

if ( !function_exists( 'homey_listing_type_edit_meta_fields' ) ) :
	function homey_listing_type_edit_meta_fields( $term ) {
		$homey_meta = homey_get_listing_type_meta( $term->term_id );
?>
	  <?php

		$most_used = get_option( 'homey_recent_colors' );

		$colors = '';

		if ( !empty( $most_used ) ) {
			$colors .= '<p>'.__( 'Recently used', 'homey' ).': <br/>';
			foreach ( $most_used as $color ) {
				$colors .= '<a href="#" style="width: 20px; height: 20px; background: '.$color.'; float: left; margin-right:3px; border: 1px solid #aaa;" class="homey_colorpick" data-color="'.$color.'"></a>';
			}
			$colors .= '</p>';
		}

	?>

	 <tr class="form-field">
		<th scope="row" valign="top"><label><?php esc_html_e('Color', 'homey' ); ?></label></th>
			<td>
				<label><input type="radio" name="homey[color_type]" value="inherit" class="homey-radio color-type" <?php checked( $homey_meta['color_type'], 'inherit' );?>> <?php esc_html_e('Inherit from default accent color', 'homey' ); ?></label> <br/>
				<label><input type="radio" name="homey[color_type]" value="custom" class="homey-radio color-type" <?php checked( $homey_meta['color_type'], 'custom' );?>> <?php esc_html_e('Custom', 'homey' ); ?></label>
			  <div id="homey_color_wrap">
			  <p>
			    	<input name="homey[color]" type="text" class="homey_colorpicker" value="<?php echo esc_attr($homey_meta['color']); ?>" data-default-color="<?php echo esc_attr($homey_meta['color']); ?>"/>
			  </p>
			  <?php if ( !empty( $colors ) ) { echo ''.$colors; } ?>
				</div>
				<div class="clear"></div>
				<p class="howto"><?php esc_html_e('Choose color', 'homey' ); ?></p>
			</td>
		</tr>

	<?php
	}
endif;

add_action( 'listing_type_edit_form_fields', 'homey_listing_type_edit_meta_fields', 10, 2 );


if ( !function_exists( 'homey_save_listing_type_meta_fields' ) ) :
	function homey_save_listing_type_meta_fields( $term_id ) {

		if ( isset( $_POST['homey'] ) ) {

			$homey_meta = array();

			$homey_meta['color'] = isset( $_POST['homey']['color'] ) ? $_POST['homey']['color'] : 0;
			$homey_meta['color_type'] = isset( $_POST['homey']['color_type'] ) ? $_POST['homey']['color_type'] : 0;

			update_option( '_homey_listing_type_'.$term_id, $homey_meta );

			if ( $homey_meta['color_type'] == 'custom' ) {
				homey_update_recent_colors( $homey_meta['color'] );
			}

			homey_update_listing_type_colors( $term_id, $homey_meta['color'], $homey_meta['color_type'] );
		}

	}
endif;

add_action( 'edited_listing_type', 'homey_save_listing_type_meta_fields', 10, 2 );
add_action( 'create_listing_type', 'homey_save_listing_type_meta_fields', 10, 2 );

?>