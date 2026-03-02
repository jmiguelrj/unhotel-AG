<?php
global $post, $listing_founds, $homey_local;
$sortby = $what_to_show = get_post_meta( $post->ID, 'homey_listings_sort', true );
if( isset( $_GET['sortby'] ) ) {
    $sortby = $_GET['sortby'];
}else{
    $sortby = 'x_price';
}

$rental_text = $homey_local['rental_label'];
if($listing_founds > 1) {
    $rental_text = $homey_local['rentals_label'];
}
?>
<div class="sort-wrap clearfix">
    <div class="pull-left">
        <div id="listings_found" class="number-of-listings">
            <?php echo esc_attr($listing_founds).' '.esc_html($rental_text); ?>
        </div>
    </div>
    <div class="pull-right">
        <ul class="list-inline">
            <li><strong><?php echo esc_attr($homey_local['sort_by']); ?>:</strong></li>
            <li>
                <select id="sort_listings" class="selectpicker bs-select-hidden" title="<?php esc_attr_e( 'Default Order', 'homey' ); ?>" data-live-search-style="begins" data-live-search="false">
                <option <?php if( $sortby == 'x_price' ) { echo "selected"; } ?> value="x_price"><?php esc_html_e( 'Default Order', 'homey' ); ?></option>
                <option <?php if( $sortby == 'a_price' ) { echo "selected"; } ?> value="a_price"><?php esc_html_e( 'Price (Low to High)', 'homey' ); ?></option>
                <option <?php if( $sortby == 'd_price' ) { echo "selected"; } ?> value="d_price"><?php esc_html_e( 'Price (High to Low)', 'homey' ); ?></option>

                <option <?php if( $sortby == 'd_rating' ) { echo "selected"; } ?> value="d_rating"><?php esc_html_e( 'Rating', 'homey' ); ?></option>
                
                <option <?php if( $sortby == 'featured_top' ) { echo "selected"; } ?> value="featured_top"><?php esc_html_e( 'Featured First', 'homey' ); ?></option>
                
                <option <?php if( $sortby == 'a_date' ) { echo "selected"; } ?> value="a_date"><?php esc_html_e( 'Date Old to New', 'homey' ); ?></option>
                <option <?php if( $sortby == 'd_date' ) { echo "selected"; } ?> value="d_date"><?php esc_html_e( 'Date New to Old', 'homey' ); ?></option>
            </select>
            </li>
        </ul>
    </div>
</div><!-- sort-wrap clearfix -->