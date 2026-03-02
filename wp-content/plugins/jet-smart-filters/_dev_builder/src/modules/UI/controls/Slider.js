import { RangeControl, Flex, FlexItem } from '@wordpress/components';

const Slider = ({
	value,
	min = 0,
	max = 100,
	step = 1,
	onChange,
	label = '',
	description = '',
	...props
}) => {
	return (
		<RangeControl
			{...props}
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			label={label}
			help={description}
			min={min}
			max={max}
			step={step}
			value={value}
			onChange={(newValue) => onChange(newValue)}
		/>
	);
};

export default Slider;