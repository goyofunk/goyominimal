/**
 * 홈 히어로: 위로 스크롤할수록 .mainhero-content 를 축소(스크롤 거리 비례)
 */
(function () {
	'use strict';

	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	function attach(content, spacer) {
		var SCALE_MAX = 1;
		var SCALE_MIN = 0.7;
		var scaleRange = SCALE_MAX - SCALE_MIN;
		var OPACITY_MAX = 1;
		var OPACITY_MIN = 0.5;
		var opacityRange = OPACITY_MAX - OPACITY_MIN;
		var maxScroll = 1;
		var raf = false;

		function readScrollY() {
			return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
		}

		function getMaxScroll() {
			var h = spacer.offsetHeight;
			if (h < 1) {
				h = window.innerHeight;
			}
			return Math.max(1, h);
		}

		function applyScale() {
			raf = false;
			if (document.body.classList.contains('goyo-hero-past-fold')) {
				return;
			}
			var y = readScrollY();
			var t = Math.min(1, y / maxScroll);
			var s = SCALE_MAX - scaleRange * t;
			var o = OPACITY_MAX - opacityRange * t;
			content.style.setProperty('transform', 'scale3d(' + s + ',' + s + ',1)');
			content.style.setProperty('opacity', String(o));
		}

		function requestApply() {
			if (raf) {
				return;
			}
			raf = true;
			window.requestAnimationFrame(applyScale);
		}

		function remeasure() {
			maxScroll = getMaxScroll();
			updateHeroPastFold();
			requestApply();
		}

		/* 히어로 스페이서(1뷰포트) 아래: body 클래스로 전체 레이어 비표시(CSS)·유니콘 파괴(unicorn-loader)와 동기 */
		function updateHeroPastFold() {
			var y = readScrollY();
			var th = spacer.offsetHeight;
			if (th < 1) {
				th = window.innerHeight;
			}
			var past = y >= th - 0.5;
			document.body.classList.toggle('goyo-hero-past-fold', past);
		}

		function onScrollOrResizeFold() {
			updateHeroPastFold();
			requestApply();
		}

		remeasure();

		window.addEventListener('scroll', onScrollOrResizeFold, { passive: true });
		window.addEventListener('resize', remeasure, { passive: true });
	}

	function bootstrap() {
		if (!document.body) {
			return;
		}
		var isHomeLayout = document.body.classList.contains('front-page') || document.body.classList.contains('goyo-home-overlay');
		if (!isHomeLayout) {
			return;
		}

		function init() {
			var content = document.querySelector('.mainhero .mainhero-content');
			var spacer = document.querySelector('.mainhero-spacer');
			if (!content || !spacer) {
				return;
			}
			attach(content, spacer);
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', init);
		} else {
			init();
		}
	}

	bootstrap();
})();
