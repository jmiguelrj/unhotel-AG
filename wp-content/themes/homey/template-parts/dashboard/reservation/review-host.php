<?php
global $homey_local, $homey_prefix;
$reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';

$classes = '';
$review_id = get_post_meta($reservationID, 'guest_review_id', true);
$rating = get_post_meta($review_id, 'homey_guest_rating', true);
if(isset($_GET['write_review'])) {
    $classes = 'collapse in';
}
?>
<div id="review-form" class="collapse review-form-block review-form-block-user <?php echo esc_attr($classes); ?>">
    <div class="block">
        <div class="block-title">
            <h3 class="title"><?php esc_html_e('Leave a Review', 'homey'); ?></h3>
        </div><!-- block-head -->
        <div class="block-body">
            <form method="post" action="#" class="form-msg">
                <div class="review-form-block">
                    <div class="stars">
                        <div class="rating">
                            <div class="form-group">
                                <select name="rating" id="rating" class="selectpicker" data-live-search="false" title="<?php esc_attr_e('Select your rating', 'homey'); ?>">
                                    <option <?php selected($rating, '1'); ?> value="1"><?php esc_html_e('1 Star - Poor', 'homey'); ?></option>
                                    <option <?php selected($rating, '2'); ?> value="2"><?php esc_html_e('2 Star -  Fair', 'homey'); ?></option>
                                    <option <?php selected($rating, '3'); ?> value="3"><?php esc_html_e('3 Star - Average', 'homey'); ?></option>
                                    <option <?php selected($rating, '4'); ?> value="4"><?php esc_html_e('4 Star - Good', 'homey'); ?></option>
                                    <option <?php selected($rating, '5'); ?> value="5"><?php esc_html_e('5 Star - Excellent', 'homey'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                        <div class="msg-type-block">
                            <textarea name="review_content" id="review_content" class="form-control" placeholder="<?php esc_attr_e('Type your review here...', 'homey'); ?>" rows="5"><?php echo get_post_field('post_content', $review_id); ?></textarea>
                        </div>
                        <div class="form-msg-btns">
                            <input type="hidden" name="review_guest_reservation_id" id="review_guest_reservation_id" value="<?php echo intval($reservationID); ?>">

                            <input type="hidden" name="review-security" id="review-security" value="<?php echo wp_create_nonce('review-security-nonce'); ?>"/>

                            <?php if(!empty($review_id)) { ?>
                                <input type="hidden" name="review_guest_action" id="review_guest_action" value="update_guest_review">
                                <button id="add_guest_review" class="btn btn-primary btn-xs-full-width"><?php esc_html_e('Update Review', 'homey'); ?></button>
                            <?php } else { ?>
                                <input type="hidden" name="review_guest_action" id="review_guest_action" value="add_guest_review">
                                <button id="add_guest_review" class="btn btn-primary btn-xs-full-width"><?php esc_html_e('Submit Your Review', 'homey'); ?></button>
                            <?php } ?>
                        </div>
                    
                </div>
            </form>
        </div><!-- block-body -->
    </div>
</div>