import { Button, Flex } from '@wordpress/components';
import { Select } from 'modules/UI';
import { useListingData } from "services/data";
import { useNavigate } from "services/navigate";
import request from "services/request";

const CardSettings = () => {
	// Data
	const {
		// selectors
		listingId: id,
		listingName: name,
		listingQuery: query,
		listingSettings: settings,
		listingCards: cards,
		listingCard: card,
		// actions
		setListingCard: updateCard,
		setIsListingSaving: setIsSaving,
	} = useListingData();

	const hasCard = () => {
		return !!card;
	};

	// Navigation
	const {
		goToNewItem,
		goToEditItem
	} = useNavigate();

	// Actions
	const goToItemAndSaveList = (type = 'new') => {
		const data = {
			ID: id || '',
			name: name,
			query: { ...query },
			settings: { ...settings },
			item_id: card,
		};

		if (id) {
			request.saveListing(data);

			goToItem(type);
		} else {
			setIsSaving(true);

			request.saveListing(data).then(response => {
				setIsSaving(false);

				if (!response?.data?.listing_id)
					return;

				goToItem(type, response.data.listing_id);
			});
		}
	};

	const goToItem = (type = 'new', listingId = id) => {
		switch (type) {
			case 'new':
				goToNewItem({ relatedListingId: listingId });

				break;

			case 'edit':
				goToEditItem(card, { relatedListingId: listingId });

				break;
		}
	};

	return (
		<div className="jsf-listings-edit__content">
			<h2>Item</h2>
			<p>Assign item to the listing.</p>
			<Select
				isSearchable={true}
				value={card}
				options={cards}
				onChange={(newValue) => updateCard(newValue)}
			/>
			<Flex
				align="center"
				justify="flex-start"
				gap="4"
			>
				{hasCard() && <Button
					variant="secondary"
					onClick={() => {
						goToItemAndSaveList('edit');
					}}
				>
					Edit current item
				</Button>}
				<Button
					variant="link"
					onClick={() => {
						goToItemAndSaveList('new');
					}}
				>
					Create new item
				</Button>
			</Flex>
		</div>
	);
};

export default CardSettings;