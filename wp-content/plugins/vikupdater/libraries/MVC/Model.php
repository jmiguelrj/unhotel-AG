<?php
/** 
 * @package   	VikUpdater
 * @subpackage 	mvc (model-view-controller)
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

namespace VikWP\VikUpdater\MVC;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Generic interface used to declare a model able to fetch, save and delete records.
 * 
 * @since 2.0
 */
interface Model
{
    /**
     * Basic item loading implementation.
     *
     * @param   array|string  $pk  An optional primary key value to load the row by, or an array of fields to match.
     *
     * @return  object        The record object.
     * 
     * @throws  \Exception    In case the item does not exist.
     */
    public function getItem($pk);

    /**
     * Basic insert implementation.
     *
     * @param   array|object  $data  Either an array or an object of data to insert.
     *
     * @return  object        The saved object.
     * 
     * @throws  \Exception    In case of saving error.
     */
    public function save($data);

    /**
     * Basic delete implementation.
     *
     * @param   array|string  $ids  Either the record ID or a list of records.
     *
     * @return  bool          True on success, false otherwise.
     */
    public function delete($pk);
}
