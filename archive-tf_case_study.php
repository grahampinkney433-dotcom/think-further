<?php
/**
 * Case study archive: /work/.
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'Selected work', 'thinkfurther' ),
		'title'   => __( 'Outcomes, *not anecdotes.*', 'thinkfurther' ),
		'subline' => __( 'A selection of engagements across both practices — each with the baseline we started from and the result we measured.', 'thinkfurther' ),
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'Case studies', 'thinkfurther' ); ?>">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="case-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'case' );
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

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();
