import HieraSelectData from './input';
import HieraSelectSignal from './signal';
import RequiredHierarchicalSelectRestriction from './RequiredHierarchicalSelectRestriction';
const { NotEmptyRestriction } = JetFormBuilderAbstract;

const { addFilter } = JetPlugins.hooks;

addFilter(
	'jet.fb.inputs',
	'jet-form-builder/hierarchical-select',
	function ( inputs ) {
		inputs = [ HieraSelectData, ...inputs ];
		return inputs;
	},
);

addFilter(
	'jet.fb.signals',
	'jet-form-builder/hierarchical-select',
	function ( signals ) {
		signals = [ HieraSelectSignal, ...signals ];
		return signals;
	},
);

addFilter(
	'jet.fb.restrictions.default',
	'jet-form-builder/hierarchical-select',
	function ( restrictions ) {
		restrictions.push( RequiredHierarchicalSelectRestriction );
		return restrictions;
	},
);

addFilter(
	'jet.fb.restrictions',
	'jet-form-builder/hierarchical-select',
	function (restrictions) {
		let NotEmpty = null;

		if (NotEmptyRestriction) {
			NotEmpty = restrictions.find((Ctor) =>
				Ctor === NotEmptyRestriction ||
				Ctor?.prototype === NotEmptyRestriction.prototype
			);
		}

		if (NotEmpty && !NotEmpty.__hrSkipPatched) {
			const origIsSupported = NotEmpty.prototype.isSupported;

			NotEmpty.prototype.isSupported = function (node, reporting) {
				if (reporting?.input?.inputType === 'hr-select-level') {
					return false;
				}
				return origIsSupported.call(this, node, reporting);
			};

			NotEmpty.__hrSkipPatched = true;
		}

		if (window.JetFormBuilderAbstract?.AdvancedRestriction) {
			import('./RequiredHierarchicalSelectRestrictionAdvanced').then(
				({ default: RequiredHierarchicalSelectRestrictionAdvanced }) => {
					restrictions.push(RequiredHierarchicalSelectRestrictionAdvanced);
				}
			);
		}
		return restrictions;
	},
	999
);
