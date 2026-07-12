<?php
/**
 * 404 page.
 *
 * @package ThinkFurther
 */

get_header();
?>

<section class="page-hero page-hero--404 theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<p class="error-code" aria-hidden="true">404</p>
		<h1 class="page-hero__title reveal"><?php echo wp_kses( tf_headline( __( 'This page didn\'t make the answer. *You still can.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h1>
		<p class="page-hero__subline reveal"><?php esc_html_e( 'The page you\'re looking for has moved or never existed. Here\'s where to go instead.', 'thinkfurther' ); ?></p>
		<div class="hero__actions reveal">
			<a class="btn btn--light btn--lg" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'thinkfurther' ); ?></a>
			<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( tf_cta_url() ); ?>"><?php esc_html_e( 'Talk to us', 'thinkfurther' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
