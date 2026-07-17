<?php
/**
 * Template Name: AI Visibility Checker
 * Template Post Type: page
 *
 * /tools/ai-visibility-checker/ — Brand AI Visibility Checker.
 *
 * Front end lives in assets/js/visibility-checker.js and renders into the
 * [data-visibility] mount below. It POSTs to the backend REST route registered
 * in inc/visibility-checker.php, which runs the real API queries, enforces the
 * per-visitor daily cap, requires an email, and logs each check.
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec = tf_spec_for_path();
?>

<section class="page-hero theme-dark tf-arm-search">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Free tool', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec ? $tf_spec['meta'] : __( 'Check how often AI assistants and AI search name, cite and recommend your brand — with a single AI Visibility Score out of 100.', 'thinkfurther' ) ); ?></p>
	</div>
</section>

<section class="section vis-section tf-arm-search" aria-label="<?php esc_attr_e( 'AI Visibility Checker', 'thinkfurther' ); ?>">
	<div class="container">
		<div id="tf-visibility" class="vis" data-visibility>
			<noscript>
				<div class="form-notice form-notice--error">
					<p><?php esc_html_e( 'The visibility checker needs JavaScript to run. Please enable JavaScript, or email us and we\'ll run the check with you on a call.', 'thinkfurther' ); ?></p>
				</div>
			</noscript>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'What we measure', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'What we measure', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Six signals of *AI visibility.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<p class="reveal"><?php esc_html_e( 'The checker queries the major AI assistants and AI-search engines the way a buyer would, then measures six things: how often you are mentioned, how often you are cited with a source, your share of voice against named competitors, the sentiment AI attaches to you, which sources it pulls from, and an overall AI Visibility Score out of 100.', 'thinkfurther' ); ?></p>
				<p class="reveal"><?php esc_html_e( 'It is a snapshot, not a full audit — but the pattern it reveals (named but not cited, strong sentiment but low share of voice) usually points straight at the work that matters most.', 'thinkfurther' ); ?></p>
			</div>
		</div>
	</div>
</section>

<?php
$tf_ph = '[PLACEHOLDER — 40–60 word answer-first response, written at content stage.]';
tf_faq_section(
	__( 'FAQs', 'thinkfurther' ),
	__( 'Visibility checker questions, *answered directly.*', 'thinkfurther' ),
	array(
		array( __( 'What is an AI Visibility Score?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Which AI assistants does the checker query?', 'thinkfurther' ), $tf_ph ),
		array( __( 'How is share of voice calculated?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Why do you ask for an email address?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Is this the same as a full AI visibility audit?', 'thinkfurther' ), $tf_ph ),
		array( __( 'How often should I check my AI visibility?', 'thinkfurther' ), $tf_ph ),
	)
);

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

get_template_part( 'template-parts/cta' );
get_footer();
