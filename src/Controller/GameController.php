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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;
use App\Entity\Game;

class GameController extends AbstractController
{
    private const CANVAS_WIDTH = 900;
    private const CANVAS_HEIGHT = 600;
    private const GAME_FIELD_WIDTH = 150;
    private const GAME_FIELD_HEIGHT = 100;
    /**
     * @Route("/game", name="game")
     */
    public function index(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $doctrine = $this->getDoctrine();
        $repository = $doctrine
            ->getRepository(Game::class);

        $query = $repository->createQueryBuilder('g')
            ->where("(g.userId1 = :userId or g.userId2 = :userId) 
                                   and (g.status=:waitingStatus or g.status=:playStatus)")
            ->setParameter('userId', $userId)
            ->setParameter('playStatus', Game::STATUS_PLAY)
            ->setParameter('waitingStatus', Game::STATUS_WAITING)
            ->getQuery();

        $game = $query->setMaxResults(1)->getOneOrNullResult();

        if ($game == null)
            return $this->redirectToRoute('lobby');

        $form = $this->createFormBuilder()
            ->add('leave', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
            return $this->onFormSubmited($game, $doctrine->getManager());

        return $this->render('game.html.twig', [
                'width' => self::CANVAS_WIDTH,
                'height' => self::CANVAS_HEIGHT,
                'gameFieldWidth' => self::GAME_FIELD_WIDTH,
                'gameFieldHeight' => self::GAME_FIELD_HEIGHT,
                'form' => $form->createView(),
                'userId1' => $game->getUserId1(),
                'userId2' => $game->getUserId2()
        ]);
    }
    private function onFormSubmited(&$game, $entityManager)
    {
        $game->setStatus(Game::STATUS_END);
        $entityManager->persist($game);
        $entityManager->flush();
        return $this->redirect('lobby');
    }
}