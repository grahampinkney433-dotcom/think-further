<?php
/**
 * Brand AI Visibility Checker — backend data layer.
 * =================================================
 *
 * A REAL backend service (WordPress REST route) that measures how visible a
 * brand is across AI assistants and AI search. It is deliberately split into
 * two layers so the front end never talks to an LLM directly:
 *
 *   Front end  ──POST──▶  /wp-json/tf/v1/visibility-check  ──▶  this file
 *
 * WHAT THIS FILE DOES
 *   1. Enforces cost controls BEFORE spending a penny on API calls:
 *        - a per-visitor daily cap (IP + browser session), configurable;
 *        - a required, validated email address (gates bots / throwaways);
 *        - a honeypot field.
 *   2. Queries each configured AI source. Every source is abstracted behind
 *      its OWN clearly-named function (tf_vis_source_openai, _anthropic,
 *      _gemini, _perplexity) and registered in tf_vis_sources(), so sources
 *      can be added or removed in one place. Keys are read from environment
 *      variables / constants only — NOTHING is hardcoded.
 *   3. If no source has a key configured, it falls back to clearly-labelled
 *      SAMPLE data (sample=true) so the tool is still demonstrable. The real
 *      call path above is fully built, not stubbed.
 *   4. Aggregates the results into citations, mentions, share of voice,
 *      sentiment, sources and an overall AI Visibility Score out of 100.
 *   5. Logs every check (timestamp, brand, email, score, sample flag, sources)
 *      to an option you can review under Tools ▸ AI Visibility Log, and mirrors
 *      it to the PHP error log.
 *
 * SUPPLYING KEYS (you do this separately — never commit keys)
 *   Define any of these as environment variables OR PHP constants in
 *   wp-config.php. A source with no key is skipped automatically.
 *
 *     TF_VIS_OPENAI_KEY       (or env OPENAI_API_KEY)
 *     TF_VIS_ANTHROPIC_KEY    (or env ANTHROPIC_API_KEY)
 *     TF_VIS_GEMINI_KEY       (or env GEMINI_API_KEY / GOOGLE_API_KEY)
 *     TF_VIS_PERPLEXITY_KEY   (or env PERPLEXITY_API_KEY)
 *
 *   Optional model overrides (constants or env), with sensible defaults:
 *     TF_VIS_OPENAI_MODEL      default "gpt-4o-mini"
 *     TF_VIS_ANTHROPIC_MODEL   default "claude-opus-4-8"
 *     TF_VIS_GEMINI_MODEL      default "gemini-1.5-flash"
 *     TF_VIS_PERPLEXITY_MODEL  default "sonar"
 *
 *   Cost control override:
 *     TF_VIS_CHECKS_PER_DAY    default 3
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =================================================================
 * Configuration helpers
 * ================================================================= */

/**
 * Daily check cap per visitor. Configurable via constant/env; default 3.
 *
 * @return int
 */
function tf_vis_daily_limit() {
	$limit = tf_vis_setting( 'TF_VIS_CHECKS_PER_DAY', '' );
	$limit = $limit ? (int) $limit : 3;
	return max( 1, $limit );
}

/**
 * Read a setting from a PHP constant first, then an environment variable.
 * Keys are NEVER hardcoded — this is the only place they are resolved.
 *
 * @param string       $constant Constant name (also tried as an env var).
 * @param string|array $env_alt  Additional env var name(s) to try.
 * @return string Empty string when unset.
 */
function tf_vis_setting( $constant, $env_alt = '' ) {
	if ( defined( $constant ) && '' !== (string) constant( $constant ) ) {
		return (string) constant( $constant );
	}
	$names = array_merge( array( $constant ), (array) $env_alt );
	foreach ( $names as $name ) {
		if ( ! $name ) {
			continue;
		}
		$val = getenv( $name );
		if ( false !== $val && '' !== $val ) {
			return (string) $val;
		}
	}
	return '';
}

/**
 * Registry of AI sources. Each entry names a normalised id, a display label
 * and the callback that queries it. Filterable so you can add/remove sources
 * without editing the engine.
 *
 * @return array<string,array{label:string,callback:callable}>
 */
function tf_vis_sources() {
	$sources = array(
		'chatgpt'    => array(
			'label'    => 'ChatGPT (OpenAI)',
			'callback' => 'tf_vis_source_openai',
		),
		'claude'     => array(
			'label'    => 'Claude (Anthropic)',
			'callback' => 'tf_vis_source_anthropic',
		),
		'gemini'     => array(
			'label'    => 'Google Gemini',
			'callback' => 'tf_vis_source_gemini',
		),
		'perplexity' => array(
			'label'    => 'Perplexity',
			'callback' => 'tf_vis_source_perplexity',
		),
	);

	/**
	 * Filter the AI sources the checker queries.
	 *
	 * @param array $sources Source registry.
	 */
	return apply_filters( 'tf_vis_sources', $sources );
}

