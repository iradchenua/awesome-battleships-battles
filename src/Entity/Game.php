<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 *
 * @ORM\Table(name="game")
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_PLAY = 'play';
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
     * @ORM\Column(name="user_id1", type="integer", nullable=false)
     */
    private $userId1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="user_id2", type="integer", nullable=true)
     */
    private $userId2;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId1(): ?int
    {
        return $this->userId1;
    }

    public function setUserId1(int $userId1): self
    {
        $this->userId1 = $userId1;

        return $this;
    }

    public function getUserId2(): ?int
    {
        return $this->userId2;
    }

    public function setUserId2(?int $userId2): self
    {
        $this->userId2 = $userId2;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }


}
