<?php
/**
 * Pre-footer call to action band.
 *
 * @package ThinkFurther
 */
?>
<section class="cta-band section" aria-label="<?php esc_attr_e( 'Get in touch', 'thinkfurther' ); ?>">
	<div class="container">
		<div class="cta-band__panel theme-dark reveal" data-tilt>
			<div class="cta-band__glow" aria-hidden="true"></div>
			<?php tf_eyebrow( __( 'Start the conversation', 'thinkfurther' ) ); ?>
			<h2 class="cta-band__title">
				<?php echo wp_kses( tf_headline( __( 'Find out what *thinking further* looks like for your business.', 'thinkfurther' ) ), array( 'em' => array() ) ); ?>
			</h2>
			<p class="cta-band__desc"><?php esc_html_e( 'A 30-minute call. We\'ll tell you honestly where AI can move your numbers — and where it can\'t.', 'thinkfurther' ); ?></p>
			<div class="cta-band__actions">
				<a class="btn btn--light btn--lg" href="<?php echo esc_url( tf_cta_url() ); ?>">
					<?php echo esc_html( tf_option( 'tf_hero_cta' ) ); ?>
					<span aria-hidden="true">&rarr;</span>
				</a>
				<a class="arrow-link arrow-link--light" href="mailto:<?php echo esc_attr( antispambot( tf_option( 'tf_contact_email' ) ) ); ?>">
					<span class="arrow-link__label"><?php echo esc_html( antispambot( tf_option( 'tf_contact_email' ) ) ); ?></span>
					<span class="arrow-link__arrow" aria-hidden="true">&rarr;</span>
				</a>
			</div>
		</div>
	</div>
</section>
