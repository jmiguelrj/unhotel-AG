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

class VikChannelManagerControllerPush extends JControllerAdmin
{
	/**
	 * Task push.subscriptions will handle a Push subscription registration.
	 */
	public function subscriptions()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		if (!VCMPushSubscription::isSupported()) {
			// this functionality cannot be supported
			VCMHttpDocument::getInstance()->close(500, 'Unsupported functionality');
		}

		$type 	   = $app->input->getString('type', 'new');
		$agent 	   = $app->input->getString('agent', $input->server->getString('HTTP_USER_AGENT', ''));
		$endpoint  = $app->input->getString('endpoint', '');
		$publicKey = $app->input->getString('publicKey', '');
		$authToken = $app->input->getString('authToken', '');
		$encoding  = $app->input->getString('encoding', 'aesgcm');

		$user   = JFactory::getUser();
		$uname  = $user->username;
		$uemail = $user->email;

		if (!$endpoint) {
			VCMHttpDocument::getInstance()->close(500, 'Missing push registration endpoint');
		}

		$subscription = [
			'type' => $type,
			'data' => [
				'agent' 	=> $agent,
				'endpoint' 	=> $endpoint,
				'publicKey' => $publicKey,
				'authToken' => $authToken,
				'encoding' 	=> $encoding,
			],
			'user' => [
				'name'  => $uname,
				'email' => $uemail,
				'lang'  => JFactory::getLanguage()->getTag(),
			],
		];

		try {
			VCMPushSubscription::getInstance($subscription)->save();
		} catch(Exception $e) {
			VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
		}

		VCMHttpDocument::getInstance()->json(['ok' => 1]);
	}

	/**
	 * Task push.delete_registration will delete one Push subscription registration.
	 */
	public function delete_registration()
	{
		$index = JFactory::getApplication()->input->getInt('index', null);

		if ($index === null) {
			VCMHttpDocument::getInstance()->close(500, 'Missing registration index');
		}

		if (!VCMPushSubscription::isSupported()) {
			// this functionality cannot be supported
			VCMHttpDocument::getInstance()->close(500, 'Unsupported functionality');
		}

		try {
			VCMPushSubscription::getInstance(['index' => $index])->delete();
		} catch(Exception $e) {
			VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
		}

		VCMHttpDocument::getInstance()->json(['ok' => 1]);
	}

	/**
	 * Task push.reload_registrations will download the active Push subscription registrations.
	 */
	public function reload_registrations()
	{
		try {
			VCMPushSubscription::getInstance()->reload();
		} catch(Exception $e) {
			VCMHttpDocument::getInstance()->close($e->getCode(), $e->getMessage());
		}

		VCMHttpDocument::getInstance()->json(['ok' => 1]);
	}
}
