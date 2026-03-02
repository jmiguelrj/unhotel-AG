import { useEffect, useMemo } from '@wordpress/element';
import { DataViews } from "@wordpress/dataviews/wp";
import { trash } from "@wordpress/icons";
import { Button } from "@wordpress/components";
import { useNavigate } from "services/navigate";
import { useItemsLitsData } from "services/data";
import { useMountStatus } from "services/helper";
import request from "services/request";

const ItemsList = () => {
	// On mount
	const isMounted = useMountStatus(() => {
		// On unmount
		clearData();
	});

	// Navigation
	const {
		goToNewItem,
		goToEditItem
	} = useNavigate();

	// Data
	const {
		// selectors
		itemsListData: data,
		itemsListCount: itemsCount,
		itemsListColumns: tableColumns,
		itemsListView: tableView,
		itemsListDefaultLayouts: tableDefaultLayouts,
		// actions
		setItemsListData: updateData,
		setItemsListCount: updateItemsCount,
		setItemsListView: updateTableView,
		clearItemsListData: clearData
	} = useItemsLitsData();

	const tableItemActions = [
		{
			label: 'Delete',
			icon: { trash },
			callback: (item) => {
				if (confirm("Are you sure you want to delete this item?"))
					removeItem(item[0].ID);
			}
		},
	];

	// Actions
	const removeItem = (id) => {
		request.remove_listing_item(id, queryArgs).then(response => {
			updateData(response?.data?.items || []);
			updateItemsCount(response?.data?.count || 0);
		});
	};

	// Hooks
	const queryArgs = useMemo(() => {
		const args = {
			search: tableView.search,
			order_by: tableView.sort.field,
			order: tableView.sort.direction,
			per_page: tableView.perPage,
			page: tableView.page
		};

		return args;
	}, [tableView]);

	const paginationInfo = useMemo(() => {
		return {
			totalItems: itemsCount,
			totalPages: Math.ceil(itemsCount / tableView.perPage)
		};
	}, [itemsCount, tableView.perPage]);

	useEffect(() => {
		if (!isMounted)
			return;

		request.getItems(queryArgs).then(response => {
			updateData(response?.data?.items || []);
			updateItemsCount(response?.data?.count || 0);
		});
	}, [
		tableView.search,
		tableView.sort.field,
		tableView.sort.direction,
		tableView.perPage,
		tableView.page
	]);

	return (
		<div className='jsf-items-list'>
			<Button
				className='jsf-items-list__add-new-button'
				variant="primary"
				onClick={() => goToNewItem()}
			>
				Add New Item
			</Button>
			<DataViews
				data={data}
				fields={tableColumns}
				getItemId={(item) => item.ID}
				view={tableView}
				onChangeView={(newView) => updateTableView(newView)}
				defaultLayouts={tableDefaultLayouts}
				tableLayout="fixed"
				paginationInfo={paginationInfo}
				actions={tableItemActions}
				onClickItem={(item) => {
					goToEditItem(item.ID);
				}}
			/>
		</div>
	);
};

export default ItemsList;