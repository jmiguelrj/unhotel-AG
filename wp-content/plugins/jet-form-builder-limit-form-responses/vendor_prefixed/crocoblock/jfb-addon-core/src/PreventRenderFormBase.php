<?php

namespace JFB\LimitResponses\Vendor\JFBCore;

trait PreventRenderFormBase
{
    public function __construct()
    {
        add_filter($this->action_name(), array($this, 'prevent_render_form'), $this->priority(), 2);
    }
    public function priority()
    {
        return 100;
    }
    public abstract function render_form($form_id, $attrs, $prev_content);
    public abstract function form_id_key();
    public abstract function action_name();
    public final function prevent_render_form($content, $attrs)
    {
        $form_id = isset($attrs[$this->form_id_key()]) ? absint($attrs[$this->form_id_key()]) : 0;
        unset($attrs[$this->form_id_key()]);
        return $this->render_form($form_id, $attrs, $content);
    }
}
