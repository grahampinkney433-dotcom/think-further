<?php
/**
 * Expose the theme's SEO / FAQ post meta to the WordPress REST API so the
 * external content publisher (tools/wp-publish/publish.py) can set them when
 * it creates or updates a page, guide, blog post or glossary term.
 *
 * Without this, the REST API silently drops writes to unregistered meta
 * keys. Editing still requires an authenticated user with edit_posts.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the meta keys the templates read, per post type.
 */
function tf_register_rest_meta() {
	$can_edit = function () {
		return current_user_can( 'edit_posts' );
	};

	$string_meta = array(
		'_tf_title_tag'    => array( 'post', 'page', 'tf_guide', 'tf_glossary', 'tf_case_study' ),
		'_tf_meta_desc'    => array( 'post', 'page', 'tf_guide', 'tf_glossary', 'tf_case_study' ),
		'_tf_money_url'    => array( 'tf_guide' ),
		'_tf_money_anchor' => array( 'tf_guide' ),
		'_tf_guide_url'    => array( 'tf_glossary' ),
		// Editorial-only: the "Internal links / Schema / Compliance" notes
		// from the content file, kept with the post for the reviewer.
		'_tf_editor_notes' => array( 'post', 'page', 'tf_guide', 'tf_glossary', 'tf_case_study' ),
	);

	foreach ( $string_meta as $key => $types ) {
		foreach ( $types as $type ) {
			register_post_meta(
				$type,
				$key,
				array(
					'type'          => 'string',
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => $can_edit,
				)
			);
		}
	}

	// FAQ pairs: array of [ question, answer ] arrays. Drives the FAQ
	// accordion and FAQPage schema on guides and blog posts.
	foreach ( array( 'post', 'tf_guide' ) as $type ) {
		register_post_meta(
			$type,
			'_tf_faqs',
			array(
				'type'          => 'array',
				'single'        => true,
				'show_in_rest'  => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
				),
				'auth_callback' => $can_edit,
			)
		);
	}
}
add_action( 'init', 'tf_register_rest_meta', 20 );
