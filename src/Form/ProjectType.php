<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\Language;
use App\Repository\LanguageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProjectType extends AbstractType
{
    private LanguageRepository $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('name', TextType::class)
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'label',
                'choices' => $this->languageRepository->findAll(),
                'required' => true,
                'placeholder' => 'Choose a language',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'data' => $user,
                'disabled' => true,
            ])
            ->add('target_languages', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'label',
                'choices' => $this->languageRepository->findAll(),
                'required' => true,
                'placeholder' => 'Choose target languages',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Create Project'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'user' => null,
        ]);
    }
}
