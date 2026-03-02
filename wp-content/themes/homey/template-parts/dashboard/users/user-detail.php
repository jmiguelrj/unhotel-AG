<?php
global $homey_local, $user_meta, $single_user_id;
$single_user_id = isset($_GET['user-id']) ? $_GET['user-id'] : '';
if(empty($single_user_id) || !homey_is_admin()) {
    return;
}

$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$host_earnings_link = add_query_arg( 'host', $single_user_id, $wallet_page_link );
$guest_deposit_link = add_query_arg( 'guest', $single_user_id, $wallet_page_link );

$active_doc = $active_info = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'documents') {
    $active_doc = 'active';
} else {
    $active_info = 'active';
}

$user_meta = homey_get_author_by_id('100', '100', 'img-circle', $single_user_id);
$photo = $user_meta['photo'];
$name = $user_meta['name'];
$email = $user_meta['email'];
$address = $user_meta['address'];
$native_language = $user_meta['native_language'];
$other_language = $user_meta['other_language'];
$bio = $user_meta['bio'];
$link = $user_meta['link'];
$user_role = homey_get_role_name($single_user_id);

$is_superhost = $user_meta['is_superhost'];
$doc_verified = $user_meta['doc_verified'];
$user_document_id = $user_meta['user_document_id'];
$id_verification_link = wp_get_attachment_url( $user_document_id );

// Emergency Contact 
$em_contact_name = $user_meta['em_contact_name'];
$em_relationship = $user_meta['em_relationship'];
$em_email = $user_meta['em_email'];
$em_phone = $user_meta['em_phone'];

$payout_payment_method = $user_meta['payout_payment_method'];
$payout_paypal_email = $user_meta['payout_paypal_email'];
$payout_skrill_email = $user_meta['payout_skrill_email'];

// Beneficiary Information
$ben_first_name = $user_meta['ben_first_name'];
$ben_last_name = $user_meta['ben_last_name'];
$ben_company_name = $user_meta['ben_company_name'];
$ben_tax_number = $user_meta['ben_tax_number'];
$ben_street_address = $user_meta['ben_street_address'];
$ben_apt_suit = $user_meta['ben_apt_suit'];
$ben_city = $user_meta['ben_city'];
$ben_state = $user_meta['ben_state'];
$ben_zip_code = $user_meta['ben_zip_code'];

//Wire Transfer Information
$bank_account = $user_meta['bank_account'];
$swift = $user_meta['swift'];
$bank_name = $user_meta['bank_name'];
$wir_street_address = $user_meta['wir_street_address'];
$wir_aptsuit = $user_meta['wir_aptsuit'];
$wir_city = $user_meta['wir_city'];
$wir_state = $user_meta['wir_state'];
$wir_zip_code = $user_meta['wir_zip_code'];

//Social links
$facebook     =  $user_meta['facebook'];
$twitter      =  $user_meta['twitter'];
$linkedin     =  $user_meta['linkedin'];
$pinterest    =  $user_meta['pinterest'];
$instagram    =  $user_meta['instagram'];
$googleplus   =  $user_meta['googleplus'];
$youtube      =  $user_meta['youtube'];
$vimeo        =  $user_meta['vimeo'];

$reviews = homey_get_host_reviews($single_user_id);

