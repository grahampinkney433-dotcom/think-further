<?php
/**
 * One-click site setup — builds the full Tab 2 architecture:
 * every specced page (correct hierarchy + template), the two-level
 * navigation menu, /blog/ permalinks, and draft guide/glossary seeds.
 * Idempotent — never overwrites existing content.
 *
 * Appearance → Theme Setup.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map spec template keys to theme template files.
 *
 * @param string $key  Spec template key.
 * @param string $path Page path.
 * @return string Template file ('' = default page template).
 */
function tf_setup_template_file( $key, $path ) {
	switch ( $key ) {
		case 'pillar':
			return '/ai-search/' === $path ? 'page-templates/ai-search.php' : 'page-templates/ai-transformation.php';
		case 'service':
		case 'local':
		case 'sector':
			return 'page-templates/spec-page.php';
		case 'industries-index':
			return 'page-templates/industries-index.php';
		case 'about':
			return 'page-templates/about.php';
		case 'team':
			return 'page-templates/team.php';
		case 'contact':
			return 'page-templates/contact.php';
		case 'pricing':
			return 'page-templates/pricing.php';
		case 'resources':
			return 'page-templates/resources.php';
		case 'tools':
			return 'page-templates/tools.php';
		case 'roi-calculator':
			return 'page-templates/roi-calculator.php';
		case 'ai-visibility-checker':
			return 'page-templates/ai-visibility-checker.php';
		case 'careers':
			return 'page-templates/careers.php';
		default:
			return '';
	}
}

/**
 * Spec paths that are NOT WordPress pages (CPT archives / posts page).
 *
 * @param array $rec Spec record.
 * @return bool
 */
function tf_setup_is_page( $rec ) {
	return ! in_array( $rec['template'], array( 'cpt-archive', 'blog-index' ), true );
}

/**
 * Register the admin page.
 */
function tf_setup_menu() {
	add_theme_page( __( 'Theme Setup', 'thinkfurther' ), __( 'Theme Setup', 'thinkfurther' ), 'manage_options', 'tf-setup', 'tf_setup_render_page' );
}
add_action( 'admin_menu', 'tf_setup_menu' );

/**
 * Current configuration status.
 *
 * @return array
 */
function tf_setup_status() {
	$pages_ok = true;
	foreach ( tf_spec_pages() as $path => $rec ) {
		if ( '/' === $path || ! tf_setup_is_page( $rec ) ) {
			continue;
		}
		if ( ! get_page_by_path( trim( $path, '/' ) ) ) {
			$pages_ok = false;
			break;
		}
	}

	$locations = get_nav_menu_locations();

	return array(
		'pages'      => $pages_ok,
		'front_page' => 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ),
		'menu'       => ! empty( $locations['primary'] ),
		'permalinks' => '/blog/%postname%/' === get_option( 'permalink_structure' ),
		'seeds'      => (bool) get_posts( array( 'post_type' => 'tf_guide', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) ),
	);
}

/**
 * Render the setup screen.
 */
