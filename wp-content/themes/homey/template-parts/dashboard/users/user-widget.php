<?php
global $user_meta;
$single_user_id = isset($_GET['user-id']) ? $_GET['user-id'] : '';
$is_superhost = $user_meta['is_superhost'];
$doc_verified = $user_meta['doc_verified'];
?>
<div class="admin-sidebar">
    <div class="user-sidebar-inner">
        <div class="block">
            <div class="block-body">
                <h3><?php esc_html_e('Actions', 'homey'); ?></h3>
                
                <form>
                    <label><?php esc_html_e('Approve ID verification', 'homey'); ?></label>    
                    <div class="block-section-50">
                        <div class="block-left">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input type="radio" <?php checked($doc_verified, 1); ?> name="doc_verified" value="1">
                                    <span class="control-text"><?php esc_html_e('Yes', 'homey'); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                        </div>
                        <div class="block-right">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input type="radio" <?php checked($doc_verified, 0); ?> name="doc_verified" value="0">
                                    <span class="control-text"><?php esc_html_e('No', 'homey'); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(!homey_is_renter($single_user_id)) { ?> 
                    <label><?php esc_html_e('Promote to super user', 'homey'); ?></label>  
                    <div class="block-section-50">
                        <div class="block-left">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input type="radio" <?php checked($is_superhost, 1); ?> name="is_superhost" value="1">
                                    <span class="control-text"><?php esc_html_e('Yes', 'homey'); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                        </div>
                        <div class="block-right">
                            <div class="form-group">
                                <label class="control control--radio radio-tab">
                                    <input type="radio" <?php checked($is_superhost, 0); ?> name="is_superhost" value="0">
                                    <span class="control-text"><?php esc_html_e('No', 'homey'); ?></span>
                                    <span class="control__indicator"></span>
                                    <span class="radio-tab-inner"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <input type="hidden" name="user_id" id="user_id" value="<?php echo intval($single_user_id); ?>">
                    <?php wp_nonce_field( 'homey_superhost_idverify_nonce', 'homey_superhost_idverify_security' ); ?>
                </form>

                <button id="superhost_docVerify" class="btn btn-success btn-full-width"><?php esc_html_e('Save', 'homey'); ?></button>
            </div>
        </div>
    </div>
</div>