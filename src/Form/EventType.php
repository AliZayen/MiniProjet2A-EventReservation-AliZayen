<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $controlClass = 'bg-light text-dark border-secondary';

        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'class' => $controlClass,
                    'placeholder' => 'Symfony meetup, workshop…',
                ],
            ])
            ->add('eventDate', DateTimeType::class, [
                'label' => 'Date & time',
                'widget' => 'single_text',
                'attr' => [
                    'class' => $controlClass,
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'attr' => [
                    'class' => $controlClass,
                    'placeholder' => 'Sfax, Tunis…',
                ],
            ])
            ->add('picture', TextType::class, [
                'label' => 'Picture URL',
                'required' => false,
                'attr' => [
                    'class' => $controlClass,
                    'placeholder' => 'https://…',
                ],
            ])
            ->add('orgnizer', TextType::class, [
                'label' => 'Organizer',
                'attr' => [
                    'class' => $controlClass,
                    'placeholder' => 'Company / person name',
                ],
            ])
            ->add('ticketPrices', TextType::class, [
                'label' => 'Ticket prices',
                'required' => false,
                'attr' => [
                    'class' => $controlClass,
                    'placeholder' => 'Free, 15 TND, VIP 50 TND',
                ],
            ])
            ->add('tags', EntityType::class, [
                'label' => 'Tags',
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => $controlClass,
                ],
            ])
            ->add('planning', TextareaType::class, [
                'label' => 'Planning (optional)',
                'required' => false,
                'attr' => [
                    'class' => $controlClass,
                    'rows' => 5,
                    'placeholder' => "09:00 - Welcome\n10:00 - Talk…",
                ],
            ])
            ->add('details', TextareaType::class, [
                'label' => 'Details',
                'attr' => [
                    'class' => $controlClass,
                    'rows' => 6,
                    'placeholder' => 'Describe the event…',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

