<?php


namespace JFB\LimitResponses\RestrictTypes;

use JFB\LimitResponses\Counters\SyncedCycleCounter;
use JFB\LimitResponses\LimitResponses;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;
use JFB\LimitResponses\RestrictTypes\Interfaces\RestrictionIt;
use JFB\LimitResponses\RestrictTypes\Traits\RestrictionTrait;

class Session implements RestrictionIt {

	use RestrictionTrait;

	private $session = array();

	public function __construct(
		SyncedCycleCounter $counter,
		SettingsMetaQuery $settings
	) {
		$this->set_counter( $counter );
		$this->set_meta_settings( $settings );
	}

	public function get_id(): string {
		return 'session';
	}

	public function before_run() {
		$this->start_session();

		$form_id = $this->get_meta_settings()->get_form_id();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$this->session = $_SESSION[ LimitResponses::PLUGIN_META_KEY ] ?? array();

		if ( ! isset( $this->session[ $form_id ] ) ) {
			$this->session[ $form_id ] = 0;
		}

		if ( ! is_array( $this->session[ $form_id ] ) ) {
			$this->session[ $form_id ] = array(
				'count'    => $this->session[ $form_id ],
				'reset_at' => - 1,
			);
		}

		$this->get_counter()->set_count( (int) ( $this->session[ $form_id ]['count'] ) );
		$this->get_counter()->set_reset_at( (int) ( $this->session[ $form_id ]['reset_at'] ) );
	}

	public function increment() {
		// increase reset_at timestamp
		$this->get_counter()->new_reset_at();

		$this->session[ $this->get_meta_settings()->get_form_id() ] = array(
			'count'    => 1 + $this->get_counter()->get_count(),
			'reset_at' => $this->get_counter()->get_reset_at(),
		);

		$_SESSION[ LimitResponses::PLUGIN_META_KEY ] = $this->session;
	}

	/**
	 * Maybe start session
	 */
	private function start_session() {
		if ( headers_sent() ) {
			return;
		}

		if ( ! session_id() ) {
			session_start();
		}
	}
}
