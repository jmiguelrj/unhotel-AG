<?php

namespace Jet_FB_HR_Select\JetFormBuilder\Blocks;

// If this file is called directly, abort.
use Jet_FB_HR_Select\BaseHrSelectField;
use Jet_FB_HR_Select\Exceptions\HrSelectException;
use Jet_FB_HR_Select\Plugin;
use Jet_Form_Builder\Blocks\Types\Base;
use JetHRSelectCore\JetFormBuilder\SmartBaseBlock;
use JetHRSelectCore\BlockEditorData;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Text field block class
 */
class HrSelect extends Base {

	use SmartBaseBlock;
	use BlockEditorData;
	use BaseHrSelectField;

	/** @var \WP_Term[] */
	protected $tree = array();
	protected $parent_id = 0;

	/**
	 * Returns block name
	 *
	 * @return [type] [description]
	 */
	public function get_name() {
		return 'hr-select';
	}

	public function render_instance() {
		return new HrSelectRender( $this );
	}

	public function set_preset() {
		$main_default = array_filter( $this->get_default_for_levels( true ) );

		if ( ! empty( $main_default ) ) {
			$this->block_attrs['default'] = $main_default;

			return;
		}

		$default = array_filter( $this->get_default_for_levels() );

		$this->block_attrs['default'] = $default;
	}

	public function get_default_for_levels( $main = false ) {
		$default = array();
		$levels  = $this->block_attrs['levels'] ?? array();

		foreach ( $levels as $index => $level ) {
			try {
				$this->maybe_set_tree( $this->get_level_name( $main, $level ) );

				$default[] = $this->get_tree_item_value( $index );

			} catch ( HrSelectException $exception ) {
				$default[] = $exception->getMessage();
			}
		}

		return $default;
	}

	protected function get_level_name( $main = false, $level = array() ): string {
		return $main ? ( $this->block_attrs['name'] ?? '' ) : ( $level['name'] ?? '' );
	}

	public function expected_preset_type(): array {
		return array( 'custom' );
	}

	public function get_path_metadata_block() {
		$path_parts = array( 'assets', 'src', 'jet-form-builder', $this->get_name() );
		$path       = implode( DIRECTORY_SEPARATOR, $path_parts );

		return Plugin::instance()->plugin_dir( $path );
	}

	/**
	 * @param $level_name
	 *
	 * @return $this
	 * @throws HrSelectException
	 */
	protected function maybe_set_tree( $level_name ): HrSelect {
		if ( ! $this->tree ) {
			$this->tree = $this->get_tree( $level_name );
		}

		return $this;
	}

	/**
	 * @param $level_name
	 *
	 * @return array
	 * @throws HrSelectException
	 */
	protected function get_tree( $level_name ): array {
		$level_attrs = array_merge( $this->block_attrs, array( 'name' => $level_name ) );
		$tree        = $this->get_default_from_preset( $level_attrs );

		if ( is_array( $tree ) && is_a( $tree[0] ?? false, \WP_Term::class ) ) {
			return $tree;
		}

		throw new HrSelectException( $tree[0] ?? $tree );
	}


	/**
	 * @return bool|mixed
	 */
	protected function get_tree_item_value( $level_index ) {
		if ( ! is_array( $this->tree ) ) {
			return false;
		}

		foreach ( $this->tree as $term ) {
			if ( $term->parent === $this->parent_id ) {
				$this->parent_id = $term->term_id;

				return $term->term_id;
			}
		}

		return false;
	}

}
