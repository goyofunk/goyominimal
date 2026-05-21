<?php
/**
 * 사용자 정의: '메인페이지' 패널 — '메인페이지 상단'(히어로·Unicorn) + 슬라이드·갤러리.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 메인 슬라이드/갤러리용 카테고리 ID theme_mod 정리.
 *
 * @param mixed $value 원본 값.
 * @return int 유효한 카테고리 term_id 또는 0.
 */
function goyoartdark_sanitize_main_page_category_id( $value ) {
	$tid = absint( $value );
	if ( $tid <= 0 ) {
		return 0;
	}
	$term = get_category( $tid );
	return ( $term && ! is_wp_error( $term ) ) ? $tid : 0;
}

/**
 * 노출 개수(1~50).
 *
 * @param mixed $value 원본 값.
 * @return int
 */
function goyoartdark_sanitize_main_page_count( $value ) {
	$n = absint( $value );
	if ( $n < 1 ) {
		return 1;
	}
	if ( $n > 50 ) {
		return 50;
	}
	return $n;
}

/**
 * 숨기기 체크박스 값.
 *
 * @param mixed $value 원본 값.
 * @return bool 체크 시 true.
 */
function goyoartdark_sanitize_main_page_hide( $value ) {
	return rest_sanitize_boolean( $value );
}

/**
 * 한 줄 노출 개수(1~5) — 메인 3열 스와이프·갤러리 그리드.
 *
 * @param mixed $value 원본 값.
 * @return int
 */
function goyoartdark_sanitize_main_page_per_row( $value ) {
	$n = absint( $value );
	if ( $n < 1 ) {
		return 1;
	}
	if ( $n > 5 ) {
		return 5;
	}
	return $n;
}

/**
 * 썸네일 비율 slug — 커스터마이저·출력 공통.
 *
 * @param mixed $value 원본 값.
 * @return string '4-3'|'1-1'|'3-4'|'16-9'
 */
function goyoartdark_sanitize_main_page_image_ratio_slug( $value ) {
	$allowed = array( '4-3', '1-1', '3-4', '16-9' );
	$v       = sanitize_text_field( (string) $value );
	return in_array( $v, $allowed, true ) ? $v : '4-3';
}


/**
 * 히어로 메인 슬로건·보조문구: 글 편집과 동일 허용 태그( p, br, div, a … ) — wp_kses_post 와 동일.
 *
 * @param mixed $value 원본.
 * @return string
 */
function goyoartdark_sanitize_hero_rich_text( $value ) {
	return wp_kses_post( (string) $value );
}

/**
 * 히어로 버튼 링크 URL.
 *
 * @param mixed $value 원본.
 * @return string
 */
function goyoartdark_sanitize_hero_button_url( $value ) {
	$s = (string) $value;
	$s = trim( $s );
	if ( '' === $s ) {
		return '';
	}
	if ( '#' === $s ) {
		return '#';
	}
	if ( false === strpos( $s, '://' ) && preg_match( '/^(localhost|127(?:\.\d{1,3}){3}|\[::1\])(?::\d{1,5})?(?:\/.*)?$/i', $s ) ) {
		$s = 'http://' . $s;
		return esc_url_raw( $s );
	}
	if ( 0 === strpos( $s, '/' ) || 0 === strpos( $s, './' ) || 0 === strpos( $s, '../' ) || 0 === strpos( $s, '?' ) ) {
		return esc_url_raw( home_url( $s ) );
	}
	if ( false === strpos( $s, '://' ) && 0 !== strpos( $s, 'mailto:' ) && 0 !== strpos( $s, 'tel:' ) ) {
		// "contact/" 같은 상대경로는 현재 경로 기준으로 붙어버리므로 사이트 기준 절대경로로 고정한다.
		return esc_url_raw( home_url( '/' . ltrim( $s, '/' ) ) );
	}
	return esc_url_raw( $s );
}

/**
 * 히어로 콘텐츠 정렬 위치 — 'center'|'bottom-left'
 *
 * @param mixed $value 원본.
 * @return string
 */
function goyoartdark_sanitize_hero_layout( $value ) {
	$allowed = array( 'center', 'bottom-left' );
	$v       = sanitize_text_field( (string) $value );
	return in_array( $v, $allowed, true ) ? $v : 'center';
}

/**
 * 히어로 버튼 — 새 탭에서 열기.
 *
 * @param mixed $value 원본.
 * @return bool
 */
function goyoartdark_sanitize_hero_button_new_tab( $value ) {
	return rest_sanitize_boolean( $value );
}

