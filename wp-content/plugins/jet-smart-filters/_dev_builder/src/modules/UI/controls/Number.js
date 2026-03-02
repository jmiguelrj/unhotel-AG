import { __experimentalNumberControl as NumberControl } from '@wordpress/components';

const Number = ({
	value,
	onChange,
	label = '',
	description = '',
	...props
}) => {
	return (
		<div className='jsf-control-number'>
			<NumberControl
				{...props}
				__next40pxDefaultSize
				label={label}
				help={description}
				spinControls="custom"
				value={value}
				onChange={(newValue) => onChange(newValue ?? '')}
			/>
		</div>
	);
};

export default Number;