import store from "store";
import request from "services/request";
import helper from "services/helper";

// === Localized data ===
const localizedData = window?.JSFBuilderData?.data || {};
const postTypeList = localizedData?.post_types || {};
const postTypeOptions = helper.convertToOptions(postTypeList);

const postsOrderByList = localizedData?.posts_order_by || {};
const postsOrderByOptions = helper.convertToOptions(postsOrderByList);

const postStatiList = localizedData?.post_stati || {};
const postStatiOptions = helper.convertToOptions(postStatiList);

const taxonomiesList = localizedData?.taxonomies || {};
const taxonomiesOptions = helper.convertToOptions(taxonomiesList);

const groupedTaxonomiesList = localizedData?.grouped_taxonomies || {};
const groupedTaxonomiesOptions = helper.convertNestedOptions(groupedTaxonomiesList);

const taxonomyTermFieldList = localizedData?.term_fields || {};
const taxonomyTermFieldOptions = helper.convertToOptions(taxonomyTermFieldList);

const termCompareOperatorsList = localizedData?.term_compare_operators || {};
const termCompareOperatorOptions = helper.convertToOptions(termCompareOperatorsList);

const metaCompareOperatorsList = localizedData?.meta_compare_operators || {};
const metaCompareOperatorOptions = helper.convertToOptions(metaCompareOperatorsList);

const metaTypeList = localizedData?.meta_type || {};
const metaTypeOptions = helper.convertToOptions(metaTypeList);

const fieldSourcesList = localizedData?.field_sources || {};
const fieldSourcesOptions = helper.convertToOptions(fieldSourcesList);

const objectFieldsList = localizedData?.object_fields || [];
const objectFieldsOptions = helper.convertNestedOptions(objectFieldsList);

const filterCallbacksList = localizedData?.filter_сallbacks || [];
const filterCallbacksOptions = helper.convertToOptions(filterCallbacksList);

const linkSourcesList = localizedData?.link_sources || [];
const linkSourcesOptions = helper.convertNestedOptions(linkSourcesList);

const mediaSourcesList = localizedData?.media_sources || [];
const mediaSourcesOptions = helper.convertNestedOptions(mediaSourcesList);

const imageSizesList = localizedData?.image_sizes || [];
const imageSizesOptions = helper.convertToOptions(imageSizesList);

const labelTypesList = localizedData?.label_types || [];
const labelTypesOptions = helper.convertToOptions(labelTypesList);

const labelAriaTypesList = localizedData?.label_aria_types || [];
const labelAriaTypesOptions = helper.convertToOptions(labelAriaTypesList);

const relAttributeTypesList = localizedData?.rel_attribute_types || [];
const relAttributeTypesOptions = helper.convertToOptions(relAttributeTypesList);

const termsOrderByList = localizedData?.term_order_by_fields || [];
const termsOrderByOptions = helper.convertToOptions(termsOrderByList);

const termsOrderList = localizedData?.term_order_fields || [];
const termsOrderOptions = helper.convertToOptions(termsOrderList);

const blocksCategories = localizedData?.blocks_categories || [];
const allowedBlock = localizedData?.allowed_block || [];
const imagePlaceholderUrl = localizedData?.image_placeholder_url || '';

export const useLocalizedData = () => {
	return {
		localizedData,

		postTypeList,
		postTypeOptions,

		postsOrderByList,
		postsOrderByOptions,

		postStatiList,
		postStatiOptions,

		taxonomiesList,
		taxonomiesOptions,

		groupedTaxonomiesList,
		groupedTaxonomiesOptions,

		taxonomyTermFieldList,
		taxonomyTermFieldOptions,

		termCompareOperatorsList,
		termCompareOperatorOptions,

		metaCompareOperatorsList,
		metaCompareOperatorOptions,

		metaTypeList,
		metaTypeOptions,

		fieldSourcesList,
		fieldSourcesOptions,

		objectFieldsList,
		objectFieldsOptions,

		filterCallbacksList,
		filterCallbacksOptions,

		linkSourcesList,
		linkSourcesOptions,

		mediaSourcesList,
		mediaSourcesOptions,

		imageSizesList,
		imageSizesOptions,

		labelTypesList,
		labelTypesOptions,

		labelAriaTypesList,
		labelAriaTypesOptions,

		relAttributeTypesList,
		relAttributeTypesOptions,

		termsOrderByList,
		termsOrderByOptions,

		termsOrderList,
		termsOrderOptions,

		blocksCategories,
		allowedBlock,
		imagePlaceholderUrl
	};
};

// === Page data ===
export const usePageData = () => {
	// store
	const { pagesMap, pageData, pageTitle } = store.router.useSelect();
	const { setPagesMap, setPageData, setPageTitle } = store.router.useDispatch();

	return {
		// selectors
		pagesMap,
		pageData,
		pageTitle,
		// actions
		setPagesMap,
		setPageData,
		setPageTitle
	};
};

