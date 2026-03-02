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
 * AI service site controller.
 * 
 * @since 1.9.7
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

		if (!$this->isOperator())
		{
			// not an operator
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
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

		VCMHttpDocument::getInstance($app)->json([
			'question' => $question,
			'answer' => $message->answer,
			'attachments' => $message->attachments,
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

		if (!$this->isOperator())
		{
			// not an operator
			VCMHttpDocument::getInstance($app)->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
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
	 * Checks whether the user is currently logged in as operator.
	 * 
	 * @return  bool
	 */
	protected function isOperator()
	{
		static $operator = null;

		if ($operator === null) {
			// access the global operators object
			$oper_obj = VikBooking::getOperatorInstance();

			// attempt to get the current operator
			$operator = $oper_obj->getOperatorAccount();
		}

		return $operator;
	}
}
