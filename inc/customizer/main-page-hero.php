<?php
/**
 * 사용자 정의 '메인페이지' — 슬로건 스타일(정렬·캡션 배경·폰트·색) + 검색 아이콘 컨트롤.
 *
 * goyominimal 의 홈 히어로는 MetaSlider 슬라이더가 담당하므로 슬로건 텍스트·버튼·배경 이미지·높이
 * 컨트롤이 없다. 여기서는 슬라이더 캡션(.mainhero-title/.mainhero-lead) 의 스타일 변수만 다룬다.
 * sanitize 콜백은 전부 부모(goyobase/inc/customizer/main-page-sanitizers.php) 제공.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 메인페이지 — 슬로건·보조문구 스타일 컨트롤.
 *
 * @param WP_Customize_Manager $wp_customize 매니저.
 * @return void
 */
function goyobase_customizer_main_page_top_hero( $wp_customize ) {
	$section_id = 'goyo_main_page_top';

	// ── 메인 슬로건 정렬 ──────────────────────────────────────────
	$wp_customize->add_setting(
		'goyo_hero_layout',
		array(
			'default'           => 'center',
			'sanitize_callback' => 'goyobase_sanitize_hero_layout',
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
			'sanitize_callback' => 'goyobase_sanitize_hero_opacity',
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
			'sanitize_callback' => 'goyobase_sanitize_hero_font_family',
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
			'sanitize_callback' => 'goyobase_sanitize_hero_css_value',
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
			'sanitize_callback' => 'goyobase_sanitize_hero_font_weight',
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
			'default'           => goyo_default( 'hero_slogan_color', '#fff157' ),
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
			'sanitize_callback' => 'goyobase_sanitize_hero_opacity',
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
			'sanitize_callback' => 'goyobase_sanitize_hero_font_family',
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
			'sanitize_callback' => 'goyobase_sanitize_hero_css_value',
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
			'default'           => goyo_default( 'hero_subtext_opacity', 1 ),
			'sanitize_callback' => 'goyobase_sanitize_hero_opacity',
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

	// 버튼(링크)·히어로 배경 이미지·히어로 높이 컨트롤은 이 테마에 없음( MetaSlider 가 담당 ).

	// '상단 검색…' 직전에 시각적 구분선( 이 테마는 Unicorn 컨트롤 없음 — unicorn_enabled => false ).
	$wp_customize->add_setting(
		'goyo_main_page_top_sep_before_search_1',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		new Goyoartdark_Customize_Separator_Control(
			$wp_customize,
			'goyo_main_page_top_sep_before_search_1',
			array(
				'section'  => $section_id,
				'settings' => 'goyo_main_page_top_sep_before_search_1',
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
			'label'    => __( '상단 검색 아이콘 숨기기', 'goyoartdark' ),
			'section'  => $section_id,
			'settings' => 'hide_search_box',
			'type'     => 'checkbox',
			'priority' => 40,
		)
	);
}
add_action( 'customize_register', 'goyobase_customizer_main_page_top_hero', 8 );
