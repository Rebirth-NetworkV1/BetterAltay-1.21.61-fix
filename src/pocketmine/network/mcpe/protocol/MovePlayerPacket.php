<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;

class MovePlayerPacket extends DataPacket {
    public const NETWORK_ID = ProtocolInfo::MOVE_PLAYER_PACKET;

    public const MODE_NORMAL = 0;
    public const MODE_RESET = 1;
    public const MODE_TELEPORT = 2;
    public const MODE_PITCH = 3;

    private const MAX_Y_CHANGE = 6.0; // Prevents teleport hacks
    private const MAX_XZ_CHANGE = 5.0; // Prevents speed hacks
    private const MAX_POSITION_VALUE = 30000000; // Prevents out-of-bounds exploits
    private const MOVEMENT_TIMEOUT = 10; 

    private static array $lastMovementTime = [];

    /** @var int */
    public $entityRuntimeId;
    /** @var Vector3 */
    public $position;
    /** @var float */
    public $pitch;
    /** @var float */
    public $yaw;
    /** @var float */
    public $headYaw;
    /** @var int */
    public $mode = self::MODE_NORMAL;
    /** @var bool */
    public $onGround = false;
    /** @var int */
    public $ridingEid = 0;
    /** @var int */
    public $teleportCause = 0;
    /** @var int */
    public $teleportItem = 0;
    /** @var int */
    public $tick = 0;

    protected function decodePayload() {
        $this->entityRuntimeId = $this->getEntityRuntimeId();
        $this->position = $this->getVector3();
        $this->pitch = $this->getLFloat();
        $this->yaw = $this->getLFloat();
        $this->headYaw = $this->getLFloat();
        $this->mode = $this->getByte();
        $this->onGround = $this->getBool();
        $this->ridingEid = $this->getEntityRuntimeId();
        if ($this->mode === self::MODE_TELEPORT) {
            $this->teleportCause = $this->getLInt();
            $this->teleportItem = $this->getLInt();
        }
        $this->tick = $this->getUnsignedVarLong();
    }

    protected function encodePayload() {
        $this->putEntityRuntimeId($this->entityRuntimeId);
        $this->putVector3($this->position);
        $this->putLFloat($this->pitch);
        $this->putLFloat($this->yaw);
        $this->putLFloat($this->headYaw);
        $this->putByte($this->mode);
        $this->putBool($this->onGround);
        $this->putEntityRuntimeId($this->ridingEid);
        if ($this->mode === self::MODE_TELEPORT) {
            $this->putLInt($this->teleportCause);
            $this->putLInt($this->teleportItem);
        }
        $this->putUnsignedVarLong($this->tick);
    }

    public function handle(NetworkSession $session) : bool {
        $this->validateMovement($session);
        return $session->handleMovePlayer($this);
    }

    private function validateMovement(NetworkSession $session): void {
        $player = $session->getPlayer();
        if (!$player) return;

        $playerName = strtolower($player->getName());
        $currentTime = time();

        // Prevent null or invalid positions
        if ($this->position === null || !($this->position instanceof Vector3)) {
            $player->close("", "Invalid movement data");
            return;
        }

        // Prevent out-of-bounds movement
        if (abs($this->position->x) > self::MAX_POSITION_VALUE || 
            abs($this->position->y) > self::MAX_POSITION_VALUE || 
            abs($this->position->z) > self::MAX_POSITION_VALUE) {
            $player->close("", "Invalid position data detected");
            return;
        }

        // Flood protection (anti-spam)
        if (isset(self::$lastMovementTime[$playerName])) {
            $lastMoveTime = self::$lastMovementTime[$playerName];
            if ($currentTime - $lastMoveTime < self::MOVEMENT_TIMEOUT) {
                $player->close("", "Flooding detected (too many movement packets)");
                return;
            }
        }
        self::$lastMovementTime[$playerName] = $currentTime;

        // Prevent extreme vertical movement
        if (abs($this->position->y - $player->getPosition()->y) > self::MAX_Y_CHANGE) {
            $player->close("", "Excessive vertical movement detected");
            return;
        }

        // Prevent extreme horizontal movement
        if (abs($this->position->x - $player->getPosition()->x) > self::MAX_XZ_CHANGE || 
            abs($this->position->z - $player->getPosition()->z) > self::MAX_XZ_CHANGE) {
            $player->close("", "Excessive horizontal movement detected");
            return;
        }
    }
}
