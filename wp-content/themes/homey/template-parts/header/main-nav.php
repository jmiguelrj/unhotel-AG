<nav class="navi">
    <?php
    // Pages Menu
    if ( has_nav_menu( 'main-menu' ) ) :
        wp_nav_menu( array (
            'theme_location' => 'main-menu',
            'container' => '',
            'container_class' => '',
            'menu_class' => 'main-menu',
            'menu_id' => 'main-menu',
            'depth' => 4
        ));
    endif;
    ?>
</nav>