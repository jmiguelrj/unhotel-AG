import { ToggleControl } from '@wordpress/components';

const Toggle = ({
	value,
	onChange,
	label = '',
	description = '',
	...props
}) => {
	return (
		<div className='jsf-control-toggle'>
			<ToggleControl
				{...props}
				__nextHasNoMarginBottom
				label={label}
				help={description}
				checked={value}
				onChange={(newValue) => onChange(newValue)}
			/>
		</div>
	);
};

export default Toggle;