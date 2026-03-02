<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php /** @var WPDataTable $wpDataTable */ ?>
<div id="<?php echo $wpDataTable->getId() ?>_md_dialog" style="display: none">

    <!-- .wdt-details-dialog-fields-block -->
    <div class="row wdt-details-dialog-fields-block">
        <?php
        /** @var WDTColumn $dataColumn */
        foreach( $wpDataTable->getColumnsByHeaders() as $dataColumn_key=>$dataColumn ) {
            ?>
            <!-- .form-group -->
            <div
                <?php
                if (($dataColumn_key == 'wdt_ID') || ($dataColumn_key == 'masterdetail') ||
                (isset($dataColumn->masterDetailColumnOption) && $dataColumn->masterDetailColumnOption !== 1) ||
                (($wpDataTable->getUserIdColumn() != '') && ($dataColumn_key == $wpDataTable->getUserIdColumn()))) { ?>
                    style="display: none"
                    <?php if ($dataColumn_key == $wpDataTable->getIdColumnKey()) { ?>
                        class="idRow"
                    <?php } ?>
                <?php } else { ?>
                    class="form-group col-xs-12">
                <?php } ?>

                <p  class="col-sm-3 <?php echo $wpDataTable->getId() ?>_<?php echo $dataColumn_key ?>">
                    <?php echo $dataColumn->getTitle(); ?>:<?php if ($dataColumn->isNotNull()) { ?> * <?php } ?>
                </p>
                <!-- .col-sm-9 -->
                <div class="col-sm-9">
                    <div class="fg-line">
                        <div id="<?php echo $wpDataTable->getId() ?>_<?php echo $dataColumn_key ?>_detials"
                             data-key="<?php echo $dataColumn_key ?>"
                             data-column_type="<?php echo $dataColumn->getDataType(); ?>"
                             data-column_header="<?php echo $dataColumn->getTitle(); ?>"
                             data-input_type="<?php echo $dataColumn->getInputType(); ?>"
                             style="<?php echo $dataColumn->getCSSStyle(); ?>"
                             class="detailColumn column-<?php echo strtolower(str_replace(' ', '-',$dataColumn->getOriginalHeader())) . " " . $dataColumn->getCSSClasses() ?>"
                        ></div>
                    </div>
                </div>
                <!-- .col-sm-9 -->
            </div>
            <!--/ .form-group -->
        <?php } ?>
        <?php if (isset($wpDataTable->masterDetail) && $wpDataTable->masterDetail &&
            isset($wpDataTable->masterDetailLogic) && $wpDataTable->masterDetailLogic =='row' &&
            isset($wpDataTable->masterDetailRender) &&
            ($wpDataTable->masterDetailRender =='wdtNewPage' || $wpDataTable->masterDetailRender =='wdtNewPost') &&
            (isset($wpDataTable->masterDetailRenderPage) || isset($wpDataTable->masterDetailRenderPost))) {
            $targetAttribute = '_blank';
            $formName = '';
            $masterDetailSender = $wpDataTable->masterDetailSender ?? 'post';
            $masterDetailSendParentTableColumnIDName = $wpDataTable->masterDetailSendParentTableColumnIDName ?? '';
            $masterDetailSendChildTableColumnIDName = $wpDataTable->masterDetailSendChildTableColumnIDName ?? '';
            $masterDetailSendTableType = $wpDataTable->masterDetailSendTableType ?? 'existingTable';
            if ($masterDetailSendTableType == 'childTable')
                $formName = 'wdt_md_c_t_id';
            if ($masterDetailSendTableType == 'childTableRender')
                $formName = 'wdt_md_c_t_id_render';
            $masterDetailSendChildTableID = $wpDataTable->masterDetailSendChildTableID ?? 0;
            if (isset($wpDataTable->masterDetailLinkTargetAttribute))
                $targetAttribute = $wpDataTable->masterDetailLinkTargetAttribute ? '_self' : '_blank';
            $renderAction = $wpDataTable->masterDetailRender == 'wdtNewPage' ? $wpDataTable->masterDetailRenderPage : $wpDataTable->masterDetailRenderPost;
            if ($masterDetailSender == 'post'){?>
                <form class='wdt_md_form' method='<?php echo $masterDetailSender; ?>' target='<?php echo $targetAttribute; ?>' action='<?php echo $renderAction; ?>'>
                    <input class='wdt_md_hidden_data' type='hidden' name='wdt_details_data' value=''>
                    <input class='master_detail_column_btn' type='submit' value='Submit'>
                </form>
            <?php } else if ($masterDetailSender == 'get'){ ?>
                <?php  if ($masterDetailSendTableType == 'existingTable'){ ?>
                <form class='wdt_md_form' method='<?php echo $masterDetailSender; ?>' target='<?php echo $targetAttribute; ?>' action='<?php echo $renderAction; ?>'>
                    <input class='wdt_md_hidden_parent_table_id' type='hidden' name='wdt_md_p_t_id' value=''>
                    <input class='wdt_md_hidden_parent_table_column_id_name' type='hidden' name='wdt_md_p_t_col_name' value=''>
                    <input class='wdt_md_hidden_column_id_value' type='hidden' name='wdt_md_col_value' value=''>
                    <input class='master_detail_column_btn' type='submit' value='Submit'>
                </form>
                <?php } else if ($masterDetailSendTableType == 'childTable' || $masterDetailSendTableType == 'childTableRender'){ ?>
                    <form class='wdt_md_form' method='<?php echo $masterDetailSender; ?>' target='<?php echo $targetAttribute; ?>' action='<?php echo $renderAction; ?>'>
                        <input class='wdt_md_hidden_parent_table_id' type='hidden' name='wdt_md_p_t_id' value=''>
                        <input class='wdt_md_hidden_parent_table_column_id_name' type='hidden' name='wdt_md_p_t_col_name' value=''>
                        <input class='wdt_md_hidden_child_table_id' type='hidden' name='<?php echo $formName ?>' value=''>
                        <input class='wdt_md_hidden_child_table_column_id_name' type='hidden' name='wdt_md_c_t_col_name' value=''>
                        <input class='wdt_md_hidden_column_id_value' type='hidden' name='wdt_md_col_value' value=''>
                        <input class='master_detail_column_btn' type='submit' value='Submit'>
                    </form>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>
    <!--/ .wdt-details-dialog-fields-block -->

</div>
