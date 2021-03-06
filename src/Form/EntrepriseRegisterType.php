<?php

namespace App\Form;

use App\Entity\Entreprise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntrepriseRegisterType extends AbstractType
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
                    "placeholder" => "Nom de la société"
                ]
            ])
            ->add("adresse", TextareaType::class, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Adresse de la société"
                ]
            ])
            ->add('siren', TextType::class, [
                "label" => false,
                "attr" => [
                    "placeholder" => "SIREN (9 chiffres)",
                    "pattern" => "^[0-9]{9}$",
                    "title" => "Le SIREN doit contenir 9 chiffres."
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entreprise::class,
        ]);
    }
}
