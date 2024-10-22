<?php

namespace App\Form;

use App\Entity\UserProfile;
use App\Enum\UserGender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; // Ajoutez ceci pour le bouton d'enregistrement
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPersonalInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userName', TextType::class, [
                'label' => 'Pseudo',
                'attr' => [
                    'placeholder' => 'Entrez votre pseudo',
                    'class' => 'form-control'
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Entrez votre prénom',
                    'class' => 'form-control'
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom',
                    'class' => 'form-control'
                ],
            ])
            ->add('dateBirth', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => 'AAAA-MM-JJ', // Ajoute un placeholder
                    'class' => 'form-control' // Classe Bootstrap pour le style
                ],
                'html5' => true, // Utilise le sélecteur de date HTML5
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => UserGender::Monsieur,
                    'Femme' => UserGender::Madame,
                    'Autre' => UserGender::Autre,
                ],
                'expanded' => false, // Utilise un select
                'multiple' => false, // Assure qu'une seule valeur peut être sélectionnée
                'label' => 'Genre',
                'attr' => [
                    'class' => 'form-select' // Classe Bootstrap pour le style
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les informations',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
