<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 07.03.2019
 * Time: 15:04
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ship
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="ship")
 * @ORM\DiscriminatorColumn(name="name", type = "string", length=255)
 * @ORM\Entity(repositoryClass="App\Repository\ShipRepository")
 */
abstract class Ship extends EntityOnMap
{
    protected const CLASS_NAME = "Ship";
    protected const IMG = "none";
    protected const WIDTH = 0;
    protected const HEIGHT = 0;
    protected const HANDLING = 0;
    protected const SPEED = 0;
    protected const PP = 0;
    protected const ENGINE_POWER = "none";

    public const    ORDER_PHASE = 0;
    public const    MOVEMENT_PHASE = 1;
    public const    SHOOT_PHASE = 2;

    private $phaseName = [
        self::ORDER_PHASE => 'Order',
        self::MOVEMENT_PHASE => 'Move',
        self::SHOOT_PHASE => 'Shoot',
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="game_id", type="integer", nullable=false)
     */
    protected $gameId;

    /**
     * @var int
     *
     * @ORM\Column(name="x", type="integer", nullable=false)
     */
    protected $x;

    /**
     * @var int
     *
     * @ORM\Column(name="y", type="integer", nullable=false)
     */
    protected $y;

    /**
     * @var int
     *
     * @ORM\Column(name="dir_x", type="integer", nullable=false)
     */
    protected $dirX = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="dir_y", type="integer", nullable=false)
     */
    protected $dirY = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_activated", type="boolean", nullable=false)
     */
    protected $isActivated = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_stationery", type="boolean", nullable=false)
     */
    protected $isStationery = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_turn", type="boolean", nullable=false)
     */
    protected $canTurn = true;

    /**
     * @var int
     *
     * @ORM\Column(name="phase", type="integer", nullable=false)
     */
    protected $phase = self::ORDER_PHASE;

    /**
     * @var int
     *
     * @ORM\Column(name="moved", type="integer", nullable=false)
     */
    protected $moved = 0;
    /**
     * @var int
     *
     * @ORM\Column(name="speed", type="integer", nullable=false)
     */
    protected $speed;
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_live", type="boolean", nullable=false)
     */
    protected $isLive = true;

    public function getName(): ?string
    {
        return static::CLASS_NAME;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getGameId(): ?int
    {
        return $this->gameId;
    }

    public function setGameId(int $gameId): self
    {
        $this->gameId = $gameId;

        return $this;
    }

    public function getClassName()
    {
        return static::CLASS_NAME;
    }

    public function getEnginePower()
    {
        return static::ENGINE_POWER;
    }

    public function getIsActivated(): ?bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): self
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    public function __construct(array $params)
    {

        $this->setSpeed(static::SPEED);

        if (isset($params['id'])) {
            $this->setId($params['id']);
            $this->setIsStationery($params['isStationery']);
            $this->setCanTurn($params['canTurn']);
            $this->setMoved($params['moved']);
            $this->setSpeed($params['speed']);
            $this->setPhase($params['phase']);
            $this->setIsActivated($params['isActivated']);
            $this->setSpeed($params['speed']);
            $this->setIsLive($params['isLive']);
        }

        $this->setGameId($params['gameId']);
        $this->setUserId($params['userId']);

        $this->setX($params['x']);
        $this->setY($params['y']);
        $this->setDirX($params['dirX']);
        $this->setDirY($params['dirY']);

        $this->setName(static::CLASS_NAME);
    }

    public function getPhase(): ?int
    {
        return $this->phase;
    }

    public function setPhase(int $phase): self
    {
        $this->phase = $phase;

        return $this;
    }
    public function incrementPhase()
    {
        if ($this->phase < self::SHOOT_PHASE) {
            $this->phase += 1;
        } else {
            $this->endShipTurn();
        }
    }
    public function rotate($where)
    {
        if (!$this->getCanTurn()) {
            return;
        }

        $this->setCanTurn(false);
        $oldDirX = $this->getDirX();
        $oldDirY = $this->getDirY();

        $newDirX = $oldDirY;
        $newDirY = -$oldDirX;
        if ($where == 'left') {
            $newDirX = -$newDirX;
            $newDirY = -$newDirY;
        }

        $this->setDirX($newDirX);
        $this->setDirY($newDirY);
    }
    private function canMoveOnThisNumberOfCeils($numberOfCeils)
    {

        if ($this->getMoved() > 0)
            return false;

        if ($numberOfCeils <= 0) {
            return false;
        }

        if ($numberOfCeils == $this->getHandling()) {
            return true;
        }

        if ($this->getIsStationery() && $numberOfCeils <= $this->getHandling()) {
            return true;
        }

        return $numberOfCeils == $this->getSpeed();
    }

    private function checkXOut($x) {
        return $x <  (-Game::GAME_FIELD_WIDTH / 2) || $x >  Game::GAME_FIELD_WIDTH / 2;
    }

    private function checkYOut($y) {
        return $y < (-Game::GAME_FIELD_HEIGHT / 2) || $y > Game::GAME_FIELD_HEIGHT / 2;
    }
    public function getRect() {
        $rect = parent::getRect();

        $shift = ($rect['width'] - $rect['height']) / 2;

        if ($this->dirY === 1) {
            $rect['x'] += $shift;
            $rect['y'] += $shift;

        } else if ($this->dirY === -1) {
            $rect['x'] -= $shift;
            $rect['y'] -= $shift;
        }

        if ($this->dirX === 0) {
            $w = $rect['width'];
            $rect['width'] = $rect['height'];
            $rect['height'] = $w;
        }

        return $rect;
    }
    private function checkOutOfBounds()
    {
        $x = $this->getX();
        $y = $this->getY();
        $width = $this->getWidth();
        $height = $this->getHeight();

        $shift = ($width - $height) / 2;

        $oppositeX = $x + $this->getWidth();
        $oppositeY = $y - $this->getHeight();

        if ($this->dirY === 1) {
            $x += $shift;
            $y += $shift;
            $oppositeX += $shift;
            $oppositeY += $shift;

        } else if ($this->dirY === -1) {
            $x -= $shift;
            $y -= $shift;
            $oppositeX += $shift;
            $oppositeY += $shift;
        }

        if ($this->checkXOut($x) || $this->checkYOut($y)
        || $this->checkXOut($oppositeX) || $this->checkYOut($oppositeY)) {
            return true;
        }
        return false;
    }

    public function checkDead($obstacles)
    {
        if ($this->checkOutOfBounds() || $this->intersectWithEntities($obstacles) != false) {
            $this->setIsLive(false);
        }
    }
    public function move($numberOfCeils)
    {
        if (!$this->canMoveOnThisNumberOfCeils($numberOfCeils)) {
            return false;
        }

        $this->setIsStationery(false);
        $this->setCanTurn(false);

        $this->setX($this->getX() + $numberOfCeils * $this->getDirX());
        $this->setY($this->getY() + $numberOfCeils * $this->getDirY());
        $this->setMoved($numberOfCeils);
        if ($this->getMoved() >= $this->getHandling()) {
            $this->setCanTurn(true);
        }
        return true;
    }
    public function getAll(): array
    {
        return ([
            'id' => $this->id,
            'gameId' => $this->gameId,
            'userId' => $this->userId,
            'x' => $this->x,
            'y' => $this->y,
            'dirX' => $this->dirX,
            'dirY' => $this->dirY,
            'moved' => $this->getMoved(),
            'speed' => $this->getSpeed(),
            'isStationery' => $this->isStationery,
            'isLive' => $this->getIsLive(),
            'canTurn' => $this->getCanTurn(),
            'name' => static::CLASS_NAME,
            'isActivated' => $this->isActivated,
            'phase' => $this->phase,
        ]);
    }

    public function getDirX(): ?int
    {
        return $this->dirX;
    }

    public function setDirX(int $dirX): self
    {
        $this->dirX = $dirX;

        return $this;
    }

    public function getDirY(): ?int
    {
        return $this->dirY;
    }

    public function setDirY(int $dirY): self
    {
        $this->dirY = $dirY;

        return $this;
    }

    public function getPhaseName()
    {
        return $this->phaseName[$this->getPhase()];
    }
    public function getSpeed()
    {
        return $this->speed;
    }
    public function getHandling()
    {
        return static::HANDLING;
    }

    public function getIsStationery(): ?bool
    {
        return $this->isStationery;
    }

    public function setIsStationery(bool $isStationery): self
    {
        $this->isStationery = $isStationery;

        return $this;
    }

    public function endShipTurn()
    {
        $this->setSpeed(static::SPEED);
        $this->setIsActivated(true);
        $this->setPhase(Ship::ORDER_PHASE);

        if ($this->getMoved() > 0) {
            $isStationery = $this->getIsStationery();
            $isStationery = $isStationery || ($this->getMoved() == $this->getHandling());
            $this->setMoved(0);
            $this->setIsStationery($isStationery);
            $this->setCanTurn($isStationery);
        }
    }

    public function getCanTurn(): ?bool
    {
        return $this->canTurn;
    }

    public function setCanTurn(bool $canTurn): self
    {
        $this->canTurn = $canTurn;

        return $this;
    }

    public function getMoved(): ?int
    {
        return $this->moved;
    }

    public function setMoved(int $moved): self
    {
        $this->moved = $moved;

        return $this;
    }
    public function getPP()
    {
        return static::PP;
    }

    public function setSpeed(int $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getIsLive(): ?bool
    {
        return $this->isLive;
    }

    public function setIsLive(bool $isLive): self
    {
        $this->isLive = $isLive;

        return $this;
    }
}