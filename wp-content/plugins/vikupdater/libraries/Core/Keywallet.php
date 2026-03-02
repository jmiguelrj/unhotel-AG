<?php
/** 
 * @package     VikUpdater
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\Core;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Keywallet class.
 * 
 * @since 2.0
 */
class Keywallet implements \IteratorAggregate
{
    /**
     * The list containing all the registered keys.
     * 
     * @var object[]
     */
    protected $keys;

    /**
     * Flag used to check whether something has changed.
     * 
     * @var bool
     */
    protected $changed = false;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        // get keys from the database
        $keys = get_option('vikupdater_keywallet', null);

        // decode from JSON string
        $this->keys = $keys ? json_decode($keys) : [];
    }

    /**
     * Class destructor.
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * Adds or updates the license for the given identifier.
     * 
     * @param   string  $id
     * @param   string  $license
     * 
     * @return  void
     */
    public function save(string $id, string $license)
    {
        // check whether we already registered a license for
        // the same identifier
        $index = $this->find($id, true);

        if ($index === false)
        {
            // append to the list
            $this->keys[] = (object) [
                'id'       => $id,
                'license'  => $license,
                'created'  => (new \DateTime)->format(\DateTime::ISO8601),
                'modified' => null,
            ];
        }
        else
        {
            // update license
            $this->keys[$index]->license  = $license;
            $this->keys[$index]->modified = (new \DateTime)->format(\DateTime::ISO8601);
        }

        $this->changed = true;

        /**
         * Immediately commit the changes to prevent issues with certain server configurations
         * that seem to be unable to invoke the destruct method.
         * 
         * @since 2.0.4
         */
        $this->commit();
    }

    /**
     * Deletes the license for the given identifier.
     * 
     * @param   string  $id
     * 
     * @return  object|false  The details of the deleted license, false is missing.
     */
    public function delete(string $id)
    {
        // check whether we have a license for this identifier
        $index = $this->find($id, true);

        if ($index === false)
        {
            // record not found
            return false;
        }

        $this->changed = true;

        // remove the record from the list
        $key = array_splice($this->keys, $index, 1);

        /**
         * Immediately commit the changes to prevent issues with certain server configurations
         * that seem to be unable to invoke the destruct method.
         * 
         * @since 2.0.4
         */
        $this->commit();

        // return the details of the deleted key
        return $key;
    }

    /**
     * Looks for a license for the provided identifier.
     * 
     * @param   string  $id     The ID to look for.
     * @param   bool    $index  True to return the position of the record
     *                          rather than the record itself.
     * 
     * @return  int|object|false
     */
    public function find(string $id, bool $index = false)
    {
        foreach ($this->keys as $i => $key)
        {
            if ($key->id === $id)
            {
                return $index ? $i : clone $key;
            }
        }

        return false;
    }

    /**
     * Returns a list containing all the registered products.
     * 
     * @return  string[]
     */
    public function registered()
    {
        return array_map(function($data) {
            return $data->id;
        }, $this->keys);
    }

    /**
     * Commit the changes.
     * 
     * @return  self
     * 
     * @since   2.0.4
     */
    public function commit()
    {
        if ($this->changed)
        {
            // commit changes
            update_option('vikupdater_keywallet', json_encode($this->keys));

            $this->changed = false;
        }

        return $this;
    }

    /**
     * @inheritDoc
     * 
     * @see \IteratorAggregate
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->keys);
    }
}
