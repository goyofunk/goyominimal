<?php
/**
 * 페이지 관련 기능
 *
 * 페이지별 타이틀 표시/숨김, 가로폭 설정을 관리합니다.
 */

// ─────────────────────────────────────────────
// 타이틀 표시
// ─────────────────────────────────────────────

/** 페이지 편집 화면에 '타이틀 표시' 메타박스 추가 */
function goyo_add_page_title_meta_box() {
    add_meta_box(
        'goyo_page_title',
        '타이틀 표시',
        'goyo_page_title_meta_box_callback',
        'page',
        'side'
    );
}
add_action('add_meta_boxes', 'goyo_add_page_title_meta_box');

/** 메타박스 콘텐츠 출력 */
function goyo_page_title_meta_box_callback($post) {
    wp_nonce_field('goyo_page_title_nonce', 'goyo_page_title_nonce');
    $show_title = get_post_meta($post->ID, '_show_page_title', true);
    if ($show_title === '') {
        $show_title = '1';
    }
    ?>
    <label>
        <input type="checkbox" name="show_page_title" value="1" <?php checked($show_title, '1'); ?>>
        페이지 상단에 타이틀 표시
    </label>
    <p class="description" style="margin-top: 6px;">체크 해제 시 본문 영역의 제목이 숨겨집니다.</p>
    <?php
}

