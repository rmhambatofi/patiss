<?php

use App\Services\ImageSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Build a JPEG whose APP1 segment carries an EXIF GPS block (latitude 49° N).
 */
function jpegWithGpsExif(): string
{
    ob_start();
    imagejpeg(imagecreatetruecolor(40, 30));
    $jpeg = ob_get_clean();

    // TIFF header (little endian), IFD0 pointing to a GPS IFD at 26, then the GPS IFD and its rationals at 56.
    $tiff = 'II'.pack('v', 0x2A).pack('V', 8)
        .pack('v', 1).pack('vvVV', 0x8825, 4, 1, 26).pack('V', 0)
        .pack('v', 2)
        .pack('vvV', 0x0001, 2, 2)."N\0\0\0"
        .pack('vvVV', 0x0002, 5, 3, 56)
        .pack('V', 0)
        .pack('VVVVVV', 49, 1, 1, 1, 0, 1);
    $payload = "Exif\0\0".$tiff;
    $app1 = "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;

    return substr($jpeg, 0, 2).$app1.substr($jpeg, 2);
}

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
