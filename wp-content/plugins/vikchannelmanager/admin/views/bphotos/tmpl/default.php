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

// load assets
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_SITE_URI . 'resources/vikfxgallery.css');
$document->addScript(VBO_SITE_URI . 'resources/vikfxgallery.js');
// we use JHtml to load the jQuery UI Sortable script for compatibility with WP
JHtml::script(VBO_SITE_URI . 'resources/jquery-ui.sortable.min.js');

// Vik Booking Application for media field
$vbo_app = VikChannelManager::getVboApplication();

// VCM Application for popOver
$vcm_app = new VikApplication(VersionListener::getID());

// display precise tab (property by default)
$display_tab = JFactory::getApplication()->input->getString('tab', '');

// oldest queue date
$oldest_queue_date = null;
if (count($this->queue)) {
	$queue_dates = array();
	foreach ($this->queue as $prop => $upqueue) {
		if (count($upqueue)) {
			array_push($queue_dates, strtotime($upqueue[(count($upqueue) - 1)]['date']));
		}
	}
	if (count($queue_dates)) {
		$oldest_queue_date = date('Y-m-d', min($queue_dates));
	}
}

// container for photo_ids in all queues
$queue_photo_ids = array();

// JS language definitions
JText::script('VCMPHOTOADDCONFIRM');
JText::script('VCMPHOTORELOADCONFIRM');

// echo 'Debug<pre>' . print_r($this->photos, true) . '</pre>';
// echo 'Debug<pre>' . print_r($this->photo_tags, true) . '</pre>';
// echo 'Debug<pre>' . print_r($this->otarooms, true) . '</pre>';
// echo 'Debug<pre>' . print_r($this->channel, true) . '</pre>';

