<?php

namespace JFB_Formless\Modules\Routes\REST\Endpoints;

use Jet_Form_Builder\Blocks\Block_Helper;
use JFB_Modules\Block_Parsers\Field_Data_Parser;
use JFB_Formless\REST\Interfaces\EndpointInterface;

class GetAllFields implements EndpointInterface {

	public function get_method(): string {
		return \WP_REST_Server::READABLE;
	}

	public function get_args(): array {
		return array(
			'id'             => array(
				'type'     => 'integer',
				'required' => true,
			),
			/**
			 * This parameter doesn't exist in base route
			 */
			'include_secure' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'include_attrs'  => array(
				'type'    => 'boolean',
				'default' => false,
			),
		);
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function process( \WP_REST_Request $request ): \WP_REST_Response {
		$form_id = $request->get_param( 'id' );
		$form    = get_post( $form_id );

		if ( absint( $form->post_author ) !== get_current_user_id()
			&& ! current_user_can( 'edit_post', $form->ID )
		) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Not allowed', 'jet-form-builder' ),
				),
				403
			);
		}

		jet_fb_context()->set_parsers(
			Block_Helper::get_blocks_by_post( $form )
		);

		$fields = iterator_to_array( $this->generate_fields( $request ) );

		if ( empty( $fields ) ) {
			return new \WP_REST_Response(
				array(
					'code'    => 'not_found',
					'message' => __( 'Not founded fields', 'jet-form-builder' ),
				),
				404
			);
		}

		return new \WP_REST_Response(
			array(
				'fields' => $fields,
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return void
	 */
	protected function generate_fields( \WP_REST_Request $request ): \Generator {
		foreach ( jet_fb_context()->iterate_parsers() as $name => $parser ) {
			yield iterator_to_array( $this->generate_field( $request, $parser ) );
		}
	}

	protected function generate_field( \WP_REST_Request $request, Field_Data_Parser $parser ): \Generator {
		$include_secure = $request->get_param( 'include_secure' );
		$include_attrs  = $request->get_param( 'include_attrs' );

		if ( ! $include_secure && $parser->is_secure() ) {
			return;
		}

		yield 'value' => $parser->get_name();
		yield 'label' => $parser->get_label();
		yield 'type' => $parser->get_type();

		if ( $include_attrs ) {
			yield 'attrs' => $parser->get_settings();
		}
	}

}
