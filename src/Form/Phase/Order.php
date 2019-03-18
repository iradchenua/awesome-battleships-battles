<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 18:27
 */

namespace App\Form\Phase;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Order extends GamePhase
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('move', NumberType::class)
            /*->add('shields', NumberType::class)
            ->add('weapons', NumberType::class)*/
            ->add('distribute', SubmitType::class);
        parent::buildForm($builder, $options);
    }
}