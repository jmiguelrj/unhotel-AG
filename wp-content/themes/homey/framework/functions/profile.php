<?php
/*-----------------------------------------------------------------------------------*/
/*   Upload picture for user profile using ajax
/*-----------------------------------------------------------------------------------*/
if( !function_exists( 'homey_user_picture_upload' ) ) {
    function homey_user_picture_upload( ) {

        // Verify if Nonce is valid
        $user_id = $_REQUEST['user_id'];
        $verify_nonce = sanitize_text_field($_REQUEST['verify_nonce']);
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_upload_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid request' ) );
            die;
        }

        $homey_user_image = $_FILES['homey_file_data_name'];
        $homey_wp_handle_upload = wp_handle_upload( $homey_user_image, array( 'test_form' => false ) );

        if ( isset( $homey_wp_handle_upload['file'] ) ) {
            $file_name  = basename( $homey_user_image['name'] );
            $file_type  = wp_check_filetype( $homey_wp_handle_upload['file'] );

            $uploaded_image_details = array(
                'guid'           => $homey_wp_handle_upload['url'],
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $profile_attach_id      =   wp_insert_attachment( $uploaded_image_details, $homey_wp_handle_upload['file'] );
            $profile_attach_data    =   wp_generate_attachment_metadata( $profile_attach_id, $homey_wp_handle_upload['file'] );
            wp_update_attachment_metadata( $profile_attach_id, $profile_attach_data );

            $thumbnail_url = wp_get_attachment_image_src( $profile_attach_id, 'thumbnail' );
            homey_save_user_photo($user_id, $profile_attach_id);

            echo json_encode( array(
                'success'   => true,
                'url' => $thumbnail_url[0],
                'attachment_id'    => $profile_attach_id
            ));
            die;

        } else {
            echo json_encode( array( 'success' => false, 'reason' => 'Profile Photo upload failed!' ) );
            die;
        }

    }
}
add_action( 'wp_ajax_homey_user_picture_upload', 'homey_user_picture_upload' );

if( !function_exists('homey_save_user_photo')) {
    function homey_save_user_photo($user_id, $pic_id) {
        
        $pic_id = intval($pic_id);
        update_user_meta( $user_id, 'homey_author_picture_id', $pic_id );

    }
}

/*-----------------------------------------------------------------------------------*/
/*   Upload document for verification
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_user_document_upload', 'homey_user_document_upload' );
if( !function_exists( 'homey_user_document_upload' ) ) {
    function homey_user_document_upload( ) {

        // Verify if Nonce is valid
        $user_id = $_REQUEST['user_id'];
        $verify_nonce = $_REQUEST['verify_nonce'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_upload_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid request' ) );
            die;
        }

        $homey_user_image = $_FILES['homey_id_file_data_name'];
        $homey_wp_handle_upload = wp_handle_upload( $homey_user_image, array( 'test_form' => false ) );

        if ( isset( $homey_wp_handle_upload['file'] ) ) {
            $file_name  = basename( $homey_user_image['name'] );
            $file_type  = wp_check_filetype( $homey_wp_handle_upload['file'] );

            $uploaded_image_details = array(
                'guid'           => $homey_wp_handle_upload['url'],
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $profile_attach_id      =   wp_insert_attachment( $uploaded_image_details, $homey_wp_handle_upload['file'] );
            $profile_attach_data    =   wp_generate_attachment_metadata( $profile_attach_id, $homey_wp_handle_upload['file'] );
            wp_update_attachment_metadata( $profile_attach_id, $profile_attach_data );

            $thumbnail_url = wp_get_attachment_image_src( $profile_attach_id, 'thumbnail' );
            homey_save_user_document($user_id, $profile_attach_id);

            echo json_encode( array(
                'success'   => true,
                'url' => $thumbnail_url[0],
                'attachment_id'    => $profile_attach_id
            ));
            die;

        } else {
            echo json_encode( array( 'success' => false, 'reason' => esc_html__('Document upload failed!', 'homey') ) );
            die;
        }

    }
}

if( !function_exists('homey_save_user_document')) {
    function homey_save_user_document($user_id, $pic_id) {
        $pic_id = intval($pic_id);
        update_user_meta( $user_id, 'homey_user_document_id', $pic_id );

    }
}


/*-----------------------------------------------------------------------------------*/
// Remove user photo
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_delete_user_photo', 'homey_delete_user_photo' );
if( !function_exists('homey_delete_user_photo') ) {
    function homey_delete_user_photo() {

        $remove_attachment = false;

        $verify_nonce = $_REQUEST['verify_nonce'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_upload_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid request' ) );
            die;
        }

        if (isset($_POST['attach_id']) && isset($_POST['user_id'])) {
            $thumb_id = intval($_POST['attach_id']);
            $user_id = intval($_POST['user_id']);

            if ( $thumb_id > 0 && $user_id > 0 ) {
                delete_user_meta($user_id, 'homey_author_picture_id', $thumb_id);
                $remove_attachment = wp_delete_attachment($thumb_id);
            } elseif ($thumb_id > 0) {
                if( false == wp_delete_attachment( $thumb_id )) {
                    $remove_attachment = false;
                } else {
                    $remove_attachment = true;
                }
            }
        }

        echo json_encode(array(
            'success' => $remove_attachment,
        ));
        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
// Save host payment 
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_add_host_payment_method', 'homey_add_host_payment_method' );
if( !function_exists('homey_add_host_payment_method') ) {
    function homey_add_host_payment_method() {
        global $current_user;
        wp_get_current_user();
        $userID  = $current_user->ID;

        $payment_type = $_POST['payment_type'];
        $percent = $_POST['percent'];

        if(empty($payment_type)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Please select payment type', 'homey'),
            ));
            wp_die();
        }

        if($payment_type == 'percent' && empty($percent)) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Enter percentage', 'homey'),
            ));
            wp_die();
        }

        update_user_meta($userID, 'host_reservation_payment', $payment_type);
        update_user_meta($userID, 'host_booking_percent', $percent);

        echo json_encode(array(
            'success' => true,
            'msg' => esc_html__('Successfully saved', 'homey'),
        ));
        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
