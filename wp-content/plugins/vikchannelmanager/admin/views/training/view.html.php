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
 * AI training management view.
 * 
 * @since 1.9
 */
class VikChannelManagerViewTraining extends VCMMvcView
{
	/**
	 * @inheritDoc
	 */
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$id = $app->input->get('cid', [], 'uint');

		$this->training = null;

		$this->trainingModel = new VCMAiModelTraining;
			
		if ($id)
		{
			try
			{
				$this->training = $this->trainingModel->getItem($id[0]);

				if ($this->training->created_by)
				{
					$this->training->created_by = new JUser($this->training->created_by);
				}
				else
				{
					// in case the training set has no author, assume the latter was created by the AI
					$this->training->created_by = (object) [
						'id' => 0,
						'username' => 'AI',
						'name' => JText::_('VCM_AI_CHAT_TOOLTIP'),
					];	
				}
				
				if ($this->training->modified_by)
				{
					$this->training->modified_by = new JUser($this->training->modified_by);
				}
			}
			catch (Exception $error)
			{
				if ($app->input->get('layout') === 'modal') {
					// propagate error for modal layout
					throw $error;
				}

				$app->enqueueMessage($error->getMessage(), 'error');
				$app->redirect('index.php?option=com_vikchannelmanager&view=trainings');
				return;
			}
		}

		$this->injectUserStateData($this->training, 'vcm.training.data');

		if (empty($this->training->listing_selection) && empty($this->training->id_listing)) {
			// simulate "all listings" selection
			$this->training->listing_selection = '*';
		}

		if ($this->training->needsreview) {
			// in case the training set needs to be reviewed, manually toggle the published status
			$this->training->published = 1;
		}

		$this->rooms = VikBooking::getAvailabilityInstance()->loadRooms();

		if ($app->input->get('layout') === 'modal') {
			// prefer modal layout
			$this->setLayout('modal');
		} else {
			// Set the toolbar
			$this->addToolBar();
		}
		
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
		// add menu title and some buttons to the page
		if ($this->training->id ?? null)
		{
			JToolbarHelper::title(JText::_('VCM_AI_TRAININGS_TITLE_EDIT'), 'vikchannelmanager');
		}
		else
		{
			JToolbarHelper::title(JText::_('VCM_AI_TRAININGS_TITLE_NEW'), 'vikchannelmanager');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikchannelmanager')
			|| $user->authorise('core.create', 'com_vikchannelmanager'))
		{
			JToolbarHelper::apply('training.save');
			JToolbarHelper::save('training.saveclose');
		}

		if ($user->authorise('core.edit', 'com_vikchannelmanager')
			&& $user->authorise('core.create', 'com_vikchannelmanager'))
		{
			JToolbarHelper::save2new('training.savenew');
		}

		JToolbarHelper::cancel('training.cancel', ($this->training->id ?? null) ? 'VBOCLOSE' : 'JTOOLBAR_CANCEL');
	}
}
