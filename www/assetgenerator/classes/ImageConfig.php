<?php

declare(strict_types=1);

class ImageConfig
{
    private int $imageWidth = 640;

    private int $imageHeight = 480;

    private int $maxLineWidth = 15;

    private int $colorDeviation = 20;

    private string $text = '';

    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    public function setImageWidth(int $imageWidth): void
    {
        $this->imageWidth = $imageWidth;
    }

    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

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
