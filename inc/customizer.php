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
			'title'    => __( 'Alugence Options', 'thinkfurther' ),
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
		'tf_contact_email'  => array( __( 'Contact email', 'thinkfurther' ), 'hello@alugence.ai', 'sanitize_email' ),
		'tf_booking_url'    => array( __( 'Booking / calendar link (optional)', 'thinkfurther' ), '', 'esc_url_raw' ),
		'tf_linkedin_url'   => array( __( 'LinkedIn URL', 'thinkfurther' ), '', 'esc_url_raw' ),
		'tf_location_label' => array( __( 'Location label', 'thinkfurther' ), 'United Kingdom · Working worldwide', 'sanitize_text_field' ),
		'tf_phone'          => array( __( 'Phone (trust/NAP + schema)', 'thinkfurther' ), '', 'sanitize_text_field' ),
		'tf_address'        => array( __( 'Address / service area (NAP)', 'thinkfurther' ), 'Yorkshire, United Kingdom', 'sanitize_text_field' ),
		'tf_company_number' => array( __( 'Company registration number', 'thinkfurther' ), '', 'sanitize_text_field' ),
		'tf_founding_year'  => array( __( 'Founding year (Organization schema)', 'thinkfurther' ), '', 'sanitize_text_field' ),
		'tf_sameas_urls'    => array( __( 'sameAs profile URLs (one per line: Crunchbase, X, Companies House…)', 'thinkfurther' ), '', 'sanitize_textarea_field' ),
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
				'type'    => 'tf_sameas_urls' === $key ? 'textarea' : 'text',
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
		'tf_hero_headline' => array( __( 'Headline (use *asterisks* for colour emphasis)', 'thinkfurther' ), 'AI consultancy and AI search optimisation for *ambitious businesses*' ),
		'tf_hero_subline'  => array( __( 'Supporting line', 'thinkfurther' ), 'Alugence is an AI consultancy with two practices under one trusted brand: we help you adopt AI to run your business better, and we help you get found by AI when customers search ChatGPT, Gemini, Perplexity and Google AI Overviews.' ),
		'tf_hero_cta'      => array( __( 'Primary CTA label', 'thinkfurther' ), 'Book a discovery call' ),
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
		'tf_contact_email'  => 'hello@alugence.ai',
		'tf_booking_url'    => '',
		'tf_linkedin_url'   => '',
		'tf_location_label' => 'United Kingdom · Working worldwide',
		'tf_phone'          => '',
		'tf_address'        => 'Yorkshire, United Kingdom',
		'tf_company_number' => '',
		'tf_founding_year'  => '',
		'tf_sameas_urls'    => '',
		'tf_hero_eyebrow'   => 'AI consultancy — strategy to search',
		'tf_hero_headline'  => 'AI consultancy and AI search optimisation for *ambitious businesses*',
		'tf_hero_subline'   => 'Alugence is an AI consultancy with two practices under one trusted brand: we help you adopt AI to run your business better, and we help you get found by AI when customers search ChatGPT, Gemini, Perplexity and Google AI Overviews.',
		'tf_hero_cta'       => 'Book a discovery call',
		'tf_footer_tagline' => 'An AI consultancy for businesses that intend to lead — not follow.',
	);

	$default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

	return get_theme_mod( $key, $default );
}
