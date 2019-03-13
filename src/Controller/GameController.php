<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 03.03.2019
 * Time: 22:03
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Entity\User;
use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\Fleet;
use App\Form\Phase\Move;
use App\Form\Phase\Order;
use App\Form\Phase\Shoot;
use App\Form\GameTurn;
use App\Manager\GamePanelManager;

class GameController extends AbstractController
{
    private const CANVAS_WIDTH = 900;
    private const CANVAS_HEIGHT = 600;
    private const GAME_FIELD_WIDTH = 150;
    private const GAME_FIELD_HEIGHT = 100;

    private $entityManager;
    private $game;

    private static $phases = [
        Ship::MOVEMENT_PHASE => Move::class,
        Ship::ORDER_PHASE => Order::class,
        Ship::SHOOT_PHASE => Shoot::class
    ];

    public function __construct(

    )
    {

    }

    /**
     * @Route("/game",  name="game")
     */
    public function index(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $doctrine = $this->getDoctrine();

        $this->game = $doctrine->getRepository(Game::class)
            ->getGameForUserId($userId);
        if ($this->game == null)
            return $this->redirectToRoute('lobby');

        $this->entityManager = $doctrine->getManager();
        $ships = false;

        if ($this->game->getStatus() == Game::STATUS_PLAY)
            $ships = $this->getShips($doctrine);

        $leaveForm = $this->createLeaveForm();

        $turnForm = false;
        $phaseForm = false;
        $notActivatedShipName = false;
        if ($userId == $this->game->getCurrentUserId()) {
            $gamePanelManager = $this->onCurrentUser($ships, $userId,
                                                $turnForm, $phaseForm, $notActivatedShipName);
            if ($gamePanelManager->manage($request))
                return $this->redirectToRoute('game');
        }

        if ($ships)
            $ships = $this->serialize($ships);
        $leaveFormView = $leaveForm->createView();
        $turnFormView = $turnForm ? $turnForm->createView() : false;
        $phaseFormView = false;
        $phaseName = false;
        if ($phaseForm) {
            $phaseFormView = $phaseForm->createView();
            $phaseName = $phaseForm->getName();
        }
        return $this->render('game.html.twig', [
                'width' => self::CANVAS_WIDTH,
                'height' => self::CANVAS_HEIGHT,
                'gameFieldWidth' => self::GAME_FIELD_WIDTH,
                'gameFieldHeight' => self::GAME_FIELD_HEIGHT,
                'notActivatedShipName' => $notActivatedShipName,
                'ships' => $ships,
                'leaveForm' => $leaveFormView,
                'turnForm' => $turnFormView,
                'phaseForm' => $phaseFormView,
                'phaseName' => $phaseName,
                'userId1' => $this->game->getUserId1(),
                'userId2' => $this->game->getUserId2()
        ]);
    }
    private function getShips($doctrine)
    {
        $gameId = $this->game->getId();
        $shipRepository = $doctrine->getRepository(Ship::class);
        $userId1 = $this->game->getUserId1();
        $userId2 = $this->game->getUserId2();

        return [
            $userId1 => $shipRepository->getShipsForUser($gameId, $userId1),
            $userId2 => $shipRepository->getShipsForUser($gameId, $userId2)
        ];
    }

    private function createPhaseForm($ship) {
        $phase = $ship->getPhase();
        return ($this->createForm(self::$phases[$phase]));
    }

    private function createLeaveForm() {
        return ($this->createFormBuilder()
            ->add('leave', SubmitType::class)
            ->setAction($this->generateUrl('leave'))
            ->getForm());
    }
    private function onCurrentUser($ships, $userId,
                                   &$turnForm, &$phaseForm, &$notActivatedShipName)
    {
        $turnForm = $this->createForm(GameTurn::class);
        $fleet = new Fleet($ships[$userId], $userId);
        $notActivatedShip = $fleet->getNotActivatedShip();

        if ($notActivatedShip) {
            $notActivatedShipName = $notActivatedShip->getName();
            $phaseForm = $this->createPhaseForm($notActivatedShip);
        }

        $gamePanelManager = new GamePanelManager([
            'fleet' => $fleet,
            'ship' => $notActivatedShip,
            'turnForm' => $turnForm,
            'phaseForm' => $phaseForm,
            'game' => $this->game,
            'entityManager' => $this->entityManager
        ]);
        return ($gamePanelManager);
    }
    private function serialize($ships)
    {
        $encodes = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encodes);

        return ($serializer->serialize($ships, 'json'));
    }

    private function onLeaveFormSubmitted()
    {
        $this->game->setStatus(Game::STATUS_END);
        $this->entityManager->persist($this->game);
        $this->entityManager->flush();
    }
    /**
     * @Route("/leave", name="leave")
     */
    function leave(Request $request)
    {
        $leaveForm = $this->createLeaveForm();
        $leaveForm->handleRequest($request);

        if ($leaveForm->isSubmitted() && $leaveForm->isValid()) {
            $this->onLeaveFormSubmitted();
            return $this->redirect('lobby');
        }
    }
}