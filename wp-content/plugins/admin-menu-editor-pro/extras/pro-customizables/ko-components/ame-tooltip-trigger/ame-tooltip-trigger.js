import { createComponentConfig, KoStandaloneControl } from '../control-base.js';
class AmeTooltipTrigger extends KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        if ((typeof params.tooltip === 'undefined') || (params.tooltip === null)) {
            throw new Error('The AmeTooltipTrigger component requires the "tooltip" parameter.');
        }
        this.tooltip = params.tooltip;
        this.text = this.tooltip.htmlContent || '';
        //Convert newlines to <br> for better formatting in tooltips.
        //Some other parts of the plugin rely on the implicit conversion of newlines to <br>
        //that qTip2 apparently does when reading the title attribute, but this component
        //doesn't use the title attribute.
        this.text = this.text.replace(/\n/g, '<br>');
    }
}
export default createComponentConfig(AmeTooltipTrigger, `
	<a class="ws_tooltip_trigger" 
		data-bind="ameTooltip: {text: text}"><span class="dashicons dashicons-info"></span></a>
`);
//# sourceMappingURL=ame-tooltip-trigger.js.map