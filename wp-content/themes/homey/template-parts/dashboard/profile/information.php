<?php
global $current_user, $userID, $user_email, $homey_prefix, $homey_local;
$homey_prefix = 'homey_';
$username               =   get_the_author_meta( 'user_login' , $userID );
$first_name             =   get_the_author_meta( 'first_name' , $userID );
$last_name              =   get_the_author_meta( 'last_name' , $userID );
$description            =   get_the_author_meta( 'description' , $userID );
$website_url            =   get_the_author_meta( 'user_url' , $userID );
$gdpr_agreement         =   get_the_author_meta( 'gdpr_agreement' , $userID );
$native_language        =   get_the_author_meta( $homey_prefix.'native_language' , $userID );
$other_language         =   get_the_author_meta( $homey_prefix.'other_language' , $userID );

$gdpr_enabled = homey_option('gdpr-enabled');
$gdpr_agreement_content = homey_option('gdpr-agreement-content');
$is_role_settled = get_user_meta($userID, 'social_register_set_role', 1);
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_attr($homey_local['information']); ?></h2>
        </div>
    </div>
    <div class="block-body">
        <div class="row">
            <?php if ($is_role_settled == -1){ ?>
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="role"><?php echo esc_attr(isset($homey_local['select_role'])?$homey_local['select_role']:'Select Role'); ?></label>
                    <select name="role" class="selectpicker" id="role" data-live-search="false">
                        <option value="<?php echo esc_attr("homey_host"); ?>"><?php echo esc_html__('Host', 'homey'); ?></option>
                        <option value="<?php echo esc_attr("homey_renter"); ?>"><?php echo esc_html__('Renter', 'homey'); ?></option>
                    </select>
                </div>
            </div>
            <?php } ?>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="username"><?php echo esc_attr($homey_local['fusername_label']); ?></label>
                    <input type="text" name="username" class="form-control" value="<?php echo esc_attr($username);?>" placeholder="<?php echo esc_attr($homey_local['fusername_plac']); ?>" disabled>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="display_name"><?php echo esc_attr($homey_local['display_name_as']); ?></label>
                    <select name="display_name" class="selectpicker" id="display_name" data-live-search="false">
                        <?php
                            $public_display = array();
                            $public_display['display_username']  = $current_user->user_login;
                            $public_display['display_nickname']  = $current_user->nickname;
                            
                            if(!empty($current_user->first_name)) {
                                $public_display['display_firstname'] = $current_user->first_name;
                            }
                            
                            if(!empty($current_user->last_name)) {
                                $public_display['display_lastname'] = $current_user->last_name;
                            }
                            
                            if(!empty($current_user->first_name) && !empty($current_user->last_name) ) {
                                $public_display['display_firstlast'] = $current_user->first_name . ' ' . $current_user->last_name;
                                $public_display['display_lastfirst'] = $current_user->last_name . ' ' . $current_user->first_name;
                            }
                            
                            if(!in_array( $current_user->display_name, $public_display)) {
                                $public_display = array( 'display_displayname' => $current_user->display_name ) + $public_display;
                                $public_display = array_map( 'trim', $public_display );
                                $public_display = array_unique( $public_display );
                            }

                            foreach ($public_display as $id => $item) {
                        ?>
                            <option id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($item); ?>"<?php selected( $current_user->display_name, $item ); ?>><?php echo esc_attr($item); ?></option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
            </div>

    
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="firstname"><?php echo esc_attr($homey_local['fname_label']); ?></label>
                    <input type="text" id="firstname" class="form-control" value="<?php echo esc_attr($first_name);?>" placeholder="<?php echo esc_attr($homey_local['fname_plac']); ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="lastname"><?php echo esc_attr($homey_local['lname_label']); ?></label>
                    <input type="text" id="lastname" class="form-control" value="<?php echo esc_attr($last_name);?>" placeholder="<?php echo esc_attr($homey_local['lname_plac']); ?>">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="native_language"><?php echo esc_attr($homey_local['native_lang_label']); ?></label>
                    <input type="text" id="native_language" value="<?php echo esc_attr($native_language); ?>" class="form-control" placeholder="<?php echo esc_attr($homey_local['native_lang_label']); ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="other_language"><?php echo esc_attr($homey_local['other_lang_label']); ?></label>
                    <input type="text" id="other_language" value="<?php echo esc_attr($other_language); ?>" class="form-control" placeholder="<?php echo esc_attr($homey_local['other_lang_label']); ?>">
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="bio"><?php echo esc_attr($homey_local['bio_label']); ?></label>
                    <textarea id="bio" class="form-control" placeholder="<?php echo esc_attr($homey_local['bio_label']); ?>" rows="3"><?php echo esc_attr($description); ?></textarea>
                </div>
            </div>

            <?php if($gdpr_enabled != 0 ) { ?>
            <div class="col-sm-12 col-xs-12">
                <div class="form-group">
                    <label for="gdpr_agreement"><?php esc_html_e('GDPR Agreement *','homey');?></label>
                    <label class="control control--checkbox">
                        <input <?php if($gdpr_agreement == 'checked'){echo 'checked=checked';}?> type="checkbox" name="gdpr_agreement" id="gdpr_agreement" value="">
                        <span class="contro-text"><?php echo homey_option('gdpr-label'); ?></span>
                        <span class="control__indicator"></span>
                    </label>

                </div>
            </div>
            <div class="col-sm-12 col-xs-12">
                <div class="form-group">
                    <textarea rows="5" readonly="readonly" class="form-control"><?php echo esc_attr($gdpr_agreement_content);?></textarea>
                </div>
            </div>
            <?php } ?>
            
            <div class="col-sm-12 text-right">
                <button type="submit" class="homey_profile_save btn btn-success btn-xs-full-width"><?php echo esc_attr($homey_local['save_btn']); ?></button>
            </div>
        </div>
    </div>
</div><!-- block -->