/* =================================================================
 * REST route
 * ================================================================= */

/**
 * Register the REST endpoint.
 */
function tf_vis_register_routes() {
	register_rest_route(
		'tf/v1',
		'/visibility-check',
		array(
			'methods'             => 'POST',
			'callback'            => 'tf_vis_handle_request',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'tf_vis_register_routes' );

/**
 * Localised strings + REST metadata for the front end.
 *
 * @return array
 */
function tf_vis_frontend_settings() {
	return array(
		'restUrl'      => esc_url_raw( rest_url( 'tf/v1/visibility-check' ) ),
		'nonce'        => wp_create_nonce( 'wp_rest' ),
		'dailyLimit'   => tf_vis_daily_limit(),
		'ctaUrl'       => tf_cta_url(),
		'contactEmail' => tf_option( 'tf_contact_email' ),
	);
}

/**
 * Main request handler: cost controls → source queries → aggregation → log.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function tf_vis_handle_request( WP_REST_Request $request ) {
	$params = $request->get_json_params();
	if ( empty( $params ) ) {
		$params = $request->get_params();
	}

	// --- Honeypot: pretend success but do nothing (bots fill hidden fields). ---
	if ( ! empty( $params['company_website'] ) ) {
		return tf_vis_error( 'spam', __( 'Request could not be processed.', 'thinkfurther' ), 400 );
	}

	$email = isset( $params['email'] ) ? sanitize_email( wp_unslash( $params['email'] ) ) : '';
	$brand = isset( $params['brand'] ) ? sanitize_text_field( wp_unslash( $params['brand'] ) ) : '';
	$site  = isset( $params['website'] ) ? esc_url_raw( wp_unslash( $params['website'] ) ) : '';

	$competitors = array();
	if ( ! empty( $params['competitors'] ) && is_array( $params['competitors'] ) ) {
		foreach ( $params['competitors'] as $c ) {
			$c = sanitize_text_field( wp_unslash( $c ) );
			if ( '' !== trim( $c ) ) {
				$competitors[] = trim( $c );
			}
		}
	}
	$competitors = array_slice( array_values( array_unique( $competitors ) ), 0, 4 );

	// --- Required-email gate (controls who runs it, curbs abuse). ---
	if ( ! $email || ! is_email( $email ) ) {
		return tf_vis_error( 'email_required', __( 'A valid email address is required to run the check.', 'thinkfurther' ), 422 );
	}

	// --- Required brand. ---
	if ( '' === trim( $brand ) ) {
		return tf_vis_error( 'brand_required', __( 'Please enter the brand you want to check.', 'thinkfurther' ), 422 );
	}
	$brand = trim( $brand );

	// --- Daily usage cap (server-side, authoritative). Checked BEFORE any spend. ---
	$limit = tf_vis_daily_limit();
	$used  = tf_vis_usage_count();
	if ( $used >= $limit ) {
		return tf_vis_error(
			'rate_limited',
			sprintf(
				/* translators: %d: daily limit. */
				__( 'You have reached the free limit of %d checks for today. Book a call for a full, unlimited audit.', 'thinkfurther' ),
				$limit
			),
			429,
			array( 'remaining' => 0, 'limit' => $limit )
		);
	}

	// --- Run the check (real APIs where configured, else sample data). ---
	$query   = array(
		'brand'       => $brand,
		'website'     => $site,
		'competitors' => $competitors,
	);
	$results = tf_vis_run_sources( $query );
	$report  = tf_vis_aggregate( $query, $results['sources'], $results['sample'] );

	// --- Count this check against the visitor's daily allowance. ---
	$remaining = tf_vis_usage_increment( $limit );

	$report['remaining'] = $remaining;
	$report['limit']     = $limit;

	// --- Log for cost/usage review. ---
	tf_vis_log_check( $email, $report, $results['sample'] );

	return new WP_REST_Response( $report, 200 );
}

/**
 * Uniform error response.
 *
 * @param string $code    Machine code.
 * @param string $message Human message.
 * @param int    $status  HTTP status.
 * @param array  $extra   Extra payload.
 * @return WP_REST_Response
 */
function tf_vis_error( $code, $message, $status = 400, $extra = array() ) {
	return new WP_REST_Response(
		array_merge(
			array(
				'ok'      => false,
				'code'    => $code,
				'message' => $message,
			),
			$extra
		),
		$status
	);
}

/* =================================================================
 * Cost control: per-visitor daily usage
 * ================================================================= */

/**
 * Stable, privacy-preserving identifier for the current visitor:
 * a hash of IP + a first-party browser-session cookie.
 *
 * @return string
 */
function tf_vis_visitor_id() {
	$ip = '';
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			// X-Forwarded-For can be a list; take the first entry.
			$ip = trim( explode( ',', $ip )[0] );
			break;
		}
	}

	$session = '';
	if ( ! empty( $_COOKIE['tf_vis_sid'] ) ) {
		$session = sanitize_text_field( wp_unslash( $_COOKIE['tf_vis_sid'] ) );
	}

	return md5( $ip . '|' . $session . '|' . wp_salt( 'nonce' ) );
}