// Remove user doc
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_delete_user_doc', 'homey_delete_user_doc' );
if( !function_exists('homey_delete_user_doc') ) {
    function homey_delete_user_doc() {

        $remove_attachment = false;

        $verify_nonce = $_REQUEST['verify_nonce'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_upload_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid request' ) );
            die;
        }

        if (isset($_POST['attach_id']) && isset($_POST['user_id'])) {
            $thumb_id = intval($_POST['attach_id']);
            $user_id = intval($_POST['user_id']);

            if ( $thumb_id > 0 && $user_id > 0 ) {
                delete_user_meta($user_id, 'homey_user_document_id', $thumb_id);
                update_user_meta($user_id, 'doc_verified', 0);
                update_user_meta($user_id, 'id_doc_verified_request', 0);
                $remove_attachment = wp_delete_attachment($thumb_id);
            } elseif ($thumb_id > 0) {
                if( false == wp_delete_attachment( $thumb_id )) {
                    $remove_attachment = false;
                } else {
                    $remove_attachment = true;
                }
            }
        }

        echo json_encode(array(
            'success' => $remove_attachment,
        ));
        wp_die();

    }
}

add_action( 'wp_ajax_homey_send_doc_verification_request', 'homey_send_doc_verification_request' );
if( !function_exists('homey_send_doc_verification_request') ) {
    function homey_send_doc_verification_request() {
        global $current_user;
        wp_get_current_user();
        $userID  = $current_user->ID;

        $document = get_user_meta($userID, 'homey_user_document_id', true);

        if(empty($document)) {
            echo json_encode( array( 'success' => false , 'msg' => esc_html__('Upload your ID, Passport or Driver License', 'homey') ) );
            wp_die();
        }

        update_user_meta($userID, 'doc_verified', 0);
        update_user_meta($userID, 'id_doc_verified_request', 1);

        echo json_encode( 
            array( 
                'success' => true ,
                'msg' => esc_html__('Request sent for verification', 'homey') 
            ) 
        );

        $dashboard = homey_get_template_link_2('template/dashboard.php');
        $verify_link = add_query_arg( array(
            'dpage' => 'users',
            'user-id' => $userID,
            'tab' => 'documents',
        ), $dashboard );

        $email_args = array(
            'username' => get_the_author_meta( 'display_name' , $userID ),
            'email' => get_the_author_meta( 'email' , $userID ),
            'verify_link' => $verify_link,
        );
        
        $admin_email = get_option('admin_email');

        homey_email_composer( $admin_email, 'admin_id_verification', $email_args );
    
        wp_die();


    }
}

if( !function_exists('houzez_validate_phone_number')) {
    function houzez_validate_phone_number($phone) {
         // Allow +, - and . in phone number
         $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
         // Remove "-" from number
         $phone_to_check = str_replace("-", "", $filtered_phone_number);
         // Check the lenght of number
         // This can be customized if you want phone number from a specific country
         if (strlen($phone_to_check) < 5 || strlen($phone_to_check) > 20) {
            return false;
         } else {
           return true;
         }
     }
}



/* ------------------------------------------------------------------------------
* Save user profile data
/------------------------------------------------------------------------------ */
add_action( 'wp_ajax_nopriv_homey_save_profile', 'homey_save_profile' );
add_action( 'wp_ajax_homey_save_profile', 'homey_save_profile' );

