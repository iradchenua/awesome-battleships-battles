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

class Order extends GamePhase
{
    protected const NAME = "Order";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('move', NumberType::class)
            ->add('shields', NumberType::class)
            ->add('weapons', NumberType::class);
        parent::buildForm($builder, $options);
    }
}