<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPhoneNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Vérification si l'utilisateur a déjà un numéro de téléphone
        $userHasPhone = $options['userHasPhone'];

        if ($userHasPhone) {
            // Si l'utilisateur a déjà un numéro de téléphone, demander l'ancien numéro
            $builder
                ->add('old_phone', TelType::class, [
                    'label' => 'Ancien Numéro de Téléphone',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez entrer votre ancien numéro de téléphone.']),
                    ],
                ]);
        }

        // Champ pour le nouveau numéro de téléphone
        $builder
            ->add('new_phone', TelType::class, [
                'label' => 'Nouveau Numéro de Téléphone',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nouveau numéro de téléphone.']),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour le Numéro',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Ajout d'une option pour indiquer si l'utilisateur a un numéro de téléphone ou non
        $resolver->setDefaults([
            'userHasPhone' => false,
        ]);
    }
}
