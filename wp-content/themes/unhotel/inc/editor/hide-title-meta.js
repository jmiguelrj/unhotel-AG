/**
 * Add option to hide page title in Gutenberg
 */
(function(wp) {
    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { CheckboxControl, TextControl } = wp.components;
    const { ColorPalette } = wp.blockEditor;
    const { useSelect, useDispatch } = wp.data;
    const { createElement: el } = wp.element;

    const HideTitleOption = () => {
        const postType = useSelect((select) => 
            select('core/editor').getCurrentPostType()
        );
        
        const meta = useSelect((select) =>
            select('core/editor').getEditedPostAttribute('meta') || {}
        );
        
        const { editPost } = useDispatch('core/editor');
        
        const hideTitle = meta.unhotel_hide_title || false;
        const customMarginTop = meta.unhotel_custom_margin_top || '';
        const customPadding = meta.unhotel_custom_padding || '';
        const customPageClass = meta.unhotel_custom_page_class || '';
        // Get the actual stored value, or default for new pages
        const storedBgColor = meta.unhotel_page_bg_color;
        const pageBgColor = (storedBgColor === undefined || storedBgColor === null) ? '#f7f8f9' : storedBgColor;
        
        if (postType !== 'page') {
            return null;
        }

        return el(
            PluginDocumentSettingPanel,
            {
                name: 'unhotel-page-styles',
                title: 'Page Styles',
                className: 'unhotel-page-styles-panel',
            },
            el(CheckboxControl, {
                label: 'Hide Page Title',
                help: 'Hide the page title on the frontend',
                checked: hideTitle,
                onChange: (value) => {
                    editPost({ meta: { unhotel_hide_title: value } });
                },
            }),
            // Only show spacing options when title is NOT hidden
            !hideTitle && el('hr', { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }),
            !hideTitle && el('p', { style: { fontSize: '11px', color: '#757575', marginTop: '0', marginBottom: '12px' } }, 
                'Customize spacing (defaults when title visible: margin: 0, padding: 40px):'
            ),
            !hideTitle && el(TextControl, {
                label: 'Margin',
                help: 'Outer margin spacing (e.g., "0", "20px", "2rem")',
                value: customMarginTop,
                onChange: (value) => {
                    editPost({ meta: { unhotel_custom_margin_top: value } });
                },
                placeholder: '0'
            }),
            !hideTitle && el(TextControl, {
                label: 'Padding',
                help: 'Inner padding spacing (e.g., "40px", "20px", "50px 40px 20px 40px")',
                value: customPadding,
                onChange: (value) => {
                    editPost({ meta: { unhotel_custom_padding: value } });
                },
                placeholder: '40px'
            }),
            el('hr', { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }),
            el(TextControl, {
                label: 'Custom Page Class',
                help: 'Add a custom CSS class to the body tag for this page',
                value: customPageClass,
                onChange: (value) => {
                    editPost({ meta: { unhotel_custom_page_class: value } });
                },
                placeholder: 'my-custom-class'
            }),
            el('hr', { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }),
            el('div', { style: { marginTop: '16px' } },
                el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: '600' } }, 'Content Area Background'),
                el('p', { style: { fontSize: '11px', color: '#757575', marginTop: '0', marginBottom: '8px' } }, 
                    'Background color for the main content area (default: #f7f8f9)'
                ),
                el(ColorPalette, {
                    value: pageBgColor || undefined,
                    onChange: (color) => {
                        editPost({ meta: { unhotel_page_bg_color: color || '' } });
                    },
                    clearable: true 
                })
            )
        );
    };

    registerPlugin('unhotel-page-styles', {
        render: HideTitleOption,
        icon: 'admin-appearance',
    });
})(window.wp);
