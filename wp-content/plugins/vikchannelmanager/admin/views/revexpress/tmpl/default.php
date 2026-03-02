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

JHTML::_('behavior.tooltip');

$contents = $this->contents;

$vik = new VikApplication(VersionListener::getID());

?>

<?php if( !empty($contents['error']) ) { ?>
	<div class="vcmtacstatuserrdiv">
		<div class="vcmtacstatuserrtitlediv">
			<?php echo 'ERROR: '.$contents['error']['code']; ?>
		</div>
		<div class="vcmtacstatuserrmsgdiv">
			<?php echo $contents['error']['message']; ?>
		</div>
	</div>
<?php } else if( count($contents['data']) > 0 ) { 
	$date_format = VikChannelManager::getClearDateFormat(true);
	
	?>
	
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="50" style="text-align: left;"><?php echo JText::_('VCMREVEXPID'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="150" style="text-align: center;"><?php echo JText::_('VCMREVEXPCONFNUM'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="150" style="text-align: center;"><?php echo JText::_('VCMREVEXPMAIL'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="100" style="text-align: center;"><?php echo JText::_('VCMREVEXPCHECKIN'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="100" style="text-align: center;"><?php echo JText::_('VCMREVEXPCHECKOUT'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="50" style="text-align: center;"><?php echo JText::_('VCMREVEXPCOUNTRY'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="100" style="text-align: center;"><?php echo JText::_('VCMREVEXPSTATUS'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>
		
		<?php
		$kk = 0;
		foreach( $contents['data'] as $row ) { ?>
			<tr class="row<?php echo $kk; ?>">
				<td><?php echo $row['request_id']; ?></td>
				<td style="text-align: center;"><a href="index.php?option=com_vikchannelmanager&amp;task=ordervbfromsid&amp;sid=<?php echo $row['partner_request_id']; ?>&tmpl=component" rel="{handler: 'iframe', size: {x: 750, y: 600}}" class="modal" target="_blank">
					<?php echo $row['partner_request_id']; ?>
				</a></td>
				<td style="text-align: center;"><?php echo $row['recipient']; ?></td>
				<td style="text-align: center;"><?php echo $row['checkin']; ?></td>
				<td style="text-align: center;"><?php echo $row['checkout']; ?></td>
				<td style="text-align: center;"><img src="<?php echo VCM_ADMIN_URI.'assets/css/flags/'.strtolower($row['country']).'.png'; ?>" /></td>
				<td style="text-align: center;" class="vcmrevexpstatus<?php echo $row['status']; ?>"><?php echo JText::_('VCMREVEXPSTATUS'.strtoupper($row['status'])); ?></td>
			</tr>
			<?php
			$kk = 1 - $kk;
		}		
		?>
	</table>
	
<?php } else { ?>
	<div class="vcmtacstatuserrdiv">
		<div class="vcmtacstatuserrmsgdiv">
			<?php echo JText::_('VCMREVEXPNOREVIEWS'); ?>
		</div>
	</div>
<?php } ?>