if (count($this->photos)) {
	// if the array is empty and has got not keys, it means errors occurred with the API calls, and so we should not display anything
	?>
<div class="vcm-bphotos-wrap">
	<h3><?php echo $this->prop_name . " ({$this->main_param})"; ?></h3>
	<div class="vcm-bphotos-container">
		<div class="vcm-bphotos-tabs">
		<?php
		$j = 0;
		foreach ($this->photos as $prop => $photos) {
			$title = '';
			if ($prop != 'property') {
				if (!isset($this->otarooms[$prop])) {
					continue;
				}
				$title = $this->otarooms[$prop]['otaroomname'] . " ({$this->otarooms[$prop]['idroomota']})";
			}
			?>
			<div class="vcm-bphotos-tab<?php echo ($j < 1 && empty($display_tab)) || $display_tab == $prop ? ' vcm-bphotos-tab-active' : ''; ?>" data-propgallery="<?php echo $prop; ?>" data-totphotos="<?php echo count($photos); ?>">
				<span<?php echo !empty($title) ? ' title="' . ($this->escape($title)) . '"' : ''; ?>><?php echo $prop == 'property' ? JText::_('VCMBPHOTOGALLYPROP') : JText::sprintf('VCMBPHOTOGALLYROOM', $this->otarooms[$prop]['name']); ?></span>
			</div>
			<?php
			$j++;
		}
		?>
		</div>
		<div class="vcm-bphotos-galleries">
		<?php
		$j = 0;
		foreach ($this->photos as $prop => $photos) {
			?>
			<div class="vcm-bphotos-gallery <?php echo $prop == 'property' ? 'vcm-bphotos-gallery-property' : "vcm-bphotos-gallery-{$prop} vcm-bphotos-gallery-room"; ?>" data-propgallery="<?php echo $prop; ?>" style="<?php echo ($j > 0 && empty($display_tab)) || (!empty($display_tab) && $display_tab != $prop) ? 'display: none;' : ''; ?>">
				<div class="vcm-bphotos-gallery-inner">
					<div class="vcm-bphotos-gallery-actions">
						<div class="vcm-bphotos-gallery-actions-inner">
							<div class="vcm-bphotos-gallery-upload">
								<h4><?php echo JText::_('VCMUPLOADPHOTOS'); ?></h4>
							<?php
							if ($vbo_app !== false && method_exists($vbo_app, 'getMediaField')) {
								// media field is only supported by recent VBO versions
								?>
								<div class="vcm-bphotos-gallery-upload-file">
									<div class="vcm-bphotos-gallery-upload-file-inner">
										<label for="photofile-<?php echo $prop; ?>"><?php echo JText::_('VCMFROMMEDIAMANAGER'); ?></label>
										<?php echo $vbo_app->getMediaField('photofile[]', null, array('multiple' => true, 'id' => "photofile-{$prop}")); ?>
									</div>
								</div>
								<?php
							}
							?>
								
								<div class="vcm-bphotos-gallery-upload-drag">
									<label><?php echo JText::_('VCMFROMCOMPUTERDEVICE'); ?></label>
									<div class="vcm-dropfiles-target vcm-dropfiles-<?php echo $prop; ?>">
										<div class="vcm-uploaded-files">
											<?php
											/**
											 * For the moment we are not saving anywhere the currently uploaded files, but if we will
											 * decide to do so one day, this is the way to format every uploaded file just like how the JS
											 * does when uploading images via AJAX. This is the right HTML structure for uploaded files.
											 */
											$prop_files_upload = array();
											foreach ($prop_files_upload as $file) {
												?>
												<div class="file-elem" data-file="<?php echo $file->basename; ?>" data-fileurl="<?php echo $file->url; ?>">
													<div class="file-elem-inner">
														<div class="file-img-box">
															<img src="<?php echo $file->url; ?>" class="vcm-uploadedphoto-img file-link" />
														</div>
														<div class="file-summary">
															<div class="filename"><?php echo $file->name . '.' . $file->ext; ?></div>
															<div class="filesize"><?php echo JHtml::_('number.bytes', $file->size, 'auto', 0); ?></div>
														</div>
														<a href="javascript:void(0);" class="delete-file" style="color: #000;"><?php VikBookingIcons::e('times-circle'); ?></a>
													</div>
												</div>
												<?php
											}
											?>
										</div>
										<p class="icon">
											<i class="<?php echo VikBookingIcons::i('upload'); ?>" style="font-size: 48px;"></i>
										</p>
										<div class="lead">
											<a href="javascript: void(0);" class="upload-file"><?php echo JText::_('VCMMANUALUPLOAD'); ?></a>&nbsp;<?php echo JText::_('VCMDROPFILES'); ?>
										</div>
										<p class="maxsize">
											<?php
											echo JText::sprintf(
												'JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', 
												JHtml::_('number.bytes', ini_get('upload_max_filesize'), 'auto', 0)
											);
											?>
										</p>
										<input type="file" class="legacy-upload" style="display: none;" multiple="multiple">
									</div>
									<div class="drop-files-hint">
										<?php
										echo $vcm_app->createPopover(array(
											'title'     => 'Drop Files',
											'content'   => JText::_('VCMDROPFILESHINT'),
											'placement' => 'left',
										));
										?>
									</div>
								</div>

								<div class="vcm-bphotos-gallery-upload-queue" id="vcm-bphotos-gallery-upload-queue-<?php echo $prop; ?>" style="display: none;">
									<h4><?php echo JText::_('VCMBPHOTOFILESUPQUEUED'); ?>: <span class="vcm-upload-counter" id="vcm-upload-counter-<?php echo $prop; ?>">0</span></h4>
									<span class="vcm-bphotos-gallery-upload-queue-help">
										<?php
										echo $vcm_app->createPopover(array(
											'title'     => JText::_('VCMUPLOADPHOTOS'),
											'content'   => JText::_('VCMBPHOTOUPLOADTOBCOMHELP'),
											'placement' => 'right',
										));
										?>
									</span>
									<button type="button" class="btn vcm-config-btn vcm-upload-photos-to-queue" onclick="uploadPhotosToQueue();"><?php VikBookingIcons::e('cloud-upload-alt'); ?> <?php echo JText::_('VCMBPHOTOUPLOADTOBCOM'); ?></button>
								</div>

								<div class="vcm-bphotos-gallery-upload-debug">
								<?php
								if (VikRequest::getInt('e4j_debug', 0, 'request') && isset($this->queue[$prop])) {
									echo 'Debug<pre>' . print_r($this->queue[$prop], true) . '</pre>';
								}
								?>
								</div>

							</div>
						</div>
					</div>
					<div class="vcm-bphotos-gallery-thumbs">
						<div class="vcm-bphotos-gallery-thumbs-active">
							<h4>
								<span><?php echo JText::_('VCMBPHOTOACTIVEPHOTOS'); ?></span>
								<span class="vcm-bphotos-resort-gallery" style="display: none;">
									<button type="button" class="btn vcm-config-btn" onclick="vcmUpdateGalleryOrder(this);"><?php VikBookingIcons::e('save'); ?> <?php echo JText::_('VCMPHOTOSSAVEORDERBCOM'); ?></button>
								</span>
							</h4>
							<div class="vcm-bphotos-gallery-thumbs-inner">
							<?php
							foreach ($photos as $index => $photo) {
								?>
								<div class="vcm-bphotos-gallery-thumb<?php echo $photo->main ? ' vcm-bphotos-gallery-thumb-ismain' : ''; ?>" data-photoid="<?php echo $photo->id; ?>">
									<div class="vcm-bphotos-gallery-thumb-inner">
										<div class="vcm-bphotos-gallery-thumb-img">
										<?php
										if ($prop == 'property') {
											// main photo only for property gallery, rooms do not have a main photo
											?>
											<div class="vcm-bphotos-gallery-thumb-img-starmain" onclick="vcmSetMainPhoto(this);">
												<?php VikBookingIcons::e('star'); ?>
											</div>
											<?php
										}
										?>
											<img src="<?php echo $photo->url; ?>" class="vcm-bphotos-img" data-caption="<?php echo $this->escape($photo->name); ?>" data-propgallery="<?php echo $prop; ?>" data-index="<?php echo $index; ?>" />
										</div>
										<div class="vcm-bphotos-gallery-thumb-bottom">
											<div class="vcm-bphotos-gallery-thumb-tags">
												<span class="vcm-bphotos-gallery-thumb-tags-toggle"><?php VikBookingIcons::e('tags'); ?> (<span><?php echo count($photo->tags); ?></span>)</span>
												<div class="vcm-bphotos-gallery-thumb-tags-names" style="display: none;">
													<div class="vcm-bphotos-gallery-thumb-tags-names-inner">
													<?php
													foreach ($photo->tags as $idtag) {
														if (isset($this->photo_tags[$idtag])) {
															?>
														<span class="vcm-bphotos-gallery-thumb-tags-name"><?php echo $this->photo_tags[$idtag]; ?></span>
															<?php
														}
													}
													?>
													</div>
												</div>
											</div>
											<div class="vcm-bphotos-gallery-thumb-rmimg">
												<a href="index.php?option=com_vikchannelmanager&task=bphotos.removeImageGallery&prop=<?php echo $prop; ?>&hotelid=<?php echo $this->main_param; ?>&photo_id=<?php echo $photo->id; ?>" class="btn btn-danger" onclick="return confirm('<?php echo addslashes(JText::_('VCMBPHOTOCONFRMIG')); ?>');"><?php VikBookingIcons::e('trash'); ?></a>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							if (!count($photos)) {
								?>
								<p class="warn"><?php echo JText::_('VCMBPHOTONOPFOUNDING'); ?></p>
								<?php
							}
							?>
							</div>
						</div>
						<div class="vcm-bphotos-gallery-thumbs-queued">
							<h4>
								<?php echo JText::_('VCMBPHOTOQUEUEDPHOTOS'); ?>
								<span class="vcm-bphotos-gallery-queued-refreshing" style="display: none;"><?php
								echo $vcm_app->createPopover(array(
									'title'      => JText::_('VCMBPHOTOQUEUEDPHOTOS'),
									'content'    => JText::_('VCMBPHOTOQUEUEDPHOTOSHELP'),
									'placement'  => 'right',
									'icon_class' => VikBookingIcons::i('refresh', 'fa-spin fa-fw'),
								));
								?></span>
							</h4>
							<div class="vcm-bphotos-gallery-thumbs-inner">
							<?php
							if (isset($this->queue[$prop])) {
								$photo_index = -1;
								foreach ($this->queue[$prop] as $qk => $queue) {
									if (!isset($queue['photos']) || !count($queue['photos'])) {
										continue;
									}
									if (!isset($queue_photo_ids[$prop])) {
										$queue_photo_ids[$prop] = array();
									}
									foreach ($queue['photos'] as $qpk => $photo) {
										$photo_index++;
										$caption = $photo['url'];
										if (isset($photo['status']) && isset($photo['status']['status_message'])) {
											$caption = $photo['status']['status_message'];
										}
										$is_photo_eligible = (isset($photo['status']) && isset($photo['status']['status']) && strtolower($photo['status']['status']) == 'ok');
										$photo_id = isset($photo['status']) && isset($photo['status']['photo_id']) ? $photo['status']['photo_id'] : '';
										$photo_pending_id = isset($photo['status']) && isset($photo['status']['photo_pending_id']) ? $photo['status']['photo_pending_id'] : '';
										$photo_status = isset($photo['status']) && isset($photo['status']['status']) ? $photo['status']['status'] : '';
										array_push($queue_photo_ids[$prop], $photo_id);
										// check if this photo was already added to this gallery
										if (isset($this->photos_added[$prop]) && is_array($this->photos_added[$prop]) && in_array($photo_id, $this->photos_added[$prop])) {
											// this photo was already added to the gallery of Booking.com, so we skip it
											continue;
										}
										?>
								<div class="vcm-bphotos-gallery-thumb vcm-bphotos-gallery-thumb-queued" data-eligible="<?php echo (int)$is_photo_eligible; ?>" data-photopendingid="<?php echo $photo_pending_id; ?>" data-photoid="<?php echo $photo_id; ?>">
									<div class="vcm-bphotos-gallery-thumb-inner">
										<div class="vcm-bphotos-gallery-thumb-img">
											<img src="<?php echo $photo['url']; ?>" class="vcm-bphotos-img-queue" data-caption="<?php echo $this->escape($caption); ?>" data-propgallery="<?php echo $prop; ?>" data-index="<?php echo $photo_index; ?>" />
										</div>
										<div class="vcm-bphotos-gallery-thumb-status-time">
											<span class="badge"><?php VikBookingIcons::e('clock'); ?> <?php echo isset($photo['status']) && isset($photo['status']['request_timestamp']) ? $photo['status']['request_timestamp'] : $queue['date']; ?></span>
										</div>
										<div class="vcm-bphotos-gallery-thumb-bottom">
											<div class="vcm-bphotos-gallery-thumb-tags">
												<span class="vcm-bphotos-gallery-thumb-tags-toggle"><?php VikBookingIcons::e('tags'); ?> (<span><?php echo count($photo['tags']); ?></span>)</span>
												<div class="vcm-bphotos-gallery-thumb-tags-names" style="display: none;">
													<div class="vcm-bphotos-gallery-thumb-tags-names-inner">
													<?php
													foreach ($photo['tags'] as $idtag) {
														if (isset($this->photo_tags[$idtag])) {
															?>
														<span class="vcm-bphotos-gallery-thumb-tags-name"><?php echo $this->photo_tags[$idtag]; ?></span>
															<?php
														}
													}
													?>
													</div>
												</div>
											</div>
											<div class="vcm-bphotos-gallery-thumb-status">
												<div class="vcm-bphotos-gallery-thumb-status-approval">
													<span class="badge vcm-photo-status-<?php echo strtolower($photo_status); ?>" title="Photo Pending ID: <?php echo $this->escape($photo_pending_id); ?>"><?php echo !empty($photo_status) ? ucwords($photo_status) : '?'; ?></span>
												</div>
											</div>
										</div>
										<div class="vcm-bphotos-gallery-thumb-commands">
											<div class="vcm-bphotos-gallery-thumb-addimg" style="<?php echo !$is_photo_eligible ? 'display: none;' : ''; ?>">
												<button type="button" class="btn vcm-config-btn" onclick="vcmPublishPhotoToGallery(this);"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::_('VCMPHOTOADDTOGALLERY'); ?></button>
											</div>
											<div class="vcm-bphotos-gallery-thumb-rmimg">
												<a href="index.php?option=com_vikchannelmanager&task=bphotos.removeQueuedImage&prop=<?php echo $prop; ?>&hid=<?php echo $this->main_param; ?>&queue=<?php echo $qk; ?>&photo=<?php echo $qpk; ?>" class="btn btn-danger" onclick="return confirm('<?php echo addslashes(JText::_('VCMBPHOTOCONFRMIG')); ?>');"><?php VikBookingIcons::e('trash'); ?></a>
											</div>
										</div>
									</div>
								</div>
										<?php
									}
								}
							}
							if (!isset($this->queue[$prop]) || !count($this->queue[$prop])) {
								?>
								<p class="info"><?php echo JText::_('VCMBPHOTONOQUEUED'); ?></p>
								<?php
							}
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
			$j++;
		}
		?>
		</div>
	</div>
</div>

<div class="stop-managing-files-hint"><?php echo JText::_('VCMDROPFILESSTOPREMOVING'); ?></div>

<div class="vcm-info-overlay-block vcm-bphoto-tags-manager">
	<div class="vcm-info-overlay-content vcm-bphoto-tags-manager-inner">
		<h3></h3>
		<div class="vcm-bphoto-tags-manager-sel">
			<select id="vcm-bphoto-phototags" multiple="multiple">
				<option></option>
			<?php
			foreach ($this->photo_tags as $tagid => $tagname) {
				?>
				<option value="<?php echo $tagid; ?>"><?php echo $tagname; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<div class="vcm-bphoto-tags-manager-final">
			<button type="button" class="btn btn-danger" onclick="vcmDismissTags();"><?php echo JText::_('VCMOSDIALOGCANCBUTTON'); ?></button>
			<button type="button" class="btn vcm-config-btn" onclick="vcmApplyTags();"><?php echo JText::_('VCMOSDIALOGAPPLYBUTTON'); ?></button>
		</div>
	</div>
</div>

<a id="vcm-reload-href" href="index.php?option=com_vikchannelmanager&task=bphotos" style="display: none;"></a>
	<?php
}
?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikchannelmanager">
</form>

<script type="text/javascript">
var vcmActiveTab = '<?php echo !empty($display_tab) ? $display_tab : 'property'; ?>';
var vcmActivePhoto = null;
var vcmBaseUri = '<?php echo JUri::root(); ?>';
var vcmUpPhotoTags = {};
var vcmFxParams = {
	sourceAttr: 'src',
	captionSelector: 'self',
	captionType: 'data',
	captionsData: 'caption',
	captionClass: 'vcm-photo-caption-active',
};
// we use a different class for the caption
var vcmFxParamsPending = vcmFxParams;
vcmFxParamsPending.captionClass = 'vcm-photo-caption-pending';
//
var CAN_REMOVE_FILES = false;
var vcm_executing_request = false;
var formData = null;
var vcm_status_timer = null;
var vcm_icon_class_add = '<?php echo VikBookingIcons::i('plus-circle'); ?>';
var vcm_icon_class_spin = '<?php echo VikBookingIcons::i('refresh', 'fa-spin fa-fw'); ?>';
jQuery(document).ready(function() {
	// tabs handler
	jQuery('.vcm-bphotos-tab').click(function() {
		var prop_name = jQuery(this).attr('data-propgallery');
		// update global active tab
		vcmActiveTab = prop_name;
		// remove active class from all tabs
		jQuery('.vcm-bphotos-tab').removeClass('vcm-bphotos-tab-active');
		// add active class to this tab
		jQuery(this).addClass('vcm-bphotos-tab-active');
		// hide all containers
		jQuery('.vcm-bphotos-gallery').hide();
		// show container requested
		jQuery('.vcm-bphotos-gallery[data-propgallery="' + prop_name + '"]').fadeIn();
	});

	// toggle photo tags global listener also for added photos
	jQuery(document.body).on("click", ".vcm-bphotos-gallery-thumb-tags-toggle", function() {
		jQuery(this).parent().find('.vcm-bphotos-gallery-thumb-tags-names').toggle();
	});

	// galleries
<?php
foreach ($this->photos as $prop => $photos) {
	if (count($photos)) {
	?>
	window['vcmFxGallery<?php echo $prop; ?>'] = jQuery('.vcm-bphotos-gallery-<?php echo $prop; ?>').find('.vcm-bphotos-img').vikFxGallery(vcmFxParams);
	<?php
	}
	if (isset($queue_photo_ids[$prop]) && count($queue_photo_ids[$prop])) {
		// make sure the photos in this queue were not all added
		if (isset($this->photos_added[$prop]) && count($this->photos_added[$prop]) && !count(array_diff($queue_photo_ids[$prop], $this->photos_added[$prop]))) {
			// all photos in this queue have been added, so no gallery is needed
			continue;
		}
		// create the gallery also for the queue of this tab
	?>
	window['vcmFxGalleryQueue<?php echo $prop; ?>'] = jQuery('.vcm-bphotos-gallery-<?php echo $prop; ?>').find('.vcm-bphotos-img-queue').vikFxGallery(vcmFxParamsPending);
	<?php
	}
}
?>
	// thumbs trigger gallery opening
	jQuery('.vcm-bphotos-img, .vcm-bphotos-img-queue').click(function() {
		var prop_name = jQuery(this).attr('data-propgallery');
		var thumb_ind = jQuery(this).attr('data-index');
		if (!prop_name || !thumb_ind) {
			return;
		}
		if (!jQuery(this).hasClass('vcm-bphotos-img-queue') && typeof window['vcmFxGallery' + prop_name] !== "undefined" && typeof window['vcmFxGallery' + prop_name][thumb_ind] !== "undefined") {
			window['vcmFxGallery' + prop_name].open(jQuery(window['vcmFxGallery' + prop_name][thumb_ind]));
		} else if (jQuery(this).hasClass('vcm-bphotos-img-queue') && typeof window['vcmFxGalleryQueue' + prop_name] !== "undefined" && typeof window['vcmFxGalleryQueue' + prop_name][thumb_ind] !== "undefined") {
			window['vcmFxGalleryQueue' + prop_name].open(jQuery(window['vcmFxGalleryQueue' + prop_name][thumb_ind]));
		}
	});

	// opening effect over any uploaded photos
	jQuery(document.body).on("click", ".vcm-uploadedphoto-img", function() {
		if (CAN_REMOVE_FILES) {
			return false;
		}
		jQuery(this).vikFxGallery(vcmFxParams).open();
	});

	// toggle tags for any uploaded image
	jQuery(document.body).on("click", ".vcm-bphotos-uptags-toggle", function() {
		vcmManagePhotoTags(jQuery(this));
	});

	// select2 for tags
	jQuery('#vcm-bphoto-phototags').select2({
		placeholder: '<?php echo addslashes(JText::_('VCMBCAIMAGETAGS')); ?>',
		allowClear: false,
		width: 250
	});

	// sortable images
	jQuery('.vcm-bphotos-gallery-thumbs-active').each(function() {
		jQuery(this).find('.vcm-bphotos-gallery-thumbs-inner').sortable({
			items: '.vcm-bphotos-gallery-thumb',
			helper: 'clone',
			update: function(event, ui) {
				vcmDisplayGallerySaveOrdering();
			},
		});
		jQuery(this).find('.vcm-bphotos-gallery-thumbs-inner').disableSelection();
	});

	// media manager file selection (Joomla single file selection)
	jQuery('.vcm-bphotos-gallery-upload-file-inner input[type="text"]').change(function() {
		var media_rel_uri = jQuery(this).val();
		if (!media_rel_uri.length) {
			return;
		}
		// a file has been selected from the media manager, add it to the draggable area for upload
		var media_uri_parts = media_rel_uri.split('/');
		var media_fname = media_uri_parts[(media_uri_parts.length - 1)];
		var media_fname_parts = media_fname.split('.');
		// media name has got no extension, filename does
		var media_name = '';
		for (var i = 0; i < (media_fname_parts.length - 1); i++) {
			media_name += media_fname_parts[i];
		}
		// media file object
		var mediafile = {
			url: vcmBaseUri + media_rel_uri,
			name: media_name,
			ext: '',
			size: '',
			filename: media_fname,
		}
		var mediaimg = new UploadingFile();
		mediaimg.setFileNameSize(media_fname, null);
		mediaimg.complete(mediafile);
		jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.vcm-uploaded-files').prepend(mediaimg.getHtml());
		// make file removable and count uploaded images
		makeFileRemovable();
		countUploadedImages();
		// make the input field empty
		jQuery(this).val('');
	});

	// media manager multiple files selection (WordPress)
	jQuery(window).on('media.change', function() {
		var media_files_rel_uri = jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-gallery-upload-file-inner').find('input[name="photofile[]"]');
		if (!media_files_rel_uri.length) {
			return;
		}
		media_files_rel_uri.each(function() {
			var media_rel_uri = jQuery(this).val();
			if (!media_rel_uri.length) {
				return;
			}
			// a file has been selected from the media manager, add it to the draggable area for upload
			var media_uri_parts = media_rel_uri.split('/');
			var media_fname = media_uri_parts[(media_uri_parts.length - 1)];
			var media_fname_parts = media_fname.split('.');
			// media name has got no extension, filename does
			var media_name = '';
			for (var i = 0; i < (media_fname_parts.length - 1); i++) {
				media_name += media_fname_parts[i];
			}
			// media file object
			var mediafile = {
				url: vcmBaseUri + media_rel_uri,
				name: media_name,
				ext: '',
				size: '',
				filename: media_fname,
			}
			var mediaimg = new UploadingFile();
			mediaimg.setFileNameSize(media_fname, null);
			mediaimg.complete(mediafile);
			jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.vcm-uploaded-files').prepend(mediaimg.getHtml());
		});

		// make files removable and count uploaded images
		makeFileRemovable();
		countUploadedImages();
		
		// the WP media manager will display the selected images, but we don't need them
		var clearbtn = jQuery('#photofile-' + vcmActiveTab + '-clear-button');
		if (clearbtn && clearbtn.length) {
			clearbtn.trigger('click');
		}
	});

	// drag & drop upload
	var dragCounter = 0;
	// drag & drop actions on target div
	jQuery('.vcm-dropfiles-target').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
		e.preventDefault();
		e.stopPropagation();
	});
	jQuery('.vcm-dropfiles-target').on('dragenter', function(e) {
		// increase the drag counter because we may
		// enter into a child element
		dragCounter++;

		jQuery(this).addClass('drag-enter');
	});
	jQuery('.vcm-dropfiles-target').on('dragleave', function(e) {
		// decrease the drag counter to check if we 
		// left the main container
		dragCounter--;
		if (dragCounter <= 0) {
			jQuery(this).removeClass('drag-enter');
		}
	});
	jQuery('.vcm-dropfiles-target').on('drop', function(e) {
		jQuery(this).removeClass('drag-enter');
		var files = e.originalEvent.dataTransfer.files;
		execUploads(files);
	});
	jQuery('.vcm-dropfiles-target .upload-file').on('click', function() {
		jQuery(this).closest('.vcm-dropfiles-target').find('input.legacy-upload').trigger('click');
	});
	jQuery('input.legacy-upload').on('change', function() {
		execUploads(jQuery(this)[0].files);
	});
	// make all current files removable by clicking and pressing on them
	jQuery(window).keydown(function(event) {
		if (event.keyCode == 27) {
			CAN_REMOVE_FILES = false;
			jQuery('.vcm-uploaded-files .file-elem').removeClass('do-shake');
			jQuery('.stop-managing-files-hint').hide();
			vcmDismissTags();
		}
	});
	jQuery('.vcm-uploaded-files .file-elem a.delete-file').on('click', fileRemoveThread);

	// update status for pending photos
	monitorQueueStatus();

	// set interval of 3 minutes and 5 seconds for the refresh of the status of the pending photos
	vcm_status_timer = setInterval(monitorQueueStatus, 185000);
});

