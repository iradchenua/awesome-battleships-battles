<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 13.03.2019
 * Time: 22:20
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends  AbstractController
{

    protected $entityManager;
    protected $gameRepository;
    protected $game;

    public function __construct(
        \App\Repository\GameRepository $gameRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    )
    {
        $this->gameRepository = $gameRepository;
        $this->entityManager = $entityManager;
    }
}