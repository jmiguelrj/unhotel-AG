<?php
global $wp_query, $homey_local, $homey_prefix;
$current_author = $wp_query->get_queried_object();
$author_id = $current_author->ID;
$author_meta = get_user_meta( $author_id );
$user_meta = homey_get_author_by_id('100', '100', 'img-circle', $author_id);

$author = homey_get_author_by_id('70', '70', 'img-circle media-object avatar', $author_id);
$facebook = $author['facebook'];
$twitter = $author['twitter'];
$linkedin = $author['linkedin'];
$pinterest = $author['pinterest'];
$instagram = $author['instagram'];
$googleplus = $author['googleplus'];
$youtube = $author['youtube'];
$vimeo = $author['vimeo'];
$doc_verified = $author['doc_verified'];

// Emergency Contact
$em_contact_name = $user_meta['em_contact_name'];
$em_relationship = $user_meta['em_relationship'];
$em_email = $user_meta['em_email'];
$em_phone = $user_meta['em_phone'];

$show_social = true;
if(empty($facebook) && empty($twitter) && empty($linkedin) && empty($pinterest) && empty($instagram) && empty($googleplus) && empty($youtube) && empty($vimeo)) {
    $show_social = false;
}

$verified = false;
if($doc_verified) {
    $verified = true;
}

$current_page_user = homey_user_role_by_user_id($author_id);

$reviews = homey_get_host_reviews($author_id);

$host_email = is_email( $author['email'] );

$enable_forms_gdpr = homey_option('enable_forms_gdpr');
$forms_gdpr_text = homey_option('forms_gdpr_text');
$form_type = homey_option('form_type');
$host_profile_contact = homey_option('host_profile_contact');
$hide_host_contact = homey_option('hide-host-contact');

$is_superhost = $author['is_superhost'];

if($hide_host_contact == 1) {
    $con_classes = 'col-xs-12 col-sm-12 col-md-12 col-lg-12';
} else {
    $con_classes = 'col-xs-12 col-sm-12 col-md-8 col-lg-8';
}

?>

