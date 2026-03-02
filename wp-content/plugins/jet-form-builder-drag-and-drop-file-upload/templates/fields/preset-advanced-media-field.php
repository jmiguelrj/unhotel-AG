<?php
/**
 * Preset Advanced Media field template
 *
 * Used to display preset file values in the form.
 *
 * @var \JFB_Advanced_Media\Blocks\Advanced_Media_Field_Render $this
 * @var array $file
 */

use Jet_Form_Builder\Classes\Tools;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<input class="jet-form-builder-advanced-media__preset-value"
		type="hidden"
		name="<?php echo esc_attr( $this->block_type->get_field_name() ); ?>"
		value="<?php echo esc_attr( Tools::encode_json( $file ) ); ?>" />