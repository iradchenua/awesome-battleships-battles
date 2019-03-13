<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 16:05
 */

namespace App\Manager;


use App\FormHandler\TurnHandler;
use App\Entity\Ship;
use App\FormHandler\PhaseHandler\MoveHandler;
use App\Form\Phase\Move;
use App\Form\GameTurn;
use App\Entity\Fleet;
use App\FormHandler\PhaseHandlersFactory;

class GamePanelManager
{
    private $fleet;
    private $ship;
    private $turnForm;
    private $game;
    private $entityManager;
    private $phaseForm;
    private $request;

    public function __construct(array $params)
    {
        $this->fleet = $params['fleet'];
        $this->ship = $params['ship'];
        $this->turnForm = $params['turnForm'];
        $this->phaseForm = $params['phaseForm'];
        $this->game = $params['game'];
        $this->entityManager = $params['entityManager'];
    }
    public function manage($request)
    {
        $this->request = $request;
        $defaultParams = [
            'entityManager' => $this->entityManager
        ];

        if ($this->ship) {
            $phaseParams = [
                'form' => $this->phaseForm,
                'ship' => $this->ship,
            ];
            $phase = $this->ship->getPhase();
            $phaseParams += $defaultParams;
            $phaseFormHandler = PhaseHandlersFactory::createNew($phase, $phaseParams);
        }
        $turnParams = [
            'form' => $this->turnForm,
            'fleet' => $this->fleet,
            'game' => $this->game];
        $turnParams += $defaultParams;

        $turnFormHandler = new TurnHandler($turnParams);
        $isRedirect = $this->handleForm($this->turnForm, $turnFormHandler, $request);
        if ($this->phaseForm)
            $isRedirect = $isRedirect || $this->handleForm($this->phaseForm, $phaseFormHandler, $request);
        return ($isRedirect);
    }
    private function handleForm($form, $handler)
    {
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle();
            return (true);
        }
        return (false);
    }
}