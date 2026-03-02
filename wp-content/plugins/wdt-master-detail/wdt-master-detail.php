<?php


namespace WDTMasterDetail;

/**
 * @package Master-Detail Tables for wpDataTables
 * @version 2.0.4
 */
/*
Plugin Name: Master-Detail Tables for wpDataTables
Plugin URI: https://wpdatatables.com/documentation/addons/master-detail-tables/
Description: A wpDataTables addon which allows showing additional details for a specific row in a popup or a separate page or post. Handy when you would like to keep fewer columns in the table, while allowing user to access full details of particular entries.
Version: 2.0.4
Author: Melograno Ventures
Author URI: https://melograno.io
Text Domain: wpdatatables
*/

use Exception;
use MasterDetailWDTColumn;
use WDTConfigController;
use WDTColumn;
use WDTException;
use WDTTools;
use WP_Error;
use WPDataTable;
use Connection;

defined('ABSPATH') or die('Access denied');
// Full path to the WDT Master-detail root directory
define('WDT_MD_ROOT_PATH', plugin_dir_path(__FILE__));
// URL of WDT Master-detail plugin
define('WDT_MD_ROOT_URL', plugin_dir_url(__FILE__));
// Basename of WDT Master-detail plugin
define('WDT_MD_BASENAME', plugin_basename(__FILE__));
// Current version of WDT Master-detail plugin
define('WDT_MD_VERSION', '2.0.4');
// Required wpDataTables version
define('WDT_MD_VERSION_TO_CHECK', '7.3');
// Path to Master-detail templates
define('WDT_MD_TEMPLATE_PATH', WDT_MD_ROOT_PATH . 'templates/');

// Init Master-detail for wpDataTables add-on
add_action('plugins_loaded', array('WDTMasterDetail\Plugin', 'init'), 10);

/**
 * Class Plugin
 * Main entry point of the wpDataTables Master-detail add-on
 * @package WDTMasterDetail
 */
class Plugin
{

    public static $initialized = false;

    /**
     * Instantiates the class
     * @return bool
     */
    public static function init()
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        // Check if wpDataTables is installed
        if (!defined('WDT_ROOT_PATH')) {
            add_action('admin_notices', array('WDTMasterDetail\Plugin', 'wdtNotInstalled'));
            deactivate_plugins(WDT_MD_BASENAME);
            return false;
        }

        // Check if wpDataTables required version is installed
        if (version_compare(WDT_CURRENT_VERSION, WDT_MD_VERSION_TO_CHECK) < 0) {
            // Show message if required wpDataTables version is not installed
            add_action('admin_notices', array('WDTMasterDetail\Plugin', 'wdtRequiredVersionMissing'));
            deactivate_plugins(WDT_MD_BASENAME);
            return false;
        }

        // Add JS and CSS for editable tables on backend
        add_action('wpdatatables_enqueue_on_edit_page', array('WDTMasterDetail\Plugin', 'wdtMasterDetailEnqueueBackend'));

        // Add JS and CSS for editable tables on frontend
        add_action('wpdatatables_enqueue_on_frontend', array('wdtMasterDetail\Plugin', 'wdtMasterDetailEnqueueFrontend'));

        // Add "Master-Detail" tab on table configuration page
        add_action('wpdatatables_add_table_configuration_tab', array('WDTMasterDetail\Plugin', 'addMasterDetailSettingsTab'));

        // Add tab panel for "Master-detail" tab on table configuration page
        add_action('wpdatatables_add_table_configuration_tabpanel', array('WDTMasterDetail\Plugin', 'addMasterDetailSettingsTabPanel'));

        // Add element in Display column settings for "Master-detail" table
        add_action('wpdatatables_add_column_display_settings_element', array('WDTMasterDetail\Plugin', 'addMasterDetailColumnSettingsElement'));

        // Add new column type option
        add_action('wpdatatables_add_custom_column_type_option', array('WDTMasterDetail\Plugin', 'addMasterDetailColumnTypeOption'));

        // Extend table config before saving table to DB
        add_filter('wpdatatables_filter_insert_table_array', array('wdtMasterDetail\Plugin', 'extendTableConfig'), 10, 1);

        // Extend WPDataTable Object with new properties
        add_action('wpdatatables_extend_wpdatatable_object', array('wdtMasterDetail\Plugin', 'extendTableObject'), 10, 2);

        // Extend table description before returning it to the front-end
        add_filter('wpdatatables_filter_table_description', array('wdtMasterDetail\Plugin', 'extendJSONDescription'), 10, 3);

        // Add custom modal in DOM
        add_action('wpdatatables_add_custom_modal', array('wdtMasterDetail\Plugin', 'insertModal'), 10, 1);

        // Add custom modal in DOM
        add_action('wpdatatables_add_custom_template_modal', array('wdtMasterDetail\Plugin', 'insertTemplateModal'), 10, 1);

        // Prepare column data
        add_filter('wpdatatables_prepare_column_data', array('wdtMasterDetail\Plugin', 'prepareColumnData'), 10, 2);

        // Custom populate cells
        add_action('wpdatatables_custom_populate_cells', array('wdtMasterDetail\Plugin', 'fillCellsMasterDetail'), 10, 2);

        // Custom prepare output data
        add_filter('wpdatatables_custom_prepare_output_data', array('wdtMasterDetail\Plugin', 'prepareOutputDataMasterDetails'), 10, 5);

        // Disable wpdatatables features for new column (sorting,searching and filtering)
        add_action('wpdatatables_columns_from_arr', array('wdtMasterDetail\Plugin', 'setColumnDetails'), 10, 4);

        // Include file that contaions MasterDetailWDTColumn class from MD in wpdt
        add_filter('wpdatatables_column_formatter_file_name', array('wdtMasterDetail\Plugin', 'columnFormatterFileName'), 10, 2);

        // Filtering column types array
        add_filter('wpdatatables_columns_types_array', array('wdtMasterDetail\Plugin', 'columnsTypesArray'), 10, 3);

        // Add and save custom column
        add_action('wpdatatables_add_and_save_custom_column', array('wdtMasterDetail\Plugin', 'saveColumns'), 10, 4);

        // Removing columns that are not in source
        add_filter('wpdatatables_columns_not_in_source', array('wdtMasterDetail\Plugin', 'removeColumnsNotInSource'), 10, 4);

        // Filter the content with detail placeholders with different priority
        add_filter( 'the_content', array('wdtMasterDetail\Plugin', 'filterTheContent'), has_filter('tcb_remove_deprecated_strings') ? 999 : 10);
        add_filter( 'the_content', array('wdtMasterDetail\Plugin', 'filterTheContent'), 999);

        // Filter the content with detail placeholders Goodlayers plugin
        add_filter( 'gdlr_core_the_content', array('wdtMasterDetail\Plugin', 'filterTheContent'));
        add_filter( 'gdlr_core_escape_content', array('wdtMasterDetail\Plugin', 'filterTheContent'));

        // Filter the content with detail placeholders for Thrive Architect
        add_filter( 'tcb_remove_deprecated_strings', array('wdtMasterDetail\Plugin', 'filterTheContent'));

        // Filter column JSON definition
        add_filter('wpdatatables_extend_column_js_definition', array('wdtMasterDetail\Plugin', 'extendColumnJSONDefinition'), 10, 2);

        // Filter data column properties
        add_filter('wpdatatables_filter_data_column_properties', array('wdtMasterDetail\Plugin', 'extendDataColumnProperties'), 10, 3);

        // Filter column params
        add_filter('wpdatatables_filter_column_params', array('wdtMasterDetail\Plugin', 'extendColumnParams'), 10, 2);

        // Filter column options
        add_filter('wpdatatables_filter_column_options', array('wdtMasterDetail\Plugin', 'extendColumnOptions'), 10, 2);

        // Filter supplementary array column object
        add_filter('wpdatatables_filter_supplementary_array_column_object', array('wdtMasterDetail\Plugin', 'extendSupplementaryArrayColumnObject'), 10, 3 );

        // Extend column config object
        add_filter('wpdatatables_filter_column_config_object', array('wdtMasterDetail\Plugin', 'extendColumnConfigObject'), 10, 2 );

        // Extend column description object
        add_filter('wpdatatables_filter_column_description_object', array('wdtMasterDetail\Plugin', 'extendColumnDescriptionObject'), 10, 3 );

        // Extend datacolumn object
        add_filter('wpdatatables_extend_datacolumn_object', array('wdtMasterDetail\Plugin', 'extendDataColumnObject'), 10, 2);

        // Extend small column block
        add_action('wpdatatables_add_small_column_block', array('WDTMasterDetail\Plugin', 'addMasterDetailSmallBlack'));

        // Add Master-Detail activation setting
        add_action('wpdatatables_add_activation', array('WDTMasterDetail\Plugin', 'addMasterDetailActivation'));

        // Enqueue Master-Detail add-on files on back-end settings page
        add_action('wpdatatables_enqueue_on_settings_page', array('WDTMasterDetail\Plugin', 'wdtMasterDetailEnqueueBackendSettings'));

        // Check auto update
        add_filter('pre_set_site_transient_update_plugins', array('WDTMasterDetail\Plugin', 'wdtCheckUpdateMasterDetail'));

        // Check plugin info
        add_filter('plugins_api', array('WDTMasterDetail\Plugin', 'wdtCheckInfoMasterDetail'), 10, 3);

