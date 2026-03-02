import {
	ComponentBindingOptions,
	createComponentConfig,
	KoContainerViewModel
} from '../control-base.js';

import {AmeCustomizable} from '../../assets/customizable.js';
import Section = AmeCustomizable.Section;
import UiElement = AmeCustomizable.UiElement;
import Control = AmeCustomizable.Control;

class AmeSiSection extends KoContainerViewModel<Section> {
	private readonly createdGroups: WeakMap<UiElement, ComponentBindingOptions>;

	constructor(params: any, $element: JQuery) {
		super(params, $element);
		this.createdGroups = new WeakMap<UiElement, ComponentBindingOptions>();
	}

	protected getExpectedUiElementType(): Constructor<AmeCustomizable.Section> | null {
		return Section;
	}

	protected mapChildToComponentBinding(child: AmeCustomizable.UiElement): ComponentBindingOptions | null {
		//Put ungrouped child controls into control groups.
		if (child instanceof Control) {
			let group = this.createdGroups.get(child);
			if (!group) {
				group = ComponentBindingOptions.fromElement(
					child.createControlGroup('ame-si-control-group')
				);
				this.createdGroups.set(child, group);
			}
			return group;
		}

		return super.mapChildToComponentBinding(child);
	}

	protected get shouldMapMiscChildrenToPlaceholders(): boolean {
		return true;
	}
}

export default createComponentConfig(AmeSiSection, `
	<div class="ame-si-section">
		<h3 data-bind="text: uiElement.title"></h3>
		<!-- ko foreach: childComponents -->
			<!-- ko component: $data --><!-- /ko -->
		<!-- /ko -->
	</div>
`);
