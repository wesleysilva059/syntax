<?php

/**
 * @class PPTestimonialsModule
 */
class PPTestimonialsModule extends FLBuilderModule {

    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct()
    {
        parent::__construct(array(
            'name'          => __('Testimonials', 'bb-powerpack'),
            'description'   => __('Addon to display testimonials.', 'bb-powerpack'),
            'group'         => pp_get_modules_group(),
            'category'		=> pp_get_modules_cat( 'content' ),
            'dir'           => BB_POWERPACK_DIR . 'modules/pp-testimonials/',
            'url'           => BB_POWERPACK_URL . 'modules/pp-testimonials/',
            'editor_export' => true, // Defaults to true and can be omitted.
            'enabled'       => true, // Defaults to true and can be omitted.,
        ));

		$this->add_css(BB_POWERPACK()->fa_css);
	}
	
	public function enqueue_scripts() {
		if ( FLBuilderModel::is_builder_active() || 'slider' == $this->settings->layout ) {
			$this->add_css( BB_POWERPACK()->fa_css );
			$this->add_css( 'pp-owl-carousel' );
			$this->add_css( 'pp-owl-carousel-theme' );
			$this->add_js( 'pp-owl-carousel' );
		}
	}

	public function filter_settings( $settings, $helper ) {
		
		// Handle heading's old typography fields.
		$settings = PP_Module_Fields::handle_typography_field( $settings, array(
			'heading_font'	=> array(
				'type'			=> 'font'
			),
			'heading_font_size'	=> array(
				'type'			=> 'font_size',
			),
			'heading_alignment'	=> array(
				'type'			=> 'text_align'
			)
		), 'heading_typography' );
		
		// Handle title's old typography fields.
		$settings = PP_Module_Fields::handle_typography_field( $settings, array(
			'title_font'	=> array(
				'type'			=> 'font'
			),
			'title_font_size'	=> array(
				'type'			=> 'font_size',
			),
		), 'title_typography' );

		// Handle subtitle's old typography fields.
		$settings = PP_Module_Fields::handle_typography_field( $settings, array(
			'subtitle_font'	=> array(
				'type'			=> 'font'
			),
			'subtitle_font_size'	=> array(
				'type'			=> 'font_size',
			),
		), 'subtitle_typography' );

		// Handle text's old typography fields.
		$settings = PP_Module_Fields::handle_typography_field( $settings, array(
			'text_font'	=> array(
				'type'			=> 'font'
			),
			'text_font_size'	=> array(
				'type'			=> 'font_size',
			),
		), 'text_typography' );

		// Handle old image border and radius fields.
		$settings = PP_Module_Fields::handle_border_field( $settings, array(
			'image_border_style'	=> array(
				'type'				=> 'style'
			),
			'border_width'	=> array(
				'type'				=> 'width',
			),
			'border_color'	=> array(
				'type'				=> 'color',
			),
			'border_radius'	=> array(
				'type'				=> 'radius'
			),
		), 'image_border' );

		// Handle old content border and radius fields.
		$settings = PP_Module_Fields::handle_border_field( $settings, array(
			'box_border_style'	=> array(
				'type'				=> 'style'
			),
			'box_border_width'	=> array(
				'type'				=> 'width'
			),
			'box_border_color'	=> array(
				'type'				=> 'color'
			),
			'box_border_radius'	=> array(
				'type'				=> 'radius'
			),
			'box_shadow'		=> array(
				'type'				=> 'shadow',
				'condition'			=> ( isset( $settings->box_shadow_setting ) && 'yes' == $settings->box_shadow_setting )
			),
			'box_shadow_color'	=> array(
				'type'				=> 'shadow_color',
				'condition'			=> ( isset( $settings->box_shadow_setting ) && 'yes' == $settings->box_shadow_setting ),
				'opacity'			=> isset( $settings->box_shadow_opacity ) ? $settings->box_shadow_opacity : 1
			),
		), 'box_border' );

		return $settings;
	}

