<?php
/**
 * Resize and recompress images for fast web delivery (GD).
 */

function diar_image_load_from_file(string $path) {
    if (!is_file($path) || !function_exists('getimagesize')) {
        return [null, null];
    }
    $info = @getimagesize($path);
    if (!$info || !isset($info[2])) {
        return [null, null];
    }
    $type = (int) $info[2];
    $image = null;
    switch ($type) {
        case IMAGETYPE_JPEG:
            if (function_exists('imagecreatefromjpeg')) {
                $image = @imagecreatefromjpeg($path);
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('imagecreatefrompng')) {
                $image = @imagecreatefrompng($path);
            }
            break;
        case IMAGETYPE_GIF:
            if (function_exists('imagecreatefromgif')) {
                $image = @imagecreatefromgif($path);
            }
            break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                $image = @imagecreatefromwebp($path);
            }
            break;
    }
    return [$image, $info];
}

function diar_image_resize_resource($image, int $srcW, int $srcH, int $maxWidth) {
    if ($maxWidth <= 0 || $srcW <= $maxWidth) {
        return $image;
    }
    $ratio = $maxWidth / $srcW;
    $newW = $maxWidth;
    $newH = (int) max(1, round($srcH * $ratio));
    if (!function_exists('imagescale')) {
        return $image;
    }
    $scaled = imagescale($image, $newW, $newH, IMG_BILINEAR_FIXED);
    if ($scaled !== false) {
        imagedestroy($image);
        return $scaled;
    }
    return $image;
}

/**
 * Optimize file in place. Returns true on success.
 */
function diar_optimize_image_file(string $absolutePath, int $maxWidth = 1600, int $quality = 80): bool {
    if (!is_file($absolutePath) || !function_exists('imagewebp')) {
        return false;
    }

    [$image, $info] = diar_image_load_from_file($absolutePath);
    if (!$image || !$info) {
        return false;
    }

    $srcW = (int) $info[0];
    $srcH = (int) $info[1];
    $image = diar_image_resize_resource($image, $srcW, $srcH, $maxWidth);

    $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
    $ok = false;
    if ($ext === 'webp') {
        $ok = imagewebp($image, $absolutePath, $quality);
    } elseif ($ext === 'jpg' || $ext === 'jpeg') {
        $ok = imagejpeg($image, $absolutePath, $quality);
    } elseif ($ext === 'png') {
        $ok = imagepng($image, $absolutePath, 6);
    } else {
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $absolutePath);
        if ($webpPath !== $absolutePath) {
            $ok = imagewebp($image, $webpPath, $quality);
        }
    }
    imagedestroy($image);

    if ($ok && function_exists('clearstatcache')) {
        clearstatcache(true, $absolutePath);
    }
    return (bool) $ok;
}

/**
 * Save uploaded temp file to destination as optimized WebP.
 */
function diar_save_optimized_upload(string $tmpPath, string $destPath, int $maxWidth = 1600, int $quality = 80): bool {
    $dir = dirname($destPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    if (!function_exists('imagewebp')) {
        return @move_uploaded_file($tmpPath, $destPath) || @copy($tmpPath, $destPath);
    }

    [$image, $info] = diar_image_load_from_file($tmpPath);
    if (!$image || !$info) {
        return @move_uploaded_file($tmpPath, $destPath) || @copy($tmpPath, $destPath);
    }

    $srcW = (int) $info[0];
    $srcH = (int) $info[1];
    $image = diar_image_resize_resource($image, $srcW, $srcH, $maxWidth);

    if (!preg_match('/\.webp$/i', $destPath)) {
        $destPath = preg_replace('/\.[^.]+$/', '.webp', $destPath);
        if ($destPath === null || $destPath === '') {
            $destPath .= '.webp';
        }
    }

    $ok = imagewebp($image, $destPath, $quality);
    imagedestroy($image);
    return (bool) $ok;
}

function diar_max_width_for_image_path(string $relativePath): int {
    $rel = str_replace('\\', '/', ltrim($relativePath, '/'));
    if (str_starts_with($rel, 'team/')) {
        return 800;
    }
    if (str_starts_with($rel, 'projects/')) {
        return 1200;
    }
    if (str_starts_with($rel, 'partners/')) {
        return 400;
    }
    return 1600;
}
