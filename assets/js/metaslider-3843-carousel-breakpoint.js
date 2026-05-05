/**
 * 메타슬라이더 #metaslider_3843 캐러셀: 520px 미만 2장·그 외 4장, 뷰포트 820px 미만 슬라이드 간격 10px(PC 20px).
 * FlexSlider 수학(doMath)과 메타슬라이더 인라인 CSS(!important 20px)를 함께 맞추기 위해 vars·슬라이드 margin 동기화.
 */
(function () {
	'use strict';

	var SELECTOR = '#metaslider_3843';
	var MQ_NARROW = '(max-width: 519px)';
	var MQ_COMPACT_MARGIN = '(max-width: 819px)';
	var WIDE = { minItems: 4, maxItems: 4, itemWidth: 280 };
	var NARROW = { minItems: 2, maxItems: 2, itemWidth: 180 };
	var MARGIN_DESKTOP = 20;
	var MARGIN_TOUCH = 10;

	function getJQuery() {
		return typeof window.jQuery !== 'undefined' ? window.jQuery : null;
	}

	function isNarrowViewport() {
		return window.matchMedia(MQ_NARROW).matches;
	}

	function isCompactMarginViewport() {
		return window.matchMedia(MQ_COMPACT_MARGIN).matches;
	}

	function applyCarouselBreakpoint() {
		var $ = getJQuery();
		if (!$) {
			return;
		}
		var $root = $(SELECTOR);
		if (!$root.length) {
			return;
		}
		var slider = $root.data('flexslider');
		if (!slider || !slider.vars) {
			return;
		}
		if (slider.animating) {
			window.setTimeout(applyCarouselBreakpoint, 60);
			return;
		}

		var narrow = isNarrowViewport();
		var target = narrow ? NARROW : WIDE;
		var targetMargin = isCompactMarginViewport() ? MARGIN_TOUCH : MARGIN_DESKTOP;

		if (
			slider.vars.minItems === target.minItems &&
			slider.vars.maxItems === target.maxItems &&
			slider.vars.itemWidth === target.itemWidth &&
			slider.vars.itemMargin === targetMargin
		) {
			return;
		}

		slider.vars.minItems = target.minItems;
		slider.vars.maxItems = target.maxItems;
		slider.vars.itemWidth = target.itemWidth;
		slider.vars.itemMargin = targetMargin;

		slider.doMath();

		if (typeof slider.currentSlide === 'number' && typeof slider.last === 'number') {
			if (slider.currentSlide > slider.last) {
				slider.currentSlide = slider.last;
				slider.animatingTo = slider.last;
			}
		}

		slider.slides.css({ width: slider.computedW, marginRight: slider.computedM });
		slider.update(slider.pagingCount);
		slider.setProps();
	}

	function bindResize() {
		var t = null;
		function schedule() {
			if (t !== null) {
				window.clearTimeout(t);
			}
			t = window.setTimeout(function () {
				t = null;
				applyCarouselBreakpoint();
			}, 120);
		}
		window.addEventListener('resize', schedule, { passive: true });
		window.addEventListener('orientationchange', schedule, { passive: true });
		if (typeof window.matchMedia === 'function') {
			var mqlNarrow = window.matchMedia(MQ_NARROW);
			if (typeof mqlNarrow.addEventListener === 'function') {
				mqlNarrow.addEventListener('change', schedule);
			} else if (typeof mqlNarrow.addListener === 'function') {
				mqlNarrow.addListener(schedule);
			}
			var mqlMargin = window.matchMedia(MQ_COMPACT_MARGIN);
			if (typeof mqlMargin.addEventListener === 'function') {
				mqlMargin.addEventListener('change', schedule);
			} else if (typeof mqlMargin.addListener === 'function') {
				mqlMargin.addListener(schedule);
			}
		}
	}

	function initWhenReady() {
		var $ = getJQuery();
		if (!$) {
			return;
		}

		$(document).on('metaslider/initialized', function (e, selector) {
			if (selector === SELECTOR) {
				applyCarouselBreakpoint();
			}
		});

		bindResize();

		function poll() {
			var $el = $(SELECTOR);
			if ($el.length && $el.data('flexslider')) {
				applyCarouselBreakpoint();
				return true;
			}
			return false;
		}

		if (!poll()) {
			var n = 0;
			var id = window.setInterval(function () {
				n += 1;
				if (poll() || n >= 100) {
					window.clearInterval(id);
				}
			}, 50);
		}
	}

	if (typeof window.jQuery !== 'undefined') {
		window.jQuery(initWhenReady);
	} else {
		window.setTimeout(initWhenReady, 100);
	}
})();
