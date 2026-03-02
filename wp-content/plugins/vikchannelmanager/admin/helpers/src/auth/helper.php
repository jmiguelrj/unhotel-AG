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
 * HTTP authentication helper class.
 * 
 * @since   1.8.24
 */
final class VCMAuthHelper
{
    /** 
     * Searches for a basic authentication login.
     * 
     * @param   mixed  $server  The server input container.
     * 
     * @return  mixed  The login array (with "username" and "password")
     *                 on success, false otherwise.
     */
    public static function getBasicAuth($server = null)
    {
        if (!$server)
        {
            $server = JFactory::getApplication()->input->server;
        }

        $credentials = [];

        // try to extract credentials from headers (HTTP BASIC AUTH)
        $credentials['username'] = $server->getString('PHP_AUTH_USER');
        $credentials['password'] = $server->getString('PHP_AUTH_PW');

        if (!is_null($credentials['username']) || !is_null($credentials['password']))
        {
            // credentials found
            return $credentials;
        }

        // fallback to default authorization header
        $auth = static::getAuthorizationHeader($server);

        if (!$auth)
        {
            // unable to detect basic auth
            return false;
        }

        // extract digest from header, if any
        if (!preg_match("/^Basic\s+([A-Z0-9\/+=]+)$/i", $auth, $match))
        {
            // invalid authentication method
            return false;
        }

        $digest = base64_decode(end($match));

        // auto-populate client ID and client Secret
        list($credentials['username'], $credentials['password']) = explode(':', $digest);

        return $credentials;
    }

    /** 
     * Searches for a bearer authentication login.
     * 
     * @param   mixed  $server  The server input container.
     * 
     * @return  mixed  The bearer token on success, false otherwise.
     */
    public static function getBearer($server = null)
    {
        // get authorization directive
        $auth = static::getAuthorizationHeader($server);

        if (!$auth)
        {
            // unable to detect bearer auth
            return false;
        }

        if (!preg_match("/^Bearer\s+(\S+)$/i", $auth, $matches))
        {
            // invalid bearer
            return false;
        }

        // return extracted token
        return end($matches);
    }

    /**
     * Handles the necessary headers to allow CORS and origins.
     * 
     * @since   1.9.6
     */
    public static function handleCORS()
    {
        // check whether headers have been sent
        $can_send_headers = !headers_sent();

        // handle CORS
        if ($can_send_headers && isset($_SERVER['HTTP_ORIGIN']))
        {
            /**
             * We noticed that some requests of version HTTP/2 may force via HTACCESS
             * the request header "Access-Control-Allow-Origin" to *, maybe also due
             * to some server settings. If we were to re-set the CORS allow-origin
             * header, then the App would not work and the below error about CORS would
             * be raised "Access-Control-Allow-Origin cannot contain more than one origin".
             * Therefore, we skip this header if a custom file is present on the server.
             * 
             * @since   1.8.7
             */
            $should_skip_cors_allow_origin = false;
            $skip_cors_allow_origin_paths = [
                dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vcm_skip_cors_allow_origin',
                implode(DIRECTORY_SEPARATOR, [VCM_SITE_PATH, 'helpers', 'vcm_skip_cors_allow_origin']),
            ];
            foreach ($skip_cors_allow_origin_paths as $skip_cors_allow_origin_path) {
                $should_skip_cors_allow_origin = $should_skip_cors_allow_origin || is_file($skip_cors_allow_origin_path);
            }
            if (!$should_skip_cors_allow_origin) {
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            }

            // allow credentials
            header('Access-Control-Allow-Credentials: true');
            // cache for 1 day
            header('Access-Control-Max-Age: 86400');
        }

        // Access-Control headers are received during OPTIONS requests
        if ($can_send_headers && $_SERVER['REQUEST_METHOD'] == 'OPTIONS')
        {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            {
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
            exit;
        }
    }

    /** 
     * Tries to extract the authorization from the server headers.
     * 
     * @param   mixed  $server  The server input container.
     * 
     * @return  mixed  The auth header if specified, false if missing.
     */
    protected static function getAuthorizationHeader($server = null)
    {
        if (!$server)
        {
            $server = JFactory::getApplication()->input->server;
        }

        // search on default header
        $header = $server->getString('Authorization');

        if (!is_null($header))
        {
            return trim($header);
        }

        // try with Nginx or fast CGI
        $header = $server->getString('HTTP_AUTHORIZATION');

        if (!is_null($header))
        {
            return trim($header);
        }

        // try with a different CGI option
        $header = $server->getString('REDIRECT_HTTP_AUTHORIZATION');

        if (!is_null($header))
        {
            return trim($header);
        }

        // fallback to apache request headers
        if (function_exists('apache_request_headers'))
        {
            // extract headers from request
            $requestHeaders = apache_request_headers();

            // scan all headers in search of "authorization"
            foreach ($requestHeaders as $k => $v)
            {
                if (preg_match("/^Authorization$/i", $k))
                {
                    // authorization header found, return matching value
                    return trim($v);
                }
            }
        }

        // unable to detect authorization from server headers
        return false;
    }
}