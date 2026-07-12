<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * @package ThinkFurther
 */

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow' => __( 'Contact', 'thinkfurther' ),
		'title'   => __( 'Tell us where you are. *We\'ll tell you what\'s possible.*', 'thinkfurther' ),
		'subline' => __( 'No pitch, no pressure — a straightforward conversation about whether and where AI can move your numbers.', 'thinkfurther' ),
	)
);
?>

<section class="section" aria-label="<?php esc_attr_e( 'Contact form', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="contact-layout">
			<div class="contact-layout__aside reveal">
				<h2 class="contact-layout__heading"><?php esc_html_e( 'What happens next', 'thinkfurther' ); ?></h2>
				<ol class="contact-steps">
					<li><?php esc_html_e( 'We reply within one working day.', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'A 30-minute call to understand your situation and ambitions.', 'thinkfurther' ); ?></li>
					<li><?php esc_html_e( 'If we can help, a short written view of where we\'d start and why.', 'thinkfurther' ); ?></li>
				</ol>

				<div class="contact-direct">
					<h2 class="contact-layout__heading"><?php esc_html_e( 'Prefer email?', 'thinkfurther' ); ?></h2>
					<a class="arrow-link" href="mailto:<?php echo esc_attr( antispambot( tf_option( 'tf_contact_email' ) ) ); ?>">
						<span class="arrow-link__label"><?php echo esc_html( antispambot( tf_option( 'tf_contact_email' ) ) ); ?></span>
						<span class="arrow-link__arrow" aria-hidden="true">&rarr;</span>
					</a>
					<p class="contact-direct__location"><?php echo esc_html( tf_option( 'tf_location_label' ) ); ?></p>
				</div>
			</div>

			<div class="contact-layout__form reveal">
				<?php tf_contact_form(); ?>
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

get_footer();
