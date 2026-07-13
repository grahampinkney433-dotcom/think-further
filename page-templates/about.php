<?php
/**
 * Template Name: About
 * Template Post Type: page
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'About Alugence', 'thinkfurther' ),
		'title'   => __( 'A consultancy built for the *AI decade.*', 'thinkfurther' ),
		'subline' => __( 'Founded on a simple observation: the firms winning with AI aren\'t the ones with the biggest budgets — they\'re the ones thinking furthest ahead about how work, and being found, are changing.', 'thinkfurther' ),
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'Why we exist', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'Why we exist', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'The advice market didn\'t keep up. *So we built what was missing.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<p class="reveal"><?php esc_html_e( 'On one side: big-firm AI strategy that costs six figures before anything ships. On the other: automation freelancers gluing tools together with no view of the business. And almost nobody treating the other half of the problem — how AI assistants describe and recommend you — as the distribution channel it has become.', 'thinkfurther' ); ?></p>
				<p class="reveal"><?php esc_html_e( 'Alugence sits deliberately between and across: senior enough to work with leadership on what matters, hands-on enough to ship working systems in weeks, and specialised enough to treat AI search visibility as a first-class discipline rather than a footnote.', 'thinkfurther' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="section section--tight theme-dark" aria-label="<?php esc_attr_e( 'What we believe', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<?php tf_eyebrow( __( 'What we believe', 'thinkfurther' ), 'reveal' ); ?>
			<h2 class="section-head__title reveal"><?php esc_html_e( 'Three convictions shape every engagement.', 'thinkfurther' ); ?></h2>
		</div>
		<div class="offer-grid offer-grid--three">
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">01</p>
				<h3 class="offer__title"><?php esc_html_e( 'AI is an operating decision, not an IT project', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'The gains come from redesigning how work happens — who does what, checked by whom, at what speed — not from adding licences to the stack. That\'s a leadership conversation, and we hold it there.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">02</p>
				<h3 class="offer__title"><?php esc_html_e( 'Visibility is the new distribution', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'When assistants answer for your market, being the company the model names is distribution — as real as rankings ever were. We treat it with the same rigour: baselines, levers, monitoring.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">03</p>
				<h3 class="offer__title"><?php esc_html_e( 'Capability beats dependency', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'A consultancy that makes itself permanent has failed. Everything we build is documented, transferred and owned by your team — our engagements end with you stronger, by design.', 'thinkfurther' ); ?></p>
			</article>
		</div>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'How we are structured', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'One firm, two practices', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Capable with AI. *Found by AI.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<p class="reveal"><?php esc_html_e( 'Business AI consultancy and transformation is our core practice; AI search consultancy is the specialised line within it. They share one team and one body of knowledge about how models retrieve, reason and cite — which is exactly why they compound.', 'thinkfurther' ); ?></p>
				<div class="about-links reveal">
					<?php tf_arrow_link( tf_page_url_by_template( 'page-templates/ai-transformation.php', '/ai-consultancy/' ), __( 'AI Consultancy & Transformation', 'thinkfurther' ) ); ?>
					<?php tf_arrow_link( tf_page_url_by_template( 'page-templates/ai-search.php', '/ai-search/' ), __( 'AI Search Consultancy', 'thinkfurther' ) ); ?>
					<?php tf_arrow_link( tf_page_url_by_template( 'page-templates/approach.php', '/approach/' ), __( 'How we work', 'thinkfurther' ) ); ?>
				</div>
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
?>

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();