// drag & drop upload functions
function execUploads(files) {
	if (CAN_REMOVE_FILES) {
		return false;
	}
	for (var i = 0; i < files.length; i++) {
		if (isFileSupported(files[i].name)) {
			var status = new UploadingFile();
			status.setFileNameSize(files[i].name, files[i].size);
			status.setProgress(0);
			jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.vcm-uploaded-files').prepend(status.getHtml());
			fileUploadThread(status, files[i]);
		} else {
			alert('File ' + files[i].name + ' not supported');
		}
	}
}

function UploadingFile() {
	// create parent
	this.fileBlock = jQuery('<div class="file-elem uploading"></div>');
	// create inner
	this.fileInnerBlock = jQuery('<div class="file-elem-inner"></div>').appendTo(this.fileBlock);
	// create temporary file container and append it to parent block
	this.fileTmpCont = jQuery('<a href="javascript:void(0);" target="_blank"><?php VikBookingIcons::e('file'); ?></a>').appendTo(this.fileInnerBlock);
	// create final image container
	this.fileCont = jQuery('<div class="file-img-box"></div>');
	// create final image container to be used after the upload completes with sucess
	this.filePreview = jQuery('<img src="" class="vcm-uploadedphoto-img file-link" />').appendTo(this.fileCont);
	// create file extension
	this.fileExt = jQuery('<span class="file-extension"></span>').appendTo(this.fileTmpCont);
	// create file summary
	this.fileSummary = jQuery('<div class="file-summary"></div>').appendTo(this.fileInnerBlock);
	// create file name
	this.fileName = jQuery('<div class="filename"></div>').appendTo(this.fileSummary);
	// create file size
	this.fileSize = jQuery('<div class="filesize"></div>').appendTo(this.fileSummary);
	// create file tags
	this.fileTags = jQuery('<div class="file-tags" style="display: none;"><span class="vcm-bphotos-uptags-toggle"><?php VikBookingIcons::e('tags'); ?> (<span class="vcm-bphotos-uptags-counter">0</span>)</span></div>').appendTo(this.fileInnerBlock);
	// create remove link
	this.removeLink = jQuery('<a href="javascript:void(0);" class="delete-file" style="color: #000;"><?php VikBookingIcons::e('times'); ?></a>').appendTo(this.fileInnerBlock);
	this.removeLink.on('click', fileRemoveThread);
	// methods
	this.setFileNameSize = function(name, size) {
		// fetch name
		if (name) {
			var match = name.match(/(.*?)\.([a-z0-9]{2,})$/i);
			if (match && match.length) {
				this.fileName.html(match[1]);
				this.fileExt.html(match[2]);
			} else {
				this.fileName.html(name);
			}
		}
		// fetch size
		if (size) {
			var sizeStr = "";
			if (size > 1024*1024) {
				var sizeMB = size/(1024*1024);
				sizeStr = sizeMB.toFixed(2)+" MB";
			} else if (size > 1024) {
				var sizeKB = size/1024;
				sizeStr = sizeKB.toFixed(2)+" kB";
			} else {
				sizeStr = size.toFixed(2)+" B";
			}
			this.fileSize.html(sizeStr);
		}
	}
	this.setProgress = function(progress) {       
		var opacity = parseFloat(progress / 100);
		this.fileBlock.css('opacity', opacity);
	}
	this.complete = function(file) {
		this.setProgress(100);
		this.filePreview.attr('src', file.url);
		this.fileTmpCont.replaceWith(this.fileCont);
		this.fileName.html(file.name);
		this.fileExt.html(file.ext);
		this.fileSize.html(file.size);
		this.fileBlock.removeClass('uploading');
		this.fileBlock.attr('data-file', file.filename).attr('data-fileurl', file.url);
		this.fileTags.show();
		// update global tags container
		vcmUpPhotoTags[file.filename] = new Array;
	}
	this.getHtml = function() {
		return this.fileBlock;
	}
}

