<?php
/**
 * 블록 테마 템플릿(archive.html / single.html 등)에서 PHP 기반 레이아웃을 재사용하기 위한 숏코드 모음.
 *
 * 이 파일에서 제공하는 숏코드:
 *   [goyo_subbanner]      - primary-menu 기반 서브배너(페이지 타이틀 + 서브메뉴)
 *   [goyo_category_loop]  - 카테고리/검색 아카이브 루프(category.php 이식)
 *   [goyo_single_content] - 싱글 포스트 본문 + 이전·다음글 + 관련글 (single.php 이식)
 *
 * 의존: inc/content.php, inc/content-page.php, inc/content-empty.php, inc/content-none.php,
 *       inc/related-post.php, inc/pagination.php, inc/category-functions.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * paginate_links() 가 반환한 HTML 조각을 허용 태그만 남기도록 정제한다. (코어·필터 변조 시 대비)
 *
 * @param string $link 페이지 링크 마크업.
 * @return string
 */
function goyoartdark_kses_paginate_link_fragment( $link ) {
	$allowed = array(
		'a'    => array(
			'href'         => true,
			'class'        => true,
			'aria-label'   => true,
			'aria-current' => true,
			'rel'          => true,
			'title'        => true,
		),
		'span' => array(
			'class'        => true,
			'aria-current' => true,
		),
		'i'    => array(
			'class'       => true,
			'aria-hidden' => true,
		),
	);

	return wp_kses( (string) $link, $allowed );
}

/**
 * core/shortcode 의 wpautop 가 만든 불필요한 p/br 태그를 정리한다.
 *
 * @since goyoartdark 1.0
 *
 * @param string $html 숏코드 렌더링 HTML.
 * @return string 정리된 HTML.
 */
function goyoartdark_cleanup_shortcode_output( $html ) {
	$clean_html = (string) $html;
	$clean_html = trim( $clean_html );
	$clean_html = preg_replace( '/>\s+</', '><', $clean_html );
	$clean_html = preg_replace( '/[\r\n\t]+/', '', $clean_html );
	$clean_html = preg_replace( '/<p>\s*<\/p>/i', '', $clean_html );
	$clean_html = preg_replace( '/<p\b[^>]*>(?:\s|&nbsp;|<!--.*?-->|<br\s*\/?>)*<\/p>/is', '', $clean_html );
	$clean_html = preg_replace( '/<p>\s*<br\s*\/?>\s*<\/p>/i', '', $clean_html );
	$clean_html = preg_replace( '/<p>\s*(<\/?(?:div|section|article|aside|header|footer|main|nav|ul|ol|li|h[1-6]|form|table|thead|tbody|tr|td|th|figure|figcaption|blockquote|pre)[^>]*>)\s*<\/p>/i', '$1', $clean_html );
	$clean_html = preg_replace( '/(<\/?(?:div|section|article|aside|header|footer|main|nav|ul|ol|li|h[1-6]|form|table|thead|tbody|tr|td|th|figure|figcaption|blockquote|pre)[^>]*>)\s*<br\s*\/?>/i', '$1', $clean_html );
	$clean_html = preg_replace( '/<br\s*\/?>\s*(<\/?(?:div|section|article|aside|header|footer|main|nav|ul|ol|li|h[1-6]|form|table|thead|tbody|tr|td|th|figure|figcaption|blockquote|pre)[^>]*>)/i', '$1', $clean_html );
	return $clean_html;
}

/**
 * 빈 문단 태그만 제거한다.
 *
 * @since goyoartdark 1.0
 *
 * @param string $html 원본 HTML.
 * @return string
 */
function goyoartdark_strip_empty_paragraphs_only( $html ) {
	$clean_html = (string) $html;
	return (string) preg_replace( '/<p\b[^>]*>(?:\s|&nbsp;|<!--.*?-->|<br\s*\/?>)*<\/p>/is', '', $clean_html );
}

if ( ! function_exists( 'goyoartdark_filter_main_content_empty_paragraphs' ) ) :
	/**
	 * 프론트 메인 콘텐츠의 빈 문단 태그를 최종 정리한다.
	 *
	 * @param string $content 본문 HTML.
	 * @return string
	 */
	function goyoartdark_filter_main_content_empty_paragraphs( $content ) {
		if ( ! is_front_page() && ! is_home() && ! goyoartdark_is_main_page_blocks_context() ) {
			return $content;
		}
		return goyoartdark_strip_empty_paragraphs_only( $content );
	}
endif;
add_filter( 'the_content', 'goyoartdark_filter_main_content_empty_paragraphs', 999 );

/**
 * primary-menu 를 기반으로 현재 페이지의 부모 메뉴 타이틀과 형제 서브메뉴를 계산해 서브배너 HTML 을 반환한다.
 *
 * 카테고리·싱글 포스트·고정 페이지 등에서 동일 규칙을 쓴다. 페이지는 메뉴 URL 불일치 시 object_id 로 항목을 찾는다.
 *
 * category.php / single.php 에서 중복되던 네비게이션 메뉴 파싱 로직을 한 곳으로 모아 재사용성을 확보한다.
 *
 * @return string 서브배너 HTML.
 */
