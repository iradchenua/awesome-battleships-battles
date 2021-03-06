<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 10.03.2019
 * Time: 17:36
 */

namespace App\Form\Phase;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class Move extends GamePhase
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numberOfCeils', NumberType::class, [
                'required' => false
            ])
            ->add('move', SubmitType::class)
            ->add('rotate left', SubmitType::class)
            ->add('rotate right', SubmitType::class);
        parent::buildForm($builder, $options);
    }
}