// === Listings ===
export const useListingsData = () => {
	// store
	const { listingsData, listingsItemsCount, listingsColumns, listingsView, listingsDefaultLayouts } = store.listings.useSelect();
	const { setListingsData, setListingsItemsCount, setListingsColumns, setListingsView, setListingsDefaultLayouts, clearListingsData } = store.listings.useDispatch();


	return {
		// selectors
		listingsData,
		listingsItemsCount,
		listingsColumns,
		listingsView,
		listingsDefaultLayouts,
		// actions
		setListingsData,
		setListingsItemsCount,
		setListingsColumns,
		setListingsView,
		setListingsDefaultLayouts,
		clearListingsData
	};
};

// === Listing item ===
export const useListingData = () => {
	const { listingId, listingName, listingQuery, listingQueryProp, listingSettings, listingSetting, listingCards, listingCard, isListingLoading, isListingSaving } = store.listing.useSelect();
	const { setListingId, setListingName, setListingQuery, setListingQueryProp, setListingSettings, setListingSetting, setListingCards, setListingCard, clearListingSettings, setIsListingLoading, setIsListingSaving } = store.listing.useDispatch();

	return {
		// selectors
		listingId,
		listingName,
		listingQuery,
		listingQueryProp,
		listingSettings,
		listingSetting,
		listingCards,
		listingCard,
		isListingLoading,
		isListingSaving,
		// actions
		setListingId,
		setListingName,
		setListingQuery,
		setListingQueryProp,
		setListingSettings,
		setListingSetting,
		setListingCards,
		setListingCard,
		setIsListingLoading,
		setIsListingSaving,
		clearListingSettings
	};
};

// === Items List ===
export const useItemsLitsData = () => {
	const { itemsListData, itemsListCount, itemsListColumns, itemsListView, itemsListDefaultLayouts } = store.itemsList.useSelect();
	const { setItemsListData, setItemsListCount, setItemsListColumns, setItemsListView, setItemsListDefaultLayouts, clearItemsListData } = store.itemsList.useDispatch();

	return {
		// selectors
		itemsListData,
		itemsListCount,
		itemsListColumns,
		itemsListView,
		itemsListDefaultLayouts,
		// actions
		setItemsListData,
		setItemsListCount,
		setItemsListColumns,
		setItemsListView,
		setItemsListDefaultLayouts,
		clearItemsListData
	};
};

// === Item ===
export const useItemData = () => {
	const { itemId, itemData, editorSettings, isItemLoading, isItemSaving } = store.item.useSelect();
	const { setItemId, setItemData, setEditorSettings, setIsItemLoading, setIsItemSaving, clearItemData } = store.item.useDispatch();

	return {
		// selectors
		itemId,
		itemData,
		editorSettings,
		isItemLoading,
		isItemSaving,
		// actions
		setItemId,
		setItemData,
		setEditorSettings,
		setIsItemLoading,
		setIsItemSaving,
		clearItemData
	};
};

// === Async Options ===
export const useAsyncOptions = () => {
	const loadCards = (inputValue, callback) => {
		if (inputValue.length < 1) {
			callback([]);

			return;
		}

		request.getCards({
			search: inputValue
		}).then(response => {
			callback(Object.values(response.data));
		});
	};

	const loadUserOptions = (inputValue, callback) => {
		if (inputValue.length < 1) {
			callback([]);

			return;
		}

		request.getUsers({
			search: inputValue
		}).then(response => {
			callback(helper.convertToOptions(response.data));
		});
	};

	const loadPostOptions = (inputValue, callback) => {
		if (inputValue.length < 1) {
			callback([]);

			return;
		}

		request.getPostsList({
			search: inputValue
		}).then(response => {
			if (!helper.isObject(response.data)) {
				callback([]);
			}

			const groupedOptions = [];

			for (const key in response.data) {
				const group = response.data[key];

				groupedOptions.push({
					label: group.label,
					options: helper.convertToOptions(group.posts)
				});
			}

			callback(groupedOptions);
		});
	};

	const loadTermsOptions = (inputValue, callback) => {
		if (inputValue.length < 1) {
			callback([]);

			return;
		}

		request.getTermsList({
			search: inputValue
		}).then(response => {
			if (!helper.isObject(response.data)) {
				callback([]);
			}

			const groupedOptions = [];

			for (const key in response.data) {
				const group = response.data[key];

				groupedOptions.push({
					label: group.label,
					options: helper.convertToOptions(group.terms)
				});
			}

			callback(groupedOptions);
		});
	};

	return {
		loadCards,
		loadUserOptions,
		loadPostOptions,
		loadTermsOptions
	};
};

export default {
	useLocalizedData,
	usePageData,
	useListingsData,
	useListingData,
	useItemsLitsData,
	useItemData,
	useAsyncOptions
};