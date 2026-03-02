<?php
add_action( 'homey_create_messages_thread', 'save_homey_create_messages_thread', 10, 3 );
if(!function_exists('save_homey_create_messages_thread')) {
	function save_homey_create_messages_thread($guest_message, $reservation_id, $userID = '' ) {
		
		if ( !empty( $reservation_id ) && !empty( $guest_message ) ) {

			$message_attachments = array();

			$receiver_id = get_post_meta($reservation_id, 'listing_owner', true);

			$data = array(
				'listing_id' => $reservation_id,
				'receiver_id' => $receiver_id,
				'user_id' => $userID,
			);

			$message = $guest_message;
			$thread_id = apply_filters( 'homey_start_thread', $data );
			$message_id = apply_filters( 'homey_thread_message', $thread_id, $message, $message_attachments );

			if ( $message_id ) {


			}

		}

	}
}


add_action( 'wp_ajax_nopriv_homey_start_thread', 'homey_start_thread' );
add_action( 'wp_ajax_homey_start_thread', 'homey_start_thread' );

if ( !function_exists( 'homey_start_thread' ) ) {

	function homey_start_thread() {

		$messages_page = homey_get_template_link_2('template/dashboard-messages.php');

		$nonce = $_POST['start_thread_form_ajax'];

		if ( !wp_verify_nonce( $nonce, 'start-thread-form-nonce') ) {
			echo json_encode( array(
				'success' => false,
				'msg' => esc_html__('Unverified Nonce!', 'homey')
			));
			wp_die();
		}

		if ( empty( $_POST['message'] ) ) {
			echo json_encode( array(
				'success' => false,
				'msg' => esc_html__('Please write something in message', 'homey')
			));
			wp_die();
		}

		if ( isset( $_POST['listing_id'] ) && !empty( $_POST['listing_id'] ) && isset( $_POST['message'] ) && !empty( $_POST['message'] ) ) {

			$message_attachments = array();
			if ( isset( $_POST['listing_image_ids'] ) && sizeof( $_POST['listing_image_ids'] ) != 0 ) {
				$message_attachments = $_POST['listing_image_ids'];
			}
			$message_attachments = serialize( $message_attachments );

			$message = sanitize_textarea_field($_POST['message']);
			$thread_id = apply_filters( 'homey_start_thread', $_POST );
			$message_id = apply_filters( 'homey_thread_message', $thread_id, $message, $message_attachments );

			if ( $message_id ) {

				$redirect_link = add_query_arg( array(
		            'thread_id' => $thread_id
		        ), $messages_page );

				echo json_encode(
					array(
						'success' => true,
						'redirect_link' => $redirect_link,
						'msg' => esc_html__("Message sent successfully!", 'homey')
					)
				);

				wp_die();

			}

		}

		echo json_encode(
			array(
				'success' => false,
				'msg' => esc_html__("Some errors occurred! Please try again.", 'homey')
			)
		);

		wp_die();

	}

}

add_action( 'wp_ajax_nopriv_homey_thread_message', 'homey_thread_message' );
add_action( 'wp_ajax_homey_thread_message', 'homey_thread_message' );

