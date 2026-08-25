<?php
/**
 * Brand AI Visibility Checker — backend service.
 *
 * Exposes a single REST endpoint that measures how visible a brand is across
 * AI assistants / AI search engines (ChatGPT, Claude, Perplexity, Gemini):
 * citations, mentions, share of voice vs competitors, sentiment, the sources
 * AI pulls from, and an overall "AI Visibility Score" out of 100.
 *
 * DESIGN
 *  - Each AI source is a small, self-contained function (tf_avc_query_*)
 *    registered in tf_avc_sources(). Add or remove a source by editing that
 *    registry and adding/removing one function — nothing else changes.
 *  - API keys are read ONLY from environment variables (with a wp-config
 *    constant fallback). Nothing is hardcoded. Supply keys via the host's
 *    environment or wp-config.php; see tf_avc_api_key().
 *  - If a source has no key configured, that source returns clearly-labelled
 *    SAMPLE data so the tool stays demonstrable — but the real-call code path
 *    is fully built and runs the moment a key is present.
 *
 * COST CONTROL
 *  - A valid email is required before any API call is made.
 *  - Each visitor is capped at TF_AVC_DAILY_LIMIT checks per rolling 24h,
 *    keyed on BOTH IP address and email.
 *  - Every check is logged (timestamp, email, brand, IP, score, summary) to a
 *    custom table, reviewable under Tools → AI Visibility Log.
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------------------------
 * Configurable constants (override in wp-config.php before this loads).
 * ------------------------------------------------------------------------- */
if ( ! defined( 'TF_AVC_DAILY_LIMIT' ) ) {
	define( 'TF_AVC_DAILY_LIMIT', 3 );      // checks per visitor per rolling 24h
}
if ( ! defined( 'TF_AVC_MAX_COMPETITORS' ) ) {
	define( 'TF_AVC_MAX_COMPETITORS', 5 );  // competitor names accepted per check
}
if ( ! defined( 'TF_AVC_HTTP_TIMEOUT' ) ) {
	define( 'TF_AVC_HTTP_TIMEOUT', 25 );    // seconds per upstream API call
}

/* ---------------------------------------------------------------------------
 * Source registry — add/remove an AI source here.
 * Each 'fn' returns a normalised result via tf_avc_normalise().
 * ------------------------------------------------------------------------- */
function tf_avc_sources() {
	return array(
		'openai'     => array(
			'label' => 'ChatGPT (OpenAI)',
			'fn'    => 'tf_avc_query_openai',
			'env'   => 'OPENAI_API_KEY',
		),
		'anthropic'  => array(
			'label' => 'Claude (Anthropic)',
			'fn'    => 'tf_avc_query_anthropic',
			'env'   => 'ANTHROPIC_API_KEY',
		),
		'perplexity' => array(
			'label' => 'Perplexity',
			'fn'    => 'tf_avc_query_perplexity',
			'env'   => 'PERPLEXITY_API_KEY',
		),
		'gemini'     => array(
			'label' => 'Google Gemini',
			'fn'    => 'tf_avc_query_gemini',
			'env'   => 'GOOGLE_API_KEY',
		),
	);
}

/**
 * Read an API key from the environment (preferred) or a wp-config constant.
 * Never returns a hardcoded value.
 *
 * @param string $name Env var / constant name.
 * @return string
 */
function tf_avc_api_key( $name ) {
	$val = getenv( $name );
	if ( is_string( $val ) && '' !== trim( $val ) ) {
		return trim( $val );
	}
	if ( defined( $name ) && '' !== trim( (string) constant( $name ) ) ) {
		return trim( (string) constant( $name ) );
	}
	return '';
}

/* ===========================================================================
 * REST endpoint
 * ========================================================================= */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'alugence/v1',
			'/visibility-check',
			array(
				'methods'             => 'POST',
				'callback'            => 'tf_avc_handle_check',
				'permission_callback' => '__return_true', // public tool; gated by email + rate limit + honeypot below
			)
		);
	}
);

