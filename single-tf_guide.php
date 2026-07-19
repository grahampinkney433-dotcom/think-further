<?php
/**
 * Single pillar guide (Tab 6 PILLAR GUIDE outline): quick answer →
 * key sections → HowTo steps → related terms → FAQs. Author byline +
 * visible last-updated date (Tab 10). Editor content replaces the
 * placeholder scaffold.
 *
 * @package ThinkFurther
 */

get_header();

while ( have_posts() ) :
	the_post();
	$tf_money        = get_post_meta( get_the_ID(), '_tf_money_url', true );
	$tf_money_anchor = get_post_meta( get_the_ID(), '_tf_money_anchor', true );
	?>

	<article <?php post_class( 'single-post' ); ?>>
		<header class="page-hero theme-dark">
			<div class="page-hero__grid" aria-hidden="true"></div>
			<div class="container container--prose">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<div class="single-post__meta reveal">
					<span><?php esc_html_e( 'Guide', 'thinkfurther' ); ?></span>
					<span class="post-meta__sep" aria-hidden="true">·</span>
					<span><?php esc_html_e( 'By', 'thinkfurther' ); ?> <?php the_author(); ?></span>
					<span class="post-meta__sep" aria-hidden="true">·</span>
					<span><?php esc_html_e( 'Updated', 'thinkfurther' ); ?> <?php echo esc_html( get_the_modified_date() ); ?></span>
				</div>
				<h1 class="page-hero__title page-hero__title--article reveal"><?php the_title(); ?></h1>
			</div>
		</header>

		<div class="section">
			<div class="container container--prose">
				<h2><?php esc_html_e( 'Quick answer', 'thinkfurther' ); ?></h2>
				<?php
				if ( has_excerpt() ) {
					echo '<p class="guide-quick-answer">' . esc_html( get_the_excerpt() ) . '</p>';
				} else {
					tf_placeholder( 'Quick answer', '40–60 word quotable, self-contained answer at the top — the citation target for AI engines. Add via the guide excerpt field.', 50 );
				}

				if ( trim( get_the_content() ) ) {
					echo '<div class="prose">';
					the_content();
					echo '</div>';
				} else {
					echo '<h2>' . esc_html( '[Key sub-topic H2 — one per major sub-question]' ) . '</h2>';
					tf_placeholder( 'Key sections', 'H2 per major sub-question with H3 sub-points beneath. Question-format headings mirroring real prompts.', 400 );
					echo '<h2>' . esc_html( '[How to — numbered steps H2]' ) . '</h2>';
					tf_placeholder( 'HowTo steps', 'Numbered, self-contained steps (heavily extracted by LLMs).', 200 );
				}
				?>

				<h2><?php esc_html_e( 'Related terms', 'thinkfurther' ); ?></h2>
				<div class="about-links">
					<?php
					if ( $tf_money && $tf_money_anchor ) {
						tf_arrow_link( home_url( $tf_money ), $tf_money_anchor );
					}
					tf_arrow_link( home_url( '/glossary/' ), __( 'Browse the glossary', 'thinkfurther' ) );
					tf_arrow_link( home_url( '/guides/' ), __( 'More guides', 'thinkfurther' ) );
					?>
				</div>
			</div>
		</div>
	</article>

	<?php
	$tf_faqs = get_post_meta( get_the_ID(), '_tf_faqs', true );
	if ( empty( $tf_faqs ) || ! is_array( $tf_faqs ) ) {
		$tf_spec_like = array( 'h1' => get_the_title(), 'meta' => '', 'kw' => '', 'template' => 'guide', 'arm' => 'Shared' );
		$tf_faqs      = tf_spec_faqs( $tf_spec_like );
	}
	tf_faq_section( __( 'FAQs', 'thinkfurther' ), __( 'Related questions, *answered directly.*', 'thinkfurther' ), $tf_faqs );
endwhile;

get_template_part( 'template-parts/cta' );
get_footer();
