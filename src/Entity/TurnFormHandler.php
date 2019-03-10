<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 10.03.2019
 * Time: 12:52
 */

namespace App\Entity;

use App\Entity\Game;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TurnFormHandler
{
    private $turnForm;
    private $move = [
        'right' => ['x' => 1,
                    'y' => 0],
        'left' => ['x' => -1,
                    'y' => 0],
        'up' => ['x' => 0,
                'y' =>  -1],
        'down' => ['x' => 0,
                    'y' => 1]
    ];

    private $nameHandlersPairs = [
        'end turn' => 'onEndTurn',
        'end ship turn' => 'onEndShipTurn',
        'right' => 'onMove',
        'left' => 'onMove',
        'up' => 'onMove',
        'down' => 'onMove'
    ];
    public function __construct($turnForm, $fleet, $ship, $game, $entityManager)
    {
        $this->turnForm = $turnForm;
        $this->fleet = $fleet;
        $this->ship = $ship;
        $this->game = $game;
        $this->entityManager = $entityManager;

    }

    public function handle()
    {
        foreach($this->nameHandlersPairs as $name => $handler) {
            if ($this->turnForm->get($name)->isClicked()) {
                $this->eventName = $name;
                $this->{$handler}();
                break;
            }
        }
        $this->entityManager->flush();
    }
    private function onMove()
    {
        if ($this->ship) {
            $x = $this->move[$this->eventName]['x'];
            $y = $this->move[$this->eventName]['y'];

            $this->ship->shiftOn($x, $y);
            $this->entityManager->merge($this->ship);
        }
    }
    private function onEndShipTurn()
    {
        if ($this->ship) {
            $this->ship->setIsActivated(true);
            $this->entityManager->merge($this->ship);
        }
    }
    private function onEndTurn()
    {
        $this->game->setCurrentUserId($this->game->getNextUserId());
        $this->fleet->deactiveAllShips($this->entityManager);
        $this->entityManager->persist($this->game);
    }
}