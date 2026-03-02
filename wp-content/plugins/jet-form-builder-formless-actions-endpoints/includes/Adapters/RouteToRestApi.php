<?php

namespace JFB_Formless\Adapters;

use JFB_Formless\Services\RouteMeta;

class RouteToRestApi {

	/**
	 * @var RouteMeta
	 */
	private $meta;

	public function generate_api_row(): \Generator {
		yield 'id' => $this->get_meta()->get_route()->get_id();
		yield 'form_id' => $this->get_meta()->get_route()->get_form_id();

		if ( ! is_null( $this->get_meta()->get_route()->get_action_type() ) ) {
			yield 'action_type' => $this->get_meta()->get_route()->get_action_type();
		}
		if ( ! is_null( $this->get_meta()->get_route()->get_restriction_type() ) ) {
			yield 'restriction_type' => $this->get_meta()->get_route()->get_restriction_type();
		}
		if ( ! is_null( $this->get_meta()->get_route()->get_restriction_cap() ) ) {
			yield 'restriction_cap' => $this->get_meta()->get_route()->get_restriction_cap();
		}
		if ( ! is_null( $this->get_meta()->get_route()->get_restriction_roles() ) ) {
			yield 'restriction_roles' => $this->get_meta()->get_route()->get_restriction_roles();
		}
		if ( ! is_null( $this->get_meta()->get_route()->is_log() ) ) {
			yield 'log' => $this->get_meta()->get_route()->is_log();
		}
		if ( ! is_null( $this->get_meta()->get_route()->is_restricted() ) ) {
			yield 'restricted' => $this->get_meta()->get_route()->is_restricted();
		}

		if ( $this->get_meta()->get_properties() ) {
			yield 'meta' => $this->get_meta()->get_properties();
		}
	}


	/**
	 * @return RouteMeta
	 */
	public function get_meta(): RouteMeta {
		return $this->meta;
	}

	/**
	 * @param RouteMeta $meta
	 */
	public function set_meta( RouteMeta $meta ) {
		$this->meta = $meta;
	}

}
