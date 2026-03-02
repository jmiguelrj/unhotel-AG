import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { useRef, useEffect } from '@wordpress/element';

const Input = ({
	value,
	onChange,
	label = '',
	description = '',
	isFocus = false,
	...props
}) => {
	const inputRef = useRef(null);

	// focus/unfocus
	useEffect(() => {
		if (inputRef.current) {
			if (isFocus) {
				inputRef.current.focus();
			} else {
				inputRef.current.blur();
			}
		}
	}, [isFocus]);

	return (
		<div className='jsf-control-input'>
			<InputControl
				{...props}
				__next40pxDefaultSize
				label={label}
				help={description}
				value={value}
				onChange={(newValue) => onChange(newValue ?? '')}
				ref={inputRef}
			/>
		</div>
	);
};

export default Input;