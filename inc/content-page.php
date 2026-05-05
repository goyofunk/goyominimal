<?php
/*
 * 싱글 콘텐츠
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php if ( ! is_single() ) : ?>
    <div class="post-header">
        <h1 class="post-title"><?php the_title(); ?></h1>
        <div class="info">
            <?php
            // 현재 게시글의 카테고리에서 날짜 표시 설정 확인
            $categories = get_the_category();
            $show_date = true; // 기본값은 true

            if (!empty($categories)) {
                $category = $categories[0]; // 첫 번째 카테고리 사용
                $show_date_setting = get_term_meta($category->term_id, 'show_date', true);
                $show_date = ($show_date_setting !== '0'); // '0'이 아니면 표시
            }

            if ($show_date) {
                echo '<span class="post-date">' . esc_html( get_the_date('Y-m-d') ) . '</span>';
            }
            ?>
            <?php
                $tags = get_the_tags();
                if ($tags) { ?>
                <div class="post-tags">
                    <?php foreach ($tags as $tag) {
                        echo '<span class="tag">' . esc_html($tag->name) . '</span> ';
                    } ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="post-content container">
        <?php the_content(); ?>
    </div>
</article>