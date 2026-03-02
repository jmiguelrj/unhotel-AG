<?php

namespace JFB\ScheduleForms\Vendor\JFBCore;

use JFB\ScheduleForms\Vendor\JFBCore\Exceptions\BaseHandlerException;
trait SmartNotificationActionTrait
{
    protected $_requestData;
    protected $_instance;
    protected $_settings;
    public abstract function run_action();
    public abstract function provider_slug();
    public abstract function setRequest($key, $value);
    public abstract function hasGateway();
    public abstract function getFormId();
    public abstract function isAjax();
    public abstract function addResponse($response_arr);
    public function getInstance()
    {
        return $this->_instance;
    }
    public function getSettingsWithGlobal()
    {
        throw new BaseHandlerException('failed', __METHOD__);
    }
    public function getGlobalOptionName()
    {
        return $this->get_id();
    }
    public function parseDynamicException($type, $message) : string
    {
        return $message;
    }
    public function getRequest($key = '', $ifNotExist = \false)
    {
        if (!$key) {
            return $this->_requestData;
        }
        return isset($this->_requestData[$key]) ? $this->_requestData[$key] : $ifNotExist;
    }
    public function issetRequest($key)
    {
        return isset($this->_requestData[$key]);
    }
    public function getSettings($key = '', $ifNotExist = \false)
    {
        if (!$key) {
            return $this->_settings;
        }
        return isset($this->_settings[$key]) ? $this->_settings[$key] : $ifNotExist;
    }
    /**
     * @param $message
     * @param mixed ...$additional
     *
     * @throws BaseHandlerException
     */
    public function dynamicError($message, ...$additional)
    {
        throw new BaseHandlerException($message, 'error', ...$additional);
    }
    /**
     * @param $message
     * @param mixed ...$additional
     *
     * @throws BaseHandlerException
     */
    public function dynamicSuccess($message, ...$additional)
    {
        throw new BaseHandlerException($message, 'success', ...$additional);
    }
    /**
     * @param $status
     * @param mixed ...$additional
     *
     * @throws BaseHandlerException
     */
    public function error($status, ...$additional)
    {
        throw new BaseHandlerException($status, '', ...$additional);
    }
    public function debug(...$additional)
    {
        new BaseHandlerException('debug', '', ...$additional);
    }
    protected function applyFilters($suffix, ...$params)
    {
        return apply_filters("jet-form-builder/action/{$this->get_id()}/{$suffix}", ...$params);
    }
}
