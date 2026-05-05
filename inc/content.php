<?php
/*
 * 컴포넌트 - 공용 컨텐츠
 */
?>
<article id="post-<?php the_ID(); ?>" class="" <?php post_class(); ?>>
    <a href="<?php echo esc_url( get_permalink() ); ?>">


        <?php 
        $has_thumbnail = has_post_thumbnail();
        $content = get_the_content();
        preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
        $has_content_image = isset($matches[1]) && !empty($matches[1]);
        $mainimg = null;
        $has_mainimg = false; // ACF 플러그인이 없을 때는 false로 설정
        
        // category.php에서 전달된 이미지 사이즈 사용, 없으면 기본값 'thumbnail' (WordPress 관리자 페이지의 미디어 설정 사용)
        $image_size = get_query_var('thumbnail_size') ?: 'thumbnail';

        if ($has_thumbnail || $has_mainimg || $has_content_image) : ?>
        <div class="card-thumbnail">
            <?php 
            if ($has_thumbnail) {
                // 대표이미지가 있을 때: 지정된 사이즈 사용, fallback 로직 포함
                $thumbnail_id = get_post_thumbnail_id();
                $thumbnail_url = wp_get_attachment_image_src($thumbnail_id, $image_size);
                
                // 요청한 사이즈가 없으면 fallback 사이즈 시도
                if (!$thumbnail_url || empty($thumbnail_url[0])) {
                    $fallback_sizes = array('medium', 'thumbnail', 'medium_large', 'large');
                    foreach ($fallback_sizes as $fallback_size) {
                        $thumbnail_url = wp_get_attachment_image_src($thumbnail_id, $fallback_size);
                        if ($thumbnail_url && !empty($thumbnail_url[0])) {
                            break;
                        }
                    }
                }
                
                if ($thumbnail_url && !empty($thumbnail_url[0])) {
                    echo '<img src="' . esc_url($thumbnail_url[0]) . '" alt="' . esc_attr(get_the_title()) . '" width="' . esc_attr($thumbnail_url[1]) . '" height="' . esc_attr($thumbnail_url[2]) . '" class="poster-thumbnail-img" />';
                } else {
                    // 모든 사이즈가 없을 때는 원본 사용 (드문 경우)
                    the_post_thumbnail($image_size, ['class' => 'poster-thumbnail-img']);
                }
            } else if ($has_mainimg) {
                if (is_array($mainimg) && isset($mainimg['url'])) {
                    $img_url = $mainimg['url'];
                } else if (is_numeric($mainimg)) {
                    $img_url = wp_get_attachment_url($mainimg);
                } else {
                    $img_url = $mainimg;
                }
                if ($img_url) {
                    echo '<img src="' . esc_url($img_url) . '" alt="' . esc_attr(get_the_title()) . '" class="poster-thumbnail-img" />';
                }
            } else if ($has_content_image) {
                // 본문에서 첫 번째 이미지 찾기: 최적화된 로직 적용
                $first_image_url = $matches[1];
                $attachment_id = attachment_url_to_postid($first_image_url);
                
                if ($attachment_id) {
                    // attachment ID가 있으면 지정된 사이즈부터 시도
                    $thumbnail_url = wp_get_attachment_image_src($attachment_id, $image_size);
                    
                    // 요청한 사이즈가 없으면 여러 fallback 사이즈 시도
                    if (!$thumbnail_url || empty($thumbnail_url[0])) {
                        $fallback_sizes = array('medium', 'thumbnail', 'medium_large', 'large');
                        foreach ($fallback_sizes as $fallback_size) {
                            $thumbnail_url = wp_get_attachment_image_src($attachment_id, $fallback_size);
                            if ($thumbnail_url && !empty($thumbnail_url[0])) {
                                break;
                            }
                        }
                    }
                    
                    if ($thumbnail_url && !empty($thumbnail_url[0])) {
                        echo '<img src="' . esc_url($thumbnail_url[0]) . '" alt="' . esc_attr(get_the_title()) . '" width="' . esc_attr($thumbnail_url[1]) . '" height="' . esc_attr($thumbnail_url[2]) . '" class="poster-thumbnail-img" />';
                    } else {
                        // attachment ID는 있지만 모든 사이즈가 없을 때는 원본 사용 (드문 경우)
                        echo '<img src="' . esc_url($first_image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="poster-thumbnail-img" />';
                    }
                } else {
                    // attachment ID가 없을 때: WordPress 미디어 라이브러리 경로인지 확인하고 리사이즈 버전 찾기
                    $resized_url = function_exists('goyo_get_resized_image_url_from_path') ? goyo_get_resized_image_url_from_path($first_image_url, $image_size) : $first_image_url;
                    if ($resized_url && $resized_url !== $first_image_url) {
                        echo '<img src="' . esc_url($resized_url) . '" alt="' . esc_attr(get_the_title()) . '" class="poster-thumbnail-img" />';
                    } else {
                        // 리사이즈 버전을 찾지 못했을 때는 원본 사용 (외부 이미지 등)
                        echo '<img src="' . esc_url($first_image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="poster-thumbnail-img" />';
                    }
                }
            }
            ?>
        </div>
        <?php else: ?>
        <div class="card-thumbnail nothumb">
            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/default-thumbnail.jpg" alt="<?php echo esc_attr(get_the_title()); ?>" class="poster-thumbnail-img nothumb" />
        </div>
        <?php endif; ?>


        
        <div class="entry-text-wrap">
            <h2 class="entry-title"><?php the_title(); ?></h2>
            <div class="entry-info">
                <?php
                // queried object가 카테고리(WP_Term)일 때만 term_id 사용.
                // 관련글 컨텍스트에서는 get_queried_object_id()가 포스트 ID를 반환하므로
                // 이 경우 현재 글의 첫 번째 카테고리에서 term_id를 가져온다.
                $queried_object = get_queried_object();
                if ( $queried_object instanceof WP_Term ) {
                    $category_id = $queried_object->term_id;
                } else {
                    $post_categories = get_the_category();
                    $category_id     = ! empty( $post_categories ) ? $post_categories[0]->term_id : 0;
                }

                // get_query_var로 전달된 값 가져오기 (관련글용)
                $show_excerpt = get_query_var('show_excerpt');
                $show_date    = get_query_var('show_date');

                // 관련글에서 전달된 값이 없으면 카테고리 메타데이터에서 가져오기
                if ( $show_excerpt === '' ) {
                    $show_excerpt = get_term_meta( $category_id, 'show_excerpt', true );
                }
                if ( $show_date === '' ) {
                    $show_date = get_term_meta( $category_id, 'show_date', true );
                }

                // 요약글은 텍스트만 출력해 비정상 p 태그가 카드 링크 DOM을 깨뜨리는 상황을 방지한다.
                if ($show_excerpt === '1') :
                    $excerpt_text = wp_strip_all_tags( (string) get_the_excerpt(), true );
                    if ( '' !== $excerpt_text ) :
                    ?>
                    <div class="entry-excerpt"><?php echo esc_html( $excerpt_text ); ?></div>
                    <?php
                    endif;
                endif;
                ?>
                <span class="list-author"><?php echo esc_html( get_the_author() ); ?></span>
                <?php 
                if ($show_date === '1') : ?>
                    <span class="entry-date"><?php echo get_the_date('Y-m-d'); ?></span>
                <?php endif; ?>
                
            </div>
        </div>
    </a>
</article>