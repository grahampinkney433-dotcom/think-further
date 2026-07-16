<?php
/**
 * Single case study.
 *
 * @package ThinkFurther
 */

get_header();

while ( have_posts() ) :
	the_post();

	$tf_client = get_post_meta( get_the_ID(), '_tf_client', true );
	$tf_sector = get_post_meta( get_the_ID(), '_tf_sector', true );
	$tf_result = get_post_meta( get_the_ID(), '_tf_result', true );
	?>

	<article <?php post_class( 'single-case' ); ?>>
		<header class="page-hero theme-dark">
			<div class="page-hero__grid" aria-hidden="true"></div>
			<div class="container">
				<div class="single-post__meta reveal">
					<span><?php esc_html_e( 'Case study', 'thinkfurther' ); ?></span>
					<?php if ( $tf_client ) : ?>
						<span class="post-meta__sep" aria-hidden="true">·</span>
						<span><?php echo esc_html( $tf_client ); ?></span>
					<?php endif; ?>
					<?php if ( $tf_sector ) : ?>
						<span class="post-meta__sep" aria-hidden="true">·</span>
						<span><?php echo esc_html( $tf_sector ); ?></span>
					<?php endif; ?>
				</div>
				<h1 class="page-hero__title reveal"><?php the_title(); ?></h1>
				<?php if ( $tf_result ) : ?>
					<p class="case-hero__result reveal"><?php echo esc_html( $tf_result ); ?></p>
				<?php endif; ?>
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
					<?php the_content(); ?>
				</div>

				<h2><?php esc_html_e( 'Services used', 'thinkfurther' ); ?></h2>
				<div class="about-links">
					<?php tf_arrow_link( home_url( '/ai-consultancy/' ), __( 'AI consultancy services', 'thinkfurther' ) ); ?>
					<?php tf_arrow_link( home_url( '/ai-search/' ), __( 'AI search services', 'thinkfurther' ) ); ?>
				</div>

				<nav class="post-nav" aria-label="<?php esc_attr_e( 'Case study navigation', 'thinkfurther' ); ?>">
					<div class="post-nav__prev"><?php previous_post_link( '%link', '&larr; %title' ); ?></div>
					<div class="post-nav__next"><?php next_post_link( '%link', '%title &rarr;' ); ?></div>
				</nav>
			</div>
		</div>
	</article>

	<?php
endwhile;

get_template_part( 'template-parts/cta' );

get_footer();
