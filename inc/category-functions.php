<?php
/**
 * 카테고리 관련 기능
 * 
 * 카테고리 타입, 대표이미지 비율, 관련글 표시, 이전글/다음글 표시,
 * 카테고리 폭 등의 설정을 관리합니다.
 */

/**
 * 카테고리 폭(category_width) 메타 값을 4단계 px 값으로 정규화.
 *
 * 레거시 데이터(narrow/wide) 를 자동 마이그레이션:
 *   - 'narrow' → '1000px'
 *   - 'wide'   → '1380px'
 *
 * 허용 목록(720/1000/1200/1380) 외 값은 기본값 '1380px' 로 폴백.
 *
 * @param string $raw_value DB 에서 읽은 원본 값.
 * @return string 정규화된 px 값.
 */
function goyo_normalize_category_width( $raw_value ) {
    $allowed = function_exists( 'goyo_page_width_options' )
        ? array_keys( goyo_page_width_options() )
        : array( '720px', '1000px', '1200px', '1380px' );

    if ( $raw_value === 'narrow' ) {
        return '1000px';
    }
    if ( $raw_value === 'wide' ) {
        return '1380px';
    }

    return in_array( $raw_value, $allowed, true ) ? $raw_value : '1380px';
}

// 새 카테고리 추가 화면에 필드 추가 (기본값: webzine)
function add_category_post_format_field_create() {
    $options = [
        'webzine' => '웹진형',
        'blog' => '블로그형',
        'photo' => '포토갤러리 3단형',
        'photo4' => '포토갤러리 4단형',
        'portfolio' => '포트폴리오형',
        'noimglist' => '리스트형',
    ];
    ?>
    <div class="form-field">
        <label for="post_format">게시글 형식</label>
        <select name="post_format" id="post_format">
            <?php foreach ($options as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected('webzine', $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
        -- 웹진형 : 매거진에 사용되는 형식 (기본)<br>
        -- 블로그형 : 이미지 비율 유지하며 쌓이는 방식<br>
        -- 포토갤러리형 : 포토갤러리 형식<br>
        -- 포트폴리오형 : 포트폴리오 형식의 큰 이미지<br>
        -- 리스트형 : 제목만 보임</p>
    </div>

    <div class="form-field">
        <label>
            <input type="checkbox" name="show_excerpt" value="1" checked>
            요약글 표시
        </label>
        <p class="description">게시글의 요약글을 목록에 표시합니다.</p>
    </div>

    <div class="form-field">
        <label>
            <input type="checkbox" name="show_date" value="1" checked>
            날짜 표시
        </label>
        <p class="description">게시글의 작성일을 목록에 표시합니다.</p>
    </div>

    <div class="form-field">
        <label>
            <input type="checkbox" name="show_search_icon" value="1">
            검색아이콘 표시
        </label>
        <p class="description">카테고리 페이지에 검색 아이콘을 표시합니다.</p>
    </div>

    <div class="form-field">
        <label>
            <input type="checkbox" name="show_related_posts" value="1" checked>
            관련글 표시
        </label>
        <p class="description">글 하단에 관련글을 표시합니다.</p>
    </div>

    <div class="form-field">
        <label>
            <input type="checkbox" name="show_prev_next_posts" value="1" checked>
            이전글 다음글 표시
        </label>
        <p class="description">글 하단에 이전글/다음글을 표시합니다.</p>
    </div>

    <div class="form-field">
        <label>
            <input type="checkbox" name="show_post_count" value="1" checked>
            게시글수 표시
        </label>
        <p class="description">카테고리 페이지에 게시글 수(전체 N건)를 표시합니다.</p>
    </div>

    <div class="form-field">
        <label>
            <input type="checkbox" name="show_category_title" value="1">
            타이틀 표시
        </label>
        <p class="description">카테고리 페이지 상단에 카테고리명(타이틀)을 표시합니다.</p>
    </div>

    <div class="form-field" id="thumbnail_ratio_field" style="display: none;">
        <label for="thumbnail_ratio">대표이미지 비율</label>
        <select name="thumbnail_ratio" id="thumbnail_ratio">
            <option value="16-9">16:9 (가로형)</option>
            <option value="4-3">4:3 (가로형)</option>
            <option value="3-4">3:4 (세로형)</option>
            <option value="1-1">정사각형</option>
        </select>
        <p class="description">포트폴리오형, 포토갤러리형에서 사용되는 대표이미지 비율을 선택합니다.</p>
    </div>

    <div class="form-field">
        <label for="category_banner_image_id">카테고리 대표이미지</label>
        <input type="hidden" name="category_banner_image_id" id="category_banner_image_id" value="">
        <div id="category_banner_image_preview" style="margin-top:8px;"></div>
        <p>
            <button type="button" class="button" id="category_banner_image_upload">이미지 업로드</button>
            <button type="button" class="button" id="category_banner_image_remove" style="display:none;">이미지 제거</button>
        </p>
        <p class="description">카테고리 subBanner 배경으로 사용할 이미지를 설정합니다.</p>
    </div>

    <div class="form-field">
        <label for="category_width">카테고리 폭</label>
        <select name="category_width" id="category_width">
            <?php foreach ( goyo_page_width_options() as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( '1380px', $value ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">카테고리 목록(아카이브) 페이지의 본문/블록 레이아웃 폭에 적용됩니다.</p>
    </div>

    <div class="form-field">
        <label for="category_content_width">카테고리 내용 폭</label>
        <select name="category_content_width" id="category_content_width">
            <option value="" selected>기본 (카테고리 폭과 동일)</option>
            <?php foreach ( goyo_page_width_options() as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>">
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">개별 글(single) 페이지의 본문/블록 레이아웃 폭에만 적용됩니다. 예: 목록은 1380, 내용은 720 처럼 분리.</p>
    </div>

    <div class="form-field">
        <label for="image_border_radius">이미지 라운드 처리</label>
        <input type="number" name="image_border_radius" id="image_border_radius" value="0" min="0" step="1" style="width: 100px;">
        <span style="margin-left: 5px;">px</span>
        <p class="description">카테고리 목록의 대표이미지에 적용될 border-radius 값을 입력합니다. 기본값은 0px입니다.</p>
    </div>
    <script>
    (function() {
        var postFormatSelect = document.getElementById('post_format');
        var ratioField = document.getElementById('thumbnail_ratio_field');
        var ratioSelect = document.getElementById('thumbnail_ratio');

        function toggleRatioField() {
            var selectedFormat = postFormatSelect.value;
            if (selectedFormat === 'portfolio' || selectedFormat === 'photo' || selectedFormat === 'photo4') {
                ratioField.style.display = 'block';
                if (selectedFormat === 'portfolio' && !ratioSelect.value) {
                    ratioSelect.value = '16-9';
                } else if ((selectedFormat === 'photo' || selectedFormat === 'photo4') && !ratioSelect.value) {
                    ratioSelect.value = '1-1';
                }
            } else {
                ratioField.style.display = 'none';
            }
        }

        function initCategoryBannerUploader(config) {
            var imageInput = document.getElementById(config.imageInputId);
            var uploadButton = document.getElementById(config.uploadButtonId);
            var removeButton = document.getElementById(config.removeButtonId);
            var previewWrap = document.getElementById(config.previewWrapId);
            var mediaFrame;

            if (!imageInput || !uploadButton || !removeButton || !previewWrap) {
                return;
            }

            function renderPreview(url) {
                if (url) {
                    previewWrap.innerHTML = '<img src="' + url + '" alt="" style="max-width:220px;height:auto;display:block;border:1px solid #dcdcde;border-radius:4px;">';
                    removeButton.style.display = 'inline-block';
                } else {
                    previewWrap.innerHTML = '';
                    removeButton.style.display = 'none';
                }
            }

            uploadButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (!window.wp || !wp.media) {
                    return;
                }

                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }

                mediaFrame = wp.media({
                    title: '카테고리 대표이미지 선택',
                    button: { text: '이미지 사용' },
                    library: { type: 'image' },
                    multiple: false
                });

                mediaFrame.on('select', function() {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                    imageInput.value = attachment.id || '';
                    renderPreview(attachment.url || '');
                });

                mediaFrame.open();
            });

            removeButton.addEventListener('click', function(e) {
                e.preventDefault();
                imageInput.value = '';
                renderPreview('');
            });

            renderPreview(config.initialImageUrl || '');
        }

        if (postFormatSelect) {
            postFormatSelect.addEventListener('change', toggleRatioField);
            toggleRatioField();
        }

        initCategoryBannerUploader({
            imageInputId: 'category_banner_image_id',
            uploadButtonId: 'category_banner_image_upload',
            removeButtonId: 'category_banner_image_remove',
            previewWrapId: 'category_banner_image_preview',
            initialImageUrl: ''
        });
    })();
    </script>
    <?php
    wp_nonce_field( 'goyo_category_meta_save', 'goyo_category_meta_nonce' );
}
add_action('category_add_form_fields', 'add_category_post_format_field_create');

// 카테고리 수정 폼에도 동일한 필드 추가
function add_category_post_format_field_edit($term) {
    $post_format = get_term_meta($term->term_id, 'post_format', true);
    $show_excerpt = get_term_meta($term->term_id, 'show_excerpt', true);
    $show_date = get_term_meta($term->term_id, 'show_date', true);
    $thumbnail_ratio = get_term_meta($term->term_id, 'thumbnail_ratio', true);
    $category_banner_image_id = (int) get_term_meta( $term->term_id, 'category_banner_image_id', true );
    $category_banner_image_url = $category_banner_image_id ? wp_get_attachment_image_url( $category_banner_image_id, 'medium' ) : '';
    
    // 기본값 설정
    if (!$thumbnail_ratio) {
        if ($post_format === 'portfolio') {
            $thumbnail_ratio = '16-9';
        } elseif ($post_format === 'photo' || $post_format === 'photo4') {
            $thumbnail_ratio = '1-1';
        }
    }
    
    $options = [
        'webzine' => '웹진형',
        'blog' => '블로그형',
        'photo' => '포토갤러리 3단형',
        'photo4' => '포토갤러리 4단형',
        'portfolio' => '포트폴리오형',
        'noimglist' => '리스트형',
    ];
    ?>
    <tr class="form-field">
        <th scope="row"><label for="post_format">게시글 형식</label></th>
        <td>
            <select name="post_format" id="post_format_edit">
                <?php foreach ($options as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($post_format, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">
            -- 웹진형 : 매거진에 사용되는 형식 (기본)<br>
            -- 블로그형 : 이미지 비율 유지하며 쌓이는 방식<br>
            -- 포토갤러리형 : 포토갤러리 형식<br>
            -- 포트폴리오형 : 포트폴리오 형식의 큰 이미지<br>
            -- 리스트형 : 제목만 보임</p>
        </td>
    </tr>

    <tr class="form-field" id="thumbnail_ratio_field_edit" style="display: <?php echo ($post_format === 'portfolio' || $post_format === 'photo' || $post_format === 'photo4') ? 'table-row' : 'none'; ?>;">
        <th scope="row"><label for="thumbnail_ratio_edit">대표이미지 비율</label></th>
        <td>
            <select name="thumbnail_ratio" id="thumbnail_ratio_edit">
                <option value="16-9" <?php selected($thumbnail_ratio, '16-9'); ?>>16:9 (가로형)</option>
                <option value="4-3" <?php selected($thumbnail_ratio, '4-3'); ?>>4:3 (가로형)</option>
                <option value="3-4" <?php selected($thumbnail_ratio, '3-4'); ?>>3:4 (세로형)</option>
                <option value="1-1" <?php selected($thumbnail_ratio, '1-1'); ?>>정사각형</option>
            </select>
            <p class="description">포트폴리오형, 포토갤러리형에서 사용되는 대표이미지 비율을 선택합니다.</p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row"><label for="category_banner_image_id_edit">카테고리 대표이미지</label></th>
        <td>
            <input type="hidden" name="category_banner_image_id" id="category_banner_image_id_edit" value="<?php echo esc_attr( (string) $category_banner_image_id ); ?>">
            <div id="category_banner_image_preview_edit" style="margin-bottom:8px;">
                <?php if ( $category_banner_image_url ) : ?>
                    <img src="<?php echo esc_url( $category_banner_image_url ); ?>" alt="" style="max-width:220px;height:auto;display:block;border:1px solid #dcdcde;border-radius:4px;">
                <?php endif; ?>
            </div>
            <button type="button" class="button" id="category_banner_image_upload_edit">이미지 업로드</button>
            <button type="button" class="button" id="category_banner_image_remove_edit" style="<?php echo $category_banner_image_url ? '' : 'display:none;'; ?>">이미지 제거</button>
            <p class="description">카테고리 subBanner 배경으로 사용할 이미지를 설정합니다.</p>
        </td>
    </tr>

    <?php
    // 카테고리 폭: 4단계(720/1000/1200/1380). 레거시 narrow/wide 값은 자동으로 px 로 매핑해 표시.
    $category_width_raw       = get_term_meta( $term->term_id, 'category_width', true );
    $category_width_normalized = goyo_normalize_category_width( $category_width_raw );
    ?>
    <tr class="form-field">
        <th scope="row"><label for="category_width_edit">카테고리 폭</label></th>
        <td>
            <select name="category_width" id="category_width_edit">
                <?php foreach ( goyo_page_width_options() as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $category_width_normalized, $value ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">카테고리 목록(아카이브) 페이지의 본문/블록 레이아웃 폭에 적용됩니다.</p>
        </td>
    </tr>

    <?php
    // 카테고리 내용 폭: single 글 페이지 전용. 빈값 = "카테고리 폭과 동일(기본)".
    $category_content_width_raw = get_term_meta( $term->term_id, 'category_content_width', true );
    ?>
    <tr class="form-field">
        <th scope="row"><label for="category_content_width_edit">카테고리 내용 폭</label></th>
        <td>
            <select name="category_content_width" id="category_content_width_edit">
                <option value="" <?php selected( $category_content_width_raw, '' ); ?>>기본 (카테고리 폭과 동일)</option>
                <?php foreach ( goyo_page_width_options() as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $category_content_width_raw, $value ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">개별 글(single) 페이지의 본문/블록 레이아웃 폭에만 적용됩니다. 예: 목록은 1380, 내용은 720 처럼 분리.</p>
        </td>
    </tr>

    <?php
    $image_border_radius = get_term_meta($term->term_id, 'image_border_radius', true);
    // 기본값 설정
    if ($image_border_radius === '') {
        $image_border_radius = '0';
    }
    ?>
    <tr class="form-field">
        <th scope="row"><label for="image_border_radius_edit">이미지 라운드 처리</label></th>
        <td>
            <input type="number" name="image_border_radius" id="image_border_radius_edit" value="<?php echo esc_attr($image_border_radius); ?>" min="0" step="1" style="width: 100px;">
            <span style="margin-left: 5px;">px</span>
            <p class="description">카테고리 목록의 대표이미지에 적용될 border-radius 값을 입력합니다. 기본값은 0px입니다.</p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row"><label>요약글 표시</label></th>
        <td>
            <label>
                <input type="checkbox" name="show_excerpt" value="1" <?php checked($show_excerpt, '1'); ?>>
                게시글의 요약글을 목록에 표시
            </label>
            
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row"><label>날짜 표시</label></th>
        <td>
            <label>
                <input type="checkbox" name="show_date" value="1" <?php checked($show_date, '1'); ?>>
                게시글의 작성일을 목록에 표시
            </label>
           
        </td>
    </tr>

    <?php
    $show_search_icon = get_term_meta($term->term_id, 'show_search_icon', true);
    // 기본값 설정 (값이 없으면 숨김)
    if ($show_search_icon === '') {
        $show_search_icon = '0';
    }
    ?>
    <tr class="form-field">
        <th scope="row"><label>검색아이콘 표시</label></th>
        <td>
            <label>
                <input type="checkbox" name="show_search_icon" value="1" <?php checked($show_search_icon, '1'); ?>>
                카테고리 페이지에 검색 아이콘을 표시
            </label>
        </td>
    </tr>

    <?php
    $show_related_posts = get_term_meta($term->term_id, 'show_related_posts', true);
    // 기본값 설정 (값이 없으면 표시)
    if ($show_related_posts === '') {
        $show_related_posts = '1';
    }
    ?>
    <tr class="form-field">
        <th scope="row"><label>관련글 표시</label></th>
        <td>
            <label>
                <input type="checkbox" name="show_related_posts" value="1" <?php checked($show_related_posts, '1'); ?>>
                싱글 페이지 하단에 관련글을 표시
            </label>
        </td>
    </tr>

    <?php
    $show_prev_next_posts = get_term_meta($term->term_id, 'show_prev_next_posts', true);
    // 기본값 설정 (값이 없으면 표시)
    if ($show_prev_next_posts === '') {
        $show_prev_next_posts = '1';
    }
    ?>
    <tr class="form-field">
        <th scope="row"><label>이전글 다음글 표시</label></th>
        <td>
            <label>
                <input type="checkbox" name="show_prev_next_posts" value="1" <?php checked($show_prev_next_posts, '1'); ?>>
                싱글 페이지 하단에 이전글/다음글을 표시
            </label>
        </td>
    </tr>

    <?php
    $show_post_count = get_term_meta($term->term_id, 'show_post_count', true);
    if ($show_post_count === '') {
        $show_post_count = '1';
    }
    ?>
    <tr class="form-field">
        <th scope="row"><label>게시글수 표시</label></th>
        <td>
            <label>
                <input type="checkbox" name="show_post_count" value="1" <?php checked($show_post_count, '1'); ?>>
                카테고리 페이지에 게시글 수(전체 N건)를 표시
            </label>
        </td>
    </tr>

    <?php
    $show_category_title = get_term_meta($term->term_id, 'show_category_title', true);
    if ($show_category_title === '') {
        $show_category_title = '0';
    }
    ?>
    <tr class="form-field">
        <th scope="row"><label>타이틀 표시</label></th>
        <td>
            <label>
                <input type="checkbox" name="show_category_title" value="1" <?php checked($show_category_title, '1'); ?>>
                카테고리 페이지 상단에 카테고리명(타이틀)을 표시
            </label>
        </td>
    </tr>
    <script>
    (function() {
        var postFormatSelect = document.getElementById('post_format_edit');
        var ratioField = document.getElementById('thumbnail_ratio_field_edit');
        var ratioSelect = document.getElementById('thumbnail_ratio_edit');

        function toggleRatioField() {
            var selectedFormat = postFormatSelect.value;
            if (selectedFormat === 'portfolio' || selectedFormat === 'photo' || selectedFormat === 'photo4') {
                ratioField.style.display = 'table-row';
                if (!ratioSelect.value || ratioSelect.value === '') {
                    if (selectedFormat === 'portfolio') {
                        ratioSelect.value = '16-9';
                    } else if (selectedFormat === 'photo' || selectedFormat === 'photo4') {
                        ratioSelect.value = '1-1';
                    }
                }
            } else {
                ratioField.style.display = 'none';
            }
        }

        function initCategoryBannerUploader(config) {
            var imageInput = document.getElementById(config.imageInputId);
            var uploadButton = document.getElementById(config.uploadButtonId);
            var removeButton = document.getElementById(config.removeButtonId);
            var previewWrap = document.getElementById(config.previewWrapId);
            var mediaFrame;

            if (!imageInput || !uploadButton || !removeButton || !previewWrap) {
                return;
            }

            function renderPreview(url) {
                if (url) {
                    previewWrap.innerHTML = '<img src="' + url + '" alt="" style="max-width:220px;height:auto;display:block;border:1px solid #dcdcde;border-radius:4px;">';
                    removeButton.style.display = 'inline-block';
                } else {
                    previewWrap.innerHTML = '';
                    removeButton.style.display = 'none';
                }
            }

            uploadButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (!window.wp || !wp.media) {
                    return;
                }

                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }

                mediaFrame = wp.media({
                    title: '카테고리 대표이미지 선택',
                    button: { text: '이미지 사용' },
                    library: { type: 'image' },
                    multiple: false
                });

                mediaFrame.on('select', function() {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                    imageInput.value = attachment.id || '';
                    renderPreview(attachment.url || '');
                });

                mediaFrame.open();
            });

            removeButton.addEventListener('click', function(e) {
                e.preventDefault();
                imageInput.value = '';
                renderPreview('');
            });

            renderPreview(config.initialImageUrl || '');
        }

        if (postFormatSelect) {
            postFormatSelect.addEventListener('change', toggleRatioField);
        }

        initCategoryBannerUploader({
            imageInputId: 'category_banner_image_id_edit',
            uploadButtonId: 'category_banner_image_upload_edit',
            removeButtonId: 'category_banner_image_remove_edit',
            previewWrapId: 'category_banner_image_preview_edit',
            initialImageUrl: '<?php echo esc_js( $category_banner_image_url ? $category_banner_image_url : '' ); ?>'
        });
    })();
    </script>
    <?php
    wp_nonce_field( 'goyo_category_meta_save', 'goyo_category_meta_nonce' );
}
add_action('category_edit_form_fields', 'add_category_post_format_field_edit');

// 카테고리 저장 시 메타데이터 저장
function save_category_post_format_meta($term_id) {
    // CSRF 방어: nonce 검증
    if ( ! isset( $_POST['goyo_category_meta_nonce'] ) ||
         ! wp_verify_nonce( $_POST['goyo_category_meta_nonce'], 'goyo_category_meta_save' ) ) {
        return;
    }
    // 관리자 권한 확인
    if ( ! current_user_can( 'manage_categories' ) ) {
        return;
    }

    if (isset($_POST['post_format'])) {
        $post_format = sanitize_text_field($_POST['post_format']);
        update_term_meta($term_id, 'post_format', $post_format);

        // 이미지 비율 저장 (포트폴리오형, 포토갤러리형일 때만)
        if (isset($_POST['thumbnail_ratio']) && ($post_format === 'portfolio' || $post_format === 'photo' || $post_format === 'photo4')) {
            update_term_meta($term_id, 'thumbnail_ratio', sanitize_text_field($_POST['thumbnail_ratio']));
        } else {
            // 기본값 설정
            if ($post_format === 'portfolio') {
                update_term_meta($term_id, 'thumbnail_ratio', '16-9');
            } elseif ($post_format === 'photo' || $post_format === 'photo4') {
                update_term_meta($term_id, 'thumbnail_ratio', '1-1');
            }
        }
    }
    if ( isset( $_POST['category_banner_image_id'] ) ) {
        $category_banner_image_id = absint( $_POST['category_banner_image_id'] );
        update_term_meta( $term_id, 'category_banner_image_id', $category_banner_image_id );
    }

    // 카테고리 폭: 모든 포맷 공통, 4단계(720/1000/1200/1380). 허용값 외 → 1380px 폴백.
    if ( isset( $_POST['category_width'] ) ) {
        $normalized_width = goyo_normalize_category_width( sanitize_text_field( $_POST['category_width'] ) );
        update_term_meta( $term_id, 'category_width', $normalized_width );
    }

    // 카테고리 내용 폭(single 전용). 빈값 = '기본(카테고리 폭과 동일)' → 빈값 저장으로 보존.
    // 빈값이 아닐 때만 goyo_normalize_category_width 로 정규화해 저장.
    if ( isset( $_POST['category_content_width'] ) ) {
        $content_width_raw = sanitize_text_field( $_POST['category_content_width'] );
        if ( $content_width_raw === '' ) {
            update_term_meta( $term_id, 'category_content_width', '' );
        } else {
            $content_width_normalized = goyo_normalize_category_width( $content_width_raw );
            update_term_meta( $term_id, 'category_content_width', $content_width_normalized );
        }
    }
    if (isset($_POST['show_excerpt'])) {
        update_term_meta($term_id, 'show_excerpt', '1');
    } else {
        update_term_meta($term_id, 'show_excerpt', '0');
    }
    if (isset($_POST['show_date'])) {
        update_term_meta($term_id, 'show_date', '1');
    } else {
        update_term_meta($term_id, 'show_date', '0');
    }
    if (isset($_POST['show_related_posts'])) {
        update_term_meta($term_id, 'show_related_posts', '1');
    } else {
        update_term_meta($term_id, 'show_related_posts', '0');
    }
    if (isset($_POST['show_prev_next_posts'])) {
        update_term_meta($term_id, 'show_prev_next_posts', '1');
    } else {
        update_term_meta($term_id, 'show_prev_next_posts', '0');
    }
    if (isset($_POST['show_post_count'])) {
        update_term_meta($term_id, 'show_post_count', '1');
    } else {
        update_term_meta($term_id, 'show_post_count', '0');
    }
    if (isset($_POST['show_category_title'])) {
        update_term_meta($term_id, 'show_category_title', '1');
    } else {
        update_term_meta($term_id, 'show_category_title', '0');
    }
    if (isset($_POST['show_search_icon'])) {
        update_term_meta($term_id, 'show_search_icon', '1');
    } else {
        update_term_meta($term_id, 'show_search_icon', '0');
    }
    if (isset($_POST['image_border_radius'])) {
        $image_border_radius = sanitize_text_field($_POST['image_border_radius']);
        // 숫자만 허용하고 음수는 0으로 처리
        $image_border_radius = max(0, intval($image_border_radius));
        update_term_meta($term_id, 'image_border_radius', $image_border_radius);
    } else {
        update_term_meta($term_id, 'image_border_radius', '0');
    }
}
add_action('created_category', 'save_category_post_format_meta');
add_action('edited_category', 'save_category_post_format_meta');

/**
 * 카테고리 편집/추가 화면에서 미디어 업로더를 사용하기 위해 wp.media를 로드한다.
 *
 * @param string $hook_suffix 현재 관리자 페이지 훅.
 * @return void
 */
function goyo_enqueue_category_media_uploader( $hook_suffix ) {
    if ( 'term.php' !== $hook_suffix && 'edit-tags.php' !== $hook_suffix ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! $screen || 'category' !== $screen->taxonomy ) {
        return;
    }

    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'goyo_enqueue_category_media_uploader' );

// 아카이브 타이틀 수정
function modify_archive_title( $title ) {
    if ( is_category() ) {
        return single_cat_title( '', false );
    } elseif ( is_tag() ) {
        return single_tag_title( '', false );
    } elseif ( is_author() ) {
        return '<span class="vcard">' . get_the_author() . '</span>';
    }
    return $title;
}
add_filter( 'get_the_archive_title', 'modify_archive_title' );

/**
 * 카테고리 폭/타입 관련 body 클래스를 주입한다.
 *
 * 주입되는 클래스(카테고리 아카이브/단일글 공통):
 *   - category-{term_id}
 *   - category-type-{post_format}            (단일글에만)
 *   - category-width-{px숫자}                 (예: category-width-1000) — 신규 표준
 *   - category-width-narrow / category-width-wide — 레거시 board.css 규칙 하위호환
 *     (1000px 은 narrow, 1380px 은 wide 에 매핑)
 *
 * 과거에는 포트폴리오/포토 포맷에서만 width 클래스를 주입했으나,
 * 이제 "카테고리 폭" 은 모든 포맷 공통 설정이므로 항상 주입한다.
 */
function add_category_to_body_class( $classes ) {
    $target_category = null;

    if ( is_single() ) {
        $post_categories = get_the_category();
        if ( ! empty( $post_categories ) ) {
            $target_category = $post_categories[0];
            $classes[]       = 'category-' . absint( $target_category->term_id );

            $post_format = get_term_meta( $target_category->term_id, 'post_format', true );
            if ( $post_format ) {
                $classes[] = 'category-type-' . sanitize_html_class( $post_format );
            }
        }
    } elseif ( is_category() ) {
        $target_category = get_queried_object();
        if ( ! ( $target_category instanceof WP_Term ) ) {
            return $classes;
        }
        $classes[] = 'category-' . absint( $target_category->term_id );

        // 카테고리 타이틀 숨김 플래그: show_category_title 메타가 명시적으로 '0' 일 때.
        $show_category_title = get_term_meta( $target_category->term_id, 'show_category_title', true );
        if ( $show_category_title === '0' ) {
            $classes[] = 'hide-archive-title';
        }
    }

    // 카테고리 폭 클래스 주입 (모든 포맷 공통).
    if ( $target_category instanceof WP_Term ) {
        $width_raw        = get_term_meta( $target_category->term_id, 'category_width', true );
        $width_normalized = goyo_normalize_category_width( $width_raw );
        $width_number     = (int) $width_normalized; // "1000px" → 1000

        if ( $width_number > 0 ) {
            $classes[] = 'category-width-' . $width_number;
        }

        // 레거시 board.css 규칙(.category-width-narrow / .category-width-wide) 하위호환.
        if ( $width_normalized === '1000px' ) {
            $classes[] = 'category-width-narrow';
        } elseif ( $width_normalized === '1380px' ) {
            $classes[] = 'category-width-wide';
        }
    }

    return $classes;
}
add_filter( 'body_class', 'add_category_to_body_class' );

/**
 * 프론트엔드: 카테고리 아카이브에 본문 가로폭 + 타이틀 숨김 인라인 CSS 출력.
 *
 * - category_width 메타(레거시 narrow/wide 값 자동 마이그레이션)로
 *   .container.subbox 의 max-width 및 is-layout-constrained 의 CSS 변수 지정.
 * - show_category_title 메타가 '0' 이면 아카이브 제목 숨김.
 *
 * 페이지 측 동작(goyo_output_page_inline_styles) 과 셀렉터 규약을 맞춰 일관성 유지.
 */
function goyo_output_category_inline_styles() {
    if ( ! is_category() ) {
        return;
    }

    $category = get_queried_object();
    if ( ! ( $category instanceof WP_Term ) ) {
        return;
    }

    // 저장값 없음/비허용값은 goyo_normalize_category_width 가 기본값(1380px) 로 폴백.
    $width = goyo_normalize_category_width(
        get_term_meta( $category->term_id, 'category_width', true )
    );
    $category_banner_image_id  = (int) get_term_meta( $category->term_id, 'category_banner_image_id', true );
    $category_banner_image_url = $category_banner_image_id ? wp_get_attachment_image_url( $category_banner_image_id, 'full' ) : '';

    // (1) 레거시 래퍼 .container.subbox 방식 지원.
    $css  = '.category .container.subbox{max-width:' . esc_attr( $width ) . ';}';

    // (2) 블록 테마: 해당 카테고리의 <main> 스코프 안 is-layout-constrained 블록만 반영.
    //     header/footer template-part 는 제외되어 테마 기본 폭을 유지.
    $css .= 'body.category-' . (int) $category->term_id . ' main{'
          . '--wp--style--global--content-size:' . esc_attr( $width ) . ';'
          . '--wp--style--global--wide-size:'    . esc_attr( $width ) . ';'
          . '}';

    // 타이틀 숨김 여부: 메타가 '0' 인 경우에만 숨김 처리.
    $show_category_title = get_term_meta( $category->term_id, 'show_category_title', true );
    if ( $show_category_title === '0' ) {
        $css .= 'body.hide-archive-title .wp-block-query-title,'
              . 'body.hide-archive-title .wp-block-post-archive-title,'
              . 'body.hide-archive-title .archive-title{display:none;}';
    }

    if ( $category_banner_image_url ) {
        $css .= 'body.category-' . (int) $category->term_id . ' .subBanner{background-image:url("' . esc_url_raw( $category_banner_image_url ) . '");}';
    }

    echo '<style>' . $css . '</style>' . "\n";
}
add_action( 'wp_head', 'goyo_output_category_inline_styles' );

/**
 * 프론트엔드: single 글 페이지에 카테고리별 본문·블록 constrained 폭을 인라인 CSS 로 맞춘다.
 *
 * 동작 규칙:
 *   - category_content_width 가 있으면: .container.subbox / single-layout / post-content 및 main 의
 *     is-layout-constrained 변수를 해당 값으로 덮어쓴다 (board.css 보다 높은 특이도).
 *   - 없으면(기본 «카테고리 폭과 동일»): 포트폴리오·포토형만 category_width 를 main 의
 *     --wp--style--global--content-size 등에 반영한다. 서브배너 숏코드가 theme.json 720px 에만
 *     묶이고 .container.single-layout 은 board.css 로 1000px 인 불일치를 방지한다.
 *   - 웹진·블로그 등 그 외 포맷은 theme.json contentSize 를 그대로 둔다.
 *
 * 셀렉터는 .single 과 body.category-{id} 를 조합한다.
 */
function goyo_output_single_content_width_inline_styles() {
    if ( ! is_single() ) {
        return;
    }

    $categories = get_the_category();
    if ( empty( $categories ) ) {
        return;
    }
    $category = $categories[0];
    $cat_id   = (int) $category->term_id;
    $body_sel = 'body.single.category-' . $cat_id;

    $content_width_raw = get_term_meta( $cat_id, 'category_content_width', true );

    if ( $content_width_raw !== '' ) {
        $width = goyo_normalize_category_width( $content_width_raw );

        // (1) 레거시 래퍼 폭 제어 (board.css 보다 특이도 높임).
        $css  = $body_sel . ' .container.subbox,'
              . $body_sel . ' .container.single-layout,'
              . $body_sel . ' .post-content.container'
              . '{max-width:' . esc_attr( $width ) . ';}';

        // (2) 블록 테마 is-layout-constrained (single.html 의 숏코드·블록 자식 폭).
        $css .= $body_sel . ' main{'
              . '--wp--style--global--content-size:' . esc_attr( $width ) . ';'
              . '--wp--style--global--wide-size:'    . esc_attr( $width ) . ';'
              . '}';

        echo '<style>' . $css . '</style>' . "\n";
        return;
    }

    // 내용 폭 메타가 비어 있을 때: 포토·포트폴리오 싱글만 카테고리 폭으로 main 변수 동기화.
    $post_format = get_term_meta( $cat_id, 'post_format', true );
    if ( ! in_array( $post_format, array( 'portfolio', 'photo', 'photo4' ), true ) ) {
        return;
    }

    $width = goyo_normalize_category_width( get_term_meta( $cat_id, 'category_width', true ) );

    $css = $body_sel . ' main{'
         . '--wp--style--global--content-size:' . esc_attr( $width ) . ';'
         . '--wp--style--global--wide-size:'    . esc_attr( $width ) . ';'
         . '}';

    echo '<style>' . $css . '</style>' . "\n";
}
add_action( 'wp_head', 'goyo_output_single_content_width_inline_styles' );

// 카테고리 내 검색을 위한 필터 추가
function goyo_category_search_filter($query) {
    if (!is_admin() && $query->is_main_query()) {
        // 검색 시 비밀글 제외 및 케이보드 게시글 제외
        if (is_search() || (is_category() && isset($_GET['s']) && !empty($_GET['s']))) {
            $query->set('post_status', 'publish'); // 비밀글 제외
            
            // 케이보드 게시글 제외 (post_type을 'post'로만 제한)
            $query->set('post_type', 'post');
        }
        
        // 카테고리 페이지에서 검색할 때
        if (is_category() && isset($_GET['s']) && !empty($_GET['s'])) {
            $current_category = get_queried_object();
            if ($current_category) {
                $query->set('cat', $current_category->term_id);
                $query->set('s', sanitize_text_field($_GET['s'])); // 검색어 명시적 설정
                
                // 검색 타입에 따른 검색 범위 설정
                $search_type = isset($_GET['search_type']) ? sanitize_text_field($_GET['search_type']) : 'title_content';
                if ($search_type === 'title') {
                    // 제목만 검색하도록 설정
                    add_filter('posts_where', 'goyo_search_title_only', 10, 2);
                }
            }
        }
        // 검색 결과 페이지에서 카테고리 필터링
        elseif (is_search() && isset($_GET['cat']) && !empty($_GET['cat'])) {
            $query->set('cat', intval($_GET['cat']));
            
            // 검색 타입에 따른 검색 범위 설정
            $search_type = isset($_GET['search_type']) ? sanitize_text_field($_GET['search_type']) : 'title_content';
            if ($search_type === 'title') {
                // 제목만 검색하도록 설정
                add_filter('posts_where', 'goyo_search_title_only', 10, 2);
            }
        }
    }
}

// 제목만 검색하는 필터 함수
function goyo_search_title_only($where, $query) {
    global $wpdb;
    
    if ($query->is_search() && !is_admin()) {
        $search_term = $query->get('s');
        if (!empty($search_term)) {
            // 테이블명에 정규식 특수문자가 포함될 수 있으므로 preg_quote 적용
            $posts_table = preg_quote( $wpdb->posts, '/' );

            // 기존 검색 조건을 제목 검색으로만 제한
            $where = preg_replace(
                "/\(\s*{$posts_table}.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "({$wpdb->posts}.post_title LIKE $1)",
                $where
            );

            // 내용 검색 조건 제거
            $where = preg_replace(
                "/\s*OR\s*\(\s*{$posts_table}.post_content\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "",
                $where
            );

            // excerpt 검색 조건 제거
            $where = preg_replace(
                "/\s*OR\s*\(\s*{$posts_table}.post_excerpt\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "",
                $where
            );
        }
    }
    
    return $where;
}
add_action('pre_get_posts', 'goyo_category_search_filter');

// 검색 결과에서 특정 카테고리 제외 (mainbigimage, mobilemain)
function exclude_categories_from_search($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        // 제외할 카테고리 슬러그
        $exclude_categories = array('mainbigimage', 'mobilemain');
        
        // 카테고리 ID 가져오기
        $exclude_cat_ids = array();
        foreach ($exclude_categories as $slug) {
            $category = get_term_by( 'slug', $slug, 'category' );
            if ($category) {
                $exclude_cat_ids[] = $category->term_id;
            }
        }
        
        // 카테고리 제외 설정
        if (!empty($exclude_cat_ids)) {
            $query->set('category__not_in', $exclude_cat_ids);
        }
    }
}
add_action('pre_get_posts', 'exclude_categories_from_search');

