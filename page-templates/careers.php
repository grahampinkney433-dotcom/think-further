<?php
/**
 * Template Name: Careers
 * Template Post Type: page
 *
 * /careers/ — trust page (Tab 2 TR-04): signals a real, growing company.
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec = tf_spec_for_path();
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Careers', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec ? $tf_spec['meta'] : '' ); ?></p>
	</div>
</section>

<section class="section">
	<div class="container container--prose">
		<h2><?php esc_html_e( 'Open roles', 'thinkfurther' ); ?></h2>
		<?php
		while ( have_posts() ) :
			the_post();
			if ( trim( get_the_content() ) ) {
				echo '<div class="prose">';
				the_content();
				echo '</div>';
			} else {
				tf_placeholder( 'Careers intro + roles', 'Why join, how the team works, and current openings (or an open-application invitation when none are listed).', 150 );
			}
		endwhile;
		?>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta' );
get_footer();
