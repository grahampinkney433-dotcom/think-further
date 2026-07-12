<?php
/**
 * Empty-state for archives and search.
 *
 * @package ThinkFurther
 */
?>
<div class="content-none">
	<h2 class="content-none__title"><?php esc_html_e( 'Nothing here yet.', 'thinkfurther' ); ?></h2>
	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'No results matched your search. Try different keywords.', 'thinkfurther' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'New thinking is on its way. In the meantime, explore what we do.', 'thinkfurther' ); ?></p>
		<?php tf_arrow_link( home_url( '/' ), __( 'Back to home', 'thinkfurther' ) ); ?>
	<?php endif; ?>
</div>
