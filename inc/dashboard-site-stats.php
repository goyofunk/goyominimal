<?php
/**
 * 프론트 조회·방문 기록 및 알림판(대시보드) 통계 위젯.
 *
 * @package goyoartdark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 옵션 키 (autoload 비활성화). */
const GOYOARTDARK_SITE_STATS_OPTION = 'goyoartdark_site_stats_v1';

/** 보관 일수. */
const GOYOARTDARK_SITE_STATS_RETENTION_DAYS = 120;

/** 쿠키: 방문자 일 1회 집계(브라우저당 당일 첫 방문만 u 증가). */
const GOYOARTDARK_SITE_STATS_COOKIE_VISITOR = 'goyoartdark_dva';

/**
 * 저장소 배열을 가져와 일관된 형태로 정규화한다.
 *
 * @return array{days: array<string, array{v:int,u:int}>}
 */
function goyoartdark_site_stats_get_store() {
	$raw = get_option( GOYOARTDARK_SITE_STATS_OPTION, array() );
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}
	if ( ! isset( $raw['days'] ) || ! is_array( $raw['days'] ) ) {
		$raw['days'] = array();
	}
	return $raw;
}

/**
 * 오래된 일자 키를 잘라 용량을 제한한다.
 *
 * @param array $store 저장소.
 * @return array
 */
function goyoartdark_site_stats_prune_store( $store ) {
	if ( empty( $store['days'] ) || ! is_array( $store['days'] ) ) {
		return $store;
	}
	$keys = array_keys( $store['days'] );
	sort( $keys );
	if ( count( $keys ) <= GOYOARTDARK_SITE_STATS_RETENTION_DAYS ) {
		return $store;
	}
	$drop = count( $keys ) - GOYOARTDARK_SITE_STATS_RETENTION_DAYS;
	for ( $i = 0; $i < $drop; $i++ ) {
		unset( $store['days'][ $keys[ $i ] ] );
	}
	return $store;
}

/**
 * 특정 일자 버킷을 가져오거나 초기화한다(구형 vn/un 키는 무시).
 *
 * @param array  $store 저장소.
 * @param string $day   Y-m-d.
 * @return array 저장소.
 */
function goyoartdark_site_stats_ensure_day( $store, $day ) {
	if ( ! isset( $store['days'][ $day ] ) || ! is_array( $store['days'][ $day ] ) ) {
		$store['days'][ $day ] = array(
			'v' => 0,
			'u' => 0,
		);
	}
	$b = $store['days'][ $day ];
	$store['days'][ $day ] = array(
		'v' => (int) ( $b['v'] ?? 0 ),
		'u' => (int) ( $b['u'] ?? 0 ),
	);
	return $store;
}

/**
 * 프론트 요청 1건을 집계한다. wp 후순위에서 메인 쿼리가 끝난 뒤 실행(출력 전이라 setcookie 가능).
 *
 * @return void
 */
