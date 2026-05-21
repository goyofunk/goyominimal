/**
 * unicorn-loader.js  (goyoartdark)
 *
 * Unicorn Studio WebGL 씬을 .mainbigslider 안에 오버레이로 주입한다.
 * 원본의 UnicornStudio.init() 패턴·런타임 로드 방식을 그대로 유지하고
 * 히어로 fold 감지(goyo-hero-past-fold) 만 제거했다.
 *
 * PHP 의 wp_localize_script() 가 window.goyoUnicornConfig 를 주입한다:
 * {
 *   effect        : 'default' | 'soft-particles' | 'none',
 *   darkMode      : 'screen' | 'none',
 *   projectId     : string,   // 기본 Unicorn 프로젝트 ID
 *   softProjectId : string,   // Soft Particles 프로젝트 ID
 * }
 */
( function () {
	'use strict';

	function onReady( fn ) {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}

	onReady( function bootstrap() {

		/* ── 0. 설정 읽기 ───────────────────────────────────────── */
		if ( typeof goyoUnicornConfig === 'undefined' ) {
			return;
		}
		var cfg           = goyoUnicornConfig;
		var effect        = ( cfg.effect        || 'default' ).toString().trim();
		var darkMode      = ( cfg.darkMode      || 'screen'  ).toString().trim();
		var projectId     = ( cfg.projectId     || ''        ).toString().trim();
		var softProjectId = ( cfg.softProjectId || ''        ).toString().trim();

		if ( 'none' === effect ) {
			return;
		}

		var activeId = ( 'soft-particles' === effect && softProjectId )
			? softProjectId
			: projectId;

		if ( ! activeId ) {
			return;
		}

		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		/* ── 1. 대상: .mainbigslider ────────────────────────────── */
		var slider = document.querySelector( '.mainbigslider' );
		if ( ! slider ) {
			return;
		}

		/* ── 2. 오버레이 div 생성 (#goyo-unicorn-embed) ─────────── */
		if ( slider.querySelector( '#goyo-unicorn-embed' ) ) {
			return; // 중복 방지
		}
		var el = document.createElement( 'div' );
		el.id = 'goyo-unicorn-embed';
		el.setAttribute( 'aria-hidden', 'true' );

		/* 슬라이더를 덮는 절대 오버레이 */
		el.style.cssText = [
			'position:absolute',
			'inset:0',
			'width:100%',
			'height:100%',
			'pointer-events:none',
			'z-index:5',
			'overflow:hidden',
		].join( ';' );

		if ( 'screen' === darkMode ) {
			el.style.mixBlendMode = 'screen';
		}

		/* .mainbigslider 가 static 이면 relative 로 올려야 오버레이가 걸린다 */
		var sliderPos = window.getComputedStyle( slider ).position;
		if ( 'static' === sliderPos ) {
			slider.style.position = 'relative';
		}

		/* 슬라이더 맨 앞에 삽입 */
		slider.insertBefore( el, slider.firstChild );

		/* ── 3. data-us-* 속성 세팅 (원본 방식 그대로) ─────────── */
		var isCoarsePointer = window.matchMedia && window.matchMedia( '(hover: none), (pointer: coarse)' ).matches;
		var targetScale = isCoarsePointer ? 0.72 : 0.86;
		var targetDpi   = isCoarsePointer ? 1    : 1.2;
		var targetFps   = isCoarsePointer ? 30   : 45;

		el.setAttribute( 'data-us-project',    activeId );
		el.setAttribute( 'data-us-production', '1' );
		el.setAttribute( 'data-us-scale',      String( targetScale ) );
		el.setAttribute( 'data-us-dpi',        String( targetDpi ) );
		el.setAttribute( 'data-us-fps',        String( targetFps ) );

		/* ── 4. 런타임 로드 + UnicornStudio.init() (원본 패턴) ──── */
		var activeScene  = null;
		var runtimeState = 'none';

		function setBodyUnicornRunning( running ) {
			if ( document.body && document.body.classList ) {
				document.body.classList.toggle( 'goyo-unicorn-active', !! running );
			}
		}

		function normalizeScenes( raw ) {
			if ( ! raw ) { return []; }
			return Array.isArray( raw ) ? raw : [ raw ];
		}

		function findSceneForEmbed( scenesRaw ) {
			var arr = normalizeScenes( scenesRaw );
			for ( var i = 0; i < arr.length; i++ ) {
				var scene = arr[ i ];
				if ( ! scene || ! scene.element ) { continue; }
				if ( scene.element === el ) { return scene; }
				if ( typeof scene.element.contains === 'function' && scene.element.contains( el ) ) { return scene; }
				if ( typeof el.contains === 'function' && el.contains( scene.element ) ) { return scene; }
			}
			return arr.length ? arr[ 0 ] : null;
		}

		function applyCanvasTransparent() {
			var canvases = el.querySelectorAll( 'canvas' );
			for ( var i = 0; i < canvases.length; i++ ) {
				canvases[ i ].style.background = 'transparent';
			}
		}

		function destroyActiveScene() {
			if ( activeScene && typeof activeScene.destroy === 'function' ) {
				try { activeScene.destroy(); } catch ( e ) {}
			}
			activeScene = null;
			el.classList.remove( 'is-ready' );
			setBodyUnicornRunning( false );
			window.goyoUnicornRunning = false;
		}

		function startScene() {
			if ( typeof UnicornStudio === 'undefined' || ! UnicornStudio.init ) { return; }
			if ( activeScene ) { return; }
			el.classList.remove( 'is-ready' );

			var finalize = function ( scenesRaw ) {
				activeScene = findSceneForEmbed( scenesRaw );
				if ( ! activeScene ) { return; }
				window.goyoUnicornRunning = true;
				setBodyUnicornRunning( true );
				applyCanvasTransparent();
				window.requestAnimationFrame( function () {
					window.requestAnimationFrame( function () {
						if ( ! activeScene ) { destroyActiveScene(); return; }
						el.classList.add( 'is-ready' );
					} );
				} );
			};

			var p = UnicornStudio.init( { scale: targetScale, dpi: targetDpi } );
			if ( p && typeof p.then === 'function' ) {
				p.then( finalize ).catch( function () {} );
			} else {
				finalize();
			}
		}

		function loadRuntimeThenStart() {
			if ( runtimeState === 'ready' )   { startScene(); return; }
			if ( runtimeState === 'loading' )  { return; }
			runtimeState = 'loading';
			window.goyoUnicornLoaderDone = true;

			var s    = document.createElement( 'script' );
			s.async  = true;
			s.src    = 'https://cdn.jsdelivr.net/gh/hiunicornstudio/unicornstudio.js@v2.1.8/dist/unicornStudio.umd.js';
			s.onerror = function () {
				runtimeState = 'none';
				if ( window.console && typeof window.console.error === 'function' ) {
					window.console.error( '[goyoartdark] Unicorn runtime load failed:', s.src );
				}
			};
			s.onload = function () {
				runtimeState = 'ready';
				window.goyoUnicornRuntimeReady = true;
				startScene();
			};
			( document.head || document.body ).appendChild( s );
		}

		loadRuntimeThenStart();

		/* ── 5. 공개 API ─────────────────────────────────────────── */
		window.goyoUnicorn = {
			destroy : destroyActiveScene,
			config  : cfg,
		};
	} );
}() );
