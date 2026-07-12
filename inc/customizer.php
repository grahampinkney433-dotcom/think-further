<?php
/**
 * Customizer: editable business details and key copy.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function tf_customize_register( $wp_customize ) {

	$wp_customize->add_panel(
		'tf_theme_options',
		array(
			'title'    => __( 'Think Further Options', 'thinkfurther' ),
			'priority' => 10,
		)
	);

	/* ---- Contact & links ---- */

	$wp_customize->add_section(
		'tf_contact',
		array(
			'title' => __( 'Contact Details', 'thinkfurther' ),
			'panel' => 'tf_theme_options',
		)
	);

	$contact_fields = array(
		'tf_contact_email'  => array( __( 'Contact email', 'thinkfurther' ), 'hello@thinkfurther.ai', 'sanitize_email' ),
		'tf_booking_url'    => array( __( 'Booking / calendar link (optional)', 'thinkfurther' ), '', 'esc_url_raw' ),
		'tf_linkedin_url'   => array( __( 'LinkedIn URL', 'thinkfurther' ), '', 'esc_url_raw' ),
		'tf_location_label' => array( __( 'Location label', 'thinkfurther' ), 'United Kingdom · Working worldwide', 'sanitize_text_field' ),
	);

	foreach ( $contact_fields as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $field[1],
				'sanitize_callback' => $field[2],
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field[0],
				'section' => 'tf_contact',
				'type'    => 'text',
			)
		);
	}

	/* ---- Hero copy ---- */

	$wp_customize->add_section(
		'tf_hero',
		array(
			'title' => __( 'Homepage Hero', 'thinkfurther' ),
			'panel' => 'tf_theme_options',
		)
	);

	$hero_fields = array(
		'tf_hero_eyebrow'  => array( __( 'Eyebrow label', 'thinkfurther' ), 'AI consultancy — strategy to search' ),
		'tf_hero_headline' => array( __( 'Headline (use *asterisks* for italic serif emphasis)', 'thinkfurther' ), 'Most companies adopt AI. The ones that win *think further.*' ),
		'tf_hero_subline'  => array( __( 'Supporting line', 'thinkfurther' ), 'We help ambitious businesses turn AI from scattered experiments into a durable advantage — from operational transformation to how you\'re found and cited by AI search.' ),
		'tf_hero_cta'      => array( __( 'Primary CTA label', 'thinkfurther' ), 'Book a strategy call' ),
	);

	foreach ( $hero_fields as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $field[1],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field[0],
				'section' => 'tf_hero',
				'type'    => 'tf_hero_subline' === $key ? 'textarea' : 'text',
			)
		);
	}

	/* ---- Footer ---- */

	$wp_customize->add_section(
		'tf_footer',
		array(
			'title' => __( 'Footer', 'thinkfurther' ),
			'panel' => 'tf_theme_options',
		)
	);

	$wp_customize->add_setting(
		'tf_footer_tagline',
		array(
			'default'           => 'An AI consultancy for businesses that intend to lead — not follow.',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'tf_footer_tagline',
		array(
			'label'   => __( 'Footer tagline', 'thinkfurther' ),
			'section' => 'tf_footer',
			'type'    => 'textarea',
		)
	);
}
add_action( 'customize_register', 'tf_customize_register' );

/**
 * Convenience getter for theme mods with theme defaults.
 *
 * @param string $key Theme mod key.
 * @return string
 */
function tf_option( $key ) {
	$defaults = array(
		'tf_contact_email'  => 'hello@thinkfurther.ai',
		'tf_booking_url'    => '',
		'tf_linkedin_url'   => '',
		'tf_location_label' => 'United Kingdom · Working worldwide',
		'tf_hero_eyebrow'   => 'AI consultancy — strategy to search',
		'tf_hero_headline'  => 'Most companies adopt AI. The ones that win *think further.*',
		'tf_hero_subline'   => 'We help ambitious businesses turn AI from scattered experiments into a durable advantage — from operational transformation to how you\'re found and cited by AI search.',
		'tf_hero_cta'       => 'Book a strategy call',
		'tf_footer_tagline' => 'An AI consultancy for businesses that intend to lead — not follow.',
	);

	$default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

	return get_theme_mod( $key, $default );
}
