import { useEffect } from '@wordpress/element';
import { useParams, useLocation } from "react-router-dom";
import { usePageData } from "services/data";

const RouteComponent = ({ children }) => {
	const {
		// selectors
		pagesMap,
		// actions
		setPageData,
		setPageTitle
	} = usePageData();

	// page data
	const location = useLocation();
	const params = useParams();
	const pageSlug = location.pathname.replace(/^\/?([^\/]*)?.*$/, (_, slug) => slug || '');

	const currentPageData = {
		...pagesMap[pageSlug],
		params,
		slug: pageSlug || 'main'
	};
	delete currentPageData.component;

	useEffect(() => {
		// scroll to top
		window.scrollTo(0, 0);

		// set page data
		setPageData(currentPageData || {});
		setPageTitle(currentPageData.title || '');

		// pages
		/* switch (currentPageData.slug) {
			case 'main':
				console.log('Main Page');

				break;

			case 'new-listing':
				console.log('New Page');

				break;

			case 'edit-listing':
				console.log('Edit Page');

			case 'items':
				console.log('Items Page');

				break;

			case 'new-item':
				console.log('New Item Page');

				break;

			case 'edit-item':
				console.log('Edit Item Page');

				break;
		} */
	}, [location.pathname]);

	return children;
};

export default RouteComponent;