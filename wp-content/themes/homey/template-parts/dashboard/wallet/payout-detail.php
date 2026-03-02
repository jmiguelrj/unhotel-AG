<?php
global $reservation_page_link,
$wallet_page_link,
$earnings_page_link,
$payout_request_link,
$payouts_page_link;
$payout_id = $_GET['payout_id'];

$payout_access = homey_signle_payout_access($payout_id);

$data = homey_signle_payout($payout_id);
$payout_id = $data->payout_id;
$host_id = $data->user_id;
$total_amount = $data->total_amount;
$payout_method = $data->payout_method;
$payout_method_data = $data->payout_method_data;
$payout_beneficiary = $data->payout_beneficiary;
$ben = json_decode($payout_beneficiary);
$payout_status = $data->payout_status;
$transfer_fee = $data->transfer_fee;
$transfer_note = $data->note;
$payout_action = $data->action;
$date_requested = $data->date_requested;
$date_processed = $data->date_processed;

$date_requested_unix = strtotime($date_requested);
$request_date = homey_return_formatted_date($date_requested_unix);
$request_time = homey_get_formatted_time($date_requested_unix);

$date_processed_unix = strtotime($date_processed);
$processed_date = homey_return_formatted_date($date_processed_unix);
$processed_time = homey_get_formatted_time($date_processed_unix);

$price_prefix = '';
if($payout_action == 'add_money') {
    $price_prefix = '+';
} elseif($payout_action == 'deduct_money') {
    $price_prefix = '-';
}

