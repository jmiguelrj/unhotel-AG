<?php
global $current_user, $homey_local;
wp_get_current_user();
$userID  =  $current_user->ID;
$first_name  =  $current_user->first_name;
$last_name  =  $current_user->last_name;
$display_name = $current_user->display_name;

$offsite_payment = homey_option('off-site-payment');
$enable_wallet = homey_option('enable_wallet');


$homey_author = homey_get_author_by_id('36', '36', 'img-circle', $userID);
$author_photo = $homey_author['photo'];

$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$dashboard = homey_get_template_link_dash('template/dashboard.php');
$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');
$dashboard_listings = homey_get_template_link_dash('template/dashboard-listings.php');
$dashboard_add_listing = homey_get_template_link_dash('template/dashboard-submission.php');
$dashboard_favorites = homey_get_template_link_dash('template/dashboard-favorites.php');
$dashboard_search = homey_get_template_link_dash('template/dashboard-saved-searches.php');
$dashboard_reservations = homey_get_template_link_dash('template/dashboard-reservations.php');
$dashboard_host_reservations = homey_get_template_link_dash('template/dashboard-reservations2.php');
$dashboard_messages = homey_get_template_link_dash('template/dashboard-messages.php');
$dashboard_invoices = homey_get_template_link_dash('template/dashboard-invoices.php');

$dashboard_wallet = homey_get_template_link_dash('template/dashboard-wallet.php');
$earnings_page_link = add_query_arg( 'dpage', 'earnings', $dashboard_wallet );
$payout_request_link = add_query_arg( 'dpage', 'payout-request', $dashboard_wallet );
$payouts_page_link = add_query_arg( 'dpage', 'payouts', $dashboard_wallet );
$payouts_setup_page = add_query_arg( 'dpage', 'payment-method', $dashboard_wallet );
$payment_method_setup = add_query_arg( 'dpage', 'payment-method', $dashboard_profile );

$payouts_page_link = add_query_arg( 'dpage', 'payouts', $wallet_page_link );

$verification_page = add_query_arg( 'dpage', 'verification', $dashboard_profile );
$password_page = add_query_arg( 'dpage', 'password-reset', $dashboard_profile );

$security_deposits_page = add_query_arg( 'dpage', 'security-deposits', $wallet_page_link );

$all_users = add_query_arg( 'dpage', 'users', $dashboard );

$reservation_payment = homey_option('reservation_payment');

$home_link = home_url('/');