/**
 * REST handler: validate, rate-limit, run sources, aggregate, log, respond.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function tf_avc_handle_check( WP_REST_Request $request ) {
	// Honeypot: bots fill hidden fields. Pretend nothing happened.
	if ( '' !== trim( (string) $request->get_param( 'company_url' ) ) ) {
		return new WP_Error( 'tf_avc_spam', __( 'Request rejected.', 'thinkfurther' ), array( 'status' => 400 ) );
	}

	$email = sanitize_email( (string) $request->get_param( 'email' ) );
	$brand = sanitize_text_field( (string) $request->get_param( 'brand' ) );
	$url   = esc_url_raw( (string) $request->get_param( 'url' ) );

	$competitors = array();
	$raw_comp    = $request->get_param( 'competitors' );
	if ( is_array( $raw_comp ) ) {
		foreach ( $raw_comp as $c ) {
			$c = sanitize_text_field( (string) $c );
			if ( '' !== $c ) {
				$competitors[] = $c;
			}
		}
	} elseif ( is_string( $raw_comp ) && '' !== trim( $raw_comp ) ) {
		foreach ( preg_split( '/[,\n]+/', $raw_comp ) as $c ) {
			$c = sanitize_text_field( trim( $c ) );
			if ( '' !== $c ) {
				$competitors[] = $c;
			}
		}
	}
	$competitors = array_slice( array_values( array_unique( $competitors ) ), 0, TF_AVC_MAX_COMPETITORS );

	// Email is required BEFORE any paid API call.
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'tf_avc_email', __( 'Please enter a valid email address to run the check.', 'thinkfurther' ), array( 'status' => 422 ) );
	}
	if ( '' === $brand ) {
		return new WP_Error( 'tf_avc_brand', __( 'Please enter a brand name to check.', 'thinkfurther' ), array( 'status' => 422 ) );
	}

	// Rate limit per visitor (IP and email), rolling 24h.
	$ip = tf_avc_client_ip();
	if ( tf_avc_over_limit( $ip, $email ) ) {
		return new WP_Error(
			'tf_avc_rate',
			sprintf(
				/* translators: %d: daily check limit. */
				__( 'You\'ve reached the limit of %d checks for today. Book a call and we\'ll run a full audit for you.', 'thinkfurther' ),
				(int) TF_AVC_DAILY_LIMIT
			),
			array( 'status' => 429 )
		);
	}

	// Run every source (real call when a key exists, sample otherwise).
	$results = array();
	foreach ( tf_avc_sources() as $key => $src ) {
		$api_key = tf_avc_api_key( $src['env'] );
		if ( '' === $api_key ) {
			$results[ $key ] = tf_avc_sample_source( $key, $src['label'], $brand, $competitors );
			continue;
		}
		$out = call_user_func( $src['fn'], $brand, $url, $competitors, $api_key );
		$results[ $key ] = tf_avc_normalise( $key, $src['label'], $out );
	}

	$report = tf_avc_aggregate( $brand, $url, $competitors, $results );

	// Log for usage / cost review.
	tf_avc_log_check( $email, $brand, $ip, $report );

	return rest_ensure_response( $report );
}

/* ===========================================================================
 * Per-source query functions — one real API call each.
 * All read their key from tf_avc_api_key() upstream; each returns a raw array
 * { text, citations[], error } that tf_avc_normalise() interprets.
 * ========================================================================= */

/**
 * Shared prompt asking a model to describe the brand as it normally would.
 */
function tf_avc_prompt( $brand, $url, $competitors ) {
	$p  = "Describe the company or brand \"{$brand}\"";
	if ( $url ) {
		$p .= " (website: {$url})";
	}
	$p .= ". Explain what it does and how it is regarded. ";
	if ( $competitors ) {
		$p .= 'Compare it briefly to these competitors where relevant: ' . implode( ', ', $competitors ) . '. ';
	}
	$p .= 'List the main sources or websites you would rely on for this information as full URLs. '
		. 'If you do not have reliable information about this brand, say so plainly.';
	return $p;
}

