<?php
/**
 * SEO & GEO engine (workbook Tabs 5, 8, 9, 13).
 *
 * Spec-driven titles/metas, structured data (Tab 8 types only),
 * breadcrumbs, robots.txt AI-crawler access, noindex rules and the
 * /llms.txt manifest. Reads everything from inc/seo-spec.php.
 *
 * Meta output steps aside automatically when a dedicated SEO plugin
 * (Yoast / Rank Math) is active.
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

/* =========================================================================
 * Titles & meta descriptions (Tab 5: titles <=60, metas <=155)
 * ========================================================================= */

/**
 * Spec/document title for the current view (empty = use WP default).
 *
 * @return string
 */
function tf_document_title() {
	$spec = tf_spec_for_path();
	if ( $spec && ! empty( $spec['title'] ) ) {
		return $spec['title'];
	}
	if ( is_singular( array( 'tf_guide', 'tf_glossary', 'post' ) ) ) {
		$custom = get_post_meta( get_the_ID(), '_tf_title_tag', true );
		if ( $custom ) {
			return $custom;
		}
	}
	return '';
}

/**
 * Filter WP's document title with the spec value.
 *
 * @param string $title Default title.
 * @return string
 */
function tf_filter_title( $title ) {
	if ( tf_seo_plugin_active() ) {
		return $title;
	}
	$spec_title = tf_document_title();
	return $spec_title ? $spec_title : $title;
}
add_filter( 'pre_get_document_title', 'tf_filter_title', 20 );

/**
 * Best-available meta description for the current view.
 *
 * @return string
 */
