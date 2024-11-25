<?php

declare(strict_types=1);

class ImageConfig
{
    /**
     * @var int<1, max>
     */
    private int $imageWidth = 640;

    /**
     * @var int<1, max>
     */
    private int $imageHeight = 480;

    private int $maxLineWidth = 15;

    private int $colorDeviation = 20;

    private string $text = '';

    /**
     * @return int<1, max>
     */
    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    /**
     * @param int<1, max> $imageWidth
     */
    public function setImageWidth(int $imageWidth): void
    {
        $this->imageWidth = $imageWidth;
    }

    /**
     * @return int<1, max>
     */
    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    /**
     * @param int<1, max> $imageHeight
     */
    public function setImageHeight(int $imageHeight): void
    {
        $this->imageHeight = $imageHeight;
    }

    public function getMaxLineWidth(): int
    {
        return $this->maxLineWidth;
    }

    public function setMaxLineWidth(int $maxLineWidth): void
    {
        $this->maxLineWidth = $maxLineWidth;
    }

    public function getColorDeviation(): int
    {
        return $this->colorDeviation;
    }

    public function setColorDeviation(int $colorDeviation): void
    {
        $this->colorDeviation = $colorDeviation;
    }

    public function getSeed(): float
    {
        return (float) microtime() * 1000000;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
