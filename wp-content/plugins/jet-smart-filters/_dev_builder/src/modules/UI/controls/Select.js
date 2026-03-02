import ReactSelect from "react-select";

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

const Select = ({
	value,
	onChange,
	options = {},
	label = '',
	description = '',
	isMulti = false,
	isSearchable = false,
	...props
}) => {
	const getValue = (value) => {
		return isMulti
			? options.filter(option => value.includes(option.value))
			: options.find(option => option.value === value);
	};

	const setValue = (newValue) => {
		if (isMulti) {
			onChange(newValue ? newValue.map(option => option.value) : []);
		} else {
			onChange(newValue ? newValue.value : '');
		}
	};

	return (
		<div class="jsf-control-select">
			{label && <label className="jsf-control-select__label">{label}</label>}
			<ReactSelect
				{...props}
				classNamePrefix="jsf-control-select"
				options={options}
				isMulti={isMulti}
				isSearchable={isSearchable}
				value={getValue(value)}
				onChange={(newValue) => setValue(newValue)}
				theme={(theme) => {
					return customizeTheme(theme);
				}}
			/>
			{description && <p className="jsf-control-select__description">{description}</p>}
		</div>
	);
};

export default Select;