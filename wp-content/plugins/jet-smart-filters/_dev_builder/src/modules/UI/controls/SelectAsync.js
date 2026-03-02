import { useState } from '@wordpress/element';
import ReactSelectAsync from "react-select/async";

const customizeTheme = (theme) => {
	return {
		...theme,
		borderRadius: 2,
		spacing: {
			...theme.spacing,
			controlHeight: 30,
		},
		colors: {
			...theme.colors,
			primary: 'var( --wp-components-color-accent, var(--wp-admin-theme-color, #3858e9) )',
			neutral20: '#949494',
		},
	};
};

const formatGroupLabel = (group) => (
	<div style={{
		fontSize: '11px'
	}}>
		<span>{group.label}: </span>
		<span>{group.options.length}</span>
	</div>
);

const SelectAsync = ({
	value,
	onChange,
	loadOptions = (inputValue, callback) => { callback([]); },
	label = '',
	description = '',
	cacheOptions = true,
	isMulti = false,
	noOptionsMessage = (inputValue) => (inputValue.length < 1 ? 'Please enter 1 or more characters' : 'No options'),
	...props
}) => {
	const [inputValue, setInputValue] = useState('');

	return (
		<div class="jsf-control-select async-select">
			{label && <label className="jsf-control-select__label">{label}</label>}
			<ReactSelectAsync
				{...props}
				classNamePrefix="jsf-control-select"
				loadOptions={loadOptions}
				cacheOptions={cacheOptions}
				isMulti={isMulti}
				isSearchable={true}
				value={value}
				onInputChange={(value) => setInputValue(value)}
				noOptionsMessage={() => noOptionsMessage(inputValue)}
				formatGroupLabel={formatGroupLabel}
				onChange={(newValue) => onChange(newValue)}
				theme={(theme) => {
					return customizeTheme(theme);
				}}
			/>
			{description && <p className="jsf-control-select__description">{description}</p>}
		</div>
	);
};

export default SelectAsync;