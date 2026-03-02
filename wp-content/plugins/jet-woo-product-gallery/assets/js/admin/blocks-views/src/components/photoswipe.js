const { __ } = wp.i18n;

const {
	MediaUpload,
	MediaUploadCheck
} = wp.blockEditor;

const {
	Button,
	PanelBody,
	SelectControl,
	ToggleControl
} = wp.components;

export default props => {

	const {
		attributes,
		setAttributes
	} = props;

	return (
		<PanelBody title={ __( 'Gallery', 'jet-woo-product-gallery' ) } initialOpen={ false }>
			<SelectControl
				label={ __( 'Trigger Type', 'jet-woo-product-gallery' ) }
				value={ attributes.gallery_trigger_type }
				options={ [
					{
						value: 'button',
						label: __( 'Button', 'jet-woo-product-gallery' ),
					},
					{
						value: 'image',
						label: __( 'Image', 'jet-woo-product-gallery' ),
					}
				] }
				onChange={ ( newValue ) => {
					setAttributes( { gallery_trigger_type: newValue } );
				} }
			/>

			{ 'button' === attributes.gallery_trigger_type &&
				<div className={ "components-base-control" }>
					<MediaUploadCheck>
						{ 0 !== Object.keys( attributes.gallery_button_icon ).length &&
						<div className={ "preview-jet-gallery-media preview-jet-gallery-media-icon" }>
							<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
								setAttributes( { gallery_button_icon: {} } );
							} }
							></Button>
							<img src={ attributes.gallery_button_icon.url } width="100%" height="auto" />
						</div>
						}
						<div className="components-base-control jet-media-control">
							<MediaUpload
								allowedTypes={ [ 'image/svg+xml' ] }
								value={ attributes.gallery_button_icon.id }
								onSelect={ ( media ) => {
									const iconData = {
										id:  media.id,
										url: media.url
									};

									setAttributes( { gallery_button_icon: iconData } );
								} }
								render={ ( { open } ) => (
									<Button
										isSecondary
										icon="edit"
										onClick={ open }
									>{ __( 'Select Button Icon', 'jet-woo-product-gallery' ) }</Button>
								) }
							/>
						</div>
					</MediaUploadCheck>

					<SelectControl
						label={ __( 'Button Position', 'jet-woo-product-gallery' ) }
						value={ attributes.gallery_button_position }
						options={ [
							{
								value: 'top-right',
								label: __( 'Top Right', 'jet-woo-product-gallery' ),
							},
							{
								value: 'bottom-right',
								label: __( 'Bottom Right', 'jet-woo-product-gallery' ),
							},
							{
								value: 'bottom-left',
								label: __( 'Bottom Left', 'jet-woo-product-gallery' ),
							},
							{
								value: 'top-left',
								label: __( 'Top Left', 'jet-woo-product-gallery' ),
							},
							{
								value: 'center',
								label: __( 'Center', 'jet-woo-product-gallery' ),
							}
						] }
						onChange={ ( newValue ) => {
							setAttributes( { gallery_button_position: newValue } );
						} }
					/>

					<ToggleControl
						label={ __( 'Show on Hover', 'jet-woo-product-gallery' ) }
						checked={ attributes.show_on_hover }
						onChange={ () => {
							setAttributes( { show_on_hover: ! attributes.show_on_hover } );
						} }
					/>
				</div>
			}

			<div className="jet-gallery-heading">Controls</div>

			<ToggleControl
				label={ __( 'Show Caption', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_caption }
				onChange={ () => {
					setAttributes( { gallery_show_caption: ! attributes.gallery_show_caption } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Full Screen', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_fullscreen }
				onChange={ () => {
					setAttributes( { gallery_show_fullscreen: ! attributes.gallery_show_fullscreen } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Zoom', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_zoom }
				onChange={ () => {
					setAttributes( { gallery_show_zoom: ! attributes.gallery_show_zoom } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Share', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_share }
				onChange={ () => {
					setAttributes( { gallery_show_share: ! attributes.gallery_show_share } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Counter', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_counter }
				onChange={ () => {
					setAttributes( { gallery_show_counter: ! attributes.gallery_show_counter } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Arrows', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_arrows }
				onChange={ () => {
					setAttributes( { gallery_show_arrows: ! attributes.gallery_show_arrows } );
				} }
			/>
		</PanelBody>
	);

};