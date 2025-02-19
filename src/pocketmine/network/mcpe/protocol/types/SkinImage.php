<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use InvalidArgumentException;
use function strlen;
use function str_repeat;

class SkinImage {

    private int $height;
    private int $width;
    private string $data;

    public function __construct(int $height, int $width, string $data) {
        // If dimensions are invalid, substitute with a default 64x64 skin.
        if ($height <= 0 || $width <= 0) {
            $height = 64;
            $width = 64;
            $data = str_repeat("\x00", $height * $width * 4);
        }
        
        $expectedSize = $height * $width * 4;
        $actualSize = strlen($data);

        // If the data size does not match the expected size, substitute with default skin.
        if ($actualSize !== $expectedSize) {
            $height = 64;
            $width = 64;
            $data = str_repeat("\x00", $height * $width * 4);
        }

        $this->height = $height;
        $this->width = $width;
        $this->data = $data;
    }

    public static function fromLegacy(string $data): SkinImage {
        switch(strlen($data)){
            case 64 * 32 * 4:
                return new self(32, 64, $data);
            case 64 * 64 * 4:
                return new self(64, 64, $data);
            case 128 * 128 * 4:
                return new self(128, 128, $data);
            case 256 * 128 * 4:
                return new self(128, 256, $data);
            case 256 * 256 * 4:
                return new self(256, 256, $data);
            default:
                // Fallback: return a valid 64x64 blank skin.
                return new self(64, 64, str_repeat("\x00", 64 * 64 * 4));
        }
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
