<?php
global $homey_local;
$nav_login = esc_html__(homey_option('nav_login'), 'homey');
$nav_register = esc_html__(homey_option('nav_register'), 'homey');
$become_host_btn = esc_html__(homey_option('become_host_btn'), 'homey');
$become_host_link = homey_option('become_host_link');
$become_host_label = esc_html__(homey_option('become_host_label'), 'homey');

if($nav_login || $nav_register || $become_host_btn) { ?>
<nav id="user-nav" class="nav-dropdown main-nav-dropdown collapse navbar-collapse">
    <ul>

        <?php if($nav_login) { ?>
        <li>
            <a href="#" data-toggle="modal" data-target="#modal-login">
                <span data-toggle="collapse" data-target="#user-nav"><?php echo esc_attr($homey_local['login_text']); ?></span>
            </a>
        </li>
        <?php } ?>

        <?php if($nav_register) { ?>
        <li>
            <a href="#" data-toggle="modal" data-target="#modal-register">
                <span data-toggle="collapse" data-target="#user-nav"><?php echo esc_attr($homey_local['register_text']); ?></span>
            </a>
        </li>
        <?php } ?>

        <?php if($become_host_btn) { ?>
        <li><a href="<?php echo get_permalink($become_host_link); ?>"><?php echo esc_html($become_host_label); ?></a></li>
        <?php } ?>

        </ul>
</nav><!-- nav-collapse -->
<?php } ?>