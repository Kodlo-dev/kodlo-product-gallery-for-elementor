<?php
/**
 * Desktop view controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_layout', array(
	'label' => esc_html__( 'Desktop view settings', 'kodlo-product-gallery-for-elementor' ),
	'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
) );

$this->add_control( 'enable_desktop_view', array(
	'label'        => esc_html__( 'Enable Desktop View', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
	'render_type'  => 'template',
	'condition'    => array( 'enable_mobile_view' => 'yes' ),
) );

$kodlo_gallery_columns = range( 1, 10 );
$kodlo_gallery_columns = array_combine( $kodlo_gallery_columns, $kodlo_gallery_columns );

$this->add_control( 'desktop_columns', array(
	'label'   => esc_html__( 'Columns', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => $kodlo_gallery_columns,
	'default' => '2',
	'render_type' => 'template',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-columns: {{VALUE}};',
	),
	'condition' => array( 'enable_desktop_view' => 'yes' ),
) );

$this->add_control( 'last_image_full_width', array(
	'label'   => esc_html__( 'Image layout', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'default' => 'no',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-stretched-image: {{VALUE}};',
	),
	'render_type' => 'template',
	'options' => array(
		'no'    => esc_html__( 'No Stretch', 'kodlo-product-gallery-for-elementor' ),
		'last'  => esc_html__( 'Stretch Last Image', 'kodlo-product-gallery-for-elementor' ),
		'first' => esc_html__( 'Stretch First Image', 'kodlo-product-gallery-for-elementor' ),
	),
	'description' => esc_html__(
		'Span the selected image across all columns if the image count is not a multiple of columns.',
		'kodlo-product-gallery-for-elementor'
	),
	'condition'   => array( 'enable_desktop_view' => 'yes', 'desktop_columns!' => '1' ),
) );


$this->add_control( 'max_images', array(
	'label'       => esc_html__( 'Max images display', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::NUMBER,
	'min'         => 0,
	'default'     => '',
	'description' => esc_html__( 'Use 0 or leave empty to show all images.', 'kodlo-product-gallery-for-elementor' ),
	'render_type' => 'template',
	'condition'   => array( 'enable_desktop_view' => 'yes' ),
) );

$this->end_controls_section();
