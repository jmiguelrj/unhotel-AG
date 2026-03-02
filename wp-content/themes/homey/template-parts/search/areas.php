<?php
global $homey_local, $homey_prefix;

$get_areas = array();
$get_areas = isset ( $_GET['area'] ) ? $_GET['area'] : $get_areas;

if( taxonomy_exists('listing_area') ) {
    $areas = get_terms(
        array(
            "listing_area"
        ),
        array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        )
    );
    $areas_count = count($get_areas);
    $checked_area = '';
    $count = 0;
    if (!empty($areas)) { ?>

        <div class="filters-wrap">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                    <div class="filters">
                        <strong><?php echo esc_attr($homey_local['search_area']); ?></strong>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">

                    <?php
                    $total_areas = count($areas);
                    foreach ($areas as $area):
                        $count++;

                        if (in_array($area->slug, $get_areas)) {
                            $checked_area = $area->slug;
                        }

                        if($count == 1) {
                            echo '<div class="filters">';
                        }

                        if($count == 7) {
                            echo '<div class="collapse" id="collapseAreas">
                                    <div class="filters">';
                        }
                            echo '<label class="control control--checkbox">';
                                echo '<input name="area[]" type="checkbox" '.checked( $checked_area, $area->slug, false ).' value="' . esc_attr( $area->slug ) . '">';
                                echo '<span class="contro-text">'.esc_attr( $area->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';

                        if( ($count == 6) || ($count < 6 && $count == $total_areas) ) {    
                            echo '</div>';
                        }

                        if( ($count > 6) && ($count == $total_areas) ) {
                            echo '</div></div>';
                        }

                    endforeach;
                    ?>
                </div>

                <?php if($total_areas > 6 ) { ?>
                <div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
                    <div class="filters">
                        <a role="button" data-toggle="collapse" href="#collapseAreas" aria-expanded="false" aria-controls="collapseAreas">
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