<?php

namespace JFB\LimitResponses\Vendor\JFBCore;

trait VueComponentProps
{
    public function vue_component_props()
    {
        return array();
    }
    public final function vue_component_props_string()
    {
        $result = array();
        foreach ($this->vue_component_props() as $prop => $value) {
            $result[] = "{$prop}=\"{$value}\"";
        }
        return \implode(' ', $result);
    }
}
