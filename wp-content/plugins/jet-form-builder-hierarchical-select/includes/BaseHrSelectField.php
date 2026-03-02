<?php


namespace Jet_FB_HR_Select;

trait BaseHrSelectField {

	public $selected_by_default = false;
	public $parent_term = 0;
	protected $current_level = array();
	protected $terms_with_levels = array();
	protected $computed_level = 0;
	protected $sorted_terms_by_levels = array();
	protected $all_terms;
	protected $all_terms_with_levels = array();

	public function if_empty_child() {
		return $this->block_attrs['if_empty_child'] ?? '';
	}

	public function is_meta_term_value() {
		return ( 'meta' === $this->get_term_value_key() && $this->get_meta_term_name() );
	}

	public function get_meta_term_name() {
		return $this->block_attrs['term_meta_value'] ?? '';
	}

	public function get_term_value_key() {
		$default = 'term_id';

		return ( $this->block_attrs['term_value'] ?? $default ) ?: $default;
	}

	public function get_calculate_meta_key() {
		return ( $this->block_attrs['calc_from_meta'] ?? false )
			? $this->block_attrs['term_calc_value'] ?? ''
			: '';
	}

	public function get_query_params( $query_args = array() ) {
		$query_params = array(
			'taxonomy'   => $this->block_attrs['parent_taxonomy'] ?? '',
			'hide_empty' => false,
			'parent'     => 0,
			'orderby'    => 'parent',
		);

		if ( ! $this->is_meta_term_value() ) {
			return array_merge( $query_params, $query_args );
		}

		$query_params['meta_key']   = $this->get_meta_term_name();
		$query_params['meta_query'] = array(
			array(
				'value'   => '',
				'compare' => '!=',
			),
		);

		return array_merge( $query_params, $query_args );
	}

	public function get_term_value( $WP_term ) {
		return $this->is_meta_term_value()
			? get_term_meta( $WP_term->term_id, $this->get_meta_term_name(), true )
			: $WP_term->data->{$this->get_term_value_key()};
	}

	public function prepare_terms_attributes( $terms ) {
		$result = array();

		foreach ( $terms as $WP_term ) {
			$value = $this->get_term_value( $WP_term );

			$params = array(
				'data-term-id' => $WP_term->term_id,
				'label'        => $WP_term->name,
				'parent'       => $WP_term->parent,
				'value'        => $value,
			);

			$default = $this->block_attrs['default'] ?? array();

			if ( $default && in_array( $WP_term->term_id, $default, true ) ) {
				$params['selected'] = 'selected';
			}

			$calculate = $this->get_calculate_meta_key();

			if ( $calculate ) {
				$params['data-calculate'] = floatval( get_term_meta( $WP_term->term_id, $calculate, true ) );
			}

			$result[ $WP_term->term_id ] = $params;
		}

		return $result;

	}

	public function attach_levels() {
		foreach ( $this->all_terms as $term_id => $parent_id ) {
			$this->computed_level = - 1;

			$this->compute_term_level( $term_id );

			$this->all_terms[ $term_id ]['data-term-level'] = $this->computed_level;
		}
	}

	protected function compute_term_level( $current_term ) {
		$next_parent = $this->all_terms[ $current_term ]['parent'] ?? false;

		if ( false !== $next_parent ) {
			$this->computed_level ++;
			$this->compute_term_level( $next_parent );
		}
	}

	public function query_terms( $query_args = array() ): array {
		$query_params = apply_filters( 'jet-form-builder/hr-select/query-terms-params', $this->get_query_params( $query_args ), $this );
		$terms        = get_terms( $query_params );

		if ( ! $terms || is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return array();
		}

		return apply_filters(
			'jet-form-builder/hr-select/prepare-terms',
			$this->prepare_terms_attributes( $terms ),
			$this
		);
	}

	public function query_terms_save( $query_args = array() ): array {
		if ( empty( $this->all_terms ) ) {
			$this->all_terms = $this->query_terms( $query_args );
		}

		return $this->all_terms;
	}

	public function group_by_level( $terms ): array {
		$result = array();

		foreach ( $terms as $term ) {
			$level = $term['data-term-level'] ?? false;

			if ( false === $level ) {
				continue;
			}

			$result[ $level ][ $term['data-term-id'] ?? 0 ] = $term;
		}

		return $result;
	}

	public function query_terms_with_levels( $group ): array {
		if ( ! empty( $this->all_terms_with_levels ) ) {
			return $this->all_terms_with_levels;
		}

		$this->query_terms_save(
			array(
				'parent' => '',
			)
		);
		$this->attach_levels();

		$this->all_terms_with_levels = $group
			? $this->group_by_level( $this->all_terms )
			: $this->all_terms;

		return $this->all_terms_with_levels;

	}

	public function maybe_get_placeholder( $level = 0 ) {
		$level       = $this->block_attrs['levels'][ $level ] ?? array();
		$placeholder = $level['placeholder'] ?? '';

		if ( ! $placeholder ) {
			return array();
		}

		return HrSelectEditor::prepare_placeholder( $placeholder );
	}

	public function with_placeholder( $terms, $level = 0 ): array {

		$value = false;
		foreach ( $terms as $term ) {
			if ( isset( $term['data-term-id'] ) ) {
				$value = true;

				break;
			}
		}

		if ( false !== $value ) {
			return array_merge(
				array_filter(
					array( $this->maybe_get_placeholder( $level ) )
				),
				$terms
			);
		}

		$result = array();

		foreach ( $terms as $current_level => $terms_on_level ) {
			if ( ! is_array( $level ) || in_array( $current_level, $level, true ) ) {
				$result[ $current_level ] = $this->with_placeholder( $terms_on_level, $current_level );
			}
		}

		return $result;
	}


