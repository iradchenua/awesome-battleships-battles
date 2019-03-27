<?php
/**
 * Created by PhpStorm.
 * User: mulde
 * Date: 14.03.2019
 * Time: 11:01
 */

namespace App\Form\Phase;

use App\Entity\Ship;

class FormPhaseFactory
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;
    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    public function __construct(
        \Symfony\Component\Form\FormFactoryInterface $formFactory,
        \Symfony\Component\Routing\Matcher\UrlMatcherInterface $router
    ) {
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    private $phases = [
        Ship::MOVEMENT_PHASE => Move::class,
        Ship::ORDER_PHASE => Order::class,
        Ship::SHOOT_PHASE => Shoot::class
    ];

    public function createPhaseFormView($phase)
    {
        if ($phaseForm = $this->createPhaseForm($phase)) {
            return $phaseForm->createView();
        }
        return false;
    }
    public function createPhaseForm($phase)
    {
        return $this->formFactory->create($this->phases[$phase], null, [
            'action' => $this->router->generate('phase')
        ]);
    }
}