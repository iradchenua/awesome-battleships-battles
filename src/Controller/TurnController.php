<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 13.03.2019
 * Time: 12:08
 */

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\FormHandler\TurnHandler;
use App\Entity\Ship;
use App\Entity\Game;
use App\Form\GameTurn;
use App\Entity\Fleet;

class TurnController extends BaseController
{
    /**
     * @Route("/turn",  name="turn")
     */
    public function turn(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();

        $doctrine = $this->getDoctrine();

        $this->game = $this->gameRepository
            ->getGameForUserId($userId);

        if ($this->game->getStatus() != Game::STATUS_PLAY)
            return $this->redirectToRoute('game');

        $form = $this->createForm(GameTurn::class, null, [
            'action' => $this->generateUrl('turn')
        ]);

        $ships = $doctrine->getRepository(Ship::class)
                        ->getShipsForGame($this->game->getId());

        $handler = new TurnHandler([
            'form'  => $form,
            'fleet' => new Fleet($ships[$userId], $userId),
            'game'  => $this->game,
            'entityManager' => $this->entityManager
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
            $handler->handle();

        return $this->redirectToRoute('game');
    }

}