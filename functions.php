<?php
/**
 * goyoartdark functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Goyoartdark
 * @since goyoartdark 1.0
 */

// Adds theme support for post formats.
if ( ! function_exists( 'goyoartdark_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'goyoartdark_post_format_setup' );

// 사이트 아이덴티티 → 로고( 이미지 ) : `has_custom_logo()` / `get_custom_logo()` 사용.
if ( ! function_exists( 'goyoartdark_custom_logo_setup' ) ) :
	/**
	 * 코어 custom-logo 테마 지원.
	 *
	 * @since goyoartdark 1.0
	 * @return void
	 */
	function goyoartdark_custom_logo_setup() {
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 120,
				'width'       => 400,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'goyoartdark_custom_logo_setup' );

// 에디터 스타일과 Bootstrap Icons 를 블록 에디터에도 동일하게 주입해 WYSIWYG 일치 보장.
if ( ! function_exists( 'goyoartdark_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
		add_editor_style( 'assets/icons/bootstrap-icons.css' );
	}
endif;
add_action( 'after_setup_theme', 'goyoartdark_editor_style' );

// 사용자정의(theme_mod) 키 레지스트리: 로고(custom_logo)와 히어로 배경 키 분리 등 반복 사고 방지.
require get_template_directory() . '/inc/theme-mod-registry.php';

// inc 로드 순서: 홈 판별·숏코드가 wp_enqueue_scripts 콜백보다 먼저 정의되도록 enqueue 블록 이전에 둔다.
require get_template_directory() . '/inc/blocks.php';
require get_template_directory() . '/inc/category-functions.php';
require get_template_directory() . '/inc/shortcodes.php';

// is_front_page() 만으로는 홈이 아닌 것으로 남는 경우가 있어(서브 path·메인 쿼리 타이밍 등) 자산·body 클래스는 요청 기준 홈과 맞춘다.
if ( ! function_exists( 'goyoartdark_detect_effective_front_page_for_assets' ) ) :
	/**
	 * 홈 전용 CSS/JS·body 클래스에 쓸 "유효 홈" 판별(캐시 없이 매회 계산).
	 *
	 * @return bool
	 */
	function goyoartdark_detect_effective_front_page_for_assets() {
		if ( function_exists( 'goyoartdark_is_request_effective_front_page' ) && goyoartdark_is_request_effective_front_page() ) {
			return true;
		}
		global $_wp_current_template_id, $_wp_current_template_content;
		if ( is_string( $_wp_current_template_id ) && '' !== $_wp_current_template_id ) {
			if ( false !== stripos( $_wp_current_template_id, '//front-page' ) ) {
				return true;
			}
			// home.html — 홈이 '최신 글'일 때 이 템플릿이 쓰이며 front-page.css·body 보강을 동일하게 적용해야 한다.
			if ( false !== stripos( $_wp_current_template_id, '//home' ) && 'posts' === (string) get_option( 'show_on_front' ) ) {
				return true;
			}
		}
		// 사이트 편집기에서 템플릿 슬러그만 바뀐 경우 ID 에 front-page 가 없어도 원페이지 패턴이면 홈 자산을 맞춘다.
		if ( is_string( $_wp_current_template_content ) && '' !== $_wp_current_template_content ) {
			$content = $_wp_current_template_content;
			if ( false !== strpos( $content, 'goyoartdark/front-page-main' )
				|| false !== strpos( $content, 'goyoartdark\\/front-page-main' )
				|| false !== strpos( $content, '"slug":"front-page-main"' )
				|| false !== strpos( $content, '\"slug\":\"front-page-main\"' ) ) {
				return true;
			}
		}
		return is_front_page();
	}
endif;

if ( ! function_exists( 'goyoartdark_is_effective_front_page_for_assets' ) ) :
	/**
	 * front-page.css · 홈 전용 스크립트 · body 클래스( front-page · goyo-home-overlay ) 보강 여부.
	 *
	 * template_include 캐시는 잘못된 false 고정 사례가 있어 요청마다 detect 만 사용한다.
	 *
	 * @return bool
	 */
	function goyoartdark_is_effective_front_page_for_assets() {
		return goyoartdark_detect_effective_front_page_for_assets();
	}
endif;

if ( ! function_exists( 'goyoartdark_should_disable_dark_color_scheme' ) ) :
	/**
	 * 커스터마이저 체크 상태 기준으로 브라우저 UI 다크 color-scheme 해제 여부를 반환.
	 *
	 * @return bool
	 */
	function goyoartdark_should_disable_dark_color_scheme() {
		return (bool) get_theme_mod( 'goyoartdark_main_page_top_disable_dark_mode', false ) || (bool) get_theme_mod( 'goyoartdark_main_page_disable_dark_mode', true );
	}
endif;

// 홈 레이아웃( 고정 히어로 · 스페이스 · 본문 덮음 ) — 다른 스타일/플러그인 묻힘 방지용·front-page 핸들 맨 마지막 인라인
if ( ! function_exists( 'goyoartdark_front_page_critical_css' ) ) :
	/**
	 * enqueue된 goyoartdark-front-page 뒤에 붙여 최종 레이어링을 보장한다.
	 *
	 * @return void
	 */
	function goyoartdark_front_page_critical_css() {
		if ( wp_doing_ajax() || is_feed() ) {
			return;
		}
		// fixed 히어로(mainhero-wrapper)·spacer 레이아웃은 제거됨.
		// .conWrap 과 main~* 의 스태킹 컨텍스트만 유지한다.
		$css = <<<'CSS'
body.front-page .wp-site-blocks,body.goyo-home-overlay .wp-site-blocks{transform:none !important; filter:none !important; perspective:none !important;}
body.front-page main.wp-block-group,body.goyo-home-overlay main.wp-block-group{transform:none !important; filter:none !important; overflow:visible !important; position:relative; z-index:0;}
body.front-page .conWrap,body.goyo-home-overlay .conWrap{position:relative !important; z-index:1 !important; background:#f7f7f5 !important; background-color:var(--wp--preset--color--base,#f7f7f5) !important; mix-blend-mode:normal;}
body.front-page main~*,body.goyo-home-overlay main~*{position:relative !important; z-index:2 !important; background-color:var(--wp--preset--color--base,#f7f7f5) !important;}
CSS;
		wp_add_inline_style( 'goyoartdark-front-page', $css );
	}
endif;

if ( ! function_exists( 'goyoartdark_enqueue_front_page_bundle' ) ) :
	/**
	 * 원페이지 전용 Swiper·front-page.css·인라인 보강을 한 번에 등록한다.
	 *
	 * @return void
	 */
	function goyoartdark_enqueue_front_page_bundle() {
		$version = wp_get_theme()->get( 'Version' );
		wp_enqueue_style(
			'goyoartdark-swiper-4',
			get_parent_theme_file_uri( 'assets/css/swiper-4.3.0.min.css' ),
			array( 'goyoartdark-style' ),
			$version
		);
		$front_page_css_path = get_parent_theme_file_path( 'assets/css/front-page.css' );
		$front_page_css_ver  = file_exists( $front_page_css_path ) ? (string) filemtime( $front_page_css_path ) : $version;
		wp_enqueue_style(
			'goyoartdark-front-page',
			get_parent_theme_file_uri( 'assets/css/front-page.css' ),
			array( 'goyoartdark-style', 'goyoartdark-swiper-4' ),
			$front_page_css_ver
		);
		goyoartdark_front_page_critical_css();
	}
endif;

// 프론트엔드 스타일 로드: 메인 스타일 + Bootstrap Icons (외부 CDN 금지, 항상 로컬 자산).
if ( ! function_exists( 'goyoartdark_enqueue_styles' ) ) :
	/**
	 * Enqueues the theme stylesheet on the front.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_enqueue_styles() {
		$version = wp_get_theme()->get( 'Version' );

		wp_enqueue_style(
			'goyoartdark-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			$version
		);
		wp_style_add_data(
			'goyoartdark-style',
			'path',
			get_parent_theme_file_path( 'style.css' )
		);

		wp_enqueue_style(
			'goyoartdark-bootstrap-icons',
			get_parent_theme_file_uri( 'assets/icons/bootstrap-icons.css' ),
			array(),
			$version
		);

		// 카테고리(웹진/블로그/포토/포트폴리오/리스트) 목록 및 single 레이아웃 스타일.
		wp_enqueue_style(
			'goyoartdark-board',
			get_parent_theme_file_uri( 'assets/css/board.css' ),
			array( 'goyoartdark-style' ),
			$version
		);

		// 헤더(상단 GNB, 로고, SNS, 모바일 메뉴) 전용 스타일.
		// 관리자 표시줄(core admin-bar)보다 뒤에 두어 #wpadminbar 포지션 오버라이드가 적용되게 한다.
		$goyoartdark_header_deps = array( 'goyoartdark-style' );
		if ( is_admin_bar_showing() ) {
			$goyoartdark_header_deps[] = 'admin-bar';
		}
		wp_enqueue_style(
			'goyoartdark-header',
			get_parent_theme_file_uri( 'assets/css/header.css' ),
			$goyoartdark_header_deps,
			$version
		);

		// 페이지/링크 전환 페이드 스타일: View Transitions API + 폴백.
		wp_enqueue_style(
			'goyoartdark-page-transition',
			get_parent_theme_file_uri( 'assets/css/page-transition.css' ),
			array( 'goyoartdark-style' ),
			$version
		);
		wp_enqueue_style(
			'goyoartdark-youtube-background',
			get_parent_theme_file_uri( 'assets/css/youtube-background.css' ),
			array( 'goyoartdark-style' ),
			$version
		);

		// Lenis 부드러운 스크롤용 필수 레이아웃( html.lenis 등 )
		$lenis_css_path = get_parent_theme_file_path( 'assets/css/lenis.css' );
		$lenis_css_ver  = file_exists( $lenis_css_path ) ? (string) filemtime( $lenis_css_path ) : $version;
		wp_enqueue_style(
			'goyoartdark-lenis',
			get_parent_theme_file_uri( 'assets/css/lenis.css' ),
			array( 'goyoartdark-style' ),
			$lenis_css_ver
		);

		if ( goyoartdark_is_effective_front_page_for_assets() ) {
			goyoartdark_enqueue_front_page_bundle();
		}

		if ( goyoartdark_should_disable_dark_color_scheme() ) {
			wp_add_inline_style( 'goyoartdark-style', 'html{color-scheme:light !important;}' );
		}
	}
endif;
add_action( 'wp_enqueue_scripts', 'goyoartdark_enqueue_styles' );

if ( ! function_exists( 'goyoartdark_maybe_reenqueue_front_page_styles' ) ) :
	/**
	 * 일부 최적화 플러그인이 늦게 dequeue 할 때 홈 전용 스타일을 다시 올린다.
	 *
	 * @return void
	 */
	function goyoartdark_maybe_reenqueue_front_page_styles() {
		if ( ! goyoartdark_is_effective_front_page_for_assets() ) {
			return;
		}
		if ( wp_style_is( 'goyoartdark-front-page', 'enqueued' ) ) {
			return;
		}
		goyoartdark_enqueue_front_page_bundle();
	}
endif;
add_action( 'wp_enqueue_scripts', 'goyoartdark_maybe_reenqueue_front_page_styles', 200 );

if ( ! function_exists( 'goyoartdark_customizer_preview_enqueue_front_bundle' ) ) :
	/**
	 * 커스터마이저 미리보기 iframe 에서 첫 enqueue 가 빗나가도 홈이면 front-page 번들을 보강한다.
	 *
	 * @return void
	 */
	function goyoartdark_customizer_preview_enqueue_front_bundle() {
		if ( ! is_customize_preview() ) {
			return;
		}
		if ( ! is_front_page() ) {
			return;
		}
		if ( wp_style_is( 'goyoartdark-front-page', 'enqueued' ) ) {
			return;
		}
		goyoartdark_enqueue_front_page_bundle();
	}
endif;
add_action( 'wp_enqueue_scripts', 'goyoartdark_customizer_preview_enqueue_front_bundle', 25 );

// 프론트엔드 스크립트 로드: Lenis(부드러운 스크롤) + Swiper + custom.js(테마 공통 인터랙션).
// custom.js 는 jQuery 와 Swiper 에 의존하며, goyoTheme 전역 객체(예: isFrontPage)를 통해
// PHP 컨텍스트(현재 페이지가 프론트 페이지인지 여부)를 JS 에서 분기 처리한다.
if ( ! function_exists( 'goyoartdark_enqueue_scripts' ) ) :
	/**
	 * Enqueues the theme JavaScript files on the front.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_enqueue_scripts() {
		$version = wp_get_theme()->get( 'Version' );

		$lenis_js_path = get_parent_theme_file_path( 'assets/js/lenis.min.js' );
		$lenis_js_ver  = file_exists( $lenis_js_path ) ? (string) filemtime( $lenis_js_path ) : $version;
		wp_enqueue_script(
			'goyoartdark-lenis-lib',
			get_parent_theme_file_uri( 'assets/js/lenis.min.js' ),
			array(),
			$lenis_js_ver,
			true
		);

		$lenis_init_path = get_parent_theme_file_path( 'assets/js/lenis-init.js' );
		$lenis_init_ver  = file_exists( $lenis_init_path ) ? (string) filemtime( $lenis_init_path ) : $version;
		wp_enqueue_script(
			'goyoartdark-lenis-init',
			get_parent_theme_file_uri( 'assets/js/lenis-init.js' ),
			array( 'goyoartdark-lenis-lib' ),
			$lenis_init_ver,
			true
		);

		wp_enqueue_script(
			'goyoartdark-swiper',
			get_parent_theme_file_uri( 'assets/js/swiper.min.js' ),
			array( 'goyoartdark-lenis-init' ),
			$version,
			true
		);

		wp_enqueue_script(
			'goyoartdark-custom',
			get_parent_theme_file_uri( 'assets/js/custom.js' ),
			array( 'jquery', 'goyoartdark-swiper', 'goyoartdark-lenis-init' ),
			$version,
			true
		);

		// 페이지 전환 폴백 스크립트: footer 로드, Lenis 이후 실행.
		wp_enqueue_script(
			'goyoartdark-page-transition',
			get_parent_theme_file_uri( 'assets/js/page-transition.js' ),
			array( 'goyoartdark-lenis-init' ),
			$version,
			true
		);

		wp_localize_script(
			'goyoartdark-custom',
			'goyoTheme',
			array(
				'isFrontPage' => goyoartdark_is_effective_front_page_for_assets(),
			)
		);

		if ( goyoartdark_is_effective_front_page_for_assets() ) {
			wp_enqueue_script(
				'goyoartdark-mainhero-content-scroll-scale',
				get_parent_theme_file_uri( 'assets/js/mainhero-content-scroll-scale.js' ),
				array( 'goyoartdark-custom' ),
				$version,
				true
			);
			$unicorn_project_id = function_exists( 'goyoartdark_get_unicorn_selected_project_id' )
				? goyoartdark_get_unicorn_selected_project_id()
				: (string) get_theme_mod( 'goyo_unicorn_project_id', '' );
			if ( '' !== trim( (string) $unicorn_project_id ) ) {
				$unicorn_loader_path = get_parent_theme_file_path( 'assets/js/unicorn-loader.js' );
				$unicorn_loader_ver  = file_exists( $unicorn_loader_path ) ? (string) filemtime( $unicorn_loader_path ) : $version;
				wp_enqueue_script(
					'goyoartdark-unicorn-loader',
					get_parent_theme_file_uri( 'assets/js/unicorn-loader.js' ),
					array(
						'goyoartdark-lenis-init',
						'goyoartdark-mainhero-content-scroll-scale',
					),
					$unicorn_loader_ver,
					true
				);
				wp_localize_script(
					'goyoartdark-unicorn-loader',
					'goyoUnicornConfig',
					array(
						'projectId' => $unicorn_project_id,
					)
				);
			}
		}

		if ( ! is_admin() ) {
			$mh_parallax_deps = array( 'goyoartdark-lenis-init' );
			/* 홈: body.goyo-hero-past-fold 는 scroll-scale 가 먼저 동기해야 함 — 복원 스크롤·폴드 상태와 패럴럭스 초기 RAF 충돌 방지 */
			if ( goyoartdark_is_effective_front_page_for_assets() ) {
				$mh_parallax_deps[] = 'goyoartdark-mainhero-content-scroll-scale';
			}
			wp_enqueue_script(
				'goyoartdark-mainhero-inner-parallax',
				get_parent_theme_file_uri( 'assets/js/mainhero-inner-parallax.js' ),
				$mh_parallax_deps,
				$version,
				true
			);
		}
		/* 홈 갤러리 = category-col-reveal.js( 셀렉터에 .goyo-main-gallery-grid … 포함, board.css 동일 ) */
		if ( is_category() || is_search() || goyoartdark_is_effective_front_page_for_assets() ) {
			wp_enqueue_script(
				'goyoartdark-category-col-reveal',
				get_parent_theme_file_uri( 'assets/js/category-col-reveal.js' ),
				array( 'goyoartdark-lenis-init' ),
				$version,
				true
			);
		}
	}
endif;
add_action( 'wp_enqueue_scripts', 'goyoartdark_enqueue_scripts' );

if ( ! function_exists( 'goyoartdark_filter_metaslider_3843_parameters' ) ) :
	/**
	 * 메타슬라이더 3843 캐러셀 기본값: 데스크톱 기준 4장·슬라이드 간격 20px.
	 * 520px 미만 장수·820px 미만 슬라이드 간격은 metaslider-3843-carousel-breakpoint.js·style.css 에서 조정한다.
	 *
	 * @param array $options   FlexSlider 옵션.
	 * @param int   $slider_id 슬라이더 ID.
	 * @return array
	 */
	function goyoartdark_filter_metaslider_3843_parameters( $options, $slider_id ) {
		if ( 3843 !== (int) $slider_id ) {
			return $options;
		}

		$options['minItems']   = 4;
		$options['maxItems']   = 4;
		$options['itemWidth']  = 280;
		$options['itemMargin'] = 20;

		return $options;
	}
endif;
add_filter( 'metaslider_flex_slider_parameters', 'goyoartdark_filter_metaslider_3843_parameters', 20, 2 );

if ( ! function_exists( 'goyoartdark_enqueue_metaslider_3843_carousel_breakpoint' ) ) :
	/**
	 * Flex 슬라이더 인라인 초기화 이후 실행되도록 wp_footer 에서만 로드한다.
	 *
	 * @return void
	 */
	function goyoartdark_enqueue_metaslider_3843_carousel_breakpoint() {
		if ( is_admin() ) {
			return;
		}
		if ( ! wp_script_is( 'metaslider-flex-slider', 'enqueued' ) ) {
			return;
		}

		$path = get_parent_theme_file_path( 'assets/js/metaslider-3843-carousel-breakpoint.js' );
		$ver  = file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );

		wp_enqueue_script(
			'goyoartdark-metaslider-3843-carousel-breakpoint',
			get_parent_theme_file_uri( 'assets/js/metaslider-3843-carousel-breakpoint.js' ),
			array( 'jquery', 'metaslider-flex-slider' ),
			$ver,
			true
		);
	}
endif;
add_action( 'wp_footer', 'goyoartdark_enqueue_metaslider_3843_carousel_breakpoint', 8 );

if ( ! function_exists( 'goyo_get_resized_image_url_from_path' ) ) :
	/**
	 * 업로드 경로 URL에서 요청한 썸네일 사이즈 URL을 추론한다.
	 *
	 * attachment_id를 얻지 못한 경우(예: 본문 원시 URL)에도
	 * 워드프레스 업로드 규칙(-WxH 파일명)으로 리사이즈 이미지를 찾아
	 * 목록 카드에서 과도한 원본 로딩을 줄이기 위해 사용한다.
	 *
	 * @param string       $image_url 원본 이미지 URL.
	 * @param string|int[] $size      이미지 사이즈명 또는 [width, height].
	 * @return string 리사이즈 URL(없으면 원본 URL).
	 */
	function goyo_get_resized_image_url_from_path( $image_url, $size = 'thumbnail' ) {
		$image_url = trim( (string) $image_url );
		if ( '' === $image_url ) {
			return '';
		}

		$uploads = wp_get_upload_dir();
		if ( empty( $uploads['baseurl'] ) || false === strpos( $image_url, $uploads['baseurl'] ) ) {
			return $image_url;
		}

		$path = wp_parse_url( $image_url, PHP_URL_PATH );
		if ( empty( $path ) ) {
			return $image_url;
		}

		$upload_path = wp_parse_url( $uploads['baseurl'], PHP_URL_PATH );
		if ( empty( $upload_path ) || false === strpos( $path, $upload_path ) ) {
			return $image_url;
		}

		$relative_path = ltrim( substr( $path, strlen( $upload_path ) ), '/' );
		if ( '' === $relative_path ) {
			return $image_url;
		}

		$full_path = trailingslashit( $uploads['basedir'] ) . str_replace( '/', DIRECTORY_SEPARATOR, $relative_path );
		if ( ! file_exists( $full_path ) ) {
			return $image_url;
		}

		$width  = 0;
		$height = 0;
		if ( is_array( $size ) && isset( $size[0], $size[1] ) ) {
			$width  = absint( $size[0] );
			$height = absint( $size[1] );
		} elseif ( is_string( $size ) && '' !== $size ) {
			$registered_size = wp_get_registered_image_subsizes();
			if ( isset( $registered_size[ $size ] ) ) {
				$width  = isset( $registered_size[ $size ]['width'] ) ? absint( $registered_size[ $size ]['width'] ) : 0;
				$height = isset( $registered_size[ $size ]['height'] ) ? absint( $registered_size[ $size ]['height'] ) : 0;
			}
			if ( 0 === $width && 'thumbnail' === $size ) {
				$width = (int) get_option( 'thumbnail_size_w', 150 );
			}
			if ( 0 === $height && 'thumbnail' === $size ) {
				$height = (int) get_option( 'thumbnail_size_h', 150 );
			}
		}

		if ( $width <= 0 || $height <= 0 ) {
			return $image_url;
		}

		$pathinfo = pathinfo( $image_url );
		if ( empty( $pathinfo['dirname'] ) || empty( $pathinfo['filename'] ) || empty( $pathinfo['extension'] ) ) {
			return $image_url;
		}

		$candidate_url = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $width . 'x' . $height . '.' . $pathinfo['extension'];
		$candidate_path = trailingslashit( $uploads['basedir'] ) . str_replace( '/', DIRECTORY_SEPARATOR, ltrim( str_replace( $uploads['baseurl'], '', $candidate_url ), '/' ) );
		if ( file_exists( $candidate_path ) ) {
			return $candidate_url;
		}

		return $image_url;
	}
endif;

// 카테고리·검색 목록: col 스크롤 시 아래→위 15px 페이드(IntersectionObserver) — head 에 플래그, 스크립트는 푸터.
if ( ! function_exists( 'goyoartdark_category_col_reveal_head' ) ) :
	/**
	 * 카테고리/검색/프론트(홈) — html.goyo-reveal-tiles( 스크립트 없을 때 .goyo-col-reveal 은 board.css 기본 표시 )
	 */
	function goyoartdark_category_col_reveal_head() {
		if ( ! is_category() && ! is_search() && ! goyoartdark_is_effective_front_page_for_assets() ) {
			return;
		}
		echo '<script>document.documentElement.classList.add("goyo-reveal-tiles");</script>' . "\n";
	}
endif;
add_action( 'wp_head', 'goyoartdark_category_col_reveal_head', 0 );

// WP 로그인 화면(wp-login.php) 전용 스타일 로드: 프론트와 완전히 분리되어 로그인 UI 만 커스텀.
if ( ! function_exists( 'goyoartdark_login_styles' ) ) :
	/**
	 * Enqueues the custom login stylesheet on wp-login.php.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_login_styles() {
		wp_enqueue_style(
			'goyoartdark-login',
			get_parent_theme_file_uri( 'assets/css/login.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'login_enqueue_scripts', 'goyoartdark_login_styles' );

// 로그인 화면 로고 링크 텍스트를 사이트 제목으로 교체 (기본값: "Powered by WordPress" → "워드프레스 제공").
if ( ! function_exists( 'goyoartdark_login_header_text' ) ) :
	/**
	 * Replaces the login logo link text with the current site title.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return string Site title used as the logo link text.
	 */
	function goyoartdark_login_header_text() {
		return get_bloginfo( 'name' );
	}
endif;
add_filter( 'login_headertext', 'goyoartdark_login_header_text' );

// 로그인 화면 로고 링크 URL 을 wordpress.org 대신 사이트 홈으로 연결.
if ( ! function_exists( 'goyoartdark_login_header_url' ) ) :
	/**
	 * Replaces the login logo link URL with the site home URL.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return string Home URL used as the logo link.
	 */
	function goyoartdark_login_header_url() {
		return home_url( '/' );
	}
endif;
add_filter( 'login_headerurl', 'goyoartdark_login_header_url' );

// 로그인 화면 하단의 "블로그로 가기/사이트명 으로 가기" 링크 문구를 "홈으로 가기" 로 통일.
// WordPress 5.7+ 의 login_site_html_link 필터를 사용해 a 태그 전체를 교체한다.
if ( ! function_exists( 'goyoartdark_login_site_html_link' ) ) :
	/**
	 * Replaces the "Back to {site}" HTML link on the login screen.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @param string $link Default HTML link markup rendered by core.
	 * @return string Customized HTML link pointing to the home URL.
	 */
	function goyoartdark_login_site_html_link( $link ) {
		unset( $link );

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( home_url( '/' ) ),
			esc_html__( '← 홈으로 가기', 'goyoartdark' )
		);
	}
endif;
add_filter( 'login_site_html_link', 'goyoartdark_login_site_html_link' );

// 페이지 전환 안정화: 크리티컬 인라인 <head> 출력.
// 1) meta color-scheme: light → 브라우저 UI(스크롤바·폼)를 CSS 로드 전부터 라이트로 고정.
// 2) 인라인 <style>: html/body 밝은 배경을 외부 CSS 로드 전에 적용해 FOUC 암전 플래시 차단.
//    또한 View Transition 스택(::view-transition, ::backdrop, group/image-pair) 전체에
//    밝은 배경을 지정해 전환 중 어느 레이어에서도 어두운 틈이 새지 않도록 한다.
// 3) 인라인 <script>: ( prefers-reduced-motion 이 아닐 때 ) 모든 브라우저에 .goyo-page-fade 를 붙여
//    body opacity 기반 부드러운 전환( assets/js/page-transition.js 와 쌍 ) — Chrome 도 동일 경험.
// 반드시 외부 스타일/스크립트 로드 전에 파싱되어야 하므로 wp_head 우선순위 0 으로 인라인 출력.
if ( ! function_exists( 'goyoartdark_render_page_transition_head' ) ) :
	/**
	 * Outputs critical inline <head> markup for seamless light page transitions.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_render_page_transition_head() {
		$color_scheme_value    = 'light dark';
		$theme_color_value     = '#f7f7f5';
		$critical_bg_color     = '#f7f7f5';
		$critical_color_scheme = 'light';
		?>
<meta name="color-scheme" content="<?php echo esc_attr( $color_scheme_value ); ?>">
<meta name="theme-color" content="<?php echo esc_attr( $theme_color_value ); ?>">
<style id="goyo-page-transition-critical">html{color-scheme:<?php echo esc_html( $critical_color_scheme ); ?>}html,body{background-color:<?php echo esc_html( $critical_bg_color ); ?>}::backdrop,::view-transition,::view-transition-group(root),::view-transition-image-pair(root),::view-transition-old(root),::view-transition-new(root){background-color:<?php echo esc_html( $critical_bg_color ); ?>}</style>
<script>(function(){try{var h=document.documentElement;var m=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;var isFront=<?php echo goyoartdark_is_effective_front_page_for_assets() ? 'true' : 'false'; ?>;var isHome=<?php echo is_home() ? 'true' : 'false'; ?>;if(!m&&!isFront&&!isHome){h.classList.add('goyo-page-fade');var markReady=function(){h.classList.remove('goyo-page-leaving');h.classList.add('goyo-page-ready');};if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',markReady,{once:true});}else{requestAnimationFrame(markReady);}window.addEventListener('pageshow',function(ev){if(ev.persisted){markReady();}});}}catch(e){}})();</script>
		<?php
	}
endif;
add_action( 'wp_head', 'goyoartdark_render_page_transition_head', 0 );

// Pretendard 가변 폰트 프리로드: 고객 기기에 Pretendard 가 없어도 첫 렌더 전에 woff2 를 병렬 다운로드해 FOUT 최소화.
if ( ! function_exists( 'goyoartdark_preload_pretendard' ) ) :
	/**
	 * Outputs <link rel="preload"> for the Pretendard Variable woff2 file.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_preload_pretendard() {
		$font_uri = get_parent_theme_file_uri( 'assets/fonts/pretendard/PretendardVariable.woff2' );
		printf(
			'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin="anonymous">' . "\n",
			esc_url( $font_uri )
		);
	}
endif;
add_action( 'wp_head', 'goyoartdark_preload_pretendard', 1 );

// 클래식 네비게이션 메뉴 등록: 블록 테마에서도 "모양 > 메뉴" 화면을 노출시키기 위해 nav menu location 을 등록한다.
if ( ! function_exists( 'goyoartdark_register_nav_menus' ) ) :
	/**
	 * Registers nav menu locations used by the theme.
	 *
	 * 블록 테마는 기본적으로 "모양 > 메뉴" 화면이 숨겨져 있으나, nav menu location 이
	 * 하나라도 등록되면 관리자에 해당 메뉴가 다시 표시된다.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_register_nav_menus() {
		register_nav_menus(
			array(
				'primary-menu' => __( 'Primary Menu', 'goyoartdark' ),
				'footer-menu'  => __( 'Footer Menu', 'goyoartdark' ),
				'sidebar-menu' => __( 'Sidebar Menu', 'goyoartdark' ),
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'goyoartdark_register_nav_menus' );

// Primary 메뉴에서 하위 페이지 진입 시 최상위 부모 메뉴(예: ABOUT)도 current-menu-item 으로 강조.
if ( ! function_exists( 'goyoartdark_highlight_primary_top_ancestor_menu_item' ) ) :
	/**
	 * Adds current-menu-item class to the top ancestor page item in primary menu.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @param string[] $classes 기존 메뉴 아이템 클래스 목록.
	 * @param WP_Post  $item    현재 메뉴 아이템 객체.
	 * @param stdClass $args    wp_nav_menu() 인자.
	 * @return string[] 보정된 메뉴 아이템 클래스 목록.
	 */
	function goyoartdark_highlight_primary_top_ancestor_menu_item( $classes, $item, $args ) {
		if ( ! is_page() ) {
			return $classes;
		}

		if ( ! isset( $args->theme_location ) || 'primary-menu' !== $args->theme_location ) {
			return $classes;
		}

		$current_page_id = get_queried_object_id();
		if ( $current_page_id <= 0 ) {
			return $classes;
		}

		$ancestor_ids = get_post_ancestors( $current_page_id );
		if ( empty( $ancestor_ids ) ) {
			return $classes;
		}

		$top_ancestor_page_id = (int) end( $ancestor_ids );
		if ( 'page' !== $item->object || (int) $item->object_id !== $top_ancestor_page_id ) {
			return $classes;
		}

		$classes[] = 'current-menu-item';
		$classes[] = 'current-menu-ancestor';

		return array_values( array_unique( $classes ) );
	}
endif;
add_filter( 'nav_menu_css_class', 'goyoartdark_highlight_primary_top_ancestor_menu_item', 20, 3 );

// Registers custom block styles.
if ( ! function_exists( 'goyoartdark_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'goyoartdark' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'goyoartdark_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'goyoartdark_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_pattern_categories() {

		register_block_pattern_category(
			'goyoartdark_page',
			array(
				'label'       => __( 'Pages', 'goyoartdark' ),
				'description' => __( 'A collection of full page layouts.', 'goyoartdark' ),
			)
		);

		register_block_pattern_category(
			'goyoartdark_post-format',
			array(
				'label'       => __( 'Post formats', 'goyoartdark' ),
				'description' => __( 'A collection of post format patterns.', 'goyoartdark' ),
			)
		);
	}
endif;
add_action( 'init', 'goyoartdark_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'goyoartdark_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_register_block_bindings() {
		register_block_bindings_source(
			'goyoartdark/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'goyoartdark' ),
				'get_value_callback' => 'goyoartdark_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'goyoartdark_register_block_bindings' );

// 사용자 정의(Customizer) '공통정보' 섹션 등록: 로고/SNS/애널리틱스/OG 이미지/소유확인/카피라이트.
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/customizer-main-page.php';

// KBoard(케이보드) 커스터마이징: 문의 등록 SweetAlert, 작성자 마스킹, 알림 메일 제목, body 클래스 보강.
require get_template_directory() . '/inc/kboard-functions.php';

// 사이트 조회·방문 통계(알림판 위젯 + 프론트 기록).
require get_template_directory() . '/inc/dashboard-site-stats.php';

// 알림판 제목 옆 홈·매뉴얼 바로가기.
require get_template_directory() . '/inc/admin-dashboard-title-actions.php';

// 페이지 편집 화면 메타박스(타이틀 표시 여부, 가로폭 선택) 및 프론트 인라인 CSS 출력.
require get_template_directory() . '/inc/page-functions.php';

// 원하는 위치에 삽입 가능한 유튜브 배경 숏코드 [goyo_youtube_bg].
require get_template_directory() . '/inc/youtube-background.php';

// Query Loop: 대표이미지 없을 때 본문 첫 이미지로 대체.
require get_template_directory() . '/inc/query-loop-image-fallback.php';

// 공통정보(Customizer) 기반 <head> 메타 태그 출력: OG 이미지, 사이트 소유확인, 애널리틱스.
if ( ! function_exists( 'goyoartdark_render_common_head_meta' ) ) :
	/**
	 * Outputs <head> meta tags derived from the '공통정보' customizer values.
	 *
	 * 출력 항목: 카카오톡 미리보기용 og:image, 네이버/구글 사이트 소유확인,
	 * 구글·네이버 애널리틱스 추적 스크립트.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_render_common_head_meta() {
		$kakao_image              = get_theme_mod( Goyoartdark_Theme_Mod_Registry::KAKAO_PREVIEW_IMAGE, '' );
		$naver_site_verification  = get_theme_mod( 'naver_site_verification', '' );
		$google_site_verification = get_theme_mod( 'google_site_verification', '' );
		$google_analytics_id      = get_theme_mod( 'googleanal', '' );
		$naver_analytics_id       = get_theme_mod( 'naveranal', '' );

		if ( ! empty( $kakao_image ) ) {
			printf(
				'<meta property="og:image" content="%s">' . "\n",
				esc_url( $kakao_image )
			);
		}

		if ( ! empty( $naver_site_verification ) ) {
			printf(
				'<meta name="naver-site-verification" content="%s">' . "\n",
				esc_attr( $naver_site_verification )
			);
		}

		if ( ! empty( $google_site_verification ) ) {
			printf(
				'<meta name="google-site-verification" content="%s">' . "\n",
				esc_attr( $google_site_verification )
			);
		}

		if ( ! empty( $google_analytics_id ) ) {
			$google_gtag_js_url = esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( (string) $google_analytics_id ) );
			printf(
				'<script async src="%s"></script>' . "\n",
				$google_gtag_js_url
			);
			printf(
				'<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","%s");</script>' . "\n",
				esc_js( $google_analytics_id )
			);
		}

		if ( ! empty( $naver_analytics_id ) ) {
			echo '<script type="text/javascript" src="//wcs.naver.net/wcslog.js"></script>' . "\n";
			printf(
				'<script type="text/javascript">if(!wcs_add)var wcs_add={};wcs_add["wa"]="%s";if(window.wcs){wcs_do();}</script>' . "\n",
				esc_js( $naver_analytics_id )
			);
		}
	}
endif;
add_action( 'wp_head', 'goyoartdark_render_common_head_meta', 5 );

// 공통정보(Customizer)의 서브배너 설정값을 CSS 변수로 출력.
if ( ! function_exists( 'goyoartdark_render_sub_banner_custom_style' ) ) :
	/**
	 * Outputs inline CSS variables for .subBanner.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_render_sub_banner_custom_style() {
		$sub_banner_bg_color              = get_theme_mod( 'sub_banner_bg_color', '#333333' );
		$sub_banner_overlay_bg_color      = (string) get_theme_mod( 'sub_banner_overlay_bg_color', 'rgba(0, 0, 0, 0.2)' );
		$sub_banner_min_height            = (string) get_theme_mod( 'sub_banner_min_height', '' );
		$sub_banner_page_title_font_size  = (string) get_theme_mod( 'sub_banner_page_title_font_size', 'clamp(24px, 3.2vw, 50px)' );
		$sub_banner_page_title_font_weight = (int) get_theme_mod( 'sub_banner_page_title_font_weight', 700 );
		$sub_banner_subnav_font_size      = (string) get_theme_mod( 'sub_banner_subnav_font_size', '17px' );
		$sub_banner_subnav_font_weight    = (int) get_theme_mod( 'sub_banner_subnav_font_weight', 300 );
		$header_menu_font_size            = (string) get_theme_mod( 'header_menu_font_size', 'clamp(18px, 1.3vw, 22px)' );
		$header_submenu_font_size         = (string) get_theme_mod( 'header_submenu_font_size', '16px' );
		$header_menu_font_weight          = (int) get_theme_mod( 'header_menu_font_weight', 600 );
		$header_submenu_font_weight       = (int) get_theme_mod( 'header_submenu_font_weight', 400 );
		$default_font_family_stack        = 'Pretendard, "Noto Sans KR", sans-serif';
		$header_menu_font_family          = (string) get_theme_mod( 'header_menu_font_family', '' );
		$header_submenu_font_family       = (string) get_theme_mod( 'header_submenu_font_family', $default_font_family_stack );
		$sub_banner_page_title_font_family = (string) get_theme_mod( 'sub_banner_page_title_font_family', '"Poppins", sans-serif' );
		$sub_banner_subnav_font_family    = (string) get_theme_mod( 'sub_banner_subnav_font_family', $default_font_family_stack );
		$header_menu_font_color           = sanitize_hex_color( get_theme_mod( 'header_menu_font_color', '#ffffff' ) );
		$header_menu_current_font_color   = sanitize_hex_color( get_theme_mod( 'header_menu_current_font_color', '#ffffff' ) );
		$header_menu_font_opacity         = get_theme_mod( 'header_menu_font_opacity', 0.94 );
		$header_submenu_font_color        = sanitize_hex_color( get_theme_mod( 'header_submenu_font_color', '#606060' ) );
		$header_submenu_current_accent_color = sanitize_hex_color( get_theme_mod( 'header_submenu_current_accent_color', '#04af65' ) );
		$sub_banner_page_title_font_color = sanitize_hex_color( get_theme_mod( 'sub_banner_page_title_font_color', '#ffffff' ) );
		$sub_banner_subnav_font_color     = sanitize_hex_color( get_theme_mod( 'sub_banner_subnav_font_color', '#ffffff' ) );
		$subbanner_menu_active_bg_color   = sanitize_hex_color( get_theme_mod( 'subbanner_menu_active_bg_color', '#04af65' ) );
		$sub_banner_bg_color              = sanitize_hex_color( $sub_banner_bg_color );
		$sub_banner_overlay_bg_color      = trim( $sub_banner_overlay_bg_color );
		$header_menu_font_family          = trim( (string) $header_menu_font_family );
		$header_submenu_font_family       = trim( (string) $header_submenu_font_family );
		$sub_banner_page_title_font_family = trim( (string) $sub_banner_page_title_font_family );
		$sub_banner_subnav_font_family    = trim( (string) $sub_banner_subnav_font_family );

		// 사용자 입력 습관(끝 세미콜론)을 허용하되 CSS 변수 값은 순수 font-family 값으로 정규화.
		$header_menu_font_family          = rtrim( $header_menu_font_family, ';' );
		$header_submenu_font_family       = rtrim( $header_submenu_font_family, ';' );
		$sub_banner_page_title_font_family = rtrim( $sub_banner_page_title_font_family, ';' );
		$sub_banner_subnav_font_family    = rtrim( $sub_banner_subnav_font_family, ';' );

		if ( empty( $sub_banner_bg_color ) ) {
			$sub_banner_bg_color = '#333333';
		}

		if ( ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%#]+$/', $sub_banner_overlay_bg_color ) ) {
			$sub_banner_overlay_bg_color = 'rgba(0, 0, 0, 0.2)';
		}

		$sub_banner_min_height = trim( $sub_banner_min_height );
		if ( '' === $sub_banner_min_height ) {
			$sub_banner_min_height = 'auto';
		} elseif ( ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%]+$/', $sub_banner_min_height ) ) {
			$sub_banner_min_height = 'clamp(400px, 40vw,500px)';
		}
		if ( preg_match( '/^\d+$/', $sub_banner_min_height ) ) {
			$sub_banner_min_height_int = (int) $sub_banner_min_height;
			if ( $sub_banner_min_height_int < 100 ) {
				$sub_banner_min_height_int = 100;
			}
			$sub_banner_min_height = (string) $sub_banner_min_height_int . 'px';
		}

		if ( ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%]+$/', $sub_banner_page_title_font_size ) ) {
			$sub_banner_page_title_font_size = 'clamp(24px, 3.2vw, 50px)';
		}

		if ( ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%]+$/', $sub_banner_subnav_font_size ) ) {
			$sub_banner_subnav_font_size = '17px';
		}

		if ( preg_match( '/^\d+$/', $sub_banner_page_title_font_size ) ) {
			$sub_banner_page_title_font_size .= 'px';
		}

		if ( preg_match( '/^\d+$/', $sub_banner_subnav_font_size ) ) {
			$sub_banner_subnav_font_size .= 'px';
		}

		if ( $sub_banner_page_title_font_weight < 100 || $sub_banner_page_title_font_weight > 900 ) {
			$sub_banner_page_title_font_weight = 700;
		}

		if ( $sub_banner_subnav_font_weight < 100 || $sub_banner_subnav_font_weight > 900 ) {
			$sub_banner_subnav_font_weight = 300;
		}

		if ( ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%]+$/', $header_menu_font_size ) ) {
			$header_menu_font_size = 'clamp(18px, 1.3vw, 22px)';
		}

		if ( ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%]+$/', $header_submenu_font_size ) ) {
			$header_submenu_font_size = '16px';
		}

		if ( $header_menu_font_weight < 100 || $header_menu_font_weight > 900 ) {
			$header_menu_font_weight = 600;
		}

		if ( $header_submenu_font_weight < 100 || $header_submenu_font_weight > 900 ) {
			$header_submenu_font_weight = 400;
		}

		if ( '' === $header_menu_font_family || ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%\'"_]+$/', $header_menu_font_family ) ) {
			$header_menu_font_family = $default_font_family_stack;
		}

		if ( '' === $header_submenu_font_family || ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%\'"_]+$/', $header_submenu_font_family ) ) {
			$header_submenu_font_family = $default_font_family_stack;
		}

		if ( '' === $sub_banner_page_title_font_family || ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%\'"_]+$/', $sub_banner_page_title_font_family ) ) {
			$sub_banner_page_title_font_family = $default_font_family_stack;
		}

		if ( '' === $sub_banner_subnav_font_family || ! preg_match( '/^[0-9a-zA-Z\.\,\(\)\-\+\s%\'"_]+$/', $sub_banner_subnav_font_family ) ) {
			$sub_banner_subnav_font_family = $default_font_family_stack;
		}

		if ( empty( $header_menu_font_color ) ) {
			$header_menu_font_color = '#ffffff';
		}

		if ( empty( $header_menu_current_font_color ) ) {
			$header_menu_current_font_color = '#ffffff';
		}
		if ( function_exists( 'goyoartdark_sanitize_hero_opacity' ) ) {
			$header_menu_font_opacity = goyoartdark_sanitize_hero_opacity( $header_menu_font_opacity );
		} else {
			$header_menu_font_opacity = max( 0, min( 1, (float) $header_menu_font_opacity ) );
			$header_menu_font_opacity = round( $header_menu_font_opacity, 2 );
		}
		if ( $header_menu_font_opacity < 0.999 ) {
			$header_menu_rgb = sscanf( ltrim( $header_menu_font_color, '#' ), '%02x%02x%02x' );
			if ( is_array( $header_menu_rgb ) && count( $header_menu_rgb ) === 3 ) {
				$header_menu_font_color = 'rgba(' . (int) $header_menu_rgb[0] . ', ' . (int) $header_menu_rgb[1] . ', ' . (int) $header_menu_rgb[2] . ', ' . (string) $header_menu_font_opacity . ')';
			}
		}

		if ( empty( $sub_banner_page_title_font_color ) ) {
			$sub_banner_page_title_font_color = '#ffffff';
		}

		if ( empty( $header_submenu_font_color ) ) {
			$header_submenu_font_color = '#ffffff';
		}

		if ( empty( $header_submenu_current_accent_color ) ) {
			$header_submenu_current_accent_color = '#04af65';
		}

		if ( empty( $sub_banner_subnav_font_color ) ) {
			$sub_banner_subnav_font_color = '#ffffff';
		}

		if ( empty( $subbanner_menu_active_bg_color ) ) {
			$subbanner_menu_active_bg_color = '#04af65';
		}
		// --*-font-family 만 esc_html 없이 출력: 위 allowlist 정규식으로 < ; \ 등이 걸러져 </style> 이탈·XSS 여지를 막음. esc_html 은 font 스택 내 따옴표·엔티티를 깨뜨릴 수 있음.
		?>
<style id="goyo-sub-banner-custom-vars">:root{--sub-banner-bg-color:<?php echo esc_html( $sub_banner_bg_color ); ?>;--sub-banner-overlay-bg-color:<?php echo esc_html( $sub_banner_overlay_bg_color ); ?>;--sub-banner-min-height:<?php echo esc_html( $sub_banner_min_height ); ?>;--sub-banner-page-title-font-size:<?php echo esc_html( $sub_banner_page_title_font_size ); ?>;--sub-banner-page-title-font-weight:<?php echo esc_html( (string) $sub_banner_page_title_font_weight ); ?>;--sub-banner-page-title-font-family:<?php echo $sub_banner_page_title_font_family; ?>;--sub-banner-page-title-font-color:<?php echo esc_html( $sub_banner_page_title_font_color ); ?>;--sub-banner-subnav-font-size:<?php echo esc_html( $sub_banner_subnav_font_size ); ?>;--sub-banner-subnav-font-weight:<?php echo esc_html( (string) $sub_banner_subnav_font_weight ); ?>;--sub-banner-subnav-font-family:<?php echo $sub_banner_subnav_font_family; ?>;--sub-banner-subnav-font-color:<?php echo esc_html( $sub_banner_subnav_font_color ); ?>;--subbanner-menu-color-active:<?php echo esc_html( $subbanner_menu_active_bg_color ); ?>;--header-menu-font-size:<?php echo esc_html( $header_menu_font_size ); ?>;--header-menu-font-weight:<?php echo esc_html( (string) $header_menu_font_weight ); ?>;--header-menu-font-family:<?php echo $header_menu_font_family; ?>;--header-menu-font-color:<?php echo esc_html( $header_menu_font_color ); ?>;--menu-top-current-color:<?php echo esc_html( $header_menu_current_font_color ); ?>;--header-submenu-font-size:<?php echo esc_html( $header_submenu_font_size ); ?>;--header-submenu-font-weight:<?php echo esc_html( (string) $header_submenu_font_weight ); ?>;--header-submenu-font-family:<?php echo $header_submenu_font_family; ?>;--header-submenu-font-color:<?php echo esc_html( $header_submenu_font_color ); ?>;--header-submenu-current-accent-color:<?php echo esc_html( $header_submenu_current_accent_color ); ?>;}</style>
		<?php
	}
endif;
// wp_print_styles(8) 이후에 출력해야 style.css :root 의 --menu-top-current-color 등이 커스터마이저 값을 덮어쓰지 않는다.
add_action( 'wp_head', 'goyoartdark_render_sub_banner_custom_style', 999 );

if ( ! function_exists( 'goyoartdark_render_header_logo_custom_style' ) ) :
	/**
	 * 사이트 아이덴티티에서 설정한 헤더 로고 크기/마진을 CSS 변수로 출력한다.
	 *
	 * @return void
	 */
	function goyoartdark_render_header_logo_custom_style() {
		$logo_width_value  = (string) get_theme_mod( Goyoartdark_Theme_Mod_Registry::HEADER_LOGO_WIDTH, '188px;160px' );
		$logo_margin_value = (string) get_theme_mod( Goyoartdark_Theme_Mod_Registry::HEADER_LOGO_MARGIN, '0 0 0 0;0 0 0 0' );
		$logo_width        = '188px';
		$logo_width_mobile = '160px';
		$logo_margin       = '0 0 0 0';
		$logo_margin_mobile = '0 0 0 0';
		if ( function_exists( 'goyoartdark_sanitize_logo_width' ) ) {
			$logo_width_value = goyoartdark_sanitize_logo_width( $logo_width_value );
		}
		if ( function_exists( 'goyoartdark_sanitize_logo_margin' ) ) {
			$logo_margin_value = goyoartdark_sanitize_logo_margin( $logo_margin_value );
		}
		if ( false !== strpos( $logo_width_value, ';' ) ) {
			$logo_width_parts = explode( ';', $logo_width_value, 2 );
			$logo_width       = trim( (string) $logo_width_parts[0] );
			$logo_width_mobile = trim( (string) $logo_width_parts[1] );
		} else {
			$logo_width = trim( $logo_width_value );
			$logo_width_mobile = $logo_width;
		}
		if ( false !== strpos( $logo_margin_value, ';' ) ) {
			$logo_margin_parts = explode( ';', $logo_margin_value, 2 );
			$logo_margin       = trim( (string) $logo_margin_parts[0] );
			$logo_margin_mobile = trim( (string) $logo_margin_parts[1] );
		} else {
			$logo_margin = trim( $logo_margin_value );
			$logo_margin_mobile = $logo_margin;
		}
		?>
<style id="goyo-header-logo-custom-vars">:root{--goyo-header-logo-width:<?php echo esc_html( $logo_width ); ?>;--goyo-header-logo-margin:<?php echo esc_html( $logo_margin ); ?>;}@media screen and (max-width:520px){:root{--goyo-header-logo-width:<?php echo esc_html( $logo_width_mobile ); ?>;--goyo-header-logo-margin:<?php echo esc_html( $logo_margin_mobile ); ?>;}}</style>
		<?php
	}
endif;
add_action( 'wp_head', 'goyoartdark_render_header_logo_custom_style', 999 );

// 예전 header.php 에 넣던 광고·추가 head 코드: inc/head-custom-snippets.php 에 붙여넣기.
if ( ! function_exists( 'goyoartdark_render_head_custom_snippets' ) ) :
	/**
	 * 사용자 정의 head 스니펫 출력 (wp_head).
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_render_head_custom_snippets() {
		if ( is_admin() ) {
			return;
		}
		$path = get_parent_theme_file_path( 'inc/head-custom-snippets.php' );
		if ( is_readable( $path ) ) {
			include $path;
		}
	}
endif;
add_action( 'wp_head', 'goyoartdark_render_head_custom_snippets', 100 );

// 예전 footer.php 에 넣던 광고·추가 body 하단 코드: inc/foot-custom-snippets.php 에 붙여넣기.
if ( ! function_exists( 'goyoartdark_render_footer_custom_snippets' ) ) :
	/**
	 * 사용자 정의 footer 스니펫 출력 (wp_footer, </body> 직전).
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return void
	 */
	function goyoartdark_render_footer_custom_snippets() {
		if ( is_admin() ) {
			return;
		}
		$path = get_parent_theme_file_path( 'inc/foot-custom-snippets.php' );
		if ( is_readable( $path ) ) {
			include $path;
		}
	}
endif;
// 15: wp_print_footer_scripts(푸터 등록 JS, 기본 20)보다 앞에 출력. 이전 100이면 본문·스크립트 태그 뒤에 스니펫이 붙어
// (맨 위로 등) IIFE에서 한 번만 잡는 셀렉터·일부 init 이 빈 DOM 을 잡는 문제가 생길 수 있음.
add_action( 'wp_footer', 'goyoartdark_render_footer_custom_snippets', 15 );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'goyoartdark_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since goyoartdark 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function goyoartdark_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;


// 관리자페이지 알람 방지 
function remove_dashboard_notices() {
    // 모든 대시보드 메타박스 제거
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    // 일반적인 admin 알림 숨기기
    remove_all_actions('admin_notices');
    remove_all_actions('all_admin_notices');
    // Welcome 패널은 유지
    remove_action('welcome_panel', 'wp_welcome_panel');
}
add_action('wp_dashboard_setup', 'remove_dashboard_notices');

// 관리자 좌측 메뉴·상단바의 업데이트/대기 건수 뱃지(숫자 원형 알림) 시각 숨김 — 업데이트 기능 자체는 유지.
if ( ! function_exists( 'goyoartdark_enqueue_admin_hide_menu_badges' ) ) :
	/**
	 * @return void
	 */
	function goyoartdark_enqueue_admin_hide_menu_badges() {
		$handle = 'goyoartdark-admin-hide-menu-badges';
		wp_register_style( $handle, false, array(), wp_get_theme()->get( 'Version' ) );
		wp_enqueue_style( $handle );
		wp_add_inline_style(
			$handle,
			'#adminmenu .awaiting-mod,#adminmenu .update-plugins,#wpadminbar .update-plugins,#wpadminbar #wp-admin-bar-comments .awaiting-mod{display:none !important;}'
		);
	}
endif;
add_action( 'admin_enqueue_scripts', 'goyoartdark_enqueue_admin_hide_menu_badges', 99 );

// 코어 업데이트
add_filter('allow_dev_auto_core_updates', '__return_false');
//add_filter('allow_minor_auto_core_updates', '__return_false'); //보안
add_filter('allow_major_auto_core_updates', '__return_false');


//메일 발송 발신자 이름 변경
add_filter( 'wp_mail_from_name', 'custom_wp_mail_from_name' );
function custom_wp_mail_from_name( $original_email_from ) {
    return get_bloginfo('name'); 
}
// 새로운 사용자 등록시 관리자 이메일 알림 비활성화
add_filter( 'wp_new_user_notification_email_admin', '__return_false' );

// body에 page-{페이지 슬러그} 클래스 추가 (페이지·단일글 공통)
function add_slug_to_body_class($classes) {
    $obj = get_queried_object();
    if ($obj && isset($obj->post_name) && $obj->post_name !== '') {
        $classes[] = 'page-' . sanitize_html_class($obj->post_name);
    }
    return $classes;
}
add_filter('body_class', 'add_slug_to_body_class');


// 유효 홈일 때 코어 front-page 보강 + 테마 레이아웃 훅( 인라인/CSS 가 body.goyo-home-overlay 도 매칭 )
function add_custom_body_class( $classes ) {
	if ( goyoartdark_is_effective_front_page_for_assets() ) {
		if ( ! in_array( 'front-page', $classes, true ) ) {
			$classes[] = 'front-page';
		}
		if ( ! in_array( 'goyo-home-overlay', $classes, true ) ) {
			$classes[] = 'goyo-home-overlay';
		}
	}
	return $classes;
}
add_filter( 'body_class', 'add_custom_body_class' );

if ( ! function_exists( 'goyoartdark_body_class_home_layout_fallback' ) ) :
	/**
	 * 다른 훅이 늦게 body.front-page / goyo-home-overlay 를 빼도 is_front_page() 이면 다시 붙인다.
	 * front-page.css 의 .conWrap·고정 히어로 셀렉터가 body 클래스에 묶여 있어 레이아웃 붕괴를 막는다.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	function goyoartdark_body_class_home_layout_fallback( $classes ) {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return $classes;
		}
		if ( ! is_front_page() ) {
			return $classes;
		}
		if ( ! in_array( 'front-page', $classes, true ) ) {
			$classes[] = 'front-page';
		}
		if ( ! in_array( 'goyo-home-overlay', $classes, true ) ) {
			$classes[] = 'goyo-home-overlay';
		}
		return $classes;
	}
endif;
add_filter( 'body_class', 'goyoartdark_body_class_home_layout_fallback', 99 );

if ( ! function_exists( 'goyoartdark_body_class_unicorn_blend' ) ) :
	/**
	 * Unicorn이 검은 clear일 때 mix-blend-mode: screen( body 클래스 ) — 커스터마이저 goyo_unicorn_over_black
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	function goyoartdark_body_class_unicorn_blend( $classes ) {
		if ( ! goyoartdark_is_effective_front_page_for_assets() ) {
			return $classes;
		}
		if ( 'screen' === get_theme_mod( 'goyo_unicorn_over_black', 'screen' ) ) {
			$classes[] = 'goyo-unicorn-over-black--screen';
		}
		return $classes;
	}
endif;
add_filter( 'body_class', 'goyoartdark_body_class_unicorn_blend' );

// .hwp 업로드 허용 (MIME 타입 강제 설정 포함)
function allow_hwp_upload($mime_types) {
    $mime_types['hwp'] = 'application/octet-stream'; // 대부분 이렇게 감지됨
    return $mime_types;
}
add_filter('upload_mimes', 'allow_hwp_upload');

// MIME 타입 검사 무시 및 강제 허용
function fix_hwp_filetype_check($data, $file, $filename, $mimes) {
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'hwp') {
        $data['ext'] = 'hwp';
        $data['type'] = 'application/octet-stream'; // 실제 MIME
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'fix_hwp_filetype_check', 10, 4);

function allow_svg_uploads_with_check($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_uploads_with_check');

// SVG 파일을 업로드할 때 관리자만 허용 
function restrict_svg_upload_to_admin($file) {
    if (
        isset($file['type']) && $file['type'] === 'image/svg+xml' &&
        !current_user_can('manage_options')
    ) {
        $file['error'] = 'SVG 파일 업로드는 관리자만 가능합니다.';
    }
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'restrict_svg_upload_to_admin');

/**
 * iQ Block Country 필수 파일 누락 알림 강제 제거
 */
add_action('admin_init', function() {
    // 1. 플러그인의 메인 클래스 인스턴스를 가져와 알림 함수 제거 시도
    if (class_exists('iq_block_country')) {
        global $iq_block_country;
        // 알림을 뿌리는 핵심 메서드 제거
        remove_action('admin_notices', array($iq_block_country, 'iq_block_country_admin_notice'));
    }

    // 2. MaxMind 관련 특정 에러 알림 제거 (강제 필터링)
    global $wp_filter;
    if (isset($wp_filter['admin_notices'])) {
        foreach ($wp_filter['admin_notices']->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $idx => $callback) {
                // 콜백이 객체 형태이고 클래스명이 iq_block_country_backend 인 경우 차단
                if (is_array($callback['function']) && is_object($callback['function'][0])) {
                    $class_name = get_class($callback['function'][0]);
                    if ($class_name === 'iq_block_country_backend' || $class_name === 'iq_block_country') {
                        unset($wp_filter['admin_notices']->callbacks[$priority][$idx]);
                    }
                }
            }
        }
    }
}, 5);

/**
 * 3. CSS로 해당 문구 포함 박스 완전 박멸
 */
add_action('admin_head', function() {
    echo '<style>
        .notice.notice-error, .notice.error { display: none !important; } 
        /* 주의: 위 코드는 모든 에러 알림을 숨길 수 있으므로, 
           특정 문구(MaxMind)가 포함된 알림만 가리고 싶다면 아래 방식을 씁니다. */
        div.error:contains("MaxMind"), div.notice:contains("MaxMind") { display: none !important; }
    </style>';
    
    // jQuery를 이용한 텍스트 기반 강제 삭제 (가장 확실함)
    echo '<script>
        jQuery(document).ready(function($) {
            $("div.notice, div.error").each(function() {
                if ($(this).text().indexOf("MaxMind") !== -1 || $(this).text().indexOf("iQ Block Country") !== -1) {
                    $(this).remove();
                }
            });
        });
    </script>';
});