<section class="main-content-area user-profile host-profile">
    <div class="container">
        <div class="host-section clearfix">
            <div class="row">
                <div class="<?php echo esc_attr($con_classes); ?>">
                    <div class="block" <?php if($hide_host_contact == 1){ echo 'style="min-height:auto"';}?>>
                        <div class="block-head">
                            <div class="media">
                                <div class="media-left">
                                   <?php echo ''.$author['photo']; ?>
                                </div>
                                <div class="media-body">
                                    <h2 class="title"><span><?php echo esc_attr($homey_local['pr_iam']); ?></span> <?php echo esc_attr($author['name']); ?></h2>
                                    

                                    <ul class="list-inline profile-host-info">
                                        <?php if($is_superhost) { ?>
                                        <li class="super-host-flag"><i class="fa fa-bookmark"></i> <?php esc_html_e('Super Host', 'homey'); ?></li>
                                        <?php } ?>

                                        <?php if(!empty($author['country'])) { ?>
                                        <li><address><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo esc_attr($author['country']); ?></address></li>
                                        <?php } ?>
                                    </ul>
                                
                                </div>
                            </div>
                        </div><!-- block-head -->
                        <div class="block-body">
                            <?php if(!empty($author['bio'])) { ?>
                            <p><?php echo ''.($author['bio']); ?></p>
                            <?php } ?>

                            <?php if($show_social && $hide_host_contact != 1) { ?>
                            <div class="profile-social-icons">
                            <?php if($hide_host_contact != 1) { ?>
                                <?php echo esc_attr($homey_local['pr_followme']); ?>: 
                                <?php if(!empty($facebook)) { ?>
                                    <a class="btn-facebook" href="<?php echo esc_url($facebook); ?>"><i class="fa fa-facebook"></i></a>
                                    <?php } ?>

                                    <?php if(!empty($twitter)) { ?>
                                    <a class="btn-twitter" href="<?php echo esc_url($twitter); ?>"><i class="fa fa-twitter"></i></a>
                                    <?php } ?>

                                    <?php if(!empty($googleplus)) { ?>
                                    <a class="btn-google" href="<?php echo esc_url($googleplus); ?>"><i class="fa fa-google"></i></a>
                                    <?php } ?>

                                    <?php if(!empty($instagram)) { ?>
                                    <a class="btn-instagram" href="<?php echo esc_url($instagram); ?>"><i class="fa fa-instagram"></i></a>
                                    <?php } ?>

                                    <?php if(!empty($pinterest)) { ?>
                                    <a class="btn-pinterest" href="<?php echo esc_url($pinterest); ?>"><i class="fa fa-pinterest"></i></a>
                                    <?php } ?>

                                    <?php if(!empty($linkedin)) { ?>
                                    <a class="btn-linkedin" href="<?php echo esc_url($linkedin); ?>"><i class="fa fa-linkedin"></i></a>
                                    <?php } ?>

                                    <?php if(!empty($youtube)) { ?>
                                    <a class="btn-youtube" href="<?php echo esc_url($youtube); ?>"><i class="fa fa-youtube"></i></a>
                                    <?php } ?>

                                    <?php if(!empty($vimeo)) { ?>
                                    <a class="btn-vimeo" href="<?php echo esc_url($vimeo); ?>"><i class="fa fa-vimeo"></i></a>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <?php } ?>
                            

                            <div class="row">
                                <?php if(!empty($author['languages'])) { ?>
                                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                    <dl>
                                        <dt><?php echo esc_attr($homey_local['pr_lang']); ?></dt>
                                        <dd><?php echo esc_attr($author['languages']);?></dd>
                                    </dl>    
                                </div>
                                <?php } ?>
                                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                    <dl>
                                        
                                        <dt><?php echo esc_attr($homey_local['pr_profile_status']); ?> </dt>


                                        <?php 
                                        if($current_page_user == 'administrator') { ?>

                                            <dd class="text-success">
                                                <i class="fa fa-check-circle-o"></i> 
                                                <?php echo esc_attr($homey_local['pr_verified']); ?>
                                            </dd>

                                        <?php    
                                        } else {
                                        if($verified) { ?>
                                            <dd class="text-success"><i class="fa fa-check-circle-o"></i> <?php esc_html_e('Verified', 'homey'); ?></dd>
                                            <?php } else { ?>
                                                <dd class="text-danger"><i class="fa fa-close"></i> <?php esc_html_e('Not Verified', 'homey'); ?></dd>
                                            <?php } 
                                        }?>
                                    </dl>    
                                </div>
                                <?php if($reviews['is_host_have_reviews']) { ?>
                                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                    <dl>
                                        <dt><?php echo esc_attr($homey_local['pr_h_rating']); ?></dt>
                                        <dd>
                                            <div class="rating">
                                                <?php echo ''.$reviews['host_rating']; ?>
                                            </div>
                                        </dd>
                                    </dl>    
                                </div>
                                <?php } ?>
                            </div>
                        </div><!-- block-body -->
                    </div><!-- block -->
                    <?php if(homey_is_admin()){ ?>
                    <!--zahid.k-->
                    <div class="block">
                        <div class="block-title">
                            <h2 class="title"><?php esc_html_e('Emergency Contact', 'homey'); ?></h2>
                        </div>
                        <div class="block-body">
                            <ul class="list-unstyled list-lined">
                                <li>
                                    <strong><?php esc_html_e('Contact Name', 'homey'); ?></strong>
                                    <?php
                                    if(!empty($em_contact_name)) {
                                        echo esc_attr($em_contact_name);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </li>
                                <li>
                                    <strong><?php esc_html_e('Relationship', 'homey'); ?></strong>
                                    <?php
                                    if(!empty($em_relationship)) {
                                        echo esc_attr($em_relationship);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </li>
                            </ul>
                            <ul class="list-unstyled list-lined mb-0">
                                <li>
                                    <strong><?php esc_html_e('Phone Number', 'homey'); ?></strong>
                                    <?php
                                    if(!empty($em_phone)) {
                                        echo esc_attr($em_phone);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </li>
                                <li>
                                    <strong><?php esc_html_e('Email', 'homey'); ?></strong>
                                    <?php
                                    if(!empty($em_email)) {
                                        echo esc_attr($em_email);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!--zahid.k-->
                    <?php } ?>
                </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->

                <?php if($hide_host_contact != 1) { ?>
                <?php if($host_email) { ?>
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="host-contact-form">
                        <div class="block">
                            <div class="block-body">
                                <h3 class="title mb-20"><?php echo esc_html__('Contact me', 'homey'); ?></h3>
                                <div class="review-form-block">
                                    
                                    <?php 
                                    if($form_type != 'custom_form') {
                                        
                                        if( !empty($host_profile_contact) ) {
                                            echo do_shortcode($host_profile_contact);
                                        } else {
                                            echo esc_html__('Shortcode missing', 'homey');
                                        }

                                    } else { ?>
                                    <form class="form-msg">
                                        <input type="hidden" id="target_email" name="target_email" value="<?php echo antispambot($host_email); ?>">
                                        <input type="hidden" name="host_detail_ajax_nonce" id="host_detail_ajax_nonce" value="<?php echo wp_create_nonce('host-contact-nonce'); ?>"/>
                                        <input type="hidden" name="action" value="homey_contact_host" />

                                        <div class="form-group">
                                            
                                            <input type="text" name="name" class="form-control" placeholder="<?php echo esc_attr($homey_local['fname_plac']); ?>">
                                        </div>
                                        <div class="form-group">
                                            
                                            <input type="email" name="email" class="form-control" placeholder="<?php echo esc_attr($homey_local['email_plac']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="phone" class="form-control" placeholder="<?php echo esc_attr($homey_local['con_phone']); ?>">
                                        </div>

                                        <div class="form-group">
                                            
                                            <textarea class="form-control" name="message" placeholder="<?php echo esc_attr($homey_local['message_plac']); ?>" rows="5"></textarea>
                                        </div>

                                        <?php if($enable_forms_gdpr != 0) { ?>
                                        <div class="form-group checkbox">
                                            <label>
                                                <input name="privacy_policy" type="checkbox">
                                                <?php echo wp_kses($forms_gdpr_text, homey_allowed_html()); ?>
                                            </label>
                                        </div>
                                        <?php } ?>

                                        <?php get_template_part('template-parts/google', 'reCaptcha'); ?>

                                        <button id="host_detail_contact" class="btn btn-primary btn-full-width"><?php echo esc_html__('Send Message', 'homey'); ?></button>
                                    </form>
                                    <?php } ?>
                                </div>
                                <div id="form_messages"></div>
                            </div><!-- block-body -->
                        </div>
                    </div>
                </div><!-- col-xs-12 col-sm-12 col-md-4 col-lg-4 -->
                <?php } ?>
                <?php } ?>

            </div>
        </div><!-- host-section -->

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">    
                <div class="host-profile-tabs">

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#listings" aria-controls="listings" role="tab" data-toggle="tab"><?php echo esc_attr($homey_local['pr_listing_label']); ?></a></li>
                        <li role="presentation"><a href="#reviews" aria-controls="reviews" role="tab" data-toggle="tab"><?php echo esc_attr($homey_local['rating_reviews_label']); ?></a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade in active" id="listings">
                            <div class="host-property-section">
                                <?php
                                $per_page_listings = 7;
                                $author_args = array(
                                    'post_type' => 'listing',
                                    'posts_per_page' => "{$per_page_listings}",
                                    'author' => $author_id
                                );

                                $wp_query = new WP_Query( $author_args );

                                if ( $wp_query->have_posts() ) :
                                    $listing_founds = $wp_query->found_posts;
                                ?>
                                <div id="listings_module_section" class="listing-wrap host-listing-wrap">
                                    <div id="module_listings" class="item-row item-list-view">
                                        <?php
                                        while ( $wp_query->have_posts() ) : $wp_query->the_post();

                                            get_template_part('template-parts/listing/listing-item');

                                        endwhile;
                                        ?>
                                    </div>

                                    <?php if($listing_founds > $per_page_listings) { ?>
                                    <div class="homey-loadmore loadmore text-center">
                                        <a
                                        data-paged="2" 
                                        data-limit="<?php echo $per_page_listings; ?>"
                                        data-style="list"  
                                        data-author="yes" 
                                        data-authorid="<?php echo esc_attr($author_id); ?>"
                                        data-country=""  
                                        data-state="" 
                                        data-city="" 
                                        data-area="" 
                                        data-featured="" 
                                        data-offset=""
                                        data-sortby=""
                                        href="#" 
                                        class="btn btn-primary btn-long">
                                            <i id="spinner-icon" class="fa fa-spinner fa-pulse fa-spin fa-fw" style="display: none;"></i>
                                            <?php echo esc_attr($homey_local['loadmore_btn']); ?>
                                        </a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php
                                wp_reset_postdata();
                                else:
                                            
                                endif;
                                ?>
                            </div><!-- host-property-section -->
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="reviews">
                            <div class="host-rating-section">
                                <div class="block">
                                    <div class="block-body">
                                        <div class="reviews-section">
                                            <ul class="list-unstyled">
                                                <?php echo $reviews['reviews_data']; ?>
                                            </ul>
                                        </div><!-- reviews-section -->
                                    </div><!-- block-body -->
                                </div><!-- block -->
                            </div><!-- host-rating-section -->
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="posts">
                            <div class="block">
                                <div class="block-body">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- host-profile-tabs -->
            </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
        </div>
    </div>
</section><!-- main-content-area -->
