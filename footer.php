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
				<p class="site-footer__cta-heading"><?php esc_html_e( 'Ready to look further?', 'thinkfurther' ); ?></p>
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

			<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Key services', 'thinkfurther' ); ?>">
				<h2 class="site-footer__heading"><?php esc_html_e( 'Services', 'thinkfurther' ); ?></h2>
				<ul class="site-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/ai-consultancy/ai-strategy/' ) ); ?>"><?php esc_html_e( 'AI strategy consulting', 'thinkfurther' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/ai-consultancy/ai-automation/' ) ); ?>"><?php esc_html_e( 'AI automation & agents', 'thinkfurther' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/ai-search/generative-engine-optimisation/' ) ); ?>"><?php esc_html_e( 'Generative engine optimisation', 'thinkfurther' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/ai-search/answer-engine-optimisation/' ) ); ?>"><?php esc_html_e( 'Answer engine optimisation', 'thinkfurther' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/ai-consultancy/london/' ) ); ?>"><?php esc_html_e( 'AI consultancy London', 'thinkfurther' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/ai-search/ai-seo-london/' ) ); ?>"><?php esc_html_e( 'AI SEO consultancy London', 'thinkfurther' ); ?></a></li>
				</ul>
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

		<div class="site-footer__legal">
			<ul class="site-footer__links site-footer__links--inline">
				<li><a href="<?php echo esc_url( home_url( '/glossary/' ) ); ?>"><?php esc_html_e( 'Glossary', 'thinkfurther' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/careers/' ) ); ?>"><?php esc_html_e( 'Careers', 'thinkfurther' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy policy', 'thinkfurther' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms & conditions', 'thinkfurther' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookie policy', 'thinkfurther' ); ?></a></li>
			</ul>
		</div>

		<div class="site-footer__bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Alugence. <?php esc_html_e( 'All rights reserved.', 'thinkfurther' ); ?></p>
			<p class="site-footer__domain">alugence.ai</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
