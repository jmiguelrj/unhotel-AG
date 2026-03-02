import * as schedule from "./plugins/schedule"

const {
	addFilter
} = wp.hooks;

addFilter( 'jet.fb.register.plugin.jf-actions-panel.after', 'jet-form-builder', plugins => {
	plugins.push( schedule );

	return plugins;
} );