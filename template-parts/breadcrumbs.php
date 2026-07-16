<?php
/**
 * Visible breadcrumbs (Tab 7). BreadcrumbList JSON-LD is emitted
 * separately in inc/seo.php from the same trail.
 *
 * @package ThinkFurther
 */

$tf_trail = tf_breadcrumb_trail();
if ( count( $tf_trail ) < 2 ) {
	return;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'thinkfurther' ); ?>">
	<ol class="breadcrumbs__list">
		<?php foreach ( $tf_trail as $tf_i => $tf_crumb ) : ?>
			<li class="breadcrumbs__item">
				<?php if ( ! empty( $tf_crumb[1] ) && $tf_i < count( $tf_trail ) - 1 ) : ?>
					<a href="<?php echo esc_url( $tf_crumb[1] ); ?>"><?php echo esc_html( $tf_crumb[0] ); ?></a>
				<?php else : ?>
					<span aria-current="page"><?php echo esc_html( $tf_crumb[0] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
