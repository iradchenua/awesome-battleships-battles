<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 03.03.2019
 * Time: 22:03
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\Fleet;
use App\Form\GameTurn;

class GameController extends BaseController
{
    private const CANVAS_WIDTH = 900;
    private const CANVAS_HEIGHT = 600;
    /**
     * @var \App\Form\Phase\FormPhaseFactory
     */
    private $formPhaseFactory;

    private $obstacles;

    public function __construct(
        \App\Repository\GameRepository $gameRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \App\Form\Phase\FormPhaseFactory $formPhaseFactory,
        \App\Repository\ObstacleRepository $obstacleRepository,
        \App\Service\JsonSerializer $jsonSerializer
    ) {
        $this->formPhaseFactory = $formPhaseFactory;
        $this->obstacles = $obstacleRepository->getObstacles() ?? [];
        $this->jsonSerializer = $jsonSerializer;

        parent::__construct($gameRepository, $entityManager);
    }

    /**
     * @Route("/game",  name="game")
     */
    public function index(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $this->game = $this->gameRepository
                            ->getGameForUserId($userId);

        if (!$this->game)
            return $this->redirectToRoute('lobby');

        $leaveForm = $this->createLeaveForm();
        $turnForm = false;

        if ($userId == $this->game->getCurrentUserId()) {
            $turnForm = $this->createTurnForm();
        }

        return $this->render('game.html.twig', [
                    'leaveForm' => $this->getView($leaveForm),
                    'turnForm' => $this->getView($turnForm),
                    'orderPhase' => $this->formPhaseFactory->createPhaseFormView(Ship::ORDER_PHASE),
                    'movePhase' => $this->formPhaseFactory->createPhaseFormView(Ship::MOVEMENT_PHASE),
                    'shootPhase' => $this->formPhaseFactory->createPhaseFormView(Ship::SHOOT_PHASE),
                    'userId1' => $this->game->getUserId1(),
                    'userId2' => $this->game->getUserId2()
        ]);
    }

    /**
     * @Route("/init", name="init")
     */
    public function init(Request $request)
    {
        return new JsonResponse([
            'width' => self::CANVAS_WIDTH,
            'height' => self::CANVAS_HEIGHT,
            'gameFieldWidth' => Game::GAME_FIELD_WIDTH,
            'gameFieldHeight' => Game::GAME_FIELD_HEIGHT,
            'obstacles' => $this->jsonSerializer->serialize($this->obstacles)
        ]);
    }
    /**
     * @Route("/ships", name="ships")
     */
    public function ships(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $this->game = $this->gameRepository
            ->getGameForUserId($userId);

        $doctrine = $this->getDoctrine();
        $ships = [];
        if ($this->game->getStatus() == Game::STATUS_PLAY) {
            /** @var Ship[] $ships */
            $ships = $doctrine->getRepository(Ship::class)
                ->getShipsForGame($this->game->getId());
        }

        $notActivatedShip = [];
        if ($userId == $this->game->getCurrentUserId()) {
            $fleet = new Fleet($ships, $userId);
            /** @var Ship $notActivatedShip */
            $notActivatedShip = $fleet->getNotActivatedShip();
        }

        return new JsonResponse([
            'ships' => $this->jsonSerializer->serialize($ships),
            'notActivatedShip' => $this->jsonSerializer->serialize($notActivatedShip),
            'obstacles' => $this->jsonSerializer->serialize($this->obstacles)
        ]);
    }

    /**
     * @Route("/leave", name="leave")
     */
    public function leave(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $this->game = $this->gameRepository->getGameForUserId($userId);

        $leaveForm = $this->createLeaveForm();
        $leaveForm->handleRequest($request);

        if ($leaveForm->isSubmitted() && $leaveForm->isValid()) {
            $this->game->setStatus(Game::STATUS_END);
            $this->entityManager->persist($this->game);
            $this->entityManager->flush();
        }
        return $this->redirect('lobby');
    }

    private function getView($form)
    {
        if ($form)
            return $form->createView();
        return (false);
    }

    private function createLeaveForm() {
        return ($this->createFormBuilder()
            ->add('leave', SubmitType::class)
            ->setAction($this->generateUrl('leave'))
            ->getForm());
    }
    private function createTurnForm() {
        return $this->createForm(GameTurn::class, null, [
            'action' => $this->generateUrl('turn')
        ]);
    }
}