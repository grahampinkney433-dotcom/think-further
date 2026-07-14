<?php
/**
 * One-click site setup: creates the core pages, menu and reading
 * settings so a fresh install matches the intended site structure.
 *
 * Appearance → Theme Setup. Idempotent — safe to run repeatedly.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The pages the theme expects, keyed by slug.
 *
 * @return array
 */
function tf_setup_page_map() {
	return array(
		'home'           => array( __( 'Home', 'thinkfurther' ), '' ),
		'ai-consultancy' => array( __( 'AI Consultancy', 'thinkfurther' ), 'page-templates/ai-transformation.php' ),
		'ai-search'      => array( __( 'AI Search', 'thinkfurther' ), 'page-templates/ai-search.php' ),
		'approach'       => array( __( 'Approach', 'thinkfurther' ), 'page-templates/approach.php' ),
		'about'          => array( __( 'About', 'thinkfurther' ), 'page-templates/about.php' ),
		'ai-audit'       => array( __( 'AI Audit', 'thinkfurther' ), 'page-templates/audit.php' ),
		'contact'        => array( __( 'Contact', 'thinkfurther' ), 'page-templates/contact.php' ),
		'insights'       => array( __( 'Insights', 'thinkfurther' ), '' ),
	);
}

/**
 * Register the admin page.
 */
function tf_setup_menu() {
	add_theme_page(
		__( 'Theme Setup', 'thinkfurther' ),
		__( 'Theme Setup', 'thinkfurther' ),
		'manage_options',
		'tf-setup',
		'tf_setup_render_page'
	);
}
add_action( 'admin_menu', 'tf_setup_menu' );

/**
 * Current configuration status.
 *
 * @return array
 */
function tf_setup_status() {
	$pages_ok = true;
	foreach ( tf_setup_page_map() as $slug => $page ) {
		if ( ! get_page_by_path( $slug ) ) {
			$pages_ok = false;
			break;
		}
	}

	$locations = get_nav_menu_locations();

	return array(
		'pages'      => $pages_ok,
		'front_page' => 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ),
		'menu'       => ! empty( $locations['primary'] ),
		'permalinks' => (bool) get_option( 'permalink_structure' ),
	);
}

/**
 * Render the setup screen.
 */
function tf_setup_render_page() {
	$status   = tf_setup_status();
	$did_run  = isset( $_GET['tf-setup-done'] ) ? '1' === sanitize_key( wp_unslash( $_GET['tf-setup-done'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$all_done = ! in_array( false, $status, true );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Alugence — Theme Setup', 'thinkfurther' ); ?></h1>

		<?php if ( $did_run ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Setup complete. Pages, menu and homepage settings are configured.', 'thinkfurther' ); ?></p></div>
		<?php endif; ?>

		<p style="max-width:44em">
			<?php esc_html_e( 'This creates the pages the theme is designed around (with the right templates), assigns the navigation menu, sets the static homepage and posts page, and enables pretty permalinks. It never overwrites existing content — anything already present is left untouched.', 'thinkfurther' ); ?>
		</p>

		<table class="widefat striped" style="max-width:44em">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Core pages (Home, AI Consultancy, AI Search, AI Audit, Approach, About, Contact, Insights)', 'thinkfurther' ); ?></td>
					<td><?php echo $status['pages'] ? '&#10003;' : '&mdash;'; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Static homepage & posts page assigned', 'thinkfurther' ); ?></td>
					<td><?php echo $status['front_page'] ? '&#10003;' : '&mdash;'; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Primary navigation menu assigned', 'thinkfurther' ); ?></td>
					<td><?php echo $status['menu'] ? '&#10003;' : '&mdash;'; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Pretty permalinks enabled', 'thinkfurther' ); ?></td>
					<td><?php echo $status['permalinks'] ? '&#10003;' : '&mdash;'; ?></td>
				</tr>
			</tbody>
		</table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1em">
			<input type="hidden" name="action" value="tf_run_setup">
			<?php wp_nonce_field( 'tf_run_setup', 'tf_setup_nonce' ); ?>
			<?php submit_button( $all_done ? __( 'Re-run setup', 'thinkfurther' ) : __( 'Run setup', 'thinkfurther' ), 'primary', 'submit', false ); ?>
		</form>

		<p style="margin-top:1.5em;max-width:44em">
			<?php
			printf(
				/* translators: %s: customizer link. */
				esc_html__( 'Afterwards, set your contact details and hero copy in %s.', 'thinkfurther' ),
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

	$ids = array();

	foreach ( tf_setup_page_map() as $slug => $page ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_title'  => $page[0],
				'post_name'   => $slug,
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			if ( $page[1] ) {
				update_post_meta( $id, '_wp_page_template', $page[1] );
			}
			$ids[ $slug ] = $id;
		}
	}

	// Reading settings.
	if ( isset( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}
	if ( isset( $ids['insights'] ) ) {
		update_option( 'page_for_posts', $ids['insights'] );
	}

	// Menu.
	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		$menu = wp_get_nav_menu_object( 'Main' );
		$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'Main' );

		if ( ! is_wp_error( $menu_id ) ) {
			$items = wp_get_nav_menu_items( $menu_id );
			if ( empty( $items ) ) {
				foreach ( array( 'ai-consultancy', 'ai-search', 'ai-audit', 'approach', 'about', 'contact' ) as $slug ) {
					if ( isset( $ids[ $slug ] ) ) {
						wp_update_nav_menu_item(
							$menu_id,
							0,
							array(
								'menu-item-title'     => get_the_title( $ids[ $slug ] ),
								'menu-item-object'    => 'page',
								'menu-item-object-id' => $ids[ $slug ],
								'menu-item-type'      => 'post_type',
								'menu-item-status'    => 'publish',
							)
						);
					}
				}
			}
			$locations['primary'] = $menu_id;
			if ( empty( $locations['footer'] ) ) {
				$locations['footer'] = $menu_id;
			}
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	// Permalinks.
	if ( ! get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
	}
	flush_rewrite_rules();

	wp_safe_redirect( admin_url( 'themes.php?page=tf-setup&tf-setup-done=1' ) );
	exit;
}
add_action( 'admin_post_tf_run_setup', 'tf_run_setup' );

/**
 * Nudge towards setup on fresh installs (only while unconfigured).
 */
function tf_setup_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'appearance_page_tf-setup' === $screen->id ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$status = tf_setup_status();
	if ( $status['pages'] && $status['front_page'] ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
		esc_html__( 'Alugence theme:', 'thinkfurther' ),
		esc_html__( 'finish setting up the site structure (pages, menu, homepage) in one click.', 'thinkfurther' ),
		esc_url( admin_url( 'themes.php?page=tf-setup' ) ),
		esc_html__( 'Open Theme Setup', 'thinkfurther' )
	);
}
add_action( 'admin_notices', 'tf_setup_notice' );
