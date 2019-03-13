<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 14:32
 */

namespace App\Form\Phase;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class GamePhase extends AbstractType
{
    protected const NAME = "no name";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('end ship turn', SubmitType::class)
            ->add('end phase', SubmitType::class);
    }
    public function getFormName()
    {
        return (static::NAME);
    }
}