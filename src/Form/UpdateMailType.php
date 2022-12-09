<?php

namespace App\Form;

use App\Constraints\UniqueMail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateMailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mail', EmailType::class, [
                'label' => 'update_mail.mail',
                'constraints' => [
                    new NotBlank([
                        'message' => 'mail.not_blank',
                    ]),
                    new Length([
                        'max' => 320,
                        'maxMessage' => 'mail.too_long',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'update_mail.submit',
            ])
        ;
    }
}
