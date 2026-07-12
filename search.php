<?php
/**
 * Search results.
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'Search', 'thinkfurther' ),
		/* translators: %s: search query. */
		'title'   => sprintf( __( 'Results for “%s”', 'thinkfurther' ), get_search_query() ),
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'Search results', 'thinkfurther' ); ?>">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="insights__grid insights__grid--archive">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				?>
			</div>
			<div class="pagination-wrap">
				<?php
				the_posts_pagination(
					array(
						'prev_text' => '&larr; ' . __( 'Newer', 'thinkfurther' ),
						'next_text' => __( 'Older', 'thinkfurther' ) . ' &rarr;',
					)
				);
				?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
