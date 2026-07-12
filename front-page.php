<?php
/**
 * Front page: the primary marketing narrative.
 *
 * @package ThinkFurther
 */

get_header();
?>

<!-- ============ HERO ============ -->
<section class="hero theme-dark" aria-label="<?php esc_attr_e( 'Introduction', 'thinkfurther' ); ?>">
	<canvas class="hero__canvas" data-hero-canvas aria-hidden="true"></canvas>
	<div class="hero__inner container">
		<?php tf_eyebrow( tf_option( 'tf_hero_eyebrow' ), 'reveal' ); ?>
		<h1 class="hero__headline reveal">
			<?php echo wp_kses( tf_headline( tf_option( 'tf_hero_headline' ) ), array( 'em' => array() ) ); ?>
		</h1>
		<p class="hero__subline reveal"><?php echo esc_html( tf_option( 'tf_hero_subline' ) ); ?></p>
		<div class="hero__actions reveal">
			<a class="btn btn--light btn--lg" href="<?php echo esc_url( tf_cta_url() ); ?>">
				<?php echo esc_html( tf_option( 'tf_hero_cta' ) ); ?>
				<span aria-hidden="true">&rarr;</span>
			</a>
			<a class="btn btn--ghost btn--lg" href="#services"><?php esc_html_e( 'Explore what we do', 'thinkfurther' ); ?></a>
		</div>
	</div>
	<div class="hero__baseline container reveal">
		<p class="hero__baseline-item"><?php esc_html_e( 'Strategy & transformation', 'thinkfurther' ); ?></p>
		<p class="hero__baseline-item"><?php esc_html_e( 'Applied AI delivery', 'thinkfurther' ); ?></p>
		<p class="hero__baseline-item"><?php esc_html_e( 'AI search visibility', 'thinkfurther' ); ?></p>
	</div>
</section>

<!-- ============ MANIFESTO ============ -->
<section class="manifesto section" aria-label="<?php esc_attr_e( 'Our position', 'thinkfurther' ); ?>">
	<div class="container">
		<?php tf_eyebrow( __( 'The shift underway', 'thinkfurther' ), 'reveal' ); ?>
		<h2 class="manifesto__statement reveal">
			<?php
			echo wp_kses(
				tf_headline( __( 'AI is rewriting how businesses operate — and how customers find them. Most firms treat those as separate problems. *They\'re one problem.*', 'thinkfurther' ) ),
				array( 'em' => array() )
			);
			?>
		</h2>
		<div class="manifesto__cols">
			<p class="reveal"><?php esc_html_e( 'Inside the business, AI is collapsing the cost of work that used to take teams: analysis, drafting, triage, decisions. Outside it, buyers have stopped scrolling ten blue links — they ask an AI assistant and act on the answer.', 'thinkfurther' ); ?></p>
			<p class="reveal"><?php esc_html_e( 'Think Further exists at the intersection. We make your business genuinely capable with AI, and we make sure that when AI answers questions in your market, you\'re the company it names.', 'thinkfurther' ); ?></p>
		</div>
	</div>
</section>

