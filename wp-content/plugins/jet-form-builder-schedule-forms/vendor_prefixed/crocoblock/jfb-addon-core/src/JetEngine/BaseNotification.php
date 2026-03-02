<?php

namespace JFB\ScheduleForms\Vendor\JFBCore\JetEngine;

use JFB\ScheduleForms\Vendor\JFBCore\VueComponentProps;
abstract class BaseNotification
{
    use VueComponentProps;
    public abstract function get_id();
    public abstract function get_name();
    public function dependence()
    {
        return \true;
    }
    public function vue_component_props()
    {
        return array(':fields' => 'availableFields');
    }
    /**
     * Fires on
     * 'jet-engine/forms/booking/notifications/fields-after' action
     *
     * @return mixed
     */
    public function notification_fields()
    {
        ?>
		<template v-if="'<?php 
        echo $this->get_id();
        ?>' === currentItem.type">
			<keep-alive>
				<jet-engine-notification-<?php 
        echo $this->get_id();
        ?> v-model="currentItem.<?php 
        echo $this->get_id();
        ?>" <?php 
        echo $this->vue_component_props_string();
        ?>/>
			</keep-alive>
		</template>
		<?php 
    }
    /**
     * Fires on
     * 'jet-engine/forms/booking/notification/<notification_id>' action
     *
     * @param $settings array
     * @param $notifications 'Jet_Engine_Booking_Forms_Notifications'
     *
     * @return mixed
     */
    public abstract function do_action(array $settings, $notifications);
}