/**
 * Transient key for today's per-visitor counter.
 *
 * @return string
 */
function tf_vis_usage_key() {
	return 'tf_vis_' . tf_vis_visitor_id() . '_' . gmdate( 'Ymd' );
}

/**
 * How many checks this visitor has already run today.
 *
 * @return int
 */
function tf_vis_usage_count() {
	return (int) get_transient( tf_vis_usage_key() );
}

/**
 * Record one check and return the number remaining today.
 *
 * @param int $limit Daily limit.
 * @return int
 */
function tf_vis_usage_increment( $limit ) {
	$key   = tf_vis_usage_key();
	$count = tf_vis_usage_count() + 1;
	// Expire at the end of the UTC day.
	$ttl = strtotime( 'tomorrow midnight UTC' ) - time();
	set_transient( $key, $count, max( 60, $ttl ) );
	return max( 0, $limit - $count );
}

/* =================================================================
 * Source orchestration
 * ================================================================= */

/**
 * Run every configured source. Sources with no API key are skipped. If NOTHING
 * is configured, returns clearly-labelled sample data so the tool still works.
 *
 * @param array $query brand/website/competitors.
 * @return array{sources:array,sample:bool}
 */
function tf_vis_run_sources( $query ) {
	$out       = array();
	$any_live  = false;

	foreach ( tf_vis_sources() as $id => $source ) {
		if ( ! is_callable( $source['callback'] ) ) {
			continue;
		}
		$result = call_user_func( $source['callback'], $query );
		if ( ! is_array( $result ) ) {
			continue;
		}
		$result['id']    = $id;
		$result['label'] = $source['label'];
		if ( ! empty( $result['available'] ) ) {
			$any_live = true;
		}
		$out[] = tf_vis_normalize( $result );
	}

	if ( ! $any_live ) {
		// No keys wired up (or all calls failed) — demonstrable sample data.
		return array(
			'sources' => tf_vis_sample_sources( $query ),
			'sample'  => true,
		);
	}

	return array(
		'sources' => $out,
		'sample'  => false,
	);
}

/**
 * Ensure every source result has the expected shape.
 *
 * @param array $r Raw source result.
 * @return array
 */
function tf_vis_normalize( $r ) {
	$sentiment = isset( $r['sentiment'] ) ? strtolower( (string) $r['sentiment'] ) : 'unknown';
	if ( ! in_array( $sentiment, array( 'positive', 'neutral', 'negative', 'unknown' ), true ) ) {
		$sentiment = 'unknown';
	}

	$sources = array();
	if ( ! empty( $r['sources'] ) && is_array( $r['sources'] ) ) {
		foreach ( $r['sources'] as $s ) {
			$domain = tf_vis_domain( (string) $s );
			if ( $domain ) {
				$sources[] = $domain;
			}
		}
	}

	$competitors = array();
	if ( ! empty( $r['competitors'] ) && is_array( $r['competitors'] ) ) {
		foreach ( $r['competitors'] as $name => $mentioned ) {
			$competitors[ (string) $name ] = (bool) $mentioned;
		}
	}

	return array(
		'id'          => isset( $r['id'] ) ? (string) $r['id'] : '',
		'label'       => isset( $r['label'] ) ? (string) $r['label'] : '',
		'available'   => ! empty( $r['available'] ),
		'mentioned'   => ! empty( $r['mentioned'] ),
		'cited'       => ! empty( $r['cited'] ),
		'sentiment'   => $sentiment,
		'sources'     => array_values( array_unique( $sources ) ),
		'competitors' => $competitors,
		'note'        => isset( $r['note'] ) ? sanitize_text_field( (string) $r['note'] ) : '',
	);
}

/**
 * Extract a bare hostname from a URL or domain string.
 *
 * @param string $value URL or domain.
 * @return string
 */
function tf_vis_domain( $value ) {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	if ( false === strpos( $value, '//' ) ) {
		$value = 'https://' . $value;
	}
	$host = wp_parse_url( $value, PHP_URL_HOST );
	if ( ! $host ) {
		return '';
	}
	return preg_replace( '/^www\./', '', strtolower( $host ) );
}

/* =================================================================
 * The analysis prompt (shared by every LLM source)
 * ================================================================= */

/**
 * Build the instruction sent to each model. We ask for a strict JSON object so
 * results parse identically across providers.
 *
 * @param array $query brand/website/competitors.
 * @return string
 */
