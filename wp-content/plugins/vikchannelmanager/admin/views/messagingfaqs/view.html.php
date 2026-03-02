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
 * AI messaging FAQs list view.
 * 
 * @since 1.9
 */
class VikChannelManagerViewMessagingfaqs extends VCMMvcView
{
	/**
	 * @inheritDoc
	 */
	function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();

		$this->filters = [
			'search'   => $app->getUserStateFromRequest('messagingfaqs.filters.search', 'filter_search', '', 'string'),
		];

		$this->limit  = $app->getUserStateFromRequest('messagingfaqs.limit', 'limit', $app->get('list_limit'), 'uint');
		$this->offset = $this->getListLimitStart($this->filters + ['limit' => (int) $this->limit]);

		$this->ordering    = $app->getUserStateFromRequest('messagingfaqs.ordering', 'filter_order', 'topics.hits', 'string');
		$this->orderingDir = $app->getUserStateFromRequest('messagingfaqs.direction', 'filter_order_Dir', 'desc', 'string');

		$query = $db->getQuery(true);

		$query->select('SQL_CALC_FOUND_ROWS topics.*');
		$query->from($db->qn('#__vikchannelmanager_messaging_topics', 'topics'));

		$query->select('threads.idorder');
		$query->leftjoin($db->qn('#__vikchannelmanager_threads', 'threads') . ' ON ' . $db->qn('threads.id') . ' = ' . $db->qn('topics.idthread'));

		if ($this->filters['search'])
		{
			$query->where($db->qn('topics.topic') . ' LIKE ' . $db->q("%{$this->filters['search']}%"));
		}

		if ($this->ordering === 'topics.modified')
		{
			$query->order(sprintf('IFNULL(%s, %s) %s', $db->qn('topics.modified'), $db->qn('topics.created'), $this->orderingDir));
		}
		else
		{
			$query->order($db->qn($this->ordering) . ' ' . $this->orderingDir);
		}

		$db->setQuery($query, $this->offset, $this->limit);
		$this->items = $db->loadObjectList();

		if ($this->items)
		{
			$db->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$this->pageNav = new JPagination($db->loadResult(), $this->offset, $this->limit);
		}
		else
		{
			$this->pageNav = null;
		}

		// calculate the total number of threads
		$countQuery = $db->getQuery(true)
			->select('COUNT(1)')
			->from($db->qn('#__vikchannelmanager_threads'));

		$db->setQuery($countQuery);
		$this->totalThreads = (int) $db->loadResult();

		// count the total number of already processed threads
		$countQuery->where($db->qn('ai_processed') . ' = 1');

		$db->setQuery($countQuery);
		$this->processedThreads = (int) $db->loadResult();
		
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
		JToolBarHelper::title(JText::_('VCM_AI_MESSAGING_FAQS_TITLE'), 'vikchannelmanager');

		$this->user = JFactory::getUser();

		if ($this->user->authorise('core.delete', 'com_vikchannelmanager')) {
			JToolBarHelper::deleteList(JText::_('VBO_WANT_PROCEED'), 'messagingfaqs.delete');
		}
	}
}
