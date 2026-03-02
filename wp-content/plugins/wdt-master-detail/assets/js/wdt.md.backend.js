(function ($) {
    $(function () {

        /**
         * Extend wpdatatable_config object with new properties and methods
         */
        $.extend(wpdatatable_config, {
            masterDetail: 0,
            masterDetailLogic: '',
            masterDetailRender: '',
            masterDetailSender: '',
            masterDetailSendTableType: '',
            masterDetailSendChildTableID: '',
            masterDetailSendChildTableColumnIDName: '',
            masterDetailSendParentTableColumnIDName: '',
            masterDetailRenderPage: '',
            masterDetailRenderPost: '',
            masterDetailPopupTitle: '',
            masterDetailLinkTargetAttribute: 0,
            setMasterDetail: function (masterDetail) {
                let state = false;
                let masterColumn;
                wpdatatable_config.masterDetail = masterDetail;
                $('#wdt-md-toggle-master-detail').prop('checked', masterDetail);
                if (masterDetail == 1) {
                    jQuery('.wdt-md-column-block').removeClass('hidden');
                    jQuery('.wdt-md-click-event-logic-block').animateFadeIn();
                    jQuery('.wdt-md-render-data-in-block').animateFadeIn();
                    jQuery('.wdt-md-popup-title-block').animateFadeIn();
                    jQuery('.wdt-md-click-event-logic-block').show();
                    jQuery('.wdt-md-render-data-in-block').show();
                    jQuery('.wdt-md-popup-title-block').show();
                    jQuery('#wdt-md-click-event-logic').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-md-render-data-in').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-select-all-column-master-detail').show();
                } else {
                    jQuery('.wdt-md-click-event-logic-block').hide();
                    jQuery('.wdt-md-render-data-in-block').hide();
                    jQuery('.wdt-md-send-data-over-block').hide();
                    jQuery('.wdt-md-send-child-table-column-id-block').hide();
                    jQuery('.wdt-md-send-parent-table-column-id-block').hide();
                    jQuery('.wdt-md-send-child-table-id-block').hide();
                    jQuery('.wdt-md-popup-title-block').hide();
                    jQuery('.wdt-md-render-post-block').hide();
                    jQuery('.wdt-md-render-page-block').hide();
                    jQuery('.wdt-md-toggle-link-target-attribute-block').hide();
                    jQuery('.wdt-md-column-block').addClass('hidden');
                    jQuery('#wdt-select-all-column-master-detail').hide();
                    wpdatatable_config.setMasterDetailPopupTitle('');
                    wpdatatable_config.setMasterDetailLogic('row');
                    wpdatatable_config.setMasterDetailRender('popup');
                    wpdatatable_config.setMasterDetailSender('post');
                    wpdatatable_config.setMasterDetailSendParentTableColumnIDName('');
                    wpdatatable_config.setMasterDetailSendChildTableColumnIDName('');
                    wpdatatable_config.setMasterDetailSendTableType('existingTable');
                    wpdatatable_config.setMasterDetailSendChildTableID('');
                    wpdatatable_config.setMasterDetailLinkTargetAttribute(0);

                    for (let column of wpdatatable_config.columns) {
                        if (column.orig_header === 'masterdetail') {
                            state = true;
                            masterColumn = column;
                        }
                    }
                    if (state) {
                        //fix column positions after deleting masterdetail column
                        for (var i = masterColumn.pos + 1; i <= wpdatatable_config.columns.length - 1; i++) {
                            wpdatatable_config.columns[i].pos = --wpdatatable_config.columns[i].pos;
                        }
                        //remove masterdetaisl object from columns_by_headers
                        wpdatatable_config.columns_by_headers = _.omit(
                            wpdatatable_config.columns_by_headers, masterColumn.orig_header);

                        //remove masterdetail column from columns
                        wpdatatable_config.columns = _.reject(
                            wpdatatable_config.columns,
                            function (el) {
                                return el.orig_header == masterColumn.orig_header;
                            });
                    }

                }
            },
            setMasterDetailLogic: function (masterDetailLogic) {
                wpdatatable_config.masterDetailLogic = masterDetailLogic;
                let state = false;
                let masterColumn;
                for (let column of wpdatatable_config.columns) {
                    if (column.orig_header === 'masterdetail') {
                        state = true;
                        masterColumn = column;
                    }
                }
                if (wpdatatable_config.currentOpenColumn == null && wpdatatable_config.masterDetailLogic === 'row') {

                    if (state) {
                        //fix column positions after deleting masterdetail column
                        for (var i = masterColumn.pos + 1; i <= wpdatatable_config.columns.length - 1; i++) {
                            wpdatatable_config.columns[i].pos = --wpdatatable_config.columns[i].pos;
                        }
                        //remove masterdetaisl object from columns_by_headers
                        wpdatatable_config.columns_by_headers = _.omit(
                            wpdatatable_config.columns_by_headers, masterColumn.orig_header);

                        //remove masterdetaisl column from columns
                        wpdatatable_config.columns = _.reject(
                            wpdatatable_config.columns,
                            function (el) {
                                return el.orig_header == masterColumn.orig_header;
                            });
                    }

                } else if (wpdatatable_config.currentOpenColumn == null && wpdatatable_config.masterDetailLogic === 'button') {

                    if (!state) {
                        //Adding a new Master-detail column
                        wpdatatable_config.addColumn(
                            new WDTColumn(
                                {
                                    type: 'masterdetail',
                                    orig_header: 'masterdetail',
                                    display_header: 'Details',
                                    pos: wpdatatable_config.columns.length,
                                    details: 'masterdetail',
                                    parent_table: wpdatatable_config
                                }
                            )
                        );
                    }
                }
                $('#wdt-md-click-event-logic')
                    .val( masterDetailLogic )
                    .selectpicker('refresh');
            },
            setMasterDetailRender: function (masterDetailRender) {
                wpdatatable_config.masterDetailRender = masterDetailRender;
                $('#wdt-md-render-data-in').selectpicker('val', masterDetailRender);
                if ( wpdatatable_config.masterDetailRender == 'wdtNewPage'){
                    jQuery('.wdt-md-render-post-block').hide();
                    jQuery('.wdt-md-popup-title-block').hide();
                    jQuery('.wdt-md-render-page-block').animateFadeIn();
                    jQuery('.wdt-md-send-data-over-block').animateFadeIn();
                    jQuery('.wdt-md-toggle-link-target-attribute-block').animateFadeIn();
                    jQuery('#wdt-md-send-data-over').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-md-render-page').selectpicker('refresh').trigger('change');
                    if ( wpdatatable_config.masterDetailSender == 'get'){
                        if (wpdatatable_config.masterDetailSendTableType != 'existingTable'){
                            jQuery('.wdt-md-send-table-type-block').animateFadeIn();
                            jQuery('#wdt-md-send-table-type').selectpicker('refresh').trigger('change');
                            jQuery('.wdt-md-send-child-table-id-block').animateFadeIn();
                            jQuery('#wdt-md-send-child-table-id').selectpicker('refresh').trigger('change');
                            jQuery('.wdt-md-send-child-table-column-id-block').animateFadeIn();
                            jQuery('#wdt-md-send-child-table-column-id').selectpicker('refresh').trigger('change');
                            jQuery('.wdt-md-send-parent-table-column-id-block').animateFadeIn();
                            jQuery('#wdt-md-send-parent-table-column-id').selectpicker('refresh').trigger('change');
                        } else {
                            jQuery('.wdt-md-send-child-table-id-block').hide();
                            jQuery('.wdt-md-send-child-table-column-id-block').hide();
                        }
                    } else {
                        jQuery('.wdt-md-send-table-type-block').hide();
                        jQuery('.wdt-md-send-child-table-id-block').hide();
                        jQuery('.wdt-md-send-child-table-column-id-block').hide();
                        jQuery('.wdt-md-send-parent-table-column-id-block').hide();
                    }
                }else if ( wpdatatable_config.masterDetailRender == 'wdtNewPost'){
                    jQuery('.wdt-md-render-page-block').hide();
                    jQuery('.wdt-md-popup-title-block').hide();
                    jQuery('.wdt-md-render-post-block').animateFadeIn();
                    jQuery('.wdt-md-send-data-over-block').animateFadeIn();
                    jQuery('.wdt-md-toggle-link-target-attribute-block').animateFadeIn();
                    jQuery('#wdt-md-send-data-over').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-md-render-post').selectpicker('refresh').trigger('change');
                    if ( wpdatatable_config.masterDetailSender == 'get'){
                        if (wpdatatable_config.masterDetailSendTableType != 'existingTable'){
                            jQuery('.wdt-md-send-table-type-block').animateFadeIn();
                            jQuery('#wdt-md-send-table-type').selectpicker('refresh').trigger('change');
                            jQuery('.wdt-md-send-child-table-id-block').animateFadeIn();
                            jQuery('#wdt-md-send-child-table-id').selectpicker('refresh').trigger('change');
                            jQuery('.wdt-md-send-child-table-column-id-block').animateFadeIn();
                            jQuery('#wdt-md-send-child-table-column-id').selectpicker('refresh').trigger('change');
                            jQuery('.wdt-md-send-parent-table-column-id-block').animateFadeIn();
                            jQuery('#wdt-md-send-parent-table-column-id').selectpicker('refresh').trigger('change');
                        } else {
                            jQuery('.wdt-md-send-child-table-id-block').hide();
                            jQuery('.wdt-md-send-child-table-column-id-block').hide();
                        }
                    } else {
                        jQuery('.wdt-md-send-table-type-block').hide();
                        jQuery('.wdt-md-send-child-table-id-block').hide();
                        jQuery('.wdt-md-send-child-table-column-id-block').hide();
                        jQuery('.wdt-md-send-parent-table-column-id-block').hide();
                    }
                } else if ( wpdatatable_config.masterDetailRender == 'popup' && wpdatatable_config.masterDetail){
                    jQuery('.wdt-md-render-post-block').hide();
                    jQuery('.wdt-md-render-page-block').hide();
                    jQuery('.wdt-md-send-data-over-block').hide();
                    jQuery('.wdt-md-send-child-table-column-id-block').hide();
                    jQuery('.wdt-md-send-table-type-block').hide();
                    jQuery('.wdt-md-send-child-table-id-block').hide();
                    jQuery('.wdt-md-toggle-link-target-attribute-block').hide();
                    jQuery('.wdt-md-popup-title-block').animateFadeIn();
                    wpdatatable_config.setMasterDetailLinkTargetAttribute(0);
                    wpdatatable_config.setMasterDetailSender('post');
                    wpdatatable_config.setMasterDetailSendChildTableColumnIDName('');
                    wpdatatable_config.setMasterDetailSendParentTableColumnIDName('');
                    wpdatatable_config.setMasterDetailSendTableType('existingTable');
                    wpdatatable_config.setMasterDetailSendChildTableID('');
                }
            },
            setMasterDetailSender: function (masterDetailSender) {
                wpdatatable_config.masterDetailSender = masterDetailSender;
                $('#wdt-md-send-data-over').selectpicker('val', masterDetailSender);
                if ( wpdatatable_config.masterDetailSender == 'post'){
                    jQuery('.wdt-md-send-child-table-id-block').hide();
                    jQuery('.wdt-md-send-table-type-block').hide();
                    jQuery('.wdt-md-send-parent-table-column-id-block').hide();
                    jQuery('.wdt-md-send-child-table-column-id-block').hide();
                    wpdatatable_config.setMasterDetailSendChildTableColumnIDName('');
                    wpdatatable_config.setMasterDetailSendParentTableColumnIDName('');
                    wpdatatable_config.setMasterDetailSendTableType('existingTable');
                    wpdatatable_config.setMasterDetailSendChildTableID('');
                    jQuery('#wdt-md-send-child-table-column-id').selectpicker('refresh').trigger('change');
                } else if ( wpdatatable_config.masterDetailSender == 'get'){
                    jQuery('.wdt-md-send-table-type-block').animateFadeIn();
                    jQuery('.wdt-md-send-child-table-column-id-block').animateFadeIn();
                    jQuery('.wdt-md-send-parent-table-column-id-block').animateFadeIn();
                    jQuery('#wdt-md-send-table-type').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-md-send-child-table-column-id').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-md-send-parent-table-column-id').selectpicker('refresh').trigger('change');
                    if (jQuery('#wdt-md-send-parent-table-column-id').selectpicker('val') == '')
                        wpdatatable_config.getTableColumns(wpdatatable_config.id, 'parentTable')
                }
            },
            setMasterDetailSendTableType: function (masterDetailSendTableType) {
                wpdatatable_config.masterDetailSendTableType = masterDetailSendTableType;
                $('#wdt-md-send-table-type').selectpicker('val', masterDetailSendTableType);
                if( wpdatatable_config.masterDetailSendTableType == 'existingTable'){
                    jQuery('.wdt-md-send-child-table-id-block').hide();
                    jQuery('.wdt-md-send-child-table-column-id-block').hide();
                } else if( wpdatatable_config.masterDetailSendTableType == 'childTable' ||
                    wpdatatable_config.masterDetailSendTableType == 'childTableRender')
                {
                    if (jQuery('.wdt-md-send-child-table-column-id-block option').length == 1) {
                        for (var i in wpdatatable_config.columns) {
                            var $selecter = jQuery('#wdt-md-send-child-table-column-id');

                            jQuery('<option value="' +  wpdatatable_config.columns[i].orig_header + '">' +  wpdatatable_config.columns[i].orig_header + '</option>')
                                .appendTo($selecter);
                        }
                    }
                    jQuery('.wdt-md-send-child-table-id-block').animateFadeIn();
                    jQuery('.wdt-md-send-child-table-column-id-block').animateFadeIn();
                    jQuery('#wdt-md-send-child-table-id').selectpicker('refresh').trigger('change');
                    jQuery('#wdt-md-send-child-table-column-id').selectpicker('refresh').trigger('change');
                }

            },
            setMasterDetailSendChildTableID: function (masterDetailSendChildTableID) {
                wpdatatable_config.masterDetailSendChildTableID = masterDetailSendChildTableID;
                $('#wdt-md-send-child-table-id').selectpicker('val', masterDetailSendChildTableID);
                if (masterDetailSendChildTableID != '')
                    wpdatatable_config.getTableColumns(masterDetailSendChildTableID, 'childTable')

            },
            setMasterDetailSendChildTableColumnIDName: function (masterDetailSendChildTableColumnIDName) {
                if (masterDetailSendChildTableColumnIDName == null) masterDetailSendChildTableColumnIDName = '';
                wpdatatable_config.masterDetailSendChildTableColumnIDName = masterDetailSendChildTableColumnIDName;
                $('#wdt-md-send-child-table-column-id').selectpicker('val', masterDetailSendChildTableColumnIDName);
            },
            setMasterDetailSendParentTableColumnIDName: function (masterDetailSendParentTableColumnIDName) {
                if (masterDetailSendParentTableColumnIDName == null) masterDetailSendParentTableColumnIDName = '';
                wpdatatable_config.masterDetailSendParentTableColumnIDName = masterDetailSendParentTableColumnIDName;
                $('#wdt-md-send-parent-table-column-id').selectpicker('val', masterDetailSendParentTableColumnIDName);
            },
            setMasterDetailRenderPage: function (masterDetailRenderPage) {
                wpdatatable_config.masterDetailRenderPage = masterDetailRenderPage;
                $('#wdt-md-render-page').selectpicker('val', masterDetailRenderPage);
            },
            setMasterDetailRenderPost: function (masterDetailRenderPost) {
                wpdatatable_config.masterDetailRenderPost = masterDetailRenderPost;
                $('#wdt-md-render-post').selectpicker('val', masterDetailRenderPost);
            },
            setMasterDetailPopupTitle: function (masterDetailPopupTitle) {
                wpdatatable_config.masterDetailPopupTitle = masterDetailPopupTitle;
                jQuery( '#wdt-md-popup-title' ).val( masterDetailPopupTitle );
            },
            setMasterDetailLinkTargetAttribute: function (masterDetailLinkTargetAttribute) {
                wpdatatable_config.masterDetailLinkTargetAttribute = masterDetailLinkTargetAttribute;
                jQuery( '#wdt-md-toggle-link-target-attribute' ).prop('checked', masterDetailLinkTargetAttribute);
            },
            getTableColumns: function (tableId, tableType) {
                if (tableId) {
                    jQuery.ajax({
                        url: ajaxurl,
                        method: 'post',
                        dataType: 'json',
                        data: {
                            wdtNonce: jQuery('#wdtNonce').val(),
                            action: 'wpdatatables_get_columns_data_by_table_id',
                            table_id: tableId
                        },
                        success: function (columns) {
                            var tableTypeValue = tableType === 'childTable' ? 'child' : 'parent'
                            var tableTypeVar = tableType === 'childTable' ?
                                wpdatatable_config.masterDetailSendChildTableColumnIDName : wpdatatable_config.masterDetailSendParentTableColumnIDName
                            if (jQuery('#wdt-md-send-' + tableTypeValue + '-table-column-id option').length > 1) {
                                jQuery('#wdt-md-send-' + tableTypeValue + '-table-column-id').html('<option value="">Pick a column...</option>');
                            }
                            for (var i in columns) {
                                var option_str = '<option value="' + columns[i].orig_header + '">' + columns[i].orig_header + '</option>';
                                jQuery('#wdt-md-send-' + tableTypeValue + '-table-column-id').append(option_str);
                            }
                            $('#wdt-md-send-' + tableTypeValue + '-table-column-id').selectpicker('val', tableTypeVar);
                            $('#wdt-md-send-' + tableTypeValue + '-table-column-id').selectpicker('refresh')

                        }
                    });
                }
            },

        });

        /**
         * Load the table for editing
         */
        if (typeof wpdatatable_init_config !== 'undefined' && wpdatatable_init_config.advanced_settings !== '') {

            var advancedSettings = JSON.parse(wpdatatable_init_config.advanced_settings);

            if (advancedSettings !== null) {

                var masterDetail = advancedSettings.masterDetail;
                var masterDetailLogic = advancedSettings.masterDetailLogic;
                var masterDetailRender = advancedSettings.masterDetailRender;
                var masterDetailSender = advancedSettings.masterDetailSender;
                var masterDetailSendTableType = advancedSettings.masterDetailSendTableType;
                var masterDetailSendChildTableID = advancedSettings.masterDetailSendChildTableID;
                var masterDetailSendChildTableColumnIDName = advancedSettings.masterDetailSendChildTableColumnIDName;
                var masterDetailSendParentTableColumnIDName = advancedSettings.masterDetailSendParentTableColumnIDName;
                var masterDetailRenderPage = advancedSettings.masterDetailRenderPage;
                var masterDetailRenderPost = advancedSettings.masterDetailRenderPost;
                var masterDetailPopupTitle = advancedSettings.masterDetailPopupTitle;
                var masterDetailLinkTargetAttribute = advancedSettings.masterDetailLinkTargetAttribute;

                if (typeof masterDetail !== 'undefined') {
                    wpdatatable_config.setMasterDetail(masterDetail);
                }

                if (typeof masterDetailLogic !== 'undefined') {
                    wpdatatable_config.setMasterDetailLogic(masterDetailLogic);
                }

                if (typeof masterDetailRender !== 'undefined') {
                    wpdatatable_config.setMasterDetailRender(masterDetailRender);
                }

                if (typeof masterDetailSender !== 'undefined') {
                    wpdatatable_config.setMasterDetailSender(masterDetailSender);
                }

                if (typeof masterDetailSendTableType !== 'undefined') {
                    wpdatatable_config.setMasterDetailSendTableType(masterDetailSendTableType);
                }

                if (typeof masterDetailSendChildTableID !== 'undefined') {
                    wpdatatable_config.setMasterDetailSendChildTableID(masterDetailSendChildTableID);
                }

                if (typeof masterDetailSendParentTableColumnIDName !== 'undefined') {
                    wpdatatable_config.setMasterDetailSendParentTableColumnIDName(masterDetailSendParentTableColumnIDName);
                }

                if (typeof masterDetailSendChildTableColumnIDName !== 'undefined') {
                    wpdatatable_config.setMasterDetailSendChildTableColumnIDName(masterDetailSendChildTableColumnIDName);
                }

                if (typeof masterDetailRenderPage !== 'undefined') {
                    wpdatatable_config.setMasterDetailRenderPage(masterDetailRenderPage);
                }

                if (typeof masterDetailRenderPost !== 'undefined') {
                    wpdatatable_config.setMasterDetailRenderPost(masterDetailRenderPost);
                }

                if (typeof masterDetailPopupTitle !== 'undefined') {
                    wpdatatable_config.setMasterDetailPopupTitle(masterDetailPopupTitle);
                }

                if (typeof masterDetailLinkTargetAttribute !== 'undefined') {
                    wpdatatable_config.setMasterDetailLinkTargetAttribute(masterDetailLinkTargetAttribute);
                }

            }

        }

        /**
         * Toggle "Master-detail" option
         */
        $('#wdt-md-toggle-master-detail').change(function () {
            wpdatatable_config.setMasterDetail($(this).is(':checked') ? 1 : 0);
        });

        /**
         * Select "Master-detail" logic
         */
        $('#wdt-md-click-event-logic').change(function () {
            wpdatatable_config.setMasterDetailLogic($(this).val());
        });

        /**
         * Select "Master-detail" sender option
         */
        $('#wdt-md-send-data-over').change(function () {
            wpdatatable_config.setMasterDetailSender($(this).val());
        });

        /**
         * Select "Master-detail" send table data
         */
        $('#wdt-md-send-table-type').change(function () {
            wpdatatable_config.setMasterDetailSendTableType($(this).val());
        });

        /**
         * Select "Master-detail" send child table id
         */
        $('#wdt-md-send-child-table-id').change(function () {
            wpdatatable_config.setMasterDetailSendChildTableID($(this).val());
        });

        /**
         * Select "Master-detail" send parent table column id in url
         */
        $('#wdt-md-send-parent-table-column-id').change(function () {
            wpdatatable_config.setMasterDetailSendParentTableColumnIDName($(this).val());
        });

        /**
         * Select "Master-detail" send child table column id in url
         */
        $('#wdt-md-send-child-table-column-id').change(function () {
            wpdatatable_config.setMasterDetailSendChildTableColumnIDName($(this).val());
        });

        /**
         * Select "Master-detail" render option
         */
        $('#wdt-md-render-data-in').change(function () {
            wpdatatable_config.setMasterDetailRender($(this).val());
        });

        /**
         * Select "Master-detail" render page
         */
        $('#wdt-md-render-page').change(function () {
            wpdatatable_config.setMasterDetailRenderPage($(this).val());
        });

        /**
         * Select "Master-detail" render post
         */
        $('#wdt-md-render-post').change(function () {
            wpdatatable_config.setMasterDetailRenderPost($(this).val());
        });

        /**
         * Set "Master-detail" Popup Title
         */
        $('#wdt-md-popup-title').change(function (e) {
            wpdatatable_config.setMasterDetailPopupTitle($(this).val());
        });

        /**
         * Toggle "Link target attribute" option
         */
        $('#wdt-md-toggle-link-target-attribute').change(function () {
            wpdatatable_config.setMasterDetailLinkTargetAttribute($(this).is(':checked') ? 1 : 0);
        });

    });

})(jQuery);

