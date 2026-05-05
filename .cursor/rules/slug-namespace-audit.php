<?php
/**
 * goyoartdark 테마 무결성 점검 (예: set_theme_mod 런타임 금지).
 * 블록 네임스페이스·슬러그 상세는 slug-namespace-guard.mdc 를 따른다.
 *
 * 사용법:
 * php .cursor/rules/slug-namespace-audit.php
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$extensions = array('php', 'html', 'css', 'js', 'json');
$skipDirs   = array('.git', 'node_modules', 'vendor');
$skipFiles  = array(
	'.cursor/rules/slug-namespace-audit.php',
);

$violations = array();

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
	/** @var SplFileInfo $fileInfo */
	$path = $fileInfo->getPathname();
	$relativePath = str_replace('\\', '/', substr($path, strlen($root) + 1));

	foreach ($skipDirs as $skipDir) {
		if (strpos($relativePath, $skipDir . '/') === 0) {
			continue 2;
		}
	}
	if (in_array($relativePath, $skipFiles, true)) {
		continue;
	}

	$extension = strtolower((string) pathinfo($relativePath, PATHINFO_EXTENSION));
	if (!in_array($extension, $extensions, true)) {
		continue;
	}

	$content = @file_get_contents($path);
	if (false === $content) {
		continue;
	}

	if ( 'php' === $extension && 0 !== strpos( $relativePath, '.cursor/' ) && preg_match( '/\bset_theme_mod\s*\(/', $content ) ) {
		$violations[] = '[SECURITY] 테마 저장소 변경은 DB/외부 도구 로만 허용. ' . $relativePath . ' 에 set_theme_mod() 포함';
	}
}

if (!empty($violations)) {
	fwrite(STDERR, "❌ theme integrity audit failed\n");
	foreach ($violations as $violation) {
		fwrite(STDERR, ' - ' . $violation . "\n");
	}
	exit(1);
}

fwrite(STDOUT, "✅ theme integrity audit passed\n");
exit(0);
