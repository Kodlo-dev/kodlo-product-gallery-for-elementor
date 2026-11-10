<?php
/**
 * General style controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

// Section is visible when caption display OR sale flash is enabled (OR relation).
$this->start_controls_section( 'section_style_general', array(
	'label' => esc_html__( 'General style', 'kodlo-product-gallery-for-elementor' ),
	'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
	'conditions' => array(
		'relation' => 'or',
		'terms'    => array(
			array( 'name' => 'gallery_display_caption', 'operator' => '===', 'value' => '' ),
			array( 'name' => 'sale_flash', 'operator' => '===', 'value' => 'yes' ),
		),
	),
) );

$this->add_control( 'heading_caption_style', array(
	'label'     => esc_html__( 'Caption', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'condition' => array( 'gallery_display_caption' => '' ),
) );

$this->add_control( 'caption_alignment', array(
	'label'   => esc_html__( 'Alignment', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::CHOOSE,
	'options' => array(
		'start' => array(
			'title' => esc_html__( 'Start', 'kodlo-product-gallery-for-elementor' ),
			'icon'  => 'eicon-text-align-left',
		),
		'center' => array(
			'title' => esc_html__( 'Center', 'kodlo-product-gallery-for-elementor' ),
			'icon'  => 'eicon-text-align-center',
		),
		'end' => array(
			'title' => esc_html__( 'End', 'kodlo-product-gallery-for-elementor' ),
			'icon'  => 'eicon-text-align-right',
		),
	),
	'default'   => 'center',
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-caption' => 'text-align: {{VALUE}};' ),
	'condition' => array( 'gallery_display_caption' => '' ),
) );

$this->add_control( 'caption_color', array(
	'label'     => esc_html__( 'Text Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-caption' => 'color: {{VALUE}};' ),
	'condition' => array( 'gallery_display_caption' => '' ),
) );

$this->add_control( 'caption_background_color', array(
	'label'     => esc_html__( 'Background Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-caption' => 'background-color: {{VALUE}};' ),
	'condition' => array( 'gallery_display_caption' => '' ),
) );

$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
	'name'     => 'caption_typography',
	'selector' => '{{WRAPPER}} .kodlo-gallery-caption',
	'condition' => array( 'gallery_display_caption' => '' ),
) );

$this->add_group_control( \Elementor\Group_Control_Border::get_type(), array(
	'name'      => 'caption_border',
	'selector'  => '{{WRAPPER}} .kodlo-gallery-caption',
	'condition' => array( 'gallery_display_caption' => '' ),
) );

$this->add_control( 'caption_border_radius', array(
	'label'      => esc_html__( 'Border Radius', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-caption' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
	'condition' => array( 'gallery_display_caption' => '' ),
) );

$this->add_control( 'caption_spacing', array(
	'label'      => esc_html__( 'Spacing', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 8 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-gallery-caption' => 'margin-top: {{SIZE}}{{UNIT}};' ),
	'condition'  => array( 'gallery_display_caption' => '' ),
) );

$this->add_control( 'heading_sale_flash_style', array(
	'label'     => esc_html__( 'Sale Flash', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
	'name'      => 'sale_flash_typography',
	'selector'  => '{{WRAPPER}} .kodlo-sale-flash .onsale',
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_padding', array(
	'label'      => esc_html__( 'Padding', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'default'    => array(
		'top'      => '5',
		'right'    => '10',
		'bottom'   => '5',
		'left'     => '10',
		'unit'     => 'px',
		'isLinked' => false,
	),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-sale-flash .onsale' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_margin', array(
	'label'      => esc_html__( 'Margin', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-sale-flash .onsale' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
	),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_position', array(
	'label'   => esc_html__( 'Position', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => array(
		'top_left'      => esc_html__( 'Top Left', 'kodlo-product-gallery-for-elementor' ),
		'top_center'    => esc_html__( 'Top Center', 'kodlo-product-gallery-for-elementor' ),
		'top_right'     => esc_html__( 'Top Right', 'kodlo-product-gallery-for-elementor' ),
		'center_left'   => esc_html__( 'Center Left', 'kodlo-product-gallery-for-elementor' ),
		'center_center' => esc_html__( 'Center Center', 'kodlo-product-gallery-for-elementor' ),
		'center_right'  => esc_html__( 'Center Right', 'kodlo-product-gallery-for-elementor' ),
		'bottom_left'   => esc_html__( 'Bottom Left', 'kodlo-product-gallery-for-elementor' ),
		'bottom_center' => esc_html__( 'Bottom Center', 'kodlo-product-gallery-for-elementor' ),
		'bottom_right'  => esc_html__( 'Bottom Right', 'kodlo-product-gallery-for-elementor' ),
		'custom'        => esc_html__( 'Custom', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'top_left',
	'selectors_dictionary' => array(
		'top_left'      => 'top: 0.5rem; right: auto; bottom: auto; left: 0.5rem; transform: none;',
		'top_center'    => 'top: 0.5rem; right: auto; bottom: auto; left: 50%; transform: translateX(-50%);',
		'top_right'     => 'top: 0.5rem; right: 0.5rem; bottom: auto; left: auto; transform: none;',
		'center_left'   => 'top: 50%; right: auto; bottom: auto; left: 0.5rem; transform: translateY(-50%);',
		'center_center' => 'top: 50%; right: auto; bottom: auto; left: 50%; transform: translate(-50%, -50%);',
		'center_right'  => 'top: 50%; right: 0.5rem; bottom: auto; left: auto; transform: translateY(-50%);',
		'bottom_left'   => 'top: auto; right: auto; bottom: 0.5rem; left: 0.5rem; transform: none;',
		'bottom_center' => 'top: auto; right: auto; bottom: 0.5rem; left: 50%; transform: translateX(-50%);',
		'bottom_right'  => 'top: auto; right: 0.5rem; bottom: 0.5rem; left: auto; transform: none;',
		'custom'        => 'top: 0.5rem; right: auto; bottom: auto; left: 0.5rem; transform: none;',
	),
	'selectors' => array(
		'{{WRAPPER}} .kodlo-sale-flash' => '{{VALUE}}',
	),
	'condition' => array( 'sale_flash' => 'yes' ),
	'render_type' => 'template',
) );

$this->add_control( 'sale_flash_custom_top', array(
	'label'      => esc_html__( 'Top', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array(
		'px' => array( 'min' => -200, 'max' => 1000 ),
		'%'  => array( 'min' => -100, 'max' => 100 ),
	),
	'default'    => array( 'unit' => 'px', 'size' => 8 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-sale-flash' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;' ),
	'condition'  => array( 'sale_flash' => 'yes', 'sale_flash_position' => 'custom' ),
) );

$this->add_control( 'sale_flash_custom_left', array(
	'label'      => esc_html__( 'Left', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array(
		'px' => array( 'min' => -200, 'max' => 1000 ),
		'%'  => array( 'min' => -100, 'max' => 100 ),
	),
	'default'    => array( 'unit' => 'px', 'size' => 8 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-sale-flash' => 'left: {{SIZE}}{{UNIT}}; right: auto;' ),
	'condition'  => array( 'sale_flash' => 'yes', 'sale_flash_position' => 'custom' ),
) );

$this->start_controls_tabs( 'tabs_sale_flash_style' );

$this->start_controls_tab( 'tab_sale_flash_normal', array(
	'label'     => esc_html__( 'Normal', 'kodlo-product-gallery-for-elementor' ),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_text_color', array(
	'label'     => esc_html__( 'Text Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array( '{{WRAPPER}} .kodlo-sale-flash .onsale' => 'color: {{VALUE}};' ),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_background_color', array(
	'label'     => esc_html__( 'Background Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#000000',
	'selectors' => array( '{{WRAPPER}} .kodlo-sale-flash .onsale' => 'background-color: {{VALUE}};' ),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_border_style', array(
	'label'   => esc_html__( 'Border Style', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => array(
		'none'   => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
		'solid'  => esc_html__( 'Solid', 'kodlo-product-gallery-for-elementor' ),
		'double' => esc_html__( 'Double', 'kodlo-product-gallery-for-elementor' ),
		'dotted' => esc_html__( 'Dotted', 'kodlo-product-gallery-for-elementor' ),
		'dashed' => esc_html__( 'Dashed', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'none',
	'selectors' => array( '{{WRAPPER}} .kodlo-sale-flash .onsale' => 'border-style: {{VALUE}} !important;' ),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-sale-flash .onsale' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
	),
	'condition' => array( 'sale_flash' => 'yes', 'sale_flash_border_style!' => 'none' ),
) );

$this->add_control( 'sale_flash_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-sale-flash .onsale' => 'border-color: {{VALUE}} !important;' ),
	'condition' => array( 'sale_flash' => 'yes', 'sale_flash_border_style!' => 'none' ),
) );

$this->add_control( 'sale_flash_border_radius', array(
	'label'      => esc_html__( 'Border Radius', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'default'    => array(
		'top'      => '3',
		'right'    => '3',
		'bottom'   => '3',
		'left'     => '3',
		'unit'     => 'px',
		'isLinked' => true,
	),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-sale-flash .onsale' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
	),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->end_controls_tab();

$this->start_controls_tab( 'tab_sale_flash_hover', array(
	'label'     => esc_html__( 'Hover', 'kodlo-product-gallery-for-elementor' ),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_hover_text_color', array(
	'label'     => esc_html__( 'Text Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-sale-flash:hover .onsale' => 'color: {{VALUE}};' ),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_hover_background_color', array(
	'label'     => esc_html__( 'Background Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-sale-flash:hover .onsale' => 'background-color: {{VALUE}};' ),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_hover_border_style', array(
	'label'   => esc_html__( 'Border Style', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => array(
		'none'   => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
		'solid'  => esc_html__( 'Solid', 'kodlo-product-gallery-for-elementor' ),
		'double' => esc_html__( 'Double', 'kodlo-product-gallery-for-elementor' ),
		'dotted' => esc_html__( 'Dotted', 'kodlo-product-gallery-for-elementor' ),
		'dashed' => esc_html__( 'Dashed', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'none',
	'selectors' => array( '{{WRAPPER}} .kodlo-sale-flash:hover .onsale' => 'border-style: {{VALUE}} !important;' ),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->add_control( 'sale_flash_hover_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-sale-flash:hover .onsale' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
	),
	'condition' => array( 'sale_flash' => 'yes', 'sale_flash_hover_border_style!' => 'none' ),
) );

$this->add_control( 'sale_flash_hover_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-sale-flash:hover .onsale' => 'border-color: {{VALUE}} !important;' ),
	'condition' => array( 'sale_flash' => 'yes', 'sale_flash_hover_border_style!' => 'none' ),
) );

$this->add_control( 'sale_flash_hover_border_radius', array(
	'label'      => esc_html__( 'Border Radius', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::DIMENSIONS,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-sale-flash:hover .onsale' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
	),
	'condition' => array( 'sale_flash' => 'yes' ),
) );

$this->end_controls_tab();
$this->end_controls_tabs();

$this->end_controls_section();
