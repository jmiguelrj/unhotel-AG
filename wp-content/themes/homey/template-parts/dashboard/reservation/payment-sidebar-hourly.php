<div class="block">
	<div class="block-title">
		<h3 class="title"><?php esc_html_e('Payment', 'homey'); ?></h3>
	</div>
	<div class="block-body">
		<div class="payment-list">

		    <?php 
			$reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
		    	if(!empty($reservationID)) {
			    	echo homey_calculate_hourly_reservation_cost($reservationID, true); 
			    }
		    ?>

		</div><!-- payment-list --> 
	</div><!-- block-body -->
</div>