        // Add a message for unavailable auto update if plugin is not activated
        add_action('in_plugin_update_message-' . plugin_basename(__FILE__), array('WDTMasterDetail\Plugin', 'addMessageOnPluginsPageMasterDetail'));

        // Add error message on plugin update if plugin is not activated
        add_filter('upgrader_pre_download', array('WDTMasterDetail\Plugin', 'addMessageOnUpdateMasterDetail'), 10, 4);

        // Filter Columns CSS
        add_filter('wpdatatables_filter_columns_css', array('WDTMasterDetail\Plugin', 'wpdtFilterColumnsCss'), 10, 4);

        // Filter server side query
        add_filter('wpdatatables_filter_mysql_query', array('WDTMasterDetail\Plugin', 'filterSQLQuery'), 10, 2);

        if (defined('WDT_GF_VERSION') && version_compare(WDT_GF_VERSION, "1.7.4", '>')) {
            // Filter Gravity server side data
            add_filter('wpdatatables_filter_server_side_data', array('WDTMasterDetail\Plugin', 'filterGFAPIData'), 10, 3);
            // Filter Gravity server side search criteria
            add_filter('wpdatatables_gravity_filter_search_criteria', array('WDTMasterDetail\Plugin', 'filterGFAPISearchCriteria'), 10, 3);
        }

