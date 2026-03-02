<?php
/**
 * Admin order details
 */
?>
<hr>
<h3><?php esc_html_e( 'Appointment Details', 'jet-appointments-booking' ); ?></h3>
<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
	<?php
		foreach ( $details as $item ) {
			?>
			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
			<?php
			foreach ( $item as $field ){
				echo '<li>';
					if ( ! empty( $field['key'] ) ) {
						echo esc_html( $field['key'] ) . ': ';
					}

					if ( ! empty( $field['is_html'] ) ) {
						echo esc_html( $field['display'] );
					} else {
						echo '<strong>' . esc_html( $field['display'] ) . '</strong>';
					}

				echo '</li>';
			}?>
			</ul>
		<?php
		}
	?>
</ul>