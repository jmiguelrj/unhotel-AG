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

$node = $this->currentNode;

?>

<a
	href="javascript:void(0)"
	class="folder"
>
	<i class="fas fa-folder<?php echo $node['selected'] ? '-open' : ''; ?>"></i>
	<?php echo $node['name']; ?>
</a>
