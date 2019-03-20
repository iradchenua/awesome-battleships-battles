<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 20.03.2019
 * Time: 13:14
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Obstacle
 *
 * @ORM\Entity(repositoryClass="App\Repository\ObstacleRepository")
 */

class Obstacle extends EntityOnMap
{
    protected const IMG = "images/island.png";
    protected const WIDTH = 50;
    protected const HEIGHT = 50;

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
    protected $x = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="y", type="integer", nullable=false)
     */
    protected $y = 0;
}