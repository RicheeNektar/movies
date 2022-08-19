<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('is_admin', CheckboxType::class, [
                'label' => 'admin.user.roles.admin',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'admin.user.roles.save'
            ])
        ;
    }
}