if( !function_exists('homey_save_profile') ):

    function homey_save_profile(){
        global $current_user;
        wp_get_current_user();
        $userID  = $current_user->ID;

        $prefix = 'homey_';

        $verify_nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_profile_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'msg' => 'Invalid request' ) );
            die;
        }

        // Update GDPR
        if ( !empty( $_POST['gdpr_agreement'] ) ) {
            $gdpr_agreement = sanitize_text_field( $_POST['gdpr_agreement'] );
            update_user_meta( $userID, 'gdpr_agreement', $gdpr_agreement );
        } else {
            delete_user_meta( $userID, 'gdpr_agreement' );
        }

        if ( !empty( $_POST['firstname'] ) ) {
            $firstname = sanitize_text_field( $_POST['firstname'] );
            update_user_meta( $userID, 'first_name', $firstname );
        } else {
            delete_user_meta( $userID, 'first_name' );
        }

        if ( !empty( $_POST['lastname'] ) ) {
            $lastname = sanitize_text_field( $_POST['lastname'] );
            update_user_meta( $userID, 'last_name', $lastname );
        } else {
            delete_user_meta( $userID, 'last_name' );
        }

        if ( !empty( $_POST['bio'] ) ) {
            $bio = sanitize_text_field( $_POST['bio'] );
            update_user_meta( $userID, 'description', $bio );
        } else {
            delete_user_meta( $userID, 'description' );
        }

        if ( !empty( $_POST['native_language'] ) ) {
            $native_language = sanitize_text_field( $_POST['native_language'] );
            update_user_meta( $userID, $prefix.'native_language', $native_language );
        } else {
            delete_user_meta( $userID, $prefix.'native_language' );
        }

        if ( !empty( $_POST['other_language'] ) ) {
            $other_language = sanitize_text_field( $_POST['other_language'] );
            update_user_meta( $userID, $prefix.'other_language', $other_language );
        } else {
            delete_user_meta( $userID, $prefix.'other_language' );
        }

        if ( !empty( $_POST['street_address'] ) ) {
            $street_address = sanitize_text_field( $_POST['street_address'] );
            update_user_meta( $userID, $prefix.'street_address', $street_address );
        } else {
            delete_user_meta( $userID, $prefix.'street_address' );
        }

        if ( !empty( $_POST['apt_suit'] ) ) {
            $apt_suit = sanitize_text_field( $_POST['apt_suit'] );
            update_user_meta( $userID, $prefix.'apt_suit', $apt_suit );
        } else {
            delete_user_meta( $userID, $prefix.'apt_suit' );
        }

        if ( !empty( $_POST['zipcode'] ) ) {
            $zipcode = sanitize_text_field( $_POST['zipcode'] );
            update_user_meta( $userID, $prefix.'zipcode', $zipcode );
        } else {
            delete_user_meta( $userID, $prefix.'zipcode' );
        }

        if ( !empty( $_POST['country'] ) ) {
            $country = sanitize_text_field( $_POST['country'] );
            update_user_meta( $userID, $prefix.'country', $country );
        } else {
            delete_user_meta( $userID, $prefix.'country' );
        }

        if ( !empty( $_POST['state'] ) ) {
            $state = sanitize_text_field( $_POST['state'] );
            update_user_meta( $userID, $prefix.'state', $state );
        } else {
            delete_user_meta( $userID, $prefix.'state' );
        }

        if ( !empty( $_POST['city'] ) ) {
            $city = sanitize_text_field( $_POST['city'] );
            update_user_meta( $userID, $prefix.'city', $city );
        } else {
            delete_user_meta( $userID, $prefix.'city' );
        }

        if ( !empty( $_POST['neighborhood'] ) ) {
            $neighborhood = sanitize_text_field( $_POST['neighborhood'] );
            update_user_meta( $userID, $prefix.'neighborhood', $neighborhood );
        } else {
            delete_user_meta( $userID, $prefix.'neighborhood' );
        }

        if ( !empty( $_POST['em_contact_name'] ) ) {
            $em_contact_name = sanitize_text_field( $_POST['em_contact_name'] );
            update_user_meta( $userID, $prefix.'em_contact_name', $em_contact_name );
        } else {
            delete_user_meta( $userID, $prefix.'em_contact_name' );
        }

        if ( !empty( $_POST['em_relationship'] ) ) {
            $em_relationship = sanitize_text_field( $_POST['em_relationship'] );
            update_user_meta( $userID, $prefix.'em_relationship', $em_relationship );
        } else {
            delete_user_meta( $userID, $prefix.'em_relationship' );
        }

        if ( !empty( $_POST['em_email'] ) ) {
            $em_email = sanitize_text_field( $_POST['em_email'] );
            update_user_meta( $userID, $prefix.'em_email', $em_email );
        } else {
            delete_user_meta( $userID, $prefix.'em_email' );
        }

        if ( !empty( $_POST['em_phone'] ) ) {
            $em_phone = sanitize_text_field( $_POST['em_phone'] );

            if(houzez_validate_phone_number($em_phone)) {
                update_user_meta( $userID, $prefix.'em_phone', $em_phone );

            } else {
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid phone number.', 'homey') ) );
                wp_die();
            }
        } else {
            delete_user_meta( $userID, $prefix.'em_phone' );
        }


        // Update facebook
        if ( !empty( $_POST['facebook'] ) ) {
            $facebook = sanitize_text_field( $_POST['facebook'] );
            update_user_meta( $userID, $prefix.'author_facebook', $facebook );
        } else {
            delete_user_meta( $userID, $prefix.'author_facebook' );
        }

        // Update twitter
        if ( !empty( $_POST['twitter'] ) ) {
            $twitter = sanitize_text_field( $_POST['twitter'] );
            update_user_meta( $userID, $prefix.'author_twitter', $twitter );
        } else {
            delete_user_meta( $userID, $prefix.'author_twitter' );
        }

        // Update linkedin
        if ( !empty( $_POST['linkedin'] ) ) {
            $linkedin = sanitize_text_field( $_POST['linkedin'] );
            update_user_meta( $userID, $prefix.'author_linkedin', $linkedin );
        } else {
            delete_user_meta( $userID, $prefix.'author_linkedin' );
        }

        // Update instagram
        if ( !empty( $_POST['instagram'] ) ) {
            $instagram = sanitize_text_field( $_POST['instagram'] );
            update_user_meta( $userID, $prefix.'author_instagram', $instagram );
        } else {
            delete_user_meta( $userID, $prefix.'author_instagram' );
        }

        // Update pinterest
        if ( !empty( $_POST['pinterest'] ) ) {
            $pinterest = sanitize_text_field( $_POST['pinterest'] );
            update_user_meta( $userID, $prefix.'author_pinterest', $pinterest );
        } else {
            delete_user_meta( $userID, $prefix.'author_pinterest' );
        }

        // Update youtube
        if ( !empty( $_POST['youtube'] ) ) {
            $youtube = sanitize_text_field( $_POST['youtube'] );
            update_user_meta( $userID, $prefix.'author_youtube', $youtube );
        } else {
            delete_user_meta( $userID, $prefix.'author_youtube' );
        }

        // Update vimeo
        if ( !empty( $_POST['vimeo'] ) ) {
            $vimeo = sanitize_text_field( $_POST['vimeo'] );
            update_user_meta( $userID, $prefix.'author_vimeo', $vimeo );
        } else {
            delete_user_meta( $userID, $prefix.'author_vimeo' );
        }

        // Update airbnb
        if ( !empty( $_POST['airbnb'] ) ) {
            $airbnb = sanitize_text_field( $_POST['airbnb'] );
            update_user_meta( $userID, $prefix.'author_airbnb', $airbnb );
        } else {
            delete_user_meta( $userID, $prefix.'author_airbnb' );
        }

        // Update trip_advisor
        if ( !empty( $_POST['trip_advisor'] ) ) {
            $trip_advisor = sanitize_text_field( $_POST['trip_advisor'] );
            update_user_meta( $userID, $prefix.'author_trip_advisor', $trip_advisor );
        } else {
            delete_user_meta( $userID, $prefix.'author_trip_advisor' );
        }

        // Update Googleplus
        if ( !empty( $_POST['googleplus'] ) ) {
            $googleplus = sanitize_text_field( $_POST['googleplus'] );
            update_user_meta( $userID, $prefix.'author_googleplus', $googleplus );
        } else {
            delete_user_meta( $userID, $prefix.'author_googleplus' );
        }

        // Update Role
        if ( !empty( $_POST['role'] ) ) {
            $new_role = sanitize_text_field( $_POST['role'] );
            $default_role = get_option('default_role', 1);

            $u = new WP_User( $userID );

            $u->remove_role( $default_role );
            $u->set_role( $new_role );

            update_user_meta($userID, 'social_register_set_role', 1);
        }
        

        // Update email
        if( !empty( $_POST['useremail'] ) ) {
            $useremail = sanitize_email( $_POST['useremail'] );
            $useremail = is_email( $useremail );
            if( !$useremail ) {
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('The Email you entered is not valid. Please try again.', 'homey') ) );
                wp_die();
            } else {
                $email_exists = email_exists( $useremail );
                if( $email_exists ) {
                    if( $email_exists != $userID ) {
                        echo json_encode( array( 'success' => false, 'msg' => esc_html__('This Email is already used by another user. Please try a different one.', 'homey') ) );
                        wp_die();
                    }
                } else {
                    $return = wp_update_user( array ('ID' => $userID, 'user_email' => $useremail, 'display_name' => $display_name ) );
                    if ( is_wp_error( $return ) ) {
                        $error = $return->get_error_message();
                        echo esc_attr( $error );
                        wp_die();
                    }
                }
            }
        }
        wp_update_user( array ('ID' => $userID, 'display_name' => $_POST['display_name'] ) );
        
        echo json_encode( array( 'success' => true, 'msg' => esc_html__('Profile updated', 'homey') ) );
        die();
    }
