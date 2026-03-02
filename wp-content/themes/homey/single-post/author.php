<?php
$author_info = homey_get_author('70', '70', 'img-circle img-responsive');
$facebook = $author_info['facebook'];
$twitter = $author_info['twitter'];
$linkedin = $author_info['linkedin'];
$pinterest = $author_info['pinterest'];
$instagram = $author_info['instagram'];
$googleplus = $author_info['googleplus'];
$youtube = $author_info['youtube'];
$vimeo = $author_info['vimeo'];

if(!empty($author_info['bio'])) {
?>
<div class="blog-section block">
    <div class="author-detail-block block-body">
        <div class="media">
            <div class="media-left">
                <a class="media-object" href="#">
                    <?php echo ''.$author_info['photo']; ?>
                </a>
            </div>
            <div class="media-body">
                <h4 class="heading"><?php echo esc_attr($author_info['name']); ?></h4>
                <p><?php echo esc_attr($author_info['bio']); ?> </p>
                <ul class="profile-social list-inline">

                    <ul class="profile-social list-inline">
                        <?php if(!empty($facebook)) { ?>
                        <li><a class="btn-facebook" href="<?php echo esc_url($facebook); ?>"><i class="fa fa-facebook"></i></a></li>
                        <?php } ?>

                        <?php if(!empty($twitter)) { ?>
                        <li><a class="btn-twitter" href="<?php echo esc_url($twitter); ?>"><i class="fa fa-twitter"></i></a></li>
                        <?php } ?>

                        <?php if(!empty($googleplus)) { ?>
                        <li><a class="btn-google" href="<?php echo esc_url($googleplus); ?>"><i class="fa fa-google"></i></a></li>
                        <?php } ?>

                        <?php if(!empty($instagram)) { ?>
                        <li><a class="btn-instagram" href="<?php echo esc_url($instagram); ?>"><i class="fa fa-instagram"></i></a></li>
                        <?php } ?>

                        <?php if(!empty($pinterest)) { ?>
                        <li><a class="btn-pinterest" href="<?php echo esc_url($pinterest); ?>"><i class="fa fa-pinterest"></i></a></li>
                        <?php } ?>

                        <?php if(!empty($linkedin)) { ?>
                        <li><a class="btn-linkedin" href="<?php echo esc_url($linkedin); ?>"><i class="fa fa-linkedin"></i></a></li>
                        <?php } ?>

                        <?php if(!empty($youtube)) { ?>
                        <li><a class="btn-youtube" href="<?php echo esc_url($youtube); ?>"><i class="fa fa-youtube"></i></a></li>
                        <?php } ?>

                        <?php if(!empty($vimeo)) { ?>
                        <li><a class="btn-vimeo" href="<?php echo esc_url($vimeo); ?>"><i class="fa fa-vimeo"></i></a></li>
                        <?php } ?>

                    </ul>

                </ul>
            </div>
        </div>
    </div>
</div>
<?php } ?>