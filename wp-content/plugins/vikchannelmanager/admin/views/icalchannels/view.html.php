<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewIcalchannels extends JViewUI {
    
    function display($tpl = null) {
        $mainframe = JFactory::getApplication();
        $lim = $mainframe->getUserStateFromRequest("com_vikchannelmanager.limit", 'limit', $mainframe->get('list_limit'), 'int');
        $lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
        $navbut = '';

        $ids = VikRequest::getVar('cid', array(0));
        $channels = array();
        $editchannel = array();
            
        // Set the toolbar
        $this->addToolBar();
        
        VCM::load_css_js();
        
        $dbo = JFactory::getDbo();
        
        $q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikchannelmanager_ical_channels` ORDER BY `id` ASC";
        $dbo->setQuery($q, $lim0, $lim);
        $dbo->execute();
        if( $dbo->getNumRows() > 0 ) {
            $channels = $dbo->loadAssocList();
            $dbo->setQuery('SELECT FOUND_ROWS();');
            jimport('joomla.html.pagination');
            $pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
            $navbut="<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
        }

        if (!empty($ids[0])) {
            // fetch record to modify
            $q = "SELECT * FROM `#__vikchannelmanager_ical_channels` WHERE `id`=" . (int)$ids[0];
            $dbo->setQuery($q, 0, 1);
            $dbo->execute();
            $editchannel = $dbo->getNumRows() ? $dbo->loadAssoc() : $editchannel;
        }
        
        $this->channels = $channels;
        $this->editchannel = $editchannel;
        $this->navbut = $navbut;
        
        // Display the template (default.php)
        parent::display($tpl);
        
    }

    /**
     * Setting the toolbar
     */
    protected function addToolBar() {
        // Add menu title and some buttons to the page
        
        JToolBarHelper::title(JText::_('VCMMENUTITLEICALCHANNELS'), 'vikchannelmanager');

        $user = JFactory::getUser();

        if ($user->authorise('core.edit', 'com_vikchannelmanager') || $user->authorise('core.create', 'com_vikchannelmanager')) {
            JToolBarHelper::apply('saveIcalChannel', JText::_('SAVE'));
        }
        if ($user->authorise('core.delete', 'com_vikchannelmanager')) {
            JToolBarHelper::deleteList(JText::_('VCMREMOVECONFIRM'), 'deleteIcalChannels');
        }
    }
}