/** ChatGPT / OpenAI Chat Completions. */
function tf_avc_query_openai( $brand, $url, $competitors, $api_key ) {
	$resp = tf_avc_http_post(
		'https://api.openai.com/v1/chat/completions',
		array(
			'headers' => array( 'Authorization' => 'Bearer ' . $api_key ),
			'body'    => array(
				'model'    => defined( 'TF_AVC_OPENAI_MODEL' ) ? TF_AVC_OPENAI_MODEL : 'gpt-4o-mini',
				'messages' => array(
					array( 'role' => 'user', 'content' => tf_avc_prompt( $brand, $url, $competitors ) ),
				),
			),
		)
	);
	if ( is_wp_error( $resp ) ) {
		return array( 'error' => $resp->get_error_message() );
	}
	$text = isset( $resp['choices'][0]['message']['content'] ) ? $resp['choices'][0]['message']['content'] : '';
	return array( 'text' => $text, 'citations' => tf_avc_extract_urls( $text ) );
}

/** Claude / Anthropic Messages API. */
function tf_avc_query_anthropic( $brand, $url, $competitors, $api_key ) {
	$resp = tf_avc_http_post(
		'https://api.anthropic.com/v1/messages',
		array(
			'headers' => array(
				'x-api-key'         => $api_key,
				'anthropic-version' => '2023-06-01',
			),
			'body'    => array(
				'model'      => defined( 'TF_AVC_ANTHROPIC_MODEL' ) ? TF_AVC_ANTHROPIC_MODEL : 'claude-3-5-haiku-latest',
				'max_tokens' => 1024,
				'messages'   => array(
					array( 'role' => 'user', 'content' => tf_avc_prompt( $brand, $url, $competitors ) ),
				),
			),
		)
	);
	if ( is_wp_error( $resp ) ) {
		return array( 'error' => $resp->get_error_message() );
	}
	$text = '';
	if ( ! empty( $resp['content'] ) && is_array( $resp['content'] ) ) {
		foreach ( $resp['content'] as $block ) {
			if ( isset( $block['text'] ) ) {
				$text .= $block['text'] . "\n";
			}
		}
	}
	return array( 'text' => $text, 'citations' => tf_avc_extract_urls( $text ) );
}

/** Perplexity — returns real AI-search citations natively. */
function tf_avc_query_perplexity( $brand, $url, $competitors, $api_key ) {
	$resp = tf_avc_http_post(
		'https://api.perplexity.ai/chat/completions',
		array(
			'headers' => array( 'Authorization' => 'Bearer ' . $api_key ),
			'body'    => array(
				'model'    => defined( 'TF_AVC_PERPLEXITY_MODEL' ) ? TF_AVC_PERPLEXITY_MODEL : 'sonar',
				'messages' => array(
					array( 'role' => 'user', 'content' => tf_avc_prompt( $brand, $url, $competitors ) ),
				),
			),
		)
	);
	if ( is_wp_error( $resp ) ) {
		return array( 'error' => $resp->get_error_message() );
	}
	$text      = isset( $resp['choices'][0]['message']['content'] ) ? $resp['choices'][0]['message']['content'] : '';
	$citations = array();
	if ( ! empty( $resp['citations'] ) && is_array( $resp['citations'] ) ) {
		$citations = $resp['citations']; // Perplexity returns a URL list
	}
	$citations = array_values( array_unique( array_merge( $citations, tf_avc_extract_urls( $text ) ) ) );
	return array( 'text' => $text, 'citations' => $citations );
}

