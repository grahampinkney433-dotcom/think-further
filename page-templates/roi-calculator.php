<?php
/**
 * Template Name: AI ROI Calculator
 * Template Post Type: page
 *
 * /tools/ai-roi-calculator/ — Tab 16 functional spec. Calculator logic
 * lives in assets/js/roi-calculator.js (loaded only on this template).
 * Instant on-screen results (no email); gated "AI Opportunity Report"
 * uses the documented lead hook in that file.
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
		<?php tf_eyebrow( __( 'Free tool', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec ? $tf_spec['meta'] : '' ); ?></p>
	</div>
</section>

<section class="section audit-section" aria-label="<?php esc_attr_e( 'AI ROI calculator', 'thinkfurther' ); ?>">
	<div class="container">
		<div id="tf-roi" class="audit" data-roi>
			<noscript>
				<div class="form-notice form-notice--error"><p><?php esc_html_e( 'The calculator needs JavaScript. Email us and we\'ll run the numbers with you.', 'thinkfurther' ); ?></p></div>
			</noscript>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Methodology', 'thinkfurther' ); ?>">
	<div class="container container--prose">
		<h2><?php esc_html_e( 'How the calculator works', 'thinkfurther' ); ?></h2>
		<div class="prose">
			<p><?php esc_html_e( 'The estimate uses three published assumptions, stated here for transparency: hours saved per week = employees × admin hours per employee × expected time savings; annual cost saving = hours saved per week × blended hourly rate × 52; and productivity gain = the share of admin workload AI absorbs. The readiness score combines four dimensions — data, tooling, team and momentum — each scored 0–2.5.', 'thinkfurther' ); ?></p>
			<p><?php esc_html_e( 'Worked example: 10 employees × 8 admin hours × 35% savings = 28 hours/week; 28 × £25 × 52 = £36,400 per year, with a 35% productivity gain on admin workload.', 'thinkfurther' ); ?></p>
		</div>
		<?php $tf_interp = tf_c_section( 'how to interpret' ); if ( $tf_interp ) { echo wp_kses( $tf_interp, tf_content_kses() ); } else { tf_placeholder( 'Interpretation guidance', 'How to interpret results and choose a realistic savings percentage; what a structured 6–12 month programme changes (Tab 16: written explainer so the page ranks and is LLM-citable).', 150 ); } ?>
	</div>
</section>

<?php
$tf_ph = '[PLACEHOLDER — 40–60 word answer-first response, written at content stage.]';
$tf_roi_faqs = function_exists( 'tf_content_faqs' ) ? tf_content_faqs() : array();
if ( ! $tf_roi_faqs ) {
	$tf_roi_faqs = array(
		array( __( 'How accurate is the AI ROI calculator?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Is the calculator free to use?', 'thinkfurther' ), $tf_ph ),
		array( __( 'What happens to my data?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Which time-savings percentage should I choose?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Who is this calculator for?', 'thinkfurther' ), $tf_ph ),
		array( __( 'What is a typical payback window?', 'thinkfurther' ), $tf_ph ),
	);
}
tf_faq_section(
	__( 'FAQs', 'thinkfurther' ),
	__( 'Calculator questions, *answered directly.*', 'thinkfurther' ),
	$tf_roi_faqs
);

get_template_part( 'template-parts/cta' );
get_footer();
