<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 14:59
 */

namespace App\FormHandler\PhaseHandler;

use App\FormHandler\Handler;

class PhaseHandler extends Handler
{
    protected $nameHandlersPairs = [
        'end ship turn' => 'onEndShipTurn',
        'end phase' => 'onEndPhase'
    ];

    /**
     * @var \App\Entity\Ship
     */
    protected $ship;
    protected $ships;
    protected $obstacles;

    public function __construct($params)
    {
        parent::__construct($params);

        $this->ships = $params['ships'];
        $this->obstacles = $params['obstacles'];

        $this->ship = $params['ship'];
    }
    protected function onEndShipTurn()
    {
        if ($this->ship) {
            $this->ship->endShipTurn();
            $this->entityManager->merge($this->ship);
        }
    }
    protected function onEndPhase()
    {
        if ($this->ship) {
            $this->ship->incrementPhase();
            $this->entityManager->merge($this->ship);
        }
    }
}