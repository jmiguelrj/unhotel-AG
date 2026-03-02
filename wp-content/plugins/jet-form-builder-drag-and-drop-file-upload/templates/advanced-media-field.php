<?php
/**
 * Advanced Media field template
 *
 * @var \JFB_Advanced_Media\Blocks\Advanced_Media_Field_Render $this
 * @var \Jet_Form_Builder\Classes\Attributes_Trait $files
 */

use Jet_Form_Builder\File_Upload;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$max_files = absint( $args['max_files'] ?? 1 );
$max_files = ( $max_files <= 0 ) ? 1 : $max_files;

$this->add_attribute( 'class', 'jet-form-builder__field advanced-media-field jet-form-builder-advanced-media__input' );
$this->add_attribute( 'class', $args['class_name'] );
$this->add_attribute( 'name', $this->block_type->get_field_name( $args['name'] ) );
$this->add_attribute( 'data-field-name', $args['name'] );
$this->add_attribute( 'type', 'file' );
$this->add_attribute( 'data-form_id', $this->form_id );
$this->add_attribute( 'id', $this->block_type->get_field_id( $args ) );
$this->add_attribute( 'required', $this->block_type->get_required_val() );
$this->add_attribute( 'data-jfb-sync' );
$this->add_attribute( 'data-upload_mode', $args['upload_mode'] ?? 'on_submit' );

$allowed_user_cap = $this->block_type->block_attrs['allowed_user_cap'] ?? 'manage_options';
$this->add_attribute( 'data-allowed_user_cap', $allowed_user_cap );

// Add insert_attachment and save_uploaded_file attributes
$insert_attachment = $this->block_type->block_attrs['insert_attachment'] ?? false;
$this->add_attribute( 'data-insert_attachment', $insert_attachment ? '1' : '0' );

$save_uploaded_file = $this->block_type->block_attrs['save_uploaded_file'] ?? true;
$this->add_attribute( 'data-save_uploaded_file', $save_uploaded_file ? '1' : '0' );

if ( 1 < $max_files ) {
	$this->add_attribute( 'data-max_files', $max_files );
	$this->add_attribute( 'multiple', true );
}

if ( ! empty( $args['allowed_mimes'] ) ) {
	$this->add_attribute( 'accept', implode( ',', $args['allowed_mimes'] ) );
}

$max_size         = $this->block_type->get_max_size();
$max_image_width  = $this->block_type->block_attrs['max_image_width'] ?? '';
$max_image_height = $this->block_type->block_attrs['max_image_height'] ?? '';
$image_quality    = $this->block_type->block_attrs['image_quality'] ?? 100;

// Add image-specific data attributes
$this->add_attribute( 'data-max_image_width', $max_image_width );
$this->add_attribute( 'data-max_image_height', $max_image_height );
$this->add_attribute( 'data-image_quality', $image_quality );

$has_drag_n_drop    = $this->block_type->block_attrs['drag_n_drop'] ?? true;
$has_library        = $this->block_type->block_attrs['wp_media_library'] ?? false;
$save_uploaded_file = $this->block_type->block_attrs['save_uploaded_file'] ?? true;

// Check if user is logged in - media library features require authentication
$is_user_logged_in = is_user_logged_in();
$has_library       = $has_library && $is_user_logged_in;

// Check if at least one upload method is enabled
$has_upload_method = $has_drag_n_drop || $has_library;

// If no upload method is enabled, show warning and don't render the field
if ( ! $has_upload_method ) {
	?>
	<div class="jet-form-builder__field-wrap jet-form-builder-advanced-media">
		<div class="jet-form-builder-advanced-media__error">
			<p>
				<strong><?php esc_html_e( 'Configuration Error:', 'jet-form-builder-advanced-media' ); ?></strong><br>
				<?php esc_html_e( 'Please enable at least one upload method: Drag and Drop UI or WP Media Library.', 'jet-form-builder-advanced-media' ); ?>
			</p>
		</div>
	</div>
	<?php
	return;
}

$this->add_attribute( 'data-max_size', $max_size );
$max_size_message = $this->block_type->get_max_size_message();
$format_message   = $this->block_type->get_format_message();

$wp_media_library_label   = $this->block_type->block_attrs['wp_media_library_label'] ?? '';
$drag_n_drop_label        = $this->block_type->block_attrs['drag_n_drop_label'] ?? '';
$select_media_files_label = $this->block_type->block_attrs['select_media_files_label'] ?? '';
$drag_n_drop_separator    = $this->block_type->block_attrs['drag_n_drop_separator'] ?? '';

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<div class="jet-form-builder__field-wrap jet-form-builder-advanced-media">
	<?php do_action( 'jet-form-builder-advanced-media/before-field', $this ); ?>
	<div class="jet-form-builder-advanced-media__content">
		<?php if ( $has_drag_n_drop ) { ?>
			<div class="jet-form-builder-advanced-media__dropzone" data-jfb-dropzone>
				<span class="jet-form-builder-advanced-media__placeholder"><?php echo esc_html( $drag_n_drop_label ); ?></span>
				<span><?php echo esc_html( $drag_n_drop_separator ); ?></span>
				<button type="button" class="jet-form-builder-advanced-media__browse"><?php echo esc_html( $select_media_files_label ); ?></button>
				<div class="jet-form-builder-advanced-media__message">
					<small><?php echo wp_kses_post( $max_size_message ); ?></small>
					<?php if ( ! empty( $args['allowed_mimes'] ) ) { ?>
						<small><?php echo wp_kses_post( $format_message ); ?></small>
					<?php } ?>
				</div>
				<span class="jet-form-builder-advanced-media__limit"><span class="jet-form-builder-advanced-media__current">0</span>/<?php echo esc_html( $max_files ); ?></span>
			</div>
		<?php } ?>
		<?php
			$data_args = htmlspecialchars( Jet_Form_Builder\Classes\Tools::encode_json( array(
				'max_files'        => $max_files,
				'max_size'         => $max_size,
				'max_image_width'  => $max_image_width,
				'max_image_height' => $max_image_height,
				'image_quality'    => $image_quality,
			) ) );
		?>
		<div class="jet-form-builder-advanced-media__files" data-args="<?php echo esc_attr( $data_args ); ?>" <?php $this->files()->render_attributes_string(); ?> style="display: none;">
			<?php echo $this->render_previews(); ?>
		</div>
		<?php if ( $has_library ) { ?>
		<button type="button" class="jet-form-builder-advanced-media__library-button">
			<?php echo esc_html( $wp_media_library_label ); ?>
		</button>
	<?php } ?>
	</div>
	<div class="jet-form-builder-advanced-media__fields">
		<input <?php $this->render_attributes_string(); ?>>
		<?php if ( false === $save_uploaded_file ) { ?>
			<input type="hidden" class="jet-form-builder-advanced-media__value" name="<?php echo esc_attr( $this->block_type->get_field_name( $args['name'] ) ); ?>" data-field-name="<?php echo esc_attr( $args['name'] ); ?>" data-jfb-advanced-media-b64="<?php echo esc_attr( $this->block_type->get_field_name( $args['name'] ) ); ?>" value="">
		<?php } ?>
	</div>
	<?php do_action( 'jet-form-builder-advanced-media/after-field', $this ); ?>
</div>

<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
