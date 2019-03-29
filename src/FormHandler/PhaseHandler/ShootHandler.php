<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 18:41
 */

namespace App\FormHandler\PhaseHandler;


class ShootHandler extends PhaseHandler
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

        $newNameHandlersPairs = [
            'shoot' => 'onShoot',
        ];
        $this->nameHandlersPairs = $this->nameHandlersPairs + $newNameHandlersPairs;
    }
    protected function onShoot()
    {
        $target = $this->form->get('toShoot')->getData();

        return 'pew pew ' . $target;
    }
}