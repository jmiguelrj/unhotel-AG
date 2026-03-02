<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$report_objs = $this->report_objs;
$country_objs = $this->country_objs;
$countries = $this->countries;

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadSelect2();
$vbo_app->loadContextMenuAssets();

$preport = VikRequest::getString('report', '', 'request');
$pkrsort = VikRequest::getString('krsort', '', 'request');
$pkrorder = VikRequest::getString('krorder', '', 'request');
$pexportreport = VikRequest::getString('exportreport', '', 'request');
$pexport_format = VikRequest::getString('export_format', '', 'request');
$execreport = VikRequest::getString('execreport', '', 'request');
$execreport = !empty($execreport);

$report_obj = null;

// JS lang defs
JText::script('VBOPRINT');
JText::script('VBOREPORTSELECT');
JText::script('VBSAVE');
JText::script('VBANNULLA');
JText::script('VBOADMINLEGENDSETTINGS');
JText::script('VBCRONACTIONS');
JText::script('VBMAINPAYMENTSEDIT');
JText::script('VBO_EMPTY_DATA');
JText::script('VBDELCONFIRM');

if ($pexportreport && $execreport) {
	// if report requested, call the exportCSV() method before outputting any HMTL code
	foreach ($report_objs as $obj) {
		if ($obj->getFileName() != $preport) {
			continue;
		}

		// set the global scope
		$obj->setScope('web');

		if (method_exists($obj, 'customExport')) {
			// custom export method implemented
			$obj->customExport($pexportreport);
		} else {
			// default export method
			$obj->setExportCSVFormat($pexport_format)->exportCSV();
		}

		// execute just the requested report
		break;
	}

	foreach ($country_objs as $cobj) {
		foreach ($cobj as $obj) {
			if ($obj->getFileName() != $preport) {
				continue;
			}

			// set the global scope
			$obj->setScope('web');

			if (method_exists($obj, 'customExport')) {
				// custom export method implemented
				$obj->customExport($pexportreport);
			} else {
				// default export method
				$obj->setExportCSVFormat($pexport_format)->exportCSV();
			}

			// execute just the requested report
			break;
		}
	}
}
?>

<div class="vbo-pmsreports-overlay-helper" style="display: none;">
	<div class="vbo-info-overlay-report"></div>
</div>

