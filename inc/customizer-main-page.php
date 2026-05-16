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
 * 메인페이지/메인페이지 상단 다크모드 해제 체크박스 값.
 *
 * @param mixed $value 원본 값.
 * @return bool 체크 시 true.
 */
function goyoartdark_sanitize_main_page_dark_mode_disable( $value ) {
	return rest_sanitize_boolean( $value );
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
function goyoartdark_register_main_page_customizer_panel( $wp_customize ) {
	if ( $wp_customize->get_panel( 'goyoartdark_main_page_panel' ) ) {
		return;
	}
	$wp_customize->add_panel(
		'goyoartdark_main_page_panel',
		array(
			'title'       => __( '메인페이지', 'goyoartdark' ),
			'priority'    => 31,
		)
	);
}
add_action( 'customize_register', 'goyoartdark_register_main_page_customizer_panel', 6 );

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
			'title'       => __( '메인페이지 슬로건', 'goyoartdark' ),
			'panel'       => 'goyoartdark_main_page_panel',
			'priority'    => 1,
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

	$wp_customize->add_setting(
		'goyo_hero_slogan',
		array(
			'default'           => $hero_def['slogan'],
			'sanitize_callback' => 'goyoartdark_sanitize_hero_rich_text',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_slogan',
		array(
			'label'       => __( '메인 슬로건', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_slogan',
			'type'        => 'textarea',
			'input_attrs' => array(
				'rows' => 4,
			),
			'priority'    => 1,
		)
	);

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

	$wp_customize->add_setting(
		'goyo_hero_slogan_font_family',
		array(
			'default'           => '"Poppins", sans-serif',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_font_family',
			'transport'         => 'postMessage',
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
			'transport'         => 'postMessage',
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
			'transport'         => 'postMessage',
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
			'transport'         => 'postMessage',
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
			'transport'         => 'postMessage',
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

	$wp_customize->add_setting(
		'goyo_hero_subtext',
		array(
			'default'           => $hero_def['subtext'],
			'sanitize_callback' => 'goyoartdark_sanitize_hero_rich_text',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_subtext',
		array(
			'label'       => __( '보조문구', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_subtext',
			'type'        => 'textarea',
			'input_attrs' => array(
				'rows' => 4,
			),
			'priority'    => 7,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_subtext_font_family',
		array(
			'default'           => '',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_font_family',
			'transport'         => 'postMessage',
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
			'transport'         => 'postMessage',
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
			'transport'         => 'postMessage',
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
			'transport'         => 'postMessage',
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

	$wp_customize->add_setting(
		'goyo_hero_subtext_hide',
		array(
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_subtext_hide',
		array(
			'label'    => __( '보조문구 숨기기', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => 'goyo_hero_subtext_hide',
			'type'     => 'checkbox',
			'priority' => 12,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_button_hide',
		array(
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_button_hide',
		array(
			'label'    => __( '버튼 숨기기', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => 'goyo_hero_button_hide',
			'type'     => 'checkbox',
			'priority' => 13,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_button_label',
		array(
			'default'           => $hero_def['button_label'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_button_label',
		array(
			'label'       => __( '버튼(링크) 글자', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_button_label',
			'type'        => 'text',
			'priority'    => 14,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_button_font_size',
		array(
			'default'           => '0.9rem',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_css_value',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_button_font_size',
		array(
			'label'       => __( '버튼(링크) — 폰트 크기', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_button_font_size',
			'type'        => 'text',
			'description' => __( '예: 0.88rem, clamp(0.82rem, 1.6vw, 1rem)', 'goyoartdark' ),
			'input_attrs' => array(
				'placeholder' => '0.88rem',
			),
			'priority'    => 15,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_button_color',
		array(
			'default'           => '#f2f2f0',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'goyo_hero_button_color',
			array(
				'label'    => __( '버튼(링크) — 글자색', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'goyo_hero_button_color',
				'priority' => 14,
			)
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_button_opacity',
		array(
			'default'           => 1,
			'sanitize_callback' => 'goyoartdark_sanitize_hero_opacity',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_button_opacity',
		array(
			'label'       => __( '버튼(링크) — 투명도(알파)', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_button_opacity',
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 1,
				'step' => 0.01,
			),
			'priority'    => 15,
		)
	);

	$wp_customize->add_setting(
		Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BG_COLOR,
		array(
			'default'           => '#000000',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BG_COLOR,
			array(
				'label'    => __( '버튼링크 배경색', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BG_COLOR,
				'priority' => 16,
			)
		)
	);

	$wp_customize->add_setting(
		Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BG_OPACITY,
		array(
			'default'           => 0.78,
			'sanitize_callback' => 'goyoartdark_sanitize_hero_opacity',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BG_OPACITY,
		array(
			'label'       => __( '버튼링크 배경 — 투명도(알파)', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BG_OPACITY,
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 1,
				'step' => 0.01,
			),
			'priority'    => 17,
		)
	);

	$wp_customize->add_setting(
		Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BORDER_COLOR,
		array(
			'default'           => '#ffffff',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BORDER_COLOR,
			array(
				'label'    => __( '버튼링크 라인색', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BORDER_COLOR,
				'priority' => 18,
			)
		)
	);

	$wp_customize->add_setting(
		Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BORDER_OPACITY,
		array(
			'default'           => 0.14,
			'sanitize_callback' => 'goyoartdark_sanitize_hero_opacity',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BORDER_OPACITY,
		array(
			'label'       => __( '버튼링크 라인 — 투명도(알파)', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => Goyoartdark_Theme_Mod_Registry::HERO_BUTTON_BORDER_OPACITY,
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 1,
				'step' => 0.01,
			),
			'priority'    => 19,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_button_url',
		array(
			'default'           => '/contactpage/contact/',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_button_url',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_button_url',
		array(
			'label'       => __( '링크 주소', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_button_url',
			'type'        => 'text',
			'description' => __( '내부/외부 주소, 상대경로(/about), 로컬 입력이 가능합니다.', 'goyoartdark' ),
			'input_attrs' => array(
				'placeholder' => 'https://',
			),
			'priority'    => 20,
		)
	);

	$wp_customize->add_setting(
		'goyo_hero_button_new_tab',
		array(
			'default'           => false,
			'sanitize_callback' => 'goyoartdark_sanitize_hero_button_new_tab',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyo_hero_button_new_tab',
		array(
			'label'       => __( '체크 시 링크를 새 탭에서 열기', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyo_hero_button_new_tab',
			'type'        => 'checkbox',
			'priority'    => 21,
		)
	);

	$hero_bg_key = Goyoartdark_Theme_Mod_Registry::HERO_FONT_BACK;
	$wp_customize->add_setting(
		$hero_bg_key,
		array(
			'default'           => 'http://localhost/goyominimal/wp-content/uploads/2026/05/0003.jpg',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			$hero_bg_key,
			array(
				'label'       => __( '히어로 — 배경 이미지(전체)', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => $hero_bg_key,
				'description' => __( '변경 후 강력새로고침(F5)하세요.', 'goyoartdark' ),
				'priority'    => -1,
			)
		)
	);

	$hero_h_key = Goyoartdark_Theme_Mod_Registry::HERO_HEIGHT;
	$wp_customize->add_setting(
		$hero_h_key,
		array(
			'default'           => '100vh; 570px; 50vh',
			'sanitize_callback' => 'goyoartdark_sanitize_hero_height_value',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		$hero_h_key,
		array(
			'label'       => __( '히어로 높이', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => $hero_h_key,
			'type'        => 'text',
			'description' => __( '세미콜론(;)으로 디바이스 구분, 예: 100vh 또는 100vh ; 72vh ; 456px', 'goyoartdark' ),
			'input_attrs' => array(
				'placeholder' => '100vh; 72vh ; 440px',
			),
			'priority'    => 0,
		)
	);

	// 헤더 검색·다크모드 — Unicorn 효과 선택(42) 직상단. '상단 검색…' 직전에 시각적 구분선 .
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

	$wp_customize->add_setting(
		'goyoartdark_main_page_disable_dark_mode',
		array(
			'default'           => true,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_dark_mode_disable',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'goyoartdark_main_page_disable_dark_mode',
		array(
			'label'       => __( '다크모드 해제', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyoartdark_main_page_disable_dark_mode',
			'type'        => 'checkbox',
			'priority'    => 41,
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
			'panel'       => 'goyoartdark_main_page_panel',
			'priority'    => 2,
		)
	);

	$categories_choices = array(
		0 => __( '— 선택 —', 'goyoartdark' ),
	);
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
		if ( ! ( $cat instanceof WP_Term ) ) {
			continue;
		}
		/* translators: 1: category name, 2: slug */
		$categories_choices[ (int) $cat->term_id ] = sprintf( '%1$s (%2$s)', $cat->name, $cat->slug );
	}

	/* 3열 스와이프(홈) — 갤러리 1 직상단 */
	$wp_customize->add_setting(
		'goyoartdark_main_trio_category_id',
		array(
			'default'           => 3,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_category_id',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyoartdark_main_trio_category_id',
		array(
			'label'       => __( '3열 스와이프 — 카테고리', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyoartdark_main_trio_category_id',
			'type'        => 'select',
			'choices'     => $categories_choices,
			'priority'    => 1,
		)
	);
	$wp_customize->add_setting(
		'goyoartdark_main_trio_count',
		array(
			'default'           => 5,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_count',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyoartdark_main_trio_count',
		array(
			'label'       => __( '3열 스와이프 — 표시 개수', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyoartdark_main_trio_count',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 3,
				'max'  => 30,
				'step' => 1,
			),
			'description' => __( '3 ~ 30 개', 'goyoartdark' ),
			'priority'    => 2,
		)
	);
	$wp_customize->add_setting(
		'goyoartdark_main_trio_per_row',
		array(
			'default'           => 3,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_per_row',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyoartdark_main_trio_per_row',
		array(
			'label'    => __( '3열 스와이프 — 한 줄에 보일 개수', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => 'goyoartdark_main_trio_per_row',
			'type'     => 'select',
			'choices'  => array(
				1 => '1',
				2 => '2',
				3 => '3',
				4 => '4',
				5 => '5',
			),
			'priority' => 25,
		)
	);
	$wp_customize->add_setting(
		'goyoartdark_main_trio_image_ratio',
		array(
			'default'           => '4-3',
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_image_ratio_slug',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyoartdark_main_trio_image_ratio',
		array(
			'label'    => __( '3열 스와이프 — 이미지 비율', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => 'goyoartdark_main_trio_image_ratio',
			'type'     => 'select',
			'choices'  => array(
				'4-3'  => __( '4:3 (가로)', 'goyoartdark' ),
				'1-1'  => __( '1:1 (정사각형)', 'goyoartdark' ),
				'3-4'  => __( '3:4 (세로)', 'goyoartdark' ),
				'16-9' => __( '16:9 (가로)', 'goyoartdark' ),
			),
			'priority' => 26,
		)
	);
	$wp_customize->add_setting(
		'goyoartdark_main_trio_hide',
		array(
			'default'           => true,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_hide',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'goyoartdark_main_trio_hide',
		array(
			'label'       => __( '3열 스와이프 영역 숨기기', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => 'goyoartdark_main_trio_hide',
			'type'        => 'checkbox',
			'priority'    => 30,
		)
	);

	$gallery_keys = goyoartdark_main_gallery_mod_keys();

	$wp_customize->add_setting(
		$gallery_keys['cat_key'],
		array(
			'default'           => 3,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_category_id',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		$gallery_keys['cat_key'],
		array(
			'label'       => __( '갤러리 — 카테고리', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => $gallery_keys['cat_key'],
			'type'        => 'select',
			'choices'     => $categories_choices,
			'priority'    => 40,
		)
	);

	$wp_customize->add_setting(
		$gallery_keys['count_key'],
		array(
			'default'           => 6,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_count',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		$gallery_keys['count_key'],
		array(
			'label'       => __( '갤러리 — 표시 개수', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => $gallery_keys['count_key'],
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 1,
				'max'  => 50,
				'step' => 1,
			),
			'priority'    => 41,
		)
	);

	$wp_customize->add_setting(
		$gallery_keys['per_row_key'],
		array(
			'default'           => 3,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_per_row',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		$gallery_keys['per_row_key'],
		array(
			'label'    => __( '갤러리 — 한 줄에 보일 개수', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => $gallery_keys['per_row_key'],
			'type'     => 'select',
			'choices'  => array(
				1 => '1',
				2 => '2',
				3 => '3',
				4 => '4',
				5 => '5',
			),
			'priority' => 42,
		)
	);

	$wp_customize->add_setting(
		$gallery_keys['ratio_key'],
		array(
			'default'           => '16-9',
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_image_ratio_slug',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		$gallery_keys['ratio_key'],
		array(
			'label'    => __( '갤러리 — 이미지 비율', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => $gallery_keys['ratio_key'],
			'type'     => 'select',
			'choices'  => array(
				'4-3'  => __( '4:3 (가로)', 'goyoartdark' ),
				'1-1'  => __( '1:1 (정사각형)', 'goyoartdark' ),
				'3-4'  => __( '3:4 (세로)', 'goyoartdark' ),
				'16-9' => __( '16:9 (가로)', 'goyoartdark' ),
			),
			'priority' => 43,
		)
	);

	$wp_customize->add_setting(
		$gallery_keys['hide_key'],
		array(
			'default'           => true,
			'sanitize_callback' => 'goyoartdark_sanitize_main_page_hide',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		$gallery_keys['hide_key'],
		array(
			'label'       => __( '갤러리 영역 숨기기', 'goyoartdark' ),
			'section'     => $section_id,
			'settings'    => $gallery_keys['hide_key'],
			'type'        => 'checkbox',
			'priority'    => 44,
		)
	);

	// 코어 customize-selective-refresh: 히어로(슬로건·보조·버튼), 3열 스와이프, 갤러리.
	if ( ! isset( $wp_customize->selective_refresh ) ) {
		return;
	}

	$wp_customize->selective_refresh->add_partial(
		'goyoartdark_partial_hero_inner',
		array(
			'selector'            => '.goyo-customize-partial--hero',
			'container_inclusive' => true,
			'settings'            => array(
				'goyo_hero_slogan',
				'goyo_hero_slogan_font_family',
				'goyo_hero_slogan_font_size',
				'goyo_hero_slogan_font_weight',
				'goyo_hero_slogan_color',
				'goyo_hero_slogan_opacity',
				'goyo_hero_subtext',
				'goyo_hero_subtext_font_family',
				'goyo_hero_subtext_font_size',
				'goyo_hero_subtext_color',
				'goyo_hero_subtext_opacity',
				'goyo_hero_subtext_hide',
				'goyo_hero_button_label',
				'goyo_hero_button_font_size',
				'goyo_hero_button_hide',
				'goyo_hero_button_color',
				'goyo_hero_button_opacity',
				'goyo_hero_button_bg_color',
				'goyo_hero_button_bg_opacity',
				'goyo_hero_button_border_color',
				'goyo_hero_button_border_opacity',
				'goyo_hero_button_url',
				'goyo_hero_button_new_tab',
			),
			'primary_setting'     => 'goyo_hero_slogan',
			'render_callback'     => 'goyoartdark_customize_render_partial_hero',
		)
	);

	$wp_customize->selective_refresh->add_partial(
		'goyoartdark_partial_main_trio',
		array(
			'selector'            => '.goyo-customize-partial--main-trio',
			'container_inclusive' => true,
			'settings'            => array(
				'goyoartdark_main_trio_category_id',
				'goyoartdark_main_trio_count',
				'goyoartdark_main_trio_per_row',
				'goyoartdark_main_trio_image_ratio',
				'goyoartdark_main_trio_hide',
			),
			'primary_setting'     => 'goyoartdark_main_trio_category_id',
			'render_callback'     => 'goyoartdark_customize_render_partial_main_trio',
		)
	);

	$gallery_partial_keys = goyoartdark_main_gallery_mod_keys();
	$wp_customize->selective_refresh->add_partial(
		'goyoartdark_partial_main_gallery',
		array(
			'selector'            => '.goyo-customize-partial--main-gallery',
			'container_inclusive' => true,
			'settings'            => array(
				$gallery_partial_keys['cat_key'],
				$gallery_partial_keys['count_key'],
				$gallery_partial_keys['per_row_key'],
				$gallery_partial_keys['ratio_key'],
				$gallery_partial_keys['hide_key'],
			),
			'primary_setting'     => $gallery_partial_keys['cat_key'],
			'render_callback'     => 'goyoartdark_customize_render_partial_main_gallery',
		)
	);
}
add_action( 'customize_register', 'goyoartdark_customizer_main_page_section', 9 );

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
	}
	return $classes;
}
add_filter( 'body_class', 'goyoartdark_hero_layout_body_class' );

/**
 * 히어로(메인슬로건·보조·버튼) — selective refresh.
 *
 * @param WP_Customize_Partial $partial Partial.
 * @param array                $context 컨텍스트(선택).
 * @return string
 */
function goyoartdark_customize_render_partial_hero( $partial, $context = array() ) { // phpcs:ignore VariableAnalysis
	unset( $partial, $context );
	return goyoartdark_render_hero_inner_html( true );
}

/**
 * 홈 3열 스와이프 — selective refresh.
 *
 * @param WP_Customize_Partial $partial Partial.
 * @param array                  $context 컨텍스트.
 * @return string
 */
function goyoartdark_customize_render_partial_main_trio( $partial, $context = array() ) { // phpcs:ignore VariableAnalysis
	unset( $partial, $context );
	return goyoartdark_render_main_trio_partial_root_html();
}

/**
 * 메인 갤러리 — selective refresh 렌더.
 *
 * @param WP_Customize_Partial $partial Partial.
 * @param array                $context 컨텍스트(선택).
 * @return string
 */
function goyoartdark_customize_render_partial_main_gallery( $partial, $context = array() ) { // phpcs:ignore VariableAnalysis
	unset( $partial, $context );
	return goyoartdark_render_main_gallery_partial_root_html();
}
