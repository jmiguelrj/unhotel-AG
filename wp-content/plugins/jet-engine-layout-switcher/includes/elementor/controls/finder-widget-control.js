(function( $ ) {
	'use strict';

	function registerControl() {
		var FinderWidgetControlItemView = elementor.modules.controls.Select2.extend({

			hasOptions: false,

			addOptions: function addOptions() {
				var widgetName = this.model.get( 'widget_name' );

				if ( ! widgetName ) {
					return;
				}

				if ( ! window.elementorFrontend ) {
					return;
				}

				var $body = window.elementorFrontend.elements.$body,
					$widgets = $body.find( '.elementor-widget-' + widgetName );

				if ( ! $widgets.length ) {
					return;
				}

				if ( ! $widgets.length ) {
					return;
				}

				var result = {},
					widgetTitle = '',
					count = 1;

				if ( elementor?.config?.widgets?.[ widgetName ] ) {
					widgetTitle = elementor.config.widgets[ widgetName ].title;
				}

				this.ui.select.prop( 'disabled', true );

				$widgets.each( function() {
					var widgetId = $( this ).data( 'id' ),
						attrId = $( this ).attr( 'id' ) || '',
						modelId = $( this ).data( 'model-cid' ) || false,
						customTitle = '';

					if ( ! modelId ) {
						return;
					}

					if ( result[ widgetId ] ) {
						return;
					}

					if ( window.elementorFrontend?.config?.elements?.data?.[ modelId ]?.attributes?._title ) {
						customTitle = window.elementorFrontend.config.elements.data[ modelId ].attributes._title;
					}

					if ( customTitle ) {
						customTitle = ' - ' + customTitle;
					}

					if ( attrId ) {
						attrId = ' - #' + attrId;
					}

					result[ widgetId ] = widgetTitle + ' #' + count + ' (' + widgetId + ')' + customTitle + attrId;
					count++;
				} );

				this.hasOptions = true;

				this.model.set( 'options', result );
				this.render();
			},

			onReady: function onReady() {
				if ( ! this.hasOptions ) {
					this.addOptions();
				}
			}
		});

		// Add controls views
		elementor.addControlView( 'jet-finder-widget', FinderWidgetControlItemView );
	}

	if ( window.elementor?.modules?.controls ) {
		registerControl();
	} else {
		$( window ).on( 'elementor:init', registerControl );
	}

}( jQuery ));
