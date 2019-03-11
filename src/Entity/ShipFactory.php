<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 07.03.2019
 * Time: 20:42
 */

namespace App\Entity;
use App\Entity\RedShip;

abstract class ShipFactory
{
    static private $shipTypes = [
        'redship' => RedShip::class
    ];
    static public function createShip($shipType, array $params)
    {
        return new self::$shipTypes[$shipType]($params);
    }
}