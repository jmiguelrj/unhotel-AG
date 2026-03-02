<?php
/**
 * Review Types List template
 */
?><div id="jet-reviews-types-page" class="jet-reviews-admin-page jet-reviews-admin-page--review-types">
	<div class="jet-reviews-admin-page__header">
		<h1 class="wp-heading-inline"><?php _e( 'Review Types', 'jet-reviews' ); ?></h1>
        <span
                class="page-title-action"
                @click="addNewTypeHandle"
        ><?php
			_e( 'Add New Type', 'jet-reviews' );
			?></span>
	</div>
	<hr class="wp-header-end">
	<div class="jet-reviews-admin-page__content">
		<cx-vui-list-table
			:is-empty="0 === itemsList.length"
			empty-message="<?php _e( 'No Types found', 'jet-reviews' ); ?>"
			class="jet-reviews-admin-page__table jet-reviews-admin-page__table--review-types"
			:class="{ 'loading-status': progressStatus }"
		>
			<cx-vui-list-table-heading
				:slots="[ 'name', 'source', 'sourceType', 'fields', 'actions' ]"
				slot="heading"
			>
				<div slot="name"><?php _e( 'Name', 'jet-reviews' ); ?></div>
				<div slot="source"><?php _e( 'Source', 'jet-reviews' ); ?></div>
				<div slot="sourceType"><?php _e( 'Source Type', 'jet-reviews' ); ?></div>
				<div slot="fields"><?php _e( 'Fields', 'jet-reviews' ); ?></div>
				<div slot="actions"><?php _e( 'Actions', 'jet-reviews' ); ?></div>
			</cx-vui-list-table-heading>
			<cx-vui-list-table-item
				:slots="[ 'name', 'source', 'sourceType', 'fields', 'actions' ]"
				slot="items"
				v-for="item in itemsList"
				:key="item.id"
			>
				<div slot="name">
                    <a :href="item.editLink" target="_self">{{ item.name }}</a>
                </div>
				<div slot="source">{{ generateSourceLabel( item.source ) }}</div>
				<div slot="sourceType">{{ generateSourceTypeLabel( item.source, item.sourceType ) }}</div>
				<div slot="fields">{{ generateFieldsList( item.settings.fields )}}</div>
				<div slot="actions" v-if="'default' !== item.slug">
                    <span
                        class="edit-action"
                    >
                        <a :href="item.editLink" target="_self"><?php _e( 'Edit', 'jet-reviews' ); ?></a>
                    </span>
                    <span>|</span>
                    <!--<span
                        class="edit-action"
                        @click='copyTypeHandle( item.slug )'
                    >
                        <?php /*_e( 'Copy', 'jet-reviews' ); */?>
                    </span>
                    <span>|</span>-->
					<span
						class="delete-action"
						@click='openDeleteTypePopup( item.slug )'
					><?php
						_e( 'Delete', 'jet-reviews' );
					?></span>
				</div>
			</cx-vui-list-table-item>
		</cx-vui-list-table>
	</div>

	<transition name="popup">
		<cx-vui-popup
			class="jet-reviews-admin-page__popup"
			v-model="deletePopupVisible"
			body-width="350px"
			:ok-label="'<?php _e( 'Delete', 'jet-reviews' ) ?>'"
			:cancel-label="'<?php _e( 'Cancel', 'jet-reviews' ) ?>'"
			@on-ok="deleteTypeHandle"
		>
			<div class="cx-vui-subtitle" slot="title"><?php _e( 'Please confirm type deletion', 'jet-reviews' ); ?></div>
		</cx-vui-popup>
	</transition>
</div>