if ( !function_exists( 'homey_thread_message' ) ) {

	function homey_thread_message() {

		$nonce = $_POST['start_thread_message_form_ajax'];

		if ( !wp_verify_nonce( $nonce, 'start-thread-message-form-nonce') ) {
			echo json_encode( array(
				'success' => false,
				'url' => homey_get_template_link_2('template/dashboard-messages.php') . '?' . http_build_query( array( 'thread_id' => $thread_id, 'success' => false ) ),
				'msg' => esc_html__('Unverified Nonce!', 'homey')
			));
			wp_die();
		}

		if ( empty( $_POST['message'] ) ) {
			echo json_encode( array(
				'success' => false,
				'msg' => esc_html__('Please write something in message', 'homey')
			));
			wp_die();
		}

		if ( isset( $_POST['thread_id'] ) && !empty( $_POST['thread_id'] ) && isset( $_POST['message'] ) && !empty( $_POST['message'] ) ) {
			$message_attachments = Array ();
			$thread_id = intval($_POST['thread_id']);
			$message = sanitize_textarea_field($_POST['message']);

			if ( isset( $_POST['listing_image_ids'] ) && sizeof( $_POST['listing_image_ids'] ) != 0 ) {
				$message_attachments = $_POST['listing_image_ids'];
			}
			$message_attachments = serialize( $message_attachments );
			$message_id = apply_filters( 'homey_thread_message', $thread_id, $message, $message_attachments );

			if ( $message_id ) {

				echo json_encode(
					array(
						'success' => true,
						'url' => homey_get_template_link_2('template/dashboard-messages.php') . '?' . http_build_query( array( 'thread_id' => $thread_id, 'success' => true ) ),
						'msg' => esc_html__("Thread success fully created!", 'homey')
					)
				);

				wp_die();

			}

		}

		echo json_encode(
			array(
				'success' => false,
				'url' => homey_get_template_link_2('template/dashboard-messages.php') . '?' . http_build_query( array( 'thread_id' => $thread_id, 'success' => false ) ),
				'msg' => esc_html__("Some errors occurred! Please try again.", 'homey')
			)
		);

		wp_die();

	}

}

add_filter( 'homey_start_thread', 'homey_start_thread_filter', 1, 9 );

if ( !function_exists( 'homey_start_thread_filter' ) ) {

	function homey_start_thread_filter( $data ) {

		global $wpdb, $current_user;

		wp_get_current_user();
		$sender_id =  intval($current_user->ID);
		$user_id = intval($data['user_id']);
		$listing_id = intval($data['listing_id']);
		$receiver_id = intval($data['receiver_id']);
		$table_name = $wpdb->prefix . 'homey_threads';

		if( !empty($user_id) ) {
			$sender_id = $user_id;
		}

		$id = $wpdb->insert(
			$table_name,
			array(
				'sender_id' => $sender_id,
				'receiver_id' => $receiver_id,
				'listing_id' => $listing_id,
				'time'	=> current_time( 'mysql' )
			),
			array(
				'%d',
				'%d',
				'%d',
				'%s'
			)
		);

		return $wpdb->insert_id;

	}

}

add_filter( 'homey_thread_message', 'homey_thread_message_filter', 3, 9 );

if ( !function_exists( 'homey_thread_message_filter' ) ) {

	function homey_thread_message_filter( $thread_id, $message, $attachments ) {

		global $wpdb, $current_user;

		if ( is_array( $attachments ) ) {
			$attachments = serialize( $attachments );
		}

		wp_get_current_user();
		$created_by =  $current_user->ID;
		$table_name = $wpdb->prefix . 'homey_thread_messages';

		$message = stripslashes($message);
		$message = htmlentities($message);

		$message_id = $wpdb->insert(
			$table_name,
			array(
				'created_by' => $created_by,
				'thread_id' => intval($thread_id),
				'message' => sanitize_textarea_field($message),
				'attachments' => $attachments,
				'time' => current_time( 'mysql' )
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s'
			)
		);

		$tabel = $wpdb->prefix . 'homey_threads';
		$wpdb->update(
			$tabel,
			array(  'seen' => 0 ),
			array( 'id' => $thread_id ),
			array( '%d' ),
			array( '%d' )
		);

		$message_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $tabel 
            WHERE id = %d
            ", 
            $thread_id
        );

		$homey_thread = $wpdb->get_row( $message_query );
        $receiver_id  = $homey_thread->receiver_id;
		$sender_id    = $homey_thread->sender_id;

		if($created_by != $receiver_data){
			$receiver_data = get_user_by( 'id', $receiver_id );
			apply_filters( 'homey_message_email_notification', $thread_id, $message, $receiver_data->user_email, $created_by );
		}
		
		if($created_by != $sender_id){
			$sender_data = get_user_by( 'id', $sender_id );
			apply_filters( 'homey_message_email_notification', $thread_id, $message, $sender_data->user_email, $created_by );
		}
		
		return $message_id;

	}

}

