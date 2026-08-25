/**
 * Alugence — Brand AI Visibility Checker (front-end).
 * =================================================
 *
 * Renders the input form, a loading state, and the results dashboard, then
 * calls the theme's REST endpoint (see inc/ai-visibility-checker.php) which
 * runs the real LLM/search API calls — or clearly-labelled sample data — and
 * returns the dashboard payload. No third-party libraries.
 *
 * Settings arrive from WordPress via window.tfAvcSettings (see
 * tf_enqueue_assets): restUrl, nonce, ctaUrl, dailyLimit, maxCompetitors.
 */
(function () {
	'use strict';

	var mount = document.querySelector('[data-avc]');
	if (!mount) {
		return;
	}

	var S = window.tfAvcSettings || {};
	var REST_URL = S.restUrl || '/wp-json/alugence/v1/visibility-check';
	var NONCE = S.nonce || '';
	var CTA_URL = S.ctaUrl || '/contact/';
	var DAILY_LIMIT = S.dailyLimit || 3;
	var MAX_COMP = S.maxCompetitors || 5;

	/* ---- tiny DOM helper ------------------------------------------------ */
	function el(tag, attrs, kids) {
		var n = document.createElement(tag);
		if (attrs) {
			Object.keys(attrs).forEach(function (k) {
				if (k === 'class') { n.className = attrs[k]; }
				else if (k === 'html') { n.innerHTML = attrs[k]; }
				else if (k === 'text') { n.textContent = attrs[k]; }
				else if (k.indexOf('on') === 0 && typeof attrs[k] === 'function') { n.addEventListener(k.slice(2), attrs[k]); }
				else if (attrs[k] !== null && attrs[k] !== undefined) { n.setAttribute(k, attrs[k]); }
			});
		}
		(kids || []).forEach(function (c) {
			if (c === null || c === undefined) { return; }
			n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
		});
		return n;
	}
	function clear() { while (mount.firstChild) { mount.removeChild(mount.firstChild); } }
	function esc(s) { return String(s == null ? '' : s); }

	/* ==================================================================== */
	/* 1. INPUT FORM                                                        */
	/* ==================================================================== */
	function renderForm(prefill, errorMsg) {
		clear();
		prefill = prefill || {};

		var email = el('input', { type: 'email', id: 'avc-email', name: 'email', required: 'required', autocomplete: 'email', placeholder: 'you@company.com', value: prefill.email || '' });
		var brand = el('input', { type: 'text', id: 'avc-brand', name: 'brand', required: 'required', placeholder: 'e.g. Alugence', value: prefill.brand || '' });
		var url = el('input', { type: 'url', id: 'avc-url', name: 'url', placeholder: 'https://yourbrand.com', value: prefill.url || '' });
		var comps = el('input', { type: 'text', id: 'avc-competitors', name: 'competitors', placeholder: 'e.g. Competitor A, Competitor B', value: prefill.competitors || '' });
		var honey = el('input', { type: 'text', name: 'company_url', tabindex: '-1', autocomplete: 'off' });

		var errorBox = el('div', { class: 'avc-error', role: 'alert', 'aria-live': 'polite' });
		if (errorMsg) { errorBox.appendChild(el('p', { text: errorMsg })); }

		function field(labelText, input, hint) {
			return el('div', { class: 'avc-field' }, [
				el('label', { for: input.id, text: labelText }),
				input,
				hint ? el('span', { class: 'avc-hint', text: hint }) : null
			]);
		}

		var submit = el('button', { type: 'submit', class: 'btn btn--solid btn--lg' }, [
			'Check my AI visibility ', el('span', { 'aria-hidden': 'true', text: '→' })
		]);

		var form = el('form', { class: 'avc-form', novalidate: 'novalidate', onsubmit: function (e) {
			e.preventDefault();
			run({
				email: email.value.trim(),
				brand: brand.value.trim(),
				url: url.value.trim(),
				competitors: comps.value.trim(),
				company_url: honey.value
			}, errorBox);
		} }, [
			errorBox,
			el('div', { class: 'avc-form__grid' }, [
				field('Your brand name', brand),
				field('Work email', email, 'Required — we send nothing without asking, and it keeps this free tool sustainable.'),
				field('Website (optional)', url),
				field('Competitors (optional)', comps, 'Up to ' + MAX_COMP + ', comma-separated. Powers your share-of-voice score.')
			]),
			// honeypot (visually hidden via .avc-hp)
			el('div', { class: 'avc-hp', 'aria-hidden': 'true' }, [ el('label', { for: honey.id || '', text: 'Company URL' }), honey ]),
			el('div', { class: 'avc-form__actions' }, [
				submit,
				el('p', { class: 'avc-form__note', text: 'Limited to ' + DAILY_LIMIT + ' checks per day. Runs live against AI assistants and AI search.' })
			])
		]);

		mount.appendChild(form);
		brand.focus();
	}

	/* ==================================================================== */
	/* 2. LOADING STATE                                                     */
	/* ==================================================================== */
	function renderLoading(brand) {
		clear();
		var steps = [
			'Asking ChatGPT, Claude, Perplexity and Gemini about ' + brand + '…',
			'Counting mentions and citations…',
			'Measuring share of voice vs competitors…',
			'Reading sentiment and sources…',
			'Scoring AI visibility…'
		];
		var line = el('p', { class: 'avc-loading__step', 'aria-live': 'polite', text: steps[0] });
		var i = 0;
		var timer = setInterval(function () {
			i = (i + 1) % steps.length;
			line.textContent = steps[i];
		}, 1700);
		mount._timer = timer;

		mount.appendChild(el('div', { class: 'avc-loading' }, [
			el('div', { class: 'avc-loading__ring', 'aria-hidden': 'true' }),
			el('p', { class: 'avc-loading__title', text: 'Analysing "' + brand + '" across AI' }),
			line
		]));
	}

	/* ==================================================================== */
	/* 3. RUN THE CHECK                                                      */
	/* ==================================================================== */
	function run(payload, errorBox) {
		// client-side validation
		if (!payload.brand) { renderForm(payload, 'Please enter a brand name.'); return; }
		if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(payload.email)) { renderForm(payload, 'Please enter a valid email address to run the check.'); return; }

		var body = {
			email: payload.email,
			brand: payload.brand,
			url: payload.url,
			company_url: payload.company_url || '',
			competitors: payload.competitors ? payload.competitors.split(/[,\n]+/).map(function (s) { return s.trim(); }).filter(Boolean).slice(0, MAX_COMP) : []
		};

		renderLoading(payload.brand);

		fetch(REST_URL, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE },
			body: JSON.stringify(body)
		}).then(function (res) {
			return res.json().then(function (data) { return { ok: res.ok, status: res.status, data: data }; });
		}).then(function (r) {
			if (mount._timer) { clearInterval(mount._timer); }
			if (!r.ok) {
				var msg = (r.data && r.data.message) ? r.data.message : 'Something went wrong running the check. Please try again.';
				renderForm(payload, msg);
				return;
			}
			renderResults(r.data, payload);
		}).catch(function () {
			if (mount._timer) { clearInterval(mount._timer); }
			renderForm(payload, 'We couldn\'t reach the analysis service. Please try again in a moment.');
		});
	}

	/* ==================================================================== */
	/* 4. RESULTS DASHBOARD                                                  */
	/* ==================================================================== */
	function renderResults(d, prefill) {
		clear();
		var m = d.metrics || {};
		var dash = el('div', { class: 'avc-dash' });

		if (d.is_sample) {
			dash.appendChild(el('div', { class: 'avc-sample-banner', role: 'note' }, [
				el('strong', { text: d.all_sample ? 'Sample data. ' : 'Partly sample data. ' }),
				document.createTextNode(d.all_sample
					? 'Live AI API keys aren\'t configured yet, so these figures are illustrative — the real analysis runs automatically once keys are added.'
					: 'Some AI sources aren\'t connected yet and are shown as samples (flagged below).')
			]));
		}

		/* --- score header --- */
		dash.appendChild(el('div', { class: 'avc-scorecard' }, [
			scoreRing(d.score),
			el('div', { class: 'avc-scorecard__body' }, [
				el('p', { class: 'avc-eyebrow', text: 'AI Visibility Score' }),
				el('h2', { class: 'avc-scorecard__brand', text: d.brand }),
				el('p', { class: 'avc-scorecard__band' }, [
					el('span', { class: 'avc-badge avc-badge--' + slug(d.score_band.label), text: d.score_band.label })
				]),
				el('p', { class: 'avc-scorecard__blurb', text: d.score_band.blurb })
			])
		]));

		/* --- metric cards --- */
		var grid = el('div', { class: 'avc-metrics' });
		grid.appendChild(metricCard(m.citations.label, String(m.citations.value), m.citations.sub, m.citations.explainer));
		grid.appendChild(metricCard(m.mentions.label, m.mentions.value + ' / ' + m.mentions.of, m.mentions.sub, m.mentions.explainer));
		grid.appendChild(sovCard(m.share_of_voice, d.brand));
		grid.appendChild(sentimentCard(m.sentiment));
		dash.appendChild(grid);

		/* --- sources cited --- */
		dash.appendChild(sourcesCard(d.sources_cited));

		/* --- per-source breakdown --- */
		dash.appendChild(perSourceCard(d.per_source));

		/* --- lead capture CTA --- */
		dash.appendChild(el('div', { class: 'avc-cta theme-dark' }, [
			el('div', { class: 'avc-cta__grid', 'aria-hidden': 'true' }),
			el('div', { class: 'avc-cta__body' }, [
				el('p', { class: 'avc-eyebrow', text: 'Next step' }),
				el('h3', { class: 'avc-cta__title', text: d.cta.title }),
				el('p', { class: 'avc-cta__text', text: 'This is a snapshot. The full audit runs dozens of prompts across every major AI engine, tracks it over time, and comes with a prioritised plan to get you cited.' }),
				el('div', { class: 'avc-cta__actions' }, [
					el('a', { class: 'btn btn--solid btn--lg', href: d.cta.url || CTA_URL }, [ d.cta.label + ' ', el('span', { 'aria-hidden': 'true', text: '→' }) ]),
					el('button', { type: 'button', class: 'btn btn--ghost', onclick: function () { renderForm(prefill); window.scrollTo({ top: mount.getBoundingClientRect().top + window.pageYOffset - 80, behavior: 'smooth' }); } }, [ 'Run another check' ])
				])
			])
		]));

		mount.appendChild(dash);
		requestAnimationFrame(function () { animateRing(d.score); });
	}

	/* --- score ring (SVG) --- */
	function scoreRing(score) {
		var R = 52, C = 2 * Math.PI * R;
		var wrap = el('div', { class: 'avc-ring' });
		wrap.innerHTML =
			'<svg viewBox="0 0 120 120" role="img" aria-label="AI Visibility Score ' + score + ' out of 100">' +
			'<circle class="avc-ring__track" cx="60" cy="60" r="' + R + '"></circle>' +
			'<circle class="avc-ring__fill" cx="60" cy="60" r="' + R + '" stroke-dasharray="' + C + '" stroke-dashoffset="' + C + '" transform="rotate(-90 60 60)"></circle>' +
			'</svg>' +
			'<div class="avc-ring__num"><span class="avc-ring__score">0</span><span class="avc-ring__of">/100</span></div>';
		return wrap;
	}
	function animateRing(score) {
		var fill = mount.querySelector('.avc-ring__fill');
		var num = mount.querySelector('.avc-ring__score');
		if (!fill || !num) { return; }
		var R = 52, C = 2 * Math.PI * R;
		fill.style.strokeDashoffset = C - (C * Math.max(0, Math.min(100, score)) / 100);
		var start = null, dur = 900;
		function step(ts) {
			if (!start) { start = ts; }
			var p = Math.min(1, (ts - start) / dur);
			num.textContent = Math.round(p * score);
			if (p < 1) { requestAnimationFrame(step); }
		}
		requestAnimationFrame(step);
	}

	/* --- metric cards --- */
	function metricCard(label, value, sub, explainer) {
		return el('article', { class: 'avc-card' }, [
			el('p', { class: 'avc-card__label', text: label }),
			el('p', { class: 'avc-card__value', text: value }),
			sub ? el('p', { class: 'avc-card__sub', text: sub }) : null,
			el('p', { class: 'avc-card__explain', text: explainer })
		]);
	}
	function sovCard(sov, brand) {
		var bars = el('div', { class: 'avc-sov' });
		bars.appendChild(sovRow(brand, sov.value, true));
		(sov.competitors || []).forEach(function (c) {
			// share is computed server-side against the same total as the brand
			bars.appendChild(sovRow(c.name, c.share || 0, false));
		});
		return el('article', { class: 'avc-card avc-card--wide' }, [
			el('p', { class: 'avc-card__label', text: sov.label }),
			el('p', { class: 'avc-card__value', text: sov.value + '%' }),
			el('p', { class: 'avc-card__sub', text: sov.sub }),
			(sov.competitors && sov.competitors.length) ? bars : el('p', { class: 'avc-card__sub', text: 'Add competitors to see how your share compares.' }),
			el('p', { class: 'avc-card__explain', text: sov.explainer })
		]);
	}
	function sovRow(name, pct, isBrand) {
		return el('div', { class: 'avc-sov__row' + (isBrand ? ' is-brand' : '') }, [
			el('span', { class: 'avc-sov__name', text: name }),
			el('span', { class: 'avc-sov__track' }, [ el('span', { class: 'avc-sov__bar', style: 'width:' + Math.max(2, Math.min(100, pct)) + '%' }) ]),
			el('span', { class: 'avc-sov__pct', text: pct + '%' })
		]);
	}
	function sentimentCard(sent) {
		return el('article', { class: 'avc-card' }, [
			el('p', { class: 'avc-card__label', text: sent.label }),
			el('p', { class: 'avc-card__value' }, [ el('span', { class: 'avc-badge avc-badge--' + esc(sent.value), text: cap(sent.value) }) ]),
			el('p', { class: 'avc-card__sub', text: '+' + sent.positive + ' positive · ' + sent.neutral + ' neutral · ' + sent.negative + ' negative' }),
			el('p', { class: 'avc-card__explain', text: sent.explainer })
		]);
	}
	function sourcesCard(sources) {
		var body;
		if (sources && sources.length) {
			body = el('ul', { class: 'avc-sources' }, sources.map(function (s) {
				return el('li', { class: 'avc-sources__item' }, [
					el('a', { href: s.url, target: '_blank', rel: 'noopener nofollow', class: 'avc-sources__host', text: s.host }),
					s.count > 1 ? el('span', { class: 'avc-sources__count', text: '×' + s.count }) : null
				]);
			}));
		} else {
			body = el('p', { class: 'avc-card__sub', text: 'No external sources were cited for this brand — a sign AI has little to point people to about you yet.' });
		}
		return el('article', { class: 'avc-card avc-card--full' }, [
			el('p', { class: 'avc-card__label', text: 'Sources AI pulled from' }),
			el('p', { class: 'avc-card__explain', text: 'The web pages AI systems linked to when describing your brand. These are the pages shaping how AI talks about you — the ones worth owning or influencing.' }),
			body
		]);
	}
	function perSourceCard(rows) {
		var table = el('div', { class: 'avc-table-wrap' }, [
			el('table', { class: 'avc-table' }, [
				el('thead', {}, [ el('tr', {}, [
					el('th', { text: 'AI source' }), el('th', { text: 'Mentioned' }), el('th', { text: 'Cited' }), el('th', { text: 'Sentiment' })
				]) ]),
				el('tbody', {}, (rows || []).map(function (r) {
					return el('tr', {}, [
						el('td', {}, [ document.createTextNode(r.label + ' '), r.sample ? el('span', { class: 'avc-tag', text: 'sample' }) : (r.error ? el('span', { class: 'avc-tag avc-tag--err', title: r.error, text: 'error' }) : null) ]),
						el('td', {}, [ tick(r.mentioned) ]),
						el('td', {}, [ tick(r.cited) ]),
						el('td', { text: r.mentioned ? cap(r.sentiment) : '—' })
					]);
				}))
			])
		]);
		return el('article', { class: 'avc-card avc-card--full' }, [
			el('p', { class: 'avc-card__label', text: 'By AI source' }),
			table
		]);
	}

	/* --- small utils --- */
	function tick(on) { return el('span', { class: 'avc-tick avc-tick--' + (on ? 'yes' : 'no'), 'aria-label': on ? 'yes' : 'no', text: on ? '✓' : '–' }); }
	function cap(s) { s = esc(s); return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
	function slug(s) { return esc(s).toLowerCase().replace(/[^a-z0-9]+/g, '-'); }

	/* boot */
	renderForm();
})();
