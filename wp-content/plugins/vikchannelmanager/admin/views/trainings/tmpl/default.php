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

JHtml::_('bootstrap.tooltip', '.hasTooltip');

VCM::load_css_js();

$vik = new VikApplication(VersionListener::getID());

$canEdit = $this->user->authorise('core.edit', 'com_vikchannelmanager');

JText::script('VCM_TRANSLATE');
JText::script('VBOCLOSE');

?>

<form action="index.php?option=com_vikchannelmanager&view=trainings" method="post" name="adminForm" id="adminForm" class="vcm-list-form">

	<div class="vcm-list-form-filters vcm-btn-toolbar" style="margin-bottom: 20px;">
		<div id="filter-bar" class="btn-toolbar" style="width: 100%; display: inline-block;">
			
			<div class="btn-group pull-left">
				<input name="filter_search" type="text" id="search-filter" value="<?php echo $this->escape($this->filters['search'] ?? ''); ?>" size="40" placeholder="<?php echo $this->escape(JText::_('VCMBCAHSUBMIT')); ?>" />
			</div>

			<div class="btn-group pull-left">
				<select name="filter_listing" id="listing-filter">
					<?php
					$options = [
						JHtml::_('select.option', '', JText::_('VCM_SELECT_LISTING_FILTER')),
						JHtml::_('select.option', 0, JText::_('VCM_SELECT_ALL_LISTINGS')),
					];

					foreach ($this->rooms as $room) {
						$options[] = JHtml::_('select.option', $room['id'], $room['name']);
					}

					echo JHtml::_('select.options', $options, 'value', 'text', $this->filters['listing'] ?? null);
					?>
				</select>
			</div>
			
			<div class="btn-group pull-left">
				<select name="filter_status" id="status-filter">
					<?php
					$options = [
						JHtml::_('select.option', '', 'VCM_SELECT_STATUS_FILTER'),
						JHtml::_('select.option', 1, 'VCMPAYMENTSTATUS1'),
						JHtml::_('select.option', 0, 'VCMPAYMENTSTATUS0'),
					];

					echo JHtml::_('select.options', $options, 'value', 'text', $this->filters['status'] ?? null, true);
					?>
				</select>
			</div>

			<div class="btn-group pull-left">
				<select name="filter_language" id="language-filter">
					<?php
					$options = [
						JHtml::_('select.option', '', JText::_('VCM_SELECT_LANG_FILTER')),
					];

					foreach ($this->languages as $lang) {
						$options[] = JHtml::_('select.option', $lang['tag'], $lang['name']);
					}

					echo JHtml::_('select.options', $options, 'value', 'text', $this->filters['language'] ?? null);
					?>
				</select>
			</div>
			
			<div class="btn-group pull-left">
				&nbsp;&nbsp;&nbsp;
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-secondary"><i class="vboicn-search"></i> <?php echo JText::_('VCMBCAHSUBMIT'); ?></button>
			</div>
			<div class="btn-group pull-left">
				<button type="button" class="btn" id="clear-filters-btn"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>

			<div class="btn-group pull-right">
				<button type="button" class="btn btn-primary" id="ai-playground-btn"><?php VikBookingIcons::e('keyboard'); ?>&nbsp;<?php echo JText::_('VCM_AI_PLAYGROUND'); ?></button>
			</div>
		</div>
	</div>

	<?php if ($this->items): ?>
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
			<?php echo $vik->openTableHead(); ?>
				<tr>
					<th width="1%">
						<?php echo $vik->getAdminToggle(count($this->items)); ?>
					</th>
					<th class="title" width="30%"><?php echo JHtml::_('grid.sort', 'VCM_TITLE', 'title', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center" width="10%"><?php echo JText::_('VCM_AIRBNB_LISTINGS'); ?></th>
					<th class="title center" width="5%"><?php echo JText::_('VCMBCAHLANGUAGE'); ?></th>
					<th class="title center" width="5%"><?php echo JText::_('VBSTATUS'); ?></th>
					<th class="title center hidden-phone" width="10%"><?php echo JHtml::_('grid.sort', 'VCM_CREATED_DATE', 'created', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center hidden-phone" width="10%"><?php echo JHtml::_('grid.sort', 'VCM_MODIFIED_DATE', 'modified', $this->orderingDir, $this->ordering); ?></th>
				</tr>
			<?php echo $vik->closeTableHead(); ?>
			<?php
			for ($i = 0, $n = count($this->items); $i < $n; $i++) {
				$item = $this->items[$i];
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo (int) $item->id; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
					</td>
					<td>
						<div style="font-weight: 500;">
							<?php if ($canEdit): ?>
								<a href="index.php?option=com_vikchannelmanager&task=training.edit&cid[]=<?php echo (int) $item->id; ?>">
									<?php echo $item->title; ?>
								</a>
							<?php else: ?>
								<?php echo $item->title; ?>
							<?php endif; ?>
							<?php if ($item->attachments): ?>
								<span class="badge badge-info"><?php VikBookingIcons::e('paperclip'); ?>&nbsp;<?php echo count($item->attachments); ?></span>
							<?php endif; ?>
						</div>
						<div class="hidden-phone">
							<small>
							<?php
							$content = strip_tags($item->content);

							if (mb_strlen($content, 'UTF-8') > 256) {
								$content = rtrim(mb_substr($content, 0, 220, 'UTF-8'), '.,?!:;"\'/\\');
							}

							echo $content;
							?>
							</small>
						</div>
					</td>
					<td class="center">
						<?php
						if ($item->listing_selection == 1) {
							?><i class="fas fa-minus-circle hasTooltip" style="color: #d9534f;" title="<?php echo $this->escape(JText::_('VCM_ALL_EXCEPT')); ?>"></i>&nbsp;<?php
						}

						if (($listingsCount = count($item->id_listing)) > 1) {
							// multiple listings selected
							echo JText::sprintf('VCM_N_LISTINGS', $listingsCount);
						} else if ($listingsCount == 1) {
							// one listing only
							echo $this->rooms[$item->id_listing[0]]['name'] ?? $item->id_listing[0];
						} else {
							// no listings (= all)
							echo '*';
						}
						?>
					</td>
					<td class="center">
						<?php
						if (preg_match("/^[a-z]{2,3}-([a-z]{2,2})$/i", (string) $item->language, $match)) {
							// we have a langtag, find the last match
							$code2 = end($match);
						} else {
							// use the given code (only 2 chars)
							$code2 = substr((string) $item->language, 0, 2);
						}
						?>
						<img src="<?php echo VCM_ADMIN_URI . 'assets/css/flags/' . strtolower($code2) . '.png'; ?>" title="<?php echo $this->escape($this->languages[$item->language]['name'] ?? $item->language); ?>" class="hasTooltip" style="max-width: 20px;" />
						<br />
						<?php if (!$item->needsreview): ?>
							<a href="javascript:void(0)" class="open-translate-modal hasTooltip" data-id="<?php echo (int) $item->id; ?>" data-locale="<?php echo $this->escape($item->language); ?>" title="<?php echo $this->escape(JText::_('VCM_TRANSLATE')); ?>">
								<?php VikBookingIcons::e('language', 'fa-2x'); ?>
							</a>
						<?php else: ?>
							<span style="opacity:.25;"><?php VikBookingIcons::e('language', 'fa-2x'); ?></span>
						<?php endif; ?>
					</td>
					<td class="center">
						<?php if ($item->needsreview):
							$title = JText::plural('VCM_AI_TRAINING_NEEDS_REVIEW_WARNING', $this->trainingModel->getExpirationDays($item));

							if ($canEdit): ?>
								<a href="index.php?option=com_vikchannelmanager&task=training.edit&cid[]=<?php echo (int) $item->id; ?>">
									<i class="<?php echo VikBookingIcons::i('exclamation-triangle', 'fa-2x'); ?> hasTooltip" title="<?php echo $this->escape($title); ?>" style="color: #f90;"></i>
								</a>
							<?php else: ?>
								<i class="<?php echo VikBookingIcons::i('exclamation-triangle', 'fa-2x'); ?> hasTooltip" title="<?php echo $this->escape($title); ?>" style="color: #f90;"></i>
							<?php endif; ?>
						<?php else: ?>
							<a href="<?php echo VCMFactory::getPlatform()->getUri()->addCSRF('index.php?option=com_vikchannelmanager&task=training.publish&cid[]=' . $item->id . '&state=' . (($item->published + 1) % 2)); ?>">
								<?php if ($item->published): ?>
									<i class="<?php echo VikBookingIcons::i('check-circle', 'fa-2x'); ?>" style="color: green;"></i>
								<?php else: ?>
									<i class="<?php echo VikBookingIcons::i('times-circle', 'fa-2x'); ?>" style="color: #d9534f;"></i>
								<?php endif; ?>
							</a>
						<?php endif; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('date.relative', $item->created, null, null, 'Y-m-d H:i:s'); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo $item->modified ? JHtml::_('date.relative', $item->modified, null, null, 'Y-m-d H:i:s') : '/'; ?>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
		
		<table align="center"><tr><td><?php echo $this->pageNav->getListFooter(); ?></td></tr></table>
	<?php else: ?>
		<p class="warn"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></p>
	<?php endif; ?>

	<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->ordering); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->orderingDir); ?>" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="view" value="trainings" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	
	<?php echo JHTML::_( 'form.token' ); ?>

</form>

<?php
echo $this->loadTemplate('translate_modal');
echo JLayoutHelper::render('ai.playground.modal', ['rooms' => $this->rooms]);
?>

<script>
	(function($) {
		'use strict';

		const translateTraining = (id, languages) => {
			return new Promise((resolve, reject) => {
				VBOCore.doAjax(
					'index.php?option=com_vikchannelmanager&task=training.translate',
					{
						id: id,
						languages: languages,
					},
					(data) => {
						resolve(data);
					},
					(error) => {
						reject(error.responseText || error.statusText || 'An error has occurred! Please try again.');
					} 
				);
			});
		}

		$(function() {
			$('#clear-filters-btn').on('click', () => {
				$('#search-filter').val('');
				$('#listing-filter').val('');
				$('#status-filter').val('');
				$('#language-filter').val('');
				$('#adminForm').submit();
			});

			$('.open-translate-modal').on('click', function() {
				const selectedTrainingId = $(this).data('id')
				const selectedLocale = $(this).data('locale');

				const languagesDropdown = $('#translate-languages-select');

				let locales = [];

				// auto select all the supported languages, except for the locale of the existing training set
				languagesDropdown.find('option').each(function() {
					const langtag = $(this).val();

					if (langtag != selectedLocale) {
						locales.push(langtag);
					}
				});

				languagesDropdown.select2('val', locales);

				const closeButton = $('<button type="button" class="btn btn-secondary"></button>')
					.text(Joomla.JText._('VBOCLOSE'))
					.on('click', () => {
						VBOCore.emitEvent('translate.dismiss');
					});

				const translateButton = $('<button type="button" class="btn btn-success"></button>')
					.text(Joomla.JText._('VCM_TRANSLATE'))
					.on('click', async function() {
						let languages = languagesDropdown.select2('val').filter(locale => locale != selectedLocale);

						if (!languages || !languages.length) {
							return false;
						}

						VBOCore.emitEvent('translate.loading');
						$(this).prop('disabled', true);

						try {
							await translateTraining(selectedTrainingId, languages);

							VBOCore.emitEvent('translate.dismiss');
							setTimeout(() => {
								$('#adminForm').submit();
							}, 512);
						} catch (error) {
							alert(error);
							$(this).prop('disabled', false);
						}

						VBOCore.emitEvent('translate.loading');
					});

				VBOCore.displayModal({
					suffix: 'confirm',
					header: false,
					body: $('#translate-modal'),
					body_prepend: true,
					lock_scroll: true,
					footer_left: closeButton,
					footer_right: translateButton,
					dismiss_event: 'translate.dismiss',
					loading_event: 'translate.loading',
					loading_body: '<?php VikBookingIcons::e('circle-notch', 'fa-spin fa-fw'); ?>',
					onDismiss: () => {
						$('#translate-modal').appendTo('#translate-modal-wrapper');
					},
				});
			});

			$('#ai-playground-btn').on('click', () => {
				window.openAiPlayground();
			});
		});
	})(jQuery);
</script>