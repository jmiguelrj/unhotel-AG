<?php
add_action( 'wp_ajax_nopriv_homey_add_review', 'homey_add_review' );
add_action( 'wp_ajax_homey_add_review', 'homey_add_review' );  
if( !function_exists('homey_add_review') ) {
	function homey_add_review() {
		global $current_user;
      	$current_user = wp_get_current_user();
        $userID       = $current_user->ID;
      	$local = homey_get_localization();
      	$allowded_html = array();

      	$review_reservation_id = intval($_POST['review_reservation_id']);
      	$rating = intval($_POST['rating']);
      	$review_content = $_POST['review_content'];
      	$review_action = $_POST['review_action'];

      	if(empty($review_reservation_id)) {
      		echo json_encode( 
		     	array( 
		     		'success' => false, 
		     		'message' => $local['something_went_wrong']
		     	) 
		     );
		     wp_die();
      	}
     
      	$review_owner_id = $userID;
      	$review_listing_id = get_post_meta($review_reservation_id, 'reservation_listing_id', true);
		$review_listing_owner_id = get_post_meta($review_reservation_id, 'listing_owner', true);
		$update_review_id = get_post_meta($review_reservation_id, 'review_id', true);

		$title = esc_html__('Review', 'homey');
		//check security
      	$nonce = $_REQUEST['security'];
		if ( ! wp_verify_nonce( $nonce, 'review-security-nonce' ) ) {

		     echo json_encode( 
		     	array( 
		     		'success' => false, 
		     		'message' => $local['security_check_text'] 
		     	) 
		     );
		     wp_die();
		}

		if(empty($review_content)) {
			echo json_encode( 
		     	array( 
		     		'success' => false, 
		     		'message' => $local['review_content_required']
		     	) 
		     );
		     wp_die();
		}

		if( $review_action == 'add_review' ) {

            $review = array(
	            'post_title'	=> $title,
	            'post_status'	=> 'publish', 
	            'post_type'     => 'homey_review',
	            'post_author'   => $userID
	        );
	        $review_id =  wp_insert_post($review);  
	        
	        $review_update = array(
	            'ID'         => $review_id,
	            'post_title' => $title.' '.$review_id,
	            'post_content' => $review_content,
	        );
	        wp_update_post( $review_update );

        } else if( $review_action == 'update_review' ) {

            $update_review['ID'] = intval( $update_review_id );
            $update_review['post_content'] = $review_content;

            $review_id = wp_update_post( $update_review );

        }

        update_post_meta($review_id, 'reservation_listing_id', $review_listing_id);
        update_post_meta($review_id, 'listing_owner_id', $review_listing_owner_id);
        update_post_meta($review_id, 'reviewer_id', $review_owner_id);
        update_post_meta($review_reservation_id, 'review_id', $review_id);
        update_post_meta($review_id, 'review_reservation_id', $review_reservation_id);
    	update_post_meta($review_id, 'homey_rating', $rating);
    	
    	//if user is not listing owner then ratting should be added - zk
        if($userID != $review_listing_owner_id ){ 
        	homey_add_listing_rating($review_listing_id);
        }
        //if user is not listing owner then ratting should be added - zk      

        homey_send_review_email($review_listing_id, $review_id, $rating, $review_content, $review_listing_owner_id, $review_reservation_id);
      
        echo json_encode( 
	     	array( 
	     		'success' => true, 
	     		'message' => ''
	     	) 
	     );
	     wp_die();
	}
}

