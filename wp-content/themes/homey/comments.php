<?php
/**
 * The template for displaying Comments
 *
 * The area of the page that contains comments and the comment form.
 *
 * @package homey
 * @since homey 1.0
 */

/*
 * If the current post is protected by a password and the visitor has not yet
 * entered the password we will return early without loading the comments.
 */
global $homey_local;
if ( post_password_required() ) {
	return;
}
?>

<?php if ( have_comments() ) : ?>

	<div class="blog-section block">
        <div class="block-title">
            <h3 class="title"><?php esc_html_e('Comments', 'homey'); ?></h3>
        </div>
        <ul class="comments-list list-unstyled">
            <?php
			wp_list_comments( array(
				'style'      => 'ul',
				'short_ping' => true,
				'avatar_size'=> 70,
				'callback' => 'homey_comments_callback'
			) );
			?>
        </ul>
        
    </div>

    <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
		<nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
			<div class="nav-previous"><?php previous_comments_link( esc_html__( '&larr; Older Comments', 'homey' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments &rarr;', 'homey' ) ); ?></div>
		</nav>
	<?php endif; ?>

<?php endif; // have_comments() ?>

<?php if ( comments_open() ) : ?>
<div id="comments-form" class="blog-section block">
	
	<div class="block-title">
        <h3 class="title"><?php echo esc_attr($homey_local['join_discussion']); ?></h3>
    </div>

	<div class="comments-form block-body">
	<?php
	//Custom Fields
	$fields =  array(
		'author'=> '<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<div class="input-user input-icon">
									<input name="author" required class="form-control" id="author" value="" placeholder="'.esc_attr($homey_local['your_name']).'" type="text">
								</div>
							</div>
						</div>',

		'email' => '<div class="col-sm-6">
						<div class="form-group">
							<div class="input-email input-icon">
								<input type="email" class="form-control" required name="email" id="email" placeholder="'.esc_attr($homey_local['your_email']).'">
							</div>
						</div>
					</div>',

		'url' 	=> '</div>',
	);

	//Comment Form Args
	$comments_args = array(
		'fields' => $fields,
		'title_reply'=> '',
		'comment_notes_before' => '',
		'comment_notes_after' => '',
		'comment_field' => '<div class="row"><div class="col-sm-12"><div class="form-group"><textarea class="form-control" required rows="4" name="comment" placeholder="'.esc_attr($homey_local['your_comment']).'" id="comment"></textarea></div></div></div>',
		'label_submit' => $homey_local['btn_post_comment']
	);

	// Show Comment Form
	comment_form($comments_args);
	?>
	</div>
</div>
<?php endif; ?>


