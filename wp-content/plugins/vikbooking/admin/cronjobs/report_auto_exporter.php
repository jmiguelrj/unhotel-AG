<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Cron Job driver that automatically executes and exports a specific PMS Report.
 * 
 * @since   1.16.1 (J) - 1.6.1 (WP)
 */
class VikBookingCronJobReportAutoExporter extends VBOCronJob
{
    // does not need to track elements
    use VBOCronTrackerUnused;

    /**
     * @var     array
     * 
     * @since   1.17.1 (J) - 1.7.1 (WP)
     */
    protected $default_exp_formats_list = [
        'auto',
        'csv',
        'excel',
    ];

    /**
     * This method should return all the form fields required to collect the information
     * needed for the execution of the cron job.
     * 
     * @return  array  An associative array of form fields.
     */
    public function getForm()
    {
        // the list of eligible reports
        $eligible_reports = [];

        // associative list of report filters
        $report_filters = [];

        // associative list of report custom actions
        $report_actions = [];

        // associative list of report profile settings
        $report_profile_settings = [];

        // load all the available PMS Reports
        list($report_objs, $country_objs) = VBOReportLoader::getInstance()->getDrivers();

        // get countries involved
        $countries = VBOReportLoader::getInstance()->getInvolvedCountries();

        // global reports
        foreach ($report_objs as $obj) {
            if (!method_exists($obj, 'customExport') && (!property_exists($obj, 'exportAllowed') || !$obj->exportAllowed)) {
                // this report has not implemented any export functionality
                continue;
            }

            // set the global scope
            $obj->setScope('cron');

            // report file name
            $report_fname = $obj->getFileName();

            // push eligible report
            $eligible_reports[$report_fname] = $obj->getName();

            // get the filters to build the example JSON payload
            foreach ($obj->getFilters() as $filter) {
                if (!is_array($filter) || empty($filter['name'])) {
                    continue;
                }

                // push report filter
                if (!isset($report_filters[$report_fname])) {
                    $report_filters[$report_fname] = [];
                }

                // set default/suggested value
                $report_filters[$report_fname][$filter['name']] = $this->getFilterDefaultValue($filter);
            }

            // set report custom actions
            $report_actions[$report_fname] = $obj->getScopedActions('cron', $visible = true);

            // check if the report supports multiple profile settings
            if ($obj->allowsProfileSettings()) {
                // load all the existing profile settings
                $all_profiles = $obj->getSettingProfiles();
                if (count($all_profiles) > 1) {
                    $report_profile_settings[$report_fname] = $all_profiles;
                }
            }
        }

        // country reports
        foreach ($country_objs as $ccode => $cobj) {
            // parse reports of this country code
            foreach ($cobj as $obj) {
                if (!method_exists($obj, 'customExport') && (!property_exists($obj, 'exportAllowed') || !$obj->exportAllowed)) {
                    // this report has not implemented any export functionality
                    continue;
                }

                // set the global scope
                $obj->setScope('cron');

                // report file name
                $report_fname = $obj->getFileName();

                // push eligible report
                $eligible_reports[$report_fname] = $countries[$ccode] . ' - ' . $obj->getName();

                // get the filters to build the example JSON payload
                foreach ($obj->getFilters() as $filter) {
                    if (!is_array($filter) || empty($filter['name'])) {
                        continue;
                    }

                    // push report filter
                    if (!isset($report_filters[$report_fname])) {
                        $report_filters[$report_fname] = [];
                    }

                    // set default/suggested value
                    $report_filters[$report_fname][$filter['name']] = $this->getFilterDefaultValue($filter);
                }

                // set report custom actions
                $report_actions[$report_fname] = $obj->getScopedActions('cron', $visible = true);

                // check if the report supports multiple profile settings
                if ($obj->allowsProfileSettings()) {
                    // load all the existing profile settings
                    $all_profiles = $obj->getSettingProfiles();
                    if (count($all_profiles) > 1) {
                        $report_profile_settings[$report_fname] = $all_profiles;
                    }
                }
            }
        }

        // default export formats
        $def_export_formats = [
            'auto'  => JText::_('VBCONFIGSEARCHPSMARTSEARCHAUTO'),
            'csv'   => 'CSV',
            'excel' => 'Excel',
        ];

        // all export formats
        $all_export_formats = $def_export_formats;
        foreach ($report_actions as $report_fname => $actions) {
            if (!$actions) {
                continue;
            }
            $all_export_formats = array_merge(
                $all_export_formats,
                array_combine(
                    array_column($actions, 'id'),
                    array_column($actions, 'name')
                )
            );
        }

        // default payload
        $def_payload = new stdClass;
        $def_payload->fromdate = '{Y-m-d}';
        $def_payload->todate = '{Y-m-d +1 month}';
        $def_payload_pretty = json_encode($def_payload, JSON_PRETTY_PRINT);

        // build the helper script
        $payload_examples  = json_encode($report_filters);
        $actions_available = json_encode($report_actions);
        $export_formats    = json_encode($def_export_formats);
        $report_profiles   = json_encode($report_profile_settings);
        $payload_helper_js = <<<JS
var vbo_cron_report_auto_exporter_def_payloads = $payload_examples;
var vbo_cron_report_auto_exporter_actions = $actions_available;
var vbo_cron_report_auto_exporter_def_formats = $export_formats;
var vbo_cron_report_auto_exporter_profiles_list = $report_profiles;

/**
 * Fires when a report is selected.
 */
function vboCronReportAutoExportSetData(report) {
    vboCronReportAutoExportSetExamplePayload(report);
    vboCronReportAutoExportSetCustomActions(report);
}

/**
 * Sets the JSON Payload example for the selected report.
 */
function vboCronReportAutoExportSetExamplePayload(report, extra_params) {
    let example_payload = {};
    if (vbo_cron_report_auto_exporter_def_payloads.hasOwnProperty(report)) {
        // clone the original object to avoid referencing
        example_payload = Object.assign({}, vbo_cron_report_auto_exporter_def_payloads[report]);
    }
    if (typeof extra_params === 'object') {
        example_payload = Object.assign(example_payload, extra_params);
    }
    if (vbo_cron_report_auto_exporter_profiles_list.hasOwnProperty(report)) {
        // report with multiple profile settings
        let profiles_list = Object.keys(vbo_cron_report_auto_exporter_profiles_list[report]);
        if (profiles_list.length) {
            example_payload = Object.assign(example_payload, {
                _allReportProfiles: true,
                _reportProfiles: profiles_list.join(', ')
            });
        }
    }
    document.getElementsByClassName('vbo-report-auto-exporter-example')[0].innerText = JSON.stringify(example_payload, null, 4);
}

/**
 * Builds the available export formats and custom actions for the selected report.
 */
function vboCronReportAutoExportSetCustomActions(report) {
    let dropdown = document.querySelector('select[data-report-param="format"]');
    if (!vbo_cron_report_auto_exporter_actions.hasOwnProperty(report) || (Array.isArray(vbo_cron_report_auto_exporter_actions[report]) && !vbo_cron_report_auto_exporter_actions[report].length)) {
        // keep only the default export formats
        dropdown.querySelectorAll('option').forEach((option) => {
            let format = option.value;
            if (!vbo_cron_report_auto_exporter_def_formats.hasOwnProperty(format)) {
                option.remove();
            }
        });
        return;
    }
    let report_custom_actions = [];
    // add the custom actions for the currently selected report
    vbo_cron_report_auto_exporter_actions[report].forEach((action) => {
        // push report custom action
        report_custom_actions.push(action.id);
        // ensure this custom action is not present already
        if (dropdown.querySelector('option[value="' + action.id + '"]')) {
            return;
        }
        const option = document.createElement('option');
        option.setAttribute('value', action.id);
        option.innerText = action.name;
        dropdown.appendChild(option);
    });
    // remove the custom actions that do not belong to this report
    dropdown.querySelectorAll('option').forEach((option) => {
        let format = option.value;
        if (!report_custom_actions.includes(format) && !vbo_cron_report_auto_exporter_def_formats.hasOwnProperty(format)) {
            // remove the custom action that must belong to another report
            option.remove();
        }
    });
}

/**
 * Fires when the export format is selected in order to set the proper JSON Payload example.
 */
function vboCronReportAutoExportCheckFormat(format) {
    let report = document.querySelector('select[data-report-param="report"]').value;
    if (vbo_cron_report_auto_exporter_def_formats.hasOwnProperty(format)) {
        // default export format selected
        vboCronReportAutoExportSetExamplePayload(report);
    } else {
        // custom report action selected
        let extra_params = {
            _reportAction: format,
        };
        // scan all report custom actions to find the format selected
        vbo_cron_report_auto_exporter_actions[report].forEach((action) => {
            if (action.id === format && action.hasOwnProperty('params')) {
                // custom action requires params
                extra_params['_reportData'] = {};
                for (const prop in action.params) {
                    if (!action.params.hasOwnProperty(prop)) {
                        continue;
                    }
                    // build data value
                    let data_type = action.params[prop]['type'];
                    let data_value = '';
                    if (data_type === 'calendar' || data_type === 'date') {
                        data_value = '{Y-m-d}';
                    }
                    // push report data value
                    extra_params['_reportData'][prop] = data_value;
                }
            }
        });
        // update payload example
        vboCronReportAutoExportSetExamplePayload(report, extra_params);
    }
}


/**
 * Displays a modal with the JSON Payload value examples.
 */
function vboCronReportAutoExportExamples() {
    let modal_body = VBOCore.displayModal({
        suffix: 'cron-report-auto-exporter-examples',
        extra_class: 'vbo-modal-rounded vbo-modal-tall vbo-modal-nofooter',
        title: 'Examples',
        body: null,
        onDismiss: () => {
            jQuery('.vbo-report-auto-exporter-payload-examples-wrap').appendTo('.vbo-report-auto-exporter-payload-examples-helper');
        },
    });

    jQuery('.vbo-report-auto-exporter-payload-examples-wrap').appendTo(modal_body);
}

/**
 * Copies the JSON Payload example into the actual editor.
 */
function vboCronReportAutoExportCopyJSON() {
    let codeExample = document.querySelector('code.vbo-report-auto-exporter-example').innerText;
    try {
        Joomla.editors.instances['vikcronparams_payload'].setValue(codeExample);
    } catch (e) {
        // log the error
        console.error(e);
        // default to populating the text-area field
        document.querySelector('[name="vikcronparams[payload]"]').value = codeExample;
    }
}

/**
 * DOM Ready callback.
 */
jQuery(function() {

    // trigger the change of the report
    jQuery('select[data-report-param="report"]').trigger('change');

    // trigger the change of the export format
    setTimeout(() => {
        jQuery('select[data-report-param="format"]').trigger('change');
    }, 200);
});
JS;

        // build the HTML helper elements for the JSON Payload examples
        $ex_today_dt = date('Y-m-d');
        $ex_yesterday_dt = date('Y-m-d', strtotime('-1 day'));
        $ex_tomorrow_dt = date('Y-m-d', strtotime('+1 day'));
        $ex_weekago_dt = date('Y-m-d', strtotime('-1 week'));
        $ex_twoweeksago_dt = date('Y-m-d', strtotime('-2 weeks'));
        $ex_monthago_dt = date('Y-m-d', strtotime('-1 month'));
        $ex_monthin_dt = date('Y-m-d', strtotime('+1 month'));
        $ex_yearago_dt = date('Y-m-d', strtotime('-1 year'));
        $payload_examples_html = <<<HTML
<div class="vbo-report-auto-exporter-payload-examples-wrap">
    <p class="info">Reports often require date filters for their execution. Dynamic values can be used for the JSON Payload in order to let the report run every day with the properly calculated dates.</p>
    <ul>
        <li>
            <code>"{Y-m-d}"</code>
            <span>Stands for <strong>today's date</strong> in military format. If ran today it would be converted into "<strong>$ex_today_dt</strong>".</span>
        </li>
        <li>
            <code>"{today}"</code>
            <span>Stands for <strong>today's date</strong> in military format. It's the same as using <code>"{Y-m-d}"</code>.</span>
        </li>
        <li>
            <code>"{Y-m-d -1 day}"</code>
            <span>Stands for <strong>yesterday's date</strong> in military format. If ran today it would be converted into "<strong>$ex_yesterday_dt</strong>".</span>
        </li>
        <li>
            <code>"{yesterday}"</code>
            <span>Stands for <strong>yesterday's date</strong> in military format. It's the same as using <code>"{Y-m-d -1 day}"</code>.</span>
        </li>
        <li>
            <code>"{Y-m-d +1 day}"</code>
            <span>Stands for <strong>tomorrow's date</strong> in military format. If ran today it would be converted into "<strong>$ex_tomorrow_dt</strong>".</span>
        </li>
        <li>
            <code>"{tomorrow}"</code>
            <span>Stands for <strong>tomorrow's date</strong> in military format. It's the same as using <code>"{Y-m-d +1 day}"</code>.</span>
        </li>
        <li>
            <code>"{tomorrow +1 day}"</code>
            <span>Stands for the <strong>day after tomorrow</strong> in military format. It's the same as using <code>"{Y-m-d +2 days}"</code>.</span>
        </li>
        <li>
            <code>"{Y-m-d -2 weeks}"</code>
            <span>Represents the date of <strong>two weeks ago</strong>. If ran today it would be converted into "<strong>$ex_twoweeksago_dt</strong>".</span>
        </li>
        <li>
            <code>"{today +2 weeks}"</code>
            <span>Represents the date of the <strong>next two weeks</strong>. It's the same as using <code>"{today +14 days}"</code>.</span>
        </li>
        <li>
            <code>"{Y-m-d -1 month}"</code>
            <span>Represents <strong>today's date minus one month</strong>. If ran today it would be converted into "<strong>$ex_monthago_dt</strong>".</span>
        </li>
        <li>
            <code>"{Y-m-d +1 month}"</code>
            <span>Represents <strong>today's date plus one month</strong>. If ran today it would be converted into "<strong>$ex_monthin_dt</strong>".</span>
        </li>
        <li>
            <code>"{Y-m-d -1 year}"</code>
            <span>Represents the date of <strong>one year ago</strong>. If ran today it would be converted into "<strong>$ex_yearago_dt</strong>".</span>
        </li>
    </ul>
    <div class="vbo-admin-container vbo-admin-container-full vbo-admin-container-compact">
        <div class="vbo-params-wrap">
            <div class="vbo-params-container">
                <div class="vbo-params-block">
                    <div class="vbo-param-container">
                        <div class="vbo-param-label">
                            <span>Example for a range of dates from <strong>last week</strong> till <strong>today</strong></span>
                        </div>
                        <div class="vbo-param-setting">
                            <pre>
<code>{
  "from_date": "{today -1 week}",
  "to_date":   "{Y-m-d}"
}</code>
                            </pre>
                        </div>
                    </div>
                    <div class="vbo-param-container">
                        <div class="vbo-param-label">
                            <span>If executed <strong>today</strong>, it would be converted into</span>
                        </div>
                        <div class="vbo-param-setting">
                            <pre>
<code>{
  "from_date": "$ex_weekago_dt",
  "to_date":   "$ex_today_dt"
}</code>
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;

        // inline style for CodeMirror
        JFactory::getDocument()->addStyleDeclaration('.CodeMirror { height: auto !important; }');

        // return the whole Cron Job parameters list
        return [
            'cron_lbl' => [
                'type' => 'custom',
                'label' => '',
                'html' => '<h4>'.
                    '<i class="'.VikBookingIcons::i('cash-register').
                    '"></i>&nbsp;<i class="'.VikBookingIcons::i('download').
                    '"></i>'.
                    '&nbsp;'.$this->getTitle().
                    '</h4>'.
                    "<script>$payload_helper_js</script>".
                    '<div class="vbo-report-auto-exporter-payload-examples-helper" style="display: none;">'.$payload_examples_html.
                    '</div>',
            ],
            'report' => [
                'type' => 'select',
                'label' => trim(preg_replace("/[^A-Z0-9 ]/i", '', JText::_('VBOREPORTSELECT'))),
                'options' => $eligible_reports,
                'attributes' => [
                    'onchange' => 'vboCronReportAutoExportSetData(this.value);',
                    'data-report-param' => 'report',
                ],
            ],
            'format' => [
                'type' => 'select',
                'label' => JText::_('VBO_EXPORT_AS'),
                'default' => 'auto',
                'options' => $all_export_formats,
                'attributes' => [
                    'onchange' => 'vboCronReportAutoExportCheckFormat(this.value);',
                    'data-report-param' => 'format',
                ],
                'help' => JText::_('VBO_REPORT_EXPORT_FORMAT'),
            ],
            'payload_example' => [
                'type' => 'custom',
                'label' => 'Payload Example',
                'html' => '<div><pre><code class="json vbo-report-auto-exporter-example">'.$def_payload_pretty.
                    '</code></pre></div>',
                    'help' => JText::_('VBO_REPORT_EXPORT_PAYLOAD_HELP').
                    '<br/>'.
                    '<a href="JavaScript: void(0);" onclick="vboCronReportAutoExportCopyJSON();"><i class="'.VikBookingIcons::i('copy').
                    '"></i> '.JText::_('VBO_COPY').
                    '</a>'.
                    ' - '.
                    '<a href="JavaScript: void(0);" onclick="vboCronReportAutoExportExamples();">'.JText::_('VBO_EXAMPLES').
                    '</a>',
            ],
            'payload' => [
                'type' => 'codemirror',
                'label' => 'JSON Payload',
                'default' => $def_payload_pretty,
                'options' => [
                    'width' => '100%',
                    'height' => 200,
                    'col' => 70,
                    'row' => 10,
                    'buttons' => false,
                    'id' => null,
                    'params' => [
                        'syntax' => 'json',
                    ],
                ],
                'help' => '<i class="vboicn-lifebuoy"></i> '.JText::_('VBO_AUTO_EXPORT_JSON_HELP'),
            ],
            'test_mode' => [
                'type' => 'checkbox',
                'label' => JText::_('VBOCRONSMSREMPARAMTEST'),
                'default' => 0,
            ],
            'recipient_email' => [
                'type' => 'text',
                'label' => JText::_('VBCUSTOMEREMAIL'),
                'help' => JText::_('VBO_CONDTEXT_RULE_ATTFILES_DESCR'),
                'default' => VikBooking::getAdminMail(),
            ],
            'save_path' => [
                'type' => 'text',
                'label' => JText::_('VBSAVE'),
                'help' => JText::_('VBOINIPATH').
                    ' (Base: '.(VBOPlatformDetection::isWordPress() ? ABSPATH : JPATH_SITE).
                    ')',
                'default' => rtrim(VBOFactory::getConfig()->get('backupfolder', ''), DIRECTORY_SEPARATOR),
            ],
            'rm_file' => [
                'type' => 'select',
                'label' => JText::_('VBO_REMOVE_LOCAL_FILE'),
                'help' => JText::_('VBO_AUTO_EXPORT_RMFILE_HELP'),
                'default' => 0,
                'options' => [
                    1 => JText::_('VBYES'),
                    0 => JText::_('VBNO'),
                ],
            ],
        ];
    }

