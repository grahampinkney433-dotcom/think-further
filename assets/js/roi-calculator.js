/**
 * Alugence — AI ROI Calculator (workbook Tab 16, implemented exactly).
 *
 * - 11 inputs; instant on-screen results with NO email.
 * - hours_saved_per_week = employees × admin_hours × (savings% / 100)
 * - annual_cost_saving   = hours_saved × hourly_rate × 52
 * - productivity_gain%   = savings%
 * - Readiness score /10: Data, Tooling, Team, Momentum — each 0–2.5,
 *   linear rubric across that field's options (0 / 0.83 / 1.67 / 2.5).
 *   Momentum = mean of (previous attempts, appetite).
 * - Bands: 0–4 Foundations first · 5–7 Ready to pilot · 8–10 Ready to scale.
 * - GBP/USD toggle drives the default hourly rate + formatting.
 * - Gated "AI Opportunity Report": submitRoiLead() is the single
 *   CRM/email integration point (same contract as the audit tool —
 *   replace the body with a fetch() to your endpoint; payload below).
 */
(function () {
	'use strict';

	var mount = document.querySelector('[data-roi]');
	if (!mount) { return; }

	var SECTORS = [
		'Financial Services', 'Healthcare & Dental', 'Professional Services', 'Legal',
		'Retail & E-commerce', 'Real Estate & Property', 'Logistics & Transport',
		'Construction & Engineering', 'Manufacturing', 'Marketing & Agencies',
	];

	/* Linear 0–2.5 rubric across a field's options (Tab 16). */
	var STEPS = [0, 0.83, 1.67, 2.5];

	var FIELDS = {
		tools:    { label: 'Current tools in use', options: ['None', 'Basic SaaS', 'Some AI', 'Advanced'] },
		data:     { label: 'Where does your data live?', options: ['Paper / Manual', 'Spreadsheets', 'Cloud apps', 'Data warehouse'] },
		team:     { label: 'Team AI familiarity', options: ['None', 'Low', 'Medium', 'High'] },
		attempts: { label: 'Previous AI or automation attempts', options: ['None', 'Tried, stalled', 'Some success'] },
		appetite: { label: 'Appetite for change', options: ['Low', 'Medium', 'High'] },
		bottleneck: { label: 'Biggest bottleneck right now', options: ['Admin', 'Customer comms', 'Reporting', 'Sales', 'Ops'] },
	};

	/* Linear score for an option index within a field. */
	function fieldScore(key, index) {
		var count = FIELDS[key].options.length;
		if (count === 4) { return STEPS[index]; }
		// 3-option fields: linear 0 / 1.25 / 2.5.
		return (2.5 / (count - 1)) * index;
	}

	function band(score) {
		if (score >= 8) { return { label: 'Ready to scale', note: 'Strong foundations — focus on compounding: production systems and measurable rollout.' }; }
		if (score >= 5) { return { label: 'Ready to pilot', note: 'Good enough to start: pick one high-value workflow and prove it in weeks.' }; }
		return { label: 'Foundations first', note: 'Shore up data and tooling before scaling — a readiness sprint pays for itself here.' };
	}

	/**
	 * LEAD HOOK — the only integration point (mirrors submitLead() in
	 * audit.js). Payload:
	 * { name, email, company, website, consent,
	 *   inputs: {employees, adminHours, rate, currency, sector, savingsPct,
	 *            tools, data, team, bottleneck, attempts, appetite},
	 *   results: {hoursSavedPerWeek, annualSaving, productivityGainPct,
	 *             readiness, readinessBand, dimensions:{data,tooling,team,momentum}},
	 *   page, submittedAt }
	 * Replace this body with a fetch() to your CRM/email endpoint.
	 * Must return a Promise (resolve = success UI, reject = retry UI).
	 */
	function submitRoiLead(lead) {
		return new Promise(function (resolve) {
			console.log('[Alugence ROI] Lead captured (connect your CRM in submitRoiLead):', lead); // eslint-disable-line no-console
			setTimeout(resolve, 400);
		});
	}

	/* ---------- markup ---------- */

	function options(list) {
		return list.map(function (o, i) { return '<option value="' + i + '">' + o + '</option>'; }).join('');
	}

	mount.innerHTML =
		'<div class="audit-card">' +
		'<p class="audit-kicker">11 quick inputs · instant results · no email needed</p>' +
		'<form class="roi-form" novalidate>' +
		'<div class="audit-capture__row">' +
		'<div class="audit-capture__field"><label for="roi-cur">Currency</label><select id="roi-cur" name="currency"><option value="GBP">GBP £</option><option value="USD">USD $</option></select></div>' +
		'<div class="audit-capture__field"><label for="roi-emp">Number of employees</label><input id="roi-emp" name="employees" type="number" min="1" max="10000" value="10"></div>' +
		'<div class="audit-capture__field"><label for="roi-hrs">Admin hours per employee / week</label><input id="roi-hrs" name="adminHours" type="number" min="1" max="40" value="8"></div>' +
		'<div class="audit-capture__field"><label for="roi-rate">Average hourly rate</label><input id="roi-rate" name="rate" type="number" min="1" value="25"></div>' +
		'</div>' +
		'<div class="audit-capture__row">' +
		'<div class="audit-capture__field"><label for="roi-sector">Sector</label><select id="roi-sector" name="sector">' + SECTORS.map(function (s) { return '<option>' + s + '</option>'; }).join('') + '</select></div>' +
		'<div class="audit-capture__field"><label for="roi-save">Expected time savings from AI</label><select id="roi-save" name="savingsPct"><option value="20">20% — cautious</option><option value="35" selected>35% — realistic (6–12&nbsp;mo programme)</option><option value="50">50% — ambitious</option></select></div>' +
		'<div class="audit-capture__field"><label for="roi-bneck">' + FIELDS.bottleneck.label + '</label><select id="roi-bneck" name="bottleneck">' + options(FIELDS.bottleneck.options) + '</select></div>' +
		'</div>' +
		'<div class="audit-capture__row">' +
		'<div class="audit-capture__field"><label for="roi-tools">' + FIELDS.tools.label + '</label><select id="roi-tools" name="tools">' + options(FIELDS.tools.options) + '</select></div>' +
		'<div class="audit-capture__field"><label for="roi-data">' + FIELDS.data.label + '</label><select id="roi-data" name="data">' + options(FIELDS.data.options) + '</select></div>' +
		'<div class="audit-capture__field"><label for="roi-team">' + FIELDS.team.label + '</label><select id="roi-team" name="team">' + options(FIELDS.team.options) + '</select></div>' +
		'</div>' +
		'<div class="audit-capture__row">' +
		'<div class="audit-capture__field"><label for="roi-att">' + FIELDS.attempts.label + '</label><select id="roi-att" name="attempts">' + options(FIELDS.attempts.options) + '</select></div>' +
		'<div class="audit-capture__field"><label for="roi-app">' + FIELDS.appetite.label + '</label><select id="roi-app" name="appetite">' + options(FIELDS.appetite.options) + '</select></div>' +
		'</div>' +
		'</form>' +
		'<div class="roi-results" data-results hidden>' +
		'<h2 class="audit-sub">Your AI opportunity</h2>' +
		'<dl class="roi-stats">' +
		'<div class="roi-stat"><dt>Hours saved / week</dt><dd data-out="hours">—</dd></div>' +
		'<div class="roi-stat"><dt>Annual cost saving</dt><dd data-out="saving">—</dd></div>' +
		'<div class="roi-stat"><dt>Productivity gain</dt><dd data-out="gain">—</dd></div>' +
		'<div class="roi-stat"><dt>AI readiness</dt><dd data-out="readiness">—</dd></div>' +
		'</dl>' +
		'<p class="audit-capture__desc" data-out="bandnote"></p>' +
		'</div>' +
		'<div class="audit-capture" data-report>' +
		'<h3 class="audit-sub">Get the full AI Opportunity Report (PDF)</h3>' +
		'<p class="audit-capture__desc">Your numbers, a readiness breakdown by dimension, and three sector-specific workflow recommendations. GDPR: consent required, no third-party sharing, unsubscribe any time.</p>' +
		'<form class="audit-capture__form" data-report-form>' +
		'<div class="audit-capture__row">' +
		'<div class="audit-capture__field"><label for="roi-name">Name</label><input id="roi-name" name="name" type="text" required autocomplete="name"></div>' +
		'<div class="audit-capture__field"><label for="roi-email">Work email</label><input id="roi-email" name="email" type="email" required autocomplete="email"></div>' +
		'</div>' +
		'<div class="audit-capture__row">' +
		'<div class="audit-capture__field"><label for="roi-co">Company</label><input id="roi-co" name="company" type="text" required autocomplete="organization"></div>' +
		'<div class="audit-capture__field"><label for="roi-web">Website</label><input id="roi-web" name="website" type="url" required autocomplete="url"></div>' +
		'</div>' +
		'<label class="audit-consent"><input type="checkbox" name="consent" required> <span>Email me the report and follow up about a free 20-minute scoping call. No newsletter, no spam.</span></label>' +
		'<button type="submit" class="btn btn--solid">Send my report <span aria-hidden="true">→</span></button>' +
		'<p class="audit-capture__status" role="status" aria-live="polite"></p>' +
		'</form>' +
		'</div>' +
		'</div>';

	var form = mount.querySelector('.roi-form');
	var results = mount.querySelector('[data-results]');

	function fmt(n, currency) {
		return new Intl.NumberFormat(currency === 'USD' ? 'en-US' : 'en-GB', {
			style: 'currency', currency: currency, maximumFractionDigits: 0,
		}).format(n);
	}

	function compute() {
		var employees = Math.max(1, parseInt(form.employees.value, 10) || 0);
		var adminHours = Math.max(1, parseFloat(form.adminHours.value) || 0);
		var rate = Math.max(1, parseFloat(form.rate.value) || 0);
		var pct = parseInt(form.savingsPct.value, 10);
		var currency = form.currency.value;

		// Tab 16 calculation logic — implemented exactly.
		var hoursSaved = Math.round(employees * adminHours * (pct / 100));
		var annualSaving = hoursSaved * rate * 52;

		var dims = {
			data: fieldScore('data', parseInt(form.data.value, 10)),
			tooling: fieldScore('tools', parseInt(form.tools.value, 10)),
			team: fieldScore('team', parseInt(form.team.value, 10)),
			momentum: (fieldScore('attempts', parseInt(form.attempts.value, 10)) + fieldScore('appetite', parseInt(form.appetite.value, 10))) / 2,
		};
		var readiness = Math.round((dims.data + dims.tooling + dims.team + dims.momentum) * 10) / 10;
		var b = band(readiness);

		results.hidden = false;
		results.querySelector('[data-out="hours"]').textContent = hoursSaved + ' hrs';
		results.querySelector('[data-out="saving"]').textContent = fmt(annualSaving, currency) + '/yr';
		results.querySelector('[data-out="gain"]').textContent = pct + '%';
		results.querySelector('[data-out="readiness"]').textContent = readiness + '/10 · ' + b.label;
		results.querySelector('[data-out="bandnote"]').textContent = b.note;

		return {
			inputs: {
				employees: employees, adminHours: adminHours, rate: rate, currency: currency,
				sector: form.sector.value, savingsPct: pct,
				tools: FIELDS.tools.options[form.tools.value],
				data: FIELDS.data.options[form.data.value],
				team: FIELDS.team.options[form.team.value],
				bottleneck: FIELDS.bottleneck.options[form.bottleneck.value],
				attempts: FIELDS.attempts.options[form.attempts.value],
				appetite: FIELDS.appetite.options[form.appetite.value],
			},
			results: {
				hoursSavedPerWeek: hoursSaved,
				annualSaving: annualSaving,
				productivityGainPct: pct,
				readiness: readiness,
				readinessBand: b.label,
				dimensions: dims,
			},
		};
	}

	// Currency toggle drives the hourly-rate default (Tab 16).
	form.currency.addEventListener('change', function () {
		form.rate.value = form.currency.value === 'USD' ? 32 : 25;
		compute();
	});
	form.addEventListener('input', compute);
	compute();

	// Gated report submission.
	var reportForm = mount.querySelector('[data-report-form]');
	reportForm.addEventListener('submit', function (e) {
		e.preventDefault();
		var status = reportForm.querySelector('.audit-capture__status');
		var button = reportForm.querySelector('button[type="submit"]');
		button.disabled = true;
		status.textContent = 'Sending…';

		var snapshot = compute();
		submitRoiLead({
			name: reportForm.name.value.trim(),
			email: reportForm.email.value.trim(),
			company: reportForm.company.value.trim(),
			website: reportForm.website.value.trim(),
			consent: reportForm.consent.checked,
			inputs: snapshot.inputs,
			results: snapshot.results,
			page: window.location.href,
			submittedAt: new Date().toISOString(),
		}).then(function () {
			var wrap = mount.querySelector('[data-report]');
			wrap.innerHTML = '<h3 class="audit-sub">Report on its way</h3><p class="audit-capture__desc audit-capture__desc--success">✓ Done — your AI Opportunity Report will be in your inbox shortly, with a link to book a free 20-minute scoping call.</p>';
		}).catch(function () {
			button.disabled = false;
			status.textContent = 'Something went wrong. Please try again or email us directly.';
			status.className = 'audit-capture__status audit-capture__status--error';
		});
	});
})();
