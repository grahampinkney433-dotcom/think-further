<?php
/**
 * Archives: category, tag, date, author.
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'Archive', 'thinkfurther' ),
		'title'   => get_the_archive_title(),
		'subline' => get_the_archive_description() ? wp_strip_all_tags( get_the_archive_description() ) : '',
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'Articles', 'thinkfurther' ); ?>">
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