    /**
     * Returns the title of the cron job.
     * 
     * @return  string
     */
    public function getTitle()
    {
        return JText::_('VBMENUPMSREPORTS') . ' - ' . JText::_('VBO_AUTO_EXPORT');
    }

    /**
     * Executes the cron job.
     * 
     * @return  boolean  True on success, false otherwise.
     */
    protected function execute()
    {
        $vbo_app = VikBooking::getVboApplication();

        // build the request payload
        $json_payload   = $this->params->get('payload', '{}');
        $report_payload = $this->buildReportPayload($json_payload);

        if (!is_array($report_payload) || !$report_payload) {
            $this->output('<p>Could not build JSON Report Payload: ' . $json_payload . '</p>');
            return false;
        }

        // execution output
        $this->output('<p>JSON Report Payload built:</p>');
        $this->output('<pre>' . json_encode($report_payload, JSON_PRETTY_PRINT) . '</pre>');

        // invoke report loader to load the dependencies
        $report_loader = VBOReportLoader::getInstance();

        // inject report vars before invoking the report's constructor
        VikBookingReport::setRequestVars($report_payload);

        // get an instance of the report
        $report_obj = $report_loader->getDriver($this->params->get('report', ''));

        // the lines to export
        $csvlines = [];

        if (!$report_obj) {
            $this->output('<p>Could not instantiate the Report object.</p>');
            return false;
        }

        /**
         * One cron-job report payload allows to execute actions on all report profiles,
         * or just some report profiles, even on a single one. Supported payload properties:
         * 
         * bool     _allReportProfiles  Highest priority, if true, all report profiles will be parsed.
         * string   _reportProfile      Backward compatibility property to identify a single profile id.
         * string   _reportProfiles     Comma separated string of profile ids to parse (multiple allowed).
         * 
         * @since   1.18.7 (J) - 1.8.7 (WP)
         */

        // gather the list of report profiles to parse, if any
        $parseReportProfiles = [];

        // listings filtering validation required
        $checkListingsFiltering = false;

        if ($report_obj->allowsProfileSettings()) {
            // load all configured report profiles
            $profiles = $report_obj->getSettingProfiles();

            // collect profile instructions from report payload
            if ($report_payload['_allReportProfiles'] ?? false) {
                // parse all report profiles
                $parseReportProfiles = $profiles;
                // validate listings filtering
                $checkListingsFiltering = true;
            } elseif (($report_payload['_reportProfile'] ?? '') && is_string($report_payload['_reportProfile'])) {
                // property instruction for backward compatibility that will include a single profile id
                $profile_parts = explode(',', $report_payload['_reportProfile']);
                $report_payload['_reportProfile'] = trim($profile_parts[0]);
                if (!isset($profiles[$report_payload['_reportProfile']])) {
                    $this->output('<p>' . sprintf('Report profile setting identifier [%s] does not exist.', $report_payload['_reportProfile']) . '</p>');
                    return false;
                }
            } elseif (($report_payload['_reportProfiles'] ?? '') && is_string($report_payload['_reportProfiles'])) {
                // property instruction to include one or multiple profile ids
                $profile_parts = explode(',', $report_payload['_reportProfiles']);
                foreach ($profile_parts as $profile_id) {
                    $profile_id = trim($profile_id);
                    if ($profile_id && isset($profiles[$profile_id])) {
                        // set valid profile to parse
                        $parseReportProfiles[$profile_id] = $profiles[$profile_id];
                    }
                }
                if (!$parseReportProfiles) {
                    // none of the profiles specified is valid
                    $this->output('<p>' . sprintf('Invalid report profile identifiers: [%s].', $report_payload['_reportProfiles']) . '</p>');
                    return false;
                }
                // validate listings filtering
                $checkListingsFiltering = true;
            }
        }

        // multiple report profiles validation for listings filtering
        if ($checkListingsFiltering && count($parseReportProfiles) > 1) {
            // multiple profiles found, ensure to include only complete profiles
            foreach ($parseReportProfiles as $profile_id => $profile_name) {
                // access the report object with the current profile
                $report_obj = $report_loader->getDriver($this->params->get('report', ''));
                $report_obj->setActiveProfile($profile_id);
                if ($report_obj->allowsProfileListings()) {
                    // load profile settings
                    $reportSettings = $report_obj->loadSettings();
                    if (empty($reportSettings['_listings']) && empty($reportSettings['listings'])) {
                        // multiple profiles configured for a report that allows listings filtering
                        // this must be an incomplete report profile that should not be included
                        unset($parseReportProfiles[$profile_id]);
                    }
                }
            }

            if (!$parseReportProfiles) {
                // raise an error
                $this->output('<p>Multiple profiles found, but none of them was properly configured to filter listings.</p>');
                $this->appendLog('Multiple profiles found, but none of them was properly configured to filter listings.');
                return false;
            }
        }

        if (!$parseReportProfiles) {
            // when no profiles to parse, set a default identifier name to boolean true
            $parseReportProfiles[] = true;
        }

        // count the number of report profiles to parse
        $reportProfilesCount = count($parseReportProfiles);

        // success and failure counters
        $successCounter = 0;
        $failureCounter = 0;

        // iterate all report profiles
        foreach ($parseReportProfiles as $profile_id => $profile_name) {
            // tell if we are parsing a real profile, or just the report instance
            $isReportProfile = $profile_id && $profile_name && $profile_name !== true;

            // determine the profile logs prefix, if any
            $profileLogsPrefix = $isReportProfile ? sprintf('[%s] ', (string) $profile_name) : '';

            // always get an instance of the report
            $report_obj = $report_loader->getDriver($this->params->get('report', ''));

            if ($isReportProfile) {
                // set active report profile settings at runtime
                $report_obj->setActiveProfile($profile_id);
            }

            // set report options from payload for those reports who do not rely on request values only
            $report_obj->setReportOptions($report_payload);

            // set the global scope
            $report_obj->setScope('cron');

            // obtain the export format
            $format = $this->params->get('format', '');
            $format = $format ?: $report_payload['_reportAction'] ?? '';

            // check for report action data
            if ($report_payload['_reportData'] ?? []) {
                // set action data
                $report_obj->setActionData((array) $report_payload['_reportData']);
            }

            // check for test mode
            if ((int) $this->params->get('test_mode', 0)) {
                $this->output('<p>Test mode enabled. Aborted.</p>');
                return false;
            }

            if (!method_exists($report_obj, 'customExport')) {
                // set report format
                $report_obj->setExportCSVFormat($format);

                // let the report build the lines to export
                $csvlines = $report_obj->getExportCSVLines();
                if (!$csvlines) {
                    // no data to export
                    $report_error = $report_obj->getError();
                    $this->output('<p>' . $profileLogsPrefix . 'No data to export.' . (!empty($report_error) ? " {$report_error}" : '') . '</p>');
                    $this->appendLog($profileLogsPrefix . 'No data to export.' . (!empty($report_error) ? " {$report_error}" : '') . '');
                    if ($reportProfilesCount > 1) {
                        // process the next profile
                        $failureCounter++;
                        continue;
                    } else {
                        // terminate the process
                        return false;
                    }
                }
            }

            // the name of the exported file
            $export_fname = $report_obj->getExportCSVFileName();
            $export_fname_pretty = $report_obj->getExportCSVFileName($cut_suffix = true, $suffix = '', $pretty = true);

            // build the resource file pointer/handler
            $export_fpath = $this->params->get('save_path', '');
            if (!$export_fpath) {
                // default to the backups directory
                $export_fpath = VBOFactory::getConfig()->get('backupfolder', '');
            }
            $export_fpath = JPath::clean($export_fpath . DIRECTORY_SEPARATOR . $export_fname);

            // create (or re-use) the file resource
            $export_fp = fopen($export_fpath, 'w');
            if (!$export_fp) {
                // could not proceed
                $this->output('<p>' . $profileLogsPrefix . 'Could not create the resource file pointer containing the exported report: ' . $export_fpath . '</p>');
                $this->appendLog($profileLogsPrefix . 'Could not create the resource file pointer containing the exported report: ' . $export_fpath);
                if ($reportProfilesCount > 1) {
                    // process the next profile
                    $failureCounter++;
                    continue;
                } else {
                    // terminate the process
                    return false;
                }
            }

            // set the resource file pointer that the report will be using
            $report_obj->setExportCSVHandler($export_fp);

            // write the exported data on file
            $data_exported = true;
            $def_export_method = true;

            if (!method_exists($report_obj, 'customExport')) {
                // regular CSV export method
                if (!$report_obj->outputCSV($csvlines)) {
                    $data_exported = false;
                }
            } else {
                /**
                 * Determine how the custom export command should be executed, by supporting custom actions.
                 * 
                 * @since   1.17.1 (J) - 1.7.1 (WP)
                 */
                $custom_export_type = null;
                if ($format && !in_array($format, $this->default_exp_formats_list)) {
                    // we have a custom scoped action to run
                    $custom_export_type = $format;
                    // turn flag off for default export method
                    $def_export_method = false;
                }

                // custom export method declared within the report
                if (!$report_obj->customExport($custom_export_type)) {
                    // turn flag off
                    $data_exported = false;
                }
            }

            if (!$data_exported) {
                // an error occurred, attempt to log the result
                if ($def_export_method) {
                    $this->output('<p>' . $profileLogsPrefix . 'Could not write report data onto: ' . $export_fpath . '</p>');
                    $this->appendLog($profileLogsPrefix . 'Could not write report data onto: ' . $export_fpath);
                } else {
                    // check for errors to eventually log them
                    $custom_export_error = $report_obj->getError();
                    if ($custom_export_error) {
                        $this->output('<p>' . $profileLogsPrefix . 'Errors with the report execution: ' . $custom_export_error . '</p>');
                        $this->appendLog($profileLogsPrefix . 'Errors with the report execution: ' . $custom_export_error);
                    } else {
                        $this->output('<p>' . $profileLogsPrefix . 'Report execution completed with an error.</p>');
                        $this->appendLog($profileLogsPrefix . 'Report execution completed with an error.');
                    }
                }

                if ($reportProfilesCount > 1) {
                    // process the next profile
                    $failureCounter++;
                    continue;
                } else {
                    // terminate the process
                    return false;
                }
            }

            // export completed, increase counter
            $successCounter++;

            // log the operation result
            if ($def_export_method) {
                // log the file size details for written data
                $export_fsize = @filesize($export_fpath);
                $export_fsize = JHtml::_('number.bytes', (int) $export_fsize, 'auto', 0);
                $this->output('<p>' . $profileLogsPrefix . 'Report successfully exported onto: ' . $export_fpath . ' (' . $export_fsize . ')</p>');
                $this->appendLog($profileLogsPrefix . 'Report successfully exported onto: ' . $export_fpath . ' (' . $export_fsize . ')');
            } else {
                // set output and log
                $this->output('<p>' . $profileLogsPrefix . 'Report executed successfully.</p>');
                $this->appendLog($profileLogsPrefix . 'Report executed successfully.');
            }

            // prepare the email sending assets
            $sender = VikBooking::getSenderMail();
            $subject = VikBooking::getFrontTitle() . ' - ' . $report_obj->getName();

            // check if the exported file should be sent via email
            $email_addr = $this->params->get('recipient_email', '');

            // regular or custom file export through a file handler
            if ($def_export_method && $email_addr) {
                $content = $profileLogsPrefix . $this->getTitle() . ":\n" . $export_fname_pretty;
                if ($vbo_app->sendMail($sender, $sender, $email_addr, $sender, $subject, $content, $is_html = false, 'base64', $export_fpath)) {
                    // success
                    $this->output('<p>' . $profileLogsPrefix . 'Exported file successfully sent via email to: ' . $email_addr . '</p>');
                    $this->appendLog($profileLogsPrefix . 'Exported file successfully sent via email to: ' . $email_addr);
                } else {
                    // log the error
                    $this->output('<p>' . $profileLogsPrefix . 'Could not send the exported file via email to: ' . $email_addr . '</p>');
                    $this->appendLog($profileLogsPrefix . 'Could not send the exported file via email to: ' . $email_addr);
                }
            }

            // check if a report custom action generated a resource file
            $resources = $report_obj->getResourceFiles();
            if ($resources && $email_addr) {
                // prepare message and attachments
                $resource_attachs = [];
                $content = $profileLogsPrefix . $this->getTitle() . ":\n";

                foreach ($resources as $resource_elem) {
                    // set resource file path
                    if ($resource_path = $resource_elem->getPath()) {
                        $resource_attachs[] = $resource_path;
                    }

                    // set resource summary
                    $content .= $resource_elem->getSummary() . "\n";
                }

                // send message
                if ($vbo_app->sendMail($sender, $sender, $email_addr, $sender, $subject, rtrim($content, "\n"), $is_html = false, 'base64', $resource_attachs)) {
                    $this->output('<p>' . $profileLogsPrefix . 'Resource file successfully sent via email to: ' . $email_addr . '</p>');
                    $this->appendLog($profileLogsPrefix . 'Resource file successfully sent via email to: ' . $email_addr);
                }
            }
        }

        // return true if at least one profile completed the export with success
        return $successCounter ? true : false;
    }

