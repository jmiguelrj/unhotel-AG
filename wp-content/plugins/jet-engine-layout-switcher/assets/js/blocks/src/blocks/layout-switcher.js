import {clone} from "../../../../../../jet-engine/assets/js/admin/blocks-views/src/utils/utility";

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;

const {
	MediaUpload,
	MediaUploadCheck
} = wp.editor;

const {
	InspectorControls,
} = wp.blockEditor;

const {
	IconButton,
	TextControl,
	SelectControl,
	ToggleControl,
	PanelBody,
	Disabled,
	Path,
	SVG,
	Notice,
	__experimentalText: Text
} = wp.components;

const {
	serverSideRender: ServerSideRender
} = wp;

const { select } = wp.data;

const {
	RepeaterControl,
} = window.JetEngineBlocksComponents;

const Icon = <SVG xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="none">
	<Path fillRule="evenodd" clipRule="evenodd" d="M6 9C6 8.44772 6.44772 8 7 8H11C11.5523 8 12 8.44772 12 9V13C12 13.5523 11.5523 14 11 14H7C6.44772 14 6 13.5523 6 13V9ZM8 12V10H10V12H8Z" fill="currentColor"/>
	<Path d="M14 11C14 10.4477 14.4477 10 15 10H33C33.5523 10 34 10.4477 34 11C34 11.5523 33.5523 12 33 12H15C14.4477 12 14 11.5523 14 11Z" fill="currentColor"/>
	<Path d="M14 20C14 19.4477 14.4477 19 15 19H33C33.5523 19 34 19.4477 34 20C34 20.5523 33.5523 21 33 21H15C14.4477 21 14 20.5523 14 20Z" fill="currentColor"/>
	<Path fillRule="evenodd" clipRule="evenodd" d="M7 17C6.44772 17 6 17.4477 6 18V22C6 22.5523 6.44772 23 7 23H11C11.5523 23 12 22.5523 12 22V18C12 17.4477 11.5523 17 11 17H7ZM8 19V21H10V19H8Z" fill="currentColor"/>
	<Path fillRule="evenodd" clipRule="evenodd" d="M6 27C6 26.4477 6.44772 26 7 26H11C11.5523 26 12 26.4477 12 27V31C12 31.5523 11.5523 32 11 32H7C6.44772 32 6 31.5523 6 31V27ZM8 30V28H10V30H8Z" fill="currentColor"/>
	<Path fillRule="evenodd" clipRule="evenodd" d="M36 35C35.4477 35 35 35.4477 35 36V42C35 42.5523 35.4477 43 36 43H42C42.5523 43 43 42.5523 43 42V36C43 35.4477 42.5523 35 42 35H36ZM37 37V41H41V37H37Z" fill="currentColor"/>
	<Path fillRule="evenodd" clipRule="evenodd" d="M36 45C35.4477 45 35 45.4477 35 46V52C35 52.5523 35.4477 53 36 53H42C42.5523 53 43 52.5523 43 52V46C43 45.4477 42.5523 45 42 45H36ZM37 47V51H41V47H37Z" fill="currentColor"/>
	<Path fillRule="evenodd" clipRule="evenodd" d="M45 36C45 35.4477 45.4477 35 46 35H52C52.5523 35 53 35.4477 53 36V42C53 42.5523 52.5523 43 52 43H46C45.4477 43 45 42.5523 45 42V36ZM51 41H47V37H51V41Z" fill="currentColor"/>
	<Path fillRule="evenodd" clipRule="evenodd" d="M46 45C45.4477 45 45 45.4477 45 46V52C45 52.5523 45.4477 53 46 53H52C52.5523 53 53 52.5523 53 52V46C53 45.4477 52.5523 45 52 45H46ZM47 47V51H51V47H47Z" fill="currentColor"/>
	<Path fillRule="evenodd" clipRule="evenodd" d="M4 40C1.79086 40 0 38.2091 0 36V4C0 1.79086 1.79086 0 4 0H36C38.2091 0 40 1.79086 40 4V24H60C62.2091 24 64 25.7909 64 28V60C64 62.2091 62.2091 64 60 64H28C25.7909 64 24 62.2091 24 60V40H4ZM4 2H36C37.1046 2 38 2.89543 38 4V24H28C25.7909 24 24 25.7909 24 28H15C14.4477 28 14 28.4477 14 29C14 29.5523 14.4477 30 15 30H24V38H4C2.89543 38 2 37.1046 2 36V4C2 2.89543 2.89543 2 4 2ZM28 26H60C61.1046 26 62 26.8954 62 28V60C62 61.1046 61.1046 62 60 62H28C26.8954 62 26 61.1046 26 60V28C26 26.8954 26.8954 26 28 26Z" fill="currentColor"/>
	<Path d="M46.8853 10.4603L49.2535 12.8284C49.644 13.2189 49.644 13.8521 49.2535 14.2426C48.863 14.6332 48.2298 14.6332 47.8393 14.2426L44.3037 10.7071C43.9132 10.3166 44.0108 9.5 44.0108 9.5C44.0196 9.25627 44.1177 9.01448 44.3038 8.82843L47.8393 5.29289C48.2298 4.90237 48.863 4.90237 49.2535 5.29289C49.644 5.68342 49.644 6.31658 49.2535 6.70711L47.4749 8.48575C47.8956 8.51161 48.3335 8.55317 48.7716 8.61056C50.0287 8.77525 51.4066 9.08528 52.4472 9.60557C53.5789 10.1714 54.748 11.0435 55.6182 12.5664C56.4794 14.0734 57 16.134 57 19C57 19.5523 56.5523 20 56 20C55.4477 20 55 19.5523 55 19C55 16.366 54.5206 14.6766 53.8818 13.5586C53.252 12.4565 52.4211 11.8286 51.5528 11.3944C50.8045 11.0203 49.6825 10.747 48.5118 10.5936C47.9374 10.5184 47.3801 10.4756 46.8853 10.4603Z" fill="currentColor"/>
	<Path d="M14.7465 51.1716L17.1147 53.5397C16.6199 53.5244 16.0626 53.4816 15.4882 53.4064C14.3175 53.253 13.1955 52.9797 12.4472 52.6056C11.5789 52.1714 10.748 51.5435 10.1182 50.4414C9.47944 49.3234 9 47.634 9 45C9 44.4477 8.55229 44 8 44C7.44772 44 7 44.4477 7 45C7 47.866 7.52057 49.9266 8.38176 51.4336C9.25196 52.9565 10.4211 53.8286 11.5528 54.3944C12.5934 54.9147 13.9713 55.2247 15.2284 55.3894C15.6665 55.4468 16.1044 55.4884 16.5251 55.5142L14.7465 57.2929C14.356 57.6834 14.356 58.3166 14.7465 58.7071C15.137 59.0976 15.7702 59.0976 16.1607 58.7071L19.6962 55.1716C19.8823 54.9855 19.9804 54.7437 19.9892 54.5C19.9892 54.5 20.0868 53.6834 19.6963 53.2929L16.1607 49.7574C15.7702 49.3668 15.137 49.3668 14.7465 49.7574C14.356 50.1479 14.356 50.7811 14.7465 51.1716Z" fill="currentColor"/>
