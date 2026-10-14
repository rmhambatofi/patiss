<?php

use App\Services\ImageSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('removes every EXIF block, GPS included, from a JPEG', function () {
    Storage::fake('public');
    $source = UploadedFile::fake()->createWithContent('photo.jpg', jpegWithGpsExif());
    expect(exif_read_data($source->getRealPath()))->toHaveKey('GPSLatitude');

    $path = app(ImageSanitizer::class)->store($source, 'public', 'cake-types');

    Storage::disk('public')->assertExists($path);
    $exif = @exif_read_data(Storage::disk('public')->path($path));
    expect($exif)->not->toHaveKey('GPSLatitude')
        ->and($exif['SectionsFound'])->not->toContain('EXIF')
        ->and($exif['SectionsFound'])->not->toContain('GPS')
        ->and($exif['SectionsFound'])->not->toContain('IFD0');
});

it('scales a 6000 px PNG down to the configured maximum width as a JPEG', function () {
    Storage::fake('public');
    $pngPath = tempnam(sys_get_temp_dir(), 'png');
    imagepng(imagecreatetruecolor(6000, 300), $pngPath);

    $default = app(ImageSanitizer::class)->store($pngPath, 'public', 'cake-types');
    $custom = app()->make(ImageSanitizer::class, ['maxWidth' => 1200])->store($pngPath, 'public', 'cake-types');
    unlink($pngPath);

    foreach ([[$default, 2000, 100], [$custom, 1200, 60]] as [$path, $width, $height]) {
        [$w, $h, $type] = getimagesize(Storage::disk('public')->path($path));
        expect($w)->toBe($width)->and($h)->toBe($height)->and($type)->toBe(IMAGETYPE_JPEG);
    }
});
