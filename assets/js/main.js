/**
 * Think Further — interactions.
 * No dependencies. Everything degrades gracefully and
 * respects prefers-reduced-motion.
 */
(function () {
	'use strict';

	var doc = document.documentElement;
	doc.classList.remove('no-js');
	doc.classList.add('js');

	var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ------------------------------------------------------------
	 * Header: scrolled state
	 * ---------------------------------------------------------- */
	var header = document.querySelector('[data-header]');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 8);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* ------------------------------------------------------------
	 * Mobile navigation
	 * ---------------------------------------------------------- */
	var navToggle = document.querySelector('[data-nav-toggle]');
	var nav = document.getElementById('site-nav');
	if (navToggle && nav) {
		navToggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				nav.classList.remove('is-open');
				navToggle.setAttribute('aria-expanded', 'false');
				navToggle.focus();
			}
		});

		nav.addEventListener('click', function (e) {
			if (e.target.closest('a')) {
				nav.classList.remove('is-open');
				navToggle.setAttribute('aria-expanded', 'false');
			}
		});
	}

	/* ------------------------------------------------------------
	 * Scroll reveal
	 * ---------------------------------------------------------- */
	if (!('IntersectionObserver' in window)) {
		doc.classList.add('no-io');
	} else {
		var revealEls = document.querySelectorAll('.reveal');
		var stagger = 90;
		var revealObserver = new IntersectionObserver(
			function (entries) {
				var delay = 0;
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) {
						return;
					}
					var el = entry.target;
					if (reducedMotion) {
						el.classList.add('is-visible');
					} else {
						el.style.transitionDelay = delay + 'ms';
						el.classList.add('is-visible');
						el.addEventListener(
							'transitionend',
							function () {
								el.style.transitionDelay = '';
							},
							{ once: true }
						);
						delay += stagger;
					}
					revealObserver.unobserve(el);
				});
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.05 }
		);
		revealEls.forEach(function (el) {
			revealObserver.observe(el);
		});
	}

	/* ------------------------------------------------------------
	 * Stat counters
	 * ---------------------------------------------------------- */
	var counters = document.querySelectorAll('[data-count]');
	if (counters.length && 'IntersectionObserver' in window && !reducedMotion) {
		var countObserver = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) {
						return;
					}
					var el = entry.target;
					var target = parseInt(el.getAttribute('data-count'), 10) || 0;
					var duration = 1400;
					var start = null;

					var tick = function (ts) {
						if (!start) {
							start = ts;
						}
						var progress = Math.min((ts - start) / duration, 1);
						var eased = 1 - Math.pow(1 - progress, 3);
						el.textContent = Math.round(eased * target);
						if (progress < 1) {
							requestAnimationFrame(tick);
						}
					};
					el.textContent = '0';
					requestAnimationFrame(tick);
					countObserver.unobserve(el);
				});
			},
			{ threshold: 0.4 }
		);
		counters.forEach(function (el) {
			countObserver.observe(el);
		});
	}

	/* ------------------------------------------------------------
	 * Subtle 3D tilt on cards
	 * ---------------------------------------------------------- */
	if (!reducedMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
		document.querySelectorAll('[data-tilt]').forEach(function (card) {
			var raf = null;

			card.addEventListener('pointermove', function (e) {
				if (raf) {
					return;
				}
				raf = requestAnimationFrame(function () {
					var rect = card.getBoundingClientRect();
					var x = (e.clientX - rect.left) / rect.width - 0.5;
					var y = (e.clientY - rect.top) / rect.height - 0.5;
					card.style.transform =
						'perspective(1100px) rotateX(' + (-y * 2.4).toFixed(2) + 'deg) rotateY(' + (x * 2.4).toFixed(2) + 'deg)';
					raf = null;
				});
			});

			card.addEventListener('pointerleave', function () {
				card.style.transform = '';
			});
		});
	}

	/* ------------------------------------------------------------
	 * Reading progress bar (single posts)
	 * ---------------------------------------------------------- */
	var progressBar = document.querySelector('[data-progress-bar]');
	var progressTarget = document.querySelector('[data-progress-target]');
	if (progressBar && progressTarget) {
		var progressRaf = null;
		var updateProgress = function () {
			progressRaf = null;
			var rect = progressTarget.getBoundingClientRect();
			var total = rect.height - window.innerHeight;
			var progress = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 1;
			progressBar.style.transform = 'scaleX(' + progress.toFixed(4) + ')';
		};
		window.addEventListener(
			'scroll',
			function () {
				if (!progressRaf) {
					progressRaf = requestAnimationFrame(updateProgress);
				}
			},
			{ passive: true }
		);
		updateProgress();
	}

	/* ------------------------------------------------------------
	 * Hero canvas: drifting signal field
	 * A quiet grid of points displaced by layered sine waves —
	 * points near the pointer brighten and lift.
	 * ---------------------------------------------------------- */
	var canvas = document.querySelector('[data-hero-canvas]');
	if (canvas && !reducedMotion && canvas.getContext) {
		var ctx = canvas.getContext('2d');
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var width = 0;
		var height = 0;
		var points = [];
		var pointer = { x: -9999, y: -9999 };
		var running = true;
		var GAP = 42;

		var resize = function () {
			var rect = canvas.getBoundingClientRect();
			width = rect.width;
			height = rect.height;
			canvas.width = width * dpr;
			canvas.height = height * dpr;
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

			points = [];
			var cols = Math.ceil(width / GAP) + 2;
			var rows = Math.ceil(height / GAP) + 2;
			for (var r = 0; r < rows; r++) {
				for (var c = 0; c < cols; c++) {
					points.push({ x: c * GAP - GAP / 2, y: r * GAP - GAP / 2 });
				}
			}
		};

		var render = function (t) {
			if (!running) {
				return;
			}
			ctx.clearRect(0, 0, width, height);
			var time = t * 0.00022;

			for (var i = 0; i < points.length; i++) {
				var p = points[i];
				var wave =
					Math.sin(p.x * 0.011 + time * 2.1) +
					Math.cos(p.y * 0.013 - time * 1.7) +
					Math.sin((p.x + p.y) * 0.006 + time);
				var dx = p.x - pointer.x;
				var dy = p.y - pointer.y;
				var dist = Math.sqrt(dx * dx + dy * dy);
				var influence = Math.max(0, 1 - dist / 220);

				var px = p.x + wave * 6;
				var py = p.y + wave * 5 - influence * 10;

				// Fade the field toward the top and left where the copy sits.
				var fade = Math.min(1, (px / width) * 1.4) * Math.min(1, (py / height) * 1.6 + 0.12);
				var alpha = (0.09 + (wave + 3) * 0.05 + influence * 0.4) * fade;
				if (alpha <= 0.01) {
					continue;
				}

				var size = 1 + influence * 1.6;
				// Brand: indigo mesh, charged by volt at the pointer.
				if (influence > 0.08) {
					ctx.fillStyle = 'rgba(205, 249, 78, ' + alpha.toFixed(3) + ')';
				} else {
					ctx.fillStyle = 'rgba(110, 120, 255, ' + alpha.toFixed(3) + ')';
				}
				ctx.beginPath();
				ctx.arc(px, py, size, 0, Math.PI * 2);
				ctx.fill();
			}

			requestAnimationFrame(render);
		};

		var hero = canvas.closest('.hero') || canvas.parentElement;
		hero.addEventListener('pointermove', function (e) {
			var rect = canvas.getBoundingClientRect();
			pointer.x = e.clientX - rect.left;
			pointer.y = e.clientY - rect.top;
		});
		hero.addEventListener('pointerleave', function () {
			pointer.x = -9999;
			pointer.y = -9999;
		});

		// Pause when the hero leaves the viewport or the tab hides.
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(
				function (entries) {
					var wasRunning = running;
					running = entries[0].isIntersecting && !document.hidden;
					if (running && !wasRunning) {
						requestAnimationFrame(render);
					}
				},
				{ threshold: 0 }
			).observe(canvas);
		}
		document.addEventListener('visibilitychange', function () {
			var wasRunning = running;
			running = !document.hidden;
			if (running && !wasRunning) {
				requestAnimationFrame(render);
			}
		});

		var resizeTimer;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(resize, 150);
		});

		resize();
		requestAnimationFrame(render);
	}
})();