/** Google Gemini (generative language API). */
function tf_avc_query_gemini( $brand, $url, $competitors, $api_key ) {
	$model = defined( 'TF_AVC_GEMINI_MODEL' ) ? TF_AVC_GEMINI_MODEL : 'gemini-1.5-flash';
	$resp  = tf_avc_http_post(
		'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode( $model ) . ':generateContent?key=' . rawurlencode( $api_key ),
		array(
			'body' => array(
				'contents' => array(
					array( 'parts' => array( array( 'text' => tf_avc_prompt( $brand, $url, $competitors ) ) ) ),
				),
			),
		)
	);
	if ( is_wp_error( $resp ) ) {
		return array( 'error' => $resp->get_error_message() );
	}
	$text = '';
	if ( ! empty( $resp['candidates'][0]['content']['parts'] ) ) {
		foreach ( $resp['candidates'][0]['content']['parts'] as $part ) {
			if ( isset( $part['text'] ) ) {
				$text .= $part['text'] . "\n";
			}
		}
	}
	return array( 'text' => $text, 'citations' => tf_avc_extract_urls( $text ) );
}

/**
 * Shared JSON POST helper (wp_remote_post). Returns decoded body or WP_Error.
 *
 * @param string $endpoint URL.
 * @param array  $args     { headers: [], body: array (json-encoded) }.
 * @return array|WP_Error
 */
