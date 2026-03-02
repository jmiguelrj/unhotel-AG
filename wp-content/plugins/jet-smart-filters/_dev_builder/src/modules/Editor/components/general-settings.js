import { PanelBody } from '@wordpress/components';
import { closeSmall } from "@wordpress/icons";
import {
	Button,
	__experimentalInputControl as InputControl,
	__experimentalInputControlSuffixWrapper as InputControlSuffixWrapper
} from "@wordpress/components";
import { Slider } from 'modules/UI';
import { useEditorState, useEditorActions } from "../store";

const GeneralSettings = () => {
	// Data
	const {
		name,
		itemPreviewWidth
	} = useEditorState(['name', 'itemPreviewWidth']);

	const {
		setName,
		setItemPreviewWidth
	} = useEditorActions(['setName', 'setItemPreviewWidth']);

	return (
		<>
			<PanelBody
				title="General Settings"
				initialOpen={true}
			>
				<div className='jsf-editor__sidebar__control'>
					<InputControl
						__next40pxDefaultSize
						placeholder={'Item Name...'}
						label="Item Name"
						value={name}
						onChange={setName}
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
					/>
				</div>
				<div className='jsf-editor__sidebar__control'>
					<Slider
						label="Preview Width"
						min={300}
						max={1200}
						step={1}
						value={itemPreviewWidth}
						onChange={setItemPreviewWidth}
					/>
				</div>
			</PanelBody>
		</>
	);
};

export default GeneralSettings;
