"use strict";
var AmeRoleMergeComponent;
(function (AmeRoleMergeComponent) {
    class CapabilityChanges {
        constructor() {
            this._changedCapabilities = {};
            this._addedCaps = new Set();
            this._removedCaps = new Set();
            this._otherChanges = {};
        }
        addCapabilityChange(capName, newState, oldState) {
            if (newState === oldState) {
                return;
            }
            this._changedCapabilities[capName] = newState;
            if (newState) {
                this._addedCaps.add(capName);
            }
            else if ((newState === null) && oldState) {
                this._removedCaps.add(capName);
            }
            else {
                this._otherChanges[capName] = { oldState, newState };
            }
        }
        get addedCaps() {
            return this._addedCaps;
        }
        get otherChanges() {
            return this._otherChanges;
        }
        get removedCaps() {
            return this._removedCaps;
        }
        get changedCapabilities() {
            return this._changedCapabilities;
        }
    }
    AmeRoleMergeComponent.actionLeaveUnchanged = 'leaveUnchanged';
    AmeRoleMergeComponent.actionAcceptIncoming = 'acceptIncoming';
    AmeRoleMergeComponent.actionDisable = 'disable';
    let UiTextsVariant;
    (function (UiTextsVariant) {
        UiTextsVariant["Import"] = "import";
        UiTextsVariant["ResetRoles"] = "resetRoles";
    })(UiTextsVariant = AmeRoleMergeComponent.UiTextsVariant || (AmeRoleMergeComponent.UiTextsVariant = {}));
    const { _n, sprintf } = wp.i18n;
    let optionInstanceCounter = 0;
    class RoleOption {
        constructor(roles, allIncomingCapabilities, isCoreCapability, customCapStrategy, localOnlyCapStrategy, pickTextVariant) {
            this.roles = roles;
            this.allIncomingCapabilities = allIncomingCapabilities;
            this.isCoreCapability = isCoreCapability;
            this.customCapStrategy = customCapStrategy;
            this.localOnlyCapStrategy = localOnlyCapStrategy;
            this.isChecked = ko.observable(false);
            // noinspection JSUnusedGlobalSymbols // Used in the KO template.
            this.isPanelOpen = ko.observable(false);
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
            this.htmlId = 'rex-rm-role-option--' + (this.roles.baseRole ? this.roles.baseRole.getRoleName() : 'new-' + this.roles.incomingRole.getRoleName()) + '--' + instanceId;
            const computedChanges = ko.pureComputed(() => this.computeChanges());
            this.changeTexts = ko.pureComputed(() => {
                const changes = computedChanges();
                const texts = [];
                if (this.roles.incomingRole === null) {
                    //No incoming role, so no changes.
                    texts.push({
                        text: pickTextVariant({
                            [UiTextsVariant.Import]: 'No changes (not in the import file)',
                            [UiTextsVariant.ResetRoles]: 'No changes (no default role data)'
                        }),
                        type: 'none',
                        isLast: true
                    });
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
                        text: sprintf(_n('+%d capability', '+%d capabilities', addedCapCount), addedCapCount),
                        type: 'positive'
                    });
                }
                const removedCapCount = changes.removedCaps.size;
                if (removedCapCount > 0) {
                    texts.push({
                        text: sprintf(_n('-%d capability', '-%d capabilities', removedCapCount), removedCapCount),
                        type: 'negative'
                    });
                }
                const otherChangesCount = Object.keys(changes.otherChanges).length;
                if (otherChangesCount > 0) {
                    texts.push({
                        text: sprintf(_n('%d other change', '%d other changes', otherChangesCount), otherChangesCount),
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
                return (this.addedCaps().length > 0
                    || this.removedCaps().length > 0
                    || this.otherChanges().length > 0);
            });
            this.roleName = this.roles.baseRole ? this.roles.baseRole.getRoleName() : this.roles.incomingRole.getRoleName();
        }
        /**
         * Compute capability changes based on the current strategy settings.
         */
        computeChanges() {
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
            const relevantCaps = new Set([
                ...Object.keys(baseCaps),
                ...Object.keys(incomingCaps)
            ]);
            relevantCaps.forEach((capName) => {
                const baseState = baseCaps[capName] ?? null;
                const incomingState = incomingCaps[capName] ?? null;
                const isCustomCap = !this.isCoreCapability(capName);
                const isLocalOnlyCap = (baseCaps
                    && baseCaps.hasOwnProperty(capName)
                    && !this.allIncomingCapabilities.has(capName));
                //Default action is "merge" for all capabilities.
                //This means that if the incoming role has a different value for the capability
                //than the base role, the incoming value will be used.
                let action = AmeRoleMergeComponent.actionAcceptIncoming;
                //Custom cap strategy can optionally override the action for custom capabilities.
                if (isCustomCap) {
                    action = customCapStrategy;
                }
                //Local-only cap strategy applies to caps that exist on this site but not on the site
                //from which the incoming role was imported. The custom cap strategy can override this
                //if it's set to anything other than the default "accept incoming" action.
                if (isLocalOnlyCap && (action === AmeRoleMergeComponent.actionAcceptIncoming)) {
                    action = localOnlyCapStrategy;
                }
                switch (action) {
                    case AmeRoleMergeComponent.actionLeaveUnchanged:
                        //Do nothing.
                        break;
                    case AmeRoleMergeComponent.actionDisable:
                        if (baseState !== null) {
                            changes.addCapabilityChange(capName, null, baseState);
                        }
                        break;
                    case AmeRoleMergeComponent.actionAcceptIncoming:
                        if (incomingState !== baseState) {
                            changes.addCapabilityChange(capName, incomingState, baseState);
                        }
                        break;
                }
            });
            return changes;
        }
    }
    const DefaultConfiguration = {
        compareRolesForSorting: (a, b) => a.getDisplayName().localeCompare(b.getDisplayName()),
        textsVariant: UiTextsVariant.Import,
        initialCustomCapStrategy: AmeRoleMergeComponent.actionAcceptIncoming,
        initialLocalOnlyCapStrategy: AmeRoleMergeComponent.actionDisable,
        customCapStrategyVisible: true,
        localOnlyCapStrategyVisible: true,
        customCapMergeStrategyAllowed: true,
    };
    class RoleMergeViewModel {
        constructor(existingRoles, incomingRoles, allIncomingCapabilities, isCoreCapability, uiConfig) {
            this.existingRoles = existingRoles;
            this.incomingRoles = incomingRoles;
            this.roleOptions = ko.observableArray([]);
            this.customCapStrategy = ko.observable(AmeRoleMergeComponent.actionAcceptIncoming);
            this.localOnlyCapStrategy = ko.observable(AmeRoleMergeComponent.actionDisable);
            const config = { ...DefaultConfiguration, ...uiConfig };
            this.customCapStrategy(config.initialCustomCapStrategy);
            this.localOnlyCapStrategy(config.initialLocalOnlyCapStrategy);
            this.customCapStrategyVisible = config.customCapStrategyVisible;
            this.localOnlyCapStrategyVisible = config.localOnlyCapStrategyVisible;
            this.textsVariant = config.textsVariant;
            this.selectAllChecked = ko.computed({
                read: () => this.roleOptions().every((opt) => opt.isChecked() || !opt.canBeChecked()),
                write: (value) => {
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
                    }
                    else {
                        areAnyUnchecked = true;
                    }
                    if (areAnyChecked && areAnyUnchecked) {
                        return true;
                    }
                }
                return false;
            });
            this.areAnyOptionsChecked = ko.pureComputed(() => this.roleOptions().some(option => option.isChecked()));
            this.selectedRoleNames = ko.pureComputed(() => {
                return this.roleOptions()
                    .filter(option => option.isChecked())
                    .map(option => option.roles.baseRole
                    ? option.roles.baseRole.getRoleName()
                    : option.roles.incomingRole.getRoleName());
            });
            this.customCapStrategyOptions = [
                { value: AmeRoleMergeComponent.actionLeaveUnchanged, label: 'Leave unchanged (usually the safest option)' },
                { value: AmeRoleMergeComponent.actionDisable, label: 'Disable all custom capabilities for the selected roles' },
            ];
            if (config.customCapMergeStrategyAllowed) {
                this.customCapStrategyOptions.splice(1, 0, { value: AmeRoleMergeComponent.actionAcceptIncoming, label: 'Use settings from the imported roles' });
            }
            this.localOnlyCapStrategyOptions = [
                { value: AmeRoleMergeComponent.actionLeaveUnchanged, label: 'Leave unchanged' },
                { value: AmeRoleMergeComponent.actionDisable, label: 'Disable all such capabilities for the selected roles' },
            ];
            const roleOptions = [];
            const usedExistingRoles = new Set();
            const pickTextVariant = this.pickTextVariant.bind(this);
            for (const incomingRole of incomingRoles) {
                const name = incomingRole.getRoleName();
                const existingRole = existingRoles.hasOwnProperty(name) ? existingRoles[name] : null;
                if (existingRole) {
                    usedExistingRoles.add(existingRole);
                }
                roleOptions.push(new RoleOption({
                    baseRole: existingRole,
                    incomingRole: incomingRole
                }, allIncomingCapabilities, isCoreCapability, this.customCapStrategy, this.localOnlyCapStrategy, pickTextVariant));
            }
            //Add any existing roles that have no counterpart in the incoming set.
            //These won't be modified, but the user might want to see them for reference.
            for (const existingRoleName in existingRoles) {
                if (existingRoles.hasOwnProperty(existingRoleName)) {
                    const existingRole = existingRoles[existingRoleName];
                    if (!usedExistingRoles.has(existingRole)) {
                        roleOptions.push(new RoleOption({
                            baseRole: existingRole,
                            incomingRole: null
                        }, allIncomingCapabilities, isCoreCapability, this.customCapStrategy, this.localOnlyCapStrategy, pickTextVariant));
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
        pickTextVariant(options) {
            return options[this.textsVariant];
        }
        getSelectedChanges() {
            const result = {};
            this.roleOptions()
                .filter(option => option.isChecked())
                .forEach(option => {
                const changes = option.computeChanges();
                result[option.roleName] = {
                    changedCapabilities: changes.changedCapabilities,
                    displayName: option.displayName()
                };
            });
            return result;
        }
    }
    AmeRoleMergeComponent.RoleMergeViewModel = RoleMergeViewModel;
    ko.components.register('rex-role-merge-view', {
        viewModel: {
            createViewModel: function (params) {
                if (!params.model) {
                    throw new Error('Missing "model" parameter');
                }
                const model = ko.unwrap(params.model);
                if (model instanceof RoleMergeViewModel) {
                    //This allows updating everything in the component if the model observable is replaced.
                    //If we just used the model directly, the component would be permanently bound to that
                    //instance.
                    return { model: params.model };
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
        constructor(value) {
            this.value = value;
            this.text = ko.pureComputed(() => {
                const val = ko.unwrap(this.value);
                if (val === true) {
                    return 'enabled';
                }
                else if (val === false) {
                    return 'denied';
                }
                else {
                    return 'not set';
                }
            });
            this.className = ko.pureComputed(() => {
                const val = ko.unwrap(this.value);
                if (val === true) {
                    return 'rex-rm-cap-state-enabled';
                }
                else if (val === false) {
                    return 'rex-rm-cap-state-denied';
                }
                else {
                    return 'rex-rm-cap-state-not-set';
                }
            });
        }
    }
    ko.components.register('rex-role-merge-cap-state', {
        viewModel: {
            createViewModel: function (params) {
                return new CapStateDisplay(params.value);
            }
        },
        template: `<span data-bind="text: text, class: className"></span>`
    });
})(AmeRoleMergeComponent || (AmeRoleMergeComponent = {}));
//# sourceMappingURL=role-merge.js.map