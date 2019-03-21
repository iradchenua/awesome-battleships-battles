<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 07.03.2019
 * Time: 16:20
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use App\Entity\Ship;

/**
 *@ORM\Entity
 *@ORM\InheritanceType("SINGLE_TABLE")
 *@ORM\Table(name="ship")
 *@ORM\DiscriminatorColumn(name="name", type = "string")
 *@ORM\DiscriminatorMap({"ship" = "ship", "blueship" = "BlueShip"})
 */
class BlueShip extends Ship
{
    protected const CLASS_NAME = 'blueship';
    protected const IMG = 'images/blue.png';
    protected const HANDLING = 4;
    protected const PP = 10;
    protected const SPEED = 15;
    protected const WIDTH = 40;
    protected const HEIGHT = 10;
    protected const ENGINE_POWER = 10;
    protected const HUll_POINTS = 5;


    /**
     * @var boolean
     *
     * @ORM\Column(name="hull_points", type="integer", nullable=false)
     */
    protected $hullPoints = self::HUll_POINTS;

    /**
     * @var int
     *
     * @ORM\Column(name="speed", type="integer", nullable=false)
     */
    protected $speed = self::SPEED;
}