function goyoartdark_site_stats_record_hit() {
	static $recorded = false;
	if ( $recorded ) {
		return;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}
	if ( is_feed() || is_trackback() ) {
		return;
	}
	if ( is_customize_preview() ) {
		return;
	}

	$qv_preview = isset( $_GET['preview'] ) ? sanitize_text_field( wp_unslash( $_GET['preview'] ) ) : '';
	if ( 'true' === $qv_preview || '1' === $qv_preview ) {
		if ( isset( $_GET['p'] ) || isset( $_GET['page_id'] ) || isset( $_GET['preview_id'] ) ) {
			return;
		}
	}

	$recorded = true;

	$day = wp_date( 'Y-m-d' );
	$ymd = wp_date( 'Ymd' );

	$store = goyoartdark_site_stats_get_store();
	$store = goyoartdark_site_stats_ensure_day( $store, $day );

	// 조회수: 페이지 1회 요청마다 +1.
	$store['days'][ $day ]['v']++;

	$cookie_path   = COOKIEPATH ? COOKIEPATH : '/';
	$cookie_domain = COOKIE_DOMAIN;
	$secure        = is_ssl();
	$exp           = time() + DAY_IN_SECONDS * 2;

	// 방문자(일 1회): IP+UA별 트랜지언트만으로 판단(쿠키는 초기화 후에도 남아 u++를 막는 경우가 있어 집계 조건에서 제외).
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$ua   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
	$fp   = md5( $ip . '|' . $ua );
	$tkey = 'goyoartdark_uv_' . $ymd . '_' . $fp;

	$already_today = ( false !== get_transient( $tkey ) );

	if ( ! $already_today ) {
		$store['days'][ $day ]['u']++;
		set_transient( $tkey, 1, 2 * DAY_IN_SECONDS );
		if ( PHP_VERSION_ID >= 70300 ) {
			$cookie_args = array(
				'expires'  => $exp,
				'path'     => $cookie_path,
				'secure'   => $secure,
				'httponly' => true,
				'samesite' => 'Lax',
			);
			if ( $cookie_domain ) {
				$cookie_args['domain'] = $cookie_domain;
			}
			setcookie( GOYOARTDARK_SITE_STATS_COOKIE_VISITOR, $ymd, $cookie_args );
		} else {
			setcookie( GOYOARTDARK_SITE_STATS_COOKIE_VISITOR, $ymd, $exp, $cookie_path, $cookie_domain, $secure, true );
		}
	}

	$store = goyoartdark_site_stats_prune_store( $store );
	update_option( GOYOARTDARK_SITE_STATS_OPTION, $store, false );
}
add_action( 'wp', 'goyoartdark_site_stats_record_hit', 999 );
add_action( 'template_redirect', 'goyoartdark_site_stats_record_hit', 999 );

/**
 * 대시보드용 차트 시리즈.
 *
 * @param int $span_days 일 수.
 * @return array{labels: string[], views: int[], visitors: int[]}
 */
function goyoartdark_site_stats_build_chart_arrays( $span_days = 35 ) {
	$store = goyoartdark_site_stats_get_store();
	$days  = isset( $store['days'] ) && is_array( $store['days'] ) ? $store['days'] : array();
	$tz    = wp_timezone();

	try {
		$base = new DateTimeImmutable( 'today', $tz );
	} catch ( Exception $e ) {
		$base = new DateTimeImmutable( 'now' );
	}

	$labels     = array();
	$out_labels = array();
	$v_series   = array();
	$u_series   = array();

	for ( $i = $span_days - 1; $i >= 0; $i-- ) {
		$dt = $base->modify( '-' . $i . ' days' );
		$d  = $dt->format( 'Y-m-d' );
		$labels[] = $d;
		if ( isset( $days[ $d ] ) && is_array( $days[ $d ] ) ) {
			$v_series[] = (int) ( $days[ $d ]['v'] ?? 0 );
			$u_series[] = (int) ( $days[ $d ]['u'] ?? 0 );
		} else {
			$v_series[] = 0;
			$u_series[] = 0;
		}

		$idx = count( $labels ) - 1;
		if ( 0 === $idx || '1' === $dt->format( 'j' ) ) {
			$out_labels[] = $dt->format( 'n' ) . '/' . $dt->format( 'j' );
		} else {
			$out_labels[] = $dt->format( 'j' );
		}
	}

	if ( ! empty( $out_labels ) ) {
		$out_labels[ count( $out_labels ) - 1 ] = __( '오늘', 'goyoartdark' );
	}

	return array(
		'rawDays'   => $labels,
		'labels'    => $out_labels,
		'views'     => $v_series,
		'visitors'  => $u_series,
	);
}

/**
 * 요약(오늘/어제/누적).
 *
 * @param array $store 저장소.
 * @return array{today: array, yesterday: array, total: array}
 */
