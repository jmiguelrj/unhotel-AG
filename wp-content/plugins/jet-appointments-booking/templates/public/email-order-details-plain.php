<?php
/**
 * WooCommerce order details
 */
?>
<?php esc_html_e( 'Appointment Details', 'jet-appointments-booking' ); ?>
<?php
	foreach ( $details as $item ) {
		echo '- ';
			if ( ! empty( $item['key'] ) ) {
				echo esc_html( $item['key'] ) . ': ';
			}

			if ( ! empty( $item['is_html'] ) ) {
				echo esc_html( $item['display'] );
			} else {
				echo '<strong>' . esc_html( $item['display'] ) . '</strong>';
			}

		echo '
';
	}
?>
