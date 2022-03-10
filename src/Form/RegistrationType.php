<?php

namespace App\Form;

use App\Constraints\UniqueUsername;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
                'label' => 'admin.register.username',
                'constraints' => [
                    new NotBlank(),
                    new UniqueUsername(),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'admin.register.password',
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 8,
                    ]),
                ],
            ])
            ->add('isAdmin', CheckboxType::class, [
                'label' => 'admin.register.make_admin',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'admin.register.submit',
            ])
        ;
    }
}
