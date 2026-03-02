<?php
global $homey_local;
$nav_login = esc_html__(homey_option('nav_login'), 'homey');
$nav_register = esc_html__(homey_option('nav_register'), 'homey');
$become_host_btn = esc_html__(homey_option('become_host_btn'), 'homey');
$become_host_link = homey_option('become_host_link');
$become_host_label = esc_html__(homey_option('become_host_label'), 'homey');

$separater = '';

?>
<div class="account-login">

	<?php if($nav_login || $nav_register) { ?>
    <ul class="login-register list-inline">
    	<?php if($nav_login) { ?>
    		<li><a href="#" data-toggle="modal" data-target="#modal-login"><?php echo esc_attr($homey_local['login_text']); ?></a></li> 
    	<?php } ?>
    	
    	<?php 
        if($nav_login && $nav_register) {
            echo '<li><i class="fa fa-circle-o"></i></li> ';
        }
        ?>
    	
    	<?php if($nav_register) { ?>
    		<li><a href="#" data-toggle="modal" data-target="#modal-register"><?php echo esc_attr($homey_local['register_text']); ?></a></li>
    	<?php } ?>
    </ul>
    <?php } ?>
    
    <?php if($become_host_btn) { ?>
    	<a href="<?php echo get_permalink($become_host_link); ?>" class="btn btn-add-new-listing"><?php echo esc_attr($become_host_label); ?></a>
	<?php } ?>
</div>