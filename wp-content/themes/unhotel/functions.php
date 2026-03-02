<?php
/**
 * Unhotel Theme Functions
 *
 * @package Unhotel
 */


if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Suppress getimagesize warnings from VikBooking
 */
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
	// Suppress getimagesize warnings
	if (strpos($errstr, 'getimagesize') !== false && strpos($errstr, 'Failed to open stream') !== false) {
		return true;
	}
	// Let other errors be handled normally
	return false;
}, E_WARNING);

/**
 * Theme Setup
 */
function unhotel_setup()
{
	// Add theme support for title tag
	add_theme_support('title-tag');

	// Add theme support for post thumbnails
	add_theme_support('post-thumbnails');

	// Add theme support for automatic feed links
	add_theme_support('automatic-feed-links');

	// Add theme support for HTML5 markup
	add_theme_support('html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'script',
		'style',
	));

	// Add theme support for Gutenberg wide and full alignments
	add_theme_support('align-wide');

	// Add theme support for editor styles
	add_theme_support('editor-styles');

	// Add theme support for responsive embedded content
	add_theme_support('responsive-embeds');

	// Add theme support for custom logo
	add_theme_support('custom-logo', array(
		'height'      => 100,
		'width'       => 400,
		'flex-height' => true,
		'flex-width'  => true,
	));

	// Register navigation menus
	register_nav_menus(array(
		'primary' => esc_html__('Primary Menu', 'unhotel'),
		'uh-mobile-menu' => esc_html__('Mobile Menu', 'unhotel'), // Used for mobile navigation because the display is different
		'login' => __('Login/Register Menu', 'unhotel'), // Used for register/login links
		'footer'  => esc_html__('Footer Menu', 'unhotel'),
	));

	// Set content width
	$GLOBALS['content_width'] = 1200;
}
add_action('after_setup_theme', 'unhotel_setup');

/**
 * Include theme options
 */
require_once get_template_directory() . '/inc/theme-options.php';

/**
 * Include Gutenberg Editor customizations
 */
require_once get_template_directory() . '/inc/editor.php';

/**
 * Include Custom Sidebars Manager
 */
require_once get_template_directory() . '/inc/custom-sidebars.php';

/**
 * Include Gutenberg Blocks
 */
require_once get_template_directory() . '/inc/blocks.php';

/**
 * Include Layout Padding Functions
 */
require_once get_template_directory() . '/inc/layout-padding.php';


/**
 * Enqueue Scripts and Styles
 */
function unhotel_scripts()
{
	// Enqueue WordPress theme header (required for theme recognition)
	wp_enqueue_style('unhotel-style-header', get_stylesheet_uri(), array(), '1.0.0');

	// Enqueue compiled main stylesheet from Gulp
	wp_enqueue_style('unhotel-main-style', get_template_directory_uri() . '/assets/dist/css/style.min.css', array(), '1.0.0');
	// Enqueue carousel block CSS
	wp_enqueue_style('unhotel-carousel-style', get_template_directory_uri() . '/blocks/carousel/carousel.css', array(), '1.0.0');

	// Fix JetFormBuilder script dependencies
	// Ensure JetFBComponents loads before builder scripts
	if (wp_script_is('jet-form-builder-blocks-editor', 'registered')) {
		wp_script_add_data('jet-form-builder-blocks-editor', 'deps', array('wp-blocks', 'wp-element', 'wp-components'));
	}

	// Enqueue jQuery (WordPress includes this by default)
	wp_enqueue_script('jquery');

	// Enqueue matchHeight library
	wp_enqueue_script('jquery-matchheight', get_template_directory_uri() . '/js/jquery.matchHeight-min.js', array('jquery'), '0.7.2', true);

	// Enqueue Flickity library
	// Note: Flickity CSS is included in carousel block styles
	wp_enqueue_script('flickity', get_template_directory_uri() . '/js/flickity.pkgd.min.js', array('jquery'), '2.2.1', true);

	// Enqueue carousel frontend script
	wp_enqueue_script('unhotel-carousel-frontend', get_template_directory_uri() . '/assets/src/js/carousel-frontend.js', array('flickity'), '1.0.0', true);

	// Enqueue testimonials carousel script
	wp_enqueue_script('unhotel-testimonials-carousel', get_template_directory_uri() . '/assets/src/js/testimonials-carousel.js', array('jquery', 'flickity'), '1.0.0', true);

	// Enqueue compiled main JavaScript from Gulp
	wp_enqueue_script('unhotel-main-script', get_template_directory_uri() . '/assets/dist/js/main.min.js', array('jquery', 'jquery-matchheight', 'flickity'), '1.0.0', true);

	// Enqueue auth modal script
	// wp_enqueue_script( 'unhotel-auth-modal', get_template_directory_uri() . '/js/auth-modal.js', array( 'jquery' ), '1.0.0', true );

	// Enqueue Google Fonts
	wp_enqueue_style('unhotel-google-fonts', 'https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap', array(), null);
	// Enqueue comment reply script
	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
	// Old theme Include VikBooking custom CSS on the home page
	wp_enqueue_style('photoswipe', get_stylesheet_directory_uri() . '/css/photoswipe.css');
	if (is_front_page()) {
		wp_enqueue_style('home-custom-css', plugins_url() . '/vikbooking/site/resources/vikbooking_custom.css');
	}
}
add_action('wp_enqueue_scripts', 'unhotel_scripts');

