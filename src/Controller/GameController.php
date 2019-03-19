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
        $ships = false;
        if ($this->game->getStatus() == Game::STATUS_PLAY) {
            /** @var Ship[] $ships */
            $ships = $doctrine->getRepository(Ship::class)
                        ->getShipsForGame($this->game->getId());
        }

        $leaveForm = $this->createLeaveForm();
        $turnForm = false;
        $notActivatedShip = false;

        if ($userId == $this->game->getCurrentUserId()) {
            $turnForm = $this->createTurnForm();
            $fleet = new Fleet($ships[$userId], $userId);
            /** @var Ship $notActivatedShip */
            $notActivatedShip = $fleet->getNotActivatedShip();
        }
        $phaseName = "";
        if ($notActivatedShip) {
            $phaseName = $notActivatedShip->getPhaseName();
        }

        return $this->render('game.html.twig', [
                'width' => self::CANVAS_WIDTH,
                'height' => self::CANVAS_HEIGHT,
                'gameFieldWidth' => Game::GAME_FIELD_WIDTH,
                'gameFieldHeight' => Game::GAME_FIELD_HEIGHT,
                'notActivatedShip' => $notActivatedShip,
                'ships' => $this->serialize($ships),
                'leaveForm' => $this->getView($leaveForm),
                'turnForm' => $this->getView($turnForm),
                'phaseForm' => $this->formPhaseFactory->createPhaseFormView($notActivatedShip),
                'phaseName' => $phaseName,
                'userId1' => $this->game->getUserId1(),
                'userId2' => $this->game->getUserId2()
        ]);
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