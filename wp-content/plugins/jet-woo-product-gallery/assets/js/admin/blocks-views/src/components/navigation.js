const { __ } = wp.i18n;

const {
	PanelBody,
	SelectControl,
} = wp.components;

export default props => {

	const {
		attributes,
		setAttributes
	} = props;

	return (
		<PanelBody title={ __( 'Navigation', 'jet-woo-product-gallery' ) } initialOpen={ false }>

			<SelectControl
				label={ __( 'Navigation Type', 'jet-woo-product-gallery' ) }
				value={ attributes.navigation_type }
				options={
					[
						{
							value: 'bullets',
							label: __( 'Bullets', 'jet-woo-product-gallery' )
						},
						{
							value: 'thumbnails',
							label: __( 'Thumbnails', 'jet-woo-product-gallery' )
						}
					]
				}
				onChange={ newValue => {
					setAttributes( {
						navigation_type: newValue,
					} );
				}}
			/>
			
			<SelectControl
				label={ __( 'Navigation Position', 'jet-woo-product-gallery' ) }
				value={ attributes.navigation_position }
				options={
					[
						{
							value: 'outside',
							label: __( 'Outside', 'jet-woo-product-gallery' )
						},
						{
							value: 'inside',
							label: __( 'Inside', 'jet-woo-product-gallery' )
						}
					]
				}
				onChange={ newValue => {
					setAttributes( {
						navigation_position: newValue,
					} );
				}}
			/>

			<SelectControl
				label={ __( 'Controller Position', 'jet-woo-product-gallery' ) }
				value={ attributes.navigation_controller_position }
				options={
					[
						{
							value: 'left',
							label: __( 'Start', 'jet-woo-product-gallery' )
						},
						{
							value: 'right',
							label: __( 'End', 'jet-woo-product-gallery' )
						}
					]
				}
				onChange={ newValue => {
					setAttributes( {
						navigation_controller_position: newValue,
					} );
				}}
			/>

		</PanelBody>
	);

};