function tf_get_meta_description() {
	$spec = tf_spec_for_path();
	if ( $spec && ! empty( $spec['meta'] ) ) {
		return $spec['meta'];
	}
	if ( is_singular( array( 'tf_guide', 'tf_glossary', 'post' ) ) ) {
		$custom = get_post_meta( get_the_ID(), '_tf_meta_desc', true );
		if ( $custom ) {
			return $custom;
		}
	}
	if ( is_front_page() ) {
		return tf_option( 'tf_hero_subline' );
	}
	if ( is_singular() && has_excerpt() ) {
		return get_the_excerpt();
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

/* =========================================================================
 * Open Graph / Twitter (unchanged behaviour, spec-aware description)
 * ========================================================================= */

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
	$type        = is_singular( array( 'post', 'tf_guide' ) ) ? 'article' : 'website';

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

/* =========================================================================
 * Breadcrumbs (Tab 7: on every page; Tab 8: BreadcrumbList)
 * ========================================================================= */

/**
 * Breadcrumb trail for the current view: array of [ label, url ].
 * Last item = current page (no url needed).
 *
 * @return array
 */
function tf_breadcrumb_trail() {
	if ( is_front_page() ) {
		return array();
	}

	$trail = array( array( __( 'Home', 'thinkfurther' ), home_url( '/' ) ) );

	// Spec pages: follow the parent chain.
	$spec_path = null;
	if ( is_page() ) {
		$url       = get_permalink( get_queried_object_id() );
		$spec_path = $url ? trailingslashit( wp_parse_url( $url, PHP_URL_PATH ) ) : null;
	}
	$pages = tf_spec_pages();
	if ( $spec_path && isset( $pages[ $spec_path ] ) ) {
		$chain = array();
		$walk  = $spec_path;
		while ( $walk && isset( $pages[ $walk ] ) ) {
			array_unshift( $chain, $walk );
			$walk = isset( $pages[ $walk ]['parent'] ) ? $pages[ $walk ]['parent'] : null;
		}
		foreach ( $chain as $i => $path ) {
			$label = $pages[ $path ]['h1'];
			// Use short nav labels for intermediate crumbs.
			$label   = tf_crumb_label( $path, $label );
			$trail[] = array( $label, home_url( $path ) );
		}
		return $trail;
	}

	// CPTs & blog.
	if ( is_singular( 'tf_guide' ) || is_post_type_archive( 'tf_guide' ) ) {
		$trail[] = array( __( 'Resources', 'thinkfurther' ), home_url( '/resources/' ) );
		$trail[] = array( __( 'Guides', 'thinkfurther' ), get_post_type_archive_link( 'tf_guide' ) );
		if ( is_singular() ) {
			$trail[] = array( get_the_title(), '' );
		}
	} elseif ( is_singular( 'tf_glossary' ) || is_post_type_archive( 'tf_glossary' ) ) {
		$trail[] = array( __( 'Resources', 'thinkfurther' ), home_url( '/resources/' ) );
		$trail[] = array( __( 'Glossary', 'thinkfurther' ), get_post_type_archive_link( 'tf_glossary' ) );
		if ( is_singular() ) {
			$trail[] = array( get_the_title(), '' );
		}
	} elseif ( is_singular( 'tf_case_study' ) || is_post_type_archive( 'tf_case_study' ) ) {
		$trail[] = array( __( 'Case studies', 'thinkfurther' ), get_post_type_archive_link( 'tf_case_study' ) );
		if ( is_singular() ) {
			$trail[] = array( get_the_title(), '' );
		}
	} elseif ( is_singular( 'post' ) || is_home() || is_category() || is_tag() ) {
		$blog = get_option( 'page_for_posts' );
		$trail[] = array( __( 'Resources', 'thinkfurther' ), home_url( '/resources/' ) );
		$trail[] = array( __( 'Blog', 'thinkfurther' ), $blog ? get_permalink( $blog ) : home_url( '/blog/' ) );
		if ( is_singular( 'post' ) ) {
			$trail[] = array( get_the_title(), '' );
		}
	} elseif ( is_page() ) {
		// Non-spec pages: WP hierarchy.
		$ancestors = array_reverse( get_post_ancestors( get_queried_object_id() ) );
		foreach ( $ancestors as $ancestor ) {
			$trail[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
		}
		$trail[] = array( get_the_title(), '' );
	} elseif ( is_singular() ) {
		$trail[] = array( get_the_title(), '' );
	}

	return $trail;
}

/**
 * Short crumb labels for hub paths.
 *
 * @param string $path    Spec path.
 * @param string $default Default (H1).
 * @return string
 */
function tf_crumb_label( $path, $default ) {
	$short = array(
		'/'                => __( 'Home', 'thinkfurther' ),
		'/ai-consultancy/' => __( 'AI Consultancy', 'thinkfurther' ),
		'/ai-search/'      => __( 'AI Search', 'thinkfurther' ),
		'/industries/'     => __( 'Industries', 'thinkfurther' ),
		'/about/'          => __( 'About', 'thinkfurther' ),
		'/resources/'      => __( 'Resources', 'thinkfurther' ),
		'/tools/'          => __( 'Tools', 'thinkfurther' ),
		'/case-studies/'   => __( 'Case studies', 'thinkfurther' ),
		'/pricing/'        => __( 'Pricing', 'thinkfurther' ),
		'/contact/'        => __( 'Contact', 'thinkfurther' ),
	);
	return isset( $short[ $path ] ) ? $short[ $path ] : $default;
}

/* =========================================================================
 * Structured data (Tab 8 — these types only)
 * ========================================================================= */

/**
 * Print a JSON-LD block.
 *
 * @param array $data Schema array.
 */
function tf_jsonld( $data ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}

/**
 * Organization node with @id (referenced by Service.provider etc.).
 *
 * @return array
 */
function tf_org_node() {
	$org = array(
		'@type'       => 'Organization',
		'@id'         => home_url( '/#organization' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'description' => tf_entity_paragraph(),
		'email'       => sanitize_email( tf_option( 'tf_contact_email' ) ),
		'areaServed'  => array( 'GB', 'US' ),
	);

	$founding = tf_option( 'tf_founding_year' );
	if ( $founding ) {
		$org['foundingDate'] = $founding;
	}

	$same_as = array();
	if ( tf_option( 'tf_linkedin_url' ) ) {
		$same_as[] = tf_option( 'tf_linkedin_url' );
	}
	foreach ( array_filter( array_map( 'trim', explode( "\n", (string) tf_option( 'tf_sameas_urls' ) ) ) ) as $url ) {
		$same_as[] = $url;
	}
	if ( $same_as ) {
		$org['sameAs'] = array_values( array_unique( $same_as ) );
	}

	if ( has_custom_logo() ) {
		$logo_src = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
		if ( $logo_src ) {
			$org['logo'] = $logo_src[0];
		}
	}

	$contact_point = array(
		'@type' => 'ContactPoint',
		'email' => sanitize_email( tf_option( 'tf_contact_email' ) ),
		'contactType' => 'sales',
		'areaServed'  => array( 'GB', 'US' ),
	);
	if ( tf_option( 'tf_phone' ) ) {
		$contact_point['telephone'] = tf_option( 'tf_phone' );
	}
	$org['contactPoint'] = $contact_point;

	return $org;
}

/**
 * All structured data for the current view (site-wide + per-template).
 */
function tf_structured_data() {
	if ( tf_seo_plugin_active() ) {
		return;
	}

	/* --- Site-wide: Organization + WebSite (+SearchAction) --- */
	tf_jsonld( array_merge( array( '@context' => 'https://schema.org' ), tf_org_node() ) );
	tf_jsonld(
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'name'            => get_bloginfo( 'name' ),
			'url'             => home_url( '/' ),
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		)
	);

	/* --- Site-wide: BreadcrumbList --- */
	$trail = tf_breadcrumb_trail();
	if ( count( $trail ) > 1 ) {
		$items = array();
		foreach ( $trail as $i => $crumb ) {
			$item = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => wp_strip_all_tags( $crumb[0] ),
			);
			if ( ! empty( $crumb[1] ) ) {
				$item['item'] = $crumb[1];
			}
			$items[] = $item;
		}
		tf_jsonld(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $items,
			)
		);
	}

	/* --- Per-template (Tab 8) --- */
	$spec = tf_spec_for_path();
	$tpl  = $spec ? $spec['template'] : '';

	// Service / pillar / local / sector pages: Service (+ItemList on pillars).
	if ( in_array( $tpl, array( 'service', 'pillar', 'local', 'sector' ), true ) ) {
		$service = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Service',
			'name'        => wp_strip_all_tags( $spec['h1'] ),
			'description' => $spec['meta'],
			'url'         => trailingslashit( get_permalink( get_queried_object_id() ) ),
			'serviceType' => wp_strip_all_tags( $spec['h1'] ),
			'provider'    => array( '@id' => home_url( '/#organization' ) ),
			'areaServed'  => 'local' === $tpl ? array( 'London', 'GB' ) : array( 'GB', 'US' ),
		);
		tf_jsonld( $service );

		if ( 'pillar' === $tpl ) {
			$children = tf_spec_children( trailingslashit( wp_parse_url( get_permalink( get_queried_object_id() ), PHP_URL_PATH ) ) );
			$list     = array();
			$pos      = 1;
			foreach ( $children as $path => $child ) {
				$list[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => wp_strip_all_tags( $child['h1'] ),
					'url'      => home_url( $path ),
				);
			}
			if ( $list ) {
				tf_jsonld(
					array(
						'@context'        => 'https://schema.org',
						'@type'           => 'ItemList',
						'itemListElement' => $list,
					)
				);
			}
		}
	}

	// About: AboutPage (Organization already site-wide).
	if ( 'about' === $tpl ) {
		tf_jsonld(
			array(
				'@context' => 'https://schema.org',
				'@type'    => 'AboutPage',
				'url'      => get_permalink(),
				'about'    => array( '@id' => home_url( '/#organization' ) ),
			)
		);
	}

	// Contact: ContactPage.
	if ( 'contact' === $tpl ) {
		tf_jsonld(
			array(
				'@context' => 'https://schema.org',
				'@type'    => 'ContactPage',
				'url'      => get_permalink(),
				'about'    => array( '@id' => home_url( '/#organization' ) ),
			)
		);
	}

	// Pricing: Service + hasOfferCatalog (Tab 17).
	if ( 'pricing' === $tpl ) {
		$catalogs = array();
		foreach ( tf_spec_pricing() as $arm ) {
			$offers = array();
			foreach ( $arm['tiers'] as $tier ) {
				$offer = array(
					'@type'         => 'Offer',
					'name'          => $tier['name'],
					'description'   => $tier['duration'] . ' — ' . $tier['best'],
					'priceCurrency' => 'GBP',
				);
				if ( preg_match( '/([\d,]+)/', $tier['gbp'], $m ) ) {
					$offer['price'] = str_replace( ',', '', $m[1] );
				}
				$offers[] = $offer;
			}
			$catalogs[] = array(
				'@type'           => 'OfferCatalog',
				'name'            => wp_strip_all_tags( $arm['heading'] ),
				'itemListElement' => $offers,
			);
		}
		tf_jsonld(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'Service',
				'name'            => 'AI consultancy and AI search services',
				'provider'        => array( '@id' => home_url( '/#organization' ) ),
				'areaServed'      => array( 'GB', 'US' ),
				'hasOfferCatalog' => $catalogs,
			)
		);
	}

	// Free tool: WebApplication (Tab 8/16).
	if ( 'roi-calculator' === $tpl || 'ai-visibility-checker' === $tpl || is_page_template( 'page-templates/audit.php' ) ) {
		tf_jsonld(
			array(
				'@context'            => 'https://schema.org',
				'@type'               => 'WebApplication',
				'name'                => wp_strip_all_tags( get_the_title() ),
				'url'                 => get_permalink(),
				'applicationCategory' => 'BusinessApplication',
				'operatingSystem'     => 'Web',
				'offers'              => array(
					'@type'         => 'Offer',
					'price'         => '0',
					'priceCurrency' => 'GBP',
				),
				'provider'            => array( '@id' => home_url( '/#organization' ) ),
			)
		);
	}

	// Articles: guides + blog posts + case studies.
	if ( is_singular( array( 'post', 'tf_guide', 'tf_case_study' ) ) ) {
		$article = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Article',
			'headline'      => wp_strip_all_tags( get_the_title() ),
			'url'           => get_permalink(),
			'datePublished' => get_the_date( DATE_W3C ),
			'dateModified'  => get_the_modified_date( DATE_W3C ),
			'publisher'     => array( '@id' => home_url( '/#organization' ) ),
			'author'        => tf_author_person_node( get_post()->post_author ),
		);
		if ( is_singular( 'tf_case_study' ) ) {
			$article['about'] = get_post_meta( get_the_ID(), '_tf_sector', true );
		}
		tf_jsonld( $article );
	}

	// Glossary term: DefinedTerm + DefinedTermSet.
	if ( is_singular( 'tf_glossary' ) ) {
		tf_jsonld(
			array(
				'@context'          => 'https://schema.org',
				'@type'             => 'DefinedTerm',
				'name'              => wp_strip_all_tags( get_the_title() ),
				// Full one-sentence definition (excerpt); the meta tag may be
				// length-truncated, but structured data carries the whole thing.
				'description'       => wp_strip_all_tags( has_excerpt() ? get_the_excerpt() : tf_get_meta_description() ),
				'url'               => get_permalink(),
				'inDefinedTermSet'  => array(
					'@type' => 'DefinedTermSet',
					'@id'   => home_url( '/glossary/#termset' ),
					'name'  => 'Alugence AI & AI Search Glossary',
					'url'   => home_url( '/glossary/' ),
				),
			)
		);
	}

	// Team bio pages: Person.
	if ( is_page_template( 'page-templates/person.php' ) ) {
		$pid    = get_queried_object_id();
		$person = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Person',
			'name'     => wp_strip_all_tags( get_the_title( $pid ) ),
			'url'      => get_permalink( $pid ),
			'worksFor' => array( '@id' => home_url( '/#organization' ) ),
		);
		$job = get_post_meta( $pid, '_tf_job_title', true );
		if ( $job ) {
			$person['jobTitle'] = $job;
		}
		$linkedin = get_post_meta( $pid, '_tf_linkedin', true );
		if ( $linkedin ) {
			$person['sameAs'] = array( $linkedin );
		}
		$knows = get_post_meta( $pid, '_tf_knows_about', true );
		if ( $knows ) {
			$person['knowsAbout'] = array_map( 'trim', explode( ',', $knows ) );
		}
		$alumni = get_post_meta( $pid, '_tf_alumni_of', true );
		if ( $alumni ) {
			$person['alumniOf'] = $alumni;
		}
		tf_jsonld( $person );
	}
}
add_action( 'wp_head', 'tf_structured_data', 5 );

