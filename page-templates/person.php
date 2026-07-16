<?php
/**
 * Template Name: Consultant Bio
 * Template Post Type: page
 *
 * /about/team/{person}/ — author entity page (Tab 2 TR-03, Tab 10):
 * Person schema, credentials, LinkedIn sameAs, authored-content list.
 *
 * @package ThinkFurther
 */

get_header();

$tf_pid   = get_queried_object_id();
$tf_job   = get_post_meta( $tf_pid, '_tf_job_title', true );
$tf_creds = get_post_meta( $tf_pid, '_tf_credentials', true );
$tf_li    = get_post_meta( $tf_pid, '_tf_linkedin', true );
$tf_knows = get_post_meta( $tf_pid, '_tf_knows_about', true );
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( $tf_job ? $tf_job : __( 'Consultant', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php the_title(); ?></h1>
		<?php if ( $tf_creds ) : ?>
			<p class="page-hero__subline reveal"><?php echo esc_html( $tf_creds ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="prose-media"><?php the_post_thumbnail( 'tf-card' ); ?></figure>
				<?php endif; ?>
				<?php if ( $tf_knows ) : ?>
					<h2 class="contact-layout__heading"><?php esc_html_e( 'Expertise', 'thinkfurther' ); ?></h2>
					<div class="single-post__tags">
						<?php foreach ( array_map( 'trim', explode( ',', $tf_knows ) ) as $tf_topic ) : ?>
							<span class="tag-pill"><?php echo esc_html( $tf_topic ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( $tf_li ) : ?>
					<p style="margin-top:1.5rem"><a class="arrow-link" href="<?php echo esc_url( $tf_li ); ?>" rel="noopener"><span class="arrow-link__label"><?php esc_html_e( 'LinkedIn profile', 'thinkfurther' ); ?></span><span class="arrow-link__arrow" aria-hidden="true">&rarr;</span></a></p>
				<?php endif; ?>
			</div>
			<div class="split__body">
				<h2><?php esc_html_e( 'Bio', 'thinkfurther' ); ?></h2>
				<?php
				while ( have_posts() ) :
					the_post();
					if ( trim( get_the_content() ) ) {
						echo '<div class="prose">';
						the_content();
						echo '</div>';
					} else {
						tf_placeholder( 'Consultant bio', 'Real name, role, credentials, years of experience and specialisms — written in first person plural or third person per tone-of-voice guide (Tab 10).', 180 );
					}
				endwhile;

				// Authored content (E-E-A-T: bio -> authored articles).
				$tf_user = get_user_by( 'slug', get_post_field( 'post_name', $tf_pid ) );
				if ( $tf_user ) {
					$tf_authored = new WP_Query(
						array(
							'post_type'      => array( 'post', 'tf_guide' ),
							'author'         => $tf_user->ID,
							'posts_per_page' => 6,
							'no_found_rows'  => true,
						)
					);
					if ( $tf_authored->have_posts() ) {
						echo '<h2>' . esc_html__( 'Written by this consultant', 'thinkfurther' ) . '</h2><ul>';
						while ( $tf_authored->have_posts() ) {
							$tf_authored->the_post();
							printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
						}
						echo '</ul>';
						wp_reset_postdata();
					}
				}
				?>
			</div>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta' );
get_footer();
