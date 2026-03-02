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
 * This helper class defines common methods to deal with thread messages search.
 * 
 * IMPORTANT: this class doesn't scan the pagination of the messages. This means that
 * search tasks are performed over the preloaded messages (latest 20).
 * 
 * @since 1.9.16
 */
class VCMChatFinder implements IteratorAggregate
{
    /** @var VCMChatHandler */
    protected $chat;

    /** @var object[] */
    protected $messages = [];

    /**
     * Class construct.
     * 
     * @param  VCMChatHandler  $chat
     */
    public function __construct($chat)
    {
        $this->chat = $chat;

        $this->reset();
    }

    /**
     * Resets the search filters internally set.
     * 
     * @return  static
     */
    public function reset()
    {
        // preload messages with the first available thread
        $this->messages = $this->chat->getThreads()[0]->messages ?? [];

        return $this;
    }

    /**
     * Searches under the messages that belong to the specified thread.
     * 
     * @param   int  $threadId
     * 
     * @return  self
     */
    public function forThread(int $threadId)
    {
        // load the threads under this chat
        $threads = $this->chat->getThreads();

        // preserve only the thread matching the specified ID
        $threads = array_filter($threads, fn($thread) => $thread->id == $threadId);

        // use the thread messages if the latter exists
        $this->messages = array_shift($threads)->messages ?? [];

        return $this;
    }

    /**
     * Takes only the messages wrote by the guest.
     * 
     * @return  self
     */
    public function fromGuest()
    {
        return $this->fromRole('guest');
    }

    /**
     * Takes only the messages wrote by the host(s).
     * 
     * @return  self
     */
    public function fromHost()
    {
        return $this->fromRole(['hotel', 'host']);
    }

    /**
     * Takes only the messages wrote by the specified role(s).
     * 
     * @param   string|array  $roles
     * 
     * @return  self
     */
    public function fromRole($roles)
    {
        $this->messages = array_filter($this->messages, function($msg) use ($roles) {
            // iterate the specified roles one by one
            foreach ((array) $roles as $roleId) {
                // check whether the message has been wrote by the specified role
                if (!strcasecmp($msg->sender_type, $roleId)) {
                    // message found
                    return true;
                }
            }

            // message not found
            return false;
        });

        return $this;
    }

    /**
     * Takes only the messages with (or withour) content.
     * 
     * @param   bool  $has  Whether the content should or shouldn't be available.
     * 
     * @return  self
     */
    public function withContent(bool $has = true)
    {
        // take only the messages that has content when included or has no content when excluded
        $this->messages = array_filter($this->messages, fn($msg) => !((bool) $msg->content ^ $has));

        return $this;
    }

    /**
     * Takes only the messages with the specified property greater then the given threshold.
     * 
     * @param   string  $key        The name of the column to compare.
     * @param   mixed   $value      The threshold value to compare against.
     * @param   bool    $inclusive  True to accept equality as well.
     * 
     * @return  self
     */
    public function after(string $key, $value, bool $inclusive = true)
    {
        return $this->match($key, $value, $inclusive ? '>=' : '>');
    }

    /**
     * Takes only the messages with the specified property lower then the given threshold.
     * 
     * @param   string  $key        The name of the column to compare.
     * @param   mixed   $value      The threshold value to compare against.
     * @param   bool    $inclusive  True to accept equality as well.
     * 
     * @return  self
     */
    public function before(string $key, $value, bool $inclusive = true)
    {
        return $this->match($key, $value, $inclusive ? '<=' : '<');
    }

    /**
     * Takes only the messages with the specified property different than the given value.
     * 
     * @param   string  $key    The name of the column to compare.
     * @param   mixed   $value  The threshold value to compare against.
     * 
     * @return  self
     */
    public function not(string $key, $value)
    {
        return $this->match($key, $value, '!=');
    }

    /**
     * Takes only the messages with the specified property matching the given threshold.
     * 
     * @param   string  $key       The name of the column to compare.
     * @param   mixed   $value     The threshold value to compare.
     * @param   string  $operator  The comparison operator.
     * 
     * @return  self
     */
    public function match(string $key, $value, string $operator = '=')
    {
        $this->messages = array_filter($this->messages, function($msg) use ($key, $value, $operator) {
            $cmp = $msg->{$key} ?? null;

            switch ($operator) {
                case '>':  return $cmp > $value;
                case '<':  return $cmp < $value;
                case '>=': return $cmp >= $value;
                case '<=': return $cmp <= $value;
                case '!=': return $cmp != $value;
                default:   return $cmp == $value;
            }
        });

        return $this;
    }

    /**
     * Returns true in case there is at least an eligible message.
     * 
     * @return  bool
     */
    public function some()
    {
        return (bool) $this->messages;
    }

    /**
     * Returns the list with the matching messages.
     * 
     * @return  object[]
     */
    public function list()
    {
        return array_values($this->messages);
    }

    /**
     * Returns the latest message under the list of eligible messages.
     * 
     * @return  object
     */
    public function latest()
    {
        // clone the messages
        $list = $this->list();

        // take the first element from the array
        return array_shift($list);
    }

    /**
     * Returns the first message under the list of eligible messages.
     * 
     * @return  object
     */
    public function first()
    {
        // clone the messages
        $list = $this->list();

        // take the last element from the array
        return array_pop($list);
    }

    /**
     * @inheritDoc
     * 
     * @see IteratorAggregate::getIterator()
     */
    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->list);
    }
}