/**
 * Person node for an article author. Links to the matching team bio
 * page (same slug or display name) when one exists — E-E-A-T authorship.
 *
 * @param int $user_id WP user ID.
 * @return array
 */
function tf_author_person_node( $user_id ) {
	$name = get_the_author_meta( 'display_name', $user_id );
	$node = array(
		'@type' => 'Person',
		'name'  => $name,
	);
	$bio = tf_author_bio_page( $user_id );
	if ( $bio ) {
		$node['url'] = get_permalink( $bio );
	}
	return $node;
}

/**
 * Find the /about/team/{person}/ page for a WP user, if any.
 *
 * @param int $user_id WP user ID.
 * @return WP_Post|null
 */
function tf_author_bio_page( $user_id ) {
	$slug = get_the_author_meta( 'user_nicename', $user_id );
	$page = get_page_by_path( 'about/team/' . $slug );
	return $page instanceof WP_Post ? $page : null;
}

/* =========================================================================
 * FAQ section renderer + FAQPage schema (Tab 6/8/9)
 * (tf_faq_section lives in template-tags.php; used by all templates.)
 * ========================================================================= */

/**
 * Default FAQ set for a spec page (question-format per Tab 6; answers
 * are labelled placeholders for the content stage — source real
 * People-Also-Ask questions when writing copy).
 *
 * @param array $spec Spec record.
 * @return array [ [q, a], ... ]
 */