function unhotel_register_wpml_strings()
{
	if (function_exists('icl_register_string')) {
		$hero_heading = get_theme_mod('unhotel_hero_heading');
		if (! empty($hero_heading)) {
			icl_register_string('unhotel', 'Hero Heading', $hero_heading);
		}

		$hero_subtitle = get_theme_mod('unhotel_hero_subtitle');
		if (! empty($hero_subtitle)) {
			icl_register_string('unhotel', 'Hero Subtitle', $hero_subtitle);
		}

		$footer_description = get_theme_mod('unhotel_footer_description');
		if (! empty($footer_description)) {
			icl_register_string('unhotel', 'Footer Description', $footer_description);
		}
	}
}
add_action('init', 'unhotel_register_wpml_strings');



/* Disable WordPress Admin Bar for all users */
add_filter('show_admin_bar', '__return_false');

//JM Change WordPress login logo
function custom_login_logo()
{
	echo '
    <style type="text/css">
        #login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/wp-content/themes/unhotel/assets/dist/images/logo-Unhotel-favicon.svg) !important;
            background-size: contain !important;
            width: 100% !important;
            height: 120px !important;
        }
    </style>';
}
add_action('login_enqueue_scripts', 'custom_login_logo');

/**
 * Dequeue Google Map script enqueued by the parent theme.
 */
function unhotel_dequeue_google_map_script()
{
	// Check if the script handle 'google-map' is enqueued.
	// The handle 'google-map' is used in your parent theme's wp_enqueue_script calls.
	if (wp_script_is('google-map', 'enqueued')) {
		wp_dequeue_script('google-map');
		wp_deregister_script('google-map');
	}
}
add_action('wp_enqueue_scripts', 'unhotel_dequeue_google_map_script', 20);

/**
 * Register Widget Areas
 */
function unhotel_widgets_init()
{
	register_sidebar(array(
		'name'          => esc_html__('Header', 'unhotel'),
		'id'            => 'sidebar-header-1',
		'description'   => esc_html__('Add widgets here.', 'unhotel'),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));
	register_sidebar(array(
		'name'          => esc_html__('Sidebar', 'unhotel'),
		'id'            => 'sidebar-1',
		'description'   => esc_html__('Add widgets here.', 'unhotel'),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));
	register_sidebar(array(
		'name'          => esc_html__('Search Hero Homepage', 'unhotel'),
		'id'            => 'search-hero-homepage-1',
		'description'   => esc_html__('Add widgets here.', 'unhotel'),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));

	register_sidebar(array(
		'name'          => esc_html__('Footer', 'unhotel'),
		'id'            => 'footer-1',
		'description'   => esc_html__('Add widgets here.', 'unhotel'),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));
}
add_action('widgets_init', 'unhotel_widgets_init');

