jQuery(document).ready(function($) {

    "use strict";


    if ( typeof homeyProfile !== "undefined" ) {

        var ajaxURL = homeyProfile.ajaxURL;
        var user_id = homeyProfile.user_id;
        var homey_upload_nonce = homeyProfile.homey_upload_nonce;
        var verify_file_type = homeyProfile.verify_file_type;
        var homey_site_url = homeyProfile.homey_site_url;
        var process_loader_refresh = homeyProfile.process_loader_refresh;
        var sending_info = homeyProfile.sending_info;
        var process_loader_spinner = homeyProfile.process_loader_spinner;
        var process_loader_circle = homeyProfile.process_loader_circle;
        var process_loader_cog = homeyProfile.process_loader_cog;
        var success_icon = homeyProfile.success_icon;
        var processing_text = homeyProfile.processing_text;
        var gdpr_agree_text = homeyProfile.gdpr_agree_text;


        /*-------------------------------------------------------------------
         *  Delete Profile Photo
         * ------------------------------------------------------------------*/
         $('.delete_user_photo').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var attach_id = $('#profile-pic-id').val();
            
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url:   ajaxURL,
                data: {
                    'action'       : 'homey_delete_user_photo',
                    'verify_nonce' : homey_upload_nonce,
                    'user_id'      : user_id,
                    'attach_id'    : attach_id,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if(data.success) {
                        $('#homey_profile_photo').empty();
                    } else {
                        
                    }
                },
                error: function(errorThrown) {

                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    
                }
            });
            
         });

        /*-------------------------------------------------------------------
        *  Delete Profile Account
        * ------------------------------------------------------------------*/
        $('.delete_user_account_confirmed').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url:   ajaxURL,
                data: {
                    'action'       : 'homey_delete_user_account',
                    'verify_nonce' : homey_upload_nonce,
                    'user_id'      : user_id,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if(data.success) {
                        window.location.href = homey_site_url;
                    } else {

                    }
                },
                error: function(errorThrown) {

                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);

                }
            });

        });


        /*-------------------------------------------------------------------
        *  Delete Profile Photo
        * ------------------------------------------------------------------*/
         $('#delete_verify_id').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var attach_id = $('#profile-doc-id').val();
            
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url:   ajaxURL,
                data: {
                    'action'       : 'homey_delete_user_doc',
                    'verify_nonce' : homey_upload_nonce,
                    'user_id'      : user_id,
                    'attach_id'    : attach_id,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if(data.success) {
                        $('#homey_user_doc').empty();
                    } else {
                        
                    }
                },
                error: function(errorThrown) {

                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    
                }
            });
            
         });
        
         $('#btn_verify_id').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            jQuery('#id_verify_mgs').empty();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url:   ajaxURL,
                data: {
                    'action' : 'homey_send_doc_verification_request',
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    
                    if( data.success ) {
                        jQuery('#id_verify_mgs').empty().append('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"></button>'+ data.msg +'</div>');
                        $('#btn_verify_id').remove();
                    } else {
                        jQuery('#id_verify_mgs').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"></button>'+ data.msg +'</div>');
                        
                    }
                },
                error: function(errorThrown) {

                },
                complete: function() {
                    $this.children('i').removeClass(process_loader_spinner);
                    
                }
            });
         });

        /*-------------------------------------------------------------------
         *  Update Profile [user_profile.php]
         * ------------------------------------------------------------------*/
        $(".homey_profile_save").on('click', function(e) {
            e.preventDefault();

            var $this = $(this);

            var gdpr_agreement;

            if($('#gdpr_agreement').length > 0 ) {
                if(!$('#gdpr_agreement').is(":checked")) {
                    jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ gdpr_agree_text +'</div>');
                    $('html,body').animate({
                        scrollTop: $(".user-dashboard-right").offset().top
                    }, 'slow');

                    return false;
                } else {
                    gdpr_agreement = 'checked';
                }
            } 


            var firstname   = $("#firstname").val(),
                lastname    = $("#lastname").val(),
                useremail    = $("#useremail").val(),
                display_name    = $('select[name="display_name"] option:selected').val(),
                native_language   = $('#native_language').val(),
                other_language       = $("#other_language").val(),
                bio       = $("#bio").val(),
                street_address       = $("#street_address").val(),
                apt_suit       = $("#apt_suit").val(),
                city       = $("#city").val(),
                state       = $("#state").val(),
                zipcode       = $("#zipcode").val(),
                neighborhood       = $("#neighborhood").val(),
                country       = $("#country").val(),

                facebook  = $("#facebook").val(),
                twitter  = $("#twitter").val(),
                linkedin  = $("#linkedin").val(),
                googleplus  = $("#googleplus").val(),
                instagram  = $("#instagram").val(),
                pinterest  = $("#pinterest").val(),
                youtube  = $("#youtube").val(),
                vimeo  = $("#vimeo").val(),
                airbnb  = $("#airbnb").val(),
                trip_advisor  = $("#trip_advisor").val(),

                em_contact_name  = $("#em_contact_name").val(),
                em_relationship  = $("#em_relationship").val(),
                em_email  = $("#em_email").val(),
                em_phone  = $("#em_phone").val(),

                securityprofile = $('#homey_profile_security').val(),
                user_role    = $('select[name="role"] option:selected').val();


            $.ajax({
                type: 'POST',
                url: ajaxURL,
                dataType: 'json',
                data: {
                    'action'          : 'homey_save_profile',
                    'firstname'       : firstname,
                    'lastname'        : lastname,
                    'useremail'       : useremail,
                    'display_name'    : display_name,
                    'role'            : user_role,
                    'native_language' : native_language,
                    'other_language'  : other_language,
                    'bio'             : bio,
                    'street_address'  : street_address,
                    'apt_suit'        : apt_suit,
                    'city'            : city,
                    'state'           : state,
                    'zipcode'         : zipcode,
                    'neighborhood'    : neighborhood,
                    'country'         : country,
                    'facebook'        : facebook,
                    'twitter'         : twitter,
                    'linkedin'        : linkedin,
                    'googleplus'      : googleplus,
                    'instagram'       : instagram,
                    'pinterest'       : pinterest,
                    'youtube'         : youtube,
                    'vimeo'           : vimeo,
                    'airbnb'          : airbnb,
                    'trip_advisor'    : trip_advisor,
                    'em_contact_name' : em_contact_name,
                    'em_relationship' : em_relationship,
                    'em_email'        : em_email,
                    'em_phone'        : em_phone,
                    'gdpr_agreement': gdpr_agreement,
                    'security'        : securityprofile,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        jQuery('#profile_message').empty().append('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ data.msg +'</div>');
                        $('html,body').animate({
                            scrollTop: $(".user-dashboard-right").offset().top
                        }, 'slow');
                    } else {
                        jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ data.msg +'</div>');
                        $('html,body').animate({
                            scrollTop: $(".user-dashboard-right").offset().top
                        }, 'slow');
                    }
                },
                error: function(errorThrown) {

                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    $this.children('i').addClass(success_icon);
                }
            });

        });

        /*-------------------------------------------------------------------
         *  Make sure host and verify doc
         * ------------------------------------------------------------------*/
        $("#superhost_docVerify").on('click', function(e) {
            e.preventDefault();
            var $this = $(this);

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url:   ajaxURL,
                data: {
                    'action'       : 'homey_make_superhost_idverify',
                    'doc_verified' : $('input[name="doc_verified"]:checked').val(),
                    'is_superhost' : $('input[name="is_superhost"]:checked').val(),
                    'user_id' : $('#user_id').val(),
                    'security12'     : $('#homey_superhost_idverify_security').val(),
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        jQuery('#superhost_msgs').empty().append('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ data.msg +'</div>');
                        window.location.reload();
                    } else {
                        jQuery('#superhost_msgs').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ data.msg +'</div>');
                    }
                    $('html,body').animate({
                        scrollTop: $(".user-dashboard-right").offset().top
                    }, 'slow');
                },
                error: function(errorThrown) {

                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }
            });

        });

        /*-------------------------------------------------------------------
         *  Change Password [user-profile.php]
         * ------------------------------------------------------------------*/
        $("#homey_change_pass").on('click', function(e) {
            e.preventDefault();
            var securitypassword, oldpass, newpass, confirmpass;

            var $this = $(this);
            newpass          = $("#newpass").val();
            confirmpass      = $("#confirmpass").val();
            securitypassword = $("#homey-security-pass").val();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url:   ajaxURL,
                data: {
                    'action'      : 'homey_ajax_password_reset',
                    'newpass'     : newpass,
                    'confirmpass' : confirmpass,
                    'homey-security-pass' : securitypassword,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        jQuery('#password_reset_msgs').empty().append('<p class="success text-success"><i class="fa fa-check"></i> '+ data.msg +'</p>');
                        jQuery('#newpass, #confirmpass').val('');
                    } else {
                        jQuery('#password_reset_msgs').empty().append('<p class="error text-danger"><i class="fa fa-close"></i> '+ data.msg +'</p>');
                    }
                },
                error: function(errorThrown) {

                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    $this.children('i').addClass(success_icon);
                }
            });

        });

        $('#homey_delete_account').on('click', function(e){
            e.preventDefault();

            var confirm = window.confirm("Are you sure!, you want to delete a account.");

            if ( confirm == true ) {

                $.ajax({
                    type: 'post',
                    url: ajaxURL,
                    dataType: 'json',
                    data: {
                        'action': 'homey_delete_account'
                    },
                    beforeSend: function () {
                        profile_processing_modal(processing_text);
                    },
                    success: function( response ) {
                        if( response.success ) {
                            window.location.href = homey_site_url;
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }
                });

            }

        });


        var profile_processing_modal = function ( msg ) {
            var process_modal ='<div class="modal fade" id="fave_modal" tabindex="-1" role="dialog" aria-labelledby="faveModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body homey_messages_modal">'+msg+'</div></div></div></div></div>';
            jQuery('body').append(process_modal);
            jQuery('#fave_modal').modal();
        }

        var profile_processing_modal_close = function ( ) {
            jQuery('#fave_modal').modal('hide');
        }

        /*-------------------------------------------------------------------
         *  Upload user profile image
         * ------------------------------------------------------------------*/
        var homey_plupload = new plupload.Uploader({
            browse_button: 'select_user_profile_photo',
            file_data_name: 'homey_file_data_name',
            multi_selection : false,
            url: ajaxURL + "?action=homey_user_picture_upload&verify_nonce=" + homey_upload_nonce + "&user_id=" + user_id,
            filters: {
                mime_types : [
                    { title : verify_file_type, extensions : "jpg,jpeg,gif,png" }
                ],
                max_file_size: '5000kb',
                prevent_duplicates: false
            }
        });
        homey_plupload.init();

        homey_plupload.bind('FilesAdded', function(up, files) {
            var homey_thumbnail = "";
            plupload.each(files, function(file) {
                homey_thumbnail += '<div id="imageholder-' + file.id + '" class="homey-thumb">' + '' + '</div>';
            });
            document.getElementById('homey_profile_photo').innerHTML = homey_thumbnail;
            up.refresh();
            homey_plupload.start();
        });

        homey_plupload.bind('UploadProgress', function(up, file) {
            document.getElementById( "imageholder-" + file.id ).innerHTML = '<span>' + file.percent + "%</span>";
        });

        homey_plupload.bind('Error', function( up, err ) {
            document.getElementById('upload_errors').innerHTML += "<br/>" + "Error #" + err.code + ": " + err.message;
        });

        homey_plupload.bind('FileUploaded', function ( up, file, ajax_res ) {
            var response = $.parseJSON( ajax_res.response );

            if ( response.success ) {

                var homey_profile_thumb = '<img class="img-circle" src="' + response.url + '" alt="" />' +
                    '<input type="hidden" class="profile-pic-id" id="profile-pic-id" name="profile-pic-id" value="' + response.attachment_id + '"/>';
    

                document.getElementById( "imageholder-" + file.id ).innerHTML = homey_profile_thumb;

            } else {
                console.log ( response );
            }
        });


        /*-------------------------------------------------------------------
         *  Upload user document for verification
         * ------------------------------------------------------------------*/
        var homey_doc_plupload = new plupload.Uploader({
            browse_button: 'select_user_verify_id',
            file_data_name: 'homey_id_file_data_name',
            multi_selection : false,
            url: ajaxURL + "?action=homey_user_document_upload&verify_nonce=" + homey_upload_nonce + "&user_id=" + user_id,
            filters: {
                mime_types : [
                    { title : verify_file_type, extensions : "jpg,jpeg,gif,png" }
                ],
                max_file_size: '10000kb',
                prevent_duplicates: false
            }
        });
        homey_doc_plupload.init();

        homey_doc_plupload.bind('FilesAdded', function(up, files) {
            var homey_thumbnail = "";
            plupload.each(files, function(file) {
                homey_thumbnail += '<div id="imageholder-' + file.id + '" class="homey-thumb">' + '' + '</div>';
            });
            document.getElementById('homey_user_doc').innerHTML = homey_thumbnail;
            up.refresh();
            homey_doc_plupload.start();
        });

        homey_doc_plupload.bind('UploadProgress', function(up, file) {
            document.getElementById( "imageholder-" + file.id ).innerHTML = '<span>' + file.percent + "%</span>";
        });

        homey_doc_plupload.bind('Error', function( up, err ) {
            document.getElementById('upload_errors').innerHTML += "<br/>" + "Error #" + err.code + ": " + err.message;
        });

        homey_doc_plupload.bind('FileUploaded', function ( up, file, ajax_res ) {
            var response = $.parseJSON( ajax_res.response );

            if ( response.success ) {

                var homey_profile_thumb = '<img src="' + response.url + '" alt="" />' +
                    '<input type="hidden" class="profile-doc-id" id="profile-doc-id" name="profile-doc-id" value="' + response.attachment_id + '"/>';
    

                document.getElementById( "imageholder-" + file.id ).innerHTML = homey_profile_thumb;

            } else {
                console.log ( response );
            }
        });

    }

});