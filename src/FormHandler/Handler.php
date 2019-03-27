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
        $valid = true;
        if ($this->form->getClickedButton() &&
            ($name = $this->form->getClickedButton()->getName()) &&
            isset($this->nameHandlersPairs[$name])) {
            $this->eventName = $name;
            $valid = $this->{$this->nameHandlersPairs[$name]}();
        }
        $this->entityManager->flush();
        return $valid;
    }
}