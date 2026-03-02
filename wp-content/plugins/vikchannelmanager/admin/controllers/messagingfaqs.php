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
 * AI messaging topics admin controller.
 * 
 * @since 1.9
 */
class VikChannelManagerControllerMessagingfaqs extends JControllerAdmin
{
	/**
	 * Delete the selected messaging topics.
	 * 
	 * @return  bool
	 */
	public function delete()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// always go back to the messaging faqs list
		$this->cancel();

		if (!JSession::checkToken())
		{
			// missing CSRF-proof token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			return false;
		}

		$cid = $app->input->get('cid', [], 'uint');

		// check user permissions
		if (!$user->authorise('core.delete', 'com_vikchannelmanager'))
		{
			// not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}

		// delete the selected topics
		(new VCMChatTopics)->delete($cid);
		return true;
	}

	/**
	 * Returns to the messaging faqs list.
	 * 
	 * @return  void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikchannelmanager&view=messagingfaqs');
	}
}
