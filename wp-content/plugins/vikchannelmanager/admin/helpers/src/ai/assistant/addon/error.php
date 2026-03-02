<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// Restricted access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * AI assistant error message add-on.
 * 
 * @since 1.9
 */
class VCMAiAssistantAddonError extends VCMAiAssistantAddonwidget
{
    /** @var Throwable */
    protected $error;

    /**
     * Class constructor.
     * 
     * @param  Throwable  $error  The error faced.
     */
    public function __construct(Throwable $error)
    {
        $this->error = $error;
    }

    /**
     * @inheritDoc 
     */
    public function getTitle()
    {
        $title = 'Error';

        if ($code = $this->error->getCode()) {
            $title .= ' ' . $code;
        }

        return $title;
    }

    /**
     * @inheritDoc 
     */
    public function getIcon()
    {
        return VikBookingIcons::i('exclamation-circle');
    }

    /**
     * @inheritDoc 
     */
    public function getSummary()
    {
        return $this->error->getMessage();
    }
}
