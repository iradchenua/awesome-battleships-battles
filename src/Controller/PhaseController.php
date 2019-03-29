<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 13.03.2019
 * Time: 20:33
 */

namespace App\Controller;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Ship;
use App\Entity\Game;
use App\Entity\Fleet;

use App\FormHandler\Handler;
use App\FormHandler\PhaseHandlersFactory;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PhaseController extends BaseController
{
    /**
     * @var \App\Form\Phase\FormPhaseFactory
     */
    private $formPhaseFactory;
    protected $obstacles;
    private $jsonSerializer;

    public function __construct(
        \App\Repository\GameRepository $gameRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \App\Form\Phase\FormPhaseFactory $formPhaseFactory,
        \App\Repository\ObstacleRepository $obstacleRepository,
        \App\Service\JsonSerializer $jsonSerializer
    ) {
        $this->formPhaseFactory = $formPhaseFactory;
        $this->obstacles = $obstacleRepository->getObstacles();
        $this->jsonSerializer = $jsonSerializer;

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

        if ($userId !== $this->game->getCurrentUserId()) {
            return new JsonResponse([], 403);
        }

        if ($this->game->getStatus() != Game::STATUS_PLAY) {
            return $this->redirectToRoute('game');
        }

        $ships = $doctrine->getRepository(Ship::class)
                            ->getShipsForGame($this->game->getId());

        $fleet = new Fleet($ships, $userId);
        $ship = $fleet->getNotActivatedShip();

        if ($ship == []) {
            return new JsonResponse([
                'ships' => $this->jsonSerializer->serialize($ships),
                'notActivatedShip' => $this->jsonSerializer->serialize($ship),
                'message' => '{"success": "success"}'
            ]);
        }

        $phase = $ship->getPhase();
        $form = $this->formPhaseFactory->createPhaseForm($phase);

        $handler = PhaseHandlersFactory::createNew($phase, [
            'form' => $form,
            'fleet' => $fleet,
            'ship' => $ship,
            'ships' => $ships,
            'obstacles' => $this->obstacles,
            'game' => $this->game,
            'entityManager' => $this->entityManager
        ]);

        $form->handleRequest($request);

        $message = true;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $handler->handle();
        } else {
            return new JsonResponse([], 403);
        }
        $message = $ship->getIsLive() ? $message : $ship->getName() . ' is dead';

        if (is_string($message)) {
            $message = '{"fail": ' . '"' . $message . '"}';
        } else {
            $message = '{"success": "success"}';
        }
        $ship = $ship->getIsActivated() ? [] : $ship;
        return new JsonResponse([
            'ships' => $this->jsonSerializer->serialize($ships),
            'notActivatedShip' => $this->jsonSerializer->serialize($ship),
            'message' => $message
        ]);
    }
}