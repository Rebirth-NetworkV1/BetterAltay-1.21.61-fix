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

		$expected = $this->height * $this->width * 4;
		$actual = strlen($data);

		if ($actual !== $expected) {
			$this->data = str_repeat("\x00", $expected); // Transparent blank skin
		} else {
			$this->data = $data;
		}
	}

	public static function fromLegacy(string $data): SkinImage {
		$sizes = [
			64 * 32 * 4 => [32, 64],
			64 * 64 * 4 => [64, 64],
			128 * 128 * 4 => [128, 128],
			256 * 128 * 4 => [128, 256],
			256 * 256 * 4 => [256, 256],
		];

		$size = strlen($data);
		if (isset($sizes[$size])) {
			[$height, $width] = $sizes[$size];
			return new self($height, $width, $data);
		}

		// Default to a 64x64 blank skin if data is invalid
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
