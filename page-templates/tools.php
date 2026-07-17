<?php
/**
 * Template Name: Free Tools Hub
 * Template Post Type: page
 *
 * /tools/ — interactive lead magnets (Tab 2 RS-07).
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec  = tf_spec_for_path();
$tf_tools = array(
	array( home_url( '/tools/ai-roi-calculator/' ), __( 'AI ROI Calculator', 'thinkfurther' ), __( 'Estimate hours saved, annual cost savings and your AI readiness score in two minutes. Instant on-screen results.', 'thinkfurther' ), true ),
	array( tf_page_url_by_template( 'page-templates/audit.php', '/ai-audit/' ), __( 'AI Capabilities Audit', 'thinkfurther' ), __( '15 questions across six areas. Get an AI readiness score out of 100 with tailored next steps — no email gate.', 'thinkfurther' ), true ),
	array( tf_page_url_by_template( 'page-templates/visibility-checker.php', '/tools/ai-visibility-checker/' ), __( 'Brand AI Visibility Checker', 'thinkfurther' ), __( 'See how often ChatGPT, Claude, Gemini and Perplexity name, cite and recommend your brand — with an AI Visibility Score out of 100.', 'thinkfurther' ), true ),
);
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Free tools', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec ? $tf_spec['meta'] : '' ); ?></p>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'Tools', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="offer-grid offer-grid--three">
			<?php $tf_i = 0; foreach ( $tf_tools as $tf_tool ) : $tf_i++; ?>
				<article class="offer offer--light">
					<p class="offer__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $tf_i, 2, '0', STR_PAD_LEFT ) ); ?></p>
					<h2 class="offer__title">
						<?php if ( $tf_tool[3] ) : ?>
							<a href="<?php echo esc_url( $tf_tool[0] ); ?>"><?php echo esc_html( $tf_tool[1] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $tf_tool[1] ); ?>
						<?php endif; ?>
					</h2>
					<p><?php echo esc_html( $tf_tool[2] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta' );
get_footer();
