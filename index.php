<?php
/**
 * Main index: blog stream fallback.
 *
 * @package ThinkFurther
 */

get_header();

$tf_blog_title = is_home() && get_option( 'page_for_posts' )
	? get_the_title( get_option( 'page_for_posts' ) )
	: __( 'Insights', 'thinkfurther' );

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'Insights', 'thinkfurther' ),
		'title'   => $tf_blog_title,
		'subline' => __( 'Practical thinking on AI transformation and AI search — written for operators, not hype cycles.', 'thinkfurther' ),
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

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();
