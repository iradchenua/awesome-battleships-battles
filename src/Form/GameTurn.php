<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 10.03.2019
 * Time: 17:18
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class GameTurn extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('end turn', SubmitType::class);
    }
}