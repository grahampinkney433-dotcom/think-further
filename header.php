<?php
/**
 * Site header.
 *
 * @package ThinkFurther
 */
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'thinkfurther' ); ?></a>

<header class="site-header" data-header>
	<div class="site-header__inner container">
		<div class="site-header__brand">
			<?php tf_logo(); ?>
		</div>

		<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'thinkfurther' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-nav__list',
					'fallback_cb'    => 'tf_primary_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
			<div class="site-nav__cta">
				<a class="btn btn--solid" href="<?php echo esc_url( tf_cta_url() ); ?>"><?php echo esc_html( tf_option( 'tf_hero_cta' ) ); ?></a>
			</div>
		</nav>

		<button class="nav-toggle" data-nav-toggle aria-expanded="false" aria-controls="site-nav">
			<span class="nav-toggle__line" aria-hidden="true"></span>
			<span class="nav-toggle__line" aria-hidden="true"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'thinkfurther' ); ?></span>
		</button>
	</div>
</header>

<main id="main" class="site-main">
