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

// override channel name, if necessary
$channel_name = $this->channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : $this->channel['name'];

JText::script('VCM_AI_GEN_THROUGH');

?>
<div class="vcm-admin-container vcm-admin-container-full">
	<div class="vcm-config-maintab-left">
		<fieldset class="adminform">
			<div class="vcm-params-wrap">
				<legend class="adminlegend"><?php echo JText::_('VCM_REVIEW_GUEST_TITLE'); ?></legend>
				<div class="vcm-params-container vcm-htgreview-fields">

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCMRESLOGSIDORDOTA'); ?></div>
						<div class="vcm-param-setting">
							<img src="<?php echo VikChannelManager::getLogosInstance($this->channel['name'])->getLogoURL(); ?>" style="max-height: 40px; margin-bottom: 10px;" />
							<div>
								<span class="label label-info"><?php echo $this->reservation['idorderota']; ?></span>
							</div>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCM_HTGREVIEW_CLEAN'); ?> <sup>*</sup></div>
						<div class="vcm-param-setting">
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_CLEAN_HELP'); ?></span>
							<div class="vcm-writereview-stars-wrap" data-category="cleanliness">
							<?php
							for ($i = 1; $i <= 5; $i++) { 
								?>
								<span class="vcm-writereview-star-cont" data-star-cat="clean" data-star-rating="<?php echo $i; ?>" onclick="vcmSetStarRating(this);"><?php VikBookingIcons::e('star', 'vcm-ota-review-star'); ?></span>
								<?php
							}
							?>
								<input type="hidden" name="review_cat_clean" id="review-cat-clean" value="" />
							</div>
							<div class="vcm-writereview-stars-comment-wrap" id="vcm-writereview-stars-comment-clean" style="display: none;">
								<input type="text" name="review_cat_clean_comment" value="" autocomplete="off" />
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_COMMENT'); ?></span>
							</div>
						</div>
					</div>

				<?php
				// get the review category tags ("cleanliness") for host-to-guest review
				$rev_cat_tags_clean = VCMAirbnbReview::getCategoryTags('host_review_guest', 'cleanliness');
				if ($rev_cat_tags_clean) {
					?>
					<div class="vcm-param-container">
						<div class="vcm-param-label">&nbsp;</div>
						<div class="vcm-param-setting vcm-htgreview-category-tags" data-category="cleanliness">
						<?php
						foreach ($rev_cat_tags_clean as $tag => $tag_data) {
							// check if tag is positive or negative, if applicable
							$is_tag_positive = (int) (stripos($tag, 'positive') !== false);
							$is_tag_negative = (int) (stripos($tag, 'negative') !== false);
							$tag_icon_cls = null;
							if ($is_tag_positive) {
								$tag_icon_cls = VikBookingIcons::i('thumbs-up');
							} elseif ($is_tag_negative) {
								$tag_icon_cls = VikBookingIcons::i('thumbs-down');
							}
							?>
							<button type="button" class="btn btn-small vcm-htgreview-category-tag-btn" data-tag="<?php echo $tag; ?>" data-positive="<?php echo $is_tag_positive; ?>" data-negative="<?php echo $is_tag_negative; ?>"><?php echo ($tag_icon_cls ? '<i class="' . $tag_icon_cls . '"></i> ' : '') . $tag_data['descr']; ?></button>
							<?php
						}
						?>
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_CAT_TAGS_HELP'); ?></span>
							<input type="hidden" class="vcm-htgreview-category-tags-inp" data-category="cleanliness" value="" />
						</div>
					</div>
					<?php
				}
				?>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCM_HTGREVIEW_COMM'); ?> <sup>*</sup></div>
						<div class="vcm-param-setting">
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_COMM_HELP'); ?></span>
							<div class="vcm-writereview-stars-wrap" data-category="communication">
							<?php
							for ($i = 1; $i <= 5; $i++) { 
								?>
								<span class="vcm-writereview-star-cont" data-star-cat="comm" data-star-rating="<?php echo $i; ?>" onclick="vcmSetStarRating(this);"><?php VikBookingIcons::e('star', 'vcm-ota-review-star'); ?></span>
								<?php
							}
							?>
								<input type="hidden" name="review_cat_comm" id="review-cat-comm" data-rating="score" value="" />
							</div>
							<div class="vcm-writereview-stars-comment-wrap" id="vcm-writereview-stars-comment-comm" style="display: none;">
								<input type="text" name="review_cat_comm_comment" value="" autocomplete="off" />
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_COMMENT'); ?></span>
							</div>
						</div>
					</div>

				<?php
				// get the review category tags ("communication") for host-to-guest review
				$rev_cat_tags_comm = VCMAirbnbReview::getCategoryTags('host_review_guest', 'communication');
				if ($rev_cat_tags_comm) {
					?>
					<div class="vcm-param-container">
						<div class="vcm-param-label">&nbsp;</div>
						<div class="vcm-param-setting vcm-htgreview-category-tags" data-category="communication">
						<?php
						foreach ($rev_cat_tags_comm as $tag => $tag_data) {
							// check if tag is positive or negative, if applicable
							$is_tag_positive = (int) (stripos($tag, 'positive') !== false);
							$is_tag_negative = (int) (stripos($tag, 'negative') !== false);
							$tag_icon_cls = null;
							if ($is_tag_positive) {
								$tag_icon_cls = VikBookingIcons::i('thumbs-up');
							} elseif ($is_tag_negative) {
								$tag_icon_cls = VikBookingIcons::i('thumbs-down');
							}
							?>
							<button type="button" class="btn btn-small vcm-htgreview-category-tag-btn" data-tag="<?php echo $tag; ?>" data-positive="<?php echo $is_tag_positive; ?>" data-negative="<?php echo $is_tag_negative; ?>"><?php echo ($tag_icon_cls ? '<i class="' . $tag_icon_cls . '"></i> ' : '') . $tag_data['descr']; ?></button>
							<?php
						}
						?>
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_CAT_TAGS_HELP'); ?></span>
							<input type="hidden" class="vcm-htgreview-category-tags-inp" data-category="communication" value="" />
						</div>
					</div>
					<?php
				}
				?>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCM_HTGREVIEW_HRULES'); ?> <sup>*</sup></div>
						<div class="vcm-param-setting">
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_HRULES_HELP'); ?></span>
							<div class="vcm-writereview-stars-wrap" data-category="respect_house_rules">
							<?php
							for ($i = 1; $i <= 5; $i++) { 
								?>
								<span class="vcm-writereview-star-cont" data-star-cat="hrules" data-star-rating="<?php echo $i; ?>" onclick="vcmSetStarRating(this);"><?php VikBookingIcons::e('star', 'vcm-ota-review-star'); ?></span>
								<?php
							}
							?>
								<input type="hidden" name="review_cat_hrules" id="review-cat-hrules" value="" />
							</div>
							<div class="vcm-writereview-stars-comment-wrap" id="vcm-writereview-stars-comment-hrules" style="display: none;">
								<input type="text" name="review_cat_hrules_comment" value="" autocomplete="off" />
								<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_COMMENT'); ?></span>
							</div>
						</div>
					</div>

				<?php
				// get the review category tags ("respect_house_rules") for host-to-guest review
				$rev_cat_tags_hrules = VCMAirbnbReview::getCategoryTags('host_review_guest', 'respect_house_rules');
				if ($rev_cat_tags_hrules) {
					?>
					<div class="vcm-param-container">
						<div class="vcm-param-label">&nbsp;</div>
						<div class="vcm-param-setting vcm-htgreview-category-tags" data-category="respect_house_rules">
						<?php
						foreach ($rev_cat_tags_hrules as $tag => $tag_data) {
							// check if tag is positive or negative, if applicable
							$is_tag_positive = (int) (stripos($tag, 'positive') !== false);
							$is_tag_negative = (int) (stripos($tag, 'negative') !== false);
							$tag_icon_cls = null;
							if ($is_tag_positive) {
								$tag_icon_cls = VikBookingIcons::i('thumbs-up');
							} elseif ($is_tag_negative) {
								$tag_icon_cls = VikBookingIcons::i('thumbs-down');
							}
							?>
							<button type="button" class="btn btn-small vcm-htgreview-category-tag-btn" data-tag="<?php echo $tag; ?>" data-positive="<?php echo $is_tag_positive; ?>" data-negative="<?php echo $is_tag_negative; ?>"><?php echo ($tag_icon_cls ? '<i class="' . $tag_icon_cls . '"></i> ' : '') . $tag_data['descr']; ?></button>
							<?php
						}
						?>
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_CAT_TAGS_HELP'); ?></span>
							<input type="hidden" class="vcm-htgreview-category-tags-inp" data-category="respect_house_rules" value="" />
						</div>
					</div>
					<?php
				}
				?>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCM_HTGREVIEW_PUBLIC'); ?> <sup>*</sup></div>
						<div class="vcm-param-setting">
							<div class="vcm-htgreview-tarea-cont">
								<textarea name="public_review" id="vcm-public-review" rows="4" cols="50" onBlur="checkRequiredField('vcm-public-review');"></textarea>
							</div>
							<div class="vcm-htgreview-genai-cont">
								<button type="button" class="btn btn-small vbo-btn-black" id="ai-write-review"><?php echo JText::_('VCM_AI_GEN_THROUGH'); ?></button>
							</div>
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_PUBLIC_HELP'); ?></span>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCM_HTGREVIEW_PRIVATE'); ?></div>
						<div class="vcm-param-setting">
							<textarea name="private_review" id="vcm-private-review" rows="4" cols="50"></textarea>
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_PRIVATE_HELP'); ?></span>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"><?php echo JText::_('VCM_HTGREVIEW_WAGAIN'); ?></div>
						<div class="vcm-param-setting">
							<div class="vcm-param-radio-group">
								<span class="vcm-param-radio vcm-param-radio-positive">
									<input type="radio" name="review_host_again" id="review-host-again-yes" value="1" />
									<label for="review-host-again-yes"><?php echo JText::_('VCMYES'); ?></label>
								</span>
								<span class="vcm-param-radio vcm-param-radio-negative">
									<input type="radio" name="review_host_again" id="review-host-again-no" value="0" />
									<label for="review-host-again-no"><?php echo JText::_('VCMNO'); ?></label>
								</span>
							</div>
							<span class="vcm-param-setting-comment"><?php echo JText::_('VCM_HTGREVIEW_WAGAIN_HELP'); ?></span>
						</div>
					</div>

					<div class="vcm-param-container">
						<div class="vcm-param-label"></div>
						<div class="vcm-param-setting">
							<button type="button" class="btn btn-primary" id="vcm-submit-htgreview"><?php VikBookingIcons::e('smile'); ?> <?php echo JText::_('VCM_REVIEW_GUEST_TITLE'); ?></button>
							<button type="button" class="btn" onclick="vcmHandleCancelOperation(false);"><?php echo JText::_('CANCEL'); ?></button>
						</div>
					</div>

				</div>
			</div>
		</fieldset>
	</div>
