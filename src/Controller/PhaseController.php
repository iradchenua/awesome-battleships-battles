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
    /**
     * @var \App\Form\Phase\FormPhaseFactory
     */
    private $formPhaseFactory;

    public function __construct(
        \App\Repository\GameRepository $gameRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \App\Form\Phase\FormPhaseFactory $formPhaseFactory
    ) {
        $this->formPhaseFactory = $formPhaseFactory;
        parent::__construct($gameRepository, $entityManager);
    }

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

        if ($this->game->getStatus() != Game::STATUS_PLAY) {
            return $this->redirectToRoute('game');
        }

        $ships = $doctrine->getRepository(Ship::class)
            ->getShipsForGame($this->game->getId());

        $fleet = new Fleet($ships[$userId], $userId);
        $ship = $fleet->getNotActivatedShip();

        if (!$ship) {
            return $this->redirectToRoute('game');
        }

        $form = $this->formPhaseFactory->createPhaseForm($ship);

        $handler = PhaseHandlersFactory::createNew($ship->getPhase(), [
            'form' => $form,
            'fleet' => $fleet,
            'ship' => $ship,
            'game' => $this->game,
            'entityManager' => $this->entityManager
        ]);

        $form->handleRequest($request);

        $message = true;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $handler->handle();
        }

        if (is_string($message)) {
            $this->addFlash('error', $message);
        } else {
            $this->addFlash('success', 'done');
        }

        return $this->redirectToRoute('game');
    }
}