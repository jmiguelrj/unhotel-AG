<?php
/** 
 * @package     VikBooking
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

/**
 * VikBooking plugin PRO controller.
 *
 * @since 1.7
 */
class VikBookingControllerPro extends JControllerAdmin
{
	/**
	 * PRO version downgrade task.
	 * 
	 * @return  void
	 */
	public function downgrade()
	{
		$app = JFactory::getApplication();

		// get license key
		$key = $app->input->getString('key', '');

		try
		{
			// get pro model
			$model = $this->getModel();

			if (!$model->validate($key))
			{
				// invalid request, recover the exception and propagate it
				throw $model->getError(null, $toString = false);
			}

			// downgrade to free version
			$response = $model->downgrade();

			// make sure the downgrade went fine
			if ($response === false)
			{
				// an error has occurred, recover the exception and propagate it
				throw $model->getError(null, $toString = false);
			}
		}
		catch (Exception $error)
		{
			wp_die(
				'<h1>Error</h1><p>' . $error->getMessage() . '</p>',
				$error->getCode() ?: 500
			);
		}

		wp_die('<h1>Success</h1><p>Plugin downgraded to the FREE version successfully.</p>');
	}
}