<!-- ============ TWO ARMS ============ -->
<section class="services section section--tight" id="services" aria-label="<?php esc_attr_e( 'Services', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<?php tf_eyebrow( __( 'What we do', 'thinkfurther' ), 'reveal' ); ?>
			<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'One firm. Two ways we *compound* your advantage.', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
		</div>

		<div class="services__grid">
			<article class="service-card service-card--primary reveal" data-tilt>
				<p class="service-card__index" aria-hidden="true">01</p>
				<h3 class="service-card__title"><?php esc_html_e( 'AI Consultancy & Transformation', 'thinkfurther' ); ?></h3>
				<p class="service-card__desc"><?php esc_html_e( 'Our core practice. We work with leadership teams to find where AI genuinely moves the numbers, then design, build and embed it — from opportunity mapping and strategy through custom AI systems, agentic workflows and team capability.', 'thinkfurther' ); ?></p>
				<ul class="service-card__list">
					<li><?php esc_html_e( 'AI opportunity audit & roadmap', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'Process & workflow transformation', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'Custom AI systems & agents', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'Governance, enablement & training', 'thinkfurther' ); ?></li>
				</ul>
				<?php tf_arrow_link( tf_page_url_by_template( 'page-templates/ai-transformation.php', '/ai-consultancy/' ), __( 'Explore the practice', 'thinkfurther' ) ); ?>
			</article>

			<article class="service-card service-card--search reveal" data-tilt>
				<p class="service-card__index" aria-hidden="true">02</p>
				<p class="service-card__badge"><?php esc_html_e( 'Specialised practice', 'thinkfurther' ); ?></p>
				<h3 class="service-card__title"><?php esc_html_e( 'AI Search Consultancy', 'thinkfurther' ); ?></h3>
				<p class="service-card__desc"><?php esc_html_e( 'A dedicated service line for the new front door of your market: ChatGPT, Claude, Perplexity, Gemini and AI Overviews. We measure how AI talks about you, then engineer your visibility, citations and share of answer.', 'thinkfurther' ); ?></p>
				<ul class="service-card__list">
					<li><?php esc_html_e( 'AI visibility audit & benchmarking', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'Answer-engine optimisation (AEO/GEO)', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'Citation & mention monitoring', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'Content architecture for LLMs', 'thinkfurther' ); ?></li>
				</ul>
				<?php tf_arrow_link( tf_page_url_by_template( 'page-templates/ai-search.php', '/ai-search/' ), __( 'Explore AI search', 'thinkfurther' ) ); ?>
			</article>
		</div>
	</div>
</section>

