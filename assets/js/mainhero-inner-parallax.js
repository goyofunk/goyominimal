/**
 * Kinetic 히어로: ① 배경( .mainhero::before, CSS 변수) ② .mainhero-inner
 *    동일 target(이동+틸트), 배경 lerp 는 더 낮아 글자보다 시간차로 따라옴
 * [data-mainhero-inner-parallax] 또는 .mainhero.mainhero-wrapper(FSE 저장 시 data 속성 누락 대비) — prefers-reduced-motion 이 아닐 때만
 */
( function () {
	'use strict';

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	var root = document.querySelector( '[data-mainhero-inner-parallax]' ) || document.querySelector( '.mainhero.mainhero-wrapper' );
	if ( ! root ) {
		return;
	}
	// Unicorn WebGL과 동시 실행하면 GPU/메인스레드 부하가 커져 스크롤이 끊길 수 있어 비활성화.
	var unicornRoot = document.getElementById( 'goyo-unicorn-root' );
	if ( unicornRoot ) {
		var unicornProjectId = ( unicornRoot.getAttribute( 'data-us-project' ) || '' ).trim();
		if ( unicornProjectId ) {
			return;
		}
	}

	var inner = root.querySelector( '.mainhero-inner' );
	if ( ! inner ) {
		return;
	}

	inner.style.setProperty( 'z-index', '10', 'important' );
	inner.style.setProperty( 'position', 'relative', 'important' );
	inner.classList.add( 'mainhero-inner--parallax' );

	var maxTranslatePx = 22;
	var maxTiltDeg = 3.2;
	/* 낮을수록 반응이 늦음 — 배경이 글자보다 “늦게” 붙는 느낌 */
	var lerpInner = 0.15;
	var lerpBg = 0.05;
	var curInTx = 0;
	var curInTy = 0;
	var curInRrx = 0;
	var curInRry = 0;
	var curBgTx = 0;
	var curBgTy = 0;
	var curBgRrx = 0;
	var curBgRry = 0;
	var normX = 0;
	var normY = 0;
	var hasPointerInHero = false;
	var isTicking = false;

	/* 스크롤로 히어로 고정 레이어가 숨김 상태일 때 관성 lerping·포인터 상태를 초기화(유니콘 미사용 히어만) */
	function resetPhysicsIfPastFold() {
		if ( ! document.body || ! document.body.classList.contains( 'goyo-hero-past-fold' ) ) {
			return false;
		}
		hasPointerInHero = false;
		inner.style.transform = '';
		clearBgVars();
		curInTx = 0;
		curInTy = 0;
		curInRrx = 0;
		curInRry = 0;
		curBgTx = 0;
		curBgTy = 0;
		curBgRrx = 0;
		curBgRry = 0;
		normX = 0;
		normY = 0;
		return true;
	}

	/* position:fixed 히어로는 rect 가 항상 뷰포트 전체 — 실제로 히어로 “위”에 있을 때만(위에 덮인 .container 는 제외) */
	function isPointerOverHero( cx, cy ) {
		if ( document.elementFromPoint ) {
			var el = document.elementFromPoint( cx, cy );
			if ( ! el ) {
				return false;
			}
			return root === el || root.contains( el );
		}
		var r = root.getBoundingClientRect();
		return cx >= r.left && cx <= r.right && cy >= r.top && cy <= r.bottom;
	}

	function onDocPointer( e ) {
		if ( resetPhysicsIfPastFold() ) {
			return;
		}
		if ( ! isPointerOverHero( e.clientX, e.clientY ) ) {
			hasPointerInHero = false;
			ensureFrame();
			return;
		}
		var r = root.getBoundingClientRect();
		var w = r.width;
		var h = r.height;
		if ( w < 1 || h < 1 ) {
			return;
		}
		normX = ( ( e.clientX - r.left ) / w - 0.5 ) * 2;
		normY = ( ( e.clientY - r.top ) / h - 0.5 ) * 2;
		if ( normX < -1 ) {
			normX = -1;
		} else if ( normX > 1 ) {
			normX = 1;
		}
		if ( normY < -1 ) {
			normY = -1;
		} else if ( normY > 1 ) {
			normY = 1;
		}
		hasPointerInHero = true;
		ensureFrame();
	}

	function ensureFrame() {
		if ( isTicking ) {
			return;
		}
		isTicking = true;
		window.requestAnimationFrame( step );
	}

	function clearBgVars() {
		root.style.removeProperty( '--goyo-hero-bg-tx' );
		root.style.removeProperty( '--goyo-hero-bg-ty' );
		root.style.removeProperty( '--goyo-hero-bg-rx' );
		root.style.removeProperty( '--goyo-hero-bg-ry' );
	}

	function step() {
		isTicking = false;
		if ( resetPhysicsIfPastFold() ) {
			return;
		}
		var tgx = hasPointerInHero ? normX * maxTranslatePx : 0;
		var tgy = hasPointerInHero ? normY * maxTranslatePx : 0;
		var tgrx = hasPointerInHero ? -normY * maxTiltDeg : 0;
		var tgry = hasPointerInHero ? normX * maxTiltDeg : 0;

		curInTx += ( tgx - curInTx ) * lerpInner;
		curInTy += ( tgy - curInTy ) * lerpInner;
		curInRrx += ( tgrx - curInRrx ) * lerpInner;
		curInRry += ( tgry - curInRry ) * lerpInner;

		curBgTx += ( tgx - curBgTx ) * lerpBg;
		curBgTy += ( tgy - curBgTy ) * lerpBg;
		curBgRrx += ( tgrx - curBgRrx ) * lerpBg;
		curBgRry += ( tgry - curBgRry ) * lerpBg;

		inner.style.transform =
			'perspective(1200px) translate3d(' + curInTx + 'px,' + curInTy + 'px,1px) rotateX(' + curInRrx + 'deg) rotateY(' + curInRry + 'deg)';

		root.style.setProperty( '--goyo-hero-bg-tx', curBgTx + 'px' );
		root.style.setProperty( '--goyo-hero-bg-ty', curBgTy + 'px' );
		root.style.setProperty( '--goyo-hero-bg-rx', curBgRrx + 'deg' );
		root.style.setProperty( '--goyo-hero-bg-ry', curBgRry + 'deg' );

		var innerStill =
			! hasPointerInHero &&
			Math.abs( curInTx ) < 0.06 &&
			Math.abs( curInTy ) < 0.06 &&
			Math.abs( curInRrx ) < 0.03 &&
			Math.abs( curInRry ) < 0.03;
		var bgStill =
			! hasPointerInHero &&
			Math.abs( curBgTx ) < 0.06 &&
			Math.abs( curBgTy ) < 0.06 &&
			Math.abs( curBgRrx ) < 0.03 &&
			Math.abs( curBgRry ) < 0.03;
		if ( innerStill && bgStill ) {
			inner.style.transform = '';
			clearBgVars();
			curInTx = 0;
			curInTy = 0;
			curInRrx = 0;
			curInRry = 0;
			curBgTx = 0;
			curBgTy = 0;
			curBgRrx = 0;
			curBgRry = 0;
			return;
		}
		isTicking = true;
		window.requestAnimationFrame( step );
	}

	document.addEventListener( 'pointermove', onDocPointer, { passive: true } );
	window.addEventListener( 'scroll', resetPhysicsIfPastFold, { passive: true } );

	if ( root.getBoundingClientRect().height > 0 && root.getBoundingClientRect().top < window.innerHeight && root.getBoundingClientRect().bottom > 0 ) {
		ensureFrame();
	}
}() );