/**
 * 히어로 폰트 패밀리 — CSS `font-family` 값(스택)만 허용, 인젝션 방지
 *
 * @param mixed $value 원본.
 * @return string
 */
function goyoartdark_sanitize_hero_font_family( $value ) {
	$s = wp_strip_all_tags( (string) $value );
	$s = str_replace( array( "\r", "\n", "\t" ), ' ', $s );
	$s = preg_replace( '/[;{}<>`\\\\@!#%*]/u', '', $s );
	$s = trim( preg_replace( '/\s+/', ' ', $s ) );
	if ( '' === $s ) {
		return '';
	}
	if ( function_exists( 'mb_strlen' ) && mb_strlen( $s, 'UTF-8' ) > 300 ) {
		$s = mb_substr( $s, 0, 300, 'UTF-8' );
	} elseif ( strlen( $s ) > 300 ) {
		$s = substr( $s, 0, 300 );
	}
	return $s;
}

/**
 * 히어로 텍스트용 CSS 값(폰트 크기/색상) 정리.
 * clamp(), var(), rgba() 등 일반 CSS 함수/토큰 입력을 허용하되 선언 탈출 문자는 제거한다.
 *
 * @param mixed $value 원본.
 * @return string
 */
function goyoartdark_sanitize_hero_css_value( $value ) {
	$s = wp_strip_all_tags( (string) $value );
	$s = str_replace( array( "\r", "\n", "\t" ), ' ', $s );
	$s = preg_replace( '/[;{}<>`\\\\]/u', '', $s );
	$s = trim( preg_replace( '/\s+/', ' ', $s ) );
	if ( '' === $s ) {
		return '';
	}
	if ( function_exists( 'mb_strlen' ) && mb_strlen( $s, 'UTF-8' ) > 120 ) {
		$s = mb_substr( $s, 0, 120, 'UTF-8' );
	} elseif ( strlen( $s ) > 120 ) {
		$s = substr( $s, 0, 120 );
	}
	return $s;
}

/**
 * 히어로 색상 알파값(0~1, 소수 2자리) 정리.
 *
 * @param mixed $value 원본.
 * @return float
 */
function goyoartdark_sanitize_hero_opacity( $value ) {
	$n = (float) $value;
	if ( $n < 0 ) {
		$n = 0;
	}
	if ( $n > 1 ) {
		$n = 1;
	}
	return round( $n, 2 );
}

/**
 * 히어로 폰트 굵기(200~800, 100단위) 화이트리스트 정리.
 *
 * @param mixed $value 원본.
 * @return string 허용된 굵기 문자열. 잘못된 값은 기본 700 으로 떨어진다.
 */
function goyoartdark_sanitize_hero_font_weight( $value ) {
	$allowed = array( '200', '300', '400', '500', '600', '700', '800' );
	$value   = is_scalar( $value ) ? (string) $value : '';
	return in_array( $value, $allowed, true ) ? $value : '700';
}

/**
 * 상위 패널 '메인페이지' (히어로·슬라이드/갤러리·Unicorn을 한곳에 묶음). 다른 섹션보다 먼저 등록.
 *
 * @param WP_Customize_Manager $wp_customize 매니저.
 * @return void
 */


/**
 * '메인페이지 상단' 섹션(히어로·배경·Unicorn) — 컨트롤 등록 전에 먼저 만든다.
 *
 * @param WP_Customize_Manager $wp_customize 매니저.
 * @return void
 */
function goyoartdark_register_main_page_top_section( $wp_customize ) {
	if ( $wp_customize->get_section( 'goyoartdark_main_page_top' ) ) {
		return;
	}
	$wp_customize->add_section(
		'goyoartdark_main_page_top',
		array(
			'title'       => __( '메인페이지', 'goyoartdark' ),
			'priority'    => 31,
		)
	);
}
add_action( 'customize_register', 'goyoartdark_register_main_page_top_section', 7 );

/**
 * 메인페이지 상단 — 히어로(문구·버튼·배경).
 *
 * @param WP_Customize_Manager $wp_customize 매니저.
 * @return void
 */
