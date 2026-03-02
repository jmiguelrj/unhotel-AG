<?php

namespace JFB\LimitResponses\Vendor\JFBCore\JetFormBuilder;

use JFB\LimitResponses\Vendor\JFBCore\WithBasePluginInit;
trait WithInit
{
    use WithBasePluginInit;
    public final function base_condition() : bool
    {
        return \function_exists('jet_form_builder');
    }
    public function plugin_version_compare() : string
    {
        return '1.1.0';
    }
    public function can_init() : bool
    {
        return \version_compare(JET_FORM_BUILDER_VERSION, $this->plugin_version_compare(), '>=');
    }
}
