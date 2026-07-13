<?php
/**
 * Template Name: AI Search Consultancy
 * Template Post Type: page
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'AI Search Consultancy', 'thinkfurther' ),
		'title'   => __( 'When AI answers for your market, *make sure it says your name.*', 'thinkfurther' ),
		'subline' => __( 'ChatGPT, Claude, Perplexity, Gemini and AI Overviews are becoming the first place your buyers ask. We measure how AI represents you — then engineer your visibility.', 'thinkfurther' ),
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'Why AI search matters', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'The shift', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Search didn\'t die. *It changed shape.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<p class="reveal"><?php esc_html_e( 'Buyers increasingly skip the results page entirely. They ask an assistant, get a synthesised answer with a handful of named companies, and shortlist from there. If you\'re not in the answer, you\'re not in the deal — and traditional rank tracking won\'t even show you the problem.', 'thinkfurther' ); ?></p>
				<p class="reveal"><?php esc_html_e( 'AI search visibility is measurable and improvable, but the levers are different: entity clarity, citation-worthy sources, machine-readable content architecture, and consistent presence in the places LLMs learn from and retrieve against.', 'thinkfurther' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="section section--tight theme-dark" aria-label="<?php esc_attr_e( 'Services', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<?php tf_eyebrow( __( 'The service line', 'thinkfurther' ), 'reveal' ); ?>
			<h2 class="section-head__title reveal"><?php esc_html_e( 'From audit to share of answer.', 'thinkfurther' ); ?></h2>
		</div>

		<div class="offer-grid">
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">01</p>
				<h3 class="offer__title"><?php esc_html_e( 'AI Visibility Audit', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'How do the major assistants describe you, recommend you and cite you today — against the questions your buyers actually ask? A benchmarked baseline across models, versus your competitors.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">02</p>
				<h3 class="offer__title"><?php esc_html_e( 'Answer-Engine Optimisation', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'A prioritised programme across entity signals, structured data, authoritative citations and third-party presence — the inputs that determine whether models name you, and what they say.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">03</p>
				<h3 class="offer__title"><?php esc_html_e( 'Content Architecture for LLMs', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Content designed to be retrieved, quoted and trusted by machines as well as read by people: question-shaped structure, verifiable claims, and clean semantics across your site.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">04</p>
				<h3 class="offer__title"><?php esc_html_e( 'Monitoring & Share of Answer', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Ongoing tracking of mentions, citations, sentiment and position across AI surfaces — reported like a channel, with movement attributed to the work.', 'thinkfurther' ); ?></p>
			</article>
		</div>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'How it connects', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'Part of one firm', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Visibility works best when the *business behind it does.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<p class="reveal"><?php esc_html_e( 'AI search is a specialised practice within Alugence, not a separate agency. That matters: the same understanding of how models retrieve, reason and cite informs both how we build AI into your operations and how we position you inside everyone else\'s. Clients often start with a visibility audit and expand into transformation — or the reverse.', 'thinkfurther' ); ?></p>
				<div class="reveal">
					<?php tf_arrow_link( tf_page_url_by_template( 'page-templates/ai-transformation.php', '/ai-consultancy/' ), __( 'See the transformation practice', 'thinkfurther' ) ); ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
tf_faq_section(
	__( 'Questions', 'thinkfurther' ),
	__( 'AI search, *answered directly.*', 'thinkfurther' ),
	array(
		array(
			__( 'What is AI search optimisation?', 'thinkfurther' ),
			__( 'The practice of improving how AI assistants and answer engines — ChatGPT, Claude, Perplexity, Gemini, AI Overviews — describe, recommend and cite your business. It is sometimes called AEO or GEO; we treat it as a measurable channel with its own audit, playbook and monitoring.', 'thinkfurther' ),
		),
		array(
			__( 'How is it different from SEO?', 'thinkfurther' ),
			__( 'SEO optimises for a ranked list of links; AI search optimisation targets a synthesised answer. The levers overlap but differ: entity clarity, citation-worthy third-party sources, verifiable claims and machine-readable structure matter more than keywords and backlink volume alone. Good SEO helps — it is no longer sufficient.', 'thinkfurther' ),
		),
		array(
			__( 'Can AI visibility actually be measured?', 'thinkfurther' ),
			__( 'Yes. We run structured panels of real buyer questions across the major assistants and track whether you are named, how you are described, what gets cited and how that compares to competitors — a repeatable baseline we report against, movement by movement.', 'thinkfurther' ),
		),
		array(
			__( 'How long before we see movement?', 'thinkfurther' ),
			__( 'Some retrieval-driven surfaces respond within weeks of content and entity fixes; presence in model training data shifts more slowly. We sequence the programme so fast-moving surfaces fund patience for the slower ones, and the monitoring shows both.', 'thinkfurther' ),
		),
		array(
			__( 'Do we need the transformation practice too?', 'thinkfurther' ),
			__( 'No — AI search engagements stand alone. But the two compound: the same understanding of how models retrieve and reason informs both practices, and clients frequently expand from one into the other once the first set of results lands.', 'thinkfurther' ),
		),
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
?>

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();
