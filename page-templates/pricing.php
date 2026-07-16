<?php
/**
 * Template Name: Pricing
 * Template Post Type: page
 *
 * /pricing/ — Tab 17: tiered pricing for both arms, GBP/USD toggle,
 * comparison table, FAQ, Offer/OfferCatalog schema (in inc/seo.php).
 * Figures come from tf_spec_pricing() and are INDICATIVE — replace
 * there before launch (workbook: "ACTION FOR GRAHAM").
 *
 * @package ThinkFurther
 */

get_header();

$tf_spec    = tf_spec_for_path();
$tf_pricing = tf_spec_pricing();
?>

<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Pricing', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec ? $tf_spec['meta'] : '' ); ?></p>
		<div class="pricing-toggle reveal" role="group" aria-label="<?php esc_attr_e( 'Currency', 'thinkfurther' ); ?>">
			<button type="button" class="pricing-toggle__btn is-active" data-currency="gbp" aria-pressed="true">GBP &pound;</button>
			<button type="button" class="pricing-toggle__btn" data-currency="usd" aria-pressed="false">USD $</button>
		</div>
	</div>
</section>

<?php foreach ( $tf_pricing as $tf_arm_key => $tf_arm ) : ?>
	<section class="section<?php echo 'a2' === $tf_arm_key ? ' section--tight theme-dark' : ''; ?>" aria-label="<?php echo esc_attr( $tf_arm['heading'] ); ?>">
		<div class="container">
			<div class="section-head">
				<h2 class="section-head__title"><?php echo esc_html( $tf_arm['heading'] ); ?></h2>
			</div>
			<div class="pricing-grid">
				<?php foreach ( $tf_arm['tiers'] as $tf_tier ) : ?>
					<article class="pricing-card<?php echo 'a2' === $tf_arm_key ? ' pricing-card--dark' : ''; ?>">
						<h3 class="pricing-card__name"><?php echo esc_html( $tf_tier['name'] ); ?></h3>
						<p class="pricing-card__price">
							<span data-price-gbp><?php echo esc_html( $tf_tier['gbp'] ); ?></span>
							<span data-price-usd hidden><?php echo esc_html( $tf_tier['usd'] ); ?></span>
						</p>
						<p class="pricing-card__duration"><?php echo esc_html( $tf_tier['duration'] ); ?> &middot; <?php esc_html_e( 'ex-VAT, indicative until scoped', 'thinkfurther' ); ?></p>
						<ul class="pricing-card__list">
							<?php foreach ( $tf_tier['includes'] as $tf_inc ) : ?>
								<li><?php echo esc_html( $tf_inc ); ?></li>
							<?php endforeach; ?>
						</ul>
						<p class="pricing-card__best"><strong><?php esc_html_e( 'Best for:', 'thinkfurther' ); ?></strong> <?php echo esc_html( $tf_tier['best'] ); ?></p>
						<div class="pricing-card__actions">
							<a class="btn btn--solid" href="<?php echo esc_url( tf_cta_url() ); ?>"><?php esc_html_e( 'Book a scoping call', 'thinkfurther' ); ?></a>
							<a class="arrow-link" href="<?php echo esc_url( home_url( $tf_tier['link'] ) ); ?>"><span class="arrow-link__label"><?php esc_html_e( 'Related service', 'thinkfurther' ); ?></span><span class="arrow-link__arrow" aria-hidden="true">&rarr;</span></a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endforeach; ?>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Tier comparison', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title"><?php esc_html_e( 'How we scope and what is included', 'thinkfurther' ); ?></h2>
		</div>
		<?php tf_placeholder( 'Scoping explanation', 'How scoping works, what affects price, no-hidden-fees commitment, GDPR line, and links to case studies showing outcomes for the price (Tab 17 trust rules).', 130 ); ?>
		<div class="table-scroll">
			<table class="pricing-table">
				<caption class="screen-reader-text"><?php esc_html_e( 'Engagement tiers compared', 'thinkfurther' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Tier', 'thinkfurther' ); ?></th>
						<th scope="col"><?php esc_html_e( 'From (GBP)', 'thinkfurther' ); ?></th>
						<th scope="col"><?php esc_html_e( 'From (USD)', 'thinkfurther' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Duration', 'thinkfurther' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Best for', 'thinkfurther' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $tf_pricing as $tf_arm ) : ?>
						<?php foreach ( $tf_arm['tiers'] as $tf_tier ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $tf_tier['name'] ); ?></th>
								<td><?php echo esc_html( $tf_tier['gbp'] ); ?></td>
								<td><?php echo esc_html( $tf_tier['usd'] ); ?></td>
								<td><?php echo esc_html( $tf_tier['duration'] ); ?></td>
								<td><?php echo esc_html( $tf_tier['best'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>

<?php
while ( have_posts() ) :
	the_post();
	if ( trim( get_the_content() ) ) :
		?>
		<section class="section section--tight"><div class="container container--prose"><div class="prose"><?php the_content(); ?></div></div></section>
		<?php
	endif;
endwhile;

// FAQ per Tab 17 (real questions, placeholder answers for content stage).
$tf_ph = '[PLACEHOLDER — 40–60 word answer-first response, written at content stage.]';
tf_faq_section(
	__( 'FAQs', 'thinkfurther' ),
	__( 'Pricing questions, *answered directly.*', 'thinkfurther' ),
	array(
		array( __( 'How much does AI consulting cost?', 'thinkfurther' ), $tf_ph ),
		array( __( 'What is included in each tier?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Do you offer fixed fees?', 'thinkfurther' ), $tf_ph ),
		array( __( 'What is the typical payback window?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Are prices ex-VAT?', 'thinkfurther' ), $tf_ph ),
		array( __( 'Do you work with US clients?', 'thinkfurther' ), $tf_ph ),
	)
);

get_template_part( 'template-parts/cta' );
get_footer();
