import { getCategories, setCategories } from '@wordpress/blocks';
import { useLocalizedData } from "services/data";

const {
	blocksCategories
} = useLocalizedData();

const existing = getCategories();
const slugs = existing.map((c) => c.slug);

const merged = [
	...blocksCategories.filter((cat) => !slugs.includes(cat.slug)),
	...existing,
];

setCategories(merged);