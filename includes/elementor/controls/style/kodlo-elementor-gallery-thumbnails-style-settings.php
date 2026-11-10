<?php
/**
 * Thumbnail style controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_style_thumbnails', array(
	'label'     => esc_html__( 'Thumbnails style', 'kodlo-product-gallery-for-elementor' ),
	'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
	'condition' => array(
		'enable_mobile_view' => 'yes',
		'show_thumbnails'    => 'yes',
	),
) );

$this->add_control( 'thumb_object_fit', array(
	'label'   => esc_html__( 'Object fit', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => $object_fit_options,
	'default'   => 'cover',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumb-object-fit: {{VALUE}};',
	),
) );

$this->add_control( 'thumb_aspect_ratio', array(
	'label'   => esc_html__( 'Aspect Ratio', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => $aspect_ratio_options,
	'default'   => '1 / 1',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumb-aspect-ratio: {{VALUE}};',
	),
) );

$this->add_control( 'thumb_gap', array(
	'label'      => esc_html__( 'Gap Between Thumbnails', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 10 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumb-gap: {{SIZE}}{{UNIT}};',
	),
) );

$this->add_control( 'thumb_size', array(
	'label'      => esc_html__( 'Thumbnail Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 40, 'max' => 160 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 80 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumb-size: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'thumbs_per_view' => 'auto' ),
) );

$this->add_control( 'thumb_border_radius', array(
	'label'      => esc_html__( 'Thumbnail Border Radius', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'default'    => array(
		'top'      => '0',
		'right'    => '0',
		'bottom'   => '0',
		'left'     => '0',
		'unit'     => 'px',
		'isLinked' => true,
	),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide' =>
			'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important; overflow: hidden;',
	),
) );

$this->add_control( 'heading_thumbs_states', array(
	'label'     => esc_html__( 'States Style', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
) );

$this->start_controls_tabs( 'tabs_thumbs_style' );

$this->start_controls_tab( 'tab_thumbs_inactive', array(
	'label' => esc_html__( 'Inactive', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'thumb_background', array(
	'label'     => esc_html__( 'Background', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumb-background: {{VALUE}};',
	),
) );

$this->add_control( 'thumb_opacity', array(
	'label'     => esc_html__( 'Opacity', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SLIDER,
	'range'     => array( 'px' => array( 'min' => 0.1, 'max' => 1, 'step' => 0.05 ) ),
	'default'   => array( 'size' => 0.7 ),
	'selectors' => array( '{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide' => 'opacity: {{SIZE}} !important;' ),
) );

$this->add_control( 'thumb_border_style', array(
	'label'     => esc_html__( 'Border Style', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'none'   => 'None',
		'solid'  => 'Solid',
		'double' => 'Double',
		'dotted' => 'Dotted',
		'dashed' => 'Dashed',
	),
	'default'   => 'solid',
	'selectors' => array( '{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide' => 'border-style: {{VALUE}} !important;' ),
) );

$this->add_control( 'thumb_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'default'    => array(
		'top'      => '0',
		'right'    => '0',
		'bottom'   => '2',
		'left'     => '0',
		'unit'     => 'px',
		'isLinked' => false,
	),
	'selectors'  => array( '{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
	'condition'  => array( 'thumb_border_style!' => 'none' ),
) );

$this->add_control( 'thumb_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#dddddd',
	'selectors' => array( '{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide' => 'border-color: {{VALUE}} !important;' ),
	'condition' => array( 'thumb_border_style!' => 'none' ),
) );

$this->end_controls_tab();

$this->start_controls_tab( 'tab_thumbs_active', array(
	'label' => esc_html__( 'Active', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'thumb_active_background', array(
	'label'     => esc_html__( 'Background', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumb-active-background: {{VALUE}};',
	),
) );

$this->add_control( 'thumb_active_opacity', array(
	'label'     => esc_html__( 'Opacity', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SLIDER,
	'range'     => array( 'px' => array( 'min' => 0.1, 'max' => 1, 'step' => 0.05 ) ),
	'default'   => array( 'size' => 1.0 ),
	'selectors' => array( '{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide-thumb-active' => 'opacity: {{SIZE}} !important;' ),
) );

$this->add_control( 'thumb_active_border_style', array(
	'label'     => esc_html__( 'Border Style', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'none'   => 'None',
		'solid'  => 'Solid',
		'double' => 'Double',
		'dotted' => 'Dotted',
		'dashed' => 'Dashed',
	),
	'default'   => 'solid',
	'selectors' => array( '{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide-thumb-active' => 'border-style: {{VALUE}} !important;' ),
) );

$this->add_control( 'thumb_active_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'default'    => array(
		'top'      => '0',
		'right'    => '0',
		'bottom'   => '2',
		'left'     => '0',
		'unit'     => 'px',
		'isLinked' => false,
	),
	'selectors'  => array( '{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide-thumb-active' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
	'condition'  => array( 'thumb_active_border_style!' => 'none' ),
) );

$this->add_control( 'thumb_active_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#000000',
	'selectors' => array( '{{WRAPPER}} .kodlo-thumbs-slider .swiper-slide-thumb-active' => 'border-color: {{VALUE}} !important;' ),
	'condition' => array( 'thumb_active_border_style!' => 'none' ),
) );

$this->end_controls_tab();

$this->end_controls_tabs();

$this->end_controls_section();
