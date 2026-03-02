<?php 
global $homey_local;

$enable_search = homey_option('enable_search');
$search_position = homey_option('search_position');
$search_pages = homey_option('search_pages');
$search_selected_pages = homey_option('search_selected_pages');

$splash_page_nav = homey_option('splash_page_nav');
$menu_sticky = homey_option('menu-sticky');

if(isset($_GET['search_position'])) {
    $search_position = $_GET['search_position'];
}

if(homey_is_dashboard()) { ?>
<div class="header-dashboard no-cache-<?php echo strtotime("now"); ?>">
<?php } ?>

<div class="nav-area header-type-3 no-cache-<?php echo strtotime("now"); ?>">
    <!-- top bar -->
    <?php 
    if(homey_topbar_needed()) {
        get_template_part('template-parts/header/top-bar'); 
    }
    ?>

    <!-- desktop nav -->
    <header id="homey_nav_sticky" class="header-nav hidden-sm hidden-xs no-cache-<?php echo strtotime("now"); ?>" data-sticky="<?php echo esc_attr( $menu_sticky ); ?>">
        <div class="top-inner-header">
            <div class="<?php homey_header_container(); ?>">
                <div class="header-inner clearfix">
                    <div class="header-comp-left">
                        <?php get_template_part('template-parts/header/social'); ?>
                    </div>
                    <div class="header-comp-logo">
                        <?php get_template_part('template-parts/header/logo'); ?>
                    </div>
                    <?php if( class_exists('Homey_login_register') ): ?>
                    <div class="header-comp-right text-right no-cache-<?php echo strtotime("now"); ?>">
                        <?php 
                        if( is_user_logged_in() ) { 
                            get_template_part ('template-parts/header/account');
                        } else {
                            get_template_part ('template-parts/header/login-register-v1');
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="bottom-inner-header no-cache-<?php echo strtotime("now"); ?>">
            <div class="<?php homey_header_container(); ?>">
                <div class="header-inner clearfix">
                    <div class="header-comp-nav text-center no-cache-<?php echo strtotime("now"); ?>">
                        <?php if(!homey_is_splash() || $splash_page_nav != 0 ) { ?>
                            <?php get_template_part('template-parts/header/main-nav'); ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- mobile header -->
    <?php get_template_part ('template-parts/header/header-mobile'); ?>

    <?php 
    if( !homey_is_dashboard() ) {

        if(homey_search_needed()) {

            if (!is_home() && !is_singular('post')) {
                if ($enable_search != 0 && $search_position == 'under_nav') {
                    if ($search_pages == 'only_home') {
                        if (is_front_page()) {
                            get_template_part ('template-parts/search/main-search');
                        }
                    } elseif ($search_pages == 'all_pages') {
                            get_template_part ('template-parts/search/main-search');

                    } elseif ($search_pages == 'only_innerpages') {
                        if (!is_front_page()) {
                            get_template_part ('template-parts/search/main-search');
                        }
                    } else if( $search_pages == 'specific_pages' ) {
                        if( is_page( $search_selected_pages ) ) {
                            get_template_part ('template-parts/search/main-search');
                        }
                    } else if( $search_pages == 'only_taxonomy_pages' ) { 
                        if( is_tax() ) { 
                            get_template_part ('template-parts/search/main-search');
                        }
                    }
                }
            }
        } //homey_search_needed
    } //homey_is_dashboard
    ?>
</div>

<?php if(homey_is_dashboard()) { ?>
</div>
<?php } ?>