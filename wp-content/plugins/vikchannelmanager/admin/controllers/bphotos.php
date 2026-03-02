<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerBphotos extends JControllerAdmin
{
	/**
	 * @var  string  the VCM base upload uri for images
	 */
	protected $base_upload_uri = 'assets/uploads/';

	/**
	 * Upload photos via AJAX onto the local server
	 */
	public function uploadLocalPhotos()
	{
		$input = JFactory::getApplication()->input;

		// get file from request
		$file = $input->files->get('file', array(), 'array');
		// mandatory request values
		$prop = $input->getString('prop', '');
		$hid  = $input->getString('hotelid', '');

		if (!is_array($file) || !count($file)) {
			throw new Exception('No files to upload.', 400);
		}

		if (empty($prop) || empty($hid)) {
			throw new Exception('Missing information for the upload.', 400);
		}

		$result = new stdClass;
		$result->status = 0;

		try {
			// try to upload the file
			$result = VikChannelManager::uploadFileFromRequest($file, $this->getUploadPath(), 'png,jpg,jpeg,bmp,heic,webp,gif');
			$result->status = 1;

			/**
			 * @todo 	check whether a thumbnail should be created and returned to VCM for a better displaying
			 */

			$result->size = JHtml::_('number.bytes', filesize($result->path), 'auto', 0);
			$result->url  = str_replace(DIRECTORY_SEPARATOR, '/', str_replace(VCM_SITE_PATH . DIRECTORY_SEPARATOR, VCM_SITE_URI, $result->path));
			$result->prop = $prop;
			$result->hid  = $hid;
		} catch (Exception $e) {
			$result->error = $e->getMessage();
			$result->code  = $e->getCode();
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * Remove photo via AJAX from the local server
	 */
	public function deleteLocalPhotos()
	{
		$input = JFactory::getApplication()->input;

		$file = $input->getString('file', '');

		if (empty($file)) {
			// throw exception only if empty filename to avoid issues with the files selected through the media manager
			throw new Exception('Image not found.', 500);
		}

		$removed = $this->removeFile($file);
		
		echo json_encode(array('status' => (int)$removed));
		exit;
	}

	/**
	 * Upload photos via AJAX onto Booking.com by making a request to e4jConnect
	 */
	public function uploadPhotosToQueue()
	{
		$dbo	= JFactory::getDbo();
		$input  = JFactory::getApplication()->input;

		// mandatory request values
		$prop 	= $input->getString('prop', '');
		$hid  	= $input->getString('hotelid', '');
		$photos = $input->get('photos', array(), 'array');
		$tags 	= $input->get('tags', array(), 'array');

		if (empty($prop) || empty($hid)) {
			throw new Exception('Missing information for the upload.', 400);
		}

		if (!is_array($photos) || !count($photos)) {
			throw new Exception('No photos to upload.', 400);
		}

		// queued array of photos and tags uploaded
		$upqueue = array();

		// compose the XML request
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=phupq&c=booking.com";
		$xmlRq = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PHUPQ Request e4jConnect.com - Booking.com -->
<PhotosUploadQueueRQ xmlns="http://www.e4jconnect.com/channels/phupqrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<PhotosUploadQueue hotelid="' . $hid . '">' . "\n";

		foreach ($photos as $k => $v) {
			/**
			 * Do not lower case the original name as it could have been uploaded with upper case chars.
			 * 
			 * @since 	1.7.5
			 */
			$v = trim($v);
			//
			if (empty($v) || strpos($v, 'http') !== 0) {
				continue;
			}
			$photo_tags = (isset($tags[$k]) && count($tags[$k]) ? $tags[$k] : array());
			$xmlRq .= '<Photo url="' . urlencode($v) . '" tags="' . implode(',', $photo_tags) . '" />' . "\n";
			// push information
			array_push($upqueue, array(
				'url' 	=> $v,
				'tags' 	=> $photo_tags,
			));
		}

		$xmlRq .= '</PhotosUploadQueue>
</PhotosUploadQueueRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlRq);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			throw new Exception("CURL Error " . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()), 500);
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
		}

		// attempt to unserialize the response
		$resp = unserialize($rs);

		if (!is_object($resp) || (empty($resp->photo_batch_id) && empty($resp->photo_pending_id))) {
			throw new Exception("Missing pending batch or photo ID", 400);
		}

		// response was successful, update the configuration value for this property
		$bphotos_queue = array();
		$has_setting = false;
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('bphotos_queue_' . $prop . $hid);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$has_setting = true;
			$bphotos_queue = json_decode($dbo->loadResult(), true);
		}

		// build new queue object
		$queue = new stdClass;
		$queue->date = JFactory::getDate()->toSql(true);
		$queue->resp = $resp;
		$queue->photos = $upqueue;
		$queue->error = '';

		// gather information from B.com about the processing status of this last upload
		$batch_ids = array();
		$pending_ids = array();
		if (!empty($resp->photo_batch_id)) {
			array_push($batch_ids, $resp->photo_batch_id);
		}
		if (!empty($resp->photo_pending_id)) {
			array_push($pending_ids, $resp->photo_pending_id);
		}
		$processing_status = $this->getUploadProcessingStatus($hid, $batch_ids, $pending_ids);
		if (is_array($processing_status)) {
			// process the B.com response and update the status for all photos in this queue
			foreach ($processing_status as $photo_status) {
				if (!is_object($photo_status) || empty($photo_status->url)) {
					// invalid or unexpected response from Booking.com
					continue;
				}
				// obtain the image filename from the url
				$url_parts = explode('/', $photo_status->url);
				// photos can be also uploaded from the media manager, so we take the last part of the URL segments
				$status_fname = $url_parts[(count($url_parts) - 1)];
				if (empty($status_fname)) {
					// unable to get proper filename from uploaded URL
					continue;
				}
				// make sure to urldecode the photo file name, if necessary
				if (strpos($status_fname, '+') !== false || strpos($status_fname, '%') !== false) {
					// spaces or other symbols will be url-encoded this way
					$status_fname = urldecode($status_fname);
				}
				// find the same image in the current queue to update its status
				foreach ($queue->photos as $k => $photo) {
					// obtain the image filename from the url
					$url_parts = explode('/', $photo['url']);
					$local_fname = $url_parts[(count($url_parts) - 1)];
					if (empty($local_fname) || $local_fname != $status_fname) {
						// filenames do not match
						continue;
					}
					// filename found
					$queue->photos[$k]['status'] = $photo_status;
				}
			}
		} else {
			// an error occurred
			$queue->error = (is_string($processing_status) ? $processing_status : 'Generic Error obtaining the status');
		}

		// update property queue by prepending the current queue to the previous ones (if any)
		$bphotos_queue = array_merge(array($queue), $bphotos_queue);

		// encode array for the response and DB
		$clientresp = json_encode($bphotos_queue);

		// store value
		if ($has_setting) {
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote($clientresp) . " WHERE `param`=" . $dbo->quote('bphotos_queue_' . $prop . $hid);
		} else {
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`, `setting`) VALUES (" . $dbo->quote('bphotos_queue_' . $prop . $hid) . ", " . $dbo->quote($clientresp) . ");";
		}
		$dbo->setQuery($q);
		$dbo->execute();

		// the response will be the new upload queue array for this property
		echo $clientresp;
		exit;
	}

	/**
	 * Removes an image from the upload queue. This image was already
	 * transmitted to Booking.com, but maybe it is no longer wanted.
	 * 
	 * @return 	void
	 */
	public function removeQueuedImage()
	{
		$dbo	= JFactory::getDbo();
		$app 	= JFactory::getApplication();
		$input  = $app->input;
		
		// mandatory request values
		$prop 	= $input->getString('prop', '');
		$hid  	= $input->getString('hid', '');
		$queue 	= $input->getInt('queue', 0);
		$photo 	= $input->getInt('photo', 0);

		$goto 	= "index.php?option=com_vikchannelmanager&task=bphotos&tab={$prop}";

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('bphotos_queue_' . $prop . $hid);
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			VikError::raiseWarning('', 'No data found in queue');
			$app->redirect($goto);
			exit;
		}
		$gdata = json_decode($dbo->loadResult(), true);
		if (!is_array($gdata) || !count($gdata)) {
			VikError::raiseWarning('', 'Empty upload queue');
			$app->redirect($goto);
			exit;
		}

		if (!isset($gdata[$queue]) || !isset($gdata[$queue]['photos'][$photo])) {
			// photo not found in queue
			VikError::raiseWarning('', 'Photo not found in queue');
			$app->redirect($goto);
			exit;
		}

		// get the filename from URL (will work only if the file was uploaded, not if it was chosen through the media manager)
		$parts = explode($this->base_upload_uri, $gdata[$queue]['photos'][$photo]['url']);
		if (!empty($parts[1])) {
			// attempt to remove the local copy of the file
			$this->removeFile($parts[1]);
		}

		// unset the requested photo
		unset($gdata[$queue]['photos'][$photo]);
		
		// reset keys
		if (count($gdata[$queue]['photos'])) {
			$gdata[$queue]['photos'] = array_values($gdata[$queue]['photos']);
		}

		// store the new queue
		$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote(json_encode($gdata)) . " WHERE `param`=" . $dbo->quote('bphotos_queue_' . $prop . $hid);
		$dbo->setQuery($q);
		$dbo->execute();

		$app->redirect($goto);
		exit;
	}

	/**
	 * AJAX request to retrieve and update the status of all pending photo ids.
	 * Echoes an array with the status for each returned photo in JSON.
	 * 
	 * @return 	void
	 */
	public function retrievePhotosStatus()
	{
		$dbo	 = JFactory::getDbo();
		$input   = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		
		// request values
		$hid  		 = $input->getString('hotelid', '');
		$old_dt 	 = $input->getString('oldest_date', '');
		$force 		 = $input->getInt('force', 0);
		$pending_ids = $input->get('pending_ids', array(), 'array');

		if (empty($hid)) {
			throw new Exception('Empty hotel id', 400);
		}

		if (!is_array($pending_ids) || !count($pending_ids)) {
			throw new Exception('No pending photo IDs', 400);
		}

		// make sure the array of pending IDs is unique
		$pending_ids = array_unique($pending_ids);

		// make sure we have processed this queue at least 3 minutes ago
		$now = time();
		$wait = 180;
		$next_exec = $session->get("bphotos_{$hid}_exec", '', 'vcmbphotos');
		$next_exec = empty($next_exec) ? ($now - ($wait * 2)) : $next_exec;
		$next_exec += $wait;
		if ($now < $next_exec && !$force) {
			throw new Exception(JText::sprintf('VCMBPHOTOERRLASTSTATUPD', date('H:i', $next_exec)), 500);
		}
		// update last execution in session to current time
		$session->set("bphotos_{$hid}_exec", $now, 'vcmbphotos');

		if (count($pending_ids) > 2) {
			// we opt to peform one single request for all pending photos
			$processing_status = $this->getUploadProcessingStatus($hid, array(), array(), (!empty($old_dt) ? $old_dt : null));
		} else {
			// we generate at most two requests, so for two photo pending ids
			$processing_status = $this->getUploadProcessingStatus($hid, array(), $pending_ids);
		}

		if (is_array($processing_status)) {
			// process the response
			foreach ($processing_status as $k => $photo_status) {
				if (!is_object($photo_status) || empty($photo_status->url) || empty($photo_status->status)) {
					// invalid or unexpected response from Booking.com
					unset($processing_status[$k]);
					continue;
				}
			}
			// load from the configuration all photo queues so that we can update the status for the next refresh
			$q = "SELECT `param`,`setting` FROM `#__vikchannelmanager_config` WHERE `param` LIKE " . $dbo->quote('bphotos_queue_%' . $hid);
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$records = $dbo->loadAssocList();
				foreach ($records as $k => $r) {
					$queues = json_decode($r['setting'], true);
					$photos_found = 0;
					foreach ($queues as $qk => $queue) {
						foreach ($queue['photos'] as $pk => $pv) {
							if (!isset($pv['status']) || empty($pv['status']['photo_pending_id'])) {
								// we do not have any information about this photo
								continue;
							}
							$photo_pending_id = $pv['status']['photo_pending_id'];
							// find this photo id in the status response
							foreach ($processing_status as $photo_status) {
								if ($photo_status->photo_pending_id == $photo_pending_id) {
									// photo found, increase counter and update status array information, which will contain the photo_id beside the photo_pending_id
									$photos_found++;
									if (strtolower($photo_status->status) == 'duplicate' && !empty($photo_status->photo_id) && !empty($photo_status->status_message) && strpos($photo_status->status_message, $photo_status->photo_id) !== false) {
										/**
										 * This is the classic status message:
										 * "This is a duplicate photo. The original photo id is : hYpC4hqKoVI"
										 * In this case the "photo_id" property is correctly set to the right one assigned during the first upload of this duplicate photo.
										 * For this reason we can just set the status to "ok" because the photo can be added to the gallery
										 */
										$photo_status->status = 'ok';
									}
									$queues[$qk]['photos'][$pk]['status'] = (array)$photo_status;
									break;
								}
							}
							
						}
					}
					if ($photos_found > 0) {
						// update record
						$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote(json_encode($queues)) . " WHERE `param`=" . $dbo->quote($r['param']);
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		} else {
			// an error occurred
			throw new Exception((is_string($processing_status) ? $processing_status : 'Generic Error obtaining the status'), 400);
		}

		echo json_encode($processing_status);
		exit;
	}

	/**
	 * AJAX request to publish eligible photos to a gallery.
	 * Echoes a successful string in case of success, or exception is thrown.
	 * 
	 * @return 	void
	 */
	public function publishPhotosToGallery()
	{
		$dbo	 = JFactory::getDbo();
		$input   = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		
		// request values
		$hid  		= $input->getString('hotelid', '');
		$prop 		= $input->getString('prop', '');
		$photo_ids 	= $input->get('photo_ids', array(), 'array');

		if (empty($hid)) {
			throw new Exception('Missing Hotel ID', 400);
		}
		if (empty($prop)) {
			throw new Exception('Empty gallery name', 400);
		}
		if (!is_array($photo_ids) || !count($photo_ids)) {
			throw new Exception('No photos received', 400);
		}

		// compose the XML request
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=phmodg&c=booking.com";
		$xmlRq = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PHMODG Request e4jConnect.com - Booking.com -->
<PhotosModGalleryRQ xmlns="http://www.e4jconnect.com/channels/phmodgrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<PhotosModGallery hotelid="' . $hid . '" roomid="' . (!empty($prop) && $prop != 'property' ? $prop : '') . '" action="insert">' . "\n";

		foreach ($photo_ids as $k => $v) {
			if (empty($v)) {
				unset($photo_ids[$k]);
				continue;
			}
			$xmlRq .= '<Photo photoid="' . $v . '" main="0" />' . "\n";
		}

		$xmlRq .= '</PhotosModGallery>
</PhotosModGalleryRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlRq);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			throw new Exception("CURL Error " . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()), 500);
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
		}

		// response must be a string "e4j.ok"
		$resp = $rs;

		if (strpos($resp, 'e4j.ok') === false) {
			throw new Exception('Invalid response ' . (string)$resp, 500);
		}

		// set session value to force a reload of the gallery for the new image added
		$session->set('bphotos_refresh', '1', 'vcmbphotos');

		// store in the db that these photo IDs were added to this gallery
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('bphotos_added_' . $prop . '_' . $hid);
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			// update configuration setting
			$current = json_decode($dbo->loadResult(), true);
			if (is_array($current)) {
				$photo_ids = array_merge($current, $photo_ids);
			}
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote(json_encode($photo_ids)) . " WHERE `param`=" . $dbo->quote('bphotos_added_' . $prop . '_' . $hid) . ";";
		} else {
			// insert configuration setting
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`, `setting`) VALUES (" . $dbo->quote('bphotos_added_' . $prop . '_' . $hid) . ", " . $dbo->quote(json_encode($photo_ids)) . ");";
		}
		$dbo->setQuery($q);
		$dbo->execute();

		// echo a success string
		echo 'e4j.ok';
		exit;
	}

	/**
	 * AJAX request to change the order of the photos in a gallery.
	 * Echoes a successful string in case of success, or exception is thrown.
	 * 
	 * @return 	void
	 */
	public function sortPhotoGallery()
	{
		$dbo	 = JFactory::getDbo();
		$input   = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		
		// request values
		$hid  		= $input->getString('hotelid', '');
		$prop 		= $input->getString('prop', '');
		$photo_ids 	= $input->get('photo_ids', array(), 'array');

		if (empty($hid)) {
			throw new Exception('Missing Hotel ID', 400);
		}
		if (empty($prop)) {
			throw new Exception('Empty gallery name', 400);
		}
		if (!is_array($photo_ids) || !count($photo_ids)) {
			throw new Exception('No photos received', 400);
		}

		// compose the XML request
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=phmodg&c=booking.com";
		$xmlRq = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PHMODG Request e4jConnect.com - Booking.com -->
<PhotosModGalleryRQ xmlns="http://www.e4jconnect.com/channels/phmodgrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<PhotosModGallery hotelid="' . $hid . '" roomid="' . (!empty($prop) && $prop != 'property' ? $prop : '') . '" action="order">' . "\n";

		foreach ($photo_ids as $k => $v) {
			if (empty($v)) {
				unset($photo_ids[$k]);
				continue;
			}
			$xmlRq .= '<Photo photoid="' . $v . '" main="0" />' . "\n";
		}

		$xmlRq .= '</PhotosModGallery>
</PhotosModGalleryRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlRq);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			throw new Exception("CURL Error " . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()), 500);
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
		}

		// response must be a string "e4j.ok"
		$resp = $rs;

		if (strpos($resp, 'e4j.ok') === false) {
			throw new Exception('Invalid response ' . (string)$resp, 500);
		}

		/**
		 * Set session value to force a reload of the gallery for the ordering. No need
		 * to alter the session values as at the next reload it will be refreshed.
		 */
		$session->set('bphotos_refresh', '1', 'vcmbphotos');

		// echo a success string
		echo 'e4j.ok';
		exit;
	}

	/**
	 * AJAX request to set a photo as main in the property gallery.
	 * Echoes a successful string in case of success, or exception is thrown.
	 * 
	 * @return 	void
	 */
	public function setMainPhotoGallery()
	{
		$dbo	 = JFactory::getDbo();
		$input   = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		
		// request values
		$hid  		= $input->getString('hotelid', '');
		$photo_id 	= $input->getString('photo_id', '');

		if (empty($hid)) {
			throw new Exception('Missing Hotel ID', 400);
		}
		if (empty($photo_id)) {
			throw new Exception('Missing Photo ID', 400);
		}

		// compose the XML request
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=phmodg&c=booking.com";
		$xmlRq = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PHMODG Request e4jConnect.com - Booking.com -->
<PhotosModGalleryRQ xmlns="http://www.e4jconnect.com/channels/phmodgrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<PhotosModGallery hotelid="' . $hid . '" roomid="" action="setmain">
		<Photo photoid="' . $photo_id . '" main="1" />
	</PhotosModGallery>
</PhotosModGalleryRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlRq);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			throw new Exception("CURL Error " . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()), 500);
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			throw new Exception(VikChannelManager::getErrorFromMap($rs), 500);
		}

		// response must be a string "e4j.ok"
		$resp = $rs;

		if (strpos($resp, 'e4j.ok') === false) {
			throw new Exception('Invalid response ' . (string)$resp, 500);
		}

		// set session value to force a reload of the gallery for the new main image
		$session->set('bphotos_refresh', '1', 'vcmbphotos');

		// echo a success string
		echo 'e4j.ok';
		exit;
	}

	/**
	 * Regular task to remove photos from one gallery (not called via AJAX).
	 * 
	 * @return 	void
	 */
	public function removeImageGallery()
	{
		$dbo	 = JFactory::getDbo();
		$app 	 = JFactory::getApplication();
		$input   = $app->input;
		$session = JFactory::getSession();
		
		// request values
		$hid  		= $input->getString('hotelid', '');
		$prop 		= $input->getString('prop', '');
		$photo_id 	= $input->getString('photo_id', '');
		$photo_ids 	= $input->get('photo_ids', array(), 'array');

		$goto 		= "index.php?option=com_vikchannelmanager&task=bphotos&tab={$prop}";

		if (empty($hid)) {
			VikError::raiseWarning('', 'Missing Hotel ID');
			$app->redirect($goto);
			exit;
		}
		if (empty($prop)) {
			VikError::raiseWarning('', 'Empty gallery name');
			$app->redirect($goto);
			exit;
		}
		$all_photo_ids = array();
		if (!empty($photo_id)) {
			array_push($all_photo_ids, $photo_id);
		} elseif (is_array($photo_ids) && count($photo_ids)) {
			$all_photo_ids = $photo_ids;
		}
		if (!is_array($all_photo_ids) || !count($all_photo_ids)) {
			VikError::raiseWarning('', 'No photos received');
			$app->redirect($goto);
			exit;
		}

		// compose the XML request
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=phmodg&c=booking.com";
		$xmlRq = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PHMODG Request e4jConnect.com - Booking.com -->
<PhotosModGalleryRQ xmlns="http://www.e4jconnect.com/channels/phmodgrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<PhotosModGallery hotelid="' . $hid . '" roomid="' . (!empty($prop) && $prop != 'property' ? $prop : '') . '" action="remove">' . "\n";

		foreach ($all_photo_ids as $k => $v) {
			if (empty($v)) {
				unset($all_photo_ids[$k]);
				continue;
			}
			$xmlRq .= '<Photo photoid="' . $v . '" main="0" />' . "\n";
		}

		$xmlRq .= '</PhotosModGallery>
</PhotosModGalleryRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlRq);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', "CURL Error " . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			$app->redirect($goto);
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			$app->redirect($goto);
			exit;
		}

		// response must be a string "e4j.ok"
		$resp = $rs;

		if (strpos($resp, 'e4j.ok') === false) {
			VikError::raiseWarning('', 'Invalid response ' . (string)$resp);
			$app->redirect($goto);
			exit;
		}

		/**
		 * Set session value to force a reload of the gallery for having removed
		 * one photo and to avoind altering the current session values.
		 */
		$session->set('bphotos_refresh', '1', 'vcmbphotos');

		// redirect to the page for the refresh
		$app->redirect($goto);
		exit;
	}

	/**
	 * Private method to obtain the processing information about the various uploads made,
	 * either by using a list of photo batch ids or photo pending ids.
	 * 
	 * @param 	string 	$hid 			the B.com Hotel ID to use for the request.
	 * @param 	array 	$batch_ids 		list of B.com upload batch ids.
	 * @param 	array 	$pending_ids 	list of B.com uploaded photo pending ids.
	 * @param 	string 	$start_date 	optional start date for retrieving the queue status.
	 * 
	 * @return 	mixed 	array of photo statuses or string on failure.
	 */
	private function getUploadProcessingStatus($hid, $batch_ids = array(), $pending_ids = array(), $start_date = null)
	{
		if (empty($hid)) {
			return 'empty hotel ID';
		}

		if (!is_array($batch_ids) || !is_array($pending_ids)) {
			return 'invalid arguments';
		}

		$vals = array(count($batch_ids), count($pending_ids));
		$maxnodes = max($vals);

		// compose the XML request
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=phstatus&c=booking.com";
		$xmlRq = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PHSTATUS Request e4jConnect.com - Booking.com -->
<PhotosStatusRQ xmlns="http://www.e4jconnect.com/channels/phstatusrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<PhotosStatus hotelid="' . $hid . '">' . "\n";

		if ($maxnodes > 0) {
			// request for some specific upload identifiers
			for ($i = 0; $i < $maxnodes; $i++) { 
				$xmlRq .= '<Queue batchid="' . (isset($batch_ids[$i]) ? $batch_ids[$i] : '') . '" pendingid="' . (isset($pending_ids[$i]) ? $pending_ids[$i] : '') . '" />' . "\n";
			}
		} else {
			$xmlRq .= '<Queue batchid="" pendingid="" startdate="' . (!empty($start_date) ? $start_date : '') .'" />' . "\n";
		}

		$xmlRq .= '</PhotosStatus>
</PhotosStatusRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xmlRq);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			return "CURL Error " . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg());
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			return VikChannelManager::getErrorFromMap($rs);
		}

		// attempt to unserialize the response
		$resp = unserialize($rs);

		if (!is_array($resp)) {
			return 'invalid response obtained';
		}

		// return the plain response from e4jConnect
		return $resp;
	}

	/**
	 * Returns the VCM upload path for the images
	 * 
	 * @return 	string 	the VCM base upload dir path for images
	 */
	private function getUploadPath()
	{
		return VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
	}

	private function removeFile($fname)
	{
		jimport('joomla.filesystem.file');

		if (is_file($this->getUploadPath() . $fname)) {
			return JFile::delete($this->getUploadPath() . $fname);
		}

		return false;
	}

}
