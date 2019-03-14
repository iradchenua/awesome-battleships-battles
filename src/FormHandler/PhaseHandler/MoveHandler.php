<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 15:14
 */

namespace App\FormHandler\PhaseHandler;


class MoveHandler extends PhaseHandler
{
    public function __construct($params)
    {
        parent::__construct($params);
        $newNameHandlersPairs = [
            'rotate left' => 'onRotate',
            'rotate right' => 'onRotate',
            'move' => 'onMove'
        ];
        $this->nameHandlersPairs = $this->nameHandlersPairs + $newNameHandlersPairs;
    }
    protected function onRotate()
    {
        if ($this->ship) {
            $where = explode(" ", $this->eventName)[1];

            $this->ship->rotate($where);
            $this->entityManager->merge($this->ship);
        }
    }
    protected function onMove()
    {
        if ($this->ship) {
            $this->ship->move();
            $this->entityManager->merge($this->ship);
        }
    }
}