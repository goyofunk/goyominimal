<?php
/**
 * 커스텀 다이내믹 블록 등록.
 *
 * 판매용 테마에서 고객마다 달라지는 `wp_navigation` 포스트 ID 의존성을 제거하기 위해,
 * 클래식 테마 메뉴 로케이션( register_nav_menus )을 직접 출력하는 전용 블록을 제공한다.
 *
 * 등록 블록:
 *   - goyoartdark/site-menu   : 클래식 메뉴 로케이션을 wp_nav_menu() 로 렌더.
 *   - goyoartdark/site-header : 헤더 내비( 주 메뉴 · SNS · 검색 · 모바일 메뉴 )를 렌더.
 *                               로고 및 `.container` 래퍼는 patterns/header.php 에서 직접 처리한다.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 * @since goyoartdark 1.0
 */



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// 사이트 메뉴 블록 미리보기를 위한 에디터 전용 JS 등록 (ServerSideRender 로 render_callback 결과 미리보기).
if ( ! function_exists( 'goyoartdark_enqueue_block_editor_assets' ) ) :
	/**
	 * 블록 에디터 전용 자산 로드.
	 *
	 * @since goyoartdark 1.0
	 * @return void
	 */
	function goyoartdark_enqueue_block_editor_assets() {
		wp_enqueue_script(
			'goyoartdark-blocks',
			get_parent_theme_file_uri( 'assets/js/blocks.js' ),
			array( 'wp-blocks', 'wp-element', 'wp-i18n' ),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
endif;
add_action( 'enqueue_block_editor_assets', 'goyoartdark_enqueue_block_editor_assets' );



// 사이트 메뉴(클래식 로케이션 기반) 블록 등록.
if ( ! function_exists( 'goyoartdark_register_site_menu_block' ) ) :
	/**
	 * goyoartdark/site-menu 다이내믹 블록 등록.
	 *
	 * 사이트 에디터에서 ServerSideRender 로 미리보기되고, 프런트엔드는
	 * wp_nav_menu( [ theme_location => $location ] ) 결과를 반환한다.
	 *
	 * @since goyoartdark 1.0
	 * @return void
	 */
	function goyoartdark_register_site_menu_block() {
		register_block_type(
			'goyoartdark/site-menu',
			array(
				'api_version'     => 2,
				'title'           => __( '사이트 메뉴', 'goyoartdark' ),
				'description'     => __( '외모 → 메뉴 에서 해당 위치( Primary / Footer / Sidebar )에 할당된 메뉴를 출력합니다.', 'goyoartdark' ),
				'category'        => 'theme',
				'icon'            => 'menu',
				'keywords'        => array( 'menu', 'nav', '메뉴', '네비게이션' ),
				'attributes'      => array(
					'location'       => array(
						'type'    => 'string',
						'default' => 'primary-menu',
					),
					'containerTag'   => array(
						'type'    => 'string',
						'default' => 'nav',
					),
					'containerClass' => array(
						'type'    => 'string',
						'default' => '',
					),
					'menuClass'      => array(
						'type'    => 'string',
						'default' => 'menu',
					),
					'depth'          => array(
						'type'    => 'number',
						'default' => 2,
					),
					'ariaLabel'      => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'supports'        => array(
					'html'             => false,
					'align'            => false,
					'customClassName'  => false,
				),
				'render_callback' => 'goyoartdark_render_site_menu_block',
			)
		);
	}
endif;
add_action( 'init', 'goyoartdark_register_site_menu_block' );



if ( ! function_exists( 'goyoartdark_render_site_menu_block' ) ) :
	/**
	 * site-menu 블록 서버 렌더 콜백.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @param array $attributes 블록 속성.
	 * @return string 렌더된 HTML.
	 */
	function goyoartdark_render_site_menu_block( $attributes ) {
		$location = isset( $attributes['location'] ) ? sanitize_key( $attributes['location'] ) : 'primary-menu';



		// 메뉴가 할당되지 않은 경우: 프런트엔드는 조용히 스킵, 에디터는 안내 문구.
		if ( ! has_nav_menu( $location ) ) {
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return sprintf(
					'<div style="padding:12px;background:#f0f0f0;border:1px dashed #999;font-size:13px;color:#555;">%s</div>',
					sprintf(
						/* translators: %s: theme location slug */
						esc_html__( '"%s" 위치에 메뉴가 할당되지 않았습니다. 외모 → 메뉴에서 설정해주세요.', 'goyoartdark' ),
						esc_html( $location )
					)
				);
			}
			return '';
		}



		$container_tag   = isset( $attributes['containerTag'] ) ? sanitize_key( $attributes['containerTag'] ) : 'nav';
		$container_class = isset( $attributes['containerClass'] ) ? sanitize_html_class( $attributes['containerClass'] ) : '';
		$menu_class      = isset( $attributes['menuClass'] ) ? sanitize_html_class( $attributes['menuClass'] ) : 'menu';
		$depth           = isset( $attributes['depth'] ) ? absint( $attributes['depth'] ) : 2;
		$aria_label      = isset( $attributes['ariaLabel'] ) ? (string) $attributes['ariaLabel'] : '';



		$menu_args = array(
			'theme_location'  => $location,
			'container'       => $container_tag ? $container_tag : false,
			'container_class' => $container_class,
			'menu_class'      => $menu_class,
			'depth'           => $depth,
			'fallback_cb'     => false,
			'echo'            => false,
		);



		if ( '' !== $aria_label ) {
			$menu_args['container_aria_label'] = $aria_label;
		}



		$html = wp_nav_menu( $menu_args );



		return is_string( $html ) ? $html : '';
	}
endif;



// 사이트 헤더 내비(주메뉴/SNS/검색/모바일 메뉴) 블록 등록.
// 로고 및 `.container` 래퍼는 이 블록 외부( patterns/header.php )에서 렌더링한다.
if ( ! function_exists( 'goyoartdark_register_site_header_block' ) ) :
	/**
	 * goyoartdark/site-header 다이내믹 블록 등록.
	 *
	 * 에디터: 블록 플레이스홀더만 노출 ( HTML 코드 비노출, 링크 비활성 ).
	 * 프런트: 주 메뉴 / SNS / 검색 / 모바일 메뉴를 PHP 로 렌더.
	 *
	 * 고객 설정 경로:
	 *   - SNS / 검색(표시): 외모 → 사용자 정의하기(Customizer) → 공통정보
	 *   - 주 메뉴:    외모 → 메뉴 → Primary Menu 위치 할당
	 *
	 * @since goyoartdark 1.0
	 * @return void
	 */
	function goyoartdark_register_site_header_block() {
		register_block_type(
			'goyoartdark/site-header',
			array(
				'api_version'     => 2,
				'title'           => __( '사이트 헤더 내비', 'goyoartdark' ),
				'description'     => __( '주 메뉴, SNS, 검색, 모바일 메뉴를 출력합니다. 로고는 이 블록 바깥의 헤더 패턴에서 관리됩니다.', 'goyoartdark' ),
				'category'        => 'theme',
				'icon'            => 'align-wide',
				'keywords'        => array( 'header', '헤더', 'nav', '메뉴', 'sns' ),
				'attributes'      => array(),
				'supports'        => array(
					'html'            => false,
					'align'           => false,
					'customClassName' => false,
					'multiple'        => false,
				),
				'render_callback' => 'goyoartdark_render_site_header_block',
			)
		);
	}
endif;
add_action( 'init', 'goyoartdark_register_site_header_block' );



if ( ! function_exists( 'goyoartdark_render_site_header_block' ) ) :
	/**
	 * site-header 블록 서버 렌더 콜백.
	 *
	 * Customizer( SNS·검색 ) 및 primary-menu 로케이션을 읽어 헤더 내비 마크업을 출력한다.
	 * 로고 및 `.container` 래퍼는 이 블록에서 처리하지 않는다.
	 *
	 * @since goyoartdark 1.0
	 * @return string 렌더된 헤더 내비 HTML.
	 */
	function goyoartdark_render_site_header_block() {
		$sns_instagram = trim( (string) get_theme_mod( 'instagram_url', '' ) );
		$sns_blog      = trim( (string) get_theme_mod( 'blog_url', '' ) );
		$sns_youtube   = trim( (string) get_theme_mod( 'youtube_url', '' ) );
		$sns_facebook  = trim( (string) get_theme_mod( 'facebook_url', '' ) );
		// 공통정보: '상단 검색 아이콘 숨기기' 체크(기본) 시 .search-box 미출력.
		$show_search = ! (bool) get_theme_mod( 'hide_search_box', true );



		$has_primary_menu = has_nav_menu( 'primary-menu' );
		$has_any_sns      = ( $sns_instagram || $sns_blog || $sns_youtube || $sns_facebook );



		// SNS 영역 - 설정된 항목만 렌더.
		$sns_html = '';
		if ( $has_any_sns ) {
			$sns_html .= '<ul class="sns">';
			if ( $sns_instagram ) {
				$sns_html .= '<li class="insta edit_instagram_url"><a href="' . esc_url( $sns_instagram ) . '" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a></li>';
			}
			if ( $sns_blog ) {
				$sns_html .= '<li class="blog"><a href="' . esc_url( $sns_blog ) . '" target="_blank" rel="noopener noreferrer"><i class="bi bi-bootstrap-fill"></i></a></li>';
			}
			if ( $sns_youtube ) {
				$sns_html .= '<li class="youtube"><a href="' . esc_url( $sns_youtube ) . '" target="_blank" rel="noopener noreferrer"><i class="bi bi-play-circle-fill"></i></a></li>';
			}
			if ( $sns_facebook ) {
				$sns_html .= '<li class="facebook"><a href="' . esc_url( $sns_facebook ) . '" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a></li>';
			}
			$sns_html .= '</ul>';
		}



		// 메뉴 마크업 ( PC / 모바일 공용 - ul.menu 만 출력 ).
		$menu_html = '';
		if ( $has_primary_menu ) {
			$menu_html = wp_nav_menu(
				array(
					'theme_location' => 'primary-menu',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => false,
					'echo'           => false,
				)
			);
			if ( ! is_string( $menu_html ) ) {
				$menu_html = '';
			}
		}



		// 검색 박스 - 커스터마이저 옵션 활성 시에만 노출.
		$search_html = '';
		if ( $show_search ) {
			$search_html  = '<div class="search-box">';
			$search_html .= '<button class="search-toggle" aria-label="' . esc_attr__( '검색 열기', 'goyoartdark' ) . '"><i class="bi bi-search"></i></button>';
			$search_html .= '<form role="search" method="get" class="search-form" action="' . esc_url( home_url( '/' ) ) . '">';
			$search_html .= '<input type="search" class="search-field" placeholder="' . esc_attr__( '검색', 'goyoartdark' ) . '" value="' . esc_attr( get_search_query() ) . '" name="s" />';
			$search_html .= '<button type="submit" class="search-submit" aria-label="' . esc_attr__( '검색', 'goyoartdark' ) . '"><i class="bi bi-search"></i></button>';
			$search_html .= '</form></div>';
		}



		// 최종 조립. 한 줄 flex(.container) 안에서 오른쪽 영역이 하나의 아이템이 되도록 래퍼로 묶는다.
		$output  = '<div class="goyo-header-right">';
		$output .= '<div class="navWrap">';
		$output .= '<nav id="gnb" class="gnb" role="navigation" aria-label="' . esc_attr__( '주 메뉴', 'goyoartdark' ) . '">';
		$output .= $menu_html;
		$output .= $sns_html;
		$output .= '</nav>';
		$output .= $search_html;
		$output .= '</div>';
		$output .= '<div class="menu-btn" aria-label="' . esc_attr__( '메뉴 열기', 'goyoartdark' ) . '"><div class="menu-icon"></div></div>';
		$output .= '<div class="menu-container">';
		$output .= '<nav class="more-navigation" aria-label="' . esc_attr__( '모바일 메뉴', 'goyoartdark' ) . '">';
		$output .= '<div class="container">';
		$output .= $menu_html;
		$output .= $sns_html;
		$output .= '</div>';
		$output .= '</nav>';
		$output .= '<div class="overlay"></div>';
		$output .= '</div>';
		$output .= '</div>';



		return $output;
	}
endif;

