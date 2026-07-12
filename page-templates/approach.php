<?php
/**
 * Template Name: Approach
 * Template Post Type: page
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'Our approach', 'thinkfurther' ),
		'title'   => __( 'Rigorous where it matters. *Fast everywhere else.*', 'thinkfurther' ),
		'subline' => __( 'How we turn ambition into working systems — and why our engagements end with your team stronger, not more dependent.', 'thinkfurther' ),
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'Principles', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<?php tf_eyebrow( __( 'Principles', 'thinkfurther' ), 'reveal' ); ?>
			<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( __( 'Five commitments we make *on every engagement.*', 'thinkfurther' ) ), array( 'em' => array() ) ); ?></h2>
		</div>

		<ol class="principles">
			<li class="principle reveal">
				<h3 class="principle__title"><?php esc_html_e( 'Evidence before enthusiasm', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Every recommendation is grounded in your data, your workflows and your market — with the reasoning shown. If the honest answer is "AI won\'t help here yet", that\'s the answer you get.', 'thinkfurther' ); ?></p>
			</li>
			<li class="principle reveal">
				<h3 class="principle__title"><?php esc_html_e( 'Small, senior teams', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'You work directly with the people doing the thinking and the building. No account-management layer, no juniors learning on your budget.', 'thinkfurther' ); ?></p>
			</li>
			<li class="principle reveal">
				<h3 class="principle__title"><?php esc_html_e( 'Working software over slideware', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Strategy matters, but it\'s proven in production. We aim to have something real running inside the first six weeks of any delivery engagement.', 'thinkfurther' ); ?></p>
			</li>
			<li class="principle reveal">
				<h3 class="principle__title"><?php esc_html_e( 'Measured against baselines', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'We agree what "better" means before we start — hours, cycle time, conversion, share of answer — and report against it throughout.', 'thinkfurther' ); ?></p>
			</li>
			<li class="principle reveal">
				<h3 class="principle__title"><?php esc_html_e( 'Capability transfer by design', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Playbooks, training and documentation are deliverables, not afterthoughts. The engagement succeeds when you no longer need us for it.', 'thinkfurther' ); ?></p>
			</li>
		</ol>
	</div>
</section>

<section class="section section--tight theme-dark" aria-label="<?php esc_attr_e( 'Engagement shape', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<?php tf_eyebrow( __( 'Engagement shape', 'thinkfurther' ), 'reveal' ); ?>
			<h2 class="section-head__title reveal"><?php esc_html_e( 'Diagnose → Design → Deploy → Embed.', 'thinkfurther' ); ?></h2>
		</div>
		<div class="phase-grid">
			<div class="phase reveal">
				<p class="phase__label"><?php esc_html_e( 'Weeks 1–3', 'thinkfurther' ); ?></p>
				<h3 class="phase__title"><?php esc_html_e( 'Diagnose', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Interviews, workflow observation, data review and — where relevant — an AI visibility baseline. Output: an evidenced opportunity map.', 'thinkfurther' ); ?></p>
			</div>
			<div class="phase reveal">
				<p class="phase__label"><?php esc_html_e( 'Weeks 3–5', 'thinkfurther' ); ?></p>
				<h3 class="phase__title"><?php esc_html_e( 'Design', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Prioritisation with your leadership team, solution design, governance decisions and a delivery plan with owners and baselines.', 'thinkfurther' ); ?></p>
			</div>
			<div class="phase reveal">
				<p class="phase__label"><?php esc_html_e( 'Weeks 5–12', 'thinkfurther' ); ?></p>
				<h3 class="phase__title"><?php esc_html_e( 'Deploy', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Build, integrate and ship in short cycles. First system live around week six; measurement running from day one.', 'thinkfurther' ); ?></p>
			</div>
			<div class="phase reveal">
				<p class="phase__label"><?php esc_html_e( 'Ongoing', 'thinkfurther' ); ?></p>
				<h3 class="phase__title"><?php esc_html_e( 'Embed', 'thinkfurther' ); ?></h3>
				<p><?php esc_html_e( 'Training, playbooks and an operating rhythm. Optional retained support for monitoring, iteration and new opportunities.', 'thinkfurther' ); ?></p>
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
