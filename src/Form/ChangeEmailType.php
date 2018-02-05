<?php

namespace App\Form;

use App\Entity\ChangeEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newEmail', RepeatedType::class, [
                "type" => EmailType::class,
                'first_options'  => [
                    'label' => false,
                    "attr" => [
                        "placeholder" => "Nouvel email"
                    ]
                ],
                'second_options'  => [
                    'label' => false,
                    "attr" => [
                        "placeholder" => "Confirmer le nouvel email"
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // uncomment if you want to bind to a class
            'data_class' => ChangeEmail::class,
        ]);
    }
}