function goyoartdark_site_stats_summaries( $store ) {
	$days = isset( $store['days'] ) && is_array( $store['days'] ) ? $store['days'] : array();
	$today = wp_date( 'Y-m-d' );
	try {
		$yesterday = ( new DateTimeImmutable( $today . ' 00:00:00', wp_timezone() ) )->modify( '-1 day' )->format( 'Y-m-d' );
	} catch ( Exception $e ) {
		$yesterday = wp_date( 'Y-m-d', strtotime( '-1 day', strtotime( $today . ' 00:00:00' ) ) );
	}

	$sum_v = $sum_u = 0;
	foreach ( $days as $bucket ) {
		if ( ! is_array( $bucket ) ) {
			continue;
		}
		$sum_v += (int) ( $bucket['v'] ?? 0 );
		$sum_u += (int) ( $bucket['u'] ?? 0 );
	}

	$t_bucket = $days[ $today ] ?? array( 'v' => 0, 'u' => 0 );
	$y_bucket = $days[ $yesterday ] ?? array( 'v' => 0, 'u' => 0 );

	return array(
		'today'     => array(
			'views'    => (int) ( $t_bucket['v'] ?? 0 ),
			'visitors' => (int) ( $t_bucket['u'] ?? 0 ),
		),
		'yesterday' => array(
			'views'    => (int) ( $y_bucket['v'] ?? 0 ),
			'visitors' => (int) ( $y_bucket['u'] ?? 0 ),
		),
		'total'     => array(
			'views'    => $sum_v,
			'visitors' => $sum_u,
		),
	);
}

/**
 * 대시보드 위젯 등록.
 *
 * @return void
 */
function goyoartdark_site_stats_register_dashboard_widget() {
	if ( ! current_user_can( 'read' ) ) {
		return;
	}
	wp_add_dashboard_widget(
		'goyoartdark_site_stats',
		__( '사이트 조회·방문 통계', 'goyoartdark' ),
		'goyoartdark_site_stats_render_widget'
	);
	if ( ! empty( goyoartdark_site_stats_get_quick_link_cards() ) ) {
		wp_add_dashboard_widget(
			'goyoartdark_site_stats_links',
			__( '관리 화면 바로가기', 'goyoartdark' ),
			'goyoartdark_site_stats_render_links_widget'
		);
	}
}
add_action( 'wp_dashboard_setup', 'goyoartdark_site_stats_register_dashboard_widget', 20 );

/**
 * 알림판 본문 열 상단에 통계 위젯을 올린다.
 *
 * @return void
 */
function goyoartdark_site_stats_dashboard_widget_to_top() {
	global $wp_meta_boxes;
	if ( ! isset( $wp_meta_boxes['dashboard']['normal']['core'] ) || ! is_array( $wp_meta_boxes['dashboard']['normal']['core'] ) ) {
		return;
	}
	$core = &$wp_meta_boxes['dashboard']['normal']['core'];
	$ordered = array();
	foreach ( array( 'goyoartdark_site_stats', 'goyoartdark_site_stats_links' ) as $id ) {
		if ( isset( $core[ $id ] ) ) {
			$ordered[ $id ] = $core[ $id ];
			unset( $core[ $id ] );
		}
	}
	if ( empty( $ordered ) ) {
		return;
	}
	$core = array_merge( $ordered, $core );
}
add_action( 'wp_dashboard_setup', 'goyoartdark_site_stats_dashboard_widget_to_top', 99 );

/**
 * 통계 포스트박스를 알림판 상단 전체 폭으로 옮김(파서가 해당 노드를 만난 직후 실행).
 *
 * DOMContentLoaded 이후에만 옮기면 칸 폭으로 먼저 페인트되어 깨져 보이므로 인라인으로 처리한다.
 * 바로가기 위젯이 뒤에 오면 같은 스크립트를 한 번 더 두어 links 박스를 shell 에 합친다(무해한 idempotent).
 *
 * @return void
 */