if( $payout_access || homey_is_admin() ) {
?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <div class="dashboard-content-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">

                        <div class="block">
                            <div class="block-head">
                                <div class="block-left">
                                    <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>
                                </div><!-- block-left -->
                                <div class="block-right">
                                     <div class="custom-actions">
                                        <button class="btn-action" onclick="window.location.href='<?php echo esc_url($payouts_page_link); ?>'" data-toggle="tooltip" data-placement="top" title="<?php esc_html_e('Back', 'homey'); ?>" data-original-title="<?php esc_html_e('Back', 'homey'); ?>"><i class="fa fa-reply"></i></button>
                                    </div><!-- custom-actions -->
                                    
                                </div><!-- block-right -->
                            </div><!-- block-head -->

                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Payout', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <div class="payment-list">
                                            <ul class="list-unstyled list-lined">
                                                <li>
                                                    <strong><?php esc_html_e('ID', 'homey'); ?>:</strong> 
                                                    <span><?php echo esc_attr($payout_id); ?></span>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('Amount', 'homey'); ?>:</strong>
                                                    <span><?php echo esc_attr($price_prefix).' '.homey_formatted_price($total_amount); ?></span>
                                                </li>
                                                <?php if(!empty($transfer_fee)) { ?>
                                                <li>
                                                    <strong></strong>
                                                    <span class="less-fee">
                                                        (<?php esc_html_e('less', 'homey');  
                                                        echo ' '.homey_formatted_price($transfer_fee).' '; 
                                                        esc_html_e('transaction fee', 'homey'); ?>)
                                                    </span>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->  
                            
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Date', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <div class="payment-list">
                                            <ul class="list-unstyled list-lined">
                                                <li>
                                                    <strong><?php esc_html_e('Date Requested', 'homey'); ?>:</strong>
                                                     <span>
                                                        <?php echo homey_format_date_simple(esc_attr($date_requested)); ?><br/>
                                                        <?php echo esc_html__('at', 'homey'); ?>
                                                        <?php echo esc_attr($request_time); ?>
                                                     </span>
                                                 </li>
                                                <li>
                                                    <strong><?php esc_html_e('Date Processed', 'homey'); ?>:</strong>
                                                    <span>
                                                        <?php 
                                                        if($date_processed == '0000-00-00 00:00:00') {
                                                            echo '-';
                                                        } else {
                                                            echo homey_format_date_simple(esc_attr($date_processed)).'<br/>';
                                                            echo esc_html__('at', 'homey');
                                                            echo ' '.esc_attr($processed_time);
                                                        }
                                                        ?>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->  

                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title">
                                            <?php 
                                            $role = homey_user_role_by_user_id($host_id);
                                            if($role == 'homey_host') {
                                                esc_html_e('Host', 'homey'); 
                                            } elseif($role == 'homey_renter') {
                                                esc_html_e('Guest', 'homey');
                                            } else {
                                                esc_html_e('Host', 'homey'); 
                                            }
                                            ?>
                                                
                                        </h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <div class="payment-list">
                                            <ul class="list-unstyled list-lined">
                                                <li>
                                                    <strong><?php esc_html_e('Name', 'homey'); ?>:</strong>
                                                    <span><?php echo get_the_author_meta( 'display_name' , $host_id ); ?></span>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('Email', 'homey'); ?>:</strong>
                                                    <span><?php echo get_the_author_meta( 'email' , $host_id ); ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->  

                            <?php if($payout_action == 'host_payout') { ?>
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Method', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <ul class="list-unstyled">
                                            <li><strong><?php esc_html_e('Method', 'homey'); ?></strong> <?php echo homey_get_payout_method($payout_method); ?></li>
                                        </ul>
                                        <ul class="list-unstyled list-lined">
                                            <li>
                                                <strong><?php esc_html_e('Beneficiary Name', 'homey'); ?></strong> 
                                                <?php echo esc_attr(@$ben->ben_first_name).' '.esc_attr(@$ben->ben_last_name); ?>
                                            </li>

                                            <?php if(!empty(@$ben->ben_company_name)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Company Name', 'homey'); ?></strong> 
                                                <?php echo esc_attr(@$ben->ben_company_name); ?>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty(@$ben->ben_tax_number)) { ?>
                                            <li>
                                                <strong><?php esc_html_e('Tax Identification Number', 'homey'); ?></strong> 
                                                <?php echo esc_attr(@$ben->ben_tax_number); ?>
                                            </li>
                                            <?php } ?>
                                        </ul>

                                        <ul class="list-unstyled list-lined">
                                            <li>
                                                <strong><?php esc_html_e('Address', 'homey'); ?></strong> 
                                                <?php echo esc_attr(@$ben->ben_street_address).', '.@$ben->ben_city.', '.@$ben->ben_state.', '.@$ben->ben_zip_code; ?>
                                            </li>
                                        </ul>

                                        <ul class="list-unstyled list-lined mb-0">
                                            <?php if($payout_method == 'paypal') { ?>

                                                    <li>
                                                        <strong><?php esc_html_e('PayPal Email', 'homey'); ?></strong> 
                                                        <?php echo esc_attr($payout_method_data); ?>
                                                    </li>

                                            <?php } elseif ($payout_method == 'skrill') { ?>

                                                    <li>
                                                        <strong><?php esc_html_e('Skrill Email', 'homey'); ?></strong> 
                                                        <?php echo esc_attr($payout_method_data); ?>
                                                    </li>
                                                
                                            <?php } elseif ($payout_method == 'wire') { 
                                                    $bankInfo = json_decode($payout_method_data);
                                                ?>

                                                <li>
                                                    <strong><?php esc_html_e('Beneficiary Account Number', 'homey'); ?></strong>
                                                     <?php echo esc_attr($bankInfo->bank_account); ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('SWIFT', 'homey'); ?></strong> 
                                                    <?php echo esc_attr($bankInfo->swift); ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('Bank Name', 'homey'); ?></strong> 
                                                    <?php echo esc_attr($bankInfo->bank_name); ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e('Bank Address', 'homey'); ?></strong> 
                                                    <?php echo esc_attr($bankInfo->wir_street_address).', '.esc_attr($bankInfo->wir_city).', '.esc_attr($bankInfo->wir_state).', '.esc_attr($bankInfo->wir_zip_code);?>
                                                </li>
                                                
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div><!-- block-body -->
                            </div><!-- block-section -->  
                            <?php } else { ?>
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Purpose', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <ul class="list-unstyled">
                                            <li><?php esc_html_e('Payment Adjustment', 'homey'); ?></li>
                                        </ul>
                                        <ul class="list-unstyled">
                                            <li><?php echo ''.$transfer_note; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <?php } ?>
                            
                        </div><!-- .block -->
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
    </div><!-- .dashboard-content-area -->   
    <aside class="dashboard-sidebar admin-dashboard-sidebar">
        
        <div class="admin-sidebar">
            <div class="user-sidebar-inner">
                <div class="block">
                    <div class="block-body">
                        <h3><?php esc_html_e('Information', 'homey'); ?></h3>
                        <ul class="list-unstyled margin-0">
                            <li><?php esc_html_e('Status', 'homey'); ?>: <strong><?php echo homey_get_payout_status($payout_status); ?> </strong></li>
                        </ul>
                    </div>
                </div>

                <?php 
                if(homey_is_admin() && $payout_action == 'host_payout') {
                    if($payout_status != 4) { ?>
                    <div class="block">
                        <div class="block-body">
                            <h3><?php esc_html_e('Actions', 'homey'); ?></h3>
                            <div class="form-group">
                                <label><?php esc_html_e('Change the payout status', 'homey'); ?></label>
                                <select id="payout_status" class="selectpicker" data-live-search="false">
                                    <option <?php selected($payout_status, 1); ?> value="1"><?php echo homey_option('payout_pending_label'); ?></option>
                                    <option <?php selected($payout_status, 2); ?> value="2"><?php echo homey_option('payout_inprogress_label'); ?></option>
                                    <option <?php selected($payout_status, 3); ?> value="3"><?php echo homey_option('payout_completed_label'); ?></option>
                                    <option <?php selected($payout_status, 4); ?> value="4"><?php echo homey_option('payout_cancel_label'); ?></option>
                                </select>
                                <input type="hidden" id="payout_id" value="<?php echo intval($payout_id); ?>">
                                <?php wp_nonce_field( 'homey_payout_status_nonce', 'homey_payout_status_security' ); ?>
                            </div>
                            <div class="form-group transfer_fee">
                                <label><?php esc_html_e('Transfer Fee', 'homey'); ?></label>
                                <input class="form-control" type="text" name="transfer_fee" id="transfer_fee" value="<?php echo esc_attr($transfer_fee); ?>" placeholder="<?php esc_html_e('Enter transfer fee', 'homey'); ?>">
                            </div>
                        
                            <button id="homey_change_payout_status" class="btn btn-success btn-full-width">
                                <?php esc_html_e('Save', 'homey'); ?>   
                            </button>
                        </div>
                    </div>
                    <?php } 
                }?>
            </div>
        </div>

    </aside><!-- .dashboard-sidebar --> 
    </div><!-- .user-dashboard-right -->
    <?php } ?>
