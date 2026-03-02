import { createComponentConfig, KoContainerViewModel } from '../control-base.js';
import { AmeCustomizable } from '../../assets/customizable.js';
var Section = AmeCustomizable.Section;
class AmePostboxSection extends KoContainerViewModel {
    constructor(params, $element) {
        super(params, $element);
        this.htmlId = '';
        this.descriptionAsTooltip = null;
        this.htmlId = this.id;
        //Optionally, remember the open/closed state of the section.
        if (this.id && this.registry && this.registry.has('collapsibleStateStore')) {
            const collapsibleStateStore = this.registry.get('collapsibleStateStore');
            this.isOpen = collapsibleStateStore.getOrCreateObservable(this.id, true);
        }
        else {
            this.isOpen = ko.observable(true);
        }
        if (this.description) {
            this.descriptionAsTooltip = {
                htmlContent: this.description,
                type: 'info',
                extraClasses: []
            };
        }
    }
    getExpectedUiElementType() {
        return Section;
    }
    get shouldMapMiscChildrenToPlaceholders() {
        return true;
    }
    toggle() {
        this.isOpen(!this.isOpen());
    }
}
export default createComponentConfig(AmePostboxSection, `
	<div class="ws-ame-postbox ame-postbox-section" 
		data-bind="css: { 'ws-ame-closed-postbox': !isOpen() }, attr: { id: htmlId }">
		<div class="ws-ame-postbox-header">
			<h3>
				<span data-bind="text: title"></span>
				<!-- ko if: descriptionAsTooltip -->
					<!-- ko component: {name: 'ame-tooltip-trigger', params: {tooltip: descriptionAsTooltip}} --><!-- /ko -->
				<!-- /ko -->
			</h3>
			<button class="ws-ame-postbox-toggle" data-bind="click: toggle"></button>
		</div>
		<div class="ws-ame-postbox-content" data-bind="class: childrenContainerClass">
			<!-- ko foreach: childComponents -->
				<div class="ame-postbox-section-child">
				<!-- ko component: $data --><!-- /ko -->
				</div>
			<!-- /ko -->			
		</div>
	</div>
`);
//# sourceMappingURL=ame-postbox-section.js.map