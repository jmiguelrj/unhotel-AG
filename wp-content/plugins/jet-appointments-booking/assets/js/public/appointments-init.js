(function () {

	'use strict';

	let picker,
		calendars = {},
		settings = {
			selector: '.appointment-calendar',
			datesFilter: true,
			pastDates: false,
			layout: window.JetAPBData.layout,
			weekDays: window.JetAPBData.week_days,
			timeFormat: window.JetAPBData.time_format,
			weekStart: window.JetAPBData.start_of_week,
			scrollToDetails: window.JetAPBData.scroll_to_details,
			api: window.JetAPBData.api,
			multiBooking: window.JetAPBData.multi_booking,
			services: window.JetAPBData.services,
			providers: window.JetAPBData.providers,
			UTCOffset: parseFloat( window.JetAPBData.utc_offset ),
			namespace: '',
		};

	if ( window.JetAPBData.months ) {
		settings.months = window.JetAPBData.months;
	}

	if ( window.JetAPBData.shortWeekday ) {
		settings.shortWeekday = window.JetAPBData.shortWeekday;
	}

	const calcFiledValue = function( value, $field ) {
			if ( 'appointment' === $field.data( 'field' ) ) {
				let outputValue = 0,
					parseValue = value ? JSON.parse( value ) : 0;

				if( typeof parseValue === 'object' ){
					for ( const slot of parseValue ) {
						let price = parseFloat( slot.price );
						let count = slot.count || 1;

						outputValue += price * count;
					}

					value = outputValue;
				}
			}

			return value;
		},
		bookingFormIinit = function( e, $el ) {

			let $cal = $el.find( '.appointment-calendar' ),
			$form = $el.find( 'form' );

			if ( ! $cal.length || ! $form.length ) {
				return;
			}

			let formId = $form.data('form-id');

			if ( calendars[formId] && calendars[formId].destroy ) {
				calendars[formId].destroy();
			}

			settings.namespace = e.data.namespace;

			if ( window.JetPlugins ) {
				window.JetPlugins.hooks.applyFilters( 'jet-apb.calendar-settings', settings, e, $el );
			}

			settings.selector = `[data-form-id="${formId}"] .appointment-calendar`;
			calendars[formId] = new VanillaCalendar( settings );

			if( settings.namespace === "jet-form-builder" && window.JetFormBuilderMain ){
				JetFormBuilderMain.filters.addFilter( 'forms/calculated-field-value', calcFiledValue );
			} else {
				JetEngine.filters.addFilter( 'forms/calculated-field-value', calcFiledValue );
			}

		};

	jQuery( document ).on( 'jet-engine/booking-form/init', { namespace: "jet-form" }, bookingFormIinit );
	jQuery( document ).on( 'jet-form-builder/init', { namespace: "jet-form-builder" }, bookingFormIinit );

}());
