<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 21.03.2019
 * Time: 18:53
 */

namespace App\Entity\Weapon;


class SideLaserBatteries
{
    const CHARGE = 0;
    protected $shortRange = [1, 10];
    protected $middleRange = [11, 20];
    protected $longRange = [21, 30];
}