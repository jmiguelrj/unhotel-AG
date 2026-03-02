<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

?>

<style>
	.logs-file-status {
		display: flex;
		justify-content: space-between;
		align-items: center;
		height: 32px;
		padding: 10px 20px;
		background: var(--vcm-body-bg-color);
		border-top: 1px solid var(--vcm-middle-color-btn);
	}
</style>

<div class="logs-file-status">

	<div class="status-count"><?php echo $this->count; ?> logs</div>
	
	<div class="status-filesize"><?php echo $this->filesize; ?></div>

</div>
