<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 08/12/16
 * Time: 8:13 PM
 */

global $wpdb, $current_user;

wp_get_current_user();
$current_user_id =  $current_user->ID;
$tabel = $wpdb->prefix . 'homey_threads';
$thread_id = intval($_REQUEST['thread_id']);
$user_status = 'Offline';

$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'ASC';

if ( isset( $_GET['seen'] ) && $_GET['seen'] == 1 ) {
    homey_update_message_status( $current_user_id, $thread_id );
}

$sql_thread = $wpdb->prepare(
    "
    SELECT * 
    FROM $tabel 
    WHERE id = %d
    ",
    $thread_id
);

$homey_thread = $wpdb->get_row($sql_thread);

$homey_thread_messages = $wpdb->prefix . 'homey_thread_messages';

$sql_messages = $wpdb->prepare(
    "
    SELECT * 
    FROM $homey_thread_messages 
    WHERE thread_id = %d
    ORDER BY id ".$sort,
    $thread_id
);

$homey_messages = $wpdb->get_results($sql_messages);

$thread_author = $homey_thread->sender_id;
$thread_sender_delete = $homey_thread->sender_delete;
$thread_receiver_delete = $homey_thread->receiver_delete;

if ( $thread_author == $current_user_id ) {
    $thread_author = $homey_thread->receiver_id;
}

$sender_id = $homey_thread->sender_id;
$receiver_id = $homey_thread->receiver_id;

$user_can_reply = false;
if($sender_id == $current_user_id || $receiver_id == $current_user_id || homey_is_admin()) {
    $user_can_reply = true;
}


$thread_author_first_name  =  get_the_author_meta( 'first_name', $thread_author );
$thread_author_last_name  =  get_the_author_meta( 'last_name', $thread_author );
$thread_author_display_name = get_the_author_meta( 'display_name', $thread_author );
if( !empty($thread_author_first_name) && !empty($thread_author_last_name) ) {
    $thread_author_display_name = $thread_author_first_name.' '.$thread_author_last_name;
}

// $author_picture_id =  get_the_author_meta( 'homey_author_picture_id' , $thread_author );
// $image_array = wp_get_attachment_image_src( $author_picture_id, array('40', '40'), "", array( "class" => 'img-circle' ) );

$homey_author_info = homey_get_author_by_id('60', '60', 'img-circle', $thread_author);
$user_custom_picture = $homey_author_info['photo'];

if( !$user_custom_picture ) {
    $user_custom_picture = get_template_directory_uri().'/images/profile-avatar.png';
}
?>

<?php if ( isset( $_GET['success'] ) && $_GET['success'] == true ) { ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
        <?php esc_html_e( 'The message has been sent.', 'homey' ); ?>
    </div>
<?php } ?>

<?php if ( isset( $_GET['success'] ) && $_GET['success'] == false ) { ?>
    <div class="alert alert-error alert-dismissible" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
        <?php esc_html_e( 'Oopps something getting wrong, please try again!', 'homey' ); ?>
    </div>
<?php } ?>

