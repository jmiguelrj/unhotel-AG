const { __ } = wp.i18n;

const labels = {
	enable: __( 'Enable' ),
	from_date: __( 'From date' ),
	to_date: __( 'To date' ),
	pending_message: __( 'Pending message' ),
	expired_message: __( 'Expired message' ),
};

const help = {
	pending_message: __( 'Text to display instead of form, before the date specified in the `From date`. You can use shortcodes here' ),
	expired_message: __( 'Text to display instead of form, after the date specified in the `To date`. You can use shortcodes here' ),
};

export {
	labels,
	help
};