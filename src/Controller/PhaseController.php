<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 13.03.2019
 * Time: 20:33
 */

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Ship;
use App\Entity\Game;
use App\Entity\Fleet;

use App\FormHandler\Handler;
use App\FormHandler\PhaseHandlersFactory;

class PhaseController extends BaseController
{
    private static $phases = [
        Ship::MOVEMENT_PHASE => \App\Form\Phase\Move::class,
        Ship::ORDER_PHASE => \App\Form\Phase\Order::class,
        Ship::SHOOT_PHASE => \App\Form\Phase\Shoot::class
    ];

    /**
     * @Route("/phase",  name="phase")
     */
    public function phase(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $doctrine = $this->getDoctrine();

        $this->game = $this->gameRepository
            ->getGameForUserId($userId);

        if ($this->game->getStatus() != Game::STATUS_PLAY)
            return $this->redirectToRoute('game');

        $ships = $doctrine->getRepository(Ship::class)
                            ->getShipsForGame($this->game->getId());

        $fleet = new Fleet($ships[$userId], $userId);
        $ship = $fleet->getNotActivatedShip();
        if (!$ship)
            return $this->redirectToRoute('game');

        $form = $this->createForm(self::$phases[$ship->getPhase()], null, [
            'action' => $this->generateUrl('phase')
        ]);

        $handler = PhaseHandlersFactory::createNew($ship->getPhase(), [
            'form'  => $form,
            'fleet' => $fleet,
            'ship'  => $ship,
            'game'  => $this->game,
            'entityManager' => $this->entityManager
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
            $handler->handle();

        return $this->redirectToRoute('game');
    }
}