<?php

/**
 * The header for our theme
 *
 * @package Unhotel
 */
global $template;
$hero_bg = unhotel_get_main_image();
$heading = unhotel_get_hero_heading();
$subtitle = unhotel_get_hero_subtitle();
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<?php if ($template && basename($template) == "dashboard-reservations.php") { ?>
		<meta name="robots" content="noindex,nofollow">
	<?php } ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	<?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<div id="page" class="site">
		<header id="masthead" class="site-header" <?php if (is_front_page()) : ?>data-parallax="1.1" data-parallax-image="<?php echo esc_url($hero_bg); ?>" <?php endif; ?>>
			<?php if (is_front_page() && !empty($hero_bg)) : ?>
				<div class="header-parallax-inner" style="background-image: url(<?php echo esc_url($hero_bg); ?>);"></div>
			<?php endif; ?>
			<div class="container-fluid">
				<div class="row">
					<div class="col-7 col-md-7 col-lg-6 col-xl-3 left">
						<button id="menu-toggle" class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
						<span class="menu-icon"></span>
						<span class="menu-icon"></span>
						<span class="menu-icon"></span>
					</button>
						<div class="site-logo">
							<?php
							if (has_custom_logo()) {
								the_custom_logo();
							} else {
							?>
								<h1 class="site-title">
									<a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
										<?php bloginfo('name'); ?>
									</a>
								</h1>
								<?php
								// $unhotel_description = get_bloginfo( 'description', 'display' );
								if ($unhotel_description || is_customize_preview()) {
								?>
									<p class="site-description"><?php echo $unhotel_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																?></p>
							<?php
								}
							}
							?>
						</div>
					</div>
					<div class="col-5 col-md-5 col-lg-6 col-xl-9 right">
						<div id="right-inner-wrapper">
					
							<!-- Mobile Language Switcher -->
							<?php if (is_active_sidebar('sidebar-header-1') || function_exists('icl_get_languages')) : ?>
								<div class="header-widget-area header-widget-lang-switcher">
									<?php dynamic_sidebar('sidebar-header-1'); ?>
								</div>
							<?php endif; ?>
							<!-- Primary Navigation -->
							<nav id="site-navigation" class="main-navigation">
								<?php
								wp_nav_menu(
									array(
										'theme_location' => 'primary',
										'menu_id'        => 'primary-menu',
										'fallback_cb'    => false,
									)
								);
								?>

							</nav><!-- #site-navigation -->
							<div id="login-navigation" class="login-navigation <?php echo is_user_logged_in() ? 'logged-in' : 'logged-out'; ?>">
								<?php
								if (is_user_logged_in()) {
									$current_user = wp_get_current_user();
									//get first name and last name

									$user_name = trim($current_user->first_name . ' ' . $current_user->last_name);
									if (empty($user_name)) {
										$user_name = $current_user->user_login;
									}

									// Get profile photo from JetEngine meta field
									$profile_photo = get_user_meta($current_user->ID, 'profile_photo', true);

									// Check if profile photo exists and is not empty
									if (! empty($profile_photo)) {
										// If it's an attachment ID, get the URL
										if (is_numeric($profile_photo)) {
											$avatar_url = wp_get_attachment_url($profile_photo);
										} else {
											// If it's already a URL
											$avatar_url = $profile_photo;
										}
									} else {
										// Use default avatar if no profile photo
										$avatar_url = get_template_directory_uri() . '/assets/dist/images/avatar-fallback.png';
									}
								?>
									<div class="user-nav-wrap">
										<div class="user-nav-toggle">
											<span class="user-name"><?php echo esc_html($user_name); ?>
										
										</span>
											<div class="user-avatar">
												<img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($user_name); ?>">
											</div>
										</div>
										<nav id="user-nav" class="user-dropdown-menu">
											<?php
											wp_nav_menu(
												array(
													'theme_location' => 'login',
													'menu_id'        => 'user-menu',
													'fallback_cb'    => false,
													'container'      => false,
												)
											);
											?>
										</nav>
									</div>
								<?php
								} else {
									wp_nav_menu(
										array(
											'theme_location' => 'login',
											'menu_id'        => 'login-menu',
											'fallback_cb'    => false,
										)
									);
								}
								?>
							</div><!-- #login-navigation -->
						</div>

					</div><!-- .col-right -->

				</div><!-- .row -->
			</div><!-- .row -->
			<!-- Hero Section only on HOME -->
			<?php if (is_front_page()) : ?>
				<div class="col-12 hero-section-wrapper">
					<div id="unhotel-viksearch-form" class="wpb_wrapper">
						<div class="hero-content text-center">
							<h1 class="hero-heading"><?php echo $heading; ?></h1>
							<p class="hero-subtitle"><?php echo $subtitle; ?></p>
						</div>
						<!--serach VIK-->
						<?php if (is_active_sidebar('search-hero-homepage-1')) : ?>
							<div class="hero-section">
								<?php dynamic_sidebar('search-hero-homepage-1'); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
	</div><!-- .container -->
	</header><!-- #masthead -->
	<?php if (is_front_page()) : ?>
		<div class="uh-mobile-menu">
			<div class="uh-menu-secondary uh-general-menu">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'uh-mobile-menu'
					)
				);
				?>
			</div>
			<div class="uh-menu-language uh-general-menu">
				<?php echo do_shortcode('[wpml_language_switcher]') ?>
			</div>
			<div class="uh-menu-profile uh-general-menu">
				<?php if (is_user_logged_in()) {
					wp_nav_menu(
						array(
							'theme_location' => 'login',
							'menu_id'        => 'user-menu',
							'fallback_cb'    => false,
							'container'      => false,
						)
					);
				} ?>
			</div>
			<ul class="uh-menu-primary">
				<li>
					<a class="uh-menu-link">Menu</a>
				</li>
				<li>
					<a class="uh-language-link">Switch language</a>
				</li>
				<li>
					<a href="<?php echo get_site_url(); ?>" class="uh-logo-link">Unhotel</a>
				</li>
				<li>
					<a href="https://wa.me/5521975188181" target="_blank" class="uh-whatsapp-link">WhatsApp</a>
				</li>
				<li>
					<?php if (!is_user_logged_in()) { ?>
						<span class="<?php echo (get_locale() == 'pt_BR') ? 'open-auth-popup' : 'en-open-auth-popup'; ?>">	
					<?php } ?>
					<a class="uh-account-link <?php echo ((is_user_logged_in()) ? 'avatar' : ''); ?>">
						<?php
						if (is_user_logged_in()) {
							$current_user = wp_get_current_user();

							// Get profile photo from JetEngine meta field
							$profile_photo = get_user_meta($current_user->ID, 'profile_photo', true);

							// Check if profile photo exists and is not empty
							if (! empty($profile_photo)) {
								// If it's an attachment ID, get the URL
								if (is_numeric($profile_photo)) {
									$avatar_url = wp_get_attachment_url($profile_photo);
								} else {
									// If it's already a URL
									$avatar_url = $profile_photo;
								}
							} else {
								// Use default avatar if no profile photo
								$avatar_url = get_template_directory_uri() . '/assets/dist/images/avatar-fallback.png';
							}

							echo '<img src="' . esc_url($avatar_url) . '">';
						} else {
							echo 'Account';
						}
						?>
					</a>
					<?php if (!is_user_logged_in()) { ?>
					</span>
					<?php } ?>
				</li>
			</ul>
		</div>
	<?php endif; ?>