<?php
global $homey_local, $homey_prefix;

$get_facilities = array();
$get_facilities = isset ( $_GET['facility'] ) ? $_GET['facility'] : $get_facilities;

if( taxonomy_exists('listing_facility') ) {
    $facilities = get_terms(
        array(
            "listing_facility"
        ),
        array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        )
    );
    $facilities_count = count($get_facilities);
    $checked_facility = '';
    $count = 0;
    if (!empty($facilities)) { ?>

        <div class="filters-wrap">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                    <div class="filters">
                        <strong><?php echo esc_attr(homey_option('srh_facilities')); ?></strong>
                    </div>
                </div>
                <div class="facilities-list col-xs-12 col-sm-12 col-md-9 col-lg-9">

                    <?php
                    $total_facilities = count($facilities);
                    $id_conflict_resolver = random_int(0, 999);

                    foreach ($facilities as $facility):
                        $count++;

                        if (in_array($facility->slug, $get_facilities)) {
                            $checked_facility = $facility->slug;
                        }

                        if($count == 1) {
                            echo '<div class="filters">';
                        }

                        if($count == 7) {
                            echo '<div class="collapse" id="collapseFacilities'.$id_conflict_resolver.'">
                                    <div class="filters">';
                        }
                            echo '<label class="control control--checkbox">';
                                echo '<input name="facility[]" type="checkbox" '.checked( $checked_facility, $facility->slug, false ).' value="' . esc_attr( $facility->slug ) . '">';
                                echo '<span class="contro-text">'.esc_attr( $facility->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';

                        if( ($count == 6) || ($count < 6 && $count == $total_facilities) ) {    
                            echo '</div>';
                        }

                        if( ($count > 6) && ($count == $total_facilities) ) {
                            echo '</div></div>';
                        }

                    endforeach;
                    ?>
                </div>

                <?php if($total_facilities > 6 ) { ?>
                <div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
                    <div class="filters">
                        <a role="button" data-toggle="collapse" data-target="#collapseFacilities<?php echo $id_conflict_resolver;?>" aria-expanded="false" aria-controls="collapseFacilities<?php echo $id_conflict_resolver;?>">
                            <span class="filter-more-link"><?php echo esc_attr($homey_local['search_more']); ?></span> 
                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i> 
                        </a>
                    </div>
                </div>
                <?php } ?>

            </div><!-- featues row -->
        </div><!-- .filters-wrap -->

    <?php    
    }
}
?>