<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class VerifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setAction('/verify')
            ->add('code', NumberType::class, [
                'label' => 'verify.code',
                'attr' => [
                    'placeholder' => '000 000'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'verify.not_blank',
                    ]),
                    new Length([
                        'value' => 6,
                        'minMessage' => 'verify.too_short',
                        'maxMessage' => 'verify.too_long',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'verify.submit',
            ])
        ;
    }
}
