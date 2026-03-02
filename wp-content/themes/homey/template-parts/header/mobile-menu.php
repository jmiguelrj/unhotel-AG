<nav id="mobile-nav" class="nav-dropdown main-nav-dropdown collapse navbar-collapse">
    <?php
    // Pages Menu
    if ( has_nav_menu( 'main-menu' ) ) :
        wp_nav_menu( array (
            'theme_location' => 'main-menu',
            'container' => '',
            'container_class' => '',
            'menu_class' => 'mobile-menu',
            'menu_id' => 'mobile-menu',
            'depth' => 4
        ));
    endif;
    ?>
</nav>