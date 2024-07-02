<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Picture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la recette',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de la recette',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('pictures', FileType::class, [
                'label' => 'Photos de la recette',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'multiple' => 'multiple',
                    'accept' => 'image/*',
                    'class' => 'form-control-file',
                ]
            ])
            ->add('ingredients', TextareaType::class, [
                'mapped' => false,
                'label' => 'Ajouter vos ingrédients',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple : ingrédient:1;ingrédient:3'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
