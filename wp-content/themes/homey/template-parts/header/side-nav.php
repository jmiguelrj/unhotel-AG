<div id="side-nav-panel" class="side-nav-panel side-nav-panel-vertical side-nav-panel-left">
	<div class="mobile-nav-wrap">
		<nav id="mobile-nav" class="nav-dropdown main-nav-dropdown">
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
	</div>    
</div>