import { useEffect, useState } from '@wordpress/element';
import { parse } from '@wordpress/blocks';
import { useNavigate } from "services/navigate";
import { useMountStatus } from "services/helper";
import request from "services/request";
import { useItemData, usePageData } from "services/data";
import Editor from 'modules/Editor';

const Card = () => {
	const [clearDataOnUnmount, setClearDataOnUnmount] = useState(true);

	// On mount
	const isMounted = useMountStatus(() => {
		// On unmount
		if (clearDataOnUnmount)
			clearData();
	});

	// Navigation
	const {
		goToEditItem
	} = useNavigate();

	// Data
	const {
		// selectors
		itemId,
		itemData,
		editorSettings,
		// actions
		setItemId: updateId,
		setItemData: updateData,
		setIsItemLoading: setIsLoading,
		setIsItemSaving: setIsSaving,
		clearItemData: clearData
	} = useItemData();

	const {
		pageData
	} = usePageData();

	// Actions
	const saveItem = (newData) => {
		const data = {
			ID: pageData?.params?.item_id || '',
			...newData
		};

		setIsSaving(true);

		request.saveListingItem(data).then(response => {
			setIsSaving(false);

			if (!response?.data?.item_id)
				return;

			if (pageData.slug === 'new-item') {
				setClearDataOnUnmount(false);
				updateId(response.data.item_id);
				goToEditItem(response.data.item_id);
			}
		});
	};

	// Hooks
	useEffect(() => {
		if (!isMounted)
			return;

		setClearDataOnUnmount(true);

		if (pageData?.params?.item_id && pageData.params.item_id != itemId) {
			setIsLoading(true);

			request.getListingItem(pageData.params.item_id).then(response => {
				setIsLoading(false);

				if (!response.data)
					return;

				updateId(response.data.ID);
				delete response.data.ID;

				/**
				 * Important!
				 *
				 * Blocks always serialize before save so we need to parse them
				 * before use in editor.
				 */
				response.data.content = parse( response.data.content || '' );

				updateData(response.data);
			});
		}
	}, [pageData.params]);

	return (
		<Editor
			name={itemData.name}
			blocks={itemData.content}
			settings={editorSettings}
			onSave={saveItem}
		/>
	);
};

export default Card;