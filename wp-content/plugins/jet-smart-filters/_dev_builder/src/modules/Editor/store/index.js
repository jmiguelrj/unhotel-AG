import { registerStore, useSelect, useDispatch } from '@wordpress/data';

export const STORE_NAME = 'jsf-builder-editor';

const DEFAULT_STATE = {
	name: '',
	blocks: [],
	settings: [],
	styles: [],
	history: [],
	redoStack: [],
	isLeftSidebarOpen: false,
	leftSidebarComponent: null,
	isSidebarOpen: true,
	sidebarComponent: 'general',
	itemPreviewWidth: (() => {
		const saved = localStorage.getItem('editor_itemPreviewWidth');
		return saved ? Number(saved) : 500;
	})(),
	// actions
	onSave: null,
};

const reducer = (state = DEFAULT_STATE, action) => {
	switch (action.type) {
		case 'SET_NAME':
			return {
				...state,
				name: action.name,
			};

		case 'SET_BLOCKS':
			return {
				...state,
				blocks: action.blocks,
			};

		case 'SET_SETTINGS':
			return {
				...state,
				settings: action.settings,
			};

		case 'SET_STYLES':
			return {
				...state,
				styles: action.styles,
			};

		case 'PUSH_HISTORY':
			return {
				...state,
				history: [...state.history, action.newBlocks ?? state.blocks],
				redoStack: [],
			};

		case 'HISTORY_UNDO':
			if (state.history.length === 0)
				return state;

			const previous = state.history[state.history.length - 1];

			return {
				...state,
				history: state.history.slice(0, -1),
				redoStack: [state.blocks, ...state.redoStack],
				blocks: previous,
			};

		case 'HISTORY_REDO':
			if (state.redoStack.length === 0)
				return state;

			const next = state.redoStack[0];

			return {
				...state,
				history: [...state.history, state.blocks],
				redoStack: state.redoStack.slice(1),
				blocks: next,
			};

		case 'TOGGLE_LEFT_SIDEBAR':
			return {
				...state,
				isLeftSidebarOpen: !state.isLeftSidebarOpen
			};

		case 'SET_LEFT_SIDEBAR_OPEN':
			return {
				...state,
				isLeftSidebarOpen: true,
			};

		case 'SET_LEFT_SIDEBAR_CLOSE':
			return {
				...state,
				isLeftSidebarOpen: false,
				leftSidebarComponent: null
			};

		case 'SET_LEFT_SIDEBAR_COMPONENT':
			return {
				...state,
				leftSidebarComponent: action.leftSidebarComponent
			};

		case 'TOGGLE_SIDEBAR':
			return {
				...state,
				isSidebarOpen: !state.isSidebarOpen
			};

		case 'SET_SIDEBAR_OPEN':
			return {
				...state,
				isSidebarOpen: true
			};

		case 'SET_SIDEBAR_CLOSE':
			return {
				...state,
				isSidebarOpen: false
			};

		case 'SET_SIDEBAR_COMPONENT':
			return {
				...state,
				sidebarComponent: action.sidebarComponent
			};

		case 'SET_ITEM_PREVIEW_WIDTH':
			localStorage.setItem('editor_itemPreviewWidth', action.itemPreviewWidth);
			return {
				...state,
				itemPreviewWidth: action.itemPreviewWidth
			};

		case 'SET_ON_SAVE':
			return {
				...state,
				onSave: action.onSaveСallback,
			};

		case 'CLEAR_DATA':
			return DEFAULT_STATE;

		default:
			return state;
	}
};

const selectors = {
	name: (state) => state.name,
	blocks: (state) => state.blocks,
	settings: (state) => state.settings,
	styles: (state) => state.styles,
	history: (state) => state.history,
	historyRedoStack: (state) => state.redoStack,
	canHistoryUndo: (state) => state.history.length > 0,
	canHistoryRedo: (state) => state.redoStack.length > 0,
	isLeftSidebarOpen: (state) => state.isLeftSidebarOpen,
	leftSidebarComponent: (state) => state.leftSidebarComponent,
	isSidebarOpen: (state) => state.isSidebarOpen,
	sidebarComponent: (state) => state.sidebarComponent,
	itemPreviewWidth: (state) => state.itemPreviewWidth,
	onSave: (state) => state.onSave,
};

const actions = {
	setName(name) {
		return { type: 'SET_NAME', name };
	},
	setBlocks(blocks) {
		return { type: 'SET_BLOCKS', blocks };
	},
	setSettings(settings) {
		return { type: 'SET_SETTINGS', settings };
	},
	setStyles(styles) {
		return { type: 'SET_STYLES', styles };
	},
	pushHistory(newBlocks = null) {
		return { type: 'PUSH_HISTORY', newBlocks };
	},
	historyUndo() {
		return { type: 'HISTORY_UNDO' };
	},
	historyRedo() {
		return { type: 'HISTORY_REDO' };
	},
	toggleLeftSidebar() {
		return { type: 'TOGGLE_LEFT_SIDEBAR' };
	},
	setLeftSidebarOpen() {
		return { type: 'SET_LEFT_SIDEBAR_OPEN' };
	},
	setLeftSidebarClose() {
		return { type: 'SET_LEFT_SIDEBAR_CLOSE' };
	},
	setLeftSidebarComponent(leftSidebarComponent) {
		return { type: 'SET_LEFT_SIDEBAR_COMPONENT', leftSidebarComponent };
	},
	toggleSidebar() {
		return { type: 'TOGGLE_SIDEBAR' };
	},
	setSidebarOpen() {
		return { type: 'SET_SIDEBAR_OPEN' };
	},
	setSidebarClose() {
		return { type: 'SET_SIDEBAR_CLOSE' };
	},
	setSidebarComponent(sidebarComponent) {
		return { type: 'SET_SIDEBAR_COMPONENT', sidebarComponent };
	},
	setSidebarGeneral() {
		return actions.setSidebarComponent('general');
	},
	setSidebarBlock() {
		return actions.setSidebarComponent('block');
	},
	setItemPreviewWidth(itemPreviewWidth) {
		return { type: 'SET_ITEM_PREVIEW_WIDTH', itemPreviewWidth };
	},
	setOnSave(onSaveСallback) {
		return { type: 'SET_ON_SAVE', onSaveСallback };
	},
	clearData() {
		return { type: 'CLEAR_DATA' };
	}
};
/*
How to call multiple actions from one action
return ({ dispatch }) => {
	dispatch(actions.setSidebarComponent('general'));
	dispatch(actions.setSidebarOpen());
};
*/

const storeInstance = registerStore(STORE_NAME, {
	reducer,
	actions,
	selectors,
});

export const useEditorState = (states = []) => {
	return useSelect((select) => {
		const s = select(STORE_NAME);

		return states.reduce((acc, key) => {
			if (typeof s[key] === 'function')
				acc[key] = s[key]();

			return acc;
		}, {});
	}, [states]);
};

export const useEditorActions = (actions = []) => {
	const dispatchers = useDispatch(STORE_NAME);

	return actions.reduce((acc, key) => {
		if (typeof dispatchers[key] === 'function')
			acc[key] = dispatchers[key];

		return acc;
	}, {});
};

export default {
	STORE_NAME,
	instance: storeInstance,
	useEditorState,
	useEditorActions
};
