<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 04.03.2019
 * Time: 21:16
 */

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index(Request $request)
    {
        return $this->redirectToRoute('lobby');
    }
}