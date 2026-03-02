import { useDispatch } from '@wordpress/data';
import { BlockBreadcrumb, BlockEditorKeyboardShortcuts, BlockList, BlockTools, WritingFlow, ObserveTyping, BlockInspector } from '@wordpress/block-editor';
import { Popover } from '@wordpress/components';
import { useBlockFocusEvents, useEditorContainerStyles } from "../services/data";
import { useEditorActions } from "../store";
import Toolbar from './toolbar';
import LeftSidebar from './left-sidebar';
import Sidebar from './sidebar';

const EditorContent = () => {
	const { selectBlock } = useDispatch('core/block-editor');

	const handleClickCapture = (event) => {
		if (event.target.classList.contains('jsf-editor__canvas'))
			selectBlock(null);

		/* const isClickOutside = !event.target.closest('.wp-block, .block-editor-block-toolbar, .popover-slot, .jsf-editor__sidebar');
		if (isClickOutside)
			selectBlock(null); */
	};

	const {
		setSidebarGeneral,
		setSidebarBlock
	} = useEditorActions(['setSidebarGeneral', 'setSidebarBlock']);

	useBlockFocusEvents({
		onSelect: (clientId, block) => {
			setSidebarBlock();
		},
		onDeselect: (clientId) => {
			setSidebarGeneral();
		}
	});

	const containerStyles = useEditorContainerStyles();

	return (
		<div className="jsf-editor">
			<Toolbar />
			<div className="jsf-editor__body">
				<LeftSidebar />
				<div
					className="jsf-editor__canvas"
					onClickCapture={handleClickCapture}
				>
					<BlockEditorKeyboardShortcuts.Register />
					<div className="jsf-editor__blocks" style={containerStyles}>
						<BlockTools>
							<WritingFlow className="editor-styles-wrapper">
								<ObserveTyping>
									<BlockList />
								</ObserveTyping>
							</WritingFlow>
						</BlockTools>
					</div>
					<BlockBreadcrumb />
				</div>
				<Sidebar.InspectorFill>
					<BlockInspector />
				</Sidebar.InspectorFill>
				<Sidebar />
			</div>
			<Popover.Slot />
		</div>
	);
};

export default EditorContent;