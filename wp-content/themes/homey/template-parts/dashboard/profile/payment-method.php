<?php
global $current_user, $post, $homey_local;
$current_user = wp_get_current_user();
$userID       = $current_user->ID;
$user_data = homey_get_author_by_id('36', '36', 'img-responsive img-circle', $userID);
$payout_payment_method = $user_data['payout_payment_method'];

$enable_wallet = homey_option('enable_wallet');
$reservation_payment = homey_option('reservation_payment');

$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$payout_request_link = add_query_arg( 'dpage', 'payout-request', $wallet_page_link );

?>
<div class="date-saved-success alert alert-success alert-dismissible" role="alert" style="display: none;">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <?php echo wp_kses(__( '<strong>Congratulation!</strong> Your data has been saved.', 'homey' ), homey_allowed_html() ); ?>
</div>

<div class="validate-errors alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <?php echo wp_kses(__( '<strong>Error!</strong> Please fill out the required fields.', 'homey' ), homey_allowed_html() ); ?>
</div>

<div class="row">
    <div class="col-lg-8 col-md-8 col-sm-12">

    </div>
    <div class="col-lg-4 col-md-4 col-sm-12"></div>
</div>

<div class="block define-payout-methods">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php esc_html_e('Beneficiary Information', 'homey'); ?></h2>
        </div>

        <?php
        if($enable_wallet != 0) {
            if($reservation_payment == 'percent' || $reservation_payment == 'full') {?>
                <div class="block-right">
                    <a href="<?php echo esc_url($payout_request_link); ?>" class="btn btn-primary btn-slim"><?php esc_html_e('Request a Payout', 'homey'); ?></a>
                </div>
            <?php } 
        }?>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php esc_html_e('First name', 'homey'); ?></label>
                    <input type="text" id="ben_first_name" value="<?php echo esc_attr($user_data['ben_first_name']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter your name', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php esc_html_e('Last Name', 'homey'); ?></label>
                    <input type="text" id="ben_last_name" value="<?php echo esc_attr($user_data['ben_last_name']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter last name', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php esc_html_e('Company Name', 'homey'); ?></label>
                    <input type="text" id="ben_company_name" value="<?php echo esc_attr($user_data['ben_company_name']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter the company name', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php esc_html_e('Tax Identification Number', 'homey'); ?></label>
                    <input type="text" id="ben_tax_number" value="<?php echo esc_attr($user_data['ben_tax_number']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter tax identification number', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-9">
                <div class="form-group">
                    <label for="ben_street_address"><?php esc_html_e('Street Address', 'homey'); ?></label>
                    <input type="text" id="ben_street_address" value="<?php echo esc_attr($user_data['ben_street_address']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter street address', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="ben_apt_suit"> <?php esc_html_e('Apt, Suite', 'homey'); ?> </label>
                    <input type="text" id="ben_apt_suit" value="<?php echo esc_attr($user_data['ben_apt_suit']); ?>" class="form-control" placeholder="<?php esc_html_e('Ex. #123', 'homey'); ?> ">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="ben_city"><?php esc_html_e('City', 'homey'); ?></label>
                    <input type="text" id="ben_city" value="<?php echo esc_attr($user_data['ben_city']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter your city', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="ben_state"><?php esc_html_e('State', 'homey'); ?></label>
                    <input type="text" id="ben_state" value="<?php echo esc_attr($user_data['ben_state']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter your state/country', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="ben_zip_code"><?php esc_html_e('Zip/Post Code', 'homey'); ?></label>
                    <input type="text" id="ben_zip_code" value="<?php echo esc_attr($user_data['ben_zip_code']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter zip/post code', 'homey'); ?>">
                </div>
            </div>
        </div>
    </div>
</div>


