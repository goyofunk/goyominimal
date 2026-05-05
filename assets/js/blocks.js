/**
 * goyoartdark 커스텀 블록 에디터 등록.
 *
 * 현재 등록 블록:
 *   - goyoartdark/site-menu : register_nav_menus 로 등록한 클래식 메뉴 로케이션을
 *                             프런트엔드에서 wp_nav_menu() 로 렌더하는 다이내믹 블록.
 *
 * 에디터 UX 설계 원칙:
 *   - 실제 메뉴 마크업( ul/li/a )을 미리보기하지 않는다. 에디터 CSS 와 충돌해 디자인이 깨지고,
 *     링크가 활성화되어 실수로 편집 화면이 이동하는 문제가 생긴다.
 *   - 대신 "이 자리에 어떤 메뉴가 들어갑니다" 라는 깔끔한 블록 플레이스홀더를 표시한다.
 *   - 고객은 이 플레이스홀더로 블록의 존재와 용도만 인지하고, 실제 메뉴 편집은
 *     외모 → 메뉴 화면( 해당 theme_location 할당 )에서 수행한다.
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element ) {
		return;
	}



	var blocks = wp.blocks;
	var createElement = wp.element.createElement;
	var __ = ( wp.i18n && wp.i18n.__ ) ? wp.i18n.__ : function ( text ) { return text; };



	// 로케이션 슬러그 → 사람이 읽기 쉬운 한국어 라벨 매핑.
	var LOCATION_LABELS = {
		'primary-menu': '주 메뉴 (Primary Menu)',
		'footer-menu': '푸터 메뉴 (Footer Menu)',
		'sidebar-menu': '사이드바 메뉴 (Sidebar Menu)'
	};



	function getLocationLabel( location ) {
		if ( LOCATION_LABELS.hasOwnProperty( location ) ) {
			return LOCATION_LABELS[ location ];
		}
		return location;
	}



	// 인라인 스타일 - 에디터 내 어떤 배경(라이트/다크 포함)에서도 또렷하게 보이도록 고정.
	// WP 어드민 블루 계열을 차용해 "시스템 블록" 이라는 시각적 인지 강화.
	var placeholderStyle = {
		display: 'flex',
		alignItems: 'center',
		justifyContent: 'center',
		flexWrap: 'wrap',
		gap: '10px',
		padding: '18px 20px',
		background: '#ffffff',
		border: '2px dashed #2271b1',
		borderRadius: '4px',
		color: '#1e1e1e',
		fontSize: '13px',
		lineHeight: '1.4',
		textAlign: 'center',
		userSelect: 'none',
		boxShadow: '0 0 0 1px rgba(255, 255, 255, 0.6)'
	};



	var iconStyle = {
		display: 'inline-flex',
		alignItems: 'center',
		justifyContent: 'center',
		width: '26px',
		height: '26px',
		borderRadius: '4px',
		background: '#2271b1',
		color: '#ffffff',
		fontSize: '16px',
		fontWeight: 'bold',
		lineHeight: '1'
	};



	var titleStyle = {
		fontWeight: '600',
		color: '#1e1e1e'
	};



	var hintStyle = {
		fontSize: '11px',
		color: '#555',
		opacity: 0.9
	};



	// 플레이스홀더 공통 렌더러 - 블록 종류와 무관하게 동일한 시각 언어를 사용.
	function renderPlaceholder( iconText, title, hint ) {
		return createElement(
			'div',
			{ className: 'goyoartdark-block-placeholder', style: placeholderStyle },
			createElement( 'span', { style: iconStyle, 'aria-hidden': 'true' }, iconText ),
			createElement( 'span', { style: titleStyle }, title ),
			hint ? createElement( 'span', { style: hintStyle }, hint ) : null
		);
	}



	blocks.registerBlockType( 'goyoartdark/site-menu', {
		edit: function ( props ) {
			var location = ( props.attributes && props.attributes.location ) || 'primary-menu';
			var label = getLocationLabel( location );



			return renderPlaceholder(
				'≡',
				__( '사이트 메뉴', 'goyoartdark' ) + ': ' + label,
				__( '— 실제 메뉴는 공개 사이트에서 표시됩니다', 'goyoartdark' )
			);
		},
		save: function () {
			return null;
		}
	} );



	blocks.registerBlockType( 'goyoartdark/site-header', {
		edit: function () {
			return renderPlaceholder(
				'▣',
				__( '사이트 헤더 내비 (주 메뉴 · SNS · 검색 · 모바일 메뉴)', 'goyoartdark' ),
				__( '— 로고는 헤더 패턴에서, SNS/검색은 사용자 정의하기, 메뉴는 외모 → 메뉴에서 설정하세요', 'goyoartdark' )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp );

