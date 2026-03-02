<?php


namespace JFB\LimitResponses\RestrictTypes;

use JFB\LimitResponses\Counters\SyncedCycleCounter;
use JFB\LimitResponses\MetaQueries\FormCountersMetaQuery;
use JFB\LimitResponses\MetaQueries\SettingsMetaQuery;
use JFB\LimitResponses\RestrictTypes\Interfaces\RestrictionIt;
use JFB\LimitResponses\RestrictTypes\Traits\RestrictionTrait;

class IPAddress implements RestrictionIt {

	use RestrictionTrait;

	const RESTRICT_META_KEY = '_restrict_client_ip_addresses';

	private $client_ip;
	private $counter;
	private $query;

	public function __construct(
		SyncedCycleCounter $counter,
		FormCountersMetaQuery $query,
		SettingsMetaQuery $settings
	) {
		$this->set_counter( $counter );
		$this->set_meta_settings( $settings );
		$this->query = $query;
	}

	public function get_id(): string {
		return 'id_address';
	}

	public function before_run() {
		$this->set_ip( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );

		$this->get_query()->fetch();
		$counters = $this->get_query()->get_settings();

		$this->get_counter()->set_count(
			(int) ( $counters[ self::RESTRICT_META_KEY ][ $this->client_ip ]['count'] ?? 0 )
		);
		$this->get_counter()->set_reset_at(
			(int) ( $counters[ self::RESTRICT_META_KEY ][ $this->client_ip ]['reset_at'] ?? - 1 )
		);
	}

	public function increment() {
		// increase reset_at timestamp
		$this->get_counter()->new_reset_at();

		$counters = $this->resolve_counters();

		$counters[ self::RESTRICT_META_KEY ][ $this->client_ip ] = array(
			'count'    => 1 + $this->get_counter()->get_count(),
			'reset_at' => $this->get_counter()->get_reset_at(),
		);

		$this->get_query()->set_settings( $counters );
		$this->get_query()->update();
	}

	protected function resolve_counters(): array {
		$meta_counters = $this->get_query()->get_settings();;

		$meta_counters[ self::RESTRICT_META_KEY ] = (
			$meta_counters[ self::RESTRICT_META_KEY ] ?? array()
		);

		$meta = &$meta_counters[ self::RESTRICT_META_KEY ];

		if ( ! isset( $meta[ $this->client_ip ] ) ) {
			$meta[ $this->client_ip ] = 0;
		}

		if ( ! is_array( $meta[ $this->client_ip ] ) ) {
			$meta[ $this->client_ip ] = array(
				'count'    => $meta[ $this->client_ip ],
				'reset_at' => - 1,
			);
		}

		return $meta_counters;
	}

	private function set_ip( $ip ) {
		$this->client_ip = $ip;
	}

	/**
	 * @return FormCountersMetaQuery
	 */
	public function get_query(): FormCountersMetaQuery {
		return $this->query;
	}
}
