<?php
/**
 * One-time / maintenance: resize & compress images under assets/img.
 * Run: php scripts/optimize-site-images.php
 */

$root = dirname(__DIR__);
require_once $root . '/functions/image-optimize.php';

$imgRoot = $root . '/assets/img';
$extensions = ['webp', 'jpg', 'jpeg', 'png'];
$done = 0;
$skipped = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($imgRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, $extensions, true)) {
        continue;
    }

    $full = $file->getPathname();
    $rel = str_replace('\\', '/', substr($full, strlen($imgRoot) + 1));
    $maxW = diar_max_width_for_image_path($rel);
    $before = filesize($full);

    if (!diar_optimize_image_file($full, $maxW, 80)) {
        $skipped++;
        echo "SKIP  $rel\n";
        continue;
    }

    clearstatcache(true, $full);
    $after = filesize($full);
    $done++;
    printf("OK    %s  %s -> %s KB\n", $rel, number_format($before / 1024, 0), number_format($after / 1024, 0));
}

echo "\nOptimized: $done, skipped: $skipped\n";
