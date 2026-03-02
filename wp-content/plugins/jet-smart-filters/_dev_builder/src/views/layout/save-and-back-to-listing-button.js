import { Button } from '@wordpress/components';
import { useEditorState } from "modules/Editor/store";
import { useLocationState, useNavigate } from "services/navigate";
import { useItemData } from "services/data";
import request from "services/request";

const SaveAndBackToListingButton = () => {
	const { getLocationStateProp } = useLocationState();
	const relatedListingId = getLocationStateProp('relatedListingId');

	const {
		itemId,
		setIsItemSaving,
	} = useItemData();

	const {
		name: itemName,
		blocks: itemBlocks,
		settings: itemSettings,
		styles: itemStyles
	} = useEditorState(['name', 'blocks', 'settings', 'styles']);

	// Navigation
	const {
		goToEditListing
	} = useNavigate();

	// Actions
	const saveAndBackToListing = () => {
		if (!relatedListingId)
			return;

		setIsItemSaving(true);

		const data = {
			ID: itemId,
			name: itemName,
			content: itemBlocks,
			settings: itemSettings,
			styles: itemStyles
		};

		const payloadData = itemId
			? null
			: {
				'related_listing_id': relatedListingId
			};

		request.saveListingItem(data, payloadData).then(response => {
			setIsItemSaving(false);

			if (!response?.data?.item_id)
				return;

			goToEditListing(relatedListingId, { listingTabName: 'item' });
		});
	};

	if (!relatedListingId)
		return;

	return (
		<Button
			className="jsf-back-to-listing-btn"
			variant="link"
			onClick={() => saveAndBackToListing()}
		>
			&lt;- Save and Back to Listing
		</Button>
	);
};

export default SaveAndBackToListingButton;
