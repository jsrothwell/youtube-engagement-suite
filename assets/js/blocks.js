(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, TextControl, ToggleControl } = wp.components;
    const { __ } = wp.i18n;
    const { ServerSideRender } = wp.serverSideRender || wp.components;

    // YouTube Subscribe Button Block
    registerBlockType('ytes/subscribe-button', {
        title: __('YouTube Subscribe Button', 'yt-engagement-suite'),
        icon: 'youtube',
        category: 'widgets',
        attributes: {
            layout: {
                type: 'string',
                default: 'default'
            },
            showCount: {
                type: 'boolean',
                default: false
            },
            alignment: {
                type: 'string',
                default: 'left'
            }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return wp.element.createElement('div', blockProps, [
                wp.element.createElement(InspectorControls, null,
                    wp.element.createElement(PanelBody, { title: __('Settings', 'yt-engagement-suite') }, [
                        wp.element.createElement(SelectControl, {
                            label: __('Layout', 'yt-engagement-suite'),
                            value: attributes.layout,
                            options: [
                                { label: __('Default', 'yt-engagement-suite'), value: 'default' },
                                { label: __('Full', 'yt-engagement-suite'), value: 'full' }
                            ],
                            onChange: function(value) {
                                setAttributes({ layout: value });
                            }
                        }),
                        wp.element.createElement(ToggleControl, {
                            label: __('Show Subscriber Count', 'yt-engagement-suite'),
                            checked: attributes.showCount,
                            onChange: function(value) {
                                setAttributes({ showCount: value });
                            }
                        }),
                        wp.element.createElement(SelectControl, {
                            label: __('Alignment', 'yt-engagement-suite'),
                            value: attributes.alignment,
                            options: [
                                { label: __('Left', 'yt-engagement-suite'), value: 'left' },
                                { label: __('Center', 'yt-engagement-suite'), value: 'center' },
                                { label: __('Right', 'yt-engagement-suite'), value: 'right' }
                            ],
                            onChange: function(value) {
                                setAttributes({ alignment: value });
                            }
                        })
                    ])
                ),
                wp.element.createElement('div', { 
                    className: 'ytes-block-preview',
                    style: { textAlign: attributes.alignment }
                },
                    wp.element.createElement('div', {
                        style: {
                            background: '#f0f0f0',
                            padding: '20px',
                            borderRadius: '4px',
                            textAlign: attributes.alignment
                        }
                    },
                        wp.element.createElement('div', {
                            style: {
                                display: 'inline-block',
                                background: '#FF0000',
                                color: 'white',
                                padding: '10px 20px',
                                borderRadius: '2px',
                                fontWeight: 'bold'
                            }
                        }, '▶ Subscribe')
                    )
                )
            ]);
        },
        save: function() {
            return null; // Server-side rendering
        }
    });

    // Email Signup Block
    registerBlockType('ytes/email-signup', {
        title: __('Email Signup Form', 'yt-engagement-suite'),
        icon: 'email',
        category: 'widgets',
        attributes: {
            title: {
                type: 'string',
                default: 'Subscribe to Our Newsletter'
            },
            description: {
                type: 'string',
                default: 'Get notified about new videos!'
            },
            buttonText: {
                type: 'string',
                default: 'Subscribe'
            },
            showName: {
                type: 'boolean',
                default: false
            },
            alignment: {
                type: 'string',
                default: 'left'
            }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return wp.element.createElement('div', blockProps, [
                wp.element.createElement(InspectorControls, null,
                    wp.element.createElement(PanelBody, { title: __('Settings', 'yt-engagement-suite') }, [
                        wp.element.createElement(TextControl, {
                            label: __('Title', 'yt-engagement-suite'),
                            value: attributes.title,
                            onChange: function(value) {
                                setAttributes({ title: value });
                            }
                        }),
                        wp.element.createElement(TextControl, {
                            label: __('Description', 'yt-engagement-suite'),
                            value: attributes.description,
                            onChange: function(value) {
                                setAttributes({ description: value });
                            }
                        }),
                        wp.element.createElement(TextControl, {
                            label: __('Button Text', 'yt-engagement-suite'),
                            value: attributes.buttonText,
                            onChange: function(value) {
                                setAttributes({ buttonText: value });
                            }
                        }),
                        wp.element.createElement(ToggleControl, {
                            label: __('Show Name Field', 'yt-engagement-suite'),
                            checked: attributes.showName,
                            onChange: function(value) {
                                setAttributes({ showName: value });
                            }
                        }),
                        wp.element.createElement(SelectControl, {
                            label: __('Alignment', 'yt-engagement-suite'),
                            value: attributes.alignment,
                            options: [
                                { label: __('Left', 'yt-engagement-suite'), value: 'left' },
                                { label: __('Center', 'yt-engagement-suite'), value: 'center' },
                                { label: __('Right', 'yt-engagement-suite'), value: 'right' }
                            ],
                            onChange: function(value) {
                                setAttributes({ alignment: value });
                            }
                        })
                    ])
                ),
                wp.element.createElement('div', {
                    className: 'ytes-block-preview',
                    style: { textAlign: attributes.alignment }
                },
                    wp.element.createElement('div', {
                        style: {
                            background: '#f9f9f9',
                            padding: '30px',
                            borderRadius: '8px',
                            border: '1px solid #e0e0e0',
                            display: 'inline-block',
                            minWidth: '300px'
                        }
                    }, [
                        attributes.title && wp.element.createElement('h3', {
                            style: { marginTop: 0 }
                        }, attributes.title),
                        attributes.description && wp.element.createElement('p', {
                            style: { marginBottom: '20px' }
                        }, attributes.description),
                        attributes.showName && wp.element.createElement('input', {
                            type: 'text',
                            placeholder: __('Your Name', 'yt-engagement-suite'),
                            style: {
                                width: '100%',
                                padding: '10px',
                                marginBottom: '10px',
                                border: '1px solid #ddd',
                                borderRadius: '4px'
                            },
                            disabled: true
                        }),
                        wp.element.createElement('input', {
                            type: 'email',
                            placeholder: __('Your Email', 'yt-engagement-suite'),
                            style: {
                                width: '100%',
                                padding: '10px',
                                marginBottom: '10px',
                                border: '1px solid #ddd',
                                borderRadius: '4px'
                            },
                            disabled: true
                        }),
                        wp.element.createElement('button', {
                            style: {
                                width: '100%',
                                padding: '12px',
                                background: '#0073aa',
                                color: 'white',
                                border: 'none',
                                borderRadius: '4px',
                                fontWeight: 'bold',
                                cursor: 'pointer'
                            }
                        }, attributes.buttonText)
                    ])
                )
            ]);
        },
        save: function() {
            return null;
        }
    });

    // Social Share Block
    registerBlockType('ytes/social-share', {
        title: __('Social Share Buttons', 'yt-engagement-suite'),
        icon: 'share',
        category: 'widgets',
        attributes: {
            layout: {
                type: 'string',
                default: 'horizontal'
            },
            size: {
                type: 'string',
                default: 'medium'
            },
            alignment: {
                type: 'string',
                default: 'left'
            }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return wp.element.createElement('div', blockProps, [
                wp.element.createElement(InspectorControls, null,
                    wp.element.createElement(PanelBody, { title: __('Settings', 'yt-engagement-suite') }, [
                        wp.element.createElement(SelectControl, {
                            label: __('Layout', 'yt-engagement-suite'),
                            value: attributes.layout,
                            options: [
                                { label: __('Horizontal', 'yt-engagement-suite'), value: 'horizontal' },
                                { label: __('Vertical', 'yt-engagement-suite'), value: 'vertical' }
                            ],
                            onChange: function(value) {
                                setAttributes({ layout: value });
                            }
                        }),
                        wp.element.createElement(SelectControl, {
                            label: __('Size', 'yt-engagement-suite'),
                            value: attributes.size,
                            options: [
                                { label: __('Small', 'yt-engagement-suite'), value: 'small' },
                                { label: __('Medium', 'yt-engagement-suite'), value: 'medium' },
                                { label: __('Large', 'yt-engagement-suite'), value: 'large' }
                            ],
                            onChange: function(value) {
                                setAttributes({ size: value });
                            }
                        }),
                        wp.element.createElement(SelectControl, {
                            label: __('Alignment', 'yt-engagement-suite'),
                            value: attributes.alignment,
                            options: [
                                { label: __('Left', 'yt-engagement-suite'), value: 'left' },
                                { label: __('Center', 'yt-engagement-suite'), value: 'center' },
                                { label: __('Right', 'yt-engagement-suite'), value: 'right' }
                            ],
                            onChange: function(value) {
                                setAttributes({ alignment: value });
                            }
                        })
                    ])
                ),
                wp.element.createElement('div', {
                    className: 'ytes-block-preview',
                    style: { textAlign: attributes.alignment }
                },
                    wp.element.createElement('div', {
                        style: {
                            display: 'inline-flex',
                            gap: '10px',
                            flexDirection: attributes.layout === 'vertical' ? 'column' : 'row'
                        }
                    }, [
                        ['Facebook', '#1877F2'],
                        ['Twitter', '#1DA1F2'],
                        ['LinkedIn', '#0A66C2']
                    ].map(function(item) {
                        return wp.element.createElement('div', {
                            style: {
                                background: item[1],
                                color: 'white',
                                padding: attributes.size === 'small' ? '8px 12px' : attributes.size === 'large' ? '12px 20px' : '10px 16px',
                                borderRadius: '4px',
                                fontSize: attributes.size === 'small' ? '12px' : attributes.size === 'large' ? '16px' : '14px',
                                fontWeight: 'bold'
                            }
                        }, item[0]);
                    }))
                )
            ]);
        },
        save: function() {
            return null;
        }
    });

    // CTA Button Block
    registerBlockType('ytes/cta-button', {
        title: __('Watch on YouTube Button', 'yt-engagement-suite'),
        icon: 'video-alt3',
        category: 'widgets',
        attributes: {
            text: {
                type: 'string',
                default: 'Watch on YouTube'
            },
            videoUrl: {
                type: 'string',
                default: ''
            },
            style: {
                type: 'string',
                default: 'primary'
            },
            size: {
                type: 'string',
                default: 'medium'
            },
            alignment: {
                type: 'string',
                default: 'left'
            }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return wp.element.createElement('div', blockProps, [
                wp.element.createElement(InspectorControls, null,
                    wp.element.createElement(PanelBody, { title: __('Settings', 'yt-engagement-suite') }, [
                        wp.element.createElement(TextControl, {
                            label: __('Button Text', 'yt-engagement-suite'),
                            value: attributes.text,
                            onChange: function(value) {
                                setAttributes({ text: value });
                            }
                        }),
                        wp.element.createElement(TextControl, {
                            label: __('YouTube Video URL', 'yt-engagement-suite'),
                            value: attributes.videoUrl,
                            help: __('Leave empty to use the post meta field', 'yt-engagement-suite'),
                            onChange: function(value) {
                                setAttributes({ videoUrl: value });
                            }
                        }),
                        wp.element.createElement(SelectControl, {
                            label: __('Style', 'yt-engagement-suite'),
                            value: attributes.style,
                            options: [
                                { label: __('Primary', 'yt-engagement-suite'), value: 'primary' },
                                { label: __('Secondary', 'yt-engagement-suite'), value: 'secondary' },
                                { label: __('YouTube Red', 'yt-engagement-suite'), value: 'youtube' }
                            ],
                            onChange: function(value) {
                                setAttributes({ style: value });
                            }
                        }),
                        wp.element.createElement(SelectControl, {
                            label: __('Size', 'yt-engagement-suite'),
                            value: attributes.size,
                            options: [
                                { label: __('Small', 'yt-engagement-suite'), value: 'small' },
                                { label: __('Medium', 'yt-engagement-suite'), value: 'medium' },
                                { label: __('Large', 'yt-engagement-suite'), value: 'large' }
                            ],
                            onChange: function(value) {
                                setAttributes({ size: value });
                            }
                        }),
                        wp.element.createElement(SelectControl, {
                            label: __('Alignment', 'yt-engagement-suite'),
                            value: attributes.alignment,
                            options: [
                                { label: __('Left', 'yt-engagement-suite'), value: 'left' },
                                { label: __('Center', 'yt-engagement-suite'), value: 'center' },
                                { label: __('Right', 'yt-engagement-suite'), value: 'right' }
                            ],
                            onChange: function(value) {
                                setAttributes({ alignment: value });
                            }
                        })
                    ])
                ),
                wp.element.createElement('div', {
                    className: 'ytes-block-preview',
                    style: { textAlign: attributes.alignment }
                },
                    wp.element.createElement('button', {
                        style: {
                            background: attributes.style === 'youtube' ? '#FF0000' : attributes.style === 'primary' ? '#0073aa' : '#666',
                            color: 'white',
                            padding: attributes.size === 'small' ? '10px 20px' : attributes.size === 'large' ? '16px 32px' : '12px 24px',
                            border: 'none',
                            borderRadius: '4px',
                            fontSize: attributes.size === 'small' ? '14px' : attributes.size === 'large' ? '18px' : '16px',
                            fontWeight: 'bold',
                            cursor: 'pointer',
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: '8px'
                        }
                    }, [
                        wp.element.createElement('span', null, '▶'),
                        wp.element.createElement('span', null, attributes.text)
                    ])
                )
            ]);
        },
        save: function() {
            return null;
        }
    });

})(window.wp);
