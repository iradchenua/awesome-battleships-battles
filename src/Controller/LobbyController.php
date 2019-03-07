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

class LobbyController extends AbstractController
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

        $form = $this->createFormBuilder()
            ->add('search', SubmitType::class)
            ->add('create game', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
           return $this->onFormSubmitted($form, $doctrine, $repository, $userId);

        return $this->render('lobby.html.twig', [
            'form' => $form->createView()
        ]);
    }
    private function onFormSubmitted($form, $doctrine, $repository, $userId)
    {
        $entityManager = $doctrine->getManager();

        if ($form->get('create game')->isClicked())
            $game = $this->onCreateGame($entityManager, $userId);
        else
            $game = $this->onSearch($entityManager, $repository, $userId);

        if ($game == null) {
            $this->addFlash('error', 'there are not available games :( ');
            return $this->redirectToRoute('lobby');
        }

        $entityManager->persist($game);
        if ($game->getStatus() == Game::STATUS_PLAY) {
            $this->giveShipsToUser($entityManager, $game->getId(),  $game->getUserId1() , [
                'redship' => [
                    'x' => 0,
                    'y' => 0,
                ]
            ]);
            $this->giveShipsToUser($entityManager, $game->getId(),  $game->getUserId2(), [
                'redship' => [
                    'x' => 140,
                    'y' => 0,
                ]
            ]);
        }
        $entityManager->flush();

        return $this->redirectToRoute('game');
    }
    private function onCreateGame($entityManager, $userId)
    {
        $game = new Game();
        $game->setUserId1($userId);
        $game->setStatus(Game::STATUS_WAITING);

        return $game;

    }
    private function onSearch($entityManager, $repository, $userId)
    {
        $game = $repository->getFreeGame();

        if ($game == null)
            return $game;

        $game->setUserId2($userId);
        $game->setStatus(Game::STATUS_PLAY);

        return $game;
    }
    private function giveShipsToUser($entityManager, $gameId, $userId, $typeCordsPairs)
    {
        foreach($typeCordsPairs as $type => $cords) {
            $ship = ShipFactory::createShip($type, $gameId, $userId, $cords['x'], $cords['y']);
            $entityManager->persist($ship);
        }

    }
}