<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 11.03.2019
 * Time: 18:41
 */

namespace App\Form\Phase;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Shoot extends GamePhase
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('toShoot', HiddenType::class, [
                'data' => 'no target'
            ])
            ->add('shoot', SubmitType::class);

        parent::buildForm($builder, $options);
    }

}