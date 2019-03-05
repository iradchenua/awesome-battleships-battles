<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 04.03.2019
 * Time: 11:30
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;
use App\Entity\Game;

class LobbyController extends AbstractController
{
    /**
     * @Route("/lobby", name="lobby")
     */
    public function index(Request $request)
    {

        $user = $this->getUser();
        $userId = $user->getId();

        $doctrine = $this->getDoctrine();
        $repository = $doctrine
            ->getRepository(Game::class);

        if ($this->isUserInGame($userId, $repository))
            return $this->redirectToRoute('game');

        $form = $this->createFormBuilder()
            ->add('search', SubmitType::class)
            ->add('create game', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
           return $this->onFormSubmited($form, $doctrine, $repository, $userId);

        return $this->render('lobby.html.twig', [
            'form' => $form->createView()
        ]);
    }
    private function isUserInGame($userId, $repository)
    {
        $query = $repository->createQueryBuilder('g')
            ->where("(g.userId1 = :userId or g.userId2 = :userId) 
                                   and (g.status=:waitingStatus or g.status=:playStatus)")
            ->setParameter('userId', $userId)
            ->setParameter('playStatus', Game::STATUS_PLAY)
            ->setParameter('waitingStatus', Game::STATUS_WAITING)
            ->getQuery();

        $game = $query->setMaxResults(1)->getOneOrNullResult();
        return ($game != null);
    }

    private function onFormSubmited($form, $doctrine, $repository, $userId)
    {
        $entityManager = $doctrine->getManager();
        if ($form->get('create game')->isClicked()) {
            $game = new Game();
            $game->setUserId1($userId);
            $game->setStatus(Game::STATUS_WAITING);
        }
        else {
            $query = $repository->createQueryBuilder('g')
                ->where('g.userId2 is NULL and g.status=:status')
                ->setParameter('status', Game::STATUS_WAITING)
                ->getQuery();
            $game = $query->setMaxResults(1)->getOneOrNullResult();

            if ($game == null) {
                $this->addFlash('error', 'there are not available games :( ');
                return $this->redirectToRoute('lobby');
            }

            $game->setUserId2($userId);
            $game->setStatus(Game::STATUS_PLAY);
        }

        $entityManager->persist($game);
        $entityManager->flush();

        return $this->redirectToRoute('game');

    }
}