function tf_avc_http_post( $endpoint, $args ) {
	$headers = isset( $args['headers'] ) ? $args['headers'] : array();
	$headers['Content-Type'] = 'application/json';

	$response = wp_remote_post(
		$endpoint,
		array(
			'timeout' => TF_AVC_HTTP_TIMEOUT,
			'headers' => $headers,
			'body'    => wp_json_encode( isset( $args['body'] ) ? $args['body'] : array() ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( $code < 200 || $code >= 300 ) {
		$msg = isset( $body['error']['message'] ) ? $body['error']['message'] : ( 'HTTP ' . $code );
		return new WP_Error( 'tf_avc_http', $msg );
	}
	return is_array( $body ) ? $body : array();
}

/* ===========================================================================
 * Analysis helpers
 * ========================================================================= */

/** Extract http(s) URLs from free text. */
function tf_avc_extract_urls( $text ) {
	if ( ! $text ) {
		return array();
	}
	preg_match_all( '#https?://[^\s\)\]\}"\'<>]+#i', $text, $m );
	$urls = array();
	foreach ( $m[0] as $u ) {
		$urls[] = rtrim( $u, '.,;' );
	}
	return array_values( array_unique( $urls ) );
}

/** Case-insensitive "does this text name that term?" */
function tf_avc_text_mentions( $text, $needle ) {
	if ( '' === trim( (string) $needle ) || '' === trim( (string) $text ) ) {
		return false;
	}
	return false !== stripos( $text, $needle );
}

/**
 * Lightweight sentiment classifier over the model's characterisation of the
 * brand. Swappable: replace the body with an LLM classification call if you
 * want model-graded sentiment.
 *
 * @return string positive|neutral|negative
 */
function tf_avc_analyze_sentiment( $text ) {
	$text = strtolower( (string) $text );
	if ( '' === trim( $text ) ) {
		return 'neutral';
	}
	$pos = array( 'leading', 'trusted', 'reputable', 'innovative', 'respected', 'popular', 'strong', 'excellent', 'well-regarded', 'established', 'recommended', 'award', 'praised', 'high-quality', 'expert', 'renowned' );
	$neg = array( 'scam', 'complaint', 'poor', 'lawsuit', 'controversy', 'unreliable', 'criticis', 'negative', 'warning', 'fraud', 'concern', 'issue', 'problem', 'lacks', 'little-known', 'obscure', 'no information', 'not have reliable', 'unable to find' );
	$p = 0;
	$n = 0;
	foreach ( $pos as $w ) {
		$p += substr_count( $text, $w );
	}
	foreach ( $neg as $w ) {
		$n += substr_count( $text, $w );
	}
	if ( $p > $n ) {
		return 'positive';
	}
	if ( $n > $p ) {
		return 'negative';
	}
	return 'neutral';
}

/**
 * Turn a raw source result into the normalised shape the aggregator expects.
 */
function tf_avc_normalise( $key, $label, $raw ) {
	$text      = isset( $raw['text'] ) ? (string) $raw['text'] : '';
	$error     = isset( $raw['error'] ) ? (string) $raw['error'] : '';
	$citations = isset( $raw['citations'] ) && is_array( $raw['citations'] ) ? $raw['citations'] : array();
	return array(
		'key'       => $key,
		'label'     => $label,
		'sample'    => false,
		'error'     => $error,
		'text'      => $text,
		'citations' => $citations,
		'mentioned' => '' === $error && tf_avc_text_mentions( $text, isset( $raw['__brand'] ) ? $raw['__brand'] : '' ),
	);
}

/**
 * Clearly-labelled SAMPLE result for a source with no API key configured.
 * Deterministic per brand so the demo is stable.
 */
function tf_avc_sample_source( $key, $label, $brand, $competitors ) {
	$seed      = crc32( $key . '|' . strtolower( $brand ) );
	$mentioned = ( $seed % 5 ) !== 0;                  // ~80% mention in sample
	$cited     = $mentioned && ( ( $seed % 3 ) !== 0 ); // ~2/3 of those cite
	$sent_pool = array( 'positive', 'neutral', 'neutral', 'positive', 'negative' );
	$sentiment = $mentioned ? $sent_pool[ $seed % count( $sent_pool ) ] : 'neutral';
	$citations = array();
	if ( $cited ) {
		$citations = array( 'https://en.wikipedia.org/wiki/' . rawurlencode( $brand ), 'https://www.g2.com/' );
	}
	return array(
		'key'       => $key,
		'label'     => $label,
		'sample'    => true,
		'error'     => '',
		'text'      => 'SAMPLE: illustrative characterisation of ' . $brand . '.',
		'citations' => $citations,
		'mentioned' => $mentioned,
		'sentiment' => $sentiment,
	);
}

/* ===========================================================================
 * Aggregation → dashboard payload
 * ========================================================================= */
function tf_avc_aggregate( $brand, $url, $competitors, $results ) {
	$total_sources = count( $results );
	$mention_count = 0;
	$cite_count    = 0;
	$all_citations = array();
	$sent_tally    = array( 'positive' => 0, 'neutral' => 0, 'negative' => 0 );
	$comp_mentions = array();
	foreach ( $competitors as $c ) {
		$comp_mentions[ $c ] = 0;
	}
	$brand_mentions = 0;
	$per_source     = array();
	$any_sample     = false;
	$any_real       = false;

	foreach ( $results as $r ) {
		$is_sample = ! empty( $r['sample'] );
		$any_sample = $any_sample || $is_sample;
		$any_real   = $any_real || ! $is_sample;

		// Brand mention.
		$mentioned = $is_sample ? ! empty( $r['mentioned'] ) : tf_avc_text_mentions( $r['text'], $brand );
		if ( $mentioned ) {
			++$mention_count;
			++$brand_mentions;
		}

		// Citations.
		$cites = isset( $r['citations'] ) ? $r['citations'] : array();
		if ( $cites ) {
			++$cite_count;
			$all_citations = array_merge( $all_citations, $cites );
		}

		// Competitor share of voice.
		foreach ( $competitors as $c ) {
			$found = $is_sample
				? ( ( crc32( $r['key'] . $c ) % 3 ) === 0 )
				: tf_avc_text_mentions( $r['text'], $c );
			if ( $found ) {
				++$comp_mentions[ $c ];
			}
		}

		// Sentiment.
		$sentiment = $is_sample
			? ( isset( $r['sentiment'] ) ? $r['sentiment'] : 'neutral' )
			: tf_avc_analyze_sentiment( $r['text'] );
		if ( $mentioned && isset( $sent_tally[ $sentiment ] ) ) {
			++$sent_tally[ $sentiment ];
		}

		$per_source[] = array(
			'label'     => $r['label'],
			'sample'    => $is_sample,
			'error'     => isset( $r['error'] ) ? $r['error'] : '',
			'mentioned' => $mentioned,
			'cited'     => ! empty( $cites ),
			'sentiment' => $mentioned ? $sentiment : '—',
			'excerpt'   => tf_avc_excerpt( isset( $r['text'] ) ? $r['text'] : '', 220 ),
		);
	}

	// Distinct citation URLs → sources list with host + count.
	$sources_cited = tf_avc_rank_sources( $all_citations );

	// Share of voice %.
	$total_voice = $brand_mentions + array_sum( $comp_mentions );
	$sov         = $total_voice > 0 ? (int) round( ( $brand_mentions / $total_voice ) * 100 ) : 0;

	// Dominant sentiment.
	$sent_label = 'neutral';
	if ( array_sum( $sent_tally ) > 0 ) {
		arsort( $sent_tally );
		$sent_label = key( $sent_tally );
	}

	// Composite score /100.
	$mention_rate = $total_sources ? $mention_count / $total_sources : 0;
	$cite_rate    = $total_sources ? $cite_count / $total_sources : 0;
	$sent_score   = 'positive' === $sent_label ? 1 : ( 'negative' === $sent_label ? 0 : 0.5 );
	$score        = (int) round(
		( $mention_rate * 30 ) +
		( $cite_rate * 25 ) +
		( ( $sov / 100 ) * 25 ) +
		( $sent_score * 20 )
	);
	$score = max( 0, min( 100, $score ) );

	$competitor_rows = array();
	foreach ( $comp_mentions as $c => $cnt ) {
		$competitor_rows[] = array(
			'name'     => $c,
			'mentions' => $cnt,
			'share'    => $total_voice > 0 ? (int) round( ( $cnt / $total_voice ) * 100 ) : 0,
		);
	}

	return array(
		'brand'         => $brand,
		'url'           => $url,
		'generated_at'  => gmdate( 'c' ),
		'is_sample'     => $any_sample,
		'all_sample'    => $any_sample && ! $any_real,
		'score'         => $score,
		'score_band'    => tf_avc_score_band( $score ),
		'metrics'       => array(
			'citations'      => array(
				'value'     => count( $sources_cited ),
				'label'     => __( 'Citations', 'thinkfurther' ),
				'sub'       => sprintf( /* translators: %d sources */ __( 'from %d AI sources', 'thinkfurther' ), $cite_count ),
				'explainer' => __( 'How many distinct web sources AI systems linked to when describing your brand. Citations are the strongest signal — they mean AI is pointing users to pages about you.', 'thinkfurther' ),
			),
			'mentions'       => array(
				'value'     => $mention_count,
				'of'        => $total_sources,
				'label'     => __( 'Mentions', 'thinkfurther' ),
				'sub'       => sprintf( /* translators: 1: mentioned, 2: total */ __( '%1$d of %2$d AI sources named you', 'thinkfurther' ), $mention_count, $total_sources ),
				'explainer' => __( 'How many AI assistants named your brand at all — with or without a link. Being named is the baseline: if AI doesn\'t mention you, it can\'t recommend you.', 'thinkfurther' ),
			),
			'share_of_voice' => array(
				'value'       => $sov,
				'label'       => __( 'Share of voice', 'thinkfurther' ),
				'sub'         => __( 'vs named competitors', 'thinkfurther' ),
				'competitors' => $competitor_rows,
				'explainer'   => __( 'Of all the brand mentions across these AI answers, the share that were you rather than the competitors you listed. It shows whether AI treats you as a leader or an also-ran in your space.', 'thinkfurther' ),
			),
			'sentiment'      => array(
				'value'     => $sent_label,
				'label'     => __( 'Sentiment', 'thinkfurther' ),
				'positive'  => $sent_tally['positive'],
				'neutral'   => $sent_tally['neutral'],
				'negative'  => $sent_tally['negative'],
				'explainer' => __( 'How AI characterises your brand when it describes you — positive, neutral or negative. AI answers shape first impressions, so tone matters as much as visibility.', 'thinkfurther' ),
			),
		),
		'sources_cited' => $sources_cited,
		'per_source'    => $per_source,
		'cta'           => array(
			'url'   => function_exists( 'tf_cta_url' ) ? tf_cta_url() : home_url( '/contact/' ),
			'title' => __( 'Want the full audit and a plan to improve your AI visibility?', 'thinkfurther' ),
			'label' => __( 'Book a call', 'thinkfurther' ),
		),
	);
}

/** First N chars of text, tidied. */
function tf_avc_excerpt( $text, $len ) {
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $text ) ) );
	if ( strlen( $text ) <= $len ) {
		return $text;
	}
	return rtrim( substr( $text, 0, $len ) ) . '…';
}