<div class="block define-payout-methods">
    <div class="block-title">
        <h2 class="title"><?php esc_html_e('Select Your Payout Method', 'homey'); ?></h2>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-4 col-xs-12">
                <div class="form-group">
                    <label class="control control--radio radio-tab">
                        <input class="choose_payout_method" <?php checked($payout_payment_method, 'paypal'); ?> type="radio" name="payout_method" value="paypal">
                        <span class="control-text"><?php esc_html_e('Paypal', 'homey'); ?></span>
                        <span class="control__indicator"></span>
                        <span class="radio-tab-inner"></span>
                    </label>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="form-group">
                    <label class="control control--radio radio-tab">
                        <input class="choose_payout_method" <?php checked($payout_payment_method, 'skrill'); ?> type="radio" name="payout_method" value="skrill">
                        <span class="control-text"><?php esc_html_e('Skrill', 'homey'); ?></span>
                        <span class="control__indicator"></span>
                        <span class="radio-tab-inner"></span>
                    </label>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="form-group">
                    <label class="control control--radio radio-tab">
                        <input class="choose_payout_method" <?php checked($payout_payment_method, 'wire'); ?> type="radio" name="payout_method" value="wire">
                        <span class="control-text"><?php esc_html_e('Wire Transfer', 'homey'); ?></span>
                        <span class="control__indicator"></span>
                        <span class="radio-tab-inner"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="wire_transfer" class="block define-payout-methods" style="display: none;">
    <div class="block-title">
        <h2 class="title"><?php esc_html_e('Wire Transfer Information', 'homey'); ?></h2>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php esc_html_e('Beneficiary Account Number', 'homey'); ?></label>
                    <input type="text" id="bank_account" value="<?php echo esc_attr($user_data['bank_account']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter your bank account number', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php esc_html_e('SWIFT or IBN', 'homey'); ?></label>
                    <input type="text" id="swift" value="<?php echo esc_attr($user_data['swift']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter the SWIFT code', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label><?php esc_html_e('Bank Name', 'homey'); ?></label>
                    <input type="text" id="bank_name" value="<?php echo esc_attr($user_data['bank_name']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter the bank name', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-9">
                <div class="form-group">
                    <label for="wir_street_address"><?php esc_html_e('Street Address', 'homey'); ?></label>
                    <input type="text" id="wir_street_address" value="<?php echo esc_attr($user_data['wir_street_address']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter street address', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="wir_aptsuit"> <?php esc_html_e('Apt, Suite', 'homey'); ?> </label>
                    <input type="text" id="wir_aptsuit" value="<?php echo esc_attr($user_data['wir_aptsuit']); ?>" class="form-control" placeholder=" <?php esc_html_e('Ex. #123', 'homey'); ?> ">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="wir_city"><?php esc_html_e('City', 'homey'); ?></label>
                    <input type="text" id="wir_city" value="<?php echo esc_attr($user_data['wir_city']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter your city', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="wir_state"><?php esc_html_e('State', 'homey'); ?></label>
                    <input type="text" id="wir_state" value="<?php echo esc_attr($user_data['wir_state']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter your country/state', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="wir_zip_code"><?php esc_html_e('Zip/Post Code', 'homey'); ?></label>
                    <input type="text" id="wir_zip_code" value="<?php echo esc_attr($user_data['wir_zip_code']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter zip/post code', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-12 text-right">
                <button type="submit" class="homey_save_payout_method btn btn-success btn-xs-full-width"><?php esc_html_e('Save', 'homey'); ?></button>
            </div>
        </div>
    </div>
</div><!-- .block -->

<?php wp_nonce_field( 'homey_payout_method_nonce', 'homey_payout_method_security' ); ?>

<div id="paypal" class="block define-payout-methods" style="display: none;">
    <div class="block-title">
        <h2 class="title"><?php esc_html_e('PayPal Account Information', 'homey'); ?></h2>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label><?php esc_html_e('Recipient Email', 'homey'); ?></label>
                    <input type="text" id="paypal_email" value="<?php echo esc_attr($user_data['payout_paypal_email']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter your PayPal email', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-12 text-right">
                <button type="submit" class="homey_save_payout_method btn btn-success btn-xs-full-width"><?php esc_html_e('Save', 'homey'); ?></button>
            </div>
        </div>
    </div>
</div><!-- .block -->

<div id="skrill" class="block define-payout-methods" style="display: none;">
    <div class="block-title">
        <h2 class="title"><?php esc_html_e('Skrill Account Information', 'homey'); ?></h2>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label><?php esc_html_e('Recipient Email', 'homey'); ?></label>
                    <input type="text" id="skrill_email" value="<?php echo esc_attr($user_data['payout_skrill_email']); ?>" class="form-control" placeholder="<?php esc_html_e('Enter your Skrill email', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-12 text-right">
                <button type="submit" class="homey_save_payout_method btn btn-success btn-xs-full-width"><?php esc_html_e('Save', 'homey'); ?></button>
            </div>
        </div>
    </div>
</div><!-- .block -->