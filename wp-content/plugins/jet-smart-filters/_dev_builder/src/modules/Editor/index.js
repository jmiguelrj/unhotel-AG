import '@wordpress/format-library';

import { BlockEditorProvider } from '@wordpress/block-editor';
import { FullscreenMode } from '@wordpress/interface';
import { ShortcutProvider } from '@wordpress/keyboard-shortcuts';
import { SlotFillProvider } from '@wordpress/components';
import { StrictMode, useEffect } from '@wordpress/element';
import { useEditorState, useEditorActions } from "./store";
import { getEditorSettings, useSetBlocksWithHistory } from "./services/data";
import { areArraysEqual } from "./services/helper";
import EditorContent from './components/editor-content';

const Editor = ({
	name: _name = '',
	blocks: _blocks = [],
	onSave: _onSave = () => { },
	settings: _settings = {}
}) => {
	// Data
	const { name, blocks, settings, styles } = useEditorState(['name', 'blocks', 'settings', 'styles']);
	const { setName, setBlocks, setOnSave, clearData } = useEditorActions(['setName', 'setBlocks', 'setOnSave', 'clearData']);

	const editorSettings = getEditorSettings(_settings);

	// Actions
	const setBlocksWithHistory = useSetBlocksWithHistory();

	// Hooks
	useEffect(() => {
		return () => {
			// On unmount
			clearData();
		};
	}, []);

	useEffect(() => {
		setName(_name);
	}, [_name]);

	useEffect(() => {
		if (areArraysEqual(_blocks, blocks))
			return;

		setBlocks(_blocks);
	}, [_blocks]);

	useEffect(() => {
		setOnSave(() => {
			_onSave({
				name,
				content: blocks,
				settings,
				styles
			});
		});
	}, [_onSave, name, blocks, settings, styles]);

	return (
		<StrictMode>
			<ShortcutProvider>
				<FullscreenMode isActive={false} />
				<SlotFillProvider>
					<BlockEditorProvider
						value={blocks}
						onInput={setBlocksWithHistory}
						onChange={setBlocksWithHistory}
						settings={editorSettings}
					>
						<EditorContent />
					</BlockEditorProvider>
				</SlotFillProvider>
			</ShortcutProvider>
		</StrictMode >
	);
};

export default Editor;