<?php
/**
 * Template Name: AI Capabilities Audit
 * Template Post Type: page
 *
 * Interactive readiness questionnaire. All questions, scoring and
 * recommendations live in assets/js/audit.js (front-end only).
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'Free tool', 'thinkfurther' ),
		'title'   => __( 'The Business AI *Capabilities Audit.*', 'thinkfurther' ),
		'subline' => __( '15 quick questions, about 3 minutes. Get an AI Readiness Score out of 100, see your strongest and weakest areas, and leave with practical next steps — no email required.', 'thinkfurther' ),
	)
);
?>

<section class="section audit-section" aria-label="<?php esc_attr_e( 'AI Capabilities Audit', 'thinkfurther' ); ?>">
	<div class="container">
		<div id="tf-audit" class="audit" data-audit>
			<noscript>
				<div class="form-notice form-notice--error">
					<p><?php esc_html_e( 'The audit needs JavaScript to run. Please enable JavaScript, or email us and we\'ll run the assessment with you on a call.', 'thinkfurther' ); ?></p>
				</div>
			</noscript>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'About this audit', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'How it works', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Benchmarked the way *we\'d audit you.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<p class="reveal"><?php esc_html_e( 'The questions mirror the six areas we examine in a full AI Opportunity Audit: adoption, data readiness, team capability, automation maturity, strategy and customer-facing AI. Your answers stay in your browser — nothing is sent anywhere unless you choose to email yourself the results.', 'thinkfurther' ); ?></p>
				<p class="reveal"><?php esc_html_e( 'It\'s a self-assessment, so treat the score as a starting point, not a verdict. The pattern across sections — where you\'re strong, where you\'re stuck — is usually more telling than the headline number.', 'thinkfurther' ); ?></p>
			</div>
		</div>
	</div>
</section>

<?php
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

get_footer();
