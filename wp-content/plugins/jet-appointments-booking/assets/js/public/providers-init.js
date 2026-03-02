(function () {

	'use strict';

	function loadServices( namespace, providerWrapper, data ) {

		let $input = this;
		let service = $input.val();
		let $loader = providerWrapper.find( '.appointment-provider__loader' );
		let isAjax = window.JetAPBisAjax || false;

		if ( ! service ) {
			return;
		}

		if ( $loader.length ) {
			$loader.removeClass( 'appointment-provider__loader-hidden' );
		}

		jQuery.ajax( {
			url: data.api.service_providers,
			type: 'GET',
			dataType: 'json',
			data: {
				service: service,
				custom_template: data.custom_template,
				args_str: data.args_str,
				is_ajax: isAjax,
				default: data.default,
				namespace,
			},
		} ).done( function( response ) {

			if ( $loader.length ) {
				$loader.addClass( 'appointment-provider__loader-hidden' );
			}

			providerWrapper.val( '' ).trigger( 'refresh', [ providerWrapper ] );

			if ( ! data.custom_template ) {
				if ( response.data.length ) {
					providerWrapper.html( '<option value="">' + data.placeholder + '</option>' );
					for ( var i = 0; i < response.data.length; i++ ) {
						let checked = '',
						item = response.data[ i ];
						if ( item.ID == data.default ) {
							checked = ' selected';
						}
						providerWrapper.append( '<option' + checked + ' value="' + item.ID + '">' + item.post_title + '</option>' );
					}
				} else if ( response.data && 0 == response.data.length ) {
					providerWrapper.html( '<option value="">' + data.placeholder + '</option>' );
				}
			} else {
				if ( response.data ) {
					providerWrapper.find( '.appointment-provider__content' ).html( response.data );
				}
			}
		} );

	}

	function handleServicesBy( namespace, providerWrapper, data, formId ) {

		jQuery( document ).on( 'change',
			`.${ namespace }` + '[data-form-id="' + formId + '"]' + ` [name="` + data.service.field + `"]`,
			e => loadServices.call( jQuery( e.target ), namespace, providerWrapper, data ),
		);

		jQuery(
			`.${ namespace }` + '[data-form-id="' + formId + '"]' + ` select[name="` + data.service.field + `"]`,
		).each( function() {
			loadServices.call( jQuery( this ), namespace, providerWrapper, data )
		} );

		jQuery(
			`.${ namespace }` + '[data-form-id="' + formId + '"]' + ` input[name="` + data.service.field + `"]:checked`,
		).each( function() {
			loadServices.call( jQuery( this ), namespace, providerWrapper, data )
		} );

		jQuery(
			`.${ namespace }` + '[data-form-id="' + formId + '"]' + ` input[name="` + data.service.field + `"][type="hidden"]`,
		).each( function() {
			loadServices.call( jQuery( this ), namespace, providerWrapper, data )
		} );
	}

	function initProviderControl( event, $el ) {

		let namespace = event.data.namespace,
		formId = $el.find( 'form' ).data( 'form-id' );

		jQuery( ' .' + namespace + '[data-form-id="' + formId + '"]' + ' .appointment-provider' ).each( function() {

			var $this = jQuery( this ),
				data  = $this.data( 'args' );

			if ( data.service.field ) {

				if ( $this.is( 'select' ) ) {
					$this.html( '<option value="">' + data.placeholder + '</option>' );
				}

				handleServicesBy( namespace, $this, data, formId );
			}

		} );
	}

	jQuery( document ).on( 'jet-engine/booking-form/init', { namespace: "jet-form" }, initProviderControl );
	jQuery( document ).on( 'jet-form-builder/init', { namespace: "jet-form-builder" }, initProviderControl );

}());
