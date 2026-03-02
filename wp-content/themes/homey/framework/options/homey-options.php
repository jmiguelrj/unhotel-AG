<?php
    /**
     * ReduxFramework Sample Config File
     * For full documentation, please visit: http://docs.reduxframework.com/
     */

if ( ! class_exists( 'Redux' ) ) {
    return;
}

$allowed_html_array = array(
    'i' => array(
        'class' => array()
    ),
    'span' => array(
        'class' => array()
    ),
    'a' => array(
        'href' => array(),
        'title' => array(),
        'target' => array()
    )
);

// This is your option name where all the Redux data is stored.
$opt_name = "homey_options";

// This line is only for altering the demo. Can be easily removed.
$opt_name = apply_filters( 'redux_demo/opt_name', $opt_name );
$redux_path = ReduxFramework::$_dir;
$redux_url  = ReduxFramework::$_url;
$img_url = $redux_url. 'assets/img/';

$homey_local = homey_get_localization();

$custom_fields_array = array();
$custom_search_fields_array = array();
if(class_exists('Homey_Fields_Builder')) {
    $fields = Homey_Fields_Builder::get_form_fields();

    if(!empty($fields)) {
        foreach ( $fields as $value ) {
            $field_title = $value->label;
            $field_name = $value->field_id;
            $is_search = $value->is_search;
            
            $custom_fields_array[$field_name] = $field_title; 

            if($is_search == 'yes') {
                $custom_search_fields_array[$field_name] = $field_title;
            }
        }
    }
}


/**
 * ---> SET ARGUMENTS
 * All the possible arguments for Redux.
 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
 * */

$theme = wp_get_theme(); // For use with some settings. Not necessary.

$args = array(
    // TYPICAL -> Change these values as you need/desire
    'opt_name'             => $opt_name,
    // This is where your data is stored in the database and also becomes your global variable name.
    'display_name'         => $theme->get( 'Name' ),
    // Name that appears at the top of your panel
    'display_version'      => $theme->get( 'Version' ),
    // Version that appears at the top of your panel
    'menu_type'            => 'menu',
    //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
    'allow_sub_menu'       => true,
    // Show the sections below the admin menu item or not
    'menu_title'           => esc_html__( 'Homey Options', 'homey' ),
    'page_title'           => esc_html__( 'Options Options', 'homey' ),
    // You will need to generate a Google API key to use this feature.
    // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
    'google_api_key'       => '',
    // Set it you want google fonts to update weekly. A google_api_key value is required.
    'google_update_weekly' => false,
    // Must be defined to add google fonts to the typography module
    'async_typography'     => true,
    // Use a asynchronous font on the front end or font string
    //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
    'admin_bar'            => true,
    // Show the panel pages on the admin bar
    'admin_bar_icon'       => 'dashicons-portfolio',
    // Choose an icon for the admin bar menu
    'admin_bar_priority'   => 50,
    // Choose an priority for the admin bar menu
    'global_variable'      => '',
    // Set a different name for your global variable other than the opt_name
    'dev_mode'             => false,
    // Show the time the page took to load, etc
    'update_notice'        => false,
    // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
    'customizer'           => false,
    // Enable basic customizer support
    //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
    //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

    // OPTIONAL -> Give you extra features
    'page_priority'        => null,
    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
    'page_parent'          => 'themes.php',
    // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
    'page_permissions'     => 'manage_options',
    // Permissions needed to access the options panel.
    'menu_icon'            => '',
    // Specify a custom URL to an icon
    'last_tab'             => '',
    // Force your panel to always open to a specific tab (by id)
    'page_icon'            => 'icon-themes',
    // Icon displayed in the admin panel next to your menu_title
    'page_slug'            => 'homey_options',
    // Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
    'save_defaults'        => true,
    // On load save the defaults to DB before user clicks save or not
    'default_show'         => false,
    // If true, shows the default value next to each field that is not the default value.
    'default_mark'         => '',
    // What to print by the field's title if the value shown is default. Suggested: *
    'show_import_export'   => true,
    // Shows the Import/Export panel when not used as a field.

    // CAREFUL -> These options are for advanced use only
    'transient_time'       => 60 * MINUTE_IN_SECONDS,
    'output'               => true,
    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
    'output_tag'           => true,
    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
    // 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.

    // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
    'database'             => '',
    // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
    'use_cdn'              => true,
    // If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.

    // HINTS
    'hints'                => array(
        'icon'          => 'el el-question-sign',
        'icon_position' => 'right',
        'icon_color'    => 'lightgray',
        'icon_size'     => 'normal',
        'tip_style'     => array(
            'color'   => 'red',
            'shadow'  => true,
            'rounded' => false,
            'style'   => '',
        ),
        'tip_position'  => array(
            'my' => 'top left',
            'at' => 'bottom right',
        ),
        'tip_effect'    => array(
            'show' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'mouseover',
            ),
            'hide' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'click mouseleave',
            ),
        ),
    )
);

// ADMIN BAR LINKS -> Setup custom links in the admin bar menu as external items.
$args['admin_bar_links'][] = array(
    'id'    => 'homey-support',
    'href'  => 'https://favethemes.zendesk.com/hc/en-us/requests/new',
    'title' => esc_html__( 'Support', 'homey' ),
);


$args['share_icons'][] = array(
    'url'   => 'https://www.facebook.com/Favethemes/',
    'title' => 'Like us on Facebook',
    'icon'  => 'el el-facebook'
);
$args['share_icons'][] = array(
    'url'   => 'http://twitter.com/favethemes',
    'title' => 'Follow us on Twitter',
    'icon'  => 'el el-twitter'
);


Redux::setArgs( $opt_name, $args );

/*
 * ---> END ARGUMENTS
 */

// Change the arguments after they've been declared, but before the panel is created
add_filter('redux/options/' . $opt_name . '/args', 'change_arguments' );

/**
 * Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.
 * */
if ( ! function_exists( 'change_arguments' ) ) {
    function change_arguments( $args ) {
        $args['dev_mode'] = false;

        return $args;
    }
}

