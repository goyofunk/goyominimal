<?php
/**
 * 프런트 </body> 직전에 그대로 출력되는 사용자 정의 영역입니다.
 * 예전 클래식 테마의 footer.php 에 넣던 광고 스크립트, 추적 코드, 서드파티 스니펫 등을
 *
 * @package goyoartdark
 */

defined( 'ABSPATH' ) || exit;

// 공통정보(Customizer) 기반 채팅 버튼 값. trim 으로 공백 입력은 빈 값 처리.
$goyoartdark_kakaochat_url    = trim( (string) get_theme_mod( 'kakaochat_url', '' ) );
$goyoartdark_navertalk_url    = trim( (string) get_theme_mod( 'navertalk_url', '' ) );
$goyoartdark_has_chat_buttons = ( $goyoartdark_kakaochat_url || $goyoartdark_navertalk_url );
?>
<div class="floatRight<?php echo $goyoartdark_has_chat_buttons ? '' : ' no-chat-buttons'; ?>">
	<?php if ( $goyoartdark_kakaochat_url ) : ?>
		<div class="kakaochat edit_kakaochat_url" data-tooltip="<?php esc_attr_e( '카카오채팅', 'goyoartdark' ); ?>">
			<a href="<?php echo esc_url( $goyoartdark_kakaochat_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( '카카오톡 채팅', 'goyoartdark' ); ?>">
				<i class="bi bi-chat-dots-fill"></i>
			</a>
		</div>
	<?php endif; ?>
	<?php if ( $goyoartdark_navertalk_url ) : ?>
		<div class="navertalk" data-tooltip="<?php esc_attr_e( '네이버톡', 'goyoartdark' ); ?>">
			<a href="<?php echo esc_url( $goyoartdark_navertalk_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( '네이버 톡톡', 'goyoartdark' ); ?>">
				<i class="bi bi-chat-dots-fill"></i>
			</a>
		</div>
	<?php endif; ?>
	<a href="#" class="gotoTop" aria-label="<?php esc_attr_e( '맨 위로', 'goyoartdark' ); ?>"><i class="bi bi-arrow-up"></i></a>
</div>