function goyoartdark_site_stats_print_dashboard_relocate_script() {
	echo '<script>';
	echo '(function(){var stats=document.getElementById("goyoartdark_site_stats");var links=document.getElementById("goyoartdark_site_stats_links");var dww=document.getElementById("dashboard-widgets-wrap");if(!stats||!dww)return;var shell=stats.closest(".goyoartdark-site-stats-fullwidth");if(!shell){shell=document.createElement("div");shell.className="goyoartdark-site-stats-fullwidth";stats.parentNode.insertBefore(shell,stats);}if(stats.getAttribute("data-goyo-fullwidth")!=="1"){shell.appendChild(stats);stats.setAttribute("data-goyo-fullwidth","1");}if(links&&links.getAttribute("data-goyo-fullwidth")!=="1"){shell.appendChild(links);links.setAttribute("data-goyo-fullwidth","1");}if(shell.parentNode!==dww){dww.insertBefore(shell,dww.firstChild);}else if(dww.firstChild!==shell){dww.insertBefore(shell,dww.firstChild);}})();';
	echo '</script>';
}

/**
 * KBoard 관리자 게시판 화면 URL(admin.php?page=kboard_admin_view_*).
 *
 * @param string $page `page` 쿼리 값(예: kboard_admin_view_2).
 * @return string
 */
function goyoartdark_site_stats_kboard_admin_page_url( $page ) {
	$page = sanitize_key( (string) $page );
	if ( '' === $page ) {
		return admin_url( 'admin.php' );
	}
	return add_query_arg( 'page', $page, admin_url( 'admin.php' ) );
}

/**
 * 커스터마이저 진입 후 돌아갈 관리 화면을 지정한다.
 *
 * @param string $admin_php_basename wp-admin 기준 파일(예: index.php, options-general.php).
 * @return string
 */
function goyoartdark_site_stats_customize_with_return_url( $admin_php_basename ) {
	$admin_php_basename = ltrim( (string) $admin_php_basename, '/' );
	$return             = admin_url( $admin_php_basename );
	return add_query_arg( 'return', rawurlencode( $return ), admin_url( 'customize.php' ) );
}

/**
 * 알림판 빠른 링크(권한·KBoard 여부에 따라 항목 필터).
 *
 * @return array<int, array{icon:string,title:string,description:string,button:string,url:string}>
 */
function goyoartdark_site_stats_get_quick_link_items() {
	$contact_kboard_page = (string) apply_filters( 'goyoartdark_dashboard_kboard_contact_admin_page', 'kboard_admin_view_2' );
	$qna_kboard_page     = (string) apply_filters( 'goyoartdark_dashboard_kboard_qna_admin_page', 'kboard_admin_view_1' );

	$popup_url = apply_filters( 'goyoartdark_dashboard_link_popup', admin_url( 'admin.php?page=bd-ux-popup' ) );

	$menu_url = admin_url( 'nav-menus.php' );

	$items = array();

	if ( defined( 'KBOARD_VERSION' ) && current_user_can( 'manage_kboard' ) ) {
		$items[] = array(
			'icon'        => 'pencil-square',
			'title'       => __( '1:1문의 글목록', 'goyoartdark' ),
			'description' => __( 'Contact(1:1문의) 게시판에 작성된 글목록 보러 가기', 'goyoartdark' ),
			'button'      => __( '1:1문의 글목록', 'goyoartdark' ),
			'url'         => goyoartdark_site_stats_kboard_admin_page_url( $contact_kboard_page ),
		);
		$items[] = array(
			'icon'        => 'pencil-square',
			'title'       => __( '질문과답변 글목록', 'goyoartdark' ),
			'description' => __( '질문과답변 게시판에 작성된 글목록 보러 가기', 'goyoartdark' ),
			'button'      => __( '질문과답변 글목록', 'goyoartdark' ),
			'url'         => goyoartdark_site_stats_kboard_admin_page_url( $qna_kboard_page ),
		);
	}

	if ( current_user_can( 'edit_theme_options' ) ) {
		$items[] = array(
			'icon'        => 'file-earmark-text',
			'title'       => __( '팝업관리', 'goyoartdark' ),
			'description' => __( '팝업창을 활성화하여 첫화면에 보이도록 설정합니다.', 'goyoartdark' ),
			'button'      => __( '팝업관리', 'goyoartdark' ),
			'url'         => $popup_url,
		);
	}

	if ( current_user_can( 'edit_pages' ) ) {
		$slogan_customize = apply_filters( 'goyoartdark_dashboard_link_main_slogan_customize', goyoartdark_site_stats_customize_with_return_url( 'index.php' ) );
		$items[]           = array(
			'icon'        => 'file-earmark-text',
			'title'       => __( '메인페이지 슬로건 ', 'goyoartdark' ),
			'description' => __( '메인페이지의 슬로건 문구를 수정하세요.', 'goyoartdark' ),
			'button'      => __( '메인 슬로건수정', 'goyoartdark' ),
			'url'         => $slogan_customize,
		);
	}

	if ( current_user_can( 'edit_theme_options' ) ) {
		$items[] = array(
			'icon'        => 'link-45deg',
			'title'       => __( '메뉴수정', 'goyoartdark' ),
			'description' => __( '홈페이지 상단의 메뉴를 추가하거나 변경해 보세요.', 'goyoartdark' ),
			'button'      => __( '메뉴수정', 'goyoartdark' ),
			'url'         => $menu_url,
		);
	}

	if ( current_user_can( 'manage_options' ) ) {
		$common_settings_url = apply_filters(
			'goyoartdark_dashboard_link_site_common_settings',
			goyoartdark_site_stats_customize_with_return_url( 'options-general.php' )
		);
		$items[]             = array(
			'icon'        => 'sliders',
			'title'       => __( '사이트 공통정보설정', 'goyoartdark' ),
			'description' => __( '홈페이지의 기본환경을 설정합니다.', 'goyoartdark' ),
			'button'      => __( '공통정보 설정', 'goyoartdark' ),
			'url'         => $common_settings_url,
		);
	}

	return apply_filters( 'goyoartdark_site_stats_quick_link_items', $items );
}

