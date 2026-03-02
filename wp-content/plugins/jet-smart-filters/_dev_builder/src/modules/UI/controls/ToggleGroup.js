import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	__experimentalToggleGroupControlOptionIcon as ToggleGroupControlOptionIcon,
} from '@wordpress/components';

const ToggleGroup = ({
	value,
	onChange,
	label = '',
	description = '',
	children,
	...props
}) => {
	return (
		<div className='jsf-control-toggle-group'>
			<ToggleGroupControl
				{...props}
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={label}
				value={value}
				help={description}
				isBlock
				onChange={(newValue) => onChange(newValue)}
			>
				{children}
			</ToggleGroupControl>
		</div>
	);
};

ToggleGroup.Option = ToggleGroupControlOption;
ToggleGroup.OptionIcon = ToggleGroupControlOptionIcon;

export default ToggleGroup;