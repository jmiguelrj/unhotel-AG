<?php
/** 
 * @package   	VikChannelManager - Libraries
 * @subpackage 	language
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Switcher class to translate the VikChannelManager plugin general languages.
 *
 * @since 1.9
 */
class VikChannelManagerLanguageGeneral implements JLanguageHandler
{
	/**
	 * Checks if exists a translation for the given string.
	 *
	 * @param 	string 	$string  The string to translate.
	 *
	 * @return 	string 	The translated string, otherwise null.
	 */
	public function translate($string)
	{
		$result = null;
		
		/**
		 * Translations go here.
		 * @tip Use 'TRANSLATORS:' comment to attach a description of the language.
		 */

		switch ($string)
		{
			case 'VCM_AI_AUTO_RESPONDER_SUCCESS_TITLE':
				$result = __('AI Auto-Responder', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_RESPONDER_FAILURE_TITLE':
				$result = __('AI Auto-Responder Failure', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_RESPONDER_BTN_SEE_REPLY':
				$result = __('See reply', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_RESPONDER_BTN_SEE_REVIEW':
				$result = __('See review', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_RESPONDER_DRAFT_TITLE':
				$result = __('AI Auto-Responder Draft', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_RESPONDER_DRAFT_SUMMARY':
				$result = _x('The AI generated a draft for the guest message: %s.', 'The wildcard will be replaced by a message.', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_REVIEW_REPLY_SUCCESS_TITLE':
				$result = __('AI Auto-Reply Review', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_REVIEW_REPLY_ERROR_TITLE':
				$result = __('AI Auto-Reply Review Failure', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_REVIEW_GUEST_SUCCESS_TITLE':
				$result = __('AI Auto-Review Guest', 'vikchannelmanager');
				break;
			case 'VCM_AI_AUTO_REVIEW_GUEST_ERROR_TITLE':
				$result = __('AI Auto-Review Guest Failure', 'vikchannelmanager');
				break;
		}

		return $result;
	}
}