/**
 * 빠른 링크에 실제로 그릴 카드만 필터(아이콘 화이트리스트).
 *
 * @return array<int, array<string, string>>
 */
function goyoartdark_site_stats_get_quick_link_cards() {
	$items = goyoartdark_site_stats_get_quick_link_items();
	$allow = array_flip( array( 'pencil-square', 'file-earmark-text', 'link-45deg', 'sliders' ) );
	$out   = array();
	foreach ( $items as $item ) {
		$icon = isset( $item['icon'] ) ? (string) $item['icon'] : '';
		if ( ! isset( $allow[ $icon ] ) ) {
			continue;
		}
		$out[] = $item;
	}
	return $out;
}

/**
 * 빠른 링크 카드 마크업 출력.
 *
 * @param array<int, array<string, string>>|null $items null 이면 카드만 다시 조회.
 * @return void
 */
function goyoartdark_site_stats_render_quick_links( $items = null ) {
	if ( null === $items ) {
		$items = goyoartdark_site_stats_get_quick_link_cards();
	}
	if ( empty( $items ) ) {
		return;
	}
	echo '<div class="goyoartdark-site-stats__quicklinks">';
	echo '<div class="goyoartdark-site-stats__quickgrid" role="list">';
	foreach ( $items as $item ) {
		$icon = isset( $item['icon'] ) ? (string) $item['icon'] : '';
		echo '<div class="goyoartdark-site-stats__qcard" role="listitem">';
		echo '<div class="goyoartdark-site-stats__qcard-head">';
		echo '<span class="goyoartdark-site-stats__qcard-icon"><i class="bi bi-' . esc_attr( $icon ) . '" aria-hidden="true"></i></span>';
		echo '<div class="goyoartdark-site-stats__qcard-text">';
		echo '<p class="goyoartdark-site-stats__qcard-title">' . esc_html( $item['title'] ?? '' ) . '</p>';
		echo '<p class="goyoartdark-site-stats__qcard-desc">' . esc_html( $item['description'] ?? '' ) . '</p>';
		echo '<a class="goyoartdark-site-stats__qcard-btn" href="' . esc_url( $item['url'] ?? '#' ) . '">' . esc_html( $item['button'] ?? '' ) . '</a>';
		echo '</div></div>';
		echo '</div>';
	}
	echo '</div></div>';
}

