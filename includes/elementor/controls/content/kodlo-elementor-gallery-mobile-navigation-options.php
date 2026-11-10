<?php
/**
 * Mobile navigation controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_mobile_navigation', array(
	'label'     => esc_html__( 'Navigation settings', 'kodlo-product-gallery-for-elementor' ),
	'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
	'condition' => array( 'enable_mobile_view' => 'yes' ),
) );

$this->add_control( 'show_pagination', array(
	'label'        => esc_html__( 'Pagination Dots', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
	'condition'    => array( 'enable_mobile_view' => 'yes' ),
) );

$this->add_control( 'max_visible_dots', array(
	'label'       => esc_html__( 'Maximum Visible Dots', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::SLIDER,
	'range'       => array(
		'px' => array( 'min' => 2, 'max' => 10, 'step' => 1 ),
	),
	'default'     => array( 'size' => 5 ),
	'render_type' => 'template',
	'condition'   => array( 'enable_mobile_view' => 'yes', 'show_pagination' => 'yes' ),
	'description' => esc_html__( 'Limits the visible pagination window as the active slide changes.', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'show_main_nav', array(
	'label'        => esc_html__( 'Main Slider Arrows', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'no',
	'separator'    => 'before',
	'condition'    => array( 'enable_mobile_view' => 'yes' ),
) );

$this->add_control( 'main_nav_icon_prev', array(
	'label'     => esc_html__( 'Prev Icon', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::ICONS,
	'default'   => array( 'value' => 'fas fa-chevron-left', 'library' => 'fa-solid' ),
	'condition' => array( 'enable_mobile_view' => 'yes', 'show_main_nav' => 'yes' ),
) );

$this->add_control( 'main_nav_icon_next', array(
	'label'     => esc_html__( 'Next Icon', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::ICONS,
	'default'   => array( 'value' => 'fas fa-chevron-right', 'library' => 'fa-solid' ),
	'condition' => array( 'enable_mobile_view' => 'yes', 'show_main_nav' => 'yes' ),
) );

$this->add_control( 'show_thumbs_nav', array(
	'label'        => esc_html__( 'Thumbnails Arrows', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'no',
	'separator'    => 'before',
	'condition'    => array( 'enable_mobile_view' => 'yes', 'show_thumbnails' => 'yes' ),
) );

$this->add_control( 'thumbs_nav_icon_prev', array(
	'label'     => esc_html__( 'Prev Icon', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::ICONS,
	'default'   => array( 'value' => 'fas fa-chevron-left', 'library' => 'fa-solid' ),
	'condition' => array( 'enable_mobile_view' => 'yes', 'show_thumbnails' => 'yes', 'show_thumbs_nav' => 'yes' ),
) );

$this->add_control( 'thumbs_nav_icon_next', array(
	'label'     => esc_html__( 'Next Icon', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::ICONS,
	'default'   => array( 'value' => 'fas fa-chevron-right', 'library' => 'fa-solid' ),
	'condition' => array( 'enable_mobile_view' => 'yes', 'show_thumbnails' => 'yes', 'show_thumbs_nav' => 'yes' ),
) );

$this->end_controls_section();
