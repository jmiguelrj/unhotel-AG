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

use VikWP\VikUpdater\Psr\Container\NotFoundExceptionInterface;

/**
 * Base controller class, used to perform actions depending on the 
 * parameters set in query string.
 * 
 * @since 2.0
 */
class Controller
{
    /**
     * The default view to use when the request does not provide it.
     * 
     * @var string|null
     */
    public static $defaultView = 'licenses';

    /**
     * The controller name.
     * 
     * @var string
     */
    private $name;

    /**
     * A list of excluded methods.
     *
     * @var string[]
     */
    protected $excludedMethods = [];

    /**
     * URL for redirection.
     *
     * @var string
     */
    protected $redirect;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $reflect = new \ReflectionClass('\\VikWP\\VikUpdater\\MVC\\Controller');

        // exclude the methods defined by the base controller
        foreach ($reflect->getMethods() as $method)
        {
            $this->excludedMethods[] = $method->getName();
        }
    }

    /**
     * Typical view method for MVC based architecture.
     *
     * This function is provided as a default implementation, in most cases
     * you will need to override it in your own controllers.
     *
     * @return  self  This object to support chaining.
     */
    public function display()
    {   
        // get view name
        $action = $_REQUEST['view'] ?? static::$defaultView;

        if ($action)
        {
            // try to obtain the view related to the specified action
            $view = $this->getView($action);

            if ($view)
            {
                // display the view before to terminate
                $view->display();
            }

        }

        return $this;
    }

    /**
     * Executes a task by triggering a method in the derived class.
     *
     * @param   string  $task  The task to perform. If no matching task is found, 
     *                         the default 'display' method is executed.
     *
     * @return  mixed   The value returned by the called method.
     */
    public function execute($task)
    {
        $task = (string) $task;

        // raise an error if we are trying to call reserved methods
        if (in_array($task, $this->excludedMethods) && $task !== 'display')
        {
            // raise an error in case an exception has been thrown
            wp_die(
                '<h1>' . __('Fatal error', 'vikupdater') . '</h1>'
                . '<p>' . __('Cannot access a protected method of the controller.', 'vikupdater') . '</p>',
                501
            );
        }

        $reflect = new \ReflectionClass(get_class($this));

        // check if the $task method is callable
        if (!$reflect->hasMethod($task) || !$reflect->getMethod($task)->isPublic())
        {
            // otherwise use default 'display' method
            $task = 'display';
        }

        try
        {
            // dispatch callback
            $result = call_user_func([$this, $task]);
        }
        catch (\Exception $error)
        {
            // We need to terminate the buffer here to avoid displaying 
            // the output printed by the views into the error screen.

            while (ob_get_status())
            {
                // repeat until the buffer is empty
                ob_end_clean();
            }

            if (!wp_doing_ajax())
            {
                /**
                 * Included exception backtrace within the document in case the DEBUG is turned on.
                 *
                 * @since 10.1.35
                 */
                if (WP_DEBUG)
                {
                    $trace = '<pre style="white-space:pre-wrap;">' . $error->getTraceAsString() . '</pre>';
                }
                else
                {
                    $trace = '';
                }

                // raise an error in case an exception has been thrown
                wp_die(
                    '<h1>' . __('Fatal error', 'vikupdater') . '</h1>'
                    . '<p>' . $error->getMessage() . '</p>'
                    . $trace,
                    $error->getCode() ?: 500
                );
            }
            else
            {
                // raise a minified error for AJAX requests
                wp_die($error->getMessage(), $error->getCode() ?: 500);
            }
        }

        if (wp_doing_ajax())
        {
            global $wp;

            // if we are doing an AJAX request, encode the response in JSON format
            // register filter to attach the given header into the WP pool
            add_filter('wp_headers', function($headers)
            {
                return array_merge($headers, [
                    'Content-Type' => 'application/json',
                ]);
            });

            // send headers through WP
            $wp->send_headers();

            if (!is_string($result))
            {
                $result = json_encode($result);
            }

            echo $result;
            exit;
        }

        return $result;
    }

    /**
     * Set a URL for browser redirection.
     *
     * @param   string  $url  URL to redirect to.
     *
     * @return  self    This object to support chaining.
     */
    public function setRedirect(string $url)
    {
        // register redirection URL
        $this->redirect = $url;

        return $this;
    }

    /**
     * Redirects the browser or returns false if no redirect is set.
     *
     * @return  bool  False if no redirect exists.
     */
    public function redirect()
    {
        if ($this->redirect)
        {
            wp_redirect($this->redirect);
            exit;
        }

        return false;
    }

    /**
     * Returns the view object related to the specified name.
     *
     * @param   string  $view   The view name.
     *
     * @return  mixed   The view object if exists, otherwise false.
     */
    protected function getView($view)
    {
        try
        {
            // try to check whether a model exists for the requested view
            $model = $this->getModel($view);
        }
        catch (NotFoundExceptionInterface $error)
        {
            // model not found
            $model = null;
        }

        try
        {
            // try to instantiate the requested view
            $view = \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.view.' . $view, $model);
        }
        catch (NotFoundExceptionInterface $error)
        {
            // view not found
            $view = null;
        }

        return $view;
    }

    /**
     * Method to get the controller name.
     *
     * @return  string  The name of the controller.
     */
    public function getName()
    {
        if ($this->name === null)
        {
            $class = get_class($this);

            if (!preg_match("/\\\\([a-zA-Z0-9_]+)Controller$/", $class, $match))
            {
                throw new \RuntimeException('Cannot fetch the controller name.', 500);
            }
            
            $this->name = strtolower($match[1]);    
        }
        
        return $this->name;
    }

    /**
     * Method to get a model object.
     *
     * @param   string  $name    The model name.
     *
     * @return  mixed   Model object on success, otherwise false on failure.
     * 
     * @throws  \Exception
     */
    public function getModel(string $name = '', ...$args)
    {
        if (!$name)
        {
            // use the controller name
            $name = $this->getName();
        }

        // instantiate a new model
        return \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.model.' . $name, ...$args);
    }
}