/**
 * 위젯 본문(차트는 JS).
 *
 * @return void
 */
function goyoartdark_site_stats_render_widget() {
	echo '<div id="goyoartdark-site-stats-widget" class="goyoartdark-site-stats">';
	echo '<div class="goyoartdark-site-stats__inner">';
	echo '<div class="goyoartdark-site-stats__summary">';
	echo '<div class="goyoartdark-site-stats__kpi-panel" role="group" aria-label="' . esc_attr__( '조회수', 'goyoartdark' ) . '">';
	echo '<div class="goyoartdark-site-stats__kpi-grid">';
	echo '<div class="goyoartdark-site-stats__kpi-grid-labels">';
	echo '<span class="goyoartdark-site-stats__kpi-grid-lab">' . esc_html__( '오늘 조회수', 'goyoartdark' ) . '</span>';
	echo '<span class="goyoartdark-site-stats__kpi-grid-lab">' . esc_html__( '어제 조회수', 'goyoartdark' ) . '</span>';
	echo '<span class="goyoartdark-site-stats__kpi-grid-lab">' . esc_html__( '누적 조회수', 'goyoartdark' ) . '</span>';
	echo '</div>';
	echo '<div class="goyoartdark-site-stats__kpi-grid-values">';
	echo '<strong class="goyoartdark-site-stats__val" data-kpi="today-views">—</strong>';
	echo '<strong class="goyoartdark-site-stats__val" data-kpi="yesterday-views">—</strong>';
	echo '<strong class="goyoartdark-site-stats__val" data-kpi="total-views">—</strong>';
	echo '</div></div></div>';

	echo '<span class="goyoartdark-site-stats__vline" aria-hidden="true"></span>';

	echo '<div class="goyoartdark-site-stats__kpi-panel" role="group" aria-label="' . esc_attr__( '방문자', 'goyoartdark' ) . '">';
	echo '<div class="goyoartdark-site-stats__kpi-grid">';
	echo '<div class="goyoartdark-site-stats__kpi-grid-labels">';
	echo '<span class="goyoartdark-site-stats__kpi-grid-lab">' . esc_html__( '오늘 방문자', 'goyoartdark' ) . '</span>';
	echo '<span class="goyoartdark-site-stats__kpi-grid-lab">' . esc_html__( '어제 방문자', 'goyoartdark' ) . '</span>';
	echo '<span class="goyoartdark-site-stats__kpi-grid-lab">' . esc_html__( '누적 방문자', 'goyoartdark' ) . '</span>';
	echo '</div>';
	echo '<div class="goyoartdark-site-stats__kpi-grid-values">';
	echo '<strong class="goyoartdark-site-stats__val" data-kpi="today-visitors">—</strong>';
	echo '<strong class="goyoartdark-site-stats__val" data-kpi="yesterday-visitors">—</strong>';
	echo '<strong class="goyoartdark-site-stats__val" data-kpi="total-visitors">—</strong>';
	echo '</div></div></div>';

	if ( current_user_can( 'manage_options' ) ) {
		$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=goyoartdark_site_stats_export' ), 'goyoartdark_site_stats_export' );
		echo '<div class="goyoartdark-site-stats__summary-actions">';
		echo '<a class="button button-secondary goyoartdark-site-stats__action-export" href="' . esc_url( $export_url ) . '">' . esc_html__( '엑셀로 내려받기', 'goyoartdark' ) . '</a>';
		echo '<form class="goyoartdark-site-stats__reset-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'goyoartdark_site_stats_reset' );
		echo '<input type="hidden" name="action" value="goyoartdark_site_stats_reset" />';
		echo '<button type="submit" class="button button-secondary goyoartdark-site-stats__action-reset" onclick="return confirm(\'' . esc_js( __( '모든 일별 조회수·방문자 기록을 삭제하고 0부터 다시 집계할까요?', 'goyoartdark' ) ) . '\');">' . esc_html__( '초기화', 'goyoartdark' ) . '</button>';
		echo '</form>';
		echo '</div>';
	}

	echo '</div>';

	echo '<div class="goyoartdark-site-stats__legend">';
	echo '<span class="goyoartdark-site-stats__legend-item goyoartdark-site-stats__legend-views"><span class="goyoartdark-site-stats__legend-swatch"></span> ' . esc_html__( '조회수', 'goyoartdark' ) . '</span>';
	echo '<span class="goyoartdark-site-stats__legend-item goyoartdark-site-stats__legend-visitors"><span class="goyoartdark-site-stats__legend-swatch"></span> ' . esc_html__( '방문자', 'goyoartdark' ) . '</span>';
	echo '</div>';
	echo '<div class="goyoartdark-site-stats__chart-wrap"><canvas id="goyoartdark-site-stats-chart" aria-label="' . esc_attr__( '조회수·방문자 추이', 'goyoartdark' ) . '"></canvas></div>';

	echo '</div></div>';
	goyoartdark_site_stats_print_dashboard_relocate_script();
}

