<?php
/**
 * Template Name: Dashboard Messages
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

get_header();
global $current_user, $wpdb, $userID, $homey_threads;

$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'DESC';
$messages_page = homey_get_template_link('template/dashboard-messages.php');
$mine_messages_link = add_query_arg( 'mine', '1', $messages_page );

wp_get_current_user();
$userID = $current_user->ID;

$tabel = $wpdb->prefix . 'homey_threads';
//pagination related meta data
$items_per_page = isset( $_GET['per_page'] ) ? abs( (int) $_GET['per_page'] ) : 25;
$page = (get_query_var('paged')) ? get_query_var('paged') : 1;
$offset = ( $page * $items_per_page ) - $items_per_page;
// end pagination related meta data

if(homey_is_admin()) {
    if(isset($_GET['mine'])) {
        $total_query = $wpdb->prepare(
            "
            SELECT COUNT(sender_id) as total_results
            FROM $tabel 
            WHERE sender_id = %d OR receiver_id = %d
            ORDER BY id ".$sort."
             LIMIT %d, %d
            ",
            $userID,
            $userID,
            $offset,
            $items_per_page
        );

        $message_query = $wpdb->prepare(
            "
            SELECT * 
            FROM $tabel 
            WHERE sender_id = %d OR receiver_id = %d
            ORDER BY id ".$sort."
            LIMIT %d, %d
            ",
            $userID,
            $userID,
            $offset,
            $items_per_page
        );
    } else {
        $total_query = 'SELECT COUNT(sender_id) as total_results
        FROM '.$tabel.' 
        ORDER BY id '.$sort;

        $message_query = 'SELECT * 
        FROM '.$tabel.' 
        ORDER BY id '.$sort.' LIMIT '.$offset.', '.$items_per_page;
    }

} else {
    $total_query = $wpdb->prepare(
        "
        SELECT COUNT(sender_id) as total_results
        FROM $tabel 
        WHERE sender_id = %d OR receiver_id = %d
        ORDER BY id ".$sort."
        ",
        $userID,
        $userID
    );

    $message_query = $wpdb->prepare(
        "
        SELECT * 
        FROM $tabel 
        WHERE sender_id = %d OR receiver_id = %d
        ORDER BY id ".$sort." LIMIT %d, %d
        ",
        $userID,
        $userID,
        $offset,
        $items_per_page
    );
}

$total_result = $wpdb->get_results( $total_query );
$total_pages = absint((isset($total_result[0]->total_results)?$total_result[0]->total_results:1)/$items_per_page);
$homey_threads = $wpdb->get_results( $message_query );
?>


    <section id="body-area">

        <div class="dashboard-page-title">
            <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
        </div><!-- .dashboard-page-title -->

        <?php get_template_part('template-parts/dashboard/side-menu'); ?>

        <div class="user-dashboard-right dashboard-without-sidebar">
            <div class="dashboard-content-area">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="dashboard-area">

                                <?php
                                if(isset($_GET['message']) && $_GET['message'] == 'new') {
                                    get_template_part('template-parts/dashboard/messages/new');
                                } else { ?>

                                    <?php if ( isset( $_REQUEST['thread_id'] ) && !empty( $_REQUEST['thread_id'] ) ) {
                                        get_template_part('template-parts/dashboard/messages/detail');

                                    } else {
                                        ?>
                                        <div class="block">
                                            <div class="block-title">
                                                <div class="block-left">
                                                    <h2 class="title"><?php echo esc_html__('From', 'homey'); ?></h2>
                                                    <?php if(homey_is_admin()) { ?>
                                                        <div class="mt-10">
                                                            <a class="btn btn-primary btn-slim" href="<?php echo esc_url($messages_page); ?>"><?php esc_html_e('All', 'homey'); ?></a>
                                                            <a class="btn btn-primary btn-slim" href="<?php echo esc_url($mine_messages_link); ?>"><?php esc_html_e('Mine', 'homey'); ?></a>
                                                        </div>
                                                    <?php } ?>
                                                </div>

                                                <div class="block-right">
                                                    <form>
                                                        <label for="" class="title"><?php echo esc_html__('Sort', 'homey'); ?></label>
                                                        <select onchange="this.form.submit();" name="sort">
                                                            <option <?php echo @$_REQUEST['sort'] == 'asc' ? 'selected' : ''; ?> value="asc">ASC</option>
                                                            <option <?php echo @$_REQUEST['sort'] == 'desc' ? 'selected' : ''; ?> value="desc">DESC</option>
                                                        </select>
                                                    </form>
                                                </div>
                                            </div>

                                            <?php if ( sizeof( $homey_threads ) != 0 ) { ?>
                                                <div class="table-block dashboard-message-table">
                                                    <?php get_template_part('template-parts/dashboard/messages/messages');  ?>
                                                </div><!-- .table-block -->
                                            <?php } else { ?>
                                                <div class="block-body">
                                                    <?php esc_html_e("You don't have any message at this moment.", 'homey'); ?>
                                                </div>
                                            <?php } ?>
                                        </div><!-- .block -->
                                        <?php
                                    }
                                }
                                ?>
                            </div><!-- .dashboard-area -->
                            <!--start pagination-->
                            <?php homey_pagination($total_pages); ?>
                            <!--end pagination-->
                        </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                    </div>
                </div><!-- .container-fluid -->
            </div><!-- .dashboard-content-area -->
        </div><!-- .user-dashboard-right -->

    </section><!-- #body-area -->


<?php get_footer();?>
