<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\UUID;

class SkinData {

    public const ARM_SIZE_SLIM = "slim";
    public const ARM_SIZE_WIDE = "wide";

    private string $skinId;
    private string $playFabId;
    private string $resourcePatch;
    private SkinImage $skinImage;
    private array $animations;
    private SkinImage $capeImage;
    private string $geometryData;
    private string $geometryDataEngineVersion;
    private string $animationData;
    private string $capeId;
    private string $fullSkinId;
    private string $armSize;
    private string $skinColor;
    private array $personaPieces;
    private array $pieceTintColors;
    private bool $isVerified;
    private bool $persona;
    private bool $premium;
    private bool $personaCapeOnClassic;
    private bool $isPrimaryUser;
    private bool $override;

    /**
     * @param SkinAnimation[]         $animations
     * @param PersonaSkinPiece[]      $personaPieces
     * @param PersonaPieceTintColor[] $pieceTintColors
     */
    public function __construct(
        string $skinId,
        string $playFabId,
        string $resourcePatch,
        SkinImage $skinImage,
        array $animations = [],
        ?SkinImage $capeImage = null,
        string $geometryData = "",
        string $geometryDataEngineVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK,
        string $animationData = "",
        string $capeId = "",
        ?string $fullSkinId = null,
        string $armSize = self::ARM_SIZE_WIDE,
        string $skinColor = "",
        array $personaPieces = [],
        array $pieceTintColors = [],
        bool $isVerified = true,
        bool $premium = false,
        bool $persona = false,
        bool $personaCapeOnClassic = false,
        bool $isPrimaryUser = true,
        bool $override = true
    ) {
        $this->skinId = $this->sanitizeString($skinId);
        $this->playFabId = $this->sanitizeString($playFabId);
        $this->resourcePatch = $this->sanitizeString($resourcePatch);
        $this->skinImage = clone $skinImage;
        $this->animations = $animations;
        $this->capeImage = $capeImage ? clone $capeImage : new SkinImage(0, 0, "");

        if (!is_string($geometryData)) {
            throw new InvalidArgumentException("Geometry data must be a string.");
        }
        $this->geometryData = $this->sanitizeString($geometryData);
        $this->geometryDataEngineVersion = $this->sanitizeString($geometryDataEngineVersion);
        $this->animationData = $this->sanitizeString($animationData);
        $this->capeId = $this->sanitizeString($capeId);
        $this->fullSkinId = $fullSkinId ? $this->sanitizeString($fullSkinId) : UUID::fromRandom()->toString();
        $this->armSize = in_array($armSize, [self::ARM_SIZE_SLIM, self::ARM_SIZE_WIDE], true) ? $armSize : self::ARM_SIZE_WIDE;
        $this->skinColor = $this->sanitizeString($skinColor);
        $this->personaPieces = $personaPieces;
        $this->pieceTintColors = $pieceTintColors;
        $this->isVerified = $isVerified;
        $this->premium = $premium;
        $this->persona = $persona;
        $this->personaCapeOnClassic = $personaCapeOnClassic;
        $this->isPrimaryUser = $isPrimaryUser;
        $this->override = $override;
    }

    private function sanitizeString(string $input): string {
    return trim(filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS));
}

    public function getSkinId(): string {
        return $this->skinId;
    }

    public function getPlayFabId(): string {
        return $this->playFabId;
    }

    public function getResourcePatch(): string {
        return $this->resourcePatch;
    }

    public function getSkinImage(): SkinImage {
        return clone $this->skinImage;
    }

    /**
     * @return SkinAnimation[]
     */
    public function getAnimations(): array {
        return $this->animations;
    }

    public function getCapeImage(): SkinImage {
        return clone $this->capeImage;
    }

    public function getGeometryData(): string {
        return $this->geometryData;
    }

    public function getGeometryDataEngineVersion(): string {
        return $this->geometryDataEngineVersion;
    }

    public function getAnimationData(): string {
        return $this->animationData;
    }

    public function getCapeId(): string {
        return $this->capeId;
    }

    public function getFullSkinId(): string {
        return $this->fullSkinId;
    }

    public function getArmSize(): string {
        return $this->armSize;
    }

    public function getSkinColor(): string {
        return $this->skinColor;
    }

    /**
     * @return PersonaSkinPiece[]
     */
    public function getPersonaPieces(): array {
        return $this->personaPieces;
    }

    /**
     * @return PersonaPieceTintColor[]
     */
    public function getPieceTintColors(): array {
        return $this->pieceTintColors;
    }

    public function isPersona(): bool {
        return $this->persona;
    }

    public function isPremium(): bool {
        return $this->premium;
    }

    public function isPersonaCapeOnClassic(): bool {
        return $this->personaCapeOnClassic;
    }

    public function isPrimaryUser(): bool {
        return $this->isPrimaryUser;
    }

    public function isOverride(): bool {
        return $this->override;
    }

    public function isVerified(): bool {
        return $this->isVerified;
    }

    /**
     * @internal
     */
    public function setVerified(bool $verified): void {
        $this->isVerified = $verified;
    }
}