        return self::$initialized = true;
    }

    /**
     *  Filter Columns CSS
     * @param $columnsCSS
     * @param $columnObj
     * @param $tableID
     * @param $cssColumnHeader
     * @return string
     */
    public static function wpdtFilterColumnsCss( $columnsCSS, $columnObj, $tableID, $cssColumnHeader )
    {
        if ($columnObj->text_before != '') {
            $columnsCSS .= "\n#wdt-md-modal div.{$cssColumnHeader}:not(:empty):before{ content: '{$columnObj->text_before}' }";
        }
        if ($columnObj->text_after != '') {
            $columnsCSS .= "\n#wdt-md-modal div.{$cssColumnHeader}:not(:empty):after { content: '{$columnObj->text_after}' }";
        }
        if ($columnObj->color != '') {
            $columnsCSS .= "#wdt-md-modal div.{$cssColumnHeader}{ background-color: {$columnObj->color} !important; }";
        }

        return $columnsCSS;
    }
    /**
     *  Extend small column block
     * @param $tableData
     */
    public static function addMasterDetailSmallBlack( $tableData )
    {
        $advancedSettingsTable = json_decode($tableData->table->advanced_settings);
        if (isset($advancedSettingsTable->masterDetail) && $advancedSettingsTable->masterDetail != 0) {
            ob_start();
            include WDT_MD_ROOT_PATH . 'templates/master_detail_small_block.inc.php';
            $masterDetailSmallBlock = ob_get_contents();
            ob_end_clean();

            echo $masterDetailSmallBlock;
        }
    }


    /**
     *  Extend datacolumn object
     * @param $dataColumn
     * @param $dataColumnProperties
     * @return mixed
     */
    public static function extendDataColumnObject($dataColumn,$dataColumnProperties){
        if (isset($dataColumnProperties['masterDetailColumnOption'])){
            $dataColumn->masterDetailColumnOption = $dataColumnProperties['masterDetailColumnOption'];
        }else {
            $dataColumn->masterDetailColumnOption = 1;
        }
        return $dataColumn;
    }

    /**
     *  Extend column description object
     * @param $feColumn
     * @param $dbColumn
     * @param $advancedSettings
     * @return mixed
     */
    public static function extendColumnDescriptionObject( $feColumn, $dbColumn, $advancedSettings)
    {
        if (isset($advancedSettings->masterDetailColumnOption)){
            $feColumn->masterDetailColumnOption = $advancedSettings->masterDetailColumnOption;
        } else {
            $feColumn->masterDetailColumnOption = 1;
        }
        return $feColumn;
    }

    /**
     *  Extend column config object
     * @param $columnConfig
     * @param $feColumn
     * @return mixed
     */
    public static function extendColumnConfigObject($columnConfig, $feColumn)
    {
        $columnAdvancedSettings = json_decode($columnConfig['advanced_settings']);

        if (isset($feColumn->masterDetailColumnOption)) {
            $columnAdvancedSettings->masterDetailColumnOption = $feColumn->masterDetailColumnOption;
        } else {
            $columnAdvancedSettings->masterDetailColumnOption = 1;
        }

        $columnConfig['advanced_settings'] = json_encode($columnAdvancedSettings);

        return $columnConfig;
    }

    /**
     *  Extend supplementary array column object
     * @param $colObjOptions
     * @param $wdtParameters
     * @param $dataColumn_key
     * @return mixed
     */
    public static function extendSupplementaryArrayColumnObject($colObjOptions, $wdtParameters, $dataColumn_key)
    {
        if (isset($wdtParameters['masterDetailColumnOption'])) {
            $colObjOptions['masterDetailColumnOption'] = $wdtParameters['masterDetailColumnOption'][$dataColumn_key];
        } else {
            $colObjOptions['masterDetailColumnOption'] = true;
        }

        return $colObjOptions;
    }

    /**
     *  Extend column options
     * @param $columnOptions
     * @param $columnData
     * @return mixed
     */
    public static function extendColumnOptions($columnOptions, $columnData)
    {
        foreach ($columnData as $column) {
            $advancedSettings = json_decode($column->advanced_settings);

            if (isset($advancedSettings->masterDetailColumnOption) && $advancedSettings->masterDetailColumnOption == 1 ) {
                $masterDetailColumnOption[$column->orig_header] = $advancedSettings->masterDetailColumnOption;
            } else {
                $masterDetailColumnOption[$column->orig_header] = null;
            }
            $columnOptions['masterDetailColumnOption'] = $masterDetailColumnOption;
        }

        return $columnOptions;
    }

    /**
     *  Extend column params
     * @param $params
     * @param $columnData
     * @return mixed
     */
    public static function extendColumnParams($params, $columnData)
    {
        if (isset($columnData['masterDetailColumnOption'])) {
            $params['masterDetailColumnOption'] = $columnData['masterDetailColumnOption'];
        } else {
            $params['masterDetailColumnOption'] = 1;
        }
        return $params;
    }

    /**
     *  Extend data column properties
     * @param $dataColumnProperties
     * @param $wdtParameters
     * @param $key
     * @return mixed
     */
    public static function extendDataColumnProperties($dataColumnProperties, $wdtParameters, $key)
    {
        if (isset($wdtParameters['masterDetailColumnOption']) && isset($wdtParameters['masterDetailColumnOption'][$key]) && is_array($wdtParameters['masterDetailColumnOption'])) {
            $dataColumnProperties['masterDetailColumnOption'] =  $wdtParameters['masterDetailColumnOption'][$key];
        } else {
            $dataColumnProperties['masterDetailColumnOption'] = 1;
        }
        return $dataColumnProperties;
    }

    /**
     *  Extend column JSON definition
     * @param $colJsDefinition
     * @param $title
     * @return mixed
     */
    public static function extendColumnJSONDefinition($colJsDefinition, $wpdatatable)
    {
        if (isset($wpdatatable->masterDetailColumnOption)) {
            $colJsDefinition->masterDetailColumnOption = $wpdatatable->masterDetailColumnOption;
        } else {
            $colJsDefinition->masterDetailColumnOption = 1;
        }
        return $colJsDefinition;
    }

    /**
     *  Removing columns that that are not in source
     * @param $columnsNotInSource
     * @param $table
     * @param $tableId
     * @param $frontendColumns
     * @return array
     */
    public static function removeColumnsNotInSource($columnsNotInSource, $table, $tableId, $frontendColumns)
    {
        if ($frontendColumns != null) {
            foreach ($frontendColumns as $feColumn) {
                // We are only interested in masterdetail columns in this loop
                if ($feColumn->type != 'masterdetail') {
                    continue;
                }
                // Removing this column from the array of marked for deletiong
                $columnsNotInSource = array_diff($columnsNotInSource, array($feColumn->orig_header));

            }
            return $columnsNotInSource;
        }
        return array();
    }

    /**
     * Add and save custom column
     * @param $table \WPDataTable
     * @param $tableId
     * @param $frontendColumns
     * @throws Exception
     */
    public static function saveColumns($table, $tableId, $frontendColumns)
    {
        global $wpdb;
        if ($frontendColumns != null) {
            foreach ($frontendColumns as $feColumn) {
                // We are only interested in masterdetail column in this loop
                if ($feColumn->type != 'masterdetail') {
                    continue;
                }
                $wdtColumn = WDTColumn::generateColumn(
                    'masterdetail',
                    array(
                        'orig_header' => $feColumn->orig_header,
                        'display_header' => $feColumn->display_header,
                        'decimalPlaces' => $feColumn->decimalPlaces
                    )
                );
                $existingPositionQuery = $wpdb->prepare(
                    "SELECT pos
                FROM " . $wpdb->prefix . "wpdatatables_columns
                WHERE table_id = %d",
                    $tableId
                );

                $columnsPositionInSource = $wpdb->get_col($existingPositionQuery);
                $columnsPositionInSourceCounts = array_count_values($columnsPositionInSource);

                $tempMasterDetailPosition = $feColumn->pos;
                $keyExist = array_search($tempMasterDetailPosition,array_keys($columnsPositionInSourceCounts));
                $checkDuplicatePosition= $keyExist ? $columnsPositionInSourceCounts[$tempMasterDetailPosition] : 0;

                /** @var MasterDetailWDTColumn $wdtColumn */
                $columnConfig = WDTConfigController::prepareDBColumnConfig($wdtColumn, $frontendColumns, $tableId);
                $columnConfig['filter_type'] = 'none';

                if ((in_array($tempMasterDetailPosition, $columnsPositionInSource) && $checkDuplicatePosition > 1)
                    || in_array($tempMasterDetailPosition, $columnsPositionInSource) && $tempMasterDetailPosition >= count($columnsPositionInSource) ) {
                    $dataSourceColumns = $table->getColumns();
                    $columnConfig['pos'] = count($dataSourceColumns);
                } else {
                    $columnConfig['pos'] = $tempMasterDetailPosition;
                }

                WDTConfigController::saveSingleColumn($columnConfig);
            }
        }
    }

    /**
     * Filtering column types array
     * @param $columnsTypesArray
     * @param $columnsNotInSource
     * @param $columnsTypes
     * @return array
     */
    public static function columnsTypesArray($columnsTypesArray, $columnsNotInSource, $columnsTypes)
    {
        $columnsTypesArray = array_diff(array_combine($columnsNotInSource, $columnsTypes), ['masterdetail', 'formula', 'index', 'select']);
        return $columnsTypesArray;
    }

    /**
     * Format file that contain column class
     * @param $columnFormatterFileName
     * @param $wdtColumnType
     * @return string
     */
    public static function columnFormatterFileName($columnFormatterFileName, $wdtColumnType)
    {
        if ($wdtColumnType == 'masterdetail') {
            $columnFormatterFileName = WDT_MD_ROOT_PATH . $columnFormatterFileName;
        }
        return $columnFormatterFileName;
    }

    /**
     * Disable sorting and searching for Master-detail column
     * @param $obj \WPDataTable
     * @param $dataColumn
     * @param $wdtColumnTypes
     * @param $key
     * @throws WDTException
     */
    public static function setColumnDetails($obj, $dataColumn, $wdtColumnTypes, $key)
    {
        if (isset($wdtColumnTypes[$key])) {
            if ($wdtColumnTypes[$key] === 'masterdetail') {
                /** @var MasterDetailWDTColumn $dataColumn */
                $dataColumn->setSorting(false);
                $dataColumn->setSearchable(false);
                $dataColumn->setFilterType('none');
            }
        }
    }


    /**
     * Filter columns_from_arr
     * @param $obj \WPDataTable
     * @param $wdtColumnTypes
     */
    public static function fillCellsMasterDetail($obj, $wdtColumnTypes)
    {
        if (in_array('masterdetail', $wdtColumnTypes)) {
            self::populateDetailsCells($obj);
        }
    }

    /**
     * Fill cell with predefined values
     * @param $obj \WPDataTable
     */
    public static function populateDetailsCells($obj)
    {
        foreach (array_keys($obj->getWdtColumnTypes(), 'masterdetail') as $column_key) {

            $allDataRows = $obj->getDataRows();
            foreach ($allDataRows as &$row) {
                try {
                    $row[$column_key] = 'More details';
                } catch (Exception $e) {
                    $row[$column_key] = '';
                }
            }
            $obj->setDataRows($allDataRows);

        }
    }

    /**
     * Insert Modal templates
     * @param $output
     * @param $obj \WPDataTable
     * @param $main_res_dataRows
     * @param $wdtParameters
     * @param $colObjs
     * @return array
     * @throws WDTException
     */
    public static function prepareOutputDataMasterDetails($output, $obj, $main_res_dataRows, $wdtParameters, $colObjs)
    {
        $output = [];
        if (!empty($main_res_dataRows)) {
            foreach ($wdtParameters['foreignKeyRule'] as $columnKey => $foreignKeyRule) {
                if ($foreignKeyRule != null) {
                    $foreignKeyData = $obj->joinWithForeignWpDataTable($columnKey, $foreignKeyRule, $main_res_dataRows);
                    $main_res_dataRows = $foreignKeyData['dataRows'];
                }
            }
            $i = (int)$_POST['start'];
            foreach ($main_res_dataRows as $res_row) {
                $i++;
                $row = array();
                foreach ($wdtParameters['columnOrder'] as $dataColumn_key) {
                    if ($wdtParameters['data_types'][$dataColumn_key] == 'masterdetail') {
                        try {
                            $detailsValue = 'More Details';
                            $row[$dataColumn_key] = apply_filters(
                                'wpdatatables_filter_cell_output',
                                $colObjs[$dataColumn_key]->returnCellValue($detailsValue),
                                $obj->getWpId(),
                                $dataColumn_key
                            );
                        } catch (Exception $e) {
                            $row[$dataColumn_key] = '';
                        }
                    } else if ($wdtParameters['data_types'][$dataColumn_key] == 'formula') {
                        try {
                            $headers = array();
                            $headersInFormula = $obj->detectHeadersInFormula($wdtParameters['columnFormulas'][$dataColumn_key], array_keys($wdtParameters['data_types']));
                            $headers = WDTTools::sanitizeHeaders($headersInFormula);
                            $formulaVal =
                                $obj::solveFormula(
                                    $wdtParameters['columnFormulas'][$dataColumn_key],
                                    $headers,
                                    $res_row
                                );
                            $row[$dataColumn_key] = apply_filters(
                                'wpdatatables_filter_cell_output',
                                $colObjs[$dataColumn_key]->returnCellValue($formulaVal),
                                $obj->getWpId(),
                                $dataColumn_key
                            );
                        } catch (Exception $e) {
                            $row[$dataColumn_key] = 0;
                        }
                    } else if ($wdtParameters['data_types'][$dataColumn_key] == 'index') {
                        $row[$dataColumn_key] = apply_filters('wpdatatables_filter_cell_output', $colObjs[$dataColumn_key]->returnCellValue((int)$i), $obj->getWpId(), $dataColumn_key);
                    } else {
                        $row[$dataColumn_key] = apply_filters('wpdatatables_filter_cell_output', $colObjs[$dataColumn_key]->returnCellValue($res_row[$dataColumn_key]), $obj->getWpId(), $dataColumn_key);
                    }
                }
                $output[] = self::formatAjaxQueryResultRow($row, $obj);
            }
        }
        return $output;
    }

    /**
     * Formatting row data structure for ajax display table
     * @param $row - key => value pairs as column name and cell value of a row
     * @param $obj WPDataTable/WPExcelDataTable object
     * @return array
     */
    public static function formatAjaxQueryResultRow($row, $obj)
    {
        if (is_a($obj, 'WPExcelDataTable')) {
            return $row;
        } else if ( isset($_REQUEST['wdt_md_p_t_id']) && isset($_REQUEST['wdt_md_c_t_id_render'])) {
            return array_values($row);
        } else if ( isset($_REQUEST['wdt_md_p_t_id'])) {
            return $row;
        } else {
            return array_values($row);
        }
    }

    /**
     * Prepare column data
     * @param $returnArray
     * @param $column
     * @return mixed
     */
    public static function prepareColumnData($returnArray, $column)
    {
        if ($column->type === 'masterdetail') {
            $returnArray['columnTypes'][$column->orig_header] = $column->type;
        }

        if (isset($column->masterDetailColumnOption)){
            $returnArray['masterDetailColumnOption'][$column->orig_header] = isset($column->masterDetailColumnOption) ? $column->masterDetailColumnOption : null;
        }
        $returnArray['skip_thousands'][$column->orig_header] = $column->skip_thousands_separator ?? null;
        $returnArray['input_types'][$column->orig_header] = $column->input_type ?? null;
        $returnArray['idColumn'][$column->orig_header] = $column->id_column ?? null;

        return $returnArray;
    }

    /**
     * Insert Modal templates
     * @param $wpDataTable \WPDataTable
     */
    public static function insertModal($wpDataTable)
    {
        if (isset($wpDataTable->masterDetail) && $wpDataTable->masterDetail && is_admin()) {
            include WDT_MD_TEMPLATE_PATH . 'modal.inc.php';
            include WDT_MD_TEMPLATE_PATH . 'md_modal.inc.php';
        } else if (isset($wpDataTable->masterDetail) && $wpDataTable->masterDetail){
            include WDT_MD_TEMPLATE_PATH . 'md_modal.inc.php';
        }
    }

    /**
     * Insert Template Modal
     */
    public static function insertTemplateModal()
    {
        include WDT_MD_TEMPLATE_PATH . 'modal.inc.php';
    }


    /**
     * Show message if wpDataTables is not installed
     */
    public static function wdtNotInstalled()
    {
        $message = __('Master-detail Tables for wpDataTables is an add-on - please install and activate wpDataTables to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Show message if required wpDataTables version is not installed
     */
    public static function wdtRequiredVersionMissing()
    {
        $message = __('Master-Detail Tables add-on for wpDataTables requires wpDataTables version ' . WDT_MD_VERSION_TO_CHECK . '. Please update wpDataTables plugin to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Enqueue Master-detail add-on files on back-end
     */
    public static function wdtMasterDetailEnqueueBackend()
    {
        if (self::$initialized) {
            wp_enqueue_style(
                'wdt-md-stylesheet',
                WDT_MD_ROOT_URL . 'assets/css/wdt.md.css',
                array(),
                WDT_MD_VERSION
            );
            wp_enqueue_script(
                'wdt-md-backend',
                WDT_MD_ROOT_URL . 'assets/js/wdt.md.backend.js',
                array(),
                WDT_MD_VERSION,
                true
            );

            wp_enqueue_script(
                'wdt-md-frontend',
                WDT_MD_ROOT_URL . 'assets/js/wdt.md.frontend.js',
                array(),
                WDT_MD_VERSION,
                true
            );
                \WDTTools::exportJSVar('wdtMdDashboard', is_admin());
                \WDTTools::exportJSVar('wdtMdTranslationStrings', \WDTTools::getTranslationStringsPlugin());
            }
    }

    /**
     * Enqueue Master-Detail add-on files on front-end
     */
    public static function wdtMasterDetailEnqueueFrontend($wpDataTable)
    {
        if (self::$initialized) {
            if (isset($wpDataTable->masterDetail) && $wpDataTable->masterDetail) {
            wp_enqueue_script(
                'wdt-md-frontend',
                WDT_MD_ROOT_URL . 'assets/js/wdt.md.frontend.js',
                array(),
                WDT_MD_VERSION,
                true
            );

            wp_enqueue_style(
                'wdt-md-stylesheet',
                WDT_MD_ROOT_URL . 'assets/css/wdt.md.css',
                array(),
                WDT_MD_VERSION
            );
                \WDTTools::exportJSVar('wdtMdDashboard', is_admin());
                \WDTTools::exportJSVar('wdtMdTranslationStrings', \WDTTools::getTranslationStringsPlugin());
            }
        }
    }
    /**
     * Function that extend table config before saving table to the database
     * @param $tableConfig - array that contains table configuration
     * @return mixed
     */
    public static function extendTableConfig($tableConfig)
    {
        $table = apply_filters(
            'wpdatatables_before_save_table',
            json_decode(
                stripslashes_deep($_POST['table'])
            )
        );

        $advancedSettings = json_decode($tableConfig['advanced_settings']);
        if (isset($table->masterDetail)) $advancedSettings->masterDetail = $table->masterDetail;
        if (isset($table->masterDetailLogic)) $advancedSettings->masterDetailLogic = $table->masterDetailLogic;
        if (isset($table->masterDetailRender)) $advancedSettings->masterDetailRender = $table->masterDetailRender;
        if (isset($table->masterDetailSender)) $advancedSettings->masterDetailSender = $table->masterDetailSender;
        if (isset($table->masterDetailSendTableType)) $advancedSettings->masterDetailSendTableType = $table->masterDetailSendTableType;
        if (isset($table->masterDetailSendChildTableID)) $advancedSettings->masterDetailSendChildTableID = $table->masterDetailSendChildTableID;
        if (isset($table->masterDetailSendParentTableColumnIDName)) $advancedSettings->masterDetailSendParentTableColumnIDName = $table->masterDetailSendParentTableColumnIDName;
        if (isset($table->masterDetailSendChildTableColumnIDName)) $advancedSettings->masterDetailSendChildTableColumnIDName = $table->masterDetailSendChildTableColumnIDName;
        if (isset($table->masterDetailRenderPage)) $advancedSettings->masterDetailRenderPage = $table->masterDetailRenderPage;
        if (isset($table->masterDetailRenderPost)) $advancedSettings->masterDetailRenderPost = $table->masterDetailRenderPost;
        if (isset($table->masterDetailPopupTitle)) $advancedSettings->masterDetailPopupTitle = $table->masterDetailPopupTitle;
        if (isset($table->masterDetailLinkTargetAttribute)) $advancedSettings->masterDetailLinkTargetAttribute = $table->masterDetailLinkTargetAttribute;

        $tableConfig['advanced_settings'] = json_encode($advancedSettings);

        return $tableConfig;
    }

    /**
     * Function that extend $wpDataTable object with new properties
     * @param $wpDataTable \WPDataTable
     * @param $tableData \stdClass
     */
    public static function extendTableObject($wpDataTable, $tableData)
    {
        if (!empty($tableData->advanced_settings)) {
            $advancedSettings = json_decode($tableData->advanced_settings);

            if (isset($advancedSettings->masterDetail)) {
                $wpDataTable->masterDetail = $advancedSettings->masterDetail;
            }

            if (isset($advancedSettings->masterDetailLogic)) {
                $wpDataTable->masterDetailLogic = $advancedSettings->masterDetailLogic;
            }

            if (isset($advancedSettings->masterDetailRender)) {
                $wpDataTable->masterDetailRender = $advancedSettings->masterDetailRender;
            }

            if (isset($advancedSettings->masterDetailSender)) {
                $wpDataTable->masterDetailSender = $advancedSettings->masterDetailSender;
            }

            if (isset($advancedSettings->masterDetailSendTableType)) {
                $wpDataTable->masterDetailSendTableType = $advancedSettings->masterDetailSendTableType;
            }

            if (isset($advancedSettings->masterDetailSendChildTableID)) {
                $wpDataTable->masterDetailSendChildTableID = $advancedSettings->masterDetailSendChildTableID;
            }

            if (isset($advancedSettings->masterDetailSendParentTableColumnIDName)) {
                $wpDataTable->masterDetailSendParentTableColumnIDName = $advancedSettings->masterDetailSendParentTableColumnIDName;
            }

            if (isset($advancedSettings->masterDetailSendChildTableColumnIDName)) {
                $wpDataTable->masterDetailSendChildTableColumnIDName = $advancedSettings->masterDetailSendChildTableColumnIDName;
            }

            if (isset($advancedSettings->masterDetailRenderPage)) {
                $wpDataTable->masterDetailRenderPage = $advancedSettings->masterDetailRenderPage;
            }

            if (isset($advancedSettings->masterDetailRenderPost)) {
                $wpDataTable->masterDetailRenderPost = $advancedSettings->masterDetailRenderPost;
            }

            if (isset($advancedSettings->masterDetailPopupTitle)) {
                $wpDataTable->masterDetailPopupTitle = $advancedSettings->masterDetailPopupTitle;
            }

            if (isset($advancedSettings->masterDetailLinkTargetAttribute)) {
                $wpDataTable->masterDetailLinkTargetAttribute = $advancedSettings->masterDetailLinkTargetAttribute;
            }

        }

    }

    /**
     * Function that extend table description before returning it to the front-end
     *
     * @param $tableDescription \stdClass
     * @param $wpDataTable \WPDataTable
     * @return mixed
     */
    public static function extendJSONDescription($tableDescription, $tableId, $wpDataTable)
    {

        if (isset($wpDataTable->masterDetail)) {
            $tableDescription->masterDetail = $wpDataTable->masterDetail;
        }

        if (isset($wpDataTable->masterDetailLogic)) {
            $tableDescription->masterDetailLogic = $wpDataTable->masterDetailLogic;
        }

        if (isset($wpDataTable->masterDetailRender)) {
            $tableDescription->masterDetailRender = $wpDataTable->masterDetailRender;
        }

        if (isset($wpDataTable->masterDetailSender)) {
            $tableDescription->masterDetailSender = $wpDataTable->masterDetailSender;
        }

        if (isset($wpDataTable->masterDetailSendParentTableColumnIDName)) {
            $tableDescription->masterDetailSendParentTableColumnIDName = $wpDataTable->masterDetailSendParentTableColumnIDName;
        }

        if (isset($wpDataTable->masterDetailSendChildTableColumnIDName)) {
            $tableDescription->masterDetailSendChildTableColumnIDName = $wpDataTable->masterDetailSendChildTableColumnIDName;
        }

        if (isset($wpDataTable->masterDetailSendTableType)) {
            $tableDescription->masterDetailSendTableType = $wpDataTable->masterDetailSendTableType;
        }

        if (isset($wpDataTable->masterDetailSendChildTableID)) {
            $tableDescription->masterDetailSendChildTableID = $wpDataTable->masterDetailSendChildTableID;
        }

        if (isset($wpDataTable->masterDetailRenderPage)) {
            $tableDescription->masterDetailRenderPage = $wpDataTable->masterDetailRenderPage;
        }

        if (isset($wpDataTable->masterDetailRenderPost)) {
            $tableDescription->masterDetailRenderPost = $wpDataTable->masterDetailRenderPost;
        }

        if (isset($wpDataTable->masterDetailPopupTitle)) {
            $tableDescription->masterDetailPopupTitle = $wpDataTable->masterDetailPopupTitle;
        }

        if (isset($wpDataTable->masterDetailLinkTargetAttribute)) {
            $tableDescription->masterDetailLinkTargetAttribute = $wpDataTable->masterDetailLinkTargetAttribute;
        }

        if (isset($tableDescription->dataTableParams) && isset($tableDescription->dataTableParams->ajax['url'])) {
            if (isset($_REQUEST['wdt_md_c_t_id_render'])) {
                $tableDescription->dataTableParams->ajax['url'] .= '&wdt_md_c_t_id_render=' . urlencode((int)($_REQUEST['wdt_md_c_t_id_render']));
                if (isset($_REQUEST['wdt_md_p_t_id'])) {
                    $tableDescription->dataTableParams->ajax['url'] .= '&wdt_md_p_t_id=' . urlencode((int)($_REQUEST['wdt_md_p_t_id']));
                }
                if (isset($_REQUEST['wdt_md_p_t_col_name'])) {
                    $parentTableColumnIDName = sanitize_text_field($_REQUEST['wdt_md_p_t_col_name']);
                    $tableDescription->dataTableParams->ajax['url'] .= '&wdt_md_p_t_col_name=' . urlencode($parentTableColumnIDName);
                }
                if (isset($_REQUEST['wdt_md_c_t_col_name'])) {
                    $childTableColumnIDName = sanitize_text_field($_REQUEST['wdt_md_c_t_col_name']);
                    $tableDescription->dataTableParams->ajax['url'] .= '&wdt_md_c_t_col_name=' . urlencode($childTableColumnIDName);
                    if (isset($_REQUEST['wdt_md_col_value'])) {
                        $columnIDValue = sanitize_text_field($_REQUEST['wdt_md_col_value']);
                        $tableDescription->dataTableParams->ajax['url'] .= '&wdt_md_col_value=' . urlencode($columnIDValue);
                    }
                }
            }
        }

		$detailsBttnText = $wpDataTable->getTableSkin() === 'mojito' || $wpDataTable->getTableSkin() === 'dark-mojito'? '' : __('Details', 'wpdatatables');
        if (isset($wpDataTable->masterDetail) && $wpDataTable->masterDetail &&
            isset($wpDataTable->masterDetailLogic) && $wpDataTable->isEditable() && $wpDataTable->serverSide()) {
            (!isset($tableDescription->dataTableParams->buttons)) ? $tableDescription->dataTableParams->buttons = array() : '';
            $tableDescription->dataTableParams->buttons[] = array(
                'text' => $detailsBttnText,
                'className' => 'master_detail DTTT_button DTTT_button_md'
            );
        }

        return $tableDescription;
    }


    /**
     * Add Master-Detail Settings tab on table configuration page
     */
    public static function addMasterDetailSettingsTab()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/master_detail_settings_tab.inc.php';
        $masterDetailSettingsTab = ob_get_contents();
        ob_end_clean();

        echo $masterDetailSettingsTab;
    }

    /**
     * Add tablpanel for Master-Detail Settings tab on table configuration page
     */
    public static function addMasterDetailSettingsTabPanel()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/master_detail_settings_tabpanel.inc.php';
        $masterDetailSettingsTabPanel = ob_get_contents();
        ob_end_clean();

        echo $masterDetailSettingsTabPanel;
    }

    /**
     * Add element in column settings for Master-Detail table
     */
    public static function addMasterDetailColumnSettingsElement()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/master-detail-column-display-element.inc.php';
        $masterDetailColumnSettingsElement = ob_get_contents();
        ob_end_clean();

        echo $masterDetailColumnSettingsElement;
    }

    /**
     * Add new option for column type
     */
    public static function addMasterDetailColumnTypeOption()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/master-detail-column-type-option.inc.php';
        $masterDetailColumnTypeOption = ob_get_contents();
        ob_end_clean();

        echo $masterDetailColumnTypeOption;
    }

    /**
     * Get all pages from database
     */
    public static function getAllPages() {
        global $wpdb;

        $query = "SELECT post_title, guid, ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type = 'page' ORDER BY {$wpdb->prefix}posts.ID ASC ";

        $allPages = $wpdb->get_results($query, ARRAY_A);
        return $allPages;
    }

    /**
     * Get all posts from database
     */
    public static function getAllPosts() {
        global $wpdb;

        $query = "SELECT post_title, guid, ID FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type = 'post' ORDER BY {$wpdb->prefix}posts.ID ASC ";

        $allPosts = $wpdb->get_results($query, ARRAY_A);
        return $allPosts;
    }

    /**
     * Get masterDetailRenderPage and masterDetailRenderPost values from database
     */
    public static function removePlaceholdersFromContent($currentPostLink, $content) {
        global $wpdb;
        $finalPageIDs = [];

        $query = "SELECT id, advanced_settings FROM {$wpdb->prefix}wpdatatables WHERE {$wpdb->prefix}wpdatatables.id > 0";

        $advancedSettingsFromAllTables= $wpdb->get_results($query, ARRAY_A);
        foreach ($advancedSettingsFromAllTables as $advancedSetting) {
            $tempID = $advancedSetting['id'];
            $tempAdvancedSettings = json_decode($advancedSetting['advanced_settings']);
            if (isset($tempAdvancedSettings->masterDetailRenderPage) && $tempAdvancedSettings->masterDetailRenderPage != '' ){
                if ($tempAdvancedSettings->masterDetailRenderPage == $currentPostLink)
                    $finalPageIDs[] = $tempID;
            }
            if (empty($finalPageID)){
                if (isset($tempAdvancedSettings->masterDetailRenderPost) && $tempAdvancedSettings->masterDetailRenderPost != '' ){
                    if ($tempAdvancedSettings->masterDetailRenderPost == $currentPostLink)
                        $finalPageIDs[] = $tempID;
                }
            }
        }
        if (empty($finalPageID)){
            foreach ($finalPageIDs as $finalPageID){
                $columnsData = WDTConfigController::loadColumnsFromDB($finalPageID);
                $origHeaders= [];
                foreach ($columnsData as $columnData){
                    $origHeaders[] = $columnData->orig_header;
                }
                foreach ($origHeaders as $origHeader) {
                    $content = str_replace("%" . $origHeader . "%", "", $content);
                }
            }
        }
        // Remove placeholder for child table render
        $content = str_replace("%wpdatatables_md_child_table%", "", $content);

        return $content;
    }

    /**
     * Get details data array for post or page
     */
    private static function getDetailsDataArray( $wpDataTable, $childTableData, $columnIDName, $columnIDValue ) {

        if (in_array($childTableData->table_type,[ 'manual','sql', 'mysql'])) {
            $tableContent = WDTTools::applyPlaceholders($childTableData->content);
            $vendor = Connection::getVendor($wpDataTable->connection);
            $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
            $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

            $query = "SELECT * FROM (" . $tableContent . ") 
                                AS wdt_alias 
                                WHERE wdt_alias." . $leftSysIdentifier . $columnIDName . $rightSysIdentifier;
            $query .= Connection::isSeparate($childTableData->connection) ? "=" . $columnIDValue . " " : "='" . $columnIDValue . "' ";
            $query = wdtSanitizeQuery($query);

            if (Connection::isSeparate($childTableData->connection)) {
                $detailsDataArr = $wpDataTable->getDBConnection()->getAssoc($query, []);
                $sqlError = $wpDataTable->getDBConnection()->getLastError();
            } else {
                global $wpdb;
                // querying using the WP driver otherwise
                $detailsDataArr = $wpdb->get_results($query, ARRAY_A);
                $sqlError = $wpdb->last_error;
            }

            if (!empty($sqlError))
                return [];

            add_filter('wpdatatables_filter_mysql_query', array('WDTMasterDetail\Plugin', 'filterSQLQuery'), 10, 2);

            return $detailsDataArr;

        } else if ($childTableData->table_type == 'gravity' && $childTableData->server_side) {
            $formContent = json_decode($childTableData->content);
            $form = \GFAPI::get_form($formContent->formId);
            $fieldsData = \WDTGravityIntegration\Plugin::getFieldsData($form, $formContent->fieldIds);
            foreach ($fieldsData as $fieldData) {
                if ($fieldData['label'] == $columnIDName) {
                    if ($fieldData['label'] == 'id') {
                        $columnIDValue = (int)str_replace(array('.', ','), '', $columnIDValue);
                        $searchCriteria['field_filters'][] = ['key' => 'id', 'value' => $columnIDValue];
                    } else {
                        $searchCriteria['field_filters'][] = ['key' => $fieldData['fieldIds'], 'value' => $columnIDValue];
                    }
                }
            }

            if (empty($searchCriteria['field_filters']))
                return [];

            $entries = \GFAPI::get_entries($form['id'], $searchCriteria, null, 100000000000);

            if ($entries == [])
                return [];

            foreach ($entries as $entry) {
                $tableArrayEntry = array();
                foreach ($fieldsData as $fieldData) {
                    $tableArrayEntry[$fieldData['label']] = \WDTGravityIntegration\Plugin::prepareFieldsData($entry, $fieldData);
                }
                $detailsDataArr[] = $tableArrayEntry;
            }
            return $detailsDataArr;

        } else {
            $dataRows = $wpDataTable->getDataRows();
            $filteredData = array_filter($dataRows, function ($item) use ($columnIDValue, $columnIDName) {
                if ($item[$columnIDName] == $columnIDValue) {
                    return true;
                }
                return false;
            });

            if ($filteredData == [])
                return [];

            return array_values($filteredData);
        }
    }

    /**
     * Replace Master-detail placeholders in content(page or post)
     */
    public static function filterTheContent( $content ) {
        if (isset($_REQUEST['wdt_details_data'])){

            $detailsData = json_decode(stripslashes($_REQUEST['wdt_details_data']), true);
            $tableID = (int)$detailsData['wdt_md_id_table'];

            if ($tableID == 0 )
                return $content;

            try {
                $tableData = WDTConfigController::loadTableFromDB($tableID);
            } catch (Exception $e) {
                return $content;
            }

            if (!($tableData))
                return $content;

            $originData = self::getOriginData($tableData);

            foreach ($originData['origHeaders'] as $origHeader) {
                if (isset($detailsData[$origHeader])) {
                    $detailsData[$origHeader] = apply_filters('wpdatatables_md_filter_details_data', $detailsData[$origHeader], $origHeader, $tableID);
                    $content = str_replace("%" . $origHeader . "%", $detailsData[$origHeader], $content);
                }
            }

            if (isset($originData['removeOrigHeaders'])){
                foreach ($originData['removeOrigHeaders'] as $removeOrigHeader) {
                    $content = str_replace("%" . $removeOrigHeader . "%", '', $content);
                }
            }

        } else if (isset($_REQUEST['wdt_md_p_t_id']) && isset($_REQUEST['wdt_md_c_t_id'])) {
            $parentTableID = (int)$_REQUEST['wdt_md_p_t_id'];
            $childTableID = (int)$_REQUEST['wdt_md_c_t_id'];

            if ($parentTableID == 0 || $childTableID == 0)
                return $content;

            try {
                $parentTableData = WDTConfigController::loadTableFromDB($parentTableID);
                $childTableData = WDTConfigController::loadTableFromDB($childTableID);
            } catch (Exception $e) {
                return $content;
            }

            if (!($parentTableData && $childTableData))
                return $content;

            $parentTableDataAdvanced = json_decode($parentTableData->advanced_settings);
            if (isset($parentTableDataAdvanced->masterDetailSender) &&
                $parentTableDataAdvanced->masterDetailSender == 'get')
            {
                $parentTableColumnIDNameDB = $parentTableDataAdvanced->masterDetailSendParentTableColumnIDName;
                $childTableColumnIDNameDB = $parentTableDataAdvanced->masterDetailSendChildTableColumnIDName;

                if (!isset($_REQUEST['wdt_md_p_t_col_name'])){
                    return $content;
                }
                $parentTableColumnIDName = sanitize_text_field($_REQUEST['wdt_md_p_t_col_name']);

                if ($parentTableColumnIDName != $parentTableColumnIDNameDB){
                    return $content;
                }
                if (!isset($_REQUEST['wdt_md_c_t_col_name'])){
                    return $content;
                }
                $childTableColumnIDName = sanitize_text_field($_REQUEST['wdt_md_c_t_col_name']);
                if ($childTableColumnIDName != $childTableColumnIDNameDB){
                    return $content;
                }

                if (!isset($_REQUEST['wdt_md_col_value'])){
                    return $content;
                }
                $columnIDValue = sanitize_text_field($_REQUEST['wdt_md_col_value']);
                $columnIDValue = WDTTools::prepareStringCell($columnIDValue, $childTableData->connection);
                if ($columnIDValue == '')
                    return $content;

                $wpDataTable = new WPDataTable($childTableData->connection);
                $wpDataTable->setWpId($childTableID);
                $columnDataPrepared = $wpDataTable->prepareColumnData($childTableData);
                $columnDataPrepared['data_types'] = $columnDataPrepared['columnTypes'];
                $columnDataPrepared['input_types'] = $columnDataPrepared['columnTypes'];
                try {
                    $wpDataTable->fillFromData($childTableData,$columnDataPrepared);
                } catch (Exception $e) {
                    return $content;
                }

                if (!isset($wpDataTable->getWdtColumnTypes()[$childTableColumnIDName]))
                    return $content;

                $detailsDataArr =
                    self::getDetailsDataArray(
                        $wpDataTable,
                        $childTableData,
                        $childTableColumnIDNameDB,
                        $columnIDValue
                    );
                $originData = self::getOriginData($childTableData);
                if (!empty($detailsDataArr)){
                    $columnObjectPrepared = $wpDataTable->prepareColumns($columnDataPrepared);
                    try {
                        $detailsDataArr = self::prepareOutputDataMasterDetails(
                            [],
                            $wpDataTable,
                            $detailsDataArr,
                            $columnDataPrepared,
                            $columnObjectPrepared
                        );
                    } catch (Exception $e) {
                        return $content;
                    }

                    foreach ($originData['origHeaders'] as $origHeader) {
                        if (isset($detailsDataArr[0][$origHeader])) {
                            $detailsDataArr[0][$origHeader] = apply_filters('wpdatatables_md_filter_details_data', $detailsDataArr[0][$origHeader], $origHeader, $childTableID);
                            $content = str_replace("%" . $origHeader . "%", $detailsDataArr[0][$origHeader], $content);
                        }
                    }
                    if (isset($originData['removeOrigHeaders'])){
                        foreach ($originData['removeOrigHeaders'] as $removeOrigHeader) {
                            $content = str_replace("%" . $removeOrigHeader . "%", '', $content);
                        }
                    }
                } else {
                    foreach ($originData['origHeaders'] as $origHeader) {
                        $content = str_replace("%" . $origHeader . "%", '', $content);
                    }
                }
            }
        } else if (isset($_REQUEST['wdt_md_p_t_id']) && isset($_REQUEST['wdt_md_c_t_id_render'])) {
            if (strpos($content, "%wpdatatables_md_child_table%") === false)
                return str_replace("%wpdatatables_md_child_table%", "", $content);

            $parentTableID = (int)$_REQUEST['wdt_md_p_t_id'];
            $childTableID = (int)$_REQUEST['wdt_md_c_t_id_render'];

            if ($parentTableID == 0 || $childTableID == 0)
                return str_replace("%wpdatatables_md_child_table%", "", $content);

            try {
                $parentTableData = WDTConfigController::loadTableFromDB($parentTableID);
                $childTableData = WDTConfigController::loadTableFromDB($childTableID);
            } catch (Exception $e) {
                return str_replace("%wpdatatables_md_child_table%", "", $content);
            }

            if (!($parentTableData && $childTableData))
                return str_replace("%wpdatatables_md_child_table%", "", $content);

            $parentTableDataAdvanced = json_decode($parentTableData->advanced_settings);
            if ($parentTableDataAdvanced->masterDetailSendTableType != 'childTableRender')
                return str_replace("%wpdatatables_md_child_table%", "", $content);

            if (isset($parentTableDataAdvanced->masterDetailSender) &&
                $parentTableDataAdvanced->masterDetailSender == 'get')
            {
                $parentTableColumnIDNameDB = $parentTableDataAdvanced->masterDetailSendParentTableColumnIDName;
                $childTableColumnIDNameDB = $parentTableDataAdvanced->masterDetailSendChildTableColumnIDName;

                if (!isset($_REQUEST['wdt_md_p_t_col_name'])){
                    return str_replace("%wpdatatables_md_child_table%", "", $content);
                }
                $parentTableColumnIDName = sanitize_text_field($_REQUEST['wdt_md_p_t_col_name']);

                if ($parentTableColumnIDName != $parentTableColumnIDNameDB){
                    return str_replace("%wpdatatables_md_child_table%", "", $content);
                }

                if (!isset($_REQUEST['wdt_md_c_t_col_name'])){
                    return str_replace("%wpdatatables_md_child_table%", "", $content);
                }
                $childTableColumnIDName = sanitize_text_field($_REQUEST['wdt_md_c_t_col_name']);
                if ($childTableColumnIDName != $childTableColumnIDNameDB){
                    return str_replace("%wpdatatables_md_child_table%", "", $content);
                }

                if (!isset($_REQUEST['wdt_md_col_value'])){
                    return str_replace("%wpdatatables_md_child_table%", "", $content);
                }
                $columnIDValue = sanitize_text_field($_REQUEST['wdt_md_col_value']);
                $columnIDValue = WDTTools::prepareStringCell($columnIDValue, $childTableData->connection);

                if ($columnIDValue == '')
                    return str_replace("%wpdatatables_md_child_table%", "", $content);

                $wpDataTable = new WPDataTable($childTableData->connection);
                $wpDataTable->setWpId($childTableID);
                $columnDataPrepared = $wpDataTable->prepareColumnData($childTableData);
                $columnDataPrepared['data_types'] = $columnDataPrepared['columnTypes'];
                $columnDataPrepared['input_types'] = $columnDataPrepared['columnTypes'];

                try {
                    $wpDataTable->fillFromData($childTableData,$columnDataPrepared);
                } catch (Exception $e) {
                    return str_replace("%wpdatatables_md_child_table%", "", $content);
                }

                if (!isset($wpDataTable->getWdtColumnTypes()[$childTableColumnIDName]))
                    return str_replace("%wpdatatables_md_child_table%", "", $content);

                $detailsDataArr =
                    self::getDetailsDataArray(
                        $wpDataTable,
                        $childTableData,
                        $childTableColumnIDNameDB,
                        $columnIDValue
                    );

                $outputTable = esc_html__('No matching records found!', 'wpdatatables');
                if (!empty($detailsDataArr)){
                    $wpDataTable->setDataRows($detailsDataArr);
                    $outputTable = '';

                    if ($childTableData->show_title && $childTableData->title) {
                        $outputTable .= apply_filters('wpdatatables_filter_table_title', (empty($childTableData->title) ? '' : '<h1 class="wpdt-c" id="wdt-table-title-' . $childTableID . '">' . $childTableData->title . '</h1>'), $childTableID);
                    }
                    if ($childTableData->show_table_description && $childTableData->table_description) {
                        $outputTable .= apply_filters('wpdatatables_filter_table_description_text', (empty($childTableData->table_description) ? '' : '<p class="wpdt-c" id="wdt-table-description-' . $childTableID . '">' . $childTableData->table_description . '</p>'), $childTableID);
                    }
                    $outputTable .= $wpDataTable->generateTable($childTableData->connection);

                    if ($outputTable == '')
                        return str_replace("%wpdatatables_md_child_table%", "", $content);

                    $outputTable = apply_filters('wpdatatables_md_filter_child_table_data', $outputTable, $childTableData, $childTableID, $childTableColumnIDName, $columnIDValue);
                    $content = str_replace("%wpdatatables_md_child_table%", $outputTable, $content);
                } else {
                    $outputTable = apply_filters('wpdatatables_md_filter_child_table_data', $outputTable, $childTableData, $childTableID, $childTableColumnIDName, $columnIDValue);
                    $content = str_replace("%wpdatatables_md_child_table%", $outputTable, $content);
                }
            }
        } else if (isset($_REQUEST['wdt_md_p_t_id'])) {
            $tableID = (int)$_REQUEST['wdt_md_p_t_id'];

            if ($tableID == 0 )
                return $content;

            try {
                $tableData = WDTConfigController::loadTableFromDB($tableID);
            } catch (Exception $e) {
                return $content;
            }

            if (!($tableData))
                return $content;

            $tableDataAdvanced = json_decode($tableData->advanced_settings);
            if (isset($tableDataAdvanced->masterDetailSender) &&
                $tableDataAdvanced->masterDetailSender == 'get')
            {
                $parentTableColumnIDNameDB = $tableDataAdvanced->masterDetailSendParentTableColumnIDName;

                if (!isset($_REQUEST['wdt_md_p_t_col_name'])){
                    return $content;
                }
                $parentTableColumnIDName = sanitize_text_field($_REQUEST['wdt_md_p_t_col_name']);
                if ($parentTableColumnIDName != $parentTableColumnIDNameDB){
                    return $content;
                }

                if (!isset($_REQUEST['wdt_md_col_value'])){
                    return $content;
                }
                $columnIDValue = sanitize_text_field($_REQUEST['wdt_md_col_value']);
                $columnIDValue = WDTTools::prepareStringCell($columnIDValue, $tableData->connection);
                if ($columnIDValue == '')
                    return $content;

                $wpDataTable = new WPDataTable($tableData->connection);
                $wpDataTable->setWpId($tableID);
                $columnDataPrepared = $wpDataTable->prepareColumnData($tableData);
                $columnDataPrepared['data_types'] = $columnDataPrepared['columnTypes'];
                $columnDataPrepared['input_types'] = $columnDataPrepared['columnTypes'];

                try {
                    $wpDataTable->fillFromData($tableData,$columnDataPrepared);
                } catch (Exception $e) {
                    return $content;
                }

                if (!isset($wpDataTable->getWdtColumnTypes()[$parentTableColumnIDName]))
                    return $content;

                $detailsDataArr =
                    self::getDetailsDataArray(
                        $wpDataTable,
                        $tableData,
                        $parentTableColumnIDNameDB,
                        $columnIDValue
                    );
                $originData = self::getOriginData($tableData);
                if (!empty($detailsDataArr)){
                    $columnObjectPrepared = $wpDataTable->prepareColumns($columnDataPrepared);

                    try {
                        $detailsDataArr = self::prepareOutputDataMasterDetails(
                            [],
                            $wpDataTable,
                            $detailsDataArr,
                            $columnDataPrepared,
                            $columnObjectPrepared
                        );
                    } catch (Exception $e) {
                        return $content;
                    }

                    foreach ($originData['origHeaders'] as $origHeader) {
                        if (isset($detailsDataArr[0][$origHeader])) {
                            $detailsDataArr[0][$origHeader] = apply_filters('wpdatatables_md_filter_details_data', $detailsDataArr[0][$origHeader], $origHeader, $tableID);
                            $content = str_replace("%" . $origHeader . "%", $detailsDataArr[0][$origHeader], $content);
                        }
                    }
                    if (isset($originData['removeOrigHeaders'])){
                        foreach ($originData['removeOrigHeaders'] as $removeOrigHeader) {
                            $content = str_replace("%" . $removeOrigHeader . "%", '', $content);
                        }
                    }
                } else {
                    foreach ($originData['origHeaders'] as $origHeader) {
                        $content = str_replace("%" . $origHeader . "%", '', $content);
                    }
                }
            }
        } else {
            $currentPostLink = get_permalink(get_the_ID());
            $content = self::removePlaceholdersFromContent($currentPostLink, $content);
        }

        return $content;
    }

    /**
     * Filter SQL query for server side tables(except gravity)
     */
    public static function filterSQLQuery($query, $tableID) {
        if (isset($_REQUEST['wdt_md_p_t_id']) &&
            isset($_REQUEST['wdt_md_c_t_id_render']) &&
            isset($_REQUEST['wdt_md_p_t_col_name']) &&
            isset($_REQUEST['wdt_md_c_t_col_name']) &&
            isset($_REQUEST['wdt_md_col_value']) )
        {
            $parentTableID = (int)$_REQUEST['wdt_md_p_t_id'];
            $childTableID = (int)$_REQUEST['wdt_md_c_t_id_render'];
            if ($tableID != $childTableID)
                return $query;
            $parentTableColumnIDName = sanitize_text_field(urldecode($_REQUEST['wdt_md_p_t_col_name']));
            $childTableColumnIDName = sanitize_text_field(urldecode($_REQUEST['wdt_md_c_t_col_name']));

            try {
                $parentTableData = WDTConfigController::loadTableFromDB($parentTableID);
                $childTableData = WDTConfigController::loadTableFromDB($childTableID);
            } catch (Exception $e) {
                return $query;
            }
            $columnIDValue = sanitize_text_field(urldecode($_REQUEST['wdt_md_col_value']));
            $columnIDValue = WDTTools::prepareStringCell($columnIDValue, $childTableData->connection);

            if ($columnIDValue == '')
                return $query;

            $parentTableDataAdvanced = json_decode($parentTableData->advanced_settings);
            if (isset($parentTableDataAdvanced->masterDetailSender) &&
                $parentTableDataAdvanced->masterDetailSender == 'get')
            {
                $parentTableColumnIDNameDB = $parentTableDataAdvanced->masterDetailSendParentTableColumnIDName;
                $childTableColumnIDNameDB = $parentTableDataAdvanced->masterDetailSendChildTableColumnIDName;

                if ($parentTableColumnIDName != $parentTableColumnIDNameDB){
                    return $query;
                }
                if ($childTableColumnIDName != $childTableColumnIDNameDB){
                    return $query;
                }

                $wpDataTable = new WPDataTable($childTableData->connection);
                $wpDataTable->setWpId($childTableID);

                if (in_array($childTableData->table_type,[ 'manual','sql', 'mysql'])) {
                    $tableContent = WDTTools::applyPlaceholders($query);
                    $queryLimit = '';
                    $vendor = Connection::getVendor($wpDataTable->connection);
                    $isMySql = $vendor === Connection::$MYSQL;
                    $isMSSql = $vendor === Connection::$MSSQL;
                    $isPostgreSql = $vendor === Connection::$POSTGRESQL;
                    $leftSysIdentifier = Connection::getLeftColumnQuote($vendor);
                    $rightSysIdentifier = Connection::getRightColumnQuote($vendor);

                    if ($isMySql || $isPostgreSql){
                        if (strpos($tableContent,' LIMIT ') !== false){
                            $queryLength = strlen($tableContent);
                            $queryLimitPos = strpos($tableContent,'LIMIT');
                            $queryLimit = substr($tableContent, $queryLimitPos, $queryLength);
                            $tableContent = str_replace($queryLimit, '', $tableContent);
                        }
                    }
                    if ($isMSSql){
                        if (strpos($tableContent,' OFFSET ') !== false){
                            $queryLength = strlen($tableContent);
                            $queryLimitPos = strpos($tableContent,' OFFSET ');
                            $queryLimit = substr($tableContent, $queryLimitPos, $queryLength);
                            $tableContent = str_replace($queryLimit, '', $tableContent);
                        }
                    }
                    if (strpos($tableContent,' SQL_CALC_FOUND_ROWS ') !== false){
                        $tableContent = str_replace(' SQL_CALC_FOUND_ROWS ', ' ', $tableContent);
                    }

                    $query = "SELECT * FROM (" . $tableContent . ") 
                                AS wdt_alias 
                                WHERE wdt_alias." . $leftSysIdentifier . $childTableColumnIDNameDB . $rightSysIdentifier;
                    $query .= Connection::isSeparate($childTableData->connection) ? "=" . $columnIDValue . " " : "='" . $columnIDValue . "' ";
                    $query .= $queryLimit;
                    $query = wdtSanitizeQuery($query);
                }
            }
        }
        return $query;
    }

    /**
     * Filter GFAPI data for server side tables
     * @throws WDTException
     */
    public static function filterGFAPIData($json, $tableID, $get) {
        if (isset($_REQUEST['wdt_md_p_t_id']) &&
            isset($_REQUEST['wdt_md_c_t_id_render']) &&
            isset($_REQUEST['wdt_md_p_t_col_name']) &&
            isset($_REQUEST['wdt_md_c_t_col_name']) &&
            isset($_REQUEST['wdt_md_col_value']) )
        {
            $parentTableID = (int)$_REQUEST['wdt_md_p_t_id'];
            $childTableID = (int)$_REQUEST['wdt_md_c_t_id_render'];
            if ($tableID != $childTableID)
                return $json;
            $parentTableColumnIDName = sanitize_text_field(urldecode($_REQUEST['wdt_md_p_t_col_name']));
            $childTableColumnIDName = sanitize_text_field(urldecode($_REQUEST['wdt_md_c_t_col_name']));

            try {
                $parentTableData = WDTConfigController::loadTableFromDB($parentTableID);
                $childTableData = WDTConfigController::loadTableFromDB($childTableID);
            } catch (Exception $e) {
                return $json;
            }
            $columnIDValue = sanitize_text_field(urldecode($_REQUEST['wdt_md_col_value']));
            $columnIDValue = WDTTools::prepareStringCell($columnIDValue, $childTableData->connection);

            if ($columnIDValue == '')
                return $json;

            $parentTableDataAdvanced = json_decode($parentTableData->advanced_settings);
            if (isset($parentTableDataAdvanced->masterDetailSender) &&
                $parentTableDataAdvanced->masterDetailSender == 'get')
            {
                $parentTableColumnIDNameDB = $parentTableDataAdvanced->masterDetailSendParentTableColumnIDName;
                $childTableColumnIDNameDB = $parentTableDataAdvanced->masterDetailSendChildTableColumnIDName;

                if ($parentTableColumnIDName != $parentTableColumnIDNameDB){
                    return $json;
                }
                if ($childTableColumnIDName != $childTableColumnIDNameDB){
                    return $json;
                }
                if ($childTableData->table_type == 'gravity') {
                    $wpDataTable = new WPDataTable($childTableData->connection);
                    $wpDataTable->setWpId($childTableID);

                    $content = json_decode($childTableData->content);
                    $formId = $content->formId;
                    $fieldsIds = $content->fieldIds;

                    $tableArray = \WDTGravityIntegration\Plugin::generateFormArray($formId, $fieldsIds);

                    $countEntriesTotal = \GFAPI::count_entries($formId, array('status' => \WDTGravityIntegration\Plugin::getSearchCriteria()['status']));
                    $countEntriesFiltered = \GFAPI::count_entries($formId, \WDTGravityIntegration\Plugin::getSearchCriteria());

                    $output = array(
                        'draw' => (int)$_POST['draw'],
                        'recordsTotal' => $countEntriesTotal,
                        'recordsFiltered' => $countEntriesFiltered,
                        'data' => array()
                    );

                    $colObjs = $wpDataTable->prepareColumns(\WDTGravityIntegration\Plugin::getWdtParameters());
                    $output['data'] = $wpDataTable->prepareOutputData($tableArray, \WDTGravityIntegration\Plugin::getWdtParameters(), $colObjs);
                    $output['data'] = apply_filters('wpdatatables_custom_prepare_output_data', $output['data'], $wpDataTable, $tableArray, \WDTGravityIntegration\Plugin::getWdtParameters(), $colObjs);
                    return json_encode($output);
                }
            }
        }

        return $json;
    }
    /**
     * Filter Gravity server side search criteria
     */
    public static function filterGFAPISearchCriteria($searchCriteria, $tableID, $params)
    {
        if (isset($_REQUEST['wdt_md_p_t_id']) &&
            isset($_REQUEST['wdt_md_c_t_id_render']) &&
            isset($_REQUEST['wdt_md_p_t_col_name']) &&
            isset($_REQUEST['wdt_md_c_t_col_name']) &&
            isset($_REQUEST['wdt_md_col_value']) )
        {

            $childTableID = (int)$_REQUEST['wdt_md_c_t_id_render'];
            if ($tableID != $childTableID)
                return $searchCriteria;

            $childTableColumnIDName = sanitize_text_field(urldecode($_REQUEST['wdt_md_c_t_col_name']));

            if ($childTableColumnIDName == '')
                return $searchCriteria;

            if (!$params['data_types'][$childTableColumnIDName])
                return $searchCriteria;

            $columnIDValue = sanitize_text_field(urldecode($_REQUEST['wdt_md_col_value']));

            if ($columnIDValue == '')
                return $searchCriteria;

            try {
                $childTableData = WDTConfigController::loadTableFromDB($childTableID);
            } catch (Exception $e) {
                return $searchCriteria;
            }

            $formContent = json_decode($childTableData->content);
            $form = \GFAPI::get_form($formContent->formId);
            $fieldsData = \WDTGravityIntegration\Plugin::getFieldsData($form, $formContent->fieldIds);

            foreach ($fieldsData as $fieldData) {
                if ($fieldData['label'] == $childTableColumnIDName) {
                    if ($fieldData['label'] == 'id') {
                        $columnIDValue = (int)str_replace(array('.', ','), '', $columnIDValue);
                        $searchCriteria['field_filters'][] = ['key' => 'id', 'value' => $columnIDValue];
                    } else {
                        $searchCriteria['field_filters'][] = ['key' => $fieldData['fieldIds'], 'value' => $columnIDValue];
                    }
                }
            }
        }

        return $searchCriteria;
    }

    /**
     * Get origin headers
     *
     * @param $tableData
     *
     * @return array
     */
    private static function getOriginData($tableData)
    {
        $data = [];

        foreach ($tableData->columns as $column){
            if (isset($column->masterDetailColumnOption) &&
                $column->masterDetailColumnOption == 1 ){
                $data['origHeaders'][] = $column->orig_header;
            } else if (isset($column->masterDetailColumnOption) &&
                $column->masterDetailColumnOption == 0){
                $data['removeOrigHeaders'][] = $column->orig_header;
            }
        }
        return $data;
    }

    /**
     * Add Master-Detail activation on wpDataTables settings page
     */
    public static function addMasterDetailActivation()
    {
        ob_start();
        include WDT_MD_ROOT_PATH . 'templates/activation.inc.php';
        $activation = ob_get_contents();
        ob_end_clean();

        echo $activation;
    }

    /**
     * Enqueue Master-Detail add-on files on back-end settings page
     */
    public static function wdtMasterDetailEnqueueBackendSettings()
    {
        if (self::$initialized) {
            wp_enqueue_script(
                'wdt-md-settings',
                WDT_MD_ROOT_URL . 'assets/js/wdt.md.admin.settings.js',
                array(),
                WDT_MD_VERSION,
                true
            );
        }
    }

    /**
     * @param $transient
     *
     * @return mixed
     */
    public static function wdtCheckUpdateMasterDetail($transient)
    {

        if (class_exists('WDTTools')) {
            $pluginSlug = plugin_basename(__FILE__);

            if (empty($transient->checked)) {
                return $transient;
            }

            // Fetch transient for the plugin update data
            $updateData = get_transient('wdt_update_data_master_detail');
            $currentTime = time();

            // If no update data exists or the last check was more than a day ago, fetch new data
            if (!$updateData || ($currentTime - $updateData['last_checked']) > DAY_IN_SECONDS) {
                $purchaseCode = get_option('wdtPurchaseCodeStoreMasterDetail');

                $envatoTokenEmail = '';

                // Get the remote info
                $remoteInformation = WDTTools::getRemoteInformation('wdt-master-detail', $purchaseCode, $envatoTokenEmail);

                if ($remoteInformation) {
                    // Store the new data in the transient
                    $updateData = [
                        'last_checked' => $currentTime,
                        'remote_info'  => $remoteInformation,
                    ];
                    set_transient('wdt_update_data_master_detail', $updateData, DAY_IN_SECONDS);
                }
            }

            // Check if a newer version is available
            if (isset($updateData['remote_info']) && version_compare(WDT_MD_VERSION, $updateData['remote_info']->new_version, '<')) {
                $updateData['remote_info']->package = $updateData['remote_info']->download_link;
                $transient->response[$pluginSlug] = $updateData['remote_info'];
            }
        }

        return $transient;
    }

    /**
     * @param $response
     * @param $action
     * @param $args
     *
     * @return bool|mixed
     */
    public static function wdtCheckInfoMasterDetail($response, $action, $args)
    {

        if (class_exists('WDTTools')) {

            $pluginSlug = plugin_basename(__FILE__);

            if ('plugin_information' !== $action) {
                return $response;
            }

            if (empty($args->slug)) {
                return $response;
            }

            if ($args->slug === $pluginSlug) {
                // Try to get cached update data first
                $updateData = get_transient('wdt_update_data_master_detail');

                if ($updateData && isset($updateData['remote_info'])) {
                    return $updateData['remote_info'];
                }

                // If no cached data, fetch fresh data
                $purchaseCode = get_option('wdtPurchaseCodeStoreMasterDetail');
                $envatoTokenEmail = '';

                return WDTTools::getRemoteInformation('wdt-master-detail', $purchaseCode, $envatoTokenEmail);
            }
        }

        return $response;
    }


    public static function addMessageOnPluginsPageMasterDetail()
    {
        /** @var bool $activated */
        $activated = get_option('wdtActivatedMasterDetail');

        /** @var string $url */
        $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

        /** @var string $redirect */
        $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

        if (!$activated) {
            echo sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate Master-Detail Tables for wpDataTables.', 'wpdatatables'), $redirect);
        }
    }

    public static function addMessageOnUpdateMasterDetail($reply, $package, $updater)
    {
        if (isset($updater->skin->plugin_info['Name']) && $updater->skin->plugin_info['Name'] === get_plugin_data( __FILE__ )['Name']) {
            /** @var string $url */
            $url = get_site_url() . '/wp-admin/admin.php?page=wpdatatables-settings&activeTab=activation';

            /** @var string $redirect */
            $redirect = '<a href="' . $url . '" target="_blank">' . __('settings', 'wpdatatables') . '</a>';

            if (!$package) {
                return new WP_Error(
                    'wpdatatables_master_detail_not_activated',
                    sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate Master-Detail Tables for wpDataTables.', 'wpdatatables'), $redirect)
                );
            }

            return $reply;
        }

        return $reply;
    }
}