<?php
/**
 * Mobile style controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_style_mobile', array(
	'label'     => esc_html__( 'Mobile view style', 'kodlo-product-gallery-for-elementor' ),
	'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
	'condition' => array( 'enable_mobile_view' => 'yes' ),
) );

$this->add_control( 'mobile_main_object_fit', array(
	'label'   => esc_html__( 'Object fit', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => $object_fit_options,
	'default'   => 'cover',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-object-fit: {{VALUE}};',
	),
) );

$this->add_control( 'mobile_main_aspect_ratio', array(
	'label'   => esc_html__( 'Aspect Ratio', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => $aspect_ratio_options,
	'default'   => '5 / 6',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-aspect-ratio: {{VALUE}};',
	),
) );

$this->add_control( 'mobile_main_background', array(
	'label'     => esc_html__( 'Image Background', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-image-background: {{VALUE}};',
	),
) );

$this->add_control( 'mobile_main_border_style', array(
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
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-border-style: {{VALUE}};',
	),
) );

$this->add_control( 'mobile_main_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 20 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 0 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-border-width: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'mobile_main_border_style!' => 'none' ),
) );

$this->add_control( 'mobile_main_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-border-color: {{VALUE}};',
	),
	'condition' => array( 'mobile_main_border_style!' => 'none' ),
) );

$this->add_control( 'mobile_main_border_hover_color', array(
	'label'     => esc_html__( 'Border Hover Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-border-hover-color: {{VALUE}};',
	),
	'condition' => array( 'mobile_main_border_style!' => 'none' ),
) );

$this->add_control( 'mobile_main_border_radius', array(
	'label'      => esc_html__( 'Image Border Radius', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
) );

$this->add_control( 'mobile_image_effect', array(
	'label'     => esc_html__( 'Image Effect', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'none'      => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
		'zoom'      => esc_html__( 'Zoom', 'kodlo-product-gallery-for-elementor' ),
		'magnifier' => esc_html__( 'Magnifier', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'none',
	'separator' => 'before',
	'condition' => array( 'enable_mobile_view' => 'yes' ),
) );

$this->add_control( 'mobile_magnifier_scope', array(
	'label'     => esc_html__( 'Magnifier images', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'all'   => esc_html__( 'All images', 'kodlo-product-gallery-for-elementor' ),
		'first' => esc_html__( 'First image only', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'all',
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_zoom_scale', array(
	'label'      => esc_html__( 'Zoom Factor', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 3, 'step' => 0.05 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 1.08 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-zoom-scale: {{SIZE}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'zoom' ),
) );

$this->start_controls_tabs( 'tabs_mobile_magnifier', array(
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->start_controls_tab( 'tab_mobile_magnifier_lens', array(
	'label'     => esc_html__( 'Lens', 'kodlo-product-gallery-for-elementor' ),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_zoom', array(
	'label'      => esc_html__( 'Zoom Factor', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 3, 'step' => 0.05 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 2 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-magnifier-zoom: {{SIZE}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_size', array(
	'label'      => esc_html__( 'Lens Size', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 60, 'max' => 500 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 140 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-magnifier-size: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_border_style', array(
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
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-magnifier-border-style: {{VALUE}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_border_width', array(
	'label'      => esc_html__( 'Lens Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 2 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-magnifier-border-width: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier', 'mobile_magnifier_border_style!' => 'none' ),
) );

$this->add_control( 'mobile_magnifier_border_color', array(
	'label'     => esc_html__( 'Lens Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-magnifier-border-color: {{VALUE}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier', 'mobile_magnifier_border_style!' => 'none' ),
) );

$this->add_control( 'mobile_magnifier_border_radius', array(
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
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-mobile-magnifier-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->end_controls_tab();

$this->start_controls_tab( 'tab_mobile_magnifier_button', array(
	'label'     => esc_html__( 'Button', 'kodlo-product-gallery-for-elementor' ),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_icon', array(
	'label'       => esc_html__( 'Button Icon', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::ICONS,
	'default'     => array( 'value' => 'fas fa-search-plus', 'library' => 'fa-solid' ),
	'render_type' => 'template',
	'condition'   => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_active_icon', array(
	'label'       => esc_html__( 'Active Button Icon', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::ICONS,
	'default'     => array( 'value' => 'fas fa-search-minus', 'library' => 'fa-solid' ),
	'render_type' => 'template',
	'condition'   => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_icon_size', array(
	'label'      => esc_html__( 'Icon Size', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 8, 'max' => 80 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 18 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle svg, {{WRAPPER}} .kodlo-magnifier-toggle i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
	),
	'condition'  => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_position', array(
	'label'   => esc_html__( 'Button Position', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => array(
		'top_left'     => esc_html__( 'Top Left', 'kodlo-product-gallery-for-elementor' ),
		'top_right'    => esc_html__( 'Top Right', 'kodlo-product-gallery-for-elementor' ),
		'bottom_left'  => esc_html__( 'Bottom Left', 'kodlo-product-gallery-for-elementor' ),
		'bottom_right' => esc_html__( 'Bottom Right', 'kodlo-product-gallery-for-elementor' ),
	),
	'default' => 'top_right',
	'selectors_dictionary' => array(
		'top_left'     => 'top: 0; right: auto; bottom: auto; left: 0;',
		'top_right'    => 'top: 0; right: 0; bottom: auto; left: auto;',
		'bottom_left'  => 'top: auto; right: auto; bottom: 0; left: 0;',
		'bottom_right' => 'top: auto; right: 0; bottom: 0; left: auto;',
	),
	'selectors' => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle' => '{{VALUE}}',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_padding', array(
	'label'      => esc_html__( 'Padding', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'default'    => array(
		'top'      => '10',
		'right'    => '10',
		'bottom'   => '10',
		'left'     => '10',
		'unit'     => 'px',
		'isLinked' => true,
	),
	'selectors' => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_margin', array(
	'label'      => esc_html__( 'Margin', 'kodlo-product-gallery-for-elementor' ),
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
	'selectors' => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_border_radius', array(
	'label'      => esc_html__( 'Border Radius', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'default'    => array(
		'top'      => '0',
		'right'    => '0',
		'bottom'   => '0',
		'left'     => '20',
		'unit'     => 'px',
		'isLinked' => false,
	),
	'selectors' => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'heading_mobile_magnifier_button_normal', array(
	'label'     => esc_html__( 'Normal', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_icon_color', array(
	'label'     => esc_html__( 'Icon Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle' => 'color: {{VALUE}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_background', array(
	'label'     => esc_html__( 'Background Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => 'rgba(0,0,0,0.58)',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle' => 'background-color: {{VALUE}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_group_control( \Elementor\Group_Control_Border::get_type(), array(
	'name'      => 'mobile_magnifier_button_border',
	'selector'  => '{{WRAPPER}} .kodlo-magnifier-toggle',
	'fields_options' => array(
		'border' => array( 'default' => 'solid' ),
		'width'  => array(
			'default' => array(
				'top'      => 1,
				'right'    => 1,
				'bottom'   => 1,
				'left'     => 1,
				'unit'     => 'px',
				'isLinked' => true,
			),
		),
		'color' => array( 'default' => 'rgba(255,255,255,0.9)' ),
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'heading_mobile_magnifier_button_hover', array(
	'label'     => esc_html__( 'Hover', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

// aria-pressed="true" covers the JS-toggled active (magnifier open) state.
$this->add_control( 'mobile_magnifier_button_hover_icon_color', array(
	'label'     => esc_html__( 'Icon Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle:hover, {{WRAPPER}} .kodlo-magnifier-toggle:focus-visible, {{WRAPPER}} .kodlo-magnifier-toggle[aria-pressed="true"]' => 'color: {{VALUE}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_control( 'mobile_magnifier_button_hover_background', array(
	'label'     => esc_html__( 'Background Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => 'rgba(0,0,0,0.82)',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-magnifier-toggle:hover, {{WRAPPER}} .kodlo-magnifier-toggle:focus-visible, {{WRAPPER}} .kodlo-magnifier-toggle[aria-pressed="true"]' => 'background-color: {{VALUE}};',
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->add_group_control( \Elementor\Group_Control_Border::get_type(), array(
	'name'      => 'mobile_magnifier_button_hover_border',
	'selector'  => '{{WRAPPER}} .kodlo-magnifier-toggle:hover, {{WRAPPER}} .kodlo-magnifier-toggle:focus-visible, {{WRAPPER}} .kodlo-magnifier-toggle[aria-pressed="true"]',
	'fields_options' => array(
		'border' => array( 'default' => 'solid' ),
		'width'  => array(
			'default' => array(
				'top'      => 1,
				'right'    => 1,
				'bottom'   => 1,
				'left'     => 1,
				'unit'     => 'px',
				'isLinked' => true,
			),
		),
		'color' => array( 'default' => 'rgba(255,255,255,0.9)' ),
	),
	'condition' => array( 'enable_mobile_view' => 'yes', 'mobile_image_effect' => 'magnifier' ),
) );

$this->end_controls_tab();
$this->end_controls_tabs();

$this->end_controls_section();
