import { __experimentalLibrary as Library } from '@wordpress/block-editor';
import { __experimentalListView as ListView } from '@wordpress/block-editor';
import { Button } from "@wordpress/components";
import { closeSmall } from "@wordpress/icons";
import { useEditorState, useEditorActions } from "../store";

const LeftSidebar = () => {
	const {
		isLeftSidebarOpen,
		leftSidebarComponent
	} = useEditorState(['isLeftSidebarOpen', 'leftSidebarComponent']);

	const {
		setLeftSidebarClose
	} = useEditorActions(['setLeftSidebarClose']);

	if (!isLeftSidebarOpen)
		return null;

	let LeftSidebarComponent;
	let leftSidebarLabel;

	switch (leftSidebarComponent) {
		case "Library":
			leftSidebarLabel = 'Blocks';
			LeftSidebarComponent = <Library rootClientId={null} />;

			break;
		case "ListView":
			leftSidebarLabel = 'List view';
			LeftSidebarComponent = <ListView isExpanded={true} showBlockMovers={true} />;

			break;
	}

	return (
		<div className="jsf-editor__left-sidebar">
			<div className="jsf-editor__left-sidebar__header">
				<div className="jsf-editor__left-sidebar__header__label">{leftSidebarLabel}</div>
				<Button
					icon={closeSmall}
					variant="tertiary"
					onClick={() => setLeftSidebarClose()}
				/>
			</div>
			<div className="jsf-editor__left-sidebar__container">
				{LeftSidebarComponent}
			</div>
		</div>
	);
};

export default LeftSidebar;