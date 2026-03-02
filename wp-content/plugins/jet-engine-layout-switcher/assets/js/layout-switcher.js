( function( $ ) {

	"use strict";

	const JetEngineLayoutSwitcher = {

		activeClass: 'je-layout-switcher__btn--active',
		loadingClass: 'je-layout-switcher--loading',

		elementorInit: function() {
			window.elementorFrontend.hooks.addAction(
				'frontend/element_ready/jet-engine-layout-switcher.default',
				JetEngineLayoutSwitcher.initLayoutSwitcher
			);
		},

		commonInit: function() {
			$( document ).on(
				'click.JetEngineLayoutSwitcher',
				'.je-layout-switcher__btn',
				JetEngineLayoutSwitcher.switchLayout
			);
		},

		initLayoutSwitcher: function( $scope ) {
			const $activeBtn = $scope.find( '.' + JetEngineLayoutSwitcher.activeClass );

			if ( ! $activeBtn.length || $activeBtn.data( 'is-default' ) ) {
				return;
			}

			const $wrapper = $scope.find( '.je-layout-switcher' );
			const widgetId = $wrapper.data( 'widget-id' );
			const activeSettings = $activeBtn.data( 'settings' );

			JetEngineLayoutSwitcher.updateElementSettings( widgetId, activeSettings );
		},

		switchLayout: function( event ) {
			const $btn = $( event.currentTarget );
			const activeClass = JetEngineLayoutSwitcher.activeClass;

			if ( $btn.hasClass( activeClass ) ) {
				return;
			}

			const $wrapper = $btn.closest( '.je-layout-switcher' );
			const widgetId = $wrapper.data( 'widget-id' );
			const view     = $wrapper.data( 'view' );

			const defaultListing = $wrapper.data( 'default-listing' );

			const slug = $btn.data( 'slug' );
			const settings = $btn.data( 'settings' );
			const isDefaultLayout = $btn.data( 'is-default' ) || false;

			const $activeBtn = $btn.closest( '.je-layout-switcher__group' ).find( '.' + activeClass );
			const activeSettings = $activeBtn.data( 'settings' );

			// Update cookie
			$.ajax( {
				url: window.JetEngineSettings.ajaxlisting,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'jet_engine_ajax',
					jet_engine_action: 'jet_engine_switch_layout',
					widget_id: widgetId,
					layout: slug,
					view: view,
				},
			} );

			// Update elementor element `data-settings` attribute
			if ( 'elementor' === view ) {
				JetEngineLayoutSwitcher.updateElementSettings( widgetId, settings );
			}

			const $container = $( JetEngineLayoutSwitcher.getUniqWrapSelector( view, widgetId ) );
			const $listing = $container.find( '.jet-listing-grid__items' ).first();
			const $slider = $listing.parent( '.jet-listing-grid__slider' );

			// Load new listings
			if ( settings.lisitng_id && activeSettings.lisitng_id && settings.lisitng_id !== activeSettings.lisitng_id ) {
				let navSettings = $listing.data( 'nav' ) || {};
				navSettings = JetEngine.ensureJSON( navSettings );

				let widgetSettings = navSettings.widget_settings || {};

				// Merge widget settings.
				Object.keys( settings ).forEach( function( settingName ) {
					if ( 'lisitng_id' === settingName ) {
						widgetSettings._layout_listing = settings[settingName];
					} else {
						widgetSettings[ settingName ] = settings[settingName];
					}
				} );

				let ajaxOptions = {
					handler: 'get_listing',
					container: $container,
					masonry: false,
					slider: false,
					append: false,
					query: navSettings.query || {},
					widgetSettings: widgetSettings,
					extraProps: {
						switch_layout: 1,
						initial_listing: defaultListing,
						layout_listing: settings.lisitng_id,
						listing_query_id: $listing.data( 'query-id' ),
					}
				};

				let hasLoadMore = !! widgetSettings.use_load_more && 'false' !== widgetSettings.use_load_more,
					page = parseInt( $listing.data( 'page' ), 10 ) || 0,
					pages = parseInt( $listing.data( 'pages' ), 10 ) || 0;

				if ( hasLoadMore ) {
					ajaxOptions.extraProps.loadMorePages = {
						first: 1,
						last: page
					};
				}

				const filterQueryId = widgetSettings._element_id ? widgetSettings._element_id : 'default';

				if ( window.JetSmartFilters ) {
					// Add the filtered query to the request
					if ( window.JetSmartFilters.filterGroups
						&& window.JetSmartFilters.filterGroups['jet-engine/' + filterQueryId]
						&& window.JetSmartFilters.filterGroups['jet-engine/' + filterQueryId].currentQuery
					) {
						Object.assign( ajaxOptions.extraProps, {
							filtered_query: window.JetSmartFilters.filterGroups['jet-engine/' + filterQueryId].currentQuery,
							filter_provider: 'jet-engine/' + filterQueryId,
						} );
					}

					if ( hasLoadMore && window.JetSmartFilters?.filterGroups?.['jet-engine/' + filterQueryId]?.currentQuery?.jet_paged ) {
						delete ajaxOptions.extraProps.loadMorePages;
					}

					if ( window.JetSmartFilters?.filterGroups?.['jet-engine/' + filterQueryId]?.filters ) {
						const filters = window.JetSmartFilters.filterGroups['jet-engine/' + filterQueryId].filters;

						filters.forEach( function( filter ) {

							if ( 'pagination' === filter.name
								&& filter.isLoadMore
								&& filter.moreActiveIndexes?.length
							) {
								ajaxOptions.extraProps.loadMorePages = {
									first: filter.moreActiveIndexes[0],
									last: filter.dataValue
								};

								page = filter.dataValue;
							}

						} );
					}

					// Update filters settings
					if ( window.JetSmartFilterSettings
						&& window.JetSmartFilterSettings.settings['jet-engine']
						&& window.JetSmartFilterSettings.settings['jet-engine'][ filterQueryId ]
					) {
						window.JetSmartFilterSettings.settings['jet-engine'][ filterQueryId ]['_layout_listing'] = settings.lisitng_id;
					}
				}

				$wrapper.addClass( JetEngineLayoutSwitcher.loadingClass );

				JetEngine.ajaxGetListing( ajaxOptions, function( response ) {
					// Update nav data attributes
					const $new_listing = $container.find( '.jet-listing-grid__items' ).first();

					$new_listing
						.data( 'page', page )
						.data( 'pages', pages )
						.attr( 'data-page', page )
						.attr( 'data-pages', pages );

					// Reinit pagination filters.
					JetEngineLayoutSwitcher.reInitPaginationFilters( filterQueryId, page, pages );

					JetEngine.widgetListingGrid( $container );
					JetEngineLayoutSwitcher.addLayoutCSS( $wrapper, widgetId, settings, isDefaultLayout );

					$wrapper.removeClass( JetEngineLayoutSwitcher.loadingClass );
				}, function() {
					$wrapper.removeClass( JetEngineLayoutSwitcher.loadingClass );
				} );

			} else {
				JetEngineLayoutSwitcher.addLayoutCSS( $wrapper, widgetId, settings, isDefaultLayout );
				JetEngineLayoutSwitcher.reInitMasonry( $listing, widgetId, settings, view );
				JetEngineLayoutSwitcher.reInitSlider( $slider, settings, view );
			}

			// Update active class
			$btn.closest( '.je-layout-switcher__group' )
				.find( '.' + activeClass )
				.removeClass( activeClass );

			$btn.addClass( activeClass );

		},

		reInitPaginationFilters: function( filterQueryId, page, pages ) {

			if ( ! window.JetSmartFilters ) {
				return;
			}

			if ( ! window.JetSmartFilters?.filterGroups?.['jet-engine/' + filterQueryId] ) {
				return;
			}

			if ( ! window.JetSmartFilterSettings?.props?.['jet-engine']?.[filterQueryId] ) {
				return;
			}

			const filterGroups = window.JetSmartFilters.filterGroups['jet-engine/' + filterQueryId];
			const paginationFilters = filterGroups.getFiltersByName( 'pagination' );

			window.JetSmartFilterSettings.props['jet-engine'][ filterQueryId ].page = page;
			window.JetSmartFilterSettings.props['jet-engine'][ filterQueryId ].max_num_pages = pages;

			if ( paginationFilters.length ) {
				paginationFilters.forEach( paginationFilter => {
					paginationFilter.reinit();
				} );
			}
		},

		addLayoutCSS: function( $wrapper, widgetId, settings, isDefaultLayout ) {
			let $styleTag = $( '#jet-engine-layout-switcher-custom-css-' + widgetId );
			let inlineCss = '';

			if ( ! $styleTag.length ) {

				$styleTag = $( '<style>', {
					id: 'jet-engine-layout-switcher-custom-css-' + widgetId
				} );

				$wrapper.append( $styleTag );
			}

			const view = $wrapper.data( 'view' );

			if ( isDefaultLayout && 'elementor' === view ) {
				$styleTag.html( inlineCss );
				return;
			}

			const selector = JetEngineLayoutSwitcher.getUniqWrapSelector( view, widgetId ) + ' > .jet-listing-grid > .jet-listing-grid__items';
			const slideSelector = JetEngineLayoutSwitcher.getUniqWrapSelector( view, widgetId ) + ' > .jet-listing-grid > .jet-listing-grid__slider > .jet-listing-grid__items.slick-slider .slick-slide';
			const breakpoints = JetEngineLayoutSwitcher.getActiveBreakpoints( view );

			if ( settings.columns ) {
				const display = ( 'auto' === settings.columns ) ? 'grid' : 'flex';
				const autoColRules = ( 'auto' === settings.columns && settings.column_min_width ) ? ' grid-template-columns: repeat( auto-fill, minmax( ' + settings.column_min_width + 'px, 1fr ) ) !important;' : '';
				const slideCss = ( 'auto' === settings.columns && settings.column_min_width ) ? ' ' + slideSelector + ' { width: ' + settings.column_min_width + 'px !important; }' : '';

				inlineCss += selector + ' { display: ' + display + ' !important; --columns: ' + settings.columns + ' !important;' + autoColRules + ' }' + slideCss;
			}

			Object.keys( breakpoints ).forEach( function( breakpointName ) {

				const columnsName = 'columns' + JetEngineLayoutSwitcher.getBreakpointDivider( view ) + breakpointName;
				const columnMinWidthName = 'column_min_width' + JetEngineLayoutSwitcher.getBreakpointDivider( view ) + breakpointName;

				if ( ! settings[ columnsName ] ) {
					return;
				}

				const dir     = breakpoints[ breakpointName ].direction;
				const value   = breakpoints[ breakpointName ].value;
				const columns = settings[ columnsName ];
				const bpDisplay = ( 'auto' === columns ) ? 'grid' : 'flex';
				const bpAutoColRules = ( 'auto' === columns && settings[ columnMinWidthName ] ) ? ' grid-template-columns: repeat( auto-fill, minmax( ' + settings[ columnMinWidthName ] + 'px, 1fr ) ) !important;' : '';
				const bpSlideCss = ( 'auto' === columns && settings[ columnMinWidthName ] ) ? ' ' + slideSelector + ' { width: ' + settings[ columnMinWidthName ] + 'px !important; }' : '';

				inlineCss += ' @media(' + dir + '-width: ' + value + 'px) { ' + selector + ' { display: ' + bpDisplay + ' !important; --columns: ' + columns + ' !important;' + bpAutoColRules + ' }' + bpSlideCss + ' }';
			} );

			$styleTag.html( inlineCss );
		},

		/**
		 * Update Elementor element `data-settings` attribute
		 */
		updateElementSettings: function( widgetId, settings ) {
			let columnsSettings = {};

			Object.keys( settings ).forEach( function( settingName ) {
				if ( 0 === settingName.indexOf( 'columns' ) ) {
					columnsSettings[ settingName ] = settings[settingName];
				}
			} );

			const $eWidget = $( '.elementor-element-' + widgetId );

			if ( $eWidget.length ) {
				$eWidget.data( 'settings', {
					...$eWidget.data( 'settings' ),
					...columnsSettings
				} );
			}
		},

		reInitMasonry: function( $listing, widgetId, settings, view ) {

			if ( ! $listing.hasClass( 'jet-listing-grid__masonry' ) ) {
				return;
			}

			// Update `data-masonry-grid-options` attr for $masonry selector
			let masonryColumnsSettings = JetEngineLayoutSwitcher.getColumnsSettings( settings, view );
			let masonryOptions = $listing.data( 'masonry-grid-options' );

			masonryOptions.columns = masonryColumnsSettings;

			$listing
				.data( 'masonry-grid-options', masonryOptions )
				.attr( 'data-masonry-grid-options', JSON.stringify( masonryOptions ) );

			JetEngine.runMasonry( $listing );
		},

		reInitSlider: function( $slider, settings, view ) {

			if ( ! $slider.length ) {
				return;
			}

			// Update `data-slider_options` attr for $slider selector
			let sliderColumnsSettings = JetEngineLayoutSwitcher.getColumnsSettings( settings, view );
			let sliderOptions = $slider.data( 'slider_options' );

			if ( 'auto' === sliderColumnsSettings?.desktop
				|| 'auto' === sliderColumnsSettings?.tablet
				|| 'auto' === sliderColumnsSettings?.mobile
			) {
				sliderOptions.slidesToShow = 1;
				sliderOptions.variableWidth = true;
			} else {
				sliderOptions.slidesToShow = sliderColumnsSettings;
				delete sliderOptions.variableWidth;
			}

			$slider
				.data( 'slider_options', sliderOptions )
				.attr( 'data-slider_options', JSON.stringify( sliderOptions ) );

			$slider.find( '> .jet-listing-grid__items' ).slick( 'unslick' );

			JetEngine.initSlider( $slider );
		},

		getColumnsSettings: function( settings, view ) {
			let columnsSettings = {};

			if ( settings.columns ) {
				columnsSettings.desktop = 'auto' === settings.columns ? settings.columns : +settings.columns;
			}

			const breakpoints = JetEngineLayoutSwitcher.getActiveBreakpoints( view );

			Object.keys( breakpoints ).forEach( function( breakpointName ) {
				const columnsName = 'columns' + JetEngineLayoutSwitcher.getBreakpointDivider( view ) + breakpointName;

				if ( settings[ columnsName ] ) {
					columnsSettings[ breakpointName ] = 'auto' === settings[ columnsName ] ? settings[ columnsName ] : +settings[ columnsName ];
				}
			} );

			return columnsSettings;
		},

		getUniqWrapSelector: function( view, widgetId ) {
			return window.JetEngineLayoutSwitcherViews?.[ view ]?.getUniqWrapSelector( widgetId ) || '';
		},

		getActiveBreakpoints: function( view ) {
			return window.JetEngineLayoutSwitcherViews?.[ view ]?.getActiveBreakpoints() || {};
		},

		getBreakpointDivider: function( view ) {
			return window.JetEngineLayoutSwitcherViews?.[ view ]?.getBreakpointDivider() || '_';
		}

	};

	$( window ).on( 'elementor/frontend/init', JetEngineLayoutSwitcher.elementorInit );

	JetEngineLayoutSwitcher.commonInit();

}( jQuery ) );