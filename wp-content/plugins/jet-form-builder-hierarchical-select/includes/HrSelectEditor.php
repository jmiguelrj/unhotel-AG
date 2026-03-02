<?php


namespace Jet_FB_HR_Select;

class HrSelectEditor {

	public static function get_raw_taxonomies() {
		$taxonomies = get_taxonomies(
			apply_filters( 'jet-form-builder/hr-select/taxonomies-list', array( 'hierarchical' => true ) ),
			'objects'
		);
		$prepared   = array_map(
			function ( $taxonomy ) {
				return array(
					'value' => $taxonomy->name,
					'label' => $taxonomy->label,
				);
			},
			array_values( $taxonomies )
		);

		return array_merge(
			array(
				array(
					'value' => '',
					'label' => '--',
				),
			),
			$prepared
		);
	}

	public static function get_taxonomies() {
		$taxonomies = self::get_raw_taxonomies();
		$multiple   = array();

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! $taxonomy['value'] ) {
				continue;
			}
			if ( ! isset( $multiple[ $taxonomy['label'] ] ) ) {
				$multiple[ $taxonomy['label'] ] = 0;
			}
			$multiple[ $taxonomy['label'] ] ++;
		}

		foreach ( $taxonomies as $index => $taxonomy ) {
			if ( ! $taxonomy['value'] ) {
				continue;
			}
			$count = $multiple[ $taxonomy['label'] ] ?? 0;

			if ( 1 >= $count ) {
				continue;
			}

			$taxonomies[ $index ]['label'] .= " ({$taxonomy['value']})";
		}

		return $taxonomies;
	}

	public static function prepare_placeholder( $label ) {
		return array(
			'value' => '',
			'label' => $label,
		);
	}

}
