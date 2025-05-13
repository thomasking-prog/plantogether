<?php

namespace App\Form;

use App\Entity\Priority;
use App\Entity\Project;
use App\Entity\Statut;
use App\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', IntegerType::class, [
                'attr' => [
                    'style' => 'display:none',
                ],
                'label_attr' => [
                    'style' => 'display:none',
                ],
            ])
            ->add('label', TextType::class, [
                'label' => 'Nom de la tâche',
            ])
            ->add('estimatedTime', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'step' => 0.25,
                ],
                'label' => 'Temps estimé'
            ])
            ->add('formatTime', ChoiceType::class, [
                'choices' => [
                    'Sélectionnez un format' => '0',
                    'Jour' => 'd',
                    'Heure' => 'h',
                    'Minute' => 'm',
                    'Seconde' => 's',
                    'Semaine' => 'w'
                ],
                'label' => 'Format du temps'
            ])
            ->add('priority', EntityType::class, [
                'class' => Priority::class,
                'choice_label' => 'label',
                'label' => 'Priorité',
                'placeholder' => 'Sélectionnez une priorité',
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'label',
                'attr' => [
                    'style' => 'display:none',
                ],
                'label_attr' => [
                    'style' => 'display:none',
                ]
            ])
            ->add('statut', EntityType::class, [
                'class' => Statut::class,
                'choice_label' => 'label',
                'attr' => [
                    'style' => 'display:none',
                ],
                'label_attr' => [
                    'style' => 'display:none',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
