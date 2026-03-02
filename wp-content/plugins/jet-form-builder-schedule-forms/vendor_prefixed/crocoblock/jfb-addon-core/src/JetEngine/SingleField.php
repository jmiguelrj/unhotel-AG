<?php

namespace JFB\ScheduleForms\Vendor\JFBCore\JetEngine;

use JFB\ScheduleForms\Vendor\JFBCore\VueComponentProps;
abstract class SingleField
{
    use SmartBaseField;
    use VueComponentProps;
    /**
     * @return string
     */
    public abstract function get_name();
    /**
     * @return string
     */
    public abstract function get_title();
    public function vue_component_props()
    {
        return array(':all-settings' => 'currentItem.settings');
    }
    /**
     * Displays a template
     *
     * @return void
     */
    public function render_field_edit()
    {
        ?>
        <template v-if="'<?php 
        echo $this->get_name();
        ?>' === currentItem.settings.type">
            <keep-alive>
                <jet-engine-field-<?php 
        echo $this->get_name();
        ?>
                        v-model="currentItem.settings.<?php 
        echo $this->get_name();
        ?>" <?php 
        echo $this->vue_component_props_string();
        ?>>
            </keep-alive>
        </template>
		<?php 
    }
}