function fileUploadThread(status, file) {
	formData = new FormData();
	formData.append('file', file);
	formData.append('prop', vcmActiveTab);
	formData.append('hotelid', '<?php echo $this->main_param; ?>');

	var jqxhr = jQuery.ajax({
		xhr: function() {
			var xhrobj = jQuery.ajaxSettings.xhr();
			if (xhrobj.upload) {
				xhrobj.upload.addEventListener('progress', function(event) {
					var percent = 0;
					var position = event.loaded || event.position;
					var total = event.total;
					if (event.lengthComputable) {
						percent = Math.ceil(position / total * 100);
					}
					// update progress
					status.setProgress(percent);
				}, false);
			}
			return xhrobj;
		},
		url: 'index.php?option=com_vikchannelmanager&task=bphotos.uploadLocalPhotos',
		type: 'POST',
		contentType: false,
		processData: false,
		cache: false,
		data: formData,
		success: function(resp) {
			try {
				resp = JSON.parse(resp);
				if (resp.status == 1) {
					status.complete(resp);
					makeFileRemovable();
					countUploadedImages();
				} else {
					throw resp.error ? resp.error : 'An error occurred! Please try again.';
				}
			} catch (err) {
				console.warn(err, resp);
				alert(err);
				status.fileBlock.remove();
			}
		},
		error: function(err) {
			console.error(err.responseText);
			status.fileBlock.remove();
			alert('An error occurred! Please try again.');
		}, 
	});
}

