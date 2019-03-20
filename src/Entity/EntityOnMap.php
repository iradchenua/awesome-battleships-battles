<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 19.03.2019
 * Time: 23:52
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class EntityOnMap
{
    protected const IMG = "none";
    protected const WIDTH = 0;
    protected const HEIGHT = 0;

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
     * @ORM\Column(name="x", type="integer", nullable=false)
     */
    protected $x;

    /**
     * @var int
     *
     * @ORM\Column(name="y", type="integer", nullable=false)
     */
    protected $y;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getImg()
    {
        return static::IMG;
    }

    public function getWidth()
    {
        return static::WIDTH;
    }

    public function getHeight()
    {
        return static::HEIGHT;
    }

    public function getRect() {
        return ([
            'x' => $this->getX(),
            'y' => $this->getY(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight()
        ]);
    }

    protected function isIntersectWith(EntityOnMap $entity) {
        $rect1 = $this->getRect();
        $rect2 = $entity->getRect();

        return  $rect1['x'] < $rect2['x'] + $rect2['width'] &&
                $rect1['x'] + $rect1['width'] > $rect2['x'] &&
                $rect1['y'] > $rect2['y'] - $rect2['height'] &&
                $rect1['y'] - $rect1['height'] < $rect2['y'];
    }

    protected function intersectWithEntities($entities) {
        foreach($entities as $entity) {
            if ($this->isIntersectWith($entity)) {
                return $entity;
            }
        }
        return false;
    }
}