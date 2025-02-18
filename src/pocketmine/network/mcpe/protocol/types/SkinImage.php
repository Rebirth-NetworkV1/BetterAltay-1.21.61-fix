<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use InvalidArgumentException;
use function strlen;

class SkinImage {
    private int $height;
    private int $width;
    private string $data;

    public function __construct(int $height, int $width, string $data) {
        $this->height = max(1, $height);
        $this->width = max(1, $width);

        // Ensure correct data size
        $expected = $this->width * $this->height * 4;
        $actual = strlen($data);

        if ($actual === 0) {
            throw new InvalidArgumentException("Data cannot be empty. Expected: {$expected} bytes");
        }

        if ($actual !== $expected) {
            throw new InvalidArgumentException("Invalid skin/cape data size: {$actual} bytes (expected: {$expected} bytes)");
        }

        $this->data = $data;
    }

    public static function fromLegacy(string $data): SkinImage {
        $sizes = [
            64 * 32 * 4 => [64, 32],  // Cape size (8192 bytes)
            64 * 64 * 4 => [64, 64],
            128 * 128 * 4 => [128, 128],
            256 * 128 * 4 => [256, 128],
            256 * 256 * 4 => [256, 256],
        ];

        $size = strlen($data);
        if ($size === 0) {
            // Default to a valid 64x64 blank skin if the data is empty
            return new self(64, 64, str_repeat("\x00", 64 * 64 * 4));
        }

        if (isset($sizes[$size])) {
            [$width, $height] = $sizes[$size];
            return new self($height, $width, $data);
        }

        // Default to a valid 64x64 blank skin if the data is invalid
        return new self(64, 64, str_repeat("\x00", 64 * 64 * 4));
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