/** 페이지 저장 시 메타 저장 */
function goyo_save_page_title_meta($post_id) {
    if (!isset($_POST['goyo_page_title_nonce']) || !wp_verify_nonce($_POST['goyo_page_title_nonce'], 'goyo_page_title_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    // 체크 시 표시(1), 해제 시 숨김(0). 메타가 없을 때는 프론트에서 기본값 '보임'으로 처리.
    if (isset($_POST['show_page_title'])) {
        update_post_meta($post_id, '_show_page_title', '1');
    } else {
        update_post_meta($post_id, '_show_page_title', '0');
    }
}
add_action('save_post_page', 'goyo_save_page_title_meta');


// ─────────────────────────────────────────────
// 페이지 가로폭
// ─────────────────────────────────────────────

/** 허용된 가로폭 값 목록 (theme.json 의 settings.custom.layout 값과 동일하게 유지) */
function goyo_page_width_options() {
    return [
        '720px'  => '아주좁게 (720px)',
        '1000px' => '좁게 (1000px)',
        '1200px' => '보통 (1200px)',
        '1380px' => '넓게 (1380px)',
    ];
}

/** 페이지 편집 화면에 '페이지 가로폭' 메타박스 추가 */
function goyo_add_page_width_meta_box() {
    add_meta_box(
        'goyo_page_width',
        '페이지 가로폭',
        'goyo_page_width_meta_box_callback',
        'page',
        'side'
    );
}
add_action('add_meta_boxes', 'goyo_add_page_width_meta_box');

/** 메타박스 콘텐츠 출력 */
function goyo_page_width_meta_box_callback($post) {
    wp_nonce_field('goyo_page_width_nonce', 'goyo_page_width_nonce');

    $saved_width = get_post_meta($post->ID, '_page_content_width', true);
    // 저장된 값이 없으면 기본값 '좁게(1000px)'
    if ($saved_width === '') {
        $saved_width = '1000px';
    }

    $options = goyo_page_width_options();
    ?>
    <select name="page_content_width" id="page_content_width" style="width: 100%;">
        <?php foreach ($options as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($saved_width, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description" style="margin-top: 6px;">.container.subbox 의 max-width 값이 변경됩니다.</p>
    <?php
}

/** 페이지 저장 시 가로폭 메타 저장 */
function goyo_save_page_width_meta($post_id) {
    if (!isset($_POST['goyo_page_width_nonce']) || !wp_verify_nonce($_POST['goyo_page_width_nonce'], 'goyo_page_width_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 허용된 값만 저장 (보안: 임의 CSS값 주입 방지)
    $allowed = array_keys(goyo_page_width_options());
    $width   = isset($_POST['page_content_width']) ? sanitize_text_field($_POST['page_content_width']) : '1000px';

    if (!in_array($width, $allowed, true)) {
        $width = '1000px';
    }

    update_post_meta($post_id, '_page_content_width', $width);
}
add_action('save_post_page', 'goyo_save_page_width_meta');

/** 프론트엔드: 페이지 가로폭 + 타이틀 숨김을 한 번에 인라인 CSS 로 출력 */
function goyo_output_page_inline_styles() {
    if (!is_page()) {
        return;
    }

    $page_id = get_the_ID();
    $is_front_page = is_front_page();
    $page_banner_image_url = get_the_post_thumbnail_url( $page_id, 'full' );

    // 프론트페이지는 페이지 가로폭 옵션을 무시하고 항상 전체폭(100%)을 사용한다.
    // 그 외 페이지는 기존 메타값(미저장/비허용값 시 1000px 폴백)을 유지한다.
    $width = $is_front_page ? '100%' : goyo_get_validated_page_width($page_id);

    // 타이틀 표시 여부: 메타 없음 → 보임('1')
    $show_title = get_post_meta($page_id, '_show_page_title', true);
    if ($show_title === '') {
        $show_title = '1';
    }

    // (1) 레거시 래퍼 .container.subbox 방식(패턴에서 직접 클래스 지정한 경우) 지원.
    $css  = '.page .container.subbox{max-width:' . esc_attr($width) . ';}';

    // (2) 블록 테마 기본: is-layout-constrained 는 CSS 변수(content-size/wide-size)로 폭을 제어한다.
    //     변수 덮어쓰기를 <main> 스코프로 한정해 header/footer template-part 의 constrained 블록은
    //     테마 기본 폭을 유지하도록 한다. (page.html/single.html 구조 기준)
    $css .= 'body.page-id-' . (int) $page_id . ' main{'
          . '--wp--style--global--content-size:' . esc_attr($width) . ';';
    if (!$is_front_page) {
        $css .= '--wp--style--global--wide-size:' . esc_attr($width) . ';';
    }
    $css .= '}';

    if ( $page_banner_image_url ) {
        $css .= 'body.page-id-' . (int) $page_id . ' .subBanner{background-image:url("' . esc_url_raw( $page_banner_image_url ) . '");}';
    }

    if ($show_title === '0') {
        // 페이지 상단 기본 타이틀(.wp-block-post-title)만 숨긴다.
        // 바로 뒤에 post-content가 오는 기본 페이지 타이틀만 숨겨, 본문 내 타이틀 블록에는 영향 주지 않는다.
        $css .= 'body.hide-page-title main .wp-block-post-title:has(+ .entry-content.wp-block-post-content){display:none;}';
    }

    echo '<style>' . $css . '</style>' . "\n";
}
add_action('wp_head', 'goyo_output_page_inline_styles');

/** 페이지 타이틀 숨김 여부를 body 클래스로 노출 (CSS 셀렉터 타겟팅용) */
function goyo_add_page_title_body_class($classes) {
    if (!is_page()) {
        return $classes;
    }

    $show_title = get_post_meta(get_the_ID(), '_show_page_title', true);
    if ($show_title === '0') {
        $classes[] = 'hide-page-title';
    }

    return $classes;
}
add_filter('body_class', 'goyo_add_page_title_body_class');


// ─────────────────────────────────────────────
// 블록 에디터: 편집창 내부에도 페이지 가로폭 반영
// ─────────────────────────────────────────────

/**
 * 현재 편집 중인 페이지의 저장된 가로폭을 반환 (허용값 외 / 미저장 시 기본 1000px).
 */
function goyo_get_validated_page_width($post_id) {
    $width   = get_post_meta((int) $post_id, '_page_content_width', true);
    $allowed = array_keys(goyo_page_width_options());
    if ($width === '' || !in_array($width, $allowed, true)) {
        $width = '1000px';
    }
    return $width;
}

/**
 * 블록 에디터 캔버스 미리보기용 가로폭 (메타와 별도).
 *
 * 정적 프론트로 지정된 페이지는 프론트에서 페이지 폭 메타를 쓰지 않으므로(goyo_output_page_inline_styles),
 * 편집창도 theme.json 기본(720px)에 끌려가지 않도록 100% 로 맞춘다.
 */
function goyo_get_editor_canvas_page_width($post_id) {
    $post_id = (int) $post_id;
    if ($post_id > 0 && (int) get_option('page_on_front') === $post_id) {
        return '100%';
    }
    return goyo_get_validated_page_width($post_id);
}

/**
 * URL 쿼리 배열에서 에디터 canvas 폭 후보를 해석한다.
 *
 * @param array|null $q $_GET 또는 Referer 파싱 결과.
 * @return string|null 허용 폭 또는 해당 없음.
 */
function goyo_resolve_canvas_width_from_query_vars($q) {
    if (!is_array($q)) {
        return null;
    }
    if (!empty($q['post']) && !empty($q['action']) && 'edit' === $q['action']) {
        $pid = (int) $q['post'];
        if ($pid > 0) {
            $post = get_post($pid);
            if ($post && 'page' === $post->post_type) {
                return goyo_get_editor_canvas_page_width($pid);
            }
        }
    }
    if (!empty($q['postId']) && is_numeric($q['postId'])) {
        $pid = (int) $q['postId'];
        $post = get_post($pid);
        if ($post && 'page' === $post->post_type) {
            return goyo_get_editor_canvas_page_width($pid);
        }
    }
    if (!empty($q['p']) && is_string($q['p'])) {
        $p = $q['p'];
        if (preg_match('#//front-page\\b#', $p)) {
            return '100%';
        }
        if (preg_match('#^/page/(\\d+)#', $p, $m)) {
            return goyo_get_editor_canvas_page_width((int) $m[1]);
        }
    }
    return null;
}

/**
 * 블록 에디터 설정으로 주입할 canvas 폭 후보를 결정한다.
 *
 * @param WP_Block_Editor_Context $context 편집 컨텍스트.
 * @return string|null
 */
function goyo_block_editor_resolve_canvas_width_for_context($context) {
    if (!empty($context->post) && 'page' === $context->post->post_type) {
        $pid = (int) $context->post->ID;
        if ($pid > 0) {
            return goyo_get_editor_canvas_page_width($pid);
        }
    }
    return goyo_resolve_canvas_width_from_query_vars($_GET);
}

/**
 * 에디터 iframe 에 로드되는 글로벌 스타일보다 나중에 적용되는 스타일을 붙여 폭을 고정한다.
 *
 * @param array                     $settings 블록 에디터 설정.
 * @param WP_Block_Editor_Context $context  편집 컨텍스트.
 * @return array
 */
function goyo_block_editor_settings_canvas_width($settings, $context) {
    $width = goyo_block_editor_resolve_canvas_width_for_context($context);
    if (null === $width) {
        return $settings;
    }
    $allowed = array('100%', '720px', '1000px', '1200px', '1380px');
    if (!in_array($width, $allowed, true)) {
        return $settings;
    }
    $w   = esc_attr($width);
    $css = '.editor-styles-wrapper{--wp--style--global--content-size:' . $w . '!important;--wp--style--global--wide-size:' . $w . '!important;--goyo-page-width:' . $w . '!important;}' .
        '.editor-styles-wrapper .container.subbox{max-width:' . $w . '!important;box-sizing:border-box;}' .
        '.editor-styles-wrapper .is-layout-constrained>:where(:not(.alignleft):not(.alignright):not(.alignfull)),.editor-styles-wrapper .wp-block-post-content-is-layout-constrained>:where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width:' . $w . '!important;}' .
        '.editor-styles-wrapper .block-editor-block-list__layout.is-root-container>:where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width:' . $w . '!important;}' .
        '.editor-styles-wrapper .is-layout-constrained>.alignwide,.editor-styles-wrapper .wp-block-post-content-is-layout-constrained>.alignwide,.editor-styles-wrapper .block-editor-block-list__layout.is-root-container>.alignwide{max-width:' . $w . '!important;}' .
        '.editor-styles-wrapper .editor-visual-editor__post-title-wrapper>.wp-block-post-title,.editor-styles-wrapper .editor-visual-editor__post-title-wrapper>.editor-post-title,.editor-styles-wrapper .wp-block-post-title,.editor-styles-wrapper .editor-post-title{max-width:' . $w . '!important;margin-left:auto;margin-right:auto;}';

    if (!isset($settings['styles']) || !is_array($settings['styles'])) {
        $settings['styles'] = array();
    }
    $settings['styles'][] = array(
        'css'            => $css,
        '__unstableType' => 'goyo-canvas-width',
        'isGlobalStyles' => false,
    );
    return $settings;
}
add_filter('block_editor_settings_all', 'goyo_block_editor_settings_canvas_width', 99999, 2);

/**
 * 편집 화면에서 .editor-styles-wrapper 의 data-goyo-page-width 속성을 관리하는 JS.
 *
 * 실제 폭 적용 CSS 는 editor-style.css 에 정적으로 들어있고
 * (.editor-styles-wrapper[data-goyo-page-width="<값>"] { ... }),
 * 이 스크립트는 저장값 기반의 초기 세팅과 사이드바 셀렉트 변경에 따른
 * 실시간 갱신만 담당한다.
 *
 * iframe 이 늦게 생성/리로드될 수 있으므로 MutationObserver + 짧은 재시도
 * 루프로 확실히 반영되도록 한다.
 */
function goyo_page_width_live_update_script() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->post_type !== 'page') {
        return;
    }

    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    $width   = $post_id ? goyo_get_editor_canvas_page_width($post_id) : '1000px';
    $width_js = esc_js($width);
    $is_front = ($post_id > 0 && (int) get_option('page_on_front') === $post_id);
    ?>
    <script>
    (function () {
        var currentWidth = '<?php echo $width_js; ?>';
        var isFrontPage = <?php echo $is_front ? 'true' : 'false'; ?>;

        function reinforceWrapper(el) {
            if (!el || !currentWidth) {
                return;
            }
            el.setAttribute('data-goyo-page-width', currentWidth);
        }

        function applyAttr() {
            document.querySelectorAll('iframe').forEach(function (iframe) {
                try {
                    var doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (!doc) {
                        return;
                    }
                    var wrapper = doc.querySelector('.editor-styles-wrapper');
                    if (wrapper) {
                        reinforceWrapper(wrapper);
                    }
                } catch (err) { /* cross-origin 예외 무시 */ }
            });
            var direct = document.querySelector('.editor-styles-wrapper');
            if (direct) {
                reinforceWrapper(direct);
            }
        }

        applyAttr();
        var retry = setInterval(applyAttr, 400);
        setTimeout(function () { clearInterval(retry); }, 20000);

        if (typeof MutationObserver !== 'undefined') {
            new MutationObserver(applyAttr).observe(document.body, { childList: true, subtree: true });
        }

        document.addEventListener('change', function (e) {
            if (e.target && e.target.id === 'page_content_width') {
                if (!isFrontPage) {
                    currentWidth = e.target.value;
                }
                applyAttr();
            }
        });
    })();
    </script>
    <?php
}
add_action('admin_footer-post.php', 'goyo_page_width_live_update_script');
add_action('admin_footer-post-new.php', 'goyo_page_width_live_update_script');
