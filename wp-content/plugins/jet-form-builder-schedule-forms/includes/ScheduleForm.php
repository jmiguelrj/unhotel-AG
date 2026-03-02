<?php


namespace JFB\ScheduleForms;


use JFB\ScheduleForms\Queries\SettingsQuery;

class ScheduleForm {

	const TYPE_PENDING    = 'pending';
	const TYPE_EXPIRED    = 'expired';
	const PLUGIN_META_KEY = SettingsQuery::META_KEY;

	const DEFAULT_MESSAGES = array(
		self::TYPE_PENDING => 'Pending message',
		self::TYPE_EXPIRED => 'Expired message',
	);

	private $query;

	public function __construct(
		SettingsQuery $query
	) {
		$this->query = $query;
	}

	public function get_content(): string {
		$schedule_type = $this->get_schedule_type();

		if ( ! $schedule_type ) {
			return false;
		}

		$content = do_shortcode( $this->get_message( $schedule_type ) );

		return sprintf(
			'<div class="jet-form-schedule-message %s-message">%2$s</div>',
			$schedule_type,
			$content
		);
	}

	public function get_schedule_type() {
		if ( ! $this->is_enabled() || ! $this->has_dates() ) {
			return false;
		}
		$current_time = current_time( 'timestamp' );
		$from_date    = strtotime( $this->get_query()->get_setting( 'from_date' ) );
		$to_date      = strtotime( $this->get_query()->get_setting( 'to_date' ) );

		if ( $from_date && $current_time < $from_date ) {
			return ScheduleForm::TYPE_PENDING;
		}

		if ( $to_date && $current_time > $to_date ) {
			return ScheduleForm::TYPE_EXPIRED;
		}

		return false;
	}

	public function is_enabled(): bool {
		return (bool) $this->get_query()->get_setting( 'enable' );
	}

	public function has_dates(): bool {
		return (
			$this->get_query()->get_setting( 'from_date' )
			&& $this->get_query()->get_setting( 'to_date' )
		);
	}

	public function get_message( $type ): string {
		$attr = $type . '_message';

		return $this->get_query()->get_setting( $attr ) ?: ScheduleForm::DEFAULT_MESSAGES[ $type ];
	}

	/**
	 * @return SettingsQuery
	 */
	public function get_query(): SettingsQuery {
		return $this->query;
	}

}