function isFileSupported(name) {
	return name.match(/\.(jpe?g|png|bmp|heic|webp|gif)$/i);
}

function makeFileRemovable(selector) {
	if (!selector) {
		selector = '.vcm-dropfiles-' + vcmActiveTab + ' .vcm-uploaded-files .file-elem img';
	}
	jQuery(selector).each(function() {
		var timeout = null;
		jQuery(this).on('mousedown', function(event) {
			timeout = setTimeout(function() {
				CAN_REMOVE_FILES = true;
				jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.vcm-uploaded-files .file-elem').addClass('do-shake');
				jQuery('.stop-managing-files-hint').show();
			}, 1000);
		}).on('mouseup mouseleave', function(event) {
			clearTimeout(timeout);
		}).on('click', function(event) {
			if (CAN_REMOVE_FILES) {
				event.preventDefault();
				event.stopPropagation();
				return false;
			}
		});
	});
}

function fileRemoveThread() {
	var elem = jQuery(this).closest('.file-elem');
	var file = jQuery(elem).attr('data-file');
	if (!file.length) {
		return false;
	}
	elem.addClass('removing');
	jQuery.ajax({
		url: 'index.php?option=com_vikchannelmanager&task=bphotos.deleteLocalPhotos',
		type: 'post',
		data: {
			file: file,
		},
	}).done(function(resp) {
		elem.remove();
		if (jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.vcm-uploaded-files .file-elem').length == 0) {
			var esc = jQuery.Event('keydown', { keyCode: 27 });
			jQuery(window).trigger(esc);
		}
		countUploadedImages();
	}).fail(function(err) {
		console.error(err.responseText);
		elem.removeClass('removing');
		alert('An error occurred! Please try again.');
	});
}

