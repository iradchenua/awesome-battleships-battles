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
    protected const ENGINE_POWER = "none";
    public const    ORDER_PHASE = 0;
    public const    MOVEMENT_PHASE = 1;
    public const    SHOOT_PHASE = 2;

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
     * @ORM\Column(name="back_x", type="integer", nullable=false)
     */
    protected $backX;

    /**
     * @var int
     *
     * @ORM\Column(name="back_y", type="integer", nullable=false)
     */
    protected $backY;

    /**
     * @var int
     *
     * @ORM\Column(name="head_x", type="integer", nullable=false)
     */
    protected $headX;

    /**
     * @var int
     *
     * @ORM\Column(name="head_y", type="integer", nullable=false)
     */
    protected $headY;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_activated", type="boolean", nullable=false)
     */
    protected $isActivated;

    /**
     * @var int
     *
     * @ORM\Column(name="phase", type="integer", nullable=false)
     */
    protected $phase;

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
        return $this->headX - $this->backX;
    }

    public function getHeight()
    {
        return $this->headY - $this->backY;
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

        $this->backX = $params['backX'];
        $this->backY = $params['backY'];

        if (isset($params['headX']) && isset($params['headY'])) {
            $this->headX = $params['headX'];
            $this->headY = $params['headY'];
        }
        else
        {
            $this->headX = $this->backX + static::WIDTH;
            $this->headY = $this->backY + static::HEIGHT;
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
            $this->isActivated = true;
            $this->phase = self::MOVEMENT_PHASE;
        }
    }
    public function rotate()
    {

    }
    private static function takeDir($diff)
    {
        return $diff == 0 ? $diff : ($diff > 0 ? 1 : -1);
    }
    public function move()
    {
        $x =$this->headX - $this->backX;
        $y = $this->headY - $this->backY;

        $dir = $x > $y ? $x : $y;
        $dir = self::takeDir($dir);
        if ($x > $y)
            $this->backX += $dir;
        else
            $this->backY += $dir;
    }
    public function getAll(): array
    {
        return ([
            'id' => $this->id,
            'gameId' => $this->gameId,
            'userId' => $this->userId,
            'backX' => $this->backX,
            'backY' => $this->backY,
            'headX' => $this->headX,
            'headY' => $this->headY,
            'name' => static::CLASS_NAME,
            'isActivated' => $this->isActivated,
            'phase' => $this->phase,
        ]);
    }

    public function getBackX(): ?int
    {
        return $this->backX;
    }

    public function setBackX(int $backX): self
    {
        $this->backX = $backX;

        return $this;
    }

    public function getBackY(): ?int
    {
        return $this->backY;
    }

    public function setBackY(int $backY): self
    {
        $this->backY = $backY;

        return $this;
    }

    public function getHeadX(): ?int
    {
        return $this->headX;
    }

    public function setHeadX(int $headX): self
    {
        $this->headX = $headX;

        return $this;
    }

    public function getHeadY(): ?int
    {
        return $this->headY;
    }

    public function setHeadY(int $headY): self
    {
        $this->headY = $headY;

        return $this;
    }
}