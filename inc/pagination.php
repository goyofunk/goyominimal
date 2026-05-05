<!-- prev next -->

<section class="pagination-section">
    <?php 
        // 카테고리 정보 가져오기 (한 번만 실행)
        $categories = get_the_category();
        $category_id = !empty($categories) ? $categories[0]->term_id : 0;
        
        if ($category_id) {
            // 카테고리 메타데이터 가져오기 (한 번에 실행)
            $post_format = get_term_meta($category_id, 'post_format', true) ?: 'webzine';
            $category_width = get_term_meta($category_id, 'category_width', true);
            
            // 카테고리 폭 기본값 설정 (포트폴리오형, 포토갤러리형일 때만)
            if (!$category_width && ($post_format === 'portfolio' || $post_format === 'photo' || $post_format === 'photo4')) {
                $category_width = 'wide'; // 기본값: 넓게 (1380px)
            }
        } else {
            $post_format = 'webzine'; // 카테고리가 없는 경우 기본값
            $category_width = '';
        }

        // 컨테이너 클래스 설정 (포트폴리오형, 포토갤러리형일 때만 폭 클래스 추가)
        $container_class = 'container';
        if ($category_width && ($post_format === 'portfolio' || $post_format === 'photo' || $post_format === 'photo4')) {
            $container_class .= ' pagination-width-' . esc_attr($category_width);
        }
    ?>

    <div class="<?php echo esc_attr($container_class); ?>">
        <?php
        // pagination 클래스 기본값 설정
        $pagination_class = isset($pagination_class) ? $pagination_class : 'pagination post-navigation';
        
        // 이전글/다음글 표시 여부 확인
        $show_prev_next_posts = true; // 기본값: 표시
        if ($category_id) {
            $show_prev_next_posts_meta = get_term_meta($category_id, 'show_prev_next_posts', true);
            // 값이 없거나 '1'이면 표시, '0'이면 숨김
            if ($show_prev_next_posts_meta === '0') {
                $show_prev_next_posts = false;
            }
        }
        
        if ($category_id) {
            // 이전글/다음글 표시 옵션이 활성화되어 있을 때만 표시
            if ($show_prev_next_posts) {
                // WordPress 표준 함수를 사용하여 같은 카테고리 내 이전글/다음글 가져오기
                $prev_post = get_previous_post(true); // true = 같은 카테고리에서만 찾기
                $next_post = get_next_post(true);     // true = 같은 카테고리에서만 찾기
                
                if ($prev_post || $next_post) {
                    ?>
                    <div class="<?php echo esc_attr($pagination_class); ?>">
                        <?php
                        if ($prev_post) {
                            ?>
                            <span class="prev-link"><span class="link-text">이전글</span><a rel="prev" href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>"><?php echo esc_html(get_the_title($prev_post->ID)); ?></a></span> 
                            <?php
                        }
                        
                        if ($next_post) {
                            ?>
                            <span class="next-link"><span class="link-text">다음글</span><a rel="next" href="<?php echo esc_url(get_permalink($next_post->ID)); ?>"><?php echo esc_html(get_the_title($next_post->ID)); ?></a></span> 
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
            }
            
            // 카테고리 목록 링크 (항상 표시)
            foreach ($categories as $category) {
                echo '<span class="category-link goList"><a href="' . esc_url(get_category_link($category->term_id)) . '">목록으로</a></span>'; 
            }
        }
        ?>
    </div>
</section>
