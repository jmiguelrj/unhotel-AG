import { createComponentConfig } from '../control-base.js';
import { AmeDescriptionComponent } from '../ame-description/ame-description.js';
/**
 * A simple component that displays the description of a UI element.
 *
 * Like AmeSiblingDescription, but intended to be rendered inside
 * the parent control or container, not as a sibling.
 */
class AmeNestedDescription extends AmeDescriptionComponent {
    constructor(params, $element) {
        super(params, $element);
        this.includeLineBreak = true;
        if (typeof params.includeLineBreak !== 'undefined') {
            this.includeLineBreak = params.includeLineBreak;
        }
    }
}
export default createComponentConfig(AmeNestedDescription, `
	<!-- ko if: includeLineBreak --><br><!-- /ko --><span class="description" data-bind="html: description"></span>	
`);
//# sourceMappingURL=ame-nested-description.js.map