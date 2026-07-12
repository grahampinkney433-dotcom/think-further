<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports and nav menus.
 */
function tf_setup() {
	load_theme_textdomain( 'thinkfurther', TF_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 260,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'thinkfurther' ),
			'footer'  => __( 'Footer Navigation', 'thinkfurther' ),
		)
	);

	set_post_thumbnail_size( 1280, 720, true );
	add_image_size( 'tf-card', 720, 480, true );

	add_theme_support( 'editor-styles' );
	add_editor_style(
		array(
			'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap',
			'assets/css/editor-style.css',
		)
	);
}
add_action( 'after_setup_theme', 'tf_setup' );

/**
 * Set content width.
 */
function tf_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'tf_content_width', 0 );

/**
 * Fallback primary menu: core marketing pages when no menu is assigned.
 *
 * @param array $args wp_nav_menu args (used for menu_class so footer/header styling differ).
 */
function tf_primary_menu_fallback( $args = array() ) {
	$menu_class = ! empty( $args['menu_class'] ) ? $args['menu_class'] : 'site-nav__list';

	$links = array(
		array( tf_page_url_by_template( 'page-templates/ai-transformation.php', '/ai-consultancy/' ), __( 'AI Consultancy', 'thinkfurther' ) ),
		array( tf_page_url_by_template( 'page-templates/ai-search.php', '/ai-search/' ), __( 'AI Search', 'thinkfurther' ) ),
		array( tf_page_url_by_template( 'page-templates/approach.php', '/approach/' ), __( 'Approach', 'thinkfurther' ) ),
		array( tf_page_url_by_template( 'page-templates/contact.php', '/contact/' ), __( 'Contact', 'thinkfurther' ) ),
	);

	echo '<ul class="' . esc_attr( $menu_class ) . '">';
	foreach ( $links as $link ) {
		printf(
			'<li class="menu-item"><a href="%s">%s</a></li>',
			esc_url( $link[0] ),
			esc_html( $link[1] )
		);
	}
	echo '</ul>';
}

/**
 * Resolve the permalink of the first page using a given template.
 *
 * @param string $template Template file path relative to theme root.
 * @param string $fallback Fallback path appended to home URL.
 * @return string
 */
function tf_page_url_by_template( $template, $fallback ) {
	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => $template,
			'number'     => 1,
		)
	);

	if ( ! empty( $pages ) ) {
		return get_permalink( $pages[0]->ID );
	}

	return home_url( $fallback );
}

/**
 * Body classes for styling hooks.
 *
 * @param array $classes Body classes.
 * @return array
 */
function tf_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'tf-home';
	}
	return $classes;
}
add_filter( 'body_class', 'tf_body_classes' );

/**
 * Nicer excerpt ending.
 *
 * @return string
 */
function tf_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'tf_excerpt_more' );

/**
 * Shorter excerpts for card layouts.
 *
 * @return int
 */
function tf_excerpt_length() {
	return 28;
}
add_filter( 'excerpt_length', 'tf_excerpt_length' );
