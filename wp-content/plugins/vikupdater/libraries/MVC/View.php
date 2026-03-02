<?php
/** 
 * @package   	VikUpdater
 * @subpackage 	mvc (model-view-controller)
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

namespace VikWP\VikUpdater\MVC;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\FileSystem\File;

/**
 * The view class used by the MVC framework to render a layout.
 *
 * @since 2.0
 */
#[\AllowDynamicProperties]
abstract class View
{
    /**
     * The view name.
     * 
     * @var string
     */
    private $name;

    /**
     * The view model.
     * 
     * @var Model|null
     */
    protected $model;

    /**
     * The variables to pass to the related scripts.
     * 
     * @var array
     */
    protected $scriptVars = [];

    /**
     * Class contructor.
     * 
     * @param  Model|null  $model  The view model
     */
    public function __construct(?Model $model = null)
    {
        $this->model = $model;
    }

    /**
     * Execute and display a template script.
     *
     * @return  void
     */
    public function display()
    {
        $str = $this->loadTemplate();

        $view = $this->getName();

        // check whether we have a specific JS file to load
        if (File::exists(VIKUPDATER_BASE . '/tmpl/' . $view . '/' . $view . '.js'))
        {
            $jsId = 'vikupdater-' . $view;

            // register script always after jQuery Core (included by Wordpress)
            wp_register_script(
                $jsId,
                VIKUPDATER_URI . 'tmpl/' . $view . '/' . $view . '.js',
                [
                    'jquery'
                ],
                VIKUPDATER_VERSION,
                [
                    'in_footer' => headers_sent(),
                ]
            );
            
            wp_enqueue_script($jsId);

            if ($this->scriptVars)
            {
                // make sure PHP variables are properly passed to the view
                wp_localize_script(
                    $jsId,
                    'vikupdater' . ucfirst($view) . 'Options',
                    $this->scriptVars
                );
            }
        }

        // add support for Help tabs
        add_action('current_screen', function($screen)
        {
            // make sure $screen is a valid instance
            if (!class_exists('WP_Screen') || !$screen instanceof \WP_Screen)
            {
                // abort
                return;
            }

            // set up help tab
            $this->help($screen);
        });

        if (is_string($str))
        {
            echo $str;
        }
    }

    /**
     * Load a template file within the /tmpl folder of the view.
     * 
     * @param   string  $file  The name of the file to load.
     *
     * @return  string  The output of the template script.
     *
     * @throws  \Exception
     */
    public function loadTemplate(?string $file = null)
    {   
        // use the specified template file
        if (!$file)
        {
            $file = 'default';       
        }

        // construct view file path
        $_path = VIKUPDATER_BASE . '/tmpl/' . $this->getName() . '/' . $file . '.php';

        if (!File::exists($_path))
        {
            $err = __('View not found!', 'vikupdater');

            if (WP_DEBUG)
            {
                $err .= "\nFile: [" . $_path . "].";
            }

            throw new \DomainException($err, 404);
        }

        // start capturing output into a buffer
        ob_start();

        // include the requested template filename in the local scope
        include $_path;

        // obtain the requested template
        $output = ob_get_contents();

        // get the buffer and clear it
        ob_end_clean();

        return $output;
    }

    /**
     * Method to get the view name.
     *
     * @return  string  The name of the view.
     */
    public function getName()
    {
        if ($this->name === null)
        {
            $class = get_class($this);

            if (!preg_match("/\\\\([a-zA-Z0-9_]+)View$/", $class, $match))
            {
                throw new \RuntimeException('Cannot fetch the view name.', 500);
            }
            
            $this->name = strtolower($match[1]);    
        }
        
        return $this->name;
    }

    /**
     * Registers the script variables to be passed to the related JS file.
     * 
     * @param   array  $vars     The variables to inject.
     * @param   bool   $replace  True to add the variable to the existing ones,
     *                           false to replace them.
     * 
     * @return  self   This object to support chaining.
     */
    protected function setScriptVars(array $vars, bool $replace = false)
    {
        if ($replace)
        {
            $this->scriptVars = [];
        }

        $this->scriptVars = array_merge($this->scriptVars, $vars);

        return $this;
    }

    /**
     * This method can be overwritten in children classes to let the views
     * defining their own help tab.
     * 
     * @param   \WP_Screen  $screen
     * 
     * @return  void
     */
    protected function help(\WP_Screen $screen)
    {
        // do nothing by default
    }
}
