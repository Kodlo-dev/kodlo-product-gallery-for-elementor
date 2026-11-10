<?php
/**
 * Video content controls for Kodlo Product Gallery for Elementor.
 */

defined( 'ABSPATH' ) || exit;

$this->start_controls_section( 'section_video', array(
	'label' => esc_html__( 'Video settings', 'kodlo-product-gallery-for-elementor' ),
	'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
) );

$this->add_control( 'show_videos', array(
	'label'        => esc_html__( 'Show Videos', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
) );

$this->add_control( 'show_thumbnail_video_icon', array(
	'label'        => esc_html__( 'Video Icon on Thumbnails', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Show', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'Hide', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
	'render_type'  => 'template',
	'condition'    => array(
		'show_videos'        => 'yes',
		'enable_mobile_view' => 'yes',
		'show_thumbnails'    => 'yes',
	),
) );

$this->add_control( 'video_icon', array(
	'label'     => esc_html__( 'Play Icon', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::ICONS,
	'default'   => array( 'value' => 'fas fa-play-circle', 'library' => 'fa-solid' ),
	'condition' => array( 'show_videos' => 'yes' ),
) );

$this->add_control( 'autoplay_videos', array(
	'label'     => esc_html__( 'Autoplay Videos', 'kodlo-product-gallery-for-elementor' ),
	'type'      => \Elementor\Controls_Manager::SELECT,
	'default'   => 'no',
	'options'   => array(
		'no'      => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
		'desktop' => esc_html__( 'Desktop', 'kodlo-product-gallery-for-elementor' ),
		'mobile'  => esc_html__( 'Mobile', 'kodlo-product-gallery-for-elementor' ),
		'yes'     => esc_html__( 'Both', 'kodlo-product-gallery-for-elementor' ),
	),
	'condition' => array( 'show_videos' => 'yes' ),
) );

$this->add_control( 'mute_videos', array(
	'label'        => esc_html__( 'Mute Videos', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'yes',
	'condition' => array(
		'show_videos'      => 'yes',
		'autoplay_videos!' => 'yes',
	),
) );

$this->add_control( 'loop_videos', array(
	'label'        => esc_html__( 'Loop Videos', 'kodlo-product-gallery-for-elementor' ),
	'type'         => \Elementor\Controls_Manager::SWITCHER,
	'label_on'     => esc_html__( 'Yes', 'kodlo-product-gallery-for-elementor' ),
	'label_off'    => esc_html__( 'No', 'kodlo-product-gallery-for-elementor' ),
	'return_value' => 'yes',
	'default'      => 'no',
	'condition'    => array( 'show_videos' => 'yes' ),
) );

$this->end_controls_section();
