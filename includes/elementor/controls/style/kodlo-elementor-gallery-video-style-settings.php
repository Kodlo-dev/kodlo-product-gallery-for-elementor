<?php
/**
 * Video style controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_style_video', array(
	'label'     => esc_html__( 'Video style', 'kodlo-product-gallery-for-elementor' ),
	'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
	'condition' => array( 'show_videos' => 'yes' ),
) );

$this->add_control( 'heading_video_icon_gallery', array(
	'label'     => esc_html__( 'Gallery', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'condition' => array( 'show_videos' => 'yes' ),
) );

$this->add_control( 'video_icon_size', array(
	'label'      => esc_html__( 'Icon Size', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 20, 'max' => 150 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 60 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-video-play-btn svg, {{WRAPPER}} .kodlo-video-play-btn i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
	),
) );

$this->add_control( 'video_icon_position', array(
	'label'   => esc_html__( 'Icon Position', 'kodlo-product-gallery-for-elementor' ),
	'type'    => \Elementor\Controls_Manager::SELECT,
	'options' => array(
		'center_center' => 'Center Center',
		'top_left'      => 'Top Left',
		'top_center'    => 'Top Center',
		'top_right'     => 'Top Right',
		'center_left'   => 'Center Left',
		'center_right'  => 'Center Right',
		'bottom_left'   => 'Bottom Left',
		'bottom_center' => 'Bottom Center',
		'bottom_right'  => 'Bottom Right',
		'custom'        => 'Custom Position',
	),
	'default' => 'center_center',
	'selectors_dictionary' => array(
		'center_center' => 'top: 50%; right: auto; bottom: auto; left: 50%; transform: translate(-50%, -50%);',
		'top_left'      => 'top: 20px; right: auto; bottom: auto; left: 20px; transform: none;',
		'top_center'    => 'top: 20px; right: auto; bottom: auto; left: 50%; transform: translateX(-50%);',
		'top_right'     => 'top: 20px; right: 20px; bottom: auto; left: auto; transform: none;',
		'center_left'   => 'top: 50%; right: auto; bottom: auto; left: 20px; transform: translateY(-50%);',
		'center_right'  => 'top: 50%; right: 20px; bottom: auto; left: auto; transform: translateY(-50%);',
		'bottom_left'   => 'top: auto; right: auto; bottom: 20px; left: 20px; transform: none;',
		'bottom_center' => 'top: auto; right: auto; bottom: 20px; left: 50%; transform: translateX(-50%);',
		'bottom_right'  => 'top: auto; right: 20px; bottom: 20px; left: auto; transform: none;',
		'custom'        => 'right: auto; bottom: auto; transform: none;',
	),
	'selectors' => array(
		'{{WRAPPER}} .kodlo-video-play-btn-wrap' => '{{VALUE}}',
	),
) );

$this->add_control( 'video_icon_custom_top', array(
	'label'      => esc_html__( 'Top Offset', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 500 ) ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-video-play-btn-wrap' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;' ),
	'condition'  => array( 'video_icon_position' => 'custom' ),
) );

$this->add_control( 'video_icon_custom_left', array(
	'label'      => esc_html__( 'Left Offset', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 0, 'max' => 500 ) ),
	'selectors'  => array( '{{WRAPPER}} .kodlo-video-play-btn-wrap' => 'left: {{SIZE}}{{UNIT}}; right: auto;' ),
	'condition'  => array( 'video_icon_position' => 'custom' ),
) );

$this->start_controls_tabs( 'tabs_video_icon_style' );

$this->start_controls_tab( 'tab_video_icon_normal', array(
	'label' => esc_html__( 'Normal', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'video_icon_color', array(
	'label'     => esc_html__( 'Icon Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-video-play-btn svg, {{WRAPPER}} .kodlo-video-play-btn i' => 'color: {{VALUE}}; fill: {{VALUE}};',
		'{{WRAPPER}} .kodlo-video-play-btn circle'  => 'stroke: {{VALUE}};',
		'{{WRAPPER}} .kodlo-video-play-btn polygon' => 'fill: {{VALUE}};',
	),
) );

$this->end_controls_tab();

$this->start_controls_tab( 'tab_video_icon_hover', array(
	'label' => esc_html__( 'Hover', 'kodlo-product-gallery-for-elementor' ),
) );

$this->add_control( 'video_icon_hover_color', array(
	'label'     => esc_html__( 'Icon Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-video-play-btn:hover svg, {{WRAPPER}} .kodlo-video-play-btn:hover i' => 'color: {{VALUE}}; fill: {{VALUE}};',
		'{{WRAPPER}} .kodlo-video-play-btn:hover circle'  => 'stroke: {{VALUE}};',
		'{{WRAPPER}} .kodlo-video-play-btn:hover polygon' => 'fill: {{VALUE}};',
	),
) );

$this->add_control( 'video_play_btn_hover_animation', array(
	'label' => esc_html__( 'Hover Animation', 'kodlo-product-gallery-for-elementor' ),
	'type'  => \Elementor\Controls_Manager::HOVER_ANIMATION,
) );

$this->add_control( 'video_icon_transition', array(
	'label'      => esc_html__( 'Transition Duration (s)', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 's' ),
	'range'      => array( 's' => array( 'min' => 0, 'max' => 2, 'step' => 0.1 ) ),
	'default'    => array( 'unit' => 's', 'size' => 0.3 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-video-play-btn, {{WRAPPER}} .kodlo-video-play-btn svg, {{WRAPPER}} .kodlo-video-play-btn i, {{WRAPPER}} .kodlo-video-play-btn circle, {{WRAPPER}} .kodlo-video-play-btn polygon' => 'transition: all {{SIZE}}{{UNIT}} ease;',
	),
) );

$this->end_controls_tab();

$this->end_controls_tabs();

$kodlo_thumbnail_video_icon_conditions = array(
	'enable_mobile_view'        => 'yes',
	'show_thumbnails'           => 'yes',
	'show_thumbnail_video_icon' => 'yes',
);

$this->add_control( 'heading_video_icon_thumbnail', array(
	'label'     => esc_html__( 'Thumbnail', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::HEADING,
	'separator' => 'before',
	'condition' => $kodlo_thumbnail_video_icon_conditions,
) );

$this->add_control( 'thumbnail_video_icon_size', array(
	'label'      => esc_html__( 'Icon Size', 'kodlo-product-gallery-for-elementor' ),
	'type'       => \Elementor\Controls_Manager::SLIDER,
	'size_units' => array( 'px', 'em', 'rem', 'custom' ),
	'range'      => array( 'px' => array( 'min' => 8, 'max' => 60 ) ),
	'default'    => array( 'unit' => 'px', 'size' => 20 ),
	'selectors'  => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumbnail-video-icon-size: {{SIZE}}{{UNIT}};',
	),
	'condition'  => $kodlo_thumbnail_video_icon_conditions,
) );

$this->add_control( 'thumbnail_video_icon_color', array(
	'label'     => esc_html__( 'Icon Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '#ffffff',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumbnail-video-icon-color: {{VALUE}};',
	),
	'condition' => $kodlo_thumbnail_video_icon_conditions,
) );

$this->add_control( 'thumbnail_video_icon_background', array(
	'label'     => esc_html__( 'Background Color', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::COLOR,
	'default'   => '',
	'selectors' => array(
		'{{WRAPPER}} .kodlo-gallery-wrapper' => '--kodlo-thumbnail-video-icon-background: {{VALUE}};',
	),
	'condition' => $kodlo_thumbnail_video_icon_conditions,
) );

unset( $kodlo_thumbnail_video_icon_conditions );

$this->end_controls_section();