/**
 * 바로가기 전용 대시보드 위젯 본문(포스트박스 ·inside 분리).
 *
 * @return void
 */
function goyoartdark_site_stats_render_links_widget() {
	$quick_cards = goyoartdark_site_stats_get_quick_link_cards();
	if ( empty( $quick_cards ) ) {
		return;
	}
	echo '<div class="goyoartdark-site-stats goyoartdark-site-stats--links">';
	goyoartdark_site_stats_render_quick_links( $quick_cards );
	echo '</div>';
	goyoartdark_site_stats_print_dashboard_relocate_script();
}

/**
 * 관리자 리소스(알림판만).
 *
 * @param string $hook_suffix 화면 id.
 * @return void
 */
function goyoartdark_site_stats_admin_assets( $hook_suffix ) {
	if ( 'index.php' !== $hook_suffix ) {
		return;
	}

	$theme   = wp_get_theme();
	$ver     = $theme->get( 'Version' );
	$css_path = get_template_directory() . '/assets/css/admin-dashboard-stats.css';
	$js_path  = get_template_directory() . '/assets/js/admin-dashboard-stats.js';
	$css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : $ver;
	$js_ver   = file_exists( $js_path ) ? (string) filemtime( $js_path ) : $ver;

	wp_enqueue_style(
		'goyoartdark-admin-bi',
		get_template_directory_uri() . '/assets/icons/bootstrap-icons.css',
		array(),
		$ver
	);
	wp_enqueue_style(
		'goyoartdark-admin-dashboard-stats',
		get_template_directory_uri() . '/assets/css/admin-dashboard-stats.css',
		array( 'goyoartdark-admin-bi' ),
		$css_ver
	);

	wp_enqueue_script(
		'goyoartdark-admin-dashboard-stats',
		get_template_directory_uri() . '/assets/js/admin-dashboard-stats.js',
		array(),
		$js_ver,
		true
	);

	$store   = goyoartdark_site_stats_get_store();
	$chart   = goyoartdark_site_stats_build_chart_arrays( 35 );
	$summary = goyoartdark_site_stats_summaries( $store );

	wp_localize_script(
		'goyoartdark-admin-dashboard-stats',
		'goyoartdarkDashStats',
		array(
			'labels'  => $chart['labels'],
			'series'  => array(
				'views'    => $chart['views'],
				'visitors' => $chart['visitors'],
			),
			'summary' => $summary,
			'strings' => array(
				'chartTooltipViews'    => __( '조회수', 'goyoartdark' ),
				'chartTooltipVisitors' => __( '방문자', 'goyoartdark' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'goyoartdark_site_stats_admin_assets' );

/**
 * 방문자 1일 1회 집계에 쓰는 트랜지언트를 모두 삭제한다(초기화 후 같은 날 다시 올라가도록).
 *
 * @return void
 */
function goyoartdark_site_stats_delete_visitor_transients() {
	global $wpdb;
	$like = $wpdb->esc_like( '_transient_goyoartdark_uv_' ) . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- 초기화 시 일괄 정리만 허용.
	$rows = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return;
	}
	foreach ( $rows as $option_name ) {
		$key = str_replace( '_transient_', '', (string) $option_name );
		delete_transient( $key );
	}
}

/**
 * 통계 저장소 전체 삭제(관리자).
 *
 * @return void
 */
function goyoartdark_site_stats_handle_admin_reset() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( '권한이 없습니다.', 'goyoartdark' ) );
	}
	check_admin_referer( 'goyoartdark_site_stats_reset' );
	goyoartdark_site_stats_delete_visitor_transients();
	update_option( GOYOARTDARK_SITE_STATS_OPTION, array( 'days' => array() ), false );
	wp_safe_redirect( admin_url( 'index.php?goyo_stats_reset=1' ) );
	exit;
}
add_action( 'admin_post_goyoartdark_site_stats_reset', 'goyoartdark_site_stats_handle_admin_reset' );

