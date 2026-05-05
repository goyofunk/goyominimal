/**
 * Lenis 부드러운 스크롤: 접근성(시스템에서 모션 감소 선택 시 미적용)
 */
( function () {
	'use strict';

	if ( typeof window.Lenis === 'undefined' ) {
		return;
	}

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	// 디버깅·연동 시 window.goyoLenis 로 스크롤 제어 가능
	window.goyoLenis = new window.Lenis( {
		autoRaf: true,
		anchors: true,
	} );

}() );
