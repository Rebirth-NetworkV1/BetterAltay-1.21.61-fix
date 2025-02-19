<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

class SkinAnimation {

    public const TYPE_HEAD = 1;
    public const TYPE_BODY_32 = 2;
    public const TYPE_BODY_64 = 3;

    public const EXPRESSION_LINEAR = 0; //???
    public const EXPRESSION_BLINKING = 1;

    /** @var SkinImage */
    private $image;
    /** @var int */
    private $type;
    /** @var float */
    private $frames;
    /** @var int */
    private $expressionType;

    public function __construct(SkinImage $image, int $type, float $frames, int $expressionType) {
        // Validate and assign type
        if ($type < 1 || $type > 3) {
            error_log("Invalid animation type provided: $type, defaulting to TYPE_HEAD");
            $type = self::TYPE_HEAD; // Default to TYPE_HEAD
        }
        
        // Validate and assign expression type
        if ($expressionType < 0 || $expressionType > 1) {
            error_log("Invalid expression type provided: $expressionType, defaulting to EXPRESSION_LINEAR");
            $expressionType = self::EXPRESSION_LINEAR; // Default to EXPRESSION_LINEAR
        }

        $this->image = $image;
        $this->type = $type;
        $this->frames = $frames;
        $this->expressionType = $expressionType;
    }

    /**
     * Image of the animation.
     */
    public function getImage(): SkinImage {
        return $this->image;
    }

    /**
     * The type of animation you are applying.
     */
    public function getType(): int {
        return $this->type;
    }

    /**
     * The total amount of frames in an animation.
     */
    public function getFrames(): float {
        return $this->frames;
    }

    public function getExpressionType(): int {
        return $this->expressionType;
    }
}