endif;

/* ------------------------------------------------------------------------------
* Make superhost and update id verification
/------------------------------------------------------------------------------ */
add_action( 'wp_ajax_homey_make_superhost_idverify', 'homey_make_superhost_idverify' );

if( !function_exists('homey_make_superhost_idverify') ):

    function homey_make_superhost_idverify(){

        $prefix = 'homey_';

        /*$verify_nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_superhost_idverify_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'msg' => 'Invalid request' ) );
            die;
        }*/

        $doc_verified = sanitize_text_field($_POST['doc_verified']);
        $is_superhost = sanitize_text_field($_POST['is_superhost']);
        $user_id = sanitize_text_field($_POST['user_id']);

        if(!empty($user_id)) {
            //send email that document is verified.
            $message = esc_html__('Congratulations, your document has been verified for the platform.', 'homey');
            $user_info = get_userdata( $user_id );
            $user_email = $user_info->user_email;
            $subject = sprintf( esc_html__('Your document has been verified for the platform %s', 'homey'), get_bloginfo('name') );

            homey_send_emails( $user_email, $subject, $message );

            update_user_meta( $user_id, 'doc_verified', $doc_verified );
            update_user_meta( $user_id, 'is_superhost', $is_superhost );
            echo json_encode( array( 'success' => true, 'msg' => esc_html__('Data successfully saved.', 'homey') ) );
        }
        die();
    }
endif;


/* ------------------------------------------------------------------------------
* Ajax Reset Password function
/------------------------------------------------------------------------------ */
add_action( 'wp_ajax_nopriv_homey_ajax_password_reset', 'homey_ajax_password_reset' );
add_action( 'wp_ajax_homey_ajax_password_reset', 'homey_ajax_password_reset' );

if( !function_exists('homey_ajax_password_reset') ):
    function homey_ajax_password_reset () {
        global $current_user;
        wp_get_current_user();
        $userID         = $current_user->ID;
        $allowed_html   = array();

        $newpass        = wp_kses( $_POST['newpass'], $allowed_html );
        $confirmpass    = wp_kses( $_POST['confirmpass'], $allowed_html );

        if( $newpass == '' || $confirmpass == '' ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('New password or confirm password is blank', 'homey') ) );
            die();
        }
        if( $newpass != $confirmpass ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Passwords do not match', 'homey') ) );
            die();
        }

        check_ajax_referer( 'homey_pass_ajax_nonce', 'homey-security-pass' );

        $user = get_user_by( 'id', $userID );
        if( $user ) {
            wp_set_password( $newpass, $userID );
            echo json_encode( array( 'success' => true, 'msg' => esc_html__('Password Updated', 'homey') ) );
        } else {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Something went wrong', 'homey') ) );
        }
        die();
    }
endif; // end homey_ajax_password_reset


if(!function_exists('homey_usermeta')) {
    function homey_usermeta($user_id) {
        $user_array = array();
        $user = get_userdata($user_id);
        
        $user_array['username'] = $user->user_login;
        $user_array['email'] = $user->user_email;
        $user_array['register'] = $user->user_registered;
        $user_array['url'] = $user->user_url;
        $user_array['activation_key'] = $user->user_activation_key;
        $user_array['display_name'] = $user->display_name;

        return $user_array;
    }
}

if(!function_exists('homey_get_role_name')) {
    function homey_get_role_name($user_id) {

        $user_meta = get_userdata($user_id);
        $user_roles = $user_meta->roles;
        foreach ($user_roles as $role){
            $user_role = $role;
        }

        $is_superhost = get_the_author_meta( 'is_superhost' , $user_id);

        if($user_role == 'homey_host') {
            if($is_superhost) {
                return esc_html__('Super Host', 'homey');
            }
            return esc_html__('Host', 'homey');
        } elseif($user_role == 'homey_renter') {
            return esc_html__('Guest', 'homey');
        } else {
            return $user_role;
        }
    }
}

