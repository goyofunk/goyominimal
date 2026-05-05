<?php
/**
 * 유튜브 배경 숏코드
 *
 * 사용 예:
 * [goyo_youtube_bg url="https://www.youtube.com/watch?v=LcF6ut-1M94" autoplay="0" height="60vh" overlay="0.35" class="my-video"]
 */

if ( ! function_exists( 'goyoartdark_extract_youtube_video_id' ) ) :
	/**
	 * 다양한 유튜브 URL 형식에서 video id 를 추출한다.
	 *
	 * @param string $raw_url 원본 유튜브 URL.
	 * @return string video id 또는 빈 문자열.
	 */
	function goyoartdark_extract_youtube_video_id( $raw_url ) {
		$url = trim( (string) $raw_url );
		if ( '' === $url ) {
			return '';
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			return '';
		}

		$host = strtolower( (string) $parts['host'] );
		$path = isset( $parts['path'] ) ? trim( (string) $parts['path'], '/' ) : '';

		if ( false !== strpos( $host, 'youtu.be' ) ) {
			$first_segment = strtok( $path, '/' );
			return preg_match( '/^[A-Za-z0-9_-]{11}$/', (string) $first_segment ) ? (string) $first_segment : '';
		}

		if ( false !== strpos( $host, 'youtube.com' ) || false !== strpos( $host, 'youtube-nocookie.com' ) ) {
			if ( isset( $parts['query'] ) ) {
				parse_str( (string) $parts['query'], $query );
				if ( ! empty( $query['v'] ) && preg_match( '/^[A-Za-z0-9_-]{11}$/', (string) $query['v'] ) ) {
					return (string) $query['v'];
				}
			}

			$segments = array_values( array_filter( explode( '/', $path ) ) );
			if ( count( $segments ) >= 2 && in_array( $segments[0], array( 'embed', 'shorts', 'live' ), true ) ) {
				return preg_match( '/^[A-Za-z0-9_-]{11}$/', (string) $segments[1] ) ? (string) $segments[1] : '';
			}
		}

		return '';
	}
endif;

if ( ! function_exists( 'goyoartdark_render_youtube_background_shortcode' ) ) :
	/**
	 * [goyo_youtube_bg] 숏코드 렌더링.
	 *
	 * @param array<string, string> $atts 숏코드 속성.
	 * @return string
	 */
	function goyoartdark_render_youtube_background_shortcode( $atts ) {
		$defaults = array(
			'url'     => '',
			'autoplay'=> '0',
			'controls'=> '0',
			'height'  => '56.25vw',
			'max'     => '680px',
			'min'     => '320px',
			'overlay' => '0.35',
			'class'   => '',
		);

		$attrs = shortcode_atts( $defaults, $atts, 'goyo_youtube_bg' );
		$video_id = goyoartdark_extract_youtube_video_id( (string) $attrs['url'] );
		if ( '' === $video_id ) {
			return '';
		}

		$height_value = trim( (string) $attrs['height'] );
		$max_height   = trim( (string) $attrs['max'] );
		$min_height   = trim( (string) $attrs['min'] );
		$overlay      = (float) $attrs['overlay'];
		$overlay      = max( 0, min( 0.8, $overlay ) );
		$autoplay     = in_array( strtolower( trim( (string) $attrs['autoplay'] ) ), array( '1', 'true', 'yes', 'on' ), true ) ? '1' : '0';
		$controls     = in_array( strtolower( trim( (string) $attrs['controls'] ) ), array( '1', 'true', 'yes', 'on' ), true ) ? '1' : '0';
		$raw_class    = trim( (string) $attrs['class'] );
		$class_tokens = preg_split( '/\s+/', $raw_class );
		$class_tokens = is_array( $class_tokens ) ? array_filter( $class_tokens ) : array();
		$extra_classes = array_map( 'sanitize_html_class', $class_tokens );
		$extra_class   = implode( ' ', array_filter( $extra_classes ) );

		if ( '' === $height_value || ! preg_match( '/^[0-9\.\s\-\+\*\/a-zA-Z\(\),%]+$/', $height_value ) ) {
			$height_value = '56.25vw';
		}
		if ( '' === $max_height || ! preg_match( '/^[0-9\.\sa-zA-Z%]+$/', $max_height ) ) {
			$max_height = '680px';
		}
		if ( '' === $min_height || ! preg_match( '/^[0-9\.\sa-zA-Z%]+$/', $min_height ) ) {
			$min_height = '320px';
		}

		$wrapper_classes = 'goyo-youtube-bg';
		if ( '' !== $extra_class ) {
			$wrapper_classes .= ' ' . $extra_class;
		}

		$src = add_query_arg(
			array(
				'autoplay'       => $autoplay,
				'mute'           => '1',
				'controls'       => $controls,
				'disablekb'      => '1',
				'iv_load_policy' => '3',
				'fs'             => '0',
				'loop'           => '1',
				'playlist'       => $video_id,
				'rel'            => '0',
				'modestbranding' => '1',
				'playsinline'    => '1',
				'enablejsapi'    => '1',
			),
			'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video_id )
		);

		$style = sprintf(
			'--goyo-youtube-height:%1$s;--goyo-youtube-min-height:%2$s;--goyo-youtube-max-height:%3$s;--goyo-youtube-overlay:%4$s;',
			esc_attr( $height_value ),
			esc_attr( $min_height ),
			esc_attr( $max_height ),
			esc_attr( (string) $overlay )
		);

		ob_start();
		?>
		<div class="<?php echo esc_attr( $wrapper_classes ); ?>" style="<?php echo esc_attr( $style ); ?>">
			<div class="goyo-youtube-bg__media" aria-hidden="true">
				<iframe class="goyo-youtube-bg__iframe" src="<?php echo esc_url( $src ); ?>" title="<?php echo esc_attr__( '유튜브 배경 영상', 'goyoartdark' ); ?>" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin"></iframe>
			</div>
		</div>
		<?php
		return trim( (string) ob_get_clean() );
	}
endif;
add_shortcode( 'goyo_youtube_bg', 'goyoartdark_render_youtube_background_shortcode' );
