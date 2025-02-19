<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use InvalidArgumentException;
use function is_string;
use function strlen;
use function str_repeat;

class SkinImage {

    private int $height;
    private int $width;
    private string $data;

    public function __construct(int $height, int $width, string $data) {
        if ($height <= 0 || $width <= 0) {
            throw new InvalidArgumentException("Invalid skin dimensions: {$width}x{$height}");
        }

        if (!is_string($data)) {
            throw new InvalidArgumentException("Skin data must be a string, got " . gettype($data));
        }

        $expectedSize = $height * $width * 4;
        $actualSize = strlen($data);

        if ($actualSize !== $expectedSize) {
            throw new InvalidArgumentException("Invalid skin data size: {$actualSize} bytes (expected: {$expectedSize} bytes)");
        }

        $this->height = $height;
        $this->width = $width;
        $this->data = $data;
    }

    public static function fromLegacy(string $data): SkinImage {
        $sizes = [
            64 * 32 * 4 => [64, 32],
            64 * 64 * 4 => [64, 64],
            128 * 128 * 4 => [128, 128],
            256 * 128 * 4 => [256, 128],
            256 * 256 * 4 => [256, 256],
        ];

        $size = strlen($data);

        if (isset($sizes[$size])) {
            [$width, $height] = $sizes[$size];
            return new self($height, $width, $data);
        }

        // Fallback for invalid sizes: return a blank 64x64 skin
        $width = 64;
        $height = 64;
        $data = str_repeat("\x00", $width * $height * 4);
        return new self($height, $width, $data);
    }

    public function getHeight(): int {
        return $this->height;
    }

    public function getWidth(): int {
        return $this->width;
    }

    public function getData(): string {
        return $this->data;
    }
}
