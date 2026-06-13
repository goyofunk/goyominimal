<?php
/**
 * 사용자 정의 '메인페이지' — 상위 패널 + '메인페이지 슬로건' 섹션 등록( artlight 와 동일 골격 ).
 *
 * 부모(goyobase) unicorn-hero.php 의 Unicorn 컨트롤은 이 테마에서 제외( unicorn_enabled => false ).
 *
 * @package WordPress
 * @subpackage Goyoartdark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 상위 패널 '메인페이지' — 다른 섹션보다 먼저 등록.
 *
 * @param WP_Customize_Manager $wp_customize 매니저.
 * @return void
 */
function goyobase_register_main_page_customizer_panel( $wp_customize ) {
	if ( $wp_customize->get_panel( 'goyo_main_page_panel' ) ) {
		return;
	}
	$wp_customize->add_panel(
		'goyo_main_page_panel',
		array(
			'title'    => __( '메인페이지', 'goyoartdark' ),
			'priority' => 31,
		)
	);
}
add_action( 'customize_register', 'goyobase_register_main_page_customizer_panel', 6 );

/**
 * '메인페이지 슬로건' 섹션(슬로건 스타일·검색) — 컨트롤 등록 전에 먼저 만든다.
 *
 * @param WP_Customize_Manager $wp_customize 매니저.
 * @return void
 */
function goyobase_register_main_page_top_section( $wp_customize ) {
	if ( $wp_customize->get_section( 'goyo_main_page_top' ) ) {
		return;
	}
	$wp_customize->add_section(
		'goyo_main_page_top',
		array(
			'title'    => __( '메인페이지 슬로건', 'goyoartdark' ),
			'panel'    => 'goyo_main_page_panel',
			'priority' => 1,
		)
	);
}
add_action( 'customize_register', 'goyobase_register_main_page_top_section', 7 );
