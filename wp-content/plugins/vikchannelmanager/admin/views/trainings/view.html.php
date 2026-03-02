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

/**
 * AI trainings list view.
 * 
 * @since 1.9
 */
class VikChannelManagerViewTrainings extends VCMMvcView
{
	/**
	 * @inheritDoc
	 */
	function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$app = JFactory::getApplication();

		$this->filters = [
			'search'   => $app->getUserStateFromRequest('trainings.filters.search', 'filter_search', '', 'string'),
			'listing'  => $app->getUserStateFromRequest('trainings.filters.listing', 'filter_listing', '', 'string'),
			'status'   => $app->getUserStateFromRequest('trainings.filters.status', 'filter_status', '', 'string'),
			'language' => $app->getUserStateFromRequest('trainings.filters.language', 'filter_language', '', 'string'),
		];

		$this->limit  = $app->getUserStateFromRequest('trainings.limit', 'limit', $app->get('list_limit'), 'uint');
		$this->offset = $this->getListLimitStart($this->filters + ['limit' => (int) $this->limit]);

		$this->ordering    = $app->getUserStateFromRequest('trainings.ordering', 'filter_order', 'created', 'string');
		$this->orderingDir = $app->getUserStateFromRequest('trainings.direction', 'filter_order_Dir', 'desc', 'string');

		$this->filters = array_filter($this->filters, function($v) {
			return $v !== null && $v !== '';
		});

		$this->trainingModel = new VCMAiModelTraining;

		// load the training sets
		$response = $this->trainingModel->getItems($this->filters, [
			'ordering' => $this->ordering,
			'direction' => $this->orderingDir,
			'offset' => $this->offset,
			'limit' => $this->limit,
		]);

		if (!$response->items && $response->pagination->total) {
			// We deleted the last item of the list and the pagination is now empty...
			// Automatically refresh the list by loading the previous page.
			$app->redirect('index.php?option=com_vikchannelmanager&view=trainings&limitstart=' . ($this->offset - $this->limit));
			return;
		}

		$this->items = $response->items;

		jimport('joomla.html.pagination');
		$this->pageNav = new JPagination($response->pagination->total, $response->pagination->offset, $response->pagination->limit);

		$this->languages = VikBooking::getVboApplication()->getKnownLanguages();

		$this->rooms = VikBooking::getAvailabilityInstance()->loadRooms();
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Toolbar setup.
	 * 
	 * @return  void
	 */
	protected function addToolBar()
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCM_AI_TRAININGS_TITLE'), 'vikchannelmanager');

		$this->user = JFactory::getUser();

		if ($this->user->authorise('core.create', 'com_vikchannelmanager')) {
			JToolBarHelper::addNew('training.add');
		}

		if ($this->user->authorise('core.edit', 'com_vikchannelmanager')) {
			JToolBarHelper::editList('training.edit');
		}

		if ($this->user->authorise('core.delete', 'com_vikchannelmanager')) {
			JToolBarHelper::deleteList(JText::_('VBO_WANT_PROCEED'), 'training.delete');
		}
	}
}
