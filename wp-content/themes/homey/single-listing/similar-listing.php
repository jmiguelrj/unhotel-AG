<?php
global $homey_local;
$listings_layout = homey_option('similer_listings_layout');
$similer_count = homey_option('similer_listings_count');


$tax_query = Array ();
$term_ids = Array ();
$city_ids = Array ();
$terms = get_the_terms(get_the_ID(), 'listing_type', 'string');
$listing_city = get_the_terms( get_the_ID(), 'listing_city', 'string' );

if ( !empty( $terms ) ) :

	$term_ids = wp_list_pluck($terms, 'term_id');
	$tax_query[] = array(
		'taxonomy' => 'listing_type',
		'field' => 'id',
		'terms' => $term_ids,
		'operator' => 'IN' //Or 'AND' or 'NOT IN'
	);

endif;

if ( !empty( $listing_city ) ) :

	$city_ids = wp_list_pluck( $listing_city, 'term_id' );
	$tax_query[] = array(
		'taxonomy' => 'listing_city',
		'field' => 'id',
		'terms' => $city_ids,
		'operator' => 'IN' //Or 'AND' or 'NOT IN'
	);

endif;

$tax_count = count( $tax_query );

if ($tax_count > 1) :

    $tax_query['relation'] = 'AND';

endif;

$second_query = array(
	'post_type' => 'listing',
	'tax_query' => $tax_query,
	'posts_per_page' => $similer_count,
	'orderby' => 'rand',
	'post__not_in' => array(get_the_ID())
);

query_posts( $second_query );

if (have_posts()) :
?>
	<div id="similar-listing-section" class="similar-listing-section">
		<h2 class="title"><?php echo esc_attr(homey_option('sn_similar_label')); ?></h2>
		<div class="item-row item-<?php echo esc_attr($listings_layout); ?>-view">
			<?php
			while (have_posts()) : the_post();

				if($listings_layout == 'card') {
					get_template_part('template-parts/listing/listing-card');
				} else {
					get_template_part('template-parts/listing/listing-item');
				}

			endwhile;
			?>	
		</div>
	</div>
<?php
endif;
wp_reset_query();
?>