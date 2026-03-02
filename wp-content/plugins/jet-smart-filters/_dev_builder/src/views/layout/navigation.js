import { Link } from "react-router-dom";
import store from "store";

const Navigation = () => {
	const { pagesMap } = store.router.useSelect();

	return (
		<nav className="jsf-listings-nav">
			{Object.entries(pagesMap)
				.filter(([_, { is_nav_item }]) => is_nav_item)
				.map(([path, { title, icon: Icon }]) => (
					<Link key={path} to={`/${path}`}>
						{ Icon && <Icon /> }
						{title}
					</Link>
				))
			}
		</nav>
	);
};

export default Navigation;