/**
 * Initialize new property in object
 */
function callbackExtendColumnObject(column,obj) {
    var newOptionName = 'masterDetailColumnOption';
    if (typeof obj.masterDetailColumnOption == 'undefined'){
        obj.setAdditionalParam(newOptionName, column.masterDetailColumnOption);
    } else {
        obj.setAdditionalParam(newOptionName, 1);
    }
}

/**
 * Extend column settings and return it in an object format
 */
function callbackExtendOptionInObjectFormat(allColumnSettings, obj) {
    if (wpdatatable_config.masterDetail == 1){
        allColumnSettings.masterDetailColumnOption = obj.masterDetailColumnOption;
        return allColumnSettings;
    }
}
jQuery(document).on('click', '#wdt-select-all-column-master-detail', function (e) {
    jQuery(this).toggleClass('select-all-columns deselect-all-columns');
});
/**
 * Extend a small block with new column option in the list
 */
function callbackExtendSmallBlock($columnBlock, column) {
    jQuery('#wdt-select-all-column-master-detail').click(function (e) {
        if (jQuery(this).hasClass('deselect-all-columns')) {
            column.masterDetailColumnOption = 1;
            jQuery('i.wdt-toggle-show-details')
                .removeClass('inactive')
        } else {
            column.masterDetailColumnOption = 0;
            jQuery('i.wdt-toggle-show-details')
                .addClass('inactive')
        }
    });
    if( column === column.parent_table.columns[column.parent_table.columns.length-1]){
        jQuery('#wdt-select-all-column-master-detail').removeAttr('checked');
    }

    $columnBlock.find('i.wdt-toggle-show-details').click(function (e) {
        e.preventDefault();
        if (!column.masterDetailColumnOption) {
            column.masterDetailColumnOption = 1;
            jQuery(this)
              .removeClass('inactive')
        } else {
            column.masterDetailColumnOption = 0;
            jQuery(this)
              .addClass('inactive')
        }
    });

    if (!column.masterDetailColumnOption) {
        $columnBlock.find('i.wdt-toggle-show-details')
          .addClass('inactive')
    }
}

