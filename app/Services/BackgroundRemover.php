<?php

namespace App\Services;

// Makes the plain white (or very light) background around a product picture transparent, the way the
// admin used to do by hand before uploading. Only the background touching the edges of the picture is
// removed (a flood fill from the border), so white labels and caps inside the product stay. Pictures it
// can't handle safely are left as they are: a real scene behind the product, a picture that is already
// transparent, or a white product that would disappear with its background.
class BackgroundRemover
{
    // A border pixel counts as a light background when every channel is at least this bright
    private const LIGHT = 200;
    // Distance (largest channel difference) still counted as background: JPEG noise. Kept tight because
    // white bottles are only a few shades darker than the background (tuned on the shop's own pictures)
    private const MATCH = 10;
    // Pixels between MATCH and FEATHER next to the background become partly transparent (smooth edges)
    private const FEATHER = 80;
    // Share of the border that must be the background colour, otherwise it's a photo with a scene
    private const MIN_BORDER_SHARE = 0.9;
    // Share of the border already transparent above which the picture was cut out before upload
    private const TRANSPARENT_BORDER_SHARE = 0.3;
    // Above this share of the picture removed, the product itself was probably white: keep everything
    private const MAX_REMOVED_SHARE = 0.97;
    // Removed pixels with product on both sides, across and up/down, mean the fill leaked into a white
    // product. Clean cut-outs stay under 0.3% of the product; leaks into white bottles start above 1%
    private const MAX_ENCLOSED_SHARE = 0.01;

    private const UNSEEN = "\0";
    private const BACKGROUND = "\1";
    private const CHECKED = "\2";

