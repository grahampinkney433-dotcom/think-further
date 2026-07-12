<?php
/**
 * Template Name: AI Consultancy & Transformation
 * Template Post Type: page
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'AI Consultancy & Transformation', 'thinkfurther' ),
		'title'   => __( 'Turn AI from an experiment into *operating advantage.*', 'thinkfurther' ),
		'subline' => __( 'We work alongside leadership teams to find where AI genuinely moves the numbers — then design, build and embed the systems that capture it.', 'thinkfurther' ),
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'The problem we solve', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'The problem', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Pilots everywhere. *Progress nowhere.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<p class="reveal"><?php esc_html_e( 'Most organisations we meet have already tried AI: a copilot licence here, a chatbot pilot there, a few enthusiastic individuals doing remarkable things in isolation. What they don\'t have is compounding value — because tools were adopted before workflows were redesigned, and nobody owned the outcome.', 'thinkfurther' ); ?></p>
				<p class="reveal"><?php esc_html_e( 'Our practice closes that gap. We connect AI capability to commercial outcomes: fewer hours on low-value work, faster cycle times, better decisions, new capacity — measured against baselines you agree before we start.', 'thinkfurther' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="section section--tight theme-dark" aria-label="<?php esc_attr_e( 'Services', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<?php tf_eyebrow( __( 'The practice', 'thinkfurther' ), 'reveal' ); ?>
			<h2 class="section-head__title reveal"><?php esc_html_e( 'Four ways we engage.', 'thinkfurther' ); ?></h2>
		</div>

		<div class="offer-grid">
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">01</p>
				<h3 class="offer__title"><?php esc_html_e( 'AI Opportunity Audit', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'A structured diagnostic across your operations, data and market position. You get a prioritised map of where AI creates value in your business — with effort, impact and sequencing — in weeks, not quarters.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">02</p>
				<h3 class="offer__title"><?php esc_html_e( 'AI Strategy & Roadmap', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'A board-ready strategy that answers the hard questions: build vs buy, governance and risk, data readiness, team structure, and a delivery roadmap with owners and measurable milestones.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">03</p>
				<h3 class="offer__title"><?php esc_html_e( 'Custom AI Systems & Agents', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Design and delivery of working systems: agentic workflows, intelligent document processing, decision support, customer-facing assistants — integrated with your stack and governed properly.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer reveal">
				<p class="offer__num" aria-hidden="true">04</p>
				<h3 class="offer__title"><?php esc_html_e( 'Enablement & Governance', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Training, playbooks, usage policy and operating rhythm — so adoption spreads beyond the pilot team and the capability stays after we leave. We transfer skills, not dependency.', 'thinkfurther' ); ?></p>
			</article>
		</div>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'Who this is for', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<?php tf_eyebrow( __( 'Who we work with', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Ambitious teams with *real operations.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
			</div>
			<div class="split__body">
				<ul class="check-list reveal">
					<li><?php esc_html_e( 'Mid-market and scale-up businesses where hours saved translate directly to margin or growth capacity.', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'Leadership teams who want an honest, evidence-led view — not a vendor pitch dressed as strategy.', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'Organisations ready to change how work happens, not just add another tool to the stack.', 'thinkfurther' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php
// Surface any content added to this page in wp-admin.
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
