<?php
/**
 * Default page template.
 *
 * @package ThinkFurther
 */

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'eyebrow' => __( 'Alugence', 'thinkfurther' ),
			'title'   => get_the_title(),
		)
	);
	?>

	<section class="section">
		<div class="container container--prose">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="prose-media reveal">
					<?php the_post_thumbnail( 'post-thumbnail', array( 'loading' => 'lazy' ) ); ?>
				</figure>
			<?php endif; ?>
			<div class="prose">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'thinkfurther' ),
						'after'  => '</nav>',
					)
				);
				?>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_template_part( 'template-parts/cta' );

get_footer();
