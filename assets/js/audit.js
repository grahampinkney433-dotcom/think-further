/**
 * Alugence — Business AI Capabilities Audit
 * =========================================
 *
 * Fully self-contained front-end questionnaire. No backend is needed
 * for questions, scoring or recommendations — everything lives in the
 * CONFIG block below and is deliberately easy to edit:
 *
 *   1. QUESTIONS   — edit SECTIONS: add/remove questions, reword copy,
 *                    change answer options and their points.
 *   2. WEIGHTS     — each section has a `weight` used in the overall
 *                    score (they don't need to sum to anything).
 *   3. BANDS       — score ranges and their labels/summaries.
 *   4. RECOMMENDATIONS — per-section advice, picked for the user's
 *                    weakest sections (tier "low" < 50, "mid" 50–74).
 *   5. LEAD CAPTURE — submitLead() is the ONLY integration point.
 *                    See its doc block for how to wire a CRM/email tool.
 *
 * Progress is saved to sessionStorage after every answer, so users can
 * navigate back/forward (or accidentally reload) without losing state.
 */
(function () {
	'use strict';

	var mount = document.querySelector('[data-audit]');
	if (!mount) {
		return;
	}

	/* Settings passed from WordPress (see tf_enqueue_assets). */
	var settings = window.tfAuditSettings || {};
	var CTA_URL = settings.ctaUrl || '/contact/';
	var CONTACT_EMAIL = settings.contactEmail || '';

	/* ================================================================
	 * 1. QUESTIONS
	 * ----------------------------------------------------------------
	 * type "choice": options ordered weakest → strongest; each option's
	 *   `points` is normalised against the highest in the question.
	 * type "scale": renders 1–5 buttons; labels lo/hi caption the ends.
	 * ================================================================ */
	var SECTIONS = [
		{
			id: 'adoption',
			label: 'AI adoption & tools',
			blurb: 'How AI is actually used across the business today.',
			weight: 1,
			questions: [
				{
					id: 'adoption-spread',
					text: 'How widely are AI tools used day-to-day?',
					type: 'choice',
					options: [
						{ label: 'Not at all', points: 0 },
						{ label: 'A few individuals, unofficially', points: 1 },
						{ label: 'Some teams, regularly', points: 2 },
						{ label: 'Most teams, with guidance', points: 3 },
						{ label: 'Everywhere, with clear playbooks', points: 4 },
					],
				},
				{
					id: 'adoption-stack',
					text: 'Which best describes your AI toolkit?',
					type: 'choice',
					options: [
						{ label: 'No AI tools in use', points: 0 },
						{ label: 'Free chat tools, ad hoc', points: 1 },
						{ label: 'Paid assistants for some staff', points: 2 },
						{ label: 'Assistants plus some integrations', points: 3 },
						{ label: 'An integrated stack, incl. custom systems', points: 4 },
					],
				},
				{
					id: 'adoption-embedded',
					text: 'How embedded is AI in your core workflows — the work your business actually runs on?',
					type: 'scale',
					lo: 'Not embedded',
					hi: 'Deeply embedded',
				},
			],
		},
		{
			id: 'data',
			label: 'Data readiness',
			blurb: 'Whether your data could feed AI systems tomorrow.',
			weight: 1,
			questions: [
				{
					id: 'data-location',
					text: 'Where does your key business data live?',
					type: 'choice',
					options: [
						{ label: 'Spreadsheets and inboxes, everywhere', points: 0 },
						{ label: 'Several disconnected systems', points: 1 },
						{ label: 'Core systems, partly integrated', points: 2 },
						{ label: 'Mostly centralised and accessible', points: 3 },
						{ label: 'Well-governed and API-accessible', points: 4 },
					],
				},
				{
					id: 'data-quality',
					text: 'How much would you trust your data to drive an important decision, as it stands today?',
					type: 'scale',
					lo: 'Not at all',
					hi: 'Completely',
				},
			],
		},
		{
			id: 'skills',
			label: 'Team skills & confidence',
			blurb: 'Whether your people can use — and build on — AI.',
			weight: 1,
			questions: [
				{
					id: 'skills-confidence',
					text: 'How confident is the average person in your team with AI tools?',
					type: 'scale',
					lo: 'Avoids them',
					hi: 'Fluent daily use',
				},
				{
					id: 'skills-training',
					text: 'What AI training does your business provide?',
					type: 'choice',
					options: [
						{ label: 'None', points: 0 },
						{ label: 'Self-directed learning only', points: 1 },
						{ label: 'Occasional ad-hoc sessions', points: 2 },
						{ label: 'Structured training for some roles', points: 3 },
						{ label: 'An ongoing programme for everyone', points: 4 },
					],
				},
				{
					id: 'skills-build',
					text: 'What in-house capability do you have to build or configure AI solutions?',
					type: 'choice',
					options: [
						{ label: 'None', points: 0 },
						{ label: 'One or two enthusiasts', points: 1 },
						{ label: 'A small pocket of capability', points: 2 },
						{ label: 'A dedicated person or team', points: 3 },
						{ label: 'A mature team, plus partners', points: 4 },
					],
				},
			],
		},
		{
			id: 'automation',
			label: 'Process automation',
			blurb: 'How much repetitive work still relies on humans.',
			weight: 1,
			questions: [
				{
					id: 'automation-coverage',
					text: 'How automated are your repetitive processes today?',
					type: 'choice',
					options: [
						{ label: 'Everything is manual', points: 0 },
						{ label: 'A few macros or simple zaps', points: 1 },
						{ label: 'Several processes automated', points: 2 },
						{ label: 'Many processes, with measurement', points: 3 },
						{ label: 'Extensive — agentic where it earns it', points: 4 },
					],
				},
				{
					id: 'automation-flow',
					text: 'How freely does information move between your systems without someone re-typing it?',
					type: 'scale',
					lo: 'Constant re-entry',
					hi: 'Flows automatically',
				},
			],
		},
		{
			id: 'strategy',
			label: 'Strategy & leadership',
			blurb: 'Whether AI has an owner, a budget and a direction.',
			weight: 1,
			questions: [
				{
					id: 'strategy-plan',
					text: 'Does AI appear in your business strategy?',
					type: 'choice',
					options: [
						{ label: 'Not discussed', points: 0 },
						{ label: 'Talked about, nothing written', points: 1 },
						{ label: 'A loose plan or list of pilots', points: 2 },
						{ label: 'A written strategy with owners', points: 3 },
						{ label: 'Strategy with budget, owners and measures', points: 4 },
					],
				},
				{
					id: 'strategy-leadership',
					text: 'How bought-in is your leadership team — in budget and attention, not just words?',
					type: 'scale',
					lo: 'Sceptical',
					hi: 'Actively driving it',
				},
				{
					id: 'strategy-governance',
					text: 'Do you have an AI usage policy or governance in place?',
					type: 'choice',
					options: [
						{ label: 'Nothing', points: 0 },
						{ label: 'Informal norms only', points: 1 },
						{ label: 'A basic written policy', points: 2 },
						{ label: 'Policy plus training on it', points: 3 },
						{ label: 'Governance with a review cycle', points: 4 },
					],
				},
			],
		},
		{
			id: 'customer',
			label: 'Customer-facing AI',
			blurb: 'AI in support, marketing and sales — where buyers feel it.',
			weight: 1,
			questions: [
				{
					id: 'customer-experience',
					text: 'Where is AI in your customer support and experience?',
					type: 'choice',
					options: [
						{ label: 'Nowhere', points: 0 },
						{ label: 'Experimenting internally', points: 1 },
						{ label: 'A basic chatbot or drafted replies', points: 2 },
						{ label: 'AI-assisted across support or marketing', points: 3 },
						{ label: 'Measured AI across support, marketing & sales', points: 4 },
					],
				},
				{
					id: 'customer-content',
					text: 'How much of your marketing and sales content production is AI-assisted?',
					type: 'scale',
					lo: 'None of it',
					hi: 'Most of it, with editing',
				},
			],
		},
	];

	/* ================================================================
	 * 2. BANDS — overall score interpretation (checked top-down).
	 * ================================================================ */
	var BANDS = [
		{ min: 80, label: 'Leading', summary: 'You’re ahead of most of your market. The opportunity now is compounding: custom systems, agentic workflows and defending the advantage as it becomes table stakes.' },
		{ min: 60, label: 'Advanced', summary: 'Strong foundations with real momentum. Focused work on your weaker areas below would move you into the leading tier — and widen the gap on competitors.' },
		{ min: 40, label: 'Developing', summary: 'You’ve started, but value is leaking between pockets of adoption. The priorities below are where structured effort pays back fastest.' },
		{ min: 0, label: 'Emerging', summary: 'You’re at the start of the curve — which means the biggest, cheapest wins are still on the table. The steps below are the highest-leverage places to begin.' },
	];

	/* ================================================================
	 * 3. RECOMMENDATIONS — keyed by section id, tiered by that
	 *    section's score: "low" (< 50) and "mid" (50–74). Sections
	 *    scoring 75+ don't generate a recommendation. Shown for the
	 *    weakest sections first, capped at MAX_RECS.
	 * ================================================================ */
	var MAX_RECS = 6;
	var MIN_RECS = 3;

	var RECOMMENDATIONS = {
		adoption: {
			low: 'Start with a structured pilot: pick two teams, give them paid AI assistants and one hour of guided setup, and measure time saved for a month. Adoption follows proof, not mandates.',
			mid: 'Turn scattered usage into standard practice: document the prompts and workflows your best users already rely on, and make them team playbooks with named owners.',
		},
		data: {
			low: 'Before any AI build, run a lightweight data audit: list your five most important data sources, where they live, and who can access them. Consolidating even two of them unlocks most early AI use cases.',
			mid: 'Your data is usable but under-connected. Prioritise API access or exports for the systems your target workflows depend on — integration effort here multiplies every later AI project.',
		},
		skills: {
			low: 'Run a hands-on AI working session for each team using their real work — not generic demos. Confidence is the bottleneck, and it moves fastest when people see their own tasks accelerate.',
			mid: 'Formalise what your strongest users know: a short internal training track and a shared prompt library turn individual skill into organisational capability.',
		},
		automation: {
			low: 'List every task your team does more than five times a week in the same way — that list is your automation backlog. Start with the top two; tools like workflow builders plus AI handle far more of these than most teams expect.',
			mid: 'You’ve automated the obvious; the next gains are cross-system. Target the handoffs where people re-type or copy-paste between tools — that’s where agentic workflows earn their keep.',
		},
		strategy: {
			low: 'Get one page written: where AI could move your P&L, who owns it, and what you’ll try this quarter. A named owner with a small budget beats a perfect strategy that doesn’t exist.',
			mid: 'You have direction — add measurement. Attach baselines and review dates to each AI initiative so leadership sees returns, not anecdotes, and funding decisions get easier.',
		},
		customer: {
			low: 'Start where the volume is: AI-drafted replies for your most common customer questions, reviewed by a human. It’s low-risk, measurable, and usually pays for itself in weeks.',
			mid: 'Extend AI from assistance to consistency: shared tone-of-voice guidance and AI-assisted QA across support and marketing keeps quality up as volume grows.',
		},
	};

	/* Shown when the user is strong everywhere (fewer than MIN_RECS
	   weak sections). Ordered — first entries are used first. */
	var LEADING_RECS = [
		'With foundations this strong, the frontier is custom: agentic workflows and AI systems built around your proprietary data and processes — advantages competitors can’t buy off the shelf.',
		'Audit how AI assistants describe and recommend your business. Operational maturity and AI-search visibility are now the same battle — being capable but invisible leaves revenue on the table.',
		'Set a quarterly AI review: retire what stopped earning, scale what worked, and re-baseline. Leaders stay leaders by treating this as an operating rhythm, not a project.',
	];

	/* ================================================================
	 * 4. LEAD CAPTURE — the single integration point.
	 * ----------------------------------------------------------------
	 * Called when the user submits the optional "email me my results"
	 * form. Receives everything a CRM or email tool needs:
	 *
	 *   lead = {
	 *     name:     "Jane Doe",
	 *     email:    "jane@company.com",
	 *     company:  "Company Ltd",
	 *     phone:    "" | "+44...",         // optional field
	 *     consent:  true,                   // contact opt-in checkbox
	 *     results: {
	 *       overall: 62,                    // 0–100
	 *       band:    "Advanced",
	 *       sections: [                     // ordered as displayed
	 *         { id: "adoption", label: "AI adoption & tools", score: 75 },
	 *         ...
	 *       ],
	 *       recommendations: [ "…", "…" ],  // the texts shown on screen
	 *     },
	 *     page: "https://alugence.ai/ai-audit/",
	 *     submittedAt: "2026-07-14T10:00:00.000Z",
	 *   }
	 *
	 * MUST return a Promise. Resolve → success state shown to the user;
	 * reject → an inline error with a retry.
	 *
	 * TO CONNECT A REAL EMAIL/CRM TOOL, replace the body below with a
	 * request to your endpoint, e.g.:
	 *
	 *   return fetch('https://hooks.yourcrm.com/audit-lead', {
	 *     method: 'POST',
	 *     headers: { 'Content-Type': 'application/json' },
	 *     body: JSON.stringify(lead),
	 *   }).then(function (res) {
	 *     if (!res.ok) { throw new Error('Lead submission failed'); }
	 *   });
	 *
	 * (Works the same for HubSpot Forms API, Mailchimp, Zapier/Make
	 * webhooks, or a small WordPress REST route that calls wp_mail().)
	 * ================================================================ */
	function submitLead(lead) {
		// PLACEHOLDER IMPLEMENTATION: logs the payload and succeeds.
		// Replace this body as documented above.
		return new Promise(function (resolve) {
			/* eslint-disable no-console */
			console.log('[Alugence audit] Lead captured (connect your CRM in submitLead):', lead);
			/* eslint-enable no-console */
			setTimeout(resolve, 400);
		});
	}

	/* ================================================================
	 * Engine — nothing below needs editing for content changes.
	 * ================================================================ */

	var STORAGE_KEY = 'tf-audit-v1'; // bump when SECTIONS change shape
	var TOTAL_STEPS = SECTIONS.length;

	var state = {
		step: -1, // -1 = intro, 0..n-1 = sections, n = results
		answers: {}, // questionId -> points chosen (raw, pre-normalisation)
	};

	function save() {
		try {
			sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
		} catch (e) { /* storage unavailable: continue without persistence */ }
	}

	function restore() {
		try {
			var raw = sessionStorage.getItem(STORAGE_KEY);
			if (!raw) { return; }
			var saved = JSON.parse(raw);
			if (saved && typeof saved.step === 'number' && saved.answers) {
				state = saved;
			}
		} catch (e) { /* corrupt state: start fresh */ }
	}

	/* ---------- scoring ---------- */

	function questionMax(q) {
		if (q.type === 'scale') { return 4; } // stored as 0–4
		var max = 0;
		q.options.forEach(function (o) { if (o.points > max) { max = o.points; } });
		return max || 1;
	}

	function sectionScore(section) {
		var got = 0;
		var max = 0;
		section.questions.forEach(function (q) {
			var a = state.answers[q.id];
			max += questionMax(q);
			got += typeof a === 'number' ? a : 0;
		});
		return Math.round((got / max) * 100);
	}

	function computeResults() {
		var sections = SECTIONS.map(function (s) {
			return { id: s.id, label: s.label, weight: s.weight, score: sectionScore(s) };
		});
		var weightSum = 0;
		var acc = 0;
		sections.forEach(function (s) {
			weightSum += s.weight;
			acc += s.score * s.weight;
		});
		var overall = Math.round(acc / weightSum);
		var band = BANDS.filter(function (b) { return overall >= b.min; })[0];

		// Recommendations: weakest sections first, low tier < 50, mid < 75.
		var recs = [];
		sections
			.slice()
			.sort(function (a, b) { return a.score - b.score; })
			.forEach(function (s) {
				if (recs.length >= MAX_RECS) { return; }
				var tiers = RECOMMENDATIONS[s.id];
				if (!tiers) { return; }
				if (s.score < 50 && tiers.low) { recs.push(tiers.low); }
				else if (s.score < 75 && tiers.mid) { recs.push(tiers.mid); }
			});
		for (var i = 0; recs.length < MIN_RECS && i < LEADING_RECS.length; i++) {
			recs.push(LEADING_RECS[i]);
		}

		return {
			overall: overall,
			band: band.label,
			bandSummary: band.summary,
			sections: sections.map(function (s) { return { id: s.id, label: s.label, score: s.score }; }),
			recommendations: recs,
		};
	}

	/* ---------- rendering helpers ---------- */

	function el(tag, className, html) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (html !== undefined) { node.innerHTML = html; }
		return node;
	}

	function esc(text) {
		return String(text).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function scrollToTop() {
		var y = mount.getBoundingClientRect().top + window.pageYOffset - 96;
		window.scrollTo({ top: y, behavior: 'smooth' });
	}

	function sectionComplete(section) {
		return section.questions.every(function (q) {
			return typeof state.answers[q.id] === 'number';
		});
	}

	/* ---------- views ---------- */

	function render() {
		mount.innerHTML = '';
		if (state.step === -1) { renderIntro(); }
		else if (state.step >= TOTAL_STEPS) { renderResults(); }
		else { renderSection(state.step); }
	}

	function renderIntro() {
		var card = el('div', 'audit-card audit-card--intro');
		card.appendChild(el('p', 'audit-kicker', esc(TOTAL_STEPS) + ' sections &middot; 15 questions &middot; ~3 minutes'));
		card.appendChild(el('h2', 'audit-card__title', 'How ready is your business, <em>really?</em>'));
		card.appendChild(el('p', 'audit-card__desc', 'Answer as things are today — not where you plan to be. You’ll get an AI Readiness Score out of 100, a breakdown across six areas, and specific next steps. Results appear instantly; nothing is emailed unless you ask.'));

		var list = el('ol', 'audit-intro-list');
		SECTIONS.forEach(function (s) {
			list.appendChild(el('li', '', '<strong>' + esc(s.label) + '</strong><span>' + esc(s.blurb) + '</span>'));
		});
		card.appendChild(list);

		var actions = el('div', 'audit-actions');
		var start = el('button', 'btn btn--volt btn--lg', (hasAnyAnswer() ? 'Continue where you left off' : 'Start the audit') + ' <span aria-hidden="true">→</span>');
		start.type = 'button';
		start.addEventListener('click', function () {
			state.step = hasAnyAnswer() ? firstIncompleteStep() : 0;
			save();
			render();
			scrollToTop();
		});
		actions.appendChild(start);
		if (hasAnyAnswer()) {
			var reset = el('button', 'audit-link-btn', 'Start over');
			reset.type = 'button';
			reset.addEventListener('click', function () {
				state = { step: 0, answers: {} };
				save();
				render();
			});
			actions.appendChild(reset);
		}
		card.appendChild(actions);
		mount.appendChild(card);
	}

	function hasAnyAnswer() {
		return Object.keys(state.answers).length > 0;
	}

	function firstIncompleteStep() {
		for (var i = 0; i < TOTAL_STEPS; i++) {
			if (!sectionComplete(SECTIONS[i])) { return i; }
		}
		return TOTAL_STEPS;
	}

	function renderSection(index) {
		var section = SECTIONS[index];
		var card = el('div', 'audit-card');

		// Progress rail
		var progress = el('div', 'audit-progress');
		progress.setAttribute('role', 'progressbar');
		progress.setAttribute('aria-valuemin', '0');
		progress.setAttribute('aria-valuemax', String(TOTAL_STEPS));
		progress.setAttribute('aria-valuenow', String(index));
		progress.setAttribute('aria-label', 'Audit progress');
		var bar = el('span', 'audit-progress__bar');
		bar.style.width = ((index / TOTAL_STEPS) * 100) + '%';
		progress.appendChild(bar);
		card.appendChild(progress);

		card.appendChild(el('p', 'audit-kicker', 'Section ' + (index + 1) + ' of ' + TOTAL_STEPS));
		card.appendChild(el('h2', 'audit-card__title', esc(section.label)));
		card.appendChild(el('p', 'audit-card__desc', esc(section.blurb)));

		section.questions.forEach(function (q, qi) {
			card.appendChild(renderQuestion(q, index, qi));
		});

		var actions = el('div', 'audit-actions');
		if (index > 0) {
			var back = el('button', 'btn btn--ghost-light', '← Back');
			back.type = 'button';
			back.addEventListener('click', function () {
				state.step = index - 1;
				save();
				render();
				scrollToTop();
			});
			actions.appendChild(back);
		}
		var next = el('button', 'btn btn--solid btn--lg', (index === TOTAL_STEPS - 1 ? 'See my results' : 'Next section') + ' <span aria-hidden="true">→</span>');
		next.type = 'button';
		next.disabled = !sectionComplete(section);
		next.addEventListener('click', function () {
			if (!sectionComplete(section)) { return; }
			state.step = index + 1;
			save();
			render();
			scrollToTop();
		});
		actions.appendChild(next);
		card.appendChild(actions);

		mount.appendChild(card);

		// Re-enable "next" as soon as the section becomes complete.
		card.addEventListener('change', function () {
			next.disabled = !sectionComplete(section);
		});
	}

	function renderQuestion(q, sectionIndex, qIndex) {
		var wrap = el('fieldset', 'audit-q');
		var legend = el('legend', 'audit-q__text', esc(q.text));
		wrap.appendChild(legend);

		var group = el('div', q.type === 'scale' ? 'audit-scale' : 'audit-choices');
		var name = 'q-' + q.id;

		if (q.type === 'scale') {
			for (var v = 1; v <= 5; v++) {
				group.appendChild(answerInput(name, q.id, v - 1, String(v), 'audit-scale__opt'));
			}
			var caps = el('div', 'audit-scale__caps');
			caps.appendChild(el('span', '', esc(q.lo || 'Low')));
			caps.appendChild(el('span', '', esc(q.hi || 'High')));
			wrap.appendChild(group);
			wrap.appendChild(caps);
		} else {
			q.options.forEach(function (o) {
				group.appendChild(answerInput(name, q.id, o.points, esc(o.label), 'audit-choice'));
			});
			wrap.appendChild(group);
		}
		return wrap;
	}

	function answerInput(name, qid, points, labelHtml, className) {
		var label = el('label', className);
		var input = document.createElement('input');
		input.type = 'radio';
		input.name = name;
		input.value = String(points);
		input.checked = state.answers[qid] === points;
		input.addEventListener('change', function () {
			state.answers[qid] = points;
			save();
		});
		label.appendChild(input);
		label.appendChild(el('span', className + '__label', labelHtml));
		return label;
	}

	/* ---------- results ---------- */

	function renderResults() {
		var results = computeResults();
		var card = el('div', 'audit-card audit-card--results');

		card.appendChild(el('p', 'audit-kicker', 'Your results'));

		// Headline score + band
		var head = el('div', 'audit-score');
		var value = el('p', 'audit-score__value', '<span data-score>0</span><span class="audit-score__of">/100</span>');
		head.appendChild(value);
		var bandCol = el('div', 'audit-score__band');
		bandCol.appendChild(el('p', 'audit-band-chip', esc(results.band)));
		bandCol.appendChild(el('p', 'audit-score__summary', esc(results.bandSummary)));
		head.appendChild(bandCol);
		card.appendChild(head);

		// Section breakdown bars
		var sorted = results.sections.slice().sort(function (a, b) { return b.score - a.score; });
		var strongest = sorted[0];
		var weakest = sorted[sorted.length - 1];

		var breakdown = el('div', 'audit-breakdown');
		breakdown.appendChild(el('h3', 'audit-sub', 'Where you stand, area by area'));
		results.sections.forEach(function (s) {
			var row = el('div', 'audit-bar');
			var tag = '';
			if (s.id === strongest.id) { tag = '<span class="audit-bar__tag audit-bar__tag--strong">Strongest</span>'; }
			if (s.id === weakest.id) { tag = '<span class="audit-bar__tag">Focus here</span>'; }
			row.appendChild(el('div', 'audit-bar__head', '<span>' + esc(s.label) + tag + '</span><span class="audit-bar__num">' + s.score + '</span>'));
			var track = el('div', 'audit-bar__track');
			var fill = el('span', 'audit-bar__fill' + (s.id === weakest.id ? ' audit-bar__fill--weak' : ''));
			fill.style.width = '0%';
			track.appendChild(fill);
			row.appendChild(track);
			breakdown.appendChild(row);
			requestAnimationFrame(function () {
				requestAnimationFrame(function () { fill.style.width = s.score + '%'; });
			});
		});
		card.appendChild(breakdown);

		// Recommendations
		var recwrap = el('div', 'audit-recs');
		recwrap.appendChild(el('h3', 'audit-sub', 'What we’d do next, in your position'));
		var list = el('ol', 'audit-recs__list');
		results.recommendations.forEach(function (r) {
			list.appendChild(el('li', '', esc(r)));
		});
		recwrap.appendChild(list);
		card.appendChild(recwrap);

		// Optional email capture (non-blocking, below full results)
		card.appendChild(renderLeadForm(results));

		// Booking CTA
		var cta = el('div', 'audit-cta theme-dark');
		cta.appendChild(el('h3', 'audit-cta__title', 'Want a tailored roadmap to act on these results?'));
		cta.appendChild(el('p', 'audit-cta__desc', 'A free 30-minute call: we’ll walk through your scores, pressure-test the weak spots and tell you honestly what’s worth doing first.'));
		var ctaBtn = el('a', 'btn btn--volt btn--lg', 'Book a free call <span aria-hidden="true">→</span>');
		ctaBtn.href = CTA_URL;
		cta.appendChild(ctaBtn);
		card.appendChild(cta);

		// Retake
		var retake = el('button', 'audit-link-btn', 'Retake the audit');
		retake.type = 'button';
		retake.addEventListener('click', function () {
			state = { step: 0, answers: {} };
			save();
			render();
			scrollToTop();
		});
		card.appendChild(retake);

		mount.appendChild(card);
		animateScore(card.querySelector('[data-score]'), results.overall);
	}

	function animateScore(node, target) {
		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduced || !node) {
			if (node) { node.textContent = String(target); }
			return;
		}
		var start = null;
		var DURATION = 1200;
		function tick(ts) {
			if (!start) { start = ts; }
			var p = Math.min((ts - start) / DURATION, 1);
			node.textContent = String(Math.round((1 - Math.pow(1 - p, 3)) * target));
			if (p < 1) { requestAnimationFrame(tick); }
		}
		requestAnimationFrame(tick);
	}

	function renderLeadForm(results) {
		var wrap = el('div', 'audit-capture');
		wrap.appendChild(el('h3', 'audit-sub', 'Email me a copy of my results'));
		wrap.appendChild(el('p', 'audit-capture__desc', 'Optional — you’ve already seen everything above. This just sends you a copy to keep' + (CONTACT_EMAIL ? ', from ' + esc(CONTACT_EMAIL) : '') + '.'));

		var form = el('form', 'audit-capture__form');
		form.noValidate = false;
		form.innerHTML =
			'<div class="audit-capture__row">' +
			'<div class="audit-capture__field"><label for="au-name">Name</label><input id="au-name" name="name" type="text" required autocomplete="name"></div>' +
			'<div class="audit-capture__field"><label for="au-email">Work email</label><input id="au-email" name="email" type="email" required autocomplete="email"></div>' +
			'</div>' +
			'<div class="audit-capture__row">' +
			'<div class="audit-capture__field"><label for="au-company">Company</label><input id="au-company" name="company" type="text" required autocomplete="organization"></div>' +
			'<div class="audit-capture__field"><label for="au-phone">Phone <span class="audit-optional">(optional)</span></label><input id="au-phone" name="phone" type="tel" autocomplete="tel"></div>' +
			'</div>' +
			'<label class="audit-consent"><input type="checkbox" name="consent" required> <span>I’m happy for Alugence to email my results and contact me about them. No newsletter, no spam.</span></label>' +
			'<button type="submit" class="btn btn--solid">Send my results <span aria-hidden="true">→</span></button>' +
			'<p class="audit-capture__status" role="status" aria-live="polite"></p>';

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var status = form.querySelector('.audit-capture__status');
			var button = form.querySelector('button[type="submit"]');
			button.disabled = true;
			status.textContent = 'Sending…';
			status.className = 'audit-capture__status';

			submitLead({
				name: form.name.value.trim(),
				email: form.email.value.trim(),
				company: form.company.value.trim(),
				phone: form.phone.value.trim(),
				consent: form.consent.checked,
				results: {
					overall: results.overall,
					band: results.band,
					sections: results.sections,
					recommendations: results.recommendations,
				},
				page: window.location.href,
				submittedAt: new Date().toISOString(),
			}).then(function () {
				wrap.innerHTML = '';
				wrap.appendChild(el('h3', 'audit-sub', 'Results on their way'));
				wrap.appendChild(el('p', 'audit-capture__desc audit-capture__desc--success', '✓ Done — check your inbox shortly. We’ll only be in touch about your audit.'));
			}).catch(function () {
				button.disabled = false;
				status.textContent = 'Something went wrong sending your results. Please try again' + (CONTACT_EMAIL ? ', or email ' + CONTACT_EMAIL + ' directly' : '') + '.';
				status.className = 'audit-capture__status audit-capture__status--error';
			});
		});

		wrap.appendChild(form);
		return wrap;
	}

	/* ---------- boot ---------- */

	restore();
	render();
})();
