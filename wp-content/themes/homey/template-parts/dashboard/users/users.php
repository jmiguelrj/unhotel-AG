<?php
$dashboard = homey_get_template_link_2('template/dashboard.php');
$all_users = add_query_arg( 'dpage', 'users', $dashboard );
$args = array(
);

$role = isset($_GET['role']) ? $_GET['role'] : '';
if(!empty($role)) {
    $args['role'] = $role;
} else {
    $args['role'] = '';
}

$search_term = isset($_GET['search']) ? $_GET['search'] : '';

if(!empty($search_term)) {

    if(is_email($search_term)) {
        $args['search']       = $search_term;
        $args['search_columns'] = array( 'user_login', 'user_email', 'display_name', 'user_nicename' );
    } else {
        $args['search'] = '*' . esc_attr( $search_term ) . '*';
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key'     => 'display_name',
                'value'   => $search_term,
                'compare' => 'LIKE'
            ),

            array(
                'key'     => 'first_name',
                'value'   => $search_term,
                'compare' => 'LIKE'
            ),
            array(
                'key'     => 'last_name',
                'value'   => $search_term,
                'compare' => 'LIKE'
            ),
            array(
                'key'     => 'description',
                'value'   => $search_term ,
                'compare' => 'LIKE'
            )
        );
    }
}

$user_query = new WP_User_Query( $args );

$users_count = count_users();
?>
<div class="user-dashboard-right dashboard-without-sidebar">
    <div class="dashboard-content-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">
                        <div class="block">
                            <div class="block-title">
                                <div class="block-left">
                                    <h2 class="title"><?php esc_html_e('Manage', 'homey'); ?></h2>
                                    <div class="mt-10">
                                        <a class="btn btn-primary btn-slim" href="<?php echo esc_url($all_users); ?>"><?php esc_html_e('All', 'homey'); ?> (<?php echo esc_attr($users_count['total_users']); ?>)</a>
                                        <?php
                                        foreach($users_count['avail_roles'] as $role => $count) {
                                            $user_link = add_query_arg( 
                                                array(
                                                    'dpage' => 'users', 
                                                    'role' => $role, 
                                                ),
                                                $dashboard 
                                            );
                                            if($role != 'none') {
                                                echo '<a class="btn btn-primary btn-slim" href="'.esc_url($user_link).'">'.homey_get_role_text($role).' ('.esc_attr($count).')</a>'.' ';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="block-right">
                                    <div class="dashboard-form-inline">
                                        <form method="get" action="<?php echo esc_url($dashboard); ?>" class="form-inline">
                                            <div class="form-group">
                                                <input name="search" type="text" class="form-control" placeholder="<?php esc_html_e('Search', 'homey'); ?>">
                                            </div>
                                            <input type="hidden" name="page" value="users">
                                            <input type="hidden" name="dpage" value="users">
                                            <button type="submit" class="btn btn-primary btn-search-icon"><i class="fa fa-search" aria-hidden="true"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="table-block dashboard-table">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo esc_html__('Avatar', 'homey'); ?></th>
                                            <th><?php echo esc_html__('Name', 'homey'); ?></th>
                                            <th><?php echo esc_html__('Email', 'homey'); ?></th>
                                            <th><?php echo esc_html__('Role', 'homey'); ?></th>
                                            <th><?php echo esc_html__('Email Verification', 'homey'); ?></th>
                                            <th><?php echo esc_html__('ID Verification', 'homey'); ?></th>
                                            <th><?php echo esc_html__('Actions', 'homey'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ( ! empty( $user_query->get_results() ) ) {
                                            foreach ( $user_query->get_results() as $user ) {
                                                $user_id = $user->ID;  
                                                $user_data = homey_get_author_by_id('40', '40', 'img-circle', $user_id);
                                                $is_superhost = $user_data['is_superhost'];

                                                $doc_verified = $user_data['doc_verified'];
                                                $user_document_id = $user_data['user_document_id'];
                                                $doc_verified_request = $user_data['doc_verified_request'];

                                                $user_role = homey_get_role_name($user_id);

                                                $single_user_link = add_query_arg( array(
                                                    'dpage' => 'users',
                                                    'user-id' => $user_id,
                                                ), $dashboard );

                                            ?>
                                                <tr>
                                                    <td data-label="<?php echo esc_html__('Avatar', 'homey'); ?>">
                                                        <?php echo ''.$user_data['photo']; ?>
                                                    </td>
                                                    <td data-label="<?php echo esc_html__('Name', 'homey'); ?>">
                                                        <?php echo esc_attr($user_data['name']); ?>
                                                    </td>
                                                    <td data-label="<?php echo esc_html__('Email', 'homey'); ?>">
                                                        <?php echo esc_attr($user_data['email']); ?>
                                                    </td>
                                                    <td data-label="<?php echo esc_html__('Role', 'homey'); ?>">
                                                        <?php if($is_superhost) { ?>
                                                        <i class="fa fa-bookmark host_role" aria-hidden="true"></i> 
                                                        <?php } ?>
                                                        <?php echo esc_attr($user_role); ?>
                                                    </td>
                                                    <td data-label="<?php echo esc_html__('Email Verification', 'homey'); ?>">
                                                        <?php
                                                        $is_email_verified = get_the_author_meta( 'is_email_verified', $user_id );
                                                          if($is_email_verified) {
                                                            echo '<span class="label label-success">'.esc_html__('VERIFIED', 'homey').'</span>';
                                                        } else {
                                                             echo '<span class="label label-warning">'.esc_html__('PENDING', 'homey').'</span>';
                                                        }?>
                                                    </td>
                                                    <td data-label="<?php echo esc_html__('ID Verification', 'homey'); ?>">
                                                        <?php 
                                                        if($doc_verified) { 
                                                            echo '<span class="label label-success">'.esc_html__('VERIFIED', 'homey').'</span>';
                                                        } else {

                                                            if($doc_verified_request == '') {
                                                                echo '<span>-</span>';
                                                            } else {
                                                                echo '<span class="label label-warning">'.esc_html__('PENDING', 'homey').'</span>';
                                                            }
                                                        }
                                                        ?>
                                                        
                                                    </td>
                                                    <td data-label="<?php echo esc_html__('Actions', 'homey'); ?>">
                                                        <div class="custom-actions">
                                                            <a class="btn btn-success" href="<?php echo esc_url($single_user_link); ?>"><?php esc_html_e('Detail', 'homey'); ?></a>
                                                        </div>
                                                    </td>
                                                </tr>

                                        <?php        
                                            }
                                        } else {
                                            echo '<tr><td colspan="7">';
                                            echo esc_html__('No users found.', 'homey');
                                            echo '</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div><!-- .block -->
                        <?php //include 'inc/listing/pagination.php';?>
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
    </div><!-- .dashboard-content-area --> 
</div><!-- .user-dashboard-right -->