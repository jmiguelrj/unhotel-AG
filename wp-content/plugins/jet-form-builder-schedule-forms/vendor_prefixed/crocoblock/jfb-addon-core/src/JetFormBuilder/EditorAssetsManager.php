<?php

namespace JFB\ScheduleForms\Vendor\JFBCore\JetFormBuilder;

trait EditorAssetsManager
{
    public function assets_init()
    {
        add_action('jet-form-builder/editor-assets/before', array($this, 'before_init_editor_assets'));
    }
    public abstract function before_init_editor_assets();
}
