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

    protected $ship;


    public function __construct($params)
    {
        parent::__construct($params);
        $this->ship = $params['ship'];
    }
    protected function onEndShipTurn()
    {
        if ($this->ship) {
            $this->ship->setIsActivated(true);
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