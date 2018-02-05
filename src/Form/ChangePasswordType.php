<?php

namespace App\Form;

use App\Entity\ChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("password", PasswordType::class, [
                "attr" => [
                    "placeholder" => "Mot de passe"
                ]
            ])
            ->add("newPassword", RepeatedType::class, [
                "type" => PasswordType::class,
                'first_options'  => [
                    'label' => false,
                    "attr" => [
                        "placeholder" => "Nouveau mot de passe"
                    ]
                ],
                'second_options'  => [
                    'label' => false,
                    "attr" => [
                        "placeholder" => "Confirmer le nouveau mot de passe"
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // uncomment if you want to bind to a class
            'data_class' => ChangePassword::class,
        ]);
    }
}
