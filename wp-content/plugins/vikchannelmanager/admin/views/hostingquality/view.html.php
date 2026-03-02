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

class VikChannelManagerViewHostingquality extends JViewUI
{
    public function display($tpl = null)
    {
        // Set the toolbar
        $this->addToolBar();

        $app = JFactory::getApplication();
        $config = VCMFactory::getConfig();

        // load assets
        $document = JFactory::getDocument();
        $document->addStyleSheet(VBO_ADMIN_URI . 'resources/Chart.min.css', ['version' => VIKCHANNELMANAGER_SOFTWARE_VERSION]);
        $document->addScript(VBO_ADMIN_URI . 'resources/Chart.min.js', ['version' => VIKCHANNELMANAGER_SOFTWARE_VERSION]);
        VikBooking::getVboApplication()->loadCoreJS();

        // access current filters
        $filters = $app->input->get('filters', [], 'array');

        // access channel data
        $channel = VikChannelManager::getActiveModule(true);
        $channel['params'] = json_decode($channel['params'], true);

        // obtain the current Airbnb host ID
        $host_id = ($filters['host_id'] ?? null) ?: $channel['params']['user_id'] ?? null;

        if ($channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI || empty($host_id)) {
            $app->enqueueMessage('Empty Host ID for Airbnb.', 'error');
            $app->redirect("index.php?option=com_vikchannelmanager");
            $app->close();
        }

        // load all host accounts
        $host_accounts = VCMAirbnbContent::loadHostAccounts();
        if (!$host_accounts) {
            $app->enqueueMessage('No Airbnb listings mapped yet.', 'error');
            $app->redirect("index.php?option=com_vikchannelmanager");
            $app->close();
        }

        // fetch listing contents
        $listing_contents = VCMOtaListing::getInstance()->getItems([
            'idchannel' => VikChannelManagerConfig::AIRBNBAPI,
            'account_key' => $host_id,
            'param' => 'listing_content',
        ]);

        // decode OTA listing contents and cast objects to array
        $listing_contents = array_map(function($listing_content) {
            $listing_content->setting = (array) json_decode($listing_content->setting, true);
            return (array) $listing_content;
        }, $listing_contents);

        // normalize listing quality standards and trip issues
        $listing_quality_issues = VCMAirbnbContent::normalizeListingTripIssues($listing_contents, [
            'host_id' => $host_id,
            'listing_id' => $filters['listing_id'] ?? null,
            'purpose' => 'view',
        ]);

        // fetch property score
        $property_score = (array) $config->getArray('propscore_' . VikChannelManagerConfig::AIRBNBAPI . '_' . $host_id, []);

        // fetch hosting quality data
        $hosting_quality_data = (array) $config->getArray('hosting_quality_' . VikChannelManagerConfig::AIRBNBAPI . '_' . $host_id, []);
        $hosting_quality_data = VCMAirbnbContent::normalizeHostingQualityData($hosting_quality_data, [
            'host_id' => $host_id,
            'listing_id' => $filters['listing_id'] ?? null,
            'review_category' => $filters['review_category'] ?? null,
            'category_tag' => $filters['category_tag'] ?? null,
            'purpose' => 'view',
        ]);

        // build an associative list of all known listing IDs and names
        $ids_from_contents = [];
        foreach ($listing_contents as $listing_content) {
            if (!empty($listing_content['setting']['id']) && !empty($listing_content['setting']['name'])) {
                $ids_from_contents[$listing_content['setting']['id']] = $listing_content['setting']['name'];
            }
        }
        $listings_map = $ids_from_contents + ($listing_quality_issues['listings_map'] ?? []) + ($hosting_quality_data['listings_map'] ?? []) + array_combine(array_values($hosting_quality_data['info']['listing_ids'] ?? []), array_values($hosting_quality_data['info']['listing_ids'] ?? []));

        // set template values
        $this->filters = $filters;
        $this->host_accounts = $host_accounts;
        $this->listings_map = $listings_map;
        $this->listing_contents = $listing_contents;
        $this->listing_quality_issues = $listing_quality_issues;
        $this->property_score = $property_score;
        $this->hosting_quality_data = $hosting_quality_data;
        
        // Display the template
        parent::display($tpl);
    }

    /**
     * Setting the toolbar
     */
    protected function addToolBar()
    {
        //Add menu title and some buttons to the page
        JToolBarHelper::title('Airbnb - ' . JText::_('VCM_HOSTING_QUALITY'), 'vikchannelmanager');
        JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
        JToolBarHelper::spacer();
    }
}
