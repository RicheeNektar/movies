<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'update_password.old',
                'constraints' => [
                    new UserPassword([
                        'message' => 'password.invalid',
                    ]),
                ],
            ])
            ->add('new', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'update_password.new'
                ],
                'second_options' => [
                    'label' => 'update_password.repeat'
                ],
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'invalid_message' => 'password.not_equal',
                'constraints' => [
                    new NotBlank([
                        'message' => 'password.not_blank',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'password.min_length',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'update_password.submit'
            ])
        ;
    }
}
