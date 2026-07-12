<?php
/**
 * Post card for grids (insights, archives).
 *
 * @package ThinkFurther
 */
?>
<article <?php post_class( 'post-card reveal' ); ?>>
	<a class="post-card__link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-card__media">
				<?php the_post_thumbnail( 'tf-card', array( 'loading' => 'lazy' ) ); ?>
			</div>
		<?php else : ?>
			<div class="post-card__media post-card__media--pattern" aria-hidden="true">
				<span class="post-card__glyph">&rarr;</span>
			</div>
		<?php endif; ?>
		<div class="post-card__body">
			<?php tf_post_meta(); ?>
			<h3 class="post-card__title"><?php the_title(); ?></h3>
			<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
			<span class="post-card__more">
				<?php esc_html_e( 'Read', 'thinkfurther' ); ?> <span aria-hidden="true">&rarr;</span>
			</span>
		</div>
	</a>
</article>
