import { DragOverlay, defaultDropAnimationSideEffects } from "@dnd-kit/core";

const dropAnimationConfig = {
	sideEffects: defaultDropAnimationSideEffects({
		styles: {
			active: {
				opacity: '0.4'
			}
		}
	})
};

const SortableOverlay = ({ children }) => {
	return (
		<DragOverlay
			dropAnimation={dropAnimationConfig}
		>
			{children}
		</DragOverlay>
	);
};

export default SortableOverlay;