if(!function_exists('homey_get_role_text')) {
    function homey_get_role_text($user_role) {

        if($user_role == 'homey_host') {
            return esc_html__('Host', 'homey');
        } elseif($user_role == 'homey_renter') {
            return esc_html__('Guest', 'homey');
        } else {
            return $user_role;
        }
    }
}
/*-----------------------------------------------------------------------------------*/
// Get listing author
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_get_author') ) {
    function homey_get_author($w = '36', $h = '36', $classes = 'img-responsive img-circle') {
        
        global $homey_local;
        $author = array();
        $prefix = 'homey_';
        $comma = '';
        $maximumPoints = 100;
        $point = 0;

        $ID = get_the_author_meta( 'ID' );

        $author['is_photo'] = false;
        $author['is_email'] = false;

        $author[ 'name' ] = get_the_author();
        $author[ 'email' ] = get_the_author_meta( 'email' );
        $author[ 'bio' ] = get_the_author_meta( 'description' );

        $doc_verified = get_the_author_meta( 'doc_verified' , $ID);

        $custom_img = get_template_directory_uri().'/images/avatar.png';

        $author_picture_id = get_the_author_meta( 'homey_author_picture_id' , get_the_author_meta( 'ID' ) );

        if( !empty( $author_picture_id ) ) {
            $point+=30;

            $author_picture_id = intval( $author_picture_id );
            if ( $author_picture_id ) {

                $photo = wp_get_attachment_image( $author_picture_id, array($w, $h), "", array( "class" => $classes ) );
                
                if(!empty($photo)) {
                    $author[ 'photo' ] = $photo;
                } else {
                    $author[ 'photo' ] = '<img src="'.esc_url($custom_img).'" class="'.esc_attr($classes).'" alt="'.esc_attr($author[ 'name' ]).'" width="'.esc_attr($w).'" height="'.esc_attr($h).'">';
                }
                $author['is_photo'] = true;
            }
        } else {
            $author[ 'photo' ] = '<img src="'.esc_url($custom_img).'" class="'.esc_attr($classes).'" alt="'.esc_attr($author[ 'name' ]).'" width="'.esc_attr($w).'" height="'.esc_attr($h).'">';
        }


        
        

        $author[ 'listing_count' ] = count_user_posts( $ID , 'listing' );
        $native_language  = get_the_author_meta( $prefix.'native_language');
        $other_language  =  get_the_author_meta( $prefix.'other_language');
        if(!empty($other_language) && !empty($native_language)) {
            $comma = ', ';
        }

        $author['facebook']     =  get_the_author_meta( 'homey_author_facebook');
        $author['twitter']      =  get_the_author_meta( 'homey_author_twitter');
        $author['linkedin']     =  get_the_author_meta( 'homey_author_linkedin');
        $author['pinterest']    =  get_the_author_meta( 'homey_author_pinterest');
        $author['instagram']    =  get_the_author_meta( 'homey_author_instagram');
        $author['googleplus']   =  get_the_author_meta( 'homey_author_googleplus');
        $author['youtube']      =  get_the_author_meta( 'homey_author_youtube');
        $author['vimeo']        =  get_the_author_meta( 'homey_author_vimeo');        
        $author[ 'link' ] = get_author_posts_url( get_the_author_meta( 'ID' ) );
        $author[ 'address' ] = get_the_author_meta( $prefix.'street_address' , get_the_author_meta( 'ID' ) );
        $author[ 'country' ] = get_the_author_meta( $prefix.'country' , get_the_author_meta( 'ID' ) );
        $author[ 'state' ] = get_the_author_meta( $prefix.'state' , get_the_author_meta( 'ID' ) );
        $author[ 'city' ] = get_the_author_meta( $prefix.'city' , get_the_author_meta( 'ID' ) );
        $author[ 'area' ] = get_the_author_meta( $prefix.'area' , get_the_author_meta( 'ID' ) );
        $author[ 'is_superhost' ] = get_the_author_meta( 'is_superhost' , $ID);
        $author[ 'doc_verified' ] = $doc_verified;
        $author[ 'user_document_id' ] = get_the_author_meta( 'homey_user_document_id' , $ID );
        $author[ 'doc_verified_request' ] = get_the_author_meta( 'id_doc_verified_request' , $ID );
        $author[ 'native_language' ] = $native_language;
        $author[ 'other_language' ] = $other_language;
        $author[ 'total_earnings' ] = homey_get_host_total_earnings($ID);
        $author[ 'available_balance' ] = homey_get_host_available_earnings($ID);
        $author[ 'languages' ] = esc_attr($native_language.$comma.$other_language);

        // Emergency Contact 
        $author[ 'em_contact_name' ] = get_the_author_meta( $prefix.'em_contact_name' , $ID);
        $author[ 'em_relationship' ] = get_the_author_meta( $prefix.'em_relationship' , $ID);
        $author[ 'em_email' ] = get_the_author_meta( $prefix.'em_email' , $ID);
        $author[ 'em_phone' ] = get_the_author_meta( $prefix.'em_phone' , $ID);

        $author[ 'payout_payment_method' ] = get_the_author_meta( 'payout_payment_method' , $ID);
        $author[ 'payout_paypal_email' ] = get_the_author_meta( 'payout_paypal_email' , $ID);
        $author[ 'payout_skrill_email' ] = get_the_author_meta( 'payout_skrill_email' , $ID);

        // Beneficiary Information
        $author[ 'ben_first_name' ] = get_the_author_meta( 'ben_first_name' , $ID);
        $author[ 'ben_last_name' ] = get_the_author_meta( 'ben_last_name' , $ID);
        $author[ 'ben_company_name' ] = get_the_author_meta( 'ben_company_name' , $ID);
        $author[ 'ben_tax_number' ] = get_the_author_meta( 'ben_tax_number' , $ID);
        $author[ 'ben_street_address' ] = get_the_author_meta( 'ben_street_address' , $ID);
        $author[ 'ben_apt_suit' ] = get_the_author_meta( 'ben_apt_suit' , $ID);
        $author[ 'ben_city' ] = get_the_author_meta( 'ben_city' , $ID);
        $author[ 'ben_state' ] = get_the_author_meta( 'ben_state' , $ID);
        $author[ 'ben_zip_code' ] = get_the_author_meta( 'ben_zip_code' , $ID);

        //Wire Transfer Information
        $author[ 'bank_account' ] = get_the_author_meta( 'bank_account' , $ID);
        $author[ 'swift' ] = get_the_author_meta( 'swift' , $ID);
        $author[ 'bank_name' ] = get_the_author_meta( 'bank_name' , $ID);
        $author[ 'wir_street_address' ] = get_the_author_meta( 'wir_street_address' , $ID);
        $author[ 'wir_aptsuit' ] = get_the_author_meta( 'wir_aptsuit' , $ID);
        $author[ 'wir_city' ] = get_the_author_meta( 'wir_city' , $ID);
        $author[ 'wir_state' ] = get_the_author_meta( 'wir_state' , $ID);
        $author[ 'wir_zip_code' ] = get_the_author_meta( 'wir_zip_code' , $ID);

        if(!empty($author[ 'email' ])) {
            $point+=30;
            $author['is_email'] = true;
        }

        if($doc_verified) {
            $point+=40;
        }

        $percentage = ($point*$maximumPoints)/100;
        $author[ 'profile_status' ] = $percentage."%";
        $author[ 'is_email_verified' ] = get_the_author_meta( 'is_email_verified', $ID );

        return $author;
    }
}


