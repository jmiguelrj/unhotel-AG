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

abstract class VCM
{
	public static function printMenu()
	{
		if (VikChannelManager::isProgramBlocked(true)) {
			self::printBlockVersionView();
			return;
		}

		/**
		 * We require a recent version of Vik Booking.
		 * 
		 * @since 	1.8.11
		 */
		if (!self::requireFontAwesome()) {
			JFactory::getApplication()->enqueueMessage('Please update Vik Booking to a recent version or the menu will not work.', 'error');
			return;
		}

		// JS lang def
		JText::script('VCM_QUICK_ACTIONS');

		/**
		 * Before doing anything else, make sure the expiration details are present.
		 * This is useful for BC versions that did not have this information available.
		 * We also check if the subscription is expired to always set an error message.
		 * Instead, if the expiration date is near, we display a reminder.
		 * 
		 * @since 	1.8.3
		 */
		if (VikChannelManager::loadExpirationDetails() === false) {
			// download and update the expiration details
			VikChannelManager::downloadExpirationDetails();
		}
		$expiration_reminder = null;
		if (VikChannelManager::isSubscriptionExpired()) {
			VikError::raiseWarning('', JText::_('VCM_WARN_SUBSCR_EXPIRED'));
		} else {
			$expiration_reminder = VikChannelManager::shouldRemindExpiration();
		}

		$dbo = JFactory::getDbo();
		$document = JFactory::getDocument();
		$session = JFactory::getSession();
		$sess_notifs = $session->get('vcmNotifications', 0, 'vcm');
		
		$new_version = VikChannelManager::isNewVersionAvailable(true);
		
		$task_name = VikRequest::getString('task', '');
		$view_name = VikRequest::getString('view', '');
		$highlight = empty($task_name) && !empty($view_name) ? $view_name : $task_name;
		$highlight = empty($highlight) ? 'dashboard' : $highlight;
		
		$module = VikChannelManager::getActiveModule(true);
		$id = 0;
		if (!empty($module['id'])) {
			$id = $module['id'];
		}

		/**
		 * Attempt to register the visited menu page.
		 * 
		 * @since 	1.8.11
		 */
		VCMMenuHelper::registerPage($highlight, $module);

		// build channel entries
		$q = "SELECT `id`,`name`,`uniquekey`,`av_enabled` FROM `#__vikchannelmanager_channel` WHERE `id`!={$id};";
		$dbo->setQuery($q);
		$mod_rows = $dbo->loadAssocList();

		if (empty($module['id']) && $mod_rows) {
			$module = $mod_rows[0];
			$arr = array();
			for ($i = 1; $i < count($mod_rows); $i++) {
				$arr[$i-1] = $mod_rows[$i];
			}
			$mod_rows = $arr;
		}

		/**
		 * Load iCal Channels
		 * 
		 * @since 	1.7.0
		 */
		$current_ical_id = isset($module['ical_channel']) ? $module['ical_channel']['id'] : 0;
		$q = "SELECT * FROM `#__vikchannelmanager_ical_channels` WHERE `id`!={$current_ical_id};";
		$dbo->setQuery($q);
		$ical_sub_channels = $dbo->loadAssocList();
		if ($ical_sub_channels) {
			// find the main iCal channel
			$main_ical = array();
			$repush_main_ical = false;
			foreach ($mod_rows as $ch) {
				if ((int)$ch['uniquekey'] == (int)VikChannelManagerConfig::ICAL) {
					$main_ical = $ch;
					break;
				}
			}
			if (!count($main_ical) && !empty($module['uniquekey']) && (int)$module['uniquekey'] == (int)VikChannelManagerConfig::ICAL) {
				$main_ical = $module;
				$repush_main_ical = true;
			}
			if (count($main_ical)) {
				foreach ($ical_sub_channels as $subch) {
					$custom_mod = $main_ical;
					$custom_mod['id'] .= '-' . $subch['id'];
					$custom_mod['name'] = $subch['name'];
					$custom_mod['ical_channel'] = $subch;
					// push custom iCal channel
					array_push($mod_rows, $custom_mod);
				}
				if ($repush_main_ical && isset($module['ical_channel'])) {
					// push main iCal channel so that it can be re-selected
					array_push($mod_rows, $main_ical);
				}
			}
		}

		/**
		 * Sort the active channels properly, and attempt to set the tiny logo.
		 * 
		 * @since 	1.8.11
		 */
		VCMMenuHelper::prepareActiveChannels($mod_rows);
		
		$li_mod_class = '';
		if (!empty($module['uniquekey'])) {
			switch ($module['uniquekey']) {
				case VikChannelManagerConfig::EXPEDIA: $li_mod_class = "vcmliexpedia"; break;
				case VikChannelManagerConfig::TRIP_CONNECT: $li_mod_class = "vcmlitripconnect"; break;
				case VikChannelManagerConfig::TRIVAGO: $li_mod_class = "vcmlitrivago"; break;
				case VikChannelManagerConfig::BOOKING: $li_mod_class = "vcmlibookingcom"; break;
				case VikChannelManagerConfig::AIRBNB: $li_mod_class = "vcmliairbnb"; break;
				case VikChannelManagerConfig::FLIPKEY: $li_mod_class = "vcmliflipkey"; break;
				case VikChannelManagerConfig::HOLIDAYLETTINGS: $li_mod_class = "vcmliholidaylettings"; break;
				case VikChannelManagerConfig::AGODA: $li_mod_class = "vcmliagoda"; break;
				case VikChannelManagerConfig::WIMDU: $li_mod_class = "vcmliwimdu"; break;
				case VikChannelManagerConfig::HOMEAWAY: $li_mod_class = "vcmlihomeaway"; break;
				case VikChannelManagerConfig::VRBO: $li_mod_class = "vcmlivrbo"; break;
				case VikChannelManagerConfig::YCS50: $li_mod_class = "vcmliycs50"; break;
				case VikChannelManagerConfig::BEDANDBREAKFASTIT: $li_mod_class = "vcmlibedandbreakfastit"; break;
				case VikChannelManagerConfig::MOBILEAPP: $li_mod_class = "vcmlimobileapp"; $module['name'] = ucwords(str_replace('-', ' ', $module['name']));break;
				case VikChannelManagerConfig::DESPEGAR: $li_mod_class = "vcmlidespegar"; break;
				case VikChannelManagerConfig::OTELZ: $li_mod_class = "vcmliotelzcom"; break;
				case VikChannelManagerConfig::GARDAPASS: $li_mod_class = "vcmligardapass"; break;
				case VikChannelManagerConfig::BEDANDBREAKFASTEU: $li_mod_class = "vcmlibedandbreakfasteu"; break;
				case VikChannelManagerConfig::BEDANDBREAKFASTNL: $li_mod_class = "vcmlibedandbreakfastnl"; break;
				case VikChannelManagerConfig::FERATEL: $li_mod_class = "vcmliferatel"; break;
				case VikChannelManagerConfig::PITCHUP: $li_mod_class = "vcmlipitchup"; break;
				case VikChannelManagerConfig::CAMPSITESCOUK: $li_mod_class = "vcmlicampsitescouk"; break;
				case VikChannelManagerConfig::ICAL: $li_mod_class = "vcmliical"; break;
				case VikChannelManagerConfig::HOSTELWORLD: $li_mod_class = "vcmlihostelworld"; break;
				case VikChannelManagerConfig::AIRBNBAPI: $li_mod_class = "vcmliairbnbapi"; break;
				case VikChannelManagerConfig::GOOGLEHOTEL: $li_mod_class = "vcmligooglehotel"; break;
				case VikChannelManagerConfig::GOOGLEVR: $li_mod_class = "vcmligooglevr"; break;
				case VikChannelManagerConfig::VRBOAPI: $li_mod_class = "vcmlivrboapi"; break;
				case VikChannelManagerConfig::AI: $li_mod_class = "vcmliai"; break;
				case VikChannelManagerConfig::DAC: $li_mod_class = "vcmlidac"; break;
				case VikChannelManagerConfig::CTRIP: $li_mod_class = "vcmlictrip"; break;
				default: break;
			}
		}

		/**
		 * Custom iCal channel logo.
		 * 
		 * @since 	1.8.11
		 */
		$custom_ical_logo = null;
		if (isset($module['ical_channel'])) {
			$li_mod_class = 'vcmlisubchannelical';
			if (!empty($module['ical_channel']['logo'])) {
				$custom_ical_logo = JUri::root() . $module['ical_channel']['logo'];
			}
		}

		$av_pool = array(
			VikChannelManagerConfig::EXPEDIA,
			VikChannelManagerConfig::AGODA,
			VikChannelManagerConfig::BOOKING,
			VikChannelManagerConfig::YCS50,
			VikChannelManagerConfig::BEDANDBREAKFASTIT,
			VikChannelManagerConfig::DESPEGAR,
			VikChannelManagerConfig::OTELZ,
			VikChannelManagerConfig::GARDAPASS,
			VikChannelManagerConfig::BEDANDBREAKFASTEU,
			VikChannelManagerConfig::BEDANDBREAKFASTNL,
			VikChannelManagerConfig::FERATEL,
			VikChannelManagerConfig::PITCHUP,
			VikChannelManagerConfig::HOSTELWORLD,
			VikChannelManagerConfig::AIRBNBAPI,
			VikChannelManagerConfig::GOOGLEHOTEL,
			VikChannelManagerConfig::GOOGLEVR,
			VikChannelManagerConfig::VRBOAPI,
			VikChannelManagerConfig::CTRIP,
		);

		$pro_lv = VikChannelManager::getProLevel();
		$vcm_logo = VikChannelManager::getBackendLogoFullPath();

	?>
	<div class="vcm-menunav-wrap vcm-menu-container">
		<div class="vcm-menunav-left vcm-menu-left">
			<a href="index.php?option=com_vikchannelmanager"><img alt="Vik Channel Manager" src="<?php echo $vcm_logo; ?>" /></a>
		</div>
		<div class="vcm-menunav-right vcm-menu-right">
			<ul class="vcm-menu-ul">
				<li class="vcm-menu-parent-li vcmmenulifirst <?php echo ($highlight == 'dashboard' ? 'vmenulinkactive' : '') . ($new_version ? ' vcmnewupavailnotice' : ''); ?>">
					<span>
						<a href="index.php?option=com_vikchannelmanager">
							<?php VikBookingIcons::e('heartbeat'); ?>
							<span id="dashboard-menu"></span>
							<span class="vcm-menu-parent-txt"><?php echo JText::_('VCMMENUDASHBOARD'); ?></span>
						</a>
					</span>
				</li>
				<li class="vcm-menu-parent-li<?php echo $highlight == 'config' ? ' vmenulinkactive' : ''; ?>">
					<span>
						<a href="index.php?option=com_vikchannelmanager&amp;task=config">
							<?php VikBookingIcons::e('cogs'); ?>
							<span class="vcm-menu-parent-txt"><?php echo JText::_('VCMMENUSETTINGS'); ?></span>
						</a>
					</span>
				</li>
				<li class="vcm-menu-parent-li">
					<span>
						<a href="index.php?option=com_vikbooking">
							<?php VikBookingIcons::e('concierge-bell'); ?>
							<span class="vcm-menu-parent-txt"><?php echo JText::_('VCMMENUPMS'); ?></span>
							<?php VikBookingIcons::e('chevron-down', 'vcm-submenu-chevron'); ?>
						</a>
					</span>
					<div class="vcm-submenu-wrap">
						<ul class="vcm-submenu-ul" data-menu-scope="">
							<li>
								<div class="vmenulink">
									<a href="index.php?option=com_vikbooking">
										<?php VikBookingIcons::e('concierge-bell'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUDASHBOARD'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<li>
								<div class="vmenulink">
									<a href="index.php?option=com_vikbooking&amp;task=orders" target="_blank">
										<?php VikBookingIcons::e('clipboard-list'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUEXPFROMVB'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<li>
								<div class="vmenulink">
									<a href="index.php?option=com_vikbooking&amp;task=overv" target="_blank">
										<?php VikBookingIcons::e('calendar-check'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUOVERVIEW'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<li>
								<div class="vmenulink">
									<a href="index.php?option=com_vikbooking&amp;task=ratesoverv" target="_blank">
										<?php VikBookingIcons::e('calculator'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENURATESOVERV'); ?></span>
										</span>
									</a>
								</div>
							</li>
						</ul>
					</div>
				</li>
				<li class="vcm-menu-parent-li<?php echo (self::isHotelHighlighted($highlight) ? ' vmenulinkactive' : ''); ?> <?php echo $li_mod_class; ?>">
					<span>
						<a href="index.php?option=com_vikchannelmanager&amp;task=<?php echo self::getHotelDefaultTask($module['uniquekey']); ?>">
							<?php VikBookingIcons::e('hotel'); ?>
							<span class="vcm-menu-parent-txt"><?php echo JText::_('VCMMENUHOTEL'); ?></span>
							<?php VikBookingIcons::e('chevron-down', 'vcm-submenu-chevron'); ?>
						</a>
					</span>
					<div class="vcm-submenu-wrap">
						<ul class="vcm-submenu-ul" data-menu-scope="hotel">
							<?php
							// exclude hotel details from specific channels
							if (!in_array($module['uniquekey'], [VikChannelManagerConfig::GOOGLEVR, VikChannelManagerConfig::DAC])) {
							?>
							<li>
								<div class="<?php echo ($highlight == 'hoteldetails' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=hoteldetails">
										<?php VikBookingIcons::e('home'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUTACDETAILS'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<?php
							}
							if ($module['uniquekey'] == VikChannelManagerConfig::DAC) {
								?>
							<li>
								<div class="<?php echo ($highlight == 'dacdevices' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;view=dacdevices">
										<?php VikBookingIcons::e('fingerprint'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCM_DEVICES'); ?></span>
										</span>
									</a>
								</div>
							</li>
								<?php
							}
							// expedia, agoda, booking.com, etc..
							if (in_array($module['uniquekey'], $av_pool)) {
								// booking.com Contents API
								if ($module['uniquekey'] == VikChannelManagerConfig::BOOKING && $pro_lv >= 15) {
									?>
							<li class="vcm-submenu-li-parent subparent">
								<div class="<?php echo ($highlight == 'bphotos' || substr($highlight, 0, 3) == 'bca' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="#">
										<?php VikBookingIcons::e('icons'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPI'); ?> <?php VikBookingIcons::e('chevron-right', 'vcm-submenu-chevron'); ?></span>
										</span>
									</a>
								</div>
								<ul class="vcm-submenu-child-ul subchild">
									<li>
										<div class="<?php echo $highlight == 'bmngproperty' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;view=bmngproperty">
												<?php VikBookingIcons::e('hotel'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCM_BCOM_PROP_DETAILS'); ?></span>
												</span>
											</a>
										</div>
									</li>
									<li>
										<div class="<?php echo $highlight == 'bphotos' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=bphotos">
												<?php VikBookingIcons::e('camera'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBPHOTOS'); ?></span>
												</span>
											</a>
										</div>
									</li>
									<?php
									if ($pro_lv >= 20) {
										?>
									<li>
										<div class="<?php echo $highlight == 'bcahcont' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=bcahcont">
												<?php VikBookingIcons::e('hotel'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPIHOTEL'); ?></span>
												</span>
											</a>
										</div>
									</li>
									<li>
										<div class="<?php echo $highlight == 'bcahsummary' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=bcahsummary">
												<?php VikBookingIcons::e('check-double'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPIHOTELSUM'); ?></span>
												</span>
											</a>
										</div>
									</li>
									<li>
										<div class="<?php echo $highlight == 'bcarplans' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=bcarplans">
												<?php VikBookingIcons::e('tags'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPIRPLANS'); ?></span>
												</span>
											</a>
										</div>
									</li>
									<li>
										<div class="<?php echo $highlight == 'bcarcont' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=bcarcont">
												<?php VikBookingIcons::e('bed'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPIROOMS'); ?></span>
												</span>
											</a>
										</div>
									</li>
									<li>
										<div class="<?php echo $highlight == 'bcapnotif' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=bcapnotif">
												<?php VikBookingIcons::e('stream'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPIPNOTIF'); ?></span>
												</span>
											</a>
										</div>
									</li>
										<?php
									}
									?>
								</ul>
							</li><?php
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && $pro_lv >= 15) {
									// Airbnb Listings API and Hosting Quality
									?>
							<li class="vcm-submenu-li-parent subparent">
								<div class="<?php echo ($highlight == 'airbnblistings' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="#">
										<?php VikBookingIcons::e('icons'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPI'); ?> <?php VikBookingIcons::e('chevron-right', 'vcm-submenu-chevron'); ?></span>
										</span>
									</a>
								</div>
								<ul class="vcm-submenu-child-ul subchild">
									<li>
										<div class="<?php echo $highlight == 'airbnblistings' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=airbnblistings">
												<?php VikBookingIcons::e('building'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUAIRBMNGLST'); ?></span>
												</span>
											</a>
										</div>
									</li>
									<li>
										<div class="<?php echo $highlight == 'hostingquality' ? "vmenulinkactive" : "vmenulink"; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;view=hostingquality">
												<?php VikBookingIcons::e('thumbs-up'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCM_HOSTING_QUALITY'); ?></span>
												</span>
											</a>
										</div>
									</li>
								</ul>
							</li><?php
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::VRBOAPI && $pro_lv >= 15) {
									// Vrbo Listings API
									?>
							<li class="vcm-submenu-li-parent subparent">
								<div class="<?php echo ($highlight == 'vrbolistings' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="#">
										<?php VikBookingIcons::e('icons'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPI'); ?> <?php VikBookingIcons::e('chevron-right', 'vcm-submenu-chevron'); ?></span>
										</span>
									</a>
								</div>
								<ul class="vcm-submenu-child-ul subchild">
									<li>
										<div class="<?php echo $highlight == 'vrbolistings' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;view=vrbolistings">
												<?php VikBookingIcons::e('building'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUAIRBMNGLST'); ?></span>
												</span>
											</a>
										</div>
									</li>
								</ul>
							</li><?php
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR && $pro_lv >= 15) {
									// Google VR Listings
									?>
							<li class="vcm-submenu-li-parent subparent">
								<div class="<?php echo ($highlight == 'googlevrlistings' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="#">
										<?php VikBookingIcons::e('icons'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPI'); ?> <?php VikBookingIcons::e('chevron-right', 'vcm-submenu-chevron'); ?></span>
										</span>
									</a>
								</div>
								<ul class="vcm-submenu-child-ul subchild">
									<li>
										<div class="<?php echo $highlight == 'googlevrlistings' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;view=googlevrlistings">
												<?php VikBookingIcons::e('building'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUAIRBMNGLST'); ?></span>
												</span>
											</a>
										</div>
									</li>
								</ul>
							</li><?php
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::EXPEDIA && $pro_lv >= 15) {
									// Expedia Product API
									?>
							<li class="vcm-submenu-li-parent subparent">
								<div class="<?php echo ($highlight == 'expediaproducts' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="#">
										<?php VikBookingIcons::e('icons'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBCONTAPI'); ?> <?php VikBookingIcons::e('chevron-right', 'vcm-submenu-chevron'); ?></span>
										</span>
									</a>
								</div>
								<ul class="vcm-submenu-child-ul subchild">
									<li>
										<div class="<?php echo $highlight == 'expediaproducts' ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;view=expediaproducts">
												<?php VikBookingIcons::e('building'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMBPROMOHROOMRATES'); ?></span>
												</span>
											</a>
										</div>
									</li>
								</ul>
							</li><?php
								}
								// booking.com Promotions API
								if ($module['uniquekey'] == VikChannelManagerConfig::BOOKING && $pro_lv >= 15) {
									?>
							<li>
								<div class="<?php echo (in_array($highlight, ['bpromo', 'bpromonew', 'bpromoedit']) ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=bpromo">
										<?php VikBookingIcons::e('percent'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBPROMOTIONS'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::EXPEDIA && $pro_lv >= 15) {
									// Expedia Promotions API
									?>
							<li>
								<div class="<?php echo (in_array($highlight, ['egpromo', 'egpromonew', 'egpromoedit']) ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=egpromo">
										<?php VikBookingIcons::e('percent'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBPROMOTIONS'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
								} elseif ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && $pro_lv >= 15) {
									// Airbnb Promotions API
									?>
							<li>
								<div class="<?php echo (in_array($highlight, ['airbnbpromo', 'airbnbpromonew', 'airbnbpromoedit']) ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=airbnbpromo">
										<?php VikBookingIcons::e('percent'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBPROMOTIONS'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
								}
								?>
							<li>
								<div class="<?php echo ($highlight == 'rooms' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=rooms">
										<?php VikBookingIcons::e('exchange-alt'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUEXPROOMSREL'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<?php
								if ($module['uniquekey'] != VikChannelManagerConfig::GOOGLEVR) {
								?>
							<li>
								<div class="<?php echo ($highlight == 'roomsynch' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=roomsynch">
										<?php VikBookingIcons::e('sync'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUEXPSYNCH'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
								}
							// trip advisor
							} elseif ($module['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT) {
								?>
							<li>
								<div class="<?php echo ($highlight == 'inventory' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=inventory">
										<?php VikBookingIcons::e('sync'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUTACROOMSINV'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
							// trivago
							} elseif ($module['uniquekey'] == VikChannelManagerConfig::TRIVAGO) {
								?>
							<li>
								<div class="<?php echo ($highlight == 'trinventory' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=trinventory">
										<?php VikBookingIcons::e('calendar-check'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUTACROOMSINV'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
							// airbnb flipkey holidaylettings wimdu homeaway vrbo
							} elseif (in_array($module['uniquekey'], array(VikChannelManagerConfig::AIRBNB, VikChannelManagerConfig::FLIPKEY, VikChannelManagerConfig::HOLIDAYLETTINGS, VikChannelManagerConfig::WIMDU, VikChannelManagerConfig::HOMEAWAY, VikChannelManagerConfig::VRBO, VikChannelManagerConfig::CAMPSITESCOUK))) {
								?>
							<li>
								<div class="<?php echo ($highlight == 'listings' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=listings">
										<?php VikBookingIcons::e('calendar-check'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENULISTINGS'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
							} elseif ($module['uniquekey'] == VikChannelManagerConfig::ICAL && !isset($module['ical_channel'])) {
								?>
							<li>
								<div class="<?php echo $module['name'] . ($highlight == 'icalchannels' ? ' vmenulinkactive' : ' vmenulink'); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=icalchannels">
										<?php VikBookingIcons::e('calendar-check'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUICALCHANNELS'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
							} elseif ($module['uniquekey'] == VikChannelManagerConfig::ICAL && isset($module['ical_channel'])) {
								?>
							<li>
								<div class="<?php echo $module['name'] . ($highlight == 'listings' ? ' vmenulinkactive' : ' vmenulink'); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=listings">
										<?php VikBookingIcons::e('calendar-check'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENULISTINGS'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
							} 
							?>
						</ul>
					</div>
				</li>
				<?php
				if ($module['av_enabled'] == 1 || $module['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT) {
					?>
				<li class="vcm-menu-parent-li<?php echo (self::isOrderHighlighted($highlight) ? ' vmenulinkactive' : ''); ?> <?php echo $li_mod_class; ?>">
					<span>
						<a href="index.php?option=com_vikchannelmanager&amp;task=<?php echo self::getOrderDefaultTask($module['uniquekey']); ?>">
							<?php VikBookingIcons::e('calendar-check'); ?>
							<span class="vcm-menu-parent-txt"><?php echo JText::_('VCMMENUORDERS'); ?></span>
							<?php VikBookingIcons::e('chevron-down', 'vcm-submenu-chevron'); ?>
						</a>
					</span>
					<div class="vcm-submenu-wrap">
						<ul class="vcm-submenu-ul" data-menu-scope="bookings">
							<?php
							// expedia, agoda, booking.com, etc..
							if (in_array($module['uniquekey'], $av_pool)) {
								?>
							<!--<li>
								<div class="vmenulink">
									<a href="index.php?option=com_vikbooking&amp;task=orders">
										<?php VikBookingIcons::e('clipboard-list'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUEXPFROMVB'); ?></span>
										</span>
									</a>
								</div>
							</li>-->
								<?php
							// trip advisor
							} elseif ($module['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT) {
								?>
							<li>
								<div class="<?php echo ($highlight == 'tacstatus' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=tacstatus">
										<?php VikBookingIcons::e('info-circle'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUTACSTATUS'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<li>
								<div class="<?php echo ($highlight == 'revexpress' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=revexpress">
										<?php VikBookingIcons::e('comment'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUREVEXP'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
							}
							?>
							<li>
								<div class="<?php echo ($highlight == 'reviews' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=reviews">
										<?php VikBookingIcons::e('comments'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUREVIEWS'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
							// Booking.com and Airbnb Opportunities API
							if (in_array($module['uniquekey'], array(VikChannelManagerConfig::BOOKING, VikChannelManagerConfig::AIRBNBAPI)) && $pro_lv >= 15) {
								?>
							<li>
								<div class="<?php echo ($highlight == 'opportunities' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=opportunities">
										<?php VikBookingIcons::e('graduation-cap'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMOPPORTUNITIES'); ?></span>
										</span>
									</a>
								</div>
							</li><?php
							}
							if ($module['av_enabled'] == 1) {
								?>
							<li>
								<div class="<?php echo ($highlight == 'oversight' || $highlight == 'customa' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=oversight">
										<?php VikBookingIcons::e('clipboard-list'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUOVERVIEW'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<li>
								<div class="<?php echo (strpos($highlight, 'smartbalancer') !== false ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=smartbalancer">
										<?php VikBookingIcons::e('balance-scale'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUSMARTBALANCER'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<li class="vcm-submenu-li-parent subparent">
								<div class="<?php echo (in_array($highlight, ['avpush', 'ratespush', 'avpushsubmit', 'ratespushsubmit']) ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="#">
										<?php VikBookingIcons::e('rocket'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUBULKACTIONS'); ?> <?php VikBookingIcons::e('chevron-right', 'vcm-submenu-chevron'); ?></span>
										</span>
									</a>
								</div>
								<ul class="vcm-submenu-child-ul subchild">
									<li>
										<div class="<?php echo strpos($highlight, 'avpush') !== false ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=avpush">
												<?php VikBookingIcons::e('layer-group'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUAVPUSH'); ?></span>
												</span>
											</a>
										</div>
									</li>
									<li>
										<div class="<?php echo strpos($highlight, 'ratespush') !== false ? 'vmenulinkactive' : 'vmenulink'; ?>">
											<a href="index.php?option=com_vikchannelmanager&amp;task=ratespush">
												<?php VikBookingIcons::e('calculator'); ?>
												<span class="vcm-submenu-item">
													<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENURATESPUSH'); ?></span>
												</span>
											</a>
										</div>
									</li>
								</ul>
							</li><?php
							}
							?>
						</ul>
					</div>
				</li>
				<?php
				}

				// AI service
				if ($module['uniquekey'] == VikChannelManagerConfig::AI) {
					?>
				<li class="vcm-menu-parent-li<?php echo in_array($highlight, ['trainings', 'training', 'messagingfaqs']) ? ' vmenulinkactive' : ''; ?> <?php echo $li_mod_class; ?>">
					<span>
						<a href="index.php?option=com_vikchannelmanager&amp;view=trainings">
							<?php VikBookingIcons::e('magic'); ?>
							<span class="vcm-menu-parent-txt"><?php echo JText::_('VCM_MENU_AI_CHATBOT'); ?></span>
							<?php VikBookingIcons::e('chevron-down', 'vcm-submenu-chevron'); ?>
						</a>
					</span>
					<div class="vcm-submenu-wrap">
						<ul class="vcm-submenu-ul" data-menu-scope="app">
							<li>
								<div class="<?php echo (in_array($highlight, ['trainings', 'training']) ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;view=trainings">
										<?php VikBookingIcons::e('graduation-cap'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCM_MENU_AI_TRAININGS'); ?></span>
										</span>
									</a>
								</div>
							</li>
							<li>
								<div class="<?php echo ($highlight == 'messagingfaqs' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;view=messagingfaqs">
										<?php VikBookingIcons::e('question-circle'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCM_MENU_AI_MESSAGING_FAQS'); ?></span>
										</span>
									</a>
								</div>
							</li>
						</ul>
					</div>
				</li><?php
				}

				// mobile app
				if ($module['uniquekey'] == VikChannelManagerConfig::MOBILEAPP ) {
					?>
				<li class="vcm-menu-parent-li<?php echo (self::isAppHighlighted($highlight) ? ' vmenulinkactive' : ''); ?> <?php echo $li_mod_class; ?>">
					<span>
						<a href="index.php?option=com_vikchannelmanager&amp;task=<?php echo self::getOrderDefaultTask($module['uniquekey']); ?>">
							<?php VikBookingIcons::e('mobile'); ?>
							<span class="vcm-menu-parent-txt"><?php echo JText::_('VCMMENUAPPCONFTIT'); ?></span>
							<?php VikBookingIcons::e('chevron-down', 'vcm-submenu-chevron'); ?>
						</a>
					</span>
					<div class="vcm-submenu-wrap">
						<ul class="vcm-submenu-ul" data-menu-scope="app">
							<li>
								<div class="<?php echo ($highlight == 'appconfig' ? "vmenulinkactive" : "vmenulink"); ?>">
									<a href="index.php?option=com_vikchannelmanager&amp;task=appconfig">
										<?php VikBookingIcons::e('magic'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo JText::_('VCMMENUAPPGENSET'); ?></span>
										</span>
									</a>
								</div>
							</li>
						</ul>
					</div>
				</li><?php
				}
				if (!empty($module['id'])) {
					$module_name = ucfirst($module['name']);
					if (isset($module['ical_channel'])) {
						$module_name = ucfirst($module['ical_channel']['name']);
					} elseif ($module['uniquekey'] == VikChannelManagerConfig::ICAL) {
						$module_name = 'iCal';
					} elseif ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
						$module_name = 'Airbnb API';
					} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
						$module_name = 'Google Hotel';
					} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
						$module_name = 'Google VR';
					} elseif ($module['uniquekey'] == VikChannelManagerConfig::VRBOAPI) {
						$module_name = 'Vrbo API';
					} elseif ($module['uniquekey'] == VikChannelManagerConfig::AI) {
						$module_name = 'AI';
					} elseif ($module['uniquekey'] == VikChannelManagerConfig::DAC) {
						$module_name = 'Door Access Control';
					} elseif ($module['uniquekey'] == VikChannelManagerConfig::CTRIP) {
						$module_name = 'Trip.com';
					}
					// check if multi-columns are needed
					$multi_columns = (count($mod_rows) > 3);
					// check for current channel logo
					$channel_logo = null;
					if ($module['uniquekey'] == VikChannelManagerConfig::ICAL) {
						$channel_logo = $custom_ical_logo ? $custom_ical_logo : $channel_logo;
					} else {
						$channel_logo = VikChannelManager::getLogosInstance($module['name'])->getSmallLogoURL();
					}
					?>
				<li class="vcm-menu-parent-li <?php echo $li_mod_class; ?>">
					<span>
						<a href="index.php?option=com_vikchannelmanager&amp;task=setmodule&amp;id=<?php echo $module['id'] . (isset($module['ical_channel']) ? '-' . $module['ical_channel']['id'] : ''); ?>">
							<span class="vcm-menu-item-avatar">
							<?php
							if (!empty($channel_logo)) {
								?>
								<img src="<?php echo $channel_logo; ?>" />
								<?php
							} elseif ($module['uniquekey'] == VikChannelManagerConfig::MOBILEAPP) {
								VikBookingIcons::e('mobile');
							} else {
								VikBookingIcons::e('cloud');
							}
							?>
							</span>
							<span class="vcm-menu-parent-txt"><?php echo $module_name; ?></span>
							<?php VikBookingIcons::e('chevron-down', 'vcm-submenu-chevron'); ?>
						</a>
					</span>
					<div class="vcm-submenu-wrap<?php echo $multi_columns ? ' vcm-submenu-wrap-multi vcm-submenu-wrap-multi-notitle' : ''; ?>">
						<ul class="vcm-submenu-ul">
						<?php
						if (!$mod_rows) {
							echo '<li>' . JText::_('VCM_NO_OTHER_CHANNELS') . '</li>';
						}
						foreach ($mod_rows as $ch_index => $r) {
							if ($multi_columns && ($ch_index % 2) !== 0) {
								// in case of multi-columns, include only even channels
								continue;
							}
							// adjust channel name
							$chname = $r['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb API' : ucfirst($r['name']);
							$chname = $r['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL ? 'Google Hotel' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::GOOGLEVR ? 'Google VR' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::VRBOAPI ? 'Vrbo API' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::AI ? 'AI' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::DAC ? 'Door Access Control' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::CTRIP ? 'Trip.com' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::ICAL && !isset($r['ical_channel']) ? 'iCal' : $chname;
							?>
							<li>
								<div class="vmenulink">
									<a href="index.php?option=com_vikchannelmanager&amp;task=setmodule&amp;id=<?php echo $r['id']; ?>">
										<span class="vcm-submenu-item-helper-avatar">
										<?php
										if (!empty($r['img'])) {
											// channel tiny logo
											?>
											<img src="<?php echo $r['img']; ?>" />
											<?php
										} else {
											// default icon
											VikBookingIcons::e(($r['uniquekey'] == VikChannelManagerConfig::MOBILEAPP ? 'mobile' : 'cloud'));
										}
										?>
										</span>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo $chname; ?></span>
										</span>
									</a>
								</div>
							</li>
							<?php
						}

						if (!count($mod_rows) && $module['uniquekey'] == VikChannelManagerConfig::ICAL && isset($module['ical_channel'])) {
							// re-push main iCal channel when this is the only active channel
							?>
							<li>
								<div class="vmenulink">
									<a href="index.php?option=com_vikchannelmanager&amp;task=setmodule&amp;id=<?php echo $module['id']; ?>">
										<?php VikBookingIcons::e('globe'); ?>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt">iCal</span>
										</span>
									</a>
								</div>
							</li>
						<?php
						}
						?>
						</ul>
					<?php
					// parse the rest of the channels
					if ($multi_columns) {
						?>
						<ul class="vcm-submenu-helper-ul">
						<?php
						foreach ($mod_rows as $ch_index => $r) {
							if (($ch_index % 2) === 0) {
								// in case of multi-columns, include only odd channels in the second column
								continue;
							}
							// adjust channel name
							$chname = $r['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb API' : ucfirst($r['name']);
							$chname = $r['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL ? 'Google Hotel' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::GOOGLEVR ? 'Google VR' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::VRBOAPI ? 'Vrbo API' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::AI ? 'AI' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::DAC ? 'Door Access Control' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::CTRIP ? 'Trip.com' : $chname;
							$chname = $r['uniquekey'] == VikChannelManagerConfig::ICAL && !isset($r['ical_channel']) ? 'iCal' : $chname;
							?>
							<li>
								<div class="vmenulink">
									<a href="index.php?option=com_vikchannelmanager&amp;task=setmodule&amp;id=<?php echo $r['id']; ?>">
										<span class="vcm-submenu-item-helper-avatar">
										<?php
										if (!empty($r['img'])) {
											// channel tiny logo
											?>
											<img src="<?php echo $r['img']; ?>" />
											<?php
										} else {
											// default icon
											VikBookingIcons::e(($r['uniquekey'] == VikChannelManagerConfig::MOBILEAPP ? 'mobile' : 'cloud'));
										}
										?>
										</span>
										<span class="vcm-submenu-item">
											<span class="vcm-submenu-item-txt"><?php echo $chname; ?></span>
										</span>
									</a>
								</div>
							</li>
							<?php
						}
						?>
						</ul>
						<?php
					}
					?>
					</div>
				</li><?php
				} else {
					?>
				<li class="vcm-menu-parent-li vcmlidisabled">
					<span><?php VikBookingIcons::e('ban'); ?><a href="index.php?option=com_vikchannelmanager"><?php echo JText::_('VCMNOCHANNELSACTIVE'); ?></a></span>
				</li><?php
				}
				?>
			</ul>
			<div class="vcm-menu-updates">
				<button type="button" class="vcm-multitasking-apps" title="<?php echo htmlspecialchars(JText::_('VBO_MULTITASK_PANEL'), ENT_QUOTES, 'UTF-8'); ?>">
					<?php VikBookingIcons::e('th'); ?>
				</button>
			</div>
			<?php
			// prepare the layout data array
			$layout_data = [
				'btn_trigger' => '.vcm-multitasking-apps',
			];
			// render the multitasking layout
			echo JLayoutHelper::render('sidepanel.multitasking', $layout_data);
			?>
		</div>
	</div>

	<script type="text/javascript">
	jQuery(function() {
		jQuery('.vcm-menu-parent-li').hover(
			function() {
				jQuery(this).addClass('vcm-menu-parent-li-opened');
				jQuery(this).find('.vcm-submenu-wrap').addClass('vcm-submenu-wrap-active');
			},
			function() {
				jQuery(this).removeClass('vcm-menu-parent-li-opened');
				jQuery(this).find('.vcm-submenu-wrap').removeClass('vcm-submenu-wrap-active');
			}
		);

		// handle quick actions storage
		jQuery('.vcm-menu-right').find('.vcm-submenu-ul').find('a').on('click', function(e) {
			if (!jQuery(this).find('.vcm-submenu-item-txt').length) {
				// nothing to do
				return true;
			}

			// handle the clicked menu entry
			e.preventDefault();

			try {
				// register clicked page
				VBOCore.registerAdminMenuAction({
					name: jQuery(this).find('.vcm-submenu-item-txt').text(),
					href: jQuery(this).attr('href'),
				}, 'vcm');
			} catch(e) {
				console.error(e);
			}

			// proceed with the navigation
			window.location.href = jQuery(this).attr('href');

			return true;
		});

		// populate quick actions sub-menu helpers
		jQuery('.vcm-submenu-ul[data-menu-scope]').each(function() {
			let menu_ul = jQuery(this);
			let scope = menu_ul.attr('data-menu-scope');
			if (!scope) {
				scope = '';
			}
			let wrapper = menu_ul.closest('.vcm-submenu-wrap');
			if (!wrapper || !wrapper.length || wrapper.hasClass('vcm-submenu-wrap-multi') || wrapper.find('.vcm-submenu-helper-ul').length) {
				return;
			}
			let menu_scope_actions = [];
			try {
				menu_scope_actions = VBOCore.getAdminMenuActions(scope);
			} catch(e) {
				console.error(e);
			}
			if (!Array.isArray(menu_scope_actions) || !menu_scope_actions.length) {
				return;
			}
			wrapper.addClass('vcm-submenu-wrap-multi');
			let quick_actions = jQuery('<ul></ul>').addClass('vcm-submenu-helper-ul');
			quick_actions.append('<li class="vcm-submenu-helper-lbl-li"><span class="vcm-submenu-helper-lbl-txt">' + Joomla.JText._('VCM_QUICK_ACTIONS') + '</span></li>');
			menu_scope_actions.forEach((action, index) => {
				let is_pinned = action.hasOwnProperty('pinned') && action['pinned'];
				let quick_actions_entry = jQuery('<li></li>').addClass((is_pinned ? 'vcm-submenu-item-helper-pinned' : 'vcm-submenu-item-helper-unpinned'));
				let quick_actions_div = jQuery('<div></div>').addClass('vmenulink');
				let quick_action_link = jQuery('<a></a>').attr('href', action['href']).addClass('vcm-submenu-item-helper-link');
				if (action.hasOwnProperty('target') && action['target']) {
					quick_action_link.attr('target', action['target']);
				}
				if (action.hasOwnProperty('img') && action['img']) {
					let quick_action_img = jQuery('<span></span>').addClass('vcm-submenu-item-helper-avatar');
					quick_action_img.append('<img src="' + action['img'] + '" />');
					quick_action_link.append(quick_action_img);
				}
				let quick_action_name = jQuery('<span></span>').addClass('vcm-submenu-item-helper-txt').text(action['name']);
				quick_action_link.append(quick_action_name);
				quick_actions_div.append(quick_action_link);
				let quick_action_pin = jQuery('<span></span>').addClass('vcm-submenu-item-helper-setpin').on('click', function() {
					// toggle pinned status and update admin menu action
					if (!action.hasOwnProperty('pinned')) {
						action['pinned'] = !is_pinned;
					} else {
						action['pinned'] = !action['pinned'];
					}
					try {
						// update local storage
						VBOCore.updateAdminMenuAction(action, scope);
						// trigger event
						VBOCore.emitEvent('vbo-adminmenu-quickactions-update');
					} catch(e) {
						console.error(e);
					}
					// update action status
					if (action['pinned']) {
						jQuery(this).closest('li').removeClass('vcm-submenu-item-helper-unpinned').addClass('vcm-submenu-item-helper-pinned');
					} else {
						jQuery(this).closest('li').removeClass('vcm-submenu-item-helper-pinned').addClass('vcm-submenu-item-helper-unpinned');
					}
				});
				quick_action_pin.html('<?php VikBookingIcons::e('thumbtack'); ?>');
				quick_actions_div.append(quick_action_pin);
				quick_actions_entry.append(quick_actions_div);
				quick_actions.append(quick_actions_entry);
			});
			wrapper.append(quick_actions);
		});

		// register event to update the pinned quick actions
		if (typeof VBOCore !== 'undefined') {
			document.addEventListener('vbo-adminmenu-quickactions-update', VBOCore.debounceEvent(() => {
				let menu_scopes = [];
				jQuery('.vcm-submenu-ul[data-menu-scope]').each(function() {
					let menu_ul = jQuery(this);
					let scope = menu_ul.attr('data-menu-scope');
					if (!scope) {
						scope = '';
					}
					if (menu_scopes.indexOf(scope) < 0) {
						menu_scopes.push(scope);
					}
				});
				let admin_menu_actions = [];
				menu_scopes.forEach((scope) => {
					let menu_actions = VBOCore.getAdminMenuActions(scope);
					admin_menu_actions.push({
						scope: scope,
						actions: menu_actions,
					});
				});
				VBOCore.doAjax(
					"<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=menuactions.update'); ?>",
					{
						actions: admin_menu_actions,
					},
					(resp) => {
						// do nothing
					},
					(err) => {
						// log the error
						console.error(err.responseText);
					}
				);
			}, 500));
		}

		// define we are in VCM
		try {
			VBOCore.setOptions({
				is_vcm: true,
			});
		} catch(e) {
			// do nothing
		}

		// register shortcuts
		var vcm_menu_last_keydown = null;
		window.addEventListener('keydown', (e) => {
			e = e || window.event;
			if (!e.key) {
				return;
			}

			let modifier = 'ctrl';

			// change the modifier depending on the platform
			if (navigator.platform.toUpperCase().indexOf('MAC') === 0) {
				modifier = 'meta';
			}

			if (e.shortcut([modifier, 13])) {
				// toggle multitask panel
				VBOCore.emitEvent(VBOCore.multitask_shortcut_event);
				return;
			}

			if (VBOCore.side_panel_on && e.shortcut([modifier, 'F'])) {
				// focus search admin widget in multitask panel
				VBOCore.emitEvent(VBOCore.multitask_searchfs_event);
				e.preventDefault();

				return;
			}

			if (e.shortcut([modifier, 'K'])) {
				// set special key listener for sequences of combos
				e.preventDefault();
				vcm_menu_last_keydown = 'k';
				return;
			}

			if (vcm_menu_last_keydown === 'k' && (e.shortcut([modifier, 'A']) || e.shortcut([modifier, 'D']) || e.shortcut([modifier, 'L']))) {
				// trigger event to change color scheme preferences
				e.preventDefault();
				// unset special key
				vcm_menu_last_keydown = '';
				// activate requested color scheme preference
				let color_schemes = {
					a: 'auto',
					d: 'dark',
					l: 'light',
				};
				// dispatch the event to let VCM update color scheme
				VBOCore.emitEvent('vcm-set-color-scheme', {scheme: color_schemes[e.key]});
				return;
			}

			// always unset last key if this point gets reached
			vcm_menu_last_keydown = '';
		}, true);

		// register event to set and load the preferred color scheme
		document.addEventListener('vcm-set-color-scheme', (e) => {
			let scheme = e?.detail?.scheme;
			if (!scheme) {
				return;
			}
			let vcm_css_base_uri = '<?php echo VCM_ADMIN_URI . 'assets/css/vcm-appearance-%s.css'; ?>';
			let vbo_css_base_uri = '<?php echo VBO_ADMIN_URI . (VCMPlatformDetection::isWordPress() ? 'resources/' : '') . 'vbo-appearance-%s.css'; ?>';
			let vcm_css_base_id  = 'vcm-css-appearance-';
			let vbo_css_base_id  = 'vbo-css-appearance-';
			let vcm_css_modes 	 = {
				auto: vcm_css_base_uri.replace('%s', 'auto'),
				dark: vcm_css_base_uri.replace('%s', 'dark'),
				light: null
			};
			if (!vcm_css_modes.hasOwnProperty(scheme)) {
				return;
			}
			// set/unset CSS files from DOM
			for (let app_mode in vcm_css_modes) {
				if (!vcm_css_modes.hasOwnProperty(app_mode) || !vcm_css_modes[app_mode]) {
					continue;
				}
				if (app_mode == scheme) {
					// set this CSS file
					jQuery('head').append('<link rel="stylesheet" id="' + vcm_css_base_id + app_mode + '" href="' + vcm_css_modes[app_mode] + '" media="all">');
				} else {
					// unset this CSS file
					if (jQuery('link#' + vcm_css_base_id + app_mode).length) {
						jQuery('link#' + vcm_css_base_id + app_mode).remove();
					} else if (jQuery('link#' + vcm_css_base_id + app_mode + '-css').length) {
						// WP framework may add "-css" as suffix to the given ID
						jQuery('link#' + vcm_css_base_id + app_mode + '-css').remove();
					}
					// check if the VBO related CSS file should be unset too
					if (jQuery('link#' + vbo_css_base_id + app_mode).length) {
						jQuery('link#' + vbo_css_base_id + app_mode).remove();
					} else if (jQuery('link#' + vbo_css_base_id + app_mode + '-css').length) {
						// WP framework may add "-css" as suffix to the given ID
						jQuery('link#' + vbo_css_base_id + app_mode + '-css').remove();
					}
				}
			}
			// silently update configuration value
			VBOCore.doAjax(
				"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=configuration.update'); ?>",
				{
					settings: {
						appearance_pref: scheme,
					}
				},
				(success) => {
					// do nothing
				},
				(error) => {
					console.error(error);
				}
			);
		});
	});
	</script>

		<?php
		// handle notifications badge
		$launch_notifs = (!empty($sess_notifs) && $sess_notifs > 0);
		VikChannelManager::retrieveNotifications($launch_notifs);

		// handle subscription expiration reminder modal
		if (is_array($expiration_reminder) && $expiration_reminder['days_to_exp'] >= 0) {
			// subscription is expiring, but it's not expired yet, display a modal-reminder
			?>
	<div class="vcm-info-overlay-block vcm-info-overlay-expiration-reminder">
		<div class="vcm-info-overlay-content">
			<h3 style="color: var(--vcm-red-color);"><i class="vboicn-warning"></i> <?php echo JText::_('VCM_EXPIRATION_REMINDERS'); ?></h3>
			<div>
				<h4><?php echo JText::sprintf('VCM_EXPIRATION_REMINDER_DAYS', $expiration_reminder['days_to_exp'], $expiration_reminder['expiration_ymd']); ?></h4>
			</div>
			<div class="vcm-info-overlay-footer">
				<div class="vcm-info-overlay-footer-right">
					<button type="button" class="btn btn-danger" onclick="jQuery('.vcm-info-overlay-expiration-reminder').fadeOut();"><?php echo JText::_('VCMALERTMODALOK'); ?></button>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		jQuery(function() {
			jQuery('.vcm-info-overlay-expiration-reminder').fadeIn();
		});
	</script>
			<?php
		}
	}

	public static function printFooter()
	{
		echo '<br clear="all" />' . '<div id="hmfooter">Vik Channel Manager v.'.VIKCHANNELMANAGER_SOFTWARE_VERSION.' - <a href="https://e4jconnect.com/" target="_blank">e4jConnect</a></div>';
	}

	/**
	 * Load the necessary Vik Booking assets to let the admin widgets work
	 * properly also within the whole back-end section of Vik Channel Manager.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.11
	 */
	public static function loadExternalAssets()
	{
		if (VCMPlatformDetection::isWordPress()) {
			// on WordPress, Vik Booking has loaded the assets for externals already
			return;
		}

		static $ext_assets_loaded = null;

		if ($ext_assets_loaded) {
			return;
		}

		// cache the loaded flag
		$ext_assets_loaded = 1;

		// main library (needed to fetch the version of Vik Booking)
		require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';

		if (!method_exists('VikBooking', 'getAdminWidgetsInstance')) {
			// Vik Booking must be outdated
			return;
		}

		// take care of the language handlers
		if (defined('_JEXEC') && JFactory::getApplication()->input->get('option') == 'com_vikchannelmanager') {
			$lang = JFactory::getLanguage();
			$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		// load the necessary JS and CSS assets
		$document = JFactory::getDocument();
		$internalFilesOptions = array('version' => (defined('VIKBOOKING_SOFTWARE_VERSION') ? VIKBOOKING_SOFTWARE_VERSION : date('Y')));

		$document->addScript(VBO_ADMIN_URI . 'resources/vbocore.js', $internalFilesOptions, array('id' => 'vbo-core-script'));
		$document->addScript(VBO_ADMIN_URI . 'resources/toast.js', $internalFilesOptions, array('id' => 'vbo-toast-script'));
		$document->addStyleSheet(VBO_ADMIN_URI . 'resources/toast.css', $internalFilesOptions, array('id' => 'vbo-toast-style'));

		/**
		 * Register CMS HTML helpers from Vik Booking.
		 */
		try {
			// always prepare ajax requests to pass a csrf token.
			JHtml::addIncludePath(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html');
			JHtml::_('vbohtml.scripts.ajaxcsrf');
		} catch (Exception $e) {
			// do nothing
		}

		// build AJAX uri endpoints
		$widget_ajax_uri 	= VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=exec_admin_widget');
		$assets_ajax_uri 	= VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=widgets_get_assets');
		$multitask_ajax_uri = VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=exec_multitask_widgets');
		$watchdata_ajax_uri = VikChannelManager::ajaxUrl('index.php?option=com_vikbooking&task=widgets_watch_data');
		$current_page_name 	= defined('ABSPATH') ? 'wp-admin' : 'administrator';
		$current_page_uri 	= htmlspecialchars((string) JUri::getInstance(), ENT_QUOTES);

		// check if the notification audio file exists within VCM
		$notif_audio_path = implode(DIRECTORY_SEPARATOR, [VCM_ADMIN_PATH, 'assets', 'css', 'audio', 'new_notification.mp3']);
		$notif_audio_url  = is_file($notif_audio_path) ? (VCM_ADMIN_URI . implode('/', ['assets', 'css', 'audio', 'new_notification.mp3'])) : null;

		// add the necessary script declaration
		$document->addScriptDeclaration(
<<<JS
(function($) {
	'use strict';

	$(function() {

		VBOToast.create(VBOToast.POSITION_TOP_RIGHT);

		VBOCore.setOptions({
			widget_ajax_uri:    "$widget_ajax_uri",
			assets_ajax_uri: 	"$assets_ajax_uri",
			multitask_ajax_uri: "$multitask_ajax_uri",
			watchdata_ajax_uri: "$watchdata_ajax_uri",
			current_page: 	    "$current_page_name",
			current_page_uri:   "$current_page_uri",
			notif_audio_url: 	"$notif_audio_url",
		});

	});
})(jQuery);
JS
		);

		// finally, preload the admin widgets
		VikBooking::getAdminWidgetsInstance()->getWidgetNames($preload = true);

		return;
	}

	private static function isHotelHighlighted($task)
	{
		$av_task = array(
			'rooms',
			'roomsynch',
			'hoteldetails',
			'inventory',
			'trinventory',
			'roomsrar',
			'bcahcont',
			'bcahsummary',
			'bcarplans',
			'bcarcont',
			'bcapnotif',
			'bphotos',
			'bpromo',
			'bpromonew',
			'bpromoedit',
			'egpromo',
			'egpromonew',
			'egpromoedit',
			'airbnbpromo',
			'airbnbpromonew',
			'airbnbpromoedit',
			'airbnblistings',
			'airbnbmnglisting',
			'hostingquality',
			'vrbolistings',
			'vrbomnglisting',
			'bmngproperty',
			'googlevrlistings',
			'googlevrmnglisting',
			'listings',
			'icalchannels',
			'dacdevices',
		);
		return (in_array($task, $av_task));
	}
	
	private static function isOrderHighlighted($task)
	{
		$av_task = array(
			'customa',
			'tacstatus',
			'revexpress',
			'oversight',
			'avpush',
			'avpushsubmit',
			'ratespush',
			'ratespushsubmit',
			'smartbalancer',
			'smartbalancerstats',
			'editsmartbalancer',
			'newsmartbalancer',
			'reviews',
			'opportunities',
		);
		return (in_array($task, $av_task));
	}

	private static function isAppHighlighted($task)
	{
		$av_task = array(
			'appconfig',
		);
		return (in_array($task, $av_task));
	}
	
	private static function getHotelDefaultTask($channel)
	{
		switch ($channel) {
			case VikChannelManagerConfig::EXPEDIA: return 'rooms'; break;
			case VikChannelManagerConfig::TRIP_CONNECT: return 'hoteldetails'; break;
			case VikChannelManagerConfig::TRIVAGO: return 'hoteldetails'; break;
			case VikChannelManagerConfig::BOOKING: return 'rooms'; break;
			case VikChannelManagerConfig::AIRBNB: return 'listings'; break;
			case VikChannelManagerConfig::FLIPKEY: return 'listings'; break;
			case VikChannelManagerConfig::HOLIDAYLETTINGS: return 'listings'; break;
			case VikChannelManagerConfig::AGODA: return 'rooms'; break;
			case VikChannelManagerConfig::WIMDU: return 'listings'; break;
			case VikChannelManagerConfig::HOMEAWAY: return 'listings'; break;
			case VikChannelManagerConfig::VRBO: return 'listings'; break;
			case VikChannelManagerConfig::YCS50: return 'rooms'; break;
			case VikChannelManagerConfig::BEDANDBREAKFASTIT: return 'rooms'; break;
			case VIkChannelManagerConfig::MOBILEAPP: return 'hoteldetails'; break;
			case VikChannelManagerConfig::DESPEGAR: return 'rooms'; break;
			case VikChannelManagerConfig::OTELZ: return 'rooms'; break;
			case VikChannelManagerConfig::GARDAPASS: return 'rooms'; break;
			case VikChannelManagerConfig::BEDANDBREAKFASTEU: return 'rooms'; break;
			case VikChannelManagerConfig::BEDANDBREAKFASTNL: return 'rooms'; break;
			case VikChannelManagerConfig::FERATEL: return 'rooms'; break;
			case VikChannelManagerConfig::PITCHUP: return 'rooms'; break;
			case VikChannelManagerConfig::HOSTELWORLD: return 'rooms'; break;
			case VikChannelManagerConfig::CAMPSITESCOUK: return 'listings'; break;
			case VikChannelManagerConfig::ICAL: return 'icalchannels'; break;
			case VikChannelManagerConfig::AIRBNBAPI: return 'rooms'; break;
			case VikChannelManagerConfig::GOOGLEHOTEL: return 'rooms'; break;
			case VikChannelManagerConfig::GOOGLEVR: return 'rooms'; break;
			case VikChannelManagerConfig::VRBOAPI: return 'rooms'; break;
			case VikChannelManagerConfig::CTRIP: return 'rooms'; break;
		}
		
		return "";
	}
	
	private static function getOrderDefaultTask($channel)
	{
		switch ($channel) {
			case VikChannelManagerConfig::EXPEDIA: return 'oversight'; break;
			case VikChannelManagerConfig::TRIP_CONNECT: return 'tacstatus'; break;
			case VikChannelManagerConfig::TRIVAGO: return ''; break;
			case VikChannelManagerConfig::BOOKING: return 'oversight'; break;
			case VikChannelManagerConfig::AIRBNB: return ''; break;
			case VikChannelManagerConfig::FLIPKEY: return ''; break;
			case VikChannelManagerConfig::HOLIDAYLETTINGS: return ''; break;
			case VikChannelManagerConfig::AGODA: return 'oversight'; break;
			case VikChannelManagerConfig::WIMDU: return ''; break;
			case VikChannelManagerConfig::HOMEAWAY: return ''; break;
			case VikChannelManagerConfig::VRBO: return ''; break;
			case VikChannelManagerConfig::YCS50: return 'oversight'; break;
			case VikChannelManagerConfig::BEDANDBREAKFASTIT: return 'oversight'; break;
			case VikChannelManagerConfig::MOBILEAPP: return 'appconfig'; break;
			case VikChannelManagerConfig::DESPEGAR: return 'oversight'; break;
			case VikChannelManagerConfig::OTELZ: return 'oversight'; break;
			case VikChannelManagerConfig::GARDAPASS: return 'oversight'; break;
			case VikChannelManagerConfig::BEDANDBREAKFASTEU: return 'oversight'; break;
			case VikChannelManagerConfig::BEDANDBREAKFASTNL: return 'oversight'; break;
			case VikChannelManagerConfig::FERATEL: return 'oversight'; break;
			case VikChannelManagerConfig::PITCHUP: return 'oversight'; break;
			case VikChannelManagerConfig::HOSTELWORLD: return 'oversight'; break;
			case VikChannelManagerConfig::CAMPSITESCOUK: return ''; break;
			case VikChannelManagerConfig::ICAL: return ''; break;
			case VikChannelManagerConfig::AIRBNBAPI: return 'oversight'; break;
			case VikChannelManagerConfig::GOOGLEHOTEL: return 'oversight'; break;
			case VikChannelManagerConfig::GOOGLEVR: return 'oversight'; break;
			case VikChannelManagerConfig::VRBOAPI: return 'oversight'; break;
			case VikChannelManagerConfig::CTRIP: return 'oversight'; break;
		}
		
		return "";
	}
	
	public static function load_css_js()
	{
		static $loaded = null;

		if ($loaded) {
			return;
		}

		// turn flag on
		$loaded = 1;

		$document = JFactory::getDocument();
		$vik = new VikApplication(VersionListener::getID());

		$vik->loadFramework('jquery.framework');

		$vik->addScript(VCM_ADMIN_URI.'assets/js/jquery-ui.min.js');
		$document->addStyleSheet(VCM_ADMIN_URI.'assets/css/jquery-ui.min.css');
		if (VCMPlatformDetection::isWordPress()) {
			/**
			 * @wponly  load the proper assets for jQuery
			 */
			$document->addScript(null, array(), array('id' => 'jquery-ui-dialog'));
		}

		/**
		 * We pass the version to the main CSS files to avoid cache.
		 * 
		 * @since 	1.7.0
		 */
		$internalFilesOptions = array('version' => VIKCHANNELMANAGER_SOFTWARE_VERSION);
		
		$document->addStyleSheet(VCM_ADMIN_URI.'assets/css/vikchannelmanager.css', $internalFilesOptions, array('id' => 'vcm-style'));
		$document->addStyleSheet(VCM_ADMIN_URI.'assets/css/vcm-channels.css', $internalFilesOptions, array('id' => 'vcm-channels-style'));
		if (is_file(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.'vboicomoon.css')) {
			$document->addStyleSheet(VBO_ADMIN_URI.'resources/fonts/vboicomoon.css');
		}

		if (VCMPlatformDetection::isWordPress()) {
			VikChannelManager::loadPortabilityCSS($document);
		} else {
			$vik->loadPortabilityCSS($document);
		}

		/**
		 * We attempt to load also FontAwesome from Vik Booking.
		 * 
		 * @since 	1.6.13
		 */
		if (self::requireFontAwesome()) {
			VikBookingIcons::loadAssets();
		}

		/**
		 * Load the proper CSS file according to the appearance preferences.
		 * 
		 * @since 	1.8.3
		 */
		VikChannelManager::loadAppearancePreferenceAssets();

		/**
		 * Load the necessary Vik Booking assets to let the admin widgets work
		 * properly also within the whole back-end section of Vik Channel Manager.
		 * This method will also load the VBOCore JS object.
		 * 
		 * @since 	1.8.11
		 */
		self::loadExternalAssets();
	}

	/**
	 * Some Views are rendered by AJAX, and in order to call the methods
	 * of the class VikBookingIcons, they may need to require the class
	 * without actually loading the assets. We use this separate method.
	 * 
	 * @return 	boolean
	 * 
	 * @since 	1.7.2
	 */
	public static function requireFontAwesome()
	{
		static $fa_loaded = null;

		if ($fa_loaded) {
			return true;
		}

		$vbo_icons_path = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'icons.php';

		if (!is_file($vbo_icons_path)) {
			return false;
		}

		// require the library
		require_once $vbo_icons_path;

		// turn loaded flag on
		$fa_loaded = 1;

		return true;
	}

	public static function load_complex_select()
	{
		/**
		 * Attempt to load select2 from VikBooking, as the version may differ.
		 * 
		 * @since 	1.9.6
		 */
		try {
			VikBooking::getVboApplication()->loadSelect2();
		} catch (Throwable $e) {
			// fallback to VCM assets
			$document = JFactory::getDocument();
			$vik = new VikApplication(VersionListener::getID());
			$vik->addScript(VCM_ADMIN_URI.'assets/js/select2/select2.min.js');
			$document->addStyleSheet(VCM_ADMIN_URI.'assets/js/select2/select2.css');
		}
	}
	
	public static function printBlockVersionView()
	{
		?>
		<div class="vcmprogramblockeddiv">
			<div class="vcmupdater">
				<i class="vboicn-cloud-download"></i>
				<span class="vcmprogramblockedlabel">
					<?php echo JText::_('VCMPROGRAMBLOCKEDMESSAGE'); ?>
				</span>
				<span class="vcmupdatedashbutton">
					<a href="index.php?option=com_vikchannelmanager&task=update_program" class="vcmupdatenowlink"><?php echo JText::_('VCMUPDATENOWBTN'); ?></a>
				</span>
			</div>
		</div>
		<?php
	}

	public static function parseChannelSettings($channel)
	{
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `settings` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".$dbo->quote($channel['idchannel'])." LIMIT 1;";
		$dbo->setQuery($q);
		$db_settings = $dbo->loadResult();
		if (!$db_settings) {
			return $channel['settings'];
		}
		
		$db_settings = json_decode($db_settings, true);
		
		if (empty($db_settings)) {
			return $channel['settings'];
		}

		foreach ($db_settings as $k => $arr) {
			if (!isset($channel['settings'][$k])) {
				continue;
			}

			$channel['settings'][$k]['value'] = $arr['value'] ?? '';
		}
		
		return $channel['settings'];
	}
	
	public static function loadDatePicker()
	{
		$document = JFactory::getDocument();

		$ldecl = '
		jQuery(function(){'."\n".'
			jQuery.datepicker.regional["vikchannelmanager"] = {'."\n".'
				closeText: "'.JText::_('VCMJQCALDONE').'",'."\n".'
				prevText: "'.JText::_('VCMJQCALPREV').'",'."\n".'
				nextText: "'.JText::_('VCMJQCALNEXT').'",'."\n".'
				currentText: "'.JText::_('VCMJQCALTODAY').'",'."\n".'
				monthNames: ["'.JText::_('VCMMONTHONE').'","'.JText::_('VCMMONTHTWO').'","'.JText::_('VCMMONTHTHREE').'","'.JText::_('VCMMONTHFOUR').'","'.JText::_('VCMMONTHFIVE').'","'.JText::_('VCMMONTHSIX').'","'.JText::_('VCMMONTHSEVEN').'","'.JText::_('VCMMONTHEIGHT').'","'.JText::_('VCMMONTHNINE').'","'.JText::_('VCMMONTHTEN').'","'.JText::_('VCMMONTHELEVEN').'","'.JText::_('VCMMONTHTWELVE').'"],'."\n".'
				monthNamesShort: ["'.mb_substr(JText::_('VCMMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VCMMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
				dayNames: ["'.JText::_('VCMJQCALSUN').'", "'.JText::_('VCMJQCALMON').'", "'.JText::_('VCMJQCALTUE').'", "'.JText::_('VCMJQCALWED').'", "'.JText::_('VCMJQCALTHU').'", "'.JText::_('VCMJQCALFRI').'", "'.JText::_('VCMJQCALSAT').'"],'."\n".'
				dayNamesShort: ["'.mb_substr(JText::_('VCMJQCALSUN'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALMON'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALTUE'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALWED'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALTHU'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALFRI'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALSAT'), 0, 3, 'UTF-8').'"],'."\n".'
				dayNamesMin: ["'.mb_substr(JText::_('VCMJQCALSUN'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALMON'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALTUE'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALWED'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALTHU'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALFRI'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VCMJQCALSAT'), 0, 2, 'UTF-8').'"],'."\n".'
				weekHeader: "'.JText::_('VCMJQCALWKHEADER').'",'."\n".'
				firstDay: '.JText::_('VCMJQFIRSTDAY').','."\n".'
				isRTL: false,'."\n".'
				showMonthAfterYear: false,'."\n".'
				yearSuffix: ""'."\n".'
			};'."\n".'
			jQuery.datepicker.setDefaults(jQuery.datepicker.regional["vikchannelmanager"]);'."\n".'
		});';
		$document->addScriptDeclaration($ldecl);
	}
	
	/**
	 *	Get the actions
	 */
	public static function getActions($Id = 0)
	{
		jimport('joomla.access.access');

		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($Id)) {
			$assetName = 'com_vikchannelmanager';
		} else {
			$assetName = 'com_vikchannelmanager.message.'.(int) $Id;
		}

		$actions = JAccess::getActions('com_vikchannelmanager', 'component');

		foreach ($actions as $action) {
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * This method will have to be replaced by the one inside VboApplication.
	 * However, in case an older version of Vik Booking does not support the
	 * multi-state toggle switch buttons, we can rely on this method of VCM.
	 * 
	 * @param 	string 	$name 	the input name equal for all radio buttons.
	 * @param 	string 	$value 	the current input field value to be pre-selected.
	 * @param 	array 	$values list of radio buttons with each value.
	 * @param 	array 	$labels list of contents for each button trigger.
	 * @param 	array 	$attrs 	list of associative array attributes for each button.
	 * 
	 * @return 	string 	the necessary HTML to render the multi-state toggle switch.
	 * 
	 * @since 	1.8.3
	 */
	public static function multiStateToggleSwitchField($name, $value, $values = array(), $labels = array(), $attrs = array())
	{
		static $tooltip_js_declared = null;

		// whether tooltip for titles is needed
		$needs_tooltip = false;

		// HTML container
		$multi_state_switch = '';

		if (!is_array($values) || !count($values)) {
			// values must be set or we don't know what buttons to display
			return $multi_state_switch;
		}

		// build default classes for the tri-state toggle switch (with 3 buttons)
		$def_tristate_cls = array(
			'vik-multiswitch-radiobtn-on',
			'vik-multiswitch-radiobtn-def',
			'vik-multiswitch-radiobtn-off',
		);

		// start wrapper
		$multi_state_switch .= "\n" . '<div class="vik-multiswitch-wrap">' . "\n";

		foreach ($values as $btn_k => $btn_val) {
			// build default classes for button label
			$btn_classes = array('vik-multiswitch-radiobtn');
			if (isset($def_tristate_cls[$btn_k])) {
				// push default class for a 3-state toggle switch
				array_push($btn_classes, $def_tristate_cls[$btn_k]);
			}
			// check if additional custom classes have been defined for this button
			if (isset($attrs[$btn_k]) && isset($attrs[$btn_k]['label_class']) && !empty($attrs[$btn_k]['label_class'])) {
				if (is_array($attrs[$btn_k]['label_class'])) {
					// list of additional classes for this button
					$btn_classes = array_merge($btn_classes, $attrs[$btn_k]['label_class']);
				} elseif (is_string($attrs[$btn_k]['label_class'])) {
					// multiple classes should be space-separated
					array_push($btn_classes, $attrs[$btn_k]['label_class']);
				}
			}

			// check title as first thing, even though this is passed along with the labels
			$label_title = '';
			if (isset($labels[$btn_k]) && !is_scalar($labels[$btn_k]) && isset($labels[$btn_k]['title'])) {
				$needs_tooltip = true;
				$label_title = ' title="' . addslashes(htmlentities($labels[$btn_k]['title'])) . '"';
			}

			// start button label
			$multi_state_switch .= "\t" . '<label class="' . implode(' ', $btn_classes) . '"' . $label_title . '>' . "\n";

			// check button input radio
			$radio_attributes = array();
			if (($value !== null && $value == $btn_val) || ($value === null && $btn_k === 0)) {
				// this radio button must be checked (pre-selected)
				$radio_attributes['checked'] = true;
			}
			// check if custom attributes were specified for this input
			if (isset($attrs[$btn_k]) && isset($attrs[$btn_k]['input'])) {
				// must be an associative array with key = attribute name, value = attribute value
				foreach ($attrs[$btn_k]['input'] as $attr_name => $attr_val) {
					// javascript events could be attached like 'onchange'=>'myCallback(this.value)'
					$radio_attributes[$attr_name] = $attr_val;
				}
			}
			$radio_attr_string = '';
			foreach ($radio_attributes as $attr_name => $attr_val) {
				if ($attr_val === true) {
					// short-attribute name, like "checked"
					$radio_attr_string .= $attr_name . ' ';
					continue;
				}
				$radio_attr_string .= $attr_name . '="' . $attr_val . '" ';
			}
			$multi_state_switch .= "\t\t" . '<input type="radio" name="' . $name . '" value="' . $btn_val . '" ' . $radio_attr_string . '/>' . "\n";

			// add button trigger
			$multi_state_switch .= "\t\t" . '<span class="vik-multiswitch-trigger"></span>' . "\n";

			// check button label text
			if (isset($labels[$btn_k])) {
				/**
				 * By default, the buttons of the toggle switch use an animation,
				 * which requires an absolute positioning of the "label-text".
				 * For this reason, there cannot be a minimum width for these texts
				 * and so the content should fit the default width. Usually, using
				 * a font-awesome icon is the best content. For using literal texts,
				 * like "Dark", "Light" etc.. the class "vik-multiswitch-noanimation"
				 * should be passed to the button label text.
				 */
				$label_txt = '';
				$label_class = '';
				if (!is_scalar($labels[$btn_k])) {
					// with an associative array we accept value, title and custom classes
					if (isset($labels[$btn_k]['value'])) {
						$label_txt = $labels[$btn_k]['value'];
					}
					if (isset($labels[$btn_k]['class'])) {
						$label_class = ' ' . ltrim($labels[$btn_k]['class']);
					}
				} else {
					// just a string, maybe with text or HTML mixed content
					$label_txt = $labels[$btn_k];
				}
				if (strlen($label_txt)) {
					// append button label text only if some text has been defined
					$multi_state_switch .= "\t\t" . '<span class="vik-multiswitch-txt' . $label_class . '">' . $label_txt . '</span>' . "\n";
				}
			}

			// end button label
			$multi_state_switch .= "\t" . '</label>' . "\n";
		}

		// end wrapper
		$multi_state_switch .= '</div>' . "\n";

		// check tooltip JS rendering
		if (!$tooltip_js_declared && $needs_tooltip) {
			// turn static flag on
			$tooltip_js_declared = 1;

			// add script declaration for JS rendering of tooltips
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration(
<<<JS
jQuery(function() {
	if (jQuery.isFunction(jQuery.fn.tooltip)) {
		jQuery('.vik-multiswitch-wrap label').tooltip();
	}
});
JS
			);
		}

		return $multi_state_switch;
	}
}

class OrderingManager
{
	private static $_OPTION_;
	private static $_COLUMN_KEY_;
	private static $_TYPE_KEY_;
	
	public function __construct($option, $column_key, $type_key)
	{
		self::$_OPTION_ = $option;
		self::$_COLUMN_KEY_ = $column_key;
		self::$_TYPE_KEY_ = $type_key;
	}
	
	public static function getLinkColumnOrder($task='', $text='', $col='', $type='', $def_type='', $params=array(), $active_class='')
	{
		if( empty($type) ) {
			$type = $def_type;
			$active_class = '';
		} 
		
		$url = '<a class="'.$active_class.'" href="index.php?option='.self::$_OPTION_.'&task='.$task.'&'.self::$_COLUMN_KEY_.'='.$col.'&'.self::$_TYPE_KEY_.'='.$type;
		if( count( $params ) > 0 ) {
			foreach($params as $key => $val) {
				$url .= '&'.$key.'='.$val;
			}
		}
		
		return $url.'">'.$text.'</a>';
	}
	
	/*
	 * type = 1 ASC 
	 * type = 2 DESC
	 */
	public static function getColumnToOrder($task='', $def_col='', $def_type='', $skip_session=false)
	{
		$col = VikRequest::getString(self::$_COLUMN_KEY_);
		$type = VikRequest::getString(self::$_TYPE_KEY_);
		
		$session = JFactory::getSession();
		
		if( empty( $col ) ) {
			$col =  $def_col;
			
			if( !$skip_session ) {
				$app_c = $session->get(self::$_COLUMN_KEY_.'_'.$task, '');
				$app_t = $session->get(self::$_TYPE_KEY_.'_'.$task, '');
				
				if( !empty( $app_c ) ) {
					$col = $app_c;
				}
				
				if( !empty( $app_t ) ) {
					$type = $app_t;
				}
			}
		}
		
		if( empty( $type ) ) {
			$type = $def_type;
		}
		
		$session->set(self::$_COLUMN_KEY_.'_'.$task, $col);
		$session->set(self::$_TYPE_KEY_.'_'.$task, $type);
		
		return array( 'column' => $col, 'type' => $type );
	}
	
	public static function getSwitchColumnType($task, $col, $curr_type, $types)
	{
		$session = JFactory::getSession();
		$old_c = $session->get(self::$_COLUMN_KEY_.'_'.$task, '');
		
		if( $old_c == $col ) {
			$found = -1;
			for( $i = 0; $i < count($types) && $found == -1; $i++ ) {
				if( $types[$i] == $curr_type ) {
					$found = $i;
				}
			}
			
			if( $found != -1 ) {
				$found++;
				if( $found >= count($types) ) {
					$found = 0;
				}
				
				return $types[$found];
			}
		} 
		
		return $types[count($types)-1];
	}
	
}
