import { HashRouter, Routes, Route } from "react-router-dom";
import store from 'store';
import Header from "./header";
import Footer from "./footer";
import RouteComponent from "./route-component";

const MainContainer = () => {
	const { pagesMap } = store.router.useSelect();

	return (
		<HashRouter>
			<div className="jsf-listings-container">
				<Header />
				<Routes>
					{Object.entries(pagesMap).map(([path, { component: Component, args = [] }]) => (
						<Route
							key={path}
							path={`/${path}${args.length ? `/:${args.join("/:")}` : ""}`}
							element={
								<RouteComponent>
									<div className="jsf-listings-body">
										<Component />
									</div>
								</RouteComponent>
							}
						/>
					))}
				</Routes>
				<Footer />
			</div>
		</HashRouter>
	);
};

export default MainContainer;
