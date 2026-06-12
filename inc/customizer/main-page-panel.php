<?php
/**
 * 사용자 정의 '메인페이지' — 섹션 등록.
 *
 * goyominimal 은 메인페이지 항목이 적어 상위 패널 없이 단일 섹션 'goyo_main_page_top' 만 쓴다.
 * 부모(goyobase) unicorn-hero.php 의 Unicorn 컨트롤도 이 섹션에 붙는다.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * '메인페이지' 섹션(슬로건 스타일·검색·Unicorn) — 컨트롤 등록 전에 먼저 만든다.
 *
 * @param WP_Customize_Manager $wp_customize 매니저.
 * @return void
 */
function goyoartdark_register_main_page_top_section( $wp_customize ) {
	if ( $wp_customize->get_section( 'goyo_main_page_top' ) ) {
		return;
	}
	$wp_customize->add_section(
		'goyo_main_page_top',
		array(
			'title'    => __( '메인페이지', 'goyoartdark' ),
			'priority' => 31,
		)
	);
}
add_action( 'customize_register', 'goyoartdark_register_main_page_top_section', 7 );
