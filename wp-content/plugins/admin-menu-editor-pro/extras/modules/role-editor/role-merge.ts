namespace AmeRoleMergeComponent {
	type CapabilityState = Record<string, boolean | null>;
	type CapabilityChangesRecord = Record<string, { oldState: boolean | null, newState: boolean | null }>;

	class CapabilityChanges {
		private _changedCapabilities: CapabilityState = {};

		private _addedCaps: Set<string> = new Set<string>();
		private _removedCaps: Set<string> = new Set<string>();
		private _otherChanges: Record<string, { oldState: boolean | null, newState: boolean | null }> = {};

		addCapabilityChange(capName: string, newState: boolean | null, oldState: boolean | null): void {
			if (newState === oldState) {
				return;
			}

			this._changedCapabilities[capName] = newState;
			if (newState) {
				this._addedCaps.add(capName);
			} else if ((newState === null) && oldState) {
				this._removedCaps.add(capName);
			} else {
				this._otherChanges[capName] = {oldState, newState};
			}
		}

		get addedCaps(): Set<string> {
			return this._addedCaps;
		}

		get otherChanges(): CapabilityChangesRecord {
			return this._otherChanges;
		}

		get removedCaps(): Set<string> {
			return this._removedCaps;
		}

		get changedCapabilities(): CapabilityState {
			return this._changedCapabilities;
		}
	}

	export const actionLeaveUnchanged = 'leaveUnchanged';
	export const actionAcceptIncoming = 'acceptIncoming';
	export const actionDisable = 'disable';
	type ActionType = typeof actionLeaveUnchanged | typeof actionAcceptIncoming | typeof actionDisable;

	type CustomCapStrategy = typeof actionLeaveUnchanged | typeof actionAcceptIncoming | typeof actionDisable;
	type LocalOnlyCapStrategy = typeof actionLeaveUnchanged | typeof actionDisable;
	type CoreCapabilityChecker = (capName: string) => boolean;

	//Either the base role or the incoming role can be null, but not both at the same time.
	type RolePair = {
		baseRole: IAmeRole;
		incomingRole: IAmeRole;
	} | {
		baseRole: null;
		incomingRole: IAmeRole;
	} | {
		baseRole: IAmeRole;
		incomingRole: null;
	};

	interface RoleChangeMessage {
		text: string;
		type: 'positive' | 'negative' | 'neutral' | 'none';
		isLast?: boolean;
	}

	export enum UiTextsVariant {
		Import = 'import',
		ResetRoles = 'resetRoles'
	}

	type PickTextFunction = (options: { [key in UiTextsVariant]: string }) => string;

	const {_n, sprintf} = wp.i18n;
	let optionInstanceCounter = 0;

	class RoleOption {
		readonly isChecked: KnockoutObservable<boolean> = ko.observable(false);
		readonly canBeChecked: KnockoutComputed<boolean>;
		readonly displayName: KnockoutComputed<string>;
		readonly roleName: string;
		readonly changeTexts: KnockoutComputed<RoleChangeMessage[]>;
		readonly htmlId: string;

		readonly hasDetailsPanel: KnockoutComputed<boolean>;
		// noinspection JSUnusedGlobalSymbols // Used in the KO template.
		readonly isPanelOpen: KnockoutObservable<boolean> = ko.observable(false);

		readonly addedCaps: KnockoutComputed<string[]>;
		readonly removedCaps: KnockoutComputed<string[]>
		readonly otherChanges: KnockoutComputed<Array<{
			capName: string;
			oldState: boolean | null;
			newState: boolean | null;
		}>>;

		constructor(
			public readonly roles: RolePair,
			protected readonly allIncomingCapabilities: Set<string>,
			protected readonly isCoreCapability: CoreCapabilityChecker,
			protected readonly customCapStrategy: KnockoutObservable<CustomCapStrategy>,
			protected readonly localOnlyCapStrategy: KnockoutObservable<LocalOnlyCapStrategy>,
			pickTextVariant: PickTextFunction
		) {
			const instanceId = optionInstanceCounter++;

			this.displayName = ko.pureComputed(() => {
				const baseRole = this.roles.baseRole;
				if (baseRole) {
					return baseRole.getDisplayName();
				}
				const incomingRole = this.roles.incomingRole;
				if (incomingRole) {
					return incomingRole.getDisplayName();
				}
				return 'Error: No Role';
			});

			this.canBeChecked = ko.pureComputed(() => {
				//A role can only be selected/checked if it has an incoming counterpart.
				return this.roles.incomingRole !== null;
			});

			this.htmlId = 'rex-rm-role-option--' + (
				this.roles.baseRole ? this.roles.baseRole.getRoleName() : 'new-' + this.roles.incomingRole.getRoleName()
			) + '--' + instanceId;

			const computedChanges =
				ko.pureComputed(() => this.computeChanges());

			this.changeTexts = ko.pureComputed(() => {
				const changes = computedChanges();
				const texts: RoleChangeMessage[] = [];

				if (this.roles.incomingRole === null) {
					//No incoming role, so no changes.
					texts.push({
						text: pickTextVariant({
							[UiTextsVariant.Import]: 'No changes (not in the import file)',
							[UiTextsVariant.ResetRoles]: 'No changes (no default role data)'
						}),
						type: 'none',
						isLast: true
					})
					return texts;
				}

				if (this.roles.baseRole === null) {
					//New role, or recreating a missing default role.
					texts.push({
						text: pickTextVariant({
							[UiTextsVariant.Import]: 'Create role',
							[UiTextsVariant.ResetRoles]: 'Recreate missing role'
						}),
						type: 'positive'
					});
				}

				const addedCapCount = changes.addedCaps.size;
				if (addedCapCount > 0) {
					texts.push({
						text: sprintf(_n(
							'+%d capability',
							'+%d capabilities',
							addedCapCount,
						), addedCapCount),
						type: 'positive'
					});
				}

				const removedCapCount = changes.removedCaps.size;
				if (removedCapCount > 0) {
					texts.push({
						text: sprintf(_n(
							'-%d capability',
							'-%d capabilities',
							removedCapCount,
						), removedCapCount),
						type: 'negative'
					});
				}

				const otherChangesCount = Object.keys(changes.otherChanges).length;
				if (otherChangesCount > 0) {
					texts.push({
						text: sprintf(_n(
							'%d other change',
							'%d other changes',
							otherChangesCount,
						), otherChangesCount),
						type: 'neutral'
					});
				}

				if (texts.length > 0) {
					texts[texts.length - 1].isLast = true;
				}

				//Capitalize the first letter of the first text.
				if (texts.length > 0) {
					texts[0].text = texts[0].text.charAt(0).toUpperCase() + texts[0].text.slice(1);
				}

				return texts;
			});

			this.addedCaps = ko.pureComputed(() => {
				return Array.from(computedChanges().addedCaps).sort();
			});
			this.removedCaps = ko.pureComputed(() => {
				return Array.from(computedChanges().removedCaps).sort();
			});
			this.otherChanges = ko.pureComputed(() => {
				const changes = computedChanges().otherChanges;
				return Object.keys(changes).sort().map((capName) => ({
					capName,
					oldState: changes[capName].oldState,
					newState: changes[capName].newState
				}));
			});

			this.hasDetailsPanel = ko.pureComputed(() => {
				return (
					this.addedCaps().length > 0
					|| this.removedCaps().length > 0
					|| this.otherChanges().length > 0
				);
			});

			this.roleName = this.roles.baseRole ? this.roles.baseRole.getRoleName() : this.roles.incomingRole.getRoleName();
		}

		/**
		 * Compute capability changes based on the current strategy settings.
		 */
		computeChanges(): CapabilityChanges {
			const changes = new CapabilityChanges();
			if (this.roles.incomingRole === null) {
				return changes; //No changes possible.
			}

			const customCapStrategy = this.customCapStrategy();
			const localOnlyCapStrategy = this.localOnlyCapStrategy();

			const baseRole = this.roles.baseRole;
			const incomingRole = this.roles.incomingRole;

			const baseCaps = baseRole?.getOwnCapabilities() ?? {};
			const incomingCaps = incomingRole?.getOwnCapabilities() ?? {};

			const relevantCaps = new Set<string>([
				...Object.keys(baseCaps),
				...Object.keys(incomingCaps)
			]);

			relevantCaps.forEach((capName) => {
				const baseState = baseCaps[capName] ?? null;
				const incomingState = incomingCaps[capName] ?? null;

				const isCustomCap = !this.isCoreCapability(capName);
				const isLocalOnlyCap = (
					baseCaps
					&& baseCaps.hasOwnProperty(capName)
					&& !this.allIncomingCapabilities.has(capName)
				);

				//Default action is "merge" for all capabilities.
				//This means that if the incoming role has a different value for the capability
				//than the base role, the incoming value will be used.
				let action: ActionType = actionAcceptIncoming;

				//Custom cap strategy can optionally override the action for custom capabilities.
				if (isCustomCap) {
					action = customCapStrategy;
				}

				//Local-only cap strategy applies to caps that exist on this site but not on the site
				//from which the incoming role was imported. The custom cap strategy can override this
				//if it's set to anything other than the default "accept incoming" action.
				if (isLocalOnlyCap && (action === actionAcceptIncoming)) {
					action = localOnlyCapStrategy;
				}

				switch (action) {
					case actionLeaveUnchanged:
						//Do nothing.
						break;
					case actionDisable:
						if (baseState !== null) {
							changes.addCapabilityChange(capName, null, baseState);
						}
						break;
					case actionAcceptIncoming:
						if (incomingState !== baseState) {
							changes.addCapabilityChange(capName, incomingState, baseState);
						}
						break;
				}
			});

			return changes;
		}
	}

	interface StrategyOption<T> {
		value: T;
		label: string;
	}

	interface Configuration {
		compareRolesForSorting: (a: IAmeActor, b: IAmeActor) => number,
		textsVariant: UiTextsVariant;
		initialCustomCapStrategy: CustomCapStrategy;
		initialLocalOnlyCapStrategy: LocalOnlyCapStrategy;
		customCapStrategyVisible: boolean;
		localOnlyCapStrategyVisible: boolean;
		customCapMergeStrategyAllowed: boolean;
	}

	const DefaultConfiguration: Configuration = {
		compareRolesForSorting: (a: IAmeActor, b: IAmeActor) => a.getDisplayName().localeCompare(b.getDisplayName()),
		textsVariant: UiTextsVariant.Import,
		initialCustomCapStrategy: actionAcceptIncoming,
		initialLocalOnlyCapStrategy: actionDisable,
		customCapStrategyVisible: true,
		localOnlyCapStrategyVisible: true,
		customCapMergeStrategyAllowed: true,
	}

	export class RoleMergeViewModel {
		readonly roleOptions: KnockoutObservableArray<RoleOption> = ko.observableArray<RoleOption>([]);
		readonly customCapStrategy: KnockoutObservable<CustomCapStrategy> = ko.observable<CustomCapStrategy>(actionAcceptIncoming);
		readonly localOnlyCapStrategy: KnockoutObservable<LocalOnlyCapStrategy> = ko.observable<LocalOnlyCapStrategy>(actionDisable);

		readonly customCapStrategyVisible: boolean;
		readonly localOnlyCapStrategyVisible: boolean;

		readonly selectAllChecked: KnockoutComputed<boolean>;
		readonly selectAllIndeterminate: KnockoutComputed<boolean>;
		readonly areAnyOptionsChecked: KnockoutComputed<boolean>;
		readonly selectedRoleNames: KnockoutComputed<string[]>;

		readonly customCapStrategyOptions: StrategyOption<CustomCapStrategy>[];
		readonly localOnlyCapStrategyOptions: StrategyOption<LocalOnlyCapStrategy>[];

		readonly textsVariant: UiTextsVariant;
		readonly changesAfterText: string;
		readonly localCapsTooltipText: string;

		constructor(
			protected readonly existingRoles: Record<string, IAmeRole>,
			protected readonly incomingRoles: IAmeRole[],
			allIncomingCapabilities: Set<string>,
			isCoreCapability: CoreCapabilityChecker,
			uiConfig: Partial<Configuration>
		) {
			const config = {...DefaultConfiguration, ...uiConfig};

			this.customCapStrategy(config.initialCustomCapStrategy);
			this.localOnlyCapStrategy(config.initialLocalOnlyCapStrategy);
			this.customCapStrategyVisible = config.customCapStrategyVisible;
			this.localOnlyCapStrategyVisible = config.localOnlyCapStrategyVisible;
			this.textsVariant = config.textsVariant;

			this.selectAllChecked = ko.computed({
				read: () => this.roleOptions().every(
					(opt) => opt.isChecked() || !opt.canBeChecked()
				),
				write: (value: boolean) => {
					this.roleOptions().forEach((opt) => {
						if (opt.canBeChecked()) {
							opt.isChecked(value);
						}
					});
				},
				deferEvaluation: true
			});

			this.selectAllIndeterminate = ko.pureComputed(() => {
				const options = this.roleOptions();
				if (options.length === 0) {
					return false;
				}
				let areAnyChecked = false;
				let areAnyUnchecked = false;
				for (const opt of options) {
					//Don't consider options that can't be checked.
					if (!opt.canBeChecked()) {
						continue;
					}

					if (opt.isChecked()) {
						areAnyChecked = true;
					} else {
						areAnyUnchecked = true;
					}

					if (areAnyChecked && areAnyUnchecked) {
						return true;
					}
				}

				return false;
			});

			this.areAnyOptionsChecked = ko.pureComputed(
				() => this.roleOptions().some(option => option.isChecked())
			);

			this.selectedRoleNames = ko.pureComputed(() => {
				return this.roleOptions()
					.filter(option => option.isChecked())
					.map(option => option.roles.baseRole
						? option.roles.baseRole.getRoleName()
						: option.roles.incomingRole.getRoleName()
					);
			});

			this.customCapStrategyOptions = [
				{value: actionLeaveUnchanged, label: 'Leave unchanged (usually the safest option)'},
				{value: actionDisable, label: 'Disable all custom capabilities for the selected roles'},
			];
			if (config.customCapMergeStrategyAllowed) {
				this.customCapStrategyOptions.splice(1, 0,
					{value: actionAcceptIncoming, label: 'Use settings from the imported roles'}
				);
			}

			this.localOnlyCapStrategyOptions = [
				{value: actionLeaveUnchanged, label: 'Leave unchanged'},
				{value: actionDisable, label: 'Disable all such capabilities for the selected roles'},
			];

			const roleOptions: RoleOption[] = [];
			const usedExistingRoles = new Set<IAmeRole>();
			const pickTextVariant = this.pickTextVariant.bind(this);

			for (const incomingRole of incomingRoles) {
				const name = incomingRole.getRoleName();
				const existingRole = existingRoles.hasOwnProperty(name) ? existingRoles[name] : null;

				if (existingRole) {
					usedExistingRoles.add(existingRole);
				}

				roleOptions.push(new RoleOption(
					{
						baseRole: existingRole,
						incomingRole: incomingRole
					},
					allIncomingCapabilities,
					isCoreCapability,
					this.customCapStrategy,
					this.localOnlyCapStrategy,
					pickTextVariant
				));
			}

			//Add any existing roles that have no counterpart in the incoming set.
			//These won't be modified, but the user might want to see them for reference.
			for (const existingRoleName in existingRoles) {
				if (existingRoles.hasOwnProperty(existingRoleName)) {
					const existingRole = existingRoles[existingRoleName];
					if (!usedExistingRoles.has(existingRole)) {
						roleOptions.push(new RoleOption(
							{
								baseRole: existingRole,
								incomingRole: null
							},
							allIncomingCapabilities,
							isCoreCapability,
							this.customCapStrategy,
							this.localOnlyCapStrategy,
							pickTextVariant
						));
					}
				}
			}

			//Sort the role list. This should be consistent with how roles are usually sorted
			//in the plugin UI.
			roleOptions.sort((a, b) => {
				const aRole = a.roles.baseRole || a.roles.incomingRole;
				const bRole = b.roles.baseRole || b.roles.incomingRole;
				return config.compareRolesForSorting(aRole, bRole);
			});

			this.roleOptions(roleOptions);

			this.changesAfterText = this.pickTextVariant({
				[UiTextsVariant.Import]: 'Changes after import',
				[UiTextsVariant.ResetRoles]: 'Changes after reset'
			});

			const tooltipPrefix = 'This setting controls what happens with capabilities that exist on this site but not ';
			this.localCapsTooltipText = this.pickTextVariant({
				[UiTextsVariant.Import]: tooltipPrefix + 'in the import file.',
				[UiTextsVariant.ResetRoles]: tooltipPrefix + 'in the default role definitions.'
			});
		}

		private pickTextVariant(options: { [key in UiTextsVariant]: string }): string {
			return options[this.textsVariant];
		}

		public getSelectedChanges(): Record<string, { changedCapabilities: CapabilityState, displayName: string }> {
			const result: ReturnType<typeof this.getSelectedChanges> = {};

			this.roleOptions()
				.filter(option => option.isChecked())
				.forEach(option => {
						const changes = option.computeChanges();
						result[option.roleName] = {
							changedCapabilities: changes.changedCapabilities,
							displayName: option.displayName()
						};
					}
				);

			return result;
		}
	}

	ko.components.register('rex-role-merge-view', {
		viewModel: {
			createViewModel: function (params: { model: RoleMergeViewModel | unknown }) {
				if (!params.model) {
					throw new Error('Missing "model" parameter');
				}
				const model = ko.unwrap(params.model);
				if (model instanceof RoleMergeViewModel) {
					//This allows updating everything in the component if the model observable is replaced.
					//If we just used the model directly, the component would be permanently bound to that
					//instance.
					return {model: params.model};
				}
				throw new Error('Invalid "model" parameter');
			}
		},
		template: `
			<div class="rex-role-merge-container" data-bind="with: model">
				<div class="rex-rm-section">
					<table class="widefat striped rex-rm-role-options">
						<thead>
						<tr>
							<th class="rex-rm-checkbox-col">
								<input type="checkbox"
								       data-bind="checked: selectAllChecked, indeterminate: selectAllIndeterminate"
								       id="rex-rm-toggle-all">
								<label for="rex-rm-toggle-all" class="screen-reader-text">Toggle all roles</label>
							</th>
							<th class="rex-rm-name-col">Name</th>
							<th class="rex-rm-change-col" data-bind="text: changesAfterText"></th>
						</tr>
						</thead>
						<tbody data-bind="foreach: roleOptions">
						<tr data-bind="css: {'rex-rm-checked-option': $data.isChecked()}">
							<th scope="row" class="rex-rm-checkbox-col">
								<!--suppress HtmlFormInputWithoutLabel -->
								<input type="checkbox" 
									   data-bind="checked: isChecked, attr: {id: $data.htmlId}, enable: canBeChecked">
							</th>
							<td>
								<label data-bind="attr: {for: $data.htmlId, title: $data.roleName}">
									<span data-bind="text: displayName"></span>
								</label>
							</td>
							<td data-bind="css: {'rex-rm-has-details': hasDetailsPanel, 'rex-rm-panel-open': isPanelOpen}">
								<span class="rex-rm-change-texts"
								      data-bind="click: function() { if (hasDetailsPanel()) { isPanelOpen(!isPanelOpen()); } }">
									<!-- ko foreach: $data.changeTexts -->
										<span data-bind="text: $data.text,
											css: {
												'rex-rm-positive-change': ($data.type === 'positive'),
												'rex-rm-negative-change': ($data.type === 'negative'),
												'rex-rm-no-changes':      ($data.type === 'none'),
											}"
										      class="rex-rm-change">
										</span><!--
											ko if: !$data.isLast --><span class="rex-rm-change-separator">,</span>
										<!-- /ko -->
									<!-- /ko -->
									
									<!-- ko if: hasDetailsPanel -->
										<span class="rex-rm-change-expand-indicator"></span>
									<!-- /ko -->
								</span>
								<!-- ko if: ($data.changeTexts().length === 0) -->
								<span class="rex-rm-no-changes">
									No changes
								</span>
								<!-- /ko -->
								<!-- ko if: hasDetailsPanel -->
								<div class="rex-rm-cap-details-panel" data-bind="visible: isPanelOpen">
								
									<!-- ko if: (addedCaps().length > 0) -->
									<div class="rex-rm-cap-details-section rex-rm-added-caps">
										<h4>Added capabilities</h4>
										<ul data-bind="foreach: $data.addedCaps">
											<li data-bind="text: $data"></li>
										</ul>
									</div>
									<!-- /ko -->
									
									<!-- ko if: (removedCaps().length > 0) -->									
									<div class="rex-rm-cap-details-section rex-rm-removed-caps">
										<h4>Removed capabilities</h4>
										<ul data-bind="foreach: $data.removedCaps">
											<li data-bind="text: $data"></li>
										</ul>
									</div>
									<!-- /ko -->
									
									<!-- ko if: (otherChanges().length > 0) -->
									<div class="rex-rm-cap-details-section rex-rm-other-changes">
										<h4>Other changes</h4>
										<table class="rex-rm-other-change-list">
											<tbody data-bind="foreach: $data.otherChanges">
												<tr>
													<td class="rex-rm-cap-name" data-bind="text: capName"></td>
													<td class="rex-rm-cap-old-state">
														<rex-role-merge-cap-state params="value: oldState"></rex-role-merge-cap-state>
													</td>
													<td class="rex-rm-cap-change-indicator">→</td>
													<td class="rex-rm-cap-new-state">
														<rex-role-merge-cap-state params="value: newState"></rex-role-merge-cap-state>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<!-- /ko -->				
														
								</div>
								<!-- /ko -->
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				
				<!-- ko if: customCapStrategyVisible -->
				<div class="rex-rm-section rex-dialog-section rex-rm-role-merging-settings">
					<h4>Custom capabilities</h4>
					<fieldset data-bind="foreach: customCapStrategyOptions">
						<p>
							<label>
								<input type="radio"
								       data-bind="checked: $parent.customCapStrategy, checkedValue: value">
								<span data-bind="text: label"></span>
							</label>
						</p>
					</fieldset>
				</div>
				<!-- /ko -->
				
				<!-- ko if: localOnlyCapStrategyVisible -->
				<div class="rex-rm-section rex-dialog-section rex-rm-role-merging-settings">
					<h4>
						Local-only capabilities 
						<a class="ws_tooltip_trigger" 
							data-bind="ameTooltip: {text: localCapsTooltipText}"><span class="dashicons dashicons-info"></span></a>
					</h4>
					<fieldset data-bind="foreach: localOnlyCapStrategyOptions">
						<p>
							<label>
								<input type="radio"
								       data-bind="checked: $parent.localOnlyCapStrategy, checkedValue: value">
								<span data-bind="text: label"></span>
							</label>
						</p>
					</fieldset>
				</div>
				<!-- /ko -->
			</div>
		`
	});

	//Capability state display was extracted into a component to make experimentation with different
	//representations easier and avoid duplication of the logic for old & new states.
	class CapStateDisplay {
		public readonly text: KnockoutComputed<string>;
		public readonly className: KnockoutComputed<string>;

		constructor(private readonly value: boolean | null | KnockoutObservable<boolean | null>) {
			this.text = ko.pureComputed<string>(() => {
				const val = ko.unwrap(this.value);
				if (val === true) {
					return 'enabled';
				} else if (val === false) {
					return 'denied';
				} else {
					return 'not set';
				}
			});

			this.className = ko.pureComputed<string>(() => {
				const val = ko.unwrap(this.value);
				if (val === true) {
					return 'rex-rm-cap-state-enabled';
				} else if (val === false) {
					return 'rex-rm-cap-state-denied';
				} else {
					return 'rex-rm-cap-state-not-set';
				}
			});
		}
	}

	ko.components.register('rex-role-merge-cap-state', {
		viewModel: {
			createViewModel: function (params: { value: boolean | null | KnockoutObservable<boolean | null> }) {
				return new CapStateDisplay(params.value);
			}
		},
		template: `<span data-bind="text: text, class: className"></span>`
	});
}