/**
 * Force legacy widgets interface (fixes widget saving issues)
 */
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');

/**
 * Custom Excerpt Length
 */
function unhotel_excerpt_length($length)
{
	return 40;
}
add_filter('excerpt_length', 'unhotel_excerpt_length');

/**
 * Custom Excerpt More
 */
function unhotel_excerpt_more($more)
{
	return '...';
}
add_filter('excerpt_more', 'unhotel_excerpt_more');

/**
 * Add support for VIK Plugin compatibility
 * This ensures the theme doesn't interfere with VIK plugin functionality
 */
function unhotel_vik_plugin_support()
{
	// Remove any theme-specific styles that might conflict
	// VIK plugin will handle its own styling
}
add_action('wp_head', 'unhotel_vik_plugin_support', 1);

/**
 * Add body classes for better styling control
 */
function unhotel_body_classes($classes)
{
	// Add class if sidebar is active
	if (is_active_sidebar('sidebar-1')) {
		$classes[] = 'has-sidebar';
	}

	// Add custom page class if set
	if (is_page()) {
		$page_id = get_the_ID();
		$custom_class = get_post_meta($page_id, 'unhotel_custom_page_class', true);

		// Debug: Log to check if meta is retrieved (remove after testing)
		// error_log('Page ID: ' . $page_id . ' | Custom Class: ' . $custom_class);

		if (! empty($custom_class)) {
			// Sanitize the class name(s) - allows multiple classes separated by spaces
			$custom_classes = explode(' ', $custom_class);
			foreach ($custom_classes as $class) {
				$sanitized_class = sanitize_html_class(trim($class));
				if (! empty($sanitized_class)) {
					$classes[] = $sanitized_class;
				}
			}
		}

		// Add class if page title is hidden
		$hide_title = get_post_meta($page_id, 'unhotel_hide_title', true);
		// error_log('Page ID: ' . $page_id . ' | Hide Title: ' . ($hide_title ? 'true' : 'false'));
		if ($hide_title) {
			$classes[] = 'page-title-hidden';
		}
		
		// Add default-editor-style class if page doesn't have fullwidth unhotel-section
		global $post;
		if ($post) {
			$content = $post->post_content;
			if (strpos($content, 'unhotel-section') !== false && strpos($content, 'fullwidth') !== false) {
				// Has fullwidth section, no default style needed
			} else {
				$classes[] = 'default-editor-style';
			}
		}
	}

	return $classes;
}
add_filter('body_class', 'unhotel_body_classes');


/**
 * Prints HTML with meta information for the current author.
 */
