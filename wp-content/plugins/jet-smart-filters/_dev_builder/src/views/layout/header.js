import store from 'store';
import Navigation from './navigation';
import SaveAndBackToListingButton from './save-and-back-to-listing-button';

const Header = () => {
	const { pageTitle } = store.router.useSelect();

	return (
		<div className="jsf-listings-header">
			<div className="jsf-listings-header__left">
				<h1 className="jsf-listings-header__title">{pageTitle}</h1>
			</div>
			<div className="jsf-listings-header__right">
				<SaveAndBackToListingButton />
				<Navigation />
			</div>
		</div>
	);
};

export default Header;
