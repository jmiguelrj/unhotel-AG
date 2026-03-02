<?php


namespace JFB\LimitResponses\RestrictTypes;

use JFB\LimitResponses\Counters\SyncedCycleCounter;
use JFB\LimitResponses\LimitResponses;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;
use JFB\LimitResponses\RestrictTypes\Interfaces\RestrictionIt;
use JFB\LimitResponses\RestrictTypes\Traits\RestrictionTrait;

class Cookie implements RestrictionIt {

	use RestrictionTrait;

	private $cookie;

	public function __construct( SyncedCycleCounter $counter, SettingsMetaQuery $settings ) {
		$this->set_counter( $counter );
		$this->set_meta_settings( $settings );
	}

	public function get_id(): string {
		return 'cookie';
	}

	public function before_run() {
		$form_id = $this->get_meta_settings()->get_form_id();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$cookie = $_COOKIE[ SettingsMetaQuery::META_KEY ][ $form_id ] ?? 0;

		if ( ! is_array( $cookie ) ) {
			$cookie = array(
				'count'    => $cookie,
				'reset_at' => - 1,
			);
		}

		$this->get_counter()->set_count( (int) $cookie['count'] );
		$this->get_counter()->set_reset_at( (int) $cookie['reset_at'] );
	}

	public function increment() {
		$this->get_counter()->new_reset_at();

		$this->set_cookie( 'count', $this->get_counter()->get_count() + 1 );
		$this->set_cookie( 'reset_at', $this->get_counter()->get_reset_at() );
	}

	public function set_cookie( string $name, $value ): bool {
		$cookie_name = SettingsMetaQuery::META_KEY . "[{$this->get_meta_settings()->get_form_id()}][$name]";
		$expire      = time() + YEAR_IN_SECONDS * 100;
		$secure      = ( false !== strstr( get_option( 'home' ), 'https:' ) && is_ssl() );

		return setcookie(
			$cookie_name,
			$value,
			$expire,
			COOKIEPATH ?: '/',
			COOKIE_DOMAIN,
			$secure,
			true
		);
	}
}
