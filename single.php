<?php
/**
 * Single post.
 *
 * @package ThinkFurther
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<article <?php post_class( 'single-post' ); ?>>
		<header class="page-hero theme-dark">
			<div class="page-hero__grid" aria-hidden="true"></div>
			<div class="container container--prose">
				<div class="single-post__meta reveal">
					<?php tf_post_meta(); ?>
					<span class="post-meta__sep" aria-hidden="true">·</span>
					<span><?php echo esc_html( tf_reading_time() ); ?></span>
				</div>
				<h1 class="page-hero__title page-hero__title--article reveal"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="page-hero__subline reveal"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<div class="section">
			<div class="container container--prose">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="prose-media reveal">
						<?php the_post_thumbnail( 'post-thumbnail' ); ?>
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

				<?php
				$tf_tags = get_the_tags();
				if ( $tf_tags ) :
					?>
					<div class="single-post__tags">
						<?php foreach ( $tf_tags as $tf_tag ) : ?>
							<a class="tag-pill" href="<?php echo esc_url( get_tag_link( $tf_tag ) ); ?>"><?php echo esc_html( $tf_tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<nav class="post-nav" aria-label="<?php esc_attr_e( 'Post navigation', 'thinkfurther' ); ?>">
					<div class="post-nav__prev"><?php previous_post_link( '%link', '&larr; %title' ); ?></div>
					<div class="post-nav__next"><?php next_post_link( '%link', '%title &rarr;' ); ?></div>
				</nav>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</div>
	</article>

	<?php
endwhile;

get_template_part( 'template-parts/cta' );

get_footer();