add_action( 'wp_ajax_nopriv_homey_add_guest_review', 'homey_add_guest_review' );
add_action( 'wp_ajax_homey_add_guest_review', 'homey_add_guest_review' );  
if( !function_exists('homey_add_guest_review') ) {
	function homey_add_guest_review() {
		global $current_user;
      	$current_user = wp_get_current_user();
        $userID       = $current_user->ID;
      	$local = homey_get_localization();
      	$allowded_html = array();

      	$review_guest_reservation_id = intval($_POST['review_guest_reservation_id']);
      	$rating = intval($_POST['rating']);
      	$review_content = $_POST['review_content'];
      	$review_action = $_POST['review_action'];

      	if(empty($review_guest_reservation_id)) {
      		echo json_encode( 
		     	array( 
		     		'success' => false, 
		     		'message' => $local['something_went_wrong']
		     	) 
		     );
		     wp_die();
      	}
     
      	$review_owner_id = $userID;
      	$review_listing_id = get_post_meta($review_guest_reservation_id, 'reservation_listing_id_for_guest', true);
		$review_guest_id = get_post_meta($review_guest_reservation_id, 'listing_renter', true);
		$update_review_id = get_post_meta($review_guest_reservation_id, 'guest_review_id', true);


		$title = esc_html__('Review', 'homey');
		//check security
      	$nonce = $_REQUEST['security'];
		if ( ! wp_verify_nonce( $nonce, 'review-security-nonce' ) ) {

		     echo json_encode( 
		     	array( 
		     		'success' => false, 
		     		'message' => $local['security_check_text'] 
		     	) 
		     );
		     wp_die();
		}

		if(empty($review_content)) {
			echo json_encode( 
		     	array( 
		     		'success' => false, 
		     		'message' => $local['review_content_required']
		     	) 
		     );
		     wp_die();
		}

		if( $review_action == 'add_guest_review' ) {

            $review = array(
	            'post_title'	=> $title,
	            'post_status'	=> 'publish', 
	            'post_type'     => 'homey_review',
	            'post_author'   => $userID
	        );
	        $review_id =  wp_insert_post($review);  
	        
	        $review_update = array(
	            'ID'         => $review_id,
	            'post_title' => $title.' '.$review_id,
	            'post_content' => $review_content,
	        );
	        wp_update_post( $review_update );

        } else if( $review_action == 'update_guest_review' ) {

            $update_review['ID'] = intval( $update_review_id );
            $update_review['post_content'] = $review_content;

            $review_id = wp_update_post( $update_review );

        }

        update_post_meta($review_id, 'reservation_listing_id_for_guest', $review_listing_id);
        update_post_meta($review_id, 'review_guest_id', $review_guest_id);
        update_post_meta($review_id, 'reviewer_id', $review_owner_id);
        update_post_meta($review_guest_reservation_id, 'guest_review_id', $review_id);
        update_post_meta($review_id, 'review_guest_reservation_id', $review_guest_reservation_id);
        update_post_meta($review_id, 'homey_guest_rating', $rating);

        homey_send_review_email($review_listing_id, $review_id, $rating, $review_content, $review_guest_id, $review_guest_reservation_id);
      
        echo json_encode( 
	     	array( 
	     		'success' => true, 
	     		'message' => ''
	     	) 
	     );
	     wp_die();
	}
}

if(!function_exists('homey_send_review_email')) {
	function homey_send_review_email($review_listing_id, $review_id, $rating, $review_content, $send_to_user_id, $review_reservation_id) {

		$is_guest = $is_host = false;
		
		$role = homey_user_role_by_user_id($send_to_user_id);

		if($role == 'homey_renter') {
			$is_guest = true;
		} else {
			$is_host = true;
		}

		$review_link = get_permalink($review_listing_id);
		$review_link .= '#review-'.$review_id;

		$guest_review_link = get_author_posts_url( $send_to_user_id );
		$guest_review_link .= '#review-'.$review_id;

		$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
		$write_review_link = add_query_arg( 
			array(
				'reservation_detail' => $review_reservation_id,
				'write_review' => 1,
			),$reservation_page_link 
		);


		$email_subject = sprintf( esc_html__('A new rating has been received for reservation %s', 'homey'), $review_reservation_id );

        $email_body = esc_html__("Rating: ", 'homey') . $rating . " <br/>";
        
        $email_body .= esc_html__("Comment:", 'homey').' '.( $review_content ) . " <br/>";
        $email_body .= '----------------------------------------- <br/>'; 
        
        if($is_host) {
	        $email_body .= esc_html__('You can view this at', 'homey').' '.'<a href="'.esc_url($review_link).'">'.$review_link.'</a><br/>';
	    }

	    if($is_guest) {
	        $email_body .= esc_html__('You can view this at', 'homey').' '.'<a href="'.esc_url($guest_review_link).'">'.$guest_review_link.'</a><br/>';
	    }


        $email_body .= esc_html__('You can write your review at', 'homey').' '.'<a href="'.esc_url($write_review_link).'">'.$write_review_link.'</a><br/>';


        $headers = 'From: No Reply <noreply@'.isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : 'noreply.com'.'>' . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $target_email = get_the_author_meta( 'email', $send_to_user_id );

        $email = wp_mail( $target_email, $email_subject, $email_body, $headers);

	}

}

