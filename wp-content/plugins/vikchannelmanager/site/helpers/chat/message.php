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

/**
 * Class handler for the chat messages.
 * 
 * @since 1.6.13
 */
class VCMChatMessage extends JObject
{
	/**
	 * The chat message content.
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * The chat message attachments.
	 *
	 * @var array
	 */
	protected $attachments = [];

	/**
	 * Whether the guest messages previous than this one, on the
	 * same thread, should be marked as replied by the Hotel.
	 * 
     * @var     bool
     * 
     * @since   1.8.27
     */
    protected $mark_previous_replied = true;

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$content 	 The message content.
	 * @param 	mixed 	$attachment  The message attachment(s).
	 * @param 	mixed 	$data 		 Either an associative array or another
	 *                               object to set the initial properties of the object.
	 */
	public function __construct($content = '', $attachments = [], $data = [])
	{
		// construct parent object to keep configuration data
		parent::__construct($data);

		// setup content and attachments
		$this->setContent($content)->setAttachments($attachments);
	}

	/**
	 * Gets the chat message content.
	 *
	 * @return 	string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Sets the chat message content.
	 *
	 * @param 	string 	$content  The content.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setContent($content)
	{
		$this->content = (string) $content;

		return $this;
	}

	/**
	 * Gets the chat message attachments list.
	 *
	 * @return 	array
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	/**
	 * Sets the chat message attachments.
	 *
	 * @param 	mixed 	$data 	The attachment or a list of attachments.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @uses 	clearAttachments()
	 * @uses 	addAttachments()
	 */
	public function setAttachments($data)
	{
		// clear attachments and add the specified ones
		$this->clearAttachments()->addAttachment($data);

		return $this;
	}

	/**
	 * Adds a new chat message attachment.
	 *
	 * @param 	mixed 	$data 	The attachment or a list of attachments.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function addAttachment($data)
	{
		if (is_scalar($data))
		{
			// cast to array in case of single attachment
			$data = [$data];
		}

		// iterate the attachments and add one by one
		foreach ($data as $attachment)
		{
			$this->attachments[] = (string) $attachment;
		}

		return $this;
	}

	/**
	 * Flushes the attachments list.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function clearAttachments()
	{
		$this->attachments = [];

		return $this;
	}

	/**
	 * Removes a chat message attachment.
	 *
	 * @param 	string 	 $attachment  The attachment to remove.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	public function removeAttachment($attachment)
	{
		// get index of the attachment to remove
		$index = array_search($attachment, $this->attachments);

		// check if we found the attachment
		if ($index !== false)
		{
			// splice attachments list to remove the file
			array_splice($this->attachments, $index, 1);
		}

		return $index !== false;
	}

	/**
     * Tells or sets if previous guest messages should be marked as "replied".
     * 
     * @param   bool|null  $enabled   optionally set if marking or not.
     * 
     * @return  bool 				  the current flag status.
     * 
     * @since   1.8.27
     */
    public function markPreviousReplied($enabled = null)
    {
        if (is_bool($enabled))
        {
            $this->mark_previous_replied = $enabled;
        }

        return $this->mark_previous_replied;
    }
}
