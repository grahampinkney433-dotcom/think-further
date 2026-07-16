<?php
/**
 * Glossary term (Tab 6 GLOSSARY TERM): one-sentence definition first,
 * why it matters, related terms. DefinedTerm schema in inc/seo.php.
 *
 * @package ThinkFurther
 */

get_header();

while ( have_posts() ) :
	the_post();
	$tf_guide_url = get_post_meta( get_the_ID(), '_tf_guide_url', true );
	?>
	<article <?php post_class(); ?>>
		<header class="page-hero theme-dark">
			<div class="page-hero__grid" aria-hidden="true"></div>
			<div class="container container--prose">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<?php tf_eyebrow( __( 'Glossary', 'thinkfurther' ), 'reveal' ); ?>
				<h1 class="page-hero__title page-hero__title--article reveal"><?php the_title(); ?></h1>
			</div>
		</header>

		<div class="section">
			<div class="container container--prose">
				<h2><?php esc_html_e( 'Definition', 'thinkfurther' ); ?></h2>
				<?php
				if ( has_excerpt() ) {
					echo '<p class="guide-quick-answer">' . esc_html( get_the_excerpt() ) . '</p>';
				} else {
					tf_placeholder( 'One-sentence definition', 'One crisp, self-contained definition first (DefinedTerm description). Add via the excerpt field.', 30 );
				}
				?>

				<h2><?php esc_html_e( 'Why it matters', 'thinkfurther' ); ?></h2>
				<?php
				if ( trim( get_the_content() ) ) {
					echo '<div class="prose">';
					the_content();
					echo '</div>';
				} else {
					tf_placeholder( 'Why it matters / example', 'Short, extractable explanation with one concrete example.', 80 );
				}
				?>

				<h2><?php esc_html_e( 'Related terms', 'thinkfurther' ); ?></h2>
				<div class="about-links">
					<?php
					if ( $tf_guide_url ) {
						tf_arrow_link( home_url( $tf_guide_url ), __( 'Read the full guide', 'thinkfurther' ) );
					}
					tf_arrow_link( home_url( '/glossary/' ), __( 'All glossary terms', 'thinkfurther' ) );
					?>
				</div>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_template_part( 'template-parts/cta' );
get_footer();
