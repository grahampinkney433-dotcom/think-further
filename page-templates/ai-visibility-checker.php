<?php
/**
 * Template Name: AI Visibility Checker
 * Template Post Type: page
 *
 * /tools/ai-visibility-checker/ — Brand AI Visibility Checker.
 *
 * Front-end UI lives in assets/js/ai-visibility-checker.js; it calls the
 * REST endpoint in inc/ai-visibility-checker.php, which runs the real
 * LLM/search API calls (or clearly-labelled sample data) and returns the
 * dashboard payload. WebApplication schema is emitted from inc/seo.php.
 *
 * @package ThinkFurther
 */

get_header();

$tf_spec = tf_spec_for_path();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'Free tool', 'thinkfurther' ),
		'title'   => __( 'Brand *AI Visibility Checker.*', 'thinkfurther' ),
		'subline' => __( 'See how visible your brand is across ChatGPT, Claude, Perplexity and Gemini — citations, mentions, share of voice, sentiment and the sources AI pulls from, with an overall AI Visibility Score out of 100.', 'thinkfurther' ),
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'AI Visibility Checker', 'thinkfurther' ); ?>">
	<div class="container">
		<div id="tf-avc" class="avc" data-avc>
			<noscript>
				<div class="form-notice form-notice--error">
					<p><?php esc_html_e( 'The checker needs JavaScript to run. Please enable it, or book a call and we\'ll run the analysis with you.', 'thinkfurther' ); ?></p>
				</div>
			</noscript>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'What this measures', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'How it works', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Where AI decides *who gets named.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<p class="reveal"><?php esc_html_e( 'We ask several AI assistants and AI search engines to describe your brand, exactly as a customer would. Then we measure what came back: whether you were mentioned, whether you were cited with a link, how your share of voice compares to competitors you name, how AI characterises you, and which sources it leaned on.', 'thinkfurther' ); ?></p>
				<p class="reveal"><?php esc_html_e( 'It\'s a snapshot, not a full audit — a starting point for understanding your position in AI search. The full audit goes deeper across dozens of prompts and engines, and comes with a plan to improve.', 'thinkfurther' ); ?></p>
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
			<div class="container container--prose"><div class="prose"><?php the_content(); ?></div></div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
