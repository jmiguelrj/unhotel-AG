<?php

namespace JFB\ScheduleForms\Vendor\JFBCore\JetEngine;

trait SetEmptyMetaBox
{
    public final function get_fields()
    {
        return array($this->get_id() => array('type' => 'html', 'html' => ''));
    }
}
