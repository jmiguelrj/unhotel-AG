<?php
/** 
 * @package   	VikUpdater
 * @subpackage 	mvc (model-view-controller)
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

namespace VikWP\VikUpdater\MVC\Views;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\MVC\View;

/**
 * Licenses view.
 *
 * @since 2.0
 */
class LicensesView extends View
{
    /**
     * @inheritDoc
     */
    public function display()
    {
        /** WordPress Administration Bootstrap */
        require_once \VikWP\VikUpdater\FileSystem\Path::clean(ABSPATH . '/wp-admin/includes/admin.php');

        // prepare the variable to be used within the JS file
        $this->setScriptVars([
            'ajaxurl' => admin_url('admin-ajax.php?action=vikupdater'),
        ]);

        // get all the available products
        $this->products = $this->model->getProducts();

        // get all the registered licenses
        $this->items = $this->model->getLicenses();

        // create products option groups
        $this->productsOptGroups = [];

        foreach ($this->products as $id => $info)
        {
            if (!isset($this->productsOptGroups[$info['group']]))
            {
                $this->productsOptGroups[$info['group']] = [];
            }

            $this->productsOptGroups[$info['group']][$id] = $info['name'];
        }

        // display view
        parent::display();
    }

    /**
     * @inheritDoc
     */
    protected function help(\WP_Screen $screen)
    {
        // add licenses overview as help tab
        $screen->add_help_tab([
            'id'      => 'vikupdater_licenses_overview',
            'title'   => __('Overview'),
            'content' => '<p>' . __('Commercial plugins might accept update requests only for valid licenses. In case one of your plugin is rejecting the download of the update, you might have to specify your license here.', 'vikupdater') . '</p>'
                . '<p>' . __('<b>— For developers only</b>', 'vikupdater') . '</p>'
                . '<p>' . __('During the update of a product, in case the latter owns a license, this will be appended to the update URL as <code>&license={license}</code>.', 'vikupdater') . '</p>'
        ]);

        // add licenses management as help tab
        $screen->add_help_tab([
            'id'      => 'vikupdater_licenses_manage',
            'title'   => __('Adding licenses'),
            'content' => '<p>' . __('When registering a new license on this screen, you&#8217;ll fill in the following fields.', 'vikupdater') . '</p>'
                . '<ul>'
                . '<li>' . __('<b>Product</b> — The product for which the license should be registered. Under this list you can find all the products that support the update channel provided by VikUpdater.', 'vikupdater') . '</li>'
                . '<li>' . __('<b>License code</b> — The license code required by the server to properly distribute the update to the requestor.', 'vikupdater') . '</li>'
                . '</ul>'
                . '<p>' . __('Bear in mind that adding a new license code does not perform any validation check. You&#8217;ll figure out whether the entered license is valid only during the next update of the chosen product.', 'vikupdater') . '</p>'
        ]);

        // add help sidebar
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:') . '</strong></p>' .
            '<p><a href="' . __('https://vikwp.com/support/knowledge-base/vikupdater', 'vikupdater') . '" target="_blank">VikWP.com</a></p>'
        );
    }
}
