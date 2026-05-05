<?php
/**
 * goyoartdark 테마 사용자 정의(Customizer) 설정.
 *
 * '회사공통정보' 섹션을 추가하여 SNS URL, 카카오톡/네이버톡 주소,
 * 구글/네이버 애널리틱스, 사이트 소유확인 등을 한곳에서 관리한다.
 * 카카오톡 미리보기(OG) 이미지 등은 '사이트 아이덴티티' 추가 항목에 둔다. 블록 테마(FSE)이지만
 * 일부 값은 패턴 PHP 및 wp_head 훅에서 get_theme_mod() 로 읽는다.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 * @since goyoartdark 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'Goyoartdark_Customize_Separator_Control' ) ) {
	/**
	 * 커스터마이저 옵션 그룹을 시각적으로 구분하는 구분선 컨트롤.
	 */
	class Goyoartdark_Customize_Separator_Control extends WP_Customize_Control {
		/**
		 * Control type.
		 *
		 * @var string
		 */
		public $type = 'goyoartdark_separator';

		/**
		 * Render the control's content.
		 *
		 * @return void
		 */
		public function render_content() {
			?>
			<hr style="margin:14px 0;border:0;border-top:1px solid #dcdcde;" />
			<?php
		}
	}
}

// 회사공통정보 섹션 기본값과 라벨을 한곳에서 관리해 커스터마이저/템플릿 중복을 최소화한다.
if ( ! function_exists( 'goyoartdark_customizer_common_info_section' ) ) :
	/**
	 * Registers the '회사공통정보' section and its settings on the Customizer.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function goyoartdark_customizer_common_info_section( $wp_customize ) {
		$section_id = 'goyoartdark_common_info';

		$wp_customize->add_section(
			$section_id,
			array(
				'title'    => __( '회사공통정보', 'goyoartdark' ),
				'priority' => 30,
			)
		);
		$menu_font_panel_id    = 'goyoartdark_menu_font_settings';
		$top_menu_section_id   = 'goyoartdark_header_menu_typography';
		$sub_banner_section_id = 'goyoartdark_sub_banner_typography';
		$wp_customize->add_panel(
			$menu_font_panel_id,
			array(
				'title'    => __( '상단 디자인 및 폰트 설정 ', 'goyoartdark' ),
				'priority' => 32,
			)
		);
		$wp_customize->add_section(
			$top_menu_section_id,
			array(
				'title'    => __( '상단 메뉴 글자', 'goyoartdark' ),
				'panel'    => $menu_font_panel_id,
				'priority' => 10,
			)
		);
		$wp_customize->add_section(
			$sub_banner_section_id,
			array(
				'title'    => __( '서브페이지 상단', 'goyoartdark' ),
				'panel'    => $menu_font_panel_id,
				'priority' => 20,
			)
		);

		// 하단 푸터: 회사 정보(연락처 등 HTML 가능), 카피라이터(평문)
		$wp_customize->add_setting(
			'goyoartdark_footer_secondary_text',
			array(
				'default'           => '<span>대표전화</span> 02-731-2120 <span>이메일</span> goyofunkstudio@naver.com <span>주소</span> 서울특별시 중구 세종대로 110 (04524)',
				'sanitize_callback' => 'wp_kses_post',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'goyoartdark_footer_secondary_text',
			array(
				'label'    => __( '푸터 회사 정보', 'goyoartdark' ),
				'section'  => $section_id,
				'type'     => 'textarea',
				'settings' => 'goyoartdark_footer_secondary_text',
			)
		);
		$wp_customize->add_setting(
			'goyoartdark_footer_copyright_text',
			array(
				'default'           => 'COPYRIGHT © 고요펑크 ALL RIGHTS RESERVED.',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'goyoartdark_footer_copyright_text',
			array(
				'label'       => __( '푸터 카피라이터 문구', 'goyoartdark' ),
				'section'     => $section_id,
				'type'        => 'textarea',
				'settings'    => 'goyoartdark_footer_copyright_text',
				'input_attrs' => array(
					'rows' => 3,
				),
			)
		);

		// 카카오톡 채팅 주소
		$wp_customize->add_setting(
			'kakaochat_url',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'kakaochat_url',
			array(
				'label'       => __( '카톡채팅 주소', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'kakaochat_url',
				'type'        => 'url',
				'description' => __( '공백으로 두면 버튼이 보이지 않습니다.', 'goyoartdark' ),
			)
		);

		// 네이버톡톡 주소
		$wp_customize->add_setting(
			'navertalk_url',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'navertalk_url',
			array(
				'label'    => __( '네이버톡톡 주소', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'navertalk_url',
				'type'     => 'url',
			)
		);

		// 인스타그램
		$wp_customize->add_setting(
			'instagram_url',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'instagram_url',
			array(
				'label'    => __( '인스타 주소', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'instagram_url',
				'type'     => 'url',
			)
		);

		// 블로그
		$wp_customize->add_setting(
			'blog_url',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'blog_url',
			array(
				'label'    => __( '블로그 주소', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'blog_url',
				'type'     => 'url',
			)
		);

		// 유튜브
		$wp_customize->add_setting(
			'youtube_url',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'youtube_url',
			array(
				'label'    => __( '유튜브 주소', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'youtube_url',
				'type'     => 'url',
			)
		);

		// 페이스북
		$wp_customize->add_setting(
			'facebook_url',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'facebook_url',
			array(
				'label'    => __( '페이스북 주소', 'goyoartdark' ),
				'section'  => $section_id,
				'settings' => 'facebook_url',
				'type'     => 'url',
			)
		);

		// 서브페이지 상단영역 배경색.
		$wp_customize->add_setting(
			'sub_banner_bg_color',
			array(
				'default'           => '#333333',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'sub_banner_bg_color',
				array(
					'label'       => __( '서브페이지 상단영역 배경색', 'goyoartdark' ),
					'section'     => $sub_banner_section_id,
					'settings'    => 'sub_banner_bg_color',
					'description' => __( '서브페이지 상단영역(.subBanner) 영역 배경색을 설정합니다.', 'goyoartdark' ),
				)
			)
		);

		// 서브페이지 상단영역 오버레이 색상(알파 포함).
		$wp_customize->add_setting(
			'sub_banner_overlay_bg_color',
			array(
				'default'           => 'rgba(0, 0, 0, 0.3)',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'sub_banner_overlay_bg_color',
			array(
				'label'       => __( '서브페이지 오버레이 색상(CSS)', 'goyoartdark' ),
				'section'     => $sub_banner_section_id,
				'settings'    => 'sub_banner_overlay_bg_color',
				'type'        => 'text',
				'description' => __( '예: rgba(0, 0, 0, 0.35)', 'goyoartdark' ),
			)
		);

		// 서브페이지 상단영역 최소 높이(CSS, min-height 값).
		$wp_customize->add_setting(
			'sub_banner_min_height',
			array(
				'default'           => 'clamp(400px, 40vw,500px)',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'sub_banner_min_height',
			array(
				'label'       => __( '서브페이지 상단영역 최소높이(CSS)', 'goyoartdark' ),
				'section'     => $sub_banner_section_id,
				'settings'    => 'sub_banner_min_height',
				'type'        => 'text',
				'description' => __( '예: clamp(400px, 40vw,500px), 500px', 'goyoartdark' ),
			)
		);

		// 서브페이지 제목(.subBanner .pageTitle) 폰트 패밀리.
		$wp_customize->add_setting(
			'sub_banner_page_title_font_family',
			array(
				'default'           => 'Pretendard, "Noto Sans KR", sans-serif',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'sub_banner_page_title_font_family',
			array(
				'label'       => __( '서브페이지 제목 폰트 패밀리', 'goyoartdark' ),
				'section'     => $sub_banner_section_id,
				'settings'    => 'sub_banner_page_title_font_family',
				'type'        => 'text',
				'priority'    => 20,
				'description' => __( '서브페이지 제목(.subBanner .pageTitle) 폰트 패밀리', 'goyoartdark' ),
			)
		);


		// 서브페이지 상단 타이틀(pageTitle) 폰트 사이즈(CSS 값).

		$wp_customize->add_setting(
			'sub_banner_page_title_font_size',
			array(
				'default'           => 'clamp(24px, 3.2vw, 46px)',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'sub_banner_page_title_font_size',
			array(
				'label'       => __( '서브페이지 제목 글자 크기(CSS)', 'goyoartdark' ),
				'section'     => $sub_banner_section_id,
				'settings'    => 'sub_banner_page_title_font_size',
				'type'        => 'text',
				'priority'    => 22,
				'description' => __( '예: clamp(24px, 3.2vw, 46px)', 'goyoartdark' ),
			)
		);

		// 서브페이지 상단 타이틀(pageTitle) 폰트 굵기.
		$wp_customize->add_setting(
			'sub_banner_page_title_font_weight',
			array(
				'default'           => 700,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'sub_banner_page_title_font_weight',
			array(
				'label'       => __( '서브페이지 제목 글자 굵기', 'goyoartdark' ),
				'section'     => $sub_banner_section_id,
				'settings'    => 'sub_banner_page_title_font_weight',
				'type'        => 'number',
				'priority'    => 23,
				'input_attrs' => array(
					'min'  => 100,
					'max'  => 900,
					'step' => 100,
				),
			)
		);

		// 서브페이지 제목(.subBanner .pageTitle) 글자 색상.
		$wp_customize->add_setting(
			'sub_banner_page_title_font_color',
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'sub_banner_page_title_font_color',
				array(
					'label'       => __( '서브페이지 제목 글자 색상', 'goyoartdark' ),
					'section'     => $sub_banner_section_id,
					'settings'    => 'sub_banner_page_title_font_color',
					'priority'    => 21,
				)
			)
		);

		// 서브메뉴(.subBanner .subNav a) 폰트 사이즈(CSS 값).
		$wp_customize->add_setting(
			'sub_banner_subnav_font_size',
			array(
				'default'           => '17px',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'sub_banner_subnav_font_size',
			array(
				'label'       => __( '2차메뉴 글자 크기(CSS)', 'goyoartdark' ),
				'section'     => $sub_banner_section_id,
				'settings'    => 'sub_banner_subnav_font_size',
				'type'        => 'text',
				'priority'    => 32,
				'description' => __( '예: 17px, clamp(14px, 1.3vw, 17px)', 'goyoartdark' ),
			)
		);

		// 서브메뉴(.subBanner .subNav a) 폰트 굵기.
		$wp_customize->add_setting(
			'sub_banner_subnav_font_weight',
			array(
				'default'           => 400,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'sub_banner_subnav_font_weight',
			array(
				'label'       => __( '2차메뉴 글자 굵기', 'goyoartdark' ),
				'section'     => $sub_banner_section_id,
				'settings'    => 'sub_banner_subnav_font_weight',
				'type'        => 'number',
				'priority'    => 33,
				'input_attrs' => array(
					'min'  => 100,
					'max'  => 900,
					'step' => 100,
				),
			)
		);

		// 서브메뉴(.subBanner .subNav a) 폰트 패밀리.
		$wp_customize->add_setting(
			'sub_banner_subnav_font_family',
			array(
				'default'           => 'Pretendard, "Noto Sans KR", sans-serif',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'sub_banner_subnav_font_family',
			array(
				'label'       => __( '2차메뉴 폰트 패밀리', 'goyoartdark' ),
				'section'     => $sub_banner_section_id,
				'settings'    => 'sub_banner_subnav_font_family',
				'type'        => 'text',
				'priority'    => 30,
			)
		);

		// 서브메뉴(.subBanner .subNav a) 글자 색상.
		$wp_customize->add_setting(
			'sub_banner_subnav_font_color',
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'sub_banner_subnav_font_color',
				array(
					'label'       => __( '2차메뉴 글자 색상', 'goyoartdark' ),
					'section'     => $sub_banner_section_id,
					'settings'    => 'sub_banner_subnav_font_color',
					'priority'    => 31,
				)
			)
		);

		// 서브배너 형제 메뉴(.subBanner .subNav a.active) 배경·보더 — accent 와 동일 기본값.
		$wp_customize->add_setting(
			'subbanner_menu_active_bg_color',
			array(
				'default'           => '#04af65',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'subbanner_menu_active_bg_color',
				array(
					'label'       => __( '2차메뉴 현재 배경색', 'goyoartdark' ),
					'section'     => $sub_banner_section_id,
					'settings'    => 'subbanner_menu_active_bg_color',
					'priority'    => 32,
				)
			)
		);

		// 헤더 메뉴(.site-header .menu-item > a) 폰트 사이즈(CSS 값).
		$wp_customize->add_setting(
			'header_menu_font_size',
			array(
				'default'           => 'clamp(18px, 1.2vw, 21px)',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'header_menu_font_size',
			array(
				'label'       => __( '2차메뉴 글자 크기(CSS)', 'goyoartdark' ),
				'section'     => $top_menu_section_id,
				'settings'    => 'header_menu_font_size',
				'type'        => 'text',
				'priority'    => 3,
				'description' => __( '예: clamp(18px, 1.2vw, 21px)', 'goyoartdark' ),
			)
		);

		// 헤더 메뉴(.site-header .menu-item > a) 폰트 굵기.
		$wp_customize->add_setting(
			'header_menu_font_weight',
			array(
				'default'           => 500,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'header_menu_font_weight',
			array(
				'label'       => __( '2차메뉴 글자 굵기', 'goyoartdark' ),
				'section'     => $top_menu_section_id,
				'settings'    => 'header_menu_font_weight',
				'type'        => 'number',
				'priority'    => 4,
				'input_attrs' => array(
					'min'  => 100,
					'max'  => 900,
					'step' => 100,
				),
			)
		);

		// 헤더 2차메뉴(.site-header .sub-menu .menu-item a) 폰트 사이즈(CSS 값).
		$wp_customize->add_setting(
			'header_submenu_font_size',
			array(
				'default'           => '16px',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'header_submenu_font_size',
			array(
				'label'       => __( '2차메뉴 글자 크기(CSS)', 'goyoartdark' ),
				'section'     => $top_menu_section_id,
				'settings'    => 'header_submenu_font_size',
				'type'        => 'text',
				'priority'    => 7,
			)
		);

		// 헤더 2차메뉴(.site-header .sub-menu .menu-item a) 폰트 굵기.
		$wp_customize->add_setting(
			'header_submenu_font_weight',
			array(
				'default'           => 400,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'header_submenu_font_weight',
			array(
				'label'       => __( '2차메뉴 글자 굵기', 'goyoartdark' ),
				'section'     => $top_menu_section_id,
				'settings'    => 'header_submenu_font_weight',
				'type'        => 'number',
				'priority'    => 8,
				'input_attrs' => array(
					'min'  => 100,
					'max'  => 900,
					'step' => 100,
				),
			)
		);

		// 헤더 2차메뉴(.site-header .sub-menu .menu-item a) 폰트 패밀리.
		$wp_customize->add_setting(
			'header_submenu_font_family',
			array(
				'default'           => 'Pretendard, "Noto Sans KR", sans-serif',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'header_submenu_font_family',
			array(
				'label'       => __( '2차메뉴 폰트 패밀리', 'goyoartdark' ),
				'section'     => $top_menu_section_id,
				'settings'    => 'header_submenu_font_family',
				'type'        => 'text',
				'priority'    => 5,
			)
		);

		// 헤더 2차메뉴(.site-header .sub-menu .menu-item a) 글자 색상.
		$wp_customize->add_setting(
			'header_submenu_font_color',
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'header_submenu_font_color',
				array(
					'label'       => __( '2차메뉴 글자 색상', 'goyoartdark' ),
					'section'     => $top_menu_section_id,
					'settings'    => 'header_submenu_font_color',
					'priority'    => 6,
				)
			)
		);

		// 드롭다운·모바일 서브메뉴에서 현재 페이지 항목 강조색 — theme.json preset accent 와 동일 기본값(#04af65).
		$wp_customize->add_setting(
			'header_submenu_current_accent_color',
			array(
				'default'           => '#04af65',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'header_submenu_current_accent_color',
				array(
					'label'       => __( '현재강조색상', 'goyoartdark' ),
					'section'     => $top_menu_section_id,
					'settings'    => 'header_submenu_current_accent_color',
					'priority'    => 6.5,
				)
			)
		);

		// 헤더 메뉴(.site-header .menu-item > a) 폰트 패밀리.
		$wp_customize->add_setting(
			'header_menu_font_family',
			array(
				'default'           => 'Pretendard, "Noto Sans KR", sans-serif',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'header_menu_font_family',
			array(
				'label'       => __( '헤더 메뉴 폰트 패밀리', 'goyoartdark' ),
				'section'     => $top_menu_section_id,
				'settings'    => 'header_menu_font_family',
				'type'        => 'text',
				'priority'    => 1,
			)
		);

		// 헤더 메뉴(.site-header .menu-item > a) 글자 색상.
		$wp_customize->add_setting(
			'header_menu_font_color',
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'header_menu_font_color',
				array(
					'label'       => __( '헤더 메뉴 글자 색상', 'goyoartdark' ),
					'section'     => $top_menu_section_id,
					'settings'    => 'header_menu_font_color',
					'priority'    => 2,
				)
			)
		);

		// 현재 페이지·조상 메뉴 강조색 — header.css 의 --menu-top-current-color 와 연결.
		$wp_customize->add_setting(
			'header_menu_current_font_color',
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'header_menu_current_font_color',
				array(
					'label'       => __( '현재강조색상', 'goyoartdark' ),
					'section'     => $top_menu_section_id,
					'settings'    => 'header_menu_current_font_color',
					'priority'    => 2.2,
				)
			)
		);

		// 헤더 메뉴(.site-header .menu-item > a) 글자 색상 알파값.
		$wp_customize->add_setting(
			'header_menu_font_opacity',
			array(
				'default'           => 1,
				'sanitize_callback' => 'goyoartdark_sanitize_hero_opacity',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'header_menu_font_opacity',
			array(
				'label'       => __( '헤더 메뉴 글자 색상 투명도(알파)', 'goyoartdark' ),
				'section'     => $top_menu_section_id,
				'settings'    => 'header_menu_font_opacity',
				'type'        => 'range',
				'priority'    => 2.5,
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 1,
					'step' => 0.01,
				),
			)
		);

		// 네이버 애널리틱스
		$wp_customize->add_setting(
			'naveranal',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'naveranal',
			array(
				'label'       => __( '네이버 애널리틱스 추적 ID', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'naveranal',
				'type'        => 'text',
				'description' => __( '추적 ID만 입력하세요.', 'goyoartdark' ),
			)
		);

		// 구글 애널리틱스
		$wp_customize->add_setting(
			'googleanal',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'googleanal',
			array(
				'label'       => __( '구글 애널리틱스 추적 ID', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'googleanal',
				'type'        => 'text',
				'description' => __( '추적 ID만 입력하세요. (예: G-XXXXXXXXXX)', 'goyoartdark' ),
			)
		);

		// 네이버 서치어드바이저 소유확인
		$wp_customize->add_setting(
			'naver_site_verification',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'naver_site_verification',
			array(
				'label'       => __( '네이버 사이트 소유확인', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'naver_site_verification',
				'type'        => 'text',
				'description' => __( '네이버 서치어드바이저에서 발급받은 소유확인 메타 태그의 content 값만 입력하세요.', 'goyoartdark' ),
			)
		);

		// 구글 서치콘솔 소유확인
		$wp_customize->add_setting(
			'google_site_verification',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'google_site_verification',
			array(
				'label'       => __( '구글 서치콘솔 소유확인', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'google_site_verification',
				'type'        => 'text',
				'description' => __( '구글 서치콘솔에서 발급받은 소유확인 메타 태그의 content 값만 입력하세요.', 'goyoartdark' ),
			)
		);

		// 선택적 갱신: 값 변경 시 전체 새로고침 없이 해당 영역만 업데이트.
		$wp_customize->selective_refresh->add_partial(
			'kakaochat_url',
			array(
				'selector'        => '.edit_kakaochat_url',
				'render_callback' => static function () {
					return esc_url( get_theme_mod( 'kakaochat_url', '' ) );
				},
			)
		);
	}
endif;
add_action( 'customize_register', 'goyoartdark_customizer_common_info_section' );

// 헤더 로고는 WP 코어 사이트 아이덴티티 "로고"( add_theme_support( 'custom-logo' ) ). OG 이미지만 여기서 추가한다.
if ( ! function_exists( 'goyoartdark_sanitize_logo_width' ) ) :
	/**
	 * 로고 가로 크기를 정리한다. `PC;모바일` 형식을 지원한다.
	 *
	 * @param mixed $raw Raw value.
	 * @return string
	 */
	function goyoartdark_sanitize_logo_width( $raw ) {
		$default = '180px';
		$value   = trim( sanitize_text_field( (string) $raw ) );
		if ( '' === $value ) {
			return $default;
		}
		$sanitize_part = static function ( $part, $fallback ) {
			$part = trim( (string) $part );
			if ( preg_match( '/^-?\d+(?:\.\d+)?(?:px|em|rem|%)?$/', $part ) ) {
				if ( preg_match( '/^-?\d+(?:\.\d+)?$/', $part ) ) {
					return $part . 'px';
				}
				return $part;
			}
			return $fallback;
		};
		if ( false !== strpos( $value, ';' ) ) {
			$parts        = explode( ';', $value, 2 );
			$desktop_part = $sanitize_part( $parts[0], $default );
			$mobile_part  = $sanitize_part( $parts[1], $desktop_part );
			return $desktop_part . ';' . $mobile_part;
		}
		return $sanitize_part( $value, $default );
	}
