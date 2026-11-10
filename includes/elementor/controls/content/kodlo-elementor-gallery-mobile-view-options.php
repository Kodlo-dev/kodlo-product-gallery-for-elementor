<?php
/**
 * Mobile view controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_mobile_view_options', array(
	'label' => esc_html__( 'Mobile view settings', 'kodlo-product-gallery-for-elementor' ),
	'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
) );

$this->add_control( 'enable_mobile_view', array(
	'label'        => esc_html__( 'Enable Mobile View', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
	'render_type'  => 'template',
	'condition'    => array( 'enable_desktop_view' => 'yes' ),
) );

$this->add_control( 'mobile_breakpoint', array(
	'label'     => esc_html__( 'Breakpoint', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => $breakpoint_options,
	'default'   => $default_mobile_breakpoint,
	'condition' => array(
		'enable_desktop_view' => 'yes',
		'enable_mobile_view'  => 'yes',
	),
	'render_type' => 'template',
) );

$this->add_control( 'loop_mobile', array(
	'label'        => esc_html__( 'Infinite Loop', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'no',
	'description'  => esc_html__( 'Enable infinite looping for the mobile main slider.', 'kodlo-product-gallery-for-elementor' ),
	'condition'    => array( 'enable_mobile_view' => 'yes' ),
) );


$this->add_control( 'max_images_mobile', array(
	'label'       => esc_html__( 'Max images display', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::NUMBER,
	'min'         => 0,
	'default'     => '',
	'description' => esc_html__( 'Use 0 or leave empty to show all images in the mobile gallery.', 'kodlo-product-gallery-for-elementor' ),
	'render_type' => 'template',
	'condition'   => array( 'enable_mobile_view' => 'yes' ),
) );

$this->end_controls_section();
