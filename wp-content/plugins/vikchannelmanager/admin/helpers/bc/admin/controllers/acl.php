<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

/**
 * VikChannelManager plugin ACL controller.
 *
 * @since 	1.0
 * @see 	JControllerAdmin
 */
class VikChannelManagerControllerAcl extends JControllerAdmin
{
	public function saveclose()
	{
		$this->save(1);
	}

	public function save($close = 0)
	{
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$dbo 	= JFactory::getDbo();

		// get return URL
		$encoded = $input->getBase64('return', '');
		$active  = $input->get('activerole', '');

		if ($encoded)
		{
			$return = base64_decode($encoded);
		}
		else
		{
			$return = '';
		}

		// make sure the user is authorised to change ACL
		if (!JFactory::getUser()->authorise('core.admin', 'com_vikchannelmanager'))
		{
			$app->redirect($return);
		}

		$data = $input->get('acl', array(), 'array');

		if ($this->model->save($data))
		{
			$app->enqueueMessage(JText::_('ACL_SAVE_SUCCESS'));
		}
		else
		{
			$app->enqueueMessage(JText::_('ACL_SAVE_ERROR'), 'error');
		}

		if (!$close)
		{
			$return = 'admin.php?option=com_vikchannelmanager&view=acl&activerole=' . $active . '&return=' . $encoded;
		}

		$app->redirect($return);
	}

	public function cancel()
	{
		$app = JFactory::getApplication();

		$return = $app->input->getBase64('return', '');

		if ($return)
		{
			$return = base64_decode($return);
		}

		$app->redirect($return);
	}
}
