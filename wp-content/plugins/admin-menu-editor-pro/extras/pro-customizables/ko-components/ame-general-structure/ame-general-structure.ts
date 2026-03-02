import {createRendererComponentConfig, KoRendererViewModel} from '../control-base.js';

class AmeGeneralStructure extends KoRendererViewModel {
	constructor(params: any, $element: JQuery) {
		super(params, $element);
	}
}

export default createRendererComponentConfig(AmeGeneralStructure, `
	<!-- ko foreach: structure.children -->
		<!-- ko if: $data.component -->
			<!-- ko component: { name: $data.component, params: $data.getComponentParams() } --><!-- /ko -->					
		<!-- /ko -->
		<!-- ko ifnot: $data.component -->
			<!-- ko component: { name: 'ame-placeholder', params: $data.getComponentParams() } --><!-- /ko -->
		<!-- /ko -->
	<!-- /ko -->
`);
