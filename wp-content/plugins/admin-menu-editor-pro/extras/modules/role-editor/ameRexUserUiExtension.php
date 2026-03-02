<?php

use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;
use YahnisElsts\WpDependencyWrapper\v1\ScriptDependency;
use function YahnisElsts\AdminMenuEditor\Collections\w;

class ameRexUserUiExtension {
	const OTHER_ROLES_FIELD = 'ame_rex_other_roles';
	const OTHER_ROLES_NONCE_ACTION = 'ame_rex_update_other_roles';
	const OTHER_ROLES_NONCE_FIELD = 'ame_rex_other_roles_nonce';

	private $featureSettings;
	/**
	 * @var ameRoleEditor
	 */
	private $module;
	/**
	 * @var WPMenuEditor
	 */
	private $menuEditor;

	public function __construct(array $featureSettingsData, ameRoleEditor $module, WPMenuEditor $menuEditor) {
		$this->featureSettings = new ameRexUserUiFeatures($featureSettingsData);
		$this->module = $module;
		$this->menuEditor = $menuEditor;

		$shouldEnqueueDependencies = false;

		if ( $this->featureSettings->isAnyEnabled([
			ameRexUserUiFeatures::OTHER_ROLES_ON_EDIT_SCREEN,
			ameRexUserUiFeatures::EDIT_LINK_ON_EDIT_SCREEN,
		]) ) {
			add_action('edit_user_profile', [$this, 'outputProfileFields']);
			add_action('profile_update', [$this, 'saveOtherRoles']);
			$shouldEnqueueDependencies = true;
		}

		if ( $this->featureSettings->isEnabled(ameRexUserUiFeatures::OTHER_ROLES_ON_ADD_SCREEN) ) {
			add_action('user_new_form', [$this, 'outputNewUserFields']);
			add_action('user_register', [$this, 'saveOtherRoles']);
			if ( is_multisite() ) {
				add_action('added_existing_user', [$this, 'maybeSaveOtherRolesForAddedUser'], 10, 2);
			}
			$shouldEnqueueDependencies = true;
		}

		if ( $shouldEnqueueDependencies ) {
			add_action('admin_enqueue_scripts', [$this, 'enqueueEditUserDependencies']);
		}

		if ( $this->featureSettings->isEnabled(ameRexUserUiFeatures::CAPABILITIES_LINK_IN_USERS_TABLE) ) {
			add_filter('user_row_actions', [$this, 'addCapabilitiesLinkToUserRow'], 10, 2);
		}
	}

	public function enqueueEditUserDependencies() {
		$baseDeps = $this->menuEditor->get_base_dependencies();

		$scriptData = []; //Nothing yet.

		ScriptDependency::create(
			plugins_url('user-ui-extensions.js', __FILE__),
			'ame-rex-user-ui',
			__DIR__ . '/user-ui-extensions.js',
			['jquery', $baseDeps['ame-choices-js']]
		)
			->addJsVariable('wsAmeUserUiExtensionsData', $scriptData)
			->enqueue();

		wp_enqueue_auto_versioned_style(
			'ame-choices-css',
			plugins_url('extras/choices-js/choices.min.css', $this->menuEditor->plugin_file)
		);

		wp_enqueue_auto_versioned_style(
			'ame-rex-user-ui-style',
			plugins_url('user-ui-extensions.css', __FILE__)
		);
	}

	public function outputProfileFields($user) {
		if ( !($user instanceof WP_User) || !current_user_can('promote_user', $user->ID) ) {
			return;
		}
		$this->outputUserUiFields($user);
	}

	public function outputNewUserFields() {
		$this->outputUserUiFields(null);
	}

