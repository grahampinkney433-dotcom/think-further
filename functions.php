<?php
/**
 * Alugence theme bootstrap.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TF_THEME_VERSION', '3.0.0' );
define( 'TF_THEME_DIR', get_template_directory() );
define( 'TF_THEME_URI', get_template_directory_uri() );

require TF_THEME_DIR . '/inc/setup.php';
require TF_THEME_DIR . '/inc/customizer.php';
require TF_THEME_DIR . '/inc/template-tags.php';
require TF_THEME_DIR . '/inc/contact-form.php';
require TF_THEME_DIR . '/inc/case-studies.php';
require TF_THEME_DIR . '/inc/content-types.php';
require TF_THEME_DIR . '/inc/seo-spec.php';
require TF_THEME_DIR . '/inc/seo-content.php';
require TF_THEME_DIR . '/inc/seo-longform.php';
require TF_THEME_DIR . '/inc/seo.php';
require TF_THEME_DIR . '/inc/rest-meta.php';

if ( is_admin() ) {
	require TF_THEME_DIR . '/inc/admin-setup.php';
}

/**
 * Enqueue front-end assets.
 */
function tf_enqueue_assets() {
	// Brand typography: Space Grotesk (display), IBM Plex Sans (body), IBM Plex Mono (labels).
	wp_enqueue_style(
		'tf-fonts',
		'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=IBM+Plex+Mono:wght@400;500&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'tf-main',
		TF_THEME_URI . '/assets/css/main.css',
		array( 'tf-fonts' ),
		TF_THEME_VERSION
	);

	wp_enqueue_script(
		'tf-main',
		TF_THEME_URI . '/assets/js/main.js',
		array(),
		TF_THEME_VERSION,
		array( 'strategy' => 'defer' )
	);

	// AI ROI Calculator (Tab 16) — only on its template.
	if ( is_page_template( 'page-templates/roi-calculator.php' ) ) {
		wp_enqueue_script( 'tf-roi', TF_THEME_URI . '/assets/js/roi-calculator.js', array(), TF_THEME_VERSION, array( 'strategy' => 'defer' ) );
	}

	// AI Capabilities Audit app — only on its template.
	if ( is_page_template( 'page-templates/audit.php' ) ) {
		wp_enqueue_script(
			'tf-audit',
			TF_THEME_URI . '/assets/js/audit.js',
			array(),
			TF_THEME_VERSION,
			array( 'strategy' => 'defer' )
		);
		wp_localize_script(
			'tf-audit',
			'tfAuditSettings',
			array(
				'ctaUrl'       => tf_cta_url(),
				'contactEmail' => tf_option( 'tf_contact_email' ),
			)
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'tf_enqueue_assets' );

/**
 * Preconnect for Google Fonts.
 *
 * @param array  $urls          Resource hints.
 * @param string $relation_type Relation type.
 * @return array
 */
function tf_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'tf_resource_hints', 10, 2 );

/**
 * Trim front-end head clutter for performance.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

/**
 * Disable emoji scripts (unused, saves a request).
 */
function tf_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'tf_disable_emojis' );
