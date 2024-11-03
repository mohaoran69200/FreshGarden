<?php

namespace App\Form;

use App\Entity\UserProfile;
use App\Enum\UserGender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPersonalInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userName', TextType::class, [
                'label' => 'Pseudo',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez votre pseudo',
                    'class' => 'form-control',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez votre prénom',
                    'class' => 'form-control',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez votre nom',
                    'class' => 'form-control',
                ],
            ])
            ->add('dateBirth', DateType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => 'AAAA-MM-JJ',
                    'class' => 'form-control',
                ],
                'html5' => true,
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => UserGender::Monsieur,
                    'Femme' => UserGender::Madame,
                    'Autre' => UserGender::Autre,
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'label' => 'Titre',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les informations',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
