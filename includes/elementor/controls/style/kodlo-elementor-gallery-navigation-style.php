<?php
/**
 * Navigation style controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_style_nav', array(
	'label' => esc_html__( 'Navigation style', 'kodlo-product-gallery-for-elementor' ),
	'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
	'conditions' => array(
		'relation' => 'and',
		'terms'    => array(
			array( 'name' => 'enable_mobile_view', 'operator' => '===', 'value' => 'yes' ),
			array(
				'relation' => 'or',
				'terms'    => array(
					array( 'name' => 'show_main_nav', 'operator' => '===', 'value' => 'yes' ),
					array( 'name' => 'show_pagination', 'operator' => '===', 'value' => 'yes' ),
					array(
						'relation' => 'and',
						'terms'    => array(
							array( 'name' => 'show_thumbnails', 'operator' => '===', 'value' => 'yes' ),
							array( 'name' => 'show_thumbs_nav', 'operator' => '===', 'value' => 'yes' ),
						),
					),
				),
			),
		),
	),
) );

// Shared closure — called once for main slider arrows and once for thumbnails arrows.
$kodlo_add_navigation_arrow_controls = function ( $prefix, $selector, $conditions ) {
	$icon_selector = $selector . ' i, ' . $selector . ' svg';

	$this->add_control( $prefix . '_icon_size', array(
		'label'      => esc_html__( 'Icon Size', 'kodlo-product-gallery-for-elementor' ),
		'type'       => \Elementor\Controls_Manager::SLIDER,
		'size_units' => array( 'px', 'em', 'rem', 'custom' ),
		'range'      => array( 'px' => array( 'min' => 10, 'max' => 60 ) ),
		'default'    => array( 'unit' => 'px', 'size' => 20 ),
		'selectors'  => array(
			$icon_selector => 'font-size: {{SIZE}}{{UNIT}} !important; width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
		),
		'condition'  => $conditions,
	) );

	$this->add_control( $prefix . '_padding', array(
		'label'      => esc_html__( 'Padding', 'kodlo-product-gallery-for-elementor' ),
		'type'       => \Elementor\Controls_Manager::DIMENSIONS,
		'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
		'default'    => array(
			'top'    => '8',
			'right'  => '10',
			'bottom' => '8',
			'left'   => '10',
			'unit'   => 'px',
		),
		'selectors'  => array(
			$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		),
		'condition'  => $conditions,
	) );

	$this->start_controls_tabs( $prefix . '_tabs_style' );

	$this->start_controls_tab( $prefix . '_tab_normal', array(
		'label'     => esc_html__( 'Normal', 'kodlo-product-gallery-for-elementor' ),
		'condition' => $conditions,
	) );

	$this->add_control( $prefix . '_icon_color', array(
		'label'     => esc_html__( 'Icon Color', 'kodlo-product-gallery-for-elementor' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'default'   => '#ffffff',
		'selectors' => array(
			$icon_selector => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
		),
		'condition' => $conditions,
	) );

	$this->add_control( $prefix . '_bg_color', array(
		'label'     => esc_html__( 'Background', 'kodlo-product-gallery-for-elementor' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'default'   => 'rgba(0,0,0,0.5)',
		'selectors' => array( $selector => 'background: {{VALUE}} !important;' ),
		'condition' => $conditions,
	) );

	$this->add_control( $prefix . '_border_style', array(
		'label'     => esc_html__( 'Border Style', 'kodlo-product-gallery-for-elementor' ),
		'type'      => \Elementor\Controls_Manager::SELECT,
		'options'   => array(
			'none'   => esc_html__( 'None', 'kodlo-product-gallery-for-elementor' ),
			'solid'  => esc_html__( 'Solid', 'kodlo-product-gallery-for-elementor' ),
			'dashed' => esc_html__( 'Dashed', 'kodlo-product-gallery-for-elementor' ),
			'dotted' => esc_html__( 'Dotted', 'kodlo-product-gallery-for-elementor' ),
		),
		'default'   => 'none',
		'selectors' => array( $selector => 'border-style: {{VALUE}} !important;' ),
		'condition' => $conditions,
	) );

	$this->add_control( $prefix . '_border_width', array(
		'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
		'type'       => \Elementor\Controls_Manager::SLIDER,
		'size_units' => array( 'px', 'em', 'rem', 'custom' ),
		'range'      => array( 'px' => array( 'min' => 1, 'max' => 10 ) ),
		'default'    => array( 'unit' => 'px', 'size' => 1 ),
		'selectors'  => array( $selector => 'border-width: {{SIZE}}{{UNIT}} !important;' ),
		'condition'  => array_merge( $conditions, array( $prefix . '_border_style!' => 'none' ) ),
	) );

	$this->add_control( $prefix . '_border_color', array(
		'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'default'   => '#ffffff',
		'selectors' => array( $selector => 'border-color: {{VALUE}} !important;' ),
		'condition' => array_merge( $conditions, array( $prefix . '_border_style!' => 'none' ) ),
	) );

	$this->add_control( $prefix . '_border_radius', array(
		'label'      => esc_html__( 'Border Radius', 'kodlo-product-gallery-for-elementor' ),
		'type'       => \Elementor\Controls_Manager::DIMENSIONS,
		'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
		'default'    => array(
			'top'    => '4',
			'right'  => '4',
			'bottom' => '4',
			'left'   => '4',
			'unit'   => 'px',
		),
		'selectors'  => array(
			$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
		),
		'condition'  => $conditions,
	) );

	$this->end_controls_tab();

	$this->start_controls_tab( $prefix . '_tab_hover', array(
		'label'     => esc_html__( 'Hover', 'kodlo-product-gallery-for-elementor' ),
		'condition' => $conditions,
	) );

	$this->add_control( $prefix . '_icon_hover_color', array(
		'label'     => esc_html__( 'Icon Color', 'kodlo-product-gallery-for-elementor' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'default'   => '#cccccc',
		'selectors' => array(
			$selector . ':hover i, ' . $selector . ':hover svg' => 'color: {{VALUE}} !important; fill: {{VALUE}} !important;',
		),
		'condition' => $conditions,
	) );

	$this->add_control( $prefix . '_bg_hover_color', array(
		'label'     => esc_html__( 'Background', 'kodlo-product-gallery-for-elementor' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'default'   => 'rgba(0,0,0,0.8)',
		'selectors' => array( $selector . ':hover' => 'background: {{VALUE}} !important;' ),
		'condition' => $conditions,
	) );

	$this->add_control( $prefix . '_border_hover_color', array(
		'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'selectors' => array( $selector . ':hover' => 'border-color: {{VALUE}} !important;' ),
		'condition' => $conditions,
	) );

	$this->end_controls_tab();

	$this->end_controls_tabs();
};

// Pagination Dots
$this->add_control( 'heading_navigation_dots', array(
	'label'     => esc_html__( 'Pagination Dots', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_position', array(
	'label'     => esc_html__( 'Dots Position', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'options'   => array(
		'between' => esc_html__( 'Outside', 'kodlo-product-gallery-for-elementor' ),
		'overlay' => esc_html__( 'Inside', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'   => 'between',
	'selectors_dictionary' => array(
		'between' => 'position: relative !important; top: auto !important; bottom: auto !important; margin-top: var(--kodlo-dot-top-spacing, 8px) !important; margin-bottom: var(--kodlo-dot-bottom-spacing, 8px) !important;',
		'overlay' => 'position: absolute !important; top: auto !important; bottom: var(--kodlo-dot-bottom-spacing, 8px) !important; margin: 0 !important; z-index: 20;',
	),
	'selectors' => array(
		'{{WRAPPER}} .kodlo-mobile-slider .swiper-pagination' => '{{VALUE}}',
	),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_spacing_top', array(
	'label'      => esc_html__( 'Dots Spacing Top', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 8 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-dot-top-spacing: {{SIZE}}{{UNIT}};' ),
	'condition'  => array( 'show_pagination' => 'yes', 'dot_position' => 'between' ),
) );

$this->add_control( 'dot_spacing_bottom', array(
	'label'      => esc_html__( 'Dots Spacing Bottom / Offset', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 8 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-dot-bottom-spacing: {{SIZE}}{{UNIT}};' ),
	'condition'  => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_size', array(
	'label'      => esc_html__( 'Dot Size', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 4, 'max' => 20 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 8 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-mobile-slider .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
	),
	'condition'  => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_margin', array(
	'label'      => esc_html__( 'Dot Spacing', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 20 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 4 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-mobile-slider .swiper-pagination-bullet' => 'margin: 0 {{SIZE}}{{UNIT}} !important;',
	),
	'condition'  => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_border_radius', array(
	'label'      => esc_html__( 'Border Radius', 'kodlo-product-gallery-for-elementor' ),
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
		'{{WRAPPER}} .kodlo-mobile-slider .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
	),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->start_controls_tabs( 'tabs_dot_states' );

$this->start_controls_tab( 'tab_dot_normal', array(
	'label'     => esc_html__( 'Normal', 'kodlo-product-gallery-for-elementor' ),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_color', array(
	'label'     => esc_html__( 'Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => 'rgba(0,0,0,0.4)',
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-dot-color: {{VALUE}};' ),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_border_style', array(
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
	'selectors' => array(
		'{{WRAPPER}} .kodlo-mobile-slider .swiper-pagination-bullet' => 'border-style: {{VALUE}} !important;',
	),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 1 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-mobile-slider .swiper-pagination-bullet' => 'border-width: {{SIZE}}{{UNIT}} !important;',
	),
	'condition'  => array( 'show_pagination' => 'yes', 'dot_border_style!' => 'none' ),
) );

$this->add_control( 'dot_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-dot-border-color: {{VALUE}};' ),
	'condition' => array( 'show_pagination' => 'yes', 'dot_border_style!' => 'none' ),
) );

$this->end_controls_tab();

$this->start_controls_tab( 'tab_dot_hover', array(
	'label'     => esc_html__( 'Hover', 'kodlo-product-gallery-for-elementor' ),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

// Exclude active dot from hover — active state is styled separately in the Active tab.
$kodlo_dot_hover_selector = '{{WRAPPER}} .kodlo-mobile-slider .swiper-pagination-bullet:not(.swiper-pagination-bullet-active):hover';

$this->add_control( 'dot_hover_color', array(
	'label'     => esc_html__( 'Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#000000',
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-dot-hover-color: {{VALUE}};' ),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_hover_border_style', array(
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
	'selectors' => array( $kodlo_dot_hover_selector => 'border-style: {{VALUE}} !important;' ),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_hover_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 1 ),
	'selectors'  => array( $kodlo_dot_hover_selector => 'border-width: {{SIZE}}{{UNIT}} !important;' ),
	'condition'  => array( 'show_pagination' => 'yes', 'dot_hover_border_style!' => 'none' ),
) );

$this->add_control( 'dot_hover_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-dot-hover-border-color: {{VALUE}};' ),
	'condition' => array( 'show_pagination' => 'yes', 'dot_hover_border_style!' => 'none' ),
) );

$this->end_controls_tab();

$this->start_controls_tab( 'tab_dot_active', array(
	'label'     => esc_html__( 'Active', 'kodlo-product-gallery-for-elementor' ),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$kodlo_dot_active_selector = '{{WRAPPER}} .kodlo-mobile-slider .swiper-pagination-bullet.swiper-pagination-bullet-active, {{WRAPPER}} .kodlo-mobile-slider .swiper-pagination-bullet.swiper-pagination-bullet-active:hover';

$this->add_control( 'dot_active_color', array(
	'label'     => esc_html__( 'Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#000000',
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-dot-active-color: {{VALUE}};' ),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_active_border_style', array(
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
	'selectors' => array( $kodlo_dot_active_selector => 'border-style: {{VALUE}} !important;' ),
	'condition' => array( 'show_pagination' => 'yes' ),
) );

$this->add_control( 'dot_active_border_width', array(
	'label'      => esc_html__( 'Border Width', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 1 ),
	'selectors'  => array( $kodlo_dot_active_selector => 'border-width: {{SIZE}}{{UNIT}} !important;' ),
	'condition'  => array( 'show_pagination' => 'yes', 'dot_active_border_style!' => 'none' ),
) );

$this->add_control( 'dot_active_border_color', array(
	'label'     => esc_html__( 'Border Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'selectors' => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-dot-active-border-color: {{VALUE}};' ),
	'condition' => array( 'show_pagination' => 'yes', 'dot_active_border_style!' => 'none' ),
) );

$this->end_controls_tab();

$this->end_controls_tabs();

// Main Slider Arrows
$this->add_control( 'heading_navigation_images', array(
	'label'     => esc_html__( 'Main Slider Arrows', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
	'condition' => array( 'show_main_nav' => 'yes' ),
) );

$this->add_control( 'main_nav_position', array(
	'label'       => esc_html__( 'Position', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::SELECT,
	'options'     => array(
		'overlay' => esc_html__( 'Inside', 'kodlo-product-gallery-for-elementor' ),
		'sides'   => esc_html__( 'Outside', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'     => 'overlay',
	'render_type' => 'template',
	'selectors'   => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-main-nav-position: {{VALUE}};' ),
	'condition'   => array( 'show_main_nav' => 'yes' ),
) );

$this->add_control( 'main_nav_offset', array(
	'label'      => esc_html__( 'Horizontal Offset', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array(
		'px' => array( 'min' => -100, 'max' => 100 ),
		'%'  => array( 'min' => -25, 'max' => 50 ),
	),
	'default'    => array( 'unit' => 'px', 'size' => 8 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-main-slider-wrap' => '--kodlo-nav-offset: {{SIZE}}{{UNIT}};' ),
	'condition'  => array( 'show_main_nav' => 'yes' ),
) );

$this->add_control( 'main_nav_vertical_position', array(
	'label'      => esc_html__( 'Vertical Position', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array(
		'px' => array( 'min' => -100, 'max' => 500 ),
		'%'  => array( 'min' => 0, 'max' => 100 ),
	),
	'default'    => array( 'unit' => '%', 'size' => 50 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-main-slider-wrap' => '--kodlo-nav-top: {{SIZE}}{{UNIT}};' ),
	'condition'  => array( 'show_main_nav' => 'yes', 'main_nav_position' => 'overlay' ),
) );

$kodlo_add_navigation_arrow_controls(
	'nav',
	'{{WRAPPER}} .kodlo-main-slider-wrap .kodlo-nav-btn',
	array( 'show_main_nav' => 'yes' )
);

// Thumbnails Arrows
$this->add_control( 'heading_navigation_thumbnails', array(
	'label'     => esc_html__( 'Thumbnails Arrows', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
	'condition' => array(
		'show_thumbnails' => 'yes',
		'show_thumbs_nav' => 'yes',
	),
) );

$this->add_control( 'thumbs_nav_position', array(
	'label'       => esc_html__( 'Position', 'kodlo-product-gallery-for-elementor' ),
	'type'        => \Elementor\Controls_Manager::SELECT,
	'options'     => array(
		'overlay' => esc_html__( 'Inside', 'kodlo-product-gallery-for-elementor' ),
		'sides'   => esc_html__( 'Outside', 'kodlo-product-gallery-for-elementor' ),
	),
	'default'     => 'overlay',
	'render_type' => 'template',
	'selectors'   => array( '{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumbs-nav-position: {{VALUE}};' ),
	'condition'   => array(
		'show_thumbnails' => 'yes',
		'show_thumbs_nav' => 'yes',
	),
) );

$this->add_control( 'thumbs_nav_offset', array(
	'label'      => esc_html__( 'Horizontal Offset', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array(
		'px' => array( 'min' => -100, 'max' => 100 ),
		'%'  => array( 'min' => -25, 'max' => 50 ),
	),
	'default'    => array( 'unit' => 'px', 'size' => 8 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-thumbs-slider-wrap' => '--kodlo-nav-offset: {{SIZE}}{{UNIT}};' ),
	'condition'  => array(
		'show_thumbnails' => 'yes',
		'show_thumbs_nav' => 'yes',
	),
) );

$this->add_control( 'thumbs_nav_vertical_position', array(
	'label'      => esc_html__( 'Vertical Position', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array(
		'px' => array( 'min' => -100, 'max' => 500 ),
		'%'  => array( 'min' => 0, 'max' => 100 ),
	),
	'default'    => array( 'unit' => '%', 'size' => 50 ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-thumbs-slider-wrap' => '--kodlo-nav-top: {{SIZE}}{{UNIT}};' ),
	'condition'  => array(
		'show_thumbnails'     => 'yes',
		'show_thumbs_nav'     => 'yes',
		'thumbs_nav_position' => 'overlay',
	),
) );

$kodlo_add_navigation_arrow_controls(
	'thumbs_nav_style',
	'{{WRAPPER}} .kodlo-thumbs-slider-wrap .kodlo-nav-btn',
	array(
		'show_thumbnails' => 'yes',
		'show_thumbs_nav' => 'yes',
	)
);

// Free closure after last call — prevents accidental reuse in subsequent includes.
unset( $kodlo_add_navigation_arrow_controls );

$this->end_controls_section();
