<?php
$currency_switcher_enable = homey_option('currency_converter');
$default_currency = homey_option('default_currency');
$is_multi_currency = 0;

if( $currency_switcher_enable != 0 && $is_multi_currency != 1 ) {
    if (class_exists('FCC_Currencies')) {

        $supported_currencies = '';

        $currencies = FCC_Currencies::get_currency_codes();

        $current_currency = homey_get_wpc_current_currency();

        if($currencies) {
            foreach ($currencies as $currency) {
                $supported_currencies .='<li data-currency-code="' . esc_attr($currency->currency_code) . '">'.esc_attr($currency->currency_code).'</li>';
            }
        }

        echo '<ul class="crncy-lang-block list-unstyled">';
            echo '<li class="currency-menu">';
                echo '<a class="dropdown-toggle homey-selected-currency" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" role="button" id="crn-dropdown"><span>'.esc_attr($current_currency).'</span> <i class="fa fa-angle-down"></i></a>';
                echo '<ul id="homey-currency-switcher-list" class="dropdown-menu" aria-labelledby="crn-dropdown">';
                    echo  ''.$supported_currencies;
                echo '</ul>';
            echo '</li>';
        echo '</ul>';
    }
}
?>