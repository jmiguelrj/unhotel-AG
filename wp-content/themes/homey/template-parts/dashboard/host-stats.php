<?php
global $wpdb, $current_user, $userID, $homey_local, $homey_threads, $author;
$reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
$submission_page_link = homey_get_template_link('template/dashboard-submission.php');
$wallet_page_link = homey_get_template_link('template/dashboard-wallet.php');
$total_earnings = $author['total_earnings'];
$enable_wallet = homey_option('enable_wallet');
if($enable_wallet != 0) {
    $block_class = "block-col-33";
} else {
    $block_class = "block-col-50";
}
?>
<div class="block">

    <div class="block-head">
        <h2 class="title text-center"><?php echo esc_attr($homey_local['welcome_back_text']); ?> <?php echo esc_attr($author['name']); ?> </h2>
    </div>
    <div class="block-verify">
        <div class="block-col <?php echo esc_attr($block_class); ?>">
            <h3><?php echo esc_attr($homey_local['pr_listing_label']); ?></h3>
            <p class="block-big-text"><?php echo esc_attr(isset($author['listing_count'])?$author['listing_count']:0); ?></p>
            <a href="<?php echo esc_url($submission_page_link); ?>"><?php esc_html_e('Add New', 'homey'); ?></a>
        </div>
        <div class="block-col <?php echo esc_attr($block_class); ?>">
            <h3><?php echo esc_attr($homey_local['pr_resv_label']); ?></h3>
            <p class="block-big-text"><?php echo homey_reservation_count($userID); ?></p>
            <a href="<?php echo esc_url($reservation_page_link); ?>"><?php esc_html_e('Manage', 'homey'); ?></a>
        </div>
        <?php if($enable_wallet != 0) { ?>
        <div class="block-col <?php echo esc_attr($block_class); ?>">
            <h3><?php esc_html_e('Earnings', 'homey'); ?></h3>
            <p class="block-big-text">
                <?php 
                if($total_earnings != 0) {
                    echo homey_formatted_price($total_earnings); 
                } else {
                    echo homey_simple_currency_format($total_earnings);
                }
                ?>
            </p>
            <a href="<?php echo esc_url($wallet_page_link); ?>"><?php esc_html_e('Wallet', 'homey'); ?></a>
        </div>
        <?php } ?>
    </div>

</div>