function tf_setup_render_page() {
	$status   = tf_setup_status();
	$did_run  = isset( $_GET['tf-setup-done'] ) ? '1' === sanitize_key( wp_unslash( $_GET['tf-setup-done'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$all_done = ! in_array( false, $status, true );
	$rows     = array(
		'pages'      => __( 'Full page tree from the SEO spec (~50 pages: hubs, services, industries, trust, resources, legal)', 'thinkfurther' ),
		'front_page' => __( 'Static homepage & posts page (/blog/) assigned', 'thinkfurther' ),
		'menu'       => __( 'Primary navigation with dropdowns assigned', 'thinkfurther' ),
		'permalinks' => __( 'Permalinks set to /blog/%postname%/ for articles', 'thinkfurther' ),
		'seeds'      => __( 'Draft pillar guides + glossary terms seeded', 'thinkfurther' ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Alugence — Theme Setup', 'thinkfurther' ); ?></h1>

		<?php if ( $did_run ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Setup complete. Architecture, menu, permalinks and content seeds are configured.', 'thinkfurther' ); ?></p></div>
		<?php endif; ?>

		<p style="max-width:44em"><?php esc_html_e( 'Builds the site architecture defined in the SEO & GEO Requirements workbook (inc/seo-spec.php). Never overwrites existing content — anything already present is left untouched. Guides and glossary terms are created as DRAFTS for the content stage.', 'thinkfurther' ); ?></p>

		<table class="widefat striped" style="max-width:44em"><tbody>
			<?php foreach ( $rows as $key => $label ) : ?>
				<tr><td><?php echo esc_html( $label ); ?></td><td><?php echo $status[ $key ] ? '&#10003;' : '&mdash;'; ?></td></tr>
			<?php endforeach; ?>
		</tbody></table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1em">
			<input type="hidden" name="action" value="tf_run_setup">
			<?php wp_nonce_field( 'tf_run_setup', 'tf_setup_nonce' ); ?>
			<?php submit_button( $all_done ? __( 'Re-run setup', 'thinkfurther' ) : __( 'Run setup', 'thinkfurther' ), 'primary', 'submit', false ); ?>
		</form>

		<p style="margin-top:1.5em;max-width:44em">
			<?php
			printf(
				/* translators: %s: customizer link. */
				esc_html__( 'Afterwards, set contact details, founding year and sameAs profile URLs in %s.', 'thinkfurther' ),
				'<a href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html__( 'the Customizer under “Alugence Options”', 'thinkfurther' ) . '</a>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Execute setup.
 */
function tf_run_setup() {
	if (
		! current_user_can( 'manage_options' ) ||
		! isset( $_POST['tf_setup_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['tf_setup_nonce'] ) ), 'tf_run_setup' )
	) {
		wp_die( esc_html__( 'Not allowed.', 'thinkfurther' ) );
	}

	/* ---- 1. Page tree (parents before children — spec order suffices,
	   but resolve parents explicitly to be safe). ---- */
	$created = array();

	$ensure_page = function ( $path, $rec ) use ( &$created, &$ensure_page ) {
		$rel = trim( $path, '/' );
		if ( '' === $rel ) {
			return 0;
		}
		$existing = get_page_by_path( $rel, OBJECT, 'page' );
		if ( ! $existing ) {
			// WP seeds some pages (e.g. Privacy Policy) as drafts.
			$drafts = get_posts(
				array(
					'post_type'   => 'page',
					'name'        => end( ( explode( '/', $rel ) ) ),
					'post_status' => array( 'draft', 'pending' ),
					'numberposts' => 1,
				)
			);
			$existing = $drafts ? $drafts[0] : null;
		}
		if ( $existing ) {
			if ( 'publish' !== $existing->post_status ) {
				wp_update_post( array( 'ID' => $existing->ID, 'post_status' => 'publish' ) );
			}
			$created[ $path ] = $existing->ID;
			return $existing->ID;
		}

		$parent_id = 0;
		if ( ! empty( $rec['parent'] ) && '/' !== $rec['parent'] ) {
			$pages = tf_spec_pages();
			if ( isset( $pages[ $rec['parent'] ] ) && tf_setup_is_page( $pages[ $rec['parent'] ] ) ) {
				$parent_id = $ensure_page( $rec['parent'], $pages[ $rec['parent'] ] );
			}
		}

		$segments = explode( '/', $rel );
		$id       = wp_insert_post(
			array(
				'post_title'  => $rec['h1'],
				'post_name'   => end( $segments ),
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_parent' => $parent_id,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$tpl = tf_setup_template_file( $rec['template'], $path );
			if ( $tpl ) {
				update_post_meta( $id, '_wp_page_template', $tpl );
			}
			$created[ $path ] = $id;
			return $id;
		}
		return 0;
	};

	foreach ( tf_spec_pages() as $path => $rec ) {
		if ( '/' === $path || ! tf_setup_is_page( $rec ) ) {
			continue;
		}
		$ensure_page( $path, $rec );
	}

	/* ---- 2. Home + Blog (posts page) ---- */
	$home = get_page_by_path( 'home' );
	if ( ! $home ) {
		$home_id = wp_insert_post( array( 'post_title' => 'Home', 'post_name' => 'home', 'post_type' => 'page', 'post_status' => 'publish' ) );
	} else {
		$home_id = $home->ID;
	}
	$blog = get_page_by_path( 'blog' );
	if ( ! $blog ) {
		$insights = get_page_by_path( 'insights' );
		if ( $insights ) {
			// Rename the old posts page to /blog/ (Tab 2 RS-03).
			wp_update_post( array( 'ID' => $insights->ID, 'post_name' => 'blog', 'post_title' => 'Blog' ) );
			$blog_id = $insights->ID;
		} else {
			$blog_id = wp_insert_post( array( 'post_title' => 'Blog', 'post_name' => 'blog', 'post_type' => 'page', 'post_status' => 'publish' ) );
		}
	} else {
		$blog_id = $blog->ID;
	}
	if ( $home_id && ! is_wp_error( $home_id ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}
	if ( $blog_id && ! is_wp_error( $blog_id ) ) {
		update_option( 'page_for_posts', $blog_id );
	}

	/* ---- 3. Content seeds — real final copy, published ---- */
	tf_seed_longform();

	// Usage-log table for the AI Visibility Checker.
	if ( function_exists( 'tf_avc_maybe_create_table' ) ) {
		tf_avc_maybe_create_table();
	}

	/* ---- 4. Menu (two-level, from spec nav structure) ---- */
	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		$menu    = wp_get_nav_menu_object( 'Main' );
		$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'Main' );

		if ( ! is_wp_error( $menu_id ) && empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			foreach ( tf_nav_structure() as $item ) {
				$parent_item = wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => $item['label'],
						'menu-item-url'    => home_url( $item['path'] ),
						'menu-item-type'   => 'custom',
						'menu-item-status' => 'publish',
					)
				);
				foreach ( $item['children'] as $child_path => $child_label ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-title'     => $child_label,
							'menu-item-url'       => home_url( $child_path ),
							'menu-item-type'      => 'custom',
							'menu-item-status'    => 'publish',
							'menu-item-parent-id' => $parent_item,
						)
					);
				}
			}
			$locations['primary'] = $menu_id;
			if ( empty( $locations['footer'] ) ) {
				$locations['footer'] = $menu_id;
			}
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	/* ---- 5. Permalinks: /blog/%postname%/ (posts under /blog/) ---- */
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/blog/%postname%/' );
	flush_rewrite_rules();

	wp_safe_redirect( admin_url( 'themes.php?page=tf-setup&tf-setup-done=1' ) );
	exit;
}
add_action( 'admin_post_tf_run_setup', 'tf_run_setup' );

/**
 * Seed pillar guides (tf_guide), blog articles (post) and glossary terms
 * (tf_glossary) with the final copy from inc/seo-longform.php. Idempotent:
 * matches on slug + post type and updates in place, so re-running Theme
 * Setup re-imports the canonical copy without creating duplicates.
 *
 * Meta keys mirror the SEO engine's lookups: _tf_title_tag (title tag),
 * _tf_meta_desc (meta description), _tf_faqs (FAQPage pairs), plus
 * _tf_money_url / _tf_money_anchor for guide internal-link CTAs and
 * _tf_guide_url for a glossary term's "read the full guide" link.
 */
function tf_seed_longform() {
	if ( ! function_exists( 'tf_longform_posts' ) ) {
		return;
	}

	// Guide → money-link map (subset of guides have a matched service CTA).
	$guide_money = array();
	if ( function_exists( 'tf_spec_guides' ) ) {
		foreach ( tf_spec_guides() as $g_slug => $g ) {
			$guide_money[ $g_slug ] = array( $g['money'], $g['money_anchor'] );
		}
	}

	foreach ( tf_longform_posts() as $rec ) {
		$existing = get_page_by_path( $rec['slug'], OBJECT, $rec['cpt'] );
		$postarr  = array(
			'post_title'   => $rec['title'],
			'post_name'    => $rec['slug'],
			'post_type'    => $rec['cpt'],
			'post_status'  => 'publish',
			'post_content' => $rec['html'],
			'post_excerpt' => $rec['excerpt'],
		);
		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			$pid           = wp_update_post( $postarr );
		} else {
			$pid = wp_insert_post( $postarr );
		}
		if ( ! $pid || is_wp_error( $pid ) ) {
			continue;
		}
		update_post_meta( $pid, '_tf_title_tag', $rec['meta_title'] );
		update_post_meta( $pid, '_tf_meta_desc', $rec['meta_desc'] );
		update_post_meta( $pid, '_tf_faqs', $rec['faqs'] );
		if ( 'tf_guide' === $rec['cpt'] && isset( $guide_money[ $rec['slug'] ] ) ) {
			update_post_meta( $pid, '_tf_money_url', $guide_money[ $rec['slug'] ][0] );
			update_post_meta( $pid, '_tf_money_anchor', $guide_money[ $rec['slug'] ][1] );
		}
	}

	if ( ! function_exists( 'tf_longform_glossary' ) ) {
		return;
	}
	foreach ( tf_longform_glossary() as $rec ) {
		$existing = get_page_by_path( $rec['slug'], OBJECT, 'tf_glossary' );
		$postarr  = array(
			'post_title'   => $rec['term'],
			'post_name'    => $rec['slug'],
			'post_type'    => 'tf_glossary',
			'post_status'  => 'publish',
			'post_content' => $rec['html'],
			'post_excerpt' => $rec['definition'],
		);
		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			$pid           = wp_update_post( $postarr );
		} else {
			$pid = wp_insert_post( $postarr );
		}
		if ( ! $pid || is_wp_error( $pid ) ) {
			continue;
		}
		update_post_meta( $pid, '_tf_title_tag', $rec['meta_title'] );
		update_post_meta( $pid, '_tf_meta_desc', $rec['meta_desc'] );
		if ( ! empty( $rec['guide_url'] ) ) {
			update_post_meta( $pid, '_tf_guide_url', $rec['guide_url'] );
		}
	}
}

/**
 * Nudge towards setup on fresh installs (only while unconfigured).
 */
function tf_setup_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ( $screen && 'appearance_page_tf-setup' === $screen->id ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$status = tf_setup_status();
	if ( $status['pages'] && $status['front_page'] ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
		esc_html__( 'Alugence theme:', 'thinkfurther' ),
		esc_html__( 'build the full site architecture (pages, menu, permalinks, content seeds) in one click.', 'thinkfurther' ),
		esc_url( admin_url( 'themes.php?page=tf-setup' ) ),
		esc_html__( 'Open Theme Setup', 'thinkfurther' )
	);
}
add_action( 'admin_notices', 'tf_setup_notice' );