function goyoartdark_render_subbanner() {
	$current_category = null;
	$current_url      = '';
	$parent_title     = '';
	$has_subbanner_overlay = false;
	$subbanner_background_image_url = '';
	$single_title = '';
	$single_date  = '';

	if ( is_category() ) {
		$current_category = get_queried_object();
		if ( $current_category instanceof WP_Term ) {
			$current_url  = get_category_link( $current_category->term_id );
			$parent_title = $current_category->name;
		}
	} elseif ( is_single() ) {
		$post_categories = get_the_category();
		if ( ! empty( $post_categories ) ) {
			$current_category = $post_categories[0];
			$current_url      = get_category_link( $current_category->term_id );
			$parent_title     = $current_category->name;
		} else {
			$current_url = get_permalink();
		}

		$single_post_id = get_queried_object_id();
		if ( $single_post_id > 0 ) {
			$single_title = get_the_title( $single_post_id );
			$single_date  = get_the_date( 'Y-m-d', $single_post_id );
		}
		if ( $single_post_id > 0 && has_post_thumbnail( $single_post_id ) ) {
			$subbanner_background_image_url = get_the_post_thumbnail_url( $single_post_id, 'full' );
			$has_subbanner_overlay          = ! empty( $subbanner_background_image_url );
		}
	} elseif ( is_page() ) {
		// 일반 페이지: 기본 타이틀은 글 제목, 메뉴·카테고리 매칭 시 부모/형제로 덮어쓴다 ( page.php 와 동일 흐름).
		$current_url  = get_permalink();
		$parent_title = get_the_title();
	} elseif ( is_search() ) {
		$parent_title = __( '검색 결과', 'goyoartdark' );
		$current_url  = home_url( '/' );
	} else {
		$current_url = get_permalink();
	}

	if ( is_category() && $current_category instanceof WP_Term ) {
		$has_subbanner_overlay = (bool) absint( get_term_meta( $current_category->term_id, 'category_banner_image_id', true ) );
	} elseif ( is_page() ) {
		$has_subbanner_overlay = has_post_thumbnail( get_queried_object_id() );
	}

	$normalized_current_url = untrailingslashit( $current_url );

	// 페이지 본문에 카테고리가 붙어 있는 경우(플러그인 등) 서브네비 active·폴백에 사용.
	$page_primary_category_id = 0;
	if ( is_page() ) {
		$page_cats = get_the_category( get_queried_object_id() );
		if ( ! empty( $page_cats ) ) {
			$page_primary_category_id = (int) $page_cats[0]->term_id;
		}
	}

	// primary-menu 위치의 네비게이션을 가져와 인덱싱.
	$menu_items           = array();
	$menu_items_by_id     = array();
	$menu_items_by_parent = array();
	$locations            = get_nav_menu_locations();

	if ( isset( $locations['primary-menu'] ) ) {
		$menu = wp_get_nav_menu_object( $locations['primary-menu'] );
		if ( $menu && isset( $menu->term_id ) ) {
			$nav_items = wp_get_nav_menu_items( $menu->term_id );
			if ( is_array( $nav_items ) ) {
				$menu_items = $nav_items;
				foreach ( $menu_items as $item ) {
					$menu_items_by_id[ $item->ID ] = $item;
					if ( 0 != $item->menu_item_parent ) {
						if ( ! isset( $menu_items_by_parent[ $item->menu_item_parent ] ) ) {
							$menu_items_by_parent[ $item->menu_item_parent ] = array();
						}
						$menu_items_by_parent[ $item->menu_item_parent ][] = $item;
					}
				}
			}
		}
	}

	// 현재 URL 과 일치하는 메뉴 항목 탐색 → 부모 타이틀/형제 서브메뉴 결정.
	$current_menu_item = null;
	$submenu_items     = array();

	$page_id = is_page() ? get_queried_object_id() : 0;
	foreach ( $menu_items as $item ) {
		if ( untrailingslashit( $item->url ) === $normalized_current_url ) {
			$current_menu_item = $item;
			break;
		}
		//  page.php: URL 과 달라도 object 가 page 이면 ID 로 매칭.
		if ( $page_id && 'page' === $item->object && (int) $item->object_id === (int) $page_id ) {
			$current_menu_item = $item;
			break;
		}
	}

	if ( $current_menu_item ) {
		if ( 0 != $current_menu_item->menu_item_parent ) {
			$parent_menu_id = $current_menu_item->menu_item_parent;
			if ( isset( $menu_items_by_id[ $parent_menu_id ] ) ) {
				$parent_title = $menu_items_by_id[ $parent_menu_id ]->title;
				if ( isset( $menu_items_by_parent[ $parent_menu_id ] ) ) {
					$submenu_items = $menu_items_by_parent[ $parent_menu_id ];
				}
			}
		} else {
			$parent_title = $current_menu_item->title;
			if ( isset( $menu_items_by_parent[ $current_menu_item->ID ] ) ) {
				$submenu_items = $menu_items_by_parent[ $current_menu_item->ID ];
			}
		}
	}

	//  page.php: 메뉴에 페이지 항목이 없을 때, 페이지의 첫 카테고리 slug 가 들어간 메뉴 링크로 그룹을 찾는다.
	if ( ! $current_menu_item && is_page() && $page_primary_category_id && ! empty( $menu_items ) ) {
		$category = get_category( $page_primary_category_id );
		if ( $category && ! is_wp_error( $category ) ) {
			$category_slug = 'category/' . $category->slug;
			$matched_item  = null;
			foreach ( $menu_items as $item ) {
				if ( false !== strpos( $item->url, $category_slug ) ) {
					$matched_item = $item;
					break;
				}
			}
			if ( $matched_item ) {
				$parent_mid = (int) $matched_item->menu_item_parent;
				// 형제가 한 부모 아래 있으면 부모 타이틀 + 형제 목록 (WORKS / PHOTO 등).
				if ( $parent_mid && ! empty( $menu_items_by_parent[ $parent_mid ] ) ) {
					$submenu_items = $menu_items_by_parent[ $parent_mid ];
					if ( isset( $menu_items_by_id[ $parent_mid ] ) ) {
						$parent_title = $menu_items_by_id[ $parent_mid ]->title;
					}
				} elseif ( ! empty( $menu_items_by_parent[ $matched_item->ID ] ) ) {
					$submenu_items = $menu_items_by_parent[ $matched_item->ID ];
					$parent_title  = $matched_item->title;
				}
			}
		}
	}

	// 싱글만: 서브네비는 형제 메뉴 대신 이 글의 대표 카테고리 한 항목만 둔다 (카테고리·고정 페이지는 기존 동작 유지).
	if ( is_single() && $current_category instanceof WP_Term ) {
		$submenu_items = array(
			(object) array(
				'url'       => get_category_link( $current_category->term_id ),
				'title'     => $current_category->name,
				'object'    => 'category',
				'object_id' => (int) $current_category->term_id,
			),
		);
	}

	ob_start();
	?>
	<div class="subBanner"<?php echo $subbanner_background_image_url ? ' style="background-image:url(' . esc_url( $subbanner_background_image_url ) . ');"' : ''; ?>>
		<?php if ( $has_subbanner_overlay ) : ?>
			<div class="subBannerOverlay" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="container">
			<h1 class="pageTitle">
				<?php if ( is_single() ) : ?>
					<?php echo esc_html( wp_strip_all_tags( (string) $single_title, true ) ); ?>
					<?php if ( '' !== $single_date ) : ?>
						<span class="pageTitleDate"><?php echo esc_html( $single_date ); ?></span>
					<?php endif; ?>
				<?php else : ?>
					<?php echo esc_html( wp_strip_all_tags( (string) $parent_title, true ) ); ?>
				<?php endif; ?>
			</h1>
			<div class="subNavWrap">
				<?php if ( ! empty( $submenu_items ) ) : ?>
					<ul class="subNav">
						<?php foreach ( $submenu_items as $submenu ) : ?>
							<?php
							$active_class = ( untrailingslashit( $submenu->url ) === $normalized_current_url ) ? 'active' : '';
							// 고정 페이지인데 서브네비는 카테고리 링크만 있을 때: 페이지에 매칭된 카테고리 항목에 active.
							if ( '' === $active_class && is_page() && $page_primary_category_id && isset( $submenu->object ) && 'category' === $submenu->object && (int) $submenu->object_id === $page_primary_category_id ) {
								$active_class = 'active';
							}
							?>
							<li>
								<?php printf( '<a href="%1$s" class="%2$s">%3$s</a>', esc_url( $submenu->url ), esc_attr( $active_class ), esc_html( wp_strip_all_tags( (string) $submenu->title, true ) ) ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
	$subbanner_html = trim( ob_get_clean() );
	$subbanner_html = preg_replace( '/>\s+</', '><', $subbanner_html );
	$subbanner_html = preg_replace( '/<p>\s*(?:&nbsp;)?\s*<\/p>/i', '', $subbanner_html );
	return $subbanner_html;
}
add_shortcode( 'goyo_subbanner', 'goyoartdark_render_subbanner' );

/**
 * 카테고리(또는 카테고리 내 검색) 아카이브 루프 렌더링.
 *
 * body_class 로 주입된 `category-type-*`, `category-width-*` 클래스와 조합되어 board.css 가 기대하는
 * `.post-list-wrap .row.{post_format}` 구조를 생성한다.
 *
 * @return string 렌더링된 HTML.
 */
function goyoartdark_render_category_loop() {
	if ( ! is_category() && ! is_search() ) {
		return '';
	}

	ob_start();

	if ( is_search() ) {
		// 검색 전용 간이 루프.
		global $wp_query;
		$total_posts = $wp_query->found_posts;
		$post_format = 'webzine';
		?>
		<section class="post-list-wrap" role="main">
			<div class="container subbox">
				<div class="cate-info container">
					<div class="totalnum"><?php echo esc_html( sprintf( /* translators: 검색 결과 수 */ '전체 %d건', $total_posts ) ); ?></div>
				</div>
				<div class="row <?php echo esc_attr( $post_format ); ?>">
					<?php if ( have_posts() ) : ?>
						<?php
						while ( have_posts() ) :
							the_post();
							?>
							<div class="col goyo-col-reveal">
								<?php get_template_part( 'inc/content' ); ?>
							</div>
						<?php endwhile; ?>
					<?php else : ?>
						<?php get_template_part( 'inc/content', 'none' ); ?>
					<?php endif; ?>
				</div>
				<div class="category-btwrap container">
					<div class="pagination">
						<?php
						$big              = 999999999;
						$pagination_links = paginate_links(
							array(
								'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
								'format'    => '?paged=%#%',
								'current'   => max( 1, get_query_var( 'paged' ) ),
								'total'     => $wp_query->max_num_pages,
								'prev_text' => '<i class="bi bi-chevron-left"></i>',
								'next_text' => '<i class="bi bi-chevron-right"></i>',
								'type'      => 'array',
								'end_size'  => 1,
								'mid_size'  => 2,
								'show_all'  => true,
							)
						);
						if ( $pagination_links ) {
							echo '<ul class="category-pagination">';
							foreach ( $pagination_links as $link ) {
								$safe_link = goyoartdark_kses_paginate_link_fragment( $link );
								if ( false !== strpos( $link, 'current' ) ) {
									echo '<li class="active">' . $safe_link . '</li>';
								} else {
									echo '<li>' . $safe_link . '</li>';
								}
							}
							echo '</ul>';
						}
						?>
					</div>
				</div>
			</div>
		</section>
		<?php
		return goyoartdark_cleanup_shortcode_output( ob_get_clean() );
	}

	// ----- 카테고리 아카이브 -----
	$current_category = get_queried_object();
	if ( ! ( $current_category instanceof WP_Term ) ) {
		return '';
	}

	$category_id     = $current_category->term_id;
	$post_format     = get_term_meta( $category_id, 'post_format', true ) ?: 'webzine';
	$thumbnail_ratio = get_term_meta( $category_id, 'thumbnail_ratio', true );

	if ( ! $thumbnail_ratio ) {
		if ( 'portfolio' === $post_format ) {
			$thumbnail_ratio = '16-9';
		} elseif ( 'photo' === $post_format || 'photo4' === $post_format ) {
			$thumbnail_ratio = '1-1';
		}
	}

	// 카테고리 내 검색 여부.
	$is_search_in_category = isset( $_GET['s'] ) && '' !== trim( wp_unslash( (string) $_GET['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended — 읽기 전용 검색 쿼리.

	// 타이틀/게시글수 표시 설정.
	$show_category_title = get_term_meta( $category_id, 'show_category_title', true );
	$show_post_count     = get_term_meta( $category_id, 'show_post_count', true );
	$show_search_icon    = get_term_meta( $category_id, 'show_search_icon', true );

	$title_hidden     = ( '1' !== $show_category_title );
	$cate_info_hidden = ( '' === $show_post_count ) ? false : ( '1' !== $show_post_count );

	// 게시글 수 계산.
	if ( $is_search_in_category ) {
		global $wp_query;
		$total_posts = $wp_query->found_posts;
	} else {
		$total_posts      = $current_category->category_count;
		$child_categories = get_categories(
			array(
				'parent'     => $category_id,
				'hide_empty' => false,
			)
		);
		foreach ( $child_categories as $child ) {
			$total_posts += $child->category_count;
		}
	}
	?>
	<section class="post-list-wrap" role="main">
		<div class="container subbox">
			<h2 class="<?php echo esc_attr( trim( 'pageTitle' . ( $title_hidden ? ' is-hidden' : '' ) ) ); ?>"><?php echo esc_html( $current_category->name ); ?></h2>

			<div class="<?php echo esc_attr( trim( 'cate-info container' . ( $cate_info_hidden ? ' is-hidden' : '' ) ) ); ?>">
				<div class="totalnum"><?php echo esc_html( sprintf( /* translators: 게시글 수 */ '전체 %d건', $total_posts ) ); ?></div>
				<?php
				// 템플릿의 `core/shortcode` → wpautop() 로 인해 `<form>` 내부/주변의 줄바꿈이 `<br>`·빈 `<p>`로 치환됨. 아래는 태그 사이 개행 없이 출력(주소: wp-includes/blocks/shortcode.php `render_block_core_shortcode` 참고).
				if ( '1' === $show_search_icon ) {
					$search_form_action = get_category_link( $category_id );
					$search_type_current  = isset( $_GET['search_type'] ) ? sanitize_text_field( wp_unslash( $_GET['search_type'] ) ) : 'title'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 읽기 전용 검색 쿼리.
					$search_s_escaped   = isset( $_GET['s'] ) ? esc_attr( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 읽기 전용 검색 쿼리.
					printf(
						'<div class="category-searchwrap"><i class="bi bi-search search-trigger"></i><div class="category-bt-search top-search"><form class="category-search-form" method="get" action="%s"><select name="search_type" class="category-search-select"><option value="title"%s>제목</option><option value="title_content"%s>제목+내용</option></select><input type="text" name="s" class="category-search-input" placeholder="검색어" value="%s"><button type="submit" class="category-search-btn"><i class="bi bi-search"></i></button><input type="hidden" name="cat" value="%d"></form></div></div>',
						esc_url( $search_form_action ),
						selected( $search_type_current, 'title', false ),
						selected( $search_type_current, 'title_content', false ),
						$search_s_escaped,
						(int) $category_id
					);
				}
				?>
			</div>

			<?php
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

			$sticky_ids   = array();
			$sticky_query = null;
			if ( ! $is_search_in_category ) {
				$sticky_ids = get_option( 'sticky_posts' );
				if ( ! empty( $sticky_ids ) ) {
					$sticky_query = new WP_Query(
						array(
							'post_type'      => 'post',
							'posts_per_page' => -1,
							'post__in'       => $sticky_ids,
							'cat'            => $category_id,
							'orderby'        => 'date',
							'order'          => 'DESC',
						)
					);
				}

				$normal_query = new WP_Query(
					array(
						'post_type'      => 'post',
						'posts_per_page' => 12 - ( ! empty( $sticky_ids ) ? count( $sticky_ids ) : 0 ),
						'cat'            => $category_id,
						'post__not_in'   => ! empty( $sticky_ids ) ? $sticky_ids : array(),
						'orderby'        => 'date',
						'order'          => 'DESC',
						'paged'          => $paged,
					)
				);
			} else {
				global $wp_query;
				$normal_query = $wp_query;
			}

			// 썸네일 사이즈: 포트폴리오형은 medium, 그 외는 thumbnail.
			$image_size = ( 'portfolio' === $post_format ) ? 'medium' : 'thumbnail';

			$row_classes = 'row ' . sanitize_html_class( $post_format );
			if ( $thumbnail_ratio && in_array( $post_format, array( 'portfolio', 'photo', 'photo4' ), true ) ) {
				$row_classes .= ' ratio-' . sanitize_html_class( $thumbnail_ratio );
			}
			?>
			<div class="<?php echo esc_attr( $row_classes ); ?>">
				<?php if ( ! $is_search_in_category && $sticky_query && $sticky_query->have_posts() ) : ?>
					<?php
					while ( $sticky_query->have_posts() ) :
						$sticky_query->the_post();
						?>
						<div class="col sticky-post goyo-col-reveal">
							<?php
							set_query_var( 'thumbnail_size', $image_size );
							get_template_part( 'inc/content' );
							?>
						</div>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php endif; ?>

				<?php if ( $normal_query->have_posts() ) : ?>
					<?php
					while ( $normal_query->have_posts() ) :
						$normal_query->the_post();
						?>
						<div class="col goyo-col-reveal">
							<?php
							set_query_var( 'thumbnail_size', $image_size );
							get_template_part( 'inc/content' );
							?>
						</div>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<?php get_template_part( 'inc/content', 'empty' ); ?>
				<?php endif; ?>
			</div>

			<div class="category-btwrap container">
				<div class="pagination">
					<?php
					$big              = 999999999;
					$pagination_links = paginate_links(
						array(
							'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
							'format'    => '?paged=%#%',
							'current'   => max( 1, get_query_var( 'paged' ) ),
							'total'     => $normal_query->max_num_pages,
							'prev_text' => '<i class="bi bi-chevron-left"></i>',
							'next_text' => '<i class="bi bi-chevron-right"></i>',
							'type'      => 'array',
							'end_size'  => 1,
							'mid_size'  => 2,
							'show_all'  => true,
						)
					);
					if ( $pagination_links ) {
						echo '<ul class="category-pagination">';
						foreach ( $pagination_links as $link ) {
							$safe_link = goyoartdark_kses_paginate_link_fragment( $link );
							if ( false !== strpos( $link, 'current' ) ) {
								echo '<li class="active">' . $safe_link . '</li>';
							} else {
								echo '<li>' . $safe_link . '</li>';
							}
						}
						echo '</ul>';
					}
					?>
				</div>
			</div>
		</div>
	</section>
	<?php
	return goyoartdark_cleanup_shortcode_output( ob_get_clean() );
}
add_shortcode( 'goyo_category_loop', 'goyoartdark_render_category_loop' );

/**
 * 싱글 포스트 본문 + 이전·다음글 + 관련글을 렌더링한다.
 *
 * 카테고리별 메타(관련글 표시 여부, 이전·다음글 표시 여부)에 따라 동적으로 섹션을 숨긴다.
 *
 * @return string 렌더링된 HTML.
 */
function goyoartdark_render_single_content() {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="container single-layout">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'inc/content-page' );
		endwhile;

		// inc/pagination.php 에서 이전글/다음글 + "목록으로" 버튼을 출력.
		get_template_part( 'inc/pagination' );

		// 관련글 표시 여부 확인.
		$post_categories    = get_the_category();
		$category_id        = ! empty( $post_categories ) ? $post_categories[0]->term_id : 0;
		$show_related_posts = true;
		if ( $category_id ) {
			$show_related_posts_meta = get_term_meta( $category_id, 'show_related_posts', true );
			if ( '0' === $show_related_posts_meta ) {
				$show_related_posts = false;
			}
		}

		if ( $show_related_posts ) {
			get_template_part( 'inc/related-post' );
		}
		?>
	</div>
	<?php
	return goyoartdark_cleanup_shortcode_output( ob_get_clean() );
}
add_shortcode( 'goyo_single_content', 'goyoartdark_render_single_content' );

/**
 * 메인페이지용 카테고리 최신 글 쿼리.
 *
 * @param int $category_id 카테고리 term_id.
 * @param int $count       가져올 글 수.
 * @return WP_Query|null
 */
function goyoartdark_main_page_posts_query( $category_id, $count ) {
	$category_id = absint( $category_id );
	$count       = absint( $count );
	if ( $category_id <= 0 || $count <= 0 ) {
		return null;
	}
	$args = array(
		'post_type'           => 'post',
		'cat'                 => $category_id,
		'posts_per_page'      => $count,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	return new WP_Query( apply_filters( 'goyoartdark_main_page_posts_query_args', $args, $category_id, $count ) );
}

/**
 * 썸네일 비율 slug → CSS aspect-ratio 용 값.
 *
 * @param string $slug 저장값(또는 get_theme_mod 원본).
 * @return string 예: "4 / 3"
 */
function goyoartdark_main_page_ratio_slug_to_css_aspect( $slug ) {
	$slug = function_exists( 'goyoartdark_sanitize_main_page_image_ratio_slug' )
		? goyoartdark_sanitize_main_page_image_ratio_slug( $slug )
		: '4-3';
	$map  = array(
		'4-3'  => '4 / 3',
		'1-1'  => '1 / 1',
		'3-4'  => '3 / 4',
		'16-9' => '16 / 9',
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : '4 / 3';
}

/**
 * 슬라이드·갤러리용 대표 이미지 URL(없으면 본문 첫 img).
 *
 * @param int    $post_id 포스트 ID.
 * @param string $size    이미지 사이즈.
 * @return string
 */
function goyoartdark_find_first_image_in_blocks( $blocks, $size = 'large' ) {
	if ( ! is_array( $blocks ) || empty( $blocks ) ) {
		return '';
	}

	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$name  = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';

		if ( ! empty( $attrs['id'] ) ) {
			$image_src = wp_get_attachment_image_src( (int) $attrs['id'], $size );
			if ( is_array( $image_src ) && ! empty( $image_src[0] ) ) {
				return (string) $image_src[0];
			}
		}

		if ( in_array( $name, array( 'core/image', 'core/cover' ), true ) && ! empty( $attrs['url'] ) ) {
			return (string) $attrs['url'];
		}

		if ( isset( $block['innerHTML'] ) && preg_match( '/<img[^>]+(?:src|data-src)=[\'"]([^\'"]+)[\'"]/i', (string) $block['innerHTML'], $matches ) ) {
			return (string) $matches[1];
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$inner_image = goyoartdark_find_first_image_in_blocks( $block['innerBlocks'], $size );
			if ( '' !== $inner_image ) {
				return $inner_image;
			}
		}
	}

	return '';
}

function goyoartdark_main_page_entry_image_url( $post_id, $size = 'large' ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 ) {
		return '';
	}
	if ( has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) {
			return $url;
		}
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}

	if ( has_blocks( $post->post_content ) ) {
		$first_block_image = goyoartdark_find_first_image_in_blocks( parse_blocks( $post->post_content ), $size );
		if ( '' !== $first_block_image ) {
			return $first_block_image;
		}
	}

	if ( preg_match( '/<img[^>]+(?:src|data-src)=[\'"]([^\'"]+)[\'"]/i', $post->post_content, $matches ) ) {
		return (string) $matches[1];
	}

	$attached_images = get_attached_media( 'image', $post_id );
	if ( ! empty( $attached_images ) ) {
		$first_attachment = reset( $attached_images );
		if ( $first_attachment instanceof WP_Post ) {
			$image_src = wp_get_attachment_image_src( (int) $first_attachment->ID, $size );
			if ( is_array( $image_src ) && ! empty( $image_src[0] ) ) {
				return (string) $image_src[0];
			}
		}
	}

	return '';
}

/**
 * 메인 슬라이드·갤러리 숏코드 출력 — 실제/유효 홈 요청인지.
 *
 * is_front_page() 가 블록/FSE/숏코드 출력 타이밍에 false 가 되는 경우를 보강한다.
 * REQUEST_URI path 와 home_url path 가 같으면(루트·서브 path) 404ㆍ페이징 2+ 가 아닐 때 홈으로 본다.
 *
 * @return bool
 */
function goyoartdark_is_request_effective_front_page() {
	if ( is_front_page() ) {
		return true;
	}
	if ( is_admin() && ! is_customize_preview() ) {
		return false;
	}

	global $wp_query;
	if ( $wp_query instanceof WP_Query && $wp_query->is_front_page() ) {
		return true;
	}

	$show_on_front = (string) get_option( 'show_on_front' );
	if ( 'posts' === $show_on_front && $wp_query instanceof WP_Query && $wp_query->is_home() && ! $wp_query->is_paged() ) {
		return true;
	}

	if ( 'page' === $show_on_front ) {
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id > 0 ) {
			if ( (int) get_queried_object_id() === $front_id ) {
				return true;
			}
			if ( $wp_query instanceof WP_Query && $wp_query->is_page( $front_id ) ) {
				return true;
			}
		}
	}

	// 쿼리/queried_id 가 틀어져도 URL 이 사이트 홈 path 와 일치하면(블록 템플릿) 홈 UI 로 처리.
	if ( is_404() || ! isset( $_SERVER['REQUEST_URI'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return false;
	}
	if ( (int) get_query_var( 'paged' ) > 1 ) {
		return false;
	}

	$path = (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = ( null === $path || false === $path ) ? '' : $path;
	$path = ( '' === $path ) ? '/' : $path;
	/* XAMPP 등에서 홈이 /서브/index.php 로 올 때 path 비교가 깨지는 경우 방지 */
	if ( preg_match( '#/index\.php$#i', $path ) ) {
		$path = substr( $path, 0, -10 );
		$path = ( '' === $path ) ? '/' : $path;
	}
	$path = untrailingslashit( $path );

	$home_path = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home_path = ( null === $home_path || false === $home_path ) ? '/' : $home_path;
	$home_path = ( '' === $home_path ) ? '/' : $home_path;
	$home_path = untrailingslashit( $home_path );

	$is_root_both = in_array( $path, array( '', '/' ), true ) && in_array( $home_path, array( '', '/' ), true );
	if ( $path === $home_path || $is_root_both ) {
		return true;
	}

	return false;
}

/**
 * 사이트 에디터(FSE)가 숏코드 블록을 REST block-renderer 로 미리보기할 때 true.
 * 이 요청에서는 is_front_page() 가 false 인 경우가 많아 홈 전용 숏코드가 비어 보이므로 맥락을 허용한다.
 *
 * @return bool
 */
function goyoartdark_is_site_editor_block_preview_request() {
	if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
		return false;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return false;
	}
	$route = '';
	if ( isset( $GLOBALS['wp'] ) && $GLOBALS['wp'] instanceof WP && isset( $GLOBALS['wp']->query_vars['rest_route'] ) ) {
		$route = (string) $GLOBALS['wp']->query_vars['rest_route'];
	}
	if ( '' === $route && isset( $_SERVER['REQUEST_URI'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$full     = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$req_path = (string) wp_parse_url( $full, PHP_URL_PATH );
		$query_s  = (string) wp_parse_url( $full, PHP_URL_QUERY );
		if ( '' !== $query_s ) {
			parse_str( $query_s, $qargs );
			if ( ! empty( $qargs['rest_route'] ) ) {
				$route = (string) $qargs['rest_route'];
			}
		}
		if ( '' === $route && is_string( $req_path ) && false !== strpos( $req_path, 'block-renderer' ) ) {
			$route = $req_path;
		}
	}
	$route_norm = '/' . ltrim( $route, '/' );
	if ( false === stripos( $route_norm, 'wp/v2/block-renderer/' ) ) {
		return false;
	}
	$referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( '' === $referer ) {
		return false;
	}
	$referer_path = (string) wp_parse_url( $referer, PHP_URL_PATH );
	if ( '' === $referer_path || false === strpos( $referer_path, 'site-editor.php' ) ) {
		return false;
	}
	return true;
}

/**
 * 슬라이드·갤러리가 비었을 때 에디터/커스터마이저에 안내 플레이스홀더를 둘 맥락.
 *
 * @return bool
 */
function goyoartdark_show_main_page_shortcode_empty_placeholder() {
	return is_customize_preview() || goyoartdark_is_site_editor_block_preview_request();
}

/**
 * 메인 슬라이드·갤러리 숏코드 출력 여부( goyoartdark_is_request_effective_front_page + 미리보기 루트 완화 ).
 *
 * @return bool
 */
function goyoartdark_is_main_page_blocks_context() {
	if ( goyoartdark_is_request_effective_front_page() ) {
		return true;
	}
	if ( goyoartdark_is_site_editor_block_preview_request() ) {
		return true;
	}
	if ( ! is_customize_preview() ) {
		return false;
	}
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return false;
	}
	$path = (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$path = untrailingslashit( '' === $path ? '/' : $path );
	$home = untrailingslashit( '' === $home ? '/' : $home );
	if ( '' === $path || '/' === $path ) {
		return ( '' === $home || '/' === $home );
	}
	if ( $home && $path === $home ) {
		return true;
	}
	return false;
}

/**
 * 히어로 — 커스터마이저 `default`·get_theme_mod 공통(현재 샘플 문구와 동일, 번역은 출력 시).
 *
 * @return array{slogan: string, subtext: string, button_label: string}
 */
function goyoartdark_get_hero_default_strings() {
	// goyominimal-export.dat 사용자정의 기준 기본문구(신규 설치·theme_mod 비어 있을 때).
	return array(
		'slogan' => 'A Creative Website, <br>Done in One Day',
	);
}

// goyoartdark_render_hero_inner_content_html() 제거됨 — 슬로건·버튼 HTML 렌더링 불필요.
// 보조문구 스타일(폰트·색상·투명도)은 아래 CSS 변수 주입 함수에서 유지됨.

/**
 * 보조문구 스타일 커스터마이저 값을 CSS 변수로 주입한다.
 * (.mainhero-lead 에 --goyo-subtext-* 변수로 적용)
 */
function goyoartdark_hero_subtext_style_css_var() {
	if ( ! goyoartdark_is_main_page_blocks_context() && ! is_front_page() && ! is_customize_preview() ) {
		return;
	}

	$hex_to_rgba = static function ( $hex, $opacity ) {
		$hex     = sanitize_hex_color( (string) $hex );
		if ( ! $hex ) {
			return '';
		}
		$opacity = max( 0, min( 1, (float) $opacity ) );
		$hex     = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		if ( $opacity >= 0.999 ) {
			return '#' . $hex;
		}
		return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . round( $opacity, 2 ) . ')';
	};

	$ff  = get_theme_mod( 'goyo_hero_subtext_font_family', '' );
	$ff  = is_string( $ff ) ? goyoartdark_sanitize_hero_font_family( trim( $ff ) ) : '';
	$sz  = get_theme_mod( 'goyo_hero_subtext_font_size', 'clamp(0.9rem, 1.8vw, 1.1rem)' );
	$sz  = is_string( $sz ) ? goyoartdark_sanitize_hero_css_value( trim( $sz ) ) : '';
	$cl  = sanitize_hex_color( (string) get_theme_mod( 'goyo_hero_subtext_color', '#f2f2f0' ) );
	$op  = goyoartdark_sanitize_hero_opacity( get_theme_mod( 'goyo_hero_subtext_opacity', 1 ) );
	$cl  = $hex_to_rgba( $cl, $op );

	$vars = array();
	if ( '' !== $ff ) {
		$vars[] = '--goyo-subtext-font-family: ' . $ff;
	}
	if ( '' !== $sz ) {
		$vars[] = '--goyo-subtext-font-size: ' . $sz;
	}
	if ( '' !== $cl ) {
		$vars[] = '--goyo-subtext-color: ' . $cl;
	}

	if ( empty( $vars ) ) {
		return;
	}

	$css = '.mainhero-lead { ' . implode( '; ', $vars ) . '; }';
	wp_add_inline_style( 'goyoartdark-style', $css );
	if ( wp_style_is( 'goyoartdark-front-page', 'enqueued' ) ) {
		wp_add_inline_style( 'goyoartdark-front-page', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'goyoartdark_hero_subtext_style_css_var', 36 );

// [goyo_hero_inner] 숏코드는 제거됨 — 슬로건 HTML은 .conWrap .mainhero 안의 슬라이더에 직접 렌더링

// [goyo_hero_font_back] 숏코드 및 --goyo-hero-bg-image CSS 변수 주입은 제거됨.
// 배경 이미지는 .conWrap .mainhero 의 슬라이더(MetaSlider)가 직접 담당한다.

if ( ! function_exists( 'goyoartdark_render_block_core_shortcode' ) ) :
	/**
	 * `wp:pattern` 안의 `core/shortcode` 는 템플릿 최상단에서 `do_shortcode()` 적용 시점에 아직 존재하지 않는다.
	 * 코어 `render_block_core_shortcode` 는 `wpautop()`만 하므로 대괄호 숏코드 문자열이 그대로 출력되는 경우가 있다 — 여기서 실행한다.
	 *
	 * @param string               $block_content 출력 HTML.
	 * @param array<string, mixed> $block         파싱된 블록 배열.
	 * @return string
	 */
	function goyoartdark_render_block_core_shortcode( $block_content, $block ) {
		if ( empty( $block['blockName'] ) || 'core/shortcode' !== $block['blockName'] ) {
			return $block_content;
		}
		$raw_shortcode_content = '';
		if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
			$raw_shortcode_content = $block['innerHTML'];
		} else {
			$raw_shortcode_content = (string) $block_content;
		}
		$raw_shortcode_content = trim( shortcode_unautop( $raw_shortcode_content ) );
		if ( '' === $raw_shortcode_content ) {
			return '';
		}
		return goyoartdark_cleanup_shortcode_output( do_shortcode( $raw_shortcode_content ) );
	}
endif;
add_filter( 'render_block', 'goyoartdark_render_block_core_shortcode', 20, 2 );

if ( ! function_exists( 'goyoartdark_render_block_cleanup_main_content_paragraphs' ) ) :
	/**
	 * 메인페이지 콘텐츠 블록에서 빈 p 태그를 제거한다.
	 *
	 * @param string               $block_content 출력 HTML.
	 * @param array<string, mixed> $block         파싱된 블록 배열.
	 * @return string
	 */
	function goyoartdark_render_block_cleanup_main_content_paragraphs( $block_content, $block ) {
		if ( empty( $block['blockName'] ) || 'core/post-content' !== $block['blockName'] ) {
			return $block_content;
		}
		if ( ! is_front_page() && ! is_home() && ! goyoartdark_is_main_page_blocks_context() ) {
			return $block_content;
		}
		return goyoartdark_strip_empty_paragraphs_only( $block_content );
	}
endif;