    /**
     * Helper method to suggest the default value in the JSON Payload for a specific report filter.
     * 
     * @param   array   $filter   the whole filter associative array set by the PMS report.
     * 
     * @return  string|array|int  the suggested default value to be JSON encoded.
     */
    protected function getFilterDefaultValue(array $filter)
    {
        $def_value = isset($filter['type']) && $filter['type'] == 'select' && isset($filter['multiple']) && $filter['multiple'] ? [] : '';

        if (is_string($def_value)) {
            if (preg_match("/^(id)/i", $filter['name']) || preg_match("/(id)$/i", $filter['name'])) {
                $def_value = 0;
            }
        }

        if (preg_match("/(checkout|todate|dateto)/i", $filter['name'])) {
            $def_value = '{Y-m-d +1 day}';
        } elseif (preg_match("/(date|checkin)/i", $filter['name'])) {
            $def_value = '{Y-m-d}';
        }

        return $def_value;
    }

    /**
     * Given the Cron Job payload, builds an associative array of data to inject to the report.
     * 
     * @param   string  $json_payload   the raw Cron Job parameter provided.
     * 
     * @return  array
     */
    protected function buildReportPayload($json_payload)
    {
        $payload = (array) json_decode($json_payload, true);

        if (!$payload) {
            return [];
        }

        // recursively apply the parser on the payload values
        $payload = $this->parsePayloadValues($payload);

        // return the payload just built
        return $payload;
    }

