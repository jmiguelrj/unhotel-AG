<?php

namespace YahnisElsts\AdminMenuEditor\ImportExport;

use ameMenu, ameModule, ameUtils;
use WP_Error;
use YahnisElsts\AdminMenuEditor\Customizable\Schemas\SchemaFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary;
use YahnisElsts\WpDependencyWrapper\v1\ScriptDependency;

class wsAmeImportExportFeature {
	const UPLOAD_FILE_ACTION = 'ame_upload_settings';
	const IMPORT_FILE_ACTION = 'ame_import_uploaded_settings';
	const EXPORT_ACTION = 'ame_export_settings';

	const EXPAND_IMPORT_CONFIGS_BY_DEFAULT = false;

	public static $export_container_format_name = 'Admin Menu Editor configuration container';
	private static $export_container_format_version = '1.0';

	/**
	 * @var \WPMenuEditor
	 */
	private $wp_menu_editor;

	private $exportable_global_options = array(
		'hide_advanced_settings',
		'ui_colour_scheme',
		'submenu_icons_enabled',
		'unused_item_position',
		'compress_custom_menu',
		'dashboard_hiding_confirmation_enabled',
	);

	private static $last_instance = null;

	protected function __construct($menuEditor) {
		$this->wp_menu_editor = $menuEditor;

		add_action('admin_menu_editor-header', array($this, 'menu_editor_header'), 10, 2);

		add_filter('admin_menu_editor-tabs', array($this, 'add_import_export_tabs'), 30);
		add_action('admin_menu_editor-section-import', array($this, 'display_import_tab'));
		add_action('admin_menu_editor-section-export', array($this, 'display_export_tab'));
		add_action('admin_menu_editor-clean_up_import', array($this, 'clean_up_import_data'), 10, 3);

		add_action('admin_menu_editor-register_scripts', array($this, 'register_scripts'));
		foreach (array('import', 'export') as $tabSlug) {
			add_action('admin_menu_editor-enqueue_scripts-' . $tabSlug, array($this, 'enqueue_tab_scripts'));
			add_action('admin_menu_editor-enqueue_styles-' . $tabSlug, array($this, 'enqueue_tab_styles'));
		}
	}

	public function menu_editor_header($action = '', $post = array()) {
		//Handle universal export.
		if ( $action === self::EXPORT_ACTION ) {
			$this->handle_export_request($action, $post);
		}
	}

	public function export_data(): array {
		$components = $this->get_exportable_components();
		$settings = array();

		foreach ($components as $id => $component) {
			$exportedData = $component->export();
			if ( $exportedData !== null ) {
				$settings[$id] = $exportedData;
			}
		}

		$settings = apply_filters('admin_menu_editor-exported_data', $settings);

		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$container = array(
			'format'   => array(
				'name'    => self::$export_container_format_name,
				'version' => self::$export_container_format_version,
			),
			'settings' => $settings,
		);

		return $container;
	}

	/**
	 * @param array $container
	 * @param array|null $enabledComponents
	 * @param array|null $componentConfigs
	 * @return array<string,ameImportResult>
	 */
	public function import_data(array $container, ?array $enabledComponents = null, ?array $componentConfigs = null): array {
		$status = array_fill_keys(array_keys($container['settings']), ameImportResult::skipped());

		$settings = $container['settings'];
		$components = $this->get_exportable_components();

		//Sort by import priority (highest first).
		//This currently doesn't affect much, but it could be useful if we ever dynamically
		//activate or deactivate modules when importing "enabled modules" settings.
		uasort($components, function (ameExportableComponent $a, ameExportableComponent $b) {
			return ($b->getImportPriority() - $a->getImportPriority());
		});

		foreach ($components as $id => $component) {
			if ( isset($enabledComponents) && empty($enabledComponents[$id]) ) {
				continue;
			}

			if ( empty($settings[$id]) || !is_array($settings[$id]) ) {
				continue;
			}

			$status[$id] = $component->import(
				$settings[$id],
				isset($componentConfigs, $componentConfigs[$id]) ? $componentConfigs[$id] : null
			);
		}

		return $status;
	}

