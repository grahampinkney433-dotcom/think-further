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
$tf_name    = wp_strip_all_tags( $tf_spec['h1'] );
$tf_sector  = 'sector' === $tf_spec['template'];
$tf_content = function_exists( 'tf_content' ) ? tf_content() : null;
$tf_kses    = function_exists( 'tf_content_kses' ) ? tf_content_kses() : array();
?>

<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( 'A2' === $tf_spec['arm'] ? __( 'AI Search Consultancy', 'thinkfurther' ) : __( 'AI Consultancy', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec['h1'] ); ?></h1>
		<div class="page-hero__subline reveal"><?php echo tf_c( 'intro' ) ? wp_kses( tf_c( 'intro' ), $tf_kses ) : '<p>' . esc_html( $tf_spec['meta'] ) . '</p>'; ?></div>
		<div class="hero__actions reveal">
			<a class="btn btn--volt btn--lg" href="<?php echo esc_url( tf_cta_url() ); ?>"><?php esc_html_e( 'Book a discovery call', 'thinkfurther' ); ?> <span aria-hidden="true">&rarr;</span></a>
			<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See pricing', 'thinkfurther' ); ?></a>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Definition', 'thinkfurther' ); ?>">
	<div class="container container--prose">
		<h2><?php echo esc_html( sprintf( __( 'What is %s?', 'thinkfurther' ), strtolower( $tf_name ) ) ); ?></h2>
		<?php
		$tf_def = tf_c( 'def' );
		if ( ! $tf_def && $tf_sector ) {
			$tf_def = tf_c( 'intro' ); // sector intros are the answer-first definition
		}
		if ( $tf_def ) {
			echo '<div class="guide-quick-answer">' . wp_kses( $tf_def, $tf_kses ) . '</div>';
		} else {
			tf_placeholder( 'Answer-first definition', 'Direct 2–3 sentence definition of ' . strtolower( $tf_name ) . ' in the first 100 words. Quotable, self-contained, no preamble (AI Overview + LLM extraction target).', 60 );
		}
		?>
	</div>
</section>

<section class="section section--tight theme-dark" aria-label="<?php esc_attr_e( 'What is included', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title"><?php echo $tf_sector ? esc_html__( 'AI use cases in your sector', 'thinkfurther' ) : esc_html__( 'How we help', 'thinkfurther' ); ?></h2>
		</div>
		<?php $tf_cards = ( $tf_content && ! empty( $tf_content['cards'] ) ) ? $tf_content['cards'] : array(); ?>
		<div class="offer-grid<?php echo ( $tf_sector || count( $tf_cards ) > 4 ) ? '' : ' offer-grid--three'; ?>">
			<?php
			if ( $tf_cards ) :
				$tf_i = 0;
				foreach ( $tf_cards as $tf_card ) :
					$tf_i++;
					?>
					<article class="offer">
						<p class="offer__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $tf_i, 2, '0', STR_PAD_LEFT ) ); ?></p>
						<?php if ( ! empty( $tf_card['title'] ) ) : ?>
							<h3 class="offer__title"><?php echo esc_html( $tf_card['title'] ); ?></h3>
						<?php endif; ?>
						<?php echo wp_kses( $tf_card['body'], $tf_kses ); ?>
					</article>
					<?php
				endforeach;
			else :
				$tf_items = $tf_sector && ! empty( $tf_spec['usecases'] )
					? $tf_spec['usecases']
					: array( '[Deliverable / sub-service 1]', '[Deliverable / sub-service 2]', '[Deliverable / sub-service 3]' );
				$tf_i = 0;
				foreach ( $tf_items as $tf_item ) :
					$tf_i++;
					?>
					<article class="offer">
						<p class="offer__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $tf_i, 2, '0', STR_PAD_LEFT ) ); ?></p>
						<h3 class="offer__title"><?php echo esc_html( $tf_item ); ?></h3>
						<p><?php echo esc_html( sprintf( '[PLACEHOLDER — %s: ~60 words. Scannable, extractable description written at content stage.]', $tf_item ) ); ?></p>
					</article>
					<?php
				endforeach;
			endif;
			?>
		</div>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'Our process', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title"><?php esc_html_e( 'Our process', 'thinkfurther' ); ?></h2>
		</div>
		<ol class="steps">
			<?php
			$tf_steps = ( $tf_content && ! empty( $tf_content['steps'] ) ) ? $tf_content['steps'] : array();
			if ( $tf_steps ) :
				$tf_s = 0;
				foreach ( $tf_steps as $tf_step ) :
					$tf_s++;
					?>
					<li class="step">
						<p class="step__num" aria-hidden="true">0<?php echo (int) $tf_s; ?></p>
						<?php if ( ! empty( $tf_step['name'] ) ) : ?>
							<h3 class="step__title"><?php echo esc_html( $tf_step['name'] ); ?></h3>
						<?php endif; ?>
						<p class="step__desc"><?php echo wp_kses( $tf_step['desc'], $tf_kses ); ?></p>
					</li>
					<?php
				endforeach;
			else :
				for ( $tf_s = 1; $tf_s <= 4; $tf_s++ ) :
					?>
					<li class="step">
						<p class="step__num" aria-hidden="true">0<?php echo (int) $tf_s; ?></p>
						<h3 class="step__title"><?php echo esc_html( sprintf( '[Step %d name]', $tf_s ) ); ?></h3>
						<p class="step__desc"><?php echo esc_html( sprintf( '[PLACEHOLDER — process step %d: ~40 words. Numbered HowTo-style step, snippet-friendly.]', $tf_s ) ); ?></p>
					</li>
					<?php
				endfor;
			endif;
			?>
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
				<?php
				if ( tf_c( 'outcomes' ) ) {
					echo wp_kses( tf_c( 'outcomes' ), $tf_kses );
				} else {
					tf_placeholder( 'Outcomes', 'Quantified outcomes and benefits of ' . strtolower( $tf_name ) . '. Use specific numbers with named, dated sources where cited.', 120 );
				}
				?>
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
				<?php
				if ( tf_c( 'who' ) ) {
					echo wp_kses( tf_c( 'who' ), $tf_kses );
				} else {
					tf_placeholder( 'Qualifiers', 'Segment / industry qualifiers: who benefits most from ' . strtolower( $tf_name ) . ' and the situations it fits.', 90 );
				}
				?>
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
