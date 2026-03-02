<?php


namespace JFB\LimitResponses\Counters;

class CycleCounter {

	const DEFAULT = array(
		'count'    => 0,
		'reset_at' => - 1,
	);

	private $count    = 0;
	private $limit    = 1;
	private $reset_at = - 1;
	/**
	 * If it's empty string - counter would be never reset.
	 *
	 * @var string
	 */
	private $cycle = '';

	public function is_reached_limit(): bool {
		$this->reset_count();

		return $this->get_limit() <= $this->get_count();
	}

	private function reset_count() {
		if ( ! $this->get_cycle() || - 1 === $this->get_reset_at() || $this->get_reset_at() > time() ) {
			return;
		}
		$this->set_count( 0 );
	}

	/**
	 * @param int $count
	 */
	public function set_count( int $count ) {
		$this->count = $count;
	}

	/**
	 * @return int
	 */
	public function get_count(): int {
		return $this->count;
	}

	/**
	 * @param int $reset_at
	 */
	public function set_reset_at( int $reset_at ) {
		$this->reset_at = $reset_at;
	}

	public function new_reset_at() {
		if ( ! $this->get_cycle() ) {
			$this->reset_at = - 1;

			return;
		}

		$interval = apply_filters( 'jet-fb/limit-form-responses/reset-interval', $this->get_interval() );

		$this->reset_at = time() + $interval;
	}

	private function get_interval(): int {
		switch ( $this->get_cycle() ) {
			case 'day':
				return DAY_IN_SECONDS;
			case 'week':
				return WEEK_IN_SECONDS;
			case 'month':
				return MONTH_IN_SECONDS;
			case 'year':
				return YEAR_IN_SECONDS;
			default:
				return 0;
		}
	}

	/**
	 * @return int
	 */
	public function get_reset_at(): int {
		return $this->reset_at;
	}

	/**
	 * @param string $cycle
	 */
	public function set_cycle( string $cycle ) {
		$this->cycle = $cycle;
	}

	/**
	 * @return string
	 */
	public function get_cycle(): string {
		return $this->cycle;
	}

	/**
	 * @param int $limit
	 */
	public function set_limit( int $limit ) {
		if ( ! $limit || 0 > $limit ) {
			$limit = 1;
		}
		$this->limit = $limit;
	}

	/**
	 * @return int
	 */
	public function get_limit(): int {
		return $this->limit;
	}

}
