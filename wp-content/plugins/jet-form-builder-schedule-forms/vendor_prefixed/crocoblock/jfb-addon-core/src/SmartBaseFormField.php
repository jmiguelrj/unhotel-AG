<?php

namespace JFB\ScheduleForms\Vendor\JFBCore;

trait SmartBaseFormField
{
    public $custom_field;
    public abstract function get_template();
    public abstract function render_instance();
}
