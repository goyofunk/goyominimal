<?php
/**
 * 테마 사용자정의(theme_mod) 키·역할 레지스트리.
 *
 * 복구·리네이밍 작업 시 키를 섞어 쓰면 로고만 요청했는데 히어로 배경이 바뀌는 사고가 난다.
 * 코어 로고(custom_logo)·홈 히어로 배경(goyo_hero_font_back)·코어 사이트 배경(background_image)은 서로 무관함을 고정한다.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 * @since goyoartdark 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * theme_mod 및 관련 사용자정의 저장 키 정의 전용 클래스.
 *
 * 문자열 리터럴을 커스터마이저/숏코드에서 직접 반복하면 오타 시 치명적으로 어긋난다.
 */
final class Goyoartdark_Theme_Mod_Registry {

	/** WordPress 코어 — 사이트 아이덴티티 로고(첨부 ID). 패턴 헤더·get_custom_logo() 의 유일 근거. */
	public const CUSTOM_LOGO = 'custom_logo';

	/** 코어 사용자정의 사이트 배경(선택 플러그인/설정 경로). 이 테마 메인히어로와 혼동 금지. */
	public const CORE_BACKGROUND_IMAGE = 'background_image';

	/** 카카오/SNS 미리보기(OG). 테마 과거 접두 유지로 DB 호환만 함. 기능상 로고 아님. */
	public const KAKAO_PREVIEW_IMAGE = 'mytheme_kakao_image';

	/** 레거시: 예전 헤더 URL 로고. 신규 코드에서 읽지 말 것. DB에 남아 있을 수 있음. */
	public const LEGACY_HEADER_LOGO_URL = 'mytheme_logo';

	/** 레거시: 미사용. DB 잔존 가능. */
	public const LEGACY_HEADER_LOGO_BLACK_URL = 'mytheme_logoblack';

	/** 헤더 로고 CSS 폭(커스터마이저). */
	public const HEADER_LOGO_WIDTH = 'goyo_header_logo_width';

	/** 헤더 로고 CSS 마진(커스터마이저). */
	public const HEADER_LOGO_MARGIN = 'goyo_header_logo_margin';

	/**
	 * 이전 답변의 “아직 못 찾은 문제” 점검용 — DB·화면 체크 항목(실행 책임은 운영자).
	 *
	 * @return array<string, string>
	 */
	public static function audit_checklist_items(): array {
		return array(
			'db_theme_mod_current'           => '`wp_options` 에서 option_name LIKE \'theme_mods_%\' 로 stylesheet 와 과거 후보 확인.',
			'db_theme_mod_hero_logo_split'    => '\'theme_mods_goyoartdark\' 직렬화 내 custom_logo(ID) 확인.',
			'db_legacy_keys'                  => '`mytheme_logo`/`mytheme_logoblack` 키는 레거시. 신규 UI 없음; 값은 무시 가능. 로고 문제는 반드시 ' . self::CUSTOM_LOGO . ' 만 수정.',
			'db_background_confusion'         => '`' . self::CORE_BACKGROUND_IMAGE . '` 는 코어 배경 플로우. 메인히어로 배경은 MetaSlider 슬라이더가 직접 담당.',
			'fse_templates'                   => '`wp_posts` post_type=`wp_template` / `wp_template_part` 해당 테마 식별자와 동기 확인.',
			'fse_global_styles'               => '`wp_posts` post_type=`wp_global_styles` 활성 스타일 존재·변경 확인.',
			'screen_home_hero'                => '프론트 홈: 히어로 배경·슬라이드·카테고리 썸네일 분리 확인.',
			'screen_identity_header_footer'    => '사용자 정의·프론트: 로고·헤더 메뉴·푸터 문자열 확인.',
			'screen_customizer_vs_front'       => '미리보기 iframe 과 실제 홈 새로고침(강력 새로고침) 결과 일치 확인.',
			'repo_slug_namespace'             => '`npm run check:slug-namespace` 또는 php .cursor/rules/slug-namespace-audit.php 실행.',
		);
	}

	/**
	 * phpMyAdmin 복구·진단 시 붙여넣을 참고용 SQL 패턴(wp_ 접두사 가정).
	 *
	 * @return array<string, string>
	 */
	public static function audit_sql_hints(): array {
		return array(
			'theme_mods_list'        => 'SELECT option_name, LENGTH(option_value) AS bytes FROM wp_options WHERE option_name LIKE \'theme_mods_%\' ORDER BY bytes DESC;',
			'find_logo_in_mods'      => 'SELECT option_name FROM wp_options WHERE option_name LIKE \'theme_mods_%\' AND option_value LIKE \'%custom_logo%\' ;',
		);
	}
}
