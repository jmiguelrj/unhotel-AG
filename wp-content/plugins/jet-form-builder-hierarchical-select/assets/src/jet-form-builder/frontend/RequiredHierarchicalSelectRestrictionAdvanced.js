const {
	AdvancedRestriction,
} = JetFormBuilderAbstract;

const {
	isEmpty,
} = JetFormBuilderFunctions;


function RequiredHierarchicalSelectRestrictionAdvanced() {
	AdvancedRestriction.call( this );

	this.isSupported = function ( node, reporting ) {
		return reporting.input.inputType === 'hr-select-level' ?? false;
	};

	this.validate = function () {
		const select = this.reporting.input.getReportingNode();
		const current = select.value;
		if (isEmpty( current )) {
			const options = select.querySelectorAll('option');
			const optionsCount = options.length;
			const hasOnlyPlaceholder = optionsCount === 1 && options[0].value === '';

			if (optionsCount === 0 || hasOnlyPlaceholder) {
				return true;
			}
			return false;
		}
		return true;
	};
}

RequiredHierarchicalSelectRestrictionAdvanced.prototype = Object.create( AdvancedRestriction.prototype );

export default RequiredHierarchicalSelectRestrictionAdvanced;