</SVG>;

const blockAttributes = window.JetEngineListingData.atts.layoutSwitcher;

registerBlockType( 'jet-engine/layout-switcher', {
	title: __( 'Layout Switcher' ),
	icon: Icon,
	category: 'layout',
	attributes: blockAttributes,
	className: 'jet-layout-switcher',
	edit: class extends wp.element.Component {
		render() {
			const props          = this.props;
			const attributes     = props.attributes;
			const columnsOptions = [
				{
					value: '',
					label: 'Default'
				},
				{
					value: 1,
					label: 1
				},
				{
					value: 2,
					label: 2
				},
				{
					value: 3,
					label: 3
				},
				{
					value: 4,
					label: 4
				},
				{
					value: 5,
					label: 5
				},
				{
					value: 6,
					label: 6
				},
				{
					value: 7,
					label: 7
				},
				{
					value: 8,
					label: 8
				},
				{
					value: 9,
					label: 9
				},
				{
					value: 10,
					label: 10
				},
				{
					value: 'auto',
					label: 'Auto'
				}
			];

			let listingOptions = window.JetEngineListingData.listingOptions;

			if ( '' === listingOptions[0]['value'] ) {
				listingOptions[0]['label'] = 'Default';
			}

			const getListingsBlocksOptions = function() {
				const blocks = select( 'core/block-editor' ).getBlocks();
				const listingsBlocks = findRecursiveBlocksByName( blocks, 'jet-engine/listing-grid' );
				let result = [
					{
						value: '',
						label: 'Select...'
					},
				];

				let count = 1;

				listingsBlocks.forEach( block => {

					let label = 'Listing Grid #' + count;

					if ( block.attributes?.metadata?.name ) {
						label += ' - ' + block.attributes.metadata.name;
					}

					if ( block.attributes?._element_id ) {
						label += ' - #' + block.attributes._element_id;
					}

					result.push( {
						value: block.attributes._block_id,
						label: label,
					} );

					count++;
				} );

				return result;
			};

			const findRecursiveBlocksByName = function( blocks, name ) {
				let result = [];

				blocks.forEach( block => {
					if ( name === block.name ) {
						result.push( block );
					} else if ( block.innerBlocks.length ) {
						let innerBlocks = findRecursiveBlocksByName( block.innerBlocks, name );
						result = [...result, ...innerBlocks];
					}
				} );

				return result;
			};

			const updateItem = function( item, key, value, prop ) {

				prop = prop || 'layouts';

				let items = clone( props.attributes[ prop ] );
				const index = getItemIndex( item, prop );
				let currentItem = items[ index ];

				if ( ! currentItem ) {
					return;
				}

				if ( 'object' === typeof key && ! Array.isArray( key ) && null !== key ) {
					currentItem = { ...currentItem, ...key };
				} else {

					if ( 'is_default_layout' === key ) {
						for ( var i = 0; i < items.length; i++ ) {
							if ( items[ i ].is_default_layout ) {
								items[ i ].is_default_layout = false;
							}
						}
					}

					currentItem[ key ] = value;
				}

				items[ index ] = currentItem;

				props.setAttributes( { [ prop ]: items } );

			};

			const getItemIndex = function( item, prop ) {

				prop = prop || 'layouts';

				return props.attributes[ prop ].findIndex( _item => {
					return _item == item;
				} );
			};

			return [
				props.isSelected && (
					<InspectorControls
						key={ 'inspector' }
					>
						<PanelBody title={ __( 'General' ) }>
							<SelectControl
								label={ __( 'Select a Listing Grid block' ) }
								value={ attributes.widget_id }
								options={ getListingsBlocksOptions() }
								onChange={ newValue => {
									props.setAttributes( { widget_id: newValue } );
								} }
							/>
							<Text variant='title' as='h3'>{ __( 'Layouts' ) }</Text>
							<RepeaterControl
								data={ attributes.layouts }
								default={ {
									label: '',
									slug: '',
									is_default_layout: false,
								} }
								onChange={ newData => {
									props.setAttributes( { layouts: newData } );
								} }
							>
								{
									( item ) =>
										<div>
											<TextControl
												type="text"
												label={ __( 'Label' ) }
												value={ item.label }
												onChange={ newValue => {
													updateItem( item, 'label', newValue )
												} }
											/>
											<TextControl
												type="text"
												label={ __( 'Slug' ) }
												help={ __( 'Should contain only Latin letters, numbers, `-` or `_` chars' ) }
												value={ item.slug }
												onChange={ newValue => {
													updateItem( item, 'slug', newValue )
												} }
											/>
											<div className="jet-media-control components-base-control">
												<div className="components-base-control__label">{ __( 'Icon' ) }</div>
												<div className="jet-media-control__preview">
													{ item.icon_url && <img src={ item.icon_url } width="50"/> }
												</div>
												<MediaUploadCheck>
													<MediaUpload
														onSelect={ media => {
															updateItem( item, {
																'icon': media.id,
																'icon_url': media.url
															} );
														} }
														type="image"
														allowedTypes={ [ 'image/svg+xml' ] }
														value={ item.icon }
														render={ ( { open } ) => (
															<IconButton
																isSecondary
																icon="edit"
																onClick={ open }
															>{ __( 'Select Icon' ) }</IconButton>
														) }
													/>
													{ item.icon &&
														<IconButton
															onClick={ () => {
																updateItem( item, {
																	'icon': '',
																	'icon_url': '',
																} );
															} }
															isLink
															isDestructive
														>{ __( 'Remove Icon' ) }</IconButton>
													}
												</MediaUploadCheck>
											</div>
											<Text variant='title' as='h3'>{ __( 'Settings' ) }</Text>
											<ToggleControl
												label={ __( 'Is Default Layout' ) }
												checked={ item.is_default_layout }
												onChange={ () => {
													updateItem( item, 'is_default_layout', ! item.is_default_layout )
												} }
											/>
											{ !item.is_default_layout &&
												<SelectControl
													label={ __( 'Listing' ) }
													value={ item.lisitng_id }
													options={ listingOptions }
													onChange={ newValue => {
														updateItem( item, 'lisitng_id', newValue )
													}}
												/>
											}
											{ !item.is_default_layout &&
												<SelectControl
													label={ __( 'Columns Number' ) }
													value={ item.columns }
													options={ columnsOptions }
													onChange={ newValue => {
														updateItem( item, 'columns', newValue )
													}}
												/>
											}
											{ !item.is_default_layout && 'auto' === item.columns &&
												<TextControl
													type="number"
													label={ __( 'Column Min Width' ) }
													value={ item.column_min_width }
													min="0"
													max="1200"
													onChange={ newValue => {
														updateItem( item, 'column_min_width', newValue )
													} }
												/>
											}
											{ !item.is_default_layout && item.columns &&
												<SelectControl
													label={ __( 'Columns Number (Tablet)' ) }
													value={ item.columns_tablet }
													options={ columnsOptions }
													onChange={ newValue => {
														updateItem( item, 'columns_tablet', newValue )
													}}
												/>
											}
											{ !item.is_default_layout && item.columns && 'auto' === item.columns_tablet &&
												<TextControl
													type="number"
													label={ __( 'Column Min Width (Tablet)' ) }
													value={ item.column_min_width_tablet }
													min="0"
													max="1200"
													onChange={ newValue => {
														updateItem( item, 'column_min_width_tablet', newValue )
													} }
												/>
											}
											{ !item.is_default_layout && item.columns &&
												<SelectControl
													label={ __( 'Columns Number (Mobile)' ) }
													value={ item.columns_mobile }
													options={ columnsOptions }
													onChange={ newValue => {
														updateItem( item, 'columns_mobile', newValue )
													}}
												/>
											}
											{ !item.is_default_layout && item.columns && 'auto' === item.columns_mobile &&
												<TextControl
													type="number"
													label={ __( 'Column Min Width (Mobile)' ) }
													value={ item.column_min_width_mobile }
													min="0"
													max="1200"
													onChange={ newValue => {
														updateItem( item, 'column_min_width_mobile', newValue )
													} }
												/>
											}
											{ !item.is_default_layout && ( 'auto' === item.columns || 'auto' === item.columns_tablet || 'auto' === item.columns_mobile ) &&
												<Notice status='warning' isDismissible={ false }>{ __( 'Note: The Masonry Listing combined with Auto Columns might cause unexpected results and break the layout.' ) }</Notice>
											}
										</div>
								}
							</RepeaterControl>
							<ToggleControl
								label={ __( 'Show Labels' ) }
								checked={ attributes.show_label }
								onChange={ () => {
									props.setAttributes( { show_label: ! attributes.show_label } );
								} }
							/>
						</PanelBody>
					</InspectorControls>
				),
				<Disabled key={ 'block_render' }>
					<ServerSideRender
						block="jet-engine/layout-switcher"
						attributes={ attributes }
					/>
				</Disabled>
			];
		}
	},
	save: props => {
		return null;
	}
} );
