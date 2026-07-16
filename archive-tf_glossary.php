<?php
/**
 * Glossary index (/glossary/) — A–Z entity index (Tab 2 RS-05).
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec = tf_spec_for_path( '/glossary/' );
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Glossary', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec['h1'] ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec['meta'] ); ?></p>
	</div>
</section>

<section class="section">
	<div class="container container--prose">
		<?php if ( have_posts() ) : ?>
			<dl class="glossary-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<div class="glossary-item">
						<dt><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></dt>
						<dd><?php echo esc_html( has_excerpt() ? get_the_excerpt() : '[Definition placeholder — added at content stage.]' ); ?></dd>
					</div>
				<?php endwhile; ?>
			</dl>
			<div class="pagination-wrap"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta' );
get_footer();
