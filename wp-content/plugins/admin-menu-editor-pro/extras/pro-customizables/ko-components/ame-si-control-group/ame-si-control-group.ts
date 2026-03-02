import {
	createComponentConfig,
	KoContainerViewModel
} from '../control-base.js';

import {AmeCustomizable} from '../../assets/customizable.js';

class AmeSiControlGroup extends KoContainerViewModel<AmeCustomizable.ControlGroup> {
	public readonly labelFor: string|null;

	constructor(params: any, $element: JQuery) {
		super(params, $element);
		this.labelFor = params.labelFor || null;
	}

	protected getExpectedUiElementType(): Constructor<AmeCustomizable.ControlGroup> | null {
		return AmeCustomizable.ControlGroup;
	}

	get classes(): string[] {
		return ['ame-si-control-group', ...super.classes];
	}
}

export default createComponentConfig(AmeSiControlGroup, `
	<div data-bind="class: classString">
		<h4>
			<!-- ko if: title -->
				<!-- ko if: labelFor -->
					<label data-bind="attr: {for: labelFor}, text: title"></label>
				<!-- /ko -->
				<!-- ko ifnot: labelFor -->
					<span data-bind="text: title"></span>
				<!-- /ko -->
			<!-- /ko -->
		</h4>
		<div class="ame-si-control-group-children">
		<!-- ko foreach: childComponents -->
			<!-- ko component: $data --><!-- /ko -->
		<!-- /ko -->
		</div>
	</div>	
`);