if( !function_exists('homey_get_author_by_id') ) {
    function homey_get_author_by_id($w = '36', $h = '36', $classes = 'img-responsive img-circle', $ID = "") {
        
        global $homey_local;
        $author = array();
        $prefix = 'homey_';
        $comma = ' ';
        $maximumPoints = 100;
        $point = 0;

        $author['is_photo'] = false;
        $author['is_email'] = false;

        $custom_img = get_template_directory_uri().'/images/avatar.png';

        $author_picture_id = get_the_author_meta( 'homey_author_picture_id' , $ID );

        $doc_verified = get_the_author_meta( 'doc_verified' , $ID);

        $author[ 'name' ] = get_the_author_meta( 'display_name' , $ID );
        $author[ 'email' ] = get_the_author_meta( 'email', $ID );
        $author[ 'bio' ] = get_the_author_meta( 'description' , $ID );

        if( !empty( $author_picture_id ) ) {
            $point+=30;

            $author_picture_id = intval( $author_picture_id );
            if ( $author_picture_id ) {

                $photo = wp_get_attachment_image( $author_picture_id, array($w, $h), "", array( "class" => $classes ) );
                
                if(!empty($photo)) {
                    $author[ 'photo' ] = $photo;
                } else {
                    $author[ 'photo' ] = '<img src="'.esc_url($custom_img).'" class="'.esc_attr($classes).'" alt="'.esc_attr($author[ 'name' ]).'" width="'.esc_attr($w).'" height="'.esc_attr($h).'">';
                }

                $author['is_photo'] = true;
            }
        } else {
            $author[ 'photo' ] = '<img src="'.esc_url($custom_img).'" class="'.esc_attr($classes).'" alt="'.esc_attr($author[ 'name' ]).'" width="'.esc_attr($w).'" height="'.esc_attr($h).'">';
        }

        //counting listings with statues
        $author[ 'all_listing_count' ]          = homey_hm_user_listing_count($ID);
        $author[ 'publish_listing_count' ]      = homey_hm_user_publish_listing_count($ID);
        $author[ 'all_featured_listing_count' ] = homey_featured_listing_count($ID);

        $native_language  = get_the_author_meta( $prefix.'native_language' , $ID );
        $other_language  =  get_the_author_meta( $prefix.'other_language' , $ID );
        if(!empty($other_language) && !empty($native_language)) {
            $comma = ', ';
        }

        $author['facebook']     =  get_the_author_meta( 'homey_author_facebook' , $ID );
        $author['twitter']      =  get_the_author_meta( 'homey_author_twitter' , $ID );
        $author['linkedin']     =  get_the_author_meta( 'homey_author_linkedin' , $ID );
        $author['pinterest']    =  get_the_author_meta( 'homey_author_pinterest' , $ID );
        $author['instagram']    =  get_the_author_meta( 'homey_author_instagram' , $ID );
        $author['googleplus']   =  get_the_author_meta( 'homey_author_googleplus' , $ID );
        $author['youtube']      =  get_the_author_meta( 'homey_author_youtube' , $ID );
        $author['vimeo']        =  get_the_author_meta( 'homey_author_vimeo' , $ID );
        $author[ 'link' ] = get_author_posts_url( $ID );
        $author[ 'address' ] = get_the_author_meta( $prefix.'street_address' , $ID );
        $author[ 'country' ] = get_the_author_meta( $prefix.'country' , $ID);
        $author[ 'state' ] = get_the_author_meta( $prefix.'state' , $ID);
        $author[ 'city' ] = get_the_author_meta( $prefix.'city' , $ID);
        $author[ 'area' ] = get_the_author_meta( $prefix.'area' , $ID);
        $author[ 'is_superhost' ] = get_the_author_meta( 'is_superhost' , $ID);
        $author[ 'doc_verified' ] = $doc_verified;
        $author[ 'user_document_id' ] = get_the_author_meta( 'homey_user_document_id' , $ID );
        $author[ 'doc_verified_request' ] = get_the_author_meta( 'id_doc_verified_request' , $ID );
        $author[ 'native_language' ] = $native_language;
        $author[ 'other_language' ] = $other_language;
        $author[ 'total_earnings' ] = homey_get_host_total_earnings($ID);
        $author[ 'available_balance' ] = homey_get_host_available_earnings($ID);
        $author[ 'languages' ] = esc_attr($native_language.$comma.$other_language);

        // Emergency Contact 
        $author[ 'em_contact_name' ] = get_the_author_meta( $prefix.'em_contact_name' , $ID);
        $author[ 'em_relationship' ] = get_the_author_meta( $prefix.'em_relationship' , $ID);
        $author[ 'em_email' ] = get_the_author_meta( $prefix.'em_email' , $ID);
        $author[ 'em_phone' ] = get_the_author_meta( $prefix.'em_phone' , $ID);

        $author[ 'payout_payment_method' ] = get_the_author_meta( 'payout_payment_method' , $ID);
        $author[ 'payout_paypal_email' ] = get_the_author_meta( 'payout_paypal_email' , $ID);
        $author[ 'payout_skrill_email' ] = get_the_author_meta( 'payout_skrill_email' , $ID);

        // Beneficiary Information
        $author[ 'ben_first_name' ] = get_the_author_meta( 'ben_first_name' , $ID);
        $author[ 'ben_last_name' ] = get_the_author_meta( 'ben_last_name' , $ID);
        $author[ 'ben_company_name' ] = get_the_author_meta( 'ben_company_name' , $ID);
        $author[ 'ben_tax_number' ] = get_the_author_meta( 'ben_tax_number' , $ID);
        $author[ 'ben_street_address' ] = get_the_author_meta( 'ben_street_address' , $ID);
        $author[ 'ben_apt_suit' ] = get_the_author_meta( 'ben_apt_suit' , $ID);
        $author[ 'ben_city' ] = get_the_author_meta( 'ben_city' , $ID);
        $author[ 'ben_state' ] = get_the_author_meta( 'ben_state' , $ID);
        $author[ 'ben_zip_code' ] = get_the_author_meta( 'ben_zip_code' , $ID);

        //Wire Transfer Information
        $author[ 'bank_account' ] = get_the_author_meta( 'bank_account' , $ID);
        $author[ 'swift' ] = get_the_author_meta( 'swift' , $ID);
        $author[ 'bank_name' ] = get_the_author_meta( 'bank_name' , $ID);
        $author[ 'wir_street_address' ] = get_the_author_meta( 'wir_street_address' , $ID);
        $author[ 'wir_aptsuit' ] = get_the_author_meta( 'wir_aptsuit' , $ID);
        $author[ 'wir_city' ] = get_the_author_meta( 'wir_city' , $ID);
        $author[ 'wir_state' ] = get_the_author_meta( 'wir_state' , $ID);
        $author[ 'wir_zip_code' ] = get_the_author_meta( 'wir_zip_code' , $ID);


        if(!empty($author[ 'email' ])) {
            $point+=30;

            $author['is_email'] = true;
        }

        if($doc_verified) {
            $point+=40;
        }

        $percentage = ($point*$maximumPoints)/100;
        $author[ 'profile_status' ] = $percentage."%";
        $author[ 'is_email_verified' ] = get_the_author_meta( 'is_email_verified', $ID );

        return $author;
    }
}


