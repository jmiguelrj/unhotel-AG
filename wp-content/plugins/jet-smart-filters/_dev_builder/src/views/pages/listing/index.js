import { useEffect, useState } from '@wordpress/element';
import { useLocationState, useNavigate } from "services/navigate";
import { useListingData, usePageData } from "services/data";
import { useMountStatus } from "services/helper";
import request from "services/request";
import { Input, LoadingButton } from 'modules/UI';
import { TabPanel } from '@wordpress/components';

// Components
import Components from "./components";
const settingsComponents = Components.settings;

// Tabs map
const tabs = [
	{ name: 'query', title: 'Query', className: 'query-settings', component: settingsComponents.Query },
	{ name: 'layout', title: 'Layout', className: 'layout-settings', component: settingsComponents.Layout },
	{ name: 'item', title: 'Item', className: 'item-settings', component: settingsComponents.Card }
];

const Listing = () => {
	const [clearDataOnUnmount, setClearDataOnUnmount] = useState(true);
	const [isNameInputAutoFocus, setIsNameInputAutoFocus] = useState(false);

	// On mount
	const isMounted = useMountStatus(() => {
		// On unmount
		if (clearDataOnUnmount)
			clearSettings();
	});

	// Navigation
	const {
		goToEditListing
	} = useNavigate();

	const {
		getLocationStateProp
	} = useLocationState();

	const initialTabName = getLocationStateProp('listingTabName');

	// Data
	const {
		// selectors
		listingId: id,
		listingName: name,
		listingQuery: query,
		listingSettings: settings,
		listingCard: card,
		isListingSaving: isSaving,
		// actions
		setListingId: updateId,
		setListingName: updateName,
		setListingQuery: updateQuery,
		setListingSettings: updateSettings,
		setListingCards: updateCards,
		setListingCard: updateCard,
		setIsListingSaving: setIsSaving,
		clearListingSettings: clearSettings
	} = useListingData();

	const {
		pageData
	} = usePageData();

	const saveButtonLabel = 'Save Listing';

	// Actions
	const saveListing = () => {
		const data = {
			ID: pageData?.params?.listing_id || '',
			name: name,
			query: { ...query },
			settings: { ...settings },
			item_id: card,
		};

		setIsSaving(true);

		request.saveListing(data).then(response => {
			setIsSaving(false);

			if (!response?.data?.listing_id)
				return;

			if (pageData.slug === 'new-listing') {
				setClearDataOnUnmount(false);
				updateId(response.data.listing_id);
				goToEditListing(response.data.listing_id);
			}
		});
	};

	// Hooks
	useEffect(() => {
		if (!isMounted)
			return;

		setClearDataOnUnmount(true);

		if (pageData?.params?.listing_id && pageData.params.listing_id != id) {
			request.getListing(pageData.params.listing_id).then(response => {
				if (!response.data)
					return;

				updateId(response.data.ID);
				updateName(response.data.name);
				updateQuery(response.data.query);
				updateSettings(response.data.settings);
				updateCard(response.data.item_id);
			});
		}

		request.getCards().then(response => {
			if (!response.data)
				return;

			updateCards(response.data);
		});

		if (pageData.slug === 'new-listing')
			setIsNameInputAutoFocus(true);

	}, [pageData.params]);

	return (
		<div className="jsf-listings-edit">
			<div className="jsf-listings-toolbar">
				<div className="jsf-listings-toolbar__start">
					<Input
						className="jsf-listings-toolbar__input"
						value={name}
						placeholder={'Listing Name...'}
						onChange={(newName) => updateName(newName)}
						isFocus={isNameInputAutoFocus}
					/>
				</div>
				<div className="jsf-listings-toolbar__end">
					<LoadingButton
						label={saveButtonLabel}
						variant="primary"
						isLoading={isSaving}
						onClick={() => saveListing()}
					/>
				</div>
			</div>
			<div className="jsf-listings-edit__body">
				<TabPanel
					className="jsf-listings-edit__tabs"
					tabs={tabs}
					initialTabName={initialTabName}
				>
					{(tab) => {
						// Render the component
						const TabComponent = tab.component;

						return (
							<TabComponent />
						);
					}}
				</TabPanel>
			</div>
			{/* <pre>{JSON.stringify(query, null, 2)}</pre> */}
		</div>
	);
};

export default Listing;