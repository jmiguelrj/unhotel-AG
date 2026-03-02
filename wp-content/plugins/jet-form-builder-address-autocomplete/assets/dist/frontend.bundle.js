// <script
// src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">
function initJFBAutocomplete() {
	const $                = jQuery;
	const { applyFilters } = window?.JetPlugins?.hooks || wp.hooks;
	const service          = new google.maps.places.AutocompleteService();

	$( document ).
		on( 'input.JetEngine', '.jet-form__field.jet-address-autocomplete',
			function () {
				$( this ).trigger( 'change.JetEngine' );
			} );

	const addItemResult = ( { prediction, index } ) => {
		const text = applyFilters(
			'jet-fb.address_autocomplete.prediction.text',
			prediction.description,
			prediction,
		);

		return $( '<li>' ).
			text( text ).
			attr( 'value', index ).
			addClass( 'jet-adr-visible' );
	};

	function AutocompleteFieldInit() {
		const self     = $( this );
		const settings = self.data( 'address-settings' );
		let options    = {};

		if ( settings.countries ) {
			options.componentRestrictions = { country: [ ...settings.countries ] };
			delete settings.countries;
		}
		options = {
			...options,
			...settings,
		};

		self.editableSelect(
			applyFilters( 'jet-fb.address_autocomplete.dropdown-options', {
				effects: 'slide',
				duration: 200,
			} ) );

		let sessionToken = new google.maps.places.AutocompleteSessionToken();

		self.on( 'jet-fb.select', e => {
			sessionToken = new google.maps.places.AutocompleteSessionToken();

			const input = self[ 0 ]?.jfbSync;
			if ( !input ) {
				return;
			}

			input.value.current = self.val();
		} );

		self.on( 'jet-fb.input', ( e, list ) => {
			if ( !self.val() ) {
				list.empty();
				return;
			}

			const displayPredictions = ( predictions, status ) => {
				if ( status != google.maps.places.PlacesServiceStatus.OK ) {
					self.trigger( 'jet-fb.error', [ status, list ] );
					return;
				}
				list.empty();
				list.append(
					...predictions.map(
						( prediction, index ) => addItemResult(
							{ prediction, index } ),
					),
				);
			};

			service.getPlacePredictions( {
				...options,
				input: self.val(),
				sessionToken,
			}, displayPredictions );

		} );
	}

	const handleField = function () {
		const self = $( this ).closest( `.jet-form-repeater` );
		self.find( '.jet-address-autocomplete' ).each( AutocompleteFieldInit );
	};

	$( document ).
		on( 'jet-engine/booking-form/init', function ( event, $scope ) {
			const self = $scope.find( 'form' );
			self.find( '.jet-address-autocomplete' ).
				each( AutocompleteFieldInit );
		} );

	$( document ).on(
		'jet-engine/form/repeater-add-new',
		'.jet-form-repeater__new',
		handleField,
	);
}

