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
 * AI service admin controller.
 * 
 * @since 1.9
 */
class VikChannelManagerControllerAi extends JControllerAdmin
{
	/**
	 * Task used to trigger the AI answer service.
	 *
	 * @return  void
	 */
	public function answer()
	{
		$app = JFactory::getApplication();

		if (!JSession::checkToken())
		{
			// missing CSRF-proof token
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		if (!VikChannelManager::getChannel(VikChannelManagerConfig::AI))
		{
			// AI channel not enabled
			VCMHttpDocument::getInstance($app)->close(402, JText::_('VCM_AI_PAID_SERVICE_REQ'));
		}

		$question = $app->input->get('text', [], 'array');
		$options  = $app->input->get('options', [], 'array');

		try
		{
			$message = (new VCMAiModelService)->answer($question, $options);
		}
		catch (Exception $error)
		{
			// something went wrong
			VCMHttpDocument::getInstance($app)->close($error->getCode() ?: 500, $error->getMessage());
		}

		// inject question within the response answer
		VCMHttpDocument::getInstance($app)->json(array_merge(
			[
				'question' => $question,
			],
			(array) $message
		));
	}

	/**
	 * Task used to trigger the AI assistant service.
	 *
	 * @return  void
	 */
	public function assistant()
	{
		$app = JFactory::getApplication();

		if (!JSession::checkToken())
		{
			// missing CSRF-proof token
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		if (!VikChannelManager::getChannel(VikChannelManagerConfig::AI))
		{
			// AI channel not enabled
			VCMHttpDocument::getInstance($app)->close(402, JText::_('VCM_AI_PAID_SERVICE_REQ'));
		}

		$threadId = $app->input->get('thread_id', null, 'string');
		$messages = $app->input->get('messages', [], 'array');
		$scope    = $app->input->get('scope', '', 'string');

		try
		{
			$answer = (new VCMAiModelService)->assistant($messages, $threadId, $scope);
		}
		catch (Exception $error)
		{
			// something went wrong
			VCMHttpDocument::getInstance($app)->close($error->getCode() ?: 500, $error->getMessage());
		}

		VCMHttpDocument::getInstance($app)->json([
			'thread_id' => $answer->threadId,
			'html'      => $answer->result,
			'text'      => $answer->text,
			'addon'     => $answer->addon ?? null,
		]);
	}

	/**
	 * Task used to trigger the AI translate service.
	 *
	 * @return  void
	 */
	public function translate()
	{
		$app = JFactory::getApplication();

		if (!JSession::checkToken())
		{
			// missing CSRF-proof token
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		if (!VikChannelManager::getChannel(VikChannelManagerConfig::AI))
		{
			// AI channel not enabled
			VCMHttpDocument::getInstance($app)->close(402, JText::_('VCM_AI_PAID_SERVICE_REQ'));
		}

		$text   = JComponentHelper::filterText($app->input->getRaw('text', ''));
		$locale = $app->input->getString('locale', '');

		try
		{
			$translated = (new VCMAiModelService)->translate($text, $locale);
		}
		catch (Exception $error)
		{
			// something went wrong
			VCMHttpDocument::getInstance($app)->close($error->getCode() ?: 500, $error->getMessage());
		}

		VCMHttpDocument::getInstance($app)->json([
			'original' => $text,
			'translated' => $translated,
			'locale' => $locale,
		]);
	}

	/**
	 * Task used to trigger the AI review service.
	 *
	 * @return  void
	 */
	public function review()
	{
		$app = JFactory::getApplication();

		if (!JSession::checkToken())
		{
			// missing CSRF-proof token
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JINVALID_TOKEN'));
		}

		if (!VikChannelManager::getChannel(VikChannelManagerConfig::AI))
		{
			// AI channel not enabled
			VCMHttpDocument::getInstance($app)->close(402, JText::_('VCM_AI_PAID_SERVICE_REQ'));
		}

		$args = [];
		$args['review']    = $app->input->getString('review');
		$args['customer']  = $app->input->getString('customer');
		$args['behaviors'] = $app->input->getString('behaviors');
		$args['language']  = $app->input->getString('language');
		$args['id_order']  = $app->input->getUint('id_order');

		try
		{
			$review = (new VCMAiModelService)->review($args);
		}
		catch (Exception $error)
		{
			// something went wrong
			VCMHttpDocument::getInstance($app)->close($error->getCode() ?: 500, $error->getMessage());
		}

		VCMHttpDocument::getInstance($app)->json([
			'review' => $review,
			'options' => array_filter($args),
		]);
	}
}
