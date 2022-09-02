<?php

namespace App\Form;

use App\Constraints\UniqueMail;
use App\Constraints\UniqueUsername;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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
            ->add('mail', EmailType::class, [
                'label' => 'base.mail',
                'constraints' => [
                    new NotBlank([
                        'message' => 'mail.not_blank',
                    ]),
                    new Length([
                        'max' => 320,
                        'maxMessage' => 'mail.too_long',
                    ]),
                    new UniqueMail([
                        'message' => 'mail.not_unique',
                    ]),
                ],
            ])
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
            ->add('password', RepeatedType::class, [
                'first_options' => [
                    'label' => 'base.password',
                ],
                'second_options' => [
                    'label' => 'register.repeat_password'
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