    /**
     * Recursively applies the parser on the payload values.
     * 
     * @param   array   $payload  The payload array values to parse.
     * 
     * @return  array
     * 
     * @since   1.17.1 (J) - 1.7.1 (WP)
     */
    protected function parsePayloadValues(array $payload)
    {
        foreach ($payload as $prop => &$val) {
            if (is_array($val) || is_object($val)) {
                // apply recursively
                $val = $this->parsePayloadValues((array) $val);

                // go next
                continue;
            }

            if (!is_string($val) || !preg_match("/^\{.+\}$/i", $val)) {
                // we parse only special strings within curly brackets
                continue;
            }

            if (isset($matches)) {
                // reset any previous regex
                unset($matches);
            }

            // look for current date or datetime (Y-m-d or similar format)
            if (preg_match("/^\{(y|m|d|\-|\_|\/| |h|i|s|\:|\.)+\}$/i", $val)) {
                // set requested value
                $val = date(str_replace(['{', '}'], '', trim($val)));
                continue;
            } elseif (preg_match("/^\{(y|m|d|\-|\_|\/| |h|i|s|\:|\.)+([\+\-]\d{1,3}\s?(days?|weeks?|months?|years?))\}$/i", $val, $matches) && isset($matches[2])) {
                // matched a dynamic date like "{Y-m-d -1 week}"
                $dt_format = trim(str_replace(['{', '}', $matches[2]], '', $val));
                $val = date($dt_format, strtotime($matches[2], strtotime(date($dt_format))));
            } elseif (preg_match("/^\{(today|tomorrow|yesterday)\s?([\+\-]\d{1,3}\s?(days?|weeks?|months?|years?))\}$/i", $val, $matches) && isset($matches[2])) {
                // matched a dynamic date like "{today +1 week}"
                $dt_format = 'Y-m-d';
                $val = date($dt_format, strtotime($matches[2], strtotime($matches[1])));
            } elseif (preg_match("/^\{(today|tomorrow|yesterday)\}$/i", $val, $matches) && isset($matches[1])) {
                // matched a dynamic date like "{tomorrow}"
                $dt_format = 'Y-m-d';
                $val = date($dt_format, strtotime($matches[1]));
            }
        }

        // unset last reference
        unset($val);

        // return the payload just built
        return $payload;
    }
}
