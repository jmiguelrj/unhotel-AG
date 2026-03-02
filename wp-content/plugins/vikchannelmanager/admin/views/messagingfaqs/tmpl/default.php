<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JHtml::_('bootstrap.tooltip', '.hasTooltip');

VCM::load_css_js();

$vik = new VikApplication(VersionListener::getID());

?>

<p class="info">
	<span><?php echo JText::_('VCM_AI_MESSAGING_FAQS_WARN'); ?></span>
	<span
		class="badge badge-<?php echo $this->processedThreads == $this->totalThreads ? 'success' : 'info'; ?>"
		style="float: right;"
	><?php echo JText::sprintf('VCM_AI_MESSAGING_FAQS_PROCESSED', $this->processedThreads, $this->totalThreads); ?></span>
</p>

<form action="index.php?option=com_vikchannelmanager&view=messagingfaqs" method="post" name="adminForm" id="adminForm" class="vcm-list-form">

	<div class="vcm-list-form-filters vcm-btn-toolbar" style="margin-bottom: 20px;">
		<div id="filter-bar" class="btn-toolbar" style="width: 100%; display: inline-block;">
			
			<div class="btn-group pull-left">
				<input name="filter_search" type="text" id="search-filter" value="<?php echo $this->escape($this->filters['search'] ?? ''); ?>" size="40" placeholder="<?php echo $this->escape(JText::_('VCMBCAHSUBMIT')); ?>" />
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
		</div>
	</div>

	<?php if ($this->items): ?>
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?> vcm-list-table">
			<?php echo $vik->openTableHead(); ?>
				<tr>
					<th width="1%">
						<?php echo $vik->getAdminToggle(count($this->items)); ?>
					</th>
					<th class="title" width="30%"><?php echo JHtml::_('grid.sort', 'VCM_TOPIC', 'topics.topic', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center" width="5%"><?php echo JHtml::_('grid.sort', 'VCM_HITS', 'topics.hits', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center" width="10%"><?php echo JHtml::_('grid.sort', 'VCM_CREATED_DATE', 'topics.created', $this->orderingDir, $this->ordering); ?></th>
					<th class="title center" width="10%"><?php echo JHtml::_('grid.sort', 'VCM_MODIFIED_DATE', 'topics.modified', $this->orderingDir, $this->ordering); ?></th>
				</tr>
			<?php echo $vik->closeTableHead(); ?>
			<?php
			for ($i = 0, $n = count($this->items); $i < $n; $i++) {
				$item = $this->items[$i];

				if ($item->hits == 1) {
					$badgeStyle = 'error';
				} else if ($item->hits < 4) {
					$badgeStyle = 'warning';
				} else if ($item->hits < 10) {
					$badgeStyle = 'info';
				} else {
					$badgeStyle = 'success';
				}

				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo (int) $item->id; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
					</td>
					<td>
						<div style="font-weight: 500;">
							<?php if ($item->idorder): ?>
								<a href="javascript:void(0)" data-order="<?php echo (int) $item->idorder; ?>" class="topic-thread-handle">
									<?php echo $item->topic; ?>
								</a>
							<?php else: ?>
								<?php echo $item->topic; ?>
							<?php endif; ?>
						</div>
					</td>
					<td class="center">
						<a href="javascript:void(0)" class="badge badge-<?php echo $badgeStyle; ?> topic-search-handle" data-topic="<?php echo $this->escape($item->topic); ?>"><?php echo $item->hits; ?></a>
					</td>
					<td class="center">
						<?php echo JHtml::_('date.relative', $item->created, null, null, 'Y-m-d H:i:s'); ?>
					</td>
					<td class="center">
						<?php echo $item->modified ? JHtml::_('date.relative', $item->modified, null, null, 'Y-m-d H:i:s') : '/'; ?>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
		
		<?php if ($this->pageNav): ?>
			<table align="center"><tr><td><?php echo $this->pageNav->getListFooter(); ?></td></tr></table>
		<?php endif; ?>
	<?php else: ?>
		<p class="warn"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></p>
	<?php endif; ?>

	<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->ordering); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->orderingDir); ?>" />
	<input type="hidden" name="option" value="com_vikchannelmanager" />
	<input type="hidden" name="view" value="messagingfaqs" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	
	<?php echo JHTML::_( 'form.token' ); ?>

</form>

<script>
	(function($) {
		'use strict';

		$(function() {
			$('#clear-filters-btn').on('click', () => {
				$('#search-filter').val('');
				$('#adminForm').submit();
			});

			$('.topic-thread-handle').on('click', function() {
				const orderId = $(this).data('order');
				
				VBOCore.handleDisplayWidgetNotification({widget_id: 'guest_messages'}, {bid: orderId});
			});

			$('.topic-search-handle').on('click', function() {
				const topic = $(this).data('topic');
				
				VBOCore.handleDisplayWidgetNotification({widget_id: 'guest_messages'}, {message_contains: topic});
			});
		});
	})(jQuery);
</script>