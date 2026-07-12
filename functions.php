<?php
/**
 * Think Further theme bootstrap.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TF_THEME_VERSION', '1.1.0' );
define( 'TF_THEME_DIR', get_template_directory() );
define( 'TF_THEME_URI', get_template_directory_uri() );

require TF_THEME_DIR . '/inc/setup.php';
require TF_THEME_DIR . '/inc/customizer.php';
require TF_THEME_DIR . '/inc/template-tags.php';
require TF_THEME_DIR . '/inc/contact-form.php';
require TF_THEME_DIR . '/inc/case-studies.php';

if ( is_admin() ) {
	require TF_THEME_DIR . '/inc/admin-setup.php';
}

/**
 * Enqueue front-end assets.
 */
function tf_enqueue_assets() {
	// Typography: Fraunces (display serif), Inter (body), IBM Plex Mono (labels).
	wp_enqueue_style(
		'tf-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap',
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
 * Structured data: Organization + WebSite JSON-LD.
 * Skipped automatically if an SEO plugin already prints schema.
 */
function tf_structured_data() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
		return;
	}

	$organization = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'ProfessionalService',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'description' => __( 'AI consultancy with two practices: business AI consultancy and transformation, and AI search visibility and optimisation.', 'thinkfurther' ),
		'email'       => sanitize_email( tf_option( 'tf_contact_email' ) ),
		'knowsAbout'  => array(
			'AI strategy',
			'AI transformation',
			'Agentic workflows',
			'AI search optimisation',
			'Answer engine optimisation',
			'Generative engine optimisation',
		),
	);

	$linkedin = tf_option( 'tf_linkedin_url' );
	if ( $linkedin ) {
		$organization['sameAs'] = array( $linkedin );
	}

	if ( has_custom_logo() ) {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $logo_src ) {
			$organization['logo'] = $logo_src[0];
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $organization ) . '</script>' . "\n";
}
add_action( 'wp_head', 'tf_structured_data', 5 );

/**
 * Meta description fallback for key pages (skipped if an SEO plugin is active).
 */
function tf_meta_description() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
		return;
	}

	$description = '';
	if ( is_front_page() ) {
		$description = tf_option( 'tf_hero_subline' );
	} elseif ( is_singular() && has_excerpt() ) {
		$description = get_the_excerpt();
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $description ) ) );
	}
}
add_action( 'wp_head', 'tf_meta_description', 4 );

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
