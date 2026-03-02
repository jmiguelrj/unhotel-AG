<?php
/**
 * WooCommerce order details
 */
?>
<h2 style="color:#96588a;display:block;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left"><?php
	esc_html_e( 'Appointment Details', 'jet-appointments-booking' );
?></h2>
<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
	<?php
		foreach ( $details as $item ) {
			if( ! empty( $item[ 'is_header' ] ) ){
				echo '<br/>';
			}
			echo '<li>';
				if ( ! empty( $item['key'] ) ) {
					echo esc_html( $item['key'] ) . ': ';
				}

				if ( ! empty( $item['is_html'] ) ) {
					echo wp_kses_post( $item['display'] );
				} else {
					echo '<strong>' . wp_kses_post( $item['display'] ) . '</strong>';
				}

			echo '</li>';
		}
	?>
</ul>