<?php
/**
 * Service pillar body (Tab 6 SERVICE PILLAR): links to ALL child
 * service pages (hub→spoke), how we work, case studies, FAQs.
 * Used by /ai-consultancy/ and /ai-search/ templates.
 *
 * @package ThinkFurther
 */

$tf_spec = tf_spec_for_path();
if ( ! $tf_spec ) {
	return;
}
$tf_path     = trailingslashit( wp_parse_url( get_permalink( get_queried_object_id() ), PHP_URL_PATH ) );
$tf_children = tf_spec_children( $tf_path );
$tf_is_a2    = 'A2' === $tf_spec['arm'];
?>

<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( $tf_is_a2 ? __( 'Arm 2 — specialised practice', 'thinkfurther' ) : __( 'Arm 1 — core practice', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec['h1'] ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec['meta'] ); ?></p>
		<div class="hero__actions reveal">
			<a class="btn btn--volt btn--lg" href="<?php echo esc_url( tf_cta_url() ); ?>"><?php esc_html_e( 'Book a discovery call', 'thinkfurther' ); ?> <span aria-hidden="true">&rarr;</span></a>
			<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See pricing', 'thinkfurther' ); ?></a>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Definition', 'thinkfurther' ); ?>">
	<div class="container container--prose">
		<h2><?php echo $tf_is_a2 ? esc_html__( 'What is AI search optimisation?', 'thinkfurther' ) : esc_html__( 'What is AI consultancy?', 'thinkfurther' ); ?></h2>
		<?php tf_placeholder( 'Answer-first definition', 'Direct 2–3 sentence definition in the first 100 words. Quotable and self-contained (AI Overview + LLM extraction target).', 60 ); ?>
	</div>
</section>

<section class="section section--tight theme-dark" id="services" aria-label="<?php esc_attr_e( 'Services', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title"><?php echo $tf_is_a2 ? esc_html__( 'Our AI search services', 'thinkfurther' ) : esc_html__( 'Our AI consultancy services', 'thinkfurther' ); ?></h2>
		</div>
		<div class="offer-grid">
			<?php
			$tf_i = 0;
			foreach ( $tf_children as $tf_child_path => $tf_child ) :
				$tf_i++;
				?>
				<article class="offer">
					<p class="offer__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $tf_i, 2, '0', STR_PAD_LEFT ) ); ?></p>
					<h3 class="offer__title"><a href="<?php echo esc_url( home_url( $tf_child_path ) ); ?>"><?php echo esc_html( $tf_child['h1'] ); ?></a></h3>
					<p><?php echo esc_html( $tf_child['meta'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'How we work', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<h2 class="section-head__title"><?php esc_html_e( 'How we work', 'thinkfurther' ); ?></h2>
			</div>
			<div class="split__body">
				<?php tf_placeholder( 'Engagement model', 'How engagements run for this arm: entry points, typical phases, and how pricing tiers map to them. Link Readiness Sprint / Visibility Audit.', 140 ); ?>
			</div>
		</div>
	</div>
</section>

<?php
$tf_cases = new WP_Query( array( 'post_type' => 'tf_case_study', 'posts_per_page' => 3, 'no_found_rows' => true ) );
if ( $tf_cases->have_posts() ) :
	?>
	<section class="section section--tight" aria-label="<?php esc_attr_e( 'Case studies', 'thinkfurther' ); ?>">
		<div class="container">
			<div class="section-head section-head--split">
				<h2 class="section-head__title"><?php esc_html_e( 'Case studies', 'thinkfurther' ); ?></h2>
				<?php tf_arrow_link( get_post_type_archive_link( 'tf_case_study' ), __( 'All case studies', 'thinkfurther' ) ); ?>
			</div>
			<div class="case-grid">
				<?php
				while ( $tf_cases->have_posts() ) :
					$tf_cases->the_post();
					get_template_part( 'template-parts/content', 'case' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
endif;

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

tf_faq_section( __( 'FAQs', 'thinkfurther' ), __( 'Questions buyers *ask us first.*', 'thinkfurther' ), tf_spec_faqs( $tf_spec ) );

get_template_part( 'template-parts/cta' );
