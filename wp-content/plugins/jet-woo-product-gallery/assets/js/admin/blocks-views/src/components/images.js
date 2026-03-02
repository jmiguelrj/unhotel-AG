const { __ } = wp.i18n;

const {
	PanelBody,
	ToggleControl,
	RangeControl,
	TextControl,
	SelectControl
} = wp.components;

export default props => {

	const imageSizes = window.JetGalleryBlocksData.imageSizes;

	const {
		attributes,
		setAttributes
	} = props;

	return (
		<PanelBody title={ __( 'Images', 'jet-woo-product-gallery' ) } initialOpen={ false }>
			<SelectControl
				label={ __( 'Image Size', 'jet-woo-product-gallery' ) }
				value={ attributes.image_size }
				options={ imageSizes }
				onChange={ newValue => {
					setAttributes( {
						image_size: newValue,
					} );
				}}
			/>

			{ undefined !== attributes.thumbs_image_size &&
				<SelectControl
					label={ __( 'Thumbnails Size', 'jet-woo-product-gallery' ) }
					value={ attributes.thumbs_image_size }
					options={ imageSizes }
					onChange={ newValue => {
						setAttributes( {
							thumbs_image_size: newValue,
						} );
					}}
				/>
			}

			{ undefined !== attributes.columns &&
				<TextControl
					type="number"
					label={ __( 'Columns Number', 'jet-woo-product-gallery' ) }
					value={ attributes.columns }
					min={ `1` }
					max={ `6` }
					onChange={ newValue => {
						setAttributes( { columns: Number( newValue ) } );
					} }
				/>
			}

			{ undefined !== attributes.columns_tablet &&
				<TextControl
					type="number"
					label={ __( 'Columns Number (Tablet)', 'jet-woo-product-gallery' ) }
					value={ attributes.columns_tablet }
					min={ `1` }
					max={ `6` }
					onChange={ newValue => {
						setAttributes( { columns_tablet: Number( newValue ) } );
					} }
				/>
			}

			{ undefined !== attributes.columns_mobile &&
				<TextControl
					type="number"
					label={ __( 'Columns Number (Mobile)', 'jet-woo-product-gallery' ) }
					value={ attributes.columns_mobile }
					min={ `1` }
					max={ `6` }
					onChange={ newValue => {
						setAttributes( { columns_mobile: Number( newValue ) } );
					} }
				/>
			}

			{ undefined !== attributes.primary_image &&
				<ToggleControl
					label={ __( 'Primary Gallery Image', 'jet-woo-product-gallery' ) }
					checked={ attributes.primary_image === 'yes' }
					onChange={ newValue => {
						setAttributes( {
							primary_image: newValue ? 'yes' : '',
						} );
					} }
				/>
			}

			{ undefined !== attributes.grid_items_count &&
				<RangeControl
					label={ __( 'Grid Items Count', 'jet-woo-product-gallery' ) }
					help = { __( 'Number of images to display in the grid. Set to 0 or -1 to show all images.', 'jet-woo-product-gallery' ) }
					value={ attributes.grid_items_count }
					onChange={ newValue => {
						setAttributes( {
							grid_items_count: Number( newValue ),
						} );
					} }
					min={ -1 }
					max={ 50 }
				/>
			}

			{ undefined !== attributes.grid_overlay_text &&
				<TextControl
					label={ __( 'Overlay Text', 'jet-woo-product-gallery' ) }
					value={ attributes.grid_overlay_text }
					onChange={ newValue => {
						setAttributes( {
							grid_overlay_text: newValue,
						} );
					} }
				/>
			}

		</PanelBody>
	);

};