<?php

namespace JFB\ScheduleForms\Vendor\JFBCore\JetFormBuilder;

use JFB\ScheduleForms\Vendor\JFBCore\RegisterMetaManager;
abstract class PluginManager
{
    use EditorAssetsManager;
    use RegisterMetaManager;
    use WithInit;
    public function on_plugin_init()
    {
        $this->meta_manager_init();
        $this->assets_init();
    }
}