if(!function_exists('homey_add_listing_rating')) {
	function homey_add_listing_rating($listing_id) {
		$args = array(
		    'post_type'   => 'homey_review',
		    'meta_key' => 'reservation_listing_id',
		    'meta_value' => $listing_id,
		    'post_status' => 'publish',
		);

		$listing_rating = '';
		$total_stars = $total_review = 0;

		$review_query = new WP_Query($args);
		if($review_query->have_posts()) {
			$total_review = $review_query->found_posts;

			while($review_query->have_posts()): $review_query->the_post();
				$homey_rating = get_post_meta(get_the_ID(), 'homey_rating', true);
				$total_stars = $total_stars + $homey_rating;

			endwhile; 
			wp_reset_postdata();

			$rating = $total_stars/$total_review;
			$rating = $rating > 4.5 ? 5 : $rating;

			update_post_meta($listing_id, 'listing_total_rating', $rating);

			return true;
		}
		return true;
	}
}

if(!function_exists('homey_adjust_listing_rating_on_delete')) {
	function homey_adjust_listing_rating_on_delete($listing_id, $review_id) {
		$args = array(
		    'post_type'   => 'homey_review',
		    'meta_key' => 'reservation_listing_id',
		    'meta_value' => $listing_id,
		    'post_status' => 'publish'
		);

		$listing_rating = '';
		$total_stars = $total_review = 0;

		$review_query = new WP_Query($args);
		if($review_query->have_posts()) { 
			$total_review = $review_query->found_posts;
			while($review_query->have_posts()): $review_query->the_post();
				$homey_rating = get_post_meta(get_the_ID(), 'homey_rating', true);
				$total_stars = $total_stars + $homey_rating;

			endwhile; 
			wp_reset_postdata();
		}
			

		if($total_review == 0) {
			$rating = '';
		} else {
			$rating = $total_stars/$total_review;
		}
		
		update_post_meta($listing_id, 'listing_total_rating', $rating);

		return true;
	}
}

add_action( 'wp_ajax_nopriv_homey_ajax_review', 'homey_ajax_review' );
add_action( 'wp_ajax_homey_ajax_review', 'homey_ajax_review' );  
if( !function_exists('homey_ajax_review') ) {
	function homey_ajax_review() {
		global $homey_local;
      	$homey_local = homey_get_localization();
      	$allowded_html = array();
      	$meta_query = array();
      	$num_of_review = homey_option('num_of_review');

      	$listing_id = intval($_POST['listing_id']);
      	$sort_by = $_POST['sortby'];
      	$paged = $_POST['paged'];

      	$args = array(
		    'post_type' =>  'homey_review',
		    'posts_per_page' => $num_of_review,
		    'post_status' =>  'publish'
		);

		$meta_query[] = array(
            'key' => 'reservation_listing_id',
            'value' => $listing_id,
            'type' => 'NUMERIC',
            'compare' => '=',
        );

		if ( $sort_by == 'a_rating' ) {
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'homey_rating';
            $args['order'] = 'ASC';
        } else if ( $sort_by == 'd_rating' ) {
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'homey_rating';
            $args['order'] = 'DESC';
        } else if ( $sort_by == 'a_date' ) {
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        }

        $meta_count = count($meta_query);
        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }
        if ($meta_count > 0) {
            $args['meta_query'] = $meta_query;
        }

        if (!empty($paged) && $paged > 1) {
            $args['paged'] = $paged;
        } else {
            $args['paged'] = 1;
        }

		$review_query = new WP_Query($args);

		echo '<input type="hidden" name="page_sort" id="page_sort" value="'.$sort_by.'">';
		
		if($review_query->have_posts()) {
			while($review_query->have_posts()): $review_query->the_post(); 
				$review_author = homey_get_author('70', '70', 'img-circle');
				$homey_rating = get_post_meta(get_the_ID(), 'homey_rating', true); ?>

				<li id="review-<?php the_ID();?>" class="review-block">
					<div class="media">
						<div class="media-left">
							<a class="media-object">
								<?php echo ''.$review_author['photo']; ?>
							</a>
						</div>
						<div class="media-body media-middle">
							<div class="msg-user-info">
								<div class="msg-user-left">
									<div>
										<strong><?php echo esc_attr($review_author['name']); ?></strong> 
										<span class="rating">
											<?php echo homey_get_review_stars($homey_rating, true, true, false); ?>
										</span>
										
									</div>
									<div class="message-date">
										
											<i class="fa fa-calendar"></i> <?php esc_attr( the_time( get_option( 'date_format' ) ));?>
											<i class="fa fa-clock-o"></i> <?php esc_attr( the_time( get_option( 'time_format' ) ));?>
										
									</div>
								</div>
							</div>
							<?php the_content(); ?>
						</div>
					</div>
				</li>
		<?php
			endwhile; 
			wp_reset_postdata();
		}

	    wp_die();
	}
}

