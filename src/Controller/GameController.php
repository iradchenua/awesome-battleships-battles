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
use App\Service\FormCreator;

class GameController extends BaseController
{
    private const CANVAS_WIDTH = 900;
    private const CANVAS_HEIGHT = 600;
    private const GAME_FIELD_WIDTH = 150;
    private const GAME_FIELD_HEIGHT = 100;

    private static $phases = [
        Ship::MOVEMENT_PHASE => Move::class,
        Ship::ORDER_PHASE => Order::class,
        Ship::SHOOT_PHASE => Shoot::class
    ];

    /**
     * @Route("/game",  name="game")
     */
    public function index(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $doctrine = $this->getDoctrine();

        $this->game = $this->gameRepository
                            ->getGameForUserId($userId);

        if (!$this->game)
            return $this->redirectToRoute('lobby');

        $this->entityManager = $doctrine->getManager();
        $ships = $this->getShips($doctrine);

        $leaveForm = $this->createLeaveForm();

        $turnForm = false;
        $phaseForm = false;
        $notActivatedShip = false;

        if ($userId == $this->game->getCurrentUserId()) {
            $turnForm = $this->createTurnForm();
            $fleet = new Fleet($ships[$userId], $userId);
            $notActivatedShip = $fleet->getNotActivatedShip();
            $phaseForm = $this->createPhaseForm($notActivatedShip);
        }

        $ships = $this->serialize($ships);

        return $this->render('game.html.twig', [
                'width' => self::CANVAS_WIDTH,
                'height' => self::CANVAS_HEIGHT,
                'gameFieldWidth' => self::GAME_FIELD_WIDTH,
                'gameFieldHeight' => self::GAME_FIELD_HEIGHT,
                'notActivatedShipName' => $this->getNotActivatedShipName($notActivatedShip),
                'ships' => $ships,
                'leaveForm' => $this->getView($leaveForm),
                'turnForm' => $this->getView($turnForm),
                'phaseForm' => $this->getView($phaseForm),
                'phaseName' => $this->getPhaseFormName($phaseForm),
                'userId1' => $this->game->getUserId1(),
                'userId2' => $this->game->getUserId2()
        ]);
    }
    private function getSomethingFromOne($one, $someThing)
    {
        if ($one)
            return $one->{$someThing}();
        return false;
    }
    private function getPhaseFormName($phaseForm)
    {
        return $this->getSomethingFromOne($phaseForm, 'getName');
    }
    private function getNotActivatedShipName($ship)
    {
        return $this->getSomethingFromOne($ship, 'getName');
    }
    private function getView($form)
    {
        return $this->getSomethingFromOne($form, 'createView');
    }
    private function getShips($doctrine)
    {
        if ($this->game->getStatus() == Game::STATUS_PLAY)
            return $doctrine->getRepository(Ship::class)->getShipsForGame($this->game->getId());
        return false;
    }
    private function createPhaseForm($ship)
    {
        if (!$ship)
            return false;
        $phase = $ship->getPhase();
        return ($this->createForm(self::$phases[$phase], null, [
            'action' => $this->generateUrl('phase')
        ]));
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

    private function serialize($ships)
    {
        if (!$ships)
            return ($ships);

        $encodes = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encodes);

        return ($serializer->serialize($ships, 'json'));
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
}