function goyoartdark_customizer_main_page_top_hero( $wp_customize ) {
	$section_id = 'goyoartdark_main_page_top';
	$hero_def = goyoartdark_get_hero_default_strings();

	// ── 메인 슬로건 정렬 ──────────────────────────────────────────
	$wp_customize->add_setting(
		'goyo_hero_layout',
		array(
			'default'           => 'center',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_layout',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_layout',
		array(
			'label'    => __( '메인 슬로건 정렬', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => 'goyo_hero_layout',
			'type'     => 'select',
			'choices'  => array(
				'center'      => __( '센터', 'goyoartdark' ),
				'bottom-left' => __( '왼쪽 아래', 'goyoartdark' ),
			),
			'priority' => 1.5,
		)
	);

	// ── 메인 슬로건 배경색 ───────────────────────────────────────
	$wp_customize->add_setting(
		'goyo_hero_caption_bg_color',
		array(
			'default'           => '#000000',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'goyo_hero_caption_bg_color',
			array(
				'label'    => __( '메인 슬로건 — 배경색', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'goyo_hero_caption_bg_color',
				'priority' => 1.55,
			)
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_caption_bg_alpha',
		array(
			'default'           => 0.3,
			'sanitize_callback' => 'goyoartdark_sanitize_hero_opacity',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_caption_bg_alpha',
		array(
			'label'       => __( '메인 슬로건 — 배경 투명도(알파)', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_caption_bg_alpha',
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 1,
				'step' => 0.01,
			),
			'priority'    => 1.56,
		)
	);

	// ── 메인 슬로건 배경색 없애기 ─────────────────────────────────
	$wp_customize->add_setting(
		'goyo_hero_caption_no_bg',
		array(
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_caption_no_bg',
		array(
			'label'    => __( '메인 슬로건 배경색 없애기', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => 'goyo_hero_caption_no_bg',
			'type'     => 'checkbox',
			'priority' => 1.6,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_slogan_font_family',
		array(
			'default'           => '"Poppins", sans-serif',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_font_family',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_slogan_font_family',
		array(
			'label'       => __( '메인 슬로건 — 폰트(패밀리)', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_slogan_font_family',
			'type'        => 'text',
			'input_attrs' => array(
				'placeholder' => 'Inter, "Noto Sans KR", sans-serif',
			),
			'priority'    => 2,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_slogan_font_size',
		array(
			'default'           => 'clamp(1.9rem, 4.5vw, 4.2rem)',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_css_value',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_slogan_font_size',
		array(
			'label'       => __( '메인 슬로건 — 폰트 크기', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_slogan_font_size',
			'type'        => 'text',
			'description' => __( '예: clamp(1.9rem, 4.2vw, 3.5rem)', 'goyoartdark' ),
			'input_attrs' => array(
				'placeholder' => 'clamp(1.9rem, 4.2vw, 3.5rem)',
			),
			'priority'    => 7,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_slogan_font_weight',
		array(
			'default'           => '700',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_font_weight',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_slogan_font_weight',
		array(
			'label'    => __( '메인 슬로건 — 폰트 굵기', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => 'goyo_hero_slogan_font_weight',
			'type'     => 'select',
			'choices'  => array(
				'200' => __( '200 — Thin', 'goyoartdark' ),
				'300' => __( '300 — Light', 'goyoartdark' ),
				'400' => __( '400 — Regular', 'goyoartdark' ),
				'500' => __( '500 — Medium', 'goyoartdark' ),
				'600' => __( '600 — SemiBold', 'goyoartdark' ),
				'700' => __( '700 — Bold', 'goyoartdark' ),
				'800' => __( '800 — ExtraBold', 'goyoartdark' ),
			),
			'priority' => 4,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_slogan_color',
		array(
			'default'           => '#fff157',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'goyo_hero_slogan_color',
			array(
				'label'    => __( '메인 슬로건 — 글자색', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'goyo_hero_slogan_color',
				'priority' => 5,
			)
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_slogan_opacity',
		array(
			'default'           => 1,
			'sanitize_callback' => 'goyoartdark_sanitize_hero_opacity',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_slogan_opacity',
		array(
			'label'       => __( '메인 슬로건 — 투명도(알파)', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_slogan_opacity',
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 1,
				'step' => 0.01,
			),
			'priority'    => 6,
		)
	);

	// ── 보조문구 스타일 ──────────────────────────────────────────
	$wp_customize->add_setting(
		'goyo_hero_subtext_font_family',
		array(
			'default'           => '',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_font_family',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_subtext_font_family',
		array(
			'label'       => __( '보조문구 — 폰트(패밀리)', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_subtext_font_family',
			'type'        => 'text',
			'input_attrs' => array(
				'placeholder' => 'system-ui, sans-serif',
			),
			'priority'    => 8,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_subtext_font_size',
		array(
			'default'           => 'clamp(0.9rem, 1.8vw, 1.1rem)',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_css_value',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_subtext_font_size',
		array(
			'label'       => __( '보조문구 — 폰트 크기', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_subtext_font_size',
			'type'        => 'text',
			'description' => __( '예: clamp(1.05rem, 2vw, 1.2rem)', 'goyoartdark' ),
			'input_attrs' => array(
				'placeholder' => 'clamp(1.05rem, 2vw, 1.2rem)',
			),
			'priority'    => 9,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_subtext_color',
		array(
			'default'           => '#f2f2f0',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'goyo_hero_subtext_color',
			array(
				'label'    => __( '보조문구 — 글자색', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'goyo_hero_subtext_color',
				'priority' => 10,
			)
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_subtext_opacity',
		array(
			'default'           => 1,
			'sanitize_callback' => 'goyoartdark_sanitize_hero_opacity',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_subtext_opacity',
		array(
			'label'       => __( '보조문구 — 투명도(알파)', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_subtext_opacity',
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 1,
				'step' => 0.01,
			),
			'priority'    => 11,
		)
	);

	// 버튼(링크) 글자·폰트·색상·투명도·배경·라인·URL·새탭 컨트롤 제거됨.
	// 히어로 배경 이미지(HERO_FONT_BACK) 및 히어로 높이(HERO_HEIGHT) 컨트롤 제거됨.

	// 헤더 검색· — Unicorn 효과 선택(42) 직상단. '상단 검색…' 직전에 시각적 구분선 .
	$wp_customize->add_setting(
		'goyoartdark_main_page_top_sep_before_search_1',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		new Goyoartdark_Customize_Separator_Control(
			$wp_customize,
			'goyoartdark_main_page_top_sep_before_search_1',
			array(
				'section'  => $section_id,
				'settings' => 'goyoartdark_main_page_top_sep_before_search_1',
				'priority' => 38,
			)
		)
	);


	$wp_customize->add_setting(
		'hide_search_box',
		array(
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'hide_search_box',
		array(
			'label'       => __( '상단 검색 아이콘 숨기기', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'hide_search_box',
			'type'        => 'checkbox',
			'priority'    => 40,
		)
	);
	


}
add_action( 'customize_register', 'goyoartdark_customizer_main_page_top_hero', 8 );

/**
 * 메인페이지 패널 — 3열 스와이프·갤러리.
 *
 * @param WP_Customize_Manager $wp_customize 매니저.
 * @return void
 */
function goyoartdark_customizer_main_page_section( $wp_customize ) {
	$section_id = 'goyoartdark_main_page';

	$wp_customize->add_section(
		$section_id,
		array(
			'title'       => __( '슬라이드, 갤러리', 'goyoartdark' ),
			'priority'    => 32,
		)
	);
}
add_action( 'customize_register', 'goyoartdark_customizer_main_page_section', 9 );

/**
 * 커스터마이저 히어로 설정 → CSS 변수를 goyoartdark-front-page 핸들에 인라인 주입.
 * 기존 테마의 wp_add_inline_style() 방식과 동일. transport=refresh 이므로
 * 별도 JS 없이 미리보기 새로고침만으로 반영된다.
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
	$slogan_color       = get_theme_mod( 'goyo_hero_slogan_color', '#fff157' );
	$slogan_opacity     = get_theme_mod( 'goyo_hero_slogan_opacity', 1 );

	// ── 보조문구 ─────────────────────────────────────────────────
	$subtext_font_family = get_theme_mod( 'goyo_hero_subtext_font_family', '' );
	$subtext_font_size   = get_theme_mod( 'goyo_hero_subtext_font_size', 'clamp(0.9rem, 1.8vw, 1.1rem)' );
	$subtext_color       = get_theme_mod( 'goyo_hero_subtext_color', '#f2f2f0' );
	$subtext_opacity     = get_theme_mod( 'goyo_hero_subtext_opacity', 1 );

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
 * 히어로 레이아웃 — body_class 로 정렬 클래스 주입.
 * 'center'(기본)는 추가 없음, 'bottom-left' 일 때 goyo-hero-layout--bottom-left 추가.
 *
 * @param string[] $classes 기존 body 클래스 배열.
 * @return string[]
 */
function goyoartdark_hero_layout_body_class( $classes ) {
	if ( ! is_front_page() && ! is_customize_preview() ) {
		return $classes;
	}
	$layout = get_theme_mod( 'goyo_hero_layout', 'center' );
	if ( 'bottom-left' === $layout ) {
		$classes[] = 'goyo-hero-layout--bottom-left';
	} else {
		// 'center' 를 명시적으로 추가해야 bottom-left 가 저장된 적 있어도 CSS로 덮어씌울 수 있다.
		$classes[] = 'goyo-hero-layout--center';
	}
	return $classes;
}
add_filter( 'body_class', 'goyoartdark_hero_layout_body_class' );



