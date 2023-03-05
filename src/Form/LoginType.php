<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'base.username',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'base.password',
            ])
            ->add('remember_me', CheckboxType::class, [
                'label' => 'base.remember_me',
                'required' => false,
            ])
            ->add('csrf', HiddenType::class)
            ->add('target_path', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'login.submit',
            ])
        ;
    }
}