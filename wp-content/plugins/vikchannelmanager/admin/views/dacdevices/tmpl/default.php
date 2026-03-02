<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

?>

<div class="vcm-dacdevices-page"></div>

<script type="text/javascript">
    jQuery(function() {

        const container = document.querySelector('.vcm-dacdevices-page');

        // render admin-widget
        VBOCore.handleDisplayInlineWidget('door_access_control', {
            _modalRendering: 1,
            _modalJsId:      Math.floor(Math.random() * 100000),
        }).then((content) => {
            // populate content
            jQuery(container)
                .html(content);
        }).catch((error) => {
            console.error(error);
            // display error
            alert(error);
        });

    });
</script>