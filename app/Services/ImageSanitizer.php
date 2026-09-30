<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Interfaces\ImageManagerInterface;

/**
 * Decodes an image, scales it down and re-encodes it as JPEG so that no
 * EXIF or GPS metadata survives (KTD7, R4).
 */
class ImageSanitizer
{
    public function __construct(
        private ImageManagerInterface $images,
        private int $maxWidth = 2000,
        private int $quality = 85,
    ) {}

    /**
     * @return string The stored path, relative to the disk root.
     */
    public function store(UploadedFile|string $source, string $disk, string $directory): string
    {
        $image = $this->images->decodePath($source instanceof UploadedFile ? $source->getRealPath() : $source);
        $image->scaleDown(width: $this->maxWidth);

        $path = trim($directory, '/').'/'.Str::random(40).'.jpg';
        Storage::disk($disk)->put($path, (string) $image->encode(new JpegEncoder(quality: $this->quality, strip: true)));

        return $path;
    }
}
