<?php
/*
  Plugin Name: homey Visual Composer Extensions
  Plugin URI: http://themeforest.net/user/favethemes
  Description: Extensions to Visual Composer for the homey theme.
  Version: 1.0
  Author: Favethemes
  Author URI: http://themeforest.net/user/favethemes
  License: GPLv2 or later
 */

// don't load directly
if ( !defined( 'ABSPATH' ) )
    die( '-1' );


if (class_exists('Vc_Manager')) {

	$allowed_html_array = array(
		'a' => array(
			'href' => array(),
			'title' => array(),
			'target' => array()
		)
	);

	/*** Remove unused parameters ***/
	if (function_exists('vc_remove_param')) {
		
		vc_remove_param('vc_row', 'font_color');
	}

	$homey_grids_tax = array();	
	$homey_grids_tax['Listing Type'] = 'listing_type';
	$homey_grids_tax['Room Type'] = 'room_type';
	$homey_grids_tax['Listing Country'] = 'listing_country';
	$homey_grids_tax['Listing State'] = 'listing_state';
	$homey_grids_tax['Listing City'] = 'listing_city';
	$homey_grids_tax['Listing Area'] = 'listing_area';

	$fontawesomeIcons = array(
		"fa-adn"                => "fa fa-adn",
		"fa-android"            => "fa-Android",
		"fa-apple"              => "fa-Apple",
		"fa-behance"            => "fa-Behance",
		"fa-bitbucket"          => "fa-Bitbucket",
		"fa-bitbucket-sign"     => "fa-Bitbucket-Sign",
		"fa-bitcoin"            => "fa-Bitcoin",
		"fa-btc"                => "fa-BTC",
		"fa-css3"               => "fa-CSS3",
		"fa-codepen"            => "fa-Codepen",
		"fa-digg"            	=> "fa-Digg",
		"fa-drupal"            	=> "fa-Drupal",
		"fa-dribbble"           => "fa-Dribbble",
		"fa-dropbox"            => "fa-Dropbox",
		"fa-envelope"           => "fa-E-mail",
		"fa-facebook"           => "fa-Facebook",
		"fa-facebook-sign"      => "fa-Facebook-Sign",
		"fa-flickr"             => "fa-Flickr",
		"fa-foursquare"         => "fa-Foursquare",
		"fa-github"             => "fa-GitHub",
		"fa-github-alt"         => "fa-GitHub-Alt",
		"fa-github-sign"        => "fa-GitHub-Sign",
		"fa-gittip"             => "fa-Gittip",
		"fa-google"             => "fa-Google",
		"fa-google"        => "fa-Google Plus",
		"fa-google-sign"   => "fa-Google Plus-Sign",
		"fa-html5"              => "fa-HTML5",
		"fa-instagram"          => "fa-Instagram",
		"fa-linkedin"           => "fa-LinkedIn",
		"fa-linkedin-sign"      => "fa-LinkedIn-Sign",
		"fa-linux"              => "fa-Linux",
		"fa-maxcdn"             => "fa-MaxCDN",
		"fa-paypal"             => "fa-Paypal",
		"fa-pinterest"          => "fa-Pinterest",
		"fa-pinterest-sign"     => "fa-Pinterest-Sign",
		"fa-reddit"     		=> "fa-Reddit",
		"fa-renren"             => "fa-Renren",
		"fa-skype"              => "fa-Skype",
		"fa-stackexchange"      => "fa-StackExchange",
		"fa-soundcloud"      	=> "fa-Soundcloud",
		"fa-spotify"      		=> "fa-Spotify",
		"fa-trello"             => "fa-Trello",
		"fa-tumblr"             => "fa-Tumblr",
		"fa-tumblr-sign"        => "fa-Tumblr-Sign",
		"fa-twitter"            => "fa-Twitter",
		"fa-twitter-sign"       => "fa-Twitter-Sign",
		"fa-vimeo-square"       => "fa-Vimeo-Square",
		"fa-vk"                 => "fa-VK",
		"fa-weibo"              => "fa-Weibo",
		"fa-windows"            => "fa-Windows",
		"fa-xing"               => "fa-Xing",
		"fa-xing-sign"          => "Xing-Sign",
		"fa-yahoo"          	=> "Yahoo",
		"fa-youtube"            => "YouTube",
		"fa-youtube-play"       => "YouTube Play",
		"fa-youtube-sign"       => "YouTube-Sign"
	);

	$of_categories 			= array();
	$of_categories_obj 		= get_categories( array( 'hide_empty' => 1, 'hierarchical' => true ) );

	foreach ( $of_categories_obj as $of_category ) {
	    $of_categories[$of_category->name] = $of_category->term_id; 
	}
	$categories_buffer['- All categories -'] = '';

	$of_categories = array_merge(
            $categories_buffer,
            $of_categories
        );

	$of_tags 			= array();
	$of_tags_obj 		= get_tags( array( 'hide_empty' => 1 ) );

	foreach ( $of_tags_obj as $of_tag ) {
	    $of_tags[$of_tag->name] = $of_tag->term_id; 
	}

	$sort_by = array( 
		esc_html__('Default', 'homey') => '', 
		esc_html__('Price (Low to High)', 'homey') => 'a_price', 
		esc_html__('Price (High to Low)', 'homey') => 'd_price',
		esc_html__('Date (Old to New)', 'homey') => 'a_date',
		esc_html__('Date (New to Old)', 'homey') => 'd_date',
		esc_html__('Featured on Top', 'homey') => 'featured_top',
		esc_html__('Random', 'homey') => 'random',
	);

	/*---------------------------------------------------------------------------------
		Section Title
	-----------------------------------------------------------------------------------*/
	vc_map( array(
		"name"	=>	esc_html__( "Section Title", "homey" ),
		"base"				=> "homey-section-title",
		"description" 		=> esc_html__("Create seation titles between elements", 'homey'),
		'category'			=> "By Favethemes",
		"class"				=> "",
		'admin_enqueue_js'	=> "",
		'admin_enqueue_css'	=> "",
		"icon" 				=> "icon-section-title",
		"params"			=> array(
			array(
				"param_name" => "homey_section_title",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Title:", "homey" ),
				"description" => esc_html__( "Enter the section title", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "homey_section_subtitle",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Sub Title:", "homey" ),
				"description" => esc_html__( "Enter the section sub-title", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "homey_section_title_align",
				"type" => "dropdown",
				"value" => array( 'Center Aligned' => 'text-center', 'Left Aligned' => 'text-left', 'Right Aligned' => 'text-right' ),
				"heading" => esc_html__("Alignement:", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "homey_section_title_color",
				"type" => "dropdown",
				"value" => array( 'Default' => '', 'Light' => 'homey-section-title-light', 'Dark' => 'homey-section-title-dark' ),
				"heading" => esc_html__("Color Scheme", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "fontsize_title",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Font Size for Title:", "homey" ),
				"description" => esc_html__( "Enter the font size for section title", "homey" ),
				"group" => 'Style',
				"save_always" => true
			),
			array(
				"param_name" => "lineheight_title",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Line Height for Title:", "homey" ),
				"description" => esc_html__( "Enter the line height for section title", "homey" ),
				"group" => 'Style',
				"save_always" => true
			),

			array(
				"param_name" => "fontsize_subtitle",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Font Size for Sub Title:", "homey" ),
				"description" => esc_html__( "Enter the font size for section sub title", "homey" ),
				"group" => 'Style',
				"save_always" => true
			),
			array(
				"param_name" => "lineheight_subtitle",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Line Height for Sub Title:", "homey" ),
				"description" => esc_html__( "Enter the line height for section sub title", "homey" ),
				"group" => 'Style',
				"save_always" => true
			),
		) // end params
	) );

	/*---------------------------------------------------------------------------------
		Register 
	-----------------------------------------------------------------------------------*/
	vc_map( array(
		"name"	=>	esc_html__( "Register", "homey" ),
		"description"		=> '',
		"base"				=> "homey-register",
		'category'			=> "By Favethemes",
		"class"				=> "",
		'admin_enqueue_js'	=> "",
		'admin_enqueue_css'	=> "",
		"icon" 				=> "icon-register-section",
		"params"			=> array(
			array(
				"param_name" => "register_title",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Title:", "homey" ),
				"description" => '',
				"save_always" => true
			)
		) // end params
	) );

	/*---------------------------------------------------------------------------------
		Space
	-----------------------------------------------------------------------------------*/
	vc_map( array(
		"name" => __("Empty Space", "homey"),
		"icon" => "icon-wpb-ui-empty_space",
		"base" => "homey-space",
		"description" => "Add space between elements. It can be also used for clear floating",
		"class" => "space_extended",
		"category" => __("By Favethemes", "homey"),
		"params" => array(
			array(
				"type" => "textfield",
				"admin_label" => true,
				"heading" => __("Height of the space (px)", "homey"),
				"param_name" => "height",
				"value" => 50,
				"description" => __("Set height of the space. You can add white space between elements to separate them beautifully.", "homey")
			)
		)
	) );

	/*---------------------------------------------------------------------------------
	 Listings
	-----------------------------------------------------------------------------------*/
	vc_map(array(
		"name" => esc_html__("Listings", "homey"),
		"description" => '',
		"base" => "homey-listings",
		"description" => "Display listings in your page content",
		'category' => "By Favethemes",
		"class" => "",
		'admin_enqueue_js' => "",
		'admin_enqueue_css' => "",
		"icon" => "icon-listings",
		"params" => array(
			array(
				"param_name" => "listing_style",
				"type" => "dropdown",
				"value" => array('Listing View' => 'list', 'Grid View' => 'grid', 'Card View' => 'card'),
				"heading" => esc_html__("Listing Style", "homey"),
				"description" => esc_html__("Choose grid/list/card style, default will be list view", "homey"),
				'std'         => 'list',
				"save_always" => true
			),

			array(
				"param_name" => "booking_type",
				"type" => "dropdown",
				"value" => array(
					esc_html__('All/Any', 'homey') => '', 
					esc_html__('Per Day', 'homey') => 'per_day_date', 
					esc_html__('Per Night', 'homey') => 'per_day', 
					esc_html__('Per Week', 'homey') => 'per_week',
					esc_html__('Per Month', 'homey') => 'per_month',
					esc_html__('Per Hour', 'homey') => 'per_hour',
				),
				"heading" => esc_html__("Booking Type", "homey"),
				"description" => '',
				'std'         => '',
				"save_always" => true
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__('Listing type filter:', 'homey'),
				'taxonomy'      => 'listing_type',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_type',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Room type filter:", "homey"),
				'taxonomy'      => 'room_type',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'room_type',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing country filter:", "homey"),
				'taxonomy'      => 'listing_country',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_country',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing state filter:", "homey"),
				'taxonomy'      => 'listing_state',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_state',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing city filter:", "homey"),
				'taxonomy'      => 'listing_city',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_city',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing area filter:", "homey"),
				'taxonomy'      => 'listing_area',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_area',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				"param_name" => "featured_listing",
				"type" => "dropdown",
				"value" => array(esc_html__('- Any -', 'homey') => '', esc_html__('Without Featured', 'homey') => 'no', esc_html__('Only Featured', 'homey') => 'yes'),
				"heading" => esc_html__("Featured Listings:", "homey"),
				"description" => esc_html__("You can make a post featured by clicking the featured listings checkbox while add/edit post", "homey"),
				"group" => 'Filters',
				"save_always" => true
			),

			array(
				"param_name" => "posts_limit",
				"type" => "textfield",
				"value" => "6",
				"heading" => esc_html__("Limit listing number:", "homey"),
				"description" => "",
				"save_always" => true,
			),
			array(
				"param_name" => "sort_by",
				"type" => "dropdown",
				"heading" => esc_html__("Sort By", "homey"),
				"value" => array( 
					esc_html__('Default', 'homey') => '', 
					esc_html__('Price (Low to High)', 'homey') => 'a_price', 
					esc_html__('Price (High to Low)', 'homey') => 'd_price',
					esc_html__('Date (Old to New)', 'homey') => 'a_date',
					esc_html__('Date (New to Old)', 'homey') => 'd_date',
					esc_html__('Featured on top', 'homey') => 'featured_top'
				),
				"description" => '',
				"save_always" => true
			),
			array(
				"param_name" => "offset",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("Offset listings:", "homey"),
				"description" => "",
				"save_always" => true

			),
			array(
				"param_name" => "loadmore",
				"type" => "dropdown",
				"heading" => esc_html__("Load More", "homey"),
				"value" => array( 
					esc_html__('Enable', 'homey') => 'enable', 
					esc_html__('Disable', 'homey') => 'disable',
				),
				"description" => esc_html__('Show load more pagination', 'homey'),
				"save_always" => true
			),

		) // End params
	));

	
	/*---------------------------------------------------------------------------------
	 Listings Carousel
	-----------------------------------------------------------------------------------*/
	vc_map(array(
		"name" => esc_html__("Listings Carousel", "homey"),
		"base" => "homey-listing-carousel",
		"description" => "Display listings in a nice carousel",
		'category' => "By Favethemes",
		"class" => "",
		'admin_enqueue_js' => "",
		'admin_enqueue_css' => "",
		"icon" => "icon-listing-carousel",
		"params" => array(


			array(
				"param_name" => "listing_style",
				"type" => "dropdown",
				"value" => array('Grid View' => 'grid', 'Card View' => 'card'),
				"heading" => esc_html__("Listing style", "homey"),
				"description" => esc_html__("Select grid/card style, the default style will be list view", "homey"),
				'std'         => 'grid',
				"save_always" => true
			),

			array(
				"param_name" => "booking_type",
				"type" => "dropdown",
				"value" => array(
					esc_html__('All/Any', 'homey') => '', 
					esc_html__('Per Day', 'homey') => 'per_day_date', 
					esc_html__('Per Night', 'homey') => 'per_day', 
					esc_html__('Per Week', 'homey') => 'per_week',
					esc_html__('Per Month', 'homey') => 'per_month',
					esc_html__('Per Hour', 'homey') => 'per_hour',
				),
				"heading" => esc_html__("Booking Type", "homey"),
				"description" => '',
				'std'         => '',
				"save_always" => true
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__('Listing type filter:', 'homey'),
				'taxonomy'      => 'listing_type',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_type',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Room type filter:", "homey"),
				'taxonomy'      => 'room_type',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'room_type',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing country filter:", "homey"),
				'taxonomy'      => 'listing_country',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_country',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing state filter:", "homey"),
				'taxonomy'      => 'listing_state',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_state',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing city filter:", "homey"),
				'taxonomy'      => 'listing_city',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_city',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing area filter:", "homey"),
				'taxonomy'      => 'listing_area',
				'is_multiple'   => true,
				'is_hide_empty'   => true,
				'description'   => '',
				'param_name'    => 'listing_area',
				'save_always'   => true,
				'std'           => '',
				"group" => 'Filters',
			),

			array(
				"param_name" => "featured_listing",
				"type" => "dropdown",
				"value" => array(esc_html__('- Any -', 'homey') => '', esc_html__('Without Featured', 'homey') => 'no', esc_html__('Only Featured', 'homey') => 'yes'),
				"heading" => esc_html__("Featured listings:", "homey"),
				"description" => esc_html__("You can make a post featured by clicking the featured listings checkbox while add/edit post", "homey"),
				"group" => 'Filters',
				"save_always" => true
			),

			array(
				"param_name" => "listing_ids",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("Listing IDs:", "homey"),
				"description" => esc_html__("Enter the listings ids comma separated. Example 12,305,34", "homey"),
				"group" => 'Filters',
				"save_always" => true
			),

			array(
				"param_name" => "sort_by",
				"type" => "dropdown",
				"heading" => esc_html__("Sort By", "homey"),
				"value" => $sort_by,
				"description" => '',
				"save_always" => true
			),

			array(
				"param_name" => "posts_limit",
				"type" => "textfield",
				"value" => "9",
				"heading" => esc_html__("Limit listings number:", "homey"),
				"description" => "",
				"save_always" => true,
			),
			array(
				"param_name" => "offset",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("Offset listings:", "homey"),
				"description" => "",
				"save_always" => true

			),
			array(
				"param_name" => "slides_to_show",
				"type" => "dropdown",
				"value" => array('3 Columns' => '3cols', '4 Columns' => '4cols'),
				"heading" => esc_html__("Slides to show:", "homey"),
				"description" => "",
				"std" => "3cols",
				"save_always" => true,
				"group" => 'Carousel Settings'
			),
			array(
				"param_name" => "slides_to_scroll",
				"type" => "dropdown",
				"value" => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				"heading" => esc_html__("Slides to scroll:", "homey"),
				"description" => "",
				"save_always" => true,
				"group" => 'Carousel Settings'
			),
			array(
				"param_name" => "slide_infinite",
				"type" => "dropdown",
				"value" => array(
					'Yes' => 'true',
					'No' => 'false'
				),
				"heading" => esc_html__("Infinite scroll:", "homey"),
				"description" => "",
				"save_always" => true,
				"group" => 'Carousel Settings'
			),
			array(
				"param_name" => "slide_auto",
				"type" => "dropdown",
				"value" => array(
					'No' => 'false',
					'Yes' => 'true'
				),
				"heading" => esc_html__("Autoplay:", "homey"),
				"description" => "",
				"save_always" => true,
				"group" => 'Carousel Settings'
			),
			array(
				"param_name" => "auto_speed",
				"type" => "textfield",
				"value" => '3000',
				"heading" => esc_html__("Autoplay speed:", "homey"),
				"description" => "Set the autoplay speed in milliseconds. Default is 3000",
				"save_always" => true,
				"group" => 'Carousel Settings'
			),
			array(
				"param_name" => "navigation",
				"type" => "dropdown",
				"value" => array(
					'Yes' => 'true',
					'No' => 'false'
				),
				"heading" => esc_html__("Next/Prev navigation:", "homey"),
				"description" => "",
				"save_always" => true,
				"group" => 'Carousel Settings'
			),
			array(
				"param_name" => "slide_dots",
				"type" => "dropdown",
				"value" => array(
					'Yes' => 'true',
					'No' => 'false'
				),
				"heading" => esc_html__("Dots navigation:", "homey"),
				"description" => "",
				"save_always" => true,
				"group" => 'Carousel Settings'
			)



		) // End params
	));


	/*---------------------------------------------------------------------------------
	 Listing By ID
	-----------------------------------------------------------------------------------*/
	vc_map(array(
		"name" => esc_html__("Listing by ID", "homey"),
		"description" => esc_html__('Show single listing by id', "homey"),
		"base" => "homey-listing-by-id",
		'category' => "By Favethemes",
		"class" => "",
		'admin_enqueue_js' => "",
		'admin_enqueue_css' => "",
		"icon" => "icon-listing-by-id",
		"params" => array(
			array(
				"param_name" => "listing_style",
				"type" => "dropdown",
				"value" => array('Grid View' => 'grid', 'Card View' => 'card'),
				"heading" => esc_html__("Listing style", "homey"),
				"description" => esc_html__("Select grid/card style, the default style will be list view", "homey"),
				'std'         => 'grid',
				"save_always" => true
			),
			array(
				"param_name" => "listing_id",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("Listing ID:", "homey"),
				"description" => esc_html__("Enter the listing ID. Example 305", "homey"),
				"save_always" => true
			)

		) // End params
	));

	/*---------------------------------------------------------------------------------
	 Listing By IDs
	-----------------------------------------------------------------------------------*/
	vc_map(array(
		"name" => esc_html__("Listings by IDs", "homey"),
		"description" => esc_html__("Show listings by IDs", "homey"),
		"base" => "homey-listing-by-ids",
		'category' => "By Favethemes",
		"class" => "",
		'admin_enqueue_js' => "",
		'admin_enqueue_css' => "",
		"icon" => "icon-listing-by-ids",
		"params" => array(
			array(
				"param_name" => "listing_style",
				"type" => "dropdown",
				"value" => array('Grid View' => 'grid', 'Card View' => 'card'),
				"heading" => esc_html__("Listing Style", "homey"),
				"description" => esc_html__("Select grid/card style, the default style will be list view", "homey"),
				'std'         => 'grid',
				"save_always" => true
			),
			array(
				"param_name" => "columns",
				"type" => "dropdown",
				"value" => array('2 Columns' => '2cols', '3 Columns' => '3cols'),
				"heading" => esc_html__("Columns in Row:", "homey"),
				"description" => "",
				"std" => "3cols",
				"save_always" => true,
			),
			array(
				"param_name" => "listing_ids",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("Listing IDs:", "homey"),
				"description" => esc_html__("Enter Listings ids comma separated. Ex 12,305,34", "homey"),
				"save_always" => true
			)

		) // End params
	));


	/*---------------------------------------------------------------------------------
	Homey Grid
	-----------------------------------------------------------------------------------*/
	vc_map( array(
		"name"	=>	esc_html__( "Homey Grids", "homey" ),
		"description"			=> 'Show Listing Types, Room Types, Countries, Cities, States, Area into a grid',
		"base"					=> "homey-grids",
		'category'				=> "By Favethemes",
		"class"					=> "",
		'admin_enqueue_js'		=> "",
		'admin_enqueue_css'		=> "",
		"icon" 					=> "icon-homey-grid",
		"params"				=> array(

			array(
				"param_name" => "homey_grid_type",
				"type" => "dropdown",
				"value" => array( 'Grid v1' => 'grid_v1', 'Grid v2' => 'grid_v2', 'Grid v3' => 'grid_v3', 'Grid v4' => 'grid_v4' ),
				"heading" => esc_html__("Choose grid:", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "homey_grid_from",
				"type" => "dropdown",
				"value" => $homey_grids_tax,
				"heading" => esc_html__("Choose Taxonomy", "homey" ),
				"save_always" => true
			),
			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing Types", "homey"),
				'taxonomy'      => 'listing_type',
				'is_multiple'   => true,
				'is_hide_empty'   => false,
				'description'   => '',
				'param_name'    => 'listing_type',
				"dependency" => Array("element" => "homey_grid_from", "value" => array("listing_type")),
				'save_always'   => true,
				'std'           => '',
			),
			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Room Type", "homey"),
				'taxonomy'      => 'room_type',
				'is_multiple'   => true,
				'is_hide_empty'   => false,
				'description'   => '',
				'param_name'    => 'room_type',
				"dependency" => Array("element" => "homey_grid_from", "value" => array("room_type")),
				'save_always'   => true,
				'std'           => '',
			),
			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing Country", "homey"),
				'taxonomy'      => 'listing_country',
				'is_multiple'   => true,
				'is_hide_empty'   => false,
				'description'   => '',
				'param_name'    => 'listing_country',
				"dependency" => Array("element" => "homey_grid_from", "value" => array("listing_country")),
				'save_always'   => true,
				'std'           => '',
			),
			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing States", "homey"),
				'taxonomy'      => 'listing_state',
				'is_multiple'   => true,
				'is_hide_empty'   => false,
				'description'   => '',
				'param_name'    => 'listing_state',
				"dependency" => Array("element" => "homey_grid_from", "value" => array("listing_state")),
				'save_always'   => true,
				'std'           => '',
			),
			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing Cities", "homey"),
				'taxonomy'      => 'listing_city',
				'is_multiple'   => true,
				'is_hide_empty'   => false,
				'description'   => '',
				'param_name'    => 'listing_city',
				"dependency" => Array("element" => "homey_grid_from", "value" => array("listing_city")),
				'save_always'   => true,
				'std'           => '',
			),

			array(
				'type'          => 'homey_get_taxonomy_list',
				'heading'       => esc_html__("Listing Area", "homey"),
				'taxonomy'      => 'listing_area',
				'is_multiple'   => true,
				'is_hide_empty'   => false,
				'description'   => '',
				'param_name'    => 'listing_area',
				"dependency" => Array("element" => "homey_grid_from", "value" => array("listing_area")),
				'save_always'   => true,
				'std'           => '',
			),

			array(
				"param_name" => "homey_show_child",
				"type" => "dropdown",
				"value" => array( 'No' => '0', 'Yes' => '1' ),
				"heading" => esc_html__("Show Child:", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "orderby",
				"type" => "dropdown",
				"value" => array( 'Name' => 'name', 'Count' => 'count', 'ID' => 'id' ),
				"heading" => esc_html__("Order By:", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "order",
				"type" => "dropdown",
				"value" => array( 'ASC' => 'ASC', 'DESC' => 'DESC' ),
				"heading" => esc_html__("Order:", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "homey_hide_empty",
				"type" => "dropdown",
				"value" => array( 'Yes' => '1', 'No' => '0' ),
				"heading" => esc_html__("Hide Empty:", "homey" ),
				"save_always" => true
			),
			array(
				"param_name" => "no_of_terms",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Number of Items to Show:", "homey" ),
				"save_always" => true
			)

		) // end params
	) );

	
	/*---------------------------------------------------------------------------------
	Blog Posts Grids
	-----------------------------------------------------------------------------------*/
	vc_map( array(
		"name"					=> esc_html__( "Blog Posts Grid", "homey" ),
		"description"			=> 'Display your blog posts in a grid',
		"base"					=> "homey-blog-posts",
		'category'				=> "By Favethemes",
		"class"					=> "",
		'admin_enqueue_js'		=> "",
		'admin_enqueue_css'		=> "",
		"icon" 					=> "icon-blog-posts",
		"params"				=> array(
			array(
				"param_name" => "category_id",
				"type" => "dropdown",
				"value" => homey_get_category_id_array(),
				"heading" => esc_html__("Category Filter:", "homey" ),
				"description" => "",
				"save_always" => true
			),
			array(
				"param_name" => "offset",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Offset", "homey" ),
				"description" => "",
				"save_always" => true
			),
			array(
				"param_name" => "posts_limit",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Number of posts to show", "homey" ),
				"description" => "",
				'std'         => '6',
				"save_always" => true
			)
		) // End params
	) );

	/*---------------------------------------------------------------------------------
	Blog Posts Carousels
	-----------------------------------------------------------------------------------*/
	vc_map( array(
		"name"					=> esc_html__( "Blog Posts Carousel", "homey" ),
		"description"			=> 'Display your blog posts in a nice carousel',
		"base"					=> "homey-blog-posts-carousel",
		'category'				=> "By Favethemes",
		"class"					=> "",
		'admin_enqueue_js'		=> "",
		'admin_enqueue_css'		=> "",
		"icon" 					=> "icon-blog-posts-carousel",
		"params"				=> array(
			array(
				"param_name" => "category_id",
				"type" => "dropdown",
				"value" => homey_get_category_id_array(),
				"heading" => esc_html__("Category Filter:", "homey" ),
				"description" => "",
				"save_always" => true
			),
			array(
				"param_name" => "offset",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Offset", "homey" ),
				"description" => "",
				"save_always" => true
			),
			array(
				"param_name" => "posts_limit",
				"type" => "textfield",
				"value" => '',
				"heading" => esc_html__("Number of posts to show", "homey" ),
				"description" => "",
				'std'         => '9',
				"save_always" => true
			)
		) // End params
	) );

	/*---------------------------------------------------------------------------------
	 Partners
	-----------------------------------------------------------------------------------*/

	vc_map(array(
		"name" => esc_html__("Partners", "homey"),
		"description" => 'Display your partners logo in a nice carousel',
		"base" => "homey-partners",
		'category' => "By Favethemes",
		"class" => "",
		'admin_enqueue_js' => "",
		'admin_enqueue_css' => "",
		"icon" => "icon-prop-partners",
		"params" => array(
			array(
				"param_name" => "posts_limit",
				"type" => "textfield",
				"value" => "8",
				"heading" => esc_html__("Limit:", "homey"),
				"description" => "",
				"save_always" => true,
			),
			array(
				"param_name" => "offset",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("Offset Posts:", "homey"),
				"description" => "",
				"save_always" => true

			),
			array(
				"param_name" => "orderby",
				"type" => "dropdown",
				"value" => array('None' => 'none', 'ID' => 'ID', 'title' => 'title', 'Date' => 'date', 'Random' => 'rand', 'Menu Order' => 'menu_order' ),
				"heading" => esc_html__("Order By:", "homey"),
				"description" => '',
				"save_always" => true,
			),
			array(
				"param_name" => "order",
				"type" => "dropdown",
				"value" => array('ASC' => 'ASC', 'DESC' => 'DESC' ),
				"heading" => esc_html__("Order:", "homey"),
				"description" => '',
				"save_always" => true,
			),

		) // End params
	));

	/*---------------------------------------------------------------------------------
	 Testimonials
	-----------------------------------------------------------------------------------*/

	vc_map(array(
		"name" => esc_html__("Promo Box", "homey"),
		"description" => 'Show promo box with image and content',
		"base" => "homey-promobox",
		'category' => "By Favethemes",
		"class" => "",
		'admin_enqueue_js' => "",
		'admin_enqueue_css' => "",
		"icon" => "icon-prop-promobox",
		"params" => array(

			array(
				"param_name" => "promo_image",
				"type" => "attach_image",
				"value" => '',
				"heading" => esc_html__("Image:", "homey"),
				"description" => '',
				"save_always" => true,
			),
			array(
				"param_name" => "promo_title",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("Title:", "homey"),
				"description" => "",
				"save_always" => true,
			),
			array(
				"param_name" => "content",
				"type" => "textarea_html",
				"heading" => esc_html__("Content:", "homey"),
				"description" => "",
				"save_always" => true
			),
			array(
				"param_name" => "promo_link",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("URL:", "homey"),
				"description" => "",
				"save_always" => true,
			),
			array(
				"param_name" => "promo_link_text",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("URL Text:", "homey"),
				"description" => "",
				"save_always" => true,
			),


		) // End params
	));

	/*---------------------------------------------------------------------------------
	 Testimonials
	-----------------------------------------------------------------------------------*/

	vc_map(array(
		"name" => esc_html__("Testimonials", "homey"),
		"description" => 'Show testimonials into a grid or carousel',
		"base" => "homey-testimonials",
		'category' => "By Favethemes",
		"class" => "",
		'admin_enqueue_js' => "",
		'admin_enqueue_css' => "",
		"icon" => "icon-prop-testimonials",
		"params" => array(

			array(
				"param_name" => "testimonials_type",
				"type" => "dropdown",
				"value" => array('Grid' => 'grid', 'Slides' => 'slides'),
				"heading" => esc_html__("Testimonials Type:", "homey"),
				"description" => '',
				"save_always" => true,
			),
			array(
				"param_name" => "testi_cols",
				"type" => "dropdown",
				"value" => array('Three Columns' => 'col-sm-4', 'Four Columns' => 'col-sm-6 col-md-3'),
				"heading" => esc_html__("Columns:", "homey"),
				"description" => '',
				"dependency" => Array("element" => "testimonials_type", "value" => array("grid")),
				"save_always" => true,
			),
			array(
				"param_name" => "posts_limit",
				"type" => "textfield",
				"value" => "6",
				"heading" => esc_html__("Limit:", "homey"),
				"description" => "",
				"save_always" => true,
			),
			array(
				"param_name" => "offset",
				"type" => "textfield",
				"value" => "",
				"heading" => esc_html__("Offset Posts:", "homey"),
				"description" => "",
				"save_always" => true

			),
			array(
				"param_name" => "orderby",
				"type" => "dropdown",
				"value" => array('None' => 'none', 'ID' => 'ID', 'title' => 'title', 'Date' => 'date', 'Random' => 'rand', 'Menu Order' => 'menu_order' ),
				"heading" => esc_html__("Order By:", "homey"),
				"description" => '',
				"save_always" => true,
			),
			array(
				"param_name" => "order",
				"type" => "dropdown",
				"value" => array('ASC' => 'ASC', 'DESC' => 'DESC' ),
				"heading" => esc_html__("Order:", "homey"),
				"description" => '',
				"save_always" => true,
			),

		) // End params
	));

	/*---------------------------------------------------------------------------------
	Text with icons
	-----------------------------------------------------------------------------------*/

	class WPBakeryShortCode_Text_With_Icons  extends WPBakeryShortCodesContainer {}

	vc_map( array(
		"name" => "Text With Icons",
		"base" => "text_with_icons",
		"as_parent" => array('only' => 'text_with_icon'),
		"content_element" => true,
		"description" => 'Display a nice title and description box with icon',
		"category" => 'By Favethemes',
		"icon" => "icon-text_with_icon",
		"show_settings_on_create" => true,
		"params" => array(

			array(
				"type" => "dropdown",
				"holder" => "div",
				"class" => "",
				"heading" => "Style",
				"param_name" => "style",
				"value" => array(
					"Style One"     => "style_one",
					"Style Two"      => "style_two"
				),
				"description" => "",
				"save_always" => true
			),
			array(
				"type" => "dropdown",
				"holder" => "div",
				"class" => "",
				"heading" => "Columns:",
				"param_name" => "columns",
				"value" => array(
					"Three"     => "three_columns",
					"Four"      => "four_columns"
				),
				"description" => "",
				"save_always" => true
			)
		),
		"js_view" => 'VcColumnView'
	) );

	class WPBakeryShortCode_Text_With_Icon extends WPBakeryShortCode {}
	vc_map( array(
		"name" => "Text with icon",
		"base" => "text_with_icon",
		"icon" => "icon-text_with_icon",
		"content_element" => true,
		"as_child" => array('only' => 'text_with_icons'),
		"params" => array(
			array(
				"type" => "dropdown",
				"holder" => "div",
				"class" => "",
				"heading" => "Icon Type",
				"param_name" => "icon_type",
				"value" => array(
					"FontAwesome"   => "fontawesome_icon",
					"Custom Icon"   => "custom_icon"
				),
				"description" => "",
				"save_always" => true
			),
			array(
				"type" => "textfield",
				"class" => "",
				"heading" => "Icon",
				"param_name" => "font_awesome_icon",
				"value" => $fontawesomeIcons,
				"description" => wp_kses(__("Please set an icon. The entire list of icons can be found at <a href='http://fortawesome.github.io/Font-Awesome/icons/' target='_blank'>FontAwesome project page</a>. For example, if an icon is named 'fa-angle-right', the value you have to add inside the field is 'angle-right'.", "homey"), $allowed_html_array),
				"dependency" => Array('element' => "icon_type", 'value' => array('fontawesome_icon')),
				"save_always" => true
			),
			array(
				"type" => "attach_image",
				"class" => "",
				"heading" => "Icon",
				"param_name" => "custom_icon",
				"description" => "",
				"dependency" => Array('element' => "icon_type", 'value' => array('custom_icon')),
				"save_always" => true
			),
			array(
				"type" => "textfield",
				"holder" => "div",
				"class" => "",
				"heading" => "Title",
				"param_name" => "title",
				"description" => "",
				"save_always" => true
			),
			array(
				"type" => "textarea",
				"holder" => "div",
				"class" => "",
				"heading" => "Text",
				"param_name" => "text",
				"description" => "",
				"save_always" => true
			),
			array(
				"type" => "textfield",
				"holder" => "div",
				"class" => "",
				"heading" => "Read More Text",
				"param_name" => "read_more_text",
				"description" => "",
				"save_always" => true
			),
			array(
				"type" => "textfield",
				"holder" => "div",
				"class" => "",
				"heading" => "Read More Link",
				"param_name" => "read_more_link",
				"description" => "",
				"save_always" => true
			),
		)
	) );

	

	if ( !function_exists('homey_get_taxonomy_list') )
	{
	    function homey_get_vc_taxonomy_list($settings, $value)
	    {	
	    	$taxonomy    = isset($settings['taxonomy']) ? $settings['taxonomy'] : '';
	        $param_name  = isset($settings['param_name']) ? $settings['param_name'] : '';
	        $isHideEmpty = isset($settings['is_hide_empty']) && $settings['is_hide_empty']  ?  true : false;
	        $isMultiple  = isset($settings['is_multiple']) && $settings['is_multiple']  ?  'multiple' : '';
	        

	        if ( !is_array($value) )
	        {
	            $value = explode(',', $value);
	        }

	        $getTerms   = get_terms(
	           array(
	               'taxonomy'      => $taxonomy,
	               'hide_empty'    => $isHideEmpty
	           )
	        );

	        ob_start();
	        if ( !empty($getTerms) || !is_wp_error($getTerms) )
	        {
	            ?>
	            <select name="<?php echo esc_attr($param_name); ?>" class="wpb_vc_param_value <?php echo esc_attr($param_name); ?>" <?php echo esc_attr($isMultiple); ?>>
	            	<option value=""><?php esc_html_e('- All -', 'homey')?></option>
	                <?php
	                    foreach ( $getTerms as $getTerm ) :
	                        if ( in_array($getTerm->slug, $value) )
	                        {
	                            $selected = 'selected';
	                        }else{
	                            $selected = '';
	                        }
	                ?>
	                        <option <?php echo esc_attr($selected); ?> value="<?php echo esc_attr($getTerm->slug); ?>"><?php echo esc_html($getTerm->name); ?></option>
	                <?php
	                    endforeach;
	                ?>
	            </select>
	            <?php if ( !empty($isMultiple) ) : ?>
	            <button style="margin-top: 5px;" class="button button-primary" id="homey-toggle-select"><?php esc_html_e('Toggle Select', 'homey'); ?></button>
	            <?php endif; ?>
	            <?php
	        }else{
	            esc_html_e('There are no taxonomy found', 'homey');
	        }

	        $output = ob_get_clean();
	        return $output;
	    }

	    $homey_add_shcode_to_param = 'vc_add_';
	    $homey_add_shcode_to_param = $homey_add_shcode_to_param . 'shortcode_param';
	    $homey_add_shcode_to_param('homey_get_taxonomy_list', 'homey_get_vc_taxonomy_list');
	}


} // End Class_exists
?>
