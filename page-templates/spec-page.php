<?php
/**
 * Template Name: SEO Spec Page (service / local / sector)
 * Template Post Type: page
 *
 * Renders the Tab 6 SERVICE header outline from inc/seo-spec.php:
 * H1 → definition-first block → How we help (H3 deliverables) →
 * Our process → Outcomes → Proof → Who this is for → FAQ → CTA.
 * Body copy = labelled placeholders until the content stage; anything
 * written in the WP editor renders in the "editor content" slot.
 *
 * @package ThinkFurther
 */

get_header();

$tf_spec = tf_spec_for_path();
if ( ! $tf_spec ) {
	$tf_spec = array(
		'h1'       => get_the_title(),
		'meta'     => '',
		'kw'       => '',
		'template' => 'service',
		'arm'      => 'Shared',
	);
}
$tf_name   = wp_strip_all_tags( $tf_spec['h1'] );
$tf_sector = 'sector' === $tf_spec['template'];
?>

<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( 'A2' === $tf_spec['arm'] ? __( 'AI Search Consultancy', 'thinkfurther' ) : __( 'AI Consultancy', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec['h1'] ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec['meta'] ); ?></p>
		<div class="hero__actions reveal">
			<a class="btn btn--volt btn--lg" href="<?php echo esc_url( tf_cta_url() ); ?>"><?php esc_html_e( 'Book a discovery call', 'thinkfurther' ); ?> <span aria-hidden="true">&rarr;</span></a>
			<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See pricing', 'thinkfurther' ); ?></a>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Definition', 'thinkfurther' ); ?>">
	<div class="container container--prose">
		<h2><?php echo esc_html( sprintf( __( 'What is %s?', 'thinkfurther' ), strtolower( $tf_name ) ) ); ?></h2>
		<?php tf_placeholder( 'Answer-first definition', 'Direct 2–3 sentence definition of ' . strtolower( $tf_name ) . ' in the first 100 words. Quotable, self-contained, no preamble (AI Overview + LLM extraction target).', 60 ); ?>
	</div>
</section>

<section class="section section--tight theme-dark" aria-label="<?php esc_attr_e( 'What is included', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title"><?php echo $tf_sector ? esc_html__( 'AI use cases in your sector', 'thinkfurther' ) : esc_html__( 'How we help', 'thinkfurther' ); ?></h2>
		</div>
		<div class="offer-grid<?php echo $tf_sector ? '' : ' offer-grid--three'; ?>">
			<?php
			$tf_items = $tf_sector && ! empty( $tf_spec['usecases'] )
				? $tf_spec['usecases']
				: array(
					'[Deliverable / sub-service 1]',
					'[Deliverable / sub-service 2]',
					'[Deliverable / sub-service 3]',
				);
			$tf_i = 0;
			foreach ( $tf_items as $tf_item ) :
				$tf_i++;
				?>
				<article class="offer">
					<p class="offer__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $tf_i, 2, '0', STR_PAD_LEFT ) ); ?></p>
					<h3 class="offer__title"><?php echo esc_html( $tf_item ); ?></h3>
					<p><?php echo esc_html( sprintf( '[PLACEHOLDER — %s: ~60 words. Scannable, extractable description written at content stage.]', $tf_item ) ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'Our process', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title"><?php esc_html_e( 'Our process', 'thinkfurther' ); ?></h2>
		</div>
		<ol class="steps">
			<?php for ( $tf_s = 1; $tf_s <= 4; $tf_s++ ) : ?>
				<li class="step">
					<p class="step__num" aria-hidden="true">0<?php echo (int) $tf_s; ?></p>
					<h3 class="step__title"><?php echo esc_html( sprintf( '[Step %d name]', $tf_s ) ); ?></h3>
					<p class="step__desc"><?php echo esc_html( sprintf( '[PLACEHOLDER — process step %d: ~40 words. Numbered HowTo-style step, snippet-friendly.]', $tf_s ) ); ?></p>
				</li>
			<?php endfor; ?>
		</ol>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Outcomes', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<h2 class="section-head__title"><?php esc_html_e( 'Outcomes and benefits', 'thinkfurther' ); ?></h2>
			</div>
			<div class="split__body">
				<?php tf_placeholder( 'Outcomes', 'Quantified outcomes and benefits of ' . strtolower( $tf_name ) . '. Use specific numbers with named, dated sources where cited.', 120 ); ?>
				<div class="about-links">
					<?php tf_arrow_link( get_post_type_archive_link( 'tf_case_study' ), __( 'See our case studies', 'thinkfurther' ) ); ?>
					<?php if ( ! empty( $tf_spec['guide'] ) ) : ?>
						<?php tf_arrow_link( home_url( $tf_spec['guide'] ), __( 'Read the related guide', 'thinkfurther' ) ); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Who this is for', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<h2 class="section-head__title"><?php esc_html_e( 'Who this is for', 'thinkfurther' ); ?></h2>
			</div>
			<div class="split__body">
				<?php tf_placeholder( 'Qualifiers', 'Segment / industry qualifiers: who benefits most from ' . strtolower( $tf_name ) . ' and the situations it fits.', 90 ); ?>
			</div>
		</div>
	</div>
</section>

<?php
// Editor-content slot: final copy pasted in wp-admin renders here.
while ( have_posts() ) :
	the_post();
	if ( trim( get_the_content() ) ) :
		?>
		<section class="section section--tight">
			<div class="container container--prose">
				<div class="prose"><?php the_content(); ?></div>
			</div>
		</section>
		<?php
	endif;
endwhile;

tf_faq_section( __( 'Frequently asked questions', 'thinkfurther' ), __( 'Common questions, *answered directly.*', 'thinkfurther' ), tf_spec_faqs( $tf_spec ) );

get_template_part( 'template-parts/cta' );

get_footer();
