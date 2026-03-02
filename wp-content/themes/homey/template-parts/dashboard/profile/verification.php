<?php
global $userID, $user_email;
$user_document_id = get_the_author_meta( 'homey_user_document_id' , $userID );
$is_doc_verified_request = get_the_author_meta( 'id_doc_verified_request' , $userID );
$is_doc_verified = get_the_author_meta( 'doc_verified' , $userID );
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"> <?php esc_html_e('Verify Your Information', 'homey'); ?> </h2>
        </div>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-9">
                <label for="useremail"> <?php esc_html_e('Email Address', 'homey'); ?> </label>
                <div class="form-group">
                    <input class="form-control" name="useremail" id="useremail" value="<?php echo esc_attr($user_email); ?>" placeholder="<?php esc_html_e('your@email.com', 'homey'); ?>" disabled>
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <div class="verified">
                    <span class="btn btn-full-width" href="#"><i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php esc_html_e('Verified', 'homey'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div><!-- block -->

<div id="id_verify_mgs"></div>

<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php esc_html_e('Upload your ID', 'homey'); ?></h2>
        </div>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-2">
                <div id="homey_user_doc" class="profile-image">
                    <?php
                    if( !empty( $user_document_id ) ) {
                        $user_document_id = intval( $user_document_id );
                        if ( $user_document_id ) {
                            echo homey_user_document_for_verification($user_document_id);
                            echo '<input type="hidden" class="profile-doc-id" id="profile-doc-id" name="profile-doc-id" value="' . esc_attr( $user_document_id ).'"/>';
                        }
                    } else {
                        echo '<img src="http://place-hold.it/100x100" width="100" height="100" alt="profile image">';
                    }
                    ?>
                </div>
            </div>

            <div class="col-sm-7">
                <p class="profile-image-note"><strong><?php esc_html_e('Choose an image from your computer', 'homey'); ?></strong><br>
                <?php esc_html_e('Upload your ID, Passport or Driver License', 'homey'); ?></p>
                <button id="select_user_verify_id" type="button" class="btn btn-primary btn-xs-full-width"><?php esc_html_e('Browse', 'homey'); ?></button>
                <button id="delete_verify_id" type="button" class="btn btn-grey-outlined btn-xs-full-width"><?php esc_html_e('Delete', 'homey'); ?></button> 

                <div id="upload_errors"></div>
                <div id="plupload-container"></div>   
            </div>
            <div class="col-sm-3">
                <?php if($is_doc_verified) { ?>
                    <div class="verified">
                        <span class="btn btn-full-width" href="#"><i class="fa fa-check-circle-o" aria-hidden="true"></i> <?php esc_html_e('Verified', 'homey'); ?></span>
                    </div>
                <?php } else { ?>
                        <?php if($is_doc_verified_request) { ?>
                            <div class="not-verified">
                                <span class="btn btn-full-width" href="#"> <?php esc_html_e('Pending', 'homey'); ?></span>
                            </div>
                        <?php } ?>
                        <button id="btn_verify_id" class="btn btn-primary btn-full-width" href="#"><?php esc_html_e('Verify Your ID', 'homey'); ?></button>
                <?php } ?>
            </div>
        </div>
    </div>
</div><!-- block -->