?>
<nav id="user-nav" class="nav-dropdown main-nav-dropdown collapse navbar-collapse">
    <ul>
        <?php 
        if( !empty($dashboard) ) {
            echo '<li>
                    <a href="'.esc_url($dashboard).'">
                        <i class="fa fa-home"></i> '.$homey_local['m_dashboard_label'].'
                    </a>
                </li>';
        }

        if( !empty($dashboard_profile) ) {
            echo '<li>
                <a href="'.esc_url($dashboard_profile).'">
                    <i class="fa fa-user-o"></i> '.$homey_local['m_profile_label'].'
                </a>';
                echo '<ul>';
                    if(!homey_is_admin()) {
                        echo '<li><a href="'.esc_url($verification_page).'"><i class="fa fa-angle-right"></i> '.esc_html__('Verification', 'homey').'</a></li>';
                    }
                    echo '<li><a href="'.esc_url($password_page).'"><i class="fa fa-angle-right"></i> '.esc_html__('Password', 'homey').'</a></li>';

                    if($offsite_payment != 0 || $enable_wallet != 0) {
                        echo '<li><a href="'.esc_url($payment_method_setup).'"><i class="fa fa-angle-right"></i> '.esc_html__('Payment Method', 'homey').'</a></li>';
                    }

                echo '</ul>';

            echo '</li>';
            
        }

        if(!homey_is_renter()) {
            if( !empty($dashboard_listings) ) {
                echo '<li>
                    <a href="'.esc_url($dashboard_listings).'"><i class="fa fa-th-list"></i> '.$homey_local['m_listings_label'].'</a>
                </li>';
            }

            if( !empty($dashboard_add_listing) ) {
                echo '<li>
                    <a href="'.esc_url($dashboard_add_listing).'"><i class="fa fa-plus-circle"></i> '.$homey_local['m_add_listing_label'].' </a>
                </li>';
            }
        }

        if( !empty($dashboard_reservations) ) {

            if(homey_is_renter()) {
                echo '<li>
                    <a href="'.esc_url($dashboard_reservations).'"><i class="fa fa-calendar"></i>  '.$homey_local['m_reservation_label'].'</a>
                </li>';
            } elseif(homey_is_admin()) {
                echo '<li>
                    <a href="'.esc_url($dashboard_reservations).'"><i class="fa fa-calendar"></i>  '.esc_html__('Bookings', 'homey').'</a>
                </li>';
            } else {
                $new_notification = homey_booking_notification(1);
                $new_notification = $new_notification > 0 ? '<span class="new-booking-alert" style="display: block;"></span>' : '<span class="new-booking-alert" style="display: none;"></span>';

                echo '<li>
                    <a href="'.esc_url($dashboard_reservations).'"><i class="fa fa-calendar"></i>  '.esc_html__('My Bookings', 'homey').' '.$new_notification.'</a>
                </li>';
            }
        }

        if( !empty($dashboard_host_reservations) && !homey_is_renter()) {
            echo '<li>
                <a href="'.esc_url($dashboard_host_reservations).'"><i class="fa fa-calendar"></i>  '.esc_html__('My Reservations', 'homey').'</a>
            </li>';
        }

        if($enable_wallet != 0) {
            if($reservation_payment == 'percent' || $reservation_payment == 'full') {
                if(homey_is_host()) {
                    if( !empty($dashboard_wallet) ) {
                        echo '<li>
                            <a href="'.esc_url($dashboard_wallet).'"><i class="fa fa-money"></i> '.esc_html__('Wallet', 'homey').'</a>

                                <ul>
                                    <li><a href="'.esc_url($earnings_page_link).'"><i class="fa fa-angle-right"></i> '.esc_html__('Earnings', 'homey').'</a></li>
                                    <li><a href="'.esc_url($payouts_page_link).'"><i class="fa fa-angle-right"></i> '.esc_html__('Payouts', 'homey').'</a></li>
                                </ul>
                        </li>';
                    }
                }

                if(homey_is_renter()) {
                    if( !empty($dashboard_wallet) ) {
                        echo '<li>
                            <a href="'.esc_url($dashboard_wallet).'"><i class="fa fa-money"></i> '.esc_html__('Wallet', 'homey').'</a>
                            <ul>
                                <li><a href="'.esc_url($security_deposits_page).'"><i class="fa fa-angle-right"></i> '.esc_html__('Security Deposit', 'homey').'</a></li>

                                <li><a href="'.esc_url($payouts_page_link).'"><i class="fa fa-angle-right"></i> '.esc_html__('Payouts', 'homey').'</a></li>
                            </ul>
                        </li>';
                    }
                }

                if(homey_is_admin()) {
                    if( !empty($dashboard_wallet) ) {
                        echo '<li>
                            <a href="'.esc_url($payouts_page_link).'"><i class="fa fa-money"></i> '.esc_html__('Payouts', 'homey').'</a>
                        </li>';
                    }
                }
            }
        }

        if( !empty($dashboard_favorites) ) {
            echo '<li>
                <a href="'.esc_url($dashboard_favorites).'"><i class="fa fa-heart-o"></i> '.$homey_local['m_favorites_label'].' </a>
            </li>';
        }

        if( !empty($dashboard_invoices) ) {
            echo '<li>
                <a href="'.esc_url($dashboard_invoices).'"><i class="fa fa-file"></i> '.$homey_local['m_invoices_label'].' </a>
            </li>';
        }

        if(homey_is_admin()) {
            if( !empty($all_users) ) {
                echo '<li class="">
                    <a href="'.esc_url($all_users).'"><i class="fa fa-users"></i> '.esc_html__('Users', 'homey').'</a>
                </li>';
            }
        }


        if( !empty($dashboard_messages) ) {
            echo '<li>
                <a href="'.esc_url($dashboard_messages).'"><i class="fa fa-comments-o"></i> '.$homey_local['m_messages_label'].' '.homey_messages_notification().'</a>
            </li>';
        }

        echo '<li>
            <a href="' . wp_logout_url(home_url('/')) . '"><i class="fa fa-sign-out"></i> '.$homey_local['m_logout_label'].' </a>
        </li>';
        ?>
    </ul>
</nav><!-- nav-collapse -->