function countUploadedImages() {
	var tot_photos = jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.file-elem').length;
	if (!tot_photos || tot_photos < 1) {
		jQuery('#vcm-bphotos-gallery-upload-queue-' + vcmActiveTab).hide();
		return;
	}
	jQuery('#vcm-upload-counter-' + vcmActiveTab).text(tot_photos);
	jQuery('#vcm-bphotos-gallery-upload-queue-' + vcmActiveTab).fadeIn();
}

function uploadPhotosToQueue() {
	var tot_photos = jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.file-elem').length;
	if (!tot_photos || tot_photos < 1) {
		alert('No pending photos uploaded');
		return false;
	}
	// disable all upload buttons
	vcmDisableUpload();
	// build urls and tags
	var photo_urls = new Array;
	var photo_tags = new Array;
	var index = 0;
	jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.file-elem').each(function() {
		var filename = jQuery(this).attr('data-file');
		var photo_url = jQuery(this).attr('data-fileurl');
		if (!photo_url || !photo_url.length) {
			// continue
			return;
		}
		photo_urls.push(photo_url);
		photo_tags[index] = [];
		if (vcmUpPhotoTags.hasOwnProperty(filename)) {
			photo_tags[index] = vcmUpPhotoTags[filename];
		}
		index++;
	});

	// make the request
	jQuery.ajax({
		url: 'index.php?option=com_vikchannelmanager&task=bphotos.uploadPhotosToQueue',
		type: 'post',
		data: {
			photos: photo_urls,
			tags: photo_tags,
			prop: vcmActiveTab,
			hotelid: '<?php echo $this->main_param; ?>',
		},
	}).done(function(resp) {
		try {
			resp = JSON.parse(resp);
			// empty the draggable area for uploaded images
			jQuery('.vcm-dropfiles-' + vcmActiveTab).find('.file-elem').remove();
			// update box to trigger the upload
			countUploadedImages();
			// reload the page to let PHP parse the updated upload queue and avoid re-formatting it via JS
			document.location.href = jQuery('#vcm-reload-href').attr('href') + '&tab=' + vcmActiveTab;
		} catch (err) {
			console.warn(err, resp);
			alert('Error, please read the error from the browser console.');
		}
		// restore all upload buttons
		vcmEnableUpload();
	}).fail(function(err) {
		console.error(err, err.responseText);
		alert('An error occurred! Please try again or read the error from the browser console.');
		// restore all upload buttons
		vcmEnableUpload();
	});
}

function vcmDisableUpload() {
	jQuery('.vcm-upload-photos-to-queue').prop('disabled', true).addClass('vcm-btn-disabled');
}

function vcmEnableUpload() {
	jQuery('.vcm-upload-photos-to-queue').prop('disabled', false).removeClass('vcm-btn-disabled');
}

function vcmManagePhotoTags(tagelem) {
	vcmActivePhoto = tagelem;
	var filename = tagelem.closest('.file-elem').attr('data-file');
	if (!vcmUpPhotoTags.hasOwnProperty(filename)) {
		console.error('uploaded photo has got not tags array');
		return false;
	}
	var multisel = jQuery('#vcm-bphoto-phototags');
	// reset all selected tags
	multisel.find('option').prop('selected', false);
	// select only the active tags for this photo
	for (var i in vcmUpPhotoTags[filename]) {
		if (!vcmUpPhotoTags[filename].hasOwnProperty(i)) {
			continue;
		}
		multisel.find('option[value="' + vcmUpPhotoTags[filename][i] + '"]').prop('selected', true);
	}
	multisel.trigger('change');
	// set filename
	jQuery('.vcm-bphoto-tags-manager').find('h3').text(filename);
	// display modal
	jQuery('.vcm-bphoto-tags-manager').fadeIn();
}

function vcmDismissTags() {
	// dismiss modal
	jQuery('.vcm-bphoto-tags-manager').fadeOut();
}

function vcmApplyTags() {
	var filename = vcmActivePhoto.closest('.file-elem').attr('data-file');
	if (!vcmUpPhotoTags.hasOwnProperty(filename)) {
		console.error('uploaded photo has got not tags array for update');
		return false;
	}
	// update array of tags
	vcmUpPhotoTags[filename] = jQuery('#vcm-bphoto-phototags').val();
	// update the tags counter
	vcmActivePhoto.closest('.file-elem').find('.file-tags').find('.vcm-bphotos-uptags-counter').text(vcmUpPhotoTags[filename].length);
	// dismiss modal
	vcmDismissTags();
}

/**
 * This function will try to update the status of all the transmitted photos to Booking.com. In case some
 * photos have been accepted, the status will be changed so that they can be added to the gallery.
 * For updating the status of a batch ID or a photo pending ID we need the photo pending id, not the photo id.
 */