/**
 * Fill in the visible inputs with data
 */
function callbackFillAdditinalOptionWithData(obj) {
    jQuery('#wdt-md-column').prop('checked',obj.masterDetailColumnOption).change();
}

/**
 * Hide tabs and options from Master-detail column
 */
function callbackHideColumnOptions(obj) {
    if (obj.type == 'masterdetail') {
        jQuery('li.column-filtering-settings-tab').hide();
        jQuery('li.column-editing-settings-tab').hide();
        jQuery('li.column-sorting-settings-tab').hide();
        jQuery('li.column-conditional-formatting-settings-tab').hide();
        jQuery('#wdt-column-type option[value="masterdetail"]').prop('disabled', '');
        jQuery('#wdt-column-type').prop('disabled', 'disabled').hide();
        jQuery('#column-data-settings .row:first-child').hide();
        jQuery('div.wdt-possible-values-type-block').hide();
        jQuery('div.wdt-possible-values-options-block').hide();
        jQuery('div.wdt-formula-column-block').hide();
        jQuery('div.wdt-skip-thousands-separator-block').hide();
        jQuery('div.wdt-numeric-column-block').hide();
        jQuery('div.wdt-float-column-block').hide();
        jQuery('div.wdt-date-input-format-block').hide();
        jQuery('div.wdt-group-column-block').hide();
        jQuery('div.wdt-link-target-attribute-block').hide();
        if (jQuery('#wdt-link-button-attribute').is(':checked')) {
            jQuery('div.wdt-link-button-label-block').show();
            jQuery('div.wdt-link-button-class-block').show();
        }
        jQuery('div.wdt-link-button-attribute-block').show();
        jQuery('div.wdt-md-column-block').hide();
    } else {
        jQuery('li.column-conditional-formatting-settings-tab').show();
        jQuery('#wdt-column-type option[value="masterdetail"]').prop('disabled', 'disabled');
        if (!(obj.type == 'hidden' || obj.type == 'formula')) {
            jQuery('#wdt-column-type').prop('disabled', '');
        }
        jQuery('#column-data-settings .row:first-child').show();
    }

}

/**
 * Apply changes from UI to the object for new column option
 */
function callbackApplyUIChangesForNewColumnOption(obj) {
    obj.masterDetailColumnOption = jQuery('#wdt-md-column').is(':checked') ? 1 : 0;
}