(function(blocks, element, blockEditor, components) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var InnerBlocks = blockEditor.InnerBlocks;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var RangeControl = components.RangeControl;
    var ColorPalette = blockEditor.ColorPalette;
    var SelectControl = components.SelectControl;

    registerBlockType('unhotel/section', {
        title: 'Section',
        icon: 'layout',
        category: 'unhotel',
        attributes: {
            fullWidth: {
                type: 'boolean',
                default: false
            },
            minHeight: {
                type: 'number',
                default: 0
            },
            backgroundColor: {
                type: 'string',
                default: ''
            },
            contentWidth: {
                type: 'string',
                default: 'container' // 'container' or 'full'
            },
            contentWidthColor: {
                type: 'string',
                default: '#ffffff'
            }
        },

        edit: function(props) {
            var attrs = props.attributes;
            var setAttrs = props.setAttributes;

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Section Settings' },
                        el(ToggleControl, {
                            label: 'Full Width',
                            checked: attrs.fullWidth,
                            onChange: function(val) { setAttrs({ fullWidth: val }); }
                        }),
                        el(RangeControl, {
                            label: 'Min Height (px)',
                            value: attrs.minHeight,
                            onChange: function(val) { setAttrs({ minHeight: val }); },
                            min: 0,
                            max: 1000
                        }),
                        el('div', { style: { marginTop: '16px' } },
                            el('label', {}, 'Background Color'),
                            el(ColorPalette, {
                                value: attrs.backgroundColor,
                                onChange: function(color) { setAttrs({ backgroundColor: color || '' }); }
                            })
                        ),
                        el(SelectControl, {
                            label: 'Content Width',
                            value: attrs.contentWidth,
                            options: [
                                { label: 'Container (centered)', value: 'container' },
                                { label: 'Full Width', value: 'full' }
                            ],
                            onChange: function(val) { setAttrs({ contentWidth: val }); }
                        }),
                        el('div', { style: { marginTop: '16px' } },
                            el('label', {}, 'Content Width Background Color'),
                            el(ColorPalette, {
                                value: attrs.contentWidthColor,
                                onChange: function(color) { setAttrs({ contentWidthColor: color || '' }); },
                                clearable: true
                            })
                        )
                    )
                ),

                el('div', {
                    className: 'unhotel-section-editor' + (attrs.fullWidth ? ' fullwidth' : ''),
                    style: {
                        minHeight: attrs.minHeight ? attrs.minHeight + 'px' : 'auto',
                        border: '2px dashed #ccc',
                        padding: '20px',
                        backgroundColor: attrs.backgroundColor || 'transparent'
                    }
                },
                    el('div', {
                        className: attrs.contentWidth === 'container' ? 'section-inner-container' : 'section-inner-full',
                        style: Object.assign(
                            {},
                            attrs.contentWidth === 'container' ? { maxWidth: '1200px', margin: '0 auto', padding: '20px' } : { padding: '20px' },
                            attrs.contentWidthColor ? { backgroundColor: attrs.contentWidthColor } : {}
                        )
                    },
                        el(InnerBlocks, {
                            templateLock: false,
                            renderAppender: InnerBlocks.ButtonBlockAppender
                        })
                    )
                )
            ];
        },

        save: function(props) {
            var attrs = props.attributes;

            // Build style only with values that are actually set
            var style = {};
            if (attrs.minHeight) {
                style.minHeight = attrs.minHeight + 'px';
            }
            if (attrs.backgroundColor) {
                style.backgroundColor = attrs.backgroundColor;
            }

            // Build div props - only add style if it has content
            var divProps = {
                className: 'unhotel-section' + (attrs.fullWidth ? ' fullwidth' : '')
            };
            if (Object.keys(style).length > 0) {
                divProps.style = style;
            }

            return el('div', divProps,
                el('div', {
                    className: attrs.contentWidth === 'container' ? 'section-inner-container' : 'section-inner-full',
                    style: attrs.contentWidthColor ? { backgroundColor: attrs.contentWidthColor } : {}
                },
                    el(InnerBlocks.Content)
                )
            );
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components
);
