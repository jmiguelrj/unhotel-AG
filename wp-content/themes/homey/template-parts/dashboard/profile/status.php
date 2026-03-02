<?php global $author_info; 
$is_photo = $author_info['is_photo'];
$is_email = $author_info['is_email'];
?>
<div class="user-sidebar-inner">
    <div class="block">
        <div class="block-body">
            <div class="media">
                <div class="media-left">
                    <div class="media-object">
                        <?php echo ''.$author_info['photo']; ?>
                    </div>
                </div>
                <div class="media-body media-middle">
                    <h4 class="media-heading mb-0"><?php esc_html_e('Profile Completed', 'homey'); ?></h4>
                    <h1 class="media-count"><?php echo esc_attr($author_info['profile_status']); ?></h1>
                </div>
            </div>
        </div>
        <div class="block-verify">
            <div class="block-col block-col-50">
                <div class="block-icon text-secondary"><i class="fa fa-user-circle-o"></i></div>
                <p><strong><?php esc_html_e('Profile Picture', 'homey'); ?></strong></p>
                <?php if($is_photo) { ?>
                    <p class="text-success"><i class="fa fa-check-circle-o"></i> <?php esc_html_e('Done', 'homey'); ?></p>
                <?php } else { ?>
                    <p class="text-danger"><i class="fa fa-times-circle"></i></p>
                <?php } ?>
            </div>
            <div class="block-col block-col-50">
                <div class="block-icon text-secondary"><i class="fa fa-envelope-open-o"></i></div>
                <p><strong><?php esc_html_e('Email Address', 'homey'); ?></strong></p>
                <?php if(homey_is_admin() || $author_info['is_email_verified']) { ?>
                    <p class="text-success"><i class="fa fa-check-circle-o"></i> <?php esc_html_e('VERIFIED', 'homey'); ?></p>
                <?php } else { ?>
                    <p class="text-danger"><i class="fa fa-times-circle"></i></p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>