	private function handle_export_request($action, $post) {
		check_admin_referer($action);

		$enabledOptions = array();
		foreach (ameUtils::get($post, 'ame-selected-modules', array()) as $option => $value) {
			if ( !empty($value) && ($value !== 'off') ) {
				$enabledOptions[$option] = true;
			}
		}

		//todo: Consider adding some buffer space at the end to avoid truncation when other plugins add superfluous whitespace.

		$data = $this->export_data();
		$data['settings'] = array_intersect_key($data['settings'], $enabledOptions);
		$json = wp_json_encode($data);

		$domain = @wp_parse_url(get_bloginfo('url'), PHP_URL_HOST);
		$fileName = sprintf('%s-AME-configuration(%s).json', $domain, date_i18n('Y-m-d'));
		$fileName = apply_filters('admin_menu_editor-export_file_name', $fileName);

		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . $fileName);
		header('Content-Type: application/json; charset=' . get_option('blog_charset'), true);
		header('Connection: close');

		$size = strlen($json);
		if ( ob_get_level() > 0 ) {
			$size += ob_get_length();
		}
		header('Content-Length: ' . $size);

		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This is not an HTML page; output should be interpreted as raw JSON.
		echo $json;

		wp_ob_end_flush_all();
		flush();
		exit;
	}

	/**
	 * @return ameExportableComponent[]
	 */
	public function get_exportable_components(): array {
		$components = [
			'global'          => ameExportableComponent::builder('General plugin settings')
				->displayPriority(20)
				->exportCallback(function () {
					$globalOptions = array();
					foreach ($this->exportable_global_options as $key) {
						$globalOptions[$key] = $this->wp_menu_editor->get_plugin_option($key);
					}
					if ( !empty($globalOptions) ) {
						return $globalOptions;
					}
					return null;
				})
				->importCallback(function ($newSettings) {
					//Import global plugin settings.
					$importableOptions = array_intersect_key(
						$newSettings,
						array_fill_keys($this->exportable_global_options, true)
					);
					if ( !empty($importableOptions) ) {
						$this->wp_menu_editor->set_many_plugin_options($importableOptions);
						return sprintf('OK, %d options imported', count($importableOptions));
					}
					return true;
				})
				->build(),
			'admin-menu'      => ameExportableComponent::builder('Admin menu')
				->exportCallback(function () {
					try {
						$customMenu = $this->wp_menu_editor->load_custom_menu();
						if ( !empty($customMenu) ) {
							return ameMenu::add_format_header(ameMenu::compress($customMenu));
						}
					} catch (\InvalidMenuException $e) {
						//Ignore it. We can still try to export other settings if this part fails.
					}
					return null;
				})
				->importCallback(function ($newSettings) {
					try {
						$loadedMenu = ameMenu::load_array($newSettings);
						$menuEditor = $this->wp_menu_editor;
						$menuEditor->set_custom_menu($loadedMenu);
						return true;
					} catch (\Exception $ex) {
						return new WP_Error('exception', $ex->getMessage());
					}
				})
				->build(),
			'enabled-modules' => ameExportableComponent::builder('Enabled modules')
				->exportCallback(function () {
					list($moduleStates,) = $this->get_modules_for_porting();
					return !empty($moduleStates) ? $moduleStates : null;
				})
				->importCallback(function ($newSettings, $configFieldValue) {
					if ( !is_array($newSettings) ) {
						return ameImportResult::fromWpError(
							new WP_Error('invalid_enabled_modules_data', 'Imported module states are invalid.')
						);
					}

					if ( !empty($configFieldValue) ) {
						$s = new SchemaFactory();
						$configSchema = $s->json(
							$s->struct([
								'custom' => $s->record($s->string()->min(1)->max(300), $s->boolean()),
							])->required()
						);

						$importConfig = $configSchema->parse($configFieldValue);
						if ( is_wp_error($importConfig) ) {
							return ameImportResult::fromWpError($importConfig);
						}

						$newSettings = array_merge($newSettings, $importConfig['custom']);
					}

					$availableModules = $this->wp_menu_editor->get_available_modules();
					$isActiveModule = $this->wp_menu_editor->get_plugin_option('is_active_module');
					if ( !is_array($isActiveModule) ) {
						$isActiveModule = [];
					}

					$newlyEnabled = 0;
					$newlyDisabled = 0;
					foreach ($availableModules as $id => $module) {
						if ( !isset($newSettings[$id]) ) {
							continue;
						}
						$newState = (bool)$newSettings[$id];
						$currentState = $this->wp_menu_editor->is_module_active($id, $module);

						if ( $currentState !== $newState ) {
							$isActiveModule[$id] = $newState;
							if ( $newState ) {
								$newlyEnabled++;
							} else {
								$newlyDisabled++;
							}
						}
					}

					if ( ($newlyEnabled <= 0) && ($newlyDisabled <= 0) ) {
						return ameImportResult::nothing('No changes');
					}

					$this->wp_menu_editor->set_plugin_option('is_active_module', $isActiveModule);
					return ameImportResult::success(sprintf(
						'OK, %d modules enabled and %d modules disabled.',
						$newlyEnabled,
						$newlyDisabled
					));
				})
				->importConfigHtmlCallback(function ($pendingNewSettings, $configFieldName) {
					list($moduleStates, $moduleInfo) = $this->get_modules_for_porting();
					$scriptData = [
						'incomingState'    => $pendingNewSettings,
						'currentState'     => $moduleStates,
						'availableModules' => $moduleInfo,
					];

					$template = file_get_contents(__DIR__ . '/enabled-modules-config-template.html');
					if ( is_string($template) ) {
						return str_replace(
							['{{CONFIG_FIELD_NAME}}', '{{CONFIG_DATA_JSON}}'],
							[esc_attr($configFieldName), esc_attr(wp_json_encode($scriptData))],
							$template
						);
					} else {
						return '<p class="ame-error">Could not load import configuration template.</p>';
					}
				})
				->importPriority(200)
				->advanced()
				->build(),
		];

		foreach ($this->wp_menu_editor->get_loaded_modules() as $module) {
			if ( !($module instanceof ameModule) ) {
				continue;
			}
			$id = $module->getModuleId();
			if ( !isset($id) ) {
				continue;
			}

			if ( $module instanceof ameBasicExportableModule ) {
				foreach ($module->getExportableComponents() as $key => $component) {
					if ( !is_string($key) ) {
						$key = $id;
					}
					if ( array_key_exists($key, $components) ) {
						throw new \LogicException('Duplicate exportable component ID: ' . $key);
					}
					$components[$key] = $component;
				}
			} else if ( ($module instanceof \amePersistentModule) && $module->isSuitableForExport() ) {
				$components[$id] = ameExportableComponent::builder($module->getTabTitle())
					->exportCallback(function () use ($module) {
						$settings = $module->loadSettings();
						if ( is_array($settings) || is_null($settings) ) {
							return $settings;
						}
						if ( $settings instanceof AbstractSettingsDictionary ) {
							$exported = $settings->toArray();
							if ( !empty($exported) ) {
								return $exported;
							}
						}
						return null;
					})
					->importCallback(function ($newSettings) use ($module) {
						if ( is_array($newSettings) && !empty($newSettings) ) {
							$module->mergeSettingsWith($newSettings);
							$module->saveSettings();
							return true;
						}
						return false;
					})
					->build();
			}
		}

		//Sort by priority first, then by label.
		uasort($components, function (ameExportableComponent $a, ameExportableComponent $b) {
			$priorityA = $a->getDisplayPriority();
			$priorityB = $b->getDisplayPriority();
			if ( $priorityA !== $priorityB ) {
				return $priorityB - $priorityA;
			}

			//When comparing labels, ignore leading quotes.
			$labelA = ltrim($a->getLabel(), '\'"');
			$labelB = ltrim($b->getLabel(), '\'"');
			return strnatcasecmp($labelA, $labelB);
		});

		return $components;
	}

	public function add_import_export_tabs($tabs) {
		$tabs['import'] = 'Import';
		$tabs['export'] = 'Export';
		return $tabs;
	}

	public function display_import_tab() {
		$action = !empty($_REQUEST['action']) && is_string($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : '';

		if ( !empty($action) ) {
			$allowedActions = [self::UPLOAD_FILE_ACTION, self::IMPORT_FILE_ACTION];
			if ( !in_array($action, $allowedActions, true) ) {
				wp_die('Error: Unsupported action');
			}

			check_admin_referer($action);
		}

		$step = isset($_REQUEST['step']) ? intval($_REQUEST['step']) : 1;
		$step = min(max($step, 1), 3);

		$this->wp_menu_editor->display_settings_page_header();

		if ( $step === 1 ) {
			$formSubmitUrl = $this->get_import_tab_url(2);
			$action = self::UPLOAD_FILE_ACTION;
			$maxSize = wp_max_upload_size();
			$formattedSize = size_format($maxSize);

			printf('<form action="%s" method="post" enctype="multipart/form-data" class="ame-unified-import-form">', esc_attr($formSubmitUrl));
			?>
			<h2>Import plugin settings</h2>
			<p>
				<label for="upload">Choose a file to import
					(<?php printf('maximum size: %s', esc_html($formattedSize)); ?>):</label>
				<br>
				<input type="file" name="imported-data" size="25" id="ame-import-file-selector">
				<input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
				<input type="hidden" name="max_file_size" value="<?php echo esc_attr($maxSize); ?>">
			</p>
			<?php

			wp_nonce_field($action);
			submit_button('Next &rarr;', 'primary', 'submit', true, array('disabled' => 'disabled'));
			echo '</form>';
		} else if ( $step === 2 ) {
			$this->do_import_step_2();
		} else if ( $step === 3 ) {
			$this->do_import_step_3();
		}

		$this->wp_menu_editor->display_settings_page_footer();
	}

	private function get_import_tab_url($step = 1): string {
		return $this->wp_menu_editor->get_plugin_page_url(array(
			'sub_section' => 'import',
			'step'        => $step,
		));
	}

	private function do_import_step_2() {
		check_admin_referer(self::UPLOAD_FILE_ACTION);

		$uploadResult = $this->prepare_step_2();
		if ( is_wp_error($uploadResult) ) {
			$message = $uploadResult->get_error_message();
			printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($message));
			printf('<p><a class="button" href="%s">Go back</a></p>', esc_attr($this->get_import_tab_url()));
			return;
		}

		list($uploadedFileName, $importedData) = $uploadResult;

		//Move the file somewhere else to ensure it survives until the next step.
		$tempFile = get_temp_dir() . sprintf('AME-import-file-%d-%.3f.json', get_current_user_id(), microtime(true));
		$metaKey = 'ame_import_' . time() . '_' . substr(sha1($tempFile), 0, 10);
		move_uploaded_file($uploadedFileName, $tempFile);
		add_user_meta(get_current_user_id(), $metaKey, wp_slash($tempFile), true);

		//Schedule a cleanup in case the user doesn't go through with the import.
		wp_schedule_single_event(
			time() + 12 * 3600,
			'admin_menu_editor-clean_up_import',
			array(get_current_user_id(), $metaKey, $tempFile)
		);

		//Finally, we can get on with choosing which settings to import!
		$action = self::IMPORT_FILE_ACTION;
		$knownComponents = $this->get_exportable_components();
		$importableComponents = array_intersect_key($importedData['settings'], $knownComponents);

		//We'll show safe-ish settings first, then advanced settings. For example, accidentally overwriting
		//role capabilities with the wrong imported data could lock the user out of the site, so that option
		//is in the "Advanced" section and disabled by default.
		$regularSettings = [];
		$advancedSettings = [];
		foreach ($importedData['settings'] as $key => $data) {
			$isAdvanced = isset($knownComponents[$key]) && $knownComponents[$key]->isAdvanced();
			if ( $isAdvanced ) {
				$advancedSettings[$key] = $data;
			} else {
				$regularSettings[$key] = $data;
			}
		}
		$hasMultipleSections = !empty($advancedSettings) && !empty($regularSettings);

		printf('<form action="%s" method="post" class="ame-unified-import-form" id="ame-import-step-2">', esc_attr($this->get_import_tab_url(3)));

		echo '<h2>Choose what to import</h2>';
		$this->print_import_options($regularSettings, $knownComponents, $importableComponents);

		if ( $hasMultipleSections && !empty($advancedSettings) ) {
			echo '<h4>Advanced</h4>';
		}
		$this->print_import_options($advancedSettings, $knownComponents, $importableComponents, false);

		printf('<input type="hidden" name="meta-key" value="%s">', esc_attr($metaKey));
		printf('<input type="hidden" name="action" value="%s">', esc_attr($action));
		wp_nonce_field($action);
		submit_button('Import Settings');
		echo '</form>';
	}

	private function prepare_step_2() {
		//phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce should be verified in the caller.
		if ( empty($_FILES['imported-data']) ) {
			return new WP_Error(
				'no_file_uploaded',
				'No file uploaded. Please try again.'
				. ' (If you get this error when trying to upload a large file, make sure that post_max_size is at least as high as upload_max_filesize in php.ini.)'
			);
		}

		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$upload = $_FILES['imported-data'];
		if ( $upload['error'] !== UPLOAD_ERR_OK ) {
			return new WP_Error('upload_error', \wsMenuEditorExtras::get_upload_error_message($upload['error']));
		}

		$size = filesize($upload['tmp_name']);
		if ( $size <= 0 ) {
			return new WP_Error('empty_file', 'File is empty. Please upload a different file.');
		}

		if ( !@is_uploaded_file($upload['tmp_name']) || !@is_file($upload['tmp_name']) ) {
			return new WP_Error('not_an_uploaded_file', 'That doesn\'t seem to be a valid uploaded file.');
		}

		$content = file_get_contents($upload['tmp_name']);
		if ( empty($content) || !preg_match('/^\s{0,30}+[\[{]/', $content) ) {
			return new WP_Error('invalid_file_content', 'File format is unknown or the data is corrupted.');
		}

		$importedData = json_decode($content, true);
		if ( function_exists('json_last_error') && (json_last_error() !== JSON_ERROR_NONE) ) {
			return new WP_Error('invalid_json', 'File is not valid JSON.');
		}

		if (
			!is_array($importedData)
			|| !isset($importedData['format'], $importedData['format']['name'], $importedData['format']['version'])
		) {
			return new WP_Error('missing_format_header', 'That is not an Admin Menu Editor Pro export file.');
		}

		if ( ($importedData['format']['name'] !== self::$export_container_format_name) || empty($importedData['settings']) ) {
			return new WP_Error(
				'unsupported_format',
				'Unsupported file format. Please upload a file that was downloaded from the "Export" tab in Admin Menu Editor Pro.'
			);
		} else if ( version_compare($importedData['format']['version'], self::$export_container_format_version, '>') ) {
			return new WP_Error(
				'unsupported_version',
				sprintf(
					"Cannot import a file created by a newer version of the plugin. File format: '%s', newest supported format: '%s'.",
					$importedData['format']['version'],
					self::$export_container_format_version
				)
			);
		}

		return [$upload['tmp_name'], $importedData];
	}

	private function print_import_options(
		array $incomingSettings,
		array $knownComponents,
		array $importableComponents,
		bool  $enabledByDefault = true
	) {
		if ( empty($incomingSettings) ) {
			return;
		}

		//Sort settings according to the known components order.
		$componentOrder = array_flip(array_keys($knownComponents));
		uksort($incomingSettings, function ($a, $b) use ($componentOrder) {
			$posA = $componentOrder[$a] ?? PHP_INT_MAX;
			$posB = $componentOrder[$b] ?? PHP_INT_MAX;
			return $posA - $posB;
		});

		echo '<ul>';
		foreach (array_keys($incomingSettings) as $key) {
			$label = $key;
			$importConfigHtml = '';

			if ( isset($knownComponents[$key]) ) {
				$component = $knownComponents[$key];
				$label = $component->getLabel();
				$importConfigHtml = $component->generateImportConfigurationUi(
					$incomingSettings[$key] ?? [],
					$this->get_component_config_field_name($key)
				);
			}

			$itemClasses = ['ame-import-component-item'];
			if ( !empty($importConfigHtml) ) {
				$itemClasses[] = 'ame-has-import-config';
				if ( self::EXPAND_IMPORT_CONFIGS_BY_DEFAULT ) {
					$itemClasses[] = 'ame-has-expanded-import-config';
				}
			}

			printf('<li class="%s">', esc_attr(implode(' ', $itemClasses)));
			echo '<label>';
			printf(
				'<input type="checkbox" name="ame-selected-modules[%s]" class="ame-importable-module" %s %s> %s',
				esc_attr($key),
				(isset($importableComponents[$key]) && $enabledByDefault) ? ' checked ' : '',
				!isset($importableComponents[$key]) ? ' disabled ' : '',
				esc_html($label)
			);
			echo '</label>';

			if ( !empty($importConfigHtml) ) {
				$expandText = 'Show settings';
				$collapseText = 'Hide settings';

				echo ' <span class="ame-import-config-toggle-separator"></span>';
				printf(
					' <a class="ame-import-config-toggle" data-expand-text="%s" data-collapse-text="%s"'
					. ' title="Toggle import configuration panel">%s</a>',
					esc_attr($expandText),
					esc_attr($collapseText),
					esc_html($expandText)
				);
			}

			if ( !isset($importableComponents[$key]) ) {
				echo ' <span class="description">(You may need to install an add-on or activate a module to import this.)</span>';
			}

			if ( !empty($importConfigHtml) ) {
				echo '<div class="ame-import-component-config">';
				//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- It's up to the component to escape/sanitize its own HTML.
				echo $importConfigHtml;
				echo '</div>';
			}

			echo '</li>';
		}
		echo '</ul>';
	}

	private function do_import_step_3() {
		check_admin_referer(self::IMPORT_FILE_ACTION);

		echo '<div id="ame-import-step-3-start"><!-- This is a marker for automated testing. --></div>';

		$prepared = $this->prepare_step_3();
		if ( is_wp_error($prepared) ) {
			printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($prepared->get_error_message()));
			return;
		}
		list($tempFile, $enabledOptions, $metaKey, $componentConfigs) = $prepared;

		echo '<p>Importing settings...</p>';

		//phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- Should always be a local file.
		$container = json_decode(file_get_contents($tempFile), true);
		$moduleStatus = $this->import_data($container, $enabledOptions, $componentConfigs);

		echo '<ul class="ame-import-component-results">';
		foreach ($moduleStatus as $id => $status) {
			echo '<li>';
			printf('<span>%s: %s</span>', esc_html($id), esc_html($status->getPrimaryMessage()));
			if ( $status->hasErrorDetails() ) {
				echo '<ul class="ame-import-error-details">';
				foreach ($status->getErrorDetails() as $errorDetail) {
					echo '<li>';
					if ( !empty($errorDetail['code']) ) {
						printf(
							'<span class="ame-import-error-code">%s</span> : ',
							esc_html($errorDetail['code'])
						);
					}
					echo esc_html($errorDetail['message']);
					echo '</li>';
				}
				echo '</ul>';
			}
			echo '</li>';
		}
		echo '</ul>';

		//phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink -- Should always be in a temp. directory.
		if ( @unlink($tempFile) ) {
			echo '<p>Temporary file deleted.</p>';
		}
		if ( delete_user_meta(get_current_user_id(), $metaKey) ) {
			echo '<p>Database cleanup complete.</p>';
		}

		echo '<p id="ame-import-step-3-done">Done.</p>';
	}

	private function prepare_step_3() {
		if ( !$this->wp_menu_editor->current_user_can_edit_menu() ) {
			return new WP_Error('access_denied', 'Access denied.');
		}
		$post = $this->wp_menu_editor->get_post_params();

		//phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce should be verified in the caller.
		if ( empty($post['meta-key']) ) {
			return new WP_Error(
				'meta_key_missing',
				'One of the required fields is missing. Please try re-uploading the file.'
			);
		}

		$metaKey = sanitize_text_field($post['meta-key']);
		if ( strpos($metaKey, 'ame_import_') !== 0 ) {
			return new WP_Error('invalid_meta_key', 'Invalid meta key. (This should never happen.)');
		}

		$tempFile = get_user_meta(get_current_user_id(), $metaKey, true);
		if ( empty($tempFile) ) {
			return new WP_Error(
				'import_data_missing',
				'Import data is missing. This may be a bug or a plugin conflict.'
			);
		}

		if ( !is_file($tempFile) || !is_readable($tempFile) ) {
			return new WP_Error('temp_file_not_found', 'File not found. This may be a bug.');
		}

		$enabledOptions = [];
		$selectedComponents = ameUtils::get($post, 'ame-selected-modules', []);
		$componentConfigs = [];

		foreach ($selectedComponents as $option => $value) {
			if ( !empty($value) && ($value !== 'off') ) {
				$enabledOptions[$option] = true;

				$configFieldName = $this->get_component_config_field_name($option);
				if ( isset($post[$configFieldName]) ) {
					$componentConfigs[$option] = $post[$configFieldName];
				}
			}
		}

		if ( empty($enabledOptions) ) {
			return new WP_Error('no_enabled_components', 'No options selected.');
		}

		return [$tempFile, $enabledOptions, $metaKey, $componentConfigs];
		//phpcs:enable
	}

	private function get_component_config_field_name(string $moduleId): string {
		return sprintf('ame_component_config--%s', $moduleId);
	}

	public function clean_up_import_data($userId, $metaKey, $tempFileName) {
		$storedFileName = get_user_meta($userId, $metaKey, true);
		delete_user_meta($userId, $metaKey);

		if ( empty($storedFileName) || !is_string($storedFileName) ) {
			return;
		}

		//phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		//There's no good way to report errors from a scheduled (cron) action, so put them in the PHP error log.
		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		//It's debatable if PHP errors should be escaped; I lean towards "no".

		$extension = pathinfo($storedFileName, PATHINFO_EXTENSION);
		if ( $storedFileName === $tempFileName ) {
			if ( strtolower($extension) === 'json' ) {
				//phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink -- Should always be in a temp. directory.
				@unlink($storedFileName);
			} else {
				trigger_error(
					sprintf(
						'Admin Menu Editor Pro: Failed to clean up an import file because'
						. ' it does not have the correct extension. Expected: "json", actual: "%s".',
						$extension
					),
					E_USER_WARNING
				);
			}
		} else {
			trigger_error(
				sprintf(
					'Admin Menu Editor Pro: Cannot delete an old import file because the stored file names do not match.'
					. ' Database value: "%s", Cron job value: "%s"',
					$storedFileName,
					$tempFileName
				),
				E_USER_WARNING
			);
		}
		//phpcs:enable
	}

	public function display_export_tab() {
		$exportTabUrl = $this->wp_menu_editor->get_plugin_page_url(array(
			'sub_section' => 'export',
			'noheader'    => '1',
		));

		$components = $this->get_exportable_components();

		$this->wp_menu_editor->display_settings_page_header();
		echo '<h2>Choose what to export</h2>';

		printf('<form action="%s" method="post">', esc_attr($exportTabUrl));
		echo '<ul>';
		foreach ($components as $key => $component) {
			printf(
				'<li><label><input type="checkbox" name="ame-selected-modules[%s]" checked> %s</label></li>',
				esc_attr($key),
				esc_html($component->getLabel())
			);
		}
		echo '</ul>';

		printf('<input type="hidden" name="action" value="%s">', esc_attr(self::EXPORT_ACTION));
		wp_nonce_field(self::EXPORT_ACTION);
		submit_button('Download Export File', 'primary', 'submit', true);

		echo '</form>';

		$this->wp_menu_editor->display_settings_page_footer();
	}

	public function register_scripts() {
		$baseDeps = $this->wp_menu_editor->get_base_dependencies();

		ScriptDependency::create(
			plugins_url('extras/import-export/import-export.js', $this->wp_menu_editor->plugin_file),
			'ws-ame-import-export',
			__DIR__ . '/import-export.js',
			[
				'jquery',
				'jquery-ui-tabs',
				$baseDeps['ame-knockout'],
			]
		)
			->setInFooter()
			->register();
	}

	public function enqueue_tab_scripts() {
		wp_enqueue_script('ws-ame-import-export');
	}

	public function enqueue_tab_styles() {
		wp_enqueue_auto_versioned_style(
			'ws-ame-import-export-styles',
			plugins_url('import-export-styles.css', __FILE__)
		);
	}

	private function get_modules_for_porting(): array {
		$moduleStates = [];
		$moduleInfo = [];

		foreach ($this->wp_menu_editor->get_available_modules() as $id => $module) {
			//Don't export/import always-active modules since their state cannot be changed.
			if ( !empty($module['isAlwaysActive']) ) {
				continue;
			}

			$moduleStates[$id] = $this->wp_menu_editor->is_module_active($id, $module);

			$info = ['title' => !empty($module['title']) ? $module['title'] : $id];
			if ( !$this->wp_menu_editor->is_module_compatible($module) ) {
				$info['isCompatible'] = false;
			}
			$moduleInfo[$id] = $info;
		}
		return [$moduleStates, $moduleInfo];
	}

	public static function get_instance($menuEditor = null): self {
		if ( self::$last_instance === null ) {
			self::$last_instance = new self($menuEditor);
		}
		return self::$last_instance;
	}
}