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
use App\Entity\TurnFormHandler;

class GameController extends AbstractController
{
    private const CANVAS_WIDTH = 900;
    private const CANVAS_HEIGHT = 600;
    private const GAME_FIELD_WIDTH = 150;
    private const GAME_FIELD_HEIGHT = 100;

    private $entityManager;
    private $game;

    /**
     * @Route("/game", name="game")
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
        $leaveForm->handleRequest($request);

        if ($leaveForm->isSubmitted() && $leaveForm->isValid()) {
            $this->onLeaveFormSubmited();
            return $this->redirect('lobby');
        }

        $turnForm = false;
        $notActivatedShip = false;
        if ($userId == $this->game->getCurrentUserId()) {
            $turnForm = $this->createTurnForm();

            $fleet = new Fleet($ships[$userId], $userId);
            $notActivatedShip = $fleet->getNotActivatedShip();

            $turnFromHandler = new TurnFormHandler($turnForm,
                $fleet, $notActivatedShip, $this->game, $this->entityManager);

            $turnForm->handleRequest($request);
            if ($turnForm->isSubmitted() && $turnForm->isValid()) {
                $turnFromHandler->handle($fleet, $this->game,
                                        $this->entityManager, $notActivatedShip);
                return $this->redirectToRoute('game');
            }
        }

        if ($notActivatedShip)
            $notActivatedShipName = $notActivatedShip->getName();
        else
            $notActivatedShipName = 'There are not free ship';

        if ($ships)
            $ships = $this->serialize($ships);
        $leaveFormView = $leaveForm->createView();
        $turnFormView = $turnForm ? $turnForm->createView() : false;

        return $this->render('game.html.twig', [
                'width' => self::CANVAS_WIDTH,
                'height' => self::CANVAS_HEIGHT,
                'gameFieldWidth' => self::GAME_FIELD_WIDTH,
                'gameFieldHeight' => self::GAME_FIELD_HEIGHT,
                'notActivatedShipName' => $notActivatedShipName,
                'ships' => $ships,
                'leaveForm' => $leaveFormView,
                'turnForm' => $turnFormView,
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
    private function createTurnForm() {
        return ($this->createFormBuilder()
            ->add('end turn', SubmitType::class)
            ->add('end ship turn', SubmitType::class)
            ->add('right', SubmitType::class)
            ->add('left', SubmitType::class)
            ->add('up', SubmitType::class)
            ->add('down', SubmitType::class)
            ->getForm());
    }
    private function createLeaveForm() {
        return ($this->createFormBuilder()
            ->add('leave', SubmitType::class)
            ->getForm());
    }
    private function serialize($ships)
    {
        $encodes = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encodes);

        return ($serializer->serialize($ships, 'json'));
    }
    private function onLeaveFormSubmited()
    {
        $this->game->setStatus(Game::STATUS_END);
        $this->entityManager->persist($this->game);
        $this->entityManager->flush();
    }
}