	public function get_alt( $settings )
	{
		if(is_object($settings->photo)) {
			$photo = $settings->photo;
		}
		else {
			$photo = FLBuilderPhoto::get_attachment_data($settings->photo);
		}

		if ( ! empty( $photo->alt ) ) {
			return htmlspecialchars( $photo->alt );
		}
		else if ( ! empty( $photo->description ) ) {
			return htmlspecialchars( $photo->description );
		}
		else if ( ! empty( $photo->caption ) ) {
			return htmlspecialchars( $photo->caption );
		}
		else if ( ! empty( $photo->title ) ) {
			return htmlspecialchars( $photo->title );
		}
		else if ( ! empty( $settings->title ) ) {
			return htmlspecialchars( $settings->title );
		}
	}
}

/**
 * Register the module and its form settings.
 */
BB_PowerPack::register_module('PPTestimonialsModule', array(
	'general'      => array( // Tab
		'title'         => __( 'General', 'bb-powerpack' ), // Tab title
		'sections'      => array( // Tab Sections
			'heading'       => array( // Section
				'title'         => '', // Section Title
				'fields'        => array( // Section Fields
					'heading'         => array(
						'type'          => 'text',
						'default'       => __( 'Testimonials', 'bb-powerpack' ),
						'label'         => __( 'Heading', 'bb-powerpack' ),
						'connections'   => array( 'string', 'html' ),
						'preview'       => array(
							'type'          => 'text',
							'selector'      => '.pp-testimonials-heading',
						),
					),
					'layout'    => array(
						'type'          => 'pp-switch',
						'label'         => __( 'Layout', 'bb-powerpack' ),
						'default'       => 'slider',
						'options'       => array(
							'grid'          => __( 'Grid', 'bb-powerpack' ),
							'slider'      	=> __( 'Slider', 'bb-powerpack' ),
						),
						'toggle'	=> array(
							'grid'		=> array(
								'fields'	=> array( 'grid_columns', 'spacing' ),
							),
							'slider'	=> array(
								'sections'	=> array( 'slider', 'arrow_nav', 'dot_nav' ),
							),
						),
					),
					'order'         => array(
						'type'          => 'select',
						'label'         => __( 'Order', 'bb-powerpack' ),
						'default'       => 'asc',
						'options'       => array(
							'asc'			=> __( 'Ascending', 'bb-powerpack' ),
							'desc'         	=> __( 'Descending', 'bb-powerpack' ),
							'random'		=> __( 'Random', 'bb-powerpack' ),
						),
					),
					'adaptive_height'   => array(
						'type'              => 'pp-switch',
						'label'             => __('Fixed Height', 'bb-powerpack'),
						'default'           => 'yes',
						'options'           => array(
							'yes'               => __('Yes', 'bb-powerpack'),
							'no'                => __('No', 'bb-powerpack')
						),
						'help'              => __('Fix height to the tallest item.', 'bb-powerpack')
					),
					'grid_columns'   => array(
						'type'          => 'unit',
						'label' 		=> __( 'Grid Columns', 'bb-powerpack' ),
						'default'		=> '3',
						'slider'  => array(
							'min'  => 1,
							'max'  => 6,
							'step' => 1,
						),
						'responsive' => true,
					),
					'spacing'          => array(
						'type'          => 'unit',
						'label'         => __( 'Spacing', 'bb-powerpack' ),
						'default'       => '20',
						'units'   		=> array( 'px' ),
						'slider'		=> true,
						'responsive'	=> true,
					),
				),
			),
			'slider'       => array( // Section
				'title'         => __( 'Slider Settings', 'bb-powerpack' ), // Section Title
				'fields'        => array( // Section Fields
					'autoplay'         => array(
						'type'          => 'pp-switch',
						'label'         => __( 'Autoplay', 'bb-powerpack' ),
						'default'       => '1',
						'options'       => array(
							'1'             => __( 'Yes', 'bb-powerpack' ),
							'0'             => __( 'No', 'bb-powerpack' )
						),
					),
					'pause'         => array(
						'type'          => 'unit',
						'label'         => __('Autoplay Speed', 'bb-powerpack'),
						'default'       => '4',
						'units'     	=> array( 'seconds' ),
					),
					'hover_pause'         => array(
						'type'          => 'pp-switch',
						'label'         => __('Pause on hover', 'bb-powerpack'),
						'default'       => '1',
						'help'          => __('Pause when mouse hovers over slider.', 'bb-powerpack'),
						'options'       => array(
							'1'             => __('Yes', 'bb-powerpack'),
							'0'             => __('No', 'bb-powerpack'),
						),
					),
					'loop'         => array(
						'type'          => 'pp-switch',
						'label'         => __('Loop', 'bb-powerpack'),
						'default'       => '1',
						'options'       => array(
							'1'             => __('Yes', 'bb-powerpack'),
							'0'             => __('No', 'bb-powerpack'),
						),
					),
					'transition'    => array(
						'type'          => 'pp-switch',
						'label'         => __('Mode', 'bb-powerpack'),
						'default'       => 'horizontal',
						'options'       => array(
							'horizontal'    => _x( 'Horizontal', 'Transition type.', 'bb-powerpack' ),
							'vertical'    => _x( 'Vertical', 'Transition type.', 'bb-powerpack' ),
							'fade'          => __( 'Fade', 'bb-powerpack' )
						),
					),
					'speed'         => array(
						'type'          => 'unit',
						'label'         => __('Transition Speed', 'bb-powerpack'),
						'default'       => '0.5',
						'units'     	=> array( 'seconds' ),
					),
					'carousel'         => array(
						'type'          => 'pp-switch',
						'label'         => __('Carousel', 'bb-powerpack'),
						'default'       => '0',
                        'options'       => array(
							'1'             => __('Yes', 'bb-powerpack'),
                            '0'             => __('No', 'bb-powerpack')
						),
                        'toggle'        => array(
							'1'         => array(
								'fields'        => array('min_slides', 'move_slides', 'max_slides', 'slide_width', 'slide_margin')
							)
						)
					),
                    'min_slides'         => array(
						'type'          => 'unit',
						'label'         => __('Minimum Slides', 'bb-powerpack'),
						'default'       => '1',
						'units'			=> array( 'px' ),
						'slider'		=> true,
						'responsive'	=> true,
                        'help'          => __('The minimum number of slides to be shown.', 'bb-powerpack'),
					),
                    // 'max_slides'         => array(
					// 	'type'          => 'unit',
					// 	'label'         => __('Maximum Slides', 'bb-powerpack'),
					// 	'default'       => '1',
					// 	'units'			=> array( 'px' ),
                    //     'slider'		=> true,
					// 	'responsive'	=> true,
                    //     'help'          => __('The maximum number of slides to be shown.', 'bb-powerpack'),
					// ),
                    'move_slides'         => array(
						'type'          => 'unit',
						'label'         => __('Move Slides', 'bb-powerpack'),
						'default'       => '1',
						'units'			=> array( 'px' ),
                        'slider'		=> true,
						'responsive'	=> true,
                        'help'          => __('The number of slides to move on transition.', 'bb-powerpack'),
					),
                    // 'slide_width'         => array(
					// 	'type'          => 'unit',
					// 	'label'         => __('Slides Width', 'bb-powerpack'),
					// 	'default'       => '350',
					// 	'units'			=> array( 'px' ),
					// 	'slider'		=> true,
					// 	'responsive'	=> true,
                    //     'help'          => __('The width of each slide. This setting is required for all horizontal carousels!', 'bb-powerpack'),
					// ),
                    'slide_margin'         => array(
						'type'          => 'unit',
						'label'         => __('Slides Margin', 'bb-powerpack'),
						'default'       => '20',
                        'units'			=> array( 'px' ),
						'slider'		=> true,
						'responsive'	=> true,
                        'help'          => __('Margin between each slide.', 'bb-powerpack'),
					),
				)
			),
			'arrow_nav'       => array( // Section
				'title'         => '',
				'fields'        => array( // Section Fields
					'arrows'       => array(
						'type'          => 'pp-switch',
						'label'         => __('Show Arrows', 'bb-powerpack'),
						'default'       => '1',
						'options'       => array(
							'1'             => __('Yes', 'bb-powerpack'),
                            '0'             => __('No', 'bb-powerpack')
						),
						'toggle'        => array(
							'1'         => array(
								'fields'        => array('arrow_color', 'arrow_alignment')
							)
						)
					),
					'arrow_color'       => array(
						'type'          => 'color',
						'label'         => __('Arrow Color', 'bb-powerpack'),
						'default'       => '999999',
						'show_alpha'    => false,
						'show_reset'    => true,
						'connections'	=> array('color'),
						'preview'       => array(
							'type'          => 'css',
							'selector'      => '.pp-testimonials-wrap .owl-theme .owl-nav button svg',
							'property'      => 'color'
						)
					),
					'arrow_bg_color'       => array(
						'type'          => 'color',
						'label'         => __('Arrow Color', 'bb-powerpack'),
						'default'       => '',
						'show_alpha'    => true,
						'show_reset'    => true,
						'connections'	=> array('color'),
						'preview'       => array(
							'type'          => 'css',
							'selector'      => '.pp-testimonials-wrap .owl-theme .owl-nav button',
							'property'      => 'background'
						)
					),
					'arrow_alignment'       => array(
						'type'          => 'align',
						'label'         => __('Arrow Alignment', 'bb-powerpack'),
						'default'       => 'center',
						'preview'       => array(
                            'type'          => 'css',
							'selector'      => '.pp-testimonials-wrap .owl-theme .owl-nav',
							'property'      => 'text-align'
						)
					),
					'arrow_size' => array(
						'type'	=> 'unit',
						'label'	=> __( 'Arrow Size', 'bb-powerpack' ),
						'default' => '20',
						'slider'	=> true,
						'units'	=> array( 'px' ),
						'preview'	=> array(
							'type'		=> 'css',
							'rules'		=> array(
								array(
									'selector' 	=> '.pp-testimonials-wrap .owl-theme .owl-nav button svg',
									'property'	=> 'width',
									'unit'		=> 'px',
								),
								array(
									'selector' 	=> '.pp-testimonials-wrap .owl-theme .owl-nav button svg',
									'property'	=> 'height',
									'unit'		=> 'px',
								),
							),
						),
					),
				)
			),
			'dot_nav'       => array( // Section
				'title'         => '', // Section Title
				'fields'        => array( // Section Fields
					'dots'       => array(
						'type'          => 'pp-switch',
						'label'         => __('Show Dots', 'bb-powerpack'),
						'default'       => '1',
						'options'       => array(
							'1'             => __('Yes', 'bb-powerpack'),
                            '0'             => __('No', 'bb-powerpack'),
						),
						'toggle'        => array(
							'1'         => array(
								'fields'        => array('dot_color', 'active_dot_color')
							)
						)
					),
					'dot_color'       => array(
						'type'          => 'color',
						'label'         => __('Dot Color', 'bb-powerpack'),
						'default'       => '999999',
						'show_alpha'    => true,
						'show_reset'    => true,
						'connections'	=> array('color'),
                        'preview'       => array(
							'type'          => 'css',
							'selector'      => '.pp-testimonials-wrap .bx-wrapper .bx-pager a',
							'property'      => 'background'
						)
					),
					'active_dot_color'       => array(
						'type'          => 'color',
						'label'         => __('Active Dot Color', 'bb-powerpack'),
						'default'       => '999999',
						'show_alpha'    => true,
						'show_reset'    => true,
						'connections'	=> array('color'),
                        'preview'       => array(
							'type'          => 'css',
							'selector'      => '.pp-testimonials-wrap .bx-wrapper .bx-pager a.active',
							'property'      => 'background'
						)
					),
				)
			)
		)
	),
	'testimonials'      => array( // Tab
		'title'         => __('Testimonials', 'bb-powerpack'), // Tab title
		'sections'      => array( // Tab Sections
			'general'       => array( // Section
				'title'         => '', // Section Title
				'fields'        => array( // Section Fields
					'testimonials'     => array(
						'type'          => 'form',
						'label'         => __('Testimonial', 'bb-powerpack'),
						'form'          => 'pp_testimonials_form', // ID from registered form below
						'preview_text'  => 'title', // Name of a field to use for the preview text
						'multiple'      => true
					),
				)
			)
		)
	),
    'layouts'       => array(
        'title'     => __('Layout', 'bb-powerpack'),
        'sections'  => array(
            'layout'       => array( // Section
				'title'         => '', // Section Title
				'fields'        => array( // Section Fields
					'testimonial_layout'     => array(
						'type'          => 'layout',
						'label'         => __('Layout', 'bb-powerpack'),
						'default'		=> 1,
                        'options'        => array(
                            '1'      => BB_POWERPACK_URL . 'modules/pp-testimonials/images/layout-1.jpg',
                            '2'      => BB_POWERPACK_URL . 'modules/pp-testimonials/images/layout-2.jpg',
                            '3'      => BB_POWERPACK_URL . 'modules/pp-testimonials/images/layout-3.jpg',
                            '4'      => BB_POWERPACK_URL . 'modules/pp-testimonials/images/layout-4.jpg',
                            '5'      => BB_POWERPACK_URL . 'modules/pp-testimonials/images/layout-5.jpg',
                        ),
					),
				)
			),
        ),
    ),
    'styles'      => array( // Tab
		'title'         => __('Style', 'bb-powerpack'), // Tab title
		'sections'      => array( // Tab Sections
            'box_borders'        => array(
                'title'     => __('Content Box', 'bb-powerpack'),
                'fields'        => array( // Section Fields
					'layout_4_content_bg'    => array(
                        'type'      => 'color',
                        'label'     => __('Background Color', 'bb-powerpack'),
						'show_reset'    => true,
						'show_alpha'    => true,
						'connections'	=> array('color'),
                        'preview'       => array(
                            'type'          => 'css',
                            'rules'     => array(
                                array(
                                    'selector'      => '.pp-testimonials .layout-1 .pp-content-wrapper',
                                    'property'      => 'background-color',
                                ),
                                array(
                                    'selector'      => '.pp-testimonials .layout-2 .pp-content-wrapper',
                                    'property'      => 'background-color',
                                ),
                                array(
                                    'selector'      => '.pp-testimonials .layout-3 .pp-content-wrapper',
                                    'property'      => 'background-color',
                                ),
                                array(
                                    'selector'      => '.pp-testimonials .layout-4 .layout-4-content',
                                    'property'      => 'background-color',
                                ),
                                array(
                                    'selector'      => '.pp-testimonials .layout-5 .pp-content-wrapper',
                                    'property'      => 'background-color',
                                ),
                                array(
                                    'selector'      => '.pp-testimonials .pp-arrow-top',
                                    'property'      => 'border-bottom-color',
                                ),
                                array(
                                    'selector'      => '.pp-testimonials .pp-arrow-bottom',
                                    'property'      => 'border-top-color',
                                ),
                                array(
                                    'selector'      => '.pp-testimonials .pp-arrow-left',
                                    'property'      => 'border-right-color',
                                ),
                            ),
                        )
                    ),
                    'box_border'	=> array(
						'type'          => 'border',
						'label'         => __( 'Border', 'bb-powerpack' ),
						'responsive'	=> true,
						'preview'   	=> array(
                            'type'  		=> 'css',
                            'selector'  	=> '.pp-testimonial.layout-1 .pp-content-wrapper, .pp-testimonial.layout-2 .pp-content-wrapper, .pp-testimonial.layout-3 .pp-content-wrapper, .pp-testimonial.layout-4 .layout-4-content, .pp-testimonial.layout-5 .pp-content-wrapper',
                            'property'  	=> 'border',
                        ),
					),
                    'show_arrow'    => array(
                        'type'      => 'pp-switch',
                        'default'   => 'no',
                        'label'     => __('Show Content Indicator', 'bb-powerpack'),
                        'options'   => array(
                            'yes'    => __('Yes', 'bb-powerpack'),
                            'no'    => __('No', 'bb-powerpack'),
                        ),
                    ),
				),
            ),
            'borders'        => array(
                'title'     => __('Image Box', 'bb-powerpack'),
                'fields'        => array( // Section Fields
                    'image_size'    => array(
                        'type'          => 'unit',
                        'label'         => __('Image Size', 'bb-powerpack'),
                        'default'       => 100,
                        'units'   		=> array ('px' ),
						'slider'		=> true
                    ),
                    'image_border'	=> array(
						'type'          => 'border',
						'label'         => __( 'Border', 'bb-powerpack' ),
						'responsive'	=> true,
						'preview'   	=> array(
                            'type'  		=> 'css',
                            'selector'  	=> '.pp-testimonials-image img',
                            'property'  	=> 'border',
                        ),
					),
				)
            ),
		)
	),
    'typography'                => array(
        'title'                     => __('Typography', 'bb-powerpack'),
        'sections'                  => array(
            'heading_fonts'             => array(
                'title'                     => __('Heading', 'bb-powerpack'),
                'fields'                    => array( // Section Fields
                    'heading_typography'	=> array(
						'type'			=> 'typography',
						'label'			=> __('Typography', 'bb-powerpack'),
						'responsive'  	=> true,
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-testimonials-heading',
						),
					),
                    'heading_color'    => array(
						'type'          => 'color',
						'label'         => __('Color', 'bb-powerpack'),
						'show_reset'    => true,
						'connections'	=> array('color'),
                        'preview'       => array(
                            'type'          => 'css',
                            'selector'      => '.pp-testimonials-heading',
                            'property'      => 'color',
                        )
					),
                )
            ),
            'title_fonts'       => array(
                'title'             => __('Client Name', 'bb-powerpack'),
                'fields'            => array(
                    'title_typography'	=> array(
						'type'			=> 'typography',
						'label'			=> __('Typography', 'bb-powerpack'),
						'responsive'  	=> true,
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-testimonials-title',
						),
					),
                    'title_color'    => array(
						'type'          => 'color',
						'label'         => __('Color', 'bb-powerpack'),
						'show_reset'    => true,
						'connections'	=> array('color'),
                        'preview'       => array(
                            'type'          => 'css',
                            'selector'      => '.pp-testimonials-title',
                            'property'      => 'color',
                        )
					),
                    'title_margin'      => array(
                        'type'              => 'pp-multitext',
                        'label'             => __('Margin', 'bb-powerpack'),
                        'description'       => 'px',
                        'default'           => array(
                            'top'               => '',
                            'bottom'            => '',
                        ),
                        'options'           => array(
                            'top'               => array(
                                'placeholder'       => __('Top', 'bb-powerpack'),
                                'tooltip'           => __('Top', 'bb-powerpack'),
                                'icon'              => 'fa-long-arrow-up',
                                'preview'           => array(
                                    'selector'          => '.pp-testimonials-title',
                                    'property'          => 'margin-top',
                                    'unit'              => 'px'
                                ),
                            ),
                            'bottom'            => array(
                                'placeholder'       => __('Bottom', 'bb-powerpack'),
                                'tooltip'           => __('Bottom', 'bb-powerpack'),
                                'icon'              => 'fa-long-arrow-down',
                                'preview'           => array(
                                    'selector'          => '.pp-testimonials-title',
                                    'property'          => 'margin-bottom',
                                    'unit'              => 'px'
                                ),
                            )
                        )
                    ),
                )
            ),
            'subtitle_fonts'        => array(
                'title'                 => __('Client Profile', 'bb-powerpack'),
                'fields'                => array(
                    'subtitle_typography'	=> array(
						'type'			=> 'typography',
						'label'			=> __('Typography', 'bb-powerpack'),
						'responsive'  	=> true,
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-testimonials-subtitle',
						),
					),
                    'subtitle_color'    => array(
						'type'          => 'color',
						'label'         => __('Color', 'bb-powerpack'),
						'show_reset'    => true,
						'connections'	=> array('color'),
                        'preview'       => array(
                            'type'          => 'css',
                            'selector'      => '.pp-testimonials-subtitle',
                            'property'      => 'color',
                        )
					),
                    'subtitle_margin'   => array(
                        'type'              => 'pp-multitext',
                        'label'             => __('Margin', 'bb-powerpack'),
                        'description'       => 'px',
                        'default'           => array(
                            'top'               => '',
                            'bottom'            => '',
                        ),
                        'options'           => array(
                            'top'               => array(
                                'placeholder'       => __('Top', 'bb-powerpack'),
                                'tooltip'           => __('Top', 'bb-powerpack'),
                                'icon'              => 'fa-long-arrow-up',
                                'preview'           => array(
                                    'selector'          => '.pp-testimonials-subtitle',
                                    'property'          => 'margin-top',
                                    'unit'              => 'px'
                                ),
                            ),
                            'bottom'            => array(
                                'placeholder'       => __('Bottom', 'bb-powerpack'),
                                'tooltip'           => __('Bottom', 'bb-powerpack'),
                                'icon'              => 'fa-long-arrow-down',
                                'preview'           => array(
                                    'selector'          => '.pp-testimonials-subtitle',
                                    'property'          => 'margin-bottom',
                                    'unit'              => 'px'
                                ),
                            )
                        )
                    ),
                )
            ),
            'content_fonts'     => array(
                'title'             => __('Content', 'bb-powerpack'),
                'fields'            => array(
                    'text_typography'	=> array(
						'type'			=> 'typography',
						'label'			=> __('Typography', 'bb-powerpack'),
						'responsive'  	=> true,
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-testimonials-content',
						),
					),
                    'text_color'    => array(
						'type'          => 'color',
						'label'         => __('Color', 'bb-powerpack'),
						'show_reset'    => true,
						'connections'	=> array('color'),
                        'preview'       => array(
                            'type'          => 'css',
                            'selector'      => '.pp-testimonials-content',
                            'property'      => 'color',
                        )
					),
                    'content_margin'      => array(
                        'type'              => 'pp-multitext',
                        'label'             => __('Margin', 'bb-powerpack'),
                        'description'       => 'px',
                        'default'           => array(
                            'top'               => '',
                            'bottom'            => '',
                        ),
                        'options'           => array(
                            'top'               => array(
                                'placeholder'       => __('Top', 'bb-powerpack'),
                                'tooltip'           => __('Top', 'bb-powerpack'),
                                'icon'              => 'fa-long-arrow-up',
                                'preview'           => array(
                                    'selector'          => '.pp-testimonials-content',
                                    'property'          => 'margin-top',
                                    'unit'              => 'px'
                                ),
                            ),
                            'bottom'            => array(
                                'placeholder'       => __('Bottom', 'bb-powerpack'),
                                'tooltip'           => __('Bottom', 'bb-powerpack'),
                                'icon'              => 'fa-long-arrow-down',
                                'preview'           => array(
                                    'selector'          => '.pp-testimonials-content',
                                    'property'          => 'margin-bottom',
                                    'unit'              => 'px'
                                ),
                            )
                        )
                    ),
                ),
            ),
        )
    )
));


