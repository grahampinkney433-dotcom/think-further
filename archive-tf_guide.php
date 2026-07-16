<?php
/**
 * Guides index (/guides/) — Tab 2 RS-01.
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec = tf_spec_for_path( '/guides/' );
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Guides', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec['h1'] ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec['meta'] ); ?></p>
	</div>
</section>

<section class="section">
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
			<div class="pagination-wrap"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta' );
get_footer();