endif;

if ( ! function_exists( 'goyoartdark_sanitize_logo_margin' ) ) :
	/**
	 * 로고 margin 입력값을 CSS shorthand 형식으로 제한한다.
	 *
	 * @param mixed $raw Raw value.
	 * @return string
	 */
	function goyoartdark_sanitize_logo_margin( $raw ) {
		$default = '0 12px 0 0';
		$value = trim( sanitize_text_field( (string) $raw ) );
		if ( '' === $value ) {
			return $default;
		}
		$sanitize_part = static function ( $part, $fallback ) {
			$part = trim( (string) $part );
			if ( preg_match( '/^-?\d+(?:\.\d+)?(?:px|em|rem|%)?(?:\s+-?\d+(?:\.\d+)?(?:px|em|rem|%)?){0,3}$/', $part ) ) {
				return $part;
			}
			return $fallback;
		};
		if ( false !== strpos( $value, ';' ) ) {
			$parts        = explode( ';', $value, 2 );
			$desktop_part = $sanitize_part( $parts[0], $default );
			$mobile_part  = $sanitize_part( $parts[1], $desktop_part );
			return $desktop_part . ';' . $mobile_part;
		}
		return $sanitize_part( $value, $default );
	}
endif;

if ( ! function_exists( 'goyoartdark_customizer_site_identity_extra' ) ) :
	/**
	 * Registers theme-only image controls under the Site Identity section.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function goyoartdark_customizer_site_identity_extra( $wp_customize ) {
		$site_identity_section = 'title_tagline';
		$logo_priority         = 31;
		$tagline_control       = $wp_customize->get_control( 'blogdescription' );

		if ( $tagline_control && isset( $tagline_control->priority ) ) {
			$logo_priority = (int) $tagline_control->priority + 1;
		}

		// 코어 로고 컨트롤을 태그라인 바로 아래로 이동.
		if ( $wp_customize->get_control( 'custom_logo' ) ) {
			$wp_customize->get_control( 'custom_logo' )->priority = $logo_priority;
		}

		$wp_customize->add_setting(
			Goyoartdark_Theme_Mod_Registry::HEADER_LOGO_WIDTH,
			array(
				'default'           => '180px',
				'sanitize_callback' => 'goyoartdark_sanitize_logo_width',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			Goyoartdark_Theme_Mod_Registry::HEADER_LOGO_WIDTH,
			array(
				'label'       => __( '로고 가로 크기(px)', 'goyoartdark' ),
				'section'     => $site_identity_section,
				'settings'    => Goyoartdark_Theme_Mod_Registry::HEADER_LOGO_WIDTH,
				'type'        => 'text',
				'priority'    => $logo_priority + 1,
				'description' => __( '예: 115px; 40px (PC;모바일 520px 이하)', 'goyoartdark' ),
			)
		);
		$wp_customize->add_setting(
			Goyoartdark_Theme_Mod_Registry::HEADER_LOGO_MARGIN,
			array(
				'default'           => '0 12px 0 0',
				'sanitize_callback' => 'goyoartdark_sanitize_logo_margin',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			Goyoartdark_Theme_Mod_Registry::HEADER_LOGO_MARGIN,
			array(
				'label'       => __( '헤더 로고 마진', 'goyoartdark' ),
				'section'     => $site_identity_section,
				'settings'    => Goyoartdark_Theme_Mod_Registry::HEADER_LOGO_MARGIN,
				'type'        => 'text',
				'priority'    => $logo_priority + 2,
				'description' => __( '예: 0 12px 0 0; 0 8px 0 0 (PC;모바일 520px 이하)', 'goyoartdark' ),
			)
		);

		// 카카오톡/SNS 미리보기 이미지(OG 이미지) - SNS 공유 시 노출되는 사이트 대표 이미지.
		$wp_customize->add_setting(
			Goyoartdark_Theme_Mod_Registry::KAKAO_PREVIEW_IMAGE,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				Goyoartdark_Theme_Mod_Registry::KAKAO_PREVIEW_IMAGE,
				array(
					'label'       => __( '카카오톡 미리보기 이미지', 'goyoartdark' ),
					'section'     => $site_identity_section,
					'settings'    => Goyoartdark_Theme_Mod_Registry::KAKAO_PREVIEW_IMAGE,
					'description' => __( '사이즈 : 800 x 400px', 'goyoartdark' ),
					'priority'    => $logo_priority + 20,
				)
			)
		);
	}
endif;
add_action( 'customize_register', 'goyoartdark_customizer_site_identity_extra' );

if ( ! function_exists( 'goyoartdark_sanitize_unicorn_project_id' ) ) :
	/**
	 * Unicorn Studio 프로젝트 ID 정리.
	 * ID만 입력해도 되고, embed/edit/remix URL 전체를 넣어도 자동 추출한다.
	 *
	 * @param mixed $raw Raw value.
	 * @return string
	 */
	function goyoartdark_sanitize_unicorn_project_id( $raw ) {
		$s = sanitize_text_field( is_string( $raw ) ? $raw : '' );
		$s = trim( $s );
		if ( '' === $s ) {
			return '';
		}

		if ( preg_match( '#/(?:embed|edit|remix)/([a-zA-Z0-9]+)#', $s, $m ) && ! empty( $m[1] ) ) {
			return $m[1];
		}

		if ( preg_match( '#\b([a-zA-Z0-9]{10,})\b#', $s, $m ) && ! empty( $m[1] ) ) {
			return $m[1];
		}

		return preg_replace( '/[^a-zA-Z0-9]/', '', $s );
	}
