<?php
global $current_user, $wpdb, $userID, $homey_threads;
$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'DESC';

if ( sizeof( $homey_threads ) != 0 ) :
	foreach ( $homey_threads as $thread ) {

	$sender_id = $thread->sender_id;
	$receiver_id = $thread->receiver_id;

	$delete = 0;
	if($userID == $sender_id) {
		$delete = $thread->sender_delete;
	} elseif($userID == $receiver_id) {
		$delete = $thread->receiver_delete;
	} else {
		if($thread->sender_delete && $thread->receiver_delete) {
			$delete = 1;
		}

	}

	$user_can_reply = false;
	if($sender_id == $userID || $receiver_id == $userID || homey_is_admin()) {
	    $user_can_reply = true;
	}

	if($delete != 1) {

	$thread_class = 'msg-unread new-message';
	$tabel = $wpdb->prefix . 'homey_thread_messages';
	$thread_id = $thread->id;


	$homey_sql = $wpdb->prepare(
		"
			SELECT * 
			FROM $tabel 
			WHERE thread_id = %d
			ORDER BY id " .$sort,
		$thread_id
	);

	$last_message = $wpdb->get_row($homey_sql);

	// $author_picture_id =  get_the_author_meta( 'homey_author_picture_id' , $sender_id );
	$user_for_photo_id = $sender_id;
    if($sender_id == $userID){
        // $author_picture_id =  get_the_author_meta( 'homey_author_picture_id' , $receiver_id );
        $user_for_photo_id = $receiver_id;
    }
	// $image_array = wp_get_attachment_image_src( $author_picture_id, array('40', '40'), "", array( "class" => 'img-circle' ) );

	$homey_current_user_info = homey_get_author_by_id('60', '60', 'img-circle', $user_for_photo_id);
    $user_custom_picture = $homey_current_user_info['photo'];

	if( empty($user_custom_picture) ) {
		$user_custom_picture = get_template_directory_uri().'/images/profile-avatar.png';
	}

	if($user_can_reply) {
		$url_query = array( 'thread_id' => $thread_id, 'seen' => true );
	} else {
		$url_query = array( 'thread_id' => $thread_id);
	}

	if ( $last_message->created_by == $userID || $thread->seen ) {
		$thread_class = '';
		unset( $url_query['seen'] );
	}

	$thread_link = homey_get_template_link_2('template/dashboard-messages.php');
	$thread_link = add_query_arg( $url_query, $thread_link );

	$sender_first_name  =  get_the_author_meta( 'first_name', $sender_id );
	$sender_last_name  =  get_the_author_meta( 'last_name', $sender_id );
	$sender_display_name = get_the_author_meta( 'display_name', $sender_id );

	if($sender_id == $userID){
        $sender_first_name  =  get_the_author_meta( 'first_name', $receiver_id );
        $sender_last_name  =  get_the_author_meta( 'last_name', $receiver_id );
        $sender_display_name = get_the_author_meta( 'display_name', $receiver_id );
    }

	if( !empty($sender_first_name) && !empty($sender_last_name) ) {
		$sender_display_name = $sender_first_name.' '.$sender_last_name;
	}

	$last_sender_first_name  =  get_the_author_meta( 'first_name', $last_message->created_by );
	$last_sender_last_name  =  get_the_author_meta( 'last_name', $last_message->created_by );
	$last_sender_display_name = get_the_author_meta( 'display_name', $last_message->created_by );
	if( !empty($last_sender_first_name) && !empty($last_sender_last_name) ) {
		$last_sender_display_name = $last_sender_first_name.' '.$last_sender_last_name;
	}

	?>

    <div class="table-row <?php echo esc_attr($thread_class); ?>">
	    <div class="table-col clearfix">
	        <div class="media user-list-media">
	            <div class="media-left">
	                <span class="media-signal">
	                    <!-- <img src="<?php echo esc_url( $user_custom_picture ); ?>" class="img-circle" alt="<?php esc_attr_e('Image', 'homey'); ?>" width="40" height="40"> -->
	                    <?php echo $user_custom_picture; ?>
	                </span>
	            </div>
	            <div class="media-body">
	                <strong><?php echo ucfirst( $sender_display_name ); ?></strong><br>
	                <?php echo date_i18n( homey_convert_date(homey_option('homey_date_format')).' '.get_option('time_format'), strtotime( $last_message->time ) ); ?>
	            </div>
	        </div>
	    </div>
	    <div class="table-col clearfix short-message-block">
	        <strong><?php esc_html_e('Message', 'homey');?></strong><br>
            <i><?php echo esc_attr($last_sender_display_name); ?>:</i> <?php echo str_replace("\\", "", html_entity_decode($last_message->message)); ?>
	    </div>
	    <div class="table-col clearfix">
	        <div class="custom-actions">
	        	<?php if($user_can_reply) { ?>
	        	<button class="homey_delete_msg_thread btn-action" data-thread-id="<?php echo intval($thread_id); ?>" data-sender-id="<?php echo intval($sender_id); ?>" data-receiver-id="<?php echo intval($receiver_id); ?>" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e('Delete', 'homey'); ?>"><i class="fa fa-trash"></i></button>
	        	<?php } ?>
	            <a href="<?php echo esc_url( $thread_link ).'#message-'.intval($last_message->id); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php esc_attr_e('View', 'homey');?>"><i class="fa fa-reply"></i></a>
	        </div>
	    </div>
	</div><!-- .table-row -->


<?php }
} endif; ?>
