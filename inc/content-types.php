<?php
/**
 * Guides and Glossary post types (Tab 2: RS-02 /guides/{slug}/,
 * RS-06 /glossary/{term}/).
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Guide + Glossary post types.
 */
function tf_register_content_types() {
	register_post_type(
		'tf_guide',
		array(
			'labels'        => array(
				'name'          => __( 'Guides', 'thinkfurther' ),
				'singular_name' => __( 'Guide', 'thinkfurther' ),
				'add_new_item'  => __( 'Add New Guide', 'thinkfurther' ),
				'edit_item'     => __( 'Edit Guide', 'thinkfurther' ),
				'all_items'     => __( 'All Guides', 'thinkfurther' ),
			),
			'public'        => true,
			'has_archive'   => 'guides',
			'rewrite'       => array(
				'slug'       => 'guides',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-welcome-learn-more',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'tf_glossary',
		array(
			'labels'        => array(
				'name'          => __( 'Glossary', 'thinkfurther' ),
				'singular_name' => __( 'Glossary Term', 'thinkfurther' ),
				'add_new_item'  => __( 'Add New Term', 'thinkfurther' ),
				'edit_item'     => __( 'Edit Term', 'thinkfurther' ),
				'all_items'     => __( 'All Terms', 'thinkfurther' ),
			),
			'public'        => true,
			'has_archive'   => 'glossary',
			'rewrite'       => array(
				'slug'       => 'glossary',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-book-alt',
			'menu_position' => 23,
			'supports'      => array( 'title', 'editor', 'excerpt', 'revisions', 'custom-fields' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'tf_register_content_types' );

/**
 * SEO meta box (title tag + meta description) for guides & glossary.
 * Values feed inc/seo.php; limits per Tab 1 (<=60 / <=155).
 */
function tf_seo_meta_box() {
	foreach ( array( 'tf_guide', 'tf_glossary' ) as $type ) {
		add_meta_box( 'tf_seo_meta', __( 'SEO (title <=60, meta <=155)', 'thinkfurther' ), 'tf_seo_meta_box_render', $type, 'side' );
	}
}
add_action( 'add_meta_boxes', 'tf_seo_meta_box' );

/**
 * Render the SEO meta box.
 *
 * @param WP_Post $post Current post.
 */
function tf_seo_meta_box_render( $post ) {
	wp_nonce_field( 'tf_seo_meta_save', 'tf_seo_meta_nonce' );
	$title = get_post_meta( $post->ID, '_tf_title_tag', true );
	$meta  = get_post_meta( $post->ID, '_tf_meta_desc', true );
	?>
	<p>
		<label for="tf-title-tag"><strong><?php esc_html_e( 'Title tag', 'thinkfurther' ); ?></strong></label><br>
		<input type="text" style="width:100%" id="tf-title-tag" name="_tf_title_tag" maxlength="60" value="<?php echo esc_attr( $title ); ?>">
	</p>
	<p>
		<label for="tf-meta-desc"><strong><?php esc_html_e( 'Meta description', 'thinkfurther' ); ?></strong></label><br>
		<textarea style="width:100%" rows="4" id="tf-meta-desc" name="_tf_meta_desc" maxlength="155"><?php echo esc_textarea( $meta ); ?></textarea>
	</p>
	<?php
}

/**
 * Save SEO meta (enforcing character limits server-side).
 *
 * @param int $post_id Post ID.
 */
function tf_seo_meta_box_save( $post_id ) {
	if (
		! isset( $_POST['tf_seo_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['tf_seo_meta_nonce'] ) ), 'tf_seo_meta_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	if ( isset( $_POST['_tf_title_tag'] ) ) {
		update_post_meta( $post_id, '_tf_title_tag', mb_substr( sanitize_text_field( wp_unslash( $_POST['_tf_title_tag'] ) ), 0, 60 ) );
	}
	if ( isset( $_POST['_tf_meta_desc'] ) ) {
		update_post_meta( $post_id, '_tf_meta_desc', mb_substr( sanitize_text_field( wp_unslash( $_POST['_tf_meta_desc'] ) ), 0, 155 ) );
	}
}
add_action( 'save_post_tf_guide', 'tf_seo_meta_box_save' );
add_action( 'save_post_tf_glossary', 'tf_seo_meta_box_save' );

/**
 * Person page meta (job title, credentials, LinkedIn, knowsAbout) —
 * Tab 10 author-bio requirements. Applies to pages using person.php.
 */
function tf_person_meta_box() {
	add_meta_box( 'tf_person_meta', __( 'Consultant Profile (E-E-A-T)', 'thinkfurther' ), 'tf_person_meta_box_render', 'page', 'side' );
}
add_action( 'add_meta_boxes', 'tf_person_meta_box' );

/**
 * Render person meta box (only meaningful on person-template pages).
 *
 * @param WP_Post $post Current post.
 */
function tf_person_meta_box_render( $post ) {
	if ( 'page-templates/person.php' !== get_page_template_slug( $post ) ) {
		echo '<p class="description">' . esc_html__( 'Used when the page template is "Consultant Bio".', 'thinkfurther' ) . '</p>';
	}
	wp_nonce_field( 'tf_person_meta_save', 'tf_person_meta_nonce' );
	$fields = array(
		'_tf_job_title'   => __( 'Job title', 'thinkfurther' ),
		'_tf_credentials' => __( 'Credentials (e.g. MSc AI, 12 yrs)', 'thinkfurther' ),
		'_tf_linkedin'    => __( 'LinkedIn URL (sameAs)', 'thinkfurther' ),
		'_tf_knows_about' => __( 'knowsAbout (comma-separated)', 'thinkfurther' ),
		'_tf_alumni_of'   => __( 'alumniOf (optional)', 'thinkfurther' ),
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
 * Save person meta.
 *
 * @param int $post_id Post ID.
 */
function tf_person_meta_box_save( $post_id ) {
	if (
		! isset( $_POST['tf_person_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['tf_person_meta_nonce'] ) ), 'tf_person_meta_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}
	foreach ( array( '_tf_job_title', '_tf_credentials', '_tf_linkedin', '_tf_knows_about', '_tf_alumni_of' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
}
add_action( 'save_post_page', 'tf_person_meta_box_save' );
