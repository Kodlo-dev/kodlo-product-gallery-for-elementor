<?php
/**
 * General content controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_gallery_settings', array(
	'label' => esc_html__( 'General settings', 'kodlo-product-gallery-for-elementor' ),
	'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
) );

$this->add_group_control( \Elementor\Group_Control_Image_Size::get_type(), array(
	'name'    => 'gallery_image',
	'default' => 'medium_large',
	// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
	'exclude' => array( 'custom' ),
) );

$this->add_control( 'gallery_display_caption', array(
	'label'   => esc_html__( 'Caption', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'default' => 'none',
	'options' => array(
		'none' => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
		''     => esc_html__( 'Attachment Caption', 'kodlo-product-gallery-for-elementor' ),
	),
) );

$this->add_control( 'gallery_link', array(
	'label'   => esc_html__( 'Link', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'default' => 'none',
	'options' => array(
		'file'       => esc_html__( 'Media File', 'kodlo-product-gallery-for-elementor' ),
		'attachment' => esc_html__( 'Attachment Page', 'kodlo-product-gallery-for-elementor' ),
		'none'       => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
	),
) );

$this->add_control( 'open_lightbox', array(
	'label'     => esc_html__( 'Lightbox', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'default'   => 'yes',
	'options'   => array(
		'default' => esc_html__( 'Default', 'kodlo-product-gallery-for-elementor' ),
		'yes'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
		'no'      => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	),
	'condition' => array( 'gallery_link' => 'file' ),
) );

$this->add_control( 'gallery_order', array(
	'label'   => esc_html__( 'Order By', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'default' => '',
	'options' => array(
		''     => esc_html__( 'Default', 'kodlo-product-gallery-for-elementor' ),
		'rand' => esc_html__( 'Random', 'kodlo-product-gallery-for-elementor' ),
	),
) );

$this->add_control( 'show_variations', array(
	'label'        => esc_html__( 'Product Variations', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
	'render_type'  => 'template',
) );

$this->add_control( 'show_featured_image', array(
	'label'        => esc_html__( 'Featured Image', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
	'render_type'  => 'template',
) );

$this->add_control( 'sale_flash', array(
	'label'        => esc_html__( 'Sale Flash', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'no',
	'render_type'  => 'template',
) );

$this->add_control( 'sale_flash_url', array(
	'label'       => esc_html__( 'Sale Flash URL', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::URL,
	'placeholder' => esc_html__( 'https://example.com', 'kodlo-product-gallery-for-elementor' ),
	'show_external' => true,
	'dynamic'     => array( 'active' => true ),
	'condition'   => array( 'sale_flash' => 'yes' ),
	'render_type' => 'template',
) );

$this->add_control( 'enable_mobile_edge_bleed', array(
	'label'        => esc_html__( 'Swipe Edge Bleed', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'no',
	'render_type'  => 'template',
	'separator'    => 'before',
	'description'  => esc_html__( 'Lets slides move through the side gutter and clip at the screen edge while swiping.', 'kodlo-product-gallery-for-elementor' ),
	'condition'    => array( 'enable_mobile_view' => 'yes' ),
) );

$this->add_control( 'mobile_edge_bleed_gutter', array(
	'label'      => esc_html__( 'Mobile Side Gutter', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array(
		'px' => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
	),
	'default'    => array( 'unit' => 'px', 'size' => 30 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-side-gutter-negative: -{{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'enable_mobile_edge_bleed' => 'yes' ),
	'description' => esc_html__( 'Match this value to the mobile horizontal padding around the gallery.', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'mobile_edge_bleed_transition_duration', array(
	'label'      => esc_html__( 'Reveal Duration', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'ms' ),
	'range'      => array(
		'ms' => array( 'min' => 0, 'max' => 1000, 'step' => 10 ),
	),
	'default'    => array( 'unit' => 'ms', 'size' => 200 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-edge-bleed-reveal-duration: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'enable_mobile_edge_bleed' => 'yes' ),
	'description' => esc_html__( 'Controls how quickly side images appear when swiping starts.', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'mobile_edge_bleed_hide_duration', array(
	'label'      => esc_html__( 'Hide Duration', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'ms' ),
	'range'      => array(
		'ms' => array( 'min' => 0, 'max' => 1000, 'step' => 10 ),
	),
	'default'    => array( 'unit' => 'ms', 'size' => 200 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-edge-bleed-hide-duration: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'enable_mobile_edge_bleed' => 'yes' ),
	'description' => esc_html__( 'Controls how quickly side images disappear when swiping ends.', 'kodlo-product-gallery-for-elementor' ),
) );

// Visible only when edge bleed is on AND at least one nav arrow is in overlay mode.
$this->add_control( 'navigation_edge_bleed_nav_edge', array(
	'label'       => esc_html__( 'Navigation Arrows Edge', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::SELECT,
	'options'     => array(
		'content' => esc_html__( 'Content Edge', 'kodlo-product-gallery-for-elementor' ),
		'screen'  => esc_html__( 'Screen Edge', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'     => 'content',
	'render_type' => 'template',
	'conditions'  => array(
		'relation' => 'and',
		'terms'    => array(
			array( 'name' => 'enable_mobile_view', 'operator' => '===', 'value' => 'yes' ),
			array( 'name' => 'enable_mobile_edge_bleed', 'operator' => '===', 'value' => 'yes' ),
			array(
				'relation' => 'or',
				'terms'    => array(
					array(
						'relation' => 'and',
						'terms'    => array(
							array( 'name' => 'show_main_nav', 'operator' => '===', 'value' => 'yes' ),
							array( 'name' => 'main_nav_position', 'operator' => '===', 'value' => 'overlay' ),
						),
					),
					array(
						'relation' => 'and',
						'terms'    => array(
							array( 'name' => 'show_thumbnails', 'operator' => '===', 'value' => 'yes' ),
							array( 'name' => 'show_thumbs_nav', 'operator' => '===', 'value' => 'yes' ),
							array( 'name' => 'thumbs_nav_position', 'operator' => '===', 'value' => 'overlay' ),
						),
					),
				),
			),
		),
	),
) );

$this->add_control( 'image_loading_optimization', array(
	'label'        => esc_html__( 'Image Loading Optimization', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'no',
	'separator'    => 'before',
) );

$this->add_control( 'desktop_priority_images', array(
	'label'     => esc_html__( 'Desktop Priority Images', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::NUMBER,
	'default'   => 1,
	'min'       => 0,
	'max'       => 10,
	'condition' => array(
		'image_loading_optimization' => 'yes',
		'enable_desktop_view'        => 'yes',
	),
) );

$this->add_control( 'mobile_main_priority_images', array(
	'label'     => esc_html__( 'Mobile Slider Priority Images', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::NUMBER,
	'default'   => 1,
	'min'       => 0,
	'max'       => 10,
	'condition' => array(
		'image_loading_optimization' => 'yes',
		'enable_mobile_view'         => 'yes',
	),
) );

$this->add_control( 'mobile_thumbs_priority_images', array(
	'label'     => esc_html__( 'Mobile Thumbnails Priority Images', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::NUMBER,
	'default'   => 0,
	'min'       => 0,
	'max'       => 20,
	'condition' => array(
		'image_loading_optimization' => 'yes',
		'enable_mobile_view'         => 'yes',
		'show_thumbnails'            => 'yes',
	),
) );

$this->add_control( 'defer_offscreen_images', array(
	'label'        => esc_html__( 'Defer Off-Screen Images', 'kodlo-product-gallery-for-elementor' ),
	'description'  => esc_html__( 'Thumbnails outside the visible row load after the page has loaded or on the first interaction with the gallery, so they do not compete with the first image. When the desktop view is off, mobile slides after the priority images are deferred as well. Images of the desktop grid are never deferred.', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'no',
	'condition'    => array(
		'image_loading_optimization' => 'yes',
		'enable_mobile_view'         => 'yes',
	),
) );

$this->end_controls_section();
