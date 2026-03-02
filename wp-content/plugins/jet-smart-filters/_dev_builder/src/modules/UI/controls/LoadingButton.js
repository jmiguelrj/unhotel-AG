import { Button, Spinner } from '@wordpress/components';

const LoadingButton = ({
	label,
	isLoading,
	...props
}) => {
	const buttonClassName = [
		'jsf-control-loading-button',
		isLoading ? 'jsf-loading' : '',
		props.className || '',
	]
		.filter(Boolean)
		.join(' ');

	return (
		<Button
			{...props}
			className={buttonClassName}
			disabled={isLoading}
		>
			{isLoading && (
				<Spinner className='jsf-control-loading-button__spinner' />
			)}
			<span className='jsf-control-loading-button__label' >
				{label}
			</span>
		</Button>
	);
};

export default LoadingButton;