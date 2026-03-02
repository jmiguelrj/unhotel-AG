import PluginScheduleForm from "./render";

const { __ } = wp.i18n;

const base = {
	name: 'jf-schedule-panel',
	title: __( 'Form Schedule' )
};

const settings = {
	render: PluginScheduleForm,
	icon: null
};

export {
	base,
	settings
};