if(!function_exists('homey_get_review_stars')) {
	function homey_get_review_stars($stars, $is_span = false, $is_label = true, $is_label_as_text = true ) {

		$local = homey_get_localization();

		$output = '';

		if($is_label_as_text) {
			$label_class = 'star-text-right';
		} else {
			$label_class = 'label label-success';
		}

		if($is_span) {
			$html_attr = 'span';
			$html_attr2 = '';
			$html_attr2_end = '';
		} else {
			$html_attr = 'li';
			$html_attr2 = '<li>';
			$html_attr2_end = '</li>';
		}

		if($stars >= 1 && $stars < 1.5) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'>
                <'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>
                <'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>
                <'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>
                <'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>';

	            if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_poor'].'</span>'.$html_attr2_end;
		        }

		} elseif($stars >= 1.5 && $stars < 2) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'>
                <'.$html_attr.' class="fa fa-star-half-o"></'.$html_attr.'>
                <'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>
                <'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>
                <'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>';

                if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_fair'].'</span>'.$html_attr2_end;
		        }

		} elseif($stars >= 2 & $stars < 2.5) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>';

                if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_fair'].'</span>'.$html_attr2_end;
		        }

		}  elseif($stars >= 2.5 & $stars < 3) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-half-o"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>';

                if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_average'].'</span>'.$html_attr2_end;
		        }

		} elseif($stars >= 3 && $stars < 3.5 ) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>';

                if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_average'].'</span>'.$html_attr2_end;
		        }

		} elseif($stars >= 3.5 && $stars < 4 ) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-half-o"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>';

                if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_good'].'</span>'.$html_attr2_end;
		        }

		} elseif($stars >= 4 && $stars < 4.5) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-o"></'.$html_attr.'>';

                if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_good'].'</span>'.$html_attr2_end;
		        }

		}  elseif($stars >= 4.5 && $stars < 5) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star-half-o"></'.$html_attr.'>';

                if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_excellent'].'</span>'.$html_attr2_end;
		        }

		} elseif($stars >= 5) {
			$output = '
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'> 
				<'.$html_attr.' class="fa fa-star"></'.$html_attr.'>';

                if($is_label) {
		            $output .= $html_attr2.'<span class="'.$label_class.'">'.$local['rating_excellent'].'</span>'.$html_attr2_end;
		        }
		}

		return $output;

	}
}

if(!function_exists('homey_get_listing_rating')) {
	function homey_get_listing_rating($listing_id, $is_span = false, $is_label = true) {
		$args = array(
		    'post_type'   => 'homey_review',
		    'meta_key' => 'reservation_listing_id',
		    'meta_value' => $listing_id,
		    'post_status' => 'publish',
		);

		$listing_rating = '';
		$total_stars = $total_review = 0;

		$review_query = new WP_Query($args);
		if($review_query->have_posts()) {
			$total_review = $review_query->found_posts;

			while($review_query->have_posts()): $review_query->the_post();
				$homey_rating = get_post_meta(get_the_ID(), 'homey_rating', true);
				$total_stars = $total_stars + $homey_rating;

			endwhile; 
			wp_reset_postdata();

			$rating = $total_stars/$total_review;

			$listing_rating = homey_get_review_stars($rating, $is_span, $is_label);
			//update_post_meta($listing_id, 'listing_total_rating', $rating);

			return $listing_rating;
		}
		return $listing_rating;
	}
}