if ( !function_exists( 'homey_is_user_online' ) ) {

	function homey_is_user_online( $user_id ) {

		// get the online users list
		$logged_in_users = get_transient('users_online');

		// online, if (s)he is in the list and last activity was less than 15 minutes ago
		return isset($logged_in_users[$user_id]) && ($logged_in_users[$user_id] > (current_time('timestamp') - (15 * 60)));

	}

}

add_action( 'wp', 'homey_update_online_users_status' );

if ( !function_exists( 'homey_update_online_users_status' ) ) {

	function homey_update_online_users_status() {

		if ( is_user_logged_in() ) {

			if ( ( $logged_in_users = get_transient( 'users_online' ) ) === false ) $logged_in_users = array();

			$current_user = wp_get_current_user();
			$current_user = $current_user->ID;
			$current_time = current_time('timestamp');

			if ( !isset( $logged_in_users[ $current_user ] ) || ( $logged_in_users[ $current_user ] < ( $current_time - ( 15 * 60 ) ) ) ) {
				$logged_in_users[ $current_user ] = $current_time;
				set_transient( 'users_online', $logged_in_users, 30 * 60 );
			}

		}

	}

}

add_action( 'wp_logout', 'homey_update_logout_users_status' );

if ( !function_exists( 'homey_update_logout_users_status' ) ) {

	function homey_update_logout_users_status() {

		if ( ( $logged_in_users = get_transient( 'users_online' ) ) === false ) $logged_in_users = array();

		$current_user = wp_get_current_user();
		$current_user = $current_user->ID;
		unset( $logged_in_users[ $current_user ] );
		set_transient( 'users_online', $logged_in_users, 30 * 60 );

	}

}

if ( !function_exists( 'homey_messages_notification' ) ) {

	function homey_messages_notification( $class = 'msg-alert' ) {

		global $wpdb;

		$notification = 'none';
		$current_user = wp_get_current_user();
		$userID = $current_user->ID;
		$tabel = $wpdb->prefix . 'homey_threads';

		$homey_threads = $wpdb->get_results(
			"
			SELECT * 
			FROM $tabel
			WHERE seen = '0' AND sender_delete = 0 AND receiver_delete = 0 AND ( sender_id = $userID OR receiver_id = $userID )
			"
		);

		if ( sizeof( $homey_threads ) != 0 ) {

			$tabel = $wpdb->prefix . 'homey_thread_messages';

			foreach ( $homey_threads as $thread ) {

				$thread_id = $thread->id;
				$last_message = $wpdb->get_row(
					"SELECT * 
					FROM $tabel
					WHERE sender_delete = 0 AND receiver_delete = 0 AND thread_id = $thread_id
					ORDER BY id DESC"
				);

				if ( $userID != $last_message->created_by ) {
					$notification = 'block';
				}

			}
		}

		return '<span class="' . $class . '" style="display: ' . $notification . ';"></span>';
		
	}

}

if ( !function_exists( 'homey_update_message_status' ) ) {

	function homey_update_message_status( $current_user_id = 0, $thread_id = 0 ) {

		if ( $current_user_id != 0 && $thread_id != 0 ) {

			global $wpdb;

			$table_thread_messages = $wpdb->prefix . 'homey_thread_messages';

			$sql = $wpdb->prepare( 
				"
					SELECT * 
					FROM $table_thread_messages 
					WHERE thread_id = %d
					ORDER BY id DESC
				", 
				$thread_id
			);

			$last_message = $wpdb->get_row($sql);

			if ( $current_user_id != $last_message->created_by ) {

				$tabel = $wpdb->prefix . 'homey_threads';
				$wpdb->update(
					$tabel,
					array(  'seen' => 1 ),
					array( 'id' => $thread_id ),
					array( '%d' ),
					array( '%d' )
				);

			}

		}

	}

}

add_action( 'wp_ajax_nopriv_homey_chcek_messages_notifications', 'homey_chcek_messages_notifications' );
add_action( 'wp_ajax_homey_chcek_messages_notifications', 'homey_chcek_messages_notifications' );

