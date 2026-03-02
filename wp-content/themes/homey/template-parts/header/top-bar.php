<?php
$top_bar_left = homey_option('top_bar_left');
$top_bar_right = homey_option('top_bar_right');
$top_bar_mobile = homey_option('top_bar_mobile');
$top_bar_width = homey_option('top_bar_width');

if(( is_page_template(array('template/template-search.php')) && homey_option('search_result_page') == 'half_map' ) ) {
    $css_class = 'container-fluid';
    $top_bar_width = $css_class;
}

$nav_left_class = $nav_right_class = $top_bar_right_width = $top_bar_left_width = $top_has_nav = $hide_top_bar_mobile = $top_drop_downs_left = $top_drop_downs_right ='';

if( $top_bar_left == 'menu_bar' ) {
    $nav_left_class = 'top-nav-area';
    $top_has_nav = 'top-has-nav';

} elseif( $top_bar_right == 'menu_bar' ) {
    $nav_right_class = 'top-nav-area';
    $top_has_nav = 'top-has-nav';
}

if( $top_bar_left == 'none' ) {
    $top_bar_right_width = 'homey-top-bar-full';
}

if( $top_bar_right == 'none' ) {
    $top_bar_left_width = 'homey-top-bar-full';
}

if( $top_bar_mobile != 0 ) {
    $hide_top_bar_mobile = 'hide-top-bar-mobile';
}

if( $top_bar_left == 'currency_switchers' ) {
    $top_drop_downs_left = 'top-drop-downs';
}
if( $top_bar_right == 'currency_switchers' ) {
    $top_drop_downs_right = 'top-drop-downs';
}
?>
<div class="header-top-bar <?php echo esc_attr( $top_has_nav ).' '.esc_attr( $hide_top_bar_mobile );?>">
    <div class="<?php echo esc_attr($top_bar_width); ?>">
        <div class="top-bar-inner">
            <?php if( $top_bar_left != 'none' ) { ?>
            <div class="top-bar-left <?php echo esc_attr( $nav_left_class.' '.$top_bar_left_width ); ?>">
                <?php
                if( $top_bar_left == 'contact_info' ) {
                    get_template_part('template-parts/header/top-bar-contact');

                } elseif ( $top_bar_left == 'social_icons' ) {
                    get_template_part('template-parts/header/top-bar-social');

                } elseif ( $top_bar_left == 'slogan' ) {
                    get_template_part( 'template-parts/header/top-bar-slogan' );

                } elseif ( $top_bar_left == 'currency_switchers' ) {
                    get_template_part( 'template-parts/header/top-bar-currency' );
                }
                ?>
            </div>
            <?php } ?>

            <?php if( $top_bar_right != 'none' ) { ?>
            <div class="top-bar-right <?php echo esc_attr( $nav_right_class.' '.$top_bar_right_width ); ?>">
                <?php
                if( $top_bar_right == 'contact_info' ) {
                    get_template_part('template-parts/header/top-bar-contact');

                } elseif ( $top_bar_right == 'social_icons' ) {
                    get_template_part('template-parts/header/top-bar-social');

                } elseif ( $top_bar_right == 'slogan' ) {
                    get_template_part( 'template-parts/header/top-bar-slogan' );
                    
                } elseif ( $top_bar_right == 'currency_switchers' ) {
                    get_template_part( 'template-parts/header/top-bar-currency' );
                }
                ?>
            </div>
            <?php } ?>
        </div>
    </div>
</div>