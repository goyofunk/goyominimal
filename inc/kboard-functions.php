<?php
/**
 * KBoard (케이보드) 관련 커스터마이징 함수 모음
 *
 */

// ============================================================================
// 문의 등록 완료 시 토스트 표시를 위한 리다이렉트 처리 (게시판 ID: 2)
// ============================================================================
add_action('kboard_content_execute_pre_redirect', 'my_kboard_content_execute_pre_redirect', 10, 3);
function my_kboard_content_execute_pre_redirect($next_page_url, $content, $board) {
    if ($board->id != '2') {
        return;
    }

    $redirect_url = add_query_arg('goyo_kboard_toast', 'inquiry_saved', $next_page_url);
    wp_safe_redirect($redirect_url);
    exit;
}

// ============================================================================
// 문의 등록 완료 토스트 출력 (리다이렉트된 페이지 하단)
// ============================================================================
add_action('wp_footer', 'goyoartdark_render_kboard_inquiry_toast', 99);
function goyoartdark_render_kboard_inquiry_toast() {
    if (!isset($_GET['goyo_kboard_toast']) || $_GET['goyo_kboard_toast'] !== 'inquiry_saved') {
        return;
    }
    ?>
    <style>
        .goyo-kboard-toast-wrap { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 99999; pointer-events: none; }
        .goyo-kboard-toast { position: relative; pointer-events: auto; background: #3F51B5; border: 1px solid #576ad8; border-radius: 12px; box-shadow: 0 14px 32px rgba(0, 0, 0, 0.18); min-width: 320px; max-width: 380px; padding:40px 30px; opacity: 0; transform: translateY(-8px); transition: opacity 0.24s ease, transform 0.24s ease; }
        .goyo-kboard-toast.is-visible { opacity: 1; transform: translateY(0); }
        .goyo-kboard-toast-close { position: absolute; top: 10px; right: 10px; width: 28px; height: 28px; border: 0; border-radius: 50%; background: transparent; color:rgb(255, 255, 255); font-size: 30px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .goyo-kboard-toast-close:hover {  color:rgb(255, 255, 255); }
        .goyo-kboard-toast-body { display: flex; flex-direction: column; align-items: center; gap: 16px; margin-bottom: 20px; justify-content: center; text-align: center; }
        .goyo-kboard-toast-icon { font-size: 28px; color: #a8d8ff; width: 50px; height: 50px; border: 2px solid #a8d8ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1; box-sizing: border-box; }
        .goyo-kboard-toast-message { margin: 0; color:rgb(255, 255, 255); font-size: 16px; line-height: 1.5; letter-spacing: -0.01em;}
        .goyo-kboard-toast-actions { display: flex; justify-content: center; align-items: center; }
        .goyo-kboard-toast-confirm {     border: #1ec0dc;background: #1ec0dc; color: #ffffff; border-radius: 4px; padding: 12px 32px; font-size: 15px; min-width: 150px; font-weight: 500; line-height: 1; cursor: pointer; }
        .goyo-kboard-toast-confirm:hover { border-color:#0c9eb8; background:#0c9eb8 }
    </style>
    <div class="goyo-kboard-toast-wrap" aria-live="polite" aria-atomic="true">
        <div class="goyo-kboard-toast" id="goyo-kboard-toast">
            <button type="button" class="goyo-kboard-toast-close" id="goyo-kboard-toast-close" aria-label="알림 닫기">&times;</button>
            <div class="goyo-kboard-toast-body">
                <span class="goyo-kboard-toast-icon" aria-hidden="true"><i class="bi bi-check2"></i></span>
                <p class="goyo-kboard-toast-message">문의글이 잘 등록되었습니다.<br>확인 후 답변드리겠습니다.</p>
            </div>
            <div class="goyo-kboard-toast-actions">
                <button type="button" class="goyo-kboard-toast-confirm" id="goyo-kboard-toast-confirm">확인</button>
            </div>
        </div>
    </div>
    <script>
        (function() {
            const toast = document.getElementById("goyo-kboard-toast");
            const closeButton = document.getElementById("goyo-kboard-toast-close");
            const confirmButton = document.getElementById("goyo-kboard-toast-confirm");
            if (!toast) {
                return;
            }

            let isClosing = false;

            function clearToastQueryString() {
                const url = new URL(window.location.href);
                url.searchParams.delete("goyo_kboard_toast");
                window.history.replaceState({}, "", url.toString());
            }

            function closeToast() {
                if (isClosing) {
                    return;
                }
                isClosing = true;
                toast.classList.remove("is-visible");
                window.setTimeout(function() {
                    toast.remove();
                    clearToastQueryString();
                }, 240);
            }

            window.requestAnimationFrame(function() {
                toast.classList.add("is-visible");
            });

            if (closeButton) {
                closeButton.addEventListener("click", closeToast);
            }

            if (confirmButton) {
                confirmButton.addEventListener("click", closeToast);
            }
        })();
    </script>
    <?php
}

// ============================================================================
// 질문과답변 게시판(ID: 1) 작성자 이름 마스킹 (관리자 및 본인은 전체 공개)
// ============================================================================
add_filter('kboard_user_display', 'kboard_user_display_2025', 10, 5);
function kboard_user_display_2025($user_display, $user_id, $user_name, $plugins, $boardBuilder) {
    if (!$boardBuilder || !isset($boardBuilder->board)) {
        return $user_display;
    }

    $board = $boardBuilder->board;
    $is_target_board = isset($board->id) && $board->id == '1';
    $is_not_admin    = method_exists($board, 'isAdmin') && !$board->isAdmin();
    $is_other_user   = ($user_id != get_current_user_id() || !$user_id);

    if ($is_target_board && $is_not_admin && $is_other_user) {
        $strlen       = mb_strlen($user_name, 'utf-8');
        $user_display = mb_substr($user_name, 0, 1, 'utf-8') . str_repeat('*', max(0, $strlen - 1));
    }

    return $user_display;
}

// ============================================================================
// 알림 이메일 제목: "{작성자}님이 새로운 글을 등록하셨습니다."
// ============================================================================
add_filter('kboard_latest_alerts_subject', 'my_kboard_latest_alerts_subject', 10, 2);
function my_kboard_latest_alerts_subject($title, $content) {
    return $content->member_display . '님이 새로운 글을 등록하셨습니다.';
}

// ============================================================================
// KBoard 숏코드의 게시판 ID를 body 클래스에 추가 (예: kboard-id-1)
// ============================================================================
add_filter('body_class', 'add_kboard_id_body_class');
function add_kboard_id_body_class($classes) {
    if (!is_singular()) {
        return $classes;
    }

    global $post;
    if (!$post || empty($post->post_content)) {
        return $classes;
    }

    $shortcode_pattern = '/\[kboard[^\]]*id=[\'"]?(\d+)[\'"]?[^\]]*\]/i';
    if (!preg_match_all($shortcode_pattern, $post->post_content, $matches)) {
        return $classes;
    }

    $board_ids = array_unique(array_map('intval', $matches[1]));
    foreach ($board_ids as $board_id) {
        if ($board_id > 0) {
            $classes[] = 'kboard-id-' . $board_id;
        }
    }

    return $classes;
}

// ============================================================================
// KBoard mod 파라미터(document/editor)를 body 클래스에 추가 (예: mode-document)
// ============================================================================
add_filter('body_class', 'add_document_mode_class');
function add_document_mode_class($classes) {
    if (isset($_GET['mod']) && in_array($_GET['mod'], array('document', 'editor'), true)) {
        $classes[] = 'mode-' . $_GET['mod'];
    }
    return $classes;
}