</div>

<a href="index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $this->reservation['id']; ?>" class="vcm-placeholder-backlink" style="display: none;"></a>

<script type="text/javascript">
	
	function checkRequiredField(id) {
		var elem = jQuery('#'+id);
		if (!elem.length) {
			return;
		}
		var lbl = elem.closest('.vcm-param-container').find('.vcm-param-label');
		if (!lbl.length) {
			return;
		}
		if (elem.val().length) {
			lbl.removeClass('vcm-param-label-isrequired');
			return true;
		}
		lbl.addClass('vcm-param-label-isrequired');
		return false;
	}

	/**
	 * If we are inside a modal, the modal will be dismissed, otherwise
	 * we redirect the user to the booking details page in VikBooking.
	 */
	function vcmHandleCancelOperation(refresh) {
		if (refresh) {
			jQuery('.vcm-params-container').hide();
		}

		var nav_fallback = jQuery('.vcm-placeholder-backlink').first().attr('href');
		var modal = jQuery('.modal[id*="vbo"]');
		var needs_parent = false;
		if (!modal.length) {
			// check if we are in a iFrame and so the element we want is inside the parent
			modal = jQuery('.modal[id*="vbo"]', parent.document);
			if (modal.length) {
				needs_parent = true;
			}
		}
		if (!modal.length) {
			// we are probably not inside a modal, so navigate
			window.location.href = nav_fallback;
			return;
		}
		
		// try to dismiss the modal
		try {
			modal.modal('hide');
		} catch(e) {
			// dismissing did not succeed
		}
		
		if (refresh) {
			// navigate to refresh the page
			if (needs_parent) {
				window.parent.location.href = nav_fallback;
			} else {
				window.location.href = nav_fallback;
			}
		}
	}

	function vcmSetStarRating(elem) {
		let star = jQuery(elem);
		let star_cat = star.attr('data-star-cat');
		let review_cat = star.closest('.vcm-writereview-stars-wrap').attr('data-category');
		let star_rating = star.attr('data-star-rating');
		if (!star_rating || isNaN(star_rating)) {
			return false;
		}
		star_rating = parseInt(star_rating);

		// remove full class from the entire category
		jQuery('.vcm-writereview-star-cont[data-star-cat="' + star_cat + '"]').find('i').removeClass('vcm-ota-review-star-full');

		// add full class until this rating is reached
		for (let i = 1; i <= star_rating; i++) {
			jQuery('.vcm-writereview-star-cont[data-star-cat="' + star_cat + '"][data-star-rating="' + i + '"]').find('i').addClass('vcm-ota-review-star-full');
		}

		// toggle optional comment if less than 5 stars
		if (star_rating < 5) {
			jQuery('#vcm-writereview-stars-comment-' + star_cat).show();
		} else {
			jQuery('#vcm-writereview-stars-comment-' + star_cat).hide().val('');
		}

		// populate rating in hidden field
		jQuery('#review-cat-' + star_cat).val(star_rating);

		// handle category tags
		let cat_btns_list = jQuery('.vcm-htgreview-category-tags[data-category="' + review_cat + '"]');

		// maximum and minimum ratings should make sure negative or positive tags are not selected or displayed
		cat_btns_list.find('button.vcm-htgreview-category-tag-btn').each(function(k, v) {
			let btn = jQuery(v);
			let is_active = btn.hasClass('tag-active');
			let btn_tag = btn.attr('data-tag');
			let is_positive = btn.attr('data-positive');
			let is_negative = btn.attr('data-negative');

			if ((star_rating === 1 && is_positive == '1') || (star_rating === 5 && is_negative == '1')) {
				if (is_active) {
					// trigger the click event to de-activate this category tag
					btn.trigger('click');
				}
				// hide this category tag
				btn.hide();
				btn.addClass('invisible');
			} else if (btn.hasClass('invisible')) {
				// show this previously hid category tag
				btn.show();
				btn.removeClass('invisible');
			}
		});

		// show or hide the whole list of buttons
		if (cat_btns_list.find('button.vcm-htgreview-category-tag-btn:not(.invisible)').length) {
			// show list
			cat_btns_list.closest('.vcm-param-container').show();
		} else {
			// hide list
			cat_btns_list.closest('.vcm-param-container').hide();
		}
	}

	jQuery(function($) {

		// add listener to submit review button
		$('#vcm-submit-htgreview').on('click', function() {
			if (!confirm('<?php echo addslashes(JText::_('VCM_SUBMIT_REVIEW_CONF')); ?>')) {
				return false;
			}

			// build request values
			let rqValues = {
				vbo_oid: <?php echo $this->reservation['id']; ?>,
				review_host_again: document.querySelector('input[name="review_host_again"]:checked')?.value,
			};

			// scan any other input field
			document.querySelector('.vcm-htgreview-fields').querySelectorAll('input[type="hidden"], input[type="text"], textarea').forEach((input_el) => {
				let input_name = input_el.getAttribute('name');
				if (!input_name) {
					return;
				}
				rqValues[input_name] = input_el.value;
			});

			// handle review category tags
			document.querySelectorAll('input.vcm-htgreview-category-tags-inp').forEach((input_el) => {
				let tags_category = input_el.getAttribute('data-category');
				let tags_string = input_el.value;
				if (!tags_string) {
					return;
				}
				let now_category_tags = tags_string.replace('/,$/', '').split(',').filter(t => t);
				if (!rqValues.hasOwnProperty('category_tags')) {
					rqValues['category_tags'] = {};
				}
				rqValues['category_tags'][tags_category] = now_category_tags;
			});

			// make sure the public review is set
			if (!rqValues['public_review']) {
				alert('Public review cannot be empty');
				return false;
			}

			// get the submit button
			let button = $(this);

			// disable button
			button.prop('disabled', true);

			// show loading
			button.find('i').attr('class', '<?php echo VikBookingIcons::i('refresh', 'fa-spin fa-fw'); ?>');

			// make the request
			VBOCore.doAjax(
				'<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=review.host_to_guest'); ?>',
				rqValues,
				(response) => {
					// dismiss modal or redirect when page loads, if process completed successfully
					vcmHandleCancelOperation(true);
				},
				(error) => {
					// display error
					alert(error.responseText || error.statusText || 'An error has occurred');

					// enable button
					button.prop('disabled', false);

					// hide loading
					button.find('i').attr('class', '<?php echo VikBookingIcons::i('smile'); ?>');
				}
			);
		});

		const typeAnswer = (textarea, words, min, max) => {
			if (isNaN(min) || min < 0) {
				min = 0;
			}

			if (isNaN(max) || max < 0) {
				max = 512;
			}

			return new Promise((resolve) => {
				typeAnswerRecursive(resolve, textarea, words, min, max);
			});
		}

		const typeAnswerRecursive = (resolve, textarea, words, min, max) => {
			if (words.length == 0) {
				// typed all the provided words
				resolve();
			} else {
				// register timeout to append the next word
				setTimeout(() => {
					let val = textarea.val();
					// extract word and append it within the textarea value
					textarea.val((val.length ? val + ' ' : '') + words.shift());
					// keep going until we reach the end of the queue
					typeAnswerRecursive(resolve, textarea, words, min, max);
				}, Math.floor(Math.random() * (max - min + 1) + min));
			}
		}

		$('#ai-write-review').on('click', function() {
			// disable button and start animation
			$(this).prop('disabled', true).html('<?php echo VikBookingIcons::e('spinner', 'fa-spin'); ?>');
			$(this).parent().addClass('hover');

			// clear public review textarea
			const reviewTextarea = $('#vcm-public-review');
			reviewTextarea.val('');

			// extract ratings and comments from the form
			let behaviors = [], totRating = 0;

			const lookup = {
				clean: 'Cleanliness',
				comm: 'Communication',
				hrules: 'House Rules',
			};

			const cats_lookup = {
				clean: 'cleanliness',
				comm: 'communication',
				hrules: 'respect_house_rules',
			}

			for (let k in lookup) {
				// take the selected rating
				let rating = parseInt($('input[name="review_cat_' + k + '"]').val() || 5);

				totRating += rating;

				// take the comment of the rating, if any
				let comment = $('input[name="review_cat_' + k + '_comment"]').val();

				if (!comment && cats_lookup[k]) {
					// try to set the comment from the review category tags
					let cat_tags = document.querySelector('.vcm-htgreview-category-tags-inp[data-category="' + cats_lookup[k] + '"]');
					if (cat_tags && cat_tags.value) {
						// gather category tag descriptions
						let active_tag_descriptions = [];
						cat_tags.value.replace('/,$/', '').split(',').filter(t => t).forEach((tag) => {
							let tag_btn = document.querySelector('.vcm-htgreview-category-tag-btn[data-tag="' + tag + '"]');
							if (tag_btn) {
								// push tag description
								active_tag_descriptions.push(tag_btn.innerText.trim());
							}
						});

						if (active_tag_descriptions.length) {
							// use category tag descriptions as notes
							comment = active_tag_descriptions.join(', ');
						}
					}
				}

				if (comment) {
					// push additional notes
					behaviors.push(lookup[k] + ': ' + comment);
				} else {
					// push category score
					behaviors.push(lookup[k] + ': ' + rating + '/5');
				}
			}

			// auto-select the "Would you host this guest again?" radio depending on the overall score
			$('input[name="review_host_again"][value="' + (Math.round(totRating / 3) < 3 ? 0 : 1) + '"]').prop('checked', true);

			new Promise((resolve, reject) => {
				VBOCore.doAjax(
					'<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=ai.review'); ?>',
					{
						customer: '<?php echo $this->customer['first_name'] ?? ''; ?>',
						language: '<?php echo $this->reservation['lang']; ?>',
						behaviors: behaviors.join("\n"),
					},
					(response) => {
						resolve(response);
					},
					(error) => {
						reject(error.responseText || error.statusText || 'An error has occurred');
					}
				);
			}).then(async (response) => {
				await typeAnswer(reviewTextarea, (response.review || '').split(/ +/), 64, 128);
			}).catch((error) => {
				if (error) {
					alert(error);
				}
			}).finally(() => {
				// re-enable button
				$(this).prop('disabled', false).text(Joomla.JText._('VCM_AI_GEN_THROUGH'));
				$(this).parent().removeClass('hover');
			});
		});

		// pre-select all the 5-star ratings
		$('span[data-star-rating="5"]').trigger('click');

		// listen to the click event on the category tag buttons
		$('.vcm-htgreview-category-tag-btn').on('click', function() {
			let btn = $(this);
			let tag = btn.attr('data-tag');
			let category = btn.closest('.vcm-htgreview-category-tags').attr('data-category');
			let input_el = $('.vcm-htgreview-category-tags-inp[data-category="' + category + '"]');
			if (btn.hasClass('tag-active')) {
				// deselect tag
				btn.removeClass('tag-active');
				btn.removeClass('btn-primary');
				input_el.val(input_el.val().replace(tag + ',', ''));
			} else {
				// select tag
				btn.addClass('tag-active');
				btn.addClass('btn-primary');
				input_el.val(tag + ',' + input_el.val());
			}
		});

	});
	
</script>
