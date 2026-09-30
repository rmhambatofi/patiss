<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

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