function tf_vis_prompt( $query ) {
	$brand = $query['brand'];
	$site  = ! empty( $query['website'] ) ? $query['website'] : '(not provided)';
	$comps = ! empty( $query['competitors'] ) ? implode( ', ', $query['competitors'] ) : '(none provided)';

	return
		"You are auditing how visible a brand is in AI answers. Assess the brand \"{$brand}\" " .
		"(website: {$site}). Named competitors to compare against: {$comps}.\n\n" .
		"Answer as you normally would if a user asked you about this brand and its main " .
		"alternatives, then report what your answer contained. Respond with ONLY a JSON object, " .
		"no markdown fences, using exactly this shape:\n" .
		"{\n" .
		"  \"mentioned\": true|false,           // did you name the brand at all?\n" .
		"  \"cited\": true|false,               // did you point to a specific URL/source for it?\n" .
		"  \"sentiment\": \"positive\"|\"neutral\"|\"negative\",\n" .
		"  \"sources\": [\"example.com\", ...], // domains you would cite for this brand\n" .
		"  \"competitors_mentioned\": [\"Name\", ...], // which of the named competitors you'd mention\n" .
		"  \"summary\": \"one short sentence on how you'd describe the brand\"\n" .
		"}";
}

/**
 * Turn a model's raw text answer into a normalised source result.
 *
 * @param string $text  Raw model output (expected JSON).
 * @param array  $query brand/website/competitors.
 * @return array Partial source result (available flag added by caller).
 */
function tf_vis_parse_model_json( $text, $query ) {
	$data = tf_vis_extract_json( $text );

	$competitors = array();
	foreach ( $query['competitors'] as $name ) {
		$competitors[ $name ] = false;
	}
	if ( $data && ! empty( $data['competitors_mentioned'] ) && is_array( $data['competitors_mentioned'] ) ) {
		foreach ( $data['competitors_mentioned'] as $named ) {
			foreach ( $query['competitors'] as $name ) {
				if ( 0 === strcasecmp( trim( (string) $named ), $name ) ) {
					$competitors[ $name ] = true;
				}
			}
		}
	}

	$sources = array();
	if ( $data && ! empty( $data['sources'] ) && is_array( $data['sources'] ) ) {
		$sources = $data['sources'];
	}

	return array(
		'mentioned'   => $data ? ! empty( $data['mentioned'] ) : false,
		'cited'       => $data ? ! empty( $data['cited'] ) : false,
		'sentiment'   => $data && ! empty( $data['sentiment'] ) ? $data['sentiment'] : 'unknown',
		'sources'     => $sources,
		'competitors' => $competitors,
		'note'        => $data && ! empty( $data['summary'] ) ? (string) $data['summary'] : '',
	);
}

/**
 * Pull the first JSON object out of a model response (tolerates code fences /
 * stray prose around it).
 *
 * @param string $text Model output.
 * @return array|null
 */
function tf_vis_extract_json( $text ) {
	$text = trim( (string) $text );
	if ( '' === $text ) {
		return null;
	}
	$decoded = json_decode( $text, true );
	if ( is_array( $decoded ) ) {
		return $decoded;
	}
	$start = strpos( $text, '{' );
	$end   = strrpos( $text, '}' );
	if ( false !== $start && false !== $end && $end > $start ) {
		$decoded = json_decode( substr( $text, $start, $end - $start + 1 ), true );
		if ( is_array( $decoded ) ) {
			return $decoded;
		}
	}
	return null;
}

/* =================================================================
 * Per-source API callers — one clearly-named function each.
 * Add or remove a source by editing tf_vis_sources() plus a function here.
 * Every one reads its key via tf_vis_setting() (constant/env only).
 * ================================================================= */

/**
 * OpenAI (ChatGPT) via the Chat Completions API.
 *
 * @param array $query brand/website/competitors.
 * @return array
 */
