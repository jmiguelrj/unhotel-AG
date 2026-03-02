import { arrowUp, arrowDown } from '@wordpress/icons';
import Select from './Select';
import ToggleGroup from "./ToggleGroup";

const Sort = ({
	value,
	onChange,
	options = {},
	label = '',
	description = '',
	orderbyLabel = 'Order By',
	orderLabel = 'Order',
	isSearchable = false,
	...props
}) => {
	return (
		<div className='jsf-control-sort'>
			{label && <label className="jsf-control-sort__label">{label}</label>}
			<div className='jsf-control-sort__container'>
				<div className='jsf-control-sort__orderby'>
					{orderbyLabel && <label className="jsf-control-sort__orderby__label">{orderbyLabel}</label>}
					<Select
						{...props}
						options={options}
						isMulti={false}
						isSearchable={isSearchable}
						value={value.orderby}
						onChange={(newValue) => onChange({
							...value,
							orderby: newValue
						})}
					/>
				</div>
				<div className='jsf-control-sort__order'>
					<ToggleGroup
						label={orderLabel}
						value={value.order}
						onChange={(newValue) => onChange({
							...value,
							order: newValue
						})}
					>
						<ToggleGroup.OptionIcon
							value="ASC"
							icon={arrowUp}
							label="Ascending"
						/>
						<ToggleGroup.OptionIcon
							value="DESC"
							icon={arrowDown}
							label="Descending"
						/>
					</ToggleGroup>
				</div>
			</div>
			{description && <p className="jsf-control-sort__description">{description}</p>}
		</div>
	);
};

export default Sort;