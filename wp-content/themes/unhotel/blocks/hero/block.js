(function(blocks, element, blockEditor, components) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var RichText = blockEditor.RichText;
    var MediaUpload = blockEditor.MediaUpload;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var RangeControl = components.RangeControl;
    var Button = components.Button;
    var SelectControl = components.SelectControl;

    registerBlockType('unhotel/hero', {
        title: 'Hero Banner',
        icon: 'cover-image',
        category: 'design',
        attributes: {
            imageUrl: {
                type: 'string',
                default: ''
            },
            title: {
                type: 'string',
                default: 'Welcome'
            },
            subtitle: {
                type: 'string',
                default: 'Subtitle here'
            },
            fullWidth: {
                type: 'boolean',
                default: true
            },
            minHeight: {
                type: 'number',
                default: 500
            },
            overlayOpacity: {
                type: 'number',
                default: 0.5
            },
            textColor: {
                type: 'string',
                default: '#ffffff'
            },
            textPosition: {
                type: 'string',
                default: 'center'
            }
        },

        edit: function(props) {
            var attrs = props.attributes;
            var setAttrs = props.setAttributes;

            // Helper function to get alignment styles based on position
            var getAlignmentStyles = function(position) {
                var positions = {
                    'top-left': { alignItems: 'flex-start', justifyContent: 'flex-start', textAlign: 'left' },
                    'top-center': { alignItems: 'flex-start', justifyContent: 'center', textAlign: 'center' },
                    'top-right': { alignItems: 'flex-start', justifyContent: 'flex-end', textAlign: 'right' },
                    'center-left': { alignItems: 'center', justifyContent: 'flex-start', textAlign: 'left' },
                    'center': { alignItems: 'center', justifyContent: 'center', textAlign: 'center' },
                    'center-right': { alignItems: 'center', justifyContent: 'flex-end', textAlign: 'right' },
                    'bottom-left': { alignItems: 'flex-end', justifyContent: 'flex-start', textAlign: 'left' },
                    'bottom-center': { alignItems: 'flex-end', justifyContent: 'center', textAlign: 'center' },
                    'bottom-right': { alignItems: 'flex-end', justifyContent: 'flex-end', textAlign: 'right' }
                };
                return positions[position] || positions['center'];
            };

            // Ensure textPosition has a valid value, default to 'center' if not set
            var textPosition = attrs.textPosition || 'center';
            var alignment = getAlignmentStyles(textPosition);

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Hero Settings' },
                        el(ToggleControl, {
                            label: 'Full Width',
                            checked: attrs.fullWidth,
                            onChange: function(val) { setAttrs({ fullWidth: val }); }
                        }),
                        el(RangeControl, {
                            label: 'Min Height (px)',
                            value: attrs.minHeight,
                            onChange: function(val) { setAttrs({ minHeight: val }); },
                            min: 200,
                            max: 1000
                        }),
                        el(RangeControl, {
                            label: 'Overlay Opacity',
                            value: attrs.overlayOpacity,
                            onChange: function(val) { setAttrs({ overlayOpacity: val }); },
                            min: 0,
                            max: 1,
                            step: 0.1
                        }),
                        el('div', { style: { marginTop: '16px', marginBottom: '16px' } },
                            el('label', { 
                                style: { 
                                    display: 'block', 
                                    marginBottom: '8px', 
                                    fontWeight: '600',
                                    fontSize: '13px'
                                } 
                            }, 'Text Color'),
                            el('input', {
                                type: 'color',
                                value: attrs.textColor,
                                onChange: function(e) { setAttrs({ textColor: e.target.value }); },
                                style: { 
                                    width: '100%', 
                                    height: '40px', 
                                    cursor: 'pointer',
                                    border: '1px solid #ddd',
                                    borderRadius: '2px'
                                }
                            })
                        ),
                        el(SelectControl, {
                            label: 'Text Position',
                            value: attrs.textPosition,
                            options: [
                                { label: 'Top Left', value: 'top-left' },
                                { label: 'Top Center', value: 'top-center' },
                                { label: 'Top Right', value: 'top-right' },
                                { label: 'Center Left', value: 'center-left' },
                                { label: 'Center', value: 'center' },
                                { label: 'Center Right', value: 'center-right' },
                                { label: 'Bottom Left', value: 'bottom-left' },
                                { label: 'Bottom Center', value: 'bottom-center' },
                                { label: 'Bottom Right', value: 'bottom-right' }
                            ],
                            onChange: function(val) { setAttrs({ textPosition: val }); }
                        })
                    )
                ),

                el('div', {
                    className: 'unhotel-hero-editor' + (attrs.fullWidth ? ' fullwidth' : ''),
                    style: {
                        backgroundImage: attrs.imageUrl ? 'url(' + attrs.imageUrl + ')' : 'none',
                        minHeight: attrs.minHeight + 'px',
                        position: 'relative',
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                        display: 'flex',
                        alignItems: alignment.alignItems,
                        justifyContent: alignment.justifyContent
                    }
                },
                    el('div', {
                        className: 'unhotel-hero-overlay',
                        style: { opacity: attrs.overlayOpacity }
                    }),
                    
                    el('div', { 
                        className: 'unhotel-hero-content',
                        style: { 
                            position: 'relative', 
                            zIndex: 2, 
                            textAlign: alignment.textAlign, 
                            padding: '2rem',
                            color: attrs.textColor
                        }
                    },
                        el(MediaUpload, {
                            onSelect: function(media) {
                                setAttrs({ imageUrl: media.url });
                            },
                            allowedTypes: ['image'],
                            value: attrs.imageUrl,
                            render: function(obj) {
                                return el(Button, {
                                    onClick: obj.open,
                                    className: 'button button-large',
                                    style: { marginBottom: '10px' }
                                }, attrs.imageUrl ? 'Change Image' : 'Select Background');
                            }
                        }),
                        
                        el(RichText, {
                            tagName: 'h1',
                            value: attrs.title,
                            onChange: function(val) { setAttrs({ title: val }); },
                            placeholder: 'Enter title...',
                            style: { color: attrs.textColor, fontSize: '3rem', marginBottom: '1rem' }
                        }),
                        
                        el(RichText, {
                            tagName: 'p',
                            value: attrs.subtitle,
                            onChange: function(val) { setAttrs({ subtitle: val }); },
                            placeholder: 'Enter subtitle...',
                            style: { color: attrs.textColor, fontSize: '1.5rem' }
                        })
                    )
                )
            ];
        },

        save: function(props) {
            var attrs = props.attributes;

            // Helper function to get alignment styles based on position
            var getAlignmentStyles = function(position) {
                var positions = {
                    'top-left': { alignItems: 'flex-start', justifyContent: 'flex-start', textAlign: 'left' },
                    'top-center': { alignItems: 'flex-start', justifyContent: 'center', textAlign: 'center' },
                    'top-right': { alignItems: 'flex-start', justifyContent: 'flex-end', textAlign: 'right' },
                    'center-left': { alignItems: 'center', justifyContent: 'flex-start', textAlign: 'left' },
                    'center': { alignItems: 'center', justifyContent: 'center', textAlign: 'center' },
                    'center-right': { alignItems: 'center', justifyContent: 'flex-end', textAlign: 'right' },
                    'bottom-left': { alignItems: 'flex-end', justifyContent: 'flex-start', textAlign: 'left' },
                    'bottom-center': { alignItems: 'flex-end', justifyContent: 'center', textAlign: 'center' },
                    'bottom-right': { alignItems: 'flex-end', justifyContent: 'flex-end', textAlign: 'right' }
                };
                return positions[position] || positions['center'];
            };

            // Ensure textPosition has a valid value, default to 'center' if not set
            var textPosition = attrs.textPosition || 'center';
            var alignment = getAlignmentStyles(textPosition);

            return el('div', {
                className: 'unhotel-hero' + (attrs.fullWidth ? ' fullwidth' : ''),
                style: {
                    backgroundImage: attrs.imageUrl ? 'url(' + attrs.imageUrl + ')' : 'none',
                    minHeight: attrs.minHeight + 'px',
                    display: 'flex',
                    alignItems: alignment.alignItems,
                    justifyContent: alignment.justifyContent
                }
            },
                el('div', {
                    className: 'unhotel-hero-overlay',
                    style: { opacity: attrs.overlayOpacity }
                }),
                
                el('div', { 
                    className: 'unhotel-hero-content',
                    style: {
                        textAlign: alignment.textAlign,
                        color: attrs.textColor
                    }
                },
                    el(RichText.Content, {
                        tagName: 'h1',
                        value: attrs.title
                    }),
                    el(RichText.Content, {
                        tagName: 'p',
                        value: attrs.subtitle
                    })
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