/** Rank citation URLs by frequency, with host label. */
function tf_avc_rank_sources( $urls ) {
	$counts = array();
	foreach ( $urls as $u ) {
		$counts[ $u ] = isset( $counts[ $u ] ) ? $counts[ $u ] + 1 : 1;
	}
	arsort( $counts );
	$out = array();
	foreach ( $counts as $u => $cnt ) {
		$host  = wp_parse_url( $u, PHP_URL_HOST );
		$out[] = array(
			'url'   => $u,
			'host'  => $host ? preg_replace( '/^www\./', '', $host ) : $u,
			'count' => $cnt,
		);
		if ( count( $out ) >= 12 ) {
			break;
		}
	}
	return $out;
}

/** Score → band label + plain-English blurb. */
function tf_avc_score_band( $score ) {
	if ( $score >= 75 ) {
		return array( 'label' => __( 'Strong', 'thinkfurther' ), 'blurb' => __( 'AI systems know your brand and point people to it. Focus on defending share of voice and sentiment.', 'thinkfurther' ) );
	}
	if ( $score >= 45 ) {
		return array( 'label' => __( 'Emerging', 'thinkfurther' ), 'blurb' => __( 'AI mentions you sometimes, but citations and share of voice have real room to grow.', 'thinkfurther' ) );
	}
	if ( $score >= 20 ) {
		return array( 'label' => __( 'Low', 'thinkfurther' ), 'blurb' => __( 'AI rarely surfaces your brand. There\'s a clear opportunity to become the answer in your category.', 'thinkfurther' ) );
	}
	return array( 'label' => __( 'Invisible', 'thinkfurther' ), 'blurb' => __( 'AI systems don\'t reliably know your brand yet. This is where a GEO/AEO programme has the biggest impact.', 'thinkfurther' ) );
}

