<!-- Master-Detail settings -->
<div role="tabpanel" class="tab-pane" id="master-detail-settings">
    <!-- .row -->
    <div class="row">
        <!-- Master-detail checkbox-->
        <div class="col-sm-4 m-b-16 wdt-md-toggle-master-detail-block">
            <h4 class="c-title-color m-b-4 m-t-0">
                <?php esc_html_e('Master-detail', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Enable this to turn the master-detail functionality on for this table.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-md-toggle-master-detail" type="checkbox" hidden="hidden">
                <label for="wdt-md-toggle-master-detail"
                       class="ts-label"><?php esc_html_e('Enable master-detail functionality', 'wpdatatables'); ?></label>
            </div>
        </div>
        <!-- /Master-Detail checkbox-->

        <!-- Master-Detail Click Event Logic-->
        <div class="col-sm-4 wdt-md-click-event-logic-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-click-event-logic" class="c-title-color m-b-4">
                            <?php esc_html_e('Open details on:', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('If the “Row click” is selected, users will be able to access details for a row by clicking it. If the “Button click” is selected, a new column will be added to the table, where each row would get a button opening the details for it.', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-click-event-logic">
                            <option value="row"><?php esc_html_e('Row click', 'wpdatatables'); ?></option>
                            <option value="button"><?php esc_html_e('Button click', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Click Event Logic-->

        <!-- Master-Detail Render data in-->
        <div class="col-sm-4 wdt-md-render-data-in-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-render-data-in" class="c-title-color m-b-4">
                            <?php esc_html_e('Show details in:', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('If the “popup” option is selected, the details for the selected row will appear in a popup dialog on the same page. If you choose on of the the “Post” or “Page” options, users will be redirected to the chosen post or page (picked in a separate setting), which will be used as a template to render the details. Please note that you need to create the template post or page and fill it in with placeholders first, so that you could select it here', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-render-data-in">
                            <option value="popup"><?php esc_html_e('Popup', 'wpdatatables'); ?></option>
                            <option value="wdtNewPage"><?php esc_html_e('Page', 'wpdatatables'); ?></option>
                            <option value="wdtNewPost"><?php esc_html_e('Post', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Render data in-->
    </div>
    <!-- /.row -->

    <!-- .row -->
    <div class="row">
        <!-- Master-Detail Render page-->
        <div class="col-sm-4 wdt-md-render-page-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-render-page" class="c-title-color m-b-4">
                            <?php esc_html_e('Template page', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Choose which page will be used to showing the row details', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-render-page">
                            <?php foreach (WDTMasterDetail\Plugin::getAllPages() as $page) { ?>
                                <option value="<?php echo get_permalink($page['ID']); ?>"><?php echo esc_html($page['post_title']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Render page -->

        <!-- Master-Detail Render post-->
        <div class="col-sm-4 wdt-md-render-post-block hidden">

            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-render-post" class="c-title-color m-b-4">
                            <?php esc_html_e('Template post', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Choose which post will be used to showing the row details', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-render-post">
                            <?php foreach (WDTMasterDetail\Plugin::getAllPosts() as $post) { ?>
                                <option value="<?php echo get_permalink($post['ID']); ?>"><?php echo esc_html($post['post_title']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Render post -->

        <!-- Master-Detail Popup Title -->
        <div class="col-sm-4 wdt-md-popup-title-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="row">
                        <div class="col-sm-12">
                            <label for="wdt-md-popup-title" class="c-title-color m-b-4">
                                <?php esc_html_e('Popup Title', 'wpdatatables'); ?>
                                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                   title="<?php esc_attr_e('Enter a title for the popup with row details. If you leave the field blank, the default title is “Row details”', 'wpdatatables'); ?>"></i>
                            </label>
                            <input type="text" name="wdt-md-popup-title" id="wdt-md-popup-title"
                                                                           class="form-control input-sm" placeholder="Enter a title for Popup modal"
                                                                           value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Master-Detail Popup Title -->

        <!-- Master-detail Link Target Attribute-->
        <div class="col-sm-4 m-b-16 wdt-md-toggle-link-target-attribute-block hidden">
            <h4 class="c-title-color m-b-4 m-t-0">
                <?php esc_html_e('Link target attribute', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Set how to open post or page. By default option is turned off and it is opening post/page in new tab.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="toggle-switch" data-ts-color="blue">
                <input id="wdt-md-toggle-link-target-attribute" type="checkbox" hidden="hidden">
                <label for="wdt-md-toggle-link-target-attribute"
                       class="ts-label"><?php esc_html_e('Open page/post in the same tab', 'wpdatatables'); ?></label>
            </div>
        </div>
        <!-- /Master-Detail Link Target Attribute-->

        <!-- Master-Detail Send data over-->
        <div class="col-sm-4 wdt-md-send-data-over-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-send-data-over" class="c-title-color m-b-4">
                            <?php esc_html_e('Send details over:', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('When opting for the "POST" method, the details pertaining to the selected row will be transmitted via the POST method. Conversely, if you opt for the "GET" method, the details data associated with the table ID and the chosen column ID from the selected row will be conveyed through GET parameters, making the information accessible via the URL.', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-send-data-over">
                            <option value="post"><?php esc_html_e('POST', 'wpdatatables'); ?></option>
                            <option value="get"><?php esc_html_e('GET', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Send data over-->
    </div>
    <!-- /.row -->

    <!-- .row -->
    <div class="row">
        <!-- Master-Detail Send table data-->
        <div class="col-sm-4 wdt-md-send-table-type-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-send-table-type" class="c-title-color m-b-4">
                            <?php esc_html_e('Send table data:', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('In this section, you have the flexibility to select the table data you wish to transmit. There are three options available:"Send Parent Table Row Data": This option allows you to transmit detailed information from the rows of the parent/existing table. "Send Child Table Row Data": Choose this option to send row details specifically from the child table."Send Child Table Data": Opt for this option to transmit the entire dataset from the child table, facilitating the rendering of the complete table on the designated post or page.', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-send-table-type">
                            <option value="existingTable"><?php esc_html_e('Send Parent Table Row Data', 'wpdatatables'); ?></option>
                            <option value="childTable"><?php esc_html_e('Send Child Table Row Data', 'wpdatatables'); ?></option>
                            <option value="childTableRender"><?php esc_html_e('Send Child Table Data', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Send table data-->

        <!-- Master-Detail Send parent table column id-->
        <div class="col-sm-4 wdt-md-send-parent-table-column-id-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-send-column-id" class="c-title-color m-b-4">
                            <?php esc_html_e('Parent Table Column Name (recommended with unique values):', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('With this option, you can select a column name from the parent/existing table(this table). The value from this chosen column will be sent to the designated post or page as a GET parameter, making it accessible in the URL.', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-send-parent-table-column-id">
                            <option value=''><?php esc_html_e('Pick a column...', 'wpdatatables'); ?></option>
                            <?php if (isset($_GET['table_id']) && isset($_GET['page']) && $_GET['page'] == 'wpdatatables-constructor'){
                                $tableID = (int)$_GET['table_id'];
                                foreach (WDTConfigController::loadColumnsFromDB($tableID) as $column) { ?>
                                    <option value="<?php echo esc_attr($column->orig_header); ?>"><?php echo esc_html($column->orig_header); ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Send parent table column id-->
    </div>
    <!-- /.row -->

    <!-- .row -->
    <div class="row">

        <!-- Master-Detail Send table ID-->
        <div class="col-sm-4 wdt-md-send-child-table-id-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-send-child-table-id" class="c-title-color m-b-4">
                            <?php esc_html_e('Child Tables:', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('This option becomes available when values such as "Send Child Table Row Data" or "Send Child Table Data" are chosen within the "Send Table Data" option. Here, you will find a list of all created tables (excluding Simple tables). You can browse through this list to select the desired child table for establishing a connection.', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-send-child-table-id" data-live-search="true">
                            <option value=''><?php esc_html_e('Pick a child table...', 'wpdatatables'); ?></option>
                            <?php foreach (WPDataTable::getAllTablesExceptSimple() as $wdt) {?>
                                    <option value="<?php echo esc_attr($wdt['id']); ?>"><?php echo esc_html($wdt['title']); ?>
                                        (id: <?php echo esc_html($wdt['id']); ?>)
                                    </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Send table ID-->

        <!-- Master-Detail Send child table column id-->
        <div class="col-sm-4 wdt-md-send-child-table-column-id-block hidden">
            <div class="form-group">
                <div class="fg-line">
                    <div class="select">
                        <label for="wdt-md-send-child-table-column-id" class="c-title-color m-b-4">
                            <?php esc_html_e('Child Table Column Name:', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('With this option, you will select a column from the child table chosen in the "Child Tables" option. This selected column will then be filtered based on the value specified in the "Parent Table Column Name" option.', 'wpdatatables'); ?>"></i>
                        </label>
                        <select class="form-control selectpicker" id="wdt-md-send-child-table-column-id">
                            <option value=''><?php esc_html_e('Pick a column...', 'wpdatatables'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Master-Detail Send child table column id-->
    </div>
    <!-- /.row -->
</div>
<!-- /Master-Detail settings -->