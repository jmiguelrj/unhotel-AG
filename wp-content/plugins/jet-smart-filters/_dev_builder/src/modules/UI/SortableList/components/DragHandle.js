import { useContext } from "@wordpress/element";
import { SortableItemContext } from "./SortableItem";
import { DragIcon } from 'modules/Icons';

const DragHandle = () => {
	const {
		attributes,
		listeners,
		ref
	} = useContext(SortableItemContext);

	return (
		<button className="jsf-sortable__drag-handle" {...attributes} {...listeners} ref={ref}>
			<DragIcon />
		</button>
	);
};

export default DragHandle;