/**
 * 페이지 전환: html.goyo-page-fade 시 body opacity 로 부드럽게 페이드 ( Chrome / Firefox 공통 ).
 *
 * - prefers-reduced-motion 이면 인라인 스크립트가 .goyo-page-fade 를 붙이지 않아 이 파일은 조기 종료.
 * - 최초 로드: body opacity 0 → .goyo-page-ready 로 페이드-인 ( page-transition.css 와 동일한 짧은 시간 ).
 * - 내부 링크: .goyo-page-leaving 으로 페이드-아웃( 180ms ) 후 이동 — transitionend(opacity) 또는 타임아웃.
 */
(function () {
	'use strict';

	var FADE_CLASS = 'goyo-page-fade';
	var READY_CLASS = 'goyo-page-ready';
	var LEAVING_CLASS = 'goyo-page-leaving';
	/* page-transition.css 의 html.goyo-page-fade.goyo-page-leaving body transition-duration 과 맞출 것 */
	var FADE_OUT_MS = 180;
	var FADE_OUT_BUFFER_MS = 50;

	var html = document.documentElement;

	if (!html.classList.contains(FADE_CLASS)) {
		return;
	}

	function markReady() {
		requestAnimationFrame(function () {
			html.classList.remove(LEAVING_CLASS);
			html.classList.add(READY_CLASS);
		});
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', markReady, { once: true });
	} else {
		markReady();
	}

	window.addEventListener('pageshow', function (event) {
		if (event.persisted) {
			html.classList.remove(LEAVING_CLASS);
			html.classList.add(READY_CLASS);
		}
	});

	function isInternalNavigableLink(anchor, event) {
		if (!anchor || !anchor.href) return false;
		// 맨 위로: custom.js가 스크롤만 담당. 페이드+location 강제 이동 시 본문이 사라지는 것처럼 보임
		if (anchor.classList && anchor.classList.contains('gotoTop')) {
			return false;
		}
		if (event.defaultPrevented) return false;
		if (event.button !== 0) return false;
		if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
		if (anchor.target && anchor.target !== '_self') return false;
		if (anchor.hasAttribute('download')) return false;
		if (anchor.getAttribute('rel') && /(?:^|\s)external(?:\s|$)/.test(anchor.getAttribute('rel'))) return false;

		var url;
		try {
			url = new URL(anchor.href, window.location.href);
		} catch (e) {
			return false;
		}

		if (url.origin !== window.location.origin) return false;
		// 동일 문서(경로·쿼리만 같음, 앵커/#만 다름, href="#") — 페이드 내비 제외. 빈 hash 가드 누락이면 href="#" 이 전체 전환으로 잡힘
		if (url.pathname === window.location.pathname && url.search === window.location.search) {
			return false;
		}
		if (/\.(pdf|zip|rar|7z|dmg|exe|mp4|mp3|jpg|jpeg|png|gif|webp|svg)$/i.test(url.pathname)) return false;

		return true;
	}

	document.addEventListener(
		'click',
		function (event) {
			var anchor = event.target && event.target.closest ? event.target.closest('a[href]') : null;
			if (!isInternalNavigableLink(anchor, event)) return;

			event.preventDefault();
			var destination = anchor.href;

			html.classList.remove(READY_CLASS);
			html.classList.add(LEAVING_CLASS);

			var navigated = false;
			function go() {
				if (navigated) return;
				navigated = true;
				window.location.href = destination;
			}

			var timeoutId;
			function onBodyTransitionEnd(e) {
				if (e.target !== document.body) return;
				if (e.propertyName !== 'opacity') return;
				document.body.removeEventListener('transitionend', onBodyTransitionEnd);
				if (typeof timeoutId !== 'undefined') clearTimeout(timeoutId);
				go();
			}

			document.body.addEventListener('transitionend', onBodyTransitionEnd);
			timeoutId = setTimeout(function () {
				document.body.removeEventListener('transitionend', onBodyTransitionEnd);
				go();
			}, FADE_OUT_MS + FADE_OUT_BUFFER_MS);
		},
		true
	);

	document.addEventListener('submit', function (event) {
		var form = event.target;
		if (!form || form.tagName !== 'FORM') return;
		if (event.defaultPrevented) return;
		if (form.target && form.target !== '_self') return;

		html.classList.remove(READY_CLASS);
		html.classList.add(LEAVING_CLASS);
	});
})();
