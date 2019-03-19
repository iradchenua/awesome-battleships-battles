<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 07.03.2019
 * Time: 20:42
 */

namespace App\Entity;
use App\Entity\RedShip;
use App\Entity\BlueShip;

abstract class ShipFactory
{
    static private $shipTypes = [
        'redship' => RedShip::class,
        'blueship' => BlueShip::class
    ];
    static public function createShip($shipType, array $params)
    {
        return new self::$shipTypes[$shipType]($params);
    }
}