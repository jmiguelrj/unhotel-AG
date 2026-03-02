import { Number, SortableRepeater } from 'modules/UI';
import { useListingData } from "services/data";

const LayoutSettings = () => {
	// Data
	const {
		// selectors
		listingSetting: setting,
		// actions
		setListingSetting: updateSetting,
	} = useListingData();

	// Actions
	const updateRepeaterItem = (newValue, settingKey, item, repeaterKey) => {
		item[settingKey] = newValue;
		updateSetting(repeaterKey, setting(repeaterKey));
	};

	return (
		<div className="jsf-listings-edit__content">
			<h2>Sizing</h2>
			<div className="jsf-control-sizing">
				<SortableRepeater
					items={setting('sizing')}
					labelMask="{columns|| сolumn| сolumns||} up to {width::_}px with {spacing::0}px spacing"
					buttonAddLabel="Add Size"
					defaultItemData={{
						width: '',
						columns: 1,
						spacing: 0
					}}
					onChange={(newValue) => updateSetting('sizing', newValue)}
				>
					{(item, index) =>
						<div className="jsf-sizing-repeater-item">
							<Number
								label="Max width"
								value={item.width}
								suffix="PX"
								onChange={(newValue) => updateRepeaterItem(newValue, 'width', item, 'sizing')}
							/>
							<Number
								label="Columns"
								value={item.columns}
								onChange={(newValue) => updateRepeaterItem(newValue, 'columns', item, 'sizing')}
							/>
							<Number
								label="Spacing"
								value={item.spacing}
								suffix="PX"
								onChange={(newValue) => updateRepeaterItem(newValue, 'spacing', item, 'sizing')}
							/>
						</div>
					}
				</SortableRepeater>
			</div>
		</div>
	);
};

export default LayoutSettings;