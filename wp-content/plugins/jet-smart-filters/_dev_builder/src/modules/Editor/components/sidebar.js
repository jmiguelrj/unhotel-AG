import { Button } from '@wordpress/components';
import { closeSmall } from "@wordpress/icons";
import GeneralSettings from './general-settings';
import { useEditorState, useEditorActions } from "../store";
import { createSlotFill } from '@wordpress/components';

const { Slot: InspectorSlot, Fill: InspectorFill } = createSlotFill(
	'JSFBlockEditorSidebarInspector'
);

const Sidebar = () => {
	const {
		isSidebarOpen,
		sidebarComponent
	} = useEditorState(['isSidebarOpen', 'sidebarComponent']);

	const {
		setSidebarClose,
		setSidebarComponent
	} = useEditorActions(['setSidebarClose', 'setSidebarComponent']);

	if (!isSidebarOpen)
		return null;

	const tabs = [
		{
			name: 'general',
			title: 'Item',
			content: <GeneralSettings />,
		},
		{
			name: 'block',
			title: 'Block',
			content: <InspectorSlot bubblesVirtually />,
		},
	];

	return (
		<div className="jsf-editor__sidebar">
			<div className="jsf-editor__sidebar__header">
				<div className="jsf-editor__sidebar__tab-buttons">
					{tabs.map((tab) => (
						<button
							className={sidebarComponent === tab.name ? "active" : ""}
							data-tab={tab.name}
							onClick={() => setSidebarComponent(tab.name)}>
							{tab.title}
						</button>
					))}
				</div>
				<Button
					className="jsf-editor__sidebar__close"
					icon={closeSmall}
					variant="tertiary"
					onClick={() => { setSidebarClose(); }}
				/>
			</div>
			<div className="jsf-editor__sidebar__body">
				<div className="jsf-editor__sidebar__tab-content">
					{tabs.find((tab) => tab.name === sidebarComponent)?.content}
				</div>
			</div>
		</div>
	);
};

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
