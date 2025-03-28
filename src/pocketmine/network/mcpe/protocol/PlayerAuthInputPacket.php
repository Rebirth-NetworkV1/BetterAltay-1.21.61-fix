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

class PlayerAuthInputPacket extends DataPacket
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

    private const JUMP_PACKET_LIMIT = 500;
    private const JUMP_PACKET_TIME_FRAME = 3; 
    private array $jumpPacketCount = [];
    private array $jumpPacketTimer = [];

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

        // Handling Jump Packet Limitations
        $session = /* Retrieve the NetworkSession */;
        $playerName = strtolower($session->getPlayer()->getName());
        $currentTime = time();

        // **Ignore Small Y Movements on Slabs**
        if ($this->delta->y > 0 && $this->delta->y <= 0.6) {
            return; // Slabs and stairs should NOT count as jumps
        }

        if ($this->inputFlags & PlayerAuthInputFlags::JUMP && $this->delta->y > 0.42) { 
            // Only count actual jumps, not stepping onto slabs
            if (!isset($this->jumpPacketCount[$playerName])) {
                $this->jumpPacketCount[$playerName] = 0;
                $this->jumpPacketTimer[$playerName] = $currentTime;
            }

            if ($currentTime - $this->jumpPacketTimer[$playerName] > self::JUMP_PACKET_TIME_FRAME) {
                $this->jumpPacketCount[$playerName] = 0;
                $this->jumpPacketTimer[$playerName] = $currentTime;
            }

            $this->jumpPacketCount[$playerName]++;

            if ($this->jumpPacketCount[$playerName] > self::JUMP_PACKET_LIMIT) {
                $session->getPlayer()->close("", "Bot detected (excessive jump packets)");
                return;
            }
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
