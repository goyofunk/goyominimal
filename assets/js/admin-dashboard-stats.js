/**
 * 알림판 조회·방문 통계: KPI, Canvas 차트, 전체 폭 배치.
 */
(function () {
	'use strict';

	function onReady(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	function getCfg() {
		return window.goyoartdarkDashStats || null;
	}

	function applyKpi(summary, scope) {
		var rootEl = scope || document;
		var map = {
			'today-views': summary.today.views,
			'yesterday-views': summary.yesterday.views,
			'total-views': summary.total.views,
			'today-visitors': summary.today.visitors,
			'yesterday-visitors': summary.yesterday.visitors,
			'total-visitors': summary.total.visitors
		};
		Object.keys(map).forEach(function (key) {
			var el = rootEl.querySelector('[data-kpi="' + key + '"]');
			if (el) {
				el.textContent = String(map[key]);
			}
		});
	}

	function moveStatsWidgetFullWidth() {
		var statsBox = document.getElementById('goyoartdark_site_stats');
		var linksBox = document.getElementById('goyoartdark_site_stats_links');
		var dww = document.getElementById('dashboard-widgets-wrap');
		if (!statsBox || !dww) {
			return;
		}
		var shell = statsBox.closest('.goyoartdark-site-stats-fullwidth');
		if (!shell) {
			shell = document.createElement('div');
			shell.className = 'goyoartdark-site-stats-fullwidth';
			statsBox.parentNode.insertBefore(shell, statsBox);
		}
		if (statsBox.getAttribute('data-goyo-fullwidth') !== '1') {
			shell.appendChild(statsBox);
			statsBox.setAttribute('data-goyo-fullwidth', '1');
		}
		if (linksBox && linksBox.getAttribute('data-goyo-fullwidth') !== '1') {
			shell.appendChild(linksBox);
			linksBox.setAttribute('data-goyo-fullwidth', '1');
		}
		if (shell.parentNode !== dww) {
			dww.insertBefore(shell, dww.firstChild);
		} else if (dww.firstChild !== shell) {
			dww.insertBefore(shell, dww.firstChild);
		}
	}

	function drawChart(canvas, labels, views, visitors) {
		var dpr = window.devicePixelRatio || 1;
		var wrap = canvas.parentElement;
		var cssH = 200;
		var cssW = Math.floor(canvas.getBoundingClientRect().width);
		if (cssW < 2 && wrap) {
			cssW = Math.floor(wrap.getBoundingClientRect().width - 8);
		}
		if (cssW < 2) {
			cssW = 680;
		}
		if (cssW < 280) {
			cssW = 280;
		}
		canvas.width = Math.floor(cssW * dpr);
		canvas.height = Math.floor(cssH * dpr);
		var ctx = canvas.getContext('2d');
		ctx.setTransform(1, 0, 0, 1, 0, 0);
		ctx.scale(dpr, dpr);
		var W = cssW;
		var H = cssH;
		var padL = 36;
		var padR = 12;
		var padT = 12;
		var padB = 28;
		var innerW = W - padL - padR;
		var innerH = H - padT - padB;
		var maxVal = 1;
		var i;
		for (i = 0; i < views.length; i++) {
			if (views[i] > maxVal) {
				maxVal = views[i];
			}
		}
		for (i = 0; i < visitors.length; i++) {
			if (visitors[i] > maxVal) {
				maxVal = visitors[i];
			}
		}
		ctx.clearRect(0, 0, W, H);
		ctx.fillStyle = '#fff';
		ctx.fillRect(0, 0, W, H);
		ctx.strokeStyle = 'rgba(0, 0, 0, 0.07)';
		ctx.lineWidth = 1;
		var gridN = 4;
		var g;
		for (g = 0; g <= gridN; g++) {
			var gy = padT + (innerH * g) / gridN;
			ctx.beginPath();
			ctx.setLineDash([3, 4]);
			ctx.moveTo(padL, gy);
			ctx.lineTo(padL + innerW, gy);
			ctx.stroke();
		}
		ctx.setLineDash([]);
		function drawLine(arr, color, lw) {
			if (!arr.length) {
				return;
			}
			var n = arr.length;
			var xi;
			ctx.beginPath();
			for (xi = 0; xi < n; xi++) {
				var vx = padL + (innerW * xi) / (n > 1 ? n - 1 : 1);
				var vy = padT + innerH - (innerH * arr[xi]) / maxVal;
				if (xi === 0) {
					ctx.moveTo(vx, vy);
				} else {
					ctx.lineTo(vx, vy);
				}
			}
			ctx.strokeStyle = color;
			ctx.lineWidth = lw;
			ctx.stroke();
			for (xi = 0; xi < n; xi++) {
				var px = padL + (innerW * xi) / (n > 1 ? n - 1 : 1);
				var py = padT + innerH - (innerH * arr[xi]) / maxVal;
				ctx.beginPath();
				ctx.fillStyle = '#fff';
				ctx.strokeStyle = color;
				ctx.lineWidth = 1.5;
				ctx.arc(px, py, 3.5, 0, Math.PI * 2);
				ctx.fill();
				ctx.stroke();
			}
		}
		drawLine(views, '#08b58a', 2);
		drawLine(visitors, '#8c8f94', 1.75);
		ctx.font = '11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
		ctx.fillStyle = '#646970';
		var step = Math.max(1, Math.ceil(labels.length / 8));
		var li;
		for (li = 0; li < labels.length; li++) {
			if (li % step !== 0 && li !== labels.length - 1) {
				continue;
			}
			var lx = padL + (innerW * li) / (labels.length > 1 ? labels.length - 1 : 1);
			if (li === labels.length - 1) {
				ctx.textAlign = 'right';
				ctx.fillText(labels[li], padL + innerW, H - 8);
			} else {
				ctx.textAlign = 'center';
				ctx.fillText(labels[li], lx, H - 8);
			}
		}
		canvas._goyoLayout = {
			padL: padL,
			padR: padR,
			padT: padT,
			padB: padB,
			innerW: innerW,
			innerH: innerH,
			maxVal: maxVal,
			n: views.length,
			labels: labels,
			views: views,
			visitors: visitors,
			W: W,
			H: H
		};
	}

	/** 그래프 좌표계에서 점–선분 최단 거리와 근처 표시값. */
	function closestOnSeries(mx, my, layout, arr) {
		var n = layout.n;
		if (n < 1) {
			return null;
		}
		var denom = n > 1 ? n - 1 : 1;
		var bestD = Infinity;
		var bestPx = layout.padL;
		var bestPy = 0;
		var bestVal = arr[0];
		var bestXi = 0;
		if (n === 1) {
			bestPx = layout.padL;
			bestPy = layout.padT + layout.innerH - (layout.innerH * arr[0]) / layout.maxVal;
			return {
				dist: Math.sqrt((mx - bestPx) * (mx - bestPx) + (my - bestPy) * (my - bestPy)),
				px: bestPx,
				py: bestPy,
				value: arr[0],
				xi: 0
			};
		}
		var i;
		var x1;
		var y1;
		var x2;
		var y2;
		var dx;
		var dy;
		var len2;
		var t;
		var qx;
		var qy;
		var d;
		var dist1;
		var dist2;
		for (i = 0; i < n - 1; i++) {
			x1 = layout.padL + (layout.innerW * i) / denom;
			y1 = layout.padT + layout.innerH - (layout.innerH * arr[i]) / layout.maxVal;
			x2 = layout.padL + (layout.innerW * (i + 1)) / denom;
			y2 = layout.padT + layout.innerH - (layout.innerH * arr[i + 1]) / layout.maxVal;
			dx = x2 - x1;
			dy = y2 - y1;
			len2 = dx * dx + dy * dy;
			t = len2 < 1e-10 ? 0 : Math.max(0, Math.min(1, ((mx - x1) * dx + (my - y1) * dy) / len2));
			qx = x1 + t * dx;
			qy = y1 + t * dy;
			d = Math.sqrt((mx - qx) * (mx - qx) + (my - qy) * (my - qy));
			if (d < bestD) {
				bestD = d;
				bestPx = qx;
				bestPy = qy;
				dist1 = Math.sqrt((mx - x1) * (mx - x1) + (my - y1) * (my - y1));
				dist2 = Math.sqrt((mx - x2) * (mx - x2) + (my - y2) * (my - y2));
				if (dist1 <= dist2) {
					bestVal = arr[i];
					bestXi = i;
				} else {
					bestVal = arr[i + 1];
					bestXi = i + 1;
				}
			}
		}
		return { dist: bestD, px: bestPx, py: bestPy, value: bestVal, xi: bestXi };
	}

	function bindChartTooltip(canvas, wrap, cfg) {
		var tip = document.createElement('div');
		tip.className = 'goyoartdark-site-stats__chart-tooltip';
		tip.setAttribute('role', 'tooltip');
		tip.hidden = true;
		wrap.appendChild(tip);
		var lineNums = document.createElement('span');
		lineNums.className = 'goyoartdark-site-stats__chart-tooltip-nums';
		var lineSub = document.createElement('span');
		lineSub.className = 'goyoartdark-site-stats__chart-tooltip-line';
		tip.appendChild(lineNums);
		tip.appendChild(lineSub);

		function hideTip() {
			tip.hidden = true;
		}

		function canvasCoords(evt) {
			var layout = canvas._goyoLayout;
			if (!layout) {
				return null;
			}
			var rect = canvas.getBoundingClientRect();
			if (!rect.width || !rect.height) {
				return null;
			}
			return {
				mx: ((evt.clientX - rect.left) / rect.width) * layout.W,
				my: ((evt.clientY - rect.top) / rect.height) * layout.H,
				layout: layout
			};
		}

		function onMove(evt) {
			var cur = canvasCoords(evt);
			if (!cur) {
				hideTip();
				return;
			}
			var layout = cur.layout;
			if (layout.n < 1) {
				hideTip();
				return;
			}
			if (cur.mx < layout.padL - 8 || cur.mx > layout.W - layout.padR + 8 || cur.my < layout.padT - 8 || cur.my > layout.H - layout.padB + 8) {
				hideTip();
				return;
			}
			var hitMax = 22;
			var nearV = closestOnSeries(cur.mx, cur.my, layout, layout.views);
			var nearU = closestOnSeries(cur.mx, cur.my, layout, layout.visitors);
			if (!nearV || !nearU) {
				hideTip();
				return;
			}
			var pick;
			if (nearV.dist < nearU.dist) {
				pick = { kind: 'views', hit: nearV };
			} else {
				pick = { kind: 'visitors', hit: nearU };
			}
			if (pick.hit.dist > hitMax) {
				hideTip();
				return;
			}
			var lab = layout.labels[pick.hit.xi] ? String(layout.labels[pick.hit.xi]) : '';
			var sv = cfg.strings && cfg.strings.chartTooltipViews ? cfg.strings.chartTooltipViews : '';
			var su = cfg.strings && cfg.strings.chartTooltipVisitors ? cfg.strings.chartTooltipVisitors : '';
			lineNums.textContent = String(pick.hit.value);
			lineSub.textContent = lab;
			lineSub.hidden = !lab;
			if (pick.kind === 'views' && sv) {
				tip.setAttribute('aria-label', (lab ? lab + ', ' : '') + sv + ' ' + String(pick.hit.value));
			} else if (pick.kind === 'visitors' && su) {
				tip.setAttribute('aria-label', (lab ? lab + ', ' : '') + su + ' ' + String(pick.hit.value));
			} else {
				tip.removeAttribute('aria-label');
			}
			tip.style.left = ((pick.hit.px / layout.W) * 100) + '%';
			tip.style.top = Math.max(4, pick.hit.py - 40) + 'px';
			tip.hidden = false;
		}

		canvas.addEventListener('mousemove', onMove);
		canvas.addEventListener('mouseleave', hideTip);
		wrap.addEventListener('mouseleave', hideTip);
	}

	onReady(function () {
		var cfg = getCfg();
		var root = document.getElementById('goyoartdark-site-stats-widget');
		if (!cfg || !root) {
			return;
		}
		var inner = root.querySelector('.goyoartdark-site-stats__inner');
		var canvas = document.getElementById('goyoartdark-site-stats-chart');
		if (!inner || !canvas) {
			return;
		}

		function redrawChart() {
			drawChart(canvas, cfg.labels, cfg.series.views, cfg.series.visitors);
		}

		applyKpi(cfg.summary, root);
		moveStatsWidgetFullWidth();
		redrawChart();
		requestAnimationFrame(function () {
			requestAnimationFrame(redrawChart);
		});

		window.addEventListener('resize', redrawChart);

		var chartWrap = canvas.parentElement;
		if (chartWrap) {
			bindChartTooltip(canvas, chartWrap, cfg);
		}
	});
}());
