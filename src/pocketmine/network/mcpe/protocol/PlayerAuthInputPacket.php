<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use InvalidArgumentException;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayMode;
use function assert;

class PlayerAuthInputPacket extends DataPacket/* implements ServerboundPacket*/
{
    public const NETWORK_ID = ProtocolInfo::PLAYER_AUTH_INPUT_PACKET;

    /** @var Vector3 */
    private $position;
    /** @var float */
    private $pitch;
    /** @var float */
    private $yaw;
    /** @var float */
    private $headYaw;
    /** @var float */
    private $moveVecX;
    /** @var float */
    private $moveVecZ;
    /** @var int */
    private $inputFlags;
    /** @var int */
    private $inputMode;
    /** @var int */
    private $playMode;
    private int $interactionMode;
    /** @var Vector3|null */
    private $vrGazeDirection = null;
    /** @var int */
    private $tick;
    /** @var Vector3 */
    private $delta;

    /**
     * @param int          $inputFlags @see InputFlags
     * @param int          $inputMode @see InputMode
     * @param int          $playMode @see PlayMode
     * @param Vector3|null $vrGazeDirection only used when PlayMode::VR
     */
    public static function create(Vector3 $position, float $pitch, float $yaw, float $headYaw, float $moveVecX, float $moveVecZ, int $inputFlags, int $inputMode, int $playMode, int $interactionMode, ?Vector3 $vrGazeDirection, int $tick, Vector3 $delta) : self {
        if ($playMode === PlayMode::VR && $vrGazeDirection === null) {
            throw new InvalidArgumentException("Gaze direction must be provided for VR play mode");
        }
        $result = new self;
        $result->position = $position->asVector3();
        $result->pitch = $pitch;
        $result->yaw = $yaw;
        $result->headYaw = $headYaw;
        $result->moveVecX = $moveVecX;
        $result->moveVecZ = $moveVecZ;
        $result->inputFlags = $inputFlags;
        $result->inputMode = $inputMode;
        $result->playMode = $playMode;
        $result->interactionMode = $interactionMode;
        if ($vrGazeDirection !== null) {
            $result->vrGazeDirection = $vrGazeDirection->asVector3();
        }
        $result->tick = $tick;
        $result->delta = $delta;
        return $result;
    }

    public function getPosition() : Vector3 {
        return $this->position;
    }

    public function getPitch() : float {
        return $this->pitch;
    }

    public function getYaw() : float {
        return $this->yaw;
    }

    public function getHeadYaw() : float {
        return $this->headYaw;
    }

    public function getMoveVecX() : float {
        return $this->moveVecX;
    }

    public function getMoveVecZ() : float {
        return $this->moveVecZ;
    }

    public function getInputFlags() : int {
        return $this->inputFlags;
    }

    public function getInputMode() : int {
        return $this->inputMode;
    }

    public function getPlayMode() : int {
        return $this->playMode;
    }

    public function getInteractionMode() : int {
        return $this->interactionMode;
    }

    public function getVrGazeDirection() : ?Vector3 {
        return $this->vrGazeDirection;
    }

    public function getTick() : int {
        return $this->tick;
    }

    public function getDelta() : Vector3 {
        return $this->delta;
    }

    protected function decodePayload() : void {
        $this->pitch = $this->getLFloat();
        $this->yaw = $this->getLFloat();
        $this->position = $this->getVector3();
        $this->moveVecX = $this->getLFloat();
        $this->moveVecZ = $this->getLFloat();
        $this->headYaw = $this->getLFloat();
        $this->inputFlags = $this->getUnsignedVarLong();
        $this->inputMode = $this->getUnsignedVarInt();
        $this->playMode = $this->getUnsignedVarInt();
        $this->interactionMode = $this->getUnsignedVarInt();
        if ($this->playMode === PlayMode::VR) {
            $this->vrGazeDirection = $this->getVector3();
        }
        $this->tick = $this->getUnsignedVarLong();
        $this->delta = $this->getVector3();

        // Ensure the tick is a non-negative value
        if ($this->tick < 0) {
            $this->tick = 0; // Reset to 0 to avoid any invalid behavior
        }

        // Clamp movement vectors to avoid excessive values
        if (abs($this->moveVecX) > 10 || abs($this->moveVecZ) > 10) {
            $this->moveVecX = 0;
            $this->moveVecZ = 0; // Prevent unrealistic movement
        }

        // Validate position to prevent potential teleportation exploits
        if (abs($this->position->x) > 30000000 || abs($this->position->y) > 30000000 || abs($this->position->z) > 30000000) {
            $this->position = new Vector3(0, 0, 0); // Reset position to a safe default
        }
    }

    protected function encodePayload() : void {
        $this->putLFloat($this->pitch);
        $this->putLFloat($this->yaw);
        $this->putVector3($this->position);
        $this->putLFloat($this->moveVecX);
        $this->putLFloat($this->moveVecZ);
        $this->putLFloat($this->headYaw);
        $this->putUnsignedVarLong($this->inputFlags);
        $this->putUnsignedVarInt($this->inputMode);
        $this->putUnsignedVarInt($this->playMode);
        $this->putUnsignedVarInt($this->interactionMode);
        if ($this->playMode === PlayMode::VR) {
            assert($this->vrGazeDirection !== null);
            $this->putVector3($this->vrGazeDirection);
        }
        $this->putUnsignedVarLong($this->tick);
        $this->putVector3($this->delta);
    }

    public function handle(NetworkSession $session) : bool {
        return $session->handlePlayerAuthInput($this);
    }
}
