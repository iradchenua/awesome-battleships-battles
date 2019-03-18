<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 18:31
 */

namespace App\FormHandler\PhaseHandler;


class OrderHandler extends PhaseHandler
{
    protected $form;
    protected $entityManager;
    protected $eventName;
    /**
     * @var $ship \App\Entity\Ship
     */
    protected $ship;

    public function __construct($params)
    {
        parent::__construct($params);
        $newNameHandlersPairs = [
            'distribute' => 'onDistribute',
        ];
        $this->nameHandlersPairs = $this->nameHandlersPairs + $newNameHandlersPairs;
    }
    protected function onDistribute()
    {
        $speedIncrement = $this->form->get('move')->getData();
        if ($speedIncrement < 0 || $speedIncrement > $this->ship->getPP())
            return ;
        if ($this->ship) {
            $this->ship->setSpeed($this->ship->getSpeed() + $speedIncrement);
        }
        $this->ship->incrementPhase();
        $this->entityManager->merge($this->ship);
    }

}