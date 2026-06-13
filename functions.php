<?php
/**
 * 미니멀리즘 테마(자식 테마) functions.
 *
 * 공통 기능은 전부 부모 테마 goyobase 에 있다. 이 파일이 담당하는 것:
 *  1. 라이트 프로필·테마별 기본값 선언( goyobase inc/theme-profile.php 의 필터 ).
 *  2. 메인페이지 커스터마이저(섹션·슬로건 스타일·프론트 출력) 로드 — inc/customizer-main-page.php.
 *
 * 공통 CSS 는 부모가 제공하며, 이 테마의 style.css 는 라이트 컬러 오버라이드만 담는다.
 * 메인페이지 전용 스타일은 assets/css/front-page.css (자식 우선 로드).
 * 홈 히어로는 고정(fixed) 히어로가 아니라 .conWrap .mainhero 안의 MetaSlider 슬라이더가 직접 담당한다.
 *
 * 주의: 자식 functions.php 는 부모보다 먼저 로드된다 — 함수 정의·필터 등록만 할 것.
 *
 * @package WordPress
 * @subpackage Goyominimal
 * @since goyominimal 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 페이지/글당 리비전 보관 개수 제한 — DB 비대 방지(최근 10개만 유지, 자동저장은 별도).
add_filter(
	'wp_revisions_to_keep',
	function () {
		return 10;
	}
);

// 라이트 프로필: 페이지 전환 크리티컬 <head>·홈 레이아웃 인라인 CSS 의 스킴/배경.
add_filter(
	'goyo_theme_profile',
	function () {
		return array(
			'scheme' => 'light',
			'bg'     => '#f7f7f5',
		);
	}
);

// 테마별 기본값( theme_mod 가 저장되지 않은 항목에만 적용 ) — goyo_default() 가 읽는다.
add_filter(
	'goyo_theme_defaults',
	function () {
		return array(
			'main_page_disable_dark_mode' => true,
			'hero_slogan_color'           => '#fff157',
			'hero_subtext_opacity'        => 1,
			// 고정 히어로 스크립트 패키지 미사용 — Unicorn 컨트롤·unicorn-loader.js·mainhero-content-scroll-scale.js
			// 는 goyoartdark·goyoartlight 전용( 부모 enqueue.php·unicorn-hero.php·body-class.php 가 이 키를 읽음 ).
			'unicorn_enabled'             => false,
			'hero_scroll_scale_enabled'   => false,
			'header_menu_font_size'       => 'clamp(18px, 1.3vw, 22px)',
			'header_menu_font_opacity'    => 0.94,
			'header_submenu_font_color'   => '#606060',
			'sub_banner_min_height_empty' => 'clamp(400px, 40vw, 500px)',
			'header_logo_width'           => '188px; 188px; 100px',
		);
	}
);

// 히어로 기본 문구( 신규 설치·theme_mod 비어 있을 때 ) — 슬로건은 MetaSlider 캡션 마크업이 담당하므로 기본 슬로건만 선언.
add_filter(
	'goyo_hero_default_strings',
	function () {
		return array(
			'slogan' => 'A Creative Website, <br>Done in One Day',
		);
	}
);

// 사용자 정의(Customizer) '메인페이지' 섹션 — 이 테마 고유 구성.
require get_stylesheet_directory() . '/inc/customizer-main-page.php';

if ( ! function_exists( 'goyominimal_enqueue_cate_card_swiper' ) ) :
	/**
	 * 메인페이지 카테고리 카드(쿼리 루프 .cateCard) Swiper 변환 스크립트.
	 * Swiper 본체·CSS 는 부모(goyobase) 프론트 번들이 로드하므로 메인페이지에서만 enqueue 한다.
	 *
	 * @return void
	 */
	function goyominimal_enqueue_cate_card_swiper() {
		if ( ! function_exists( 'goyoartdark_is_effective_front_page_for_assets' ) || ! goyoartdark_is_effective_front_page_for_assets() ) {
			return;
		}
		$path = get_stylesheet_directory() . '/assets/js/cate-card-swiper.js';
		wp_enqueue_script(
			'goyominimal-cate-card-swiper',
			get_stylesheet_directory_uri() . '/assets/js/cate-card-swiper.js',
			array( 'goyoartdark-swiper' ),
			file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' ),
			true
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'goyominimal_enqueue_cate_card_swiper', 30 );
