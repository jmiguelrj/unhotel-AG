const {
	      InputData,
      } = JetFormBuilderAbstract;
const {
	      getParsedName,
      } = JetFormBuilderFunctions;

function HieraSelectData() {
	InputData.call( this );

	this.isSupported = function ( node ) {
		return node.classList.contains(
			'jet-form-builder-hr-select-level',
		);
	};

	this.addListeners = function () {
		const [ , node ] = this.nodes;

		node.addEventListener( 'input', event => {
			this.value.current = event.target.value;
		} );
		node.addEventListener( 'change', event => {
			this.value.current = event.target.value;
		} );
		node.addEventListener( 'blur', event => {
			this.reportOnBlur();
		} );
	};

	this.setNode = function ( node ) {
		InputData.prototype.setNode.call( this, node );

		this.nodes.push( node.querySelector( 'select, input' ) );

		const [ , control ] = this.nodes;

		this.rawName = control.name ?? '';
		this.name    = getParsedName( this.rawName );

		this.inputType = 'hr-select-level';
	};

	this.setValue = function () {
		const [ , control ] = this.nodes;
		this.value.current  = control?.value;
	};

	this.onClear = function () {
		this.silenceSet( '' );
	};

	this.onChangeLoading = function () {
		this.getSubmit().lockState.current = this.loading.current;

		const [ node, control ] = this.nodes;

		control.readOnly = this.loading.current;
		node.classList.toggle( 'is-loading', this.loading.current );
	};

	this.checkIsRequired = function () {
		const [ , node ] = this.nodes;

		return node.required;
	};

	this.getReportingNode = function () {
		return this.nodes[ 1 ];
	};
}

HieraSelectData.prototype = Object.create( InputData.prototype );

HieraSelectData.prototype.resetControl = function () {
	const [ level ] = this.nodes;

	this.setNode( level );
	this.addListeners();
};

HieraSelectData.prototype.getPrevLevel = function () {
	const [ level ] = this.nodes;

	return level?.previousElementSibling?.jfbSync;
};

export default HieraSelectData;