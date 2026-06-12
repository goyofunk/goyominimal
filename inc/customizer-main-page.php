<?php
/**
 * 사용자 정의: '메인페이지' 패널 — 로더 (자식 테마 고유 구성).
 *
 * 이 테마가 갖는 것(테마별로 패널명·항목 구성이 다른 부분):
 *  - main-page-panel.php    : '메인페이지' 섹션 등록( goyominimal 은 패널 없이 단일 섹션 ).
 *  - main-page-hero.php     : 슬로건 스타일(정렬·캡션 배경·폰트·색)·검색 아이콘 컨트롤.
 *  - main-page-frontend.php : 슬로건/보조문구 CSS 변수·캡션 배경 인라인 출력 + 정렬 body class.
 *
 * goyominimal 은 고정 히어로·3열 스와이프·갤러리 컨트롤이 없고, 홈 히어로는 MetaSlider
 * 슬라이더( .conWrap .mainhero )가 직접 담당한다 — 슬로건 텍스트도 슬라이더 캡션 마크업 소속.
 *
 * 부모(goyobase)가 갖는 것(모든 자식 공유): sanitize 콜백·selective refresh partial·Unicorn 컨트롤
 * — goyobase/inc/customizer/main-page-*.php · unicorn-hero.php 참조.
 *
 * 자식 테마 소속이므로 get_stylesheet_directory() 로 로드한다.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_stylesheet_directory() . '/inc/customizer/main-page-panel.php';
require get_stylesheet_directory() . '/inc/customizer/main-page-hero.php';
require get_stylesheet_directory() . '/inc/customizer/main-page-frontend.php';
