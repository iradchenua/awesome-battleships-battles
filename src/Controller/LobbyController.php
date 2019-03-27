<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 04.03.2019
 * Time: 11:30
 */

namespace App\Controller;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;
use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\ShipFactory;

class LobbyController extends BaseController
{
    /**
     * @Route("/")
     * @Route("/lobby", name="lobby")
     */
    public function index(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $doctrine = $this->getDoctrine();
        $repository = $doctrine
            ->getRepository(Game::class);

        if ($repository->getGameForUserId($userId) != null)
            return $this->redirectToRoute('game');

        $form = $this->createInitGameForm();

        return $this->render('lobby.html.twig', [
            'form' => $form->createView()
        ]);
    }
    private function createInitGameForm()
    {
        return  $this->createFormBuilder()
            ->setAction($this->generateUrl('init_game'))
            ->add('search', SubmitType::class)
            ->add('create game', SubmitType::class)
            ->getForm();
    }

    private function onCreateGame($userId)
    {
        $game = new Game();
        $game->setUserId1($userId);
        $game->setStatus(Game::STATUS_WAITING);

        return $game;

    }
    private function onSearch($userId)
    {
        $game = $this->gameRepository->getFreeGame();
        if ($game == null)
            return $game;
        $game->setUserId2($userId);
        $game->setStatus(Game::STATUS_PLAY);

        return $game;
    }
    private function giveShipsToUser($gameId, $userId, $typeParamPairs)
    {
        $commonParams = [
            'gameId' => $gameId,
            'userId' => $userId,
        ];
        foreach($typeParamPairs as $type => $params) {
            $params += $commonParams;
            $ship = ShipFactory::createShip($type, $params);
            $this->entityManager->persist($ship);
        }

    }
    /**
     * @Route("/init/game", name="init_game")
     */
    public function initGame(Request $request)
    {
        $form = $this->createInitGameForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid())
            return $this->redirectToRoute('lobby');

        $user = $this->getUser();
        $userId = $user->getId();

        if ($form->get('create game')->isClicked()) {
            $game = $this->onCreateGame($userId);
        }
        else {
            $game = $this->onSearch($userId);
        }

        if ($game == null) {
            $this->addFlash('error', 'there are not available games :( ');
            return $this->redirectToRoute('lobby');
        }

        $this->entityManager->persist($game);

        if ($game->getStatus() == Game::STATUS_PLAY) {
            $this->giveShipsToUser($game->getId(),  $game->getUserId1() , [
                'redship' => [
                    'x' => -75,
                    'y' => 0,
                    'dirX' => 1,
                    'dirY' => 0
                ]
            ]);
            $this->giveShipsToUser($game->getId(),  $game->getUserId2(), [
                'blueship' => [
                    'x' => 35,
                    'y' => 0,
                    'dirX' => -1,
                    'dirY' => 0
                ]
            ]);
            $game->setCurrentUserId($game->getUserId1());
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('lobby');
    }
}