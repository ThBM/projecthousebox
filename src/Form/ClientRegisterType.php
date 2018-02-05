<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                "attr" => ["placeholder" => "Votre Email"],
                "label" => false
            ])
            ->add("password", RepeatedType::class, [
                "type" => PasswordType::class,
                'first_options'  => [
                    'label' => false,
                    "attr" => [
                        "placeholder" => "Mot de passe"
                    ]
                ],
                'second_options'  => [
                    'label' => false,
                    "attr" => [
                        "placeholder" => "Confirmer le mot de passe"
                    ]
                ]
            ])
            ->add('nom', TextType::class, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Nom"
                ]
            ])
            ->add('prenom', TextType::class, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Prénom"
                ]
            ])
            ->add("adresse", TextareaType::class, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Adresse"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // uncomment if you want to bind to a class
            'data_class' => Client::class,
        ]);
    }
}
