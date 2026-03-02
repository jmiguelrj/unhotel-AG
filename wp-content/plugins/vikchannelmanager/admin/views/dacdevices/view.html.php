<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewDacdevices extends JViewUI
{
    public function display($tpl = null)
    {
        // Set the toolbar
        $this->addToolBar();

        VikBooking::getVboApplication()->loadCoreJS();

        if (!class_exists('VBODooraccessFactory')) {
            $app = JFactory::getApplication();
            $app->enqueueMessage('Please update VikBooking.', 'error');
            $app->redirect('index.php?option=com_vikchannelmanager');
            $app->close();
        }

        // Display the template
        parent::display($tpl);
    }

    /**
     * Setting the toolbar
     */
    protected function addToolBar()
    {
        JToolBarHelper::title('Door Access Control - ' . JText::_('VCM_DEVICES'), 'vikchannelmanager');
        JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
        JToolBarHelper::spacer();
    }
}
