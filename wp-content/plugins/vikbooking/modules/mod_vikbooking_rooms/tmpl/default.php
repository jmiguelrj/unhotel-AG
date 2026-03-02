<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_rooms
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2018 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// no direct access
defined('ABSPATH') or die('No script kiddies please!');

$currencysymb = $params->get('currency');
$widthroom = $params->get('widthroom');
$pagination = (int)$params->get('pagination') ? 'true' : 'false';
$navigation = (int)$params->get('navigation') ? 'true' : 'false';
$autoplayparam = (int)$params->get('autoplay') ? 'true' : 'false';
$autoplaytime = $params->get('autoplaytime');
$totalpeople = $params->get('shownumbpeople');
$showdetails = $params->get('showdetailsbtn');
$roomdesc = $params->get('showroomdesc');
$getdesc = $params->get('mod_desc');
$getshowcarats = $params->get('show_carats');

$get_module_class = $params->get('moduleclass_sfx');

$numb_xrow = $params->get('numb_roomrow');

/* 
 * @Since March 10th 2023
 * Now the Rooms List layout can have the old "Scroll" layout (1) and the "Grid" layout (0).
 */
$get_list_layout = $params->get('layoutlist');

$document = JFactory::getDocument();

if ($get_list_layout == 1) {
	$document->addStyleSheet($baseurl.'modules/mod_vikbooking_rooms/src/owl.carousel.min.css');
	//$document->addStyleSheet($baseurl.'modules/mod_vikbooking_rooms/src/owl.theme.default.min.css');
	JHtml::_('script', $baseurl.'modules/mod_vikbooking_rooms/src/owl.carousel.min.js');
}
$document->addStyleSheet($baseurl.'modules/mod_vikbooking_rooms/mod_vikbooking_rooms.css');


?>
<div class="vbmodroomscontainer wrap">
	<?php if (!empty($getdesc)) { ?>
		<div class="vbmodroom-desc"><?php echo $getdesc; ?></div>
	<?php } ?>
	<div>
		<div id="vbo-modrooms-<?php echo $randid; ?>" class="<?php echo ($get_list_layout == 1) ? "owl-carousel owl-theme vbmodroom-layout-scroll" : "vbmodroom-layout-grid vbmodroom-grid-numb-".$numb_xrow."" ?> vbmodrooms">
		<?php
		$vbo_tn = null;
		try {
			$vbo_tn = VikBooking::getTranslator();
		} catch (Exception $e) {
			// do nothing
		}
		foreach ($rooms as $c) {
			$carats = VikBooking::getRoomCaratOriz($c['idcarat'], $vbo_tn);
			?>
			<div class="vbmodrooms-item vbomodroom-num-<?php echo $numb_xrow; ?>">
				<div class="vbmodroomsboxdiv">
					<?php
					if (!empty($c['img'])) {
						?>
						<a class="vbmodrooms-img-link" href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=roomdetails&roomid='.$c['id'].'&Itemid='.$params->get('itemid')); ?>">
							<img title="<?php echo $c['name']; ?>" alt="<?php echo $c['name']; ?>" src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $c['img']; ?>" class="vbmodroomsimg"/>
						</a>
						<?php
					}
					?>
					<div class="vbinf">
						<div class="vbmodrooms-divblock">
					        <span class="vbmodroomsname"><?php echo $c['name']; ?></span>
					        <?php
							if ($totalpeople == 1) {
								?>
					        	<span class="vbmodroomsbeds"><?php echo $c['totpeople']; ?> <?php echo ($c['totpeople'] > 1 ? JText::_('VBMODROOMSBEDS') : JText::_('VBMODROOMSBED')); ?></span>
					        	<?php
					    	}
							?>
						</div>
						<?php
						if ($showcatname) {
							?>
							<span class="vbmodroomscat"><?php echo $c['catname']; ?></span>
							<?php
						}
						if ($roomdesc) {
							?>
							<span class="vbmodroomsdesc"><?php echo $c['smalldesc']; ?></span>		
							<?php
						}
						if ($carats && $getshowcarats == 1) {
							?>
							<div class="vbmodrooms-carats">
								<?php echo $carats; ?>
							</div>
							<?php
						}
						if ($c['cost'] > 0) {
							?>
							<div class="vbmodroomsroomcost">
								<?php echo VikBooking::formatCurrencyNumber(modvikbooking_roomsHelper::numberFormat($c['cost']), $currencysymb, ['<span class="vbo_currency">%s</span>', '<span class="vbo_price">%s</span>']); ?>
							<?php
							if (array_key_exists('custpricetxt', $c) && !empty($c['custpricetxt'])) {
								?>
								<span class="vbmodroomslabelcost"><?php echo $c['custpricetxt']; ?></span>
								<?php
							}
							?>
							</div>
							<?php
						}
						?>
					</div>
					<?php
					if ($showdetails == 1) {
						?>
					<div class="vbmodroomsview">
						<a class="btn vbo-pref-color-btn" href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=roomdetails&roomid='.$c['id'].'&Itemid='.$params->get('itemid')); ?>"><?php echo JText::_('VBMODROOMSCONTINUE'); ?></a>
					</div>
						<?php
					}
					?>
				</div>	
			</div>
		<?php
		}
		?>
		</div>
	</div>
</div>

<?php if ($get_list_layout == 1) { ?>

	<script type="text/javascript">
		jQuery(function() {
			jQuery("#vbo-modrooms-<?php echo $randid; ?>").owlCarousel({
				items : <?php echo $numb_xrow; ?>,
				autoplay : <?php echo $autoplayparam; ?>,
				nav : <?php echo $navigation; ?>,
				navText : ['<?php echo JText::_('VBMODROOMSPREV'); ?>', '<?php echo JText::_('VBMODROOMSNEXT'); ?>'],
				dots : <?php echo $pagination; ?>,
				lazyLoad : true,
				responsiveClass: true,
				responsive: {
					0: {
						items: 1,
						nav: true
					},
					<?php if($numb_xrow == 1) { ?>
						600: {
							items:1,
							nav: true
						},
					<?php } else { ?>
						600: {
							items:2,
							nav: true
						},
					<?php } ?>
					<?php if($numb_xrow == 1) { ?>
						820: {
							items: 1,
							nav: true
						},
					<?php } else if($numb_xrow == 2) { ?>
						820: {
							items: 2,
							nav: true
						},
					<?php } else { ?>
						820: {
							items: 3,
							nav: true
						},
					<?php } ?>
					1024: {
						items: <?php echo $numb_xrow; ?>,
						nav: true
					}
				}		
			});

			<?php if ($navigation == "false") { ?>
				jQuery("#vbo-modrooms-<?php echo $randid; ?> .owl-nav").addClass('owl-disabled');
			<?php } ?>
		});
	</script>

<?php } ?>
