import HieraSelectData from './input';

const {
	      BaseSignal,
      } = JetFormBuilderAbstract;

function HieraSelectSignal() {
	BaseSignal.call( this );

	this.isSupported = function ( node, input ) {
		return input instanceof HieraSelectData;
	};

	this.runSignal = function () {
		this.input.calcValue = parseFloat( this.input.calcValue );
		const [ , control ] = this.input.nodes;

		if ( control.value !== this.input.value.current ) {
			control.value = this.input.value.current;
		}

		if ( 'select-one' !== control.type ) {
			return;
		}

		for ( const option of control.options ) {
			if ( ! option.selected ) {
				continue;
			}
			this.input.calcValue = parseFloat(
				option.dataset?.calculate ?? option.value,
			);
		}

	};
}

HieraSelectSignal.prototype = Object.create( BaseSignal.prototype );

export default HieraSelectSignal;