	/**
	 * @param WP_User|null $user
	 */
	private function outputUserUiFields(?WP_User $user) {
		if ( !current_user_can('promote_users') ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen === null ) {
			return;
		}
		if ( $screen->base === 'user-edit' ) {
			$otherRolesEnabled = $this->featureSettings->isEnabled(ameRexUserUiFeatures::OTHER_ROLES_ON_EDIT_SCREEN);
			$editLinkEnabled = $this->featureSettings->isEnabled(ameRexUserUiFeatures::EDIT_LINK_ON_EDIT_SCREEN);
			$rolesFeatureLabel = '\'Show the "Other Roles" field\' under "Edit User screen"';
		} else if ( ($screen->base === 'user') && ($screen->action === 'add') ) {
			$otherRolesEnabled = $this->featureSettings->isEnabled(ameRexUserUiFeatures::OTHER_ROLES_ON_ADD_SCREEN);
			$editLinkEnabled = false;
			$rolesFeatureLabel = '\'Show the "Other Roles" field\' under "Add User screen"';
		} else {
			return;
		}

		//The "Edit" link won't work if the user doesn't exist yet.
		if ( !$user || !$user->exists() ) {
			$editLinkEnabled = false;
		}

		if ( !$otherRolesEnabled && !$editLinkEnabled ) {
			return;
		}

		$tooltipText = "Select additional roles for this user. The primary role is set using the standard Role dropdown above."
			. "\n\n"
			. "This field was added by the Admin Menu Editor Pro plugin. If you want to remove it:\n"
			. "1. Go to the \"Roles\" tab in AME settings\n"
			. "2. Click the \"User screens\" button on the right\n"
			. "3. Uncheck $rolesFeatureLabel";

		$wpRoles = wp_roles();
		?>
		<h2 id="ame-rex-additional-caps-heading"><?php _e('Additional Capabilities'); ?></h2>
		<table class="form-table" id="ame-rex-user-profile-fields">
			<?php if ( $otherRolesEnabled ): ?>
				<tr id="ame-rex-other-roles-row">
					<th scope="row">
						<label for="ame-rex-other-roles">Other Roles</label>
						<span title="<?php echo esc_attr($tooltipText); ?>"
						      class="ame-rex-tooltip-trigger"><span
								class="dashicons dashicons-editor-help"></span></span>
					</th>
					<td>
						<div id="ame-rex-other-roles-field-wrapper">
							<select name="<?php echo esc_attr(self::OTHER_ROLES_FIELD); ?>[]"
							        id="ame-rex-other-roles"
							        multiple="multiple" size="7">
								<?php
								//Note: Doesn't include special bbPress roles. bbPress probably has its own
								//way of handling user editing, and we'll try not to interfere with that.
								$editableRoles = get_editable_roles();

								if ( $user ) {
									$editableSelectedRoles = w($user->roles)->intersect(w($editableRoles)->keys());
									$primaryRole = $editableSelectedRoles->headOrNull();
								} else {
									$editableSelectedRoles = w([]);
									$primaryRole = null;
								}

								/*
								Note: The JS script will need to ensure the user can't select the primary role
								in the list of other roles. The primary role for new users doesn't *always* match
								the default role (it can also be set via $_POST['role']), and the user can change
								it before submitting the form.
								*/

								foreach ($editableRoles as $role => $details) {
									$isSelected = $editableSelectedRoles->contains($role) && ($role !== $primaryRole);

									echo HtmlHelper::tag(
										'option',
										[
											//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- tag() escapes attributes.
											'value'    => $role,
											'selected' => $isSelected ? 'selected' : null,
										],
										esc_html(translate_user_role($details['name']))
									);
								}
								?>
							</select>
						</div>
						<?php
						/*
						A nonce is *probably* not needed here since WP already does a nonce check
						when updating/adding a user, but I'll add a separate one for this field
						in case there's some scenario I haven't thought of.
						Note: No referrer field for this nonce because WP already adds one to
						the user profile form.
						*/
						wp_nonce_field(self::OTHER_ROLES_NONCE_ACTION, self::OTHER_ROLES_NONCE_FIELD, false);
						?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( $editLinkEnabled ): ?>
				<tr id="ame-rex-additional-capabilities-row">
					<th scope="row"><?php _e('Capabilities'); ?></th>
					<td>
						<?php
						$capsOutputHtml = w($user->caps)
							->rejectKeys([$wpRoles, 'is_role'])
							->map(function ($enabled, $cap) {
								//Uses existing WP core translations.
								return $enabled ? $cap : sprintf(__('Denied: %s'), $cap);
							})
							->implode(', ');

						if ( !empty($capsOutputHtml) ) {
							echo esc_html($capsOutputHtml) . '<br>';
						}

						printf(
							'<a id="ame-rex-edit-user-link" href="%1$s">%2$s</a>',
							esc_url($this->getEditUserCapsUrl($user)),
							esc_html('Edit')
						);
						?>
					</td>
				</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Handle saving the "Other Roles" field for existing and new users.
	 *
	 * @param $userId
	 */
	public function saveOtherRoles($userId) {
		if (
			empty($userId)
			|| !current_user_can('edit_user', $userId)
			|| !current_user_can('promote_user', $userId)
			|| empty($_POST[self::OTHER_ROLES_NONCE_FIELD])
		) {
			return;
		}
		if ( !check_admin_referer(self::OTHER_ROLES_NONCE_ACTION, self::OTHER_ROLES_NONCE_FIELD) ) {
			return; //Probably unreachable because WP will die with an "Are you sure?" message first.
		}
		$user = get_userdata($userId);
		if ( !$user ) {
			return;
		}

		$validOtherRoles = w($_POST)
			->get(self::OTHER_ROLES_FIELD)
			->ensureArray()
			//Basic sanitization.
			->mapValues('strval')
			//Note: This part *does* include dynamic bbPress roles, in case they somehow end up in
			//the list. We don't want to accidentally remove them from the user.
			->intersect(array_keys($this->module->getEffectiveEditableRoles()))
			->strictDiff(is_array($user->roles) ? $user->roles : []);

		//Since this hook runs after WP has already updated the user, we can assume WP has already set
		//the primary role and removed any extra roles. So we just need to add the selected roles.
		foreach ($validOtherRoles as $roleId) {
			$user->add_role($roleId);
		}
	}

	public function maybeSaveOtherRolesForAddedUser($userId, $addResult = null) {
		//It looks like WP triggers the "added_existing_user" action even if it *failed* to add
		//the user, so we need to check the result before proceeding.
		if ( is_wp_error($addResult) ) {
			return;
		}
		$this->saveOtherRoles($userId);
	}

	public function addCapabilitiesLinkToUserRow($actions, $user = null) {
		if ( !($user instanceof WP_User) || !current_user_can('edit_user', $user->ID) ) {
			return $actions;
		}
		$actions['ame-capabilities'] = sprintf(
			'<a href="%s" title="%s">Capabilities</a>',
			esc_url($this->getEditUserCapsUrl($user)),
			esc_attr('Edit user capabilities with Admin Menu Editor Pro')
		);
		return $actions;
	}

	private function getEditUserCapsUrl(WP_User $user): string {
		return $this->module->getTabUrl(['selected_actor' => 'user:' . $user->user_login]);
	}
}

class ameRexUserUiFeatures {
	const OTHER_ROLES_ON_EDIT_SCREEN = 'otherRolesOnEditScreen';
	const OTHER_ROLES_ON_ADD_SCREEN = 'otherRolesOnAddScreen';
	const EDIT_LINK_ON_EDIT_SCREEN = 'editLinkOnEditScreen';
	const CAPABILITIES_LINK_IN_USERS_TABLE = 'capabilitiesLinkInUsersTable';

	/**
	 * @var array<string,boolean>
	 */
	private $settings;

	public function __construct(array $featureSettings) {
		$this->settings = $featureSettings;
	}

	public function isEnabled($featureConstant): bool {
		if ( isset($this->settings[$featureConstant]) ) {
			return (bool)$this->settings[$featureConstant];
		}
		return true; //Everything enabled by default.
	}

	public function isAnyEnabled(array $constants): bool {
		foreach ($constants as $constant) {
			if ( $this->isEnabled($constant) ) {
				return true;
			}
		}
		return false;
	}

	public static function getSupportedFeatureFlags(): array {
		return [
			self::OTHER_ROLES_ON_EDIT_SCREEN,
			self::OTHER_ROLES_ON_ADD_SCREEN,
			self::EDIT_LINK_ON_EDIT_SCREEN,
			self::CAPABILITIES_LINK_IN_USERS_TABLE,
		];
	}
}