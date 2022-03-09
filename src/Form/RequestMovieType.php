<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RequestMovieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextType::class, [
                'label' => 'Film Titel',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Film Titel - z.B.: Conjuring, Hellraiser, ...',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Suchen',
            ])
            ->setMethod('GET')
        ;
    }
}
