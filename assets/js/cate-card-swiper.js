/**
 * 메인페이지 카테고리 카드(쿼리 루프) → Swiper 자동 슬라이드.
 *
 * 페이지 편집기의 쿼리 루프 마크업( div.cateCard > ul.cateCardGrid > li )을
 * Swiper 4 구조( .swiper-container > .swiper-wrapper > .swiper-slide )로 변환한 뒤 초기화한다.
 * Swiper 본체는 부모 테마(goyobase)가 전 페이지에 로드한다( goyobase-swiper 의존 ).
 */
(function () {
	"use strict";

	function initCateCardSwiper() {
		if (typeof Swiper === "undefined") {
			return;
		}
		var lists = document.querySelectorAll(".cateCard .cateCardGrid");
		Array.prototype.forEach.call(lists, function (list) {
			if (list.closest(".swiper-container")) {
				return; /* 이미 변환됨 */
			}
			if (list.children.length < 2) {
				return;
			}

			/* ul 을 .swiper-container 로 감싸고 WP 그리드 레이아웃 클래스를 걷어낸다 */
			var container = document.createElement("div");
			container.className = "swiper-container cateCardSwiper";
			list.parentNode.insertBefore(container, list);
			container.appendChild(list);

			Array.prototype.slice.call(list.classList).forEach(function (cls) {
				if (cls === "is-layout-grid" || cls === "columns-3" || cls.indexOf("wp-container-") === 0 || cls.indexOf("wp-block-post-template-is-layout-") === 0) {
					list.classList.remove(cls);
				}
			});
			list.classList.add("swiper-wrapper");

			Array.prototype.forEach.call(list.children, function (li) {
				li.classList.add("swiper-slide");
			});

			var pagination = document.createElement("div");
			pagination.className = "swiper-pagination";
			container.appendChild(pagination);

			var prevEl = document.createElement("div");
			prevEl.className = "swiper-button-prev cateCard-prev";
			container.parentNode.appendChild(prevEl);

			var nextEl = document.createElement("div");
			nextEl.className = "swiper-button-next cateCard-next";
			container.parentNode.appendChild(nextEl);

			/* Swiper 4 breakpoints 는 max-width 기준: ≤600 → 1장, ≤1024 → 2장, 그 외 3장 */
			new Swiper(container, {
				slidesPerView: 3,
				spaceBetween: 30,
				loop: true,
				speed: 800,
				observer: true,
				observeParents: true,
				autoplay: {
					delay: 3000,
					disableOnInteraction: false,
				},
				pagination: {
					el: pagination,
					clickable: true,
				},
				navigation: {
					prevEl: prevEl,
					nextEl: nextEl,
				},
				breakpoints: {
					600: { slidesPerView: 1, spaceBetween: 16 },
					1024: { slidesPerView: 2, spaceBetween: 20 },
				},
			});
		});
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", initCateCardSwiper);
	} else {
		initCateCardSwiper();
	}
})();