if ( !function_exists( 'homey_chcek_messages_notifications' ) ) {

	function homey_chcek_messages_notifications() {

		$notification_data = array(
			'success' => true,
			'notification' => false
		);

		global $wpdb;

		$notification = 'none';
		$current_user = wp_get_current_user();
		$userID = $current_user->ID;
		$tabel_threads = $wpdb->prefix . 'homey_threads';

		$sql_thread = $wpdb->prepare( 
			"
			SELECT * 
			FROM $tabel_threads 
			WHERE seen = '0' AND sender_delete = 0 AND receiver_delete = 0 AND ( sender_id = %d OR receiver_id = %d )
			", 
			$userID,
			$userID
		);

		$homey_threads = $wpdb->get_results($sql_thread);

		if ( sizeof( $homey_threads ) != 0 ) {

			$tabel_thread_messages = $wpdb->prefix . 'homey_thread_messages';

			foreach ( $homey_threads as $thread ) {

				$thread_id = $thread->id;
				$sql_thread_messages = $wpdb->prepare( 
					"
					SELECT * 
					FROM $tabel_thread_messages 
					WHERE sender_delete = 0 AND receiver_delete = 0 AND thread_id = %d 
					ORDER BY id DESC
					", 
					$thread_id
				);

				$last_message = $wpdb->get_row($sql_thread_messages);

				if ( $userID != $last_message->created_by ) {
					$notification_data['notification'] = true;
					break;
				}

			}
		}

		echo json_encode( $notification_data );
		wp_die();

	}

}

add_action( 'wp_ajax_homey_message_attacment_upload', 'homey_message_attacment_upload' );    // only for logged in user
add_action( 'wp_ajax_nopriv_homey_message_attacment_upload', 'homey_message_attacment_upload' );

