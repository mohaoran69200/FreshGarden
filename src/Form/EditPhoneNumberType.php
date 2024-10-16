<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditPhoneNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('old_phone', TelType::class, [
                'label' => 'Ancien Numéro de Téléphone',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre ancien numéro de téléphone.']),
                ],
            ])
            ->add('new_phone', TelType::class, [
                'label' => 'Nouveau Numéro de Téléphone',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nouveau numéro de téléphone.']),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour le Numéro',
                'attr' => ['class' => 'btn'],]);
    }
}
