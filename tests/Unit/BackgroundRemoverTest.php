<?php

namespace Tests\Unit;

use App\Services\BackgroundRemover;
use PHPUnit\Framework\TestCase;

// Pictures drawn with GD, no database
class BackgroundRemoverTest extends TestCase
{
    // A dark tub with a white label, on $background
    private function product(array $background = [255, 255, 255]): \GdImage
    {
        $image = imagecreatetruecolor(200, 200);
        imagefill($image, 0, 0, imagecolorallocate($image, ...$background));
        imagefilledrectangle($image, 50, 40, 150, 170, imagecolorallocate($image, 30, 30, 30));
        imagefilledrectangle($image, 70, 80, 130, 120, imagecolorallocate($image, 255, 255, 255));

        return $image;
    }

    private function alpha(\GdImage $image, int $x, int $y): int
    {
        return (imagecolorat($image, $x, $y) >> 24) & 0x7F;
    }

    public function test_the_white_background_becomes_transparent_and_the_product_stays(): void
    {
        $image = $this->product();

        $this->assertTrue(BackgroundRemover::removeLightBackground($image));
        $this->assertSame(127, $this->alpha($image, 0, 0));
        $this->assertSame(127, $this->alpha($image, 199, 199));
        $this->assertSame(127, $this->alpha($image, 49, 100), 'right next to the product');
        $this->assertSame(0, $this->alpha($image, 60, 50), 'the product');
        $this->assertSame(0, $this->alpha($image, 100, 100), 'the white label inside the product is kept');
    }

    public function test_an_off_white_background_with_jpeg_noise_is_removed(): void
    {
        $image = $this->product([246, 244, 245]);
        imagesetpixel($image, 5, 5, imagecolorallocate($image, 240, 238, 242));

        $this->assertTrue(BackgroundRemover::removeLightBackground($image));
        $this->assertSame(127, $this->alpha($image, 5, 5));
        $this->assertSame(0, $this->alpha($image, 100, 100));
    }

    public function test_a_photo_with_a_scene_behind_the_product_is_left_alone(): void
    {
        $dark = $this->product([90, 120, 60]);
        $this->assertFalse(BackgroundRemover::removeLightBackground($dark));
        $this->assertSame(0, $this->alpha($dark, 0, 0));

        // Light but not plain: a gradient wall
        $wall = imagecreatetruecolor(200, 200);
        for ($x = 0; $x < 200; $x++) {
            imageline($wall, $x, 0, $x, 199, imagecolorallocate($wall, 255 - intdiv($x, 4), 230, 210));
        }
        $this->assertFalse(BackgroundRemover::removeLightBackground($wall));
    }

    public function test_a_picture_already_cut_out_is_left_alone(): void
    {
        $image = imagecreatetruecolor(200, 200);
        imagealphablending($image, false);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 255, 255, 127));
        imagefilledrectangle($image, 50, 40, 150, 170, imagecolorallocate($image, 30, 30, 30));

        $this->assertFalse(BackgroundRemover::removeLightBackground($image));
    }

    public function test_a_white_bottle_whose_outline_has_a_gap_keeps_its_background(): void
    {
        // A white bottle (a few shades off the background) with a thin outline open at the top: the fill
        // would run inside the bottle, so nothing is removed rather than eating the product
        $image = imagecreatetruecolor(200, 200);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagefilledrectangle($image, 50, 40, 150, 170, imagecolorallocate($image, 250, 250, 250));
        $outline = imagecolorallocate($image, 120, 120, 120);
        imageline($image, 50, 40, 50, 170, $outline);
        imageline($image, 150, 40, 150, 170, $outline);
        imageline($image, 50, 170, 150, 170, $outline);
        imageline($image, 50, 40, 90, 40, $outline);
        imageline($image, 110, 40, 150, 40, $outline);
        imagefilledrectangle($image, 60, 100, 140, 130, imagecolorallocate($image, 200, 60, 20));

        $this->assertFalse(BackgroundRemover::removeLightBackground($image));
        $this->assertSame(0, $this->alpha($image, 0, 0));
        $this->assertSame(0, $this->alpha($image, 100, 60));
    }

    public function test_an_all_white_picture_is_left_alone(): void
    {
        $image = imagecreatetruecolor(100, 100);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));

        $this->assertFalse(BackgroundRemover::removeLightBackground($image));
    }
}
