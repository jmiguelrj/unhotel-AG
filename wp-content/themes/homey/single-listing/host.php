<?php
global $post, $homey_prefix, $homey_local, $listing_author;

$is_superhost = $listing_author['is_superhost'];
$doc_verified = $listing_author['doc_verified'];

$verified = false;
if($doc_verified) {
    $verified = true;
}

$reviews = homey_get_host_reviews(get_the_author_meta( 'ID' ));
?>
<div id="host-section" class="host-section">
    <div class="block">
        <div class="block-head">
            <div class="media">
                <div class="media-left">
                    <?php echo ''.$listing_author['photo']; ?>
                </div>
                <div class="media-body">
                    <h2 class="title"><?php echo esc_attr(homey_option('sn_hosted_by')); ?> <span><?php echo esc_attr($listing_author['name']); ?></span>
                    </h2>

                    <ul class="list-inline profile-host-info">
                        <?php if($is_superhost) { ?>
                        <li class="super-host-flag"><i class="fa fa-bookmark"></i> <?php esc_html_e('Super Host', 'homey'); ?></li>
                        <?php } ?>

                        <?php if(!empty($listing_author['country'])) { ?>
                        <li><address><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo esc_attr($listing_author['country']); ?></address></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div><!-- block-head -->
        <div class="block-body">
            <div class="row">
                <?php if(!empty($listing_author['languages'])) { ?>
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <dl>
                        <dt><?php echo esc_attr(homey_option('sn_pr_lang')); ?></dt>
                        <dd><?php echo esc_attr($listing_author['languages']); ?></dd>
                    </dl>    
                </div>
                <?php } ?>
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <dl>
                        <dt><?php echo esc_attr(homey_option('sn_pr_profile_status')); ?></dt>
                        <?php if(user_can( get_the_author_meta( 'ID' ), 'administrator' )) { ?>
                            <dd class="text-success"><i class="fa fa-check-circle-o"></i> <?php echo esc_attr(homey_option('sn_pr_verified')); ?></dd>
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
                        <dt><?php echo esc_attr(homey_option('sn_pr_h_rating')); ?></dt>
                        <dd>
                            <div class="rating">
                                <?php echo ''.$reviews['host_rating']; ?>
                            </div>
                        </dd>
                    </dl>    
                </div>
                <?php } ?>
                
            </div>
            <div class="host-section-buttons">

                <?php if(homey_option('detail_contact_form') != 0 && homey_option('hide-host-contact') !=1 ) { ?>
                <a href="#" data-toggle="modal" data-target="#modal-contact-host" class="btn btn-grey-outlined btn-half-width"><?php echo esc_attr(homey_option('sn_pr_cont_host')); ?></a>
                <?php } ?>

                <a <?php if(homey_option('hide-host-contact') != 0) { echo 'style="width:100%"'; }?> href="<?php echo esc_url($listing_author['link']); ?>" class="btn btn-grey-outlined btn-half-width">
                    <?php echo esc_attr(homey_option('sn_view_profile')); ?>        
                </a>
            </div><!-- block-body -->
        </div><!-- block-body -->

    </div><!-- block -->
</div><!-- host-section -->