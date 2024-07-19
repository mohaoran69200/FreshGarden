<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\ProductUnit;
use NumberFormatter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => [
                    'class' => 'form-input'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('price', MoneyType::class, [
                'currency' => "EUR",
                'rounding_mode' => 0,
            ])
            ->add('unit', EnumType::class, [
                'class' => ProductUnit::class
            ])
            ->add('stock')
            ->add('categorie', IntegerType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name',
            ])
            ->add('image', EntityType::class, [
                'class' => Image::class,
                'choice_label' => 'imageUrl',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