if( !function_exists( 'homey_message_attacment_upload' ) ) {

	function homey_message_attacment_upload( ) {

		// Check security Nonce
		$verify_nonce = $_REQUEST['verify_nonce'];
		if ( ! wp_verify_nonce( $verify_nonce, 'verify_gallery_nonce' ) ) {
			echo json_encode( array( 'success' => false , 'reason' => 'Invalid nonce!' ) );
			die;
		}

		$submitted_file = $_FILES['messages_upload_file'];
		$uploaded_image = wp_handle_upload( $submitted_file, array( 'test_form' => false ) );

		if ( isset( $uploaded_image['file'] ) ) {
			$file_name          =   basename( $submitted_file['name'] );
			$file_type          =   wp_check_filetype( $uploaded_image['file'] );

			// Prepare an array of post data for the attachment.
			$attachment_details = array(
				'guid'           => $uploaded_image['url'],
				'post_mime_type' => $file_type['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			$attach_id      =   wp_insert_attachment( $attachment_details, $uploaded_image['file'] );
			$attach_data    =   wp_generate_attachment_metadata( $attach_id, $uploaded_image['file'] );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			$thumbnail_url = wp_get_attachment_image_src( $attach_id, 'homey-image350_350' );;
			$fullimage_url  = wp_get_attachment_image_src( $attach_id, 'full' );

			$ajax_response = array(
				'success'   => true,
				'url' => $thumbnail_url[0],
				'attachment_id'    => $attach_id,
				'full_image'    => $fullimage_url[0],
				'file_name'    => basename( $submitted_file['name'] ),
			);

			echo json_encode( $ajax_response );
			die;

		} else {
			$ajax_response = array( 'success' => false, 'reason' => 'Image upload failed!' );
			echo json_encode( $ajax_response );
			die;
		}

	}

}

// homey_remove_message_attachment
add_action( 'wp_ajax_homey_remove_message_attachment', 'homey_remove_message_attachment' );
add_action( 'wp_ajax_nopriv_homey_remove_message_attachment', 'homey_remove_message_attachment' );

if ( !function_exists( 'homey_remove_message_attachment' ) ) {
	function homey_remove_message_attachment() {


		$attachment_removed = false;

		if ( isset($_POST['thumbnail_id'] ) ) {

			$attachment_id = intval( $_POST['thumbnail_id'] );

			if ( $attachment_id > 0 ) {
				$attachment_removed = wp_delete_attachment($attachment_id);
			} elseif ($attachment_id > 0) {
				if( false == wp_delete_attachment( $attachment_id )) {
					$attachment_removed = false;
				} else {
					$attachment_removed = true;
				}
			}
		}

		$ajax_response = array(
			'attachment_remove' => $attachment_removed,
		);
		echo json_encode($ajax_response);
		wp_die();

	}
}

add_filter( 'homey_message_email_notification', 'homey_message_email_notification_filter', 4, 9 );

if ( !function_exists( 'homey_message_email_notification_filter' ) ) {

	function homey_message_email_notification_filter( $thread_id, $message, $email, $created_by ) {

		ob_start();

		$url_query = array( 'thread_id' => $thread_id, 'seen' => true );
		$thread_link = homey_get_template_link_2('template/dashboard-messages.php');
		$thread_link = add_query_arg( $url_query, $thread_link );
		$sender_name = get_the_author_meta( 'display_name', $created_by );

		?>
		<table style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;width:100%;margin:0;padding:0">
			<tbody>
			<tr style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0">
				<td style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0">
					<p style="margin:0 0 15px;padding:0"><?php esc_html_e( 'You have a new message on', 'homey' ); ?> <b><?php echo esc_attr( get_option('blogname') );?></b> <?php echo esc_html_e('from', 'homey');?> <i><?php echo esc_attr($sender_name); ?></i></p>
					<div style="padding-left:20px;margin:0;border-left:2px solid #ccc;color:#888">
						<p><?php echo ''.$message; ?></p>
					</div>
					<p style="padding:20px 0 0 0;margin:0">
						<a style="color:#15bcaf" href="<?php echo esc_url( $thread_link ); ?>">
							<?php echo esc_html__('Click here to see message on website dashboard.', 'homey');?>
						</a>
					</p>
				</td>
			</tr>
			</tbody>
		</table>

		<?php
		$data = ob_get_contents();

		ob_clean();

		$subject = esc_html__( 'You have new message!', 'homey' );

		homey_send_emails( $email, $subject, $data );

	}

}

add_filter( 'homey_thread_email_notification', 'homey_thread_email_notification_filter', 3, 9 );

if ( !function_exists( 'homey_thread_email_notification_filter' ) ) {

	function homey_thread_email_notification_filter( $thread_id, $message, $email ) {

		global $current_user;

		wp_get_current_user();

		$current_user_id =  $current_user->ID;

		$sender_name = ucfirst( get_the_author_meta( 'display_name', $current_user_id ) );

		ob_start();

		$custom_logo = homey_option( 'custom_logo', false, 'url' );
		?>
		<table class="m_-2338629203816253595body-wrap" style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;width:100%;margin:0;padding:30px" bgcolor="#F6F6F6">
			<tbody>
			<tr style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0">
				<td style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0"><br>
				</td>
				<td class="m_-2338629203816253595container" style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;clear:both!important;display:block!important;max-width:600px!important;margin:0 auto;padding:40px;border:1px solid #eee" bgcolor="#FFFFFF">
					<div class="m_-2338629203816253595content" style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;display:block;max-width:600px;margin:0 auto;padding:0">
						<table style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;width:100%;margin:0;padding:0">
							<tbody>
							<?php if( !empty( $custom_logo ) ) { ?>
								<tr style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0">
									<td style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0">
										<div style="margin:0 0 30px"><img style="width:auto;height:30px" alt="<?php esc_attr_e('Favethemes', 'homey'); ?>" src="<?php echo esc_url( $custom_logo ); ?>" class="CToWUd"></div>
									</td>
								</tr>
							<?php } ?>
							<tr style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0">
								<td style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0">
									<p style="margin:0 0 15px;padding:0"><?php esc_html_e( 'You have received a message from:', 'homey' ); ?> <i><?php echo esc_attr($sender_name); ?></i></p>
									<div style="padding-left:20px;margin:0;border-left:2px solid #ccc;color:#888">
										<p><?php echo ''.$message; ?></p>
										<p><br></p>
									</div>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</td>
				<td style="font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;font-size:100%;line-height:1.6em;margin:0;padding:0"><br></td>
			</tr>
			</tbody>
		</table>
		<?php

		$data = ob_get_contents();

		ob_clean();

		$subject = sprintf( esc_html__('New message sent by %s using agent contact form at %s', 'homey'), $sender_name, get_bloginfo('name') );

		homey_send_emails( $email, $subject, $data );

	}

}

add_action( 'wp_ajax_homey_message_delete', 'homey_message_delete' );    // only for logged in user
if ( !function_exists( 'homey_message_delete' ) ) {
	function homey_message_delete()
	{

		$ajax_response = array( 'success' => false );

		if ( isset($_POST['message_id'] ) ) {

			global $wpdb;

			$tabel = $wpdb->prefix . 'homey_thread_messages';
			$wpdb->delete( $tabel, array( 'id' => $_POST['message_id'] ), array( '%d' ) );

			$ajax_response = array( 'success' => true );

		}

		echo json_encode($ajax_response);
		wp_die();

	}
}

add_action( 'wp_ajax_homey_delete_message_thread', 'homey_delete_message_thread' );    // only for logged in user
if ( !function_exists( 'homey_delete_message_thread' ) ) {
	function homey_delete_message_thread() {
		global $wpdb, $current_user;
		wp_get_current_user();
		$userID =  $current_user->ID;
		$column = '';

		$thread_id = intval($_POST['thread_id']);
		$sender_id = intval($_POST['sender_id']);
		$receiver_id = intval($_POST['receiver_id']);

		if($userID == $sender_id) {
			$column = 'sender_delete';
		} elseif($userID == $receiver_id) {
			$column = 'receiver_delete';
		}


		if(!empty($column) && !empty($thread_id)) {
			$tabel = $wpdb->prefix . 'homey_threads';
			$wpdb->update(
				$tabel,
				array(  $column => 1 ),
				array( 'id' => $thread_id ),
				array( '%d' ),
				array( '%d' )
			);
		}

		echo json_encode(
			array(
				'success' => true,
				'msg' => ''
			)
		);
		wp_die();

	}
}

add_action( 'wp_ajax_homey_delete_message', 'homey_delete_message' );    // only for logged in user
if ( !function_exists( 'homey_delete_message' ) ) {
	function homey_delete_message() {
		global $wpdb, $current_user;
		wp_get_current_user();
		$userID =  $current_user->ID;
		$column = '';
		$permanent_delete = false;
		$tabel = $wpdb->prefix . 'homey_thread_messages';

		$message_id = $_POST['message_id'];
		$created_by = $_POST['created_by'];

		if($userID == $created_by) {
			$column = 'sender_delete';
			$permanent_delete = true;
		} else {
			$column = 'receiver_delete';
		}

		if($permanent_delete) {

			if(!empty($message_id)) {
				$wpdb->delete( $tabel, array( 'id' => $_POST['message_id'] ), array( '%d' ) );
			}

		} else {	
			if(!empty($column) && !empty($message_id)) {
				$wpdb->update(
					$tabel,
					array(  $column => 1 ),
					array( 'id' => $message_id ),
					array( '%d' ),
					array( '%d' )
				);
			}
		}

		echo json_encode(
			array(
				'success' => true,
				'msg' => ''
			)
		);
		wp_die();

	}
}


if ( !function_exists( 'homey_chcek_reservation_thread' ) ) {

	function homey_chcek_reservation_thread($reserv_id) {

		global $wpdb;
		$tabel_threads = $wpdb->prefix . 'homey_threads';

		$sql_thread = $wpdb->prepare( 
			"
			SELECT * 
			FROM $tabel_threads 
			WHERE listing_id = %d
			", 
			$reserv_id
		);

		$homey_threads = $wpdb->get_row($sql_thread);
		if ( null !== $homey_threads ) {
		  // do something with the link 
		  return $homey_threads->id;
		} else {
		  // no link found
		  return '';
		}

	}
}
