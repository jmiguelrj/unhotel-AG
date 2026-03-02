import { useEffect, useRef } from '@wordpress/element';
import {
	Button,
	__experimentalInputControl as InputControl,
	__experimentalInputControlPrefixWrapper as InputControlPrefixWrapper,
	__experimentalInputControlSuffixWrapper as InputControlSuffixWrapper
} from "@wordpress/components";
import { Icon, plus, edit, closeSmall } from "@wordpress/icons";
import { NestedMenu, Sidebar, Undo, Redo } from "./icons";
import { useEditorState, useEditorActions } from "../store";
import { useSwitchLeftSidebar } from "../services/data";
import { usePageData, useItemData } from "services/data";
import { LoadingButton } from 'modules/UI';

const Toolbar = () => {
	const inputRef = useRef(null);

	// Data
	const {
		name,
		canHistoryUndo,
		canHistoryRedo,
		onSave
	} = useEditorState(['name', 'canHistoryUndo', 'canHistoryRedo', 'onSave']);

	const {
		setName,
		toggleSidebar,
		historyUndo,
		historyRedo
	} = useEditorActions(['setName', 'toggleSidebar', 'historyUndo', 'historyRedo']);

	const {
		pageData
	} = usePageData();

	const {
		isItemLoading,
		isItemSaving
	} = useItemData();

	// Actions
	const switchLeftSidebar = useSwitchLeftSidebar();

	// focus/unfocus
	useEffect(() => {
		if (pageData.slug === 'new-item' && inputRef.current)
			inputRef.current.focus();
	}, [pageData.slug]);

	return (
		<div className="jsf-editor__toolbar">
			<div className="jsf-editor__toolbar__left">
				<Button
					icon={plus}
					variant="primary"
					onClick={() => switchLeftSidebar('Library')}
				/>
				<Button
					icon={Undo}
					disabled={!canHistoryUndo}
					variant="tertiary"
					onClick={() => historyUndo()}
				/>
				<Button
					icon={Redo}
					disabled={!canHistoryRedo}
					variant="tertiary"
					onClick={() => historyRedo()}
				/>
				<Button
					icon={NestedMenu}
					variant="tertiary"
					onClick={() => switchLeftSidebar('ListView')}
				/>
			</div>
			<div className="jsf-editor__toolbar__center">
				<div id="item-name-input">
					<InputControl
						__next40pxDefaultSize
						placeholder={'Item Name...'}
						value={name}
						onChange={setName}
						prefix={<InputControlPrefixWrapper variant="icon" ><Icon icon={edit} /></InputControlPrefixWrapper>}
						suffix={name
							? <InputControlSuffixWrapper variant="control">
								<Button
									icon={closeSmall}
									label="Clear"
									size="small"
									onClick={() => setName('')}
								/>
							</InputControlSuffixWrapper>
							: null
						}
						ref={inputRef}
					/>
				</div>
			</div>
			<div className="jsf-editor__toolbar__right">
				<Button
					icon={Sidebar}
					variant="tertiary"
					onClick={() => toggleSidebar()}
				/>
				<LoadingButton
					label="Save"
					variant="primary"
					isLoading={isItemSaving}
					onClick={() => onSave()}
				/>
			</div>
		</div>
	);
};

export default Toolbar;