endif;

if ( ! function_exists( 'goyoartdark_sanitize_unicorn_over_black' ) ) :
	/**
	 * Unicorn: 검은 화면 clear 시 뒤 배경(스크린) vs 투명 내보낸 씬(일반) 분기
	 *
	 * @param mixed $raw Raw value.
	 * @return string
	 */
	function goyoartdark_sanitize_unicorn_over_black( $raw ) {
		$s = (string) $raw;
		return 'normal' === $s ? 'normal' : 'screen';
	}
endif;

if ( ! function_exists( 'goyoartdark_sanitize_unicorn_effect_preset' ) ) :
	/**
	 * Unicorn 효과 프리셋 키 정리.
	 *
	 * @param mixed $raw Raw value.
	 * @return string
	 */
	function goyoartdark_sanitize_unicorn_effect_preset( $raw ) {
		$allowed = array(
			'default',
			'soft_particles_fog',
			'none',
		);
		$key     = sanitize_key( is_string( $raw ) ? $raw : '' );
		return in_array( $key, $allowed, true ) ? $key : 'default';
	}
endif;

if ( ! function_exists( 'goyoartdark_get_unicorn_selected_project_id' ) ) :
	/**
	 * 선택된 Unicorn 효과 프리셋에 맞는 프로젝트 ID를 반환.
	 *
	 * @return string
	 */
	function goyoartdark_get_unicorn_selected_project_id() {
		$preset = goyoartdark_sanitize_unicorn_effect_preset( get_theme_mod( 'goyo_unicorn_effect_preset', 'default' ) );
		if ( 'none' === $preset ) {
			return '';
		}
		$key_map = array(
			'default'            => 'goyo_unicorn_project_id',
			'soft_particles_fog' => 'goyo_unicorn_project_id_soft_particles_fog',
		);

		$default_id = (string) get_theme_mod( 'goyo_unicorn_project_id', 'yMdOzFD8aQSbrqNp2425' );
		$key        = isset( $key_map[ $preset ] ) ? $key_map[ $preset ] : 'goyo_unicorn_project_id';
		$selected   = (string) get_theme_mod( $key, '' );
		$selected   = goyoartdark_sanitize_unicorn_project_id( $selected );

		if ( '' !== $selected ) {
			return $selected;
		}

		return goyoartdark_sanitize_unicorn_project_id( $default_id );
	}
