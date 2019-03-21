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
    protected $form;
    protected $entityManager;
    protected $eventName;
    /**
     * @var $ship \App\Entity\Ship
     */
    protected $ship;
    protected $ships;
    protected $obstacles;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->ships = $params['ships'];
        $this->obstacles = $params['obstacles'];

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
            $this->afterAction();
        }
        return (true);
    }
    protected function afterAction()
    {
        $interShip = $this->ship->checkCollisionWithShips($this->ships);
        $this->ship->checkCollisionWithObstacles($this->obstacles);
        $this->ship->checkOutOfBounds();
        if ($interShip) {
            $this->entityManager->merge($interShip);
        }
        $this->entityManager->merge($this->ship);
        $this->entityManager->flush();
    }
    protected function onMove()
    {
        $numberOfCeils = $this->form->get('numberOfCeils')->getData();
        if (!is_numeric($numberOfCeils))
            return 'invalid number of ceils';
        $canMove = true;
        if ($this->ship) {
            $canMove =  $this->ship->move($numberOfCeils);
            $this->afterAction();
        }
        if (!$canMove)
            return 'can*t move, read the rules';
        return $canMove;
    }
}