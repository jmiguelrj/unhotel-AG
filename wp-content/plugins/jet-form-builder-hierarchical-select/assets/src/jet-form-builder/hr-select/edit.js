import preview from './preview';

const {
		  AdvancedFields,
		  GeneralFields,
		  ToolBarFields,
		  FieldWrapper,
		  ActionModal,
		  RepeaterWithState,
		  PlaceholderMessage,
	  } = JetFBComponents;

const {
		  TextControl,
		  SelectControl,
		  ToggleControl,
		  PanelBody,
		  Button,
	  } = wp.components;

const {
		  InspectorControls,
		  useBlockProps,
	  } = wp.blockEditor;

const {
		  useState,
	  } = wp.element;

function HrSelectEdit( props ) {
	const blockProps = useBlockProps();

	const {
			  attributes,
			  setAttributes,
			  isSelected,
			  editProps: { uniqKey, source },
		  } = props;

	const [ levelsModal, openLevelsModal ] = useState( false );

	if ( attributes.isPreview ) {
		return <div style={ {
			width: '100%',
			display: 'flex',
			justifyContent: 'center',
		} }>
			{ preview }
		</div>;
	}

	const ifEmptyChildDescription = {
		'': 'All hierarchy levels are visible at all times.',
		'hide': 'Each next level becomes visible only when the previous level has a selected value and options for the current level exist.',
		'disable': 'All hierarchy levels are visible but disabled. Each next level becomes enabled only when the previous level has a selected value and options for the current level exist.',
	};

	return [
		<ToolBarFields
			key={ uniqKey( 'ToolBarFields' ) }
			{ ...props }
		>
			<Button
				key={ 'validate_api_key' }
				isSecondary
				onClick={ () => openLevelsModal( true ) }
			>
				{ source.label( 'modal' ) }
			</Button>
		</ToolBarFields>,
		isSelected && <InspectorControls
			key={ uniqKey( 'InspectorControls' ) }
		>
			<GeneralFields
				key={ uniqKey( 'GeneralFields' ) }
				{ ...props }
			/>
			<AdvancedFields
				key={ uniqKey( 'AdvancedFields' ) }
				{ ...props }
			/>
		</InspectorControls>,
		<div { ...blockProps } key={ uniqKey( 'viewBlock' ) }>
			<FieldWrapper
				key={ uniqKey( 'FieldWrapper' ) }
				{ ...props }
			>
				{ 0 === attributes.levels.length ? <PlaceholderMessage style={ {
					display: 'flex',
					justifyContent: 'space-between',
				} }>
					<span>{ source.label( 'warning_if_empty' ) }</span>
					<Button
						key={ 'modal_button' }
						isSecondary
						onClick={ () => openLevelsModal( true ) }
					>
						{ source.label( 'modal' ) }
					</Button>
				</PlaceholderMessage> : attributes.levels.map( ( level, index ) => <FieldWrapper
					key={ uniqKey( 'FieldWrapperForLevel_' ) + (level.name || index) }
					{ ...props }
					attributes={ {
						required: attributes.required,
						name: level.name,
						label: level.label,
						desc: level.desc,
					} }
					setAttributes={ value => {
						const levels = JSON.parse( JSON.stringify( attributes.levels ) );

						levels[ index ] = {
							...levels[ index ],
							...value,
						}

						setAttributes( { levels } );
					} }
				>
					<SelectControl
						key={ uniqKey( `select_place_holder_block_${ level.name }` ) }
						options={ [ { value: '', label: level.placeholder || '--' } ] }
						onChange={ () => {
						} }
					/>
				</FieldWrapper> ) }

			</FieldWrapper>
			{ isSelected && <div className='inside-block-options'>
				<SelectControl
					key='parent_taxonomy'
					label={ source.label( 'parent_taxonomy' ) }
					labelPosition='top'
					className='jet-required-field'
					value={ attributes.parent_taxonomy }
					onChange={ parent_taxonomy => setAttributes( { parent_taxonomy } ) }
					options={ source.taxonomies }
				/>
				<SelectControl
					key='term_value'
					label={ source.label( 'term_value' ) }
					labelPosition='top'
					className='jet-required-field'
					value={ attributes.term_value }
					onChange={ term_value => setAttributes( { term_value } ) }
					options={ source.term_value }
				/>
				{ 'meta' === attributes.term_value && <TextControl
					label={ source.label( 'term_meta_value' ) }
					placeholder={ attributes.term_meta_value }
					key={ uniqKey( 'place_holder_block' ) }
					onChange={ term_meta_value => setAttributes( { term_meta_value } ) }
					value={ attributes.term_meta_value }
				/> }
				<SelectControl
					key='if_empty_child'
					label={ source.label( 'if_empty_child' ) }
					labelPosition='top'
					className='jet-required-field'
					value={ attributes.if_empty_child }
					onChange={ if_empty_child => setAttributes( { if_empty_child } ) }
					options={ source.if_empty_child }
					help={ifEmptyChildDescription[attributes.if_empty_child]}
				/>
				<ToggleControl
					label={ source.label( 'calc_from_meta' ) }
					checked={ attributes.calc_from_meta }
					onChange={ calc_from_meta => setAttributes( { calc_from_meta } ) }
				/>
				{ attributes.calc_from_meta && <TextControl
					label={ source.label( 'term_calc_value' ) }
					help={ source.help( 'term_calc_value' ) }
					placeholder={ attributes.term_calc_value }
					onChange={ term_calc_value => setAttributes( { term_calc_value } ) }
					value={ attributes.term_calc_value }
				/> }
			</div> }
		</div>,
		levelsModal && <ActionModal
			key={ uniqKey( 'ActionModal' ) }
			title={ source.label( 'modal' ) }
			onRequestClose={ () => openLevelsModal( false ) }
			classNames={ [ 'width-60' ] }
		>
			{ ( { actionClick, onRequestClose } ) => <RepeaterWithState
				items={ attributes.levels }
				onSaveItems={ levels => setAttributes( { levels } ) }
				newItem={ source.default_item }
				onUnMount={ onRequestClose }
				isSaveAction={ actionClick }
				addNewButtonLabel={ source.label( 'add_new_level' ) }
				ItemHeading={ ( { index, currentItem } ) => ( `#${ index + 1 } ${ currentItem.name || '' }` ) }
			>
				{ ( { currentItem, changeCurrentItem, currentIndex = 0 } ) => <>
					{ ( 0 !== currentIndex && ! attributes.calc_from_meta ) && <ToggleControl
						label={ source.label( 'level_display_input' ) }
						help={ source.help( 'level_display_input' ) }
						checked={ currentItem.display_input }
						onChange={ display_input => changeCurrentItem( { display_input } ) }
					/> }
					<TextControl
						key='manual_value'
						label={ source.label( 'level_name' ) }
						className={ 'jet-required-field' }
						value={ currentItem.name }
						onChange={ name => changeCurrentItem( { name } ) }
						onFocus={ () => {
							if ( currentItem.name ) {
								return;
							}
							changeCurrentItem( { name: `${ attributes.name }_level_${ currentIndex }` } )
						} }
					/>
					<TextControl
						key='manual_label'
						label={ source.label( 'level_label' ) }
						value={ currentItem.label }
						onChange={ label => changeCurrentItem( { label } ) }
					/>
					<TextControl
						key='manual_calculate'
						label={ source.label( 'level_placeholder' ) }
						value={ currentItem.placeholder }
						onChange={ placeholder => changeCurrentItem( { placeholder } ) }
					/>
					<TextControl
						key='manual_description'
						label={ source.label( 'level_description' ) }
						value={ currentItem.desc }
						onChange={ desc => changeCurrentItem( { desc } ) }
					/>
				</> }
			</RepeaterWithState> }
		</ActionModal>,
	];
}

export default HrSelectEdit;