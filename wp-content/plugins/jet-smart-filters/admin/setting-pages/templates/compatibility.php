<div
	class="jet-smart-filters-settings-page jet-smart-filters-settings-page__compatibility"
>
	<cx-vui-collapse collapsed="true">
		<h3 class="cx-vui-subtitle" slot="title">
			<?php esc_html_e( 'WooCommerce', 'jet-smart-filters' ); ?>
		</h3>
		<div class="cx-vui-panel" slot="content">
			<cx-vui-switcher
				label="<?php esc_attr_e( 'Hide Out-of-Stock Variations in Filter Results', 'jet-smart-filters' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				v-model="settings.wc_hide_out_of_stock_variations"
			></cx-vui-switcher>
			<cx-vui-input
				label="<?php esc_attr_e( 'Pagination Container CSS Selector', 'jet-smart-filters' ); ?>"
				description="<?php esc_attr_e(
					'CSS selector of top level element in Woo pagination. Required for Default WooCommerce Archive provider to correctly refresh pagination.',
					'jet-smart-filters'
				); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				size="fullwidth"
				v-model="settings.wc_archive_pager_cont_selector"
			></cx-vui-input>
			<cx-vui-input
				label="<?php esc_attr_e( 'Pagination Item CSS Selector', 'jet-smart-filters' ); ?>"
				description="<?php esc_attr_e(
					'CSS selector of individual page link in Woo pagination. Required for Default WooCommerce Archive provider to correctly handle pagination click.',
					'jet-smart-filters'
				); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				size="fullwidth"
				v-model="settings.wc_archive_pager_item_selector"
			></cx-vui-input>
		</div>
	</cx-vui-collapse>
</div>