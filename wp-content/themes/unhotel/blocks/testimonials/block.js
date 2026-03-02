(function(blocks, element, blockEditor, components) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var RichText = blockEditor.RichText;
    var MediaUpload = blockEditor.MediaUpload;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var RangeControl = components.RangeControl;
    var SelectControl = components.SelectControl;
    var Button = components.Button;
    var TextControl = components.TextControl;
    var ColorPalette = blockEditor.ColorPalette;

    registerBlockType('unhotel/testimonials', {
        title: 'Testimonial Carousel/Grid',
        icon: 'format-quote',
        category: 'unhotel',
        attributes: {
            testimonials: {
                type: 'array',
                default: [{
                    testimonialText: '',
                    authorName: '',
                    authorCompany: '',
                    authorPosition: '',
                    rating: 5,
                    authorImageUrl: '',
                    authorImageId: 0
                }]
            },
            showRating: {
                type: 'boolean',
                default: true
            },
            showImage: {
                type: 'boolean',
                default: true
            },
            itemsPerSlide: {
                type: 'number',
                default: 1
            },
            showArrows: {
                type: 'boolean',
                default: true
            },
            showDots: {
                type: 'boolean',
                default: true
            },
            fullWidth: {
                type: 'boolean',
                default: false
            },
            hasBackground: {
                type: 'boolean',
                default: false
            },
            backgroundColor: {
                type: 'string',
                default: ''
            },
            textAlign: {
                type: 'string',
                default: 'left'
            },
            displayMode: {
                type: 'string',
                default: 'slider'
            }
        },

        edit: function(props) {
            var attrs = props.attributes;
            var setAttrs = props.setAttributes;

            // Ensure testimonials is always an array
            if (!attrs.testimonials || !Array.isArray(attrs.testimonials)) {
                attrs.testimonials = [{
                    testimonialText: '',
                    authorName: '',
                    authorCompany: '',
                    authorPosition: '',
                    rating: 5,
                    authorImageUrl: '',
                    authorImageId: 0
                }];
                setAttrs({ testimonials: attrs.testimonials });
            }

            var addTestimonial = function() {
                var newTestimonials = (attrs.testimonials || []).slice();
                newTestimonials.push({
                    testimonialText: '',
                    authorName: '',
                    authorCompany: '',
                    authorPosition: '',
                    rating: 5,
                    authorImageUrl: '',
                    authorImageId: 0
                });
                setAttrs({ testimonials: newTestimonials });
            };

            var removeTestimonial = function(index) {
                var newTestimonials = (attrs.testimonials || []).slice();
                if (newTestimonials.length > 1) {
                    newTestimonials.splice(index, 1);
                    setAttrs({ testimonials: newTestimonials });
                }
            };

            var updateTestimonial = function(index, field, value) {
                var newTestimonials = (attrs.testimonials || []).slice();
                newTestimonials[index] = Object.assign({}, newTestimonials[index], { [field]: value });
                setAttrs({ testimonials: newTestimonials });
            };

            var containerClasses = 'unhotel-testimonials-carousel-editor';
            if (attrs.fullWidth) {
                containerClasses += ' fullwidth';
            }

            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Display Settings', initialOpen: true },
                        el(SelectControl, {
                            label: 'Display Mode',
                            value: attrs.displayMode || 'slider',
                            options: [
                                { label: 'Slider/Carousel', value: 'slider' },
                                { label: 'Grid (4 Items)', value: 'grid' }
                            ],
                            onChange: function(val) { setAttrs({ displayMode: val }); }
                        }),
                        attrs.displayMode === 'slider' ? el(RangeControl, {
                            label: 'Items Per Slide',
                            value: attrs.itemsPerSlide,
                            onChange: function(val) { setAttrs({ itemsPerSlide: val }); },
                            min: 1,
                            max: 4
                        }) : null,
                        attrs.displayMode === 'slider' ? el(ToggleControl, {
                            label: 'Show Navigation Arrows',
                            checked: attrs.showArrows,
                            onChange: function(val) { setAttrs({ showArrows: val }); }
                        }) : null,
                        attrs.displayMode === 'slider' ? el(ToggleControl, {
                            label: 'Show Dots',
                            checked: attrs.showDots,
                            onChange: function(val) { setAttrs({ showDots: val }); }
                        }) : null,
                        el(ToggleControl, {
                            label: 'Show Rating Stars',
                            checked: attrs.showRating,
                            onChange: function(val) { setAttrs({ showRating: val }); }
                        }),
                        el(ToggleControl, {
                            label: 'Show Author Image',
                            checked: attrs.showImage,
                            onChange: function(val) { setAttrs({ showImage: val }); }
                        }),
                        el(SelectControl, {
                            label: 'Text Alignment',
                            value: attrs.textAlign,
                            options: [
                                { label: 'Left', value: 'left' },
                                { label: 'Center', value: 'center' },
                                { label: 'Right', value: 'right' }
                            ],
                            onChange: function(val) { setAttrs({ textAlign: val }); }
                        }),
                        el(ToggleControl, {
                            label: 'Full Width',
                            checked: attrs.fullWidth,
                            onChange: function(val) { setAttrs({ fullWidth: val }); }
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
                    ),
                    el(PanelBody, { title: 'Testimonials', initialOpen: true },
                        (attrs.testimonials || []).map(function(testimonial, index) {
                            var ratingStars = [];
                            for (var i = 1; i <= 5; i++) {
                                ratingStars.push(
                                    el('span', {
                                        key: i,
                                        className: 'star ' + (i <= testimonial.rating ? 'filled' : ''),
                                        style: {
                                            fontSize: '16px',
                                            color: i <= testimonial.rating ? '#ffc107' : '#ddd',
                                            cursor: 'pointer'
                                        },
                                        onClick: function(starNum) {
                                            return function() {
                                                updateTestimonial(index, 'rating', starNum);
                                            };
                                        }(i)
                                    }, '★')
                                );
                            }

                            return el('div', {
                                key: index,
                                style: {
                                    border: '1px solid #ddd',
                                    borderRadius: '8px',
                                    padding: '16px',
                                    marginBottom: '16px',
                                    backgroundColor: '#f9f9f9'
                                }
                            }, [
                                el('div', {
                                    style: {
                                        display: 'flex',
                                        justifyContent: 'space-between',
                                        alignItems: 'center',
                                        marginBottom: '12px'
                                    }
                                }, [
                                    el('strong', {}, 'Testimonial ' + (index + 1)),
                                    (attrs.testimonials || []).length > 1 ? el(Button, {
                                        isDestructive: true,
                                        isSmall: true,
                                        onClick: function() { removeTestimonial(index); }
                                    }, 'Remove') : null
                                ]),
                                el(RichText, {
                                    tagName: 'p',
                                    value: testimonial.testimonialText,
                                    onChange: function(val) { updateTestimonial(index, 'testimonialText', val); },
                                    placeholder: 'Enter testimonial text...',
                                    style: { marginBottom: '12px', minHeight: '60px' }
                                }),
                                el('div', { style: { marginBottom: '12px' } },
                                    el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '12px', fontWeight: 'bold' } }, 'Rating'),
                                    el('div', { style: { display: 'flex', gap: '4px', alignItems: 'center' } },
                                        ratingStars,
                                        el('span', { style: { marginLeft: '8px', fontSize: '12px', color: '#666' } }, testimonial.rating + '/5')
                                    )
                                ),
                                el(TextControl, {
                                    label: 'Author Name',
                                    value: testimonial.authorName,
                                    onChange: function(val) { updateTestimonial(index, 'authorName', val); },
                                    placeholder: 'John Doe',
                                    style: { marginBottom: '8px' }
                                }),
                                el(TextControl, {
                                    label: 'Company/Organization',
                                    value: testimonial.authorCompany,
                                    onChange: function(val) { updateTestimonial(index, 'authorCompany', val); },
                                    placeholder: 'Company Name',
                                    style: { marginBottom: '8px' }
                                }),
                                el(TextControl, {
                                    label: 'Position/Title',
                                    value: testimonial.authorPosition,
                                    onChange: function(val) { updateTestimonial(index, 'authorPosition', val); },
                                    placeholder: 'CEO, Manager, etc.',
                                    style: { marginBottom: '8px' }
                                }),
                                el('div', { style: { marginTop: '8px' } },
                                    el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '12px', fontWeight: 'bold' } }, 'Author Photo'),
                                    el(MediaUpload, {
                                        onSelect: function(media) {
                                            if (media && media.url && media.id) {
                                                updateTestimonial(index, 'authorImageUrl', media.url);
                                                updateTestimonial(index, 'authorImageId', media.id);
                                            }
                                        },
                                        allowedTypes: ['image'],
                                        value: testimonial.authorImageId || 0,
                                        render: function(obj) {
                                            if (!obj || typeof obj.open !== 'function') {
                                                return el('div', {}, 'Media uploader unavailable');
                                            }
                                            return el('div', {},
                                                el(Button, {
                                                    onClick: obj.open,
                                                    isSmall: true,
                                                    style: { marginBottom: '8px' }
                                                }, testimonial.authorImageUrl ? 'Change Image' : 'Select Photo'),
                                                testimonial.authorImageUrl ? el('div', {},
                                                    el('img', {
                                                        src: testimonial.authorImageUrl,
                                                        alt: testimonial.authorName || 'Author',
                                                        style: { width: '60px', height: '60px', borderRadius: '50%', objectFit: 'cover', marginTop: '8px', display: 'block' },
                                                        onError: function(e) {
                                                            // Hide broken images instead of causing errors
                                                            if (e.target) {
                                                                e.target.style.display = 'none';
                                                            }
                                                        }
                                                    }),
                                                    el(Button, {
                                                        onClick: function() {
                                                            updateTestimonial(index, 'authorImageUrl', '');
                                                            updateTestimonial(index, 'authorImageId', 0);
                                                        },
                                                        isDestructive: true,
                                                        isSmall: true,
                                                        style: { marginTop: '8px' }
                                                    }, 'Remove')
                                                ) : null
                                            );
                                        }
                                    })
                                )
                            ]);
                        }),
                        el(Button, {
                            isPrimary: true,
                            onClick: addTestimonial,
                            style: { marginTop: '16px', width: '100%' }
                        }, '+ Add Testimonial')
                    )
                ),
                el('div', {
                    className: containerClasses,
                    style: {
                        border: '2px dashed #ddd',
                        borderRadius: '8px',
                        padding: '20px',
                        backgroundColor: attrs.hasBackground && attrs.backgroundColor ? attrs.backgroundColor : '#fff'
                    }
                },
                    el('div', {
                        className: 'testimonials-carousel-preview',
                        style: {
                            display: 'flex',
                            gap: '20px',
                            overflowX: 'auto',
                            padding: '10px 0'
                        }
                    },
                        (attrs.testimonials || []).map(function(testimonial, index) {
                            var ratingStars = [];
                            if (attrs.showRating) {
                                for (var i = 1; i <= 5; i++) {
                                    ratingStars.push(
                                        el('span', {
                                            key: i,
                                            className: 'star ' + (i <= testimonial.rating ? 'filled' : ''),
                                            style: {
                                                fontSize: '16px',
                                                color: i <= testimonial.rating ? '#ffc107' : '#ddd'
                                            }
                                        }, '★')
                                    );
                                }
                            }

                            return el('div', {
                                key: index,
                                className: 'testimonial-preview-item',
                                style: {
                                    minWidth: '300px',
                                    padding: '20px',
                                    backgroundColor: '#f9f9f9',
                                    borderRadius: '8px',
                                    textAlign: attrs.textAlign
                                }
                            }, [
                                attrs.showRating && ratingStars.length > 0 ? el('div', {
                                    className: 'testimonial-rating',
                                    style: { marginBottom: '12px', display: 'flex', gap: '4px' }
                                }, ratingStars) : null,
                                el('blockquote', {
                                    className: 'testimonial-content',
                                    style: {
                                        margin: '0 0 16px 0',
                                        fontSize: '16px',
                                        fontStyle: 'italic',
                                        color: '#333'
                                    }
                                }, testimonial.testimonialText || el('span', { style: { color: '#999' } }, 'Enter testimonial text...')),
                                el('div', {
                                    className: 'testimonial-author',
                                    style: {
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: '12px',
                                        marginTop: '12px'
                                    }
                                }, [
                                    attrs.showImage && testimonial.authorImageUrl ? el('img', {
                                        src: testimonial.authorImageUrl,
                                        alt: testimonial.authorName || 'Author',
                                        style: {
                                            width: '60px',
                                            height: '60px',
                                            borderRadius: '50%',
                                            objectFit: 'cover',
                                            border: '2px solid #e0e0e0',
                                            flexShrink: 0,
                                            display: 'block'
                                        },
                                        onError: function(e) {
                                            // Hide broken images to prevent errors
                                            try {
                                                if (e && e.target) {
                                                    e.target.style.display = 'none';
                                                }
                                            } catch(err) {
                                                // Silently fail to prevent recovery mode
                                            }
                                        }
                                    }) : (attrs.showImage ? el('div', {
                                        style: {
                                            width: '60px',
                                            height: '60px',
                                            borderRadius: '50%',
                                            backgroundColor: '#e0e0e0',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            fontSize: '24px',
                                            color: '#999',
                                            flexShrink: 0
                                        }
                                    }, '👤') : null),
                                    el('div', {
                                        style: { flex: 1 }
                                    }, [
                                        testimonial.authorName ? el('cite', {
                                            style: {
                                                display: 'block',
                                                fontStyle: 'normal',
                                                fontWeight: 'bold',
                                                fontSize: '14px',
                                                marginBottom: '4px',
                                                color: '#333'
                                            }
                                        }, testimonial.authorName) : el('cite', {
                                            style: {
                                                display: 'block',
                                                fontStyle: 'normal',
                                                fontSize: '14px',
                                                marginBottom: '4px',
                                                color: '#999'
                                            }
                                        }, 'Author Name'),
                                        (testimonial.authorPosition || testimonial.authorCompany) ? el('span', {
                                            style: {
                                                display: 'block',
                                                fontSize: '12px',
                                                color: '#666',
                                                lineHeight: '1.4'
                                            }
                                        }, [testimonial.authorPosition ? testimonial.authorPosition + (testimonial.authorCompany ? ', ' : '') : '', testimonial.authorCompany ? testimonial.authorCompany : '']) : null
                                    ])
                                ])
                            ]);
                        })
                    )
                )
            ];
        },

        save: function() {
            // Dynamic rendering in PHP via render_callback
            // Editor preview is handled in the edit() function above
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components
);
