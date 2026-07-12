<?php
/**
 * Site footer.
 *
 * @package ThinkFurther
 */
?>
</main>

<footer class="site-footer theme-dark">
	<div class="container">
		<div class="site-footer__top">
			<div class="site-footer__brand">
				<?php tf_logo(); ?>
				<p class="site-footer__tagline"><?php echo esc_html( tf_option( 'tf_footer_tagline' ) ); ?></p>
			</div>

			<div class="site-footer__cta">
				<p class="site-footer__cta-heading"><?php esc_html_e( 'Ready to think further?', 'thinkfurther' ); ?></p>
				<a class="btn btn--light btn--lg" href="<?php echo esc_url( tf_cta_url() ); ?>">
					<?php echo esc_html( tf_option( 'tf_hero_cta' ) ); ?>
					<span aria-hidden="true">&rarr;</span>
				</a>
			</div>
		</div>

		<div class="site-footer__grid">
			<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'thinkfurther' ); ?>">
				<h2 class="site-footer__heading"><?php esc_html_e( 'Explore', 'thinkfurther' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'site-footer__links',
						'fallback_cb'    => 'tf_primary_menu_fallback',
						'depth'          => 1,
					)
				);
				?>
			</nav>

			<div class="site-footer__contact">
				<h2 class="site-footer__heading"><?php esc_html_e( 'Contact', 'thinkfurther' ); ?></h2>
				<ul class="site-footer__links">
					<li><a href="mailto:<?php echo esc_attr( antispambot( tf_option( 'tf_contact_email' ) ) ); ?>"><?php echo esc_html( antispambot( tf_option( 'tf_contact_email' ) ) ); ?></a></li>
					<?php if ( tf_option( 'tf_linkedin_url' ) ) : ?>
						<li><a href="<?php echo esc_url( tf_option( 'tf_linkedin_url' ) ); ?>" rel="noopener"><?php esc_html_e( 'LinkedIn', 'thinkfurther' ); ?></a></li>
					<?php endif; ?>
				</ul>
				<p class="site-footer__location"><?php echo esc_html( tf_option( 'tf_location_label' ) ); ?></p>
			</div>

			<div class="site-footer__note">
				<h2 class="site-footer__heading"><?php esc_html_e( 'Two disciplines, one firm', 'thinkfurther' ); ?></h2>
				<p><?php esc_html_e( 'Business AI consultancy and transformation, and AI search visibility — because being capable with AI and being found by AI are now the same problem.', 'thinkfurther' ); ?></p>
			</div>
		</div>

		<div class="site-footer__bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Think Further. <?php esc_html_e( 'All rights reserved.', 'thinkfurther' ); ?></p>
			<p class="site-footer__domain">thinkfurther.ai</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
