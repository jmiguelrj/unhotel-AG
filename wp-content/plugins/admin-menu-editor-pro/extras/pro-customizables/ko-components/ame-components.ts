/*
 * This utility module imports all the base Knockout components and exports
 * a function that can be used to register the components with Knockout.
 */

import ameBoxDimensions from './ame-box-dimensions/ame-box-dimensions.js';
import ameColorPicker from './ame-color-picker/ame-color-picker.js';
import ameFontStylePicker from './ame-font-style-picker/ame-font-style-picker.js';
import ameImageSelector from './ame-image-selector/ame-image-selector.js';
import ameNumberInput from './ame-number-input/ame-number-input.js';
import ameNestedDescription from './ame-nested-description/ame-nested-description.js';
import ameRadioButtonBar from './ame-radio-button-bar/ame-radio-button-bar.js';
import ameRadioGroup from './ame-radio-group/ame-radio-group.js';
import ameSelectBox from './ame-select-box/ame-select-box.js';
import ameSiblingDescription from './ame-sibling-description/ame-sibling-description.js';
import ameStaticHtml from './ame-static-html/ame-static-html.js';
import ameTextInput from './ame-text-input/ame-text-input.js';
import ameToggleCheckbox from './ame-toggle-checkbox/ame-toggle-checkbox.js';
import ameUnitDropdown from './ame-unit-dropdown/ame-unit-dropdown.js';
import ameWpEditor from './ame-wp-editor/ame-wp-editor.js';
import ameHorizontalSeparator from './ame-horizontal-separator/ame-horizontal-separator.js';
import ameCodeEditor from './ame-code-editor/ame-code-editor.js';
import ameTooltipTrigger from './ame-tooltip-trigger/ame-tooltip-trigger.js';
import ameActorFeatureCheckbox from './ame-actor-feature-checkbox/ame-actor-feature-checkbox.js';
import ameEventButton    from './ame-event-button/ame-event-button.js';

import amePlaceholder from './ame-placeholder/ame-placeholder.js';
import ameGeneralControlGroup from './ame-general-control-group/ame-general-control-group.js';
import ameGeneralStructure from './ame-general-structure/ame-general-structure.js';
import amePostboxSection from './ame-postbox-section/ame-postbox-section.js';

let componentsRegistered = false;

/**
 * Register the base Knockout components that are part of AME.
 *
 * It's safe to call this function multiple times. It will only register the components once.
 */
export function registerBaseComponents(): void {
	if (componentsRegistered) {
		return;
	}

	ko.components.register('ame-placeholder', amePlaceholder);
	ko.components.register('ame-general-control-group', ameGeneralControlGroup);
	ko.components.register('ame-general-structure', ameGeneralStructure);

	ko.components.register('ame-box-dimensions', ameBoxDimensions);
	ko.components.register('ame-color-picker', ameColorPicker);
	ko.components.register('ame-font-style-picker', ameFontStylePicker);
	ko.components.register('ame-image-selector', ameImageSelector);
	ko.components.register('ame-number-input', ameNumberInput);
	ko.components.register('ame-nested-description', ameNestedDescription);
	ko.components.register('ame-radio-button-bar', ameRadioButtonBar);
	ko.components.register('ame-radio-group', ameRadioGroup);
	ko.components.register('ame-select-box', ameSelectBox);
	ko.components.register('ame-sibling-description', ameSiblingDescription);
	ko.components.register('ame-static-html', ameStaticHtml);
	ko.components.register('ame-text-input', ameTextInput);
	ko.components.register('ame-toggle-checkbox', ameToggleCheckbox);
	ko.components.register('ame-unit-dropdown', ameUnitDropdown);
	ko.components.register('ame-wp-editor', ameWpEditor);
	ko.components.register('ame-horizontal-separator', ameHorizontalSeparator);
	ko.components.register('ame-code-editor', ameCodeEditor);
	ko.components.register('ame-actor-feature-checkbox', ameActorFeatureCheckbox);
	ko.components.register('ame-event-button', ameEventButton);
	ko.components.register('ame-postbox-section', amePostboxSection);

	ko.components.register('ame-tooltip-trigger', ameTooltipTrigger);

	componentsRegistered = true;
}