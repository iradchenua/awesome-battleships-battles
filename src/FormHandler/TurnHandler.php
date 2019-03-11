<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 10.03.2019
 * Time: 12:52
 */

namespace App\FormHandler;

use App\Entity\Game;
use App\FormHandler\Handler;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TurnHandler extends Handler
{
    protected $nameHandlersPairs = [
        'end turn' => 'onEndTurn'
    ];

    protected $fleet;
    protected $game;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->fleet = $params['fleet'];
        $this->game = $params['game'];

    }

    protected function onEndTurn()
    {
        $this->game->setCurrentUserId($this->game->getNextUserId());
        $this->fleet->deactiveAllShips($this->entityManager);
        $this->entityManager->persist($this->game);
    }
}