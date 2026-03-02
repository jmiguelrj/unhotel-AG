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

$vcm_app 	= new VikApplication(VersionListener::getID());
$module 	= $this->module;
$listings 	= $this->listings;
$config 	= VikChannelManager::loadConfiguration();

$valid_urls = array();
foreach ($listings as $k => $l) {
	if (!empty($l['retrieval_url']) && filter_var($l['retrieval_url'], FILTER_VALIDATE_URL)) {
		$valid_urls[$k] = 1;
	}
}
$canpull = (!empty($module['params']) && (int)$module['av_enabled'] != 1 && count($valid_urls));
if (isset($config['last_ical_pull_' . $module['uniquekey']])) {
	$last_pull = json_decode($config['last_ical_pull_' . $module['uniquekey']]);
	if (date('Y-m-d', $last_pull->ts) == date('Y-m-d') && $last_pull->retry > 2) {
		/**
		 * We allow to manually pull the iCal bookings up to 3 times per day.
		 * 
		 * @since 	1.6.18
		 */
		$canpull = false;
	}
}

if ($canpull === true) {
	?>
<div class="vcm-manual-ical-pull-wrap">
	<a href="index.php?option=com_vikchannelmanager&task=ical_manual_pull" class="btn btn-large btn-secondary" onclick="return confirm('<?php echo addslashes(JText::_('VCMICALMANUALPULLHELP')) ?>');" style="float: right; margin-bottom: 10px;">
		<i class="vboicn-cloud-download"></i>
		<?php echo JText::_('VCMICALMANUALPULL'); ?>
	</a>
</div>
	<?php
}

?>

<form action="index.php" name="adminForm" id="adminForm" method="post">

	<div class="vcmlistingscont">
		
		<?php
		$dtitle = JText::_('VCMLISTINGDURLTIP' . strtoupper($module['name']));
		$dtitle = $dtitle == 'VCMLISTINGDURLTIP' . strtoupper($module['name']) ? JText::_('VCMLISTINGDURLTIPICAL') : $dtitle;
		$rtitle = JText::_('VCMLISTINGRURLTIP' . strtoupper($module['name']));
		$rtitle = $rtitle == 'VCMLISTINGRURLTIP' . strtoupper($module['name']) ? JText::_('VCMLISTINGRURLTIPICAL') : $rtitle;
		foreach ($listings as $k => $l) {
			$url_ok = isset($valid_urls[$k]);
			?>
			<div class="vcm-listing-prop-container">
				<div class="vcmsingleproperty">
					
					<!-- IMAGE ON LEFT -->
					<?php if( !empty($l['img']) ) { ?>
						<div class="vcmpropertyleft">
							<img src="<?php echo VBO_SITE_URI.'resources/uploads/'.$l['img']; ?>" />
						</div>
					<?php } ?>
					
					<!-- CENTER AND RIGHT SIDE -->
					<div class="vcmpropertydetails">
						
						<!-- DETAILS TITLE TOP -->
						<div class="vcmpropertydetailstop">
							<h3><?php echo $l['name']; ?></h3>
						</div>
						
						<!-- DETAILS URL -->
						<div class="vcmpropertydetailsmiddle">
							<div class="vcmpropertyurle4j">
								<div class="vcmpropertyinputblock" title="<?php echo $dtitle; ?>">
									<?php echo JText::_('VCMLISTINGLABELDOWNLOADURL'); ?>
								</div>
								<div class="vcmpropertyinputblock">
									<input type="text" value="<?php echo $l['download_url']; ?>" onfocus="this.select();" readonly/>
								</div>
							<?php
							if ($l['units'] > 1) {
								/**
								 * Show calendar download URLs for all sub-units.
								 * 
								 * @since 	1.7.0
								 */
								?>
								<div class="vcmpropertyinputblock vcm-listings-subunits-cals-toggle">
									<a href="JavaScript: void(0);" onclick="jQuery('#vcm-cals-subunits-<?php echo $l['id']; ?>').slideToggle();"><?php VikBookingIcons::e('calendar'); ?> <?php echo JText::_('VCMTOGGLEICALSUBUNITS'); ?></a>
									<?php echo $vcm_app->createPopover(array('title' => JText::_('VCMTOGGLEICALSUBUNITS'), 'content' => JText::_('VCMTOGGLEICALSUBUNITSHELP'))); ?>
								</div>
								<div class="vcmpropertyinputblock vcm-listings-subunits-cals-wrap" id="vcm-cals-subunits-<?php echo $l['id']; ?>" style="display: none;">
								<?php
								for ($i = 1; $i <= $l['units']; $i++) {
									?>
									<div>
										<span><?php VikBookingIcons::e('bed'); ?> <?php echo "#{$i}"; ?></span>
										<input type="text" value="<?php echo $l['download_url'] . '&subunit=' . $i; ?>" onfocus="this.select();" readonly/>
									</div>
									<?php
								}
								?>
								</div>
								<?php
							}
							?>
							</div>
							
							<div class="vcmpropertyurlcha">
								<div class="vcmpropertyinputblock" title="<?php echo $rtitle; ?>">
									<?php echo JText::sprintf('VCMLISTINGLABELRETRIEVALURL', ucwords($module['name'])); ?>
								</div>
								<div class="vcmpropertyinputblock">
									<input type="text" value="<?php echo $l['retrieval_url']; ?>" name="urls[]" 
									style="<?php echo (!empty($l['retrieval_url']) && !$url_ok ? 'color: #AA0000' : ''); ?>"/>
								</div>
							</div>
							
							 <div class="vcmpropertystatus">
								<div class="vcmpropertyinputblock vcmpropertystatus<?php echo ($url_ok ? 'ok' : 'bad'); ?>">
									<?php echo JText::_('VCMLISTINGSTATUS'.($url_ok ? 'OK' : 'BAD') ); ?>
								</div>
							</div>
						</div>
						
					</div>
					
				</div>
			</div>
			
			<input type="hidden" name="id_vb_rooms[]" value="<?php echo $l['id']; ?>" />
			<input type="hidden" name="id_assoc[]" value="<?php echo $l['id_assoc']; ?>" />
				
		<?php } ?>
		
	</div>

	<input type="hidden" name="task" value="listings" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<?php echo $this->navbut; ?>

</form>

<script>
	
	jQuery(document).ready(function() {
		jQuery('.vcmpropertyinputblock').tooltip();
	});
	
</script>
