<?php
/**
 * Theme Stylesheet Options
 * Refer to Theme Options
 * @package homey
 * @since   homey 1.0
**/

function homey_custom_styling() {
  global $post;

  $pageID = $marker_type_color = '';
  if( !is_404() && !is_search() && !is_author() ) {
    $pageID = isset($post->ID) ? $post->ID : '';
  }

  $parallax_opacity = get_post_meta( $pageID, 'homey_header_opacity', true );
  if(empty($parallax_opacity)) {
    $parallax_opacity = '0.5';
  }

  $logo_desktop_dimensions = homey_option('logo_desktop_dimensions');
  $logo_mobile_dimensions = homey_option('logo_mobile_dimensions');

  $desktop_logo_width = isset($logo_desktop_dimensions['width']) ? $logo_desktop_dimensions['width'] : '';
  $desktop_logo_height = isset($logo_desktop_dimensions['height']) ? $logo_desktop_dimensions['height'] : '';

  if(empty($desktop_logo_width)) {
    $desktop_logo_width = '128px';
  }

  $mobile_logo_width = isset($logo_mobile_dimensions['width']) ? $logo_mobile_dimensions['width'] : '';
  $mobile_logo_height = isset($logo_mobile_dimensions['height']) ? $logo_mobile_dimensions['height'] : '';

  if(empty($mobile_logo_width)) {
    $mobile_logo_width = '128px';
  }

  if(empty($mobile_logo_height)) {
    $mobile_logo_height = '30px';
  }

  $splash_opacity = homey_option('splash_opacity');
  if(empty($splash_opacity)) {
    $splash_opacity = '0.5';
  }
  
  $trans_menu_color = homey_option('trans_menu_color');
  $trans_menu_color_hover = homey_option('trans_menu_color_hover');

  $parallax_height = get_post_meta( $pageID, 'homey_parallax_height', true );
  if( !empty($parallax_height) ) {
    $parallax_height = 'height: ' . ( preg_match( '/(px|em|\%|pt|cm)$/', $parallax_height ) ? $parallax_height : $parallax_height . 'px' ) . ';';
  } else {
    $parallax_height = 'height: 600px';
  }

  $parallax_height_mobile = get_post_meta( $pageID, 'homey_parallax_height_mobile', true );
  if( !empty($parallax_height_mobile) ) {
    $parallax_height_mobile = 'height: ' . ( preg_match( '/(px|em|\%|pt|cm)$/', $parallax_height_mobile ) ? $parallax_height_mobile : $parallax_height_mobile . 'px' ) . ';';
  } else {
    $parallax_height_mobile = 'height: 300px';
  }

  $typo_body = homey_option('typo-body');
  $typo_headings = homey_option('typo-headings');
  $typo_menu  = homey_option('typo-menu');

  $body_bg_color = homey_option('body_bg_color');
  $text_color = homey_option('text_color');

  $primary_color =  homey_option('primary_color');
  $primary_color_hover =  homey_option('primary_color_hover');

  $secondary_color =  homey_option('secondary_color');
  $secondary_color_hover =  homey_option('secondary_color_hover');


  $body_font_size = isset($typo_body['font-size']) ? $typo_body['font-size'] : '';

  /* body
  /* ----------------------------------------------------------- */
  $typography_body = "
  body, address, li, dt, dd, .pac-container  {
    font-size: {$body_font_size};
    line-height: {$typo_body['line-height']};
    font-weight: {$typo_body['font-weight']};
    text-transform: {$typo_body['text-transform']};
    letter-spacing: 0;
    font-family: {$typo_body['font-family']}, sans-serif;
  }
  .woocommerce ul.products li.product .button {
    font-family: {$typo_body['font-family']}, sans-serif;
    font-size: {$body_font_size}; 
  }
  ";

    /* main nav typography
    /* ----------------------------------------------------------- */
    $typography_nav = "
    .navi > .main-menu > li > a,
    .account-loggedin,
    .login-register a {
      font-size: {$typo_menu['font-size']};
      line-height: {$typo_menu['line-height']};
      font-weight: {$typo_menu['font-weight']};
      text-transform: {$typo_menu['text-transform']};
      font-family: {$typo_menu['font-family']}, sans-serif;
    }
    .menu-dropdown,
    .sub-menu li a,
    .navi .homey-megamenu-wrap > .sub-menu, 
    .listing-navi .homey-megamenu-wrap > .sub-menu,
    .account-dropdown ul > li a {
      font-size: {$typo_menu['font-size']};
      line-height: 1;
      font-weight: {$typo_menu['font-weight']};
      text-transform: {$typo_menu['text-transform']};
      font-family: {$typo_menu['font-family']}, sans-serif;
    }

    ";
    /* headings
    /* ----------------------------------------------------------- */
    $typography_heading = "
    h1, h2, h3, h4, h5, h6, .banner-title {
      font-family: {$typo_headings['font-family']}, sans-serif;
      font-weight: {$typo_headings['font-weight']};
      text-transform: {$typo_headings['text-transform']};
      text-align: {$typo_headings['text-align']};
    }
    ";
    

    /* Primary color
    /* ----------------------------------------------------------- */
    $primary_color_css = "
    a,
    .primary-color,
    .btn-primary-outlined,
    .btn-link,
    .super-host-flag,
    .is-style-outline .wp-block-button__link,
    .woocommerce nav.woocommerce-pagination ul li a, 
    .woocommerce nav.woocommerce-pagination ul li span,
    .woocommerce-MyAccount-navigation ul li a:hover  {
      color: {$primary_color};
    }
    .pagination > .active > a, 
    .pagination > .active > a:focus, 
    .pagination > .active > a:hover, 
    .pagination > .active > span, 
    .pagination > .active > span:focus, 
    .pagination > .active > span:hover,
    .btn-primary-outlined,
    .searchform button,
    .is-style-outline .wp-block-button__link,
    .wp-block-file .wp-block-file__button,
    .wp-block-search__button {
      border-color: {$primary_color};
    }
    
    .pagination > .active > a, 
    .pagination > .active > a:focus, 
    .pagination > .active > a:hover, 
    .pagination > .active > span, 
    .pagination > .active > span:focus, 
    .pagination > .active > span:hover,
    .media-signal .signal-icon,
    .single-blog-article .meta-tags a,
    .title .circle-icon,
    .label-primary,
    .searchform button,
    .next-prev-block .prev-box, 
    .next-prev-block .next-box,
    .dropdown-menu>.active>a, .dropdown-menu>.active>a:focus, 
    .dropdown-menu>.active>a:hover,
    .tagcloud a,
    .title-section .avatar .super-host-icon,
    .wp-block-button__link,
    .wp-block-file .wp-block-file__button,
    .wp-block-search__button {
      background-color: {$primary_color};
    }

    .slick-prev,
    .slick-next {
      color: {$primary_color};
      border: 1px solid {$primary_color};
      background-color: transparent;
    }
    .slick-prev:before,
    .slick-next:before {
      color: {$primary_color};
    }
    .slick-prev:hover:before,
    .slick-next:hover:before,
    .top-gallery-section .slick-prev:before,
    .top-gallery-section .slick-next:before {
      color: #fff;
    }

    .header-slider .slick-prev,
    .header-slider .slick-next,
    .top-gallery-section .slick-prev,
    .top-gallery-section .slick-next {
      border: 1px solid {$primary_color};
      background-color: {$primary_color};
    }
    .nav-tabs > li.active > a {
      box-shadow: 0px -2px 0px 0px inset {$primary_color};
    }
    .woocommerce nav.woocommerce-pagination ul li a:focus, 
    .woocommerce nav.woocommerce-pagination ul li a:hover, 
    .woocommerce nav.woocommerce-pagination ul li span.current {
      border: 1px solid {$primary_color};
      background-color: {$primary_color};
      color: #fff;
    }
    ";

    /* Primary color
    /* ----------------------------------------------------------- */
    $primary_color_hover_css = "
    a:hover,
    a:focus,
    a:active,
    .btn-primary-outlined:focus,
    .crncy-lang-block > li:hover a,
    .crncy-lang-block .dropdown-menu li:hover {
      color: {$primary_color_hover};
    }

    .pagination > li > a:hover,
    .pagination > li > span:hover,
    .table-hover > tbody > tr:hover,
    .search-auto-complete li:hover,
    .btn-primary-outlined:hover,
    .btn-primary-outlined:active,
    .item-tools .dropdown-menu > li > a:hover,
    .tagcloud a:hover,
    .pagination-main a:hover,
    .page-links a:hover,
    .wp-block-button__link:hover,
    .wp-block-file .wp-block-file__button:hover,
    .wp-block-search__button:hover {
      background-color: {$primary_color_hover};
    }
    .pagination > li > a:hover,
    .pagination > li > span:hover,
    .pagination-main a:hover,
    .page-links a:hover,
    .wp-block-file .wp-block-file__button:hover,
    .wp-block-search__button:hover {
      border: 1px solid {$primary_color_hover};
    }
    .is-style-outline .wp-block-button__link:hover  {
      border: 2px solid {$primary_color_hover};
      color: #fff;
    }

    .slick-prev:focus, .slick-prev:active,
    .slick-next:focus,
    .slick-next:active {
      color: {$primary_color_hover};
      border: 1px solid {$primary_color_hover};
      background-color: transparent;
    }
    .slick-prev:hover,
    .slick-next:hover {
      background-color: {$primary_color_hover};
      border: 1px solid {$primary_color_hover};
      color: #fff;
    }

    .header-slider .slick-prev:focus,
    .header-slider .slick-next:active {
      border: 1px solid {$primary_color_hover};
      background-color: {$primary_color_hover};
    }
    .header-slider .slick-prev:hover,
    .header-slider .slick-next:hover {
      background-color: rgba(241, 94, 117, 0.65);
      border: 1px solid {$primary_color_hover};
    }
    ";


    /* secondary color
    /* ----------------------------------------------------------- */
    $secondary_color_css = "
    .secondary-color,
    .btn-secondary-outlined,
    .taber-nav li.active a,
    .saved-search-block .saved-search-icon,
    .block-title .help,
    .custom-actions .btn-action,
    .daterangepicker .input-mini.active + i,
    .daterangepicker td.in-range,
    .payment-list-detail-btn {
      color: {$secondary_color};
    }

    .daterangepicker td.active,
    .daterangepicker td.active.end-date,
    .homy-progress-bar .progress-bar-inner,
    .fc-event,
    .property-calendar .current-day,
    .label-secondary,
    .wallet-label {
      background-color: {$secondary_color};
    }

    .availability-section .search-calendar .days li.day-available.current-day {
      background-color: {$secondary_color} !important;    
    }

    .daterangepicker .input-mini.active,
    .daterangepicker td.in-range,
    .msg-unread {
      background-color: rgba(84, 196, 217, 0.2);
    }

    .msgs-reply-list .msg-me {
      background-color: rgba(84, 196, 217, 0.1) !important;
    }

    .control input:checked ~ .control-text {
      color: {$secondary_color};
    }
    .control input:checked ~ .control__indicator {
      background-color: {$secondary_color_hover};
      border-color: {$secondary_color};
    }

    .open > .btn-default.dropdown-toggle,
    .custom-actions .btn-action,
    .daterangepicker .input-mini.active,
    .msg-unread {
      border-color: {$secondary_color};
    }

    .bootstrap-select .btn:focus,
    .bootstrap-select .btn:active {
      border-color: {$secondary_color} !important;
    }
    .main-search-calendar-wrap .days li.selected, 
    .main-search-calendar-wrap .days li:hover:not(.day-disabled),
    .single-listing-booking-calendar-js .days li.selected,
    .single-listing-booking-calendar-js .days li:hover:not(.day-disabled) {
      background-color: {$secondary_color} !important;
      color: #fff
    }
    .main-search-calendar-wrap .days li.in-between,
    .single-listing-booking-calendar-js .days li.in-between {
      background-color: rgba(84, 196, 217, 0.2)!important;
    }
    .single-listing-booking-calendar-js .days li.homey-not-available-for-booking:hover {
      background-color: transparent !important;
      color: #949ca5;
    }
    li.current-month.reservation_start.homey-not-available-for-booking:hover {
      background-color: {$secondary_color} !important;
      color: #fff
    }
    .woocommerce span.onsale,
    .woocommerce ul.products li.product .button,
    .woocommerce #respond input#submit.alt, 
    .woocommerce a.button.alt, 
    .woocommerce button.button.alt, 
    .woocommerce input.button.alt,
    .woocommerce #review_form #respond .form-submit input,
    .woocommerce #respond input#submit, 
    .woocommerce a.button, 
    .woocommerce button.button, 
    .woocommerce input.button {
      color: #fff;
      background-color: {$secondary_color};
      border-color: {$secondary_color}; 
    }
    .woocommerce ul.products li.product .button:focus,
    .woocommerce ul.products li.product .button:active,
    .woocommerce #respond input#submit.alt:focus, 
    .woocommerce a.button.alt:focus, 
    .woocommerce button.button.alt:focus, 
    .woocommerce input.button.alt:focus,
    .woocommerce #respond input#submit.alt:active, 
    .woocommerce a.button.alt:active, 
    .woocommerce button.button.alt:active, 
    .woocommerce input.button.alt:active,
    .woocommerce #review_form #respond .form-submit input:focus,
    .woocommerce #review_form #respond .form-submit input:active,
    .woocommerce #respond input#submit:active, 
    .woocommerce a.button:active, 
    .woocommerce button.button:active, 
    .woocommerce input.button:active,
    .woocommerce #respond input#submit:focus, 
    .woocommerce a.button:focus, 
    .woocommerce button.button:focus, 
    .woocommerce input.button:focus {
      color: #fff;
      background-color: {$secondary_color};
      border-color: {$secondary_color}; 
    }
    .woocommerce ul.products li.product .button:hover,
    .woocommerce #respond input#submit.alt:hover, 
    .woocommerce a.button.alt:hover, 
    .woocommerce button.button.alt:hover, 
    .woocommerce input.button.alt:hover,
    .woocommerce #review_form #respond .form-submit input:hover,
    .woocommerce #respond input#submit:hover, 
    .woocommerce a.button:hover, 
    .woocommerce button.button:hover, 
    .woocommerce input.button:hover {
      color: #fff;
      background-color: {$secondary_color_hover};
      border-color: {$secondary_color_hover}; 
    }
    ";

    /* secondary color :hover
    /* ----------------------------------------------------------- */
    $secondary_color_hover_css = "
    .taber-nav li:hover a,
    .payment-list-detail-btn:hover,
    .payment-list-detail-btn:focus {
      color: {$secondary_color_hover};
    }

    .header-comp-search .form-control:focus {
      background-color: rgba(84, 196, 217, 0.2);
    }

    .bootstrap-select.btn-group .dropdown-menu a:hover,
    .daterangepicker td.active:hover,
    .daterangepicker td.available:hover,
    .daterangepicker th.available:hover,
    .custom-actions .btn-action:hover,
    .calendar-table .prev:hover,
    .calendar-table .next:hover,
    .btn-secondary-outlined:hover,
    .btn-secondary-outlined:active,
    .btn-preview-listing:hover,
    .btn-preview-listing:active,
    .btn-preview-listing:focus,
    .btn-action:hover,
    .btn-action:active,
    .btn-action:focus {
      background-color: {$secondary_color_hover};
    }

    .woocommerce #respond input#submit.alt:hover, 
    .woocommerce a.button.alt:hover, 
    .woocommerce button.button.alt:hover, 
    .woocommerce input.button.alt:hover,
    .woocommerce #respond input#submit:hover, 
    .woocommerce a.button:hover, 
    .woocommerce button.button:hover, 
    .woocommerce input.button:hover {
      background-color: {$secondary_color_hover};
    }

    .form-control:focus,
    .open > .btn-default.dropdown-toggle:hover,
    .open > .btn-default.dropdown-toggle:focus,
    .open > .btn-default.dropdown-toggle:active,
    .header-comp-search .form-control:focus,
    .btn-secondary-outlined:hover,
    .btn-secondary-outlined:active,
    .btn-secondary-outlined:focus,
    .btn-preview-listing:hover,
    .btn-preview-listing:active,
    .btn-preview-listing:focus {
      border-color: {$secondary_color_hover};
    }

    .bootstrap-select .btn:focus,
    .bootstrap-select .btn:active {
      border-color: {$secondary_color_hover} !important;
    }
    ";

    /* body bg color
    /* ----------------------------------------------------------- */
    $body_color = "
    body {
      background-color: {$body_bg_color};
    }
    ";

    /* Text Color
    /* ----------------------------------------------------------- */
    $text_color = "
    body,
    .fc button,
    .pagination > li > a,
    .pagination > li > span,
    .item-title-head .title a,
    .sidebar .widget .review-block .title a,
    .sidebar .widget .comment-block .title a,
    .adults-calculator .quantity-calculator input[disbaled],
    .children-calculator .quantity-calculator input[disbaled],
    .nav-tabs > li > a,
    .nav-tabs > li > a:hover,
    .nav-tabs > li > a:focus,
    .nav-tabs > li.active > a,
    .nav-tabs > li.active > a:hover,
    .nav-tabs > li.active > a:focus,
    .modal-login-form .forgot-password-text a,
    .modal-login-form .checkbox a,
    .bootstrap-select.btn-group .dropdown-menu a,
    .header-nav .social-icons a,
    .header-nav .crncy-lang-block > li span,
    .header-comp-logo h1,
    .item-list-view .item-user-image,
    .item-title-head .title a,
    .control,
    .blog-wrap h2 a,
    .banner-caption-side-search .banner-title, 
    .banner-caption-side-search .banner-subtitle,
    .widget_categories select,
    .widget_archive  select,
    .woocommerce ul.products li.product .price,
    .woocommerce div.product p.price, 
    .woocommerce div.product span.price,
    .woocommerce #reviews #comments ol.commentlist li .meta,
    .woocommerce-MyAccount-navigation ul li a {
      color: {$text_color};
    }

    .item-title-head .title a:hover,
    .sidebar .widget .review-block .title a:hover,
    .sidebar .widget .comment-block .title a:hover {
      color: rgba(79, 89, 98, 0.5);
    }
    ";


    /* buttons colors
    /* ----------------------------------------------------------- */

    $primary_btn_color_regular = homey_option('primary_btn_color', false, 'regular');
    $primary_btn_color_hover = homey_option('primary_btn_color', false, 'hover');
    $primary_btn_color_active = homey_option('primary_btn_color', false, 'active');
    $primary_btn_bg_color_regular = homey_option('primary_btn_bg_color', false, 'regular');
    $primary_btn_bg_color_hover = homey_option('primary_btn_bg_color', false, 'hover');
    $primary_btn_bg_color_active = homey_option('primary_btn_bg_color', false, 'active');
    $primary_btn_border_color_regular = homey_option('primary_btn_border_color', false, 'regular');
    $primary_btn_border_color_hover = homey_option('primary_btn_border_color', false, 'hover');
    $primary_btn_border_color_active = homey_option('primary_btn_border_color', false, 'active');

    $secondary_btn_color_regular = homey_option('secondary_btn_color', false, 'regular');
    $secondary_btn_color_hover = homey_option('secondary_btn_color', false, 'hover');
    $secondary_btn_color_active = homey_option('secondary_btn_color', false, 'active');
    $secondary_btn_bg_color_regular = homey_option('secondary_btn_bg_color', false, 'regular');
    $secondary_btn_bg_color_hover = homey_option('secondary_btn_bg_color', false, 'hover');
    $secondary_btn_bg_color_active = homey_option('secondary_btn_bg_color', false, 'active');
    $secondary_btn_border_color_regular = homey_option('secondary_btn_border_color', false, 'regular');
    $secondary_btn_border_color_hover = homey_option('secondary_btn_border_color', false, 'hover');
    $secondary_btn_border_color_active = homey_option('secondary_btn_border_color', false, 'active');

    $button_colors = "
    .btn-primary,
    .post-password-form input[type='submit'],
    .wpcf7-submit,
    .gform_wrapper .button, .gform_button {
      color: {$primary_btn_color_regular};
      background-color: {$primary_btn_bg_color_regular};
      border-color: {$primary_btn_border_color_regular};
    }
    .btn-primary:focus,
    .btn-primary:active:focus,
    .post-password-form input[type='submit']:focus,
    .post-password-form input[type='submit']:active:focus,
    .wpcf7-submit:focus,
    .wpcf7-submit:active:focus,
    .gform_wrapper .button, .gform_button:focus,
    .gform_wrapper .button, .gform_button:active:focus {
      color: {$primary_btn_color_regular};
      background-color: {$primary_btn_bg_color_regular};
      border-color: {$primary_btn_border_color_regular};
    }
    .btn-primary:hover,
    .post-password-form input[type='submit']:hover,
    .wpcf7-submit:hover,
    .gform_wrapper .button, .gform_button:hover {
      color: {$primary_btn_color_hover};
      background-color: {$primary_btn_bg_color_hover};
      border-color: {$primary_btn_border_color_hover};
    }
    .btn-primary:active,
    .post-password-form input[type='submit']:active,
    .wpcf7-submit:active,
    .gform_wrapper .button, .gform_button:active {
      color: {$primary_btn_color_active};
      background-color: {$primary_btn_bg_color_active};
      border-color: {$primary_btn_border_color_active};
    }

    .btn-secondary {
      color: {$secondary_btn_color_regular};
      background-color: {$secondary_btn_bg_color_regular};
      border-color: {$secondary_btn_border_color_regular};
    }
    .btn-secondary:focus,
    .btn-secondary:active:focus {
      color: {$secondary_btn_color_regular};
      background-color: {$secondary_btn_bg_color_regular};
      border-color: {$secondary_btn_border_color_regular};
    }
    .btn-secondary:hover {
      color: {$secondary_btn_color_hover};
      background-color: {$secondary_btn_bg_color_hover};
      border-color: {$secondary_btn_border_color_hover};
    }
    .btn-secondary:active {
      color: {$secondary_btn_color_active};
      background-color: {$secondary_btn_bg_color_active};
      border-color: {$secondary_btn_border_color_active};
    }
    .btn-secondary-outlined,
    .btn-secondary-outlined:focus {
      color: {$secondary_btn_bg_color_regular};
      border-color: {$secondary_btn_border_color_regular};
      background-color: transparent;
    } 
    .btn-secondary-outlined:hover {
      color: {$secondary_btn_color_hover};
      background-color: {$secondary_btn_bg_color_hover};
      border-color: {$secondary_btn_border_color_hover};
    }
    .btn-secondary-outlined:hover:active {
      color: {$secondary_btn_color_active};
      background-color: {$secondary_btn_bg_color_active};
      border-color: {$secondary_btn_border_color_active};
    }
    ";


    /* main nav colors
    /* ----------------------------------------------------------- */
    $header_bg = homey_option('header_bg');
    $header_top_bg = homey_option('header_top_bg');
    $header_border = homey_option('header_border');
    $header_top_border = homey_option('header_top_border');
    $mainmenu_color = homey_option('mainmenu_color');
    $mainmenu_color_hover = homey_option('mainmenu_color_hover');
    $mainmenu_dropdown_color = homey_option('mainmenu_dropdown_color');
    $mainmenu_dropdown_color_hover = homey_option('mainmenu_dropdown_color_hover');
    $mainmenu_dropdown_bg_color = homey_option('mainmenu_dropdown_bg_color');
    $mainmenu_dropdown_border = homey_option('mainmenu_dropdown_border');

    $login_regis_color = homey_option('login_regis_color');
    $login_regis_color_hover = homey_option('login_regis_color_hover');
    $user_menu_color = homey_option('user_menu_color');
    $user_menu_color_hover = homey_option('user_menu_color_hover');
    $user_menu_color_bg_hover = homey_option('user_menu_color_bg_hover');
    $user_menu_color_bg_hover = homey_hex2rgb($user_menu_color_bg_hover);
    $user_menu_bg_color = homey_option('user_menu_bg_color');
    $mainmenu_trigger_color = homey_option('mainmenu_trigger_color');
    $header_top_social_color = homey_option('header_top_social_color');

    $main_menu_color = "
    .header-nav {
      background-color: {$header_bg};
      border-bottom: {$header_border['border-bottom']} {$header_border['border-style']} {$header_border['border-color']};
    }

    .navi > .main-menu > li > a,
    .header-mobile .btn-mobile-nav {
      color: {$mainmenu_color};
    }
    .navi > .main-menu > li > a:hover, .navi > .main-menu > li > a:active,
    .navi .homey-megamenu-wrap > .sub-menu a:hover,
    .navi .homey-megamenu-wrap > .sub-menu a:active {
      color: {$mainmenu_color_hover};
    }

    .navi > .main-menu > li > a:before,
    .listing-navi > .main-menu > li > a:before {
      background-color: {$mainmenu_color_hover};
    }
    .navi > .main-menu > li.active > a,
    .listing-navi > .main-menu > li.active > a {
      color: {$mainmenu_color_hover};
    }
    .navi .homey-megamenu-wrap,
    .listing-navi .homey-megamenu-wrap {
      background-color: #fff;
    }
    .banner-inner:before,
    .video-background:before {
      opacity: {$parallax_opacity};
    }
    .page-template-template-splash .banner-inner:before,
    .page-template-template-splash .video-background:before {
      opacity: {$splash_opacity};
    }
    .top-banner-wrap {
     {$parallax_height}
   }
   @media (max-width: 767px) {
    .top-banner-wrap {
     {$parallax_height_mobile}
   }
 }

 .header-type-2 .top-inner-header,
 .header-type-3 .top-inner-header {
  background-color: {$header_top_bg};
  border-bottom: {$header_top_border['border-bottom']} {$header_top_border['border-style']} {$header_top_border['border-color']};
}

.header-type-2 .bottom-inner-header {
  background-color: {$header_bg};
  border-bottom: {$header_border['border-bottom']} {$header_border['border-style']} {$header_border['border-color']};
}

.header-type-3 .bottom-inner-header {
  background-color: {$header_bg};
  border-bottom: {$header_border['border-bottom']} {$header_border['border-style']} {$header_border['border-color']};
}
.login-register a,
.account-loggedin,
.account-login .login-register .fa {
  color: {$login_regis_color};
  background-color: transparent;
}
.login-register a:hover, 
.login-register a:active,
.account-loggedin:hover,
.account-loggedin:active {
  color: {$login_regis_color_hover};
  background-color: transparent;
}
.account-loggedin:before {
  background-color: {$login_regis_color_hover};
}
.account-loggedin.active .account-dropdown {
  background-color: {$user_menu_bg_color}
}
.account-dropdown ul > li a {
  color: {$user_menu_color};
}
.account-dropdown ul > li a:hover {
  background-color: rgba({$user_menu_color_bg_hover['r']},{$user_menu_color_bg_hover['g']},{$user_menu_color_bg_hover['b']},.15);
  color: {$user_menu_color_hover};
}
span.side-nav-trigger {
  color: {$mainmenu_trigger_color};
}
.transparent-header span.side-nav-trigger {
  color: {$trans_menu_color};
}
.top-inner-header .social-icons a {
  color: {$header_top_social_color};
}
";


$map_cluster = homey_option('pin_cluster', '', 'url');
if (!empty($map_cluster)) {
  $clusterIcon = $map_cluster;
} else {
  $clusterIcon = get_template_directory_uri() . '/images/cluster-icon.png';
}

$osm_cluster_css = "
.homey-osm-cluster {
  background-image: url({$clusterIcon});
  text-align: center;
  color: #fff;
  width: 47px;
  height: 47px;
  line-height: 47px;
}
";

    /* transparen header
    /* ----------------------------------------------------------- */

    $transparent_menu_color = "
    .transparent-header .navi > .main-menu > li > a,
    .transparent-header .account-loggedin,
    .transparent-header .header-mobile .login-register a,
    .transparent-header .header-mobile .btn-mobile-nav {
      color: {$trans_menu_color};
    }
    .transparent-header .navi > .main-menu > li > a:hover, .transparent-header .navi > .main-menu > li > a:active,
    .transparent-header .account-loggedin:hover,
    .transparent-header .account-loggedin:active,
    .transparent-header .login-register a:hover,
    .transparent-header .login-register a:active {
      color: {$trans_menu_color_hover};
    }
    .transparent-header .navi > .main-menu > li > a:before {
      background-color: {$trans_menu_color_hover};
    }
    .transparent-header .navi > .main-menu > li > a:before,
    .transparent-header .listing-navi > .main-menu > li > a:before {
      background-color: {$trans_menu_color_hover};
    }
    .transparent-header .navi > .main-menu > li.active > a,
    .transparent-header .listing-navi > .main-menu > li.active > a {
      color: {$trans_menu_color_hover};
    }
    .transparent-header .account-loggedin:before {
      background-color: {$trans_menu_color_hover};
    }
    .transparent-header .navi .homey-megamenu-wrap,
    .transparent-header .listing-navi .homey-megamenu-wrap {
      background-color: {$trans_menu_color};
    }
    ";

    /* main nav dropdown colors
    /* ----------------------------------------------------------- */
    $main_menu_color .= "
    .navi .homey-megamenu-wrap > .sub-menu a,
    .listing-navi .homey-megamenu-wrap > .sub-menu a {
      color: {$mainmenu_dropdown_color};
      background-color: {$mainmenu_dropdown_bg_color};
    }
    .navi .homey-megamenu-wrap > .sub-menu a:hover,
    .listing-navi .homey-megamenu-wrap > .sub-menu a:hover {
      color: {$mainmenu_dropdown_color_hover};
      background-color: {$mainmenu_dropdown_bg_color};
    }
    .header-nav .menu-dropdown a,
    .header-nav .sub-menu a {
      color: {$mainmenu_dropdown_color};
      background-color: {$mainmenu_dropdown_bg_color};
      border-bottom: {$mainmenu_dropdown_border['border-bottom']} {$mainmenu_dropdown_border['border-style']} {$mainmenu_dropdown_border['border-color']};
    }
    .header-nav .menu-dropdown a:hover,
    .header-nav .sub-menu a:hover {
      color: {$mainmenu_dropdown_color_hover};
      background-color: {$mainmenu_dropdown_bg_color};
    }
    .header-nav .menu-dropdown li.active > a,
    .header-nav .sub-menu li.active > a {
      color: {$mainmenu_dropdown_color_hover};
    }
    ";

    /* Become a Host Button
    /* ----------------------------------------------------------- */
    $become_host_color = homey_option('become_host_color');
    $become_host_color_hover = homey_option('become_host_color_hover');
    $become_host_bg_color = homey_option('become_host_bg_color');
    $become_host_bg_color_hover = homey_option('become_host_bg_color_hover');
    $become_host_border_color = homey_option('become_host_border_color');
    $become_host_border_color_hover = homey_option('become_host_border_color_hover');

    $become_host = "
    .btn-add-new-listing {
      color: {$become_host_color};
      background-color: {$become_host_bg_color};
      border-color: {$become_host_border_color};
      font-size: 14px;
    }
    .btn-add-new-listing:focus {
      color: {$become_host_color_hover};
      background-color: {$become_host_bg_color_hover};
      border-color: {$become_host_border_color_hover};
    }
    .btn-add-new-listing:hover {
      color: {$become_host_color_hover};
      background-color: {$become_host_bg_color_hover};
      border-color: {$become_host_border_color_hover};
    }
    .btn-add-new-listing:active {
      color: {$become_host_color_hover};
      background-color: {$become_host_bg_color_hover};
      border-color: {$become_host_border_color_hover};
    } 
    ";


    /* top bar colors
    /* ----------------------------------------------------------- */
    $top_bar_bg = homey_option('top_bar_bg');
    $top_bar_color = homey_option('top_bar_color');
    $top_bar_color_hover = homey_option('top_bar_color_hover', false, 'rgba');

    $top_bar = "
    .header-top-bar {
      background-color: {$top_bar_bg};
    }

    .social-icons a,
    .top-bar-inner,
    .top-bar-inner li {
      color: {$top_bar_color};
    }

    .top-contact-address li {
      color: {$top_bar_color};
    }
    .top-contact-address a {
      color: {$top_bar_color};
    }
    .top-contact-address a:hover {
      color: {$top_bar_color_hover};
    }
    ";

    $logos_dimensions = "
    .header-comp-logo img {
      width: {$desktop_logo_width};
      height: {$desktop_logo_height};
    }
    .mobile-logo img {
      width: {$mobile_logo_width};
      height: {$mobile_logo_height};
    }
    ";

    /* dashboard footer colors
    /* ----------------------------------------------------------- */
    $footer_bg_color = homey_option('footer_bg_color');
    $footer_bottom_bg_color = homey_option('footer_bottom_bg_color');
    $footer_color = homey_option('footer_color');
    $footer_hover_color = homey_option('footer_hover_color');

    $footer_color = "
    .footer-top-wrap {
      background-color: {$footer_bg_color};
      color: {$footer_color};
    }

    .footer-bottom-wrap,
    .footer-small {
      background-color: {$footer_bottom_bg_color};
      color: {$footer_color};
    }

    .footer .social-icons a,
    .footer a,
    .footer .title a,
    .widget-latest-posts .post-author, 
    .widget-latest-posts .post-author a {
      color: {$footer_color};
    }

    .footer .social-icons a:hover,
    .footer a:hover,
    .footer .title a:hover {
      color: {$footer_hover_color};
    }

    .footer-copyright {
      color: {$footer_color};
    }
    ";


    /* dashboard footer colors
    /* ----------------------------------------------------------- */
    $search_bg = homey_option('search_bg');

    $main_search_color = "
    .main-search {
      background-color: {$search_bg};
    }
    ";
    
    // Marker color based on type
    if( !homey_is_dashboard() ) {
      if( taxonomy_exists('listing_type') ) {

        $marker_type = get_terms( 'listing_type' );

        if( $marker_type ) {
          foreach( $marker_type as $term ) {

            $homey_term_id = $term->term_id;
            $meta = get_option( '_homey_listing_type_'.$homey_term_id );

            if ( is_array($meta) && $meta['color_type'] == 'custom' ) {

              $marker_type_color .= "
              .gm-marker-color-{$homey_term_id} {
                background-color: {$meta['color']};
              }
              ";
              $marker_type_color .="
              .gm-marker-color-{$homey_term_id}:after {
                border-top-color: {$meta['color']};
              }
              ";

            }
          }
        }

      }
    }


    /* Featured label
    /* ----------------------------------------------------------- */
    $featured_label_bg_color = homey_option('featured_label_bg_color');
    $featured_label_color = homey_option('featured_label_color');

    $featured_label = "
    .label-featured {
      background-color: {$featured_label_bg_color};
      color: {$featured_label_color};
    }
    ";


    $homey_custom_css = homey_option('custom_css');

    wp_add_inline_style( 'homey-style',
      $typography_body.
      $typography_heading.
      $typography_nav.
      $primary_color_css.
      $primary_color_hover_css.
      $secondary_color_css.
      $secondary_color_hover_css.
      $body_color.
      $text_color.
      $osm_cluster_css.
      $transparent_menu_color.
      $main_menu_color.
      $become_host.
      $button_colors.
      $main_search_color.
      $top_bar.
      $logos_dimensions.
      $footer_color.
      $featured_label.
      $marker_type_color.
      $homey_custom_css
    );

  }
  add_action( 'wp_enqueue_scripts', 'homey_custom_styling', 21 );
  ?>