if(!function_exists('homey_user_document_for_verification')) {
    function homey_user_document_for_verification($user_document_id, $w = '100', $h = '100', $classes = null) {
        $photo = wp_get_attachment_image( $user_document_id, array($w, $h), "", array( "class" => $classes ) );
        if(!empty($photo)) {
            return $photo;
        } 
        return '';
    }
}


if(!function_exists('homey_is_host_payout')) {
    function homey_is_host_payout($user_id) {
        $payout_method = get_user_meta($user_id, 'payout_payment_method', true);

        if(!empty($payout_method)) {
            return $payout_method;
        } else {
            return '';
        }
    }
}


if(!function_exists('homey_reservation_count')) {
    function homey_reservation_count($user_id) {
        $args = array(
            'post_type'        =>  'homey_reservation',
            'posts_per_page'    => -1,
        );

        if( homey_is_renter() ) {
            $meta_query[] = array(
                'key' => 'listing_renter',
                'value' => $user_id,
                'compare' => '='
            );
            $args['meta_query'] = $meta_query;
        } else {
            $meta_query[] = array(
                'key' => 'listing_owner',
                'value' => $user_id,
                'compare' => '='
            );
            $args['meta_query'] = $meta_query;
        }

        $Qry = new WP_Query($args);
        $founds = $Qry->found_posts;

        return $founds;

    }
}

if(!function_exists('homey_featured_listing_count')) {
    function homey_featured_listing_count($user_id)
    {
        global $wpdb;
        $sql = 'SELECT COUNT("ID") as total_featured_listings 
                    FROM '.$wpdb->prefix.'posts AS p 
                    INNER JOIN '. $wpdb->prefix.'postmeta AS pm ON p.ID =  pm.post_id 
                    WHERE p.post_author = '.$user_id.' AND pm.meta_key = "homey_featured" AND pm.meta_value = 1';
  
      $total_featured_listings = $wpdb->get_results($sql);
        return isset($total_featured_listings[0]->total_featured_listings) ? $total_featured_listings[0]->total_featured_listings : 0;
    }
}

if(!function_exists('homey_hm_user_listing_count')) {
    function homey_hm_user_listing_count($user_id)
    {
      global $wpdb;
      $sql = 'SELECT COUNT("ID") as total_listings FROM '.$wpdb->prefix.'posts AS p WHERE p.post_author = '.$user_id.' AND p.post_type = "listing"';
      $total_listings = $wpdb->get_results($sql);
      return isset($total_listings[0]->total_listings) ? $total_listings[0]->total_listings : 0;
    }
}

if(!function_exists('homey_hm_user_publish_listing_count')) {
    function homey_hm_user_publish_listing_count($user_id)
    {
      global $wpdb;
      $sql = 'SELECT COUNT("ID") as total_listings FROM '.$wpdb->prefix.'posts AS p WHERE p.post_status ="publish" AND p.post_author = '.$user_id.' AND p.post_type = "listing"';
      $total_listings = $wpdb->get_results($sql);
      return $total_listings[0]->total_listings;
    }
}



/**
 * Show custom user profile fields
 * @param  obj $user The user object.
 * @return void
 */
function homey_custom_user_profile_fields($user) {
?>
    
    <h2><?php echo esc_html__('Social Info', 'homey'); ?></h2>
    <table class="form-table">
        <tbody>
            <tr class="user-homey_author_facebook-wrap">
                <th><label for="homey_author_facebook"><?php echo esc_html__('Facebook', 'homey'); ?></label></th>
                <td><input type="text" name="homey_author_facebook" id="homey_author_facebook" value="<?php echo esc_url( get_the_author_meta( 'homey_author_facebook', $user->ID ) ); ?>" class="regular-text"></td>
            </tr>
            <tr class="user-homey_author_linkedin-wrap">
                <th><label for="homey_author_linkedin"><?php echo esc_html__('LinkedIn', 'homey'); ?></label></th>
                <td><input type="text" name="homey_author_linkedin" id="homey_author_linkedin" value="<?php echo esc_url( get_the_author_meta( 'homey_author_linkedin', $user->ID ) ); ?>" class="regular-text"></td>
            </tr>
            <tr class="user-homey_author_twitter-wrap">
                <th><label for="homey_author_twitter"><?php echo esc_html__('Twitter', 'homey'); ?></label></th>
                <td><input type="text" name="homey_author_twitter" id="homey_author_twitter" value="<?php echo esc_url( get_the_author_meta( 'homey_author_twitter', $user->ID ) ); ?>" class="regular-text"></td>
            </tr>
            <tr class="user-homey_author_pinterest-wrap">
                <th><label for="homey_author_pinterest"><?php echo esc_html__('Pinterest', 'homey'); ?></label></th>
                <td><input type="text" name="homey_author_pinterest" id="homey_author_pinterest" value="<?php echo esc_url( get_the_author_meta( 'homey_author_pinterest', $user->ID ) ); ?>" class="regular-text"></td>
            </tr>
            <tr class="user-homey_author_instagram-wrap">
                <th><label for="homey_author_instagram"><?php echo esc_html__('Instagram', 'homey'); ?></label></th>
                <td><input type="text" name="homey_author_instagram" id="homey_author_instagram" value="<?php echo esc_url( get_the_author_meta( 'homey_author_instagram', $user->ID ) ); ?>" class="regular-text"></td>
            </tr>
            <tr class="user-homey_author_youtube-wrap">
                <th><label for="homey_author_youtube"><?php echo esc_html__('Youtube', 'homey'); ?></label></th>
                <td><input type="text" name="homey_author_youtube" id="homey_author_youtube" value="<?php echo esc_url( get_the_author_meta( 'homey_author_youtube', $user->ID ) ); ?>" class="regular-text"></td>
            </tr>
            <tr class="user-homey_author_vimeo-wrap">
                <th><label for="homey_author_vimeo"><?php echo esc_html__('Vimeo', 'homey'); ?></label></th>
                <td><input type="text" name="homey_author_vimeo" id="homey_author_vimeo" value="<?php echo esc_url( get_the_author_meta( 'homey_author_vimeo', $user->ID ) ); ?>" class="regular-text"></td>
            </tr>
            <tr class="user-homey_author_googleplus-wrap">
                <th><label for="homey_author_googleplus"><?php echo esc_html__('Google Plus', 'homey'); ?></label></th>
                <td><input type="text" name="homey_author_googleplus" id="homey_author_googleplus" value="<?php echo esc_url( get_the_author_meta( 'homey_author_googleplus', $user->ID ) ); ?>" class="regular-text"></td>
            </tr>
        </tbody>
    </table>

<?php
}
add_action('show_user_profile', 'homey_custom_user_profile_fields');
add_action('edit_user_profile', 'homey_custom_user_profile_fields');


