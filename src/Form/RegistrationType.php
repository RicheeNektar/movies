<?php

namespace App\Form;

use App\Constraints\UniqueUsername;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'base.username',
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'register.username.not_blank',
                    ]),
                    new UniqueUsername([
                        'message' => 'register.username.not_unique',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'base.password',
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'register.password.not_blank',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'register.password.min_length',
                    ]),
                ],
            ])
            ->add('invitation', HiddenType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'register.submit',
            ])
        ;
    }
}
