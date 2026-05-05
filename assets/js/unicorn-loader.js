/**
 * Unicorn Studio UMD 지연 로드 후 UnicornStudio.init().
 * body.goyo-hero-past-fold(히어로 스페이서 아래)에서는 씬 destroy 로 WebGL 렌더 중단, 폴드 복귀 시 재 init. 전역 표시/CSS는 front-page 고정 레이어에 맡김.
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
		var el = document.getElementById( 'goyo-unicorn-root' );
		if ( ! el || typeof goyoUnicornConfig === 'undefined' ) {
			return;
		}

		var id = ( goyoUnicornConfig.projectId || '' ).toString().trim();
		if ( ! id ) {
			el.setAttribute( 'data-us-project', '' );
			return;
		}

		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		el.setAttribute( 'data-us-project', id );

		var isCoarsePointer = window.matchMedia && window.matchMedia( '(hover: none), (pointer: coarse)' ).matches;
		var targetScale = isCoarsePointer ? 0.72 : 0.86;
		var targetDpi = isCoarsePointer ? 1 : 1.2;
		var targetFps = isCoarsePointer ? 30 : 45;
		el.setAttribute( 'data-us-production', '1' );
		el.setAttribute( 'data-us-scale', String( targetScale ) );
		el.setAttribute( 'data-us-dpi', String( targetDpi ) );
		el.setAttribute( 'data-us-fps', String( targetFps ) );

		var activeScene = null;
		var runtimeState = 'none';

		function setBodyUnicornRunning( running ) {
			if ( document.body && document.body.classList ) {
				document.body.classList.toggle( 'goyo-unicorn-active', !! running );
			}
		}

		function isHeroPastFold() {
			return document.body && document.body.classList.contains( 'goyo-hero-past-fold' );
		}

		function normalizeScenes( raw ) {
			if ( ! raw ) {
				return [];
			}
			if ( Array.isArray( raw ) ) {
				return raw;
			}
			return [ raw ];
		}

		function findSceneForEmbed( scenesRaw ) {
			var arr = normalizeScenes( scenesRaw );
			var i = 0;
			for ( i = 0; i < arr.length; i++ ) {
				var scene = arr[ i ];
				if ( ! scene || ! scene.element ) {
					continue;
				}
				if ( scene.element === el ) {
					return scene;
				}
				if ( typeof scene.element.contains === 'function' && scene.element.contains( el ) ) {
					return scene;
				}
				if ( typeof el.contains === 'function' && el.contains( scene.element ) ) {
					return scene;
				}
			}
			return arr.length ? arr[ 0 ] : null;
		}

		function destroyActiveScene() {
			if ( activeScene && typeof activeScene.destroy === 'function' ) {
				try {
					activeScene.destroy();
				} catch ( eDestroy ) {}
			}
			activeScene = null;
			el.classList.remove( 'is-ready' );
			setBodyUnicornRunning( false );
			window.goyoUnicornRunning = false;
		}

		function destroySpawnedScene( scenesRaw ) {
			var orphaned = findSceneForEmbed( scenesRaw );
			if ( orphaned && typeof orphaned.destroy === 'function' ) {
				try {
					orphaned.destroy();
				} catch ( eOrphan ) {}
			}
		}

		function applyCanvasTransparent() {
			var canv = el.querySelectorAll( 'canvas' );
			var i = 0;
			for ( i = 0; i < canv.length; i++ ) {
				canv[ i ].style.background = 'transparent';
			}
		}

		function startSceneIfEligible() {
			if ( typeof UnicornStudio === 'undefined' || ! UnicornStudio.init ) {
				return;
			}
			if ( isHeroPastFold() ) {
				destroyActiveScene();
				return;
			}
			if ( activeScene ) {
				return;
			}
			el.classList.remove( 'is-ready' );
			var finalize = function ( scenesRaw ) {
				if ( isHeroPastFold() ) {
					destroySpawnedScene( scenesRaw );
					return;
				}
				activeScene = findSceneForEmbed( scenesRaw );
				if ( ! activeScene ) {
					return;
				}
				window.goyoUnicornRunning = true;
				setBodyUnicornRunning( true );
				applyCanvasTransparent();
				window.requestAnimationFrame( function () {
					window.requestAnimationFrame( function () {
						if ( isHeroPastFold() || ! activeScene ) {
							destroyActiveScene();
							return;
						}
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

		function ensureRuntimeThenSync() {
			if ( runtimeState === 'ready' ) {
				if ( ! isHeroPastFold() ) {
					startSceneIfEligible();
				}
				return;
			}
			if ( runtimeState === 'loading' ) {
				return;
			}
			runtimeState = 'loading';
			window.goyoUnicornLoaderDone = true;

			var s = document.createElement( 'script' );
			s.async = true;
			s.src = 'https://cdn.jsdelivr.net/gh/hiunicornstudio/unicornstudio.js@v2.1.8/dist/unicornStudio.umd.js';
			s.onerror = function () {
				runtimeState = 'none';
				if ( window.console && typeof window.console.error === 'function' ) {
					window.console.error( '[goyoartdark] Unicorn runtime load failed:', s.src );
				}
			};
			s.onload = function () {
				runtimeState = 'ready';
				window.goyoUnicornRuntimeReady = true;
				if ( ! isHeroPastFold() ) {
					startSceneIfEligible();
				}
			};
			( document.head || document.body ).appendChild( s );
		}

		function syncFoldVisibility() {
			if ( isHeroPastFold() ) {
				destroyActiveScene();
				return;
			}
			ensureRuntimeThenSync();
		}

		if ( typeof MutationObserver !== 'undefined' && document.body ) {
			try {
				new MutationObserver( syncFoldVisibility ).observe( document.body, {
					attributes: true,
					attributeFilter: [ 'class' ],
				} );
			} catch ( eObs ) {}
		}

		window.addEventListener( 'scroll', syncFoldVisibility, { passive: true } );
		window.addEventListener( 'resize', syncFoldVisibility, { passive: true } );
		syncFoldVisibility();
	} );
}() );