    // Changes $image in place; returns false when it was left untouched
    public static function removeLightBackground(\GdImage $image): bool
    {
        if (!imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width < 3 || $height < 3) {
            return false;
        }

        $background = self::borderBackground($image, $width, $height);
        if ($background === null) {
            return false;
        }

        // Flood fill from every background pixel on the border
        $state = str_repeat(self::UNSEEN, $width * $height);
        $stack = [];
        foreach (self::borderPoints($width, $height) as [$x, $y]) {
            $i = $y * $width + $x;
            if ($state[$i] === self::UNSEEN && self::distance(imagecolorat($image, $x, $y), $background) <= self::MATCH) {
                $state[$i] = self::BACKGROUND;
                $stack[] = $i;
            }
        }

        $removed = 0;
        $edges = [];
        while ($stack) {
            $i = array_pop($stack);
            $removed++;
            $x = $i % $width;
            $y = intdiv($i, $width);
            foreach ([[$x - 1, $y], [$x + 1, $y], [$x, $y - 1], [$x, $y + 1]] as [$nx, $ny]) {
                if ($nx < 0 || $ny < 0 || $nx >= $width || $ny >= $height) {
                    continue;
                }
                $n = $ny * $width + $nx;
                if ($state[$n] !== self::UNSEEN) {
                    continue;
                }
                $distance = self::distance(imagecolorat($image, $nx, $ny), $background);
                if ($distance <= self::MATCH) {
                    $state[$n] = self::BACKGROUND;
                    $stack[] = $n;
                } else {
                    $state[$n] = self::CHECKED;
                    if ($distance < self::FEATHER) {
                        $edges[$n] = $distance;
                    }
                }
            }
        }

        if ($removed === 0 || $removed > self::MAX_REMOVED_SHARE * $width * $height
            || self::enclosedShare($state, $width, $height, $removed) > self::MAX_ENCLOSED_SHARE) {
            return false;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        for ($i = strpos($state, self::BACKGROUND); $i !== false; $i = strpos($state, self::BACKGROUND, $i + 1)) {
            imagesetpixel($image, $i % $width, intdiv($i, $width), $transparent);
        }
        foreach ($edges as $i => $distance) {
            self::softenEdge($image, $i % $width, intdiv($i, $width), $distance, $background);
        }

        return true;
    }

    // The light colour most of the border shares, or null when the border isn't a plain light background
    private static function borderBackground(\GdImage $image, int $width, int $height): ?array
    {
        $points = self::borderPoints($width, $height);
        $light = [];
        $transparent = 0;
        foreach ($points as [$x, $y]) {
            $color = imagecolorat($image, $x, $y);
            if ((($color >> 24) & 0x7F) > 100) {
                $transparent++;
                continue;
            }
            $rgb = [($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF];
            if (min($rgb) >= self::LIGHT) {
                $light[] = $rgb;
            }
        }

        $total = count($points);
        if ($transparent > self::TRANSPARENT_BORDER_SHARE * $total || count($light) < self::MIN_BORDER_SHARE * $total) {
            return null;
        }

        // The median of each channel ignores a few product pixels touching the border
        $background = [];
        foreach ([0, 1, 2] as $channel) {
            $values = array_column($light, $channel);
            sort($values);
            $background[] = $values[intdiv(count($values), 2)];
        }

        $matching = 0;
        foreach ($light as $rgb) {
            if (max(abs($rgb[0] - $background[0]), abs($rgb[1] - $background[1]), abs($rgb[2] - $background[2])) <= self::MATCH) {
                $matching++;
            }
        }

        return $matching >= self::MIN_BORDER_SHARE * $total ? $background : null;
    }

    // Share of the product's pixels that would be removed pixels lying inside the product's outline
    // (between product pixels in their row and in their column)
    private static function enclosedShare(string $state, int $width, int $height, int $removed): float
    {
        $left = array_fill(0, $height, $width);
        $right = array_fill(0, $height, -1);
        $top = array_fill(0, $width, $height);
        $bottom = array_fill(0, $width, -1);
        for ($y = 0, $i = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++, $i++) {
                if ($state[$i] !== self::BACKGROUND) {
                    $left[$y] = min($left[$y], $x);
                    $right[$y] = max($right[$y], $x);
                    $top[$x] = min($top[$x], $y);
                    $bottom[$x] = max($bottom[$x], $y);
                }
            }
        }

        $enclosed = 0;
        for ($i = strpos($state, self::BACKGROUND); $i !== false; $i = strpos($state, self::BACKGROUND, $i + 1)) {
            $x = $i % $width;
            $y = intdiv($i, $width);
            if ($x > $left[$y] && $x < $right[$y] && $y > $top[$x] && $y < $bottom[$x]) {
                $enclosed++;
            }
        }

        return $enclosed / max(1, $width * $height - $removed);
    }

    /** @return array<int, array{int, int}> every pixel on the four edges, corners once */
    private static function borderPoints(int $width, int $height): array
    {
        $points = [];
        for ($x = 0; $x < $width; $x++) {
            $points[] = [$x, 0];
            $points[] = [$x, $height - 1];
        }
        for ($y = 1; $y < $height - 1; $y++) {
            $points[] = [0, $y];
            $points[] = [$width - 1, $y];
        }

        return $points;
    }

    private static function distance(int $color, array $background): int
    {
        return max(
            abs((($color >> 16) & 0xFF) - $background[0]),
            abs((($color >> 8) & 0xFF) - $background[1]),
            abs(($color & 0xFF) - $background[2]),
        );
    }

    // An edge pixel is part product, part background: make it partly transparent and take the
    // background's share out of its colour, so no white halo is left around the product
    private static function softenEdge(\GdImage $image, int $x, int $y, int $distance, array $background): void
    {
        $opacity = ($distance - self::MATCH) / (self::FEATHER - self::MATCH);
        $color = imagecolorat($image, $x, $y);
        $rgb = [($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF];
        foreach ($rgb as $channel => $value) {
            $rgb[$channel] = (int) max(0, min(255, round(($value - (1 - $opacity) * $background[$channel]) / $opacity)));
        }
        $alpha = (int) round(127 * (1 - $opacity));
        imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $rgb[0], $rgb[1], $rgb[2], $alpha));
    }
}
