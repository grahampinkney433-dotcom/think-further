<?php
/**
 * Template Name: Resources Hub
 * Template Post Type: page
 *
 * /resources/ — master content hub (Tab 2 RS-00): Guides, Blog,
 * Glossary, Free Tools.
 *
 * @package ThinkFurther
 */

get_header();
$tf_spec  = tf_spec_for_path();
$tf_cards = array(
	array( home_url( '/guides/' ), __( 'Guides', 'thinkfurther' ), __( 'Definitive, definition-first guides to GEO, AEO, agentic AI and AI transformation.', 'thinkfurther' ) ),
	array( home_url( '/blog/' ), __( 'Blog', 'thinkfurther' ), __( 'Articles, comparisons and tool round-ups on AI adoption and AI search.', 'thinkfurther' ) ),
	array( home_url( '/glossary/' ), __( 'Glossary', 'thinkfurther' ), __( 'Plain-English definitions of the AI and AI-search terms that matter.', 'thinkfurther' ) ),
	array( home_url( '/tools/' ), __( 'Free tools', 'thinkfurther' ), __( 'Interactive calculators and audits — instant results, no email required.', 'thinkfurther' ) ),
);
?>
<section class="page-hero theme-dark">
	<div class="page-hero__grid" aria-hidden="true"></div>
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<?php tf_eyebrow( __( 'Resources', 'thinkfurther' ), 'reveal' ); ?>
		<h1 class="page-hero__title reveal"><?php echo esc_html( $tf_spec ? $tf_spec['h1'] : get_the_title() ); ?></h1>
		<p class="page-hero__subline reveal"><?php echo esc_html( $tf_spec ? $tf_spec['meta'] : '' ); ?></p>
	</div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'Resource types', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="offer-grid">
			<?php $tf_i = 0; foreach ( $tf_cards as $tf_card ) : $tf_i++; ?>
				<article class="offer offer--light">
					<p class="offer__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $tf_i, 2, '0', STR_PAD_LEFT ) ); ?></p>
					<h2 class="offer__title"><a href="<?php echo esc_url( $tf_card[0] ); ?>"><?php echo esc_html( $tf_card[1] ); ?></a></h2>
					<p><?php echo esc_html( $tf_card[2] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
$tf_guides = new WP_Query( array( 'post_type' => 'tf_guide', 'posts_per_page' => 6, 'no_found_rows' => true ) );
if ( $tf_guides->have_posts() ) :
	?>
	<section class="section section--tight" aria-label="<?php esc_attr_e( 'Featured guides', 'thinkfurther' ); ?>">
		<div class="container">
			<div class="section-head section-head--split">
				<h2 class="section-head__title"><?php esc_html_e( 'Start with the pillar guides', 'thinkfurther' ); ?></h2>
				<?php tf_arrow_link( home_url( '/guides/' ), __( 'All guides', 'thinkfurther' ) ); ?>
			</div>
			<div class="insights__grid">
				<?php
				while ( $tf_guides->have_posts() ) :
					$tf_guides->the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
get_template_part( 'template-parts/cta' );
get_footer();
