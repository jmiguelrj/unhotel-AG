( function ( $ ) {
	// jQuery Editable Select
	EditableSelect = function ( input, options ) {
		var that = this;

		this.options = options;
		this.$input = $( input );
		this.$list = $( '<ul class="jet-adr-list">' );
		this.utility = new EditableSelectUtility( this );

		if ( [ 'focus', 'manual' ].indexOf( this.options.trigger ) < 0 ) this.options.trigger = 'focus';
		if ( [ 'default', 'fade', 'slide' ].indexOf( this.options.effects ) < 0 ) this.options.effects = 'default';
		if ( isNaN( this.options.duration ) && [ 'fast', 'slow' ].indexOf( this.options.duration ) < 0 ) this.options.duration = 'fast';

		this.$list.appendTo( this.options.appendTo || this.$input.parent() );

		// initalization
		this.utility.initializeList();
		this.utility.initializeInput();
		this.utility.trigger( 'created' );
	}
	EditableSelect.DEFAULTS = { filter: true, effects: 'default', duration: 'fast', trigger: 'focus' };
	EditableSelect.prototype.filter = function () {
		// since 1.0.8 empty
	};
	EditableSelect.prototype.show = function () {
		this.$list.css( {
			top: this.$input.position().top + this.$input.outerHeight() - 1,
			left: this.$input.position().left,
			width: this.$input.outerWidth()
		} );

		if ( ! this.$list.is( ':visible' ) && this.$list.find( 'li.jet-adr-visible' ).length > 0 ) {
			var fns = { default: 'show', fade: 'fadeIn', slide: 'slideDown' };
			var fn = fns[ this.options.effects ];

			this.utility.trigger( 'show' );
			this.$input.addClass( 'open' );
			this.$list[ fn ]( this.options.duration, $.proxy( this.utility.trigger, this.utility, 'shown' ) );
		}
	};
	EditableSelect.prototype.hide = function () {
		var fns = { default: 'hide', fade: 'fadeOut', slide: 'slideUp' };
		var fn = fns[ this.options.effects ];

		this.utility.trigger( 'hide' );
		this.$input.removeClass( 'open' );
		this.$list[ fn ]( this.options.duration, $.proxy( this.utility.trigger, this.utility, 'hidden' ) );
	};
	EditableSelect.prototype.select = function ( $li ) {
		if ( ! this.$list.has( $li ) || ! $li.is( 'li.jet-adr-visible:not([disabled])' ) ) return;
		this.$input.val( $li.text() );
		if ( this.options.filter ) this.hide();
		this.filter();
		this.utility.trigger( 'select', $li );
	};

	// Utility
	EditableSelectUtility = function ( es ) {
		this.es = es;
	};
	EditableSelectUtility.prototype.initializeList = function () {
		var that = this;
		that.es.$list
		.on( 'mousemove', 'li:not([disabled])', function () {
			that.es.$list.find( '.selected' ).removeClass( 'selected' );
			$( this ).addClass( 'selected' );
		} )
		.on( 'mousedown', 'li', function ( e ) {
			if ( $( this ).is( '[disabled]' ) ) e.preventDefault();
			else that.es.select( $( this ) );
		} )
		.on( 'mouseup', function () {
			that.es.$list.find( 'li.selected' ).removeClass( 'selected' );
		} );
	};
	EditableSelectUtility.prototype.initializeInput = function () {
		var that = this;
		switch ( this.es.options.trigger ) {
			default:
			case 'focus':
				that.es.$input
				.on( 'focus', $.proxy( that.es.show, that.es ) )
				.on( "blur", $.proxy( function () {
						if ( $( ".jet-adr-list:hover" ).length === 0 ) {
							that.es.hide();
						}
						else {
							this.$input.focus();
						}
					}, that.es
				) );
				break;
			case 'manual':
				break;
		}
		that.es.$input.on( 'input keydown', function ( e ) {
			let visible, selectedIndex;

			switch ( e.keyCode ) {
				case 38: // Up
					visible = that.es.$list.find( 'li.jet-adr-visible:not([disabled])' );
					selectedIndex = visible.index( visible.filter( 'li.selected' ) );
					that.highlight( selectedIndex - 1 );
					e.preventDefault();
					break;
				case 40: // Down
					visible = that.es.$list.find( 'li.jet-adr-visible:not([disabled])' );
					selectedIndex = visible.index( visible.filter( 'li.selected' ) );
					that.highlight( selectedIndex + 1 );
					e.preventDefault();
					break;
				case 13: // Enter
					if ( that.es.$list.is( ':visible' ) ) {
						that.es.select( that.es.$list.find( 'li.selected' ) );
						e.preventDefault();
					}
					break;
				case 9:  // Tab
				case 27: // Esc
					that.es.hide();
					break;
			}
		} );
		that.es.$input.on( 'input', e => {
			$( that.es.$input ).trigger( 'jet-fb.input', [ that.es.$list ] );
			that.es.filter();
			that.highlight( 0 );
		} );
	};
	EditableSelectUtility.prototype.highlight = function ( index ) {
		var that = this;
		that.es.show();
		setTimeout( function () {
			var visibles = that.es.$list.find( 'li.jet-adr-visible' );
			var oldSelected = that.es.$list.find( 'li.selected' ).removeClass( 'selected' );
			var oldSelectedIndex = visibles.index( oldSelected );

			if ( visibles.length > 0 ) {
				var selectedIndex = ( visibles.length + index ) % visibles.length;
				var selected = visibles.eq( selectedIndex );
				var top = selected.position().top;

				selected.addClass( 'selected' );
				if ( selectedIndex < oldSelectedIndex && top < 0 )
					that.es.$list.scrollTop( that.es.$list.scrollTop() + top );
				if ( selectedIndex > oldSelectedIndex && top + selected.outerHeight() > that.es.$list.outerHeight() )
					that.es.$list.scrollTop( that.es.$list.scrollTop() + selected.outerHeight() + 2 * ( top - that.es.$list.outerHeight() ) );
			}
		} );
	};
	EditableSelectUtility.prototype.trigger = function ( event ) {
		var params = Array.prototype.slice.call( arguments, 1 );
		var args = [ `jet-fb.${ event }` ];
		args.push( params );
		this.es.$input.trigger.apply( this.es.$input, args );
	};

	// Plugin

	$.fn.editableSelect = function ( option ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		return this.each( function () {
			var $this = $( this );
			var data = $this.data( 'editable-select' );
			var options = $.extend( {}, EditableSelect.DEFAULTS, $this.data(), typeof option == 'object' && option );

			if ( ! data ) data = new EditableSelect( this, options );
			if ( typeof option == 'string' ) data[ option ].apply( data, args );
		} );
	};

	$.fn.editableSelect.Constructor = EditableSelect;

} )( jQuery );