/* ===========================================================================
 * Rate limiting + logging (custom table)
 * ========================================================================= */
function tf_avc_log_table() {
	global $wpdb;
	return $wpdb->prefix . 'tf_avc_log';
}

/** Create the log table (called on setup / theme switch; safe to re-run). */
function tf_avc_maybe_create_table() {
	global $wpdb;
	$table   = tf_avc_log_table();
	$charset = $wpdb->get_charset_collate();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta(
		"CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			created_at DATETIME NOT NULL,
			email VARCHAR(191) NOT NULL DEFAULT '',
			brand VARCHAR(191) NOT NULL DEFAULT '',
			ip VARCHAR(64) NOT NULL DEFAULT '',
			score TINYINT UNSIGNED NOT NULL DEFAULT 0,
			summary TEXT NULL,
			PRIMARY KEY  (id),
			KEY created_at (created_at),
			KEY email (email),
			KEY ip (ip)
		) {$charset};"
	);
}
add_action( 'after_switch_theme', 'tf_avc_maybe_create_table' );

/** Best-effort client IP (respects a single proxy hop). */
function tf_avc_client_ip() {
	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	foreach ( $keys as $k ) {
		if ( ! empty( $_SERVER[ $k ] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER[ $k ] ) );
			$ip = trim( explode( ',', $ip )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '0.0.0.0';
}

/** True if this IP or email has hit the daily cap. */
function tf_avc_over_limit( $ip, $email ) {
	global $wpdb;
	$table = tf_avc_log_table();
	// If the table doesn't exist yet, create it and allow the check.
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		tf_avc_maybe_create_table();
		return false;
	}
	$since = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );
	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE created_at > %s AND ( ip = %s OR email = %s )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$since,
			$ip,
			$email
		)
	);
	return $count >= (int) TF_AVC_DAILY_LIMIT;
}

