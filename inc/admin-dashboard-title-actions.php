<?php
/**
 * 알림판(대시보드) 화면: 페이지 제목 옆 바로가기 링크(코어에 해당 훅이 없어 푸터에서 한 줄 삽입).
 *
 * @package goyoartdark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 사용 매뉴얼(외부 FAQ 카테고리) 고정 URL — 새 탭 */
const GOYOARTDARK_MANUAL_URL_DASHBOARD = 'https://goyofunk.com/category/mzine/faq/';

if ( ! function_exists( 'goyoartdark_dashboard_title_actions_styles' ) ) :
	/**
	 * 제목과 버튼(.page-title-action) 줄바꿈·간격 보정.
	 *
	 * @return void
	 */
	function goyoartdark_dashboard_title_actions_styles() {
		echo '<style>#wpbody-content .wrap > h1{display:inline-block;margin-right:10px;padding-bottom:20px;vertical-align:middle;}#wpbody-content .wrap > h1 + .goyoartdark-dashboard-title-actions{display:inline-flex;gap:10px;padding:0 0 0 10px;vertical-align:middle;}#wpbody-content .wrap > h1 + .goyoartdark-dashboard-title-actions .page-title-action{margin-left:0;min-height:38px;padding:6px 18px;font-size:14px;font-weight:400;line-height:1.2;border-radius:4px;border-width:1px;display:flex;align-items:center;justify-content:center;background:#ffffff;}</style>' . "\n";
	}
endif;

if ( ! function_exists( 'goyoartdark_dashboard_title_actions_script' ) ) :
	/**
	 * 제목 h1 바로 다음에 홈·매뉴얼 링크 삽입.
	 *
	 * @return void
	 */
	function goyoartdark_dashboard_title_actions_script() {
		if ( ! current_user_can( 'read' ) ) {
			return;
		}

		$manual_esc = esc_url( GOYOARTDARK_MANUAL_URL_DASHBOARD );

		$payload = array(
			'homeUrl'     => esc_url( home_url( '/' ) ),
			'manualUrl'   => $manual_esc,
			'homeLabel'   => __( '홈페이지 (새창)', 'goyoartdark' ),
			'manualLabel' => __( '사용매뉴얼 (새창)', 'goyoartdark' ),
		);

		$json = wp_json_encode(
			$payload,
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
		);

		wp_print_inline_script_tag(
			'(function(){var d=' . $json . ';var h=document.querySelector("#wpbody-content .wrap > h1");if(!h||!d.homeUrl){return;}var sp=document.createElement("span");sp.className="goyoartdark-dashboard-title-actions";function mk(url,label){var a=document.createElement("a");a.className="page-title-action";a.href=url;a.target="_blank";a.rel="noopener noreferrer";a.textContent=label;return a;}sp.appendChild(mk(d.homeUrl,d.homeLabel));if(d.manualUrl){sp.appendChild(mk(d.manualUrl,d.manualLabel));}h.insertAdjacentElement("afterend",sp);})();',
			array( 'type' => 'text/javascript' )
		);
	}
endif;

add_action( 'admin_head-index.php', 'goyoartdark_dashboard_title_actions_styles' );
add_action( 'admin_footer-index.php', 'goyoartdark_dashboard_title_actions_script', 5 );
