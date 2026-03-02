"use strict";
jQuery(function ($) {
    //We only care about the import form on step 2 (options selection).
    const $importForm = $('form#ame-import-step-2');
    if ($importForm.length === 0) {
        return;
    }
    console.log(wsAmeRoleImportHelperData);
    const $configContainer = $importForm.find('#ame-rex-import-roles-merge-container');
    class ImportHelper {
        constructor() {
            const coreCapsSet = new Set(wsAmeRoleImportHelperData.coreCapabilities);
            const incomingRoleData = wsAmeRoleImportHelperData.incomingRoleData;
            const existingRoles = AmeActors.getRoles();
            const incomingRoles = Object.values(incomingRoleData.roles).map(roleData => new AmeRole(roleData.name, roleData.displayName, roleData.capabilities));
            this.importMergeVm = new AmeRoleMergeComponent.RoleMergeViewModel(existingRoles, incomingRoles, new Set(incomingRoleData.capabilityIndex), (capName) => coreCapsSet.has(capName), {
                textsVariant: AmeRoleMergeComponent.UiTextsVariant.Import,
                compareRolesForSorting: AmeActors.compareRolesForSorting,
                localOnlyCapStrategyVisible: true,
                initialLocalOnlyCapStrategy: AmeRoleMergeComponent.actionLeaveUnchanged,
                customCapStrategyVisible: false,
                initialCustomCapStrategy: AmeRoleMergeComponent.actionAcceptIncoming,
            });
            //Start with all roles selected.
            this.importMergeVm.selectAllChecked(true);
            this.configFieldValue = ko.pureComputed(() => {
                //Include selected roles and the chosen capability strategy.
                return JSON.stringify({
                    roles: this.importMergeVm.roleOptions()
                        .filter(opt => opt.isChecked())
                        .map(opt => opt.roleName),
                    localOnlyCapStrategy: this.importMergeVm.localOnlyCapStrategy(),
                });
            });
        }
    }
    const importHelper = new ImportHelper();
    ko.applyBindings(importHelper, $configContainer[0]);
});
//# sourceMappingURL=import-helper.js.map