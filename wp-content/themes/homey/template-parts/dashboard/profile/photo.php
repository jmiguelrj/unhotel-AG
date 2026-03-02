<?php
global $userID, $user_email, $homey_local, $author_info;
$author_picture_id = get_the_author_meta( 'homey_author_picture_id' , $userID );
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__('Photo', 'homey'); ?></h2>
        </div>
        
        <div class="block-right">
            <a href="<?php echo esc_url($author_info['link']); ?>"><strong><?php echo esc_attr($homey_local['view_profile']); ?></strong></a>
        </div>
        
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-2">
                <div id="homey_profile_photo" class="profile-image">
                    <?php
                    if( !empty( $author_picture_id ) ) {
                        $author_picture_id = intval( $author_picture_id );
                        if ( $author_picture_id ) {
                            echo ''.$author_info['photo'];
                            echo '<input type="hidden" class="profile-pic-id" id="profile-pic-id" name="profile-pic-id" value="' . esc_attr( $author_picture_id ).'"/>';
                        }
                    } else {
                        echo ''.$author_info['photo'];
                    }
                    ?>
                    
                </div>
            </div>
            <div class="col-sm-10">
                <p class="profile-image-note"><strong><?php echo esc_html__('Choose an image from your computer', 'homey'); ?></strong><br>
                <?php echo esc_html__('Minimum size 100 x 100 px', 'homey'); ?></p>
                <button id="select_user_profile_photo" type="button" class="btn btn-primary btn-xs-full-width"><?php echo esc_html__('Browse', 'homey'); ?></button>
                <button type="button" class="btn btn-grey-outlined btn-xs-full-width delete_user_photo"><?php echo esc_html__('Delete', 'homey'); ?></button>    
            </div>
            <div id="upload_errors"></div>
            <div id="plupload-container"></div>
        </div>
    </div>
</div>