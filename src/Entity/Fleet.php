<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 09.03.2019
 * Time: 17:53
 */

namespace App\Entity;

use App\Entity\Ship;

class Fleet
{
    private $ships;
    private $owner;

    public function __construct($ships, $owner)
    {
        $this->ships = $ships;
        $this->owner = $owner;
    }
    public function getNotActivatedShip()
    {
        foreach($this->ships as $ship) {
            if (!$ship->getIsActivated())
                return $ship;
        }
    }
    public function deactiveAllShips($entityManager)
    {
        foreach($this->ships as $ship)
        {
            $ship->setIsActivated(false);
            $ship->setPhase(Ship::MOVEMENT_PHASE);
            $entityManager->merge($ship);
        }
    }
}