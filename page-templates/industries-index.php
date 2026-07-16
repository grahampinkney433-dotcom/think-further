<?php
/**
 * Template Name: Industries Index
 * Template Post Type: page
 *
 * /industries/ — sector hub linking to all vertical pages (Tab 2 PC-03).
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec    = tf_spec_for_path();
$tf_sectors = tf_spec_children( '/industries/' );
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Industries', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec ? $tf_spec['meta'] : '' ); ?></p>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'Sectors', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="insights__grid">
			<?php foreach ( $tf_sectors as $tf_path => $tf_sector ) : ?>
				<article class="post-card">
					<a class="post-card__link" href="<?php echo esc_url( home_url( $tf_path ) ); ?>">
						<div class="post-card__body">
							<h2 class="post-card__title"><?php echo esc_html( $tf_sector['h1'] ); ?></h2>
							<p class="post-card__excerpt"><?php echo esc_html( implode( ' · ', array_slice( $tf_sector['usecases'], 0, 3 ) ) ); ?></p>
							<span class="post-card__more"><?php esc_html_e( 'Explore sector', 'thinkfurther' ); ?> <span aria-hidden="true">&rarr;</span></span>
						</div>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta' );
get_footer();