function tf_spec_faqs( $spec ) {
	// Prefer real FAQ copy from the content layer (feeds both the visible
	// accordion and FAQPage schema) when it exists for this page.
	if ( function_exists( 'tf_content' ) ) {
		$content = tf_content();
		if ( $content && ! empty( $content['faqs'] ) ) {
			$out = array();
			foreach ( $content['faqs'] as $faq ) {
				$out[] = array( $faq[0], $faq[1] );
			}
			return $out;
		}
	}
	$name = strtolower( wp_strip_all_tags( $spec['h1'] ) );
	$ph   = '[PLACEHOLDER — 40–60 word answer-first response, written at content stage from real People Also Ask questions.]';
	return array(
		array( sprintf( 'What is %s?', $name ), $ph ),
		array( sprintf( 'What does %s include?', $name ), $ph ),
		array( sprintf( 'How much does %s cost?', $name ), $ph ),
		array( sprintf( 'How long does %s take?', $name ), $ph ),
	);
}

/* =========================================================================
 * robots.txt + noindex (Tab 13)
 * ========================================================================= */

/**
 * robots.txt: allow AI/search crawlers explicitly, reference sitemap.
 * (Served by WP when no physical robots.txt file exists.)
 *
 * @param string $output Default robots.txt.
 * @return string
 */
