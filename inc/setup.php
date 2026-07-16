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
			'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=IBM+Plex+Mono:wght@400;500&display=swap',
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

	// Footer variant: flat list of the primary destinations.
	if ( 'site-footer__links' === $menu_class ) {
		echo '<ul class="' . esc_attr( $menu_class ) . '">';
		foreach ( tf_nav_structure() as $item ) {
			printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( home_url( $item['path'] ) ), esc_html( $item['label'] ) );
		}
		echo '</ul>';
		return;
	}

	// Header: two-level nav with dropdowns (Tab 7 global nav).
	echo '<ul class="' . esc_attr( $menu_class ) . '">';
	foreach ( tf_nav_structure() as $item ) {
		$has_children = ! empty( $item['children'] );
		printf(
			'<li class="menu-item%s"><a href="%s">%s</a>',
			$has_children ? ' menu-item-has-children' : '',
			esc_url( home_url( $item['path'] ) ),
			esc_html( $item['label'] )
		);
		if ( $has_children ) {
			echo '<ul class="sub-menu">';
			foreach ( $item['children'] as $child_path => $child_label ) {
				printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( home_url( $child_path ) ), esc_html( $child_label ) );
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Global nav structure per Tab 7 + Tab 2 nav locations,
 * derived from the SEO spec.
 *
 * @return array
 */
function tf_nav_structure() {
	$dropdown = function ( $parent ) {
		$out = array();
		foreach ( tf_spec_children( $parent ) as $path => $rec ) {
			if ( isset( $rec['nav'] ) && 'dropdown' === $rec['nav'] ) {
				$out[ $path ] = $rec['h1'];
			}
		}
		return $out;
	};

	return array(
		array(
			'label'    => __( 'AI Consultancy', 'thinkfurther' ),
			'path'     => '/ai-consultancy/',
			'children' => $dropdown( '/ai-consultancy/' ),
		),
		array(
			'label'    => __( 'AI Search', 'thinkfurther' ),
			'path'     => '/ai-search/',
			'children' => $dropdown( '/ai-search/' ),
		),
		array(
			'label'    => __( 'Industries', 'thinkfurther' ),
			'path'     => '/industries/',
			'children' => $dropdown( '/industries/' ),
		),
		array(
			'label'    => __( 'Case Studies', 'thinkfurther' ),
			'path'     => '/case-studies/',
			'children' => array(),
		),
		array(
			'label'    => __( 'Pricing', 'thinkfurther' ),
			'path'     => '/pricing/',
			'children' => array(),
		),
		array(
			'label'    => __( 'Resources', 'thinkfurther' ),
			'path'     => '/resources/',
			'children' => array(
				'/guides/'                 => __( 'Guides', 'thinkfurther' ),
				'/blog/'                   => __( 'Blog', 'thinkfurther' ),
				'/glossary/'               => __( 'Glossary', 'thinkfurther' ),
				'/tools/'                  => __( 'Free tools', 'thinkfurther' ),
				'/tools/ai-roi-calculator/' => __( 'AI ROI calculator', 'thinkfurther' ),
			),
		),
		array(
			'label'    => __( 'About', 'thinkfurther' ),
			'path'     => '/about/',
			'children' => array(),
		),
	);
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
	// Two-arm brand cue: AI-search content picks up Beam.
	if ( is_page_template( 'page-templates/ai-search.php' ) ) {
		$classes[] = 'tf-arm-search';
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
