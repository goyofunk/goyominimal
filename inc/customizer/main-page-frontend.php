<?php
/**
 * 사용자 정의 '메인페이지' — 프론트 출력 (goyominimal 고유).
 *
 * goyominimal 의 슬로건(.mainhero-title)·보조문구(.mainhero-lead)는 MetaSlider 캡션 마크업이라
 * 부모의 히어로 숏코드 렌더러를 거치지 않는다. 커스터마이저 값을 CSS 변수로 직접 주입해야
 * front-page.css 의 var(--goyo-slogan-*, --goyo-subtext-*) 가 동작한다.
 *
 * 부모(goyobase/inc/customizer/main-page-frontend.php)가 갖는 것: 'bottom-left' body class·partial 렌더러.
 * 여기서는 'center' 일 때의 body class 만 추가한다( front-page.css 가 --center 클래스를 사용 ).
 *
 * @package WordPress
 * @subpackage Goyoartdark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 커스터마이저 히어로 설정 → CSS 변수를 goyoartdark-front-page 핸들에 인라인 주입.
 * transport=refresh 이므로 별도 JS 없이 미리보기 새로고침만으로 반영된다.
 *
 * @return void
 */
function goyoartdark_hero_customizer_inline_css() {
	if ( ! is_front_page() && ! is_customize_preview() ) {
		return;
	}
	if ( ! wp_style_is( 'goyoartdark-front-page', 'enqueued' ) ) {
		return;
	}

	// ── 메인 슬로건 ─────────────────────────────────────────────
	$slogan_font_family = get_theme_mod( 'goyo_hero_slogan_font_family', '"Poppins", sans-serif' );
	$slogan_font_size   = get_theme_mod( 'goyo_hero_slogan_font_size', 'clamp(1.9rem, 4.5vw, 4.2rem)' );
	$slogan_font_weight = get_theme_mod( 'goyo_hero_slogan_font_weight', '700' );
	$slogan_color       = get_theme_mod( 'goyo_hero_slogan_color', goyo_default( 'hero_slogan_color', '#fff157' ) );
	$slogan_opacity     = get_theme_mod( 'goyo_hero_slogan_opacity', 1 );

	// ── 보조문구 ─────────────────────────────────────────────────
	$subtext_font_family = get_theme_mod( 'goyo_hero_subtext_font_family', '' );
	$subtext_font_size   = get_theme_mod( 'goyo_hero_subtext_font_size', 'clamp(0.9rem, 1.8vw, 1.1rem)' );
	$subtext_color       = get_theme_mod( 'goyo_hero_subtext_color', '#f2f2f0' );
	$subtext_opacity     = get_theme_mod( 'goyo_hero_subtext_opacity', goyo_default( 'hero_subtext_opacity', 1 ) );

	// CSS 변수 빌드 — 빈 값은 제외
	$vars = array();
	if ( $slogan_font_family ) {
		$vars[] = '--goyo-slogan-font-family: ' . esc_attr( $slogan_font_family );
	}
	if ( $slogan_font_size ) {
		$vars[] = '--goyo-slogan-font-size: ' . esc_attr( $slogan_font_size );
	}
	if ( $slogan_font_weight ) {
		$vars[] = '--goyo-slogan-font-weight: ' . esc_attr( $slogan_font_weight );
	}
	if ( $slogan_color ) {
		$vars[] = '--goyo-slogan-color: ' . esc_attr( $slogan_color );
	}
	$vars[] = '--goyo-slogan-opacity: ' . floatval( $slogan_opacity );

	if ( $subtext_font_family ) {
		$vars[] = '--goyo-subtext-font-family: ' . esc_attr( $subtext_font_family );
	}
	if ( $subtext_font_size ) {
		$vars[] = '--goyo-subtext-font-size: ' . esc_attr( $subtext_font_size );
	}
	if ( $subtext_color ) {
		$vars[] = '--goyo-subtext-color: ' . esc_attr( $subtext_color );
	}
	$vars[] = '--goyo-subtext-opacity: ' . floatval( $subtext_opacity );

	if ( empty( $vars ) ) {
		return;
	}

	wp_add_inline_style(
		'goyoartdark-front-page',
		'.mainhero{' . implode( ';', $vars ) . ';}'
	);

	// ── 메인 슬로건 배경색 없애기 / 배경색 적용 ──────────────────
	if ( get_theme_mod( 'goyo_hero_caption_no_bg', true ) ) {
		wp_add_inline_style(
			'goyoartdark-front-page',
			'.mainhero .metaslider .caption{background-color:transparent !important;padding:0 0 2vw 0 !important;}'
		);
	} else {
		$caption_bg_hex   = get_theme_mod( 'goyo_hero_caption_bg_color', '#000000' );
		$caption_bg_alpha = floatval( get_theme_mod( 'goyo_hero_caption_bg_alpha', 0.3 ) );

		// HEX → RGB 변환
		$caption_bg_hex = ltrim( $caption_bg_hex, '#' );
		if ( 3 === strlen( $caption_bg_hex ) ) {
			$caption_bg_hex = $caption_bg_hex[0] . $caption_bg_hex[0]
				. $caption_bg_hex[1] . $caption_bg_hex[1]
				. $caption_bg_hex[2] . $caption_bg_hex[2];
		}
		$r = hexdec( substr( $caption_bg_hex, 0, 2 ) );
		$g = hexdec( substr( $caption_bg_hex, 2, 2 ) );
		$b = hexdec( substr( $caption_bg_hex, 4, 2 ) );

		$rgba_value = 'rgba(' . (int) $r . ', ' . (int) $g . ', ' . (int) $b . ', ' . $caption_bg_alpha . ')';

		wp_add_inline_style(
			'goyoartdark-front-page',
			'.mainhero .metaslider .caption{background-color:' . $rgba_value . ' !important;}'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'goyoartdark_hero_customizer_inline_css', 201 );

/**
 * 보조문구 스타일 커스터마이저 값을 CSS 변수로 주입한다.
 * (.mainhero-lead 에 --goyo-subtext-* 변수로 적용 — 블록 에디터 컨텍스트·공통 핸들 포함)
 *
 * @return void
 */
function goyoartdark_hero_subtext_style_css_var() {
	if ( ! goyoartdark_is_main_page_blocks_context() && ! is_front_page() && ! is_customize_preview() ) {
		return;
	}

	$hex_to_rgba = static function ( $hex, $opacity ) {
		$hex = sanitize_hex_color( (string) $hex );
		if ( ! $hex ) {
			return '';
		}
		$opacity = max( 0, min( 1, (float) $opacity ) );
		$hex     = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		if ( $opacity >= 0.999 ) {
			return '#' . $hex;
		}
		return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . round( $opacity, 2 ) . ')';
	};

	$ff = get_theme_mod( 'goyo_hero_subtext_font_family', '' );
	$ff = is_string( $ff ) ? goyoartdark_sanitize_hero_font_family( trim( $ff ) ) : '';
	$sz = get_theme_mod( 'goyo_hero_subtext_font_size', 'clamp(0.9rem, 1.8vw, 1.1rem)' );
	$sz = is_string( $sz ) ? goyoartdark_sanitize_hero_css_value( trim( $sz ) ) : '';
	$cl = sanitize_hex_color( (string) get_theme_mod( 'goyo_hero_subtext_color', '#f2f2f0' ) );
	$op = goyoartdark_sanitize_hero_opacity( get_theme_mod( 'goyo_hero_subtext_opacity', goyo_default( 'hero_subtext_opacity', 1 ) ) );
	$cl = $hex_to_rgba( $cl, $op );

	$vars = array();
	if ( '' !== $ff ) {
		$vars[] = '--goyo-subtext-font-family: ' . $ff;
	}
	if ( '' !== $sz ) {
		$vars[] = '--goyo-subtext-font-size: ' . $sz;
	}
	if ( '' !== $cl ) {
		$vars[] = '--goyo-subtext-color: ' . $cl;
	}

	if ( empty( $vars ) ) {
		return;
	}

	$css = '.mainhero-lead { ' . implode( '; ', $vars ) . '; }';
	wp_add_inline_style( 'goyoartdark-style', $css );
	if ( wp_style_is( 'goyoartdark-front-page', 'enqueued' ) ) {
		wp_add_inline_style( 'goyoartdark-front-page', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'goyoartdark_hero_subtext_style_css_var', 36 );

/**
 * 히어로 정렬 'center' body class 추가 — front-page.css 가 .goyo-hero-layout--center 를 사용한다.
 * 부모는 'bottom-left' 만 추가하므로( goyobase/inc/customizer/main-page-frontend.php ),
 * 'center' 를 명시적으로 추가해야 bottom-left 가 저장된 적 있어도 CSS 로 덮어씌울 수 있다.
 *
 * @param string[] $classes 기존 body 클래스 배열.
 * @return string[]
 */
function goyoartdark_hero_layout_center_body_class( $classes ) {
	if ( ! is_front_page() && ! is_customize_preview() ) {
		return $classes;
	}
	if ( 'bottom-left' !== get_theme_mod( 'goyo_hero_layout', 'center' ) ) {
		$classes[] = 'goyo-hero-layout--center';
	}
	return $classes;
}
add_filter( 'body_class', 'goyoartdark_hero_layout_center_body_class' );