	/**
	 * Returns current block render instance
	 *
	 * @return string
	 */
	public function get_template() {
		if ( empty( $this->block_attrs['levels'] ) || empty( $this->block_attrs['levels'][0] ) ) {
			return '';
		}

		foreach ( $this->block_attrs['levels'] as $current => $level ) {
			$this->current_level = $current;

			$this->preset_options();
		}

		return $this->render_instance()->set_up()->complete_render();
	}


	protected function preset_options() {
		if ( false === $this->parent_term ) {
			$this->set_current_level( '_initial_text', true );

			return;
		}

		$terms = $this->query_terms(
			array( 'parent' => $this->parent_term )
		);

		if ( ! $terms ) {
			$this->set_current_level( '_initial_text', true );
			$this->parent_term = false;

			return;
		} else {
			$this->set_current_level(
				'options',
				$this->with_placeholder( $terms, $this->current_level )
			);
		}

		$selected_parent = false;

		foreach ( $this->get_current_level( 'options' ) as $index => $term_option ) {
			$selected = $term_option['selected'] ?? false;

			if ( $selected ) {
				$selected_parent = $term_option['data-term-id'] ?? false;
			}
		}

		$this->parent_term = $selected_parent;
	}

	protected function set_current_level( $key, $value ) {
		$this->block_attrs['levels'][ $this->current_level ][ $key ] = $value;

		return $this;
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function get_current_level( $key ) {
		return $this->block_attrs['levels'][ $this->current_level ][ $key ] ?? false;
	}

	public function editor_data(): array {
		return array(
			'taxonomies'   => HrSelectEditor::get_taxonomies(),
			'term_value'   => array(
				array(
					'value' => '',
					'label' => '--',
				),
				array(
					'value' => 'term_id',
					'label' => __( 'Term ID', 'jet-form-builder-hr-select' ),
				),
				array(
					'value' => 'name',
					'label' => __( 'Term Name', 'jet-form-builder-hr-select' ),
				),
				array(
					'value' => 'slug',
					'label' => __( 'Term Slug', 'jet-form-builder-hr-select' ),
				),
				array(
					'value' => 'meta',
					'label' => __( 'Term Meta', 'jet-form-builder-hr-select' ),
				),
			),
			'default_item' => array(
				'display_input' => false,
				'label'         => '',
				'name'          => '',
				'placeholder'   => '',
				'desc'          => '',
			),
			'attach_to'    => array(
				array(
					'value' => '',
					'label' => __( 'Current Post', 'jet-form-builder-hr-select' ),
				),
				array(
					'value' => 'from_preset',
					'label' => __( 'Queried Post from preset', 'jet-form-builder-hr-select' ),
				),
			),
			'if_empty_child'    => array(
				array(
					'value' => '',
					'label' => __( 'Show all children levels', 'jet-form-builder-hr-select' ),
				),
				array(
					'value' => 'hide',
					'label' => __( 'Hide all children levels until parent level selected', 'jet-form-builder-hr-select' ),
				),
				array(
					'value' => 'disable',
					'label' => __( 'Disable all children levels until parent level selected', 'jet-form-builder-hr-select' ),
				),
			),
		);
	}

	public function editor_labels(): array {
		return array(
			'parent_taxonomy'      => __( 'Taxonomy type', 'jet-form-builder-hr-select' ),
			'term_value'           => __( 'Term value from', 'jet-form-builder-hr-select' ),
			'term_meta_value'      => __( 'Term\'s meta field key/ID', 'jet-form-builder-hr-select' ),
			'calc_from_meta'       => __( 'Get calculated value from term meta', 'jet-form-builder-hr-select' ),
			'term_calc_value'      => __( 'Term\'s meta field key/ID for calculated value', 'jet-form-builder-hr-select' ),
			'modal'                => __( 'Edit Levels', 'jet-form-builder-hr-select' ),
			'add_new_level'        => __( '+ Add new level', 'jet-form-builder-hr-select' ),
			'level_name'           => __( 'Name', 'jet-form-builder-hr-select' ),
			'level_label'          => __( 'Label', 'jet-form-builder-hr-select' ),
			'level_placeholder'    => __( 'Placeholder', 'jet-form-builder-hr-select' ),
			'level_description'    => __( 'Description', 'jet-form-builder-hr-select' ),
			'level_display_input'  => __( 'Enable manual input', 'jet-form-builder-hr-select' ),
			'warning_if_empty'     => __( 'Please add at least one level', 'jet-form-builder-hr-select' ),
			'manage_levels_button' => __( 'Manage Levels', 'jet-form-builder-hr-select' ),
			'attach_to'            => __( 'Attach new terms to the', 'jet-form-builder-hr-select' ),
			'if_empty_child'       => __( 'Access to child levels', 'jet-form-builder-hr-select' ),
		);
	}

	public function editor_help(): array {
		return array(
			'term_calc_value'     => __( 'Calculations are possible for numerical values only.', 'jet-form-builder-hr-select' ),
			'level_display_input' => __(
				"Works only if the term from the previous select field doesn't have any child terms",
				'jet-form-builder-hr-select'
			),
		);
	}

}