?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <div class="dashboard-content-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">
                        <div id="superhost_msgs"></div>

                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="<?php echo  esc_attr($active_info); ?>">
                                <a href="#information" aria-controls="information" role="tab" data-toggle="tab"><?php esc_html_e('Information', 'homey'); ?></a>
                            </li>

                            <?php if(!homey_is_renter($single_user_id)) { ?>
                            <li role="presentation">
                                <a href="#reviews" aria-controls="reviews" role="tab" data-toggle="tab"><?php esc_html_e('Reviews', 'homey'); ?></a>
                            </li>
                            <?php } ?>

                            <li class="<?php echo esc_attr($active_doc); ?>" role="presentation">
                                <a href="#documents" aria-controls="documents" role="tab" data-toggle="tab"><?php esc_html_e('Documents', 'homey'); ?></a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane <?php echo esc_attr($active_info); ?>" id="information">
                                <div class="block">
                                    <div class="block-title">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Profile', 'homey'); ?></h2>
                                        </div>
                                        <div class="block-right">
                                            <?php if(homey_is_renter()) { ?>
                                                <a href="<?php echo esc_url($guest_deposit_link); ?>" class="btn btn-primary btn-slim"><strong><?php esc_html_e('Security Deposit', 'homey'); ?></strong></a>
                                            <?php } ?>
                                            <a target="_blank" href="<?php echo esc_url($link); ?>" class="btn btn-primary btn-slim"><strong><?php esc_html_e('View Profile', 'homey'); ?></strong></a>
                                        </div>
                                    </div>
                                    <div class="block-body">
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <div class="profile-image">
                                                    <?php echo ''.$photo; ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-10">
                                                <ul class="list-unstyled list-lined">
                                                    <?php if(!empty($name)) { ?>
                                                    <li>
                                                        <strong><?php esc_html_e('Name', 'homey'); ?></strong> 
                                                        <?php echo esc_attr($name); ?>
                                                    </li>
                                                    <?php } ?>

                                                    <?php if(!empty($email)) { ?>
                                                    <li>
                                                        <strong><?php esc_html_e('Email', 'homey'); ?></strong> 
                                                        <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_attr($email); ?></a> 
                                                        
                                                    </li>
                                                    <?php } ?>

                                                    <li>
                                                        <strong><?php esc_html_e('ID', 'homey'); ?></strong> 
                                                        <?php if(!empty($user_document_id)) { ?>
                                                        <a target="_blank" href="<?php echo esc_url($id_verification_link); ?>"><?php esc_html_e('View Picture', 'homey'); ?></a>
                                                        <?php } else {
                                                            echo esc_html__('Not Available', 'homey');
                                                        }?>
                                                        <?php if($doc_verified) { ?>
                                                        <span class="text-success">
                                                            <i class="fa fa-check-circle-o" aria-hidden="true"></i> 
                                                            <?php esc_html_e('Verified', 'homey'); ?>
                                                        </span>
                                                        <?php } ?>
                                                    </li>

                                                    <?php if(!empty($user_role)) { ?>
                                                    <li>
                                                        <strong><?php esc_html_e('Role', 'homey'); ?></strong> 
                                                        <?php if($is_superhost) { ?>
                                                        <i class="fa fa-bookmark host_role" aria-hidden="true"></i> 
                                                        <?php } ?>
                                                        <?php echo esc_attr($user_role); ?>
                                                    </li>
                                                    <?php } ?>
                                                </ul>

                                                
                                                <ul class="list-unstyled list-lined">
                                                    <li>
                                                        <strong><?php esc_html_e('Address', 'homey'); ?></strong> 
                                                        <?php 
                                                        if(!empty($address)) {
                                                            echo esc_attr($address); 
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </li>
                                                </ul>

                                                <ul class="list-unstyled list-lined">
                                                    <li>
                                                        <strong><?php esc_html_e('Native Language', 'homey'); ?></strong> 
                                                        <?php 
                                                        if(!empty($native_language)) {
                                                            echo esc_attr($native_language); 
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </li>
                                                    <li>
                                                        <strong><?php esc_html_e('Other Language', 'homey'); ?></strong> 
                                                        <?php 
                                                        if(!empty($other_language)) {
                                                            echo esc_attr($other_language); 
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </li>
                                                </ul>
                                                
                                                <ul class="list-unstyled mb-0">
                                                    <li>
                                                        <strong><?php esc_html_e('Bio', 'homey'); ?>:</strong> 
                                                        <?php 
                                                        if(!empty($bio)) {
                                                            echo esc_attr($bio); 
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </li>
                                                </ul>
                                                
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if(!homey_is_renter($single_user_id)) { ?>
                                <div class="block">
                                    <div class="block-title">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Withdraw Method', 'homey'); ?></h2>
                                        </div>
                                        <div class="block-right">
                                            <a href="<?php echo esc_url($host_earnings_link); ?>" class="btn btn-primary btn-slim"><strong><?php esc_html_e('View Earnings', 'homey'); ?></strong></a>
                                        </div>
                                    </div>
                                    <div class="block-body">
                                        <ul class="list-unstyled">
                                            <li><strong><?php esc_html_e('Method', 'homey'); ?></strong> <?php echo homey_get_payout_method($payout_payment_method); ?></li>
                                        </ul>
                                        <ul class="list-unstyled list-lined">
                                            <li>
                                                <strong><?php esc_html_e('Beneficiary Name', 'homey'); ?></strong> 
                                                <?php 
                                                if(!empty($ben_first_name) || !empty($ben_last_name)) {
                                                    echo esc_attr($ben_first_name).' '.esc_attr($ben_last_name); 
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </li>
                                            <li>
                                                <strong><?php esc_html_e('Company Name', 'homey'); ?></strong> 
                                                <?php echo esc_attr($ben_company_name); ?>
                                                <?php 
                                                if(!empty($ben_company_name)) {
                                                    echo esc_attr($ben_company_name); 
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </li>
                                            <li>
                                                <strong><?php esc_html_e('Tax Identification Number', 'homey'); ?></strong> 
                                                <?php 
                                                if(!empty($ben_tax_number)) {
                                                    echo esc_attr($ben_tax_number); 
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled list-lined">
                                            <li>
                                                <strong><?php esc_html_e('Address', 'homey'); ?></strong> 
                                                <?php 
                                                if(!empty($ben_street_address)) {
                                                    echo esc_attr($ben_street_address); 
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </li>
                                        </ul>

                                        <ul class="list-unstyled list-lined mb-0">
                                            <?php if($payout_payment_method == 'paypal') { ?>

                                                    <li>
                                                        <strong><?php esc_html_e('PayPal Email', 'homey'); ?></strong> 
                                                        <?php echo esc_attr($payout_paypal_email); ?>
                                                    </li>

                                            <?php } elseif ($payout_payment_method == 'skrill') { ?>

                                                    <li>
                                                        <strong><?php esc_html_e('Skrill Email', 'homey'); ?></strong> 
                                                        <?php echo esc_attr($payout_skrill_email); ?>
                                                    </li>
                                                
                                            <?php } elseif ($payout_payment_method == 'wire') { ?>
                                                <li>
                                                    <strong><?php esc_html_e('Beneficiary Account Number', 'homey'); ?></strong> 
                                                    <?php 
                                                    if(!empty($bank_account)) {
                                                        echo esc_attr($bank_account); 
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('SWIFT', 'homey'); ?></strong> 
                                                    <?php 
                                                    if(!empty($swift)) {
                                                        echo esc_attr($swift); 
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('Bank Name', 'homey'); ?></strong> 
                                                    <?php 
                                                    if(!empty($bank_name)) {
                                                        echo esc_attr($bank_name); 
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('Bank Address', 'homey'); ?></strong> 
                                                    <?php 
                                                    if(!empty($wir_street_address)) {
                                                        echo esc_attr($wir_street_address).', '.$wir_city.', '.$wir_state.', '.$wir_zip_code;
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php } ?>

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

                                <?php if(!homey_is_renter($single_user_id)) { ?>
                                <div class="block">
                                    <div class="block-title">
                                        <h2 class="title"><?php esc_html_e('Social Media', 'homey'); ?></h2>
                                    </div>
                                    <div class="block-body">
                                        <ul class="list-unstyled mb-0 list-lined">
                                            <?php if(!empty($facebook)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Facebook', 'homey'); ?></strong>
                                                 <a target="_blank" href="<?php echo esc_url($facebook); ?>"><?php esc_html_e('View profile', 'homey'); ?></a>
                                             </li>
                                            <?php } ?>

                                            <?php if(!empty($twitter)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Twitter', 'homey'); ?></strong> 
                                                <a target="_blank" href="<?php echo esc_url($twitter); ?>"><?php esc_html_e('View profile', 'homey'); ?></a>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($googleplus)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Google Plus', 'homey'); ?></strong> 
                                                <a target="_blank" href="<?php echo esc_url($googleplus); ?>"><?php esc_html_e('View profile', 'homey'); ?></a>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($instagram)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Instagram', 'homey'); ?></strong> 
                                                <a target="_blank" href="<?php echo esc_url($instagram); ?>"><?php esc_html_e('View profile', 'homey'); ?></a>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($pinterest)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Pinterest', 'homey'); ?></strong> 
                                                <a target="_blank" href="<?php echo esc_url($pinterest); ?>"><?php esc_html_e('View profile', 'homey'); ?></a>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($linkedin)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Linkedin', 'homey'); ?></strong> 
                                                <a target="_blank" href="<?php echo esc_url($linkedin); ?>"><?php esc_html_e('View profile', 'homey'); ?></a>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($youtube)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Youtube', 'homey'); ?></strong> 
                                                <a target="_blank" href="<?php echo esc_url($youtube); ?>"><?php esc_html_e('View profile', 'homey'); ?></a>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($vimeo)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Vimeo', 'homey'); ?></strong> 
                                                <a target="_blank" href="<?php echo esc_url($vimeo); ?>"><?php esc_html_e('View profile', 'homey'); ?></a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php } ?>

                            </div>
                            
                            <div role="tabpanel" class="tab-pane" id="reviews">
                                <div class="host-rating-section">
                                    <div class="block">
                                        <div class="block-title">
                                            <div class="block-left">
                                                <h2 class="title"><?php esc_html_e('Reviews and Ratings', 'homey'); ?></h2>
                                            </div>
                                            <div class="block-right rating">
                                                <strong><?php echo esc_html__('Overall Ratings', 'homey'); ?>:</strong> <?php echo ''.$reviews['host_rating']; ?>
                                            </div>
                                        </div>
                                        <div class="block-body">
                                            <div class="reviews-section">
                                                <ul class="list-unstyled">
                                                    <?php 
                                                    if(!empty($reviews['reviews_data'])) {
                                                        echo ''.$reviews['reviews_data']; 
                                                    } else {
                                                        echo esc_html__('No result found.', 'homey');
                                                    }
                                                    ?>
                                                </ul>
                                            </div><!-- reviews-section -->
                                        </div><!-- block-body -->
                                    </div><!-- block -->
                                </div><!-- host-rating-section -->    
                            </div>
                            <div role="tabpanel" class="tab-pane <?php echo esc_attr($active_doc); ?>" id="documents">
                                <div class="block">
                                    <div class="block-title">
                                        <h2 class="title"><?php esc_html_e('Uploaded Documents', 'homey'); ?></h2>
                                    </div>
                                    <div class="block-body">
                                        <?php 
                                        $docVry = homey_user_document_for_verification($user_document_id, '800', '600', 'img-responsive'); 
                                        if(!empty($docVry)) {
                                            echo ''.$docVry;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
    </div><!-- .dashboard-content-area -->
       
    <aside class="dashboard-sidebar admin-dashboard-sidebar">
        <?php get_template_part('template-parts/dashboard/users/user-widget'); ?>
    </aside><!-- .dashboard-sidebar -->
    
</div><!-- .user-dashboard-right -->