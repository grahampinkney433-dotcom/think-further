<?php
/**
 * Reusable presentation helpers.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a headline where *asterisk-wrapped* spans become colour emphasis.
 *
 * @param string $text Raw headline text.
 * @return string Safe HTML.
 */
function tf_headline( $text ) {
	$safe = esc_html( $text );
	return preg_replace( '/\*([^*]+)\*/', '<em>$1</em>', $safe );
}

/**
 * Output the Alugence wordmark (custom logo if set, else built-in mark).
 */
function tf_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php esc_attr_e( 'Alugence — home', 'thinkfurther' ); ?>">
		<span class="site-logo__mark" aria-hidden="true">
			<svg viewBox="0 0 64 64" width="64" height="64" xmlns="http://www.w3.org/2000/svg">
				<rect width="64" height="64" rx="16" fill="#2B34E0"/>
				<g transform="translate(15,15) scale(1.2143)">
					<path d="M4 14 H19" stroke="#fff" stroke-width="2.4" stroke-linecap="round" fill="none"/>
					<path d="M14.5 8 L20.5 14 L14.5 20" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
				</g>
			</svg>
		</span>
		<span class="site-logo__text">Alugence<span class="site-logo__dot">.</span></span>
	</a>
	<?php
}

/**
 * Output a mono-type eyebrow label with tick mark.
 *
 * @param string $text  Label text.
 * @param string $class Extra class names.
 */
function tf_eyebrow( $text, $class = '' ) {
	printf(
		'<p class="eyebrow %s"><span class="eyebrow__tick" aria-hidden="true"></span>%s</p>',
		esc_attr( $class ),
		esc_html( $text )
	);
}

/**
 * Output an arrow-link (text link with animated arrow).
 *
 * @param string $url   Target URL.
 * @param string $label Link label.
 * @param string $class Extra class names.
 */
function tf_arrow_link( $url, $label, $class = '' ) {
	printf(
		'<a class="arrow-link %1$s" href="%2$s"><span class="arrow-link__label">%3$s</span><span class="arrow-link__arrow" aria-hidden="true">&rarr;</span></a>',
		esc_attr( $class ),
		esc_url( $url ),
		esc_html( $label )
	);
}

/**
 * Primary CTA URL: booking link if set, else contact page.
 *
 * @return string
 */
function tf_cta_url() {
	$booking = tf_option( 'tf_booking_url' );
	if ( $booking ) {
		return $booking;
	}
	return tf_page_url_by_template( 'page-templates/contact.php', '/contact/' );
}

/**
 * Post meta line for blog cards and single posts.
 */
function tf_post_meta() {
	?>
	<div class="post-meta">
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		<?php
		$categories = get_the_category();
		if ( ! empty( $categories ) ) :
			?>
			<span class="post-meta__sep" aria-hidden="true">·</span>
			<span class="post-meta__cat"><?php echo esc_html( $categories[0]->name ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render an FAQ section (accessible details/summary accordion)
 * and print matching FAQPage JSON-LD.
 *
 * @param string $eyebrow Section eyebrow.
 * @param string $title   Section title (supports *emphasis*).
 * @param array  $faqs    Array of [ question, answer ] pairs.
 */
function tf_faq_section( $eyebrow, $title, $faqs ) {
	if ( empty( $faqs ) ) {
		return;
	}
	?>
	<section class="section faq-section" aria-label="<?php echo esc_attr( $eyebrow ); ?>">
		<div class="container">
			<div class="split">
				<div class="split__lead">
					<?php tf_eyebrow( $eyebrow, 'reveal' ); ?>
					<h2 class="section-head__title reveal"><?php echo wp_kses( tf_headline( $title ), array( 'em' => array() ) ); ?></h2>
				</div>
				<div class="split__body">
					<div class="faq-list reveal">
						<?php foreach ( $faqs as $faq ) : ?>
							<details class="faq">
								<summary class="faq__q">
									<span><?php echo esc_html( $faq[0] ); ?></span>
									<span class="faq__icon" aria-hidden="true"></span>
								</summary>
								<div class="faq__a"><p><?php echo esc_html( $faq[1] ); ?></p></div>
							</details>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array(),
	);
	foreach ( $faqs as $faq ) {
		$schema['mainEntity'][] = array(
			'@type'          => 'Question',
			'name'           => $faq[0],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq[1],
			),
		);
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}

/**
 * Estimated reading time for the current post.
 *
 * @return string
 */
function tf_reading_time() {
	$words   = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
	$minutes = max( 1, (int) ceil( $words / 220 ) );
	/* translators: %d: minutes. */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'thinkfurther' ), $minutes );
}
