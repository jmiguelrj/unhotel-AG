<?php

namespace JFB\ScheduleForms\Vendor\JFBCore\JetFormBuilder;

abstract class BlocksParserManager
{
    use WithInit;
    public abstract function parsers() : array;
    public function on_plugin_init()
    {
        add_filter('jet-form-builder/parsers-request/register', function ($tabs) {
            $tabs = \array_merge($tabs, $this->parsers());
            return $tabs;
        });
    }
}
