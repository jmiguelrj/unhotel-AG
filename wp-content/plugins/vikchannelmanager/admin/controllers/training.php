<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

/**
 * AI training admin controller.
 * 
 * @since 1.9
 */
class VikChannelManagerControllerTraining extends JControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return  bool
	 */
	public function add()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// unset user state for being recovered again
		$app->setUserState('vcm.training.data', []);

		// check user permissions
		if (!$user->authorise('core.create', 'com_vikchannelmanager'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikchannelmanager&view=training');
		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return  bool
	 */
	public function edit()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// unset user state for being recovered again
		$app->setUserState('vcm.training.data', []);

		// check user permissions
		if (!$user->authorise('core.edit', 'com_vikchannelmanager'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', [0]);

		$this->setRedirect('index.php?option=com_vikchannelmanager&view=training&cid[]=' . $cid[0]);
		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return  void
	 */
	public function saveclose()
	{
		if ($this->save())
		{
			$this->cancel();
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the creation
	 * page of a new record.
	 *
	 * @return  void
	 */
	public function savenew()
	{
		if ($this->save())
		{
			$this->setRedirect('index.php?option=com_vikchannelmanager&task=training.add');
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return  bool
	 */
	public function save()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		$isAjax = $app->input->getBool('ajax', false);

		if (!JSession::checkToken())
		{
			if ($isAjax) {
				VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
			}

			// back to main list, missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$this->cancel();

			return false;
		}
		
		$args = [];
		$args['id']                = $app->input->getUint('id', 0);
		$args['title']             = $app->input->getString('title', '');
		$args['content']           = $app->input->getString('content', '');
		$args['attachments']       = $app->input->getString('attachments', []);
		$args['id_listing']        = $app->input->getUint('id_listing', []);
		$args['listing_selection'] = $app->input->getString('listing_selection', 0);
		$args['published']         = $app->input->getBool('published', false);
		$args['language']          = $app->input->getString('language', '');

		if ($args['listing_selection'] === '*') {
			// always reset the listings in case of "all" option picked
			$args['id_listing'] = [];
		}

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikchannelmanager'))
		{
			if ($isAjax) {
				VCMHttpDocument::getInstance($app)->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}

			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$app->setUserState('vcm.training.data', $args);

		// take the new attachments to upload
		$uploadFiles = $app->input->files->get('attachments', [], 'array');

		$warnings = [];

		foreach ($uploadFiles as $file)
		{
			if (empty($file['name']))
			{
				continue;
			}

			$dest = VCM_SITE_PATH . '/helpers/chat/attachments/docs/' . $file['name'];

			try
			{
				// validate supported file extension
				if (!preg_match("/\.(a?png|bmp|gif|ico|jpe?g|svg|ip|tar|rar|gz|bzip2|pdf|docx?|rtf|odt|pages|xlsx?|csv|ods|numbers|ppsx?|odp|key|txt|md|markdown)$/i", $file['name']))
				{
					$ext = explode('.', $file['name']);
					throw new RuntimeException(sprintf('File type [%s] not supported', count($ext) > 1 ? end($ext) : ''), 400);
				}

				// try to upload the file
				if (!JFile::upload($file['tmp_name'], $dest))
				{
					throw new RuntimeException(sprintf('Unable to upload the file [%s] to [%s]', $file['tmp_name'], $dest), 500);
				}

				// do not push the attachment in case it already exists
				if (!in_array($file['name'], $args['attachments']))
				{
					$args['attachments'][] = $file['name'];
				}
			}
			catch (Exception $error)
			{
				if ($isAjax) {
					$warnings[] = $error->getMessage();
				} else {
					$app->enqueueMessage($error->getMessage(), 'warning');
				}
			}
		}

		$model = new VCMAiModelTraining;

		try
		{
			if ($args['id'] > 0)
			{
				$model->update($args);
			}
			else
			{
				$args['id'] = $model->insert($args);
			}
		}
		catch (Exception $error)
		{
			$errorMessage = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error->getMessage());

			if ($isAjax) {
				VCMHttpDocument::getInstance($app)->close($error->getCode() ?: 500, $errorMessage);
			}

			// display error message
			$app->enqueueMessage($errorMessage, 'error');

			$url = 'index.php?option=com_vikchannelmanager&view=training';

			if ($args['id'])
			{
				$url .= '&cid[]=' . $args['id'];
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		if ($isAjax) {
			VCMHttpDocument::getInstance($app)->json([
				'success' => true,
				'warnings' => $warnings,
			]);
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikchannelmanager&task=training.edit&cid[]=' . $args['id']);
		return true;
	}

	/**
	 * Translate the provided AI training into the specified languages.
	 * 
	 * @return  bool
	 */
	public function translate()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!JSession::checkToken())
		{
			// missing CSRF-proof token
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		// check user permissions
		if (!$user->authorise('core.create', 'com_vikchannelmanager'))
		{
			// not authorised to create records
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$ids = $app->input->getUint('id', []);
		$languages = $app->input->getString('languages', []);

		if (!$languages)
		{
			VCMHttpDocument::getInstance($app)->close(400, 'No provided languages.');
		}

		$trainingModel = new VCMAiModelTraining;
		$serviceModel = new VCMAiModelService;

		$response = [];

		try
		{
			foreach ((array) $ids as $id)
			{
				// fetch training details
				$training = $trainingModel->getItem($id);

				foreach ((array) $languages as $locale)
				{
					if ($training->language == $locale)
					{
						// skip if we are translating the set into the same language
						continue;
					}

					// attempt to translate the content of the current training set for the given locale
					$translatedText = $serviceModel->translate($training->content, $locale);

					// create new record
					$newTraining = clone $training;
					$newTraining->title .= " ($locale)";
					$newTraining->content = $translatedText;
					$newTraining->language = $locale;

					// make insert request
					$newTraining->id = $trainingModel->insert($newTraining);

					$response[] = $newTraining;
				}
			}
		}
		catch (Exception $error)
		{
			// something went wrong, abort all
			VCMHttpDocument::getInstance($app)->close($error->getCode(), $error->getMessage());
		}

		// sent the created training sets to the caller
		VCMHttpDocument::getInstance($app)->json($response);
	}

	/**
	 * Changes the state of the selected AI trainings.
	 * 
	 * @return  bool
	 */
	public function publish()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// always go back to the trainings list
		$this->cancel();

		if (!JSession::checkToken() && !JSession::checkToken('get'))
		{
			// missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			return false;
		}

		$cid = $app->input->get('cid', [], 'uint');
		$state = $app->input->get('state', 1, 'uint');

		// check user permissions
		if (!$user->authorise('core.edit', 'com_vikchannelmanager'))
		{
			// not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}

		$model = new VCMAiModelTraining;

		foreach ($cid as $id)
		{
			try
			{
				// attempt to change the status of the training set
				$model->update([
					'id' => $id,
					'published' => $state,
				]);
			}
			catch (Exception $error)
			{
				$app->enqueueMessage($error->getMessage(), 'error');
			}
		}

		return true;
	}

	/**
	 * Delete the selected AI trainings.
	 * 
	 * @return  bool
	 */
	public function delete()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		$isAjax = $app->input->getBool('ajax', false);

		// always go back to the trainings list
		$this->cancel();

		if (!JSession::checkToken())
		{
			if ($isAjax) {
				VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
			}

			// missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			return false;
		}

		$cid = $app->input->get('cid', [], 'uint');

		// check user permissions
		if (!$user->authorise('core.delete', 'com_vikchannelmanager'))
		{
			if ($isAjax) {
				VCMHttpDocument::getInstance($app)->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}

			// not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}

		$model = new VCMAiModelTraining;

		$warnings = [];

		foreach ($cid as $id)
		{
			try
			{
				// attempt to delete the training set
				$model->delete((int) $id);
			}
			catch (Exception $error)
			{
				if ($isAjax) {
					$warnings[] = $error->getMessage();
				} else {
					$app->enqueueMessage($error->getMessage(), 'warning');
				}
			}
		}

		if ($isAjax) {
			VCMHttpDocument::getInstance($app)->json([
				'success' => true,
				'warnings' => $warnings,
			]);
		}

		return true;
	}

	/**
	 * Returns to the trainings list.
	 * 
	 * @return  void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikchannelmanager&view=trainings');
	}

	/**
	 * AJAX end-point to access the details form of a training drafts.
	 *
	 * @return 	void
	 *
	 * @since 	1.9.16
	 */
	public function editdraft()
	{
		$app = JFactory::getApplication();

		// clear cached information
		$app->setUserState('vcm.training.data', []);

		// add support for both view and task
		$app->input->set('view', 'training');
		$app->input->set('layout', 'modal');
		$app->input->set('tmpl', 'component');

		// start output buffer
		ob_start();
		
		try {
			// display view
			parent::display();
		} catch (Exception $e) {
			// clear output buffer
			ob_end_clean();
			// raise error
			VCMHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
		}

		// obtain view HTML from buffer
		$html = ob_get_contents();
		// clear output buffer
		ob_end_clean();

		// encode HTML in JSON to avoid encoding issues
		VCMHttpDocument::getInstance($app)->json(json_encode([
			'html' => $html,
		]));
	}
}
