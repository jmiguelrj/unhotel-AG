<?php 
global $homey_local; 
$author_picture_id = get_the_author_meta( 'homey_author_picture_id' , get_the_author_meta( 'ID' ) );

if( !empty( $author_picture_id ) ) {
    $author_picture_id = intval( $author_picture_id );
    if ( $author_picture_id ) {
        echo wp_get_attachment_image( $author_picture_id, array('36', '36'), "", array( "class" => "img-responsive img-circle" ) );
    }
}
?>
<span class="item-user-info"><?php echo esc_attr($homey_local['hosted_by']);?><br>
<?php echo get_the_author(); ?></span>