function unhotel_posted_by()
{
	$byline = sprintf(
		/* translators: %s: post author. */
		esc_html_x('by %s', 'post author', 'unhotel'),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
	);

	echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Prints HTML with meta information for the current post-date/time.
 */
function unhotel_posted_on()
{
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if (get_the_time('U') !== get_the_modified_time('U')) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf(
		$time_string,
		esc_attr(get_the_date(DATE_W3C)),
		esc_html(get_the_date()),
		esc_attr(get_the_modified_date(DATE_W3C)),
		esc_html(get_the_modified_date())
	);

	$posted_on = sprintf(
		/* translators: %s: post date. */
		esc_html_x('Posted on %s', 'post date', 'unhotel'),
		'<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Displays an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views.
 */
function unhotel_post_thumbnail()
{
	if (post_password_required() || is_attachment() || ! has_post_thumbnail()) {
		return;
	}

	if (is_singular()) :
?>

		<div class="post-thumbnail">
			<?php the_post_thumbnail(); ?>
		</div><!-- .post-thumbnail -->

	<?php else : ?>

		<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php
			the_post_thumbnail(
				'post-thumbnail',
				array(
					'alt' => the_title_attribute(
						array(
							'echo' => false,
						)
					),
				)
			);
			?>
		</a>

<?php
	endif; // End is_singular().
}

/**
 * Copied from old projects
 * Use a regular expression to match and remove the first word starting with "RIO"
 */
function removeRIOFirstWord($inputString)
{
	$pattern = '/^(RIO\S*|[^ ]+)\s*(.*)/';
	if (preg_match($pattern, $inputString, $matches)) {
		return trim($matches[2]);
	} else {
		return $inputString;
	}
}

/**
 * Calculate profile completion percentage
 * Returns the percentage of completed profile fields added via user meta
 */

function my_profile_completion_percent()
{
	$user_id = get_current_user_id();
	if (! $user_id) {
		return '';
	}

	$required_meta = [
		'first_name',
		'last_name',
		'native_language',
		'other_language',
		'bio',
		'street_address',
		'apt_suite',
		'zip_code',
		'state',
		'city',
		'country',
		'zip_code',
		'neighborhood',
		'contact_name',
		'email',
		'phone',
		'relationship',
		'facebook_url',
		'twitter_url',
		'linkedin_url',
		'instagram_url',
		'trip_advisor_url',
		'google_plus_url',
		'pinterest_url',
		'youtube_url',
		'vimeo_url',
		'airbnb_url',
	];

	$total  = count($required_meta);
	$filled = 0;

	foreach ($required_meta as $key) {
		$val = get_user_meta($user_id, $key, true);
		if (! empty(trim((string) $val))) {
			$filled++;
		}
	}

	$percent = $total ? round(($filled / $total) * 100) : 0;

	// Return progress bar HTML for mobile and simple text for desktop
	$html = '<div class="profile-completion-wrapper">';

	// Desktop version - simple text
	$html .= '<span class="profile-completion">' . $percent . '%</span>';

	// Mobile version - progress bar
	$html .= '<div class="profile-completion-mobile" style="display: none;">';
	$html .= '<div class="profile-completion-bar" style="display: flex; width: 100%; height: 55px; overflow: hidden;">';

	if ($percent > 0) {
		$html .= '<div class="profile-completion-label" style="background-color: #54c4d9; color: white; padding: 20px 30px; display: flex; align-items: center; justify-content: center; font-weight: 600; width: ' . $percent . '%; min-width: 150px;">Progresso</div>';
	}

	$html .= '<div class="profile-completion-percent" style="background-color: #d8dce2; color: #fff; padding: 20px 30px; display: flex; align-items: center; justify-content: center; font-weight: 600; flex: 1;">' . $percent . '%</div>';
	$html .= '</div>';
	$html .= '</div>';

	// Add responsive CSS
	$html .= '<style>
		@media (max-width: 1024px) {
			.profile-completion-desktop { display: none !important; }
			.profile-completion-mobile { display: block !important; }
		}
	</style>';

	$html .= '</div>';

	return $html;
}

add_shortcode('profile_completion', function () {
	return my_profile_completion_percent();
});

// Logout redirect
add_action('template_redirect', function () {
	if (is_page('logout') && is_user_logged_in()) {
		wp_logout();
		wp_safe_redirect(home_url('/'));
		exit;
	}
});

/**
 * Hide menu items with 'logged-in-only' class when user is not logged in
 * Add the  class menu to any menu item that should only be visible to logged-in users
 */
add_filter('wp_nav_menu_objects', function ($items) {
	if (is_admin() || is_user_logged_in()) {
		return $items;
	}

	// User is not logged in - remove items with logged-in-only class
	foreach ($items as $key => $item) {
		if (in_array('logged-in-only', $item->classes)) {
			unset($items[$key]);
		}
	}

	return $items;
}, 10, 1);


//Remove avatar profile image
add_action('jet-form-builder/form-handler/after-send', function () {

	if (empty($_POST['delete_avatar']) || $_POST['delete_avatar'] !== '1') {
		return;
	}
	$user_id = get_current_user_id();
	if (! $user_id) {
		return;
	}
	delete_user_meta($user_id, 'profile_photo');
}, 10);