<!-- ============ CAPABILITIES BAND ============ -->
<section class="capabilities theme-dark section" aria-label="<?php esc_attr_e( 'Capabilities', 'thinkfurther' ); ?>">
	<div class="marquee" data-marquee aria-hidden="true">
		<div class="marquee__track">
			<?php
			$tf_marquee_items = array( 'AI strategy', 'Agentic workflows', 'LLM search visibility', 'Process automation', 'Answer-engine optimisation', 'Capability building', 'Custom AI systems', 'Share of answer' );
			for ( $tf_i = 0; $tf_i < 2; $tf_i++ ) :
				foreach ( $tf_marquee_items as $tf_item ) :
					?>
					<span class="marquee__item"><?php echo esc_html( $tf_item ); ?><span class="marquee__dot"></span></span>
					<?php
				endforeach;
			endfor;
			?>
		</div>
	</div>

	<div class="container">
		<div class="section-head">
			<?php tf_eyebrow( __( 'Why it works', 'thinkfurther' ), 'reveal' ); ?>
			<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Built for outcomes, *not theatre.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
		</div>

		<div class="capabilities__grid">
			<div class="capability reveal">
				<h3 class="capability__title"><?php esc_html_e( 'Business-first, model-second', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'We start from your P&L and your customers\' behaviour, not from a technology looking for a use case. If AI isn\'t the answer, we\'ll say so.', 'thinkfurther' ); ?></p>
			</div>
			<div class="capability reveal">
				<h3 class="capability__title"><?php esc_html_e( 'Senior people, end to end', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'The people who scope your engagement are the people who deliver it. No handoff to a bench, no diluted thinking.', 'thinkfurther' ); ?></p>
			</div>
			<div class="capability reveal">
				<h3 class="capability__title"><?php esc_html_e( 'Ship, measure, embed', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Strategy decks don\'t transform anything. We deploy working systems, measure them against agreed baselines, and train your team to own them.', 'thinkfurther' ); ?></p>
			</div>
			<div class="capability reveal">
				<h3 class="capability__title"><?php esc_html_e( 'Visibility as a discipline', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'We treat AI search the way the last decade treated SEO — as a measurable channel with its own audit, playbook and monitoring, not a bolt-on.', 'thinkfurther' ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- ============ APPROACH ============ -->
<section class="approach section" aria-label="<?php esc_attr_e( 'Our approach', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<?php tf_eyebrow( __( 'How we work', 'thinkfurther' ), 'reveal' ); ?>
			<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'A method that moves from *insight to infrastructure.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
		</div>

		<ol class="steps">
			<li class="step reveal">
				<p class="step__num" aria-hidden="true">01</p>
				<h3 class="step__title"><?php esc_html_e( 'Diagnose', 'thinkfurther' ); ?></h3>
				<p class="step__desc"><?php esc_html_e( 'A short, intensive discovery: where value leaks, where AI can compound, and how AI currently represents you to your market. You get an evidenced map, not a hunch.', 'thinkfurther' ); ?></p>
			</li>
			<li class="step reveal">
				<p class="step__num" aria-hidden="true">02</p>
				<h3 class="step__title"><?php esc_html_e( 'Design', 'thinkfurther' ); ?></h3>
				<p class="step__desc"><?php esc_html_e( 'We prioritise ruthlessly — a small number of interventions with clear owners, baselines and expected returns. Every recommendation comes with the case for it.', 'thinkfurther' ); ?></p>
			</li>
			<li class="step reveal">
				<p class="step__num" aria-hidden="true">03</p>
				<h3 class="step__title"><?php esc_html_e( 'Deploy', 'thinkfurther' ); ?></h3>
				<p class="step__desc"><?php esc_html_e( 'Working systems in weeks: automations, agents, content architecture, monitoring. Built on your stack, integrated with your data, measured from day one.', 'thinkfurther' ); ?></p>
			</li>
			<li class="step reveal">
				<p class="step__num" aria-hidden="true">04</p>
				<h3 class="step__title"><?php esc_html_e( 'Embed', 'thinkfurther' ); ?></h3>
				<p class="step__desc"><?php esc_html_e( 'We transfer capability, not dependency — training, playbooks and governance so the advantage keeps compounding after we step back.', 'thinkfurther' ); ?></p>
			</li>
		</ol>

		<div class="approach__link reveal">
			<?php tf_arrow_link( tf_page_url_by_template( 'page-templates/approach.php', '/approach/' ), __( 'More on how we work', 'thinkfurther' ) ); ?>
		</div>
	</div>
</section>

<!-- ============ PROOF / NUMBERS ============ -->
<section class="proof theme-dark section" aria-label="<?php esc_attr_e( 'The stakes', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="proof__layout">
			<div class="proof__copy">
				<?php tf_eyebrow( __( 'The stakes', 'thinkfurther' ), 'reveal' ); ?>
				<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'The gap between AI leaders and laggards is *widening quarterly.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
				<p class="reveal"><?php esc_html_e( 'Advantage in this cycle compounds: better workflows produce better data, better data produces better systems, and visible brands get cited by the AI tools your buyers now trust. The cost of waiting is not standing still — it\'s falling behind at an accelerating rate.', 'thinkfurther' ); ?></p>
			</div>
			<dl class="proof__stats">
				<div class="stat reveal">
					<dt class="stat__label"><?php esc_html_e( 'of buyers now use AI tools in their purchase research', 'thinkfurther' ); ?></dt>
					<dd class="stat__value"><span data-count="70">70</span><span class="stat__unit">%+</span></dd>
				</div>
				<div class="stat reveal">
					<dt class="stat__label"><?php esc_html_e( 'typical share of workflows with automation potential we find in an audit', 'thinkfurther' ); ?></dt>
					<dd class="stat__value"><span data-count="30">30</span><span class="stat__unit">%+</span></dd>
				</div>
				<div class="stat reveal">
					<dt class="stat__label"><?php esc_html_e( 'weeks from engagement start to first system live', 'thinkfurther' ); ?></dt>
					<dd class="stat__value"><span data-count="6">6</span><span class="stat__unit">&nbsp;wks</span></dd>
				</div>
			</dl>
		</div>
	</div>
</section>

<!-- ============ INSIGHTS ============ -->
<?php
$tf_latest = new WP_Query(
	array(
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( $tf_latest->have_posts() ) :
	?>
	<section class="insights section" aria-label="<?php esc_attr_e( 'Insights', 'thinkfurther' ); ?>">
		<div class="container">
			<div class="section-head section-head--split">
				<div>
					<?php tf_eyebrow( __( 'Insights', 'thinkfurther' ), 'reveal' ); ?>
					<h2 class="section-head__title reveal"><?php esc_html_e( 'Thinking, further.', 'thinkfurther' ); ?></h2>
				</div>
				<div class="reveal">
					<?php tf_arrow_link( get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' ), __( 'All insights', 'thinkfurther' ) ); ?>
				</div>
			</div>

			<div class="insights__grid">
				<?php
				while ( $tf_latest->have_posts() ) :
					$tf_latest->the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();
