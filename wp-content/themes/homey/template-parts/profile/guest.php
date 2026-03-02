<?php
global $wp_query, $homey_local, $homey_prefix;
$current_author = $wp_query->get_queried_object();
$author_id = $current_author->ID;
$author_meta = get_user_meta( $author_id );
$author = homey_get_author_by_id('70', '70', 'img-circle media-object avatar mb-30', $author_id);
$doc_verified = $author['doc_verified'];
$email_verified = $author['is_email_verified'];
$email_address = true;

$verified = false;
if($doc_verified) {
    $verified = true;
}

$facebook = $author['facebook'];
$twitter = $author['twitter'];
$linkedin = $author['linkedin'];
$pinterest = $author['pinterest'];
$instagram = $author['instagram'];
$googleplus = $author['googleplus'];
$youtube = $author['youtube'];
$vimeo = $author['vimeo'];
$show_social = true;
if(empty($facebook) && empty($twitter) && empty($linkedin) && empty($pinterest) && empty($instagram) && empty($googleplus) && empty($youtube) && empty($vimeo)) {
    $show_social = false;
}

$reviews = homey_get_guest_reviews($author_id);
?>
<section class="main-content-area user-profile guest-profile">
        <div class="container">
            <div class="guest-section clearfix">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="block">
                            <div class="block-head">

                                <?php echo ''.$author['photo']; ?>

                                <h2 class="title"><span><?php echo esc_attr($homey_local['pr_iam']); ?></span> <?php echo esc_attr($author['name']); ?></h2>

                                <?php if(!empty($author['country'])) { ?>
                                <address><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo esc_attr($author['country']); ?></address>
                                <?php } ?>

                                <?php if($reviews['is_guest_have_reviews']) { ?>
                                <dl>
                                    <dt class="mb-10"><?php esc_html_e('Guest Rating', 'homey'); ?></dt>
                                    <dd>
                                        <div class="rating">
                                            <?php echo ''.$reviews['guest_rating']; ?>
                                        </div>
                                    </dd>
                                </dl>
                                <?php } ?>

                                <dl class="mb-30">
                                    <dt class="mb-10"><?php esc_html_e('Profile Status', 'homey'); ?></dt>
                                    <?php if($verified) { ?>
                                    <dd class="text-success"><i class="fa fa-check-circle-o"></i> <?php esc_html_e('Verified', 'homey'); ?></dd>
                                    <?php } else { ?>
                                        <dd class="text-danger"><i class="fa fa-close"></i> <?php esc_html_e('Not Verified', 'homey'); ?></dd>
                                    <?php } ?>
                                </dl> 

                                <dl class="mb-0">
                                    <dt class="mb-10"><?php esc_html_e('Provided', 'homey'); ?> </dt>
                                    <?php if($doc_verified == 1) { ?>
                                    <dd><?php esc_html_e('Government ID', 'homey'); ?></dd>
                                    <?php } ?>

                                    <dd><?php esc_html_e('Email address', 'homey'); ?></dd>
                                </dl> 

                            </div><!-- block-head -->
                        </div><!-- block -->
                    </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->

                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">    

                        <div class="block">
                            <div class="block-body">
                                <?php 
                                if($reviews['is_guest_have_reviews']) { 
                                    $total_reviews = $reviews['total_reviews'];
                                    if($total_reviews > 1) {
                                        $rw_text = esc_html__('Reviews', 'homey');
                                    } else {
                                        $rw_text = esc_html__('Review', 'homey');
                                    }
                                ?>
                                <h3><?php echo esc_attr($total_reviews); ?> <?php echo esc_attr($rw_text); ?></h3>
                                <ul class="list-unstyled">
                                    <?php echo ''.$reviews['reviews_data']; ?>
                                </ul>
                                <?php } else { ?>
                                    <h3 class="mb-0"><?php esc_html_e('No Reviews', 'homey'); ?></h3>
                                <?php } ?>
                            </div><!-- block-body -->
                        </div><!-- block -->
                        
                        <?php //include('inc/listing/pagination.php'); ?>
                        
                    </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
                </div>
            </div><!-- guest-section -->
            
            

        </div>
    </div>
</section><!-- main-content-area -->