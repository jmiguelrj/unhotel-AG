<?php global $author_info; 
$is_photo = $author_info['is_photo'];
$is_email = $author_info['is_email'];
?>
<div class="block visible-sm visible-xs">
    <div class="block-head text-center">
        <h2 class="title"> <?php esc_html_e('Profile Completed', 'homey'); ?> <?php echo esc_attr($author_info['profile_status']); ?> </h2>
    </div>
    <div class="block-bordered">
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
            <?php if($is_email) { ?>
                <p class="text-success"><i class="fa fa-check-circle-o"></i> <?php esc_html_e('Done', 'homey'); ?></p>
            <?php } else { ?>
                <p class="text-danger"><i class="fa fa-times-circle"></i></p>
            <?php } ?>
        </div>
    </div>
    <div class="homy-progress-bar">
        <div class="progress-bar-inner secondary-backgroud" style="width: <?php echo esc_attr($author_info['profile_status']); ?>;"></div>
        <span class="bar-title"><?php esc_html_e('Progress', 'homey'); ?></span>
        <span class="bar-number"><?php echo esc_attr($author_info['profile_status']); ?></span>
    </div>
</div><!-- .profile-progress-block  -->