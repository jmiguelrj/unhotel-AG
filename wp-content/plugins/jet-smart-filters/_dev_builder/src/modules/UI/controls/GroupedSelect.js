import { BaseControl } from '@wordpress/components';

const GroupedSelectControl = ({
	label,
	value,
	options = [],
	description = '',
	onChange
}) => {
	return (
		<BaseControl
			__nextHasNoMarginBottom
			className="jsf-control-grouped-select"
			label={label}
			help={description}
		>
			<select
				className="jsf-control-grouped-select__input"
				value={value}
				onChange={(event) => onChange(event.target.value)}
			>
				{options.map((group) => (
					<optgroup key={group.label} label={group.label}>
						{group.options.map((opt) => (
							<option key={opt.value} value={opt.value}>
								{opt.label}
							</option>
						))}
					</optgroup>
				))}
			</select>
		</BaseControl>
	);
};

export default GroupedSelectControl;