<?php
if( $user_can_reply ) { ?>
<div class="messages-area-user-info">
    <div class="messages-area-user-status">
        <div class="media">
            <div class="media-left">
                <a class="media-object">
                    <?php echo  $user_custom_picture ; ?>
                    <!-- <img src="<?php echo esc_url( $user_custom_picture ); ?>" class="img-circle" alt="<?php echo esc_attr($thread_author_display_name); ?>"> -->
                </a>
            </div>
            <div class="media-body media-middle">
                <div class="msg-user-info">
                    <div class="msg-user-left">
                        <div><strong><?php echo ucfirst( $thread_author_display_name ); ?></strong> <?php esc_html_e('on', 'homey'); ?> <strong><?php echo get_the_title( $homey_thread->listing_id ); ?></strong></div>
                        <div class="user-status">
                            <?php
                            if ( homey_is_user_online( $thread_author ) ) {
                                echo '<i class="fa fa-circle text-success" aria-hidden="true"></i> '.esc_html__('Status:', 'homey').' <strong>'.esc_html__('Online', 'homey').'</strong>';
                            } else {
                                echo '<i class="fa fa-circle text-danger" aria-hidden="true"></i> '.esc_html__('Status:', 'homey').' <strong>'.esc_html__('Offline', 'homey').'</strong>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="messages-area">
    
    <div class="msgs-reply-list">
        
        <?php foreach ( $homey_messages AS $message ) {

            $message_class = '';
            $message_id = $message->id;
            $message_author = $message->created_by;
            
          $message_author_last_name  =  get_the_author_meta( 'last_name', $message_author );
          $message_author_display_name = get_the_author_meta( 'display_name', $message_author );
          if( !empty($message_author_first_name) && !empty($message_author_last_name) ) {
            $message_author_display_name = $message_author_first_name.' '.$message_author_last_name;
          }
          $message_author_name = ucfirst( $message_author_display_name );
           
            if ( $message_author == $current_user_id ) {
                $message_author_name = esc_html__( 'Me', 'homey' );
                $message_class = 'msg-me';
            }

            // $author_picture_id =  get_the_author_meta( 'homey_author_picture_id' , $message_author );
            // $image_array = wp_get_attachment_image_src( $author_picture_id, array('40', '40'), "", array( "class" => 'img-circle' ) );

             $message_owner_info = homey_get_author_by_id('60', '60', 'img-circle', $message_author);
$message_author_picture = $message_owner_info['photo'];

            if ( empty( @$message_author_picture )) {
                $message_author_picture = get_template_directory_uri().'/images/profile-avatar.png';
            }

            if($current_user_id == $message_author) {
                $delete = $message->sender_delete;
            } else {
                $delete = $message->receiver_delete;
            }

            if($delete != 1) {
            ?>
            <div id="message-<?php echo intval($message_id); ?>" class="media <?php echo esc_attr($message_class); ?>">
                <div class="media-left">
                    <a href="#" class="media-object">
                        <?php echo $message_author_picture; ?>
                    </a>
                </div>

                <div class="media-body">
                    <div class="msg-user-info">
                        <div class="msg-user-left">
                            <div><strong><?php echo esc_attr($message_author_name); ?></strong>
                                <span class="message-date">
                                    <span><i class="fa fa-calendar"></i> <?php echo date_i18n( homey_convert_date(homey_option('homey_date_format')), strtotime( $message->time ) ); ?> </span>
                                    <span><i class="fa fa-clock-o"></i>  <?php echo date_i18n( get_option('time_format'), strtotime( $message->time ) ); ?> </span>
                                </span>

                            </div>
                        </div>
                        <?php if($user_can_reply == 1) { ?>
                        <div class="custom-actions">
                            <button class="homey_delete_message btn-action" data-message-id="<?php echo intval($message_id); ?>" data-created-by="<?php echo intval($message_author); ?>" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e('Delete', 'homey'); ?>"><i class="fa fa-trash"></i></button>
                        </div>
                        <?php } ?>
                    </div>

                    <p><?php echo str_replace("\\","", wp_specialchars_decode($message->message)); ?></p>

                    <?php
                    if ( !empty( $message->attachments ) ) {

                        $attachments = unserialize( $message->attachments );

                        if ( sizeof( $attachments ) ) {

                            echo '<ul>';

                            foreach ( $attachments AS $attachment ) {

                                $attachment_url  = wp_get_attachment_url( $attachment );

                                echo '<li> <a href="' . esc_url($attachment_url) . '" target="_blank"> ' . get_the_title( $attachment ) . ' </a> </li>';
                            }

                            echo '</ul>';

                        }

                    }
                    ?>

                </div>

            </div>
            <?php }
        }?>
        
    </div>
    <div class="media msg-send-block">
        
        <?php
        if($user_can_reply == 1) {
        if($thread_sender_delete != 1 && $thread_receiver_delete != 1) { ?>
        <div class="media-left">
            <div class="media-object">
                <?php
                // $current_user_picture_id =  intval(get_the_author_meta( 'homey_author_picture_id' , $current_user_id ));

                $homey_current_user_info = homey_get_author_by_id('60', '60', 'img-circle', $current_user_id);
                $author_photo = $homey_current_user_info['photo'];

                // $image_array = wp_get_attachment_image_src( $current_user_picture_id, array('40', '40'), "", array( "class" => 'img-circle' ) );

                if ( empty( @$author_photo )) {
                    $current_user_picture = get_template_directory_uri().'/images/profile-avatar.png'; 
                    ?>

<img src="<?php echo esc_url($current_user_picture); ?>" class="img-circle" alt="<?php the_author_meta( 'display_name', $current_user_id ) ?>">
                
                <?php }else{
                    echo $current_user_picture = $author_photo;
                }

                ?>
                
            </div>
        </div>
        <div class="media-body">
            <h4 class="media-heading"><?php echo esc_html__('Reply', 'homey'); ?></h4>
            <form class="form-msg" method="post">
                <input type="hidden" name="start_thread_message_form_ajax" value="<?php echo wp_create_nonce('start-thread-message-form-nonce'); ?>"/>
                <input type="hidden" name="thread_id" value="<?php echo intval($thread_id); ?>"/>
                <input type="hidden" name="action" value="homey_thread_message">

                <div class="msg-type-block">
                    <div class="arrow"></div>
                    <textarea name="message" id="message" rows="5" class="form-control" placeholder="<?php esc_attr_e( 'Type your message here...', 'homey' ); ?>"></textarea>

                    <div class="msg-attachment-row">
                        <div class="msg-attachment">
                            <ul id="listing-thumbs-container" class="list-inline">
                                <li class="new-attach" id="thread-message-attachment">
                                    <div class="attach-icon new-attachment">
                                        <i class="fa fa-paperclip"></i>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div id="plupload-container"></div>
                        <div id="errors-log"></div>
                    </div>

                </div>
                <div class="form-msg-btns">
                    <button class="btn btn-success start_thread_message_form"><?php echo esc_html__('Send', 'homey'); ?></button>
                </div>
                <div class="messages-notification text-right"></div>
            </form>
        </div>
        <?php
        }else{
          echo '<div id="message_auth" class="error notice is-dismissible"><p>'.esc_html_e("This message thread is deleted from the owner, so you can't reply this.", 'homey').'</p></div>';
        }
    }else{
          echo '<div id="message_auth" class="error notice is-dismissible"><p>'.esc_html_e('You are not allowed to access this', 'homey').'</p></div>';
        }?>
    </div><!-- end .msg-send-block -->

</div>
<?php } else { ?>

    <div class="messages-area">
        <p><?php esc_html_e('You are not allowed to access this', 'homey'); ?></p>
    </div>

<?php } ?>
