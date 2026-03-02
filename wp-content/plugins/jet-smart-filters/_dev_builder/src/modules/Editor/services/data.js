import { useSelect } from '@wordpress/data';
import { useState, useEffect, useMemo, useRef } from '@wordpress/element';
import { useEditorState, useEditorActions } from "../store";
import { uploadMedia } from '@wordpress/media-utils';
import { createDebounceChecker } from "./helper";

const historyDebounceChecker = createDebounceChecker(1100);
export const useSetBlocksWithHistory = () => {
	const {
		blocks
	} = useEditorState(['blocks']);

	const {
		setBlocks,
		pushHistory,
	} = useEditorActions(['setBlocks', 'pushHistory']);

	return (newBlocks) => {
		if (historyDebounceChecker())
			pushHistory([...blocks]);

		setBlocks(newBlocks);
	};
};

/**
 * Leftsidebar
 */
export const useSwitchLeftSidebar = () => {
	const {
		isLeftSidebarOpen,
		leftSidebarComponent,
	} = useEditorState(['isLeftSidebarOpen', 'leftSidebarComponent']);

	const {
		setLeftSidebarOpen,
		setLeftSidebarClose,
		setLeftSidebarComponent,
	} = useEditorActions(['setLeftSidebarOpen', 'setLeftSidebarClose', 'setLeftSidebarComponent']);

	return (componentName) => {
		if (leftSidebarComponent === componentName) {
			setLeftSidebarComponent(null);
			setLeftSidebarClose();

			return;
		}

		setLeftSidebarComponent(componentName);

		if (!isLeftSidebarOpen)
			setLeftSidebarOpen();
	};
};

/**
 * Editor
 */
export const getCanUserCreateMedia = () =>
	useSelect((select) => {
		const canUserCreateMedia = select('core').canUser('create', 'media');
		return canUserCreateMedia || canUserCreateMedia !== false;
	}, []);

export const getEditorSettings = (_settings = {}) => {
	const canUserCreateMedia = getCanUserCreateMedia();

	return useMemo(() => {
		if (!canUserCreateMedia) {
			return _settings;
		}
		return {
			..._settings,
			mediaUpload({ onError, ...rest }) {
				uploadMedia({
					wpAllowedMimeTypes: _settings.allowedMimeTypes,
					onError: ({ message }) => onError(message),
					...rest,
				});
			},
		};
	}, [canUserCreateMedia, _settings]);
};

export const useBlockFocusEvents = ({ onSelect, onDeselect }) => {
	const clientId = useSelect(
		(select) => select('core/block-editor').getSelectedBlockClientId(),
		[]
	);

	const block = useSelect(
		(select) =>
			clientId ? select('core/block-editor').getBlock(clientId) : null,
		[clientId]
	);

	const lastClientIdRef = useRef(null);

	useEffect(() => {
		const prevClientId = lastClientIdRef.current;
		lastClientIdRef.current = clientId;

		if (prevClientId && !clientId && onDeselect)
			onDeselect(prevClientId);

		if (clientId && clientId !== prevClientId && onSelect && block)
			onSelect(clientId, block);

	}, [clientId, block]);
};

export const useEditorContainerStyles = () => {
	const {
		itemPreviewWidth,
	} = useEditorState(['itemPreviewWidth']);

	const [containerStyles, setContainerStyles] = useState({});

	useEffect(() => {
		if (!itemPreviewWidth) {
			setContainerStyles({});

			return;
		}

		setContainerStyles({
			width: `${itemPreviewWidth}px`,
		});
	}, [itemPreviewWidth]);

	return containerStyles;
};