/**
 * 일별 조회·방문 CSV 내보내기(UTF-8 BOM, 엑셀에서 열기 가능).
 *
 * @return void
 */
function goyoartdark_site_stats_handle_admin_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( '권한이 없습니다.', 'goyoartdark' ) );
	}
	check_admin_referer( 'goyoartdark_site_stats_export' );

	// 그래프와 동일한 일수(기본 35일): 저장에 없는 날도 0으로 채워 내보낸다.
	$chart = goyoartdark_site_stats_build_chart_arrays( 35 );
	$raw   = isset( $chart['rawDays'] ) && is_array( $chart['rawDays'] ) ? $chart['rawDays'] : array();
	$vs    = isset( $chart['views'] ) && is_array( $chart['views'] ) ? $chart['views'] : array();
	$us    = isset( $chart['visitors'] ) && is_array( $chart['visitors'] ) ? $chart['visitors'] : array();

	$fn = 'hit_' . wp_date( 'Ymd_His' ) . '.csv';
	// 헤더에 비ASCII 파일명을 쓰지 않아 브라우저 호환을 유지한다.
	$fn_safe = preg_replace( '/[^a-zA-Z0-9._-]/', '', $fn );
	if ( '' === $fn_safe ) {
		$fn_safe = 'hit_export.csv';
	}

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $fn_safe . '"' );

	$out = fopen( 'php://output', 'w' );
	if ( false === $out ) {
		wp_die( esc_html__( '파일을 만들 수 없습니다.', 'goyoartdark' ) );
	}
	// 엑셀에서 한글 깨짐 방지.
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
	fputcsv( $out, array( __( '날짜', 'goyoartdark' ), __( '조회수', 'goyoartdark' ), __( '방문수', 'goyoartdark' ) ) );
	$n = count( $raw );
	for ( $i = 0; $i < $n; $i++ ) {
		fputcsv(
			$out,
			array(
				(string) $raw[ $i ],
				(int) ( $vs[ $i ] ?? 0 ),
				(int) ( $us[ $i ] ?? 0 ),
			)
		);
	}
	fclose( $out );
	exit;
}
add_action( 'admin_post_goyoartdark_site_stats_export', 'goyoartdark_site_stats_handle_admin_export' );

/**
 * 초기화 완료 알림.
 *
 * @return void
 */
function goyoartdark_site_stats_reset_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'dashboard' !== $screen->id ) {
		return;
	}
	if ( empty( $_GET['goyo_stats_reset'] ) || '1' !== sanitize_text_field( wp_unslash( $_GET['goyo_stats_reset'] ) ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	wp_admin_notice(
		esc_html__( '조회·방문 통계를 초기화했습니다.', 'goyoartdark' ),
		array(
			'type'           => 'success',
			'dismissible'    => true,
			'id'             => 'goyoartdark_site_stats_reset_notice',
			'paragraph_wrap' => false,
		)
	);
}
add_action( 'admin_notices', 'goyoartdark_site_stats_reset_admin_notice' );
