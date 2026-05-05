<?php
/**
 * Query Loop 대표이미지 폴백
 *
 * core/post-featured-image 블록이 비어 렌더될 때,
 * 대표이미지 대신 본문 첫 이미지를 출력한다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'goyoartdark_render_query_loop_featured_image_fallback' ) ) :
	/**
	 * Query Loop 내 core/post-featured-image 렌더링 폴백.
	 *
	 * @param string   $block_content 블록 렌더링 결과 HTML.
	 * @param array    $parsed_block  파싱된 블록 데이터.
	 * @param WP_Block $block         블록 인스턴스.
	 * @return string
	 */
	function goyoartdark_render_query_loop_featured_image_fallback( $block_content, $parsed_block, $block ) {
		$rendered_html = (string) $block_content;
		if ( preg_match( '/<img\b/i', $rendered_html ) ) {
			return $block_content;
		}

		$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : 0;
		if ( $post_id <= 0 ) {
			return $block_content;
		}

		if ( has_post_thumbnail( $post_id ) ) {
			return $block_content;
		}

		$image_url = '';
		if ( function_exists( 'goyoartdark_main_page_entry_image_url' ) ) {
			$image_url = (string) goyoartdark_main_page_entry_image_url( $post_id, 'large' );
		}
		if ( '' === $image_url ) {
			return $block_content;
		}

		$attrs = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
		$classes = array( 'wp-block-post-featured-image' );
		if ( ! empty( $attrs['align'] ) ) {
			$classes[] = 'align' . sanitize_html_class( (string) $attrs['align'] );
		}
		if ( ! empty( $attrs['className'] ) ) {
			$classes[] = sanitize_html_class( (string) $attrs['className'] );
		}

		$img_attrs = array(
			'src' => esc_url( $image_url ),
			'alt' => esc_attr( wp_strip_all_tags( get_the_title( $post_id ) ) ),
			'class' => 'wp-post-image',
			'loading' => 'lazy',
			'decoding' => 'async',
		);

		if ( ! empty( $attrs['aspectRatio'] ) && is_string( $attrs['aspectRatio'] ) ) {
			$img_attrs['style'] = 'aspect-ratio:' . esc_attr( $attrs['aspectRatio'] ) . ';object-fit:cover;';
		}

		$img_html = sprintf(
			'<img src="%1$s" alt="%2$s" class="%3$s" loading="%4$s" decoding="%5$s"%6$s />',
			$img_attrs['src'],
			$img_attrs['alt'],
			$img_attrs['class'],
			$img_attrs['loading'],
			$img_attrs['decoding'],
			isset( $img_attrs['style'] ) ? ' style="' . esc_attr( $img_attrs['style'] ) . '"' : ''
		);

		$is_link = ! empty( $attrs['isLink'] );
		if ( $is_link ) {
			$img_html = '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . $img_html . '</a>';
		}

		return '<figure class="' . esc_attr( implode( ' ', array_filter( array_unique( $classes ) ) ) ) . '">' . $img_html . '</figure>';
	}

endif;

add_filter( 'render_block_core/post-featured-image', 'goyoartdark_render_query_loop_featured_image_fallback', 10, 3 );