function monitorQueueStatus() {
	// find if there are some not-eligible photos waiting to be approved
	var noneligible = 0;
	jQuery('.vcm-bphotos-gallery').each(function() {
		noneligible = jQuery(this).find('.vcm-bphotos-gallery-thumbs-queued').find('.vcm-bphotos-gallery-thumb-queued[data-eligible="0"]').length;
		if (noneligible > 0) {
			return false;
		}
	});
	if (noneligible < 1) {
		// no photos require an update of their status
		console.log('no photos require an update of the status');
		jQuery('.vcm-bphotos-gallery-queued-refreshing').hide();
		return false;
	}
	// gather all the pending photo ids and count enqueued photos per gallery (just uploaded)
	var pending_photo_ids = new Array;
	var props_enqueued = {};
	var tot_enqueued = 0;
	jQuery('.vcm-bphotos-gallery').each(function() {
		var nowprop = jQuery(this).attr('data-propgallery');
		var queued = jQuery(this).find('.vcm-bphotos-gallery-thumbs-queued').find('.vcm-bphotos-gallery-thumb-queued[data-eligible="0"]');
		if (!queued.length) {
			return;
		}
		queued.each(function() {
			var pendid = jQuery(this).attr('data-photopendingid');
			if (pendid.length) {
				pending_photo_ids.push(pendid);
			}
			if (jQuery(this).find('.vcm-bphotos-gallery-thumb-status-approval span').hasClass('vcm-photo-status-enqueued')) {
				tot_enqueued++;
				props_enqueued[nowprop] = 1;
			}
		});
	});
	if (!pending_photo_ids.length) {
		console.log('all photos are eligible');
		jQuery('.vcm-bphotos-gallery-queued-refreshing').hide();
		return false;
	}
	// display the popover indicating that the status of some photos is being monitored
	if (tot_enqueued > 0) {
		for (var prop in props_enqueued) {
			if (!props_enqueued.hasOwnProperty(prop)) {
				continue;
			}
			jQuery('.vcm-bphotos-gallery-' + prop).find('.vcm-bphotos-gallery-queued-refreshing').show();
		}
	}
	// make the request
	jQuery.ajax({
		url: 'index.php?option=com_vikchannelmanager&task=bphotos.retrievePhotosStatus',
		type: 'post',
		data: {
			pending_ids: pending_photo_ids,
			hotelid: '<?php echo $this->main_param; ?>',
			oldest_date: '<?php echo !is_null($oldest_queue_date) ? $oldest_queue_date : ''; ?>',
		},
	}).done(function(resp) {
		try {
			resp = JSON.parse(resp);
			var needs_reload = false;
			for (var i in resp) {
				if (!resp.hasOwnProperty(i)) {
					continue;
				}
				if (!resp[i].hasOwnProperty('photo_pending_id') || !resp[i].hasOwnProperty('status')) {
					// missing information needed
					continue;
				}
				var photo_pending_id = resp[i]['photo_pending_id'];
				var status = resp[i]['status'];
				if (pending_photo_ids.indexOf(photo_pending_id) < 0) {
					// photo not in array
					continue;
				}
				// we have got this pending photo, update the status
				var status_mess = resp[i].hasOwnProperty('status_message') ? resp[i]['status_message'] : '';
				var elem = jQuery('.vcm-bphotos-gallery-thumb-queued[data-photopendingid="' + photo_pending_id + '"]');
				if (!elem.length) {
					console.log('photo not found in DOM although in array', photo_pending_id);
					continue;
				}
				// update status, status message, photo_id and eligible (if now ok)
				elem.find('.vcm-bphotos-gallery-thumb-status-approval').find('span').text(status).attr('class', 'badge vcm-photo-status-' + status);
				elem.find('img.vcm-bphotos-img-queue').attr('data-caption', status_mess);
				if (resp[i].hasOwnProperty('photo_id')) {
					// update the photo_id, which will be needed to add the photo to the gallery
					elem.attr('data-photoid', resp[i]['photo_id']);
				}
				if (status.toLowerCase().indexOf('ok') >= 0) {
					// this photo has been approved
					needs_reload = true;
					elem.attr('data-eligible', '1');
					elem.find('.vcm-bphotos-gallery-thumb-addimg').fadeIn();
				} else {
					elem.find('.vcm-bphotos-gallery-thumb-addimg').hide();
				}
			}
			if (needs_reload) {
				// hide loading icon(s)
				jQuery('.vcm-bphotos-gallery-queued-refreshing').hide();
				// print confirm message to reload the page as some photos have been accepted and added to the gallery after the upload
				if (confirm(Joomla.JText._('VCMPHOTORELOADCONFIRM'))) {
					// we force the reload of the gallery meta tags and all photos
					document.location.href = jQuery('#vcm-reload-href').attr('href') + '&refresh=1&tab=' + vcmActiveTab;
					return;
				}
			}
		} catch (err) {
			console.error(err, resp);
		}
	}).fail(function(err) {
		console.error(err, err.responseText);
	});
}

