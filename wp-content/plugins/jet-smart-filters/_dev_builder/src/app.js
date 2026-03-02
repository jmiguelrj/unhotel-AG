import "store";
import "services/data";
import "modules/Editor/store";
import "./blocks/item-blocks";
import { render } from '@wordpress/element';
import { registerCoreBlocks } from '@wordpress/block-library';
import domReady from '@wordpress/dom-ready';
import MainContainer from '@/views/layout/main-container';

// === App ===
const App = class {
	constructor() {

		this.$el = document.getElementById(window.JSFBuilderData.el_id);

		domReady(() => {
			registerCoreBlocks();
			render(
				<MainContainer />,
				this.$el
			);
		});
	}
};

export default App;