/**
 * Register a settings form to use in the "form" field type above.
 */
FLBuilder::register_settings_form('pp_testimonials_form', array(
	'title' => __('Add Testimonial', 'bb-powerpack'),
	'tabs'  => array(
		'general'      => array( // Tab
			'title'         => __('General', 'bb-powerpack'), // Tab title
			'sections'      => array( // Tab Sections
                'title'          => array(
                    'title'      => '',
                    'fields'     => array(
                        'title'     => array(
                            'type'          => 'text',
                            'label'         => __('Client Name', 'bb-powerpack'),
                            'connections'   => array( 'string', 'html', 'url' ),
                        ),
                        'subtitle'     => array(
                            'type'          => 'text',
                            'label'         => __('Client Profile', 'bb-powerpack'),
                            'connections'   => array( 'string', 'html', 'url' ),
                        ),
                        'photo'     => array(
                            'type'          => 'photo',
                            'label'         => __('Client Photo', 'bb-powerpack'),
                            'show_remove'   => true,
                            'connections'   => array( 'photo' ),
                        ),
                    ),
                ),
                'content'       => array( // Section
					'title'         => __('Content', 'bb-powerpack'), // Section Title
					'fields'        => array( // Section Fields
						'testimonial'          => array(
							'type'          => 'editor',
							'label'         => '',
                            'connections'   => array( 'string', 'html', 'url' ),
						)
					)
				),
			)
		)
	)
));
