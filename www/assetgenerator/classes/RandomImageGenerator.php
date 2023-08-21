<?php

declare(strict_types=1);

class RandomImageGenerator
{
    private ImageConfig $config;

    /**
     * @var resource
     */
    private $image;

    public function __construct(ImageConfig $imageConfig)
    {
        $this->config = $imageConfig;
        $this->generateImageData();
    }

    /**
     * @return resource
     */
    public static function create(ImageConfig $imageConfig)
    {
        return (new self($imageConfig))->getImageData();
    }

    /**
     * @return resource
     */
    public function getImageData()
    {
        return $this->image;
    }

    private function generateImageData(): void
    {
        $width = $this->config->getImageWidth();
        $height = $this->config->getImageHeight();
        $text = $this->config->getText();
        $squareSize = rand(1, 3) * 10 + \strlen($text);
        $canvas = imagecreatetruecolor($width, $height);
        if ($canvas === false) {
            throw new RuntimeException('Could not create image');
        }

        for ($y = 0; $y < $height / $squareSize; ++$y) {
            for ($x = 0; $x < $width / $squareSize; ++$x) {
                $color = imagecolorallocate($canvas, rand(0, 255), rand(0, 255), rand(0, 255));
                if (!\is_int($color)) {
                    throw new RuntimeException('Could not create color');
                }
                imagefilledrectangle($canvas, $x * $squareSize, $y * $squareSize, ($x * $squareSize) + $squareSize, ($y * $squareSize) + $squareSize, $color);
            }
        }

        $font = 'fonts/OpenSans-Regular.ttf';
        $black = imagecolorallocate($canvas, 0, 0, 0);
        if (!\is_int($black)) {
            throw new RuntimeException('Could not create black color');
        }
        $white = imagecolorallocate($canvas, 255, 255, 255);
        if (!\is_int($white)) {
            throw new RuntimeException('Could not create white color');
        }
        $font_size = 72;
        $boundingBox = $this->imagettfbboxextended($font_size + 3, $font, $text);
        while ($boundingBox['width'] > $width || $boundingBox['height'] > $height) {
            --$font_size;
            $boundingBox = $this->imagettfbboxextended($font_size, $font, $text);
        }

        $this->imagettfstroketext($canvas, $font_size, $boundingBox['x'], $boundingBox['y'], $white, $black, $font, $text);
        $this->image = $canvas;
    }

    /**
     * @param resource $image
     */
    private function imagettfstroketext(
        $image,
        int $size,
        int $x,
        int $y,
        int $textcolor,
        int $strokecolor,
        string $fontfile,
        string $text
    ): void {
        for ($c1 = $x - abs(2); $c1 <= ($x + abs(2)); ++$c1) {
            for ($c2 = $y - abs(2); $c2 <= ($y + abs(2)); ++$c2) {
                imagettftext($image, $size, 0, $c1, $c2, $strokecolor, $fontfile, $text);
            }
        }

        imagettftext($image, $size, 0, $x, $y, $textcolor, $fontfile, $text);
    }

    private function imagettfbboxextended(int $size, string $fontfile, string $text): array
    {
        $bbox = imagettfbbox($size, 0, $fontfile, $text);
        if (!\is_array($bbox)) {
            throw new RuntimeException('Could not create image box');
        }

        if ($bbox[0] >= -1) {
            $bbox['x'] = abs($bbox[0] + 1) * -1;
        } else {
            $bbox['x'] = abs($bbox[0] + 2);
        }

        $bbox['width'] = abs($bbox[2] - $bbox[0]);
        if ($bbox[0] < -1) {
            $bbox['width'] = abs($bbox[2]) + abs($bbox[0]) - 1;
        }

        $bbox['y'] = abs($bbox[5] + 1);

        $bbox['height'] = abs($bbox[7]) - abs($bbox[1]);
        if ($bbox[3] > 0) {
            $bbox['height'] = abs($bbox[7] - $bbox[1]) - 1;
        }

        return $bbox;
    }
}
