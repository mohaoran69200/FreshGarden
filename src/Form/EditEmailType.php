<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('old_email', EmailType::class, [
                'label' => 'Ancien Email',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre ancien email.']),
                ],
            ])
            ->add('new_email', EmailType::class, [
                'label' => 'Nouvel Email',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nouvel email.']),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Mettre à jour l\'Email']);
    }
}