/** Persist one check for usage/cost review. */
function tf_avc_log_check( $email, $brand, $ip, $report ) {
	global $wpdb;
	$table   = tf_avc_log_table();
	$summary = sprintf(
		'score=%d; mentions=%d/%d; citations=%d; sov=%d%%; sentiment=%s; sample=%s',
		$report['score'],
		$report['metrics']['mentions']['value'],
		$report['metrics']['mentions']['of'],
		$report['metrics']['citations']['value'],
		$report['metrics']['share_of_voice']['value'],
		$report['metrics']['sentiment']['value'],
		$report['is_sample'] ? 'yes' : 'no'
	);
	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table,
		array(
			'created_at' => current_time( 'mysql', true ),
			'email'      => $email,
			'brand'      => $brand,
			'ip'         => $ip,
			'score'      => (int) $report['score'],
			'summary'    => $summary,
		),
		array( '%s', '%s', '%s', '%d', '%s' )
	);
}

/* ===========================================================================
 * Admin review screen: Tools → AI Visibility Log
 * ========================================================================= */
add_action(
	'admin_menu',
	function () {
		add_management_page(
			__( 'AI Visibility Log', 'thinkfurther' ),
			__( 'AI Visibility Log', 'thinkfurther' ),
			'manage_options',
			'tf-avc-log',
			'tf_avc_render_log_page'
		);
	}
);

function tf_avc_render_log_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table = tf_avc_log_table();
	$rows  = array();
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 200" ); // phpcs:ignore
		$today = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at > %s", gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS ) ) ); // phpcs:ignore
		$all   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore
	} else {
		$today = 0;
		$all   = 0;
	}
	echo '<div class="wrap"><h1>' . esc_html__( 'AI Visibility Log', 'thinkfurther' ) . '</h1>';
	echo '<p>' . sprintf( /* translators: 1: today, 2: all-time, 3: per-visitor cap */ esc_html__( 'Checks in the last 24h: %1$d · all-time: %2$d · per-visitor daily cap: %3$d. Each real check costs API credits.', 'thinkfurther' ), (int) $today, (int) $all, (int) TF_AVC_DAILY_LIMIT ) . '</p>';
	echo '<table class="widefat striped"><thead><tr>';
	foreach ( array( 'When (UTC)', 'Email', 'Brand', 'IP', 'Score', 'Summary' ) as $h ) {
		echo '<th>' . esc_html( $h ) . '</th>';
	}
	echo '</tr></thead><tbody>';
	if ( $rows ) {
		foreach ( $rows as $row ) {
			echo '<tr>';
			echo '<td>' . esc_html( $row->created_at ) . '</td>';
			echo '<td>' . esc_html( $row->email ) . '</td>';
			echo '<td>' . esc_html( $row->brand ) . '</td>';
			echo '<td>' . esc_html( $row->ip ) . '</td>';
			echo '<td>' . esc_html( $row->score ) . '</td>';
			echo '<td><code>' . esc_html( $row->summary ) . '</code></td>';
			echo '</tr>';
		}
	} else {
		echo '<tr><td colspan="6">' . esc_html__( 'No checks logged yet.', 'thinkfurther' ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
}