<div class="vbo-reports-container">
	<form name="adminForm" action="index.php?option=com_vikbooking&task=pmsreports" method="post" enctype="multipart/form-data" id="adminForm">
		<div class="vbo-reports-filters-wrap">
			<div class="vbo-reports-filters-outer vbo-btn-toolbar">
				<div class="vbo-reports-filters-main">
					<select id="choose-report" name="report" onchange="document.adminForm.submit();">
						<option value=""></option>
					<?php
					foreach ($report_objs as $obj) {
						$opt_active = false;
						if ($obj->getFileName() == $preport) {
							//get current report object
							$report_obj = $obj;
							//
							$opt_active = true;
						}
						?>
						<option value="<?php echo $obj->getFileName(); ?>"<?php echo $opt_active ? ' selected="selected"' : ''; ?>><?php echo $obj->getName(); ?></option>
						<?php
					}
					foreach ($country_objs as $ccode => $cobj) {
						?>
						<optgroup label="<?php echo $countries[$ccode]; ?>">
						<?php
						foreach ($cobj as $obj) {
							$opt_active = false;
							if ($obj->getFileName() == $preport) {
								//get current report object
								$report_obj = $obj;
								//
								$opt_active = true;
							}
							?>
							<option value="<?php echo $obj->getFileName(); ?>"<?php echo $opt_active ? ' selected="selected"' : ''; ?>><?php echo $obj->getName(); ?></option>
							<?php
						}
						?>
						</optgroup>
						<?php
					}
					?>
					</select>
				</div>
			<?php
			$report_filters = $report_obj !== null ? $report_obj->getFilters() : [];
			if ($report_filters) {
				?>
				<div class="vbo-reports-filters-report">
				<?php
				foreach ($report_filters as $filt) {
					?>
					<div class="vbo-report-filter-wrap">
					<?php
					if (!empty($filt['label'])) {
						?>
						<div class="vbo-report-filter-lbl">
							<span><?php echo $filt['label']; ?></span>
						</div>
						<?php
					}
					if (!empty($filt['html'])) {
						?>
						<div class="vbo-report-filter-val">
							<?php echo $filt['html']; ?>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				?>
				</div>
				<?php
			}
			if ($report_obj !== null) {
				?>
				<div class="vbo-reports-filters-launch">
					<input type="submit" class="btn" name="execreport" value="<?php echo JText::_('VBOREPORTLOAD'); ?>" />
				</div>
				<?php
				if ($execreport && property_exists($report_obj, 'exportAllowed') && $report_obj->exportAllowed) {
					// get the pretty version of the report file name
					$export_fname = $report_obj->getExportCSVFileName($cut_suffix = true, $suffix = '.csv', $pretty = true);
					?>
				<div style="display: none;">
					<span class="vbo-report-compactname"><?php echo preg_replace("/[^a-z0-9]+/i", '-', strtolower($report_obj->getName())); ?></span>
					<span class="vbo-report-prettyname"><?php echo $export_fname; ?></span>
				</div>

				<div class="vbo-reports-filters-export">
					<button type="button" class="btn btn-success vbo-context-menu-btn vbo-context-menu-exportcsvtype">
						<span class="vbo-context-menu-lbl">
							<?php VikBookingIcons::e('table'); ?> <?php echo JText::_('VBO_EXPORT_AS'); ?>
						</span><span class="vbo-context-menu-ico">
							<?php VikBookingIcons::e('sort-down'); ?>
						</span>
					</button>
				</div>

				<script type="text/javascript">
					jQuery(function() {
						jQuery('.vbo-context-menu-exportcsvtype').vboContextMenu({
							placement: 'bottom-right',
							buttons: [
								{
									icon: '<?php echo VikBookingIcons::i('file-csv'); ?>',
									text: 'CSV',
									separator: false,
									action: (root, config) => {
										vboDoExport('csv');
									},
								},
								{
									icon: '<?php echo VikBookingIcons::i('file-excel'); ?>',
									text: 'Excel',
									separator: true,
									action: (root, config) => {
										vboDoExport('excel');
									},
								},
								{
									icon: '<?php echo VikBookingIcons::i('print'); ?>',
									text: Joomla.JText._('VBOPRINT'),
									separator: false,
									action: (root, config) => {
										// attempt to detect the user agent
										const ua = navigator.userAgent;

										// get the element we want to print (report output)
										let print_element = document.getElementsByClassName('vbo-reports-output')[0];
										print_element.classList.add('vbo-report-output-printing');

										// get the title and compact name of the report
										let report_title = document.getElementsByClassName('vbo-report-prettyname')[0].innerText;
										let report_cname = document.getElementsByClassName('vbo-report-compactname')[0].innerText;

										// clone the whole and current HTML page source
										let clone_source = document.documentElement.cloneNode(true);
										clone_body = clone_source.getElementsByTagName('body')[0];
										// add a class to the body and set the only content to what we want to print
										clone_body.classList.add('vbo-is-printing', 'vbo-report-printing-' + report_cname);
										clone_body.innerHTML = '<h4 class="vbo-report-title-print">' + report_title + '</h4>' + "\n" + print_element.outerHTML;

										// write the proper HTML source onto a new window
										let print_window = window.open('', '_blank');
										print_window.document.write(clone_source.outerHTML);
										print_window.document.close();

										// delay the print command to avoid printing a blank page
										setTimeout(() => {
											print_window.focus();
											print_window.print();
											if (!/^((?!chrome|android).)*safari/i.test(ua)) {
												// do not close the new window when the browser is Safari to avoid issues
												print_window.close();
											}
										}, 500);
									},
								},
							],
						});
					});
				</script>
				<?php
				} elseif ($execreport && property_exists($report_obj, 'customExport')) {
				?>
				<div class="vbo-reports-filters-export">
					<?php echo $report_obj->customExport; ?>
				</div>
				<?php
				}

				/**
				 * Check for report custom scoped actions.
				 * 
				 * @since 	1.17.1 (J) - 1.7.1 (WP)
				 */
				$report_actions = $execreport ? $report_obj->getScopedActions('web') : [];
				if ($report_actions) {
					// check for custom action params
					$report_action_params = [];
					foreach ($report_actions as $report_action) {
						if ($report_action['params'] ?? []) {
							$report_action_params[$report_action['id']] = (array) $report_action['params'];
						}
					}

					// display context-menu button to allow the selection of the action
					?>
				<div class="vbo-reports-filters-export vbo-reports-filters-scoped-actions">
					<button
						type="button"
						class="btn btn-rounded btn-warning vbo-context-menu-btn vbo-context-menu-scopedactions"
					>
						<span class="vbo-context-menu-ico-left"><?php VikBookingIcons::e('rocket'); ?></span>
						<span class="vbo-context-menu-lbl"><?php echo JText::_('VBCRONACTIONS'); ?></span>
						<span class="vbo-context-menu-ico"><?php VikBookingIcons::e('sort-down'); ?></span>
					</button>
				</div>

				<script type="text/javascript">
					jQuery(function() {

						let scoped_actions = <?php echo json_encode($report_actions); ?>;
						let report_file    = '<?php echo $report_obj->getFileName(); ?>';
						let action_buttons = [];

						scoped_actions.forEach((action) => {
							// determine whether the action needs params
							let needs_params = (Object.keys(action?.params || []).length > 0);

							// push action button
							action_buttons.push({
								icon: action?.icon || '<?php echo VikBookingIcons::i('play'); ?>',
								text: action?.name || action.id,
								separator: true,
								action: (root, config) => {
									// modal default ondismiss callback
									let dismiss_action = null;

									// define the modal cancel button
									let cancel_btn = jQuery('<button></button>')
										.attr('type', 'button')
										.addClass('btn')
										.text(Joomla.JText._('VBANNULLA'))
										.on('click', function() {
											VBOCore.emitEvent('vbo-report-custom-scopedactions-dismiss');
										});

									// modal submit button only in case of action params
									let submit_btn = null;
									if (needs_params) {
										// define the ondismiss callback
										dismiss_action = () => {
											jQuery('#vbo-report-custom-action-params-' + action.id).appendTo('.vbo-report-custom-scopedactions-params-helper');
											jQuery('#vbo-report-custom-action-params-' + action.id).find('.vbo-report-custom-action-params-response').html('');
										};

										// define the submit button
										submit_btn = jQuery('<button></button>')
											.attr('type', 'button')
											.addClass('btn btn-primary')
											.text(action?.params_submit_lbl || action.name || action.id)
											.on('click', function() {
												// start loading
												VBOCore.emitEvent('vbo-report-custom-scopedactions-loading');

												// get form values
												let formValues = jQuery('#vbo-report-custom-action-params-form-' + action.id).serialize();

												// perform the request
												VBOCore.doAjax(
													"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=report.executeCustomAction'); ?>",
													formValues,
													(resp) => {
														resp = typeof resp === 'string' ? JSON.parse(resp) : resp;
														if (resp.hasOwnProperty('html') && resp['html']) {
															// stop loading
															VBOCore.emitEvent('vbo-report-custom-scopedactions-loading');

															// display action output
															modal_body.find('.vbo-report-custom-action-params-response').html(resp['html']);

															// animate the modal to the visible point of the response
															document.querySelector('.vbo-report-custom-action-params-response').scrollIntoView({
																behavior: 'smooth',
																block: 'start',
															});
														} else {
															// dismiss the modal on success
															VBOCore.emitEvent('vbo-report-custom-scopedactions-dismiss');
														}
													},
													(error) => {
														// stop loading
														VBOCore.emitEvent('vbo-report-custom-scopedactions-loading');

														// display the error
														alert(error.responseText);
													}
												);
											});
									}

									// render modal
									let modal_body = VBOCore.displayModal({
										suffix: 'report-custom-scopedactions',
										extra_class: 'vbo-modal-rounded vbo-modal-tall',
										title: action?.name || action.id,
										body: (action?.help ? '<p class="info">' + action.help + '</p>' : null),
										body_prepend: true,
										footer_left: cancel_btn,
										footer_right: submit_btn,
										dismiss_event: 'vbo-report-custom-scopedactions-dismiss',
										loading_event: 'vbo-report-custom-scopedactions-loading',
										onDismiss: dismiss_action,
									});

									if (needs_params) {
										// append report custom action params form to modal body
										jQuery('#vbo-report-custom-action-params-' + action.id).appendTo(modal_body);
									} else {
										// start loading
										VBOCore.emitEvent('vbo-report-custom-scopedactions-loading');

										// perform the request as soon as the modal is displayed in case of no params
										VBOCore.doAjax(
											"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=report.executeCustomAction'); ?>",
											{
												report_file:   report_file,
												report_action: action.id,
												report_scope:  'web',
											},
											(resp) => {
												resp = typeof resp === 'string' ? JSON.parse(resp) : resp;
												if (resp.hasOwnProperty('html') && resp['html']) {
													// stop loading
													VBOCore.emitEvent('vbo-report-custom-scopedactions-loading');

													// display action output
													modal_body.append(resp['html']);
												} else {
													// dismiss the modal on success
													VBOCore.emitEvent('vbo-report-custom-scopedactions-dismiss');
												}
											},
											(error) => {
												// display the error
												alert(error.responseText);

												// dismiss the modal on error
												VBOCore.emitEvent('vbo-report-custom-scopedactions-dismiss');
											}
										);
									}
								},
							});
						});

						jQuery('.vbo-context-menu-scopedactions').vboContextMenu({
							placement: 'bottom-right',
							buttons:   action_buttons,
						});

					});
				</script>
					<?php
				}

				/**
				 * Check for report custom settings.
				 * 
				 * @since 	1.17.1 (J) - 1.7.1 (WP)
				 */
				$report_settings = $report_obj ? $report_obj->getSettingFields() : [];
				if ($report_settings) {
					/**
					 * Handle report multiple profile settings.
					 * 
					 * @since 	1.17.7 (J) - 1.7.7 (WP)
					 */
					$report_profiles = $report_obj->allowsProfileSettings() ? $report_obj->getSettingProfiles() : [];
					$has_multi_profiles = (count($report_profiles) > 1);
					$report_active_profile = $report_obj->getActiveProfile();

					if ($has_multi_profiles) {
						// multiple report profile settings
						?>
				<div class="vbo-reports-filters-export vbo-reports-filters-settings">
					<button
						type="button"
						class="btn btn-rounded btn-primary vbo-context-menu-btn vbo-context-menu-report-profile-settings"
						data-report-name="<?php echo $this->escape($report_obj->getName()); ?>"
						data-report-file="<?php echo $this->escape($report_obj->getFileName()); ?>"
					>
						<span class="vbo-context-menu-ico-left"><?php VikBookingIcons::e('cogs'); ?></span>
						<span class="vbo-context-menu-lbl"><?php echo JText::_('VBOADMINLEGENDSETTINGS'); ?></span>
						<span class="vbo-context-menu-ico"><?php VikBookingIcons::e('sort-down'); ?></span>
					</button>
				</div>
						<?php
					} else {
						// classic report singular settings
						?>
				<div class="vbo-reports-filters-export vbo-reports-filters-settings">
					<button
						type="button"
						class="btn btn-rounded btn-primary vbo-report-render-settings"
						data-report-name="<?php echo $this->escape($report_obj->getName()); ?>"
						data-report-file="<?php echo $this->escape($report_obj->getFileName()); ?>"
					><?php VikBookingIcons::e('cogs'); ?> <?php echo JText::_('VBOADMINLEGENDSETTINGS'); ?></button>
				</div>
						<?php
					}
					?>

				<script type="text/javascript">

					/**
					 * Define current profile settings and active profile.
					 */
					var vboProfileSettings = <?php echo json_encode(($has_multi_profiles ? $report_profiles : (new stdClass))); ?>;
					var vboActiveProfile = '<?php echo $report_active_profile; ?>';

					/**
					 * Render report settings within a modal window.
					 */
					const vboRenderSettingsModal = (reportName, reportFile) => {
						let cancel_btn = jQuery('<button></button>')
							.attr('type', 'button')
							.addClass('btn')
							.text(Joomla.JText._('VBANNULLA'))
							.on('click', function() {
								VBOCore.emitEvent('vbo-report-custom-settings-dismiss');
							});

						let save_btn = jQuery('<button></button>')
							.attr('type', 'button')
							.addClass('btn btn-success')
							.text(Joomla.JText._('VBSAVE'))
							.on('click', function() {
								// start loading
								VBOCore.emitEvent('vbo-report-custom-settings-loading');

								// get form values
								let formValues = jQuery('#vbo-report-custom-settings-form').serialize();

								// perform the request
								VBOCore.doAjax(
									"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=report.saveSettings'); ?>",
									formValues,
									(resp) => {
										resp = typeof resp === 'string' ? JSON.parse(resp) : resp;

										if (typeof resp?.profiles === 'object' && Object.keys(resp.profiles).length) {
											// update global profiles variable
											vboProfileSettings = resp.profiles;
										}

										if (resp?.active_profile) {
											// update active profile global variable
											vboActiveProfile = resp.active_profile;

											// re-build profile setting buttons
											vboBuildProfileSettingButtons(true);
										}

										// dismiss the modal on success
										VBOCore.emitEvent('vbo-report-custom-settings-dismiss');

										// dispatch an event when settings have been saved
										VBOCore.emitEvent('vbo-report-settings-saved', {
											report: reportFile,
											settings: formValues,
										});

										// attempt to register quick menu action
										try {
											let menu_link_name = <?php echo json_encode(($report_obj ? addslashes($report_obj->getName()) : '')); ?>;
											if (menu_link_name) {
												VBOCore.registerAdminMenuAction({
													name: menu_link_name,
													href: (window.location.href + '&report=<?php echo $preport; ?>').replace('&tmpl=component', ''),
												}, 'pms');
											}
										} catch(e) {
											console.error(e);
										}
									},
									(error) => {
										// stop loading
										VBOCore.emitEvent('vbo-report-custom-settings-loading');

										// display the error
										alert(error.responseText);
									}
								);
							});

						// render modal
						let modal_body = VBOCore.displayModal({
							suffix: 'report-custom-settings',
							extra_class: 'vbo-modal-rounded vbo-modal-tall',
							title: reportName + ' - ' + Joomla.JText._('VBOADMINLEGENDSETTINGS'),
							footer_left: cancel_btn,
							footer_right: save_btn,
							dismiss_event: 'vbo-report-custom-settings-dismiss',
							loading_event: 'vbo-report-custom-settings-loading',
						});

						// start loading
						VBOCore.emitEvent('vbo-report-custom-settings-loading');

						// perform the request
						VBOCore.doAjax(
							"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=report.renderSettings'); ?>",
							{
								report: reportFile,
							},
							(resp) => {
								// stop loading
								VBOCore.emitEvent('vbo-report-custom-settings-loading');
								try {
									let obj_res = typeof resp === 'string' ? JSON.parse(resp) : resp;
									modal_body.append(obj_res['html']);
								} catch (err) {
									console.error('Error decoding the response', err, resp);
								}
							},
							(error) => {
								alert(error.responseText);
								// dismiss the modal
								VBOCore.emitEvent('vbo-report-custom-settings-dismiss');
							}
						);
					};

					/**
					 * Builds and registers the profile setting buttons.
					 */
					const vboBuildProfileSettingButtons = (rebuild) => {
						let profile_setting_buttons = [];
						let last_profile_setting = '';

						// push button to render current settings
						profile_setting_buttons.push({
							icon: '<?php echo VikBookingIcons::i('edit'); ?>',
							text: Joomla.JText._('VBMAINPAYMENTSEDIT'),
							separator: true,
							action: (root, config) => {
								// render settings modal
								let reportName = jQuery('.vbo-context-menu-report-profile-settings').attr('data-report-name');
								let reportFile = jQuery('.vbo-context-menu-report-profile-settings').attr('data-report-file');

								vboRenderSettingsModal(reportName, reportFile);
							},
						});

						if (typeof vboProfileSettings === 'object' && Object.keys(vboProfileSettings).length) {
							// count profiles
							let tot_profiles = Object.keys(vboProfileSettings).length;
							last_profile_setting = Object.keys(vboProfileSettings)[(tot_profiles - 1)];

							// iterate current profiles
							for (const [profileId, profileName] of Object.entries(vboProfileSettings)) {
								// push profile setting button
								profile_setting_buttons.push({
									icon: vboActiveProfile == profileId ? '<?php echo VikBookingIcons::i('check-circle', 'vbo-enabled-icon'); ?>' : '<?php echo VikBookingIcons::i('plug'); ?>',
									text: profileName,
									separator: (last_profile_setting == profileId),
									action: (root, config) => {
										// perform the request to change the active profile
										VBOCore.doAjax(
											"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=report.setActiveProfile'); ?>",
											{
												report_file:    jQuery('.vbo-context-menu-report-profile-settings').attr('data-report-file'),
												report_profile: profileId,
											},
											(resp) => {
												if (vboActiveProfile != profileId) {
													// empty listings filter (if any) when switching profile
													let settingListingsEl = document.querySelector('select[name="listings[]"][multiple]');
													if (settingListingsEl) {
														settingListingsEl.querySelectorAll('option').forEach((optEl) => {
															optEl.selected = false;
														});
														settingListingsEl.dispatchEvent(new Event('change'));
													}
												}

												// render profile settings
												let reportName = jQuery('.vbo-context-menu-report-profile-settings').attr('data-report-name');
												let reportFile = jQuery('.vbo-context-menu-report-profile-settings').attr('data-report-file');

												vboRenderSettingsModal(reportName, reportFile);

												// update active profile global variable
												vboActiveProfile = profileId;

												// re-build profile setting buttons
												vboBuildProfileSettingButtons(true);
											},
											(error) => {
												// log and display the error
												console.error(error);
												alert(error.responseText || 'Generic error');
											}
										);
									},
								});
							}

							// push button to clear all profile settings
							profile_setting_buttons.push({
								icon: '<?php echo VikBookingIcons::i('broom'); ?>',
								text: Joomla.JText._('VBO_EMPTY_DATA'),
								class: 'vbo-context-menu-entry-danger',
								separator: false,
								action: (root, config) => {
									if (confirm(Joomla.JText._('VBDELCONFIRM'))) {
										// perform the request to clear all profiles
										VBOCore.doAjax(
											"<?php echo VikBooking::ajaxUrl('index.php?option=com_vikbooking&task=report.clearProfiles'); ?>",
											{
												report_file: jQuery('.vbo-context-menu-report-profile-settings').attr('data-report-file'),
											},
											(resp) => {
												// empty global profile variables
												vboProfileSettings = {};
												vboActiveProfile = '';

												// re-build profile setting buttons
												vboBuildProfileSettingButtons(true);
											},
											(error) => {
												// log and display the error
												console.error(error);
												alert(error.responseText || 'Generic error');
											}
										);
									}
								},
							});
						}

						if (!rebuild) {
							jQuery('.vbo-context-menu-report-profile-settings').vboContextMenu({
								placement: 'bottom-right',
								buttons:   profile_setting_buttons,
							});
						} else {
							jQuery('.vbo-context-menu-report-profile-settings').vboContextMenu('buttons', profile_setting_buttons);
						}
					};

					/**
					 * DOM ready event.
					 */
					jQuery(function() {

						/**
						 * Register click event on Settings button (singular profile), if available.
						 */
						jQuery('.vbo-report-render-settings').on('click', function() {
							let reportName = jQuery(this).attr('data-report-name');
							let reportFile = jQuery(this).attr('data-report-file');

							vboRenderSettingsModal(reportName, reportFile);
						});

						/**
						 * Register Settings button context menu (multiple profile settings), if available.
						 */
						if (jQuery('.vbo-context-menu-report-profile-settings').length) {
							vboBuildProfileSettingButtons(false);
						}

						if (window.location.hash == '#settings') {
							if (jQuery('.vbo-context-menu-report-profile-settings').length) {
								// open settings modal from context menu (Edit)
								jQuery('.vbo-context-menu-report-profile-settings').vboContextMenu('buttons')[0].action();
							} else {
								// open settings modal from button
								jQuery('.vbo-report-render-settings').trigger('click');
							}
						}

						/**
						 * Handle settings profile changed event.
						 */
						document.addEventListener('vbo-report-settings-profile-changed', (e) => {
							let profile_type = e?.detail ? e.detail?.value : '';
							if (profile_type == '_new') {
								jQuery('#vbo-report-custom-settings-form').find('[data-profile="_new"]').show();
							} else {
								jQuery('#vbo-report-custom-settings-form').find('[data-profile="_new"]').hide();
							}
						});

					});
				</script>
					<?php
				}
			}
			?>
			</div>
			<div class="vbo-reports-subfilters-wrap">
			<?php
			/**
			 * Render report sub-filters template.
			 * 
			 * @since 	1.18.6 (J) - 1.8.6 (WP)
			 */
			if ($report_obj && ($subFiltersTpl = $report_obj->getSubFiltersTpl())) {
				?>
				<div class="vbo-reports-subfilters-cont">
					<?php echo $subFiltersTpl; ?>
				</div>
				<?php
			}
			?>
			</div>
		</div>
		<div id="vbo_hidden_fields"></div>
		<input type="hidden" name="krsort" value="<?php echo $pkrsort; ?>" />
		<input type="hidden" name="krorder" value="<?php echo $pkrorder; ?>" />
		<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug', 0, 'request'); ?>" />
		<input type="hidden" name="task" value="pmsreports" />
		<input type="hidden" name="option" value="com_vikbooking" />
	</form>

<div class="vbo-report-custom-scopedactions-params-helper" style="display: none;">
<?php
/**
 * Prepare HTML helper elements for the report custom action params.
 * 
 * @since 	1.17.1 (J) - 1.7.1 (WP)
 */
foreach (($report_action_params ?? []) as $action_id => $action_params) {
	?>
	<div id="vbo-report-custom-action-params-<?php echo $action_id; ?>">

		<form action="#report-action-params" method="post" name="vbo-report-custom-action-params-form-<?php echo $action_id; ?>" id="vbo-report-custom-action-params-form-<?php echo $action_id; ?>">

			<input type="hidden" name="report_file" value="<?php echo $report_obj->getFileName(); ?>" />
			<input type="hidden" name="report_action" value="<?php echo $action_id; ?>" />
			<input type="hidden" name="report_scope" value="web" />

			<div class="vbo-admin-container vbo-admin-container-full vbo-admin-container-compact">
				<div class="vbo-params-wrap">
					<div class="vbo-params-container">

						<div class="vbo-params-block">

							<?php
							// render the report custom action settings
							echo VBOParamsRendering::getInstance($action_params, [])->setInputName('report_data')->getHtml();
							?>

						</div>

					</div>
				</div>
			</div>

		</form>

		<div class="vbo-report-custom-action-params-response"></div>

	</div>
	<?php
}
?>
</div>

<?php
if ($report_obj !== null && $execreport) {
	// execute the report
	$res = $report_obj->getReportData();

	// get the report Chart (if any)
	$report_chart = $report_obj->getChart();

	// build the list of supported layouts
	$supportedLayoutsMap = [
		'sheetnchart' => [
			'title' => JText::_('VBOSHEETNCHART'),
			'class' => 'vbo-report-sheetnchart',
		],
		'sheet' => [
			'title' => JText::_('VBOSHEETONLY'),
			'class' => 'vbo-report-sheetonly',
		],
		'chart' => [
			'title' => JText::_('VBOCHARTONLY'),
			'class' => 'vbo-report-chartonly',
		],
	];

	// sort layouts according to report's preferred layout
	$preferredLayout = $report_obj->getDefaultLayoutType((bool) (!empty($report_chart)));
	uksort($supportedLayoutsMap, function($a, $b) use ($preferredLayout) {
		if ($a == $preferredLayout) {
			return -1;
		}
		if ($b == $preferredLayout) {
			return 1;
		}
		return 0;
	});

	/**
	 * Attempt to register in the local storage the choice of this PMS report.
	 * 
	 * @since 	1.16.0 (J) - 1.6.0 (WP)
	 */
	$link_name = addslashes($report_obj->getName());
	JFactory::getDocument()->addScriptDeclaration(
<<<JS
;(function($) {
	$(function() {
		try {
			VBOCore.registerAdminMenuAction({
				name: '$link_name',
				href: (window.location.href + '&report=$preport').replace('&tmpl=component', ''),
			}, 'pms');
		} catch(e) {
			console.error(e);
		}
	});
})(jQuery);
JS
	);

	if ($res && !empty($report_chart)) {
		// display the layout type choice
		?>
	<div class="vbo-report-layout-type" style="display: none;">
		<div class="vbo-report-layout-type-inner">
			<label for="vbo-report-layout"><?php VikBookingIcons::e('chart-line'); ?></label>
			<select id="vbo-report-layout" onchange="vboSetReportLayout(this.value);">
			<?php
			foreach ($supportedLayoutsMap as $layoutEnum => $layoutData) {
				?>
				<option value="<?php echo $layoutEnum; ?>"><?php echo $layoutData['title']; ?></option>
				<?php
			}
			?>
			</select>
		</div>
	</div>
	<script>
		VBOCore.DOMLoaded(() => {
			// move the report layout selection onto a different position
			const vboReportLayoutEl = document.querySelector('.vbo-report-layout-type');
			if (document.querySelector('.vbo-reports-subfilters-cont')) {
				// move it only if sub-filters were defined
				document.querySelector('.vbo-reports-subfilters-wrap').append(vboReportLayoutEl);
			}
			vboReportLayoutEl.style.display = '';
		});
	</script>
		<?php
	}

	// determine the default output class depending on preferred layout type
	$defOutputCls = array_values($supportedLayoutsMap)[0]['class'] ?? 'vbo-report-sheetonly';

	?>
	<div class="vbo-reports-output <?php echo $defOutputCls; ?> vbo-report-output-<?php echo $report_obj->getFileName(); ?>">
	<?php

	if (!$res) {
		// error generating the report
		?>
		<p class="err"><?php echo $report_obj->getError(); ?></p>
		<?php
	} else {
		// display the report and set default ordering and sorting
		if (empty($pkrsort) && property_exists($report_obj, 'defaultKeySort')) {
			$pkrsort = $report_obj->defaultKeySort;
		}
		if (empty($pkrorder) && property_exists($report_obj, 'defaultKeyOrder')) {
			$pkrorder = $report_obj->defaultKeyOrder;
		}
		if (strlen($report_obj->getWarning())) {
			// warning message should not stop the report from rendering
			?>
		<p class="warn"><?php echo $report_obj->getWarning(); ?></p>
			<?php
		}

		// parse the classic sheet of the report
		?>
		<div class="vbo-report-sheet">
			<div class="table-responsive vbo-table-sticky-components">
				<table class="table">
					<thead>
						<tr>
						<?php
						foreach ($report_obj->getReportCols() as $col) {
							if ($col['ignore_view'] ?? 0) {
								continue;
							}
							$col_cont = $col['label'];
							if (!empty($col['tip'])) {
								$tip_data = [
									'title' => ($col['tip_label'] ?? '') ?: $col['label'],
									'content' => $col['tip']
								];
								if (!empty($col['tip_pos'])) {
									$tip_data['placement'] = $col['tip_pos'];
								}
								$col_cont = $vbo_app->createPopover($tip_data) . ' ' . $col_cont;
							}
							if (!($col['attr'] ?? []) && ($col['center'] ?? 0)) {
								// allow shortcut to define a centered styling for the cell
								$col['attr'] = ['class="center"'];
							}
							?>
							<th<?php echo ($col['attr'] ?? []) ? ' ' . implode(' ', (array) $col['attr']) : ''; ?>>
							<?php
							if ($col['sortable'] ?? 0) {
								$krorder = $pkrsort == $col['key'] && $pkrorder == 'DESC' ? 'ASC' : 'DESC';
								?>
								<a href="JavaScript: void(0);" onclick="vboSetFilters({krsort: '<?php echo $col['key']; ?>', krorder: '<?php echo $krorder; ?>'}, true);" class="<?php echo $pkrsort == $col['key'] ? 'vbo-list-activesort' : ''; ?>">
									<span><?php echo $col_cont; ?></span>
									<i class="fa <?php echo $pkrsort == $col['key'] && $krorder == 'DESC' ? 'fa-sort-asc' : ($pkrsort == $col['key'] ? 'fa-sort-desc' : 'fa-sort'); ?>"></i>
								</a>
								<?php
							} else {
								?>
								<span><?php echo $col_cont; ?></span>
								<?php
							}
							?>
							</th>
							<?php
						}
						?>
						</tr>
					</thead>
					<tbody>
					<?php
					// obtain the report row classes
					$reportRowClasses = $report_obj->getReportRowClasses();
					foreach ($report_obj->getReportRows() as $rowIndex => $row) {
						?>
						<tr<?php echo ($reportRowClasses ? ' class="' . implode(' ', (array) $reportRowClasses[$rowIndex] ?? []) . '"' : ''); ?>>
						<?php
						foreach ($row as $cell) {
							if ($cell['ignore_view'] ?? 0) {
								continue;
							}
							if (!($cell['attr'] ?? []) && ($cell['center'] ?? 0)) {
								// allow shortcut to define a centered styling for the cell
								$cell['attr'] = ['class="center"'];
							}
							?>
							<td<?php echo ($cell['attr'] ?? []) ? ' ' . implode(' ', (array) $cell['attr']) : ''; ?>>
							<?php
							if (($cell['html'] ?? 0) && is_callable($cell['callback'] ?? null)) {
								// custom HTML output expected
								echo $cell['callback']($cell['value']);
							} else {
								?>
								<span<?php echo !empty($cell['title']) ? ' title="' . htmlspecialchars($cell['title']) . '"' : ''; ?>><?php echo is_callable($cell['callback'] ?? null) ? $cell['callback']($cell['value']) : $cell['value']; ?></span>
								<?php
							}
							?>
							</td>
							<?php
						}
						?>
						</tr>
						<?php
					}
					?>
					</tbody>
				<?php
				if ($reportFooterRows = $report_obj->getReportFooterRow()) {
					// display table footer
					?>
					<tfoot>
					<?php
					foreach ($reportFooterRows as $row) {
						?>
						<tr class="vbo-report-footer-row vbo-report-footer-<?php echo strtolower(str_replace('_', '-', (string) $report_obj->getFileName())); ?>">
						<?php
						foreach ($row as $cell) {
							if ($cell['ignore_view'] ?? 0) {
								continue;
							}
							if (!($cell['attr'] ?? []) && (($cell['center'] ?? 0) || ($cell['colspan'] ?? 0))) {
								// allow shortcuts to define a centered styling and/or colspan for the cell
								$cell['attr'] = [];
								if ($cell['center'] ?? 0) {
									$cell['attr'][] = 'class="center"';
								}
								if ($cell['colspan'] ?? 0) {
									$cell['attr'][] = 'colspan="' . ((int) $cell['colspan']) . '"';
								}
							}
							?>
							<td<?php echo ($cell['attr'] ?? []) ? ' ' . implode(' ', (array) $cell['attr']) : ''; ?>>
							<?php
							if (($cell['html'] ?? 0) && is_callable($cell['callback'] ?? null)) {
								// custom HTML output expected
								echo $cell['callback']($cell['value']);
							} else {
								?>
								<span><?php echo is_callable($cell['callback'] ?? null) ? $cell['callback']($cell['value']) : $cell['value']; ?></span>
								<?php
							}
							?>
							</td>
							<?php
						}
						?>
						</tr>
						<?php
					}
					?>
					</tfoot>
					<?php
				}
				?>
				</table>
			</div>
		</div>
		<?php

		// parse the Chart, if defined
		if (!empty($report_chart)) {
			$report_chart_title = $report_obj->getChartTitle();
			?>
		<div class="vbo-report-chart-wrap" style="<?php echo $defOutputCls === 'vbo-report-sheetonly' ? 'display: none;' : ''; ?>">
			<div class="vbo-report-chart-inner">
				<div class="vbo-report-chart-main">
				<?php
				$top_chart_metas = $report_obj->getChartMetaData('top');
				if (is_array($top_chart_metas) && count($top_chart_metas)) {
					?>
					<div class="vbo-report-chart-metas vbo-report-chart-metas-top">
					<?php
					foreach ($top_chart_metas as $chart_meta) {
						?>
						<div class="vbo-report-chart-meta<?php echo isset($chart_meta['class']) ? ' ' . $chart_meta['class'] : ''; ?>">
							<div class="vbo-report-chart-meta-inner">
								<div class="vbo-report-chart-meta-lbl"><?php echo isset($chart_meta['label']) ? $chart_meta['label'] : ''; ?></div>
								<div class="vbo-report-chart-meta-val">
									<span class="vbo-report-chart-meta-val-main"><?php echo isset($chart_meta['value']) ? $chart_meta['value'] : ''; ?></span>
								<?php
								if (isset($chart_meta['descr'])) {
									?>
									<span class="vbo-report-chart-meta-val-descr"><?php echo $chart_meta['descr']; ?></span>
									<?php
								}
								?>
									<?php echo isset($chart_meta['extra']) ? $chart_meta['extra'] : ''; ?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				?>
					<div class="vbo-report-chart-content">
					<?php
					if (!empty($report_chart_title)) {
						?>
						<h4><?php echo $report_chart_title; ?></h4>
						<?php
					}
					?>
						<?php echo $report_chart; ?>
					</div>
				<?php
				$bottom_chart_metas = $report_obj->getChartMetaData('bottom');
				if (is_array($bottom_chart_metas) && count($bottom_chart_metas)) {
					?>
					<div class="vbo-report-chart-metas vbo-report-chart-metas-bottom">
					<?php
					foreach ($bottom_chart_metas as $chart_meta) {
						?>
						<div class="vbo-report-chart-meta<?php echo isset($chart_meta['class']) ? ' ' . $chart_meta['class'] : ''; ?>">
							<div class="vbo-report-chart-meta-inner">
								<div class="vbo-report-chart-meta-lbl"><?php echo isset($chart_meta['label']) ? $chart_meta['label'] : ''; ?></div>
								<div class="vbo-report-chart-meta-val">
									<span class="vbo-report-chart-meta-val-main"><?php echo isset($chart_meta['value']) ? $chart_meta['value'] : ''; ?></span>
								<?php
								if (isset($chart_meta['descr'])) {
									?>
									<span class="vbo-report-chart-meta-val-descr"><?php echo $chart_meta['descr']; ?></span>
									<?php
								}
								?>
									<?php echo isset($chart_meta['extra']) ? $chart_meta['extra'] : ''; ?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				?>
				</div>
			<?php
			$right_chart_metas = $report_obj->getChartMetaData('right');
			if (is_array($right_chart_metas) && count($right_chart_metas)) {
				?>
				<div class="vbo-report-chart-right">
					<div class="vbo-report-chart-metas vbo-report-chart-metas-right">
					<?php
					foreach ($right_chart_metas as $chart_meta) {
						?>
						<div class="vbo-report-chart-meta<?php echo isset($chart_meta['class']) ? ' ' . $chart_meta['class'] : ''; ?>">
							<div class="vbo-report-chart-meta-inner">
								<div class="vbo-report-chart-meta-lbl"><?php echo isset($chart_meta['label']) ? $chart_meta['label'] : ''; ?></div>
								<div class="vbo-report-chart-meta-val">
									<span class="vbo-report-chart-meta-val-main"><?php echo isset($chart_meta['value']) ? $chart_meta['value'] : ''; ?></span>
								<?php
								if (isset($chart_meta['descr'])) {
									?>
									<span class="vbo-report-chart-meta-val-descr"><?php echo $chart_meta['descr']; ?></span>
									<?php
								}
								?>
									<?php echo isset($chart_meta['extra']) ? $chart_meta['extra'] : ''; ?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
				<?php
			}
			?>
			</div>
		</div>
			<?php
		}
	}
	?>
	</div>
	<?php
}
?>
</div>

<script type="text/javascript">
function vboSetFilters(obj, dosubmit) {
	if (typeof obj != "object") {
		console.log("arg is not an object");
		return;
	}
	for (var p in obj) {
		if (!obj.hasOwnProperty(p)) {
			continue;
		}
		var elem = document.adminForm[p];
		if (elem) {
			document.adminForm[p].value = obj[p];
		} else {
			document.getElementById("vbo_hidden_fields").innerHTML += "<input type='hidden' name='"+p+"' value='"+obj[p]+"' />";
		}
	}
	if (!obj.hasOwnProperty('execreport')) {
		document.getElementById("vbo_hidden_fields").innerHTML += "<input type='hidden' name='execreport' value='1' />";
	}
	if (dosubmit) {
		document.adminForm.submit();
	}
}

function vboDoExport(format) {
	// set input vars and submit
	document.adminForm.target = '_blank';
	document.adminForm.action += '&tmpl=component';
	vboSetFilters({
		exportreport: '1',
		export_format: format
	}, true);

	// restore input vars
	setTimeout(function() {
		document.adminForm.target = '';
		document.adminForm.action = document.adminForm.action.replace('&tmpl=component', '');
		vboSetFilters({
			exportreport: '0',
			export_format: ''
		}, false);
	}, 1000);
}

function vboShowOverlay(options) {
	let modal_options = {
		suffix: 	   'pmsreports-driver-helper',
		extra_class:   'vbo-modal-rounded vbo-modal-tall vbo-modal-nofooter',
		title: 		   Joomla.JText._('VBCRONACTIONS'),
		body: 		   null,
		body_prepend:  true,
		draggable: 	   true,
		dismiss_event: 'vbo-pmsreports-helper-modal-dismiss',
		loading_event: 'vbo-pmsreports-helper-modal-loading',
		onDismiss: 	   () => {
			jQuery('.vbo-info-overlay-report').appendTo('.vbo-pmsreports-overlay-helper');
		},
	};

	if (typeof options === 'object') {
		modal_options = Object.assign(modal_options, options);
	}

	let modal_body = VBOCore.displayModal(modal_options);

	jQuery('.vbo-info-overlay-report').appendTo(modal_body);
}

function vboHideOverlay() {
	VBOCore.emitEvent('vbo-pmsreports-helper-modal-dismiss');
}

function vboSetReportLayout(layout) {
	if (layout == 'sheet') {
		jQuery('.vbo-reports-output').removeClass('vbo-report-sheetnchart').removeClass('vbo-report-chartonly').addClass('vbo-report-sheetonly');
		jQuery('.vbo-report-chart-wrap').hide();
		jQuery('.vbo-report-sheet').show();
	} else if (layout == 'chart') {
		jQuery('.vbo-reports-output').removeClass('vbo-report-sheetnchart').removeClass('vbo-report-sheetonly').addClass('vbo-report-chartonly');
		jQuery('.vbo-report-sheet').hide();
		jQuery('.vbo-report-chart-wrap').show();
	} else if (layout == 'sheetnchart') {
		jQuery('.vbo-reports-output').removeClass('vbo-report-sheetonly').removeClass('vbo-report-chartonly').addClass('vbo-report-sheetnchart');
		jQuery('.vbo-report-sheet').show();
		jQuery('.vbo-report-chart-wrap').show();
	}
	VBOCore.emitEvent('vbo_report_layout_changed', {layout: layout});
}

function vboReportSetTableHeight(element) {
	if (!element) {
		return;
	}

	// minimum, decent, table height threshold
	let heightThreshold = 300;

	// grace distance from bottom
	let graceBottomDistance = 10;

	// calculate values
	let offsetTop       = element.getBoundingClientRect().top;
	let viewportHeight  = window.innerHeight;
	let availableHeight = viewportHeight - offsetTop - graceBottomDistance;

	if (availableHeight < heightThreshold) {
		// apply threshold
		availableHeight = heightThreshold;
	}

	// apply element height
	element.style.height = availableHeight + 'px';
}

VBOCore.DOMLoaded(() => {

	/**
	 * Render select2 element for choosing a report.
	 */
	jQuery("#choose-report").select2({placeholder: Joomla.JText._('VBOREPORTSELECT'), width: "200px"});

	/**
	 * Register event for (re)loading the current report.
	 */
	document.addEventListener('vbo_report_reload', () => {
		// simulate the click on the "load report data" button
		document.querySelector('input[type="submit"][name="execreport"]').click();
	});

	/**
	 * Calculate the report table maximum height to fit in the viewport.
	 */
	const reportTableEl = document.querySelector('.vbo-table-sticky-components');
	vboReportSetTableHeight(reportTableEl);
	window.addEventListener('resize', () => {
		vboReportSetTableHeight(reportTableEl);
	});
});

<?php echo $report_obj !== null ? ($report_obj->getScript() ?: '') : ''; ?>
</script>
