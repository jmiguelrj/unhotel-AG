(function(blocks, element, blockEditor, components) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var SelectControl = components.SelectControl;
    var RangeControl = components.RangeControl;
    var ColorPalette = blockEditor.ColorPalette;
    var MediaUpload = blockEditor.MediaUpload;
    var Button = components.Button;
    var TextControl = components.TextControl;
    var TextareaControl = components.TextareaControl;

registerBlockType('unhotel/carousel', {
    title: 'Carousel',
    icon: 'images-alt2',
    category: 'unhotel',
    attributes: {
            fullwidth: {
                type: 'boolean',
                default: false
            },
            source: {
                type: 'string',
                default: 'images'
            },
            displayMode: {
                type: 'string',
                default: 'carousel'
            },
            itemsToShow: {
                type: 'number',
                default: 3
            },
            itemsToShowMobile: {
                type: 'number',
                default: 1
            },
            itemsToShowTablet: {
                type: 'number',
                default: 2
            },
            showArrows: {
                type: 'boolean',
                default: true
            },
            showDots: {
                type: 'boolean',
                default: true
            },
            hasBackground: {
                type: 'boolean',
                default: false
            },
            backgroundColor: {
                type: 'string',
                default: ''
            },
            galleryImages: {
                type: 'array',
                default: []
            },
            testimonials: {
                type: 'array',
                default: []
            },
            postsPerPage: {
                type: 'number',
                default: 6
            },
            postsCategory: {
                type: 'string',
                default: ''
            }
        },
        edit: function(props) {
            var attrs = props.attributes;
            var setAttrs = props.setAttributes;

            function updateGalleryImages(mediaItems) {
                if (!Array.isArray(mediaItems)) {
                    mediaItems = [mediaItems];
                }
                var images = mediaItems.map(function(media) {
                    return {
                        id: media.id,
                        url: media.url,
                        alt: media.alt || media.title || ''
                    };
                });
                setAttrs({ galleryImages: images });
            }

            function updateTestimonials(index, field, value) {
                var items = attrs.testimonials ? attrs.testimonials.slice() : [];
                if (!items[index]) {
                    items[index] = {
                        text: '',
                        author: '',
                        role: '',
                        company: '',
                        rating: 5,
                        imageUrl: '',
                        imageId: 0
                    };
                }
                items[index][field] = value;
                setAttrs({ testimonials: items });
            }

            function addTestimonial() {
                var items = attrs.testimonials ? attrs.testimonials.slice() : [];
                items.push({
                    text: '',
                    author: '',
                    role: '',
                    company: '',
                    rating: 5,
                    imageUrl: '',
                    imageId: 0
                });
                setAttrs({ testimonials: items });
            }

            function removeTestimonial(index) {
                var items = attrs.testimonials ? attrs.testimonials.slice() : [];
                items.splice(index, 1);
                setAttrs({ testimonials: items });
            }

            // Create placeholder items for editor preview
            var placeholderItems = [];
            var hasGalleryImages = attrs.source === 'images' && attrs.galleryImages && attrs.galleryImages.length;
            var hasTestimonials = attrs.source === 'testimonials' && attrs.testimonials && attrs.testimonials.length;
            var itemCount = attrs.itemsToShow;
            if (attrs.source === 'images' && hasGalleryImages) {
                itemCount = attrs.galleryImages.length;
            } else if (attrs.source === 'testimonials' && hasTestimonials) {
                itemCount = attrs.testimonials.length;
            }
            for (var i = 0; i < itemCount; i++) {
                var itemContent = '';
                if (attrs.source === 'images') {
                    if (hasGalleryImages && attrs.galleryImages[i]) {
                        itemContent = el('img', {
                            src: attrs.galleryImages[i].url,
                            alt: attrs.galleryImages[i].alt || 'Carousel image',
                            style: {
                                width: '100%',
                                height: '150px',
                                objectFit: 'cover'
                            }
                        });
                    } else {
                        itemContent = el('div', { 
                            style: { 
                                width: '100%', 
                                height: '150px', 
                                background: '#f0f0f0', 
                                display: 'flex', 
                                alignItems: 'center', 
                                justifyContent: 'center',
                                borderRadius: '4px'
                            }
                        }, 'Image ' + (i + 1));
                    }
                } else if (attrs.source === 'testimonials') {
                    var testimonial = hasTestimonials ? attrs.testimonials[i] : null;
                    itemContent = el('div', {
                        className: 'carousel-testimonial-preview',
                        style: { padding: '20px' }
                    }, [
                        testimonial && testimonial.imageUrl ? el('img', {
                            key: 'image',
                            src: testimonial.imageUrl,
                            alt: testimonial.author || 'Author',
                            style: { width: '60px', height: '60px', borderRadius: '50%', objectFit: 'cover', marginBottom: '10px' }
                        }) : null,
                        el('blockquote', { key: 'quote', style: { margin: '0 0 10px 0', fontStyle: 'italic' } }, testimonial && testimonial.text ? testimonial.text : 'Testimonial quote ' + (i + 1)),
                        el('cite', { key: 'author', style: { fontStyle: 'italic', fontWeight: 'bold' } }, testimonial && testimonial.author ? testimonial.author : 'Author Name'),
                        testimonial && (testimonial.role || testimonial.company) ? el('span', { key: 'meta', style: { display: 'block', color: '#666', marginTop: '4px' } }, (testimonial.role ? testimonial.role : '') + (testimonial.role && testimonial.company ? ', ' : '') + (testimonial.company ? testimonial.company : '')) : null
                    ]);
                } else if (attrs.source === 'posts') {
                    itemContent = el('div', {
                        style: { padding: '20px' }
                    }, [
                        el('h3', { key: 'title', style: { margin: '0 0 10px 0', fontSize: '18px' } }, 'Blog Post Title ' + (i + 1)),
                        el('p', { key: 'excerpt', style: { margin: '0', color: '#666' } }, 'Post excerpt text here...')
                    ]);
                }

                placeholderItems.push(
                    el('div', {
                        key: i,
                        className: 'carousel-item',
                        style: {
                            border: '1px dashed #ccc',
                            borderRadius: '4px',
                            overflow: 'hidden'
                        }
                    }, itemContent)
                );
            }

            var containerStyle = {
                border: '2px dashed #ddd',
                borderRadius: '4px',
                padding: '10px'
            };
            if (attrs.hasBackground && attrs.backgroundColor) {
                containerStyle.backgroundColor = attrs.backgroundColor;
                containerStyle.padding = '20px';
            }

            var displayStyle = attrs.displayMode === 'grid' 
                ? {
                    display: 'grid',
                    gridTemplateColumns: 'repeat(' + attrs.itemsToShow + ', 1fr)',
                    gap: '20px'
                }
                : {
                    display: 'flex',
                    gap: '10px',
                    overflowX: 'auto'
                };

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Content Settings', initialOpen: true },
                        el(SelectControl, {
                            label: 'Content Type',
                            value: attrs.source,
                            options: [
                                { label: 'Images', value: 'images' },
                                 { label: 'Testimonials', value: 'testimonials' },
                                { label: 'Blog Posts', value: 'posts' }
                            ],
                            onChange: function(val) { setAttrs({ source: val }); }
                        }),
                        attrs.source === 'images' ? el('div', { className: 'carousel-media-control' },
                            [
                                el(MediaUpload, {
                                    onSelect: updateGalleryImages,
                                    allowedTypes: ['image'],
                                    gallery: true,
                                    multiple: true,
                                    value: (attrs.galleryImages || []).map(function(image) { return image.id; }),
                                    render: function(obj) {
                                        return el(Button, {
                                            onClick: obj.open,
                                            className: 'button button-large',
                                            style: { marginBottom: '10px' }
                                        }, attrs.galleryImages && attrs.galleryImages.length ? 'Edit Gallery' : 'Select Images');
                                    }
                                }),
                                attrs.galleryImages && attrs.galleryImages.length
                                    ? el('div', { className: 'carousel-selected-images' },
                                        attrs.galleryImages.map(function(image, index) {
                                            return el('div', { className: 'carousel-thumb', key: index, style: { position: 'relative', display: 'inline-block', marginRight: '10px' } },
                                                [
                                                    el('img', {
                                                        src: image.url,
                                                        alt: image.alt || 'Selected image',
                                                        style: { width: '60px', height: '60px', objectFit: 'cover', borderRadius: '4px' }
                                                    }),
                                                    el(Button, {
                                                        isSmall: true,
                                                        isDestructive: true,
                                                        style: { position: 'absolute', top: '-8px', right: '-8px' },
                                                        onClick: function() {
                                                            var updated = attrs.galleryImages.filter(function(_, imgIndex) {
                                                                return imgIndex !== index;
                                                            });
                                                            setAttrs({ galleryImages: updated });
                                                        }
                                                    }, '×')
                                                ]
                                            );
                                        })
                                    )
                                    : el('p', { className: 'description' }, 'No images selected yet.')
                            ]
                        ) : null,
                        attrs.source === 'posts' ? [
                            el(RangeControl, {
                                key: 'postsPerPage',
                                label: 'Number of Posts',
                                value: attrs.postsPerPage,
                                min: 1,
                                max: 12,
                                onChange: function(val) { setAttrs({ postsPerPage: val }); }
                            })
                        ] : null,
                        attrs.source === 'testimonials' ? el('div', { className: 'carousel-testimonials-control' },
                            [
                                (attrs.testimonials && attrs.testimonials.length)
                                    ? attrs.testimonials.map(function(testimonial, index) {
                                        return el('div', { className: 'testimonial-control-card', key: index, style: { padding: '15px', border: '1px solid #ddd', borderRadius: '6px', marginBottom: '15px' } },
                                            [
                                                el(TextareaControl, {
                                                    label: 'Testimonial Text',
                                                    value: testimonial.text,
                                                    onChange: function(val) { updateTestimonials(index, 'text', val); }
                                                }),
                                                el(TextControl, {
                                                    label: 'Author Name',
                                                    value: testimonial.author,
                                                    onChange: function(val) { updateTestimonials(index, 'author', val); }
                                                }),
                                                el(TextControl, {
                                                    label: 'Author Role',
                                                    value: testimonial.role,
                                                    onChange: function(val) { updateTestimonials(index, 'role', val); }
                                                }),
                                                el(TextControl, {
                                                    label: 'Author Company',
                                                    value: testimonial.company,
                                                    onChange: function(val) { updateTestimonials(index, 'company', val); }
                                                }),
                                                el(RangeControl, {
                                                    label: 'Rating',
                                                    value: testimonial.rating || 5,
                                                    min: 1,
                                                    max: 5,
                                                    onChange: function(val) { updateTestimonials(index, 'rating', val); }
                                                }),
                                                el('div', { className: 'testimonial-image-control', style: { marginTop: '10px' } },
                                                    [
                                                        el('label', { style: { display: 'block', fontWeight: '600', marginBottom: '8px' } }, 'Author Image'),
                                                        el(MediaUpload, {
                                                            onSelect: function(media) {
                                                                updateTestimonials(index, 'imageUrl', media.url);
                                                                updateTestimonials(index, 'imageId', media.id);
                                                            },
                                                            allowedTypes: ['image'],
                                                            value: testimonial.imageId,
                                                            render: function(obj) {
                                                                return el(Button, {
                                                                    onClick: obj.open,
                                                                    className: 'button',
                                                                    style: { marginBottom: '10px' }
                                                                }, testimonial.imageUrl ? 'Change Image' : 'Select Image');
                                                            }
                                                        }),
                                                        testimonial.imageUrl ? el('div', { style: { position: 'relative', display: 'inline-block' } },
                                                            [
                                                                el('img', {
                                                                    src: testimonial.imageUrl,
                                                                    alt: testimonial.author || 'Author',
                                                                    style: { width: '80px', height: '80px', borderRadius: '50%', objectFit: 'cover' }
                                                                }),
                                                                el(Button, {
                                                                    isSmall: true,
                                                                    isDestructive: true,
                                                                    style: { position: 'absolute', top: '-8px', right: '-8px' },
                                                                    onClick: function() {
                                                                        updateTestimonials(index, 'imageUrl', '');
                                                                        updateTestimonials(index, 'imageId', 0);
                                                                    }
                                                                }, '×')
                                                            ]
                                                        ) : null
                                                    ]
                                                ),
                                                el(Button, {
                                                    isDestructive: true,
                                                    onClick: function() { removeTestimonial(index); },
                                                    style: { marginTop: '10px' }
                                                }, 'Remove Testimonial')
                                            ]
                                        );
                                    })
                                    : el('p', { className: 'description' }, 'No testimonials yet. Add one below.'),
                                el(Button, {
                                    isPrimary: true,
                                    onClick: addTestimonial
                                }, 'Add Testimonial')
                            ]
                        ) : null
                    ),
                    el(PanelBody, { title: 'Display Settings', initialOpen: false },
                        el(SelectControl, {
                            label: 'Display Mode',
                            value: attrs.displayMode,
                            options: [
                                { label: 'Carousel/Slider', value: 'carousel' },
                                { label: 'Grid', value: 'grid' }
                            ],
                            onChange: function(val) { setAttrs({ displayMode: val }); }
                        }),
                        attrs.displayMode === 'carousel' ? [
                            el(RangeControl, {
                                key: 'itemsToShow',
                                label: 'Items Per Page (Desktop)',
                                value: attrs.itemsToShow,
                                min: 1,
                                max: 6,
                                onChange: function(val) { setAttrs({ itemsToShow: val }); }
                            }),
                            el(RangeControl, {
                                key: 'itemsToShowTablet',
                                label: 'Items Per Page (Tablet)',
                                value: attrs.itemsToShowTablet || 2,
                                min: 1,
                                max: 4,
                                onChange: function(val) { setAttrs({ itemsToShowTablet: val }); }
                            }),
                            el(RangeControl, {
                                key: 'itemsToShowMobile',
                                label: 'Items Per Page (Mobile)',
                                value: attrs.itemsToShowMobile || 1,
                                min: 1,
                                max: 2,
                                onChange: function(val) { setAttrs({ itemsToShowMobile: val }); }
                            }),
                            el(ToggleControl, {
                                key: 'showArrows',
                                label: 'Show Arrows',
                                checked: attrs.showArrows,
                                onChange: function(val) { setAttrs({ showArrows: val }); }
                            }),
                            el(ToggleControl, {
                                key: 'showDots',
                                label: 'Show Dots',
                                checked: attrs.showDots,
                                onChange: function(val) { setAttrs({ showDots: val }); }
                            })
                        ] : [
                            el(RangeControl, {
                                key: 'itemsToShow',
                                label: 'Columns',
                                value: attrs.itemsToShow,
                                min: 1,
                                max: 4,
                                onChange: function(val) { setAttrs({ itemsToShow: val }); }
                            })
                        ]
                    ),
                    el(PanelBody, { title: 'Layout Settings', initialOpen: false },
                        el(ToggleControl, {
                            label: 'Full Width',
                            checked: attrs.fullwidth,
                            onChange: function(val) { setAttrs({ fullwidth: val }); }
                        }),
                        el(ToggleControl, {
                            label: 'Background Color',
                            checked: attrs.hasBackground,
                            onChange: function(val) { setAttrs({ hasBackground: val }); }
                        }),
                        attrs.hasBackground ? el('div', {
                            style: { marginTop: '16px' }
                        }, [
                            el('label', {
                                style: { display: 'block', marginBottom: '8px' }
                            }, 'Background Color'),
                            el(ColorPalette, {
                                value: attrs.backgroundColor,
                                onChange: function(color) { setAttrs({ backgroundColor: color }); }
                            })
                        ]) : null
                    )
                ),
                el('div', {
                    className: 'unhotel-carousel-editor' + (attrs.fullwidth ? ' fullwidth' : ''),
                    style: containerStyle
                },
                    el('div', {
                        className: 'carousel-track' + (attrs.displayMode === 'grid' ? ' grid-mode' : ' carousel-mode'),
                        style: displayStyle
                    }, placeholderItems)
                )
            ];
        },
        save: function() {
            // Dynamic rendering in PHP via render_callback
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components
);
