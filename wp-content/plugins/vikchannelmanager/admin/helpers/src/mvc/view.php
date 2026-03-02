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

/**
 * View helper class.
 * 
 * @since 1.9
 */
class VCMMvcView extends JViewUI
{
	/**
	 * The current signature of the filters.
	 *
	 * @var array
	 */
	protected $signatureId = '';

	/**
	 * This method returns the correct limit start to use.
	 * In case the filters changes, the limit is always reset.
	 *
	 * @param 	array 	 $args 	  The filters associative array.
	 * @param 	mixed 	 $id 	  An optional value used to restrict
	 * 							  the states only to a specific ID/page.
	 * @param 	string 	 $prefix  An optional prefix to use in case a page
	 * 							  supports more than one pagination (@since 1.7).
	 *
	 * @return 	integer  The list start limit.
	 *
	 * @uses 	getPoolName()
	 * @uses 	registerSignature()
	 * @uses 	checkSignature()
	 * @uses 	resetLimit()
	 */
	protected function getListLimitStart(array $args, $id = null, $prefix = '')
	{
		$app = JFactory::getApplication();

		// calculate pool name
		$name = $this->getPoolName($id);

		// get list limit
		$start = $app->getUserStateFromRequest($name . '.' . $prefix . 'limitstart', $prefix . 'limitstart', 0, 'uint');

		// register new filters signature
		$this->registerSignature($args, $id);

		if ($start > 0 && !$this->checkSignature($id))
		{
			// filters are changed, reset limit
			$this->resetLimit($start, $id, $prefix);
		}

		return $start;
	}

	/**
	 * Calculates the signature of the given filters and register it in the user state.
	 *
	 * @param 	array 	$args 	The filters associative array.
	 * @param 	mixed 	$id 	An optional value used to restrict
	 * 							the states only to a specific ID/page.
	 *
	 * @return 	string 	The old signature.
	 *
	 * @uses 	getPoolName()
	 */
	protected function registerSignature(array $args, $id = null)
	{
		$app = JFactory::getApplication();

		// calculate new signature
		$sign = array();
		
		foreach ($args as $k => $v)
		{
			if (is_null($v))
			{
				continue;
			}

			if (is_array($v))
			{
				// implode elements in the list to have a string
				$v = implode(',', $v);
			}
			
			if (strlen((string) $v))
			{
				$sign[$k] = $v;
			}
		}

		$sign = $sign ? serialize($sign) : '';

		// calculate signature name
		$name = $this->getPoolName($id);

		// get old signature because `setUserState` owns a bug for returning the old state
		$this->signatureId = $app->getUserState($name . '.signature', '');

		// register new signature
		$app->setUserState($name . '.signature', $sign);

		// return old signature
		return $this->signatureId;
	}

	/**
	 * Checks if the new signature matches the previous one.
	 *
	 * @param 	mixed 	 $id 	 An optional value used to restrict
	 * 					 		 the states only to a specific ID/page.
	 * @param 	string 	 $token  The token to check against the new one.
	 * 					  		 If not provided, the internal one will be used.
	 *
	 * @return 	boolean  True if the tokens are equal.
	 *
	 * @uses 	getPoolName()
	 */
	protected function checkSignature($id = null, $token = null)
	{
		if (!$token)
		{
			// use property in case the argument is empty
			$token = $this->signatureId;
		}

		// calculate signature name
		$name = $this->getPoolName($id);

		// get current signature
		$sign = JFactory::getApplication()->getUserState($name . '.signature', '');

		// check if the 2 signatures are equal
		return !strcasecmp($sign, $token);
	}
	
	/**
	 * Resets the list limit and save it in the user state.
	 *
	 * @param 	integer  &$start  The start list limit.
	 * @param 	mixed 	 $id 	  An optional value used to restrict
	 * 					 		  the states only to a specific ID/page.
	 * @param 	string 	 $prefix  An optional prefix to use in case a page
	 * 							  supports more than one pagination (@since 1.7).
	 *
	 * @return 	void
	 *
	 * @uses 	getPoolName();
	 */
	protected function resetLimit(&$start, $id = null, $prefix = '')
	{
		// limit start passed by reference, reset it
		$start = 0;

		// calculate limit name
		$name = $this->getPoolName($id);

		// register the new limit within the user state
		JFactory::getApplication()->setUserState($name . '.' . $prefix . 'limitstart', $start);
	}

	/**
	 * Returns the pool base name in which is stored the user state.
	 *
	 * @param 	mixed 	$id  An optional value used to restrict
	 * 						 the states only to a specific ID/page.
	 *
	 * @return 	string 	The pool name.
	 */
	public function getPoolName($id = null)
	{
		$name = 'vcm' . $this->getName();

		if (!is_null($id))
		{
			// access the user state of a specific ID/page
			$name .= "[$id]";
		}

		return $name;
	}

	/**
	 * In case the user state owns a pending record, its properties will be injected within the
	 * specified data object. This usually occurs after a saving failure.
	 *
	 * @param 	object  &$data  The data object.
	 * @param 	mixed   $key    Either the user state key or a data object/array
	 *                          (changed from string @since 1.9).
	 *
	 * @return 	void
	 */
	public function injectUserStateData(&$data, $key = '')
	{
		$app = JFactory::getApplication();

		// use data stored in user state
		$state = $app->getUserState($key ?: ($this->getName() . '.state'), []);

		if (!$data)
		{
			$data = new stdClass;
		}

		// inject data stored in user state
		foreach ($state as $property => $value)
		{
			$data->{$property} = $value;
		}
	}
}
