<?php
/**
 * Template Name: About
 * Template Post Type: page
 *
 * /about/ — entity-defining page (Tab 6 ABOUT outline; Tab 9 Layer 1).
 * The "Who we are" paragraph is the canonical entity paragraph, reused
 * verbatim on Home and llms.txt.
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec = tf_spec_for_path();
?>

<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'About', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Who we are', 'thinkfurther' ); ?>">
	<div class="container container--prose">
		<h2><?php esc_html_e( 'Who we are', 'thinkfurther' ); ?></h2>
		<p class="guide-quick-answer"><?php echo esc_html( tf_entity_paragraph() ); ?></p>
		<?php tf_placeholder( 'Founding story & mission', 'Founding year, mission and what makes the firm different — consistent with the entity paragraph above.', 150 ); ?>
	</div>
</section>

<section class="section section--tight theme-dark" aria-label="<?php esc_attr_e( 'Our two practices', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="section-head">
			<h2 class="section-head__title"><?php esc_html_e( 'Our two practices', 'thinkfurther' ); ?></h2>
		</div>
		<div class="offer-grid">
			<article class="offer">
				<p class="offer__num" aria-hidden="true">01</p>
				<h3 class="offer__title"><a href="<?php echo esc_url( home_url( '/ai-consultancy/' ) ); ?>"><?php esc_html_e( 'AI consultancy & transformation', 'thinkfurther' ); ?></a></h3>
				<p><?php esc_html_e( 'AI strategy, implementation, automation and agents, custom development, and training — the core practice.', 'thinkfurther' ); ?></p>
			</article>
			<article class="offer">
				<p class="offer__num" aria-hidden="true">02</p>
				<h3 class="offer__title"><a href="<?php echo esc_url( home_url( '/ai-search/' ) ); ?>"><?php esc_html_e( 'AI search consultancy (GEO, AEO, AI SEO)', 'thinkfurther' ); ?></a></h3>
				<p><?php esc_html_e( 'AI/LLM search visibility and optimisation: GEO, AEO, visibility tracking, and ChatGPT/LLM optimisation.', 'thinkfurther' ); ?></p>
			</article>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Our team', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<h2 class="section-head__title"><?php esc_html_e( 'Our team', 'thinkfurther' ); ?></h2>
			</div>
			<div class="split__body">
				<p><?php esc_html_e( 'Named consultants with real credentials — every guide and case study on this site carries an author you can meet.', 'thinkfurther' ); ?></p>
				<div class="about-links">
					<?php tf_arrow_link( home_url( '/about/team/' ), __( 'Meet the team', 'thinkfurther' ) ); ?>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Credentials', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<h2 class="section-head__title"><?php esc_html_e( 'Credentials, partners & recognition', 'thinkfurther' ); ?></h2>
			</div>
			<div class="split__body">
				<?php tf_placeholder( 'Credentials', 'Certifications, technology partnerships, memberships, awards and review widgets — the authoritativeness signals from Tab 10. Only genuine items.', 100 ); ?>
			</div>
		</div>
	</div>
</section>

<section class="section section--tight" aria-label="<?php esc_attr_e( 'Contact and company details', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="split">
			<div class="split__lead">
				<h2 class="section-head__title"><?php esc_html_e( 'Contact & company details', 'thinkfurther' ); ?></h2>
			</div>
			<div class="split__body">
				<ul class="check-list">
					<li><?php echo esc_html( tf_option( 'tf_address' ) ); ?></li>
					<li><a href="mailto:<?php echo esc_attr( antispambot( tf_option( 'tf_contact_email' ) ) ); ?>"><?php echo esc_html( antispambot( tf_option( 'tf_contact_email' ) ) ); ?></a></li>
					<?php if ( tf_option( 'tf_phone' ) ) : ?>
						<li><?php echo esc_html( tf_option( 'tf_phone' ) ); ?></li>
					<?php endif; ?>
					<?php if ( tf_option( 'tf_company_number' ) ) : ?>
						<li><?php echo esc_html( sprintf( __( 'Company no. %s', 'thinkfurther' ), tf_option( 'tf_company_number' ) ) ); ?></li>
					<?php endif; ?>
				</ul>
				<div class="about-links">
					<?php tf_arrow_link( home_url( '/contact/' ), __( 'Contact us', 'thinkfurther' ) ); ?>
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
		<section class="section section--tight"><div class="container container--prose"><div class="prose"><?php the_content(); ?></div></div></section>
		<?php
	endif;
endwhile;

get_template_part( 'template-parts/cta' );
get_footer();
