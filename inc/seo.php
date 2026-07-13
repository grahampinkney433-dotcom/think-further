<?php
/**
 * SEO & machine readability: JSON-LD, meta description, Open Graph,
 * and a dynamically generated /llms.txt.
 *
 * Everything here steps aside automatically when a dedicated SEO
 * plugin (Yoast / Rank Math) is active.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a dedicated SEO plugin is handling meta output.
 *
 * @return bool
 */
function tf_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}

/**
 * Best-available description for the current view.
 *
 * @return string
 */
function tf_get_meta_description() {
	if ( is_front_page() ) {
		return tf_option( 'tf_hero_subline' );
	}
	if ( is_singular() && has_excerpt() ) {
		return get_the_excerpt();
	}
	if ( is_page( array( 'ai-consultancy', 'ai-search', 'approach', 'contact', 'about' ) ) ) {
		$map = array(
			'ai-consultancy' => __( 'AI consultancy and transformation for ambitious businesses: opportunity audits, strategy, custom AI systems and agentic workflows — measured against baselines you agree before we start.', 'thinkfurther' ),
			'ai-search'      => __( 'AI search consultancy: measure and improve how ChatGPT, Claude, Perplexity, Gemini and AI Overviews describe, recommend and cite your business.', 'thinkfurther' ),
			'approach'       => __( 'How Alugence works: evidence before enthusiasm, small senior teams, working software over slideware, and capability transfer by design.', 'thinkfurther' ),
			'contact'        => __( 'Talk to Alugence about AI transformation or AI search visibility. We reply within one working day.', 'thinkfurther' ),
			'about'          => __( 'Alugence is an AI consultancy with two practices: business AI transformation, and AI search visibility — one firm for being capable with AI and found by it.', 'thinkfurther' ),
		);
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( $map[ $slug ] ) ) {
			return $map[ $slug ];
		}
	}
	return '';
}

/**
 * Meta description tag.
 */
function tf_meta_description() {
	if ( tf_seo_plugin_active() ) {
		return;
	}
	$description = tf_get_meta_description();
	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $description ) ) );
	}
}
add_action( 'wp_head', 'tf_meta_description', 4 );

/**
 * Open Graph / Twitter card tags.
 */
function tf_og_tags() {
	if ( tf_seo_plugin_active() ) {
		return;
	}

	$title       = wp_get_document_title();
	$description = wp_strip_all_tags( tf_get_meta_description() );
	$url         = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
	$type        = is_singular( 'post' ) ? 'article' : 'website';

	$image = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'post-thumbnail' );
		if ( $src ) {
			$image = $src[0];
		}
	}
	if ( ! $image && get_site_icon_url( 512 ) ) {
		$image = get_site_icon_url( 512 );
	}

	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( trailingslashit( $url ) ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	} else {
		echo '<meta name="twitter:card" content="summary">' . "\n";
	}
}
add_action( 'wp_head', 'tf_og_tags', 6 );

/**
 * Structured data: ProfessionalService JSON-LD.
 */
function tf_structured_data() {
	if ( tf_seo_plugin_active() ) {
		return;
	}

	$organization = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'ProfessionalService',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'description' => __( 'AI consultancy with two practices: business AI consultancy and transformation, and AI search visibility and optimisation.', 'thinkfurther' ),
		'email'       => sanitize_email( tf_option( 'tf_contact_email' ) ),
		'knowsAbout'  => array(
			'AI strategy',
			'AI transformation',
			'Agentic workflows',
			'AI search optimisation',
			'Answer engine optimisation',
			'Generative engine optimisation',
		),
	);

	$linkedin = tf_option( 'tf_linkedin_url' );
	if ( $linkedin ) {
		$organization['sameAs'] = array( $linkedin );
	}

	if ( has_custom_logo() ) {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $logo_src ) {
			$organization['logo'] = $logo_src[0];
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $organization ) . '</script>' . "\n";
}
add_action( 'wp_head', 'tf_structured_data', 5 );

/* -------------------------------------------------------------------------
 * /llms.txt — a plain-text site guide for AI crawlers and assistants.
 * ---------------------------------------------------------------------- */

/**
 * Register the llms.txt rewrite.
 */