if( !function_exists('homey_update_extra_profile_fields') ) {
    function homey_update_extra_profile_fields($user_id)
    {
        if (current_user_can('edit_user', $user_id))


        /*
         * Social Info
        --------------------------------------------------------------------------------*/
        update_user_meta($user_id, 'homey_author_facebook', sanitize_text_field($_POST['homey_author_facebook']));
        update_user_meta($user_id, 'homey_author_linkedin', sanitize_text_field($_POST['homey_author_linkedin']));
        update_user_meta($user_id, 'homey_author_twitter', sanitize_text_field($_POST['homey_author_twitter']));
        update_user_meta($user_id, 'homey_author_pinterest', sanitize_text_field($_POST['homey_author_pinterest']));
        update_user_meta($user_id, 'homey_author_instagram', sanitize_text_field($_POST['homey_author_instagram']));
        update_user_meta($user_id, 'homey_author_youtube', sanitize_text_field($_POST['homey_author_youtube']));
        update_user_meta($user_id, 'homey_author_vimeo', sanitize_text_field($_POST['homey_author_vimeo']));
        update_user_meta($user_id, 'homey_author_googleplus', sanitize_text_field($_POST['homey_author_googleplus']));

    }
}
add_action('edit_user_profile_update', 'homey_update_extra_profile_fields');
add_action('personal_options_update', 'homey_update_extra_profile_fields');


/* -----------------------------------------------------------------------------------------------------------
 *  Forgot PassWord function
 -------------------------------------------------------------------------------------------------------------*/

$reset_password_link = homey_get_template_link_2( 'template/template-verification.php' );

if ( !empty( $reset_password_link ) ) :

    add_action( 'login_form_rp', 'homey_redirect_to_custom_password_reset' );
    add_action( 'login_form_resetpass', 'homey_redirect_to_custom_password_reset' );

endif;

if ( !function_exists( 'homey_redirect_to_custom_password_reset' ) ) :

    function homey_redirect_to_custom_password_reset() {

        if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) :

            $reset_password_link = homey_get_template_link_2( 'template/template-verification.php' );

            // Verify key / login combo
            $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );

            if ( ! $user || is_wp_error( $user ) ) :

                if ( $user && $user->get_error_code() === 'expired_key' ) :

                    wp_redirect( home_url( $reset_password_link. '?login=expiredkey' ) );

                else :

                    wp_redirect( home_url( $reset_password_link. '?login=invalidkey' ) );

                endif;

                exit;

            endif;

        
            $redirect_url = add_query_arg( 
                array(
                    'login' => esc_attr( $_REQUEST['login'] ), 
                    'key' => esc_attr( $_REQUEST['key'] ),
                ),
                $reset_password_link 
            );

            wp_redirect( $redirect_url );

            exit;

        endif;

    }

endif;

add_action( 'wp_ajax_nopriv_homey_reset_password_2', 'homey_reset_password_2' );
add_action( 'wp_ajax_homey_reset_password_2', 'homey_reset_password_2' );

if( !function_exists('homey_reset_password_2') ) {
    function homey_reset_password_2() {
        $allowed_html   = array();

        $newpass        = wp_kses( $_POST['password'], $allowed_html );
        $rq_login   = wp_kses( $_POST['rq_login'], $allowed_html );
        $rp_key   = wp_kses( $_POST['rp_key'], $allowed_html );

        $user = check_password_reset_key( $rp_key, $rq_login );

        if ( ! $user || is_wp_error( $user ) ) {

            if ($user && $user->get_error_code() === 'expired_key') {
                echo json_encode(array('success' => false, 'msg' => esc_html__('Reset password Session key expired.', 'homey')));
                die();
            } else {
                echo json_encode(array('success' => false, 'msg' => esc_html__('Invalid password reset Key', 'homey')));
                die();
            }
        }

        if( $newpass == '' ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Please enter password', 'homey') ) );
            die();
        }
        
        reset_password( $user, $newpass );
        echo json_encode( array( 'success' => true, 'msg' => esc_html__('Password reset successfully, you can login now.', 'homey') ) );
        die();
    }
}

/*-----------------------------------------------------------------------------------*/
// Remove user photo
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_delete_user_account', 'homey_delete_user_account' );
if( !function_exists('homey_delete_user_account') ) {
    function homey_delete_user_account() {
        global $wpdb;
        $remove_account = false;

        $verify_nonce = $_REQUEST['verify_nonce'];
        if ( ! wp_verify_nonce( $verify_nonce, 'homey_upload_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid request' ) );
            die;
        }

        if (isset($_POST['user_id'])) {
            $user_id = intval($_POST['user_id']);

            if ( $user_id > 0 ) {
                $remove_account = wp_delete_user($user_id);
                $sql = "DELETE a,b,c
                        FROM {$wpdb->prefix}posts a
                            LEFT JOIN {$wpdb->prefix}term_relationships b
                                ON (a.ID = b.object_id)
                            LEFT JOIN {$wpdb->prefix}postmeta c
                                ON (a.ID = c.post_id)
                        WHERE a.post_author = {$user_id} AND a.post_type = 'homey_invoice';";
                $wpdb->get_results($sql);

            }
        }

        echo json_encode(array(
            'success' => $remove_account,
        ));
        wp_die();

    }
}




