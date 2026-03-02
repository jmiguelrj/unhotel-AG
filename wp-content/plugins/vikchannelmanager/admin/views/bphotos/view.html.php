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

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewBphotos extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::load_complex_select();
		
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
	
		$config = VikChannelManager::loadConfiguration();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = !empty($channel['params']) ? json_decode($channel['params'], true) : array();

		// grab the first param, probably hotelid
		$main_param = '';
		foreach ($channel['params'] as $v) {
			$main_param = $v;
			break;
		}

		if ($channel['uniquekey'] != VikChannelManagerConfig::BOOKING || empty($main_param)) {
			VikError::raiseWarning('', 'The page Photos requires the channel Booking.com to be active and set up');
			$app->redirect('index.php?option=com_vikchannelmanager');
			exit;
		}

		// fetch all rooms mapped for this account ID
		$otarooms = array();
		$prop_name = '';
		$q = "SELECT `x`.*, `r`.`name` FROM `#__vikchannelmanager_roomsxref` AS `x` LEFT JOIN `#__vikbooking_rooms` AS `r` ON `x`.`idroomvb`=`r`.`id` WHERE `x`.`idchannel`=".(int)$channel['uniquekey']." AND `x`.`prop_params` LIKE " . $dbo->quote("%{$main_param}%") . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$mapping = $dbo->loadAssocList();
			foreach ($mapping as $map) {
				if (empty($prop_name)) {
					$prop_name = $map['prop_name'];
				}
				$otarooms[$map['idroomota']] = $map;
			}
		}
		if (!count($otarooms)) {
			VikError::raiseWarning('', 'No rooms mapped for this Booking.com account. Please complete the configuration');
			$app->redirect('index.php?option=com_vikchannelmanager');
			exit;
		}

		// error from Photo API
		$photoserr = $session->get('bphotoserr', '', 'vcmbphotos');
		if (!empty($photoserr)) {
			VikError::raiseWarning('', $photoserr);
		}

		// force download
		$force = VikRequest::getInt('force', 0, 'request');

		/**
		 * The newly submitted credentials may not be yet available on the Slave,
		 * and so in order to avoid a generic "Authentication Error" on the Slave,
		 * we force the call on the Master in case this is the first time for this call.
		 * 
		 * @since 	1.7.5
		 */
		$usemaster = false;
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('bphotos_api_call');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$usemaster = true;
		}
		//

		// the Booking.com tags for all photos
		$photo_tags = array();
		if ((!isset($config['bphototags']) || empty($config['bphototags'])) && (empty($photoserr) || $force)) {
			// download photo tags from e4jConnect
			$base_url = $usemaster ? 'https://e4jconnect.com/' : 'https://slave.e4jconnect.com/';
			$e4jc_url = $base_url . "channelmanager/?r=phtags&c=".$channel['name'];
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PHTAGS Request e4jConnect.com - '.ucwords($channel['name']).' -->
<PhotosGetTagsRQ xmlns="http://www.e4jconnect.com/channels/phtagsrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$config['apikey'].'"/>
	<PhotosGetTags>
		<Fetch hotelid="' . $main_param . '" />
	</PhotosGetTags>
</PhotosGetTagsRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();

			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$session->set('bphotoserr', VikChannelManager::getErrorFromMap($rs), 'vcmbphotos');
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$app->redirect("index.php?option=com_vikchannelmanager&task=bphotos");
				exit;
			}

			// attempt to unserialize the response
			$dwnl_tags = unserialize($rs);

			if (is_array($dwnl_tags) && count($dwnl_tags)) {
				// build an associative array for the photo tags
				foreach ($dwnl_tags as $ptag) {
					$photo_tags[$ptag->id] = $ptag->tag;
				}
				// sort tags alphabetically
				asort($photo_tags);
				// update/insert the configuration value in the database
				if (isset($config['bphototags'])) {
					$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote(json_encode($photo_tags)) . " WHERE `param`='bphototags';";
				} else {
					$q = "INSERT INTO `#__vikchannelmanager_config` (`param`, `setting`) VALUES ('bphototags', " . $dbo->quote(json_encode($photo_tags)) . ");";
				}
				$dbo->setQuery($q);
				$dbo->execute();
			}
		} elseif (!empty($config['bphototags'])) {
			$photo_tags = json_decode($config['bphototags'], true);
		}

		// make sure the photo tags is an array
		$photo_tags = is_array($photo_tags) ? $photo_tags : array();

		// load photos from the session or from e4jConnect
		$photos = $session->get('bphotos', '', 'vcmbphotos');
		$refresh = $session->get('bphotos_refresh', '', 'vcmbphotos');
		$prefresh = VikRequest::getInt('refresh', 0, 'request');

		if (((!empty($refresh) || !empty($prefresh)) && empty($photoserr)) || (empty($photos) && (empty($photoserr) || $force))) {
			// download photos from e4jConnect when the page loads
			$filters = array('hotelid="' . $main_param . '"');
			$base_url = $usemaster ? 'https://e4jconnect.com/' : 'https://slave.e4jconnect.com/';
			$e4jc_url = $base_url . "channelmanager/?r=phmeta&c=".$channel['name'];
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PHMETA Request e4jConnect.com - '.ucwords($channel['name']).' -->
<PhotosGetMetaRQ xmlns="http://www.e4jconnect.com/channels/phmetarq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$config['apikey'].'"/>
	<PhotosGetMeta>
		<Fetch '.implode(' ', $filters).'/>
	</PhotosGetMeta>
</PhotosGetMetaRQ>';
			
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if ($e4jC->getErrorNo()) {
				$session->set('bphotoserr', 'CURL Error', 'vcmbphotos');
				VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
				$app->redirect("index.php?option=com_vikchannelmanager&task=bphotos");
				exit;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$session->set('bphotoserr', VikChannelManager::getErrorFromMap($rs), 'vcmbphotos');
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				$app->redirect("index.php?option=com_vikchannelmanager&task=bphotos");
				exit;
			}

			$photos = unserialize($rs);
			if ($photos === false || empty($photos) || !is_array($photos)) {
				$session->set('bphotoserr', 'Invalid Response', 'vcmbphotos');
				VikError::raiseWarning('', 'Invalid response.<br/>'.$rs);
				$app->redirect("index.php?option=com_vikchannelmanager&task=bphotos");
				exit;
			}

			// we may get duplicate values from the response, make sure the photo IDs are unique per gallery
			foreach ($photos as $prop => $prop_photos) {
				$prop_photo_ids = array();
				foreach ($prop_photos as $pk => $prop_photo) {
					$photo_identifier = $prop_photo->id . $prop_photo->url;
					if (in_array($photo_identifier, $prop_photo_ids)) {
						// duplicate photo
						unset($photos[$prop][$pk]);
					} else {
						array_push($prop_photo_ids, $photo_identifier);
					}
				}
				// reset keys
				$photos[$prop] = array_values($photos[$prop]);
			}

			// update session value for the photos downloaded
			$session->set('bphotos', $photos, 'vcmbphotos');
			$session->set('bphotos_refresh', '', 'vcmbphotos');
		}

		$photos = is_array($photos) ? $photos : array();

		if (count($photos)) {
			// make sure to add the missing rooms mapped with no images uploaded (at least one photo must exist)
			foreach ($otarooms as $otarid => $otardata) {
				if (!isset($photos[$otarid])) {
					// empty array of photos for this room
					$photos[$otarid] = array();
				}
			}
			if (!isset($photos['property'])) {
				// add an empty array of photos for the property
				$photos['property'] = array();
			}
			// sort the array by putting property as first element
			$mirror = array();
			$mirror['property'] = $photos['property'];
			foreach ($photos as $k => $v) {
				if ($k == 'property') {
					continue;
				}
				$mirror[$k] = $v;
			}
			$photos = $mirror;
			unset($mirror);
		} elseif (empty($photoserr)) {
			// no errors, but no photos, add empty arrays
			$photos['property'] = array();
			foreach ($otarooms as $otarid => $otardata) {
				// empty array of photos for this room
				$photos[$otarid] = array();
			}
		}

		// load current queue of uploaded photos
		$queue = array();
		if (count($photos)) {
			$galleries = array_keys($photos);
			foreach ($galleries as $gallery) {
				$queue[$gallery] = array();
				// check whether we have an upload queue for this gallery
				$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('bphotos_queue_' . $gallery . $main_param);
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows()) {
					$gdata = json_decode($dbo->loadResult(), true);
					$queue[$gallery] = is_array($gdata) ? $gdata : array();
				}
			}
		}

		// load photos that were added
		$photos_added = array();
		$q = "SELECT `param`,`setting` FROM `#__vikchannelmanager_config` WHERE `param` LIKE " . $dbo->quote('bphotos_added_%_' . $main_param);
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$records = $dbo->loadAssocList();
			foreach ($records as $v) {
				$photo_ids = json_decode($v['setting'], true);
				if (is_array($photo_ids) && count($photo_ids)) {
					// get property (gallery) name
					$parts = explode('bphotos_added_', $v['param']);
					$partstwo = explode('_', $parts[1]);
					if (!empty($partstwo[0])) {
						$gallery_name = $partstwo[0];
						$photos_added[$gallery_name] = $photo_ids;
					}
				}
			}
		}

		if (empty($photoserr)) {
			/**
			 * If no errors, update the config value so that next calls will be made on the Slave.
			 * 
			 * @since 	1.7.5
			 */
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`='1' WHERE `param`=" . $dbo->quote('bphotos_api_call');
			$dbo->setQuery($q);
			$dbo->execute();
		}

		$this->config = $config;
		$this->otarooms = $otarooms;
		$this->photos = $photos;
		$this->photo_tags = $photo_tags;
		$this->photoserr = $photoserr;
		$this->channel = $channel;
		$this->prop_name = $prop_name;
		$this->main_param = $main_param;
		$this->queue = $queue;
		$this->photos_added = $photos_added;

		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTBPHOTOS'), 'vikchannelmanager');
		JToolBarHelper::cancel( 'cancel', JText::_('CANCEL'));
	}
}
