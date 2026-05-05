/**
 * html.goyo-reveal-tiles 일 때: 카테고리/홈 .goyo-col-reveal + 공통 .fadeup — 뷰포트 진입 시 아래→위 + 페이드( board.css )
 */
(function () {
	"use strict";
	/* 기존 reveal 대상 + 공통 fadeup 클래스 */
	var sel = ".post-list-wrap .row .goyo-col-reveal, .goyo-main-gallery-grid .goyo-main-gallery-item.goyo-col-reveal, .fadeup";
	if ( ! document.documentElement.classList.contains( "goyo-reveal-tiles" ) ) {
		return;
	}
	if ( typeof window.IntersectionObserver === "undefined" ) {
		if ( document.readyState === "loading" ) {
			document.addEventListener( "DOMContentLoaded", function () {
				document.querySelectorAll( sel ).forEach( function ( el ) {
					el.classList.add( "goyo-col-reveal--in" );
				} );
			} );
		} else {
			document.querySelectorAll( sel ).forEach( function ( el ) {
				el.classList.add( "goyo-col-reveal--in" );
			} );
		}
		return;
	}
	function run() {
		var nodes = document.querySelectorAll( sel );
		if ( ! nodes.length ) {
			return;
		}
		var io = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( "goyo-col-reveal--in" );
						io.unobserve( entry.target );
					}
				} );
			},
			{ root: null, rootMargin: "0px 0px 0px 0px", threshold: 0.01 }
		);
		nodes.forEach( function ( el ) {
			io.observe( el );
		} );
	}
	if ( document.readyState === "loading" ) {
		document.addEventListener( "DOMContentLoaded", run );
	} else {
		run();
	}
})();
