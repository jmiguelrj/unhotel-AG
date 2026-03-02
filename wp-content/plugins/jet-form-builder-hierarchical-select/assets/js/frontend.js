(
	function( $ ) {
		const { addAction, addFilter } = window?.JetPlugins?.hooks || wp.hooks;
		const actionPrefix = JetFormHrSelectSettings.action;

		const onChangeLevel = function( queryData, { levelWrapper, nextLevel, fieldWrapper } ) {
			const self = this;
			const input = levelWrapper[0]?.jfbSync;

			input ? input.loading.start() : toggle( fieldWrapper, false );

			$.ajax( {
				url: JetFormHrSelectSettings.url,
				type: 'POST',
				data: queryData,
				success: function( response ) {
					if ( response.success ) {
						mayBeConvert( 'input', nextLevel );
						insertOptions( nextLevel, response.data, fieldWrapper );

						clearNextLevelsOptions( nextLevel.next() );
					} else {
						mayBeConvert( 'select', nextLevel );
						clearNextLevelsOptions( nextLevel );

						if (! input ) {
							toggle( fieldWrapper, true );
						}
					}

					fieldWrapper.trigger( 'jet-form-builder.hr-select.on-query', [
						{
							select: self,
							wrapper: fieldWrapper,
							response,
						},
					] );

					input?.loading?.end();

					check_if_empty_child(fieldWrapper);
				},
				error: function( err ) {
					console.log( 'Error: ', err );

					fieldWrapper.trigger( 'jet-form-builder.hr-select.on-query', [
						{
							select: self,
							wrapper: fieldWrapper,
						},
					] );

					input ? input.loading.end() : toggle( fieldWrapper, true );

					check_if_empty_child(fieldWrapper);
				},
			} );
		};

		const getConvertedAttrs = function( self ) {
			return {
				name: self.attr( 'name' ),
				required: self.attr( 'required' ) || false,
				'class': self.attr( 'class' ),
				'data-field-name': self.data( 'field-name' ),
				'data-display-input': self.data( 'display-input' ),
				'data-placeholder': self.data( 'placeholder' ) || false,
				'data-taxonomy': self.data( 'taxonomy' ),
			};
		};

		const convertAttrsToString = function( attrObj ) {
			return Object.entries( attrObj ).map( ( [ attr, value ] ) => {
				if ( false === value ) {
					return '';
				}
				return `${ attr }="${ value }"`;
			} ).filter( attr => attr ).join( ' ' );
		};

		const replaceWithInput = function() {
			const self = $( this );
			const attrs = {
				type: 'text',
				...getConvertedAttrs( self ),
			};
			attrs.placeholder = self.data( 'placeholder' ) || '';

			return `<input ${ convertAttrsToString( attrs ) }>`;
		};

		const replaceWithSelect = function() {
			const self = $( this );
			const attrs = getConvertedAttrs( self );

			let placeholder = self.data( 'placeholder' );
			placeholder = placeholder ? prepareOption( { label: placeholder } ) : '';

			return `<select ${ convertAttrsToString( attrs ) }>${ placeholder }</select>`;
		};

		const replaceCallbacks = {
			select: replaceWithInput,
			input: replaceWithSelect,
		};

		/**
		 * @param selector
		 * @param nextLevel
		 * @return void
		 */
		const mayBeConvert = function( selector, nextLevel ) {
			if ( ! nextLevel.length ) {
				return;
			}

			const nextSelect = nextLevel.find( selector );
			if ( ! nextSelect.length ) {
				mayBeConvert( selector, nextLevel.next() );
				return;
			}

			const convert = nextSelect.data( 'display-input' );
			if ( ! convert ) {
				mayBeConvert( selector, nextLevel.next() );
				return;
			}

			nextSelect.replaceWith( replaceCallbacks[ selector ] );

			const input = nextLevel[0]?.jfbSync;
			input?.resetControl();

			mayBeConvert( selector, nextLevel.next() );
		};

		const prepareOption = function( props ) {
			const exclude = [ 'label', 'parent', 'data-term-level' ];

			const attrs = [];
			for ( const propsKey in props ) {
				if ( exclude.includes( propsKey ) ) {
					continue;
				}
				attrs.push( propsKey + '="' + props[ propsKey ] + '"' );
			}

			return `<option ${ attrs.join( ' ' ) }>${ props.label }</option>`;
		};

		const insertFields = function( $form, $fields ) {
			for ( const fieldName in $fields ) {
				const field = $form.find( `.jet-form-builder__field-wrap[data-field-name="${ fieldName }"]` );

				for ( const levelIndex in $fields[ fieldName ] ) {
					const level = field.find( `.jet-form-builder-hr-select-level[data-level="${ levelIndex }"]` );

					mayBeConvert( 'input', level );
					insertOptions( level, $fields[ fieldName ][ levelIndex ], field, true );
				}
			}
		};

		const insertOptions = function( nextLevel, options, fieldWrapper, suppressRecursiveTrigger = false ) {
			const select = nextLevel.find( 'select.jet-form-builder-hr-select' );

			select.find( 'option' ).remove().end().append( options.map( prepareOption ).join( '' ) );

			/**
			 * @type {InputData}
			 */
			const jfbSync = nextLevel[0]?.jfbSync;

			if ( ! select.data( 'placeholder' ) ) {
				const selectedVal = select.find( 'option:selected' ).val();

				if ( jfbSync ) {
					jfbSync.value.current = selectedVal;
				} else {
					select.val( selectedVal );
				}
			}

			if ( ! suppressRecursiveTrigger ) {
				if ( ! customTrigger( select ) && ! jfbSync ) {
					toggle( fieldWrapper, true );
				}
			} else if ( ! jfbSync ) {
				toggle( fieldWrapper, true );
			}
		};

		const check_if_empty_child = function(fieldWrapper) {
			fieldWrapper.find( 'select.jet-form-builder-hr-select' ).each( function() {
				const $select = $(this);
				const dataAttribute = $select.data('if_empty_child');
				const optionsCount = $select.find('option').length;
				const hasPlaceholder = $select.find('option').length === 1 && $select.find('option').eq(0).val() === '';

				if (dataAttribute) {
					const levelWrapper = $select.closest( '.jet-form-builder-hr-select-level' );
					if (optionsCount === 0 || hasPlaceholder) {
						if (dataAttribute === 'disable') {
							$select.prop('disabled', true);
						} else if (dataAttribute === 'hide') {
							levelWrapper.hide();
						}
					} else {
						$select.prop('disabled', false);
						levelWrapper.show();
					}
				}
			} );
		}

		const clearNextLevelsOptions = function( levelWrapper ) {
			if ( ! levelWrapper.length ) {
				return;
			}

			do {
				if ( levelWrapper.find( 'select' ).data( 'placeholder' ) ) {
					levelWrapper.find( 'option:not(:first-child)' ).remove();
				} else {
					levelWrapper.find( 'option' ).remove();
				}
				levelWrapper = levelWrapper.next();
			} while ( levelWrapper.length );
		};

		const toggle = function( fieldWrapper, enable = false ) {
			if ( enable ) {
				toggleEnabled( fieldWrapper );
			} else {
				toggleDisabled( fieldWrapper );
			}
		};

		const toggleEnabled = function( fieldWrapper ) {
			fieldWrapper.css( 'opacity', 1 );
			fieldWrapper.find( 'select.jet-form-builder-hr-select' ).each( function() {
				const self = $( this );

				self.removeAttr( 'disabled' );
			} );
		};

		const toggleDisabled = function( fieldWrapper ) {
			fieldWrapper.css( 'opacity', 0.5 );
			fieldWrapper.find( 'select.jet-form-builder-hr-select' ).each( function() {
				const self = $( this );

				self.attr( 'disabled', 'disabled' );
			} );
		};

		$( document ).on( 'change', 'select.jet-form-builder-hr-select', event => {
			const self = $( event.target );

			if ( event.target?.parentElement?.jfbSync ) {
				return;
			}

			customTrigger(self);
		} );

		const customTrigger = ( self, event ) => {
			const termValue    = self.val(),
				  levelWrapper = self.closest( '.jet-form-builder-hr-select-level' ),
				  fieldWrapper = levelWrapper.closest( '.jet-form-builder__field-wrap' ),
				  nextLevel    = levelWrapper.next();

			if ( ! nextLevel.length ) {
				check_if_empty_child(fieldWrapper);
				return false;
			}
			if ( 0 === +termValue ) {
				clearNextLevelsOptions( nextLevel );
				check_if_empty_child(fieldWrapper);
				return false;
			}

			const termID          = self.find( `option[value="${ termValue }"]` ).data( 'term-id' ),
				namespace       = self.hasClass( 'jet-form-builder__field' ) ? 'jfb' : 'jef',
				parentFieldName = fieldWrapper.data( 'field-name' );
			if ( event != null ) {
				onChangeLevel.call( self, {
					termID,
					parentFieldName,
					level: levelWrapper.data( 'level' ),
					formID: levelWrapper.closest( 'form' ).data( 'form-id' ),
					action: actionPrefix + namespace,
				}, {
					levelWrapper,
					nextLevel,
					fieldWrapper,
				} );
			}

			return true;
		};

		/**
		 * For JetFormBuilder 3.0
		 */
		addAction(
			'jet.fb.observe.after',
			'jet-form-builder/hr-select',
			/**
			 * @param observable {Observable}
			 */
			function ( observable ) {
				for ( const input of observable.getInputs() ) {
					const [ level ] = input.nodes;

					if ( ! level.classList.contains(
						'jet-form-builder-hr-select-level'
					) ) {
						continue;
					}
					input.watch(
						(event) => {
							const select = $( level ).find( 'select' );
							select.length && customTrigger( select, event );
						}
					);

					const prevInput = level?.previousElementSibling?.jfbSync;

					if ( ! prevInput ) {
						continue;
					}

					prevInput.loading.watch( () => {
						input.loading.current = prevInput.loading.current;
					} );
					check_if_empty_child($( level ));
				}
			}
		);

		const addTerms = ( $form, resolve, reject ) => {
			const inputs = $form.find( '.jet-form-builder-hr-select[type="text"]' );
			const terms = [];
			let uniquePrevSelect = false;
			const formID = $form.data( 'form-id' );

			inputs.each( function() {
				const self            = $( this ),
					  taxonomy        = self.data( 'taxonomy' ),
					  fieldWrapper    = self.closest( '.jet-form-builder__field-wrap' ),
					  parentFieldName = fieldWrapper.data( 'field-name' ),
					  currentLevel    = self.closest( '.jet-form-builder-hr-select-level' ),
					  prevLevel       = currentLevel.data( 'level' ) - 1,
					  prev            = currentLevel.siblings( `[data-level="${ prevLevel }"]` ),
					  prevSelect      = $( 'select.jet-form-builder-hr-select', prev );

				const props = {
					term: self.val(),
					taxonomy,
					parentFieldName,
					level: currentLevel.data( 'level' ),
				};

				if ( ! prevSelect.length ) {
					terms.push( props );
					return;
				}

				if ( false === uniquePrevSelect ) {
					uniquePrevSelect = prevSelect;
				}

				terms.push( {
					...props,
					args: {
						parent: prevSelect.find( `option[value="${ prevSelect.val() }"]` ).data( 'term-id' ),
					},
				} );
			} );

			if ( ! terms.length ) {
				resolve();

				return;
			}

			$.ajax( {
				url: JetFormHrSelectSettings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'jet_fb_hr_select_add_terms',
					terms,
					formID,
				},
			} ).done( response => {
				if ( response.success ) {
					insertFields( $form, response.data );
				}
				resolve();
			} ).fail( reject );
		};

		addFilter(
			'jet.fb.submit.ajax.promises',
			'jet-form-builder-hr-select',
			function( promises, $form ) {
				promises.push( new Promise( ( resolve, reject ) => {
					addTerms( $form, resolve, reject );
				} ) );

				return promises;
			},
		);

		addFilter(
			'jet.fb.submit.reload.promises',
			'jet-form-builder-hr-select',
			function( promises, event ) {
				promises.push( new Promise( ( resolve, reject ) => {
					addTerms( $( event.target ), resolve, reject );
				} ) );

				return promises;
			},
		);

	}
)( jQuery );