$Countries = array(
    'US' => 'United States',
    'CA' => 'Canada',
    'AU' => 'Australia',
    'FR' => 'France',
    'DE' => 'Germany',
    'IS' => 'Iceland',
    'IE' => 'Ireland',
    'IT' => 'Italy',
    'ES' => 'Spain',
    'SE' => 'Sweden',
    'AT' => 'Austria',
    'BE' => 'Belgium',
    'FI' => 'Finland',
    'CZ' => 'Czech Republic',
    'DK' => 'Denmark',
    'NO' => 'Norway',
    'GB' => 'United Kingdom',
    'CH' => 'Switzerland',
    'NZ' => 'New Zealand',
    'RU' => 'Russian Federation',
    'PT' => 'Portugal',
    'NL' => 'Netherlands',
    'IM' => 'Isle of Man',
    'AF' => 'Afghanistan',
    'AX' => 'Aland Islands ',
    'AL' => 'Albania',
    'DZ' => 'Algeria',
    'AS' => 'American Samoa',
    'AD' => 'Andorra',
    'AO' => 'Angola',
    'AI' => 'Anguilla',
    'AQ' => 'Antarctica',
    'AG' => 'Antigua and Barbuda',
    'AR' => 'Argentina',
    'AM' => 'Armenia',
    'AW' => 'Aruba',
    'AZ' => 'Azerbaijan',
    'BS' => 'Bahamas',
    'BH' => 'Bahrain',
    'BD' => 'Bangladesh',
    'BB' => 'Barbados',
    'BY' => 'Belarus',
    'BZ' => 'Belize',
    'BJ' => 'Benin',
    'BM' => 'Bermuda',
    'BT' => 'Bhutan',
    'BO' => 'Bolivia, Plurinational State of',
    'BQ' => 'Bonaire, Sint Eustatius and Saba',
    'BA' => 'Bosnia and Herzegovina',
    'BW' => 'Botswana',
    'BV' => 'Bouvet Island',
    'BR' => 'Brazil',
    'IO' => 'British Indian Ocean Territory',
    'BN' => 'Brunei Darussalam',
    'BG' => 'Bulgaria',
    'BF' => 'Burkina Faso',
    'BI' => 'Burundi',
    'KH' => 'Cambodia',
    'CM' => 'Cameroon',
    'CV' => 'Cape Verde',
    'KY' => 'Cayman Islands',
    'CF' => 'Central African Republic',
    'TD' => 'Chad',
    'CL' => 'Chile',
    'CN' => 'China',
    'CX' => 'Christmas Island',
    'CC' => 'Cocos (Keeling) Islands',
    'CO' => 'Colombia',
    'KM' => 'Comoros',
    'CG' => 'Congo',
    'CD' => 'Congo, the Democratic Republic of the',
    'CK' => 'Cook Islands',
    'CR' => 'Costa Rica',
    'CI' => "Cote d'Ivoire",
    'HR' => 'Croatia',
    'CU' => 'Cuba',
    'CW' => 'Curacao',
    'CY' => 'Cyprus',
    'DJ' => 'Djibouti',
    'DM' => 'Dominica',
    'DO' => 'Dominican Republic',
    'EC' => 'Ecuador',
    'EG' => 'Egypt',
    'SV' => 'El Salvador',
    'GQ' => 'Equatorial Guinea',
    'ER' => 'Eritrea',
    'EE' => 'Estonia',
    'ET' => 'Ethiopia',
    'FK' => 'Falkland Islands (Malvinas)',
    'FO' => 'Faroe Islands',
    'FJ' => 'Fiji',
    'GF' => 'French Guiana',
    'PF' => 'French Polynesia',
    'TF' => 'French Southern Territories',
    'GA' => 'Gabon',
    'GM' => 'Gambia',
    'GE' => 'Georgia',
    'GH' => 'Ghana',
    'GI' => 'Gibraltar',
    'GR' => 'Greece',
    'GL' => 'Greenland',
    'GD' => 'Grenada',
    'GP' => 'Guadeloupe',
    'GU' => 'Guam',
    'GT' => 'Guatemala',
    'GG' => 'Guernsey',
    'GN' => 'Guinea',
    'GW' => 'Guinea-Bissau',
    'GY' => 'Guyana',
    'HT' => 'Haiti',
    'HM' => 'Heard Island and McDonald Islands',
    'VA' => 'Holy See (Vatican City State)',
    'HN' => 'Honduras',
    'HK' => 'Hong Kong',
    'HU' => 'Hungary',
    'IN' => 'India',
    'ID' => 'Indonesia',
    'IR' => 'Iran, Islamic Republic of',
    'IQ' => 'Iraq',
    'IL' => 'Israel',
    'JM' => 'Jamaica',
    'JP' => 'Japan',
    'JE' => 'Jersey',
    'JO' => 'Jordan',
    'KZ' => 'Kazakhstan',
    'KE' => 'Kenya',
    'KI' => 'Kiribati',
    'KP' => 'Korea, Democratic People\'s Republic of',
    'KR' => 'Korea, Republic of',
    'KV' => 'kosovo',
    'KW' => 'Kuwait',
    'KG' => 'Kyrgyzstan',
    'LA' => 'Lao People\'s Democratic Republic',
    'LV' => 'Latvia',
    'LB' => 'Lebanon',
    'LS' => 'Lesotho',
    'LR' => 'Liberia',
    'LY' => 'Libyan Arab Jamahiriya',
    'LI' => 'Liechtenstein',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'MO' => 'Macao',
    'MK' => 'Macedonia',
    'MG' => 'Madagascar',
    'MW' => 'Malawi',
    'MY' => 'Malaysia',
    'MV' => 'Maldives',
    'ML' => 'Mali',
    'MT' => 'Malta',
    'MH' => 'Marshall Islands',
    'MQ' => 'Martinique',
    'MR' => 'Mauritania',
    'MU' => 'Mauritius',
    'YT' => 'Mayotte',
    'MX' => 'Mexico',
    'FM' => 'Micronesia, Federated States of',
    'MD' => 'Moldova, Republic of',
    'MC' => 'Monaco',
    'MN' => 'Mongolia',
    'ME' => 'Montenegro',
    'MS' => 'Montserrat',
    'MA' => 'Morocco',
    'MZ' => 'Mozambique',
    'MM' => 'Myanmar',
    'NA' => 'Namibia',
    'NR' => 'Nauru',
    'NP' => 'Nepal',
    'NC' => 'New Caledonia',
    'NI' => 'Nicaragua',
    'NE' => 'Niger',
    'NG' => 'Nigeria',
    'NU' => 'Niue',
    'NF' => 'Norfolk Island',
    'MP' => 'Northern Mariana Islands',
    'OM' => 'Oman',
    'PK' => 'Pakistan',
    'PW' => 'Palau',
    'PS' => 'Palestinian Territory, Occupied',
    'PA' => 'Panama',
    'PG' => 'Papua New Guinea',
    'PY' => 'Paraguay',
    'PE' => 'Peru',
    'PH' => 'Philippines',
    'PN' => 'Pitcairn',
    'PL' => 'Poland',
    'PR' => 'Puerto Rico',
    'QA' => 'Qatar',
    'RE' => 'Reunion',
    'RO' => 'Romania',
    'RW' => 'Rwanda',
    'BL' => 'Saint Barthelemy',
    'SH' => 'Saint Helena',
    'KN' => 'Saint Kitts and Nevis',
    'LC' => 'Saint Lucia',
    'MF' => 'Saint Martin (French part)',
    'PM' => 'Saint Pierre and Miquelon',
    'VC' => 'Saint Vincent and the Grenadines',
    'WS' => 'Samoa',
    'SM' => 'San Marino',
    'ST' => 'Sao Tome and Principe',
    'SA' => 'Saudi Arabia',
    'SN' => 'Senegal',
    'RS' => 'Serbia',
    'SC' => 'Seychelles',
    'SL' => 'Sierra Leone',
    'SG' => 'Singapore',
    'SX' => 'Sint Maarten (Dutch part)',
    'SK' => 'Slovakia',
    'SI' => 'Slovenia',
    'SB' => 'Solomon Islands',
    'SO' => 'Somalia',
    'ZA' => 'South Africa',
    'GS' => 'South Georgia and the South Sandwich Islands',
    'LK' => 'Sri Lanka',
    'SD' => 'Sudan',
    'SR' => 'Suriname',
    'SJ' => 'Svalbard and Jan Mayen',
    'SZ' => 'Swaziland',
    'SY' => 'Syrian Arab Republic',
    'TW' => 'Taiwan, Province of China',
    'TJ' => 'Tajikistan',
    'TZ' => 'Tanzania, United Republic of',
    'TH' => 'Thailand',
    'TL' => 'Timor-Leste',
    'TG' => 'Togo',
    'TK' => 'Tokelau',
    'TO' => 'Tonga',
    'TT' => 'Trinidad and Tobago',
    'TN' => 'Tunisia',
    'TR' => 'Turkey',
    'TM' => 'Turkmenistan',
    'TC' => 'Turks and Caicos Islands',
    'TV' => 'Tuvalu',
    'UG' => 'Uganda',
    'UA' => 'Ukraine',
    'UAE' => 'United Arab Emirates',
    'UM' => 'United States Minor Outlying Islands',
    'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan',
    'VU' => 'Vanuatu',
    'VE' => 'Venezuela, Bolivarian Republic of',
    'VN' => 'Viet Nam',
    'VG' => 'Virgin Islands, British',
    'VI' => 'Virgin Islands, U.S.',
    'WF' => 'Wallis and Futuna',
    'EH' => 'Western Sahara',
    'YE' => 'Yemen',
    'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe'
);

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Booking Mode', 'homey' ),
    'id'     => 'site-mode',
    'desc'   => '',
    'icon'   => 'el-icon-home el-icon-small',
    'fields'        => array(
        
        array(
            'id'       => 'homey_site_mode',
            'type'     => 'select',
            'title'    => esc_html__( 'Select Booking Mode', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Choose how you want to use your site.', 'homey'),
            'options'  => array(
                'per_day' => esc_html__('Per Night', 'homey'),
                'per_day_date' => esc_html__('Per Day', 'homey'),
                'per_week' => esc_html__('Per Week', 'homey'),
                'per_month' => esc_html__('Per Month', 'homey'),
                'per_hour' => esc_html__('Per Hour', 'homey'),
                'both' => esc_html__('All in One (Night, Day, Week, Month, Hour). Owner choose while add listing.', 'homey')
            ),
            'default' => 'per_day'
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'General', 'homey' ),
    'id'     => 'general-options',
    'desc'   => '',
    'icon'   => 'el-icon-home el-icon-small',
    'fields'        => array(
        array(
            'id'       => 'homey_calendar_months',
            'type'     => 'text',
            'title'    => esc_html__('Number of Months', 'homey'),
            'subtitle' => esc_html__('Choose number of months to show for calendars.', 'homey'),
            'desc'     => '',
            'default' => '12',
            'validate' => 'numeric'
        ),
        array(
            'id'       => 'homey_date_format',
            'type'     => 'button_set',
            'title'    => esc_html__('Date Format', 'homey'),
            'subtitle' => esc_html__('Select Date Format for datepickers.', 'homey'),
            'desc'     => '',
            'options' => array(
                'yy-mm-dd' => 'yy-mm-dd',
                'yy-dd-mm' => 'yy-dd-mm',
                'mm-yy-dd' => 'mm-yy-dd',
                'dd-yy-mm' => 'dd-yy-mm',
                'mm-dd-yy' => 'mm-dd-yy',
                'dd-mm-yy' => 'dd-mm-yy',
                'dd.mm.yy' => 'dd.mm.yy',
             ),
            'default' => 'yy-mm-dd'
        ),

        array(
            'id'       => 'homey_time_format',
            'type'     => 'button_set',
            'title'    => esc_html__('Time Format', 'homey'),
            'subtitle' => esc_html__('Select time format.', 'homey'),
            'desc'     => '',
            'options' => array(
                '12' => '12h',
                '24' => '24h',
             ), 
            'default' => '12'
        ),
        array(
            'id'       => 'users_admin_access',
            'type'     => 'switch',
            'title'    => esc_html__( 'Block Users Admin Access?', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Restrict user(host, renter etc) admin panel access', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'hide-host-contact',
            'type'     => 'switch',
            'title'    => esc_html__( 'Host Contact', 'homey' ),
            'desc'     => esc_html__( 'Hide host contact, social media on listing detail page and host profile.', 'homey' ),
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Hide', 'homey' ),
            'off'      => esc_html__( 'Show', 'homey' ),
        ),
        array(
            'id'       => 'menu-sticky',
            'type'     => 'switch',
            'title'    => esc_html__( 'Sticky Menu', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the sticky menu', 'homey' ),
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        
        array(
            'id'       => 'sticky_search',
            'type'     => 'switch',
            'title'    => esc_html__( 'Sticky Search', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__('Enable/Disable sticky seach', 'homey'),
            'default'  => 0,
            'on'       => esc_html__( 'Enable', 'homey' ),
            'off'      => esc_html__( 'Disable', 'homey' ),
        ),
        
        array(
            'id'       => 'site_breadcrumb',
            'type'     => 'switch',
            'title'    => esc_html__( 'Breadcrumb', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable the breadcrumb', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        
        array(
            'id'       => 'sticky_sidebar',
            'type'     => 'checkbox',
            'title'    => esc_html__( 'Sticky Sidebar', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Choose which sidebars you want to be sticky?', 'homey'),
            'options'  => array(
                'default_sidebar' => esc_html__('Default Sidebar', 'homey'),
                'listing_sidebar' => esc_html__('Listings Sidebar', 'homey'),
                'page_sidebar' => esc_html__('Page Sidebar', 'homey'),
                'blog_sidebar' => esc_html__('Blog Sidebar', 'homey'),
            ),
            'default' => array(
                'default_sidebar' => '0',
                'listing_sidebar' => '0',
                'page_sidebar' => '0',
                'blog_sidebar' => '0',
            )
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Labels', 'homey' ),
    'id'     => 'labels-management',
    'desc'   => '',
    'icon'   => 'el-icon-home el-icon-small',
    'fields'        => array(
        
    )
));
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Common', 'homey' ),
    'id'     => 'common-labels',
    'desc'   => 'Use the options in this section if you need to change the fields name below. ',
    'subsection'   => true,
    'fields'        => array(
        array(
            'id'       => 'glc_day_date_label',
            'type'     => 'text',
            'title'    => $homey_local['day_date_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['day_date_label'],
        ),
        array(
            'id'       => 'glc_day_dates_label',
            'type'     => 'text',
            'title'    => $homey_local['day_dates_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['day_dates_label'],
        ),array(
            'id'       => 'glc_day_night_label',
            'type'     => 'text',
            'title'    => $homey_local['night_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['night_label'],
        ),
        array(
            'id'       => 'glc_day_nights_label',
            'type'     => 'text',
            'title'    => $homey_local['nights_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['nights_label'],
        ),
        array(
            'id'       => 'glc_hour_label',
            'type'     => 'text',
            'title'    => esc_html__('Hour', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Hour',
        ),
        array(
            'id'       => 'glc_hours_label',
            'type'     => 'text',
            'title'    => esc_html__('Hours', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Hours',
        ),
        array(
            'id'       => 'glc_week_label',
            'type'     => 'text',
            'title'    => esc_html__('Week', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'week',
        ),
        array(
            'id'       => 'glc_weeks_label',
            'type'     => 'text',
            'title'    => esc_html__('Weeks', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'weeks',
        ),
        array(
            'id'       => 'glc_month_label',
            'type'     => 'text',
            'title'    => esc_html__('Month', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Month',
        ),
        array(
            'id'       => 'glc_months_label',
            'type'     => 'text',
            'title'    => esc_html__('Months', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Months',
        ),
        array(
            'id'       => 'cmn_guest_label',
            'type'     => 'text',
            'title'    => $homey_local['guest_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['guest_label'],
        ),
        array(
            'id'       => 'cmn_guests_label',
            'type'     => 'text',
            'title'    => $homey_local['guests_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['guests_label'],
        ),
    )
));
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Searches & Booking', 'homey' ),
    'id'     => 'searches-labels',
    'desc'   => esc_html__('Manage searches and booking form labels.', 'homey'),
    'subsection'   => true,
    'fields'        => array(
        array(
            'id'       => 'srh_whr_to_go',
            'type'     => 'text',
            'title'    => $homey_local['whr_to_go'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['whr_to_go'],
        ),
        array(
            'id'       => 'srh_arrive_label',
            'type'     => 'text',
            'title'    => $homey_local['arrive_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['arrive_label'],
        ),
        array(
            'id'       => 'srh_depart_label',
            'type'     => 'text',
            'title'    => $homey_local['depart_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['depart_label'],
        ),
        array(
            'id'       => 'srh_starts_label',
            'type'     => 'text',
            'title'    => esc_html__('Starts', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Starts',
        ),
        array(
            'id'       => 'srh_ends_label',
            'type'     => 'text',
            'title'    => esc_html__('Ends', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Ends',
        ),
        array(
            'id'       => 'srh_guest_label',
            'type'     => 'text',
            'title'    => $homey_local['guest_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['guest_label'],
        ),
        array(
            'id'       => 'srh_guests_label',
            'type'     => 'text',
            'title'    => $homey_local['sr_guests_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['sr_guests_label'],
        ),
        array(
            'id'       => 'srh_adults_label',
            'type'     => 'text',
            'title'    => $homey_local['sr_adults_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['sr_adults_label'],
        ),
        array(
            'id'       => 'srh_child_label',
            'type'     => 'text',
            'title'    => $homey_local['sr_child_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['sr_child_label'],
        ),
        array(
            'id'       => 'srh_listing_type',
            'type'     => 'text',
            'title'    => $homey_local['sr_listing_type_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['sr_listing_type_label'],
        ),
        array(
            'id'       => 'srh_room_type',
            'type'     => 'text',
            'title'    => $homey_local['sr_room_type_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['sr_room_type_label'],
        ),
        array(
            'id'       => 'srh_size',
            'type'     => 'text',
            'title'    => $homey_local['search_size'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['search_size'],
        ),
        array(
            'id'       => 'srh_price',
            'type'     => 'text',
            'title'    => $homey_local['search_price'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['search_price'],
        ),
        array(
            'id'       => 'srh_bedrooms',
            'type'     => 'text',
            'title'    => $homey_local['search_bedrooms'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['search_bedrooms'],
        ),
        array(
            'id'       => 'srh_rooms',
            'type'     => 'text',
            'title'    => $homey_local['search_rooms'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['search_rooms'],
        ),
        array(
            'id'       => 'srh_amenities',
            'type'     => 'text',
            'title'    => $homey_local['search_amenities'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['search_amenities'],
        ),
        array(
            'id'       => 'srh_facilities',
            'type'     => 'text',
            'title'    => $homey_local['search_facilities'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['search_facilities'],
        ),
    )
));
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Add New Listing', 'homey' ),
    'id'     => 'listing-add-trans',
    'desc'   => esc_html__( 'Manage add new listing section and fields titles.', 'homey' ),
    'subsection' => true,
    'fields' => array(
        
        // Information section
        array(
            'id'       => 'ad_text_yes',
            'type'     => 'text',
            'title'    => esc_html__( 'Yes Label', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['text_yes']
        ),

        array(
            'id'       => 'ad_text_no',
            'type'     => 'text',
            'title'    => esc_html__( 'No Label', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['text_no']
        ),

        array(
            'id'       => 'ad_perstay_text',
            'type'     => 'text',
            'title'    => esc_html__( 'Per Stay Label', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['perstay_text']
        ),

        array(
            'id'       => 'ad_daily_text',
            'type'     => 'text',
            'title'    => esc_html__( 'Daily Label', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['daily_text']
        ),
        array(
            'id'       => 'ad_weekly_text',
            'type'     => 'text',
            'title'    => esc_html__( 'Weekly Label', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Weekly'
        ),
        array(
            'id'       => 'ad_monthly_text',
            'type'     => 'text',
            'title'    => esc_html__( 'Monthly Label', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Monthly'
        ),
        array(
            'id'       => 'ad_day_date_text',
            'type'     => 'text',
            'title'    => esc_html__( 'One Day Label', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['daily_date_text']
        ),
        array(
            'id'       => 'ad_hourly_text',
            'type'     => 'text',
            'title'    => esc_html__( 'Hourly Label', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['hourly_text']
        ),
        array(
            'id'       => 'ad_close',
            'type'     => 'text',
            'title'    => esc_html__('Close', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => esc_html__('Closed', 'homey'),
        ),
        array(
            'id'       => 'ad_text_select',
            'type'     => 'text',
            'title'    => esc_html__('Select', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['text_select'],
        ),

        // Information
        array(
            'id'       => 'ad_section_mode',
            'type'     => 'text',
            'title'    => esc_html__( 'Listing Mode', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Listing Mode'
        ),

        array(
            'id'       => 'ad_section_info',
            'type'     => 'text',
            'title'    => esc_html__( 'Information', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for information section', 'homey'),
            'default' => $homey_local['information']
        ),

        array(
            'id'       => 'add_info_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        array(
            'id'       => 'ad_room_type',
            'type'     => 'text',
            'title'    => esc_html__( 'Room Type', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['room_type']
        ),

        array(
            'id'       => 'ad_title',
            'type'     => 'text',
            'title'    => esc_html__( 'Title', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['listing_title']
        ),

        array(
            'id'       => 'ad_title_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Title Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['title_placeholder']
        ),

        array(
            'id'       => 'ad_des',
            'type'     => 'text',
            'title'    => esc_html__( 'Description', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['listing_des']
        ),

        array(
            'id'       => 'ad_no_of_bedrooms',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Bedrooms', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_bedrooms']
        ),

        array(
            'id'       => 'ad_no_of_bedrooms_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Bedrooms Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_bedrooms_plac']
        ),

        array(
            'id'       => 'ad_no_of_guests',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Guests', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_guests']
        ),

        array(
            'id'       => 'ad_no_of_guests_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Guests Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_guests_plac']
        ),

        array(
            'id'       => 'ad_no_of_beds',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Beds', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_beds']
        ),

        array(
            'id'       => 'ad_no_of_beds_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Beds Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_beds_plac']
        ),

        array(
            'id'       => 'ad_no_of_bathrooms',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Bathrooms', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_bathrooms']
        ),

        array(
            'id'       => 'ad_no_of_bathrooms_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Bathrooms Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_bathrooms_plac']
        ),

        array(
            'id'       => 'ad_listing_rooms',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Rooms', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_rooms']
        ),

        array(
            'id'       => 'ad_listing_rooms_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Rooms Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['no_of_rooms_plac']
        ),

        array(
            'id'       => 'ad_listing_type',
            'type'     => 'text',
            'title'    => esc_html__( 'Listing Type', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['listing_type']
        ),

        array(
            'id'       => 'ad_listing_type_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Listing Type Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['listing_type_plac']
        ),

        array(
            'id'       => 'ad_listing_size',
            'type'     => 'text',
            'title'    => esc_html__( 'Size', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['listing_size']
        ),

        array(
            'id'       => 'ad_size_placeholder',
            'type'     => 'text',
            'title'    => esc_html__( 'Size Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['size_placeholder']
        ),

        array(
            'id'       => 'ad_listing_size_unit',
            'type'     => 'text',
            'title'    => esc_html__( 'Unit of measure', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['listing_size_unit']
        ),

        array(
            'id'       => 'ad_listing_size_unit_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Unit of measure Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['size_unit_placeholder']
        ),

        array(
            'id'       => 'ad_affiliate_booking_link',
            'type'     => 'text',
            'title'    => esc_html__( 'Affiliate Booking Link', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Affiliate Booking Link'
        ),

        array(
            'id'       => 'ad_affiliate_booking_link_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Enter Affiliate Booking Link', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter Affiliate Booking Link'
        ),

        array(
            'id'       => 'ad_is_featured_label',
            'type'     => 'text',
            'title'    => esc_html__( 'Make Featured', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['is_featured_label']
        ),

        array(
            'id'       => 'add_info_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Pricing 
        array(
            'id'       => 'ad_pricing_label',
            'type'     => 'text',
            'title'    => esc_html__( 'Pricing', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for pricing section', 'homey'),
            'default' => $homey_local['pricing_label']
        ),
        array(
            'id'       => 'add_pricing_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        array(
            'id'       => 'ad_ins_booking_label',
            'type'     => 'text',
            'title'    => esc_html__( 'Instant Booking', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['ins_booking_label']
        ),

        array(
            'id'       => 'ad_ins_booking_des',
            'type'     => 'text',
            'title'    => esc_html__( 'Instant Booking Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['ins_booking_des']
        ),

        array(
            'id'       => 'ad_price_label',
            'type'     => 'text',
            'title'    => esc_html__('Price', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Price'
        ),

        array(
            'id'       => 'ad_price_plac',
            'type'     => 'text',
            'title'    => esc_html__('Price Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter Price'
        ),

        array(
            'id'       => 'ad_day_date_daily_label',
            'type'     => 'text',
            'title'    => esc_html__($homey_local['day_daily_label'], 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' =>  $homey_local['day_daily_label']
        ),

        array(
            'id'       => 'ad_day_date_daily_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Daily Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['day_daily_plac']
        ),

        array(
            'id'       => 'ad_nightly_label',
            'type'     => 'text',
            'title'    => $homey_local['nightly_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['nightly_label']
        ),

        array(
            'id'       => 'ad_nightly_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Nightly Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['nightly_plac']
        ),

        array(
            'id'       => 'ad_weekly_label',
            'type'     => 'text',
            'title'    => esc_html__('Weekly Label', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Weekly'
        ),

        array(
            'id'       => 'ad_weekly_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Weekly Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter price for 1 week'
        ),

        array(
            'id'       => 'ad_monthly_label',
            'type'     => 'text',
            'title'    => esc_html__('Monthly Label', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Monthly'
        ),

        array(
            'id'       => 'ad_monthly_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Monthly Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter price for 1 month'
        ),

        array(
            'id'       => 'ad_price_postfix_label',
            'type'     => 'text',
            'title'    => esc_html__('After Price Label', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'After Price Label'
        ),

        array(
            'id'       => 'ad_price_postfix_plac',
            'type'     => 'text',
            'title'    => esc_html__('After Price Label Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter after price label. Eg: Night/Hr'
        ),

        array(
            'id'       => 'ad_weekends_label',
            'type'     => 'text',
            'title'    => $homey_local['weekends_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekends_label']
        ),

        array(
            'id'       => 'ad_weekends_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Weekends Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekends_plac']
        ),

        array(
            'id'       => 'ad_weekend_days_label',
            'type'     => 'text',
            'title'    => esc_html__( 'Weekend Days', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekend_days_label']
        ),

        array(
            'id'       => 'add_pricing_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Long Term Pricing 
        array(
            'id'       => 'ad_long_term_pricing',
            'type'     => 'text',
            'title'    => esc_html__( 'Long-term pricing', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for Long-term pricing section', 'homey'),
            'default' => $homey_local['long_term_pricing']
        ),
        array(
            'id'       => 'add_long_pricing_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        
        array(
            'id'       => 'ad_weekly7DayDates',
            'type'     => 'text',
            'title'    => esc_html__('Weekly - 7+ days', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekly7DayDates']
        ),

        array(
            'id'       => 'ad_weekly7DayDates_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Weekly - 7+ days Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekly7DayDates_plac']
        ),
        array(
            'id'       => 'ad_weekly7nights',
            'type'     => 'text',
            'title'    => esc_html__('Weekly - 7+ nights', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekly7nights']
        ),

        array(
            'id'       => 'ad_weekly7nights_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Weekly - 7+ nights Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekly7nights_plac']
        ),

        array(
            'id'       => 'ad_monthly30DayDates',
            'type'     => 'text',
            'title'    => esc_html__('Monthly - 30+ days', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['monthly30DayDates']
        ),

        array(
            'id'       => 'ad_monthly30DayDates_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Monthly - 30+ days Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['monthly30DayDates_plac']
        ),
        array(
            'id'       => 'ad_monthly30nights',
            'type'     => 'text',
            'title'    => esc_html__('Monthly - 30+ nights', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['monthly30nights']
        ),

        array(
            'id'       => 'ad_monthly30nights_plac',
            'type'     => 'text',
            'title'    => esc_html__( 'Monthly - 30+ nights Placeholder', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['monthly30nights_plac']
        ),

        array(
            'id'       => 'add_long_pricing_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Additional Costs
        array(
            'id'       => 'ad_add_costs_label',
            'type'     => 'text',
            'title'    => esc_html__( 'Additional costs', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for additional costs section', 'homey'),
            'default' => $homey_local['add_costs_label']
        ),
        array(
            'id'       => 'add_addi_cost_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        
        array(
            'id'       => 'ad_allow_additional_guests',
            'type'     => 'text',
            'title'    => $homey_local['allow_additional_guests'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['allow_additional_guests']
        ),

        array(
            'id'       => 'ad_addinal_guests_label',
            'type'     => 'text',
            'title'    => $homey_local['addinal_guests_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['addinal_guests_label']
        ),

        array(
            'id'       => 'ad_addinal_guests_plac',
            'type'     => 'text',
            'title'    => esc_html__('Additional Guests Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['addinal_guests_plac']
        ),

        array(
            'id'       => 'ad_cleaning_fee',
            'type'     => 'text',
            'title'    => $homey_local['cleaning_fee'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['cleaning_fee']
        ),

        array(
            'id'       => 'ad_cleaning_fee_plac',
            'type'     => 'text',
            'title'    => esc_html__('Cleaning Fee Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['cleaning_fee_plac']
        ),

        array(
            'id'       => 'ad_cleaning_fee_type_label',
            'type'     => 'text',
            'title'    => esc_html__('Cleaning Fee Type', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['cleaning_fee_type_label']
        ),

        array(
            'id'       => 'ad_city_fee',
            'type'     => 'text',
            'title'    => $homey_local['city_fee'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['city_fee']
        ),

        array(
            'id'       => 'ad_city_fee_plac',
            'type'     => 'text',
            'title'    => esc_html__('City Fee Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['city_fee_plac']
        ),
        array(
            'id'       => 'ad_city_fee_type_label',
            'type'     => 'text',
            'title'    => esc_html__('City Fee Type', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['city_fee_type_label']
        ),

        array(
            'id'       => 'ad_security_deposit_label',
            'type'     => 'text',
            'title'    => $homey_local['security_deposit_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['security_deposit_label']
        ),

        array(
            'id'       => 'ad_security_deposit_plac',
            'type'     => 'text',
            'title'    => esc_html__('Security Deposit Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['security_deposit_plac']
        ),

        array(
            'id'       => 'ad_tax_rate_label',
            'type'     => 'text',
            'title'    => $homey_local['tax_rate_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['tax_rate_label']
        ),

        array(
            'id'       => 'ad_tax_rate_plac',
            'type'     => 'text',
            'title'    => esc_html__('Tax Field Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['tax_rate_plac']
        ),

        array(
            'id'       => 'add_addi_cost_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Features
        array(
            'id'       => 'ad_features',
            'type'     => 'text',
            'title'    => esc_html__( 'Features', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for features section', 'homey'),
            'default' => $homey_local['features']
        ),
        array(
            'id'       => 'add_features_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        
        array(
            'id'       => 'ad_amenities',
            'type'     => 'text',
            'title'    => $homey_local['amenities'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['amenities']
        ),
        array(
            'id'       => 'ad_facilities',
            'type'     => 'text',
            'title'    => $homey_local['facilities'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['facilities']
        ),

        array(
            'id'       => 'add_features_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Image Gallery 
        array(
            'id'       => 'ad_section_media',
            'type'     => 'text',
            'title'    => esc_html__( 'Media', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for Media section', 'homey'),
            'default' => esc_html__('Media', 'homey'),
        ),
        array(
            'id'       => 'add_media_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        
        array(
            'id'       => 'ad_drag_drop_img',
            'type'     => 'text',
            'title'    => esc_html__('Drag & Drop', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['drag_drop_img']
        ),

        array(
            'id'       => 'ad_image_size_text',
            'type'     => 'text',
            'title'    => esc_html__('Image Size', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['image_size_text']
        ),

        array(
            'id'       => 'ad_upload_btn',
            'type'     => 'text',
            'title'    => esc_html__('Upload Button', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['upload_btn']
        ),

        array(
            'id'       => 'ad_video_section',
            'type'     => 'text',
            'title'    => esc_html__('Video', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['video_heading']
        ),

        array(
            'id'       => 'ad_video_url',
            'type'     => 'text',
            'title'    => esc_html__('Video Url', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['video_url']
        ),

        array(
            'id'       => 'ad_video_placeholder',
            'type'     => 'text',
            'title'    => esc_html__('Video Url Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['video_placeholder']
        ),
    
        array(
            'id'       => 'add_media_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Location
        array(
            'id'       => 'ad_location',
            'type'     => 'text',
            'title'    => $homey_local['location'],
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for location section', 'homey'),
            'default' => $homey_local['location']
        ),
        array(
            'id'       => 'add_location_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        
        array(
            'id'       => 'ad_address',
            'type'     => 'text',
            'title'    => $homey_local['address'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['address']
        ),
        array(
            'id'       => 'ad_address_placeholder',
            'type'     => 'text',
            'title'    => esc_html__('Address Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['address_placeholder']
        ),

        array(
            'id'       => 'ad_aptSuit',
            'type'     => 'text',
            'title'    => $homey_local['aptSuit'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['aptSuit']
        ),
        array(
            'id'       => 'ad_aptSuit_placeholder',
            'type'     => 'text',
            'title'    => esc_html__('AptSuit Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['aptSuit_placeholder']
        ),

        array(
            'id'       => 'ad_country',
            'type'     => 'text',
            'title'    => $homey_local['country'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['country']
        ),
        array(
            'id'       => 'ad_country_placeholder',
            'type'     => 'text',
            'title'    => esc_html__('Country Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['country_placeholder']
        ),

        array(
            'id'       => 'ad_state',
            'type'     => 'text',
            'title'    => $homey_local['state'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['state']
        ),
        array(
            'id'       => 'ad_state_placeholder',
            'type'     => 'text',
            'title'    => esc_html__('State Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['state_placeholder']
        ),

        array(
            'id'       => 'ad_city',
            'type'     => 'text',
            'title'    => $homey_local['city'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['city']
        ),
        array(
            'id'       => 'ad_city_placeholder',
            'type'     => 'text',
            'title'    => esc_html__('City Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['city_placeholder']
        ),

        array(
            'id'       => 'ad_area',
            'type'     => 'text',
            'title'    => $homey_local['area'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['area']
        ),
        array(
            'id'       => 'ad_area_placeholder',
            'type'     => 'text',
            'title'    => esc_html__('Area Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['area_placeholder']
        ),

        array(
            'id'       => 'ad_zipcode',
            'type'     => 'text',
            'title'    => $homey_local['zipcode'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['zipcode']
        ),
        array(
            'id'       => 'ad_zipcode_placeholder',
            'type'     => 'text',
            'title'    => esc_html__('Zipcode Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['zipcode_placeholder']
        ),

        array(
            'id'       => 'ad_drag_pin',
            'type'     => 'text',
            'title'    => esc_html__('Map Drag Title', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['drag_pin']
        ),

        array(
            'id'       => 'ad_find_address',
            'type'     => 'text',
            'title'    => esc_html__('Find Address', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['find_address']
        ),

        array(
            'id'       => 'ad_lat',
            'type'     => 'text',
            'title'    => $homey_local['lat'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['lat']
        ),

        array(
            'id'       => 'ad_long',
            'type'     => 'text',
            'title'    => $homey_local['long'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['long']
        ),

        array(
            'id'       => 'ad_find_address_btn',
            'type'     => 'text',
            'title'    => esc_html__('Find Address Button', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['find_address_btn']
        ),

        array(
            'id'       => 'add_location_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Bedrooms
        array(
            'id'       => 'ad_bedrooms_text',
            'type'     => 'text',
            'title'    => $homey_local['bedrooms_text'],
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for bedrooms section', 'homey'),
            'default' => $homey_local['bedrooms_text'],
        ),
        array(
            'id'       => 'add_bedroom_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        
        array(
            'id'       => 'ad_acc_bedroom_name',
            'type'     => 'text',
            'title'    => $homey_local['acc_bedroom_name'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_bedroom_name']
        ),

        array(
            'id'       => 'ad_acc_bedroom_name_plac',
            'type'     => 'text',
            'title'    => esc_html__('Bedroom Name Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_bedroom_name_plac']
        ),

        array(
            'id'       => 'ad_acc_guests',
            'type'     => 'text',
            'title'    => $homey_local['acc_guests'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_guests']
        ),

        array(
            'id'       => 'ad_acc_guests_plac',
            'type'     => 'text',
            'title'    => esc_html__('Number of Guests Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_guests_plac']
        ),

        array(
            'id'       => 'ad_acc_no_of_beds',
            'type'     => 'text',
            'title'    => $homey_local['acc_no_of_beds'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_no_of_beds']
        ),

        array(
            'id'       => 'ad_acc_no_of_beds_plac',
            'type'     => 'text',
            'title'    => esc_html__('Number of beds Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_no_of_beds_plac']
        ),

        array(
            'id'       => 'ad_acc_bedroom_type',
            'type'     => 'text',
            'title'    => $homey_local['acc_bedroom_type'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_bedroom_type']
        ),

        array(
            'id'       => 'ad_acc_bedroom_type_plac',
            'type'     => 'text',
            'title'    => esc_html__('Bed type Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_bedroom_type_plac']
        ),

        array(
            'id'       => 'ad_acc_btn_remove_room',
            'type'     => 'text',
            'title'    => esc_html__('Button Remove', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_btn_remove_room']
        ),

        array(
            'id'       => 'ad_acc_btn_add_other',
            'type'     => 'text',
            'title'    => esc_html__('Button Add More', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_btn_add_other']
        ),
    
        array(
            'id'       => 'add_bedroom_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Services
        array(
            'id'       => 'ad_services_text',
            'type'     => 'text',
            'title'    => $homey_local['services_text'],
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for services section', 'homey'),
            'default' => $homey_local['services_text'],
        ),
        array(
            'id'       => 'add_services_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        
        array(
            'id'       => 'ad_service_name',
            'type'     => 'text',
            'title'    => $homey_local['service_name'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['service_name']
        ),

        array(
            'id'       => 'ad_service_name_plac',
            'type'     => 'text',
            'title'    => esc_html__('Service Name Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['service_name_plac']
        ),

        array(
            'id'       => 'ad_service_price',
            'type'     => 'text',
            'title'    => $homey_local['service_price'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['service_price']
        ),

        array(
            'id'       => 'ad_service_price_plac',
            'type'     => 'text',
            'title'    => esc_html__('Service price Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['service_price_plac']
        ),

        array(
            'id'       => 'ad_service_des',
            'type'     => 'text',
            'title'    => $homey_local['service_des'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['service_des']
        ),

        array(
            'id'       => 'ad_service_des_plac',
            'type'     => 'text',
            'title'    => esc_html__('Service description Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['service_des_plac']
        ),

        

        array(
            'id'       => 'ad_btn_remove_service',
            'type'     => 'text',
            'title'    => esc_html__('Button Remove', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['btn_remove_service']
        ),

        array(
            'id'       => 'ad_btn_add_service',
            'type'     => 'text',
            'title'    => esc_html__('Button Add More', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['btn_add_service']
        ),
    
        array(
            'id'       => 'add_services_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Terms & Rules
        array(
            'id'       => 'ad_terms_rules',
            'type'     => 'text',
            'title'    => $homey_local['terms_rules'],
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for terms & rules section', 'homey'),
            'default' => $homey_local['terms_rules'],
        ),
        array(
            'id'       => 'add_terms_rules_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        
        array(
            'id'       => 'ad_cancel_policy',
            'type'     => 'text',
            'title'    => $homey_local['cancel_policy'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['cancel_policy']
        ),

        array(
            'id'       => 'ad_cancel_policy_plac',
            'type'     => 'text',
            'title'    => esc_html__('Policy Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['cancel_policy_plac']
        ),

        array(
            'id'       => 'ad_min_days_booking',
            'type'     => 'text',
            'title'    => $homey_local['min_days_booking'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['min_days_booking']
        ),

        array(
            'id'       => 'ad_min_days_booking_plac',
            'type'     => 'text',
            'title'    => esc_html__('Minimum days of a booking Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['min_days_booking_plac']
        ),

        array(
            'id'       => 'ad_max_days_booking',
            'type'     => 'text',
            'title'    => $homey_local['max_days_booking'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['max_days_booking']
        ),

        array(
            'id'       => 'ad_max_days_booking_plac',
            'type'     => 'text',
            'title'    => esc_html__('Maximum days of a booking Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['max_days_booking_plac']
        ),


        array(
            'id'       => 'ad_min_weeks_booking',
            'type'     => 'text',
            'title'    => esc_html__('Minimum number of weeks', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Minimum number of weeks'
        ),

        array(
            'id'       => 'ad_min_weeks_booking_plac',
            'type'     => 'text',
            'title'    => esc_html__('Minimum weeks of a booking Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter the minimum weeks of a booking (Only number)'
        ),

        array(
            'id'       => 'ad_max_weeks_booking',
            'type'     => 'text',
            'title'    => esc_html__('Maximum number of weeks', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Maximum number of weeks'
        ),

        array(
            'id'       => 'ad_max_weeks_booking_plac',
            'type'     => 'text',
            'title'    => esc_html__('Maximum weeks of a booking Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter the maximum weeks of a booking (Only number)'
        ),




        array(
            'id'       => 'ad_min_months_booking',
            'type'     => 'text',
            'title'    => esc_html__('Minimum number of months', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Minimum number of months'
        ),

        array(
            'id'       => 'ad_min_months_booking_plac',
            'type'     => 'text',
            'title'    => esc_html__('Minimum months of a booking Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter the minimum months of a booking (Only number)'
        ),

        array(
            'id'       => 'ad_max_months_booking',
            'type'     => 'text',
            'title'    => esc_html__('Maximum number of months', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Maximum number of months'
        ),

        array(
            'id'       => 'ad_max_months_booking_plac',
            'type'     => 'text',
            'title'    => esc_html__('Maximum months of a booking Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter the maximum months of a booking (Only number)'
        ),




        array(
            'id'       => 'ad_min_hours_booking',
            'type'     => 'text',
            'title'    => esc_html__('Minimum hours of a booking', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Minimum hours of a booking'
        ),

        array(
            'id'       => 'ad_min_hours_booking_plac',
            'type'     => 'text',
            'title'    => esc_html__('Minimum hours of a booking Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter the minimum hours of a booking'
        ),

        array(
            'id'       => 'ad_max_hours_booking',
            'type'     => 'text',
            'title'    => esc_html__('Maximum hours of a booking', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Maximum hours of a booking'
        ),

        array(
            'id'       => 'ad_max_hours_booking_plac',
            'type'     => 'text',
            'title'    => esc_html__('Maximum hours of a booking Placeholder', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Enter the maximum hours of a booking'
        ),

        array(
            'id'       => 'ad_check_in_after',
            'type'     => 'text',
            'title'    => $homey_local['check_in_after'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['check_in_after']
        ),

        array(
            'id'       => 'ad_check_out_before',
            'type'     => 'text',
            'title'    => $homey_local['check_out_before'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['check_out_before']
        ),

        array(
            'id'       => 'ad_smoking_allowed',
            'type'     => 'text',
            'title'    => $homey_local['smoking_allowed'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['smoking_allowed']
        ),

        array(
            'id'       => 'ad_pets_allowed',
            'type'     => 'text',
            'title'    => $homey_local['pets_allowed'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['pets_allowed']
        ),
        array(
            'id'       => 'ad_party_allowed',
            'type'     => 'text',
            'title'    => $homey_local['party_allowed'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['party_allowed']
        ),

        array(
            'id'       => 'ad_children_allowed',
            'type'     => 'text',
            'title'    => $homey_local['children_allowed'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['children_allowed']
        ),

        array(
            'id'       => 'ad_add_rules_info_optional',
            'type'     => 'text',
            'title'    => $homey_local['add_rules_info_optional'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['add_rules_info_optional']
        ),

        array(
            'id'       => 'add_terms_rules_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        // Openning Hours
        array(
            'id'       => 'ad_section_openning',
            'type'     => 'text',
            'title'    => esc_html__( 'Openning Hours', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Enter title for openning hours section', 'homey'),
            'default' =>  esc_html__( 'Openning Hours', 'homey' ),
        ),

        array(
            'id'       => 'add_openning_hours_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),

        array(
            'id'       => 'ad_mon_fri',
            'type'     => 'text',
            'title'    => esc_html__('Monday to Friday', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => esc_html__('Monday to Friday', 'homey'),
        ),

        array(
            'id'       => 'ad_sat',
            'type'     => 'text',
            'title'    => esc_html__('Saturday', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => esc_html__('Saturday', 'homey'),
        ),

        array(
            'id'       => 'ad_sun',
            'type'     => 'text',
            'title'    => esc_html__('Sunday', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => esc_html__('Sunday', 'homey'),
        ),

        array(
            'id'       => 'add_openning_hours_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Listing Detail Page', 'homey' ),
    'id'     => 'detail-page-labels',
    'desc'   => esc_html__('Manage listing detail page labels.', 'homey'),
    'subsection' => true,
    'fields' => array(

        array(
            'id'       => 'sn_type_label',
            'type'     => 'text',
            'title'    => $homey_local['type_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['type_label'],
        ),

        array(
            'id'       => 'sn_accom_label',
            'type'     => 'text',
            'title'    => $homey_local['accom_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['accom_label'],
        ),
        array(
            'id'       => 'sn_guests_label',
            'type'     => 'text',
            'title'    => $homey_local['guests_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['guests_label'],
        ),
        array(
            'id'       => 'sn_id_label',
            'type'     => 'text',
            'title'    => esc_html__('ID', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'ID',
        ),
        array(
            'id'       => 'sn_bedrooms_label',
            'type'     => 'text',
            'title'    => $homey_local['bedrooms_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['bedrooms_label'],
        ),
        array(
            'id'       => 'sn_beds_label',
            'type'     => 'text',
            'title'    => $homey_local['beds_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['beds_label'],
        ),
        array(
            'id'       => 'sn_bathrooms_label',
            'type'     => 'text',
            'title'    => $homey_local['bathrooms_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['bathrooms_label'],
        ),
        array(
            'id'       => 'sn_rooms_label',
            'type'     => 'text',
            'title'    => $homey_local['rooms_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['rooms_label'],
        ),
        array(
            'id'       => 'sn_fullbath_label',
            'type'     => 'text',
            'title'    => $homey_local['fullbath_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['fullbath_label'],
        ),
        array(
            'id'       => 'sn_halfbath_label',
            'type'     => 'text',
            'title'    => $homey_local['halfbath_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['halfbath_label'],
        ),
        array(
            'id'       => 'sn_closed_label',
            'type'     => 'text',
            'title'    => $homey_local['closed_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['closed_label'],
        ),
        array(
            'id'       => 'sn_about_listing_title',
            'type'     => 'text',
            'title'    => $homey_local['about_listing_title'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['about_listing_title'],
        ),
        array(
            'id'       => 'sn_accommodates_label',
            'type'     => 'text',
            'title'    => $homey_local['accommodates_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['accommodates_label'],
        ),
        array(
            'id'       => 'sn_opening_hours_label',
            'type'     => 'text',
            'title'    => $homey_local['opening_hours_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['opening_hours_label'],
        ),
        array(
            'id'       => 'sn_mon_fri_label',
            'type'     => 'text',
            'title'    => $homey_local['mon_fri_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['mon_fri_label'],
        ),
        array(
            'id'       => 'sn_sat_label',
            'type'     => 'text',
            'title'    => $homey_local['sat_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['sat_label'],
        ),
        array(
            'id'       => 'sn_sun_label',
            'type'     => 'text',
            'title'    => $homey_local['sun_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['sun_label'],
        ),
        array(
            'id'       => 'sn_size_label',
            'type'     => 'text',
            'title'    => $homey_local['size_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['size_label'],
        ),
        array(
            'id'       => 'sn_check_in_after',
            'type'     => 'text',
            'title'    => $homey_local['check_in_after'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['check_in_after'],
        ),
        array(
            'id'       => 'sn_check_out_before',
            'type'     => 'text',
            'title'    => $homey_local['check_out_before'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['check_out_before'],
        ),
        array(
            'id'       => 'sn_nightly_label',
            'type'     => 'text',
            'title'    => $homey_local['nightly_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['nightly_label'],
        ),
        array(
            'id'       => 'sn_hourly_label',
            'type'     => 'text',
            'title'    => esc_html__('Hourly', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Hourly',
        ),
        array(
            'id'       => 'sn_weekends_label',
            'type'     => 'text',
            'title'    => $homey_local['weekends_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekends_label'],
        ),
        array(
            'id'       => 'sn_weekly7d_label',
            'type'     => 'text',
            'title'    => $homey_local['weekly7d_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['weekly7d_label'],
        ),
        array(
            'id'       => 'sn_monthly30d_label',
            'type'     => 'text',
            'title'    => $homey_local['monthly30d_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['monthly30d_label'],
        ),
        array(
            'id'       => 'sn_security_deposit_label',
            'type'     => 'text',
            'title'    => $homey_local['security_deposit_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['security_deposit_label'],
        ),
        array(
            'id'       => 'sn_tax_rate_label',
            'type'     => 'text',
            'title'    => $homey_local['tax_rate_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['tax_rate_label'],
        ),
        array(
            'id'       => 'sn_addinal_guests_label',
            'type'     => 'text',
            'title'    => $homey_local['addinal_guests_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['addinal_guests_label'],
        ),
        array(
            'id'       => 'sn_allow_additional_guests',
            'type'     => 'text',
            'title'    => $homey_local['allow_additional_guests'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['allow_additional_guests'],
        ),
        array(
            'id'       => 'sn_cleaning_fee',
            'type'     => 'text',
            'title'    => $homey_local['cleaning_fee'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['cleaning_fee'],
        ),
        array(
            'id'       => 'sn_city_fee',
            'type'     => 'text',
            'title'    => $homey_local['city_fee'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['city_fee'],
        ),
        array(
            'id'       => 'sn_min_no_of_days',
            'type'     => 'text',
            'title'    => $homey_local['min_no_of_days'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['min_no_of_days'],
        ),
        array(
            'id'       => 'sn_max_no_of_days',
            'type'     => 'text',
            'title'    => $homey_local['max_no_of_days'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['max_no_of_days'],
        ),
        array(
            'id'       => 'sn_min_no_of_hours',
            'type'     => 'text',
            'title'    => esc_html__('Minimum number of hours', 'homey'),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Minimum number of hours',
        ),
        array(
            'id'       => 'sn_smoking_allowed',
            'type'     => 'text',
            'title'    => $homey_local['smoking_allowed'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['smoking_allowed'],
        ),
        array(
            'id'       => 'sn_pets_allowed',
            'type'     => 'text',
            'title'    => $homey_local['pets_allowed'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['pets_allowed'],
        ),
        array(
            'id'       => 'sn_party_allowed',
            'type'     => 'text',
            'title'    => $homey_local['party_allowed'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['party_allowed'],
        ),
        array(
            'id'       => 'sn_children_allowed',
            'type'     => 'text',
            'title'    => $homey_local['children_allowed'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['children_allowed'],
        ),
        array(
            'id'       => 'sn_add_rules_info',
            'type'     => 'text',
            'title'    => $homey_local['add_rules_info'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['add_rules_info'],
        ),
        array(
            'id'       => 'sn_night_label',
            'type'     => 'text',
            'title'    => $homey_local['night_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['night_label'],
        ),
        array(
            'id'       => 'sn_nights_label',
            'type'     => 'text',
            'title'    => $homey_local['nights_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['nights_label'],
        ),
        array(
            'id'       => 'sn_min_stay_is',
            'type'     => 'text',
            'title'    => $homey_local['min_stay_is'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['min_stay_is'],
        ),
        array(
            'id'       => 'sn_max_stay_is',
            'type'     => 'text',
            'title'    => $homey_local['max_stay_is'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['max_stay_is'],
        ),
        array(
            'id'       => 'sn_avail_label',
            'type'     => 'text',
            'title'    => $homey_local['avail_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['avail_label'],
        ),
        array(
            'id'       => 'sn_pending_label',
            'type'     => 'text',
            'title'    => $homey_local['pending_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['pending_label'],
        ),
        array(
            'id'       => 'sn_booked_label',
            'type'     => 'text',
            'title'    => $homey_local['booked_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['booked_label'],
        ),
        array(
            'id'       => 'sn_acc_guests_label',
            'type'     => 'text',
            'title'    => $homey_local['acc_guests_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_guests_label'],
        ),
        array(
            'id'       => 'sn_acc_guest_label',
            'type'     => 'text',
            'title'    => $homey_local['acc_guest_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['acc_guest_label'],
        ),


        array(
            'id'       => 'sn_hosted_by',
            'type'     => 'text',
            'title'    => $homey_local['hosted_by'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['hosted_by'],
        ),
        array(
            'id'       => 'sn_pr_lang',
            'type'     => 'text',
            'title'    => $homey_local['pr_lang'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['pr_lang'],
        ),
        array(
            'id'       => 'sn_pr_profile_status',
            'type'     => 'text',
            'title'    => $homey_local['pr_profile_status'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['pr_profile_status'],
        ),
        array(
            'id'       => 'sn_pr_verified',
            'type'     => 'text',
            'title'    => $homey_local['pr_verified'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['pr_verified'],
        ),
        array(
            'id'       => 'sn_pr_h_rating',
            'type'     => 'text',
            'title'    => $homey_local['pr_h_rating'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['pr_h_rating'],
        ),
        array(
            'id'       => 'sn_pr_cont_host',
            'type'     => 'text',
            'title'    => $homey_local['pr_cont_host'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['pr_cont_host'],
        ),
        array(
            'id'       => 'sn_view_profile',
            'type'     => 'text',
            'title'    => $homey_local['view_profile'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['view_profile'],
        ),
        array(
            'id'       => 'sn_text_no',
            'type'     => 'text',
            'title'    => $homey_local['text_no'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['text_no'],
        ),
        array(
            'id'       => 'sn_text_yes',
            'type'     => 'text',
            'title'    => $homey_local['text_yes'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['text_yes'],
        ),

        //Headings
        array(
            'id'     => 'single-listing-titles-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Section Titles</span>', 'homey' ), $allowed_html_array),
            'subtitle' => '',
            'desc'   => ''
        ),
        array(
            'id'       => 'sn_detail_heading',
            'type'     => 'text',
            'title'    => $homey_local['detail_heading'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['detail_heading'],
        ),
        array(
            'id'       => 'sn_prices_heading',
            'type'     => 'text',
            'title'    => $homey_local['prices_heading'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['prices_heading'],
        ),
        array(
            'id'       => 'sn_accomodation_text',
            'type'     => 'text',
            'title'    => $homey_local['accomodation_text'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['accomodation_text'],
        ),
        array(
            'id'       => 'sn_terms_rules',
            'type'     => 'text',
            'title'    => $homey_local['terms_rules'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['terms_rules'],
        ),
        array(
            'id'       => 'sn_video_heading',
            'type'     => 'text',
            'title'    => $homey_local['video_heading'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['video_heading'],
        ),
        array(
            'id'       => 'sn_availability_label',
            'type'     => 'text',
            'title'    => $homey_local['availability_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['availability_label'],
        ),
        array(
            'id'       => 'sn_features',
            'type'     => 'text',
            'title'    => $homey_local['features'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['features'],
        ),
        array(
            'id'       => 'sn_amenities',
            'type'     => 'text',
            'title'    => $homey_local['amenities'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['amenities'],
        ),
        array(
            'id'       => 'sn_facilities',
            'type'     => 'text',
            'title'    => $homey_local['facilities'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['facilities'],
        ),
        array(
            'id'       => 'sn_services_text',
            'type'     => 'text',
            'title'    => $homey_local['services_text'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['services_text'],
        ),
        array(
            'id'       => 'sn_similar_label',
            'type'     => 'text',
            'title'    => $homey_local['similar_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['similar_label'],
        ),
        array(
            'id'       => 'sn_nearby_label',
            'type'     => 'text',
            'title'    => $homey_local['nearby_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['nearby_label'],
        ),

    )
));
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Grid, List & Card', 'homey' ),
    'id'     => 'grid-list-card-labels',
    'desc'   => esc_html__( 'Manage titles for Grid, List & Card', 'homey' ),
    'subsection'   => true,
    'fields'        => array(
        array(
            'id'       => 'glc_bedrooms_label',
            'type'     => 'text',
            'title'    => $homey_local['bedrooms_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['bedrooms_label'],
        ),
        array(
            'id'       => 'glc_baths_label',
            'type'     => 'text',
            'title'    => $homey_local['baths_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['baths_label'],
        ),
        array(
            'id'       => 'glc_guests_label',
            'type'     => 'text',
            'title'    => $homey_local['guests_label'],
            'desc'     => '',
            'subtitle' => '',
            'default' => $homey_local['guests_label'],
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Logos & Favicon', 'homey' ),
    'id'     => 'logo-favicon',
    'desc'   => '',
    'icon'   => 'el-icon-home el-icon-small',
    'fields'        => array(
        array(
            'id'        => 'custom_logo',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Logo', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
            'subtitle'  => esc_html__( 'Upload the logo.', 'homey' ),
        ),

        array(
            'id'        => 'retina_logo',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'     => esc_html__( 'Retina Logo', 'homey' ),
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo@2x.png' ),
            'subtitle'  => esc_html__( 'Upload the logo for retina devices (The retina logo have to be double size of the regular logo).', 'homey' ),
        ),

        array(
            'id'        => 'custom_logo_splash',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Logo Splash & Transparent Header', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
            'subtitle'  => esc_html__( 'Upload the logo for the splash page and the transparent header.', 'homey' ),
        ),

        array(
            'id'        => 'retina_logo_splash',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'     => esc_html__( 'Retina Logo Splash  & Transparent Header', 'homey' ),
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo@2x.png' ),
            'subtitle'  => esc_html__( 'Upload the retina logo for splash page and transparent header (The retina logo have to be double size of the regular logo).', 'homey' ),
        ),

        array(
            'id'        => 'mobile_logo',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Mobile Logo', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
            'subtitle'  => esc_html__( 'Upload the custom site logo for mobiles.', 'homey' ),
        ),

        array(
            'id'        => 'mobile_retina_logo',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'     => esc_html__( 'Mobile Retina Logo', 'homey' ),
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo@2x.png' ),
            'subtitle'  => esc_html__( 'Upload the retina logo for mobiles (The retina logo have to be double size of the regular logo).', 'homey' ),
        ),

        array(
            'id'        => 'custom_logo_mobile_splash',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Mobile Logo Splash', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
            'subtitle'  => esc_html__( 'Upload the logo for the mobile splash page.', 'homey' ),
        ),

        array(
            'id'        => 'retina_logo_mobile_splash',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'     => esc_html__( 'Mobile Retina Logo Splash', 'homey' ),
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo@2x.png' ),
            'subtitle'  => esc_html__( 'Upload the retina logo for the mobile splash page (The retina logo have to be double size of the regular logo).', 'homey' ),
        ),


        array(
            'id'       => 'logo_desktop_dimensions',
            'type'     => 'dimensions',
            //'units'    => array('px'),
            'title'    => __('Desktop logo dimensions', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => array(
                'Width'   => '128',
                'Height'  => '30'
            ),
        ),

        array(
            'id'       => 'logo_mobile_dimensions',
            'type'     => 'dimensions',
            //'units'    => array('px'),
            'title'    => __('Tablet and mobile logo dimensions', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => array(
                'Width'   => '128',
                'Height'  => '30'
            ),
        ),

        array(
            'id'    => 'favicon',
            'url'           => true,
            'type'      => 'media',
            'readonly'      => false,
            'title'     => esc_html__( 'Favicon', 'homey' ),
            'default'   => array( 'url' => get_template_directory_uri() .'/images/favicon.png' ),
            'subtitle'  => esc_html__( 'Upload the site favicon. (16px x 16px)', 'homey' ),
        ),

        array(
            'id'        => 'iphone_icon',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'     => esc_html__( 'Apple iPhone Icon ', 'homey' ),
            'default'   => array(
                'url'   => get_template_directory_uri() .'/images/favicon-57x57.png'
            ),
            'subtitle'  => esc_html__( 'Upload the iPhone icon (57px x 57px).', 'homey' ),
        ),

        array(
            'id'        => 'iphone_icon_retina',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'     => esc_html__( 'Apple iPhone Retina Icon ', 'homey' ),
            'default'   => array(
                'url'   => get_template_directory_uri() .'/images/favicon-114x114.png'
            ),
            'subtitle'  => esc_html__( 'Upload the iPhone retina icon (114px x 114px).', 'homey' ),
        ),

        array(
            'id'        => 'ipad_icon',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'     => esc_html__( 'Apple iPad Icon ', 'homey' ),
            'default'   => array(
                'url'   => get_template_directory_uri() .'/images/favicon-72x72.png'
            ),
            'subtitle'  => esc_html__( 'Upload the iPad icon (72px x 72px).', 'homey' ),
        ),

        array(
            'id'        => 'ipad_icon_retina',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'     => esc_html__( 'Apple iPad Retina Icon ', 'homey' ),
            'default'   => array(
                'url'   => get_template_directory_uri() .'/images/favicon-144x144.png'
            ),
            'subtitle'  => esc_html__( 'Upload the iPad retina icon (144px x 144px).', 'homey' ),
        )
    ),
) );

/* **********************************************************************
 * Headers
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Header Nav', 'homey' ),
    'id'               => 'header-nav',
    'desc'             => '',
    'fields'           => array(
        array(
            'id'       => 'header_type',
            'type'     => 'select',
            'title'    => esc_html__( 'Header Style', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                '1' => esc_html__( 'Header v1', 'homey' ),
                '2' => esc_html__( 'Header v2', 'homey' ),
                '3' => esc_html__( 'Header v3', 'homey' ),
                '4' => esc_html__( 'Header v4', 'homey' ),
            ),
            'desc'     => esc_html__( 'Select the header version you want to use', 'homey' ),
            'default'  => '1'// 1 = on | 0 = off
        ),
        array(
            'id'       => 'header_width',
            'type'     => 'select',
            'title'    => esc_html__( 'Layout', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'container' => esc_html__( 'Boxed', 'homey' ),
                'container-fluid'   => esc_html__( 'Full Width', 'homey' )
            ),
            'desc'     => esc_html__( 'Select the header layout', 'homey' ),
            'default'  => 'container'// 1 = on | 0 = off
        ),
        array(
            'id'       => 'header_menu_align',
            'type'     => 'select',
            'title'    => esc_html__( 'Navigation Align', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'text-left'  => esc_html__( 'Left Align', 'homey' ),
                'text-right' => esc_html__( 'Right Align', 'homey' ),
                'text-center' => esc_html__( 'Center Align (only for header v2)', 'homey' ),
            ),
            'desc'     => esc_html__( 'Select the navigation alignment', 'homey' ),
            'required' => array('header_type', '!=', '3' ),
            'default'  => 'text-right'// 1 = on | 0 = off
        ),
        array(
            'id'       => 'become_host_btn',
            'type'     => 'switch',
            'title'    => esc_html__( 'Become Host', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the become host button on the main menu', 'homey' ),
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'become_host_link',
            'type'     => 'select',
            'data'     => 'pages',
            'title'    => esc_html__( 'Become Host Page', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Select which page to link the become host button', 'homey' ),
            'required' => array('become_host_btn', '=', '1')
        ),
        array(
            'id'       => 'become_host_label',
            'type'     => 'text',
            'default'  => esc_html__('Become a Host', 'homey'),
            'title'    => esc_html__( 'Become Host Text', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter text for the become host button', 'homey' ),
            'required' => array('become_host_btn', '=', '1')
        )
    )
) );

/* **********************************************************************
 * Header 3 social
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Social', 'homey' ),
    'id'     => 'header-social',
    'desc'   => '',
    'icon'   => '',
    'subsection'   => true,
    'fields'        => array(
        array(
            'id'       => 'social-header',
            'type'     => 'switch',
            'title'    => esc_html__( 'Social media icons for header v3', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the social media icons for the header v3', 'homey' ),
            'subtitle' => '',
            'default'  => 0,
            'on'       => 'Enabled',
            'off'      => 'Disabled',
        ),
        array(
            'id'       => 'hs-facebook',
            'type'     => 'text',
            'required' => array( 'social-header', '=', '1' ),
            'title'    => esc_html__( 'Facebook', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Facebook profile or page URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'hs-twitter',
            'type'     => 'text',
            'required' => array( 'social-header', '=', '1' ),
            'title'    => esc_html__( 'Twitter', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Twitter profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'hs-googleplus',
            'type'     => 'text',
            'required' => array( 'social-header', '=', '1' ),
            'title'    => esc_html__( 'Google Plus', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Google Plus profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'hs-linkedin',
            'type'     => 'text',
            'required' => array( 'social-header', '=', '1' ),
            'title'    => esc_html__( 'Linked In', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Linkedin profile or business page URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'hs-instagram',
            'type'     => 'text',
            'required' => array( 'social-header', '=', '1' ),
            'title'    => esc_html__( 'Instagram', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Instagram profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'hs-pinterest',
            'type'     => 'text',
            'required' => array( 'social-header', '=', '1' ),
            'title'    => esc_html__( 'Pinterest', 'homey' ),
            'subtitle' => '',
            'desc'     =>  esc_html__( 'Enter your Pinterest profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'hs-yelp',
            'type'     => 'text',
            'required' => array( 'social-header', '=', '1' ),
            'title'    => esc_html__( 'Yelp', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Yelp profile or page URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'hs-youtube',
            'type'     => 'text',
            'required' => array( 'social-header', '=', '1' ),
            'title'    => esc_html__( 'Youtube', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter Youtube profile URL', 'homey' ),
            'default'  => false,
        )

    ),
));

/* **********************************************************************
 * Login & Register
 * **********************************************************************/
if( class_exists('Homey_login_register') ):
Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Login & Register', 'homey' ),
    'id'               => 'header-login-register',
    'subsection'       => false,
    'desc'             => '',
    'fields'           => array(
        array(
            'id'       => 'nav_login',
            'type'     => 'switch',
            'title'    => esc_html__( 'Login', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the login button on the main menu', 'homey' ),
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'nav_register',
            'type'     => 'switch',
            'title'    => esc_html__( 'Register', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the register button on the main menu', 'homey' ),
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'        => 'login_image',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__('Login Screen Image', 'homey'),
            'subtitle'  => '',
            'default'   => '',
            'desc'      => esc_html__( 'Upload an image for the login pop-up', 'homey' ),
        ),
        array(
            'id'       => 'login_text',
            'type'     => 'text',
            'title'    => esc_html__( 'Login screen text', 'homey' ),
            'subtitle' => '',
            'default'  => 'Lorem Ipsum Dolor Sit Amet',
            'desc'     => esc_html__( 'Enter a overlay text for the login screen image', 'homey' )
        ),
        array(
            'id'        => 'register_image',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__('Register Screen Image', 'homey'),
            'subtitle'  => '',
            'default'   => '',
            'desc'      => esc_html__( 'Upload an image for the register pop-up', 'homey' ),
        ),
        array(
            'id'       => 'register_text',
            'type'     => 'text',
            'title'    => esc_html__( 'Register screen text', 'homey' ),
            'subtitle' => '',
            'default'  => 'Lorem Ipsum Dolor Sit Amet',
            'desc'     => esc_html__( 'Enter a overlay text for the register screen image', 'homey' )
        ),
        array(
            'id'       => 'enable_password',
            'type'     => 'select',
            'title'    => esc_html__( 'Users can type the password on registration form', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'yes'   => esc_html__( 'Yes', 'homey' ),
                'no'    => esc_html__( 'No', 'homey' )
            ),
            'desc'     => esc_html__('If no, users will get an auto-generated password via email', 'homey'),
            'default'  => 'no'// 1 = on | 0 = off
        ),
        array(
            'id'       => 'login_redirect',
            'type'     => 'select',
            'title'    => esc_html__( 'After Login Redirect Page', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'same_page'   => esc_html__( 'Current Page', 'homey' ),
                'diff_page'    => esc_html__( 'Different Page', 'homey' )
            ),
            'desc'     => esc_html__('Select a page where you want to redirect the users after they have been logged in', 'homey'),
            'default'  => 'same_page'
        ),
        array(
            'id'       => 'login_redirect_link',
            'type'     => 'text',
            'required' => array('login_redirect', '=', 'diff_page' ),
            'title'    => esc_html__( 'Enter Redirect Page Link', 'homey' ),
            'subtitle' => esc_html__( 'This must be a URL.', 'homey' ),
            'desc'     => '',
            'validate' => 'url',
            'default'  => '',
        ),

        array(
            'id'       => 'login_terms_condition',
            'type'     => 'select',
            'data'     => 'pages',
            'title'    => esc_html__( 'Terms & Conditions', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Select the page to use for Terms & Conditions', 'homey' ),
        ),
        array(
            'id'       => 'facebook_login',
            'type'     => 'select',
            'title'    => esc_html__( 'Allow login via Facebook?', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'yes'   => esc_html__( 'Yes', 'homey' ),
                'no'    => esc_html__( 'No', 'homey' )
            ),
            'desc'     => '',
            'default'  => 'no'
        ),
        array(
            'id'       => 'facebook_api_key',
            'type'     => 'text',
            'required' => array( 'facebook_login', '=', 'yes' ),
            'title'    => esc_html__( 'Facebook Api key', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter the Facebook Api code for Facebook login', 'homey' ),
            'default'  => ''
        ),
        array(
            'id'       => 'facebook_secret',
            'type'     => 'text',
            'required' => array( 'facebook_login', '=', 'yes' ),
            'title'    => esc_html__( 'Facebook Secret Code', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter the Facebook the secret code for Facebook login', 'homey' ),
            'default'  => ''
        ),
        array(
            'id'       => 'google_login',
            'type'     => 'select',
            'title'    => esc_html__( 'Allow login via Google?', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'yes'   => esc_html__( 'Yes', 'homey' ),
                'no'    => esc_html__( 'No', 'homey' )
            ),
            'desc'     => '',
            'default'  => 'no'
        ),
        array(
            'id'       => 'google_client_id',
            'type'     => 'text',
            'required' => array( 'google_login', '=', 'yes' ),
            'title'    => esc_html__( 'Google OAuth Client ID', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter the Google oAuth client ID for Google login', 'homey' ),
            'default'  => ''
        ),
        array(
            'id'       => 'google_secret',
            'type'     => 'text',
            'required' => array( 'google_login', '=', 'yes' ),
            'title'    => esc_html__( 'Google Client Secret', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter the Google client secret code for Google login', 'homey' ),
            'default'  => ''
        ),
    )
) );

Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'User Roles', 'homey' ),
    'id'               => 'header-user-roles',
    'subsection'       => true,
    'desc'             => '',
    'fields'           => array(
        array(
            'id'       => 'show_roles',
            'type'     => 'switch',
            'title'    => esc_html__( 'User roles on the register form', 'homey' ),
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable user roles on the register form', 'homey' ),
        ),
        array(
            'id'       => 'host_role',
            'type'     => 'text',
            'title'    => esc_html__( 'Host Role', 'homey' ),
            'subtitle' => esc_html__( 'Name for host role', 'homey' ),
            'desc'     => esc_html__( 'Default: I want to host', 'homey' ),
            'default'  => esc_html__( 'I want to host', 'homey')
        ),
        array(
            'id'       => 'renter_role',
            'type'     => 'text',
            'title'    => esc_html__( 'Renter Role', 'homey' ),
            'subtitle' => esc_html__( 'Name for renter role', 'homey' ),
            'desc'     => esc_html__( 'Default: I want to book', 'homey' ),
            'default'  => esc_html__( 'I want to book', 'homey')
        )
    )
) );

endif;


 /* **********************************************************************
 * Splash Page Template
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Splash Page', 'homey' ),
    'id'     => 'splash-page',
    'desc'   => '',
    'icon'   => 'el-icon-screen el-icon-small',
    'fields'        => array(
        array(
            'id'       => 'splash_layout',
            'type'     => 'select',
            'title'    => esc_html__( 'Splash Page Layout', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'container-fluid' => 'Full Width',
                'container' => 'Boxed'
            ),
            'desc'     => esc_html__( 'Select the splash page layout', 'homey' ),
            'default'  => 'container-fluid'
        ),
        array(
            'id'       => 'backgroud_type',
            'type'     => 'select',
            'title'    => esc_html__( 'Background Type', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'image' => 'Background Image',
                'slider' => 'Background Slider',
                'video' => 'Background Video'
            ),
            'desc'     => esc_html__( 'Select the background type for the splash page', 'homey' ),
            'default'  => 'image'
        ),

        array(
            'id'       => 'splash_search',
            'type'     => 'switch',
            'title'    => esc_html__( 'Search', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the splash page search', 'homey' ),
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),

        array(
            'id'       => 'splash_search_style',
            'type'     => 'select',
            'title'    => esc_html__( 'Search Style', 'homey' ),
            'subtitle' => '',
            'required' => array('splash_search', '=', '1'),
            'options'   => array(
                'horizontal' => esc_html__('Horizontal', 'homey' ),
                'vertical' => esc_html__('Vertical', 'homey' ),  
            ),
            'desc'     => esc_html__( 'Choose the splash page search style', 'homey' ),
            'default'  => 'horizontal'
        ),

        array(
            'id'       => 'splash_page_nav',
            'type'     => 'switch',
            'title'    => esc_html__( 'Navigation', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the navigation menu on the splash page', 'homey' ),
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        
        array(
            'id'       => 'splash_menu_align',
            'type'     => 'select',
            'title'    => esc_html__( 'Navigation Align', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'text-left'  => esc_html__( 'Left Align', 'homey' ),
                'text-right' => esc_html__( 'Right Align', 'homey' )
            ),
            'desc'     => esc_html__( 'Select the navigation alignment', 'homey' ),
            'default'  => 'text-left'// 1 = on | 0 = off
        ),


        // Section background image
        array(
            'id'        => 'splash_image',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__('Upload image', 'homey'),
            'required' => array('backgroud_type', '=', 'image'),
            'default'   => '',
            'desc'      => esc_html__('Recommended image size 2000 x 1000 pixels', 'homey'),
            'subtitle'  => '',
        ),

        // Section background slider
        array(
            'id'        => 'splash_slider',
            'url'       => true,
            'type'      => 'gallery',
            'title'     => esc_html__('Add/Edit Images', 'homey'),
            'required' => array('backgroud_type', '=', 'slider'),
            'default'   => '',
            'desc'      => esc_html__('Recommended image size 2000 x 1000 pixels', 'homey'),
            'subtitle'  => '',
        ),

        // Section background video
        array(
            'id'       => 'splash_video_section-start',
            'type'     => 'section',
            'required' => array('backgroud_type', '=', 'video'),
            'title'    => esc_html__( 'Background Video Options', 'homey' ),
            'subtitle' => 'If you want to use the video background option, is mandatory to upload all video file format and the video image',
            'indent'   => true,
        ),
        array(
            'id'        => 'splash_bg_mp4',
            'url'       => true,
            'type'      => 'media',
            'mode'       => false,
            'title'     => esc_html__('MP4 File', 'homey'),
            'default'   => '',
            'desc'      => esc_html__('Upload the MP4 file', 'homey'),
            'subtitle'  => '',
        ),
        array(
            'id'        => 'splash_bg_webm',
            'url'       => true,
            'type'      => 'media',
            'mode'       => false,
            'title'     => esc_html__('WEBM File', 'homey'),
            'default'   => '',
            'desc'      => esc_html__('Upload the WEBM file', 'homey'),
            'subtitle'  => '',
        ),
        array(
            'id'        => 'splash_bg_ogv',
            'url'       => true,
            'type'      => 'media',
            'mode'       => false,
            'title'     => esc_html__('OGV File', 'homey'),
            'default'   => '',
            'desc'      => esc_html__('Upload the OGV file', 'homey'),
            'subtitle'  => '',
        ),
        array(
            'id'        => 'splash_video_image',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__('Upload video image', 'homey'),
            'default'   => '',
            'desc'      => esc_html__('Upload an image to use as cover during the video file load', 'homey'),
            'subtitle'  => '',
        ),
        array(
            'id'     => 'splash_video_section_end',
            'type'   => 'section',
            'indent' => false,
        ),
        array(
            'id'        => 'splash_opacity',
            'type'      => 'text',
            'title'     => esc_html__('Opacity', 'homey'),
            'default'   => '0.5',
            'desc'      => esc_html__('Enter overlay opacity ie: 0.1, 0.2, 0.5', 'homey'),
            'subtitle'  => '',
            'validate' => 'numeric'
        ),

    ),
));

Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Welcome Title', 'homey' ),
    'id'               => 'splash-welcome',
    'subsection'       => true,
    'desc'             => '',
    'fields'           => array(
        array(
            'id'       => 'splash_welcome_text',
            'type'     => 'text',
            'title'    => esc_html__( 'Splash Page Title', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter the splash page title', 'homey' ),
            'default'  => 'Book and Experience Unique Places',
        ),
        array(
            'id'       => 'splash_welcome_sub',
            'type'     => 'text',
            'title'    => esc_html__( 'Splash Page Subtitle', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter the splash page subtitle', 'homey' ),
            'default'  => 'WordPress Theme For Rentals',
        )
    )
) );

/* **********************************************************************
 * Top Bar
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Top Bar', 'homey' ),
    'id'               => 'header-top-bar',
    'subsection'       => false,
    'desc'             => '',
    'fields'           => array(
        array(
            'id'       => 'top_bar',
            'type'     => 'switch',
            'title'    => esc_html__( 'Top bar', 'homey' ),
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the header top bar', 'homey' ),
        ),
        array(
            'id'       => 'top_bar_width',
            'type'     => 'select',
            'title'    => esc_html__( 'Layout', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'container' => esc_html__( 'Boxed', 'homey' ),
                'container-fluid'   => esc_html__( 'Full Width', 'homey' )
            ),
            'desc'     => esc_html__( 'Select the top bar layout', 'homey' ),
            'default'  => 'container'// 1 = on | 0 = off
        ),
        array(
            'id'       => 'top_bar_mobile',
            'type'     => 'switch',
            'title'    => esc_html__( 'Hide Top Bar in Mobile?', 'homey' ),
            'desc'     => esc_html__( 'Select yes if you want to hide the top bar on mobile devices', 'homey'),
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'top_bar_left',
            'type'     => 'select',
            'title'    => esc_html__( 'Top Bar Left Area', 'homey' ),
            'subtitle' => '',
            'desc' => esc_html__( 'What would you like to show on the top bar left area', 'homey' ),
            'options'   => array(
                'none'   => esc_html__( 'Nothing', 'homey' ),
                //'menu_bar'    => esc_html__( 'Menu ( Create and assing menu under Appearance -> Menus )', 'homey' ),
                'social_icons'    => esc_html__( 'Social Icons', 'homey' ),
                'contact_info'    => esc_html__( 'Contact Info', 'homey' ),
                //'contact_info_and_social_icons'    => esc_html__( 'Contact Info + Social Icons', 'homey' ),
                'currency_switchers'    => esc_html__( 'Currency Switcher', 'homey' ),
                'slogan'    => esc_html__( 'Slogan', 'homey' )
            ),
            'default'  => 'none'
        ),
        array(
            'id'       => 'top_bar_right',
            'type'     => 'select',
            'title'    => esc_html__( 'Top Bar Right Area', 'homey' ),
            'subtitle' => '',
            'desc' => esc_html__( 'What would you like to show on the top bar right area', 'homey' ),
            'options'   => array(
                'none'   => esc_html__( 'Nothing', 'homey' ),
                //'menu_bar'    => esc_html__( 'Menu ( Create and assing menu under Appearance -> Menus )', 'homey' ),
                'social_icons'    => esc_html__( 'Social Icons', 'homey' ),
                'contact_info'    => esc_html__( 'Contact Info', 'homey' ),
                //'contact_info_and_social_icons'    => esc_html__( 'Contact Info + Social Icons', 'homey' ),
                'currency_switchers'    => esc_html__( 'Currency Switcher', 'homey' ),
                'slogan'    => esc_html__( 'Slogan', 'homey' ),
                
            ),
            'default'  => 'none'
        ),
        array(
            'id'        => 'top_bar_phone',
            'type'      => 'text',
            'default'   => '',
            'title'     => esc_html__( 'Phone Number', 'homey' ),
            'subtitle'  => '',
            'desc' => esc_html__('Enter your phone number', 'homey' ),
        ),
        array(
            'id'        => 'top_bar_email',
            'type'      => 'text',
            'default'   => '',
            'title'     => esc_html__( 'Email Address', 'homey' ),
            'subtitle'  => '',
            'desc' => esc_html__('Enter your email address', 'homey' ),
        ),
        array(
            'id'        => 'top_bar_slogan',
            'type'      => 'textarea',
            'default'   => '',
            'title'     => esc_html__( 'Slogan', 'homey' ),
            'subtitle'  => '',
            'desc'  => esc_html__( 'Enter the website slogan', 'homey' )
        )
    )
) );

/* **********************************************************************
 * Header 3 social
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Social', 'homey' ),
    'id'     => 'topbar-social',
    'desc'   => '',
    'icon'   => '',
    'subsection'   => true,
    'fields'        => array(
        array(
            'id'       => 'tps-facebook',
            'type'     => 'text',
            'title'    => esc_html__( 'Facebook', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Facebook profile or page URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'tps-twitter',
            'type'     => 'text',
            'title'    => esc_html__( 'Twitter', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Twitter profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'tps-googleplus',
            'type'     => 'text',
            'title'    => esc_html__( 'Google Plus', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Google Plus profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'tps-linkedin',
            'type'     => 'text',
            'title'    => esc_html__( 'Linked In', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Linkedin profile or business page URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'tps-instagram',
            'type'     => 'text',
            'title'    => esc_html__( 'Instagram', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Instagram profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'tps-pinterest',
            'type'     => 'text',
            'title'    => esc_html__( 'Pinterest', 'homey' ),
            'subtitle' => '',
            'desc'     =>  esc_html__( 'Enter your Pinterest profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'tps-yelp',
            'type'     => 'text',
            'title'    => esc_html__( 'Yelp', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter your Yelp profile or page URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'tps-youtube',
            'type'     => 'text',
            'title'    => esc_html__( 'Youtube', 'homey' ),
            'subtitle' => '',
            'desc'     => esc_html__( 'Enter Youtube profile URL', 'homey' ),
            'default'  => false,
        )

    ),
));

/* **********************************************************************
 * Price Format
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Price & Currency', 'homey' ),
    'id'     => 'price-format',
    'desc'   => '',
    'icon'   => 'el-icon-usd el-icon-small',
    'fields'        => array(

        array(
            'id'       => 'currency_converter',
            'type'     => 'switch',
            'title'    => esc_html__( 'Currency Switcher', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Enable currency switcher', 'homey'),
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'default_currency',
            'type'     => 'select',
            'title'    => esc_html__('Default Currency', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Choose default currency', 'homey'),
            'required'  => array('currency_converter', '=', '1'),
            'default' => 'USD',
            'options'  => homey_available_currencies()
        ),
        array(
            'id'   => 'info_normal',
            'type' => 'info',
            'title'    => esc_html__( 'Info', 'homey' ),
            'desc'     => wp_kses(__( 'Please <a target="_blank" href="admin.php?page=fcc_currencies">Add Currencies</a> here.', 'homey' ), $allowed_html_array),
            'required'  => array('currency_converter', '=', '1'),
        ),
        array(
            'id'        => 'currency_symbol',
            'type'      => 'text',
            'title'     => esc_html__( 'Currency Symbol', 'homey' ),
            'readonly' => false,
            'default'   => '$',
            'subtitle'  => '',
            'desc'  => esc_html__( 'Provide the currency simbol. For example $', 'homey' ),
            'validate' => 'not_empty'
        ),
        array(
            'id'        => 'currency_position',
            'type'      => 'select',
            'title'     => esc_html__( 'Where to Show the currency?', 'homey' ),
            'readonly' => false,
            'options'   => array(
                'before'    => esc_html__( 'Before', 'homey' ),
                'after'         => esc_html__( 'After', 'homey' )
            ),
            'default'   => 'before',
            'subtitle'  => '',
        ),
        array(
            'id'        => 'decimals',
            'type'      => 'select',
            'title'     => esc_html__( 'Number of decimal points?', 'homey' ),
            'readonly' => false,
            'options'   => array(
                '0' => '0',
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
                '7' => '7',
                '8' => '8',
                '9' => '9',
                '10' => '10',
            ),
            'default'   => '2',
            'subtitle'  => '',
        ),
        array(
            'id'        => 'decimal_point_separator',
            'type'      => 'text',
            'title'     => esc_html__( 'Decimal Point Separator', 'homey' ),
            'readonly' => false,
            'default'   => '.',
            'subtitle'  => esc_html__( 'Provide the decimal point separator. For Example: .', 'homey' ),
            'validate' => 'not_empty'
        ),
        array(
            'id'        => 'thousands_separator',
            'type'      => 'text',
            'title'     => esc_html__( 'Thousands Separator', 'homey' ),
            'readonly' => false,
            'default'   => ',',
            'subtitle'  => esc_html__( 'Provide the thousands separator. For Example: ,', 'homey' ),
            'validate' => 'not_empty'
        ),
        array(
            'id'        => 'currency_separator',
            'type'      => 'text',
            'title'     => esc_html__( 'Price Separator', 'homey' ),
            'readonly' => false,
            'default'   => '/',
            'subtitle'  => '',
            'desc'  => esc_html__( 'Provide what you want to show between price and price label. Example: / or empty space', 'homey' )
        ),
    ),
));

/* **********************************************************************
 * Reservation
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Reservation', 'homey' ),
    'id'     => 'reservation-section',
    'desc'   => '',
    'icon'   => 'el-icon-usd el-icon-small',
    'fields'        => array(

        array(
            'id'       => 'reservation_payment',
            'type'     => 'select',
            'title'    => esc_html__('Payment while booking', 'homey' ),
            'subtitle' => '',
            'desc' => esc_html__('Check how much initial payment you want the client to deposit while booking', 'homey' ),
            'options'   => array(
                'percent' => esc_html__('Percentage(%)', 'homey'),
                'full' => esc_html__('Full Payment', 'homey'),
                'only_security' => esc_html__('Security Deposit (It can be add while add/edit listing)', 'homey'),
                'only_services' => esc_html__('Only Services Fee', 'homey'),
                'services_security' => esc_html__('Services fee + Security deposit', 'homey'),
                'no_upfront' => esc_html__('No upfront, Take full payment locally', 'homey'),
            ),
            'default' => 'percent',
        ),
        array(
            'id'       => 'no_login_needed_for_booking',
            'type'     => 'select',
            'title'    => esc_html__('No Login Need For Booking', 'homey' ),
            'subtitle' => '',
            'desc' => esc_html__('If you want users to make reservations without login?', 'homey' ),
            'options'   => array(
                'yes' => esc_html__('Yes', 'homey'),
                'no' => esc_html__('No', 'homey'),
            ),
            'default' => 'no',
        ),
        array(
            'id'       => 'booking_percent',
            'type'     => 'text',
            'title'    => esc_html__('Percent', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__('Enter how many % payment required while booking.', 'homey'),
            'required' => array('reservation_payment', '=', 'percent'),
            'default' => '',
            'validate' => 'numeric'
        ),
        
        array(
            'id'       => 'num_0f_hours_to_remove_pending_resrv',
            'type'     => 'text',
            'title'    => esc_html__('After how many hours the reservation must be cancel?', 'homey' ),
            'subtitle'     => esc_html__('IE: 1, 2, 4, 5', 'homey'),
            'desc' => esc_html__('Insert the number of hours a pending reservation must be canceled if the customer does not make the payment after the listing author has confirmed the availability.', 'homey'),
            'default' => '24',
            'validate' => 'numeric'
        ),
        array(
            'id'       => 'num_0f_hours_before_checkin_remove_resrv',
            'type'     => 'text',
            'title'    => esc_html__('Before checkin how many hours the reservation must be cancel?', 'homey' ),
            'subtitle'     => esc_html__('IE: 1, 2, 4, 5', 'homey'),
            'desc' => esc_html__('Insert the number of hours a reservation must be canceled if the customer does not want to checkin.', 'homey'),
            'default' => '24',
            'validate' => 'numeric'
        ),

    ),
));

/* -----------------------------------------------
 * Services Fee
 * ----------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Instant Booking', 'homey' ),
    'id'     => 'instance-booking',
    'desc'   => '',
    'subsection'   => true,
    'fields'        => array(
        array(
            'id'       => 'ins_page_title',
            'type'     => 'text',
            'title'    => esc_html__('Page Title', 'homey' ),
            'subtitle' => esc_html__('Enter Instant booking page title.', 'homey'),
            'desc' =>'',
            'default' => $homey_local['ins_page_title'],
        ),
        array(
            'id'       => 'ins_page_subtitle',
            'type'     => 'text',
            'title'    => esc_html__('Subtitle', 'homey' ),
            'subtitle' => esc_html__('Enter Instant booking page subtitle.', 'homey'),
            'desc' =>'',
            'default' => $homey_local['ins_page_subtitle'],
        ),
        array(
            'id'       => 'ins_learnmore',
            'type'     => 'text',
            'title'    => esc_html__('Learn More link', 'homey' ),
            'subtitle' => esc_html__('Enter link for learn more.', 'homey'),
            'desc' =>'',
            'default' => '',
        ),
        
    ),
));


/* -----------------------------------------------
 * Services Fee
 * ----------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Services Fee', 'homey' ),
    'id'     => 'services-fee',
    'desc'   => '',
    'subsection'   => true,
    'fields'        => array(

        array(
            'id'       => 'enable_services_fee',
            'type'     => 'switch',
            'title'    => esc_html__('Services Fee', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the services fee for booking.', 'homey' ),
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'services_fee_type',
            'type'     => 'select',
            'title'    => esc_html__( 'Type', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'required' => array('enable_services_fee', '=', '1'),
            'options'   => array(
                'fixed' => esc_html__('Fixed', 'homey'),
                'percent' => esc_html__('Percent(%)', 'homey'),
            ),
            'default' => 'fixed',
        ),
        array(
            'id'       => 'services_fee',
            'type'     => 'text',
            'title'    => esc_html__('Amount', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__('Enter the service fee amount. Only number', 'homey'),
            'required' => array('enable_services_fee', '=', '1'),
            'default' => '',
        ),
        
    ),
));

/* -----------------------------------------------
 * Host Fee
 * ----------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Host Fee', 'homey' ),
    'id'     => 'host-fee',
    'desc'   => '',
    'subsection'   => true,
    'fields'        => array(
        array(
            'id'       => 'host_fee',
            'type'     => 'text',
            'title'    => esc_html__('Host Fee', 'homey' ),
            'subtitle'     => esc_html__('Host Fee in %', 'homey' ),
            'desc' => esc_html__('Enter the host fee in %. Only number', 'homey'),
            'validate' => 'numeric',
            'default' => '5',
        ),
        
    ),
));


/* -----------------------------------------------
 * Taxes Fee
 * ----------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Taxes', 'homey' ),
    'id'     => 'taxes-fee',
    'desc'   => '',
    'subsection'   => true,
    'fields'        => array(

        array(
            'id'       => 'enable_taxes',
            'type'     => 'switch',
            'title'    => esc_html__( 'Taxes', 'homey' ),
            'desc'     => esc_html__( 'Enable/Disable the taxes for booking.', 'homey' ),
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'tax_type',
            'type'     => 'select',
            'title'    => esc_html__( 'Type', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'required' => array('enable_taxes', '=', '1'),
            'options'   => array(
                'global_tax' => esc_html__('Global tax (all listings will have same tax percentage as set in below field)', 'homey'),
                'single_tax' => esc_html__('Single listing tax (each listing will have a tax field where to add different tax percentage
according to the law in their country)', 'homey'),
            ),
            'default' => 'global_tax',
        ),
        array(
            'id'       => 'taxes_percent',
            'type'     => 'text',
            'title'    => esc_html__('Tax fees', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__('Enter the tax fees in percentage. Only number', 'homey'),
            'required' => array('tax_type', '=', 'global_tax'),
            'default' => '',
            'validate' => 'numeric',
        )

    ),
));

/* -----------------------------------------------
 * Taxes Fee
 * ----------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Show/Hide Booking Form Fields', 'homey' ),
    'id'     => 'showhide-booking-form-fields',
    'desc'   => '',
    'subsection'   => true,
    'fields'        => array(

        array(
            'id'       => 'booking_hide_fields',
            'type'     => 'checkbox',
            'title'    => esc_html__( 'Hide Fields', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Choose which fields you want to hide for booking form', 'homey'),
            'options'  => array(
                'guests' => esc_html__('Guests', 'homey'),
                'children' => esc_html__('Children', 'homey'),
            ),
            'default' => array(
                'guests' => '0',
                'children' => '0',

            )
        ),

        array(
            'id'       => 'booking_detail_hide_fields',
            'type'     => 'checkbox',
            'title'    => esc_html__( 'Hide Fields', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Choose which fields you want to hide for booking detail page', 'homey'),
            'options'  => array(
                'renter_information_on_detail' => esc_html__('Renter information on detail', 'homey')
            ),
            'default' => array(
                'renter_information_on_detail' => '0',

            )
        ),

    ),
));

/* -----------------------------------------------
 * Wallet
 * ----------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Wallet Settings', 'homey' ),
    'id'     => 'wallet-settigns',
    'desc'   => '',
    'subsection'   => false,
    'fields'        => array(
        array(
            'id'       => 'enable_wallet',
            'type'     => 'switch',
            'title'    => esc_html__( 'Wallet System', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable wallet system', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enable', 'homey' ),
            'off'      => esc_html__( 'Disable', 'homey' ),
        ),
        array(
            'id'       => 'minimum_payout_amount',
            'type'     => 'text',
            'title'    => esc_html__('Minimum Payout Amount', 'homey' ),
            'subtitle'     => esc_html__('Enter how much minimum amount required for host for payout.', 'homey' ),
            'desc' => '',
            'validate' => 'numeric',
            'default' => '100',
        ),

        array(
            'id'       => 'payout_labels_section-start',
            'type'     => 'section',
            'title'    => '',
            'subtitle' => '',
            'indent'   => true,
        ),
        array(
            'id'       => 'payout_pending_label',
            'type'     => 'text',
            'title'    => esc_html__( 'Pending', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Pending'
        ),

        array(
            'id'       => 'payout_completed_label',
            'type'     => 'text',
            'title'    => esc_html__( 'Completed', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Completed'
        ),

        array(
            'id'       => 'payout_inprogress_label',
            'type'     => 'text',
            'title'    => esc_html__( 'In Progress', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'In Progress'
        ),
        array(
            'id'       => 'payout_cancel_label',
            'type'     => 'text',
            'title'    => esc_html__( 'Cancelled', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default' => 'Cancelled'
        ),

        array(
            'id'       => 'payout_labels_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),
    ),
));

/* **********************************************************************
 * Advanced Search
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Search', 'homey' ),
    'id'     => 'search-homey',
    'desc'   => '',
    'icon'   => 'el-icon-cog el-icon-small',
    'fields'        => array(
        array(
            'id'       => 'enable_search',
            'type'     => 'switch',
            'title'    => esc_html__( 'Search.', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__('Enable/Disable the search system', 'homey'),
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),

        array(
            'id'       => 'search_width',
            'type'     => 'select',
            'title'    => esc_html__( 'Layout', 'homey' ),
            'subtitle' => '',
            'options'   => array(
                'container' => esc_html__( 'Boxed', 'homey' ),
                'container-fluid'   => esc_html__( 'Full Width', 'homey' )
            ),
            'desc'     => esc_html__( 'Select the search layout', 'homey' ),
            'default'  => 'container'// 1 = on | 0 = off
        ),
        array(
            'id'       => 'search_position',
            'type'     => 'select',
            'title'    => esc_html__( 'Search Position', 'homey' ),
            'subtitle' => '',
            'required' => array('enable_search', '=', '1'),
            'options'   => array(
                'under_nav' => esc_html__( 'Under Navigation', 'homey' ),
                'under_banner'  => esc_html__( 'Under banner ( Slider, Video, Map etc )', 'homey' )
            ),
            'desc'     => esc_html__('Select the search position', 'homey'),
            'default'  => 'under_nav'
        ),
        array(
            'id'       => 'search_pages',
            'type'     => 'select',
            'title'    => esc_html__( 'Search Pages', 'homey' ),
            'subtitle' => '',
            'required' => array('enable_search', '=', '1'),
            'options'   => array(
                'only_home' => esc_html__( 'Only Homepage', 'homey' ),
                'all_pages' => esc_html__( 'Homepage + Inner Pages', 'homey' ),
                'only_innerpages' => esc_html__( 'Only Inner Pages', 'homey' ),
                'specific_pages' => esc_html__( 'Specific Pages', 'homey' ),
                'only_taxonomy_pages' => esc_html__( 'Only Taxonomy Pages', 'homey' ),
            ),
            'desc'     => esc_html__('Select on what page you want to show search', 'homey'),
            'default'  => 'only_home'
        ),
        array(
            'id'       => 'search_selected_pages',
            'type'     => 'select',
            'multi'    => true,
            'required' => array('search_pages', '=', 'specific_pages'),
            'title'    => __('Select Pages', 'homey'),
            'desc' => __('You can select multiple pages', 'homey'),
            'subtitle'     => '',
            'data' => 'pages',
        ),
        
        array(
            'id'        => 'min_price',
            'type'      => 'textarea',
            'title'     => esc_html__( 'Minimum Prices List for Advanced Search Form', 'homey' ),
            'readonly' => false,
            'default'   => '10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150, 160',
            'desc'  => esc_html__( 'Only provide comma separated numbers. Do not add any decimal points, dashes, spaces or currency symbols.', 'homey' ),
            'validate' => 'comma_numeric'
        ),
        array(
            'id'        => 'max_price',
            'type'      => 'textarea',
            'title'     => esc_html__( 'Maximum Prices List for Advanced Search Form', 'homey' ),
            'readonly' => false,
            'default'   => '50, 100, 125, 150, 160, 200, 250, 300, 400, 500, 600, 700, 800, 900, 1000, 1200',
            'desc'  => esc_html__( 'Only provide comma separated numbers. Do not add any decimal points, dashes, spaces or currency symbols.', 'homey' ),
            'validate' => 'comma_numeric'
        ),
    ),
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Search Fields', 'homey' ),
    'id'     => 'search-fields',
    'desc'   => '',
    'subsection' => true,
    'fields'        => array(
        array(
            'id'      => 'search_visible_fields',
            'type'    => 'sorter',
            'title'   => esc_html__('Search Visible Fields', 'homey'),
            'subtitle'   => esc_html__('It is suggested to use maximum 3 modules', 'homey'),
            'desc'    => esc_html__('Drag and drop fields, to quickly search.', 'homey'),
            'options' => array(
                'enabled'  => array(
                    'location'  => esc_html__('Location', 'homey'),
                    'arrive_depart'    => $homey_local['arrive_depart_label'],
                    'guests'    => $homey_local['guest_label']
                ),
                'disabled' => array(
                    'listing_type'  => $homey_local['sr_listing_type_label'],
                )
            ),
        ),
        array(
            'id'      => 'hourly_search_visible_fields',
            'type'    => 'sorter',
            'title'   => esc_html__('Hourly Search Visible Fields', 'homey'),
            'subtitle'   => esc_html__('It is suggested to use maximum 4 modules', 'homey'),
            'desc'    => esc_html__('Drag and drop fields, to quickly search.', 'homey'),
            'options' => array(
                'enabled'  => array(
                    'location'  => esc_html__('Location', 'homey'),
                    'arrive'    => esc_html__('Arrive', 'homey'),
                    'start_end_hours'    => esc_html__('Start & End', 'homey'),
                    'guests'    => $homey_local['guest_label']
                ),
                'disabled' => array(
                    'listing_type'  => $homey_local['sr_listing_type_label'],
                )
            ),
        ),
        array(
            'id'       => 'location_field',
            'type'     => 'select',
            'title'    => __('Location Field', 'homey'),
            'subtitle' => __('What location field should search from?', 'homey'),
            'options'  => array(
                'geo_location' => esc_html__('Geo Location', 'homey'),
                'keyword' => esc_html__('Title and Content', 'homey'),
                'country' => esc_html__('Country', 'homey'),
                'state' => esc_html__('State', 'homey'),
                'city' => esc_html__('City', 'homey'),
                'area' => esc_html__('Area', 'homey'),
            ),
            'default' => 'city'
        ),
        array(
            'id'       => 'enable_radius',
            'type'     => 'switch',
            'title'    => esc_html__( 'Use Radius?', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__('Enable/Disable radius for searches', 'homey'),
            'default'  => 0,
            'required' => array('location_field', '=', 'geo_location'),
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'show_radius',
            'type'     => 'switch',
            'title'    => esc_html__( 'Show Radius?', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__('Want to show Radius filter to User', 'homey'),
            'default'  => 1,
            'required' => array(),
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),

        array(
            'id' => 'default_radius',
            'type' => 'slider',
            'title' => __('Default Radius', 'homey'),
            'subtitle' => __('Choose default radius', 'homey'),
            'desc' => '',
            "default" => 30,
            "min" => 0,
            "step" => 1,
            "max" => 100,
            'required' => array('location_field', '=', 'geo_location'),
            'display_value' => ''
        ),
        array(
            'id'       => 'radius_unit',
            'type'     => 'select',
            'title'    => __('Radius Unit', 'homey'),
            'description' => '',
            'options'  => array(
                'km' => 'km',
                'mi' => 'mi'
            ),
            'default' => 'km',
            'required' => array('location_field', '=', 'geo_location')
        ),

        array(
            'id'       => 'advanced_filter',
            'type'     => 'switch',
            'title'    => esc_html__( 'Advanced Filters', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__('Enable/Disable the advanced filters on the search system', 'homey'),
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'search_hide_fields',
            'type'     => 'checkbox',
            'title'    => esc_html__( 'Hide Fields', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Choose which fields you want to hide in advanced search area', 'homey'),
            'options'  => array(
                'bedrooms' => $homey_local['search_bedrooms'],
                'rooms' => $homey_local['search_rooms'],
                'room_type' => $homey_local['sr_room_type_label'],
                'search_price' => $homey_local['search_price'],
                'search_amenities' => $homey_local['search_amenities'],
                'search_facilities' => $homey_local['search_facilities'],
                'adults' => esc_html__('Adults', 'homey'),
                'children' => esc_html__('Children', 'homey'),
                'pets' => esc_html__('Pets', 'homey'),
            ),
            'default' => array(
                'bedrooms' => '0',
                'rooms' => '0',
                'room_type' => '0',
                'search_price' => '0',
                'search_amenities' => '0',
                'search_facilities' => '0',
                'adults' => '0',
                'children' => '0',
                'pets' => '0',
                
            ),
            'required' => array('advanced_filter', '=', '1'),
        ),
        array(
            'id'       => 'beds_baths_rooms_search',
            'type'     => 'select',
            'title'    => esc_html__( 'Guest, Bedrooms, Rooms', 'homey' ),
            'subtitle'    => esc_html__( 'Search criteria for guest, bedrooms and rooms', 'homey' ),
            'desc'     => '',
            'options'  => array(
                'equal' => esc_html__('Equal', 'homey'),
                'greater' => esc_html__('Greater than or equal to', 'homey'),
                'lessthen' => esc_html__('Less than or equal to', 'homey')
            ),
            'default' => 'equal'
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Search Results Page', 'homey' ),
    'id'     => 'search-result',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'search_result_page',
            'type'     => 'select',
            'title'    => esc_html__('Layout', 'homey'),
            'description' => '',
            'options'  => array(
                'normal_page' => 'Normal Page',
                'half_map' => 'Half Map'
            ),
            'default' => 'normal_page',
            'desc'    => esc_html__('Select the layout for the search results page', 'homey'),
        ),
        
        array(
            'id'       => 'search_posts_layout',
            'type'     => 'select',
            'title'    => esc_html__('Listings Layout', 'homey'),
            'desc' => esc_html__('Select the listings layout for the search results page.', 'homey'),
            'options'  => array(
                'list' => 'List View',
                'grid' => 'Grid View',
                'card' => 'Card View',
            ),
            'default' => 'list'
        ),

        array(
            'id'       => 'search_default_order',
            'type'     => 'select',
            'title'    => esc_html__('Default Order', 'homey'),
            'desc' => esc_html__('Select the search results page listings default display order.', 'homey'),
            'options'  => array(
                'd_date' => esc_html__( 'Date New to Old', 'homey' ),
                'a_date' => esc_html__( 'Date Old to New', 'homey' ),
                'd_price' => esc_html__( 'Price (High to Low)', 'homey' ),
                'a_price' => esc_html__( 'Price (Low to High)', 'homey' ),
                'd_rating' => esc_html__( 'Rating', 'homey' ),
                'featured_top' => esc_html__( 'Show Featured Listings on Top', 'homey' ),
            ),
            'default' => 'd_date'
        ),

        array(
            'id'       => 'search_num_posts',
            'type'     => 'text',
            'title'    => esc_html__('Number of Listings', 'homey'),
            'subtitle' => '',
            'desc'     => esc_html__('Enter the number of listings to show on the search results page', 'homey'),
            'default'  => '9',
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Contact Forms', 'homey' ),
    'id'     => 'homey-contact-forms',
    'desc'   => '',
    'fields' => array(
        array(
            'id'       => 'form_type',
            'type'     => 'select',
            'title'    => esc_html__('Form Type', 'homey'),
            'subtitle' => esc_html__('Select which forms you want to use.', 'homey'),
            'options'  => array(
                'custom_form' => 'Homey Custom Forms',
                'contact_form_7_gravity_form' => 'Contact Form 7 or Gravity Form',
            ),
            'default' => 'custom_form',
        ),
        
        array(
            'id'       => 'single_listing_host_contact',
            'type'     => 'textarea',
            'required' => array( 'form_type', '!=', 'custom_form' ),
            'title'    => esc_html__( 'Listing Contact Form', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enter shortcode for contact form on listing detail page.', 'homey' ),
            'default'  => ''
        ),

        array(
            'id'       => 'host_profile_contact',
            'type'     => 'textarea',
            'required' => array( 'form_type', '!=', 'custom_form' ),
            'title'    => esc_html__( 'Host Profile Contact Form', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enter shortcode for contact form on host profile page.', 'homey' ),
            'default'  => ''
        ),
    )
));

/* **********************************************************************
 * listing
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Payment Gateways', 'homey' ),
    'id'     => 'payment-gateways',
    'desc'   => '',
    'icon'   => 'el-icon-cog el-icon-small',
    'fields'        => array(

        array(
            'id'       => 'homey_payment_gateways',
            'type'     => 'button_set',
            'title'    => __('Choose Payment gateway type', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            //Must provide key => value pairs for options
            'options' => array(
                'homey_custom_gw' => 'Homey Custom Gateways', 
                'gw_woocommerce' => 'WooCommerce', 
             ), 
            'default' => 'homey_custom_gw'
        ),

        array(
            'id'     => 'woocommerce-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( 'Follow <a target="_blank" href="https://favethemes.zendesk.com/hc/en-us/articles/360045293072">WooCommerce Documentation</a>', 'homey' ), $allowed_html_array),
            'subtitle' => __('"homey-woo-addon" and "woocommerce" plugin required', 'homey'),
            'desc'   => '',
            'required' => array('homey_payment_gateways', '=', 'gw_woocommerce')
        ),

        array(
            'id'       => 'payment_currency',
            'type'     => 'select',
            'title'    => esc_html__('Currency', 'homey'),
            'subtitle' => '',
            'desc'     => esc_html__('Select the currency to use for payments', 'homey'),
            'options'  => array(
                'USD'  => 'USD',
                'EUR'  => 'EUR',
                'AUD'  => 'AUD',
                'ARS'  => 'ARS',
                'AZN'  => 'AZN',
                'BRL'  => 'BRL',
                'CAD'  => 'CAD',
                'CHF'  => 'CHF',
                'COP'  => 'COP',
                'CZK'  => 'CZK',
                'DKK'  => 'DKK',
                'HKD'  => 'HKD',
                'HUF'  => 'HUF',
                'IDR'  => 'IDR',
                'ILS'  => 'ILS',
                'INR'  => 'INR',
                'JMD'  => 'JMD',
                'JPY'  => 'JPY',
                'KOR'  => 'KOR',
                'KSH'  => 'KSH',
                'LKR'  => 'LKR',
                'MYR'  => 'MYR',
                'MXN'  => 'MXN',
                'MUR'  => 'MUR',
                'NGN'  => 'NGN',
                'NOK'  => 'NOK',
                'NZD'  => 'NZD',
                'PEN'  => 'PEN',
                'PHP'  => 'PHP',
                'PLN'  => 'PLN',
                'GBP'  => 'GBP',
                'RUB'  => 'RUB',
                'SGD'  => 'SGD',
                'SEK'  => 'SEK',
                'TWD'  => 'TWD',
                'THB'  => 'THB',
                'TRY'  => 'TRY',
                'VND'  => 'VND',
                'ZAR'  => 'ZAR'
            ),
            'default'  => 'USD',
        ),
        array(
            'id'       => 'make_featured',
            'type'     => 'switch',
            'title'    => esc_html__( 'Make Featured', 'homey' ),
            'desc'     => esc_html__( 'Allow user to pay for featuring their listings', 'homey' ),
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'price_featured_listing',
            'type'     => 'text',
            'title'    => esc_html__('Featured Listing Price', 'homey'),
            'desc' => esc_html__('Enter the price to make a listing featured. Only numbers', 'homey'),
            'required' => array('make_featured', '=', '1'),
            'default'  => '0',
        ),
        array(
            'id'       => 'featured_listing_expire',
            'type'     => 'text',
            'required' => array('make_featured', '=', '1'),
            'title'    => esc_html__('Number of Expire Days', 'homey'),
            'subtitle' => '',
            'desc'     => esc_html__('No of days until a featured listings will expire. It starts from the moment the listing is published on the website. Enter -1 for unlimited', 'homey'),
            'default'  => '30',
        ),
        array(
            'id'       => 'payment_terms_condition',
            'type'     => 'select',
            'data'     => 'pages',
            'title'    => esc_html__( 'Terms & Conditions', 'homey' ),
            'desc' => esc_html__( 'Select the page to use for the terms & conditions', 'homey' ),
            'subtitle'     => '',
        ),
        array(
            'id'       => 'payment_privacy_policy',
            'type'     => 'select',
            'data'     => 'pages',
            'title'    => esc_html__( 'Privacy Policy', 'homey' ),
            'desc' => esc_html__( 'Select the page to use for the privacy policy', 'homey' ),
            'subtitle'     => '',
        ),
    ),
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Paypal Settings', 'homey' ),
    'id'     => 'mem-paypal-settings',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'enable_paypal',
            'type'     => 'switch',
            'title'    => esc_html__( 'Enable Paypal', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'paypal_api',
            'type'     => 'select',
            'title'    => esc_html__('Paypal Api', 'homey'),
            'subtitle' => '',
            'desc'     => esc_html__('Update PayPal, settings according to API type selection', 'homey'),
            'required' => array( 'enable_paypal', '=', '1' ),
            'options'  => array(
                'sandbox'=> 'Sandbox',
                'live'   => 'Live',
            ),
            'default'  => 'sandbox',
        ),
        array(
            'id'       => 'paypal_client_id',
            'type'     => 'text',
            'required' => array( 'enable_paypal', '=', '1' ),
            'title'    => esc_html__('Paypal Client ID', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => '',
        ),
        array(
            'id'       => 'paypal_client_secret_key',
            'type'     => 'text',
            'required' => array( 'enable_paypal', '=', '1' ),
            'title'    => esc_html__('Paypal Client Secret Key', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => '',
        ),
        array(
            'id'       => 'paypal_receiving_email',
            'type'     => 'text',
            'required' => array( 'enable_paypal', '=', '1' ),
            'title'    => esc_html__('Paypal Receiving Email', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => '',
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Stripe Settings', 'homey' ),
    'id'     => 'mem-stripe-settings',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'enable_stripe',
            'type'     => 'switch',
            'title'    => esc_html__( 'Enable Stripe', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'stripe_api',
            'type'     => 'select',
            'title'    => esc_html__('Stripe Api', 'homey'),
            'subtitle' => '',
            'desc'     => esc_html__('Update Stripe, settings according to API type selection', 'homey'),
            'required' => array( 'enable_stripe', '=', '1' ),
            'options'  => array(
                'sandbox'=> 'Sandbox',
                'live'   => 'Live',
            ),
            'default'  => 'sandbox',
        ),
        array(
            'id'       => 'stripe_secret_key',
            'type'     => 'text',
            'required' => array( 'enable_stripe', '=', '1' ),
            'title'    => esc_html__('Stripe Secret Key', 'homey'),
            'desc' => esc_html__('The information is taken from your account at https://dashboard.stripe.com/login', 'homey'),
            'subtitle'     => '',
            'default'  => '',
        ),
        array(
            'id'       => 'stripe_publishable_key',
            'type'     => 'text',
            'required' => array( 'enable_stripe', '=', '1' ),
            'title'    => esc_html__('Stripe Publishable Key', 'homey'),
            'desc' => esc_html__('The information is taken from your account at https://dashboard.stripe.com/login', 'homey'),
            'subtitle'     => '',
            'default'  => '',
        ),

        array(
            'id'       => 'stripe_webhook_secret',
            'type'     => 'text',
            'required' => array( 'enable_stripe', '=', '1' ),
            'title'    => esc_html__('Stripe Webhook Secret Key', 'homey'),
            'desc' => esc_html__('The information is taken from your account at https://dashboard.stripe.com/login', 'homey'),
            'subtitle'     => '',
            'default'  => '',
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Off-site Payment', 'homey' ),
    'id'     => 'mem-off-site-settings',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'off-site-payment',
            'type'     => 'switch',
            'title'    => esc_html__( 'Off-site Payments', 'homey' ),
            'desc'     => esc_html__( 'Allow host to get payments directly', 'homey' ),
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'offsite-payment-instruction',
            'type'     => 'textarea',
            'title'    => esc_html__('Payment Instruction', 'homey'),
            'desc' => '',
            'subtitle'     => esc_html__('Enter Instructions for off site payment', 'homey'),
            'default'  => 'Please make payment using giving method and click on mark as paid button.',
        ),
    )
));


/* **********************************************************************
 * Typography
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Typography', 'homey' ),
    'id'     => 'homey-typography',
    'desc'   => '',
    'icon'   => 'el-icon-font el-icon-small',
    'fields'  => array(
        array(
            'id'          => 'typo-body',
            'type'        => 'typography',
            'title'       => esc_html__('Body', 'homey'),
            'google'      => true,
            'font-family' => true,
            'font-backup' => false,
            'text-align'  => false,
            'color'  => false,
            'text-transform' => true,
            'font-style' => false,
            'units'       =>'px',
            'subtitle'    => esc_html__('Select your custom font options for your main body font.', 'homey'),
            'all_styles'  => true,
            'default'     => array(
                'font-weight'  => '400',
                'font-family' => 'Quicksand',
                'google'      => true,
                'font-size'   => '14px',
                'line-height' => '24px',
                'text-transform' => 'none'
            ),
        ),

        array(
            'id'          => 'typo-menu',
            'type'        => 'typography',
            'title'       => esc_html__('Menu', 'homey'),
            'google'      => true,
            'font-family' => true,
            'font-backup' => false,
            'text-align'  => false,
            'color'  => false,
            'text-transform' => true,
            'font-style' => false,
            'units'       =>'px',
            'subtitle'    => esc_html__('Select your custom font options for your main meny.', 'homey'),
            'all_styles'  => true,
            'default'     => array(
                'font-weight'  => '700',
                'font-family' => 'Quicksand',
                'google'      => true,
                'font-size'   => '14px',
                'line-height' => '80px',
                'text-transform' => 'none'
            ),
        ),

        // Typo Headings 1
        array(
            'id'          => 'typo-headings',
            'type'        => 'typography',
            'title'       => esc_html__('Headings', 'homey'),
            'google'      => true,
            'font-family' => true,
            'font-backup' => false,
            'text-align'  => true,
            'font-size'   => false,
            'line-height'   => false,
            'text-transform' => true,
            'color' => false,
            'font-style' => false,
            'units'       =>'px',
            'subtitle'    => esc_html__('Select your custom font options for headings ( h1, h2, h3, h3 etc ).', 'homey'),
            'default'     => array(
                'font-family' => 'Quicksand',
                'font-weight'  => '700',
                'google'      => true,
                'text-transform' => 'inherit',
                'text-align' => 'inherit'
            ),
        ),
    ),
));

/* **********************************************************************
 * Styling
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Styling', 'homey' ),
    'id'               => 'homey-styling',
    'desc'             => '',
    'customizer_width' => '',
    'icon'             => 'el-icon-brush el-icon-small'
) );

/* Body
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Body', 'homey' ),
    'id'     => 'styling-body',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'body_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Background Color', 'homey' ),
            'desc' => esc_html__('Select body background color', 'homey'),
            'default'  => '#f7f8f9',
            'transparent' => false,
        ),
        array(
            'id'       => 'text_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Text Color', 'homey' ),
            'desc' => esc_html__('Select the text color', 'homey'),
            'default'  => '#4f5962',
            'transparent' => false,
        )
    )
));

/* General Colors
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Primary & Secondary', 'homey' ),
    'id'     => 'styling-primary-sec',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'primary_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Primary Color', 'homey' ),
            'desc' => esc_html__( 'Select the website primary color.', 'homey' ),
            'default'  => '#f15e75',
            'transparent' => false
        ),
        array(
            'id'       => 'primary_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Primary Hover Color', 'homey' ),
            'desc' => esc_html__( 'Select the website primary hover color.', 'homey' ),
            'default'  => '#f58d9d'
        ),

        array(
            'id'       => 'secondary_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Secondary Color', 'homey' ),
            'desc' => esc_html__( 'Select the website secondary color.', 'homey' ),
            'default'  => '#54c4d9',
            'transparent' => false
        ),
        array(
            'id'       => 'secondary_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Secondary Hover Color', 'homey' ),
            'desc' => esc_html__( 'Select the website secondary hover color.', 'homey' ),
            'default'  => '#7ed2e2'
        ),
    )
));

/* Button Colors
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Button Colors', 'homey' ),
    'id'     => 'styling-button',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'primary_btn_bg_color',
            'type'     => 'link_color',
            'title'    => esc_html__( 'Primary Button', 'homey' ),
            'desc' => esc_html__( 'Select the primary button background color', 'homey' ),
            'subtitle'     => '',
            'default'  => array(
                'regular' => '#f15e75',
                'hover'   => '#f58d9d',
                'active'  => '#f58d9d',
            )
        ),
        array(
            'id'       => 'primary_btn_border_color',
            'type'     => 'link_color',
            'title'    => esc_html__( 'Primary Button', 'homey' ),
            'desc' => esc_html__( 'Select the primary button border color', 'homey' ),
            'subtitle'     => '',
            'default'  => array(
                'regular' => '#f15e75',
                'hover'   => '#f58d9d',
                'active'  => '#f58d9d',
            )
        ),
        array(
            'id'       => 'primary_btn_color',
            'type'     => 'link_color',
            'title'    => esc_html__( 'Primary Button', 'homey' ),
            'desc' => esc_html__( 'Select the primary button text color', 'homey' ),
            'subtitle'     => '',
            'default'  => array(
                'regular' => '#ffffff',
                'hover'   => '#ffffff',
                'active'  => '#ffffff',
            )
        ),

        array(
            'id'       => 'secondary_btn_bg_color',
            'type'     => 'link_color',
            'title'    => esc_html__( 'Secondary Button', 'homey' ),
            'desc' => esc_html__( 'Select the secondary button background color', 'homey' ),
            'subtitle'     => '',
            'default'  => array(
                'regular' => '#54c4d9',
                'hover'   => '#7ed2e2',
                'active'  => '#7ed2e2',
            )
        ),
        array(
            'id'       => 'secondary_btn_border_color',
            'type'     => 'link_color',
            'title'    => esc_html__( 'Secondary Button', 'homey' ),
            'desc' => esc_html__( 'Select the secondary button border color', 'homey' ),
            'subtitle'     => '',
            'default'  => array(
                'regular' => '#54c4d9',
                'hover'   => '#7ed2e2',
                'active'  => '#7ed2e2',
            )
        ),
        array(
            'id'       => 'secondary_btn_color',
            'type'     => 'link_color',
            'title'    => esc_html__( 'Secondary Button', 'homey' ),
            'desc' => esc_html__( 'Select the secondary button text color', 'homey' ),
            'subtitle'     => '',
            'default'  => array(
                'regular' => '#ffffff',
                'hover'   => '#ffffff',
                'active'  => '#ffffff',
            )
        ),
    )
));


/* Headers
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Headers', 'homey' ),
    'id'     => 'styling-headers',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'header_bg',
            'type'     => 'color',
            'title'    => esc_html__( 'Background Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false

        ),
        array(
            'id'       => 'header_border',
            'type'     => 'border',
            'all'      => false,
            'title'    => esc_html__( 'Border Color', 'homey' ),
            'subtitle' => '',
            'desc'     => '',
            'top' => false,
            'right' => false,
            'left' => false,
            'default'  => array(
                'border-color'  => '#d8dce1',
                'border-style'  => 'solid',
                'border-top'    => '0px',
                'border-right'  => '0px',
                'border-bottom' => '1px',
                'border-left'   => '0px'
            )
        ),

        array(
            'id'       => 'mainmenu_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Menu Text Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#4f5962',
            'transparent' => false

        ),
        array(
            'id'       => 'mainmenu_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Menu Text Color on Hover', 'homey' ),
            'subtitle' => '',
            'default'  => '#f15e75',
            'transparent' => false

        ),

        array(
            'id'       => 'mainmenu_dropdown_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Menu Text Dropdown Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#4f5962',
            'transparent' => false
        ),
        array(
            'id'       => 'mainmenu_dropdown_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Menu Text Dropdown Color Hover', 'homey' ),
            'subtitle' => '',
            'default'  => '#f15e75',
            'transparent' => false
        ),

        array(
            'id'       => 'mainmenu_dropdown_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Menu Dropdown Background Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false
        ),

        array(
            'id'       => 'mainmenu_dropdown_border',
            'type'     => 'border',
            'all'      => false,
            'title'    => esc_html__( 'Menu dropdown Border', 'homey' ),
            'subtitle' => '',
            'desc'     => '',
            'top' => false,
            'right' => false,
            'left' => false,
            'default'  => array(
                'border-color'  => '#d8dce1',
                'border-style'  => 'solid',
                'border-top'    => '0px',
                'border-right'  => '0px',
                'border-bottom' => '1px',
                'border-left'   => '0px'
            )
        ),

        array(
            'id'       => 'mainmenu_trigger_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Menu Button', 'homey' ),
            'desc' => esc_html__( 'Menu button color. It only works for header v4', 'homey' ),
            'default'  => '#4f5962',
            'transparent' => false

        ),

        /*------------------------------------------------------------------
           Login & Register
        ------------------------------------------------------------------*/
        array(
            'id'       => 'header_login_section-start',
            'type'     => 'section',
            //'required' => array('styling_headers_type', '=', 'header-2'),
            'title'    => esc_html__( 'Login & Register', 'homey' ),
            'subtitle' => '',
            'indent'   => true,
        ),
        array(
            'id'       => 'login_regis_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Login/Register Text Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#4f5962',
            'transparent' => false

        ),
        array(
            'id'       => 'login_regis_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Login/Register Text Color on Hover', 'homey' ),
            'subtitle' => '',
            'default'  => '#f15e75',
            'transparent' => false

        ),
        array(
            'id'       => 'user_menu_color',
            'type'     => 'color',
            'title'    => esc_html__( 'User Menu Text Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#4f5962',
            'transparent' => false

        ),
        array(
            'id'       => 'user_menu_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'User Menu Text Color on Hover', 'homey' ),
            'subtitle' => '',
            'default'  => '#4f5962',
            'transparent' => false

        ),
        array(
            'id'       => 'user_menu_color_bg_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'User Menu Color Hover background', 'homey' ),
            'subtitle' => '',
            'default'  => '#54c4d9',
            'transparent' => false

        ),
        array(
            'id'       => 'user_menu_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'User Menu Background Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false

        ),
        array(
            'id'       => 'header_login_section-end',
            'type'     => 'section',
            //'required' => array('styling_headers_type', '=', 'header-2'),
            'indent'   => false,
        ),

        /*------------------------------------------------------------------
            Become Host Button
        ------------------------------------------------------------------*/
        array(
            'id'       => 'header_host_section-start',
            'type'     => 'section',
            'title'    => esc_html__( 'Become a Host', 'homey' ),
            'subtitle' => '',
            'indent'   => true,
        ),
        array(
            'id'       => 'become_host_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Button Text Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#f15e75',
            'transparent' => false

        ),
        array(
            'id'       => 'become_host_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Button Text Color on Hover', 'homey' ),
            'subtitle' => '',
            'default'  => '#f15e75',
            'transparent' => false

        ),
        array(
            'id'       => 'become_host_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Button Background Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false

        ),
        array(
            'id'       => 'become_host_bg_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Button Background Color on Hover', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false

        ),
        array(
            'id'       => 'become_host_border_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Border Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#f15e75',
            'transparent' => false

        ),
        array(
            'id'       => 'become_host_border_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Border Color Hover', 'homey' ),
            'subtitle' => '',
            'default'  => '#f15e75',
            'transparent' => false

        ),
        array(
            'id'       => 'header_host_section-end',
            'type'     => 'section',
            'indent'   => false,
        ),

        /*------------------------------------------------------------------
            Headers Top Area
        ------------------------------------------------------------------*/
        array(
            'id'       => 'header_section-start',
            'type'     => 'section',
            //'required' => array('styling_headers_type', '=', 'header-2'),
            'title'    => esc_html__( 'Header Top Area (v2 and v3)', 'homey' ),
            'subtitle' => '',
            'indent'   => true,
        ),
        array(
            'id'       => 'header_top_bg',
            'type'     => 'color',
            'title'    => esc_html__( 'Background Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false

        ),
        array(
            'id'       => 'header_top_border',
            'type'     => 'border',
            'all'      => false,
            'title'    => esc_html__( 'Border Color', 'homey' ),
            'subtitle' => '',
            'desc'     => '',
            'top' => false,
            'right' => false,
            'left' => false,
            'default'  => array(
                'border-color'  => '#d8dce1',
                'border-style'  => 'solid',
                'border-top'    => '0px',
                'border-right'  => '0px',
                'border-bottom' => '1px',
                'border-left'   => '0px'
            )
        ),
        array(
            'id'       => 'header_top_social_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Social Icons Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#4f5962',
            'transparent' => false

        ),
        array(
            'id'       => 'header_section-end',
            'type'     => 'section',
            //'required' => array('styling_headers_type', '=', 'header-2'),
            'indent'   => false,
        ),
    )
));

/* Transparent Headers
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Transparent Header', 'homey' ),
    'id'     => 'styling-trans-headers',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'trans_menu_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Menu Text Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false

        ),
        array(
            'id'       => 'trans_menu_color_hover',
            'type'     => 'color',
            'title'    => esc_html__( 'Menu Text Color on Hover', 'homey' ),
            'subtitle' => '',
            'default'  => '#f15e75',
            'transparent' => false

        )
    )
));

/* Headers
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Search', 'homey' ),
    'id'     => 'styling-search',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'search_bg',
            'type'     => 'color',
            'title'    => esc_html__( 'Background Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false

        )
    )
));

/* Top Bar
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Top Bar', 'homey' ),
    'id'     => 'styling-top-bar',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'top_bar_bg',
            'type'     => 'color',
            'title'    => esc_html__( 'Background Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#4f5962',
            'transparent' => true
        ),
        array(
            'id'       => 'top_bar_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Text Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false
        ),
        array(
            'id'       => 'top_bar_color_hover',
            'type'     => 'color_rgba',
            'title'    => esc_html__( 'Text Color on Hover', 'homey' ),
            'subtitle' => '',
            'default'  => array(
                'color' => '#ffffff',
                'alpha' => '.80',
                'rgba'  => ''
            )
        ),
    )
));

/* Featured Label
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Featured Label', 'homey' ),
    'id'     => 'styling-featured-label',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'featured_label_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Background Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#77c720',
            'transparent' => true
        ),
        array(
            'id'       => 'featured_label_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Text Color', 'homey' ),
            'subtitle' => '',
            'default'  => '#ffffff',
            'transparent' => false
        )
    )
));


/* Footer
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Footer', 'homey' ),
    'id'     => 'styling-footer',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'footer_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Background Color', 'homey' ),
            'desc' => esc_html__('Select the footer background color', 'homey'),
            'default'  => '#4f5962',
            'transparent' => false,
        ),
        array(
            'id'       => 'footer_bottom_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Footer Bottom Background Color', 'homey' ),
            'desc' => esc_html__('Select the footer bottom background color', 'homey'),
            'default'  => '#4a545c',
            'transparent' => false,
        ),
        array(
            'id'       => 'footer_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Text Color', 'homey' ),
            'desc' => esc_html__('Select the footer text color', 'homey'),
            'default'  => '#FFFFFF',
            'transparent' => false
        ),
        array(
            'id'       => 'footer_hover_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Text Hover Color', 'homey' ),
            'desc' => esc_html__('Select the footer text  hover color', 'homey'),
            'default'  => '00aeef',
            'transparent' => false
        ),

    )
));

/* **********************************************************************
 * Add New Listing
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Add New Listing', 'homey' ),
    'id'     => 'add-listing-page',
    'desc'   => '',
    'icon'   => 'el-icon-cog el-icon-small',
    'fields'        => array(
        array(
            'id'       => 'listings_admin_approved',
            'type'     => 'switch',
            'title'    => esc_html__('Submited Listings Should be Approved by Admin?', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'edit_listings_admin_approved',
            'type'     => 'switch',
            'title'    => esc_html__('Edit Listings Should be Approved by Admin?', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => 0,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'      => 'listing_form_sections',
            'type'    => 'sorter',
            'title'   => 'Submission Form Layout Manager',
            'desc'    => 'Drag and drop layout manager, to quickly organize your listing submission form layout.',
            'options' => array(
                'enabled'  => array(
                    'information'         => esc_html__('Information', 'homey'),
                    'pricing'  => esc_html__('Pricing', 'homey'),
                    'media'      => esc_html__('Media', 'homey'),
                    'features'      => esc_html__('Features', 'homey'),
                    'location'      => esc_html__('Location', 'homey'),
                    'bedrooms'  => esc_html__('Bedrooms', 'homey'),
                    'term_rules'  => esc_html__('Terms & Rules', 'homey'),
                ),
                'disabled' => array(
                    'services'  => esc_html__('Services', 'homey'),
                )
            ),
        ),

        array(
            'id'       => 'upload_image_min_dimensions',
            'type'     => 'text',
            'title'    => esc_html__( 'Gallery Images Minumum Dimensions', 'homey' ),
            'desc' => esc_html__('Enter default minimum dimensions for gallery images uploads (e.g: 1440x900).', 'homey'),
            'default'  => '1440x900',
        ),
        array(
            'id'       => 'add_location_lat',
            'type'     => 'text',
            'title'    => esc_html__( 'Latitudes', 'homey' ),
            'desc' => esc_html__('Enter default latitudes for add new listing map.', 'homey'),
            'default'  => '25.761681',
        ),

        array(
            'id'       => 'add_location_lat',
            'type'     => 'text',
            'title'    => esc_html__( 'Latitudes', 'homey' ),
            'desc' => esc_html__('Enter default latitudes for add new listing map.', 'homey'),
            'default'  => '25.761681',
        ),
        array(
            'id'       => 'add_location_long',
            'type'     => 'text',
            'title'    => esc_html__( 'Longitudes', 'homey' ),
            'desc' => esc_html__('Enter default longitudes for add new listing map.', 'homey'),
            'default'  => '-80.191788',
        ),

        array(
            'id'        => 'openning_hours_list',
            'type'      => 'textarea',
            'title'     => esc_html__( 'Opening Hours list', 'homey' ),
            'readonly' => false,
            'default'   => '8:00 AM, 8:30 AM, 9:00 AM, 9:30 AM, 10:00 AM, 10:30 AM, 11:00 AM, 11:30 AM, 12:00 PM, 12:30 PM, 1:00 PM, 1:30 PM, 2:00 PM, 2:30 PM, 3:00 PM, 3:30 PM, 4:00 PM, 4:30 PM, 5:00 PM, 5:30 PM, 6:00 PM, 6:30 PM, 7:00 PM, 7:30 PM, 8:00 PM, 8:30 PM, 9:00 PM',
            'desc'  => esc_html__( 'Only provide hours comma separated. Do not add decimal points, dashes or spaces', 'homey' )
        ),

        array(
            'id'        => 'checkin_after_before',
            'type'      => 'textarea',
            'title'     => esc_html__( 'Check-in After & Check-out Before', 'homey' ),
            'readonly' => false,
            'default'   => '8:00 AM, 8:30 AM, 9:00 AM, 9:30 AM, 10:00 AM, 10:30 AM, 11:00 AM, 11:30 AM, 12:00 PM, 12:30 PM, 1:00 PM, 1:30 PM, 2:00 PM, 2:30 PM, 3:00 PM, 3:30 PM, 4:00 PM, 4:30 PM, 5:00 PM, 5:30 PM, 6:00 PM, 6:30 PM, 7:00 PM, 7:30 PM, 8:00 PM, 8:30 PM, 9:00 PM',
            'desc'  => esc_html__( 'Only provide hours comma separated. Do not add decimal points, dashes or spaces', 'homey' )
        ),
    )
));

$submit_form_fields = array(
    'room_type' => $homey_local['room_type'],
    'listing_title' => $homey_local['listing_title'],
    'description' => $homey_local['listing_des'],
    'listing_bedrooms' => $homey_local['no_of_bedrooms'],
    'guests' => $homey_local['no_of_guests'],
    'beds' => $homey_local['no_of_beds'],
    'baths' => $homey_local['no_of_bathrooms'],
    'listing_rooms' => $homey_local['no_of_rooms'],
    'listing_type' => $homey_local['listing_type'],
    'listing_size' => $homey_local['listing_size'],
    'listing_size_unit' => $homey_local['listing_size_unit'],
    'affiliate_booking_link' => esc_html__('Affiliate Booking Link', 'homey'),
    'instant_booking' => $homey_local['ins_booking_label'],
    'night_price' => $homey_local['nightly_label'],
    'price_postfix' => esc_html__('After Price Label', 'homey'),
    'weekends_price' => $homey_local['weekends_label'],
    'weekends_days' => $homey_local['weekend_days_label'],
    'priceWeek' => $homey_local['weekly7nights'],
    'priceMonthly' => $homey_local['monthly30nights'],
    'allow_additional_guests' => $homey_local['allow_additional_guests'],
    'cleaning_fee' => $homey_local['cleaning_fee'],
    'city_fee' => $homey_local['city_fee'],
    'security_deposit' => $homey_local['security_deposit_label'],
    'tax_rate' => $homey_local['tax_rate_label'],
    'amenities' => $homey_local['amenities'],
    'facilities' => $homey_local['facilities'],
    'listing_address' => $homey_local['address'],
    'aptSuit' => $homey_local['aptSuit'],
    'country' => $homey_local['country'],
    'state' => $homey_local['state'],
    'city' => $homey_local['city'],
    'area' => $homey_local['area'],
    'zipcode' => $homey_local['zipcode'],
    'video_url' => $homey_local['video_heading'],
    'cancel_policy' => $homey_local['cancel_policy'],
    'min_book_days' => $homey_local['min_days_booking'],
    'max_book_days' => $homey_local['max_days_booking'],

    'min_book_weeks' => esc_html__('Minimum weeks of a booking', 'homey'),
    'max_book_weeks' => esc_html__('Maximum weeks of a booking', 'homey'),

    'min_book_months' => esc_html__('Minimum months of a booking', 'homey'),
    'max_book_months' => esc_html__('Maximum months of a booking', 'homey'),

    'checkin_after' => $homey_local['check_in_after'],
    'checkout_before' => $homey_local['check_out_before'],
    'smoking_allowed' => $homey_local['smoking_allowed'],
    'pets_allowed' => $homey_local['pets_allowed'],
    'party_allowed' => $homey_local['party_allowed'],
    'children_allowed' => $homey_local['children_allowed'],
    'additional_rules' => $homey_local['add_rules_info_optional'],

    'section_openning' => $homey_local['openning_hours_label'],
    'section_custom_pricing' => $homey_local['setup_period_prices'],
    'custom_weekend_price' => esc_html__('Weekend Custom Price', 'homey'),
    'extra_prices' => esc_html__('Extra Services Prices', 'homey'),
);

$submit_form_fields = array_merge($submit_form_fields, $custom_fields_array);

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Show/Hide Fields', 'homey' ),
    'id'     => 'listing-showhide',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'add_hide_fields',
            'type'     => 'checkbox',
            'title'    => esc_html__( 'Submit Form Fields', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Choose which fields you want to hide from the add new listing page', 'homey'),
            'options'  => $submit_form_fields,
            'default' => array(
                'section_openning' => '1',
                'section_custom_pricing' => '1',
                'extra_prices' => '1',
                'affiliate_booking_link' => '1',
                'room_type' => '0',
                
            )
        ),
    )
));

$submit_form_required_fields = array(
    'room_type' => $homey_local['room_type'],
    'listing_title' => $homey_local['listing_title'],
    'listing_bedrooms' => $homey_local['no_of_bedrooms'],
    'guests' => $homey_local['no_of_guests'],
    'beds' => $homey_local['no_of_beds'],
    'baths' => $homey_local['no_of_bathrooms'],
    'listing_rooms' => $homey_local['no_of_rooms'],
    'listing_type' => $homey_local['listing_type'],
    'listing_size' => $homey_local['listing_size'],
    'listing_size_unit' => $homey_local['listing_size_unit'],
    'night_price' => $homey_local['nightly_label'],
    'weekends_price' => $homey_local['weekends_label'],
    'weekends_days' => $homey_local['weekend_days_label'],
    'priceWeek' => $homey_local['weekly7nights'],
    'priceMonthly' => $homey_local['monthly30nights'],
    'listing_address' => $homey_local['address'],
    'aptSuit' => $homey_local['aptSuit'],
    'country' => $homey_local['country'],
    'state' => $homey_local['state'],
    'city' => $homey_local['city'],
    'area' => $homey_local['area'],
    'zip' => $homey_local['zipcode'],
    'cancellation_policy' => $homey_local['cancel_policy'],
    'min_book_days' => $homey_local['min_days_booking'],
    'max_book_days' => $homey_local['max_days_booking'],

    'min_book_weeks' => esc_html__('Minimum weeks of a booking', 'homey'),
    'max_book_weeks' => esc_html__('Maximum weeks of a booking', 'homey'),

    'min_book_months' => esc_html__('Minimum months of a booking', 'homey'),
    'max_book_months' => esc_html__('Maximum months of a booking', 'homey'),

    'checkin_after' => $homey_local['check_in_after'],
    'checkout_before' => $homey_local['check_out_before'],
    'start_hour' => esc_html__('Start Hour', 'homey'),
    'end_hour' => esc_html__('End Hour', 'homey'),
);
$submit_form_required_fields = array_merge($submit_form_required_fields, $custom_fields_array);

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Required Fields', 'homey' ),
    'id'     => 'listing-required-fields',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'add_listing_required_fields',
            'type'     => 'checkbox',
            'title'    => esc_html__( 'Required Fields', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Make add listing fields required.', 'homey'),
            'options'  => $submit_form_required_fields,
            'default' => array(
                'listing_title' => '1',
                'night_price' => '1',
                'listing_address' => '1',
                'priceWeek' => '0',
            )
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Submitted / Success', 'homey' ),
    'id'     => 'listing-submitted-success',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'update_cal_section-start',
            'type'     => 'section',
            'title'    => esc_html__( 'Update Calendar', 'homey' ),
            'subtitle' => '',
            'indent'   => true, // Indent all options below until the next 'section' option is set.
        ),

        array(
            'id'       => 'update_cal_title',
            'type'     => 'text',
            'title'    => esc_html__( 'Title', 'homey' ),
            'desc' => esc_html__('Enter the title for the update calendar section', 'homey'),
            'default'  => 'Update Calendar',
            'transparent' => false,
        ),

        array(
            'id'       => 'update_cal_des',
            'type'     => 'textarea',
            'title'    => esc_html__( 'Description', 'homey' ),
            'desc' => esc_html__('Enter a description for update calendar section', 'homey'),
            'default'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut vehicula eget turpis laoreet pulvinar',
            'transparent' => false,
        ),

        array(
            'id'     => 'update_cal_section-end',
            'type'   => 'section',
            'indent' => false, // Indent all options below until the next 'section' option is set.
        ),

        array(
            'id'       => 'custom_prices_section-start',
            'type'     => 'section',
            'title'    => esc_html__( 'Custom Prices', 'homey' ),
            'subtitle' => '',
            'indent'   => true, // Indent all options below until the next 'section' option is set.
        ),

        array(
            'id'       => 'custom_prices_title',
            'type'     => 'text',
            'title'    => esc_html__( 'Title', 'homey' ),
            'desc' => esc_html__('Enter the title for the custom prices section', 'homey'),
            'default'  => 'Setup Custom Prices',
            'transparent' => false,
        ),

        array(
            'id'       => 'custom_prices_des',
            'type'     => 'textarea',
            'title'    => esc_html__( 'Description', 'homey' ),
            'desc' => esc_html__('Enter a description for the custom prices section', 'homey'),
            'default'  => 'Set up custom prices for selected periods.',
            'transparent' => false,
        ),

        array(
            'id'     => 'custom_prices_section-end',
            'type'   => 'section',
            'indent' => false, // Indent all options below until the next 'section' option is set.
        ),

        array(
            'id'       => 'update_featured_section-start',
            'type'     => 'section',
            'title'    => esc_html__( 'Upgrade to featured', 'homey' ),
            'subtitle' => '',
            'indent'   => true, // Indent all options below until the next 'section' option is set.
        ),

        array(
            'id'       => 'update_featured_title',
            'type'     => 'text',
            'title'    => esc_html__( 'Title', 'homey' ),
            'desc' => esc_html__('Enter the title for the upgrade to featured section', 'homey'),
            'default'  => 'Upgrade to featured',
            'transparent' => false,
        ),

        array(
            'id'       => 'update_featured_des',
            'type'     => 'textarea',
            'title'    => esc_html__( 'Description', 'homey' ),
            'desc' => esc_html__('Enter a description for the upgrade to featured section', 'homey'),
            'default'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut vehicula eget turpis laoreet pulvinar',
            'transparent' => false,
        ),

        array(
            'id'     => 'update_featured_section-end',
            'type'   => 'section',
            'indent' => false, // Indent all options below until the next 'section' option is set.
        ),
    )
));

/* **********************************************************************
 * Listing detail page
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Listing Detail Page', 'homey' ),
    'id'     => 'listing-details-page',
    'desc'   => '',
    'icon'   => 'el-icon-cog el-icon-small',
    'fields'        => array(
        array(
            'id'       => 'detail_layout',
            'type'     => 'select',
            'title'    => esc_html__('Layout', 'homey'),
            'desc' => esc_html__('Select the layout for the listing detail page', 'homey'),
            'subtitle'     => '',
            'options'  => array(
                'v1'   => esc_html__( 'Version 1', 'homey' ),
                'v2'   => esc_html__( 'Version 2', 'homey' ),
                'v3'   => esc_html__( 'Version 3', 'homey' ),
                'v4'   => esc_html__( 'Version 4', 'homey' ),
            ),
            'default'  => 'v1',
        ),

        
        array(
            'id'       => 'listing-detail-nav',
            'type'     => 'switch',
            'title'    => esc_html__('listing Detail Nav', 'homey'),
            'subtitle' => esc_html__('listing detail page sticky navigation', 'homey'),
            'desc'     => '',
            'default'  => 0,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),

        array(
            'id'       => 'what_to_show',
            'type'     => 'select',
            'title'    => esc_html__('What to display in the sidebar?', 'homey'),
            'desc' => esc_html__('Select what to display in the sidebar of listing detail page', 'homey'),
            'subtitle'     => '',
            'options'  => array(
                'booking_form'   => esc_html__( 'Booking form', 'homey' ),
                'contact_form'   => esc_html__( 'Contact Form', 'homey' ),
                'contact_form_to_guest' => esc_html__('Contact Form To Guest and Booking To User', 'homey'),
            ),
            'default'  => 'booking_form',
        ),
        array(
            'id'       => 'print_button',
            'type'     => 'switch',
            'title'    => esc_html__( 'Print Listing', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__( 'Enable/Disable the print listing button', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'detail_favorite',
            'type'     => 'switch',
            'title'    => esc_html__( 'Favorite Listing', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__( 'Enable/Disable the favorite listing button', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'detail_share',
            'type'     => 'switch',
            'title'    => esc_html__( 'Share buttons', 'homey' ),
            'subtitle'     => '',
            'desc' => esc_html__( 'Enable/Disable the share button', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),

        array(
            'id'       => 'detail_contact_form',
            'type'     => 'switch',
            'title'    => esc_html__( 'Contact Host', 'homey' ),
            'desc' => esc_html__( 'Enable/Disable the contact host button.', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        )
    ),
));

/* Sections
----------------------------------------------------------------*/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Layout Manager', 'homey' ),
    'id'     => 'listing-section',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'      => 'listing_blocks',
            'type'    => 'sorter',
            'title'   => 'Listing Layout Manager',
            'desc'    => 'Drag and drop layout manager, to quickly organize your listing layout contents.',
            'options' => array(
                'enabled'  => array(
                    'about'           => esc_html__('About', 'homey'),
                    'details'         => esc_html__('Details', 'homey'),
                    'gallery'         => esc_html__('Gallery', 'homey'),
                    'details'         => esc_html__('Details', 'homey'),
                    'prices'          => esc_html__('Prices', 'homey'),
                    'accomodation'    => esc_html__('Accomodation', 'homey'),
                    'features'        => esc_html__('Features', 'homey'),
                    'map'             => esc_html__('Map', 'homey'),
                    'nearby'          => esc_html__('Yelp Near By Places', 'homey'),
                    'rules'           => esc_html__('Rules', 'homey'),
                    'availability'    => esc_html__('Availability', 'homey'),
                    'host'            => esc_html__('Host', 'homey'),
                    'reviews'         => esc_html__('Reviews & Ratings', 'homey'),
                    'similar-listing' => esc_html__('Similar Listing', 'homey'),
                ),
                'disabled' => array(
                    'video'        => esc_html__('Video', 'homey'),
                    'about_commercial' => esc_html__('About(commercial)', 'homey'),
                    'services' => esc_html__('Services', 'homey'),
                    'custom-periods' => esc_html__('Custom Periods', 'homey'),
                )
            ),
        )
    )
));

$listing_detail_showhide = array(
    'sn_type_label' => $homey_local['type_label'],
    'sn_accom_label' => $homey_local['accom_label'],
    'sn_bedrooms_label' => $homey_local['bedrooms_label'],
    'sn_bathrooms_label' => $homey_local['bathrooms_label'],
    'sn_rooms_label' => $homey_local['rooms_label'],
    'sn_about_listing_title' => $homey_local['about_listing_title'],
    'sn_accommodates_label' => $homey_local['accommodates_label'],
    'sn_opening_hours_label' => $homey_local['opening_hours_label'],
    'sn_size_label' => $homey_local['size_label'],
    'sn_beds_label' => $homey_local['beds_label'],
    'sn_guests_label' => $homey_local['guests_label'],
    'sn_id_label' => esc_html__('ID', 'homey'),
    'sn_check_in_after' => $homey_local['check_in_after'],
    'sn_check_out_before' => $homey_local['check_out_before'],
    'sn_nightly_label' => $homey_local['nightly_label'],
    'sn_hourly_label' => esc_html__('Hourly', 'homey'),
    'sn_weekends_label' => $homey_local['weekends_label'],
    'sn_weekly7d_label' => $homey_local['weekly7d_label'],
    'sn_monthly30d_label' => $homey_local['monthly30d_label'],
    'sn_security_deposit_label' => $homey_local['security_deposit_label'],
    'sn_tax_rate_label' => $homey_local['tax_rate_label'],
    'sn_addinal_guests_label' => $homey_local['addinal_guests_label'],
    'sn_allow_additional_guests' => $homey_local['allow_additional_guests'],
    'sn_cleaning_fee' => $homey_local['cleaning_fee'],
    'sn_city_fee' => $homey_local['city_fee'],
    'sn_min_no_of_days' => $homey_local['min_no_of_days'],
    'sn_max_no_of_days' => $homey_local['max_no_of_days'],
    'sn_min_no_of_hours' => esc_html__('Minimum number of hours', 'homey'),
    'sn_smoking_allowed' => $homey_local['smoking_allowed'],
    'sn_pets_allowed' => $homey_local['pets_allowed'],
    'sn_party_allowed' => $homey_local['party_allowed'],
    'sn_children_allowed' => $homey_local['children_allowed'],
    'sn_add_rules_info' => $homey_local['add_rules_info'],
    'sn_min_stay_is' => $homey_local['min_stay_is'],
    'sn_max_stay_is' => $homey_local['max_stay_is'],
);
$listing_detail_showhide = array_merge($listing_detail_showhide, $custom_fields_array);

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Show/Hide Data', 'homey' ),
    'id'     => 'single-listing-showhide',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'show_hide_labels',
            'type'     => 'checkbox',
            'title'    => esc_html__( 'Listing Detail Data', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__('Check what data you want to hide from the listing detail page', 'homey'),
            'options'  => $listing_detail_showhide,
            'default' => array(
                'sn_type_label' => '0',
                'sn_size_label' => '0',
                'sn_accom_label' => '0',
                'sn_bedrooms_label' => '0',
                'sn_beds_label' => '0',
                'sn_bathrooms_label' => '0',
                'sn_about_listing_title' => '0',
                'sn_opening_hours_label' => '0',
                'sn_guests_label' => '0',
                'sn_id_label' => '0',
                'sn_check_in_after' => '0',
                'sn_check_out_before' => '0',
                'sn_nightly_label' => '0',
                'sn_hourly_label' => '0',
                'sn_weekends_label' => '0',
                'sn_weekly7d_label' => '0',
                'sn_monthly30d_label' => '0',
                'sn_security_deposit_label' => '0',
                'sn_tax_rate_label' => '0',
                'sn_addinal_guests_label' => '0',
                'sn_allow_additional_guests' => '0',
                'sn_cleaning_fee' => '0',
                'sn_city_fee' => '0',
                'sn_min_no_of_days' => '0',
                'sn_max_no_of_days' => '0',
                'sn_min_no_of_hours' => '0',
                'sn_smoking_allowed' => '0',
                'sn_pets_allowed' => '0',
                'sn_party_allowed' => '0',
                'sn_children_allowed' => '0',
                'sn_add_rules_info' => '0',
                'sn_min_stay_is' => '0',
                'sn_max_stay_is' => '0',
                
            )
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Yelp Nearby Places', 'homey' ),
    'id'     => 'yelp',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'homey_yelp_api_key',
            'type'     => 'text',
            'title'    => esc_html__( 'API Key', 'homey' ),
            'subtitle' => '',
            'desc'     => wp_kses(__('Enter the API key provided by Yealp. Get this detail after you signup here <a target="_blank" href="https://www.yelp.com/developers/v3/manage_app">https://www.yelp.com/developers/v3/manage_app</a>', 'homey'), $allowed_html_array),
        ),
        array(
            'id'       => 'homey_yelp_term',
            'type'     => 'select',
            'multi'    => true,
            'title'    => esc_html__( 'Select Term', 'homey' ),
            'desc' => esc_html__( "Select the Yelp terms that you want to display", 'homey' ),
            'options'  => $yelp_categories = array (
                'active' => 'Active Life',
                'arts' => 'Arts & Entertainment',
                'auto' => 'Automotive',
                'beautysvc' => 'Beauty & Spas',
                'education' => 'Education',
                'eventservices' => 'Event Planning & Services',
                'financialservices' => 'Financial Services',
                'food' => 'Food',
                'health' => 'Health & Medical',
                'homeservices' => 'Home Services ',
                'hotelstravel' => 'Hotels & Travel',
                'localflavor' => 'Local Flavor',
                'localservices' => 'Local Services',
                'massmedia' => 'Mass Media',
                'nightlife' => 'Nightlife',
                'pets' => 'Pets',
                'professional' => 'Professional Services',
                'publicservicesgovt' => 'Public Services & Government',
                'realestate' => 'Real Estate',
                'religiousorgs' => 'Religious Organizations',
                'restaurants' => 'Restaurants',
                'shopping' => 'Shoppi',
                'transport' => 'Transportation'
            ),
            'default' => array('food', 'health', 'education', 'realestate'),
        ),
        array(
            'id'       => 'homey_yelp_limit',
            'type'     => 'text',
            'title'    => esc_html__( 'Result Limit', 'homey' ),
            'desc' => esc_html__( "Enter Yelp results limit. Only numbers", 'homey' ),
            'default' => 3
        ),
        array(
            'id'       => 'homey_yelp_dist_unit',
            'type'     => 'select',
            'multi'    => false,
            'title'    => esc_html__( 'Distance Unit', 'homey' ),
            'desc' => esc_html__( "Select the Yelp distance unit.", 'homey' ),
            'options'  => array (
                'miles' => 'Miles',
                'kilometers' => 'Kilometers'
            ),
            'default' => 'miles',
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Reviews & Rating', 'homey' ),
    'id'     => 'listing-reviews',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(

        array(
            'id'       => 'num_of_review',
            'type'     => 'text',
            'title'    => esc_html__( 'Number of Reviews', 'homey' ),
            'desc' => esc_html__( 'Enter the number of reviews to display per page', 'homey' ),
            'default'  => 5,
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Similar Listings', 'homey' ),
    'id'     => 'listing-similar-showhide',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(

        array(
            'id'       => 'similer_listings_layout',
            'type'     => 'select',
            'title'    => esc_html__( 'Layout', 'homey' ),
            'desc' => esc_html__( "Select the layout for the similar listings", 'homey' ),
            'options'  => array(
                'list' => 'List View',
                'grid' => 'Grid View',
                'card' => 'Card View',
            ),
            'default' => 'list'
        ),

        array(
            'id'       => 'similer_listings_count',
            'type'     => 'select',
            'title'    => esc_html__( 'Listing Number', 'homey' ),
            'subtitle' => esc_html__( "Select how many similar listings you want to display", 'homey' ),
            'options'  => array(
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
                7 => 7,
                8 => 8,
                9 => 9,
                10 => 10,
            ),
            'default' => 4
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Icons', 'homey' ),
    'id'     => 'listing-detail-icons',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(

        array(
            'id'       => 'detail_icon_type',
            'type'     => 'select',
            'title'    => esc_html__( 'Icon Type', 'homey' ),
            'subtitle' => '',
            'options'  => array(
                'fontawesome_icon' => 'FontAwesome',
                'custom_icon' => 'Custom Icon'
            ),
            'default' => 'fontawesome_icon'
        ),

        array(
            'id'        => 'de_type_icon',
            'type'      => 'text',
            'title'     => esc_html__( 'Listing Type', 'homey' ),
            'default'   => 'fa fa-home',
            'desc'      => 'Default: fa fa-home',
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'fontawesome_icon')
        ),

        array(
            'id'        => 'de_acco_icon',
            'type'      => 'text',
            'title'     => esc_html__( 'Accomodation', 'homey' ),
            'default'   => 'fa fa-user',
            'desc'      => 'Default: fa fa-user',
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'fontawesome_icon')
        ),

        array(
            'id'        => 'de_acco_sec_icon',
            'type'      => 'text',
            'title'     => esc_html__( 'Accomodation Section', 'homey' ),
            'default'   => 'fa fa-bed',
            'desc'      => 'Default: fa fa-bed',
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'fontawesome_icon')
        ),

        array(
            'id'        => 'de_bedroom_icon',
            'type'      => 'text',
            'title'     => esc_html__( 'Bedrooms', 'homey' ),
            'default'   => 'fa fa-bed',
            'desc'      => 'Default: fa fa-bed',
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'fontawesome_icon')
        ),

        array(
            'id'        => 'de_bathroom_icon',
            'type'      => 'text',
            'title'     => esc_html__( 'Bathrooms', 'homey' ),
            'default'   => 'fa fa-shower',
            'desc'      => 'Default: fa fa-shower',
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'fontawesome_icon')
        ),

        array(
            'id'        => 'de_cus_type_icon',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Listing Type', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => '' ),
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'custom_icon')
        ),

        array(
            'id'        => 'de_cus_acco_icon',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Accomodation', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => '' ),
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'custom_icon')
        ),

        array(
            'id'        => 'de_cus_acco_sec_icon',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Accomodation Section', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => '' ),
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'custom_icon')
        ),

        array(
            'id'        => 'de_cus_bedroom_icon',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Bedrooms', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => '' ),
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'custom_icon')
        ),

        array(
            'id'        => 'de_cus_bathroom_icon',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Bathrooms', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => '' ),
            'subtitle'  => '',
            'required'  => array('detail_icon_type', '=', 'custom_icon')
        ),
    )
));

/* **********************************************************************
 * listing Print
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Print Listing', 'homey' ),
    'id'     => 'listing-print',
    'desc'   => '',
    'icon'   => 'el-icon-print el-icon-small',
    'fields'        => array(
        array(
            'id'        => 'print_page_logo',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Print listing Logo', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
            'desc'  => esc_html__( 'Upload your custom site logo for the print listing', 'homey' ),
        ),
        array(
            'id'       => 'print_tagline',
            'type'     => 'switch',
            'title'    => esc_html__( 'Tagline', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_rating',
            'type'     => 'switch',
            'title'    => esc_html__( 'Rating', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_qr_code',
            'type'     => 'switch',
            'title'    => esc_html__( 'QR Code', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_host',
            'type'     => 'switch',
            'title'    => esc_html__( 'Host Info', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_description',
            'type'     => 'switch',
            'title'    => esc_html__( 'Description', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_details',
            'type'     => 'switch',
            'title'    => esc_html__( 'Details', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_pricing',
            'type'     => 'switch',
            'title'    => esc_html__( 'Pricing', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_accomodation',
            'type'     => 'switch',
            'title'    => esc_html__( 'Accomodation', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_features',
            'type'     => 'switch',
            'title'    => esc_html__( 'Features', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_rules',
            'type'     => 'switch',
            'title'    => esc_html__( 'Rules', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_availability',
            'type'     => 'switch',
            'title'    => esc_html__( 'Availability', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 1,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
        array(
            'id'       => 'print_gallery',
            'type'     => 'switch',
            'title'    => esc_html__( 'Gallery Images', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Yes', 'homey' ),
            'off'      => esc_html__( 'No', 'homey' ),
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Listings', 'homey' ),
    'id'     => 'listings-homey',
    'desc'   => '',
    'icon'   => 'el-icon-cog el-icon-small',
    'fields'        => array(

    ),
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Listings Page', 'homey' ),
    'id'     => 'listings-page',
    'desc'   => '',
    'subsection' => true,
    'fields'        => array(
        array(
            'id'       => 'pagination_type',
            'type'     => 'select',
            'title'    => esc_html__('Pagination', 'homey'),
            'desc' => esc_html__('Select the pagination type for the listing pages', 'homey'),
            'desc'     => '',
            'options'  => array(
                'number'   => esc_html__( 'Number', 'homey' ),
                'loadmore'   => esc_html__( 'Load More', 'homey' ),
            ),
            'default'  => 'number',
        )
    ),
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Sticky Map Listings', 'homey' ),
    'id'     => 'listing-sticky-mao',
    'desc'   => esc_html__( 'Listing page with sticky map settings', 'homey' ),
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'sticky_map_layout',
            'type'     => 'select',
            'title'    => __('Listings Layout', 'homey'),
            'desc' => __('Select the listings layout', 'homey'),
            'options'  => array(
                'list' => 'List View',
                'grid' => 'Grid View',
                'card' => 'Card View',
            ),
            'default' => 'list'
        ),
        /*array(
            'id'       => 'sticky_map_pagi_type',
            'type'     => 'select',
            'title'    => esc_html__('Pagination', 'homey'),
            'subtitle' => esc_html__('Choose pagination type', 'homey'),
            'desc'     => '',
            'options'  => array(
                'number'   => esc_html__( 'Number', 'homey' ),
                'loadmore'   => esc_html__( 'Load More', 'homey' ),
            ),
            'default'  => 'loadmore',
        ),*/
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Half Map Listings', 'homey' ),
    'id'     => 'halfmap-listings',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'halfmap_posts_layout',
            'type'     => 'select',
            'title'    => esc_html__('Listings Layout', 'homey'),
            'desc' => esc_html__('Select the listings layout for the half map page.', 'homey'),
            'options'  => array(
                'list' => 'List View',
                'grid' => 'Grid View',
                'card' => 'Card View',
            ),
            'default' => 'list'
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Show/Hide Data', 'homey' ),
    'id'     => 'listing-cgl-showhide',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(
        array(
            'id'       => 'cgl_meta',
            'type'     => 'switch',
            'title'    => esc_html__( 'Listing Data', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable listing data on grid and list view', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'cgl_beds',
            'type'     => 'switch',
            'title'    => $homey_local['bedrooms_label'],
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable bedrooms on grid and list view', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'cgl_baths',
            'type'     => 'switch',
            'title'    => $homey_local['baths_label'],
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable baths on grid and list view', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'cgl_guests',
            'type'     => 'switch',
            'title'    => $homey_local['guests_label'],
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable guests on grid and list view', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'cgl_types',
            'type'     => 'switch',
            'title'    => $homey_local['listing_type'],
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable listing type on grid and list view', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'enable_host',
            'type'     => 'switch',
            'title'    => esc_html__( 'Host Name', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable the host name on grid and list view', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'rating',
            'type'     => 'switch',
            'title'    => esc_html__( 'Rating', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable the rating information on grid and list view', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id'       => 'compare_favorite',
            'type'     => 'switch',
            'title'    => esc_html__( 'Compare & Favorite', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enable/Disable the photo count information on grid and list view', 'homey' ),
            'default'  => 1,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Icons', 'homey' ),
    'id'     => 'listings-icons',
    'desc'   => '',
    'subsection' => true,
    'fields' => array(

        array(
            'id'        => 'lgc_bedroom_icon',
            'type'      => 'text',
            'title'     => esc_html__( 'Bedrooms', 'homey' ),
            'default'   => 'fa fa-bed',
            'desc'      => 'Default: fa fa-bed',
            'subtitle'  => '',
        ),

        array(
            'id'        => 'lgc_bathroom_icon',
            'type'      => 'text',
            'title'     => esc_html__( 'Bathrooms', 'homey' ),
            'default'   => 'fa fa-shower',
            'desc'      => 'Default: fa fa-shower',
            'subtitle'  => '',
        ),

        array(
            'id'        => 'lgc_guests_icon',
            'type'      => 'text',
            'title'     => esc_html__( 'Guests', 'homey' ),
            'default'   => 'fa fa-user',
            'desc'      => 'Default: fa fa-user',
            'subtitle'  => '',
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Taxonomies Layout', 'homey' ),
    'id'     => 'taxonomies-pages',
    'desc'   => esc_html__( 'Select taxonomies ( type, room type, city, state, country, area etc ) pages layout', 'homey' ),
    'subsection' => false,
    'fields' => array(
        array(
            'id'       => 'taxonomy_layout',
            'type'     => 'image_select',
            'title'    => __('Page Layout', 'homey'),
            'subtitle' => '',
            'options'  => array(
                'no-sidebar' => array(
                    'alt'   => '',
                    'img'   => ReduxFramework::$_url.'assets/img/1c.png'
                ),
                'left-sidebar' => array(
                    'alt'   => '',
                    'img'   => ReduxFramework::$_url.'assets/img/2cl.png'
                ),
                'right-sidebar' => array(
                    'alt'   => '',
                    'img'  => ReduxFramework::$_url.'assets/img/2cr.png'
                )
            ),
            'default' => 'left-sidebar'
        ),
        array(
            'id'       => 'taxonomy_posts_layout',
            'type'     => 'select',
            'title'    => __('Listings Layout', 'homey'),
            'desc' => __('Select the listings layout for the taxonomy pages', 'homey'),
            'options'  => array(
                'list' => 'List View',
                'grid' => 'Grid View',
                'card' => 'Card View',
            ),
            'default' => 'list'
        ),

        array(
            'id'       => 'taxonomy_default_order',
            'type'     => 'select',
            'title'    => __('Default Order', 'homey'),
            'desc' => __('Select the taxonomy page default display order.', 'homey'),
            'options'  => array(
                'd_date' => esc_html__( 'Date New to Old', 'homey' ),
                'a_date' => esc_html__( 'Date Old to New', 'homey' ),
                'd_price' => esc_html__( 'Price (High to Low)', 'homey' ),
                'a_price' => esc_html__( 'Price (Low to High)', 'homey' ),
                'd_rating' => esc_html__( 'Rating', 'homey' ),
                'featured_top' => esc_html__( 'Show Featured Listings on Top', 'homey' ),
            ),
            'default' => 'd_date'
        ),

        array(
            'id'       => 'taxonomy_num_posts',
            'type'     => 'text',
            'title'    => esc_html__('Number of listings to display', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => '9',
            'validate' => 'numeric'
        ),
    )
));

/* **********************************************************************
 * Google Map Settings
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Map Settings', 'homey' ),
    'id'     => 'homey-googlemap-settings',
    'desc'   => '',
    'icon'   => 'el-icon-globe el-icon-small',
    'fields' => array(
        array(
            'id'       => 'homey_map_system',
            'type'     => 'button_set',
            'title'    => esc_html__('Map System', 'homey'),
            'subtitle' => esc_html__('Select the map system that you want to use', 'homey'),
            'desc'     => '',
            'options' => array(
                'open_street_map' => 'Open Street Map',
                'mapbox' => 'Map Box',
                'google' => 'Google',
             ), 
            'default' => 'open_street_map'
        ),
        array(
            'id'       => 'map_api_key',
            'type'     => 'text',
            'title'    => esc_html__( 'Google Maps API KEY', 'homey' ),
            'desc'     => wp_kses(__( 'Enter your google maps api key. You can get it from <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key">here</a>.', 'homey' ), $allowed_html_array),
            'subtitle' => '',
            'required'  => array('homey_map_system', '=', 'google'),
            'default'  => ''
        ),
        array(
            'id'       => 'mapbox_api_key',
            'type'     => 'text',
            'title'    => esc_html__( 'Mapbox API KEY', 'homey' ),
            'desc'     => wp_kses(__( 'Please enter the Mapbox API key, you can get from <a target="_blank" href="https://account.mapbox.com/">here</a>.', 'homey' ), $allowed_html_array),
            'required'  => array('homey_map_system', '=', 'mapbox')
        ),
        array(
            'id'       => 'googlemap_ssl',
            'type'     => 'select',
            'title'    => esc_html__( 'Google Maps SSL', 'homey' ),
            'desc' => esc_html__( 'Use Google Maps with SSL', 'homey' ),
            'options'  => array(
                'no'   => esc_html__( 'No', 'homey' ),
                'yes'   => esc_html__( 'Yes', 'homey' )
            ),
            'required'  => array('homey_map_system', '=', 'google'),
            'default'  => 'no'
        ),

        array(
            'id'       => 'geo_country_limit',
            'type'     => 'switch',
            'title'    => esc_html__( 'Limit to Country', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Geo autocomplete limit to specific country', 'homey' ),
            'default'  => 0,
            'required'  => array('homey_map_system', '=', 'google'),
            'on'       => 'Enabled',
            'off'      => 'Disabled',
        ),
        array(
            'id'        => 'geocomplete_country',
            'type'      => 'select',
            'required'  => array('geo_country_limit', '=', '1'),
            'title'     => esc_html__( 'Geo Auto Complete Country', 'homey' ),
            'subtitle'  => esc_html__( 'Limit Geo auto complete to specific country', 'homey' ),
            'options'   => $Countries,
            'default' => ''
        ),

        array(
            'id'       => 'markerPricePins',
            'type'     => 'select',
            'title'    => esc_html__( 'Marker Type', 'homey' ),
            'desc' => esc_html__( 'Select the marker type for Google Map', 'homey' ),
            'options'  => array(
                'no'   => esc_html__( 'Marker Icon', 'homey' ),
                'yes'   => esc_html__( 'Price Pins', 'homey' )
            ),
            'default'  => 'no'
        ),
        array(
            'id'       => 'default_lat',
            'type'     => 'text',
            'title'    => esc_html__( 'Default Latitude', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enter default latitude for maps', 'homey' ),
            'default'  => '25.686540',
        ),
        array(
            'id'       => 'default_lng',
            'type'     => 'text',
            'title'    => esc_html__( 'Default Longitude', 'homey' ),
            'desc'     => '',
            'subtitle' => esc_html__( 'Enter default longitude for maps', 'homey' ),
            'default'  => '-80.431345',
        )
    ),
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Cluster', 'homey' ),
    'id'     => 'map-cluster',
    'desc'   => '',
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'       => 'pin_cluster_enable',
            'type'     => 'select',
            'title'    => esc_html__( 'Pin Cluster', 'homey' ),
            'desc' => esc_html__( 'Use a pin cluster on Google Map', 'homey' ),
            'options'  => array(
                'yes'   => esc_html__( 'Yes', 'homey' ),
                'no'   => esc_html__( 'No', 'homey' )
            ),
            'default'  => 'yes'
        ),
        array(
            'id'        => 'pin_cluster',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Cluster Icon', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => get_template_directory_uri() . '/images/cluster-icon.png' ),
            'desc'  => esc_html__( 'Upload the map cluster icon.', 'homey' ),
            'required'  => array('pin_cluster_enable', '=', 'yes'),
        ),
        array(
            'id'       => 'pin_cluster_zoom',
            'type'     => 'text',
            'title'    => esc_html__( 'Cluster Zoom Level', 'homey' ),
            'desc'     => '',
            'desc' => esc_html__( 'Enter the maximum zoom level for cluster to appear. Default 12', 'homey' ),
            'default'  => '12',
            'validate' => 'numeric',
            'required'  => array('pin_cluster_enable', '=', 'yes'),
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Single Listing Map', 'homey' ),
    'id'     => 'map-single-listing',
    'desc'   => '',
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'       => 'detail_map_pin_type',
            'type'     => 'select',
            'title'    => esc_html__('Pin or Circle', 'homey'),
            'desc' => esc_html__('Select what to show on map, Marker or Circle pin', 'homey'),
            'options'  => array(
                'marker'   => esc_html__( 'Marker Pin', 'homey' ),
                'circle'   => esc_html__( 'Circle', 'homey' ),
            ),
            'default'  => 'marker',
        ),
        array(
            'id'       => 'singlemap_zoom_level',
            'type'     => 'text',
            'title'    => esc_html__( 'Single Listing Map Zoom', 'homey' ),
            'desc'     => '',
            'desc' => esc_html__( 'Enter a number from 1 to 20', 'homey' ),
            'default'  => '14',
            'validate' => 'numeric'
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Map Style', 'homey' ),
    'id'     => 'map-style',
    'desc'   => esc_html__('Only work with Google Map', 'homey'),
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'       => 'googlemap_stype',
            'type'     => 'ace_editor',
            'title'    => esc_html__( 'Style for Google Map', 'homey' ),
            'subtitle' => esc_html__( 'Use https://snazzymaps.com/ to create styles', 'homey' ),
            'desc'     => '',
            'default'  => '',
            'mode'     => 'plain'
        )
    )
));



/* **********************************************************************
 * Google ReCaptcha
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Google reCaptcha', 'homey' ),
    'id'     => 'google-recaptcha',
    'desc'   => '',
    'icon'   => 'el-icon-envelope el-icon-small',
    'fields'        => array(
        array(
            'id'       => 'enable_reCaptcha',
            'type'     => 'switch',
            'title'    => esc_html__( 'Enable reCaptcha for contact forms?', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),
        array(
            'id' => 'google_recaptcha_info',
            'type' => 'info',
            'title' => esc_html__('Google reCaptcha', 'homey'),
            'style' => 'info',
            'desc' => __('<p>If you do not have keys already then visit <kbd>
            <a href = "https://www.google.com/recaptcha/admin">
                https://www.google.com/recaptcha/admin</a></kbd> to generate them.
        Set the respective keys in <kbd>Site Key</kbd> and
        <kbd>Secret Key</kbd></p>', 'homey')
        ),
        array(
            'id'       => 'recaptha_site_key',
            'type'     => 'text',
            'title'    => esc_html__( 'Site Key', 'homey' ),
            'desc'     => esc_html__('Enter the Google reCaptha site key.', 'homey'),
            'default'  => ''
        ),

        array(
            'id'       => 'recaptha_secret_key',
            'type'     => 'text',
            'title'    => esc_html__( 'Secret Key', 'homey' ),
            'desc'     => esc_html__('Enter the Google reCaptha secret key.', 'homey'),
            'default'  => ''
        ),
    ),
));

/* **********************************************************************
 * Invoices
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Invoice Options', 'homey' ),
    'id'     => 'listing-invoice',
    'desc'   => '',
    'icon'   => 'el-icon-cog el-icon-small',
    'fields'        => array(
        array(
            'id'        => 'invoice_logo',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Company Logo', 'homey' ),
            'readonly' => false,
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
            'desc'  => esc_html__( 'Upload the company logo for invoices.', 'homey' ),
        ),
        array(
            'id'        => 'invoice_company_name',
            'type'      => 'text',
            'title'     => esc_html__( 'Company Name', 'homey' ),
            'default'   => 'Homey LLC',
            'desc'  => esc_html__( 'Enter the company name', 'homey' ),
        ),
        array(
            'id'        => 'invoice_address',
            'type'      => 'textarea',
            'title'     => esc_html__( 'Company Address', 'homey' ),
            'default'   => '2983 Halton Road, Suite #320<br/>
                            Miami Beach<br/>                    
                            Florida<br/>
                            33139<br/>',
            'desc'  => esc_html__( 'Enter the company address', 'homey' )
        ),
        /*array(
            'id'        => 'invoice_phone',
            'type'      => 'text',
            'title'     => esc_html__( 'Company Phone', 'homey' ),
            'default'   => '(987)654 3210',
            'subtitle'  => '',
        ),*/
        array(
            'id'        => 'invoice_additional_info',
            'type'      => 'editor',
            'title'     => esc_html__( 'Additional Info', 'homey' ),
            'default'   => '<p>The lorem ipsum text is typically a scrambled section of De finibus bonorum et malorum, a 1st-century BC Latin text by Cicero, with words altered, added, and removed to make it nonsensical, improper Latin.[citation needed]</p>',
            'subtitle'  => ''
        )
    ),
));

/* **********************************************************************
 * Email Management
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Email Management', 'homey' ),
    'id'     => 'homey-email-management',
    'desc'   => esc_html__( 'Global variables: {site_url} as website url, {site_title} as website name, {user_email} as user_email, {user_login} as username', 'homey' ),
    'icon'   => 'el-icon-envelope el-icon-small',
    'fields'        => array(
        array(
            'id'       => 'enable_html_emails',
            'type'     => 'switch',
            'title'    => esc_html__( 'HTML Emails?', 'homey' ),
            'desc'     => '',
            'desc' => esc_html__('Enable/Disable HTML emails. If enabled then system will allow you to add HTML code in the email templates', 'homey'),
            'default'  => 1,
            'on'       => esc_html__( 'Enable', 'homey' ),
            'off'      => esc_html__( 'Disable', 'homey' ),
        ),

        array(
            'id'     => 'email-header',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Email Header</span>', 'homey' ), $allowed_html_array),
            'subtitle' => '',
            'desc'   => ''
        ),
        array(
            'id'       => 'enable_email_header',
            'type'     => 'switch',
            'title'    => esc_html__( 'Enable Email Header.', 'homey' ),
            'desc'     => '',
            'desc' => esc_html__('Enable/Disable the email header', 'homey'),
            'default'  => 1,
            'on'       => esc_html__( 'Enable', 'homey' ),
            'off'      => esc_html__( 'Disable', 'homey' ),
        ),
        array(
            'id'        => 'email_head_logo',
            'url'       => true,
            'type'      => 'media',
            'title'     => esc_html__( 'Logo', 'homey' ),
            'readonly' => false,
            'required' => array('enable_email_header', '=', '1'),
            'default'   => array( 'url' => get_template_directory_uri() .'/images/logo.png' ),
            'desc'  => esc_html__( 'Upload the logo for the email header', 'homey' ),
        ),
        array(
            'id'       => 'email_head_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Header Background Color', 'homey' ),
            'subtitle' => '',
            'required' => array('enable_email_header', '=', '1'),
            'default'  => '#00AEEF',
            'transparent' => false,
            'desc'  => esc_html__( 'Select the header background color', 'homey' ),
        ),

        array(
            'id'     => 'email-footer',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Email Footer</span>', 'homey' ), $allowed_html_array),
            'subtitle' => '',
            'desc'   => ''
        ),
        array(
            'id'       => 'enable_email_footer',
            'type'     => 'switch',
            'title'    => esc_html__( 'Enable Email Footer.', 'homey' ),
            'desc'     => '',
            'desc' => esc_html__('Enable/Disable the email footer', 'homey'),
            'default'  => 1,
            'on'       => esc_html__( 'Enable', 'homey' ),
            'off'      => esc_html__( 'Disable', 'homey' ),
        ),
        array(
            'id'       => 'email_foot_bg_color',
            'type'     => 'color',
            'title'    => esc_html__( 'Footer background Color', 'homey' ),
            'subtitle' => '',
            'required' => array('enable_email_footer', '=', '1'),
            'default'  => '#FFFFFF',
            'transparent' => false,
            'desc'  => esc_html__( 'Select the footer background color', 'homey' ),
        ),

        array(
            'id'       => 'email_footer_content',
            'type'     => 'editor',
            'title'    => esc_html__('Footer Content', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'required' => array('enable_email_footer', '=', '1'),
            'default'  => '<p style="margin: 0 0 10px;">Copyright &copy; 2018 Favethemes, All rights reserved.</p>
            <p style="margin: 0 0 10px;">Please do not reply to this email. You are receiving this email because you are subscribed to <a href="http://example.com" style="color: #00AEEF; text-decoration: none;">gethomey.io</a></p>

            <p style="margin: 0 0 10px;">Our mailing address is:</p>
            <p style="margin: 0 0 10px;">Favethemes<br>
                1680 Michigan Ave<br>
            Miami Beach, FL 33139-2538</p>',
            'args' => array(
                'teeny' => false,
                'textarea_rows' => 10
            )
        ),
        array(
            'id'     => 'email-ft-social-link1',
            'type'   => 'info',
            'notice' => false,
            'required' => array('enable_email_footer', '=', '1'),
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Social Link 1</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),
        array(
            'id'       => 'no_reply_email_address',
            'type'     => 'text',
            'title'    => esc_html__('No Reply Email Address', 'homey'),
            'desc' => esc_html__('Enter the no reply email address', 'homey'),
            'default'  => '',
        ),
        array(
            'id'       => 'social_1_icon',
            'url'       => true,
            'type'      => 'media',
            'required' => array('enable_email_footer', '=', '1'),
            'readonly' => true,
            'title'    => esc_html__( 'Social Icon', 'homey' ),
            'desc'  => esc_html__( 'Upload a social icon image', 'homey' ),
            'default'  => ''
        ),
        array(
            'id'       => 'social_1_link',
            'type'     => 'text',
            'required' => array('enable_email_footer', '=', '1'),
            'title'    => esc_html__('Link', 'homey'),
            'desc' => esc_html__('Enter the social media profile URL', 'homey'),
            'default'  => '',
        ),

        array(
            'id'     => 'email-ft-social-link2',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'required' => array('enable_email_footer', '=', '1'),
            'title'  => wp_kses(__( '<span class="font24">Social Link 2</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),
        array(
            'id'       => 'social_2_icon',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'required' => array('enable_email_footer', '=', '1'),
            'title'    => esc_html__( 'Social Icon', 'homey' ),
            'desc'  => esc_html__( 'Upload a social icon image', 'homey' ),
            'default'  => ''
        ),
        array(
            'id'       => 'social_2_link',
            'type'     => 'text',
            'required' => array('enable_email_footer', '=', '1'),
            'title'    => esc_html__('Link', 'homey'),
            'desc' => esc_html__('Enter the social media profile URL', 'homey'),
            'default'  => '',
        ),
        array(
            'id'     => 'email-ft-social-link3',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'required' => array('enable_email_footer', '=', '1'),
            'title'  => wp_kses(__( '<span class="font24">Social Link 3</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),
        array(
            'id'       => 'social_3_icon',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'required' => array('enable_email_footer', '=', '1'),
            'title'    => esc_html__( 'Social Icon', 'homey' ),
            'subtitle' => '',
            'default'  => '',
            'desc'  => esc_html__( 'Upload a social icon image', 'homey' ),
        ),
        array(
            'id'       => 'social_3_link',
            'type'     => 'text',
            'title'    => esc_html__('Link', 'homey'),
            'desc' => esc_html__('Enter the social media profile URL', 'homey'),
            'required' => array('enable_email_footer', '=', '1'),
            'default'  => '',
        ),
        array(
            'id'     => 'email-ft-social-link4',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'required' => array('enable_email_footer', '=', '1'),
            'title'  => wp_kses(__( '<span class="font24">Social Link 4</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),
        array(
            'id'       => 'social_4_icon',
            'url'       => true,
            'type'      => 'media',
            'readonly' => false,
            'title'    => esc_html__( 'Social Icon', 'homey' ),
            'subtitle' => '',
            'required' => array('enable_email_footer', '=', '1'),
            'default'  => '',
            'desc'  => esc_html__( 'Upload a social icon image', 'homey' ),
        ),
        array(
            'id'       => 'social_4_link',
            'type'     => 'text',
            'title'    => esc_html__('Link', 'homey'),
            'desc' => esc_html__('Enter the social media profile URL', 'homey'),
            'required' => array('enable_email_footer', '=', '1'),
            'default'  => '',
        ),
    ),
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'New Register', 'homey' ),
    'id'     => 'email-new-register',
    'desc'   => '',
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'     => 'email-new-user-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">New Registered User Notification</span>', 'homey' ), $allowed_html_array),
            'desc'   => esc_html__( '{user_login_register} as username, {user_password} as user password, {user_email_register} as new user email, {profile_url}', 'homey' )
        ),

        array(
            'id'       => 'homey_subject_new_user_register',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('Your username and password on {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_new_user_register',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'desc'     => '',
            'default'  => 'Congratulations! Your account has been registered at {site_title} <br>
Your username : {user_login_register}<br>
Your password : {user_password}<br>

You can manage your account <a href="{profile_url}">here</a><br>
You can verify your email by clicking <a href="{profile_url}">here</a><br>
<br>
Thank you for choosing Homey!<br>
Homey Team.',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'email-new-admin-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">New Registered Admin Notification</span>', 'homey' ), $allowed_html_array),
            'desc'   => esc_html__( '{user_login_register} as username, {user_password} as user password, {user_email_register} as new user email, {profile_url}', 'homey' )
        ),

        array(
            'id'       => 'homey_subject_admin_new_user_register',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('New User Registration on {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_admin_new_user_register',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => 'New user registration on {site_title}.<br>
Username: {user_login_register},<br>
E-mail: {user_email_register}',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Reservation', 'homey' ),
    'id'     => 'email-reservation',
    'desc'   => esc_html__('Global variables {reservation_detail_url}, {guest_message}, {message_link}', 'homey' ),
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'     => 'resrv-receive-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__('<span class="font24">New Reservation Received</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),

        array(
            'id'       => 'homey_subject_new_reservation',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('New Reservation Request at {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_new_reservation',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => '<p><strong>Horay!</strong> You received a new reservation on {site_title}</p>
<p><strong>Guest Message:</strong> {guest_message}&nbsp;<a href="{message_link}">click for reply</a><br /><br />Confirm Availability <a href="{reservation_detail_url}">Confirm</a><br /><br />Thank you for choosing Homey!<br />Homey Team.</p>',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'resrv-sent-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__('<span class="font24">New Reservation Sent</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),

        array(
            'id'       => 'homey_subject_new_reservation_sent',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('Your Reservation Request Sent to {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_new_reservation_sent',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => '<p><strong>Thank You!</strong> Your reservation is sent to {site_title}.</p>
<p><strong>Your Message:</strong> {guest_message}&nbsp;<a href="{message_link}">click to send another message</a><br /><br /> for details <a href="{reservation_detail_url}">Click here</a><br /><br />Thank you for choosing Homey!<br />Homey Team.</p>',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),
        array(
            'id'     => 'resrv-confirmed-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__('<span class="font24">Confirm Reservation</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),

        array(
            'id'       => 'homey_subject_confirm_reservation',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('Reservation Confirmed on {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_confirm_reservation',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => 'So far so good! Host confirmed availability for your reservation.<br>
Complete the payment due <a href="{reservation_detail_url}">Pay Now</a><br>
<br>
Thank you for choosing Homey!<br>
Homey Team.',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),
        array(
            'id'     => 'resrv-booked-user-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__('<span class="font24">Reservation Booked User</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),

        array(
            'id'       => 'homey_subject_booked_reservation',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('Reservation Booked on {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_booked_reservation',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => 'Well done! Payment received the reservation has been booked.<br>
View detail by clicking <a href="{reservation_detail_url}">here</a><br>
<br>
Thank you for choosing Homey!<br>
Homey Team.',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'resrv-booked-admin-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__('<span class="font24">Reservation Booked Admin</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),

        array(
            'id'       => 'homey_subject_admin_booked_reservation',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('Reservation Booked on {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_admin_booked_reservation',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => 'Congratulations! The reservation has been booked.<br>
View detail by clicking <a href="{reservation_detail_url}">here</a><br>
<br>
Thank you for choosing Homey!<br>
Homey Team.',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'resrv-declined-user-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__('<span class="font24">Reservation Declined</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),

        array(
            'id'       => 'homey_subject_declined_reservation',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('Reservation Declined on {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_declined_reservation',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => 'Your reservation has been declined by the host<br>
View detail by clicking <a href="{reservation_detail_url}">here</a><br>
<br>
Thank you for choosing Homey!<br>
Homey Team.',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'resrv-cancelled-user-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__('<span class="font24">Reservation Cancelled</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),

        array(
            'id'       => 'homey_subject_cancelled_reservation',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('Reservation Cancelled on {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_cancelled_reservation',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => 'The reservation has been cancelled<br>
View detail by clicking <a href="{reservation_detail_url}">here</a><br>
<br>
Thank you for choosing Homey!<br>
Homey Team.',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'resrv-payment-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__('<span class="font24">Local Payment Received</span>', 'homey' ), $allowed_html_array),
            'desc'   => ''
        ),

        array(
            'id'       => 'homey_subject_guest_sent_payment_reservation',
            'type'     => 'text',
            'title'    => esc_html__('Email Subject', 'homey'),
            'desc'     => esc_html__('Enter the email subject', 'homey'),
            'default'  => esc_html__('Reservation Payment {site_title}', 'homey'),
        ),
        array(
            'id'       => 'homey_guest_sent_payment_reservation',
            'type'     => 'editor',
            'title'    => esc_html__('Email Content', 'homey'),
            'default'  => 'Guest made reservation payment, verify it and mark reservation as booked<br>
View detail by clicking <a href="{reservation_detail_url}">here</a><br>
<br>
Thank you for choosing Homey!<br>
Homey Team.',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Submission of Listings', 'homey' ),
    'id'     => 'email-submission-listings',
    'desc'   => '',
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'     => 'email-new-submission-perlisting-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">New Submission of Listing</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('Following is new listing information you can use to find on website {listing_title} as listing title and {listing_id} as listing id and {post_status_user} for listing status for user  and {post_status_admin} listing status for admin', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_new_submission_listing',
            'type'     => 'text',
            'title'    => esc_html__('Subject for New Submission Of Listing', 'homey'),
            'subtitle' => esc_html__('Email subject for New submission Of Listing', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('New Listing on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_new_submission_listing',
            'type'     => 'editor',
            'title'    => esc_html__('Content for New Submission Listing', 'homey'),
            'subtitle' => esc_html__('Email content for new submission of listing', 'homey'),
            'desc'     => '',
            'default'  => 'Hi there,
You have a new submission of listing on  {site_url}!
Listing Title: {listing_title}
Listing ID:  {listing_id}
Listing Status: {post_status_user}',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'       => 'homey_subject_admin_new_submission_listing',
            'type'     => 'text',
            'title'    => esc_html__('Subject for Admin - New Submission Listing', 'homey'),
            'subtitle' => esc_html__('Email subject for new submission of listing', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('New submission listing on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_admin_new_submission_listing',
            'type'     => 'editor',
            'title'    => esc_html__('Content for Admin - New Submission Of Listing', 'homey'),
            'subtitle' => esc_html__('Email content for new submission of listing', 'homey'),
            'desc'     => '',
            'default'  => 'Hi there,
You have a new submission of listing on  {site_url}!
Listing Title: {listing_title}
Listing ID:  {listing_id}
Listing Status: {post_status_admin}',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),
        array(
            'id'     => 'email-update-submission-perlisting-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Update Submission of Listing</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('Following is update listing information you can use to find on website {listing_title} as listing title and {listing_id} as listing id and {post_status_user} for listing status for user  and {post_status_admin} listing status for admin', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_update_submission_listing',
            'type'     => 'text',
            'title'    => esc_html__('Subject for Update Submission Of Listing', 'homey'),
            'subtitle' => esc_html__('Email subject for Update submission Of Listing', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('Update Listing on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_update_submission_listing',
            'type'     => 'editor',
            'title'    => esc_html__('Content for Update Submission Listing', 'homey'),
            'subtitle' => esc_html__('Email content for update submission of listing', 'homey'),
            'desc'     => '',
            'default'  => 'Hi there,
You have a update submission of listing on  {site_url}!
Listing Title: {listing_title}
Listing ID:  {listing_id}
Listing Status: {post_status_user}',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'       => 'homey_subject_admin_update_submission_listing',
            'type'     => 'text',
            'title'    => esc_html__('Subject for Admin - Update Submission Listing', 'homey'),
            'subtitle' => esc_html__('Email subject for update submission of listing', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('Update submission listing on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_admin_update_submission_listing',
            'type'     => 'editor',
            'title'    => esc_html__('Content for Admin - Update Submission Of Listing', 'homey'),
            'subtitle' => esc_html__('Email content for update submission of listing', 'homey'),
            'desc'     => '',
            'default'  => 'Hi there,
You have a updated submission of listing on  {site_url}!
Listing Title: {listing_title}
Listing ID:  {listing_id}
Listing Status: {post_status_admin}',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Upgrade to Featured', 'homey' ),
    'id'     => 'email-upgrade-featured',
    'desc'   => '',
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'     => 'email-featured-perlisting-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Upgrade to Featured</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('you can use {invoice_no} as invoice number, {listing_title} as listing title and {listing_id} as listing id', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_featured_submission_listing',
            'type'     => 'text',
            'title'    => esc_html__('Subject for Featured Submission', 'homey'),
            'subtitle' => esc_html__('Email subject for featured submission per listing', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('New featured upgrade on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_featured_submission_listing',
            'type'     => 'editor',
            'title'    => esc_html__('Content for Featured Submission', 'homey'),
            'subtitle' => esc_html__('Email content for featured submission per listing', 'homey'),
            'desc'     => '',
            'default'  => 'Hi there,
You have a new featured submission on  {site_url}!
Listing Title: {listing_title}
Listing ID:  {listing_id}
The invoice number is: {invoice_no}',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'       => 'homey_subject_admin_featured_submission_listing',
            'type'     => 'text',
            'title'    => esc_html__('Subject for Admin - Featured Submission', 'homey'),
            'subtitle' => esc_html__('Email subject for featured submission per listing', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('New featured submission on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_admin_featured_submission_listing',
            'type'     => 'editor',
            'title'    => esc_html__('Content for Admin - Featured Submission', 'homey'),
            'subtitle' => esc_html__('Email content for featured submission per listing', 'homey'),
            'desc'     => '',
            'default'  => 'Hi there,
You have a new featured submission on  {site_url}!
Listing Title: {listing_title}
Listing ID:  {listing_id}
The invoice number is: {invoice_no}',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Verification', 'homey' ),
    'id'     => 'email-verification',
    'desc'   => '',
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'     => 'email-id-verification-info',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">ID Verification Request</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('you can use {username} as User Name, {email} as user email and {verify_link} as id verify page link', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_admin_id_verification',
            'type'     => 'text',
            'title'    => esc_html__('Subject', 'homey'),
            'subtitle' => esc_html__('Email subject for id verification request', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('New ID Verification Request on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_admin_id_verification',
            'type'     => 'editor',
            'title'    => esc_html__('Content', 'homey'),
            'subtitle' => esc_html__('Email content for id verification request', 'homey'),
            'desc'     => '',
            'default'  => 'Hi there,<br>
You have a new id verification request on {site_url}!<br>
Username: {username}<br>
Email:  {email}<br>
Follow this link to verify: {verify_link}',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        )
    )
));

Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Wallet', 'homey' ),
    'id'     => 'wallet-emails',
    'desc'   => '',
    'icon'   => '',
    'subsection' => true,
    'fields'    => array(
        array(
            'id'     => 'payout-request-info-admin',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Payout Request Admin</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('you can use {payout_amount} as Payout Amount, {payout_link} as payout page detail link', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_admin_payout_request',
            'type'     => 'text',
            'title'    => esc_html__('Subject', 'homey'),
            'subtitle' => esc_html__('Email subject for payout request', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('New Payout Request on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_admin_payout_request',
            'type'     => 'editor',
            'title'    => esc_html__('Content', 'homey'),
            'subtitle' => esc_html__('Email content for payout request', 'homey'),
            'desc'     => '',
            'default'  => 'Hi,<br>
<p>You have a new payout request on {site_url}!</p>
<p>Amount: {payout_amount}</p>
<p>Follow this link for details: <a href="{payout_link}">{payout_link}</a></p>',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'payout-request-info-host',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Payout Request Host</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('you can use {host_name} as Host Name, {payout_amount} as Payout Amount {host_name} as host name', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_payout_request',
            'type'     => 'text',
            'title'    => esc_html__('Subject', 'homey'),
            'subtitle' => esc_html__('Email subject for payout request', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('Payout Request on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_payout_request',
            'type'     => 'editor',
            'title'    => esc_html__('Content', 'homey'),
            'subtitle' => esc_html__('Email content for payout request', 'homey'),
            'desc'     => '',
            'default'  => 'Hi {host_name},<br>
We wanted to let you know that your payout request received.<br/>
<br>
Your payout will be {payout_amount}. <br>
<br>
Your payout will be processed as part of our normal schedule. You will receive an email when your payout has been processed',
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'payout-request-sent',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Payout Completed</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('you can use {host_name} as Host Name, {payout_amount} as Payout Amount, {transfer_fee} as Transaction Fee', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_payout_request_completed',
            'type'     => 'text',
            'title'    => esc_html__('Subject', 'homey'),
            'subtitle' => esc_html__('Email subject for payout request completed', 'homey'),
            'desc'     => '',
            'default'  => esc_html__('Payout Request on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_payout_request_completed',
            'type'     => 'editor',
            'title'    => esc_html__('Content', 'homey'),
            'subtitle' => esc_html__('Email content for payout request completed', 'homey'),
            'desc'     => '',
            'default'  => "Hi {host_name},<br>
It's Payday! We just processed your payout for {payout_amount} less {transfer_fee} transaction fee.
<br/>
Happy Spending!",
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'payout-request-cancelled',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Payout Cancelled</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('you can use {host_name} as Host Name, {payout_amount} as Payout Amount', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_payout_request_cancelled',
            'type'     => 'text',
            'title'    => esc_html__('Subject', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => esc_html__('Payout Request Cancelled on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_payout_request_cancelled',
            'type'     => 'editor',
            'title'    => esc_html__('Content', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => "Hi {host_name},<br>
Your payout request for {payout_amount} has been cancelled.
<br/>
Thank You",
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),


        array(
            'id'     => 'payment-adjsut-add-money',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Payment Adjustment Add Money</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('you can use {username} as User Name, {amount} as Adjustment Amount, {reason} as Reason', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_payment_add_money',
            'type'     => 'text',
            'title'    => esc_html__('Subject', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => esc_html__('Payment Adjustment on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_payment_add_money',
            'type'     => 'editor',
            'title'    => esc_html__('Content', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => "Hi {username},<br>
It is just to inform you that we have added {amount} in your balance.
<br/>
<strong>Reason:</strong> {reason}
<br/>
Thank You",
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        ),

        array(
            'id'     => 'payment-adjsut-deduct-money',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => wp_kses(__( '<span class="font24">Payment Adjustment Deduct Money</span>', 'homey' ), $allowed_html_array),
            'subtitle' => esc_html__('you can use {username} as User Name, {amount} as Adjustment Amount, {reason} as Reason', 'homey'),
        ),

        array(
            'id'       => 'homey_subject_payment_deduct_money',
            'type'     => 'text',
            'title'    => esc_html__('Subject', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => esc_html__('Payment Adjustment on {site_url}', 'homey'),
        ),
        array(
            'id'       => 'homey_payment_deduct_money',
            'type'     => 'editor',
            'title'    => esc_html__('Content', 'homey'),
            'subtitle' => '',
            'desc'     => '',
            'default'  => "Hi {username},<br>
It is just to inform you that we have deduct {amount} in your balance.
<br/>
<strong>Reason:</strong> {reason}
<br/>
Thank You",
            'args' => array(
                'teeny' => true,
                'wpautop' => false,
                'media_buttons' => false,
                'textarea_rows' => 10
            )
        )

    )
));


/* **********************************************************************
 * Page 404
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Page 404', 'homey' ),
    'id'     => 'page-404',
    'desc'   => '',
    'icon'   => 'el-icon-error el-icon-small',
    'fields'        => array(

        array(
            'id'       => '404-title',
            'type'     => 'text',
            'title'    => esc_html__( 'Page Title', 'homey' ),
            'desc'     => '',
            'default'  => 'Oh oh! Page not found.'
        ),
        array(
            'id'        => '404-des',
            'type'      => 'text',
            'title'     => esc_html__( 'Page Description', 'homey' ),
            'default'   => "We're sorry, but the page you are looking for doesn't exist.<br>
                You can search your topic using the box below or return to the homepage."
        )
    ),
));

/* **********************************************************************
 * Optimizations
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'         => esc_html__( 'Optimizations', 'homey' ),
    'id'         => 'homey_optimazation',
    'icon'       => 'el el-icon-tasks el-icon-small',
    'desc'       => '',
    'fields'     => array(
        array(
            'id'        => 'minify_js',
            'type'      => 'switch',
            'title'     => esc_html__( 'Minify JS', 'homey' ),
            'subtitle'  => esc_html__( 'Use minify version of js files', 'homey' ),
            "default"   => 0,
            'on'        => esc_html__( 'On', 'homey' ),
            'off'       => esc_html__( 'Off', 'homey' ),
        ),

        array(
            'id'        => 'minify_css',
            'type'      => 'switch',
            'title'     => esc_html__( 'Minify CSS', 'homey' ),
            'desc'  => esc_html__( 'By default the theme loads a style.css that is not minified. If you wish you can enable this setting to instead load a single style-min.css file with the code minified. If you are using a child theme you will have to change the @import from pointing to style.css to point to style-min.css', 'homey' ),
            "default"   => 0,
            'on'        => esc_html__( 'On', 'homey' ),
            'off'       => esc_html__( 'Off', 'homey' ),
        ),

        array(
            'id'        => 'remove_scripts_version',
            'type'      => 'switch',
            'title'     => __( 'Remove Version Parameter From JS & CSS Files', 'homey' ),
            'desc'  => __( 'Most scripts and style-sheets called by WordPress include a query string identifying the version. This can cause issues with caching and such, which will result in less than optimal load times. You can toggle this setting on to remove the query string from such strings.', 'homey' ),
            "default"   => 0,
            'on'        => esc_html__( 'On', 'homey' ),
            'off'       => esc_html__( 'Off', 'homey' ),
        ),

        array(
            'id'        => 'jpeg_100',
            'type'      => 'switch',
            'title'     => esc_html__( 'JPEG 100% Quality', 'homey' ),
            'desc'  => esc_html__( 'By default images cropped with WordPress are resized/cropped at 90% quality. Enable this setting to set all JPEGs to 100% quality.', 'homey' ),
            "default"   => 0,
            'on'        => esc_html__( 'On', 'homey' ),
            'off'       => esc_html__( 'Off', 'homey' ),
        )
    )
) );


 /* **********************************************************************
 * Custom Code
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'      => esc_html__( 'Custom Code', 'homey' ),
    'id'         => 'custom_code',
    'icon'       => 'el el-cog el-icon-small',
    'desc'       => '',
    'fields'     => array(
        array(
            'id'       => 'custom_css',
            'type'     => 'ace_editor',
            'title'    => esc_html__( 'CSS Code', 'homey' ),
            'subtitle' => esc_html__( 'Paste your CSS code here.', 'homey' ),
            'mode'     => 'css',
            'theme'    => 'monokai',
            'desc'     => '',
            'default'  => ""
        ),
        array(
            'id'       => 'custom_js_header',
            'type'     => 'ace_editor',
            'title'    => esc_html__( 'Custom JS Code', 'homey' ),
            'subtitle' => esc_html__( 'Custom JavaScript/Analytics Header.', 'homey' ),
            'mode'     => 'text',
            'theme'    => 'chrome',
            'desc'     => '',
            'default'  => ""
        ),
        array(
            'id'       => 'custom_js_footer',
            'type'     => 'ace_editor',
            'title'    => esc_html__( 'Custom JS Code', 'homey' ),
            'subtitle' => esc_html__( 'Custom JavaScript/Analytics Footer.', 'homey' ),
            'mode'     => 'text',
            'theme'    => 'chrome',
            'desc'     => '',
            'default'  => ""
        )
    )
) );

/* **********************************************************************
 * Footer
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'Footer', 'homey' ),
    'id'     => 'footer',
    'desc'   => '',
    'icon'   => 'el-icon-bookmark el-icon-small',
    'fields'        => array(
        array(
            'id'       => 'footer_cols',
            'type'     => 'image_select',
            'title'    => esc_html__('Footer Layout', 'homey'),
            'subtitle' => '',
            'desc'     => esc_html__('Select the footer layout', 'homey'),
            'options'  => array(
                'one_col' => array(
                    'alt' => '1 Column',
                    'img' => ReduxFramework::$_url . 'assets/img/1col.png'
                ),
                'two_col' => array(
                    'alt' => '2 Column Left',
                    'img' => ReduxFramework::$_url . 'assets/img/2cl.png'
                ),
                'three_cols_middle' => array(
                    'alt' => '3 Column Middle',
                    'img' => ReduxFramework::$_url . 'assets/img/3cm.png'
                ),
                'three_cols' => array(
                    'alt' => '3 Column Left',
                    'img' => ReduxFramework::$_url . 'assets/img/3cl.png'
                ),
                'four_cols' => array(
                    'alt' => '4 Column',
                    'img' => get_template_directory_uri() . '/images/4cl.png'
                )
            ),
            'default'  => 'three_cols'
        ),
        array(
            'id'       => 'copy_rights',
            'type'     => 'text',
            'title'    => esc_html__( 'Copyright', 'homey' ),
            'desc'     => esc_html__('Enter the copyright text', 'homey'),
            'default'  => 'homey - All rights reserved - Designed and Developed by Favethemes'
        ),
        array(
            'id'       => 'social-footer',
            'type'     => 'switch',
            'title'    => esc_html__( 'Social Media on Footer', 'homey' ),
            'desc'     => esc_html__('Enable/Disable the social media on the footer', 'homey'),
            'subtitle' => '',
            'default'  => 0,
            'on'       => 'Enabled',
            'off'      => 'Disabled',
        ),
        array(
            'id'       => 'fs-facebook',
            'type'     => 'text',
            'required' => array( 'social-footer', '=', '1' ),
            'title'    => esc_html__( 'Facebook', 'homey' ),
            'desc' => esc_html__( 'Enter your Facebook profile URL', 'homey' ),
            'desc'     => '',
            'default'  => false,
        ),
        array(
            'id'       => 'fs-twitter',
            'type'     => 'text',
            'required' => array( 'social-footer', '=', '1' ),
            'title'    => esc_html__( 'Twitter', 'homey' ),
            'desc' => esc_html__( 'Enter your Twitter profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'fs-googleplus',
            'type'     => 'text',
            'required' => array( 'social-footer', '=', '1' ),
            'title'    => esc_html__( 'Google Plus', 'homey' ),
            'desc' => esc_html__( 'Enter your Google Plus profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'fs-linkedin',
            'type'     => 'text',
            'required' => array( 'social-footer', '=', '1' ),
            'title'    => esc_html__( 'Linked In', 'homey' ),
            'desc' => esc_html__( 'Enter your Linkedin profile or business page URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'fs-instagram',
            'type'     => 'text',
            'required' => array( 'social-footer', '=', '1' ),
            'title'    => esc_html__( 'Instagram', 'homey' ),
            'desc' => esc_html__( 'Enter your Instagram profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'fs-pinterest',
            'type'     => 'text',
            'required' => array( 'social-footer', '=', '1' ),
            'title'    => esc_html__( 'Pinterest', 'homey' ),
            'desc' => esc_html__( 'Enter your Pinterest profile URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'fs-yelp',
            'type'     => 'text',
            'required' => array( 'social-footer', '=', '1' ),
            'title'    => esc_html__( 'Yelp', 'homey' ),
            'desc' => esc_html__( 'Enter your Yelp profile or page URL', 'homey' ),
            'default'  => false,
        ),
        array(
            'id'       => 'fs-youtube',
            'type'     => 'text',
            'required' => array( 'social-footer', '=', '1' ),
            'title'    => esc_html__( 'Youtube', 'homey' ),
            'desc' => esc_html__( 'Enter Youtube profile URL', 'homey' ),
            'default'  => false,
        )

    ),
));

/* **********************************************************************
 * GDPR Agreement
 * **********************************************************************/
Redux::setSection( $opt_name, array(
    'title'  => esc_html__( 'GDPR Agreement', 'homey' ),
    'id'     => 'gdpr-agreement',
    'desc'   => '',
    'icon'   => 'el-icon-bookmark el-icon-small',
    'fields'        => array(
        array(
            'id'     => 'gdpr-info-for-contact-forms',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => esc_html__( 'GDPR for contact forms and register', 'homey' ),
            'desc'   => ''
        ),
        array(
            'id'       => 'enable_forms_gdpr',
            'type'     => 'switch',
            'title'    => esc_html__( 'GDPR / Privacy Policy for forms', 'homey' ),
            'subtitle' => esc_html__( 'Enable/Disable GDPR or privay Policy checkbox for contact forms, register form', 'homey' ),
            'default'  => 0,
            'on'       => esc_html__( 'Enabled', 'homey' ),
            'off'      => esc_html__( 'Disabled', 'homey' ),
        ),

        array(
            'id'       => 'forms_gdpr_text',
            'type'     => 'text',
            'title'    => esc_html__( 'GDPR / Privacy Policy Content', 'homey' ),
            'subtitle' => esc_html__( 'Add GDPR / Privacy Policy checkbox content and link', 'homey' ),
            'required' => array('enable_forms_gdpr', '=', '1'),
            'default'  => 'I agree with your <a href="http://your-website.com/privacy-policy">Privacy Policy</a>'
        ),

        array(
            'id'       => 'forms_gdpr_validation',
            'type'     => 'text',
            'title'    => esc_html__( 'GDPR / Privacy Policy Validation Message', 'homey' ),
            'subtitle' => esc_html__( 'Add GDPR / Privacy Policy checkbox validation message', 'homey' ),
            'required' => array('enable_forms_gdpr', '=', '1'),
            'default'  => 'You need to agree with privacy policy.'
        ),

        array(
            'id'     => 'gdpr-info-for-profile',
            'type'   => 'info',
            'notice' => false,
            'style'  => 'info',
            'title'  => esc_html__( 'GDPR for profile', 'homey' ),
            'desc'   => ''
        ),
        array(
            'id'       => 'gdpr-enabled',
            'type'     => 'switch',
            'title'    => esc_html__( 'Enable/Disable GRPR on profile page.', 'homey' ),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 0,
            'on'       => 'Enabled',
            'off'      => 'Disabled',
        ),
        array(
            'id'       => 'gdpr-label',
            'type'     => 'text',
            'title'    => esc_html__( 'GDPR Label.', 'homey' ),
            'required' => array('gdpr-enabled', '=', '1'),
            'desc'     => '',
            'subtitle' => '',
            'default'  => 'I consent to having this website to store my submitted infomation, read more infomation below',
        ),
        array(
            'id'       => 'gdpr-agreement-content',
            'type'     => 'textarea',
            'title'    => esc_html__( 'GDPR Description', 'homey' ),
            'required' => array('gdpr-enabled', '=', '1'),
            'desc'     => '',
            'subtitle' => '',
            'default'  => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed risus lacus, sollicitudin at finibus at, pretium id dui. Nunc erat felis, pharetra id feugiat et, faucibus a justo. Donec eu condimentum nisi. Integer facilisis luctus massa, sit amet commodo nulla vehicula ac. Fusce vehicula nibh magna, in efficitur elit euismod eget. Quisque egestas consectetur diam, eu facilisis justo vestibulum a. Aenean facilisis volutpat orci. Mauris in pellentesque nulla. Maecenas justo felis, vestibulum non cursus sit amet, blandit et velit.

Vivamus a commodo urna. In hac habitasse platea dictumst. Ut tincidunt est sed accumsan aliquet. Sed fringilla volutpat bibendum. Nunc fermentum massa vitae iaculis pulvinar. Integer hendrerit auctor risus et luctus. Donec convallis luctus ultrices. Maecenas scelerisque sed purus ac hendrerit. Nulla vel facilisis magna.

Suspendisse hendrerit enim in tellus pharetra cursus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Integer sed laoreet nisl. Nullam feugiat ut enim id tempor. Nunc euismod nec dui at suscipit. Duis sit amet cursus nibh. Mauris tincidunt ante quis augue accumsan, quis porttitor ipsum bibendum. Vivamus congue arcu sit amet arcu imperdiet, a laoreet ligula auctor. Aliquam ultrices porttitor malesuada.

Mauris erat quam, condimentum quis lacinia sed, suscipit in nisi. Etiam eleifend tristique pellentesque. Duis a odio neque. Quisque mollis velit enim, in mollis arcu blandit vel. Praesent accumsan nisi odio, vitae semper neque faucibus in. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Aliquam pellentesque neque sem. Donec vehicula, lacus vitae gravida tempus, lorem felis faucibus est, sed dapibus velit tortor nec velit.

Aliquam convallis id metus eu venenatis. Morbi nec augue turpis. Suspendisse tincidunt massa vitae malesuada mollis. Donec suscipit feugiat porttitor. Sed pharetra auctor enim. Cras faucibus in metus eu ultrices. Mauris vitae vehicula sapien."
        )

    ),
));
