<?php
namespace App\Form;

use App\Entity\Traduction;
use App\Entity\Language;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraductionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content')
            ->add('language', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'label',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $projectTargetLanguages = $options['project']->getTargetLanguages()->map(fn($l) => $l->getCode())->toArray();
                    $userLanguages = $options['user']->getLanguages()->map(fn($l) => $l->getCode())->toArray();
                    $codes = array_intersect($projectTargetLanguages, $userLanguages);
                    return $er->createQueryBuilder('l')
                        ->where('l.code IN (:codes)')
                        ->setParameter('codes', $codes);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Traduction::class,
            'user' => null,
            'project' => null,
        ]);
    }
}