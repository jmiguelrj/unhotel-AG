import {createComponentConfig, KoComponentParams, KoComponentViewModel} from '../control-base.js';
import {AmeCustomizable} from '../../assets/customizable.js';
import UiElement = AmeCustomizable.UiElement;

class AmePlaceholder extends KoComponentViewModel<UiElement> {
	constructor(params: KoComponentParams, $element: JQuery) {
		super(params, $element);
	}

	protected getExpectedUiElementType(): AbstractConstructor<AmeCustomizable.UiElement> | null {
		return UiElement;
	}
}

export default createComponentConfig(AmePlaceholder, `
	<div class="ame-placeholder-component">
		<span class="ame-placeholder-component-text">
			UI element without a component. <br>
			
			<!-- ko if: id --> 
			ID: <strong data-bind="text: id"></strong>
			<!-- /ko -->
			<!-- ko if: params.label --> 
			Label: <strong data-bind="text: params.label"></strong>
			<!-- /ko -->
			<!-- ko if: params.title -->
			Title: <strong data-bind="text: params.title"></strong>
			<!-- /ko -->
		</span>
	</div>
`);