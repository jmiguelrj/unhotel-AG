<?php

namespace JFB\LimitResponses\Vendor\JFBCore;

trait ActionLocalize
{
    public abstract function get_id();
    public abstract function get_name();
    public abstract function visible_attributes_for_gateway_editor();
    public abstract function self_script_name();
    public abstract function editor_labels();
    public function editor_labels_help()
    {
        return array();
    }
    /**
     * Register custom action data
     *
     * @return array [description]
     */
    public function action_data()
    {
        return array();
    }
}
