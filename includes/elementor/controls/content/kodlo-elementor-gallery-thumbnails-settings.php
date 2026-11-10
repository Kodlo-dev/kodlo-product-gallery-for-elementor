<?php
/**
 * Thumbnail content controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_thumbnails_settings', array(
	'label'     => esc_html__( 'Thumbnails settings', 'kodlo-product-gallery-for-elementor' ),
	'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
	'condition' => array( 'enable_mobile_view' => 'yes' ),
) );

$this->add_control( 'show_thumbnails', array(
	'label'        => esc_html__( 'Show Thumbnails', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
	'render_type'  => 'template',
) );

$this->add_control( 'thumbs_per_view', array(
	'label'       => esc_html__( 'Thumbnails Per View', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::SELECT,
	'options'     => array(
		'auto' => 'Auto',
		'2'    => '2',
		'3'    => '3',
		'4'    => '4',
		'5'    => '5',
	),
	'default'     => '4',
	'selectors'   => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumbs-per-view: {{VALUE}};',
	),
	'render_type' => 'template',
	'condition'   => array( 'show_thumbnails' => 'yes' ),
) );

$this->end_controls_section();
