<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
