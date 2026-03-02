import { useMemo, useState } from "@wordpress/element";
import { DndContext, PointerSensor, useSensor, useSensors, closestCenter } from "@dnd-kit/core";
import { SortableContext, arrayMove, verticalListSortingStrategy } from "@dnd-kit/sortable";
//import { restrictToVerticalAxis } from "@dnd-kit/modifiers";

import { DragHandle, SortableItem, SortableOverlay } from "./components";

const SortableList = ({
	items,
	onChange,
	onDragStart = () => { },
	onDragEnd = () => { },
	children
}) => {
	const [active, setActive] = useState(null);

	const activeItem = useMemo(
		() => items.find((item) => item.id === active?.id),
		[active, items]
	);

	const sensors = useSensors(
		useSensor(PointerSensor)
	);

	const handleDragStart = (event) => {
		const { active } = event;

		onDragStart(active);
		setActive(active);
	};

	const handleDragEnd = (event) => {
		const { active, over } = event;

		if (over && active.id !== over?.id) {
			const activeIndex = items.findIndex(({ id }) => id === active.id);
			const overIndex = items.findIndex(({ id }) => id === over.id);

			onChange(arrayMove(items, activeIndex, overIndex));
		}

		onDragEnd(active);
		setActive(null);
	};

	const handleDragCancel = () => {
		setActive(null);
	};

	return (
		<DndContext
			sensors={sensors}
			collisionDetection={closestCenter}
			onDragStart={handleDragStart}
			onDragEnd={handleDragEnd}
			onDragCancel={handleDragCancel}
		/* modifiers={[restrictToVerticalAxis]} */
		>
			<SortableContext items={items} strategy={verticalListSortingStrategy}>
				<div
					className="jsf-sortable"
					role="application"
				>
					{items.map((item, index) => (
						<React.Fragment key={item.id}>
							{children(item, index)}
						</React.Fragment>
					))}
				</div>
			</SortableContext>
			<SortableOverlay>
				{activeItem ? children(activeItem) : null}
			</SortableOverlay>
		</DndContext>
	);
};

SortableList.Item = SortableItem;
SortableList.DragHandle = DragHandle;

export default SortableList;