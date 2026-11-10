<?php
/**
 * Desktop style controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_style_desktop', array(
	'label' => esc_html__( 'Desktop view style', 'kodlo-product-gallery-for-elementor' ),
	'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
) );

$this->add_control( 'object_fit', array(
	'label'   => esc_html__( 'Object fit', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => $object_fit_options,
	'default' => 'cover',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-object-fit: {{VALUE}};',
	),
) );

$this->add_control( 'aspect_ratio', array(
	'label'   => esc_html__( 'Aspect Ratio', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => $aspect_ratio_options,
	'default' => '5 / 6',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-aspect-ratio: {{VALUE}};',
	),
) );

$this->add_control( 'grid_image_background', array(
	'label'     => esc_html__( 'Image Background', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-image-background: {{VALUE}};',
	),
) );

$this->add_control( 'item_gap', array(
	'label'      => esc_html__( 'Gap Between Items', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 10 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-main-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
	),
) );

$this->add_control( 'main_border_radius', array(
	'label'      => esc_html__( 'Image Border Radius', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
) );

$this->add_control( 'desktop_image_effect', array(
	'label'   => esc_html__( 'Image Effect', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => array(
		'none'      => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
		'zoom'      => esc_html__( 'Zoom', 'kodlo-product-gallery-for-elementor' ),
		'magnifier' => esc_html__( 'Magnifier', 'kodlo-product-gallery-for-elementor' ),
	),
	'default' => 'none',
) );

$this->add_control( 'desktop_magnifier_scope', array(
	'label'     => esc_html__( 'Magnifier images', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'all'   => esc_html__( 'All images', 'kodlo-product-gallery-for-elementor' ),
		'first' => esc_html__( 'First image only', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'all',
	'condition' => array( 'desktop_image_effect' => 'magnifier' ),
) );

$this->add_control( 'desktop_zoom_scale', array(
	'label'      => esc_html__( 'Zoom Factor', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 3, 'step' => 0.05 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 1.08 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-zoom-scale: {{SIZE}};',
	),
	'condition'  => array( 'desktop_image_effect' => 'zoom' ),
) );

$this->add_control( 'desktop_magnifier_zoom', array(
	'label'      => esc_html__( 'Zoom Factor', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 3, 'step' => 0.05 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 2 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-magnifier-zoom: {{SIZE}};',
	),
	'condition'  => array( 'desktop_image_effect' => 'magnifier' ),
) );

$this->add_control( 'desktop_magnifier_size', array(
	'label'      => esc_html__( 'Lens Size', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 50, 'max' => 350 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 160 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-magnifier-size: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'desktop_image_effect' => 'magnifier' ),
) );

$this->add_control( 'desktop_magnifier_border_style', array(
	'label'     => esc_html__( 'Lens Border Style', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'none'   => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
		'solid'  => esc_html__( 'Solid', 'kodlo-product-gallery-for-elementor' ),
		'dashed' => esc_html__( 'Dashed', 'kodlo-product-gallery-for-elementor' ),
		'dotted' => esc_html__( 'Dotted', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'solid',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-magnifier-border-style: {{VALUE}};',
	),
	'condition' => array( 'desktop_image_effect' => 'magnifier' ),
) );

$this->add_control( 'desktop_magnifier_border_width', array(
	'label'      => esc_html__( 'Lens Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 2 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-magnifier-border-width: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'desktop_image_effect' => 'magnifier', 'desktop_magnifier_border_style!' => 'none' ),
) );

$this->add_control( 'desktop_magnifier_border_color', array(
	'label'     => esc_html__( 'Lens Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-magnifier-border-color: {{VALUE}};',
	),
	'condition' => array( 'desktop_image_effect' => 'magnifier', 'desktop_magnifier_border_style!' => 'none' ),
) );

$this->add_control( 'desktop_magnifier_border_radius', array(
	'label'      => esc_html__( 'Lens Border Radius', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'default'    => array(
		'top'      => '50',
		'right'    => '50',
		'bottom'   => '50',
		'left'     => '50',
		'unit'     => '%',
		'isLinked' => true,
	),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-magnifier-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
	'condition'  => array( 'desktop_image_effect' => 'magnifier' ),
) );

$this->add_control( 'heading_last_image_style', array(
	'label'     => esc_html__( 'Stretched Image Style', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
	'condition' => array( 'last_image_full_width!' => 'no' ),
) );

$this->add_control( 'last_image_object_fit', array(
	'label'     => esc_html__( 'Object fit', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => $object_fit_options,
	'default'   => 'cover',
	'render_type' => 'template',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-last-image-object-fit: {{VALUE}};',
	),
	'condition' => array( 'last_image_full_width!' => 'no' ),
) );

$this->add_control( 'last_image_aspect_ratio', array(
	'label'     => esc_html__( 'Aspect Ratio', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => $aspect_ratio_options,
	'default'   => '16 / 9',
	'render_type' => 'template',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-last-image-aspect-ratio: {{VALUE}};',
	),
	'condition' => array( 'last_image_full_width!' => 'no' ),
) );

$this->add_control( 'heading_grid_item_border', array(
	'label'     => esc_html__( 'Grid Items Border', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
) );

$this->start_controls_tabs( 'tabs_grid_border' );

$this->start_controls_tab( 'tab_grid_border_normal', array(
	'label' => esc_html__( 'Normal', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'grid_border_style', array(
	'label'     => esc_html__( 'Border Style', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'none'   => 'None',
		'solid'  => 'Solid',
		'double' => 'Double',
		'dotted' => 'Dotted',
		'dashed' => 'Dashed',
	),
	'default'   => 'none',
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-border-style: {{VALUE}};' ),
) );

$this->add_control( 'grid_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 20 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 1 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-border-width: {{SIZE}}{{UNIT}};' ),
	'condition'  => array( 'grid_border_style!' => 'none' ),
) );

$this->add_control( 'grid_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-border-color: {{VALUE}};' ),
	'condition' => array( 'grid_border_style!' => 'none' ),
) );

$this->end_controls_tab();

$this->start_controls_tab( 'tab_grid_border_hover', array(
	'label' => esc_html__( 'Hover', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'grid_border_hover_style', array(
	'label'     => esc_html__( 'Border Style', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'none'   => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
		'solid'  => esc_html__( 'Solid', 'kodlo-product-gallery-for-elementor' ),
		'double' => esc_html__( 'Double', 'kodlo-product-gallery-for-elementor' ),
		'dotted' => esc_html__( 'Dotted', 'kodlo-product-gallery-for-elementor' ),
		'dashed' => esc_html__( 'Dashed', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'none',
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-border-hover-style: {{VALUE}};' ),
) );

$this->add_control( 'grid_border_hover_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 20 ) ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-border-hover-width: {{SIZE}}{{UNIT}};' ),
	'condition'  => array( 'grid_border_hover_style!' => 'none' ),
) );

$this->add_control( 'grid_border_hover_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-desktop-border-hover-color: {{VALUE}};' ),
	'condition' => array( 'grid_border_hover_style!' => 'none' ),
) );

$this->end_controls_tab();

$this->end_controls_tabs();

$this->end_controls_section();
