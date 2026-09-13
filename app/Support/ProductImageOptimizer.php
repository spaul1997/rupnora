<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductImageOptimizer
{
    private const DISK = 'public';

    private const SIZES = [
        'sm' => 480,
        'md' => 960,
        'lg' => 1600,
    ];

    public static function store(UploadedFile $file, string $directory = 'products'): string
    {
        $source = self::createSourceImage($file);
        $baseName = trim($directory, '/').'/'.(string) Str::uuid();

        foreach (self::SIZES as $suffix => $maxWidth) {
            $resized = self::resize($source, $maxWidth);

            Storage::disk(self::DISK)->put("{$baseName}-{$suffix}.webp", self::encodeWebp($resized));

            if (function_exists('imageavif')) {
                Storage::disk(self::DISK)->put("{$baseName}-{$suffix}.avif", self::encodeAvif($resized));
            }

            imagedestroy($resized);
        }

        imagedestroy($source);

        return "{$baseName}-lg.webp";
    }

    public static function delete(?string $path): void
    {
        foreach (self::variantPaths($path) as $variantPath) {
            Storage::disk(self::DISK)->delete($variantPath);
        }
    }

    public static function variantPaths(?string $path): array
    {
        if (! $path) {
            return [];
        }

        if (! preg_match('/^(.*)-(sm|md|lg)\.(webp|avif)$/', $path, $matches)) {
            return [$path];
        }

        $paths = [];

        foreach (array_keys(self::SIZES) as $suffix) {
            $paths[] = "{$matches[1]}-{$suffix}.webp";
            $paths[] = "{$matches[1]}-{$suffix}.avif";
        }

        return array_values(array_unique($paths));
    }

    private static function createSourceImage(UploadedFile $file): GdImage
    {
        $path = $file->getRealPath();
        $mime = $file->getMimeType();

        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/avif' => function_exists('imagecreatefromavif') ? imagecreatefromavif($path) : false,
            default => false,
        };

        if (! $image instanceof GdImage) {
            throw new RuntimeException('The uploaded image could not be processed.');
        }

        if ($mime === 'image/jpeg') {
            $image = self::orientJpeg($image, $path);
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    private static function orientJpeg(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = $exif['Orientation'] ?? null;

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    private static function resize(GdImage $source, int $maxWidth): GdImage
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetWidth = min($sourceWidth, $maxWidth);
        $targetHeight = (int) round($sourceHeight * ($targetWidth / $sourceWidth));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);

        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        return $target;
    }

    private static function encodeWebp(GdImage $image): string
    {
        ob_start();
        imagewebp($image, null, 88);

        return (string) ob_get_clean();
    }

    private static function encodeAvif(GdImage $image): string
    {
        ob_start();
        imageavif($image, null, 72, 6);

        return (string) ob_get_clean();
    }
}