function tf_vis_source_openai( $query ) {
	$key = tf_vis_setting( 'TF_VIS_OPENAI_KEY', 'OPENAI_API_KEY' );
	if ( ! $key ) {
		return array( 'available' => false );
	}
	$model = tf_vis_setting( 'TF_VIS_OPENAI_MODEL' );
	$model = $model ? $model : 'gpt-4o-mini';

	$response = tf_vis_remote_post(
		'https://api.openai.com/v1/chat/completions',
		array(
			'Authorization' => 'Bearer ' . $key,
			'Content-Type'  => 'application/json',
		),
		array(
			'model'           => $model,
			'temperature'     => 0.2,
			'max_tokens'      => 600,
			'response_format' => array( 'type' => 'json_object' ),
			'messages'        => array(
				array( 'role' => 'system', 'content' => 'You output only valid JSON.' ),
				array( 'role' => 'user', 'content' => tf_vis_prompt( $query ) ),
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return array( 'available' => false, 'note' => $response->get_error_message() );
	}

	$text   = isset( $response['choices'][0]['message']['content'] ) ? $response['choices'][0]['message']['content'] : '';
	$result = tf_vis_parse_model_json( $text, $query );
	$result['available'] = true;
	return $result;
}

/**
 * Anthropic (Claude) via the Messages API (raw HTTP; no SDK in this theme).
 *
 * @param array $query brand/website/competitors.
 * @return array
 */
function tf_vis_source_anthropic( $query ) {
	$key = tf_vis_setting( 'TF_VIS_ANTHROPIC_KEY', 'ANTHROPIC_API_KEY' );
	if ( ! $key ) {
		return array( 'available' => false );
	}
	$model = tf_vis_setting( 'TF_VIS_ANTHROPIC_MODEL' );
	$model = $model ? $model : 'claude-opus-4-8';

	$response = tf_vis_remote_post(
		'https://api.anthropic.com/v1/messages',
		array(
			'x-api-key'         => $key,
			'anthropic-version' => '2023-06-01',
			'Content-Type'      => 'application/json',
		),
		array(
			'model'      => $model,
			'max_tokens' => 600,
			'messages'   => array(
				array( 'role' => 'user', 'content' => tf_vis_prompt( $query ) ),
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return array( 'available' => false, 'note' => $response->get_error_message() );
	}

	// Messages API returns content as an array of blocks; concatenate text blocks.
	$text = '';
	if ( ! empty( $response['content'] ) && is_array( $response['content'] ) ) {
		foreach ( $response['content'] as $block ) {
			if ( isset( $block['type'] ) && 'text' === $block['type'] && isset( $block['text'] ) ) {
				$text .= $block['text'];
			}
		}
	}
	$result = tf_vis_parse_model_json( $text, $query );
	$result['available'] = true;
	return $result;
}

/**
 * Google Gemini via the Generative Language API.
 *
 * @param array $query brand/website/competitors.
 * @return array
 */
function tf_vis_source_gemini( $query ) {
	$key = tf_vis_setting( 'TF_VIS_GEMINI_KEY', array( 'GEMINI_API_KEY', 'GOOGLE_API_KEY' ) );
	if ( ! $key ) {
		return array( 'available' => false );
	}
	$model = tf_vis_setting( 'TF_VIS_GEMINI_MODEL' );
	$model = $model ? $model : 'gemini-1.5-flash';

	$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode( $model ) . ':generateContent?key=' . rawurlencode( $key );

	$response = tf_vis_remote_post(
		$url,
		array( 'Content-Type' => 'application/json' ),
		array(
			'contents'         => array(
				array( 'parts' => array( array( 'text' => tf_vis_prompt( $query ) ) ) ),
			),
			'generationConfig' => array(
				'temperature'      => 0.2,
				'responseMimeType' => 'application/json',
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return array( 'available' => false, 'note' => $response->get_error_message() );
	}

	$text = isset( $response['candidates'][0]['content']['parts'][0]['text'] ) ? $response['candidates'][0]['content']['parts'][0]['text'] : '';
	$result = tf_vis_parse_model_json( $text, $query );
	$result['available'] = true;
	return $result;
}

/**
 * Perplexity via its OpenAI-compatible chat API. Perplexity answers from live
 * search, so it also returns native citations we merge into sources.
 *
 * @param array $query brand/website/competitors.
 * @return array
 */
function tf_vis_source_perplexity( $query ) {
	$key = tf_vis_setting( 'TF_VIS_PERPLEXITY_KEY', 'PERPLEXITY_API_KEY' );
	if ( ! $key ) {
		return array( 'available' => false );
	}
	$model = tf_vis_setting( 'TF_VIS_PERPLEXITY_MODEL' );
	$model = $model ? $model : 'sonar';

	$response = tf_vis_remote_post(
		'https://api.perplexity.ai/chat/completions',
		array(
			'Authorization' => 'Bearer ' . $key,
			'Content-Type'  => 'application/json',
		),
		array(
			'model'       => $model,
			'temperature' => 0.2,
			'max_tokens'  => 600,
			'messages'    => array(
				array( 'role' => 'system', 'content' => 'You output only valid JSON.' ),
				array( 'role' => 'user', 'content' => tf_vis_prompt( $query ) ),
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return array( 'available' => false, 'note' => $response->get_error_message() );
	}

	$text   = isset( $response['choices'][0]['message']['content'] ) ? $response['choices'][0]['message']['content'] : '';
	$result = tf_vis_parse_model_json( $text, $query );

	// Perplexity returns real citations — count them and fold into sources.
	if ( ! empty( $response['citations'] ) && is_array( $response['citations'] ) ) {
		$result['sources'] = array_merge( $result['sources'], $response['citations'] );
		$result['cited']   = true;
	}
	$result['available'] = true;
	return $result;
}

/**
 * Shared JSON POST helper with a sane timeout and JSON decoding.
 *
 * @param string $url     Endpoint.
 * @param array  $headers Request headers.
 * @param array  $body    Body (encoded to JSON).
 * @return array|WP_Error Decoded response array, or WP_Error.
 */
function tf_vis_remote_post( $url, $headers, $body ) {
	$response = wp_remote_post(
		$url,
		array(
			'headers' => $headers,
			'body'    => wp_json_encode( $body ),
			'timeout' => 25,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = wp_remote_retrieve_response_code( $response );
	$raw  = wp_remote_retrieve_body( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'tf_vis_http', 'HTTP ' . $code . ': ' . wp_strip_all_tags( substr( $raw, 0, 200 ) ) );
	}
	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		return new WP_Error( 'tf_vis_json', 'Malformed response' );
	}
	return $decoded;
}

/* =================================================================
 * Aggregation → the report the front end renders
 * ================================================================= */

/**
 * Combine per-source results into scores and breakdowns.
 *
 * @param array $query   brand/website/competitors.
 * @param array $sources Normalised source results.
 * @param bool  $sample  Whether these are sample results.
 * @return array
 */
function tf_vis_aggregate( $query, $sources, $sample ) {
	$available = array_values(
		array_filter(
			$sources,
			function ( $s ) {
				return ! empty( $s['available'] );
			}
		)
	);
	$total = max( 1, count( $available ) );

	$mentions  = 0;
	$citations = 0;
	$sentiment = array( 'positive' => 0, 'neutral' => 0, 'negative' => 0 );
	$domains   = array();

	// Share of voice tallies.
	$brand_voice = 0;
	$comp_voice  = array();
	foreach ( $query['competitors'] as $name ) {
		$comp_voice[ $name ] = 0;
	}

	foreach ( $available as $s ) {
		if ( $s['mentioned'] ) {
			++$mentions;
			++$brand_voice;
			if ( isset( $sentiment[ $s['sentiment'] ] ) ) {
				++$sentiment[ $s['sentiment'] ];
			}
		}
		if ( $s['cited'] ) {
			++$citations;
		}
		foreach ( $s['sources'] as $d ) {
			$domains[ $d ] = isset( $domains[ $d ] ) ? $domains[ $d ] + 1 : 1;
		}
		foreach ( $s['competitors'] as $name => $named ) {
			if ( $named && isset( $comp_voice[ $name ] ) ) {
				++$comp_voice[ $name ];
			}
		}
	}

	$mention_rate  = round( ( $mentions / $total ) * 100 );
	$citation_rate = round( ( $citations / $total ) * 100 );

	// Sentiment percentages across assistants that mentioned the brand.
	$sentiment_total = max( 1, array_sum( $sentiment ) );
	$sentiment_pct   = array(
		'positive' => round( ( $sentiment['positive'] / $sentiment_total ) * 100 ),
		'neutral'  => round( ( $sentiment['neutral'] / $sentiment_total ) * 100 ),
		'negative' => round( ( $sentiment['negative'] / $sentiment_total ) * 100 ),
	);
	$sentiment_label = 'neutral';
	if ( $mentions > 0 ) {
		arsort( $sentiment );
		$sentiment_label = (string) array_key_first( $sentiment );
	}
	$sentiment_score = ( $sentiment_pct['positive'] + ( 0.5 * $sentiment_pct['neutral'] ) ) / 100;

	// Share of voice.
	$comp_total = array_sum( $comp_voice );
	$voice_all  = $brand_voice + $comp_total;
	$sov_brand  = $voice_all > 0 ? round( ( $brand_voice / $voice_all ) * 100 ) : 0;
	$sov_comps  = array();
	foreach ( $comp_voice as $name => $count ) {
		$sov_comps[] = array(
			'name'     => $name,
			'mentions' => $count,
			'pct'      => $voice_all > 0 ? round( ( $count / $voice_all ) * 100 ) : 0,
		);
	}

	// Sources, most-cited first.
	arsort( $domains );
	$source_list = array();
	foreach ( $domains as $domain => $count ) {
		$source_list[] = array(
			'domain' => $domain,
			'count'  => $count,
		);
	}

	// Overall AI Visibility Score /100 — weighted blend.
	$score = round(
		( $mention_rate * 0.35 ) +
		( $citation_rate * 0.25 ) +
		( $sov_brand * 0.25 ) +
		( $sentiment_score * 100 * 0.15 )
	);
	$score = max( 0, min( 100, $score ) );

	$band = tf_vis_band( $score );

	return array(
		'ok'          => true,
		'sample'      => (bool) $sample,
		'brand'       => $query['brand'],
		'website'     => $query['website'],
		'generatedAt' => gmdate( 'c' ),
		'score'       => $score,
		'band'        => $band,
		'metrics'     => array(
			'citations'    => array(
				'value'   => $citations,
				'outOf'   => count( $available ),
				'rate'    => $citation_rate,
				'explain' => __( 'How often AI systems point to a specific source or link when they mention you. Citations are what get users clicking through — the closest thing to a ranking in AI search.', 'thinkfurther' ),
			),
			'mentions'     => array(
				'value'   => $mentions,
				'outOf'   => count( $available ),
				'rate'    => $mention_rate,
				'explain' => __( 'How often AI systems name your brand at all, even without a link. Being named is the entry ticket: if assistants don\'t mention you, buyers never hear you exist.', 'thinkfurther' ),
			),
			'shareOfVoice' => array(
				'brand'       => $sov_brand,
				'competitors' => $sov_comps,
				'explain'     => __( 'Your slice of the conversation versus the competitors you named. It shows whether AI answers position you as a leader in your category or an afterthought.', 'thinkfurther' ),
			),
			'sentiment'    => array(
				'label'    => $sentiment_label,
				'positive' => $sentiment_pct['positive'],
				'neutral'  => $sentiment_pct['neutral'],
				'negative' => $sentiment_pct['negative'],
				'explain'  => __( 'How AI systems characterise you when they do talk about you. Tone shapes buying decisions before a prospect ever reaches your site.', 'thinkfurther' ),
			),
			'sources'      => array(
				'list'    => $source_list,
				'explain' => __( 'The websites AI systems lean on to describe your brand. Earning coverage on these sources is the most direct lever for improving how you show up.', 'thinkfurther' ),
			),
		),
		'assistants'  => array_map(
			function ( $s ) {
				return array(
					'id'        => $s['id'],
					'label'     => $s['label'],
					'mentioned' => $s['mentioned'],
					'cited'     => $s['cited'],
					'sentiment' => $s['sentiment'],
					'sources'   => $s['sources'],
					'note'      => $s['note'],
				);
			},
			$available
		),
	);
}

/**
 * Interpret the overall score.
 *
 * @param int $score 0–100.
 * @return array{label:string,summary:string}
 */
function tf_vis_band( $score ) {
	if ( $score >= 75 ) {
		return array(
			'label'   => __( 'Highly visible', 'thinkfurther' ),
			'summary' => __( 'AI assistants know your brand, name it confidently and cite sources for it. The work now is defending that lead as competitors catch up.', 'thinkfurther' ),
		);
	}
	if ( $score >= 50 ) {
		return array(
			'label'   => __( 'Emerging', 'thinkfurther' ),
			'summary' => __( 'You show up in AI answers, but inconsistently — named in some, cited in fewer. Targeted work on sources and coverage would move you into the leading tier.', 'thinkfurther' ),
		);
	}
	if ( $score >= 25 ) {
		return array(
			'label'   => __( 'Low visibility', 'thinkfurther' ),
			'summary' => __( 'AI systems rarely surface you, and competitors are taking the share of voice. This is where focused GEO/AEO work pays back fastest.', 'thinkfurther' ),
		);
	}
	return array(
		'label'   => __( 'Nearly invisible', 'thinkfurther' ),
		'summary' => __( 'AI assistants effectively don\'t mention you yet — which means the biggest, cheapest wins are still on the table. Every gain here is greenfield.', 'thinkfurther' ),
	);
}

/* =================================================================
 * Sample data (clearly labelled) — used only when no keys are configured
 * ================================================================= */

/**
 * Deterministic, plausible sample results derived from the brand name so the
 * dashboard is demonstrable without any API keys. Always flagged sample=true.
 *
 * @param array $query brand/website/competitors.
 * @return array
 */
function tf_vis_sample_sources( $query ) {
	$seed  = abs( crc32( strtolower( $query['brand'] ) ) );
	$brand = $query['brand'];

	$sentiments = array( 'positive', 'neutral', 'positive', 'neutral', 'negative' );
	$pools      = array(
		array( 'wikipedia.org', 'g2.com', 'reddit.com', 'trustpilot.com' ),
		array( 'linkedin.com', 'crunchbase.com', 'capterra.com', 'forbes.com' ),
		array( 'techcrunch.com', 'producthunt.com', 'gartner.com', 'medium.com' ),
	);

	$labels = array();
	foreach ( tf_vis_sources() as $id => $s ) {
		$labels[ $id ] = $s['label'];
	}
	if ( empty( $labels ) ) {
		$labels = array(
			'chatgpt'    => 'ChatGPT (OpenAI)',
			'claude'     => 'Claude (Anthropic)',
			'gemini'     => 'Google Gemini',
			'perplexity' => 'Perplexity',
		);
	}

	$out = array();
	$i   = 0;
	foreach ( $labels as $id => $label ) {
		$mentioned = ( ( $seed >> $i ) & 1 ) || 0 === $i; // ensure at least one mention
		$cited     = $mentioned && ( ( $seed >> ( $i + 2 ) ) & 1 );

		$competitors = array();
		foreach ( $query['competitors'] as $ci => $name ) {
			$competitors[ $name ] = (bool) ( ( $seed >> ( $i + $ci + 1 ) ) & 1 );
		}

		$sources = array();
		if ( $mentioned ) {
			$pool    = $pools[ $i % count( $pools ) ];
			$sources = array_slice( $pool, 0, 2 + ( ( $seed >> $i ) % 2 ) );
		}

		$out[] = tf_vis_normalize(
			array(
				'id'          => $id,
				'label'       => $label,
				'available'   => true,
				'mentioned'   => $mentioned,
				'cited'       => $cited,
				'sentiment'   => $mentioned ? $sentiments[ ( $seed + $i ) % count( $sentiments ) ] : 'unknown',
				'sources'     => $sources,
				'competitors' => $competitors,
				'note'        => $mentioned ? sprintf(
					/* translators: %s: brand name. */
					__( 'Sample: %s described as an option in its category.', 'thinkfurther' ),
					$brand
				) : '',
			)
		);
		++$i;
	}
	return $out;
}

/* =================================================================
 * Usage / cost logging + admin review screen
 * ================================================================= */

/**
 * Append a compact record of each check to a rolling option, and mirror it to
 * the PHP error log so cost can be monitored either way.
 *
 * @param string $email  Visitor email.
 * @param array  $report Aggregated report.
 * @param bool   $sample Whether sample data was used.
 */
function tf_vis_log_check( $email, $report, $sample ) {
	$entry = array(
		'time'    => gmdate( 'c' ),
		'brand'   => isset( $report['brand'] ) ? $report['brand'] : '',
		'website' => isset( $report['website'] ) ? $report['website'] : '',
		'email'   => $email,
		'score'   => isset( $report['score'] ) ? (int) $report['score'] : 0,
		'band'    => isset( $report['band']['label'] ) ? $report['band']['label'] : '',
		'sample'  => (bool) $sample,
		'sources' => isset( $report['assistants'] ) ? count( $report['assistants'] ) : 0,
		'ip'      => tf_vis_visitor_id(),
	);

	$log   = get_option( 'tf_vis_log', array() );
	$log   = is_array( $log ) ? $log : array();
	$log[] = $entry;
	// Keep the most recent 1,000 entries.
	if ( count( $log ) > 1000 ) {
		$log = array_slice( $log, -1000 );
	}
	update_option( 'tf_vis_log', $log, false );

	error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		sprintf(
			'[AI Visibility Checker] brand="%s" score=%d email=%s sample=%s',
			$entry['brand'],
			$entry['score'],
			$entry['email'],
			$entry['sample'] ? 'yes' : 'no'
		)
	);
}

/**
 * Register the admin review screen under Tools.
 */
function tf_vis_admin_menu() {
	add_management_page(
		__( 'AI Visibility Log', 'thinkfurther' ),
		__( 'AI Visibility Log', 'thinkfurther' ),
		'manage_options',
		'tf-vis-log',
		'tf_vis_render_log_page'
	);
}
add_action( 'admin_menu', 'tf_vis_admin_menu' );

/**
 * Render the usage/cost log for review.
 */
function tf_vis_render_log_page() {
	$log = get_option( 'tf_vis_log', array() );
	$log = is_array( $log ) ? array_reverse( $log ) : array();

	$live  = 0;
	$today = 0;
	$today_key = gmdate( 'Y-m-d' );
	foreach ( $log as $row ) {
		if ( empty( $row['sample'] ) ) {
			++$live;
		}
		if ( isset( $row['time'] ) && 0 === strpos( $row['time'], $today_key ) ) {
			++$today;
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'AI Visibility Checker — Usage Log', 'thinkfurther' ); ?></h1>
		<p style="max-width:44em">
			<?php
			printf(
				/* translators: 1: total checks, 2: live (paid) checks, 3: checks today. */
				esc_html__( 'Every check is recorded here so you can monitor usage and API cost. Total: %1$d · Live API checks: %2$d · Today: %3$d. Live checks are the ones that cost money; “sample” rows ran without API keys configured.', 'thinkfurther' ),
				count( $log ),
				(int) $live,
				(int) $today
			);
			?>
		</p>

		<?php if ( empty( $log ) ) : ?>
			<p><?php esc_html_e( 'No checks recorded yet.', 'thinkfurther' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time (UTC)', 'thinkfurther' ); ?></th>
						<th><?php esc_html_e( 'Brand', 'thinkfurther' ); ?></th>
						<th><?php esc_html_e( 'Email', 'thinkfurther' ); ?></th>
						<th><?php esc_html_e( 'Score', 'thinkfurther' ); ?></th>
						<th><?php esc_html_e( 'Band', 'thinkfurther' ); ?></th>
						<th><?php esc_html_e( 'Type', 'thinkfurther' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $log as $row ) : ?>
						<tr>
							<td><?php echo esc_html( isset( $row['time'] ) ? $row['time'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $row['brand'] ) ? $row['brand'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $row['email'] ) ? $row['email'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $row['score'] ) ? $row['score'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $row['band'] ) ? $row['band'] : '' ); ?></td>
							<td><?php echo empty( $row['sample'] ) ? esc_html__( 'Live', 'thinkfurther' ) : esc_html__( 'Sample', 'thinkfurther' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
