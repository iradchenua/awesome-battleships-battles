<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 16:25
 */

namespace App\FormHandler;

use App\Entity\Ship;
use App\FormHandler\PhaseHandler;

class PhaseHandlersFactory
{
    private static $phasesHandlers = [
        Ship::MOVEMENT_PHASE => PhaseHandler\MoveHandler::class,
        Ship::ORDER_PHASE => PhaseHandler\OrderHandler::class,
        Ship::SHOOT_PHASE => PhaseHandler\ShootHandler::class,
    ];

    public static function createNew($phase, $params)
    {
        return new self::$phasesHandlers[$phase]($params);
    }
}