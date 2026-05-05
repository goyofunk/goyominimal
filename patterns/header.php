<?php
/**
 * Title: Header
 * Slug: goyoartdark/header
 * Categories: header
 * Block Types: core/template-part/header
 * Description: 한 줄( Columns ) — 왼쪽 로고·제목, 오른쪽 GNB( 커스텀 블록 ).
 *
 * 로고는 WordPress 코어 `custom_logo`(첨부 ID)만 사용한다. URL 기반 `mytheme_*` 레거시 키는 사용하지 않는다.
 *
 * @package WordPress
 * @subpackage Goyoartdark
 * @since goyoartdark 1.0
 */
?>
<!-- wp:group {"className":"container"} -->
<div class="wp-block-group container">
	<!-- wp:columns {"isStackedOnMobile":false,"className":"goyo-header-row","verticalAlignment":"center"} -->
	<div class="wp-block-columns goyo-header-row is-not-stacked-on-mobile are-vertically-aligned-center">
		<!-- wp:column {"width":"32%","verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:32%">
			<!-- wp:group {"className":"goyo-header-brand"} -->
			<div class="wp-block-group goyo-header-brand">
				<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
					<?php echo wp_kses_post( get_custom_logo() ); ?>
				<?php else : ?>
					<!-- wp:site-title {"level":0} /-->
				<?php endif; ?>
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"width":"68%","verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:68%">
			<!-- wp:goyoartdark/site-header /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
