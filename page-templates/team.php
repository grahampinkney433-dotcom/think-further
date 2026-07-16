<?php
/**
 * Template Name: Team Index
 * Template Post Type: page
 *
 * /about/team/ — named consultants grid (Tab 10). Lists child pages
 * using the Consultant Bio template.
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec   = tf_spec_for_path();
$tf_people = get_pages(
	array(
		'parent'    => get_queried_object_id(),
		'sort_column' => 'menu_order,post_title',
	)
);
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Our team', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec ? $tf_spec['meta'] : '' ); ?></p>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'Consultants', 'thinkfurther' ); ?>">
	<div class="container">
		<?php if ( $tf_people ) : ?>
			<div class="insights__grid">
				<?php foreach ( $tf_people as $tf_person ) : ?>
					<article class="post-card">
						<a class="post-card__link" href="<?php echo esc_url( get_permalink( $tf_person ) ); ?>">
							<?php if ( has_post_thumbnail( $tf_person ) ) : ?>
								<div class="post-card__media"><?php echo get_the_post_thumbnail( $tf_person, 'tf-card', array( 'loading' => 'lazy' ) ); ?></div>
							<?php endif; ?>
							<div class="post-card__body">
								<h2 class="post-card__title"><?php echo esc_html( get_the_title( $tf_person ) ); ?></h2>
								<p class="post-card__excerpt"><?php echo esc_html( get_post_meta( $tf_person->ID, '_tf_job_title', true ) ); ?></p>
								<span class="post-card__more"><?php esc_html_e( 'View profile', 'thinkfurther' ); ?> <span aria-hidden="true">&rarr;</span></span>
							</div>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<?php tf_placeholder( 'Team members', 'Create child pages of /about/team/ using the "Consultant Bio" template — one per named consultant, with photo, role, credentials and LinkedIn (Tab 10, P1).', 0 ); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta' );
get_footer();
