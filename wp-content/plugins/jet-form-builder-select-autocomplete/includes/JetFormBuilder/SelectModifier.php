<?php


namespace JFB\SelectAutocomplete\JetFormBuilder;


use Jet_Form_Builder\Classes\Tools;
use JFB\SelectAutocomplete\BaseSelectModifier;
use JFB\SelectAutocomplete\Plugin;
use Jet_Form_Builder\Blocks\Types\Base;
use Jet_Form_Builder\Blocks\Types\Repeater_Row;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use JFB\SelectAutocomplete\Vendor\JFBCore\JetFormBuilder\BaseFieldModifier;
use JFB_Modules\Option_Field\Module;

class SelectModifier extends BaseFieldModifier {

	use BaseSelectModifier;

	public function type(): string {
		return 'select-field';
	}

	public function on_plugin_init() {
		parent::on_plugin_init();

		add_action(
			'jet-form-builder/after-start-form-row',
			array( $this, 'prevent_query_field_options_and_default' ),
			9
		);
	}

	public function blockAttributes( $args ): array {
		$args['attributes']['autocomplete_enable']             = array(
			'type'    => 'boolean',
			'default' => '',
		);
		$args['attributes']['autocomplete_via_ajax']           = array(
			'type'    => 'boolean',
			'default' => '',
		);
		$args['attributes']['autocomplete_minimumInputLength'] = array(
			'type'    => 'number',
			'default' => '',
		);
		$args['attributes']['autocompleteMessages']            = array(
			'type'    => 'object',
			'default' => array(),
		);

		return $args;
	}

	public function enqueueFrontendAssets() {
		$args = $this->getArgs();

		if ( ! empty( $args['autocompleteMessages']['enabled'] ) ) {
			unset( $args['autocompleteMessages']['enabled'] );

			$this->getClass()->add_attribute(
				'data-i18n',
				Tools::encode_json( $args['autocompleteMessages'] )
			);
		}

		// jfb <= 3.0.0
		if ( ! class_exists( '\Jet_Form_Builder\Blocks\Validation' ) ) {
			wp_enqueue_script(
				Plugin::SLUG,
				JET_FB_SELECT_AUTOCOMPLETE_URL . 'assets/js/builder.frontend.js',
				array(),
				JET_FB_SELECT_AUTOCOMPLETE_VERSION,
				true
			);

			return;
		}

		$script_asset = require_once JET_FB_SELECT_AUTOCOMPLETE_PATH . 'assets/js/builder.frontend.v3.asset.php';

		if ( true === $script_asset ) {
			return;
		}

		array_push(
			$script_asset['dependencies'],
			'jet-form-builder-frontend-forms'
		);

		wp_enqueue_script(
			Plugin::SLUG,
			JET_FB_SELECT_AUTOCOMPLETE_URL . 'assets/js/builder.frontend.v3.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

	}

	public function editorAssets() {
		$script_asset = require_once JET_FB_SELECT_AUTOCOMPLETE_PATH . 'assets/js/builder.editor.asset.php';

		wp_enqueue_script(
			Plugin::SLUG,
			JET_FB_SELECT_AUTOCOMPLETE_URL . 'assets/js/builder.editor.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Select Autocomplete</b> needs <b>JetFormBuilder</b> update.',
			'jet-form-builder-select-autocomplete'
		) );
	}

	public function on_base_need_install() {
	}

	/**
	 * Remove querying field option for select field,
	 * if it using loading options by ajax.
	 *
	 * Also this method clear the default value
	 *
	 * Works only with JetFormBuilder >= 3.3.1
	 *
	 * @param Base $block
	 *
	 * @throws Repository_Exception
	 */
	public function prevent_query_field_options_and_default( Base $block ) {
		if (
			! class_exists( '\JFB_Modules\Option_Field\Module' ) ||
			$this->type() !== $block->get_name() ||
			empty( $block->block_attrs['autocomplete_enable'] ) ||
			empty( $block->block_attrs['autocomplete_via_ajax'] )
		) {
			return;
		}
		// clear preset
		$block->block_attrs['default'] = '';

		// clear manual options
		$block->block_attrs['field_options'] = array();

		/**
		 * Prevent get default value from preset row
		 *
		 * @see \Jet_Form_Builder\Blocks\Types\Base::get_default_from_preset
		 */
		$block->block_context[ Repeater_Row::CONTEXT_INDEX ] = - 1;

		/** @var Module $module */
		$module = jet_form_builder()->module( 'option-field' );

		remove_action(
			'jet-form-builder/after-start-form-row',
			array( $module, 'apply_field_options' )
		);

		// return removed action
		add_action(
			'jet-form-builder/before-end-form-row',
			array( $this, 'return_apply_field_options_hook' )
		);
	}

	/**
	 * @param Base $block
	 *
	 * @throws Repository_Exception
	 */
	public function return_apply_field_options_hook( Base $block ) {
		// prevent repeating this action
		remove_action(
			'jet-form-builder/before-end-form-row',
			array( $this, 'return_apply_field_options_hook' )
		);

		/** @var Module $module */
		$module = jet_form_builder()->module( 'option-field' );

		add_action(
			'jet-form-builder/after-start-form-row',
			array( $module, 'apply_field_options' )
		);
	}
}