function tf_llms_rewrite() {
	add_rewrite_rule( '^llms\.txt$', 'index.php?tf_llms=1', 'top' );
}
add_action( 'init', 'tf_llms_rewrite' );

/**
 * Register the query var.
 *
 * @param array $vars Query vars.
 * @return array
 */
function tf_llms_query_var( $vars ) {
	$vars[] = 'tf_llms';
	return $vars;
}
add_filter( 'query_vars', 'tf_llms_query_var' );

/**
 * Serve llms.txt.
 */
function tf_llms_output() {
	if ( '1' !== get_query_var( 'tf_llms' ) ) {
		return;
	}

	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );

	$lines   = array();
	$lines[] = '# ' . get_bloginfo( 'name' );
	$lines[] = '';
	$lines[] = '> ' . wp_strip_all_tags( tf_option( 'tf_hero_subline' ) );
	$lines[] = '';
	$lines[] = wp_strip_all_tags( __( 'Alugence is an AI consultancy with two practices: business AI consultancy and transformation (the core practice), and AI search consultancy (a specialised service line for visibility in ChatGPT, Claude, Perplexity, Gemini and AI Overviews). Contact:', 'thinkfurther' ) ) . ' ' . sanitize_email( tf_option( 'tf_contact_email' ) );
	$lines[] = '';
	$lines[] = '## Services';
	$lines[] = '';

	$services = array(
		array( 'page-templates/ai-transformation.php', '/ai-consultancy/', __( 'AI Consultancy & Transformation', 'thinkfurther' ), __( 'Opportunity audits, AI strategy and roadmaps, custom AI systems and agents, enablement and governance.', 'thinkfurther' ) ),
		array( 'page-templates/ai-search.php', '/ai-search/', __( 'AI Search Consultancy', 'thinkfurther' ), __( 'AI visibility audits, answer-engine optimisation (AEO/GEO), content architecture for LLMs, citation monitoring and share of answer.', 'thinkfurther' ) ),
		array( 'page-templates/approach.php', '/approach/', __( 'Approach', 'thinkfurther' ), __( 'How engagements run: diagnose, design, deploy, embed — measured against agreed baselines.', 'thinkfurther' ) ),
		array( 'page-templates/about.php', '/about/', __( 'About', 'thinkfurther' ), __( 'Why the firm exists and what it believes.', 'thinkfurther' ) ),
		array( 'page-templates/contact.php', '/contact/', __( 'Contact', 'thinkfurther' ), __( 'Start a conversation; replies within one working day.', 'thinkfurther' ) ),
	);
	foreach ( $services as $service ) {
		$lines[] = sprintf( '- [%s](%s): %s', $service[2], tf_page_url_by_template( $service[0], $service[1] ), $service[3] );
	}

	$cases = get_posts(
		array(
			'post_type'      => 'tf_case_study',
			'posts_per_page' => 5,
		)
	);
	if ( $cases ) {
		$lines[] = '';
		$lines[] = '## Case studies';
		$lines[] = '';
		foreach ( $cases as $case ) {
			$result  = get_post_meta( $case->ID, '_tf_result', true );
			$lines[] = sprintf(
				'- [%s](%s)%s',
				$case->post_title,
				get_permalink( $case ),
				$result ? ': ' . $result : ''
			);
		}
	}

	$posts = get_posts( array( 'posts_per_page' => 10 ) );
	if ( $posts ) {
		$lines[] = '';
		$lines[] = '## Latest insights';
		$lines[] = '';
		foreach ( $posts as $post ) {
			$lines[] = sprintf( '- [%s](%s)', $post->post_title, get_permalink( $post ) );
		}
	}

	$lines[] = '';

	// Plain-text response: HTML escaping would corrupt the content.
	echo implode( "\n", $lines ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'template_redirect', 'tf_llms_output', 0 );

/**
 * Keep canonical redirects away from /llms.txt.
 *
 * @param string $redirect_url Proposed redirect.
 * @return string|false
 */
function tf_llms_no_canonical( $redirect_url ) {
	if ( '1' === get_query_var( 'tf_llms' ) ) {
		return false;
	}
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'tf_llms_no_canonical' );

/**
 * Make sure the llms.txt rule exists after theme activation.
 */
function tf_llms_flush() {
	tf_llms_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'tf_llms_flush' );
