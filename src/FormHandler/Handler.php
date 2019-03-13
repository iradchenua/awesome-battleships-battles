<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 14:41
 */

namespace App\FormHandler;


class Handler
{
    protected $form;
    protected $entityManager;
    protected $eventName;

    protected $nameHandlersPairs = [];

    public function __construct(array $params)
    {
        $this->form = $params['form'];
        $this->entityManager = $params['entityManager'];
    }

    public function handle()
    {
        foreach($this->nameHandlersPairs as $name => $handler) {
            if ($this->form->get($name)->isClicked()) {
                $this->eventName = $name;
                $this->{$handler}();
                break;
            }
        }
        $this->entityManager->flush();
    }
}