<?php
/**
 * Title: Footer
 * Slug: goyoartdark/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: Footer with navigation, contact info and copyright.
 *
 * 판매용 테마 주의: 푸터 메뉴는 core/navigation 블록이 아니라 커스텀 다이내믹 블록
 *                 goyoartdark/site-menu 를 사용한다. 이 블록은 register_nav_menus()
 *                 로 등록된 theme_location( footer-menu )을 직접 렌더하므로
 *                 사이트 에디터 저장 시 다른 메뉴로 자동 교체되는 문제가 발생하지 않는다.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 * @since goyoartdark 1.0
 *
 * 사용자 정의(공통정보) 푸터 문자열 기본값은 inc/customizer.php 의 해당 add_setting default 와 같게 둔다.
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
	<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"align":"full","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center","verticalAlignment":"top"}} -->
		<div class="wp-block-group alignfull">
			<!-- wp:columns -->
			<div class="wp-block-columns">
				<!-- wp:column {"width":"100%"} -->
				<div class="wp-block-column" style="flex-basis:100%">
					<!-- wp:goyoartdark/site-menu {"location":"footer-menu","containerTag":"nav","containerClass":"footer-navigation","menuClass":"footer-nav","depth":1,"ariaLabel":"푸터 메뉴"} /-->

					<!-- wp:paragraph {"align":"center"} -->
					<p class="has-text-align-center"><?php echo wp_kses_post( get_theme_mod( 'goyoartdark_footer_secondary_text', '<span>대표전화</span> 02-731-2120 <span>이메일</span> goyofunkstudio@naver.com <span>주소</span> 서울특별시 중구 세종대로 110 (04524)' ) ); ?></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"align":"center"} -->
					<p class="has-text-align-center"><?php echo esc_html( get_theme_mod( 'goyoartdark_footer_copyright_text', 'COPYRIGHT © 고요펑크 ALL RIGHTS RESERVED.' ) ); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:column -->
			</div>
			<!-- /wp:columns -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
