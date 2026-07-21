<?php
/**
 * Case Studies: custom post type + details meta box.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Case Study post type.
 */
function tf_register_case_studies() {
	register_post_type(
		'tf_case_study',
		array(
			'labels'       => array(
				'name'               => __( 'Case Studies', 'thinkfurther' ),
				'singular_name'      => __( 'Case Study', 'thinkfurther' ),
				'add_new_item'       => __( 'Add New Case Study', 'thinkfurther' ),
				'edit_item'          => __( 'Edit Case Study', 'thinkfurther' ),
				'new_item'           => __( 'New Case Study', 'thinkfurther' ),
				'view_item'          => __( 'View Case Study', 'thinkfurther' ),
				'search_items'       => __( 'Search Case Studies', 'thinkfurther' ),
				'not_found'          => __( 'No case studies found', 'thinkfurther' ),
				'all_items'          => __( 'All Case Studies', 'thinkfurther' ),
				'archives'           => __( 'Work', 'thinkfurther' ),
			),
			'public'       => true,
			'has_archive'  => 'case-studies',
			'rewrite'      => array(
				'slug'       => 'case-studies',
				'with_front' => false,
			),
			'menu_icon'    => 'dashicons-awards',
			'menu_position' => 21,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
			'show_in_rest' => true,
			// Tab 6 CASE STUDY outline pre-filled in the editor.
			'template'     => array(
				array( 'core/heading', array( 'content' => 'The challenge' ) ),
				array( 'core/paragraph', array( 'placeholder' => '[Context: client situation and the problem — Experience signal]' ) ),
				array( 'core/heading', array( 'content' => 'Our approach' ) ),
				array( 'core/paragraph', array( 'placeholder' => '[What we did, specifically — first-hand actions taken]' ) ),
				array( 'core/heading', array( 'content' => 'The results' ) ),
				array( 'core/paragraph', array( 'placeholder' => '[Metrics table or quantified outcomes — LLM-extractable]' ) ),
			),
		)
	);
}
add_action( 'init', 'tf_register_case_studies' );

/**
 * Case study details meta box (client, sector, headline result).
 */
function tf_case_meta_box() {
	add_meta_box(
		'tf_case_details',
		__( 'Case Study Details', 'thinkfurther' ),
		'tf_case_meta_box_render',
		'tf_case_study',
		'side'
	);
}
add_action( 'add_meta_boxes', 'tf_case_meta_box' );

/**
 * Render the details meta box.
 *
 * @param WP_Post $post Current post.
 */
function tf_case_meta_box_render( $post ) {
	wp_nonce_field( 'tf_case_details_save', 'tf_case_details_nonce' );

	$fields = array(
		'_tf_client' => __( 'Client (or "Confidential")', 'thinkfurther' ),
		'_tf_sector' => __( 'Sector', 'thinkfurther' ),
		'_tf_result' => __( 'Headline result (e.g. "38% faster cycle time")', 'thinkfurther' ),
	);

	foreach ( $fields as $key => $label ) {
		printf(
			'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" style="width:100%%" id="%1$s" name="%1$s" value="%3$s"></p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( get_post_meta( $post->ID, $key, true ) )
		);
	}
}

/**
 * Save case study details.
 *
 * @param int $post_id Post ID.
 */
function tf_case_meta_box_save( $post_id ) {
	if (
		! isset( $_POST['tf_case_details_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['tf_case_details_nonce'] ) ), 'tf_case_details_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	foreach ( array( '_tf_client', '_tf_sector', '_tf_result' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			if ( $value ) {
				update_post_meta( $post_id, $key, $value );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}
	}
}
add_action( 'save_post_tf_case_study', 'tf_case_meta_box_save' );
