<?php
namespace Jet_Popup\Compatibility;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Jet_Style_Manager {

	/**
	 *
	 */
	public function __construct() {

		if ( ! defined( 'JET_SM_VERSION' ) ) {
			return false;
		}

		add_filter( 'jet-popup/export-import/export-data', array( $this, 'modify_export_data' ), 10, 3 );
		add_filter( 'jet-popup/export-import/new-popup-args', array( $this, 'modify_new_popup_args' ), 10, 3 );
	}

	/**
	 * @param $export_data
	 * @param $popup_id
	 * @return mixed
	 */
	public function modify_export_data( $export_data, $popup_id ) {
		$meta_sm_ready_style = get_post_meta( $popup_id, '_jet_sm_ready_style', true );

		if ( ! empty( $meta_sm_ready_style ) ) {
			$export_data['_jet_sm_ready_style'] = $meta_sm_ready_style;
		}

		$meta_sm_style = get_post_meta( $popup_id, '_jet_sm_style', true );

		if ( ! empty( $meta_sm_style ) ) {
			$export_data['_jet_sm_style'] = $meta_sm_style;
		}

		$meta_sm_controls_values = get_post_meta( $popup_id, '_jet_sm_controls_values', true );

		if ( ! empty( $meta_sm_controls_values ) ) {
			$export_data['_jet_sm_controls_values'] = $meta_sm_controls_values;
		}

		return $export_data;
	}

	/**
	 * @param $new_popup_args
	 * @return mixed
	 */
	public function modify_new_popup_args( $new_popup_args, $import_data ) {

		if ( isset( $import_data['_jet_sm_ready_style'] ) ) {
			$new_popup_args['meta_input']['_jet_sm_ready_style'] = $import_data['_jet_sm_ready_style'];
		}

		if ( isset( $import_data['_jet_sm_style'] ) ) {
			$new_popup_args['meta_input']['_jet_sm_style'] = $import_data['_jet_sm_style'];
		}

		if ( isset( $import_data['_jet_sm_controls_values'] ) ) {
			$new_popup_args['meta_input']['_jet_sm_controls_values'] = $import_data['_jet_sm_controls_values'];
		}

		return $new_popup_args;
	}

}