if(!function_exists('homey_get_host_reviews')) {
	function homey_get_host_reviews($author_id) {

		$homey_local = homey_get_localization();
		$is_host_have_reviews = false;
		$all_reviews = '';
		$host_rating = '';

		$return_reviews = array();

		$review_args = array(
		    'post_type' =>  'homey_review',
		    'meta_key' => 'listing_owner_id',
		    'meta_value' => $author_id,
		    'post_status' =>  'publish'
		);

		$review_query = new WP_Query($review_args);

		if($review_query->have_posts()) {
		    //$total_review = $review_query->found_posts;
		    $total_review =0;

		    $is_host_have_reviews = true;

		    $total_stars = 0;

		    if($total_review > 1) {
		        $review_label = $homey_local['rating_reviews_label'];
		    } else {
		        $review_label = $homey_local['rating_review_label'];
		    }

		    while($review_query->have_posts()): $review_query->the_post();
                $reviewer_id = get_post_meta(get_the_ID(), 'reviewer_id', true);

	            if($author_id != $reviewer_id){
		    		$total_review++;
	                $review_author = homey_get_author('70', '70', 'img-circle');
	                    $homey_rating = get_post_meta(get_the_ID(), 'homey_rating', true);
	                    // print_r(get_post_meta(get_the_ID()));
	                    $listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id', true);

	                    $total_stars = $total_stars + $homey_rating;

	                    $all_reviews .= '
	                    <li class="review-block">
	                        <div class="media">
	                            <div class="media-left">
	                                <a href="'.$review_author['link'].'" target="_blank" class="media-object">
	                                    '.$review_author['photo'].'
	                                </a>
	                            </div>
	                            <div class="media-body media-middle">
	                                <div class="msg-user-info">
	                                    <div class="msg-user-left">
	                                        <strong>'.esc_attr($review_author['name']).'</strong>
	                                        <div>'.esc_html__('on', 'homey').' <a href="'.get_permalink($listing_id).'">'.get_the_title($listing_id).'</a> 
	                                        <span class="rating">
	                                            '.homey_get_review_stars($homey_rating, true, true, false).'
	                                        </span>
	                                        </div>
	                                        <div class="message-date">
	                                            
	                                                <i class="fa fa-calendar"></i> '.esc_attr( get_the_time( get_option( 'date_format' ) )).'
	                                                <i class="fa fa-clock-o"></i> '.esc_attr( get_the_time( get_option( 'time_format' ) )).'
	                                            
	                                        </div>
	                                    </div>
	                                </div>
	                                <p>
	                                '.get_the_content().'
	                                </p>
	                            </div>
	                        </div>
	                    </li>';
		    	}                
            endwhile; 

		    $rating = $total_stars/$total_review;
		    $rating = $rating > 4.5 ? 5 : $rating;

		    $host_rating = homey_get_review_stars($rating, true, $is_label = true, $is_label_as_text = true);
		    wp_reset_postdata();
		}

		$return_reviews['reviews_data'] = $all_reviews;
		$return_reviews['host_rating'] = $host_rating;
		$return_reviews['is_host_have_reviews'] = $is_host_have_reviews;

		return $return_reviews;
	}
}

if(!function_exists('homey_get_guest_reviews')) {
	function homey_get_guest_reviews($author_id) {

		$homey_local = homey_get_localization();
		$is_guest_have_reviews = false;
		$all_reviews = '';
		$host_rating = '';
		$total_review = 0;

		$return_reviews = array();

		$review_args = array(
		    'post_type' =>  'homey_review',
		    'meta_key' => 'review_guest_id',
		    'meta_value' => $author_id,
		    'post_status' =>  'publish'
		);

		$review_query = new WP_Query($review_args);

		if($review_query->have_posts()) {
		    $total_review = $review_query->found_posts;

		    $is_guest_have_reviews = true;

		    $total_stars = 0;

		    if($total_review > 1) {
		        $review_label = $homey_local['rating_reviews_label'];
		    } else {
		        $review_label = $homey_local['rating_review_label'];
		    }

		    while($review_query->have_posts()): $review_query->the_post(); 
		        $review_author = homey_get_author('70', '70', 'img-circle');
		        $homey_rating = get_post_meta(get_the_ID(), 'homey_guest_rating', true);
		        $listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id_for_guest', true);

		        $total_stars = $total_stars + $homey_rating;

                $all_reviews .= '
		        <li id="review-'.get_the_ID().'" class="review-block">
                    <div class="media">
                        <div class="media-left">
                            <a href="'.$review_author['link'].'" target="_blank" class="media-object">
                                '.$review_author['photo'].'
                            </a>
                        </div>
                        <div class="media-body media-middle">
                            <div class="msg-user-info">
                                <div class="msg-user-left">
                                    <strong>'.esc_attr($review_author['name']).'</strong> 
                                    <span class="rating">
                                    '.homey_get_review_stars($homey_rating, true, true, false).'
                                    </span>
                                    <div>
                                    </div>
                                    <div class="message-date">
		                                <i class="fa fa-calendar"></i> '.esc_attr( get_the_time( get_option( 'date_format' ) )).'
		                                <i class="fa fa-clock-o"></i> '.esc_attr( get_the_time( get_option( 'time_format' ) )).'
                                    </div>
                                </div>
                            </div>
                            <p>'.get_the_content().'</p>
                        </div>
                    </div>
                </li>';

		    endwhile; 

		    $rating = $total_stars/$total_review;
		    $host_rating = homey_get_review_stars($rating, true, $is_label = true, $is_label_as_text = true);
		    wp_reset_postdata();
		}

		$return_reviews['reviews_data'] = $all_reviews;
		$return_reviews['guest_rating'] = $host_rating;
		$return_reviews['total_reviews'] = $total_review;
		$return_reviews['is_guest_have_reviews'] = $is_guest_have_reviews;

		return $return_reviews;
	}
}
