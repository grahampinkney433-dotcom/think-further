<?php
/**
 * Compact dark hero for interior pages.
 *
 * Accepts via get_template_part $args:
 * - eyebrow  (string)
 * - title    (string, supports *emphasis*)
 * - subline  (string)
 *
 * @package ThinkFurther
 */

$tf_eyebrow = isset( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$tf_title   = isset( $args['title'] ) ? $args['title'] : get_the_title();
$tf_subline = isset( $args['subline'] ) ? $args['subline'] : '';
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php
		get_template_part( 'template-parts/breadcrumbs' );
		if ( $tf_eyebrow ) {
			tf_eyebrow( $tf_eyebrow, 'reveal' );
		}
		?>
		<h1 class="page-hero__title reveal"><?php echo wp_kses( tf_headline( $tf_title ), array( 'em' => array() ) ); ?></h1>
		<?php if ( $tf_subline ) : ?>
			<p class="page-hero__subline reveal"><?php echo esc_html( $tf_subline ); ?></p>
		<?php endif; ?>
	</div>
</section>
