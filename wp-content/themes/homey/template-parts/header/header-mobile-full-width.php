<header class="header-nav header-mobile hidden-md hidden-lg no-cache-<?php echo strtotime("now"); ?>">
    <div class="header-mobile-wrap no-cache-<?php echo strtotime("now"); ?>">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-3">
                    <button type="button" class="btn btn-mobile-nav" data-toggle="collapse" data-target="#mobile-nav" aria-expanded="false">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button><!-- btn-mobile-nav -->
                </div>
                <div class="col-xs-6">
                    <div class="mobile-logo text-center">
                        <?php get_template_part ('inc/header/logo'); ?>
                    </div><!-- mobile-logo -->
                </div>
                <div class="col-xs-3">
                    <?php if(homey_is_login_register()) { ?>
                    <div class="user-menu text-right">
                        <button type="button" class="btn btn-mobile-nav" data-toggle="collapse" data-target="#user-nav" aria-expanded="false">
                            <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                        </button>
                    </div><!-- user-menu -->
                    <?php } ?>
                </div>
            </div><!-- row -->
        </div><!-- container -->
    </div><!-- header-mobile-wrap -->
    
    <div class="container-fluid no-cache-<?php echo strtotime("now"); ?>">
        <div class="row">
            <div class="mobile-nav-wrap">
                <?php get_template_part ('inc/header/mobile-menu'); ?>
            </div><!-- mobile-nav-wrap -->    
        </div>        
    </div><!-- container -->
    <div class="container-fluid">
        <div class="row">
            <div class="user-nav-wrap">
                <?php get_template_part ('inc/header/mobile-user-menu'); ?>
            </div><!-- mobile-nav-wrap -->
        </div>
    </div><!-- container -->
</header><!-- header-nav header-mobile hidden-md hidden-lg -->