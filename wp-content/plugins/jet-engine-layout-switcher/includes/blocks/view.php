<?php
namespace Jet_Engine_Layout_Switcher\Blocks;

class View extends \Jet_Engine_Layout_Switcher\Base\View {

	public function get_id() {
		return 'blocks';
	}

	public function is_edit_mode() {
		return ( isset( $_GET['context'] ) && 'edit' === $_GET['context'] && isset( $_GET['attributes'] ) && $_GET['_locale'] );
	}

	public function get_uniq_wrap_selector( $element_id ) {
		return '.jet-listing-grid--blocks[data-element-id="' . $element_id . '"]';
	}

	public function get_active_breakpoints() {
		return array(
			'tablet' => array(
				'direction' => 'max',
				'value'     => 1024,
			),
			'mobile' => array(
				'direction' => 'max',
				'value'     => 767,
			),
		);
	}

	public function get_listing_settings_by_id( $element_id ) {

		if ( empty( $element_id ) ) {
			return array();
		}

		$post_id = apply_filters( 'jet-engine-layout-switcher/current-post-id', get_the_ID() );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return array();
		}

		$blocks = parse_blocks( $post->post_content );
		$attrs  = $this->recursive_find_block( $blocks, $element_id );

		if ( empty( $attrs ) ) {
			return array();
		}

		$defaults = array(
			'lisitng_id'       => '',
			'columns'          => 3,
			'columns_tablet'   => 3,
			'columns_mobile'   => 1,
			'column_min_width' => 240,
		);

		return array_merge( $defaults, $attrs );
	}

	public function recursive_find_block( $blocks = array(), $element_id = null ) {

		if ( empty( $blocks ) ) {
			return false;
		}

		foreach ( $blocks as $block ) {
			if ( ! empty( $block['attrs']['_block_id'] ) && $element_id === $block['attrs']['_block_id'] ) {
				return $block['attrs'];
			} elseif ( ! empty( $block['innerBlocks'] ) ) {
				$attrs = $this->recursive_find_block( $block['innerBlocks'], $element_id );

				if ( $attrs ) {
					return $attrs;
				}
			}
		}

		return false;
	}

	public function find_relevant_switcher_on_page( $grid_block_id ) {

		$post_id = apply_filters( 'jet-engine-layout-switcher/current-post-id', get_the_ID() );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		$blocks = parse_blocks( $post->post_content );

		return $this->find_switcher_block_by_grid_id( $blocks, $grid_block_id );
	}

	public function find_switcher_block_by_grid_id( $blocks, $grid_block_id ) {

		if ( empty( $blocks ) ) {
			return false;
		}

		foreach ( $blocks as $block ) {

			if ( ! empty( $block['blockName'] )
				 && 'jet-engine/layout-switcher' === $block['blockName']
				 && ! empty( $block['attrs'] )
				 && ! empty( $block['attrs']['widget_id'] )
				 && $grid_block_id === $block['attrs']['widget_id']
			) {
				return $block['attrs'];
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$result = $this->find_switcher_block_by_grid_id( $block['innerBlocks'], $grid_block_id );

				if ( ! empty( $result ) ) {
					return $result;
				}
			}
		}

		return false;
	}

}
