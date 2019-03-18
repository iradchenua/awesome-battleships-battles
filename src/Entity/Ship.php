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
abstract class Ship
{
    protected const CLASS_NAME = "Ship";
    protected const COLOR = "none";
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
    private $id;

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
    protected $dirX;

    /**
     * @var int
     *
     * @ORM\Column(name="dir_y", type="integer", nullable=false)
     */
    protected $dirY;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_activated", type="boolean", nullable=false)
     */
    protected $isActivated;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_stationery", type="boolean", nullable=false)
     */
    protected $isStationery;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_turn", type="boolean", nullable=false)
     */
    protected $canTurn;

    /**
     * @var int
     *
     * @ORM\Column(name="phase", type="integer", nullable=false)
     */
    protected $phase;

    /**
     * @var int
     *
     * @ORM\Column(name="moved", type="integer", nullable=false)
     */
    protected $moved;

    /**
     * @var int
     *
     * @ORM\Column(name="moved_from_last_turn", type="integer", nullable=false)
     */
    protected $movedFromLastTurn;

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getColor()
    {
        return static::COLOR;
    }

    public function getWidth()
    {
        return static::WIDTH;
    }

    public function getHeight()
    {
        return static::HEIGHT;
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
        if (isset($params['id']))
            $this->id = $params['id'];

        $this->gameId = $params['gameId'];
        $this->userId = $params['userId'];

        $this->x = $params['x'];
        $this->y = $params['y'];
        $this->isStationery = $params['isStationery'];
        $this->moved = $params['moved'];
        $this->setCanTurn($params['canTurn']);
        $this->setMovedFromLastTurn($params['movedFromLastTurn']);
        if (isset($params['dirX']) && isset($params['dirY'])) {
            $this->dirX = $params['dirX'];
            $this->dirY = $params['dirY'];
        }
        else
        {
            $this->dirX = 1;
            $this->dirY = 0;
        }

        $this->name = static::CLASS_NAME;
        $this->isActivated = $params['isActivated'];
        if (isset($params['phase']))
            $this->phase = $params['phase'];
        else
            $this->phase = self::MOVEMENT_PHASE;
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
        if ($this->phase < self::SHOOT_PHASE)
            $this->phase += 1;
        else
        {
            $this->endShipTurn();
        }
    }
    public function rotate($where)
    {
        if (!$this->getCanTurn())
            return ;

        $this->setMovedFromLastTurn(0);
        $this->setCanTurn(false);
        $this->setIsStationery(false);

        $oldDirX = $this->getDirX();
        $oldDirY = $this->getDirY();

        $newDirX = $oldDirY;
        $newDirY = -$oldDirX;
        if ($where == 'left')
        {
            $newDirX = -$newDirX;
            $newDirY = -$newDirY;
        }

        $this->setDirX($newDirX);
        $this->setDirY($newDirY);
        $shift = ($this->getWidth() - $this->getHeight()) / 2;
        if ($oldDirX == 0)
            $shift = -$shift;

        $this->setX($this->getX() + $shift );
        $this->setY($this->getY() + $shift );

    }
    private function canMoveOnThisNumberOfCeils($numberOfCeils)
    {

        if ($numberOfCeils <= 0 ||
            $numberOfCeils + $this->getMoved() > $this->getSpeed()) {
            return false;
        }

        if ($this->getIsStationery()) {
            return true;
        }

        if ($numberOfCeils < $this->getHandling()) {
            return false;
        }

        return true;
    }
    public function move($numberOfCeils)
    {
        if (!$this->canMoveOnThisNumberOfCeils($numberOfCeils)) {
            return;
        }

        $this->setIsStationery(false);
        $this->setCanTurn(false);

        $this->setCanTurn(false);
        $this->setX($this->getX() + $numberOfCeils * $this->getDirX());
        $this->setY($this->getY() + $numberOfCeils * $this->getDirY());
        $this->setMoved($this->getMoved() + $numberOfCeils);
        $this->setMovedFromLastTurn($this->getMovedFromLastTurn() + $numberOfCeils);

        if ($this->getMovedFromLastTurn() >= $this->getHandling()) {
            $this->setCanTurn(true);
        }
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
            'moved' => $this->moved,
            'movedFromLastTurn' => $this->getMovedFromLastTurn(),
            'isStationery' => $this->isStationery,
            'canTurn' => $this->getCanTurn(),
            'name' => static::CLASS_NAME,
            'isActivated' => $this->isActivated,
            'phase' => $this->phase,
        ]);
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(int $x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function setY(int $y): self
    {
        $this->y = $y;

        return $this;
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
        return static::SPEED;
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

    public function getMoved(): ?int
    {
        return $this->moved;
    }

    public function setMoved(int $moved): self
    {
        $this->moved = $moved;

        return $this;
    }

    public function endShipTurn()
    {
        $moved = $this->getMoved();
        $handling = $this->getHandling();

        if ($moved == $this->getSpeed() || $moved <= $handling) {
            $this->setIsActivated(true);
            $this->setPhase(Ship::MOVEMENT_PHASE);
            $isStationery = ($this->getMoved() == $this->getHandling());
            $this->setMoved(0);
            $this->setMovedFromLastTurn(0);
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

    public function getMovedFromLastTurn(): ?int
    {
        return $this->movedFromLastTurn;
    }

    public function setMovedFromLastTurn(int $movedFromLastTurn): self
    {
        $this->movedFromLastTurn = $movedFromLastTurn;

        return $this;
    }
}