<?php
if ( ! function_exists( 'goyoartdark_related_post_ids_closest_by_date' ) ) {
	/**
	 * 같은 카테고리 글만 후보로 두고, 발행 시각이 현재 글과 가장 가까운 순으로 최대 $limit개 ID를 반환한다.
	 * 최신글만 있거나 과거 글만 있어도, 시간 거리가 짧은 쪽부터 채운다.
	 *
	 * @param int    $exclude_post_id   현재 글(제외).
	 * @param string $comparison_date   비교 기준 시각(post_date DB 문자열).
	 * @param int[]  $category_term_ids 카테고리 term_id 목록.
	 * @param int    $limit             최대 개수.
	 * @return int[] 가까운 순 글 ID.
	 */
	function goyoartdark_related_post_ids_closest_by_date( $exclude_post_id, $comparison_date, $category_term_ids, $limit = 12 ) {
		$exclude_post_id     = absint( $exclude_post_id );
		$limit               = absint( $limit );
		$category_term_ids   = array_filter( array_map( 'absint', (array) $category_term_ids ) );
		$comparison_ts       = strtotime( $comparison_date );
		if ( $exclude_post_id < 1 || $limit < 1 || $comparison_ts < 1 || empty( $category_term_ids ) ) {
			return array();
		}

		$base = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'post__not_in'        => array( $exclude_post_id ),
			'category__in'        => $category_term_ids,
			'posts_per_page'      => $limit,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		);

		$newer_ids = get_posts(
			array_merge(
				$base,
				array(
					'date_query' => array(
						array(
							'after'     => $comparison_date,
							'inclusive' => false,
							'column'    => 'post_date',
						),
					),
					'orderby' => 'date',
					'order'   => 'ASC',
				)
			)
		);

		$older_ids = get_posts(
			array_merge(
				$base,
				array(
					'date_query' => array(
						array(
							'before'    => $comparison_date,
							'inclusive' => false,
							'column'    => 'post_date',
						),
					),
					'orderby' => 'date',
					'order'   => 'DESC',
				)
			)
		);

		$i        = 0;
		$j        = 0;
		$newer_n  = count( $newer_ids );
		$older_n  = count( $older_ids );
		$result   = array();

		while ( count( $result ) < $limit && ( $i < $newer_n || $j < $older_n ) ) {
			$delta_newer = null;
			$delta_older = null;

			if ( $i < $newer_n ) {
				$ts = strtotime( (string) get_post_field( 'post_date', $newer_ids[ $i ] ) );
				if ( $ts > 0 ) {
					$delta_newer = $ts - $comparison_ts;
				}
			}
			if ( $j < $older_n ) {
				$ts = strtotime( (string) get_post_field( 'post_date', $older_ids[ $j ] ) );
				if ( $ts > 0 ) {
					$delta_older = $comparison_ts - $ts;
				}
			}

			if ( null !== $delta_newer && null !== $delta_older ) {
				if ( $delta_newer <= $delta_older ) {
					$result[] = absint( $newer_ids[ $i ] );
					++$i;
				} else {
					$result[] = absint( $older_ids[ $j ] );
					++$j;
				}
			} elseif ( null !== $delta_newer ) {
				$result[] = absint( $newer_ids[ $i ] );
				++$i;
			} elseif ( null !== $delta_older ) {
				$result[] = absint( $older_ids[ $j ] );
				++$j;
			} else {
				if ( $i < $newer_n ) {
					++$i;
				}
				if ( $j < $older_n ) {
					++$j;
				}
				if ( $i >= $newer_n && $j >= $older_n ) {
					break;
				}
			}
		}

		return array_values( array_unique( array_filter( $result ) ) );
	}
}
?>
<section class="related-section">
    <?php 
        // 카테고리 정보 가져오기 (한 번만 실행)
        $categories = get_the_category();
        $category_id = !empty($categories) ? $categories[0]->term_id : 0;
        $current_slug = !empty($categories) ? $categories[0]->slug : '';
        
        if ($category_id) {
            // 카테고리 메타데이터 가져오기 (한 번에 실행)
            $post_format = get_term_meta($category_id, 'post_format', true) ?: 'webzine';
            $show_excerpt = get_term_meta($category_id, 'show_excerpt', true);
            $show_date = get_term_meta($category_id, 'show_date', true);
            $thumbnail_ratio = get_term_meta($category_id, 'thumbnail_ratio', true);
            $category_width = get_term_meta($category_id, 'category_width', true);
            
            // 기본값 설정
            if (!$thumbnail_ratio) {
                if ($post_format === 'portfolio') {
                    $thumbnail_ratio = '16-9';
                } elseif ($post_format === 'photo' || $post_format === 'photo4') {
                    $thumbnail_ratio = '1-1';
                }
            }
            
            // 카테고리 폭 기본값 설정 (포트폴리오형, 포토갤러리형일 때만)
            if (!$category_width && ($post_format === 'portfolio' || $post_format === 'photo' || $post_format === 'photo4')) {
                $category_width = 'wide'; // 기본값: 넓게 (1380px)
            }
        } else {
            $post_format = 'webzine'; // 카테고리가 없는 경우 기본값
            $show_excerpt = false;
            $show_date = false;
            $thumbnail_ratio = '';
            $category_width = '';
        }

        // 컨테이너 클래스 설정 (포트폴리오형, 포토갤러리형일 때만 폭 클래스 추가)
        $container_class = 'container';
        if ($category_width && ($post_format === 'portfolio' || $post_format === 'photo' || $post_format === 'photo4')) {
            $container_class .= ' related-width-' . esc_attr($category_width);
        }
    ?>

    <div class="<?php echo esc_attr($container_class); ?>">
        <h3 class="related-title">관련글</h3>
        <div class="row <?php echo esc_attr($post_format); ?><?php echo ($thumbnail_ratio && ($post_format === 'portfolio' || $post_format === 'photo' || $post_format === 'photo4')) ? ' ratio-' . esc_attr($thumbnail_ratio) : ''; ?>">
            <?php
            $cats = wp_get_post_terms(get_the_ID(), 'category'); // 현재 카테고리
            $cats_ids = array(); //카테고리 id 배열

            foreach ($cats as $related_cat) {
                $cats_ids[] = $related_cat->term_id;
            }

            $current_id = get_the_ID();
            $current_post_obj = get_post($current_id);
            $compare_date = $current_post_obj ? $current_post_obj->post_date : '';

            if (!empty($cats_ids) && $compare_date !== '') {
                $related_ids = goyoartdark_related_post_ids_closest_by_date($current_id, $compare_date, $cats_ids, 12);
                $arr_posts = new WP_Query(
                    array(
                        'post__in'          => !empty($related_ids) ? $related_ids : array(0),
                        'orderby'           => 'post__in',
                        'posts_per_page'    => 12,
                        'no_found_rows'     => true,
                        'ignore_sticky_posts' => true,
                    )
                );
            } else {
                $args = array(
                    'posts_per_page' => 12,
                    'post__not_in' => array($current_id),
                    'no_found_rows' => true,
                );
                if (!empty($cats_ids)) {
                    $args['category__in'] = $cats_ids;
                }
                $arr_posts = new WP_Query($args);
            } ?>
            <?php if ($arr_posts->have_posts()): ?>
                <?php while ($arr_posts->have_posts()):
                    $arr_posts->the_post(); ?>
                    <div class="col">
                        <?php 
                        set_query_var('show_excerpt', $show_excerpt);
                        set_query_var('show_date', $show_date);
                        get_template_part('inc/content'); 
                        ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
            <?php get_template_part('inc/content', 'empty'); ?>
            <?php endif; ?>
        </div>
    </div>
</section>
