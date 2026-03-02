jQuery(function ($: JQueryStatic) {
	const $importForm = $('form.ame-unified-import-form').first(),
		$importFile = $importForm.find('#ame-import-file-selector'),
		$submitButton = $importForm.find(':submit');

	//Enable the "next" button when the user selects a file.
	$importFile.on('change', function () {
		$submitButton.prop('disabled', !$importFile.val());
	});

	if ($importForm.is('#ame-import-step-2')) {
		const $importableModules = $importForm.find('.ame-importable-module');
		//Only enable the submit button when at least one module is selected.
		$importableModules.change(function () {
			$submitButton.prop('disabled', $importableModules.filter(':checked').length === 0);
		});
	}

	//Expand/collapse component configuration.
	$importForm.find('.ame-import-config-toggle').on('click', function (event) {
		const $toggle = $(event.target),
			$item = $toggle.closest('.ame-import-component-item'),
			expandText = $toggle.data('expand-text') || 'Show settings',
			collapseText = $toggle.data('collapse-text') || 'Hide settings';

		$item.toggleClass('ame-has-expanded-import-config');
		$toggle.text($item.hasClass(
			'ame-has-expanded-import-config') ? collapseText : expandText
		);
	});
});

//region Configuration for the "Enabled modules" component.
{
	interface EnabledModulesConfigData {
		currentState: Record<string, boolean>;
		incomingState: Record<string, boolean>;
		availableModules: Record<string, {
			title: string;
			isCompatible?: boolean;
		}>;
	}

	class ModuleOption {
		public readonly enabledAfterImport: KnockoutObservable<boolean>;
		public readonly willChange: KnockoutComputed<boolean>;
		public readonly customState: KnockoutObservable<boolean | null>;

		constructor(
			public readonly id: string,
			public readonly title: string,
			public readonly isCompatible: boolean,
			public readonly currentState: boolean,
			incomingState: boolean | null
		) {
			const defaultResult = incomingState !== null ? incomingState : currentState;
			this.customState = ko.observable(null);

			this.enabledAfterImport = ko.computed({
				read: () => {
					if (!this.isCompatible) {
						return this.currentState;
					}

					const custom = this.customState();
					if (custom !== null) {
						return custom;
					}
					return defaultResult;
				},
				write: (value: boolean) => {
					if (value === defaultResult) {
						this.customState(null);
					} else {
						this.customState(value);
					}
				}
			});

			this.willChange = ko.pureComputed(() => this.enabledAfterImport() !== this.currentState);
		}
	}

	class EnabledModulesConfigViewModel {
		public readonly options: ModuleOption[];
		public readonly willBeEnabled: KnockoutComputed<ModuleOption[]>;
		public readonly willBeDisabled: KnockoutComputed<ModuleOption[]>;
		public readonly unchanged: KnockoutComputed<ModuleOption[]>;

		public readonly configFieldValue: KnockoutObservable<string>;

		constructor(data: EnabledModulesConfigData) {
			this.options = Object.entries(data.availableModules).map(
				([moduleId, moduleInfo]) => new ModuleOption(
					moduleId,
					moduleInfo.title,
					moduleInfo.isCompatible !== false,
					data.currentState[moduleId],
					moduleId in data.incomingState
						? data.incomingState[moduleId]
						: null
				)
			);

			this.willBeEnabled = ko.pureComputed(() =>
				this.options.filter(opt => opt.willChange() && opt.enabledAfterImport())
			);
			this.willBeDisabled = ko.pureComputed(() =>
				this.options.filter(opt => opt.willChange() && !opt.enabledAfterImport())
			);
			this.unchanged = ko.pureComputed(() =>
				this.options.filter(opt => !opt.willChange())
			);

			this.configFieldValue = ko.pureComputed(() => {
				const customValues: Record<string, boolean> = {};
				this.options.forEach(opt => {
					const custom = opt.customState();
					if (custom !== null) {
						customValues[opt.id] = custom;
					}
				});
				return JSON.stringify({'custom': customValues});
			});
		}
	}

	jQuery(function ($: JQueryStatic) {
		const $container = $('#ame-import-enabled-modules-container');
		if ($container.length < 1) {
			return;
		}

		$('#ame-iem-tabs').tabs();

		const initialData = $container.data('config-data') as EnabledModulesConfigData;
		console.log(initialData);
		const viewModel = new EnabledModulesConfigViewModel(initialData);
		ko.applyBindings(viewModel, $container.get(0));
	});
}
//endregion