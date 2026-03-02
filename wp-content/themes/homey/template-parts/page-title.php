<div class="page-title">
    <div class="block-top-title">
        <?php get_template_part('template-parts/breadcrumb'); ?>
        <h1 class="listing-title">
        	<?php
                if (is_category()) {
                    single_cat_title();

                } elseif(is_tag()) {
                    single_tag_title();

                }  elseif(is_tax()) {
                    single_term_title();

                } elseif (is_day()) {
                    printf( esc_html__( '%s', 'homey' ), get_the_date() );

                } elseif (is_month()) {
                    printf( esc_html__( '%s', 'homey' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'homey' ) ) );

                } elseif (is_year()) {
                    printf( esc_html__( '%s', 'homey' ), get_the_date( _x( 'Y', 'yearly archives date format', 'homey' ) ) );

                } elseif ( get_post_format() ) {
                    echo get_post_format();

                } elseif (is_author()) {
                    esc_html_e('Author Archive', 'homey');
                }
                elseif ( is_post_type_archive() ) {
                    echo post_type_archive_title();
                } 
                elseif( !is_front_page() ) {
                   the_title();
                }
                else {
                    if( !is_front_page() ) {
                       the_title();
                    }
                }
            ?>
        </h1>
    </div><!-- block-top-title -->
</div><!-- page-title -->