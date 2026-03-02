<?php
/**
 * Template Name: Splash Page Template
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

    <?php wp_head(); ?>
</head>


<body <?php body_class('splash-page splash-page-image'); ?>>
<?php
global $homey_local, $homey_prefix;
$homey_local = homey_get_localization();
$homey_prefix = 'homey_';
$search_template = homey_get_template_link('template/template-search.php');
$splash_type = homey_option('backgroud_type');
$splash_layout = homey_option('splash_layout');
$splash_search = homey_option('splash_search');
if( isset($_GET['splash_type'] ) ) {
    $splash_type = $_GET['splash_type'];
}
$splash_image = homey_option( 'splash_image', false, 'url' );
$mp4 = homey_option( 'splash_bg_mp4', false, 'url' );
$webm = homey_option( 'splash_bg_webm', false, 'url' );
$ogv = homey_option( 'splash_bg_ogv', false, 'url' );
$splash_video_image = homey_option('splash_video_image', false, 'url');

$header_type = homey_option('header_type');
?>    

<?php if($splash_type == 'image') { ?>
<div class="banner-inner parallax" data-parallax-bg-image="<?php echo esc_url($splash_image); ?>">
<?php } else { ?>
<div class="banner-inner">
<?php } ?>    

<?php 
if($header_type == "1" || $header_type == "2" || $header_type == "3") {
    $header_type = '1';
}
get_template_part('template-parts/header/header', $header_type); ?>


<?php if($splash_type == 'slider') { ?>
<script>
jQuery(document).ready(function($){
    $('.splash-slider').slick({
        lazyLoad: 'ondemand',
        adaptiveHeight: true,
        autoplay: true,
        infinite: true,
        speed: 300,
        slidesToShow: 1,
        arrows: false,
    });
});
</script>    
<div class="splash-slider">
    <?php
    $image_ids = homey_option( 'splash_slider' );
    $image_ids = explode(',', $image_ids );
    $images = '';
    foreach ( $image_ids as $id ) {
        $url = wp_get_attachment_image_src($id, array(2000, 1000));
        echo '<div class="splash-slider-item" style="background-image: url('.esc_url($url[0]).');"></div>';
    }
    ?>
</div><!-- background-slider -->
<?php } ?>

<?php if($splash_type == 'video') {

    $ogv = substr($ogv, 0, strrpos($ogv, "."));
    $mp4 = substr($mp4, 0, strrpos($mp4, "."));
    $webm = substr($webm, 0, strrpos($webm, "."));
    $splash_video_image = substr($splash_video_image, 0, strrpos($splash_video_image, "."));
 ?>

<div id="video-background" class="video-background splash-video-background" data-vide-bg="mp4: <?php echo esc_url($mp4); ?>, webm: <?php echo esc_url($webm); ?>, ogv: <?php echo esc_url($ogv); ?>, poster: <?php echo esc_url($splash_video_image); ?>" data-vide-options="position: 0% 50%">
        </div>
<?php } ?>        

<div class="banner-caption <?php homey_banner_search_class(); ?>">

    <?php 
    homey_banner_search_div_start(); 

    get_template_part('template-parts/banner/caption');

    if($splash_search != 0) {
        get_template_part ('template-parts/search/banner-'.homey_banner_search_style());  
    }

    homey_banner_search_div_end();
    ?>
    <?php get_footer(); ?>
</div><!-- banner-caption -->



</div><!-- splash-page-inner -->