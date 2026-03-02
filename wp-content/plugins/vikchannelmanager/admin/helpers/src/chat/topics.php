<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helper class to handle auto-responding features to OTA guest messages.
 * 
 * @since 1.9
 */
class VCMChatTopics
{
	/** @var JDatabaseDriver */
	protected $db;

	/**
	 * Class constructor.
	 * 
	 * @param   mixed  $db  The database driver instance.
	 */
	public function __construct($db = null)
	{
		$this->db = $db ?: JFactory::getDbo();
	}

	/**
	 * Returns the record matching the provided topic, if any.
	 * 
	 * @param   string  $topic  The topic keywords.
	 * 
	 * @return  object|null
	 */
	public function getTopic(string $topic)
	{
		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->qn('#__vikchannelmanager_messaging_topics'))
			->where($this->db->qn('topic') . ' = ' . $this->db->q($topic));

		$this->db->setQuery($query, 0, 1);
		return $this->db->loadObject();
	}

	/**
	 * Returns a list containing the most frequent messaging topics.
	 * 
	 * @param   int  $limit   The maximum number of topics to take.
	 * @param   int  $offset  The pagination offset.
	 * 
	 * @return  object[]
	 */
	public function getMostFrequentTopics(?int $limit = null, int $offset = 0)
	{
		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->qn('#__vikchannelmanager_messaging_topics'))
			->order($this->db->qn('hits') . ' DESC');

		$this->db->setQuery($query, $offset, $limit);
		return $this->db->loadObjectList();
	}

	/**
	 * Hit the provided topic(s).
	 * 
	 * @param   array|string  $topics    Either a list of topics or a string.
	 * @param   int|null      $threadId  An optional thread ID as reference.
	 * 
	 * @return  void
	 */
	public function hit($topics, ?int $threadId = null)
	{
		foreach ((array) $topics as $topic)
		{
			// fetch topic record
			$record = $this->getTopic($topic);

			$now = JFactory::getDate()->toSql();

			if ($record)
			{
				// increase hits by one
				$record->hits++;
				$record->modified = $now;
				$record->idthread = $threadId;
				$this->db->updateObject('#__vikchannelmanager_messaging_topics', $record, 'id');
			}
			else
			{
				// create a new record
				$record = new stdClass;
				$record->topic = $topic;
				$record->created = $now;
				$record->idthread = $threadId;
				$this->db->insertObject('#__vikchannelmanager_messaging_topics', $record, 'id');
			}
		}
	}

	/**
	 * Deletes the provided topic(s).
	 * 
	 * @param   mixed  Either an array, a topic string or a topic ID.
	 * 
	 * @return  bool   True on success, false otherwise.
	 */
	public function delete($topics)
	{
		$deleted = false;

		foreach ((array) $topics as $topic)
		{
			$query = $this->db->getQuery(true)
				->delete($this->db->qn('#__vikchannelmanager_messaging_topics'));

			if (is_numeric($topic))
			{
				$query->where($this->db->qn('id') . ' = ' . (int) $topic);
			}
			else
			{
				$query->where($this->db->qn('topic') . ' = ' . $db->q($topic));
			}

			$this->db->setQuery($query);
			$this->db->execute();
			$deleted = $deleted || $this->db->getAffectedRows();
		}

		return $deleted;
	}
}
