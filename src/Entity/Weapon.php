<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 21.03.2019
 * Time: 18:49
 */

namespace App\Entity;


abstract class Weapon
{
    const CHARGE = 0;
    protected $shortRange = [];
    protected $middleRange = [];
    protected $longRange = [];

}