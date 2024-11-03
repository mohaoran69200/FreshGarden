<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

class EditEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('old_email', EmailType::class, [
                'label' => 'Ancien Email',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre ancien email.']),
                    new EmailConstraint(['message' => 'Veuillez entrer un email valide.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Nouvel Email',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nouvel email.']),
                    new EmailConstraint(['message' => 'Veuillez entrer un email valide.']),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour l\'Email',
                'attr' => ['class' => 'btn'],
            ]);
    }
}