function vcmPublishPhotoToGallery(elem) {
	elem = jQuery(elem);
	var parent_elem = elem.closest('.vcm-bphotos-gallery-thumb-queued[data-eligible="1"]');
	var photo_id = parent_elem.attr('data-photoid');
	if (!photo_id || !photo_id.length) {
		alert('Photo ID not found or not yet approved');
		return false;
	}
	var photo_url = parent_elem.find('.vcm-bphotos-img-queue').attr('src');
	var photo_tags_count = parent_elem.find('.vcm-bphotos-gallery-thumb-tags-toggle span').text();
	var photo_tags_html = parent_elem.find('.vcm-bphotos-gallery-thumb-tags-names-inner').html();
	if (confirm(Joomla.JText._('VCMPHOTOADDCONFIRM'))) {
		var photo_ids = new Array;
		photo_ids.push(photo_id);
		// disable the button
		elem.prop('disabled', true);
		// show loading icon
		elem.find('i').attr('class', vcm_icon_class_spin);
		// make the request
		jQuery.ajax({
			url: 'index.php?option=com_vikchannelmanager&task=bphotos.publishPhotosToGallery',
			type: 'post',
			data: {
				hotelid: '<?php echo $this->main_param; ?>',
				prop: vcmActiveTab,
				photo_ids: photo_ids,
			},
		}).done(function(resp) {
			// if request is successful, the response will be just a useless string
			if (!jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-gallery-thumbs-active').find('.vcm-bphotos-gallery-thumb').length) {
				// remove the element from the DOM first, just as an animation
				elem.closest('.vcm-bphotos-gallery-thumb-queued[data-eligible="1"]').remove();
				// no published photos yet, let the page reload to display it in the gallery
				document.location.href = jQuery('#vcm-reload-href').attr('href') + '&tab=' + vcmActiveTab;
				return;
			}
			// put this image in the live gallery by cloning an existing one
			var tot_live_gphotos = jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-gallery-thumbs-active').find('.vcm-bphotos-gallery-thumb').length;
			var wrapper = jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-gallery-thumbs-active').find('.vcm-bphotos-gallery-thumb').first().clone();
			var cloned_photo_id = jQuery(wrapper).attr('data-photoid');
			// update the photoid attribute, remove the main class (if any)
			jQuery(wrapper).attr('data-photoid', photo_id).removeClass('vcm-bphotos-gallery-thumb-ismain');
			// modify the image attributes
			jQuery(wrapper).find('.vcm-bphotos-img').attr('src', photo_url).attr('data-caption', '').attr('data-index', (tot_live_gphotos + 1));
			// update photo tags
			jQuery(wrapper).find('.vcm-bphotos-gallery-thumb-tags-toggle span').text(photo_tags_count);
			jQuery(wrapper).find('.vcm-bphotos-gallery-thumb-tags-names-inner').html(photo_tags_html);
			// replace photo id in remove link
			var cloned_rm_link = jQuery(wrapper).find('.vcm-bphotos-gallery-thumb-rmimg a').attr('href');
			jQuery(wrapper).find('.vcm-bphotos-gallery-thumb-rmimg a').attr('href', cloned_rm_link.replace(cloned_photo_id, photo_id));
			// append the cloned element to the live gallery
			jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-gallery-thumbs-active').find('.vcm-bphotos-gallery-thumbs-inner').append(wrapper);
			// remove the element from the DOM
			elem.closest('.vcm-bphotos-gallery-thumb-queued[data-eligible="1"]').remove();
			// re-create the galleries with lightbox effect
			if (typeof window['vcmFxGallery' + vcmActiveTab] !== "undefined") {
				// destroy the previous gallery first
				window['vcmFxGallery' + vcmActiveTab].destroy();
				setTimeout(function() {
					// gallery restoration must be delayed in order to reset all the DOM events for the keyboard navigation
					window['vcmFxGallery' + vcmActiveTab] = jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-img').vikFxGallery(vcmFxParams);
				}, 500);
			}
			if (typeof window['vcmFxGalleryQueue' + vcmActiveTab] !== "undefined") {
				// destroy the previous gallery first
				window['vcmFxGalleryQueue' + vcmActiveTab].destroy();
				if (jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-img-queue').length) {
					setTimeout(function() {
						// gallery restoration must be delayed in order to reset all the DOM events for the keyboard navigation
						window['vcmFxGalleryQueue' + vcmActiveTab] = jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-img-queue').vikFxGallery(vcmFxParamsPending);
					}, 500);
				}
			}
		}).fail(function(err) {
			console.error(err.responseText);
			alert('An error occurred, see the full error from the browser console');
			elem.prop('disabled', false);
			// restore original icon
			elem.find('i').attr('class', vcm_icon_class_add);
		});
	} else {
		return false;
	}
}

function vcmDisplayGallerySaveOrdering() {
	jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-resort-gallery').fadeIn();
}

function vcmUpdateGalleryOrder(elem) {
	elem = jQuery(elem);
	// gather the correct list of photo ids in the new order
	var photo_ids = new Array;
	jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-gallery-thumbs-active').find('.vcm-bphotos-gallery-thumb').each(function() {
		photo_ids.push(jQuery(this).attr('data-photoid'));
	});
	if (!photo_ids.length) {
		alert('No active photos found');
		return false;
	}
	var btn_icon_elem = jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-resort-gallery').find('i');
	var orig_btn_class = btn_icon_elem.attr('class');
	// disable the button
	elem.prop('disabled', true);
	// show loading icon
	btn_icon_elem.attr('class', vcm_icon_class_spin);
	// make the request
	jQuery.ajax({
		url: 'index.php?option=com_vikchannelmanager&task=bphotos.sortPhotoGallery',
		type: 'post',
		data: {
			hotelid: '<?php echo $this->main_param; ?>',
			prop: vcmActiveTab,
			photo_ids: photo_ids,
		},
	}).done(function(resp) {
		// if request is successful, the response will be just a useless string
		// restore original values
		jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-resort-gallery').fadeOut();
		elem.prop('disabled', false);
		btn_icon_elem.attr('class', orig_btn_class);
		//
		// let the page reload to display it in the gallery (not necessary)
		// document.location.href = jQuery('#vcm-reload-href').attr('href') + '&tab=' + vcmActiveTab;
	}).fail(function(err) {
		console.error(err.responseText);
		alert('An error occurred, see the full error from the browser console');
		elem.prop('disabled', false);
		// restore original icon
		btn_icon_elem.attr('class', orig_btn_class);
	});
}

function vcmSetMainPhoto(elem) {
	elem = jQuery(elem);
	if (vcmActiveTab != 'property') {
		alert('Main photo can only be set for the property main gallery');
		return false;
	}
	if (vcm_executing_request === true) {
		console.error('One execution is still going on, please wait for it to be completed');
		return false;
	}
	if (elem.closest('.vcm-bphotos-gallery-thumb').hasClass('vcm-bphotos-gallery-thumb-ismain')) {
		alert('This is already the main photo in the gallery');
		return false;
	}
	var photo_id = elem.closest('.vcm-bphotos-gallery-thumb').attr('data-photoid');
	if (!photo_id || !photo_id.length) {
		alert('Photo ID not found');
		return false;
	}
	// start flags
	vcm_executing_request = true;
	var orig_btn_class = elem.find('i').attr('class');
	// show loading icon
	elem.find('i').attr('class', vcm_icon_class_spin);
	// make the request
	jQuery.ajax({
		url: 'index.php?option=com_vikchannelmanager&task=bphotos.setMainPhotoGallery',
		type: 'post',
		data: {
			hotelid: '<?php echo $this->main_param; ?>',
			photo_id: photo_id,
		},
	}).done(function(resp) {
		// if request is successful, the response will be just a useless string
		// restore original values
		elem.find('i').attr('class', orig_btn_class);
		vcm_executing_request = false;
		// move main-photo class
		jQuery('.vcm-bphotos-gallery-' + vcmActiveTab).find('.vcm-bphotos-gallery-thumb-ismain').removeClass('vcm-bphotos-gallery-thumb-ismain');
		elem.closest('.vcm-bphotos-gallery-thumb').addClass('vcm-bphotos-gallery-thumb-ismain');
	}).fail(function(err) {
		console.error(err.responseText);
		alert('An error occurred, see the full error from the browser console');
		// restore original icon
		elem.find('i').attr('class', orig_btn_class);
		vcm_executing_request = false;
	});
}
</script>
