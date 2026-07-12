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

	<div class="reading-progress" aria-hidden="true"><span class="reading-progress__bar" data-progress-bar></span></div>

	<article <?php post_class( 'single-post' ); ?> data-progress-target>
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
	$tf_related_args = array(
		'posts_per_page'      => 3,
		'post__not_in'        => array( get_the_ID() ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$tf_categories    = wp_get_post_categories( get_the_ID() );
	if ( $tf_categories ) {
		$tf_related_args['category__in'] = $tf_categories;
	}
	$tf_related = new WP_Query( $tf_related_args );
	if ( $tf_related->have_posts() ) :
		?>
		<section class="insights section section--tight related" aria-label="<?php esc_attr_e( 'Related articles', 'thinkfurther' ); ?>">
			<div class="container">
				<div class="section-head">
					<?php tf_eyebrow( __( 'Keep reading', 'thinkfurther' ), 'reveal' ); ?>
					<h2 class="section-head__title reveal"><?php esc_html_e( 'Related thinking.', 'thinkfurther' ); ?></h2>
				</div>
				<div class="insights__grid">
					<?php
					while ( $tf_related->have_posts() ) :
						$tf_related->the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_template_part( 'template-parts/cta' );

get_footer();
