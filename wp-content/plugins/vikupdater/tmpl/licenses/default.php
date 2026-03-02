<?php
/** 
 * @package     VikUpdater
 * @subpackage  views
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

?>

<div class="wrap nosubsub">

    <!-- page title -->

    <h1 class="wp-heading-inline"><?php _e('Licenses', 'vikupdater'); ?></h1>

    <hr class="wp-header-end">

    <!-- section used to display ajax messages -->

    <div id="ajax-response"></div>

    <!-- page content -->

    <div id="col-container" class="wp-clearfix">

        <!-- left side -->

        <div id="col-left">

            <!-- license management form -->

            <div class="col-wrap">
                <?php echo $this->loadTemplate('edit'); ?>
            </div>

        </div>

        <div id="col-right">

            <!-- licenses table -->

            <div class="col-wrap">
                <?php echo $this->loadTemplate('list'); ?>
            </div>

        </div>

    </div>

</div>