function tf_robots_txt( $output ) {
	$ai_bots = array(
		'GPTBot',
		'OAI-SearchBot',
		'ChatGPT-User',
		'PerplexityBot',
		'ClaudeBot',
		'Claude-Web',
		'Google-Extended',
		'Applebot-Extended',
		'Bingbot',
		'CCBot',
	);

	$lines   = array( $output );
	$lines[] = '# AI / LLM crawler access (explicitly allowed — Alugence GEO spec Tab 13)';
	foreach ( $ai_bots as $bot ) {
		$lines[] = 'User-agent: ' . $bot;
		$lines[] = 'Allow: /';
		$lines[] = '';
	}
	$lines[] = 'Sitemap: ' . home_url( '/wp-sitemap.xml' );
	$lines[] = '# LLM manifest: ' . home_url( '/llms.txt' );

	return implode( "\n", $lines );
}
add_filter( 'robots_txt', 'tf_robots_txt', 20 );

/**
 * noindex thin/utility pages (Tab 13): internal search, tag archives.
 *
 * @param array $robots Directives.
 * @return array
 */
function tf_noindex_utility( $robots ) {
	if ( is_search() || is_tag() || is_date() || is_author() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'tf_noindex_utility' );

/* =========================================================================
 * /llms.txt (Tab 9 Layer 3 / Tab 2 SY-01)
 * ========================================================================= */

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
 * Serve llms.txt: entity paragraph + curated key URLs with one-line
 * descriptions (services, guides, about, contact, pricing, tools).
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
	$lines[] = '> ' . tf_entity_paragraph();
	$lines[] = '';
	$lines[] = 'Contact: ' . sanitize_email( tf_option( 'tf_contact_email' ) );
	$lines[] = '';

	$groups = array(
		'AI Consultancy (business AI transformation)' => array( 'A1' ),
		'AI Search Consultancy (GEO / AEO / AI SEO)'  => array( 'A2' ),
		'Company & trust'                             => array( 'Shared' ),
	);

	$pages = tf_spec_pages();
	foreach ( $groups as $heading => $arms ) {
		$lines[] = '## ' . $heading;
		$lines[] = '';
		foreach ( $pages as $path => $rec ) {
			if ( ! in_array( $rec['arm'], $arms, true ) || 'P3' === $rec['priority'] ) {
				continue;
			}
			if ( in_array( $rec['template'], array( 'legal', 'home' ), true ) ) {
				continue;
			}
			$lines[] = sprintf( '- [%s](%s): %s', $rec['h1'], home_url( $path ), $rec['meta'] );
		}
		$lines[] = '';
	}

	// Published guides.
	$guides = get_posts( array( 'post_type' => 'tf_guide', 'posts_per_page' => 20 ) );
	if ( $guides ) {
		$lines[] = '## Guides';
		$lines[] = '';
		foreach ( $guides as $guide ) {
			$desc    = get_post_meta( $guide->ID, '_tf_meta_desc', true );
			$lines[] = sprintf( '- [%s](%s)%s', $guide->post_title, get_permalink( $guide ), $desc ? ': ' . $desc : '' );
		}
		$lines[] = '';
	}

	// Published case studies.
	$cases = get_posts( array( 'post_type' => 'tf_case_study', 'posts_per_page' => 10 ) );
	if ( $cases ) {
		$lines[] = '## Case studies';
		$lines[] = '';
		foreach ( $cases as $case ) {
			$result  = get_post_meta( $case->ID, '_tf_result', true );
			$lines[] = sprintf( '- [%s](%s)%s', $case->post_title, get_permalink( $case ), $result ? ': ' . $result : '' );
		}
		$lines[] = '';
	}

	$posts = get_posts( array( 'posts_per_page' => 10 ) );
	if ( $posts ) {
		$lines[] = '## Latest articles';
		$lines[] = '';
		foreach ( $posts as $post ) {
			$lines[] = sprintf( '- [%s](%s)', $post->post_title, get_permalink( $post ) );
		}
		$lines[] = '';
	}

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
