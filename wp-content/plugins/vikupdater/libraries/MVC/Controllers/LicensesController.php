<?php
/** 
 * @package   	VikUpdater
 * @subpackage 	mvc (model-view-controller)
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

namespace VikWP\VikUpdater\MVC\Controllers;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\MVC\Controller;

/**
 * Licenses controller.
 * 
 * @since 2.0
 */
class LicensesController extends Controller
{
    /**
     * Task used to save a license in the keywallet.
     * 
     * @return  mixed
     */
    public function save()
    {
        $this->setRedirect('tools.php?page=vikupdater');

        // validate session token
        if (!wp_verify_nonce($_REQUEST['_wpnonce'] ?? '', 'license'))
        {
            $error = __('The most recent request was denied because it had an invalid security token. Please refresh the page and try again.', 'vikupdater');

            if (!wp_doing_ajax())
            {
                \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.messages')->error($error);

                // back to the licenses view
                return false;
            }
            
            // if we are doing AJAX, the response will be properly closed
            // by the parent controller
            throw new \Exception($error, 403);
        }

        $data = [];
        $data['product'] = $_REQUEST['product'] ?? null;
        $data['license'] = $_REQUEST['license'] ?? null;

        try
        {
            // attempt to save the license
            $this->getModel()->save($data);
        }
        catch (\Exception $error)
        {
            if (!wp_doing_ajax())
            {
                \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.messages')->error($error->getMessage());

                // back to the licenses view
                return false;
            }

            // if we are doing AJAX, the response will be properly closed
            // by the parent controller
            throw $error;
        }

        if (!wp_doing_ajax())
        {
            \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.messages')->success(__('License registered successfully.', 'vikupdater'));

            // back to the licenses view
            return true;
        }

        return $data;
    }

    /**
     * Task used to delete the licenses from the keywallet.
     * 
     * @return  mixed
     */
    public function delete()
    {
        $this->setRedirect('tools.php?page=vikupdater');

        // validate session token
        if (!wp_verify_nonce($_REQUEST['_wpnonce'] ?? '', 'license'))
        {
            $error = __('The most recent request was denied because it had an invalid security token. Please refresh the page and try again.', 'vikupdater');

            if (!wp_doing_ajax())
            {
                \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.messages')->error($error);

                // back to the licenses view
                return false;
            }
            
            // if we are doing AJAX, the response will be properly closed
            // by the parent controller
            throw new \Exception($error, 403);
        }

        $ids = (array) ($_REQUEST['delete_licenses'] ?? []);

        // attempt to delete the licenses
        $deleted = $this->getModel()->delete($ids);

        if ($deleted && !wp_doing_ajax())
        {
            $message = _n('License deleted successfully.', 'Licenses deleted successfully.', count($ids), 'vikupdater');

            \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.messages')->success($message);

            // back to the licenses view
            return true;
        }

        // return to AJAX the status of the operation
        return [
            'status' => $deleted,
        ];
    }
}
