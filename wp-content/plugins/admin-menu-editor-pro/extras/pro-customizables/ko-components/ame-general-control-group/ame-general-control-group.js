import { createComponentConfig, KoContainerViewModel } from '../control-base.js';
import { AmeCustomizable } from '../../assets/customizable.js';
class AmeGeneralControlGroup extends KoContainerViewModel {
    constructor(params, $element) {
        super(params, $element);
        this.labelFor = params.labelFor || null;
    }
    getExpectedUiElementType() {
        return AmeCustomizable.ControlGroup;
    }
    get classes() {
        return ['ame-general-control-group', ...super.classes];
    }
}
export default createComponentConfig(AmeGeneralControlGroup, `
	<div data-bind="class: classString">
		<h4 class="ame-gcg-title">
			<!-- ko if: title -->
				<!-- ko if: labelFor -->
					<label data-bind="attr: {for: labelFor}, text: title"></label>
				<!-- /ko -->
				<!-- ko ifnot: labelFor -->
					<span data-bind="text: title"></span>
				<!-- /ko -->
			<!-- /ko -->
		</h4>
		<div class="ame-gcg-children">
		<!-- ko foreach: childComponents -->
			<!-- ko component: $data --><!-- /ko -->
		<!-- /ko -->
		</div>
	</div>	
`);
//# sourceMappingURL=ame-general-control-group.js.map