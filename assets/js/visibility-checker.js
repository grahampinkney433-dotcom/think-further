/**
 * Alugence — Brand AI Visibility Checker (front end)
 * ==================================================
 *
 * Pure presentation layer. It collects the brand/website/competitors/email,
 * POSTs them to the backend REST route (see inc/visibility-checker.php), and
 * renders whatever report comes back. All measurement, cost control, API calls
 * and logging happen server-side — this file never talks to an LLM directly.
 *
 * Three states, one mount point [data-visibility]:
 *   1. form     — input form (brand, website, competitors, email, consent)
 *   2. loading  — animated "querying each assistant" progress
 *   3. results  — score dashboard + breakdown cards + lead-capture CTA
 */
(function () {
	'use strict';

	var mount = document.querySelector('[data-visibility]');
	if (!mount) {
		return;
	}

	var settings = window.tfVisibilitySettings || {};
	var REST_URL = settings.restUrl || '/wp-json/tf/v1/visibility-check';
	var NONCE = settings.nonce || '';
	var DAILY_LIMIT = settings.dailyLimit || 3;
	var CTA_URL = settings.ctaUrl || '/contact/';
	var CONTACT_EMAIL = settings.contactEmail || '';

	/* First-party session id so the backend can rate-limit per browser as well
	   as per IP. Not a tracking cookie — just a random per-session token. */
	ensureSessionCookie();

	/* ---------- tiny DOM helpers ---------- */

	function el(tag, className, html) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (html !== undefined) { node.innerHTML = html; }
		return node;
	}

	function esc(text) {
		return String(text == null ? '' : text).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function scrollToTop() {
		var y = mount.getBoundingClientRect().top + window.pageYOffset - 96;
		window.scrollTo({ top: y, behavior: 'smooth' });
	}

	function ensureSessionCookie() {
		if (/(?:^|;\s*)tf_vis_sid=/.test(document.cookie)) { return; }
		var token = Math.random().toString(36).slice(2) + Date.now().toString(36);
		document.cookie = 'tf_vis_sid=' + token + '; path=/; max-age=86400; SameSite=Lax';
	}

	/* ================================================================
	 * 1. FORM
	 * ================================================================ */

	function renderForm(prefill, errorMsg) {
		prefill = prefill || {};
		mount.innerHTML = '';

		var card = el('div', 'vis-card');
		card.appendChild(el('p', 'vis-kicker', 'AI visibility check &middot; ~30 seconds'));
		card.appendChild(el('h2', 'vis-card__title', 'How visible is your brand <em>to AI?</em>'));
		card.appendChild(el('p', 'vis-card__desc', 'Enter your brand and we’ll check how often AI assistants and AI search name you, cite you, and how you compare with your competitors — with a single AI Visibility Score out of 100.'));

		if (errorMsg) {
			card.appendChild(el('div', 'form-notice form-notice--error', '<p>' + esc(errorMsg) + '</p>'));
		}

		var form = el('form', 'vis-form');
		form.noValidate = false;

		form.innerHTML =
			'<div class="vis-field">' +
				'<label for="vis-brand">Brand name <span class="vis-req" aria-hidden="true">*</span></label>' +
				'<input id="vis-brand" name="brand" type="text" required autocomplete="organization" placeholder="e.g. Alugence" value="' + esc(prefill.brand || '') + '">' +
			'</div>' +
			'<div class="vis-field">' +
				'<label for="vis-website">Website <span class="vis-optional">(optional)</span></label>' +
				'<input id="vis-website" name="website" type="url" inputmode="url" autocomplete="url" placeholder="https://yourbrand.com" value="' + esc(prefill.website || '') + '">' +
			'</div>' +
			'<div class="vis-field">' +
				'<label>Competitors <span class="vis-optional">(optional, up to 4)</span></label>' +
				'<div class="vis-competitors">' +
					competitorInput(0, prefill) +
					competitorInput(1, prefill) +
					competitorInput(2, prefill) +
					competitorInput(3, prefill) +
				'</div>' +
			'</div>' +
			'<div class="vis-field">' +
				'<label for="vis-email">Work email <span class="vis-req" aria-hidden="true">*</span></label>' +
				'<input id="vis-email" name="email" type="email" required autocomplete="email" placeholder="you@company.com" value="' + esc(prefill.email || '') + '">' +
				'<p class="vis-hint">We ask for an email to keep this free tool available — it stops bots and abuse. No newsletter, no spam.</p>' +
			'</div>' +
			/* Honeypot — hidden from humans, catches bots. */
			'<div class="vis-hp" aria-hidden="true"><label for="vis-company-website">Company website</label><input id="vis-company-website" name="company_website" type="text" tabindex="-1" autocomplete="off"></div>' +
			'<label class="vis-consent"><input type="checkbox" name="consent" required> <span>I’m happy for Alugence to email me about my results. ' + (DAILY_LIMIT ? 'Limited to ' + DAILY_LIMIT + ' free checks per day.' : '') + '</span></label>' +
			'<button type="submit" class="btn btn--volt btn--lg">Check my AI visibility <span aria-hidden="true">→</span></button>' +
			'<p class="vis-form__status" role="status" aria-live="polite"></p>';

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			submitCheck(form);
		});

		card.appendChild(form);
		mount.appendChild(card);
	}

	function competitorInput(i, prefill) {
		var comps = prefill.competitors || [];
		var val = comps[i] ? esc(comps[i]) : '';
		return '<input class="vis-competitor" name="competitor" type="text" autocomplete="off" placeholder="Competitor ' + (i + 1) + '" value="' + val + '">';
	}

	function readForm(form) {
		var competitors = [];
		form.querySelectorAll('.vis-competitor').forEach(function (input) {
			var v = input.value.trim();
			if (v) { competitors.push(v); }
		});
		return {
			brand: form.brand.value.trim(),
			website: form.website.value.trim(),
			email: form.email.value.trim(),
			consent: form.consent.checked,
			company_website: form.company_website.value, // honeypot
			competitors: competitors
		};
	}

	/* ================================================================
	 * 2. SUBMIT + LOADING
	 * ================================================================ */

	function submitCheck(form) {
		var data = readForm(form);

		if (!data.brand) { return formError(form, 'Please enter your brand name.'); }
		if (!data.email) { return formError(form, 'Please enter your email address.'); }
		if (!data.consent) { return formError(form, 'Please tick the consent box to continue.'); }

		renderLoading(data);

		fetch(REST_URL, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE },
			body: JSON.stringify(data)
		}).then(function (res) {
			return res.json().then(function (body) {
				return { status: res.status, body: body };
			});
		}).then(function (payload) {
			var body = payload.body || {};
			if (payload.status >= 200 && payload.status < 300 && body.ok) {
				renderResults(body, data);
			} else {
				renderForm(data, body.message || 'Something went wrong running your check. Please try again' + (CONTACT_EMAIL ? ', or email ' + CONTACT_EMAIL + '.' : '.'));
				scrollToTop();
			}
		}).catch(function () {
			renderForm(data, 'We couldn’t reach the checker. Please try again in a moment' + (CONTACT_EMAIL ? ', or email ' + CONTACT_EMAIL + '.' : '.'));
			scrollToTop();
		});
	}

	function formError(form, msg) {
		var status = form.querySelector('.vis-form__status');
		if (status) {
			status.textContent = msg;
			status.className = 'vis-form__status vis-form__status--error';
		}
	}

	function renderLoading(data) {
		mount.innerHTML = '';
		var card = el('div', 'vis-card vis-card--loading');
		card.appendChild(el('p', 'vis-kicker', 'Running your check'));
		card.appendChild(el('h2', 'vis-card__title', 'Asking the AI assistants about <em>' + esc(data.brand) + '</em>…'));
		card.appendChild(el('p', 'vis-card__desc', 'We’re querying each AI source and analysing how they describe your brand. This usually takes 20–40 seconds.'));

		var steps = ['ChatGPT (OpenAI)', 'Claude (Anthropic)', 'Google Gemini', 'Perplexity', 'Scoring & share of voice'];
		var list = el('ul', 'vis-loading-steps');
		steps.forEach(function (label) {
			list.appendChild(el('li', 'vis-loading-step', '<span class="vis-spinner" aria-hidden="true"></span>' + esc(label)));
		});
		card.appendChild(list);
		mount.appendChild(card);

		// Progressively "complete" steps for perceived progress (purely cosmetic).
		var items = list.querySelectorAll('.vis-loading-step');
		var idx = 0;
		var timer = setInterval(function () {
			if (idx >= items.length) { clearInterval(timer); return; }
			items[idx].classList.add('is-done');
			idx++;
		}, 1400);
	}

	/* ================================================================
	 * 3. RESULTS DASHBOARD
	 * ================================================================ */

	function renderResults(report, data) {
		mount.innerHTML = '';
		var card = el('div', 'vis-card vis-card--results');

		if (report.sample) {
			card.appendChild(el('div', 'vis-sample-banner',
				'<strong>Sample data.</strong> Live AI APIs aren’t connected yet, so these figures are illustrative — the full report uses real queries across ChatGPT, Claude, Gemini and Perplexity.'));
		}

		card.appendChild(el('p', 'vis-kicker', 'AI Visibility Report'));

		// --- Score hero ---
		var head = el('div', 'vis-score');
		var ring = el('div', 'vis-score__ring');
		ring.innerHTML =
			'<svg viewBox="0 0 120 120" width="120" height="120" aria-hidden="true">' +
			'<circle class="vis-score__track" cx="60" cy="60" r="52" fill="none" stroke-width="10"></circle>' +
			'<circle class="vis-score__arc" cx="60" cy="60" r="52" fill="none" stroke-width="10" stroke-linecap="round" transform="rotate(-90 60 60)"></circle>' +
			'</svg>' +
			'<span class="vis-score__num"><span data-score>0</span><span class="vis-score__of">/100</span></span>';
		head.appendChild(ring);

		var headText = el('div', 'vis-score__text');
		headText.appendChild(el('p', 'vis-band-chip', esc(report.band && report.band.label ? report.band.label : '')));
		headText.appendChild(el('h2', 'vis-score__head', 'AI Visibility Score for <em>' + esc(report.brand) + '</em>'));
		headText.appendChild(el('p', 'vis-score__summary', esc(report.band && report.band.summary ? report.band.summary : '')));
		head.appendChild(headText);
		card.appendChild(head);

		// --- Breakdown cards ---
		card.appendChild(el('h3', 'vis-sub', 'The breakdown'));
		var grid = el('div', 'vis-grid');
		grid.appendChild(metricCard('Citations', report.metrics.citations.value + ' / ' + report.metrics.citations.outOf, report.metrics.citations.rate + '% of assistants linked a source', report.metrics.citations.explain));
		grid.appendChild(metricCard('Mentions', report.metrics.mentions.value + ' / ' + report.metrics.mentions.outOf, report.metrics.mentions.rate + '% of assistants named you', report.metrics.mentions.explain));
		grid.appendChild(sentimentCard(report.metrics.sentiment));
		grid.appendChild(shareOfVoiceCard(report.metrics.shareOfVoice, report.brand));
		card.appendChild(grid);

		// --- Sources ---
		card.appendChild(sourcesBlock(report.metrics.sources));

		// --- Per-assistant breakdown ---
		card.appendChild(assistantsBlock(report.assistants));

		// --- Lead capture CTA ---
		card.appendChild(ctaBlock(report));

		// --- Run again ---
		var again = el('button', 'vis-link-btn', 'Check another brand');
		again.type = 'button';
		again.addEventListener('click', function () {
			renderForm({ email: data.email });
			scrollToTop();
		});
		card.appendChild(again);

		if (typeof report.remaining === 'number' && report.remaining >= 0) {
			card.appendChild(el('p', 'vis-remaining', esc(report.remaining) + ' of ' + esc(report.limit || DAILY_LIMIT) + ' free checks left today.'));
		}

		mount.appendChild(card);
		scrollToTop();
		animateScore(card.querySelector('[data-score]'), card.querySelector('.vis-score__arc'), report.score);
		animateBars(card);
	}

	function metricCard(title, big, sub, explain) {
		var c = el('div', 'vis-metric');
		c.appendChild(el('p', 'vis-metric__label', esc(title)));
		c.appendChild(el('p', 'vis-metric__value', esc(big)));
		c.appendChild(el('p', 'vis-metric__sub', esc(sub)));
		c.appendChild(el('p', 'vis-metric__explain', esc(explain)));
		return c;
	}

	function sentimentCard(s) {
		var c = el('div', 'vis-metric');
		c.appendChild(el('p', 'vis-metric__label', 'Sentiment'));
		c.appendChild(el('p', 'vis-metric__value vis-sent--' + esc(s.label), esc(cap(s.label))));
		var bar = el('div', 'vis-sentbar');
		bar.innerHTML =
			'<span class="vis-sentbar__seg vis-sentbar__seg--pos" style="width:' + s.positive + '%" title="Positive ' + s.positive + '%"></span>' +
			'<span class="vis-sentbar__seg vis-sentbar__seg--neu" style="width:' + s.neutral + '%" title="Neutral ' + s.neutral + '%"></span>' +
			'<span class="vis-sentbar__seg vis-sentbar__seg--neg" style="width:' + s.negative + '%" title="Negative ' + s.negative + '%"></span>';
		c.appendChild(bar);
		c.appendChild(el('p', 'vis-metric__sub', s.positive + '% positive &middot; ' + s.neutral + '% neutral &middot; ' + s.negative + '% negative'));
		c.appendChild(el('p', 'vis-metric__explain', esc(s.explain)));
		return c;
	}

	function shareOfVoiceCard(sov, brand) {
		var c = el('div', 'vis-metric vis-metric--wide');
		c.appendChild(el('p', 'vis-metric__label', 'Share of voice'));
		c.appendChild(el('p', 'vis-metric__value', esc(sov.brand) + '%'));
		c.appendChild(el('p', 'vis-metric__sub', 'Your share of the AI conversation vs named competitors'));

		var rows = el('div', 'vis-sov');
		rows.appendChild(sovRow(brand, sov.brand, true));
		(sov.competitors || []).forEach(function (comp) {
			rows.appendChild(sovRow(comp.name, comp.pct, false));
		});
		c.appendChild(rows);
		c.appendChild(el('p', 'vis-metric__explain', esc(sov.explain)));
		return c;
	}

	function sovRow(name, pct, isBrand) {
		var row = el('div', 'vis-sov__row');
		row.appendChild(el('div', 'vis-sov__head', '<span>' + esc(name) + (isBrand ? ' <span class="vis-sov__you">You</span>' : '') + '</span><span class="vis-sov__pct">' + esc(pct) + '%</span>'));
		var track = el('div', 'vis-sov__track');
		var fill = el('span', 'vis-sov__fill' + (isBrand ? ' vis-sov__fill--brand' : ''));
		fill.setAttribute('data-w', pct);
		fill.style.width = '0%';
		track.appendChild(fill);
		row.appendChild(track);
		return row;
	}

	function sourcesBlock(sources) {
		var wrap = el('div', 'vis-block');
		wrap.appendChild(el('h3', 'vis-sub', 'Where AI gets its information'));
		wrap.appendChild(el('p', 'vis-block__desc', esc(sources.explain)));
		if (!sources.list || !sources.list.length) {
			wrap.appendChild(el('p', 'vis-empty', 'No sources were surfaced for your brand — a strong signal that AI systems have little to draw on yet.'));
			return wrap;
		}
		var chips = el('div', 'vis-sources');
		sources.list.forEach(function (s) {
			chips.appendChild(el('span', 'vis-source-chip', esc(s.domain) + (s.count > 1 ? '<span class="vis-source-chip__count">' + s.count + '</span>' : '')));
		});
		wrap.appendChild(chips);
		return wrap;
	}

	function assistantsBlock(assistants) {
		var wrap = el('div', 'vis-block');
		wrap.appendChild(el('h3', 'vis-sub', 'Assistant by assistant'));
		var table = el('div', 'vis-assistants');
		(assistants || []).forEach(function (a) {
			var row = el('div', 'vis-assistant');
			row.appendChild(el('p', 'vis-assistant__name', esc(a.label)));
			var tags = el('div', 'vis-assistant__tags');
			tags.appendChild(tag(a.mentioned ? 'Named you' : 'Didn’t name you', a.mentioned ? 'yes' : 'no'));
			tags.appendChild(tag(a.cited ? 'Cited a source' : 'No citation', a.cited ? 'yes' : 'no'));
			if (a.mentioned && a.sentiment && a.sentiment !== 'unknown') {
				tags.appendChild(tag(cap(a.sentiment), 'sent-' + a.sentiment));
			}
			row.appendChild(tags);
			if (a.note) {
				row.appendChild(el('p', 'vis-assistant__note', esc(a.note)));
			}
			table.appendChild(row);
		});
		wrap.appendChild(table);
		return wrap;
	}

	function tag(label, kind) {
		return el('span', 'vis-tag vis-tag--' + kind, esc(label));
	}

	function ctaBlock(report) {
		var cta = el('div', 'vis-cta theme-dark');
		cta.appendChild(el('h3', 'vis-cta__title', 'Want the full audit and a plan to improve your AI visibility?'));
		cta.appendChild(el('p', 'vis-cta__desc', 'This is a snapshot. A full audit tracks every buyer question, benchmarks you against competitors over time, and gives you a prioritised plan to get named and cited more often. Book a free 30-minute call and we’ll walk you through it.'));
		var btn = el('a', 'btn btn--volt btn--lg', 'Book a call <span aria-hidden="true">→</span>');
		btn.href = CTA_URL;
		cta.appendChild(btn);
		if (CONTACT_EMAIL) {
			var mail = el('a', 'vis-cta__mail', 'or email ' + esc(CONTACT_EMAIL));
			mail.href = 'mailto:' + CONTACT_EMAIL + '?subject=' + encodeURIComponent('AI visibility audit — ' + (report.brand || ''));
			cta.appendChild(mail);
		}
		return cta;
	}

	/* ---------- animations ---------- */

	function animateScore(numNode, arcNode, target) {
		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var circumference = 2 * Math.PI * 52;
		if (arcNode) {
			arcNode.style.strokeDasharray = circumference;
			arcNode.style.strokeDashoffset = circumference;
		}
		if (reduced) {
			if (numNode) { numNode.textContent = String(target); }
			if (arcNode) { arcNode.style.strokeDashoffset = circumference * (1 - target / 100); }
			return;
		}
		var start = null;
		var DURATION = 1200;
		function tick(ts) {
			if (!start) { start = ts; }
			var p = Math.min((ts - start) / DURATION, 1);
			var eased = 1 - Math.pow(1 - p, 3);
			if (numNode) { numNode.textContent = String(Math.round(eased * target)); }
			if (arcNode) { arcNode.style.strokeDashoffset = circumference * (1 - (eased * target) / 100); }
			if (p < 1) { requestAnimationFrame(tick); }
		}
		requestAnimationFrame(tick);
	}

	function animateBars(scope) {
		var fills = scope.querySelectorAll('.vis-sov__fill');
		requestAnimationFrame(function () {
			requestAnimationFrame(function () {
				fills.forEach(function (f) {
					f.style.width = (f.getAttribute('data-w') || 0) + '%';
				});
			});
		});
	}

	function cap(s) {
		s = String(s || '');
		return s.charAt(0).toUpperCase() + s.slice(1);
	}

	/* ---------- boot ---------- */
	renderForm();
})();
