<?php

namespace App\Form;

use App\Entity\OrderLine;
use App\Enum\DeliveryMode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Ajoutez le champ pour le mode de récupération (retrait ou livraison)
            ->add('deliveryMode', EnumType::class, [
                'class' => DeliveryMode::class,
                'label' => 'Mode de récupération',
                'attr' => [
                    'class' => 'delivery-mode',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
        ]);
    }
}
