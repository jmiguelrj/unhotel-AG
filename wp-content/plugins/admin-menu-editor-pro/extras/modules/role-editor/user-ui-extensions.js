"use strict";
var AmeRexUserUiExtensions;
(function (AmeRexUserUiExtensions) {
    jQuery(function ($) {
        if (!$) {
            return;
        }
        const $defaultRoleSelect = $('.user-role-wrap select#role, #createuser select#role, #adduser select#adduser-role').first(), $defaultRoleRow = $defaultRoleSelect.closest('tr'), $otherRolesRow = $('#ame-rex-other-roles-row'), $extraTable = $('#ame-rex-user-profile-fields'), $extraUiContainers = $extraTable.add($('#ame-rex-additional-caps-heading'));
        if ($defaultRoleSelect.length === 0) {
            //This is some kind of unsupported/unexpected UI, so we won't try to modify it.
            $extraUiContainers.remove();
            return;
        }
        //Move the extra field(s) below the role selection.
        $defaultRoleRow.after($otherRolesRow);
        //If the default "Capabilities" row exists, move the edit link there and delete our version.
        const $defaultCapsRow = $('tr.user-capabilities-wrap'), $editUserLink = $('#ame-rex-edit-user-link');
        if (($defaultCapsRow.length > 0) && ($editUserLink.length > 0)) {
            const $editLinkRow = $editUserLink.closest('tr');
            $defaultCapsRow.find('td').append('<br/>').append($editUserLink);
            $editLinkRow.remove();
        }
        //If everything gets moved, our table will be empty and the table + its heading can be removed.
        if ($extraTable.find('tr').length === 0) {
            $extraUiContainers.remove();
        }
        const $otherRolesSelect = $('#ame-rex-other-roles');
        if ($otherRolesSelect.length > 0) {
            const selectElement = $otherRolesSelect.get(0);
            const choices = new Choices(selectElement, {
                removeItemButton: true,
                searchEnabled: true,
                itemSelectText: '',
                addChoices: false,
                duplicateItemsAllowed: false,
                placeholderValue: '', //Shown in the text box (even when there are selected items).
                noChoicesText: 'No roles left',
                removeItemIconText: '',
                //Note: It would be convenient to set "classNames" here, but the type definition
                //for that option requires setting *all* class names, which is tedious and fragile.
            });
            //Automatically deselect the primary role in the other-roles select when the primary
            //role changes.
            function deselectPrimaryRoleInOtherRoles() {
                const primaryRole = $defaultRoleSelect.val();
                if (primaryRole !== '') {
                    choices.removeActiveItemsByValue(primaryRole);
                }
            }
            $defaultRoleSelect.on('change', function () {
                deselectPrimaryRoleInOtherRoles();
            });
            //Prevent the user from selecting the primary role in the other-roles select.
            //Unfortunately, Choices.js doesn't seem to provide a way to dynamically disable specific
            //options, so we just remove the item if it gets added.
            selectElement.addEventListener('addItem', function (event) {
                if ((event instanceof CustomEvent)
                    && (typeof event.detail === 'object')
                    && (event.detail !== null)) {
                    const addedValue = event.detail.value;
                    const primaryRole = $defaultRoleSelect.val();
                    if ((addedValue === primaryRole) && (primaryRole !== '')) {
                        //Remove it again.
                        choices.removeActiveItemsByValue(addedValue);
                    }
                }
            });
            //Initial sync.
            deselectPrimaryRoleInOtherRoles();
        }
    });
})(AmeRexUserUiExtensions || (AmeRexUserUiExtensions = {}));
//# sourceMappingURL=user-ui-extensions.js.map