endif;

	if ( ! function_exists( 'goyoartdark_customizer_unicorn_hero' ) ) :
	/**
	 * Unicorn WebGL: 메인페이지 상단 섹션에 컨트롤 추가(섹션 자체는 customizer-main-page.php 에서 등록).
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager.
	 * @return void
	 */
	function goyoartdark_customizer_unicorn_hero( $wp_customize ) {
		$section_id = 'goyoartdark_main_page_top';
		$wp_customize->add_setting(
			'goyo_unicorn_effect_preset',
			array(
				'default'           => 'default',
				'sanitize_callback' => 'goyoartdark_sanitize_unicorn_effect_preset',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'goyo_unicorn_effect_preset',
			array(
				'label'       => __( 'Unicorn 효과 선택', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'goyo_unicorn_effect_preset',
				'type'        => 'select',
				'choices'     => array(
					'default'            => __( '기본(현재 효과)', 'goyoartdark' ),
					'soft_particles_fog' => __( 'Soft Particles Fog (테스트용)', 'goyoartdark' ),
					'none'               => __( '효과없음', 'goyoartdark' ),
				),
				'priority'    => 42,
			)
		);
		$wp_customize->add_setting(
			'goyo_unicorn_over_black',
			array(
				'default'           => 'screen',
				'sanitize_callback' => 'goyoartdark_sanitize_unicorn_over_black',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'goyo_unicorn_over_black',
			array(
				'label'       => __( 'Unicorn/검은 배경 위에 히어로 사진', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'goyo_unicorn_over_black',
				'type'        => 'select',
				'choices'     => array(
					'screen' => __( '스크린(기본) — Unicorn이 검은색으로 clear할 때 뒤 이미지가 보임', 'goyoartdark' ),
					'normal' => __( '일반 — Unicorn 씬이 진짜 투명(alpha)이면(배경 레이어 끔). 검정이면 뒤가 가림', 'goyoartdark' ),
				),
				'priority'    => 43,
			)
		);
		$wp_customize->add_setting(
			'goyo_unicorn_project_id',
			array(
				'default'           => 'yMdOzFD8aQSbrqNp2425',
				'sanitize_callback' => 'goyoartdark_sanitize_unicorn_project_id',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'goyo_unicorn_project_id',
			array(
				'label'       => __( '기본(현재 효과) 프로젝트 ID', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'goyo_unicorn_project_id',
				'type'        => 'text',
				'description' => __( '기본 효과 선택 시 사용됩니다. 비우면 WebGL을 불러오지 않습니다.', 'goyoartdark' ),
				'priority'    => 44,
			)
		);
	
		$wp_customize->add_setting(
			'goyo_unicorn_project_id_soft_particles_fog',
			array(
				'default'           => '',
				'sanitize_callback' => 'goyoartdark_sanitize_unicorn_project_id',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'goyo_unicorn_project_id_soft_particles_fog',
			array(
				'label'       => __( 'Soft Particles Fog 프로젝트 ID', 'goyoartdark' ),
				'section'     => $section_id,
				'settings'    => 'goyo_unicorn_project_id_soft_particles_fog',
				'type'        => 'text',
				'description' => __( '비우면 기본(현재 효과) 프로젝트 ID로 대체됩니다.', 'goyoartdark' ),
				'priority'    => 45,
			)
		);
	
	
	}
endif;
add_action( 'customize_register', 'goyoartdark_customizer_unicorn_hero', 10 );

if ( ! function_exists( 'goyoartdark_enqueue_customize_controls_theme_styles' ) ) :
	/**
	 * 사용자 정의 화면 사이드바 — 체크박스 토글 등 테마 전용 스타일.
	 *
	 * @return void
	 */
	function goyoartdark_enqueue_customize_controls_theme_styles() {
		$path = get_template_directory() . '/assets/css/customize-controls.css';
		if ( ! is_readable( $path ) ) {
			return;
		}
		wp_enqueue_style(
			'goyoartdark-customize-controls',
			get_template_directory_uri() . '/assets/css/customize-controls.css',
			array(),
			(string) filemtime( $path )
		);
	}
endif;
add_action( 'customize_controls_enqueue_scripts', 'goyoartdark_enqueue_customize_controls_theme_styles', 20 );
