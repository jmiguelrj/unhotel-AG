import { createContext, useMemo } from "@wordpress/element";
import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";

export const SortableItemContext = createContext({
	attributes: {},
	listeners: undefined,
	ref() { }
});

const SortableItem = ({ children, id }) => {
	const {
		attributes,
		isDragging,
		listeners,
		setNodeRef,
		setActivatorNodeRef,
		transform,
		transition
	} = useSortable({ id });

	const context = useMemo(
		() => ({
			attributes,
			listeners,
			ref: setActivatorNodeRef
		}),
		[attributes, listeners, setActivatorNodeRef]
	);

	const style = {
		opacity: isDragging ? 0.4 : undefined,
		transform: CSS.Translate.toString(transform),
		transition
	};

	return (
		<SortableItemContext.Provider
			value={context}
		>
			<div
				className="jsf-sortable__item"
				ref={setNodeRef}
				style={style}
			>
				{children}
			</div>
		</SortableItemContext.Provider>
	);
};

export default SortableItem;